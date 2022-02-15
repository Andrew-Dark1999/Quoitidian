<?php
/**
 * ProcessModule - модуль Процессов
 *
 * @author Alex R.
 * @version 1.0
 */

class ProcessModule extends \Module
{
    protected $_destroy = false;

    protected $_page_interface_type = self::PAGE_IT_PROCESS;

    protected $_constructor_setting_blocked = [
        \ConstructorModel::SETTING_TEMPLATES,
        \ConstructorModel::SETTING_DATA_IF_PARTICIPANT,
        \ConstructorModel::SETTING_FINISHED_OBJECTS,
    ];

    public $clone = false;

    public $auto_table_name = false;

    public static $table_name = 'process';

    public function __construct($id, $parent, $config = null)
    {
        parent::__construct($id, $parent, $config);

        \Yii::import('application.models.ParticipantModel');
        \Yii::import('application.models.ParticipantActionsModel');
        \Yii::import('application.models.ParticipantConstModel');

        Yii::import('Process.components.*');
        Yii::import('Process.models.*');
        Yii::import('Process.models.NotificationService.*');
        Yii::import('Process.extensions.*');
    }

    public function setModuleName()
    {
        $this->_moduleName = 'Process';
    }

    public function setModuleVersion()
    {
        $this->_moduleVersion = '1.0';
    }

    public function setConstructorTitle()
    {
        $this->_constructor_title = Yii::t($this->getModuleName() . 'Module.base', 'Process');
    }

    public function getConstructorFields()
    {
        return array_merge($this->_constructor_fields, ['module', 'relate_dinamic']);
    }

    /**
     * Инициализация алиасов для кнопок
     */
    protected function initListViewGeneralBtnList()
    {
        $this->list_view_btn_general = [
            'add'          => [
                'title' => Yii::t('base', 'Start') . ' +',
            ],
            'add_template' => [
                'title' => Yii::t('base', 'Add'),
            ],
        ];
    }

}    
