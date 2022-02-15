<?php

/**
 * Класс призназначяен для обработки и создания файла-документ на основании внутренних шаблонов и данных,
 * переданых ему в качестве параметра bpm-процесса, связанных с этим процессом сущностей других модулей.
 *
 * Алгоритм работы:
 * 1. Загрузка информации о BPM процессе на основании Id процесса (processEntityId)
 * 2. Загрузка информации о всех сущностях связанных модулях связанного объекта процесса
 * 3. Локально создание файла-документа на основании данных связаных модулей и внутренних шаблонов
 * 4. Создание сущности модуля Документы с загрузкой сформированого ранне файла-документа на сервер CRM
 * 5. Установка связи из связанным объектом процесса и сущностью из модуля Документы
 *
 * Class ZaoobedinennoegHandleDocument
 *
 * @author Aleksandr Roik
 */
class ZaoobedinennoegHandleDocument
{
    /**
     * Список модулей системы (module_id)
     */
    const MODULE_PROJECTS = 10; // Проекты
    const MODULE_DEALS = 1004; //Сделки
    const MODULE_PROCESS = 9; // Процессы
    const MODULE_DOCUMENTS = 11; //Документы
    const MODULE_PARTNERS = 1000; // Партнеры
    const MODULE_COUNTERPARTNERS = 1012; // Контрагенты
    const MODULE_FINANCE = 1005; //Финансы

    /**
     * Алиясы модулей
     */
    const MODULE_ALIASES = [
        self::MODULE_DOCUMENTS => 'doc',
    ];

    /**
     * Действия API
     */
    const API_ACTION_MODULE_UPLOAD_FILE = 'module.uploadFile';
    const API_ACTION_PROCESS_BPM_GET_INFO = 'process.bpm.getInfo';
    const API_ACTION_MODULE_SAVE = 'module.save';
    const API_ACTION_MODULE_UPDATE = 'module.update';
    const API_ACTION_MODULE_IMPORT = 'module.import';

    /**
     * Типы статусов ответов API
     */
    const RESPONSE_STATUS_SUCCESS = 'success';
    const RESPONSE_STATUS_ERROR = 'error';

    /**
     * Uri для за запросов API
     *
     * @var string
     */
    private $apiUri = 'https://host/api/html/run';

    /**
     * Пользователь, от имени которого будет использоваться API
     *
     * @var string
     */
    private $userEmail = '';

    /**
     * Ключ пользователя для API
     *
     * @var string
     */
    private $apiKey = '';

    /**
     * Id сущности активного процесса
     *
     * @var
     */
    private $processEntityId;

    /**
     * ZaoobedinennoegHandleDocument constructor.
     *
     * @param $processId
     */
    public function __construct($processEntityId)
    {
        $this->processEntityId = $processEntityId;
    }

    /**
     * Запуск обработчика
     */
    public function run()
    {
        $processBpmInfo = $this->getProcessBpmInfo();
        if (!$processBpmInfo) {
            return;
        }

        $modulesInfo = $this->getModulesInfo($processBpmInfo);
        if (!$modulesInfo) {
            return;
        }

        $documentId = $this->saveExternalDocument($this->makeInternalDocument($modulesInfo));
        if ($documentId) {
            $this->linkToRelateModule($processBpmInfo, $documentId);
        }
    }

    /**
     * Возвращает информацию о BPM процессе
     *
     * @return StdObject|null
     */
    private function getProcessBpmInfo()
    {
        // данные
        $data = [
            'id' => $this->processEntityId,
        ];

        $response = $this->sendHtml(
            $this->formatQueryData(self::API_ACTION_PROCESS_BPM_GET_INFO, $data)
        );

        return ($response->status == self::RESPONSE_STATUS_SUCCESS ? $response->data : null);
    }

    /**
     * Возвращает информацию о всех сущностях связанных модулях связанного объекта процесса
     *
     * @param $processBpmInfo
     * @return StdObject|null
     */
    private function getModulesInfo($processBpmInfo)
    {
        // данные
        $data = [
            'module_id'      => $processBpmInfo->properties->related_module_id,
            'entity_id'      => $processBpmInfo->properties->related_entity_id,
            'relate_modules' => [
                [
                    'module_id' => self::MODULE_PARTNERS,
                ],
                [
                    'module_id' => self::MODULE_COUNTERPARTNERS,
                ],
                [
                    'module_id' => self::MODULE_FINANCE,
                ]
            ]
        ];

        $response = $this->sendHtml(
            $this->formatQueryData(self::API_ACTION_MODULE_IMPORT, $data)
        );

        return ($response->status == self::RESPONSE_STATUS_SUCCESS ? $response->data : null);
    }

    /**
     * Локально создает файл-документ на основании данных связаных модулей и шаблона
     *
     * @param $moduleInfo
     * @return string
     */
    private function makeInternalDocument($moduleInfo)
    {
        //TODO: Здесь необходимо реализовать код создания файла-документа и возвратить его путь на диске

        $filePath = '/home/alex/Картинки/images.jpeg';

        return $filePath;
    }

    /**
     * Создает сушность в модуле Документы
     *
     * @param $documentFilePath Путь к файлу
     * @return StdObject|null
     */
    private function saveExternalDocument($documentFilePath)
    {
        // Сначала загружаем файл
        $fileId = $this->uploadFile($documentFilePath);
        if (!$fileId) {
            return;
        }

        $data = [
            'module_id'  => self::MODULE_DOCUMENTS,
            'attributes' => [
                'module_title' => 'Новый документ',
                'doc_file'     => $fileId,
            ]
        ];

        // Создаем номую сущность в модуле Документы
        $response = $this->sendHtml(
            $this->formatQueryData(self::API_ACTION_MODULE_SAVE, $data)
        );

        return ($response->status == self::RESPONSE_STATUS_SUCCESS ? $response->data : null);
    }

    /**
     * Загружает файл
     *
     * @param $documentFilePath
     * @return StdObject|null
     */
    private function uploadFile($documentFilePath)
    {
        $response = $this->sendHtml(
            $this->formatQueryData(self::API_ACTION_MODULE_UPLOAD_FILE, '', $documentFilePath)
        );

        return ($response->status == self::RESPONSE_STATUS_SUCCESS ? $response->data : null);
    }

    /**
     * Связывает сущность связанного объекта процесса и сущность из модуля Документы
     *
     * @param $processBpmInfo
     * @param $documentId
     */
    private function linkToRelateModule($processBpmInfo, $documentId)
    {
        $data = [
            'module_id'  => $processBpmInfo->properties->related_module_id,
            'entity_id'  => $processBpmInfo->properties->related_entity_id,
            'attributes' => [
                self::MODULE_ALIASES[self::MODULE_DOCUMENTS] => $documentId, //Связываем в сабмодуле Документы новую сущность $documentId
            ]
        ];

        $response = $this->sendHtml(
            $this->formatQueryData(self::API_ACTION_MODULE_UPDATE, $data)
        );

        return ($response->status == self::RESPONSE_STATUS_SUCCESS ? true : false);
    }




    /**********************************************************************************
     *              API ЗАПРОСЫ
     ***********************************************************************************/

    /**
     * Отправляем API запрос
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
     * @param string $action
     * @param array $data
     * @param null $filePath
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
     * @param $data
     * @return string
     */
    private function encodeDataToSignature($data)
    {

        return $this->userEmail . ':' . md5($this->apiKey . md5($this->apiKey . $this->getDataAsString($data)));
    }

    /**
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
