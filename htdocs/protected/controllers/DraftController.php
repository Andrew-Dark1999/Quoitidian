<?php

/**
 * Контроолер для работы с черновиками
 * Class DraftController
 *
 * @author Aleksandr Roik
 */
class DraftController extends Controller
{
    /**
     * @var DraftManager
     */
    private $manager;

    /**
     * DraftController constructor.
     *
     * @param $id
     * @param null $module
     */
    public function __construct($id, $module = null)
    {
        $this->manager = new DraftManager();

        parent::__construct($id, $module);

    }

    /**
     * Устанавливаем главный клас Respons-a для всего контроллера
     *
     * @return string
     */
    public function getResponse()
    {
        return ResponseData::class;
    }

    /**
     * Вставляем новую или обновляет существующую сущность тустового типа
     *
     * @return ResponseData
     */
    public function actionSaveText()
    {
        return new ResponseData($this->manager->saveText($_POST));
    }

    /**
     * Вставляем новую или обновляет существующую сущность типа json
     *
     * @return ResponseData
     */
    public function actionSaveJson()
    {
        return new ResponseData($this->manager->saveJson($_POST));
    }

    /**
     * Возвращает сущность по индексу
     *
     * @param $uid
     * @return ResponseData
     */
    public function actionGetByIndex($uid)
    {
        return new ResponseData($this->manager->getByUid($uid));
    }

    /**
     * Удаляем сущность
     *
     * @param $uid
     * @return ResponseData
     */
    public function actionDeleteByIndex($uid)
    {
        return new ResponseData($this->manager->deleteByUid($uid));
    }
}
