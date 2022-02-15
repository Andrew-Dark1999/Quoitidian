<?php

class CommunicationsSourceModel{


    private $_is_init_sources = false;
    private static $_sources = array();

    private $_result = array();
    private $_messages = array();
    private $_error = false;

    private $_active_source_model;

    private $_user_id;

    public function __construct($source_name = null, $service_name = null, $user_id = null){

        if($user_id === null){
            $this->_user_id = WebUser::getUserId();
        }else{
            $this->_user_id = $user_id;
        }

        $this->initSources();

        if($source_name !== null){
            $this->loadSourceInstance($source_name);
            $this->loadServiceInstance($service_name);
        }
    }



    public function getActiveSourceModel(){
        return $this->_active_source_model;
    }



    private function isSource($source_name){
        return !empty(static::$_sources[$source_name]);
    }



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }


    public function getStatus(){
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




    private function initSources($sources_name = null){
        if($this->_is_init_sources) return;

        if($sources_name !== null && array_key_exists($sources_name, static::$_sources)) return;

        $sources = \Yii::app()->params['communications']['sources'];
        if($sources_name !== null){
            $sources = array($sources[$sources_name]);
        }

        foreach($sources as $sources_name => $source){
            if(array_key_exists($sources_name, self::$_sources)) continue;

            if($source['enable']){
                \Yii::import('application.extensions.CommunicationSources.' . $source['class']);
                static::$_sources[$sources_name] = $source['class'];
            }
        }
        if($sources_name === null){
            $this->_is_init_sources = true;
        }
    }



    public static function getSources(){
        return static::$_sources;
    }



    public function loadSourceInstance($source_name){
        if($this->isSource($source_name) == false){
            $this->addError('The source "{s}" disable or not exist', ['{s}' => $source_name]);
            return $this;
        }

        $class = static::$_sources[$source_name];
        $this->_active_source_model = new $class();

        return $this;
    }



    public function loadServiceInstance($service_name = null, $init_params = true){
        if($this->_error){
            return $this;
        }

        if($service_name === null){
            $service_params_model = new CommunicationsServiceParamsModel();
            $service_name = $service_params_model->getActiveServiceName($this->_user_id, $this->_active_source_model->getSourceName());
        }

        if($this->_active_source_model->isService($service_name) == false){
            return $this->addError('The service "{s1}" of source "{s2}" is disable or not exist', ['{s1}' => $service_name, '{s2}' => $this->_active_source_model->getSourceName()]);
        }

        $service_class_name = $this->_active_source_model->getServiceClassName($service_name);
        $service_model = new $service_class_name();

        if($init_params){
            $service_model
                ->setUserId($this->_user_id)
                ->initParams();
        }

        $this->_active_source_model->setActiveServiceModel($service_model);

        return $this;
    }




    /**
      @param $action    ACTION_SEND_MESSAGE
                        ACTION_GET_MESSAGE_HEADER
                        ACTION_GET_MESSAGE_BODY

      @param $vars      для ACTION_SEND_MESSAGE
                            MailerLettersOutboxModel::model()
                        для ACTION_GET_MESSAGE_HEADER
                            'start_date' // выбрать письма моложе даты-времени strtotime("-1 day")
                            'start_uid'  // начальный идентификатор письма
                            'answer_only'// только ответы
                        для ACTION_GET_MESSAGE_BODY
                            'uid'       // идентификатор письма
                            'mailer_box_name'  // название почтового ящика из ACTION_GET_MESSAGE_HEADER
                        для ACTION_CHECK_SERVICE_PARAMS
                            'source_name'
                            'service_name'
                            'params' array()
     */
    public function runAction($actions, $vars = null){
        if($this->_error) return $this;

        $this
            ->getActiveSourceModel()
            ->setActionVars($vars)
            ->runAction($actions);

        $this->prepereActionResult();

        return $this;
    }



    private function prepereActionResult(){
        $result = $this->_active_source_model->getResult();

        if($result['status'] == false){
            $this->_error = true;
        }

        if(!empty($result['messages'])){
            $this->_messages = array_merge($this->_messages, $result['messages']);
        }

        if(!empty($result['result'])){
            $this->_result = array_merge($this->_result, $result['result']);
        }

        foreach($result as $key => $value){
            if(in_array($key, ['status', 'messages', 'result'])) continue;
            $this->_result[$key] = $value;
        }

        return $this;
    }



    public static function getSourceList(){
        $source_list = array();
        foreach (static::$_sources as $class_name){
            $source = new $class_name();
            $source_list[] = ['source_name' => $source->getSourceName(), 'source_title' => $source->getSourceTitle(), 'services' => $source->getServiceList()];
        }
        return $source_list;
    }


    public function getServiceUserParamsList($source_name = null, $active_only = true){

        if($source_name !== null){
            $sources = (new SourceModel())->getSourceList($active_only);
            foreach($sources as $source){
                if($source_name == $source['source_name']){
                    $source_list[] = $source;
                    break;
                }else{
                }
            }
        }

        $service_params = $this->getActiveSourceModel()->getUserFormParams();

        $service_list = array();
        foreach ($source_list as $source){
            if(!empty($source['services'])){
                foreach ($source['services'] as $service){
                    if(($active_only && ($service['active']) || !$active_only)){
                        $service_list[] = $service_params[$service['service_name']];
                    }
                }
            }
        }
        return $service_list;
    }




}
