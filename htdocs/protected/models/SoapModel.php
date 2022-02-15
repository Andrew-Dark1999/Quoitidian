<?php

/**
 * Class SoapModel - для тестирования.
 */
class SoapModel {



    /**
     * Главный action - собираем и отправляем данные
     */
    public function getSendData(){
        // данные
        $module_data = array(
            'copy_id' => 307,
            'condition' => array(),
            'sub_modules' => array(
                'copy_id' => 308,
                'pk_only' => true,
                'condition' => array(),
            ),
        );

        /*
        $send_data = array(
            'language' => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'module' => $module_data,                   // данные (массив)
            'signature' => self::encode($module_data),  // создаем подпись: хэш(ключ + данные + ключ)
        );
        */

        $send_data = array(
            'language' => 'ru',
            'user_name' => '',
            'module' => $module_data,
            'signature' => self::encode($module_data),
        );

        //$result = $this->sendSOAP($send_data);          // отправляем по SOAP

        return $send_data;
    }







    /**
     * отправляем по SOAP
     */
    private function sendSOAP($vars){
        $soap_client = new SoapClient('http://crm.localhost/api/soap/run');
        $result = $soap_client->importData($vars); //
        echo $result;
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
