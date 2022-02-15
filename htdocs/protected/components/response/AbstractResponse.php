<?php

/**
 * Абстрактный клас результата выполнения действия
 * Class AbstractResponse
 *
 * @author Aleksandr Roik
 */
abstract class AbstractResponse
{
    /**
     * Результат, что обрабатывает класс Response
     *
     * @var mixed
     */
    protected $responseData;

    /**
     * Response constructor.
     *
     * @param $response
     */
    public function __construct($responseData)
    {
        $this->responseData = $responseData;

        $this->prepare();
    }

    /**
     * Дополнительная обработка (загрулка)
     */
    protected function prepare()
    {
    }

    /**
     * Возвращает результат
     *
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * Метод вызывается в базовом контрролере для получения результата метода действия
     *
     * @return void
     */
    abstract public function render();

    /**
     * @param $data
     * @return void
     */
    protected function renderJson($data)
    {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    /**
     * @param $data
     * @return void
     */
    protected function renderText($data)
    {
        echo $data;
    }

    /**
     * @param $data
     * @return void
     */
    protected function renderXml($data)
    {
        header("Content-Type: application/xml; charset=utf-8");
        $this->renderText(\Helper::arrayToXml($data));
    }
}
