<?php
/**
* Validate - проверка состояния данных формы конструктора и схемы полей  
* @author Alex R.
* @version 1.0
*/

class Validate{
    
    const TM_ERROR = 'error';
    const TM_WARNING = 'warning';
    const TM_INFORMATION = 'information';
    const TM_SUCCESS = 'success';
    const TM_CONFIRM = 'confirm';
    
    
    protected $validate_result = array();
    protected $type_messages = array('e' => 'error', 'w' => 'warning', 'i' => 'information', 's' => 'success', 'c' => 'confirm');
    
    // количество error, warning, information, success
    public $error_count = 0;
    public $warning_count = 0;
    public $information_count = 0;
    public $success_count = 0;
    public $confirm_count = 0;

    protected $_confirm_button_default = true;
    protected $_buttons = array(
        'Close'=> array('type'=>'button', 'class'=>'btn btn-default close-button', 'data-dismiss'=>'modal'),
    );
    protected $_params = array();
    

    public static function getInstance(){
        return new static();
    }
    
    /**
    *   Добавление сообщения
    *   @param string(1) $type_message
    *   @param string $message
    */
    public function addValidateResult($type_message, $messages, $code_action = null, $add_before= false){
        $messages = (array)$messages;

        foreach($messages as $message){
            $str = array(
                'type' => $this->type_messages[$type_message],
                'message' => $message,
                'code_action' => $code_action,
            );

            if($add_before){
                array_unshift($this->validate_result, $str);
            } else{
                $this->validate_result[] = $str;
            }


            $attr_name = $this->type_messages[$type_message] . '_count';
            $this->$attr_name++;


        }

        return $this;
    }



    /**
    *   Добавление сообщения типа "confirm"
    *   @param string(1) $type_message
    *   @param string $message
    */
    public function addValidateResultConfirm($type_message, $message, $code_action = null, $only_one = true){
        if($only_one && $this->beMessagesConfirmOnly()) return;
        
        $this->validate_result[] = array(
            'type' => $this->type_messages[$type_message],
            'message' => $message,
            'code_action' => $code_action,
        );
        
        $attr_name = $this->type_messages[$type_message] . '_count';
        $this->$attr_name++;
        
        return $this;
    }



    /**
    *   Добавление сообщений из массива ошибок, возвращенних из модели
    *   @param array $model_errors
    */
    public function addValidateResultFromModel(array $model_errors){
        foreach($model_errors as $errors){
            foreach($errors as $error){
                if(!is_array($error)){
                    $this->addValidateResult('e', $error);
                } else {
                    $this->addValidateResult('e', $error['message']);
                }
            }
        }
        return $this;
    }



    /**
     * возвращает  весь массив уведомлений 
     */
    public function getValidateResult(){
        return $this->validate_result;
    }

    /**
     * @param array $validate_result
     * @return Validate
     */
    public function setValidateResult($validate_result)
    {
        $this->validate_result = $validate_result;

        return $this;
    }


    /**
     * возвращает массив уведомлений без типа "confirm" 
     */
    public function getValidateWithOutConfirm(){
        if(!$this->confirm_count) return $this->validate_result;
        $result = array();
        foreach($this->validate_result as $validate){
            if($validate['type'] == 'confirm') continue;
            $result[] = $validate;
        }

        return $result;
    }



    public function setConfirmButtonsDefault($status = true){
        $this->_confirm_button_default = $status;
        return $this;
    }


    public function setParams($params){
        $this->_params = $params;
        return $this;
    }

    public function addParams($params){
        $this->_params[] = $params;
        return $this;
    }

    public function setButtons($buttons = array('Close'=> array('type'=>'button', 'class'=>'btn btn-default close-button', 'data-dismiss'=>'modal'))){
        $this->_buttons = $buttons;
        return $this;
    }

    public function setButtonsConfirm($buttons = array(array('name'=>'Yes', 'class' => 'btn btn-default confirm-yes-button'), array('name'=>'No', 'class' => 'btn btn-default close-button'))){
        $this->_buttons = array();
        foreach($buttons as $button){
            $this->_buttons[$button['name']] = array('type'=>'button', 'class' => $button['class'], 'data-dismiss'=>'modal');
        }
        return $this;
    }


    public function getButtons(){
        return $this->_buttons;
    }


    /**
     * возвращает уведомления в html для поп-апа
     */ 
    public function getValidateResultHtml(){
        if($this->beMessagesConfirmOnly()){
            if($this->_confirm_button_default) $this->setButtonsConfirm();
            return Yii::app()->controller->renderPartial(ViewList::getView('dialogs/message'), array(
                        'messages' => $this->getValidateResult(),
                        'translate' => false,
                        'buttons' => $this->getButtons(),
                        'params' => $this->_params,
                    ), true);
        }


        if($this->beMessages()){
            return Yii::app()->controller->renderPartial(ViewList::getView('dialogs/message'), array(
                        'messages' => $this->getValidateWithOutConfirm(),
                        'translate' => false,
                        'buttons' => $this->getButtons(),
                        'params' => $this->_params,
                    ), true);
        }
    }

   
   
   
    /**
     * возвращает наличие уведолений
     * @return boolean
     */
    public function beMessages($type_message = null){
        if($type_message !== null){
            switch($type_message){
                case self::TM_ERROR :
                    return (boolean)$this->error_count;
                case self::TM_WARNING :
                    return (boolean)$this->warning_count;
                case self::TM_INFORMATION :
                    return (boolean)$this->information_count;
                case self::TM_SUCCESS :
                    return (boolean)$this->success_count;
                case self::TM_CONFIRM :
                    return (boolean)$this->confirm_count;
            }
        }
        
        return !empty($this->validate_result);
    }
    

 
    /**
     * возвращает наличие только уведомлений с подтверждением
     */
    public function beMessagesConfirmOnly(){
        if(($this->error_count + $this->warning_count + $this->information_count + $this->success_count) == 0 && $this->confirm_count > 0)
            return true;
        else
            return false;
    }
        


    /**
     * возвращает наличие только уведомлений с подтверждением
     */
    public function beMessagesWithOutConfirm(){
        return (boolean)($this->error_count + $this->warning_count + $this->information_coun + $this->success_count);
    }



}
