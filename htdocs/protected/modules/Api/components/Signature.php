<?php

/**
 * Class Signature
 *
 * @author Aleksandr Roik
 */
final class Signature
{
    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var Signature
     */
    private static $instance;

    /**
     * @param null $signature
     * @return Signature
     */
    public static function getInstance($signature = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($signature);
        }

        return self::$instance;
    }

    /**
     * Signature constructor.
     *
     * @param null $signature
     */
    private function __construct($signature = null)
    {
        if ($signature === null) {
            $signature = Vars::getInstance()->getVar('signature');
        }

        $this->init($signature);
    }

    /**
     * Парсит строку signature
     * Значение signature - строка, состоятщая из Имени пользователя и самой подписи (32 знака), разделенные двоеточием.
     * Пример: user@mail.ru:KiosEwxhkUmRVmcrvsD1mjpRLsc9e8V2Mqhldbj45
     *
     * @param string $signature
     */
    private function init($signature)
    {
        if (!$signature) {
            return;
        }

        $explode = explode(':', $signature);
        if (count($explode) != 2) {
            return;
        }

        $this->userName = $explode[0];
        $this->signature = $explode[1];
    }

    /**
     * Возвращает подпись
     *
     * @return null|string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Возвращает пользователя
     *
     * @return null|string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}
