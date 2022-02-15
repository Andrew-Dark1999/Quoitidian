<?php

/**
 * Class ActionModuleUploadFileValidator
 *
 * @author Aleksandr Roik
 */
class ActionModuleUploadFileValidator extends AbstractValidator
{
    /**
     * Проверка
     *
     * @return bool
     */
    public function validate()
    {
        if (!$_FILES) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameters'));

            return false;
        }

        if (!array_key_exists('file', $_FILES)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'file']));
        } elseif (empty($_FILES['file'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'file']));
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }
}
