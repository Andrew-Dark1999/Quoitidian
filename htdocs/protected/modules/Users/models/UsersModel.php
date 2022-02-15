<?php

/**
 * UsersModel
 * 
 * @author Alex R.
 * @copyright 2014
 */
class UsersModel extends ActiveRecord
{
    public $tableName = 'users';
    public $change_password = false;
    // хранит реальный (не шифрованый) пароль после сохранения
    public $password_real = '';
    
	public static function model($className=__CLASS__){
		return parent::model($className);
	}
    
	public function rules()
	{
		return array(
			array('sur_name, first_name, father_name, email, password', 'required', 'on'=>'insert'),
            array('password', 'required', 'on'=>'update_password'),
            array('email', 'unique', 'on'=>'insert'),
            array('email', 'required', 'on'=>'login'),
            array('password', 'authenticate', 'on'=>'login'),
            array('sur_name, first_name, email, password', 'required', 'on'=>'registration'),
            array('email', 'email','on'=>'registration'),
            array('email', 'unique', 'on'=>'registration'),            
            array('email', 'required', 'on'=>'restore'),
            array('api_active', 'in', 'range' => ["0", "1"], 'on' => 'api'),
            array('api_key, ', 'length', 'is' => 32, 'on' => 'api'),
            array('email', 'exist','attributeName'=>'email','className'=>'UsersModel','on'=>'restore','message'=>Yii::t('UsersModule.messages','Email is not registered!')),
			array('sur_name, first_name, father_name, email, password', 'length', 'max'=>255),
			array('active', 'safe'),
		);
	}

    public function authenticate(){
        if($this->hasErrors()){
            return false;
        }

        if($this->email){
            $user_model = self::model()->find('email = "'.$this->email.'"');
            if($user_model && $user_model->active !== '1'){
                $this->addError('email', Yii::t('UsersModule.messages', 'Sorry, your account is locked') . '.</br>' . Yii::t('UsersModule.messages', 'To unlock, you need to contact the system administrator'));
                $this->addError('password', '');
                return;
            }
        }

        $identity = new UserIdentity($this->email, $this->password);
        if($identity->authenticate()){
            Yii::app()->user->login($identity);
            return true;
        } else {
            switch($identity->errorCode){
                case $identity::ERROR_USERNAME_INVALID :
                case $identity::ERROR_PASSWORD_INVALID :
                case $identity::ERROR_UNKNOWN_IDENTITY :
                    $this->addError('email', Yii::t('UsersModule.messages', 'Username and/or password are incorrect'));  
                    $this->addError('password', '');
            }
        }

        return false;
    }

	public function relations(){
    	return array(
    	    'usersRoles' => array(self::HAS_MANY, 'UsersRolesModel', 'users_id'),
            'usersStorageEmails' => array(self::MANY_MANY, 'EmailsModel', '{{users_storage_email}}(users_id, email_id)'),
            'emails' => array(self::HAS_ONE, 'EmailsModel', array('email_id' => 'email_id')),
            'userRestorePassword' => array(self::HAS_MANY, 'UsersRestoreModel', 'users_id'),
        );
	}


    public function scopes(){
        return array(
            "activeUsers" => array(
                "condition" => "active='1'",
            ),
        );
    }


    public function scopeAuthorizeUser($users_id = null){
	    if($users_id === null){
            $users_id = WebUser::getUserId();
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition('t.users_id=:users_id');
        $criteria->params = array(':users_id' => $users_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;

    }



    public function setChangePassword($change_password){
        $this->change_password = $change_password;

        return $this;
    }


    public function setMyAttributes($attributes){
         foreach($attributes as $key => $value){
             $this->{$key} = $value;
         }
    }

    /**
     *  условие отбора данных шаблoна
     */
    public function scopesThisTemplate($this_template = null){
        if(!isset($this->this_template)){
            return $this;
        }

        if($this_template === null){
            return $this;
        }

        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'this_template=:this_template',
            'params' => array(':this_template' => (string)(integer)$this_template),
        ));

        return $this;
    }

    /**
     * @param $aId
     * @param bool $in
     * @return $this
     */
    public function scopesUserIdIn($aId, $in = true)
    {
        if(empty($aId)){
            return $this;
        }

        $criteria = new CDbCriteria();
        if($in) {
            $criteria->addInCondition('users_id', $aId);
        } else {
            $criteria->addNotInCondition('users_id', $aId);
        }

        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

	public function attributeLabels()
	{
		return array(
			'sur_name' => Yii::t('UsersModule.base', 'Surname'),
			'first_name' => Yii::t('UsersModule.base', 'First name'),
			'father_name' => Yii::t('UsersModule.base', 'Father name'),
			'email' => Yii::t('UsersModule.base', 'Email'),
			'password' => Yii::t('UsersModule.base', 'Password'),
			'active' => Yii::t('UsersModule.base', 'Active'),
		);
	}



    /**
     * генерируем случайный пароль
     */
    public function geterateRandomPassword($password_len = 11){
        $arr = array('a','b','c','d','e','f',  
                     'g','h','i','j','k','l',  
                     'm','n','o','p','r','s',  
                     't','u','v','x','y','z',  
                     'A','B','C','D','E','F',  
                     'G','H','I','J','K','L',  
                     'M','N','O','P','R','S',  
                     'T','U','V','X','Y','Z',  
                     '1','2','3','4','5','6',  
                     '7','8','9','0','.','+','-');  
        $pass = "";  
        for($i = 0; $i < $password_len; $i++)  {  
            $index = rand(0, count($arr) - 1);  
            $pass .= $arr[$index];  
        }  
        $this->password = $pass;
        
        return $this;         
    }
    
    

    public function beforeSave(){
        //password
        $this->active = 1;
        if($this->change_password) {
            $this->password_real = $this->password;
            $this->password = CPasswordHelper::hashPassword($this->password);
        }
            
        return true;
    }


    public function afterSave(){
        if($this->getIsNewRecord()){
            ProfileNotificationSettingModel::saveDefaultSetting($this->getPrimaryKey());
        }
    }


    protected function beforeValidate(){
        if($this->isNewRecord)
            $this->date_create = new CDbExpression('now()');
        else
            $this->date_edit = new CDbExpression('now()');
            
        return true;
    }

    public function getFullName($add_father_name = false){
        if($add_father_name)
            $full_name = implode(' ', array($this->sur_name, $this->first_name, $this->father_name));
        else
            $full_name = implode(' ', array($this->sur_name, $this->first_name));

        return $full_name;

    }


    /**
     * getUserModel
     */
    public static function getUserModel($users_id = null){
        if($users_id === null){
            $users_id = WebUser::getUserId();
        }

        return self::model()->findByPk($users_id);
    }


    /**
     * getRoles
     */
    public function getUsersRoles(){
        return array_keys(CHtml::listData($this->usersRoles, 'roles_id', ''));
    }


    public function setLogout($status){
        $this->logout = (string)(int)$status;
        $this->save();
    }


    public function getLogout(){
        return $this->logout;
    }





    /**
     * Emails
     */
    public static function hasStorageEmailId($email_id, $users_id = null){
        $email_model = static::model()->scopeAuthorizeUser($users_id)->with(
                                array(
                                    'usersStorageEmails' => array(
                                        'condition' => 'usersStorageEmails.email_id=:email_id',
                                        'params' => array(':email_id' => $email_id)
                                    )
                                ))
                                ->find();

        if(!empty($email_model->usersStorageEmails)){
            return true;
        }

        return false;
    }


    public static function findByEmail($email){
        $users_model = static::model()->find(array(
                'condition' => 'email=:email',
                'params' => array(':email' => $email)
            ));

        return $users_model;
    }



}
