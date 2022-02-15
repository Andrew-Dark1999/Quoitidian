<?php
/**
* DataReportModel
* 
* @author Alex R.
*/

namespace Reports\models;


class DataReportModel{

    const ELEMENT_TYPE_INDICATOR    = 'indicator';
    const ELEMENT_TYPE_GRAPH        = 'graph';
    const ELEMENT_TYPE_TABLE        = 'table';

    
    private $_schema;
    private $_data_setting;    // array('params' => array(), 'indiators' => array())

    public static $use_filters = true;
    public static $filter_params = array();

    public static function getInstance(){
        return new self();
    }
    


    /**
     * setSchema
     */
    public function setSchema($schema){
        $this->_schema = $schema;
        
        return $this;
    }


    /**
     * setDataSetting
     */
    public function setDataSetting($setting){
        $this->_data_setting = $setting;
        
        return $this;
    }


    /**
     * executeQuery
     */
    private function executeQuery($query){
        $data_model = new \DataModel();
        $result = $data_model
                    ->setText($query)
                    ->findAll();
        return $result;
    }



    /**
     * executeQuery
     */
    private function executeQueryCount($query){
        $data_model = new \DataModel();
        $result = $data_model
                    ->setText($query)
                    ->findCount();
        return $result;
    }


     
    /**
     * getIndicators
     */
    public function getIndicators(){
        $this->appendOtherQuerySetting(array('actions' => array('search', 'filter')));
        $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                        ->setDataSetting($this->_data_setting)
                        ->setElementType(self::ELEMENT_TYPE_INDICATOR)
                        ->build()
                        ->getQuery();

        if(empty($query)) return;

        $data = $this->executeQuery($query);


        return $data;
    }



    /**
     * getGraph
     */
    public function getGraph($schema_element){
        $result = null;
        
        switch($schema_element['graph_type']){
            case \Reports\models\ConstructorModel::GRAPH_LINE :
                $result = $this->getGraphLine($schema_element);
                break;
            case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM :
                $result = $this->getGraphHistogram($schema_element);
                break;
            case \Reports\models\ConstructorModel::GRAPH_CIRCULAR :
                $result = $this->getGraphCircular($schema_element);
                break;
            case \Reports\models\ConstructorModel::GRAPH_CRATER :
                $result = $this->getGraphCrater($schema_element);
                break;
        }
        
        return $result; 
    }
    





    /**
     * getIndicatrosList
     */
    private function getIndicatorsList($indicators, $data_indicators){
        $list = array();
        if(empty($indicators)) return $list;
        foreach($indicators as $indicator){
            if(in_array($indicator['unique_index'], $data_indicators)){
                $list[] = $indicator;
            }
        }
        if(empty($list) && !empty($indicators)){
            $list[] = $indicators[0];
        }

        return $list;
    }


    /**
     * getGraphLineData
     */
    private function getGraphLineData($data_indicators){
        $data = array();

        $this->_data_setting['indicators'] = $this->getIndicatorsList($this->_data_setting['indicators'], $data_indicators);
        $this->appendOtherQuerySetting(array('actions' => array('search', 'filter')));
        $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                                ->setDataSetting($this->_data_setting)
                                ->setElementType(self::ELEMENT_TYPE_GRAPH)
                                ->build()
                                ->addTransformationQuery()
                                ->getQuery();

        if(!empty($query)){
            $data = $this->executeQuery($query);
        }

        return $data;
    }




    /**
     * getGraphLine
     */
    public function getGraphLine($schema_element, $take_period_data = true)
    {
        $no_data = false;
        $result = array(
            'data' => array(),
            'lineColors' => array(),
            'xkey' => '',
            'ykeys' => array(),
            'labels' => array(),
            'xLabels' => '',
        );

        $interval = array(
            '_date_interval_start' => $this->_data_setting['filters']['_date_interval_start'],
            '_date_interval_end' => $this->_data_setting['filters']['_date_interval_end']
        );

        if(!empty($this->_data_setting['indicators'])){
            $period = $this->_data_setting['indicators'][0]['period'];
            foreach($this->_data_setting['indicators'] as $indicator){
                $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
            }
            $indicators = array_keys($type_indicators);

            $data = array();

            if(!empty($schema_element['data_indicators'])){
                $data = $this->getGraphLineData($schema_element['data_indicators']);
            } else {
                $no_data = true;
                $this->_data_setting['indicators'] = array();
                $this->_data_setting['indicators'][0] = array(
                    'period' => 'month',
                    'unique_index' => '1',
                    'title' => \Yii::t('ReportsModule.messages', 'No data'),
                    'type_indicator' => \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY,
                );
                $type_indicators[1] = '';
            }
        } else {
                $no_data = true;
                $this->_data_setting['indicators'] = array();
                $this->_data_setting['indicators'][0] = array(
                    'period' => 'month',
                    'unique_index' => 1,
                    'title' => \Yii::t('ReportsModule.messages', 'No data'),
                    'type_indicator' => \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY,
                );
                $type_indicators[1] = '';

                $period = $this->_data_setting['indicators'][0]['period'];
                foreach($this->_data_setting['indicators'] as $indicator){
                    $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
                }
                $indicators = array_keys($type_indicators);
        }


        if(!empty($data)){

            $data = \Reports\models\ZeroValuesModel::getInstance()
                ->setParams(array(
                    'indicators' => $indicators,
                    'interval' => $interval,
                    'period' => $period,
                    'data' => $data))
                ->appendZeroValues();

            if(!empty($data)){
                foreach($data as &$row){
                    $row['param_x_sort'] = $row['param_x'];
                }

                /*
                $search = new \Search();
                $search->setTextFromUrl();
                if($search::$text !== null){

                    $data = $this->useMemoryTable(\Reports\models\ConstructorModel::GRAPH_LINE, $data);
                    if($data === false){
                        $data = \Reports\models\ZeroValuesModel::getInstance()
                            ->setParams(array(
                                'indicators' => $indicators,
                                'interval' => $interval,
                                'period' => $period,
                                'data' => array()))
                            ->appendZeroValues();
                    }
                }
                */
            }

        } else{
            $data = \Reports\models\ZeroValuesModel::getInstance()
                ->setParams(array(
                    'indicators' => $indicators,
                    'interval' => $interval,
                    'period' => $period,
                    'data' => array()))
                ->appendZeroValues();
        }


        $data_list = array();
        foreach($data as &$value){
            $unique_index = '';
            foreach(array_keys($value) as $key_value){
                if(in_array(substr($key_value, 1), $indicators))
                    $unique_index = substr($key_value, 1);
            }
            if(empty($unique_index)) continue;

            $type_indicator = $this->getTypeIndicator($type_indicators, $unique_index);
            $value['f' . $unique_index] = (float)$value['f' . $unique_index];
            $value['f' . $unique_index] = \Reports\models\ConstructorModel::formatNumber($type_indicator, $value['f' . $unique_index], '.', '');
            $data_list[] = $value;
        }

        $data = $data_list;

        $result['data'] = $data;

        $result['xkey'] = 'param_x';
        $result['xLabels'] = $period;


        $colors = array('#62D5CE', '#EF9C9C');
        $i = 0;

        if(!empty($this->_data_setting['indicators'])){
            foreach($this->_data_setting['indicators'] as $indicator){
                $result['lineColors'][] = $colors[$i];
                if(isset($indicator['unique_index'])) $result['ykeys'][] = 'f' . $indicator['unique_index'];
                if(isset($indicator['title'])) $result['labels'][] = $indicator['title'];
                $suffix['f' . $indicator['unique_index']] = $type_indicators[$indicator['unique_index']];
                $i++;
                if($i >= 2) $i = 0;
            }
            $result['other_params']['suffix'] = $suffix;
        }

        if($take_period_data && $no_data == false){
            $data_tp = array();
            $tp_model = new \Reports\models\TakePeriodToIntervalModel;
            $tp_model
                ->setDataSetting($this->_data_setting)
                ->run();
            if($tp_model->getAppendPeriod()){
                $data_setting = $tp_model->getDataSetting();

                $data_model = new \Reports\models\DataReportModel();
                $data_tp = $data_model
                    ->setSchema($this->_schema)
                    ->setDataSetting($data_setting)
                    ->getGraphLine($schema_element, false);
                if(!empty($data_tp)){
                    foreach($data_tp['data'] as &$tp_value){
                        $tp_value['is_first_period'] = true;
                    }
                }
            }
            if(!empty($data_tp))
                $result = $this->concatGrapgLineData($data_tp, $result);
        }
        return $result;
    }


    /**
     * concatGrapgLineData
     */
    private function concatGrapgLineData($data_tp, $data){
        $result = $data;
        $result['data'] = array_merge($data_tp['data'], $data['data']);
        return $result;
    }

    




    /**
     * getWeek
     */
    private function getWeek($date){
        $data_model = new \DataModel();
        $data_model = $data_model->setText('SELECT YEARWEEK("'.$date.'") AS param_s1')->findRow();
        
        return $data_model['param_s1'];
    }



    /**
     * zeroDataFromPeriod
     * @param $period
     * @return array
     */
    public function zeroDataFromPeriod($period){
        $result = array(
                    'param_x' => \Yii::t('ReportsModule.messages', 'No data'),
                    'param_s1' => "0",
                    'param_s2' => "0",
                    'param_s3' => "0",
                );

        switch($period){
            case 'day' :
                $result['param_x'] = date('Y-m-d', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s1'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s2'] = date('m', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s3'] = date('d', strtotime($this->_data_setting['filters']['_date_interval_start']));
                break;
            case 'all_period' :
                $result['param_x'] = "";
                break;
            case 'week' :
                $result['param_s1'] = $this->getWeek($this->_data_setting['filters']['_date_interval_start']);
                $result['param_x'] = $result['param_s1'];
                break;
            case 'month' :
                $result['param_x'] = date('Y-m-01', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s1'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s2'] = date('m', strtotime($this->_data_setting['filters']['_date_interval_start']));
                break;
            case 'quarter' :
                $quarter = \DateTimeOperations::getQuarter($this->_data_setting['filters']['_date_interval_start']);
                $result['param_x'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start'])) . '-' . $quarter;
                $result['param_s1'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s2'] = $quarter;
                break;
            case 'year' :
                $result['param_x'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start']));
                $result['param_s1'] = date('Y', strtotime($this->_data_setting['filters']['_date_interval_start']));
                break;
        }

        return $result;
    }






    /**
     * formatDataForHistogram
     * @param $period
     * @param $value
     * @return array
     */
    public function formatDataForHistogram($period, $value){
        $result = array(
            'param_x' => '',
            'param_s1' => "0",
            'param_s2' => "0",
            'param_s3' => "0",
        );

        switch($period){
            case 'day' :
                $result['param_x'] = $value;
                $result['param_s1'] = date('Y', strtotime($value));
                $result['param_s2'] = date('m', strtotime($value));
                $result['param_s3'] = date('d', strtotime($value));
                break;
            case 'all_period' :
                $result['param_x'] = $value;
                break;
            case 'week' :
                $result['param_x'] = $value;
                $result['param_s1'] = $value;
                break;
            case 'month' :
                $result['param_x'] = $value;
                $result['param_s1'] = date('Y', strtotime($value));
                $result['param_s2'] = date('m', strtotime($value));
                break;
            case 'quarter' :
                $result['param_x'] =  $value;
                $result['param_s1'] = substr($value, 0, 4);
                $result['param_s2'] = substr($value, 5);
                break;
            case 'year' :
                $result['param_x'] = date('Y', strtotime($value));
                $result['param_s1'] = date('Y', strtotime($value));
                break;
        }

        return $result;
    }






    /**
     * formatDataForCircular
     * @param $period
     * @param $value
     * @return array
     */
    public function formatDataForCircular($period, $value){
        $result = array(
            'param_x' => '',
            'param_s1' => "0",
            'param_s2' => "0",
            'param_s3' => "0",
        );

        switch($period){
            case 'day' :
                $result['param_x'] = $value;
                $result['param_s1'] = date('Y', strtotime($value));
                $result['param_s2'] = date('m', strtotime($value));
                $result['param_s3'] = date('d', strtotime($value));
                break;
            case 'all_period' :
                $result['param_x'] = $value;
                break;
            case 'week' :
                $result['param_x'] = $value;
                $result['param_s1'] = $value;
                break;
            case 'month' :
                $result['param_x'] = $value;
                $result['param_s1'] = date('Y', strtotime($value));
                $result['param_s2'] = date('m', strtotime($value));
                break;
            case 'quarter' :
                $result['param_x'] =  $value;
                $result['param_s1'] = substr($value, 0, 4);
                $result['param_s2'] = substr($value, 5);
                break;
            case 'year' :
                $result['param_x'] = date('Y', strtotime($value));
                $result['param_s1'] = date('Y', strtotime($value));
                break;
        }

        return $result;
    }



    /**
     * getZeroData
     */
    public function getZeroData($display_option){
        $result = array(
            'param_x' => \Yii::t('ReportsModule.messages', 'No data'),
            'param_s1' => 0,
            'param_s2' => 0,
            'param_s3' => 0,
        );

        foreach($this->_data_setting['indicators'] as $indicator){
            $result['f' . $indicator['unique_index']] = 0;
        }

        if($display_option == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY){
            $result = array_merge($result, $this->zeroDataFromPeriod($this->_data_setting['indicators'][0]['period']));
        }

        $result = array($result);
        return $result;
    }








    /**
     * getGraphHistogram
     */
    private function getGraphHistogram($schema_element){
        $result = array(
            'data' => array(),
            'barColors' => array(),
            'xkey' => 'param_x',
            'ykeys' => array(),
            'labels' => array(),
        );


        if(!empty($schema_element['data_indicators'])){
            $this->_data_setting['indicators'] = $this->getIndicatorsList($this->_data_setting['indicators'], $schema_element['data_indicators']);
            /*
            if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
                $this->appendOtherQuerySetting(array('actions' => array('filter')));
            } else {
                $this->appendOtherQuerySetting(array('actions' => array('filter', 'search')));
            }
            */
            $this->appendOtherQuerySetting(array('actions' => array('search', 'filter')));

            $query_builder = \Reports\extensions\Query\QueryBuilder::getInstance()
                                    ->setDataSetting($this->_data_setting)
                                    ->setElementType(self::ELEMENT_TYPE_GRAPH)
                                    ->build()
                                    ->addTransformationQuery();

            $query = $query_builder->getQuery();
        } else {
            $this->_data_setting['indicators'] = array();
            $this->_data_setting['indicators'][0] = array(
                'period' => 'month',
                'unique_index' => '1',
                'display_option' => $schema_element['display_option'],
                'title' => \Yii::t('ReportsModule.messages', 'No data'),
                'type_indicator' => \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY,
            );
        }

        $data = array();
        $data_is_zero = false;
        $type_indicators = array();
        $period = $this->getPeriodName($this->_data_setting['param']['field_name']);

        if(!empty($query_builder)){
            $indicator_keys = $query_builder->getIndicatorKeys();
        } else {
            $indicator_keys = array('1');
        }

        $interval = array(
            '_date_interval_start' => $this->_data_setting['filters']['_date_interval_start'],
            '_date_interval_end' => $this->_data_setting['filters']['_date_interval_end']
        );

        if(!empty($query)){
            $data = $this->executeQuery($query);
        }

        foreach($this->_data_setting['indicators'] as $indicator){
            $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
        }
        $indicators = array_keys($type_indicators);

        // форматируем данные из БД
        if(!empty($data)){
            if($this->_data_setting['indicators'][0]['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY){
               $type_indicators = array();
                foreach($indicators as $indicator){
                    $type_indicators[$indicator] = $this->_data_setting['indicators'][0]['type_indicator'];
                }
                $indicators = array_keys($type_indicators);
            }
        } else {
            // форматируем дефолтные данные
            //no data
            $data_is_zero = true;
            $data = $this->zeroDataFromPeriod(null);
            foreach($indicators as $indicator){
                $data['f'.$indicator] = 0;
            }

            $data = array(($data));
        }

        // format title
        if($data_is_zero == false && $period){
            $format_model = \Reports\extensions\ElementMaster\FormatDataTitle::getInstance();
            foreach($data as &$row){
                $data_tmp = array($this->formatDataForHistogram($period, $row['param_x']));
                $data_tmp = \Reports\models\ZeroValuesModel::getInstance()
                    ->setParams(array(
                            'indicators' => $indicators,
                            'interval' => $interval,
                            'period' => $period,
                            'data' => $data_tmp)
                    )
                    ->formatZeroValues();
                $data_tmp = array_values($data_tmp);

                $row['param_x'] = $format_model
                    ->setPeriod($period)
                    ->setInterval($interval)
                    ->setReturnWeekPeriod(false)
                    ->setValue($data_tmp[0]['param_x'])
                    ->format();
            }
        }

        // 1. search and filter
        if($data_is_zero == false){
            if(!empty($data)){
                foreach($data as &$row){
                    $row['param_x_sort'] = $row['param_x'];
                }

                /*
                if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
                    $data = $this->useMemoryTable(\Reports\models\ConstructorModel::GRAPH_HISTOGRAM, $data);
                    if($data === false){
                        // форматируем дефолтные данные
                        $data = $this->zeroDataFromPeriod(null);
                        foreach(array_keys($indicator_keys) as $indicator){
                            $data['f'.$indicator] = '0';
                            $type_indicators[$indicator] = $this->_data_setting['indicators'][0]['type_indicator'];
                        }
                        $indicators = array_keys($type_indicators);
                        $data = array(($data));
                    }
                }
                */
            }
        } // 1 (end)

        // format value
        $data_list = array();
        foreach($data as &$value){
            foreach(array_keys($value) as $key_value){
                if(in_array(substr($key_value, 1), $indicators)){
                    $unique_index = substr($key_value, 1);
                    if(empty($unique_index)) continue;
                    $type_indicator = $this->getTypeIndicator($type_indicators, $unique_index);
                    $value['f'.$unique_index] = (float)$value['f'.$unique_index];
                    $value['f'.$unique_index] = \Reports\models\ConstructorModel::formatNumber($type_indicator, $value['f'.$unique_index], '.', '');
                }
            }
            $data_list[] = $value;
        }


        $result['data'] = $data_list;

        $colors = array('#79d1cf', '#e67a77');
        $suffix = array();
        $ii = 0;
        for($i = 0; $i < count($indicators); $i++){
            $result['barColors'][] = $colors[$ii];
            $result['ykeys'][] = 'f' . $indicators[$i];
            $suffix['f' . $indicators[$i]] = $type_indicators[$indicators[$i]];
            $ii++;
            if($ii >= 2) $ii=0;
        }
        $result['other_params']['suffix'] = $suffix;


        foreach($this->_data_setting['indicators'] as $indicator){
            if(isset($indicator['title'])) $result['labels'][] = $indicator['title'];
        }

        return $result;
    }









    /**
     * getGraphHistogramChangedParams (not use)
     */
    /*
    private function getGraphHistogramChangedParams($schema_element){
        $result = array(
            'data' => array(),
            'barColors' => array(),
            'xkey' => 'param_x',
            'ykeys' => array(),
            'labels' => array(),
        );

    
        if(!empty($schema_element['data_indicators'])){
            $this->_data_setting['indicators'] = $this->getIndicatorsList($this->_data_setting['indicators'], $schema_element['data_indicators']);
            $this->appendOtherQuerySetting(array('actions' => array('filter')));
            $query_builder = \Reports\extensions\Query\QueryBuilder::getInstance()
                            ->setDataSetting($this->_data_setting)
                            ->setElementType(self::ELEMENT_TYPE_GRAPH)
                            ->build();

            if($this->_data_setting['indicators'][0]['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY)
                $query_builder->addTransformationQueryOnUniqueIndex();
            else
                $query_builder->addTransformationQuery();

            $query = $query_builder->getQuery();
        } else {
            $this->_data_setting['indicators'] = array();
            $this->_data_setting['indicators'][0] = array(
                'period' => 'month',
                'unique_index' => '1',
                'display_option' => $schema_element['display_option'],
                'title' => \Yii::t('ReportsModule.messages', 'No data'),
                'type_indicator' => \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY,
            );
        }

        $data = array();
        $period = null;
        $data_is_zero = false;
        if(!empty($query_builder)){
            $indicator_keys = $query_builder->getIndicatorKeys();
        } else {
            $indicator_keys = array('1');
        }

        $interval = array(
            '_date_interval_start' => $this->_data_setting['filters']['_date_interval_start'],
            '_date_interval_end' => $this->_data_setting['filters']['_date_interval_end']
        );

        if(!empty($query)){
            $data = $this->executeQuery($query);
        }

        foreach($this->_data_setting['indicators'] as $indicator){
            $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
        }
        $indicators = array_keys($type_indicators);

        // форматируем данные из БД
        if(!empty($data)){
            if($this->_data_setting['indicators'][0]['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY){
                //data isset
                $data_list = array();
                foreach($data as &$value){
                    $unique_index = '';
                    foreach(array_keys($value) as $key_value){
                        if(in_array(substr($key_value, 1), $indicators))
                            $unique_index = substr($key_value, 1);
                    }
                    if(empty($unique_index)) continue;

                    $type_indicator = $this->getTypeIndicator($type_indicators, $unique_index);
                    $value['f' . $unique_index] = (float)$value['f' . $unique_index];
                    $value['f' . $unique_index] = \Reports\models\ConstructorModel::formatNumber($type_indicator, $value['f' . $unique_index], '.', '');
                    $data_list[] = $value;
                }
            } else {
                //data isset
                $type_indicators = array();

                foreach(array_keys($indicator_keys) as $indicator){
                    $type_indicators[$indicator] = $this->_data_setting['indicators'][0]['type_indicator'];
                }
                $indicators = array_keys($type_indicators);

                $data_list = array();

                foreach($data as &$value){
                    foreach(array_keys($value) as $key_value){
                        if(in_array(substr($key_value, 1), $indicators)){
                            $unique_index = substr($key_value, 1);
                            if(empty($unique_index)) continue;
                            $type_indicator = $this->getTypeIndicator($type_indicators, $unique_index);
                            $value['f'.$unique_index] = (float)$value['f'.$unique_index];
                            $value['f'.$unique_index] = \Reports\models\ConstructorModel::formatNumber($type_indicator, $value['f'.$unique_index], '.', '');
                        }
                    }
                    $data_list[] = $value;
                }

            }

            $data = $data_list;


            $period =  $this->_data_setting['indicators'][0]['period'];
            if($period){
                $data = \Reports\models\ZeroValuesModel::getInstance()
                    ->setParams(array(
                        'indicators' => $indicators,
                        'interval' => $interval,
                        'period' => $period,
                        'data' => $data))
                    ->appendZeroValues();
            }

        } else {
            // форматируем дефолтные данные
            //no data
            $data_is_zero = true;
            $period = $this->_data_setting['indicators'][0]['period'];

            foreach(array_keys($indicator_keys) as $indicator){
                $type_indicators[$indicator] = $this->_data_setting['indicators'][0]['type_indicator'];
            }
            $indicators = array_keys($type_indicators);

            $data = \Reports\models\ZeroValuesModel::getInstance()
                            ->setParams(array(
                                'indicators' => $indicators,
                                'interval' => $interval,
                                'period' => $period,
                                'data' => array()))
                            ->appendZeroValues();
            $data = array_values($data);

            $result['labels'][] = \Yii::t('ReportsModule.messages', 'No data');
        }


        // format title
        if($period){
            $format_model = \Reports\extensions\ElementMaster\FormatDataTitle::getInstance();
            foreach($data as &$row){
                $row['param_x'] = $format_model
                    ->setPeriod($period)
                    ->setInterval($interval)
                    ->setValue($row['param_x'])
                    ->format();
            }
        }

        $result['data'] = $data;



        $colors = array('#E67A77', '#F2C021', '#95B85E', '#1FB5AC', '#57C8F2', '#A48AD4','#C7CBD6', '#FA8564');
        $ii = 0;
        for($i = 0; $i < count($indicators); $i++){
            $result['barColors'][] = $colors[$ii];
            $result['ykeys'][] = 'f' . $indicators[$i];
            $ii++;
            if($ii >= 8) $ii=0;
        }


        if($data_is_zero == false){

            if($this->_data_setting['indicators'][0]['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_NOT_DISPLAY){
                foreach($this->_data_setting['indicators'] as $indicator){
                    if(isset($indicator['title'])) $result['labels'][] = $indicator['title'];
                }
            } else {
                $period = $this->getPeriodName($this->_data_setting['param']['field_name']);
                if($period === null){
                    foreach($indicator_keys as $key => $value){
                        $result['labels'][] = $value;
                    }
                } else {
                    $labels = array();
                    $format_model = new  \Reports\extensions\ElementMaster\FormatDataTitle();
                    foreach($indicator_keys as $value){
                        $data_tmp = array($this->formatDataForHistogram($period, $value));
                        $data_tmp = \Reports\models\ZeroValuesModel::getInstance()
                            ->setParams(array(
                                    'indicators' => $indicators,
                                    'interval' => $interval,
                                    'period' => $period,
                                    'data' => $data_tmp)
                            )
                            ->formatZeroValues();
                        $data_tmp = array_values($data_tmp);
                        $labels[] = $format_model
                            ->setPeriod($period)
                            ->setInterval($interval)
                            ->setValue($data_tmp[0]['param_x'])
                            ->format();
                    }
                    $result['labels'] = $labels;
                }
            }
        }

        return $result;        
    }
    */








    /**
     * getTypeIndicator
     */
    private function getTypeIndicator($indicators, $key){
        if(is_int($key)){
            if($indicators[$key]['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT)
                return 'amount';
            else
                return $indicators[$key]['type_indicator'];
        } else {
            if(isset($indicators[$key])) return $indicators[$key];
        }
    }






    /**
     * getFormatCircularData
     */
    public function getFormatCircularData(&$data){
        $period = null;
        if(!empty($this->_data_setting['indicators'][0]['display_option']) && $this->_data_setting['indicators'][0]['display_option'] == \Reports\models\ConstructorModel::GRAPH_DISPLAY_OPTION_DISPLAY){
            $period = $this->getPeriodName($this->_data_setting['param']['field_name']);
        }

        $interval =array (
            '_date_interval_start' => $this->_data_setting['filters']['_date_interval_start'],
            '_date_interval_end' => $this->_data_setting['filters']['_date_interval_end']
        );

        foreach($this->_data_setting['indicators'] as $indicator){
            $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
        }
        $indicators = array_keys($type_indicators);

        if(!empty($period)){
            $format_model = \Reports\extensions\ElementMaster\FormatDataTitle::getInstance();
            foreach($data[0] as &$value){
                $data_tmp = array($this->formatDataForCircular($period, $value['label']));

                $data_tmp = \Reports\models\ZeroValuesModel::getInstance()
                    ->setParams(array(
                        'indicators' => $indicators,
                        'interval' => $interval,
                        'period' => $period,
                        'data' => $data_tmp)
                    )
                    ->formatZeroValues();

                $data_tmp = array_values($data_tmp);

                $value['label'] = $format_model
                    ->setPeriod($period)
                    ->setInterval($interval)
                    ->setValue($data_tmp[0]['param_x'])
                    ->format();
            }
        }
    }



    private function deleteZeroValues($data, $field_name){
        $result = array();
        foreach($data as $value){
            if((float)$value[$field_name] == 0) continue;
            $result[] = $value;
        }
        return $result;
    }


    /**
     * getGraphCircular
     */
    private function getGraphCircular($schema_element){
        $result = array(
            'data' => array(),
            'labelColor' => '#8b8b8b',
            'colors' => array(), 
        );
        
        $query = null;
        //query
        if(!empty($schema_element['data_indicators'])){
            $this->_data_setting['indicators'] = $this->getIndicatorsList($this->_data_setting['indicators'], $schema_element['data_indicators']);
            $this->appendOtherQuerySetting(array('actions' => array('search', 'filter')));

            $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                            ->setDataSetting($this->_data_setting)
                            ->setElementType(self::ELEMENT_TYPE_GRAPH)
                            ->build()
                            ->addTransformationCircularQuery()
                            ->getQuery();
        }


        $data = array();

        if(!empty($query)){
            //query1
            $data[] = $this->executeQuery($query[0]);
            if(!empty($data[0])){
                $this->getFormatCircularData($data);
                //query 2
                $data[] = $this->executeQuery($query[1]);
                if(count($data[1]) == 1 && $data[1][0]['value'] && $data[1][0]['formatted']){
                    $data = array_merge($data[0], $data[1]);
                } else {
                    $data = $data[0];
                }
            } else {
                $data = array();
            }
        }

        $data = $this->deleteZeroValues($data, 'value');

        // processing
        if(!empty($data)){
            /*
            // 1. search and filter
            $search = new \Search();
            $search->setTextFromUrl();
            if($search::$text !== null){
                foreach($data as &$row){
                    $row['param_x_sort'] = $row['value'];
                }

                $data = $this->useMemoryTable(\Reports\models\ConstructorModel::GRAPH_CIRCULAR, $data);
                if($data === false){
                    $data = array(
                        array(
                            'label' => \Yii::t('ReportsModule.messages', 'No data'),
                            'value' => 1,
                            'formatted' => number_format(0, 2, ',', ' '),
                        ),
                    );
                }
            } else {

            }
            */

            $data = \Helper::arraySort($data, 'value', 'desc');

            $data_list = array();
            $type_indicator = $this->getTypeIndicator($this->_data_setting['indicators'], 0);
            foreach($data as &$value){
                $value['value'] = (float)$value['value'];
                $value['formatted'] = (float)$value['formatted'];
                $value['formatted'] = \Reports\models\ConstructorModel::formatNumber($type_indicator, $value['formatted'], ',', ' ', array('percent_value' => '%'));
                $data_list[] = $value;
            }
            $data = $data_list;
            $result['data'] = $data;


            $colors = array('#FA8564', '#F2C021', '#95B85E', '#1FB5AC', '#57C8F2', '#A48AD4', '#6479c9', '#c96496', '#e49146', '#C7CBD6');
            $ii = 0;
            for($i = 0; $i <= count($data); $i++){
                $result['colors'][] = $colors[$ii];
                $ii++;
                if($ii >= 10) $ii=0;
            }
        } else { 
                $data = array(
                            array(
                            	'label' => \Yii::t('ReportsModule.messages', 'No data'),
                                'value' => 1,
                                'formatted' => number_format(0, 2, ',', ' '),
                            ), 
                        );
                $result['data'] = $data;
                $result['colors'][] = '#E67A77';
        }

        return $result;      
    }





    /**
     * getGraphCrater
     */
    private function getGraphCrater($schema_element){
        
        
    }






    /**
     * prepareOtherQueryParams
     * @param string $count
     * @param array $params
     */
    private function appendOtherQuerySetting($params = array()){
        unset($this->_data_setting['sorting']);
        unset($this->_data_setting['pagination']);
        unset($this->_data_setting['filters']['search_model']);
        unset($this->_data_setting['filters']['filter_model']);
        unset($this->_data_setting['filters']['filter_params']);

        if(empty($params)) return;

        if(in_array('sorting', $params['actions'])){
            \Sorting::getInstance()->setParamsFromUrl();
            $sorting = \Sorting::$params;
            if($sorting){
                $this->_data_setting = \Helper::arrayMerge($this->_data_setting, array('sorting' => $sorting));
            } else {
                $this->_data_setting = \Helper::arrayMerge($this->_data_setting, array('sorting' => array('param_x' => 'asc')));
            }

        }

        if(in_array('pagination', $params['actions'])){
            $pagination = new \Pagination();
            $pagination->setParamsFromUrl();

            if($pagination->getActivePageSize() > 0){
                $this->_data_setting['pagination']['limit'] = $pagination->getActivePageSize();
                $this->_data_setting['pagination']['offset'] = $pagination->getOffset();
            }
        }

        if(in_array('search', $params['actions'])){
            $search = new \Search();
            $search->setTextFromUrl();
            if($search::$text !== null){
                $this->_data_setting['filters']['search_model'] = $search;
            }

        }

        if(in_array('filter', $params['actions'])){
            $filters = new \Filters();
            $filters->setTextFromUrl();
            if(!$filters->isTextEmpty()){
                $this->_data_setting['filters']['filter_model'] = $filters;
            }

            // for Constructor
            $filter_params = \Reports\models\ReportsFilterModel::getFilterParams($this->_schema);
            $filter_params_indicator = \Reports\models\ReportsFilterModel::getFilterParamsIndicator($this->_schema);

            if(!empty($filter_params)){
                $this->_data_setting['filters']['filter_params'] = $filter_params;
            } elseif(!empty(self::$filter_params)){ // динамическое изменение параметров в конструкторе
                $this->_data_setting['filters']['filter_params'] = self::$filter_params;
            }

            if(!empty($filter_params_indicator)){
                $this->_data_setting['filters']['filter_params_indicator'] = $filter_params_indicator;
            }

        }
    }


    /**
     * getPeriodName
     * @param $field_name
     * @return null|string
     */
    public function getPeriodName($field_name){
        $period = null;
        switch($field_name){
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_DAY :
                $period = 'day'; break; 
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_WEEK :
                $period = 'week'; break; 
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_MONTH :
                $period = 'month'; break; 
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_QUARTER :
                $period = 'quarter'; break; 
            case \Reports\models\ConstructorModel::PARAM_FIELD_NAME_YEAR :
                $period = 'year'; break; 
        }

        return $period;
    }



    /**
     * getTable
     */

    public function getTableQuery(){
        // period
        $period = $this->getPeriodName($this->_data_setting['param']['field_name']);

        // 1
        if($period === null){
            $this->appendOtherQuerySetting(array('actions' => array('search', 'filter', 'sorting', 'pagination')));
            $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                                    ->setDataSetting($this->_data_setting)
                                    ->setElementType(self::ELEMENT_TYPE_TABLE)
                                    ->build()
                                    ->addTransformationQuery()
                                    ->getQuery();
            if(empty($query)) return;

            $data = $this->executeQuery($query);

            \Pagination::getInstance()->setItemCount();
            // если страница пагинации указан больше чем есть в действительности - еще раз выполняем вигрузку для последней страницы в наборе
            if(\Pagination::switchActivePageIdLarger()){
                $this->appendOtherQuerySetting(array('actions' => array('search', 'filter', 'sorting', 'pagination')));
                $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                    ->setDataSetting($this->_data_setting)
                    ->setElementType(self::ELEMENT_TYPE_TABLE)
                    ->build()
                    ->addTransformationQuery()
                    ->getQuery();
                if(empty($query)) return;
                $data = $this->executeQuery($query);
            }


            if(empty($data)) return;

            foreach($data as &$row){
                $row['id'] = $row['param_x'];
            }

            $result = $data;
            if($result === false) $result = array();

            // 2. with Memory table...
        } else {
            $this->appendOtherQuerySetting(array('actions' => array('search', 'filter')));
            $query = \Reports\extensions\Query\QueryBuilder::getInstance()
                                ->setDataSetting($this->_data_setting)
                                ->setElementType(self::ELEMENT_TYPE_TABLE)
                                ->build()
                                ->addTransformationQuery()
                                ->getQuery();
            if(empty($query)) return;
            $data = $this->executeQuery($query);
            if(empty($data)) return;

            $this->appendOtherQuerySetting(array('actions' => array('filter', 'sorting')));
            $interval = array(
                '_date_interval_start' => $this->_data_setting['filters']['_date_interval_start'],
                '_date_interval_end' => $this->_data_setting['filters']['_date_interval_end']
            );

            foreach($this->_data_setting['indicators'] as $indicator){
                $type_indicators[$indicator['unique_index']] = $indicator['type_indicator'];
            }
            $indicators = array_keys($type_indicators);

            $data = \Reports\models\ZeroValuesModel::getInstance()
                            ->setParams(array(
                                            'indicators' => $indicators,
                                            'interval' => $interval,
                                            'period' => $period,
                                            'data' => $data))
                            ->appendZeroValues();

            $format_model = \Reports\extensions\ElementMaster\FormatDataTitle::getInstance();

            $keys = null;
            if(!empty($this->_data_setting['sorting'])){
                $keys = array_keys($this->_data_setting['sorting']);
            }

            foreach($data as &$row){
                if($keys){
                    $row['param_x_sort'] = $row[$keys[0]];
                } else {
                    $row['param_x_sort'] = 'param_x';
                }

                $row['param_x'] = $format_model
                    ->setPeriod($period)
                    ->setInterval($interval)
                    ->setValue($row['param_x'])
                    ->format();
            }

            $result =  $this->useMemoryTable(\Reports\models\ConstructorModel::TABLE, $data);

            \Pagination::getInstance()->setItemCount();
            // если страница пагинации указан больше чем есть в действительности - еще раз выполняем вигрузку для последней страницы в наборе
            if(\Pagination::switchActivePageIdLarger()){
                $result =  $this->useMemoryTable(\Reports\models\ConstructorModel::TABLE, $data);
            }


            if($result === false) $result = array();
        }

        return $result;
    }












    /**
     * useMemoryTable
     */
    private function useMemoryTable($element_type, $data){

        if(in_array($element_type, array(\Reports\models\ConstructorModel::GRAPH_CIRCULAR, \Reports\models\ConstructorModel::GRAPH_LINE))){
            $this->appendOtherQuerySetting(array('actions' => array('filter')));
        }

            //QueryMemoryTable
        $queryes = \Reports\extensions\Query\QueryMemoryTable::getInstance()
                        ->setDataSetting($this->_data_setting)
                        ->setData($data)
                        ->setReportsSchema($this->_schema)
                        ->setElementType($element_type)
                        ->build($element_type)
                        ->getResult();


        $data_model = new \DataModel();
        //create
        $data_model->setText($queryes['create'])->execute();
        //insert
        foreach($queryes['insert'] as $insert){
            $data_model->setText($insert)->execute();
        }

        switch($element_type){
            /*
            case \Reports\models\ConstructorModel::GRAPH_CIRCULAR:
                $this->appendOtherQuerySetting(array('actions' => array('search')));
                $this->_data_setting['sorting'] = array('param_x_sort' => 'desc');
                $queryes_sel = \Reports\extensions\Query\QueryMemoryTable::getInstance()
                    ->setDataSetting($this->_data_setting)
                    ->setData($data)
                    ->setReportsSchema($this->_schema)
                    ->setElementType($element_type)
                    ->prepareFieldsGraphCircular()
                    ->prepareSelectCircularQuery()
                    ->getResult();
                break;
            case \Reports\models\ConstructorModel::GRAPH_LINE:
                $this->appendOtherQuerySetting(array('actions' => array('search')));
                $queryes_sel = \Reports\extensions\Query\QueryMemoryTable::getInstance()
                    ->setDataSetting($this->_data_setting)
                    ->setData($data)
                    ->setReportsSchema($this->_schema)
                    ->setElementType($element_type)
                    ->prepareFields()
                    ->prepareSelectQuery()
                    ->getResult();
                break;

            case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM:
                $this->appendOtherQuerySetting(array('actions' => array('search', 'sorting')));

                $queryes_sel = \Reports\extensions\Query\QueryMemoryTable::getInstance()
                    ->setDataSetting($this->_data_setting)
                    ->setData($data)
                    ->setReportsSchema($this->_schema)
                    ->setElementType($element_type)
                    ->prepareFields()
                    ->prepareSelectQuery()
                    ->getResult();
                break;
            */
            case \Reports\models\ConstructorModel::TABLE:
                $this->appendOtherQuerySetting(array('actions' => array('sorting', 'pagination')));

                $queryes_sel = \Reports\extensions\Query\QueryMemoryTable::getInstance()
                    ->setDataSetting($this->_data_setting)
                    ->setData($data)
                    ->setReportsSchema($this->_schema)
                    ->setElementType($element_type)
                    ->prepareFields()
                    ->prepareSelectQuery()
                    ->getResult();
                break;
        }

        $params = \Yii::app()->params;
        if(!empty($params['reports']['logging_query'])){
            \DataModel::getInstance()->setText('insert into {{reports_query}} (date_create, query, element) values (now(), "' . addslashes($queryes_sel['select']) . '", "' . $element_type . '_memory' . '")')->execute();
        }

        $data = $this->executeQuery($queryes_sel['select']);

        //drop
        $data_model->setText($queryes['drop'])->execute();

        if(empty($data)) return false;

        return $data;
    }






    
    
}
