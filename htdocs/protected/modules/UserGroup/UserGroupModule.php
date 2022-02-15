<?php
/**
* UserGroupModule - модуль групп пользователей  
* @author Alex R.
* @version 1.0
*/ 

class UserGroupModule extends Module
{   
    protected $_prefixName = 'groups';
    
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'user_group';
    public $menu = 'main_left';
    public $menu_icon_class = 'fa-group';
    public $db_set_access = '0';
       
   
   
    public function setModuleName(){
        $this->_moduleName = 'UserGroup';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Groups');
    } 
    

    public function getSchemaFatureDefault(){
        $schema = 
            array(
                Schema::getInstance()->generateDefaultSchema(
                        array(
                            'block' =>
                                array(
                                    'type' => 'block',
                                    'params' => array(
                                        'title' => Yii::t('base', 'New block'),
                                        'header_hidden' => true,
                                        'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000)),
                                    ),
                                    'elements' => array(
                                        array(
                                            'block_panel' => array(
                                                array(
                                                    'type' => 'block_panel_contact',
                                                    'params' => array(
                                                        'make' => false,
                                                    ),
                                                    'elements' => array(),
                                                ),
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 1),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Name'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'name',
                                                                                        'type' => 'string',
                                                                                        'required' => true,
                                                                                    ),
                                                                                ),
                                                                            ),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),                                                        
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            )
            )
        );
        return $schema;
    }
    


}    
