<?php

/**
 * Class ActionModuleImportValidator
 *
 * @author Aleksandr Roik
 */
class ActionModuleImportValidator extends AbstractValidator
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

        foreach ($this->getData() as $vars) {
            $this->validateModuleId($vars);
            $this->validateEntityId($vars);
            $this->validateRelateModules($vars);
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }

    /**
     * @param $vars
     * @return $this
     */
    private function validateModuleId($vars)
    {
        if (!array_key_exists('module_id', $vars)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'module_id']));
        } elseif (empty($vars['module_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'module_id']));
        } elseif ($this->isSetCopyId($vars['module_id'], false) == false) {
            $this->addValidateGeneral('e', Yii::t('api', 'Module with id "{s}" can not be found', ['{s}' => $vars['module_id']]));
        }

        return $this;
    }

    /**
     * @param $vars
     * @return $this
     */
    private function validateEntityId($vars)
    {
        if (!empty($vars['entity_id']) && !is_numeric($vars['entity_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The "{s}" field value(s) must be numeric', ['{s}' => 'entity_id']));
        }

        return $this;
    }

    /**
     * @param $vars
     */
    private function validateRelateModules($vars)
    {
        if (is_array($vars) && array_key_exists('relate_modules', $vars)) {
            if (!isset($vars['relate_modules'][0])) {
                $this->validateModuleId($vars['relate_modules']);
            } else {
                foreach ($vars['relate_modules'] as $var) {
                    $this->validateModuleId($var);
                }
            }
        }
    }
}
