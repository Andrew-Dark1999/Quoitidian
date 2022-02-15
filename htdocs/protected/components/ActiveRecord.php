<?php

/**
 * Default wrapper for CActiveRecord
 */
class ActiveRecord extends CActiveRecord
{

    public $tableName;

    public function __construct($scenario = 'insert')
    {
        parent::__construct($scenario);

        \TimeZonesModel::setTimeZone();
    }

    public static function model($className = __CLASS__)
    {
        if (Cache::enabled(Cache::CACHE_TYPE_DB)) {
            $db_models = Cache::getParam(Cache::CACHE_TYPE_DB, 'ar_models');
            if (empty($db_models) || !in_array($className, $db_models)) {
                return parent::model($className);
            }

            return parent::model($className)->cache(Cache::getParam(Cache::CACHE_TYPE_DB, 'duration', 60));
        } else {
            return parent::model($className);
        }
    }

    /**
     * @return false|string
     */
    public function tableName()
    {
        return '{{' . $this->tableName . '}}';
    }

    /**
     * @param string $name
     * @return array|mixed|null
     */
    public function getAttribute($name)
    {
        $methodName = 'get' . ucfirst($name) . 'Attribute';

        if (method_exists($this, $methodName) && !$this->functionIsBackTraced(get_class($this), $methodName)) {
            return $this->$methodName();
        } else {
            return parent::getAttribute($name);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($name, $value)
    {
        $methodName = 'set' . ucfirst($name) . 'Attribute';

        if (method_exists($this, $methodName) && !$this->functionIsBackTraced(get_class($this), $methodName)) {
            $this->$methodName($value);

            return true;
        } else {
            return parent::setAttribute($name, $value);
        }
    }

    /**
     *  Возвращает массив ошибок из класса Validate
     */
    public function getErrorsHtml()
    {
        if ($this->hasErrors() == false) {
            return;
        }

        return Validate::getInstance()->addValidateResultFromModel($this->getErrors())->getValidateResultHtml();
    }

    /**
     * взвращает одномерный список ошибок
     *
     * @return array()
     */
    public function getErrorsList()
    {
        if (!$this->hasErrors()) {
            return [];
        }
        $result = [];
        foreach ($this->getErrors() as $error) {
            if (!is_array($error)) {
                $result[] = $error;
            } else {
                $result = array_merge($result, $error);
            }
        }

        return $result;
    }

    /**
     * Возвращает список ошибок в формате json
     *
     * @return array()
     */
    public function getErrorsJson()
    {
        if (!$this->hasErrors()) {
            return;
        }

        return json_encode($this->getErrors(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Возвращает экземпляр обьекта со списком ошибок
     *
     * @return Validate
     */
    public function getErrorsAsValidate()
    {
        return (new Validate())->setValidateResult($this->getErrors());
    }

    /**
     * @param string $name
     * @return array|null
     * @throws CDbException
     */
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name) . 'Attribute';

        if (method_exists($this, $methodName) && !$this->functionIsBackTraced(get_class($this), $methodName)) {
            return $this->$methodName();
        } else {
            return parent::__get($name);
        }
    }

    /**
     * Проверяет трасировку и ищет вызов функции по ее имени.
     * Используется, чтобы исклочить зацикливание при присвоении/чтении значений поля сущности
     *
     * @param $className
     * @param $methodName
     * @return bool
     */
    protected function functionIsBackTraced($className, $methodName)
    {
        $traces = debug_backtrace();
        if (!$traces) {
            return true;
        }

        foreach ($traces as $trace) {
            if ($trace['class'] == $className && $trace['function'] == $methodName) {
                return true;
            }
        }

        return false;
    }
}
