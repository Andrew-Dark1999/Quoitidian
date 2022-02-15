<?php

/**
 * Исключение, если значение параметра отсутвует
 * Class PropertyNotFoundException
 *
 * @author Aleksandr Roik
 */
class PropertyNotFoundException extends BaseException
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
