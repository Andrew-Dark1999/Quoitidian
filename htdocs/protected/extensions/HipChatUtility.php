<?php

class HipChatUtility {



    public static function getInstance(){
        return new self();
    }

   
   /**
    *   Отправляем сообщение в HipChat
    */
    public function sendMessage($phone, $operator_id){

        if(!empty(Yii::app()->modules['hipchat']['auth_token']) && !empty(Yii::app()->modules['hipchat']['class']) && !empty($phone)){
            Yii::import(Yii::app()->modules['hipchat']['class']);
            \HipChatExt::getInstance()
                ->setUserId($operator_id)
                ->setRoomId()
                ->prepareMessage($phone)
                ->setAuthToken(Yii::app()->modules['hipchat']['auth_token'])
                ->sendMessage();
        }

    }
    
    
    public function addCard($code){
        $data = false;
        if(!empty(Yii::app()->modules['hipchat']['auth_token']) && !empty(Yii::app()->modules['hipchat']['class']) && !empty($code)){
            Yii::import(Yii::app()->modules['hipchat']['class']);
            $data = \HipChatExt::getInstance()->addCard($code);
        }
        return $data;
    }

}




