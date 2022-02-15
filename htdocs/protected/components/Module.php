<?php

abstract class Module extends CWebModule
{
    // Pages interface types
    const PAGE_IT_DEFAULT = 'default';
    const PAGE_IT_CONSTRUCTOR = 'constructor';
    const PAGE_IT_REPORTS = 'reports';
    const PAGE_IT_PROCESS = 'process';
    const PAGE_IT_COMMUNICATIONS = 'communication';
    const PAGE_IT_CALLS = 'calls';

    const PAGE_NAME_DEFAULT = 'default';
    const PAGE_NAME_LIST_VIEW = 'list_view';
    const PAGE_NAME_PROCESS_VIEW = 'process_view';

    // Имя модуля - соответствует названию класса модуля без "Module"
    protected $_moduleName;

    // Версия
    protected $_moduleVersion;

    // префикс экземпляра модуля
    protected $_prefixName = null;

    // название модуля при создании в конструкторе
    protected $_constructor_title;

    // Список полей для отображения в кострукторе
    protected $_constructor_fields = ['numeric', 'string', 'logical', 'datetime', 'file', 'file_image', 'select', 'relate', 'calculated'];

    // Параметры конструктора для блокировки
    protected $_constructor_setting_blocked = [];

    // указывает, что у модуля есть родительский модуль 
    protected $_be_parent_module = false;

    // указывает на существования страницы шаблонов для  модуля
    protected $_is_template = EditViewModel::THIS_TEMPLATE_MODULE;

    // разрешает удаление модуля
    protected $_destroy = true;

    // показывает/скрывает пункт меню для модуля
    protected $_menu_display = true;

    // Заверщенные обьекты - не скрывать
    protected $_finished_object = false;

    // Показ блоков, по-умолчанию показ всех
    protected $_show_blocks = true;

    // тип интерфейса. Служит для опереденения набора js скриптов, стилей
    protected $_page_interface_type = self::PAGE_IT_DEFAULT;

    // позволяет провести автопереход на подчиненный модуль (после сохранення новой сущности )
    protected $_auto_show_child_list_entities_pf = false;

    // Разрешает загрузать экземпляр модуля в конструкторе
    public $constructor = true;

    // Разрешает создавать экземпляр модуля
    public $clone = true;

    // При создании экземпляра модуля генерирует автоматическое название таблицы в БД (true). В противном случае название = названию модуля
    public $auto_table_name = true;

    // Название таблицы в БД при $auto_table_name = false
    public static $table_name = null;

    // Модель ExtensionModel
    public $extension;

    // Модель ExtensionCopyModel
    public $extensionCopy;

    // параметр для таблицы extension copy. Указывает, что для экземпляра модуля необходимо устанавливать персональный  доступ
    public $db_set_access = '1';

    // главный шаблон для listView
    public $list_view_layout = true;

    // меню. Значение null не отображает ссылку в меню
    public $menu = 'main_top';

    // показ меню для ListView
    public $menu_list_view = true;

    // показ переключения из ListView в ProcessView и наоборот
    public $switch_to_pw = true;

    // список иконок (CommunicationParams, ListView, ProcessView) для отображения
    public $list_view_icon_show = [];

    // отображение в listView меню Инструменты
    public $list_view_btn_tools = [];

    // показывать только данные модуля, если участник
    public $data_if_participant = false;

    // добавление/редактирование в EditView
    public $edit_view_enable = true;

    // inline редактирование
    public $inline_edit_enable = true;

    // показывает кнопку Проекты на ListView
    public $list_view_btn_project = false;

    // елементы меню кнопки "Дествия"
    public $list_view_btn_actions = [];

    // показывает кнопку Шаблоны на ListView
    public $list_view_btn_templates = true;

    // показывает кнопку Фильтры на ListView
    public $list_view_btn_filter = true;

    // список исключенных полей для list_view
    public $list_view_without_fields = [];

    // показывает кнопку Проекты на ProcessView
    public $process_view_btn_project = false;

    // показывает кнопку Сотировка на ProcessView
    public $process_view_btn_sorting = true;

    // показывает кнопку Дабавить список (Панель)
    public $process_view_btn_add_panel = false;

    // показывает последнюю загруженную картинку в блок Активности
    public $process_view_last_bl_active_image = true;

    // не отобрадать пустые панели, если была произведена сортировка или фильтрация
    public $process_view_show_zero_panels_if_find = false;

    // дополнительный префикс для UserStorage destination
    public static $relate_store_postfix_params = '';

    // параметры для подальшего ипользования при проверке доступа
    private $_access_check_params = [];

    // список алиасов для кнопок
    public $list_view_btn_general = [];

    public function __construct($id, $parent, $config = null)
    {
        parent::__construct($id, $parent, $config);

        $this->setModuleName();
        $this->setConstructorTitle();
        $this->initListViewBtnActionList();
        $this->initListViewBtnToolsList();
        $this->initListViewIconsList();
        $this->initListViewGeneralBtnList();
    }

    public static function getInstance()
    {
        return new static;
    }

    abstract protected function setModuleName();

    abstract protected function setConstructorTitle();

    abstract protected function setModuleVersion();

    public function getModuleName()
    {
        return $this->_moduleName;
    }

    public function getPrefixName()
    {
        return $this->_prefixName;
    }

    public function getConstructorTitle()
    {
        return $this->_constructor_title;
    }

    public function getModuleVersion()
    {
        return $this->_moduleVersion;
    }

    // Название модуля
    public function getModuleTitle($add_parent_title = true)
    {

        if (isset($_GET['pci']) && isset($_GET['pdi']) && $add_parent_title) {
            $extension_copy_pci = ExtensionCopyModel::model()->findByPk($_GET['pci']);
            $schema_parser = $extension_copy_pci->getSchemaParse();

            $alias = 'evm_' . $extension_copy_pci->copy_id;
            $dinamic_params = [
                'tableName' => $extension_copy_pci->getTableName(null, false, false),
                'params'    => Fields::getInstance()->getActiveRecordsParams($schema_parser),
            ];

            $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($_GET['pdi']);
            $module_title = ExtensionCopyModel::model()->findByPk($_GET['pci'])->getModule(false)->getModuleTitle(false) . ': ' . $extension_data->getModuleTitle($extension_copy_pci);
        } else {
            if (!empty($this->extensionCopy)) {
                $module_title = Yii::t($this->getModuleName() . 'Module.base', $this->extensionCopy->title);
            } else {
                $module_title = $this->getModuleTitleDefault();
            }
        }

        return $module_title;
    }

    // Название модуля (из словаря)
    public function getModuleTitleDefault()
    {
        return Yii::t($this->getModuleName() . 'Module.base', 'Base');
    }

    // Описание (из словаря)
    public function getModuleDescriptionDefault()
    {
        return Yii::t($this->getModuleName() . 'Module.base', 'Description');
    }

    /**
     * setTableName
     */
    public function setTableName($table_name)
    {
        static::$table_name = $table_name;
    }

    /**
     * getTableName
     */
    public function getTableName()
    {
        return static::$table_name;
    }

    /**
     * Возвращает Схему полей данных по умолчанию
     *
     * @return array()
     */
    public function getSchemaFatureDefault()
    {
        return [];
    }

    /**
     * Возвращает Схему полей данных для конструктора
     *
     * @return array()
     */
    public function getSchemaConstructor()
    {
        if (empty($this->extensionCopy)) {
            return $this->getSchemaFatureDefault();
        }

        $schema_fature = $this->getSchemaFature();
        if (!empty($schema_fature)) {
            return $schema_fature;
        } else {
            $schema = $this->getSchema($this->extensionCopy);
            if (!empty($schema)) {
                return $schema;
            } else {
                return $this->getSchemaFatureDefault();
            }
        }
    }

    /**
     * Возвращает редактируемую Схему полей
     *
     * @return array()
     */
    public function getSchemaFature()
    {
        if (empty($this->extensionCopy)) {
            return [];
        }

        return json_decode($this->extensionCopy->schema_fature, true);
    }

    /**
     * Возвращает рабочую (инсталированую) Схему полей
     *
     * @return array()
     */
    public function getSchema($extension_copy)
    {
        if (empty($extension_copy)) {
            return [];
        }

        return json_decode($extension_copy->schema, true);
    }

    /**
     *   установка обьекта  модели  Extension по названию модуля
     */
    public function setExtension()
    {
        $this->extension = ExtensionModel::model()->modulesActive()->find('name=:name', [':name' => $this->getModuleName()]);

        return $this;
    }

    /**
     *   установка обьекта  модели  Extension по названию модуля
     */
    public function setExtensionCopy($extension_copy)
    {
        $this->extensionCopy = $extension_copy;

        return $this;
    }

    /**
     *   установка первого обьекта модели ExtensionCopy исходя из модели Extension
     */
    public function setFirstExtensionCopy()
    {
        if ($this->extension === null) {
            return $this;
        }

        $extension_copies = ExtensionCopyModel::model()
            ->modulesActive()
            ->findAll('extension_id=:extension_id', [':extension_id' => $this->extension->extension_id]);
        if (!empty($extension_copies)) {
            $this->extensionCopy = $extension_copies[0];
        }

        return $this;
    }

    /**
     * возвращает список полей для конструктора
     */
    public function getConstructorFields()
    {
        return $this->_constructor_fields;
    }

    /**
     * возвращает статус, является ли модуль подчиненным другому модулю, или нет
     */
    public function getBeParentModule()
    {
        return $this->_be_parent_module;
    }

    /**
     * возвращает статус существования шаблона у модуля
     */
    public function isTemplate($extension_copy = null)
    {
        if ($extension_copy === null) {
            $extension_copy = $this->extensionCopy;
        }
        if (!empty($extension_copy)) {
            return $extension_copy->is_template;
        } else {
            return $this->_is_template;
        }
    }

    /**
     * @return void
     */
    public function getRawDataIfParticipant()
    {
        $data_if_participant = $this->data_if_participant;

        if ($this->extensionCopy) {
            $data_if_participant = $this->extensionCopy->getRawDataIfParticipant();
        }

        return $data_if_participant;
    }

    /**
     * возвращает статус: показывать только данные модуля, если участник
     *
     * @return boolean
     */
    public function dataIfParticipant($extension_copy = null)
    {
        $data_if_participant = $this->data_if_participant;

        if ($extension_copy === null) {
            $extension_copy = $this->extensionCopy;
        }
        if ($extension_copy) {
            $data_if_participant = $extension_copy->dataIfParticipant();
        }

        return $data_if_participant;
    }

    /**
     * разрешает/запрещает удаление модуля
     *
     * @return boolean
     */
    public function Destroy()
    {
        if (!empty($this->extensionCopy)) {
            return (boolean)$this->extensionCopy->destroy;
        } else {
            return $this->_destroy;
        }
    }

    /**
     * показывает/скрывает пункт меню для модуля
     *
     * @return boolean
     */
    public function menuDisplay()
    {
        if (!empty($this->extensionCopy)) {
            return (boolean)$this->extensionCopy->menu_display;
        } else {
            return $this->_menu_display;
        }
    }

    /**
     * параметр "Завершенные обьекты"
     *
     * @return boolean
     */
    public function finishedObject()
    {
        if (!empty($this->extensionCopy)) {
            return (boolean)$this->extensionCopy->finished_object;
        } else {
            return $this->_finished_object;
        }
    }

    /**
     * параметр "Показ блоков"
     *
     * @return boolean
     */
    public function showBlocks()
    {
        if (!empty($this->extensionCopy)) {
            return (boolean)$this->extensionCopy->show_blocks;
        } else {
            return $this->_show_blocks;
        }
    }

    /**
     * устанавливает елементы меню для кнопки Дествия в ListView
     */
    protected function initListViewBtnActionList()
    {
        $this->list_view_btn_actions = [
            'copy'   => [
                'class'           => 'list_view_btn-copy',
                'title'           => Yii::t('base', 'Copy'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EDIT,
            ],
            'delete' => [
                'class'           => 'list_view_btn-delete',
                'title'           => Yii::t('base', 'Delete'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_DELETE,
            ],
        ];

        return $this;
    }

    /**
     * getListViewBtnActionList - возвращает список Действий для модуля
     *
     * @return array
     */
    public function getListViewBtnActionList()
    {
        $result = [];

        $action_list = $this->list_view_btn_actions;

        if ($action_list == false) {
            return $result;
        }

        $additional_btn_action = \AdditionalProccessingModel::getInstance()->getAdditionalBtnActions($this->extensionCopy);
        if (is_array($additional_btn_action) && count($additional_btn_action)) {
            $action_list = array_merge($action_list, $additional_btn_action);
        }

        foreach ($action_list as $key => $action) {
            if (
                !empty($action['permission_name']) &&
                Access::checkAccess($action['permission_name'], Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))
            ) {
                $result[$key] = $action;
            }
        }

        return $result;
    }

    /**
     * устанавливает елементы меню для кнопки Инструменты в ListView
     */
    protected function initListViewBtnToolsList()
    {
        $this->list_view_btn_tools = [
            'print'             => [
                'class'           => 'list_view_btn-print',
                'title'           => Yii::t('base', 'Print'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
            'export_to_excel'   => [
                'class'           => 'list_view_btn-select_export_to_excel',
                'title'           => Yii::t('base', 'Export to Excel'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
            'save_to_pdf'       => [
                'class'           => 'list_view_btn-select_export_to_pdf',
                'title'           => Yii::t('base', 'Save to PDF'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT,
            ],
            'import_from_excel' => [
                'class'           => 'list_view_btn-import_data',
                'title'           => Yii::t('base', 'Import from Excel'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_IMPORT,
            ],
            'lot_edit'          => [
                'class'           => 'list_view_btn-bulk_edit',
                'title'           => Yii::t('base', 'Lot editing'),
                'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EDIT,
            ],

        ];

        return $this;
    }

    /**
     * getListViewBtnActionList - возвращает список Инструментов для модуля
     *
     * @return array
     */
    public function getListViewBtnToolsList()
    {
        $result = [];
        $tools_list = $this->list_view_btn_tools;

        if ($tools_list == false) {
            return $result;
        }

        foreach ($tools_list as $key => $tools) {
            if ($key == 'lot_edit' && $this->inline_edit_enable == false) {
                continue;
            }

            if (
                !empty($tools['permission_name']) &&
                Access::checkAccess($tools['permission_name'], Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))
            ) {
                $result[] = $tools;
            }
        }

        return $result;
    }

    /**
     * устанавливает иконы (CommunicationParams, ListView, ProcessView) в ListView
     */
    protected function initListViewIconsList()
    {
        $this->list_view_icon_show = [
            'switch_to_cv' => true, // CalendarView
            'switch_to_lv' => true, // ListView
            'switch_to_pv' => true, // ProcessView
        ];

        return $this;
    }

    /**
     * Установка параметров для подальшего ипользования при проверке доступа
     * $access_id, $access_id_type
     */
    public function setAccessCheckParams($access_id, $access_id_type)
    {
        $this->_access_check_params = [
            'access_id'      => $access_id,
            'access_id_type' => $access_id_type,
        ];

        return $this;
    }

    /**
     * Возвращает параметры для ипользования при проверке доступа
     */
    public function getAccessCheckParams($param_name = null)
    {
        if ($param_name === null) {
            return $this->_access_check_params;
        } else {
            return $this->_access_check_params[$param_name];
        }
    }

    /**
     * constructorSettingBlocked - возвращает статус блокировки параметра мудуля в конструкторе
     */
    public function constructorSettingBlocked($setting_type)
    {
        if (empty($this->_constructor_setting_blocked)) {
            return false;
        }

        return in_array($setting_type, $this->_constructor_setting_blocked);
    }

    /**
     * getPageInterfaceType
     */
    public function getPageInterfaceType()
    {
        return $this->_page_interface_type;
    }

    /**
     * getProcessViewBtnSorting
     */
    public function getProcessViewBtnSorting()
    {
        return $this->process_view_btn_sorting;
    }

    /**
     * checkProcessViewBttnAddZeroPanel
     */
    public function checkProcessViewBttnAddZeroPanel()
    {
        return $this->process_view_btn_add_panel;
    }

    /**
     * checkProcessViewAddZeroPanel
     */
    public function checkProcessViewAddZeroPanel()
    {
        return true;
    }

    /**
     * checkAutoShowChildListEntitiesPf
     */
    public function checkAutoShowChildListEntitiesPf()
    {
        return $this->_auto_show_child_list_entities_pf;
    }

    /**
     * correctProcessViewSchemaForFieldGroupList - коректировка схемы полей для списка группировок
     */
    public function correctProcessViewSchemaForFieldGroupList(&$schema_parse = null)
    {
        return $this;
    }

    /**
     * getProcessViewCardDataListAppendToSelect - добавление условия select при формировании запроса на выборку списка карточек
     */
    public function getProcessViewCardDataListAppendToSelect()
    {
        return;
    }

    /**
     * initPropertiesForProcessView - Установка глобальных параметров для ProcessView. Вызывается с контроллера ProcessView
     *
     * @param null $vars
     */
    public function initPropertiesForProcessView($vars = null)
    {
        return $this;
    }

    /**
     * getProcessViewShowZeroPanelsIfFind - не отобрадать пустые панели, если была произведена сортировка или фильтрация
     */
    public function getProcessViewShowZeroPanelsIfFind()
    {
        return $this->process_view_show_zero_panels_if_find;
    }

    /**
     * Инициализация алиасов для кнопок
     */
    protected function initListViewGeneralBtnList()
    {
        $this->list_view_btn_general = [
            'add'          => [
                'title' => Yii::t('base', 'Add'),
            ],
            'add_template' => [
                'title' => Yii::t('base', 'Add'),
            ],
        ];
    }

    /**
     * Возвращает данные кнопки: подись, название и др.
     *
     * @param string $buttonAlias
     * @return array
     */
    public function getListViewGeneralBtn(string $buttonAlias, bool $thisTamplate = false): array
    {
        if ($thisTamplate) {
            $buttonAlias = $buttonAlias . '_template';
        }

        return array_key_exists($buttonAlias, $this->list_view_btn_general) ? $this->list_view_btn_general[$buttonAlias] : [];
    }
}
