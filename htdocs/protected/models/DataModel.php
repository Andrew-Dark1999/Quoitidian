<?php

class DataModel
{

    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';
    const JOIN_INNER = 'inner';

    private $command;

    private $extension_copy;

    private $select = [];

    public $having_conditions = [];

    public $having_params = [];

    public $_is_set_search = false; // указывает, что будет установлено условие для поиска в LV || PV

    public $_add_sql_calc_found_rows = false;

    private $_concat_ws_field_name = [];

    private $_search_ws_sdm = false;

    private $_search_ws_sm = false;

    private $_joined_list = [];

    private $_participant_group_field = null;

    private $_query_vars = [
        /*
        // пример структуры
        'parent' => array(
            'fields' => null,
        ),
        'relate' => array(
            array(
                'copy_id' => null,
                'fields' => null,
                'filters' => null,
                'relate' => false,
            )
        ),
        */
    ];

    public function __construct()
    {
        \TimeZonesModel::setTimeZone();

        $this->command = \Yii::app()->db->createCommand();
    }

    public static function getInstance()
    {
        return new self();
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->extension_copy = $extension_copy;

        return $this;
    }

    public function setIsSetSearch($is_set_search)
    {
        $this->_is_set_search = $is_set_search;

        return $this;
    }

    public function setSearchWsSdm($search_ws_sdm)
    {
        $this->_search_ws_sdm = $search_ws_sdm;

        return $this;
    }

    public function setSearchWsSm($search_ws_sm)
    {
        $this->_search_ws_sm = $search_ws_sm;

        return $this;
    }

    public function setQueryVars($query_vars)
    {
        $this->_query_vars = $query_vars;

        return $this;
    }

    public function setText($query)
    {
        $this->command->setText($query);

        return $this;
    }

    public function getText()
    {
        return $this->command->getText();
    }

    public function setLimit($value)
    {
        $this->command->setLimit($value);

        return $this;
    }

    public function setOffSet($value)
    {
        $this->command->setOffSet($value);

        return $this;
    }

    public function addSqlCallFoundRows()
    {
        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;

        $this->command->reset();
        $this->command->select("SQL_CALC_FOUND_ROWS (0), data.*");
        $this->command->setFrom($query);
        $this->command->params = $params;

        return $this;
    }

    public function setSelect($select = '*')
    {
        if (is_array($select)) {
            $select = implode(',', $select);
        }
        $this->command->select($select);

        return $this;
    }

    public function setSelectFoundRows()
    {
        $this->command->select('FOUND_ROWS()');

        return $this;
    }

    public function getSelect()
    {
        return $this->command->getSelect();
    }

    public function addSelect($select)
    {
        $this->select[] = $select;

        return $this;
    }

    /**
     * добавляем конструкцию case к запросу. используется для замены значений поля
     *
     * @param $field - поле для замены
     * @param $params - ассоциативный массив для замены
     * @param $start_value - индекс в массиве, который заменяем
     * @param $replace_value - индекс в массиве, которым заменяем
     * @param $full_as - маркер добавления названия таблицы в алиасе
     */
    public function addCase($field, $params, $start_value, $replace_value, $full_as = false)
    {

        if (count($params) > 0) {
            $query = "CASE";
            foreach ($params as $param) {
                $query .= '
                    WHEN ' . $this->extension_copy->getTableName() . '.' . $field . ' = "' . $param[$start_value] . '"
                    THEN "' . $param[$replace_value] . '"';
            }
            if ($full_as) {
                $field = $this->extension_copy->getTableName() . '.' . $field;
            }
            $query .= ' ELSE "" END AS "' . $field . '"';
            $this->addSelect($query);
        }

        return $this;
    }

    public function setCollectingSelect()
    {
        $select = implode(',', $this->select);
        $this->setSelect($select);

        return $this;
    }

    public function setSelectNew()
    {
        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        return $this;
    }

    public function setGroup($group = null)
    {
        if ($group !== null) {
            $this->command->setGroup($group);

            return $this;
        }
        $this->command->setGroup($this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id');

        return $this;
    }

    public function setOrder($order)
    {
        if (empty($order)) {
            return $this;
        }
        $this->command->setOrder($order);

        return $this;
    }

    public function setFrom($table_name)
    {
        $this->command->from($table_name);

        return $this;
    }

    public function clearJoinedList($table_name = null)
    {
        if ($this->_joined_list == false) {
            return $this;
        }

        if ($table_name == false) {
            $this->_joined_list = [];
        }

        foreach ($this->_joined_list as $i => $joined_list) {
            if ($joined_list == $table_name) {
                unset($this->_joined_list[$i]);

                return $this;
            }
        }

        return $this;
    }

    public function join($table_name, $conditions, $params = [], $direction = self::JOIN_LEFT, $add_table_prefix = true)
    {
        if ($add_table_prefix) {
            $table_name = '{{' . $table_name . '}}';
        }

        if (!empty($this->_joined_list) && in_array($table_name, $this->_joined_list)) {
            return $this;
        }
        $this->_joined_list[] = $table_name;

        switch ($direction) {
            case self::JOIN_LEFT    :
                $this->command->leftJoin($table_name, $conditions, $params);
                break;
            case self::JOIN_RIGHT   :
                $this->command->rightJoin($table_name, $conditions, $params);
                break;
            case self::JOIN_INNER   :
                $this->command->join($table_name, $conditions, $params);
                break;
        }

        return $this;
    }

    public function setUnion($query, $all = false)
    {
        if ($all == false) {
            $this->command->setUnion($query);
        } else {
            $text = $this->getText();
            $this->setText($text . ' UNION ALL ' . $query);
        }

        return $this;
    }

    public function clearWhere()
    {
        $this->where_conditions = [];

        return $this;
    }

    public function andWhere($conditions, $params = [])
    {
        $this->command->andWhere($conditions, $params);

        return $this;
    }

    public function orWhere($conditions, $params = [])
    {
        $this->command->orWhere($conditions, $params);

        return $this;
    }

    public function prepareWhereProperties(&$conditions, &$params)
    {
        if ($conditions == false) {
            return;
        }
        if ($params == false) {
            return;
        }

        // prepare "null"
        foreach ($params as $field_name => $value) {
            if ($value !== null) {
                continue;
            }

            if (is_array($conditions)) {
                $condition =& $conditions[1];
            } else {
                $condition =& $conditions;
            }

            if (strpos($condition, '!=' . $field_name) !== false || strpos($condition, '!= ' . $field_name) !== false) {
                $condition = str_replace(['!=' . $field_name, '!= ' . $field_name], ' is not NULL ', $condition);
                unset($params[$field_name]);
            }

            if (strpos($condition, '=' . $field_name) !== false || strpos($condition, '= ' . $field_name) !== false) {
                $condition = str_replace(['=' . $field_name, '= ' . $field_name], ' is NULL ', $condition);
                unset($params[$field_name]);
            }
        }
    }

    public function setWhere($conditions, $params = [])
    {
        $this->prepareWhereProperties($conditions, $params);

        if (empty($conditions)) {
            return $this;
        }
        if (empty($params)) {
            $this->command->where($conditions);
        } else {
            $this->command->where($conditions, $params);
        }

        return $this;
    }

    public function addHaving($conditions, $params)
    {
        if (empty($this->having_conditions)) {
            $this->having_conditions = $conditions;
        } else {
            $this->having_conditions = array_merge($this->having_conditions, $conditions);
        }

        if (is_array($params)) {
            $this->having_params = array_merge($this->having_params, $params);
        } else {
            $this->having_params[] = $params;
        }

        return $this;
    }

    public function setHaving($conditions, $params = [])
    {
        if (empty($params)) {
            $this->command->having($conditions);
        } else {
            $this->command->having($conditions, $params);
        }

        return $this;
    }

    public function setCollectingHaving()
    {
        if (!empty($this->having_conditions)) {
            $this->setHaving($this->having_conditions, $this->having_params);
        }

        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setParams($params)
    {
        $this->command->params = $params;

        return $this;
    }

    public function getParams()
    {
        return $this->command->params;
    }

    public function reset()
    {
        $this->command->reset();

        return $this;
    }

    public function findCount()
    {
        $this->command->setText('SELECT count(*) as x_count FROM (' . $this->getText() . ') AS data');
        $result = $this->findScalar();

        return $result;
    }

    public function findAll()
    {
        return $this->command->queryAll();
    }

    public function find()
    {
        return $this->command->query();
    }

    public function findRow()
    {
        return $this->command->queryRow();
    }

    public function findCol()
    {
        return $this->command->queryColumn();
    }

    public function findScalar()
    {
        return $this->command->queryScalar();
    }

    public function Delete($table, $conditions = '', $params = [])
    {
        $this->prepareWhereProperties($conditions, $params);

        return $this->command->delete($table, $conditions, $params);
    }

    public function Insert($table, array $columns)
    {
        return $this->command->insert($table, $columns);
    }

    public function InsertMulti($table, array $columns)
    {
        $builder = Yii::app()->db->schema->commandBuilder;
        $command = $builder->createMultipleInsertCommand($table, $columns);
        $command->execute();
    }

    public function Update($table, array $columns, $conditions = '', $params = [])
    {
        $this->prepareWhereProperties($conditions, $params);

        return $this->command->update($table, $columns, $conditions, $params);
    }

    public function execute()
    {
        $this->command->execute();
    }

    public function prepare()
    {
        $this->command->prepare();
    }

    /**
     * setUniqueIndex - сортировка в PV - добавляем к выборке уникальный индекс
     *
     * @param string|array $fields -  список полей, по которым будет составлен уникальный индекс
     */
    public function setUniqueIndex($field_names = null, $field_name_as = 'unique_index', $add_group = false)
    {
        if ($field_names === null) {
            $field_names = ['data.' . $this->extension_copy->prefix_name . '_id'];
        }

        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setSelect('data.*, md5(CONCAT_WS("",' . implode(',', $field_names) . ')) as ' . $field_name_as);
        $this->command->setFrom($query);
        $this->command->params = $params;

        if ($add_group) {
            $this->setGroup(implode(',', $field_names));
        }

        return $this;
    }

    /**
     * getJsonFieldsString - составляет из полей(ля) json строку с помощью concat и выводит в одном поле
     */
    public function getJsonFieldsString(array $field_names, $extension_copy = null)
    {
        if (!$field_names) {
            return '""';
        }

        if ($extension_copy === null) {
            $extension_copy = $this->extension_copy;
        }

        $columns = [];

        foreach ($field_names as $field_name) {
            $field_params = $extension_copy->getFieldSchemaParams($field_name);

            if (in_array($field_params['params']['type'], [
                \Fields::MFT_RELATE_PARTICIPANT,
            ])) {
                $columns[] = '\'"participant_ug_id":\',COALESCE(participant_ug_id, \'null\')';
                continue;
            }

            if (in_array($field_params['params']['type'], [
                \Fields::MFT_RELATE,
                \Fields::MFT_RELATE_DINAMIC,
                \Fields::MFT_RELATE_THIS,
            ])) {
                $field_name = DataModel::getInstance()
                    ->setExtensionCopy($this->extension_copy)
                    ->getRealFieldName($field_name);
            }

            // $field_name
            if (in_array($field_params['params']['type'], [
                \Fields::MFT_NUMERIC,
                \Fields::MFT_LOGICAL,
                \Fields::MFT_SELECT,
                \Fields::MFT_MODULE,
                \Fields::MFT_RELATE,
                \Fields::MFT_RELATE_DINAMIC,
                \Fields::MFT_RELATE_THIS,
            ])) {
                $columns[] = '\'"' . $field_name . '":\',COALESCE(' . $field_name . ', \'null\')';
            } else {
                $columns[] = '\'"' . $field_name . '":"\',COALESCE(' . $field_name . ', \'\'), \'"\'';
            }

            // MFT_DATETIME => TYPE_VIEW_BUTTON_DATE_ENDING
            if ($field_params['params']['type'] == \Fields::MFT_DATETIME) {
                if ($field_params['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                    $columns[] = '\'"' . $field_name . '_ad":"\',COALESCE(' . $field_name . '_ad, \'\'), \'"\'';
                }
            }

        }

        $json_str = 'concat(\'{\', ' . implode(',\',\',', $columns) . ',\'}\')';

        return $json_str;
    }

    /**
     * processViewSortingIsChanged - провверяет возвращает статус изменения списка в карточке
     *
     * @param $sorting_list_id
     * @return bool|unique_index
     */
    public function processViewSortingIsChanged($sorting_list_id)
    {
        $unique_index = (new \DataModel())
            ->setSelect('unique_index')
            ->setFrom('(' . $this->getText() . ') as DATA')
            ->setParams($this->command->params)
            ->findScalar();

        if (!$unique_index) {
            return true;
        }

        $unique_index_base = (new \ProcessViewSortingListModel())->findByPk($sorting_list_id);

        $b = !($unique_index_base && $unique_index == $unique_index_base['unique_index']);

        return ($b ? $unique_index : false);
    }

    /**
     *  сортировка списков в PV
     * $vars - дополнительные параметры: fields_group, fields_group_after_as, data_id_list, pci, pdi, sorting_list_id
     */
    public function setProcessViewPanelQuery($vars)
    {
        $users_id = ProcessViewSortingListModel::getInstance()->getUsersId();

        $unique_index = null;
        if ($vars['data_id_list'] && $vars['sorting_list_id']) {
            if ($b = $this->processViewSortingIsChanged($vars['sorting_list_id'])) {
                $vars['sorting_list_id'] = null;
                $unique_index = $b;
            }
        }

        // 1
        $select = '';

        if ($vars['only_id'] || $vars['only_id'] === null) {
            if ($vars['sorting_list_id'] == false) {
                $select = '
                {{process_view_sorting_list}}.sorting_list_id,
                {{process_view_sorting_list}}.unique_index,
                {{process_view_sorting_list}}.fields_data,
                {{process_view_sorting_list}}.mirror,
                {{process_view_sorting_list}}.group_data,
                {{process_view_sorting_list}}.sort,
                null as sort_str
            FROM
                {{process_view_sorting_list}}
            WHERE
                {{process_view_sorting_list}}.users_id ' . ($users_id ? '=' . $users_id : ' is NULL ') . ' AND
                {{process_view_sorting_list}}.copy_id = ' . $this->extension_copy->copy_id . ' AND ' .
                    (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pci = ' . $vars['pci'] . ' AND ' : '({{process_view_sorting_list}}.pci = "" OR {{process_view_sorting_list}}.pci is null) AND ') .
                    (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pdi = ' . $vars['pdi'] . ' AND ' : '({{process_view_sorting_list}}.pdi = "" OR {{process_view_sorting_list}}.pdi is null) AND') . '
                {{process_view_sorting_list}}.group_data = "' . $vars['group_data'] . '"
            UNION
            
            SELECT ';
            }

            $select .= '
                {{process_view_sorting_list}}.sorting_list_id,
                if({{process_view_sorting_list.unique_index}}, {{process_view_sorting_list.unique_index}}, data.unique_index) as unique_index,
                if({{process_view_sorting_list}}.fields_data, {{process_view_sorting_list}}.fields_data, ' . $this->getJsonFieldsString($vars['fields_group']) . ') as fields_data,
                if({{process_view_sorting_list.mirror}}, {{process_view_sorting_list.mirror}}, null) as mirror,
                if({{process_view_sorting_list.group_data}}, {{process_view_sorting_list.group_data}}, null) as group_data,
                if({{process_view_sorting_list}}.sort, {{process_view_sorting_list}}.sort, 999999999999) AS sort,
                if({{process_view_sorting_list}}.sort, {{process_view_sorting_list}}.sort, concat(' . (!empty($vars['fields_group_after_as']) ? implode(',', $vars['fields_group_after_as']) : '') . ')) AS sort_str
        ';

            $query = '(' . $this->getText() . ') as data
                    LEFT JOIN 
                        {{process_view_sorting_cards}} ON
                            {{process_view_sorting_cards}}.data_id = data.' . $this->extension_copy->getPkFieldName() . '
                    
                    LEFT JOIN
                        {{process_view_sorting_list}} ON ' .
                ($vars['sorting_list_id'] == false ? '{{process_view_sorting_list}}.sorting_list_id = {{process_view_sorting_cards}}.sorting_list_id AND ' : '') .
                ($vars['sorting_list_id'] ? '{{process_view_sorting_list}}.sorting_list_id = ' . addslashes($vars['sorting_list_id']) . ' AND ' : '') . '
                            {{process_view_sorting_list}}.users_id ' . ($users_id ? '=' . $users_id : ' is NULL ') . ' AND
                            {{process_view_sorting_list}}.copy_id = ' . $this->extension_copy->copy_id . ' AND ' .
                (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pci = ' . $vars['pci'] . ' AND ' : '({{process_view_sorting_list}}.pci = "" OR {{process_view_sorting_list}}.pci is null) AND ') .
                (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pdi = ' . $vars['pdi'] . ' AND ' : '({{process_view_sorting_list}}.pdi = "" OR {{process_view_sorting_list}}.pdi is null) AND ') . '
                            {{process_view_sorting_list}}.group_data = "' . $vars['group_data'] . '" AND
                            {{process_view_sorting_list}}.unique_index = data.unique_index
                    ';

            $params = $this->command->params;
            $this->command->reset();
            $this->command->setSelect($select);
            $this->command->setFrom($query);
            $this->command->params = $params;

            // 2
            $this->setSelectNew();
            $this->setGroup('unique_index');
        }

        if ($vars['sorting_list_id'] == false) {
            $query = '';

            if ($vars['only_id'] || $vars['only_id'] === null) {
                $query .= '(' . $this->getText() . ') AS data UNION ';
            }

            // 3
            $query .= '
                (SELECT
                    {{process_view_sorting_list}}.sorting_list_id,
                    {{process_view_sorting_list}}.unique_index,
                    {{process_view_sorting_list}}.fields_data,
                    {{process_view_sorting_list}}.mirror,
                    {{process_view_sorting_list}}.group_data,
                    {{process_view_sorting_list}}.sort,
                    null as sort_str
                FROM
                    {{process_view_sorting_list}}
                WHERE
                    {{process_view_sorting_list}}.users_id ' . ($users_id ? '=' . $users_id : ' is NULL ') . ' AND
                    {{process_view_sorting_list}}.copy_id = ' . $this->extension_copy->copy_id . ' AND ' .
                (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pci = ' . $vars['pci'] . ' AND ' : '({{process_view_sorting_list}}.pci = "" OR {{process_view_sorting_list}}.pci is null) AND ') .
                (!empty($vars['pci']) && !empty($vars['pdi']) ? '{{process_view_sorting_list}}.pdi = ' . $vars['pdi'] . ' AND ' : '({{process_view_sorting_list}}.pdi = "" OR {{process_view_sorting_list}}.pdi is null) AND ') . '
                    {{process_view_sorting_list}}.group_data = "' . $vars['group_data'] . '"
                ) 
            ';

            if ($vars['only_id'] === false) {
                $query .= 'AS data2';
            }

            $params = $this->command->params;
            $this->command->reset();
            $this->command->setFrom($query);
            $this->command->params = $params;
        }

        // 4
        $this->setSelectNew();
        $this->setGroup('sorting_list_id, unique_index');
        $this->setOrder('sort, sort_str');

        // если карточка сменила список или создалась карточка, что может попасть в несколько списков
        if ($unique_index) {
            $this->andWhere('unique_index="' . $unique_index . '" AND mirror is NULL');
        }

        return $this;
    }

    /**
     * сортировка карточек в PV
     *
     * @param $vars - field_names_after_as, panel_data, mirror
     */
    public function setProcessViewCardQuery($vars)
    {
        // 1
        $select = '
                data.*,
                {{process_view_sorting_cards}}.sorting_cards_id,
                {{process_view_sorting_cards}}.data_id AS sorting_cards_data_id,
                {{process_view_sorting_cards}}.sorting_list_id as cards_sorting_sorting_list_id,
                if({{process_view_sorting_cards}}.sort, {{process_view_sorting_cards}}.sort, 999999999999) AS cards_sorting_sort,
                concat("a_"' . (!empty($vars['fields_group_after_as']) ? ',' . implode(',', $vars['fields_group_after_as']) : '') . ') AS cards_sorting_sort_str
            ';

        $query = '(' . $this->getText() . ') as data
                    LEFT JOIN
                        {{process_view_sorting_cards}} ON {{process_view_sorting_cards}}.sorting_list_id = ' . $vars['panel_data']['sorting_list_id'] . ' AND 
                                                          {{process_view_sorting_cards}}.data_id = ' . $this->extension_copy->getPkFieldName() . '
                                                          ';

        $condition = '';

        // если список дублирующий - отбираем краточки, что уже есть в таблице сортировок или новые , что только что создались (по параметру $vars['data_id_list'])
        if ($vars['panel_data']['mirror']) {
            $condition .= ' {{process_view_sorting_cards}}.sorting_list_id is not NULL ';
            if ($vars['data_id_list']) {
                $condition .= ' OR ' . $this->extension_copy->getPkFieldName() . ' in(' . addslashes(implode(',', $vars['data_id_list'])) . ')';
            }

            // если список гравный - отбираем краточки, что уже есть в таблице сортировок, новые, перенесенные в гругой список и по параметру $vars['data_id_list'])
        } else {
            $condition .= ' {{process_view_sorting_cards}}.sorting_list_id is not NULL ';
            if ($vars['data_id_list']) {
                $condition .= ' OR ' . $this->extension_copy->getPkFieldName() . ' in(' . addslashes(implode(',', $vars['data_id_list'])) . ')';
            }
            if ($vars['data_id_list']) {
                $condition .= ' OR ' . $this->extension_copy->getPkFieldName() . ' in(' . addslashes(implode(',', $vars['data_id_list'])) . ')';
            } else {
                $condition .= ' OR
                              not exists
                              (
                                  SELECT t0.*, unique_index as unique_index1
                                  FROM {{process_view_sorting_cards}} t0
                                  WHERE
                                    t0.sorting_list_id is not NULL AND
                                    t0.sorting_list_id != ' . $vars['panel_data']['sorting_list_id'] . ' AND
                                    t0.data_id = ' . $this->extension_copy->getPkFieldName() . '
                                  HAVING
                                    exists(
                                        SELECT t1.*
                                        FROM {{process_view_sorting_list}} t1
                                        WHERE
                                          sorting_list_id = t0.sorting_list_id AND
                                          users_id = ' . $vars['users_id'] . ' AND
                                          copy_id = ' . $this->extension_copy->copy_id . ' AND ' .
                    ($vars['pci'] ? ' pci = ' . addslashes($vars['pci']) : ' pci is null ') . ' AND ' .
                    ($vars['pdi'] ? ' pdi = ' . addslashes($vars['pdi']) : ' pdi is null ') . ' AND
                                          group_data = "' . $vars['group_data'] . '" AND 
                                          t1.unique_index = unique_index1
                                    )
                              )                
                ';
            }
        }

        if ($condition) {
            $query .= ' WHERE ' . $condition;
        }

        $params = $this->command->params;
        $this->command->reset();
        $this->command->setSelect($select);
        $this->command->setFrom($query);
        $this->command->params = $params;
        $this->setOrder('cards_sorting_sort, cards_sorting_sort_str, ' . $this->extension_copy->getPkFieldName());

        return $this;
    }



    /**
     *  сортировка в PV
     */
    /*
    public function setProcessViewOrderByUniqueIndex($fields = null, $compare_to_data_id = false, $type, $pci, $pdi){
        $base_fields = $fields;
        $users_id = ProcessViewSortingListModel::getInstance()->getUsersId();

        if($fields){
            array_unshift($fields, '{{process_view_sorting}}.sort');
        } else {
            $fields = array('{{process_view_sorting}}.sort');
        }

        $query = '(' . $this->getText() .') as data
                    LEFT JOIN `{{process_view_sorting}}` ON `{{process_view_sorting}}`.unique_index'.($compare_to_data_id == true ? '_id = data.' . $this->extension_copy->getPkFieldName() : ' = data.unique_index') . ' AND
                                                        `{{process_view_sorting}}`.user_id ' . ($users_id ? '='.$users_id : ' is NULL ') . ' AND
                                                        `{{process_view_sorting}}`.copy_id = ' . $this->extension_copy->copy_id . ' AND
                                                        ' .
            (!empty($pci) && !empty($pdi) ? '`{{process_view_sorting}}`.pci = ' . $pci . ' AND ' :  '(`{{process_view_sorting}}`.pci = "" OR `{{process_view_sorting}}`.pci is null)  AND ') .
            (!empty($pci) && !empty($pdi) ? '`{{process_view_sorting}}`.pdi = ' . $pdi . ' AND ' :  '(`{{process_view_sorting}}`.pdi = "" OR `{{process_view_sorting}}`.pdi is null)  AND ') .
            '
                                                        `{{process_view_sorting}}`.type = ' . $type.'
                    ' . ($compare_to_data_id == false ? ' GROUP BY ' . implode(', ', $base_fields) : '') . '
                    ORDER BY '. implode(', ', $fields) .' ASC';

        $select = 'data.*, {{process_view_sorting}}.sort, {{process_view_sorting}}.sorting_id as unique_index_sorting_id';
        if($compare_to_data_id){
            $select .= ',({{process_view_sorting}}.unique_index = data.unique_index) as unique_index_compare';
        }

        $params = $this->command->params;
        $this->command->reset();
        $this->command->setSelect($select);
        $this->command->setFrom($query);
        $this->command->params = $params;

        return $this;
    }
    */

    /**
     * возвращает массив полей для сортировки. Указывается порядок сортировки
     * string $sorting_to_pk - сотировать по первичному полю
     *
     * @return string
     */
    public function getOrderFromSortingParams($get_compare = 'both', $sorting_to_pk = null)
    {
        $sorting = Sorting::$params;
        $order_by = $this->getComparedWithFields($sorting, $get_compare);

        if (!empty($order_by)) {
            $order_by[] = $this->extension_copy->prefix_name . '_id asc';

            return implode(',', $order_by);
        }

        if ($sorting_to_pk) {
            return $this->extension_copy->prefix_name . '_id ' . $sorting_to_pk;
        }
    }

    public function setGroupFromSortingParams($group)
    {
        if (!empty($group)) {

            if (!empty($this->_participant_group_field)) {
                foreach ($group as &$group_field) {
                    if (isset($this->_participant_group_field[$group_field]) && $this->_participant_group_field[$group_field]) {
                        $group_field = $this->_participant_group_field[$group_field];
                    }
                }

            }

            $query = '(' . $this->getText() . ') as data';
            $params = $this->command->params;
            $this->command->reset();
            $this->command->setFrom($query);
            $this->command->params = $params;
            $this->command->setGroup(implode(',', $group));
        }

        return $this;
    }

    /**
     * возвращает массив данних полей для группировки или сортировки
     *
     * @return array
     */
    public function getComparedWithFields($parent_data = null, $get_compare = 'both')
    {
        if (empty($parent_data)) {
            return [];
        }

        $schema_parse = $this->extension_copy->getSchemaParse([], [], [], false);
        $result = [];
        foreach ($schema_parse['elements'] as $element) {
            $direction = '';
            if (isset($element['field'])) {
                if ($get_compare == 'both') {
                    $field_exists = array_key_exists($element['field']['params']['name'], $parent_data);
                    if (empty($field_exists)) {
                        continue;
                    }
                    $direction = ' ' . $parent_data[$element['field']['params']['name']];
                } elseif ($get_compare == 'value') {
                    $field_exists = in_array($element['field']['params']['name'], $parent_data);
                    if (empty($field_exists)) {
                        continue;
                    }
                }

                if ($element['field']['params']['type'] == 'select') {
                    $result[] = $this->extension_copy->prefix_name . '_' . $element['field']['params']['name'] . '_title' . $direction;
                } elseif ($element['field']['params']['type'] == 'file' || $element['field']['params']['type'] == 'file_image') {
                    $result[] = $this->extension_copy->prefix_name . '_' . $element['field']['params']['name'] . '_file_title' . $direction;
                } elseif ($element['field']['params']['type'] == 'relate') {
                    $module_table = ModuleTablesModel::model()->find(
                        'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_one"',
                        [':copy_id' => $this->extension_copy->copy_id, ':relate_copy_id' => $element['field']['params']['relate_module_copy_id']]);
                    $result[] = \ExtensionCopyModel::model()->findByPk($element['field']['params']['relate_module_copy_id'])->getTableName(null, false, false) . '_' . $module_table->relate_field_name . $direction;
                } elseif ($element['field']['params']['type'] == 'relate_participant' && $element['field']['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                    $schema_responsible = $this->extension_copy->getResponsibleField();
                    $result[] = $schema_responsible['params']['name'] . $direction;
                } elseif ($element['field']['params']['type'] == 'relate_participant' && $element['field']['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
                    $schema_responsible = $this->extension_copy->getParticipantField();
                    $result[] = $schema_responsible['params']['name'] . $direction;
                } elseif ($element['field']['params']['type'] == 'display_block') {
                    $result[] = $this->extension_copy->prefix_name . '_' . $element['field']['params']['name'] . '_title' . $direction;
                } else {
                    $result[] = $element['field']['params']['name'] . $direction;
                }

            } elseif (isset($element['sub_module'])) {
            }
        }

        return $result;
    }

    /**
     * возвращает название поля в БД после "AS"
     *
     * @param string $field - название поля в схеме
     * @param bool $return_real = true -  название поля после "AS", false - в схеме
     * @return array()
     */
    public function getRealFieldName($field, $return_real = true)
    {
        $params = $this->extension_copy->getFieldSchemaParams($field);
        $field_name = $field;

        if (empty($params['params'])) {
            return $field_name;
        }

        if ($params['params']['type'] == 'select') {
            $field_name = $this->extension_copy->prefix_name . '_' . $params['params']['name'] . '_title';
        } elseif ($params['params']['type'] == 'logical') {
            if ($return_real == false) {
                $field_name = $this->extension_copy->getTableName() . '_' . $params['params']['name'] . '_title';
            }
        } elseif ($params['params']['type'] == 'file' || $params['params']['type'] == 'file_image') {
            $field_name = $this->extension_copy->prefix_name . '_' . $params['params']['name'] . '_file_title';
        } elseif ($params['params']['type'] == 'relate') {
            $module_table = ModuleTablesModel::model()->find(
                'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_one"',
                [':copy_id' => $this->extension_copy->copy_id, ':relate_copy_id' => $params['params']['relate_module_copy_id']]);
            $field_name = \ExtensionCopyModel::model()->findByPk($params['params']['relate_module_copy_id'])->getTableName(null, false, false) . '_' . $module_table->relate_field_name;
        } elseif ($params['params']['type'] == 'relate_participant' && $params['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
            $schema_responsible = $this->extension_copy->getResponsibleField();
            $field_name = $schema_responsible['params']['name'];
        } elseif ($params['params']['type'] == 'relate_participant' && $params['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
            $schema_responsible = $this->extension_copy->getParticipantField();
            $field_name = $schema_responsible['params']['name'];
        } elseif ($params['params']['type'] == 'display_block') {
            $field_name = $this->extension_copy->prefix_name . '_' . $params['params']['name'] . '_title';
        }

        return $field_name;
    }

    /**
     * формирует и возвращает часть запроса для поиска
     */
    public function getQueryWhereForSearch($search_text, array $field_name_list = null, $without_params = false)
    {
        $result = [];
        if ($search_text === null) {
            return $result;
        }

        $search_text = str_replace('_', '\_', $search_text);

        $schema_parse = $this->extension_copy->getSchemaParse();
        $where_condition = [];
        $where_params = [];

        // function
        $addCondition = function ($real_field_name, $field_name, $search_text, $operation = '=') use (&$where_condition, &$where_params, $without_params) {
            if ($without_params == false) {
                $where_condition[] = $real_field_name . ' ' . $operation . ' :find_' . $field_name;
                $where_params[':find_' . $field_name] = $search_text;
            } else {
                $where_condition[] = $real_field_name . ' ' . $operation . ' "' . addslashes($search_text) . '"';
            }
        };

        if ($this->_search_ws_sdm == false && $this->_search_ws_sm == false) {
            foreach ($schema_parse['elements'] as $params) {
                if (!isset($params['field'])) {
                    continue;
                }

                if ($params['field']['params']['type'] == 'datetime' && Helper::checkCharForDate($search_text) == false) {
                    continue;
                }
                if ($params['field']['params']['type'] == 'activity') {
                    continue;
                }
                if ($params['field']['params']['type'] == 'relate') {
                    continue;
                }
                if ($params['field']['params']['type'] == 'relate_dinamic') {
                    continue;
                }
                if ($params['field']['params']['type'] == 'module') {
                    continue;
                }
                if ($field_name_list !== null && !in_array($params['field']['params']['name'], $field_name_list)) {
                    continue;
                }

                $field_name = $this->getRealFieldName($params['field']['params']['name'], false);

                if ($params['field']['params']['type'] == 'numeric') {
                    if (!is_numeric($search_text)) {
                        continue;
                    }
                    if ($search_text == '0') {
                        $search_text = '0.00000';
                    }

                    $addCondition($field_name, $params['field']['params']['name'], $search_text);
                } else {
                    if ($params['field']['params']['type'] == 'datetime') {
                        $addCondition($field_name, $params['field']['params']['name'], '%' . \DateTimeOperations::getDateToSqlSearch($search_text) . '%', 'like');
                    } else {
                        if ($params['field']['params']['type'] == 'datetime_activity') {
                            $addCondition($field_name, $params['field']['params']['name'], '%' . \DateTimeOperations::getDateToSqlSearch($search_text) . '%', 'COLLATE utf8_general_ci like');
                        } else {
                            $addCondition($field_name, $params['field']['params']['name'], '%' . $search_text . '%', 'like');
                        }
                    }
                }
            }
        }

        if (!empty($this->_concat_ws_field_name)) {
            foreach ($this->_concat_ws_field_name as $real_field_name => $alias_field_name) {
                if ($field_name_list !== null && !in_array($real_field_name, $field_name_list)) {
                    continue;
                }

                $addCondition($alias_field_name, $alias_field_name, '%' . $search_text . '%', 'like');
            }
        }

        if (!empty($where_condition)) {
            array_unshift($where_condition, 'OR');

            return ['condition' => $where_condition, 'params' => $where_params];
        }

        return $result;
    }

    /**
     *   Установка словия для поиска с родительским модулем
     */
    public function setParentModule($pdi)
    {
        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        $this->andWhere(['AND', $this->extension_copy->prefix_name . '_id in (' . $pdi . ')']);

        return $this;
    }

    /**
     *   Установка словия для поиска с родительским модулем типа "Участники"
     */
    public function setParticipantParentModule($pci, $pdi)
    {
        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;
        $this->setWhere(['AND', 'copy_id=:copy_id AND data_id=:data_id'], [':copy_id' => $pci, ':data_id' => $pdi]);

        return $this;
    }

    /**
     *   Установка словия для поиска по всех данных
     */
    public function setSearch($condition_params)
    {
        if (empty($condition_params)) {
            return $this;
        }

        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        if (is_array($condition_params)) {
            $this->setWhere($condition_params['condition'], $condition_params['params']);
        } else {
            $this->setWhere($condition_params);
        }
    }

    public function setSelectOther()
    {
        // type "display_block"
        if (!$this->extension_copy->isShowAllBlocks()) {
            $blocks = $this->extension_copy->getSchemaBlocksData();

            $q = [];
            if (!empty($blocks)) {
                foreach ($blocks as $block) {
                    $q[] = 'WHEN "' . $block['unique_index'] . '" THEN "' . $block['title'] . '"';
                }
            }

            if ($q) {
                $blocks = $this->extension_copy->getFieldSchemaParamsByType('display_block');
                $q = '(CASE ' . $this->extension_copy->getTableName() . '.' . $blocks['params']['name'] . ' ' . implode(' ', $q) . ' END) AS ' . $this->extension_copy->prefix_name . '_' . $blocks['params']['name'] . '_title';
                $this->addSelect($q);
            }
        }

        //type "calculated"
        $calc_fields = $this->extension_copy->getFieldSchemaParamsByType(\Fields::MFT_CALCULATED, null, false);
        if (!empty($calc_fields)) {
            foreach ($calc_fields as $calc_field) {
                $select_fields = \CalculatedFields::getInstance()
                    ->setExtensionCopy($this->extension_copy)
                    ->setFieldName($calc_field['params']['name'])
                    ->prepareFormula()
                    ->getSelectFields();
                if ($select_fields) {
                    $this->addSelect($select_fields);
                }
            }
        }
    }

    /**
     *  присоединение всех связаных таблиц, прописаных в module_tables
     */
    public function setFromModuleTables($only_parent = false)
    {
        $module_tables_list = ModuleTablesModel::model()->findAll(
            [
                'condition' => 'copy_id=:copy_id',
                'params'    => [':copy_id' => $this->extension_copy->copy_id]
            ]
        );

        foreach ($module_tables_list as $mt_model) {
            //parent
            if ($mt_model->type == 'parent') {
                $this->addSelect('{{' . $mt_model->table_name . '}}.*');
                $this->command->from('{{' . $mt_model->table_name . '}}');

                //relate_select
            } else {
                if ($mt_model->type == 'relate_select') {
                    $this->join($mt_model->table_name,
                        $this->extension_copy->getTableName() . '.' . $mt_model->parent_field_name .
                        '=' .
                        '{{' . $mt_model->table_name . '}}.' . $mt_model->relate_field_name,
                        []
                    );

                    $this->addSelect('{{' . $mt_model->table_name . '}}.' . $mt_model->parent_field_name . '_title as ' . $this->extension_copy->prefix_name . '_' . $mt_model->parent_field_name . '_title');
                    $this->addSelect('{{' . $mt_model->table_name . '}}.' . $mt_model->parent_field_name . '_color as ' . $this->extension_copy->prefix_name . '_' . $mt_model->parent_field_name . '_color');

                    //relate_module_one
                } elseif ($only_parent == false) {
                    if ($mt_model->type == 'relate_module_one') {
                        switch ($mt_model->relate_type) {
                            case 'belongs_to':
                                $is_set_search = $this->_is_set_search;
                                if ($this->_search_ws_sdm == true || $this->_search_ws_sm == true) {
                                    $is_set_search = false;
                                }

                                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($mt_model->relate_copy_id);
                                $relate_one_result = DataQueryRelateOneModel::getInstance()
                                    ->setExtensionCopy($this->extension_copy)
                                    ->setRelateExtensionCopy($relate_extension_copy)
                                    ->setIsSetSearch($is_set_search)
                                    ->setModuleTablesModel($mt_model)
                                    ->prepare()
                                    ->getResult();

                                if (!empty($relate_one_result['select'])) {
                                    $this->addSelect(implode(', ', $relate_one_result['select']));
                                }
                                if (!empty($relate_one_result['select_concat'])) {
                                    $this->addSelect('concat_ws(" ",' . implode(', ', $relate_one_result['select_concat']) . ') as ' . $relate_extension_copy->getTableName(null, false) . '_' . $relate_extension_copy->prefix_name . '_value');

                                    $relate_field_name = SchemaOperation::getInstance()->getElementsRelateParams($this->extension_copy->getSchema(), false, $mt_model->relate_copy_id)['name'];
                                    $this->_concat_ws_field_name[$relate_field_name] = $relate_extension_copy->getTableName(null, false) . '_' . $relate_extension_copy->prefix_name . '_value';
                                }
                                if (!empty($relate_one_result['join'])) {
                                    foreach ($relate_one_result['join'] as $join) {
                                        $this->join($join['table'], $join['on'], [], self::JOIN_LEFT, false);
                                    }
                                }
                        }
                    } else //relate_module_many
                    {
                        if ($mt_model->type == 'relate_module_many') {
                            /*
                            switch($mt_model->relate_type){
                                case 'many_many':
                                    $this->join($mt_model->table_name,
                                                  $this->extension_copy->getTableName() . '.' . $mt_model->parent_field_name .
                                                  '=' .
                                                  '{{' . $mt_model->table_name . '}}.' . $mt_model->parent_field_name
                                              );
                                break;
                            }
                            $this->addSelect('{{' . $mt_model->table_name . '}}.' . $mt_model->relate_field_name  . ' as ' . $mt_model->table_name . '_' . $mt_model->relate_field_name);
                            */
                        }
                    }
                }
            }
        }

        $this->setSelectOther();

        return $this;
    }

    /**
     * данные поля для групировки
     *
     * @param $extension_copy
     * @return $this
     */
    public function setConcatGroupFieldSDM($extension_copy, $revert_to_cm = false)
    {
        if ($this->_is_set_search == false) {
            return $this;
        }

        if ($revert_to_cm == true) {
            $this->_concat_ws_field_name = ['module_title' => 'module_title'];

            return $this;
        }

        $relate_extension_copy = $this->extension_copy;

        $relate_one_result = DataQueryRelateOneModel::getInstance()
            ->setParentExtensionCopy($relate_extension_copy)
            ->setExtensionCopy($extension_copy)
            ->setRelateExtensionCopy($relate_extension_copy)
            ->setIsSetSearch($this->_is_set_search)
            ->setModuleTablesModel(DataQueryRelateOneModel::getInstance()->getModuleTablesModel($extension_copy->copy_id, $relate_extension_copy->copy_id))
            ->prepare()
            ->getResult();

        $this->_concat_ws_field_name = [];
        if (!empty($relate_one_result['select_concat'])) {
            $this->addSelect('concat_ws(" ",' . implode(', ', $relate_one_result['select_concat']) . ') as ' . $extension_copy->getTableName() . '_concat_group_field');
            $fn = $extension_copy->getTableName() . '_concat_group_field';
            $this->_concat_ws_field_name = [$fn => $fn];
        }

        if (!empty($relate_one_result['join'])) {
            foreach ($relate_one_result['join'] as $join) {
                $this->join($join['table'], $join['on'], [], self::JOIN_LEFT, false);
            }
        }

        return $this;
    }

    /**
     * данные поля для групироки
     *
     * @param $extension_copy
     * @return $this
     */
    public function setConcatGroupFieldSM($extension_copy)
    {
        $this->_concat_ws_field_name = [];

        $field_params = $extension_copy->getFirstFieldParamsForRelate();

        $this->addSelect('(' . $extension_copy->getTableName() . '.' . $field_params['params']['name'] . ') as ' . $extension_copy->getTableName() . '_concat_group_field');
        $fn = $extension_copy->getTableName() . '_concat_group_field';
        $this->_concat_ws_field_name = [$fn => $fn];

        return $this;
    }

    /**
     *  присоеденяем к запросу звязаные таблицы: файлы, логический тип
     */
    public function setFromFieldTypes()
    {
        $schema_parse = $this->extension_copy->getSchemaParse();

        foreach ($schema_parse['elements'] as $element) {
            if (isset($element['field'])) {
                // добавляем связь на таблицу  uploads
                if ($element['field']['params']['type'] == 'file' || $element['field']['params']['type'] == 'file_image') {
                    $this->addSelect('(SELECT {{uploads}}.file_title 
                                       FROM {{uploads}}
                                       WHERE {{uploads}}.relate_key = ' . $this->extension_copy->getTableName() . '.' . $element['field']['params']['name'] . ' LIMIT 1)
                                       as ' . $this->extension_copy->prefix_name . '_' . $element['field']['params']['name'] . '_file_title');
                } else {
                    if ($element['field']['params']['type'] == 'logical') {
                        $logical = Fields::getInstance()->getLogicalData();

                        $this->addSelect('if(' . $this->extension_copy->getTableName() . '.' . $element['field']['params']['name'] . ' = "0", "' . $logical[0] . '",
                                      if(' . $this->extension_copy->getTableName() . '.' . $element['field']['params']['name'] . ',"' . $logical[1] . '",
                                      ' . $this->extension_copy->getTableName() . '.' . $element['field']['params']['name'] . ')) AS ' . $this->extension_copy->getTableName() . '_' . $element['field']['params']['name'] . '_title');
                    }
                }
            } elseif (isset($element['sub_module'])) {
            }
        }

        return $this;
    }

    /**
     * присоеденяем таблицу Учасников для Ответсвенного
     */
    public function setFromResponsible($join_all_participant = false)
    {
        $schema_responsible = $this->extension_copy->getResponsibleField();

        $this->addSelect('participant1.participant_id as participant_participant_id,
                          participant1.ug_id as participant_ug_id,
                          participant1.ug_type as participant_ug_type,
                          participant1.responsible as participant_responsible,
                          if(participant1.ug_type = "user", TRIM(BOTH FROM CONCAT(COALESCE(`{{users}}`.sur_name, "")," ", COALESCE (`{{users}}`.first_name, "")," ",COALESCE (`{{users}}`.father_name, ""))), "") as ' . $schema_responsible['params']['name']);

        $this->join('{{participant}} participant1', 'participant1.copy_id = ' . $this->extension_copy->copy_id . ' AND participant1.data_id=' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id AND participant1.responsible = "1"', [], self::JOIN_LEFT, false);
        if ($join_all_participant) {
            $this->join('{{participant}} participant2', 'participant2.copy_id = ' . $this->extension_copy->copy_id . ' AND participant2.data_id=' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id', [], self::JOIN_LEFT, false);
        }
        $this->join('users', 'participant1.ug_id = {{users}}.users_id');

        $this->_participant_group_field = [
            $schema_responsible['params']['name'] => 'participant1.ug_id'
        ];

        // связь с Процессами
        $this->addSelect('{{process_operations}}.process_id AS {{process_operations}}_process_id');

        if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            $this->join('process_operations', '{{process_operations}}.process_id = {{process}}.process_id AND {{process_operations}}.element_name in (' . implode(',', ['"' . \Process\models\OperationsModel::ELEMENT_TASK . '"', '"' . \Process\models\OperationsModel::ELEMENT_AGREETMENT . '"']) . ')');
        } else {
            $this->join('process_operations',
                '{{process_operations}}.copy_id = ' . $this->extension_copy->copy_id . ' AND
                 {{process_operations}}.card_id = ' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id'
            );
        }

        return $this;
    }

    /**
     * присоеденяем таблицу Учасников
     */
    public function setFromParticipant($join_all_participant = false)
    {
        $schema_responsible = $this->extension_copy->getParticipantField();

        $this->addSelect('participant1.participant_id as participant_participant_id,
                          participant1.ug_id as participant_ug_id,
                          participant1.ug_type as participant_ug_type,
                          participant1.responsible as participant_responsible,
                          if(participant1.ug_type = "user", TRIM(BOTH FROM CONCAT(COALESCE(`{{users}}`.sur_name, "")," ", COALESCE (`{{users}}`.first_name, "")," ",COALESCE (`{{users}}`.father_name, ""))), "") as ' . $schema_responsible['params']['name']);

        $this->join('{{participant}} participant1', 'participant1.copy_id = ' . $this->extension_copy->copy_id . ' AND participant1.data_id=' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id AND participant1.responsible = "1"', [], self::JOIN_LEFT, false);
        if ($join_all_participant) {
            $this->join('{{participant}} participant2', 'participant2.copy_id = ' . $this->extension_copy->copy_id . ' AND participant2.data_id=' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id', [], self::JOIN_LEFT, false);
        }
        $this->join('users', 'participant1.ug_id = {{users}}.users_id');

        $this->_participant_group_field = [
            $schema_responsible['params']['name'] => 'participant1.ug_id'
        ];

        // связь с Процессами
        $this->addSelect('{{process_operations}}.process_id AS {{process_operations}}_process_id');

        if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            $this->join('process_operations', '{{process_operations}}.process_id = {{process}}.process_id AND {{process_operations}}.element_name in (' . implode(',', ['"' . \Process\models\OperationsModel::ELEMENT_TASK . '"', '"' . \Process\models\OperationsModel::ELEMENT_AGREETMENT . '"']) . ')');
        } else {
            $this->join('process_operations',
                '{{process_operations}}.copy_id = ' . $this->extension_copy->copy_id . ' AND
                 {{process_operations}}.card_id = ' . $this->extension_copy->getTableName() . '.' . $this->extension_copy->prefix_name . '_id'
            );
        }

        return $this;
    }





    /**
     *   Отбор только тех данных, где пользователь есть владельцем (создал запись), или его подписали как участника
     */
    /*
    public function setOtherPartisipantAllowed1(){
        $query = '(' . $this->getText() .') as data';
        $params = $this->command->params; 
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $this->setWhere(array(
                            'AND',
                            '(
                                 (
                                 {{process_operations}}_process_id is NULL
                                    AND
                                    (
                                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = '.$this->extension_copy->copy_id.' AND data_id = data.'. $this->extension_copy->prefix_name . '_id AND ug_id = '.WebUser::getUserId().' AND ug_type = "user") 
                                        OR  
                                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = '.$this->extension_copy->copy_id.' AND data_id = data.'. $this->extension_copy->prefix_name . '_id AND ug_id ' . (count($roles)==1 ? ' = '.$roles[0] : 'in ('.implode(',', $roles).')') . ' AND ug_type = "group")
                                    )
                                 )
                                 OR
                                 (
                                    {{process_operations}}_process_id is NOT NULL
                                    AND
                                    (
                                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = '.\ExtensionCopyModel::MODULE_PROCESS.' AND data_id = {{process_operations}}_process_id AND ug_id = '.WebUser::getUserId().' AND ug_type = "user")
                                        OR
                                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = '.\ExtensionCopyModel::MODULE_PROCESS.' AND data_id = {{process_operations}}_process_id AND ug_id ' . (count($roles)==1 ? ' = '.$roles[0] : 'in ('.implode(',', $roles).')') . ' AND ug_type = "group")
                                    )
                                 )
                            )'
                           )); 
    }
    */

    /**
     * Добавляет условие отбора данных "только участники по связи через модуль через поле Название
     *
     * @return string
     */
    public function setOtherPartisipantAllowed($copy_id, $all_control = true)
    {

        // parent
        $parent_copy_id = \ModuleTablesModel::getParentModuleCopyId($copy_id);
        if (empty($parent_copy_id)) {
            $this->setProcessPartisipantAllowed($all_control);

            return $this;
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id);

        // parent data_if_participant == false
        if ($parent_extension_copy->dataIfParticipant() == false || ($parent_extension_copy->isParticipant() == false && $parent_extension_copy->isResponsible() == false)) {
            $this->setProcessPartisipantAllowed($all_control);

            return $this;
        }

        $relate_model = \ModuleTablesModel::model()->find([
                'condition' => 'copy_id =:copy_id AND relate_copy_id =:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id'        => $parent_copy_id,
                    ':relate_copy_id' => $copy_id,
                ]
            ]
        );

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $query = 'SELECT tt3.* FROM ' . $parent_extension_copy->getTableName() . ' tt1
                    LEFT JOIN {{' . $relate_model->table_name . '}} tt2 ON tt1.' . $parent_extension_copy->prefix_name . '_id = tt2.' . $relate_model['parent_field_name'] . '
                    RIGHT JOIN (' . $this->getText() . ') tt3 ON  tt2.' . $relate_model['relate_field_name'] . ' = tt3.' . $extension_copy->prefix_name . '_id';

        $condition = '
                    WHERE
                        (
                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $this->extension_copy->copy_id . ' AND data_id = tt3.' . $this->extension_copy->prefix_name . '_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user") 
                            OR  
                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $this->extension_copy->copy_id . ' AND data_id = tt3.' . $this->extension_copy->prefix_name . '_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',', $roles) . ')') . ' AND ug_type = "group")
                        )
                        AND
                        (
                            (
                                {{process_operations}}_process_id is NULL
                                AND
                                (
                                    tt1.' . $parent_extension_copy->prefix_name . '_id is NOT null
                                    AND (
                                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $parent_extension_copy->copy_id . ' AND data_id = tt1.' . $parent_extension_copy->prefix_name . '_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user")
                                            OR
                                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $parent_extension_copy->copy_id . ' AND data_id = tt1.' . $parent_extension_copy->prefix_name . '_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',',
                    $roles) . ')') . ' AND ug_type = "group")
                                    )
    
                        ';

        if ($copy_id == \ExtensionCopyModel::MODULE_TASKS) {
            $condition .= ' OR (tt1.' . $parent_extension_copy->prefix_name . '_id is null AND tt3.is_bpm_operation is NULL)'; // просто задача
            $condition .= ' OR tt3.is_bpm_operation = "0"'; // задача из процесса
        } else {
            $condition .= ' OR tt1.' . $parent_extension_copy->prefix_name . '_id is null';
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $condition .= '         )
                            ) 
                            OR (
                                {{process_operations}}_process_id is NOT NULL
                                AND
                                (
                                    exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user")
                                    OR
                                    exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',', $roles) . ')') . ' AND ug_type = "group")
                                )
                            )
                        )
        ';

        $query = '(' . $query . $condition . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        return $this;
    }

    /**
     *   Отбор всех данных и только тех данных, где пользователь есть владельцем  перез Процессы
     */
    private function setProcessPartisipantAllowed($all_control)
    {
        $query = '(' . $this->getText() . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $condition_b = 'AND
                        (
                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $this->extension_copy->copy_id . ' AND data_id = data.' . $this->extension_copy->prefix_name . '_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user") 
                            OR  
                            exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $this->extension_copy->copy_id . ' AND data_id = data.' . $this->extension_copy->prefix_name . '_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',', $roles) . ')') . ' AND ug_type = "group")
                        )';
        if ($all_control == false) {
            $condition_b = '';
        }

        $this->setWhere([
            'AND',
            '
                 {{process_operations}}_process_id is NULL
                 ' . $condition_b . '
                 OR
                 (
                    {{process_operations}}_process_id is NOT NULL
                    AND
                    (
                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user")
                        OR
                        exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',', $roles) . ')') . ' AND ug_type = "group")
                    )
                 )
            '
        ]);
    }

    /**
     * Добавляет условие отбора данных "только участники по связи через модуль через поле Название
     *
     * @return string
     */
    public function setDataBasedParentModule($copy_id, $all_control = false)
    {
        $parent_copy_id = \ModuleTablesModel::getParentModuleCopyId($copy_id);
        if (empty($parent_copy_id)) {
            $this->setProcessPartisipantAllowed($all_control);

            return $this;
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id);

        if ($parent_extension_copy->dataIfParticipant() == false || ($parent_extension_copy->isParticipant() == false && $parent_extension_copy->isResponsible() == false)) {
            $this->setProcessPartisipantAllowed($all_control);

            return $this;
        }

        $relate_model = \ModuleTablesModel::model()->find([
                'condition' => 'copy_id =:copy_id AND relate_copy_id =:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id'        => $parent_copy_id,
                    ':relate_copy_id' => $copy_id,
                ]
            ]
        );

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $query = 'SELECT tt3.* FROM ' . $parent_extension_copy->getTableName() . ' tt1
                    LEFT JOIN {{' . $relate_model->table_name . '}} tt2 ON tt1.' . $parent_extension_copy->prefix_name . '_id = tt2.' . $relate_model['parent_field_name'] . '
                    RIGHT JOIN (' . $this->getText() . ') tt3 ON  tt2.' . $relate_model['relate_field_name'] . ' = tt3.' . $extension_copy->prefix_name . '_id';

        $condition = '
                    WHERE
                        (
                            {{process_operations}}_process_id is NULL
                            AND
                            (
                                tt1.' . $parent_extension_copy->prefix_name . '_id is NOT null AND (
                                    (
                                    exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $parent_extension_copy->copy_id . ' AND data_id = tt1.' . $parent_extension_copy->prefix_name . '_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user")
                                    OR
                                    exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . $parent_extension_copy->copy_id . ' AND data_id = tt1.' . $parent_extension_copy->prefix_name . '_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',',
                    $roles) . ')') . ' AND ug_type = "group")
                                    ) 
                                )

                        ';

        if ($copy_id == \ExtensionCopyModel::MODULE_TASKS) {
            $condition .= ' OR (tt1.' . $parent_extension_copy->prefix_name . '_id is null AND tt3.is_bpm_operation is NULL)';
            $condition .= ' OR tt3.is_bpm_operation = "0"';
        } else {
            $condition .= ' OR (tt1.' . $parent_extension_copy->prefix_name . '_id is NULL)';
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $roles = UsersModel::model()->getUserModel()->getUsersRoles();

        $condition .= '  )
                        ) 
                        OR (
                            {{process_operations}}_process_id is NOT NULL
                            AND
                            (
                                exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id = ' . WebUser::getUserId() . ' AND ug_type = "user")
                                OR
                                exists (SELECT participant_id FROM {{participant}} WHERE copy_id = ' . \ExtensionCopyModel::MODULE_PROCESS . ' AND data_id = {{process_operations}}_process_id AND ug_id ' . (count($roles) == 1 ? ' = ' . $roles[0] : 'in (' . implode(',', $roles) . ')') . ' AND ug_type = "group")
                            )
                        )
                        
        ';

        $query = '(' . $query . $condition . ') as data';
        $params = $this->command->params;
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;

        return $this;
    }

    /**
     * Добавляет условие к запросу, которое убирает сущности, если родительская сущность,
     * что связана через relate_title - шаблон
     *
     * @return $this
     */
    public function withOutRelateTitleTemplate($pdi = null, $alias = 'data')
    {
        $realte_copy_id = $this->extension_copy->getParentPrimaryCopyId();

        if ($realte_copy_id == false) {
            return $this;
        }

        $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($realte_copy_id);

        if ($relate_extension_copy->getIsTemplate() === "0") {
            return $this;
        }

        if ($pdi) {
            $relate_data_count = (new DataModel())
                ->setFrom($relate_extension_copy->getTableName())
                ->setWhere($relate_extension_copy->getPkFieldName() . '=:pdi', [':pdi' => $pdi])
                ->findCount();

            if ($relate_data_count) {
                return $this;
            }
        }

        $module_table_model = ModuleTablesModel::getRelateModel($realte_copy_id, $this->extension_copy->copy_id, ModuleTablesModel::TYPE_RELATE_MODULE_ONE);

        if ($module_table_model == false) {
            return $this;
        }

        $alias = $alias ? $alias . '.' : '';

        $this->andWhere('
            NOT exists(
                SELECT *
                FROM {{' . $module_table_model->table_name . '}} t199
                LEFT JOIN ' . $relate_extension_copy->getTableName() . ' t200 ON t199.' . $relate_extension_copy->getPkFieldName() . ' = t200.' . $relate_extension_copy->getPkFieldName() . '
                WHERE
                    t199.' . $this->extension_copy->getPkFieldName() . ' = ' . $alias . $this->extension_copy->getPkFieldName() . ' AND
                    t200.this_template = "1"
        
                )             
        ');

        return $this;
    }

    /**
     * strReplace
     * Замена параметров реальными данными.
     * Внимание! Все данные берутся в кавычки. Тип поля не учитывается!
     */
    public function replaceParamsOnRealValue($query = null, $params = null)
    {
        if ($query === null) {
            $query = $this->getText();
        }
        if ($params === null) {
            $params = $this->getParams();
        }

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $query = \Helper::strReplace($key, '"' . $value . '"', $query);
            }
        }

        return $query;
    }

    /**
     * Добавляет условие отбора where как новый уровень
     *
     * @param $condition
     * @param array $params
     */
    public function addGlobalCondition($condition, $params = [])
    {
        $query = '(' . $this->getText() . ') as data';
        $params = array_merge($this->command->params, $params);
        $this->command->reset();
        $this->command->setFrom($query);
        $this->command->params = $params;
        $this->setWhere($condition, $params);
    }

    /**
     * setQueryVarsRelateFilters - применение фильтров из $this->_query_vars['relate']['filters']
     */
    public function setQueryVarsRelateFilters()
    {
        if (empty($this->_query_vars['relate'])) {
            return $this;
        }

        foreach ($this->_query_vars['relate'] as $query_var) {
            if (empty($query_var['filters'])) {
                continue;
            }
            $extension_copy = \ExtensionCopyModel::model()->findByPk($query_var['copy_id']);
            $module_tables_relate = ModuleTablesModel::getRelateModel($this->extension_copy->copy_id, $query_var['copy_id'], [ModuleTablesModel::TYPE_RELATE_MODULE_ONE, ModuleTablesModel::TYPE_RELATE_MODULE_MANY]);

            //TYPE_RELATE_MODULE_MANY
            if ($module_tables_relate->type == ModuleTablesModel::TYPE_RELATE_MODULE_MANY) {

                $this->join($module_tables_relate->table_name,
                    $this->extension_copy->getTableName() . '.' . $module_tables_relate->parent_field_name .
                    '=' .
                    '{{' . $module_tables_relate->table_name . '}}.' . $module_tables_relate->parent_field_name,
                    [],
                    self::JOIN_INNER
                );
            }

            $this->join(
                $this->getDataQueryForVarsRelateFilters($extension_copy, $query_var['filters']),
                '{{' . $module_tables_relate->table_name . '}}.' . $module_tables_relate->relate_field_name .
                '=' .
                $extension_copy->getTableName() . '.' . $module_tables_relate->relate_field_name,
                [],
                self::JOIN_INNER,
                false
            );
        }

        return $this;
    }

    /**
     * getDataQueryForVarsRelateFilters
     */
    private function getDataQueryForVarsRelateFilters($extension_copy, $filter_data)
    {
        //  get data
        $data_model = new \DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        //responsible
        if ($extension_copy->isResponsible()) {
            $data_model->setFromResponsible(true);
        }

        //participant
        if ($extension_copy->isParticipant()) {
            $data_model->setFromParticipant(true);
        }

        //this_template
        $data_model->andWhere(['AND', $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null']);

        if (!empty($filter_data)) {
            $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup()
            ->replaceParamsOnRealValue();

        $result = $data_model->getText();

        $result = '(' . $result . ') as ' . $extension_copy->getTableName();

        return $result;

    }
}
