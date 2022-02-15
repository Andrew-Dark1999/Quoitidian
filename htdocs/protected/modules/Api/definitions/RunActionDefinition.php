<?php

/**
 * Class RunActionDefinition
 *
 * @author Aleksandr Roik
 */
class RunActionDefinition
{
    //версия 1.0
    const ACTION_MODULE_SAVE = 'moduleSave';
    const ACTION_MODULE_UPDATE = 'moduleUpdate';
    const ACTION_MODULE_VALIDATE = 'moduleValidate';
    const ACTION_MODULE_IMPORT = 'moduleImport';
    const ACTION_MODULE_ACTIVITY_CREATE_TEXT_MESSAGE = 'module.activity.createTextMessage';
    const ACTION_MODULE_UPLOAD_FILE = 'module.uploadFile';
    const ACTION_MODULE_UPLOAD_FILE_IMAGE = 'module.uploadFileImage';
    const ACTION_MODULE_ACTIVITY_UPLOAD_FILE = 'module.activity.uploadFile';
    const ACTION_SAVE_PROCESSING_MARK = 'saveProcessingMark';
    //Процессы
    const ACTION_PROCESS_BPM_GET_INFO = 'process.bpm.getInfo';
    //версия 2.0
    const ACTION_MODULE_SAVE_2_0 = 'module.save';
    const ACTION_MODULE_UPDATE_2_0 = 'module.update';
    const ACTION_MODULE_VALIDATE_2_0 = 'module.validate';
    const ACTION_MODULE_IMPORT_2_0 = 'module.import';
    const ACTION_MODULE_SAVE_PROCESSING_MARK = 'module.saveProcessingMark';

    /**
     * @var array
     */
    protected static $actionCollection = [
        self::ACTION_MODULE_SAVE,
        self::ACTION_MODULE_UPDATE,
        self::ACTION_MODULE_VALIDATE,
        self::ACTION_MODULE_IMPORT,
        self::ACTION_MODULE_ACTIVITY_CREATE_TEXT_MESSAGE,
        self::ACTION_MODULE_UPLOAD_FILE,
        self::ACTION_MODULE_UPLOAD_FILE_IMAGE,
        self::ACTION_MODULE_ACTIVITY_UPLOAD_FILE,
        self::ACTION_SAVE_PROCESSING_MARK,
        self::ACTION_PROCESS_BPM_GET_INFO,
        self::ACTION_MODULE_SAVE_2_0,
        self::ACTION_MODULE_UPDATE_2_0,
        self::ACTION_MODULE_VALIDATE_2_0,
        self::ACTION_MODULE_IMPORT_2_0,
    ];

    /**
     * Возвращает список действий
     *
     * @return array
     */
    public static function getActionCollection()
    {
        return static::$actionCollection;
    }

    /**
     * Возвращает наличие действия
     *
     * @param $type
     * @return bool
     */
    public static function hasAction($type)
    {
        return in_array($type, self::getActionCollection());
    }
}
