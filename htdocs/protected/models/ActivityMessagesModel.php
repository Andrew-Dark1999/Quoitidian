<?php

/**
 * ActivityMessagesModel
 *
 * @author Alex R.
 */

class ActivityMessagesModel extends ActiveRecord
{

    const TYPE_COMMENT_GENERAL = 'general';
    const TYPE_COMMENT_EMAIL = 'email';

    public $tableName = 'activity_messages';

    public $status = 'temp';

    public $type_comment = self::TYPE_COMMENT_GENERAL;

    private $_history_save = true;

    private $_user_is_empty = false;

    private $changed_copy_id_to_communications = false;

    /**
     * список UploadsModel.id вложений, что будут прикреплены к сообщению при сохранении
     *
     * @var int|array
     */
    private $_files;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return [
            ['copy_id, data_id', 'numerical', 'integerOnly' => true, 'on' => 'insert'],
            ['date_create, date_edit, user_create, user_edit, copy_id, data_id, attachment', 'safe', 'on' => 'insert'],
            ['message', 'validateMessage', 'on' => 'insert'],
        ];
    }

    public function relations()
    {
        return [
            'staff'                          => [self::BELONGS_TO, 'StaffModel', 'user_create'],
            'mailerOutboxRelate'             => [self::HAS_ONE, 'MailerLettersOutboxRelateModel', 'relate_id', 'on' => 'resource_type="' . MailerLettersOutboxRelateModel::RESOURCE_TYPE_ACTIVITY . '"'],
            'mailerInboxRelate'              => [self::HAS_ONE, 'MailerLettersInboxRelateModel', 'relate_id', 'on' => 'resource_type="' . MailerLettersInboxRelateModel::RESOURCE_TYPE_ACTIVITY . '"'],
            'communicationsActivityMarkView' => [self::HAS_ONE, 'CommunicationsActivityMarkViewModel', 'activity_messages_id'],
        ];
    }

    public function setHistorySave($history_save)
    {
        $this->_history_save = $history_save;

        return $this;
    }

    public function setUserIsEmpty($user_is_empty)
    {
        $this->_user_is_empty = $user_is_empty;

        return $this;
    }

    public function setScopeStatus($status = 'asserted')
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'status=:status',
            'params'    => [':status' => $status],
        ]);

        return $this;
    }

    private function getMessage($json_decode = false)
    {
        if ($this->message == false) {
            return;
        }
        if ($json_decode) {
            $message_json = json_decode($this->message, true);
            if ($message_json == false || is_array($message_json) == false) {
                return;
            } else {
                return $message_json;
            }
        }

        return $this->message;
    }

    private static function getTypeCommentTitle($type_comment)
    {
        switch ($type_comment) {
            case self::TYPE_COMMENT_GENERAL :
                return Yii::t('base', 'Message');
            case self::TYPE_COMMENT_EMAIL :
                return Yii::t('base', 'Email');
        }
    }

    public static function updateDateEditByLetter($letter_model)
    {
        $outbox_relate_model = $letter_model->mailerOutboxRelate;
        if ($outbox_relate_model) {
            $activity_model = $outbox_relate_model->activityMessages;
            if ($activity_model) {
                $activity_model->date_edit = date('Y-m-d H:i:s');
                $activity_model->save();
            }
        }

    }

    /**
     * getTypeCommentTitleList - возвращает список названий
     *
     * @param $type_comment_list
     * @return array
     */
    public static function getTypeCommentTitleList($type_comment_list)
    {
        $result = [];

        if ($type_comment_list == false) {
            return $result;
        }

        foreach ($type_comment_list as $type_comment) {
            $result[$type_comment] = self::getTypeCommentTitle($type_comment);
        }

        return $result;
    }

    /**
     * Возвращает список констант TypeComment, допустимых в определенном модуле
     */
    public static function getTypeCommentList($copy_id, $only_content_general = false)
    {
        $list = [
            self::TYPE_COMMENT_GENERAL,
        ];

        if ($only_content_general) {
            return $list;
        }

        $use = (new \ExtensionCopyModel())->useCommunicationFunctional($copy_id);

        if ($use) {
            if ($copy_id == ExtensionCopyModel::MODULE_COMMUNICATIONS) {
                $list = [
                    self::TYPE_COMMENT_EMAIL,
                ];
            } else {
                $list[] = self::TYPE_COMMENT_EMAIL;
            }
        }

        return $list;
    }

    public function setMyAttributes($attribute_list, $prepare_attr = true)
    {
        if ($prepare_attr) {
            $this->prepareAttributes($attribute_list);
        }

        foreach ($attribute_list as $key => $value) {
            if ($key == 'data_id') {
                if (!empty($value)) {
                    $this->status = 'asserted';
                }
                $this->{$key} = $value;
            } else {
                if ($key == 'message' && is_array($value)) {
                    $this->{$key} = json_encode($value);
                } else {
                    if ($key == 'attachment') {
                        $this->_files = $value;
                    } else {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    private function prepareAttributes(&$attribute_list)
    {
        switch ($attribute_list['type_comment']) {
            case self::TYPE_COMMENT_EMAIL:
                $this->prepareAttributesCommunications($attribute_list);
                break;
        }
    }

    private function getEditViewModel($copy_id, $data_id)
    {
        $ev_action_model = (new EditViewActionModel($copy_id))
            ->setEditData(['id' => $data_id])
            ->createEditViewModel();

        $edit_view_model = $ev_action_model->getEditModel();

        return $edit_view_model;
    }

    /**
     * prepareAttributesCommunications - подготовка аттрибутов письма для Коммуникаций
     *
     * @param $attribute_list
     */
    private function prepareAttributesCommunications(&$attribute_list)
    {
        if ($attribute_list['copy_id'] != ExtensionCopyModel::MODULE_COMMUNICATIONS) {
            $this->changed_copy_id_to_communications = true;
            $attribute_list['data_id'] = $attribute_list['channel_data_id'];
            $attribute_list['copy_id'] = ExtensionCopyModel::MODULE_COMMUNICATIONS;
            $attribute_list['subject'] = $this->getEditViewModel($attribute_list['copy_id'], $attribute_list['data_id'])->module_title;
        }

        $recipients_list = null;

        // список адресатов для отсылке по емейлу
        $recipients_list = $this->getRecipientsList($attribute_list);

        if ($recipients_list) {
            $this->withOutActiveUserEmail($recipients_list);
            asort($recipients_list);
            $recipients_list = array_values($recipients_list);
        }

        $attribute_list['message'] = [
            'subject'         => $attribute_list['subject'],
            'recipients_list' => ($recipients_list ? array_unique($recipients_list) : null),
            'message'         => $attribute_list['message'],
        ];

        unset($attribute_list['channel_data_id']);
        unset($attribute_list['subject']);
        unset($attribute_list['block_participant']);
    }

    /**
     * getRecipientsList - Возвращает список адресатов для отсылке по емейлу
     */
    private function getRecipientsList($attribute_list)
    {
        $recipients_list = [];

        if (!empty($attribute_list['channel_data_id'])) { // channel_data_id - ІД Канала - если отсылка идет из связанной сущности
            $recipients_list = $this->getRecipientsListByAttrChannel($attribute_list);
        } else {
            if (!empty($attribute_list['block_participant'][ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL])) {
                $recipients_list = $this->getRecipientsListByAttrBlockParticipant($attribute_list);
            }
        }

        return $recipients_list;
    }

    private function getRecipientsListByAttrChannel($attribute_list)
    {
        $result = [];

        $participant_email_list = \ParticipantEmailModel::model()
            ->scopeCardParams($attribute_list['copy_id'], $attribute_list['data_id'])
            ->with('emails')
            ->findEmails();

        if ($participant_email_list == false) {
            return $result;
        }

        $result = $participant_email_list;

        $participant_model_list = ParticipantModel::getParticipants($attribute_list['copy_id'], $attribute_list['data_id'], \ParticipantModel::PARTICIPANT_UG_TYPE_USER);
        if ($participant_model_list == false) {
            return $result;
        }

        $participant_data_list = [];
        foreach ($participant_model_list as $participant_model) {
            $participant_data_list[] = [
                'ug_id'   => $participant_model->ug_id,
                'ug_type' => $participant_model->ug_type,
            ];
        }

        $result = array_merge($result, $this->getEmailListByParticipant_Pariticipant($participant_data_list));

        return $result;
    }

    /**
     * getRecipientsListByAttrBlockParticipant - возвращает список email адресов по запросу из участников и ИД-адресов
     *
     * @param $participant_data_list
     * @param bool $skip_active_user
     * @return array
     */
    private function getRecipientsListByAttrBlockParticipant($attribute_list)
    {
        $participant_data_list = $attribute_list['block_participant'];

        $result = [];

        if ($participant_data_list == false) {
            return $result;
        }

        foreach ([ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT, ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL] as $type_item_list) {
            $method_name = '';
            switch ($type_item_list) {
                case ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT:
                    $method_name = 'getEmailListByParticipant_Pariticipant';
                    break;
                case ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL:
                    $method_name = 'getEmailListByParticipant_Email';
                    break;
            }

            if (empty($participant_data_list[$type_item_list])) {
                continue;
            }

            $result = array_merge($result, $this->{$method_name}($participant_data_list[$type_item_list]));
        }

        return $result;
    }

    private function getEmailListByParticipant_Pariticipant($participant_data_list)
    {
        $result = [];

        if (empty($participant_data_list)) {
            return $result;
        }

        foreach ($participant_data_list as $participant_data) {
            if (empty($participant_data['ug_type']) || $participant_data['ug_type'] != ParticipantModel::PARTICIPANT_UG_TYPE_USER) {
                continue;
            }

            $criteria = new \CDbCriteria();
            $criteria->addCondition('user_id=:user_id');
            $criteria->params = [
                ':user_id' => $participant_data['ug_id'],
            ];
            $communication_params_model = \CommunicationsServiceParamsModel::model()->find($criteria);

            if ($communication_params_model == false) {
                continue;
            }

            $c_params = $communication_params_model->getParamsJsonDecode();
            $result[] = $c_params['user_login'];
        }

        return $result;
    }

    private function getEmailListByParticipant_Email($participant_data_list)
    {
        $result = [];

        if (empty($participant_data_list)) {
            return $result;
        }

        foreach ($participant_data_list as $participant_data) {
            $email_id_list[] = $participant_data['email_id'];
        }

        if ($email_id_list) {
            $result = EmailsModel::findByEmailIdList($email_id_list);
        }

        return $result;
    }

    private function withOutActiveUserEmail(&$email_list)
    {
        $criteria = new \CDbCriteria();
        $criteria->addCondition('user_id=:user_id');
        $criteria->params = [
            ':user_id' => \WebUser::getUserId(),
        ];
        $communication_params_model = \CommunicationsServiceParamsModel::model()->find($criteria);

        if ($communication_params_model == false) {
            return;
        }

        $c_params = $communication_params_model->getParamsJsonDecode();

        foreach ($email_list as $key => $email) {
            if ($email == $c_params['user_login']) {
                unset($email_list[$key]);

                return;
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'message' => Yii::t('base', 'Message'),
        ];
    }

    public function validateMessage($argument, $params)
    {
        switch ($this->type_comment) {
            case self::TYPE_COMMENT_EMAIL:
                return $this->validateMessageEmail($argument, $params);
        }
    }

    public function validateMessageEmail($argument, $params)
    {
        $message_json = $this->getMessage(true);

        if (empty($message_json['message'])) {
            $this->addError($argument, Yii::t('communications', 'No message text entered'));
        }

        /*
        if(empty($message_json['recipients_list'])) {
            $this->addError($argument, Yii::t('communications', 'Undefined destination email address'));
        }
        */

        if ($this->changed_copy_id_to_communications && empty($this->data_id)) {
            $this->addError($argument, Yii::t('communications', 'Need select channel'));
        }

    }

    protected function beforeSave()
    {
        if ($this->getIsNewRecord()) {
            if ($this->getScenario() != 'copy') {
                if (empty($this->date_create)) {
                    $this->date_create = date('Y-m-d H:i:s');
                }

                if ($this->_user_is_empty == false) {
                    $this->user_create = WebUser::getUserId();
                }
            }
        } else {
            if ($this->getScenario() != 'copy') {
                $this->date_edit = date('Y-m-d H:i:s');

                if ($this->_user_is_empty == false) {
                    $this->user_edit = WebUser::getUserId();
                }
            }
        }

        // files
        $this->linkFiles();
        $this->copyFile();

        return true;
    }

    public function afterSave()
    {
        $this->updateFileStatusTo();

        $extension_copy = ExtensionCopyModel::model()->findByPk($this->copy_id);

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false, true),
            'params'    => Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
        ];

        $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($this->data_id);

        $make_loggin = true;

        if ($extension_copy->getAttribute('copy_id') == ExtensionCopyModel::MODULE_TASKS) {
            if ($extension_data !== null) {
                TaskModel::deleteMarkTaskIsView($extension_data->getPrimaryKey());

                if ($extension_data->is_bpm_operation == "1") {
                    $make_loggin = false;
                }
            }
        }

        if ($make_loggin || ($this->type_comment != self::TYPE_COMMENT_GENERAL)) {
            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
            $comment = \Process\models\BindingObjectModel::getRelateObjectHistoryMessage(['copy_id' => $extension_copy->copy_id, 'card_id' => $this->data_id]);
            if (!empty($comment)) {
                $comment = '</br>' . $comment;
            }

            if (in_array($this->type_comment, [self::TYPE_COMMENT_GENERAL, self::TYPE_COMMENT_EMAIL])) {
                if ($this->_history_save) {
                    $files = [];
                    if ($this->attachment) {
                        $upload_models = UploadsModel::model()->setRelateKey($this->attachment)->findAll();
                        foreach ($upload_models as $upload_model) {
                            $files['{uploads_id}'][] = $upload_model->id;
                            $files['{file_url}'][] = '/' . $upload_model->getFileUrl();
                            $files['{file_title}'][] = $upload_model->file_title;
                            $files['{file_path}'][] = $upload_model->file_path;
                        }
                    }

                    History::getInstance()->addToHistory(($this->getIsNewRecord() ? HistoryMessagesModel::MT_COMMENT_CREATED : HistoryMessagesModel::MT_COMMENT_CHANGED),
                        $extension_copy->copy_id,
                        $this->data_id,
                        array_merge(
                            [
                                '{module_data_title}'    => (!empty($extension_data)) ? $extension_data->getModuleTitle($extension_copy) : null,
                                '{user_id}'              => WebUser::getUserId(),
                                '{activity_messages_id}' => $this->getAttribute('activity_messages_id'),
                                '{message}'              => $this->getAttribute('message'),
                                '{comment}'              => (!empty($comment) ? $comment : ''),
                            ],
                            $files
                        ),
                        false,
                        true
                    );
                }

                $user_params = CommunicationsServiceParamsModel::model()->getUserParams();
                $message = $this->getMessage(true);
                if (($this->type_comment == self::TYPE_COMMENT_EMAIL)
                    && (!empty($message['sender']))
                    && ($message['sender'] != $user_params['user_login'])
                ) {

                    $communication_activity_mark_view_model = new CommunicationsActivityMarkViewModel();
                    $communication_activity_mark_view_model->activity_messages_id = $this->activity_messages_id;

                    $responsible = ParticipantModel::getParticipants($this->copy_id, $this->data_id, ParticipantModel::PARTICIPANT_UG_TYPE_USER, true, true);
                    $responsible_id = !empty($responsible) ? $responsible->ug_id : null;
                    $communication_activity_mark_view_model->user_id = (!empty($this->user_create) ? $this->user_create : $responsible_id);
                    $communication_activity_mark_view_model->save();
                }
            }

            /*
            if($this->_new_record == false){
                History::getInstance()->unactiveOldComments($extension_copy->copy_id, $this->data_id, $this->getAttribute('activity_messages_id'));
            }
            */

            HistoryContainerModel::save();
        }

        return $this;
    }

    protected function beforeDelete()
    {
        //удаляем письма
        $this->deleteEmailMessage();

        // удаляем файлы
        if (empty($this->attachment)) {
            return true;
        }
        $files = UploadsModel::model()->setRelateKey($this->attachment)->findAll();
        if (!empty($files)) {
            foreach ($files as $file) {
                $file->delete();
            }
        }

        return true;
    }

    /**
     * Связывает загруженные файлы с сущностью
     */
    private function linkFiles()
    {
        $idList = $this->_files;
        $relateKey = $this->attachment;

        if (!$idList) {
            return;
        }

        if (!$relateKey) {
            $relateKey = md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . 'attachment');
            $this->attachment = $relateKey;
        }

        UploadsModel::model()->updateAll(
            [
                'relate_key' => $relateKey,
                'copy_id'    => $this->copy_id,
            ],
            (new CDbCriteria())->addInCondition('id', (array)$idList)
        );

    }

    /**
     * Обновляет статус всех загруженых файлов сущности
     *
     * @param string $status
     */
    private function updateFileStatusTo($status = 'asserted')
    {
        if (!$this->attachment) {
            return;
        }

        UploadsModel::model()->updateAll(
            ['status' => $status,],
            (new CDbCriteria())->addCondition('relate_key = "' . $this->attachment . '"')
        );
    }

    /**
     * копируем файлы уведомлений
     */
    protected function copyFile()
    {
        if ($this->getScenario() == 'copy') {
            if (empty($this->attachment)) {
                return;
            }

            $files = UploadsModel::model()->setRelateKey($this->attachment)->findAll();

            $this->attachment = null;
            if (!empty($files)) {
                $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . 'attachment';
                $relate_key = md5($relate_key);

                foreach ($files as $value_file) {
                    $upload_model = new UploadsModel();
                    $upload_model->setScenario('copy');
                    $upload_model->setThumbScenario('copy');
                    $upload_model->id = null;
                    $upload_model->relate_key = $relate_key;
                    $upload_model->copy_id = $value_file->copy_id;
                    $upload_model->file_source = $value_file->file_source;
                    $upload_model->file_path_copy = $value_file->file_path;
                    $upload_model->file_name = $value_file->file_name;
                    $upload_model->file_title = $value_file->file_title;
                    $upload_model->thumbs = $value_file->thumbs;
                    $upload_model->status = 'asserted';
                    $upload_model->save();
                }
                $this->attachment = $relate_key;
            }
        }
    }

    public static function getDateFormated($date)
    {
        if ($date == false) {
            return;
        }

        if (is_string($date)) {
            $date = getdate(strtotime($date));
        }

        $date_now = getdate();

        $params = [
            'd'    => $date['mday'],
            'y'    => substr($date['year'], -2, 2),
            'Y'    => $date['year'],

            'H'    => (strlen($date['hours']) == 1 ? '0' . $date['hours'] : $date['hours']),
            'i'    => (strlen($date['minutes']) == 1 ? '0' . $date['minutes'] : $date['minutes']),
            's'    => '',
            'mmmm' => LocaleCRM::getInstance2()->getMonthName($date['mon'] - 1),
            'MMMM' => LocaleCRM::getInstance2()->getMonthName($date['mon'] - 1, 'wide', true),
        ];

        $params_now = [
            'd'    => $date_now['mday'],
            'y'    => substr($date_now['year'], -2, 2),
            'Y'    => $date_now['year'],

            'H'    => (strlen($date_now['hours']) == 1 ? '0' . $date_now['hours'] : $date_now['hours']),
            'i'    => (strlen($date_now['minutes']) == 1 ? '0' . $date_now['minutes'] : $date_now['minutes']),
            's'    => '',
            'mmmm' => LocaleCRM::getInstance2()->getMonthName($date_now['mon'] - 1),
            'MMMM' => LocaleCRM::getInstance2()->getMonthName($date_now['mon'] - 1, 'wide', true),
        ];

        if ($params['Y'] == $params_now['Y']) {
            $result = LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['long_without_Ys'];
        } else {
            $result = LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['long_without_s'];
        }

        foreach ($params as $key => $value) {
            $result = str_replace($key, $value, $result);
        }

        return $result;

    }

    /**
     * форматирует дату
     */
    public function getDateCreateFormated()
    {
        $this->refresh();
        $date = getdate(strtotime($this->date_create));
        $date_format = static::getDateFormated($date);

        return $date_format;
    }

    /**
     * getAuthorCommentAvatar - Возвращает аватар участника для подписи комментария в Активности,
     */
    public function getAuthorCommentAvatar()
    {
        switch ($this->type_comment) {
            case self::TYPE_COMMENT_GENERAL :
                return $this->getAuthorCommentAvatarStaff();
            case self::TYPE_COMMENT_EMAIL :
                return $this->getAuthorCommentAvatarEmailParticipant();
            default :
                return $this->getAuthorCommentAvatarStaff();

        }
    }

    private function getAuthorCommentAvatarStaff()
    {
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_STAFF)->getModule(false);
        if ($this->staff) {
            return $this->staff->getAvatar(42);
        }
    }

    private function getAuthorCommentAvatarEmailParticipant()
    {
        if ($this->mailerOutboxRelate) {
            return $this->getAuthorCommentAvatarStaff();
        }

        if ($this->mailerInboxRelate) {
            $mailer_inbox_model = $this->mailerInboxRelate->mailerInbox;
            if ($mailer_inbox_model && $emails = $mailer_inbox_model->emails) {
                return $emails->getAvatar(42);
            } else {
                if ($mailer_inbox_model) {
                    return (new AvatarModel())->setThumbSize(42)->getAvatar();
                }
            }
        }

        return $this->getAuthorCommentAvatarStaff();

    }

    /**
     * getAuthorCommentName - Возвращает название участника для подписи комментария в Активности,
     */
    public function getAuthorCommentName()
    {
        switch ($this->type_comment) {
            case self::TYPE_COMMENT_GENERAL :
                return $this->getAuthorCommentNameStaff();
                break;
            case self::TYPE_COMMENT_EMAIL :
                return $this->getAuthorCommentNameEmailParticipant();
                break;
            default :
                return $this->getAuthorCommentNameStaff();
        }
    }

    private function getAuthorCommentNameStaff()
    {
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_STAFF)->getModule(false);
        if ($this->staff) {
            return $this->staff->getFullName();
        }
    }

    private function getAuthorCommentNameEmailParticipant()
    {
        if ($this->mailerOutboxRelate) {
            return $this->getAuthorCommentNameStaff();
        }

        if ($this->mailerInboxRelate) {
            $mailer_inbox_model = $this->mailerInboxRelate->mailerInbox;
            if ($mailer_inbox_model && $emails = $mailer_inbox_model->emails) {
                return ($emails->title != '' ? $emails->title . ' &lt;' . $emails->email . '&gt;' : $emails->email);
            } else {
                if ($mailer_inbox_model) {
                    return ($mailer_inbox_model->letter_from_name != '' ? $mailer_inbox_model->letter_from_name . ' &lt;' . $mailer_inbox_model->letter_from . '&gt;' : $mailer_inbox_model->letter_from);
                }
            }
        }

        return $this->getAuthorCommentNameStaff();
    }

    /**
     * getCommentText - Возвращает текст  в Активность,
     */
    public function getCommentText()
    {
        switch ($this->type_comment) {
            case self::TYPE_COMMENT_GENERAL :
                return $this->getCommentTextGeneral();
                break;
            case self::TYPE_COMMENT_EMAIL :
                return $this->getCommentTextEmail();
                break;
            default :
                return $this->getCommentTextGeneral();

        }
    }

    private function getCommentTextGeneral()
    {
        return $this->getMessage();
    }

    private function getCommentTextEmail()
    {
        $message_json = $this->getMessage(true);

        if ($message_json == false || key_exists('message', $message_json) == false) {
            return Yii::t('communications', 'Error reading message text');
        }

        return $message_json['message'];
    }

    public function showEmailDeliveryStatus()
    {
        if ($this->type_comment == self::TYPE_COMMENT_EMAIL) {
            $outbox_relate_model = $this->mailerOutboxRelate;
            if ($outbox_relate_model) {
                return true;
            }
        }

        return false;
    }

    public function getEmailDeliveryStatusTitle()
    {
        if ($this->type_comment != self::TYPE_COMMENT_EMAIL) {
            return;
        }

        $outbox_relate_model = $this->mailerOutboxRelate;

        if ($outbox_relate_model == false) {
            return;
        }

        if ($outbox_relate_model && $outbox_model = $outbox_relate_model->mailerOutbox) {
            return $outbox_model->getStatusTitle();
        }

        return \Yii::t('base', 'Waiting for sending');
    }

    public function isDeleteAvailable()
    {
        if ($this->type_comment != self::TYPE_COMMENT_EMAIL) {
            return true;
        }

        $outbox_relate_model = $this->mailerOutboxRelate;

        if ($outbox_relate_model && $outbox_model = $outbox_relate_model->mailerOutbox) {
            switch ($outbox_model->status) {
                case MailerLettersOutboxModel::STATUS_SEND:
                    return true;
                case MailerLettersOutboxModel::STATUS_IS_SENT:
                case MailerLettersOutboxModel::STATUS_SENDED:
                    return false;
            }
        }

        return true;
    }

    /**
     * @param $copy_id
     * @param $data_id
     * @return mixed|null
     */
    public static function getLastAttachment($copy_id, $data_id)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'attachment';
        $criteria->condition = "copy_id=:copy_id AND data_id=:data_id AND attachment IS NOT NULL";
        $criteria->params = [
            ':copy_id' => $copy_id,
            ':data_id' => $data_id
        ];
        $criteria->order = "date_create DESC";

        $result = ActivityMessagesModel::model()->findAll($criteria);

        return $result;
    }

    /**
     * deletePrepareActivityMessages - удаляем уведомления из Активности - Только подготовка!
     */
    public static function deletePrepareActivityMessages($extension_copy, $id_list)
    {
        $activity_schema = $extension_copy->getFieldSchemaParamsByType('activity');
        if ($activity_schema == false) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id');
        $criteria->params = [':copy_id' => $extension_copy->copy_id];
        $criteria->addInCondition('data_id', $id_list);

        $activity_model_list = ActivityMessagesModel::model()->findAll($criteria);

        if ($activity_model_list) {
            foreach ($activity_model_list as $activity_model) {
                // данные уведомления
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('activity_messages', \QueryDeleteModel::D_TYPE_DATA, ['table_name' => 'activity_messages', 'primary_field_name' => 'activity_messages_id'])
                    ->appendValues('activity_messages', \QueryDeleteModel::D_TYPE_DATA, $activity_model->activity_messages_id);

                \UploadsModel::deletePrepareUploads($activity_model->attachment);

                $activity_model->deleteEmailMessage();
            }
        }
    }

    public function communicationSaveMessage()
    {
        if ($this->type_comment == false) {
            return $this;
        }

        $message_data = $this->getCommunicationMessageData();

        switch ($this->type_comment) {
            case static::TYPE_COMMENT_EMAIL :
                $this->communicationSaveEmailMessage($message_data);
                break;
        }

        return $this;
    }

    private function communicationSaveEmailMessage($message_data)
    {
        $model = (new MailerLettersOutboxModel())->saveNewLetter($message_data);

        return $model;
    }

    public function communicationSend($message_model)
    {
        $class = null;

        if($message_model) {
            $class = get_class($message_model);
        }

        switch ($class) {
            case 'MailerLettersOutboxModel' :
                $this->communicationSendEmail($message_model);
        }
    }

    private function communicationSendEmail($message_model)
    {
        $result = (new CommunicationsSourceModel(static::TYPE_COMMENT_EMAIL, null, WebUser::getUserId()))
            ->runAction(SourceFactory::ACTION_SEND_MESSAGE, $message_model)
            ->getResult();

        return $result;
    }

    private function getCommunicationMessageData()
    {

        switch ($this->type_comment) {
            case self::TYPE_COMMENT_EMAIL:
                return $this->getCommunicationMessageDataEmail();
                break;
        }
    }

    private function getCommunicationMessageDataEmail()
    {

        if (!empty($this->attachment)) {
            $attachments = (new UploadsModel())->findAll('relate_key = :relate_key', [':relate_key' => $this->attachment]);
        }

        $message = json_decode($this->message);

        $result = [
            'user_id'            => WebUser::getUserId(),
            'letter_to'          => (is_array($message->recipients_list) ? implode(',', $message->recipients_list) : $message->recipients_list),
            'letter_to_name'     => '',
            'letter_subject'     => $message->subject,
            'letter_body'        => $message->message,
            'letter_attachments' => (!empty($attachments)) ? $attachments : [],
            'resource_type'      => MailerLettersOutboxRelateModel::RESOURCE_TYPE_ACTIVITY,
            'relate_id'          => $this->activity_messages_id,
            'data_id'            => $this->data_id,
            'copy_id'            => $this->copy_id,
        ];

        return $result;
    }

    /**
     * deleteEmailMessage - удаление прикрепленных писем
     */
    public function deleteEmailMessage()
    {
        // если есть удаляем внешнее сообшение
        if ($this->type_comment != ActivityMessagesModel::TYPE_COMMENT_EMAIL) {
            return;
        }

        // Outbox
        if ($outbox_relate_model = $this->mailerOutboxRelate) {
            /* отключено
            if($outbox_relate_model->mailerOutboxParams){
                DataModel::getInstance()->Insert(
                    '{{mailer_letters_actions_scheduler}}',
                    array(
                        'uid'=> $outbox_relate_model->mailerOutboxParams->uid,
                        'users_id' => WebUser::getUserId(),
                        'action_name' => MailerLettersActionsSchedulerModel::ACTION_DELETE,
                        'mailbox' => MailerLettersActionsSchedulerModel::MAILBOX_NAME_SENT,
                    )
                );
            }
            */
            // files
            if ($outbox_model = $this->mailerOutboxRelate->mailerOutbox) {
                if ($mailer_letters_outbox_files_model_list = $outbox_model->mailerOutboxFiles) {
                    foreach ($mailer_letters_outbox_files_model_list as $mailer_letters_outbox_files_model) {
                        $uploads_model = (new UploadsModel())->findByPk($mailer_letters_outbox_files_model->uploads_id);
                        if ($uploads_model !== null) {
                            $uploads_model->delete();
                        }
                    }
                }
                $outbox_model->delete();
            }
        }

        // Inbox
        if ($inbox_relate_model = $this->mailerInboxRelate) {
            if ($inbox_relate_model->mailerInboxParams) {
                DataModel::getInstance()->Insert(
                    '{{mailer_letters_actions_scheduler}}',
                    [
                        'uid'         => $inbox_relate_model->mailerInboxParams->uid,
                        'users_id'    => WebUser::getUserId(),
                        'action_name' => MailerLettersActionsSchedulerModel::ACTION_DELETE,
                        'mailbox'     => MailerLettersActionsSchedulerModel::MAILBOX_NAME_INBOX,
                    ]
                );
            }

            // files
            if ($inbox_model = $this->mailerInboxRelate->mailerInbox) {
                if ($mailer_letters_inbox_files_model_list = $inbox_model->mailerInboxFiles) {
                    foreach ($mailer_letters_inbox_files_model_list as $mailer_letters_inbox_files_model) {
                        $uploads_model = (new UploadsModel())->findByPk($mailer_letters_inbox_files_model->uploads_id);
                        if ($uploads_model !== null) {
                            $uploads_model->delete();
                        }
                    }
                }
                $inbox_model->delete();
            }
        }

    }

    /**
     * getActivityMessagesListByDataId - возвращает список уведомлений опеределенной сущности по списку activity_id
     */
    public static function getActivityMessagesListByActivityId($activity_id)
    {
        if ($activity_id == false) {
            return;
        }

        $activity_data_list = ActivityMessagesModel::model()->findAll([
            'condition' => 'activity_messages_id in (' . implode(',', (array)($activity_id)) . ')',
            'order'     => 'date_create desc',
        ]);

        return $activity_data_list;
    }

    /**
     * getActivityMessagesListByDataId - возвращает список уведомлений опеределенной сущности
     */
    public static function getActivityMessagesListByDataId($extension_copy, $data_id)
    {
        if ($data_id == false) {
            return;
        }

        $activity_data_list = null;

        $relate_copy_id = ExtensionCopyModel::MODULE_COMMUNICATIONS;
        $module_tables_model = ModuleTablesModel::getRelateModel($extension_copy->copy_id, $relate_copy_id, ModuleTablesModel::TYPE_RELATE_MODULE_MANY);

        if ($module_tables_model) {
            $relate_data_id = (new \DataModel())
                ->setSelect($module_tables_model->relate_field_name)
                ->setFrom('{{' . $module_tables_model->table_name . '}}')
                ->setWhere($module_tables_model->parent_field_name . ' = ' . $data_id)
                ->findCol();
        }

        if (!empty($relate_data_id)) {
            $activity_data_list = ActivityMessagesModel::model()->setScopeStatus()->findAll([
                'condition' => '(copy_id =:copy_id AND data_id =:data_id) OR (copy_id =:relate_copy_id AND data_id in (' . implode(',', $relate_data_id) . '))',
                'params'    => [
                    ':copy_id'        => $extension_copy->copy_id,
                    ':data_id'        => $data_id,
                    ':relate_copy_id' => $relate_copy_id,
                ],
                'order'     => 'date_create desc',
            ]);
        } else {
            $activity_data_list = ActivityMessagesModel::model()->setScopeStatus()->findAll([
                'condition' => 'copy_id =:copy_id AND data_id =:data_id',
                'params'    => [
                    ':copy_id' => $extension_copy->copy_id,
                    ':data_id' => $data_id,
                ],
                'order'     => 'date_create desc',
            ]);
        }

        if ($activity_data_list) {
            $activity_id_list = array_values(CHtml::listData($activity_data_list, 'activity_messages_id', 'activity_messages_id'));
            static::setMessageIsView($activity_id_list);
            static::setFlagSeen($activity_id_list);
        }

        return $activity_data_list;
    }

    /**
     * setMessageIsView - удаление меток системы для просмотренных уведомлений блока активности
     */
    private static function setMessageIsView($activity_messages_id_list, $users_id = null)
    {
        if ($activity_messages_id_list == false) {
            return;
        }
        if ($users_id === null) {
            $users_id = WebUser::getUserId();
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('user_id=:user_id');
        $criteria->params = [
            ':user_id' => $users_id,
        ];
        $criteria->addInCondition('activity_messages_id', $activity_messages_id_list);

        CommunicationsActivityMarkViewModel::model()->deleteAll($criteria);
    }

    /**
     * setFlagSeen - установка расписания для обновления статуса "просмотрено" для писем
     */
    private static function setFlagSeen($activity_messages_id_list, $users_id = null)
    {
        if ($activity_messages_id_list == false) {
            return;
        }
        if ($users_id === null) {
            $users_id = WebUser::getUserId();
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('flag_seen = "0" AND activityMessages.user_create=:user_id');
        $criteria->params = [
            ':user_id' => $users_id,
        ];
        $criteria->addInCondition('activity_messages_id', $activity_messages_id_list);

        $inbox_params_model_list = MailerLettersInboxParamsModel::model()
            ->with(['mailerInboxRelate.activityMessages' => ['select' => false]])
            ->findAll($criteria);

        if ($inbox_params_model_list == false) {
            return;
        }

        // MailerLettersActionsSchedulerModel: заполняем таблицу-планировщик заданий для сервиса. flag = FLAG_SET_SEEN
        foreach ($inbox_params_model_list as $inbox_params_model) {
            $scheduled_model = new MailerLettersActionsSchedulerModel();
            $scheduled_model->uid = $inbox_params_model->uid;
            $scheduled_model->users_id = WebUser::getUserId();
            $scheduled_model->action_name = MailerLettersActionsSchedulerModel::ACTION_SET_SEEN;
            $scheduled_model->mailbox = MailerLettersActionsSchedulerModel::MAILBOX_NAME_INBOX;
            $scheduled_model->save();
        }

        // обновление MailerLettersInboxParamsModel: flag_seen = 1
        $inbox_params_id_list = array_values(CHtml::listData($inbox_params_model_list, 'inbox_params_id', 'inbox_params_id'));
        $criteria = new CDbCriteria();
        $criteria->addInCondition('inbox_params_id', $inbox_params_id_list);

        MailerLettersInboxParamsModel::model()->updateAll(['flag_seen' => '1'], $criteria);
    }

}





