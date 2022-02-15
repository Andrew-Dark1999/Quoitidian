<?php
/**
 * MailerLettersInboxModel
 */

class MailerLettersInboxModel extends ActiveRecord{


    public $tableName = 'mailer_letters_inbox';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('user_create, date_receipt, date_upload, letter_from, letter_from_name, letter_to, letter_to_name, letter_subject, letter_body, status', 'safe'),
        );
    }




    public function relations(){
        return array(
            'mailerInboxParams' => array(self::HAS_ONE, 'MailerLettersInboxParamsModel', 'mailer_id'),
            'mailerInboxRelate' => array(self::HAS_ONE, 'MailerLettersInboxRelateModel', 'mailer_id'),
            'mailerInboxFiles' => array(self::HAS_MANY, 'MailerLettersInboxFilesModel', 'mailer_id'),
            'emails' => array(self::HAS_ONE, 'EmailsModel', array('email'=> 'letter_from')),
        );
    }



    protected function beforeSave(){
        if($this->getIsNewRecord()){
            $this->date_upload = new CDbExpression('now()');
        }

        return true;
    }






}
