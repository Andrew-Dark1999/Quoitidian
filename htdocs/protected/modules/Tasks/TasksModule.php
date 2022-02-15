<?php
/**
* TasksModule - модуль задач
* @author Alex R.
* @version 1.0
*/ 

class TasksModule extends Module
{   
    //protected $_be_parent_module = true;
    protected $_destroy = false;
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'tasks';
    public $db_set_access = '1';

    public $list_view_btn_project = true;
    public $process_view_btn_sorting = true;
    public $process_view_btn_project = true;
    public $process_view_btn_add_panel = false;
    public $view_related_task = false;
    public static $relate_store_postfix_params = '_parent';

 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);

        \Yii::import('application.models.DataListModel');
        \Yii::import('ext.ElementMaster.EditView.Elements.Edit.Edit');
        \Yii::import('ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit');

        Yii::import('Tasks.models.*');
        Yii::import('Tasks.extensions.ElementMaster.*');
        
    }
       
   
    public function setModuleName(){
        $this->_moduleName = 'Tasks';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 



    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Tasks');
    }


    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('relate_dinamic'));
    }


    public function getProcessViewBtnSorting(){
        if(\Yii::app()->request->getParam('pci') || \Yii::app()->request->getParam('pdi')){
            return false;
        }

        return $this->process_view_btn_sorting;
    }


    public function checkProcessViewBttnAddZeroPanel(){
        if(Yii::app()->request->getParam('finished_object')){
            return false;
        }

        return $this->process_view_btn_add_panel;
    }


    public function checkProcessViewAddZeroPanel(){
        if(Yii::app()->request->getParam('finished_object')){
            return false;
        }

        return true;
    }


    /**
     * correctProcessViewSchemaForFieldGroupList - коректировка схемы полей для списка группировок
     */
    public function correctProcessViewSchemaForFieldGroupList(&$schema_parse = null){
        if($this->view_related_task == false){
            foreach($schema_parse['elements'] as $key => $element){
                if(!isset($element['field'])) continue;
                if($element['field']['params']['type'] == \Fields::MFT_SELECT && $element['field']['params']['name'] == 'todo_list'){
                    unset($schema_parse['elements'][$key]);
                }
            }
        }

        return $this;
    }



    public function getSchemaFatureDefault(){
        $base_schema = ExtensionModel::model()->findByPk(ExtensionModel::MODULE_BASE)->getModule()->getSchemaFatureDefault();
        $schema =
            array_merge(
            $base_schema,

            array(
                Schema::getInstance()->generateDefaultSchema(
                        array(
                            'block' =>
                                array(
                                    'type' => 'block',
                                    'params' => array(
                                        'title' => Yii::t($this->getModuleName() . 'Module.base', 'ToDo block'),
                                        'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000) . 'todo_list'),
                                        'edit_view_display' => false,
                                        'header_hidden' => true,
                                    ),
                                    'elements' => array(
                                        array(
                                            'block_panel' => array(
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 1),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                                'process_view_group' => true,
                                                                'c_count_select_fields_display' => false, 
                                                                'c_list_view_display' => false,
                                                                'c_process_view_group_display' => false,
                                                                'destroy' => false,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => Yii::t($this->getModuleName() . 'Module.base', 'ToDo list')),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => 'todo_list',
                                                                                        'type' => 'string',
                                                                                        'group_index' => 100,
                                                                                        'c_types_list_index' => Fields::TYPES_LIST_INDEX_DEFAULT,
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





    /**
     * initPropertiesForProcessView - Установка глобальных параметров для ProcessView. Вызывается с контроллера ProcessView
     * @param null $vars
     */
    public function initPropertiesForProcessView($vars = null){
        $pci = (!empty($vars['pci']) ? $vars['pci'] : Yii::app()->request->getParam('pci'));
        $pdi = (!empty($vars['pdi']) ? $vars['pdi'] : Yii::app()->request->getParam('pdi'));

        if($pci && $pdi){
            $this->process_view_btn_sorting = false;
            $this->process_view_btn_add_panel = true;
            $this->view_related_task = true;
        } else {
            $this->process_view_btn_sorting = true;
            $this->process_view_btn_add_panel = false;
            $this->view_related_task = false;
        }

        return $this;
    }





    /**
     * getProcessViewShowZeroPanelsIfFind - не отобрадать пустые панели, если была произведена сортировка или фильтрация
     */
    public function getProcessViewShowZeroPanelsIfFind(){
        if(Yii::app()->request->getParam('pci') && Yii::app()->request->getParam('pci')){
            return true;
        }

        return $this->process_view_show_zero_panels_if_find;
    }




}    
