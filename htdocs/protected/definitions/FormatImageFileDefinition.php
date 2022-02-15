<?php

/**
 * Список типов файлов для изображений
 * Class BlockActivityEditorDefinition
 *
 * @author Aleksandr Roik
 */
class FormatImageFileDefinition extends AbstractDefinition
{
    const BMP = 'bmp';
    const GIF = 'gif';
    const JPG = 'jpg';
    const JPEG = 'jpeg';
    const PNG = 'png';
    const TGA = 'tga';
    const TIF = 'tif';
    const ICO = 'ico';

    protected static $collection = [
        self::BMP,
        self::GIF,
        self::JPG,
        self::JPEG,
        self::PNG,
        self::TGA,
        self::TIF,
        self::ICO,
    ];

    protected static $titleCollection = [
        self::BMP  => 'bmp',
        self::GIF  => 'gif',
        self::JPG  => 'jpg',
        self::JPEG => 'jpeg',
        self::PNG  => 'png',
        self::TGA  => 'tga',
        self::TIF  => 'tif',
        self::ICO  => 'ico',
    ];
}
