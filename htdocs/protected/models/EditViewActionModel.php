<?php
/**
 * EditViewActionModel - Модель действий для EditView
 */

class EditViewActionModel
{
    const ACTION_RUN_AUTO = 'auto';
    const ACTION_ADD_BY_TITLE = 'add_by_title';

    const STATUS_DATA = 'data';
    const STATUS_SAVE = 'save';
    const STATUS_SAVE_ERROR = 'save_error';
    const STATUS_ERROR = 'error';

    private $_edit_data;        // данные модуля

    private $_controller;       // активный контроллер

    private $_extension_copy;

    private $_template_data_id;

    private $_id;               // id записи в EditViewModel

    private $_html_data;        // html для EditView

    private $_status;           // save | data | error

    private $_validate;         // модель для валидации

    private $_edit_view_builder;// класс конструктора  EditView

    private $_data_not_found;   // указывает на отсутствие данных

    private $_is_new_record;    // указывает, что запись была новая

    private $_make_logging = true; // делает логирование

    public $_edit_model;       // эклемпляр модели EditViewModel

    private $_edit_view_is_new_record;

    private $_edit_view_cache_new_record;

    private $_dinamic_rules = [];

    private $_validate_data = true;

    private $_extension_copy_schema;

    private $_entity_model;

    private $_auto_next_card = false; //ожидается запуск создания определенной карточки

    private $_ev_refresh_field = false;

    private $_validate_entity_required = true;

    public function __construct($copy_id = null)
    {
        $this->_validate = new \Validate();
        $this->_controller = \Yii::app()->controller;

        if ($copy_id === null) {
            $this->_extension_copy = $this->_controller->module->extensionCopy;
        } else {
            $this->_extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        }
    }

    public function setSwitchRunProcess($switch_run_process)
    {
        if (empty($this->_edit_model)) {
            return $this;
        }

        $this->_edit_model->setSwitchRunProcess($switch_run_process);

        return $this;
    }

    public function setExtensionCopySchema($extension_copy_schema)
    {
        $this->_extension_copy_schema = $extension_copy_schema;

        return $this;
    }

    public function getExtensionCopySchema()
    {
        if ($this->_extension_copy_schema !== null && !empty($this->_extension_copy_schema)) {
            return $this->_extension_copy_schema;
        } else {
            return $this->_extension_copy->getSchema();
        }
    }

    public function setEditViewIsNewRecord($edit_view_is_new_record)
    {
        $this->_edit_view_is_new_record = $edit_view_is_new_record;

        return $this;
    }

    public function setEditViewCacheNewRecord($edit_view_cache_new_record)
    {
        $this->_edit_view_cache_new_record = $edit_view_cache_new_record;

        return $this;
    }

    /**
     * setExtensionCopy
     *
     * @param $data - ExtensionCopyModel or copy_id
     */
    public function setExtensionCopy($data)
    {
        if ($data instanceof \ExtensionCopyModel) {
            $this->_extension_copy = $data;
        } elseif (is_numeric($data)) {
            $this->_extension_copy = \ExtensionCopyModel::model()->findByPk($data);
        }

        if (!empty($this->_edit_model)) {
            $this->_edit_model->_dinamic_params['tableName'] = $this->_extension_copy->getTableName(null, false);
            $this->_edit_model->extension_copy = $this->_extension_copy;
        }

        return $this;
    }

    public function getExtensionCopy()
    {
        return $this->_extension_copy;
    }

    /**
     * setDinamicRules - дополнительные параметры для условия rules в EditView
     */
    public function setDinamicRules(array $dinamic_rules)
    {
        $this->_dinamic_rules = $dinamic_rules;

        return $this;
    }

    public function setValidateData($status)
    {
        $this->_validate_data = $status;

        return $this;
    }

    /**
     * setEditData - данные для созхранения
     */
    public function setEditData($edit_data)
    {
        $this->_edit_data = $edit_data;

        return $this;
    }

    /**
     * setNextCard - данные для следующей карточки
     */
    private function setNextCard($value)
    {
        $this->_auto_next_card = $value;

        return $this;
    }

    /**
     * setEVRefreshFields - дополнительно обновляемые поля
     */
    private function setEVRefreshFields($value)
    {
        $this->_ev_refresh_field = $value;

        return $this;
    }

    public function setMakeLogging($make_logging)
    {
        $this->_make_logging = $make_logging;

        if (!empty($this->_edit_model)) {
            $this->_edit_model->setMakeLogging($this->_make_logging);
        }

        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    /**
     * getIdFromEditData
     */
    private function getIdFromEditData()
    {
        $id = null;
        if (isset($this->_edit_data['id'])) {
            $id = $this->_edit_data['id'];
        }
        if ($id == 'new') {
            $id = null;
        }

        return $id;
    }

    /**
     * getStatus
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * getHtmlData - данные для отображения страницы EditView
     */
    public function getHtmlData()
    {
        return $this->_html_data;
    }

    public function setEditViewBuilder($edit_view_builder)
    {
        $this->_edit_view_builder = $edit_view_builder;

        return $this;
    }

    public function getEditViewBuilder()
    {
        return $this->_edit_view_builder;
    }

    public function getDataNotFound()
    {
        return $this->_data_not_found;
    }

    public function getIsNewRecord()
    {
        return $this->_is_new_record;
    }

    /**
     * @return EditViewModel
     */
    public function getEditModel()
    {
        return $this->_edit_model;
    }

    public function getInstanceEditViewModel($alias = null, $dinamicParams = [], $is_new_record = false, $cache_new_record = true)
    {
        if ($this->_edit_view_is_new_record !== null) {
            $is_new_record = $this->_edit_view_is_new_record;
        }
        if ($this->_edit_view_cache_new_record !== null) {
            $cache_new_record = $this->_edit_view_cache_new_record;
        }

        return EditViewModel::modelR($alias, $dinamicParams, $is_new_record, $cache_new_record);
    }

    public function setEditViewModelMethodParam($method_name, $param)
    {
        $this->_edit_model->{$method_name}($param);

        return $this;
    }

    public function refresh()
    {
        $this->_edit_model->refresh();

        return $this;
    }

    public function refreshMetaData()
    {
        $this->_edit_model->refreshMetaData();

        return $this;
    }

    public function run($action_name, $edit_data)
    {
        switch ($action_name) {
            case self::ACTION_RUN_AUTO :
                $this->actionRunAuto($edit_data);
                break;
            case self::ACTION_ADD_BY_TITLE :
                $this->actionAddByTitle($edit_data);
                break;
        }

        return $this;
    }

    /**
     * actionAddByTitle
     */
    private function actionAddByTitle($edit_data)
    {
        $params = [
            'id'                 => null,
            'parent_copy_id'     => null,
            'parent_data_id'     => null,
            'this_template'      => 0,
            'relate_template'    => 0,
            'block_unique_index' => null,
            'auto_new_card'      => null,
            'unique_index'       => null,
            'params'             => null,
            'block_attributes'   => [
                'block_participant' => [
                    'participant' => [
                        'element_participant' => [
                            [
                                'participant_id' => null,
                                'ug_id'          => WebUser::getUserId(),
                                'ug_type'        => ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                                'responsible'    => "1",
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $edit_data += $params;

        $this->_validate_entity_required = false;

        $this->actionRunAuto($edit_data);

        return $this;
    }

    /**
     * actionRunAuto
     */
    private function actionRunAuto($edit_data)
    {
        $this
            ->setEditData($edit_data)
            ->validateSubModule()
            ->createEditViewModel();

        // если сохраняем
        if (!empty($this->_edit_data['EditViewModel'])) {
            $this->save();

            if ($this->_is_new_record) {
                $this->_edit_model->actionCreateProcessAfterCreatedEntity();
            } else {
                $this->_edit_model->actionCreateProcessAfterChangedEntity();
            }

            // if save
            if ($this->_status == self::STATUS_SAVE) {
                $this->prepareEntity();

                return $this;
            }
        } else {
            $this->loadFromTemplate();
        }

        $this
            ->checkSubscriptionAccess()
            ->markHistoryIsView()
            ->prepareEntity()
            ->prepareHtmlData();

        return $this;
    }

    /**
     * validateSubModule - если создаем карточку из сабмодуля - проверяем
     */
    private function validateSubModule()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        if (isset($this->_edit_data['parent_object']) && $this->_edit_data['parent_object'] == 'sub_module') {
            if (empty($this->_edit_data['id'])) {
                $code_action = ValidateConfirmActions::ACTION_SUB_MODULE_EDIT_VIEW_CREATE;
            } else {
                $code_action = ValidateConfirmActions::ACTION_SUB_MODULE_EDIT_VIEW_EDIT;
            }

            $sub_module_validate = ValidateSubModule::getInstance()->check(array_merge($this->_edit_data, ['copy_id' => $this->_extension_copy->copy_id]), $code_action);
            if ($sub_module_validate->beMessages()) {
                $this->_validate = $sub_module_validate;
                $this->setStatus(self::STATUS_ERROR);

                return $this;
            }
        }

        return $this;
    }

    /**
     * arrayUnique - Проверяет значения массива на уникальность
     */
    private function arrayUnique(array $array_list, array $array_check)
    {
        $result = false;

        if (empty($array_list) || empty($array_check)) {
            return $result;
        }

        foreach ($array_list as $row) {
            $r = 0;
            foreach ($array_check as $row_check) {
                if (in_array($row_check, $row)) {
                    $r++;
                }
            }
            if (count($row) == count($array_check) && $r === count($array_check)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * getEditViewDinamicParams - возвращает динамические параметры для класса EditView
     */
    private function getEditViewDinamicParams($schema_parser, $exception_name_list = [])
    {
        if ($this->_validate_data == false) {
            return [];
        }

        $params = Fields::getInstance()->getActiveRecordsParams($schema_parser);

        if (!empty($this->_dinamic_rules)) {
            if (array_key_exists('rules', $params)) {
                $params['rules'] = array_merge($params['rules'], $this->_dinamic_rules);
            } else {
                $params['rules'] = $this->_dinamic_rules;
            }

            // set unique
            if (!empty($params['rules'])) {
                $rules_list = [];
                foreach ($params['rules'] as $rule) {
                    $u = $this->arrayUnique($rules_list, $rule);
                    if ($u) {
                        continue;
                    }
                    $rules_list[] = $rule;
                }
                $params['rules'] = $rules_list;
            }

        }

        return $params;
    }

    /**
     * createEditViewModel - создание модели
     */
    public function createEditViewModel()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        $schema_parser = $this->_extension_copy->getSchemaParse();

        $alias = 'evm_' . $this->_extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $this->_extension_copy->getTableName(null, false),
            'params'    => $this->getEditViewDinamicParams($schema_parser),
        ];

        if (!$this->_extension_copy->isShowAllBlocks() && Yii::app()->request->getParam('block_unique_index')) {
            //определяем поля, которые не будут проверяться на валидацию для блоков
            $exception_name_list = $this->_extension_copy->getFieldsExceptBlock(Yii::app()->request->getParam('block_unique_index'));
            $schema_parser = $this->_extension_copy->getSchemaParse([], [], $exception_name_list);
            $dinamic_params = [
                'tableName' => $this->_extension_copy->getTableName(null, false),
                'params'    => $this->getEditViewDinamicParams($schema_parser, $exception_name_list),
            ];
        }

        $id = $this->getIdFromEditData();

        if (!empty($id)) {
            $this->_edit_model = $this->getInstanceEditViewModel($alias, $dinamic_params);
            $this->_edit_model = $this->_edit_model->findByPk($id);

            if (!($this->_edit_model)) {
                $this->_validate->addValidateResult('e', Yii::t('messages', 'Data not found'));
                $this->setStatus(self::STATUS_ERROR);
                $this->_data_not_found = true;

                return $this;
            }
            $this->_data_not_found = false;
            $this->_edit_model->scenario = 'update';
        } else {
            $this->_edit_model = $this->getInstanceEditViewModel($alias, $dinamic_params, true);
            $this->_edit_model->setIsNewRecord(true);
            $this->_edit_model->scenario = 'edit';
        }

        if (!empty($this->_edit_data['pci'])) {
            $this->_edit_model->setPci($this->_edit_data['pci']);
        }
        if (!empty($this->_edit_data['pdi'])) {
            $this->_edit_model->setPdi($this->_edit_data['pdi']);
        }

        $this->_is_new_record = $this->_edit_model->getIsNewRecord();

        $this->_edit_model->setElementSchema($schema_parser);
        $this->_edit_model->extension_copy = $this->_extension_copy;
        $this->_edit_model->setMakeLogging($this->_make_logging);
        $this->prepareValidateEntityRequired();

        return $this;
    }

    /**
     * prepareValidateEntityRequired
     */
    private function prepareValidateEntityRequired()
    {
        if ($this->_validate_entity_required) {
            return $this;
        }

        if ($this->_edit_model == false) {
            return $this;
        }

        $this->_edit_model->removeRuleRequiredAll();

        return $this;
    }

    /**
     * save - сохраняем
     */
    public function save()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        if ($this->getIsNewRecord() && in_array($this->_extension_copy->extension_id, [ExtensionModel::MODULE_USERS, ExtensionModel::MODULE_STAFF])) {
            $params_model = ParamsModel::model()->findByAttributes(['title' => 'license_users_limit']);

            if ($params_model !== null) {
                $maxUsersCount = $params_model->value;

                if ($maxUsersCount > 0) {
                    $usersCount = UsersModel::model()->count();

                    if ($usersCount >= $maxUsersCount) {
                        $this->_validate->addValidateResult('e', Yii::t(
                            'messages',
                            'Licenses number exceeded. Maximum number of users allowed - {s}',
                            ['{s}' => $maxUsersCount]
                        ));

                        $this->setStatus(self::STATUS_ERROR);

                        return $this;
                    }
                }
            }
        }

        //если родительская карточка
        if (isset($this->_edit_data['this_template'])) {
            if (isset($this->_edit_data['relate_template']) && (boolean)$this->_edit_data['relate_template'] == true && $this->_edit_data['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE) {
                $this->_edit_data['this_template'] = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM;
            }
            $this->_edit_data['EditViewModel']['this_template'] = $this->_edit_data['this_template'];
        }

        $this->_edit_model->setPrimaryEntities(!empty($this->_edit_data['primary_entities']) ? $this->_edit_data['primary_entities'] : null);
        $this->_edit_model->setMyAttributes($this->_edit_data['EditViewModel']);
        $this->_edit_model->setDataBlockAttributes(!empty($this->_edit_data['block_attributes']) ? $this->_edit_data['block_attributes'] : null);

        // SAVE
        if ($this->_edit_model->save()) {
            $this->refresh();
            $this->refreshMetaData();
            $this->setStatus(self::STATUS_SAVE);
            $this->_id = $this->_edit_model->qwe_primary_key;

            // for Edinstvo
            if (!empty($this->_extension_copy->copy_id)) {
                $parent_data = [
                    'parent_copy_id' => (!empty($this->_edit_data['parent_copy_id'])) ? $this->_edit_data['parent_copy_id'] : null,
                    'parent_data_id' => (!empty($this->_edit_data['parent_data_id'])) ? $this->_edit_data['parent_data_id'] : null,
                ];

                $linked_cards = ((!empty($this->_edit_data['auto_new_card'])) && ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS)) ? [$this->_id, $this->_edit_data['auto_new_card']] : [];
                $params = \AdditionalProccessingModel::getInstance()->afterSave($parent_data, $this->_extension_copy->copy_id, $this->_id, $this->_is_new_record, $linked_cards);
                if (!empty($params['auto_next_card']) && $params['auto_next_card'] === true && !empty(Yii::app()->request->getParam('params')['access_auto_next_card']) && Yii::app()->request->getParam('params')['access_auto_next_card'] == true) {
                    $this->setNextCard(\ExtensionCopyModel::MODULE_DOCUMENTS);
                }
                if (!empty($params['ev_refresh_fields'])) {
                    $this->setEVRefreshFields($params['ev_refresh_fields']);
                }
            }

        } else {
            $this->setStatus(self::STATUS_SAVE_ERROR);
        }

        return $this;
    }

    /**
     * loadFromTemplate - копирование данных из шаблона
     */
    private function loadFromTemplate()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        if (
            $this->_controller->module->isTemplate($this->_extension_copy) &&
            !$this->_edit_model->isNewRecord &&
            isset($this->_edit_data['from_template']) &&
            (boolean)$this->_edit_data['from_template'] == true) {
            $this->_template_data_id = $this->_edit_model->getPrimaryKey();
            $this->_edit_model->setPrimaryEntities($this->_edit_data['primary_entities']);
            $this->_edit_model->copyFilesFromTemplate();
            $this->_edit_model->copyActivityFromTemplate();
        }

        return $this;
    }

    /**
     * checkSubscriptionAccess - проверка доступа на изменение подписчиков
     */
    public function checkSubscriptionAccess()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        //проверка доступа на изменение подписчиков
        if (!$this->_edit_model->isNewRecord && $this->_extension_copy->isParticipant()) {
            if (!ParticipantModel::model()->checkUserSubscription(
                $this->_extension_copy->copy_id,
                $this->_edit_model->{$this->_extension_copy->prefix_name . '_id'},
                $this->_edit_model)
            ) {
                $this->_validate->addValidateResult('e', Yii::t('messages', 'Access denied! You are not a owner or member'));
                $this->setStatus(self::STATUS_ERROR);

                return $this;
            }
        }

        return $this;
    }

    /**
     * findAndMarkHistoryIsView - отметка о прочтении задачи пользователем из зне
     */
    public function findAndMarkHistoryIsView($data_id)
    {
        if (!$data_id) {
            return;
        }

        $this->markHistoryIsView($data_id);
    }

    /**
     * markHistoryTaskIsView - отметка о прочтении задачи пользователем
     */
    public function markHistoryIsView($data_id = null)
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        if ($data_id === null && $this->_edit_model->getIsNewRecord() == false) {
            $data_id = $this->_edit_model->getPrimaryKey();
        }

        if (!empty($data_id)) {
            if ($this->_extension_copy->getAttribute('copy_id') == ExtensionCopyModel::MODULE_TASKS) {
                TaskModel::markTaskIsView($data_id);
            }

            History::markHistoryIsView($this->_extension_copy->getAttribute('copy_id'), $data_id);
        }

        return $this;
    }

    /**
     * prepareHtmlData - подготавливает верстку EditView
     *
     * @param $edit_view_builder - 'EditViewBuilder' class name
     */
    public function prepareHtmlData()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        // вывод результата в виде html страницы для EditView
        $parent_copy_id = ['pci' => '', 'parent_copy_id' => ''];
        $parent_data_id = ['pdi' => '', 'parent_data_id' => ''];
        foreach ($this->_edit_data as $key => $value) {
            if ($key == 'pci' || $key == 'parent_copy_id') {
                $parent_copy_id[$key] = $value;
            }
            if ($key == 'pdi' || $key == 'parent_data_id') {
                $parent_data_id[$key] = $value;
            }
        }

        $this->setStatus(self::STATUS_DATA);

        $edit_view_builder = $this->getEditViewBuilder();
        if ($edit_view_builder === null) {
            $edit_view_builder = new EditViewBuilder();
        }

        $block_unique_index = $this->getBlockUniqueIndex();
        $data_from_template = Yii::app()->request->getParam('data_from_template');
        $this->_html_data = [
            'edit_view_action_model' => $this,
            'entity_model'           => $this->_entity_model,
            'extension_copy'         => $this->_extension_copy,
            'extension_data'         => $this->_edit_model,
            'parent_copy_id'         => $parent_copy_id,
            'parent_data_id'         => $parent_data_id,
            'pci'                    => \Yii::app()->request->getParam('pci'),
            'pdi'                    => \Yii::app()->request->getParam('pdi'),
            'this_template'          => (isset($this->_edit_data['this_template']) ? $this->_edit_data['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
            'relate_template'        => (isset($this->_edit_data['relate_template']) ? $this->_edit_data['relate_template'] : '0'),
            'template_data_id'       => $this->_template_data_id,
            'auto_new_card'          => (!empty($this->_edit_data['auto_new_card'])) ? $this->_edit_data['auto_new_card'] : '',
            'block_unique_index'     => $block_unique_index,
            'id'                     => (!empty($this->_edit_data['id']) && $this->_edit_data['id'] != 'new' ? $this->_edit_data['id'] : null),
            'parent_header'          => \Yii::app()->request->getParam('parent_header'),
            'content'                => $edit_view_builder
                ->setDataId((!empty($this->_edit_data['id']) && $this->_edit_data['id'] != 'new' ? $this->_edit_data['id'] : null))
                ->setRelate((!empty($this->_edit_data['element_relate']) ? $this->_edit_data['element_relate'] : null))
                ->setPrimaryEntities((!empty($this->_edit_data['primary_entities']) ? $this->_edit_data['primary_entities'] : null))
                ->setThisTemplate((isset($this->_edit_data['this_template']) ? $this->_edit_data['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE))
                ->setParentCopyId($parent_copy_id)
                ->setParentDataId($parent_data_id)
                ->setParentRelateDataList((array_key_exists('parent_relate_data_list', $this->_edit_data) ? $this->_edit_data['parent_relate_data_list'] : null))
                ->setDefaultData((!empty($this->_edit_data['default_data']) ? $this->_edit_data['default_data'] : null))
                ->setExtensionCopy($this->_extension_copy)
                ->setExtensionData($this->_edit_model)
                ->setBlockUniqueIndex($block_unique_index)
                ->setBlockSelect((!empty($this->_edit_data['auto_new_card']) && $block_unique_index) ? $block_unique_index : false)
                ->setDefaultDataFromLinkedCard((!empty($this->_edit_data['auto_new_card'])) ? $this->_edit_data['auto_new_card'] : false)
                ->setDefaultDataFromTemplate((!empty($data_from_template)) ? true : false)
                ->setTypeCommentList($this->getTypeCommentList())
                ->buildEditViewPage($this->getExtensionCopySchema()),
        ];

        return $this;
    }

    private function getTypeCommentList()
    {
        return ActivityMessagesModel::getTypeCommentList($this->_extension_copy->copy_id, $this->getEditModel()->blockActivityOnlyContentGeneral());
    }

    /**
     * возвращает список полей, только для просмотра
     */
    private function getReadOnlyFields()
    {
        $result = [];
        $schema = $this->_extension_copy->getSchemaParse();
        if (!empty($schema)) {
            foreach ($schema['elements'] as $value) {
                if (isset($value['field'])) {
                    if (!empty($value['field']['params']['read_only'])) {
                        $result[] = $value['field']['params']['name'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * getEditViewHtml - возвращает страницу EditView
     */
    private function getEditViewHtml()
    {
        if ($this->isBadStatus() === true) {
            return $this;
        }

        return $this->_controller->renderPartial(ViewList::getView('site/editView'), $this->_html_data, true);
    }

    /**
     * Возвращает уникальный индекс блока, который показываем
     */
    private function getBlockUniqueIndex()
    {
        $show_only_block = false;
        if (!$this->_extension_copy->isShowAllBlocks()) {
            $block_field_data = $this->_extension_copy->getFieldBlockData();

            if ($block_field_data) {
                if (isset($this->_edit_model->{$block_field_data['name']})) {
                    $show_only_block = $this->_edit_model->{$block_field_data['name']};
                } else {
                    if (isset($this->_edit_data['default_data'][$block_field_data['name']])) {
                        $show_only_block = $this->_edit_data['default_data'][$block_field_data['name']];
                    }
                }

            }
        }

        return $show_only_block;
    }

    /**
     *  setStatus
     */
    private function setStatus($status)
    {
        $this->_status = $status;

        return $this;
    }

    /**
     * isBadStatus - возвращает статус наличия ошибок. true - ошибки присутствуют
     */
    public function isBadStatus()
    {
        $result = false;
        if (in_array($this->_status, [self::STATUS_ERROR])) {
            $result = true;
        }

        return $result;
    }

    /**
     * getChildListEntities - возвращает массив ContentReload для перехода на страницу подчиненного модуля
     */
    private function getChildListEntities()
    {
        if ($this->_controller->module->checkAutoShowChildListEntitiesPf() == false) {
            return false;
        }

        if ($this->getIsNewRecord() == false) {
            return false;
        }

        $primary_params = $this->_extension_copy->getPrimaryField();
        if ($primary_params == false || $primary_params['params']['type'] != \Fields::MFT_RELATE_STRING) {
            return false;
        }

        $reload_model = (new \ContentReloadModel(8))
            ->addVars(
                [
                    'module' =>
                        [
                            'copy_id' => $primary_params['params']['relate_module_copy_id'],
                            'params'  => [
                                'pci' => $this->_extension_copy->copy_id,
                                'pdi' => $this->_id
                            ]
                        ]
                ]
            )
            ->prepare();

        return [
            'action_key' => $reload_model->getKey(),
            'vars'       => $reload_model->getContentVars(false)[$reload_model->getKey()],
        ];
    }

    /**
     * Созвращает данные чеоновика, если они есть
     *
     * @return array|null
     */
    protected function getDraft()
    {
        $uid = ModuleEntityUid::generate(
            $this->_extension_copy->copy_id,
            $this->getEditModel()->getPrimaryKey()
        );

        $data = null;

        try {
            $data = (new DraftManager())->getByUid($uid);
        } catch (\Exception $e) {
        }

        return $data;
    }

    /**
     * getResult - возвращает результат
     */
    public function getResult($render_json = true)
    {
        $result = [];

        switch ($this->_status) {
            case self::STATUS_DATA :
            case self::STATUS_SAVE_ERROR :
                $result = [
                    'status'   => self::STATUS_DATA,
                    'copy_id'  => $this->_extension_copy->copy_id,
                    'data'     => $this->getEditViewHtml(),
                    'readonly' => $this->getReadOnlyFields(),
                    'entity'   => EntityModel::getEntityProperties(),
                    'draft'    => $this->getDraft(),
                ];
                break;

            case self::STATUS_SAVE :

                if (!empty($this->_edit_data['params']) && !empty($this->_edit_data['EditViewModel'])) {
                    if (array_key_exists('default_data', $this->_edit_data['params'])) {
                        foreach ($this->_edit_data['params']['default_data'] as $k => $v) {
                            if (empty($v)) {
                                if (array_key_exists($k, $this->_edit_data['EditViewModel'])) {
                                    $this->_edit_data['params']['default_data'][$k] = $this->_edit_data['EditViewModel'][$k];
                                } else {
                                    //relate
                                    if (!empty($this->_edit_data['element_relate'])) {
                                        foreach ($this->_edit_data['element_relate'] as $value) {
                                            if ($value['name'] == 'EditViewModel[' . $k . ']') {
                                                $this->_edit_data['params']['default_data'][$k] = $value['id'];
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $result = [
                    'status'                   => self::STATUS_SAVE,
                    'id'                       => $this->_id,
                    'auto_next_card'           => $this->_auto_next_card,
                    'params'                   => (!empty($this->_edit_data['params'])) ? $this->_edit_data['params'] : '',
                    'attributes_data'          => ($this->_edit_model ? $this->_edit_model->getAttributes() : null),
                    'ev_refresh_field'         => $this->_ev_refresh_field,
                    'show_child_list_entities' => $this->getChildListEntities(),
                    'entity'                   => EntityModel::getEntityProperties(),
                ];
                break;

            case self::STATUS_ERROR :
                $result = [
                    'status'   => self::STATUS_ERROR,
                    'messages' => $this->_validate->getValidateResultHtml()
                ];
                break;
        }

        // return
        if ($render_json == true) {
            return $this->_controller->renderJson($result);
        } else {
            return $result;
        }
    }







    //*****************************************************
    // ENTITY
    //*****************************************************

    /**
     * prepareEntity - установка параметров EntityModel
     *
     * @return $this
     */
    private function prepareEntity()
    {
        $vars = [
            'copy_id'         => $this->_extension_copy->copy_id,
            'id'              => ($this->_edit_model ? $this->_edit_model->getPrimaryKey() : ($this->getId() ? $this->getId() : null)),
            'pci'             => (array_key_exists('pci', $this->_edit_data) ? $this->_edit_data['pci'] : null), // для старой версии
            'pdi'             => (array_key_exists('pdi', $this->_edit_data) ? $this->_edit_data['pdi'] : null), // для старой версии
            'this_template'   => (array_key_exists('this_template', $this->_edit_data) ? $this->_edit_data['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
            'finished_object' => (array_key_exists('finished_object', $this->_edit_data) ? $this->_edit_data['finished_object'] : null),
        ];

        $entity_vars_model = new \EntityVarsModel();

        $this->_entity_model = (new EntityModel(true))
            ->setElementType(\EntityElementTypeModel::TYPE_EDIT_VIEW)
            ->setParentKey($entity_vars_model->getParentKey());

        $this->_entity_model
            ->setLastParentKey($this->_entity_model->getKey())
            ->setParentEventId($entity_vars_model->getParentEventId())
            ->setVars($entity_vars_model->prepareModuleVars($vars)->getVars())
            ->setEvents($this->getEntityEvents())
            ->setCallbacks($this->getEntityCallbacks())
            ->resetToEntityProperties();

        return $this;
    }

    /**
     * getEntityEvents
     */
    protected function getEntityEvents()
    {
        $events = [
            ['.edit_view_btn-save', EntityEventsModel::EVENT_CLICK, ['EditView', 'entity', 'save'], ['event_id' => \EntityEventsModel::EID_EDIT_VIEW_SAVE]],
        ];

        if (($event_id = $this->_entity_model->getParentEventId()) == false) {
            return $events;
        }

        switch ($event_id) {
            case EntityEventsModel::EID_LIST_VIEW_SDM_EDIT :
                $events = [
                    ['.edit_view_btn-save', EntityEventsModel::EVENT_CLICK, ['EditView', 'entity', 'saveSdm'], ['event_id' => \EntityEventsModel::EID_LIST_VIEW_SAVE]],
                ];
                break;

        }

        return $events;
    }

    /**
     * getEntityCallbacks
     */
    protected function getEntityCallbacks()
    {
        $callbacks = [];

        if (($event_id = $this->_entity_model->getParentEventId()) == false) {
            return $callbacks;
        }

        switch ($event_id) {
            case EntityEventsModel::EID_EDIT_VIEW_SDM_ADD :
            case EntityEventsModel::EID_EDIT_VIEW_SDM_EDIT :
            case EntityEventsModel::EID_LIST_VIEW_SDM_ADD :
                $callbacks = [
                    ['destroy', ['EditView', 'entity', 'reloadParentRelateIfExistValue']],
                ];
                break;
        }

        return $callbacks;
    }

}
