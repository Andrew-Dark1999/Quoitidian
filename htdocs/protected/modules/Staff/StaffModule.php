<?php
/**
* StaffModule - модуль пользователей  
* @author Alex R.
* @version 1.0
*/ 

class StaffModule extends Module
{   
    protected $_prefixName = 'users';
    
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'users';
    public $db_set_access = '1';
 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);
        
        Yii::import('Staff.components.*');
        Yii::import('Staff.models.*');
        Yii::import('Staff.extensions.*');
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Staff';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Employees');
    } 
    
    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('relate_this'));
    }




    /**
     * устанавливает елементы меню для кнопки Дествия в ListVieww
     */
    public function initListViewBtnActionList(){
        parent::initListViewBtnActionList();
        $buttons = $this->list_view_btn_actions;
        unset($buttons['copy']);
        $this->list_view_btn_actions = $buttons;

        return $this;
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
                                        'block_panel_contact_exists' => true,
                                    ),
                                    'elements' => array(
                                        array(
                                            'block_panel' => array(
                                                array(
                                                    'type' => 'block_panel_contact',
                                                    'params' => array(
                                                        'make' => true,
                                                    ),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'block_field_type_contact',
                                                            'params' => array(
                                                                'count_edit_hidden' => 3,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => 'Phone',
                                                                        'name' => 'phone',
                                                                        'destroy' => false,
                                                                    )
                                                                ),
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => 'Mobile',
                                                                        'name' => 'mobile',
                                                                        'destroy' => false,
                                                                    )
                                                                ),
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => 'Skype',
                                                                        'name' => 'skype',
                                                                        'destroy' => false,
                                                                    )
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),                                    
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 3),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'process_view_group' => true,
                                                                'c_count_select_fields_display' => false, 
                                                                'c_list_view_display' => false,  
                                                                'c_process_view_group_display' => false,
                                                                'edit_view_show' => false,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Full name'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 3),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'is_primary' => true,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                                                        'c_load_params_btn_display' => false,
                                                                                        'name' => 'sur_name',
                                                                                        'type' => 'string',
                                                                                        'group_index' => 0,
                                                                                        'avatar' => false,
                                                                                    ),
                                                                                ),
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'first_name',
                                                                                        'type' => 'string',
                                                                                        'is_primary' => true,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                                                        'c_load_params_btn_display' => false,                                                                                        
                                                                                    ),
                                                                                ),
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'is_primary' => true,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                                                        'c_load_params_btn_display' => false,                                                                                        
                                                                                        'name' => 'father_name',
                                                                                        'type' => 'string',
                                                                                    ),
                                                                                ),
                                                                            ),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Email'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'email',
                                                                                        'type' => 'string',
                                                                                    ),
                                                                                ),
                                                                            ),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),  
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Leader'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'leader',
                                                                                        'type' => 'relate_this',
                                                                                        'relate_module_copy_id' => 1,
                                                                                        'relate_field' => 'sur_name,first_name,father_name',
                                                                                         
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
