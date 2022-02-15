<?php

/**
 * Class MangoOfficeIELog - MabgoOffice internal/external executor
 *                          Обработка полученных ответов (внешних и внутренних) от sip-сервера.
 *                          Исходя от типа действия  делается запись в модуль Звонки
 */

class MangoOfficeIEExecutor {


    private $_ie_log_model;
    private $_service_model;



    public function __construct($service_model){
        $this->_service_model = $service_model;
    }



    public function setIELogModel($ie_log_model){
        $this->_ie_log_model = $ie_log_model;
        return $this;
    }



    public function run(){
        $method_name = $this->getMethodName();

        if($method_name == false){
            return $this;
        }

        $this->{$method_name}();

        return $this;
    }




    /**
     * Возвращает название метода обработчика
     * @return null|void
     */
    public function getMethodName(){
        $type_executor = MangoOfficeApiRequest::getTypeByRequestName($this->_ie_log_model->request_name);

        $method_name = null;
        switch($type_executor){
            case MangoOfficeApiRequest::TYPE_INTERNAL:
                $method_name = $this->getMethodNameByInternal();
                break;
            case MangoOfficeApiRequest::TYPE_EXTERNAL:
                $method_name = $this->getMethodNameByExternal();
                break;
        }

        return $method_name;

    }





    public function getMethodNameByInternal(){
        $request_name = $this->_ie_log_model->request_name;
        $method_name = null;

        switch($request_name){
            case MangoOfficeApiRequest::IH_COMMANDS_CALLBACK:
                $method_name = 'ihCommands_Callback';
                break;
            case MangoOfficeApiRequest::IH_COMMANDS_CALLBACK_GROUP:
                $method_name = 'ihCommands_CallbackGroup';
                break;
            case MangoOfficeApiRequest::IH_COMMANDS_CALL_HANGUP:
                $method_name = 'ihCommands_CallHangup';
                break;
            case MangoOfficeApiRequest::IH_RESULT_CALLBACK:
                $method_name = 'ihResult_Callback';
                break;
            case MangoOfficeApiRequest::IH_RESULT_CALLBACK_GROUP:
                $method_name = 'ihResult_CallbackGroup';
                break;
            case MangoOfficeApiRequest::IH_RESULT_CALL_HANGUP:
                $method_name = 'ihResult_CallHangup';
                break;
        }

        return $method_name;
    }



    public function getMethodNameByExternal(){
        $request_name = $this->_ie_log_model->request_name;
        $method_name = null;

        switch($request_name){
            case MangoOfficeApiRequest::EH_EVENTS_CALL:
                $method_name = 'enEvents_Call';
                break;
            case MangoOfficeApiRequest::EH_EVENTS_SMS:
                $method_name = 'enEvents_Sms';
                break;
            case MangoOfficeApiRequest::EH_EVENTS_RECORDING:
                $method_name = 'enEvents_Recording';
                break;
            case MangoOfficeApiRequest::EH_EVENTS_DTMF:
                $method_name = 'enEvents_DTMF';
                break;
            case MangoOfficeApiRequest::EH_EVENTS_SUMMARY:
                $method_name = 'enEvents_Summary';
                break;
        }

        return $method_name;
    }




    /**
     * insertToModuleCalls - добавляем запись в модуль Звонки
     * @param $attributes
     */
    private function insertToModuleCalls($attributes){
        $calls_model = new CallsModel();
        $calls_model->setAttributes($attributes);
        $status = $calls_model->save();

        return $status;
    }




    //***************************************************
    // handlers - ОБРАБОТЧИКИ:
    //***************************************************



    //*********************************
    // Commands
    //*********************************

    /**
     * ihCommands_Callback  - звонок
     */
    private function ihCommands_Callback(){
        //module_title, calls_to, calls_from, calls_duration, calls_type

        $attributes = [
            'module_title' => null,
            'this_template' => null,
            'calls_to' => null,
            'calls_from' => null,
            'calls_duration' => null,
            'calls_type' => CallsModel::CALLS_TYPE_OUTGOING,
        ];

        $this->insertToModuleCalls($attributes);
    }

    /**
     * ihCommands_Callback  - звонок группе
     */
    private function ihCommands_CallbackGroup(){
    }

    /**
     * ihCommands_Callback  - завершить звонок
     */
    private function ihCommands_CallHangup(){
    }






    //*********************************
    // Result
    //*********************************
    private function ihResult_Callback(){
    }

    private function ihResult_CallbackGroup(){
    }

    private function ihResult_CallHangup(){
    }





    //*********************************
    // Events
    //*********************************
    private function enEvents_Call(){
    }

    private function enEvents_Sms(){
    }

    private function enEvents_Recording(){
    }

    private function enEvents_DTMF(){
    }

    private function enEvents_Summary(){
    }


}
