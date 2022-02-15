<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationTaskModel extends OperationTaskBaseModel{



    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Tasks');
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

            $set_active = false;
            // запуск оператора
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->moveInCardRun(false); // Делаем параметр оператора Задачей...
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                $this->actionOperationSetActive();
                $set_active = true;
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                if($set_active == false) $this->moveInCardRun(true); // Делаем параметр оператора Задачей...
                if($this->getCardBStatus() == static::B_STATUS_CONPLETED){
                    $this->setStatus(OperationsModel::STATUS_DONE);
                } else if($this->getCardBStatus() == null){
                    $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                }
            }

            // выполнение оператора
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
                $this->updateCardBStatus(null, self::B_STATUS_IN_WORK);
                $this->actionOperationSetActive();
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












}

