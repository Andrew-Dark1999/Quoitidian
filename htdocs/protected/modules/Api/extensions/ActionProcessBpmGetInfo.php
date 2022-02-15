<?php

use Process\models\ProcessModel;
use Process\models\OperationsModel;

/**
 * Процесс. Возвращает информацию о структуре Bpm
 * ActionProcessBpmGetInfo
 *
 * @property ActionProcessBpmGetInfoValidator $validator
 * @author Alex R.
 */
class ActionProcessBpmGetInfo extends AbstractAction
{
    public function __construct($data)
    {
        parent::__construct($data);

        ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
    }

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionProcessBpmGetInfoValidator::class;
    }

    /**
     * Стартует подготовку данных об процессе
     *
     * @return bool
     */
    public function prepare()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        /* @var ProcessModel $process */
        $process = ProcessModel::getInstance($this->getDataByName('id'));
        if (!$process) {
            return;
        }

        $this->result = [
            'properties' => [
                'entity_id'         => $process->process_id,
                'status'            => $this->formatProcessStatus($process),
                'related_module_id' => $process->related_module,
                'related_entity_id' => $this->getRelateEntityIdByProcess($process),

            ],
            'operations' => $this->getOperationsByProcess($process),
        ];

        return !$this->validator->beMessages(Validate::TM_ERROR);
    }

    /**
     * Возвращает данные об операторах
     *
     * @param ProcessModel $process
     */
    private function getOperationsByProcess($process)
    {
        $result = [];

        $operations = $process->getSchemaOperations();
        if (!$operations) {
            return $result;
        }

        foreach ($operations as $operation) {
            $result[] = $this->formatOperation($process, $operation);
        }

        return $result;
    }

    /**
     * Возвращает форматированные данные об операторе
     *
     * @param ProcessModel $process
     * @param $operation
     */
    private function formatOperation($process, $operation)
    {
        $operationModel = $process->getOperationByUniqueIndex($operation['unique_index']);

        $result = [
            'name'         => $operation['name'],
            'title'        => $operation['title'],
            'unique_index' => $operation['unique_index'],
            'status'       => $operationModel->status,
        ];

        if (in_array($operation['name'], [
            OperationsModel::ELEMENT_TASK,
            OperationsModel::ELEMENT_AGREETMENT,
            OperationsModel::ELEMENT_DATA_RECORD,
        ])) {
            $result['module_id'] = $operationModel->copy_id;
            $result['entity_id'] = $operationModel->card_id;
        }

        $result['arrows'] = $this->formatArrows($operation);

        return $result;
    }

    /**
     * @param $process
     * @return string
     */
    protected function formatProcessStatus($process)
    {
        switch ($process->b_status){
            case ProcessModel::B_STATUS_IN_WORK:
                return 'in_work';
            case ProcessModel::B_STATUS_STOPED:
                return 'stoped';
            case ProcessModel::B_STATUS_TERMINATED:
                return 'terminated';
            default:
                return 'undefined';
        }

    }


    /**
     * Созвращает данные о стрелках оператора
     *
     * @param $operation
     */
    private function formatArrows($operation)
    {
        if (empty($operation['arrows'])) {
            return [];
        }

        $result = [];

        foreach ($operation['arrows'] as $arrow) {
            if ($arrow['unique_index']) {
                $result[] = $arrow['unique_index'];
            }
        }

        return $result;
    }

    /**
     * Возвращает id сущности модуля, что связан с процессом как "связанный объект"
     *
     * @param $process
     */
    private function getRelateEntityIdByProcess($process)
    {
        $moduleTables = ModuleTablesModel::getRelateModel($process->related_module, ExtensionCopyModel::MODULE_PROCESS, ModuleTablesModel::TYPE_RELATE_MODULE_MANY);

        if (!$moduleTables) {
            return;
        }

        $relateData = (new DataModel())
            ->setFrom($moduleTables->table_name)
            ->setWhere($moduleTables->relate_field_name . '=' . $process->process_id)
            ->findRow();

        return $relateData ? $relateData[$moduleTables->parent_field_name] : null;
    }
}

