<?php


class Unisender extends PluginServiceAbstractFactory {

    public $api_url;
    public $log;

    private $_send_model;



    public function __construct($source_model){
        parent::__construct($source_model);

        $this->initSendModel();
    }


    public function getName(){
        return 'unisender';
    }


    public  function getTitle(){
        return 'UniSender';
    }


    public function getParamsModelClassName(){
        return 'UnisenderParamsModel';
    }


    public function initSendModel(){
        $this->_send_model = new UnisenderSend();
        $this->_send_model->setServiceModel($this);
    }


    public function getSendModel(){
        return $this->_send_model;
    }

}


