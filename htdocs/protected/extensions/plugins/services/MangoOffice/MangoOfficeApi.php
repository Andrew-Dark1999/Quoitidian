<?php

/**
 * Class MangoOfficeApi - выполнение API.
 */



class MangoOfficeApi implements PluginServiceApiInterface {




    protected $_service_model;

    private $_request_name;
    private $_request_params;

    private $_api_url;
    private $_api_key;
    private $_api_salt;

    private $_unique_key; // => command_id

    private $_error = false;

    private $_send_result;




    public function __construct($service_model){
        $this->_service_model = $service_model;
        $this->initUniqueKey();
        $this->init();
    }


    private function init(){
        $params = $this->getParams();

        if($params == false){
            $this->_error = true;
            return;
        }


        if(array_key_exists('api_url', $params)){
            $this->_api_url = $params['api_url'];
        }
        if(array_key_exists('api_key', $params)){
            $this->_api_key = $params['api_key'];
        }
        if(array_key_exists('api_salt', $params)){
            $this->_api_salt = $params['api_salt'];
        }
    }


    private function getParams(){
        return $this->_service_model->getParamsModel();
    }


    private function getUserParams(){
        return $this->_service_model->getUserParamsModel();
    }


    private function initUniqueKey(){
        $this->_unique_key = $this->_service_model->getNewUniqueKey();
    }


    public function getUniqueKey(){
        return $this->_unique_key;
    }


    public function getRequestName(){
        return $this->_request_name;
    }


    public function getRequestParams(){
        return $this->_request_params;
    }


    private function getMethodNameByRequestName(){
        switch($this->_request_name){
            case MangoOfficeApiRequest::IH_COMMANDS_CALLBACK:
                return 'CommandsCallback';
            case MangoOfficeApiRequest::IH_COMMANDS_CALLBACK_GROUP:
                return 'CommandsCallbackGroup';
            case MangoOfficeApiRequest::IH_RESULT_CALLBACK:
                return 'ResultCallback';
            case MangoOfficeApiRequest::IH_RESULT_CALLBACK_GROUP:
                return 'ResultCallbackGroup';
        }
    }


    private function getUrl(){
        $url = $this->_api_url;

        if($url == false){
            $this->_error = true;
            return;
        }

        if(substr($url, -1) == '/'){
            $url .= $this->_request_name;
        } else {
            $url .= '/' . $this->_request_name;
        }

        return $url;
    }




    public function getStatus(){
        return !$this->_error;
    }



    public function getSendResult(){
        $result = $this->_send_result;

        if($result == false){
            return;
        }

        if(is_string($result)){
            $result = json_decode($result, true);
        }

        return $result;
    }




    public function getResult(){
        $result = [
            'status' => $this->getStatus(),
        ];

        if($this->getStatus() == false){
            return $result;
        }

        $result['send_result'] = $this->getSendResult();

        return $result;
    }





    public function run($request_name, $request_params){
        $this->_request_name = $request_name;
        $this->_request_params = $request_params;

        $request_data = $this->getRequestData();

        $this->curlSend($request_data);

        return $this;
    }




    private function curlSend($request_data){
        $url = $this->getUrl();

        if($this->_error){
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        if($request_data){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
        }

        $this->_send_result = curl_exec($ch);
    }



    private function getRequestData(){
        $user_params = $this->getUserParams();
        if($user_params == false){
            $this->_error = true;
            return;
        }

        $json = $this->getRequestDataJson();

        $params = [
            'vpbx_api_key' => $this->_api_key,
            'sign' => $this->getSign($json),
            'json' => $json,
        ];

        return $params;
    }



    private function getSign($json){
        $sign = hash('sha256', $this->_api_key . $json . $this->_api_salt);

        return $sign;
    }




    private function getRequestDataJson(){
        $method_name = 'getRequestDataJson_' . $this->getMethodNameByRequestName();

        $data = $this->{$method_name}();

        return json_encode($data);
    }




    /**
     * getRequestDataJson_CommandsCallback - команда - Звонок
     */
    private function getRequestDataJson_CommandsCallback(){
        $user_params = $this->getUserParams();

        $data = [
            'command_id' => $this->_unique_key,
            'from' => [
                'extension' => $user_params['number'],
            ],
            'to_number' => $this->_request_params['to_number'],
        ];

        return $data;
    }



    /**
     * getRequestDataJson_CommandsCallbackGroup - команда - Звонок группе
     */
    private function getRequestDataJson_CommandsCallbackGroup(){
        $user_params = $this->getUserParams();

        $data = [
            'command_id' => $this->_unique_key,
            'from' => [
                'extension' => $user_params['number'],
            ],
            'to_number' => $this->_request_params['to_number'],
        ];

        return $data;
    }






}
