<?php

class HipChatController extends Controller{
    


    /**
     * filter
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }  

    
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){

        switch(Yii::app()->controller->action->id){
            case 'addCall':
                if(empty($_GET['phone'])){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'Not defined parameters'), false);
                }
            break;
            case 'addCard':
                 if(empty($_GET['code'])) {
                     return $this->returnCheckMessage('w', Yii::t('messages', 'Not defined parameters'), false);
                 }
            break;
        }
        
        $filterChain->run();
    }    
    


   /**
    * Добавление звонка в HipChat
    */
    public function actionAddCall(){
       \HipChatUtility::getInstance()->sendMessage($_GET['phone'], (!empty($_GET['operator_id'])) ? $_GET['operator_id'] : false);
    }


   /**
    * Новая карточка
    */
    public function actionAddCard(){
        $url = \HipChatUtility::getInstance()->addCard($_GET['code']);
        if($url)
            $this->redirect($url);
    
    }

    
    
} 
