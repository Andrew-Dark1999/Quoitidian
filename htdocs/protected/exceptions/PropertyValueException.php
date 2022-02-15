<?php

/**
 * Исключение, если неверное значение параметра
 * Class PropertyNotFoundException
 */
class PropertyValueException extends \Exception
{
    /**
     * PropertyValueException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Ошибочный параметр', $code = 423, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
