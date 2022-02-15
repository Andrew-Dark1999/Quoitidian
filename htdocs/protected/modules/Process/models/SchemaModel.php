<?php
/**
 * SchemaModel
 * @author Alex R.
 */

namespace Process\models;

use Process\extensions\ElementMaster\Schema;

class SchemaModel{



    const ELEMENT_TYPE_RESPONSIBLE  = 'responsible';
    const ELEMENT_TYPE_OPERATION    = 'operation';


    private $_schema;
    private $_operations_schema_list = array();


    public static function getInstance(){
        return new static();
    }





    public function setSchema($reload_oparations = true){
        $this->_schema = \Process\models\ProcessModel::getInstance()->getSchema();
        if($reload_oparations){
            $this->reloadOperationStatusForSchema($this->getOperationsModels(null, false));
        }

        return $this;
    }




    public function getSchema($return_json = false, $reload_oparations = true){
        if($this->_schema === null){
            $this->setSchema($reload_oparations);
        }

        if($return_json == true){
            return json_encode($this->_schema);
        } else {
            return $this->_schema;
        }
    }


    /**
     * Обработка схемы: проверка и подготовка статусов параметров
     */
    public function setOperationsExecutionStatus(){
        $execution_status_model = OperationsExecutionStatusModel::getInstance(true)
                                                ->setSchema($this->getSchema())
                                                ->run();

        $this->reloadOperationStatusForSchema($execution_status_model->getOperationsModels());

        return $this;
    }


    /**
     * Обработка схемы (private): установка статусов операторов в схеме
     */
    private function reloadOperationStatusForSchema($operations_models){
        $this->reloadOperationStatus($this->_schema, $operations_models);

        return $this;
    }


    /**
     * Обработка схемы (private): установка статусов операторов в схеме
     */
    private function reloadOperationStatus(&$schema, $operations_models){
        if(!is_array($schema)) return $this;

        if(array_key_exists('elements', $schema) && !empty($schema['elements'])){
            $this->reloadOperationStatus($schema['elements'], $operations_models);
        } elseif(array_key_exists('type', $schema) && $schema['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION){

            $status_o = $operations_models[$schema['unique_index']]->getStatus();

            if(ProcessModel::getInstance()->getBStatus() == ProcessModel::B_STATUS_TERMINATED){
                $status_o = OperationsModel::STATUS_DONE;
            }

            $schema['status'] = $status_o;
            $index = 0;
            foreach($schema['arrows'] as &$arrow){
                $arrow['status'] = $operations_models[$schema['unique_index']]->getArrowStatus($index);
                $index++;
            }

        } elseif(is_array($schema)){
            foreach($schema as &$item){
                $this->reloadOperationStatus($item, $operations_models);
            }
        }
    }




    /**
     * Обработка схемы: установка статусов операторов в схеме
     */
    public function reloadOtherParamsForSchema(){
        $this->reloadOtherParams($this->_schema);

        return $this;
    }


    /**
     * Обработка схемы (private): Обработка схемы: установка статусов операторов в схеме
     */
    private function reloadOtherParams(&$schema){
        if(!is_array($schema)) return $this;

        if(array_key_exists('type', $schema) && $schema['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE){
            if(empty($schema['unique_index'])) $schema['unique_index'] = md5($schema['ug_id'] . $schema['ug_type'] .date_format(date_create(), 'YmdHisu')) . '99'; // можно со временем удалить
            $schema['title'] = (new \Process\models\SchemaModel())->getParticipantTitle($schema['ug_id'], $schema['ug_type']);
        }

        // если Ответственный
        if(array_key_exists('elements', $schema) && !empty($schema['elements'])){
            $this->reloadOtherParams($schema['elements']);

        // если Оператор
        } elseif(array_key_exists('type', $schema) && $schema['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION){
            // begin
            if($schema['name'] == \Process\models\OperationsModel::ELEMENT_BEGIN){
                $schema['title'] = \Yii::t('ProcessModule.base', 'Begin');
            }
            // end
            if($schema['name'] == \Process\models\OperationsModel::ELEMENT_END){
                $schema['title'] = \Yii::t('ProcessModule.base', 'End');
            }

            //$this->refreshOperationTitle($schema);

            //date_ending
            $schema['date_ending'] = null; //OperationsModel::getChildrenModelByUniqueIndex($schema['unique_index'])->getDateEnding();

        // другое..
        } elseif(is_array($schema)){
            foreach($schema as &$element){
                $this->reloadOtherParams($element);
            }
        }
    }




    private function refreshOperationTitle(&$schema){
        if(!in_array($schema['name'], [\Process\models\OperationsModel::ELEMENT_TASK, \Process\models\OperationsModel::ELEMENT_AGREETMENT])){
            return;
        }

        $operation_model = OperationsModel::getChildrenModelByUniqueIndex($schema['unique_index']);

        $schema['title'] = $operation_model->getTitle();
    }




    /**
     * getOperationTitleByUniqueIndex - Ворзвращает название (подпись) оператора
     */
    public function getOperationTitleByUniqueIndex($unique_index){
        $operations_schema = $this->getOperationProcessSchema($unique_index);
        if(empty($operations_schema)){
            return;
        }

        $title = null;

        if(!array_key_exists('title', $operations_schema)){
            $operation_model = OperationsModel::getChildrenModelByUniqueIndex($unique_index);
            if($operation_model){
                return $operation_model->getTitle();
            }
        } else {
            $title = $operations_schema['title'];
        }

        return $title;
    }




    /**
     * getOperationProcessSchema  -возвращает схему процесса оператора по unique_index
     * @param $unique_index
     */
    public function getOperationProcessSchema($unique_index){
        $operations_schema = \Process\extensions\ElementMaster\Schema::getInstance()->getOperations();

        if(empty($operations_schema)) return;

        foreach($operations_schema as $operation){
            if($operation['unique_index'] == $unique_index){
                return $operation;
            }
        }
    }



    /****************************************
     *
     * Update node values in general (process) schema
     *
     ****************************************/

    public function update(array $find_params, $new_values){
        $this->getSchema();

        $this->updateRun($this->_schema, $find_params, $new_values);
        return $this;
    }





    private function updateCheckParams(array $params, $node){
        foreach($params as $key => $value){
            if(!array_key_exists($key, $node)) return false;
            if($node[$key] != $value) return false;
        }
        return true;
    }





    private function updateRun(&$schema_all, array $find_params, array $new_values){
        if($schema_all == false) return;
        foreach($schema_all as &$schema){
            $check = $this->updateCheckParams($find_params, $schema);
            if($check){
                $schema = \Helper::arrayMerge($schema, $new_values);
                return true;
            }

            if(array_key_exists('elements', $schema)){
                $r = $this->updateRun($schema['elements'], $find_params, $new_values);
                if($r == true) return;
            }
        }
     }


    /**
     * Возвращает часть схемы оператор  по его типу
     *
     * @param $find_key
     */
    public static function getOperationElementFromSchema($operation_schema, $type){
        $result = array();
        if(empty($operation_schema)) return $result;
        foreach($operation_schema as $element){
            if(array_key_exists('type', $element) && $element['type'] == $type){
                $result = $element;
                break;
            }
        }

        return $result;
    }




    /**
     * возвращает все елементы в схеме. По дефолту подставляется схема процесса
     */
    public function getElementsFromSchema($schema = null, $find_params = array(), $check_key_only = false, $reload_oparations = false){
        $this->_operations_schema_list = array();
        if($schema === null) $schema = $this->getSchema(false, $reload_oparations); // общая схема процесса

        $this->findElemetsInSchema($schema, $find_params, $check_key_only);

        return $this->_operations_schema_list;
    }





    /**
     * поиск операторов в схеме
     */
    private function findElemetsInSchema($schema, $find_params = array(), $check_key_only = false){
        if(!is_array($schema)) return $this;

        if(array_key_exists('elements', $schema) && !empty($schema['elements'])){
            $this->findElemetsInSchema($schema['elements'], $find_params, $check_key_only);
        } else if($this->findOperationsCheckFindParams($schema, $find_params, $check_key_only)){
            if(array_key_exists('only_first', $find_params) && $find_params['only_first'] == true){
                $this->_operations_schema_list = $schema;
                return $this;
            }elseif(array_key_exists('all_types', $find_params) && $find_params['all_types'] == true){
                $this->_operations_schema_list[] = $schema;
                return $this;
            } else {
                if(array_key_exists('unique_index', $schema)){
                    $this->_operations_schema_list[$schema['unique_index']] = $schema;
                } else {
                    return $this;
                }
            }
        } else if(is_array($schema)){
            foreach($schema as $item){
                $this->findElemetsInSchema($item, $find_params, $check_key_only);
            }
        }

        return $this;
    }



    /**
     * findOperationsCheckFindParams - Проверка дополнительного условия отбора веток схемы
     * @param $schema
     * @param array $find_params
     * @return bool
     */
    private function findOperationsCheckFindParams($schema, $find_params = array(), $check_key_only = false){
        $result = true;
        if(!empty($find_params)){
            foreach($find_params as $key => $value){
                if($key == 'only_first') continue;
                if($key == 'all_types') continue;

                if(array_key_exists($key, $schema)){
                    if($check_key_only == true) return;

                    if(is_array($value)){
                        $i = 0;
                        foreach($value as $item){
                            if($schema[$key] !== $item){
                                $i++;
                            }
                        }
                        if(count($value) == $i){
                            $result = false;
                            break;
                        }
                    } else {
                        if($schema[$key] !== $value){
                            $result = false;
                            break;
                        }
                    }
                } else {
                    $result = false;
                    break;
                }
            }
        }

        return $result;

    }





    /**
     * список моделей операторов
     */
    public function getOperationsModels($operations_schema = null, $reload_oparations = true){
        $result = array();

        if($operations_schema === null){
            $operations_schema = $this->getElementsFromSchema(null, array('type' => SchemaModel::ELEMENT_TYPE_OPERATION), false, $reload_oparations);
        }

        foreach($operations_schema as $operation_schema){
            $result[$operation_schema['unique_index']] = \Process\models\OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $operation_schema['unique_index']);
        }

        return $result;
    }


    /**
     * isSetActiveOperationsInResponsible - возвращает наличие активного оператора для определенного участника
     */
    public function isSetActiveOperationsInResponsible($responsible){
        $operations = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsibleList();
        if(empty($operations)) return false;

        foreach($operations as $unique_index => $resp){
            $operation_model = OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $unique_index);
            if(
                $resp['ug_id'] == $responsible['ug_id'] &&
                $resp['ug_type'] == $responsible['ug_type'] &&
                in_array($operation_model->getStatus(), array(OperationsModel::STATUS_ACTIVE, OperationsModel::STATUS_DONE, OperationsModel::STATUS_PAUSE))
            ){
                return true;
            }
        }

        return false;
    }


    /**
     * getParticipantTitle - Возвыращает название ответвенного
     * @param $ug_id
     * @param $ug_type
     * @return string
     */
    public function getParticipantTitle($ug_id, $ug_type){
        $responsible = \ParticipantModel::model()->getEntityDataByParams($ug_id, $ug_type);
        if($responsible){
            $responsible_title = $responsible['full_name'];

            /*
            if(
                $responsible['ug_id'] == \ParticipantConstModel::TC_RELATE_RESPONSIBLE &&
                $responsible['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_CONST &&
                ProcessModel::getInstance()->related_module == false
            ){
                $responsible_title = \Yii::t('ProcessModule.base', 'Responsible not selected');
            }
            */
        } else {
            $responsible_title = \Yii::t('ProcessModule.base', 'Responsible not selected');
        }

        return $responsible_title;
    }


}
