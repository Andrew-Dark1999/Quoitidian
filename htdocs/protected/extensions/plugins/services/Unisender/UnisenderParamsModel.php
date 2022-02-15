<?php

class UnisenderParamsModel extends PluginParamsFactory {


    // property for save
    public $api_key;


    public function rules(){
        return array(
            array('api_key', 'required'),
            array('api_key', 'length', 'max'=>50),
            array('api_key', 'validateApiKey'),
        );
    }

    public function attributeLabels(){
        return array(
            'api_key' => Yii::t('base', 'Api key'),
        );
    }




    /**
     * Проверка Api ключа
     */
    public function validateApiKey($attribute, $params){
        $params_u = array(
            'api_key' => $this->{$attribute},
            'date_from'=>'2000-01-01 00:00',
            'date_to'=>'2000-01-01 00:00',
            'limit'=>1
        );

        $result = $this
                    ->_service_model
                    ->getSendModel()
                    ->executeMethod(\UnisenderSend::METHOD_GET_MESSAGES, $params_u)
                    ->getResult();

        if(empty($result)){
            $this->addError($attribute, \Yii::t('messages', 'Error connecting to the API-server'). '. ' . \Yii::t('messages', 'Api-key is not checked'));
            return false;
        } else {
            if(is_string($result)){
                $result = ['code' => $result];
            }
            if(!empty($result['code']) && $result['code'] == 'invalid_api_key'){
                $this->addError($attribute, \Yii::t('messages', 'Invalid Api-key'));
                return false;
            } else if(!empty($result['code']) && $result['code'] == 'Please use HTTPS for API requests.'){
                $this->addError($attribute, \Yii::t('messages', 'Error connecting to the API-server'));
                return false;
            }
        }

        return true;
    }




    public function getPublicAttributes(){
        return [
            'api_key' => $this->api_key,
        ];
    }



    public function getHtml(){
        $data = array(
            'view' => 'general-params',
            'data' => array(
                'params_model' => $this,
            )
        );

        return Yii::app()->controller->widget('ext.plugins.services.Unisender.elements.UnisenderWidget', $data, true);
    }




}
