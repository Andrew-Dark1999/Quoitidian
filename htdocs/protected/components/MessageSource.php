<?php

class MessageSource extends CPhpMessageSource{
    
    public function __construct(){
        $this->basePath = Yii::app()->messages->basePath;
    }
    
    
    public function getMessagesJs($language = null){
        if($language === null){
            $language = Yii::app()->getLanguage();
        }

        $l = $this->loadMessages('messages_js', $language);

        return $l;
    }
    
    
} 
