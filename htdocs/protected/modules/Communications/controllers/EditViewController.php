<?php


class EditViewController extends EditView{


    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'edit':
                if(Yii::app()->controller->module->edit_view_enable == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

            case 'inLineSave':
                if(Yii::app()->controller->module->inline_edit_enable == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

            case 'inLineCancel':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;

        }

        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);

        $filterChain->run();
    }

    public function actionEdit(){
        $action_model = new Communications\models\EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new Communications\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }

    /**
     * Сохранение результатов InLine редактирования
     */
    public function actionInLineSave($copy_id){
        $validate = new Validate();

        $extension_copy = $this->module->extensionCopy;

        if(\Yii::app()->request->getPost('id_list') == false && in_array($extension_copy->extension_id, [ExtensionModel::MODULE_USERS, ExtensionModel::MODULE_STAFF])) {
            $params_model = ParamsModel::model()->findByAttributes(['title' => 'license_users_limit']);

            if ($params_model !== null) {
                $maxUsersCount = $params_model->value;
                if ($maxUsersCount > 0) {
                    $usersCount = UsersModel::model()->count();

                    if ($usersCount >= $maxUsersCount) {
                        $validate->addValidateResult('e', Yii::t(
                            'messages',
                            'Licenses number exceeded. Maximum number of users allowed - {s}',
                            ['{s}' => $maxUsersCount]
                        ));
                    }
                }
            }
        }

        /*
        if(\Yii::app()->request->getPost('EditViewModel') == false){
            $validate->addValidateResult('e', Yii::t('messages', 'Lack of data for conservation'));
        }
        */

        if($validate->error_count > 0){
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        $schema_parser = $extension_copy->getSchemaParse($extension_copy->getSchema());

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $extension_copy->getTableName(null, false),
            'params' => Fields::getInstance()->getActiveRecordsParams($schema_parser),
        );

        if(\Yii::app()->request->getPost('id_list') == false){
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => (new \Validate())->addValidateResult('e', Yii::t('messages', 'No entries selected for update')),
            ));
        }

        $id_list_saved = [];
        $id_list = (array)\Yii::app()->request->getPost('id_list');
        if(!$id_list) $id_list = [null]; // если добавление новой записи через редактирование...


        foreach($id_list as $data_id){
            if($data_id){
                $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($data_id);

                if(!ParticipantModel::model()->checkUserSubscription(
                    $extension_copy->copy_id,
                    $data_id,
                    $extension_data
                )
                ){
                    if(\Yii::app()->request->getPost('lot_edit', false)){
                        continue;
                    } else{
                        return $this->returnCheckMessage('w', Yii::t('messages', 'Access denied! You are not a owner or member'));
                    }
                }

            } else {
                $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
            }

            $extension_data->setElementSchema($schema_parser);
            $extension_data->extension_copy = $extension_copy;
            $extension_data->setMyAttributes(\Yii::app()->request->getPost('EditViewModel'));
            $extension_data->scenario = 'inline_edit';
            $extension_data->setPrimaryEntities((!empty($_POST['primary_entities']) ? $_POST['primary_entities'] : null));

            if($extension_data->save()){
                $id = $extension_data->getPrimaryKey();
                $id_list_saved[] = $id;

                // если не массовое редактирование
                if(\Yii::app()->request->getPost('lot_edit', false)){
                    continue;
                } else {
                    if(!in_array($extension_copy->copy_id,
                        array(
                            ExtensionCopyModel::MODULE_ROLES,
                            ExtensionCopyModel::MODULE_USERS,
                            ExtensionCopyModel::MODULE_PERMISSION,
                            ExtensionCopyModel::MODULE_STAFF
                        ))){

                        if($extension_copy->getAttribute('copy_id') == ExtensionCopyModel::MODULE_TASKS && !$extension_data->isNewRecord){
                            TaskModel::deleteMarkTaskIsView($extension_data->getPrimaryKey());
                        }

                        /*
                        if(Yii::app()->request->getPost('data_changed')) {
                            History::getInstance()->addToHistory(HistoryMessagesModel::MT_CHANGED,
                                $extension_copy->copy_id,
                                $extension_data->getPrimaryKey(),
                                array('{module_data_title}' => $extension_data->getModuleTitle(), '{user_id}' => WebUser::getUserId())
                            );
                        }
                        */
                    }

                    $params = array();
                    if(isset($schema_parser['elements'])){
                        foreach($schema_parser['elements'] as $key => $value){
                            if(!isset($value['field'])) continue;
                            if($value['field']['params']['type'] == 'display_none') continue;
                            $denied_relate = SchemaOperation::getDeniedRelateCopyId(array($value['field']['params']));
                            if($denied_relate['be_fields'] == false) continue;

                            $value['field']['params']['title'] = $value['field']['title'];
                            $params[] = $value['field']['params'];
                        }
                    }

                    $module_data = new DataModel();
                    $module_data
                        ->setExtensionCopy($extension_copy)
                        ->setFromModuleTables();

                    //responsible
                    if($extension_copy->isResponsible())
                        $module_data->setFromResponsible();

                    //participant
                    if($extension_copy->isParticipant())
                        $module_data->setFromParticipant();


                    $module_data
                        ->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = :id', array(':id' => $id))
                        ->addSelect('(select  max(date_create) from {{activity_messages}} where( copy_id = '.$extension_copy->copy_id.' and data_id = {{communications.communications_id}})) as activity_last_date')
                        ->setCollectingSelect();

                    $primary_link = ListViewBulder::PRIMARY_LINK_EDIT_VIEW;
                    if($extension_copy->copy_id == ExtensionCopyModel::MODULE_USERS){
                        $primary_link = ListViewBulder::PRIMARY_LINK_NONE_LINK;
                    } elseif($extension_copy->copy_id == ExtensionCopyModel::MODULE_REPORTS){
                        $primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
                    } elseif($extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS){
                        $primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
                    }

                    ListViewBulder::$primary_link_aded = false;

                    $data=$module_data->findRow();
                    $datetime_activity_params_block = SchemaOperation::getBlockWithKey($params,'type','datetime_activity');
                    if($datetime_activity_params_block)
                    {
                        $name_ativity_param_value = 'activity_last_date';
                        $field_name = $datetime_activity_params_block['name'];
                        $data[$field_name] = $data[$name_ativity_param_value];
                    }
                    $inline_elements = ListViewBulder::getInstance($extension_copy)->buildHtmlListView($params, $data, $primary_link);

                    return $this->renderJson(array(
                        'status' => 'save',
                        'element_data' => $inline_elements,
                        'content_reload_vars' => \ContentReloadModel::getContentVars(),
                        'id' => $id,
                    ));
                }
            } else {
                if(\Yii::app()->request->getPost('lot_edit', false) ==false){
                    return $this->renderJson(array(
                        'status' => 'error_save',
                        'messages' => $extension_data->getErrorsHtml()
                    ));
                }
            }
        }

        return $this->renderJson(array(
            'status' => 'save',
            'id_list' => $id_list_saved,
        ));
    }

    /**
     * Отмена результатов InLine редактирования
     */
    public function actionInLineCancel(){
        $validate = new Validate();


        if(empty($_POST['id'])) $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        if($validate->error_count > 0){
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse($extension_copy->getSchema());

        $params = array();
        foreach($schema_parser['elements'] as $key => $value){
            if(!isset($value['field'])) continue;
            if($value['field']['params']['type'] == 'display_none') continue;

            $denied_relate = SchemaOperation::getDeniedRelateCopyId(array($value['field']['params']));
            if($denied_relate['be_fields'] == false) continue;

            $value['field']['params']['title'] = $value['field']['title'];
            $params[] = $value['field']['params'];
        }


        $module_data = new DataModel();
        $module_data
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        //responsible
        if($extension_copy->isResponsible())
            $module_data->setFromResponsible();

        //participant
        if($extension_copy->isParticipant())
            $module_data->setFromParticipant();



        $module_data
            ->addSelect('(select  max(date_create) from {{activity_messages}} where( copy_id = '.$extension_copy->copy_id.' and data_id = {{communications.communications_id}})) as activity_last_date')
            ->setCollectingSelect()
            ->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = :id', array(':id'=>$_POST['id']));


        $primary_link = ListViewBulder::PRIMARY_LINK_EDIT_VIEW;
        if($extension_copy->copy_id == ExtensionCopyModel::MODULE_USERS){
            $primary_link = ListViewBulder::PRIMARY_LINK_NONE_LINK;
        } elseif($extension_copy->copy_id == ExtensionCopyModel::MODULE_REPORTS){
            $primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
        } elseif($extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS){
            $primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
        }

        $inline_elements = ListViewBulder::getInstance($extension_copy)->buildHtmlListView($params, $module_data->findRow(), $primary_link);

        return $this->renderJson(array(
            'status' => true,
            'element_data' => $inline_elements,
            'content_reload_vars' => \ContentReloadModel::getContentVars(),
        ));
    }

}
