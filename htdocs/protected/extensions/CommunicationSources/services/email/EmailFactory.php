<?php
/**
 * EmailFactory
 */


class EmailFactory{


    const MAILBOX_NAME_INBOX    = 'inbox';  // Входящие
    const MAILBOX_NAME_SENT     = 'sent';   // Отправленные
    const MAILBOX_NAME_TRASH    = 'trash';  // Корзина

    //const FLAG_DELETE_MESSAGE   = 'delete_message';

    const FLAG_SET_SEEN         = 'set_seen';


    protected $_source_name = 'email';

    protected $_service_name;
    protected $_service_title;
    protected $_user_form_params = array();

    protected $_active = false;

    protected $_imap_server_host;
    protected $_imap_server_connect_secure;
    protected $_imap_server_port;

    protected $_smtp_server_host;
    protected $_smtp_server_connect_secure;
    protected $_smtp_server_port;


    protected $_user_id;
    protected $_user_login;
    protected $_user_password;
    protected $_user_outbox_name = false;

    protected $_imap_stream;

    protected $_imap_mailbox;
    protected $_imap_mailbox_list;
    protected $_imap_mailbox_international_list;

    protected $_result = array();
    protected $_messages = array();
    protected $_error = false;

    protected $_action_vars;
    protected $_attachment_models = null;

    protected $_attachment_file_path;
    protected $_attachment_type_file;

    protected $_logging_model;


    public function __construct(){
        \Yii::import('application.extensions.mailer.phpmailer.*');

        $this->setActive();
        $this->_attachment_file_path = YiiBase::app()->basePath. '/../' . \ParamsModel::getValueFromModel('upload_path_tmp');
        $this->_attachment_type_file = \Yii::app()->params['communications']['sources']['email']['attachment_type_files'];
    }


    public function setActionVars($action_vars){
        $this->_action_vars = $action_vars;
        return $this;
    }


    public function getServiceName(){
        return $this->_service_name;
    }


    public function getServiceTitle(){
        return Yii::t('communications', $this->_service_title);
    }


    public function getServiceActive(){
        return $this->_active;
    }




    private function initLoggingModel($log_name){
        $rand = rand(111111, 999999);
        $prefix = '#'.$rand;

        $this->_logging_model = Logging::getInstance($prefix);
        $this->_logging_model
            ->setLogName($log_name)
            ->setAppendPrefixToMessage(true);

    }



    protected function clearMessages(){
        $this->_messages = [];
        return $this;
    }

    protected function addMessage($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        return $this;
    }


    protected function addMessageError($message, $params = array()){
        $this->_error = true;
        $this->addMessage($message, $params);

        return $this;
    }

    protected function addMessageErrorBefore($message, $params = array()){
        $this->_error = true;
        array_unshift($this->_messages, Yii::t('communications', $message, $params));

        return $this;
    }



    protected function addMessages($messages = array()){
        $messages = (array)$messages;

        foreach ($messages as $message){
            $this->addMessage($message);
        }

        return $this;
    }


    public function getStatus(){
        return $this->_error ? false : true;
    }


    public function getResult(){
        return array(
            'status' => $this->getStatus(),
            'messages' => $this->_messages,
        ) + $this->_result;
    }


    private function setActive(){
        $this->_active = \Yii::app()->params['communications']['sources']['email']['services'][$this->getServiceName()]['enable'];
        return $this;
    }


    protected function isActive(){
        return $this->_active;
    }


    public function setUserId($user_id){
        $this->_user_id = $user_id;
        return $this;
    }



    protected function getUserId(){
        return $this->_user_id;
    }




    protected function setUserLogin($user_login){
        $this->_user_login = $user_login;
    }



    protected function getUserLogin(){
        return $this->_user_login;
    }



    /**
     * Загружаем параметры сервиса из конфига
     * @return $this
     */
    public function initParams(){
        $params = \Yii::app()->params['communications']['sources']['email']['services'][$this->getServiceName()]['server_params'];

        if (empty($params)) {
            $this->addMessageError('IMAP server params are not exists', []);
            return $this;
        }

        foreach ($params as $param_name => $param_value) {
            if (property_exists($this, '_' . $param_name)) {
                $this->{'_' . $param_name} = $param_value;
            }
        }

        $this->initUserParams();
        $this->_imap_mailbox_international_list = \Yii::app()->params['communications']['sources']['email']['mailbox_international'];

        $this->_imap_mailbox_list = array($this->getImapMailboxDefault() . 'INBOX');

        return $this;
    }



    /**
     * Загружаем пользовательские параметры из CommunicationsServiceParamsModel
     * @return $this
     */
    protected function initUserParams(){
        $params = (new CommunicationsServiceParamsModel())->getUserParams($this->_user_id, $this->_source_name, $this->_service_name);

        if($params == false) {
            return $this;
        }

        foreach ($params as $param_name => $param_value) {
            if (property_exists($this, '_' . $param_name)) {
                $this->{'_' . $param_name} = $param_value;
            }
        }

        if(!$this->_user_outbox_name){
            $this->_user_outbox_name = $this->_user_login;
        }

        return $this;
    }



    /**
     * Загружаем новые параметры из ActiveVars
     * @return $this
     */
    protected function mergeParams($param_list){
        foreach($param_list as $param_name => $param_value) {
            if (property_exists($this, '_' . $param_name)) {
                $this->{'_' . $param_name} = $param_value;
            }
        }

        if(!$this->_user_outbox_name){
            $this->_user_outbox_name = $this->_user_login;
        }

        $this->_imap_mailbox_list = array($this->getImapMailboxDefault());

        return $this;
    }



    public function getUserFormParams(){
        return $this->_user_form_params;
    }








    /*******************************************************
     *                  ACTIONS
    /******************************************************/



    public function runAction($method_name){
        if(method_exists($this, $method_name)){
            $this->{$method_name}();
        }

        return $this;
    }





    protected function actionRunFlag(){
        if($this->_action_vars == false){
            return;
        }

        switch($this->_action_vars['flag']){
            case self::FLAG_SET_SEEN :
                $this->runFlagSetSeen();
                break;
        }
    }





    /**
     * runFlagSetSeen
     */
    private function runFlagSetSeen(){
        if(empty($this->_action_vars['uid_list'])){
            return $this;
        }

        $uid_list = array();
        $this->ImapOpen();

        foreach($this->_action_vars['uid_list'] as $uid){
            $result = imap_setflag_full($this->_imap_stream, $uid, '\\Seen', ST_UID);
            if($result){
                $uid_list[] = $uid;
            }
        }

        $this->ImapClose();

        $this->_result['uid_list'] = $uid_list;

        return $this;
    }





    /*
     * actionDeleteMessage
     */
    public function actionDeleteMessage(){
        if(empty($this->_action_vars['uid_list'])){
            return $this;
        }

        $uid_list = array();

        $this->ImapOpen();

        $mailbox = $this->findImapMailbox(self::MAILBOX_NAME_TRASH);

        foreach($this->_action_vars['uid_list'] as $uid){
            if($mailbox){
                // move
                $result = $this->imapMove($mailbox, $uid);
            } else {
                // delete
                $result = $this->imapDelete(null, $uid);
            }

            if($result){
                $uid_list[] = $uid;
            }
        }

        $this->ImapClose();

        $this->_result['uid_list'] = $uid_list;

        return $this;
    }




    /**
     * action SendMessage
     */
    protected function actionSendMessage(){
        $letter_model = $this->_action_vars;

        if($letter_model == false){
            return $this->addMessageError('Data for sending is empty');
        }

        $attachment_models = (new MailerLettersOutboxFilesModel())->getAttachmentModelsByMailerId($this->_action_vars->mailer_id);

        try {
            $mailer_model = new PHPMailer();
            $mailer_model->Host = $this->_smtp_server_host;
            $mailer_model->IsSMTP();
            $mailer_model->CharSet = 'UTF-8';
            $mailer_model->SMTPAuth = true;
            $mailer_model->From = $this->_user_login;
            $mailer_model->Port = $this->_smtp_server_port;
            $mailer_model->SMTPSecure = mb_strtolower($this->_smtp_server_connect_secure);
            $mailer_model->Timeout = 10;

            $alternate_smtp = \Yii::app()->params['communications']['sources']['email']['services']['yandex']['server_params'];

            $mailer_model->Username = $this->_smtp_server_host == $alternate_smtp['smtp_server_host'] ? $alternate_smtp['alternate_username'] : $this->_user_login;
            $mailer_model->Password = $this->_smtp_server_host == $alternate_smtp['smtp_server_host'] ? $alternate_smtp['alternate_password'] : $this->_user_password;

            $mailer_model->FromName = $letter_model->letter_from_name;

            $letter_to = trim($letter_model->letter_to, '\"');
            $letter_to = explode(',', $letter_to);
            if(is_array($letter_to)){
                foreach($letter_to as $destenation){
                    $mailer_model->AddAddress($destenation);
                }
            } else{
                $mailer_model->AddAddress($letter_to);
            }

            $mailer_model->Subject = $letter_model->letter_subject;

            $mailer_model->Body = $letter_model->letter_body;
            $mailer_model->AltBody = $letter_model->letter_body;

            if(!empty($attachment_models)){
                foreach($attachment_models as $attachment_model){
                    $full_file_name = $attachment_model->getFullFileName(true);
                    $file_name = (new SplFileInfo($full_file_name))->getFilename();
                    $mailer_model->AddAttachment($full_file_name, $file_name, 'base64', mime_content_type($full_file_name));
                }
            }

            // send
            if($mailer_model->Send() == false){
                $this->addMessageError($mailer_model->ErrorInfo);
                $this->addMessageError('The letter was not sent, it will be sending later');
                return $this;
            }

        } catch (Exception $e){
            $this->addMessageError($e->getMessage());
            $this->addMessageError('The letter was not sent, it will be sending later');
            return $this;
        }

        $this->afterSendMessage($letter_model, $mailer_model);

        return $this;
    }




    /**
     * afterSendMessage
     */
    protected function afterSendMessage($letter_model, $mailer_model){
        //1. переносим в Отправленные
        $uid_sent = null;

        $this->imapOpen();

        $mailbox = $this->findImapMailbox(self::MAILBOX_NAME_SENT);
        $uid = $this->findUidByMessageId($mailbox, $mailer_model->getLastMessageID());

        if($uid){
            $uid_sent = $uid;
        } else {
            $result = $this->imapAppend($mailbox, $mailer_model->getSentMIMEMessage());
            if($result){
                $uid = $this->findUidByMessageId($mailbox, $mailer_model->getLastMessageID());

                if($uid){
                    imap_setflag_full($this->_imap_stream, $uid, '\\Seen', ST_UID);
                    $uid_sent = $uid;
                }
            }
        }

        $this->_error = false;
        $this->clearMessages();
        $this->imapClose();

        // сохраняем служебную информацию об отправленном письме
        $attributes = array(
            'mailer_id' => $letter_model->mailer_id,
            'message_id' => $mailer_model->getLastMessageID(),
            'uid' => $uid_sent,
        );
        $outbox_params_model = new MailerLettersOutboxParamsModel();
        $outbox_params_model->setAttributes($attributes);
        $outbox_params_model->save();
    }





    /**
     * findUidByMessageId - поиск uid по message_id
     */
    private function findUidByMessageId($mailbox, $message_id){
        if($this->hasImapStream() == false){
            return;
        }

        $this->imapReopen($mailbox);

        $this->setLetterCountAll();
        if($this->_letter_count_all == false){
            return;
        }

        $this->_letter_count_last = $this->_letter_count_all;

        while(true){
            $letter_header_list = $this->getImapFetchOverviewPrior();

            if($letter_header_list == false){
                break;
            }

            for($i = count($letter_header_list) - 1; $i >= 0; $i--){
                $header = $letter_header_list[$i];
                if($header->message_id && $header->message_id == $message_id){
                    return $header->uid;
                }
            }
        }


        return;
    }




    /**
     * imapMove
     */
    protected function imapMove($mailbox, $uid){
        if($this->hasImapStream() == false){
            return false;
        }

        if($mailbox){
            $mailbox_name = $this->getImapMailboxName($mailbox);
        }

        if($mailbox_name == false){
            return false;
        }

        $result = imap_mail_copy($this->_imap_stream, $uid, $mailbox_name, CP_UID | CP_MOVE);
        imap_expunge($this->_imap_stream);

        return $result;
    }



    /**
     * imapDelete
     */
    protected function imapDelete($mailbox, $uid){
        if($this->hasImapStream() == false){
            return false;
        }

        $this->imapReopen($mailbox);

        imap_delete($this->_imap_stream, $uid, FT_UID);
        imap_expunge($this->_imap_stream);

        return true;
    }


    /**
     * imapAppend
     */
    protected function imapAppend($mailbox, $message){
        if($this->hasImapStream() == false){
            return false;
        }

        $result = imap_append($this->_imap_stream, $mailbox, $message);

        return $result;
    }







    /**
     * actionGetMessageHeader - Загружает письма с сервера.
     *                          Загрузка работает поблочно - по 100 писем начиная с конца
     */
    protected function actionGetMessageHeader(){
        $this->initLoggingModel('service-load-email-letters');

        // function
        $func = function($mailbox){
            $letter_list = array();

            $this->_logging_model->toFile("Load count all letters");

            $this->setLetterCountAll();
            if($this->_letter_count_all == false){
                $this->_logging_model->toFile("false");
                return;
            }

            $this->_logging_model->toFile("true");
            $this->_logging_model->toFile("Letter_count_step - " . $this->_letter_count_step);

            $this->_letter_count_last = $this->_letter_count_all;

            $block = 1;

            if(!empty($this->_action_vars['last_uid'])){
                $this->_logging_model->toFile("Last_uid - " . $this->_action_vars['last_uid']);
            } else {
                $this->_logging_model->toFile("Warning!!! Param \"last_uid\" not found");
            }

            while(true){
                $this->_logging_model->toFile("Load next letters... [Block $block]");
                $this->_logging_model->toFile("   -letter_count_all - " . $this->_letter_count_all);
                $this->_logging_model->toFile("   -letter_count_last - " . $this->_letter_count_last);

                $letter_header_list = $this->getImapFetchOverviewPrior();

                if($letter_header_list == false){
                    $this->_logging_model->toFile("Break [Block $block]");
                    break;
                }

                $this->_logging_model->toFile('   Loaded ' . count($letter_header_list) . " next letters");

                $append_new = 0;

                for($i = count($letter_header_list) - 1; $i >= 0; $i--){
                    $header = $letter_header_list[$i];

                    if($this->issetPropertiesInImapHeaderInfo($header, 'uid') == false){
                        $this->_logging_model->toFile("   Skip. Not UID. ". ($this->issetPropertiesInImapHeaderInfo($header, 'message_id') ? 'message_id: ' . $header->message_id : ''));
                        continue;
                    }
                    /*
                    if($this->issetPropertiesInImapHeaderInfo($header, array('uid', 'from', 'message_id', 'udate')) == false){
                        continue;
                    }
                    */

                    //last_uid
                    if(!empty($this->_action_vars['last_uid']) && ($this->_action_vars['last_uid'] >= $header->uid)){
                        $this->_logging_model->toFile("   Last letters in mailbox #" . $header->uid);
                        $this->_logging_model->toFile("   Appended $append_new new letters");
                        $this->_logging_model->toFile("Break [Block $block]");
                        break(2);
                    }

                    //subject
                    if($this->issetPropertiesInImapHeaderInfo($header, 'subject')){
                        $header->subject = $this->getImapMimeHeaderDecode($header->subject);
                    }

                    //from
                    if($this->issetPropertiesInImapHeaderInfo($header, 'from') && !empty($header->from)){
                        preg_match("/[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}/", $header->from, $from_email);
                        if(!empty($from_email)){
                            $header->from = $from_email[0];
                        }
                    }

                    $letter_data = array(
                        'uid' => $header->uid,
                        'mailer_box_name' => $mailbox,
                        'subject' => ($this->issetPropertiesInImapHeaderInfo($header, 'subject') ? $header->subject : ''),
                        'from' => ($this->issetPropertiesInImapHeaderInfo($header, 'from') ? $header->from : ''),
                        'date' => ($this->issetPropertiesInImapHeaderInfo($header, 'udate') ? $header->udate : ''),
                        'message_id' => ($this->issetPropertiesInImapHeaderInfo($header, 'message_id') ? $header->message_id : ''),
                        'references' => ($this->issetPropertiesInImapHeaderInfo($header, 'references') ? $header->references : ''),
                    );

                    array_unshift($letter_list, $letter_data);

                    $this->_logging_model->toFile("   uid: " . $letter_data['uid'] . ", date: " . $letter_data['date']);

                    $append_new++;
                }

                $this->_logging_model->toFile("   Append $append_new new letters");
                if($append_new){
                    $this->_logging_model->toFile("Break [Block $block]");
                }

                $block++;
            }

            return $letter_list;
        };


        $result = array();

        $this->imapOpen();

        $this->_logging_model->toFile("--------------------------------");
        $this->_logging_model->toFile("--- Start load email letters ---");
        $this->_logging_model->toFile("User login: \"{$this->_user_login}\"");

        $mailbox = $this->findImapMailbox(self::MAILBOX_NAME_INBOX);

        if($mailbox){
            $this->_logging_model->toFile("Mailbox \"$mailbox\"");

            $this
                ->callForMailbox($mailbox, $func, $result)
                ->imapClose();

            $this->_logging_model->toFile("Find " . count($result) . " new letters");
        } else {
            $this->_logging_model->toFile("Mailbox - false");
        }

        $this->_logging_model->toFile('--- Done ---');

        $this->_result = ['letter_list' => $result];

        return $this;
    }







    protected function actionGetMessageBody(){
        \Yii::import('application.extensions.mailer.imap.*');

        $letter_header_list = $this->_action_vars['letter_header_list'];


        if(empty($letter_header_list)){
            $this->addMessageError('Undefined params "upload_uid"', []);
            return $this;
        }

        $this->ImapOpen();

        if($this->hasImapStream() == false){
            $this->ImapClose();
            return $this;
        }

        $letter_list = [];

        foreach($letter_header_list as $letter_header){
            if($this->_imap_mailbox != $letter_header['mailer_box_name']){
                if($this->imapReopen($letter_header['mailer_box_name']) == false){
                    continue;
                }
            }

            $email_person_list = array(
                'from' => array(),
                'to' => array(),
                'copy' => array(),
                'all' => array(),
            );

            $num = imap_msgno($this->_imap_stream, $letter_header['uid']);
            if($num > 0){
                $header = (imap_headerinfo($this->_imap_stream, $num));

                if($this->issetPropertiesInImapHeaderInfo($header, array('fromaddress', 'toaddress', 'from', 'to', 'message_id', 'udate')) == false){
                    continue;
                }


                //email from
                preg_match("/[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}/", $header->fromaddress, $from_email);
                if (!empty($from_email) && is_array($from_email)) {
                    //email
                    $email_person_list['from']['email'] = $from_email[0];
                    //title
                    if (!empty($header->sender[0]->personal)) {
                        $email_person_list['from']['title'] = $this->getImapMimeHeaderDecode($header->sender[0]->personal);
                    } else {
                        $email_person_list['from']['title'] = $this->getImapMimeHeaderDecode($header->fromaddress);
                    }
                    $email_person_list['all'][] = array('title'=> $email_person_list['from']['title'], 'email' => $email_person_list['from']['email']);
                }

                //email to
                preg_match_all("/[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}/", $header->toaddress, $to_email);
                if (!empty($to_email) && is_array($to_email)) {
                    foreach($to_email[0] as $email){
                        $email_person_list['to'][] = array('title'=> '', 'email' => $email);
                        $email_person_list['all'][] = array('title'=> '', 'email' => $email);
                    }
                }

                //email copy
                if(property_exists($header, 'ccaddress')){
                    preg_match_all("/[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?[\ .A-Za-z0-9]{2,}/", $header->ccaddress, $copy_email);
                    if(!empty($to_email) && is_array($copy_email)){
                        foreach($copy_email[0] as $email){
                            $email_person_list['copy'][] = array('title'=> null, 'email' => $email);
                            $email_person_list['all'][] = array('title'=> null, 'email' => $email);
                        }
                    }
                }

                restore_error_handler();
                $message = Yii::createComponent('application.extensions.mailer.imap.Message', $this->_imap_stream, $letter_header['uid'], $this->_attachment_file_path);

                $files = $message->getAttachments();

                $file_id_list = array();
                $attachments = array();
                foreach ($files as $file) {
                    if(empty($file['origin_file_name'])){
                        unlink(YiiBase::app()->basePath. '/../' . \ParamsModel::getValueFromModel('upload_path_tmp') . DIRECTORY_SEPARATOR . $file['upload_file_name']);
                        continue;
                    }
                    $file_params = array(
                        'origin_file_name' => $file['origin_file_name'],
                        'tmp_file_name' => $file['upload_file_name'],
                        'path' => \ParamsModel::getValueFromModel('upload_path_tmp'),
                        'mime_type' => $file['mime_type'],
                        'size' => $file['size'],
                        'source_name' => 'email',
                        'thumb_scenario' => 'activity',
                        'file_type' => 'activity',
                        'copy_id' => ExtensionCopyModel::MODULE_COMMUNICATIONS,

                    );
                    $u_model = $this->UploadFile($file_params);

                    if(!empty($u_model->id)){
                        $file_params['uploads_id'] = $u_model->id;
                        $file_id_list[] = $u_model->id;
                        $attachments[] = $file_params;
                    }else{
                        $this->addMessageError('File was not saved');
                    }
                }

                $subject = html_entity_decode(trim($message->getSubject()));
                $body = $message->getMessageBody(true);

                $letter_list[] =
                    array(
                        'user_id' => $this->_user_id,

                        'message_id' => $header->message_id,
                        'references' => isset($header->references) ? $header->references : '',
                        'in_reply_to' => isset($header->in_reply_to) ? $header->in_reply_to : '',
                        'uid' => $letter_header['uid'],

                        'mailer_box_name' => $letter_header['mailer_box_name'],

                        'email_from' => $email_person_list['from'],
                        'email_to' => $email_person_list['to'],
                        'email_copy' => $email_person_list['copy'],
                        'email_all' => $email_person_list['all'],

                        'date' => $header->udate,
                        'subject' => $this->prepareLetterValue($subject),
                        'body' => $this->prepareBody($body, false),
                        'body_text' => $this->prepareBodyText($body, true),
                        'attachments' => $attachments,
                        'file_id_list' => $file_id_list,
                    );
            }
        }

        $this->_result = array('letter_list' => $letter_list);

        return $this;
    }







    /**
     * actionCheckServiceParams - проверка параметров подключения к сервису
     */
    protected function actionCheckServiceParams(){
        error_reporting(0);

        $this->mergeParams($this->_action_vars['params']);

        $this->imapOpen();

        if($this->hasImapStream() == false){
            $this
                ->clearMessages()
                //->addImapMessagesErrors()
                ->addMessageErrorBefore('<br>')
                ->addMessageErrorBefore('Can\'t connecting with service "{service}"', array('{service}' => $this->_service_title))
                ->addMessageError('Login and/or password is not correctly')
                ->imapClose();

            error_reporting(E_ALL);
        }

        $this->ImapClose();

        error_reporting(E_ALL);

        return $this;
    }







    /**
     * actionGetMessageIdList - возвращает  список uid всех писем ()
     *
     * @return $this
     */
    protected function actionGetMessageIdList(){
        $this->ImapOpen();

        if($this->_error){
            $this->ImapClose();
            return $this;
        }

        $letter_list = array();
        $mailbox_list = $this->_imap_mailbox_list;

        if($mailbox_list){
            foreach($mailbox_list as $mailbox){
                if($this->_imap_mailbox != $mailbox){
                    if($this->imapReopen($mailbox) == false){
                        return $this;
                    }
                }

                $search_list = imap_search($this->_imap_stream, 'ALL', SE_UID);

                if($search_list){
                    $letter_list+= $search_list;
                }

            }
        }

        $this->ImapClose();

        $this->_result = ['letter_list' => $letter_list];

        return $this;
    }






    /*******************************************************
     *                  IMAP METHODS
    /******************************************************/





    /**
     *  imapOpen - Открывает поток IMAP
     */
    protected function imapOpen($imap_mailbox = null){
        if($imap_mailbox === null){
            $imap_mailbox = $this->getImapMailboxDefault();
        }

        if($imap_mailbox){
            $this->_imap_stream = @imap_open($imap_mailbox, $this->_user_login, $this->_user_password);

            if($this->hasImapStream() == false){
                return $this->addMessageError('Error opening IMAP connection');
            }

            $this->_imap_mailbox = $imap_mailbox;

        } else {
            return $this->addMessageError('Error opening IMAP connection, the Host must have value');
        }

        //Вот только я никак немогу найти подтверждение, что наша imap_list() возвращает результат команды xlist, а не list. Так же не вижу, чтобы было точно написано, что  xlist должен возвратить списки папок именно в англ локализации.


    }


    /**
     *  imapReopen - Переоткрывает поток IMAP к новому ящику
     */
    protected function imapReopen($mailbox){
        if($this->hasImapStream() && $mailbox){
            if($mailbox == false){
                return false;
            }
            if($this->_imap_mailbox == $mailbox){
                return true;
            }
            $result = imap_reopen($this->_imap_stream, $mailbox);
            if($result){
                $this->_imap_mailbox = $mailbox;
            }

            return $result;
        }
    }



    protected function hasImapStream(){
        return $this->_imap_stream ? true : false;
    }



    protected function ImapClose(){
        if($this->hasImapStream()){
            imap_close($this->_imap_stream);
        }

        $this->clearImapAlerts();
        $this->clearImapErrors();

        return $this;
    }



    protected function clearImapErrors(){
        imap_errors();
        return $this;
    }


    protected function clearImapAlerts(){
        imap_alerts();
        return $this;
    }



    protected function addImapMessagesErrors(){
        $imap_errors = imap_errors();
        if(($imap_errors) == false){
            return $this;
        }

        $messages = [];
        foreach($imap_errors as $message){
            $messages[] = $message;
        }

        $this->addMessageError('IMAP error: "{s}"', ['{s}' => implode('<br>', $messages)]);

        return $this;
    }




    protected function addImapMessagesAlerts(){
        $imap_alerts = imap_alerts();
        if(($imap_alerts) == false){
            return $this;
        }

        $messages = [];
        foreach($imap_alerts as $message){
            $messages[] = $message;
        }

        $this->addMessageError('IMAP alert: "{s}"', ['{s}' => implode('<br>', $messages)]);

        return $this;
    }





    /**
     * getMainBox - возвращает активный почтовый ящик
     */
    protected function getMainBox(){
        return $this->_imap_mailbox;
    }




    /**
     * getImapMailboxList - возвращает список почтовых ящиков
     */
    protected function getImapMailboxList(){
        return $this->_imap_mailbox_list;
    }



    /**
     * getImapMailboxDefault - возвращает почтовый ящик по умолчанию
     */
    protected function getImapMailboxDefault(){
        return '{' . $this->_imap_server_host . ':' . $this->_imap_server_port . '/imap/' . $this->_imap_server_connect_secure . '}';
    }



    /**
     * loadImapMailboxList - возвращает все почтовые ящики
     * @return array
     */
    private function loadImapMailboxList(){
        if($this->hasImapStream() == false){
            return;
        }

        $mailbox_list = array();

        $imap_stream = imap_open($this->getImapMailboxDefault(), $this->_user_login, $this->_user_password);
        $list = imap_list($imap_stream, $this->getImapMailboxDefault(), "*");
        imap_close($imap_stream);

        if($list){
            $mailbox_list = array_merge($mailbox_list, $list);
        }

        $key = array_search($this->getImapMailboxDefault() . 'INBOX', $mailbox_list);
        if($key !== false){
            unset($mailbox_list[$key]);
        }

        array_unshift($mailbox_list, $this->getImapMailboxDefault() . 'INBOX');



        if($mailbox_list){
            return $mailbox_list;
        }
    }


    /**
     * setImapMailboxList - Установка всех почтовых ящиков, на которые подписаны
     * @param null $mailbox_name
     * @return $this
     */

    private function setImapMailboxList($mailbox_name = null){
        $this->_imap_mailbox_list = null;

        $mailbox_list = $this->loadImapMailboxList();

        if($mailbox_list == false){
            return $this;
        }

        if($mailbox_name === null){
            $this->_imap_mailbox_list = $mailbox_list;
            return $this;
        }

        $mailbox_item = $this->findImapMailbox($mailbox_name, $mailbox_list);

        if($mailbox_item){
            $this->_imap_mailbox_list = array($mailbox_item);
        }

        return $this;
    }




    /**
     * mainboxListUtf7Decode - Декодирует список поштовых ящиков в utf8
     */
    private function mainboxListUtf7Decode(&$mailbox_list){
        if($mailbox_list == false){
            return;
        }

        foreach($mailbox_list as $key => &$value){
            $value = imap_utf7_decode($value);
        }
    }



    protected function findImapMailbox($mailbox_name, $mailbox_list = null){
        if($mailbox_list === null){
            $mailbox_list = $this->loadImapMailboxList();
        }

        if($mailbox_list == false){
            return;
        }

        $mailbox_international_list = $this->_imap_mailbox_international_list[$mailbox_name];

        if($mailbox_international_list == false){
            return $this;
        }

        // search function
        $func_array_pos = function($str) use ($mailbox_list){
            foreach($mailbox_list as $key => $mailbox){
                if(strpos($mailbox, $str)){
                    return $key;
                }
            }

            return false;
        };

        // search mailbox
        foreach($mailbox_international_list as $mailbox_international){
            $key = $func_array_pos($mailbox_international);
            if($key !== false){
                return $mailbox_list[$key];
            }

            $mailbox_utf7 = imap_utf8_to_mutf7($mailbox_international);
            $key = $func_array_pos($mailbox_utf7);
            if($key !== false){
                return $mailbox_list[$key];
            }
        }
    }


    /**
     * getImapMailboxName - возвращает название Mailbox без параметров сервера
     */
    protected function getImapMailboxName($mailbox){
        if($mailbox == false){
            return;
        }

        return str_replace($this->getImapMailboxDefault(), '', $mailbox);
    }








    private function getImapMimeHeaderDecode($content){
        $mime = imap_mime_header_decode($content);
        $content = "";

        foreach($mime as $key => $m){
            if(!$this->checkUtf8($m->charset)){
                $content .= $this->convertToCharset($m->charset, 'utf-8', $m->text);
            }else{
                $content .= $m->text;
            }
        }
        return $content;
    }








    /*******************************************************
     *                  IMAP METHODS FOR load headers
    /******************************************************/






    /**
     * callForAllMailbox - вызывает указанную функцию для всех ящиков и возвращает общий результат.
     *                        Если функция возвратит false - цикл прекратиться
     *                        &$result - должна быть такого же типа, что и возвращаемый  результат из функции
     */
    protected function callForAllMailbox($function, &$result = null){
        if($this->hasImapStream() == false){
            return $this;
        }

        $mailbox_list = $this->_imap_mailbox_list;

        if($mailbox_list){
            foreach($mailbox_list as $mailbox){
                $this->callForMailbox($mailbox, $function, $result);
            }
        }

        return $this;
    }







    /**
     * callForMailbox - вызывает указанную функцию для почтового ящика и возвращает общий результат.
     *                        Если функция возвратит false - цикл прекратиться
     *                        &$result - должна быть такого же типа, что и возвращаемый  результат из функции
     */
    protected function callForMailbox($mailbox, $function, &$result = null){
        if($this->hasImapStream() == false){
            return $this;
        }

        if($this->_imap_mailbox != $mailbox){
            if($this->imapReopen($mailbox) == false){
                return $this;
            }
        }

        // call
        $s = $function($mailbox);
        if($s === false){
            return $this;
        }

        if(is_array($s)){
            if($s){
                $result += $s;
            }
        } else if(is_scalar($s)){
            if($s){
                $result .= $s;
            }
        }

        return $this;
    }






    private $_letter_count_all = 0;
    private $_letter_count_last = 1;
    private $_letter_count_step = 50;


    private function setLetterCountAll(){
        $this->_letter_count_all = imap_check($this->_imap_stream)->Nmsgs;
    }

    private function setDefaultLetterCount(){
        $this->_letter_count_all = 0;
        $this->_letter_count_last = 1;
    }

    private function getLetterSequencePrior(){
        if($this->_letter_count_all == false){
            return false;
        }

        if($this->_letter_count_all === 1){
            $this->setDefaultLetterCount();
            return 1;
        }

        $start = $this->_letter_count_last; // 101
        $end = $this->_letter_count_last - $this->_letter_count_step + 1;

        if($end <= 0){
            $end = 1;
        }

        $this->_letter_count_last = $end - 1; //101


        if($this->_letter_count_last <= 0){
            $this->setDefaultLetterCount();
        }

        return $end . ':' . $start;
    }


    /**
     * getImapFetchOverviewPrior - возвращает список писем с сервера в диапазоне, начиная с конца
     * @return array|void
     */
    private function getImapFetchOverviewPrior(){
        $sequence = $this->getLetterSequencePrior();
        if($sequence === false){
            return;
        }

        $result = imap_fetch_overview($this->_imap_stream, $sequence);

        return $result;
    }











    /*******************************************************
     *                  OTHER METHODS
    /******************************************************/



    /**
     * issetPropertiesInImapHeaderInfo - проверка существования опеределенного ключа в отвевете imap
     * @param stdClass $data
     * @return bool
     */
    private function issetPropertiesInImapHeaderInfo($data, $property_name_list){
        if(!($data instanceof stdClass)){
            return false;
        }

        $property_name_list = (array)$property_name_list;

        foreach($property_name_list as $property_name){
            if(property_exists($data, $property_name) == false){
                return false;
            }
        }

        return true;
    }





    private function checkUtf8($charset){
        if(strtolower($charset) != "utf-8"){
            return false;
        }
        return true;
    }


    private function detectEncoding($str){
        return mb_detect_encoding($str);
    }


    /**
     * Проверка существования кодировки
     * @param $encoding
     * @return bool
     */
    private function isSetCharset($charset){
        $list = mb_list_encodings();

        if($list == false){
            return false;
        }

        return in_array($charset, $list);
    }



    private function convertToCharset($in_charset, $out_charset = "utf-8", $str){
        if($this->isSetCharset($in_charset) == false){
            return $str;
        }

        try {
            if($in_charset == 'default'){
                $in_charset = 'utf-8';
            }

            $str = iconv(strtolower($in_charset), $out_charset, $str);
        } catch (Error $e){
        } catch (Exception $e){
        }

        return $str;
    }



    private function prepareLetterValue($value){
        if($value === null || $value === ''){
            return;
        }

        $in_charset = $this->detectEncoding($value);

        if($in_charset === false){
            return $value;
        }

        if($this->checkUtf8($in_charset) == false){
            $value = $this->convertToCharset($in_charset, 'utf-8', $value);
        }

        return $value;
    }




    private function prepareBody($body, $clear_response_text = false){
        if($body === null || $body === ''){
            return;
        }

        $in_charset = $this->detectEncoding($body);

        if($in_charset === false){
            return $body;
        }

        if($this->checkUtf8($in_charset) == false){
            $body = $this->convertToCharset($in_charset, 'utf-8', $body);
        }

        if($clear_response_text){
            $body = (new HelperEmail())
                        ->setHtmlText($body)
                        ->htmlToText()
                        ->getHtmlText();
        }

        return $body;
    }





    private function prepareBodyText($body, $clear_response_text = false){
        if($body === null || $body === ''){
            return;
        }

        $in_charset = $this->detectEncoding($body);

        if($this->checkUtf8($in_charset) == false){
            $body = $this->convertToCharset($in_charset, 'utf-8', $body);
        }

        if($clear_response_text){
            $body = (new HelperEmail())
                ->setHtmlText($body)
                ->htmlToTextEmails()
                ->htmlToText()
                ->clearResponseText()
                ->getHtmlText();
        }

        return $body;
    }




    private function UploadFile($file){
        $uploads_model = new UploadsModel();

        $uploads_model->setScenario(UploadsModel::SCENARIO_MODULE_MOVE_TO);
        $uploads_model->setThumbScenario($file['thumb_scenario']);
        $uploads_model->setFileType($file['file_type']);

        $uploads_model->file_source = UploadsModel::SOURCE_MODULE;
        $uploads_model->file_name = $file['origin_file_name'];
        $uploads_model->file_title = $file['origin_file_name'];
        $uploads_model->user_create = $this->_user_id;
        $uploads_model->status = 'temp';
        $uploads_model->copy_id = $file['copy_id'];

        $uploads_model->file_path_copy = YiiBase::app()->basePath. '/../' . $file['path'] . DIRECTORY_SEPARATOR . $file['tmp_file_name'];

        $uploads_model->save();

        return $uploads_model;
    }













}

