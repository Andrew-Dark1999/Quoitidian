<?php

/**
 * Логирем результат выполнения Api
 * Class ApiLogger
 *
 * @author Aleksandr Roik
 */
class ApiLogger
{
    /**
     * Экземпляр класса для проверки параметров ведения логов
     *
     * @var AccessCheckerBin
     */
    private $accessCheckerBin;

    /**
     * @var ResponseApi
     */
    private $responseApi;

    /**
     * ApiLogger constructor.
     *
     * @param $responseApi
     */
    public function __construct($responseApi)
    {
        $this->responseApi = $responseApi;

        // Загружаем параметры логирования
        $this->accessCheckerBin = new AccessCheckerBin(ParamsModel::getValueFromModel('api_logger'));
    }

    /**
     * Выполняем логирование
     */
    public function prepare()
    {
        $this->prepareErrorLogThrowable();
        $this->prepareErrorLogValidator();
        $this->prepareRequestLog();
    }

    /**
     * Пишем лог ошибок и исключений
     */
    private function prepareErrorLogThrowable()
    {
        if ($this->responseApi->getStatus() != ResponseApiStatusDefinition::ERROR) {
            return;
        }

        // Ошибки програмной логики
        if (
            !$this->accessCheckerBin->check(LogDataTypeDefinition::API_LOGGER_THROWABLE) ||
            !$this->responseApi->getResponseData() instanceof Throwable
        ) {
            return;
        }

        $this->logError('Interface: API_LOGGER_THROWABLE');

        // пишем трассировку
        $e = $this->responseApi->getResponseData();
        $traces = $e->getTrace();
        for ($i = count($traces) - 1; $i >= 0; $i--) {
            $trace = $traces[$i];
            $message = "{$trace['file']} ({$trace['line']}): {$trace['class']}{$trace['type']}{$trace['function']}()";
            $this->logError($message);
        }

        // пишем основное сообщение
        $level = $e instanceof Error ? 'Error' : 'Exception';
        $message = "$level: {$e->getMessage()} in {$e->getFile()} ({$e->getLine()})";
        $this->logError($message);
    }

    /**
     * Пишем лог ошибок валидации
     */
    private function prepareErrorLogValidator()
    {
        if ($this->responseApi->getStatus() != ResponseApiStatusDefinition::ERROR) {
            return;
        }

        // Ошибки програмной логики
        if (
            !$this->accessCheckerBin->check(LogDataTypeDefinition::API_LOGGER_VALIDATOR) ||
            !$this->responseApi->getResponseData() instanceof Validate
        ) {
            return;
        }
        $this->logError('Interface: API_LOGGER_VALIDATOR');
        $this->prepareErrorLogValidatorRecicle($this->responseApi->getErrorMessages());
    }

    /**
     * Пишем лог ошибок валидации, циклично обходя весь массив
     *
     * @param $messages
     */
    private function prepareErrorLogValidatorRecicle($messages)
    {
        foreach ($messages as $key => $message) {
            if (is_array($message)) {
                $this->prepareErrorLogValidatorRecicle($message);
            } else {
                $this->logError($key . ' => ' . $message);
            }
        }
    }

    /**
     * Пишем лог ошибок и исключений
     */
    private function prepareRequestLog()
    {
        // Ошибки програмной логики
        if (!$this->accessCheckerBin->check(LogDataTypeDefinition::API_LOGGER_REQUEST_URI | LogDataTypeDefinition::API_LOGGER_REQUEST_PARAMS)) {
            return;
        }

        $level = $this->responseApi->getStatus() == ResponseStatusDefinition::SUCCESS ? CLogger::LEVEL_INFO : CLogger::LEVEL_ERROR;

        if($this->accessCheckerBin->check(LogDataTypeDefinition::API_LOGGER_REQUEST_URI)) {
            $this->logRequest('URI: ' . Yii::app()->request->getRequestUri(), $level);
        }

        if($_POST && $this->accessCheckerBin->check(LogDataTypeDefinition::API_LOGGER_REQUEST_PARAMS)) {
            $this->logRequest('Params: ' . json_encode($_POST), $level);
        }
    }

    /**
     * Пишем лог ошибок
     *
     * @param $messages
     */
    private function logError($message, $level = CLogger::LEVEL_ERROR)
    {
        Yii::log($message, $level, 'api-error');
    }

    /**
     * Пишем лог запросов к методам Api
     *
     * @param $message
     */
    private function logRequest($message, $level = CLogger::LEVEL_INFO)
    {
        Yii::log($message, $level, 'api-request');
    }
}
