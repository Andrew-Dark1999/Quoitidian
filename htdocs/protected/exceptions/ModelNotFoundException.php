<?php

/**
 * Исключение поиска записи в БД
 * Class ModelNotFoundException
 */
class ModelNotFoundException extends \Exception
{
    /**
     * ModelNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Не удалось найти запись', $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

