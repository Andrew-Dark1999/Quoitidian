<?php

namespace Communications\models;


class ServiceModel{


    const SERVICE_NAME_EMAIL = 'email';

    private $_error = false;
    private $_messages = array();



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    public function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        return array(
                'status' => $this->getStatus(),
                'messages' => array_merge($this->_messages, (!empty($this->_result['messages'])) ? $this->_result['messages'] : array()),
            ) + $this->_result;
    }



    public function rules(){
        return array(
            'email' => ['required', 'email'],
            'password' => ['required'],
            'text' => ['required'],
            'integer' => ['required', 'integer'],
        );
    }


}