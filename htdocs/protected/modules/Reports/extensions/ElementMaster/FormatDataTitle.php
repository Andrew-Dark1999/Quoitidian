<?php
/**
* FormatDataTitle - форматирование подписей значений 
* @author Alex R.
* @version 1.0
*/
namespace Reports\extensions\ElementMaster;



class FormatDataTitle {
    

    private $_value;
    private $_interval;
    
    private $_period;
    private $_data_p;

    private $_return_week_period = true;


    public function __construct(){
        $this->_data_p = \LocaleCRM::getInstance2()->_data_p;
    }

    public static function getInstance(){
        return new self();
    }
    


    /**
     * setValue
     */
    public function setValue($value){
        $this->_value = $value;
        
        return $this;
    }


    /**
     * setInterval
     */
    public function setInterval($value){
        $this->_interval = $value;
        
        return $this;
    }



    /**
     * setPeriod
     */
    public function setPeriod($period){
        $this->_period = $period;
        
        return $this;
    }


    /**
     * setReturnWeekPeriod
     */
    public function setReturnWeekPeriod($value){
        $this->_return_week_period = $value;
        return $this;
    }

    
    /**
     * format
     */
    public function format(){
        $result = $this->_value;
        
        if(empty($this->_value)) return $result;

        if(!empty($this->_period)){
            switch($this->_period){
                case 'day' :
                    $result = $this->getTitleDay($this->_value);
                    break;
                case 'all_period' :
                    $result = $this->getTitleDay($this->_value);
                    break;
                case 'week' :
                    $result = $this->getTitleWeek($this->_value);
                    break;
                case 'month' :
                    $result = $this->getTitleMonth($this->_value);
                    break;
                case 'quarter' :
                    $result = $this->getTitleQuarter($this->_value);
                    break;
                case 'year' :
                    $result = $this->getTitleYear($this->_value);
                    break;
            }
        }

        return $result;
    }
    


    private function getTitleDay($date){
        return date($this->_data_p['dateFormats']['medium'], strtotime($date)); 
    }



    private function getFormatWeek($date_start, $date_end){
        if($this->_return_week_period){
            return $date_start->format($this->_data_p['dateFormats']['medium']) . '-' .$date_end->format($this->_data_p['dateFormats']['medium']);
        } else {
            return $date_start->format($this->_data_p['dateFormats']['medium']);
        }
    }

    private function getTitleWeek($date){
        $date_start = new \DateTime($date);
        $date_end = new \DateTime($date);

        $result = '';

        if($date_start == new \DateTime($this->_interval['_date_interval_end'])){
            return $this->getFormatWeek($date_start, $date_end);
        }

        $week = (integer)(date('w', strtotime($date)));
        if($week == 0) $week = 7;
        $days = 7 - $week;

        if($days !== 0){
            $date_end->modify('+'.$days.' days');
        } else {
            return $this->getFormatWeek($date_start, $date_end);
        }

        if($date_end > new \DateTime($this->_interval['_date_interval_end'])){
            $date_end = new \DateTime($this->_interval['_date_interval_end']);
        }

        return $this->getFormatWeek($date_start, $date_end);
    }



    private function getTitleMonth($date){
        $month = (integer)(date('m', strtotime($date))) - 1;
        $month = $this->_data_p['monthNames']['wide_2'][$month];
        
        return $month . ' ' . date('Y', strtotime($date)); 
    }
    


    private function getTitleQuarter($date){
        $q = \DateTimeOperations::getQuarter($date);
        
        return 'Q' . $q .' ' . date('Y', strtotime($date)); 
    }
    


    private function getTitleYear($date){
        return date('Y', strtotime($date . '-01-01')); 
    }

    
}
