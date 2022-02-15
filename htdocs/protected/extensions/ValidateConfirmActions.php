<?php 
/**
*    ValidateConfirmActions
*    @author Alex R.
*/ 

class ValidateConfirmActions{
           
    const ACTION_SUB_MODULE_EDIT_VIEW_CREATE                = 100;
    const ACTION_SUB_MODULE_EDIT_VIEW_EDIT                  = 101;
    const ACTION_SUB_MODULE_EDIT_VIEW_SELECT                = 102;
    const ACTION_SUB_MODULE_EDIT_VIEW_DELETE                = 103;
    const ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE          = 104;
    const ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE      = 105;
    const ACTION_SUB_MODULE_EDIT_VIEW_CREATE_SELECT         = 106;
    const ACTION_MODULE_DELETE_TEMPLATES                    = 107;
    const ACTION_SUB_MODULE_TEMPLATE_REMOVE                 = 108;
    const ACTION_RELATE_CHENGED_SDM                         = 109;
    //const ACTION_PROCESS_OBJECT_INSTANCE                  = 110;
    const ACTION_PROCESS_BO_CLEAR                           = 111;


    
    
    static $_set_action = false; 
    static $_set_params = false;
    
    static $_code_action = array();
    static $_params = array();
    
    
    public function __construct(){
        $this->setCodeAction();
        $this->setParams();
    }
    
    
    
    public static function getInstance(){
        return new static();
    } 
    
    
    
    private function setCodeAction(){
        if(static::$_set_action) return;
        static::$_set_action = true;
        
        if(!isset($_POST['confirm_code_action']) || empty($_POST['confirm_code_action'])) return;

        static::$_code_action = $_POST['confirm_code_action'];
    }        



    private function setParams(){
        if(static::$_set_params) return;
        static::$_set_params = true;
        
        if(!isset($_POST['confirm_params']) || empty($_POST['confirm_params'])) return;

        static::$_params = $_POST['confirm_params'];
    }        



    protected function getParams($code_action){
        if(empty(static::$_params)) return false;
        foreach(static::$_params as $param){
            if(isset($param[$code_action])) return $param[$code_action]; 
        }
        return false;
    }

   
    
    public static function isCodeAction($code_action){
        if(!empty(static::$_code_action) && in_array($code_action, static::$_code_action))
            return true;

        return false; 
    }
    
    
    /**
     * исполняем действие
     */
    public function runAction($code_action){
        if(empty(static::$_code_action) || !in_array($code_action, static::$_code_action)) return;
        
        switch($code_action){
            case static::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE :
                $this->action104();
                break;
            case static::ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE :
                $this->action105();
                break;
            case static::ACTION_MODULE_DELETE_TEMPLATES :
                $this->action107();
                break;
            case static::ACTION_PROCESS_BO_CLEAR :
                $this->action111();
                break;


        }
    }
    
    
    
    /**
     * удаляем связи между модулями, если был изменен первичный модуль 
     */
    public function action104(){
        $params = $this->getParams(static::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE);
        if($params === false) return;
        
        foreach($params['relate_copy_id'] as $relate_copy_id){
            $relate_table = ModuleTablesModel::model()->find(array(
                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                            'params' => array(
                                                            ':copy_id' => $params['primary_copy_id'],
                                                            ':relate_copy_id' => $relate_copy_id)));
                                                            
            DataModel::getInstance()->setText('TRUNCATE TABLE {{' . $relate_table->table_name . '}}')->execute();
        }
    }

   
  
       
    
    
    /**
     * удаляем связи между модулями...
     */
    public function action105(){
        $params = $this->getParams(static::ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE);
        if($params === false) return;
        
        
        foreach($params['relate_copy_id'] as $relate_copy_id){
            $relate_table = ModuleTablesModel::model()->find(array(
                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                            'params' => array(
                                                            ':copy_id' => $params['parent_copy_id'],
                                                            ':relate_copy_id' => $relate_copy_id)));
            if(!empty($relate_table))                                                 
                DataModel::getInstance()->setText('TRUNCATE TABLE {{' . $relate_table->table_name . '}}')->execute();
        }
    }




    /**
     * удаляем шаблоны из модуля
     */
    public function action107(){
        $params = $this->getParams(static::ACTION_MODULE_DELETE_TEMPLATES);
        if($params === false) return;


        if(empty($params['copy_id'])) return;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($params['copy_id']);
        if($extension_copy == false) return;

        $criteria = new CDbCriteria();
        $criteria->addCondition('this_template=:this_template');
        $criteria->params = [':this_template' => "1"];

        $result = EditViewDeleteModel::getInstance()
            ->setThisTemplate(EditViewModel::THIS_TEMPLATE_TEMPLATE)
            ->setCheckAdvancedAccess(false)
            ->setCriteria($criteria)
            ->prepare($params['copy_id'], 'all')
            ->delete()
            ->getResult();


        return $result;
    }




    /**
     * удаляем copy_id модуля из {{process}}, связаного как модуля-обьекта
     */
    public function action111(){
        $params = $this->getParams(static::ACTION_PROCESS_BO_CLEAR);
        if($params === false) return;

        \DataModel::getInstance()->Update('{{process}}', array('related_module' =>null), 'related_module=' . $params['copy_id']);
    }


    
}

