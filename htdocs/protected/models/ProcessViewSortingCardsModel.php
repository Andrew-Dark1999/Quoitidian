<?php
/**
 * ProcessViewSortingCardsModel - Сортировка сущностей в process view
 */

class ProcessViewSortingCardsModel extends ProcessViewSortingFactoryModel{

    private static $_instance;

    protected $_table_name = 'process_view_sorting_cards';

    protected $_extension_copy;
    protected $_sorting_list_id;


    public static function getInstance($refresh = false){
        if(static::$_instance === null || $refresh){
            static::$_instance = new static();
        }

        return static::$_instance;
    }




    /**
     * addInsertValues - добавляет данные для insert запросса. Возвращает true, если посде добавления данные были записаны в БД
     */
    public function insertPrepareValueAndAdd(array $data){
        if($this->_insert_last_sort === null){
            $this->_insert_last_sort = $this->getLastSort();
        }

        $values = array(
            'sorting_list_id' => $this->_sorting_list_id,
            'data_id' => $data[$this->_extension_copy->getPkFieldName()],
            'sort' => $this->getInsertNextSort(),
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

        $fields = 'sorting_list_id, data_id, sort';

        $values = implode(',', $this->_insert_values);

        $query = 'INSERT INTO ' . $this->getTableName() . ' ('. $fields .') VALUES ' . $values . ';';

        $this->_insert_queries[] = $query;

        $this->_insert_values[] = array();
        $this->_insert_values_count = 0;

        return $this;
    }





    /**
     *  getLastSort - последний индекс сортировки
     */
    protected function getLastSort(){
        $condition = 'sorting_list_id=:sorting_list_id';
        $params =  array(
            ':sorting_list_id' => $this->_sorting_list_id,
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
     * updateCardsSort - обновление индексов сортировки карточек
     */
    public function updateCardsSort($vars){
        if($this->_sorting_list_id == false) return false;
        if(empty($vars['sorting_cards_id_list'])) return false;

        $sort = 1;

        // sorting_cards_id_before
        if($vars['sorting_cards_id_before'] && $vars['sorting_cards_id_before'] != -1){
            $before_sort = (new \DataModel())
                                ->setSelect('sort')
                                ->setFrom($this->getTableName())
                                ->setWhere('sorting_cards_id=:sorting_cards_id',[':sorting_cards_id'=>$vars['sorting_cards_id_before']])
                                ->findScalar();

            $sort = (int)$before_sort+1;
        }

        // update sort for "sorting_cards_id"
        foreach($vars['sorting_cards_id_list'] as $sorting_cards_id){
            (new \DataModel)->Update(
                                $this->getTableName(),
                                [
                                    'sorting_list_id' => $this->_sorting_list_id,
                                    'sort' => $sort,
                                ],
                                'sorting_cards_id=:sorting_cards_id',
                                [':sorting_cards_id' => $sorting_cards_id]
            );
            $sort++;
        }

        $condition = [
            'sorting_list_id=:sorting_list_id',
        ];
        $params = [
            ':sorting_list_id' => $this->_sorting_list_id,
        ];

        if(!empty($before_sort)){
            $condition = array_merge($condition, [
                'sort>:sort',
                'sorting_cards_id not in('.implode($vars['sorting_cards_id_list']).')',
            ]);
            $params+= [
                ':sort' => $before_sort,
            ];
        } else {
            $condition = array_merge($condition, [
                'sorting_cards_id not in('.implode($vars['sorting_cards_id_list']).')',
            ]);
        }

        // update sort values
        (new DataModel())->Update($this->getTableName(), ['sort'=>new CDbExpression('sort + ' . count($vars['sorting_cards_id_list']))], implode(' AND ', $condition), $params);

        return true;
    }





    /**
     * deleteCards - удаляем сортировку карточки
     */
    /*
    public function deleteCards($sorting_cards_id_list){
        if($sorting_cards_id_list == false){
            return true;
        }

        $sorting_cards_id_list = array($sorting_cards_id_list);
        $sorting_cards_id_list = addslashes(implode(', ', $sorting_cards_id_list));


        $condition = '
            sorting_cards_id in ('.$sorting_cards_id_list.') AND
            exists(SELECT *
                FROM ' . (new \ProcessViewSortingListModel())->getTableName() . '  
                WHERE
                ' . $this->getTableName() . '.sorting_list_id = sorting_list_id AND
                users_id = ' .  $this->_users_id . ')';


        (new \DataModel())->Delete($this->getTableName(), $condition);

        return true;
    }
    */




    /*
    private function getQueryModelModuleEntities($vars){
        $process_view_bulder = (new ProcessViewBuilder())
                    ->setExtensionCopy($vars['extension_copy'])
                    ->setPci($vars['pci'])
                    ->setPdi($vars['pdi'])
                    ->setThisTemplate($vars['this_template'])
                    ->setFinishedObject($vars['finished_object']);

        $data_model = new DataModel();
        $data_model
            ->setSelect($vars['extension_copy']->getPkFieldName(true))
            ->setExtensionCopy($vars['extension_copy'])
            ->setFromModuleTables();

        //responsible
        if($vars['extension_copy']->isResponsible())
            $data_model->setFromResponsible(false);

        //participant
        if($vars['extension_copy']->isParticipant())
            $data_model->setFromParticipant(false);


        if($vars['extension_copy']->finished_object){
            list($filter_controller) = Yii::app()->createController($vars['extension_copy']->extension->name . '/ListViewFilter');
            if(Yii::app()->request->getParam('finished_object')){
                $filter_data_2 = $filter_controller->getParamsToQuery($vars['extension_copy'], array(FilterVirtualModel::VF_FINISHED_OBJECT), array(FilterVirtualModel::VF_FINISHED_OBJECT => array('corresponds' => 'corresponds')));
            } else {
                $filter_data_2 = $filter_controller->getParamsToQuery($vars['extension_copy'], array(FilterVirtualModel::VF_FINISHED_OBJECT), array(FilterVirtualModel::VF_FINISHED_OBJECT => array('corresponds' => 'corresponds_not')));
            }
            if(!empty($filter_data_2))
                $data_model->andWhere($filter_data_2['conditions'], $filter_data_2['params']);
        }


        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->prepare();
        $data_model
            ->setUniqueIndex($process_view_bulder->getFieldsGroup(true));

        return $data_model;
    }
    */




    /*
    public function getDataIdListBySortingListId($vars){
        if($vars['sorting_list_id'] === null){
            $vars['sorting_list_id'] = $this->_sorting_list_id;
        }

        $data_model = $this->getQueryModelModuleEntities($vars);

        $query = '
                SELECT '. $vars['extension_copy']->getPkFieldName() .'
                FROM (
                  '.$data_model->getText().'
                ) as t0
                
                WHERE
                  '. $vars['extension_copy']->getPkFieldName() .' in (
                    SELECT data_id
                    FROM '.$this->getTableName().'
                    WHERE sorting_list_id = '.$vars['sorting_list_id'].'
                  )
                  AND
                  exists(
                      SELECT data_id
                      FROM '.$this->getTableName().'
                      WHERE
                        exists(
                            SELECT *
                            FROM '.(new \ProcessViewSortingListModel())->getTableName().'
                            WHERE
                              sorting_list_id = '.$vars['sorting_list_id'].' AND
                              {{process_view_sorting_cards}}.sorting_list_id = sorting_list_id AND
                              t0.unique_index = unique_index
                        )
                  )
        ';

        $list = (new \DataModel())
                    ->setText($query)
                    ->setParams($data_model->getParams())
                    ->findCol();

        return $list;
    }
    */




}
