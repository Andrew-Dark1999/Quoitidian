<?php

class Mailer
{

    const LETTER_STATIC = 'static';
    const LETTER_USER_RESTORE_PASSWORD = 'user_restore_password';
    const LETTER_USER_RESTORE_PASSWORD_TOKEN = 'user_restore_password_token';
    const LETTER_USER_REGISTRATION = 'user_registration';
    const LETTER_HISTORY_NOTIFICATION = 'history_notification';

    const LETTER_TEMPLATE0 = 'template0';
    const LETTER_TEMPLATE1 = 'template1';
    const LETTER_TEMPLATE2 = 'template2';
    const LETTER_TEMPLATE_TEXT = 'template_text';

    public static $image_logo_mail_top = '/static/images/letters/logo_mail_top.png';

    public static $image_logo_mail_bottom = '/static/images/letters/logo_mail_bottom.png';

    private $_letters_id = [];

    private $_sended_id = [];

    private $_send_id = [];

    private $_letters_model;

    private $_user_id;

    private $_template = self::LETTER_TEMPLATE1;

    private $_template_letter_data = [
        'template' => '',
        'subject'  => '',
        'body'     => '',
        'header'   => '',
    ];

    public function __construct()
    {
        \Yii::import('application.extensions.mailer.phpmailer.*');
    }

    public static function getInstance()
    {
        return new self();
    }

    public function setTemplate($template)
    {
        $this->_template = $template;

        return $this;
    }

    /**
     * подставновка значений парамертов в текст
     */
    private function getParamToText($text, array $params)
    {
        if (empty($params)) {
            return $text;
        }
        foreach ($params as $key => $value) {
            $text = Helper::mbStrReplace($key, $value, $text);
        }

        return $text;
    }

    public function setLetterData($template_letter_data)
    {
        $this->_template_letter_data = $template_letter_data;

        return $this;
    }

    /**
     * getTemplateLettersData - загрузка данных
     */
    private function getTemplateLettersData($letter)
    {
        $controler = new CController('Site');

        if ($letter !== null) {
            $letter_path = Helper::mbStrReplace('//', DIRECTORY_SEPARATOR, $controler->getViewFile(Yii::app()->findLocalizedFile('//letters/' . $letter)));
            include($letter_path);
        }

        $letter_template = Helper::mbStrReplace('//', DIRECTORY_SEPARATOR, $controler->getViewFile(Yii::app()->findLocalizedFile('//letters/' . $this->_template)));
        include($letter_template);

        return $this->_template_letter_data;
    }

    /**
     *  возвращает параметры письма: тема, текст
     */
    private function getLetter($letter, array $params = [])
    {
        // параметры присутствуют в загруженных шаблонах
        $letters_data = $this->getTemplateLettersData($letter);

        $letters_data['template'] = $this->getParamToText($letters_data['template'], $params);
        $letters_data['template'] = Helper::mbStrReplace('{body}', $this->getParamToText($letters_data['body'], $params), $letters_data['template']);
        $letters_data['template'] = Helper::mbStrReplace('{header}', $this->getParamToText($letters_data['header'], $params), $letters_data['template']);

        $body_letters = $letters_data['template'];

        $result = [
            'subject' => $this->getParamToText($letters_data['subject'], $params),
            'body'    => $body_letters,
        ];

        return $result;
    }

    public function setUserId($user_id)
    {
        $this->_user_id = $user_id;

        return $this;
    }

    /**
     * $from = "no-reply@crm.com"
     * $from_name = 'no-reply@crm.com - User name',
     * $adress = 'some@email.com',
     * $name = 'some@email.com - name')
     * $subject = 'crm.com - Subject',
     * $body = 'Some HTML or text',
     */
    public function setLetter($from, $from_name, $to, $to_name, $letter, $params, $status = MailerLettersOutboxModel::STATUS_SEND)
    {
        $letter_data = $this->getLetter($letter, $params);

        $mailer_model = new MailerLettersOutboxModel();
        $mailer_model->user_create = $this->_user_id;
        $mailer_model->letter_from = $from;
        $mailer_model->letter_from_name = $from_name;
        $mailer_model->letter_to = $to;
        $mailer_model->letter_to_name = $to_name;
        $mailer_model->letter_subject = $letter_data['subject'];
        $mailer_model->letter_body = $letter_data['body'];
        $mailer_model->status = $status;

        if ($mailer_model->save()) {
            $this->_letters_id[] = $mailer_model->getPrimaryKey();

            $params = [
                'mailer_id' => $mailer_model->mailer_id,
                'source'    => MailerLettersSourcesModel::SOURCE_GENERAL,
            ];

            $mailer_service_model = new MailerLettersSourcesModel();

            if ($mailer_service_model->addNewLetter($params)) {
                return true;
            }
        } else {
            return false;
        }
    }

    public function prepareLettesFromIdArray()
    {
        if (empty($this->_letters_id)) {
            return $this;
        }
        $mailer_model = new MailerLettersOutboxModel();
        $criteria = new CDbCriteria();
        $criteria->addInCondition('mailer_id', $this->_letters_id);
        $this->_letters_model = $mailer_model->findAll($criteria);

        return $this;
    }

    public function setMarkSended()
    {
        if (empty($this->_sended_id)) {
            return $this;
        }
        $data_model = new DataModel();
        $data_model->setText('UPDATE {{mailer_letters_outbox}} SET status = "' . MailerLettersOutboxModel::STATUS_SENDED . '" WHERE mailer_id in (' . implode(',', $this->_sended_id) . ')')->execute();

        return $this;
    }

    public function setMarkSend()
    {
        if (empty($this->_send_id)) {
            return $this;
        }
        $data_model = new DataModel();
        $data_model->setText('UPDATE {{mailer_letters_outbox}} SET status = "' . MailerLettersOutboxModel::STATUS_SEND . '" WHERE mailer_id in (' . implode(',', $this->_send_id) . ')')->execute();

        return $this;
    }

    public function deleteSended()
    {
        if (empty($this->_sended_id)) {
            return $this;
        }
        $data_model = new DataModel();
        $data_model->Delete('{{mailer_letters_outbox}}', 'mailer_id in (' . implode(',', $this->_sended_id) . ')');

        return $this;
    }

    /**
     * Возвращает экзампляр PHPMailer исходя из параметров настроек в разделе "СЕРВИСЫ РАССЫЛКИ"
     * и параметров в конфиге
     *
     * @return PHPMailer
     */
    public function createPhpMailer()
    {
        $mailer = null;
        $ms_model = new MailingServicesModel();
        $ms_model->initParams();

        switch ($ms_model->email_box) {
            case MailingServicesModel::EMAIL_BOX_INTERNAL:
                $mailerParams = \Yii::app()->params['phpMailer'];
                if ($mailerParams['mailer'] == 'mail') {
                    $mailer = $this->createPhpMailer();
                } else {
                    if ($mailerParams['mailer'] == 'smtp') {
                        $mailer = $this->createPhpMailerSMTP($mailerParams['host'], $mailerParams['port'], $mailerParams['userName'], $mailerParams['password'], $mailerParams['secure']);
                    }
                }
                break;

            case MailingServicesModel::EMAIL_BOX_EXTERNAL:
                $mailer = $this->createPhpMailerSMTP($ms_model->email_host, $ms_model->email_port, $ms_model->email_username, $ms_model->email_password);
                break;
        }

        return $mailer;
    }

    /**
     * @return PHPMailer
     */
    private function createPhpMailerMail()
    {
        $mailer = new PHPMailer();
        $mailer->Mailer = 'mail';
        $mailer->CharSet = 'utf-8';
        $mailer->ContentType = "text/html";

        return $mailer;
    }

    /**
     * @param string $host
     * @param integer $port 25, 465(ssl) or 587(tls)
     * @param null|string $userName
     * @param null|string $password
     * @param null|string $secure Options: '', 'ssl' or 'tls'
     * @return PHPMailer
     */
    private function createPhpMailerSMTP($host, $port, $userName = null, $password = null, $secure = null)
    {
        if ($secure === null) {
            $secure = '';
            if ($port == 465) {
                $secure = 'ssl';
            } else {
                if ($port == 587) {
                    $secure = 'tls';
                }
            }
        }

        $mailer = new PHPMailer();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->SMTPSecure = $secure;
        $mailer->SMTPAuth = ($userName !== null && $password !== null ? true : false);
        $mailer->Username = $userName;
        $mailer->Password = $password;
        $mailer->Mailer = 'smtp';
        $mailer->CharSet = 'utf-8';
        $mailer->ContentType = "text/html";

        return $mailer;

    }

    public function send()
    {
        if (empty($this->_letters_model)) {
            return $this;
        }

        $mailer = $this->createPhpMailer();

        foreach ($this->_letters_model as $letter_model) {
            if ($letter_model->status == MailerLettersOutboxModel::STATUS_SENDED) {
                continue;
            }

            $mailer->From = $letter_model->letter_from;
            $mailer->FromName = $letter_model->letter_from_name;

            $mailer->AddAddress($letter_model->letter_to, $letter_model->letter_to_name);

            $mailer->Subject = $letter_model->letter_subject;
            $mailer->Body = $letter_model->letter_body;

            if ($mailer->Send()) {
                $this->_sended_id[] = $letter_model->getPrimaryKey();
            } else {
                $this->_send_id[] = $letter_model->getPrimaryKey();
            }
            $mailer->ClearAddresses();
        }

        return $this;
    }

    public function sendByModel($letter_model)
    {

        if (!$letter_model) {
            return false;
        }
        if ($letter_model->status == MailerLettersOutboxModel::STATUS_SENDED) {
            return;
        }

        $mailer = $this->createPhpMailer();

        $mailer->From = $letter_model->letter_from;
        $mailer->FromName = $letter_model->letter_from_name;

        $mailer->AddAddress($letter_model->letter_to, $letter_model->letter_to_name);

        $mailer->Subject = $letter_model->letter_subject;
        $mailer->Body = $letter_model->letter_body;

        $send = $mailer->Send();
        if ($send) {
            $this->_sended_id[] = $letter_model->getPrimaryKey();
        } else {
            $this->_send_id[] = $letter_model->getPrimaryKey();
        }

        $mailer->ClearAddresses();

        return $send;

    }

}
