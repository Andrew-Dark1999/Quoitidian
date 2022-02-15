<?php

/**
 * Список допустимых эдиторов для блока Активности в EditView
 * Class BlockActivityEditorDefinition
 *
 * @author Aleksandr Roik
 */
class BlockActivityEditorDefinition extends AbstractDefinition
{
    const EMOJI = 'emoji';
    const TINY_MCE = 'tiny_mce';

    protected static $collection = [
        self::EMOJI,
        self::TINY_MCE,
    ];

    protected static $titleCollection = [
        self::EMOJI    => 'Emoji',
        self::TINY_MCE => 'TinyMCE',
    ];

    /**
     * Возвращает список названий
     *
     * @return array
     */
    public static function getTitleCollection()
    {
        $result = [];

        foreach (static::$titleCollection as $key => $title) {
            $result[$key] = Yii::t('base', $title);
        }

        return $result;
    }
}
