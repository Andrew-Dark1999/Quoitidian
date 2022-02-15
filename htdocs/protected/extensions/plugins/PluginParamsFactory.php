<?php

/**
 * Class PluginServiceUserParamsFactory - Общие параметры подключения (Фабричный класс)
 */


abstract class PluginParamsFactory extends FormModel implements PluginServiceParamsInterface{

    // db attributes
    public $id;
    public $source_name;
    public $service_name;
    public $params;
    public $active;


    protected $_service_model;


    public function setServiceModel($service_model){
        $this->_service_model = $service_model;

        return $this;
    }



    public function initAttributes(){
        $this->initDefaultPropeties();
        $this->initSavedParams();
    }



    protected function initDefaultPropeties(){
        $params = $this->_service_model->getParams();
        if($params == false || empty($params['params'])){
            return;
        }

        foreach($params['params'] as $attribute_name => $value){
            $this->setAttribute($attribute_name, $value);
        }
    }

    

    protected function initSavedParams(){
        $pp_model = \PluginParamsModel::model()
                        ->scopeSourceName($this->_service_model->getSourceModel()->getName())
                        ->scopeServiceName($this->_service_model->getName())
                        ->find();

        if($pp_model == false){
            return;
        }
        

        //PluginParamsModel
        $this->id = $pp_model->id;
        $this->source_name = $pp_model->source_name;
        $this->service_name = $pp_model->service_name;
        $this->params = $pp_model->params;
        $this->active = $pp_model->active;


        // params
        $params = $pp_model->getParams();

        foreach($params as $attribute_name => $value){
            $this->setAttribute($attribute_name, $value);
        }
    }



    public function setMyAttributes($attributes){
        if(empty($attributes)){
            return $this;
        }

        foreach($attributes as $property => $value){
            if(property_exists($this, $property)) $this->{$property} = $value;
        }

        return $this;
    }



    public function setAttribute($attribute_name, $value){
        $this->setAttributes([$attribute_name => $value]);

        return $this;
    }






    public function save(){
        $attributes = [
            'source_name' => $this->_service_model->getSourceModel()->getName(),
            'service_name' => $this->_service_model->getName(),
            'params' => $this->getPublicAttributes(),
            'active' => '1',
        ];

        \PluginParamsModel::model()->updateAll(['active' => '0'], 'source_name=:source_name', [':source_name' => $this->_service_model->getSourceModel()->getName()]);

        $pp_model = \PluginParamsModel::model()
                        ->scopeSourceName($this->_service_model->getSourceModel()->getName())
                        ->scopeServiceName($this->_service_model->getName())
                        ->find();

        if($pp_model == false){
            $pp_model = new \PluginParamsModel();
        }

        $pp_model->setAttributes($attributes);
        $pp_model->save();
    }




}
