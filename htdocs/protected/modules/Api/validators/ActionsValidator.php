<?php

/**
 * Class ActionsValidator
 *
 * @author Aleksandr Roik
 */
class ActionsValidator extends AbstractValidator
{
    /**
     * validateAction
     */
    private function validateAction()
    {
        if (!isset($this->request['action'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'action']));
        } elseif (empty($this->request['action'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'action']));
        } elseif (!RunActionDefinition::hasAction($this->request['action'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined action'));
        }
    }

    /**
     * validateVars
     */
    private function validateVars()
    {
        if (!isset($this->request['vars'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'vars']));
        } elseif (empty($this->request['vars'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'vars']));
        } elseif (!is_array(json_decode($this->request['vars'], true))) {
            $this->addValidateGeneral('e', Yii::t('api', 'Parameter "{s}" should be an array', ['{s}' => 'vars']));
        }
    }

    /**
     * getVars
     *
     * @return |null
     */
    private function getVars()
    {
        return Vars::getInstance()->getVars();
    }

    /**
     * validateVarLanguage
     */
    private function validateVarLanguage()
    {
        if (!$vars = $this->getVars()) {
            return;
        }

        if (!array_key_exists('language', $vars)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'language']));
        } elseif (!$vars['language']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'language']));
        } elseif (!in_array($vars['language'], ['en', 'ru'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Invalid parameter "{s}"', ['{s}' => 'language']));
        }
    }

    /**
     * validateVarSignature
     */
    private function validateVarSignature()
    {
        if (!$vars = $this->getVars()) {
            return;
        }

        if (!array_key_exists('signature', $vars)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'signature']));
        } elseif (!strpos($vars['signature'], ':')) {
            $this->addValidateGeneral('e', Yii::t('api', 'Invalid parameter "{s}"', ['{s}' => 'signature']));
        } elseif (!Signature::getInstance()->getSignature()) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'signature']));
        } elseif (array_key_exists('data', $vars) && !(new Encryption($vars['data'], Signature::getInstance()->getSignature()))->check()) {
            $this->addValidateGeneral('e', Yii::t('api', 'Signature not confirm'));
        }
    }

    /**
     * validateVarResponseType
     */
    private function validateVarResponseType()
    {
        if (!$vars = $this->getVars()) {
            return;
        }

        if (!array_key_exists('response_type', $vars)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'response_type']));
        } elseif (!$vars['response_type']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'response_type']));
        } elseif (!in_array($vars['response_type'], ResponseTypeDefinition::getTypeCollection())) {
            $this->addValidateGeneral('e', Yii::t('api', 'Invalid parameter "{s}"', ['{s}' => 'response_type']));
        }
    }

    private function validateVarData()
    {
        if (!$vars = $this->getVars()) {
            return;
        }

        if (!array_key_exists('data', $vars)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'data']));
        } elseif (!$vars['data']) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'data']));
        }
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

        if(!ApiUser::getUserId()){
            $this->addValidateGeneral('e', Yii::t('api', 'The user is not defined, it is deactivated or the usage function Api is not active in the profile'));

            return false;
        }

        $this->validateAction();
        $this->validateVars();
        $this->validateVarLanguage();
        $this->validateVarSignature();
        $this->validateVarResponseType();

        return !$this->beMessages(Validate::TM_ERROR);
    }
}
