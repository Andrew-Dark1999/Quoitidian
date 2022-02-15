<?php
/**
* UsersModule - модуль пользователей  
* @author Alex R.
* @version 1.0
*/ 

class UsersModule extends Module
{   
    protected $_prefixName = 'users';
    protected $_be_parent_module = true;
    
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'users';
    public $menu = 'main_left';
    public $menu_icon_class = 'fa-user';
    public $db_set_access = '0';
    public $menu_list_view = false;
    public $switch_to_pw = false;

 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);
        
        Yii::import('Users.components.*');
        Yii::import('Users.models.*');
        Yii::import('Users.extensions.*');
    }


    public function setModuleName(){
        $this->_moduleName = 'Users';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Users');
    } 
    

    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('relate_this'));
    }



    /**
     * устанавливает елементы меню для кнопки Дествия в ListVieww
     */
    protected function initListViewBtnActionList(){
        parent::initListViewBtnActionList();
        $buttons = $this->list_view_btn_actions;
        unset($buttons['copy']);
        $this->list_view_btn_actions = $buttons;

        return $this;
    }



    /**
     * устанавливает елементы меню для кнопки Инструменты в ListView
     */
    protected function initListViewBtnToolsList(){
        $this->list_view_btn_tools = array(
            'print' => array(
                'class' => 'list_view_btn-print',
                'title' => Yii::t('base', 'Print'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ),
            'export_to_excel' => array(
                'class' => 'list_view_btn-select_export_to_excel',
                'title' => Yii::t('base', 'Export to Excel'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ),
            'save_to_pdf' => array(
                'class' => 'list_view_btn-select_export_to_pdf',
                'title' => Yii::t('base', 'Save to PDF'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ),
        );

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
                                                        'make' => false,
                                                    ),
                                                    'elements' => array(),
                                                ),
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 5),
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
                                                                'list_view_visible' => false,
                                                                'processview_view_display' => false,
                                                                'edit_view_display' => false,
                                                                'inline_edit' => false,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Password'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'password',
                                                                                        'type' => 'string',
                                                                                        'input_attr' => '{"type":"password"}', 
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
                                                                                        'required' => true,
                                                                                        'input_attr' => '{"type":"email"}',
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
                                                                            'params' => array('title' => 'Role'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'user_roles',
                                                                                        'type' => 'relate',
                                                                                        'required' => true,
                                                                                        'relate_module_copy_id' => ExtensionCopyModel::MODULE_ROLES,// звязаний модуль (для типов: sub_module, relate
                                                                                        'relate_field' => 'module_title',         // название поля для связи Один-ко-многим  
                                                                                        'default_value' => 1,
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
                                                                                        'relate_module_copy_id' => ExtensionCopyModel::MODULE_STAFF,
                                                                                        'relate_field' => 'sur_name,first_name,father_name',
                                                                                         
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
                                                                            'params' => array('title' => 'Active'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'active',
                                                                                        'type' => 'logical',
                                                                                        'required' => true,
                                                                                        'default_value' => 1,
                                                                                        'add_zero_value' => false,
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
