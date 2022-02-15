<?php
/**
 * SourceFactory
 */



class SourceFactory {

    const ACTION_SEND_MESSAGE                       = 'send_message';
    const ACTION_GET_MESSAGE_HEADER                 = 'get_message_header';
    const ACTION_GET_MESSAGE_BODY                   = 'get_message_body';
    const ACTION_DELETE_MESSAGE                     = 'delete_message';
    const ACTION_CHECK_SERVICE_PARAMS               = 'check_service_params';
    const ACTION_RUN_FLAG                           = 'run_flag';
    const ACTION_GET_MESSAGE_ID_LIST                = 'get_message_id_list';

    protected $_source_name;
    protected $_source_title;

    protected $_active = false;

    protected static $_is_init_services = false;
    protected static $_services = array();
    protected $_user_form_params = array();

    private $_result = array();
    private $_messages = array();
    private $_error = false;


    protected $_active_service_model;

    protected $_action_vars;



    public function __construct(){
        $this->setActive();
        $this->initServices();
    }



    public function getSourceName(){
        return $this->_source_name;
    }



    public function getSourceTitle(){
        return Yii::t('communications', $this->_source_title);
    }



    public function getServices(){
        return static::$_services;
    }



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }


    protected function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        $messages = array();
        if(!empty($this->_result['messages'])){
            $messages = $this->_result['messages'];
            unset($this->_result['messages']);
        }

        return array(
            'status' => $this->getStatus(),
            'messages' => array_merge($this->_messages, $messages),
        ) + $this->_result;
    }



    private function setActive(){
        $this->_active = \Yii::app()->params['communications']['sources'][$this->_source_name]['enable'];
        return $this;
    }



    public function setActiveServiceModel($active_service_model){
        $this->_active_service_model = $active_service_model;
        return $this;
    }


    public function getActiveServiceModel(){
        return $this->_active_service_model;
    }



    public function isActive(){
        return $this->_active;
    }



    public function isService($service_name){
        return !empty(static::$_services[$service_name]);

    }


    public function getUserFormParams(){
        return $this->_user_form_params;
    }


    public function getServiceClassName($service_name){
        return static::$_services[$service_name];
    }



    public function setActionVars($action_vars){
        $this->_action_vars = $action_vars;
        return $this;
    }


    public function getActionVars(){
        return $this->_action_vars;
    }



    private function initServices($service_name = null){
        if($this->isActive() == false) return;
        if(self::$_is_init_services) return;


        if($service_name !== null && array_key_exists($service_name, static::$_services)) return;

        $services = \Yii::app()->params['communications']['sources'][$this->_source_name]['services'];
        if($service_name !== null){
            $services = array($services[$service_name]);
        }

        foreach($services as $service_name => $service){
            if($service['enable']){
                \Yii::import('application.extensions.CommunicationSources.services.' . $this->_source_name . '.'.$service['class']);
                static::$_services[$service_name] = $service['class'];
                $this->_user_form_params += array(
                    $service_name => (new $service['class']())->getUserFormParams(),
                );
            }
        }

        if($service_name === null){
            static::$_is_init_services = true;
        }
    }



    public function getServiceList(){

        $service_list = array();
        foreach (static::$_services as $class_name){
            $service = new $class_name();
            $service_list[] = ['service_name' => $service->getServiceName(), 'service_title' => $service->getServiceTitle(), 'active' => $service->getServiceActive()];
        }
        return $service_list;
    }




    private function prepereActionResult(){
        $result = $this->_active_service_model->getResult();

        if($result['status'] == false){
            $this->_error = true;
        }

        if(!empty($result['messages'])){
            $this->_messages = array_merge($this->_messages, $result['messages']);
        }

        $other_vars = array();

        foreach($result as $key => $value){
            if(in_array($key, ['status', 'messages'])) continue;
            $other_vars[$key] =$value;

        }

        if($other_vars){
            $this->_result = $other_vars;
        }

        return $this;
    }






    /************************************************
     *              ACTIONS
     ***********************************************/

    public function runAction($action){
        switch($action){
            case static::ACTION_SEND_MESSAGE :
                $method_name = 'actionSendMessage';
                break;
            case static::ACTION_GET_MESSAGE_HEADER :
                $method_name = 'actionGetMessageHeader';
                break;
            case static::ACTION_GET_MESSAGE_BODY :
                $method_name = 'actionGetMessageBody';
                break;
            case static::ACTION_DELETE_MESSAGE :
                $method_name = 'actionDeleteMessage';
                break;
            case static::ACTION_RUN_FLAG :
                $method_name = 'actionRunFlag';
                break;
            case static::ACTION_CHECK_SERVICE_PARAMS :
                $method_name = 'actionCheckServiceParams';
                break;
            case static::ACTION_GET_MESSAGE_ID_LIST:
                $method_name = 'actionGetMessageIdList';
                break;

        }

        $this->_active_service_model
                    ->setActionVars($this->getActionVars($action))
                    ->runAction($method_name);

        $this->prepereActionResult();

        return $this;
    }







}
