<?php

/**
 * EditViewBuilderForDr - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */

namespace Process\extensions\ElementMaster;


class EditViewBuilderForDr extends \EditViewBuilder{


    private $_operations_model = null;

    private $is_new = false;


    public function setOperationsModel($operations_model){
        $this->_operations_model = $operations_model;
        return $this;
    }


    public function setIsNew($is_new){
        $this->is_new = $is_new;
        return $this;
    }


    /**
     * Возвращает елемент "Тип поля" (Edit)
     * @return string (html)
     */
    public function getEditViewElementEdit($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $default_data = null;

        if($this->_extension_data->isNewRecord){
            $default_data = $schema['params']['default_value'];
        }


        $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
            array(
                'schema' => $schema,
                'primary_entities' => $this->_primary_entities,
                'parent_copy_id' => $this->_parent_copy_id,
                'parent_data_id' => $this->_parent_data_id,
                'parent_relate_data_list' => $this->_parent_relate_data_list,
                'extension_copy' => $this->_extension_copy,
                'extension_data' => $this->_extension_data,
                'default_data' => $default_data,
                'this_template' => $this->_this_template,
                'relate' => $this->_relate,
            ),
            true);
        return $result;
    }







    /**
     * Возвращает елемент "Кнопка" (Button)
     * @return string (html)
     */
    public function getEditViewElementButton($schema){
        if($this->is_new == false){
            return parent::getEditViewElementButton($schema);
        }


        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $is_new_record = false;
        if($this->_extension_data->isNewRecord){
            $is_new_record = true;
        }

        $default_data = null;

        if($this->_operations_model->getStatus() == \Process\models\OperationsModel::STATUS_ACTIVE && $schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){
            $operation_model = $this->_operations_model->getOperationsModel();
            $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($operation_model->getSchema(), array('only_first' => true, 'field_name' => $schema['params']['name'], 'type' => \Process\models\OperationDataRecordModel::ELEMENT_VALUE_BLOCK));
            if(!empty($from_schema['value'])){
                $default_data = ($from_schema['value'] === '' ? null : $from_schema['value']);
            } else {
                if($this->_extension_data->isNewRecord){
                    $default_data = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsible(null, $operation_model->unique_index);
                }
            }
        }

        if(($this->_extension_data->isNewRecord || \Yii::app()->request->getParam('from_template'))&&
            ($schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_STATUS || $schema['params']['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING)){

            $default_data = $schema['params']['default_value'];
            if($this->_default_data !== null && isset($this->_default_data[$schema['params']['name']]))
                $default_data = $this->_default_data[$schema['params']['name']];
        }

        $result =\Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Buttons.Buttons'),
            array(
                'schema' => $schema,
                'view' => 'button',
                'extension_copy' => $this->_extension_copy,
                'extension_data' => $this->_extension_data,
                'is_new_record' => $is_new_record,
                'default_data' => $default_data,
            ),
            true);

        return $result;
    }









}
