<?php
/**
* NotificationModule - модуль Оповещение
* @author Alex R.
* @version 1.0
*/ 

class NotificationModule extends Module{


    protected $_destroy = false;
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'notification';
    public $db_set_access = '1';



    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);
    }


    public function setModuleName(){
        $this->_moduleName = 'Notification';
    }

    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    }

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Notification');
    }

       


}    
