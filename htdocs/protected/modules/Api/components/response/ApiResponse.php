<?php

class ApiResponse extends Response
{
    /**
     * Экземпляр класса для проверки параметров ведения логов
     *
     * @var AccessCheckerBin
     */
    protected $accessCheckerBin;

    /**
     * Тип, в котором будут возвращены данные
     *
     * @var array
     */
    protected $type = ResponseTypeDefinition::TYPE_JSON;

    /**
     * Результат, что будет отдан на вывод (возвращен как результат)
     *
     * @var string
     */
    private $result;

    /**
     * Установка типа, в котором будут возвращены данные
     *
     * @param array $type
     * @return ApiResponse
     * @see ResponseTypeDefinition
     */
    public function setType($type)
    {
        if (in_array($type, ResponseTypeDefinition::getTypeCollection())) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Дополнительная обработка
     */
    protected function prepare()
    {
        $this->initAccessCheckerBin();
        $this->prepareResult();
        $this->prepareLog();
    }

    /**
     * Установка класса для проверки параметров ведения логов
     */
    private function initAccessCheckerBin()
    {
        $this->accessCheckerBin = new AccessCheckerBin(ParamsModel::getValueFromModel('api_logger'));
    }

    /**
     * Подготовка результата на основании данных $this->responseData
     */
    private function prepareResult()
    {
        $result = [];
        $status = $this->getStatus();

        if ($status === ResponseStatusDefinition::SUCCESS) {
            $result['data'] = $this->responseData;
        } elseif ($status === ResponseStatusDefinition::ERROR) {
            $result['messages'] = $this->getPublicErrorMessages();
        }

        $this->result = [
                'status' => $status
            ] + $result;
    }

    /**
     *
     */
    private function prepareLog()
    {
        $status = $this->getStatus();
        if($status == ResponseStatusDefinition::ERROR) {
            $this->saveLoggerError();
        }


        $this->saveLoggerRequest();



        if (!($this->responseData instanceof Throwable)) {
            return;
        }
        //API_LOGGER_THROWABLE|API_LOGGER_VALIDATOR|API_LOGGER_SUCCESS

        $this->getErrorMessages();

        // пишем трассировку
        $e = $this->responseData;
        $traces = $e->getTrace();
        for ($i = count($traces) - 1; $i >= 0; $i--) {
            $trace = $traces[$i];
            $message = "{$trace['file']} ({$trace['line']}): {$trace['class']}{$trace['type']}{$trace['function']}()";
            $this->addLog($message);
        }

        // пишем основное сообщение
        $level = $e instanceof Error ? 'Error' : 'Exception';
        $message = "$level: {$e->getMessage()} in {$e->getFile()} ({$e->getLine()})";
        $this->addLog($message);
    }

    /**
     * Выводит результат
     *
     * @param $type Тип, в котором
     */
    public function render()
    {
        if ($this->responseData instanceof Response) {
            return $this->responseData->render();
        }

        switch ($this->type) {
            case ResponseTypeDefinition::TYPE_JSON :
                header("Content-Type: application/json; charset=utf-8");

                return $this->renderJson($this->result);

            case ResponseTypeDefinition::TYPE_XML :
                header("Content-Type: application/xml; charset=utf-8");

                return $this->renderText(\Helper::arrayToXml($this->result));
        }
    }

    /**
     * Возвращает статус ответа
     *
     * @return string
     */
    private function getStatus()
    {
        if (
            $this->responseData instanceof Validate ||
            $this->responseData instanceof Throwable
        ) {
            return ResponseStatusDefinition::ERROR;
        }

        return ResponseStatusDefinition::SUCCESS;
    }

    /**
     * Возвращает список уведомлений об ощибках(е)
     */
    private function getPublicErrorMessages()
    {
        $message = $this->getErrorMessages();

        return $message === null ? Yii::t('api', 'Server error') : $message;
    }

    /**
     * Возвращает список уведомлений об ощибках(е)
     */
    private function getErrorMessages()
    {
        if ($this->responseData instanceof Validate) {
            return $this->responseData->getValidateResult();
        }
        if ($this->responseData instanceof Throwable) {
            if (YII_DEBUG === true) {
                return $this->responseData->getMessage();
            }
        }
    }

    /**
     * Пишем лог
     *
     * @param $messages
     */
    private function addLogError($message, $level, $category)
    {
        Yii::log($message, $level, $category);
        //Yii::log($message, CLogger::LEVEL_ERROR, 'api-error');
    }

    /**
     * Пишем лог
     *
     * @param $messages
     */
    private function addLogError1($message, $level, $category)
    {
        Yii::log($message, $level, $category);
        //Yii::log($message, CLogger::LEVEL_ERROR, 'api-error');
    }
}
