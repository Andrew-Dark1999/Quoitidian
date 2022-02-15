<?php

class Filters{
    
    public static $text = null;
    
    public static function getInstance(){
        return new self(); 
    }
    
    
    public function getText(){
        return self::$text;   
    }
    
    
    public function setTextFromUrl(){
        if(isset($_GET['filters']) && is_array($_GET['filters']))
        self::$text = $_GET['filters'];
        
        return $this;
    }
    
    public function isTextEmpty(){
        if(empty(self::$text)) return true;
        return false;
    }
    
}
