<?php

/**
 * Class MangoOfficeInternalActions -   обработка внутренних действий пользователя:
 *                                      инициализация звонка(ов), завершение звонков и др.
 *
 *
 * Сценарий:
 *  - опеределение названия действия, установка папраметров
 *  - выполнения действия (API для sip-сервера) - возврат результата
 *  - обработка результата (afterApi()):
 *      - сохранение лога (class saveIELogModel)
 *      - проверка и обработка сообщения, опеределение статуса сообщения (class MangoOfficeApiMessages)
 *      - обработка результат Исходящего действия внутреннего обработчика (class MangoOfficeIEExecutor)
 */

class MangoOfficeInternalActions implements PluginServiceInternalActionsInterface{


    private $_service_model;
    private $_params;
    private $_error = false;

    private $_result = [];



    public function __construct($service_model){
        $this->_service_model = $service_model;
    }


    public function setParams($params){
        $this->_params = $params;
        return $this;
    }


    private function getStatus(){
        return !$this->_error;
    }


    public function getResult(){
        $result = [
            'status' => $this->getStatus(),
        ];

        $result+= $this->_result;

        return $result;
    }


    private function addResult($key, $value){
        $this->_result[$key] = $value;
    }




    private function setMessage($message){
        if($message == false){
            return $this;
        }

        $this->addResult('message', $message);

        return $this;
    }



    private function setMessageError($message){
        $this->_error = true;
        $this->setMessage('message', $message);

        return $this;
    }




    private function getActionList(){
        return [
            PluginServiceInternalActions::CALL,
            PluginServiceInternalActions::HANGUP,
        ];
    }



    private function hasAction($action_name){
        return (bool)in_array($action_name, $this->getActionList());
    }


    /**
     * run - Выполняет действие
     * @param $action_name
     * @return $this|MangoOfficeInternalActions
     */
    public function run($action_name){
        if($this->hasAction($action_name) == false){
            return $this->setMessageError('Запрашиваемое действие "{s}" не найдено', ['{s}' => $action_name]);
        }

        $method_name = 'action' . $action_name;
        $this->{$method_name}();

        return $this;
    }




    /**
     * calls - сделать звонок
     */
    public function actionCall(){
        if($this->_error){
            return;
        }

        $request_data = [
            'to_number' => $this->_params['to_number'],
        ];


        $api_model = $this->_service_model->getApi();
        $api_model->run(MangoOfficeApiRequest::IH_COMMANDS_CALLBACK, $request_data);

        $this->afterApi($api_model);

        return $api_model;
    }



    /**
     * hangup = повесить трубку
     */
    public function actionHangup(){
        if($this->_error){
            return $this;
        }


    }


    /**
     * afterApi - вызывается после выполнения API запроса
     * @return $this
     */
    private function afterApi($api_model){
        if($api_model->getStatus() == false){
            return $this->setMessageError('Исполнение действия "{s}" завершено с ошибкой', ['{s}' => $api_model->getRequestName()]);
        }

        // сохранение лога
        $ie_log_model = $this->saveIELogModel($api_model);

        // Проверка и обработка сообщения, опеределение статуса сообщения
        $api_messages_model = new MangoOfficeApiMessages();
        $api_messages_model
            ->setApiResult($api_model->getSendResult());

        // Если результат API - success, т.е. "завершено успешно"
        if($api_messages_model->isSuccess()){
            // Обработка результат Исходящего действия внутреннего обработчика (internal handler)
            $this->runIEExecutor($ie_log_model);
            $this->setMessage($api_messages_model->getMessage());
        } else {
            $this->setMessageError($api_messages_model->getMessage());
        }

        return $this;
    }




    /**
     * saveIELogModel - сохранение ответа от sip-сервера в БД
     * @param $api_model
     * @return PluginIELogModel
     */
    private function saveIELogModel($api_model){
        $attributes = [
            'plugin_user_params_id' => $this->_service_model->getUserParamsModel()->id,
            'request_name' => $api_model->getRequestName(),
            'unique_key' => $api_model->getUniqueKey(),
            'external_params' => $api_model->getSendResult(),
        ];

        $ie_log_model = new \PluginIELogModel();
        $ie_log_model->setAttributes($attributes);
        $ie_log_model->save();

        return $ie_log_model;
    }



    /**
     * saveIELogModel - обработка ответа от sip-сервера
     * @param $api_model
     * @return PluginIELogModel
     */
    private function runIEExecutor($ie_log_model){
        if($ie_log_model == false){
            return $this;
        }

        (new MangoOfficeIEExecutor($this))
            ->setIELogModel($ie_log_model)
            ->run();

        return $this;
    }









}
