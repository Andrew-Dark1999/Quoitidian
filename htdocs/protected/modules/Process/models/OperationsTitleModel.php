<?php
/**
 *  OperationsTitleModel - проверка и обновления названия (подписи) оператора
 */

namespace Process\models;


class OperationsTitleModel
{


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
        if($this->_schema == false){
            return;
        }

        foreach($this->_schema as &$schema_participant){
            $this->processSchemaOperations($schema_participant);
        }
    }


    private function processSchemaOperations(&$schema_participant){
        $operations = &$schema_participant['elements'];
        if($operations == false){
            return;
        }

        foreach($operations as &$schema_operation){
            if($this->skipOperation($schema_operation['name'])){
                continue;
            }

            $title = $this->getTitle($schema_operation);
            if($title === null){
                continue;
            }

            if(!array_key_exists('title', $schema_operation) || $schema_operation['title'] != $title){
                $schema_operation['title'] = $title;
                $this->_is_changed = true;
            }
        }
    }


    private function skipOperation($operation_name){
        return false;
    }



    private function getTitle($schema_operation){
        $operation_model = OperationsModel::getChildrenModelByUniqueIndex($schema_operation['unique_index']);
        if($operation_model == false){
            return;
        }

        return $operation_model->getTitle();

    }




}
