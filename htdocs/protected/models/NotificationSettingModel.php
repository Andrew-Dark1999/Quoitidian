<?php

class NotificationSettingModel extends \ActiveRecord{

    //Отправка уведомлений - список
    const ELEMENT_SN_ENABLED    = '1';
    const ELEMENT_SN_DISABLED   = '0';

    //Способ отправки - список
    const ELEMENT_SM_EMAIL      = 'email';

    //Частота отправки - список
    const ELEMENT_FS_INSTANTLY      = 'instantly';
    const ELEMENT_FS_EVERY_HOUR     = 'every_hour';
    const ELEMENT_FS_ONCE_OF_DAY    = 'once_of_day';
    const ELEMENT_FS_ONCE_OF_WEEK   = 'once_of_week';

    //Модули - список
    const ELEMENT_NM_ALL   = 'all';
    const ELEMENT_NM_COME  = 'come';


    public $setting_notification = self::ELEMENT_SN_DISABLED;   //Отправка уведомлений
    public $sending_method = self::ELEMENT_SM_EMAIL;            //Способ отправки
    public $sending_vars;                                       //Параметры отправки
    public $frequency_sending = self::ELEMENT_FS_EVERY_HOUR;    //Частота отправки
    public $notifications_modules = self::ELEMENT_NM_ALL;                //Модули

    // поля, которых нт в БД. Значения пишутся в sending_vars
    public $email_notification;
    public $notifications_module_element;

    private $_users_id;


    public $tableName = 'users_notification_setting';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules()
    {
        return array(
            array('setting_notification, sending_method, frequency_sending, notifications_modules', 'required'),
            array('setting_notification, sending_method, sending_vars, frequency_sending, notifications_modules', 'validateFields'),
            array('sending_vars', 'length', 'max' => 1000),
            array('users_id', 'safe'),
        );
    }



    public function relations(){
        return array(
            'notificationSettingModules' => array(self::HAS_MANY, 'ProfileNotificationSettingModulesModel', 'notification_setting_id'),
            'users' => array(self::HAS_ONE, 'UsersModel', array('users_id' => 'users_id')),
        );
    }


    public function validateFields($attribute, $params){
        $validate = true;
        switch($attribute){
            case $this->setting_notification:
                if(!in_array($params, array(static::ELEMENT_SN_DISABLED, static::ELEMENT_SN_ENABLED))){
                    $validate = false;
                }
                break;
            case $this->sending_method:
                if(!in_array($params, array(static::ELEMENT_SM_EMAIL))){
                    $validate = false;
                }
                break;
            case $this->sending_vars:
                if($this->sending_method == static::ELEMENT_SM_EMAIL){
                    if($params == ''){
                        $this->addError('email_notification', Yii::t('messages', '{s} cannot be blank', array('{s}' => $this->getAttributeLabel('email_notification'))));
                        return;
                    }
                }
                break;
            case $this->frequency_sending:
                if(!in_array($params, array(static::ELEMENT_FS_INSTANTLY,static::ELEMENT_FS_EVERY_HOUR,static::ELEMENT_FS_ONCE_OF_DAY,static::ELEMENT_FS_ONCE_OF_WEEK))){
                    $validate = false;
                }
                break;
            case $this->notifications_modules:
                if(!in_array($params, array(static::ELEMENT_NM_ALL, static::ELEMENT_NM_COME))){
                    $validate = false;
                }
                break;
        }

        if($validate == false){
            $this->addError($attribute, Yii::t('UsersModule.messages', 'Not defined value of the field "{s}"', array('{s}' => $this->getAttributeLabel($attribute))));
            return;
        }

        return true;
    }




    public function attributeLabels(){
        return array(
            'setting_notification' => Yii::t('UsersModule.base', 'Sending notifications'),
            'sending_method' => Yii::t('UsersModule.base', 'Sending method'),
            'frequency_sending' => Yii::t('UsersModule.base', 'Frequency of sending'),
            'notifications_modules' => Yii::t('UsersModule.base', 'Notifications modules'),

            'email_notification' => Yii::t('UsersModule.base', 'Email Notification'),
        );
    }



    public static function getModel($get_new = true, $user_id = null){
        if($user_id === null) $user_id = WebUser::getUserId();

        $model = ProfileNotificationSettingModel::model()->find('users_id=' . $user_id);
        if(empty($model) && $get_new){
            $model = new ProfileNotificationSettingModel();
        }

        if(!empty($model)){
            $model->users_id = $user_id;
        }

        return $model;
    }


    public function setMyAttributes($attr){
        foreach($attr as $key => $value){
            $this->setAttribute($key, $value);
        }

        return $this;
    }


    public function setUsersId($users_id){
        $this->_users_id = $users_id;
        return $this;
    }


    public function getSendingVarsValue($key){
        $vars = $this->sending_vars;
        if(empty($vars)) return;
        if(is_string($vars)) $vars = json_decode($vars, true);
        if(array_key_exists($key, $vars)){
            return $vars[$key];
        }
    }


    public function prepareData($action, $user_id = null){
        switch($action){
            case 'get':
                $this->setSmEmailNotificationValueForGet($user_id);
                break;
            case 'set':
                $this->setSmEmailNotificationValueForSet($user_id);
                break;
        }

        return $this;
    }



    public function getSettingNotificationList(){
        return array(
            self::ELEMENT_SN_ENABLED => Yii::t('UsersModule.base', 'Еnabled'),
            self::ELEMENT_SN_DISABLED => Yii::t('UsersModule.base', 'Disabled'),
        );
    }



    public function getSendingMethodList(){
        return array(
            self::ELEMENT_SM_EMAIL => 'Email',
        );
    }


    public function getFrequencySendingList(){
        return array(
            self::ELEMENT_FS_INSTANTLY => Yii::t('UsersModule.base', 'Instantly'),
            self::ELEMENT_FS_EVERY_HOUR => Yii::t('UsersModule.base', 'Every hour'),
            self::ELEMENT_FS_ONCE_OF_DAY => Yii::t('UsersModule.base', 'Once a day'),
            self::ELEMENT_FS_ONCE_OF_WEEK => Yii::t('UsersModule.base', 'Once a week'),

        );
    }


    public function getNotificationsModulesList(){
        return array(
            self::ELEMENT_NM_ALL => Yii::t('UsersModule.base', 'Receive in all modules'),
            self::ELEMENT_NM_COME => Yii::t('UsersModule.base', 'Receive in some models'),

        );
    }



    public function afterSave(){
        if(!empty($this->notifications_module_element)){
            ProfileNotificationSettingModulesModel::insertData($this->getPrimaryKey(), $this->notifications_module_element);
        } else {
            ProfileNotificationSettingModulesModel::deleteData($this->getPrimaryKey());
        }

        // управление расписанием
        if($this->setting_notification == self::ELEMENT_SN_ENABLED){
            HistoryNotificationDeliveryLogModel::getInstance()->update($this->frequency_sending, $this->_users_id);
        } else {
            HistoryNotificationDeliveryLogModel::getInstance()->delete($this->_users_id);
        }

        return true;
    }



    public static function saveDefaultSetting($users_id){
        $attibutes = array(
            'users_id' => $users_id,
            'setting_notification' => self::ELEMENT_SN_ENABLED,
            'sending_method' => self::ELEMENT_SM_EMAIL,
            'email_notification' => '',
            'frequency_sending' => self::ELEMENT_FS_INSTANTLY,
            'notifications_modules' => self::ELEMENT_NM_ALL,
        );

        $model = new self();
        $model
            ->setUsersId($users_id)
            ->setMyAttributes($attibutes)
            ->prepareData('set', $users_id)
            ->save();
     }


    private function setSmEmailNotificationValueForSet($users_id = null){
        if($users_id === null) $users_id = WebUser::getUserId();

        if($this->sending_method == static::ELEMENT_SM_EMAIL){
            if($this->email_notification == ''){
                $this->email_notification = UsersModel::model()->findByPk($users_id)->email;
            }
            $vars = array(
                'email_notification' => $this->email_notification,
            );
            $this->sending_vars = json_encode($vars);
        }

        if($this->setting_notification == static::ELEMENT_SN_DISABLED){
            $this->sending_vars = null;
            $this->sending_method = static::ELEMENT_SM_EMAIL;
            $this->notifications_modules = static::ELEMENT_NM_ALL;
        }

        return $this;
    }


    private function setSmEmailNotificationValueForGet($users_id){
        if($users_id === null) $users_id = WebUser::getUserId();

        if($this->sending_method != static::ELEMENT_SM_EMAIL){
            $this->email_notification = null;
        }

        $f = false;
        if(!empty($this->sending_vars)){
            $vars = json_decode($this->sending_vars, true);
            if(is_array($vars) && array_key_exists('email_notification', $vars)){
                $this->email_notification = $vars['email_notification'];
                $f = true;
            }
        }

        if($f == false){
            $this->email_notification = UsersModel::model()->findByPk($users_id)->email;
        }
        return $this;
    }




    public function getNotificationSettingModulesList(){
        $result = array();

        if($this->notifications_modules == static::ELEMENT_NM_ALL) return false;

        $modules_list = $this->notificationSettingModules();

        $extension_copy_list = ExtensionCopyModel::model()->modulesActive()->modulesUser()->findAll(array('order' => 'title asc'));

        foreach($extension_copy_list as $extension_copy){
            $result[$extension_copy->copy_id] = array(
                'copy_id' => $extension_copy->copy_id,
                'title' => $extension_copy->title,
                'checked' => false,
            );

            if(!empty($modules_list)){
                foreach($modules_list as $item){
                    if($extension_copy->copy_id == $item->copy_id){
                        $result[$extension_copy->copy_id]['checked'] = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }


}
