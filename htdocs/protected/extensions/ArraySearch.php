<?php

/**
 * ArraySearch - производит поиск в массиве

 * @autor Alex R.
 */


class ArraySearch {

    const ACTION_FIND       = 'find';
    const ACTION_REPLACE    = 'replace';

    // user params
    private $_search_params = null;
    private $_array = null;

    private $_return_key = null;
    private $_return_index = 1;
    private $_return_only_first = true;

    private $_strict = false;


    //
    private $_action;
    private $_result;



    /**
     * установка параметров
     */


    public function setSearchParams($search_params){
        $this->_search_params = $search_params;
        return $this;
    }

    public function setArray($array){
        $this->_array = $array;
        return $this;
    }

    public function setStrict($strict){
        $this->_strict = $strict;
        return $this;
    }

    public function setReturnKey($return_key){
        $this->_return_key = $return_key;
        return $this;
    }

    public function setReturnIndex($return_index){
        $this->_return_index = $return_index;
        return $this;
    }

    public function setReturnOnlyFirst($return_only_first){
        $this->_return_only_first = $return_only_first;
        return $this;
    }


    /**
     * getResult - возвращает результат
     */
    public function getResult(){
        return $this->_result;
    }


    /**
     * find - поиск значения
     */
    public function find(){
        $this->_action = self::ACTION_FIND;
        $this->nextFind($this->_array);

        return $this;
    }


    /**
     * replace - поиск и замена
     */
    public function replace(){
        $this->_action = self::ACTION_REPLACE;
        $this->nextFind($this->_array);

        return $this;
    }




    private function nextFind(&$node){
        if($this->check()){
            $this->runAction($node);
        } else {

        }


    }



    private function check(){



    }



    private function runAction(&$node){
        switch($this->_action){
            case self::ACTION_FIND :
                $this->runActionFind($node);
                break;
            case self::ACTION_REPLACE :
                $this->runActionFind($node);
                break;
        }

        return $this;
    }




    private function runActionFind($node){


    }





    private function runActionReplace($node){
        // ...
    }


}
