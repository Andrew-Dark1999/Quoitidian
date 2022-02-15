<?php

/**
 * EditViewBuilderForCard - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */

namespace Process\extensions\ElementMaster;


class EditViewBuilderForCard extends \EditViewBuilder{


    const GROUP_BUTTON_SAVE     = 1;
    const GROUP_BUTTON_APPROVE  = 2;


    private $_operations_card_model = null;
    private $_panel_is_added = false;


    public function setOperationsCardModel($operations_card_model){
        $this->_operations_card_model = $operations_card_model;
        return $this;
    }



    public static function getGroupButtonsIndex($operations_model){
        if(
            $operations_model->element_name == \Process\models\OperationsModel::ELEMENT_TASK ||
            /*$operations_model->element_name == \Process\models\OperationsModel::ELEMENT_NOTIFICATION ||*/
            ($operations_model->element_name == \Process\models\OperationsModel::ELEMENT_AGREETMENT && $operations_model->getMode() == \Process\models\ProcessModel::MODE_CONSTRUCTOR)
        ){
            return self::GROUP_BUTTON_SAVE;
        }

        if($operations_model->element_name == \Process\models\OperationsModel::ELEMENT_AGREETMENT && $operations_model->getMode() != \Process\models\ProcessModel::MODE_CONSTRUCTOR){
            return self::GROUP_BUTTON_APPROVE;
        }

    }





    /**
     * Возвращает елемент "Блок кнопок" (BlockButton)
     * @return string (html)
     */
    public function getEditViewElementBlockButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $result = \Yii::app()->controller->widget('\Process\extensions\ElementMaster\EditView\Elements\Buttons\Buttons',
            array(
                'schema' => $schema,
                'view' => 'block',
                'content' => $this->buildEditViewPage($schema['elements']),
                'operations_model' => $this->_operations_card_model->getOperationsModel(),
            ),
            true);
        return $result;
    }









    /**
     * Возвращает елемент "Блок Панель" (BlockPanel)
     * @return string (html)
     */
    public function getEditViewElementBlockPanel($schema){
        $content = '';
        $result = '';

        if($this->_panel_is_added == false && $this->_operations_card_model instanceof \Process\models\OperationAgreetmentModel){
            $content .= $this->_operations_card_model->_elements_params[\Process\models\OperationAgreetmentModel::ELEMENT_TYPE_AGREETMENT];
            $content .= $this->_operations_card_model->_elements_params[\Process\models\OperationAgreetmentModel::ELEMENT_EMAIL];


            $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Panel.Panel'),
                array(
                    'view' => 'block_panel',
                    'content' => $content,
                ),
                true);
        }


        $result.= parent::getEditViewElementBlockPanel($schema);

        $this->_panel_is_added = true;

        return $result;
    }








    /**
     * Возвращает елемент "Кнопка" (Button)
     * @return string (html)
     */
    public function getEditViewElementButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $result = '';

        $operations_model = $this->_operations_card_model->getOperationsModel();

        // если режим Конструктора или Исполнение (оператор еще не исполнялся)
        if($operations_model->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR || $operations_model->element_name == \Process\models\OperationsModel::ELEMENT_AGREETMENT){
            // пропускаем кнопки
            if(
                isset($schema['params']['type_view']) &&
                in_array($schema['params']['type_view'], array(\Fields::TYPE_VIEW_BUTTON_STATUS, \Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION))
            ){
                return false;
            }
        }


        //добавляем OperationTaskBaseModel::ELEMENT_EXECUTION_TIME
        if( isset($schema['params']['type_view']) &&
            $schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING &&
            $operations_model->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR
        ){
            \Yii::import(\ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'));
            $result.= $this->_operations_card_model->_elements_params[\Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME];

        //добавляем стандартные элементы
        } else {

            $is_new_record = false;
            if($this->_extension_data->isNewRecord){
                $is_new_record = true;
            }

            $default_data = null;
            if($this->_extension_data->isNewRecord || \Yii::app()->request->getParam('from_template')){
                if($schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_STATUS || $schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
                    $default_data = ($schema['params']['default_value'] === '' ? null : $schema['params']['default_value']);

                    if($this->_default_data !== null && isset($this->_default_data[$schema['params']['name']])){
                        $default_data = $this->_default_data[$schema['params']['name']];
                    }
                } else if($schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){
                    $default_data = ($schema['params']['default_value'] === '' ? null : $schema['params']['default_value']);
                }
            }

            $result .= \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'),
                array(
                    'schema' => $schema,
                    'view' => 'button',
                    'extension_copy' => $this->_extension_copy,
                    'extension_data' => $this->_extension_data,
                    'is_new_record' => $is_new_record,
                    'default_data' => $default_data,
                ),
                true);
        }

        // добавляем OperationTaskBaseModel::ELEMENT_SDM_OPERATION_TASK
        if( isset($schema['params']['type_view']) &&
            $schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING &&
            $operations_model->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR)
        {
            $result.= $this->_operations_card_model->_elements_params[\Process\models\OperationTaskBaseModel::ELEMENT_SDM_OPERATION_TASK];
        }

        return $result;
    }









}
