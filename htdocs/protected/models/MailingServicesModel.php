<?php

class MailingServicesModel extends FormModel{

    const SENDING_METHOD_EMAIL = 'sm_email';


    const EMAIL_BOX_INTERNAL = 'internal';
    const EMAIL_BOX_EXTERNAL = 'external';

    public $email_box = self::EMAIL_BOX_INTERNAL;
    public $email_host;
    public $email_port = 25;
    public $email_username;
    public $email_password;




    public function rules(){
        return array(
            array('email_box', 'length', 'max'=>20),
            array('email_host', 'length', 'max'=>80),
            array('email_username, email_password', 'length', 'max'=>50),
            array('email_port', 'numerical', 'integerOnly'=>true),
        );
    }

    public function attributeLabels(){
        return array(
            'email_box' => Yii::t('base', 'Mailbox'),
            'email_host' => Yii::t('base', 'SMTP-server'),
            'email_port' => Yii::t('base', 'Port'),
            'email_username' => Yii::t('base', 'Username'),
            'email_password' => Yii::t('base', 'Password'),
        );
    }


    public function getEmailMailboxList(){
        return array(
            static::EMAIL_BOX_INTERNAL => Yii::t('base', 'Internal'),
            static::EMAIL_BOX_EXTERNAL => Yii::t('base', 'External'),
        );
    }


    public function setMyAttributes($attributes){
        foreach($attributes as $property => $value){
            if(property_exists($this, $property)) $this->{$property} = $value;
        }

        return $this;
    }


    /**
     * сохраняем параметры
     */
    public function saveParams(){
        //self::SENDING_METHOD_EMAIL
        if($this->email_box == \MailingServicesModel::EMAIL_BOX_INTERNAL){
            $value = array(
                'email_box' => $this->email_box,
            );
        } elseif($this->email_box == \MailingServicesModel::EMAIL_BOX_EXTERNAL){
            $value = array(
                'email_box' => $this->email_box,
                'email_host' => $this->email_host,
                'email_port' => $this->email_port,
                'email_username' => $this->email_username,
                'email_password' => $this->email_password,
            );
        }

        ParamsModel::InsertOrUpdateData('mailing_service_' . self::SENDING_METHOD_EMAIL, $value);

        return true;
    }



    /**
     * востанавливаем параметры
     */
    public function initParams(){
        $params = ParamsModel::getValueArrayFromModel('mailing_service_' . self::SENDING_METHOD_EMAIL);
        if(!empty($params)){
            $this->setMyAttributes($params);
        }

        return true;
    }




    public function prepareData(){

        return $this;
    }


}
