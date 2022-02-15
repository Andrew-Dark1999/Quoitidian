<?php

/**
 * Исключение, если значение параметра отсутвует
 * Class PropertyNotFoundException
 */
class PropertyNotFoundException extends \Exception
{
    /**
     * PropertyNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Отсутствует параметр', $code = 423, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
