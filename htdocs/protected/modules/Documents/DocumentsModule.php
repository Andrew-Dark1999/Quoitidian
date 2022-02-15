<?php

/**
* DocumentsModule - Шаблоны документов
* @author Alex B.
* @version 1.0
*/ 

class DocumentsModule extends \Module{
    public $menu = 'main_top';
    
    public $auto_table_name = false;
    public static $table_name = 'documents_templates';


    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);

        Yii::import('Documents.components.*');
        Yii::import('Documents.models.*');
        Yii::import('Documents.extensions.*'); 
    }
    
    public function setModuleName(){
        $this->_moduleName = 'Documents';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.Base', 'New module');
    } 

    

}    
