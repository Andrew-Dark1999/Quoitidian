<?php

/**
 * Клас представляет результат выполнения действия  с возвратом данных в определенном формате.
 * Class ResponseData
 *
 * @author Aleksandr Roik
 */
class ResponseData extends AbstractResponse
{
    /**
     * Статус
     *
     * @var null
     */
    private $status;

    /**
     * Результат для вывода
     *
     * @var array
     */
    private $result;

    /**
     * ResponseData constructor.
     *
     * @param $responseData
     * @param null $status
     */
    public function __construct($responseData, $status = null)
    {
        if ($status !== null) {
            $this->status = $status;
        }

        parent::__construct($responseData);
    }

    /**
     * @param null $status
     * @return ResponseData
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Дополнительная обработка
     */
    protected function prepare()
    {
        $this->prepareResult();
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
            $result['messages'] = $this->getErrorMessages();
        }

        $this->result = [
                'status' => $status
            ] + $result;
    }

    /**
     * Выводит результат
     *
     * @return void
     */
    public function render()
    {
        $this->renderJson($this->result);
    }

    /**
     * Возвращает статус ответа
     *
     * @return string
     */
    private function getStatus()
    {
        if ($this->status !== null) {
            return $this->status;
        }

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
    private function getErrorMessages()
    {
        if ($this->responseData instanceof Validate) {
            return $this->responseData->getValidateResult();
        }

        if ($this->responseData instanceof Throwable) {
            return $this->responseData->getMessage();
        }
    }
}
