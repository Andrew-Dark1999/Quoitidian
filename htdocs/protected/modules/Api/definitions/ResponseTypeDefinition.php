<?php

/**
 * Типы ответов
 * Class ResponseTypeDefinition
 *
 * @author Aleksandr Roik
 */
class ResponseTypeDefinition
{
    /**
     * @var array
     */
    const TYPE_JSON = 'json';
    const TYPE_XML = 'xml';

    /**
     * @var array
     */
    protected static $typeCollection = [
        self::TYPE_JSON,
        self::TYPE_XML,
    ];

    /**
     * Возвращает список типов
     *
     * @return array
     */
    public static function getTypeCollection()
    {
        return static::$typeCollection;
    }

    /**
     * Возвращает наличие типа
     *
     * @param $type
     * @return bool
     */
    public static function hasType($type)
    {
        return in_array($type, self::getTypeCollection());
    }
}
