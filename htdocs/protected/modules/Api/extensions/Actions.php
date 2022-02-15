<?php

/**
 * Actions - парсинг и исполнение действий АРI методов
 *
 * @author Alex R.
 */
class Actions
{
    /**
     * Должен иметь два значения: action, vars
     *
     * @var array
     */
    private $request;

    /**
     * @var ActionsValidator
     */
    private $validator;

    /**
     * Результат выполнения действия. Может быть null
     *
     * @var mixed
     */
    private $result;

    /**
     * ApiActions constructor.
     *
     * @param array $request . Должен иметь два значения: action, vars
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->validator = new ActionsValidator($this, $request);
        $this->prepareLanguage();
    }

    /**
     * @param $vars
     */
    private function prepareLanguage()
    {
        $language = Vars::getInstance()->getVar('language');

        // language
        if ($language && LanguageModel::model()->count(['condition' => 'name=:name', 'params' => [':name' => $language]]) > 0) {
            Yii::app()->setLanguage($language);
        } else {
            Yii::app()->setLanguage(ParamsModel::model()->titleName('language')->find()->getValue());
        }
    }

    /**
     * @return ActionValidator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return $this
     */
    public function getAction()
    {
        return $this->request['action'];
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Запуск
     */
    public function run()
    {
        if (!$this->validator->validate()) {
            return false;
        }

        return $this->executeAction();
    }

    /**
     * Выполняем действие
     *
     * @return bool
     */
    private function executeAction()
    {
        switch ($this->getAction()) {
            case RunActionDefinition::ACTION_MODULE_SAVE :
            case RunActionDefinition::ACTION_MODULE_SAVE_2_0 :
                return $this->executeActionModuleSave();

            case RunActionDefinition::ACTION_MODULE_UPDATE :
            case RunActionDefinition::ACTION_MODULE_UPDATE_2_0 :
                return $this->executeActionModuleUpdate();

            case RunActionDefinition::ACTION_MODULE_VALIDATE :
            case RunActionDefinition::ACTION_MODULE_VALIDATE_2_0 :
                return $this->executeActionModuleValidate();

            case RunActionDefinition::ACTION_MODULE_IMPORT :
            case RunActionDefinition::ACTION_MODULE_IMPORT_2_0 :
                return $this->executeActionModuleImport();

            case RunActionDefinition::ACTION_SAVE_PROCESSING_MARK :
            case RunActionDefinition::ACTION_MODULE_SAVE_PROCESSING_MARK:
                return $this->executeActionSaveProcessingMark();

            case RunActionDefinition::ACTION_MODULE_ACTIVITY_CREATE_TEXT_MESSAGE :
                return $this->executeActionModuleActivityCreateTextMessage();

            case RunActionDefinition::ACTION_PROCESS_BPM_GET_INFO:
                return $this->executeActionProcessBpmGetInfo();

            case RunActionDefinition::ACTION_MODULE_UPLOAD_FILE:
                return $this->executeActionModuleUploadFile();

            case RunActionDefinition::ACTION_MODULE_UPLOAD_FILE_IMAGE:
                return $this->executeActionModuleUploadFileImage();

            case RunActionDefinition::ACTION_MODULE_ACTIVITY_UPLOAD_FILE :
                return $this->executeActionModuleActivityUploadFile();
        }

        return false;
    }

    /**
     * Выполняем действие Сохранение новой сущности модуля
     *
     * @return bool
     */
    private function executeActionModuleSave()
    {
        $apiModule = new ActionModuleSave(Vars::getInstance()->getVar('data'));

        if ($apiModule->save()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getEditViewModel()->qwe_primary_key;

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Выполняем действие Обновление сущности модуля
     *
     * @return bool
     */
    private function executeActionModuleUpdate()
    {
        $apiModule = new ActionModuleUpdate(Vars::getInstance()->getVar('data'));

        if ($apiModule->update()) {
            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Выполняем действие Проверка сущности модуля
     *
     * @return bool
     */
    private function executeActionModuleValidate()
    {
        $apiModule = new ActionModuleSave(Vars::getInstance()->getVar('data'));

        if ($apiModule->validateModel()) {
            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Выполняем действие Импорм данных модуля
     *
     * @return bool
     */
    private function executeActionModuleImport()
    {
        $apiModule = new ActionModuleImport(Vars::getInstance()->getVar('data'));

        if ($apiModule->import()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Сохранение некой отметки для сущности модуля.
     * Дальше в методе moduleImport в условии можно проверить эту отметку
     *
     * @return bool
     */
    private function executeActionSaveProcessingMark()
    {
        $apiModule = new ActionSaveProcessingMark(Vars::getInstance()->getVar('data'));

        if ($apiModule->save()) {
            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;

    }

    /**
     * Сохранение нового тектового сообщения из блока Активность
     *
     * @return bool
     */
    private function executeActionModuleActivityCreateTextMessage()
    {
        $apiModule = new ActionModuleActivityCreateTextMessage(Vars::getInstance()->getVar('data'));

        if ($apiModule->save()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Процесс. Возвращает информацию о структуре Bpm
     *
     * @return bool
     */
    private function executeActionProcessBpmGetInfo()
    {
        $apiModule = new ActionProcessBpmGetInfo(Vars::getInstance()->getVar('data'));

        if ($apiModule->prepare()) {
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Загрузка файла для сущности модуля
     *
     * @return bool
     */
    private function executeActionModuleUploadFile()
    {
        $apiModule = new ActionModuleUploadFile(Vars::getInstance()->getVar('data'));

        if ($apiModule->upload()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Загрузка файла для сущности модуля
     *
     * @return bool
     */
    private function executeActionModuleUploadFileImage()
    {
        $apiModule = new ActionModuleUploadFileImage(Vars::getInstance()->getVar('data'));

        if ($apiModule->upload()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;
    }

    /**
     * Загрузка файла для блока Активность
     *
     * @return bool
     */
    private function executeActionModuleActivityUploadFile()
    {
        $apiModule = new ActionModuleActivityUploadFile(Vars::getInstance()->getVar('data'));

        if ($apiModule->upload()) {
            // Как результат возвращает id новой сущности
            $this->result = $apiModule->getResult();

            return true;
        }

        $this->validator = $apiModule->getValidator();

        return false;

    }

}

