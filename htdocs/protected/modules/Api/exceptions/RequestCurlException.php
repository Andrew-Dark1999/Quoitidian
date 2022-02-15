<?php

/**
 * Исключение, если возникла ошибка выполнения запросса
 * Class RequestCurlException
 *
 * @author Aleksandr Roik
 */
class RequestCurlException extends BaseException
{
    /**
     * RequestCurlException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Возникла ошибка выполнения запросса', $code = 423, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
