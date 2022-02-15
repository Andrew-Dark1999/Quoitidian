<?php

/**
 * Class ActionModuleUpdate
 *
 * @property ActionModuleUpdateValidator $validator
 * @method ActionModuleUpdateValidator getValidator()
 * @author Aleksandr Roik
 */
class ActionModuleUpdate extends ActionModuleSave
{
    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionModuleUpdateValidator::class;
    }

    /**
     * Создает и возвращает экземпляр EditViewModel
     */
    private function createEditViewModel()
    {
        $data = $this->getData();
        $extensionCopy = $this->createExtensionCopyModel();
        $schemaParser = $extensionCopy->getSchemaParse();

        $alias = 'evm_' . $extensionCopy->copy_id;
        $dinamic_params = [
            'tableName' => $extensionCopy->getTableName(null, false),
            'params'    => Fields::getInstance()->setCheckAccess(false)->getActiveRecordsParams($schemaParser),
        ];

        $this->editViewModel = \EditViewModel::modelR($alias, $dinamic_params)->findByPk($data['entity_id']);

        if (!$this->editViewModel) {
            return false;
        }

        $this->editViewModel->scenario = 'update_scalar';

        $this->editViewModel
            ->setElementSchema($schemaParser)
            ->setExtensionCopy($extensionCopy);

        return true;
    }

    /**
     * Обновляем сущность
     *
     * @return bool
     */
    public function update()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $this->createEditViewModel();

        if (!$this->editViewModel) {
            $this->getValidator()->addValidateGeneral('e', Yii::t('api', 'Data with {s1} = {s2} not found', [
                '{s1}' => 'entity_id',
                '{s2}' => $this->getDataByName('entity_id'),
            ]));

            return false;
        }

        if (!$this->getValidator()->validateModuleFields($this->editViewModel)) {
            return false;
        }

        $this->editViewModel->setMyAttributes($this->getAttributes());

        if ($this->editViewModel->save()) {
            $this->saveSm();
            $this->editViewModel->actionCreateProcessAfterChangedEntity();

            return true;
        } else {
            if ($this->editViewModel->hasErrors()) {
                $this->setDataErrors($this->editViewModel->getErrors());
            }
        }

        return false;
    }
}
