<?php

class Controller extends CController
{
    public $layout = "//layouts/main";

    public $data = [];

    public $left_menu = false;

    public $only_body = false;

    // указывает, что модуль открыт как шаблон
    public $this_template = false;

    public function init()
    {
        $this->setLocal();

        \History::getInstance();
        // иницциадизация плагинов
    }

    /**
     * @param string $actionID
     * @throws CHttpException
     */
    public function run($actionID)
    {
        $controller_id = Yii::app()->controller->id;

        History::setUserStorageBackUrl($controller_id, $actionID);

        parent::run($actionID);
    }

    /**
     * Creates the action instance based on the action name.
     *
     * @param string $actionID ID of the action. If empty, the {@link defaultAction default action} will be used.
     * @return CAction the action instance, null if the action does not exist.
     * @throws CException
     * @see actions
     */
    public function createAction($actionID)
    {
        if ($actionID === '') {
            $actionID = $this->defaultAction;
        }
        if (method_exists($this, 'action' . $actionID) && strcasecmp($actionID, 's')) // we have actions method
        {
            // Переопеределяем, чтобы получить в дальнейшем объект Response
            return new InlineAction($this, $actionID);
        } else {
            $action = $this->createActionFromMap($this->actions(), $actionID, $actionID);
            if ($action !== null && !method_exists($action, 'run')) {
                throw new CException(Yii::t('yii', 'Action class {class} must implement the "run" method.', ['{class}' => get_class($action)]));
            }

            return $action;
        }
    }

    /**
     * Возвращает объект Response для контроллера
     *
     * @return string
     */
    public function getResponse()
    {
        return ResponseHtml::class;
    }

    /**
     * Ловим результат выполнення метода действия и отправляем на вывод форматирований ответ
     *
     * @param InlineAction $action
     */
    public function afterAction($action)
    {
        parent::afterAction($action);

        if ($action instanceof InlineAction) {
            /* @var Response $response */
            $response = $action
                ->getResponse();

            if (!$response) {
                return;
            }

            if ($response->getResponseData() instanceof AbstractResponse) {
                $response->getResponseData()->render();
            } else {
                $response->render();
            }
        }
    }

    /**
     * filter
     */
    public function filters()
    {
        return [
            'checkInit',
        ];
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckInit($filterChain)
    {
        if (\Yii::app()->request->isAjaxRequest && Yii::app()->user->isGuest) {
            return $this->renderJson([
                'user_logout' => true,
            ]);
        }

        $r = $this->checkLock();
        if (is_string($r) || $r === false) {
            if (\Yii::app()->request->isAjaxRequest) {
                return $this->renderJson([
                    'user_logout' => true,
                ]);
            } else {
                if (is_string($r)) {
                    return $this->redirect($r);
                }

            }
        } else {

            $r = $this->checkLockTechnicalWorks();
            if (is_string($r) || $r === false) {
                if (\Yii::app()->request->isAjaxRequest) {
                    return $this->renderJson([
                        'user_logout' => true,
                    ]);
                } else {
                    if (is_string($r)) {
                        return $this->redirect($r);
                    }
                }

            }
        }

        if ($this->checkUserLogout() == false) {
            if (\Yii::app()->request->isAjaxRequest) {
                return $this->renderJson([
                    'user_logout' => true,
                ]);
            } else {
                $requestUri = strtolower(Yii::app()->getRequest()->getPathInfo());
                if (!in_array($requestUri, ['login', 'registration', 'restore', 'restore-password', 'locked','restore-password-change'])) {
                    return $this->redirect('/login');
                }
            }
        }

        $filterChain->run();
    }

    /**
     * @return array|false|string
     */
    public function getApplicationTitle()
    {
        if (!$at = getenv('APPLICATION_TITLE')) {
            return ParamsModel::model()->titleName('crm_name')->find()->getValue();
        } else {
            return $at;
        }
    }

    /**
     * возвращает количество пунктов меню
     */
    public function getTopModuleMenuHistoryCount()
    {
        $count = null;
        $history_data = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_MENU_COUNT, 1);

        if (!empty($history_data) && isset($history_data['count']) && $history_data['count']) {
            $count = $history_data['count'];
        }

        return $count;
    }

    /**
     * возвращает список модулей для меню
     */
    public function getExtensionCopyForModuleMenu($check_access = true)
    {
        $result = [];
        $extension_copy_data = ExtensionCopyModel::getUsersModule();
        if ($check_access == true) {
            if (empty($extension_copy_data)) {
                return $result;
            } else {
                foreach ($extension_copy_data as $data) {
                    if (Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $data['copy_id'], Access::ACCESS_TYPE_MODULE) &&
                        (boolean)$data->menu_display == true) {
                        $result[] = $data;
                    }
                }

                return $result;
            }
        } else {
            return $extension_copy_data;
        }
    }

    /**
     * @return bool|string
     * @throws CException
     */
    private function checkLock()
    {
        $params_model = ParamsModel::model()->findByAttributes(['title' => 'locked']);
        $requestUri = strtolower(Yii::app()->getRequest()->getRequestUri());
        $locked = $params_model === null ? false : (bool)$params_model->value;

        if ($requestUri == '/locked' && $locked == false) {
            return '/';
        } else {
            if ($requestUri != '/locked' && $locked) {
                if (!Yii::app()->user->isGuest) {
                    Yii::app()->user->logout();
                }

                return '/locked';
            } else {
                if ($locked) {
                    if (!Yii::app()->user->isGuest) {
                        Yii::app()->user->logout();
                    }

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool|string
     * @throws CException
     */
    private function checkLockTechnicalWorks()
    {
        $params_model = ParamsModel::model()->findByAttributes(['title' => 'locked_technical_works']);
        $requestUri = strtolower(Yii::app()->getRequest()->getRequestUri());
        $locked = $params_model === null ? false : (bool)$params_model->value;

        if ($requestUri == '/locked-technical-works' && !$locked) {
            return '/';
        } else {
            if ($requestUri != '/locked-technical-works' && $locked) {
                if (!Yii::app()->user->isGuest) {
                    Yii::app()->user->logout();
                }

                return '/locked-technical-works';
            } else {
                if ($locked) {
                    if (!Yii::app()->user->isGuest) {
                        Yii::app()->user->logout();
                    }

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkUserLogout()
    {
        if (Yii::app()->user->isGuest == false) {
            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
            $users_model = UsersModel::getUserModel();
            if ($users_model === null) {
                return false;
            }
            if ($users_model->getLogout()) {
                $users_model->setLogout(false);
                Yii::app()->user->logout();

                return false;
            } else {
                return true;
            }
        }

        // exceptions:
        // - api
        if (Yii::app()->controller->module && Yii::app()->controller->module->id == 'api') {
            return true;
        }

        return false;
    }

    /**
     *
     */
    private function setLocal()
    {
        Yii::app()->setLocaleDataPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'i18n');
        $users_params_model = UsersParamsModel::model()->scopeUsersId(WebUser::getUserId())->find();
        if (!empty($users_params_model) && LanguageModel::model()->count(['condition' => 'name=:name', 'params' => [':name' => $users_params_model->language]]) > 0) {
            Yii::app()->setLanguage($users_params_model->language);
        } else {
            Yii::app()->setLanguage(ParamsModel::model()->titleName('language')->find()->getValue());
        }
    }

    /**
     * @param $block
     * @param array $params
     * @param bool $return
     * @return string
     * @throws CException
     */
    public function renderBlock($block, $params = [], $return = false)
    {
        return $this->renderPartial('//blocks/' . $block, $params, $return);
    }

    /**
     * @param array $data
     * @param bool $return
     * @return false|string
     */
    public function renderJson(array $data, $return = false)
    {
        if ($return) {
            return json_encode($data);
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode($data);
        }
    }

    /**
     * @param $data
     */
    public function renderTextOnly($data)
    {
        echo $data;
    }

    /**
     * @param $view
     * @param null $data
     * @throws CException
     */
    public function renderAjax($view, $data = null)
    {
        \ControllerModel::setContentBlocks(\Yii::app()->request->getParam('content_blocks'));

        if (Yii::app()->request->getParam('get_full_page')) {
            $content_html = $this->render($view, $data, true);
        } else {
            $content_html = $this->renderPartial($view, $data, true);
        }

        $result = [
            'status'              => true,
            'page_interface_type' => $this->getPageInterfaceType(),
            'page_name'           => $this->getPageName(),
            'content_html'        => $content_html,
        ];

        if ($this instanceof CalendarView) {
            $result['content_vars'] = \ContentReloadModel::getContentVars();
        }

        if ($action_key = \Yii::app()->request->getParam('action_key')) {
            $result['action_key'] = $action_key;
        }

        if ($content_block = \Yii::app()->request->getParam('content_blocks_different')) {
            $result['content_html_different'] = (new ContentReloadModel)->gettContentHtmlDifferenBlocks($content_block);
        }

        if (EntityModel::isSetProperties()) {
            $result['entity'] = EntityModel::getEntityProperties();
        }

        $this->renderJson($result);
    }

    /**
     * @param $view
     * @param null $data
     * @param bool $return
     * @return string|void
     */
    public function renderAuto($view, $data = null, $return = false)
    {
        if (\Yii::app()->request->isAjaxRequest) {
            return $this->renderAjax($view, $data);
        } else {
            if ($return) {
                return $this->render($view, $data, $return);
            } else {
                $this->render($view, $data, $return);
            }
        }
    }

    /**
     * @param $timestamp
     * @param bool $add_time
     * @return false|string|void
     */
    public function getDateTime($timestamp, $add_time = false)
    {
        if (empty($timestamp)) {
            return;
        }
        if ($add_time === true) {
            return date('d.m.Y H:i:s', strtotime($timestamp));
        } else {
            return date('d.m.Y', strtotime($timestamp));
        }
    }

    /**
     * @return mixed
     */
    public function getPageInterfaceType()
    {
        return $this->module->getPageInterfaceType();
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        $pn = \Module::PAGE_NAME_DEFAULT;
        if ($this instanceof ListView) {
            $pn = \Module::PAGE_NAME_LIST_VIEW;
        } else {
            if ($this instanceof ProcessView) {
                $pn = \Module::PAGE_NAME_PROCESS_VIEW;
            }
        }

        return $pn;
    }

    /**
     * установка параметров для выделения пункта меню
     * принцип: если есть pci и this_template = отмечаем меню родителя
     */
    protected function setMenuMain()
    {
        $copy_id = (isset($_GET['pci']) ? $_GET['pci'] : $this->module->extensionCopy->copy_id);
        $index = $copy_id;

        if ($this->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS) {
            if ($this->module->view_related_task) {
                $index = $this->module->extensionCopy->copy_id . TasksModule::$relate_store_postfix_params;
            }
        }

        $history_model = \History::getInstance()->getUserStorage(UsersStorageModel::TYPE_PAGE_PARAMS, $index);

        $menu_main = [
            'index'         => $copy_id,
            'this_template' => (isset($_GET['pci']) ? $history_model['this_template'] : $this->this_template),
        ];
        $this->data = array_merge($this->data, ['menu_main' => $menu_main]);
    }

    /**
     * showListView - разрешает отображение ListView
     *
     * @return bool
     */
    protected function showListView()
    {
        return true;
    }

    /**
     * check message
     * $status = false || 'access_error',
     */
    protected function returnCheckMessage($message_type, $message, $status = 'access_error', $throw_404 = true)
    {
        $validate = new Validate();

        $validate->addValidateResult($message_type, $message);

        if ($throw_404 && \Yii::app()->request->isAjaxRequest == false) {
            throw new \CHttpException(404);
        }

        $this->renderJson([
            'status'   => $status,
            'messages' => $validate->getValidateResult()
        ]);

        return false;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getPackage($key = null)
    {
        $path_to_file = './../package.json';
        $string = file_get_contents($path_to_file);
        $json_arr = json_decode($string, true);

        if ($key && array_key_exists($key, $json_arr)) {
            return $json_arr[$key];

        }

        return $json_arr;
    }

}
