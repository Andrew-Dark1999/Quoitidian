<?php

/**
 * Сущность "черновик".
 * Используется для сохранения временных данных полей модулей
 * Class DraftModel
 *
 * @property string $uid;
 * @property string $data_type;
 * @property string $data;
 * @author Aleksandr Roik
 */
class DraftModel extends ActiveRecord
{
    /**
     * @var string
     */
    public $tableName = 'draft';

    /**
     * @param string $className
     * @return CActiveRecord|mixed
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['uid, data_type', 'required'],
            ['uid', 'unique', 'className' => 'DraftModel', 'attributeName' => 'uid', 'caseSensitive' => true],
            ['data_type', 'in', 'range' => DraftDataTypeDefinition::getCollection()],
            ['data', 'validateData'],
        ];
    }

    /**
     * @param $attibute
     * @param $value
     * @return bool
     */
    public function validateData($attibute)
    {
        $value = $this->data;

        if ($this->data_type == DraftDataTypeDefinition::JSON && !is_array($value)) {
            $this->addError($attibute, Yii::t('messages', 'Invalid value type'));

            return false;
        }

        if ($this->data_type == DraftDataTypeDefinition::TEXT && (!is_string($value) || !is_numeric($value))) {
            $this->addError($attibute, Yii::t('messages', 'Invalid value type'));

            return false;
        }

        if (is_string($value) && strlen($value) > 65536) {
            $this->addError($attibute, Yii::t('messages', 'The value must not exceed {s} characters', ['{s}' => 65536]));
        }
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        if ($this->data_type == DraftDataTypeDefinition::JSON && is_array($this->data)) {
            $this->data = json_encode($this->data);
        }

        return parent::beforeSave();
    }

    /**
     * @return string
     */
    public function getDataAttribute()
    {
        $value = $this->getAttribute('data');

        switch ($this->data_type) {
            case DraftDataTypeDefinition::TEXT:
                return $value;
            case DraftDataTypeDefinition::JSON:
                return (is_string($value)) ? json_decode($value, true) : $value;
        }

        return $value;
    }

    /**
     * @param $data
     * @return $this
     */
    /*
    public function setDataAttribute($data)
    {
        switch ($this->data_type){
            case DraftDataTypeDefinition::TEXT:
                $this->setAttribute('data', $data, true);
                break;
            case DraftDataTypeDefinition::JSON:
                if(is_array($this->data)) {
                    $this->setAttribute('data', json_encode($data), true);
                }
                break;
            default:
                $this->setAttribute('data', $data, true);
        }

        return $this;
    }
    */
}
