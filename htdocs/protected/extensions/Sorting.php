<?php

class Sorting{
    
    public static $params = array();
    
    public static function getInstance(){
        return new self(); 
    }
    
    
    public function getParams(){
        return self::$params;   
    }
    
    
    public function setParamsFromUrl($refresh = false){
        if($refresh){
            self::$params = array();
        }

        if(!isset($_GET['sort'])) return $this;
        $params = json_decode($_GET['sort'], true);

        if(empty($params) || !is_array($params)) return $this;

        foreach($params as $field => $direction)
            self::$params[$field] = ($direction == 'd' ? 'desc' : ($direction == 'a' ? 'asc' : ''));

        return $this;
    }
    
    public function setParams(array $params, $clear = false){
        if($clear){
            self::$params = array();
        }

        if(empty($params)){
            self::$params = array();
            return $this;
        } 

        foreach($params as $field => $direction)
            self::$params[$field] = ($direction == 'd' ? 'desc' : ($direction == 'a' ? 'asc' : 'asc'));

        return $this;
    }


    public function setParamsFromFieldNames(array $field_names){
        if(empty($field_names)){
            self::$params = array();
            return $this;
        } 

        foreach($field_names as $field_name)
            self::$params[$field_name] = 'asc';

        return $this;
    }

    
    public function getParamsToString(){
        $result = array();
        if(!empty(self::$params)){
            foreach(self::$params as $field => $direction){
                $result[] = $field . ' ' . $direction;
            }
            $result = implode(',', $result); 
        } else {
            $result = '';
        }
        return $result;
    }


    public function getParamsWithOriginalDirections(){
        $result = array();
        if(!empty(self::$params)){
            foreach(self::$params as $field => $direction){
                $result[$field] = ($direction == 'desc' ? 'd' : ($direction == 'asc' ? 'a' : 'a'));
            }
        }
        return $result;
    }

    
    public function fieldExists($params, $key){
        if($params === null) $params = self::$params;
        $result = true;
        if(empty($params)) return false;
        $keys = explode(',', $key);
        foreach($keys as $key_value){
            if(!array_key_exists($key_value, $params)){
                $result = false;
                break;
            }
        }
        return $result;
    }
    
    
    public function getParamFieldName($params = null){
        if($params === null) $params = self::$params;        
        $fields = array();
        if(!empty($params))
        foreach($params as $field => $value){
            $fields[] = $field;
        }
        return $fields;
    }
    
    
}
