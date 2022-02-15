<?php

class FilterMap
{
    const GROUP_1 = 'group1';
    const GROUP_2 = 'group2';
    const GROUP_3 = 'group3';
    const GROUP_4 = 'group4';
    const GROUP_5 = 'group5';

    /**
     * Типы фильтров.
     * Название ключа первого уровня выступает параметром группы (group) фильтра
     */
    private static $_filter_groups = [
        // for string and other types
        self::GROUP_1 => [
            'destination' => ['listView'],
            'list'        => [
                \FilterModel::FT_BEGIN_WITH,
                \FilterModel::FT_CORRESPONDS,
                \FilterModel::FT_CONTAINS,
            ],
        ],
        // for numeric and calculated type
        self::GROUP_2 => [
            'destination' => ['listView'],
            'list'        => [
                \FilterModel::FT_BEGIN_WITH,
                \FilterModel::FT_CORRESPONDS,
                \FilterModel::FT_EQUAL_NOT,
                \FilterModel::FT_MORE,
                \FilterModel::FT_LESS,

            ],
        ],
        // for date types
        self::GROUP_3 => [
            'destination' => ['listView'],
            'list'        => [
                \FilterModel::FT_DATE_FOR_TODAY,
                \FilterModel::FT_DATE_FOR_7_DAYS,
                \FilterModel::FT_DATE_FOR_30_DAYS,
                \FilterModel::FT_DATE_CURRENT_MONTH,
                \FilterModel::FT_DATE_PAST_MONTH,
                \FilterModel::FT_DATE_CURRENT_YEAR,
                \FilterModel::FT_DATE_PAST_YEAR,
                \FilterModel::FT_DATE_PERIOD,
                \FilterModel::FT_DATE_TO,
                \FilterModel::FT_DATE_AFTER,
                \FilterModel::FT_DATE_PRIOR_TO_CURRENT,
                \FilterModel::FT_DATE_AFTER_CURRENT
            ],
        ],
        // for participant types
        self::GROUP_4 => [
            'destination' => ['listView'],
            'list'        => [
                \FilterModel::FT_CORRESPONDS_RP
            ],
        ],
        // for relate, logical
        self::GROUP_5 => [
            'destination' => ['listView'],
            'list'        => [
                \FilterModel::FT_CORRESPONDS
            ],
        ],
    ];

    /**
     * Возвращает список (list) для из массива фильтров
     *
     * @param string $group - группы данних (название ключа первого уровня выступает параметром группы (group) фильтра)
     * @param mixed $destination - группа применения (ListView, Constructor...), в которой состоит фильтр
     * @param array $exception_position - список исключеных (по названию) фильтров
     * @return array
     */
    public static function getFilterList($group, $destination = [], array $exception_position = [])
    {
        if($group === null){
            return [];
        }

        $list = [];
        $filters = self::$_filter_groups[$group];
        if ($destination === null || ($destination !== null && in_array($destination, $filters['destination']))) {
            foreach ($filters['list'] as $filter_type) {
                if (!empty($exception_position)) {
                    if (!in_array($filter_type, $exception_position)) {
                        $list[$filter_type] = ['title' => self::getFilterTitle($filter_type)];
                    }
                } else {
                    $list[$filter_type] = ['title' => self::getFilterTitle($filter_type)];
                }
            }
        }

        return $list;
    }

    /**
     * Возвращает подпись фильтра
     *
     * @param $filter_type
     * @return string
     */
    public static function getFilterTitle($filter_type)
    {
        switch ($filter_type) {
            case \FilterModel::FT_BEGIN_WITH :
                return \Yii::t('filters', 'Begins with');
            case \FilterModel::FT_CORRESPONDS :
                return \Yii::t('filters', 'Corresponds');
            case \FilterModel::FT_CORRESPONDS_RP  :
                return \Yii::t('filters', 'Corresponds');
            case \FilterModel::FT_CONTAINS :
                return \Yii::t('filters', 'Contains');
            case \FilterModel::FT_BEGIN_WITH :
                return \Yii::t('filters', 'Begins with');
            case \FilterModel::FT_MORE :
                return \Yii::t('filters', 'More');
            case \FilterModel::FT_LESS  :
                return \Yii::t('filters', 'Less');
            case \FilterModel::FT_END  :
                return \Yii::t('filters', 'Ending');
            case \FilterModel::FT_EQUAL_NOT  :
                return \Yii::t('filters', 'Not corresponds'); //Not equal
            case \FilterModel::FT_DATE_FOR_TODAY  :
                return \Yii::t('filters', 'For today');
            case \FilterModel::FT_DATE_FOR_7_DAYS  :
                return \Yii::t('filters', 'For 7 days');
            case \FilterModel::FT_DATE_FOR_30_DAYS  :
                return \Yii::t('filters', 'For 30 days');
            case \FilterModel::FT_DATE_CURRENT_MONTH  :
                return \Yii::t('filters', 'Current month');
            case \FilterModel::FT_DATE_PAST_MONTH  :
                return \Yii::t('filters', 'Past month');
            case \FilterModel::FT_DATE_CURRENT_YEAR  :
                return \Yii::t('filters', 'Current year');
            case \FilterModel::FT_DATE_PAST_YEAR  :
                return \Yii::t('filters', 'Past year');
            case \FilterModel::FT_DATE_PERIOD  :
                return \Yii::t('filters', 'Period');
            case \FilterModel::FT_DATE_TO  :
                return \Yii::t('filters', 'To');
            case \FilterModel::FT_DATE_AFTER  :
                return \Yii::t('filters', 'After');
            case \FilterModel::FT_DATE_PRIOR_TO_CURRENT  :
                return \Yii::t('filters', 'Prior to the current date');
            case \FilterModel::FT_DATE_AFTER_CURRENT  :
                return \Yii::t('filters', 'After the current date');
        }
    }

    /**
     * oneArrayFilterList
     *
     * @param $filter_list
     */
    public static function oneArrayFilterList(&$filter_list)
    {
        foreach ($filter_list as $key => &$value) {
            $value = $value['title'];
        }
    }
}
