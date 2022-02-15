<?php
/**
 * Fields widget
 *
 * @author Alex R.
 * @version 1.0
 */

class Fields
{

    private $_ar_rules_scenario = 'edit';

    private $_check_access = true;

    const TYPES_LIST_INDEX_DEFAULT = 1; // список типов по дефолту для модуля
    const TYPES_LIST_INDEX_TITLE = 2; // список типов для поля "Название"
    const TYPES_LIST_INDEX_BLOCK = 3; // для поля "Показать блок"

    const TYPE_VIEW_DEFAULT = 'edit';
    const TYPE_VIEW_EDIT_HIDDEN = 'edit_hiddel';
    const TYPE_VIEW_AVATAR = 'avatar';

    const TYPE_VIEW_BUTTON_DATE_ENDING = 'button_date_ending';
    const TYPE_VIEW_BUTTON_SUBSCRIPTION = 'button_subscription';
    const TYPE_VIEW_BUTTON_RESPONSIBLE = 'button_responsible';
    const TYPE_VIEW_BUTTON_STATUS = 'button_status';

    const TYPE_VIEW_DT_DATE = 'dt_date';
    const TYPE_VIEW_DT_DATETIME = 'dt_datetime';

    const TYPE_VIEW_BLOCK_PARTICIPANT = 'block_participant';
    const TYPE_VIEW_BLOCK_ATTACHMENTS = 'block_attachments';

    const RELATE_TYPE_ONE = 'one_to_many';
    const RELATE_TYPE_MANY = 'many_to_many';

    const NAME_GENERATE_SEPARATOR = '_';

    // Module field types
    const MFT_NUMERIC = 'numeric';
    const MFT_STRING = 'string';
    const MFT_LOGICAL = 'logical';
    const MFT_DATETIME = 'datetime';
    const MFT_FILE = 'file';
    const MFT_FILE_IMAGE = 'file_image';
    const MFT_ATTACHMENTS = 'attachments';
    const MFT_SELECT = 'select';
    const MFT_MODULE = 'module';
    const MFT_MODULE_PUBLIC = 'module_public';
    const MFT_RELATE = 'relate';
    const MFT_RELATE_DINAMIC = 'relate_dinamic';
    const MFT_RELATE_THIS = 'relate_this';
    const MFT_RELATE_STRING = 'relate_string';
    const MFT_RELATE_PARTICIPANT = 'relate_participant';
    const MFT_SUB_MODULE = 'sub_module';
    const MFT_ACCESS = 'access';
    const MFT_PERMISSION = 'permission';
    const MFT_DISPLAY = 'display';
    const MFT_DISPLAY_NONE = 'display_none';
    const MFT_AUTO_NUMBER = 'auto_number';
    const MFT_DISPLAY_BLOCK = 'display_block';
    const MFT_ACTIVITY = 'activity';
    const MFT_CALCULATED = 'calculated';
    const MFT_DATETIME_ACTIVITY = 'datetime_activity';

    // Группы полей
    private $_fields = [
        self::MFT_NUMERIC            => [
            'title'                   => 'valor numérico',
            'name'                    => 'Numeric',
            'field_types'             => ['decimal', 'integer', 'float'],
            'ActiveRecordsRuleMethod' => 'NumericAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => 'group2',
            ],
        ],
        self::MFT_STRING             => [
            'title'                   => 'valor de cadena',
            'name'                    => 'String',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_1,
            ],
        ],
        self::MFT_LOGICAL            => [
            'title'                   => 'Logical value',
            'name'                    => 'Logical',
            'field_types'             => ['enum'],
            'ActiveRecordsRuleMethod' => 'LogicalAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_5,
            ],
        ],
        self::MFT_DATETIME           => [
            'title'                   => 'fecha y hora',
            'name'                    => 'DateTime',
            'field_types'             => ['datetime'],
            'ActiveRecordsRuleMethod' => 'DateTimeAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_3,
            ],
        ],
        self::MFT_FILE               => [
            'title'                   => 'documento descargable',
            'name'                    => 'File',
            'field_types'             => ['string'],
            'process_view_group'      => false,
            'params'                  => [
                'file_min_size' => null,
                'file_max_size' => null,
                'filter_group'  => \FilterMap::GROUP_1,
            ],
            'ActiveRecordsRuleMethod' => 'FileAR',
        ],
        self::MFT_FILE_IMAGE         => [
            'title'                   => 'imágen',
            'name'                    => 'ImageFile',
            'field_types'             => ['string'],
            'process_view_group'      => false,
            'params'                  => [
                'file_min_size'   => null,
                'file_max_size'   => null,
                'file_types'      => ['bmp', 'jpg', 'jpeg', 'gif', 'png', 'tiff', 'tif', 'tga', 'ico'],
                'file_types_mime' => 'image/bmp,image/x-windows-bmp,image/gif,image/jpeg,image/png,image/tiff,image/x-icon,image/x-tga',
                'filter_group'    => \FilterMap::GROUP_1,
            ],
            'ActiveRecordsRuleMethod' => 'FileImageAR',
        ],
        self::MFT_ATTACHMENTS        => [
            'title'                   => 'Attachments',
            'name'                    => 'File',
            'field_types'             => ['string'],
            'process_view_group'      => false,
            'params'                  => [
                'file_min_size'  => null,
                'file_max_size'  => null,
                'filter_enabled' => false,
                'filter_group'   => null,
            ],
            'ActiveRecordsRuleMethod' => 'FileAll',
        ],
        self::MFT_SELECT             => [
            'title'                   => 'lista de valores',
            'name'                    => 'Select',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'SelectAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_1,
            ],

        ],
        // Список модулей, что связаные в "текущем модулем"
        self::MFT_MODULE             => [
            'title'                   => 'List of modules',
            'name'                    => 'Module',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'SelectAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled' => false,
            ],
        ],
        // Список публичных модулей
        self::MFT_MODULE_PUBLIC      => [
            'title'                   => 'List of modules',
            'name'                    => 'Module',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'SelectAR',
            'process_view_group'      => false,
        ],
        // связь с другим модулем
        self::MFT_RELATE             => [
            'title'                   => 'relacionar con otro modulo',
            'name'                    => 'Relate',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'RelateAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_5,
            ],
        ],
        // связь с динамично изменяемым модулем
        self::MFT_RELATE_DINAMIC     => [
            'title'                   => 'relacionar con otro módulo (dinámico)',
            'name'                    => 'Relate_dinamic',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'RelateAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled' => false,
            ],
        ],
        // связь на самого себя
        self::MFT_RELATE_THIS        => [
            'title'                   => 'Relate with this module',
            'name'                    => 'Relate_this',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'RelateAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_5,
            ],
        ],
        // связь с другим модулем (поле Название). Дополнительно в главной таблице родительского модуля создается текстовое поле
        self::MFT_RELATE_STRING      => [
            'title'                   => 'relacionar con otro modulo',
            'name'                    => 'Relate_title',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_group'   => \FilterMap::GROUP_1,
                'edit_view_show' => false,
            ],
        ],
        // связь с другим модулем (Учасники)
        self::MFT_RELATE_PARTICIPANT => [
            'title'                   => 'Relate with participant table',
            'name'                    => 'Relate_participant',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'RelateAR',
            'process_view_group'      => true,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_4,
                'c_db_create'  => false,
            ],
        ],
        // для сабмодулей
        self::MFT_SUB_MODULE         => [
            'title'                   => 'Submodule',
            'name'                    => 'Select',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'SubModuleAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled' => false,
            ],
        ],
        // список модулей. Устаревшее, использовался в доступах...
        self::MFT_ACCESS             => [
            'title'                   => 'Access list',
            'name'                    => 'Access',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'AccessAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled' => false,
            ],
        ],
        // список прав доступов. Устаревшее, использовался в доступах...
        self::MFT_PERMISSION         => [
            'title'                   => 'Permission list',
            'name'                    => 'Permission',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'PermissionAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled' => false,
            ],
        ],
        // по типу аналогично Текстовому.  
        self::MFT_DISPLAY            => [
            'title'                   => 'desplegar',
            'name'                    => 'Display',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => false,
            'params'                  => [
                'c_load_params_btn_display' => false,
                'filter_group'              => \FilterMap::GROUP_1,
                'edit_view_show'            => false,
            ],
        ],
        // по типу аналогично Текстовому. Не отображается в ListView
        self::MFT_DISPLAY_NONE       => [
            'title'                   => 'no desplegar',
            'name'                    => 'Display_none',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => false,
            'params'                  => [
                'display'                   => false,
                'c_load_params_btn_display' => false,
                'filter_enabled'            => false,
                'edit_view_show'            => false,
            ],
        ],
        //для автонумерации
        self::MFT_AUTO_NUMBER        => [
            'title'                   => 'numeración automática',
            'name'                    => 'Auto_number',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => false,
            'params'                  => [
                'c_load_params_btn_display' => true,
                'filter_group'              => \FilterMap::GROUP_1,
                'edit_view_show'            => false,
            ],
        ],
        // по типу аналогично текстовому, для показа определенного блока
        self::MFT_DISPLAY_BLOCK      => [
            'title'                   => 'Show one block',
            'name'                    => 'Show_one_block',
            'field_types'             => ['string'],
            'ActiveRecordsRuleMethod' => 'StringAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_group' => \FilterMap::GROUP_1,
            ],
        ],
        //вычисляемое поле
        self::MFT_CALCULATED         => [
            'title'                   => 'campo calculado',
            'name'                    => 'Calculated',
            'field_types'             => ['decimal', 'integer', 'float'],
            'ActiveRecordsRuleMethod' => 'NumericAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_group' => 'group2',
            ],
        ],
        // блок Активность
        self::MFT_ACTIVITY           => [
            'title'                   => 'Activity',
            'name'                    => 'Activity',
            'field_types'             => ['integer'],
            'ActiveRecordsRuleMethod' => 'ActivityAR',
            'process_view_group'      => false,
            'params'                  => [
                'filter_enabled'     => false,
                'list_view_visible'  => false,
                'process_view_group' => false,
            ],
        ],

        self::MFT_DATETIME_ACTIVITY => [
            'title'                   => 'Date and time of last Ativity message',
            'name'                    => 'DateTimeAtivity',
            'field_types'             => ['datetime'],
            'ActiveRecordsRuleMethod' => 'DateTimeAR',
            'process_view_group'      => false,
            'params'                  => [
                'process_view_group'        => false,
                'edit_view_show'            => false,           // показывать полу на странице EditView
                'c_load_params_btn_display' => false,// отображение на форме кнопки вызова параметров
                'c_db_create'               => false,              // создавать поле в базе данных
                'с_remove'                  => false,                 // разрешает/запредает удаление елемента
                //'type' => $field_type,          // тип поля - $this->_fileds()
                'type_view'                 => self::TYPE_VIEW_DEFAULT, // тип отобрадения, тоисть как будет отображен елемент на вьюхах
                'group_index'               => null,          // индекс групировки. Идентифицирует поля с одинаковыми названиями. Задается при парсинге в конструкторе
                'filter_enabled'            => true,       // фильтр - вкл./выкл.
                'filter_exception_position' => [],    // список исключеных (по названию) фильтров
                'add_zero_value'            => true,     // добавляет к списку первым пустое значение. true=null. По умолчанию не задаетс
                'read_only'                 => true,           // маркер, поле только для чтения
                'filter_group'              => \FilterMap::GROUP_3,
            ],
        ],
    ];

    //проверяем только определенные поля
    private $only_active_records_fields = false;

    public static function getInstance()
    {
        return new self;
    }

    public function setCheckAccess($status)
    {
        $this->_check_access = $status;

        return $this;
    }

    /**
     * сортировка списка полей
     */
    private function fieldsSort(array $fields_for_sort, array $templates)
    {
        $fields_tmp = [];
        foreach ($templates as $field) {
            $fields_tmp[$field] = $fields_for_sort[$field];
        }

        return $fields_tmp;
    }

    /**
     * Возвращает паратметры поля(ей) массива self::_fields
     *
     * @param sring $fields - список полей, что нужно вернуть
     * @return array
     */
    public function getFields($fields = [])
    {
        if (empty($fields)) {
            return $this->_fields;
        }

        $fields_tmp = [];

        foreach ($this->_fields as $key => $value) {
            if (in_array($key, $fields)) {
                $fields_tmp[$key] = $value;
            }
        }

        return $this->fieldsSort($fields_tmp, $fields);
    }

    /**
     * Возвращает паратметры поля(ей) по его груповому индексу
     *
     * @param
     * @return array
     */
    public function getFieldsByGroupIndex($index)
    {
        $fields = [];
        switch ($index) {
            case self::TYPES_LIST_INDEX_TITLE :
                $fields = ['display', 'display_none', 'relate_string', 'auto_number'];
                break;

            case self::TYPES_LIST_INDEX_BLOCK :
                $fields = ['display_block'];
                break;
        }

        return $this->fieldsSort($this->getFields($fields), $fields);
    }

    /**
     * Возвращает название поля
     *
     * @return string
     */
    public function getTitle($field_type)
    {
        if (empty($field_type)) {
            return;
        }

        return Yii::t('constructor', $this->_fields[$field_type]['title']);
    }

    /**
     * Возвращает параметры, используемые для автоматической генерации названия
     *
     * @return array
     */
    public function getNameGenerationParams($extension_copy)
    {
        $fields = [
            ['name' => 'counter', 'title' => Yii::t('constructor', 'Counter')],
            ['name' => 'year', 'title' => Yii::t('constructor', 'Year')],
            ['name' => 'quarter', 'title' => Yii::t('constructor', 'Quarter')],
            ['name' => 'month', 'title' => Yii::t('constructor', 'Month')],
            ['name' => 'static_text', 'title' => Yii::t('constructor', 'Static value')],
        ];

        $related_fields = [];

        if ($extension_copy) {
            $modules_ids = [];
            $modules_ids [] = $extension_copy->copy_id;

            //получаем привязанные модули и поля
            $related = \DataModel::getInstance()->setFrom('{{module_tables}}')->setWhere("relate_copy_id is not null AND type = 'relate_module_one' AND copy_id = " . $extension_copy->copy_id)->findAll();

            if (count($related)) {
                foreach ($related as $relate) {
                    $modules_ids [] = $relate['relate_copy_id'];
                }
            }

            //загружаем поля
            if (count($modules_ids)) {
                foreach ($modules_ids as $module_id) {
                    $related_extension_copy = ExtensionCopyModel::model()->findByPK($module_id);
                    $skip_display = false;
                    //для текущего модуля не показываем название, оно не имеет смысла
                    if ($module_id == $extension_copy->copy_id) {
                        $skip_display = true;
                    }
                    $fields_from_module = $this->getNameGenarationFields($related_extension_copy->getSchemaParse(), $skip_display);
                    if (!empty($fields_from_module)) {
                        $related_fields[] = [
                            'title'  => $related_extension_copy->title,
                            'id'     => $related_extension_copy->copy_id,
                            'fields' => $fields_from_module,
                        ];
                    }
                }
            }
        }

        return [$fields, $related_fields];
    }

    /**
     * Получаем массив полей модуля для генерации названия
     *
     * @return array
     */
    public function getNameGenarationFields($schema, $skip_display = false)
    {

        $result = [];
        $types = ['display', 'display_block', 'logical', 'string', 'select', 'numeric'];
        if ($skip_display) {
            unset($types[array_search('display', $types)]);
            unset($types[array_search('display_block', $types)]);
        }
        if (count($schema['elements'])) {
            foreach ($schema['elements'] as $element) {
                if (isset($element['field']['params']['type'])) {
                    if (in_array($element['field']['params']['type'], $types) && $element['field']['params']['type_view'] == 'edit') {
                        $result[] = ['name' => $element['field']['params']['name'], 'title' => $element['field']['title'], 'type' => $element['field']['params']['type']];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Автоматическая генерация названия
     *
     * @return string
     */
    public function getNewRecordTitle($name_generate_data, $name_generate_params, $extension_copy, $edit_data = false)
    {
        if ($edit_data === false) {
            return Yii::t('messages', 'The name will be created automatically');
        }

        $name_generate_data = json_decode($name_generate_data);
        if ($name_generate_params) {
            $name_generate_params = json_decode($name_generate_params);
        }

        if (is_array($name_generate_data) && count($name_generate_data)) {

            $generated_titles = [];
            $current_data = \DataModel::getInstance()->setFrom('{{name_generate_counters}}')->setWhere('extension_copy_id = ' . $extension_copy->copy_id . ' AND field_name IS NULL')->findRow();

            //для значения каждого поля свой счетчик
            foreach ($name_generate_data as $param) {
                if (is_object($param)) {
                    $param_data = $param->{key($param)};
                    $param = key($param);
                }
                switch ($param) {
                    case 'year':
                        $generated_titles [] = date('Y');

                        if ($current_data) {
                            //если текущий год не входит в дату последнего счетчика, удаляем счетчик
                            if (date('Y') != date('Y', $current_data['last_change'])) {
                                \DataModel::getInstance()->delete('{{name_generate_counters}}', 'extension_copy_id = ' . $extension_copy->copy_id . ' AND field_name IS NULL');
                                $current_data = false;
                            }
                        }

                        break;
                    case 'quarter':
                        $generated_titles [] = Yii::t('constructor', 'Quarter') . (int)((date('n') + 2) / 3);

                        if ($current_data) {
                            if ((int)((date('n') + 2) / 3) != (int)((date('n', $current_data['last_change']) + 2) / 3)) {
                                \DataModel::getInstance()->delete('{{name_generate_counters}}', 'extension_copy_id = ' . $extension_copy->copy_id . ' AND field_name IS NULL');
                                $current_data = false;
                            }
                        }

                        break;
                    case 'month':
                        $generated_titles [] = current(explode('|', Yii::t('base', date('F'))));

                        if ($current_data) {
                            if (date('m') != date('m', $current_data['last_change'])) {
                                \DataModel::getInstance()->delete('{{name_generate_counters}}', 'extension_copy_id = ' . $extension_copy->copy_id . ' AND field_name IS NULL');
                                $current_data = false;
                            }
                        }
                        break;
                    case 'counter':
                        $cnt = 1;
                        if ($current_data) {
                            $cnt = $current_data['cnt'] + 1;
                            \DataModel::getInstance()->Update('{{name_generate_counters}}', ['cnt' => $cnt, 'last_change' => time()], 'extension_copy_id = ' . $extension_copy->copy_id . ' AND field_name IS NULL');
                        } else {
                            \DataModel::getInstance()->Insert('{{name_generate_counters}}', ['extension_copy_id' => $extension_copy->copy_id, 'cnt' => $cnt, 'last_change' => time()]);
                        }

                        $generated_titles [] = str_pad($cnt, 4, "0", STR_PAD_LEFT);
                        break;
                    case 'static_text':
                        $generated_titles [] = $param_data;
                        break;
                    default:

                        //используются поля из модуля

                        if ($extension_copy->copy_id == $param) {

                            //поле текущего модуля

                            if (isset($edit_data['EditViewModel'][$param_data])) {

                                $field_value = $this->getNameGenerationFieldValue($extension_copy, $param_data, $edit_data['EditViewModel'][$param_data]);
                                $generated_titles[] = ($this->getNameGenerationParam($name_generate_params, 'independed_numeration', $edit_data['EditViewModel'][$param_data], $extension_copy->copy_id)) ? $this->setFieldCounter($extension_copy, $param_data, $field_value) : $field_value;

                            }

                        } else {

                            //связанный модуль

                            if (!empty($edit_data['element_relate'])) {
                                foreach ($edit_data['element_relate'] as $element_relate) {
                                    if ($element_relate['relate_copy_id'] == $param) {

                                        $ex_copy = \ExtensionCopyModel::model()->findByPK($param);

                                        $data_model = new DataModel();
                                        $data_model
                                            ->setExtensionCopy($ex_copy)
                                            ->setFromModuleTables()
                                            ->setWhere($ex_copy->getTableName() . '.' . $ex_copy->prefix_name . '_id = :id', [':id' => $element_relate['id']]);

                                        $data = $data_model->findRow();

                                        if (isset($data[$param_data])) {
                                            $field_value = $this->getNameGenerationFieldValue($ex_copy, $param_data, $data[$param_data]);
                                            //$generated_titles[] = $this->setFieldCounter($ex_copy, $param_data, $field_value);
                                            $generated_titles[] = ($this->getNameGenerationParam($name_generate_params, 'independed_numeration', $data[$param_data], $extension_copy->copy_id)) ? $this->setFieldCounter($ex_copy, $param_data, $field_value) : $field_value;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }

            return implode(Fields::NAME_GENERATE_SEPARATOR, $generated_titles);
        }

        return false;
    }

    /**
     * Получаем значение определенного параметра автогенерации
     *
     * @return string
     */
    private function getNameGenerationParam($name_generate_params, $param_name, $field_name, $copy_id)
    {

        $param_value = false;

        if (is_array($name_generate_params) && count($name_generate_params)) {
            foreach ($name_generate_params as $param) {
                if (is_object($param)) {
                    if (!empty($param->{$param_name}) && ($param->copy_id == $copy_id) && ($param->name == $field_name)) {
                        $param_value = $param->$param_name;
                        break;
                    }
                }
            }
        }

        return $param_value;
    }

    /**
     * Значение поля
     *
     * @return array
     */
    private function getNameGenerationFieldValue($extension_copy, $field_name, $field_value)
    {

        //загружаем значения, дополнительно проверяем блоки, для них id подменяем на название
        if (!$extension_copy->isShowAllBlocks()) {
            $block_field_data = $extension_copy->getFieldBlockData();
            if (isset($block_field_data['name']) && $field_name == $block_field_data['name']) {
                $blocks = $extension_copy->getSchemaBlocksData();
                foreach ($blocks as $block) {
                    if ($block['unique_index'] == $field_value) {
                        return $block['title'];
                        break;
                    }
                }
            }
        }
        $schema = $extension_copy->getSchemaParse();
        if (count($schema['elements'])) {
            foreach ($schema['elements'] as $element) {
                if (isset($element['field']['params']['type'])) {
                    if (in_array($element['field']['params']['type'], ['select', 'logical', 'string', 'display', 'numeric']) && $element['field']['params']['type_view'] == 'edit' && $element['field']['params']['name'] == $field_name) {
                        if ($element['field']['params']['type'] == 'select') {
                            return $element['field']['params']['values'][$field_value];
                        }
                        if ($element['field']['params']['type'] == 'logical') {
                            return ($field_value) ? Yii::t('base', 'Да') : Yii::t('messages', 'Нет');
                        }
                        if ($element['field']['params']['type'] == 'string' || $element['field']['params']['type'] == 'display') {
                            return $field_value;
                        }
                        if ($element['field']['params']['type'] == 'numeric') {
                            return rtrim($field_value, '0');
                        }
                    }
                }
            }
        }
    }

    /**
     * Счетчик для каждого поля
     *
     * @return array
     */
    private function setFieldCounter($extension_copy, $field_name, $field_value)
    {

        if (!empty($field_name) && !empty($field_value)) {
            $cnt = 1;
            $current_field_data = \DataModel::getInstance()->setFrom('{{name_generate_counters}}')->setWhere("extension_copy_id = " . $extension_copy->copy_id . " AND field_name = '" . $field_name . "' AND field_value = '" . $field_value . "'")->findRow();

            if ($current_field_data) {
                $cnt = $current_field_data['cnt'] + 1;
                \DataModel::getInstance()->Update('{{name_generate_counters}}', ['cnt' => $cnt, 'last_change' => time()], "extension_copy_id = " . $extension_copy->copy_id . " AND field_name = '" . $field_name . "' AND field_value = '" . $field_value . "'");
            } else {
                \DataModel::getInstance()->Insert('{{name_generate_counters}}', ['extension_copy_id' => $extension_copy->copy_id, 'field_name' => $field_name, 'field_value' => $field_value, 'cnt' => $cnt, 'last_change' => time()]);
            }

            return $field_value . str_pad($cnt, 4, "0", STR_PAD_LEFT);
        }

    }

    /**
     * разгрупировка полей c одинаковыми названиями
     * return array()
     */
    public function UnGroupFieldIfTitleSimilar($fields)
    {
        $result = [];
        $tmp = [];
        if (empty($fields)) {
            return $result;
        }
        if (!is_array($fields)) {
            return explode(',', $fields);
        } else {
            foreach ($fields as $value) {
                $tmp = array_merge($tmp, explode(',', $value));
            }
            $result = $tmp;
        }

        return $result;
    }

    /**
     * Создание нового поля модуля в Конструкторе модулей
     * Возвращает массив html елементов формы для Отображения
     *
     * @param string $filed_type
     * @return string
     */
    public function fieldParamsView($field_attr, $exception_copy_id, $extension_copy_id)
    {
        //if(!$config = $this->getFields($field_type)) return;
        $controller = Yii::app()->controller;
        Yii::import('ext.ElementMaster.Constructor.Params.FieldModel');
        $model = new FieldModel;
        $params = $this->getDefaultSchemaParams($field_attr['field_type']);

        /*
        switch($field_attr['field_type']){
            case 'select': 
            $params = array_merge($params, array('values' => array(1 => '', 2 => '')));
        }
*/

        return $controller->widget('ext.ElementMaster.Constructor.Params.Params',
            [
                'params'            => $params,
                'exception_copy_id' => (!empty($exception_copy_id) ? $exception_copy_id : []),
                'model'             => $model,
                'field_attr'        => $field_attr,
                'extension_copy_id' => $extension_copy_id,
            ],
            true);

    }

    /**
     * Возвращает массив полей
     *
     * @param integer $relate_module_copy_id
     * @return string
     */
    public function getModuleFields($relate_module_copy_id, $otherListViewVisible = false)
    {
        $fields = [];
        if (empty($relate_module_copy_id)) {
            return $fields;
        }
        $extension_copy = ExtensionCopyModel::model()->findByPk($relate_module_copy_id);
        $schema = $extension_copy->getSchemaParse();

        if (empty($schema)) {
            return $fields;
        }

        foreach ($schema['elements'] as $value) {
            if (isset($value['field'])) {
                if ($otherListViewVisible == true && (!isset($value['field']['params']['list_view_visible']) || $value['field']['params']['list_view_visible'] == false)) {
                    continue;
                }
                $fields[$value['field']['params']['name']] = [
                    'title'       => $value['field']['title'],
                    'group_index' => (isset($value['field']['params']['group_index']) ? $value['field']['params']['group_index'] : 1),
                ];
            }
        }

        return $fields;
    }

    /**
     * возвращает параметры для определенного типа поля
     */
    public function getDefaultSchemaParams($field_type = null)
    {
        $result = [];
        $schema_default = [
            'display'                   => true,    // отображение поля на формах (страницах) отображения
            'is_primary'                => false,   // указывает, что поле первичное
            'edit_view_show'            => true,    // показывать полу на странице EditView
            'c_load_params_btn_display' => true,    // отображение на форме кнопки вызова параметров
            'c_load_params_view'        => true,    // загружает форму параметров типа сразу с загрузкой типов
            'c_db_create'               => true,    // создавать поле в базе данных
            'c_types_list_index'        => self::TYPES_LIST_INDEX_DEFAULT, //индекс списка типов полей, что выводятся в конструкторе
            'с_remove'                  => true,    // разрешает/запредает удаление елемента
            'title'                    => null,     // подпись
            'name'                      => '',      // название поля в БД
            'relate_module_copy_id'     => null,    // звязаний модуль (для типов: sub_module, relate
            'relate_module_template'    => false,   // указывает на связь с шаблоном модуля
            'relate_index'              => null,    // порядочний номер блока Таблицы или Сабмодуля на форме
            'relate_field'              => null,    // название поля для связи Один-ко-многим
            'relate_type'               => null,    // тип связи
            'relate_many_select'        => false,   // указывает на множественный выбор значений
            'relate_links'              => [
                ['value' => 'create', 'checked' => true],
                ['value' => 'select', 'checked' => true],
                ['value' => 'copy', 'checked' => true],
                ['value' => 'delete', 'checked' => true],
            ],                                          // ссылки блока Сабмодуля
            'values'                    => [],          // значения списка
            'pk'                        => false,       // БД. первичный ключ
            'type'                      => $field_type, // тип поля - $this->_fileds()
            'type_db'                   => '',          // БД. тип
            'type_view'                 => self::TYPE_VIEW_DEFAULT, // тип отобрадения, тоисть как будет отображен елемент на вьюхах
            'maxLength'                 => null,    // БД. для integer, float, decimal до 10^9
            'minLength'                 => null,    // БД. для integer, float, decimal до 10^9
            'file_types'                => null,    // тип файла
            'file_types_mimo'           => null,    // mimo файла
            'file_thumbs_size'          => null,    // UploadsModel::$file_thumbs_size
            'file_max_size'             => null,    // для file, file_image
            'file_min_size'             => null,    // для integer, float, decimal до 10^9
            'file_generate'             => false,   // элемент (типа Файл) для генерации документа
            'name_generate'             => false,   // автоматическая генерация имени файла
            'read_only'                 => false,   // маркер, поле только для чтения
            'size'                      => null,    // БД. для integer, float, decimal, string
            'decimal'                   => null,    // БД. количество знаков дробной части
            'required'                  => false,   // обязательное поле.
            'formula'                   => false,   // используется для ввода формул
            'default_value'             => null,    // значение по умолчанию
            'group_index'               => null,    // индекс групировки. Идентифицирует поля с одинаковыми названиями. Задается при парсинге в конструкторе
            'filter_enabled'            => true,    // фильтр - вкл./выкл.
            'filter_exception_position' => [],      // список исключеных (по названию) фильтров
            'input_attr'                => '',      // атрибуты, что применяются к елементам input. Пример: 'type'=>'password'
            'add_zero_value'            => true,    // добавляет к списку первым пустое значение. true=null. По умолчанию не задается
            'avatar'                    => true,
            'rules'                     => '',
            'unique'                    => false,   // уникальное значение поля: true|false
            'money_type'                => false,
            'add_hundredths'            => false,
        ];
        if ($field_type === null) {
            $result = $schema_default;
        } else {
            if (array_key_exists($field_type, $this->_fields)) {
                if (isset($this->_fields[$field_type]['params'])) {
                    $schema_default = Helper::arrayMerge(
                        $schema_default,
                        $this->_fields[$field_type]['params']
                    );
                }

                $result = Helper::arrayMerge(
                    $schema_default,
                    FieldTypes::getInstance()->getType($this->_fields[$field_type]['field_types'][0])
                );
            }
        }

        return $result;

    }

    public function getLogicalData()
    {
        return [
            "1" => Yii::t('base', 'Yes'),
            "0" => Yii::t('base', 'No'),
        ];
    }

    /**
     *   установка названия сценария для валидатора ActiveRecord
     */
    public function setRrRulesScenario($scenario = 'edit')
    {
        $this->_ar_rules_scenario = $scenario;

        return $this;
    }

    /**
     *   установка только определенных полей для валидатора ActiveRecord
     */
    public function setOnlyActiveRecordsFields($fields)
    {
        $this->only_active_records_fields = $fields;

        return $this;
    }

    /**
     * Возвращает для ActiveRecords массив параметров Валидации, описок названий полей
     */
    public function getActiveRecordsParams($schema)
    {

        $params = [
            'rules'           => [],
            'relations'       => [],
            'scopes'          => [],
            'attributeLabels' => [],
        ];

        if (empty($schema)) {
            return;
        }
        if (!isset($schema['elements'])) {
            return;
        }

        foreach ($schema['elements'] as $value) {

            $rules_tmp = [];

            if ($this->only_active_records_fields !== false) {
                if (isset($value['field']) && !in_array($value['field']['params']['name'], $this->only_active_records_fields)) {
                    continue;
                }
            }

            if (isset($value['field'])) {
                if ($this->_fields[$value['field']['params']['type']]['ActiveRecordsRuleMethod']) {
                    $rules_tmp = $this->{$this->_fields[$value['field']['params']['type']]['ActiveRecordsRuleMethod']}($value['field']);
                }

                if (isset($value['field']['params']['rules']) && $value['field']['params']['rules']) {
                    foreach (explode(',', $value['field']['params']['rules']) as $r) {
                        $rules_tmp['rules'][] = [$value['field']['params']['name'], $r];
                    }
                }
            }

            // merge data array
            if (!empty($rules_tmp['rules'])) {
                $params['rules'] = array_merge($params['rules'], $rules_tmp['rules']);
            }
            if (!empty($rules_tmp['relations'])) {
                $params['relations'] = array_merge($params['relations'], $rules_tmp['relations']);
            }

            if (!empty($rules_tmp['scopes'])) {
                $params['scopes'] = array_merge($params['scopes'], $rules_tmp['scopes']);
            }
            if (!empty($rules_tmp['attributeLabels'])) {
                $params['attributeLabels'] = array_merge($params['attributeLabels'], $rules_tmp['attributeLabels']);
            }
        }

        return $params;
    }

    private function NumericAR($element_schema)
    {
        $rules = [];
        if ($tmp = $this->RuleRequired($element_schema)) {
            $rules[] = $tmp;
        }
        if ($tmp = $this->RuleNumerical($element_schema)) {
            $rules[] = $tmp;
        }
        if ($tmp = $this->RuleUnique($element_schema)) {
            $rules[] = $tmp;
        }

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function StringAR($element_schema)
    {
        $rules = [];

        $set_require = true;
        if (isset($element_schema['params']['input_attr'])) {
            $attr = json_decode($element_schema['params']['input_attr'], true);
            if (!empty($attr)) {
                if (in_array('password', $attr)) {
                    $set_require = false;
                }
            }
        }

        if ($set_require) {
            if ($tmp = $this->RuleRequired($element_schema)) {
                $rules[] = $tmp;
            }
        }
        if ($tmp = $this->RuleUnique($element_schema)) {
            $rules[] = $tmp;
        }

        //if($tmp = $this->RuleLength($element_schema)) $rules[] = $tmp;

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function LogicalAR($element_schema)
    {
        $rules = [];
        if ($tmp = $this->RuleRequired($element_schema)) {
            $rules[] = $tmp;
        }

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function DateTimeAR($element_schema)
    {
        $rules = [];
        if ($element_schema['params']['required'] == true) {

            if ($tmp = $this->RuleRequired($element_schema)) {
                $rules[] = $tmp;
            } else {
                if ($tmp = $this->RuleDateTime($element_schema)) {
                    $rules[] = $tmp;
                }
            }

        }

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function FileAR($element_schema)
    {
        $rules = [];
        if ($element_schema['params']['required'] == true) {
            $rules[] = [$element_schema['params']['name'], 'fileCheckRequired'];
        }
        $rules[] = [$element_schema['params']['name'], 'fileCheck'];

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function FileImageAR($element_schema)
    {
        $rules = [];
        if ($element_schema['params']['required'] == true) {
            $rules[] = [$element_schema['params']['name'], 'fileCheckRequired'];
        }
        $rules[] = [$element_schema['params']['name'], 'fileCheck'];

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function FileAll($element_schema)
    {
        $rules = [];
        if ($element_schema['params']['required'] == true) {
            $rules[] = [$element_schema['params']['name'], 'fileCheckRequired'];
        }
        $rules[] = [$element_schema['params']['name'], 'fileCheck'];

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function SelectAR($element_schema)
    {
        $rules = [];
        if ($tmp = $this->RuleRequired($element_schema)) {
            $rules[] = $tmp;
        }

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function RelateAR($element_schema)
    {
        $rules = [];
        if ($this->_check_access && !Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $element_schema['params']['relate_module_copy_id'], Access::ACCESS_TYPE_MODULE)) {
            return $rules;
        }

        if ($element_schema['params']['required'] == true) {
            $rules[] = [$element_schema['params']['name'], 'relateCheckRequired'];
        }

        return [
            'rules'           => $rules,
            'attributeLabels' => [$element_schema['params']['name'] => $element_schema['title']],
        ];
    }

    private function SubModuleAR($element_schema)
    {
        $rules = [];

        return $rules;
    }

    private function AccessAR($element_schema)
    {
        $rules = [];

        return $rules;
    }

    private function PermissionAR($element_schema)
    {
        $rules = [];

        return $rules;
    }

    private function ActivityAR($element_schema)
    {
        $rules = [];

        return $rules;
    }

    /**
     * Создает правло для обезательного поля
     */
    private function RuleRequired($element_schema)
    {
        if ($element_schema['params']['required'] == true) {
            return [$element_schema['params']['name'], 'required'];
        }
    }

    /**
     * Создает правло для уникального поля
     */
    private function RuleUnique($element_schema)
    {
        if (isset($element_schema['params']['unique']) && $element_schema['params']['unique'] == true) {
            return [$element_schema['params']['name'], 'unique'];
        }
    }

    /**
     * Создает правло для даты/времени
     */
    private function RuleDateTime($element_schema)
    {
        if ($element_schema['params']['type'] == 'datetime') {
            return [$element_schema['params']['name'], 'date', 'allowEmpty' => false, 'format' => 'yyyy-MM-dd hh:mm:ss'];
        }
    }

    /**
     * Создает правло для проверки числового типа
     */
    private function RuleNumerical($element_schema)
    {
        $rules = [$element_schema['params']['name'], 'numerical'];

        if ($element_schema['params']['decimal'] === null) {
            $rules['integerOnly'] = true;
            $rules['max'] = 2147483647;
            $rules['min'] = -2147483648;
        } else {
            $rules['integerOnly'] = false;
            $rules['max'] = 99999999999;
            $rules['min'] = -99999999999;
        }

        //if($element_schema['params']['maxLength']) $rules['max'] = $element_schema['params']['maxLength'];
        //if($element_schema['params']['minLength']) $rules['min'] = $element_schema['params']['minLength'];

        return $rules;
    }

    /**
     * Создает правло для проверки длины символов
     */
    private function RuleLength($element_schema)
    {
        $rules = [$element_schema['params']['name'], 'length'];

        if ($element_schema['params']['maxLength']) {
            $rules['max'] = $element_schema['params']['maxLength'];
        }
        if ($element_schema['params']['minLength']) {
            $rules['min'] = $element_schema['params']['minLength'];
        }

        return $rules;
    }

    /**
     * Возвращает тип группы для фильтра
     */
    public function getFilterGroup($field_type)
    {
        if (!empty($this->_fields[$field_type]['params']['filter_group'])) {
            return $this->_fields[$field_type]['params']['filter_group'];
        }
    }

    /**
     * Возвращает статус поля по его типу для отображения в сортировке в ProcessView
     */
    public function getEnabledProcessViewGroup($field_type)
    {
        if (array_key_exists($field_type, $this->_fields)) {
            return $this->_fields[$field_type]['process_view_group'];
        }

        return false;
    }

}

