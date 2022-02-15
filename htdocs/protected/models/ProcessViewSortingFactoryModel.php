<?php

abstract class ProcessViewSortingFactoryModel{

    protected $_table_name;

    protected $_insert_values = array();
    protected $_insert_values_count = 0;
    protected $_insert_queries = array();
    protected $_insert_last_sort;

    protected $_error = false;
    protected $_messages = [];



    abstract protected function insertPrepareValueAndAdd(array $data);

    abstract protected function insertPrepareQuery($length = null);

    abstract protected function getLastSort();



    public function setGlobalVars(array $vars){
        if(!$vars) return $this;

        foreach($vars as $property_name => $var){
            if(property_exists($this, $property_name)){
                $this->{$property_name} = $var;
            }
        }

        return $this;
    }




    public function getTableName($add_prefix = true){
        if($add_prefix){
            return '{{' . $this->_table_name . '}}';
        } else {
            return $this->_table_name;
        }
    }




    protected function insertValueAdd($values){
        $this->_insert_values[] = $values;
        $this->_insert_values_count++;

        return $this;
    }




    protected function getInsertNextSort(){
        if($this->_insert_last_sort === null){
            $this->_insert_last_sort = $this->getLastSort();
        }

        $this->_insert_last_sort++;

        return $this->_insert_last_sort;
    }





    public function insertAllToDB($length = null){
        $this->insertPrepareQuery($length);

        if(!$this->_insert_queries){
            return false;
        }

        $query = implode(' ', $this->_insert_queries);
        (new \DataModel())->setText($query)->execute();

        $this->_insert_queries = array();

        return true;
    }




    public function getLastInsertId(){
        $id = null;

        $sql = 'SELECT LAST_INSERT_ID()';
        $id = \DataModel::getInstance()->setText($sql)->findScalar();
        if(empty($id)){
            return;
        }

        return $id;
    }




    public function beInsertValues(){
        return (bool)($this->_insert_values_count);
    }




    protected function setError($error){
        $this->_error = $error;

        return $this;
    }


    protected function addErrorMessage($message, $params = array()){
        $this->_messages[] = Yii::t('messages', $message, $params);
        $this->_error = true;

        return $this;
    }



    protected function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        return array(
                'status' => $this->getStatus(),
                'messages' => \Validate::getInstance()->addValidateResult('e', $this->_messages)->getValidateResult(),
        );
    }



}
