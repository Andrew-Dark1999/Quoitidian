<?php

class EditViewModel extends ActiveRecordClone
{

    const THIS_TEMPLATE_MODULE = "0"; // обычные карточки
    const THIS_TEMPLATE_TEMPLATE = "1"; // шаблоны
    const THIS_TEMPLATE_TEMPLATE_CM = "2"; // СМ - шаблоны

    const LINK_TG_EDIT_VIEW = 'tg_edit_view';
    const LINK_TG_MODULE = 'tg_module';
    const LINK_TG_MODULE_PCI = 'tg_module_pci';
    const LINK_TG_MODULE_PROCESS = 'tg_module_process';

    const POT_MODE_NONE = "0";
    const POT_MODE_RUNNING = "1";

    // data block attributes
    const DBA_BLOCK_PARTICIPANT = 'block_participant';

    // ІД записи после сохранения
    public $qwe_primary_key;

    //параметры, переданые для сохранения
    private $_params = [];

    // указывает, что дата завершения изменена
    private $_date_ending_changed = null;

    // указывает, что статус изменен
    private $_status_changed = null;

    // разрешение на установку записи связи между главним и подчиненниым модулем, связаных по полю Название
    private $_set_relate_form_parent = true;

    // primary_entities
    private $_primary_entities;

    // указывает на изменение первичного поля модуля
    private $_primary_entity_changed = false;

    private $user_model = null;

    public $_history_add_files = [];

    public $_history_add_rules = [];

    // пропускаем элементы СДМ первого модуля        
    public $miss_first_module = true;

    // загруженые файлы
    private $_files = [];

    // схема елементов полей
    public $_schema_fields = [];

    // Указывает, что надо создать копии файлов
    public $copy_files = false;

    // Указывает, что надо создать копии участников
    public $copy_participant = false;

    // Модель участников для копирования
    public $copy_participant_model = null;

    // Указывает, что надо создать копии активности
    public $copy_activity = false;

    // Модель активности для копирования
    public $copy_activity_model = null;

    // Использовать только список ИД  template_activity_id_list при отборе уведомлений активности
    public $use_only_template_activity_id_list = false;

    // Список ИД уведомлений активности, что были созданы из шаблона
    public $template_activity_id_list = [];

    // Логирование удаления
    private $_logging_remove = true;

    // Разрешает удаление участников перед вставкой новых
    private $_delete_participant = true;

    // Разрешает выполненине процесса после сохранения карточки
    private $_switch_run_process = true;

    // Указывает, что при создании(копировании, изменении) ответсвенным должен стать Активный пользователь
    private $_set_responsible_is_active_user = false;

    // Указывает, что можно урезать длинные названия значений
    private $_truncate_long_value = false;

    // Список названий полей, в кторых были урезаны значения
    private $_truncated_long_fields = [];

    // Данные из блоков (Блок участники и др..)
    private $_data_block_attributes = [];

    //
    private $_pci;

    //
    private $_pdi;

    /**
     * @var ExtensionCopyModel
     */
    public $extension_copy;

    private $_make_logging = true;                  // делает логирование

    private $_pot_mode = self::POT_MODE_NONE;   // process operation task && agreetment

    public static function modelR($alias = null, $dinamicParams = [], $is_new_record = false, $cache_new_record = true, $className = __CLASS__)
    {
        return parent::modelR($alias, $dinamicParams, $is_new_record, $cache_new_record, $className);
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->extension_copy = $extension_copy;

        return $this;
    }

    /**
     * @return ExtensionCopyModel
     */
    public function getExtensionCopy()
    {
        return $this->extension_copy;
    }

    public function setPrimaryEntities($primary_entities)
    {
        $this->_primary_entities = $primary_entities;

        return $this;
    }

    public function setResponsibleIsActiveUser($param)
    {
        $this->_set_responsible_is_active_user = $param;

        return $this;
    }

    public function setLoggingRemove($logging_remove)
    {
        $this->_logging_remove = $logging_remove;

        return $this;
    }

    public function setMakeLogging($make_logging)
    {
        $this->_make_logging = $make_logging;

        return $this;
    }

    public function setPotMode($pot_mode)
    {
        $this->_pot_mode = $pot_mode;

        return $this;
    }

    public function setSwitchRunProcess($switch_run_process)
    {
        $this->_switch_run_process = $switch_run_process;

        return $this;
    }

    public function setDeleteParticipant($delete_participant)
    {
        $this->_delete_participant = $delete_participant;

        return $this;
    }

    private function isSetPotMode($pot_mode)
    {
        return ($this->_pot_mode == $pot_mode ? true : false);
    }

    public function setTruncateLongValue($truncate_long_value)
    {
        $this->_truncate_long_value = $truncate_long_value;

        return $this;
    }

    public function getTruncatedLongFields()
    {
        return $this->_truncated_long_fields;
    }

    public function setDataBlockAttributes($data_block_attributes)
    {
        $this->_data_block_attributes = $data_block_attributes;

        return $this;
    }

    private function getDataBlockAttributes($data_block_attributes_name)
    {
        if (!empty($this->_data_block_attributes[$data_block_attributes_name])) {
            return $this->_data_block_attributes[$data_block_attributes_name];
        }
    }

    private function getAttributesBlockParticipant($data_block_attributes_name, $data_type)
    {
        $block_attributes = $this->getDataBlockAttributes($data_block_attributes_name);
        if ($block_attributes == false) {
            return;
        }

        if (!empty($block_attributes[$data_type])) {
            return $block_attributes[$data_type];
        }
    }

    public function setPci($pci)
    {
        $this->_pci = $pci;

        return $this;
    }

    public function getPci()
    {
        return $this->_pci;
    }

    public function setPdi($pdi)
    {
        $this->_pdi = $pdi;

        return $this;
    }

    public function getPdi()
    {
        return $this->pdi;
    }

    public static function findEntity($copy_id, $data_id)
    {
        $edit_view_model = (new EditViewActionModel($copy_id))
            ->setEditData(['id' => $data_id])
            ->createEditViewModel()
            ->getEditModel();

        return $edit_view_model;
    }

    public function hasBpmOperation()
    {
        return $this->hasAttribute('is_bpm_operation');
    }

    public function blockActivityOnlyContentGeneral()
    {
        $is_bpm_operation = $this->getAttribute('is_bpm_operation');

        if ($is_bpm_operation === null) {
            return false;
        }
        if ($is_bpm_operation === "1") {
            return true;
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        $process_model = \Process\models\ProcessModel::model()->with('operations')->find([
            'condition' => 'operations.copy_id = ' . $this->extension_copy->copy_id . ' AND card_id = ' . $this->getPrimaryKey()
        ]);

        if ($process_model && $process_model->this_template) {
            return true;
        }

        return false;
    }

    /**
     *  условие отбора данных шаблoна
     */
    public function scopesThisTemplate($this_template = null)
    {
        if ($this_template === null) {
            return $this;
        }

        $this->getDbCriteria()->mergeWith([
            'condition' => 'this_template=:this_template',
            'params'    => [':this_template' => (string)(integer)$this_template],
        ]);

        return $this;
    }

    /**
     *  условие отбора данных шаблoна
     */
    public function scopesWithOutBplOperation()
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => '(is_bpm_operation is NULL OR is_bpm_operation = "0")',
        ]);

        return $this;
    }

    /**
     * @param boolean $primary_entity_changed
     */
    public function setPrimaryEntityChanged($primary_entity_changed)
    {
        $this->_primary_entity_changed = $primary_entity_changed;

        return $this;
    }

    /**
     *
     */
    public function setQwePrimaryKey()
    {
        $this->qwe_primary_key = !$this->isNewRecord ? $this->getPrimaryKey() : null;
    }

    public function getDefaultModuleTitle()
    {
        return \Yii::t('messages', 'New record');
    }

    public function isThisTemplate()
    {
        if ($this->hasAttribute('this_template') == false) {
            return false;
        }

        if ($this->this_template == static::THIS_TEMPLATE_TEMPLATE || $this->this_template == static::THIS_TEMPLATE_TEMPLATE_CM) {
            return true;
        }

        return false;
    }

    /**
     * возвращает текстовое значение поля Название
     * $return_first_field - возвращает только первое поле
     */
    public function getModuleTitle($extension_copy = null, $return_first_field = true)
    {
        $result = '';
        if (!empty($extension_copy)) {
            $pm_schema = $extension_copy->getPrimaryField(null, $return_first_field);
        } else {
            $pm_schema = $this->extension_copy->getPrimaryField(null, $return_first_field);
        }
        if (empty($pm_schema)) {
            return $result;
        }

        if ($return_first_field) {
            return $this->{$pm_schema['params']['name']};
        } else {
            $full_title = [];
            foreach ($pm_schema as $schema) {
                $full_title[] = $this->{$schema['params']['name']};
            }

            return implode(' ', $full_title);
        }
    }

    /**
     * Проверка связаный данных на обезательность
     */
    public function relateCheckRequired($attribute, $params)
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return true;
        }

        $check_field = false;
        if (empty($_POST['element_relate'])) {
            $this->addError($attribute, Yii::t('messages', '{s} cannot be blank', ['{s}' => $this->getAttributeLabel($attribute)]));

            return;
        } else {
            foreach ($_POST['element_relate'] as $relate) {
                if ($relate['name'] == 'EditViewModel[' . $attribute . ']') {
                    if (!empty($relate['id']) || (isset($relate['disabled']) && $relate['disabled'])) {
                        $check_field = true;
                        break;
                    }
                }
            }
        }

        if ($check_field == false) {
            $this->addError($attribute, Yii::t('messages', '{s} cannot be blank', ['{s}' => $this->getAttributeLabel($attribute)]));

            return;
        }

        return true;
    }

    /**
     * Проверка файла на обезательность
     */
    public function fileCheckRequired($attribute, $params)
    {
        $fileList = !empty($this->_files[$attribute]) ? (array)$this->_files[$attribute] : null;
        if (!$fileList) {
            return true;
        }

        foreach ($fileList as $uploadsModelId) {
            if (!$uploadsModelId) {
                $this->addError($attribute, ['file_name' => '', 'message' => Yii::t('messages', 'You need to download file in a field "{s}"', ['{s}' => $this->getAttributeLabel($attribute)])]);

                return false;
            }
        }

        return true;
    }

    /**
     * Проверка файла
     */
    public function fileCheck($attribute, $params)
    {
        $fileList = !empty($this->_files[$attribute]) ? (array)$this->_files[$attribute] : null;
        if (!$fileList) {
            return true;
        }

        foreach ($fileList as $uploadsModelId) {
            $uploadModel = UploadsModel::model()->findByPk($uploadsModelId);

            if (!$uploadModel) {
                $this->addError($attribute, ['message' => Yii::t('messages', 'File #{s} not found', ['{s}' => $uploadsModelId])]);
                continue;
            }

            //file_types
            $file_types = $this->_schema_fields[$attribute]['params']['file_types'];
            if ($file_types !== null || $file_types != '*') {
                if (is_array($file_types) && !in_array(mb_strtolower($uploadModel->getFileType()), $file_types)) //$this->addError($attribute, 'Недопустимый тип файла "' . $value->getFileType() . '"');
                {
                    $this->addError($attribute, ['file_name' => $uploadModel->getFileName(), 'message' => Yii::t('messages', 'Invalid file type "{s}"', ['{s}' => $uploadModel->getFileType()])]);
                }
            }
            //file_min_size
            if ($file_min_size = $this->_schema_fields[$attribute]['params']['file_min_size']) {
                if ((integer)$file_min_size > 0 && $file_min_size > $uploadModel->getFileSize()) {
                    $this->addError($attribute, ['file_name' => $uploadModel->getFileName(), 'message' => Yii::t('messages', 'File size {s1} bytes less for the minimum allowable size in bytes {s2}', ['{s1}' => $uploadModel->getFileSize(), '{s2}' => $file_min_size])]);
                }
            }
            //file_max_size
            if ($file_max_size = $this->_schema_fields[$attribute]['params']['file_max_size']) {
                if ((integer)$file_max_size > 0 && $file_max_size < $uploadModel->getFileSize()) {
                    $this->addError($attribute, ['file_name' => $uploadModel->getFileName(), 'message' => Yii::t('messages', 'File size {s1} bytes more for the maximum allowable size in bytes {s2}', ['{s1}' => $uploadModel->getFileSize(), '{s2}' => $file_max_size])]);
                }
            }
        }

        return true;
    }

    public function responsibleCheck()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return true;
        }
        if (empty($this->extension_copy) && !($this->extension_copy->isResponsible() || $this->extension_copy->isParticipant())) {
            return true;
        }

        $field_name = '';

        $participant_attributes = $this->getAttributesBlockParticipant(self::DBA_BLOCK_PARTICIPANT, ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT);
        $element_participant_id = (!empty($participant_attributes['element_participant_id']) ? $participant_attributes['element_participant_id'] : null);
        $element_participant = (!empty($participant_attributes['element_participant']) ? $participant_attributes['element_participant'] : null);

        if ($this->extension_copy->isResponsible()) {
            if ($element_participant_id) {
                foreach ($element_participant_id as $item) {
                    if (!empty($item['responsible']) && $item['responsible'] == '1') {
                        return true;
                    }
                }
            }
            $field_name = $this->extension_copy->getFieldSchemaParamsByType('relate_participant')['params']['name'];
        } else {
            if ($this->extension_copy->isParticipant()) {
                if ($element_participant_id) {
                    foreach ($element_participant_id as $item) {
                        if (!empty($item['responsible']) && $item['responsible'] == '1') {
                            return true;
                        }
                    }

                    if ($element_participant) {
                        foreach ($element_participant as $item) {
                            if (!empty($item['responsible']) && $item['responsible'] == '1') {
                                return true;
                            }
                        }
                    }
                }
                $field_name = $this->extension_copy->getFieldSchemaParamsByType('relate_participant')['params']['name'];
            }
        }

        $this->addError($field_name, Yii::t('messages', 'You must appoint a responsible'));

        return false;
    }

    public function setElementSchema($schema)
    {
        if (!isset($schema['elements'])) {
            return this;
        }
        foreach ($schema['elements'] as $value) {
            if (isset($value['field'])) {
                $this->_schema_fields[$value['field']['params']['name']] = $value['field'];
            }
            /*if(isset($value['sub_module']))
                $this->_schema_fields[$value['sub_module']['params']['name']] = $value;   
                */
        }

        return $this;
    }

    public function setMyAttributes($params)
    {
        if ($params == false) {
            return;
        }

        $this->_history_add_rules = [];
        $this->_params = $params;

        foreach ($params as $field_name => $value) {
            $field_params = [];

            if (array_key_exists($field_name, $this->_schema_fields)) {
                $field_params = $this->_schema_fields[$field_name];
            }

            if (
                ($field_params && in_array($field_params['params']['type'], [Fields::MFT_RELATE])) ||
                !$this->hasAttribute($field_name)
            ) {
                continue;
            }

            //change rule view for module
            if (
                $this->_make_logging &&
                $field_params &&
                $field_params['params']['type'] == 'select' &&
                $field_params['params']['type_view'] == Fields::TYPE_VIEW_DEFAULT &&
                (
                    $field_params['params']['name'] == 'rule_view' ||
                    $field_params['params']['name'] == 'rule_edit' ||
                    $field_params['params']['name'] == 'rule_delete' ||
                    $field_params['params']['name'] == 'rule_import' ||
                    $field_params['params']['name'] == 'rule_export'

                )
            ) {

                if ($this->{$field_params['params']['name']} != $value) {
                    if (isset($this->_schema_fields['access_id']) && isset($this->_schema_fields['access_id_type'])) {
                        $schema = $this->_schema_fields['access_id_type'];
                        if ($this->{$schema['params']['name']} > 1) { //only modules
                            $schema = $this->_schema_fields['access_id'];
                            $ec = ExtensionCopyModel::model()->findByPk($this->{$schema['params']['name']});
                            if ($ec) {
                                $history_rules_data = [
                                    '{module_title}' => $ec->getAttribute('title'),
                                    '{user_id}'      => WebUser::getUserId(),
                                    '{copy_id}'      => $ec->getPrimaryKey()
                                ];
                                if ($field_params['params']['name'] == 'rule_view') {
                                    if ($value > 1) {  //disabled
                                        $this->_history_add_rules[HistoryMessagesModel::MT_DISABLE_MODULE_ACCESS] = $history_rules_data;
                                    } else {         //enabled
                                        $this->_history_add_rules[HistoryMessagesModel::MT_ENABLE_MODULE_ACCESS] = $history_rules_data;
                                    }
                                } else {
                                    $this->_history_add_rules[HistoryMessagesModel::MT_CHANGED_MODULE_ACCESS] = $history_rules_data;
                                }
                            }
                        }
                    }

                }
            }

            // дата завершения
            if ($field_params && $field_params['params']['type'] == 'datetime' && $field_params['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                if (is_array($value) && array_key_exists('all_day', $value)) {
                    $this->{$field_name . '_ad'} = $value['all_day'];
                }

                if (is_array($value) && array_key_exists('date_time', $value)) {
                    $value = trim($value['date_time']);
                } else {
                    $value = trim($value);
                }

                if ($value == false) {
                    $value = date('Y-m-d H:i:s');
                }

                if ($value && $this->{$field_name . '_ad'}) {
                    $value = date('Y-m-d 23:59:59', strtotime($value));
                }

                // для процесов
                if (!$this->isNewRecord || $this->isSetPotMode(self::POT_MODE_RUNNING)) {
                    if (date('Y-m-d', strtotime($this->{$field_params['params']['name']})) != date('Y-m-d', strtotime($value))) {
                        $this->_date_ending_changed = $value;
                    }
                }
            }

            //datetime
            if ($field_params && $field_params['params']['type'] == 'datetime') {
                $value = trim($value);
                if (empty($value)) {
                    $value = null;
                } elseif (strtotime($value)) {
                    $value = date('Y-m-d H:i:s', strtotime($value));
                } else {
                    $value = null;
                }
            }

            if ($field_params && $field_params['params']['type'] == 'select' && $field_params['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS) {
                if (!$this->isNewRecord || $this->isSetPotMode(self::POT_MODE_RUNNING)) {
                    if ($this->{$field_params['params']['name']} != $value) {
                        if (isset($field_params['params']['values'][$value])) {
                            $this->_status_changed = $field_params['params']['values'][$value];
                        }
                    }
                }
            }

            if ($field_params && $field_params['params']['type'] == Fields::MFT_NUMERIC) {
                if (!empty($value)) {
                    $value = str_replace(' ', '', $value);
                }
            }

            if ($field_params && $field_params['params']['type'] == 'access') {
                if (empty($value)) {
                    $this->{$field_name . '_type'} = null;
                    $value = null;
                } else {
                    if (is_array($value)) {
                        $this->{$field_name . '_type'} = $value['type'];
                        $value = $value['id'];
                    }
                }
            }

            // files
            if (($field_params && ($field_params['params']['type'] == 'file' || $field_params['params']['type'] == 'file_image' || $field_params['params']['type'] == 'attachments')) || $field_name == 'ehc_image1') {
                $this->_files[$field_name] = $value;
                continue;
            }

            if (empty($value) && $value === '') {
                $value = null;
            }

            $this->$field_name = $value;
        }

    }

    /**
     *  копирование файлов
     */
    private function copyFiles()
    {
        foreach ($this->getAttributes() as $key => $value) {
            $field_params = [];
            if (isset($this->_schema_fields[$key])) {
                $field_params = $this->_schema_fields[$key];
            }
            if ((!empty($field_params) && ($field_params['params']['type'] == 'file' || $field_params['params']['type'] == 'file_image' || $field_params['params']['type'] == 'attachments')) || $key == 'ehc_image1') {
                if (empty($value)) {
                    continue;
                }
                $upload_model_data = new UploadsModel();
                $upload_model_data = $upload_model_data->setRelateKey($value)->findAll();
                $relate_key = md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $key);

                foreach ($upload_model_data as $upload_model) {
                    $upload_model->isNewRecord = true;
                    $upload_model->id = null;
                    $upload_model->setScenario('copy');
                    $upload_model->setThumbScenario('copy');
                    $upload_model->relate_key = $relate_key;
                    $upload_model->copy_id = $upload_model->copy_id;
                    $upload_model->file_path_copy = $upload_model->file_path;
                    $upload_model->save();
                    $upload_model->refresh();
                    if ($this->_make_logging) {
                        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
                        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
                        if (!empty($comment)) {
                            $comment = '</br>' . $comment;
                        }

                        History::getInstance()->addToHistory(HistoryMessagesModel::MT_FILE_UPLOADED,
                            $this->extension_copy->copy_id,
                            $this->getPrimaryKey(),
                            [
                                '{module_data_title}' => $this->getModuleTitle(),
                                '{user_id}'           => WebUser::getUserId(),
                                '{uploads_id}'        => $upload_model->id,
                                '{file_url}'          => '/' . $upload_model->getFileUrl(),
                                '{file_title}'        => $upload_model->file_title,
                                '{file_path}'         => $upload_model->file_path,
                                '{comment}'           => (!empty($comment) ? $comment : ''),
                            ],
                            true,
                            true

                        );
                    }
                }
                $this->{$key} = $relate_key;
            }
        }
    }

    /**
     *  копирование файлов и предание им статуса временных. Используется при создании карточки из шаблона
     */
    public function copyFilesFromTemplate()
    {
        foreach ($this->getAttributes() as $key => $value) {
            $field_params = [];
            if (isset($this->_schema_fields[$key])) {
                $field_params = $this->_schema_fields[$key];
            }

            $uploads_id = [];
            $parents_id = [];
            if (!empty($field_params) && ($field_params['params']['type'] == 'file' || $field_params['params']['type'] == 'file_image' || $field_params['params']['type'] == 'attachments') || $key == 'ehc_image1') {
                $files = UploadsModel::model()->setRelateKey($value)->findAll();
                if (!empty($files)) {
                    foreach ($files as $value_file) {
                        $upload_model = new UploadsModel();
                        $upload_model->setScenario('copy');
                        $upload_model->setThumbScenario('copy');
                        $upload_model->copy_id = $value_file->copy_id;
                        $upload_model->file_source = $value_file->file_source;
                        $upload_model->file_path_copy = $value_file->file_path;
                        $upload_model->file_name = $value_file->file_name;
                        $upload_model->file_title = $value_file->file_title;
                        $upload_model->thumbs = $value_file->thumbs;
                        $upload_model->relate_key = null;
                        $upload_model->status = 'temp';
                        $upload_model->save();
                        $upload_model->refresh();
                        $uploads_id[] = $upload_model->getPrimaryKey();
                        $parents_id[$upload_model->getPrimaryKey()] = $value_file->id;

                        if ($value_file->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {
                            if (isset($field_params['params']['file_generate'])) {
                                if ($field_params['params']['file_generate']) {
                                    $parent_upload_model = new UploadsParentsModel();
                                    $parent_upload_model->parent_upload_id = $value_file->id;
                                    $parent_upload_model->upload_id = $upload_model->getPrimaryKey();
                                    $parent_upload_model->parent_doc_id = $this->getAttributes()[\ExtensionCopyModel::model()->findByPk($value_file->copy_id)->prefix_name . '_id'];
                                    $parent_upload_model->save();
                                }
                            }
                        }

                    }
                }
                $this->$key = $uploads_id;

            }

        }
    }

    /**
     * removeRuleRequiredAll - удаляет контроль на обязательность значения поля из всех полей
     */
    public function removeRuleRequiredAll()
    {
        if (!empty($this->_dinamic_params['params']['rules'])) {
            $rules = [];
            foreach ($this->_dinamic_params['params']['rules'] as $rule) {
                if (!empty($rule[1]) && in_array($rule[1], ['required', 'relateCheckRequired'])) {
                    continue;
                }
                $rules[] = $rule;
            }
            $this->_dinamic_params['params']['rules'] = $rules;
        }
    }

    /**
     * removeRuleRequiredForBpmOperation - удаляет контроль на обязательность значения поля, если карточка BPM оператора
     */
    private function removeRuleRequiredForBpmOperation()
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists('is_bpm_operation', $attributes) || $this->is_bpm_operation != 1) {
            return;
        }

        $this->removeRuleRequiredAll();
    }

    /**
     * removeRuleRequiredIfShowBlocks
     */
    private function removeRuleRequiredIfShowBlocks()
    {
        if ($this->extension_copy->isShowAllBlocks()) {
            return;
        }
        if (empty($this->_dinamic_params['params']['rules'])) {
            return;
        }

        $schema_params = $this->extension_copy->getFieldSchemaParamsByType(Fields::MFT_DISPLAY_BLOCK);
        if ($schema_params == false) {
            return;
        }

        $unique_index = $this->{$schema_params['params']['name']};

        //if($unique_index == false) return;

        $block_ui_skip_list = $this->extension_copy->getSchemaBlocksData(false, true);
        if ($block_ui_skip_list == false || count($block_ui_skip_list) == 1) {
            return;
        }

        $field_name_list = [];
        foreach ($block_ui_skip_list as $block) {
            if ($block['field_name_list'] == false) {
                continue;
            }
            if ($unique_index == $block['unique_index']) {
                continue;
            }
            $field_name_list = array_merge($field_name_list, array_keys($block['field_name_list']));
        }
        if ($field_name_list == false) {
            return;
        }

        $rules = [];
        foreach ($this->_dinamic_params['params']['rules'] as $rule) {
            if (!empty($rule[0]) && !empty($rule[1]) && in_array($rule[0], $field_name_list) && in_array($rule[1], ['required', 'relateCheckRequired'])) {
                continue;
            }
            $rules[] = $rule;
        }
        $this->_dinamic_params['params']['rules'] = $rules;
    }

    protected function beforeValidate()
    {
        $this->removeRuleRequiredForBpmOperation();
        $this->removeRuleRequiredIfShowBlocks();

        $this->validateLongValue();

        return parent::beforeValidate();
    }

    protected function afterValidate()
    {
        parent::afterValidate();
        //$this->responsibleCheck();

        if ($this->hasErrors()) {
            foreach ($this->_files as $field_name => $value) {
                if (!empty($value)) {
                    $this->{$field_name} = $value;
                } else {
                    $this->{$field_name} = null;
                }
            }
        }

        return true;
    }

    /**
     * validateLongValue - проверка значения текстового поля на количество символов
     * Результатом есть своего вида предутреждение, в массив _truncated_long_fields записывается название поля
     */
    private function validateLongValue()
    {
        if ($this->_truncate_long_value == false) {
            return;
        }

        foreach ($this->_schema_fields as $schema_field) {
            //MFT_STRING
            if ($schema_field['params']['type'] != \Fields::MFT_STRING) {
                continue;
            }

            $value = $this->{$schema_field['params']['name']};

            switch ($schema_field['params']['size']) {
                case \FieldTypes::TYPE_SIZE_VARCHAR :
                    $l = mb_strlen($value);
                    if ($l > \FieldTypes::TYPE_SIZE_VARCHAR) {
                        $value = mb_substr($value, 0, $schema_field['params']['size'] - 1);
                        $this->{$schema_field['params']['name']} = $value;
                        $this->_truncated_long_fields[] = $schema_field['params']['name'];
                    }
                    break;
                case \FieldTypes::TYPE_SIZE_TEXT :
                    $l = mb_strlen($value);
                    if ($l > \FieldTypes::TYPE_SIZE_TEXT) {
                        $value = mb_substr($value, 0, $schema_field['params']['size'] - 1);
                        $this->{$schema_field['params']['name']} = $value;
                        $this->_truncated_long_fields[] = $schema_field['params']['name'];
                    }
                    break;
            }
        }
    }

    /**
     * deletePrepare - подготавливает данные для удаления
     */
    public function deletePrepare()
    {
        if (empty($this->_schema_fields)) {
            return false;
        }

        // карточка
        $table_name = $this->extension_copy->getTableName(null, false);
        \QueryDeleteModel::getInstance()
            ->setDeleteModelParams($table_name, \QueryDeleteModel::D_TYPE_DATA, ['table_name' => $table_name, 'primary_field_name' => $this->extension_copy->prefix_name . '_id'])
            ->appendValues($table_name, \QueryDeleteModel::D_TYPE_DATA, $this->getPrimaryKey());

        // файлы
        $fields = $this->getAttributes(false);
        foreach ($fields as $key => $value) {
            $field_params = [];
            if (empty($value)) {
                continue;
            }
            if (isset($this->_schema_fields[$key])) {
                $field_params = $this->_schema_fields[$key];
            }

            if (!empty($field_params) && ($field_params['params']['type'] == 'file' || $field_params['params']['type'] == 'file_image' || $field_params['params']['type'] == 'attachments') || $key == 'ehc_image1') {
                \UploadsModel::deletePrepareUploads($value);
            }
        }

        // участники
        $this->deletePrepareParticipant($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});
        // email-участники
        $this->deletePrepareEmailParticipant($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});

        return true;
    }

    protected function beforeDelete()
    {
        // удаляем файлы
        if (empty($this->_schema_fields)) {
            return true;
        }

        $fields = $this->getAttributes(false);
        foreach ($fields as $key => $value) {
            $field_params = [];
            if (empty($value)) {
                continue;
            }
            if (isset($this->_schema_fields[$key])) {
                $field_params = $this->_schema_fields[$key];
            }

            if (!empty($field_params) && ($field_params['params']['type'] == 'file' || $field_params['params']['type'] == 'file_image' || $field_params['params']['type'] == 'attachments') || $key == 'ehc_image1') {
                $files = UploadsModel::model()->setRelateKey($value)->findAll();
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if ($this->_make_logging) {
                            $this->saveHistoryDeleteFile($file->file_title, $file->file_path, true);
                        }
                        $file->delete();
                    }

                }
            }
        }

        // удаляем уведомления из Активности
        $this->deleteActivityMessages();
        // удаляем участников
        $this->deleteParticipantParticipant($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});

        //$this->deleteParticipantEmail($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});

        return true;
    }

    public function beforeSave()
    {
        if ($this->isNewRecord) {
            // datetime
            $this->date_create = new CDbExpression('now()');
            $this->user_create = WebUser::getUserId();

            // module_title
            $this_template = (isset($this->this_template) && $this->this_template ? true : false);
            if (!$this_template) {
                //карточка сохранена, проверяем, есть ли автоматическая генерация, если есть, меняем название и сохраняем повторно
                $pk_schema = $this->extension_copy->getPrimaryField();
                if (!empty($pk_schema['params']['name_generate'])) {
                    $params = ['EditViewModel' => $this->_params];
                    if (!empty($_POST['element_relate'])) {
                        foreach ($_POST['element_relate'] as $relate) {
                            $params['element_relate'][] = $relate;
                        }
                    }
                    $auto_name = Fields::getInstance()->getNewRecordTitle(
                        $pk_schema['params']['name_generate'],
                        $pk_schema['params']['name_generate_params'],
                        $this->extension_copy,
                        $params);
                    if ($auto_name !== false) {
                        $this->setAttribute($pk_schema['params']['name'], $auto_name);
                    }
                }
            }

        } else {
            $this->date_edit = new CDbExpression('now()');
            $this->user_edit = WebUser::getUserId();
        }

        //password
        //ExtensionCopyModel::MODULE_USERS
        if ($this->extension_copy->copy_id == ExtensionCopyModel::MODULE_USERS) {
            foreach ($this->attributes as $field_name => $value) {
                $field_params = [];

                if (isset($this->_schema_fields[$field_name])) {
                    $field_params = $this->_schema_fields[$field_name];
                }

                if ($field_params == false || $field_params['params']['type'] != 'string') {
                    continue;
                }

                if (isset($field_params['params']['input_attr'])) {
                    $input_attr = json_decode($field_params['params']['input_attr'], true);
                    $password = 'qwertyuiop123';

                    if (is_array($input_attr) && in_array('password', $input_attr) && isset($this->_params[$field_name])) {
                        $this->{$field_name} = CPasswordHelper::hashPassword(($this->{$field_name} === null || $this->{$field_name} === '' ? $password : $this->{$field_name}));
                    } else {
                        if ($this->isNewRecord) {
                            if (is_array($input_attr) && in_array('password', $input_attr) && (!isset($this->_params[$field_name]) || $this->_params[$field_name] === null)) {
                                $this->user_model = new UsersModel();
                                $this->user_model->setMyAttributes($this->getAttributes());
                                $this->user_model->geterateRandomPassword();
                                $password = $this->user_model->password_real = $this->user_model->password;

                                $this->{$field_name} = CPasswordHelper::hashPassword($password);
                                $this->user_model->password = $this->{$field_name};
                            }
                        }
                    }
                }
            }
        }

        if ($this->copy_files) {
            $this->copyFiles();
        }

        // files
        $this->linkFiles();

        return true;
    }

    /**
     * sendUserEmail
     */
    private function sendUserEmail()
    {
        if ($this->user_model && $this->user_model->email && $this->user_model->hasErrors() == false) {

            $params_model = ParamsModel::model()->findAll();

            $mailer = new Mailer();
            $mailer
                ->setLetter(
                    ParamsModel::getValueFromModel('sending_out', $params_model),
                    ParamsModel::getValueFromModel('sending_out_name', $params_model),
                    $this->user_model->email,
                    $this->user_model->getFullName(),
                    Mailer::LETTER_USER_REGISTRATION,
                    [
                        '{site_url}'          => ParamsModel::getValueFromModel('site_url', $params_model),
                        '{site_title}'        => preg_replace('~(http://|https://)~', '', ParamsModel::getValueFromModel('site_url', $params_model)),
                        '{company_name}'      => ParamsModel::getValueFromModel('crm_name', $params_model),
                        '{service_email}'       => ParamsModel::getValueFromModel('service_email', $params_model),
                        '{sales_email}' => ParamsModel::getValueFromModel('sales_email', $params_model),
                        '{support_email}'     => ParamsModel::getValueFromModel('support_email', $params_model),
                        '{presentation_link}' => ParamsModel::getValueFromModel('presentation_link', $params_model),
                        '{login}'             => $this->user_model->email,
                        '{password}'          => $this->user_model->password_real,
                        '{user_name}'         => $this->user_model->first_name
                    ]
                );
        }

    }

    /**
     * Логирование
     */
    public function saveHistoryNewCard($delete_mark = true)
    {
        if ($this->_make_logging == false) {
            return;
        }

        if ($delete_mark && $this->extension_copy->copy_id == ExtensionCopyModel::MODULE_TASKS) {
            TaskModel::deleteMarkTaskIsView($this->getPrimaryKey());
        }
        if ($delete_mark && $this->extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS) {
            return;
        }

        $module_type = HistoryMessagesModel::MT_CREATED;
        $user_create_is_null = false;

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        // if Process operation
        $field_names = array_keys($this->getAttributes());
        if ($this->getIsNewRecord() == false && in_array('is_bpm_operation', $field_names) && $this->is_bpm_operation !== null) {
            $operations_model = \Process\models\OperationsModel::findOperationsModelByEntityParams($this->extension_copy->copy_id, $this->getPrimaryKey());

            if (!empty($operations_model)) {
                switch ($operations_model->element_name) {
                    case \Process\models\OperationsModel::ELEMENT_TASK :
                    case \Process\models\OperationsModel::ELEMENT_AGREETMENT :
                        $module_type = HistoryMessagesModel::MT_OPERATION_CREATED_TASK;
                        $user_create_is_null = true;
                        break;
                    /*
                    case \Process\models\OperationsModel::ELEMENT_NOTIFICATION :
                        $module_type = HistoryMessagesModel::MT_OPERATION_CREATED_NOTIFICATION;
                        $user_create_is_null = true;
                        break;
                    */
                }
            }
        } else {
            if ($this->getIsNewRecord() == false) {
                return;
            }
        }

        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
        if (!empty($comment)) {
            $comment = '</br>' . $comment;
        }

        History::getInstance()->addToHistory($module_type,
            $this->extension_copy->copy_id,
            $this->getPrimaryKey(),
            [
                '{module_data_title}' => $this->getModuleTitle(),
                '{user_id}'           => WebUser::getUserId(),
                '{comment}'           => (!empty($comment) ? $comment : ''),
            ],
            false,
            true,
            $user_create_is_null
        );

    }

    /**
     *
     */
    private function saveHistoryFiles()
    {
        if ($this->_make_logging == false) {
            return;
        }

        if (!empty($this->_history_add_files)) {

            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
            $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
            if (!empty($comment)) {
                $comment = '</br>' . $comment;
            }

            foreach ($this->_history_add_files as $params) {
                $p = $params;
                $p['{comment}'] = (!empty($comment) ? $comment : '');
                History::getInstance()->addToHistory(HistoryMessagesModel::MT_FILE_UPLOADED,
                    $this->extension_copy->copy_id,
                    $this->getPrimaryKey(),
                    $p,
                    true,
                    true
                );
            }
        }

    }

    /**
     *
     */
    private function saveHistoryAccess()
    {
        if ($this->_make_logging == false) {
            return;
        }

        if (!empty($this->_history_add_rules)) {
            $type = key($this->_history_add_rules);

            History::getInstance()->addToHistory(
                $type,
                $this->extension_copy->copy_id,
                $this->getPrimaryKey(),
                $this->_history_add_rules[$type],
                false,
                true
            );
        }
    }

    /**
     *
     */
    private function saveHistoryDateChanged()
    {
        if ($this->_make_logging == false) {
            return;
        }

        if ($this->_date_ending_changed !== null) {

            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
            $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
            if (!empty($comment)) {
                $comment = '</br>' . $comment;
            }

            History::getInstance()->addToHistory(HistoryMessagesModel::MT_DATE_ENDING_CHANGED,
                $this->extension_copy->copy_id,
                $this->getPrimaryKey(),
                [
                    '{module_data_title}' => $this->getModuleTitle(),
                    '{user_id}'           => WebUser::getUserId(),
                    '{date_ending}'       => $this->_date_ending_changed,
                    '{comment}'           => (!empty($comment) ? $comment : ''),
                ],
                false,
                true
            );
        }
    }

    /**
     *
     */
    private function saveHistoryStatusChanged()
    {
        if ($this->_make_logging == false) {
            return;
        }

        if ($this->_status_changed !== null) {

            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
            $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
            if (!empty($comment)) {
                $comment = '</br>' . $comment;
            }

            History::getInstance()->addToHistory(HistoryMessagesModel::MT_STATUS_CHANGED,
                $this->extension_copy->copy_id,
                $this->getPrimaryKey(),
                [
                    '{module_data_title}' => $this->getModuleTitle(),
                    '{user_id}'           => WebUser::getUserId(),
                    '{status}'            => $this->_status_changed,
                    '{comment}'           => (!empty($comment) ? $comment : ''),
                ],
                false,
                true
            );
        }

    }

    /**
     *  запись в лог. об удалении файла
     */
    private function saveHistoryDeleteFile($file_title, $file_path, $use_hitory_container = false)
    {
        if ($this->_logging_remove == false) {
            return;
        }
        if ($this->_make_logging == false) {
            return;
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $this->extension_copy->copy_id, 'card_id' => $this->getPrimaryKey()]);
        if (!empty($comment)) {
            $comment = '</br>' . $comment;
        }

        History::getInstance()->addToHistory(HistoryMessagesModel::MT_FILE_DELETED,
            $this->extension_copy->copy_id,
            $this->getPrimaryKey(),
            [
                '{module_data_title}' => $this->getModuleTitle(),
                '{user_id}'           => WebUser::getUserId(),
                '{file_title}'        => $file_title,
                '{file_path}'         => $file_path,
                '{comment}'           => (!empty($comment) ? $comment : ''),
            ],
            true,
            $use_hitory_container
        );
    }

    /**
     *
     */
    public function saveHistoryResponsibleAppointed($copy_id, $data_id, $user_id)
    {
        if ($this->_make_logging == false) {
            return;
        }

        $user_create_is_null = false;
        if ($this->extension_copy->copy_id == ExtensionCopyModel::MODULE_TASKS && $this->is_bpm_operation !== null) {
            $user_create_is_null = true;
        }
        if ($this->extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS) {
            return;
        }

        // for Process
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $copy_id, 'card_id' => $data_id]);
        if (!empty($comment)) {
            $comment = '</br>' . $comment;
        }

        History::getInstance()->addToHistory(HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED,
            $copy_id,
            $data_id,
            [
                '{module_data_title}' => $this->getModuleTitle(),
                '{user_id}'           => $user_id,
                '{comment}'           => (!empty($comment) ? $comment : ''),
            ],
            false,
            true,
            $user_create_is_null
        );

    }

    /**
     *
     */
    public function saveHistoryCommentCreated($activity_model, $copy_id, $data_id, $user_id)
    {
        if ($this->_make_logging == false) {
            return;
        }

        // history
        $activity_model->refresh();
        $files = [];
        if ($activity_model->getAttribute('attachment')) {
            $upload_models = UploadsModel::model()->setRelateKey($activity_model->getAttribute('attachment'))->findAll();

            foreach ($upload_models as $upload_model) {
                $files['{uploads_id}'][] = $upload_model->id;
                $files['{file_url}'][] = '/' . $upload_model->getFileUrl();
                $files['{file_title}'][] = $upload_model->file_title;
                $files['{file_path}'][] = $upload_model->file_path;
            }
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $copy_id, 'card_id' => $data_id]);
        if (!empty($comment)) {
            $comment = '</br>' . $comment;
        }

        $params = array_merge(
            [
                '{module_data_title}'    => $this->getModuleTitle(),
                '{user_id}'              => $user_id,
                '{activity_messages_id}' => $activity_model->getAttribute('activity_messages_id'),
                '{message}'              => $activity_model->getAttribute('message'),
                '{comment}'              => (!empty($comment) ? $comment : ''),
            ],
            $files
        );

        History::getInstance()->addToHistory(HistoryMessagesModel::MT_COMMENT_CREATED,
            $copy_id,
            $data_id,
            $params,
            false,
            true
        );
    }

    public function afterSave()
    {
        $this->eventOnCreated();
        $this->eventOnChanged();

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        $this->qwe_primary_key = $this->getPrimaryKey();

        if (empty($this->_primary_entities['primary_pdi']) && $this->qwe_primary_key) {
            $this->_primary_entities['primary_pdi'] = $this->qwe_primary_key;
        }

        if ($this->isNewRecord && in_array($this->extension_copy->copy_id, [ExtensionCopyModel::MODULE_STAFF])) {
            $this->user_model = UsersModel::model()->findByPk($this->getPrimaryKey());
            $this->user_model->setScenario('update_password');

            $this->user_model
                ->geterateRandomPassword()
                ->setChangePassword(true)
                ->save();
        }

        if (in_array($this->extension_copy->copy_id, [ExtensionCopyModel::MODULE_USERS, ExtensionCopyModel::MODULE_STAFF])) {
            $this->sendUserEmail();
        }

        $this->updateFileStatusTo();
        $this->saveSubModulesFromTemplate();
        $this->saveRelate();

        $this->updateAllPrimaryModelValue();

        $this->saveRelateResponsible();
        $this->saveRelateParticipant();

        if ($this->copy_participant) {
            $this->copyParticipant();
        }
        if ($this->copy_activity) {
            $this->copyActivity();
        }

        if ($this->isNewRecord) {
            $this->activityChangeStatus();
            $this->saveRelateString();

            if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_USERS) {
                ProfileNotificationSettingModel::saveDefaultSetting($this->getPrimaryKey());
            }
        }

        $this->copyProcessViewSortingList();
        $this->copyRelatePrimaryDataFromTemplate();

        $this->saveHistoryNewCard();
        $this->saveHistoryFiles();
        $this->saveHistoryAccess();
        $this->saveHistoryDateChanged();
        $this->saveHistoryStatusChanged();

        HistoryContainerModel::save();

        $this->runProcess();

        return true;
    }

    /**
     * @internal
     */
    public function afterDelete()
    {
        $this->deleteDraft();

        parent::afterDelete();
    }

    /**
     * runProcess - запуск процесса
     */
    private function runProcess()
    {
        if ($this->_switch_run_process == false) {
            return;
        }
        $pk = $this->getPrimaryKey();

        $data_model = \DataModel::getInstance()
            ->setSelect('process_id')
            ->setFrom('{{process_operations}}')
            ->setWhere('copy_id = ' . $this->extension_copy->copy_id . ' AND card_id=' . $pk)
            ->findScalar();
        if (empty($data_model)) {
            return;
        }

        \Process\models\OperationCardModel::setChangedCard($this->extension_copy->copy_id, $pk);
        \Process\models\ProcessModel::getInstance($data_model);
        \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus();
    }

    /**
     * Инициализация евента после создания сущности
     */
    protected function eventOnCreated()
    {
        if (!$this->getIsNewRecord()) {
            return;
        }

        $this->updateUid();
        $this->initWebhook(WebhookActionModel::ACTION_MODULE_CREATED_ENTITY);

    }

    /**
     * Инициализация евента после изменения сущности
     */
    protected function eventOnChanged()
    {
        if ($this->getIsNewRecord()) {
            return;
        }

        $this->deleteDraft();
        $this->initWebhook(WebhookActionModel::ACTION_MODULE_CHANGED_ENTITY);
    }

    /**
     * Удаление черновика сущности
     */
    protected function deleteDraft()
    {
        $uid = ModuleEntityUid::generate($this->extension_copy->copy_id, $this->getPrimaryKey());
        if ($uid) {
            DraftModel::model()->deleteAll('uid=:uid', [':uid' => $uid]);
        }

    }

    /**
     * Обновляем Uid
     */
    protected function updateUid()
    {
        $uid = ModuleEntityUid::generate($this->extension_copy->copy_id, $this->getPrimaryKey());
        (new DataModel())
            ->setText('update ' . $this->tableName() . ' set uid = ' . $uid . ' where ' . $this->extension_copy->getPkFieldName() . ' = ' . $this->getPrimaryKey())
            ->execute();
    }

    /**
     * Отправляем вебхук
     *
     * @param $action Название дейстия для вебхука
     * @see WebhookActionModel
     */
    protected function initWebhook($action)
    {
        $properties = [
            'copy_id'      => $this->extension_copy->copy_id,
            'action'       => $action,
            'request_data' => [
                'copy_id'   => $this->extension_copy->copy_id,
                'entity_id' => $this->getPrimaryKey(),
            ],
        ];

        (new \ConsoleRunAsync())
            ->setCommandProperties($properties)
            ->setControllerName('webhook')
            ->setActionName('init')
            ->exec();
    }

    /**
     * actionCreateProcessAfterCreatedEntity - создание нового Процесса исходя из действия - создание сущности
     *
     * @param $process_action_name
     */
    public function actionCreateProcessAfterCreatedEntity($async = true)
    {
        Logging::getInstance()->setLogName('process-actions-run')
            ->toFile('Start "CreateProcessAfterCreatedEntity"');

        if ($this->hasErrors()) {
            Logging::getInstance()->setLogName('process-actions-run')
                ->toFile('Model hasErrors. Stop');

            return;
        }

        if ($this->isThisTemplate()) {
            Logging::getInstance()->setLogName('process-actions-run')
                ->toFile('Model isThisTemplate. Stop');

            return;
        }

        if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            Logging::getInstance()->setLogName('process-actions-run')
                ->toFile('copy_id is MODULE_PROCESS. Stop');

            return;
        }

        $properties = [
            'properties' => [
                'action_name' => \ProcessActions::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY,
                'vars'        => [
                    'copy_id' => $this->extension_copy->copy_id,
                    'data_id' => $this->getPrimaryKey(),
                ],
            ],
        ];

        Logging::getInstance()->setLogName('process-actions-run')
            ->toFile('Properties: ' . json_encode($properties));

        $result = (new \ConsoleRunAsync())
            ->setCommandProperties($properties)
            ->setActionName('processActionsRun')
            ->setAsync($async)
            ->exec()
            ->getResult();

        Logging::getInstance()->setLogName('process-actions-run')
            ->toFile('Exec result: ' . json_encode($result))
            ->toFile('Done');

        /*
        $vars = array(
            'copy_id' => $this->extension_copy->copy_id,
            'data_id' => $this->getPrimaryKey(),
        );

        (new ProcessActions())
            ->setVars($vars)
            ->setActionName(\ProcessActions::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY)
            ->run()
            ->getResult();
        */
    }

    /**
     * actionCreateProcessAfterChangedEntity - создание нового Процесса исходя из действия - изменение сущности
     *
     * @param $process_action_name
     */
    public function actionCreateProcessAfterChangedEntity($async = true)
    {
        if ($this->hasErrors()) {
            return;
        }

        if ($this->isThisTemplate()) {
            return;
        }

        if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            return;
        }

        $properties = [
            'properties' => [
                'action_name' => \ProcessActions::ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY,
                'vars'        => [
                    'copy_id' => $this->extension_copy->copy_id,
                    'data_id' => $this->getPrimaryKey(),
                ],
            ],
        ];

        (new \ConsoleRunAsync())
            ->setCommandProperties($properties)
            ->setActionName('processActionsRun')
            ->setAsync($async)
            ->exec();

        if ($this->_status_changed) {
            (new \ConsoleRunAsync())
                ->setControllerName('my')
                ->setCommandProperties($this->getPrimaryKey())
                ->setActionName('rmdAfterSetStatusNedozvon')
                ->exec();
        }

    }

    /**
     * actionChangeProcessParticipantAfterChangedParticipant - обновляет в процессе старого участника нового (ответсвенного) из текущей сущности
     */
    public function actionChangeProcessParticipantAfterChangedParticipant($ug_id_from, $ug_type_from)
    {
        if ($this->hasErrors()) {
            return;
        }

        if ($this->isThisTemplate()) {
            return;
        }

        if ($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            return;
        }

        // текущий отвественный
        $participant_model = \ParticipantModel::getParticipants($this->extension_copy->copy_id, $this->getPrimaryKey(), null, true, true);

        if ($participant_model == false) {
            return;
        }

        if ($ug_id_from == $participant_model->ug_id && $ug_type_from == $participant_model->ug_type) {
            return;
        }

        $properties = [
            'properties' => [
                'action_name' => \ProcessActions::ACTION_CHANGE_PROCESS_PARTICIPANT_AFTER_CHENGED_PARTICIPANT,
                'vars'        => [
                    'copy_id'     => $this->extension_copy->copy_id,
                    'data_id'     => $this->getPrimaryKey(),
                    'participant' => [
                        'from' => [
                            'ug_id'   => $ug_id_from,
                            'ug_type' => $ug_type_from,
                        ],
                        'to'   => [
                            'ug_id'   => $participant_model->ug_id,
                            'ug_type' => $participant_model->ug_type,
                        ],
                    ],
                ],
            ],
        ];

        (new \ConsoleRunAsync())
            ->setCommandProperties($properties)
            ->setActionName('processActionsRun')
            ->exec();

        /*
        $vars = array(
                    'copy_id' => $this->extension_copy->copy_id,
                    'data_id' => $this->getPrimaryKey(),
                    'participant' => array(
                        'from' => array(
                            'ug_id' => $ug_id_from,
                            'ug_type' => $ug_type_from,
                        ),
                        'to' => array(
                            'ug_id' => $participant_model->ug_id,
                            'ug_type' => $participant_model->ug_type,
                        ),
                    ),
            );

        (new ProcessActions())
            ->setVars($vars)
            ->setActionName(ProcessActions::ACTION_CHANGE_PROCESS_PARTICIPANT_AFTER_CHENGED_PARTICIPANT)
            ->run()
            ->getResult();
        */
    }

    /**
     * Копия данных Участников из основной таблицы
     */
    protected function copyParticipant()
    {
        if (empty($this->copy_participant_model)) {
            return;
        }

        $real_responsible = [];
        $skip_user = false;

        foreach ($this->copy_participant_model as $model) {
            $attributes = $model->getAttributes();
            // делаем активного пользователя ответсвенным

            if ($this->_set_responsible_is_active_user && WebUser::getAppType() == WebUser::APP_WEB) {
                if ($attributes['responsible'] == '1') {
                    if ($attributes['ug_id'] != WebUser::getUserId() && $attributes['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER) {
                        $real_responsible = $attributes;
                        $attributes['ug_id'] = WebUser::getUserId();
                        $attributes['ug_type'] = ParticipantModel::PARTICIPANT_UG_TYPE_USER;
                    }
                } else {
                    if ($attributes['ug_id'] == WebUser::getUserId() && $attributes['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER) {
                        $skip_user = true;
                        continue;
                    }
                }
            }

            $this->insertNewParticipant($attributes);
        }

        if (!empty($real_responsible) && $skip_user) {
            $real_responsible['responsible'] = '0';
            $this->insertNewParticipant($real_responsible);
        }

        return $this;
    }

    private function insertNewParticipant($attributes)
    {

        $attributes['data_id'] = $this->{$this->extension_copy->prefix_name . '_id'};
        $attributes['date_create'] = new CDbExpression('now()');
        $attributes['date_edit'] = null;
        $attributes['user_create'] = WebUser::getUserId();
        $attributes['user_edit'] = null;

        $new_participant = new ParticipantModel();
        $new_participant->setMyAttributes($attributes);

        if ($new_participant->save()) {
            if ($attributes['responsible'] === "1") {
                $this->saveHistoryResponsibleAppointed($attributes['copy_id'], $this->{$this->extension_copy->prefix_name . '_id'}, $attributes['ug_id']);
            }
        }
    }

    /**
     * Копия данных Активности
     */
    protected function copyActivity()
    {
        if (empty($this->copy_activity_model)) {
            return;
        }

        foreach ($this->copy_activity_model as $activity_model) {
            $attributes = $activity_model->getAttributes();
            $new_activity = new ActivityMessagesModel();
            $new_activity->setScenario('copy');
            $attributes['activity_messages_id'] = null;
            $attributes['data_id'] = $this->{$this->extension_copy->prefix_name . '_id'};
            //$attributes['date_create'] = new CDbExpression('now()');
            //$attributes['date_edit'] = null;
            $new_activity->setMyAttributes($attributes);

            if ($new_activity->save()) {
                $new_activity->refresh();
                // history

                $this->saveHistoryCommentCreated($new_activity, $this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'}, $new_activity->user_create);
            }
        }

        return $this;
    }

    /**
     * Копия данных Активности при создании из шаблона
     */
    public function copyActivityFromTemplate()
    {
        $this->use_only_template_activity_id_list = true;

        $copy_activity_model = ActivityMessagesModel::model()->findAll([
            'condition' => 'copy_id = :copy_id AND data_id = :data_id AND `status` = "asserted"',
            'params'    => [
                ':copy_id' => $this->extension_copy->copy_id,
                ':data_id' => $this->{$this->extension_copy->prefix_name . '_id'},
            ]
        ]);

        if (empty($copy_activity_model)) {
            return;
        }

        foreach ($copy_activity_model as $activity_model) {
            $attributes = $activity_model->getAttributes();
            $new_activity = new ActivityMessagesModel();
            $new_activity->setScenario('copy');
            $attributes['activity_messages_id'] = null;
            $attributes['data_id'] = null;
            $attributes['date_create'] = new CDbExpression('now()');
            $attributes['date_edit'] = null;
            $attributes['status'] = 'temp';
            $new_activity->setMyAttributes($attributes);

            $new_activity->save();
            $new_activity->refresh();
            $this->template_activity_id_list[] = $new_activity->getPrimaryKey();
        }

        return $this;
    }

    /**
     * проверка на наличия первичного поля в подчиненном модуле
     */
    private function isPrimaryModule($relate_copy_id)
    {
        $result = false;

        if (!empty($this->_primary_entities['primary_pci']) &&
            ModuleTablesModel::isSetRelate($this->_primary_entities['primary_pci'], $relate_copy_id, 'relate_module_many') &&
            ModuleTablesModel::isSetRelate($relate_copy_id, $this->_primary_entities['primary_pci'], 'relate_module_one')) {
            $result = true;
        }

        return $result;
    }

    /**
     * обновляем на новые значения первичные поля СДМ во всех записях елемента СМ
     */
    private function updatePrimaryValueForSubModules($extension_copy, array $id, $check_new_record = true, $parent_extension_copy = null, $miss_first_module = null)
    {
        if ($this->_primary_entity_changed == false) {
            return;
        }
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }
        if ($check_new_record && $this->isNewRecord) {
            return;
        }
        //if($this->extension_copy->getModule(false)->isTemplate($this->extension_copy) && (boolean)$this->this_template == true) return;

        // элементы блока СМ
        $sub_modules = SchemaOperation::getSubModules($extension_copy->getSchemaParse());
        if (!empty($sub_modules)) {
            foreach ($sub_modules as $module) {
                $module = $module['sub_module'];
                if ($parent_extension_copy && $module['params']['relate_module_copy_id'] == $parent_extension_copy->copy_id) {
                    continue;
                } //?
                if ($this->isPrimaryModule($module['params']['relate_module_copy_id']) == false) {
                    continue;
                }

                $relate_table = ModuleTablesModel::model()->find([
                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"',
                    'params'    => [
                        ':copy_id'        => $extension_copy->copy_id,
                        ':relate_copy_id' => $module['params']['relate_module_copy_id']
                    ]
                ]);
                // все данные сабмодуля
                $sub_module_data = new DataModel();
                $sub_module_data
                    ->setFrom('{{' . $relate_table->table_name . '}}')
                    ->setWhere($relate_table->parent_field_name . ' in (' . implode(',', $id) . ')');

                $sub_module_data = $sub_module_data->findAll();
                if (empty($sub_module_data)) {
                    continue;
                }

                $sub_module_data_id = array_unique(array_keys(CHtml::listData($sub_module_data, $relate_table->relate_field_name, '')));

                SubModuleUpdatePrimaryModel::getInstance()
                    ->setPrimaryEntities($this->_primary_entities)
                    ->update(
                        $extension_copy->copy_id,
                        $id,
                        $module['params']['relate_module_copy_id'],
                        $sub_module_data_id
                    );

                $this->updatePrimaryValueForSubModules(ExtensionCopyModel::model()->findByPk($module['params']['relate_module_copy_id']), $sub_module_data_id, $check_new_record, $extension_copy, false);
            }
        }

        SubModuleUpdatePrimaryModel::getInstance()
            ->setPrimaryEntities($this->_primary_entities)
            ->updateFromTitleRelate(
                $extension_copy->copy_id,
                $id
            );

        if ($miss_first_module === false) { // пропускаем элементы СДМ первого модуля
            foreach ($id as $id_value) {
                $this->deletePrimaryValueForRelate($parent_extension_copy, $extension_copy, $id_value);
            }
        }
    }

    /**
     * удаляет связи во всех елементах СДМ
     */
    public function deletePrimaryValueForRelate($parent_extension_copy, $extension_copy, $id)
    {
        if ($this->_primary_entity_changed == false) {
            return;
        }
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }
        if ($this->isNewRecord) {
            return;
        }

        SubModuleUpdatePrimaryModel::getInstance()
            ->setPrimaryEntities($this->_primary_entities)
            ->deletePrimaryValueForRelate(
                $parent_extension_copy,
                $extension_copy,
                $id
            );
    }

    /**
     * обновляем на новые значения первичные поля СДМ во всех записях елемента СМ
     */
    public function updateAllPrimaryModelValue()
    {
        // элементы блока СМ
        $this->updatePrimaryValueForSubModules($this->extension_copy, [$this->qwe_primary_key], false, null, $this->miss_first_module);

        $this->_primary_entity_changed == false;
    }

    /**
     * сохраняем submodules
     */
    private function saveSubModulesFromTemplate()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }
        //if(empty(Yii::app()->controller->module) || Yii::app()->controller->module->isTemplate($this->extension_copy) == false || isset($_POST['from_template']) == false || (boolean)$_POST['from_template'] == false) return;
        if (empty($_POST['submodules'])) {
            return;
        }
        if (Yii::app()->controller->module->isTemplate($this->extension_copy) == false || isset($_POST['from_template']) == false || (boolean)$_POST['from_template'] == false) {
            return;
        }

        foreach ($_POST['submodules'] as $value) {
            $submodule_relate_model = ModuleTablesModel::model()->findByPk($value['relate_table_module_id']);
            $submodule_extension_copy = ExtensionCopyModel::model()->findByPk($submodule_relate_model->relate_copy_id);

            if (isset($value['data_id_list']) && !empty($value['data_id_list'])) {
                $result = EditViewCopyModel::getInstance($submodule_extension_copy)
                    ->setThisTemplate(self::THIS_TEMPLATE_MODULE)
                    ->copy(
                        $value['data_id_list'],
                        $submodule_extension_copy,
                        false,
                        $this->extension_copy
                    )
                    ->getResult();

                if ($result['status'] == false || empty($result['id'])) {
                    continue;
                }

                foreach ($result['id'] as $sm_value) {
                    DataModel::getInstance()->Insert('{{' . $submodule_relate_model->table_name . '}}',
                        [
                            $this->extension_copy->prefix_name . '_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                            ExtensionCopyModel::model()
                                ->findByPk(ModuleTablesModel::model()
                                    ->findByPk($value['relate_table_module_id'])
                                    ->relate_copy_id)
                                ->prefix_name . '_id'                  => $sm_value,
                        ]
                    );

                }

            }
        }

        $this->_primary_entity_changed = true;
        //$this->updateAllPrimaryModelValue();
    }

    /**
     * сохраняем relate (СДМ и СМ)
     */
    private function saveRelate()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }
        $parent_relate_sutup = false;

        if (!empty($_POST['element_relate'])) {
            foreach ($_POST['element_relate'] as $relate) {
                if (!empty($_POST['parent_copy_id']) && $_POST['parent_copy_id'] == $relate['relate_copy_id']) {
                    $parent_relate_sutup = true;
                }
                if (empty($this->{$this->extension_copy->prefix_name . '_id'})) {
                    continue;
                }

                $module_tables_model = \ModuleTablesModel::getRelateModuleTableData($this->extension_copy->copy_id, $relate['relate_copy_id']);

                if (!empty($module_tables_model)) {
                    //проверка и установка статуса _primary_entity_changed, изменено ли значение первичного поля
                    if (!empty($this->_primary_entities) && isset($this->_primary_entities['primary_pci']) && $this->_primary_entities['primary_pci'] == $relate['relate_copy_id']) {
                        $primary_model = DataModel::getInstance()
                            ->setFrom('{{' . $module_tables_model['table_name'] . '}}')
                            ->setWhere($this->extension_copy->prefix_name . '_id=:data_id', [':data_id' => $this->{$this->extension_copy->prefix_name . '_id'}])
                            ->FindAll();
                        if (empty($primary_model) && !empty($this->_primary_entities['primary_pdi'])) {
                            $this->_primary_entity_changed = true;
                        } else {
                            if (!empty($primary_model) && $primary_model[0][$module_tables_model['relate_field_name']] != $this->_primary_entities['primary_pdi']) {
                                $this->_primary_entity_changed = true;
                            }
                        }

                    }

                    DataModel::getInstance()->Delete('{{' . $module_tables_model['table_name'] . '}}',
                        $this->extension_copy->prefix_name . '_id=:data_id',
                        [':data_id' => $this->{$this->extension_copy->prefix_name . '_id'}]
                    );

                    $id_list = explode(',', $relate['id']);

                    foreach ($id_list as $id) {
                        if (!empty($id) && $id != 'false') {
                            if ($this->_pci && $this->_pdi && (integer)$this->_pci == (integer)$relate['relate_copy_id'] && (integer)$this->_pdi == (integer)$id) {
                                $this->_set_relate_form_parent = false;
                            }

                            // если сохраняем из шаблона - делаем копию данных, и к ним вяжемся
                            if (
                                isset($_POST['template_data_id']) && !empty($_POST['template_data_id']) &&
                                ($this->_pci == false || $this->_pci != $relate['relate_copy_id'])
                            ) {
                                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate['relate_copy_id']);
                                //проверка, шаблонная ли это карточка
                                if ($relate_extension_copy->isSetIsTemplate()) {
                                    $data_model = new DataModel();
                                    $data_model
                                        ->setFrom($relate_extension_copy->getTableName())
                                        ->setWhere($relate_extension_copy->prefix_name . '_id =:id', [':id' => $id]);
                                    $relate_data = $data_model->findRow();

                                    if (!empty($relate_data) && $relate_data['this_template'] == self::THIS_TEMPLATE_TEMPLATE) {

                                        $last_record = EditViewCopyModel::getInstance($relate_extension_copy)
                                            ->setThisTemplate(self::THIS_TEMPLATE_MODULE)
                                            ->copy(
                                                [$id],
                                                $relate_extension_copy,
                                                false,
                                                $this->extension_copy
                                            )
                                            ->getResult();

                                        if (empty($last_record)) {
                                            continue;
                                        }
                                        $id = $last_record['id'][0];

                                        RelateUpdatePrimaryModel::getInstance()
                                            ->setPrimaryEntities($this->_primary_entities)
                                            ->update(
                                                $relate['relate_copy_id'],
                                                $id);
                                    }
                                }
                            }

                            DataModel::getInstance()->Insert('{{' . $module_tables_model['table_name'] . '}}',
                                [
                                    $this->extension_copy->prefix_name . '_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                                    ExtensionCopyModel::model()
                                        ->findByPk($module_tables_model['relate_copy_id'])
                                        ->prefix_name . '_id'                  => $id,
                                ]
                            );
                        }
                    }
                }
            }
        }

        // если есть родитель, то есть, edit-view  открыт из сабмодуля - установка связи между карточками модулей
        if (!empty($_POST['parent_copy_id']) && !empty($_POST['parent_data_id']) &&
            $parent_relate_sutup == false &&
            $_POST['parent_copy_id'] != ExtensionCopyModel::MODULE_ROLES) {

            $relate_table = ModuleTablesModel::model()->find([
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"',
                'params'    => [
                    ':copy_id'        => $_POST['parent_copy_id'],
                    ':relate_copy_id' => $this->extension_copy->copy_id
                ]
            ]);
            if (!empty($relate_table)) {
                // проверяем, не ли еще связи          
                $relate_data_list =
                    DataModel::getInstance()
                        ->setSelect('count(*) as xcount')
                        ->setFrom('{{' . $relate_table->table_name . '}}')
                        ->setWhere(
                            $relate_table->relate_field_name . '=:relate_data_id AND ' . $relate_table->parent_field_name . '=:parent_data_id',
                            [
                                ':relate_data_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                                ':parent_data_id' => $_POST['parent_data_id'],
                            ])
                        ->findRow();

                if (!empty($relate_data_list) && (integer)$relate_data_list['xcount'] == 0) {
                    // устанавливаем связь 
                    DataModel::getInstance()->Insert('{{' . $relate_table->table_name . '}}',
                        [
                            $relate_table->relate_field_name => $this->{$this->extension_copy->prefix_name . '_id'},
                            $relate_table->parent_field_name => $_POST['parent_data_id'],
                        ]);
                }
            }
        }

    }

    /**
     * удаляем уведомления из Активности
     */
    public function deleteActivityMessages()
    {
        $activity_schema = $this->extension_copy->getFieldSchemaParamsByType('activity');
        if (empty($activity_schema)) {
            return;
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition("copy_id=:copy_id AND data_id=:data_id");
        $criteria->params = [
            ':copy_id' => $this->extension_copy->copy_id,
            ':data_id' => $this->getPrimaryKey(),
        ];

        $activity_model = ActivityMessagesModel::model()->findAll($criteria);
        if (!empty($activity_model)) {
            foreach ($activity_model as $activity) {
                $activity->delete();
            }
        }
    }

    /**
     *  Удаляем учасников  - Только подготовка!
     */
    private function deletePrepareParticipant($copy_id, $data_id)
    {
        $data_model = new DataModel();
        $data_model
            ->setSelect('participant_id, copy_id, data_id, ug_id')
            ->setFrom('{{participant}}')
            ->andWhere('copy_id=:copy_id AND data_id=:data_id', [':copy_id' => $copy_id, ':data_id' => $data_id]);

        $data_participant = $data_model->findAll();

        if (!empty($data_participant)) {
            foreach ($data_participant as $participant) {
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('participant', \QueryDeleteModel::D_TYPE_DATA, ['table_name' => 'participant', 'primary_field_name' => 'participant_id'])
                    ->appendValues('participant', \QueryDeleteModel::D_TYPE_DATA, $participant['participant_id']);

                ParticipantModel::deleteParticipantsFromChildrenModules(
                    $participant['copy_id'],
                    $participant['data_id'],
                    $participant['ug_id'],
                    true
                );
            }
        }
    }

    /**
     *  Удаляем email-учасников  - Только подготовка!
     */
    private function deletePrepareEmailParticipant($copy_id, $data_id)
    {
        $data_model = new DataModel();
        $data_model
            ->setSelect('participant_email_id, copy_id, data_id')
            ->setFrom('{{participant_email}}')
            ->andWhere('copy_id=:copy_id AND data_id=:data_id', [':copy_id' => $copy_id, ':data_id' => $data_id]);

        $data_participant = $data_model->findAll();

        if (!empty($data_participant)) {
            foreach ($data_participant as $participant) {
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('participant_email', \QueryDeleteModel::D_TYPE_DATA, ['table_name' => 'participant_email', 'primary_field_name' => 'participant_email_id'])
                    ->appendValues('participant_email', \QueryDeleteModel::D_TYPE_DATA, $participant['participant_email_id']);
            }
        }
    }

    /**
     *  Удаляем учасников
     */
    private function deleteParticipantParticipant($copy_id, $data_id, $participant_id = null)
    {
        $data_for_delete = ParticipantModel::model()
            ->scopeExceptionParticipantId($participant_id)
            ->findAll([
                'condition' => 'copy_id=:copy_id AND data_id=:data_id',
                'params'    => [
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                ]
            ]);

        if (!empty($data_for_delete)) {
            foreach ($data_for_delete as $data) {
                if ($data->delete()) {
                    ParticipantModel::deleteParticipantsFromChildrenModules(
                        $data->getAttribute('copy_id'),
                        $data->getAttribute('data_id'),
                        $data->getAttribute('ug_id')
                    );
                }
            }
        }
    }

    /**
     *  Удаляем учасников
     */
    private function deleteParticipantEmail($copy_id, $data_id, $participant_email_id = null)
    {
        $participant_model_list = ParticipantEmailModel::model()
            ->scopeWithOutParticipantEmailId($participant_email_id)
            ->scopeCardParams($copy_id, $data_id)
            ->findAll();

        if ($participant_model_list == false) {
            return;
        }

        foreach ($participant_model_list as $participant_model) {
            $participant_model->delete();
        }
    }

    /**
     * сохраняем Ответственных
     */
    private function saveRelateResponsible()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        // ответсвенный участник
        if ($this->isNewRecord == false) {
            $participant_responsible_model = ParticipantModel::getParticipants($this->extension_copy->copy_id, $this->qwe_primary_key, null, true, true);
        }

        if (!empty($_POST['element_responsible'])) {
            foreach ($_POST['element_responsible'] as $key => $post_value) {
                if (empty($post_value['ug_id'])) {
                    continue;
                }

                $participant_model = $this->findResponsible($post_value);

                if ($participant_model !== false) {
                    $changed = ($participant_model->ug_id != $post_value['ug_id'] || $participant_model->ug_type != $post_value['ug_type']);
                    $participant_model->setMyAttributes($post_value);
                    $participant_model->update();

                    if ($this->_make_logging && $changed) {
                        $this->saveHistoryResponsibleAppointed($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'}, $post_value['ug_id']);
                    }
                } else {
                    $this->refresh();
                    $participant_model = new ParticipantModel();
                    $participant_model->setMyAttributes($post_value);
                    $participant_model->copy_id = $this->extension_copy->copy_id;
                    $participant_model->data_id = $this->{$this->extension_copy->prefix_name . '_id'};
                    $participant_model->insert();
                    if ($this->_make_logging) {
                        $this->saveHistoryResponsibleAppointed($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'}, $post_value['ug_id']);
                    }
                }
            }
        } else {
            if ($this->scenario != 'copy' && $this->scenario != 'update_scalar' && $this->scenario != 'process_view_update' && $this->scenario != 'bulk_edit') {
                if ($this->_delete_participant) {
                    if (!empty($this->extension_copy)) {
                        if ($this->extension_copy->isResponsible()) //если блок ответственный
                        {
                            ParticipantModel::model()->deleteAll('copy_id=:copy_id AND data_id=:data_id',
                                [
                                    ':copy_id' => $this->extension_copy->copy_id,
                                    ':data_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                                ]);
                        }
                    }
                }
            }
        }

        if ($this->isNewRecord == false && !empty($participant_responsible_model)) {
            $this->actionChangeProcessParticipantAfterChangedParticipant($participant_responsible_model->ug_id, $participant_responsible_model->ug_type);
        }

    }

    /**
     * сохраняем Учасников
     */
    private function saveRelateParticipant()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        $this->saveRelateParticipantParticipant();
        $this->saveRelateParticipantEmail();
    }

    /**
     * сохраняем Участников
     */
    private function saveRelateParticipantParticipant()
    {
        $participant_attributes = $this->getAttributesBlockParticipant(self::DBA_BLOCK_PARTICIPANT, ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT);
        $element_participant_id = (!empty($participant_attributes['element_participant_id']) ? $participant_attributes['element_participant_id'] : null);
        $element_participant = (!empty($participant_attributes['element_participant']) ? $participant_attributes['element_participant'] : null);

        // ответсвенный участник
        if ($this->isNewRecord == false) {
            $participant_responsible_model = ParticipantModel::getParticipants($this->extension_copy->copy_id, $this->qwe_primary_key, null, true, true);
        }

        // массив существующих записей
        if ($element_participant_id) {
            $participant_id = [];

            foreach ($element_participant_id as $post_value) {
                $participant_id[] = $post_value['participant_id'];
            }

            // удаляем участников, которых нет в массиве
            $this->deleteParticipantParticipant($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'}, $participant_id);

            // обновляем старые
            foreach ($element_participant_id as $post_value) {
                $id = $post_value['participant_id'];
                $relate_data = ParticipantModel::model()->findByPk($id);
                if (!empty($relate_data)) {

                    $was_responsible = $relate_data->getAttribute('responsible');

                    $relate_data->setMyAttributes($post_value);
                    $relate_data->update();

                    if ($this->_make_logging && !$was_responsible && $post_value['responsible'] == '1') {
                        $this->saveHistoryResponsibleAppointed($this->extension_copy->copy_id, $relate_data->data_id, $post_value['ug_id']);
                    }
                }
            }
        } else {
            if (
                $this->scenario != 'inline_edit' &&
                $this->scenario != 'process_view_update' &&
                $this->scenario != 'copy' &&
                $this->scenario != 'update_scalar' &&
                $this->scenario != 'add_from_process' &&
                $this->_delete_participant == true &&
                !$this->extension_copy->isResponsible()
            ) // если массив пуст - удаляем всех участников карточки (только не для Inline  редактирования)
            {
                $this->deleteParticipantParticipant($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});
            }
        }

        // добавляем новые
        if ($element_participant) {
            foreach ($element_participant as $post_value) {
                $relate_data = new ParticipantModel();
                $relate_data->setMyAttributes($post_value);
                $relate_data->copy_id = $this->extension_copy->copy_id;
                $relate_data->data_id = $this->{$this->extension_copy->prefix_name . '_id'};
                $relate_data->insert();

                if ($this->_make_logging && $post_value['responsible'] == '1') {
                    $this->saveHistoryResponsibleAppointed($relate_data->copy_id, $relate_data->data_id, $post_value['ug_id']);
                }
            }

        }

        // оновляем участника в процессе
        if ($this->isNewRecord == false && !empty($participant_responsible_model)) {
            $this->actionChangeProcessParticipantAfterChangedParticipant($participant_responsible_model->ug_id, $participant_responsible_model->ug_type);
        }
    }

    /**
     * сохраняем email-участников
     */
    private function saveRelateParticipantEmail()
    {
        $participant_attributes = $this->getAttributesBlockParticipant(self::DBA_BLOCK_PARTICIPANT, ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL);
        $element_participant_id = (!empty($participant_attributes['element_participant_id']) ? $participant_attributes['element_participant_id'] : null);
        $element_participant = (!empty($participant_attributes['element_participant']) ? $participant_attributes['element_participant'] : null);

        // массив существующих записей
        if ($element_participant_id) {
            $participant_email_id = [];
            foreach ($element_participant_id as $post_value) {
                $participant_email_id[] = $post_value['participant_email_id'];
            }

            // удаляем участников, которых нет в массиве
            $this->deleteParticipantEmail($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'}, $participant_email_id);
        } else {
            if (
                $this->scenario != 'inline_edit' &&
                $this->scenario != 'process_view_update' &&
                $this->scenario != 'copy' &&
                $this->scenario != 'update_scalar' &&
                $this->scenario != 'add_from_process' &&
                $this->_delete_participant == true &&
                !$this->extension_copy->isResponsible()
            ) // если массив пуст - удаляем всех участников карточки (только не для Inline  редактирования)
            {
                $this->deleteParticipantEmail($this->extension_copy->copy_id, $this->{$this->extension_copy->prefix_name . '_id'});
            }
        }

        // добавляем новые
        if ($element_participant) {
            foreach ($element_participant as $post_value) {
                $relate_data = new ParticipantEmailModel();
                $relate_data->setMyAttributes($post_value);
                $relate_data->copy_id = $this->extension_copy->copy_id;
                $relate_data->data_id = $this->{$this->extension_copy->prefix_name . '_id'};
                $relate_data->insert();
            }

        }
        /* else
        if(!empty($_POST['element_responsible'])){
            foreach($_POST['element_responsible'] as $post_value){
                if($this->_make_logging && $post_value['responsible'] == '1') {
                    History::getInstance()->addToHistory(HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED,
                        $this->extension_copy->copy_id,
                        $this->{$this->extension_copy->prefix_name . '_id'},
                        array('{module_data_title}' => $this->getModuleTitle(), '{user_id}' => $post_value['ug_id'])
                    );
                }

            }
        }*/

    }

    /**
     * обновление участников. используется в processView при перетаскивании карточек
     */
    public function updateResponsible($ug_id)
    {
        $participant_model_d = ParticipantModel::model()
            ->find(
                'copy_id=:copy_id AND data_id=:data_id AND ug_type=:ug_type',
                [
                    ':copy_id' => $this->extension_copy->copy_id,
                    ':data_id' => $this->getPrimaryKey(),
                    ':ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                ]
            );

        if ($participant_model_d == false) {
            $participant_model_d = new ParticipantModel();
        }

        $attributes = [
            'copy_id'     => $this->extension_copy->copy_id,
            'data_id'     => $this->getPrimaryKey(),
            'ug_id'       => $ug_id,
            'ug_type'     => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
            'responsible' => "1",
        ];

        $participant_model_d->setAttributes($attributes);

        if ($participant_model_d->save()) {
            if ($this->_make_logging) {
                $this->saveHistoryResponsibleAppointed($this->extension_copy->copy_id, $participant_model_d->data_id, $participant_model_d->ug_id);
            }

            return true;
        }

        return false;
    }

    /**
     *  поиск ответственного
     */
    private function findResponsible($responsible_attr)
    {
        $participant_model = ParticipantModel::model()->find([
            'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
            'params'    => [
                ':copy_id' => $this->extension_copy->copy_id,
                ':data_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                ':ug_id'   => $responsible_attr['ug_id'],
                ':ug_type' => $responsible_attr['ug_type'],
            ],
        ]);
        if (empty($participant_model)) {
            if (!empty($responsible_attr['participant_id'])) {
                $participant_model = ParticipantModel::model()->findByPk($responsible_attr['participant_id']);
            }
            if (!empty($participant_model)) {
                return $participant_model;
            } else {
                return false;
            }
        } else {
            $data_model = new DataModel();
            $data_model->Update(
                '{{participant}}',
                ['responsible' => '0'],
                'copy_id=:copy_id AND data_id=:data_id AND ug_id!=:ug_id',
                [
                    ':copy_id' => $this->extension_copy->copy_id,
                    ':data_id' => $this->{$this->extension_copy->prefix_name . '_id'},
                    ':ug_id'   => $responsible_attr['ug_id'],

                ]
            );

            return $participant_model;
        }
    }

    /**
     * меняем статус для уведомлений блока Активность на "утвержденные"
     */
    private function activityChangeStatus()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        $ids = Yii::app()->request->getPost('element_block_activity');
        if (!$ids || empty($ids)) {
            return;
        }

        $criteria = new CDBCriteria();
        $criteria->condition = 'copy_id = ' . $this->extension_copy->copy_id . ' AND status="temp"';
        $criteria->addInCondition('activity_messages_id', $ids);
        $temp_activities = ActivityMessagesModel::model()->findAll($criteria);

        if (!$temp_activities) {
            return;
        }

        foreach ($temp_activities as $temp_activity) {
            $temp_activity->setAttribute('status', 'asserted');
            $temp_activity->setAttribute('data_id', $this->qwe_primary_key);
            if ($temp_activity->type_comment == ActivityMessagesModel::TYPE_COMMENT_EMAIL) {
                $message = json_decode($temp_activity->message);
                $message->subject = $this->_params['module_title'];
                $temp_activity->message = json_encode($message);
            }
            if ($temp_activity->save()) {
                $this->saveHistoryCommentCreated($temp_activity, $this->extension_copy->copy_id, $this->qwe_primary_key, \WebUser::getUserId());
                $temp_activity->communicationSaveMessage();
            }
        }
    }

    /**
     * сохраняем relate_string
     */
    private function saveRelateString()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        if ($this->_set_relate_form_parent == false) {
            return;
        }

        if ($this->_pci && $this->_pdi) {
            $relate_table = ModuleTablesModel::model()->find([
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"',
                'params'    => [
                    ':copy_id'        => $this->_pdi,
                    ':relate_copy_id' => $this->extension_copy->copy_id
                ]
            ]);
            if (!empty($relate_table)) {
                DataModel::getInstance()->Insert('{{' . $relate_table->table_name . '}}',
                    [
                        $relate_table->parent_field_name => $this->_pdi,
                        $relate_table->relate_field_name => $this->{$relate_table->relate_field_name},
                    ]);
            }
        }
    }

    /**
     * Связывает загруженные файлы с сущностью
     */
    private function linkFiles()
    {
        //Возвращает массив полей параметров схемы, что соответсвуют питам изобращения
        $fieldSchemaList = $this->extension_copy->getFileFieldSchemaParams();

        if (!$fieldSchemaList) {
            return;
        }

        (new ExtensionCopyModel())->findByPk(ExtensionCopyModel::MODULE_DOCUMENTS)->getModule(false);

        foreach ($fieldSchemaList as $fieldSchema) {
            //1. Вяжем файлы из сущностью
            $fieldName = $fieldSchema['name'];

            if (!array_key_exists($fieldName, $this->_files)) {
                continue;
            }

            $idList = (array)$this->_files[$fieldName];
            $relateKey = $this->{$fieldName};
            $this->clearOldFilesById($relateKey, $idList); // очищаем от старых или удаленных файлов. Сработает, если запись редактируется ($relateKey not NULL)

            if (!$idList) {
                $this->{$fieldName} = null;
                continue;
            }

            if (!$relateKey) {
                $relateKey = md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $fieldName);
                $this->{$fieldName} = $relateKey;
            }

            \UploadsModel::model()->updateAll(
                [
                    'relate_key' => $relateKey,
                    'copy_id'    => $this->extension_copy->copy_id,
                ],
                (new CDbCriteria())->addInCondition('id', $idList)
            );

            //2. Другая шняга: делаем что-то с файлыми в модуле документы, обновляем _history_add_files
            $uploadModels = UploadsModel::model()->findAll((new CDbCriteria())->addInCondition('id', $idList));
            //актуально для модуля Документы

            DocumentsModel::updateUploadsChilds($uploadModels);

            if ($this->_make_logging && $uploadModels) {
                $this->_history_add_files = [];
                foreach ($uploadModels as $uploadModel) {
                    $this->_history_add_files[] = [
                        '{module_data_title}' => $this->getModuleTitle(),
                        '{user_id}'           => WebUser::getUserId(),
                        '{uploads_id}'        => $uploadModel->id,
                        '{file_url}'          => '/' . $uploadModel->getFileUrl(),
                        '{file_title}'        => $uploadModel->file_title,
                        '{file_path}'         => $uploadModel->file_path,
                    ];
                }
            }
        }
    }

    /**
     * Обновляет статус всех загруженых файлов сущности
     *
     * @param string $status
     */
    private function updateFileStatusTo($status = 'asserted')
    {
        //Возвращает массив полей параметров схемы, что соответсвуют питам изобращения
        $fieldSchemaList = $this->extension_copy->getFileFieldSchemaParams();

        if (!$fieldSchemaList) {
            return;
        }

        $relateKeyList = [];
        foreach ($fieldSchemaList as $fieldSchema) {
            $fieldName = $fieldSchema['name'];
            if ($this->{$fieldName}) {
                $relateKeyList[] = $this->{$fieldName};
            }
        }

        if (!$relateKeyList) {
            return;
        }

        UploadsModel::model()->updateAll(
            ['status' => $status,],
            (new CDbCriteria())->addInCondition('relate_key', $relateKeyList)
        );
    }

    /**
     * Очищаем от старых или удаленных файлов. Сработает, если запись редактируется ($relateKey not NULL)
     *
     * @param $fileRelakeKey
     * @param $fileIdList
     * @throws CDbException
     */
    private function clearOldFilesById($fileRelakeKey, $fileIdList)
    {
        if (!$fileRelakeKey) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('relate_key=:relate_key');
        $criteria->params = [
            ':relate_key' => $fileRelakeKey,
        ];
        if ($fileIdList) {
            $criteria->addNotInCondition('id', $fileIdList);
        }

        $uploadsModel = UploadsModel::model()->findAll($criteria);

        if ($uploadsModel) {
            foreach ($uploadsModel as $uploadModel) {
                if ($uploadModel->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {
                    //дополнительно проверяем таблицу соответствия загрузок (актуально для модуля Документы)
                    DocumentsModel::updateUploadsParents($uploadModel);
                }

                if ($uploadModel->delete()) {
                    $this->saveHistoryDeleteFile($uploadModel->file_title, $uploadModel->file_path, true);
                }
            }
        }
    }

    /**
     * обновление файлов. используется в processView при перетаскивании карточек
     */
    public function updateFiles($relate_keys)
    {
        if (count($relate_keys) == 0) {
            return;
        }
        foreach ($relate_keys as $fieldName => $relateKey) {
            if (!empty($this->{$field_name})) {
                $files = UploadsModel::model()->setRelateKey($this->{$fieldName})->findAll();
                if (!$files) {
                    foreach ($files as $file) {
                        $file->delete();
                        $this->saveHistoryDeleteFile($file->file_title, $file->file_path);
                    }
                }
            }
            if ($relateKey) {
                $files = UploadsModel::model()->setRelateKey($relateKey)->findAll();
                if (!empty($files)) {
                    $relate_key = md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $fieldName);

                    foreach ($files as $value_file) {
                        $upload_model = new UploadsModel();
                        $upload_model->setScenario('copy');
                        $upload_model->setThumbScenario('copy');
                        $upload_model->copy_id = $value_file->copy_id;
                        $upload_model->file_source = $value_file->file_source;
                        $upload_model->file_path_copy = $value_file->file_path;
                        $upload_model->file_name = $value_file->file_name;
                        $upload_model->file_title = $value_file->file_title;
                        $upload_model->thumbs = $value_file->thumbs;
                        $upload_model->relate_key = $relate_key;
                        $upload_model->status = 'asserted';
                        $upload_model->save();
                        $upload_model->refresh();
                    }
                }
                $this->{$fieldName} = $relateKey;
            }
        }
    }

    private function copyProcessViewSortingList()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        if (!isset($_POST['template_data_id']) || empty($_POST['template_data_id'])) {
            return;
        }

        $panel_data_list = (new DataModel())
            ->setFrom((new ProcessViewSortingListModel())->getTableName())
            ->setWhere(
                'pci=:pci AND pdi=:pdi',
                [
                    ':pci' => $this->extension_copy->copy_id,
                    ':pdi' => $_POST['template_data_id'],
                ]
            )
            ->findAll();

        $index_start = 0;
        $index_finish = 0;

        // prepare new data
        foreach ($panel_data_list as &$panel_data) {
            $panel_data['pdi'] = $this->qwe_primary_key;

            $b = ProcessViewSortingListModel::getInstance()
                ->insertAdd($panel_data)
                ->insertAllToDB(100);

            if ($b) {
                $this->addSortingIdToNewModuleData($panel_data_list, ProcessViewSortingListModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_list_id');
                $index_start = $index_finish + 1;
            }

            $index_finish++;
        }

        $index_finish--;

        if (ProcessViewSortingListModel::getInstance()->beInsertValues()) {
            ProcessViewSortingListModel::getInstance()->insertAllToDB();
            $this->addSortingIdToNewModuleData($panel_data_list, ProcessViewSortingListModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_list_id');
        }

        $this->insertNewIdsToProcessViewTodoList($panel_data_list);
    }

    /**
     * addSortingIdToNewModuleData - добавляет к массиву данных sorting_id новой записи
     *
     * @param $panel_data
     * @param $sorting_id_start
     * @param $index_start
     * @param $index_finish
     */
    private
    function addSortingIdToNewModuleData(&$entity_data, $sorting_list_id_start, $index_start, $index_finish, $pk_field_name)
    {
        $sorting_list_id = $sorting_list_id_start;

        for ($i = $index_start; $i <= $index_finish; $i++) {
            $entity_data[$i][$pk_field_name] = $sorting_list_id;
            $sorting_list_id++;
        }
    }

    /**
     * insertNewIdsToProcessViewTodoList - вставка данных о ТОДО листах в связующую таблицу
     *
     * @param $panel_data_list
     */
    private
    function insertNewIdsToProcessViewTodoList($panel_data_list)
    {
        if ($panel_data_list == false) {
            return;
        }

        $insert_attr = [];

        foreach ($panel_data_list as $panel_data) {
            if (empty($panel_data['sorting_list_id']) || empty($panel_data['fields_data'])) {
                continue;
            }

            $fields_data = json_decode($panel_data['fields_data'], true);

            if (empty($fields_data['todo_list'])) {
                continue;
            }

            $insert_attr[] = [
                'todo_list_id'    => $fields_data['todo_list'],
                'sorting_list_id' => $panel_data['sorting_list_id'],
            ];
        }

        if ($insert_attr) {
            (new ProcessViewTodoListModel())->insertMulti($insert_attr);
        }
    }

    /**
     * копируем данные связаного модуля по полю Название. При создании из шаблона...
     */
    private
    function copyRelatePrimaryDataFromTemplate()
    {
        if ($this->scenario == 'copy' || $this->scenario == 'update_scalar') {
            return;
        }

        if (!isset($_POST['template_data_id']) || empty($_POST['template_data_id'])) {
            return;
        }

        EditViewCopyModel::getInstance($this->extension_copy)
            ->copyRelateString(
                $this->extension_copy,
                $_POST['template_data_id'],
                $this->{$this->extension_copy->prefix_name . '_id'}
            );
    }

    public
    static function getSubModuleLink($params)
    {
        $result = [
            'href'        => 'javascript:void(0)',
            'target'      => '',
            'data-target' => self::LINK_TG_EDIT_VIEW,
        ];

        if ($params['extension_copy']->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            $result['href'] = '/module/BPM/run/' . \ExtensionCopyModel::MODULE_PROCESS . '?process_id=' . $params['id'];
            $result['target'] = '_blank';
            $result['data-target'] = self::LINK_TG_MODULE_PROCESS;

        } else {
            if ($params['params']['type'] == 'relate_string' && Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_VIEW, $params['params']['relate_module_copy_id'])) {
                $result['href'] = History::getInstance()
                    ->setPci($params['extension_copy']->copy_id)
                    ->setPdi($params['id'])
                    ->getUserStorageUrl([
                        'copy_id' => $params['params']['relate_module_copy_id'],
                        'params'  => [
                            'pci'           => $params['extension_copy']->copy_id,
                            'pdi'           => $params['id'],
                            'this_template' => $params['this_template'],
                        ]
                    ]);
                //$result['href'] = str_replace('"', '\'', $url);
                $result['target'] = '_blank';
                $result['data-target'] = self::LINK_TG_MODULE_PCI;

            } else {
                if (in_array($params['extension_copy']->copy_id, [\ExtensionCopyModel::MODULE_PROJECTS])) {
                    $url = History::getInstance()->getUserStorageUrl(['copy_id' => $params['extension_copy']->copy_id]);
                    //$url = str_replace('"', '\}', $url);
                    if (strpos($url, '?')) {
                        $result['href'] = $url . '&modal_ev=' . $params['id'];
                    } else {
                        $result['href'] = $url . '?modal_ev=' . $params['id'];
                    }

                    $result['target'] = '_blank';
                    $result['data-target'] = self::LINK_TG_MODULE;
                }
            }
        }

        return $result;
    }

    /**
     * checkOnObjectInstance - проверяет наличие связаного єкзепляра объекта в процессе
     *
     * @param $copy_id
     * @return bool
     */
    /*
    public static function checkOnObjectInstance($copy_id, $relate_copy_id){
        $result = false;
        if($relate_copy_id != \ExtensionCopyModel::MODULE_PROCESS) return $result;

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        if(\Process\models\ProcessModel::isSetRelateObjectInstance($copy_id)){
            $result = true;
        }

        return $result;
    }
    */

    /**
     * @return string
     */
    /*
    public static function getObjectInstanceConfirmMessage(){
        $validate = new \Validate();
        $validate->addValidateResultConfirm(
            'c',
            \Yii::t('messages', 'This will create related processes') . '</br></br>' . \Yii::t('messages', 'Note: before creating the process(es) change a new card will be saved') . '</br></br>' .  \Yii::t('messages', 'Continue')  . '?',
            ValidateConfirmActions::ACTION_PROCESS_OBJECT_INSTANCE);
        $message = json_encode(array('message' => $validate->getValidateResultHtml()));

        return $message;
    }
    */

    /**
     * addNewProcessesForSubModule - создает и добавляет новые процессы в сабмодуле
     */
    /*
    public static function addNewProcessesForSubModule($parent_copy_id, $parent_data_id){
        ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        $process_model_list = \Process\models\ProcessModel::model()->findAll(\Process\models\BindingObjectModel::RM_FIELD_NAME . '=:relate_copy_id AND this_template="1"', array(':relate_copy_id' => $parent_copy_id));
        if(empty($process_model_list)) return;

        foreach($process_model_list as $process_model){
            \Process\models\ProcessModel::createFromTemplate(
                                $process_model->process_id,
                                null,
                                \Process\models\ProcessModel::B_STATUS_IN_WORK,
                                $parent_copy_id,
                                $parent_data_id
                            );

        }
    }
    */

    /**
     * getRelateModuleData - Возвращает данные связаного модуля по названию поля
     *
     * @return array;
     */
    public
    function getRelateModuleData($field_name)
    {
        $id = $this->{$this->extension_copy->getPkFieldName(false, false)};
        if (!$id) {
            return;
        }

        $relate_schema = $this->extension_copy->getFieldSchemaParams($field_name);

        if (!$relate_schema) {
            return;
        }

        $module_tables = \ModuleTablesModel::getRelateModel($this->extension_copy->copy_id, $relate_schema['params']['relate_module_copy_id'], \ModuleTablesModel::TYPE_RELATE_MODULE_ONE);

        if (!$module_tables) {
            return;
        }

        $data_list = \DataModel::getInstance()
            ->setSelect($module_tables->relate_field_name)
            ->setFrom('{{' . $module_tables->table_name . '}}')
            ->setWhere($module_tables->parent_field_name . '=' . $id)
            ->findCol();

        if (empty($data_list)) {
            return;
        }

        return $data_list;
    }

}


