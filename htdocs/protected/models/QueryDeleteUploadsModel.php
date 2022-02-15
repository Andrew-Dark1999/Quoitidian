<?php
/**
 * class QueryDeleteUploadsModel
 *
 * @author Alex R.
 */


class QueryDeleteUploadsModel {

    private $_file_list = array();
    private $_count_files = 0;
    private $_params_are_set = false;




    public function setAllParams($params){
        if($this->_params_are_set) return;

        if(empty($params)) return;
        foreach($params as $property => $param){
            $property_name = '_' . $property;
            if(property_exists('QueryDeleteDataModel', $property_name)){
                $this->{$property_name} = $params;
            }
        }
        $this->_params_are_set = true;

        return $this;
    }


    public function appendFile($values){
        $this->_file_list[] = array(
            'fp' => $values['file_path'],
            'fn' => $values['file_name'],
        );
        $this->_count_files++;

        return $this;
    }


    public function getFilesList(){
        return $this->_file_list;
    }



    public function getCountFilesList(){
        return $this->_count_files;
    }


    public function clearFilesList(){
        $this->_file_list = array();
        $this->_count_files = 0;

        return $this;
    }



}
