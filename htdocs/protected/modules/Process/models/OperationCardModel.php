<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationCardModel extends \Process\components\OperationModel{

    const COPY_ID = 'copy_id'; // ссылка на модуль
    const CARD_ID = 'card_id'; // ссылка на карточку


    public $_elements_params = array();
    protected $_relate_copy_id;

    private static $_copy_id_changed;
    private static $_data_id_changed;


    protected function setTitle(){
    }


    public function getRelateCopyId(){
        return $this->_relate_copy_id;
    }


    public static function setChangedCard($copy_id, $data_id){
        self::$_copy_id_changed = $copy_id;
        self::$_data_id_changed = $data_id;
    }




    public function buildElementsParams(){
        if(empty($this->_operations_model)) return;

        $schema = $this->_operations_model->getSchema();
        if(empty($schema)) return;
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        foreach($schema as $element){
            if(isset($element['is_element']) && $element['is_element'] === false) continue;
            $this->_elements_params[$element['type']] = $this->getElementHtml($element);
        }

        return $this;
    }






    /**
     * getCardBStatus - возвращает статус карточки
     * @return bool
     */
    public function getCardBStatus($task_id = null){
        if(empty($task_id)){
            $task_id = $this->getIdCardFromSchema();
        }

        $result = null;
        if(empty($task_id)) return $result;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());

        $status_params = $extension_copy->getStatusField();

        $data = \DataModel::getInstance()
                ->setSelect($status_params['params']['name'])
                ->setFrom($extension_copy->getTableName())
                ->setWhere($extension_copy->prefix_name . '_id=' . $task_id)
                ->findRow();

        if(!empty($data) && $data[$status_params['params']['name']]){
            $result = $data[$status_params['params']['name']];
        }

        return $result;
    }







    /**
     * обновляет статус карточки
     */
    public function updateCardBStatus($card_id, $b_status){
        if(empty($card_id)) $card_id = $this->getIdCardFromSchema();
        if(empty($card_id)) return $this;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());

        if(self::$_copy_id_changed == $extension_copy->copy_id && self::$_data_id_changed == $card_id){
            return $this;
        }

        $status_params = $extension_copy->getStatusField();

        $data_model = new \DataModel();
        $data_model
            ->setText('UPDATE ' . $extension_copy->getTableName() . ' SET '. $status_params['params']['name'] .' = "'. $b_status .'" WHERE ' . $extension_copy->prefix_name . '_id=' . $card_id)
            ->execute();

        return $this;
    }






    /**
     * editViewSaveDefault - сохраняет дефолтную карточку
     */
    public function editViewSaveDefault($params){
        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());
        $edit_data = array(
            'id' => null,
            'parent_copy_id' => null,
            'parent_data_id' => null,
            'this_template' => 0,
            'relate_template' => 0,
            'primary_entities' => array(
                'primary_pci' => null,
                'primary_pdi' => null,
            ),
            'EditViewModel' => array(
                'module_title' => $this->getModuleTitle(),
            ),

        );

        $edit_model = $this->editViewSave($edit_data, true);
        if(!empty($edit_model)){
            // set schema
            $card_id = $edit_model->getId();

            // сохранение связи карточки модуля с процессом
            $this->saveRelateCardAndProcess($card_id);

            // participant
            $this->saveParticipantDefault(
                        $extension_copy->copy_id,
                        $edit_model->getId(),
                        $params['participant']['ug_id'],
                        $params['participant']['ug_type'],
                        $params['participant']['flag']
                    );

            $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, $this->_operations_model->getSchema(), self::COPY_ID, $this->getRelateCopyId(), false);
            $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, json_decode($schema, true), self::CARD_ID, $card_id, false);

            $this->_operations_model->schema = $schema;
            $this->_operations_model->copy_id = $this->getRelateCopyId();
            $this->_operations_model->card_id = $card_id;
        }

        return $this;
    }







    /**
     * getEditViewDataSave - сохраняет данные EditView
     */
    public function editViewSave($edit_data, $ignore_required = true){
        $this->buildElementsParams();

        $edit_action_model = new \EditViewActionModel($this->getRelateCopyId()); //MODULE
        $edit_action_model
            ->setEditData($edit_data)
            ->createEditViewModel();

        $is_new_record = $edit_action_model->getIsNewRecord();

        if($is_new_record){
            $edit_data['EditViewModel']['is_bpm_operation'] = "1";
            $edit_action_model->setMakeLogging(false);
        } else{
            if($this->_operations_model->getMode() == OperationsModel::MODE_CONSTRUCTOR){
                $edit_action_model->setMakeLogging(false);
            }
        }

        $edit_action_model
            ->setEditData($edit_data);

        $this->deleteSomeRules($edit_action_model, $ignore_required);

        $edit_action_model
            ->save(false);

        if($edit_action_model->getStatus() == \EditViewActionModel::STATUS_SAVE){
            /*
            if($is_new_record){
                $this->updateCardBStatus($edit_action_model->getId(), static::B_STATUS_CREATED);
                $edit_action_model->refresh();
            }
            */
            return $edit_action_model;
        } else {
            $edit_action_model
                ->setEditViewBuilder((new \Process\extensions\ElementMaster\EditViewBuilderForCard())->setOperationsCardModel($this))
                ->prepareHtmlData();

            return $edit_action_model;
        }
    }










    /**
     * getEditViewDataForShow - возвращает данные для EditView
     */
    public function getEditViewModel($id = null){
        if($id === null && $id !== false){
            if(empty($this->_operations_model)){
                return false;
            }

            $id = $this->getIdCardFromSchema();
        }

        if($id == false){
            return;
        }

        $edit_data = array(
            'id' => $id,
            'pci' => null,
            'pdi' => null,
            'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
            'relate_template' => '0',
            'template_data_id' => null,
        );

        $edit_model = new \EditViewActionModel($this->getRelateCopyId());
        $edit_model
            ->setEditData($edit_data)
            ->createEditViewModel();

        if($edit_model->getDataNotFound() === true){
            return;
        }

        return $edit_model->getEditModel();

    }








    /**
     * getEditViewDataForShow - возвращает данные для EditView
     */
    public function getEditViewDataForShow($id = null){
        if($id === null && $id !== false){
            $validate = new \Validate();

            if(empty($this->_operations_model)){
                $validate->addValidateResult('e', \Yii::t('messages', 'Not defined data parameters'));
                return array(
                    'status' => 'error',
                    'messages' => $validate->getValidateResultHtml(),
                );
            }

            $id = $this->getIdCardFromSchema();
        }

        if(empty($id)){
            $id = null;
            $this->updateValueInOperation(null, null);
            $this->_operations_model->setMode(OperationsModel::MODE_CONSTRUCTOR);
            $this->_operations_model->refresh();
        }

        $edit_data = array(
            'id' => $id,
            'pci' => null,
            'pdi' => null,
            'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
            'relate_template' => '0',
            'template_data_id' => null,
        );

        $this->buildElementsParams();

        $edit_model = new \EditViewActionModel($this->getRelateCopyId());
        $edit_model
            ->setEditData($edit_data)
            ->createEditViewModel();

        if($edit_model->getDataNotFound() === true){
            return $this->getEditViewDataForShow(false);
        }

        $edit_model
            ->setEditViewBuilder((new \Process\extensions\ElementMaster\EditViewBuilderForCard())->setOperationsCardModel($this))
            ->checkSubscriptionAccess();

        if($this->_operations_model->getMode() != OperationsModel::MODE_CONSTRUCTOR){
            $edit_model->markHistoryIsView();
        }

        $edit_model->prepareHtmlData();

        if($edit_model->getStatus() == \EditViewActionModel::STATUS_DATA){
            return $edit_model->getHtmlData();
        }
    }









    private function getModuleTitle(){
        switch($this->_operations_model->element_name){
            case OperationsModel::ELEMENT_TASK:
                return \Yii::t('ProcessModule.base', 'Task');
            case OperationsModel::ELEMENT_AGREETMENT:
                return \Yii::t('ProcessModule.base', 'Agreetment');
            case OperationsModel::ELEMENT_NOTIFICATION:
                return \Yii::t('ProcessModule.base', 'Notification');
        }
    }







    /**
     * сохранение связи карточки модуля с процессом
     */
    private function saveRelateCardAndProcess($card_id){
        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());
        $relates = \SchemaOperation::getRelates($extension_copy->getSchemaParse());

        if(empty($relates)) return;
        foreach($relates as $relate){
            if($relate['params']['relate_module_copy_id'] != \ExtensionCopyModel::MODULE_PROCESS) continue;
            $relate_table = \ModuleTablesModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                'params' => array(
                    ':copy_id' => $this->getRelateCopyId(),
                    ':relate_copy_id' => \ExtensionCopyModel::MODULE_PROCESS)));
            if(empty($relate_table)) continue;

            \DataModel::getInstance()->Insert('{{'.$relate_table->table_name.'}}', array(
                $relate_table->parent_field_name => $card_id,
                $relate_table->relate_field_name => ProcessModel::getInstance()->process_id,
            ));
        }
    }






    /**
     * saveParticipantDefault - сохраняет ответсвенного для дефолтной задачи
     */
    private function saveParticipantDefault($copy_id, $data_id, $ug_id, $ug_type, $flag){
        $responsible = '1';
        if($ug_type == \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
            $responsible = '0';
        }

        $attributes = array(
            'participant_id' => null,
            'ug_id' => $ug_id,
            'ug_type' => $ug_type,
            'responsible' => $responsible,
        );
        $participant_model = new \ParticipantModel();
        $participant_model->setMyAttributes($attributes);
        $participant_model->copy_id = $copy_id;
        $participant_model->data_id = $data_id;
        $participant_model->insert();

        if($flag){
            $participant_model->insertProcessFlag($flag);
        }
    }











    /**
     * getUpdatedValueInSchema - возвращает обновленный ID карточки в схеме
     * @return json
     */
    public static function getUpdatedValueInSchema($element_name, $schema, $element_type, $value, $check_empty = true){
        OperationsModel::getOperationClassName($element_name);

        foreach($schema as &$operation){
            if($operation['type'] == $element_type){
                if($check_empty == true && !empty($operation['value'])) continue;
                $operation['value'] = $value;
            }
        }
        $schema = json_encode($schema);

        return $schema;
    }






    /**
     * updateValueInOperation - обновляет ID карточки в схеме
     */
    public function updateValueInOperation($copy_id, $card_id){
        $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, $this->_operations_model->getSchema(), self::COPY_ID, $copy_id, false);
        $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, json_decode($schema, true), self::CARD_ID, $card_id, false);

        $this->_operations_model->schema = $schema;
        $this->_operations_model->copy_id = $copy_id;
        $this->_operations_model->card_id = $card_id;
        $this->_operations_model->save();

        return $this;
    }





    /**
     * Удаляем некоторые правила валидации перед сохранение параметра оператора для Задачи
     * @param $edit_model
     */
    private function deleteSomeRules(&$edit_action_model, $ignore_required = false){
        $status_params = $edit_action_model->getExtensionCopy()->getStatusField();

        if(!empty($status_params)){
            $dinamic_params = $edit_action_model->getEditModel()->_dinamic_params;
            $dinamic_params_tmp = $dinamic_params;
            $rules_tmp = array();

            if(!empty($dinamic_params['params']['rules'])){
                foreach($dinamic_params['params']['rules'] as $rules){
                    $field_name = $rules[0];
                    if($status_params['params']['name'] == $field_name) continue; // status-button

                    if($ignore_required){
                        if($rules[1] == 'required' || $rules[1] == 'relateCheckRequired') continue;
                    }

                    $rules_tmp[] = $rules;
                }
            }

            $dinamic_params_tmp['params']['rules'] = $rules_tmp;

            $edit_action_model->getEditModel()->setDinamicParams($dinamic_params_tmp);
        }
    }









    /**
     * getIdCardFromSchema
     */
    public function getIdCardFromSchema($schema_operator = null){
        $id = null;

        if($schema_operator === null){
            $schema_operator = $this->_operations_model->getSchema();
        }

        foreach($schema_operator as $element){
            if(isset($element['type']) && $element['type'] == self::CARD_ID){
                $id = $element['value'];
                break;
            }
        }

        return $id;
    }







    /**
     * isCard
     * @param $extension_copy
     * @param $card_id
     * @return bool
     */
    private function isCard($extension_copy, $card_id){
        $count = \DataModel::getInstance()
            ->setText('SELECT * FROM ' . $extension_copy->getTableName() . ' WHERE ' . $extension_copy->prefix_name . '_id=' . $card_id . ' AND is_bpm_operation = "1"')
            ->findCount();
        if($count == true) return false;

        return true;
    }


    /**
     * moveInTask - переносим задачу в таблицу Task
     */
    protected function moveInCardRun($check = true){
        $card_id = $this->getIdCardFromSchema();
        if(empty($card_id)) return false;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());

        if($check && $this->isCard($extension_copy, $card_id)) return false;

        $data_model = new \DataModel();
        $data_model
            ->setText('UPDATE ' . $extension_copy->getTableName() . ' SET is_bpm_operation = "0" WHERE ' . $extension_copy->prefix_name . '_id=' . $card_id)
            ->execute();


        // логируем
        $this->makeHistory($card_id);

        $relate_task_id = $this->getRelateIdCardFromSchema();

        // оновляем дату для сообщений активности
        $this->updateActivityMessagesDate($card_id);
        // копия Активности
        $this->copyActivity($card_id, $relate_task_id);
    }





    /**
     * Логирует создание задачи
     * @param $edit_model
     */

    public function makeHistory($card_id, $loggin_respponsible_only = false, array $mt_list = [\HistoryMessagesModel::MT_CREATED, \HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED, \HistoryMessagesModel::MT_COMMENT_CREATED]){
        if($mt_list == false){
            return;
        }

        // делаем Задачей
        $edit_data = array(
            'id' => $card_id,
        );

        $edit_action_model = new \EditViewActionModel($this->getRelateCopyId()); //MODULE_TASKS
        $edit_action_model
            ->setEditData($edit_data)
            ->createEditViewModel();

        $edit_model = $edit_action_model->getEditModel();
        if(empty($edit_model)) return;

        $edit_model->setPotMode(\EditViewModel::POT_MODE_RUNNING);

        //MT_CREATED
        if(in_array(\HistoryMessagesModel::MT_CREATED, $mt_list)){
            $edit_model->saveHistoryNewCard(false);
        }

        //MT_RESPONSIBLE_APPOINTED
        if(in_array(\HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED, $mt_list)){
            $participant_model = \ParticipantModel::model()->find(array(
                'condition' => 'copy_id = :copy_id AND data_id = :data_id AND responsible = "1"',
                'params' => array(
                    ':copy_id' => $this->getRelateCopyId(),
                    ':data_id' => $card_id,
                )));

            if(!empty($participant_model)){
                $edit_model->saveHistoryResponsibleAppointed($this->getRelateCopyId(), $card_id, $participant_model->ug_id);
            }
        }

        //MT_COMMENT_CREATED
        if(in_array(\HistoryMessagesModel::MT_COMMENT_CREATED, $mt_list)){
            $activity_models = \ActivityMessagesModel::model()->findAll(array(
                'condition' => 'copy_id = :copy_id AND data_id = :data_id AND `status` = "asserted"',
                'params' => array(
                    ':copy_id' => $this->getRelateCopyId(),
                    ':data_id' => $card_id,
                )));

            if(!empty($activity_models)){
                foreach($activity_models as $activity_model){
                    $edit_model->saveHistoryCommentCreated($activity_model, $this->getRelateCopyId(), $card_id, $activity_model->user_create);
                }
            }
        }

        \HistoryContainerModel::save($loggin_respponsible_only);
    }




    /**
     * оновляем дату для сообщений активности
     * @return $this
     */
    protected function updateActivityMessagesDate($card_id){
        if(empty($card_id)) return $this;


        $tdc_count = \DataModel::getInstance()
            ->setFrom('{{process_am_tdc}}')
            ->andWhere('copy_id=:copy_id AND card_id=:card_id', array(':copy_id' => $this->getRelateCopyId(), ':card_id' => $card_id))
            ->findCount();

        if($tdc_count) return $this;


        $am_models = \DataModel::getInstance()
            ->setSelect('activity_messages_id')
            ->setFrom('{{activity_messages}}')
            ->andWhere('copy_id=:copy_id AND data_id=:data_id', array(':copy_id' => $this->getRelateCopyId(), ':data_id' => $card_id))
            ->findAll();

        $sql = '';
        if(!empty($am_models)){
            $date_time = new \DateTime();
            foreach($am_models as $am){
                $sql.= 'UPDATE {{activity_messages}} SET date_create = "'.$date_time->format('Y-m-d H:i:s').'", date_edit = null WHERE activity_messages_id = ' . $am['activity_messages_id'] . ';';
                $date_time->modify('+1 second');
            }
        }


        if($sql !== ''){
            \DataModel::getInstance()
                ->setText($sql)
                ->execute();
        }

        \DataModel::getInstance()->insert('{{process_am_tdc}}', array('copy_id' => $this->getRelateCopyId(), 'card_id' => $card_id));
    }




    /**
     * Копируем блок Активность
     */
    protected function copyActivity($card_id, $relate_card_id){
        if(empty($card_id) || empty($relate_card_id)) return $this;

        $activity_messages_list = \DataModel::getInstance()
            ->setSelect('{{activity_messages}}.*')
            ->setFrom('{{activity_messages}}')
            ->andWhere('copy_id = '.$this->getRelateCopyId().' AND data_id = '.$relate_card_id.' AND `status` = "asserted" AND not exists(SELECT * FROM {{process_am_copied}} WHERE activity_messages_id = {{activity_messages}}.activity_messages_id AND {{process_am_copied}}.copy_id = '. $this->getRelateCopyId() .' AND {{process_am_copied}}.card_id = ' . $card_id . ')')
            ->findAll();

        $activity_messages_id_list1 = array();
        $activity_messages_id_list2 = array();

        if(!empty($activity_messages_list)){
            foreach($activity_messages_list as $activity_model){
                $attributes = $activity_model;

                $new_activity = new \ActivityMessagesModel();
                $new_activity->setScenario('copy');
                $activity_messages_id_list1[] = $attributes['activity_messages_id'];
                $attributes['activity_messages_id'] = null;
                $attributes['data_id'] = $card_id;
                $new_activity->setMyAttributes($attributes);

                $r = $new_activity->save();
                $new_activity->refresh();
                $new_activity->refreshMetaData();

                if(!$r){
                    unset($activity_messages_id_list1[count($activity_messages_id_list1) - 1]);
                } else {
                    $activity_messages_id_list2[] = $new_activity->getPrimaryKey();
                }
            }

            $this->saveAmHistory($card_id, $activity_messages_id_list1, $relate_card_id, $activity_messages_id_list2);

        }

        return $this;
    }


    /**
     * saveAmHistory
     */
    private function saveAmHistory($card_id, $activity_messages_id_list1, $relate_card_id, $activity_messages_id_list2){
        if(!empty($activity_messages_id_list1)){
            foreach($activity_messages_id_list1 as $activity_messages_id){
                \DataModel::getInstance()->insert(
                    '{{process_am_copied}}',
                    array(
                        'activity_messages_id' => $activity_messages_id,
                        'copy_id' => $this->getRelateCopyId(),
                        'card_id' => $card_id,
                    ));
            }
        }

        if(!empty($activity_messages_id_list2)){
            foreach($activity_messages_id_list2 as $activity_messages_id){
                \DataModel::getInstance()->insert(
                    '{{process_am_copied}}',
                    array(
                        'activity_messages_id' => $activity_messages_id,
                        'copy_id' => $this->getRelateCopyId(),
                        'card_id' => $relate_card_id,
                    ));
            }
        }
    }




    /**
     * updateGeneralSchema - обновление параметров оператора в главной схеме
     * @param $params
     * @return $this
     */
    /*
    public function updateGeneralSchema($params){
        $params = $params;
        $find_params = array(
            'type' => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
            'name' => $params['element_name'],
            'unique_index' => $params['unique_index'],
        );

        $new_values = array(
            'title' => $params['edit_view_data']['EditViewModel']['module_title'],
        );

        $schema = SchemaModel::getInstance()
            ->update($find_params, $new_values)
            ->getSchema();

        if(empty($schema)){
            \Process\models\ProcessModel::getInstance()->setSchema($schema)->save();
        }

        return $this;
    }
    */





    /**
     * getSDMOperatorTaskDataList - Возвращает список задач
     */
    public function getSDMOperatorTaskDataList($operations_model, $only_first = false){
        $this->_parents_tasks_list = array();

        $this->findParentTasks($operations_model->unique_index, $only_first);

        asort($this->_parents_tasks_list);
        $this->_parents_tasks_list = array_merge(array(null=>\Yii::t('ProcessModule.base', 'Link with the task')), $this->_parents_tasks_list);

        return $this->_parents_tasks_list;
    }




    /**
     * findParentTasks - Ищет задачи
     */
    private function findParentTasks($unique_index, $only_first = false){
        $parent_ui_list = ArrowModel::getInstance()
                                ->setProcessIdCheck(false)
                                ->getUniqueIndexParent($unique_index);

        if(!empty($parent_ui_list)){
            foreach($parent_ui_list as $unique_index){
                $schema = SchemaModel::getInstance()->getSchema(false, true);
                $element = SchemaModel::getInstance()->getElementsFromSchema(
                    $schema,
                    array(
                        'type' => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
                        'unique_index' => $unique_index,
                    ));
                if(!empty($element) && $element[$unique_index]['name'] == \Process\models\OperationsModel::ELEMENT_TASK){
                    $this->_parents_tasks_list[$element[$unique_index]['unique_index']] = $element[$unique_index]['title'];
                }

                if(!empty($element) && $element[$unique_index]['name'] != \Process\models\OperationsModel::ELEMENT_BEGIN){
                    if($only_first) break;
                    $this->findParentTasks($element[$unique_index]['unique_index']);
                }
            }
        }
    }









    protected function calculateBDateEnding($start_date = null){
        $days = null;

        if($start_date === null){
            $start_date = date('Y-m-d 23:59:59');
        }

        $start_date = new \DateTime($start_date);

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationTaskBaseModel::ELEMENT_EXECUTION_TIME));
        if($from_schema && !empty($from_schema['value'][OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY])){
            $days = $from_schema['value'][OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY];
            if(is_numeric($days)){
                $start_date->modify('+' . $days . ' days');
            }
        }

        return $start_date->format('Y-m-d 23:59:59');
    }



    protected function getBDateEnding(){
        if(empty($card_id)) $card_id = $this->getIdCardFromSchema();
        if(empty($card_id)) return;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());

        $data_model = new \DataModel();
        $b_date_ending = $data_model
            ->setSelect('b_date_ending')
            ->setFrom($extension_copy->getTableName())
            ->setWhere($extension_copy->prefix_name . '_id=' . $card_id)
            ->findScalar();

        return ($b_date_ending ? $b_date_ending : null);
    }


















    /**
     * actionCloneDataBeforeSave - клонирует карточку
     */
    public function actionCloneDataBeforeSave($vars = null){
        $element_name = $this->_operations_model->element_name;
        if(!in_array($element_name, [OperationsModel::ELEMENT_TASK, OperationsModel::ELEMENT_AGREETMENT])){
            return $this;
        }

        $card_id = $this->getIdCardFromSchema();

        if(empty($card_id)){
            $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, $this->_operations_model->getSchema(), self::CARD_ID, $card_id, false);
            $this->_operations_model->schema = $schema;
            $this->_operations_model->card_id = $card_id;
        } else {
            $make_loggin = false;
            if($this->_operations_model->getStatus() != OperationsModel::STATUS_UNACTIVE) $make_loggin = true;

            $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());
            $copy_result = \EditViewCopyModel::getInstance($extension_copy)
                ->setMakeLoggin($make_loggin)
                ->setCopyFromProcess(true)
                ->copy(array($card_id), $extension_copy, false, null)
                ->getResult();

            $card_id = null;
            if(!empty($copy_result['id'][0])){
                $card_id = $copy_result['id'][0];
            }
            $schema = static::getUpdatedValueInSchema($this->_operations_model->element_name, $this->_operations_model->getSchema(), self::CARD_ID, $card_id, false);
            $this->_operations_model->schema = $schema;
            $this->_operations_model->card_id = $card_id;
        }

        return $this;
    }




    /**
     * actionOperationSetActive - вызывается когда оператор становится в Статус "Активный"
     */
    public function actionOperationSetActive(){
        if(empty($card_id)) $card_id = $this->getIdCardFromSchema();
        if(empty($card_id)) return $this;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getRelateCopyId());

        if(self::$_copy_id_changed == $extension_copy->copy_id && self::$_data_id_changed == $card_id){
            return $this;
        }

        //b_date_ending
        $date_ending = $this->calculateBDateEnding();

        $data_model = new \DataModel();
        $data_model
            ->setText('UPDATE ' . $extension_copy->getTableName() . ' SET b_date_ending="'. $date_ending .'" WHERE ' . $extension_copy->prefix_name . '_id=' . $card_id)
            ->execute();

        return $this;
    }




}
