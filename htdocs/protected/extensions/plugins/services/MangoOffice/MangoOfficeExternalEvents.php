<?php


class MangoOfficeExternalEvents implements PluginServiceExternalEventsInterface{

    private $_service_model;
    private $_params;
    private $_error = false;

    private $_result = [];



    public function __construct($service_model){
        $this->_service_model = $service_model;
    }


    public function setParams($params){
        $this->_params = $params;
        return $this;
    }




}
