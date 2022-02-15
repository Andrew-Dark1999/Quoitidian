<?php
/**
 * class QueryInsertDataModel
 *
 * @author Alex R.
 */


class QueryInsertDataModel {

    private $_table_name;
    private $_fields = array();
    private $_values = array();
    private $_count_values = 0;

    private $_this_template = false;
    private $_primary_field_name;
    private $_primary_key;



    public static function getInstance(){
        return new self();
    }



    public function setTableName($table_name){
        $this->_table_name = $table_name;
        return $this;
    }


    public function getTableName(){
        return $this->_table_name;
    }


    public function setPrimaryKeyStart($primary_key){
        $this->_primary_key = $primary_key;
        return $this;
    }


    public function setPrimaryKeyNext(){
        $this->_primary_key++;
        return $this;
    }


    public function getPrimaryKey(){
        return $this->_primary_key;
    }


    public function setPrimaryFieldName($primary_field_name){
        $this->_primary_field_name = $primary_field_name;
        return $this;
    }


    public function getPrimaryFieldName(){
        return $this->_primary_field_name;
    }


    public function setFields($fields){
        $this->_fields = $fields;
        return $this;
    }


    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }



    public function getFields($implode = true){
        if($implode){
            return implode(',', $this->_fields);
        } else{
            return $this->_fields;
        }
    }


    /**
     * appendValues
     * @param array $values - array('field_name' => 'value')
     * @return $this
     */
    public function appendValues($values){
        $values_tmp = array();

        if(!empty($this->_primary_field_name)){
            $values[$this->_primary_field_name] = $this->_primary_key;
            $this->setPrimaryKeyNext();
        }

        if(array_key_exists('date_create', $values))    $values['date_create'] = date('Y-m-d H:i:s');
        if(array_key_exists('user_create', $values))    $values['user_create'] = WebUser::getUserId();
        if(array_key_exists('this_template', $values))    $values['this_template'] = (string)(integer)$this->_this_template;

        foreach($this->_fields as $field_name){
            if(is_string($values[$field_name])){
                $values_tmp[] = '"' . $values[$field_name] . '"';
            } elseif(is_null($values[$field_name])){
                $values_tmp[] = 'null';
            } else {
                $values_tmp[] = $values[$field_name];
            }
        }

        $this->_values[] =  '(' . implode(',', $values_tmp) . ')';
        $this->_count_values++;

        return $this;
    }



    public function getValues(){
        return $this->_values;
    }


    public function getCountValues(){
        return $this->_count_values;
    }


    public function clearValues(){
        $this->_values = array();
        $this->_count_values = 0;

        return $this;
    }



}
