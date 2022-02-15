<?php
/**
* ConstructorModel
* @author Alex R.
* @version 1.0
*/
namespace Reports\models;

class ConstructorModel extends \ConstructorModel{
    
    const GRAPH_LINE        = 'graph-line';
    const GRAPH_HISTOGRAM   = 'graph-histogram';
    const GRAPH_CIRCULAR    = 'graph-circular';
    const GRAPH_CRATER      = 'graph-crater';
    const TABLE             = 'table';
    
    const GRAPH_POSITION_BOTTON = 'botton';
    const GRAPH_POSITION_LEFT = 'left';
    const GRAPH_POSITION_RIGHT = 'right';
  
    const GRAPH_DISPLAY_OPTION_DISPLAY = 'display';
    const GRAPH_DISPLAY_OPTION_NOT_DISPLAY = 'not_display';
    
    const SETTING_INDICATOR_MAX_COUNT = 4;
    
    const PARAM_FIELD_NAME_DAY = '__pfn_day__';
    const PARAM_FIELD_NAME_WEEK = '__pfn_week__';
    const PARAM_FIELD_NAME_MONTH = '__pfn_month__';
    const PARAM_FIELD_NAME_QUARTER = '__pfn_quarter__';
    const PARAM_FIELD_NAME_YEAR = '__pfn_year__';
    const PARAM_FIELD_NAME_ID = '__id__';
    const PARAM_FIELD_NAME_AMOUNT = '__amount__';

    const TI_AMOUNT     = 'amount';
    const TI_SUM        = 'sum';
    const TI_PERCENT    = 'percent';
    const TI_MIN        = 'minimum';
    const TI_MAX        = 'maximum';
    const TI_AVERAGE    = 'average';


    
    private $_graph_periods = array(
                'day' => 'Day',
                'week' => 'Week',
                'month' => 'Month',
                'quarter' => 'Quarter',
                'year' => 'Year',
                'all_period' => 'All period',
            );
    
    private $_type_indicator = array(
                //'amount' => 'Amount',
                self::TI_SUM        => 'Sum',
                self::TI_PERCENT    => 'Percent',
                self::TI_MIN        => 'Minimum',
                self::TI_MAX        => 'Maximum',
                self::TI_AVERAGE    => 'Average',
            );
    

    private $_type_date = array(
                'date_create' => 'Date created',
                'date_edit' => 'Date edit',
            );
    
    
    
    
    
    public static function getInstance(){
        return new  self();
    }
    
    
    
    
    public function getGraphPeriods($add_list = array()){
        $result = array();
        
        foreach($add_list as $key){
            $result[$key] = \Yii::t('ReportsModule.base', $this->_graph_periods[$key]);    
        }
        
        return $result;
    }


    public function getTypeIndicator($element_schema){
        $result = array();

        $list_only = null;
        $schema_model = \Reports\extensions\ElementMaster\Schema::getInstance();
        $p = $schema_model->getSchemaDataAnalisisParam();
        if($p && $schema_model && $p['module_copy_id'] == $element_schema['module_copy_id']){
            $list_only = array(self::TI_SUM,  self::TI_PERCENT);
        }

        foreach($this->_type_indicator as $key => $value){
            if($list_only !== null && !in_array($key, $list_only)) continue;
            $result[$key] = \Yii::t('ReportsModule.base', $value);
        }
        
        return $result;
    }




    /**
     * getTypeDateList - возвращает список полей типа Дата по всем модулям: модуль-параметр + связанные модули
     */
    public function getTypeDateList($copy_id){
        $result = array();

        $extensions = $this->getExtensionCopyList(\ExtensionCopyModel::model()->findByPk($copy_id)) ;
        if($extensions === false) return $result;

        $add_title = (count($extensions) > 1 ? true : false);

        foreach($extensions as $extension_copy){
            $tmp = $this->getTypeDate($extension_copy->copy_id, $add_title);
            if(empty($tmp)) continue;
            $result += $tmp;
        }

        return $result;
    }




    /**
     * getTypeDate - возвращает список полей типа Дата опеределенного модуля
     */
    private function getTypeDate($copy_id, $add_title = false){
        $result = array();

        if(empty($copy_id)) return $result;
        
        $fields = $this->getModuleFields(\ExtensionCopyModel::model()->findByPk($copy_id), array('datetime'));

        if($add_title){
            $extension_copy = \ExtensionCopyModel::model()->findByPK($copy_id);
            $module_title = $extension_copy->getModule()->getModuleTitle()  . ': ';
        } else {
            $module_title = '';
        }

        foreach($this->_type_date as $key => $value){
            $result[$copy_id . ':' . $key] = $module_title .  \Yii::t('ReportsModule.base', $value);
        }

        if(!empty($fields)){
            foreach($fields as $field){
                $result[$copy_id . ':' . $field['field_name']] = $module_title . $field['title'];
            }
        }
        
        return $result;
    }


    /**
     * getModulesParam
     */
    public function getModulesParam(){
        $result = array(); 
        $extensions = \ExtensionCopyModel::model()
                    ->modulesActive()
                    ->findAll(array(
                            'condition'=>'(`schema` != "" OR `schema` is not NULL) AND constructor = "1" AND copy_id != ' . \ExtensionCopyModel::MODULE_REPORTS,
                            'order'=> 'sort',
                            )
                        );
        if(empty($extensions)) return $result;
        foreach($extensions as $extension_copy){
            $module_array = $this->getPreparedModuleData($extension_copy, array('string', 'logical', 'display', 'display_block', 'relate', 'relate_string', 'numeric', 'datetime', 'select', 'relate_participant'), true);
            if(empty($module_array)) continue;
            $result[] = $module_array;             
            
        }

        if(!empty($result))
            $result = \Helper::arraySort($result, 'title');

        return $result;    
    }



    /**
     * getModulesIndicator - возвращает список модулей для индикатора
     */
    public function getModulesIndicator($copy_id, $data_analysis_param){
        $result = array();

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        
        $extensions = $this->getExtensionCopyList($extension_copy);

        if($extensions === false) return $result;

        $all_fields = false;
        if(!empty($data_analysis_param['field_name']) && $data_analysis_param['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
            $all_fields = true;
        }

        if(empty($extensions)) return $result;
        foreach($extensions as $extension_copy){
            if($all_fields == true && $copy_id == $extension_copy->copy_id){
                $vf_amount = false;
                $fields_type = null;
            } else {
                $vf_amount = true;
                $fields_type = array('numeric');
            }

            $module_array = $this->getPreparedModuleData($extension_copy, $fields_type, false, $vf_amount);
            if(empty($module_array)) continue;
            $result[] = $module_array;             
            
        }

        if(!empty($result))
            $result = \Helper::arraySort($result, 'title');

        return $result;
    }


    /**
     * getExtensionCopyList - возвращает список: модуль-параметр и все связанные подули
     */
    private function getExtensionCopyList($extension_copy_param){
        if(empty($extension_copy_param)) return false;

        $extensions = array($extension_copy_param);

        // SDM
        $relates = $extension_copy_param->getFieldSchemaParamsByType(array('relate', 'relate_string'),  null, false);
        if(!empty($relates)){
            foreach($relates as $relate){
                $extensions[] = \ExtensionCopyModel::model()->findByPk($relate['params']['relate_module_copy_id']);
            }
        }

        // SM
        $modules = \SchemaOperation::getSubModules($extension_copy_param->getSchemaParse());
        if(!empty($modules)){
            foreach($modules as $module){
                $extensions[] = \ExtensionCopyModel::model()->findByPk($module['sub_module']['params']['relate_module_copy_id']);
            }
        }

        return $extensions;
    }




    public function getModulesFilter($module_copy_id){
        $result = array(); 
        if(empty($module_copy_id)) return array();
        
        $extensions = \ExtensionCopyModel::model()
                    ->modulesActive()
                    ->findAll(array(
                            'condition'=>'(`schema` != "" OR `schema` is not NULL) AND constructor = "1" AND copy_id in(' . implode(',', $module_copy_id)  . ')',
                            'order'=> 'sort',
                            )
                        );
        if(empty($extensions)) return $result;
        foreach($extensions as $extension_copy){
            $module_array = $this->getPreparedModuleData($extension_copy, array('string', 'numeric', 'datetime', 'select'));
            if(empty($module_array)) continue;
            $result[] = $module_array;             
            
        }
        
        return $result;    
    }
    


    public static function getVirtualFieldsPeriods(){
        return array(
            array('title' => '---' . \Yii::t('ReportsModule.base', 'day') . '---', 'field_name' => self::PARAM_FIELD_NAME_DAY),
            array('title' => '---' . \Yii::t('ReportsModule.base', 'week') . '---', 'field_name' => self::PARAM_FIELD_NAME_WEEK),
            array('title' => '---' . \Yii::t('ReportsModule.base', 'month') . '---', 'field_name' => self::PARAM_FIELD_NAME_MONTH),
            array('title' => '---' . \Yii::t('ReportsModule.base', 'quarter') . '---', 'field_name' => self::PARAM_FIELD_NAME_QUARTER),
            array('title' => '---' . \Yii::t('ReportsModule.base', 'year') . '---', 'field_name' => self::PARAM_FIELD_NAME_YEAR)
        );
    }

    
    public function getModuleFields($extension_copy, $fields_type = null, $add_virtual_fields = false, $add_virtual_field_amount = false){
        $sub_module_schema_parse = $extension_copy->getSchemaParse();
        $fields_array = array();
        
        if($add_virtual_fields){
            $fields_array = self::getVirtualFieldsPeriods();
            $fields_array[] =  array('title' => 'ID', 'field_name' => self::PARAM_FIELD_NAME_ID);
        }

        $params = \SchemaConcatFields::getInstance()
            ->setSchema($sub_module_schema_parse['elements'])
            ->setWithoutFieldsForReports()
            ->parsing()
            ->primaryOnFirstPlace()
            ->prepareWithoutCompositeFields()
            ->getResult();

        if(!empty($params['header'])) {
            foreach ($params['header'] as &$fields) {
                foreach(explode(',', $fields['name']) as $field_name){
                    if($fields_type !== null && !in_array($params['params'][$field_name]['type'], $fields_type)) continue;

                    if($field_name == 'bl_participant'){
                        $fields['title'] = \Yii::t('base', 'Responsible');
                    }

                    $fields_array[] = array(
                                    'title' => $fields['title'], 
                                    'field_name' => $field_name,
                    );
                }
            }
        }

        if($add_virtual_field_amount == true){
            $fields_array = array_merge(array(array('title' => '---' . \Yii::t('ReportsModule.base', 'amount') . '---', 'field_name' => self::PARAM_FIELD_NAME_AMOUNT)), $fields_array);
        }

        return $fields_array;
    } 
    
    
    
    
    private function  getPreparedModuleData($extension_copy, $fields_type = null, $add_virtual_fields = false, $add_virtual_field_amount = false){
        $fields_array = $this->getModuleFields($extension_copy, $fields_type, $add_virtual_fields, $add_virtual_field_amount);

        return array(
                    'title' => $extension_copy->title,
                    'module_copy_id' => $extension_copy->copy_id,
                    'fields' => $fields_array,
        );
    }
    





    public function calculateDataReportForIndicator($schema, &$schema_indicators, $data_setting, $indicator_settings){
        if(empty($schema_indicators)) return;

        $ui_list = array();
        if(!empty($indicator_settings['elements'])){
            foreach($indicator_settings['elements'] as $element){
                if($element['unique_index']) $ui_list[] = $element['unique_index'];
            }
        }

        $indicators = array();
        foreach($data_setting['indicators'] as $indicator){
            if($indicator['unique_index'] && in_array($indicator['unique_index'], $ui_list)){
                $indicators[] = $indicator;
            }
        }
        if($indicators){
            $data_setting['indicators'] = $indicators;


            $data_model = new \Reports\models\DataReportModel();
            $data_model = $data_model
                ->setSchema($schema)
                ->setDataSetting($data_setting)
                ->getIndicators();
        }

        foreach($schema_indicators as &$element){
            $element['indicator_number'] = 0;

            if(!empty($data_model)){
                foreach($data_model as $data){
                    if($element['unique_index'] == $data['unique_index'])
                        $element['indicator_number'] = $data['param_y'];
                }
            }
        }
    }



    public function getDataReportForGraph($schema, $schema_element, $data_setting){
        $data_model = new \Reports\models\DataReportModel();
        $data_model = $data_model
            ->setSchema($schema)
            ->setDataSetting($data_setting)
            ->getGraph($schema_element);
        
        return $data_model;
    }




    public function getQueryReportForTable($schema, $data_setting){
        $data_model = new \Reports\models\DataReportModel();
        $query = $data_model
                    ->setSchema($schema)
                    ->setDataSetting($data_setting)
                    ->getTableQuery();
        return $query;
    }




    
    public static function getGraphicsList(){
        return array(
            self::GRAPH_LINE => \Yii::t('ReportsModule.base', 'Line graph'),
            self::GRAPH_HISTOGRAM => \Yii::t('ReportsModule.base', 'Histogram'),
            self::GRAPH_CIRCULAR => \Yii::t('ReportsModule.base', 'Circular graph'),
            //self::GRAPH_CRATER => \Yii::t('ReportsModule.base', 'Sales funnel'),      
        );
    }
    





    private static function checkPosition($positions){
        if(empty($positions)) return false;
        
        $result = true;
        if($positions[count($positions)-2] != self::GRAPH_POSITION_BOTTON && $positions[count($positions)-2] != self::GRAPH_POSITION_RIGHT) $result = false;
        if($positions[count($positions)-1] != self::GRAPH_POSITION_BOTTON) $result = false;
        
        return $result;
    } 




    public static function getGraphPosition($graph_count, $positions){
        if((integer)$graph_count == 1 || ((integer)$graph_count >= 2 && self::checkPosition($positions))){
            return array(
                self::GRAPH_POSITION_BOTTON => \Yii::t('ReportsModule.base', 'Bottom'),
                self::GRAPH_POSITION_LEFT => \Yii::t('ReportsModule.base', 'Left'),
                self::GRAPH_POSITION_RIGHT => \Yii::t('ReportsModule.base', 'Right'),
            );
        }

        return array(
            self::GRAPH_POSITION_BOTTON => \Yii::t('ReportsModule.base', 'Bottom'),
        );

    }


    public static function getGraphDisplayOptions(){
        return array(
            self::GRAPH_DISPLAY_OPTION_DISPLAY => \Yii::t('ReportsModule.base', 'Display setting'),
            self::GRAPH_DISPLAY_OPTION_NOT_DISPLAY => \Yii::t('ReportsModule.base', 'Do not display'),
        );
    }
    
    
    
    
    /**
     *  форматируем число 
     */
    public static function formatNumber($type_indicator, $value, $dec_point = ',', $housands_sep = ' ', $add_suffix = array()){
        if($value === '' || $value === null) $value = 0;

        switch($type_indicator){
            case self::TI_AMOUNT  :
                $result = number_format($value, 0);
                break;
            case self::TI_SUM :
                $result = number_format($value, 2, $dec_point, $housands_sep);
                break;
            case self::TI_PERCENT :
                $result = number_format($value, 2);
                if(isset($add_suffix['percent_value'])) $result .= $add_suffix['percent_value'];
                break;
            case self::TI_MIN :
                $result = number_format($value, 2, $dec_point, $housands_sep);
                break;
            case self::TI_MAX :
                $result = number_format($value, 2, $dec_point, $housands_sep);
                break;
            case self::TI_AVERAGE :
                $result = number_format($value, 2, $dec_point, $housands_sep);
                break;
            default :
                $result = 0;
                break;
        }
        return $result;

    } 
         
          


    public static function formatIndicatorValue($schema, $element, $value, $add_number_suffix = array()){
        $result = ''; 

        if(!empty($schema['data']['indicators'])){
            foreach($schema['data']['indicators'] as $indicator){
                if(!empty($element['unique_index']) && !empty($element['unique_index']) && $indicator['unique_index'] == $element['unique_index']){
                    if($value == 'title'){
                        $result = $indicator[$value];
                    } elseif($value == 'indicator_number'){
                        $result = self::formatNumber($indicator['type_indicator'], $indicator[$value], ',', ' ', $add_number_suffix);
                    }

                    break;
                } else{
                    switch($value){
                        case 'title' :
                            $result = \Yii::t('ReportsModule.messages', 'No data');
                            break;
                        case 'indicator_number' :
                            $result = 0;
                            break;
                    }
                }
            }
        } else {
            switch($value){
                    case 'title' :
                    $result = \Yii::t('ReportsModule.messages', 'No data');
                    break;
                case 'indicator_number' :
                    $result = 0;
                    break;
            }
        }
        return $result;
    }






    /**
     * isPeriodConstant
     */
    public static function isPeriodConstant($field_name){
        $period = false;
        switch($field_name){
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_DAY :
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_WEEK :
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_MONTH :
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_QUARTER :
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_YEAR :
                $period = true; break;
        }

        return $period;
    }






    /**
     * isPeriodConstant
     */
    public static function isFieldConstant($field_name){
        $period = false;
        switch($field_name){
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID :
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT :
                $period = true; break;
        }

        return $period;
    }





} 




