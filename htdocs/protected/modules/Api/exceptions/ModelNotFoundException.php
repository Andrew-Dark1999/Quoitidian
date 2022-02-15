<?php

/**
 * Исключение поиска записи в БД
 * Class ModelNotFoundException
 *
 * @author Aleksandr Roik
 */
class ModelNotFoundException extends BaseException
{
    /**
     * ModelNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Не удалось найти сущность', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

