<?php

/**
 * EditViewBuilder - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */

class EditViewBuilder
{

    // orm актуальной части данных сабмодуля для EditView
    protected $_extension_data;

    // экземпляр ExtensionCopyModel
    protected $_extension_copy;

    // сущностипервичного родительского модуля
    protected $_primary_entities;

    // формирование данных для шаблона
    protected $_this_template = EditViewModel::THIS_TEMPLATE_MODULE;

    // CopyId родительского модуля
    protected $_parent_copy_id;

    //  Id записи родительского модуля
    protected $_parent_data_id;

    // массив данных полей relate модуля. Передается при добавлении данных в EditView для Сабмодуля
    protected $_parent_relate_data_list = null;

    // данные елементов по умолчанию
    protected $_default_data = null;

    //Id основной таблицы записи в базе
    protected $_data_id;

    // не добавляет элемент Panel
    protected $_skip_element_panel = false;

    //данные массива из связаных модулей
    public $_relate;

    //статусы отображений для блоков в EditView
    public static $block_display_status = [];

    //параметры последнего отображенного блока
    public static $block_display_last = [];

    // список полей связаних модулей для исключения
    public static $relate_module_copy_id_exception = [];

    //показывает только блок с определенным id
    protected $_only_unique_block_index = false;

    //возможен выбор только одного блока
    protected $_only_one_block_select = false;

    //данные из связанной записи
    protected $_default_data_from_linked_card = false;

    //маркер данных по-умолчанию во время создания из шаблона
    protected $_default_data_from_template = false;

    //массив значений кнопки-переключателя "Комментарий" в блоке "Активность"
    protected $_type_comment_list = [ActivityMessagesModel::TYPE_COMMENT_GENERAL];

    /**
     *  экземпляр ExtensionCopyModel
     *
     * @return this
     */
    public function setExtensionCopy($extension_copy)
    {
        $this->_extension_copy = $extension_copy;

        return $this;
    }

    /**
     *  установка orm актуальной части данных модуля для EditView
     *
     * @return this
     */
    public function setExtensionData($extension_data)
    {
        $this->_extension_data = $extension_data;

        return $this;
    }

    public function getExtensionData()
    {
        return $this->_extension_data;
    }

    /**
     *  установка Id основной таблицы записи в базе
     *
     * @return this
     */
    public function setDataId($id)
    {
        $this->_data_id = $id;

        return $this;
    }

    public function setTypeCommentList($type_comment_list)
    {
        $this->_type_comment_list = $type_comment_list;

        return $this;
    }

    public function getTypeCommentList()
    {
        return $this->_type_comment_list;
    }

    /**
     *  установка сущностей первичного родительского модуля: primary_pci, primary_pdi, auto
     *
     * @return this
     */
    public function setPrimaryEntities($entities)
    {
        $this->_primary_entities = $entities;

        return $this;
    }

    public function getPrimaryEntities()
    {
        return $this->_primary_entities;
    }

    /**
     * формирование данных для шаблона
     *
     * @return this
     */
    public function setThisTemplate($this_template)
    {
        $this->_this_template = $this_template;

        return $this;
    }

    public function getThisTemplate()
    {
        return $this->_this_template;
    }

    /**
     *  установка CopyId родительского модуля
     *
     * @return this
     */
    public function setParentCopyId($id)
    {
        $this->_parent_copy_id = $id;

        return $this;
    }

    public function getParentCopyId()
    {

        return $this->_parent_copy_id;
    }

    /**
     *  установка Id данных родительского модуля
     *
     * @return this
     */
    public function setParentDataId($id)
    {
        $this->_parent_data_id = $id;

        return $this;
    }

    public function getParentDataId()
    {
        return $this->_parent_data_id;
    }

    /**
     *  формирует массив данных полей relate модуля. Передается при добавлении данных в EditView для Сабмодуля
     *
     * @return this
     */
    public function setParentRelateDataList($list)
    {
        $this->_parent_relate_data_list = $list;

        return $this;
    }

    public function getParentRelateDataList()
    {
        return $this->_parent_relate_data_list;
    }

    /**
     *  установка данных по умолчанию
     *
     * @return this
     */
    public function setDefaultData($data)
    {
        $this->_default_data = $data;

        return $this;
    }

    public function getDefaultData()
    {
        return $this->_default_data;
    }

    /**
     *  данные массива из связаных модулей
     *
     * @return this
     */
    public function setRelate($relate)
    {
        $this->_relate = $relate;

        return $this;
    }

    public function getRelate()
    {
        return $this->_relate;
    }

    /**
     *  для показа только определенного блока
     *
     * @return this
     */
    public function setBlockUniqueIndex($only_unique_block_index = false)
    {
        $this->_only_unique_block_index = $only_unique_block_index;

        return $this;
    }

    /**
     *  для выбора только определенного блока
     *
     * @return this
     */
    public function setBlockSelect($only_one_block_select = false)
    {
        $this->_only_one_block_select = $only_one_block_select;

        return $this;
    }

    /**
     *  данные по умолчанию со связанной карточки
     *
     * @return this
     */
    public function setDefaultDataFromLinkedCard($card_id = false)
    {
        if ($card_id) {
            $this->_default_data_from_linked_card = \AdditionalProccessingModel::getInstance()->getDataFromLinkedCard($card_id);
        }

        return $this;
    }

    /**
     *  позволяет заполнять данные по-умолчанию во время создании записи из шаблона
     *
     * @return this
     */
    public function setDefaultDataFromTemplate($access = false)
    {
        $this->_default_data_from_template = $access;

        return $this;
    }

    /**
     *  загрузка данных статуса отображения блоков в EditView
     */
    private function setEvBlockDisplayStatus()
    {
        if (empty($this->_extension_copy)) {
            return $this;
        }
        if (!empty(self::$ev_block_display_status)) {
            return $this;
        }
        self::$block_display_status = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_EV_BLOCK_DISPLAY, 'editView_' . $this->_extension_copy->copy_id);

        return $this;
    }

    /**
     *  возвращает статус отображения блоков в EditView
     */
    public static function getEvBlockDisplayStatus($unique_index, $block_name)
    {
        self::$block_display_last = ['unique_index' => $unique_index, 'block_name' => $block_name];

        $status = ($block_name == 'block_fields' ? 'fa-chevron-down' : 'fa-chevron-up');
        if (empty(self::$block_display_status)) {
            return $status;
        }
        foreach (self::$block_display_status as $block) {
            if ($block['unique_index'] == $unique_index) {
                return $block['status'];
            }
        }

        return $status;
    }

    /**
     * показываем только определенный блок
     *
     * @param $schema_element
     * @return bool
     */
    private function checkIsViewBlock($schema_element)
    {
        if ($this->_extension_copy->isShowAllBlocks() == false) {
            if ($schema_element['elements'][0]['type'] == 'block_panel' && !$schema_element['params']['header_hidden']) {
                if ($this->_only_unique_block_index == false || ($this->_only_unique_block_index && $schema_element['params']['unique_index'] != $this->_only_unique_block_index)) {
                    return false;
                }
            }
        }

        return true;
    }




    /*
    block
    |-----block_buttons
    |               |-----button_date_ending
    |               |-----button_subscription
    |               |-----button_responsible
    |-----block_participant
    |-----block_attachments
    |-----block_activity
    |-----block_panel_contact
    |               |-----block_field_type_contact
    |                     |------field_type_hidden
    |-----block_panel
    |               |-----panel
    |               |-----field
    |               |	  |-----label
    |               |	             |-----block_field_type
    |               |                 		|-----field_type
    |               |
    |               |-----table
    |                         |-----table_column
    |			                     |-----table_header
    |			                     |-----edit
    |                                |-----table_footer
    |-----sub_module
    |-----attachments
    |-----activity
    */

    /**
     * строит елементы полей  формы editView
     *
     * @return string (html)
     */
    public function buildEditViewPage($schema)
    {
        if (empty($schema)) {
            return;
        }
        if (count($schema) == 0) {
            return;
        }
        $result = '';
        foreach ($schema as $value) {
            if (isset($value['type']))
                switch ($value['type']) {
                    case 'block' : // +
                        if ($this->checkIsViewBlock($value) == false) {
                            continue 2;
                        }

                        $result .= $this->getEditViewElementBlock($value);
                        break ;
                    case 'block_panel' : // +
                        $result .= $this->getEditViewElementBlockPanel($value);
                        break ;
                    case 'block_panel_contact' : // +
                        $result .= $this->getEditViewElementBlockPanelContact($value);
                        break ;
                    case 'block_button' : // +
                        $result .= $this->getEditViewElementBlockButton($value);
                        break ;
                    case 'activity' : // +
                        $result .= $this->getEditViewElementBlockActivity($value);
                        break ;
                    case 'participant' : // +
                        $result .= $this->getEditViewElementBlockParticipant($value);
                        break ;
                    case 'attachments' : // +
                        $result .= $this->getEditViewElementBlockAttachments($value);
                        break ;
                    case 'panel' : // +
                        $result .= $this->getEditViewElementPanel($value);
                        break ;
                    case 'label' : // +
                        $result .= $this->getEditViewElementLabel($value);
                        break ;
                    case 'block_field_type' : // +
                        $result .= $this->getEditViewElementBlockEdit($value);
                        break ;
                    case 'block_field_type_contact' : // +
                        $result .= $this->getEditViewElementBlockEditContact($value);
                        break ;
                    case 'edit' : // +
                        $result .= $this->getEditViewElementEdit($value);
                        break ;
                    case 'edit_hidden' : // +
                        $result .= $this->getEditViewElementEditHidden($value);
                        break ;
                    case 'button' : // +
                        $result .= $this->getEditViewElementButton($value);
                        break ;
                    case 'table_column' :
                        $result .= $this->getEditViewElementTableColumn($value);
                        break ;
                    case 'sub_module' :
                        $result .= $this->getEditViewElementSubModule($value);
                        break ;
                }
        }

        return $result;
    }

    /**
     * Возвращает елемент "Блок" (block)
     *
     * @return string (html)
     */
    public function getEditViewElementBlock($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        //block_name
        $block_name = 'block_fields';
        if (isset($schema['elements'][0]['type']) && $schema['elements'][0]['type'] == 'sub_module') {
            // проверка доступа к модулю
            $sm_extension_copy = ExtensionCopyModel::model()->findByPk($schema['elements'][0]['params']['relate_module_copy_id']);

            if ($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE && $this->_extension_copy->isSetIsTemplate() == true && $sm_extension_copy->isSetIsTemplate() == false) {
                return false;
            }

            if ((boolean)$sm_extension_copy->active == false) {
                return false;
            }
            if (Access::moduleAdministrativeAccess($schema['elements'][0]['params']['relate_module_copy_id'])) {
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return false;
                }
            } else {
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $schema['elements'][0]['params']['relate_module_copy_id'], Access::ACCESS_TYPE_MODULE)) {
                    return false;
                }
            }

            $block_name = 'block_sub_module';
        }

        $content = $this->buildEditViewPage($schema['elements']);

        if (empty($content)) {
            return false;
        }

        $this->setEvBlockDisplayStatus();
        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Block.Block'),
            [
                'schema'     => $schema,
                'content'    => $content,
                'block_name' => $block_name,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок Панель" (BlockPanel)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockPanel($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        if (!isset($schema['params']['make']) || $schema['params']['make'] == false) {
            return false;
        }

        $content = $this->buildEditViewPage($schema['elements']);

        if (empty($content)) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Panel.Panel'),
            [
                'schema'  => $schema,
                'content' => $content,
                'view'    => 'block_panel',
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок Панель Контактов" (BlockPanelContact)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockPanelContact($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }
        if (!isset($schema['params']['make']) || $schema['params']['make'] == false) {
            return false;
        }

        $content = $this->buildEditViewPage($schema['elements']);

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Panel.Panel'),
            [
                'schema'         => $schema,
                'content'        => $content,
                'view'           => 'block_panel_contact',
                'extension_copy' => $this->_extension_copy,
                'extension_data' => $this->_extension_data,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок кнопок" (BlockButton)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockButton($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $content = $this->buildEditViewPage($schema['elements']);

        $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'),
            [
                'schema'         => $schema,
                'view'           => 'block',
                'content'        => $content,
                'extension_copy' => $this->_extension_copy,
            ],
            true);

        return $result;
    }

    protected function getEditViewElementBlockActivityMessagesModelList()
    {
        if ($this->_extension_data->use_only_template_activity_id_list) {
            if ($this->_extension_data->template_activity_id_list == false) {
                return;
            }

            return ActivityMessagesModel::getActivityMessagesListByActivityId($this->_extension_data->template_activity_id_list);

        } else {
            return ActivityMessagesModel::getActivityMessagesListByDataId(
                $this->_extension_copy,
                $this->_extension_data[$this->_extension_copy->getPkFieldName()]
            );
        }
    }

    /**
     * Возвращает елемент "Блок Активаность" (Activity)
     *
     * @return string (html)
     */
    protected function getEditViewElementBlockActivity($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
            [
                'schema'                       => $schema,
                'extension_copy'               => $this->_extension_copy,
                'data_id'                      => $this->_data_id,
                'activity_messages_model_list' => $this->getEditViewElementBlockActivityMessagesModelList(),
                'type_comment_list'            => $this->getTypeCommentList(),
                'module_title'                 => $this->_extension_data->module_title,
                'edit_view_buider_model'       => $this,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок Учасники" (BlockParticipant)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockParticipant($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $participant_model_list = [];
        $edit_partisipants = true;
        $content = '';

        if ($this->_extension_data->isNewRecord == false) {
            //проверка доступа на изменение подписчиков
            $edit_partisipants = ParticipantModel::model()->checkUserSubscription(
                $this->_extension_copy->copy_id,
                $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'},
                $this->_extension_data);

            // participant
            $participant_model = ParticipantModel::getParticipantSaved(
                $this->_extension_copy->copy_id,
                $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'}
            );

            $content = $this->getEditViewElementBlockParticipantParticipantContent($participant_model);
            $participant_model_list = array_merge($participant_model_list, $participant_model);

            // email
            $participant_model = ParticipantEmailModel::getParticipantSaved(
                $this->_extension_copy->copy_id,
                $this->_extension_data->{$this->_extension_copy->prefix_name . '_id'}
            );

            $content .= $this->getEditViewElementBlockParticipantEmailContent($participant_model);
            $participant_model_list = array_merge($participant_model_list, $participant_model);

        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock'),
            [
                'schema'              => $schema,
                'extension_copy'      => $this->_extension_copy,
                'view'                => 'block',
                'content'             => $content,
                'participant_data'    => $participant_model_list,
                'edit_partisipants'   => $edit_partisipants,
                'extension_copy_data' => $this->_extension_data
            ],
            true);

        return $result;
    }

    protected function getEditViewElementBlockParticipantParticipantContent($participant_model_list)
    {
        $content = '';
        $pci = null;
        $pdi = null;

        if (is_array($this->_parent_copy_id)) {
            foreach ($this->_parent_copy_id as $val) {
                if ($val) {
                    $pci = $val;
                    break;
                }
            }
        } else {
            $pci = $this->_parent_copy_id;
        }

        if (is_array($this->_parent_data_id)) {
            foreach ($this->_parent_data_id as $val) {
                if ($val) {
                    $pdi = $val;
                    break;
                }
            }
        } else {
            $pdi = $this->_parent_data_id;
        }

        if (!empty($participant_model_list)) {
            foreach ($participant_model_list as $participant_model) {
                if (isset($_POST['from_template']) && (boolean)$_POST['from_template'] && !Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $participant_model->copy_id, Access::ACCESS_TYPE_MODULE, $participant_model->ug_id)) {
                    continue;
                }
                $content .= $this->getEditViewElementCardParticipant($participant_model->getEntityData());
            }
        }

        if ($pdi && $pci) {
            $parent_participant_model = ParticipantModel::getParticipants($pci, $pdi);
            if (!empty($parent_participant_model)) {
                foreach ($participant_model_list as $key => $participant_model) {

                    $find = false;
                    foreach ($parent_participant_model as $parent_participant_data) {
                        if ($participant_model->ug_id == $parent_participant_data->ug_id) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        unset($participant_model_list[$key]);
                    }

                }
            }
        }

        return $content;
    }

    /**
     * @param $participant_model
     * @return string
     */
    protected function getEditViewElementBlockParticipantEmailContent($participant_model)
    {
        $content = '';

        if (!empty($participant_model)) {
            foreach ($participant_model as $participant_data) {
                $content .= $this->getEditViewElementCardEmail($participant_data->getEntityData());
            }
        }

        return $content;
    }

    /**
     * Возвращает елемент "картка Учасников" (BlockParticipant)
     *
     * @return string (html)
     */
    public function getEditViewElementCardParticipant($participant_data_entities)
    {
        if (empty($participant_data_entities)) {
            return;
        }
        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock'),
            [
                'view'             => 'participant',
                'participant_data' => $participant_data_entities,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Email" (BlockParticipant)
     *
     * @return string (html)
     */
    public function getEditViewElementCardEmail($participant_data_entities)
    {
        if (empty($participant_data_entities)) {
            return;
        }
        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock'),
            [
                'view'             => 'email',
                'participant_data' => $participant_data_entities,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает сам контент для блока "Вложения"
     *
     * @return string (html)
     */
    public function getAttachmentsContent($upload_model, $schema, $buttons = null, $return_array = false, $thumb_size = 60)
    {
        $content = [];
        if (!empty($upload_model)) {
            foreach ($upload_model as $upload_value) {
                $content[$upload_value->getPrimaryKey()] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Attachments.Attachments'),
                    [
                        'view'           => 'element',
                        'schema'         => $schema,
                        'extension_copy' => $this->_extension_copy,
                        'extension_data' => $this->_extension_data,
                        'upload_value'   => $upload_value,
                        'buttons'        => $buttons,
                        'thumb_size'     => $thumb_size,
                    ],
                    true);
            }
        }

        if ($return_array) {
            return $content;
        } else {
            return implode('', array_values($content));
        }
    }

    /**
     * Возвращает елемент "Блок Вложения" (BlockAttachments)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockAttachments($schema, $schema_activity = null)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $data = $this->_extension_data->{$schema['params']['name']};
        $upload_model = null;
        if (!empty($data)) {
            if (is_array($data)) {
                $upload_model = UploadsModel::model()->findAll('id in (' . implode(',', $data) . ')');
            } else {
                $upload_model = UploadsModel::model()->setRelateKey($data)->findAll();
            }
        }

        $content = $this->getAttachmentsContent($upload_model, $schema);

        // добавляем файлы блока Активность
        if (!$this->_extension_data->isNewRecord && $schema_activity === null) {
            $schema_activity = $this->_extension_copy->getFieldSchemaParamsByType('activity');
            if (!empty($schema_activity)) {
                $activity_model = ActivityMessagesModel::model()->findAll([
                    'condition' => 'copy_id=:copy_id AND data_id=:data_id AND status = "asserted"',
                    'params'    => [
                        ':copy_id' => $this->_extension_copy->copy_id,
                        ':data_id' => $this->_extension_data->getPrimaryKey(),
                    ],
                ]);

                if (!empty($activity_model)) {
                    foreach ($activity_model as $activity) {
                        if (!empty($activity->attachment)) {
                            $buttons = ['download_file', 'delete_file'];
                            if ($activity->user_create != WebUser::getUserId()) {
                                $buttons = ['download_file',];
                            }

                            $upload_model = UploadsModel::model()->setRelateKey($activity->attachment)->findAll();
                            $content .= $this->getAttachmentsContent($upload_model, $schema_activity, $buttons);
                        }
                    }
                }
            }
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Attachments.Attachments'),
            [
                'view'           => 'block',
                'schema'         => $schema,
                'extension_copy' => $this->_extension_copy,
                'content'        => $content,
                'extension_data' => $this->_extension_data,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Панель" (Panel)
     *
     * @return string (html)
     */
    public function getEditViewElementPanel($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        // скрываем деактивированые модули
        $denied_relate = SchemaOperation::getDeniedRelateCopyId($schema);
        if ($denied_relate['be_fields'] == false) {
            return false;
        }
        self::$relate_module_copy_id_exception = $denied_relate['copy_id_exteptions'];

        $count = 0;
        $contents = [];

        foreach ($schema['elements'] as $element) {
            $content = $this->buildEditViewPage([$element]);
            if (empty($content)) {
                continue;
            }
            $contents[] = $content;
            $count++;
        }

        if (count($schema['elements']) != $count) {
            return false;
        }

        $content = implode('', $contents);

        if (empty($content)) {
            return false;
        }

        if ($this->_skip_element_panel) {
            $this->_skip_element_panel = false;

            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Panel.Panel'),
            [
                'schema'  => $schema,
                'content' => $content,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Метка" (Label)
     *
     * @return string (html)
     */
    public function getEditViewElementLabel($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Label.Label'),
            [
                'schema' => $schema,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок Типа поля" (BlockEdit)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockEdit($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $content = $this->buildEditViewPage($schema['elements']);

        if (empty($content)) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
            [
                'schema'  => $schema,
                'content' => $content,
                'view'    => 'block',
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Блок Типа поля Контактов" (BlockEditContact)
     *
     * @return string (html)
     */
    public function getEditViewElementBlockEditContact($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $content = $this->buildEditViewPage($schema['elements']);

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
            [
                'schema'  => $schema,
                'content' => $content,
                'view'    => 'block_contact',
            ],
            true);

        return $result;
    }

    protected function checkForSkipElementPanel($schema, $extension_data)
    {
        $result_skip = false;

        if ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS) {
            // данные Проектов
            if ($extension_data['is_bpm_operation'] === '0' || $extension_data['is_bpm_operation'] === '1') {
                if (
                    //($schema['params']['type'] == 'relate' && $schema['params']['relate_module_copy_id'] == \ExtensionCopyModel::MODULE_PROJECTS)
                    //||
                ($schema['params']['type'] == 'select' && $schema['params']['name'] == 'todo_list')
                ) {
                    $result_skip = true;
                }
            }
            // данные Процессов
            /*
        } elseif($extension_data['is_bpm_operation'] === null) {
            if($schema['params']['type'] == 'relate' && $schema['params']['relate_module_copy_id'] == \ExtensionCopyModel::MODULE_PROCESS){
                $result_skip = true;
            }
        }
            */
        }

        return $result_skip;
    }

    /**
     * Возвращает елемент "Тип поля" (Edit)
     *
     * @return string (html)
     */
    public function getEditViewElementEdit($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $default_data = null;
        if ($this->_extension_data->isNewRecord) {
            $default_data = $schema['params']['default_value'];
            if ($this->_default_data !== null && isset($this->_default_data[$schema['params']['name']])) {
                $default_data = $this->_default_data[$schema['params']['name']];
            }
        } else {
            if (!empty($this->_default_data[$schema['params']['name']])) {
                $default_data = $this->_default_data[$schema['params']['name']];
            }
        }

        $this->_skip_element_panel = $this->checkForSkipElementPanel($schema, $this->_extension_data);
        if ($this->_skip_element_panel) {
            return false;
        }

        if ($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE && $schema['params']['type'] == 'relate_dinamic') {
            return false;
        } elseif ($this->_this_template != EditViewModel::THIS_TEMPLATE_TEMPLATE && $schema['params']['type'] == 'module') {
            return false;
        }
        if ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS && $schema['params']['type'] == 'relate_dinamic' && $this->_extension_data->is_bpm_operation === null) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
            [
                'schema'                  => $schema,
                'primary_entities'        => $this->_primary_entities,
                'parent_copy_id'          => $this->_parent_copy_id,
                'parent_data_id'          => $this->_parent_data_id,
                'parent_relate_data_list' => $this->_parent_relate_data_list,
                'extension_copy'          => $this->_extension_copy,
                'extension_data'          => $this->_extension_data,
                'default_data'            => $default_data,
                'this_template'           => $this->_this_template,
                'relate'                  => $this->_relate,
                'builder_model'           => $this,
            ],
            true);

        return $result;
    }


    /*
    public function getElementSelectOptionList($schema_params){
        return $select_list = DataModel::getInstance()->setFrom($this->_extension_copy->getTableName($schema_params['params']['name']))->findAll();
    }
    */

    /**
     * Возвращает елемент "Тип поля Контактов" (EditHidden)
     *
     * @return string (html)
     */
    public function getEditViewElementEditHidden($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }
        if (isset($schema['params']['name']) && $schema['params']['name'] == 'ehc_image1') {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
            [
                'view'           => 'element_hidden',
                'schema'         => $schema,
                'parent_copy_id' => $this->_parent_copy_id,
                'extension_copy' => $this->_extension_copy,
                'extension_data' => $this->_extension_data,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Кнопка" (Button)
     *
     * @return string (html)
     */
    public function getEditViewElementButton($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $is_new_record = false;
        if ($this->_extension_data->isNewRecord) {
            $is_new_record = true;
        }

        $default_data = null;
        if (($this->_extension_data->isNewRecord || Yii::app()->request->getParam('from_template')) &&
            ($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS || $schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING)) {

            $default_data = ($schema['params']['default_value'] === '' ? null : $schema['params']['default_value']);

            if ($this->_default_data !== null && isset($this->_default_data[$schema['params']['name']])) {
                if ($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                    $default_data = $this->_default_data[$schema['params']['name']];
                    if (array_key_exists($schema['params']['name'] . '_ad', $this->_default_data)) {
                        $this->_extension_data->{$schema['params']['name'] . '_ad'} = $this->_default_data[$schema['params']['name'] . '_ad'];
                    }
                } else {
                    $default_data = $this->_default_data[$schema['params']['name']];
                }
            }
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'),
            [
                'schema'         => $schema,
                'view'           => 'button',
                'extension_copy' => $this->_extension_copy,
                'extension_data' => $this->_extension_data,
                'is_new_record'  => $is_new_record,
                'default_data'   => $default_data,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "Колонка тыблицы" (TableColumn)
     *
     * @return string (html)
     */
    public function getEditViewElementTableColumn($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.TableColumn.TableColumn'),
            [
                'schema' => $schema,
            ],
            true);

        return $result;
    }

    /**
     * Возвращает елемент "СубМодуль" (SubModule)
     *
     * @return string (html)
     */
    public function getEditViewElementSubModule($schema)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.SubModule.BlockTable'),
            [
                'data_id'        => $this->_data_id,
                'extension_copy' => $this->_extension_copy,
                'schema'         => $schema,
                'this_template'  => $this->_this_template,
            ],
            true);

        return $result;
    }

    public static function findRelateOne($copy_id, $relate_copy_id)
    {
        $relate_module_table = ModuleTablesModel::model()->findAll([
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                'params'    => [':copy_id' => $copy_id, ':relate_copy_id' => $relate_copy_id]
            ]
        );

        return (boolean)count($relate_module_table);
    }

    public static function findRelateMany($copy_id, $relate_copy_id)
    {
        $relate_module_table = ModuleTablesModel::model()->findAll([
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_many")',
                'params'    => [':copy_id' => $copy_id, ':relate_copy_id' => $relate_copy_id]
            ]
        );

        return (boolean)count($relate_module_table);
    }

    /**
     * Блокировка элемента типа module
     */
    public static function disableElementModule($copy_id, $data_id, $this_template)
    {
        $disable = false;

        if ($copy_id == \ExtensionCopyModel::MODULE_PROCESS && $this_template == false) {
            $disable = true;
        }

        if (
            $copy_id == \ExtensionCopyModel::MODULE_PROCESS &&
            $data_id &&
            (new \Process\models\ProcessAutostartByEntityModel())->findByProcessId($data_id)
        ) {
            $disable = true;
        }

        return $disable;
    }

}
