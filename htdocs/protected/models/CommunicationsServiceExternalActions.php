<?php
/**
 * CommunicationsServiceExternalActions - Работа с внешними источниками сервисов
 */

class CommunicationsServiceExternalActions{

    const ACTION_LOAD_EMAIL_LETTERS                     = 'load_email_letters';
    const ACTION_DELETE_EMAIL_LETTERS                   = 'delete_email_letters';
    const ACTION_SET_SEEN_EMAIL_LETTERS                 = 'set_seen_email_letters';
    const ACTION_SYNCHRONIZATION_DELETED_EMAIL_LETTERS  = 'synchronization_deleted_email_letters';


    private $_vars;

    private $_error = false;
    private $_error_cicle = false;

    private $_messages;
    private $_result = [];

    private $_logging_model;


    private function addErrorMessage($message, $params = null){
        $this->_error = true;
        $this->_error_cicle = true;

        $this->addMessage($message, $params);

        return $this;
    }


    private function addMessage($message, $params = null){
        $this->_messages[] = Yii::t('communications', $message, $params);

        return $this;
    }


    private function isError(){
        return $this->_error;
    }

    private function isErrorCicle(){
        return $this->_error_cicle;
    }


    private function getStatus(){
        return ($this->isError()) ? false : true;
    }


    public function getResult(){
        $result = array(
            'status' => $this->getStatus(),
            'messages' => $this->_messages,
        );

        if($this->_result){
            $result = array_merge($result, $this->_result);
        }

        return $result;
    }


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    private function getVars($key = null){
        if($key === null){
            return $this->_vars;
        }

        if($this->_vars && array_key_exists($key, $this->_vars)){
            return $this->_vars[$key];
        }
    }



    private function initLoggingModel($log_name){
        $rand = rand(111111, 999999);
        $prefix = '#'.$rand;

        $this->_logging_model = Logging::getInstance($prefix);
        $this->_logging_model
            ->setLogName($log_name)
            ->setAppendPrefixToMessage(true);

    }



    public function run($action_name){
        switch($action_name){
            case self::ACTION_LOAD_EMAIL_LETTERS :
                $this->actionLoadEmailLetters();
                break;

            case self::ACTION_DELETE_EMAIL_LETTERS :
                $this->actionDeleteEmailLetters();
                break;

            case self::ACTION_SET_SEEN_EMAIL_LETTERS :
                $this->actionSetSeenEmailLetters();
                break;

            case self::ACTION_SYNCHRONIZATION_DELETED_EMAIL_LETTERS:
                $this->actionSyncDeletedEmailLetters();
                break;
        }

        return $this;
    }






    /**
     * action "LoadEmailLetters" - Получаем все входящие сообщения всех пользователей
     */
    private function actionLoadEmailLetters(){
        $this->initLoggingModel('com-load-email-letters');

        ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_USERS)->getModule(false);

        // Получаем все модели пользователей с параметрами 'email' сервисов
        if($this->getVars('communications_service_params_id')){
            $communications_service_params_list[] = CommunicationsServiceParamsModel::model()->findByPk($this->getVars('communications_service_params_id'));
        } else{
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll('source_name = :source_name', array(':source_name' => 'email'));
        }

        $this->_logging_model->toFile('--- Start load email letters ---');

        $this->_logging_model->toFile('Find ' . count($communications_service_params_list) . ' communications');

        if($communications_service_params_list == false){
            $this->_logging_model->toFile('--- Done ---');
            return $this;
        }

        foreach ($communications_service_params_list as $communications_service_params){
            $params = $communications_service_params->getParamsJsonDecode();
            $this->_logging_model->toFile("#user_id: {$communications_service_params->user_id}, email: \"{$params['user_login']}\"");

            WebUser::setAutoSetUserId(false);
            WebUser::setUserId($communications_service_params->user_id);

            $email_letter_list = $this->_getEmailLetterListFromService($communications_service_params);

            // Сохранение сообщений
            $this->_saveLoadedEmailLetterList($email_letter_list, $communications_service_params);

        }

        $this->_logging_model->toFile('--- Done ---');

        return $this;
    }





    /**
     * action "DeleteEmailLetters" удаление писем из почтового сервера
     */
    private function actionDeleteEmailLetters(){
        if($this->getVars('communications_service_params_id')){
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll(array(
                'condition' => 'id=:id AND exists (SELECT users_id FROM {{mailer_letters_actions_scheduler}} WHERE users_id = t.user_id)',
                'params' => array(':id' => $this->getVars('communications_service_params_id')),
            ));
        } else {
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll(
                'exists (SELECT users_id FROM {{mailer_letters_actions_scheduler}} WHERE users_id = t.user_id)'
            );
        }

        if($communications_service_params_list == false){
            return;
        }

        foreach ($communications_service_params_list as $communications_service_params){
            $this->_runActionSchedulerToService($communications_service_params, SourceFactory::ACTION_DELETE_MESSAGE, MailerLettersActionsSchedulerModel::ACTION_DELETE);
        }
    }






    /**
     * action "actionLettersSetSeen" установка статуса на прочтение письма
     */
    private function actionSetSeenEmailLetters(){
        if($this->getVars('communications_service_params_id')){
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll(array(
                'condition' => 'id=:id AND exists (SELECT users_id FROM {{mailer_letters_actions_scheduler}} WHERE users_id = t.user_id)',
                'params' => array(':id' => $this->getVars('communications_service_params_id')),
            ));
        } else{
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll(
                'exists (SELECT users_id FROM {{mailer_letters_actions_scheduler}} WHERE users_id = t.user_id)'
            );
        }

        if($communications_service_params_list == false){
            return;
        }

        foreach ($communications_service_params_list as $communications_service_params){
            $this->_runActionSchedulerToService($communications_service_params, SourceFactory::ACTION_RUN_FLAG, MailerLettersActionsSchedulerModel::ACTION_SET_SEEN);
        }
    }




    /**
     * action "actionLettersSetSeen" установка статуса на прочтение письма
     */
    private function actionSyncDeletedEmailLetters(){
        // Получаем все модели пользователей с параметрами 'email' сервисов
        if($this->getVars('communications_service_params_id')){
            $communications_service_params_list[] = CommunicationsServiceParamsModel::model()->findByPk($this->getVars('communications_service_params_id'));
        } else{
            $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll('source_name = :source_name', array(':source_name' => 'email'));
        }

        if($communications_service_params_list == false){
            return;
        }

        foreach ($communications_service_params_list as $communications_service_params){
            $this->_syncDeletedEmailLetters($communications_service_params);
        }
    }





    private function _syncDeletedEmailLetters($communications_service_params){
        $result = (new CommunicationsSourceModel(
                        $communications_service_params->source_name,
                        $communications_service_params->service_name,
                        $communications_service_params->user_id))
                        ->runAction(SourceFactory::ACTION_GET_MESSAGE_ID_LIST)
                        ->getResult();

        if($result['status'] == false || $result['letter_list'] == false){
            return $this;
        }

        //$letters_id_list = $result['letter_list'];
        /// !!!

        return $this;
    }









    /**
     * _runActionSchedulerToService - выдполняет некие действия на сервере сервиса из данных планировщика
     */
    private function _runActionSchedulerToService($communications_service_params, $source_action, $action_name){
        $criteria = new CDbCriteria();
        $criteria->addCondition('users_id=:users_id AND action_name=:action_name');
        $criteria->params = array(
            ':users_id' => $communications_service_params->user_id,
            ':action_name' => $action_name,
        );

        $action_model_list = MailerLettersActionsSchedulerModel::model()->findAll($criteria);
        if($action_model_list == false){
            return;
        }

        $uid_list = CHtml::listData($action_model_list, 'scheduler_id', 'uid');

        $vars = array('uid_list' => $uid_list);

        switch($action_name){
            case MailerLettersActionsSchedulerModel::ACTION_SET_SEEN:
                $vars['flag'] = EmailFactory::FLAG_SET_SEEN;
                break;
        }

        $result = (new CommunicationsSourceModel(
                        $communications_service_params->source_name,
                        $communications_service_params->service_name,
                        $communications_service_params->user_id))
                    ->runAction($source_action, $vars)
                    ->getResult();

        if($result['status'] && $result['uid_list']){
            $criteria->addInCondition('uid', $result['uid_list']);
            MailerLettersActionsSchedulerModel::model()->deleteAll($criteria);
        }
    }






    /**
     * _getEmailLetterListFromService - загружает список писем из сервиса пользователя
     * @param $communication_service_params_model
     */
    private function _getEmailLetterListFromService($communication_service_params_model){
        $this->_logging_model->toFile("Get email letters from service...");

        $letter_header_list = $this->_getEmailLetterHeaderListFromService($communication_service_params_model);

        if($letter_header_list == false){
            $this->_logging_model->toFile("Find 0 letters");
            return;
        }

        $letter_body_list = $this->_getEmailLetterBodyListFromService($communication_service_params_model, array('letter_header_list' => $letter_header_list));

        $this->_logging_model->toFile("Find ".count($letter_body_list)." letters");

        return $letter_body_list;
    }









    private function _getEmailLetterHeaderListFromService($communication_service_params_model){
        $vars = array(
            'last_uid' => $this->getLastGettingUID($communication_service_params_model, MailerLettersUidGettingModel::MAILBOX_NAME_INBOX),
        );

        $letter_header_list = (new CommunicationsSourceModel($communication_service_params_model->source_name, $communication_service_params_model->service_name, $communication_service_params_model->user_id))
                        ->runAction(SourceFactory::ACTION_GET_MESSAGE_HEADER, $vars)
                        ->getResult();

        if($letter_header_list['status'] == false || empty($letter_header_list['letter_list'])){
            return;
        }

        // ОБРАТОТКА ПОЛУЧЕННЫХ ХИДЕРОВ - удаление дублей по message_id
        $result = array();

        foreach ($letter_header_list['letter_list'] as $letter_header) {
            $result[] = array(
                            'uid' => $letter_header['uid'],
                            'mailer_box_name' => $letter_header['mailer_box_name'],
                        );
        }

        if($result){
            return $result;
        }
    }


    /**
     * _getEmailLetterBodyListFromService - загружаем BODY и ATTACHMENTS писем
     * @param $communication_service_params_model
     * @param $letter_body_list
     */
    private function _getEmailLetterBodyListFromService($communication_service_params_model, $letter_header_list){
        $letter_body_list = (new CommunicationsSourceModel($communication_service_params_model->source_name, $communication_service_params_model->service_name, $communication_service_params_model->user_id))
                                    ->runAction(SourceFactory::ACTION_GET_MESSAGE_BODY, $letter_header_list)
                                    ->getResult();

        if(!empty($letter_body_list['letter_list'])){
            return $letter_body_list['letter_list'];
        }
    }





    /**
     * _getOutboxLettersModelListByMessageId - ищет в определенной сущности отправленное письмо по его $message_id
     */
    private function _getOutboxLettersModelListByMessageId($message_id){
        $criteria = new CDbCriteria();
        $criteria->with = array(
            'mailerOutboxParams' => array(
                'select' => false,
            ),
        );

        $criteria->addCondition('mailerOutboxParams.message_id=:message_id');
        $criteria->params = array(
            ':message_id' => $message_id,
        );

        $model_list = MailerLettersOutboxModel::model()->findAll($criteria);

        return $model_list;
    }




    /**
     * _getInboxLettersModelListByMessageId - ищет в определенной сущности принятое письмо по его $message_id
     */
    private function _getInboxLettersModelListByMessageId($message_id){
        $criteria = new CDbCriteria();
        $criteria->with = array(
            'mailerInboxParams' => array(
                'select' => false,
            ),
        );

        $criteria->addCondition('mailerInboxParams.message_id=:message_id');
        $criteria->params = array(
            ':message_id' => $message_id,
        );

        $model_list = MailerLettersInboxModel::model()->findAll($criteria);

        return $model_list;
    }


    /**
     * _hasOutboxLettersByMailerId - возвращает наличие исходящих писем по его $mailer_id
     */
    private function _getOutboxLettersModelByMailerId($mailer_id){
        if($mailer_id == false){
            return false;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('mailer_id=:mailer_id');
        $criteria->params = array(
            ':mailer_id' => $mailer_id,
        );

        $model = MailerLettersOutboxModel::model()->find($criteria);

        return $model;
    }


    /**
     * _getMailerIdFromLetterBody - ищет mailer_id в теле письма
     */
    private function _getMailerIdFromLetterBody($letter_body){
        if($letter_body == false){
            return;
        }

        if(preg_match('~^(<img){1}.+(tgCrmLabel).*[>]{1}~', $letter_body) == false){
            return;
        }

        $dom = new DOMDocument;
        $dom->loadHTML($letter_body);
        $images = $dom->getElementsByTagName('img');

        if($images == false){
            return;
        }

        if($images[0]->getAttribute('class') == 'tgCrmLabel'){
            return $images[0]->getAttribute('data-id');
        }
    }


    /**
     * _prepareEmailLetterDataForSave - обработка данных письма перед сохранением
     */
    private function _prepareEmailLetterDataForSave(&$email_letter){
        $subject = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $email_letter['subject']);
        
        $function_pre_subject = function($subject){
            $template_list = array(
                '/^(Re\[.*\]: )+/',
                '/^(RE\[.*\]: )+/',
                '/^(re\[.*\]: )+/',
                '/^(Fwd\[.*\]: )+/',
                '/^(FWD\[.*\]: )+/',
                '/^(fwd\[.*\]: )+/',
                '/^(Re: )+/',
                '/^(RE: )+/',
                '/^(re: )+/',
                '/^(Fwd: )+/',
                '/^(FWD: )+/',
                '/^(fwd: )+/',
            );
            foreach($template_list as $tempalate){
                $subject = preg_replace($tempalate, '', $subject);
            }
            return $subject;
        };

        while(true){
            $subject_before = $subject;
            $subject = $function_pre_subject($subject);

            if($subject_before == $subject){
                break;
            }
        }

        if($subject === '' || $subject === null){
            $subject = \Yii::t('communications', '--- without title ---');
        }

        $email_letter['subject'] = $subject;
        $email_letter['body_text'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $email_letter['body_text']);
        $email_letter['type_comment'] = \ActivityMessagesModel::TYPE_COMMENT_EMAIL;
    }



    /**
     * Возвращает uid последнего загруженного письма
     *  - из $communication_service_params_model или
     *  - из _vars['last_getting_uid']
     */
    private function getLastGettingUID($communication_service_params_model, $mailbox_name){
        if($this->getVars('last_getting_uid')){
            return $this->getVars('last_getting_uid');
        }

        if($communication_service_params_model == false){
            return;
        }

        $params = $communication_service_params_model->getParamsJsonDecode();

        if($params == false || key_exists('user_login', $params) == false){
            return;
        }

        $user_login = $params['user_login'];

        $uid_getting_model = MailerLettersUidGettingModel::model()
                                    ->setScopeEmail($user_login)
                                    ->setScopeMailboxName($mailbox_name)
                                    ->find();

        if($uid_getting_model){
            return $uid_getting_model->uid;
        }
    }




    /**
     * Обновляем uid
     */
    private function updateLastGettingUID($communication_service_params_model, $uid){
        if($this->getVars('update_last_getting_messages_uid_in_db') === false){
            return;
        }

        if($uid == false){
            return;
        }

        $params = $communication_service_params_model->getParamsJsonDecode();
        if($params == false){
            return;
        }

        $attributes = [
            'email' => $params['user_login'],
            'mailbox_name' => MailerLettersUidGettingModel::MAILBOX_NAME_INBOX,
            'uid' => $uid,
        ];

        $this->_logging_model->toFile("Update last getting UID to " . $params['user_login'] . ' -> ' . $uid);

        MailerLettersUidGettingModel::insertOrUpdate($attributes);
    }






    /**
     * _saveLoadedEmailLetterList - сохранение списка email-сообщений
     */
    private function _saveLoadedEmailLetterList($email_letter_list, $communication_service_params_model){
        $this->_messages[] = '--------';
        $this->_messages[] = 'users_id - ' . WebUser::getUserId();
        $this->_messages[] = 'total_all - ' . count($email_letter_list);

        $total_new = 0;
        $count_saved = 0;
        $this->_error_cicle = false;

        if($email_letter_list == false){
            return;
        }

        $this->_logging_model->toFile("Save letters:");

        foreach($email_letter_list as $email_letter){
            $this->_logging_model->toFile("Letter #" . $email_letter['uid']);
            $total_new++;

            // prepare $email_letter
            $this->_prepareEmailLetterDataForSave($email_letter);


            $vars = array(
                'communiction_service_params_model' => $communication_service_params_model,
                'email_letter' => $email_letter,
            );

            // save
            $r = $this->_saveLoadedEmailLetter($vars);

            if($this->isErrorCicle()){
                $this->_logging_model->toFile("Skip");
                break;
            } else {
                if($r){
                    $this->_logging_model->toFile("Saved");
                    $count_saved++;
                } else {
                    $this->_logging_model->toFile("Not saved");
                }
            }

            $this->updateLastGettingUID($communication_service_params_model, $email_letter['uid']);
        }

        $this->_logging_model->toFile("Total new: " . $total_new);
        $this->_logging_model->toFile("Total saved: " . $count_saved);

        $this->_messages[] = 'total_new - ' . $total_new;
        $this->_messages[] = 'count_saved - ' . $count_saved;

        $this->_logging_model->toFile("Save done");
    }




    /**
     * _saveLoadedEmailLetter - сохранение сообщения
     */
    private function _saveLoadedEmailLetter($vars){
        // проверка и сохранение входящего письма
        $r =
        (
            $this->_saveLoadedEmailLetterIfIsLetterDouble($vars)
            ||
            $this->_saveLoadedEmailLetterIfReference($vars)
            ||
            $this->_saveLoadedEmailLetterIfExist($vars)
            ||
            $this->_saveLoadedEmailLetterIfNew($vars)
        );

        return $r;
    }





    /**
     * 0. _saveLoadedEmailLetterIfIsLetterDouble - проверка письма с базой отпаравленных и принятых писем.
     *                                          Если удовлетворительно - сохраняем само письмо и связываем
     *                                          с существующим уведомлением активности
     */
    private function _saveLoadedEmailLetterIfIsLetterDouble($vars){
        $email_letter = $vars['email_letter'];

        ($letters_model_list = $this->_getOutboxLettersModelListByMessageId($email_letter['message_id']))
        ||
        ($letters_model_list = $this->_getInboxLettersModelListByMessageId($email_letter['message_id']))
        ||
        ($letters_model_list = $this->_getOutboxLettersModelByMailerId($this->_getMailerIdFromLetterBody($email_letter['body'])));

        if($letters_model_list == false){
            $this->_logging_model->toFile(" - IfIsLetterDouble(1) - false");
            return false;
        }

        $activity_messages_model = $this->getActivityMessagesModelByLettersModelList($letters_model_list);

        $data_id = null;

        if($activity_messages_model){
            $data_id = $activity_messages_model->data_id;
        }

        $this->_logging_model->toFile(" - IfIsLetterDouble - true");

        $this->_saveLoadedEmailLetterChannel_MessageData($data_id, $vars['email_letter'], true, $activity_messages_model->activity_messages_id);

        if($data_id){
            $this->checkAndAppendParticipant($data_id, WebUser::getUserId());
        }

        return true;
    }


    /**
     * getActivityMessagesModelByLettersModelList - ищет в массиме первую модель activityMessages и возвращает
     * @param $letters_model_list
     */
    private function getActivityMessagesModelByLettersModelList($letters_model_list){
        if($letters_model_list == false){
            return;
        }

        foreach($letters_model_list as $letters_model){
            if($letters_model instanceof MailerLettersOutboxModel){
                $activity_messages_model = $letters_model->mailerOutboxRelate->activityMessages;
            } elseif($letters_model instanceof MailerLettersInboxModel){
                $activity_messages_model = $letters_model->mailerInboxRelate->activityMessages;
            }

            if($activity_messages_model){
                return $activity_messages_model;
            }
        }
    }



    /**
     * 1. _saveLoadedEmailLetter - сохранение сообщения как отвеченого
     */
    private function _saveLoadedEmailLetterIfReference($vars){
        if($this->isErrorCicle()){
            return false;
        }

        $email_letter = $vars['email_letter'];

        $reference = mb_substr($email_letter['references'], 0, 10000);
        $in_reply_to = $email_letter['in_reply_to'];
        if($reference == false && $in_reply_to == false){
            $this->_logging_model->toFile(" - IfReference(1) - false");
            return false;
        }


        // outbox
        ($letter_outbox_params_model = MailerLettersOutboxParamsModel::model()->setScopeMessageId($reference)->find())
        ||
        ($letter_outbox_params_model = MailerLettersOutboxParamsModel::model()->setScopeMessageId($in_reply_to)->find());

        if($letter_outbox_params_model){
            $letter_outbox_relate_model = $letter_outbox_params_model->mailerOutboxRelate;
            if($letter_outbox_relate_model){
                $activity_message_model = $letter_outbox_relate_model->activityMessages;
            }
        }

        // inbox (если письмо пришло из ящика авторассылки...)
        if(empty($activity_message_model)){
            ($letter_inbox_params_model = MailerLettersInboxParamsModel::model()->setScopeMessageId($reference)->find())
            ||
            ($letter_inbox_params_model = MailerLettersInboxParamsModel::model()->setScopeMessageId($in_reply_to)->find());

            if($letter_inbox_params_model){
                $letter_inbox_relate_model = $letter_inbox_params_model->mailerInboxRelate;
                if($letter_inbox_relate_model){
                    $activity_message_model = $letter_inbox_relate_model->activityMessages;
                }
            }
        }

        if(empty($activity_message_model)){
            $this->_logging_model->toFile(" - IfReference(2) - false");
            return false;
        }

        // поиск data_id
        $data_id = $activity_message_model->data_id;
        if($data_id == false){
            $this->_logging_model->toFile(" - IfReference(3) - false");
            return false;
        }

        $this->_logging_model->toFile(" - IfReference - true");

        $this->_saveLoadedEmailLetterChannel_MessageData($data_id, $email_letter);
        $this->_saveLoadedEmailLetterChannel_Participant($data_id, $email_letter);


        return true;
    }


    /**
     * 2. _saveLoadedEmailLetter - сохранение сообщения как найденного по названию
     */
    private function _saveLoadedEmailLetterIfExist($vars){
        if($this->isErrorCicle()){
            return false;
        }

        $email_letter = $vars['email_letter'];

        $copy_id = \ExtensionCopyModel::MODULE_COMMUNICATIONS;

        $module_title = mb_substr($email_letter['subject'], 0, 255);

        $communication_model_list = \CommunicationsModel::model()
                                        ->with(array(
                                            'activityMessages' => array(
                                                'select' => false,
                                            )
                                        ))
                                        ->findAll(array(
                                            'condition' => 'module_title=:module_title',
                                            'params' => array(
                                                ':module_title' => $module_title,
                                            ),
                                            'order' => 'activityMessages.date_create desc, t.date_create desc',
                                        ));

        if($communication_model_list == false){
            $this->_logging_model->toFile(" - IfExist(1) - false");
            return false;
        }

        foreach($communication_model_list as $communication_model){
            $b2 = ParticipantEmailModel::hasParticipantEmail($copy_id, $communication_model->communications_id, $this->_getLetterEmailEntityList($email_letter['email_all']));

            // если емейл совпадает с емейлов данного пользователя, что введен в параметрах активности - добавляем как участника
            if(!$b2){
                continue;
            }

            $this->_logging_model->toFile(" - IfExist - true");

            $this->_saveLoadedEmailLetterChannel_MessageData($communication_model->getPrimaryKey(), $email_letter);
            $this->_saveLoadedEmailLetterChannel_Participant($communication_model->getPrimaryKey(), $email_letter);


            return true;
        }

        $this->_logging_model->toFile(" - IfExist(2) - false");

        return false;
    }



    /**
     * 3. _saveLoadedEmailLetterIfNew - сохранение сообщения как нового
     */
    private function _saveLoadedEmailLetterIfNew($vars){
        if($this->isErrorCicle()){
            return false;
        }

        $this->_logging_model->toFile(" - IfNew - true");

        $email_letter = $vars['email_letter'];
        $data_id = null;

        if($email_letter['body_text'] !== ""){
            $edit_data = [
                'EditViewModel' => [
                    'module_title' => mb_substr($email_letter['subject'], 0, 255),
                    'communication_source' => CommunicationsCommunicationSourceModel::getSourceIdBySlug(CommunicationsCommunicationSourceModel::SOURCE_SLUG_EMAIL),
                ],
                'block_attributes' => [
                    'block_participant' => [
                        'participant' => [
                            'element_participant' => [
                                [
                                    'participant_id' => null,
                                    'ug_id' => WebUser::getUserId(),
                                    'ug_type' => ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                                    'responsible' => 1,
                                ]
                            ]
                        ]
                    ],
                ],
            ];

            $edit_view_model = $this->_saveLoadedEmailLetter_ChannelData($edit_data);
            if($edit_view_model){
                $data_id = $edit_view_model->getPrimaryKey();
                $this->_saveLoadedEmailLetterChannel_Participant($data_id, $email_letter, false);
            }
        }

        $this->_saveLoadedEmailLetterChannel_MessageData($data_id, $email_letter);

        return true;
    }


    /**
     * _saveLoadedEmailLetter_ChannelData - сохранение данных о Канале
     */
    private function _saveLoadedEmailLetter_ChannelData($edit_data){
        if($this->isErrorCicle()){
            return false;
        }

        $eva_model = new EditViewActionModel(\ExtensionCopyModel::MODULE_COMMUNICATIONS);
        $eva_model
            ->setEditData($edit_data)
            ->setEditViewIsNewRecord(true)
            ->createEditViewModel();
        $eva_model->getEditModel()
            ->setSwitchRunProcess(false);
        $eva_model->save();


        $eva_model->getEditModel()->destroyInstance();

        if($eva_model->isBadStatus()){
            $this->_logging_model->toFile("     Insert new chennel - false");
            $this->addErrorMessage('Error save channel data');
            return false;
        } else {
            $this->_logging_model->toFile("     Insert new chennel - true");

            return $eva_model->getEditModel();
        }
    }



    /**
     * _getLetterEmailEntityList - возвращает список сущностей емейл адреса: список адресов или список названий адресов
     * @param $letter_email
     * @param string $key - ключ сущности, что будет возвращена: title|email
     * @return array
     */
    private function _getLetterEmailEntityList($letter_email, $key = 'email'){
        $result = array();

        if(empty($letter_email)){
            return $result;
        }

        foreach($letter_email as $email){
            $result[] = $email[$key];
        }

        return $result;
    }





    /**
     * _saveLoadedEmailLetterChannel_MessageData - сохранение данных самого сообщения
     */
    private function _saveLoadedEmailLetterChannel_MessageData($data_id, $email_letter, $save_only_letter = false, $activity_messages_id = null){
        if($this->isErrorCicle()){
            return false;
        }

        $this->_logging_model->toFile("     Save letter. #{$email_letter['uid']}");

        $copy_id = \ExtensionCopyModel::MODULE_COMMUNICATIONS;

        // 1. save ActivityMessagesModel
        if($save_only_letter == false && $email_letter['body_text'] !== "" && $data_id){
            $attributes = array(
                'date_create' => date('Y-m-d H:i:s', $email_letter['date']), //date('Y-m-d H:i:s'),
                'copy_id' => $copy_id,
                'data_id' => $data_id,
                'user_create' => WebUser::getUserId(),
                'message' => json_encode(
                    array(
                        'subject' => $email_letter['subject'],
                        'sender' => $email_letter['email_from']['email'],
                        'message' => $email_letter['body_text'],
                    )),
                'attachment' => $email_letter['file_id_list'],
                'type_comment' => \ActivityMessagesModel::TYPE_COMMENT_EMAIL,
                'status' => 'asserted',
            );

            // 1. save ActivityMessages
            $activity_model = new ActivityMessagesModel('external_insert');
            $activity_model
                ->setUserIsEmpty(true)
                ->setHistorySave(false)
                ->setMyAttributes($attributes, false);

            if($activity_model->save() == false){
                $this->_logging_model->toFile("     - ActivityMessagesModel - false");
                $this->addErrorMessage('#' . $data_id . '. ActivityMessagesModel. Error save');
                return false;
            }

            $activity_messages_id = $activity_model->getPrimaryKey();

            $this->_logging_model->toFile("     - ActivityMessagesModel[#{$activity_messages_id}] - true");
        } else {
            $this->_logging_model->toFile("     - ActivityMessagesModel - cancel");
        }


        // 2. save MailerLettersInboxModel
        $attributes = array(
            'user_create' => $email_letter['user_id'],
            'date_receipt' => date('Y-m-d H:i:s', $email_letter['date']),
            'letter_from' => mb_substr($email_letter['email_from']['email'], 0, 1000),
            'letter_from_name' => mb_substr($email_letter['email_from']['title'], 0, 1000),
            'letter_to' => mb_substr(implode(',', array_merge($this->_getLetterEmailEntityList($email_letter['email_to']), $this->_getLetterEmailEntityList($email_letter['email_copy']))), 0, 3000),
            'letter_to_name' => '',
            'letter_subject' => mb_substr($email_letter['subject'], 0, 3000) ,
            'letter_body' => ($save_only_letter == false ? $email_letter['body'] : ''),
        );

        $inbox_model = new \MailerLettersInboxModel();
        $inbox_model->setAttributes($attributes);

        try {
            if($inbox_model->save() == false){
                $this->_logging_model->toFile("     - MailerLettersInboxModel - false");
                return false;
            }
        } catch (\Error $e) {
            $this->_logging_model->toFile("     - MailerLettersInboxModel - false" . $e->getMessage());
            return false;
        } catch (CDbException $e) {
            $this->_logging_model->toFile("     - MailerLettersInboxModel - false" . $e->getMessage());
            return false;
        } catch (\Exception $e){
            $this->_logging_model->toFile("     - MailerLettersInboxModel - false" . $e->getMessage());
            return false;
        }

        $email_letter['mailer_id'] = $inbox_model->getPrimaryKey();
        $this->_logging_model->toFile("     - MailerLettersInboxModel[#{$email_letter['mailer_id']}] - true");

        // 3. save MailerLettersInboxRelateModel
        if($activity_messages_id){
            $attributes = array(
                'mailer_id' => $email_letter['mailer_id'],
                'relate_id' => $activity_messages_id,
                'resource_type' => MailerLettersInboxRelateModel::RESOURCE_TYPE_ACTIVITY,
            );

            $model = new MailerLettersInboxRelateModel();
            $model->setAttributes($attributes);

            if($model->save() == false){
                $this->_logging_model->toFile("     - MailerLettersInboxRelateModel - false");
                $this->addErrorMessage('#' . $data_id . '. MailerLettersInboxRelateModel. Error save!');
                return false;
            } else {
                $id = $model->getPrimaryKey();
                $this->_logging_model->toFile("     - MailerLettersInboxRelateModel[$id] - true");
            }
        } else {
            $this->_logging_model->toFile("     - MailerLettersInboxRelateModel - cancel");
        }


        // 4. save MailerLettersInboxParamsModel
        $attributes = array(
            'mailer_id' => $email_letter['mailer_id'],
            'message_id' => $email_letter['message_id'],
            'reference' => mb_substr($email_letter['references'], 0, 10000),
            'in_reply_to' => mb_substr($email_letter['in_reply_to'], 0, 10000),
            'uid' => $email_letter['uid'],
        );

        $model = new MailerLettersInboxParamsModel();
        $model->setAttributes($attributes);

        if($model->save() == false){
            $this->_logging_model->toFile("     - MailerLettersInboxParamsModel - false");
            $this->addErrorMessage('#' . $data_id . '. MailerLettersInboxParamsModel. Error save!');
            return false;
        } else {
            $id = $model->getPrimaryKey();
            $this->_logging_model->toFile("     - MailerLettersInboxParamsModel[$id] - true");
        }

        // 5. MailerLettersInboxFilesModel - copyModuleFiles
        if($save_only_letter == true){
            $this->_logging_model->toFile("     - MailerLettersInboxFilesModel - skiped");
        } else {
            if(!empty($email_letter['file_id_list'])){
                $inbox_files_model = new MailerLettersInboxFilesModel();
                $inbox_files_model->setScenario(UploadsModel::SCENARIO_EMAIL_COPY_TO);
                $inbox_files_model->copyModuleFiles($email_letter);
                $this->_logging_model->toFile("     - MailerLettersInboxFilesModel - true");
            } else{
                $this->_logging_model->toFile("     - MailerLettersInboxFilesModel - not files");
            }
        }

        return true;
    }





    /**
     * _saveLoadedEmailLetterChannel_Participant - сохранение участников Канала
     */
    private function _saveLoadedEmailLetterChannel_Participant($data_id, $email_letter, $check_user_email = true){
        $this->_logging_model->toFile("     Insert new participant...");

        if($this->isErrorCicle() || empty($email_letter['email_all'])){
            $this->_logging_model->toFile("     false");
            return false;
        }
        $copy_id = \ExtensionCopyModel::MODULE_COMMUNICATIONS;

        $participant_list = array();
        $participant_email_list = array();
        $emails = array();

        $email_list_added = array();

        if($check_user_email){
            $user_email = UsersModel::getUserModel()->email;
        }

        // подготовка данных email адресов
        foreach($email_letter['email_all'] as $email_data){
            $email = $email_data['email'];

            // проверка на дубль
            if(in_array($email, $email_list_added)) continue;
            $email_list_added[] = $email;

            if($check_user_email && $email == $user_email) continue;


            // если емейл совпадает с емейлов данного пользователя, что введен в параметрах активности - добавляем как участника
            $communiction_service_params_model = (new CommunicationsServiceParamsModel())->findByUserLogin($email);
            if($communiction_service_params_model){
                if(ParticipantModel::hasParticipant($copy_id, $data_id, $communiction_service_params_model->user_id, \ParticipantModel::PARTICIPANT_UG_TYPE_USER)){
                    continue;
                }
                $participant_list[] = array(
                    'copy_id' => $copy_id,
                    'data_id' => $data_id,
                    'ug_id' => $communiction_service_params_model->user_id,
                    'ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                );
            } else {
                // добавляем как emeil-участника
                $emails_model = EmailsModel::findByEmail($email);
                if($emails_model){
                    if(ParticipantEmailModel::hasParticipant($copy_id, $data_id, $emails_model->email_id)){
                        continue;
                    }
                    $participant_email_list[] = array(
                        'email_id' => $emails_model->email_id,
                        'copy_id' => $copy_id,
                        'data_id' => $data_id,
                        'email' => $email_data['email'],
                        'title' => ($email_data['title'] ? $email_data['title'] : null),
                    );
                    continue;
                }
                $emails[] = array(
                    'email_id' => null,
                    'copy_id' => $copy_id,
                    'data_id' => $data_id,
                    'email' => $email_data['email'],
                    'title' => ($email_data['title'] ? $email_data['title'] : null),
                );
            }
        }

        // insert
        $this->_insertParticipant($participant_list);
        $this->_insertParticipantEmail($participant_email_list);
        $this->_insertEmails($emails);

        $this->_logging_model->toFile("     true");

        return $this;
    }





    private function checkAndAppendParticipant($data_id, $users_id){
        if(!$data_id || !$users_id){
            return false;
        }

        $participant_model = ParticipantModel::model()
                ->find(array(
                    'condition' => 'copy_id =:copy_id AND data_id =:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
                    'params' => array(
                        ':copy_id' => \ExtensionCopyModel::MODULE_COMMUNICATIONS,
                        ':data_id' => $data_id,
                        ':ug_id' => \WebUser::getUserId(),
                        ':ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                    ),
                ));

        if($participant_model){
            return;
        }



        $participant_list = array(
            array(
                'copy_id' => \ExtensionCopyModel::MODULE_COMMUNICATIONS,
                'data_id' => $data_id,
                'ug_id' => \WebUser::getUserId(),
                'ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
            )
        );
        $this->_insertParticipant($participant_list);
    }



    /**
     * _insertParticipant - сохранение участников
     */
    private function _insertParticipant($participant_list){
        if($participant_list == false){
            return;
        }

        foreach($participant_list as $participant){
            $participant_model = new \ParticipantModel();
            $participant_model->setMyAttributes($participant);
            $participant_model->save();
        }
    }


    /**
     * _insertParticipantEmail - сохранение емелов-участников
     */
    private function _insertParticipantEmail($participant_list){
        if($participant_list == false){
            return;
        }

        foreach($participant_list as $participant){
            if(empty($participant['email_id'])){
                continue;
            }

            $attributes = array(
                'copy_id' => $participant['copy_id'],
                'data_id' => $participant['data_id'],
                'email_id' => $participant['email_id'],
            );

            $participant_model = new \ParticipantEmailModel();
            $participant_model->setMyAttributes($attributes);
            $participant_model->save();

            if(UsersModel::hasStorageEmailId($participant['email_id']) == false){
                $storage_email_model = new UsersStorageEmailModel();
                $storage_email_model->setAttributes(array(
                    'users_id' => \WebUser::getUserId(),
                    'email_id' => $participant['email_id'],
                ));
                $storage_email_model->save();
            }

        }
    }



    /**
     * _insertEmails - созранение новый емейлов и емейлов-участников
     */
    private function _insertEmails($participant_list, $insert_participant_email = true){
        if($participant_list == false){
            return;
        }

        foreach($participant_list as &$participant){
            $attributes = array(
                'email' => $participant['email'],
                'title' => $participant['title'],
            );

            $emails_model = new \EmailsModel();
            $emails_model->setScenario('insert_communication');
            $emails_model->setAttributes($attributes);

            try{
                $emails_model = $emails_model->saveUnique();
            } catch (\Error $e) {
                return;
            } catch (CDbException $e) {
                return;
            } catch (\Exception $e){
                return;
            }



            if($emails_model){
                $participant['email_id'] = $emails_model->getPrimaryKey();
            }
        }

        if($insert_participant_email){
            $this->_insertParticipantEmail($participant_list);
        }
    }



}
