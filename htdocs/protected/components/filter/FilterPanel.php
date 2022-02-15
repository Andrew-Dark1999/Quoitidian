<?php

namespace application\components\filter;

/**
 * Обрабатывает данные для отобрадения панели фильтра для ListView i ProcessView
 * Class FilterPanel
 *
 * @package application\components\filter
 * @author Aleksandr Roik
 */
class FilterPanel
{
    /**
     * @var \ExtensionCopyModel
     */
    private $extensionCopy;

    /**
     * @var string
     */
    private $fieldValue;

    /**
     * @var string
     */
    private $destination = 'listView';

    /**
     * @var string
     */
    private $conditionValue;

    /**
     * @var string
     */
    private $conditionValueValue;

    /**
     * @var numeric
     */
    private $thisTemplate;

    /**
     * @var array
     */
    private $fieldsSchemaCache;

    /**
     * FilterPanel constructor.
     *
     * @param arrya $config
     */
    public function __construct($config)
    {
        foreach ($config as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        $this->validateAndCorectFieldValue();
    }

    /**
     * Сбрасываем значение названия поля, если оно уже отсутсвует в схеме.
     * При таком раскладе в фильтре подставится первое значение названия поля
     */
    private function validateAndCorectFieldValue()
    {
        if ($this->fieldValue === null || $this->fieldValue === '') {
            return;
        }

        $fieldName = $this->getFieldNameByAlias($this->fieldValue, 'block_participant');
        $fieldsSchema = $this->getFieldsSchema();

        if (empty($fieldsSchema['params']) || !array_key_exists($fieldName, $fieldsSchema['params'])) {
            $this->fieldValue = null;
        }
    }

    /**
     * Возвращает схему полей модуля
     *
     * @return array
     */
    private function getFieldsSchema()
    {
        if ($this->fieldsSchemaCache !== null) {
            return $this->fieldsSchemaCache;
        }

        $this->fieldsSchemaCache = \SchemaConcatFields::getInstance()
            ->setSchema($this->getSchemaElements())
            ->setWithoutFieldsForFilterGroup($this->extensionCopy->getModule(false)->getModuleName())
            ->parsing()
            ->prepareWithOutDeniedRelateCopyId()
            ->primaryOnFirstPlace()
            ->prepareWithConcatName()
            ->getResult();

        return $this->fieldsSchemaCache;
    }

    /**
     * @return array
     */
    private function getSchemaElements()
    {
        $schema = $this->extensionCopy->getSchemaParse();

        return !empty($schema['elements']) ? $schema['elements'] : [];
    }

    /**
     * Проверяет по названию поля, вибрано ли оно
     *
     * @param $fieldName
     * @return bool
     */
    private function isFieldSelectedByNameAlias($fieldNameAlias)
    {
        if (!$this->fieldValue) {
            return false;
        }

        return $fieldNameAlias == $this->fieldValue;
    }

    /**
     * Созвращает название первого поля из строкового массива
     *
     * @param $fieldStrList
     */
    private function getFieldNameByFieldList($fieldStrList)
    {
        $fieldName = explode(',', $fieldStrList);

        return $fieldName[0];
    }

    /**
     * Созвращает название первого поля из строкового массива, что содержится в схеме
     *
     * @param $fieldSchema
     */
    private function getFieldNameByHeaderSchema($fieldHeaderSchema)
    {
        $fieldName = explode(',', $fieldHeaderSchema['name']);

        return $fieldName[0];
    }

    /**
     * @param $fieldName
     * @return mixed
     */
    private function getFieldSchemaByName($fieldHeaderSchema, $fieldName)
    {
        $fieldsSchema = $this->getFieldsSchema();

        if (!array_key_exists($fieldName, $fieldsSchema['params'])) {
            return;
        }

        $fieldSchema = $fieldsSchema['params'][$fieldName];

        return [
                'title' => \ListViewBulder::getFieldTitle(['title' => $fieldHeaderSchema['title']] + $fieldSchema),
            ] + $fieldSchema;
    }

    /**
     * @param $fieldSchema
     */
    private function getFieldNameAliasBySchema($fieldSchema, $prefix = null)
    {
        return $fieldSchema['name'] . ($prefix ? ('_' . $prefix) : '');
    }

    /**
     * @param $fieldAlias
     * @param null $prefix
     * @return string|string[]
     */
    private function getFieldNameByAlias($fieldAlias, $prefix = null)
    {
        if ($prefix === null) {
            return $fieldAlias;
        }

        return str_replace('_' . $prefix, '', $fieldAlias);
    }

    /**
     * @param $fieldSchema
     * @return string
     */
    private function getFieldTitleBySchema($fieldSchema)
    {
        if ($this->extensionCopy->copy_id == \ExtensionCopyModel::MODULE_USERS) {
            return \Yii::t('UsersModule.base', $fieldSchema['title']);
        } elseif ($this->extensionCopy->copy_id == \ExtensionCopyModel::MODULE_ROLES) {
            return \Yii::t('RolesModule.base', $fieldSchema['title']);
        }

        return \ListViewBulder::getFieldTitle($fieldSchema);
    }

    /**
     * @return array
     */
    public function getModuleFields()
    {
        $fieldsSchema = $this->getFieldsSchema();

        if (empty($fieldsSchema['header'])) {
            return [];
        }

        $result = [];
        foreach ($fieldsSchema['header'] as $fieldHeaderSchema) {
            $fieldName = $this->getFieldNameByHeaderSchema($fieldHeaderSchema);
            $fieldSchema = $this->getFieldSchemaByName($fieldHeaderSchema, $fieldName);

            if (!$fieldSchema['filter_enabled']) {
                continue;
            }

            $this->yieldModuleFieldBySchema($fieldSchema, $result);
        }

        return $result;
    }

    /**
     * @param $fieldSchema
     * @param $result
     */
    private function yieldModuleFieldBySchema($fieldSchema, &$result)
    {
        $fiedNameAlias = $this->getFieldNameAliasBySchema($fieldSchema);
        $result[] = [
            'value'    => $fiedNameAlias,
            'title'    => $this->getFieldTitleBySchema($fieldSchema),
            'selected' => $this->isFieldSelectedByNameAlias($fiedNameAlias),
        ];

        if ($fieldSchema['type'] == \Fields::MFT_RELATE_PARTICIPANT && $fieldSchema['type_view'] == \Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
            $fiedNameAlias = $this->getFieldNameAliasBySchema($fieldSchema, 'block_participant');

            $result[] = [
                'value'    => $fiedNameAlias,
                'title'    => \Yii::t('base', 'Participants'),
                'selected' => $this->isFieldSelectedByNameAlias($fiedNameAlias),
            ];

        }
    }

    /**
     * @return array
     */
    private function getSchemaOfTheActiveField()
    {
        $fieldsSchema = $this->getFieldsSchema();
        if (empty($fieldsSchema['header'])) {
            return [];
        }

        if ($this->fieldValue) {
            $fieldName = $this->getFieldNameByAlias($this->fieldValue, 'block_participant');
            if (array_key_exists($fieldName, $fieldsSchema['params'])) {
                return $fieldsSchema['params'][$fieldName];
            }
        }

        $fieldName = $this->getFieldNameByFieldList($fieldsSchema['header'][0]['name']); // берем схему первого элемента в списке

        return $this->getFieldSchemaByName($fieldsSchema['header'][0], $fieldName);
    }

    /**
     * @return string|null
     */
    public function getConditionView()
    {
        $fieldSchema = $this->getSchemaOfTheActiveField();
        if (!$fieldSchema) {
            return;
        }

        return \Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterCondition.FilterCondition'),
            [
                'field_schema'    => ['params' => $fieldSchema],
                'condition_value' => $this->conditionValue,
                'destination'     => $this->destination,
            ],
            true);
    }

    /**
     * @return string|null
     */
    public function getConditionValueView()
    {
        $fieldSchema = $this->getSchemaOfTheActiveField();
        if (!$fieldSchema) {
            return;
        }

        return \Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
            [
                'extension_copy'        => $this->extensionCopy,
                'schema'                => ['params' => $fieldSchema],
                'condition_value'       => $this->conditionValue,
                'condition_value_value' => $this->conditionValueValue,
                'this_template'         => $this->thisTemplate,
            ],
            true);
    }
}
