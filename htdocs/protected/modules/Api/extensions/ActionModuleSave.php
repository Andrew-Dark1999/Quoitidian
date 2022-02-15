<?php

/**
 * ApiModuleSave
 *
 * @property ActionModuleSaveValidator $validator
 * @author Alex R.
 */
class ActionModuleSave extends AbstractAction
{
    /**
     * @var EditViewModel
     */
    protected $editViewModel;

    /**
     * @return string
     */
    protected function getValidatorName()
    {
        return ActionModuleSaveValidator::class;
    }

    /**
     * @return mixed
     */
    protected function getAttributes()
    {
        return $this->getData()['attributes'];;
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getAttributebyName($name)
    {
        $attributes = $this->getAttributes();

        if (array_key_exists($name, $attributes)) {
            return $attributes[$name];
        }
    }

    /**
     * @return mixed
     */
    public function getEditViewModel()
    {
        return $this->editViewModel;
    }

    /**
     * Подготовка параметров
     */
    private function prepareBefore()
    {
        if ($this->validator->beMessages(Validate::TM_ERROR)) {
            return $this;
        }

        $this->prepareSdm();
    }

    /**
     * Подготовка данных для установки связи(ей) по типу СДМ
     */
    private function prepareSdm()
    {
        $data = $this->getData();
        $elementRelate = [];

        // 1. Если связанные данные переданы в параметре 'data' => 'element_relate'
        // !!! Устарело
        if (!empty($data['element_relate'])) {
            $elementRelate = $data['element_relate'];
        }

        // 2. Передается id связанной сущности в названии поля
        $extensionCopy = $this->createExtensionCopyModel();
        foreach ($this->getAttributes() as $fieldName => $value) {
            $params = $extensionCopy->getFieldSchemaParams($fieldName);

            if ($params && $params['params']['type'] == Fields::MFT_RELATE) {
                $elementRelate[] = [
                    'name'           => 'EditViewModel[' . $fieldName . ']',
                    'relate_copy_id' => $params['params']['relate_module_copy_id'],
                    'id'             => $value,
                ];
            }
        }

        // Передаем в $_POST чтобы из увидел класс EditViewModel
        if ($elementRelate) {
            $_POST['element_relate'] = $elementRelate;
        }
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
        $this->editViewModel->scenario = 'edit';

        $this->editViewModel
            ->setElementSchema($schemaParser)
            ->setExtensionCopy($extensionCopy);
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
     * Сохраняем данные
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $this->prepareBefore();
        $this->createEditViewModel();

        if (!$this->validator->validateModuleFields($this->editViewModel)) {
            return false;
        }

        $this->editViewModel->setMyAttributes($this->getAttributes());

        if ($this->editViewModel->save()) {
            $this->saveSm();
            $this->editViewModel->actionCreateProcessAfterCreatedEntity();

            return true;
        } else {
            if ($this->editViewModel->hasErrors()) {
                $this->setDataErrors($this->editViewModel->getErrors());
            }
        }

        return false;
    }

    /**
     * Сохраняем данные Сабмодуля
     */
    protected function saveSm()
    {
        // 2. Передается id связанной сущности в названии поля
        $extensionCopy = $this->createExtensionCopyModel();
        $submoduleParams = $extensionCopy->getSubmoduleParamsList();

        // Если нет СМ - входим
        if (!$submoduleParams) {
            return;
        }

        $aliases = array_keys($this->getAttributes());

        foreach ($submoduleParams as $params) {
            // Поиск модуля по названию его алиаса
            $criteria = new CDbCriteria();
            $criteria->addCondition('copy_id=:copy_id');
            $criteria->params = [
                ':copy_id' => $params['params']['relate_module_copy_id'],
            ];
            $criteria->addInCondition('alias', $aliases);

            $smExtensionCopy = ExtensionCopyModel::model()->find($criteria);

            if (!$smExtensionCopy) {
                continue;
            }

            $attributeValues = $this->getAttributebyName($smExtensionCopy->alias);
            if (!$attributeValues) {
                continue;
            }

            $attributeValues = (array)$attributeValues;

            $attributeValues = (new DataModel())
                ->setFrom($smExtensionCopy->getTableName())
                ->setWhere($smExtensionCopy->getPkFieldName() . ' in (' . implode(',', $attributeValues) . ')')
                ->findCol();

            if (!$attributeValues) {
                continue;
            }

            $moduleTablesModel = ModuleTablesModel::getRelateModel($extensionCopy->copy_id, $params['params']['relate_module_copy_id'], ModuleTablesModel::TYPE_RELATE_MODULE_MANY);

            if (!$moduleTablesModel) {
                continue;
            }
            foreach ((array)$attributeValues as $value) {
                DataModel::getInstance()->Insert('{{' . $moduleTablesModel->table_name . '}}',
                    [
                        $moduleTablesModel->parent_field_name => $this->editViewModel->getPrimaryKey(),
                        $moduleTablesModel->relate_field_name => $value,
                    ]);
            }
        }
    }

    /**
     * Проверяем данные валидатором модели
     *
     * @return bool
     */
    public function validateModel()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        $this->prepareBefore();
        $this->createEditViewModel();

        if (!$this->validator->validateModuleFields($this->editViewModel)) {
            return false;
        }

        $this->editViewModel->setMyAttributes($this->getAttributes());

        if ($this->editViewModel->validate()) {
            return true;
        }

        if ($this->editViewModel->hasErrors()) {
            $this->setDataErrors($this->editViewModel->getErrors());
        }

        return false;
    }
}
