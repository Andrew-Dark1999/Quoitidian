<?php

/**
 * Class MailerLettersOutboxRelateModel
 */


class MailerLettersOutboxRelateModel extends ActiveRecord{

    // Источники исходящих сообщений
    const RESOURCE_TYPE_SYSTEM       = 'system';
    const RESOURCE_TYPE_ACTIVITY     = 'activity';
    const RESOURCE_TYPE_NOTIFICATION = 'notification';

    public $tableName = 'mailer_letters_outbox_relate';



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function relations(){
        return array(
            'mailerOutbox' => array(self::BELONGS_TO, 'MailerLettersOutboxModel', 'mailer_id'),
            'mailerOutboxParams' => array(self::HAS_ONE, 'MailerLettersOutboxParamsModel', array('mailer_id' => 'mailer_id')),
            'activityMessages' => array(self::BELONGS_TO, 'ActivityMessagesModel',  'relate_id'),
        );
    }



    public function saveModel($params){
        $this->mailer_id = $params['mailer_id'];
        $this->relate_id = $params['relate_id'];
        $this->resource_type = $params['resource_type'];

        if ($this->save()){
            return $this;
        }
        return false;
    }


}
