<?php

/**
 * Class ActionModuleActivityCreateTextMessageValidator
 *
 * @author Aleksandr Roik
 */
class ActionModuleActivityCreateTextMessageValidator extends AbstractValidator
{
    /**
     * @return array
     */
    protected function getData()
    {
        return $this->request;
    }

    /**
     * Проверка
     *
     * @return bool
     */
    public function validate()
    {
        if (!$this->request || !is_array($this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameters'));

            return false;
        }

        if (!array_key_exists('module_id', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'module_id']));
        } elseif (empty($this->request['module_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'module_id']));
        } elseif ($this->isSetCopyId($this->request['module_id'], false) == false) {
            $this->addValidateGeneral('e', Yii::t('api', 'Module with id "{s}" can not be found', ['{s}' => $this->request['module_id']]));
        }

        if (!array_key_exists('entity_id', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'entity_id']));
        } elseif (empty($this->request['entity_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'entity_id']));
        }

        if (!array_key_exists('message', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'message']));
        } elseif (empty($this->request['message'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'message']));
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }
}
