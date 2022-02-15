<?php
/**
 *  ParticipantOperationsRemoveModel - проверка отвественных в общей схемы процесса и перенос операторов процесса
 *                                     в опеределеный блок с ответсвенными
 */

namespace Process\models;


use Process\extensions\ElementMaster\Schema;

class ParticipantOperationsRemoveModel{


    private $_schema;
    private $_is_changed = false;


    public function setSchema($schema){
        $this->_schema = $schema;
        return $this;
    }


    public function getSchema(){
        return $this->_schema;
    }


    public function getIsChanged(){
        return $this->_is_changed;
    }


    public function prepare(){
        $this->processSchemaParticipant();

        return $this;
    }



    private function processSchemaParticipant(){
        $schema = $this->_schema;

        if($schema == false){
            return;
        }

        foreach($schema as $schema_participant){
            $this->processSchemaOperations($schema_participant);
        }
    }


    private function processSchemaOperations($schema_participant){
        $operations = $schema_participant['elements'];
        if($operations == false){
            return;
        }

        foreach($operations as $schema_operation){
            if($this->skipOperation($schema_operation['name'])){
                continue;
            }

            $responsible_model = $this->getOperationResponsible($schema_operation);
            if($responsible_model == false){
                continue;
            }

            if($this->checkOperationResponsible($schema_participant, $responsible_model) === false){
                $this->removeOperation($schema_participant, $schema_operation, $responsible_model);
            }
        }
    }


    private function skipOperation($operation_name){
        return !in_array($operation_name, [OperationsModel::ELEMENT_TASK, OperationsModel::ELEMENT_AGREETMENT]);
    }


    private function getOperationResponsible($schema_operation){
        $operations_model = OperationsModel::getChildrenModelByUniqueIndex($schema_operation['unique_index']);
        if($operations_model == false){
            return;
        }

        $responsible_model = $operations_model->getParticipantResponsible();

        return $responsible_model;
    }


    private function checkOperationResponsible($schema_participant, $responsible_model){
        if($responsible_model == false){
            return;
        }
        $b1 = ($schema_participant['ug_id'] == $responsible_model->ug_id ? true : false);
        $b2 = ($schema_participant['ug_type'] == $responsible_model->ug_type ? true : false);

        if($b1 && $b2){
            return true;
        }

        return false;
    }



    private function removeOperation($schema_participant, $schema_operation, $responsible_model){
        $this->_is_changed = true;
        //1
        $this->moveOperationFromSchema($schema_participant, $schema_operation);
        //2
        $this->addOperationFromSchema($schema_operation, $responsible_model);
    }



    private function moveOperationFromSchema($schema_participant, $schema_operation){
        $key = $this->findKeyToResponsibleNode($schema_participant['ug_id'], $schema_participant['ug_type']);
        if($key === null){
            return;
        }

        foreach($this->_schema[$key]['elements'] as $key2 => $element){
            if($element['unique_index'] == $schema_operation['unique_index']){
                unset($this->_schema[$key]['elements'][$key2]);
                sort($this->_schema[$key]['elements']);
                return;
            }
        }
    }


    private function addOperationFromSchema($schema_operation, $responsible_model){
        $key = $this->findKeyToResponsibleNode($responsible_model['ug_id'], $responsible_model['ug_type']);

        if($key !== null){
            $this->_schema[$key]['elements'][] = $schema_operation;
        } else {
            $responsible_node = Schema::getInstance()->getDefaultSchemaResponsible($responsible_model['ug_id'], $responsible_model['ug_type']);
            $responsible_node['elements'][] = $schema_operation;
            $this->_schema[] = $responsible_node;
        }
    }



    private function findKeyToResponsibleNode($ug_id, $ug_type){
        foreach($this->_schema as $key => $node){
            if($node['ug_id'] == $ug_id && $node['ug_type'] == $ug_type){
                return $key;
            }
       }
    }





}
