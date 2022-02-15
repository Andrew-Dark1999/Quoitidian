<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationTaskBaseModel extends OperationCardModel{

    const ELEMENT_SDM_OPERATION_TASK    = 'sdm_operation_task';     // элемент "Связь с задачей"

    const ELEMENT_EXECUTION_TIME        = 'execution_time';
    const ELEMENT_EXECUTION_TIME_DAY    = 'days';


    const B_STATUS_CONPLETED    = '1';   //Завершена
    const B_STATUS_IN_WORK      = '2';   //В работе
    const B_STATUS_STOPED       = '3';   //Остановлена
    const B_STATUS_CREATED      = '4';   //Создана

    protected $_relate_copy_id = \ExtensionCopyModel::MODULE_TASKS;
    protected $_title;




    protected function setTitle(){
    }


    protected function isSetBStatus($b_status){
        switch($b_status){
            case self::B_STATUS_CONPLETED:
            case self::B_STATUS_IN_WORK:
            case self::B_STATUS_STOPED:
            case self::B_STATUS_CREATED:
                return true;
        }

        return false;
    }




    /**
     * Возвращает ІД связаной задачи
     */
    protected function getRelateIdCardFromSchema(){
        $schema_operator = $this->_operations_model->getSchema();
        $element = SchemaModel::getOperationElementFromSchema($schema_operator, self::ELEMENT_SDM_OPERATION_TASK);
        if(empty($element) || !empty($element[self::ELEMENT_SDM_OPERATION_TASK])) return;

        if(empty($element['value'])) return;

        $relate_model = OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $element['value']);
        if(empty($relate_model)) return;

        $operation_model = OperationsModel::getChildrenModel($relate_model->element_name);

        $relate_task_id = $operation_model->getIdCardFromSchema($relate_model->getSchema());

        if(empty($relate_task_id)) return;

        return $relate_task_id;
    }




    /**
     * getParticipantResponsible - возвращает ответсвенного в операторе
     */
    public function getParticipantResponsible(){
        $criteria = new \CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id AND data_id=:data_id AND responsible = "1"');
        $criteria->params = [
            ':copy_id' => $this->getOperationsModel()->copy_id,
            ':data_id' => $this->getOperationsModel()->card_id,
        ];

        $participant_model = \ParticipantModel::model()->find($criteria);

        return $participant_model;
    }






    public function getDateEnding(){
        if($this->getStatus() != OperationsModel::STATUS_UNACTIVE){
            return $this->getBDateEnding();
        }

        $parent_date_ending = $this->_operations_model->parentOperationsMaxDateEnding();
        $date_ending = $this->calculateBDateEnding($parent_date_ending);

        return $date_ending;
    }





    public function getTitle(){
        $edit_view_model = $this->getEditViewModel();

        if($edit_view_model == false){
            return $this->_title;
        }

        return $edit_view_model->module_title;
    }






    /*************************************
     * ACTIONS
     *************************************/




    /**
     * actionAddNewOperationByDefault
     */
    public function actionAddNewOperationByDefault($vars = null){
        $this->editViewSaveDefault($vars);
        $this->getOperationsModel()->save();

        return $this;
    }


    

    /**
     * actionDelete - удаляет оператор из БД
     */
    public function actionDelete(){
        $copy_id = $this->getRelateCopyId();
        $card_id = $this->getIdCardFromSchema();

        $result = $this->_operations_model->delete();

        if($result && $copy_id && $card_id){
            $make_loggin = false;
            if($this->_operations_model->getStatus() != OperationsModel::STATUS_UNACTIVE){
                $make_loggin = true;
            }

            \EditViewDeleteModel::getInstance()
                    ->setDeleteTasksAll(true)
                    ->setMakeLoggin($make_loggin)
                    ->prepare($copy_id, array($card_id))
                    ->delete();
        }

        return $result;
    }




    /**
     * actionCloneDataAfterSave - переносит карточку в новый процесс
     */
    public function actionCloneDataAfterSave($vars = null){
        $module_tables = \ModuleTablesModel::getRelateModel(\ExtensionCopyModel::MODULE_TASKS, \ExtensionCopyModel::MODULE_PROCESS, [\ModuleTablesModel::TYPE_RELATE_MODULE_ONE, \ModuleTablesModel::TYPE_RELATE_MODULE_MANY]);
        if($module_tables == false) return;

        $operations_models = OperationsModel::model()->findAll('process_id=' . $vars['process_id_new'] . ' AND element_name in ("'.OperationsModel::ELEMENT_TASK.'", "'.OperationsModel::ELEMENT_AGREETMENT.'")');
        if($operations_models == false) return;

        foreach($operations_models as $operations_model){
            if($operations_model->card_id == false) continue;
            \DataModel::getInstance()->Update('{{' . $module_tables['table_name'] . '}}', [$module_tables->relate_field_name => $vars['process_id_new']], $module_tables->parent_field_name . '=' . $operations_model->card_id);
        }
    }








}

