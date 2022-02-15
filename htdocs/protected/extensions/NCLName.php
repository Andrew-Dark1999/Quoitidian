<?php

class NCLName {

    private $_object;

    public function __construct(){
        
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import('ext.NameCaseLib.*', true);
        
        require_once('NCL.NameCase.ru.php');
        
        $this->_object = new NCLNameCaseRu();
        
        spl_autoload_register(array('YiiBase','autoload'));

        
    }


    public static function getInstance(){
        return new self();
    }

   
   /**
    *   Получаем данные в необходимом падеже
    */
    public function get($fio, $padej, $sex){

        return($this->_object->q($fio, $padej, $sex));

    }
    
   


}




