<?php
/**
* ReportsModule - модуль Отчетов
* @author Alex R.
* @version 1.0
*/ 

class ReportsModule extends \Module
{   
    protected $_destroy = false;
    protected $_constructor_setting_blocked = array(\Reports\models\ConstructorModel::SETTING_DATA_IF_PARTICIPANT);

    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'reports';
    public $db_set_access = '1';
    public $switch_to_pw = false;
    public $list_view_btn_templates = false;
    public $list_view_btn_filter = false;
    protected $_page_interface_type = self::PAGE_IT_REPORTS;
    
    
    
 
    public function __construct($id,$parent,$config=null){
        parent::__construct($id,$parent,$config);

        Yii::import('Reports.models.*');
        Yii::import('Reports.extensions.*');
    }


    public function setModuleName(){
        $this->_moduleName = 'Reports';
    } 
 
    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    } 

    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Reports');
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


}    
