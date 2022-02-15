<?php
/**
 * FilterModel
 *
 * @author Alex R.
 */

class FilterModel extends ActiveRecord
{

    // views
    const VIEW_PERSONAL = 'personal';
    const VIEW_GENERAL = 'general';

    // filter types
    const FT_BEGIN_WITH = 'begin_with';
    const FT_CORRESPONDS = 'corresponds';
    const FT_CORRESPONDS_NOT = 'corresponds_not';
    const FT_CORRESPONDS_RP = 'corresponds_rp';
    const FT_CONTAINS = 'contains';

    const FT_MORE = 'more';
    const FT_LESS = 'less';
    const FT_END = 'end';
    const FT_EQUAL_NOT = 'equal_not';

    const FT_DATE_FOR_TODAY = 'date_for_today';
    const FT_DATE_FOR_7_DAYS = 'date_for_7_days';
    const FT_DATE_FOR_30_DAYS = 'date_for_30_days';
    const FT_DATE_CURRENT_MONTH = 'date_current_month';
    const FT_DATE_PAST_MONTH = 'date_past_month';
    const FT_DATE_CURRENT_YEAR = 'date_current_year';
    const FT_DATE_PAST_YEAR = 'date_past_year';
    const FT_DATE_AFTER = 'date_after';
    const FT_DATE_TO = 'date_to';
    const FT_DATE_PERIOD = 'date_period';
    const FT_DATE_AFTER_CURRENT = 'date_after_current';
    const FT_DATE_PRIOR_TO_CURRENT = 'date_prior_current';

    private $_extension_copy;

    private $_add_table_name = true;

    private $_add_table_alias = false;

    private $_prepared_query = [
        'having'     => [],
        'conditions' => [],
        'params'     => [],
    ];

    private static $_key_index = 1;

    private $_field_name_index = '';

    private $_there_is_participant = false;

    private $_use_full_field_name_real = false; // true - в качестве названия поля (тип relate) использовать название таблицы модуля

    public $tableName = 'filter';

    public $view = self::VIEW_PERSONAL;

    public static $_access_to_change = true;

    private function clear()
    {
        $this->_extension_copy = null;
        $this->_add_table_name = true;
        $this->_add_table_alias = false;
        $this->_prepared_query = [
            'having'     => [],
            'conditions' => [],
            'params'     => [],
        ];
        $this->_field_name_index = '';
        $this->_there_is_participant = false;
        $this->_use_full_field_name_real = false;
        $this->view = self::VIEW_PERSONAL;
    }

    public static function model($className = __CLASS__)
    {
        $model = parent::model($className);
        $model->clear();

        return $model;
    }

    public function rules()
    {
        return [
            ['title, copy_id', 'required'],
            ['params', 'paramsValidate'],
            ['name', 'nameValidate'],
            ['view', 'accessToChange'],
            ['name, view', 'length', 'max' => 255],
            ['title', 'length', 'max' => 100],
            ['params', 'length', 'max' => 5000],
            ['id, user_create, copy_id, name', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title'   => Yii::t('base', 'Filter name'),
            'copy_id' => Yii::t('base', 'Module code'),
        ];
    }

    public function relations()
    {
        return [
            'extensionCopy' => [self::BELONGS_TO, 'ExtensionCopyModel', 'copy_id'],
        ];
    }

    public function scopes()
    {
        return [
            "onlyPersonal" => [
                "condition" => "(user_create = " . WebUser::getUserId() . " OR `view` = '" . self::VIEW_GENERAL . "')",
            ],

        ];
    }

    public function beforeValidate()
    {
        $this->name = Translit::forDataBase($this->title);

        if (!empty($this->params)) {
            $this->params = json_encode($this->params);
        } else {
            $this->params = '';
        }

        if (empty($errors)) {
            if ($this->getIsNewRecord() == false && !($this instanceof Reports\models\ReportsFilterModel)) {
                if ($this->view == static::VIEW_PERSONAL) {
                    $filter_data = \DataModel::getInstance()->setFrom($this->tableName())->setWhere('filter_id = ' . $this->filter_id)->findRow();
                    if ($filter_data['view'] == static::VIEW_GENERAL) {
                        $this->user_create = \WebUser::getUserId();
                    }
                } elseif ($this->view == static::VIEW_GENERAL) {
                    $filter_data = \DataModel::getInstance()->setFrom($this->tableName())->setWhere('filter_id = ' . $this->filter_id)->findRow();
                    if ($filter_data['view'] == static::VIEW_PERSONAL && $filter_data['user_create'] != \WebUser::getUserId()) {
                        $this->view = static::VIEW_PERSONAL;
                    }
                }
            }
        }

        return true;
    }

    public function beforeSave()
    {
        if ($this->getIsNewRecord()) {
            $this->user_create = \WebUser::getUserId();
        }

        return true;
    }

    public function setFieldNameIndex($index_name)
    {
        $this->_field_name_index = $index_name;

        return $this;
    }

    public function setUseFullFieldNameReal($use_full_field_name_real)
    {
        $this->_use_full_field_name_real = $use_full_field_name_real;

        return $this;
    }

    public function paramsValidate($argument, $params)
    {
        if (empty($this->{$argument})) {
            $this->addError($argument, Yii::t('messages', 'Do not set the filter options'));
        }
    }

    public function nameValidate($argument, $params)
    {
        if (FilterVirtualModel::isSetFilter($this->{$argument})) {
            $this->addError($argument, Yii::t('messages', 'The name of the filter system reserved'));
        }
    }

    public function accessToChange($argument, $params)
    {
        if ($this->getAccessToChange() == false) {
            $this->addError($argument, Yii::t('messages', 'Access denied') . '!');
        }

        return true;
    }

    public function setCopyId($copy_id)
    {
        $this->copy_id = $copy_id;

        return $this;
    }

    public function setAddTableName($param)
    {
        $this->_add_table_name = $param;

        return $this;
    }

    public function setAddTableAlias($alias)
    {
        $this->_add_table_alias = $alias;

        return $this;
    }

    public function setParams($params, $json_encode = false)
    {
        if ($json_encode) {
            $params = json_encode($params);
        }
        $this->params = $params;

        return $this;
    }

    public function getParams()
    {
        return json_decode($this->params, true);
    }

    public function getThereIsParticipant()
    {
        return $this->_there_is_participant;
    }

    /**
     * @param $fieldAlias
     * @param null $prefix
     * @return string|string[]
     */
    private function getFieldNameByAlias($fieldAlias, $prefix = null)
    {
        if ($prefix === null) {
            return $fieldAlias;
        }

        return str_replace('_' . $prefix, '', $fieldAlias);
    }


    public function prepareQuery()
    {
        $this->_extension_copy = \ExtensionCopyModel::model()->findByPk($this->copy_id);

        $params = [];
        $params_db = json_decode($this->params, true);

        foreach ($params_db as $value) {
            $field_names = explode(',', $value['name']);
            foreach ($field_names as $field_name) {
                $field_schema = $this->_extension_copy->getFieldSchemaParams($this->getFieldNameByAlias($field_name, 'block_participant'));
                if (!$field_schema) {
                    continue;
                }
                $table_name = $this->_extension_copy->getTableName();

                if ($field_name == 'activity_last_date') {
                    $table_name = '';
                }

                switch ($value['condition']) {
                    case self::FT_BEGIN_WITH :
                        $v = $this->qmLike($table_name, $field_name, $value['condition_value'][0], $field_schema, 'left');
                        if ($v !== false) {
                            $this->addConditionToPreparedQuery($v, $field_schema);
                        }
                        break;
                    case self::FT_CORRESPONDS :
                        $this->addConditionToPreparedQuery($this->qmCorrespondsTo($table_name, $field_name, $value['condition_value'][0], $field_schema), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value['condition_value'][0];
                        break;
                    case self::FT_CORRESPONDS_NOT :
                        $this->addConditionToPreparedQuery($this->qmNotCorrespondsTo($table_name, $field_name, $value['condition_value'][0], $field_schema), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value['condition_value'][0];
                        break;
                    case self::FT_CORRESPONDS_RP :
                        $this->addConditionToPreparedQuery($this->qmCorrespondsTo($table_name, $field_name, $value['condition_value'], $field_schema), $field_schema);
                        $params[':ug_id' . self::$_key_index] = $value['condition_value'][0];
                        $params[':ug_type' . self::$_key_index] = $value['condition_value'][1];
                        break;
                    case self::FT_CONTAINS :
                        $v = $this->qmLike($table_name, $field_name, $value['condition_value'][0], $field_schema, 'inner');
                        if ($v !== false) {
                            $this->addConditionToPreparedQuery($v, $field_schema);
                        }
                        break;
                    case self::FT_MORE :
                    case self::FT_LESS :
                    case self::FT_EQUAL_NOT :
                        $v = $this->qmCompare($table_name, $field_name, $value, $field_schema);
                        if ($v !== false) {
                            $this->addConditionToPreparedQuery($v, $field_schema);
                            $value1 = $value['condition_value'][0];
                            if ($value1 === '') {
                                $value1 = 0;
                            }
                            $params[':' . $field_name . self::$_key_index] = $value1;
                        }
                        break;
                    case self::FT_DATE_FOR_7_DAYS :
                        $value1 = date('Y-m-d 00:00:00', strtotime('-7 days'));
                        $value2 = date('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_FOR_30_DAYS :
                        $value1 = date('Y-m-d 00:00:00', strtotime('-30 days'));
                        $value2 = date('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_FOR_TODAY :
                        $value1 = date('Y-m-d 00:00:00');
                        $value2 = date('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_CURRENT_MONTH :
                        $value1 = date('Y-m-01 00:00:00');
                        $date2 = new \DateTime($value1);
                        $date2->modify('+1 month -1 day');
                        $value2 = $date2->format('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_PAST_MONTH :
                        $value1 = date('Y-m-01 00:00:00', strtotime('-1 month'));
                        $date2 = new \DateTime($value1);
                        $date2->modify('+1 month -1 day');
                        $value2 = $date2->format('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_CURRENT_YEAR :
                        $value1 = date('Y-01-01 00:00:00');
                        $date2 = new \DateTime($value1);
                        $date2->modify('+1 year -1 day');
                        $value2 = $date2->format('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_PAST_YEAR :
                        $date2 = new \DateTime(date('Y-01-01'));
                        $date2->modify('-1 year');
                        $value1 = $date2->format('Y-m-d 00:00:00');
                        $date2 = new \DateTime($value1);
                        $date2->modify('+1 year -1 day');
                        $value2 = $date2->format('Y-m-d 23:59:59');
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                    case self::FT_DATE_AFTER :
                        $value1 = date('Y-m-d 00:00:00', strtotime($value['condition_value'][0] . '+1 day'));
                        $this->addConditionToPreparedQuery($this->qmMoreOrCorrespondsTo($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value1;
                        break;
                    case self::FT_DATE_AFTER_CURRENT :
                        $value1 = date('Y-m-d 00:00:00', strtotime('+1 day'));
                        $this->addConditionToPreparedQuery($this->qmMoreOrCorrespondsTo($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value1;
                        break;
                    case self::FT_DATE_TO :
                        $value1 = date('Y-m-d 23:59:59', strtotime($value['condition_value'][0] . '-1 day'));
                        $this->addConditionToPreparedQuery($this->qmLessOrCorrespondsTo($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value1;
                        break;
                    case self::FT_DATE_PRIOR_TO_CURRENT :
                        $value1 = date('Y-m-d 23:59:59', strtotime('-1 day'));
                        $this->addConditionToPreparedQuery($this->qmLessOrCorrespondsTo($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . self::$_key_index] = $value1;
                        break;
                    case self::FT_DATE_PERIOD :
                        $value1 = date('Y-m-d 00:00:00', strtotime($value['condition_value'][0]));
                        $value2 = date('Y-m-d 23:59:59', strtotime($value['condition_value'][1]));
                        $this->addConditionToPreparedQuery($this->qmBeetween($table_name, $field_name), $field_schema);
                        $params[':' . $field_name . '_start' . self::$_key_index] = $value1;
                        $params[':' . $field_name . '_end' . self::$_key_index] = $value2;
                        break;
                }

                self::$_key_index++;
            }
        }

        $this->_prepared_query['params'] = $params;

        return $this;
    }

    private function addConditionToPreparedQuery($condition, $field_schema)
    {
        $key = 'conditions';

        if ($field_schema['params']['type'] == Fields::MFT_DATETIME_ACTIVITY) {
            $key = 'having';
        }

        $this->_prepared_query[$key][] = $condition;
    }

    /**
     * getQuery
     */
    public function getQuery($concat = true)
    {
        if ($concat) {
            return [
                'conditions' => \FilterConcatModel::getInstance()
                    ->setQuery($this->_prepared_query['conditions'])
                    ->concat()
                    ->getResultQuery(),
                'params'     => $this->_prepared_query['params'],
                'having'     => $this->_prepared_query['having'],
            ];
        } else {
            return $this->_prepared_query;
        }
    }

    /**
     * searchDisplayBlockVasue - созвращает список индексов при фильтрации по типу display_block
     *
     * @return string|null
     */
    private function searchDisplayBlockValue($field_name, $value, $operation)
    {
        if ($value === '') {
            return '';
        }

        $block_field_data = $this->_extension_copy->getFieldBlockData();
        if ($block_field_data == false) {
            return false;
        }
        if ($block_field_data['name'] != $field_name) {
            return false;
        }

        $values = [];
        $blocks_list = $this->_extension_copy->getSchemaBlocksData();

        if ($blocks_list == false) {
            return '';
        }

        //для блоков подменяем значение поля
        foreach ($blocks_list as $block) {
            switch ($operation) {
                case 'left':
                    $p = mb_strpos(mb_strtolower($block['title'], 'UTF-8'), mb_strtolower($value, 'UTF-8'), 0, 'UTF-8');
                    if ($p !== false && $p == 0) {
                        $values[] = '"' . $block['unique_index'] . '"';
                    }

                    break;
                case 'inner':
                    $p = mb_strpos(mb_strtolower($block['title'], 'UTF-8'), mb_strtolower($value, 'UTF-8'), 0, 'UTF-8');
                    if ($p !== false) {
                        $values[] = '"' . $block['unique_index'] . '"';
                    }
                    break;
            }
        }

        if (!empty($values)) {
            return implode(',', $values);
        }

        return '';
    }

    /**
     *  Возможны условия: начинается с, содержит, заканчивается
     *
     * @param char $operation - left, inner
     */
    private function qmLike($table_name, $field_name, $value, $field_schema, $operation = null)
    {
        if ($field_schema['params']['type'] != \Fields::MFT_DISPLAY_BLOCK && $value !== '') {
            $value = str_replace('_', '\_', $value);
            $value = str_replace('%', '\%', $value);
            if ($operation !== null) {
                switch ($operation) {
                    case 'left':
                        $value .= '%';
                        break;
                    case 'inner':
                        $value = '%' . $value . '%';
                        break;
                }
            }
        }

        // select
        if ($field_schema['params']['type'] == \Fields::MFT_SELECT) {
            $full_field_name = $field_name . '_title';
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '= "")';
            } else {
                $query = ['like', $full_field_name, $value];
            }

            return [
                'full_field_name' => ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //file, file_image
        } elseif ($field_schema['params']['type'] == \Fields::MFT_FILE || $field_schema['params']['type'] == \Fields::MFT_FILE_IMAGE) {
            $full_field_name = ($this->_add_table_name ? $this->extensionCopy->getTableName() . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '= "")';
            } else {
                $query = 'EXISTS (SELECT * FROM {{uploads}} WHERE ' . $full_field_name . '=relate_key AND file_title LIKE ("' . $value . '"))';
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //display_block
        } elseif ($field_schema['params']['type'] == \Fields::MFT_DISPLAY_BLOCK) {
            $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
            $db_value = $this->searchDisplayBlockValue($field_name, $value, $operation);
            if ($db_value === false) {
                return false;
            }
            if ($db_value) {
                $query = $full_field_name . ' in (' . $db_value . ')';
            } else {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '= "")';
            }

            return [
                'full_field_name' => ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //calculated
        } elseif ($field_schema['params']['type'] == \Fields::MFT_CALCULATED) {
            $full_field_name = \CalculatedFields::getInstance()
                ->setExtensionCopy($this->extensionCopy)
                ->setFieldName($field_name)
                ->prepareFormula()
                ->getFieldCondSQL();
            $full_field_name = '(' . $full_field_name . ')';
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '= "")';
            } else {
                $query = ['like', $full_field_name, $value];
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
        } else {
            $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '= "")';
            } else {
                $query = ['like', $full_field_name, $value];
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
        }
    }

    /**
     * Возможны условия: больше, менше, не равно
     * !!! Используется только для числового типа, и вычисляемого
     */
    private function qmCompare($table_name, $field_name, $value, $field_schema)
    {
        if ($value['condition_value'][0] !== '' && !is_numeric($value['condition_value'][0])) {
            return false;
        }
        if ($value['condition'] == false) {
            return false;
        }

        $operation = null;
        $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;

        switch ($value['condition']) {
            case self::FT_MORE :
                $operation = '>';
                break;
            case self::FT_LESS :
                $operation = '<';
                break;
            case self::FT_EQUAL_NOT :
                $operation = '!=';
                break;
        }

        if ($field_schema['params']['type'] == \Fields::MFT_CALCULATED) {
            //вычисляемый тип, загружаем значение
            $full_field_name = \CalculatedFields::getInstance()
                ->setExtensionCopy($this->extensionCopy)
                ->setFieldName($field_name)
                ->prepareFormula()
                ->getFieldCondSQL();
            $full_field_name = '(' . $full_field_name . ')';
        }

        $query = $full_field_name . $operation . ':' . $field_name . self::$_key_index;

        return [
            'full_field_name' => $full_field_name . $this->_field_name_index,
            'query'           => $query,
        ];
    }

    /**
     *   Условие: соответствует
     */
    private function qmCorrespondsTo($table_name, $field_name, $value, $field_schema)
    {
        //relate
        if ($field_schema['params']['type'] == \Fields::MFT_RELATE) {
            if ($this->_use_full_field_name_real) {
                $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($field_schema['params']['relate_module_copy_id']);
                $full_field_name = $relate_extension_copy->getTableName(null, false) . '_' . $relate_extension_copy->getPkFieldName(false, false);
            } else {
                $relate_table = ModuleTablesModel::model()->find('copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_one"',
                    [
                        ':copy_id'        => $this->copy_id,
                        ':relate_copy_id' => $field_schema['params']['relate_module_copy_id'],
                    ]);
                $table_prefix = Yii::app()->db->tablePrefix;
                $full_field_name = $table_prefix . $relate_table->table_name . '.' . $relate_table->relate_field_name;
            }

            if ($value === '') {
                $query = $full_field_name . ' is NULL';
            } else {
                $query = $full_field_name . '=:' . $field_name . self::$_key_index;
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //relate_participant
        } elseif ($field_schema['params']['type'] == \Fields::MFT_RELATE_PARTICIPANT) {
            $alias_name = 'participant1';

            if (
                $value[1] == 'group' ||
                strpos($field_name, 'block_participant') !== false // условие утвердительное, если фильтрация по Участниках
            ) {
                $alias_name = 'participant2';
            }
            $table_prefix = Yii::app()->db->tablePrefix;
            $full_field_name = $table_prefix . 'participant.ug_id' . $table_prefix . 'participant.ug_type';

            if ($value[0] === '' && $value[1] === '') {
                $query = $alias_name . '.participant_id is NULL';
                $this->_there_is_participant = true;
            } else {
                $query = $alias_name . '.ug_id=:ug_id' . self::$_key_index . ' AND ' . $alias_name . '.ug_type=:ug_type' . self::$_key_index;
                $this->_there_is_participant = true;
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //file, file_image
        } elseif ($field_schema['params']['type'] == \Fields::MFT_FILE || $field_schema['params']['type'] == \Fields::MFT_FILE_IMAGE) {
            if ($value === '') {
                $full_field_name = ($this->_add_table_name ? $this->extensionCopy->getTableName() . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '="")';

                return [
                    'full_field_name' => $full_field_name . $this->_field_name_index,
                    'query'           => $query,
                ];
            } else {
                $full_field_name = ($this->_add_table_name ? $this->extensionCopy->getTableName() . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
                $query = 'EXISTS (SELECT * FROM {{uploads}} WHERE ' . $full_field_name . '=relate_key AND file_title=:' . $field_name . self::$_key_index . ')';

                return [
                    'full_field_name' => $full_field_name . $this->_field_name_index,
                    'query'           => $query,
                ];
            }
            //display_block, select
        } elseif (in_array($field_schema['params']['type'], [\Fields::MFT_DISPLAY_BLOCK, \Fields::MFT_SELECT])) {
            $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '=:' . $field_name . self::$_key_index . ')';
            } else {
                $query = $full_field_name . '=:' . $field_name . self::$_key_index;
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //calculated
        } elseif ($field_schema['params']['type'] == \Fields::MFT_CALCULATED) {
            $full_field_name = \CalculatedFields::getInstance()
                ->setExtensionCopy($this->extensionCopy)
                ->setFieldName($field_name)
                ->prepareFormula()
                ->getFieldCondSQL();
            $full_field_name = '(' . $full_field_name . ')';
            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '=:' . $field_name . self::$_key_index . ')';
            } else {
                $query = $full_field_name . '=:' . $field_name . self::$_key_index;
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
        } else {
            $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;

            if ($value === '') {
                $query = '(' . $full_field_name . ' is NULL OR ' . $full_field_name . '=:' . $field_name . self::$_key_index . ')';
            } else {
                $query = $full_field_name . '=:' . $field_name . self::$_key_index;
            }

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
        }
    }

    /**
     *   Условие: НЕ соответствует
     */
    private function qmNotCorrespondsTo($table_name, $field_name, $value, $field_schema)
    {
        //relate
        if ($field_schema['params']['type'] == \Fields::MFT_RELATE) {
            if ($this->_use_full_field_name_real) {
                $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($field_schema['params']['relate_module_copy_id']);
                $full_field_name = $relate_extension_copy->getTableName(null, false) . '_' . $relate_extension_copy->getPkFieldName(false, false);
            } else {
                $relate_table = ModuleTablesModel::model()->find('copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_one"',
                    [
                        ':copy_id'        => $this->copy_id,
                        ':relate_copy_id' => $field_schema['params']['relate_module_copy_id'],
                    ]);
                $table_prefix = Yii::app()->db->tablePrefix;
                $full_field_name = $table_prefix . $relate_table->table_name . '.' . $relate_table->relate_field_name;
            }
            $query = $full_field_name . '!=:' . $field_name . self::$_key_index;

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //relate_participant
        } elseif ($field_schema['params']['type'] == \Fields::MFT_RELATE_PARTICIPANT) {
            $table_prefix = Yii::app()->db->tablePrefix;
            $full_field_name = $table_prefix . 'participant.ug_id' . $table_prefix . 'participant.ug_type';
            $query = '(participant2.ug_id!=:ug_id' . self::$_key_index . ' OR participant2.ug_type!=:ug_type' . self::$_key_index . ')';
            $this->_there_is_participant = true;

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
            //file, file_image
        } elseif ($field_schema['params']['type'] == \Fields::MFT_FILE || $field_schema['params']['type'] == \Fields::MFT_FILE_IMAGE) {
            if ($value === '') {
                $full_field_name = ($this->_add_table_name ? $this->extensionCopy->getTableName() . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
                $query = '(' . $full_field_name . '= "" OR ' . $full_field_name . ' is NULL)';;

                return [
                    'full_field_name' => $full_field_name . $this->_field_name_index,
                    'query'           => $query,
                ];
            } else {
                $full_field_name = ($this->_add_table_name ? $this->extensionCopy->getTableName() . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
                $query = 'EXISTS (SELECT * FROM {{uploads}} WHERE ' . $full_field_name . '=relate_key AND file_title=:' . $field_name . self::$_key_index . ')';

                return [
                    'full_field_name' => $full_field_name . $this->_field_name_index,
                    'query'           => $query,
                ];
            }
        } else {
            $full_field_name = ($this->_add_table_name ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;
            $query = '(' . $full_field_name . '!=:' . $field_name . self::$_key_index . ' OR ' . $full_field_name . ' is NULL) ';

            return [
                'full_field_name' => $full_field_name . $this->_field_name_index,
                'query'           => $query,
            ];
        }
    }

    /**
     *   Условие: больше или равенство
     */
    private function qmMoreOrCorrespondsTo($table_name, $field_name)
    {
        $full_field_name = ($this->_add_table_name && !empty($table_name) ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;

        return [
            'full_field_name' => $full_field_name . $this->_field_name_index,
            'query'           => $full_field_name . '>=:' . $field_name . self::$_key_index,
        ];
    }

    /**
     *   Условие: менше или равенство
     */
    private function qmLessOrCorrespondsTo($table_name, $field_name)
    {
        $full_field_name = ($this->_add_table_name && !empty($table_name) ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;

        return [
            'full_field_name' => $full_field_name . $this->_field_name_index,
            'query'           => $full_field_name . '<=:' . $field_name . self::$_key_index,
        ];
    }

    /**
     *   Условие: между
     */
    private function qmBeetween($table_name, $field_name)
    {
        $full_field_name = ($this->_add_table_name && !empty($table_name) ? $table_name . '.' : ($this->_add_table_alias ? $this->_add_table_alias . '.' : '')) . $field_name;

        return [
            'full_field_name' => $full_field_name . $this->_field_name_index,
            'query'           => $full_field_name . ' between :' . $field_name . '_start' . self::$_key_index . ' AND ' . ' :' . $field_name . '_end' . self::$_key_index,
        ];
    }

    /**
     * getViewList
     */
    public static function getViewList()
    {
        return [
            self::VIEW_PERSONAL => \Yii::t('filters', 'Personal'),
            self::VIEW_GENERAL  => \Yii::t('filters', 'General'),
        ];
    }

    /**
     * getAccessToChange
     */
    public function getAccessToChange()
    {
        $filter_model = $this;

        if (empty($filter_model)) {
            return false;
        }

        if ($filter_model->getIsNewRecord()) {
            return true;
        }

        if ($filter_model->user_create == WebUser::getUserId()) {
            return true;
        }
        if ($filter_model->view == static::VIEW_GENERAL) {
            return true;
        }

        return false;
    }

    /**
     * setAccessToChange
     */
    public function setAccessToChange()
    {
        self::$_access_to_change = $this->getAccessToChange();

        return $this;
    }

}
