<?php

/**
* EditViewDeleteModel - Основной класс удаления данных модулей и связаных с ним данных
*/

class EditViewDeleteModel {
    
    // статусы удаление связаных данных модуля    
    const DELETE_DATA_RELATE_STRING = 1;
    const DELETE_SM = 2;
    const DELETE_CM_TEMPLATE = 3;

    private $_validate;
    private $_this_template = null;
    private $_logging_remove = true;
    private $_status;
    private $_make_loggin = true;
    private $_deleted_id_list = array();

    private $_check_advanced_access = true;
    private $_criteria;

    // используются при контроле удаления задач операторов
    private $_delete_tasks_all = false;
    private $_count_rows = null;
    private $_count_rows_skip = null;

    private $_forbidden_to_removed = array();
     



    public static function getInstance(){
        return new self();
    }



    public function setDeleteTasksAll($delete_tasks_all){
        $this->_delete_tasks_all = $delete_tasks_all;
        return $this;
    }


    private function addCountRowsSkip(){
        if($this->_count_rows_skip === null){
            $this->_count_rows_skip = 1;
            return;
        }

        $this->_count_rows_skip++;
    }


    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }


    public function setValidateModel($validate_model){
        $this->_validate = $validate_model;
        return $this;       
    } 
    

    public function getValidateModel(){
        return $this->_validate;
    }


    public function setLoggingRemove($logging_remove){
        $this->_logging_remove = $logging_remove;
        return $this;
    }


    public function setMakeLoggin($make_loggin){
        $this->_make_loggin = $make_loggin;
        return $this;
    }


    public function setCriteria($criteria){
        $this->_criteria = $criteria;
        return $this;
    }


    public function setCheckAdvancedAccess($check_advanced_access){
        $this->_check_advanced_access = $check_advanced_access;
        return $this;
    }

    public function getMessageForbiddenToRemoved(){
        $html = '';
        $validate = new Validate();

        if($this->_count_rows !== null && $this->_count_rows_skip !== null){
            if($this->_count_rows == $this->_count_rows_skip){
                $validate->addValidateResult('w', Yii::t('messages', 'Tasks associated with the processes are only removed from the process'));
            }

            if($this->_count_rows > $this->_count_rows_skip){
                $validate->addValidateResult('w', Yii::t('messages', 'Tasks with the process are not removed'));
            }
        }

        if(!empty($this->_forbidden_to_removed)){
            foreach($this->_forbidden_to_removed as $key => $value){
                $validate->addValidateResult('w', Yii::t('messages', 'Remove "{s}" prohibited', array('{s}' => $value)));
            }
        }

        if($validate->beMessages()){
            $html = $validate->getValidateResultHtml();
        }

        return $html;
    }




    public function delete(){
        \QueryDeleteModel::getInstance()
            ->executeAllData()
            ->clearDataModels();

        return $this;
    }




    public function getStatus(){
        return (boolean)$this->_status;
    }



    public function getResult(){
        return [
            'status' => $this->getStatus(),
            'deleted_id_list' => $this->_deleted_id_list,
            'messages' => $this->getMessageForbiddenToRemoved(),
        ];
    }


    /**
     * удаление данных модуля одной строки
     */
    public function prepare($copy_id, $id_list, $delete_where_this_template = null, $set_count_rows = true, $is_recurse = false){
        if($this->_check_advanced_access && Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $copy_id) == false){
            return $this;
        }

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName'=> $extension_copy->getTableName(null, false)
        );

        $edit_view_model = EditViewModel::modelR($alias, $dinamic_params, true);
        // основные данные
        $schema_parser = $extension_copy->getSchemaParse();
        if($id_list === 'all'){
            $id_list = array();
            if($this->_criteria){
                $edit_view_model->getDbCriteria()->mergeWith($this->_criteria);
            }
            if($extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS){
                $edit_view_model = $edit_view_model->scopesWithOutBplOperation()->findAll();
            } else {
                $edit_view_model = $edit_view_model->findAll();
            }
            if(!empty($edit_view_model)){
                $key_field_name = $extension_copy->prefix_name . '_id';
                foreach($edit_view_model as $model_data){
                    $id_list[] = $model_data[$key_field_name];
                }
            }

        } else {
            $edit_view_model = $edit_view_model->findAllByPk($id_list);
        }



        if(!empty($edit_view_model)){
            $delete_relate = false;

            if($set_count_rows){
                $this->_count_rows = count($edit_view_model);
            }
            // обходим данные по одному
            foreach($edit_view_model as $edit_view_data){

                // пропускаем задачи, созданые из проектов
                if($copy_id == \ExtensionCopyModel::MODULE_TASKS && $this->_delete_tasks_all == false){
                    if($edit_view_data->is_bpm_operation !== null){
                        $this->addCountRowsSkip();
                        continue;
                    }
                }

                // проверка, если $delete_where_this_template  - удаляем только данные с таким атрибутом
                if($delete_where_this_template !== null){
                    if($edit_view_data->this_template != $delete_where_this_template) continue;
                }

                // проверка на доступ на удаления записи (allowed_to_removed == true)
                if($copy_id == ExtensionCopyModel::MODULE_USERS || $copy_id == ExtensionCopyModel::MODULE_STAFF){
                    if((boolean)$edit_view_data->allowed_to_removed == false){
                        unset($id_list[array_search($edit_view_data->getPrimaryKey(), $id_list)]);
                        $this->_forbidden_to_removed[$edit_view_data->getPrimaryKey()] = $edit_view_data->getModuleTitle($extension_copy, false);
                        continue;
                    }
                }

                $edit_view_data
                    ->setExtensionCopy($extension_copy)
                    ->setElementSchema($schema_parser)
                    ->setLoggingRemove($this->_logging_remove);


                // запись истории
                if($this->_logging_remove == true){

                    if($copy_id == ExtensionCopyModel::MODULE_PARTICIPANT){// если модуль участников
                        if($edit_view_data->ug_type == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                            ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_USERS)->getModule(false);
                            $module_data_title = UsersModel::model()->findByPk($edit_view_data->ug_id)->getFullName();
                        } else{
                            $module_data_title = 'group'; // !!!!!!! группа - на будущее
                        }
                    } else {
                        $module_data_title = $edit_view_data->getModuleTitle();
                    }
                    if($this->_make_loggin){
                        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
                        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(array('copy_id' => $copy_id, 'card_id' => $edit_view_data->getPrimaryKey()));
                        if(!empty($comment)) $comment = '</br>' . $comment;

                        History::getInstance()->addToHistory(
                            HistoryMessagesModel::MT_DELETED,
                            $copy_id,
                            $edit_view_data->getPrimaryKey(),
                            array(
                                '{module_data_title}' => $module_data_title,
                                '{user_id}' => WebUser::getUserId(),
                                '{comment}' =>  (!empty($comment) ? $comment : ''),
                            )
                            //,
                            //false,
                            //true
                        );
                    }

                }


                    // Добавляем данные в массив для удаления
                $d = $edit_view_data->deletePrepare();

                if($d){
                    if($copy_id == ExtensionCopyModel::MODULE_PARTICIPANT){
                        ParticipantModel::deleteParticipantsFromChildrenModules(
                                                        $edit_view_data->getAttribute('copy_id'),
                                                        $edit_view_data->getAttribute('data_id'),
                                                        $edit_view_data->getAttribute('ug_id'),
                                                        true
                                                    );
                    }

                    // ToDo list
                    /*
                    if($copy_id == ExtensionCopyModel::MODULE_TASKS){
                        $this->clearTodoList($copy_id, $edit_view_data->todo_list, true);
                    }
                    */

                    if($is_recurse == false){
                        $this->_deleted_id_list[] = $edit_view_data->getPrimaryKey();
                    }

                    $delete_relate = true;
                }
            }



            // удаление других связаных данных
            if($delete_relate && !empty($id_list)){
                // связаные типы данных: relate, sub_module, relate_string
                $this->deleteRelate($extension_copy, $id_list);

                // уведомления активности
                $this->deleteActivityMessages($extension_copy, $id_list, true);

                //соpтировка в processView
                $this->deletePrepareProcessViewSorting($extension_copy, $id_list);

                //история
                $this->deletePrepareHistory($extension_copy, $id_list);

                // если  модуль Сотрудники
                if($copy_id == ExtensionCopyModel::MODULE_USERS || $copy_id == ExtensionCopyModel::MODULE_STAFF){
                    $this->deletePrepareProcessViewSorting($extension_copy, $id_list, true);
                    $this->deletePrepareParticipant($id_list);
                    $this->deletePrepareUsersRoles($id_list);
                }
            }
        }

        $this->_status = true;
        return $this;
    }





    
    /**
     * связаные типы данных: relate, sub_module, relate_string
     */ 
    private function deleteRelate($extension_copy, $id_list){
        $relate_module_tables = $this->getRelateModuleTables($extension_copy->copy_id); 
        $element_relate_params = SchemaOperation::getInstance()->getElementsRelateParams($extension_copy->getSchemaParse()); // все поля типов Связь

        if(!empty($relate_module_tables)){
            $relate_table = array();
            foreach($relate_module_tables as $module_table){
                if(in_array($module_table->table_name, $relate_table)) continue;
                $relate_params = $this->relateParamsByCopyId($extension_copy->copy_id, $module_table, $element_relate_params);
                
                //если связь по полю relate_string - удаляем данные+связь
                if($allow_status = $this->allowDeleteRelateData($extension_copy->copy_id, $relate_params)){

                    $delete_where_this_template = null;
                    if($allow_status == self::DELETE_CM_TEMPLATE){
                        if($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                            $delete_where_this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM;
                        } else {
                            //удаляем только связи
                            \QueryDeleteModel::getInstance()
                                    ->setDeleteModelParams($module_table->table_name, \QueryDeleteModel::D_TYPE_DATA, array('table_name' => $module_table->table_name, 'primary_field_name' => $extension_copy->prefix_name . '_id'))
                                    ->appendValues($module_table->table_name, \QueryDeleteModel::D_TYPE_DATA, $id_list);

                            continue;
                        }
                    } 

                    
                    $parent_field_name = $module_table->parent_field_name;
                    $relate_field_name = $module_table->relate_field_name;
                    if($extension_copy->copy_id == $module_table->relate_copy_id){
                        $parent_field_name = $module_table->relate_field_name;
                        $relate_field_name = $module_table->parent_field_name;
                    }

                    $relate_id_list = DataModel::getInstance()
                                                    ->setFrom('{{'.$module_table->table_name.'}}')
                                                    ->setWhere($parent_field_name . ' in (' . implode(',', $id_list) . ')')
                                                    ->findAll();
                    if(!empty($relate_id_list)){
                        $relate_id_list = array_keys(CHtml::listData($relate_id_list, $relate_field_name, ''));
                    }

                    // рекурсивно удаляем данные
                    $this->prepare((integer)$relate_params['relate_module_copy_id'], $relate_id_list, $delete_where_this_template, false, true);

                } else {
                    //удаляем только связи
                    \QueryDeleteModel::getInstance()
                            ->setDeleteModelParams($module_table->table_name, \QueryDeleteModel::D_TYPE_DATA, array('table_name' => $module_table->table_name, 'primary_field_name' => $extension_copy->prefix_name . '_id'))
                            ->appendValues($module_table->table_name, \QueryDeleteModel::D_TYPE_DATA, $id_list);
                }

                $relate_table[] = $module_table->table_name;
            }
        }        
    }









    /**
     * deletePrepareProcessViewSorting - удаляем данные о сортировке сущностей в ProcessView
     * $param bool $remove_panels - Используется при удалении пользователя. Тогда удаляем панель и карточки
     */
    private function deletePrepareProcessViewSorting($extension_copy, $data_id_list, $remove_panels = false){
        if($remove_panels){
            $table_name = (new \ProcessViewSortingListModel())->getTableName(false);
            $primary_field_name = 'sorting_list_id';
        } else {
            $table_name = (new \ProcessViewSortingCardsModel())->getTableName(false);
            $primary_field_name = 'sorting_cards_id';
        }


        if($remove_panels){
            $data_model = new DataModel();
            $data_model
                ->setSelect($primary_field_name)
                ->setFrom('{{' . $table_name . '}}')
                ->andWhere('users_id in ('.addslashes(implode(',', $data_id_list)).')');
        } else {
            $table_name_join = (new \ProcessViewSortingListModel())->getTableName(false);
            $data_model = new DataModel();
            $data_model
                ->setSelect($primary_field_name)
                ->setFrom('{{' . $table_name . '}}')
                ->join($table_name_join, '{{'.$table_name_join. '}}.' . 'sorting_list_id = {{' . $table_name . '}}.' . 'sorting_list_id')
                ->andWhere(
                    '(copy_id=:copy_id AND data_id in('.addslashes(implode(',', $data_id_list)).')) OR (pci=:pci AND pdi in('.addslashes(implode(',', $data_id_list)).'))',
                    array(':copy_id' => $extension_copy->copy_id, ':pci' => $extension_copy->copy_id)
                );
        }

        $data_sorting = $data_model->findAll();

        if(empty($data_sorting)) return;

        foreach($data_sorting as $value){
            \QueryDeleteModel::getInstance()
                ->setDeleteModelParams($table_name . '_1', \QueryDeleteModel::D_TYPE_DATA, array('table_name' => $table_name, 'primary_field_name' => $primary_field_name))
                ->appendValues($table_name . '_1', \QueryDeleteModel::D_TYPE_DATA, $value[$primary_field_name]);
        }
    }





    /**
     * deletePrepareHistory
     */
    private function deletePrepareHistory($extension_copy, $id_list){
        $copy_id_list = array(
            ExtensionCopyModel::MODULE_PROCESS,
        );

        if(!in_array($extension_copy->copy_id, $copy_id_list)){
            return;
        }
        if(empty($id_list)){
            return;
        }

        $table_name = 'history';
        $history_messages_index = implode(',', HistoryMessagesModel::getMTListForProcess());
        $id_list = implode(',', $id_list);

        $delete_params = array(
            'table_name' => $table_name,
            'condition' => 'history_messages_index in('.$history_messages_index.') AND copy_id = '.$extension_copy->copy_id.' AND data_id in('.$id_list.')',
        );

        \QueryDeleteModel::getInstance()
            ->setDeleteModelParams($table_name, \QueryDeleteModel::D_TYPE_DATA, $delete_params);
    }





    /**
     * deletePrepareParticipant
     */
    private function deletePrepareParticipant($id_list){
        $table_name = 'participant';

        $data_model = new DataModel();
        $data_model
            ->setSelect('participant_id')
            ->setFrom('{{'.$table_name.'}}')
            ->andWhere('ug_type="'.ParticipantModel::PARTICIPANT_UG_TYPE_USER.'" AND ug_id in ('.implode(',', $id_list).')');

        $data = $data_model->findAll();

        if(!empty($data)){
            foreach($data as $value){
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams($table_name, \QueryDeleteModel::D_TYPE_DATA, array('table_name' => $table_name, 'primary_field_name' => 'participant_id'))
                    ->appendValues($table_name, \QueryDeleteModel::D_TYPE_DATA, $value['participant_id']);
            }
        }
    }






    /**
     * deletePrepareUsersRoles
     */
    private function deletePrepareUsersRoles($id_list){
        $table_name = 'users_roles';

        $data_model = new DataModel();
        $data_model
            ->setSelect('id')
            ->setFrom('{{'.$table_name.'}}')
            ->andWhere('users_id in('.implode(',', $id_list).')');

        $data = $data_model->findAll();

        if(!empty($data)){
            foreach($data as $value){
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams($table_name, \QueryDeleteModel::D_TYPE_DATA, array('table_name' => $table_name, 'primary_field_name' => 'id'))
                    ->appendValues($table_name, \QueryDeleteModel::D_TYPE_DATA, $value['id']);
            }
        }
    }





    /**
     * проверка, можно ли удалять данные связаного модуля
     */
    public function allowDeleteRelateData($parent_copy_id, $relate_params){
        $result = false; 
        
        if(empty($relate_params)) return $result;

        //1 - по полю "название"
        if($relate_params['type'] == 'relate_string' && (boolean)$relate_params['is_primary'] == true) return self::DELETE_DATA_RELATE_STRING;
        //2 - поиск обратной связи, то есть тип связи МКО... 
        if($relate_params['type'] == 'sub_module'){
            $relate_one = ModuleTablesModel::model()->findAll(array(
                                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                            'params' => array(
                                                                ':copy_id' => $relate_params['relate_module_copy_id'],
                                                                ':relate_copy_id' => $parent_copy_id,
                                                            )));
            if(!empty($relate_one)) return self::DELETE_SM;
        }
        //3 - СМ-шаблоны
        if($relate_params['type'] == 'sub_module' && $relate_params['relate_module_template'] == true) return self::DELETE_CM_TEMPLATE;

        
        return $result;        
    }
    
    
    
    /**
     * Возвращает список связаных таблиц 
     */
    private function getRelateModuleTables($copy_id){
        return ModuleTablesModel::model()->findAll(array(
                                                    'condition' => '(copy_id=:copy_id OR relate_copy_id=:copy_id) AND `type` in ("relate_module_one", "relate_module_many")',
                                                    'params' => array(':copy_id' => $copy_id)));
        
        
    }
    

    
    /**
     * возвращает параметы схемы поля по copy_id 
     */
    private function relateParamsByCopyId($copy_id, $module_table, $element_relate_params){
        $result = array();
        if(empty($element_relate_params)) return $result;
        if($module_table->copy_id != $copy_id)
            $relate_copy_id = $module_table->copy_id;
        elseif($module_table->relate_copy_id != $copy_id)
            $relate_copy_id = $module_table->relate_copy_id;
        
        foreach($element_relate_params as $params){
            if($params['relate_module_copy_id'] == $relate_copy_id){
                $result = $params;
                break;
            }
        }
        return $result;
    
        
        
        
    }
    
    

    /**
     *  очистка ТОДО списка от "пустых" значений  
     */
    /*
    public function clearTodoList($copy_id){
        if($copy_id != \ExtensionCopyModel::MODULE_TASKS) return;

        $data_model = new DataModel();
        $data = $data_model
            ->setText('
                    SELECT todo_list_id
                    FROM {{tasks_todo_list}}
                    WHERE
                      not exists (SELECT * FROM {{tasks}} WHERE {{tasks_todo_list}}.todo_list_id = {{tasks}}.todo_list) AND
                      not exists (
                        SELECT *
                        FROM ' . ProcessViewSortingListModel::getInstance()->getTableName() . ' as t1
                        WHERE
                            t1.copy_id = ' . $copy_id . ' AND
                            t1.pci is not NULL AND
                            t1.pdi is not NULL AND
                            t1.unique_index = md5(CONCAT_WS("",todo_list_id))
                            )
                      ')
            ->findAll();

        if(!empty($data)){
            foreach($data as $value){
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('tasks_todo_list', \QueryDeleteModel::D_TYPE_DATA, array('table_name' => 'tasks_todo_list', 'primary_field_name' => 'todo_list_id'))
                    ->appendValues('tasks_todo_list', \QueryDeleteModel::D_TYPE_DATA, $value['todo_list_id']);
            }

            \QueryDeleteModel::getInstance()->executeAllData();
        }
     }
    */
    
    
    
    
    
    
    /**
     * удаление всех связаных данных модуля. Используется в конструкторе при удалении модуля
     */
    public function deleteAll(array $copy_id_list){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        foreach($copy_id_list as $copy_id){
            $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
            if((boolean)$extension_copy->destroy == false){
                $this->_validate->addValidateResult('e', Yii::t('messages', 'Module "{s}" to remove prohibited', array('{s}' => $extension_copy->title)));
                continue;
            }

            // удаляем привязаные данные к шаблонам
            $this->deleteRelateTemplateCM($copy_id);
            // удаляем связаные таблицы и элементы из связаных модулей
            $this->deleteRelateElements($copy_id);

            $tables = ModuleTablesModel::model()->findAll(array('condition'=>'copy_id=:copy_id', 'params'=>array(':copy_id'=>$copy_id)));
            if(!empty($tables)){
                $connection = Yii::app()->db;
                $command = $connection->createCommand();
                foreach($tables as $table){
                    //файлы
                    FileOperations::getInstance()->deleteAllFilesByTableModel($table);
                    $command->setText('DROP TABLE IF EXISTS {{' . $table->table_name . '}}')->execute();
                }
            }
            //соpтировка в processView
            (new ProcessViewSortingListModel())->flushPanelAll($copy_id);

            //Удаление участников
            $this->deleteParticipants($copy_id);

            //связанные процессы в Процессах
            \DataModel::getInstance()->Update('{{process}}', array('related_module' =>null), 'related_module=' . $copy_id);
            //Активность (уведомления)
            $this->deleteActivityMessages($extension_copy);
            //логи истории пользователей
            $this->deleteUserStorage($copy_id);
            // история пользователей
            $this->deleteHistory($copy_id);
            // user_filters
            $this->deleteUsersFilters();

            // права
            PermissionModel::getInstance()->deletePermission($copy_id, Access::ACCESS_TYPE_MODULE);
            // связи в module_tabled
            ModuleTablesModel::model()->deleteAll('copy_id = :copy_id', array(':copy_id'=>$copy_id));
            // this Module
            ExtensionCopyModel::model()->deleteByPk($copy_id);
            $this->_validate->addValidateResult('i', Yii::t('messages', 'Module "{s}" removed', array('{s}'=>$extension_copy->title)));
        }
    }


    /**
     * Удаление участников
     */
    private function deleteParticipants($copy_id){
        //участники, ответственные
        ParticipantModel::model()->deleteAll('copy_id=:copy_id', array(':copy_id' => $copy_id));
        //email-участники
        ParticipantEmailModel::model()->deleteAll('copy_id=:copy_id', array(':copy_id' => $copy_id));
        //участники-константы  из Процессов
        ParticipantModel::model()->deleteAll(
                            'copy_id=:copy_id AND data_id in (SELECT process_id FROM {{process}} WHERE related_module=:related_module) AND ug_id=:ug_id AND ug_type=:ug_type',
                            array(
                                ':ug_id' => ParticipantConstModel::TC_RELATE_RESPONSIBLE,
                                ':ug_type' => ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
                                ':copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
                                ':related_module' => $copy_id
                            )
                        );
    }



    
    /**
     * удаляем уведомления блока Активность
     */
    private function deleteActivityMessages($extension_copy, $id_list = null, $only_prepare = false){
        if($only_prepare == false){
            if($id_list !== null){
                if(!empty($id_list))
                    $activity_model_list = ActivityMessagesModel::model()->FindAll('copy_id=:copy_id AND data_id in(' . addslashes(implode(',', $id_list)) . ')', array(':copy_id' => $extension_copy->copy_id));
            } else{
                $activity_model_list = ActivityMessagesModel::model()->FindAll('copy_id=:copy_id', array(':copy_id' => $extension_copy->copy_id));
            }

            if(!empty($activity_model_list))
                foreach($activity_model_list as $activity_model){
                    $activity_model->Delete();
                }
        } else {
            \ActivityMessagesModel::deletePrepareActivityMessages($extension_copy, $id_list);
        }


        return $this;
    }
    
    
    
    /**
     * удаляем логи истории пользователей
     */
    private function deleteUserStorage($copy_id){
        $user_storage = new UsersStorageModel();
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_FILTER . ' AND storage_index = "'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_SORTING . ' AND storage_index like "processView_'.$copy_id.'%"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_SORTING . ' AND storage_index like "listView_'.$copy_id.'%"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_PAGINATION . ' AND storage_index = "listView_'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_TH_HIDE . ' AND storage_index = "listView_'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_TH_HIDE . ' AND storage_index LIKE "report_'.$copy_id.'%"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_TH_WIDTH . ' AND storage_index = "listView_'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_TH_WIDTH . ' AND storage_index LIKE "report_'.$copy_id.'%"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_PAGE_PARAMS . ' AND storage_index = "'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_PV_SORTING_PANEL . ' AND storage_index = "'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_EV_BLOCK_DISPLAY . ' AND storage_index = "editView_'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_PV_SECOND_FIELDS . ' AND storage_index like "processView_'.$copy_id.'%"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_BACK_AFTER_LOGIN);
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_MENU_COUNT . ' AND storage_index = "1"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_LIST_TH_HIDE_FIRST_TIME . ' AND storage_index = "listView_'.$copy_id.'"');
        $user_storage->deleteAll('type=' . UsersStorageModel::TYPE_FINISHED_OBJECT . ' AND storage_index = "'.$copy_id.'"');
    }



    /**
     * deletePrepareHistory
     */
    private function deleteHistory($copy_id){
        $copy_id_list = array(
            ExtensionCopyModel::MODULE_PROCESS,
        );

        if(!in_array($copy_id, $copy_id_list)){
            return;
        }

        $history_messages_index = implode(',', HistoryMessagesModel::getMTListForProcess());

        \HistoryModel::model()->deleteAll('history_messages_index in ('.$history_messages_index.') AND copy_id = '.$copy_id);
    }



    /**
     * deleteUsersFilters
     */
    private function deleteUsersFilters(){
        ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_REPORTS)->getModule(false);

        $filter_model_list = \Reports\models\ReportsFilterModel::model()->findAll();
        if($filter_model_list == false){
            return;
        }

        foreach($filter_model_list as $filter_model){
            $check = true;

            $params = $filter_model->getParams();
            if($params == false){
                $filter_model->delete();
                continue;
            }

            foreach($params as $param){
                if(!array_key_exists('copy_id', $param) || !$param['copy_id']){
                    $filter_model->delete();
                    $check = false;
                    break;
                } else {
                    $extension_copy = ExtensionCopyModel::model()->findByPk($param['copy_id']);

                    if($extension_copy == false){
                        $filter_model->delete();
                        $check = false;
                        break;
                    }
                }
            }

            if($check == false){
                continue;
            }
        }
    }



    
    /**
    * Удаление елементов связей в других модулях
    */ 
    private function deleteRelateElements($copy_id){
        $modules = ModuleTablesModel::model()->findAll(array('condition'=>'relate_copy_id=:relate_copy_id', 'params'=>array(':relate_copy_id'=>$copy_id)));
        if(empty($modules)) return;

        foreach($modules as $module){
            $extension_copy = $module->extensionCopy; 
            $schema = $extension_copy->getSchema(true);

            // delete sdm
            if($module->type == 'relate_module_one'){
                $schema = SchemaOperation::getInstance()->getSchemaWithOutRelate($schema, $copy_id);
            }
            // delete sm
            if($module->type == 'relate_module_many'){
                $schema = SchemaOperation::getInstance()->getSchemaWithOutSubModule($schema, $copy_id);
            }

            // save
            $extension_copy->schema = json_encode($schema);
            $extension_copy->update();
            
            //delete relate table
            $command = Yii::app()->db->createCommand();
            $command->setText('DROP TABLE IF EXISTS {{' . $module->table_name . '}}')->execute();
            $module->delete();
        }
    }

    
    
    
    /**
     *  удaляем данные из связаного модуля, если this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM
     */
     private function deleteRelateTemplateCM($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $sub_modules = SchemaOperation::getSubModules($extension_copy->getSchemaParse());
        if(empty($sub_modules)) return;

        foreach($sub_modules as $sub_module){
            $this->deleteTemplateCM($extension_copy, $sub_module['sub_module']['params']);
        }
     }
    
    
    

    /**
     * удяляем данные связаного модуля, если this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM
     */         
    public function deleteTemplateCM($extension_copy, $sub_module_params, $only_prepare = false){
        if((boolean)$sub_module_params['relate_module_template'] == false) return;
        
        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($sub_module_params['relate_module_copy_id']);

        // данные родителя
        $data_model = DataModel::getInstance()
                            ->setSelect($extension_copy->prefix_name . '_id')
                            ->setFrom($extension_copy->getTableName());
        $data_model = $data_model->findAll();
        $id_list_parent = array();
        
        if(empty($data_model)) return;
        foreach($data_model as $value)
            $id_list_parent[] = $value[$extension_copy->prefix_name . '_id'];

        // связающие данные
        $table_module = ModuleTablesModel::model()->find(array(
                                                            'condition'=>'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_many"',
                                                            'params' => array(
                                                                            ':copy_id'=>$extension_copy->copy_id,
                                                                            ':relate_copy_id'=>$relate_extension_copy->copy_id)));
        // данные родителя
        $data_model = DataModel::getInstance()
                            ->setFrom('{{' . $table_module->table_name . '}}')
                            ->setWhere($table_module->parent_field_name . ' in (' . implode(',', $id_list_parent) . ')');
        $data_model = $data_model->findAll();

        if(empty($data_model)) return;
        $id_list_relate = array();
        foreach($data_model as $value)
            $id_list_relate[] = $value[$table_module->relate_field_name];

        // данные связаного модуля
        $data_model = DataModel::getInstance()
                            ->setSelect($relate_extension_copy->prefix_name . '_id')
                            ->setFrom($relate_extension_copy->getTableName())
                            ->andWhere($relate_extension_copy->prefix_name . '_id' . ' in (' . implode(',', $id_list_relate) . ') AND this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '"');
        $data_model = $data_model->findAll();
        
        if(!empty($data_model)){
            foreach($data_model as $value){
                $id_list[] = $value[$relate_extension_copy->prefix_name . '_id'];
            }
            if($only_prepare == false){
                EditViewDeleteModel::getInstance()->delete($relate_extension_copy->copy_id, $id_list);
            } else {
                ///------------
            }
        }
     }
         
    
    
    
} 
