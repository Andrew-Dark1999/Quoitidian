<?php

/**
 * MailerLettersOutboxParamsModel
 */


class MailerLettersOutboxParamsModel extends ActiveRecord{


    public $tableName = 'mailer_letters_outbox_params';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function rules(){
        return array(
            array('mailer_id, message_id, reference, uid, flag_seen', 'safe'),
        );
    }




    public function relations(){
        return array(
            'mailerOutbox' => array(self::BELONGS_TO, 'MailerLettersOutboxModel', array('mailer_id' => 'mailer_id')),
            'mailerOutboxRelate' => array(self::HAS_ONE, 'MailerLettersOutboxRelateModel', array('mailer_id' => 'mailer_id')),
        );
    }



    public function setScopeMessageId($message_id){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'message_id=:message_id',
            'params' => array(':message_id' => $message_id),
        ));

        return $this;
    }




}
