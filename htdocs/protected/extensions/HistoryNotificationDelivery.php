<?php
/**
 * HistoryNotificationDelivery - Рассылка уведомлений
 * @Auhor Alex R.
 */


class HistoryNotificationDelivery {

    private $_status = true;
    private $_controller;
    private $_mailer_model;


    public static function getInstance(){
        return new self();
    }


    public function setController($controller){
        $this->_controller = $controller;
        return $this;
    }

    public function getResult(){
        return array(
            'status' => $this->_status,
        );
    }


    private function makeMailerModel(){
        $this->_mailer_model = new \Mailer;
        $this->_mailer_model->setUserId(WebUser::getUserId());
        $this->_mailer_model->setTemplate(\Mailer::LETTER_TEMPLATE2);

        return $this;
    }




    /**
     * run - выполенение
     */
    public function run(){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule();

        $ns_list = NotificationSettingModel::model()->with('users')->findAll('setting_notification = ' . NotificationSettingModel::ELEMENT_SN_ENABLED . ' AND users.active = "1"');

        if(empty($ns_list)) return $this;
        $this->makeMailerModel();




        foreach($ns_list as $ns_model){
            if($this->checkSheduledSend($ns_model) == false) continue; // проверка

            $ns_data = $this->getPreparedNSData($ns_model); // подготовка данных

            if($ns_data['status'] == false) continue;

            $this->prepareLetter($ns_data); // подготовка данных письма

            // установка следующей отправки
            \HistoryNotificationDeliveryLogModel::getInstance()->update($ns_model->frequency_sending, $ns_model->users_id);
        }

        $this->send();

        return $this;
    }




    /**
     * checkSheduledSend - проверка рассписания
     */
    private function checkSheduledSend($ns_model){
        return \HistoryNotificationDeliveryLogModel::getInstance()->checkSheduled($ns_model->frequency_sending, $ns_model->users_id);
    }



    /**
     * setMarkPerformance - отметка о выполнении
     */
    private function setMarkPerformance($ns_model, $history_id_list){
        if(empty($history_id_list)) return;

        $is_data = true;
        $offset = 0;
        $history_id_list_c = array_slice($history_id_list, $offset, 1000);
        $offset = $offset+1000;

        while($is_data){
            // отметка об отправке для уведомления
            if(!empty($history_id_list_c)){
                $data_model = new \DataModel();
                $data_model->setText('UPDATE {{history_mark_view}} SET notification_delivery = "' . \HistoryMarkViewModel::DELIVERY_STATE_NONE . '" WHERE user_id = '.$ns_model->users_id.' AND history_id in ('.implode(',', $history_id_list_c).')')->execute();

                $history_id_list_c = array_slice($history_id_list, $offset, 1000);
                if(empty($history_id_list_c)) $is_data = false;
                $offset = $offset+1000;
            }
        }
    }





    /**
     * getPreparedNSData
     */
    private function getPreparedNSData($ns_model){
        if($ns_model->notifications_modules == \NotificationSettingModel::ELEMENT_NM_ALL){
            return array(
                'status' => true,
                'ns_model' => $ns_model,
            );
        }

        if($ns_model->notifications_modules == \NotificationSettingModel::ELEMENT_NM_COME){
            $nsm = $ns_model->notificationSettingModules;
            if(!empty($nsm)){
                return array(
                    'status' => true,
                    'ns_model' => $ns_model,
                    'copy_id_list' => $this->getPreparedCopyIdList($nsm),
                );
            }
        }

        return array('status' => false);
    }




    /**
     * getPreparedCopyIdList
     */
    private function getPreparedCopyIdList($nsm){
        $result = array();
        foreach($nsm as $nm){
            $result[] = $nm->copy_id;
        }

        return $this;
    }



    /**
     * prepareLetter - подготовка письма
     */
    private function prepareLetter($ns_data){
        $users_model = UsersModel::model()->findByPk($ns_data['ns_model']->users_id);
        $users_params_model = UsersParamsModel::model()->scopeUsersId($users_model->users_id)->find();

        if(!empty($users_params_model)){
            Yii::app()->setLanguage($users_params_model->language);
        } else {
            Yii::app()->setLanguage(ParamsModel::model()->titleName('language')->find()->getValue());
        }
            $to_name = $users_model->getFullName();

        $result = false;

        // get notice and set letter
        $history_model_list = $this->getHistoryModelList($ns_data['ns_model']);
        if($history_model_list && $history_model_list['data'] == false){
            return false;
        }

        $letter_params = $this->getLetterParams($ns_data, $history_model_list);

        if(!empty($letter_params)){
            $result = $this->setLetter($ns_data['ns_model'], $to_name, $letter_params);
        }

        if($result){
            $history_id_list = $this->getHistoryIdList($history_model_list['data']);
            $this->setMarkPerformance($ns_data['ns_model'], $history_id_list);
        }


    }


    private function getHistoryModelList($ns_model){
        $notification_delivery_vars = array(
            'modules' => array(),
        );

        if($ns_model->notifications_modules == \NotificationSettingModel::ELEMENT_NM_COME){
            $nsm_model_list = $ns_model->notificationSettingModules();
            if(empty($nsm_model_list))return;
            foreach($nsm_model_list as $nsm_model){
                $notification_delivery_vars['modules'][] = $nsm_model->copy_id;
            }
        }

        $history_model_list = History::getInstance()->getFromHistory(
                                    HistoryMessagesModel::OBJECT_DN,
                                    [
                                        'limit' => 0,
                                        'get_notice_count' => false,
                                        'user_id' => $ns_model->users_id
                                    ],
                                    $notification_delivery_vars);

        return $history_model_list;
    }



    /**
     * getLetterParams
     */
    private function getLetterParams($ns_data, $history_model_list){
        switch($ns_data['ns_model']->sending_method){
            case \NotificationSettingModel::ELEMENT_SM_EMAIL:
                return $this->getLetterParamsToEmail($history_model_list);
        }
    }


    /**
     * getHistoryIdList
     */
    public function getHistoryIdList($history_model_data_list){
        if(empty($history_model_data_list)) return array();

        $result = array();
        foreach($history_model_data_list as $list){
            $result[] = (integer)$list['history_model']->history_id;
        }

        return $result;
    }


    /**
     * getLetterParamsToEmail
     */
    private function getLetterParamsToEmail($history_model_list){
        $params_model = ParamsModel::model()->findAll();
        $history_model_list['site_url'] = ParamsModel::getValueFromModel('site_url', $params_model);


        $letter_body = $this->_controller->widget('ext.ElementMaster.HeaderNotices.Notices', array('data' => $history_model_list));
        $letter_body = $letter_body->setNoticeHND()->buildInner()->getResult(true);

        $letter_body = $letter_body;

        $body_list = [];
        if($letter_body){
            foreach ($letter_body as $body){
                $body_list[] = $body['html'];
            }
        }

        $result = array(
            '{site_url}' => ParamsModel::getValueFromModel('site_url', $params_model),
            '{sales_email}' => ParamsModel::getValueFromModel('sales_email', $params_model),
            '{support_email}' => ParamsModel::getValueFromModel('support_email', $params_model),
            '{presentation_link}' => ParamsModel::getValueFromModel('presentation_link', $params_model),
            '{body}' => $body_list,
        );

        return $result;
    }



    /**
     * setLetter
     */
    private function setLetter($ns_model, $to_name, $letter_params){
        $to = $ns_model->getSendingVarsValue('email_notification');

        switch($ns_model->sending_method){
            case \NotificationSettingModel::ELEMENT_SM_EMAIL:
                return $this->setLetterToEmail($to, $to_name, $letter_params);
                break;
        }
    }


    /**
     * setLetterToEmail
     */
    private function setLetterToEmail($to, $to_name, $letter_params){
        $params_model = ParamsModel::model()->findAll();

        return $this->_mailer_model
                            ->setLetter(
                                ParamsModel::getValueFromModel('sending_out', $params_model),
                                ParamsModel::getValueFromModel('sending_out_name', $params_model),
                                $to,
                                $to_name,
                                Mailer::LETTER_HISTORY_NOTIFICATION,
                                $letter_params,
                                MailerLettersOutboxModel::STATUS_IS_SENT
                            );
    }




    /**
     * send
     */
    private function send(){
        // send All to email
        $this->_mailer_model
            ->prepareLettesFromIdArray()
            ->send()
            ->setMarkSended()
            ->setMarkSend();
    }


}
