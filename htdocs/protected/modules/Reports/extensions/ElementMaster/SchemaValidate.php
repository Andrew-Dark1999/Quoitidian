<?php
/**
 * SchemaValidate - проверка состояния данных
 * @author Alex R.
 * @version 1.0
 */
namespace Reports\extensions\ElementMaster;

class SchemaValidate extends \SchemaValidate{


    private $_base_object;
    private $_extension_copy_schema;

    private $_module_fields = array();

    private $_module_relates = array();

    private $_invalid_list = array();
    private $_invalid_elements = array(
                                    'modules' => array(),
                                    'fields' => array(),
                                    'relates' => array(),
                                    );

    const E_ELEMENT_REPORTS_MODULE_REMOVE           = 8001;
    const E_ELEMENT_REPORTS_FIELD_REMOVE            = 8002;
    const E_ELEMENT_REPORTS_MODULE_RELATE_CHANGE    = 8003;


    const E_ELEMENT_DATA_ANALYSIS_PANEL_MODULE  = 'dap_m';
    const E_ELEMENT_DATA_ANALYSIS_PANEL_FIELD   = 'dap_f';
    const E_ELEMENT_DATA_ANALYSIS_INDICATOR_MODULE  = 'dai_m';
    const E_ELEMENT_DATA_ANALYSIS_INDICATOR_FIELD   = 'dai_f';
    const E_ELEMENT_DATA_ANALYSIS_INDICATOR_RELATE  = 'dai_r';
    const E_ELEMENT_FILTER_MODULE  = 'f_m';
    const E_ELEMENT_FILTER_FIELD   = 'f_f';
    const E_ELEMENT_FILTER_RELATE  = 'f_r';




    /**
     * setBaseObject
     */
    public function setBaseObject($base_object){
        $this->_base_object = $base_object;
        return $this;
    }





    /**
     * validateIM
     */
    public function validateIM(){
        if(empty($this->_base_object->extension_copy->copy_id)) return;

        $this->prepareBaseObjectElements();
        $this->validateRun();
    }


    /**
     * validateIMConfirm
     */
    public function validateIMConfirm(){
        if(empty($this->_base_object->extension_copy->copy_id)) return;

        $this->prepareBaseObjectElements();
        $this->validateRunConfirm();
    }



    /**
     * confirmParamsAppend
     */
    private function confirmParamsAppend(&$confirm_params, $params){
        if(empty($params)) return;

        foreach($params as $reports_id => $value){
            if(isset($confirm_params[$reports_id])){
                $confirm_params[$reports_id] = array_merge($confirm_params[$reports_id], $value);
            } else {
                $confirm_params[$reports_id] = $value;
            }

        }

    }




    /**
     * validateRun
     */
    private function validateRun(){
        $reports = \Reports\models\ReportsModel::model()->findAll();
        if(empty($reports)) return;

        $confirm_params = array();

        foreach($reports as $report){
            $schema = $report->schema;
            if(!empty($schema)) $schema = json_decode($schema, true);
            if(empty($schema)) continue;

            $params = $this->validateFieldsDataAnalysis($report->reports_id, $schema);
            if(!empty($params))
                $this->confirmParamsAppend($confirm_params, $params);

            $params = $this->validateFieldsFilters($report->reports_id, $schema);
            if(!empty($params))
                $this->confirmParamsAppend($confirm_params, $params);

            $params = $this->validateRelates($report->reports_id, $schema);
            if(!empty($params))
                $this->confirmParamsAppend($confirm_params, $params);
        }


        if(!empty($confirm_params)){
            $params = array();
            $params[\Reports\extensions\ElementMaster\ValidateConfirmActions::ACTION_DELETE_ELEMENT] = $confirm_params;
            $this->_base_object->addParams($params);

            $msg = $this->getMessage();
            $this->_base_object->addValidateResultConfirm(
                'c',
                $msg,
                \Reports\extensions\ElementMaster\ValidateConfirmActions::ACTION_DELETE_ELEMENT,
                false
            );
        }
    }






    /**
     * validateRunConfirm
     */
    private function validateRunConfirm(){
        $reports = \Reports\models\ReportsModel::model()->findAll();
        if(empty($reports)) return;

        $confirm_params = array();

        foreach($reports as $report){
            $schema = $report->schema;
            if(!empty($schema)) $schema = json_decode($schema, true);
            if(empty($schema)) continue;

            $params = $this->validateModules($report->reports_id, $schema);
            if(!empty($params))
                $this->confirmParamsAppend($confirm_params, $params);
        }


        if(!empty($confirm_params)){
            $params = array();
            $params[\Reports\extensions\ElementMaster\ValidateConfirmActions::ACTION_DELETE_MODULE] = $confirm_params;
            $this->_base_object->addParams($params);


            $msg = $this->getMessage();
            $this->_base_object->addValidateResultConfirm(
                'c',
                $msg,
                \Reports\extensions\ElementMaster\ValidateConfirmActions::ACTION_DELETE_MODULE,
                false
            );
        }
    }





    /**
     * getMessage
     * @return string
     */
    private function getMessage(){
        $msg = array(
            'begin' => \Yii::t('ReportsModule.messages', 'After removing'),
            'body' => array(),
            'end' => \Yii::t('ReportsModule.messages', 'reports can be displayed incorrectly'),
        );

        //module
        if(in_array(static::E_ELEMENT_REPORTS_MODULE_REMOVE, $this->_invalid_list)){
            $params = array('{s1}' => implode(', ', $this->_invalid_elements['modules']));
            $msg['body'][] = \Yii::t('ReportsModule.messages', 'module "{s1}"', $params);
        }
        // field
        if(in_array(static::E_ELEMENT_REPORTS_FIELD_REMOVE, $this->_invalid_list)){
            $params = array('{s2}' => implode(', ', $this->_invalid_elements['fields']));
            $msg['body'][] = \Yii::t('ReportsModule.messages', 'field(s) ({s2})', $params);
        }
        // relate
        if(in_array(static::E_ELEMENT_REPORTS_MODULE_RELATE_CHANGE, $this->_invalid_list)){
            $params = array('{s3}' => implode(', ', $this->_invalid_elements['relates']));
            $msg['body'][] = \Yii::t('ReportsModule.messages', 'relations between the modules ({s3})', $params);
        }

        if(count($msg['body']) == 1){
            $msg['body'] = $msg['body'][0];
        } elseif(count($msg['body']) > 1){
            $msg['body'] = implode(' ' . \Yii::t('ReportsModule.messages', 'and') . '  ', $msg['body']);
        }

        return implode(' ', $msg);
    }






    /**
     * isSetFieldName
     */
    private function isSetFieldName($copy_id, $field_name){
        $result = false;

        $fn = $field_name;

        if(\Reports\models\ConstructorModel::isPeriodConstant($field_name) || \Reports\models\ConstructorModel::isFieldConstant($field_name)){
            return true;
        } elseif($field_name == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
            $field_name = \ExtensionCopyModel::module()->findByPk($copy_id)->getPrimaryViewFieldName('');
        } elseif($field_name == 'b_responsible' || $field_name == 'bl_participant'){
            $field_name = array('b_responsible', 'bl_participant');
        }

        if(!is_array($field_name)) $field_name = array($field_name);

        foreach($this->_module_fields as $field){
            if(in_array($field['field_name'], $field_name)){
                $result = true;
                break;
            }
        }

        if($result == false){
            $params = $this->getFieldSchemaParams($fn);
            if(is_array($params)){
                $this->_invalid_elements['fields'][] = $params['title'];
            } else {
                $this->_invalid_elements['fields'][] = $params;
            }
            $this->_invalid_elements['fields'] = array_unique($this->_invalid_elements['fields'], SORT_STRING);
        }

        return $result;
    }





    /**
     * validateFieldsDataAnalysis - проверка блока Данные для анализа
     */
    private function validateFieldsDataAnalysis($reports_id, $schema){
        $confirm_params = array();

        $param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_param');
        $indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_indicator');

        if(!empty($param['module_copy_id']) && !empty($param['field_name']) && $param['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
            $isset = $this->isSetFieldName($param['module_copy_id'], $param['field_name']);
            if($isset == false){
                $confirm_params[$reports_id][$param['unique_index']][] = self::E_ELEMENT_DATA_ANALYSIS_PANEL_FIELD;
            }
        }


        if(!empty($indicators)){
            foreach($indicators as $indicator){
                if(!empty($indicator['module_copy_id']) && !empty($indicator['field_name']) && $indicator['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
                    $isset = $this->isSetFieldName($indicator['module_copy_id'], $indicator['field_name']);
                    if($isset == false){
                        $confirm_params[$reports_id][$indicator['unique_index']][] = self::E_ELEMENT_DATA_ANALYSIS_INDICATOR_FIELD;
                    }
                }


            }
        }

        if(!empty($confirm_params))
            $this->_invalid_list[] = self::E_ELEMENT_REPORTS_FIELD_REMOVE;

        return $confirm_params;
    }




    /**
     * validateFieldsFilters
     */
    private function validateFieldsFilters($reports_id, $schema){
        $confirm_params = array();

        $filters = \Reports\extensions\ElementMaster\Schema::getFilterElements($schema);

        if(!empty($filters)){
            foreach($filters as $filter){
                if(!empty($filter['module_copy_id']) && !empty($filter['field_name']) && $filter['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
                    $isset = $this->isSetFieldName(null, $filter['field_name']);
                    if($isset == false){
                        $confirm_params[$reports_id][$filter['unique_index']][] = self::E_ELEMENT_FILTER_FIELD;
                    }
                }
            }
        }

        if(!empty($confirm_params))
            $this->_invalid_list[] = self::E_ELEMENT_REPORTS_FIELD_REMOVE;


        return $confirm_params;
    }






    /**
     * validateModules
     */
    private function validateModules($reports_id, $schema){
        $confirm_params = array();

        $param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_param');
        if(!empty($param['module_copy_id']) && $param['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
            $confirm_params[$reports_id][$param['unique_index']][] = self::E_ELEMENT_DATA_ANALYSIS_PANEL_MODULE;
        }

        $indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_indicator');
        if(!empty($indicators)){
            foreach($indicators as $indicator){
                if(!empty($indicator['module_copy_id']) && $indicator['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
                    $confirm_params[$reports_id][$indicator['unique_index']][] = self::E_ELEMENT_DATA_ANALYSIS_INDICATOR_MODULE;
                }
            }
        }

        $filters = \Reports\extensions\ElementMaster\Schema::getFilterElements($schema);

        if(!empty($filters)){
            foreach($filters as $filter){
                if(!empty($filter['module_copy_id']) && $filter['module_copy_id'] == $this->_base_object->extension_copy->copy_id){
                    $confirm_params[$reports_id][$filter['unique_index']][] = self::E_ELEMENT_FILTER_MODULE;
                }
            }
        }

        if(!empty($confirm_params)){
            $this->_invalid_list[] = self::E_ELEMENT_REPORTS_MODULE_REMOVE;
            $this->_invalid_elements['modules'][] = $this->_base_object->extension_copy->title;
            $this->_invalid_elements['modules'] = array_unique($this->_invalid_elements['modules'], SORT_STRING);
        }

        return $confirm_params;
    }






    /**
     * isSetRelate
     */
    private function isSetRelate($copy_id){
        $result = false;
        if(!empty($this->_module_relates)){

            foreach($this->_module_relates as $module_relate){
                if($copy_id == $module_relate['relate_module_copy_id']){
                    return true;
                }
            }
        }

        if($result == false){
            $this->_invalid_elements['relates'][] = \ExtensionCopyModel::model()->findByPk($copy_id)->title;
            $this->_invalid_elements['relates'] = array_unique($this->_invalid_elements['relates'], SORT_STRING);
        }

        return $result;
    }




    /**
     * validateRelates
     */
    private function validateRelates($reports_id, $schema){
        $confirm_params = array();

        $param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_param');
        if(empty($param) || $param['module_copy_id'] === null || $param['module_copy_id'] != $this->_base_object->extension_copy->copy_id){
            return $confirm_params;
        }

        $indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($schema, 'data_analysis_indicator');
        if(!empty($indicators)){
            foreach($indicators as $indicator){
                if(!empty($indicator) && $indicator['module_copy_id']){
                    if(!empty($param['module_copy_id']) && $param['module_copy_id'] == $indicator['module_copy_id']){
                        continue;
                    }
                    $isset = $this->isSetRelate($indicator['module_copy_id']);
                    if($isset == false){
                        $confirm_params[$reports_id][$indicator['unique_index']][] = self::E_ELEMENT_DATA_ANALYSIS_INDICATOR_RELATE;
                    }
                }
            }
        }

        $filters = \Reports\extensions\ElementMaster\Schema::getFilterElements($schema);

        if(!empty($filters)){
            foreach($filters as $filter){
                if(!empty($filter['module_copy_id'])){
                    if(!empty($param['module_copy_id']) && $param['module_copy_id'] == $filter['module_copy_id']){
                        continue;
                    }

                    $isset = $this->isSetRelate($filter['module_copy_id']);
                    if($isset == false){
                        $confirm_params[$reports_id][$filter['unique_index']][] = self::E_ELEMENT_FILTER_RELATE;
                        $confirm_params[$reports_id][$filter['unique_index']][] = self::E_ELEMENT_FILTER_RELATE;
                    }

                }
            }
        }

        if(!empty($confirm_params))
            $this->_invalid_list[] = self::E_ELEMENT_REPORTS_MODULE_RELATE_CHANGE;

        return $confirm_params;
   }





    /**
     * getFieldSchemaParams
     */
    private function getFieldSchemaParams($field_name){
        if(empty($this->_extension_copy_schema))
            return $field_name;
        $params = \ExtensionCopyModel::model()->getFieldSchemaParams($field_name, $this->_extension_copy_schema);

        return $params;
    }




    /**
     * prepareBaseObjectElements
     */
    public function prepareBaseObjectElements(){
        $schema = \ExtensionCopyModel::model()->getSchemaParse($this->_base_object->schema_fature);

        if(!empty($this->_base_object->extension_copy)){
            $this->_extension_copy_schema = $this->_base_object->extension_copy->getSchemaParse();
        }

        if(empty($schema)) return;

        foreach($schema['elements'] as $element){
            if(isset($element['field'])){

                //_module_fields
                $this->_module_fields[] = array(
                    'type' => $element['field']['params']['type'],
                    'field_name' => $element['field']['params']['name'],
                );

                if(in_array($element['field']['params']['type'], array('relate', 'relate_string'))){
                    //_module_relates
                    $this->_module_relates[] = array(
                        'type' => $element['field']['params']['type'],
                        'relate_module_copy_id' => $element['field']['params']['relate_module_copy_id'],
                    );
                }

            } elseif(isset($element['sub_module'])){
                //sub_module
                $this->_module_relates[] = array(
                    'type' => 'sub_module',
                    'relate_module_copy_id' => $element['sub_module']['params']['relate_module_copy_id'],
                );
            }
        }
    }














}
