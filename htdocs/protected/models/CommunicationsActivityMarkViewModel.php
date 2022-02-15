<?php

class CommunicationsActivityMarkViewModel extends ActiveRecord{

    private $_error = false;
    private $_messages = array();

    private $_result = array();

    public $params_model = null;



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function tableName(){
        return '{{communications_activity_mark_view}}';
    }



    public function relations(){
        return array(
            'activity' => array(self::BELONGS_TO, 'ActivityMessagesModel', 'activity_messages_id'),
        );
    }



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    protected function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        return array(
                'status' => $this->getStatus(),
                'messages' => array_merge($this->_messages, (!empty($this->_result['messages'])) ? $this->_result['messages'] : array()),
            ) + $this->_result;
    }

}