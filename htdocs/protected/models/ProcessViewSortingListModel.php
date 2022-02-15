<?php
/**
 * ProcessViewSortingListModel - Сортировка списков в process view
 */

class ProcessViewSortingListModel extends ProcessViewSortingFactoryModel{

    const PANEL_MENU_ACTION_DELETE  = 'delete';
    const PANEL_MENU_ACTION_ARCHIVE = 'archive';

    private static $_instance;

    protected $_table_name = 'process_view_sorting_list';

    protected $_extension_copy;
    protected $_users_id;
    protected $_pci;
    protected $_pdi;
    protected $_finished_object;
    protected $_this_template;




    public static function getInstance($refresh = false){
        if(static::$_instance === null || $refresh){
            static::$_instance = new ProcessViewSortingListModel();
            static::$_instance->_users_id = \WebUser::getUserId();
        }

        return static::$_instance;
    }



    public function setGlobalVars(array $vars){
        parent::setGlobalVars($vars);

        $this->initUsersId();
        $this->initProcessViewModel();

        return $this;
    }



    private function initUsersId(){
        if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS && $this->_pci && $this->_pdi){
            $this->_users_id = null;
        }

        return $this;
    }



    private function initProcessViewModel(){
        if(ProcessViewModel::isInit()) return;

        ProcessViewModel::getInstance()
            ->setExtensionCopy($this->_extension_copy)
            ->setPci($this->_pci)
            ->setPdi($this->_pdi)
            ->setThisTemplate($this->_this_template)
            ->setFinishedObject($this->_finished_object);
    }



    public function getUsersId(){
        return $this->_users_id;
    }


    public function getGroupData(){
        $this->initProcessViewModel();

        return ProcessViewModel::getInstance()->getGroupData();
    }


    public function findByPk($sorting_list_id){
        return (new \DataModel())
                    ->setFrom($this->getTableName())
                    ->setWhere(
                        'sorting_list_id=:sorting_list_id',
                        [':sorting_list_id' => $sorting_list_id]
                    )
                    ->findRow();
    }




    /**
     * insertPrepareValueAndAdd - добавляет данные для insert запросса. Возвращает true, если посде добавления данные были записаны в БД
     */
    public function insertPrepareValueAndAdd(array $data){
        if($this->_insert_last_sort === null){
            $this->_insert_last_sort = $this->getLastSort();
        }

        $values = array(
            'users_id' => ($this->_users_id ? addslashes($this->_users_id) : 'null'),
            'copy_id' => $this->_extension_copy->copy_id,
            'pci' => ($this->_pci ? addslashes($this->_pci) : 'null'),
            'pdi' => ($this->_pdi ? addslashes($this->_pdi) : 'null'),
            'group_data' => '"' . $this->getGroupData() . '"',
            'unique_index' => "'" . $data['unique_index'] . "'",
            'fields_data' => "'" . $data['fields_data'] . "'",
            'mirror' => ($data['mirror'] ? '1' : 'null'),
            'sort' => $this->getInsertNextSort(),
        );


        $values = '(' . implode(',', $values) . ')';
        $this->insertValueAdd($values);

        return $this;
    }



    /**
     * insertAdd - добавляет данные для insert запросса
     */
    public function insertAdd(array $values){
        $values = array(
            'users_id' => ($values['users_id'] ? $values['users_id'] : 'null'),
            'copy_id' => ($values['copy_id'] ? $values['copy_id'] : 'null'),
            'pci' => ($values['pci'] ? $values['pci'] : 'null'),
            'pdi' => ($values['pdi'] ? $values['pdi'] : 'null'),
            'group_data' => ($values['group_data'] ? '"' . $values['group_data'] . '"' : 'null'),
            'unique_index' => ($values['unique_index'] ? '"' . $values['unique_index'] . '"' : 'null'),
            'fields_data' => ($values['fields_data'] ? '"' . addslashes($values['fields_data']) . '"' : 'null'),
            'mirror' => ($values['mirror'] ? '"' . $values['mirror'] . '"' : 'null'),
            'sort' => ($values['sort'] ? $values['sort'] : 'null'),
        );


        $values = '(' . implode(',', $values) . ')';
        $this->insertValueAdd($values);

        return $this;
    }




    /**
     * insertPrepareQuery - запись в базу индекса сортировки
     */
    protected function insertPrepareQuery($length = null){
        if($this->_insert_values == false) return false;

        if($length || $this->_insert_values_count < $length){
            return false;
        }

        $fields = 'users_id, copy_id, pci, pdi, group_data, unique_index, fields_data, mirror, sort';

        $values = implode(',', $this->_insert_values);

        $query = 'INSERT INTO ' . $this->getTableName() . ' ('. $fields .') VALUES ' . $values . ';';

        $this->_insert_queries[] = $query;

        $this->_insert_values[] = array();
        $this->_insert_values_count = 0;

        return true;
    }



    /**
     *  getLastSort - последний индекс сортировки
     * WHERE user_id is not null
     */
    protected function getLastSort(){
        $condition = 'users_id=:users_id AND copy_id=:copy_id AND pci=:pci AND pdi=:pdi AND group_data=:group_data';
        $params =  array(
            ':users_id' => ($this->_users_id ? $this->_users_id : null),
            ':copy_id' => $this->_extension_copy->copy_id,
            ':pci' => ($this->_pci ? $this->_pci : null),
            ':pdi' => ($this->_pdi ? $this->_pdi : null),
            ':group_data' => $this->getGroupData(),
        );


        $sort_last = (new \DataModel())
                        ->setSelect('max(sort) as sort_max')
                        ->setFrom($this->getTableName())
                        ->setWhere($condition, $params)
                        ->findScalar();

        if(!empty($sort_last)) return $sort_last;

        return 0;
    }










    /**
     * updatePanelSort - обновление индексов сортировки панели
     */
    public function updatePanelSort($vars){
        if(empty($vars['sorting_list_id'])) return false;

        $sort = 1;

        // sorting_list_id_before
        if($vars['sorting_list_id_before'] && $vars['sorting_list_id_before'] != -1){
            $before_sort = (new \DataModel())
                                ->setSelect('sort')
                                ->setFrom($this->getTableName())
                                ->setWhere('sorting_list_id=:sorting_list_id',[':sorting_list_id'=>$vars['sorting_list_id_before']])
                                ->findScalar();

            $sort = (int)$before_sort+1;
        }

        // update sort for "sorting_list_id"
        (new \DataModel)->Update(
                            $this->getTableName(),
                            ['sort'=>$sort],
                            'sorting_list_id=:sorting_list_id',
                            [':sorting_list_id'=>$vars['sorting_list_id']]
        );

        // set condition
        $condition = [
            'users_id=:users_id',
            'copy_id=:copy_id',
            'pci=:pci',
            'pdi=:pdi',
            'group_data=:group_data',
        ];
        $params = [
            ':users_id' => ($this->_users_id ? $this->_users_id : null),
            ':copy_id' => $this->_extension_copy->copy_id,
            ':pci' => ($this->_pci ? $this->_pci : null),
            ':pdi' => ($this->_pdi ? $this->_pdi : null),
            ':group_data' => $this->getGroupData(),
        ];

        if(!empty($before_sort)){
            $condition = array_merge($condition, [
                'sort>=:sort',
                'sorting_list_id!=:sorting_list_id',
            ]);
            $params+= [
                ':sorting_list_id' => $vars['sorting_list_id'],
                ':sort' => $sort,
            ];
        } else {
            $condition = array_merge($condition, [
                'sorting_list_id!=:sorting_list_id',
            ]);
            $params+= [
                ':sorting_list_id' => $vars['sorting_list_id'],
            ];
        }

        // update sort values
        (new DataModel())->Update($this->getTableName(), ['sort'=>new CDbExpression('sort + 1')], implode(' AND ', $condition), $params);

        return true;
    }




    /**
     * accessForDelete - проверяет, можно ли удалять панель
     * @param $sorting_list_id
     * @return bool
     */
    public function accessForDelete($sorting_list_id){
        $data = (new \DataModel())
            ->setFrom($this->getTableName())
            ->setWhere('sorting_list_id=:id', [':id'=>$sorting_list_id])
            ->findRow();

        if($data == false) return true;

        if(
            $data['copy_id'] == \ExtensionCopyModel::MODULE_TASKS &&
            $data['pci'] &&
            $data['pdi'] &&
            !in_array($data['group_data'], [ProcessViewModel::GROUP_DATA_FINISHED_OBJECT, ProcessViewModel::GROUP_DATA_FINISHED_OBJECT_TEMPLATE])
        ){
            return false;
        }

        return true;
    }







    /**
     * flushCardsEntities - Удаляет продублированные устаревшие данные, что относятся к карточкам
     * @param $params: copy_id, pci, pdi
     */
    public function flushCardsEntities($data_id_list = null){
        $cards_table_name = (new ProcessViewSortingCardsModel())->getTableName();

        $query = ' 
            DELETE FROM '.$cards_table_name.'
                WHERE exists(
                            SELECT *
                            FROM (
                               SELECT
                               MAX(sorting_cards_id) AS sorting_cards_id,
                               data_id
                             FROM
                             (
                                 SELECT t0.*
                                 FROM
                                   '.$cards_table_name.' t0
                                 WHERE
                                   exists(
                                       SELECT
                                         MAX(t1.sorting_cards_id)   AS sorting_cards_id,
                                         COUNT(t1.sorting_cards_id) AS xcount
                                       FROM
                                         '.$cards_table_name.' t1
                                         LEFT JOIN
                                         '.$this->getTableName().' t2 ON t1.sorting_list_id = t2.sorting_list_id
                                       WHERE
                                         t1.data_id = t0.data_id AND
                                         t2.users_id '.($this->_users_id ? '='.$this->_users_id : 'is null').' AND
                                         t2.copy_id = '.$this->_extension_copy->copy_id.
                                         ($this->_pci ? ' AND t2.pci=' . addslashes($this->_pci) : ' AND t2.pci is null') .
                                         ($this->_pdi ? ' AND t2.pdi=' . addslashes($this->_pdi) : ' AND t2.pdi is null') . //' AND
                                         //t2.group_data = "'.$this->_group_data.'"
                                        '
                                       HAVING xcount > 1
                                   )
                                   '.($data_id_list ? 'AND data_id IN ('.implode(',',$data_id_list).')' : '').'
                               ) AS data
                             GROUP BY data_id
                           ) AS data
                      WHERE
                        '.$cards_table_name.'.sorting_cards_id != sorting_cards_id AND
                        '.$cards_table_name.'.data_id = data_id
                  )';

        (new \DataModel())->setText($query)->execute();
    }




    /**
     * flushPanelEntities - Удаляет продублированные устаревшие данные, что относятся к панелям
     * @param $params: copy_id, pci, pdi
     */
    public function flushPanelEntities($only_empty = true){
        $condition = ($only_empty ? '
                    not exists (
                        SELECT * FROM '.(new \ProcessViewSortingCardsModel())->getTableName().'
                            WHERE sorting_list_id = '.$this->getTableName().'.sorting_list_id
                        )
                        AND
                    ' : '').'
                        users_id '.($this->_users_id ? '='.$this->_users_id : 'is null').' AND
                        copy_id = '.$this->_extension_copy->copy_id.
                        ($this->_pci ? ' AND pci=' . addslashes($this->_pci) : ' AND pci is null') .
                        ($this->_pdi ? ' AND pdi=' . addslashes($this->_pdi) : ' AND pdi is null') . ' AND
                        group_data = "'.$this->getGroupData().'"
                    ';


        $copy_id_list = (new DataModel())
                            ->setSelect('copy_id')
                            ->setFrom($this->getTableName())
                            ->setWhere($condition)
                            ->setGroup('copy_id')
                            ->findCol();

        if($copy_id_list == false){
            return $this;
        }

        $query = 'DELETE FROM '.$this->getTableName().' WHERE '. $condition;
        (new \DataModel())->setText($query)->execute();

        (new ProcessViewTodoListModel())->clearModuleTodoList($copy_id_list);
    }



    /**
     * flushPanelAll - Удаляет все панели для модуля. Используется при удалении или очистке данных модуля
     * @param $params: copy_id, pci, pdi
     */
    public function flushPanelAll($copy_id){
        $query = '
            DELETE FROM '.$this->getTableName().'
            WHERE users_id is not NULL AND (copy_id='.$copy_id.' OR pci= '.$copy_id . ')';

        (new \DataModel())->setText($query)->execute();

        (new ProcessViewTodoListModel())->clearModuleTodoList($copy_id);
    }



    /**
     * isChangedSorting - проверяет на изменение сортировки
     */
    public function isChangedSorting($fields_group){
        $history_fields = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_PV_SORTING_PANEL, $this->_extension_copy->copy_id . '_' . $this->getGroupData(), $this->_pci, $this->_pdi);

        if(empty($history_fields) || array_diff($history_fields, $fields_group)){
            History::getInstance()->setUserStorage(
                UsersStorageModel::TYPE_PV_SORTING_PANEL,
                $this->_extension_copy->copy_id . '_' . $this->getGroupData(),
                $fields_group,
                false,
                $this->_pci,
                $this->_pdi
            );
            return true;
        }

        return false;
    }





    /**
     * getNewUniqueIndex - генерирует и возвращает уникальний индекс (md5) по переданым значениям
     */
    public function getNewUniqueIndex($values_list = ''){
        $unique_index = DataValueModel::generateUniqueIndex($values_list);

        return $unique_index;
    }





    /**
     * saveTitle - сохраняет название
     * @param $vars - доп. параметры с фронта
     * @return array
     */
    public function saveTitle($vars){
        $result = [
            'status' => false,
        ];

        $field_values_list = [];
        foreach($vars['fields_data_list'] as $fields_data){
            $field_values_list[] = $fields_data['value'];
        }

        $sorting_list_data_base = (new DataModel())
                        ->setFrom($this->getTableName())
                        ->setWhere(
                            'sorting_list_id=:sorting_list_id AND users_id=:users_id',
                            [
                                ':sorting_list_id' => $vars['sorting_list_id'],
                                ':users_id' => $this->_users_id,
                            ])
                        ->findRow();

        if($sorting_list_data_base == false){
            return $result;
        }

        $fields_data_base = null;
        if($sorting_list_data_base['fields_data']){
            $fields_data_base = json_decode($sorting_list_data_base['fields_data'], true);
        }


        $query_cards_function = function() use ($vars){
            $query = '
              SELECT data_id
                  FROM {{process_view_sorting_cards}}
                    LEFT JOIN `{{process_view_sorting_list}}` ON {{process_view_sorting_list}}.sorting_list_id = {{process_view_sorting_cards}}.sorting_list_id
                  WHERE {{process_view_sorting_list}}.sorting_list_id = '.$vars['sorting_list_id'].' AND {{process_view_sorting_list}}.users_id ' . ($this->_users_id ? '='.$this->_users_id : 'is null');
            return $query;
        };

        $result_fields_data = [];
        $field_data_sort_list = [];
        $str_for_unique_index = '';

        // fields_data_list
        foreach($vars['fields_data_list'] as $fields_data){

            $s = [
                'field_name' => $fields_data['field_name'],
                'text' => $fields_data['value'],
            ];


            $params = $this->_extension_copy->getFieldSchemaParams($fields_data['field_name']);


            switch ($params['params']['type']){
                // MFT_STRING
                case \Fields::MFT_STRING :
                    // проверка, были ли изменены значение списка
                    if($fields_data_base == false || !array_key_exists($fields_data['field_name'], $fields_data_base) || $fields_data_base[$fields_data['field_name']] != $fields_data['value']){
                        DataModel::getInstance()->Update(
                            $this->_extension_copy->getTableName(),
                            array($fields_data['field_name'] => $fields_data['value']),
                            $this->_extension_copy->prefix_name . '_id in (' . $query_cards_function() . ')'
                        );
                    }

                    $s['id'] = '';
                    $s['value'] = $fields_data['value'];
                    $str_for_unique_index.= $fields_data['value'];
                    $field_data_sort_list[] = [$fields_data['field_name'] => $fields_data['value']];

                    break;

                // MFT_SELECT
                case \Fields::MFT_SELECT :
                    if(!empty($fields_data['id'])){
                        // проверка, были ли изменены значение списка
                        $id = (integer)$fields_data['id'];
                        if($fields_data_base == false || !array_key_exists($fields_data['field_name'], $fields_data_base) || $fields_data_base[$fields_data['field_name']] != $fields_data['value']){
                            // update select value
                            DataModel::getInstance()->Update(
                                $this->_extension_copy->getTableName($fields_data['field_name']),
                                array($fields_data['field_name'].'_title' => $fields_data['value']),
                                $fields_data['field_name'].'_id=:id',
                                array(':id'=> $fields_data['id'])
                            );
                        }
                    } else {
                        // insert
                        (new DataModel())->Insert(
                            $this->_extension_copy->getTableName($fields_data['field_name']),
                            array($fields_data['field_name'].'_title' => $fields_data['value'])
                        );
                        $id = (new DataModel())
                                ->setSelect('LAST_INSERT_ID()')
                                ->setFrom($this->_extension_copy->getTableName($fields_data['field_name']))
                                ->findScalar();

                        (new ProcessViewTodoListModel())->insert([
                            'todo_list_id' => $id,
                            'sorting_list_id' => $vars['sorting_list_id'],
                        ]);

                        $id = (int)$id;

                        DataModel::getInstance()->Update(
                            $this->_extension_copy->getTableName(),
                            array($fields_data['field_name'] => $id),
                            $this->_extension_copy->prefix_name . '_id in (' . $query_cards_function() . ')'
                        );
                    }

                    $s['id'] = $id;
                    $s['value'] = $id;
                    $str_for_unique_index.= (string)$id;

                    $field_data_sort_list[$fields_data['field_name']] = $id;
                    break;

            } // end switch


            $result_fields_data[] = $s;
        }

        $unique_index = $this->getNewUniqueIndex($str_for_unique_index);


        // update "fields_name"
        (new DataModel())->Update(
            $this->getTableName(),
            [
                'unique_index' => $unique_index,
                'fields_data' => json_encode($field_data_sort_list),
                'mirror' => ($this->isMirror($vars['sorting_list_id'], $unique_index) ? "1" : null),
            ],
            'sorting_list_id=:sorting_list_id AND users_id=:users_id',
            [
                ':sorting_list_id' => $vars['sorting_list_id'],
                ':users_id' => ($this->_users_id ? $this->_users_id : null),
            ]
        );

        if($result_fields_data){
            $result += array(
                'status' => true,
                'sorting_list_id' => $vars['sorting_list_id'],
                'unique_index' => $unique_index,
                'fields_data' => $result_fields_data,
            );
        }

        return $result;
    }




    /**
     * isMirror -
     */
    public function isMirror($sorting_list_id, $unique_index){
        // set condition
        $condition = [
            'sorting_list_id!=:sorting_list_id',
            'users_id=:users_id',
            'copy_id=:copy_id',
            'pci=:pci',
            'pdi=:pdi',
            'group_data=:group_data',
            'unique_index=:unique_index',
        ];

        $params = [
            ':sorting_list_id' => $sorting_list_id,
            ':users_id' => ($this->_users_id ? $this->_users_id : null),
            ':copy_id' => $this->_extension_copy->copy_id,
            ':pci' => ($this->_pci ? $this->_pci : null),
            ':pdi' => ($this->_pdi ? $this->_pdi : null),
            ':group_data' => $this->getGroupData(),
            ':unique_index' => $unique_index,
        ];

        $mirror = (new \DataModel())
                    ->setSelect('count(*)')
                    ->setFrom($this->getTableName())
                    ->setWhere(implode(' AND ', $condition), $params)
                    ->findScalar();

        return (boolean)$mirror;
    }



    /**
     * accessAddPanels - возвращает статус доступа на изменение панелей
     * @return bool
     */
    public function accessChangePanels(){
        $b = false;

        if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS && $this->_pci && $this->_pdi){
            $b = true;
        }

        return $b;
    }



    /**
     * accessAddPanels - возвращает статус доступа на изменение панелей
     * @return bool
     */
    public function accessChangePanelsActions($action){
        if($this->accessChangePanels() == false){
            return false;
        }

        $b = false;

        switch($action){
            case self::PANEL_MENU_ACTION_ARCHIVE :
                if(
                    $this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS &&
                    $this->_pci &&
                    $this->_pdi &&
                    $this->_finished_object == false &&
                    $this->_this_template == false
                ){
                    $b = true;
                }
                break;
            default:
                $b = true;
        }

        return $b;
    }







    /**
     * getPanelMenuList - Возвращает список меню для списка
     * @return array|void
     */
    public function getPanelMenuList($check_access = true){
        if($check_access && $this->accessChangePanels() == false){
            return;
        }

        $menu_list = [
            self::PANEL_MENU_ACTION_ARCHIVE => Yii::t('base', 'Archive list'),
            self::PANEL_MENU_ACTION_DELETE => Yii::t('base', 'Delete list'),
        ];

        if($check_access){
            foreach($menu_list as $action => $title){
                if($this->accessChangePanelsActions($action) == false){
                    unset($menu_list[$action]);
                }
            }
        }

        return $menu_list;
    }


    /**
     * panelMenuActionRun - исполняет действие из меню списка
     * @param $vars
     */
    public function panelMenuActionRun($vars){
        switch($vars['run_action']){
            case self::PANEL_MENU_ACTION_DELETE :
                $this->PanelMenuActionDelete($vars);
                break;
            case self::PANEL_MENU_ACTION_ARCHIVE :
                $this->PanelMenuActionArchive($vars);
                break;
        }

        return $this;
    }





    /**
     * getDataIdListBySortingListId - возвращает список id карточек по sorting_list_id (дня удаления и архивации)
     * @param $vars
     */
    private function getDataIdListBySortingListId($vars){
        if($vars['sorting_list_id'] === null){
            $vars['sorting_list_id'] = $this->_sorting_list_id;
        }

        $panel_data = $this->findByPk($vars['sorting_list_id']);
        if($panel_data == false) return;

        $process_view_bulder = (new ProcessViewBuilder())
                                    ->setExtensionCopy($vars['extension_copy'])
                                    ->setPci($vars['pci'])
                                    ->setPdi($vars['pdi'])
                                    ->setThisTemplate($vars['this_template'])
                                    ->setFinishedObject($vars['finished_object'])
                                    ->prepareFieldsGroup();

        $card_data_model = $process_view_bulder->getCardDataModel($panel_data);
        if($card_data_model == false) return;

        $card_data_model
            ->setSelectNew()
            ->setSelect($vars['extension_copy']->getPkFieldName());

        $data_list = $card_data_model->findCol();

        return $data_list;
    }





    /**
     * deletePanel - удаляем панель
     * @param bool $all_users - сквозное удаление панели для всех пользователей в одном отображении (group_data)
     */
    public function deletePanel($sorting_list_id, $check_access_delete = true, $all_users = false){
        if($sorting_list_id == false){
            return false;
        }

        if($check_access_delete && $this->accessForDelete($sorting_list_id) == false){
            return false;
        }

        // condition
        $condition = ['sorting_list_id=:sorting_list_id'];
        $params = [
            ':sorting_list_id' => $sorting_list_id,
        ];


        $sorting_list_data = (new \DataModel())
            ->setFrom($this->getTableName())
            ->setWhere(implode(' AND ', $condition), $params)
            ->findRow();

        if($sorting_list_data == false){
            return true;
        };


        if($all_users){
            $condition = [
                'copy_id=' . $sorting_list_data['copy_id'],
                'pci' . ($sorting_list_data['pci'] ? '='.$sorting_list_data['pci'] : ' is null'),
                'pdi' . ($sorting_list_data['pdi'] ? '='.$sorting_list_data['pdi'] : ' is null'),
                'unique_index="' . $sorting_list_data['unique_index'].'"',
                'mirror' . ($sorting_list_data['mirror'] ? '='.$sorting_list_data['mirror'] : ' is null'),
                'group_data="' . $sorting_list_data['group_data'].'"',
            ];
            $params = [];
        } else {
            $condition[] = 'users_id=:users_id';
            $params[':users_id'] = $this->_users_id;
        }

        if($check_access_delete){
            $condition[] = 'not exists (SELECT * FROM ' . (new \ProcessViewSortingCardsModel())->getTableName() . ' WHERE sorting_list_id = ' . $this->getTableName() . '.sorting_list_id)';
        }

        $condition = implode(' AND ', $condition);

        (new \DataModel())->Delete($this->getTableName(), $condition, $params);

        $count = (new \DataModel())
            ->setFrom($this->getTableName())
            ->setWhere($condition, $params)
            ->findCount();

        // очистка ТОДО списка в модуле и связующей ТОДО-таблицы
        if($count == false){
            (new ProcessViewTodoListModel())->clearModuleTodoList($sorting_list_data['copy_id']);
        }

        return !(bool)$count;
    }




    /**
     * panelMenuActionDelete - удаление списка
     * @param $vars
     * @return array|bool
     */
    private function panelMenuActionDelete($vars){
        // delete entities
        $vars = array(
            'sorting_list_id' => $vars['sorting_list_id'],
            'extension_copy' => $this->_extension_copy,
            'finished_object' => $this->_finished_object,
            'this_template' => $this->_this_template,
            'pci' => $this->_pci,
            'pdi' => $this->_pdi,
        );

        $data_id_list = $this->getDataIdListBySortingListId($vars);

        if($data_id_list){
            $deleted = EditViewDeleteModel::getInstance()
                            ->setThisTemplate($this->_this_template)
                            ->prepare($this->_extension_copy->copy_id, $data_id_list)
                            ->delete()
                            ->getResult();

            if($deleted['status'] == false){
                $this->setError(true);
                return $this;
            }
        }

        // delete panel
        $this->deletePanel($vars['sorting_list_id'], false, true);

        return $this;
    }




    /**
     * panelMenuActionArchive - архивация списка
     * @param $vars
     * @return $this
     */
    private function panelMenuActionArchive($vars){
        if($this->accessChangePanels() == false){
            $this->setError(true);
            return $this;
        }

        // archive entities
        $vars = array(
            'sorting_list_id' => $vars['sorting_list_id'],
            'extension_copy' => $this->_extension_copy,
            'finished_object' => $this->_finished_object,
            'this_template' => $this->_this_template,
            'pci' => $this->_pci,
            'pdi' => $this->_pdi,
        );

        $data_id_list = $this->getDataIdListBySortingListId($vars);

        if($data_id_list == false){
            $this->addErrorMessage('Data not available');
            return $this;
        }


        $fo_select_id = $this->getFinishedObjectSelectId();
        if($fo_select_id == false){
            $this->addErrorMessage('The status for the completed object is not defined');
            return $this;
        }


        $status_params = $this->_extension_copy->getStatusField();

        // update entities
        (new \DataModel())->Update(
            $this->_extension_copy->getTableName(),
            [$status_params['params']['name'] => $fo_select_id],
            ['in', $this->_extension_copy->getPkFieldName(), $data_id_list]
        );

        // delete panel
        $this->deletePanel($vars['sorting_list_id'], false, true);

        return $this;
    }





    /**
     * getFinishedObjectSelectId - Возвращает ID статуса с атребутом finished_object
     * @return bool
     */
    private function getFinishedObjectSelectId(){
        $status_params = $this->_extension_copy->getStatusField();

        if(empty($status_params)) return;

        $id = \DataModel::getInstance()
            ->setSelect($status_params['params']['name'] . '_id')
            ->setFrom($this->_extension_copy->getTableName($status_params['params']['name']))
            ->setWhere($status_params['params']['name'] . '_finished_object = "1"')
            ->findScalar();

        return $id;
    }






}
