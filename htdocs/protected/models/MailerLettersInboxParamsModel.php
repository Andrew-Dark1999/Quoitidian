<?php

/**
 * Class MailerLettersInboxParamsModel
 */

class MailerLettersInboxParamsModel extends ActiveRecord{

    public $tableName = 'mailer_letters_inbox_params';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }




    public function rules(){
        return array(
            array('mailer_id, message_id, reference, uid', 'safe'),
        );
    }



    public function relations(){
        return array(
            'mailerInbox' => array(self::BELONGS_TO, 'MailerLettersInboxModel', array('mailer_id' => 'mailer_id')),
            'mailerInboxRelate' => array(self::HAS_ONE, 'MailerLettersInboxRelateModel', array('mailer_id' => 'mailer_id')),
        );
    }



    public function setScopeMessageId($message_id){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'message_id=:message_id',
            'params' => array(':message_id' => $message_id),
        ));

        return $this;
    }



    public static function hasMessage($message_id, $user_id){
        $result = static::model()->with('mailerInbox')->find('message_id=:message_id AND mailerInbox.user_create=:user_id', array(
            ':message_id' => $message_id,
            ':user_id' => $user_id,
        ));

        return ($result ? true : false);
    }







}
