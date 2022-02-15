<?php
/**
 * ResponsibleBpmRoleCheckWorkingModel - последовательная проверка по запученных операторах процесса и поиск Роли
 * в качестве ответсвенного или проверка на несуществующего ответственного.
 * @author Alex R.
 */

namespace Process\models;


use Process\extensions\ElementMaster\Schema;

class ResponsibleBpmRoleCheckWorkingModel{

    private static $_instance;

    private $_schema;
    private $_operations = array();
    private $_responsible_schema_list = array();


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
        if(empty($operations_schema)){
            $operations_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->getSchema(), array('type' => SchemaModel::ELEMENT_TYPE_OPERATION));
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



    public function getResult(){
        return $this->_responsible_schema_list;
    }



    /**
     * Старт проверки операторов
     */
    public function run(){
        $this->prepareOperations();
        $this->checkRun();
        $this->orderResponsibleSchemaList();

        return $this;
    }


    /**
     * Сортировка ответственных согласно схемы
     */
    private function orderResponsibleSchemaList(){
        if($this->_responsible_schema_list == false) return;

        $sorting_function = function($a, $b){
            $k1 = 0;
            $k2 = 0;

            foreach($this->_schema as $key => $element){
                if($a['ug_id'] == $element['ug_id'] && $a['ug_type'] == $element['ug_type']){
                    $k1 = $key;
                    continue;
                }
                if($b['ug_id'] == $element['ug_id'] && $b['ug_type'] == $element['ug_type']){
                    $k2 = $key;
                    continue;
                }
            }

            if($k1 === $k2) return 0;
            return ($k1 < $k2) ? -1 : 1;
        };

        usort($this->_responsible_schema_list, $sorting_function);
        $this->_responsible_schema_list;
    }

    /**
     * Старт проверки операторов
     */
    private function checkRun(){
        $begin_schema = $this->getOperationSchemaOfName(OperationsModel::ELEMENT_BEGIN);
        $this->check($begin_schema, null);
    }



    /**
     * добавляем роль, что надо заменить на сотрудника
     */
    private function addResponsibleSchemaList($responsible_data, $entity_is_bad = false){
        if(empty($responsible_data) || !is_array($responsible_data)) return;

        // пропускаем, если уже добавлено
        if(!empty($this->_responsible_schema_list)){
            foreach($this->_responsible_schema_list as $item){
                if($item['ug_type'] == $responsible_data['ug_type'] && $item['ug_id'] == $responsible_data['ug_id']) return;
            }
        }

        $responsible_data['entity_is_bad'] = $entity_is_bad; // указывает, що сущности (участника)  не сществует

        $this->_responsible_schema_list[] = $responsible_data;
    }



    /**
     * Проверка оператора
     */
    private function check($operation_params){
        if(empty($operation_params)) return;

        $operation_model = OperationsModel::getChildrenModel($operation_params['model']->element_name)
                                ->setOperationsModel($operation_params['model']);

        if($operation_params['model']->getStatus() != OperationsModel::STATUS_DONE){
            // проверка на существование пользователя или на допуск константы
            if($operation_model->checkIsSetResponsibleUser() == false){
                $this->addResponsibleSchemaList(\Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsible(null, $operation_params['model']->unique_index), true);
            }
            else
            // проверка на роль
            if($operation_model->checkIsResponsibleRole()){
                $this->addResponsibleSchemaList(\Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsible(null, $operation_params['model']->unique_index));
            }


            // проверка статусов оператора для продолжения проверки
            if(!$this->checkStatusChildrenOperation($operation_params)){
                return;
            }
        }

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
            //if($item['status'] != ArrowModel::STATUS_ACTIVE) continue;
            //$operation_params = $this->_operations[$item['unique_index']];
            /*
            if(!in_array($operation_params['operation_schema']['name'],
                        array(
                            \Process\models\OperationsModel::ELEMENT_TASK,
                            \Process\models\OperationsModel::ELEMENT_AGREETMENT,
                            \Process\models\OperationsModel::ELEMENT_DATA_RECORD,
                        ))) continue;
            */
            $this->check($this->_operations[$item['unique_index']]);

        }
    }


    /**
     * Проверяет статус операторов для продолжения проверки
     */
    private function checkStatusChildrenOperation($operation_params){

        //ELEMENT_TASK
        //ELEMENT_AGREETMENT
        //ELEMENT_CONDITION
        if(in_array($operation_params['operation_schema']['name'], array(
            \Process\models\OperationsModel::ELEMENT_TASK,
            \Process\models\OperationsModel::ELEMENT_AGREETMENT,
            \Process\models\OperationsModel::ELEMENT_CONDITION,
            //\Process\models\OperationsModel::ELEMENT_NOTIFICATION,
        ))){
            if($operation_params['model']->getStatus() != OperationsModel::STATUS_DONE){
                return false;
            }
        }


        //ELEMENT_TIMER
        if(in_array($operation_params['operation_schema']['name'], array(
            \Process\models\OperationsModel::ELEMENT_TIMER,
        ))){
            $sot = null;
            $operation_schema  = $operation_params['model']->getSchema(true);
            foreach($operation_schema as $row){
                if($row['type'] == OperationBeginModel::ELEMENT_START_ON_TIME){
                    $sot = $row['value'];
                }
            }

            if($sot == OperationTimerModel::START_ON_TIME_DISABLED){
                return true;
            } else {
                $start_results = (new StartTimeModel())
                                    ->setPreparation(true)
                                    ->setUseVirtualStModel(true)
                                    ->setOperationsModel($operation_params['model'])
                                    ->prepareOperationParams()
                                    ->insertNewSchedules()
                                    ->startSchedule(false)
                                    ->getRunResult(false);

                if(empty($start_results)){
                    return true;
                } else {
                    return (in_array(false, $start_results) ? false : true);
                }
            }
        }

        //ELEMENT_DATA_RECORD
        if(in_array($operation_params['operation_schema']['name'], array(
            \Process\models\OperationsModel::ELEMENT_DATA_RECORD,
        ))){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($operation_params['model']->getSchema(true), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW));
            if($from_schema['value'] == OperationDataRecordModel::ELEMENT_CEV_CALL && $operation_params['model']->getStatus() != OperationsModel::STATUS_DONE){
                return false;
            }
        }


        //ELEMENT_AND
        //ELEMENT_CONDITION
        if(in_array($operation_params['operation_schema']['name'], array(
            \Process\models\OperationsModel::ELEMENT_AND,
        ))){
            // если первый оператор
            if(!empty($operation_params['operation_schema']['helper'])){
                return true;
            }

            // если второй оператор
            foreach($this->_operations as $operation){
                if(!empty($operation['operation_schema']['helper'])){
                    if($operation['operation_schema']['helper'] == $operation_params['operation_schema']['unique_index'] && $operation_params['model']->getStatus() != OperationsModel::STATUS_DONE){
                        return false;
                    }
                }
            }
        }

        return true;
    }




    /**
     * Возвращает все unique_index подчиненных операторов
     */
    private function getUniqueIndexChild($operation_params){
        $result = array();
        if(array_key_exists('arrows', $operation_params)){
            foreach($operation_params['arrows'] as $arrow){
                $result[] = array('unique_index' => $arrow['unique_index'], 'status' => $arrow['status']);
            }
        }
        return $result;
    }


}
