<?php

class MailerLettersOutboxMarkViewModel extends ActiveRecord {

    public $tableName = 'mailer_letters_outbox_mark_view';



    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('mailer_id, date_read', 'safe'),
        );
    }


    public function relations(){
        return array(
            'mailerOutbox' => array(self::BELONGS_TO, 'MailerLettersOutboxModel', 'mailer_id'),
        );
    }



    public static function insertMarkView($mailer_id){
        $count = static::model()->count('mailer_id=' . $mailer_id);

        if($count){
            return;
        }

        $model = new MailerLettersOutboxMarkViewModel();
        $model->setAttributes(array(
            'mailer_id' => $mailer_id,
            'date_read' => new CDbExpression('now()'),
        ));
        $model->insert();
    }


}
