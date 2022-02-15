<?php
/**
 * @autor Alex R.
 */

namespace Process\models;


class OperationsSaveModel{

    private $_params;
    private $_attributes;
    private $_validate;
    private $_status = true;

    private $_card_result;



    public function __construct(){
        $this->_validate = new \Validate();
    }


    public static function getInstance(){
        return new static();
    }


    public function setParams($params){
        $this->_params = $params;
        return $this;
    }


    public function setStatus($status){
        if($this->_status != false) $this->_status = $status;

        return $this;
    }


    /**
     * getResult
     */
    public function getResult(){
        $result = array();
        switch($this->_params['element_name']){
            case OperationsModel::ELEMENT_BEGIN :
            case OperationsModel::ELEMENT_END :
            case OperationsModel::ELEMENT_AND :
            case OperationsModel::ELEMENT_CONDITION :
            case OperationsModel::ELEMENT_TIMER :
            case OperationsModel::ELEMENT_NOTIFICATION :
            case OperationsModel::ELEMENT_SCENARIO :
                $result = array(
                    'status' => $this->_status,
                    'messages' => $this->_validate->getValidateResultHtml(),
                );
                break;

            case OperationsModel::ELEMENT_TASK :
            case OperationsModel::ELEMENT_AGREETMENT :
            case OperationsModel::ELEMENT_DATA_RECORD :
                if(!empty($this->_card_result) && $this->_card_result->getStatus() != \EditViewActionModel::STATUS_SAVE){
                    return $this->_card_result->getResult(false);
                } else {
                    $status = $this->_status;
                    if($status == true){
                        $status = \EditViewActionModel::STATUS_SAVE;
                    } else {
                        $status = \EditViewActionModel::STATUS_ERROR;
                    }

                    $result = array(
                        'status' => $status,
                        'messages' => $this->_validate->getValidateResultHtml(),
                    );
                }
                break;
        }

        $result['process_status'] = ProcessModel::getInstance(null, true)->getBStatus();


        if($result['status'] == true && $result['status'] == \EditViewActionModel::STATUS_SAVE){
            $result['schema'] = \Process\models\SchemaModel::getInstance()
                                        ->setOperationsExecutionStatus()
                                        ->reloadOtherParamsForSchema()
                                        ->getSchema(true);
        }


        // validate and messsages html
        if(in_array($this->_params['element_name'], array( OperationsModel::ELEMENT_BEGIN,
                                                    OperationsModel::ELEMENT_TIMER,
                                                    OperationsModel::ELEMENT_NOTIFICATION,
                                                    OperationsModel::ELEMENT_SCENARIO,
                                                    ))){
            if($result['status'] == false){
                $result['html'] = false;
            }

            $operations_model = \Process\models\OperationsModel::findByParams($this->_params['process_id'], $this->_params['unique_index']);
            if(empty($operations_model)) return $result;
            $operations = $this->_attributes['operations'];
            $operations['schema'] = $this->completeOperationSchema($operations_model, $operations['schema']);
            $operations_model->setAttributes($operations);

            $class = \Process\models\OperationsModel::getOperationClassName($this->_params['element_name']);

            if($result['status'] == true){
                if(in_array($this->_params['element_name'], array(OperationsModel::ELEMENT_NOTIFICATION))){
                    $sie = $class::getInstance()
                                        ->setOperationsModel($operations_model)
                                        ->prepareBaseEntities()
                                        ->getActiveServiceModel()
                                        ->serviceIsEnabled();
                    if($sie['status'] == false){
                        $result['messages'] = $sie['messages'];
                    }
                }

            } elseif($result['status'] == false){
                $result['html'] = $class::getInstance()
                                        ->setOperationsModel($operations_model)
                                        ->setValidateElements(true)
                                        ->actionReturnHtmlResultWhileError()
                                        ->getBuildedParamsContent();
            }
        }


        return $result;
    }



    /**
     * save
     * @return array
     */
    public function save(){
        $this->prepareAttibutes();

        switch($this->_params['element_name']){
            case OperationsModel::ELEMENT_BEGIN :
            case OperationsModel::ELEMENT_END :
            case OperationsModel::ELEMENT_AND :
            case OperationsModel::ELEMENT_CONDITION :
            case OperationsModel::ELEMENT_TIMER :
            case OperationsModel::ELEMENT_NOTIFICATION :
            case OperationsModel::ELEMENT_SCENARIO :
                $this->saveOperation();
                break;

            case OperationsModel::ELEMENT_DATA_RECORD :
                $operations_model = \Process\models\OperationsModel::findByParams($this->_params['process_id'], $this->_params['unique_index']);
                if($operations_model->getMode(true) == OperationsModel::MODE_CONSTRUCTOR){
                    $this->saveOperation();
                } else {
                    $this->saveCardDataRecord($operations_model);
                }
                break;

            case OperationsModel::ELEMENT_TASK :
            case OperationsModel::ELEMENT_AGREETMENT :
                $this->saveCard();
                if($this->_card_result->getStatus() != \EditViewActionModel::STATUS_SAVE){
                    return $this;
                }

                $this->prepareCardOperationDataForSave();
                $this->saveOperation();
                $this->_card_result = null;

                break;
        }


        $this->afterSave();

        return $this;
    }





    private function afterSave(){
        $this->checkAndRemoveOperationsToResponsible();
        $this->checkAndUpdateOperationsTitle();
    }





    /**
     * Проверяет операторы на соответствие определенному ответсвыенному в схеме, и при необходимости перемещает
     * @return bool
     */
    public function checkAndRemoveOperationsToResponsible(){
        $process_model = \Process\models\ProcessModel::getInstance();

        $por_model = (new ParticipantOperationsRemoveModel())
            ->setSchema($process_model->getSchema())
            ->prepare();

        if($por_model->getIsChanged()){
            $process_model->setSchema($por_model->getSchema());
            $process_model->save();
        }
    }




    /**
     * Проверяет и обновляет названия операторов в схеме процесса
     * @return bool
     */
    public function checkAndUpdateOperationsTitle(){
        $process_model = \Process\models\ProcessModel::getInstance();

        $por_model = (new OperationsTitleModel())
            ->setSchema($process_model->getSchema())
            ->prepare();

        if($por_model->getIsChanged()){
            $process_model->setSchema($por_model->getSchema());
            $process_model->save();
        }
    }







    /**
     * prepareAttibutes
     */
    private function prepareAttibutes(){
        $this->_attributes['operations']['process_id'] = $this->_params['process_id'];
        $this->_attributes['operations']['unique_index'] = $this->_params['unique_index'];

        switch($this->_params['element_name']){
            case OperationsModel::ELEMENT_BEGIN :
            case OperationsModel::ELEMENT_END :
            case OperationsModel::ELEMENT_AND :
            case OperationsModel::ELEMENT_CONDITION :
            case OperationsModel::ELEMENT_TIMER :
            case OperationsModel::ELEMENT_NOTIFICATION :
            case OperationsModel::ELEMENT_SCENARIO :
                $this->_attributes['operations']['schema'] = json_encode($this->_params['schema_operation']);
                break;
            case OperationsModel::ELEMENT_DATA_RECORD :
                $operations_model = \Process\models\OperationsModel::findByParams($this->_params['process_id'], $this->_params['unique_index']);
                if($operations_model->getMode(true) == OperationsModel::MODE_CONSTRUCTOR){
                    $this->_attributes['operations']['schema'] = json_encode($this->_params['schema_operation']);
                } else {
                    $this->_attributes['edit_view_data'] = $this->_params['edit_view_data'];
                }
                break;
            case OperationsModel::ELEMENT_TASK :
            case OperationsModel::ELEMENT_AGREETMENT :
            //case OperationsModel::ELEMENT_NOTIFICATION :
                $this->_attributes['operations']['schema'] = json_encode($this->_params['schema_operation']);
                $this->_attributes['edit_view_data'] = $this->_params['edit_view_data'];
                $this->prepareCardDataForSave();
                break;
        }

    }



    private function completeOperationSchema($operations_model, $schema){
        if(in_array($operations_model->element_name, array(
                                                        OperationsModel::ELEMENT_BEGIN,
                                                        OperationsModel::ELEMENT_CONDITION,
                                                        OperationsModel::ELEMENT_DATA_RECORD,
                                                        OperationsModel::ELEMENT_TIMER,
                                                        OperationsModel::ELEMENT_NOTIFICATION))
        ){
            return $schema;
        }

        $schema = json_decode($schema, true);
        $schema_saved = $operations_model->getSchema();

        $result = \Helper::arrayMerge($schema_saved, $schema);

        return json_encode($result);
    }




    /**
     * saveOperation
     */
    private function saveOperation(){
        $operations_model = \Process\models\OperationsModel::findByParams($this->_params['process_id'], $this->_params['unique_index']);

        if(empty($operations_model)){
            $operations_model = new \Process\models\OperationsModel();
        }

        $operations = $this->_attributes['operations'];

        $operations['schema'] = $this->completeOperationSchema($operations_model, $operations['schema']);

        $operations_model->setAttributes($operations);

        //saveOperation
        // сохранение схемы
        if($operations_model->saveOperation()){
            $this->setStatus(true);
        } else {
            $this->setStatus(false);
            $this->_validate->addValidateResult('e', \Yii::t('messages', 'Error saving data'));
        }


        return $this;
    }




    /**
     * prepareCardDataForSave - подготавливает данные Задачи перед сохранением
     * @return $this
     */
    private function prepareCardDataForSave(){
        unset($this->_attributes['edit_view_data']['']);

        return $this;
    }




    /**
     * prepareCardOperationDataForSave - подготавливает данные схемы оператора перед сохранением
     * @return $this
     */
    private function prepareCardOperationDataForSave(){
        $schema = json_decode($this->_attributes['operations']['schema'], true);

        $card_id = $this->_card_result->getId();

        $schema = OperationCardModel::getUpdatedValueInSchema($this->_params['element_name'], $schema, OperationCardModel::CARD_ID, $card_id);
        $this->_attributes['operations']['schema'] = $schema;
        $this->_attributes['operations']['card_id'] = $card_id;

        return $this;
    }



    /**
     * saveCardDataRecord - Сохраняет данные карточки EditView
     */
    private function saveCardDataRecord($operations_model){
        if(empty($operations_model)){
            $operations_model = new \Process\models\OperationsModel();
        }

        $this->_card_result = OperationDataRecordModel::getInstance()
                    ->setOperationsModel($operations_model)
                    ->setStatus($operations_model->getStatus())
                    ->editViewSave($this->_attributes['edit_view_data']);

        return $this;
    }





    /**
     * saveCard
     */
    private function saveCard(){
        $operations_model = \Process\models\OperationsModel::findByParams($this->_params['process_id'], $this->_params['unique_index']);

        if(empty($operations_model)){
            $operations_model = new \Process\models\OperationsModel();
        }

        $this->_card_result = OperationsModel::getChildrenModel($operations_model->element_name)
                                    ->setOperationsModel($operations_model)
                                    ->setStatus($operations_model->getStatus())
                                    ->editViewSave($this->_attributes['edit_view_data']);

        return $this;
    }








}
