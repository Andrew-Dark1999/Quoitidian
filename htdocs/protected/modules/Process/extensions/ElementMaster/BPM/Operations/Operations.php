<?php
/**
* Block widget - Набор показателей
* @author Alex R.
*/

namespace Process\extensions\ElementMaster\BPM\Operations;

use Process\models\OperationBeginModel;

class Operations extends \CWidget{

    public $view_type;
    public $element_name;
    public $element_schema;
    public $operation_model;  // class OperationModel - наследник
    public $operations_model; // class OperationsModel

    //управляет блокировкой элементов ввода
    private $_elements_enabled = true;



    public function getElementsEnabled(){
        return $this->_elements_enabled;
    }


    public function init(){
        $result = '';

        switch($this->view_type){
            case 'chevron' :
                $result = $this->getChevron();
                break;

            case 'params' :
                if($this->operations_model->getMode() != \Process\models\OperationsModel::MODE_CONSTRUCTOR){
                    $this->_elements_enabled = false;
                }

                $result = $this->getParams();
                break;

        }


        echo $result;
    }



    public function getMode(){
        return $this->operations_model->getMode();
    }



    private function getChevron(){
        return $this->render('chevron-' . $this->element_name, array(), true);
    }



    private function getParams(){
        $element_name = str_replace('_', '', $this->element_name);
        $method = 'getParams' . $element_name;

        return $this->$method();
    }




    private function getParamsBegin(){
        $element_schema = $this->element_schema;

        if(in_array($element_schema['type'], array(
            \Process\models\OperationChangeElementModel::ELEMENT_OBJECT_NAME,
            \Process\models\OperationChangeElementModel::ELEMENT_RELATE_MODULE,
            \Process\models\OperationChangeElementModel::ELEMENT_FIELD_NAME,
            \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR,
            \Process\models\OperationChangeElementModel::ELEMENT_VALUE_DATETIME,
            \Process\models\OperationChangeElementModel::ELEMENT_VALUE_RELATE,
            \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SELECT,
            \Process\models\OperationChangeElementModel::ELEMENT_LABEL_ADD_VALUE))
        ){
            return $this->render('params-change-element', $element_schema, true);
        } else {
            return $this->render('params-begin', $element_schema, true);
        }



    }


    private function getParamsEnd(){
        $element_schema = $this->element_schema;

        return $this->render('params-end', $element_schema, true);
    }



    private function getParamsAnd(){
        $element_schema = $this->element_schema;

        return $this->render('params-and', $element_schema, true);
    }



    private function getParamsTask(){
        $element_schema = $this->element_schema;

        return $this->render('params-task', $element_schema, true);
    }



    private function getParamsAgreetment(){
        $element_schema = $this->element_schema;

        return $this->render('params-agreetment', $element_schema, true);
    }


    private function getParamsCondition(){
        $element_schema = $this->element_schema;

        return $this->render('params-change-element', $element_schema, true);
    }



    private function getParamsDataRecord(){
        $element_schema = $this->element_schema;

        return $this->render('params-data_record', $element_schema, true);
    }




    private function getParamsTimer(){
        $element_schema = $this->element_schema;

        if(in_array($element_schema['type'], array(
            \Process\models\OperationChangeElementModel::ELEMENT_OBJECT_NAME,
            \Process\models\OperationChangeElementModel::ELEMENT_RELATE_MODULE,
            \Process\models\OperationChangeElementModel::ELEMENT_FIELD_NAME))
        ){
            return $this->render('params-change-element', $element_schema, true);
        } else {
            return $this->render('params-timer', $element_schema, true);
        }
    }




    private function getParamsNotification(){
        $element_schema = $this->element_schema;

        $view_lastfix = '';
        if(!($this->operation_model instanceof \Process\models\OperationNotificationFactoryModel)){
            $view_lastfix = '-' . $this->operation_model->getView();
        }

        return $this->render('params-notification' . $view_lastfix , $element_schema, true);
    }




    private function getParamsScenario(){
        $element_schema = $this->element_schema;

        return $this->render('params-scenario', $element_schema, true);
    }



}
