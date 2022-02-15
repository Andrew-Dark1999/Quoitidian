<?php

/**
 * Class ActionModuleSaveValidator
 *
 * @author Aleksandr Roik
 */
class ActionModuleSaveValidator extends AbstractValidator
{
    /**
     * @return mixed
     */
    protected function getData()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    protected function getAttributes()
    {
        return $this->request['attributes'];;
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

        $data = $this->getData();

        if (!isset($data['module_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'module_id']));
        } elseif (empty($data['module_id'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'module_id']));
        } elseif (!$this->isSetCopyId($data['module_id'], true, PermissionModel::PERMISSION_DATA_RECORD_EDIT)) {
            $this->addValidateGeneral('e', Yii::t('api', 'Module with id "{s}" can not be found, or the module access denied', ['{s}' => $data['module_id']]));
        }

        if (!isset($data['attributes'])) {
            $this->ddValidateGeneral('e', Yii::t('api', 'Not defined parameter "{s}"', ['{s}' => 'attributes']));
        } elseif (empty($data['attributes'])) {
            $this->addValidateGeneral('e', Yii::t('api', 'The parameter "{s}" can not be empty', ['{s}' => 'attributes']));
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }

    /**
     * Проверка полей модели
     *
     * @param EditViewModel $editViewModel
     * @return bool
     */
    public function validateModuleFields($editViewModel)
    {
        $fields = $editViewModel->getAttributes();

        if (!$fields) {
            $this->addValidateGeneral('e', Yii::t('api', 'Fields not found'));

            return false;
        }

        $fields = array_keys($fields);
        $schema = $editViewModel->getExtensionCopy()->getSchema();
        $relateParamsList = SchemaOperation::getInstance()->getElementsRelateParams($schema);

        foreach ($this->getAttributes() as $field_name => $value) {
            $isRelateType = $this->fieldIsRelateType($relateParamsList, $field_name);

            // Проверка существования поля в модуле.
            // Поля связаных типов пропускаем, так как их не должно быть при передаче аттрибутов
            if (!in_array($field_name, $fields) && !$isRelateType) {
                $this->addValidateGeneral('e', Yii::t('api', 'Field "{s}" not found', ['{s}' => $field_name]));
            }

            // проверка значения/ний типа Relate
            if ($isRelateType) {
                $value = (array)$value;
                foreach ($value as $idValue) {
                    if (!is_numeric($idValue)) {
                        $this->addValidateGeneral('e', Yii::t('api', 'The "{s}" field value(s) must be numeric', ['{s}' => $field_name]));
                    }
                }
            }
        }

        return !$this->beMessages(Validate::TM_ERROR);
    }

    /**
     * Проверяем название атрибута, является ли он СМ или СДМ типом
     *
     * @param $relateParamsList
     * @param $fieldName
     * @return bool
     */
    private function fieldIsRelateType($relateParamsList, $fieldName)
    {
        if (!$relateParamsList) {
            return false;
        }

        foreach ($relateParamsList as $params) {
            if ($params['type'] == Fields::MFT_RELATE && $params['name'] == $fieldName) {
                return true;
            }
            if ($params['type'] == Fields::MFT_SUB_MODULE) {
                $extensionCopy = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
                // проверяем по названию алиаса
                if ($extensionCopy && $extensionCopy->alias == $fieldName) {
                    return true;
                }
            }
        }

        return false;
    }
}
