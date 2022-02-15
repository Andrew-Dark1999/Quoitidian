<?php

class ForAPIHelp
{
    /**
     * Uri для за запросов API
     *
     * @var string
     */
    private $apiUri = 'https://<you_domain>/api/html/run';

    /**
     * Пользователь, от имени которого будет использоваться API
     *
     * @var string
     */
    private $userEmail = '<you_user_email>';

    /**
     * Ключ пользователя для API
     *
     * @var string
     */
    private $apiKey = 'you_api_key';

    /**
     * Сохранение новой сущности модуля
     *
     * @return StdObject
     */
    public function actionModuleSave()
    {
        $data = [
            'copy_id'    => 7,
            'attributes' => [
                'module_title' => 'Запись 1',
            ]
        ];

        return $this->sendHtml(
            $this->formatQueryData('module.save', $data)
        );
    }

    /**
     * Обновление сущности модуля
     *
     * @return StdObject
     */
    public function actionModuleUpdate()
    {
        $data = [
            'copy_id'    => 1001,
            'card_id'    => 8,
            'attributes' => [
                'file_general' => 441,
            ],
        ];

        return $this->sendHtml(
            $this->formatQueryData('module.update', $data)
        );
    }

    /**
     * Загрузка данных сущности(ях) модуля.
     *
     * @return StdObject
     */
    public function actionModuleImport()
    {
        $data = [
            'module_id'      => 1,
            'entity_id'      => 1,
            'relate_modules' => [
                [
                    'module_id' => 1000,
                ],
                [
                    'module_id' => 1001,
                    'entity_id' => 4,
                ],
                [
                    'module_id' => 1002,
                ]
            ]
        ];

        return $this->sendHtml(
            $this->formatQueryData('module.import', $data)
        );
    }

    /**
     * Загрузка файла
     *
     * @return StdObject
     */
    public function actionActivityUploadFile()
    {
        $fileName = '<путь_к_файлу>';

        return $this->sendHtml(
            $this->formatQueryData('module.uploadFile', '', $fileName)
        );
    }

    /**
     * Создание нового сообщения текстового типа в блоке Активность
     *
     * @return StdObject
     */
    public function actionCreateTextMessage()
    {
        $data = [
            'module_id'  => 7,
            'entity_id'  => 1,
            'message'    => 'Текст сообщения',
            'attachment' => [100], //ID, что возвращает метод self::actionActivityUploadFile()
        ];

        return $this->sendHtml(
            $this->formatQueryData('module.activity.createTextMessage', $data)
        );
    }

    /**
     * Возвращает информацию о bpm-процесе по его id
     *
     * @return StdObject
     */
    public function actionProcessBpmGetInfo()
    {
        $data = [
            'id' => 1,
        ];

        return $this->sendHtml(
            $this->formatQueryData('process.bpm.getInfo', $data)
        );
    }




    /**********************************************************************************
     *              API ЗАПРОСЫ НА СЕРВЕР
     ***********************************************************************************/

    /**
     * Отправляем запрос
     *
     * @return StdObject
     */
    private function sendHtml($queryData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $this->apiUri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryData);

        $response = curl_exec($ch);

        return json_decode($response);
    }

    /**
     * Форматирует параметры для rest запроса
     *
     * @param string $action Название действия
     * @param array $data Данные для метода действия
     * @param null $filePath Путь к файлу
     * @return array
     */
    private function formatQueryData($action, $data, $filePath = null)
    {
        $vars = [
            'language'      => 'ru', // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'signature'     => $this->encodeDataToSignature($data),
        ];

        if ($data) {
            $vars['data'] = $data;
        }

        $queryData = [
            'action' => $action,
            'vars'   => json_encode($vars),
        ];

        if ($filePath) {
            $queryData['file'] = curl_file_create($filePath, mime_content_type($filePath), basename($filePath));
        }

        return $queryData;
    }

    /**
     * Возвращает подпись (signature)
     *
     * @param $data
     * @return string
     */
    private function encodeDataToSignature($data)
    {

        return $this->userEmail . ':' . md5($this->apiKey . md5($this->apiKey . $this->getDataAsString($data)));
    }

    /**
     * Переводит данные массива в строку и возвращает
     *
     * @param $data
     * @return string
     */
    private function getDataAsString($data)
    {
        $str = '';
        $this->arrayToStr($data, $str);

        return $str;
    }

    /**
     * Рекурсивно переводит данные массива в строку
     *
     * @param $data
     * @param $response_str
     * @return void
     */
    private function arrayToStr($data, &$response_str)
    {
        foreach ((array)$data AS $key => $value) {
            if (is_array($value)) {
                $this->arrayToStr($value, $response_str);
            } else {
                $response_str .= $key . (string)$value;
            }
        }
    }
}
