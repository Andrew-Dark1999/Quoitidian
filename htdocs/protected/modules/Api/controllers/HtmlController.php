<?php

/**
 * HtmlController
 *
 * @author Alex R.
 * @version 1.0
 */

class HtmlController extends Controller
{
    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);

        ApiUser::initWebUser();
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return ResponseApi::class;
    }

    /**
     * actionRun - Основной контроллер запуска Api
     * Принимает два параметра: action, vars
     *
     * @return string(json)
     */
    public function actionRun()
    {
        //выполняем метод Api
        $api = new Actions(array_merge($_POST, $_GET));

        if(!$api->run()) {
            return $api->getValidator();
        }

        return $api->getResult();
    }
}

