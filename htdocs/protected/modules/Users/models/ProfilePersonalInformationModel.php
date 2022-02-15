<?php

class ProfilePersonalInformationModel extends CFormModel
{

    public $sur_name;

    public $first_name;

    public $email;

    public $time_zones_id;

    public $language;

    public $password;

    public $password_confirm;

    public $activity_editor;

    public $background;

    public $background_file_title;

    public function rules()
    {
        return [
            ['sur_name, first_name, email', 'required'],
            ['password', 'compare', 'compareAttribute' => 'password_confirm', 'allowEmpty' => true],
            ['activity_editor', 'length', 'max' => 30],
            ['background', 'numerical'],
            ['background', 'validateBackgroundFormat'],
            //array('email', 'email'),
            ['language', 'length', 'max' => 3],
            ['time_zones_id', 'length', 'max' => 30],
            ['sur_name, first_name, email, password, password_confirm', 'length', 'max' => 255],
        ];
    }

    public function password_confirm($attribute, $params)
    {
        if ((string)$this->password == '' && (string)$this->password_confirm == '') {
            return true;
        }

        if ((string)$this->password != (string)$this->password_confirm) {
            $this->addError($attribute, Yii::t('UsersModule.messages', 'Passwords do not match'));
        }
    }

    public function validateBackgroundFormat($attribute, $params)
    {

    }

    public function attributeLabels()
    {
        return [
            'sur_name'         => Yii::t('UsersModule.base', 'Surname'),
            'first_name'       => Yii::t('UsersModule.base', 'First name'),
            'time_zones_id'    => Yii::t('UsersModule.base', 'Time zone'),
            'language'         => Yii::t('UsersModule.base', 'Language'),
            'password'         => Yii::t('UsersModule.base', 'Password'),
            'password_confirm' => Yii::t('UsersModule.base', 'Confirm password'),
            'email'            => Yii::t('UsersModule.base', 'Email'),
            'activity_editor'  => Yii::t('UsersModule.base', 'Type of editor'),
            'background'       => Yii::t('UsersModule.base', 'Background'),
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        if ($this->background) {
            $this->background_file_title = UsersParamsModel::model()->scopeActiveUser()->find()->getBackgroundFileTitle($this->background);
        }
    }

    public function save($users_id)
    {
        $user_model = UsersModel::model()->findByPk($users_id);
        $user_model->setAttributes([
            'sur_name'   => $this->sur_name,
            'first_name' => $this->first_name,
            'email'      => $this->email,
        ]);
        if ($this->password !== null && (string)$this->password != '') {
            $user_model->change_password = true;
            $user_model->password = $this->password;
        }

        $status = $user_model->save();

        if ($status) {
            $user_params_model = UsersParamsModel::model()->scopeUsersId($users_id)->find();
            if (empty($user_params_model)) {
                $user_params_model = new UsersParamsModel;
                $user_params_model->users_id = $users_id;
            }
            $user_params_model->language = $this->language;
            $user_params_model->time_zones_id = $this->time_zones_id;
            $user_params_model->activity_editor = $this->activity_editor;
            $user_params_model->background = $this->background;
            $user_params_model->save();

            // перегружаем данные пользователя
            $identity = new UserIdentity($this->email, $this->password);
            $identity->setUserState($user_model);
        }

        return $status;
    }

}
