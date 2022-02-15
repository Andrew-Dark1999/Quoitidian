<?php

class WebUser{

    const APP_CONSOLE   = 'app_console';
    const APP_WEB       = 'app_web';
    const APP_API       = 'app_api';

    const ADMINISTRATOR_ID = 1;

    // имя пользователя для установки принудительно
    private static $_user_id;
    // для возвращения авторизированого пользователя. false - принудительный
    private static $_auto_set_user_id = true;


    private static $_app_type;



    public static function setAutoSetUserId($auto_set_user_id = true){
        self::$_auto_set_user_id = $auto_set_user_id;
    }


    public static function setAppType($app_type = null){
        if($app_type !== null){
            self::$_app_type = $app_type;
            return;
        }

        if(self::$_app_type !== null) return;

        $sapi_type = php_sapi_name();
        if($sapi_type == 'cli'){
            self::$_app_type = self::APP_CONSOLE;
        } else {
            self::$_app_type = self::APP_WEB;
        }
    }


    public static function getAppType(){
        return self::$_app_type;
    }




    public static function setUserId($user_id){
        self::$_user_id = $user_id;
    }


    public static function getUserId(){
        self::setAppType();

        switch(self::$_app_type){
            case self::APP_WEB :
            case self::APP_API :
                return self::getActiveUserId();

            case self::APP_CONSOLE :
                return self::getAdministratorUserId();
        }

        return self::getActiveUserId();
    }




    private static function getActiveUserId(){
        if(in_array(self::$_app_type, [self::APP_CONSOLE]) == false){
            if(self::$_auto_set_user_id){
                return \Yii::app()->user->id;
            }
        }

        return self::$_user_id;
    }


    private static function getAdministratorUserId(){
        $user_id = self::getActiveUserId();

        if($user_id){
            return $user_id;
        } else {
            return self::ADMINISTRATOR_ID;
        }

    }







}
