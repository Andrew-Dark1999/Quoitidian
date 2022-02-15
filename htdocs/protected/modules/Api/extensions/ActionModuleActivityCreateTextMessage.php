<?php

/**
 * ActionModuleActivityCreateTextMessage
 *
 * @property ActionModuleActivityCreateTextMessageValidator $validator
 * @author Alex R.
 */
class ActionModuleActivityCreateTextMessage extends AbstractAction
{
    /**
     * @var EditViewModel
     */
    protected $editViewModel;

    /**
     * @var
     */
    private $result;

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionModuleActivityCreateTextMessageValidator::class;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return mixed
     */
    public function getEditViewModel()
    {
        return $this->editViewModel;
    }

    /**
     * @return ExtensionCopyModel|mixed
     */
    protected function createExtensionCopyModel()
    {
        $data = $this->getData();

        return ExtensionCopyModel::model()->findByPk($data['module_id']);
    }

    /**
     *  Запись ошибки
     */
    protected function setDataErrors($errors)
    {
        foreach ($errors as $field_name => $errors) {
            $this->validator->addValidateModule('e', $field_name, $errors);
        }
    }

    /**
     *  Инициализация модели
     */
    private function createEditViewModel()
    {
        $extensionCopy = $this->createExtensionCopyModel();
        $schemaParser = $extensionCopy->getSchemaParse();

        $alias = 'evm_' . $extensionCopy->copy_id;
        $dinamic_params = [
            'tableName' => $extensionCopy->getTableName(null, false),
            'params'    => Fields::getInstance()->setCheckAccess(false)->getActiveRecordsParams($schemaParser),
        ];

        $this->editViewModel = EditViewModel::modelR($alias, $dinamic_params, true);
        $this->editViewModel->scenario = 'update_scalar';

        $this->editViewModel
            ->setElementSchema($schemaParser)
            ->setExtensionCopy($extensionCopy);
    }

    /**
     * Сохраняем данные
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $this->createEditViewModel();

        if (!$this->editViewModel) {
            $this->validator->addValidateGeneral('e', Yii::t('api', 'Data not found'));

            return false;
        }

        $attributes = [
            'copy_id'      => $this->getDataByName('module_id'),
            'data_id'      => $this->getDataByName('entity_id'),
            'message'      => $this->getDataByName('message'),
            'type_comment' => ActivityMessagesModel::TYPE_COMMENT_GENERAL,
        ];

        if($attachment = $this->getDataByName('attachment')) {
            $attributes['attachment'] = $attachment;
        }

        $activity_model = new ActivityMessagesModel();
        $activity_model
            ->setMyAttributes($attributes);

        if ($activity_model->save()) {
            $this->result = $activity_model->getPrimaryKey();

            return true;
        } else {
            if ($activity_model->hasErrors()) {
                $this->setDataErrors($activity_model->getErrors());
            }
        }

        return false;
    }

}
