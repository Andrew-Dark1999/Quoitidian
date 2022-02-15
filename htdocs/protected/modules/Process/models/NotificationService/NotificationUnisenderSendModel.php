<?php
/**
 * NotificationUnisenderSendModel - модель отсылки уведомлений
 */

namespace Process\models\NotificationService;


use Process\models\OperationDataRecordModel;
use Process\models\ProcessModel;


class NotificationUnisenderSendModel {

    const TYPE_MESSAGE_EMAIL    = 'tm_email';
    const TYPE_MESSAGE_SMS      = 'tm_sms';


    private $_be_error;

    private $_notification_unisender_model;

    private $_service_model;

    private $_schema = array();

    private $_post_data = array();

    private $_result;




    public function __construct($notification_unisender_model){
        $this->_notification_unisender_model = $notification_unisender_model;

        $this->initUnisender();
    }



    public function initUnisender(){
        $source_name = null;
        switch($this->_notification_unisender_model->getOperationNfModel()->getActiveTypeMessage()){
            case NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL :
                $source_name = \PluginSources::SOURCE_EMAIL;
                break;
            case NotificationUnisenderSendModel::TYPE_MESSAGE_SMS :
                $source_name = \PluginSources::SOURCE_SMS;
                break;
        }

        if($source_name == false){
            return;
        }

        $service_model = (new \PluginsModel())->getServiceModel($source_name, 'unisender');

        if($service_model == false){
            return;
        }

        $this->_service_model = $service_model;
    }




    private function getSendModel(){
        if($this->_service_model){
            return;
        }

        return $this->_service_model->getSendModel();
    }



    public function getResult(){
        $status = ($this->_be_error ? false : true);
        if($status){
            $result = NotificationUnisenderModel::getParsedSendResult($this->_result);
            $status = $result['status'];
        }

        return array(
            'status' => $status,
        );
    }





    /**
     * run
     */
    public function run(){
        // check
        $schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_notification_unisender_model->getOperationNfModel()->getOperationsModel()->getSchema(), array('only_first' => true, 'type' => \Process\models\OperationNotificationFactoryModel::ELEMENT_SERVICE_VARS));
        if(empty($schema['value'])) return $this;
        $schema = $schema['value'];

        // подготовка схемы
        $this->_notification_unisender_model->prepareAll($schema);
        $this->_schema = $this->_notification_unisender_model->getSchema();

        // подготовка данных для отсылки
        $this->preparePostData();

        $this->check($schema);
        $this->send();

        return $this;
    }





    /**
     * prepareDeliveryData - подготовка данных для отправки
     */
    private function preparePostData(){
        if($this->_be_error) return;

        switch($this->_notification_unisender_model->getOperationNfModel()->getActiveTypeMessage()){
            case NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL:
                $template = $this->getTemplate();
                if(empty($template)) return;

                $this->_post_data = array(
                    'sender_name' => $template['sender_name'],    // Имя отправителя. Произвольная строка, не совпадающая с e-mail адресом
                    'sender_email' => $template['sender_email'], // E-mail адрес отправителя
                    'subject' => $template['subject'],           // Тема письма
                    'body' => $template['body'],                 // Текст письма в формате HTML
                    'list_id' => $template['list_id'],
                    'error_checking' => 1,
                );
                $this->appendAddresses();

                break;

            case NotificationUnisenderSendModel::TYPE_MESSAGE_SMS :
                $this->_post_data = array(
                    'phone' => $this->getPhones(),    //Телефон получателя в международном формате с кодом страны (можно опускать ведущий «+»).
                    'sender' => $this->getSenderName(),   //Отправитель – строка от 3 до 11 латинских букв или цифр с буквами.
                    'text' => $this->getBody(),     //Текст сообщения, до 1000 символов
                );
                break;
        }
    }


    /**
     * check - проверка перед отправкой
     */
    private function check(){
        if($this->_be_error) return;

        if(empty($this->_post_data)){
            $this->_be_error = true;
            return;
        }
    }



    private function send(){
        if($this->_be_error) return;

        switch($this->_notification_unisender_model->getOperationNfModel()->getActiveTypeMessage()){
            case NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL:
                $this->executeMethod(\UnisenderSend::METHOD_SEND_EMAIL, $this->_post_data);
                break;

            case NotificationUnisenderSendModel::TYPE_MESSAGE_SMS:
                $this->executeMethod(\UnisenderSend::METHOD_SEND_SMS, $this->_post_data);
                break;
        }
    }




    private function executeMethod($method_name, $params = null, $save_log = true){
        $send_model = $this->getSendModel();
        if($send_model == false){
            return;
        }

        $result = $send_model
                    ->executeMethod(\UnisenderSend::METHOD_GET_TEMPLATES, $params)
                    ->getResult();

        if($save_log){
            $this->log(
                $method_name,
                $result,
                \Process\models\ProcessModel::getInstance()->process_id,
                $this->_notification_unisender_model->getOperationNfModel()->getOperationsModel()->unique_index
            );
        }

        $this->_result = $result;
    }




    /**
     * Логирование результатов выполненния запросов на сервис
     */
    private function log($send_method, $recive_text, $process_id, $unique_index){
        if(empty(\Yii::app()->params['plugins']['services']['unisender']['log'])){
            return;
        }

        \DataModel::getInstance()->setText(
            'INSERT into {{unisender_log}} (`date_create`, `process_id`, `operation_unique_index`, `send_method`,`recive_text`)
                    VALUES (
                      "'.date('Y-m-d H:i:s').'",
                      "'.$process_id.'",
                      "'.$unique_index.'",
                      "'.$send_method.'",
                      "'.addslashes($recive_text).'")'
        )->execute();

    }



    /**
     * getAddressSendedList - список отправленных адресов
     */
    private function getAddressSendedList(){
        $result = array();

        $send_method = null;
        switch($this->_notification_unisender_model->getOperationNfModel()->getActiveTypeMessage()){
            case NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL :
                $send_method = \UnisenderSend::METHOD_SEND_EMAIL;
                break;
            case NotificationUnisenderSendModel::TYPE_MESSAGE_SMS :
                $send_method = \UnisenderSend::METHOD_SEND_SMS;
                break;
        }

        $data = \DataModel::getInstance()
                    ->setSelect('recive_text')
                    ->setFrom('{{unisender_log}}')
                    ->setWhere(
                            'process_id=:process_id AND operation_unique_index=:operation_unique_index AND send_method=:send_method',
                            array(
                                ':process_id' => \Process\models\ProcessModel::getInstance()->process_id,
                                ':operation_unique_index' => $this->_notification_unisender_model->getOperationNfModel()->getOperationsModel()->unique_index,
                                ':send_method' => $send_method,
                            )
                        )
                    ->findCol();

        if(empty($data)) return;

        foreach($data as $row){
            $res = json_decode($row, true);
            if(empty($res['result'])) continue;
            foreach($res['result'] as $val){
                if(isset($val['errors'])) continue;
                if(!empty($val['email'])){
                    $result[] = $val['email'];
                }
                /*
                if(!empty($val['sms'])){
                    $result[] = $val['sms'];
                }
                */
            }
        }

        if(!empty($result)) return $result;
    }





    /**
     * getAddressList - список адресов
     */
    private function getAddressList(){
        $result = array();

        $address = $this->_notification_unisender_model->getAddressList();

        $address_s  = $this->getAddressSendedList();
        if(empty($address_s)){
            $result = $address;
        } else if(!empty($address)){
            foreach($address as $addres){
                if(in_array($addres, $address_s)) continue;
                $result[] = $addres;
            }
        }

        return $result;
    }

    /**
     * список адресов
     */
    private function appendAddresses(){
        $address_list = $this->getAddressList();

        if(empty($address_list)) return;
        $i = 0;
        foreach($address_list as $address){
            $this->_post_data['email['.($i++).']'] = $address;
        }
    }


    /**
     * список телефонов
     */
    private function getPhones(){
        $phone_list = $this->getAddressList();

        if(empty($phone_list)) return;
        return implode(',', $phone_list);
    }


    /**
     * Имя отправителя. Произвольная строка, не совпадающая с e-mail адресом
     */
    private function getSenderName(){
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_schema, array('only_first' => true, 'type' => NotificationUnisenderModel::ELEMENT_SENDER_NAME));
        if(!empty($from_schema['value'])){
            return $from_schema['value'];
        }
    }


    /**
     * Текст письма
     */
    private function getBody(){
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_schema, array('only_first' => true, 'type' => NotificationUnisenderModel::ELEMENT_MESSAGE_TEXT));
        if(!empty($from_schema['value'])){
            return $from_schema['value'];
        }
    }


    private function getMessageTemplateId(){
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_schema, array('only_first' => true, 'type' => NotificationUnisenderModel::ELEMENT_MESSAGE_TEMPLATE));
        if(!empty($from_schema['value'])){
            return $from_schema['value'];
        }
    }






    public function getTemplates(){
        $list = array();

        $send_model = $this->getSendModel();
        if($send_model == false){
            return $list;
        }

        $result = $send_model
                    ->executeMethod(\UnisenderSend::METHOD_GET_TEMPLATES)
                    ->getResult();

        $parsed_result = NotificationUnisenderModel::getParsedSendResult($result);

        if(empty($parsed_result) || $parsed_result['status'] == false){
            return array();
        }

        $result = json_decode($result, true);

        if(!empty($result['result'])){
            foreach($result['result'] as $row){
                $list[$row['id']] = $row['title'];
            }
        }

        if(!empty($list)) return $list;
    }




    public function getTemplate(){
        $template_id = $this->getMessageTemplateId();
        if(empty($template_id)){
            return;
        }

        $send_model = $this->getSendModel();
        if($send_model == false){
            return;
        }

        $result = $send_model
                        ->executeMethod(\UnisenderSend::METHOD_GET_TEMPLATE, array('template_id' => $template_id))
                        ->getResult();


        $parsed_result = NotificationUnisenderModel::getParsedSendResult($result);

        if(empty($parsed_result) || $parsed_result['status'] == false){
            return array();
        }

        $result = json_decode($result, true);


        if(!empty($result['result'])){
            return $result['result'];
        }
    }


}
