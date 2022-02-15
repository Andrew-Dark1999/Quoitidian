<?php

/**
 * Class ActionProcessBpmGetInfoValidator
 *
 * @author Aleksandr Roik
 */
class ActionProcessBpmGetInfoValidator extends AbstractValidator
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

        if (!array_key_exists('id', $this->request)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'id']));
        } elseif (empty($this->request['id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'id']));
        } elseif(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, \ExtensionCopyModel::MODULE_PROCESS, Access::ACCESS_TYPE_MODULE)) {
            $this->addValidateGeneral('e', Yii::t('messages', 'You do not have access to this object'));
        } elseif(!\Process\models\ProcessModel::getInstance($this->action->getDataByName('id'))){
            $this->addValidateGeneral('e', Yii::t('api', 'Process #{s} not found', ['{s}' => $this->action->getDataByName('id')]));
        } elseif(!ParticipantModel::model()->checkUserSubscription(\ExtensionCopyModel::MODULE_PROCESS, $this->action->getDataByName('id'))){
            $this->addValidateGeneral('e', Yii::t('messages', 'You do not have access to this object'));
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }
}
