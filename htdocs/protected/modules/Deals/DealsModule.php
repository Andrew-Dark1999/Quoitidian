<?php
/**
* DealsModule - модуль для Сделок
* only for Edinstvo (whole module)
* @author Alex B.
* @version 1.0
*/ 

class DealsModule extends \Module
{   

    public $auto_table_name = false;
    public static $table_name = 'ms_base_sdelkin';

 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);

        Yii::import('Deals.components.*');
        Yii::import('Deals.models.*');
        Yii::import('Deals.extensions.*');
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Deals';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Deals');
    } 
    





}    
