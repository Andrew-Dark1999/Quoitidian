<?php

/**
 * Исключение сохранения или обновление записи в БД
 * Class ModelNotSaveException
 */
class ModelNotSaveException extends \Exception
{
    /**
     * ModelNotSaveException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Не удалось сохранить запис', $code = 423, Throwable $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }
}
