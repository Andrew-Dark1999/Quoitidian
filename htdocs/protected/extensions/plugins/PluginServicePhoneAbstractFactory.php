<?php


/**
 * Class PluginServiceAbstractFactory - абстрактраный класс для главного класса-сервиса Телефонии
 */


abstract class PluginServicePhoneAbstractFactory extends PluginServiceAbstractFactory implements PluginServicePhoneAbstractInterface {


    public function getUserParamsModel(){
        if($this->_user_params_model === null){
            $this->initUserParamsModel();
        }

        return $this->_user_params_model;
    }



    public function getInternalActions(){
        $class_name = $this->getInternalActionsModelClassName();
        $model = new $class_name($this);

        if($model instanceof PluginServiceInternalActionsInterface){
            return $model;
        }
    }




    public function getExternalEvents(){
        $class_name = $this->getExternalEventsModelClassName();
        $model = new $class_name($this);

        if($model instanceof PluginServiceExternalEventsInterface){
            return $model;
        }
    }



    public function getApi(){
        $class_name = $this->getApiModelClassName();
        $model = new $class_name($this);

        if($model instanceof PluginServiceApiInterface){
            return $model;
        }
    }


}
