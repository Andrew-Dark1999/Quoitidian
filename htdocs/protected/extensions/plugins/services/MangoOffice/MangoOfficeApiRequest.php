<?php
/**
 * Class MangoOfficeApiRequest - Список API запросов (уведомлений) и комманд для sip-сервера.
 */


class MangoOfficeApiRequest{

    // типы запросов
    const TYPE_INTERNAL     = 'internal';   // внутренный исходящий
    const TYPE_EXTERNAL     = 'external';   // внешний, входящий от sip-сервера


    //internal handler
    //API Команды для отсылки на сервер:
    const IH_COMMANDS_CALLBACK          = '/commands/callback';         //Инициирование вызова от имени сотрудника
    const IH_COMMANDS_CALLBACK_GROUP    = '/commands/callback_group';   //Инициирование вызова от имени группы
    const IH_COMMANDS_CALL_HANGUP       = '/commands/call/hangup';      //Завершение вызова
    //Результаты по командах:
    const IH_RESULT_CALLBACK           = '/result/callback';            //Результат. Инициирование вызова от имени сотрудника
    const IH_RESULT_CALLBACK_GROUP     = '/result/callback_group';      //Результат. Инициирование вызова от имени группы
    const IH_RESULT_CALL_HANGUP        = '/result/call_hangup';         //Результат. Завершение вызова



    //external handler
    //API Realtime (запросы (уведомления), что приходят от сервера)
    const EH_EVENTS_CALL        = '/events/call';       //Уведомление о вызове
    const EH_EVENTS_SMS         = '/events/sms';        //Уведомление о результате отправки SMS
    const EH_EVENTS_RECORDING   = '/events/recording';  //Уведомление о записи разговора
    const EH_EVENTS_DTMF        = '/events/dtmf';       //Уведомление о нажатиях DTMF клавиш
    const EH_EVENTS_SUMMARY     = '/events/summary';    //Уведомление о завершении вызова





    private static function getInternalList(){
        return [
            static::IH_COMMANDS_CALLBACK,
            static::IH_COMMANDS_CALLBACK_GROUP,
            static::IH_COMMANDS_CALL_HANGUP,
            static::IH_RESULT_CALLBACK,
            static::IH_RESULT_CALLBACK_GROUP,
            static::IH_RESULT_CALL_HANGUP,
        ];
    }


    private static function getExternalList(){
        return [
            static::EH_EVENTS_CALL,
            static::EH_EVENTS_SMS,
            static::EH_EVENTS_RECORDING,
            static::EH_EVENTS_DTMF,
            static::EH_EVENTS_SUMMARY,
        ];
    }


    public static function getTypeByRequestName($request_name){
        $list = static::getInternalList();
        if(in_array($request_name, $list)){
            return static::TYPE_INTERNAL;
        }

        $list = static::getExternalList();
        if(in_array($request_name, $list)){
            return static::TYPE_EXTERNAL;
        }
    }



    public static function isRequestName($request_name){
        $list = static::getInternalList();
        if(in_array($request_name, $list)){
            return true;
        }

        $list = static::getExternalList();
        if(in_array($request_name, $list)){
            true;
        }

        return false;
    }




}
