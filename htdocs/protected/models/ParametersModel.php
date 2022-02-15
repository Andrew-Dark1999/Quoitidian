<?php

class ParametersModel extends FormModel
{

    public $crm_name;

    public $crm_description;

    public $admin_email;

    public $admin_password;

    public $admin_password_confirm;

    public $db_type;

    public $db_server_name;

    public $db_user;

    public $db_password;

    public $db_name;

    public $db_prefix;

    public $reg_background;

    public function rules()
    {
        $rules = [
            ['crm_name, crm_description, admin_email', 'required', 'on' => 'update, update_install'],
            ['crm_name, crm_description, admin_email, admin_password', 'required', 'on' => 'update_install, install'],
            ['crm_name, crm_description, admin_email, admin_password', 'length', 'max' => 255, 'on' => 'update, update_install, install'],
            ['admin_password', 'compare', 'compareAttribute' => 'admin_password_confirm', 'on' => 'update'],
            //array('admin_email', 'email', 'on' => 'update, update_install, install'),
        ];

        if (\ParamsModel::getValueFromModel('parameters_db_enable')) {
            $rules[] = ['db_type, db_server_name, db_user, db_name', 'required', 'on' => 'update, update_install'];
            $rules[] = ['db_type, db_server_name, db_user, db_password, db_name', 'length', 'max' => 255, 'on' => 'update, update_install'];
        }

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'crm_name'               => Yii::t('base', 'Company name'),
            'crm_description'        => Yii::t('base', 'Company description'),
            'admin_email'            => Yii::t('base', 'Administrator Email'),
            'admin_password'         => Yii::t('base', 'Password'),
            'admin_password_confirm' => Yii::t('base', 'Confirm password'),
            'db_type'                => Yii::t('base', 'Database Type'),
            'db_server_name'         => Yii::t('base', 'Server name'),
            'db_user'                => Yii::t('base', 'Username'),
            'db_password'            => Yii::t('base', 'Password'),
            'db_name'                => Yii::t('base', 'Database name'),
            'db_prefix'              => Yii::t('base', 'Database prefix'),
            'reg_background'              => Yii::t('base', 'Background'),
        ];
    }

    public function setMyAttributes($attributes)
    {
        foreach ($attributes as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        return $this;
    }

    /**
     * сохраняем параметры
     */
    public function saveParams()
    {
        if (\ParamsModel::getValueFromModel('parameters_db_enable')) {
            $dsn = $this->db_type . ":host=" . $this->db_server_name . ";dbname=" . $this->db_name;
            $config_array = "<?php
            return array(
            	\"components\" => array(
            		\"db\" => array(
                        'connectionString' => '" . $dsn . "',
                        'emulatePrepare' => true,
                        'username' => '" . $this->db_user . "',
                        'password' => '" . $this->db_password . "',
                        'tablePrefix' => '" . $this->db_prefix . "',
                        'charset' => 'utf8',
            		),
                ),
            );
        ";
            file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "local.php", print_r($config_array, true));

            Yii::app()->db;
            Yii::app()->db->setActive(false);
            Yii::app()->db->connectionString = $dsn;
            Yii::app()->db->username = $this->db_user;
            Yii::app()->db->password = $this->db_password;
            Yii::app()->db->tablePrefix = $this->db_prefix;
            Yii::app()->db->setActive(true);
        }

        \DataModel::getInstance()->Update('{{params}}', ['value' => $this->crm_name], 'title=:title', [':title' => 'crm_name']);
        \DataModel::getInstance()->Update('{{params}}', ['value' => $this->crm_description], 'title=:title', [':title' => 'crm_description']);

        $this->saveBackground();
        $this->saveAdinistratorUser();

        return true;
    }

    private function saveBackground()
    {
        if($this->reg_background){
            $uploadModel = UploadsModel::model()->findByPk($this->reg_background);
            if($uploadModel){
                $uploadModel->status = 'asserted';
                $uploadModel->save();
            }
        } else {
            $reg_background = ParamsModel::model()->titleName('reg_background')->find()->getValue();
            if(!$reg_background){
                return;
            }
            UploadsModel::model()->findByPk($reg_background)->delete();
        }

        \DataModel::getInstance()->Update('{{params}}', ['value' => $this->reg_background], 'title=:title', [':title' => 'reg_background']);
    }

    private function saveAdinistratorUser()
    {
        ExtensionModel::model()->refreshMetaData();
        ExtensionCopyModel::model()->refreshMetaData();
        ExtensionCopyModel::model()->find(ExtensionCopyModel::MODULE_USERS)->getModule();
        UsersModel::model()->refreshMetaData();
        $user_model = UsersModel::model()->findByPk(1);
        $user_model->setAttributes([
            'email' => $this->admin_email,
        ]);
        if ($this->getScenario() == 'update') {
            if ((string)$this->admin_password != '') {
                $user_model->password = $this->admin_password;
                $user_model->change_password = true;
            }
        } else {
            $user_model->password = $this->admin_password;
            $user_model->change_password = true;
        }
        $user_model->save();
    }

    public function getAdministrator()
    {
        ExtensionCopyModel::model()->find(ExtensionCopyModel::MODULE_USERS)->getModule();
        $user_model = UsersModel::model()->findByPk(1);
        $this->admin_email = $user_model->email;
    }

    /**
     * @return void|null
     */
    public function getRegBackgroundImageTitle()
    {
        if (!$this->reg_background) {
            return;
        }

        $uploadModel = UploadsModel::model()->findByPk($this->reg_background);

        return $uploadModel ? $uploadModel->file_title : null;
    }
}
