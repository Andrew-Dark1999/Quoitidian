<?php

class Search{
    
    public static $text = null;
    
    public static function getInstance(){
        return new self(); 
    }
    
    
    public function getText(){
        return self::$text;   
    }
    
    
    public function setTextFromUrl(){
        if(!isset($_GET['search'])) return $this;
        self::$text = urldecode($_GET['search']);
        
        return $this;
    }
    

    
}
