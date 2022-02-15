<?php


class DateTimeOperations {
    
    
    /**
     * Проверка дат на равенство
     */
    public static function isDateEquality($date1, $date2){
        if(!($date1 instanceof DateTime)) $date1 = date_create(date('Y-m-d 00:00:00', strtotime($date1)));
        if(!($date2 instanceof DateTime)) $date2 = date_create(date('Y-m-d 00:00:00', strtotime($date2)));
        
        $interval = date_diff($date1, $date2);
        if($interval && $interval->d === 0) return true;
        return false; 
    }


    /**
     * Возвращает разницу воличества дней между двумя датами
     * @param $date1
     * @param $date2
     */
    public static function dateDiffAsDays($date1, $date2){
        if(!$date1 || !$date2){
            return false;
        }

        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);

        return $interval->days;
    }


    /**
     * Сравнение дат на равенство
     * return: 0 - равны, -1 - первая менше второй, 1 - первая больше второй   
     */
    public static function dateDiff($date1, $date2){
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $result = null;
        
        if($date1 == $date2) $result = 0;
        elseif($date1 < $date2) $result = -1;
        elseif($date1 > $date2) $result = 1;
        
        return $result; 
    } 

    
    /**
     *  переводит дату в строковое представление
     */
    public static function getFullDateStr($date, $add_time = false, $add_year = true, $add_seconds = true){
        if(empty($date)) return;
        
        if(!($date instanceof DateTime)) $date = getdate(strtotime($date));

        $params = array(
                        'd' => (strlen($date['mday']) == 1 ? '0' . $date['mday'] : $date['mday']),
                        'y' => substr($date['year'], -2, 2),
                        'Y' => $date['year'],
                        'mmmm' => LocaleCRM::getInstance2()->getMonthName($date['mon'] - 1),
                        'm' => LocaleCRM::getInstance2()->getMonthName($date['mon'] - 1, 'number'),
                        'MMMM' => LocaleCRM::getInstance2()->getMonthName($date['mon'] - 1, 'wide', true),
                        'H' => (strlen($date['hours']) == 1 ? '0' . $date['hours'] : $date['hours']),
                        'i' => (strlen($date['minutes']) == 1 ? '0' . $date['minutes'] : $date['minutes']),
                        's' => (strlen($date['seconds']) == 1 ? '0' . $date['seconds'] : $date['seconds']),
                    );

        if(!$add_time){
            $result = LocaleCRM::getInstance2()->_data_p['dateFormats']['medium'];
        } else {
            if($add_seconds){
                $result = LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['medium'];
            } else {
                $result = LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['medium_short'];
            }
        }
            
        
        foreach($params as $key => $value){
            if($add_year == false && ($key == 'y' || $key == 'Y')) $value = '';

            $result = str_replace($key, $value, $result);    
        }
        
        return $result;
    }


    /**
     * getDaysOfMonth - возвращает количетсво дней в месяце
     * return string
     */
    public static function getDaysOfMonth($date){
        if(!($date instanceof \DateTime)){
            $date = new \DateTime($date);
        }

        $date_tmp = new \DateTime($date->format('Y-m-01 H:i:s'));
        $date_tmp->modify('+1 month');
        $date_tmp->modify('-1 day');
        $days = $date_tmp->format('d');
        $days = $days;
        return $days;

    }


    /**
     * getAddMonth - добавляет месяцы к дате
     * @param str|DateTime getAddMonth - базовая дата
     * @param string $count_month - кол. месяцев, что необходимо прибавить
     * @param string $end_day - установить последнее число в месяце
     * return DateTime
     */
    public static function getAddMonth($date, $count_month, $end_day = null){
        if(!($date instanceof \DateTime)){
            $date = new \DateTime($date);
        }

        $day = $date->format('d');
        if($end_day !== null) $day = $end_day;

        $date_tmp = new \DateTime($date->format('Y-m-01 H:i:s'));
        $date_tmp->modify('+' . $count_month . ' month');

        if(checkdate($date_tmp->format('m'), $day, $date_tmp->format('Y'))){
            $date_tmp = new \DateTime($date_tmp->format('Y-m-'.$day.' H:i:s'));
        } else {
            $day = self::getDaysOfMonth($date_tmp);
            $date_tmp = new \DateTime($date_tmp->format('Y-m-'.$day.' H:i:s'));
        }

        return $date_tmp;
    }


    public static function getQuarter($date_str){
        $month = date('m', strtotime($date_str));
        $quarter = null;
        switch($month){
            case '01':
            case '02':
            case '03':
                $quarter = 1; break;
            case '04':
            case '05':
            case '06':
                $quarter = 2; break;
            case '07':
            case '08':
            case '09':
                $quarter = 3; break;
            case '10':
            case '11':
            case '12':
                $quarter = 4; break;
        }
        return $quarter;
    }


    public static function getQuarterFirstMonth($quarter){
        $month = 0;
        switch($quarter){
            case 1:
                $month = '01'; break;
            case 2:
                $month = '04'; break;
            case 3:
                $month = '07'; break;
            case 4:
                $month = '10'; break;
        }
        return $month;
    }


    /**
     * Дата первой недели в году
     * @param $date
     * @param int $week
     * @param null $add_time
     * @return bool|string
     */
    public static function getFirstDateWeekInYear($date, $week = 1,  $add_time = null){
        if(!strtotime($date)) return false;
        if(!in_array($week, array(0,1,2,3,4,5,6))) return false; //monday
        if($week === 0) $week = 7;

        $format = 'Y-m-d' . ($add_time !== null ? ' ' . $add_time : '');

        $date_time = new \DateTime($date);

        $date_f = new \DateTime(date('Y-m-d', strtotime($date_time->format('Y-01-01'))));
        $week_f = (integer)$date_f->format('w');
        if($week_f == 0) $week_f = 7;

        if($week == $week_f){
            return $date_f->format($format);
        } elseif($week > $week_f){
            $days = $week - $week_f;
        } elseif($week < $week_f){
            $days = 7 - $week_f + $week;
        }

        $date_f->modify('+'.$days.' days');
        return $date_f->format($format);
    }


    /**
     * Количество недель в году
     * @param $date
     * @param int $week
     * @return bool|int
     */
    public static function getCountWeekInYear($date, $week = 1){
        if(!strtotime($date)) return false;
        if(!in_array($week, array(0,1,2,3,4,5,6))) return false; //monday

        $date_steep = self::getFirstDateWeekInYear($date, $week);
        $date_steep = new \DateTime($date_steep);

        $date_end = clone $date_steep;
        $date_end->modify('+1 year');
        $date_end = self::getFirstDateWeekInYear($date_end->format('Y-m-d'), $week);
        $date_end = new \DateTime($date_end);

        $lich = 0;
        while(true){
            $date_steep->modify('+7 days');
            if(strtotime($date_steep->format('Y-m-d')) >= strtotime($date_end->format('Y-m-d'))){
                break;
            }
            $lich++;
        }
        $lich++;

        return $lich;
    }


    /**
     * Определяет, является ли первым первое число недели в году
     * @param $date
     * @param int $week
     * @return bool
     */
    public static function thisFirstNumberWeekInYear($date, $week = 1){
        $result = false;

        if(!strtotime($date)) return $result;
        if(!in_array($week, array(0,1,2,3,4,5,6))) return $result; //monday
        if($week === 0) $week = 7;

        $date_time = new \DateTime($date);

        $date_f = new \DateTime(date('Y-m-d', strtotime($date_time->format('Y-01-01'))));
        $week_f = (integer)$date_f->format('w');
        if($week_f == 0) $week_f = 7;

        if($week == $week_f){
            $result = true;
        }

        return $result;
    }


    /**
     * Первое число недели
     * @param $date
     * @param int $week
     * @return bool|string
     */
    public static function getFirstDateWeek($date, $week = 1){
        if(!strtotime($date)) return false;
        if(!in_array($week, array(0,1,2,3,4,5,6))) return false; //monday

        $date = new \DateTime($date);
        $date_first = self::getFirstDateWeekInYear($date->format('Y-m-d'), $week);
        if($date_first === false) return false;
        $date_first = new \DateTime($date_first);

        while(true){
            $date_first->modify('+7 days');
            if(strtotime($date_first->format('Y-m-d')) > strtotime($date->format('Y-m-d'))){
                $date_first->modify('-7 days');
                return $date_first->format('Y-m-d');
            } elseif(strtotime($date_first->format('Y-m-d')) == strtotime($date->format('Y-m-d'))){
                return $date_first->format('Y-m-d');
            }
        }

    }


    /**
     * Первое число недели
     * @param $date
     * @param int $week
     * @return bool|string
     */
    public static function getFirstDateWeekByYearWeek($yearweek, $week = 1){
        if(empty($yearweek)) return false;
        if(!in_array($week, array(0,1,2,3,4,5,6))) return false;

        $year = substr($yearweek, 0, 4);
        $week_count = (integer)substr($yearweek, 4, 2);

        $date_first = self::getFirstDateWeekInYear($year. '-01-01', $week);
        if($date_first === false) return false;
        $date_first = new \DateTime($date_first);

        $lich = 0;
        while(true){
            $lich++;
            if($lich == $week_count){
                return $date_first->format('Y-m-d');
            }
            $date_first->modify('+7 days');
        }

    }


    /**
     * Номер недели в году
     * @param $date
     * @param int $week
     * @return bool|string
     */
    public static function getWeekNumber($date_end, $week = 1){
        if(!strtotime($date_end)) return false;
        $date_end = new \DateTime($date_end);
        $date_first = self::getFirstDateWeekInYear($date_end->format('Y-m-d'), $week);
        if($date_first === false) return false;
        $date_first = new \DateTime($date_first);
        $lich = 0;

        while(true){
            $lich++;
            $date_first->modify('+7 days');
            if(strtotime($date_first->format('Y-m-d')) > strtotime($date_end->format('Y-m-d'))){
                break;
            }
        }
        return $lich;
    }


    /**
     *  Возвращает прописью количество пройденых часов/минут/дней/лет
     */
    public static function getDateTimeOldStr($datetime, $add_ending_str = true){
        $datetime1 = new DateTime($datetime);
        $datetime2 = new DateTime();
        $interval = $datetime1->diff($datetime2);
        $date_str = array();
        $just_now = false;
        //year
        if($interval->y){
            //'год-1|года-2'
            if($interval->y === 1) $date_str[] = $interval->y . ' ' . Yii::t('datetime', 'year', 1);
            if($interval->y > 1 && $interval->y <= 4) $date_str[] = $interval->y . ' ' . Yii::t('datetime', 'year', 2);
            if($interval->y >= 5) $date_str[] = $interval->y . ' ' . Yii::t('datetime', 'years');
        }
        //month
        if($interval->m){
            //'месяц-1|месяца-2'
            if($interval->m === 1) $date_str[] = $interval->m . ' ' . Yii::t('datetime', 'month', 1);
            if($interval->m > 1 && $interval->m <= 4) $date_str[] = $interval->m . ' ' . Yii::t('datetime', 'month', 2);
            if($interval->m >= 5) $date_str[] = $interval->m . ' ' . Yii::t('datetime', 'months');
        }
        //day
        if($interval->d){
            //'день-1|дня-2 ',
            if($interval->m > 0){
                if($interval->days <= 365){
                    $interval->d = $interval->days;
                    unset($date_str[count($date_str)-1]);
                } else {
                    $interval->d = ($interval->days - ($interval->y * 365));
                    unset($date_str[count($date_str)-1]);
                }
            }
            if($interval->d === 1) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'day', 1);
            elseif($interval->d > 1 && $interval->d <= 4) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'day', 2);
            elseif($interval->d >= 5 && $interval->d <=20) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'days');
            elseif($interval->d >= 21){
                $mod = $interval->d%10;
                if($mod === 0) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'days');
                elseif($mod === 1) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'day', 1);
                elseif($mod > 1 && $mod <= 4) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'day', 2);
                elseif($mod >= 5) $date_str[] = $interval->d . ' ' . Yii::t('datetime', 'days');
            }
        }
        //hover
        if($interval->h)
        if(!($interval->y+$interval->m+$interval->d)){
            //'час - 1|часа - 2',
            if($interval->h === 1) $date_str[] = $interval->h . ' ' . Yii::t('datetime', 'hour', 1);
            if($interval->h > 1 && $interval->h <= 4) $date_str[] = $interval->h . ' ' . Yii::t('datetime', 'hour', 2);
            if($interval->h >= 5) $date_str[] = $interval->h . ' ' . Yii::t('datetime', 'hours');
        }
        //minute
        if(!($interval->y+$interval->m+$interval->d+$interval->h)){
            // минута - 1|минуты - 2
            if($interval->i === 1) $date_str[] = $interval->i . ' ' . Yii::t('datetime', 'minute', 1);
            elseif($interval->i > 1 && $interval->i <= 4) $date_str[] = $interval->i . ' '. Yii::t('datetime', 'minute', 2);
            elseif($interval->i >= 5 && $interval->i <= 20) $date_str[] = $interval->i . ' ' . Yii::t('datetime', 'minutes');
            elseif($interval->i >= 21){
                $mod = $interval->i%10;
                if($mod === 0) $date_str[] = $interval->i . ' ' . Yii::t('datetime', 'minutes');
                elseif($mod === 1) $date_str[] = $interval->i . ' ' .  Yii::t('datetime', 'minute', 1);
                elseif($mod > 1 && $mod <= 4) $date_str[] = $interval->i . ' ' . Yii::t('datetime', 'minute', 2);
                elseif($mod >= 5) $date_str[] = $interval->i . ' ' . Yii::t('datetime', 'minutes');
            }
            else {
                $just_now = true;
                $date_str[] = Yii::t('datetime', 'just now');
            }
        }

        $date_str = implode(' ', $date_str);

        if($add_ending_str && $just_now == false)
            $date_str  .= ' ' . Yii::t('base', 'back');

        return $date_str;
    }


    /**
     * Список недель
     * @return array
     */
    public static function getWeeks(){
        $result = array();

        $locale_data = LocaleCRM::getInstance2()->getAllData();
        foreach($locale_data['weekDayNamesSA']['wide'] as $week_index => $week_title){
            $result[(string)$week_index] = $week_title;
        }

        if(Yii::app()->getLanguage() != 'en'){
            unset($result['0']);
            $result[0] = $locale_data['weekDayNamesSA']['wide'][0];
        }

        return $result;
    }




    /**
     * getDateToSqlSearch - поздготавиливает и возвращает дату для поиска sql запросом
     * @param $date_str
     * @return false|string|void
     */
    public static function getDateToSqlSearch($date_str){
        if($date_str == false) return $date_str;

        if(strlen($date_str) == 8){
            return date('Y-m-d', strtotime($date_str));
        } else
        if(strlen($date_str) == 17){
            return date('Y-m-d H:i:s', strtotime($date_str));
        }

        $date_str_list = explode(' ', $date_str);
        $result = [];

        foreach($date_str_list as $value){
            $t = [];
            preg_match('~[/.-]~', $value, $t);
            if($t){
                $date_p = explode($t[0], $value);
                $date_p = array_reverse($date_p);
                $result[] = implode('-', $date_p);
                continue;
            }

            $result[] = $value;
        }

        return implode(' ', $result);
    }


    /**
     * sortDateArray - Сортировка дат в массиве
     * @param array $date_list
     */
    public static function sortDateArray(array &$date_list){
        $function_sort = function($date1, $date2){
            if($date1 == $date2){
                return 0;
            }

            return (strtotime($date1) < strtotime($date2) ? -1 : 1);
        };

        usort($date_list, $function_sort);
    }




}
