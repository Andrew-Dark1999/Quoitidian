<?php

class ExtensionCopyModel extends ActiveRecord
{

    const MODULE_USERS = 1;
    //    const MODULE_USERS_GROUP    = 2;
    const MODULE_PERMISSION = 3;
    const MODULE_ROLES = 4;
    const MODULE_STAFF = 5;
    const MODULE_PARTICIPANT = 6;
    const MODULE_TASKS = 7;
    const MODULE_REPORTS = 8;
    const MODULE_PROCESS = 9;
    const MODULE_PROJECTS = 10;
    const MODULE_DOCUMENTS = 11;
    const MODULE_NOTIFICATION = 12;
    const MODULE_COMMUNICATIONS = 13;
    const MODULE_CALLS = 14;
    const MODULE_WEBHOOK = 15;

    const IS_TEMPLATE_DISABLED = '0';
    const IS_TEMPLATE_ENABLE = '1';
    const IS_TEMPLATE_ENABLE_ONLY = '2';

    public $tableName = 'extension_copy';

    public $clone = 1;

    public $active = 1;

    public $constructor = 1;

    //добавляет id
    private $_add_id = false;

    //добавляет дату создания и дату изменения модулю в схему
    private $_add_date_create_entity = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function setAddDateCreateEntity($status)
    {
        $this->_add_date_create_entity = $status;

        return $this;
    }

    public function setAddId($status = true)
    {
        $this->_add_id = $status;

        return $this;
    }

    public function rules()
    {
        return [
            ['extension_id, prefix_name, title', 'required'],
            ['extension_id', 'numerical', 'integerOnly' => true],
            ['prefix_name', 'length', 'max' => 100],
            ['prefix_name', 'uniqueSubModule'],
            ['title', 'length', 'max' => 255],
            ['destroy, is_template, menu_display, data_if_participant, finished_object, show_blocks, calendar_view', 'length', 'max' => 1],
            ['date_create, date_edit, alias, schema, schema_fature, menu, clone, active, set_access, constructor, sort, be_parent_module, make_relate', 'safe'],
        ];
    }

    public function relations()
    {
        return [
            'extension'    => [self::BELONGS_TO, 'ExtensionModel', 'extension_id'],
            'moduleTables' => [self::HAS_MANY, 'moduleTablesModel', 'copy_id'],
            'participant'  => [self::HAS_MANY, 'ParticipantModel', 'copy_id'],
        ];
    }

    public function scopes()
    {
        return [
            "modulesActive" => [
                "condition" => "active =  '1'",
            ],
            "modulesUser"   => [
                "condition" => "copy_id NOT IN(" . join(',', [
                        self::MODULE_USERS,
                        self::MODULE_PERMISSION,
                        self::MODULE_ROLES,
                        self::MODULE_PARTICIPANT,
                    ]) . ")",
            ],
            "setAccess"     => [
                "condition" => "set_access = '1'",
            ],
        ];
    }

    public function setScopesWithOutId($id_list)
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'copy_id not in (' . implode(',', $id_list) . ')',
        ]);

        return $this;
    }

    public function changeConstructor($param)
    {
        if ($param === null) {
            return $this;
        }
        $this->getDbCriteria()->mergeWith([
            'condition' => 'constructor=:constructor',
            'params'    => [':constructor' => $param],
        ]);

        return $this;
    }

    public function setScopeMenu($menu)
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'menu=:menu',
            'params'    => [':menu' => $menu],
        ]);

        return $this;
    }

    public function attributeLabels()
    {
        return [
            'copy_id'       => 'Copy',
            'extension_id'  => 'Extension',
            'date_create'   => Yii::t('base', 'Date Create'),
            'date_edit'     => Yii::t('base', 'Date Edit'),
            'prefix_name'   => Yii::t('base', 'Prefix'),
            'title'         => Yii::t('base', 'Name'),
            'alias'         => Yii::t('base', 'Alias'),
            'description'   => Yii::t('base', 'Description'),
            'schema'        => Yii::t('base', 'Schema'),
            'schema_fature' => 'Schema Fature',
            'clone'         => Yii::t('base', 'Clone'),
            'active'        => 'Active',
        ];
    }

    public function isActive()
    {
        return ($this->active ? true : false);
    }

    public function uniqueSubModule()
    {
        if (!$this->isNewRecord) {
            return;
        }
        $data = $this->findAll([
            'condition' => 'extension_id=:extension_id AND prefix_name=:prefix_name',
            'params'    => [':extension_id' => $this->extension_id, ':prefix_name' => $this->prefix_name]
        ]);
        if (!empty($data)) {
            $this->addError('prefix_name', 'error');
        }

    }

    public function getModule($set_module_extension_copy = true, $return_this = false)
    {
        $module = Yii::app()->getModule($this->extension->name);

        if (empty($module)) {
            $module = $this->extension->getModule(($set_module_extension_copy ? $this : null));
        } elseif ($set_module_extension_copy) {
            $module->setExtensionCopy($this);
        }

        if ($return_this) {
            return $this;
        }

        return $module;
    }

    /**
     * getTableName - возвращает название таблицы
     *
     * @param null $last_prefix - используется при создании связаных таблиц
     * @param bool $add_table_prefix - добавление префикса БД
     * @param bool $set_module_extension_copy
     * @return string
     */
    public function getTableName($last_prefix = null, $add_table_prefix = true, $set_module_extension_copy = false)
    {
        if ($last_prefix !== null) {
            $last_prefix = '_' . $last_prefix;
        }

        if ($add_table_prefix == true) {
            $table_prefix = Yii::app()->db->tablePrefix;
        } else {
            $table_prefix = '';
        }

        $module = $this->getModule($set_module_extension_copy, false);
        if ($module->auto_table_name == true) {
            return $table_prefix . 'ms_' . Helper::strToLower($this->extension->name . '_' . $this->prefix_name . $last_prefix);
        } else {
            return $table_prefix . $module->getTableName() . $last_prefix;
        }
    }

    /**
     * getPkFieldName - возвращает название первичного поля
     *
     * @param bool $add_table_name
     * @param bool $add_table_prefix
     * @return string
     */
    public function getPkFieldName($add_table_name = false, $add_table_prefix = true)
    {
        $field_name = $this->prefix_name . '_id';

        if ($add_table_name) {
            $field_name = $this->getTableName(null, $add_table_prefix, false) . '.' . $field_name;
        }

        return $field_name;
    }

    protected function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->date_create = new CDbExpression('now()');

            // проверка префикса модуля
            $prefix_name = $this->prefix_name;
            for ($lich = 1; $lich < 1000; $lich++) {
                if (ExtensionCopyModel::model()->count('prefix_name = "' . $prefix_name . '"')) {
                    $prefix_name = $this->prefix_name . $lich;
                } else {
                    break;
                }
            }
            $this->prefix_name = $prefix_name;

        } else {
            $this->date_edit = new CDbExpression('now()');
        }

        return true;
    }

    /**
     *  проверяет существование поля(ей) в схеме
     */
    public function isSetFieldInSchema(array $field_names)
    {
        $find_fields = 0;
        $schema_parse = $this->getSchemaParse();
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && in_array($value['field']['params']['name'], $field_names)) {
                $find_fields++;
            }
        }

        return count($field_names) === $find_fields;
    }

    /**
     *   Возвращает схему
     */
    public function getSchema($only_from_db = false)
    {
        $schema = $this->getModule(false)->getSchema($this);

        if ($only_from_db) {
            return $schema;
        }

        if ($this->_add_id) {
            $schema =
                array_merge(
                    [
                        Schema::getInstance()->generateDefaultSchema(
                            [
                                'block' =>
                                    [
                                        'type'     => 'block',
                                        'params'   => [
                                            'title'             => '',
                                            'unique_index'      => md5(date('YmdHis') . mt_rand(1, 1000) . 'date_id'),
                                            'edit_view_display' => false,
                                            'header_hidden'     => true,
                                        ],
                                        'elements' => [
                                            [
                                                'block_panel' => [
                                                    [
                                                        'type'     => 'block_panel',
                                                        'params'   => ['count_panels' => 1],
                                                        'elements' => [
                                                            [
                                                                'type'     => 'panel',
                                                                'params'   => [
                                                                    'active_count_select_fields'    => 1,
                                                                    'process_view_group'            => false,
                                                                    'c_count_select_fields_display' => false,
                                                                    'c_list_view_display'           => false,
                                                                    'c_process_view_group_display'  => false,
                                                                    'destroy'                       => false,
                                                                    'inline_edit'                   => false,
                                                                ],
                                                                'elements' => [
                                                                    [
                                                                        'field' => [
                                                                            [
                                                                                'type'   => 'label',
                                                                                'params' => ['title' => ''],
                                                                            ],
                                                                            [
                                                                                'type'     => 'block_field_type',
                                                                                'params'   => ['count_edit' => 1],
                                                                                'elements' => [
                                                                                    [
                                                                                        'type'   => 'edit',
                                                                                        'title'  => Yii::t('base', 'ID'),
                                                                                        'params' => [
                                                                                            'is_id_field'            => true,
                                                                                            'title'                  => Yii::t('base', 'ID'),
                                                                                            'name'                   => $this->prefix_name . '_id',
                                                                                            'type'                   => 'numeric',
                                                                                            'group_index'            => -3,
                                                                                            'edit_view_show'         => false,
                                                                                            'list_view_def_not_show' => true,
                                                                                            'c_types_list_index'     => Fields::TYPES_LIST_INDEX_DEFAULT,
                                                                                        ]
                                                                                    ],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],

                                        ],
                                    ],
                            ]
                        )
                    ],
                    $schema
                );
        }

        if ($this->_add_date_create_entity) {
            $schema =
                array_merge(
                    [
                        Schema::getInstance()->generateDefaultSchema(
                            [
                                'block' =>
                                    [
                                        'type'     => 'block',
                                        'params'   => [
                                            'title'             => '',
                                            'unique_index'      => md5(date('YmdHis') . mt_rand(1, 1000) . 'date_create_edit'),
                                            'edit_view_display' => false,
                                            'header_hidden'     => true,
                                        ],
                                        'elements' => [
                                            [
                                                'block_panel' => [
                                                    [
                                                        'type'     => 'block_panel',
                                                        'params'   => ['count_panels' => 1],
                                                        'elements' => [
                                                            [
                                                                'type'     => 'panel',
                                                                'params'   => [
                                                                    'active_count_select_fields'    => 2,
                                                                    'process_view_group'            => false,
                                                                    'c_count_select_fields_display' => false,
                                                                    'c_list_view_display'           => false,
                                                                    'c_process_view_group_display'  => false,
                                                                    'destroy'                       => false,
                                                                    'inline_edit'                   => false,
                                                                ],
                                                                'elements' => [
                                                                    [
                                                                        'field' => [
                                                                            [
                                                                                'type'   => 'label',
                                                                                'params' => ['title' => ''],
                                                                            ],
                                                                            [
                                                                                'type'     => 'block_field_type',
                                                                                'params'   => ['count_edit' => 2],
                                                                                'elements' => [
                                                                                    [
                                                                                        'type'   => 'edit',
                                                                                        'title'  => Yii::t('base', 'Date created'),
                                                                                        'params' => [
                                                                                            'title'                  => Yii::t('base', 'Date created'),
                                                                                            'name'                   => 'date_create',
                                                                                            'type'                   => 'datetime',
                                                                                            'group_index'            => -1,
                                                                                            'edit_view_show'         => false,
                                                                                            'list_view_def_not_show' => true,
                                                                                            'c_types_list_index'     => Fields::TYPES_LIST_INDEX_DEFAULT,
                                                                                        ]
                                                                                    ],
                                                                                    [
                                                                                        'edit' => [
                                                                                            'type'   => 'edit',
                                                                                            'title'  => Yii::t('base', 'Date edit'),
                                                                                            'params' => [
                                                                                                'title'                  => Yii::t('base', 'Date edit'),
                                                                                                'name'                   => 'date_edit',
                                                                                                'type'                   => 'datetime',
                                                                                                'edit_view_show'         => false,
                                                                                                'list_view_def_not_show' => true,
                                                                                                'group_index'            => -2,
                                                                                                'c_types_list_index'     => Fields::TYPES_LIST_INDEX_DEFAULT,
                                                                                            ],
                                                                                        ],
                                                                                    ],
                                                                                ],
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],

                                        ],
                                    ],
                            ]
                        )
                    ],
                    $schema
                );
        }

        return $schema;
    }

    /**
     * getPrimaryField - Вовращает параметры схемы первичного поля (Название)
     */
    public function getPrimaryField($schema_parse = null, $return_first_field = true)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        $params = [];

        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && (boolean)$value['field']['params']['is_primary'] === true) {
                $params[] = $value['field'];
            }
        }
        if (empty($params)) {
            $params = null;
        } else {
            if ($return_first_field) {
                $params = $params[0];
            }
        }

        return $params;
    }

    /**
     * getParentPrimaryCopyId - Возвращает copy_id родительского подуля, связаного через поле Название
     */
    public function getParentPrimaryCopyId()
    {
        $relate_tables = ModuleTablesModel::model()->findAll([
            'condition' => 'relate_copy_id=:relate_copy_id AND `type`="relate_module_one"',
            'params'    => [
                ':relate_copy_id' => $this->copy_id,
            ]
        ]);

        if (empty($relate_tables)) {
            return;
        }

        foreach ($relate_tables as $relate_table) {
            $parent_extension_copy = \ExtensionCopyModel::model()->findByPk($relate_table->copy_id);
            $parent_field_params = $parent_extension_copy->getPrimaryField();
            if (empty($parent_field_params) || $parent_field_params['params']['relate_module_copy_id'] != $this->copy_id) {
                continue;
            }

            return $relate_table->copy_id;
        }
    }

    /**
     * Возращает параметры схемы поля Ответственный
     */
    public function getResponsibleField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'relate_participant' && $value['field']['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                return $value['field'];
            }
        }
    }

    /**
     * Вовращает параметры схемы блока Участники
     */
    public function getParticipantField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }

        if (!isset($schema_parse['elements'])) {
            return;
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'relate_participant' && $value['field']['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
                return $value['field'];
            }
        }
    }

    /**
     * Вовращает присутствие типа "Ответственный"
     */
    public function isResponsible($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        $responsible_schema = $this->getResponsibleField($schema_parse);

        if (!empty($responsible_schema)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Вовращает присутствие типа "Участники"
     */
    public function isParticipant($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        $responsible_schema = $this->getParticipantField($schema_parse);

        if (!empty($responsible_schema)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Вовращает параметры схемы блока Вложения
     */
    public function getAttachmentsField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'attachments' && $value['field']['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_ATTACHMENTS) {
                return $value['field'];
            }
        }
    }

    /**
     * Вовращает параметры схемы блока Активность
     */
    public function getActivityField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'activity') {
                return $value['field'];
            }
        }
    }

    /**
     * @param null $schema_parse
     * @return mixed
     */
    public function getDateEndingField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'datetime' && $value['field']['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                return $value['field'];
            }
        }
    }

    /**
     * @param null $schema_parse
     * @return mixed
     */
    public function getStatusField($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && $value['field']['params']['type'] == 'select' && $value['field']['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS) {
                return $value['field'];
            }
        }
    }

    /**
     *   Возвращает присутсве аватара в модуле на основании наличия блока Контакты,  Участники или ответсвенного
     */
    public function isAvatar($schema = null)
    {
        if (empty($schema)) {
            $schema = $this->getSchema();
        }

        $schema_model = new SchemaOperation();

        return ($schema_model->beBlockPanelContact($schema) ? true : false);
    }

    /**
     *   Возвращает роспарсенную схему полей
     */
    public function getSchemaParse($schema = [], $exception_param_list = [], $exception_name_list = [], $use_cache = true)
    {
        if (empty($schema)) {
            $schema = $this->getSchema();
        }

        $schema = SchemaOperation::getInstance()->getSchemaParse($this->copy_id, $schema, $exception_name_list, null, $use_cache);

        if (!empty($exception_param_list)) {
            $schema = \SchemaOperation::getSchemaParsedWithOutElements($schema, $exception_param_list);
        }

        return $schema;
    }

    /**
     *  Возвращает схему полей (ветка params)
     */
    public function getSchemaFields()
    {
        $schema = $this->getSchemaParse();

        $result = [];

        if (empty($schema) || !isset($schema['elements'])) {
            return [];
        }
        foreach ($schema['elements'] as $element) {
            if (isset($element['field'])) {
                $result[] = $element['field'];
            }
        }

        return $result;
    }

    /**
     *   Возвращает из схемы данные блоков
     */
    public function getSchemaBlocksData($block_unique_index = null, $append_field_name_list = false)
    {
        $result = [];

        $schema = $this->getSchema();
        if (empty($schema)) {
            return $result;
        }

        foreach ($schema as $node) {
            if (empty($node['type']) || $node['type'] == $node['elements'][0]['type']) {
                continue;
            }
            if (empty($node['elements'][0]['type']) || $node['elements'][0]['type'] != 'block_panel') {
                continue;
            }
            if ($node['params']['header_hidden']) {
                continue;
            }
            if ($block_unique_index) {
                if ($node['params']['unique_index'] != $block_unique_index) {
                    continue;
                }
            }

            $r = [
                'unique_index' => $node['params']['unique_index'],
                'title'        => $node['params']['title']
            ];

            if ($append_field_name_list) {
                $r['field_name_list'] = $this->getFieldsSchemaList($node);
            }
            $result[] = $r;

        }

        return $result;
    }

    /**
     *   Возвращает из схемы данные поля типа "Показать блок"
     */
    public function getFieldBlockData($schema = [])
    {
        if (empty($schema)) {
            $schema = $this->getSchemaParse();
        }

        $result = false;

        if (!empty($schema)) {
            foreach ($schema['elements'] as $value) {
                if (isset($value['field']['params']['type'])) {
                    if ($value['field']['params']['type'] == 'display_block') {
                        $result = [
                            'name'  => $value['field']['params']['name'],
                            'title' => $value['field']['title'],
                        ];
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     *  Возвращает роспарсенную схему заданого типа данных по названию поля (field) или copy_id  для sub_module
     *
     * @param $field_name - Название поля или copy_id связаного модуля, если ищем по sub_module
     * @param array $schema_parse
     * @return array|mixed
     */
    public function getFieldSchemaParams($field_name, $schema_parse = [])
    {
        $result = [];

        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse();
        }
        foreach ($schema_parse['elements'] as $value) {
            if (is_array($field_name)) {
                if (isset($value['field']) && in_array($value['field']['params']['name'], $field_name)) {
                    $result[$value['field']['params']['name']] = $value['field']['params'];
                    continue;
                }
                if (isset($value['sub_module']) && in_array($value['sub_module']['params']['name'], $field_name)) {
                    $result[$value['field']['params']['name']] = $value['field']['params'];
                    continue;
                }
            } else {
                if (isset($value['field']) && $value['field']['params']['name'] == $field_name) {
                    $result[] = $value['field'];
                    continue;
                }
                if (isset($value['sub_module']) && $value['sub_module']['params']['relate_module_copy_id'] == $field_name) {
                    $result[] = $value['sub_module'];
                    continue;
                }
            }
        }

        if (is_array($field_name)) {
            return $result;
        } else {
            if (!empty($result)) {
                return $result[0];
            }
        }
    }

    /**
     *  Возвращает первую (если полей с данным типом больше одного) роспарсенную схему заданого типа данных
     *
     * @param string|array $field_type
     * @param null $schema_parse
     * @param bool $return_first_only
     * @return array|null
     */
    public function getFieldSchemaParamsByType($field_type, $schema_parse = null, $return_first_only = true)
    {
        if ($schema_parse === null) {
            $schema_parse = $this->getSchemaParse();
        }
        $result = [];
        foreach ($schema_parse['elements'] as $value) {
            if (
                (is_array($field_type) && isset($value['field']) && in_array($value['field']['params']['type'], $field_type))
                ||
                (!is_array($field_type) && isset($value['field']) && $value['field']['params']['type'] == $field_type)
            ) {
                if ($return_first_only) {
                    return $value['field'];
                } else {
                    $result[] = $value['field'];
                }
            }
        }

        return (!empty($result) ? $result : null);
    }

    /**
     * Возвращает массив полей параметров схемы, что соответсвуют питам изобращения
     *
     * @return array
     */
    public function getFileFieldSchemaParams($schema_parse = null)
    {
        $result = [];

        $schemaByName = $this->getFieldSchemaParams(['ehc_image1'], $schema_parse);
        $schemaByType = $this->getFieldSchemaParamsByType(['file', 'file_image', 'attachments'], $schema_parse, false);

        foreach (['schemaByName', 'schemaByType'] as $proterty) {
            $schemaProperties = ${$proterty};
            if (!$schemaProperties) {
                continue;
            }

            foreach ($schemaProperties as $schemaProperty) {
                if (isset($schemaProperty['params'])) {
                    $result[$schemaProperty['params']['name']] = $schemaProperty['params'];
                } else {
                    $result[$schemaProperty['name']] = $schemaProperty;
                }
            }
        }

        return $result;
    }

    /**
     *  Возвращает параметры всех сабмодулей
     */
    public function getSubmoduleParamsList($schema_parse = null)
    {
        if ($schema_parse === null) {
            $schema_parse = $this->getSchemaParse();
        }

        $result = [];

        foreach ($schema_parse['elements'] as $params) {
            if (array_key_exists('sub_module', $params)) {
                $result[] = $params['sub_module'];
            }
        }

        return $result;
    }

    /**
     *  Возвращает список полей схемы
     */
    public function getFieldsSchemaList($schema = [])
    {
        $result = [];

        if (empty($schema)) {
            $schema = $this->getSchemaParse();
        }

        if (empty($schema['elements'])) {
            return $result;
        }

        foreach ($schema['elements'] as $element) {
            if (array_key_exists('type', $element) && $element['type'] == 'edit') {
                $result[$element['params']['name']] = $element;
            } else {
                if (array_key_exists('field', $element)) {
                    $result[$element['field']['params']['name']] = $element['field'];
                } else {
                    if (array_key_exists('elements', $element)) {
                        return $this->getFieldsSchemaList($element);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Поля, которые исключаем из построения схемы. Все поля блоков кроме указанного
     */
    public function getFieldsExceptBlock($block_unique_index)
    {

        $result = [];
        $schema = $this->getSchema();

        if (!empty($schema)) {
            foreach ($schema as $value) {
                if (isset($value['type'])) {
                    if ($value['type'] == 'block') {
                        if ($value['elements'][0]['type'] == 'block_panel' && !$value['params']['header_hidden']) {
                            if ($value['params']['unique_index'] != $block_unique_index) {

                                $fields = SchemaOperation::getInstance()->getSchemaParse($this->copy_id, $value['elements']);

                                if (!empty($fields['elements'])) {
                                    foreach ($fields['elements'] as $v) {
                                        if (isset($v['field']['params']['name'])) {
                                            $result [] = $v['field']['params']['name'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;

    }

    /**
     *  Возвращает роспарсенную схему первого поля
     */
    public function getFirstFieldSchemaParams()
    {
        $schema_parse = $this->getSchemaParse();
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field'])) {
                return $value['field'];
            }
        }
    }

    /**
     *   Возвращает схему полей для фильтра
     */
    public function getFieldSchemaForFilter($field_name = null, $prefix = null)
    {
        $schema_parse = SchemaOperation::getInstance()->getSchemaParse($this->copy_id, $this->getSchema(), [], 'field');

        $group_index = null;
        $fields = [];
        if (!empty($schema_parse)) {
            foreach ($schema_parse as $value) {
                if ($value['params']['filter_enabled'] == false) {
                    continue;
                }
                $fields[] = $value;
            }
        }

        $fields = array_merge(
            [
                Schema::getInstance()->generateDefaultSchema(
                    [
                        'edit' => [
                            'type'   => 'edit',
                            'title'  => Yii::t('base', 'Date created'),
                            'params' => [
                                'title'       => Yii::t('base', 'Date created'),
                                'name'        => 'date_create',
                                'type'        => 'datetime',
                                'group_index' => 1889
                            ],
                        ],
                    ]
                )
            ],
            [
                Schema::getInstance()->generateDefaultSchema(
                    [
                        'edit' => [
                            'type'   => 'edit',
                            'title'  => Yii::t('base', 'Date edit'),
                            'params' => [
                                'title'       => Yii::t('base', 'Date edit'),
                                'name'        => 'date_edit',
                                'type'        => 'datetime',
                                'group_index' => 1890
                            ],
                        ],
                    ]
                )
            ],
            $fields
        );

        if ($field_name !== null) {
            $field_name = explode(',', $field_name);
            $field_name = array_map(function ($name) use ($prefix) {
                if ($prefix) {
                    return str_replace('_' . $prefix, '', $name);
                }

                return $name;
            }, $field_name);

            foreach ($fields as $field) {
                if (in_array($field['params']['name'], $field_name)) {
                    return $field;
                }
            }
        }

        return $fields;
    }

    /**
     * возвращает список модулей
     */
    public static function getUsersModule()
    {
        $module = ExtensionCopyModel::model()
            ->modulesActive()
            ->setScopeMenu('main_top')
            ->findAll(['condition' => '`schema` != "" OR `schema` is not NULL', 'order' => 'sort asc']);

        return $module;
    }

    /**
     * возвращает первый доступный пользователю модуль
     */
    public static function getFirstUsersModule()
    {
        $modules = self::getUsersModule();
        if (empty($modules)) {
            return;
        }
        foreach ($modules as $data) {
            if (Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $data['copy_id'], Access::ACCESS_TYPE_MODULE)) {
                return $data;
            }
        }
    }

    /**
     * возвращает параметры схемы поля для отображения в типе relate
     */
    public function getFirstFieldParamsForRelate($schema = null)
    {
        $schema_parse = $this->getSchemaParse($schema);
        $field_params = [];
        foreach ($schema_parse['elements'] as $params) {
            if (!isset($params['field'])) {
                continue;
            }
            if (((boolean)$params['field']['params']['is_primary'] == true && ($params['field']['params']['type'] == 'display' || $params['field']['params']['type'] == 'relate_string')) &&
                ($params['field']['params']['type_view'] == Fields::TYPE_VIEW_DEFAULT || $params['field']['params']['type_view'] == Fields::TYPE_VIEW_EDIT_HIDDEN)) {
                $field_params = $params['field'];
                break;
            }
        }
        if (empty($field_params)) {
            foreach ($schema_parse['elements'] as $params) {
                if (!isset($params['field'])) {
                    continue;
                }
                if (($params['field']['params']['type'] == 'string' || $params['field']['params']['type'] == 'numeric') &&
                    ($params['field']['params']['type_view'] == Fields::TYPE_VIEW_DEFAULT || $params['field']['params']['type_view'] == Fields::TYPE_VIEW_EDIT_HIDDEN)
                ) {
                    $field_params = $params['field'];
                    break;
                }
            }
        }

        return $field_params;

    }

    /**
     * визвращает наличие шаблона в модуля
     */
    public function isSetIsTemplate()
    {
        return (boolean)$this->is_template;
    }

    public function getIsTemplate()
    {
        return (string)$this->is_template;
    }

    /**
     * возвращает параметр "Показывать все блоки"
     */
    public function isShowAllBlocks()
    {
        return (boolean)$this->show_blocks;
    }

    /**
     * getModulesList - возвращает список модулей.
     */
    public static function getModulesList($primary_copy_id = null)
    {
        $extension_models = \ExtensionCopyModel::model()
            ->modulesActive()
            ->setAccess()
            ->setScopesWithOutId([\ExtensionCopyModel::MODULE_PROCESS, \ExtensionCopyModel::MODULE_REPORTS])
            ->findAll([
                'order' => 'title',
            ]);

        if (!$extension_models) {
            return [];
        }

        $result = [];

        foreach ($extension_models as $extension_copy) {
            if (!empty($primary_copy_id)) {
                $module_table_data = ModuleTablesModel::getRelateModuleTableData($primary_copy_id, $extension_copy->copy_id);
                if (empty($module_table_data)) {
                    continue;
                }
            }

            $result[$extension_copy['copy_id']] = $extension_copy['title'];
        }

        return $result;
    }

    /**
     * getModulesList - возвращает список публичных модулей.
     */
    public static function getPublicModuleList()
    {
        $result = [];

        $extension_models = \ExtensionCopyModel::model()
            ->modulesActive()
            ->setAccess()
            ->findAll([
                'order' => 'title',
            ]);

        if (!$extension_models) {
            return $result;
        }

        foreach ($extension_models as $extension_copy) {
            $result[$extension_copy['copy_id']] = $extension_copy['title'];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function getRawDataIfParticipant()
    {
        return $this->data_if_participant == "1" ? true : false;
    }

    /**
     * dataIfParticipant - возвращает статус параметра: отображать данные только участников
     *
     * @return bool
     */
    public function dataIfParticipant()
    {
        $dataIfPatticipant = ($this->data_if_participant == "1" ? true : false);

        if($dataIfPatticipant) {
            $dataIfPatticipant = \Access::checkAccess(\PermissionModel::PERMISSION_DATA_ALL_PARTICIPANTS, $this->copy_id, Access::ACCESS_TYPE_MODULE) ? false : true;
        }

        return $dataIfPatticipant;
    }

    /**
     * getPrimaryViewFieldName - возращает поле как первичное Модуля для отбражения с линком в списках данных
     */
    public function getPrimaryViewFieldName($schema_parse = null)
    {
        if (empty($schema_parse)) {
            $schema_parse = $this->getSchemaParse($this->getSchema());
        }

        $mt_params = $this->getPrimaryField($schema_parse);
        if (!empty($mt_params) && in_array($mt_params['params']['type'], [Fields::MFT_DISPLAY, Fields::MFT_RELATE_STRING])) {
            return $mt_params['params']['name'];
        }

        $field_types = [Fields::MFT_STRING, Fields::MFT_NUMERIC];
        $field_type_view = [Fields::TYPE_VIEW_DEFAULT, Fields::TYPE_VIEW_EDIT_HIDDEN];

        foreach ($schema_parse['elements'] as $element) {
            if (!isset($element['field'])) {
                continue;
            }

            if (in_array($element['field']['params']['type'], $field_types) && in_array($element['field']['params']['type_view'], $field_type_view)) {
                return $element['field']['params']['name'];
            }
        }
    }

    /**
     * isSetValueByKey - проверяет наличие значения по его ключу
     *
     * @param $schema
     * @param $key
     * @param $value
     * @return bool
     */
    public function isSetValueByKey($schema, $key, $value)
    {
        if ((!empty($schema[$key])) && ($schema[$key] == $value)) {
            return true;
        } else {
            foreach ($schema as $block) {
                if (is_array($block)) {
                    if ($this->isSetValueByKey($block, $key, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * isAutoEntityTitle - автоматическое формирование названия сущности у модуля
     *
     * @return boolean
     */
    public function isAutoEntityTitle()
    {
        $primary_field_schema = $this->getPrimaryField();
        if (array_key_exists('name_generate', $primary_field_schema['params'])) {
            return (boolean)$primary_field_schema['params']['name_generate'];
        } else {
            return false;
        }
    }




    //проверяет налячие связи с Коммуникациями в схеме модуля
    /*
    public function hasCommunicationsSDM($copy_id = null){
        if($copy_id !== null){
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        } else {
            $extension_copy = $this;
        }

        $schema_list = $extension_copy->getFieldSchemaParamsByType(\Fields::MFT_RELATE, $extension_copy->getSchemaParse(), false);
        if($schema_list == false){
            return false;
        }

        foreach($schema_list as $schema){
            if($schema['params']['relate_module_copy_id'] == ExtensionCopyModel::MODULE_COMMUNICATIONS){
                return true;
            }
        }

        return false;
    }
    */

    //проверяет налячие связи с Коммуникациями в схеме модуля
    public function hasCommunicationsSM($copy_id = null)
    {
        if ($copy_id !== null) {
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        } else {
            $extension_copy = $this;
        }

        $sm_params = (new SchemaOperation())->getSubModuleSchema($extension_copy->getSchema(), ExtensionCopyModel::MODULE_COMMUNICATIONS);
        if ($sm_params) {
            return true;
        }

        return false;
    }

    /**
     * проверяет есть ли в модуле блок участники и связан ли модуль с Коммуникациями
     *
     * @param null $copy_id
     * @return bool
     */
    public function useCommunicationFunctional($copy_id = null)
    {
        if ($copy_id == ExtensionCopyModel::MODULE_COMMUNICATIONS) {
            return true;
        }

        if ($copy_id !== null) {
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        } else {
            $extension_copy = $this;
        }

        if ($extension_copy->hasCommunicationsSM() == false) {
            return false;
        }

        if ($extension_copy->getActivityField() == false) {
            return false;
        }

        return true;
    }

    /**
     * isCalendarView - возвращает статус Календаря (CalendarView) - включен/отключен
     *
     * @return bool
     */
    public function isCalendarView()
    {
        return (boolean)$this->calendar_view;
    }

}

