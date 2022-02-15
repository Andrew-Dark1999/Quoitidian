<?php
/**
 * ParticipantActionsModel
 */

class ParticipantActionsModel{

    const ACTION_PREPARE_SELECT_ITEM_LIST       = 'prepare_select_item_list';   // PSIL
    const ACTION_PREPARE_ICON_ITEM              = 'prepare_icon_item';          // PII
    const ACTION_PREPARE_LIST_ITEM              = 'prepare_list_item';          // PLI
    const ACTION_PREPARE_SAVE_ITEM_EMAIL        = 'prepare_save_item_email';    // SIE
    const ACTION_PREPARE_HAS_PARTICIPANT_USER   = 'prepare_has_participant';    // HPU
    const ACTION_FIND_EXISTS_EMAIL_PARTICIPANT_IN_COMMUNICATIONS = 'find_email_participant_in_comunications';   // FNEPIC



    private $_vars = array();


    private $_type_item_list = ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT;


    private $_status = true;
    private $_result = [];
    private $_messages;



    public function getShowResponsible(){
        if($this->_vars && array_key_exists('show_responsible', $this->_vars)){
            return $this->_vars['show_responsible'];
        }

        return false;
    }


    private function prepareExceptionListId(){
        if(empty($this->_vars['exception_list_id'])){
            return;
        }

        if(is_array($this->_vars['exception_list_id'])){
            foreach($this->_vars['exception_list_id'] as &$item){
                if(is_string($item)){
                    $item = json_decode($item, true);
                }
            }
        }

        return $this;
    }


    private function getExceptionListId($type_item_list = null){
        if($type_item_list === null){
            $type_item_list = $this->_type_item_list;
        }

        if(!empty($this->_vars['exception_list_id'][$type_item_list])){
            return $this->_vars['exception_list_id'][$type_item_list];
        }
    }


    protected function getParticipantItemListBulderInstance(){
        return new ParticipantItemListBulder();
    }


    public function setProperties($properties){
        if(!$properties){
            return $this;
        }

        foreach($properties as $property_name => $value){
            if(property_exists($this, '_' . $property_name)){
                $this->{'_' . $property_name} = $value;
            }
        }

        $this->prepareExceptionListId();

        return $this;
    }



    public function getResult(){
        $result = $this->_result;

        if($this->_status == false && $this->_messages){
            $result['messages'] = $this->_messages;
        }
        $result['status'] = $this->_status;

        return $result;
    }



    public function setMessageAr(ActiveRecord $model){
        $this->_status = false;

        $validate = new \Validate();
        $validate->addValidateResultFromModel($model->getErrors());

        $this->_messages = $validate->getValidateResultHtml();

        return false;
    }




    public function run($action_name){
        switch($action_name){
            case self::ACTION_PREPARE_SELECT_ITEM_LIST:
                $this->prepareSelectItemList();
                break;
            case self::ACTION_PREPARE_ICON_ITEM:
                $this->prepareIconItem();
                break;
            case self::ACTION_PREPARE_LIST_ITEM:
                $this->prepareListItem();
                break;
            case self::ACTION_PREPARE_SAVE_ITEM_EMAIL:
                $this->prepareSaveItemEmail();
                break;
            case self::ACTION_PREPARE_HAS_PARTICIPANT_USER:
                $this->prepareHasParticipantUserByEmailId();
                break;
            case self::ACTION_FIND_EXISTS_EMAIL_PARTICIPANT_IN_COMMUNICATIONS:
                $this->findExistsEmailParticipantInCommunications();
        }

        return $this;
    }


    /**
     * 1. ACTION_PREPARE_SELECT_ITEM_LIST - PSIL
     */
    protected function prepareSelectItemList(){
        $select_model = $this->getParticipantItemListBulderInstance()
                                    ->setBDisplay(true)
                                    ->setBData($this->getBData_PSIL())
                                    ->setBaResponsibleAvatar($this->getResponsibleAvatar())
                                    ->setBilTypeItemList($this->_type_item_list)
                                    ->setBilLinkSelectedItemList(true)
                                    ->setBilItemListSwitchShow(false) //useCommunicationFunctional()
                                    ->setBilData($this->getBilData_PSIL())
                                    ->setIClassAdd(true)
                                    ->prepareHtml(ParticipantItemListBulder::VIEW_BLOCK);

        $this->_result['html'] = $select_model->getHtml();

        return $this;
    }


    /**
     * 2. ACTION_PREPARE_ICON_ITEM - PII
     */
    private function prepareIconItem(){
        $data_entity = $this->getDataEntity_PII();

        if(array_key_exists('change_responsible', $this->_vars)){
            ParticipantModel::setChangeResponsible((boolean)$this->_vars['change_responsible']);
        }

        $this->_result['html'] = $this->getHtml_PII($data_entity);

        return $this;
    }




    /**
     * 3. ACTION_PREPARE_LIST_ITEM - PLI
     */
    private function prepareListItem(){
        if(!empty($this->_vars['save_entity']) && $this->_type_item_list == ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT){
            $attr = array(
                'copy_id' => $this->_vars['copy_id'],
                'data_id' => $this->_vars['data_id'],
                'ug_id' => $this->_vars['ug_id'],
                'ug_type' => $this->_vars['ug_type'],
            );
            $this->saveParticipant($attr);
        }

        $i_data = $this->getIData_PLI();

        $select_model = $this->getParticipantItemListBulderInstance()
                                    ->setIData($i_data)
                                    ->setILinkRemove(true);

        $this->_result['html'] = $this->getHtml_PLI($select_model);

        return $this;
    }


    /**
     * 4. SAVE_ITEM_EMAIL - SIE
     */
    private function prepareSaveItemEmail(){
        if($this->validate_SIE() == false){
            return;
        }

        // поиск участника системы по его емейлу
        $users_model = $this->findParticipantUserModelByEmail_SIE();
        // если найден - возвращаем как участника
        if($users_model){
            $this->_result['users_id'] = $users_model->users_id;
            $emails_model = EmailsModel::findByEmail($this->_vars['email']);
            if($emails_model){
                $this->_result['email_id'] = $emails_model->email_id;
                UsersStorageEmailModel::updateDateAffect(array('users_id' => $users_model->users_id, 'email_id' => $emails_model->email_id));
            }

        // Сохранение email
        } else {
            $emails_model = $this->saveEmail_SIE();
            if($emails_model){
                $this->_result['email_id'] = $emails_model->email_id;
            }
        }

        return $this;
    }




    /**
     * 5. ACTION_PREPARE_HAS_PARTICIPANT_USER - HPU
     *    Проверяет и возвращает наличие пользователя системы по его емейл
     */
    private function prepareHasParticipantUserByEmailId(){
        $emails_model = EmailsModel::model()->findByPk($this->_vars['email_id']);

        if($emails_model == false){
            $this->_status = false;
            return;
        }

        $users_model_list = $this->findParticipantUserByEmail_HPU($emails_model->email);

        if($users_model_list == false){
            $this->_status = false;
            return $this;
        }

        $result = array();

        foreach($users_model_list as $users_model){
            $result[] =  array(
                'email_id' => $emails_model->email_id,
                'users_id' => $users_model->users_id,
            );

            UsersStorageEmailModel::updateDateAffect(array('users_id' => $users_model->users_id, 'email_id' => $emails_model->email_id));
        }

        $this->_result['participan_list'] = $result;


        return $this;
    }






    /**
     * 6. findExistsEmailParticipantInCommunications - FEPIC
     * Производит поиск участников из блока Участники, у которых емейл из блока емейл-участнки
     * совпадает с емейлом, указанном для коммуникаций
     */
    private function findExistsEmailParticipantInCommunications(){
        $this->_result['email_list'] = array();

        $participant_list = array();
        $participant_email_list = array();

        //participant
        if(!empty($this->_vars['block_participant']['participant'])){
            foreach($this->_vars['block_participant']['participant'] as $key => $participant){
                if($participant['ug_type'] != \ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                    continue;
                }

                $participant_list[] = $participant['ug_id'];
            }
        }
        // email participant
        if(!empty($this->_vars['block_participant']['email'])){
            foreach($this->_vars['block_participant']['email'] as $participant){
                $participant_email_list[] = $participant['email_id'];
            }
        }

        if($participant_list == false || $participant_email_list == false){
            return $this;
        }

        $this->_result['email_list'] = ParticipantEmailModel::getEmailListIsExistsInCommunications($participant_list, $participant_email_list);


        return $this;
    }







    /*************************
     *      Общие методы
     **************************/




    private function getResponsibleAvatar(){
        if($this->getShowResponsible()){
            return true;
        }
        return false;
    }



    private function getBDataParticipant(){
        $b_data = null;
        if($this->getShowResponsible()){
            $paticpant_class = $this->getParticipantClassName();
            $b_data = $paticpant_class::model()->find(
                array(
                    'condition' => 'copy_id = :copy_id AND data_id = :data_id AND responsible = "1"',
                    'params' => array(
                        ':copy_id' => $this->_vars['copy_id'],
                        ':data_id' => $this->_vars['data_id'],
                    )
                ));
        }

        return $b_data;
    }



    private function getParentPrimaryData(){
        return \DataValueModel::getInstance()->getParentPrimaryData($this->_vars['copy_id'], $this->_vars['data_id'], $this->_vars['pci'], $this->_vars['pdi']);
    }






    /**
     * сохранение участника
     */
    private function saveParticipant(array $attributes){
        $paticpant_class = $this->getParticipantClassName();

        $model = new $paticpant_class();
        $model->setAttributes($attributes);
        return $model->save();
    }



    protected function getParticipantClassName(){
        return 'ParticipantModel';
    }


    /*
    protected function useCommunicationFunctional(){
        return \ExtensionCopyModel::model()->findByPk($this->_vars['copy_id'])->useCommunicationFunctional();
    }
    */




    /**
     * сохранение участника-email
     */
    /*
    private function saveEmail(array $attributes){
        $model = new ParticipantEmailModel();
        $model->setAttributes($attributes);
        return $model->save();
    }
    */





    /*************************
     *      1. PSIL - ACTION_PREPARE_SELECT_ITEM_LIST
     **************************/


    private function getBData_PSIL(){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return $this->getBDataParticipant();
                break;
        }
    }



    private function getBilData_PSIL(){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return $this->getBilDataParticipant_PSIL();
                break;
            case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                return $this->getBilDataEmail_PSIL();
                break;
        }
    }



    private function getBilDataParticipant_PSIL(){
        $parent_primary_data = $this->getParentPrimaryData();

        $paticpant_class = $this->getParticipantClassName();

        $bil_data = (new $paticpant_class())->getOtherEntities(
            $this->getExceptionListId(ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT),
            $this->_vars['copy_id'],
            $this->_vars['data_id'],
            $parent_primary_data['pci'],
            $parent_primary_data['pdi']
        );

        return $bil_data;
    }




    private function getBilDataEmail_PSIL(){
        $bil_data = ParticipantEmailModel::model()->getOtherEntities(
                                $this->_vars['copy_id'],
                                $this->_vars['data_id'],
                                $this->getExceptionListId(ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL)
                            );

        return $bil_data;
    }










    /*************************
     *      2. PII - ACTION_PREPARE_ICON_ITEM
    *************************/



    private function getDataEntity_PII(){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return $this->getDataEntityParticipant_PII();
            case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                return $this->getDataEntityEmail_PII();
        }
    }



    private function getDataEntityParticipant_PII(){
        $paticpant_class = $this->getParticipantClassName();

        $participant_model = null;
        if($this->_vars['copy_id'] && $this->_vars['data_id']){
            $participant_model = \ParticipantModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
                                        'params' => array(
                                            ':copy_id' => $this->_vars['copy_id'],
                                            ':data_id' => $this->_vars['data_id'],
                                            ':ug_id' => $this->_vars['ug_id'],
                                            ':ug_type' => $this->_vars['ug_type'],
                                        ),
                                    ));
            if($participant_model){
                $participant_model->responsible = '0';
            }

        }

        return (new $paticpant_class())->getEntityDataByParams($this->_vars['ug_id'], $this->_vars['ug_type'], $participant_model);
    }



    private function getDataEntityEmail_PII(){
        $data = (new ParticipantEmailModel())->getEntityDataById($this->_vars['email_id']);
        UsersStorageEmailModel::updateDateAffect(array('users_id' => WebUser::getUserId(), 'email_id' => $this->_vars['email_id']));
        if(!empty($data['participant_email_id'])){
            $data['participant_email_id'] = null;
        }

        return $data;
    }



    private function getHtml_PII($data_entity){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return (new EditViewBuilder())->getEditViewElementCardParticipant($data_entity);
            case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                return (new EditViewBuilder())->getEditViewElementCardEmail($data_entity);
        }
    }











    /*************************
     *      3. PLI - ACTION_PREPARE_LIST_ITEM
     *************************/


    private function getIData_PLI(){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return $this->getDataEntityParticipant_PII();
            case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                return $this->getDataEntityEmail_PII();
        }
    }


    private function getHtml_PLI($select_builder){
        switch($this->_type_item_list){
            case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList'),
                    array(
                        'view' => $select_builder->getView(ParticipantItemListBulder::VIEW_ITEM_PARTICIPANT),
                        'model' => $select_builder,
                    ),
                    true);
            case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList'),
                    array(
                        'view' => $select_builder->getView(ParticipantItemListBulder::VIEW_ITEM_EMAIL),
                        'model' => $select_builder,
                    ),
                    true);
        }
    }




    /*************************
    *   4. SAVE_ITEM_EMAIL - SIE
    *************************/
    private function validate_SIE(){
        $use_model = new EmailsModel();
        $use_model->setScenario('validate');
        $use_model->setAttribute('email', $this->_vars['email']);

        if($use_model->validate() == false){
            return $this->setMessageAr($use_model);
        }

        return true;
    }


    /**
     * findParticipantByEmail_SIE - поиск участника с емейлом
     */
    private function findParticipantUserModelByEmail_SIE(){
        $communiction_service_params_model = (new CommunicationsServiceParamsModel())->findByUserLogin($this->_vars['email']);

        // если емейл совпадает с емейлов данного пользователя, что введен в параметрах активности - добавляем как участника
        if($communiction_service_params_model){
            $users_model = UsersModel::getUserModel($communiction_service_params_model->user_id);
            return $users_model;
        }
    }



    private function saveEmail_SIE(){
        // сохранение нового емейла
        $emails_model = new EmailsModel('insert');
        $emails_model->setAttribute('email', $this->_vars['email']);
        $emails_model = $emails_model->saveUnique();

        $exception_id_list = $this->getExceptionListId(ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL);

        if($emails_model && $exception_id_list && in_array($emails_model->email_id, $exception_id_list)){
            $this->_status = false;
            return;
        }

        if(UsersModel::hasStorageEmailId($emails_model->email_id) == false){
            $storage_email_model = new UsersStorageEmailModel();
            $storage_email_model->setAttributes(array(
                'users_id' => \WebUser::getUserId(),
                'email_id' => $emails_model->email_id,
            ));

            $storage_email_model->save();
        }

        return $emails_model;
    }









    /**
     * 5. ACTION_PREPARE_HAS_PARTICIPANT_USER - HPU
     */


    /**
     * findParticipantByEmail_SIE - поиск участника с емейлом
     */
    private function findParticipantUserByEmail_HPU($user_login){
        $communiction_service_params_model_list = (new CommunicationsServiceParamsModel())->findAllByUserLogin($user_login);

        // если емейл совпадает с емейлов данного пользователя, что введен в параметрах активности - добавляем как участника
        if($communiction_service_params_model_list == false){
            return;
        }

        $result = array();
        foreach($communiction_service_params_model_list as $communiction_service_params_model){
            $users_model = UsersModel::getUserModel($communiction_service_params_model->user_id);
            if($users_model == false){
                continue;
            }
            $result[] = $users_model;
        }

        return $result;
    }





}
