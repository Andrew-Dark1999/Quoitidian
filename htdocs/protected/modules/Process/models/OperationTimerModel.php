<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationTimerModel extends OperationBeginModel{

    protected $_is_possibly_bo = true;



    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Timer');
    }


    public static function setStartTimeRun($start_time_run){
        self::$_start_time_run = $start_time_run;
    }



    public static function getParamsDataStartOnTime(){
        return array(
            self::START_ON_TIME_ONE => \Yii::t('ProcessModule.base', 'Disposable start'),
            self::START_ON_TIME_DETERMINED => \Yii::t('ProcessModule.base', 'Start through determined time'),
            self::START_ON_BEFORE_TIME => \Yii::t('ProcessModule.base', 'Start up to the specified time'),
            self::START_ON_AFTER_TIME => \Yii::t('ProcessModule.base', 'Start after the specified time'),
        );
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
                (new StartTimeModel())
                                    ->setOperationsModel($this->_operations_model)
                                    ->updateSchedule();
            }



            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_START_ON_TIME));

                if(!empty($from_schema['value']) && $from_schema['value'] == self::START_ON_TIME_DISABLED){
                    $this->setStatus(OperationsModel::STATUS_DONE);
                } else{
                    $start_result = (new StartTimeModel())
                                        ->setOperationsModel($this->_operations_model)
                                        ->startSchedule();

                    if($start_result){
                        $this->setStatus(OperationsModel::STATUS_DONE);
                    }
                }


            }

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
        }


        return $this;
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

        $model = new self();
        $model->prepareBaseEntities($schema);

        if(
            $model->_active_object_name_type == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
            is_array($model->_active_object_name) && array_key_exists('type', $model->_active_object_name) &&
            $model->_active_object_name['type'] == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
            $model->_active_object_name['copy_id'] == $compare_value
        ){
            return true;
        }

        return false;
    }
    */






    protected function inRecordNameList($unique_index, $value){
        return OperationChangeElementModel::inRecordNameList($unique_index, $value);
    }





    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    protected function prepareBaseEntities($schema){
        $this->setOnlyTypes(\Fields::MFT_DATETIME);

        parent::prepareBaseEntities($schema);

        return $this;
    }









    public function getDateEnding(){
        $date_ending = parent::getDateEnding();

        return $date_ending;

        /*
        $start_result = (new StartTimeModel())
            ->setOperationsModel($this->_operations_model)
            ->startSchedule();
        */


    }





    /********************************************
    * ACTIONS
    ********************************************/


    /**
     * actionAfterSave -  вызывается после сохранение схемы оператора
     */
    public function actionAfterSave(){
        if($this->_operations_model->getMode(true) != OperationsModel::MODE_CONSTRUCTOR && $this->_operations_model->getStatus() == OperationsModel::STATUS_ACTIVE){
            (new StartTimeModel())
                ->setOperationsModel($this->_operations_model)
                ->updateSchedule();
        }

        return $this;
    }







    public function getElementObjectNameTitle(){
        return OperationChangeElementModel::getElementObjectNameTitle();
    }



    /**
     * getObjectNameList - возвращает список сущностей объекта-параметра для поля
     * @return array
     */
    public function getObjectNameList($unique_index){
       return OperationChangeElementModel::getObjectNameList($unique_index);
    }



    /**
     * actionPrepareDataForNewOperation Подготовка данных  оператора перед сохранением
     * @return $this
     */
    public function actionPrepareDataForNewOperation(){
        return $this;
    }




/**
 * ACTIONS end
 */





}

