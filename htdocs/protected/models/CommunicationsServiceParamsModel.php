<?php


class CommunicationsServiceParamsModel extends ActiveRecord{


    private $_error = false;
    private $_messages = array();
    private $_result = array();

    private $_attribute_params = array();

    public $params_model = null;
    public $user_params = null;
    public $user_form_params = null;


    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function tableName(){
        return '{{communication_service_params}}';
    }



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    public function setAttributeParams($attribute_params){
        $this->_attribute_params = $attribute_params;
    }



    public function getAttributeParams(){
        return $this->_attribute_params;
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


    public function getMessages(){
        return $this->_messages;
    }

    private function addMessage($message){
        foreach ($message as $key => $value){
            $message[$key] = Yii::t('communications', $value);
        }
        $this->_messages += $message;
    }



    public function getSourceParams($user_id = null, $source_name = null){

        if($user_id === null && $source_name === null){
            return self::findAll();
        }

        if($source_name !== null){
            $params = self::findAll('user_id = :user_id AND source_name = :source_name',
                array(':user_id' => $user_id, ':source_name' => $source_name));
        }else{
            $params = self::findAll('user_id = :user_id',
                array(':user_id' => $user_id));
        }

        return $params;
    }



    public static function isSetUserParams(){
        return (new self())->getUserParams();
    }



    public function getUserParams($user_id = null, $source_name = null){
        if($user_id === null){
            $user_id = WebUser::getUserId();
        }

        if($source_name === null){
            $source_name = 'email';
        }

        $params = self::find('user_id = :user_id AND source_name = :source_name',
            array(':user_id' => $user_id, ':source_name' => $source_name));

        if(empty($params)){
            return false;
        } else {
            $array = get_object_vars(json_decode($params->params));
            \Yii::import('ext.PasswordEncoder',true);
            $array['user_password'] = empty($array['user_password'])? null : \PasswordEncoder::decode($array['user_password']);
            $array['source_name'] = $params->source_name;
            $array['service_name'] = $params->service_name;
            return $this->user_params = $array;
        }
    }



    public function getActiveServiceName($user_id, $source_name){
        $params = self::find('user_id = :user_id AND source_name = :source_name',
            array(':user_id' => $user_id, ':source_name' => $source_name));
        if (!empty($params)) {
            return $params->service_name;
        }else{
            return false;
        }
    }



    public function getActiveServiceId($source_name, $user_id){
        $params = self::find('user_id = :user_id AND source_name = :source_name',
            array(':user_id' => $user_id, ':source_name' => $source_name));
        if($params !== null){
            return $params->id;
        }else{
            return false;
        }
    }



    public function getUserParamsModel($source_name = null, $user_id = null){

        if($user_id === null){
            $user_id = WebUser::getUserId();
        }

        if($source_name === null){
            $source_name = 'email';
        }

        $params_model = self::find('user_id = :user_id AND source_name = :source_name',
            array(':user_id' => $user_id, ':source_name' => $source_name));

        if(empty($params_model)){
            return false;
        }else{
            return $params_model;
        }
    }



    public function getParamsModel($source_name){
        $this->params_model = $this->getUserParamsModel($source_name);
        $this->user_form_params = (new CommunicationsSourceModel($source_name))->getActiveSourceModel()->getUserFormParams();
        $this->getUserParams(WebUser::getUserId(), \Communications\models\ServiceModel::SERVICE_NAME_EMAIL);
        return $this;
    }



    public function saveParams($source_name = null){

        if(!empty($this->_attribute_params)){

            $model = $this->getUserParamsModel();
            if(!$model){
                $model = new CommunicationsServiceParamsModel();
            }

            $model->user_id = WebUser::getUserId();
            $model->source_name = $source_name;
            $model->service_name = $this->_attribute_params['service_name'];
            $model->signature = $this->_attribute_params['signature'];
            $model->params = $this->_attribute_params['params'];

            $model->save();
            return $this;

        }else{
            return $this->addError('Params is empty', []);
        }
    }



    public function validateEmailParams(){

        $params = $this->_attribute_params;
        $result = true;

        if(empty($params)){
            $this->addError('Settings is empty', []);
            return false;
        }

        if(empty($params['service_name'])){
            $this->addError('Undefined service ID', []);
        }

        foreach($this->user_form_params[$params['service_name']]['user_params'] as $u_param){
            foreach($params['list'] as $param_name => $param_value){
                if($u_param['name'] == $param_name){
                    if ($u_param['validate_type'] == 'email') {
                        preg_match("/[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}/", $param_value, $email);
                        if (empty($email)) {
                            $this->addMessage([$param_name => 'Field must have email address and can`t be empty']);
                            $result = false;
                        }
                    }
                    if (($u_param['validate_type'] == 'password') && (empty($param_value))) {
                        $this->addMessage([$param_name => 'Password field can`t be empty']);
                        $result = false;
                    }

                    if (($u_param['validate_type'] == 'imap_port') && (empty($param_value) || ($param_value != 993))){
                        $this->addMessage([$param_name => 'Field must have value 993 and can`t be empty']);
                        $result = false;
                    }
                    if (($u_param['validate_type'] == 'smtp_port') && (empty($param_value) || (!(in_array($param_value, [465, 587, 25, 2525]))))){
                        $this->addMessage([$param_name => 'Field must have value 465, 587, 25 or 2525 and can`t be empty']);
                        $result = false;
                    }

                    if (($u_param['validate_type'] == 'text') && (empty($param_value))) {
                        $this->addMessage([$param_name => 'Field must have value and can`t be empty']);
                        $result = false;
                    }
                    if (($u_param['validate_type'] == 'ssl') && ((empty($param_value)) || ((strtoupper($param_value) != 'SSL') && (strtoupper($param_value) != 'TLS')))){
                        $this->addMessage([$param_name => 'Field must have value "SSL" or "TLS" and can`t be empty']);
                        $result = false;
                    }
                }
            }
        }

        if($result === false) {
            $result['status'] = 'error';
        } else {

            $vars = array(
                'source_name' => $params['source_name'],
                'service_name' => $params['service_name'],
                'params' => $params['list'],
            );

            $result = (new CommunicationsSourceModel($params['source_name'], $params['service_name']))
                                ->runAction(SourceFactory::ACTION_CHECK_SERVICE_PARAMS, $vars)
                                ->getResult();

            if($result['status'] === false){
                $messages = '';
                foreach($result['messages'] as $message){
                    if(is_string($message)) {
                        $messages .= $message . '<br>';
                    }
                }
                $messages = rtrim($messages, '<br>');
                $this->addMessage(['messages' => $messages]);
                $result['status'] = 'error_email_connect';
            }
        }
        return $result;
    }



    public function prepareParams(){
        $array = array();
        foreach ($this->_attribute_params['list'] as $param_name => $param_value){
            $array += array($param_name => $param_value);
        }

        \Yii::import('ext.PasswordEncoder',true);
        $array['user_password']=\PasswordEncoder::encode($array['user_password']);
        $this->_attribute_params['params'] = json_encode($array);
        return $this;
    }




    public function getParamsJsonDecode(){
        if($this->params == false){
            return;
        }

        return json_decode($this->params, true);
    }



    
    private static $_instance_list;
    
    private function loadInstanceList(){
        if(static::$_instance_list === null){
            static::$_instance_list = static::model()->findAll();
            $this->prepareInstanceList();
        }
    }

    
    private function prepareInstanceList(){
        if(static::$_instance_list == false){
            return;
        }

        foreach(static::$_instance_list as $instance){
            $instance->params = $instance->getParamsJsonDecode();
        }
    }





    public function findByUserLogin($user_login){
        $this->loadInstanceList();

        if(static::$_instance_list == false){
            return;
        }

        foreach(static::$_instance_list as $instance){
            if($user_login == $instance->params['user_login']){
                return $instance;
            }
        }
    }





    public function findAllByUserLogin($user_login){
        $this->loadInstanceList();

        if(static::$_instance_list == false){
            return;
        }

        $result = array();
        foreach(static::$_instance_list as $instance){
            if($user_login == $instance->params['user_login']){
                $result[] = $instance;
            }
        }

        return $result;
    }



    

}
