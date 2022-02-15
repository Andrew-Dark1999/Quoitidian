<?php

/**
 *    ParticipantController
 *    @author Alex R.
 */



class Participant extends Controller{



    /**
     * filter
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }





    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'addSelectCardAsResponsible':
            case 'actionHasParticipant':

                Access::setAccessCheckParams(Yii::app()->request->getPost('copy_id'));

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Access::getAccessCheckParams('access_id'), Access::getAccessCheckParams('access_id_type'))){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }

        $filterChain->run();
    }





    public function getData($copy_id, $data_id){
        $participant_data = ParticipantModel::model()->findAll(array(
            'condition' => 'copy_id = :copy_id AND data_id = :data_id',
            'params' => array(
                ':copy_id' => $copy_id,
                ':data_id' => $data_id,
            ),
        ));
        $participant_data_entities = array();
        if(!empty($participant_data_all)){
            foreach($participant_data_all as $data){
                $participant_data_entities[] = $data->getEntityData();
            }
        }

        return $participant_data;
    }



    /**
     *   Возвращает все данные для отображения listView
     */
    public function getDataForView(){
        $data = array();
        $data['submodule_data'] = $this->getData($_GET['pci'], $_GET['pdi']);
        return $data;
    }




    protected function getParticipantActionsInstance(){
        return new ParticipantActionsModel();
    }



    public function actionShow(){
        $data = $this->getDataForView();
        $this->data['menu_main'] = null;//$this->module->extensionCopy->copy_id;

        $this->renderAuto(ViewList::getView('participant/listView'), $data);
    }






    /**
     * Производит поиск участников из блока Участники, у которых емейл из блока емейл-участнки
     * совпадает с емейлом, указанном для коммуникаций
     */
    public function actionFindExistsEmailParticipantInCommunications($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
            ->setProperties($properties)
            ->run(ParticipantActionsModel::ACTION_FIND_EXISTS_EMAIL_PARTICIPANT_IN_COMMUNICATIONS)
            ->getResult();

        return $this->renderJson($result);
    }




    /**
     * проверяет и возвращает наличие пользователя системы по его емейл
     */
    public function actionHasParticipantUserByEmailId($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_HAS_PARTICIPANT_USER)
                            ->getResult();

        return $this->renderJson($result);
    }



    /**
     * возвращает список новых учасников
     */
    public function actionGetItemList($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_SELECT_ITEM_LIST)
                            ->getResult();

        $result['without_participant_const'] = false;

        return $this->renderJson($result);
    }






    /**
     * возвращает карточку-иконку учасника из виджета EditView.BlockParticipant
     */
    public function actionGetSelectedIconItem($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_ICON_ITEM)
                            ->getResult();

        return $this->renderJson($result);
    }



    /**
     * возвращает карточку текущего пользователя  (кнопка подписаться/отписаться)
     */
    public function actionGetSelectedIconItemForUser(){

        // ищем уже сохраненного пользователя
        $copy_id = \Yii::app()->request->getPost('copy_id');
        $data_id = \Yii::app()->request->getPost('data_id');
        $participant_model = null;

        if($copy_id && $data_id){
            $participant_model = \ParticipantModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
                'params' => array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                    ':ug_id' => WebUser::getUserId(),
                    ':ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                ),
            ));
            if($participant_model){
                $participant_model->responsible = '0';
            }
        }


        $item_data = ParticipantModel::model()->getEntityDataByParams(WebUser::getUserId(), \ParticipantModel::PARTICIPANT_UG_TYPE_USER, $participant_model);
        $html_first = (new EditViewBuilder())->getEditViewElementCardParticipant($item_data);

        $select_model = (new ParticipantItemListBulder())
            ->setIData($item_data)
            ->setILinkRemove(true);

        $html_second = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList'),
            array(
                'view' => $select_model->getView(ParticipantItemListBulder::VIEW_ITEM_PARTICIPANT),
                'model' => $select_model,
            ),
            true);

        return $this->renderJson(array(
            'status' => true,
            'html_first' => $html_first,
            'html_second' => $html_second,
        ));
    }







    /**
     * возвращает карточку учасника для блока select
     */
    public function actionGetSelectedListItem($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_LIST_ITEM)
                            ->getResult();

        return $this->renderJson($result);

    }







    /**
     * делает учасника ответственым
     */
    public function actionGetListItemAsResponsible(){
        $validate = new Validate();

        if(!Yii::app()->request->getPost('ug_id') || !Yii::app()->request->getPost('ug_type')){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));

        }

        ParticipantModel::model()->updateAll(array('responsible' => '0'),
            'copy_id=:copy_id AND data_id=:data_id AND ug_type=:ug_type',
            array(
                ':copy_id' => Yii::app()->request->getPost('copy_id'),
                ':data_id' => Yii::app()->request->getPost('data_id'),
                ':ug_type' => Yii::app()->request->getPost('ug_type')
            )
        );

        $responsible = ParticipantModel::model()->find(
            'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
            array(
                ':copy_id' => Yii::app()->request->getPost('copy_id'),
                ':data_id' => Yii::app()->request->getPost('data_id'),
                ':ug_id'   => Yii::app()->request->getPost('ug_id'),
                ':ug_type' => Yii::app()->request->getPost('ug_type')
            )
        );

        if($responsible){
            $responsible->setAttribute('responsible','1');
            $result = $responsible->save();
        } else {
            // сохраняет учасника
            $result = $this->saveCard(array(
                'copy_id' => Yii::app()->request->getPost('copy_id'),
                'data_id' => Yii::app()->request->getPost('data_id'),
                'ug_id' => Yii::app()->request->getPost('ug_id'),
                'ug_type' => Yii::app()->request->getPost('ug_type'),
                'responsible' => "1",
            ));
        }

        // выбираем учасника
        $b_data = $responsible ? $responsible : ParticipantModel::model()->find(array(
            'condition' => 'copy_id = :copy_id AND data_id = :data_id AND ug_type=:ug_type AND responsible = "1"',
            'params' => array(
                ':copy_id' => Yii::app()->request->getPost('copy_id'),
                ':data_id' => Yii::app()->request->getPost('data_id'),
                ':ug_type' => Yii::app()->request->getPost('ug_type')
            )));

        $b_data = $b_data;

        $select_model = (new ParticipantItemListBulder())
            ->setBData($b_data)
            ->getPrepareBlockAvatarParams()
            ->setBaResponsibleAvatar(true)
            ->prepareHtml(ParticipantItemListBulder::VIEW_BLOCK_AVATAR);

        $html = $select_model->getHtml();

        return $this->renderJson(array(
            'status' => $result,
            'html' => $html,
        ));
    }








    /**
     * сохраняет Email учасника, или возвращает сохраненный
     */
    public function actionSaveItemEmail($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_SAVE_ITEM_EMAIL)
                            ->getResult();

        return $this->renderJson($result);
    }





    /**
     * сохранение участника
     */
    private function saveCard(array $attributes){
        $model = new ParticipantModel();
        $model->setAttributes($attributes);
        return $model->save();
    }




    /**
     * actionHasParticipant - возвращает статус наличия блока участники или ответсвенного
     */
    public function actionHasParticipant($copy_id){
        return $this->renderJson(array(
            'status' => ParticipantModel::hasElementParticipant($copy_id),
        ));
    }







}
