<?php

/**
 * Class ApiUser
 *
 * @author Aleksandr Roik
 */
final class ApiUser
{
    /**
     * Возвращает пользователя
     *
     * @return mixed
     */
    public static function getUserName()
    {
        return Signature::getInstance()->getUserName();
    }


    /**
     * Подготовка пользователя и установка глобального объекта
     * prepareUsers
     */
    public static function initWebUser()
    {
        $userName = self::getUserName();

        if (!$userName) {
            return;
        }

        $user_id = \DataModel::getInstance()
            ->setSelect('users_id')
            ->setFrom('{{users}}')
            ->setWhere('email=:email AND active = "1" AND api_active = "1"', [':email' => $userName])
            ->findScalar();

        if (!$user_id) {
            return;
        }

        WebUser::setAppType(WebUser::APP_API);
        WebUser::setAutoSetUserId(false);
        WebUser::setUserId($user_id);
    }

    /**
     * Возвращает id пользователя, что испольщует api
     *
     * @return int
     */
    public static function getUserId()
    {
        return WebUser::getUserId();
    }

}
