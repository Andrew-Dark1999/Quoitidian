<?php

/**
 * Schema - Мастер динамических полей модуля
 * @author Alex R.
 * @version 1.0
 */

namespace Reports\extensions\ElementMaster;

use \Reports\extensions\ElementMaster as Extensions;
use \Reports\models\ConstructorModel;

class Schema
{

    private $_schema = array();
    private $_result_schema = array();

    private $_filter_module_copy_id_list = null;

    private $_graph_type = null;

    private $_data_analisis_param = null;
    private $_data_analisis_indicators = null;

    private $_schema_data_analisis_param = array();
    private $_schema_data_analisis_indicators = array();

    private $_filters;

    private $_set_from_users_storage = false;

    private $_there_is_param_indicator = false; /// указывает, что есть индикатор, модуль которого равен модулю-параметру при \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID


    private static $_instance;


    public static function getInstance($refresh = false)
    {
        if(self::$_instance === null || $refresh){
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    public function __construct()
    {
        $params = array(
            '_date_interval_start' => date('Y-m-d 00:00:00', strtotime('- 1 month')),
            '_date_interval_end' => date('Y-m-d 23:59:59'),
        );

        $this->setFilters($params);
    }


    public function setSchemaDataAnalisisParam($param, $update = true)
    {
        if($update == true){
            $this->_schema_data_analisis_param = $param;
            return;
        }
        if(empty($this->_schema_data_analisis_param)){
            $this->_schema_data_analisis_param = $param;
        }
    }


    public function getSchemaDataAnalisisParam()
    {
        return $this->_schema_data_analisis_param;
    }


    public function setSchemaDataAnalisisIndicators($param, $update = true)
    {
        if($update == true){
            $this->_schema_data_analisis_indicators[$param['unique_index']] = $param;
            return;
        }
        if(empty($this->_schema_data_analisis_indicators[$param['unique_index']])){
            $this->_schema_data_analisis_indicators[$param['unique_index']] = $param;
        }
    }


    public function getSchemaDataAnalisisIndicators()
    {
        return $this->_schema_data_analisis_indicators;
    }


    /**
     * setThereIsParamIndicator - установка параметра _there_is_param_indicator.
     *                            Указывает на существование показателя, модуль которого соответсвует молулю-параметру
     *                            при \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID
     */
    public function setThereIsParamIndicator($indicator)
    {
        if(
            $this->_there_is_param_indicator == false &&
            !empty($indicator['module_copy_id']) &&
            !empty($indicator['type']) &&
            $indicator['type'] == 'data_analysis_indicator' &&
            $this->_schema_data_analisis_param &&
            $this->_schema_data_analisis_param['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID
        ){
            $b = ($this->_schema_data_analisis_param['module_copy_id'] == $indicator['module_copy_id']);
            $this->_there_is_param_indicator = $b;
        }


    }


    public function getThereIsParamIndicator()
    {
        return $this->_there_is_param_indicator;
    }


    /**
     * prepareSchema - дополнительная обработка схемы.
     */
    public function prepareSchema(&$schema)
    {
        foreach($schema as &$element){
            if(isset($element['type']) && $element['type'] == 'data_analysis'){

                foreach($element['elements'] as &$item){

                    if(isset($item['type']) && $item['type'] == 'data_analysis_param'){
                        $this->dataAnalisysCheckResponsible($item);
                    }
                }
                unset($item);
            }
        }
        unset($element);
    }


    /**
     * setSchema
     */
    public function setSchema($schema)
    {
        $this->prepareSchema($schema);

        $this->_schema = $schema;

        return $this;
    }


    public function getResultSchema()
    {
        return $this->_result_schema;
    }


    /**
     * Построение схемы элементов конструктора
     */
    public function buildSchema($return = false)
    {
        $result = $this->buildCycleSchema($this->_schema);

        if($return == false){
            $this->_result_schema = $result;
            $result = $this;
        }

        return $result;
    }


    public function buildCycleSchema($schema)
    {
        $result = array();

        foreach($schema as $element){
            $node = $this->addNode($element);
            if($node === false) continue;
            $result[] = $this->addNode($element);
        }
        return $result;
    }


    /**
     * Добавляет элементы конструктора
     */
    private function addNode($element_base)
    {
        $element = $element_base;

        if(!isset($element['type']) || empty($element['type'])) return;

        if(isset($element['elements']) && !empty($element['elements']))
            $element['elements'] = $this->buildCycleSchema($element['elements']);

        switch($element['type']){
            case 'indicator' :
                return $this->getElementIndicator($element);

            case 'indicator_panel' :
                return $this->getElementIndicatorPanel($element);

            case 'graph' :
                return $this->getElementGraph($element);

            case 'graph_element' :
                return $this->getElementGraphElement($element);

            case 'data_analysis' :
                return $this->getElementDataAnalysis($element, $element_base);

            case 'data_analysis_param' :
                return $this->getElementDataAnalysisParam($element);

            case 'data_analysis_indicator' :
                return $this->getElementDataAnalysisIndicator($element);

            case 'filter' :
                return $this->getElementFilter($element);

            case 'filter_panel' :
                return $this->getElementFilterPanel($element);
        }
    }


    /**
     * fieldIsNumeric
     */
    private function fieldIsNumeric($copy_id, $field_name)
    {
        if(empty($copy_id) || empty($field_name)) return true;

        if($field_name == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) return true;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        $params = $extension_copy->getFieldSchemaParams($field_name);

        if($params['params']['type'] == 'numeric') return true;

        return false;
    }


    /**
     * addDataAnalisysIndicatorItem
     * @param $indicator
     * @param bool $num_only
     */
    private function addDataAnalisysIndicatorItem($indicator, $num_only = true)
    {
        if($num_only){
            if($this->fieldIsNumeric($indicator['module_copy_id'], $indicator['field_name']) == false) return false;
        }
        $this->_data_analisis_indicators[] = $indicator;

        return true;
    }


    /**
     * addDataAnalisysIndicatorItemList
     * @param $indicator
     * @param bool $num_only
     */
    private function addDataAnalisysIndicatorItemList($indicators, $num_only = true)
    {
        foreach($indicators as $indicator){
            $this->addDataAnalisysIndicatorItem($indicator, $num_only);
        }
        return $this;
    }


    /**
     * setupGlobalDataAnalysisParams
     */
    public function setupGlobalDataAnalysisParams()
    {
        $result = null;

        $this->_data_analisis_param = null;
        $this->_data_analisis_indicators = null;

        //param
        if(!empty($this->_schema))
            foreach($this->_schema as $schema){
                if($schema['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                    foreach($schema['elements'] as $element){
                        if($element['type'] == 'data_analysis_param'){
                            $result = array(
                                'title' => $element['title'],
                                'module_copy_id' => $element['module_copy_id'],
                                'field_name' => $element['field_name'],
                                'type_date' => (!empty($element['type_date']) ? $element['type_date'] : null),
                                'unique_index' => $element['unique_index'],
                            );

                            $this->checkFieldName($result);
                            $this->setSchemaDataAnalisisParam($result, false);
                            break;
                        }
                    }
                    break;
                }
            }


        $this->_data_analisis_param = $result;

        //indicators
        if(!empty($this->_schema))
            foreach($this->_schema as $schema){
                if($schema['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                    foreach($schema['elements'] as $element){
                        if($element['type'] == 'data_analysis_param') continue;
                        $result = array(
                            'title' => $element['title'],
                            'module_copy_id' => $element['module_copy_id'],
                            'field_name' => $element['field_name'],
                            'type_indicator' => $element['type_indicator'],
                            'period' => 'month',
                            'display_option' => \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY,
                            'unique_index' => $element['unique_index'],
                        );
                        $this->checkFieldName($result);
                        $this->setSchemaDataAnalisisIndicators($result, false);
                        $this->addDataAnalisysIndicatorItem($result, false);
                    }
                }
            }


        return $this;
    }




    /*****************************************
     * INDICATORS
     *****************************************/


    /**
     * getElementIndicatorData
     */
    private function getElementIndicatorData($indicator_settings)
    {
        $result = array(
            'param' => array(),
            'indicators' => array(),
        );

        //param
        if($this->_data_analisis_param !== null && !empty($this->_data_analisis_param)){
            $result['param'] = $this->_data_analisis_param;
        } else{
            if(!empty($this->_schema))
                foreach($this->_schema as $schema){
                    if($schema['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        foreach($schema['elements'] as $element){
                            if($element['type'] == 'data_analysis_param'){
                                $result['param'] = array(
                                    'title' => $element['title'],
                                    'module_copy_id' => $element['module_copy_id'],
                                    'field_name' => $element['field_name'],
                                    'type_date' => (!empty($element['type_date']) ? $element['type_date'] : null),
                                    'unique_index' => $element['unique_index'],
                                );
                                break;
                            }
                        }
                        break;
                    }
                }
            $this->checkFieldName($result['param']);
            $this->setSchemaDataAnalisisParam($result['param'], false);
            $this->_data_analisis_param = $result['param'];
        }


        //indicators
        if($this->_data_analisis_indicators !== null && !empty($this->_data_analisis_indicators)){
            $result['indicators'] = $this->_data_analisis_indicators;
        } else{
            if(!empty($this->_schema))
                foreach($this->_schema as $schema){
                    if($schema['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        foreach($schema['elements'] as $element){
                            if($element['type'] == 'data_analysis_param') continue;
                            $indicator = array(
                                'title' => $element['title'],
                                'module_copy_id' => $element['module_copy_id'],
                                'field_name' => $element['field_name'],
                                'type_indicator' => $element['type_indicator'],
                                'unique_index' => $element['unique_index'],
                            );

                            $this->checkFieldName($indicator);
                            $this->setSchemaDataAnalisisIndicators($indicator, false);

                            if($this->fieldIsNumeric($element['module_copy_id'], $element['field_name']) == false) continue;
                            if($this->addDataAnalisysIndicatorItem($indicator, false)){
                                $result['indicators'][] = $indicator;
                            }
                        }
                    }
                }
        }


        $data_model = new \Reports\models\ConstructorModel();
        $data_model->calculateDataReportForIndicator($this->_schema, $result['indicators'], $this->getDataSettings(), $indicator_settings);

        return $result;
    }


    /**
     * Indicator
     */
    private function getElementIndicator($settings = array())
    {
        $schema = array(
            'type' => 'indicator',
            'title' => \Yii::t('ReportsModule.base', 'Set of indicators'),
            'data' => $this->getElementIndicatorData($settings),
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '121',
            'remove' => true,
            'elements' => array(),
        );

        return \Helper::arrayMerge($schema, $settings);
    }


    /**
     * IndicatorPanel
     */
    private function getElementIndicatorPanel($settings = array())
    {
        $schema = array(
            'type' => 'indicator_panel',
            'module_copy_id' => null,
            'field_name' => null,
            'color' => 'gray',
            'remove' => true,
            'unique_index' => null,
        );
        return \Helper::arrayMerge($schema, $settings);
    }










    /*****************************************
     *
     *   GRAPH
     *
     *****************************************/


    /**
     * getElementGraphPeriod
     */
    private function getElementGraphPeriod($schema)
    {
        if(empty($schema)) return;

        $graph_type = null;
        if($this->_graph_type !== null){
            $graph_type = $this->_graph_type;
        } else{
            if(!empty($schema)){
                if($schema['type'] == ConstructorBuilder::ELEMENT_GRAPH){
                    foreach($schema['elements'] as $element){
                        $graph_type = $element['graph_type'];
                    }
                }
            }
        }

        $result = array();
        switch($graph_type){
            case \Reports\models\ConstructorModel::GRAPH_LINE :
                $result = \Reports\models\ConstructorModel::getInstance()->getGraphPeriods(array('day', 'week', 'month', 'quarter', 'year'));
                break;
            case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM :
                $result = \Reports\models\ConstructorModel::getInstance()->getGraphPeriods(array('all_period'));
                break;
            case \Reports\models\ConstructorModel::GRAPH_CIRCULAR :
                $result = \Reports\models\ConstructorModel::getInstance()->getGraphPeriods(array('month'));
                break;
        }

        return $result;
    }


    /**
     * Graph, getElementGraphData
     */
    private function getElementGraphData($schema_element = null)
    {
        $result = array(
            'periods' => $this->getElementGraphPeriod($schema_element),
            'param' => array(),
            'indicators' => array(),
            'data' => array(),
        );


        //param
        if($this->_data_analisis_param !== null && !empty($this->_data_analisis_param)){
            $result['param'] = $this->_data_analisis_param;
        } else{
            if(!empty($this->_schema))
                foreach($this->_schema as $schema){
                    if($schema['type'] == \Reports\extensions\ElementMaster\ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        foreach($schema['elements'] as $element){
                            if($element['type'] == 'data_analysis_param'){
                                $result['param'] = array(
                                    'title' => $element['title'],
                                    'module_copy_id' => $element['module_copy_id'],
                                    'field_name' => $element['field_name'],
                                    'type_date' => (!empty($element['type_date']) ? $element['type_date'] : null),
                                    'unique_index' => $element['unique_index'],
                                );
                                $this->checkFieldName($result['param']);
                                $this->setSchemaDataAnalisisParam($result['param'], false);
                                break;
                            }
                        }
                        break;
                    }
                }
            $this->_data_analisis_param = $result['param'];
        }

        //indicators
        if($this->_data_analisis_indicators !== null && !empty($this->_data_analisis_indicators)){
            $result['indicators'] = $this->_data_analisis_indicators;
        } else{
            if(!empty($this->_schema))
                foreach($this->_schema as $schema){
                    if($schema['type'] == \Reports\extensions\ElementMaster\ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        foreach($schema['elements'] as $element){
                            if($element['type'] == 'data_analysis_param') continue;

                            $indicator = array(
                                'title' => $element['title'],
                                'module_copy_id' => $element['module_copy_id'],
                                'field_name' => $element['field_name'],
                                'type_indicator' => $element['type_indicator'],
                                'unique_index' => $element['unique_index'],
                            );

                            $this->checkFieldName($indicator);
                            $this->setSchemaDataAnalisisIndicators($indicator, false);
                            if($this->addDataAnalisysIndicatorItem($indicator))
                                $result['indicators'][] = $indicator;
                        }
                    }
                }
        }

        return $result;
    }


    /**
     * Graph, getElementGraphElementData
     */
    private function getElementGraphElementData($schema_element)
    {
        $data_setting = $this->getDataSettings();

        if(!empty($data_setting['indicators'])){
            foreach($data_setting['indicators'] as &$indicator){
                $indicator['period'] = ($schema_element['period'] ? $schema_element['period'] : 'month');
                $indicator['graph_type'] = $schema_element['graph_type'];

                switch($schema_element['graph_type']){
                    case \Reports\models\ConstructorModel::GRAPH_LINE :
                        $indicator['display_option'] = ($schema_element['display_option'] ? $schema_element['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM :
                        $indicator['period'] = ($schema_element['period'] ? $schema_element['period'] : 'all_period');
                        $indicator['display_option'] = ($schema_element['display_option'] ? $schema_element['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_CIRCULAR :
                        $indicator['period'] = ($schema_element['period'] ? $schema_element['period'] : 'month');
                        $indicator['display_option'] = ($schema_element['display_option'] ? $schema_element['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_CRATER :
                        $indicator['display_option'] = ($schema_element['display_option'] ? $schema_element['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                }

            }
            unset($indicator);
        }

        $data_model = new \Reports\models\ConstructorModel();

        return $data_model->getDataReportForGraph($this->_schema, $schema_element, $data_setting);
    }


    /**
     * Graph
     */
    private function getElementGraph($settings = array())
    {
        $schema = array(
            'type' => 'graph',
            'title' => \Yii::t('ReportsModule.base', 'Graphics name'),
            'remove' => true,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '123',
            'data' => $this->getElementGraphData($settings), // для списка параметров в конструкторе
            'elements' => array(),
        );

        return \Helper::arrayMerge($schema, $settings);
    }


    /**
     * GraphElement
     */
    private function getElementGraphElement($settings = array())
    {
        $schema = array(
            'type' => 'graph_element',
            'graph_type' => null,
            'position' => \Reports\models\ConstructorModel::GRAPH_POSITION_BOTTON,
            'period' => 'month', // по умолчанию
            'display_option' => null,
            'indicator' => null, // по умолчанию
            'data' => array(),
            'data_indicators' => array(),
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '124',
        );

        $schema = \Helper::arrayMerge($schema, $settings);

        $this->getElementGraphData();

        $schema['data'] = $this->getElementGraphElementData($schema);

        return $schema;
    }







    /*****************************************
     *
     *   DATA ANALYSIS
     *
     *****************************************/


    /**
     * getDataAnalysisModules - возвразает в виде массива данные по модуля и их параметров для Параметров и Показателей
     */
    private function getDataAnalysisModules($settings_base, $return_param_only = false)
    {
        $modules_param = \Reports\models\ConstructorModel::getInstance()->getModulesParam();

        if($return_param_only){
            return $modules_param;
        }
        $modules_indicator = array();
        $module_copy_id = null;

        if(isset($settings_base['elements'][0]['type']) && $settings_base['elements'][0]['type'] == 'data_analysis_param' && isset($settings_base['elements'][0]['module_copy_id']) && $settings_base['elements'][0]['module_copy_id'] !== null){
            $module_copy_id = $settings_base['elements'][0]['module_copy_id'];
        } elseif(!empty($modules_param)){
            $module_copy_id = $modules_param[0]['module_copy_id'];
        }

        if(!empty($this->_data_analisis_param)){
            $data_analisis_param = $this->_data_analisis_param;
        } else{
            $data_analisis_param = $this->getDataAnalysisElement($this->_schema, 'data_analysis_param');
        }


        if($module_copy_id){
            $modules_indicator = \Reports\models\ConstructorModel::getInstance()->getModulesIndicator($module_copy_id, $data_analisis_param);
        }

        return array(
            'param' => $modules_param,
            'indicator' => $modules_indicator,
        );
    }


    /**
     * DataAnalysis
     */
    private function getElementDataAnalysis($settings = array(), $settings_base = array())
    {
        $modules = $this->getDataAnalysisModules($settings_base, false);
        $schema = array(
            'type' => 'data_analysis',
            'title' => \Yii::t('ReportsModule.base', 'Data analysis'),
            'remove' => true,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '124',
            'data' => array(
                'param' => array(
                    'modules' => $modules['param'],
                ),
                'indicator' => array(
                    'modules' => $modules['indicator'],
                ),
            ),
            'elements' => array(),
        );
        return \Helper::arrayMerge($schema, $settings);
    }


    /**
     * dataAnalisysCheckResponsible
     * @param $settings
     */
    private function dataAnalisysCheckResponsible(&$settings)
    {
        if(!isset($settings['field_name'])) return;
        if($settings['module_copy_id'] == null || $settings['field_name'] === null) return;
        if(!in_array($settings['field_name'], array('b_responsible', 'bl_participant'))) return;

        $modules = $this->getDataAnalysisModules(null, true);
        if(empty($modules)) return;
        foreach($modules as $module){
            if($module['module_copy_id'] == $settings['module_copy_id']){
                $fields = $module['fields'];
                if(empty($fields)) return;

                foreach($fields as $field){
                    if($field['field_name'] == $settings['field_name']) return;
                    if(in_array($field['field_name'], array('b_responsible', 'bl_participant'))){
                        switch($settings['field_name']){
                            case 'b_responsible' :
                                if($field['field_name'] == 'bl_participant'){
                                    $settings['field_name'] = 'bl_participant';
                                }
                                break;
                            case 'bl_participant' :
                                if($field['field_name'] == 'b_responsible'){
                                    $settings['field_name'] = 'b_responsible';
                                }
                                break;
                        }
                        return;
                    }
                }

            }
        }
    }


    /**
     * checkFieldName - проверка поля
     * @param array $settings
     */
    private function checkFieldName(&$settings = array())
    {
        if($settings == false) return;

        if(!empty($settings['module_copy_id']) && !empty($settings['field_name'])){
            $extension_copy = \ExtensionCopyModel::model()->findByPk($settings['module_copy_id']);
            $i_params = $extension_copy->getFieldSchemaParams($settings['field_name']);
            if(!$i_params){
                $settings['field_type'] = null;
                return;
            }
            switch($i_params['params']['type']){
                case 'display_none' :
                    $settings['field_name'] = $extension_copy->getPrimaryViewFieldName();
                    break;
            }
            $settings['field_type'] = $i_params['params']['type'];
        }

        $this->setThereIsParamIndicator($settings);
    }


    /**
     * DataAnalysisParam
     */
    private function getElementDataAnalysisParam($settings = array())
    {
        $this->checkFieldName($settings);

        $schema = array(
            'type' => 'data_analysis_param',
            'title' => null,
            'module_copy_id' => null,
            'field_name' => null,
            'drag_marker' => false,
            'type_date' => null,
            'remove' => false,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '1',
        );

        $schema = \Helper::arrayMerge($schema, $settings);

        $this->_schema_data_analisis_param = $schema;
        $this->setSchemaDataAnalisisParam($schema);

        if(isset($settings['add_to_schema']) && $settings['add_to_schema'] == false){
            return false;
        }

        return $schema;
    }


    /**
     * DataAnalysisIndicator
     */
    private function getElementDataAnalysisIndicator($settings = array())
    {
        $this->checkFieldName($settings);

        $schema = array(
            'type' => 'data_analysis_indicator',
            'title' => null,
            'module_copy_id' => null,
            'field_name' => null,
            'drag_marker' => true,
            'remove' => true,
            'type_indicator' => null,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '1',
            'filters' => null,
        );

        $schema = \Helper::arrayMerge($schema, $settings);

        $this->setSchemaDataAnalisisIndicators($schema);

        return $schema;
    }












    /*****************************************
     *
     *    FILTERS
     *
     *****************************************/

    /**
     * getModulesFilter
     * @param $schema
     * @return array
     */
    public function getModulesFilter($schema)
    {
        $result = array();

        foreach($schema as $schema_value){
            if($schema_value['type'] == ConstructorBuilder::ELEMENT_FILTER && isset($schema_value['data']['modules'])){
                $result = $schema_value['data']['modules'];
            }
        }

        return $result;
    }


    /**
     * getElementFilterData
     */
    private function getElementFilterData()
    {
        $result = array(
            'modules' => array(),
        );

        if($this->_filter_module_copy_id_list !== null && !empty($this->_filter_module_copy_id_list)){
            $this->_filter_module_copy_id_list = array_unique($this->_filter_module_copy_id_list);
            $result['modules'] = \Reports\models\ConstructorModel::getInstance()->getModulesFilter($this->_filter_module_copy_id_list);
        } else{
            if(!empty($this->_schema)){
                foreach($this->_schema as $schema_value){
                    if($schema_value['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        $module_copy_id_list = array();
                        foreach($schema_value['elements'] as $element){
                            if(empty($element['module_copy_id'])) continue;
                            $module_copy_id_list[] = $element['module_copy_id'];
                        }
                        if(!empty($module_copy_id_list)) $result['modules'] = \Reports\models\ConstructorModel::getInstance()->getModulesFilter($module_copy_id_list);
                    }
                }
            }
        }

        return $result;
    }


    public function getModulesForFilterPanel($element)
    {
        if(empty($element['module_copy_id'])) return;

        return array(
            'modules' => \Reports\models\ConstructorModel::getInstance()->getModulesFilter(array($element['module_copy_id']))
        );
    }


    /**
     * Filter
     */
    private function getElementFilter($settings = array())
    {
        $schema = array(
            'type' => 'filter',
            'title' => \Yii::t('ReportsModule.base', 'Filters'),
            'remove' => true,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '125',
            'data' => $this->getElementFilterData(),
            'elements' => array(),
        );
        return \Helper::arrayMerge($schema, $settings);
    }


    /**
     * FilterPanel
     */
    private function getElementFilterPanel($settings = array())
    {
        $schema = array(
            'type' => 'filter_panel',
            'module_copy_id' => null,
            'field_name' => null,
            'remove' => true,
            'drag_marker' => true,
            'condition' => null,
            'condition_value' => null,
            'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '126',
            'show_module_copy_id' => true,
        );
        return \Helper::arrayMerge($schema, $settings);
    }


    /**
     * Возвращает дефолтную схему конструктора
     */
    public function getDefaultSchema()
    {
        $schema = array(
            //data_analysis
            array(
                'type' => 'data_analysis',
                'remove' => false,
                'elements' => array(
                    array(
                        'type' => 'data_analysis_param',
                        'remove' => false,
                        'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '1',
                    ),
                    array(
                        'type' => 'data_analysis_indicator',
                        'drag_marker' => false,
                        'remove' => false,
                        'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '2',
                    ),
                ),
            ),


            //filter
            array(
                'type' => 'filter',
                'remove' => false,
                'elements' => array(
                    array(
                        'type' => 'filter_panel',
                        'remove' => false,
                        'drag_marker' => false,
                    ),
                ),
            ),
        );

        return $this->setSchema($schema)->buildSchema(true);
    }












    /*****************************************
     * OTHER...
     *****************************************/


    /**
     * getElementSchemaIndicatorsBlock - возвращает схему для блока индикаторов
     */
    private function getElementSchemaIndicatorsPanel($post_params)
    {
        $schema_elements = array(
            'type' => 'indicator_panel',
            'unique_index' => null,
            'module_copy_id' => null,
            'field_name' => null,
            'color' => null,
        );


        $indicator_ui = array();
        if(!empty($post_params['view_elements']['indicator'])){
            $indicator_ui = array_unique($post_params['view_elements']['indicator']);
        }


        if(!empty($post_params['unique_index']) && in_array($post_params['unique_index'], $indicator_ui)){
            $schema_elements = array(
                'type' => 'indicator_panel',
                'unique_index' => (isset($post_params['unique_index']) ? $post_params['unique_index'] : null),
                'module_copy_id' => (isset($post_params['module_copy_id']) ? $post_params['module_copy_id'] : null),
                'field_name' => (isset($post_params['field_name']) ? $post_params['field_name'] : null),
                'color' => (isset($post_params['color']) ? $post_params['color'] : null),
            );
        }

        $schema = array(
            array(
                'type' => 'indicator',
                'elements' => array($schema_elements),
            )
        );

        return $schema;
    }


    /**
     * Генерация схемы элементов конструктора
     */
    public function generateConstructorSchema($element, $params = null, $return_html = false)
    {
        if(empty($element)) return;
        $result = array();

        //indicator_block
        switch($element){
            case 'indicator_block' :
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'indicator',
                        'elements' => array(
                            array(
                                'type' => 'indicator_panel',
                                'color' => 'gray',
                                'remove' => false,
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()->buildConstructorPage($result);
                break;

            //indicator_setting_indicator
            case 'indicator_setting_indicator' :
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'indicator',
                        'elements' => array(
                            array(
                                'type' => 'indicator_panel',
                                'remove' => true,
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getIndicator(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('block-block-setting-indicator'),
                    ));

                break;

            //indicator_panel
            case 'indicator_panel' :
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = $this->getElementSchemaIndicatorsPanel($params);

                $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getIndicator(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('panel'),
                    ));

                break;


            //indicator_panels
            case 'indicator_panels' :
                $result_list = array();
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $indicator_indicators = $params['indicator_indicators'];
                if(!empty($indicator_indicators)){
                    foreach($indicator_indicators as $params_item){
                        if(!empty($params['view_elements'])){
                            $params_item['view_elements'] = $params['view_elements'];
                        }
                        $schema = $this->getElementSchemaIndicatorsPanel($params_item);

                        $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                        $result_list[$params_item['unique_index']] = Extensions\ConstructorBuilder::getInstance()
                            ->getIndicator(array(
                                'schema' => $result[0],
                                'element' => $result[0]['elements'][0],
                                'views' => array('panel'),
                            ));
                    }
                }
                $result = $result_list;

                break;

            //graph_block
            case 'graph_block' :
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;


                $graph_type = $params['graph_type'];
                if(!empty($graph_type))
                    $this->_graph_type = $graph_type;

                $schema = array(
                    array(
                        'type' => 'graph',
                        'elements' => array(
                            array(
                                'type' => 'graph_element',
                                'graph_type' => $params['graph_type'],
                                'indicator' => (!empty($indicators[0]['unique_index']) ? $indicators[0]['unique_index'] : null),
                                'position' => $params['position'],
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()->buildConstructorPage($result);
                break;

            //graph_setting_indicator
            case 'graph_setting_indicator' :
                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'graph',
                        'elements' => array(
                            array(
                                'type' => 'graph_element',
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getGraph(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('block_block_setting_indicator'),
                    ));
                break;


            //graph_element
            case 'graph_element' :
                $graph_list = $params['graph_list'];
                if(empty($graph_list)) break;

                $indicators = $params['indicators'];
                if(!empty($indicators))
                    $this->addDataAnalisysIndicatorItemList($indicators);

                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $result_list = array();

                foreach($graph_list as $graph_element){
                    $schema = array(
                        array(
                            'type' => 'graph',
                            'elements' => array(
                                array(
                                    'type' => 'graph_element',
                                    'graph_type' => $graph_element['graph_type'],
                                    'period' => (!empty($graph_element['period']) ? $graph_element['period'] : null),
                                    'display_option' => (!empty($graph_element['display_option']) ? $graph_element['display_option'] : null),
                                    'indicator' => (!empty($graph_element['indicator']) ? $graph_element['indicator'] : null),
                                    'unique_index' => $graph_element['unique_index'],
                                ),
                            ),
                        ),
                    );


                    $result = $this->setSchema($schema)->prepareForConstructor()->buildSchema(true);

                    $result_list['id_' . $graph_element['unique_index']] = Extensions\ConstructorBuilder::getInstance()
                        ->getGraph(array(
                            'schema' => $result[0],
                            'element' => $result[0]['elements'][0],
                            'views' => array('graph_element'),
                        ));
                }
                $result = $result_list;
                break;

            //data_analysis_indicator
            case 'data_analysis_indicator' :
                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'data_analysis',
                        'elements' => array(
                            array(
                                'type' => 'data_analysis_param',
                                'module_copy_id' => $params['module_copy_id'],
                                'add_to_schema' => false, // не добавляет элемент к схеме
                            ),
                            array(
                                'type' => 'data_analysis_indicator',
                                'drag_marker' => true,
                                'remove' => true,
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getDataAnalysis(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('panel'),
                    ));
                break;

            //data_analysis_param_settings
            case 'data_analysis_param_settings' :
                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'data_analysis',
                        'elements' => array(
                            array(
                                'type' => 'data_analysis_param',
                                'module_copy_id' => $params['module_copy_id'],
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getDataAnalysis(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('settings'),
                    ));
                break;


            //data_analysis_indicator_settings
            case 'data_analysis_indicator_settings' :
                //$param = $params['param'];
                if(!empty($param['param'])){
                    $this->_data_analisis_param = $param['param'];
                }

                $schema = array(
                    array(
                        'type' => 'data_analysis',
                        'elements' => array(
                            array(
                                'type' => 'data_analysis_param',
                                'module_copy_id' => $params['parent_module_copy_id'],
                                'field_name' => $params['parent_field_name'],
                                'add_to_schema' => false, // не добавляет элемент к схеме
                            ),
                            array(
                                'type' => 'data_analysis_indicator',
                                'module_copy_id' => $params['module_copy_id'],
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getDataAnalysis(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('settings'),
                    ));
                break;


            //data_analysis_indicator_module_params
            case 'data_analysis_indicator_module_params' :
                $param = $params['param'];
                if(!empty($param))
                    $this->_data_analisis_param = $param;

                $schema = array(
                    array(
                        'type' => 'data_analysis',
                        'elements' => array(
                            array(
                                'type' => 'data_analysis_param',
                                'module_copy_id' => $params['module_copy_id'],
                                'add_to_schema' => false, // не добавляет элемент к схеме
                            ),
                            array(
                                'type' => 'data_analysis_indicator',
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getDataAnalysis(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('module-params'),
                    ));
                break;


            //filter
            case 'filter' :
                $module_copy_id_list = $params['module_copy_id_list'];
                if(!empty($module_copy_id_list))
                    $this->_filter_module_copy_id_list = $module_copy_id_list;

                $schema = array(
                    array(
                        'type' => 'filter',
                        'elements' => array(
                            array(
                                'type' => 'filter_panel',
                                'remove' => (isset($params['remove']) ? $params['remove'] : true),
                                'drag_marker' => (isset($params['drag_marker']) ? $params['drag_marker'] : true),
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getFilter(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('panel'),
                    ));
                break;


            //filter_indicator
            case 'filter_indicator' :
                $module_copy_id = $params['module_copy_id'];
                if(empty($module_copy_id)) break;

                $this->_filter_module_copy_id_list = array($module_copy_id);

                $schema = array(
                    array(
                        'type' => 'filter',
                        'elements' => array(
                            array(
                                'type' => 'filter_panel',
                                'remove' => true,
                                'drag_marker' => false,
                                'module_copy_id' => $module_copy_id,
                                'show_module_copy_id' => false,
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getFilter(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('panel'),
                    ));
                break;


            case 'filter_module' :
                $module_copy_id_list = $params['module_copy_id_list'];
                if(!empty($module_copy_id_list))
                    $this->_filter_module_copy_id_list = array_unique($module_copy_id_list);

                $schema = array(
                    array(
                        'type' => 'filter',
                        'elements' => array(
                            array(
                                'type' => 'filter_panel',
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getFilter(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('module'),
                    ));
                break;

            case 'filter_field_params' :
                $module_copy_id = $params['module_copy_id'];
                if(!empty($module_copy_id)) $this->_filter_module_copy_id_list = array($module_copy_id);

                $schema = array(
                    array(
                        'type' => 'filter',
                        'elements' => array(
                            array(
                                'type' => 'filter_panel',
                                'module_copy_id' => $module_copy_id,
                            ),
                        ),
                    ),
                );

                $result = $this->setSchema($schema)->buildSchema(true);

                if($return_html) $result = Extensions\ConstructorBuilder::getInstance()
                    ->getFilter(array(
                        'schema' => $result[0],
                        'element' => $result[0]['elements'][0],
                        'views' => array('field-params'),
                    ));
                break;


        }

        return $result;
    }


    /**
     * Генерация схемы элементов отчета
     */
    public function generateReportSchema($element, $params = null)
    {
        if(empty($element)) return;
        $result = array();

        $schema_saved = \Reports\models\ReportsModel::getSavedSchema($params['reports_id']);

        $this
            ->setSchema($schema_saved)
            ->setFromUsersStorage($params['reports_id'])
            ->setupGlobalDataAnalysisParams();


        switch($element){

            //block_setting_indicator
            case 'block_setting_indicator' :
                $schema = $this->prepareForConstructor()->buildSchema(true);
                $result = Extensions\ReportBuilder::getInstance()
                    ->getGraph(array(
                        'schema' => $schema[0],
                        'element' => $schema[0]['elements'][0],
                        'views' => array('block_setting_indicator'),
                    ));
                break;

            //graph_element
            case 'graph_element' :
                $schema = array();
                foreach($this->_schema as &$element){
                    if($element['type'] == 'graph'){
                        if($element['elements'][0]['unique_index'] == $params['graph_unique_index']){
                            $schema[] = $element;
                        }
                    } elseif($element['type'] == 'filter'){
                        $schema[] = $element;
                    }
                }
                if(empty($schema)) continue;

                $schema = $this->setSchema($schema)->buildSchema(true);

                $result = Extensions\ConstructorBuilder::getInstance()
                    ->getGraph(array(
                        'schema' => $schema[0],
                        'element' => $schema[0]['elements'][0],
                        'views' => array('graph_element'),
                    ));
                break;


        }

        return $result;
    }


    /**
     * getDataAnalysisEntityesBySchema
     */
    public function getDataAnalysisEntityesBySchema($schema)
    {
        $result = array();
        if(empty($schema)) return $result;

        foreach($schema as $element){
            if($element['type'] != 'data_analysis') continue;
            foreach($element['elements'] as $indicator){
                $result[] = $indicator;
            }
        }

        return $result;
    }


    /**
     * getFilters
     */
    private function setFilters($params)
    {
        $this->_filters = \Helper::arrayMerge($this->_filters, $params);
        return $this;
    }


    /**
     * getFilters
     */
    public function getFilters()
    {
        return $this->_filters;
    }


    /**
     * getDataSettings
     */
    public function getDataSettings()
    {
        $data_setting = array(
            'param' => $this->_data_analisis_param,
            'indicators' => $this->_data_analisis_indicators,
            'filters' => $this->getFilters(),
        );

        return $data_setting;
    }


    /**
     * prepareForConstructor
     */
    public function prepareForConstructor()
    {
        $this->prepareGraphsForConstructor();

        return $this;
    }


    /**
     * setFromUsersStorage
     */
    public function setFromUsersStorage($reports_id)
    {
        $this->_set_from_users_storage = true;

        $us_value = \Reports\models\ReportsUsersStorageModel::getStorage($reports_id);

        $this->userStorageSetDateInterval($us_value);
        $this->userStorageSetGraphIndicators($us_value);
        $this->userStorageSetPeriod($us_value);
        $this->userStorageSetDisplayOption($us_value);


        return $this;
    }


    /**
     * userStorageSetDateInterval
     */
    private function userStorageSetDateInterval($us_value)
    {
        if(!empty($us_value['date_interval'])){
            $this->setFilters($us_value['date_interval']);
        }
    }


    private function getDataAnalysisIndicators($schema = null)
    {
        $indicators = array();

        if($schema === null) $schema = $this->_schema;

        //indicators
        if($this->_data_analisis_indicators !== null && !empty($this->_data_analisis_indicators)){
            $indicators = $this->_data_analisis_indicators;
        } else{
            if(!empty($schema))
                foreach($schema as $schema_value){
                    if($schema_value['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                        foreach($schema_value['elements'] as $element){
                            if($element['type'] == 'data_analysis_param') continue;
                            if($this->fieldIsNumeric($element['module_copy_id'], $element['field_name']) == false) continue;
                            $indicators[] = array(
                                'title' => $element['title'],
                                'module_copy_id' => $element['module_copy_id'],
                                'field_name' => $element['field_name'],
                                'type_indicator' => $element['type_indicator'],
                                'unique_index' => $element['unique_index'],
                            );
                        }
                    }
                }
        }

        return $indicators;
    }


    /**
     * userStorageSetIndicators
     */
    private function userStorageSetGraphIndicators($us_value)
    {
        foreach($this->_schema as &$schema){
            if($schema['type'] != 'graph') continue;

            if(!empty($us_value['graph_indicators'][$schema['elements'][0]['unique_index']])){
                $schema['elements'][0]['data_indicators'] = $us_value['graph_indicators'][$schema['elements'][0]['unique_index']];
            } elseif(!empty($schema['elements'][0]['indicator'])){
                $schema['elements'][0]['data_indicators'] = array($schema['elements'][0]['indicator']);
            } else{
                $indicators = $this->getDataAnalysisIndicators();
                if(!empty($indicators)){
                    $schema['elements'][0]['data_indicators'] = array($indicators[0]['unique_index']);
                }
            }
        }
        unset($schema);

        return $this;
    }


    /**
     * userStorageSetPeriod
     */
    private function userStorageSetPeriod($us_value)
    {
        foreach($this->_schema as &$schema){
            if($schema['type'] != 'graph') continue;

            if(!empty($us_value['graph_period'][$schema['elements'][0]['unique_index']])){
                $schema['elements'][0]['period'] = $us_value['graph_period'][$schema['elements'][0]['unique_index']];
            }
        }
        unset($schema);

        return $this;
    }


    /**
     * userStorageSetDisplayOption
     */
    private function userStorageSetDisplayOption($us_value)
    {
        foreach($this->_schema as &$schema){
            if($schema['type'] != 'graph') continue;

            if(!empty($us_value['graph_display_option'][$schema['elements'][0]['unique_index']])){
                $schema['elements'][0]['display_option'] = $us_value['graph_display_option'][$schema['elements'][0]['unique_index']];
            } else{
                switch($schema['elements'][0]['graph_type']){
                    case \Reports\models\ConstructorModel::GRAPH_LINE :
                        $schema['elements'][0]['display_option'] = ($schema['elements'][0]['display_option'] ? $schema['elements'][0]['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM :
                        $schema['elements'][0]['display_option'] = ($schema['elements'][0]['display_option'] ? $schema['elements'][0]['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_CIRCULAR :
                        $schema['elements'][0]['display_option'] = ($schema['elements'][0]['display_option'] ? $schema['elements'][0]['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                    case \Reports\models\ConstructorModel::GRAPH_CRATER :
                        $schema['elements'][0]['display_option'] = ($schema['elements'][0]['display_option'] ? $schema['elements'][0]['display_option'] : \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY);
                        break;
                }
            }
        }
        unset($schema);

        return $this;
    }


    private function prepareGraphsForConstructor()
    {
        foreach($this->_schema as &$schema){
            if($schema['type'] == 'graph'){
                if(!empty($schema['elements'][0]['indicator'])){
                    $schema['elements'][0]['data_indicators'] = array($schema['elements'][0]['indicator']);
                }
            }

            if($schema['type'] == 'data_analysis'){
                $schema['title'] = \Yii::t('ReportsModule.base', 'Data analysis');
            }
            if($schema['type'] == 'filter'){
                $schema['title'] = \Yii::t('ReportsModule.base', 'Filters');
            }
        }
        unset($schema);

        return $this;
    }


    /**
     * getDataAnalysisParamPeriod
     */
    public static function getDataAnalysisParamPeriod($schema)
    {
        $period = null;
        if(empty($schema)) return $period;

        foreach($schema as $element_block){
            if($element_block['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                foreach($element_block['elements'] as $element){
                    if($element['type'] == 'data_analysis_param')
                        return \Reports\models\DataReportModel::getInstance()->getPeriodName($element['field_name']);
                }
            }
        }
    }


    /**
     * getDataAnalysisElement - Поиск элемента на его типу (data_analysis_param | data_analysis_indicator)
     * @param $schema
     * @param $element_type -  data_analysis_param | data_analysis_indicator
     * @return array
     */
    public static function getDataAnalysisElement($schema, $element_type = null)
    {
        $result = array();

        if(empty($schema)) return $result;

        foreach($schema as $schema_value){
            if($schema_value['type'] == ConstructorBuilder::ELEMENT_DATA_ANALYSIS){
                foreach($schema_value['elements'] as $element){
                    if($element_type === null){
                        $result[] = $element;
                        continue;
                    } elseif($element['type'] != $element_type){
                        continue;
                    } elseif($element_type == 'data_analysis_param'){
                        $result = $element;
                        break;
                    }
                    if($element_type == 'data_analysis_indicator') $result[] = $element;
                }
            }
        }

        return $result;
    }


    /**
     * getFilterElements - Поиск элементов фильтра
     * @param $schema
     * @return array
     */
    public static function getFilterElements($schema)
    {
        $result = array();

        if(empty($schema)) return $result;

        foreach($schema as $schema_value){
            if($schema_value['type'] == ConstructorBuilder::ELEMENT_FILTER){
                $result = $schema_value['elements'];
            }
        }

        return $result;
    }


}
