<?php
/**
 * ProcessActions
 */

class ProcessActions{



    const ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY                    = 'cp_after_created_entity';
    const ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY                    = 'cp_after_changed_entity';
    const ACTION_CHANGE_PROCESS_PARTICIPANT_AFTER_CHENGED_PARTICIPANT   = 'cpp_after_changed_participant';


    private $_action_name;
    private $_vars;
    private $_error = false;

    private $_messages;
    private $_result = [];




    private function addErrorMessage($message, $params){
        $this->_error = true;
        $this->_error_cicle = true;

        $this->addMessage($message, $params);

        return $this;
    }

    private function addMessage($message, $params){
        $this->_messages[] = Yii::t('communications', $message, $params);

        return $this;
    }

    public function setError($error = true){
        $this->_error = $error;
        return $this;
    }

    private function isError(){
        return $this->_error;
    }

    private function getStatus(){
        return ($this->isError()) ? false : true;
    }

    public function getResult(){
        $result = array(
            'status' => $this->getStatus(),
            'messages' => $this->_messages,
        );

        if($this->_result){
            $result = array_merge($result, $this->_result);
        }

        return $result;
    }

    public function setActionName($action_name){
        $this->_action_name = $action_name;
        return $this;
    }

    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    public function setEnv(){
        if($this->_vars == false){
            return $this;
        }

        foreach($this->_vars as $key => $var){
            if(is_array($var)){
                continue;
            }
            putenv($key.'='.$var);
        }
        return $this;
    }



    /**
     * run
     */
    public function run(){
        switch($this->_action_name){
            //создание или изменение суности модуля
            case self::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY:
            case self::ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY:
                $this->actionCreateProcessACPDataList();
                break;
            case self::ACTION_CHANGE_PROCESS_PARTICIPANT_AFTER_CHENGED_PARTICIPANT:
                $this->actionChangeProcessParticipantACCP();
        }

        return $this;
    }





    /**
     * 1. actionCreateProcessACPDataList - создание или изменение сущности модуля -
     */
    private function actionCreateProcessACPDataList(){
        if(!empty($this->_vars['data_id'])){
            $data_id_list = (array)$this->_vars['data_id'];
        } else if(!empty($this->_vars['data_id_list'])){
            $data_id_list = $this->_vars['data_id_list'];
        }

        foreach($data_id_list as $data_id){
            $this->_vars['data_id'] = $data_id;
            $this->actionCreateProcessACP();
        }
    }



    /**
     * actionCreateProcessACP - создание или изменение суности модуля
     * @return ProcessActions
     */
    private function actionCreateProcessACP(){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule();

        if(empty($this->_vars['edit_view_model'])){
            $edit_view_model = $this->findEditViewModel();
        }

        if($edit_view_model == false){
            return $this->setError();
        }

        $autostart_model_list = $this->getProcessAutostartByEntityList();

        if($autostart_model_list == false){
            return $this->setError();
        }

        foreach($autostart_model_list as $autostart_model){
            $begin_model = $this->actionCreateProcessACP_getOperationBeginModel($autostart_model->operations_id);

            switch($this->_action_name){
                case self::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY:
                    $ch = true;
                    break;
                case self::ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY:
                    // проверка статуса процесса
                    $ch = $this->actionCreateProcessACP_CheckProcessStatus($autostart_model);
                    // прорерка знаний по условию
                    if($ch){
                        $ch = $begin_model->checkConditionForEntity($edit_view_model);
                    }
                    break;
            }

            if($ch){
                $this->actionCreateProcessACP_CreateProcess($begin_model);
            }
        }
    }




    private function actionCreateProcessACP_getOperationBeginModel($operations_id){
        return \Process\models\OperationsModel::getChildrenModelByOperationsId($operations_id);
    }


    /**
     * actionCreateProcessACP_CheckProcessStatus - проверяет, если ли процесс со статусов "в работе",
     *                                              что был уже создан из данного шаблона
     */
    private function actionCreateProcessACP_CheckProcessStatus($autostart_model){
        $module_tables_model = \ModuleTablesModel::getRelateModel($autostart_model->copy_id, \ExtensionCopyModel::MODULE_PROCESS, [ModuleTablesModel::TYPE_RELATE_MODULE_ONE, ModuleTablesModel::TYPE_RELATE_MODULE_MANY], true);
        if($module_tables_model == false){
            return false;
        }

        $data_model = new \DataModel();
        $data_model
            ->setSelect('process_id')
            ->setFrom('{{process}}')
            ->andWhere('parent_process_id = ' . $autostart_model->operations->process_id)
            ->andWhere('related_module= ' . $autostart_model->copy_id)
            ->andWhere('(b_status is NULL OR b_status != "' . Process\models\ProcessModel::B_STATUS_TERMINATED . '")');

        $process_model_list = $data_model->findCol();

        if($process_model_list == false){
            return true;
        }

        $count = (new \DataModel())
                        ->setFrom('{{'.$module_tables_model->table_name.'}}')
                        ->andWhere($module_tables_model->parent_field_name . ' = ' . $this->_vars['data_id'])
                        ->andWhere(array('in', $module_tables_model->relate_field_name, $process_model_list))
                        ->findCount();

        return ($count == false ? true : false);
    }




    private function actionCreateProcessACP_CreateProcess($begin_model){
        $process_id = $begin_model->getOperationsModel()->process_id;

        $vars = array(
            'process' => array(
                'b_status' => \Process\models\ProcessModel::B_STATUS_IN_WORK,
            ),
            'bpm_params' => array(
                'objects' => array(
                    \Process\models\BpmParamsModel::OBJECT_BINDING_OBJECT => array(
                        'attributes' => array(
                            'copy_id' => $this->_vars['copy_id'],
                            'data_id' => $this->_vars['data_id'],
                        )
                    )
                )
            )
        );

        $process_id = \Process\models\ProcessModel::getInstance($process_id, true)
                                ->setVars($vars)
                                ->validateBeforeCreateFromTemplate('checkCreateFromTemplateParticipantTypeConst')
                                ->createFromTemplate();

        if($process_id){
            $process_model = \Process\models\ProcessModel::getInstance($process_id, true)->setVars($vars);

            if(\Process\models\OperationBeginModel::setProcessRelatedObject($this->_vars['data_id'])){
                    $process_model
                        ->replaceResponsibleByParticipantTypeConst('process')
                        ->replaceResponsibleByParticipantTypeConst('operations')
                        ->runProcess();
            } else {
                // сообщение пользователю
                \Process\models\ProcessModel::addActivityMessageIfEmptyRelateOblect();

            }
        }
    }



    private function getProcessAutostartActionName(){
        switch($this->_action_name){
            case self::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY:
                return \Process\models\ProcessAutostartByEntityModel::ACTION_NAME_CREATE;
            case self::ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY:
                return \Process\models\ProcessAutostartByEntityModel::ACTION_NAME_CHANGE;
        }
    }




    private function getProcessAutostartByEntityList(){
        $criteria = new CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id');
        $criteria->addCondition('action_name=:action_name');
        $criteria->params = array(
            ':copy_id' => $this->_vars['copy_id'],
            ':action_name' => $this->getProcessAutostartActionName(),
        );

        return \Process\models\ProcessAutostartByEntityModel::model()->findAll($criteria);
    }




    private function findEditViewModel(){
        if(empty($this->_vars['copy_id']) || empty($this->_vars['data_id'])){
            return;
        }

        $edit_view_action_model = (new \EditViewActionModel($this->_vars['copy_id']))
                                        ->setEditData(array('id' => $this->_vars['data_id']))
                                        ->createEditViewModel();

        return $edit_view_action_model->getEditModel();
    }










    /**
     * 3. actionChangeProcessParticipantACCP
     */
    private function actionChangeProcessParticipantACCP(){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule();

        $module_tables_model = \ModuleTablesModel::getRelateModel($this->_vars['copy_id'], \ExtensionCopyModel::MODULE_PROCESS, [ModuleTablesModel::TYPE_RELATE_MODULE_ONE, ModuleTablesModel::TYPE_RELATE_MODULE_MANY], true);
        if($module_tables_model == false){
            return false;
        }

        $process_id_list = (new \DataModel())
                        ->setSelect('process_id')
                        ->setFrom('{{'.$module_tables_model->table_name.'}} as t1')
                        ->andWhere($module_tables_model->parent_field_name . ' = ' . $this->_vars['data_id'] )
                        ->findCol();

        if($process_id_list == false){
            return;
        }

        foreach($process_id_list as $process_id){
            $process_model = \Process\models\ProcessModel::getInstance($process_id, true);

            // пропускаем завершенные процессы
            if($process_model->getBStatus() == \Process\models\ProcessModel::B_STATUS_TERMINATED){
                continue;
            }

            $b = $this->actionChangeProcessParticipantACCP_ReplaceProcessParticipant();
            if($b){
                $this->actionChangeProcessParticipantACCP_ReplaceProcessBpmParticipant();
            }
        }
    }


    /**
     * actionChangeProcessParticipantACCP_ReplaceProcessParticipant - замена ответсвенного в параметре процесса
     * @param $process_id
     */
    private function actionChangeProcessParticipantACCP_ReplaceProcessParticipant(){
        // ищем участника в процессе, который был подменен с Константы
        $criteria = new CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type');
        $criteria->addCondition('participantFlags.flag=:flag');
        $criteria->params = array(
            ':copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
            ':data_id' => Process\models\ProcessModel::getInstance()->process_id,
            ':ug_id' => $this->_vars['participant']['from']['ug_id'],
            ':ug_type' => $this->_vars['participant']['from']['ug_type'],
            ':flag' => ParticipantFlagsModel::FLAG_CONST_RELATE_RESPONSIBLE,
        );

        $participant_model = ParticipantModel::model()->with('participantFlags')->find($criteria);

        if($participant_model == false){
            return false;
        }

        $participant_model->setAttributes(array(
                'ug_id' => $this->_vars['participant']['to']['ug_id'],
                'ug_type' => $this->_vars['participant']['to']['ug_type'],
            )
        );

        if($participant_model->save()){
            return true;
        }

        return false;
    }




    /**
     * actionChangeProcessParticipantACCP_ReplaceProcessParticipant - замена ответсвеных bpm процессе (блок связанных, операторы)
     * @param $process_id
     */
    private function actionChangeProcessParticipantACCP_ReplaceProcessBpmParticipant(){
        foreach(\ParticipantConstModel::getTypeConstListFull() as $type_const){
            $participant_model = \Process\models\ProcessModel::getInstance()->getResponsileByParticipantTypeConst(\Process\models\ProcessModel::getInstance()->process_id, $type_const);
            if($participant_model == false){
                continue;
            }

            $participants = array(
                array(
                    'ug_id' => $this->_vars['participant']['from']['ug_id'],
                    'ug_type' => $this->_vars['participant']['from']['ug_type'],
                    'flag' => (new \ParticipantConstModel())->getProcessFlagByConstType($type_const),
                    'attributes' => array(
                        'ug_id' => $participant_model->ug_id,
                        'ug_type' => $participant_model->ug_type,
                    ),
                )
            );

            $vars = array(
                'action' => \Process\models\BpmParamsModel::ACTION_UPDATE,
                'process_id' => \Process\models\ProcessModel::getInstance()->process_id,
                'objects' => array(
                    'participants' => $participants,
                ),
            );



            (new \Process\models\BpmParamsModel())
                ->setVars($vars)
                ->validate()
                ->run()
                ->getResultMessages();

            \Process\models\ResponsibleBpmFactoryModel::flush();
        }




        return $this;
    }









    /*
    private function actionChangeProcessParticipantACCP_getProcessResponsible(){
        $participant_model = \ParticipantModel::getParticipants(\ExtensionCopyModel::MODULE_PROCESS, \Process\models\ProcessModel::getInstance()->process_id, null, true, true);
        if(empty($participant_model)){
            return;
        }

        return array(
            'ug_id' => $participant_model->ug_id,
            'ug_type' => $participant_model->ug_type,
        );
    }

    private function actionChangeProcessParticipantACCP_getProcessRelateResponsible(){
        return array(
            'ug_id' => $this->_vars['participant']['to']['ug_id'],
            'ug_type' => $this->_vars['participant']['to']['ug_type'],
        );
    }
    */





}
