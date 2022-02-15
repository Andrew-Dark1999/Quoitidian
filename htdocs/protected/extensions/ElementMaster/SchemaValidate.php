<?php
/**
 * SchemaValidate - проверка состояния данных формы конструктора и схемы полей
 *
 * @author Alex R.
 * @version 1.0
 */

class SchemaValidate extends Validate
{

    protected $extension_copy;

    protected $schema_fature;

    private $element_block_number = 0;

    private $element_panel_number = 0;

    private $element_edit_number = 0;

    private $element_table_column_number = 0;

    private $element_sub_module_number = 0;

    private $element_button_number = 0;

    private $element_participant_exist = false;

    private $element_count_select_fields = 0;

    private $block_edit_name_list = [];

    private $table_edit_name_list = [];

    private $connected_relate = [];

    private $isBlockFieldType = false;

    private $isTableColumn = false;

    private $isBlockButton = false;

    private $saved_fields = [];

    public $module_params;

    protected $reserved_field_names = [
        'uid',
        'date_create',
        'date_edit',
        'user_create',
        'user_edit',
        'import_status',
        'this_template',
        'ehc_image1',
        'b_date_ending',
        'b_date_ending',
        'b_responsible',
        'b_subscription',
        'bl_participant',
        'bl_attachments',
        'bl_activity',
        'parent_process_id',
        'schema',
        'is_bpm_operation',
    ];

    const E_SCHEMA_EMPTY = 1;
    const E_ELEMENT_LABEL_EMPTY_TITLE = 2;
    const E_ELEMENT_LABEL_TITLE_MAX_LENGTH = 3;
    const E_ELEMENT_EDIT_NAME_EMPTY = 4;
    const E_ELEMENT_EDIT_NAME_BAD = 5;
    const E_ELEMENT_EDIT_TYPE_BAD = 6;
    const E_ELEMENT_EDIT_TYPE_DB_BAD = 7;
    const E_ELEMENT_EDIT_NAME_BAD_MAX_LENGTH = 8;
    const E_ELEMENT_EDIT_FIELD_NAME_EXISTS = 9;
    const E_ELEMENT_EDIT_RELATE_EXISTS = 10;
    const E_ELEMENT_SUBMODULE_RELATE_EXISTS = 11;
    const E_ELEMENT_SUBMODULE_NONE_FIELDS = 12;
    const E_ELEMENT_EDIT_PROHIDITED_TYPE_FILE = 13;
    const E_ELEMENT_EDIT_SELECT_VALUE_EMPTY = 14;
    const E_ELEMENT_EDIT_SELECT_VALUE_ISSET = 15;
    const E_ELEMENT_EDIT_NAME_RESERVED = 16;
    const E_ELEMENT_CHANGE_TEMPLATE = 17;
    const E_ELEMENT_SUB_MODULE_TEMPLATE_REMOVE = 18;
    const E_ELEMENT_SM_THROUGH_SECOND_MODULE = 19;
    const E_ELEMENT_SDM_TITLE_THROUGH_SECOND_MODULE = 20;
    const E_ELEMENT_NOT_SELECT_STATUS = 21;
    const E_ELEMENT_NOT_CHANGED_FIELD_TYPE = 22;
    const E_ELEMENT_PROCESS_THERE_IS_BO = 23;
    const E_ELEMENT_LABEL_BUTTON_TITLE = 24;
    const E_ELEMENT_BUTTON_TITLE_MAX_LENGTH = 25;

    public static function getInstance()
    {
        return new static();
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->extension_copy = $extension_copy;

        return $this;
    }

    protected function getReservedFieldNames()
    {
        return $this->reserved_field_names;
    }

    private function addMessage($message_ident)
    {
        switch ($message_ident) {
            case self::E_SCHEMA_EMPTY :
                $this->addValidateResult('e', Yii::t('messages', 'Not defined schema module'));
                break;
            case self::E_ELEMENT_LABEL_EMPTY_TITLE :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number]) . ': ' . Yii::t('messages', 'Not defined field name'));
                break;
            case self::E_ELEMENT_LABEL_TITLE_MAX_LENGTH :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number]) . ': ' . Yii::t('messages', 'The length of the field names longer than {s} characters', ['{s}' => 100]));
                break;
            case self::E_ELEMENT_LABEL_BUTTON_TITLE :
                $this->addValidateResult('e', Yii::t('messages', '[Button {s1}]', ['{s1}' => $this->element_button_number]) . ': ' . Yii::t('messages', 'Not defined button name'));
                break;
            case self::E_ELEMENT_BUTTON_TITLE_MAX_LENGTH :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number]) . ': ' . Yii::t('messages', 'The length of the button names longer than {s} characters', ['{s}' => 100]));
                break;
            case self::E_ELEMENT_EDIT_NAME_EMPTY :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Name record is not defined in the database'));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Name record is not defined in the database'));
                }
                break;
            case self::E_ELEMENT_EDIT_NAME_RESERVED :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Field name "{s}" for DB system reserved',
                            ['{s}' => func_get_arg(1)]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Field name "{s}" for DB system reserved',
                            ['{s}' => func_get_arg(1)]));
                }
                break;
            case self::E_ELEMENT_EDIT_FIELD_NAME_EXISTS :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'DB field name "{s}" already exists', ['{s}' => func_get_arg(1)]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'DB field name "{s}" already exists',
                            ['{s}' => func_get_arg(1)]));
                }
                break;
            case self::E_ELEMENT_NOT_CHANGED_FIELD_TYPE :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'It is forbidden to change the type of the saved field "{s}"',
                            ['{s}' => func_get_arg(1)]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'It is forbidden to change the type of the saved field "{s}"',
                            ['{s}' => func_get_arg(1)]));
                }
                break;
            case self::E_ELEMENT_EDIT_RELATE_EXISTS :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Module "{s}" already connected',
                            ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Module "{s}" in table already connected',
                            ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                }
                break;
            case self::E_ELEMENT_EDIT_NAME_BAD :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Incorrect entries "{s}" in the database',
                            ['{s}' => func_get_arg(1)]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Incorrect entries "{s}" in the database',
                            ['{s}' => func_get_arg(1)]));
                }
                break;
            case self::E_ELEMENT_EDIT_TYPE_BAD :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Invalid field type "{s}"', ['{s}' => func_get_arg(1)]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Invalid field type "{s}"', ['{s}' => func_get_arg(1)]));
                }
                break;
            case self::E_ELEMENT_EDIT_TYPE_DB_BAD :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'Invalid DB field type "{s}"',
                            ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'Invalid DB field type "{s}"',
                            ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                }
                break;
            case self::E_ELEMENT_EDIT_NAME_BAD_MAX_LENGTH :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'The length of the field names database longer than {s} characters',
                            ['{s}' => 100]));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages',
                            'The length of the field names database longer than {s} characters', ['{s}' => 100]));
                }
                break;
            case self::E_ELEMENT_SUBMODULE_RELATE_EXISTS :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}]', ['{s1}' => $this->element_block_number]) . ': ' . Yii::t('messages', 'Module "{s}" already connected', ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                break;
            case self::E_ELEMENT_SUBMODULE_NONE_FIELDS :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}]', ['{s1}' => $this->element_block_number]) . ': ' . Yii::t('messages', 'Module "{s}" - not selected fields to display', ['{s}' => ExtensionCopyModel::model()->findByPk(func_get_arg(1))->title]));
                break;
            case self::E_ELEMENT_EDIT_PROHIDITED_TYPE_FILE :
                $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number]) . ': ' . Yii::t('messages', 'Invalid type "{s1}", if the number of fields on the panel is greater than {s2}',
                        ['{s1}' => func_get_arg(1), '{s2}' => func_get_arg(2)]));
                break;
            case self::E_ELEMENT_EDIT_SELECT_VALUE_EMPTY :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'List elements contain nulls'));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'List elements contain nulls'));
                }
                if ($this->isBlockButton) {
                    $this->addValidateResult('e', Yii::t('messages', '[Button {s1}]', ['{s1}' => $this->element_button_number]) . ': ' . Yii::t('messages', 'List elements contain nulls'));
                }
                break;
            case self::E_ELEMENT_EDIT_SELECT_VALUE_ISSET :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages', 'List elements contain duplicate values'));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e',
                        Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages', 'List elements contain duplicate values'));
                }
                if ($this->isBlockButton) {
                    $this->addValidateResult('e', Yii::t('messages', '[Button {s1}]', ['{s1}' => $this->element_button_number]) . ': ' . Yii::t('messages', 'List elements contain duplicate values'));
                }
                break;
            case self::E_ELEMENT_PROCESS_THERE_IS_BO :
                if ($this->isBlockFieldType) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, field {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_edit_number]) . ': ' . Yii::t('messages',
                            'Deleting a link to the Processes module is forbidden - module objects associated with the process operators'));
                }
                if ($this->isTableColumn) {
                    $this->addValidateResult('e', Yii::t('messages', '[Block {s1}, panel {s2}, row {s3}]', ['{s1}' => $this->element_block_number, '{s2}' => $this->element_panel_number, '{s3}' => $this->element_table_column_number]) . ': ' . Yii::t('messages',
                            'Deleting a link to the Processes module is forbidden - module objects associated with the process operators'));
                }
                break;
            case self::E_ELEMENT_SM_THROUGH_SECOND_MODULE :
            case self::E_ELEMENT_SDM_TITLE_THROUGH_SECOND_MODULE :
                $s2 = '';
                $attr = func_get_arg(2);
                if (!empty($attr)) {
                    foreach ($attr as $xc) {
                        $roal_array[] = $xc['title'];
                    }
                    $s2 = implode(' -> ', $roal_array);
                }

                $this->addValidateResult('e', Yii::t('messages', 'Installation of communication "many-to-many" with module "{s1}" in 2-st and more level prohibited (chain links: {s2})', ['{s1}' => func_get_arg(1)->title, '{s2}' => $s2]));
                break;

            case self::E_ELEMENT_NOT_SELECT_STATUS :
                $this->addValidateResult('e', Yii::t('messages', 'For parameter "Finished Objects"  with meaning "Hide" requires a availability of button "Status"'));
                break;

        }
    }

    private function validateShowOnlyParticipant()
    {
        if (!empty($this->module_params['data_if_participant'])) {
            if (!$this->element_participant_exist) {
                $this->addValidateResult('e', Yii::t('messages', 'Module setting "Data visibility: Display data only to participants" can only be activated if block "Participants" or button "Responsible" exists'));
            }
        }
    }

    public function ValidateAll(array $schema_fature)
    {
        if ($this->extension_copy) {
            $this->saved_fields = $this->extension_copy->getFieldsSchemaList();
        }

        if (empty($schema_fature)) {
            $this->addMessage(self::E_SCHEMA_EMPTY);

            return $this;
        }
        $this->schema_fature = $schema_fature;

        $this->validateGeneral($schema_fature);

        $this->validatePrimaryRelate($schema_fature);
        //$this->validateSdmDinamic($schema_fature);
        $this->validateChangeTypeToOne($schema_fature);
        $this->validateDeleteTemplate();
        $this->validateSubModuleTemplateRemove($schema_fature);
        $this->validateChangeRelateSDM($schema_fature);
        $this->validateShowOnlyParticipant();
        $this->validateRelateThroughSecondModule($this->extension_copy, $schema_fature);
        $this->validateParamFinishedObject($schema_fature);

        $this->validateInstalledModules(false);

        return $this;
    }

    public function ValidateAllForDelete($schema_fature)
    {
        if (empty($schema_fature)) {
            $this->addMessage(self::E_SCHEMA_EMPTY);

            return $this;
        }
        $this->schema_fature = $schema_fature;

        //$this->validateSdmDinamicForDeleteModule();

        $this->validateInstalledModules(true);

        return $this;
    }

    /**
     * выполнение валидации в подключенных модулях
     */
    public function validateInstalledModules($confirm_only)
    {
        if ($this->beMessages()) {
            return;
        }

        $params = \ValidateModulesModel::getParams();

        if (empty($params)) {
            return;
        }

        foreach ($params as $value) {
            ExtensionCopyModel::model()->findByPk($value['copy_id'])->getModule(false);
            $obj = new $value['class']();
            $obj->setBaseObject($this);
            if ($confirm_only) {
                $obj->validateIMConfirm();
            } else {
                $obj->validateIM();
            }

            $this->validate_result = array_merge($this->validate_result, $obj->validate_result);
        }
    }

    /*
    block
    |-----block_panel
            |-----panel
                    |-----field
                    |	  |-----label
                    |	             |-----block_field_type
                    |                 		|-----field_type
    	            |
    	            |-----table
    		              |-----table_column
    			                     |-----table_header
    			                     |-----edit
                                     |-----table_footer
    |-----sub_module
    */
    private function validateGeneral($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['type'])) {
                if ($value['type'] == 'block') {
                    $this->element_block_number++;
                    $this->element_panel_number = 0;
                }
                if ($value['type'] == 'panel') {
                    $this->element_panel_number++;
                    $this->element_count_select_fields = $value['params']['active_count_select_fields'];
                    $this->table_edit_name_list = [];
                    $this->element_table_column_number = 0;
                }
                if ($value['type'] == 'block_field_type') {
                    $this->isBlockFieldType = true;
                    $this->isTableColumn = false;
                    $this->isBlockButton = false;
                    $this->element_edit_number = 0;
                }
                if ($value['type'] == 'table_column') {
                    $this->isBlockFieldType = false;
                    $this->isTableColumn = true;
                    $this->isBlockButton = false;
                    $this->element_table_column_number++;
                }
                if ($value['type'] == 'block_button') {
                    $this->isBlockFieldType = false;
                    $this->isTableColumn = false;
                    $this->isBlockButton = true;
                }
                if ($value['type'] == 'participant') {
                    $this->element_participant_exist = true;
                }
                if ($value['type'] == 'sub_module') {
                    $this->element_sub_module_number++;
                    $this->validateSubModule($value['params']);
                }
                if ($value['type'] == 'label') {
                    $this->validateLabel($value['params']);
                }
                if ($value['type'] == 'edit') {
                    $this->element_edit_number++;
                    $this->validateEdit($value['params']);
                }
                if ($value['type'] == 'button') {
                    if ($value['params']['type'] == 'relate_participant' && $value['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                        $this->element_participant_exist = true;
                    }

                    $this->element_button_number++;
                    $this->validateEdit($value['params']);
                    $this->validateButtonTitle($value['params']);
                }

                if (isset($value['elements'])) {
                    $this->validateGeneral($value['elements']);
                }
            }
        }
    }

    /**
     * Проверка елемента Label
     */
    private function validateLabel($params)
    {
        if (empty($params['title'])) {
            $this->addMessage(self::E_ELEMENT_LABEL_EMPTY_TITLE);
        }
        if (!empty($params['title']) && strlen($params['title']) > 100) {
            $this->addMessage(self::E_ELEMENT_LABEL_TITLE_MAX_LENGTH);
        }
    }

    /**
     * Проверка елемента Label
     */
    private function validateButtonTitle($params)
    {
        if (in_array($params['type_view'], [Fields::TYPE_VIEW_BUTTON_RESPONSIBLE])) {
            if ($params['title'] === null || $params['title'] === '') {
                $this->addMessage(self::E_ELEMENT_LABEL_BUTTON_TITLE);
            }
            if (!empty($params['title']) && strlen($params['title']) > 100) {
                $this->addMessage(self::E_ELEMENT_BUTTON_TITLE_MAX_LENGTH);
            }
        }
    }

    /**
     * проверка на зарезервированое название поля
     */
    protected function validateFieldNameResived($params)
    {
        if ($params['type_view'] == Fields::TYPE_VIEW_DEFAULT && in_array($params['name'], $this->getReservedFieldNames())) {
            $this->addMessage(self::E_ELEMENT_EDIT_NAME_RESERVED, $params['name']);
        }
    }

    /**
     * Проверка елемента Edit
     */
    private function validateEdit($params)
    {
        if (empty($params['name'])) {
            $this->addMessage(self::E_ELEMENT_EDIT_NAME_EMPTY);
        } else {

            // проверка на зарезервированое название поля
            $this->validateFieldNameResived($params);

            // проверка названия поля для БД
            if ($this->isBlockFieldType || $this->isBlockButton) { // для простого поля и кнопок
                // дубль названия поля
                if (in_array($params['name'], $this->block_edit_name_list)) {
                    $this->addMessage(self::E_ELEMENT_EDIT_FIELD_NAME_EXISTS, $params['name']);
                } else {
                    if (
                        $this->saved_fields &&
                        array_key_exists($params['name'], $this->saved_fields) &&
                        $params['type'] != $this->saved_fields[$params['name']]['params']['type'] &&
                        $params['name'] != 'module_title'
                    ) {
                        $this->addMessage(self::E_ELEMENT_NOT_CHANGED_FIELD_TYPE, $params['name']);
                    }
                }
                $this->block_edit_name_list[] = $params['name'];
                // дубль подключения модуля
                if ($params['relate_module_copy_id']) {
                    if (in_array($params['relate_module_copy_id'], $this->connected_relate)) {
                        $this->addMessage(self::E_ELEMENT_EDIT_RELATE_EXISTS, $params['relate_module_copy_id']);
                    }
                    $this->connected_relate[] = $params['relate_module_copy_id'];
                }
            }
            if ($this->isTableColumn) { // для поля в таблице
                // дубль названия поля
                if (in_array($params['name'], $this->table_edit_name_list)) {
                    $this->addMessage(self::E_ELEMENT_EDIT_FIELD_NAME_EXISTS, $params['name']);
                }
                $this->table_edit_name_list[] = $params['name'];
                // дубль подключения модуля
                if ($params['relate_module_copy_id']) {
                    if (in_array($params['relate_module_copy_id'], $this->connected_relate)) {
                        $this->addMessage(self::E_ELEMENT_EDIT_RELATE_EXISTS, $params['relate_module_copy_id']);
                    }
                    $this->connected_relate[] = $params['relate_module_copy_id'];
                }
            }

            if (!preg_match('/^([a-zA-z])+([0-9a-zA-z])*$/', $params['name'])) {
                $this->addMessage(self::E_ELEMENT_EDIT_NAME_BAD, $params['name']);
            }
            if (strlen($params['name']) > 100) {
                $this->addMessage(self::E_ELEMENT_EDIT_NAME_BAD_MAX_LENGTH);
            }

            if (!array_key_exists($params['type'], Fields::getInstance()->getFields())) {
                $this->addMessage(self::E_ELEMENT_EDIT_TYPE_BAD, $params['type']);
            } else {
                if ($params['type'] == 'select') {
                    $is_set_values = [];
                    foreach ($params['values'] as $select_value) {
                        if ($select_value === '') {
                            if ($params['name'] != 'todo_list') {
                                $this->addMessage(self::E_ELEMENT_EDIT_SELECT_VALUE_EMPTY, $params['name']);
                            }
                        } else {
                            if (!empty($is_set_values) && in_array($select_value, $is_set_values) && $params['name'] != 'todo_list') {
                                $this->addMessage(self::E_ELEMENT_EDIT_SELECT_VALUE_ISSET, $params['name']);
                            }
                        }
                        $is_set_values[] = $select_value;
                    }
                }
            }
            //if(!empty($params['type_db']) && !array_key_exists($params['type_db'], FieldTypes::getInstance()->getType())) $this->addMessage(self::E_ELEMENT_EDIT_TYPE_DB_BAD, $params['type_db']);
            if ($this->element_count_select_fields > 1 && ($params['type'] == 'file' || $params['type'] == 'file_image')) {
                $this->addMessage(self::E_ELEMENT_EDIT_PROHIDITED_TYPE_FILE, Fields::getInstance()->getTitle($params['type']), 1);
            }
        }
    }

    /**
     * Проверка елемента SubModule
     */
    private function validateSubModule($params)
    {
        if ($params['relate_module_copy_id']) {
            if (in_array($params['relate_module_copy_id'], $this->connected_relate)) {
                $this->addMessage(self::E_ELEMENT_SUBMODULE_RELATE_EXISTS, $params['relate_module_copy_id']);
            }
            $this->connected_relate[] = $params['relate_module_copy_id'];
        }

        if (empty($params['values'])) {
            $this->addMessage(self::E_ELEMENT_SUBMODULE_NONE_FIELDS, $params['relate_module_copy_id']);
        }

    }

    /**
     * проверка на наличия первичного поля в подчиненном модуле
     */
    private function isPrimaryModule($primary_copy_id, $relate_copy_id)
    {
        $result = false;

        if (ModuleTablesModel::isSetRelate($primary_copy_id, $relate_copy_id, 'relate_module_many') &&
            ModuleTablesModel::isSetRelate($relate_copy_id, $primary_copy_id, 'relate_module_one')) {
            $result = true;
        }

        return $result;
    }

    /**
     * возвращает copy_id первичного модуля
     */
    private function getPrimaryPci($extension_copy)
    {
        $relate_model = new EditViewRelateModel();
        $relate_model
            ->setVars(['extension_copy' => $extension_copy])
            ->setAutoPci();
        if ($pci = $relate_model->getPci()) {
            return $pci;
        } else {
            return $extension_copy->copy_id;
        }
    }

    /**
     * валидация типа relate на изменение первичного поля
     */
    private function validatePrimaryRelate($schema_fature)
    {
        if ($this->beMessages()) {
            return;
        }
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        $primary_copy_id = $this->getPrimaryPci($this->extension_copy);
        $relate_params = $this->extension_copy->getFieldSchemaParamsByType('relate');
        $relate_params_fature = $this->extension_copy->getFieldSchemaParamsByType('relate', $this->extension_copy->getSchemaParse($schema_fature));

        if (empty($relate_params)) {
            return;
        }
        if (empty($relate_params_fature)) {
            return;
        }

        // если первичный модуль не первое поле типа relate, первичное поле отсутствует, или первичный модуль сам модуль
        if ($primary_copy_id != $relate_params['params']['relate_module_copy_id']) {
            return;
        }

        // если первычное поле (модуль) - этот же модуль  
        if ($primary_copy_id != $relate_params['params']['relate_module_copy_id']) {
            return;
        }

        // если первычное поле не изменилось
        if (!empty($relate_params) && $relate_params['params']['relate_module_copy_id'] == $relate_params_fature['params']['relate_module_copy_id']) {
            return;
        }

        // проверка на наличие соответствующих связей - то есть, новое поле (модуль) типа relate есть первичным
        $relate_params_fature_primary =
            (ModuleTablesModel::isSetRelate($relate_params_fature['params']['relate_module_copy_id'], $this->extension_copy->copy_id, 'relate_module_many') &&
                ModuleTablesModel::isSetRelate($this->extension_copy->copy_id, $relate_params_fature['params']['relate_module_copy_id'], 'relate_module_one'));

        if ($relate_params_fature_primary == false) {
            return;
        }

        $params = [ValidateConfirmActions::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE => ['primary_copy_id' => $relate_params_fature['params']['relate_module_copy_id']]];

        $relates_list = SchemaOperation::getRelates($this->extension_copy->getSchemaParse($schema_fature), [$relate_params_fature['params']['relate_module_copy_id']]);
        $submodules_list = SchemaOperation::getSubModules($this->extension_copy->getSchemaParse($schema_fature));
        if (empty($relates_list) && empty($submodules_list)) {
            return;
        }

        $relates_copy_id_list = [];
        foreach ($relates_list as $relate) {
            $relates_copy_id_list[] = $relate['params']['relate_module_copy_id'];
        }
        foreach ($submodules_list as $relate) {
            $relates_copy_id_list[] = $relate['sub_module']['params']['relate_module_copy_id'];
        }

        $sub_module_list = [];
        $primary_extension_copy = ExtensionCopyModel::model()->findByPk($relate_params_fature['params']['relate_module_copy_id']);
        $sub_modules = SchemaOperation::getSubModules($primary_extension_copy->getSchemaParse());

        if (!empty($sub_modules)) {

            foreach ($sub_modules as $module) {
                $module = $module['sub_module'];
                $module_extension_copy = ExtensionCopyModel::model()->findByPk($module['params']['relate_module_copy_id']);
                if (in_array($module['params']['relate_module_copy_id'], $relates_copy_id_list) == false) {
                    continue;
                }
                if ($this->isPrimaryModule($relate_params_fature['params']['relate_module_copy_id'], $module['params']['relate_module_copy_id']) == false) {
                    continue;
                }

                $params[ValidateConfirmActions::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE]['relate_copy_id'][] = $module['params']['relate_module_copy_id'];
                $sub_module_list[] = $module_extension_copy->title;
            }
        }

        if (!empty($sub_module_list)) {
            $this->addParams($params);
            $this->addValidateResultConfirm(
                'c',
                Yii::t(
                    'messages',
                    'As a result of changes in the primary field "{s1}" to "{s2}" would be violated in connection sabmodules: "{s3}".<br /> Continue?',
                    [
                        '{s1}' => $relate_params['title'],
                        '{s2}' => $relate_params_fature['title'],
                        '{s3}' => implode('", "', $sub_module_list),
                    ]
                ),
                ValidateConfirmActions::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE,
                false
            );
        }

    }






    /**
     * валидация удаления поля СДМ на использование как динамического модуля в динамичном типе relate_dinamic
     */
    /*
    private function validateSdmDinamic($schema_fature){
        if($this->beMessages()) return;
        if(empty($this->extension_copy)) return;
        if($this->extension_copy->isNewRecord) return;


        //Used
        $sdm_used = $this->extension_copy->getFieldSchemaParamsByType(array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_STRING), null, false);
        $sm_used = SchemaOperation::getInstance()->getSubModules($this->extension_copy->getSchemaParse());

        $relate_used = array();
        if($sdm_used){
            foreach($sdm_used as $item){
                $relate_used[] = $item['params']['relate_module_copy_id'];
            }
        }
        if($sm_used){
            foreach($sm_used as $item){
                $relate_used[] = $item['sub_module']['params']['relate_module_copy_id'];
            }
        }

        if(!$relate_used || !in_array(\ExtensionCopyModel::MODULE_PROCESS, $relate_used)) return;

        //New
        $schema_fature_parsed = $this->extension_copy->getSchemaParse($schema_fature, array(), array(), false);
        $sdm_new = $this->extension_copy->getFieldSchemaParamsByType(array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_STRING), $schema_fature_parsed, false);
        $sm_new = SchemaOperation::getInstance()->getSubModules($schema_fature_parsed);

        $relate_new = array();
        if($sdm_new){
            foreach($sdm_new as $item){
                $relate_new[] = $item['params']['relate_module_copy_id'];
            }
        }
        if($sm_new){
            foreach($sm_new as $item){
                $relate_new[] = $item['sub_module']['params']['relate_module_copy_id'];
            }
        }

        if($relate_new && in_array(\ExtensionCopyModel::MODULE_PROCESS, $relate_new)) return;

        //Check processes BO
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        $there_is = \Process\models\OperationsModel::thereIsSettedBindingObjectOperations($this->extension_copy->copy_id);


        if($there_is === true){
            $this->addMessage(self::E_ELEMENT_PROCESS_THERE_IS_BO); // Есть процессы и операторы
        } else if($there_is === false){ // Есть только процессы - очищается только таблица {{process}}
            $params = array(ValidateConfirmActions::ACTION_PROCESS_BO_CLEAR => array('copy_id' => $this->extension_copy->copy_id));

            $this->addParams($params);
            $this->addValidateResultConfirm(
                'c',
                Yii::t(
                    'messages',
                    'If you delete the associated Processes module, all associated objects with the current module will be deleted.<br /> Continue?',
                    array()
                ),
                ValidateConfirmActions::ACTION_PROCESS_BO_CLEAR,
                false
            );
        }
    }
    */

    /**
     * валидация удаления поля СДМ на использование как динамического модуля в динамичном типе relate_dinamic
     */
    /*
    private function validateSdmDinamicForDeleteModule(){
        if($this->beMessages()) return;
        if(empty($this->extension_copy)) return;

        //Check processes BO
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
        $there_is = \Process\models\OperationsModel::thereIsSettedBindingObjectOperations($this->extension_copy->copy_id);

        if($there_is === true){
            $this->addValidateResult('e', Yii::t('base', 'Module') . ' ' . $this->extension_copy->title . '. ' . Yii::t('messages', 'Deleting is forbidden - module objects associated with the process operators'));
        }
    }
    */

    /**
     * валидация смены/установки типа связи с МНОГО, МКО, МКМ на ОДИН, ОКО, ОКМ
     */
    private function validateChangeTypeToOne($schema_fature)
    {
        if ($this->beMessages()) {
            return;
        }
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        $relates_old = SchemaOperation::getInstance()->getAllElementsWhereType($this->extension_copy->getSchemaParse(), ['relate', 'relate_string']);
        $relates_new = SchemaOperation::getInstance()->getAllElementsWhereType($schema_fature, ['relate']);

        if (empty($relates_new)) {
            return;
        }

        $params = [];
        foreach ($relates_new as $relate_new) {

            if ($this->inRelates($relate_new['relate_module_copy_id'], $relates_old) == true) {
                continue;
            }

            $relate_table = ModuleTablesModel::model()->find([
                'condition' => '(copy_id=:copy_id AND relate_copy_id=:relate_copy_id) OR (copy_id=:relate_copy_id AND relate_copy_id=:copy_id) AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id'        => $relate_new['relate_module_copy_id'],
                    ':relate_copy_id' => $this->extension_copy->copy_id
                ]
            ]);

            if (empty($relate_table)) {
                continue;
            }

            $r_extension_copy = ExtensionCopyModel::model()->findByPk($relate_new['relate_module_copy_id']);
            $double_relates = $this->findDoubleRelates($relate_table);

            if ($double_relates) {
                $r_params = $r_extension_copy->getFieldSchemaParams($relate_new['name'], $r_extension_copy->getSchemaParse($schema_fature));
                $params[] = $relate_new['relate_module_copy_id'];

                $this->addValidateResultConfirm(
                    'c',
                    Yii::t(
                        'messages',
                        'After the creation field "{s1}" will delete the previously created connection module "{s2}". <br /> Continue?',
                        ['{s1}' => $r_params['title'], '{s2}' => $r_extension_copy->title]
                    ),
                    ValidateConfirmActions::ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE,
                    false
                );
            }
        }

        if (!empty($params)) {
            $params = [
                ValidateConfirmActions::ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE =>
                    [
                        'parent_copy_id' => $this->extension_copy->copy_id,
                        'relate_copy_id' => $params,
                    ]
            ];
            $this->addParams($params);
        }
    }

    /**
     * поиск наличия нескольких связей между данными модулей
     */
    private function findDoubleRelates($relate_table)
    {
        $data_model = new DataModel();
        $data_model
            ->setText('
                SELECT ' . $relate_table->relate_field_name . ', count(*) as xcount
                FROM {{' . $relate_table->table_name . '}}
                group by ' . $relate_table->relate_field_name . '
                having xcount > 1                    
            ');
        $data_model = $data_model->findAll();

        return (!empty($data_model) ? true : false);
    }

    /**
     * поиск в массиве модуля по его copy_id
     */
    private function inRelates($copy_id, $relates)
    {
        $result = false;
        if (empty($relates)) {
            return false;
        }

        foreach ($relates as $relate) {
            if ($relate['relate_module_copy_id'] == $copy_id) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * удаление шаблонов
     */
    private function validateDeleteTemplate()
    {
        if ($this->beMessages()) {
            return;
        }
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        if (isset($this->module_params['is_template']) && (integer)$this->module_params['is_template'] === 0 &&
            ($this->extension_copy->is_template == \ExtensionCopyModel::IS_TEMPLATE_ENABLE || $this->extension_copy->is_template == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY)) {
            $params = [
                ValidateConfirmActions::ACTION_MODULE_DELETE_TEMPLATES => [
                    'copy_id' => $this->extension_copy->copy_id,
                ]
            ];
            $this->addParams($params);
            $this->addValidateResultConfirm(
                'c',
                Yii::t(
                    'messages',
                    'You have disabled templates. This will erase all data templates. Continue?'
                ),
                ValidateConfirmActions::ACTION_MODULE_DELETE_TEMPLATES,
                false
            );
        }
    }

    /**
     * поиск модуля-шаблона
     */
    private function findSubModule($copy_id, $schema_fature_params)
    {
        if (empty($schema_fature_params)) {
            return false;
        }
        $result = false;
        foreach ($schema_fature_params as $schema) {
            if ($schema['sub_module']['params']['relate_module_copy_id'] == $copy_id) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * поиск СДМ
     */
    private function findRelate($copy_id, $schema_relates)
    {
        if (empty($schema_relates)) {
            return false;
        }
        $result = false;
        foreach ($schema_relates as $schema) {
            if ($schema['relate_module_copy_id'] == $copy_id) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Проверка елемента SubModule
     */
    private function validateSubModuleTemplateRemove($schema_fature)
    {
        if ($this->beMessages()) {
            return;
        }
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        $schema = SchemaOperation::getSubModules($this->extension_copy->getSchemaParse());
        $schema_fature_params = SchemaOperation::getSubModules(ExtensionCopyModel::model()->getSchemaParse($schema_fature));
        if (empty($schema)) {
            return;
        }

        $be = true;
        $title = '';
        foreach ($schema as $schema_value) {
            if (!$be) {
                break;
            }
            if ((boolean)$schema_value['sub_module']['params']['relate_module_template'] == false) {
                continue;
            }
            if (empty($schema_fature_params) && (boolean)$schema_value['sub_module']['params']['relate_module_template'] == true) {
                $be = false;
                $title = $schema_value['sub_module']['title'];
                break;
            }
            if (!$this->findSubModule($schema_value['sub_module']['params']['relate_module_copy_id'], $schema_fature_params)) {
                $be = false;
                $title = $schema_value['sub_module']['title'];
                break;
            }
        }

        if ($be == false) {
            $this->addValidateResultConfirm(
                'c',
                Yii::t(
                    'messages',
                    'Removed Submodul-template "{s}". After saving the data will be removed from the module "{s}" formulaic attachment to the data of the current module.<br /> Continue?',
                    ['{s}' => $title]
                ),
                ValidateConfirmActions::ACTION_SUB_MODULE_TEMPLATE_REMOVE,
                false
            );
        }
    }

    /**
     * Проверка изменения типа связи СДМ через поле Название на поле СДМ
     */
    private function validateChangeRelateSDM($schema_fature)
    {
        if ($this->beMessages()) {
            return;
        }
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        $schema_primary = $this->extension_copy->getPrimaryField(null, false);

        $schema_fature_params = SchemaOperation::getRelates(ExtensionCopyModel::model()->getSchemaParse($schema_fature));

        if (empty($schema_fature_params)) {
            return;
        }

        if (count($schema_primary) > 1) {
            foreach ($schema_primary as $sv) {
                if ($sv['params']['type'] == 'relate_string') {
                    $schema_primary = $sv;
                    break;
                }
            }
        } else {
            $schema_primary = $schema_primary[0];
        }

        if (empty($schema_primary) || $schema_primary['params']['type'] != 'relate_string') {
            return;
        }

        $title = '';
        $be = false;
        foreach ($schema_fature_params as $schema_value) {
            if ($schema_value['params']['relate_module_copy_id'] == $schema_primary['params']['relate_module_copy_id']) {
                $be = true;
                $title = ExtensionCopyModel::model()->findByPk($schema_value['params']['relate_module_copy_id'])->title;
                break;
            }
        }

        if ($be == true) {
            $this->addValidateResultConfirm(
                'c',
                Yii::t(
                    'messages',
                    'Changed coupler module "{s}" Existing communication between the modules will be removed.<br /> Continue?',
                    ['{s}' => $title]
                ),
                ValidateConfirmActions::ACTION_RELATE_CHENGED_SDM,
                false
            );
        }
    }

    /**
     * поиск новый добавленных СМ и СДМ-название
     */
    private function getAddedRelateElements($schema_fature, $only_new = true)
    {
        if (empty($this->extension_copy)) {
            return;
        }
        if ($this->extension_copy->isNewRecord) {
            return;
        }

        $result = [];

        $schema = SchemaOperation::getSubModules($this->extension_copy->getSchemaParse());
        $schema_fature_params = SchemaOperation::getSubModules(ExtensionCopyModel::model()->getSchemaParse($schema_fature));

        if (!empty($schema_fature_params)) {
            foreach ($schema_fature_params as $schema_value) {
                if ($only_new) {
                    if ($this->findSubModule($schema_value['sub_module']['params']['relate_module_copy_id'], $schema) == false) {
                        $result['sub_module'][] = $schema_value['sub_module'];
                    }
                } else {
                    $result['sub_module'][] = $schema_value['sub_module'];
                }
            }
        }

        $schema_fature_title_params = SchemaOperation::getInstance()->getAllElementsWhereType($this->extension_copy->getSchemaParse($schema_fature), ['relate_string']);
        if (!empty($schema_fature_title_params)) {
            foreach ($schema_fature_title_params as $schema_value) {
                if ($only_new) {
                    $schema_title_params = SchemaOperation::getInstance()->getAllElementsWhereType($this->extension_copy->getSchemaParse(), ['relate_string']);
                    if (empty($schema_title_params) || (!empty($schema_title_params) && $this->findRelate($schema_value['relate_module_copy_id'], $schema_title_params) == false)) {
                        $result['relate_string'][]['params'] = $schema_value;
                    }
                } else {
                    $result['relate_string'][]['params'] = $schema_value;
                }
            }
        }

        return $result;
    }

    /**
     * контроль на запрещение создания связи МКМ через 2-й и более модуль, если цепочка связаных модулей построена по типах связей МНОГИЕ, МКМ, МКО
     */
    private $_vrtsm_object = false;

    private $_vrtsm_road;

    private function validateRelateThroughSecondModule($extension_copy, $schema_fature)
    {
        $relate_elements = $this->getAddedRelateElements($schema_fature);

        foreach (['sub_module', 'relate_string'] as $element_type) {
            if (isset($relate_elements[$element_type]) == false) {
                continue;
            }
            foreach ($relate_elements[$element_type] as $element) {
                $this->_vrtsm_object = false;
                $this->_vrtsm_road = [];
                $relate_copy_id = $element['params']['relate_module_copy_id'];
                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate_copy_id);

                self::$_frtsm_cicle = 0;
                $this->findRelateThroughSecondModule($extension_copy->copy_id, $relate_copy_id, $extension_copy->copy_id, true);

                if ($this->_vrtsm_object == true) {
                    $this->prepareVrtsmRoad();
                    $road = array_merge([$relate_extension_copy], $this->_vrtsm_road, [$extension_copy]);
                    if ($element_type == 'sub_module') {
                        $this->addMessage(self::E_ELEMENT_SM_THROUGH_SECOND_MODULE, $relate_extension_copy, $road);
                    } elseif ($element_type == 'relate_string') {
                        $this->addMessage(self::E_ELEMENT_SDM_TITLE_THROUGH_SECOND_MODULE, $relate_extension_copy, $road);
                    }
                }
            }
        }
    }

    private function prepareVrtsmRoad()
    {
        if ($this->_vrtsm_road == false) {
            return;
        }

        $result = [];
        $list = [];
        foreach ($this->_vrtsm_road as $vrtsm_road) {
            if ($list && in_array($vrtsm_road->copy_id, $list)) {
                break;
            }
            $list[] = $vrtsm_road->copy_id;
            $result[] = $vrtsm_road;
        }

        $this->_vrtsm_road = $result;
    }

    private static $_frtsm_cicle = 0;

    private $_frtsm_cicle_break = 20;

    /**
     * поиск связи МКМ через 2-й и более модуль, если цепочка связаных модулей построена по типах связей МНОГИЕ, МКМ, МКО
     * результат возвращается в $this->_vrtsm_object
     */
    private function findRelateThroughSecondModule($copy_id, $relate_copy_id, $parent_copy_id, $is_first_cycle = true, $road = [])
    {
        self::$_frtsm_cicle++;
        if (self::$_frtsm_cicle == $this->_frtsm_cicle_break) {
            $this->_vrtsm_object = true;
            $this->_vrtsm_road = $road;

            return;
        }

        if ($this->_vrtsm_object) {
            return;
        }
        // проверка в первом цикле наличия обратной связи
        if ($is_first_cycle) {
            /*
            $relate_table = ModuleTablesModel::model()->findAll(array(
                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")' ,
                                    'params' => array(
                                                    ':copy_id' => $relate_copy_id,
                                                    ':relate_copy_id' => $copy_id)));
    
                                
            if(!empty($relate_table)){
                foreach($relate_table as $relate){
                    if($relate['type'] == 'relate_module_one'){
                        $relate_primary_params = ExtensionCopyModel::model()->findByPk($relate->relate_copy_id)->getPrimaryField(null, false);
                        if(!empty($relate_title_params))
                        foreach($relate_primary_params as $primary_params){
                            if($primary_params['params']['type'] != 'relate_string') continue;
                            if($primary_params['params']['relate_module_copy_id'] == $parent_copy_id) return;
                        }
                            
                    } elseif($relate['type'] == 'relate_module_many'){
                        return;
                    }
                }
            }
            */
            $relate_table = ModuleTablesModel::model()->findAll([
                'condition' => 'copy_id = :copy_id AND relate_copy_id != :relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id'        => $relate_copy_id,
                    ':relate_copy_id' => $copy_id
                ]
            ]);
        } else {
            $relate_table = ModuleTablesModel::model()->findAll([
                'condition' => 'copy_id = :copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id' => $relate_copy_id
                ]
            ]);
        }

        // дальнейшая проверка во втором и более циклах
        if (!empty($relate_table)) {
            foreach ($relate_table as $relate) {
                if ($relate->relate_copy_id == $copy_id) {
                    continue;
                }

                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate->relate_copy_id);
                $relate_primary_params = $relate_extension_copy->getPrimaryField(null, false);
                if ($relate['type'] == 'relate_module_one') {
                    if (!empty($relate_title_params)) {
                        foreach ($relate_primary_params as $primary_params) {
                            if ($is_first_cycle == false) {
                                if ($primary_params['params']['type'] != 'relate_string') {
                                    continue;
                                }
                                if ($relate->relate_copy_id == $parent_copy_id) {
                                    $this->_vrtsm_object = true;
                                    $this->_vrtsm_road = $road;

                                    return;
                                }
                            }
                            $road[] = $relate_extension_copy;
                            $this->findRelateThroughSecondModule($relate->copy_id, $relate->relate_copy_id, $parent_copy_id, false, $road);
                        }
                    }

                } elseif ($relate['type'] == 'relate_module_many') {
                    if ($relate->relate_copy_id == $parent_copy_id && $is_first_cycle == false) {
                        $this->_vrtsm_object = true;
                        $this->_vrtsm_road = $road;

                        return;
                    } else {
                        $road[] = $relate_extension_copy;
                        $this->findRelateThroughSecondModule($relate->copy_id, $relate->relate_copy_id, $parent_copy_id, false, $road);
                    }
                }
            }
        }

    }

    private function validateParamFinishedObject($schema_fature)
    {
        if (!$this->module_params[\ConstructorModel::SETTING_FINISHED_OBJECTS]) {
            return;
        }

        // search button "status"
        $ecm = \ExtensionCopyModel::model();
        $param = $ecm->getStatusField($ecm->getSchemaParse($schema_fature));
        if (empty($param)) {
            $this->addMessage(self::E_ELEMENT_NOT_SELECT_STATUS);
        }

    }

}
