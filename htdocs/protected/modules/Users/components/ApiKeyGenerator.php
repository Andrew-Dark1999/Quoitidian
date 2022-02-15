<?php

/**
 * Генератор ключа для API
 *
 * Class ApiKeyGenerator
 * @author Aleksandr Roik
 */
class ApiKeyGenerator
{
    /**
     * Генерирует ключ для API
     *
     * @param array $user
     */
    public static function generate(array $user)
    {
        $str = $user['users_id'] . $user['email'] . rand(1, 99999) . date('YmdHisu');

        return md5($str);
    }
}
