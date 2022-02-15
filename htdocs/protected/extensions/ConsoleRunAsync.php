<?php
/**
 * ConsoleRunAsync - запуск консольных скриптов асинхронно
 */



class ConsoleRunAsync{



    private $_controller_name = 'utility';
    private $_action_name;
    private $_properties = '';
    private $_async = true;
    private $_result;
    private $_config;
    private $_enabled = true;




    public function __construct(){
        $this->prepareConfig();
        $this->prepareEnabled();
    }


    public function setControllerName($controller_name){
        $this->_controller_name = $controller_name;
        return $this;
    }

    public function setActionName($action_name){
        $this->_action_name = $action_name;
        return $this;
    }

    public function setAsync($async){
        $this->_async = $async;
        return $this;
    }


    public function getResult(){
        return $this->_result;
    }


    private function prepareConfig(){
        $this->_config = \ParamsModel::getValueArrayFromModel('console_run_async');
    }


    private function prepareEnabled(){
        if($this->_config && array_key_exists('enabled', $this->_config)){
            $this->_enabled = (boolean)$this->_config['enabled'];
        }
    }





    /**
     * setCommandProperties - устновка параметров, что будут переданы в консольную комынду
     * @param $properties
     *                 если передается массив, тогда название первого ключа массива = название свойства дейсвия,
     *                 остальное - его значение
     *
     * @return $this
     */
    public function setCommandProperties($properties){
        if($properties == false){
            return $this;
        }

        if(is_array($properties) == false){
            if(is_numeric($properties)){
                $this->_properties = "--properties=" . $properties;
            } else {
                $this->_properties = "--properties='" . $properties . "'";
            }
        } else {
            $list = array();
            foreach($properties as $property_name => $value){
                if(is_numeric($value) || is_string($value)) {
                    $list[] = "--" . $property_name . "=" . $value;
                } elseif(is_array($value)){
                    $list[] = "--" . $property_name . "='" . json_encode($value) . "'";
                }
            }
            $this->_properties = implode(' ', $list);
        }

        return $this;
    }




    public function exec(){
        if($this->_enabled == false){
            return $this;
        }

        $command = $this->getCommand();

        $this->_result[] = $command;

        $this->_result[] = exec($command, $this->_result);

        return $this;
    }




    private function getCommand(){
        $sudo = '';

        if($this->_config['use_sudo']){
            $sudo = 'sudo ';
        }

        $dev = '';
        if($this->_async && $this->_config['async']){
            $dev = ' > /dev/null 2>/dev/null &';
        }

        $command =  $sudo .
                    'php ' .
                    YiiBase::app()->basePath .
                    DIRECTORY_SEPARATOR . 'yiic ' . $this->_controller_name .
                    ' ' .
                    $this->_action_name .
                    ' ' .
                    $this->_properties .
                    $dev;

        return $command;
    }


}
