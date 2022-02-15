<?php

class CalendarViewModel
{

    // Константы действий
    const ACTION_GET_DATA_BY_PERIOD = 'get_data_by_period';             //DataByPeriod
    const ACTION_GET_DATA_BY_DATE_TIMES = 'get_data_by_date_times';         //DataByDateTimes
    const ACTION_GET_DATA_BY_DATE_TIME_RANGE = 'get_data_by_date_time_range';    //DataByDateTimeRange
    const ACTION_UPDATE_DATA = 'update_data';

    // Периоды
    const PERIOD_MONTH = 'month';
    const PERIOD_WEEK = 'week';
    const PERIOD_DAYS = 'days';

    /**
     * @var ExtensionCopyModel
     */
    private $_extension_copy;

    private $_vars;

    protected $_before_condition = [];

    protected $_before_params = [];

    protected $_last_condition = [];

    protected $_last_params = [];

    private $_error = false;

    private $_result;

    /**
     * @var string
     */
    private $active_field_name;

    /**
     * @var array
     */
    private $date_time_fields = [];

    /**
     * Список поле, которые запрещено обновлять.
     * Используется при перетаскивании сущностей в календаре
     *
     * @var string[]
     */
    private $update_disallow_fields = [
        'date_create',
        'date_edit',
    ];

    /**
     * @var bool
     */
    protected $_finished_object = false;

    /**
     * @param array $vars
     */
    public function __construct(array $vars)
    {
        $this->_vars = $vars;
        $this->validateCopyId();
        $this->initBase();
    }

    /***
     * @param $vars
     * @return $this
     */
    public function setVars($vars)
    {
        $this->_vars = $vars;

        return $this;
    }

    /**
     * @return bool
     */
    private function getStatus()
    {
        return ($this->_error ? false : true);
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * actionGetDataByPeriod - выгрузка данных по периоду дат с _vars['period'] по _vars['date_time']
     *
     * @param string|null $activeFieldName
     * @return $this
     */
    public function actionGetDataByPeriod($activeFieldName)
    {
        $this->setActiveFieldName($activeFieldName);

        $this
            ->validateGetDataByPeriod()
            ->initGetDataByPeriod()
            ->prepareGetDataArray()
            ->prepareGetDataByPeriodGeneral()
            ->prepareGetDataResult();

        return $this;
    }

    /**
     * actionGetDataByDateTimes - выгрузка данных по датам из перевенной _vars['date_time_list']
     *
     * @param string|null $activeFieldName
     * @return $this
     */
    public function actionGetDataByDateTimes($activeFieldName)
    {
        $this->setActiveFieldName($activeFieldName);

        $this
            ->validateGetDataByDateTimes()
            ->initGetDataByDateTimes()
            ->prepareGetDataArray()
            ->prepareGetDataByDateTimesGeneral()
            ->prepareGetDataResult();

        return $this;
    }

    /**
     * actionGetDataByDateTimeRange - выгрузка данных за период с _vars['date_time_from'] по _vars['date_time_to']
     *
     * @param string|null $activeFieldName
     * @return $this
     */
    public function actionGetDataByDateTimeRange($activeFieldName)
    {
        $this->setActiveFieldName($activeFieldName);

        $this
            ->validateGetDataByDateTimeRange()
            ->initGetDataByDateTimeRange()
            ->prepareGetDataArray()
            ->prepareGetDataByDateTimeRangeGeneral()
            ->prepareGetDataResult();

        return $this;
    }

    /**
     * actionUpdateDataByParams
     *
     * @param string|null $activeFieldName
     * @return $this
     */
    public function actionUpdateDataByParams($activeFieldName)
    {
        $this->setActiveFieldName($activeFieldName);

        $this
            ->validateUpdateDataByParams()
            ->initUpdateDataByParams()
            ->updateDataByParams()
            ->prepareUpdateDataByParamResult();

        return $this;
    }

    /**
     * Устанавливает название активного поля, что имеет тип Дата/время
     *
     * @param $fieldName
     */
    private function setActiveFieldName($fieldName = null)
    {
        if (array_key_exists($fieldName, $this->date_time_fields)) {
            $this->active_field_name = $fieldName;
        } else {
            if ($this->date_time_fields) {
                $this->active_field_name = array_keys($this->date_time_fields)[0];
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getActiveFieldName()
    {
        return $this->active_field_name;
    }

    /**
     * @return string|null
     */
    private function getActiveFieldNameAd()
    {
        return $this->activeFieldNameIsDateEndind() && $this->getActiveFieldName() ? $this->getActiveFieldName() . '_ad' : null;
    }

    /**
     * @return bool
     */
    private function activeFieldNameIsDateEndind(): bool
    {
        $fieldName = $this->getActiveFieldName();

        return $fieldName ? $this->date_time_fields[$fieldName]['type_view'] === Fields::TYPE_VIEW_BUTTON_DATE_ENDING : false;
    }

    /**
     * @param $condition
     * @param array|null $params
     * @return $this
     */
    private function addBeforeCondition($condition, array $params = null)
    {
        $this->_before_condition[] = $condition;
        if ($params) {
            $this->_before_params += $params;
        }

        return $this;
    }

    /**
     * Проверка на доступ на обновление данных сущности
     *
     * @param $fieldName
     * @return bool
     */
    private function updateDisallow($fieldName): bool
    {
        return in_array($fieldName, $this->update_disallow_fields);
    }

    private function getBeforeCondition()
    {
        if ($this->_before_condition) {
            return implode(' AND ', $this->_before_condition);
        }
    }

    private function getBeforeParams()
    {
        return $this->_before_params;
    }

    private function getLastCondition()
    {
        if ($this->_last_condition) {
            return implode(' AND ', $this->_last_condition);
        }
    }

    private function getLastParams()
    {
        return $this->_last_params;
    }

    private function getTitleFieldName()
    {
        return 'module_title';
    }

    /**
     * validateGetDataByPeriod
     */
    private function validateCopyId()
    {
        $attr_list = ['copy_id'];

        foreach ($attr_list as $attr_name) {
            if (!array_key_exists($attr_name, $this->_vars)) {
                $this->_error = true;
                break;
            }

            if (empty($this->_vars[$attr_name])) {
                $this->_error = true;
                break;
            }
        }

        return $this;
    }

    /**
     * validateGetDataByPeriod
     */
    private function validateGetDataByPeriod()
    {
        $attr_list = ['period', 'date_time'];

        foreach ($attr_list as $attr_name) {
            if (!array_key_exists($attr_name, $this->_vars)) {
                $this->_error = true;
                break;
            }

            if (empty($this->_vars[$attr_name])) {
                $this->_error = true;
                break;
            }
        }

        return $this;
    }

    /**
     * validateGetDataByDateTimes
     */
    private function validateGetDataByDateTimes()
    {
        $attr_list = ['period', 'date_time_list'];

        foreach ($attr_list as $attr_name) {
            if (!array_key_exists($attr_name, $this->_vars)) {
                $this->_error = true;
                break;
            }

            if (empty($this->_vars[$attr_name])) {
                $this->_error = true;
                break;
            }
        }

        return $this;
    }

    /**
     * validateGetDataByDateTimeRange
     */
    private function validateGetDataByDateTimeRange()
    {
        $attr_list = ['period', 'date_time_from', 'date_time_to'];

        foreach ($attr_list as $attr_name) {
            if (!array_key_exists($attr_name, $this->_vars)) {
                $this->_error = true;
                break;
            }

            if (empty($this->_vars[$attr_name])) {
                $this->_error = true;
                break;
            }
        }

        if (strtotime($this->_vars['date_time_from']) == false) {
            $this->_error = true;
        }
        if (strtotime($this->_vars['date_time_to']) == false) {
            $this->_error = true;
        }

        return $this;
    }

    /**
     * validateUpdateDataByParams
     */
    private function validateUpdateDataByParams()
    {
        $attr_list = ['period', 'id', 'attributes'];

        foreach ($attr_list as $attr_name) {
            if (!array_key_exists($attr_name, $this->_vars)) {
                $this->_error = true;
                break;
            }

            if (empty($this->_vars[$attr_name])) {
                $this->_error = true;
                break;
            }
        }

        if ($this->updateDisallow($this->getActiveFieldName())) {
            $this->_error = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function prepareDateTimeFields()
    {
        if (!$this->_extension_copy) {
            return $this;

        }
        $schema_parse = $this->_extension_copy->getSchemaParse();
        if (!empty($schema_parse['elements'])) {
            foreach ($schema_parse['elements'] as $value) {
                if (isset($value['field']) && $value['field']['params']['type'] == 'datetime') {
                    $this->date_time_fields[$value['field']['params']['name']] = [
                        'title'     => $value['field']['title'],
                        'type_view' => $value['field']['params']['type_view'],
                    ];
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getDateTimeFieldsAsOptions()
    {
        return array_map(function ($v) {
            return $v['title'];
        }, $this->date_time_fields);
    }

    /**
     * @return $this|void
     */
    private function initBase()
    {
        if ($this->_error) {
            return $this;
        }

        $this->_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['copy_id']);
        $this->prepareDateTimeFields();

        return $this;
    }

    /**
     * @return $this
     */
    private function initGetDataByPeriod()
    {
        if ($this->_error) {
            return $this;
        }

        //2. set condition period date
        $date_time = $this->_vars['date_time'];

        $date_time_from = $this->getDateTimeFirstByPeriod($date_time);
        $date_time_to = $this->getDateTimeLastByPeriod($date_time_from);

        if (!$date_time_from || !$date_time_to) {
            $this->_error = true;
        } else {
            $table_name = $this->_extension_copy->getTableName();
            $field_name = $this->getActiveFieldName();
            $condition = $table_name . '.' . $field_name . ' BETWEEN :date_time_from AND :date_time_to';
            $params = [
                ':date_time_from' => $date_time_from,
                ':date_time_to'   => $date_time_to,
            ];
            $this->addBeforeCondition($condition, $params);
        }

        return $this;
    }

    /**
     * initGetDataByDateTimes
     */
    private function initGetDataByDateTimes()
    {
        if ($this->_error) {
            return $this;
        }

        $condition = [];
        $table_name = $this->_extension_copy->getTableName();
        $field_name = $this->getActiveFieldName();

        foreach ($this->_vars['date_time_list'] as $date) {
            $date_time = $date['date_time'];
            $all_day = (array_key_exists('all_day', $date) ? $date['all_day'] : null); // свойственно полько полю "Дата завершения"

            //$date_time_first
            $date_time_first = $this->getDateTimeFirstByTime($date_time, $all_day);
            if (!$date_time_first) {
                $this->_error = true;

                return;
            }

            if ($all_day && in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
                $condition[] = '(' . $table_name . '.' . $field_name . ' = "' . $date_time_first . '" AND ' . $table_name . '.' . $field_name . '_ad = "' . $all_day . '" )';
                continue;
            }

            //$date_time_last
            $date_time_last = $this->getDateTimeLastByTime($date_time_first);
            if (!$date_time_last) {
                $this->_error = true;

                return;
            }

            if (in_array($this->_vars['period'], [self::PERIOD_MONTH])) {
                $condition[] = '(' . $table_name . '.' . $field_name . ' BETWEEN "' . $date_time_first . '" AND "' . $date_time_last . '")';
            } else {
                if (in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
                    if ($all_day) {
                        $condition[] = '(' . $table_name . '.' . $field_name . ' = "' . $date_time_first . '" AND ' . $table_name . '.' . $field_name . '_ad = "' . $all_day . '" )';
                    } else {
                        $condition[] = '(' . $table_name . '.' . $field_name . ' = "' . $date_time_first . '")';
                    }
                }
            }
        }

        if ($condition) {
            $this->addBeforeCondition('(' . implode(' OR ', $condition) . ')');
        } else {
            $this->_error = true;
        }

        return $this;
    }

    /**
     * initGetDataByDateTimeRange
     */
    private function initGetDataByDateTimeRange()
    {
        if ($this->_error) {
            return $this;
        }

        //2. set condition period date
        $table_name = $this->_extension_copy->getTableName();
        $field_name = $this->getActiveFieldName();

        $condition = $table_name . '.' . $field_name . ' BETWEEN :date_time_from AND :date_time_to';
        $params = [
            ':date_time_from' => $this->_vars['date_time_from'],
            ':date_time_to'   => $this->_vars['date_time_to'],
        ];
        $this->addBeforeCondition($condition, $params);

        return $this;
    }

    private function initUpdateDataByParams()
    {
        if ($this->_error) {
            return $this;
        }

        return $this;
    }

    /**
     * getDateTimeFirstByPeriod
     */
    private function getDateTimeFirstByPeriod($date_time)
    {
        $result_date = null;

        $period = $this->_vars['period'];

        switch ($period) {
            case self::PERIOD_MONTH:
                $result_date = date('Y-m-01 00:00:00', strtotime($date_time));
                break;
            case self::PERIOD_WEEK:
            case self::PERIOD_DAYS:
                $result_date = date('Y-m-d 00:00:00', strtotime($date_time));
                break;
        }

        return $result_date;
    }

    /**
     * getDateTimeLastByPeriod
     */
    private function getDateTimeLastByPeriod($date_time)
    {
        $result_date = null;
        $period = $this->_vars['period'];

        switch ($period) {
            case self::PERIOD_MONTH:
                $day_of_month = DateTimeOperations::getDaysOfMonth($date_time);
                $result_date = date('Y-m-' . $day_of_month . ' 23:59:59', strtotime($date_time));
                break;

            case self::PERIOD_WEEK:
                $date_time = new DateTime($date_time);
                $date_time->modify('+7 days');
                $result_date = $date_time->format('Y-m-d 23:59:59');
                break;

            case self::PERIOD_DAYS:
                $result_date = date('Y-m-d 23:59:59', strtotime($date_time));
                break;
        }

        return $result_date;
    }

    /**
     * getDateTimeFirstByTime
     */
    private function getDateTimeFirstByTime($date_time, $all_day = null)
    {
        $result_date = null;

        $period = $this->_vars['period'];

        switch ($period) {
            case self::PERIOD_MONTH:
                $result_date = date('Y-m-d 00:00:00', strtotime($date_time));
                break;
            case self::PERIOD_WEEK:
            case self::PERIOD_DAYS:
                if ($all_day) {
                    $time = '23:59:59';
                } else {
                    $time = $this->getFirstTimeByDate($date_time);
                }
                $result_date = date('Y-m-d ' . $time, strtotime($date_time));
                break;
        }

        return $result_date;
    }

    /**
     * getDateTimeLastByPeriod
     */
    private function getDateTimeLastByTime($date_time)
    {
        if (in_array($this->_vars['period'], [self::PERIOD_MONTH])) {
            $result_date = date('Y-m-d 23:59:59', strtotime($date_time));
        } else {
            if (in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
                $date_model = new DateTime($date_time);
                $date_model->modify('+30 minutes');

                $result_date = $date_model->format('Y-m-d H:i:s');
            }
        }

        return $result_date;
    }

    /**
     * getQueryCalendarWeekTimeIxdex - вынужденный костыль для баз, где невозможно создать функции
     */
    private function getFirstTimeByDate($date_time)
    {
        $time = (int)date('Hi', strtotime($date_time));

        switch ($time) {
            case $time < 30:
                return '00:00:00';
            case $time < 100:
                return '00:30:00';
            case $time < 130:
                return '01:00:00';
            case $time < 200:
                return '01:30:00';
            case $time < 230:
                return '02:00:00';
            case $time < 300:
                return '02:30:00';
            case $time < 330:
                return '03:00:00';
            case $time < 400:
                return '03:30:00';
            case $time < 430:
                return '04:00:00';
            case $time < 500:
                return '04:30:00';
            case $time < 530:
                return '05:00:00';
            case $time < 600:
                return '05:30:00';
            case $time < 630:
                return '06:00:00';
            case $time < 700:
                return '06:30:00';
            case $time < 730:
                return '07:00:00';
            case $time < 800:
                return '07:30:00';
            case $time < 830:
                return '08:00:00';
            case $time < 900:
                return '08:30:00';
            case $time < 930:
                return '09:00:00';
            case $time < 1000:
                return '9:30:00';
            case $time < 1030:
                return '10:00:00';
            case $time < 1100:
                return '10:30:00';
            case $time < 1130:
                return '11:00:00';
            case $time < 1200:
                return '11:30:00';
            case $time < 1230:
                return '12:00:00';
            case $time < 1300:
                return '12:30:00';
            case $time < 1330:
                return '13:00:00';
            case $time < 1400:
                return '13:30:00';
            case $time < 1430:
                return '14:00:00';
            case $time < 1500:
                return '14:30:00';
            case $time < 1530:
                return '15:00:00';
            case $time < 1600:
                return '15:30:00';
            case $time < 1630:
                return '16:00:00';
            case $time < 1700:
                return '16:30:00';
            case $time < 1730:
                return '17:00:00';
            case $time < 1800:
                return '17:30:00';
            case $time < 1830:
                return '18:00:00';
            case $time < 1900:
                return '18:30:00';
            case $time < 1930:
                return '19:00:00';
            case $time < 2000:
                return '19:30:00';
            case $time < 2030:
                return '20:00:00';
            case $time < 2100:
                return '20:30:00';
            case $time < 2130:
                return '21:00:00';
            case $time < 2200:
                return '21:30:00';
            case $time < 2230:
                return '22:00:00';
            case $time < 2300:
                return '22:30:00';
            case $time < 2330:
                return '23:00:00';
            case $time >= 2330:
                return '23:30:00';
        }
    }

    /**
     * getListViewQueryConditionLimit
     *
     * @param $add_days
     * @param $get_count
     * @return string|void
     */
    private function getListViewQueryConditionLimit($add_days, $get_count)
    {
        $_list_view_condition_limit = [
            // all days = true
            1 => [
                self::PERIOD_MONTH => [
                    1 => 3,
                    0 => 3,
                ],
                self::PERIOD_WEEK  => [
                    1 => 3,
                    0 => 3,
                ],
                self::PERIOD_DAYS  => [
                    1 => null,
                    0 => null,
                ],
            ],
            // all days = false
            0 => [
                self::PERIOD_MONTH => [
                    1 => 3,
                    0 => 3,
                ],
                self::PERIOD_WEEK  => [
                    1 => 4,
                    0 => 4,
                ],
                self::PERIOD_DAYS  => [
                    1 => 8,
                    0 => 8,
                ],
            ],
        ];

        $add_days = ($add_days ? 1 : 0);
        $get_count = ($get_count ? 1 : 0);

        $count = $_list_view_condition_limit[$add_days][$this->_vars['period']][$get_count];

        if ($count == false) {
            return;
        }

        $n = ' N <= ';
        if ($get_count) {
            $n = ' N > ';
        }

        return $n . $count;
    }

    /**
     * getQueryCalendarWeekTimeIxdex - вынужденный костыль для баз, где невозможно создать функции
     */
    private function getQueryCalendarWeekTimeIxdex($a)
    {
        return '
            CASE
                WHEN ' . $a . '< 30 THEN "00:00"
                WHEN ' . $a . '<100 THEN "00:30"
                WHEN ' . $a . '<130 THEN "01:00"
                WHEN ' . $a . '<200 THEN "01:30"
                WHEN ' . $a . '<230 THEN "02:00"
                WHEN ' . $a . '<300 THEN "02:30"
                WHEN ' . $a . '<330 THEN "03:00"
                WHEN ' . $a . '<400 THEN "03:30"
                WHEN ' . $a . '<430 THEN "04:00"
                WHEN ' . $a . '<500 THEN "04:30"
                WHEN ' . $a . '<530 THEN "05:00"
                WHEN ' . $a . '<600 THEN "05:30"
                WHEN ' . $a . '<630 THEN "06:00"
                WHEN ' . $a . '<700 THEN "06:30"
                WHEN ' . $a . '<730 THEN "07:00"
                WHEN ' . $a . '<800 THEN "07:30"
                WHEN ' . $a . '<830 THEN "08:00"
                WHEN ' . $a . '<900 THEN "08:30"
                WHEN ' . $a . '<930 THEN "09:00"
                WHEN ' . $a . '<1000 THEN "09:30"
                WHEN ' . $a . '<1030 THEN "10:00"
                WHEN ' . $a . '<1100 THEN "10:30"
                WHEN ' . $a . '<1130 THEN "11:00"
                WHEN ' . $a . '<1200 THEN "11:30"
                WHEN ' . $a . '<1230 THEN "12:00"
                WHEN ' . $a . '<1300 THEN "12:30"
                WHEN ' . $a . '<1330 THEN "13:00"
                WHEN ' . $a . '<1400 THEN "13:30"
                WHEN ' . $a . '<1430 THEN "14:00"
                WHEN ' . $a . '<1500 THEN "14:30"
                WHEN ' . $a . '<1530 THEN "15:00"
                WHEN ' . $a . '<1600 THEN "15:30"
                WHEN ' . $a . '<1630 THEN "16:00"
                WHEN ' . $a . '<1700 THEN "16:30"
                WHEN ' . $a . '<1730 THEN "17:00"
                WHEN ' . $a . '<1800 THEN "17:30"
                WHEN ' . $a . '<1830 THEN "18:00"
                WHEN ' . $a . '<1900 THEN "18:30"
                WHEN ' . $a . '<1930 THEN "19:00"
                WHEN ' . $a . '<2000 THEN "19:30"
                WHEN ' . $a . '<2030 THEN "20:00"
                WHEN ' . $a . '<2100 THEN "20:30"
                WHEN ' . $a . '<2130 THEN "21:00"
                WHEN ' . $a . '<2200 THEN "21:30"
                WHEN ' . $a . '<2230 THEN "22:00"
                WHEN ' . $a . '<2300 THEN "22:30"
                WHEN ' . $a . '<2330 THEN "23:00"
                WHEN ' . $a . '>=2330 THEN "23:30"
                ELSE null
            END';
    }

    /**
     * @param false $get_count
     * @return array
     */
    private function getListViewDataSqlParams($get_count = false)
    {
        $table_name = $this->_extension_copy->getTableName();
        $date_time_fn = $this->getActiveFieldName();

        if ($this->activeFieldNameIsDateEndind() && in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
            if ($this->activeFieldNameIsDateEndind()) {
                $this->addBeforeCondition($table_name . '.' . $this->getActiveFieldNameAd() . '="1"');
                $this->_before_condition = array_values($this->_before_condition);
            }
        }

        //1. get data
        $data_model = $this->getListViewDataModel();
        $query_module = $data_model->getText();
        $params = $data_model->getParams();

        $last_session_id_request = 'DATE_FORMAT(' . $date_time_fn . ', "%Y-%m-%d")';
        $order_by = $date_time_fn . ' asc, ' . $this->_extension_copy->getPkFieldName() . ' desc';

        if (in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
            if ($this->activeFieldNameIsDateEndind()) {
                $last_session_id_request = 'concat(DATE_FORMAT(' . $date_time_fn . ', "%Y-%m-%d"), "_", ' . $this->getQueryCalendarWeekTimeIxdex('DATE_FORMAT(' . $date_time_fn . ', "%H%i")') . ', "_", ' . $this->getActiveFieldNameAd() . ')';
                $order_by = 'DATE_FORMAT(' . $date_time_fn . ', "%Y-%m-%d"), ' . $this->getActiveFieldNameAd() . ', ' . $date_time_fn . ' asc,' . $this->_extension_copy->getPkFieldName() . ' desc';
            } else {
                $last_session_id_request = 'concat(DATE_FORMAT(' . $date_time_fn . ', "%Y-%m-%d"), "_", ' . $this->getQueryCalendarWeekTimeIxdex('DATE_FORMAT(' . $date_time_fn . ', "%H%i")') . ')';
                $order_by = 'DATE_FORMAT(' . $date_time_fn . ', "%Y-%m-%d"), ' . $date_time_fn . ' asc,' . $this->_extension_copy->getPkFieldName() . ' desc';
            }
        }

        $condition_limit = $this->getListViewQueryConditionLimit(true, $get_count);
        $query = '
            SELECT *
            FROM (
                SELECT
                    *,
                    IF(@last_session_id=' . $last_session_id_request . ', @I:=@I+1, @I:=1) as N,
                    @last_session_id := ' . $last_session_id_request . ' as last_session_id
            FROM
                (' . $query_module . ') as data,
                (SELECT @last_session_id := null, @I := 0) as T
                ORDER BY ' . $order_by . '
            ) as T' . ($condition_limit ? ' WHERE ' . $condition_limit : '');

        //2. get data
        if ($this->activeFieldNameIsDateEndind() && in_array($this->_vars['period'], [self::PERIOD_WEEK, self::PERIOD_DAYS])) {
            $query .= ' UNION ALL ';

            if ($this->_before_condition) {
                unset($this->_before_condition[count($this->_before_condition) - 1]);
            }
            if ($this->activeFieldNameIsDateEndind()) {
                $this->addBeforeCondition($table_name . '.' . $this->getActiveFieldNameAd() . '="0"');
            }
            $this->_before_condition = array_values($this->_before_condition);

            //2. get data
            $data_model = $this->getListViewDataModel();
            $query_module = $data_model->getText();
            $params += $data_model->getParams();

            if ($this->_before_condition) {
                unset($this->_before_condition[count($this->_before_condition) - 1]);
            }

            $condition_limit = $this->getListViewQueryConditionLimit(false, $get_count);

            $query .= '
            SELECT *
                FROM (
                    SELECT
                        *,
                        IF(@last_session_id=' . $last_session_id_request . ', @I:=@I+1, @I:=1) as N,
                        @last_session_id := ' . $last_session_id_request . ' as last_session_id
                FROM
                    (' . $query_module . ') as data,
                    (SELECT @last_session_id := null, @I := 0) as T
                    ORDER BY ' . $order_by . '
                ) as T' . ($condition_limit ? ' WHERE ' . $condition_limit : '');
        }

        $result = [
            'query'  => $query,
            'params' => $params,
        ];

        return $result;
    }

    /**
     * @return mixed
     */
    private function getListViewDataList()
    {
        $sql_params = $this->getListViewDataSqlParams(false);

        $data_list = (new \DataModel())
            ->setText($sql_params['query'])
            ->setParams($sql_params['params'])
            ->findAll();

        return $data_list;
    }

    private function getListViewDataCount()
    {

        if ($this->_vars['period'] == self::PERIOD_DAYS) {
            return;
        }

        $sql_params = $this->getListViewDataSqlParams(true);
        $field_name = $this->getActiveFieldName();

        $select = 'DATE_FORMAT(' . $field_name . ', "%Y-%m-%d") as date_time, count(*) as count';

        if (in_array($this->_vars['period'], [self::PERIOD_WEEK])) {
            $datetime_query = 'concat(DATE_FORMAT(' . $field_name . ', "%Y-%m-%d"), " ", ' . $this->getQueryCalendarWeekTimeIxdex('DATE_FORMAT(' . $field_name . ', "%H%i")') . ')';
            if ($this->activeFieldNameIsDateEndind()) { // Проверка на поле "Дата завершения"
                $field_name_all_day = $this->getActiveFieldNameAd();
                $select = $datetime_query . ' as date_time, ' . $field_name_all_day . ' as all_day, count(*) as count';
            } else {
                $select = $datetime_query . ' as date_time, 0 as all_day, count(*) as count';
            }
        }

        $query = '
            SELECT ' . $select . '
            FROM (
              ' . $sql_params['query'] . '
            ) as data
            GROUP BY last_session_id       
        ';

        $data_list = (new \DataModel())
            ->setText($query)
            ->setParams($sql_params['params'])
            ->findAll();

        return $data_list;
    }

    /**
     *
     */
    private function getListViewDataModel()
    {
        if ($this->_error) {
            return;
        }

        $global_params = [
            'pci'             => null,
            'pdi'             => null,
            'finished_object' => $this->_vars['finished_object'],
        ];

        Pagination::$active_page = null;
        Pagination::$active_page_size = null;

        $data_model = \DataListModel::getInstance()
            ->setExtensionCopy($this->_extension_copy)
            ->setFinishedObject($this->_finished_object)
            ->setThisTemplate(null)
            ->setGlobalParams($global_params)
            ->setSortingToPk('desc')
            ->setBeforeCondition($this->getBeforeCondition(), $this->getBeforeParams())
            ->setLastCondition($this->getLastCondition(), $this->getLastParams())
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getDataModel();

        return $data_model;
    }

    /**
     * @return $this
     */
    private function prepareGetDataArray()
    {
        if ($this->_error) {
            return $this;
        }

        //1. getListViewDataList
        $data_array = [];
        $data_list = $this->getListViewDataList();

        if ($data_list) {
            $pk_fieldname = $this->_extension_copy->getPkFieldName();
            $title_fieldname = $this->getTitleFieldName();

            foreach ($data_list as $data) {
                $tmp = [
                    'id'        => $data[$pk_fieldname],
                    'date_time' => $data[$this->getActiveFieldName()],
                    'all_day'   => $this->activeFieldNameIsDateEndind() ? $data[$this->getActiveFieldNameAd()] : null,
                    'title'     => $data[$title_fieldname],
                ];

                $data_array[] = $tmp;
            }
        }

        $this->_result['data'] = $data_array;

        //2. getListViewDataCount
        if (in_array($this->_vars['period'], [self::PERIOD_MONTH, self::PERIOD_WEEK])) {
            $this->_result['data_count'] = $this->getListViewDataCount();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function prepareGetDataByPeriodGeneral()
    {
        if ($this->_error) {
            return $this;
        }

        $data = [
            'copy_id'              => $this->_extension_copy->copy_id,
            'title'                => $this->_extension_copy->title,
            'this_templates'       => null,
            'finished_object'      => $this->_vars['finished_object'],
            'pci'                  => null,
            'pdi'                  => null,
            'period_type'          => $this->_vars['period'], // week, days +++
            'current_date'         => $this->_vars['date_time'],
            'date_time_fields'     => $this->getDateTimeFieldsAsOptions(),
            'date_time_field_name' => $this->getActiveFieldName(),
            'update_disallow'      => $this->updateDisallow($this->getActiveFieldName()),
            'has_all_day'          => $this->activeFieldNameIsDateEndind(),
        ];

        $this->_result['general'] = $data;

        return $this;
    }

    private function prepareGetDataByDateTimesGeneral()
    {
        if ($this->_error) {
            return $this;
        }

        $data = [
            'copy_id'              => $this->_extension_copy->copy_id,
            'title'                => $this->_extension_copy->title,
            'this_templates'       => null,
            'finished_object'      => $this->_vars['finished_object'],
            'pci'                  => null,
            'pdi'                  => null,
            'period_type'          => $this->_vars['period'], // week, days +++
            'current_date'         => $this->_vars['date_time_list'][count($this->_vars['date_time_list']) - 1],
            'date_time_fields'     => $this->getDateTimeFieldsAsOptions(),
            'date_time_field_name' => $this->getActiveFieldName(),
            'update_disallow'      => $this->updateDisallow($this->getActiveFieldName()),
            'has_all_day'          => $this->activeFieldNameIsDateEndind(),
        ];

        $this->_result['general'] = $data;

        return $this;
    }

    private function prepareGetDataByDateTimeRangeGeneral()
    {
        if ($this->_error) {
            return $this;
        }

        $data = [
            'copy_id'              => $this->_extension_copy->copy_id,
            'title'                => $this->_extension_copy->title,
            'this_templates'       => null,
            'finished_object'      => $this->_vars['finished_object'],
            'pci'                  => null,
            'pdi'                  => null,
            'period_type'          => $this->_vars['period'], // week, days +++
            'current_date'         => date('Y-m-d H:i:s'),
            'date_time_fields'     => $this->getDateTimeFieldsAsOptions(),
            'date_time_field_name' => $this->getActiveFieldName(),
            'update_disallow'      => $this->updateDisallow($this->getActiveFieldName()),
            'has_all_day'          => $this->activeFieldNameIsDateEndind(),
        ];

        $this->_result['general'] = $data;

        return $this;
    }

    /**
     * @param string $date_time_old
     * @return string|null
     */
    private function getDateTimeForUpdate($date_time_old)
    {
        $date_time = $this->_vars['attributes']['date_time'];
        $result_date = null;
        $time = null;

        switch ($this->_vars['period']) {
            case self::PERIOD_MONTH:
                $time = date('H:i:s', strtotime($date_time_old));
            case self::PERIOD_WEEK:
            case self::PERIOD_DAYS:
                if ($this->activeFieldNameIsDateEndind() && $this->_vars['attributes']['all_day']) {
                    $result_date = date('Y-m-d 23:59:59', strtotime($date_time));
                } else {
                    if ($time) {
                        $result_date = date('Y-m-d ' . $time, strtotime($date_time));
                    } else {
                        $result_date = date('Y-m-d H:i:s', strtotime($date_time));
                    }
                }

                break;
        }

        return $result_date;
    }

    /**
     * Обновление данных даты при перетаскивании
     *
     * @return $this
     */
    private function updateDataByParams()
    {
        $data_entity = (new DataModel())
            ->setFrom($this->_extension_copy->getTableName())
            ->setWhere($this->_extension_copy->getPkfieldName() . '=' . $this->_vars['id'])
            ->findRow();

        if ($data_entity == false) {
            $this->_error = true;

            return $this;
        }

        $date_time = $this->getDateTimeForUpdate(
            $data_entity[$this->getActiveFieldName()]
        );
        if ($date_time == false) {
            $this->_error = true;

            return $this;
        }

        //attributes
        $attributes = [
            $this->getActiveFieldName() => $date_time,
        ];

        // Для поля "Дата завершения"
        if ($this->activeFieldNameIsDateEndind()) {
            $attributes[$this->getActiveFieldNameAd()] = $this->_vars['attributes']['all_day'];
        }

        //update
        (new DataModel())->Update(
            $this->_extension_copy->getTableName(),
            $attributes,
            $this->_extension_copy->getPkfieldName() . '=' . $this->_vars['id']
        );

        return $this;
    }

    private function prepareGetDataResult()
    {
        $this->_result['status'] = $this->getStatus();

        return $this;
    }

    private function prepareUpdateDataByParamResult()
    {
        $this->_result['status'] = $this->getStatus();

        return $this;
    }

    public function setFinishedObject($finished_object)
    {
        $this->_finished_object = $finished_object;

        return $this;
    }
}


/*
 *
GetDataByPeriod()
    period : 'month',
    date_time : 2018-01-01,


GetDataByDateTimes()
    period : 'month',
    date_time_list[] : [
        date_time : '2018-01-01 00:30:00' - 2018-01-01 01:00:00
        date_time_ad : 1,
    ],
    date_time_list[] : [
        date_time : '2018-01-01 00:05:22',
        date_time_ad : 1,
    ]


*/
