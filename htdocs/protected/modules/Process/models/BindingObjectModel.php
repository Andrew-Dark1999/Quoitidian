<?php
/**
 * BindingObjectModel - Связанные обьекты. Используються в процессах
 *
 * @author Alex R.
 */

namespace Process\models;

class BindingObjectModel
{

    const ACTION_CHECK = 'action_check';
    const ACTION_UPDATE = 'action_update';

    const RM_FIELD_NAME = 'related_module';

    public $_vars;

    private $_action = null;

    public $_be_error = false;

    private $_messages = [];

    private $_process_model;

    private $_relate_copy_id;

    private $_access_update = true;

    public static function getInstance()
    {
        return new self();
    }

    public function setRelateCopyId($relate_copy_id)
    {
        $this->_relate_copy_id = $relate_copy_id;

        return $this;
    }

    public function getRelateCopyId()
    {
        return $this->_relate_copy_id;
    }

    public function setAccessUpdate($access_update)
    {
        $this->_access_update = $access_update;

        return $this;
    }

    public function setVars($vars, $process_refresh = false)
    {
        $this->_vars = $vars;

        if (isset($vars['action'])) {
            $this->_action = $vars['action'];
        }

        $this->_process_model = ProcessModel::getInstance($vars['process_id'], $process_refresh);
        if (!empty($this->_process_model)) {
            $this->_relate_copy_id = $this->_process_model->{self::RM_FIELD_NAME};
        }

        return $this;
    }

    private function addMessage($name, $text)
    {
        $this->_messages[$name] = $text;

        return $this;
    }

    private function addMessageError($name, $text)
    {
        $this->addMessage($name, $text);
        $this->_be_error = true;

        return $this;
    }

    public function getMessage($name)
    {
        if (isset($this->_messages[$name])) {
            return $this->_messages[$name];
        }
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * validate
     */
    public function validate()
    {
        switch ($this->_action) {
            case self::ACTION_UPDATE :
                // 1
                if (empty($this->_vars['attributes']) || empty($this->_vars['attributes']['copy_id'])) {
                    return $this->addMessageError('relate_object_block', \Yii::t('messages', 'Not defined parameters'));
                }

                // 2
                if (empty($this->_vars['attributes']['data_id'])) {
                    return $this->addMessageError('relate_object_block', \Yii::t('ProcessModule.messages', 'Not defined Object instance'));
                }

                // 3
                $vars = [
                    'bpm_params' => [
                        'objects' => [
                            BpmParamsModel::OBJECT_BINDING_OBJECT => [
                                'attributes' => [
                                    'copy_id' => $this->_vars['attributes']['copy_id'],
                                    'data_id' => $this->_vars['attributes']['data_id'],
                                ]
                            ]
                        ]
                    ]
                ];

                $process_model = ProcessModel::getInstance();
                $process_model
                    ->setVars($vars)
                    ->validateBeforeCreateFromTemplate('checkCreateFromTemplateParticipantTypeConst');

                $result = $process_model->getResult();

                if ($result['status'] == false) {
                    if (!empty($result['messages'])) {
                        foreach ($result['messages'] as $field_name => $message) {
                            return $this->addMessageError($field_name, implode('', $message));
                        }

                    }
                }

                break;
        }

        return $this;
    }

    /**
     * run
     */
    public function run()
    {
        if ($this->_be_error) {
            return $this;
        }

        switch ($this->_action) {
            case self::ACTION_CHECK:
                $this->actionCheck();
                break;
            case self::ACTION_UPDATE:
                $this->actionUpdate();
                break;
        }

        return $this;
    }

    /**
     * actionCheck
     */
    private function actionCheck()
    {
        // связь с модулем не указана
        if (empty($this->_relate_copy_id)) {
            return;
        }

        if (ProcessModel::getInstance()->getMode() == ProcessModel::MODE_CONSTRUCTOR) {
            $this->_be_error = true;

            return;
        };

        $is = $this->isSetRelateByProcessId();

        if ($is === null || $is === true) {
            return;
        }

        $this->_be_error = true;
    }

    public function getRelateModuleTableData()
    {
        return \ModuleTablesModel::getRelateModuleTableData(\ExtensionCopyModel::MODULE_PROCESS, $this->_relate_copy_id);
    }

    private function isSetRelateTypeModuleOneWithRelateModule()
    {
        return \ModuleTablesModel::isSetRelate($this->_relate_copy_id, \ExtensionCopyModel::MODULE_PROCESS, \ModuleTablesModel::TYPE_RELATE_MODULE_ONE);
    }

    /**
     * actionUpdate
     */
    private function actionUpdate()
    {
        $b = $this->updateRelateTableInDb();

        if ($b) {
            $this->replaceResponsibleByParticipantTypeConst();
        }
    }

    /**
     * updateRelateTableInDb
     */
    private function updateRelateTableInDb()
    {
        if ($this->_access_update == false) {
            return;
        }

        $is = $this->isSetRelateByProcessId();

        if ($is === null || $is === true) {
            return;
        }

        $relate_data = $this->getRelateModuleTableData();
        if ($relate_data === null) {
            return;
        }

        \DataModel::getInstance()->insert(
            '{{' . $relate_data['table_name'] . '}}',
            [
                $relate_data['parent_field_name'] => $this->_vars['process_id'],
                $relate_data['relate_field_name'] => $this->_vars['attributes']['data_id'],
            ]);

        return true;
    }

    private function replaceResponsibleByParticipantTypeConst()
    {
        $process_model = ProcessModel::getInstance();

        if ($process_model == false) {
            return;
        }

        $process_model
            ->replaceResponsibleByParticipantTypeConst('process')
            ->replaceResponsibleByParticipantTypeConst('operations');
    }

    /**
     * isSetRelateByProcessId - проверка наличия связаного модуля
     */
    public function isSetRelateByProcessId()
    {
        $result = $this->getRelateDataByProcessId(true);

        if ($result === null) {
            return;
        } else {
            return (boolean)$result;
        }
    }

    /**
     * isSetRelateByDataId - проверка наличия связаного модуля
     */
    public function isSetRelateByDataId()
    {
        $result = $this->getRelateDataByDataId(true);

        if ($result === null) {
            return;
        } else {
            return (boolean)$result;
        }
    }

    /**
     * getRelateDataByProcessId
     */
    public function getRelateDataByProcessId($return_count = false)
    {
        $relate_data = $this->getRelateModuleTableData();
        if (empty($relate_data)) {
            return;
        }

        $data_model = \DataModel::getInstance()
            ->setFrom('{{' . $relate_data['table_name'] . '}}')
            ->setWhere($relate_data['parent_field_name'] . '=' . $this->_vars['process_id']);

        if ($return_count) {
            $result = $data_model->findCount();
        } else {
            $result = $data_model->findRow();
        }

        return $result;
    }

    /**
     * getRelateDataByDataId
     */
    private function getRelateDataByDataId($return_count = false)
    {
        if (empty($this->_vars['attributes']['data_id'])) {
            return;
        }

        $relate_data = $this->getRelateModuleTableData();
        if (empty($relate_data)) {
            return;
        }

        $data_model = new \DataModel();
        $data_model->setFrom('{{' . $relate_data['table_name'] . '}}');

        if ($this->isSetRelateTypeModuleOneWithRelateModule() == false) {
            $data_model->andWhere($relate_data['parent_field_name'] . '=' . $this->_vars['process_id']);
        }

        $data_model->andWhere($relate_data['relate_field_name'] . '=' . $this->_vars['attributes']['data_id']);

        if ($return_count) {
            $result = $data_model->findCount();
        } else {
            $result = $data_model->findRow();
        }

        return $result;
    }

    public function getRelatefieldDataTitle($id)
    {
        if (empty($id)) {
            return;
        }

        $relate_value = \EditViewRelateModel::getInstance()
            ->setRelateExtensionCopy(\ExtensionCopyModel::model()->findByPk($this->_relate_copy_id))
            ->setId($id)
            ->getValue();
        $params = [
            'relate_field'          => ['module_title'],
            'relate_module_copy_id' => $this->_relate_copy_id,
        ];

        $title = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, false);

        return $title;
    }

    /**
     * getRelateFieldData
     */
    public function getRelateFieldData($get_title = true)
    {
        $result = [];

        $relate_data = $this->getRelateDataByProcessId();
        if (!empty($relate_data)) {
            $relate_table_data = $this->getRelateModuleTableData();
            $result['card_id'] = $relate_data[$relate_table_data['relate_field_name']];
            $result['copy_id'] = $this->_relate_copy_id;

            if ($get_title) {
                $result['title'] = $this->getRelatefieldDataTitle($result['card_id']);
            }
        }

        return $result;
    }

    public function getStatus()
    {
        return (($this->_be_error) ? false : true);
    }

    /**
     * getResult
     */
    public function getResult()
    {
        $result = [
            'status'  => $this->getStatus(),
            'message' => null,
        ];

        return $result;
    }

    /**
     * getDialogHtml
     */
    public function getDialogHtml()
    {
        $sapi_type = php_sapi_name();
        if ($sapi_type == 'cli') {
            return;
        }

        list($process_controller) = \Yii::app()->createController('Process/ListView');

        $data = [
            'bo_model' => $this,
        ];

        $html = $process_controller->renderPartial('/dialogs/li-binding-object', $data, true);

        return $html;
    }

    public function getModuleDataListContent()
    {
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS);

        $schema = [
            'params' =>
                [
                    'display'                   => '1',
                    'is_primary'                => '0',
                    'edit_view_show'            => '1',
                    'c_load_params_btn_display' => '1',
                    'c_load_params_view'        => true,
                    'c_db_create'               => true,
                    'c_types_list_index'        => '1',
                    'с_remove'                  => true,
                    'name'                      => 'bo',
                    'relate_module_copy_id'     => $this->_relate_copy_id,
                    'relate_module_template'    => false,
                    'relate_data_id'            => null,
                    'relate_index'              => '1',
                    'relate_field'              => 'module_title',
                    'relate_type'               => null,
                    'relate_many_select'        => '0',
                    'pk'                        => false,
                    'type'                      => 'relate',
                    'type_db'                   => 'integer',
                    'type_view'                 => 'edit',
                    'maxLength'                 => null,
                    'minLength'                 => null,
                    'file_types'                => null,
                    'file_types_mimo'           => null,
                    'file_thumbs_size'          => null,
                    'file_max_size'             => null,
                    'file_min_size'             => null,
                    'size'                      => 11,
                    'decimal'                   => null,
                    'required'                  => '0',
                    'default_value'             => (!empty($this->_vars['attributes']['data_id']) ? $this->_vars['attributes']['data_id'] : null),
                    'group_index'               => '3',
                    'filter_enabled'            => true,
                    'input_attr'                => '',
                    'add_zero_value'            => '1',
                    'avatar'                    => true,
                    'rules'                     => '',
                    'list_view_visible'         => '1',
                    'process_view_group'        => '0',
                    'list_view_display'         => '1',
                    'edit_view_display'         => '1',
                    'edit_view_edit'            => '1',
                    'inline_edit'               => '1',
                    'button_actions'            => false,
                ],
        ];

        $alias = 'evm_' . $extension_copy->copy_id . '_1';
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false),
            'params'    => \Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
        ];

        $edit_model = \EditViewModel::modelR($alias, $dinamic_params, true);
        $edit_model->setExtensionCopy($extension_copy);

        $msg = '';
        if ($this->_be_error) {
            $msg = $this->getMessage('relate_object_block');
            if (empty($msg)) {
                $msg = ' ';
            }
        }

        $edit_model->addError('bo', $msg);

        \EditViewRelateModel::setReloaderDefault(\EditViewRelateModel::RELOADER_STATUS_DEFAULT);

        $content = (new \EditViewBuilder())
            ->setExtensionCopy($extension_copy)
            ->setExtensionData($edit_model)
            ->getEditViewElementEdit($schema);

        return $content;
    }

    /**
     * Возвращает сообщение о связанном объекте для History
     *
     * @param null $vars
     * @return null|string|void
     */
    public static function getRelateObjectHistoryMessage($vars = null)
    {
        $result = null;

        if (!empty($vars['process_id'])) {
            $bo_model = self::getInstance()->setVars($vars);
        } elseif (!empty($vars['copy_id']) && !empty($vars['card_id'])) {
            $process_id = \DataModel::getInstance()
                ->setSelect('process_id')
                ->setFrom('{{process_operations}}')
                ->setWhere('copy_id=:copy_id AND card_id=:card_id', [':copy_id' => $vars['copy_id'], ':card_id' => $vars['card_id']])
                ->findScalar();

            if (empty($process_id)) {
                return;
            }
            $bo_model = self::getInstance()->setVars(['process_id' => $process_id]);
        }

        if (empty($bo_model)) {
            return;
        }

        $relate_data = $bo_model->getRelateFieldData();

        if ($relate_data) {
            $result = 'Yii::t("ProcessModule.messages", "Related object"): <a class="navigation_message_notice_ro" href="javascript:void(0)" data-copy_id="' . $relate_data['copy_id'] . '" data-card_id="' . $relate_data['card_id'] . '" >' . $relate_data['title'] . '</a>';
        }

        return $result;
    }

    public function rrr()
    {
        $vars = get_defined_vars();
        unset($vars['params']);
        unset($vars['value_data']);
        $vars['schema']['params'] = $params;
        $vars['extension_data'] = $value_data;

        $ddl_data = \DropDownListModel::getInstance()
            ->setActiveDataType(\DropDownListModel::DATA_TYPE_5)
            ->setVars($vars)
            ->prepareHtml()
            ->getResultHtml();

        if ($ddl_data['status'] == false) {
            return;
        }
    }

}
