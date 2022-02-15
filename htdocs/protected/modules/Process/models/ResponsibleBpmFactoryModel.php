<?php
/**
 * ResponsibleBpmFactoryModel
 * Author Alex R.
 */

namespace Process\models;

use Process\extensions\ElementMaster\BPM\Operations\Operations;
use Process\extensions\ElementMaster\Schema;

class ResponsibleBpmFactoryModel{


    const ACTION_CHECK  = 'action_check';
    const ACTION_UPDATE = 'action_update';


    protected $_vars;
    protected $_action = null;

    protected $_be_error = false;
    protected $_messages = array();

    protected $_process_model;

    protected $_participant_model;
    protected $_ug_id_user;
    protected $_access_update = true;
    protected $_delivery_messages = false;
    protected $_block_index_active;

    protected static $_block_index_last = 1;
    protected static $_responsible_chacked_list = [];


    public function __construct(){
        $this->_block_index_active = self::$_block_index_last++;
    }


    public static function flush(){
        self::$_block_index_last = 1;
        self::$_responsible_chacked_list = [];
    }

    public function setDeliveryMessages($delivery_messages){
        $this->_delivery_messages = $delivery_messages;
        return $this;
    }

    public function setParticipantModel($participant_model){
        $this->_participant_model = $participant_model;
        return $this;
    }


    public function setUgIdUser($ug_id_user){
        $this->_ug_id_user = $ug_id_user;
        return $this;
    }

    public function setAccessUpdate($access_update){
        $this->_access_update = $access_update;
        return $this;
    }


    public function getBlockIndex(){
        return $this->_block_index_active;
    }

    protected function addResponsibleChackedList($ug_id, $ug_type){
        self::$_responsible_chacked_list[] = $ug_id . '_' . $ug_type;
    }

    protected function isSetResponsibleChackedList($ug_id, $ug_type){
        if(self::$_responsible_chacked_list == false){
            return false;
        }

        $value = $ug_id . '_' . $ug_type;

        return in_array($value, static::$_responsible_chacked_list);
    }

    public function setVars($vars, $process_refresh = false){
        $this->_vars = $vars;

        if(isset($vars['action'])){
            $this->_action = $vars['action'];
        }

        if(isset($this->_vars['ug_id'])) $this->_vars['base_ug_id'] = $this->_vars['ug_id'];
        if(isset($this->_vars['ug_type'])) $this->_vars['base_ug_type'] = $this->_vars['ug_type'];
        if(isset($this->_vars['flag'])) $this->_vars['base_flag'] = $this->_vars['flag'];

        $this->_process_model = ProcessModel::getInstance($vars['process_id'], $process_refresh);

        return $this;
    }


    protected function addMessage($name, $text){
        $this->_be_error = true;
        $this->_messages[$name] = $text;

        return $this;
    }


    public function getMessage($name){
        if(isset($this->_messages[$name])){
            return $this->_messages[$name];
        }
    }

    public function getMessages(){
        return $this->_messages;
    }


    /**
     * validate
     */
    public function validate(){
        switch($this->_action){
            case self::ACTION_UPDATE :
                if(
                    empty($this->_vars['ug_id']) ||
                    empty($this->_vars['ug_type']) ||
                    empty($this->_vars['attributes']) ||
                    empty($this->_vars['attributes']['ug_id']) ||
                    empty($this->_vars['attributes']['ug_type'])
                ){
                    return $this->addMessage('participant_block' . $this->_block_index_active, \Yii::t('ProcessModule.messages', 'Not defined Responsible'));
                }

                if($this->isSetResponsibleChackedList($this->_vars['attributes']['ug_id'], $this->_vars['attributes']['ug_type'])){
                    return $this->addMessage('participant_block' . $this->_block_index_active, \Yii::t('ProcessModule.messages', 'Responsible already selected'));
                }

                break;
        }

        return $this;
    }




    /**
     * run
     */
    public function run(){
        if($this->_be_error){
            return $this;
        }

        $this->addResponsibleChackedList($this->_vars['attributes']['ug_id'], $this->_vars['attributes']['ug_type']);

        switch($this->_action){
            case self::ACTION_CHECK:
                $this->actionCheck();
                break;
            case self::ACTION_UPDATE:
                $this->actionUpdate();
                break;
        }

        return $this;
    }


    /**
     * actionCheck
     */
    protected function actionCheck(){}



    /**
     * sendMessageToProcessResponsible - отсылка уведомления ответственному за процесс
     */
    protected function sendMessageToProcessResponsible(){
        if($this->_delivery_messages == false) return;

        $participant_model = \ParticipantModel::getParticipants(
            \ExtensionCopyModel::MODULE_PROCESS,
            ProcessModel::getInstance()->process_id,
            null, true, true);

        if(empty($participant_model)) return;

        $edit_data = array(
            'id' => (integer)ProcessModel::getInstance()->process_id,
        );
        $edit_action_model = new \EditViewActionModel(\ExtensionCopyModel::MODULE_PROCESS); //MODULE_TASKS
        $edit_action_model
            ->setEditData($edit_data)
            ->createEditViewModel();
        $edit_model = $edit_action_model->getEditModel();



        \History::getInstance()->addToHistory(\HistoryMessagesModel::MT_PROCESS_MUST_APPOINT_RESPONSIBLE,
            \ExtensionCopyModel::MODULE_PROCESS,
            ProcessModel::getInstance()->process_id,
            array(
                '{module_data_title}' => $edit_model->getModuleTitle(),
                '{user_id}' => $participant_model->ug_id,
                '{comment}' => '',
            ),
            false,
            false,
            true,
            true
        );
    }


    /**
     * actionUpdate - обновление данных об участнике (ответственном)
     */
    protected function actionUpdate(){
        if($this->_access_update == false) return;

        $operations_ui_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationsByResponsible($this->_vars['base_ug_id'], $this->_vars['base_ug_type']);

        //$find_params
        $find_params =  array(
            'type' => \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE,
            'ug_id' => $this->_vars['base_ug_id'],
            'ug_type' => $this->_vars['base_ug_type'],
        );
        if(!empty($this->_vars['base_flag'])){
            $find_params['flag'] = $this->_vars['base_flag'];
        }

        //$new_values
        $new_values = array(
            'ug_id' => $this->_vars['attributes']['ug_id'],
            'ug_type' => $this->_vars['attributes']['ug_type'],
        );
        if($this->_vars['base_ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_CONST){
            $new_values['flag'] = (new \ParticipantConstModel())->getProcessFlagByConstType($this->_vars['base_ug_id']);
        }


        // обновление в Схеме процесса
        $schema_model = SchemaModel::getInstance()->update($find_params, $new_values);

        ProcessModel::getInstance()->setSchema($schema_model->getSchema())->save();

        // обновляем в операторах процесса
        $this->updateParticipantInOperations($operations_ui_list);
    }


    /**
     * updateParticipantInOperations - обновление ответственного в операторах процесса
     */
    private function updateParticipantInOperations($operations_ui_list){
        $process_id = ProcessModel::getInstance()->process_id;
        $schema_process = ProcessModel::getInstance()->getSchema();


        $operation_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperations($schema_process);
        if(empty($operation_list)) return;

        $operations_model = new OperationsModel();
        $operation_responsible_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsibleList($schema_process);

        foreach($operation_list as $operation){
            $participant_vars = array(
                'from' => array(
                    'ug_id' => $this->_vars['base_ug_id'],
                    'ug_type' => $this->_vars['base_ug_type'],
                ),
                'to' => array(
                    'ug_id' => $operation_responsible_list[$operation['unique_index']]['ug_id'],
                    'ug_type' => $operation_responsible_list[$operation['unique_index']]['ug_type'],
                    'flag' => $operation_responsible_list[$operation['unique_index']]['flag'],
                ),
            );


            if($operation_responsible_list[$operation['unique_index']]['flag'] == false){
                $operations_model->updateParticipantInOperation($process_id, $operation, $participant_vars);
                if(!empty($operations_ui_list) && in_array($operation['unique_index'], $operations_ui_list)){
                    $operations_model->deleteParticipantInOperation($process_id, $operation, $this->_vars['base_ug_id'], $this->_vars['base_ug_type']);
                }
            } else {
                $operations_model->replaceParticipantInOperation($process_id, $operation, $participant_vars);
            }
        }
    }



    public function getStatus(){
        return (($this->_be_error) ? false : true);
    }


    /**
     * getResult
     */
    public function getResult(){
        $result = array(
            'status' => $this->getStatus(),
            'message' => null,
        );

        if($result['status'] == false){
            $this->setHistoryMessageMarkIsView();
            $result['message'] = $this->getMessageHtml();
        }

        return $result;
    }


    /**
     * setHistoryMessageMarkIsView
     */
    private function setHistoryMessageMarkIsView(){
        \History::markHistoryIsView(\ExtensionCopyModel::MODULE_PROCESS, ProcessModel::getInstance()->process_id);
    }



    private function getMessageHtml(){
        return $this->getDialogHtml();
    }


    /**
     * getDialogHtml - возвращает верстку со списком значений
     */
    public function getDialogHtml($li_only = false){}




    /**
     * getLableTitle - подпись для элемента ввода
     */
    public function getLableTitle(){
        static::$_responsible_title_number++;

        return \Yii::t('base', 'Responsible') . ' (' . static::$_responsible_title_number . ')';
    }




}
