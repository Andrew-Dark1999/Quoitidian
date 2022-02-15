<?php


/**
 * Class PluginServiceAbstractFactory - абстрактраный класс для главного класса-сервиса
 */


abstract class PluginServiceAbstractFactory implements PluginServiceAbstractInterface {

    private $_active = false;

    protected $_source_model;
    protected $_params_model;
    protected $_user_params_model;


    public function __construct($source_model){
        $this->setSourceModel($source_model);

        $this->init();
    }

    protected function init(){
        $this->initActive();
        $this->initPropeties();
        $this->initParamsModel();
    }



    protected function initActive(){
        $pp_model = \PluginParamsModel::model()
            ->scopeSourceName($this->_source_model->getName())
            ->scopeServiceName($this->getName())
            ->find();

        if($pp_model == false){
            return;
        }

        // active
        $this->setActive((bool)$pp_model->active);
    }



    protected function initPropeties(){
        $params = $this->getParams();
        if($params == false){
            return;
        }

        foreach($params as $attribute_name => $value){
            if(property_exists($this, $attribute_name)){
                $this->{$attribute_name} = $value;
            }
        }
    }


    protected function initParamsModel(){
        $class_name = $this->getParamsModelClassName();
        $model = new $class_name();

        if($model instanceof PluginServiceParamsInterface){
            $model
                ->setServiceModel($this)
                ->initAttributes();

            $this->_params_model = $model;
        }
    }



    protected function initUserParamsModel(){
        $class_name = $this->getUserParamsModelClassName();
        $model = new $class_name();
        $model->setServiceModel($this);

        if($model instanceof PluginServiceUserParamsFactory){
            $model = $model->findByUsersId();
            $this->_user_params_model = $model;
        }
    }


    public function setActive($active){
        $this->_active = $active;
        return $this;
    }


    public function getActive(){
        return $this->_active;
    }


    protected function setSourceModel($source_model){
        $this->_source_model = $source_model;
        return $this;
    }


    public function getSourceModel(){
        return $this->_source_model;
    }


    public function getParamsModel(){
        return $this->_params_model;
    }


    public function getParams(){
        $params = \Yii::app()->params['plugins']['services'];

        if(array_key_exists($this->getName(), $params)){
            return $params[$this->getName()];
        }
    }


    public function getNewUniqueKey(){
        return Helper::getUniqueKey(false);
    }






}
