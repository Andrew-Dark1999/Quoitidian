<?php
/**
 * @author Alex R.
 */

namespace Process\models;


use Process\components\OperationModel;

class OperationConditionModel extends OperationChangeElementModel{


    protected $_is_possibly_bo = true;


    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Condition');
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

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
            }


            // запуск оператора - простой...
            $this->checkDataRecord();

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_DONE);
            }
        }


        return $this;
    }






    /**
     * getBuildedParamsContent - собирает контент
     */
    public function getBuildedParamsContent($schema = null){
        if($schema === null){
            if(empty($this->_operations_model)) return;
            $schema = $this->_operations_model->getSchema();
        }

        if(empty($schema)) return;

        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);

        $content = '';
        foreach($schema as $element_schema){
            $content.= $this->getElementHtml($element_schema);
        }

        return $content;
    }






    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    protected function prepareBaseEntities($schema){
        parent::prepareBaseEntities($schema);

        return $this;
    }










    /**
     * Возвращает список значений списка поля типа Select
     */
    public function getValueSelectList(){
        $result = array();

        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false || $this->_active_field_name == false) return $result;

        if($this->_active_field_type != \Fields::MFT_SELECT) return $result;

        $data_model = new \DataModel();
        $data_model
            ->setSelect(array($this->_active_field_name . '_id', $this->_active_field_name . '_title'))
            ->setFrom($extension_copy->getTableName($this->_active_field_name));

        $data = $data_model->findAll();

        if(empty($data)) return $result;
        foreach($data as $row){
            $result[$row[$this->_active_field_name . '_id']] = $row[$this->_active_field_name . '_title'];
        }

        return $result;
    }




    /**
     * Возвращает список значений списка поля типа СДМ
     */
    public function getValueSdmList(){
        $result = array();

        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false || $this->_active_field_name == false) return $result;

        if(!in_array($this->_active_field_type, array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_THIS, \Fields::MFT_RELATE_STRING))) return $result;

        $data_model = new \DataModel();
        $data_model
            ->setSelect(array($this->_active_field_name . '_id', $this->_active_field_name . '_title'))
            ->setFrom($extension_copy->getTableName($this->_active_field_name));

        $data = $data_model->findAll();

        if(empty($data)) return $result;
        foreach($data as $row){
            $result[$row[$this->_active_field_name . '_id']] = $row[$this->_active_field_name . '_title'];
        }

        return $result;
    }







    /**
     * issetFieldSelectValue - Проверяет наличие значения в поле типа select
     */
    public function issetFieldSelectValue($id){
        $result = false;

        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false || $this->_active_field_name == false) return $result;

        if($this->_active_field_type != 'select') return $result;

        $data_model = new \DataModel();
        $data_model
            ->setFrom($extension_copy->getTableName($this->_active_field_name))
            ->setWhere($this->_active_field_name . '_id=:id', array(':id' => $id));
        $count = $data_model->findCount();

        if($count > 0) $result = true;

        return $result;
    }












    /**
     * checkDataRecord - проверка выполнения оператора Запись данных
     */
    private function checkDataRecord(){
        $schema = $this->_operations_model->getSchema();
        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);
        $this->_operations_model->setSchema($schema)->save();

        $extension_copy = $this->getExtensionCopy();
        $edit_model = null;

        if(!empty($this->_active_card_id) && !empty($extension_copy) && !empty($this->_active_field_name)){
            $edit_model = $this->getCardData();
        }

        foreach($schema as &$element){
            if(empty($this->_active_card_id) && empty($extension_copy) && empty($this->_active_field_name)){
                $element['arrow_status'] = ArrowModel::STATUS_ACTIVE;
                continue;
            }

            if($edit_model == false && $this->_active_relate_extension_copy){
                $element['arrow_status'] = ArrowModel::STATUS_UNACTIVE;
                continue;
            }

            if(!in_array($element['type'], array(
                                            self::ELEMENT_VALUE_SCALAR,
                                            self::ELEMENT_VALUE_SELECT,
                                            self::ELEMENT_VALUE_DATETIME,
                                            self::ELEMENT_VALUE_RELATE))
            ) continue;

            if($edit_model == false || $edit_model->getIsNewRecord()){
                $element['arrow_status'] = ArrowModel::STATUS_ACTIVE;
                continue;
            }

            $status = $this->checkDirect($element, $edit_model);

            $arrow_status = ArrowModel::STATUS_UNACTIVE;
            if($status){
                $arrow_status = ArrowModel::STATUS_ACTIVE;
            }

            $element['arrow_status'] = $arrow_status;
        }

        $this->_operations_model->setSchema($schema)->save();

        return $this;
    }









    /**
     * getArrowStatus - Возвращает статус ветки
     */
    public function getArrowStatus($index){
        $arrow_status = ArrowModel::STATUS_UNACTIVE;

        $schema = $this->_operations_model->getSchema();
        $conditions = SchemaModel::getInstance()->getElementsFromSchema($schema, array('all_types' => true, 'type' => array(self::ELEMENT_VALUE_SCALAR, self::ELEMENT_VALUE_SELECT, self::ELEMENT_VALUE_RELATE, self::ELEMENT_VALUE_DATETIME)));

        if(empty($conditions)) return $arrow_status;


        if(isset($conditions[$index]['arrow_status'])){
            $arrow_status = $conditions[$index]['arrow_status'];
        }

        return $arrow_status;
    }




    /**
     * actionCloneDataAfterSave - клонирование параметров всех операторов в процессе
     */
    public function actionCloneDataAfterSave($vars = null){
        if(empty(OperationDataRecordModel::$clone_params_replase_list)) return $this;

        $operations_models = OperationsModel::model()->findAll(array(
            'condition' => 'process_id=:process_id AND element_name=:element_name',
            'params' => array(
                ':process_id' => $vars['process_id_new'],
                ':element_name' => OperationsModel::ELEMENT_CONDITION,
            ),
        ));

        if(empty($operations_models)) return $this;

        foreach($operations_models as $operations_model){
            $this->_operations_model = $operations_model;
            $this->updateRecordNameListIdInSchema($operations_model);
        }

        return $this;
    }






    /**
     * updateRecordNameListIdInSchema - заменяем ИД значения параметра на новое
     * @param $replase_list
     */
    public function updateRecordNameListIdInSchema($operations_model){
        $operations_model->refresh();
        $operations_model->refreshMetaData();
        $schema = $operations_model->getSchema(true);
        $changed = false;
        foreach($schema as &$element){
            if($element['type'] != self::ELEMENT_OBJECT_NAME) continue;
            if(empty($element['value'])){
                continue;
            }
            $value = json_decode($element['value'], true);

            if(
                empty($value['type']) ||
                empty($value['data_record_id']) ||
                $value['type'] != OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM ||
                !array_key_exists($value['data_record_id'], OperationDataRecordModel::$clone_params_replase_list)
            ) continue;

            $value['data_record_id'] = OperationDataRecordModel::$clone_params_replase_list[$value['data_record_id']];
            $element['value'] = json_encode($value);
            $changed = true;
            break;
        }
        if($changed == true){
            $operations_model->setSchema($schema);
            $operations_model->save();
        }
    }






    /**
     * thereIsSettedBindingObject - проверяет схему оператора и возвращает результат сравнения связанного объекта
     * @param string|integer $compare_value - величина для сравнения
     */
    /*
    public static function thereIsSettedBindingObject($schema, $compare_value){
        if(is_string($schema)){
            $schema = json_decode($schema, true);
        }

        $model = new self();
        $model->prepareBaseEntities($schema);

        if(
            $model->_active_object_name_type == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
            is_array($model->_active_object_name) && array_key_exists('type', $model->_active_object_name) &&
            $model->_active_object_name['type'] == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
            $model->_active_object_name['copy_id'] == $compare_value
        ){
            return true;
        }

        return false;
    }
    */



}
