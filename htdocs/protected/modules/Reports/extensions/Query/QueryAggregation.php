<?php
/**
* QueryAggregation
* 
* @author Alex R.
*/

namespace Reports\extensions\Query;


use Reports\models\ConstructorModel;

class QueryAggregation{
    
    
    private $_data_setting; 
    private $_query;
    private $_query_indicator;
    private $_aggregate = true; // вкл/выкл агрегирование
    private $_element_type;

    private $_query_entityes = array(
                                'select' => array(),
                                'from' => array(),
                                'group_by' => array(),
                                );


    public static function getInstance(){
        return new self();
    }




    public function setElementType($element_type){
        $this->_element_type = $element_type;
        return $this;
    }




    private function prepareVars(){
        $result = true;
        $setting = $this->_data_setting;

        //вкл/выкл агрегирование
        if(
            $this->_element_type == \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE &&
            $this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID &&
            $this->_data_setting['param']['module_copy_id'] == $this->_data_setting['indicator']['module_copy_id'] &&
            $this->_data_setting['indicator']['type_indicator'] != \Reports\models\ConstructorModel::TI_PERCENT
        ){
            $this->_aggregate = false;
        }


        if(empty($setting['indicator']['module_copy_id']) ||
           empty($setting['indicator']['field_name']) ||
           empty($setting['indicator']['type_indicator']))
        {
            $result = false;
        }
        
        return $result; 
    }



    public function setDataSetting($setting){
        $this->_data_setting = $setting;
        
        return $this;
    }
    
    
    
    
    public function build(){
        if($this->prepareVars() == false) return $this;

        $query_indicator = \Reports\extensions\Query\QueryIndicator::getInstance()
                                ->setDataSetting($this->_data_setting)
                                ->setElementType($this->_element_type)
                                ->build()
                                ->getQuery();
        
        if(!empty($query_indicator)){
            $this->_query_indicator = $query_indicator;
            $this->addSelect();
            $this->addFrom();
            $this->addGroup();
        }
            
        return $this;
    }
    
    
    

    private function getAggregateFunction($type_indicator){
        $result = '';
        switch($type_indicator){
            /*
            case 'amount' :
                $result = 'COUNT(indicator_value) AS indicator_value';
                break;
            */
            case 'sum' :
                $result = 'SUM(indicator_value) AS indicator_value';
                break;
            case 'percent' :
                $result = '(indicator_value / total_sum) * 100 AS indicator_value';
                break;
            case 'minimum' :
                $result = 'MIN(indicator_value) AS indicator_value';
                break;
            case 'maximum' :
                $result = 'MAX(indicator_value) AS indicator_value';
                break;
            case 'average' :
                $result = 'AVG(indicator_value) AS indicator_value';
                break;
        }

        $this->_element_type = $this->_element_type;

        if($this->_data_setting['indicator']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT){
            $result = 'SUM(indicator_value) AS indicator_value';
        }

        return $result;
    }


    
    private function addSelect(){
        $select = array();
        $setting = $this->_data_setting;

        if($this->_aggregate && $this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
            $select[] = 'MAX(param_x) AS param_x';
        } else {
            $select[] = 'param_x';
        }
        $select[] = 'param_s1';
        $select[] = 'param_s2';
        $select[] = 'param_s3';
        $select[] = 'unique_index';

        if($this->_aggregate){
            //buildAll..
            switch($this->_element_type){
                case \Reports\models\DataReportModel::ELEMENT_TYPE_INDICATOR :
                    $select[] = $this->getAggregateFunction($setting['indicator']['type_indicator']);
                    break;

                case \Reports\models\DataReportModel::ELEMENT_TYPE_GRAPH :
                    $select[] = $this->getAggregateFunction($setting['indicator']['type_indicator']);
                    break;

                case \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE :
                    $select[] = $this->getAggregateFunction($setting['indicator']['type_indicator']);
                    break;
            }
        } else {
            $select[] = 'indicator_value';
        }


        $this->_query_entityes['select'] = $select;
    }


        


    private function addFrom(){
        $this->_query_entityes['from'] = '('. $this->_query_indicator .') AS DATA_X';
    }
    
    
    

    private function addGroup(){
        $list = array();

        //buildAll..
        switch($this->_element_type){
            /*
            case \Reports\models\DataReportModel::ELEMENT_TYPE_INDICATOR :
                if($this->_aggregate == false) return;
                $list[] = 'unique_index';
                break;

            case \Reports\models\DataReportModel::ELEMENT_TYPE_GRAPH :
                if($this->_aggregate == false) return;
                $list[] = 'param_x';
                $list[] = 'unique_index';
                break;
            */
            case \Reports\models\DataReportModel::ELEMENT_TYPE_INDICATOR :
            case \Reports\models\DataReportModel::ELEMENT_TYPE_GRAPH :
            case \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE :
                if($this->_data_setting['param']['field_name'] != \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                    $list[] = 'param_s1';
                    $list[] = 'param_s2';
                    $list[] = 'param_s3';
                    $list[] = 'unique_index';
                } elseif($this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID && $this->_aggregate
                ){
                    if($this->_data_setting['param']['module_copy_id'] == $this->_data_setting['indicator']['module_copy_id']){
                        $list[] = 'param_x';
                    } else {
                        if(\Reports\extensions\ElementMaster\Schema::getInstance()->getThereIsParamIndicator()){
                            $list[] = 'param_x';
                        } else {
                            if($this->_element_type == \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE){
                                $params = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id'])->getFieldSchemaParams('module_title');
                                if($params['params']['type'] == 'display_none'){
                                    $list[] = 'param_x';
                                    break;
                                }
                            }

                            $list[] = 'param_s1';
                            $list[] = 'param_s2';
                            $list[] = 'param_s3';
                            $list[] = 'unique_index';
                        }
                    }
                }
                break;
        }

        if(!empty($list))
            $this->_query_entityes['group_by'][] = implode(', ', $list);
    }
    

 
    private function buildQuery(){
        foreach($this->_query_entityes as $key => $value){
            if(empty($value)) continue;
            
            switch($key){
                case 'select' :
                    $result = implode(',', $value);
                    $this->_query .= ' SELECT ' . $result;
                    break;
                case 'from' :
                    $this->_query .= ' FROM ' . $value;
                    break;
                case 'group_by' :
                    if(!empty($value)){
                        $result = implode(',', $value);
                        $this->_query .= ' GROUP BY ' . $result;
                    }  
                    break;

            }            
        }
      
    }
    
    

    
    
    public function getQuery(){
        $this->buildQuery();
        
        return $this->_query;
    }
    
    
    
    
    
    
}
