<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationAndModel  extends \Process\components\OperationModel{

    const ELEMENT_NUMBER_BRANCHES   = 'number_branches';
    const ELEMENT_SHOW_PARAMS = 'show_params';



    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'And');
    }




    public function getBuildedParamsContent(){
        if(empty($this->_operations_model)) return;

        $schema = $this->_operations_model->getSchema();
        if(empty($schema)) return;
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        $content = '';
        foreach($schema as $element){
            $content.= $this->getElementHtml($element);
        }

        return $content;
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
            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_DONE);
            }

            /*
            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_PAUSE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
            }
            */

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
            /*
            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_PAUSE);
            }
            */
        }


        return $this;
    }



}

