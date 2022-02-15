<?php
/**
* ParticipantModule - модуль списка участников  
* @author Alex R.
* @version 1.0
*/ 

class ParticipantModule extends Module
{   
    protected $_be_parent_module = true;
    
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'participant';
    public $db_set_access = '0';
    public $menu_list_view = true;
    public $menu = null;
    public $switch_to_pw = false;
    public $list_view_btn_tools = false;

 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);
        
        Yii::import('Participant.models.*');
        Yii::import('Participant.extensions.ElementMaster.*');
        
        unset($this->list_view_btn_actions['copy']);        
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Participant';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 



    // Название модуля
    public function getModuleTitle($add_parent_title = true){
        $module_title = '';
        
        $parent_module_title =  '';
        if(isset($_GET['pci']) && $add_parent_title){
            $extension_copy_pci = ExtensionCopyModel::model()->findByPk($_GET['pci']);
            $parent_module_title = ' ' . Yii::t($this->getModuleName() . 'Module.base', 'from module') . ' "' . $extension_copy_pci->getModule()->getModuleTitle(false) . '"';    
        }

        if(!empty($this->extensionCopy))
            return Yii::t($this->getModuleName() . 'Module.base', $this->extensionCopy->title) . $parent_module_title;
        else 
            return $this->getModuleTitleDefault();
        
        return $module_title;        
    }




    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Participant');
    } 
    

    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('relate_participant'));
    }

       
    public function getSchema($extension_copy){
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
                                        'border_top' => false,
                                        'chevron_down' => true,
                                        'unique_index' => md5(date('YmdHis'). mt_rand(1, 1000)),
                                    ),
                                    'elements' => array(
                                        array(
                                            'block_panel' => array(
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 2),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'destroy' => false,
                                                                'list_view_display' => true,
                                                                'edit_view_display' => true,
                                                                'edit_view_edit' => false,
                                                                'inline_edit' => false
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => Yii::t($this->getModuleName() . 'Module.base', 'Participants')),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'avatar' => true,
                                                                                        'is_primary' => true,
                                                                                        'edit_view_show' => true,
                                                                                        'name' => 'bl_participant',
                                                                                        'type' => 'relate_participant',
                                                                                        'type_view' => Fields::TYPE_VIEW_BLOCK_PARTICIPANT,
                                                                                        'group_index' => 1,
                                                                                        'filter_enabled' => true,
                                                                                        'required' => true
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
                                                                'list_view_display' => true,
                                                                'edit_view_display' => true,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => Yii::t($this->getModuleName() . 'Module.base', 'Responsible')),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'responsible',
                                                                                        'type' => 'logical',
                                                                                        'add_zero_value' => false,
                                                                                        'group_index' => 2,
                                                                                        'filter_enabled' => true,
                                                                                        'filter_exception_position' => array('begin_with'),
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
