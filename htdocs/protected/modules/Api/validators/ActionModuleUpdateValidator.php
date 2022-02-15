<?php

/**
 * Class ActionModuleUpdateValidator
 *
 * @author Aleksandr Roik
 */
class ActionModuleUpdateValidator extends ActionModuleSaveValidator
{
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

        //module_id
        if (!array_key_exists('module_id', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'module_id']));
        } elseif (!$this->request['module_id']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'module_id']));
        } elseif ($this->isSetCopyId($this->request['module_id'], false) == false) {
            $this->addValidateGeneral('e', Yii::t('api', 'Module with id "{s}" can not be found', ['{s}' => $this->request['module_id']]));
        }

        //entity_id
        if (!array_key_exists('entity_id', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'entity_id']));
        } elseif (!$this->request['entity_id']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'entity_id']));
        }

        //attributes
        if (!array_key_exists('attributes', $this->request) || !is_array($this->request['attributes'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'attributes']));
        } elseif (!$this->request['attributes']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'attributes']));
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }
}
