<?php



class MailerLettersTemplatesModel{


    const LETTER_USER_REGISTRATION  = 'userRegistration';

    private $_vars;





    public static function sendLetter($mailer_letter_name, $vars){
        $method = 'getLetter' . $mailer_letter_name;
        \Yii::app()->setLanguage(\ParamsModel::model()->titleName('language')->find()->getValue());
        (new MailerLettersTemplatesModel())->setVars($vars)->{$method}();
    }





    private function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    /**
     * getLetterUserRegistration - отправка письма о регистрации нового пользователя
     *    $this->_vars = {
     *       0 = email
     *       1 = user_name
     *       2 = login
     *       3 = password
     *    }
     */
    private function getLetterUserRegistration(){
        if(count($this->_vars) != 4) return;
        $params_model = ParamsModel::model()->findAll();

        $mailer = new Mailer();
        $mailer
            ->setLetter(
                ParamsModel::getValueFromModel('sending_out', $params_model),
                ParamsModel::getValueFromModel('sending_out_name', $params_model),
                $this->_vars[0],
                $this->_vars[1],
                Mailer::LETTER_USER_REGISTRATION,
                array(
                    '{site_url}' => ParamsModel::getValueFromModel('site_url', $params_model),
                    '{site_title}' => preg_replace('~(http://|https://)~', '', ParamsModel::getValueFromModel('site_url', $params_model)),
                    '{company_name}' => ParamsModel::getValueFromModel('crm_name', $params_model),
                    '{service_email}' => ParamsModel::getValueFromModel('service_email', $params_model),
                    '{sales_email}' => ParamsModel::getValueFromModel('sales_email', $params_model),
                    '{support_email}' => ParamsModel::getValueFromModel('support_email', $params_model),
                    '{presentation_link}' => ParamsModel::getValueFromModel('presentation_link', $params_model),
                    '{login}' => $this->_vars[2],
                    '{password}' => $this->_vars[3],
                    '{user_name}' => $this->_vars[1]),
                MailerLettersOutboxModel::STATUS_IS_SENT
            );

        $mailer
            ->prepareLettesFromIdArray()
            ->send()
            ->setMarkSended()
            ->setMarkSend();
    }













}
