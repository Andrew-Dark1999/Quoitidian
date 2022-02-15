<?php
/**
 * ValidateConfirmActions - проверка состояния данных
 * @author Alex R.
 * @version 1.0
 */
namespace Reports\extensions\ElementMaster;

use \Reports\extensions\ElementMaster as ext1;

class ValidateConfirmActions extends \ValidateConfirmActions{
           
    const ACTION_DELETE_MODULE = 8001;
    const ACTION_DELETE_ELEMENT = 8002;

    private $_validate_params;



    /**
     * исполняем действие
     */
    public function runAction($code_action){
        if(empty(static::$_code_action) || !in_array($code_action, static::$_code_action)) return;

        switch($code_action){
            case static::ACTION_DELETE_MODULE :
                $this->action8001($code_action);
                break;
            case static::ACTION_DELETE_ELEMENT :
                $this->action8002($code_action);
                break;
        }

    }




    /**
     * action8001. Module
     */
    public function action8001($code_action){
        $this->_validate_params = $this->getParams($code_action);
        if($this->_validate_params === false) return;

        $id_list = array_keys($this->_validate_params);

        $reports = \Reports\models\ReportsModel::model()->findAllByPk($id_list);
        if(empty($reports)) return;

        foreach($reports as $report){
            $schema_report = json_decode($report->schema, true);
            $schema_report = $this->prepareSchema($report->reports_id, $schema_report);
            // save
            if(!empty($schema_report)){
                $report->schema = json_encode($schema_report);
                $report->save();
            }
        }

        \Reports\models\ReportsFilterModel::model()->deleteAll('reports_id in (:reports_id)', array(':reports_id' => implode(',', $id_list)));
        $this->clearReportsUserStorage($id_list);
    }






    /**
     * action8002. Element
     */
    public function action8002($code_action){
        $this->_validate_params = $this->getParams($code_action);
        if($this->_validate_params === false) return;

        $id_list = array_keys($this->_validate_params);

        $reports = \Reports\models\ReportsModel::model()->findAllByPk($id_list);
        if(empty($reports)) return;

        foreach($reports as $report){
            $schema_report = json_decode($report->schema, true);
            $schema_report = $this->prepareSchema($report->reports_id, $schema_report);
            // save
            if(!empty($schema_report)){
                $report->schema = json_encode($schema_report);
                $report->save();
            }
        }

        \Reports\models\ReportsFilterModel::model()->deleteAll('reports_id in (:reports_id)', array(':reports_id' => implode(',', $id_list)));
        $this->clearReportsUserStorage($id_list);
    }


    /**
     * clearReportsUserStorage - удаляем все параметры за исключением date_interval
     */
    private function clearReportsUserStorage($reports_id_list){
        $storages = \Reports\models\ReportsUsersStorageModel::model()->findAll('reports_id in (:reports_id)', array(':reports_id' => implode(',', $reports_id_list)));
        if(empty($storages)) return;

        foreach($storages as $storage_model){
            if(empty($storage_model->storage_value)){
                $storage_model->delete();
                continue;
            }
            $params = json_decode($storage_model->storage_value, true);
            if(array_key_exists('date_interval', $params)){
                $params = array('date_interval' => $params['date_interval']);
                $storage_model->storage_value = json_encode($params);
                $storage_model->save();
            } else {
                $storage_model->delete;
            }
        }
    }




    /**
     * prepareSchema
     */
    private function prepareSchema($reports_id, $schema_report){
        $result = array();

        foreach($schema_report as $block){
            if(
                $block['type'] == ext1\ConstructorBuilder::ELEMENT_INDICATOR ||
                $block['type'] == ext1\ConstructorBuilder::ELEMENT_GRAPH
            ){
                $result[] = $block;
            } elseif(
                    $block['type'] == ext1\ConstructorBuilder::ELEMENT_DATA_ANALYSIS ||
                    $block['type'] == ext1\ConstructorBuilder::ELEMENT_FILTER
            ){
                $tmp = $block;
                $tmp_elements = array();
                foreach($tmp['elements'] as $element){
                    switch($element['type']){
                        case 'data_analysis_param':
                            $s = $this->getSchemaDAP($reports_id, $element);
                            break;
                        case 'data_analysis_indicator':
                            $s = $this->getSchemaDAI($reports_id, $element);
                            break;
                        case 'filter_panel':
                            $s = $this->getSchemaFilterPanel($reports_id, $element);
                            break;
                    }
                    if(!empty($s)) $tmp_elements[] = $s;
                }
                $tmp['elements'] = $tmp_elements;
                $result[] = $tmp;
            }

        }

        return $result;
    }



    private function getElementValidateParams($reports_id, $element){
        if(!isset($element['unique_index'])) return false;
        if(!isset($this->_validate_params[$reports_id])) return false;
        if(!isset($this->_validate_params[$reports_id][$element['unique_index']])) return false;

        return $this->_validate_params[$reports_id][$element['unique_index']];
    }




    private $_delete_dap_copy_id = false;

    private function getSchemaDAP($reports_id, $element){
        $validate_params = $this->getElementValidateParams($reports_id, $element);
        $result = $element;

        if($validate_params === false){
            return $result;
        } elseif(in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_PANEL_MODULE, $validate_params)){
            $this->_delete_dap_copy_id = true;
            $result['module_copy_id'] = null;
            $result['field_name'] = null;
            $result['type_date'] = null;
        } elseif(in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_PANEL_FIELD, $validate_params)){
            $result['field_name'] = \ExtensionCopyModel::model()->findByPk($result['module_copy_id'])->getPrimaryViewFieldName();
            $result['type_date'] = $result['module_copy_id'] . ':date_create';
        }

        return $result;
    }



    private function getSchemaDAI($reports_id, $element){
        $validate_params = $this->getElementValidateParams($reports_id, $element);
        $result = $element;

        if($validate_params === false && $this->_delete_dap_copy_id == false){
            return $result;
        } elseif(
                $this->_delete_dap_copy_id == true ||
                in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_PANEL_MODULE, $validate_params) ||
                in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_INDICATOR_MODULE, $validate_params) ||
                in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_INDICATOR_RELATE, $validate_params) ||
                in_array(ext1\SchemaValidate::E_ELEMENT_DATA_ANALYSIS_INDICATOR_FIELD, $validate_params))
        {
            $result['module_copy_id'] = null;
            $result['field_name'] = null;
            $result['type_indicator'] = null;
        }

        if($result['remove'] == true){
            $result = array();
        }

        return $result;
    }



    private function getSchemaFilterPanel($reports_id, $element){
        $validate_params = $this->getElementValidateParams($reports_id, $element);
        $result = $element;

        if($validate_params === false && $this->_delete_dap_copy_id == false){
            return $result;
        } elseif(
            $this->_delete_dap_copy_id == true ||
            in_array(ext1\SchemaValidate::E_ELEMENT_FILTER_MODULE, $validate_params) ||
            in_array(ext1\SchemaValidate::E_ELEMENT_FILTER_RELATE, $validate_params) ||
            in_array(ext1\SchemaValidate::E_ELEMENT_FILTER_FIELD, $validate_params))
        {
            $result['module_copy_id'] = null;
            $result['field_name'] = null;
            $result['condition'] = null;
            $result['condition_value'] = null;

        }

        if($result['remove'] == true){
            $result = array();
        }

        return $result;
    }



}

