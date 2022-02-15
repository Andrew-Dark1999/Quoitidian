<?php
/**
 * NotificationSystems - внутренные сервисы рассылки
 *
 * @author Alex R.
 */

namespace Process\models\NotificationService;


class NotificationSystemModel extends \Process\models\OperationCardModel{

    const TYPE_MESSAGE_NIS    = 'notice_in_system';

    const ELEMENT_SDM_OPERATION_TASK    = 'sdm_operation_task';     // элемент "Связь с задачей"

    const B_STATUS_CONPLETED    = '1';   //Завершена
    const B_STATUS_IN_WORK      = '2';   //В работе
    const B_STATUS_STOPED       = '3';   //Остановлена
    const B_STATUS_CREATED      = '4';   //Создана

    private $_operation_nf_model = null; //OperationNotificationFactoryModel
    private $_enabled = false;

    protected $_relate_copy_id = \ExtensionCopyModel::MODULE_NOTIFICATION;

    private $_type_messages = array(
        array(
            'name' => self::TYPE_MESSAGE_NIS,
            'title' => 'Notice in system',
            'enabled' => true,
        ),
    );



    public function init(){
        return $this;
    }


    public function getServiceName(){
        return 'system';
    }


    public function getView(){
        return 'system';
    }



    public function getServiceTitle(){
        return \Yii::t('ProcessModule.base', 'System');
    }


    public function setOperationNfModel($operation_nf_model){
        $this->_operation_nf_model = $operation_nf_model;
        return $this;
    }



    public function getEnabled(){
        return $this->_enabled;
    }


    /**
     *  getOtherVars - параметры оператора для формирования отображения
     */
    public function getOtherVars(){
        return array(
            'service_name' => $this->getServiceName(),
            'view' => $this->getView(),
            'edit_view_data' => $this->getEditViewDataForShow(),
        );
    }



    /**
     * getTypeMessagesList - возвращает список типов сообщений (email, sms)
     */
    public function getTypeMessagesList(){

        $result = array();

        if(!empty($this->_type_messages)){

            foreach($this->_type_messages as $type_message){
                if($type_message['enabled'] == false) continue;
                $result[$type_message['name']] = \Yii::t('ProcessModule.base', $type_message['title']);
            }
        }

        return $result;
    }


    /**
     * isTypeMessage - проверяет наличие сервиса
     */
    public function isTypeMessage($type_message_name){
        $tm = $this->getTypeMessagesList();

        if(empty($tm)) return false;

        foreach($tm as $item){
            if($item['name'] == $type_message_name)
                return true;
        }

        if(empty($tm)) return false;
    }



    protected function isSetBStatus($b_status){
        switch($b_status){
            case self::B_STATUS_CONPLETED:
            case self::B_STATUS_IN_WORK:
            case self::B_STATUS_STOPED:
            case self::B_STATUS_CREATED:
                return true;
        }

        return false;
    }





    /**
     * ***
     * Возвращает ІД связаной задачи
     */
    protected function getRelateIdCardFromSchema(){
        $schema_operator = $this->_operations_model->getSchema();
        $element = SchemaModel::getOperationElementFromSchema($schema_operator, self::ELEMENT_SDM_OPERATION_TASK);
        if(empty($element) || !empty($element[self::ELEMENT_SDM_OPERATION_TASK])) return;

        if(empty($element['value'])) return;

        $relate_model = OperationsModel::findByParams(ProcessModel::getInstance()->process_id, $element['value']);
        if(empty($relate_model)) return;

        $operation_model = OperationsModel::getChildrenModel($relate_model->element_name);

        $relate_task_id = $operation_model->getIdCardFromSchema($relate_model->getSchema());

        if(empty($relate_task_id)) return;

        return $relate_task_id;
    }






    /**
     * checkExecution - проверка выполнения, установка статуса
     * @return $this
     */
    public function checkExecution(){
        $process_model = ProcessModel::getInstance();

        if($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if($b_status == ProcessModel::B_STATUS_STOPED){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_STOPED);
            }

            if($this->getCardBStatus() != static::B_STATUS_CONPLETED){
                $this->updateCardBStatus(null, self::B_STATUS_STOPED);
            }

            return $this;
        }

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            $set_active = false;
            // запуск оператора
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->moveInCardRun(false); // Делаем параметр оператора карточкой...
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $set_active = true;
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                if($set_active == false) $this->moveInCardRun(true); // Делаем параметр оператора карточкой...
                if($this->getCardBStatus() == static::B_STATUS_CONPLETED){
                    $this->setStatus(OperationsModel::STATUS_DONE);
                } else {
                    $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                }
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
            }


            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->updateCardBStatus(null, self::B_STATUS_CONPLETED);
                $this->setStatus(OperationsModel::STATUS_PAUSE);
            }

        }

        return $this;
    }












    /**
     * thereIsSettedBindingObject - проверяет схему оператора и возвращает результат сравнения связанного объекта
     * @param string|integer $compare_value - величина для сравнения
     */
    /*
    public static function thereIsSettedBindingObject($schema, $compare_value){
        return false;
    }
    */











}
