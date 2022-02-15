<?php


class CallsModule extends \Module{

    protected $_destroy = false;
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'calls';
    protected $_page_interface_type = self::PAGE_IT_CALLS;

    public $user_params = false;

    //public $edit_view_enable = false;
    //public $inline_edit_enable = false;

    protected $_constructor_setting_blocked = array(
                        \ConstructorModel::SETTING_TEMPLATES,
                        \ConstructorModel::SETTING_DATA_IF_PARTICIPANT,
                        \ConstructorModel::SETTING_FINISHED_OBJECTS,
                        \ConstructorModel::SETTING_SHOW_BLOCKS,
                    );



    public function __construct($id, $parent, $config=null){
        parent::__construct($id, $parent, $config);

        //\Yii::import('application.models.EditViewModel');

        \Yii::import('Calls.models.*');
        \Yii::import('application.modules.Calls.extensions.*');

        //(new CommunicationsSourceModel(\Communications\models\ServiceModel::SERVICE_NAME_EMAIL, null, WebUser::getUserId()));

        $this->user_params = (new CommunicationsServiceParamsModel())->getUserParams();
    }


    public function setModuleName(){
        $this->_moduleName = 'Calls';
    }



    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    }


    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t('base', 'New module');
    }




    /**
     * устанавливает елементы меню для кнопки Дествия в ListView
     */
    protected function initListViewBtnActionList(){
        $this->list_view_btn_actions = array();
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
            'lot_edit' => array(
                'class' => 'list_view_btn-bulk_edit',
                'title' => Yii::t('base', 'Lot editing'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EDIT,
            ),
        );

        return $this;
    }



}
