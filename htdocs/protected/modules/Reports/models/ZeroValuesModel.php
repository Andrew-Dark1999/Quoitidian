<?php
/**
* ZeroValuesModel
* 
* @author Alex R.
*/

namespace Reports\models;


class ZeroValuesModel{
    
    private $_indicators;
    private $_interval;
    private $_period;
    private $_data;
    private $_week = 1; //monday
    
    
    public static function getInstance(){
        return new self();
    }


    public function setWeek($week){
        $this->_week = $week;
        return $this;
    }


    /**
     * setParams
     */
    public function setParams($params){
        if(isset($params['indicators'])) $this->_indicators = $params['indicators'];
        if(isset($params['interval'])) $this->_interval = $params['interval'];
        if(isset($params['period'])) $this->_period = $params['period'];
        if(isset($params['data'])) $this->_data = $params['data'];

        return $this;
    } 



    /**
     * appendZeroValues
     */
    public function appendZeroValues(){
        $result = $this->appendZeroValuesIntervalDay();

        switch($this->_period){
            case 'day' :
                $result = $this->appendZeroValuesIntervalDay();
                break;
            case 'all_period' :
                $result = $this->appendZeroValuesIntervalAllPeriod();
                break;
            case 'week' :
                $result = $this->appendZeroValuesIntervalWeek();
                break; 
            case 'month' :
                $result = $this->appendZeroValuesIntervalMonth();
                break;
            case 'quarter' :
                $result = $this->appendZeroValuesIntervalQuarter();
                break; 
            case 'year' :
                $result = $this->appendZeroValuesIntervalYear();
                break; 
        }
        return $result;
    }



    /**
     * formatZeroValues
     */
    public function formatZeroValues(){
        $result = array();    

        switch($this->_period){
            case 'day' :
                $result = $this->setFirstDays();
                break;
            case 'all_period' :
                $result = $this->setFirstDays();
                break; 
            case 'week' :
                $result = $this->setFirstWeekMonday();
                break;
            case 'month' :
                $result = $this->setFirstMonth();
                break;
            case 'quarter' :
                $result = $this->setFirstQuarter();
                break; 
            case 'year' :
                $result = $this->setFirstYear();
                break; 
        }
  
        return $result;
    }






    /**
     * getZeroElementValue
     */
    private function getZeroElementValue(){
        $result = array(
                        'param_x' => '',
                        'param_s1' => '0',
                        'param_s2' => '0',
                        'param_s3' => '0',
                    );

        foreach($this->_indicators as $indicator){
            $result['f' . $indicator] = 0;
        }
        
        return $result;
    }
    
    
  

    /**
     * setFirstDays
     */
    private function setFirstDays(){
        $result = array();
        foreach ($this->_data as $row) {
            $result[$row['param_x']] = $row;
        }
        return $result;
    }
    







  
    /**
     * appendZeroValuesIntervalDay
     */
    private function appendZeroValuesIntervalDay(){
        $begin = new \DateTime($this->_interval['_date_interval_start']);
        $end = new \DateTime($this->_interval['_date_interval_end']);
        $end = $end->modify('+ 1 day');
        
        $date_interval = new \DateInterval('P1D');
        $date_range = new \DatePeriod($begin, $date_interval, $end);
        
        $zero_value = $this->getZeroElementValue();
        
        $dr1 = array();
        
        foreach ($date_range as $date) {
            $zv = $zero_value;
            $d = $date->format('Y-m-d');
            $zv['param_x'] = $d;
            $dr1[$d] = $zv;
        }
        
        $dr2 = $this->setFirstDays();
        
        $result = array_merge($dr1, $dr2);
        $result = array_values($result);        

        return $result;
    }







    /**
     * appendZeroValuesIntervalDay
     */
    private function appendZeroValuesIntervalAllPeriod(){

        if(empty($this->_data)){
            $this->_data =  array($this->getZeroElementValue());
        }

        $result = $this->setFirstDays();
        $result = array_values($result);

        return $result;
    }










    /**
     * appendZeroValuesIntervalWeek
     */
    private function appendZeroValuesIntervalWeek(){
        $result = array();
        switch($this->_week){
            case 0;
                $result = $this->appendZeroValuesIntervalWeekSunday();
                break;
            case 1;
                $result = $this->appendZeroValuesIntervalWeekMonday();
                break;
        }

        return $result;
    }





    /**
     * appendZeroValuesIntervalWeekMonday
     */
    private function appendZeroValuesIntervalWeekMonday(){
        $begin = new \DateTime($this->_interval['_date_interval_start']);
        $begin_step = new \DateTime($this->_interval['_date_interval_start']);
        $end = new \DateTime($this->_interval['_date_interval_end']);

        // dr1
        $dr1 = array();
        $zero_value = $this->getZeroElementValue();
        $first_steep = true;

        while(true){
            // если первая дата
            if($first_steep){
                $first_steep = false;
                $zv = $zero_value;
                $d = $begin_step->format('Y-m-d');
                $zv['param_x'] = $d;
                $dr1[$d] = $zv;
                $begin_step = \DateTimeOperations::getFirstDateWeek($begin_step->format('Y-m-d'));
                $begin_step = new \DateTime($begin_step);
                if(strtotime($begin_step->format('Y-m-d')) <= strtotime($begin->format('Y-m-d'))){
                    $begin_step->modify('+7 days');
                }

                if(strtotime($begin->format('Y-m-d')) == strtotime($end->format('Y-m-d'))){
                    break;
                } else {
                    continue;
                }
            } else {
                $first_steep = false;
            }

            if(strtotime($begin_step->format('Y-m-d')) < strtotime($end->format('Y-m-d'))){
                $zv = $zero_value;
                $d = $begin_step->format('Y-m-d');
                $zv['param_x'] = $d;
                $dr1[$d] = $zv;
            } elseif(strtotime($begin_step->format('Y-m-d')) == strtotime($end->format('Y-m-d'))){
                $zv = $zero_value;
                $d = $end->format('Y-m-d');
                $zv['param_x'] = $d;
                $dr1[$d] = $zv;
                break;
            } elseif(strtotime($begin_step->format('Y-m-d')) > strtotime($end->format('Y-m-d'))){
                break;
            }

            $begin_step->modify('+7 days');
        }

        // dr2
        $dr2 = $this->setFirstWeekMonday();

        $result = array_merge($dr1, $dr2);
        $result = array_values($result);

        return $result;
    }


    /**
     * сдвигаем недели на 1 назад, если начало недели "понедельник" и началась 53-я неделя в году
     */
    private function prepareDataToWeekMonday(){
        foreach ($this->_data as &$row){
            if(empty($row['param_s1']) || strlen($row['param_s1']) != 6) continue;

            $year = substr($row['param_s1'], 0, 4);
            $week = substr($row['param_s1'], 4, 2);

            $pre_year = $year - 1;
            $pre_yearweek = \DataModel::getInstance()->setText('select YEARWEEK("'.$pre_year.'-12-31", 3)')->findScalar();
            $pre_yearweek = array(
                                'year' => substr($pre_yearweek, 0, 4),
                                'week' => substr($pre_yearweek, 4, 2),
                                );

            if((integer)$pre_yearweek['year'] == (integer)$year){ // началась 53 неделя...
                if((integer)$week == 1){
                    $year = $year - 1;

                    $pre_yearweek2 = \DataModel::getInstance()->setText('select YEARWEEK("'.($pre_year - 1).'-12-31", 3)')->findScalar();
                    if((integer)substr($pre_yearweek2, 0, 4) == $pre_year)
                        $weeks_count = 52;
                    else
                        $weeks_count = 53;
                } else {
                    $weeks_count = $week-1;
                }
                if(strlen($weeks_count) == 1) $weeks_count = '0' . $weeks_count;

                $year_week =  $year . $weeks_count;
                $row['param_x'] = $year_week;
                $row['param_s1'] = $year_week;
            }
        }
    }


    /**
     * setFirstWeekMonday
     */
    private function setFirstWeekMonday(){
        $result = array();
        if(empty($this->_data)) return $result;

        $this->prepareDataToWeekMonday();

        foreach ($this->_data as $row) {
            if(empty($row['param_s1']) || strlen($row['param_s1']) != 6) continue;

            $zv = $row;
            $zv['param_x'] = \DateTimeOperations::getFirstDateWeekByYearWeek($zv['param_s1'], 1);

            if(strtotime($zv['param_x']) < strtotime($this->_interval['_date_interval_start'])){
                $zv['param_x'] = date('Y-m-d', strtotime($this->_interval['_date_interval_start']));

            }

            $result[$zv['param_x']] = $zv;
        }
        return $result;
    }








    /**
     * appendZeroValuesIntervalWeekSunday
     */
    private function appendZeroValuesIntervalWeekSunday(){
        $w = date('w', strtotime($this->_interval['_date_interval_start']));
        $add_days = 0;
        if((integer)$w > 0){
            $add_days = 7 - $w;
        }
          
        $begin = new \DateTime($this->_interval['_date_interval_start']);
        if($add_days > 0) $end = $begin->modify('+ '.$add_days.' day');
        
        $end = new \DateTime($this->_interval['_date_interval_end']);
        $end = $end->modify('+ 1 day');
        
        $date_interval = new \DateInterval('P1W');
        $date_range = new \DatePeriod($begin, $date_interval, $end);
        
        $zero_value = $this->getZeroElementValue();
        
        $dr1 = array();
        
        // dr1        
        if($add_days > 0){
            $zv = $zero_value;
            $d = date('Y-m-d', strtotime($this->_interval['_date_interval_start']));
            $zv['param_x'] = $d;                                                
            $dr1[$d] = $zv;
        }
        foreach ($date_range as $date) {
            $zv = $zero_value;
            $d = $date->format('Y-m-d');
            $zv['param_x'] = $d;
            $dr1[$d] = $zv;
        }
        if(strtotime($d) != strtotime($this->_interval['_date_interval_end'])){
            $zv = $zero_value;
            $d = date('Y-m-d', strtotime($this->_interval['_date_interval_end']));
            $zv['param_x'] = $d;
        }

        // dr2
        $dr2 = $this->setFirstWeekSunday();

        $result = array_merge($dr1, $dr2);
        $result = array_values($result);

        return $result;
    }










    /**
     * setFirstWeekSunday
     */
    private function setFirstWeekSunday(){
        $result = array();
        if(empty($this->_data)) return $result;

        foreach ($this->_data as $row) {
            if(empty($row['param_s1']) || strlen($row['param_s1']) != 6) continue;

            $zv = $row;

            $add_days = 0;
            $w = date('w', strtotime(substr($row['param_s1'], 0, 4) . '-01-01'));
            if((integer)$w > 0){
                $add_days = 7 - $w ;
            }

            $add_days = (substr($row['param_s1'], 4)-1) * 7 + $add_days;

            $zv['param_x'] = date('Y-m-d', strtotime('+ '.$add_days.' days', strtotime(substr($row['param_s1'], 0, 4) . '-01-01')));

            if(strtotime($zv['param_x']) < strtotime($this->_interval['_date_interval_start'])){
                $zv['param_x'] = date('Y-m-d', strtotime($this->_interval['_date_interval_start']));

            }

            $result[$zv['param_x']] = $zv;
        }

        return $result;
    }






    /**
     * setFirstMonth
     */
    private function setFirstMonth(){
        $result = array();
        foreach ($this->_data as $row) {
            $result[$row['param_x']] = $row;
        }
        return $result;
    }
    



    /**
     * appendZeroValuesIntervalMonth
     */
    private function appendZeroValuesIntervalMonth(){
        $begin = new \DateTime(date('Y-m-01', strtotime($this->_interval['_date_interval_start'])));
        $end = new \DateTime($this->_interval['_date_interval_end']);
        $end = $end->modify('+ 1 day');
        
        $date_interval = new \DateInterval('P1M');
        $date_range = new \DatePeriod($begin, $date_interval, $end);
        
        $zero_value = $this->getZeroElementValue();
        
        $dr1 = array();
        
        foreach ($date_range as $date) {
            $zv = $zero_value;
            $d = $date->format('Y-m-d');
            $zv['param_x'] = $d;
            $dr1[$d] = $zv;
        }
        
        $dr2 = $this->setFirstMonth();
        
        $result = array_merge($dr1, $dr2);
        $result = array_values($result);        

        return $result;
    }





    /**
     * setFirstQuarter
     */
    private function setFirstQuarter(){
        $result = array();
        foreach ($this->_data as $row) {
            if(empty($row['param_s1']) || strlen($row['param_s1']) != 4) continue;

            $tmp = $row;
            $month = \DateTimeOperations::getQuarterFirstMonth($tmp['param_s2']);
            if(empty($month)) continue;
            $tmp['param_x'] = date('Y-m-d', strtotime($tmp['param_s1'] . '-' .  \DateTimeOperations::getQuarterFirstMonth($tmp['param_s2']) . '-01'));
            $result[$tmp['param_x']] = $tmp;
        }

        return $result;
    }
    


    /**
     * appendZeroValuesIntervalQuarter
     */
    private function appendZeroValuesIntervalQuarter(){
        $date_begin = date('Y', strtotime($this->_interval['_date_interval_start'])) . '-' . \DateTimeOperations::getQuarterFirstMonth(\DateTimeOperations::getQuarter($this->_interval['_date_interval_start'])) . '-01';
        $begin = new \DateTime($date_begin);

        $end = new \DateTime($this->_interval['_date_interval_end']);
        $end = $end->modify('+ 1 day');
        
        $date_interval = new \DateInterval('P3M');
        $date_range = new \DatePeriod($begin, $date_interval, $end);
        
        $zero_value = $this->getZeroElementValue();
        
        $dr1 = array();
        $dr2 = array();
        
        foreach ($date_range as $date) {
            $zv = $zero_value;
            $d = $date->format('Y-m-d');
            $zv['param_x'] = $d;
            $dr1[$d] = $zv;
        }
        
        $dr2 = $this->setFirstQuarter();

        $result = array_merge($dr1, $dr2);
        $result = array_values($result);        

        return $result;
    }





    /**
     * setFirstYear
     */
    private function setFirstYear(){
        $result = array();
        foreach ($this->_data as $row) {
            if(empty($row['param_s1']) || strlen($row['param_s1']) != 4) continue;

            $tmp = $row;
            $tmp['param_x'] = date('Y-m-d', strtotime($tmp['param_s1'] . '-01-01'));
            $result[$tmp['param_x']] = $tmp;
        }

        return $result;
    }
    


    /**
     * appendZeroValuesIntervalYear
     */
    private function appendZeroValuesIntervalYear(){
        $result = array();

        $date_begin = date('Y', strtotime($this->_interval['_date_interval_start'])) . '-01-01';
        $begin = new \DateTime($date_begin);

        $end = new \DateTime($this->_interval['_date_interval_end']);
        $end = $end->modify('+ 1 day');
        
        $date_interval = new \DateInterval('P1Y');
        $date_range = new \DatePeriod($begin, $date_interval, $end);
        
        $zero_value = $this->getZeroElementValue();
        
        $dr1 = array();
        
        foreach ($date_range as $date) {
            $zv = $zero_value;
            $d = $date->format('Y-m-d');
            $zv['param_x'] = $d;
            $dr1[$d] = $zv;
        }

        $dr2 = $this->setFirstYear();        

        $result = array_merge($dr1, $dr2);
        $result = array_values($result);        

        return $result;
    }







}
