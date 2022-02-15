<?php


/**
 * для тестирования.
 */



class ApiSOAPController extends Controller{

    /**
     * Главный action - собираем и отправляем данные
     */
    public function actionSend(){
        // данные
        $module_data = array(
            // ИД модуля
            'copy_id' => 307,
            // массив значений: название_поля => значение, название_поля => значение ...
            'attributes' => array(
                'module_title' => 'Module name111',
            )
        );

        $send_data = array(
            'language' => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'user_name' => 'mailopen@yandex.ru',
            'module' => $module_data,                   // данные (массив)
            'signature' => self::encode($module_data),  // создаем подпись: хэш(ключ + данные + ключ)
        );

        $result = $this->sendSOAP($send_data);          // отправляем по SOAP

        return $result;
    }



    /**
     * отправляем по SOAP
     */
    private function sendSOAP($vars){
        $soap_client = new SoapClient('http://crm.localhost/api/soap/run');
        $result = $soap_client->moduleValidate($vars); // moduleSave - метод на стороне СРМ
        return $result;
    }



    /**
     * создаем подпись
     */
    public static function encode($data){
        $signature_key = 'KiosEwxhkUmRVmcrvsD1mjpRLsc9e8V2Mqhldbj45';

        // собираем все данные в строку: ...+ключ+"_"+значение+...
        self::$_concat_data = '';
        if(is_array($data)){
            self::concatArray($data);
            $data = self::$_concat_data;
        }

        return (sha1($signature_key .  $data . $signature_key));
    }




    /**
     * собираем все данные в строку
     */
    private static $_concat_data = '';
    private static function concatArray($array){
        foreach($array AS $key => $value){
            if(!is_array($value))
                static::$_concat_data.= $key.'_'.$value;
            else
                static::concatArray($value);
        }
    }




}
