<?php
/**
* QueryBuilder
* 
* @author Alex R.
*/

namespace Reports\extensions\Query;


use Reports\extensions\ElementMaster\ConstructorBuilder;
use Reports\models\ConstructorModel;

class QueryBuilder{

    private $_data_setting;    // array('params' => array(), 'indiators' => array())
    
    private $_query;
    private $_query_aggregations = array();
    private $_query_aggregations_status = array();
    public  $_indicator_keys = array();



    private $_element_type;

    public static function getInstance(){
        return new self();
    }


    public function setDataSetting($setting){
        $this->_data_setting = $setting;
        
        return $this;
    }
    
    
    public function getQuery(){
        $params = \Yii::app()->params;
        if(!empty($params['reports']['logging_query'])){
            if(is_array($this->_query)){
                foreach($this->_query as $query){
                    \DataModel::getInstance()->setText('insert into {{reports_query}} (date_create, query, element) values (now(), "' . addslashes($query) . '", "' . $this->_element_type . '")')->execute();
                }
            } else{
                \DataModel::getInstance()->setText('insert into {{reports_query}} (date_create, query, element) values (now(), "' . addslashes($this->_query) . '", "' . $this->_element_type . '")')->execute();
            }
        }

        return $this->_query;
    }


    public function setElementType($element_type){
        $this->_element_type = $element_type;
        return $this;
    }



    public function getIndicatorKeys(){
        return $this->_indicator_keys;
    }



    public function build(){
        if(empty($this->_data_setting['indicators'])) return $this;

        $this->prepareAggregations();

        //buildAll..
        switch($this->_element_type){
            case \Reports\models\DataReportModel::ELEMENT_TYPE_INDICATOR :
                $this->buildAllSummQuery();
                break;
            case \Reports\models\DataReportModel::ELEMENT_TYPE_GRAPH :
            case \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE :
                $this->buildAllQuery();
                break;
        }

        return $this;
    }





    /**
     * prepareAggregations - подготавиливаем запросы по каждому Показателю
     */
    private function prepareAggregations(){
        $data_setting = $this->_data_setting;
        unset($data_setting['indicators']);


        foreach($this->_data_setting['indicators'] as $indicator){
            $aggregate_status = true;
            if($this->_element_type == \Reports\models\DataReportModel::ELEMENT_TYPE_TABLE && $data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                $aggregate_status = false;
            }

            $data_setting['indicator'] = $indicator;

            $query_aggregation = \Reports\extensions\Query\QueryAggregation::getInstance()
                                        ->setDataSetting($data_setting)
                                        ->setElementType($this->_element_type)
                                        ->build()
                                        ->getQuery();

            if(!empty($query_aggregation)){
                $this->_query_aggregations[] = $query_aggregation;
                $this->_query_aggregations_status[] = $aggregate_status;
            }
        }
    }
    
    
    
    private function concatQueryAggregations($query = null){
        if(!empty($query))
            return implode(' UNION ALL ', $query);
        else
            return implode(' UNION ALL ', $this->_query_aggregations);
    }



    
    public function buildAllSummQuery(){
        if(empty($this->_query_aggregations)) return $this;

        /*
        $search = array();
        if(!empty($this->_data_setting['filters']['search_model'])){
            $search_model = $this->_data_setting['filters']['search_model'];
            $search_text = $search_model::$text;
            $search_text = str_replace('_', '\_', $search_text);
            $search[] = 'indicator_value LIKE "%' . $search_text . '%"';
        }
        */
        $this->buildAllQuery();

        $query = array();
        foreach($this->_query_aggregations as $key => $query_aggregation){
            $query[] = "
                SELECT
                    sum(indicator_value) as param_y,
                    unique_index
                FROM (
                    $query_aggregation
                ) AS data
            ";
        }
        $query = $this->concatQueryAggregations($query);
        $this->_query = $query;

        return $this;

    }
    
    
    

    public function buildAllQuery(){
        if(empty($this->_query_aggregations)) return $this;
        

        $query = array();
        foreach($this->_query_aggregations as $key => $query_aggregation){
            if($this->_query_aggregations_status[$key]){
                $group = 'param_s1, param_s2, param_s3, unique_index';
                $param_y = 'sum(indicator_value)';
                if($this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                    $group = 'param_x, unique_index';
                    $param_y = 'indicator_value';
                }

                $query[] = "
                SELECT
                    param_x,
                    param_s1,
                    param_s2,
                    param_s3,
                    $param_y as param_y,
                    unique_index
                FROM (
                    $query_aggregation
                ) AS DATA
                GROUP BY
                    $group
            ";
            } else {

                $query[] = "
                SELECT
                    param_x,
                    param_s1,
                    param_s2,
                    param_s3,
                    indicator_value as param_y,
                    unique_index
                FROM (
                    $query_aggregation
                ) AS DATA
            ";
            }
        }
        $query = $this->concatQueryAggregations($query);
        $this->_query = $query;

        return $this;        
    }
    


    private function checkIndicatorFieldName($field_name){
        if($field_name == 'param_x') return true;
        foreach($this->_data_setting['indicators'] as $indicator){
            if('f' . $indicator['unique_index'] == $field_name) return true;
        }
        return false;
    }
    
    
    
    /**
     * addTransformationQuery
     */
    public function addTransformationQuery(){
        $data_1 = array(); 
        $data_2 = array();

        if(empty($this->_query)) return $this;

        $is_id = ($this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID);
        foreach($this->_data_setting['indicators'] as $indicator){
            if(!isset($indicator['unique_index'])) continue;

            $aggregate = true;
            if($is_id && $this->_data_setting['param']['module_copy_id'] == $indicator['module_copy_id']){
                $aggregate = false;
            }

            $this->_indicator_keys[$indicator['unique_index']] = $indicator['unique_index'];
            if($aggregate){
                $data_1[] = 'IF(unique_index = "' . $indicator['unique_index'] . '", param_y, 0) AS f' . $indicator['unique_index'];
                $data_2[] = 'SUM(f' . $indicator['unique_index'] . ') AS f' . $indicator['unique_index'];
            } else {
                $data_1[] = 'IF(unique_index = "' . $indicator['unique_index'] . '", param_y, "") AS f' . $indicator['unique_index'];
                $data_2[] = 'GROUP_CONCAT(f' . $indicator['unique_index'] . ' SEPARATOR "") AS f' . $indicator['unique_index'];
            }
        }

        $group_by = 'GROUP BY param_s1, param_s2, param_s3';
        $order_by = 'ORDER BY param_s1, param_s2, param_s3';
        if($this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
            $group_by = 'GROUP BY param_x';
        }


        //sorting
        if(!empty($this->_data_setting['sorting'])){
            $order_by = array();
            foreach($this->_data_setting['sorting'] as $field_name => $order){
                if($this->checkIndicatorFieldName($field_name) == false) continue;

                if($field_name == 'param_x'  && !\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
                    $field_name = 'param_s1';
                }

                $order_by[] = $field_name . ' ' .$order;
            }
            if(!empty($order_by)){
                $order_by = 'ORDER BY ' . implode(',', $order_by);
            } else {
                $order_by = 'ORDER BY param_s1, param_s2, param_s3';
            }
        }


        //pagination
        $limit = '';
        $offset = '';
        $calc_found_rows  = '';
        if(!empty($this->_data_setting['pagination'])){
            $calc_found_rows = 'SQL_CALC_FOUND_ROWS ';
            $limit = 'LIMIT ' . $this->_data_setting['pagination']['limit'];
            $offset = 'OFFSET ' . $this->_data_setting['pagination']['offset'];
        }


        //search
        /*
        $search = array();
        if(!empty($this->_data_setting['filters']['search_model'])){
            $search_model = $this->_data_setting['filters']['search_model'];
            $search_text = $search_model::$text;
            if(!empty($search_text)){
                $search_text = str_replace('_', '\_', $search_text);
                if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
                    $search[] = 'param_x LIKE "%' . $search_text . '%"';
                } else {
                    $search[] = 'param_s1 LIKE "%' . $search_text . '%"';
                }

                foreach($this->_data_setting['indicators'] as $indicator){
                    $search[] = 'f' . $indicator['unique_index'] . ' LIKE "%' . $search_text . '%"';
                }
            }
        }
        */


        $data_1 = implode(',', $data_1);
        $data_2 = implode(',', $data_2);
        
        $query = "
            SELECT
                $calc_found_rows
            	param_x,
                param_s1,
                param_s2,
                param_s3,
                $data_2
             FROM (
                SELECT
                	param_x,
                    param_s1,
                    param_s2,
                    param_s3,
                    $data_1
                 FROM (
                    $this->_query
                 ) AS data
             ) AS data
             " . /*(!empty($search) ? 'WHERE ' . implode(' OR ', $search) : '') .*/ "
             $group_by
             $order_by
             $limit
             $offset
        ";
        
        $this->_query = $query;

        return $this;
    }








    /**
     * addTransformationQueryOnUniqueIndex
     */
    /*
    public function addTransformationQueryOnUniqueIndex(){
        $data_1 = array();
        $data_2 = array();

        if(empty($this->_query)) return $this;

        $indicator_keys = \DataModel::getInstance()->setText("
                            SELECT
                                unique_index
                            FROM (
                              $this->_query
                            ) AS data
                            GROUP BY unique_index
                            ORDER BY unique_index
                        ")->findCol();

        if(empty($indicator_keys)) return $this;

        foreach($indicator_keys as &$key){
            $key_t = \Translit::forDataBase($key);
            $this->_indicator_keys[$key_t] = $key;
            $data_1[] = 'IF(unique_index = "'.$key.'", param_y, 0) AS f'. $key_t;
            $data_2[] = 'SUM(f'.$key_t.') AS f'.$key_t;
        }
        unset($key);

        $order_by = 'ORDER BY param_s1, param_s2, param_s3';
        if(!empty($this->_data_setting['sorting'])){
            $order_by = array();
            foreach($this->_data_setting['sorting'] as $field_name => $order){
                $order_by[] = $field_name . ' ' .$order;
            }
            $order_by = 'ORDER BY ' . implode(',', $order_by);
        }

        $limit = '';
        $offset = '';
        if(!empty($this->_data_setting['pagination'])){
            $limit = 'LIMIT ' . $this->_data_setting['pagination']['limit'];
            $offset = 'OFFSET ' . $this->_data_setting['pagination']['offset'];
        }

        $data_1 = implode(',', $data_1);
        $data_2 = implode(',', $data_2);

        $query = "
            SELECT
            	param_x,
                param_s1,
                param_s2,
                param_s3,
                $data_2
             FROM (
                SELECT
                	param_x,
                    param_s1,
                    param_s2,
                    param_s3,
                    $data_1
                 FROM (
                    $this->_query
                 ) AS data
             ) AS data
             GROUP BY param_s1, param_s2, param_s3, unique_index
             $order_by
             $limit
             $offset
        ";

        $this->_query = $query;

        return $this;
    }
    */






    /**
     * addTransformationCircularQuery
     */
    public function addTransformationCircularQuery(){
        $data_1 = array(); 
        $data_2 = array();
        $query = array();
        if(empty($this->_query)) return $this;

        foreach($this->_data_setting['indicators'] as $indicator){
            if(!isset($indicator['unique_index'])) continue;
            $data_1[] = 'IF(unique_index = "'.$indicator['unique_index'].'", param_y, 0) AS formatted';
            $data_2[] = 'SUM(formatted) AS formatted';
            break;
        }

        $data_1 = implode(',', $data_1);
        $data_2 = implode(',', $data_2);
        $other = \Yii::t('ReportsModule.base', 'Other');
        
        $query[] = "
            SELECT
                formatted AS value,
                value as label,
                formatted
            FROM
            (
                (
                    SELECT
                    	param_x AS value,
                        param_s1,
                        param_s2,
                        param_s3,
                        $data_2
                     FROM (
                        SELECT
                        	param_x,
                            param_s1,
                            param_s2,
                            param_s3,
                            $data_1
                         FROM (
                            $this->_query
                         ) AS data
                         ORDER BY formatted desc
                         LIMIT 9
                     ) AS data
                     GROUP BY value
                     ORDER BY 
                        param_s1,
                        param_s2,
                        param_s3
                )
            ) AS data
        ";
 
        $query[] = "
            SELECT
                formatted as value,
                value as label,
                formatted
            FROM
            (
                (  
                    SELECT
                    	'$other' AS value,
                        '' AS param_s1,
                        '' AS param_s2,
                        '' AS param_s3,
                        $data_2
                     FROM (
                        SELECT
                        	param_x,
                            $data_1
                         FROM (
                            $this->_query
                         ) AS data
                         ORDER BY formatted desc
                         LIMIT 9,1000000000
                     ) AS data
                )    
            ) AS data
        ";
 

        $this->_query = $query;
        
        return $this;
    }    
    
    
    
}

