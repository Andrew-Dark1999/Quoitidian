<?php

/**
 * Class MailerLettersInboxRelateModel
 */


class MailerLettersInboxRelateModel extends ActiveRecord{


    public $tableName = 'mailer_letters_inbox_relate';

    // Источники входящих сообщений
    const RESOURCE_TYPE_SYSTEM       = 'system';
    const RESOURCE_TYPE_ACTIVITY     = 'activity';
    const RESOURCE_TYPE_NOTIFICATION = 'notification';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function rules(){
        return array(
            array('mailer_id, relate_id, resource_type', 'safe'),
        );
    }


    public function relations(){
        return array(
            'mailerInbox' => array(self::BELONGS_TO, 'MailerLettersInboxModel', 'mailer_id'),
            'mailerInboxParams' => array(self::HAS_ONE, 'MailerLettersInboxParamsModel', array('mailer_id' => 'mailer_id')),
            'activityMessages' => array(self::BELONGS_TO, 'ActivityMessagesModel',  'relate_id'),
        );
    }






}
