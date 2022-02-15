<?php

/**
 * Class MailerLettersOutboxModel
 */


class MailerLettersOutboxModel extends ActiveRecord{

    // Статус отправки сообщений
    const STATUS_SEND       = 'send';       // на отсылку
    const STATUS_IS_SENT    = 'is_sent';    // осуществляется отсылка
    const STATUS_SENDED     = 'sended';     // отослано

    private $_error = false;
    private $_messages = array();
    private $_result = array();

    private $_params = null;

    public $tableName = 'mailer_letters_outbox';



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function relations(){
        return array(
            'mailerOutboxParams' => array(self::HAS_ONE, 'MailerLettersOutboxParamsModel', 'mailer_id'),
            'mailerOutboxRelate' => array(self::HAS_ONE, 'MailerLettersOutboxRelateModel', 'mailer_id'),
            'mailerOutboxSources' => array(self::HAS_ONE, 'MailerLettersSourcesModel', 'mailer_id'),
            'mailerOutboxFiles' => array(self::HAS_MANY, 'MailerLettersOutboxFilesModel', 'mailer_id'),
            'mailerOutboxMarkView' => array(self::HAS_MANY, 'MailerLettersOutboxMarkViewModel', 'mailer_id'),
        );
    }



    public function rules(){
        return array(
            array('letter_from, letter_to, letter_subject', 'length', 'max'=>255),
            array('letter_from_name, letter_to_name', 'length', 'max'=>100),
            array('letter_body', 'length', 'max'=>16777215),
            array('status', 'safe'),
        );
    }





    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    public function setParams($params){
        $this->_params = $params;
    }



    public function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        return array(
                'status' => $this->getStatus(),
                'messages' => array_merge($this->_messages, (!empty($this->_result['messages'])) ? $this->_result['messages'] : array()),
            ) + $this->_result;
    }



    public function getMessages(){
        return $this->_messages;
    }



    public function addMessages($messages = array()){
        $this->_messages = array_merge($this->_messages, $messages);
        return $this;
    }



    public function addMessage($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        return $this;
    }



    public function getStatusTitle(){
        switch($this->status){
            case self::STATUS_SEND:
            case self::STATUS_IS_SENT:
                return \Yii::t('base', 'Waiting for sending');
            case self::STATUS_SENDED:
                $date_read_list = $this->mailerOutboxMarkView;
                if($date_read_list){
                    return \Yii::t('base', 'Read');
                } else {
                    return \Yii::t('base', 'Sended');
                }

            default:
                return \Yii::t('base', 'Waiting for sending');
        }
    }


    function beforeSave(){
        if($this->isNewRecord){
            $this->date_create = new CDbExpression('now()');
        } else {
            $this->date_edit = new CDbExpression('now()');
        }
        return true;
    }



    public function setLetterStatus($mailer_id, $status){
        $letter = self::find('mailer_id = :mailer_id', array(':mailer_id' => $mailer_id));
        $letter->status = $status;
        $letter->save();
    }



    protected function checkParams(){
        if (!empty($this->_params)) {
            return true;
        }

        $this->addError('Letter params is not validate. It was not saved and sent.', []);
        return false;
    }



    private function prepareParams(){
        if($this->_error){
            return $this;
        }

        $user_id = $this->_params['user_id'];
        if(empty($user_id)){
            if(($user_id = WebUser::getUserId()) === null){
                $this->addError('Undefined user');
                return $this;
            }
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $user_model = UsersModel::model()->findByPk($user_id);

        $communications_service_params = (new CommunicationsServiceParamsModel())->getUserParams($user_id);

        $this->_params += [
            'status' => 'send',
            'user_create' => $user_id,
            'letter_from' => $communications_service_params['user_login'],
            'letter_from_name' => (!empty($user_model)) ? $user_model->sur_name . ' ' . $user_model->first_name : '',
            'letter_attachments' => !empty($this->_params['attachments']) ? $this->_params['attachments'] : array(),
        ];
    }



    private function validateBeforeSave(){
        if($this->_error){
            return $this;
        }

        if(empty($this->_params['letter_to'])){
            $this->_error = true;
            return $this;
        }

        return $this;
    }



    private function getLetterTrackingImage(){
        $site_url = ParamsModel::getValueFromModel('site_url');
        $img = '<img class="tgCrmLabel" style="display:none" data-id="'.$this->mailer_id.'" src="' . $site_url . '/file/LetterImage/' . $this->mailer_id . '.png">';

        return $img;
    }



    private function getLetterSignature(){
        $signature = '';

        $service_params=CommunicationsServiceParamsModel::model()->find('user_id=:user_id AND source_name=:source_name',
            array(':user_id'=>WebUser::getUserId(),':source_name'=>'email')
        );

        if(!empty($service_params)){
            $signature = !empty($service_params['signature']) ? "<br><br><br>".$service_params['signature'] : "";
            $signature = preg_replace('/\n/','<br>',$signature);
        }

        return $signature;
    }



    /**
     * Сохранение нового письма
     */
    public function saveNewLetter($params){
        $this->setParams($params);
        $this->prepareParams();
        $this->validateBeforeSave();


        if($this->_error){
            return $this;
        }


        $letter_body = $this->_params['letter_body'];

        $this->_params['letter_body'] = $letter_body . $this->getLetterSignature();

        if($this->saveModel()){
            $activeServiceId = CommunicationsServiceParamsModel::model()->getActiveServiceId('email', $this->user_create);

            $this->_params += array(
                'mailer_id' => (int)$this->mailer_id,
                'source' => MailerLettersSourcesModel::SOURCE_COMMUNICATIONS,
                'params' => array(
                    'service_params_id' => ($activeServiceId !== false) ? $activeServiceId : null,
                ));


            // Записываем в таблицу 'mailer_letters_outbox_relate'
            if((new MailerLettersOutboxRelateModel())->saveModel($this->_params)){
                // Записываем в таблицу 'mailer_letters_source'
                if((new MailerLettersSourcesModel())->addNewLetter($this->_params)){
                    // Если есть вложенные файлы pаписываем в таблицу 'mailer_letters_outbox_files'
                    if(!empty($this->_params['letter_attachments'])){
                        $outbox_files_model = new MailerLettersOutboxFilesModel();
                        $outbox_files_model->setScenario(UploadsModel::SCENARIO_EMAIL_COPY_TO);
                        $outbox_files_model->saveAttachmentFiles($this->_params);
                    }
                }
            }
        }
        return $this;
    }



    private function saveModel(){
        $this->user_create = $this->_params['user_create'];
        $this->letter_from = $this->_params['letter_from'];
        $this->letter_from_name = $this->_params['letter_from_name'];
        $this->letter_to = $this->_params['letter_to'];
        $this->letter_to_name = $this->_params['letter_to_name'];
        $this->letter_subject = ($this->_params['letter_subject'] != '') ? $this->_params['letter_subject'] : '*****';
        $this->letter_body = $this->_params['letter_body'];
        $this->status = $this->_params['status'];

        if($this->save()){
            $this->letter_body = $this->getLetterTrackingImage() . $this->letter_body;
            $this->save();
        } else {
            $this->addError('Error writing to the table');
            return false;
        }

        $this->_params['mailer_id'] = (int)$this->mailer_id;
        return true;
    }



}
