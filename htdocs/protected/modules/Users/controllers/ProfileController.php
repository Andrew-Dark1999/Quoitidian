<?php

class ProfileController extends Controller
{

    private $_active_users_id = null;

    public function actionProfile()
    {
        $this->layout = '//layouts/main';

        $this->_active_users_id = WebUser::getUserId();
        if (isset($_GET['users_id'])) {
            $this->_active_users_id = $_GET['users_id'];
        }

        $user_info = ProfileModel::getUserInfo($this->_active_users_id);

        if (empty($user_info['user_model'])) {
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $user_info['read_only'] = false;
        if ($user_info['user_model']->users_id != WebUser::getUserId()) {
            $user_info['read_only'] = true;
        }

        $this->data['menu_main'] = '';
        $this->data['history_data'] = ProfileActivityModel::getInstance()->getData();
        $this->data['user_info'] = $user_info;
        $this->data['extension_copy_staff'] = ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_STAFF);
        $this->data['tab_active'] = 'activity';
        if (Yii::app()->user->hasFlash('tab_active')) {
            $this->data['tab_active'] = Yii::app()->user->getFlash('tab_active');
        }

        $this->setPersonalInfoData($this->_active_users_id);
        $this->setNotificationSettingData();

        return $this->renderAuto('profile', $this->data);
    }

    /**
     * генерит и возвращает
     */
    public function actionApiRegenerateToken()
    {
        $user_model = UsersModel::model()->findByPk(WebUser::getUserId());

        return $this->renderTextOnly(ApiKeyGenerator::generate($user_model->getAttributes()));
    }

    /**
     * формирования массива данных о персональной информации
     */
    private function setPersonalInfoData($users_id)
    {
        $this->data['personal_info_model'] = $this->data['user_info']['user_model'];

        $this->data['personal_info_data'] = $this->data['user_info']['user_model']->getAttributes();
        $this->data['personal_info_data']['password'] = null;
        $this->data['personal_info_data']['password_confirm'] = null;
        $this->data['personal_info_data']['activity_editor'] = null;
        $this->data['personal_info_data']['background'] = null;
        $this->data['personal_info_data']['background_file_title'] = null;

        // UsersParams
        $user_params_model = UsersParamsModel::model()->scopeUsersId($users_id)->find();
        if ($user_params_model) {
            $this->data['personal_info_data']['language'] = $user_params_model->language;
            $this->data['personal_info_data']['time_zones_id'] = $user_params_model->time_zones_id;
            $this->data['personal_info_data']['activity_editor'] = $user_params_model->activity_editor;
            $this->data['personal_info_data']['background'] = $user_params_model->background;
            $this->data['personal_info_data']['background_file_title'] = $user_params_model->getBackgroundFileTitle();
        } else {
            $this->data['personal_info_data']['language'] = ParamsModel::model()->titleName('language')->find()->getValue();
            $this->data['personal_info_data']['time_zones_id'] = ParamsModel::model()->titleName('time_zones_id')->find()->getValue();
        }
    }

    /**
     * формирования массива данных для Настройки уведомлений
     */
    private function setNotificationSettingData()
    {
        $this->data['notification_setting_model'] = ProfileNotificationSettingModel::getModel()->prepareData('get');
    }

    /**
     * сохраняем информацию из профиля:
     *  - персональная информацич
     *  - настройки уведомлений
     */
    public function actionProfileSave()
    {
        $result = ProfileModel::saveProfile($_POST);

        return $this->renderJson($result);
    }

    /**
     * обновляет информацию из профиля:
     *  - персональная информацич
     *  - настройки уведомлений
     */
    public function actionProfileHtmlRefresh()
    {
        $result = ProfileModel::refreshProfile($_POST);

        return $this->renderJson($result);
    }

    /**
     * сохранение информации из блока Контакты
     */
    public function actionPersonalContactSave()
    {
        $users_id = WebUser::getUserId();
        $user_model = UsersModel::model()->findByPk($users_id);
        $user_model->setMyAttributes($_POST['EditViewModel']);
        $status = $user_model->save();

        return $this->renderJson(['status' => $status]);
    }

    /**
     * созвращает часть Активности
     */
    public function actionActivity()
    {
        $history_data = ProfileActivityModel::getInstance()->getData();
        $result = Yii::app()->controller->widget('Users.extensions.ProfileActivity.ProfileActivity',
            [
                'history_data' => $history_data,
            ])
            ->getResult();

        $more = (Pagination::$active_page < Pagination::getInstance()->getCountPages() ? true : false);

        return $this->renderJson([
            'status'                => (!empty($result) ? true : false),
            'more'                  => $more,
            'page'                  => ($more ? Pagination::$active_page + 1 : ''),
            'date'                  => ($more ? date('Y-m-d', strtotime($history_data[count($history_data) - 1]->date_create)) : ''),
            'notification_position' => ($more ? Yii::app()->user->getFlash('notification_position') : ''),
            'html'                  => $result['html'],
            'link_actions'          => $result['link_actions'],
        ]);
    }

}
