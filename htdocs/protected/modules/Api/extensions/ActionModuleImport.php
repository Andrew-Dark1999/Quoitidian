<?php

/**
 * ActionModuleImport
 *
 * @property ActionModuleImportValidator $validator
 * @author Alex R.
 */
class ActionModuleImport extends AbstractAction
{
    /**
     * @var mixed
     */
    private $result;

    /**
     * ActionModuleImport constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (!isset($data[0])) {
            $data = [$data];
        }

        parent::__construct($data);
    }

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionModuleImportValidator::class;
    }

    /**
     * Возвращает результат выполнения
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     *  Импортируем данные
     */
    public function import()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $this->result = $this->getModuleDataList($this->getData());

        return !$this->validator->beMessages(Validate::TM_ERROR);
    }

    /**
     * @param $module_list
     * @param null $parent_extension_copy
     * @param null $parent_card_id
     * @param bool $this_relate_data
     * @return array
     */
    private function getModuleDataList($module_list, $parent_extension_copy = null, $parent_card_id = null, $this_relate_data = false)
    {
        $data_list = [];

        if (!isset($module_list[0])) {
            $module_list = [$module_list];
        }

        foreach ($module_list as $module) {
            $data = $this->getModuleData($module, $parent_extension_copy, $parent_card_id);
            $data = $this->getModuleRelateData($module, $data);
            if ($this_relate_data && empty($data)) {
                continue;
            }
            $data_list[] = $data;
        }

        return $data_list;
    }

    /**
     * @param $module
     * @param $data
     * @return mixed
     */
    private function getModuleRelateData($module, $data)
    {
        if (empty($module['relate_modules'])) {
            return $data;
        }

        $extension_copy = \ExtensionCopyModel::model()->findByPk($module['module_id']);
        foreach ($data['data'] as &$row) {
            $parent_card_id = $row[$extension_copy->prefix_name . '_id'];
            if (empty($parent_card_id)) {
                $row['_relate_data'] = null;
                continue;
            }

            $row['_relate_data_'] = $this->getModuleDataList($module['relate_modules'], $extension_copy, $parent_card_id, true);
        }

        return $data;
    }

    /**
     * @param $module
     * @param null $parent_extension_copy
     * @param null $parent_card_id
     * @return array|void
     */
    private function getModuleData($module, $parent_extension_copy = null, $parent_card_id = null)
    {
        $extension_copy = \ExtensionCopyModel::model()->findByPk($module['module_id']);
        $extension_copy->refresh();
        $extension_copy->refreshMetaData();
        $extension_copy->getModule();

        $global_params = [
            'pci'             => null,
            'pdi'             => null,
            'finished_object' => null,
        ];

        $before_condition = null;
        $before_params = [];
        if ($parent_extension_copy !== null && $parent_card_id !== null) {
            $id_list = $this->getIdList($module['module_id'], $parent_extension_copy->copy_id, $parent_card_id);
            if (!empty($id_list)) {
                $before_condition = $extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id in (' . implode(',', $id_list) . ')';
            } else {
                return;
            }
        }

        try {
            $data = \DataListModel::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setFinishedObject(false)
                ->setThisTemplate(\EditViewModel::THIS_TEMPLATE_MODULE)
                ->setGlobalParams($global_params)
                ->setDataIfParticipant(false)
                ->setDefinedPK(!empty($module['entity_id']) ? (array)$module['entity_id'] : null)
                ->setBeforeCondition($before_condition, $before_params)
                ->setLastCondition((isset($module['condition']) && !empty($module['condition']) ? $module['condition'] : null))
                ->setUseProcessMark((isset($module['use_process_mark']) && !empty($module['use_process_mark']) ? (boolean)$module['use_process_mark'] : false))
                ->prepare(\DataListModel::TYPE_LIST_VIEW)
                ->getData();
        } catch (Exception $e) {
            $this->validator->addValidateGeneral('e', Yii::t('api', 'Invalid query parameters are defined'));

            return;
        }

        $data = DataValueModel::getInstance()
            ->setSchemaFields($this->getPreparedSchemaFields($extension_copy))
            ->setExtensionCopy($extension_copy)
            ->setAddAvatar(false)
            ->setOnlyRelateId(true)
            ->setReturnOnlyValue(true)
            ->prepareData($data)
            ->getProcessedData()// без обьеденения значений
            ->getData();

        $data = [
            'module_id' => $module['module_id'],
            'data'    => $data
        ];

        return $data;
    }

    /**
     * @param $relate_copy_id
     * @param $copy_id
     * @param $card_id
     */
    private function getIdList($relate_copy_id, $copy_id, $card_id)
    {
        $relate_table = ModuleTablesModel::model()->find([
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one","relate_module_many")',
            'params'    => [
                ':copy_id'        => $copy_id,
                ':relate_copy_id' => $relate_copy_id,
            ]
        ]);

        if (empty($relate_table)) {
            return;
        }

        $data = \DataModel::getInstance()
            ->setSelect($relate_table['relate_field_name'])
            ->setFrom('{{' . $relate_table['table_name'] . '}}')
            ->setWhere($relate_table['parent_field_name'] . ' = ' . $card_id)
            ->findCol();

        if (!empty($data)) {
            return $data;
        }
    }

    /**
     * @param $extension_copy
     * @return $this|array
     */
    public function getPreparedSchemaFields($extension_copy)
    {
        $result = [];
        $extension_copy->setAddId();
        $schema = $extension_copy->getSchemaParse([],[],[],false);

        if (empty($schema) || !isset($schema['elements'])) {
            return $this;
        }
        foreach ($schema['elements'] as $element) {
            if (isset($element['field'])) {
                if ($element['field']['params']['type'] == 'activity') {
                    continue;
                }
                $result[] = $element['field'];
            }
        }

        return $result;
    }
}
