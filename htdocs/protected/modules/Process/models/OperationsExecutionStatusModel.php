<?php
/**
 * OperationsPrepareModel widget
 * @author Alex R.
 */

namespace Process\models;

class OperationsExecutionStatusModel{

    private static $_instance;

    private $_schema;
    private $_operations = array();


    /**
     * только один экзепляр класса, если $new_instance = false
     */
    public static function getInstance($new_instance = false){
        if(static::$_instance === null){
            static::$_instance = new static;
            return static::$_instance;
        } else {
            if($new_instance){
                static::$_instance = new static;
            }
            return static::$_instance;
        }
    }



    public function setSchema($schema){
        $this->_schema = $schema;
        return $this;
    }


    public function getSchema(){
        return $this->_schema;
    }



    public function getOperationsModels(){
        $result = array();

        foreach($this->_operations as $operation){
            $result[$operation['operation_schema']['unique_index']] = $operation['model'];
        }

        return $result;
    }



    /**
     * подготовка массива операторов
     */
    private function prepareOperations($operations_schema = null){
        $this->_operations = array();

        if(empty($operations_schema)){
            $schema = $this->getSchema();
            $operations_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('type' => SchemaModel::ELEMENT_TYPE_OPERATION));
            if(empty($operations_schema)) return $this;
        }

        foreach($operations_schema as $operation_schema){
            $this->_operations[$operation_schema['unique_index']] = array(
                                'operation_schema' => $operation_schema,
                                'model' => OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $operation_schema['unique_index'])
                            );
        }

        return $this;
    }







    /**
     * Возвращает схему оператора исходя из его названия
     */
    private function getOperationSchemaOfName($operation_name){
        foreach($this->_operations as $operation){
            if($operation['operation_schema']['name'] == $operation_name){
                return $operation;
            }
        }
    }






    /**
     * Старт проверки операторов
     */
    public function run(){
        $this->prepareOperations();
        $this->checkRun();

        return $this;
    }




    /**
     * Старт проверки операторов
     */
    private function checkRun(){
        $begin_schema = $this->getOperationSchemaOfName(OperationsModel::ELEMENT_BEGIN);
        $this->check($begin_schema, null);
    }


    /**
     * Проверка оператора
     */
    private function check($operation_params){
        $operation_params['model']->checkExecution();

        $unique_index_child = $this->getUniqueIndexChild($operation_params['operation_schema']);
        $this->checkNext($unique_index_child);
    }




    /**
     * Проверка следующего оператора
     */
    private function checkNext(array $unique_index_child){
        if(empty($unique_index_child)) return $this;

        foreach($unique_index_child as $item){
            if(empty($item['unique_index'])) continue;
            $this->check($this->_operations[$item['unique_index']]);
        }
    }




    /**
     * Возвращает все unique_index подчиненных операторов
     */
    private function getUniqueIndexChild($operation_params){
        $result = array();
        if(array_key_exists('arrows', $operation_params)){
            foreach($operation_params['arrows'] as $arrow){
                if(empty($arrow['unique_index'])) continue;
                $result[] = array('unique_index' => $arrow['unique_index'], 'status' => $arrow['status']);
            }
        }
        return $result;
    }



}
