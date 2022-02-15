<?php

class SiteController extends Controller
{


    /**
     * filter
     */
    public function filters()
    {
        return array(
            'checkAccess',
        );
    }
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch(Yii::app()->controller->action->id){
            case 'parameters':
            case 'getCrmParams':
            case 'plugins':
            case 'mailingServices':
            case 'mailingServicesRefresh':
            case 'showLetter':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }
        $filterChain->run();
    }


    public function actionIndex()
    {
        $first_module = ExtensionCopyModel::getFirstUsersModule();
        if(!empty($first_module)){
            $this->redirect('/module/listView/show/' . $first_module->copy_id);
        }

        $this->data['menu_main'] = array('index' => 'index');
        \Yii::app()->getModule('base');
        $this->render(ViewList::getView('site/index'), $this->data);
    }


    public function getPageInterfaceType()
    {
        return \Module::PAGE_IT_DEFAULT;
    }


    /**
     * Возвращает отформатированое сообщение
     * @param array $messages
     * @param boolean $translate
     * @return string (html)
     */
    public function actionHtmlMessage($messages = array(), $translate = false)
    {
        if(Yii::app()->request->isAjaxRequest){
            $messages = $_POST['messages'];
            $translate = $_POST['translate'];
        }

        $html = $this->renderPartial(ViewList::getView('dialogs/message'), array(
            'messages' => $messages,
            'translate' => $translate,
        ), true);

        if(Yii::app()->request->isAjaxRequest)
            echo $html;
        else return $html;
    }


    public function actionParameters()
    {
        // если сохраняем
        if(isset($_POST['ParametersModel'])){
            if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                $model = new ParametersModel();
                $model->getAdministrator();
                $model->setMyAttributes($_POST['ParametersModel']);
                $model->setScenario('update');
                if($model->validate()){
                    $model->saveParams();
                }
            }
        }

        $model = new ParametersModel();
        $model->getAdministrator();
        $model->crm_name = ParamsModel::model()->titleName('crm_name')->find()->getValue();
        $model->crm_description = ParamsModel::model()->titleName('crm_description')->find()->getValue();
        $model->reg_background = ParamsModel::model()->titleName('reg_background')->find()->getValue();

        $model->db_user = Yii::app()->db->username;
        $model->db_password = Yii::app()->db->password;
        $model->db_prefix = Yii::app()->db->tablePrefix;

        $con_str = str_replace(array(":", ";", "="), " ", Yii::app()->db->connectionString);
        $con_str_array = explode(" ", $con_str);
        $model->db_type = $con_str_array[0];
        $model->db_server_name = $con_str_array[2];
        $model->db_name = $con_str_array[4];


        $this->left_menu = true;
        $this->data['menu_main'] = array('index' => 'parameters');
        $this->data['model'] = $model;

        $this->renderAuto(ViewList::getView('site/parameters'), $this->data);
    }


    public function actionGetCrmParams()
    {
        $this->renderJson(
            ParamsModel::loadJsParams()
        );
    }


    public function actionMailingServices()
    {
        // если сохраняем
        if(!empty($_POST['MailingServicesModel']) && Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
            $model = new MailingServicesModel();
            $model->setMyAttributes($_POST['MailingServicesModel']);
            if($model->validate()){
                $model->saveParams();
                $this->refresh();
            }
        } else{
            $model = new MailingServicesModel();
            $model->initParams();
        }


        $this->left_menu = true;
        $this->data['menu_main'] = array('index' => 'mailing_services');
        $this->data['model'] = $model;

        $this->renderAuto(ViewList::getView('site/mailingServices'), $this->data);
    }


    public function actionMailingServicesRefresh()
    {
        if(!empty($_POST['MailingServicesModel']) && Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
            $model = new MailingServicesModel();
            $model
                ->setMyAttributes($_POST['MailingServicesModel'])
                ->prepareData();

            return $this->renderJson(array(
                    'status' => true,
                    'html' => $this->renderPartial(ViewList::getView('site/mailingServices'), array('model' => $model), true),
                )
            );

        } else{
            return $this->renderJson(array(
                    'status' => false,
                )
            );
        }
    }


    /**
     * очистка от ненужных данных: временные файли, сообщения блока Активность
     */
    public function actionClearRubbish()
    {
        //файли
        if(!empty($_POST['uploads_id'])){
            $criteria = new CDBCriteria();
            $criteria->addCondition('status="temp"');
            $criteria->addInCondition('id', $_POST['uploads_id']);
            $model = UploadsModel::model()->findAll($criteria);
            if(!empty($model)){
                foreach($model as $model_data){

                    //удаляем также запись из таблицы связи с родительским шаблоном
                    $parent_model = UploadsParentsModel::model()->findByAttributes(array('upload_id' => $model_data->id));
                    if($parent_model !== null)
                        $parent_model->delete();

                    $model_data->delete();
                }
            }
        }
        //Активность
        if(!empty($_POST['activity_messages_id'])){
            $criteria = new CDBCriteria();
            $criteria->addCondition('status="temp"');
            $criteria->addInCondition('activity_messages_id', $_POST['activity_messages_id']);
            $model = ActivityMessagesModel::model()->findAll($criteria);
            if(!empty($model)){
                foreach($model as $model_data){
                    $model_data->delete();
                }
            }
        }

        \AdditionalProccessingModel::getInstance()->clearRubbish($_POST['linked_card']);

    }


    public function actionGetContentReloadDifferentBlock()
    {
        if($content_block = \Yii::app()->request->getParam('content_blocks_different')){
            return $this->renderJson(array(
                'status' => true,
                'content_html_different' => (new ContentReloadModel)->gettContentHtmlDifferenBlocks($content_block),
            ));
        } else{
            return $this->renderJson(array(
                'status' => true,
            ));
        }
    }


    public function actionBpm()
    {
        $this->data['menu_main'] = array('index' => 'index');
        $this->render('//site/bpm', $this->data);
    }


    public function actionCacheFlush()
    {
        Yii::app()->cache->flush();
        echo 'done...';
    }


    public function actionToUser()
    {
        if(empty(\Yii::app()->params['to_user']['enabled'])){
            echo 'Функционал не активирован';
            return;
        }

        if($_GET['key'] != \Yii::app()->params['to_user']['key']){
            echo 'Неверный ключ';
            return;
        }


        $check_is_guest = false;
        if(array_key_exists('only_user_change', \Yii::app()->params['to_user']) == false || \Yii::app()->params['to_user']['only_user_change']){
            $check_is_guest = true;
        }


        if($check_is_guest && Yii::app()->user->isGuest == true){
            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
            Yii::app()->user->logout();
            return Yii::app()->request->redirect(Yii::app()->createUrl('login'));
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $user_model = UsersModel::model()->activeUsers()->findByAttributes(array('email' => $_GET['email']));

        if(empty($user_model)){
            echo 'Неверный пользователь "' . $_GET['email'] . '"';
            return;
        }

        $identity = new UserIdentity('', '');
        $identity->setId($user_model->users_id);
        $identity->setUserState($user_model);
        $identity->errorCode = UserIdentity::ERROR_NONE;

        Yii::app()->user->login($identity);

        return Yii::app()->request->redirect('/');
    }


    public function actionShowLetter()
    {
        $id = \Yii::app()->request->getParam('id', null);
        if($id == false){
            $id = \MailerLettersOutboxModel::model()->find(['select' => 'max(mailer_id) as mailer_id'])->mailer_id;
        }
        echo \MailerLettersOutboxModel::model()->findByPk($id)->letter_body;
    }


    public function actionTest()
    {

        $data = UsersModel::model()->with(array(
            'userRestorePassword'=>array(
                // записи нам не нужны
//                'select'=>false,
                // но нужно выбрать только пользователей с опубликованными записями
//                'joinType'=>'INNER JOIN',
                'condition'=>'id=1',
            ),
        ))->findByPk(1);
//        $data =UsersModel::model()->with('userRestorePassword')->findByPk(1);
        xdebug_var_dump($data);

//        xdebug_var_dump(UsersRestoreModel::model()->with('user')->findAll());
//        $data=UsersModel::model()->with('usersRoles')->findAll();
//        xdebug_var_dump($data);
    }



    /**
     *   Устанавливаем язык локализации
     */
    /*
    public function actionSetLanguage($language){
        if(History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LANGUAGE, 'all') == $language) return $this->renderJson(array('status' => false));

        if(!empty($language) && LanguageModel::model()->count(array('condition'=>'name=:name', 'params'=>array(':name'=>$language))) > 0){
            History::getInstance()->setUserStorage(UsersStorageModel::TYPE_LANGUAGE, 'all', $language);
            return $this->renderJson(array('status' => true));
        } else
            return $this->renderJson(array('status' => false));
    }
    */


    /************************************************************
     *
     *          Plugins
     *
     ************************************************************/


    public function actionPluginsShow()
    {
        $plugins_model = new PluginsModel();
        $this->left_menu = true;
        $this->data['menu_main'] = array('index' => 'plugins');
        $this->data['plugins_model'] = $plugins_model;

        $this->renderAuto(ViewList::getView('site/plugins'), $this->data);
    }


    /**
     * actionPluginsChangeService - страница Плагины. Изменение параметров блока
     */
    public function actionPluginsChange()
    {
        $validate = new Validate();

        if(\Yii::app()->request->getParam('source_name') == false || \Yii::app()->request->getParam('service_name') == false){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        $service_model = (new PluginsModel())->getServiceModel(\Yii::app()->request->getParam('source_name'), \Yii::app()->request->getParam('service_name'));

        $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        return $this->renderJson(array(
            'status' => true,
            'html' => (!empty($service_model) ? $service_model->getParamsModel()->getHtml() : ''),
        ));
    }


    /**
     * actionPluginsSave - страница Плагины. Сохранение параметров блока
     */
    public function actionPluginsSave()
    {
        $post_data = $_POST;
        if(empty($post_data['attributes'])){
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => (new Validate())->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'))->getValidateResultHtml(),
            ));
        }

        $plugins_model = new PluginsModel();


        if($plugins_model->validateAll($post_data['attributes']) == false){
            if($plugins_model->getValidate()->beMessages()){
                return $this->renderJson(array(
                    'status' => 'error',
                    'messages' => $plugins_model->getValidateResultHtml(),
                ));
            }
        } else{
            $plugins_model->saveAll($post_data['attributes']);
        }

        $this->renderJson($plugins_model->getResult());
    }


    /**
     * actionPluginsCancelBlockParams - страница Плагины. Отмена изменений параметров блока
     */
    public function actionPluginsCancel()
    {
        $plugins_model = new PluginsModel();

        $this->data['plugins_model'] = $plugins_model;

        return $this->renderJson(array(
            'status' => true,
            'html' => $this->renderPartial(ViewList::getView('site/plugins'), $this->data, true),
        ));
    }


}



