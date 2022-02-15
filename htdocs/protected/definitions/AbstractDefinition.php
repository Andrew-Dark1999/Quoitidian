<?php

/**
 * Абстрактный класс для справочников
 * Должен содержать список констант, в коде именуемых как id.
 * Так же константа может именить название (title). Список названий прописываента в свойстве self::$collection
 *
 * Class AbstractDefinition
 *
 * @author Aleksandr Roik
 */
abstract class AbstractDefinition
{
    /**
     * Список всех констант
     *
     * @var array
     */
    protected static $collection = [];

    /**
     * Список названий пунктов справочника
     * Предполагается, что свойство будет содержать следующую структуру
     * [
     *      константа(id типа) => название (title),
     *      ....
     * ]
     *
     * @var array
     */
    protected static $titleCollection = [];

    /**
     * Возвращает список всех констант вместе с названиями
     *
     * @return array
     */
    public static function getCollection()
    {
        return static::$collection;
    }

    /**
     * Возвращает список названий
     *
     * @return array
     */
    public static function getTitleCollection()
    {
        return static::$titleCollection;
    }

    /**
     * Возвращает название по Id
     *
     * @param string|int $id
     * @return mixed|null
     */
    public static function getTitleById($id)
    {
        if (!static::hasId($id)) {
            return null;
        }

        $titleCollection = static::getTitleCollection();

        return $titleCollection[$id];
    }

    /**
     * Проверяет наличие id
     *
     * @param string|int $id
     * @return bool
     */
    public static function hasId($id)
    {
        return in_array($id, static::getCollection());
    }
}
