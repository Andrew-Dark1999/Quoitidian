<?php

/**
 * Генерирует/разбирает уникальний uid код сущности модуля
 * Class ModuleEntityUid
 *
 * @author Aleksandr Roik
 */
class ModuleEntityUid
{
    /**
     * Максимальное количество символов для определенного свойства
     *
     * @var array
     */
    private static $propertyValueLength = [
        'copyId' => 4,
        'dataId' => 10,
    ];

    /**
     * Генерирует весь uid
     *
     * @param $copyId
     * @param $dataId
     * @return string
     */
    public static function generate($copyId, $dataId)
    {
        $uid =  '';

        foreach (self::$propertyValueLength as $propertyName => $length) {
            $methodName = 'generate' . ucfirst($propertyName);
            $uid.= self::$methodName(${$propertyName});
        }

        return $uid;
    }

    /**
     * Генерирует часть uid: copyId
     *
     * @param $value
     * @return string
     */
    public static function generateCopyId($value)
    {
        return '9' . self::addZeroBefore($value, self::$propertyValueLength['copyId']);
    }

    /**
     * Генерирует часть uid: dataId
     *
     * @param $value
     * @return string
     */
    public static function generateDataId($value)
    {
        return self::addZeroBefore($value, self::$propertyValueLength['dataId']);
    }

    /**
     * Добавляет нули в начало для достижения максимального количетсва символов
     *
     * @param $value
     * @param $length
     * @return string
     */
    private static function addZeroBefore($value, $length)
    {
        if($value === null){
            $value = '';
        }
        $addLength = $length - strlen((string)$value);
        if($addLength && $addLength > 0){
            $value = str_repeat('0', $addLength) . $value;
        }

        return $value;
    }

}
