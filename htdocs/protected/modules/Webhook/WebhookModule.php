<?php
/**
 * WebhookModule - модуль вебхуков
 *
 * @author Alex R.
 * @version 1.0
 */

class WebhookModule extends Module
{
    protected $_prefixName = 'webhooks';

    public $clone = false;

    public $auto_table_name = false;

    public static $table_name = 'webhooks';

    public $db_set_access = '0';

    public $menu = 'main_left';

    public $menu_icon_class = 'fa-globe';

    public $menu_list_view = false;

    public $switch_to_pw = false;

    public function __construct($id, $parent, $config = null)
    {
        parent::__construct($id, $parent, $config);

        Yii::import('Webhook.models.*');
        Yii::import('Webhook.extensions.*');
    }

    public function setModuleName()
    {
        $this->_moduleName = 'Webhook';
    }

    public function setModuleVersion()
    {
        $this->_moduleVersion = '1.0';
    }

    public function setConstructorTitle()
    {
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Webhooks');
    }

    public function getConstructorFields()
    {
        return array_merge($this->_constructor_fields, [Fields::MFT_MODULE_PUBLIC]);
    }

    /**
     * устанавливает елементы меню для кнопки Инструменты в ListView
     */
    protected function initListViewBtnToolsList()
    {
        $this->list_view_btn_tools = [
            'print'           => [
                'class'           => 'list_view_btn-print',
                'title'           => Yii::t('base', 'Print'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
            'export_to_excel' => [
                'class'           => 'list_view_btn-select_export_to_excel',
                'title'           => Yii::t('base', 'Export to Excel'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
            'save_to_pdf'     => [
                'class'           => 'list_view_btn-select_export_to_pdf',
                'title'           => Yii::t('base', 'Save to PDF'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
        ];

        return $this;
    }
}
