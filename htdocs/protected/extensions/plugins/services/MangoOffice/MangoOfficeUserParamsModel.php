<?php
/**
 * Class MangoOfficeUserParamsModel - Пользовательские параметры подключения
 */


class MangoOfficeUserParamsModel extends PluginServiceUserParamsFactory{

    // property for save
    public $number;
    public $sip_id;
    public $password;


    public function rules(){
        return array(
            array('number, sip_id, password', 'required'),
            array('number', 'length', 'max'=>50),
        );
    }


    public function attributeLabels(){
        return array(
            'number' => Yii::t('sip_operation_mango_office', 'Number'),
            'sip_id' => Yii::t('sip_operation_mango_office', 'SIP ID'),
            'password' => Yii::t('sip_operation_mango_office', 'Password'),
        );
    }




    public function getHtml(){
        return;
    }




    public function getPublicAttributes(){
        return [
            'number' => $this->number,
            'sip_id' => $this->sip_id,
            'password' => $this->password,
        ];
    }




}
