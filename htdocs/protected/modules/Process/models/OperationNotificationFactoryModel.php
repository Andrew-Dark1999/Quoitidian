<?php
/**
 * OperationNotificationFactoryModel - фабричный метод оператора Оповещение
 * @author Alex R.
 */

namespace Process\models;


class OperationNotificationFactoryModel extends \Process\components\OperationModel{


    public $_services = array();

    const ELEMENT_TYPE_MESSAGE  = 'type_message';     // тип сообщения
    const ELEMENT_SERVICE_NAME  = 'service_name';     // название сервиса отправки
    const ELEMENT_SERVICE_VARS  = 'service_vars';


    private $_active_type_message = null;
    private $_active_service_name = null;
    private $_active_service_model = null;

    private $_change_action = null;

    protected $_is_possibly_bo = true;




    public function __construct(){
        parent::__construct();
        $this->loadServices();
    }



    public static function getInstance(){
        return new self();
    }

    private function getServiceList(){
        return array(
                '\Process\models\NotificationService\NotificationSystemModel',
                '\Process\models\NotificationService\NotificationUnisenderModel',
                );
    }


    public function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Notification');
    }


    public function getActiveTypeMessage(){
        return $this->_active_type_message;
    }


    public function getActiveServiceName(){
        return $this->_active_service_name;
    }


    public function getActiveServiceModel(){
        return $this->_active_service_model;
    }


    private function crearVars(){
        $this->_active_type_message = null;
        $this->_active_service_name = null;
        $this->_active_service_model = null;
    }




    public function checkExecution(){
        $process_model = ProcessModel::getInstance();
        if($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if($b_status == ProcessModel::B_STATUS_STOPED) return $this;

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->getStatus() == OperationsModel::STATUS_DONE){
                return $this;
            }

            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
            }

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                // отправка
                if($this->sendMessageThroughService() == false) return $this;

                $this->setStatus(OperationsModel::STATUS_DONE);
            }
        }

        return $this;
    }


    /**
     * sendMessageThroughService - отправка уведомлений
     */
    public function sendMessageThroughService(){
        $this->setDefaultVars();

        $service_model = $this->getActiveServiceModel();
        if(empty($service_model)) return true;

        $this->prepareBaseEntities();
        $result = $service_model->send();

        return $result['status'];
    }







    /**
     * loadServices - подключает сервисы
     */
    private function loadServices($only_active = true){
        foreach($this->getServiceList() as $service_class){
            $model = new $service_class();
            $model
                ->init()
                ->setOperationNfModel($this);

            if($only_active == true && $model->getEnabled()){
                $this->_services[$model->getServiceName()] = $model;
            }
        }
    }





    /**
     * setDefaultVars - установка значений по умолчанию
     */
    public function setDefaultVars(){
        $this->crearVars();

        $service_model = $this->getServiceModel();

        if(empty($service_model)) return $this;

        $this->_active_service_name = $service_model->getServiceName();
        $this->_active_service_model = $service_model;

        $tm_list = $service_model->getTypeMessagesList();
        if(!empty($tm_list)){
            $this->_active_type_message = array_keys($tm_list)[0];
        }

        return $this;
    }




    /**
     * getTypeMessagesList - возвращает список типов сообщений (email, sms)
     */
    public function getTypeMessagesList(){
        $result = array();

        if(!empty($this->_services)){
            foreach($this->_services as $service_name => $model){
                $result = array_merge($result, $model->getTypeMessagesList());
            }
        }

        return $result;
    }




    /**
     * getServicesListByMessageType - возвращает список сервисов по названию типа сообщения
     * @param $type_message_name
     * @return array
     */
    public function getServiceNameList(){
        $result = array();

        if(!empty($this->_services)){
            foreach($this->_services as $service_name => $model){
                if($model->isTypeMessage($this->_active_type_message)){
                    $result[$service_name] = $model->getServiceTitle();
                }
            }
        }

        return $result;
    }




    /**
     * getServiceModel - возвращает модель активного сервиса
     */
    private function getServiceModel($service_name = null){
        if(empty($this->_services)) return;

        if($service_name !== null && isset($this->_services[$service_name])){
            $model = $this->_services[$service_name];
        } else {
            $model = $this->_services[array_keys($this->_services)[0]];
        }

        if(!empty($this->_operations_model)){
            $model
                ->setOperationsModel($this->_operations_model)
                ->setStatus($this->_operations_model->getStatus());
        }

        return $model;
    }







    /**
     * getBuildedParamsContent - собирает контент
     */
    public function getBuildedParamsContent($schema = null){
        if(empty($this->_operations_model)) return;

        if($schema === null){
            $schema = $this->_operations_model->getSchema();
        }

        if(empty($schema)) return;

        // подготовка схемы
        $this->prepareBaseEntities($schema);

        // собираем контент
        $content = array();
        foreach($schema as $element_schema){
            if($element_schema['type'] != self::ELEMENT_SERVICE_VARS){
                $content['main'][] = $this->getElementHtml($element_schema);
            } else {
                if($this->_change_action === null){
                    $content['child'][] = $this
                                            ->getActiveServiceModel()
                                            ->setValidateElements($this->_validate_elements)
                                            ->getBuildedParamsContent($element_schema['value']);
                } else {
                    $content['child'][] = $this
                                            ->getActiveServiceModel()
                                            ->setValidateElements($this->_validate_elements)
                                            ->changeParamsContent($this->_change_action, $element_schema['value']);
                }
            }
        }

        $result = '';
        if($this->_change_action === null){
            if(!empty($content['main']))  $result.= implode('',$content['main']);
            if(!empty($content['child'])) $result.= implode('',$content['child']);
        } else {
            $result = $content;
        }

        return $result;
    }




    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    public function prepareBaseEntities($schema = null){
        $this->crearVars();

        if($schema === null && !empty($this->_operations_model)){
            $schema = $this->_operations_model->getSchema();
        }

        if(!empty($schema)){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_SERVICE_NAME));
            $active_service_name = $from_schema['value'];

            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_TYPE_MESSAGE));
            $type_message = $from_schema['value'];
        }


        $service_model = $this->getServiceModel($active_service_name);

        if(empty($service_model)){
            $this->setDefaultVars();
            return $this;
        }

        $this->_active_service_name = $service_model->getServiceName();
        $this->_active_service_model = $service_model;

        $tm_list = $service_model->getTypeMessagesList($type_message);
        if(!empty($tm_list)){
            $this->_active_type_message = array_keys($tm_list)[0];
        } else {
            $this->setDefaultVars();
        }

        return $this;
    }





    /**
     * getOtherVars - параметры оператора для формирования отображения
     */
    public function getOtherVars(){
        if(empty($this->_active_service_model)) return;

        return $this->_active_service_model->getOtherVars();
    }







    /**
     * changeParamsContent
     */
    public function changeParamsContent($action, $params){
        $result = null;

        $this->_change_action = $action;
        $schema = $params['schema_operation'];

        switch($action){
            default :
                $result = $this->getBuildedParamsContent($schema);
        }

        return $result;
    }





    public function validate(){
        $schema = $this->_operations_model->getSchema();

        foreach($schema as $element_schema){
            if($element_schema['type'] == self::ELEMENT_SERVICE_VARS){
                $model = $this
                            ->getActiveServiceModel()
                            ->prepareAll($element_schema['value'])
                            ->validate();
                break;
            }
        }

        if(!empty($model) && $model->getBeError()){
            $this->_be_error = true;
        }

        return $this;
    }





    /**
     * validateBeforeSave - проверка перед сохранение схемы оператора
     */
    public function validateBeforeSave(){
        $this
            ->prepareBaseEntities()
            ->validate();

        if($this->getBeError()){
            return false;
        }
        return true;
    }








    /**
     * thereIsSettedBindingObject - проверяет схему оператора и возвращает результат сравнения связанного объекта
     * @param string|integer $compare_value - величина для сравнения
     */
    /*
    public static function thereIsSettedBindingObject($schema, $compare_value){
        if(is_string($schema)){
            $schema = json_decode($schema, true);
        }

        // подготовка схемы
        $model = new self();
        $model->prepareBaseEntities($schema);
        $class  = get_class($model->getActiveServiceModel());
        $result = $class::thereIsSettedBindingObject($schema, $compare_value);

        return $result;
    }
    */



}
