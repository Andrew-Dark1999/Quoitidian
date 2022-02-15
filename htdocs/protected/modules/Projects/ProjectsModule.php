<?php
/**
* TasksModule - модуль проектов
* @author Alex R.
* @version 1.0
*/ 

class ProjectsModule extends Module
{   

    protected $_destroy = false;
    protected $_auto_show_child_list_entities_pf = true;
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'projects';

    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Projects';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 



    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Projects');
    } 




}    
