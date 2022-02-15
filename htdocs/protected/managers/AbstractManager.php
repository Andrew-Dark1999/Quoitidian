<?php

/**
 * Базовый класс для реализации менеджера моделей
 * Class AbstractManager
 *
 * @Autor Aleksandr Roik
 */
abstract class AbstractManager
{
    /**
     * Возвращает название класса модели
     *
     * @return string
     */
    abstract public function modelClass();

    /**
     * ModelManager constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Заглушка функции инициализации
     */
    public function initialize()
    {
    }

    /**
     * Магическая проброска на методы модели
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $modelClass = $this->modelClass();
        if (method_exists($modelClass, $name)) {
            return $modelClass::model()->$name(...$arguments);
        } else {
            throw new \BadMethodCallException("There is no method {$name} in class " . static::class);
        }
    }
}
