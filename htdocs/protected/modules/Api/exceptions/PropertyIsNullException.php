<?php

/**
 * Исключение, если значение параметра равно NULL
 * Class PropertyIsNullException
 *
 * @author Aleksandr Roik
 */
class PropertyIsNullException extends BaseException
{
    /**
     * PropertyIsNullException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Значение параметра равно нулю', $code = 423, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
