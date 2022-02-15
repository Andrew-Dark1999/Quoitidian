<?php

/**
 * Encryption - проверка подписи
 *
 * @author Alex R.
 */
class Encryption
{
    protected $data;

    protected $signature;

    public function __construct($data, $signature)
    {
        $this->data = $data;
        $this->signature = $signature;
    }

    /**
     * Проверка подлинности присланной подписи с серверной
     *
     * @return bool
     */
    public function check()
    {
        $signature = $this->encodeDataToSignature();

        return $signature && $this->signature && $signature === $this->signature;
    }

    /**
     * Собирает данные, кодирует и возвращает подпись
     *  Формула для вычисления подписи по алгоритму HMAC:
     * md5(K . md5(K . S))
     * K - секретный хєш-ключ (общий для отправителя и получателя)
     * S - все данные из массива data (ключ+значение+...), сгрупированые в одну строку
     *
     * @return string|null
     */
    private function encodeDataToSignature()
    {
        $data = $this->getDataAsString();
        $api_key = $this->getApiKey();

        if (!$api_key) {
            return null;
        }

        $signature = md5($api_key . md5($api_key . $data));

        return $signature;
    }

    /**
     * @return mixed
     */
    private function getApiKey()
    {
        $userModel = UsersModel::model()->find('users_id = ' . WebUser::getUserId() . ' AND active = "1" AND api_active = "1"');

        return $userModel ? $userModel->api_key : null;
    }

    /**
     * Возвращает все данные в виде одной строки
     *
     * @return string
     */
    private function getDataAsString()
    {
        $result = '';

        if (is_array($this->data)) {
            $this->arrayToStr($this->data, $result);
        } else {
            $result = $this->data;
        }

        return $result;
    }

    /**
     * Сводит все данные масива в одну строку: ключ+значение+...
     * !!! Работает рекурсивно по всей вложенности массива
     *
     * @param $data
     * @param string $response_str
     */
    private function arrayToStr($data, &$response_str = '')
    {
        foreach ($data AS $key => $value) {
            if (is_array($value)) {
                $this->arrayToStr($value, $response_str);

            } else {
                $response_str .= $key . (string)$value;
            }
        }
    }

}
