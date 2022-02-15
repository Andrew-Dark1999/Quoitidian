<?php
/**
* PermissionModule - модуль прав доступа  
* @author Alex R.
* @version 1.0
*/ 

class PermissionModule extends Module
{   
    protected $_prefixName = 'permission';
    protected $_be_parent_module = true;

    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'permission';
    public $menu = '';
    public $menu_icon_class = 'fa-arrow-right';
    public $db_set_access = '0';
    public $menu_list_view = false;
    
   
   
    public function setModuleName(){
        $this->_moduleName = 'Permission';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Permissions');
    } 
    
    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('access', 'permission'));
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
                                                    'params' => array('count_panels' => 7),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'list_view_display' => false,
                                                                'edit_view_display' => false,
                                                                'inline_edit' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'access_id'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'access_id',
                                                                                        'type' => 'numeric',
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
                                                                'list_view_display' => false,
                                                                'edit_view_display' => false,
                                                                'inline_edit' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => 'access_id_type'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'access_id_type',
                                                                                        'type' => 'numeric',
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
                                                                            'params' => array('title' => 'View'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'rule_view',
                                                                                        'type' => 'select',
                                                                                        'required' => true,
                                                                                        'values' => array(1=>'Allowed', 2=>'Prohibited'),
                                                                                        'add_zero_value' => false,
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
                                                                            'params' => array('title' => 'Edit'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'rule_edit',
                                                                                        'type' => 'select',
                                                                                        'required' => true,
                                                                                        'values' => array(1=>'Allowed', 2=>'Prohibited'),
                                                                                        'add_zero_value' => false,
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
                                                                            'params' => array('title' => 'Delete'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'rule_delete',
                                                                                        'type' => 'select',
                                                                                        'required' => true,
                                                                                        'values' => array(1=>'Allowed', 2=>'Prohibited'),
                                                                                        'add_zero_value' => false,
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
                                                                            'params' => array('title' => 'Import'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'rule_import',
                                                                                        'type' => 'select',
                                                                                        'required' => true,
                                                                                        'values' => array(1=>'Allowed', 2=>'Prohibited'),
                                                                                        'add_zero_value' => false,
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
                                                                            'params' => array('title' => 'Export'),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'rule_export',
                                                                                        'type' => 'select',
                                                                                        'required' => true,
                                                                                        'values' => array(1=>'Allowed', 2=>'Prohibited'),
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
