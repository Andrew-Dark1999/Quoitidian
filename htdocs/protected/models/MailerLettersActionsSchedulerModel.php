<?php

class MailerLettersActionsSchedulerModel extends ActiveRecord{


    const ACTION_DELETE         = 'delete';
    const ACTION_SET_SEEN       = 'seen';


    const MAILBOX_NAME_INBOX    = 'inbox';
    const MAILBOX_NAME_SENT     = 'sent';
    const MAILBOX_NAME_TRASH    = 'trash';



    public $tableName = 'mailer_letters_actions_scheduler';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }




}
