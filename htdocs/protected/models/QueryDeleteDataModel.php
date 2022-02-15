<?php
/**
 * class QueryDeleteDataModel
 *
 * @author Alex R.
 */


class QueryDeleteDataModel {

    private $_table_name;
    private $_primary_field_name;
    private $_id_list = array();
    private $_condition;
    private $_params;

    private $_count_id = 0;
    private $_params_are_set = false;



    public function getTableName(){
        return $this->_table_name;
    }

    public function getPrimaryFieldName(){
        return $this->_primary_field_name;
    }

    public function getCondition(){
        return $this->_condition;
    }

    public function getParams(){
        return $this->_params;
    }

    public function setAllParams($params){
        if($this->_params_are_set) return;

        if(empty($params)) return;
        foreach($params as $property => $param){
            $property_name = '_' . $property;
            if(property_exists('QueryDeleteDataModel', $property_name)){
                $this->{$property_name} = $param;
            }
        }
        $this->_params_are_set = true;

        return $this;
    }


    public function appendId($id){
        if(empty($id)) return $this;

        if(is_numeric($id)){
            $this->_id_list[] = (integer)$id;
            $this->_count_id++;
        } elseif(is_array($id)){
            foreach($id as $id_v){
                $this->appendId($id_v);
            }
        } elseif(is_string($id)){
            $this->_id_list[] = '"' . $id . '"';
            $this->_count_id++;
        }

        return $this;
    }


    public function getIdList(){
        return $this->_id_list;
    }



    public function getCountIdList(){
        return $this->_count_id;
    }


    public function clearIdList(){
        $this->_id_list = array();
        $this->_count_id = 0;

        return $this;
    }



}
