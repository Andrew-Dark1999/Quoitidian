<?php

/**
 * Для тестирования
 */
class ApiHtmlController extends Controller
{
    private $apiKey = '6c7d608bf416082d8435e59c5fe063a3';

    private $userEmail = 'roichik.ov@gmail.com';

    /**
     * @return bool|string
     */
    public function actionProcessBpmGetInfo()
    {
        // данные
        $data = [
            'id' => 1621,
        ];
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'data'          => $data,
            'signature'     => $this->encodeDataToSignature($data),
        ];

        $result = $this->sendHTML('process.bpm.getInfo', $vars);

        print_r(json_decode($result, true));

        return $result;
    }

    /**
     * @return bool|string
     */
    public function actionCreateTextMessage()
    {
        // данные
        $data = [
            // ИД модуля
            'copy_id' => 7,
            'card_id' => 305,
            'message' => 'message1',
            'attachment' => [139, 140],
        ];
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'data'          => $data,                   // данные (массив)
            'signature'     => $this->encodeDataToSignature($data),
        ];
        $result = $this->sendHTML('module.activity.createTextMessage', $vars);          // отправляем по REST

        print_r($result);

        return $result;
    }

    /**
     * @return bool|string
     */
    public function actionActivityUploadFile()
    {
        $fileName = '/home/alex_r/Изображения/Lenix b4fm.doc';
        //$fileName = '/home/alex_r/Изображения/mazdaLogo.png';

        // данные
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'signature'     => $this->encodeDataToSignature(''),
        ];

        $result = $this->sendHTML('module.activity.uploadFile', $vars, $fileName);
        //$result = $this->sendHTML('module.uploadFile', $vars, $fileName);
        //$result = $this->sendHTML('module.uploadFileImage', $vars, $fileName);

        print_r($result);

        return $result;
    }

    /**
     * @return bool|string
     */
    public function actionModuleSave()
    {
        // данные
        $data = [
            // ИД модуля
            'copy_id'    => 7,
            'attributes' => [
                'module_title' => 'Запись 1',
            ]
        ];
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'data'          => $data,                   // данные (массив)
            'signature'     => $this->encodeDataToSignature($data),
        ];

        $result = $this->sendHTML('moduleSave', $vars);          // отправляем по REST

        print_r(json_decode($result, true));

        return $result;
    }

    /**
     * @return bool|string
     */
    public function actionModuleUpdate()
    {
        // данные
        $data = [
            // ИД модуля
            'copy_id'    => 1001,
            'card_id'    => 8,
            'attributes' => [
                'file_general' => 441,
            ],
        ];
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'data'          => $data,                   // данные (массив)
            'signature'     => $this->encodeDataToSignature($data),
        ];

        $result = $this->sendHTML('moduleUpdate', $vars);          // отправляем по REST

        print_r(json_decode($result, true));

        return $result;
    }

    /**
     * @return bool|string
     */
    public function actionModuleImport()
    {
        // данные
        $data = [
            // ИД модуля
            'copy_id' => 1001,
            'card_id' => 1,
            //'entity_id' => 1,
            //'condition' => 'zadachi_id1 = 6444',

            'relate_modules' => [
                [
                    'module_id' => 1000,
                ],
                [
                    'copy_id' => 1005,
                    'card_id' => 4,
                ],
                [
                    'module_id' => 9,
                ]
            ]

        ];
        $vars = [
            'language'      => 'ru',                         // язык сообщений, что будет возвращено в случае ошибки
            'response_type' => 'json',
            'data'          => $data,
            'signature'     => $this->encodeDataToSignature($data),
        ];

        $result = $this->sendHTML('moduleImport', $vars);          // отправляем по REST

        print_r(json_decode($result, true));

        return $result;
    }

    /**
     * отправляем по REST
     */
    private function sendHTML($action, $vars, $fileName = null)
    {
        $post_data = [
            'action' => $action,
            'vars'   => json_encode($vars),

        ];
        if ($fileName) {
            $post_data['file'] = curl_file_create($fileName, mime_content_type($fileName), basename($fileName));
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, 'http://crm.localhost/api/html/run?XDEBUG_SESSION_START=PHPSTORM');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);

        return $result;
    }

    /**
     * @param $data
     * @return string
     */
    function encodeDataToSignature($data)
    {

        return $this->userEmail . ':' . md5($this->apiKey . md5($this->apiKey . $this->getDataAsString($data)));
    }

    /**
     * @param $data
     * @return string
     */
    function getDataAsString($data)
    {
        $str = '';
        $this->arrayToStr($data, $str);

        return $str;
    }

    /**
     * @param $data
     * @param $response_str
     */
    function arrayToStr($data, &$response_str)
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
