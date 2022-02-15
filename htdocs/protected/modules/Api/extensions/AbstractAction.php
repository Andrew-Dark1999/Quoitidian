<?php

/**
 * Class AbstractAction
 *
 * @author Aleksandr Roik
 */
abstract class AbstractAction
{
    /**
     * Данные, что передаются в api запросе
     *
     * @var array
     */
    protected $data;

    /**
     * @var Validate $validator
     */
    protected $validator;

    /**
     * ApiActions constructor.
     *
     * @param $request
     */
    public function __construct($data)
    {
        $this->prepareData($data);
        $this->data = $data;

        $validatorClassName = $this->getValidatorName();
        $this->validator = new $validatorClassName($this, $data);
    }

    /**
     * Должен возвратить название класса валидатора
     *
     * @return string
     */
    abstract protected function getValidatorName();

    /**
     * @return Validate
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Подготовка данных
     *
     * @param $data
     */
    protected function prepareData(&$data)
    {
        // Костыли для совместимости с версией API 1.0
        // Рекурсивно меняем copy_id на module_id и card_id на entity_id
        if(is_array($data)){
            foreach ($data as $key => &$value) {
                if(is_array($value)){
                    $this->prepareData($value);
                } else {
                    if($key == 'copy_id'){
                        $data['module_id'] = $value;
                        unset($data['copy_id']);
                    } elseif($key == 'card_id'){
                        $data['entity_id'] = $value;
                        unset($data['card_id']);

                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getDataByName($name)
    {
        $data = $this->getData();

        if (array_key_exists($name, $data)) {
            return $data[$name];
        }
    }
}
