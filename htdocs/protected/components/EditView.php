<?php
/**
 * EditView
 *
 * @author Alex R.
 * @version 1.0
 */

class EditView extends Controller
{

    /**
     * filter
     */
    public function filters()
    {
        return [
            'checkAccess',
        ];
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'edit':
                if (Yii::app()->controller->module->edit_view_enable == false) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if ($data_id = \Yii::app()->request->getParam('id')) {
                    (new EditViewActionModel())
                        ->setExtensionCopy($this->module->extensionCopy)
                        ->findAndMarkHistoryIsView($data_id);
                }

            case 'inLineSave':
            case 'bulkEdit':
                if (Yii::app()->controller->module->inline_edit_enable == false) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if (Yii::app()->request->getPost('EditViewModel')) {
                    if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                } else {
                    if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                }

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    ListViewBulder::$participant_list_hidden = true;
                }

                if (!Access::checkAccessDataOnParticipant($this->module->extensionCopy->copy_id, \Yii::app()->request->getParam('id', null))) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;

            case 'relateReload':
            case 'relateReloadSDM':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'editSelect':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'updateRelateForTemplate':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

        }
        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);

        $filterChain->run();
    }

    /**
     * обновляем связи дочерних элементов, когда создаем с шаблона
     */
    public function actionUpdateRelateForTemplate()
    {
        $id = Yii::app()->request->getPost('id');
        $primary_entities = Yii::app()->request->getPost('primary_entities');

        if ($id && $primary_entities) {
            $extension_copy = $this->module->extensionCopy;
            if ($extension_copy) {

                $schema_parser = $extension_copy->getSchemaParse();

                $alias = 'evm_' . $extension_copy->copy_id;
                $dinamic_params = [
                    'tableName' => $extension_copy->getTableName(null, false),
                    'params'    => Fields::getInstance()->getActiveRecordsParams($schema_parser),
                ];

                $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($id);
                if ($extension_data) {
                    $extension_data->extension_copy = $extension_copy;
                    $extension_data->setPrimaryEntities($primary_entities);
                    $extension_data->setQwePrimaryKey();
                    $extension_data->setPrimaryEntityChanged(true);
                    $extension_data->updateAllPrimaryModelValue();

                    return $this->renderJson([
                        'status' => true
                    ]);
                }
            }
        }

        return $this->renderJson([
            'status' => false
        ]);

    }

    /**
     * Просмотр/добавление/редактирование данних в EditView
     * Доступные сценарии: update, edit
     */
    public function actionAddByTitle()
    {
        (new EditViewActionModel())
            ->run(EditViewActionModel::ACTION_ADD_BY_TITLE, $_POST)
            ->getResult();
    }

    /**
     * Просмотр/добавление/редактирование данних в EditView
     * Доступные сценарии: update, edit
     */
    public function actionEdit()
    {
        (new EditViewActionModel())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }

    /**
     * добавление данних в EditView в пре-выбором - новый/из шаблона
     */
    public function actionEditSelect()
    {
        $extension_copy = $this->module->extensionCopy;

        // если создаем карточку из сабмодуля
        if (Yii::app()->request->getParam('parent_class') == 'edit-view') {
            $code_action = ValidateConfirmActions::ACTION_SUB_MODULE_EDIT_VIEW_CREATE_SELECT;

            $sub_module_validate = ValidateSubModule::getInstance()->check(array_merge($_POST, ['copy_id' => $extension_copy->copy_id]), $code_action);
            if ($sub_module_validate->beMessages()) {
                return $this->renderJson([
                    'status'   => 'error',
                    'messages' => $sub_module_validate->getValidateResultHtml(),
                ]);
            }
        }

        $blocks = [];
        $block_field_name = '';
        $block_field_title = '';
        $show_blocks = !$this->module->showBlocks($extension_copy);

        $params = [];

        if ($show_blocks) {

            //ожидание определенной карточки
            if (Yii::app()->request->getParam('auto_new_card')) {
                $params = \AdditionalProccessingModel::getInstance()->addLinkedCard(Yii::app()->request->getParam('auto_new_card'), Yii::app()->request->getParam('parent_data_id'));
            }

            $only_specific_block = (array_key_exists('only_specific_block', $params)) ? $params['only_specific_block'] : false;

            //список стандартных блоков из схемы
            $blocks = $extension_copy->getSchemaBlocksData($only_specific_block);
            array_unshift($blocks, ['unique_index' => '', 'title' => '']);

            $block_field_data = $extension_copy->getFieldBlockData();
            $block_field_name = ($block_field_data) ? $block_field_data['name'] : '';
            $block_field_title = ($block_field_data) ? $block_field_data['title'] : '';

            $templates = [];
        } else {
            //блоки не показываем, шаблоны загружаются сразу
            $templates = $this->getTemplates($extension_copy, false, Yii::app()->request->getParam('parent_copy_id'));
        }

        $this->data = array_merge($this->data, [
            'extension_copy'    => $extension_copy,
            'parent_copy_id'    => Yii::app()->request->getParam('parent_copy_id'),
            'parent_data_id'    => Yii::app()->request->getParam('parent_data_id'),
            'this_template'     => Yii::app()->request->getParam('this_template'),
            'finished_object'   => Yii::app()->request->getParam('finished_object'),
            'default_data'      => (Yii::app()->request->getParam('default_data') ? json_encode(Yii::app()->request->getParam('default_data')) : ''),
            'templates'         => $templates,
            'show_blocks'       => $show_blocks,
            'blocks'            => $blocks,
            'params'            => $params,
            'block_field_name'  => $block_field_name,
            'block_field_title' => $block_field_title,
            'auto_new_card'     => Yii::app()->request->getParam('auto_new_card'),
            'parent_class'      => Yii::app()->request->getParam('parent_class'),
        ]);

        return $this->renderJson([
            'status' => true,
            'data'   => $this->renderPartial(ViewList::getView('dialogs/editViewAdd'), $this->data, true)
        ]);
    }

    /**
     *   Массовое редактирование записей
     */
    public function actionBulkEdit($copy_id)
    {

        \EditViewBulkEditModel::getInstance()
            ->setExtensionCopy($this->module->extensionCopy)
            ->prepareData((isset($_POST['EditViewModel'])) ? $_POST['EditViewModel'] : [])
            ->prepareFormulaData()
            ->setIds($_POST['list'], (isset($_POST['allChecked'])) ? true : false)
            ->edit();

        return $this->renderJson([
            'status' => true,
        ]);
    }

    public static function getTemplates($extension_copy, $add_where = false, $parent_copy_id = null)
    {

        $table_name = $extension_copy->getTableName();

        $data_model = new DataModel();
        $data_model
            ->addSelect($table_name . '.*')
            ->addSelect($table_name . '.' . $extension_copy->prefix_name . '_id as id')
            ->setFrom($table_name)
            ->setWhere($table_name . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE . '"');

        if ($extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS && $parent_copy_id) {
            $field_schema = $extension_copy->getFieldSchemaParamsByType(\Fields::MFT_MODULE);
            $data_model->andWhere(['AND', $field_schema['params']['name'] . ' = ' . $parent_copy_id]);
        }

        if ($add_where) {
            $data_model->andWhere($add_where);
        }

        if (Yii::app()->controller->module->dataIfParticipant($extension_copy) && ($extension_copy->isParticipant() || $extension_copy->isResponsible())) {
            $data_model->setExtensionCopy($extension_copy);
            //responsible
            if ($extension_copy->isResponsible()) {
                $data_model->setFromResponsible();
            }

            //participant
            if ($extension_copy->isParticipant()) {
                $data_model->setFromParticipant();
            }

            $data_model->setCollectingSelect();

            $data_model->setOtherPartisipantAllowed($extension_copy->copy_id);
        } else {
            $data_model->setCollectingSelect();
        }

        $primary_field_data = $extension_copy->getPrimaryField();
        $data_model->setOrder($primary_field_data['params']['name']);
        $data_model->setGroup($extension_copy->getPkFieldName());

        $templates = $data_model->findAll();

        array_unshift($templates, ['id' => '', 'module_title' => '']);

        return $templates;
    }

    /**
     * Сохранение результатов InLine редактирования
     */
    public function actionInLineSave($copy_id)
    {
        $validate = new Validate();

        $extension_copy = $this->module->extensionCopy;

        if (\Yii::app()->request->getPost('id_list') == false && in_array($extension_copy->extension_id, [ExtensionModel::MODULE_USERS, ExtensionModel::MODULE_STAFF])) {
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

        if ($validate->error_count > 0) {
            return $this->renderJson([
                'status'   => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $schema_parser = $extension_copy->getSchemaParse($extension_copy->getSchema());

        $attributes = \Yii::app()->request->getPost('EditViewModel');

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false),
            'params'    => Fields::getInstance()
                ->setOnlyActiveRecordsFields(array_keys($attributes))
                ->getActiveRecordsParams($schema_parser),
        ];

        if (\Yii::app()->request->getPost('id_list') == false) {
            return $this->renderJson([
                'status'   => 'error',
                'messages' => (new \Validate())->addValidateResult('e', Yii::t('messages', 'No entries selected for update')),
            ]);
        }

        $id_list_saved = [];
        $id_list = (array)\Yii::app()->request->getPost('id_list');
        if (!$id_list) {
            $id_list = [null];
        } // если добавление новой записи через редактирование...

        foreach ($id_list as $data_id) {
            if ($data_id) {
                $edit_view_model = EditViewModel::modelR($alias, $dinamic_params)->findByPk($data_id);

                if (!ParticipantModel::model()->checkUserSubscription(
                    $extension_copy->copy_id,
                    $data_id,
                    $edit_view_model
                )
                ) {
                    if (\Yii::app()->request->getPost('lot_edit', false)) {
                        continue;
                    } else {
                        return $this->returnCheckMessage('w', Yii::t('messages', 'Access denied! You are not a owner or member'));
                    }
                }

            } else {
                $edit_view_model = EditViewModel::modelR($alias, $dinamic_params, true);
            }

            $edit_view_model->setElementSchema($schema_parser);
            $edit_view_model->extension_copy = $extension_copy;
            $edit_view_model->setMyAttributes(\Yii::app()->request->getPost('EditViewModel'));
            $edit_view_model->scenario = 'inline_edit';
            $edit_view_model->setPrimaryEntities((!empty($_POST['primary_entities']) ? $_POST['primary_entities'] : null));

            if ($edit_view_model->save()) {
                $id = $edit_view_model->getPrimaryKey();
                $id_list_saved[] = $id;

                $edit_view_model->actionCreateProcessAfterChangedEntity();

                // если не массовое редактирование
                if (\Yii::app()->request->getPost('lot_edit', false)) {
                    continue;
                } else {
                    if (!in_array($extension_copy->copy_id,
                        [
                            ExtensionCopyModel::MODULE_ROLES,
                            ExtensionCopyModel::MODULE_USERS,
                            ExtensionCopyModel::MODULE_PERMISSION,
                            ExtensionCopyModel::MODULE_STAFF
                        ])) {

                        if ($extension_copy->getAttribute('copy_id') == ExtensionCopyModel::MODULE_TASKS && !$edit_view_model->isNewRecord) {
                            TaskModel::deleteMarkTaskIsView($edit_view_model->getPrimaryKey());
                        }

                        /*
                        if(Yii::app()->request->getPost('data_changed')) {
                            History::getInstance()->addToHistory(HistoryMessagesModel::MT_CHANGED,
                                $extension_copy->copy_id,
                                $edit_view_model->getPrimaryKey(),
                                array('{module_data_title}' => $edit_view_model->getModuleTitle(), '{user_id}' => WebUser::getUserId())
                            );
                        }
                        */
                    }
                    if ($extension_copy->copy_id == ExtensionCopyModel::MODULE_ROLES && empty($id_list[0])) {
                        // admin
                        $permission_casual = new PermissionsModel();
                        $permission_casual->fillWithParams(WebUser::getUserId(), Access::ACCESS_TYPE_REGULATION, PermissionsModel::ACCESS_TYPE_REGULATION, 2, 1, 1, 1, 1, 1, 2);
                        $permission_casual->save();
                        $permission_role = new PermissionRolesModel();
                        $permission_role->roles_id = $edit_view_model->roles_id;
                        $permission_role->permission_id = $permission_casual->permission_id;
                        $permission_role->save();

                        // modules
                        $extension_copy_all = ExtensionCopyModel::model()->setAccess()->findAll();
                        foreach ($extension_copy_all as $extension_copy_el) {
                            $permission_casual = new PermissionsModel();
                            $permission_casual->fillWithParams(WebUser::getUserId(), $extension_copy_el->copy_id, PermissionsModel::ACCESS_TYPE_MODULE, 2, 1, 1, 1, 1, 1, 2);
                            $permission_casual->save();

                            $permission_role = new PermissionRolesModel();
                            $permission_role->roles_id = $edit_view_model->roles_id;
                            $permission_role->permission_id = $permission_casual->permission_id;
                            $permission_role->save();
                        }
                    }

                    $schema_params = [];
                    if (isset($schema_parser['elements'])) {
                        foreach ($schema_parser['elements'] as $key => $value) {
                            if (!isset($value['field'])) {
                                continue;
                            }
                            if ($value['field']['params']['type'] == 'display_none') {
                                continue;
                            }
                            $denied_relate = SchemaOperation::getDeniedRelateCopyId([$value['field']['params']]);
                            if ($denied_relate['be_fields'] == false) {
                                continue;
                            }

                            $value['field']['params']['title'] = $value['field']['title'];
                            $schema_params[] = $value['field']['params'];
                        }
                    }

                    $module_data = new DataModel();
                    $module_data
                        ->setExtensionCopy($extension_copy)
                        ->setFromModuleTables();

                    //responsible
                    if ($extension_copy->isResponsible()) {
                        $module_data->setFromResponsible();
                    }

                    //participant
                    if ($extension_copy->isParticipant()) {
                        $module_data->setFromParticipant();
                    }

                    $module_data
                        ->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = :id', [':id' => $id])
                        ->setCollectingSelect();

                    ListViewBulder::$primary_link_aded = false;

                    $list_view_row_model = (new \ListViewRowModel())
                        ->setExtensionCopy($extension_copy)
                        ->setParentCopyId(\Yii::app()->request->getParam('pci'))
                        ->setParentCopyId(\Yii::app()->request->getParam('pdi'))
                        //->setThisTemplate($this->this_template)
                        //->setFinishedObject(false)
                        ->setSchemaParams($schema_params)
                        ->setData($module_data->findRow())
                        ->prepareHtmlRowArray();

                    return $this->renderJson([
                        'status'              => 'save',
                        'element_data'        => $list_view_row_model->getHtml(),
                        'content_reload_vars' => \ContentReloadModel::getContentVars(),
                        'entity'              => EntityModel::getEntityProperties(),
                        'id'                  => $id,
                    ]);
                }
            } else {
                if (\Yii::app()->request->getPost('lot_edit', false) == false) {
                    return $this->renderJson([
                        'status'   => 'error_save',
                        'messages' => $edit_view_model->getErrorsHtml()
                    ]);
                }
            }
        }

        return $this->renderJson([
            'status'  => 'save',
            'id_list' => $id_list_saved,
        ]);
    }

    /**
     * Отмена результатов InLine редактирования
     */
    public function actionInLineCancel()
    {
        $validate = new Validate();

        if (empty($_POST['id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        }
        if ($validate->error_count > 0) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse($extension_copy->getSchema());

        $schema_params = [];
        foreach ($schema_parser['elements'] as $key => $value) {
            if (!isset($value['field'])) {
                continue;
            }
            if ($value['field']['params']['type'] == 'display_none') {
                continue;
            }

            $denied_relate = SchemaOperation::getDeniedRelateCopyId([$value['field']['params']]);
            if ($denied_relate['be_fields'] == false) {
                continue;
            }

            $value['field']['params']['title'] = $value['field']['title'];
            $schema_params[] = $value['field']['params'];
        }

        $module_data = new DataModel();
        $module_data
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        //responsible
        if ($extension_copy->isResponsible()) {
            $module_data->setFromResponsible();
        }

        //participant
        if ($extension_copy->isParticipant()) {
            $module_data->setFromParticipant();
        }

        $module_data
            ->setCollectingSelect()
            ->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = :id', [':id' => $_POST['id']]);

        ListViewBulder::$primary_link_aded = false;

        $list_view_row_model = (new \ListViewRowModel())
            ->setExtensionCopy($extension_copy)
            ->setParentCopyId(\Yii::app()->request->getParam('pci'))
            ->setParentCopyId(\Yii::app()->request->getParam('pdi'))
            //->setThisTemplate($this->this_template)
            //->setFinishedObject(false)
            ->setSchemaParams($schema_params)
            ->setData($module_data->findRow())
            ->prepareHtmlRowArray();

        return $this->renderJson([
            'status'              => true,
            'element_data'        => $list_view_row_model->getHtml(),
            'content_reload_vars' => \ContentReloadModel::getContentVars(),
            'entity'              => EntityModel::getEntityProperties(),
        ]);
    }

    /**
     * перегрузка значений списков элементов СДМ
     */
    public function actionRelateReload()
    {
        $parent_relate_data_list = \Yii::app()->request->getParam('parent_relate_data_list');
        if ($parent_relate_data_list == false) {
            return $this->renderJson([
                'status' => false,
            ]);
        }

        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse();
        $params = Fields::getInstance()->getActiveRecordsParams($schema_parser);

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false),
            'params'    => $params,
        ];

        if (!empty($_POST['data_id'])) {
            $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($_POST['id']);
        } else {
            $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
        }

        if ($extension_data == false) {
            return $this->renderJson([
                'status' => false,
            ]);
        }

        $extension_data->setElementSchema($schema_parser);
        $extension_data->extension_copy = $extension_copy;

        $data_list = [];

        $relate_schema_params = $extension_copy->getFieldSchemaParamsByType(\Fields::MFT_RELATE, null, false);

        if ($relate_schema_params) {
            foreach ($relate_schema_params as $schema_params) {
                if (in_array((int)$schema_params['params']['relate_module_copy_id'], array_keys($parent_relate_data_list)) == false) {
                    continue;
                }

                $schema_params['params']['relate_get_value'] = false;

                if (array_key_exists('relate_get_value', $_POST) && (boolean)$_POST['relate_get_value'] == true) {
                    $schema_params['params']['relate_get_value'] = true;
                    if (!empty($_POST['parent_relate_data_list'][$schema_params['params']['relate_module_copy_id']])) {
                        $schema_params['params']['relate_data_id'] = $_POST['parent_relate_data_list'][$schema_params['params']['relate_module_copy_id']];
                    }
                }

                $default_data = null;
                if ($extension_data->isNewRecord) {
                    $default_data = $schema_params['params']['default_value'];
                }

                $vars = [
                    'schema'                  => $schema_params,
                    'extension_copy'          => $extension_copy,
                    'extension_data'          => $extension_data,
                    'default_data'            => $default_data,
                    'parent_copy_id'          => ['pci' => Yii::app()->request->getParam('pci'), 'parent_copy_id' => ''],
                    'parent_data_id'          => ['pdi' => Yii::app()->request->getParam('pdi'), 'parent_data_id' => ''],
                    'primary_entities'        => (!empty($_POST['primary_entities']) ? $_POST['primary_entities'] : null),
                    'this_template'           => (isset($_POST['this_template']) ? $_POST['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
                    'parent_relate_data_list' => (isset($_POST['parent_relate_data_list']) ? $_POST['parent_relate_data_list'] : null),
                ];

                $ddl_data = \DropDownListModel::getInstance()
                    ->setDefaultDataId($default_data)
                    ->setActiveDataType(\DropDownListModel::DATA_TYPE_1)
                    ->setVars($vars)
                    ->prepareHtml()
                    ->getResultHtml();

                if ($ddl_data['status']) {
                    $data_list[$schema_params['params']['relate_module_copy_id']] = $ddl_data['html'];
                }
            }

        }

        return $this->renderJson([
            'status'    => true,
            'data_list' => $data_list,
            'entity'    => EntityModel::getEntityProperties(),
        ]);

    }

    /**
     * перегрузка значений всех списков элементов СДМ
     */
    public function actionRelateReloadSDM()
    {
        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse();
        $params = Fields::getInstance()->getActiveRecordsParams($schema_parser);

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false),
            'params'    => $params,
        ];

        $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
        $extension_data
            ->setElementSchema($schema_parser)
            ->setExtensionCopy($extension_copy);

        if ($id = \Yii::app()->request->getParam('id')) {
            $extension_data = $extension_data->findByPk($id);
        }

        $active_data_type = \DropDownListModel::DATA_TYPE_1;

        foreach ($schema_parser['elements'] as $field_params) {
            if (!isset($field_params['field'])) {
                continue;
            }

            if (!empty($_POST['relate_element']['sub_type']) && $_POST['relate_element']['sub_type'] == 'dinamic') {
                if ($field_params['field']['params']['type'] != \Fields::MFT_RELATE_DINAMIC) {
                    continue;
                }
                $field_params['field']['params']['relate_module_copy_id'] = $_POST['relate_element']['copy_id'];
                if ($extension_copy->copy_id != \ExtensionCopyModel::MODULE_TASKS) {
                    $extension_data->related_module = $_POST['relate_element']['copy_id'];
                }
                $active_data_type = \DropDownListModel::DATA_TYPE_4;
            } else {
                if ($field_params['field']['params']['relate_module_copy_id'] != $_POST['relate_element']['copy_id']) {
                    continue;
                }
            }

            $default_data = null;
            if (!empty($_POST['relate_element']['data_id'])) {
                $default_data = $_POST['relate_element']['data_id'];
            } else {
                if ($extension_data && $extension_data->isNewRecord) {
                    $default_data = $field_params['field']['params']['default_value'];
                }
            }

            $vars = [
                'schema'                  => $field_params['field'],
                'extension_copy'          => $extension_copy,
                'extension_data'          => $extension_data,
                'default_data'            => $default_data,
                'parent_copy_id'          => ['pci' => Yii::app()->request->getParam('pci'), 'parent_copy_id' => ''],
                'parent_data_id'          => ['pdi' => Yii::app()->request->getParam('pdi'), 'parent_data_id' => ''],
                'primary_entities'        => (!empty($_POST['primary_entities']) ? $_POST['primary_entities'] : null),
                'this_template'           => (isset($_POST['this_template']) ? $_POST['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
                'parent_relate_data_list' => null,
            ];

            $ddl_data = \DropDownListModel::getInstance()
                ->setDefaultDataId($default_data)
                ->setActiveDataType($active_data_type)
                ->setVars($vars)
                ->prepareHtml()
                ->getResultHtml();

            $html = null;
            if ($ddl_data['status']) {
                $html = $ddl_data['html'];
            }

            return $this->renderJson([
                'status' => true,
                'html'   => $html,
                'entity' => EntityModel::getEntityProperties(),
            ]);
        }

        return $this->renderJson([
            'status'   => false,
            'messages' => (new Validate())->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'))->getValidateResultHtml(),
        ]);
    }

    /**
     * перегрузка значений списка СДМ-каналов в блоке Активность
     */
    public function actionRelateReloadSDMChannel()
    {
        $vars = [
            'extension_copy' => $this->module->extensionCopy,
            'data_id'        => \Yii::app()->request->getParam('id'),
        ];

        $ddl_data = \DropDownListModel::getInstance()
            ->setActiveDataType(\DropDownListModel::DATA_TYPE_9)
            ->setVars($vars)
            ->setDefaultDataId(\Yii::app()->request->getParam('data_id'))
            ->prepareHtml()
            ->getResultHtml();

        if ($ddl_data['status']) {
            return $this->renderJson([
                'status' => true,
                'html'   => $ddl_data['html'],
            ]);
        } else {
            return $this->renderJson([
                'status' => false
            ]);
        }
    }

    /**
     * переключение блоков
     */
    public function actionToggleBlocks()
    {
        $blocks_for_deleting = [];
        $schema_for_add_block = false;
        $content = '';

        $extension_copy = $this->module->extensionCopy;
        $schema = $this->module->extensionCopy->getSchema();

        foreach ($schema as $value) {
            if (isset($value['type'])) {
                if ($value['type'] == 'block') {
                    //элемент типа блок
                    if ($value['elements'][0]['type'] == 'block_panel' && !$value['params']['header_hidden']) {
                        //стандартный блок
                        if ($value['params']['unique_index'] == $_POST['block_unique_index']) {
                            //этот блок показываем
                            $schema_for_add_block = $value;
                        } else {
                            //остальные удаляем
                            $blocks_for_deleting [] = $value['params']['unique_index'];
                        }
                    }
                }
            }
        }

        if ($schema_for_add_block) {
            $alias = 'evm_' . $extension_copy->copy_id;
            $dinamic_params = [
                'tableName' => $extension_copy->getTableName(null, false),
                'params'    => \Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
            ];

            $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
            $extension_data->setExtensionCopy($extension_copy);

            $content = (new EditViewBuilder())
                ->setExtensionCopy($extension_copy)
                ->setExtensionData($extension_data)
                ->getEditViewElementBlock($schema_for_add_block);
        }

        return $this->renderJson([
            'status'  => true,
            'content' => $content,
            'deleted' => $blocks_for_deleting,
        ]);

    }
}
