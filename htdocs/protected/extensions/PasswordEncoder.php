<?php
class PasswordEncoder
{
    const KEYS_FILE_NAME='config/keys';


    public static function getEncryptMethod(){
        $method = 'AES-256-CTR';

        return $method;
    }


    public static function encode($input_text){
        if(file_exists(Yii::app()->basePath.'/'.self::KEYS_FILE_NAME)) {
            $key_value_decode = self::getKeyValue();
            $iv_decode = self::getIvKeyValue();
            $encoded_password = bin2hex(openssl_encrypt($input_text, self::getEncryptMethod(), $key_value_decode, true, $iv_decode));
            return $encoded_password;
        }
        else{
            return $input_text;
        }
    }

    public static function decode($encoded_hex){
        if(file_exists(Yii::app()->basePath.'/'.self::KEYS_FILE_NAME)) {
            $key_value_decode = self::getKeyValue();
            $iv_decode = self::getIvKeyValue();
            $decoded_password = openssl_decrypt(hex2bin($encoded_hex), PasswordEncoder::getEncryptMethod(), $key_value_decode, true, $iv_decode);
            return $decoded_password;
        }
        else{
            return $encoded_hex;
        }
    }

    private static function getKeyValue(){
        $key_hex=json_decode(file_get_contents(Yii::app()->basePath.'/'.self::KEYS_FILE_NAME),true)['key'];
        $result=hex2bin($key_hex);
        return $result;
    }

    private static function getIvKeyValue(){
        $iv_hex=json_decode(file_get_contents( Yii::app()->basePath.'/'.self::KEYS_FILE_NAME),true)['iv_key'];
        $result=hex2bin($iv_hex);
        return $result;
    }

}
