<?php


abstract class PluginSourceAbstractFactory implements PluginSourceAbstractInterface {


    protected $_enable = false;
    protected $_services = [];



    public function init(){
        //set params
        $params = $this->getParams();

        if($params == false){
            return $this;
        }

        $this->setEnable($params['enable']);

        //load services
        if($params['services']){
            $this->loadServices($params['services']);
        }


        return $this;
    }



    public function setEnable($enable){
        $this->_enable = $enable;
        return $this;
    }


    public function getEnable(){
        return $this->_enable;
    }


    /**
     * loadServices - Загрузка активных сервисов (только активные: enable=true)
     * @param $services
     * @return $this
     * @throws CException
     */
    private function loadServices($services){
        if($this->getEnable() == false){
            return $this;
        }

        if($services == false){
            return $this;
        }

        foreach($services as $service_name){
            $service_params = $this->getServiceParams($service_name);
            if($service_params == false || $service_params['enable'] == false){
                continue;
            }

            $class_name = $service_params['class'];
            Yii::import('application.extensions.plugins.services.' . $class_name . '.*');

            $service_model = new $class_name($this);

            if(($service_model instanceof PluginServiceAbstractInterface) == false){
                continue;
            }

            $this->addService($service_model);
        }

        return $this;
    }



    private function getParams(){
        $params = \Yii::app()->params['plugins']['sources'];

        if(array_key_exists($this->getName(), $params)){
            return $params[$this->getName()];
        }
    }


    private function getServiceParams($service_name){
        $params = \Yii::app()->params['plugins']['services'];

        if(array_key_exists($service_name, $params)){
            return $params[$service_name];
        }
    }


    public function addService(PluginServiceAbstractFactory $service_mode){
        $this->_services[$service_mode->getName()] = $service_mode;

        return $this;
    }


    public function getService($service_name){
        if($this->getEnable() == false){
            return;
        }

        if($this->issetService($service_name)== false){
            return;
        }

        return $this->_services[$service_name];
    }


    public function getActiveService(){
        if($this->getEnable() == false){
            return;
        }

        if($this->_services == false){
            return;
        }

        foreach($this->_services as $service_name => $service_mode){
            if($service_mode->getActive()){
                return $service_mode;
            }
        }
    }


    public function getServicesTitle(){
        $result = [];

        if($this->getEnable() == false){
            return $result;
        }

        if($this->_services == false){
            return $result;
        }

        foreach($this->_services as $service_model){
            $result[$service_model->getName()] = $service_model->getTitle();
        }

        return $result;
    }


    public function issetService($service_name){
        return array_key_exists($service_name, $this->_services);
    }



    public function countServices(){
        return count($this->_services);
    }




}
