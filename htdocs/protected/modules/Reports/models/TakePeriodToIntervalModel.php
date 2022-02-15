<?php
/**
 * TakePeriodToIntervalModel
 *
 * @author Alex R.
 */

namespace Reports\models;

class TakePeriodToIntervalModel {

    private $_data_setting;

    private $_interval;
    private $_period;
    private $_append_period;



    public function setDataSetting($data_setting){
        $this->_data_setting = $data_setting;
        return $this;
    }


    public function getDataSetting(){
        return $this->_data_setting;
    }


    public function getAppendPeriod(){
        return $this->_append_period;
    }



    public function run(){
        $this->_interval = $this->_data_setting['filters'];

        if($this->checkDate()){
            $this->addPeriod();
        }

        return $this;
    }


    /**
     * addPeriod
     */
    public function addPeriod(){
        switch($this->_data_setting['indicators'][0]['period']){
            case 'day':
            case 'all_period':
                $this->takeDay();
                break;
            case 'week':
                $this->takeWeek();
                break;
            case 'month':
                $this->takeMonth();
                break;
            case 'quarter':
                $this->takeQuarter();
                break;
            case 'year':
                $this->takeYear();
                break;
        }
    }




    private function takeDay(){
        $date = new \DateTime($this->_interval['_date_interval_start']);
        $date->modify('-1 day');

        $this->_data_setting['filters']['_date_interval_start'] = $date->format('Y-m-d 00:00:00');

        return $date->format('Y-m-d 00:00:00');
    }



    private function takeWeek(){
        $date = new \DateTime($this->_interval['_date_interval_start']);
        $date->modify('-7 days');
        $begin = \DateTimeOperations::getFirstDateWeek($date->format('Y-m-d'));
        $begin = new \DateTime($begin);
        $end = clone $begin;
        $end->modify('+6 days');

        $this->_data_setting['filters']['_date_interval_start'] = $begin->format('Y-m-d');
        $this->_data_setting['filters']['_date_interval_end'] = $end->format('Y-m-d');

        return array(
            '_date_interval_start' => $begin->format('Y-m-d'),
            '_date_interval_end' => $end->format('Y-m-d'),
        );
    }




    private function takeMonth(){
        $date = $this->takeMonthFromDate($this->_interval['_date_interval_start']);
        $begin = new \DateTime(date('Y-m-01', strtotime($date)));
        $end = clone $begin;
        $end->modify('+1 month -1 day');

        $this->_data_setting['filters']['_date_interval_start'] = $begin->format('Y-m-d');
        $this->_data_setting['filters']['_date_interval_end'] = $end->format('Y-m-d');

        return array(
            '_date_interval_start' => $begin->format('Y-m-d'),
            '_date_interval_end' => $end->format('Y-m-d'),
        );
    }


    /**
     * Отнимает от даты месяц
     * @param int $count
     * @param null $date_start
     * @return bool|null|string
     */
    private function takeMonthFromDate($date_start, $count = 1){
        $date_start_info_base = getdate(strtotime($date_start));
        $days_base = date('t', strtotime($date_start));

        $i = 0;
        while($i < $count){
            $date_start_info = getdate(strtotime($date_start));
            $days = date('t', strtotime($date_start));
            $date = new \DateTime(date('Y-m-' . $days, strtotime($date_start)));
            $date->modify('- '.$days.' days');
            $date_start = $date->format('Y-m-d 00:00:00');
            $days_new = date('t', strtotime($date_start));
            $i++;

            if((integer)$days_base == (integer)$date_start_info_base['mday']){ // если конец месяца
                $date_start = date('Y-m-d 00:00:00', strtotime(date('Y-m-', strtotime($date_start)) . $days_new));
            } else {
                $mday = $date_start_info_base['mday'];
                $ndays = date('t', strtotime($date_start));

                if((integer)$mday > (integer)$ndays){
                    $mday = $ndays;
                }
                $date_start = date('Y-m-d 00:00:00', strtotime(date('Y-m-', strtotime($date_start)) . $mday));
            }
        }

        return $date_start;
    }




    private function takeQuarter($count = 1){
        $count *= 3;
        $begin = $this->takeMonthFromDate($this->_interval['_date_interval_start'], $count);
        $qurter_month = \DateTimeOperations::getQuarterFirstMonth(\DateTimeOperations::getQuarter($begin));
        $begin = new \DateTime(date('Y-'.$qurter_month.'-01', strtotime($begin)));
        $end = clone $begin;
        $end->modify('+3 month -1 day');

        $this->_data_setting['filters']['_date_interval_start'] = $begin->format('Y-m-d');
        $this->_data_setting['filters']['_date_interval_end'] = $end->format('Y-m-d');

        return array(
            '_date_interval_start' => $begin->format('Y-m-d'),
            '_date_interval_end' => $end->format('Y-m-d'),
        );
    }




    private function takeYear(){
        $begin = new \DateTime(date('Y-01-01', strtotime($this->_interval['_date_interval_start'])));
        $begin->modify('-1 year');
        $end = clone $begin;
        $end->modify('+1 year -1 day');

        $this->_data_setting['filters']['_date_interval_start'] = $begin->format('Y-m-d');
        $this->_data_setting['filters']['_date_interval_end'] = $end->format('Y-m-d');

        return array(
            '_date_interval_start' => $begin->format('Y-m-d'),
            '_date_interval_end' => $end->format('Y-m-d'),
        );
    }





    /**
     * Отнимает от даты 1 год (не используется)
     */
    private function takeYearFromDate($date_start){
        $date_start_info = getdate(strtotime($date_start));
        $days = date('t', strtotime($date_start));

        $date_new = new \DateTime(date('Y-m-d', strtotime(date('Y-m-01', strtotime($date_start)))));
        $date_new->modify('-1 year');

        if((integer)$days == (integer)$date_start_info['mday']){ // если конец месяца
            $ndays = date('t', strtotime($date_new->format('Y-m-d')))-1;
            $date_new->modify('+' . $ndays . ' days');
            $date_new = $date_new->format('Y-m-d 00:00:00');
        } else {
            $mday = $date_start_info['mday'];
            $ndays = date('t', strtotime($date_new->format('Y-m-d')));

            if((integer)$mday > (integer)$ndays){
                $mday = $ndays;
            }

            $date_new = date('Y-m-d 00:00:00', strtotime(date('Y-m-', strtotime($date_new->format('Y-m-d'))) . $mday));
        }

        $this->_data_setting['filters']['_date_interval_start'] = $date_new;
    }








    /**
     * checkDate
     */
    private function checkDate(){
        $append_period = false;
        $count = false;
        switch($this->_data_setting['indicators'][0]['period']){
            case 'day':
            case 'all_period':
                $count = $this->countDays();
                break;
            case 'week':
                $count = $this->countWeek();
                break;
            case 'month':
                $count = $this->countMonth();
                break;
            case 'quarter':
                $count = $this->countQuarter();
                break;
            case 'year':
                $count = $this->countYear();
                break;
        }
        if($count !== false && $count <= 1) $append_period = true;

        $this->_append_period = $append_period;

        return $append_period;
    }




    /**
     * countDays
     */
    private function countDays(){
        $begin = new \DateTime($this->_interval['_date_interval_start']);
        $end = new \DateTime($this->_interval['_date_interval_end']);
        $interval = $end->diff($begin);
        $a = $interval->format('%a');
        if(is_numeric($a))
            $a = (integer)$a+1;
        else
            $a = false;

        return $a;
    }





    /**
     * countWeek
     */
    private function countWeek(){
        $begin =  \DateTimeOperations::getFirstDateWeek($this->_interval['_date_interval_start'], 1);
        $begin =  new \DateTime($begin);
        if(strtotime($begin->format('Y-m-d')) > strtotime($this->_interval['_date_interval_start'])){
            $begin->modify('-7 days');
        }
        $end = new \DateTime($this->_interval['_date_interval_end']);

        $lich = 0;
        while(true){
            $begin->modify('+ 7 days');
            if(strtotime($begin->format('Y-m-d')) > strtotime($end->format('Y-m-d'))){
                break;
            }
            $lich++;
        }
        $lich++;
        return $lich;
    }







    /**
     * countMonth
     */
    private function countMonth(){
        $begin = new \DateTime(date('Y-m-01', strtotime($this->_interval['_date_interval_start'])));
        $end = new \DateTime(date('Y-m-01', strtotime($this->_interval['_date_interval_end'])));
        $interval = $end->diff($begin);
        $a = $interval->format('%m');
        $y = $interval->format('%y');

        if(!is_numeric($a) || !is_numeric($y)){
            $a = false;
        } else if((integer)$a === 0 && (integer)$y === 0){
            $a = 1;
        } elseif((integer)$a === 0 && (integer)$y > 0){
            $a = 12 * $y;
        } elseif((integer)$a > 0 && (integer)$y === 0){
            $a = $a+1;
        } elseif((integer)$a > 0 && (integer)$y > 0){
            $a = ($y * 12) + $a;
        }

        return (integer)$a;
    }






    /**
     * countQuarter
     */
    private function countQuarter(){
        $quarter_fm = \DateTimeOperations::getQuarterFirstMonth(\DateTimeOperations::getQuarter($this->_interval['_date_interval_start']));
        $begin = new \DateTime(date('Y-'.$quarter_fm.'-01', strtotime($this->_interval['_date_interval_start'])));
        $end = new \DateTime(date('Y-m-d', strtotime($this->_interval['_date_interval_end'])));

        $lich = 0;
        while(true){
            $lich++;
            $begin->modify('+3 month -1 day');

            if(strtotime($end->format('Y-m-d')) <= strtotime($begin->format('Y-m-d'))){
                break;
            }

            $begin->modify('+1 day');
        }
        return $lich;
    }



    /**
     * countYear
     */
    private function countYear(){
        $y1 = date('Y', strtotime($this->_interval['_date_interval_start']));
        $y2 = date('Y', strtotime($this->_interval['_date_interval_end']));
        if(!is_numeric($y1) || !is_numeric($y2)){
            $a = false;
        } else{
            $a = ((integer)$y2 - (integer)$y1) + 1;
        }

        return $a;
    }










}
