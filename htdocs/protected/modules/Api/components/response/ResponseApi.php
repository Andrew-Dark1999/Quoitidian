<?php

/**
 * Клас представляет результат выполнения действия контроллера Api.
 * Class ResponseApi
 *
 * @author Aleksandr Roik
 */
class ResponseApi extends AbstractResponse
{
    /**
     * Результат, что будет отдан на вывод (возвращен как результат)
     *
     * @var string
     */
    private $result;

    /**
     * Возвращает типа, в котором будут возвращены данные
     *
     * @param array $type
     * @return string
     * @see ResponseTypeDefinition
     */
    private function getType()
    {
        $result = ResponseTypeDefinition::TYPE_JSON;

        $type = Vars::getInstance()->getVar('response_type');

        if (in_array($type, ResponseTypeDefinition::getTypeCollection())) {
            $result = $type;
        }

        return $result;
    }

    /**
     * Дополнительная обработка
     */
    protected function prepare()
    {
        $this->prepareResult();
        $this->prepareLogger();
    }

    /**
     * Подготовка результата на основании данных $this->responseData
     */
    private function prepareResult()
    {
        if ($this->responseData instanceof Response) {
            return;
        }

        $result = [];
        $status = $this->getStatus();

        if ($status === ResponseApiStatusDefinition::SUCCESS) {
            $result['data'] = $this->responseData;
        } elseif ($status === ResponseApiStatusDefinition::ERROR) {
            $result['messages'] = $this->getPublicErrorMessages();
        }

        $this->result = [
                'status' => $status
            ] + $result;
    }

    /**
     *
     */
    private function prepareLogger()
    {
        (new ApiLogger($this))->prepare();
    }

    /**
     * Метод вызывается в базовом контрролере для получения результата метода действия
     *
     * @return void
     */
    public function render()
    {
        if ($this->responseData instanceof Response) {
            $this->responseData->render();
        } else {
            // Форматируем ответ
            switch ($this->getType()) {
                case ResponseTypeDefinition::TYPE_JSON :
                    $this->renderJson($this->result);
                    break;
                case ResponseTypeDefinition::TYPE_XML :
                    $this->renderXml($this->result);
                    break;
            }
        }
    }

    /**
     * Возвращает статус ответа
     *
     * @return string
     */
    public function getStatus()
    {
        if (
            $this->responseData instanceof Validate ||
            $this->responseData instanceof Throwable
        ) {
            return ResponseApiStatusDefinition::ERROR;
        }

        return ResponseApiStatusDefinition::SUCCESS;
    }

    /**
     * Возвращает список уведомлений об ощибках(е)
     */
    private function getPublicErrorMessages()
    {
        $message = $this->getErrorMessages();

        // Если выключен режим отладки, есть ошибки и они из разряда ошибок логики - скрываем стандартным сообщением
        if (YII_DEBUG === false && $message && $this->responseData instanceof Throwable) {
            return Yii::t('api', 'Server error');
        }

        return $message;
    }

    /**
     * Возвращает список уведомлений об ощибках(е)
     */
    public function getErrorMessages()
    {
        if ($this->responseData instanceof Validate) {
            return $this->responseData->getValidateResult();
        }

        if ($this->responseData instanceof Throwable) {
            return $this->responseData->getMessage();
        }
    }
}
