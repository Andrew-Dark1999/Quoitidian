<?php
/**
* RolesModule - модуль прав доступа  
* @author Alex R.
* @version 1.0
*/ 

class RolesModule extends Module
{   
    protected $_prefixName = 'roles';
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'roles';
    public $menu = 'main_left';
    public $menu_icon_class = 'fa-arrow-right';
    public $db_set_access = '0';
    public $menu_list_view = false;
    public $switch_to_pw = false;
   
   
    public function setModuleName(){
        $this->_moduleName = 'Roles';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Roles');
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
            array_merge(
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
                                                    'params' => array('count_panels' => 2),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'c_count_select_fields_display' => false, 
                                                                'c_list_view_display' => false,  
                                                                'c_process_view_group_display' => false,
                                                                'destroy' => false,
                                                                'edit_view_edit' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Title'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'is_primary' => true,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                                                        'c_load_params_btn_display' => false,
                                                                                        'name' => 'module_title',
                                                                                        'type' => 'string',
                                                                                        'group_index' => 0,
                                                                                        'avatar' => false,
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
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'Description'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'description',
                                                                                        'type' => 'string',
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
            )    
        );
        
      
        return $schema;
    }
    


}    
