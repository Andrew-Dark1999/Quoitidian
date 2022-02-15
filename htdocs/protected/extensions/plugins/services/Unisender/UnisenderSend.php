<?php
/**
 * UnisenderSend - модель отсылки уведомлений
 */


final class UnisenderSend{

    const METHOD_SEND_SMS           = 'sendSms';
    const METHOD_SEND_EMAIL         = 'sendEmail';

    const METHOD_CREATE_LIST        = 'createList';
    const METHOD_DELETE_LIST        = 'deleteList';
    const METHOD_GET_LISTS          = 'getLists';
    const METHOD_GET_TEMPLATE       = 'getTemplate';
    const METHOD_GET_TEMPLATES      = 'getTemplates';
    const METHOD_CREATE_CAMPAING    = 'createCampaign';
    const METHOD_GET_MESSAGES       = 'getMessages';


    private $_service_model;

    private $_result;


    private $_languages = array(
        'en' => 'en',
        'ru' => 'ru',
        'it' => 'it',
    );


    public function setServiceModel($service_model){
        $this->_service_model = $service_model;
        return $this;
    }



    /**
     * setSendResult - уведомления отправки
     */
    private function setResult($result, $json_decode = true){
        if($result == false){
            return;
        }

        $r = $result;

        if($json_decode){
            $r = json_decode($result, true);

            if(!is_array($r)){
                $r = $result;
            }
        }

        $this->_result = $r;

        return $this;
    }



    public function getResult(){
        return $this->_result;
    }



    /**
     * Подготавливает и отправляет действие на сервер  unisender
     */
    public function executeMethod($method_name, $params = null){
        $result = null;

        $url = $this->getUrl(array('method' => $method_name, 'format' => 'json',));

        switch($method_name){
            case self::METHOD_CREATE_LIST:
                $post_data = array(
                    'api_key' => $this->getApiKey(),
                    'title' => 'List_' . date('Ymd_his'),
                );
                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_DELETE_LIST:
                $post_data = array(
                    'api_key' => $this->getApiKey(),
                    'list_id' => $params['list_id'],
                );
                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_GET_TEMPLATE:
                $post_data = array(
                    'api_key' => $this->getApiKey(),
                    'template_id' => $params['template_id'],
                );
                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_GET_LISTS:
            case self::METHOD_GET_TEMPLATES:
                $post_data = array(
                    'api_key' => $this->getApiKey(),
                );
                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_SEND_EMAIL:
            case self::METHOD_SEND_SMS:
                $post_data['api_key'] = $this->getApiKey();
                $post_data = array_merge($post_data, $params);

                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_CREATE_CAMPAING:
                $post_data = array(
                    'api_key' => $this->getApiKey(),
                    'message_id' => $params['message_id'],
                );
                $result = $this->curlSend($url, $post_data);
                break;

            case self::METHOD_GET_MESSAGES:
                $post_data['api_key'] = $this->getApiKey();
                $post_data = array_merge($post_data, $params);

                $result = $this->curlSend($url, $post_data);
                break;
        }

        $this->setResult($result);

        return $this;
    }



    /**
     * Сама отправка
     */
    private function curlSend($url, $post_param = null){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        if($post_param){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param);
        }

        return curl_exec($ch);
    }


    /**
     * getLanguage
     */
    private function getLanguage(){
        $sys_lang = \ParamsModel::model()->titleName('language')->find()->getValue();

        if(in_array($sys_lang, $this->_languages))
            return $this->_languages[$sys_lang];
        else
            return $this->_languages['en'];
    }


    /**
     * getUrl
     */
    private function getUrl($params){
        $url = \Yii::app()->params['plugins']['services']['unisender']['api_url'];
        $url = str_replace('{lang}', $this->getLanguage(), $url);

        return $url . $params['method'] . '?format=' . $params['format'];
    }




    private function getApiKey(){
        return $this->_service_model->getParamsModel()->api_key;
    }








}
