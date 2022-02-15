<?php
/**
* FinancesModule - модуль для Финансов
* only for Edinstvo (whole module)
* @author Alex B.
* @version 1.0
*/ 

class FinancesModule extends \Module
{   

    public $auto_table_name = false;
    public static $table_name = 'ms_base_finansy';

 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);

        Yii::import('Finances.components.*');
        Yii::import('Finances.models.*');
        Yii::import('Finances.extensions.*');
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Finances';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Finances');
    } 
    





}    
