<?php
/**
 * OperationEndModel - оператор Конец
 * @author Alex R.
 */

namespace Process\models;


class OperationEndModel extends \Process\components\OperationModel{

    const ELEMENT_NEXT_PROCESS  = 'next_process';

    // список процессов (шаблонов), запущених автоматически через связь
    private $_next_process_running_list = array();


    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'End');
    }



    public function getBuildedParamsContent(){
        if(empty($this->_operations_model)) return;

        $schema = $this->_operations_model->getSchema();
        if(empty($schema)) return;
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        $content = '';
        foreach($schema as $element){
            $content.= $this->getElementHtml($element);
        }

        return $content;
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
        if($b_status == ProcessModel::B_STATUS_STOPED) return $this;

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);

            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_DONE);
                ProcessModel::getInstance()->updateBStatus(ProcessModel::B_STATUS_TERMINATED);

                $this->runNextProcessFromEnd();
                $this->runNextProcessFromBegin();
            }

            /*
            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
            }
            */

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
            /*
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_PAUSE);
            }
            */
        }

        return $this;
    }




    private function getOperationsForProcess($process_id, $process_id_exception, $element_name){
        $condition = 'process_id!=:process_id AND element_name=:element_name AND status=:status';
        $params = array(
            ':process_id' => $process_id,
            ':element_name' => $element_name,
            ':status' => OperationsModel::STATUS_UNACTIVE,
        );
        if(!empty($process_id_exception) && is_numeric($process_id_exception)){
            $condition = 'process_id!=:process_id AND process_id!=:process_id_exception AND element_name=:element_name AND status=:status';
            $params[':process_id_exception'] = $process_id_exception;
        }

        return
            OperationsModel::model()->findAll(array(
                'condition' => $condition,
                'params' => $params,
            ));
    }


    /**
     * Автоматом запускает процесс привязаный в операторе Начало
     */
    private function runNextProcessFromBegin(){
        $process_id_n = OperationEndModel::getParentElement($this->_operations_model->getSchema(), OperationEndModel::ELEMENT_NEXT_PROCESS)['value'];
        $begin_operation_models = $this->getOperationsForProcess(ProcessModel::getInstance()->process_id, $process_id_n, OperationsModel::ELEMENT_BEGIN);

        if(empty($begin_operation_models)) return;
        $r = false;

        foreach($begin_operation_models as $operation_models){
            $process_id_p = OperationBeginModel::getParentElement($operation_models->getSchema(), OperationBeginModel::ELEMENT_PREVIOUS_PROCESS)['value'];

            if(empty($process_id_p)) continue;

            if($process_id_p != ProcessModel::getInstance()->process_id) continue;
            if($process_id_n == $operation_models->process_id) continue;

            if(!empty($this->_next_process_running_list) && in_array($operation_models->process_id, $this->_next_process_running_list)) continue;


            $run_status = $this->runNextProcess($operation_models->process_id);

            // включаем цикл, если не шаблон
            if($run_status['is_set_run'] == true){
                if($run_status['process_model_base']->this_template === "0" || $run_status['process_model_base']->this_template === null){
                    $r = true;
                } else {
                    $this->_next_process_running_list[] = $operation_models->process_id;
                }
            } else {
                $this->_next_process_running_list[] = $operation_models->process_id;
            }
        }

        if($r==true){
            $this->runNextProcessFromBegin();
        }
    }








    /**
     * Автоматом запускает процесс привязаный в операторе Конец
     */
    private function runNextProcessFromEnd(){
        $process_id = OperationBeginModel::getParentElement($this->_operations_model->getSchema(), OperationEndModel::ELEMENT_NEXT_PROCESS)['value'];
        $run_status = $this->runNextProcess($process_id);


        // обновляем process_id в операторах
        if($run_status['is_set_run'] == true && $run_status['process_model_base']->this_template !== "0" && $run_status['process_model_base']->this_template !== null){
            $operations_schema = $this->_operations_model->getSchema();
             foreach($operations_schema as &$element){
                 if($element['type'] == self::ELEMENT_NEXT_PROCESS){
                     $element['value'] = $run_status['process_model_new']->process_id;
                 }
             }
            $this->_operations_model->setSchema($operations_schema);
            $this->_operations_model->save();
        }
    }





    /**
     * Создает процесс из шаблона (если это шаблон) и запускает его
     */
    private function runNextProcess($process_id){
        if(empty($process_id)){
            return false;
        }

        $result = array(
            'is_set_run' => false,
            'process_model_base' => null,
            'process_model_new' => null,
        );

        // запуск следующего процесса
        $base_process_id = ProcessModel::getInstance()->process_id;

        $process_model = ProcessModel::getInstance($process_id, true);

        if(empty($process_model)){
            ProcessModel::getInstance($base_process_id, true);
            return $result;
        }

        $result['process_model_base'] = clone $process_model;

        $is_template = ($process_model->this_template === "0" ? false : true);

        if(
            $is_template &&
            ParticipantModel::findTypeConstByEntity(\ExtensionCopyModel::MODULE_PROCESS, $process_id) == false
        ){
            $vars = array(
                'process' => array(
                    'b_status' => ProcessModel::B_STATUS_IN_WORK,
                )
            );

            $process_id_new = \Process\models\ProcessModel::getInstance($process_id, true)
                                    ->setVars($vars)
                                    ->createFromTemplate();

            if($process_id_new){
                $process_model_new = \Process\models\ProcessModel::getInstance($process_id_new, true)->setVars($vars);
            }

            if(empty($process_model_new)){
                ProcessModel::getInstance($base_process_id, true);
                return $result;
            }

            $b = $process_model_new->insertBindingObject();

            if($b){
                $process_model_new
                    ->replaceResponsibleByParticipantTypeConst('process');
            }

            $process_model_new
                ->replaceResponsibleByParticipantTypeConst('operations');

            if($b){
                $process_model_new->runProcess();
            }



            $result['process_model_new'] = clone $process_model_new;
            $result['is_set_run'] = true;
        }

        ProcessModel::getInstance($base_process_id, true);

        return $result;
    }








    /**
     * actionAfterSave -  - вызывается после сохранение схемы оператора
     */
    public function actionAfterSave(){
        if($this->_operations_model->getMode(true) != OperationsModel::MODE_CONSTRUCTOR && $this->_operations_model->getStatus() == OperationsModel::STATUS_ACTIVE){
            $this->updateSchedule();
        }

        return $this;
    }






/*
    public function getNextProcessHtml($relate_data_id = null){
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS);

        $params = array(
            'params' =>
                array (
                    'display' => '1',
                    'is_primary' => '0',
                    'edit_view_show' => '1',
                    'c_load_params_btn_display' => '1',
                    'c_load_params_view' => true,
                    'c_db_create' => true,
                    'c_types_list_index' => '1',
                    'с_remove' => true,
                    'name' => 'field8',
                    'relate_module_copy_id' => $extension_copy->copy_id,
                    'relate_module_template' => false,
                    'relate_data_id' => $relate_data_id,
                    'relate_index' => '1',
                    'relate_field' => 'module_title',
                    'relate_type' => NULL,
                    'relate_many_select' => '0',
                    'pk' => false,
                    'type' => 'relate',
                    'type_db' => 'integer',
                    'type_view' => 'edit',
                    'maxLength' => NULL,
                    'minLength' => NULL,
                    'file_types' => NULL,
                    'file_types_mimo' => NULL,
                    'file_thumbs_size' => NULL,
                    'file_max_size' => NULL,
                    'file_min_size' => NULL,
                    'size' => 11,
                    'decimal' => NULL,
                    'required' => '0',
                    'default_value' => NULL,
                    'group_index' => '3',
                    'filter_enabled' => true,
                    'input_attr' => '',
                    'add_zero_value' => '1',
                    'avatar' => true,
                    'rules' => '',
                    'list_view_visible' => '1',
                    'process_view_group' => '0',
                    'list_view_display' => '1',
                    'edit_view_display' => '1',
                    'edit_view_edit' => '1',
                    'inline_edit' => '1',
                ),
        );

        \ActiveRecord::setDinamicParams(array(
                'tableName' => $extension_copy->getTableName(null, false),
                'params' => \Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
            )
        );
        $edit_model = new \EditViewModel();
        $edit_model->setExtensionCopy($extension_copy);

        $content = (new \EditViewBuilder())
            ->setExtensionCopy($extension_copy)
            ->setExtensionData($edit_model)
            ->getEditViewElementEdit($params);

        return $content;
    }

*/





}

