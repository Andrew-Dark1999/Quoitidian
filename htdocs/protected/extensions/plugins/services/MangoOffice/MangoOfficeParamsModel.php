<?php

/**
 * Class MangoOfficeParamsModel - Общие параметры подключения (Фабричный класс)
 */


class MangoOfficeParamsModel extends PluginParamsFactory{


    // property for save
    public $domain;
    public $port;

    public $api_url;
    public $api_key;
    public $api_salt;



    public function rules(){
        return array(
            array('domain, port, api_url, api_key, api_salt', 'required'),
            array('api_url, api_key, api_salt', 'length', 'max'=>50),
            array('domain', 'length', 'max'=>150),
            array('port', 'length', 'max'=>6),
        );
    }


    public function attributeLabels(){
        return array(
            'domain' => Yii::t('base', 'Domain'),
            'port' => Yii::t('base', 'Port'),
            'api_url' => Yii::t('sip_operation_mango_office', 'Api address'),
            'api_key' => Yii::t('sip_operation_mango_office', 'Unique code'),
            'api_salt' => Yii::t('sip_operation_mango_office', 'Key for creating a signature'),
        );
    }




    public function getHtml(){
        $data = array(
            'view' => 'general-params',
            'data' => array(
                'params_model' => $this,
            )
        );

        return Yii::app()->controller->widget('ext.plugins.services.MangoOffice.elements.MangoOfficeWidget', $data, true);
    }





    public function getPublicAttributes(){
        return [
            'domain' => $this->domain,
            'port' => $this->port,
            'api_url' => $this->api_url,
            'api_key' => $this->api_key,
            'api_salt' => $this->api_salt,
        ];
    }




}
