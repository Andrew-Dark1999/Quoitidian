<?php

/*****************************************************************
 *
 *          History
 *
 *****************************************************************/
class History {

    private $_last_history_id;

    protected $filter_model = 'FilterModel';
    protected $pci = null;
    protected $pdi = null;


    // разрешает добавление доп. информации в связаные подчиненные таблицы
    private $_add_realte_history_data = true;


    /**
     * @return History
     */
    public static function getInstance(){
        return new static();
    }


    public function getLastHistoryId(){
        return $this->_last_history_id;
    }


    public function setAddRealteHistoryData($add_realte_history_data){
        $this->_add_realte_history_data = $add_realte_history_data;
        return $this;
    }





    public function setPciFromParams($params){
        if(empty($params['params']['pci'])){
            return $this;
        }

        $pci  = $params['params']['pci'];
        if($pci == 'auto'){
            $pci = $this->getPciPdiAuto($params, 'pci');
        }

        $this->pci = $pci;

        return $this;
    }



    public function setPdiFromParams($params){
        if(empty($params['params']['pdi'])){
            $this->pci = null;
            return $this;
        }

        $pdi  = $params['params']['pdi'];
        if($pdi == 'auto'){
            $pdi = $this->getPciPdiAuto($params, 'pdi');
        }

        $this->pdi = $pdi;

        if($pdi == false){
            $this->pci = null;
        }

        return $this;
    }



    public function setPci($pci){
        $this->pci = $pci;
        return $this;
    }


    public function setPdi($pdi){
        $this->pdi = $pdi;
        return $this;
    }


    public function getPci(){
        return $this->pci;
    }

    public function getPdi(){
        return $this->pdi;
    }





    /**
     * запись активности
     * @param string $history_messages_index    - ID типа уведомления. Передается константой MT_ из HistoryMessagesModel
     * @param integer $copy_id                  - ИД модуля
     * @param integer $data_id                  - ИД данных модуля
     * @param array $params                     - доп. параметры. На основаннии из строется уведомление для вывода
     * @param bool $same_time                   - служит для обновления даты создания
     * @param bool $use_hitory_container        - использует промежуточный контейнер. Сохранение сразу в базу не производиться!
     * @param bool $user_create_is_null         - user_create = null. Использется в обсновном процессами
     * @param bool $loggin_responsible_only     - сохраняет (выводит) уведомления только для ответственного
     */
    public function addToHistory($history_messages_index, $copy_id = null, $data_id = null, array $params = null, $same_time = false, $use_hitory_container = false, $user_create_is_null = false, $loggin_responsible_only = false){
        $this->_last_history_id = null;

        HistoryModel::setSameTimeOnInsert($same_time);

        $history_model = new HistoryModel();
        $history_model
            ->setLogginResponsibleOnly($loggin_responsible_only)
            ->setAddRealteHistoryData($this->_add_realte_history_data);

        $history_model->attributes = array(
                                        'history_messages_index' => $history_messages_index,
                                        'copy_id' => $copy_id,
                                        'data_id' => $data_id,
                                        'params' => $params,
                                        );
        $history_model->setUserCreateIsNull($user_create_is_null);

        // если используем контейнер уведомлений
        if($use_hitory_container == true){
            $history_model->validate();
            \HistoryContainerModel::addToHistoryModelList($history_model);
            $history_model->updateSameTimeOnInsert();
        } else {
            $history_model->save();
            $this->_last_history_id = $history_model->getPrimaryKey();
        }
    }




    /**
     * Деактивирет старые логи сообщения из блока Актисность
     */
    /*
    public function unactiveOldComments($copy_id, $data_id, $activity_messages_id){
        $history_messages_index = implode(',', array(\HistoryMessagesModel::MT_COMMENT_CREATED, \HistoryMessagesModel::MT_COMMENT_CHANGED));
        $data_model = \DataModel::getInstance()
                            ->setSelect('history_id, params')
                            ->setFrom('{{history}}')
                            ->setWhere(
                                    'copy_id=:copy_id AND data_id=:data_id AND history_messages_index in (' . $history_messages_index . ')',
                                    array(':copy_id'=>$copy_id, ':data_id'=>$data_id))
                            ->findAll();

        if(empty($data_model)) return;
        foreach($data_model as $row){
            if(empty($row['params'])) continue;
            $params = json_decode($row['params'], true);
            if(empty($params['{activity_messages_id}'])) continue;
            if($params['{activity_messages_id}'] == $activity_messages_id){
                $history_id_list[] = $row['history_id'];
            }
        }

        if(!empty($history_id_list)){
            \DataModel::getInstance()->Delete('{{history_mark_view}}', 'history_id in ('. implode(',', $history_id_list) . ')');
        }
    }
    */




    private function getIsView($history_id, $user_id){
        $is_view = (new \DataModel())
                        ->setSelect('is_view')
                        ->setFrom('{{history_mark_view}}')
                        ->setWhere('user_id=:user_id AND history_id=:history_id', [':user_id'=>$user_id,':history_id'=>$history_id])
                        ->findScalar();

        return ($is_view ? true : false);
    }


    private static function getCountNewEntites($date_start, $user_id){
        $result = \DataModel::getInstance()
            ->setSelect('count(*)')
            ->setFrom('{{history}} t1')
            ->setWhere('t1.date_create > "'.$date_start.'" AND exists (SELECT id FROM {{history_mark_view}} WHERE t1.history_id = history_id AND user_id='.$user_id . ')')
            ->findScalar();

        return ($result ? (int)$result : 0);
    }




    private static function getCountUpdatedEntites($date_start){
        //if($date_start == false) return false;
        return true;
    }



    /**
     * @param $message_object_name

     * @param array $condition_vars. Vars: offset, limit, user_id, after_history_id, before_history_id, get_notice_count, only_is_view
     * @param int $limit
     * @param array $notification_delivery_vars - парамтры для рассылки на внешние источники
     * @return array
     */
    public function getFromHistory($message_object_name, array $query_vars = null, $notification_delivery_vars = null){
        $result = array(
            'total' => 0,       //количество всех данных
            'new'   => 0,       //количество новых данных
            'updated' => false, //отметка об обновлении старых данных
            'data'  => array(), //данные
        );

        $offset = 0;
        $limit_default = 20;
        $limit = $limit_default;
        $date_last = null;
        $limit_append = false;
        $get_new = false;
        $get_notice_count = true;      // возвратить суммы
        $user_id = null;

        if($query_vars){
            foreach($query_vars as $key => $value){
                ${$key} = $value;
            }
        }
        unset($value);

        if($user_id === null){
            $user_id = WebUser::getUserId();
        }

        /*
        $sql_data_conditions = array();
        $extension_copy_list = ExtensionCopyModel::model()->modulesActive()->modulesUser()->findAll();

        foreach($extension_copy_list as $extension_copy){
            //если в настройках модуля стоит показывать только учасникам, и у нас есть блок учасники, то скрываем записи в которых мы не есть учасниками
            $participant = $extension_copy->getParticipantField();
            if(!empty($extension_copy['data_if_participant']) && !empty($participant)){

                $data_id_list = \DataModel::getInstance()
                                    ->setSelect('data_id')
                                    ->setFrom('{{participant}}')
                                    ->setWhere(
                                        'copy_id=:copy_id AND ug_id=:ug_id',
                                        array(
                                            ':copy_id' => $extension_copy['copy_id'],
                                            ':ug_id' => $user_id,
                                        )
                                    )->findCol();

                if(empty($data_id_list)){
                    $sql_data_conditions[$extension_copy['copy_id']] = null;
                    continue;
                }

                $sql_data_conditions[$extension_copy['copy_id']] = $data_id_list;
                continue;
            }
            $sql_data_conditions[$extension_copy['copy_id']] = null;
        }

        if(empty($sql_data_conditions)){
            return $result;
        }

        // chack and delete copy_id
        $delete_list = array();
        foreach($sql_data_conditions as $copy_id => $value){
            $access = Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_VIEW, $copy_id);
            if(!$access){
                $delete_list[] = $copy_id;
            }
        }

        if(!empty($delete_list)){
            foreach($delete_list as $copy_id){
                unset($sql_data_conditions[$copy_id]);
            }
        }


        if(empty($sql_data_conditions)){
            return $result;
        }


        if(!empty($notification_delivery_vars['modules'])){
            foreach($sql_data_conditions as $copy_id => $value){
                if(!in_array($copy_id, $notification_delivery_vars['modules']))
                    unset($sql_data_conditions[$copy_id]);
            }
        }

        */

        $conditions_and = array();

        /*
        $conditions_or = array();
        */

        $conditions_and[] = '(user_create != '.$user_id.' OR user_create is NULL)';

        /*
        // set criteria
        $copy_id_list = array();
        foreach($sql_data_conditions as $copy_id => $value){
            if($value === null){
                $copy_id_list[] = $copy_id;
                continue;
            }

            $conditions_or[] = '(t.copy_id=' . $copy_id . ' AND t.data_id in ('.implode(',', $value).'))';
        }

        if($copy_id_list){
            $conditions_or[] = '( t.copy_id in ('.implode(',', $copy_id_list).'))';
        }

        if($conditions_or){
            $conditions_and[] =  '('. implode(' OR ', $conditions_or) .')';
        }
        */

        $condition = implode(' AND ', $conditions_and);


        // set criteria
        $criteria = new CDbCriteria();
        $criteria->addCondition($condition);


        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        $condition_mv = 'user_id = :user_id';

        if($notification_delivery_vars !== null){
            $condition_mv = 'user_id = :user_id AND notification_delivery = "'.HistoryMarkViewModel::DELIVERY_STATE_SEND.'"';
        }

        // offset, limit
        if($date_last !== null){
            $count_new_entities = static::getCountNewEntites($date_last, $user_id);

            if($count_new_entities){
                if($get_new){
                    $limit += $count_new_entities;
                } else{
                    $offset = $count_new_entities - 1;
                }
            }
        }

        if($limit_append){
            $count_update_entities = static::getCountUpdatedEntites($date_last);
            if($count_update_entities){
                $result['updated'] = true;
                $limit += $limit_default;
            } else {
                $offset += (($limit) ? $limit : 0);
                $limit = $limit_default;
            }
        }


        // Data counts
        if($get_notice_count){
            $total = HistoryModel::model()
                ->with(array(
                    'historyMarkView' => array(
                        'joinType' => 'JOIN',
                        'condition'=> $condition_mv,
                        'params' => array(
                            ':user_id' => $user_id
                        )
                    )
                )
            )->active()->count($criteria);

            if(!$total) {
                return $result;
            }

            $count_old = HistoryModel::model()
                ->with(array(
                        'historyMarkView' => array(
                            'joinType' => 'JOIN',
                            'condition' => 'user_id = :user_id and (is_view IS NOT NULL and is_view != 0)',
                            'params' => array(
                                ':user_id' => $user_id
                            )
                        )
                    )
                )->active()->count($criteria);


            $result['total'] = $total;
            $result['new']   = $total - $count_old;
        }

        $criteria->order = 'IF(historyMarkView.history_id IS NULL, 0, 1), IF(historyMarkView.is_view IS NULL, 0, 1)';

        if($offset) { $criteria->offset = $offset; }
        if($limit)  { $criteria->limit = $limit; }


        // Data All
        $history_model_data = HistoryModel::model()
            ->with(array(
                    'historyMarkView' => array(
                        'together' => true,
                        'joinType' => 'JOIN',
                        'condition' => $condition_mv,
                        'params' => array(
                            ':user_id' => $user_id
                        )
                    ),
                    'processOperations' => array(
                        'select' => 'process_id, unique_index',
                    ),
                )
            )
            ->active()
            ->findAll($criteria);


        ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_USERS)->getModule(null);

        $skip_total = 0;
        $skip_new = 0;

        if($history_model_data){
            foreach($history_model_data as $history_model){
                $message_params = array();

                $historyMarkView = null;
                $new = false;
                if(!empty($history_model->historyMarkView)){
                    $historyMarkView = $history_model->historyMarkView[0];
                    $new = ($historyMarkView->is_view == false ? true : false);
                }

                if(!$history_model->getParams($message_params)){
                    $skip_total++;
                    if($new) $skip_new++;
                    continue;
                }

                $history_messages_model = HistoryMessagesModel::getInstance()
                    ->setObjectName($message_object_name)
                    ->setMessageParams($message_params)
                    ->setHistoryModel($history_model)
                    ->prepare();
                $message_data = $history_messages_model->getResult();
                $message_data['new'] = $new;
                $message_data['history_model'] = $history_model;

                $result['data'][] = $message_data;
            }
        }

        $result = $this->prepareCounts($result, $skip_total, $skip_new);

        return $result;
    }




    /**
     * prepareCounts
     */
    private function prepareCounts($messages, $skip_total, $skip_new){
        if($skip_total || $skip_new){
            $messages['total'] = (integer)$messages['total'] - $skip_total;
            $messages['new'] = (integer)$messages['new'] - $skip_new;
            if((integer)$messages['total'] < (integer)$messages['new']){
                (integer)$messages['new'] = (integer)$messages['total'];
            }
        }

        return $messages;
    }




    /**
     * markHistoryIsView -  Установка статуса о прочтении сущности активным пользователем
     *                      Отметка устанавливается на все уведомления сущности
     * @param $copy_id
     * @param $data_id
     */
    public static function markHistoryIsViewByPk($history_id){
        $histories = HistoryModel::model()
            ->with(array(
                    'historyMarkView' => array(
                        'together' => true,
                        'joinType' => 'JOIN',
                        'condition'=> '(is_view IS NULL or is_view = "0") and user_id = :user_id',
                        'params' => array(
                            ':user_id' => WebUser::getUserId()
                        )
                    )
                )
            )
            ->findAllByPk($history_id);

        if(!empty($histories)){
            foreach($histories as $history){
                if(!empty($history->historyMarkView)){
                    foreach($history->historyMarkView as $historyMarkView){
                        $historyMarkView->setAttribute('is_view', 1);
                        if(!$historyMarkView->save()){
                            break;
                        }
                    }
                }
            }
            return $histories;
        }
    }




    /**
     * markHistoryIsView -  Установка статуса о прочтении сущности активным пользователем
     *                      Отметка устанавливается на все уведомления сущности
     * @param $copy_id
     * @param $data_id
     */
    public static function markHistoryIsView($copy_id, $data_id){
        if($data_id === null){
            $condition = 'copy_id =:copy_id and data_id is null';
            $params = array(
                ':copy_id' => $copy_id,
            );
        } else {
            $condition = 'copy_id =:copy_id and data_id=:data_id';
            $params = array(
                ':copy_id' => $copy_id,
                ':data_id' => $data_id
            );
        }

        $criteria = new CDbCriteria();
        $criteria->condition = $condition;
        $criteria->params = $params;

        $histories = HistoryModel::model()
            ->with(array(
                    'historyMarkView' => array(
                        'together' => true,
                        'joinType' => 'JOIN',
                        'condition'=> '(is_view IS NULL or is_view = "0") and user_id = :user_id',
                        'params' => array(
                            ':user_id' => WebUser::getUserId()
                        )
                    )
                )
            )
            ->findAll($criteria);

        if(!empty($histories)){
            foreach($histories as $history){
                if(!empty($history->historyMarkView)){
                    foreach($history->historyMarkView as $historyMarkView){
                        $historyMarkView->setAttribute('is_view', 1);
                        if(!$historyMarkView->save()){
                            break;
                        }
                    }
                }
            }
            return $histories;
        }
    }

    /**
     * @param $user_id
     * @param $count
     * @param $limit
     * @param $offset
     * @return mixed
     */
    public function getFromHistoryAll($user_id, $count = 0, $limit = 0, $offset = 0){

        $criteria = new CDbCriteria();
        if($count > 1 && $limit > 0){
            $criteria->limit = $limit;
            $criteria->offset = $offset;
        }
        $criteria->group = 'history_id';

        if(!$user_id){
            $user_id = WebUser::getUserId();
        }

        $history_model = HistoryModel::model()
            ->active()
            ->setScopeUserCreate($user_id)
            ->group()
            ->findAll($criteria);

        return $history_model;
    }





    /*****************************************************************
     *
     *          UserStorageModel
     *
     *****************************************************************/



    
    /**
     * возвращает состояние обьекта из пользовательской истории
     */
    public function getUserStorage($type, $index, $pci = null, $pdi = null){
        if(empty($type) || empty($index)) return null;

        $condition = 'users_id=:users_id AND type=:type AND storage_index=:storage_index';
        $params = array(
            ':users_id'=>WebUser::getUserId(),
            ':type' => $type,
            ':storage_index' => $index,
        );

        if($pci){
            $condition .= ' AND pci=:pci';
            $params[':pci'] = $pci;
        } else {
            $condition .= ' AND pci is NULL';
        }
        if($pdi){
            $condition .= ' AND pdi=:pdi';
            $params[':pdi'] = $pdi;
        } else {
            $condition .= ' AND pdi is NULL';
        }


        $storage = UsersStorageModel::model()->find(array('condition' => $condition,
                                                          'params' => $params));
        if(!empty($storage)) return $storage->getValue();
    }
    
    
    /**
     * запись состояния обьекта из пользовательской истории
     */  
    public function setUserStorage($type, $index, $value, $not_rewrite = false, $pci = null, $pdi = null){

        if(empty($type) || empty($index)) {
            return false;
        }

        $condition = 'users_id=:users_id AND type=:type AND storage_index=:storage_index';
        $params = array(
            ':users_id'=>WebUser::getUserId(),
            ':type' => $type,
            ':storage_index' => $index,
        );

        if($pci){
            $condition .= ' AND pci=:pci';
            $params[':pci'] = $pci;
        } else {
            $condition .= ' AND pci is NULL';
        }
        if($pdi){
            $condition .= ' AND pdi=:pdi';
            $params[':pdi'] = $pdi;
        } else {
            $condition .= ' AND pdi is NULL';
        }

        $storage = UsersStorageModel::model()->find(array('condition' => $condition,
                                                          'params' => $params));
        if(empty($storage)) {
            $storage = new UsersStorageModel();
        } else {
            if($not_rewrite){
                return false;
            }
        }

        $storage->setAttribute('type', $type);
        $storage->setAttribute('storage_index', $index);
        $storage->setValue($value);
        if($pci){
            $storage->setAttribute('pci', $pci);
        }
        if($pdi){
            $storage->setAttribute('pdi', $pdi);
        }

        if($storage->save()) {
            return true;
        }
    }






    /**
     * удаление состояния обьекта из пользовательской истории
     */
    public function deleteFromUserStorage($type, $index, $pci = null, $pdi = null){
        $condition = 'users_id=:users_id AND type=:type AND storage_index=:storage_index';
        $params = array(
            ':users_id'=>WebUser::getUserId(),
            ':type' => $type,
            ':storage_index' => $index,
        );

        if($pci){
            $condition .= ' AND pci=:pci';
            $params[':pci'] = $pci;
        } else {
            $condition .= ' AND pci is NULL';
        }
        if($pdi){
            $condition .= ' AND pdi=:pdi';
            $params[':pdi'] = $pdi;
        } else {
            $condition .= ' AND pdi is NULL';
        }

        UsersStorageModel::model()->deleteAll(array('condition' => $condition, 'params'=>$params));
    }    
 
 
    
    

    /**
     * обновление состояния обьекта пользовательской истории из урла
     */  
    public function updateUserStorageFromUrl($index, $page_name = null, $this_template = null, $pci = null, $pdi = null){
        $user_storage_type = array(
                                    UsersStorageModel::TYPE_LIST_FILTER,
                                    UsersStorageModel::TYPE_LIST_SORTING,
                                    UsersStorageModel::TYPE_LIST_PAGINATION,
                                    UsersStorageModel::TYPE_PAGE_PARAMS,
                                    UsersStorageModel::TYPE_FINISHED_OBJECT,
                                );


        foreach($user_storage_type as $type){
            $pci_q = null;
            $pdi_q = null;

            $value = array();

            if(is_array($index)){
                $storage_index = $index['destination'] . '_' . $index['copy_id'];
            } else {
                $storage_index = $index;
            }

            switch($type){
                case UsersStorageModel::TYPE_LIST_FILTER :
                    $pci_q = $pci;
                    if($page_name !== null) continue 2;
                    $filters = new \Filters();
                    $filters->setTextFromUrl();
                    if(!$filters->isTextEmpty()){
                        foreach($filters->getText() as $filter_name) $value[] = array('id'=>$filter_name);
                    }
                    if(is_array($index)){
                        $storage_index = $index['copy_id'];
                    }
                    break;

                case UsersStorageModel::TYPE_LIST_SORTING :
                    $pci_q = $pci;
                    $pdi_q = $pdi;
                    if($page_name !== null) continue 2;
                    $value = Sorting::getInstance()->getParamsWithOriginalDirections();
                    break;
                case UsersStorageModel::TYPE_LIST_PAGINATION :
                        if($page_name !== null || (isset($index['destination']) && $index['destination'] == 'processView')){
                            continue 2;
                        }
                        $min_page = array_keys(Pagination::getInstance()->page_sizes);
                        $min_page = Pagination::getInstance()->page_sizes[$min_page[0]];
                        if(Pagination::$active_page_size != $min_page) $value['page_size'] =  Pagination::$active_page_size;
                        if(Pagination::$active_page != 1) $value['page'] = Pagination::$active_page;
                        break;
                case UsersStorageModel::TYPE_PAGE_PARAMS :
                        if($page_name === null) continue 2;
                        $value = array(
                                    'active_page' => $page_name,
                                    'this_template' => $this_template,
                                    );
                        break;
                case UsersStorageModel::TYPE_FINISHED_OBJECT :
                        if($page_name === null) continue 2;
                        if(Yii::app()->request->getParam('finished_object'))
                            $value['finished_object'] = Yii::app()->request->getParam('finished_object');
                        break;
            }

            // если значения параметра нет - удаляем его из истории
            if(empty($value)){
                $delete = true;
                if($type == UsersStorageModel::TYPE_LIST_SORTING &&
                    ((is_array($index) && $index['copy_id'] == ExtensionCopyModel::MODULE_TASKS) || $index == ExtensionCopyModel::MODULE_TASKS) &&
                    Yii::app()->request->getParam('pci') && Yii::app()->request->getParam('pdi')
                ){
                    $delete = false;
                }

                if($delete) {
                    $this->deleteFromUserStorage($type, $storage_index, $pci_q, $pdi_q);
                }
                continue;
            }

            $not_rewrite = false;
            if($type == UsersStorageModel::TYPE_PAGE_PARAMS && !is_array($index) &&
                (int)$index == ExtensionCopyModel::MODULE_TASKS &&
                Yii::app()->request->getParam('pci') &&
                Yii::app()->request->getParam('pdi')
            ){
                $storage_index .= TasksModule::$relate_store_postfix_params;
                $not_rewrite = true;
            }

            $this->setUserStorage($type, $storage_index, $value, $not_rewrite, $pci_q, $pdi_q);
        }

    }    
    
    

    /**
     * возвращает состояние обьекта из пользовательской истории в виде параметров урла
     */
    public function getUserStorageUrlParams($index, $pci = null, $pdi = null, $return_string = true){
        if(!is_array($index)){
            $storage_index = array($index);
        } else {
            $storage_index = array(
                $index['destination'] . '_' . $index['copy_id'],
                $index['copy_id'],
            );
        }

        $type_list = array(
            \UsersStorageModel::TYPE_LIST_PAGINATION,
            \UsersStorageModel::TYPE_FINISHED_OBJECT,
        );


        $condition = [];
        $params = [
            ':users_id' => WebUser::getUserId(),
        ];

        if($pci && $pdi){
            $condition[] =
                '(
                (type = '.\UsersStorageModel::TYPE_LIST_SORTING.' AND pci=:pci AND pdi=:pdi) OR
                (type = '.\UsersStorageModel::TYPE_LIST_FILTER.' AND pci=:pci AND pdi is NULL) OR
                type in('.implode(',',$type_list).')
                )';
            $params[':pci'] = $pci;
            $params[':pdi'] = $pdi;
        } else {
            $condition[] =
                '(
                (type = '.\UsersStorageModel::TYPE_LIST_SORTING.' AND  pci is null AND pdi is null) OR
                (type = '.\UsersStorageModel::TYPE_LIST_FILTER.' AND  pci is null AND pdi is null) OR
                type in('.implode(',',$type_list).')
                )';
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition('users_id=:users_id');
        $criteria->addCondition(implode(' AND ', $condition));
        $criteria->params = $params;
        $criteria->addInCondition('storage_index', $storage_index);

        $storage_model_list = UsersStorageModel::model()->findAll($criteria);

        if(empty($storage_model_list)) return;
        $url = array();


        foreach($storage_model_list as $storage_model){
            $storage_value = $storage_model->getValue();
            switch($storage_model->type){
                case UsersStorageModel::TYPE_LIST_FILTER :
                        if(!empty($storage_value)){
                            $lich = 0; $filter_params = array();
                            foreach($storage_value as $filter){
                                $filter_model = new $this->filter_model;
                                if($filter_model->count('filter_id=:filter_id', array(':filter_id' => $filter['id'])) == 0 && FilterVirtualModel::isShowFilter($filter['id'], $index['copy_id']) == false) continue;

                                $filter_params[]= 'filters['.$lich.']='.$filter['id'];
                                $lich++;
                            }
                            if(!empty($filter_params)) $url[] = implode('&', $filter_params);
                        }

                        break;
                case UsersStorageModel::TYPE_LIST_SORTING :
                        $url[] = 'sort=' . json_encode($storage_value);
                        break;
                case UsersStorageModel::TYPE_LIST_PAGINATION :
                        if(!isset($index['destination']) || $index['destination'] != 'processView') {
                            if (isset($storage_value['page'])) $url[] = 'page=' . $storage_value['page'];
                            if (isset($storage_value['page_size'])) $url[] = 'page_size=' . $storage_value['page_size'];
                        }
                        break;
                case UsersStorageModel::TYPE_FINISHED_OBJECT :
                        if(isset($storage_value['finished_object']) && FilterVirtualModel::isShowFilter(FilterVirtualModel::VF_FINISHED_OBJECT, $index['copy_id'])) {
                            $url[] = 'finished_object=' . $storage_value['finished_object'];
                        }
                        break;
            }
        }

        if(!empty($url)){
            if($return_string) return implode('&', $url);
            else return $url;
        } 
    }
    
    
 

  





    /**
     * Возвращает урл на основании даных хранилища
     */
    protected function getDesination(array $params){

        $index = $params['copy_id'];
        if($params['copy_id'] == ExtensionCopyModel::MODULE_TASKS &&
            isset($params['params']['pci']) && $params['params']['pci'] &&
            isset($params['params']['pdi']) && $params['params']['pdi']
        ){
            ExtensionModel::model()->findByPk(ExtensionModel::MODULE_TASKS)->getModule();
            $index .= TasksModule::$relate_store_postfix_params;
        }

        if(isset($params['destination']) && !empty($params['destination'])){
            $destination = $params['destination'];

            if($index != $params['copy_id']){ //task relate

                $value = array(
                    'active_page' => $destination,
                    'this_template' => $params['params']['this_template'] ? EditViewModel::THIS_TEMPLATE_TEMPLATE : EditViewModel::THIS_TEMPLATE_MODULE,
                );

                $this->setUserStorage(\UsersStorageModel::TYPE_PAGE_PARAMS, $index, $value);

            }

        } else {
            $history = History::getInstance()->getUserStorage(\UsersStorageModel::TYPE_PAGE_PARAMS, $index);
            if(!empty($history['active_page'])) {
                $destination = $history['active_page'];
            } else {
                $destination = 'listView';
            }
        }
        
        $extension_copy = ExtensionCopyModel::model()->findByPK($params['copy_id']);
        if($extension_copy){
            switch($extension_copy->prefix_name){
                case 'roles' :
                    $destination = 'roles';
                    break;
                case 'users' :
                    if((integer)$params['copy_id'] == 1) $destination = 'users';
                    break;
                case 'permission' :
                    $destination = 'permission';
                    break;
                case 'webhook' :
                    $destination = 'webhook';
                    break;
            }
        }
        
        return $destination;
    }




    public function getModuleAction($params, $prepare_auto = false){
        //THIS_TEMPLATE_TEMPLATE
        if(!empty($params['params']['this_template']) && $params['params']['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            return 'showTemplate';
        }

        //$prepare_auto
        if($prepare_auto){
            if(empty($params['copy_id']) || empty($params['data_id'])){
                return 'show';
            }

            $extension_copy = \ExtensionCopyModel::model()->findByPk($params['copy_id']);
            $this_template = \DataModel::getInstance()
                                ->setSelect('this_template')
                                ->setFrom($extension_copy->getTableName())
                                ->setWhere($extension_copy->getPkFieldName() . ' = ' . $params['data_id'])
                                ->findScalar();

            if(!empty($this_template) && $this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                return 'showTemplate';
            }
        }

        return 'show';
    }


    private function getPciPdiAuto($params, $key){
        if(empty($params['copy_id']) || empty($params['data_id'])) return;

        if(!empty($params['params']['pci']) && $params['params']['pci'] != 'auto'){
            $parent_copy_id = $params['params']['pci'];
        } else {
            $extension_copy = \ExtensionCopyModel::model()->findByPk($params['copy_id']);
            $parent_copy_id = $extension_copy->getParentPrimaryCopyId();

            if($parent_copy_id == false) return;
        }

        if($key == 'pci') return $parent_copy_id;

        $module_tables = \ModuleTablesModel::getRelateModel($parent_copy_id, $params['copy_id'], \ModuleTablesModel::TYPE_RELATE_MODULE_ONE);
        if($module_tables == false) return;

        $data_id = \DataModel::getInstance()
                        ->setSelect($module_tables->parent_field_name)
                        ->setFrom('{{' . $module_tables->table_name . '}}')
                        ->setWhere($module_tables->relate_field_name .  ' = ' . $params['data_id'])
                        ->findScalar();

        if($data_id == false) return;

        return $data_id;
    }







    /**
     * Возвращает урл на основании даных хранилища
     */
    public function getUserStorageUrl(array $params){
        $destination_def =  "listView";
        $destination = $this->getDesination($params);

        if(!$params['copy_id']){
            return '/';
        }

        // формируем основной УРЛ
        switch($destination){
            case 'listView':
                        $action =  $this->getModuleAction($params);
                        $url = Yii::app()->createUrl('/module/listView/' . $action) . '/' . $params['copy_id'];
                        break;
            case 'processView':
                        $action =  $this->getModuleAction($params);
                        $destination_def = $destination;
                        $url = Yii::app()->createUrl('/module/processView/' . $action) . '/' . $params['copy_id'];
                        break;
            case 'calendarView':
                        $action =  $this->getModuleAction($params);
                        $destination_def = $destination;
                        $url = Yii::app()->createUrl('/module/calendarView/' . $action) . '/' . $params['copy_id'];
                        break;
            case 'roles': $url = Yii::app()->createUrl('roles'); break;
            case 'users': $url = Yii::app()->createUrl('users'); break;
            case 'webhook': $url = Yii::app()->createUrl('webhook'); break;
            case 'permission':
                        $url = Yii::app()->createUrl('roles') . '/' . $params['params']['pdi'];
                        unset($params['params']['pdi']);
                        unset($params['params']['pci']);
                        break;
            default :
                        $action =  $this->getModuleAction($params);
                        $url = Yii::app()->createUrl('/module/listView/' . $action) . '/' . $params['copy_id'];
                        break;
        }
        
        $url_params = $this->getUserStorageUrlParams(
            array('destination' => $destination_def, 'copy_id' => $params['copy_id']),
            $this->pci,
            $this->pdi
        );

        // параметры
        if(!empty($url_params)) $url.='?' . $url_params;


        // доп. параметры из post-a
        if(isset($params['params']) && is_array($params['params']) && count($params['params']) > 0){
            $params_post = array();
            foreach($params['params'] as $key => $value){
                if($key == 'this_template') continue;
                if(in_array($key, ['pci', 'pdi'])) continue; // пропускаем если auto

                $params_post[] = $key . '=' . $value;
            }

            // pci & pdi
            if($this->pci && $this->pdi){
                $params_post[] = 'pci=' . $this->pci;
                $params_post[] = 'pdi=' . $this->pdi;
            }

            if(!empty($params_post))
            if(empty($url_params)) $url.='?' . implode('&', $params_post);
                            else   $url.='&' . implode('&', $params_post);
        }

        return $url;
    }






    /*****************************************************************
     *
     *          UserStorageBackUrl
     *
     *****************************************************************/


    /**
     * Запись информации о последнем урле, на который перешел пользователь 
     * @return null
     */
    public static function setUserStorageBackUrl($controller_id, $action_id, $url = null)
    {
        $allowed_array=[
            'listView'      =>  ['actions'=>['show']],
            'processView'   =>  ['actions'=>['show']],
            'calendarView'  =>  ['actions'=>['show']],
            'profile'       =>  ['actions'=>['profile']],
            'constructor'   =>  ['actions'=>[null]],
            'site'          =>  ['actions'=>['parameters','plugins','mailingServices']],
        ];


        if(array_key_exists($controller_id, $allowed_array) && in_array($action_id, $allowed_array[$controller_id]['actions']) && WebUser::getUserId()){
            $storage = UsersStorageModel::model()->find(
                'users_id =:users_id AND type = '.UsersStorageModel::TYPE_BACK_AFTER_LOGIN,
                array(':users_id' => WebUser::getUserId())
            );

            if(!$storage){
                $storage = new UsersStorageModel();
                $storage->setAttribute('type', UsersStorageModel::TYPE_BACK_AFTER_LOGIN);
            }


            if($url === null){
                $url = Yii::app()->request->requestUri;
            }

            $url = urldecode($url);

            $storage->setValue(
                array(
                    'url' => $url,
                )
            );

            $storage->save();
        }
    }



    /**
     * Возвращает  информацию о последнем урле, на который перехол пользователь
     * @return null|string
     */
    public static function getUserStorageBackUrl(){
        $storage_model = UsersStorageModel::model()->find(
            'users_id =:users_id AND type = '.UsersStorageModel::TYPE_BACK_AFTER_LOGIN,
            array(':users_id' => WebUser::getUserId())
        );

        if($storage_model){
            $value = $storage_model->getValue();
            return $value['url'];
        } else {
            return '/';
        } 
    }








    public static function getStatusSetReader($module_type){
        switch($module_type){
            case HistoryMessagesModel::MODULE_TYPE_TASK :
                return false;
            case HistoryMessagesModel::MODULE_TYPE_BASE :
                $count = \HistoryMarkViewModel::model()->count('user_id='.WebUser::getUserId() . ' AND is_view is NULL');
                return (boolean)$count;
        }
        return false;
    }










}
