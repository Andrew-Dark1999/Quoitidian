<?php

class ListViewController extends \ListView{


    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'createFromTemplate':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE) ||
                   !Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false) &&
                    !Access::checkAccessDataOnParticipant(\Yii::app()->request->getParam('pci', null), \Yii::app()->request->getParam('pdi', null))
                ){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;
        }

        parent::filterCheckAccess($filterChain);
    }





    /**
     * Копирование процесса из шаблона
     */
    public function actionCreateFromTemplate(){
        $validate = new Validate();

        $process = \Yii::app()->request->getParam('process');

        if(empty($process)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                                'status' => 'error',
                                'messages' => $validate->getValidateResultHtml(),
                            ));
        } else {
            $vars = $_POST;
            $vars['process']['rau'] = true;

            $process_model = \Process\models\ProcessModel::getInstance($process['process_id'], true)
                                ->setVars($vars)
                                ->validateBeforeCreateFromTemplate(['checkCreateFromTemplateProcess', 'checkCreateFromTemplateBpmParams']);

            $process_id = $process_model->createFromTemplate();

            if($process_id){
                $process_model = \Process\models\ProcessModel::getInstance($process_id, true)->setVars($vars);
                $b = $process_model->insertBindingObject();

                if($b){
                    $process_model
                        ->replaceResponsibleByParticipantTypeConst('process');
                }

                $process_model
                    ->replaceResponsibleByParticipantTypeConst('operations');

                $process_model->runProcess();
            }

            $result = $process_model->getResult();

            if($result['status'] == false){
                return $this->renderJson(array(
                    'status' => 'error_validate',
                    'messages' => (!empty($result['messages']) ? $result['messages'] : null),
                ));
            } else {
                return $this->renderJson(array(
                    'status' => true,
                    'process_id' => $process_id,
                ));
            }
        }
    }




    /**
     * actionCopy
     */
    public function actionCopy($copy_id){
        $validate = new Validate();

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        if(!empty($_POST['id'])){
            if(!is_array($_POST['id'])){
                $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
                $result = array(
                    'status' => false,
                    'messages' => $validate->getValidateResultHtml(),
                );
            } else {
                $status = true;
                $id_list = array();
                foreach($_POST['id'] as $process_id){
                    $result = EditViewCopyModel::getInstance($extension_copy)
                                                    ->setParentThisTemplate(Yii::app()->request->getParam('pci', null))
                                                    ->copy(array($process_id), $extension_copy, true, null)
                                                    ->getResult();

                    if($result['status'] == false) $status = false;
                    if($result['status'] == true && !empty($result['id'])){
                        $id_list[] = $result['id'][0];
                        // запись связей

                        // для сабмодулей
                        $parent_copy_id = Yii::app()->request->getParam('parent_copy_id');
                        $parent_data_id = Yii::app()->request->getParam('parent_data_id');
                        if(!empty($parent_copy_id)){
                            foreach($result['id'] as $id){
                                $this->createRelateLinks($extension_copy->copy_id, $parent_copy_id, $parent_data_id, $id, 'relate_module_many');
                            }
                        }

                        // для listView, открытых через поле название
                        $parent_copy_id = Yii::app()->request->getParam('pci');
                        $parent_data_id = Yii::app()->request->getParam('pdi');
                        if(!empty($parent_copy_id)){
                            foreach($result['id'] as $id){
                                $this->createRelateLinks($extension_copy->copy_id, $parent_copy_id, $parent_data_id, $id, 'relate_module_one');
                            }
                        }


                        $process_model = \Process\models\ProcessModel::getInstance($result['id'][0]);

                        // copy operations
                        \Process\models\OperationsModel::model()->saveNewOperations($process_id, $result['id'][0], $process_model->getSchema(), true, false);
                        // operations execution status
                        \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus();

                    }
                }
                $result = array(
                    'status' => $status,
                    'id' => $id_list,
                );
            }
        } else {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }
        return $this->renderJson($result);
    }










    /**
     * Удаление данных из модуля
     */
    public function actionDelete($copy_id){
        $validate = new Validate();

        $card_id_list = array();
        if(!empty($_POST['id'])){

            foreach($_POST['id'] as &$id){
                $id = (integer)$id;
            }

            // подготовка задач к удалению
            $operations_models = \Process\models\OperationsModel::model()->findAll(array(
                'condition' => 'process_id in('.implode(',', $_POST['id']).') AND element_name in ("'.\Process\models\OperationsModel::ELEMENT_TASK.'", "'.\Process\models\OperationsModel::ELEMENT_AGREETMENT.'")',
            ));


            if(!empty($operations_models)){
                foreach($operations_models as $operations_model){
                    $task_operation_model = \Process\models\OperationsModel::getChildrenModel($operations_model->element_name);
                    $task_operation_model->setOperationsModel($operations_model);
                    $card_id = $task_operation_model->getIdCardFromSchema();
                    if(!empty($card_id)){
                        if($task_operation_model->getStatus() != \Process\models\OperationsModel::STATUS_UNACTIVE){
                            $card_id_list[true][] = $card_id;
                        } else {
                            $card_id_list[false][] = $card_id;
                        }

                    }
                }
            }

            $result = EditViewDeleteModel::getInstance()
                            ->setThisTemplate(Yii::app()->request->getPost('this_template'))
                            ->prepare($copy_id, $_POST['id'])
                            ->delete()
                            ->getResult();

            if($result['status'] == true && !empty($card_id_list)){
                if(!empty($card_id_list[true])){
                    \EditViewDeleteModel::getInstance()
                        ->setDeleteTasksAll(true)
                        ->prepare(\ExtensionCopyModel::MODULE_TASKS, $card_id_list[true])
                        ->delete();
                }
                if(!empty($card_id_list[false])){
                    \EditViewDeleteModel::getInstance()
                        ->setDeleteTasksAll(true)
                        ->setMakeLoggin(false)
                        ->prepare(\ExtensionCopyModel::MODULE_TASKS, $card_id_list[false])
                        ->delete();
                }
            }

        } else {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }

        return $this->renderJson($result);
    }
}
