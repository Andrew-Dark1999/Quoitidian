<?php
/**
 * MailerLettersUidGettingModel
 */

class MailerLettersUidGettingModel extends ActiveRecord {

    const MAILBOX_NAME_INBOX    = 'inbox';
    const MAILBOX_NAME_SENT     = 'sent';
    const MAILBOX_NAME_TRASH    = 'trash';


    public $tableName = 'mailer_letters_uid_getting';



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function relations(){
        return array();
    }



    public function rules(){
        return array(
            array('email, mailbox_name, uid', 'safe'),
        );
    }



    public function setScopeEmail($email){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'email=:email',
            'params' => array(':email' => $email),
        ));

        return $this;
    }




    public function setScopeMailboxName($mailbox_name){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'mailbox_name=:mailbox_name',
            'params' => array(':mailbox_name' => $mailbox_name),
        ));

        return $this;
    }



    public static function getUidByParams($email, $mailbox_name){
        $model = static::model()
                    ->setScopeEmail($email)
                    ->setScopeMailboxName($mailbox_name)
                    ->find();

        if($model){
            return $model->uid;
        }
    }



    public static function insertOrUpdate($attributes){
        if(
            array_key_exists('email', $attributes) ==  false ||
            array_key_exists('mailbox_name', $attributes) == false ||
            array_key_exists('uid', $attributes) == false
        ){
            return false;
        }

        $model = MailerLettersUidGettingModel::model()->find([
                            'condition' => 'email=:email AND mailbox_name=:mailbox_name',
                            'params' => [
                                ':email' => $attributes['email'],
                                ':mailbox_name' => $attributes['mailbox_name'],
                            ],
                        ]);

        if($model == false){
            $model = new static();
        }

        $model->setAttributes($attributes);
        $model->save();

        return true;
    }



}
