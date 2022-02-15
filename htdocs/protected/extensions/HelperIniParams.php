<?php

/**
 * Хелпер для параметров настройки конфигурации php
 * Class HelperIniParams
 *
 * @author Aleksandr Roik
 */
class HelperIniParams
{
    const UNIT_BYTE = 'byte';
    const UNIT_KILOBYTE = 'kilobyte';
    const UNIT_MEGABYTE = 'megabyte';
    const UNIT_GIGABYTE = 'gigabyte';

    /**
     * Возвращает значение
     *
     * @param $key
     * @return string
     */
    public function getIniParam($key)
    {
        return ini_get($key);
    }

    /**
     * Возвращает значение в байтах согласно модификатору, что в конце строки
     *
     * @param $val
     * @return int
     */
    private static function iniStrToByte($val)
    {
        $val = trim($val);
        $unit = strtolower($val[strlen($val) - 1]);

        switch ($unit) {
            case 'g':
                return (int)$val * 1024 * 1024 * 1024;
            case 'm':
                return (int)$val * 1024 * 1024;
            case 'k':
                return (int)$val * 1024;
        }

        return 0;
    }

    /**
     * @param $size
     * @param $utin
     */
    public static function biteToUnit($size, $unit)
    {
        if (!$size) {
            return 0;
        }

        switch ($unit) {
            case self::UNIT_GIGABYTE:
                return $size / 1024 / 1024 / 1024;
            case self::UNIT_MEGABYTE:
                return $size / 1024 / 1024;
            case self::UNIT_KILOBYTE:
                return $size / 1024;
            case self::UNIT_BYTE:
                return $size;
        }

        return 0;
    }

    /**
     * Возвращает максимальный размер post запросса
     */
    public static function getPostMaxSize($unit = self::UNIT_BYTE, $toInteger = true)
    {
        $size = self::iniStrToByte(ini_get("post_max_size"));
        $size = self::biteToUnit($size, $unit);

        return $toInteger ? (int)$size : $size;
    }

    /**
     * Возвращает максимальный размер для загрузки файла
     */
    public static function getUploadMaxFileSize($unit = self::UNIT_BYTE, $toInteger = true)
    {
        $size = self::iniStrToByte(ini_get("upload_max_filesize"));
        $size = self::biteToUnit($size, $unit);

        return $toInteger ? (int)$size : $size;
    }

    /**
     * Возвращает максимальный размер для загрузки файла post запроссом
     *
     * @param bool $toBytes
     * @return int
     */
    public static function getPostUploadMaxFileSize($unit = self::UNIT_BYTE, $toInteger = true)
    {
        $postSize = self::getPostMaxSize($unit);
        $uploadSize = self::getUploadMaxFileSize($unit);
        $size = $uploadSize > $postSize ? $postSize : $uploadSize;

        return $toInteger ? (int)$size : $size;
    }
}
