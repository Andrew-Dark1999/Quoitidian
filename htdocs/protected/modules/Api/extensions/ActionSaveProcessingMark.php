<?php

/**
 * Сохранение определеной отметки (api_processing_mark) для сущности модуля
 * При импорте данных (moduleImport) в условии можно проверить наличи данного параметра
 * ActionSaveProcessingMark
 *
 * @property ActionSaveProcessingMarkValidator $validator
 * @author Alex R.
 */
class ActionSaveProcessingMark extends AbstractAction
{
    /**
     * ActionSaveProcessingMark constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (isset($data['entity_id']) && !is_array($data['entity_id'])) {
            $data['entity_id'] = [$data['entity_id']];
        }

        parent::__construct($data);
    }

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionSaveProcessingMarkValidator::class;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *  Сохраняем данные
     */
    public function save()
    {
        if (!$this->validator->valdate()) {
            return false;
        }

        $values = [];
        $lich = 1;
        $data = $this->getData();

        foreach ($data['entity_id'] as $entity_id) {
            $values[] = '(' . $data['module_id'] . ', ' . $entity_id . ', ' . (!empty($data['index_name']) ? $data['index_name'] : '') . ')';
            $lich++;
            if ($lich == 1000) {
                $this->insert($values);
                $values = [];
            }
        }

        if (!empty($values)) {
            $this->insert($values);
        }

        return true;
    }

    /**
     * @param $values
     */
    private function insert($values)
    {
        $sql = 'REPLACE INTO {{api_processing_mark}} (copy_id, card_id, index_name) VALUES ' . implode(',', $values);

        DataModel::getInstance()
            ->setText($sql)
            ->execute();
    }
}
