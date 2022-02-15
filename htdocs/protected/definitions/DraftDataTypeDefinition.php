<?php

/**
 * Справочник типов данных для Черновика
 * Class DraftDataTypeDefinition
 *
 * @author Aleksandr Roik
 */
class DraftDataTypeDefinition extends AbstractDefinition
{
    /**
     * @var integer
     */
    const TEXT = 1;
    const JSON = 2;

    /**
     * @var array
     */
    protected static $collection = [
        self::TEXT,
        self::JSON,
    ];
}
