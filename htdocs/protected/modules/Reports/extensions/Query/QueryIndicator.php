<?php
/**
 * QueryIndicator
 *
 * @author Alex R.
 */

namespace Reports\extensions\Query;

use Reports\models\ConstructorModel;

class QueryIndicator
{

    const ALIAS_I_PARAM = 'i_param';
    const ALIAS_I_RELATE_DATE = 'i_relate_date';

    const ALIAS_NAME_MAIN = 'main';
    const ALIAS_NAME_RELATE = 'relate';

    private $_data_setting;

    private $_query;

    private $_query_entityes = [
        'select'    => [],
        'from'      => [],
        'left_join' => [],
        'where'     => [],
        'group'     => [],
    ];

    private $_parent_alias = 't1';

    private $_table_alias_indexes = [
        'index_last'    => 1,
        'i_param'       => 1,
        'i_relate_date' => 1,
    ];

    private $_table_alias_name_list = [
        'main'   => [],
        'relate' => [],
    ];

    private $_extension_copy_param;

    private $_extension_copy_indicator;

    private $_filter_controller;

    private $_sql_week = 3; //monday

    private $_aggregate = true; // вкл/выкл агрегирование

    private $_element_type;

    private $_is_set_search = false;

    private $_prepare_param_field_name = true;

    private $_prepare_indicator_field_name = true;

    private static $_cache_irm;

    private static $_is_set_indicator_filters; // указывает на наличие фильтра из модулей связанных показателей

    /**
     * getInstance
     *
     * @return QueryIndicator
     */
    public static function getInstance()
    {
        return new self();
    }

    public function setElementType($element_type)
    {
        $this->_element_type = $element_type;

        return $this;
    }

    public function setSqlWeek($week)
    {
        $this->_sql_week = $week;

        return $this;
    }

    public function setPrepareFieldName($prepare_param_field_name = true, $prepare_indicator_field_name = true)
    {
        $this->_prepare_param_field_name = $prepare_param_field_name;
        $this->_prepare_indicator_field_name_field_name = $prepare_indicator_field_name;

        return $this;
    }

    public function setStartAliasIndex($index)
    {
        $this->_parent_alias = 't' . $index;
        array_map(function ($value) use ($index) {
            $value = $index;
        }, $this->_table_alias_indexes);

        return $this;
    }

    private function aliasIndexUp($alias_type, $increment = true)
    {
        if ($increment) {
            $this->_table_alias_indexes['index_last']++;
        }
        $this->_table_alias_indexes[$alias_type] = $this->_table_alias_indexes['index_last'];
    }

    private function getAliasIndex($alias_type)
    {
        $tmp = [];
        if (is_array($alias_type)) {
            foreach ($alias_type as $alias) {
                if ($this->_table_alias_indexes[$alias] === 0) {
                    continue;
                }
                $tmp[] = $this->_table_alias_indexes[$alias];
            }

            return max($tmp);
        } else {
            return $this->_table_alias_indexes[$alias_type];
        }
    }

    private function addTableAliasName($alias_type, $copy_id, $alias_name)
    {
        if (!isset($this->_table_alias_name_list[$alias_type][$copy_id])) {
            $this->_table_alias_name_list[$alias_type][$copy_id] = $alias_name;
        }
    }

    private function getTableAliasName($alias_type, $copy_id)
    {
        if (!empty($this->_table_alias_name_list[$alias_type][$copy_id])) {
            return $this->_table_alias_name_list[$alias_type][$copy_id];
        }

        return false;
    }

    /**
     * prepareVars
     *
     * @return bool
     */
    public function prepareVars()
    {
        $result = true;

        // вкл/выкл агрегирование
        if (
            $this->_element_type == \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE &&
            $this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID &&
            $this->_data_setting['param']['module_copy_id'] == $this->_data_setting['indicator']['module_copy_id'] &&
            $this->_data_setting['indicator']['type_indicator'] != \Reports\models\ConstructorModel::TI_PERCENT
        ) {
            $this->_aggregate = false;
        }

        // field_type - 1
        $params = \ExtensionCopyModel::model()->findByPk($this->_data_setting['indicator']['module_copy_id'])->getFieldSchemaParams($this->_data_setting['indicator']['field_name']);
        if ($params) {
            $this->_data_setting['indicator']['field_type'] = $params['params']['type'];
        }

        $params = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id'])->getFieldSchemaParams($this->_data_setting['param']['field_name']);
        if ($params) {
            $this->_data_setting['param']['field_type'] = $params['params']['type'];
        }

        $setting = $this->_data_setting;

        if (empty($setting['param']['module_copy_id'])) {
            $result = false;
        }

        if (empty($setting['param']['field_name']) && $setting['indicator']['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY) {
            $result = false;
        }

        if (empty($setting['indicator']['module_copy_id']) ||
            empty($setting['indicator']['field_name']) ||
            empty($setting['indicator']['type_indicator'])
        ) {
            $result = false;
        }

        if (
            $result &&
            !empty($setting['param']['field_name']) &&
            $setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID &&
            $this->_element_type != \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE &&
            $this->_aggregate == false
        ) {
            $result = false;
        }

        if ($result && $setting['indicator']['field_name'] != \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) {
            if (
                $this->_aggregate == true &&
                $setting['indicator']['field_type'] != 'numeric' &&
                $this->_element_type != \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE
            ) {
                $result = false;
            }
        }

        /**
         * field_name - для основного запроса в БД
         * field_name_real - из схемы
         * field_name_base - реальное название поля из основной схемы модуля
         */
        $this->prepareFieldName('param', 'field_name_real', $setting['param']['field_name']);
        $this->prepareFieldName('param', 'field_name_base', $setting['param']['field_name']);
        $this->prepareFieldName('indicator', 'field_name_real', $setting['indicator']['field_name']);
        $this->prepareFieldName('indicator', 'field_name_base', $setting['indicator']['field_name']);

        // param
        if ($result && !empty($setting['param']['field_name'])) {
            //1
            if ($setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID || (
                    $setting['param']['field_name'] == 'module_title' && $this->_data_setting['param']['field_type'] != 'relate')
            ) {
                $this->prepareFieldName('param', 'field_name', $this->_extension_copy_param->getPrimaryViewFieldName());
                $this->prepareFieldName('param', 'field_name_base', $this->_data_setting['param']['field_name']);
                //2
            } else {
                if (array_key_exists('field_type', $this->_data_setting['param']) && $this->_data_setting['param']['field_type'] == 'relate') {
                    $extension_copy = \ExtensionCopyModel::model()->findByPk($params['params']['relate_module_copy_id']);
                    $this->prepareFieldName('param', 'field_name', $extension_copy->prefix_name . '_value');
                    $this->prepareFieldName('param', 'relate_module_copy_id', $extension_copy->copy_id);
                    $this->_is_set_search = true;
                    //3
                } else {
                    if (\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])) {
                        $this->prepareFieldName('param', 'field_name_base', $this->_extension_copy_param->getPrimaryViewFieldName());
                    }
                }
            }
        }

        // indicator
        if ($result && !empty($setting['indicator']['field_name']) && $setting['indicator']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) {
            $this->prepareFieldName('indicator', 'field_name', $this->_extension_copy_indicator->prefix_name . '_id');
            $this->prepareFieldName('indicator', 'field_name_base', $this->_data_setting['indicator']['field_name']);
        } else {
            if ($result && !empty($setting['indicator']['field_name'])) {
                $i_params = $this->_extension_copy_indicator->getFieldSchemaParams($setting['indicator']['field_name']);
                if ($i_params['params']['type'] == 'display_none') {
                    $this->prepareFieldName('indicator', 'field_name', $this->_extension_copy_indicator->getPrimaryViewFieldName());
                    $this->prepareFieldName('indicator', 'field_name_base', $this->_data_setting['indicator']['field_name']);
                }
            }
        }

        // field_type - 2
        $params = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id'])->getFieldSchemaParams($this->_data_setting['param']['field_name_base']);
        $this->_data_setting['param']['field_type'] = $params['params']['type'];
        $params = \ExtensionCopyModel::model()->findByPk($this->_data_setting['indicator']['module_copy_id'])->getFieldSchemaParams($this->_data_setting['indicator']['field_name_base']);
        if ($params) {
            $this->_data_setting['indicator']['field_type'] = $params['params']['type'];
        }

        return $result;
    }

    private function prepareFieldName($element_type, $key, $value)
    {
        if ($element_type == 'param' && $this->_prepare_param_field_name == false) {
            return $this;
        }
        if ($element_type == 'indicator' && $this->_prepare_indicator_field_name == false) {
            return $this;
        }

        $this->_data_setting[$element_type][$key] = $value;

        return $this;
    }

    /**
     * calculationDaIndicator - возвращает статус индикатора - вычисляемый / не вичисляемый
     */
    public function calculationIndicator($indicator_schema)
    {
        $data_setting = $this->_data_setting;
        $data_setting['indicator'] = $indicator_schema;

        $query_indicator = new QueryIndicator();
        $query_indicator
            ->setDataSetting($data_setting)
            ->setElementType($this->_element_type)
            ->setPrepareFieldName(false, true)
            ->prepareVars();

        $data_setting = $query_indicator->getDataSetting();

        // если поле вычисляемое или числовое
        if (
            ($data_setting['indicator']['field_type'] == 'numeric') ||
            ($indicator_schema['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT)
        ) {
            return [
                'status'    => true,
                'indicator' => $data_setting,
            ];
        }

        return [
            'status'    => false,
            'indicator' => $data_setting,
        ];

    }

    /**
     * getIndicagorFields
     */
    private function getIndicagorFields()
    {
        $result = [];

        $cacl_indicator = $this->calculationIndicator($this->_data_setting['indicator']);

        $indicators = \Reports\extensions\ElementMaster\Schema::getInstance()->getSchemaDataAnalisisIndicators();
        foreach ($indicators as $indicator) {
            if ($cacl_indicator['status']) {
                if ($indicator['unique_index'] == $this->_data_setting['indicator']['unique_index']) {
                    continue;
                }
                $cacl = $this->calculationIndicator($indicator);
                if ($cacl['status']) {
                    continue;
                } //
            } else {
                if ($indicator['unique_index'] != $this->_data_setting['indicator']['unique_index']) {
                    continue;
                }
                $cacl = $cacl_indicator;
            }

            $result[] = $cacl['indicator']['indicator']['field_name'];
        }

        return $result;
    }

    /**
     * setDataSetting
     *
     * @param $setting
     * @return $this
     */
    public function setDataSetting($setting)
    {
        if (!isset($setting['indicator']['period'])) {
            $setting['indicator']['period'] = null;
        }
        if (!isset($setting['indicator']['display_option'])) {
            $setting['indicator']['display_option'] = \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY;
        }

        if (empty($setting['param']['type_date'])) {
            $setting['param']['type_date'] = $setting['param']['module_copy_id'] . ':date_create';
        }

        $this->_data_setting = $setting;

        $this->_extension_copy_param = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id']);
        $this->_extension_copy_indicator = \ExtensionCopyModel::model()->findByPk($this->_data_setting['indicator']['module_copy_id']);

        return $this;
    }

    public function getDataSetting()
    {
        return $this->_data_setting;
    }

    /**
     * prepareIndicatorRelateModules
     */
    private function prepareIndicatorRelateModules()
    {
        if (self::$_cache_irm !== null) {
            $this->_data_setting['indicator_relate_modules'] = self::$_cache_irm;

            return;
        }

        $ci_list = $this->getFilterCopyIdList();

        foreach ($ci_list as $copy_id) {
            if ($this->_data_setting['param']['module_copy_id'] == $copy_id) {
                continue;
            }
            if (is_array(self::$_cache_irm) && in_array($copy_id, self::$_cache_irm)) {
                continue;
            }
            self::$_cache_irm[] = $copy_id;
        }

        $this->_data_setting['indicator_relate_modules'] = self::$_cache_irm;
    }

    /**
     * build
     *
     * @return $this
     */
    public function build()
    {
        if ($this->prepareVars() == false) {
            return $this;
        }

        $this->prepareIndicatorRelateModules();
        $this->addFrom();
        $this->addJoin();
        $this->addSelect();
        $this->addWhere();
        $this->addFilterIrm();

        return $this;
    }

    /**
     * buildTotalSum
     *
     * @return $this
     */
    public function buildTotalSum()
    {
        if ($this->prepareVars() == false) {
            return $this;
        }

        $this->prepareIndicatorRelateModules();
        $this->addFrom();
        $this->addJoin();
        $this->addSelectSum();
        $this->addWhere();
        $this->addFilterIrm();

        return $this;
    }

    /**
     * buildCount
     *
     * @return $this
     */
    public function buildCount()
    {
        if ($this->prepareVars() == false) {
            return $this;
        }

        $this->prepareIndicatorRelateModules();
        $this->addFrom();
        $this->addJoin();
        $this->addSelectCount();
        $this->addWhere();
        $this->addFilterIrm();

        return $this;
    }

    /**
     * getQuery
     *
     * @return mixed
     */
    public function getQuery()
    {
        $this->buildQuery();

        $params = \Yii::app()->params;
        if ($this->_query && !empty($params['reports']['logging_query'])) {
            if (is_array($this->_query)) {
                foreach ($this->_query as $query) {
                    \DataModel::getInstance()->setText('insert into {{reports_query}} (date_create, query, element) values (now(), "' . addslashes($query) . '", "' . $this->_element_type . '_indicator_' . $this->_data_setting['indicator']['unique_index'] . '")')->execute();
                }
            } else {
                \DataModel::getInstance()->setText('insert into {{reports_query}} (date_create, query, element) values (now(), "' . addslashes($this->_query) . '", "' . $this->_element_type . '_indicator_' . $this->_data_setting['indicator']['unique_index'] . '")')->execute();
            }
        }

        return $this->_query;
    }

    /**
     * buildQuery
     */
    private function buildQuery()
    {
        if (empty($this->_query_entityes['from'])) {
            return $this;
        }

        $data_model = new \DataModel();
        foreach ($this->_query_entityes as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'select' :
                    $data_model->setSelect($value);
                    break;
                case 'from' :
                    $data_model->setFrom($value);
                    break;
                case 'left_join' :
                    foreach ($value as $item) {
                        $data_model->join($item['table_name'], $item['on'], [], 'left', false);
                    }
                    break;
                case 'inner_join' :
                    foreach ($value as $item) {
                        $data_model->join($item['table_name'], $item['on'], [], 'inner', false);
                    }
                    break;
                case 'where' :
                    if (empty($value)) {
                        break;
                    }

                    foreach ($value as $w_value) {
                        if (is_string($w_value)) {
                            $data_model->andWhere(['AND', $w_value]);
                        } else {
                            $data_model->andWhere($w_value['conditions'], $w_value['params']);
                        }
                    }
                    break;
                case 'group' :
                    $data_model->setGroup($value);
                    break;

            }
        }

        $data_model->prepare();

        $this->_query = $data_model->getText();
    }

    /**
     * addSelectSum
     */
    private function addSelectSum()
    {
        if ($this->_data_setting['param']['module_copy_id'] == $this->_data_setting['indicator']['module_copy_id']) {
            $alias = $this->_parent_alias;
        } else {
            $alias = 't' . $this->getAliasIndex(self::ALIAS_I_PARAM);
        }
        $this->_query_entityes['select'][] = 'sum(' . $alias . '.' . $this->_data_setting['indicator']['field_name'] . ') AS total_sum';
    }

    /**
     * addSelectCount
     */
    private function addSelectCount()
    {
        $this->_query_entityes['select'][] = 'count(*)';
    }

    /**
     * getParamXIfPeriodDate
     *
     * @param $period
     * @param $changed_period
     * @return string
     */
    private function getParamXIfPeriodDate($period)
    {
        $field_name = $this->getTypeDateFieldName();

        if (!empty($period)) {
            switch ($period) {
                case 'day' :
                    $param_x = 'DATE_FORMAT(' . $field_name . ', "%Y-%m-%d")';
                    break;
                case 'all_period' :
                    $param_x = '""';
                    break;
                case 'week' :
                    $param_x = 'YEARWEEK(' . $field_name . ', ' . $this->_sql_week . ')';
                    break;
                case 'month' :
                    $param_x = 'CONCAT(YEAR(' . $field_name . '), "-", DATE_FORMAT(' . $field_name . ', "%m"), "-01")';
                    break;
                case 'quarter' :
                    $param_x = 'CONCAT(YEAR(' . $field_name . '), "-", QUARTER(' . $field_name . '))';
                    break;
                case 'year' :
                    $param_x = 'YEAR(' . $field_name . ')';
                    break;
            }
        } else {
            $param_x = $this->getTitleFieldName();
        }

        return $param_x;
    }

    /**
     * getTitleFieldName
     *
     * @return string
     */
    private function getTitleFieldName()
    {
        if (!\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])) {
            switch ($this->_data_setting['param']['field_type']) {
                case 'logical' :
                    $param_x = 'CASE ' . $this->_parent_alias . '.' . $this->_data_setting['param']['field_name'] . ' WHEN "1" THEN "' . \Yii::t('base', 'Yes') . '" WHEN "0" THEN "' . \Yii::t('base', 'No') . '" ELSE "" END ';
                    break;
                case 'select' :
                    $param_x = 'IFNULL(' . $this->_extension_copy_param->prefix_name . '_' . $this->_data_setting['param']['field_name'] . '_title, "")';
                    break;
                case 'relate' :
                    $param_x = $this->_parent_alias . '.' . \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['relate_module_copy_id'])->getTableName(null, false) . '_' . $this->_data_setting['param']['field_name'];
                    break;
                case 'display_block' :
                    $param_x = 'IFNULL(' . $this->_extension_copy_param->prefix_name . '_' . $this->_data_setting['param']['field_name'] . '_title, "")';
                    break;
                default :
                    $param_x = $this->_parent_alias . '.' . $this->_data_setting['param']['field_name'];
            }
        } else {
            $period = \Reports\models\DataReportModel::getInstance()->getPeriodName($this->_data_setting['param']['field_name']);
            $param_x = $this->getParamXIfPeriodDate($period);
        }

        return $param_x;
    }

    /**
     * typeDateIsParentModule - возвращает статус пренадлежности type_date модулю-параметру
     *
     * @return bool
     */
    private function typeDateIsParentModule($date = null)
    {
        if ($date === null) {
            $date = $this->_data_setting['param']['type_date'];
            $date = explode(':', $date);
        }

        if (count($date) == 1 || (integer)$date[0] == $this->_extension_copy_param->copy_id) {
            return true;
        }

        return false;
    }

    /**
     * getTypeDateFieldName - возвращает название поля type_date
     *
     * @param bool $add_prefix
     */
    private function getTypeDateFieldName($add_prefix = true)
    {
        $date = $this->_data_setting['param']['type_date'];
        $date = explode(':', $date);

        $prefix = '';

        if ($this->typeDateIsParentModule($date)) {
            if ($add_prefix) {
                $prefix = $this->_parent_alias . '.';
            }

            return $prefix . array_pop($date);
        } else {
            if ($add_prefix) {
                $prefix = 't' . $this->getAliasIndex(self::ALIAS_I_RELATE_DATE) . '.';
            }

            return $prefix . array_pop($date);
        }
    }

    /**
     * getTypeDateCopyId - возвращает название copy_id
     *
     * @param bool $add_prefix
     */
    private function getTypeDateCopyId()
    {
        $date = $this->_data_setting['param']['type_date'];
        $date = explode(':', $date);

        return $date[0];
    }

    /**
     * addSelect
     */
    private function addSelect()
    {
        $setting = $this->_data_setting;
        $select = [];
        $display_option = $setting['indicator']['display_option'];
        $period = $setting['indicator']['period'];
        $changed_period = false;

        if ($display_option == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY) {
            switch ($setting['param']['field_name']) {
                case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_DAY :
                    $period = 'day';
                    $changed_period = true;
                    break;
                case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_WEEK :
                    $period = 'week';
                    $changed_period = true;
                    break;
                case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_MONTH :
                    $period = 'month';
                    $changed_period = true;
                    break;
                case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_QUARTER :
                    $period = 'quarter';
                    $changed_period = true;
                    break;
                case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_YEAR :
                    $changed_period = true;
                    $period = 'year';
                    break;
            }
        }

        if ($changed_period && $changed_period == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY) {
            $display_option = \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY;
        }

        $period_s = $period;

        if ($display_option == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY) {
            $field_name = $this->_parent_alias . '.' . $setting['param']['field_name'];
            if (array_key_exists('field_type', $this->_data_setting['param']) && $this->_data_setting['param']['field_type'] == 'relate') {
                $field_name = $this->_parent_alias . '.' . \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['relate_module_copy_id'])->getTableName(null, false) . '_' . $this->_data_setting['param']['field_name'];
            }
            $x = 0;
            if ($this->_data_setting['param']['field_type'] == 'logical') {
                $x = -1;
            }
            $select[] = 'IFNULL(' . $field_name . ', ' . $x . ') AS param_s1';
            $select[] = '0 AS param_s2';
            $select[] = '0 AS param_s3';

            if ($this->_element_type == \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE) {
                $select[] = $this->_parent_alias . '.' . $this->_extension_copy_param->prefix_name . '_id AS param_x';
            } else {
                $select[] = 'IFNULL(' . $this->getTitleFieldName() . ', "") AS param_x';
            }
        } else {
            switch ($period_s) {
                case 'day' :
                    $select[] = 'IFNULL(YEAR(' . $this->getTypeDateFieldName() . '), 0) AS param_s1';
                    $select[] = 'IFNULL(DATE_FORMAT(' . $this->getTypeDateFieldName() . ', "%m"), 0) AS param_s2';
                    $select[] = 'IFNULL(DAY(' . $this->getTypeDateFieldName() . '), 0) AS param_s3';
                    break;
                case 'all_period' :
                    $select[] = '0 AS param_s1';
                    $select[] = '0 AS param_s2';
                    $select[] = '0 AS param_s3';
                    break;
                case 'week' :
                    $select[] = 'IFNULL(YEARWEEK(' . $this->getTypeDateFieldName() . ', ' . $this->_sql_week . '), 0) AS param_s1';
                    $select[] = '0 AS param_s2';
                    $select[] = '0 AS param_s3';
                    break;
                case 'month' :
                    $select[] = 'IFNULL(YEAR(' . $this->getTypeDateFieldName() . '), 0) AS param_s1';
                    $select[] = 'IFNULL(DATE_FORMAT(' . $this->getTypeDateFieldName() . ', "%m"), 0) AS param_s2';
                    $select[] = '"01" AS param_s3';
                    break;
                case 'quarter' :
                    $select[] = 'IFNULL(YEAR(' . $this->getTypeDateFieldName() . '), 0) AS param_s1';
                    $select[] = 'IFNULL(QUARTER(' . $this->getTypeDateFieldName() . '), 0) AS param_s2';
                    $select[] = '0 AS param_s3';
                    break;
                case 'year' :
                    $select[] = 'IFNULL(YEAR(' . $this->getTypeDateFieldName() . '), 0) AS param_s1';
                    $select[] = '0 AS param_s2';
                    $select[] = '0 AS param_s3';
                    break;
                default :
                    $select[] = 'IFNULL(YEAR(' . $this->getTypeDateFieldName() . '), 0) AS param_s1';
                    $select[] = 'IFNULL(DATE_FORMAT(' . $this->getTypeDateFieldName() . ', "%m"), 0) AS param_s2';
                    $select[] = 'IFNULL(DAY(' . $this->getTypeDateFieldName() . '), 0) AS param_s3';
            }
            $select[] = $this->getParamXIfPeriodDate($period_s) . ' AS param_x';
        }

        //indicator_value
        if ($this->_aggregate == true) {
            if ($this->_data_setting['indicator']['field_name_real'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) {
                $f = 'IFNULL(t' . $this->getAliasIndex(self::ALIAS_I_PARAM, self::ALIAS_I_RELATE_DATE) . '.' . $setting['indicator']['field_name'] . ',0)';
                $s = '(IF(' . $f . ',1,0))';
            } else {
                $s = 'IFNULL(t' . $this->getAliasIndex(self::ALIAS_I_PARAM, self::ALIAS_I_RELATE_DATE) . '.' . $setting['indicator']['field_name'] . ',0)';
            }

            if ($setting['indicator']['type_indicator'] == \Reports\models\ConstructorModel::TI_PERCENT) { // процент
                $query_total = \Reports\extensions\Query\QueryIndicator::getInstance()
                    ->setStartAliasIndex(1000)
                    ->setPrepareFieldName(false, false)
                    ->setDataSetting($this->_data_setting)
                    ->setElementType($this->_element_type)
                    ->buildTotalSum()
                    ->getQuery();

                if (!empty($query_total)) {
                    $s = 'SUM(' . $s . ')';
                    $select[] = 'total_sum';
                    $this->_query_entityes['left_join'][] = [
                        'table_name' => '(' . $query_total . ') as t_total',
                        'on'         => $this->_parent_alias . '.' . $this->_extension_copy_param->getPkFieldName(false, false) . ' = ' . $this->_parent_alias . '.' . $this->_extension_copy_param->getPkFieldName(false, false)
                    ];

                    if ($this->_data_setting['param']['module_copy_id'] == $this->_data_setting['indicator']['module_copy_id']) {
                        $this->_query_entityes['group'] = 'param_x, param_s1, param_s2, param_s3';
                    } else {
                        $this->_query_entityes['group'] = 'param_s1, param_s2, param_s3';

                        if (\Reports\extensions\ElementMaster\Schema::getInstance()->getThereIsParamIndicator()) {
                            $this->_query_entityes['group'] = 'param_x,' . $this->_query_entityes['group'];
                        }
                    }
                }
            }

        } else {
            $f = 'IFNULL(t' . $this->getAliasIndex(self::ALIAS_I_PARAM, self::ALIAS_I_RELATE_DATE) . '.' . $this->_extension_copy_param->getPkFieldName(false, false) . ',"")';
            $s = '(IF(' . $f . ',1,0))';
        }

        $select[] = $s . ' AS indicator_value';

        //unique_index
        $select[] = '"' . $setting['indicator']['unique_index'] . '" AS unique_index';

        $this->_query_entityes['select'] = $select;

    }

    /**
     * addFrom
     */
    private function addFrom()
    {
        $this->_query_entityes['from'] = $this->getData($this->_extension_copy_param, $this->_parent_alias, true);
    }

    /**
     * addJoinRelate - связующая таблица
     */
    private function addJoinRelate($copy_id, $relate_copy_id, $alias_type, $callable)
    {
        $result = true;

        $table_prefix = \Yii::app()->db->tablePrefix;

        $relate_table = \ModuleTablesModel::getRelateModel($copy_id, $relate_copy_id, [\ModuleTablesModel::TYPE_RELATE_MODULE_ONE, \ModuleTablesModel::TYPE_RELATE_MODULE_MANY]);
        if (empty($relate_table)) {
            return false;
        }

        if ($callable !== null) {
            $callable();
        }

        if ((
            $alias_type == self::ALIAS_I_RELATE_DATE &&
            $relate_table->type == \ModuleTablesModel::TYPE_RELATE_MODULE_MANY &&
            $this->typeDateIsParentModule() == false &&
            $this->_data_setting['indicator']['module_copy_id'] != $this->getTypeDateCopyId()
        )
        ) {
            $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($relate_copy_id);

            $table_name =
                '(SELECT MIN(tt2.' . $this->getTypeDateFieldName(false) . ') as ' . $this->getTypeDateFieldName(false) . ', ' .
                'tt1.' . $relate_table->parent_field_name . ', tt1.' . $relate_table->relate_field_name . ' ' .
                'FROM ' . $table_prefix . $relate_table->table_name . ' as tt1 ' .
                'LEFT JOIN ' . $relate_extension_copy->getTableName() . ' as tt2 USING (' . $relate_table->relate_field_name . ') ' .
                'GROUP BY tt1.' . $relate_table->parent_field_name .
                ') as t' . $this->getAliasIndex($alias_type);
            $result = false;
        } else {
            $table_name = $table_prefix . $relate_table->table_name . ' as t' . $this->getAliasIndex($alias_type);
        }

        $this->_query_entityes['left_join'][] = [
            'table_name' => $table_name,
            'on'         => $this->_parent_alias . '.' . $relate_table->parent_field_name . '=t' . $this->getAliasIndex($alias_type) . '.' . $relate_table->parent_field_name
        ];

        $this->addTableAliasName(self::ALIAS_NAME_RELATE, $relate_copy_id, 't' . $this->getAliasIndex($alias_type));

        return $result;
    }

    /**
     * addJoinRelateParent - присоеденяем главную таблицу связанного модуля
     */
    private function addJoinRelateParent($copy_id, $only_table_name, $alias_type, $callable)
    {
        $relate_table = \ModuleTablesModel::getRelateModel($copy_id, null, \ModuleTablesModel::TYPE_PARENT);
        if (!empty($relate_table)) {
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);

            if ($callable !== null) {
                $callable();
            }

            if ($only_table_name) {
                $table_name = '{{' . $relate_table['table_name'] . '}} t' . $this->getAliasIndex($alias_type);
            } else {
                $table_name = $this->getData($extension_copy, 't' . $this->getAliasIndex($alias_type));
            }

            if ($table_name == false) {
                return false;
            }

            $this->_query_entityes['left_join'][] = [
                'table_name' => $table_name,
                'on'         => $this->getTableAliasName(self::ALIAS_NAME_RELATE, $copy_id) . '.' . $extension_copy->prefix_name . '_id' . '=t' . $this->getAliasIndex($alias_type) . '.' . $extension_copy->prefix_name . '_id',
            ];

            $this->addTableAliasName(self::ALIAS_NAME_MAIN, $copy_id, 't' . $this->getAliasIndex($alias_type));
        } else {
            return false;
        }

        return true;
    }

    /**
     * addJoin  ******************************************************************************************************
     */
    private function addJoin()
    {
        $copy_id = $this->_data_setting['param']['module_copy_id'];
        $relate_copy_id = $this->_data_setting['indicator']['module_copy_id'];

        $seted_relates = true;

        //1. Связующая таблица по many_to_many
        if ($copy_id == $relate_copy_id) {
            $seted_relates = false;
        } else {
            if ($this->addJoinRelate($copy_id, $relate_copy_id, self::ALIAS_I_PARAM, function () use ($relate_copy_id, &$copy_id_added) {
                    $this->aliasIndexUp(self::ALIAS_I_PARAM);
                }) == false
            ) {
                $seted_relates = false;
            }
        }

        //2. связаная таблица
        if (($seted_relates && $this->_data_setting['indicator']['field_name_real'] != \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) ||
            ($seted_relates && !empty($this->_data_setting['filters']['filter_params_indicator'][$this->_data_setting['indicator']['unique_index']]))
        ) {
            $this->addJoinRelateParent($relate_copy_id, false, self::ALIAS_I_PARAM, function () use ($relate_copy_id, &$copy_id_added) {
                $this->aliasIndexUp(self::ALIAS_I_PARAM);
            });
        }

        //3. если type_date из связаного модуля
        if (
            $this->typeDateIsParentModule() == false &&
            (
                \Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name']) ||
                $this->_element_type == 'indicator' ||
                (isset($this->_data_setting['indicator']['graph_type']) && $this->_data_setting['indicator']['graph_type'] == \Reports\models\ConstructorModel::GRAPH_LINE)

            )
        ) {
            $copy_id_td = $this->getTypeDateCopyId();
            $seted_relates = true;
            if ($this->getTableAliasName(self::ALIAS_NAME_RELATE, $copy_id_td) == false) {
                $seted_relates = $this->addJoinRelate($this->_data_setting['param']['module_copy_id'], $copy_id_td, self::ALIAS_I_RELATE_DATE, function () {
                    $this->aliasIndexUp(self::ALIAS_I_RELATE_DATE);
                });
            } else {
                $this->aliasIndexUp(self::ALIAS_I_RELATE_DATE, false);
            }

            if ($seted_relates && $this->getTableAliasName(self::ALIAS_NAME_MAIN, $copy_id_td) == false) {
                $this->addJoinRelateParent($copy_id_td, true, self::ALIAS_I_RELATE_DATE, function () {
                    $this->aliasIndexUp(self::ALIAS_I_RELATE_DATE);
                });
            }
        }
    }

    private function addWhere()
    {

        // если дата в Параметре из связанного модуля
        if ($this->typeDateIsParentModule() == false) {

            // type_date - связанный модуль
            $relate_copy_id = $this->getTypeDateCopyId();
            $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($relate_copy_id);

            $relate_table = \ModuleTablesModel::getRelateModel($this->_extension_copy_param->copy_id, $relate_copy_id, [\ModuleTablesModel::TYPE_RELATE_MODULE_ONE, \ModuleTablesModel::TYPE_RELATE_MODULE_MANY]);
            if (!empty($relate_table)) {

                $data_model = new \DataModel();
                $data_model
                    ->setSelect($relate_extension_copy->getTableName() . '.' . $relate_table->relate_field_name)
                    ->setFrom('{{' . $relate_table->table_name . '}}')
                    ->andWhere(
                        $this->_parent_alias . '.' . $this->_extension_copy_param->getPkFieldName() . ' = ' . '{{' . $relate_table->table_name . '}}' . '.' . $this->_extension_copy_param->getPkFieldName() . ' AND ' .
                        $relate_extension_copy->getTableName() . '.' . $this->getTypeDateFieldName(false) . ' between "' . $this->_data_setting['filters']['_date_interval_start'] . ' 00:00:00" AND "' . $this->_data_setting['filters']['_date_interval_end'] . ' 23:59:59"')
                    ->join(
                        $relate_extension_copy->getTableName(null, false),
                        $relate_extension_copy->getTableName() . '.' . $relate_table->relate_field_name . '= {{' . $relate_table->table_name . '}}.' . $relate_table->relate_field_name);

                $this->_query_entityes['where'][] = 'exists (' . $data_model->getText() . ')';

                // search
                if (\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name_real']) && !empty($this->_data_setting['filters']['search_model'])) {
                    $search_model = $this->_data_setting['filters']['search_model'];
                    $search_text = $search_model->getText();

                    if ($search_text !== null) {
                        $search_text = str_replace('_', '\_', $search_text);

                        if (\Helper::checkCharForDate($search_text) && strtotime($search_text)) {
                            $search_text = date('Y-m-d', strtotime($search_text));
                        }
                        $this->_query_entityes['where'][] = $this->getTypeDateFieldName() . ' like "%' . $search_text . '%"';
                    }
                }

            }
        }

    }

    /**
     * addFilterIrm - устанавливает фильтр присоедененных таблиц показателей
     */
    private function addFilterIrm()
    {
        if ($this->isSetIndicatorFilters() == false) {
            return;
        }

        foreach ($this->_data_setting['indicator_relate_modules'] as $relate_copy_id) {
            // Проверка наличия фильтров
            $extension_copy = \ExtensionCopyModel::model()->findByPk($relate_copy_id);
            $this->addWhereFilterIrm($extension_copy);
        }
    }

    /**
     * addWhere
     */
    private function addWhereFilterIrm($extension_copy)
    {
        $query_wars = $this->getIndicatorQueryVars($extension_copy->copy_id);
        if (empty($query_wars)) {
            return;
        }

        $condition = $this->getConditionQueryForIndicatorFilter($extension_copy, $query_wars);

        if (!empty($condition)) {
            $this->_query_entityes['where'][] = $condition;
        }
    }

    /**
     * getIndicatorQueryVars - устанавливает фильтр присоедененных таблиц показателей
     *                         !!!! Только общие фильтры, без индивидульных фильтров для показателя
     */
    private function getIndicatorQueryVars($relate_copy_id)
    {
        if (empty($this->_data_setting['indicator_relate_modules'])) {
            return;
        }

        $_query_vars = [
            'relate' => []
        ];

        $relate_vars = [];

        foreach ($this->_data_setting['indicator_relate_modules'] as $copy_id) {
            if ($relate_copy_id != $copy_id) {
                continue;
            }
            // Проверка наличия фильтров
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
            $filter_data = $this->getFilter($extension_copy, false);
            if (empty($filter_data)) {
                continue;
            }

            $relate_vars[] = [
                'copy_id' => $copy_id,
                'fields'  => null,
                'filters' => $filter_data,
                'relate'  => true,
            ];
            break;
        }

        if (empty($relate_vars)) {
            return;
        }

        $_query_vars['relate'] = $relate_vars;

        return $_query_vars;
    }

    /**
     * getFilterList
     */
    private function getFilterList($reports_id, $element_extension_copy, $filter_list_name, $use_ffn_real = false)
    {
        $result = $this->_filter_controller
            ->setUseFullFieldNameReal($use_ffn_real)
            ->getParamsToQuery($element_extension_copy, $filter_list_name, null, true, 2, $reports_id);

        return $result;
    }

    /**
     * getFilterListFromVirtualParams
     */
    private function getFilterListFromVirtualParams($element_extension_copy, $filter_params, $use_ffn_real = false, $add_table_name = true)
    {
        $result = $this->_filter_controller
            ->setUseFullFieldNameReal($use_ffn_real)
            ->getParamsToQueryFromVirtualParams($element_extension_copy, $filter_params, $add_table_name);

        return $result;
    }

    /**
     * mergeFilters
     */
    private function mergeFilters($filter_list)
    {
        if (empty($filter_list)) {
            return false;
        }

        // merge filters
        $conditions = [];
        $params = [];
        foreach ($filter_list as $data) {
            if (!empty($data['conditions'])) {
                $conditions = array_merge($conditions, $data['conditions']);
                $params = array_merge($params, $data['params']);
            }

        }

        if (!empty($conditions)) {
            $conditions = \FilterConcatModel::getInstance()
                ->setQuery($conditions)
                ->concat()
                ->getResultQuery();
        }

        if (!empty($conditions)) {
            array_unshift($conditions, 'AND');

            return [
                'conditions' => $conditions,
                'params'     => $params,
            ];
        }
    }

    /**
     * getFilterCopyIdList - возвращает список copy_id всех примененніх фильтров
     */
    private function getFilterCopyIdList()
    {
        $result = [];

        //filter_params
        if (!empty($this->_data_setting['filters']['filter_params'])) {
            foreach ($this->_data_setting['filters']['filter_params'] as $filter_param) {
                $result[] = $filter_param['copy_id'];
            }
        }

        // достаем copy_id из params модели фильтра
        if (!empty($this->_data_setting['filters']['filter_model'])) {
            $filter_id = $this->_data_setting['filters']['filter_model']->getText();
            $criteria = new \CDbCriteria();
            $criteria->addInCondition("filter_id", $filter_id);
            $filters = \Reports\models\ReportsFilterModel::model()->findAll($criteria);
            if (!empty($filters)) {
                foreach ($filters as $filter_model) {
                    if (empty($filter_model->params)) {
                        continue;
                    }
                    $params = json_decode($filter_model->params, true);
                    if (!empty($params)) {
                        foreach ($params as $param) {
                            $result[] = $param['copy_id'];
                        }
                    }
                }
            }
        }

        $result = array_unique($result);

        return $result;
    }

    /**
     * getFilter
     */
    private function getFilter($extension_copy, $add_filter_params_indicator)
    {
        $filter_params = null;

        $list = [
            'level1' => [],
            'level2' => [],
        ];

        //filter_params
        if (!empty($this->_data_setting['filters']['filter_params'])) {
            $filter_params = $this->_data_setting['filters']['filter_params'];

            list($this->_filter_controller) = \Yii::app()->createController(\ExtensionModel::model()->findByPk(\ExtensionModel::MODULE_REPORTS)->name . '/ListViewFilter');
            $tmp = $this->getFilterListFromVirtualParams($extension_copy, $filter_params, false);
            if (!empty($tmp)) {
                $list['level1'][] = $tmp;
            }
        }

        //filter_params_indicator
        if ($add_filter_params_indicator && !empty($this->_data_setting['filters']['filter_params_indicator'][$this->_data_setting['indicator']['unique_index']])) {
            $filter_params = $this->_data_setting['filters']['filter_params_indicator'][$this->_data_setting['indicator']['unique_index']];

            list($this->_filter_controller) = \Yii::app()->createController(\ExtensionModel::model()->findByPk(\ExtensionModel::MODULE_REPORTS)->name . '/ListViewFilter');
            $tmp = $this->getFilterListFromVirtualParams($extension_copy, $filter_params, false);
            if (!empty($tmp)) {
                $list['level2'][] = $tmp;
            }
        }

        //filter_model
        if (!empty($this->_data_setting['filters']['filter_model'])) {
            $filter_model = $this->_data_setting['filters']['filter_model'];
            if ($filter_model->isTextEmpty()) {
                return false;
            }

            $filter_params = $filter_model->getText();
            list($this->_filter_controller) = \Yii::app()->createController(\ExtensionModel::model()->findByPk(\ExtensionModel::MODULE_REPORTS)->name . '/ListViewFilter');
            $tmp = $this->getFilterList(\Yii::app()->request->getParam('id'), $extension_copy, $filter_params, false);
            if (!empty($tmp)) {
                $list['level1'][] = $tmp;
            }
        }

        if ($list['level1'] == false && $list['level2'] == false) {
            return false;
        }

        $conditions = [];
        $params = [];

        foreach (['level1', 'level2'] as $level) {
            $l = $list[$level];
            $l = $this->mergeFilters($l);

            if (empty($l)) {
                continue;
            }

            if ($level == 'level2' && $conditions) {
                $l['conditions'] = array_slice($l['conditions'], 1);
            }

            $conditions = array_merge($conditions, $l['conditions']);
            $params = array_merge($params, $l['params']);

            $conditions = \DataModel::getInstance()->replaceParamsOnRealValue($conditions, $params);
        }

        if ($conditions) {
            return [
                'conditions' => $conditions,
                'params'     => $params,
            ];
        }

        return false;
    }

    /**
     * getData
     */
    public function getData($extension_copy, $alias, $this_parent_table = false)
    {
        $filter_data = $this->getFilter($extension_copy, true);

        $is_set_search = $this->_is_set_search;

        $search_text = null;
        $search_fn_list = null;

        $get_only_parent = $this->getOnlyParent($this_parent_table, empty($filter_data));

        // search
        if ($this_parent_table && !empty($this->_data_setting['filters']['search_model'])) {

            $search_model = $this->_data_setting['filters']['search_model'];
            $search_text = $search_model->getText();

            if ($search_text !== null) {
                $is_set_search = true;

                // param
                // если поле Параметра - Дата
                if (\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name_real'])) {
                    if ($this->typeDateIsParentModule()) {
                        $search_fn_list = $this->getTypeDateFieldName(false);
                    }
                    $is_set_search = false;
                } else {
                    // indicators
                    $search_fn_list = [$this->_data_setting['param']['field_name_base']];
                    if ($this->_data_setting['param']['field_name_real'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID) {
                        $search_fn_list = array_merge($search_fn_list, $this->getIndicagorFields());
                        $get_only_parent = false;
                    }
                }
            }
        }

        // DataModel
        $data_model = new \DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setIsSetSearch($is_set_search)
            ->setFromModuleTables($get_only_parent);

        //responsible
        if ($extension_copy->isResponsible()) {
            $data_model->setFromResponsible(true);
        }

        //participant
        if ($extension_copy->isParticipant()) {
            $data_model->setFromParticipant(true);
        }

        // condition DateTime
        if ($this_parent_table && $this->typeDateIsParentModule()) {
            $data_model->andWhere(['AND', '(' . $extension_copy->getTableName() . '.' . $this->getTypeDateFieldName(false) . ' between "' . $this->_data_setting['filters']['_date_interval_start'] . ' 00:00:00" AND "' . $this->_data_setting['filters']['_date_interval_end'] . ' 23:59:59")']);
        }

        //condition this_template
        $data_model->andWhere(['AND', $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null']);

        // condition other
        if (!empty($filter_data)) {
            $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup()
            ->replaceParamsOnRealValue();

        /**
         * //participant only
         * if($extension_copy->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
         * $data_model->setOtherPartisipantAllowed();
         * }
         * // Добавляет условие отбора данных "только участники по связи через модуль через поле Название
         * if($extension_copy->dataIfParticipant() == false && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
         * $data_model->setDataBasedParentModule($extension_copy->copy_id);
         * }
         */

        //search
        if ($this_parent_table && $is_set_search) {
            $search_list = $data_model->getQueryWhereForSearch($search_text, $search_fn_list, true);
            if ($search_list == false) {
                return;
            }
            $data_model->setSearch($search_list);
        }

        $result = $data_model->getText();

        return '(' . $result . ') as ' . $alias;
    }

    /**
     * getConditionQueryForIndicatorFilter - добавляет запрос как часть условия where in (....),  если есть
     */
    public function getConditionQueryForIndicatorFilter($relate_extension_copy, $query_vars)
    {
        $extension_copy = $this->_extension_copy_param;

        switch (in_array($relate_extension_copy->copy_id, explode(',', $this->_data_setting['indicator']['module_copy_id']))) {
            case true :
                $s_field_name = $relate_extension_copy->getPkFieldName(true, true);
                $s_alias_filed_name = $this->getTableAliasName(self::ALIAS_NAME_RELATE, $this->_data_setting['indicator']['module_copy_id']) . '.' . $relate_extension_copy->getPkFieldName(false, false);
                break;
            case false :
                $s_field_name = $extension_copy->getPkFieldName(true, true);
                $s_alias_filed_name = $this->_parent_alias . '.' . $extension_copy->getPkFieldName(false, false);
                break;
        }

        //  get data
        $data_model = new \DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setQueryVars($query_vars)
            ->setFromModuleTables();

        //this_template
        $data_model->andWhere(['AND', $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null']);

        $data_model
            //->setFromFieldTypes()
            //->setCollectingSelect()
            ->setSelect($s_field_name)
            ->setQueryVarsRelateFilters()
            ->setGroup()
            ->replaceParamsOnRealValue();

        $query = $data_model->getText();

        return $s_alias_filed_name . ' in (' . $query . ')';
    }

    /**
     * возвращает статус для данных - выгружаться только данные родительской таблицы (без связанных)
     */
    private function getOnlyParent($this_parent_table, $empty_filters)
    {
        // если запрос к родительской таблице
        if ($this_parent_table) {
            if (array_key_exists('field_type', $this->_data_setting['param']) && in_array($this->_data_setting['param']['field_type'], ['select', 'relate'])) {
                return false;
            } else {
                if (array_key_exists('field_type', $this->_data_setting['indicator']) && in_array($this->_data_setting['indicator']['field_type'], ['relate'])) {
                    return false;
                }
            }
        }

        // если установлены фильтра
        if ($empty_filters == false) {
            return false;
        }

        return true;
    }

    /**
     * isSetIndicatorFilters - возвращает наличие фильтров в показателях
     */
    private function isSetIndicatorFilters()
    {
        if (self::$_is_set_indicator_filters !== null) {
            return self::$_is_set_indicator_filters;
        }

        if (empty($this->_data_setting['indicator_relate_modules'])) {
            self::$_is_set_indicator_filters = false;

            return false;
        }

        self::$_is_set_indicator_filters = true;

        return true;
    }

}
