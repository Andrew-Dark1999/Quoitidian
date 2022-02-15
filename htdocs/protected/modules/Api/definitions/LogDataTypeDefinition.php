<?php

/**
 * Список типов груп данных для логирования
 * Class LogDataTypeDefinition
 *
 * @author Aleksandr Roik
 */
class LogDataTypeDefinition
{
    /**
     * Лог ошибок: ошибки логики
     */
    const API_LOGGER_THROWABLE = 1 << 0;

    /**
     * Лог ошибок: ошибки, найденные валидатором
     */
    const API_LOGGER_VALIDATOR = 1 << 1;

    /**
     * Все запросы, принятие API.
     * Логируется url и список принятых параметров
     */
    const API_LOGGER_REQUEST_URI = 1 << 2;

    /**
     * Все запросы, принятие API.
     *
     * Логируется список принятых параметров
     */
    const API_LOGGER_REQUEST_PARAMS = 1 << 3;

    /**
     * Вссссе типы
     */
    const API_LOGGER_ALL = self::API_LOGGER_THROWABLE | self::API_LOGGER_VALIDATOR | self::API_LOGGER_REQUEST_URI | self::API_LOGGER_REQUEST_PARAMS;
}
