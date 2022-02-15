<?php
/**
* BaseModule - Базовый (стандартный) модуль  
* @author Alex R.
* @version 1.0
*/ 

class BaseModule extends Module{
    public $menu = 'main_top';


    public function setModuleName(){
        $this->_moduleName = 'Base';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t('base', 'New module');
    } 

       

    public function getSchemaFatureDefault(){
        $schema =
            array_merge(
            array(
                Schema::getInstance()->generateDefaultSchema(
                        array(
                            'block' => array(
                                'type' => 'block',
                                'params' => array(
                                    'title' => Yii::t('base', 'New button block'),
                                    'header_hidden' => true,
                                    'border_top' => false,
                                    'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000) . 'button_block'),
                                    'chevron_down' => false,
                                ),
                                'elements' => array(
                                    array(
                                        'block_button' => array(
                                            array(
                                                'type' => 'block_button',
                                                'elements' => array(),
                                            ),
                                        ),
                                    ),
                                )
                            )
                        )
                )
            ),

            array(
                Schema::getInstance()->generateDefaultSchema(
                        array(
                            'block' =>
                                array(
                                    'type' => 'block',
                                    'params' => array(
                                        'title' => Yii::t('base', 'New hidden block'),
                                        'header_hidden' => true,
                                        'border_top' => false,
                                        'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000) . 'edit_block1'),
                                        'destroy' => false,
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
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => Yii::t('base', 'Phone'),
                                                                        'name' => 'ehc_field1'
                                                                    )
                                                                ),
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => Yii::t('base', 'Mobile'),
                                                                        'name' => 'ehc_field2'
                                                                    )
                                                                ),
                                                                array(
                                                                    'type' => 'edit_hidden',
                                                                    'params' => array(
                                                                        'title' => Yii::t('base', 'Email'),
                                                                        'name' => 'ehc_field3'
                                                                    )
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 1),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'process_view_group' => false,
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
                                                                            'params' => array('title' => Yii::t('base', 'Name')),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'is_primary' => true,
                                                                                        'edit_view_show' => false,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                                                        'c_load_params_btn_display' => false,
                                                                                        'name' => 'module_title',
                                                                                        'type' => 'string',
                                                                                        'filter_' => 'group1',
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
