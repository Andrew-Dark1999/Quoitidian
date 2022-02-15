<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationAgreetmentModel extends OperationTaskBaseModel{

    const ELEMENT_TYPE_AGREETMENT   = 'type_agreetment';
    const ELEMENT_EMAIL             = 'email';

    const TYPE_AGREETMENT_INTERNAL  = 'internal';
    const TYPE_AGREETMENT_EXTERNAL  = 'external';



    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Agreetment');
    }


    public static function getTypeArgetments(){
        $list = array(
            static::TYPE_AGREETMENT_INTERNAL => \Yii::t('ProcessModule.base', 'Internal'),
            static::TYPE_AGREETMENT_EXTERNAL => \Yii::t('ProcessModule.base', 'External'),
        );

        return $list;
    }



    public function typeArgetmentIsExternal(){
        $result = false;

        $schema = $this->_operations_model->getSchema();
        if(empty($schema)) return;
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        foreach($schema as $element){
            if($element['type'] != static::ELEMENT_TYPE_AGREETMENT) continue;
            if($element['value'] == static::TYPE_AGREETMENT_EXTERNAL) $result = true;
            break;
        }

        return $result;
    }














    /**
     * checkExecution - проверка выполнения, установка статуса
     * @return $this
     */
    public function checkExecution(){
        $process_model = ProcessModel::getInstance();

        if($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if($b_status == ProcessModel::B_STATUS_STOPED){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_STOPED);
            }

            if($this->getCardBStatus() != static::B_STATUS_CONPLETED){
                $this->updateCardBStatus(null, self::B_STATUS_STOPED);
            }
            return $this;
        }

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->getStatus() == OperationsModel::STATUS_DONE){
                return $this;
            }

            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            $set_active = false;
            // запуск оператора
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->moveInCardRun(false); // Делаем параметр оператора Задачей...
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                $this->actionOperationSetActive();
                $set_active = true;
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                if($set_active == false) $this->moveInCardRun(true); // Делаем параметр оператора Задачей...
                if($this->getCardBStatus() == static::B_STATUS_CONPLETED){
                    $this->setStatus(OperationsModel::STATUS_DONE);
                } else {
                    $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                }
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                $this->actionOperationSetActive();
            }

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->updateCardBStatus(null, self::B_STATUS_CONPLETED);
                $this->setStatus(OperationsModel::STATUS_PAUSE);
            }

        }

        return $this;
    }








    /**
     * getEditViewDataSave - сохраняет данные EditView
     */
    public function editViewSave($edit_data, $ignore_required = false){

        // статус Согласования
        if(isset($edit_data['operation_agreetment_approve'])){
            $operation_agreetment_approve = $edit_data['operation_agreetment_approve'];
            unset($edit_data['operation_agreetment_approve']);

            if($this->isSetBStatus($operation_agreetment_approve)){
                $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());
                $status_params = $extension_copy->getStatusField();
                if(!empty($status_params)){
                    $edit_data['EditViewModel'][$status_params['params']['name']] = $operation_agreetment_approve;
                }
            }
        }


        $edit_model = parent::editViewSave($edit_data, $ignore_required);


        if(!empty($operation_agreetment_approve)){
            $this->backToTask($operation_agreetment_approve);
        }

        return $edit_model;
    }


    /**
     * backToTask - если отклонено - возвращаем все предшествующие задачи
     */
    private function backToTask($operation_agreetment_approve){
        if($operation_agreetment_approve == OperationTaskBaseModel::B_STATUS_CREATED){
            $this->_operations_model->setStatus(OperationsModel::STATUS_UNACTIVE);
            $this->_operations_model->saveStatus();

            $this->setStatusForParentTasks();
        }
    }




    /**
     * Устанавливает статус для всех предшествующих операторов Задача
     */
    private function setStatusForParentTasks(){
        $parent_ui_list = ArrowModel::getInstance()->getUniqueIndexParent($this->_operations_model->unique_index);

        if(empty($parent_ui_list)) return;

        foreach($parent_ui_list as $unique_index){
            $operations_model = OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $unique_index);

            if($operations_model->element_name != OperationsModel::ELEMENT_TASK) continue;


            $operation_task_model = OperationsModel::getChildrenModel($operations_model->element_name)
                                            ->setOperationsModel($operations_model);

            // статус для оператора Задача
            $operation_task_model->_operations_model->setStatus(OperationsModel::STATUS_ACTIVE);
            $operation_task_model->_operations_model->saveStatus();
            $operation_task_model->_operations_model->refresh();


            // статус для самой Задачи
            $operation_task_model->updateCardBStatus(null, self::B_STATUS_IN_WORK);


            // History
            $relate_copy_id = $this->getRelateCopyId();
            $relate_task_id = $operation_task_model->getIdCardFromSchema();

            $edit_data = array(
                'id' => (integer)$relate_task_id,
            );

            $comment = BindingObjectModel::getRelateObjectHistoryMessage(array('copy_id' => $relate_copy_id, 'card_id' => $relate_task_id));
            if(!empty($comment)) $comment = '</br>' . $comment;

            $edit_action_model = new \EditViewActionModel($relate_copy_id); //MODULE_TASKS
            $edit_action_model
                ->setEditData($edit_data)
                ->createEditViewModel();
            $edit_model = $edit_action_model->getEditModel();

            if(!empty($edit_model)){
                \History::getInstance()->addToHistory(\HistoryMessagesModel::MT_OPERATION_REJECTED,
                    $relate_copy_id,
                    $relate_task_id,
                    array(
                        '{module_data_title}' => $edit_model->getModuleTitle(),
                        '{user_id}' => \WebUser::getUserId(),
                        '{comment}' =>  (!empty($comment) ? $comment : ''),
                    ),
                    false,
                    false
                );
            }


            // копия Активности
            $card_id = $this->getIdCardFromSchema();
            if(empty($card_id)) return false;
            $relate_task_id = $this->getRelateIdCardFromSchema();

            $this->copyActivity($relate_task_id, $card_id);

        }
    }



    public function makeHistory($card_id, $loggin_respponsible_only = false, array $mt_list = [\HistoryMessagesModel::MT_CREATED, \HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED, \HistoryMessagesModel::MT_COMMENT_CREATED]){
        parent::makeHistory($card_id, true, $mt_list);
    }




}
