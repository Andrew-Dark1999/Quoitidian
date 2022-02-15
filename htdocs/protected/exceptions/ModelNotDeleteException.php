<?php

/**
 * Исключение при удалении записи их БД
 * Class ModelNotDeleteException
 */
class ModelNotDeleteException extends \Exception
{
    /**
     * ModelNotDeleteException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Не удалось удалить запись', $code = 423, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
