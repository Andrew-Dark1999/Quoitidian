<?php
/**
 * @author Alex R.
 */

class ConsoleStartTimeModel extends \ActiveRecord
{

    const ELEMENT_START_ON_TIME     = 'start_on_time';                  //Запуск по времени
    const ELEMENT_PERIODICITY       = 'periodicity';                    //При регулярном - периодичность

    // для ELEMENT_START_ON_TIME
    const START_ON_TIME_DISABLED    = 'start_on_time_disabled';         // отключен
    const START_ON_TIME_ONE         = 'start_on_time_disposable_start'; // один
    const START_ON_TIME_DETERMINED  = 'start_on_time_determined';       // через определенное время
    const START_ON_TIME_REGULAR     = 'start_on_time_regular_start';    // регулярный
    const START_ON_BEFORE_TIME      = 'start_on_before_time';           // до указанного времени
    const START_ON_AFTER_TIME       = 'start_on_after_time';            // после указаного времени

    // для ELEMENT_PERIODICITY
    const PERIODICITY_YEAR          = 'periodicity_year';
    const PERIODICITY_QUARTER       = 'periodicity_quarter';
    const PERIODICITY_MONTH         = 'periodicity_month';
    const PERIODICITY_WEEK          = 'periodicity_week';
    const PERIODICITY_DAY           = 'periodicity_day';


    const ELEMENT_DATE              = 'date';
    const ELEMENT_QUARTER           = 'quarter';
    const ELEMENT_DAY_IN_MONTH      = 'day_in_month';
    const ELEMENT_WEEK              = 'week';
    const ELEMENT_TIME              = 'time';
    const ELEMENT_SUB_TIME          = 'sub_time';
    const ELEMENT_HOUR              = 'hour';
    const ELEMENT_MINUTES           = 'minutes';
    const ELEMENT_DAYS              = 'days';


    public $tableName = 'console_start_time';

    private $_model;
    private $_params = array();



    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }



    private function setModel($model){
        $this->_model = $model;
        return $this;
    }


    /**
     * getStartTimeModels
     */
    private function getStartTimeModels($executor_id){
        return static::model()->find(array(
            'condition' => 'executor_id = :executor_id',
            'params' => array(
                ':executor_id' => $executor_id,
            ),
        ));
    }



    /**
     * Подготовка данных
     * array(
    'id' => null,
    'start_on_time' => '',
    'periodicity' => '',
    'dates' = array())
     */
    private function prepareParams(){
        if(empty($this->_model)){
            $this->_params = array();
        } else {
            $this->_params = array(
                'id' => $this->_model->id,
            );

            $schema = $this->_model->getSchema();
            foreach($schema as $row){
                if($row['type'] == self::ELEMENT_START_ON_TIME){
                    if($row['value'] == self::START_ON_TIME_DISABLED) continue;
                    $this->_params[$row['type']] = $row['value'];
                    foreach($row['elements'] as $element){
                        if($element['type'] == self::ELEMENT_PERIODICITY){
                            $this->_params[$element['type']] = $element['value'];
                        }
                    }
                    $this->_params['date_list'] = $this->getDateList($row['value'], $row['elements']);
                }
            }
        }
        return $this;
    }



    /**
     * getDateList
     */
    private function getDateList($start_on_time, $elements){
        $result = array();
        $result_tmp = array();

        foreach($elements as $element){
            if(in_array($start_on_time, array(self::START_ON_TIME_DETERMINED, self::START_ON_BEFORE_TIME, self::START_ON_AFTER_TIME))){
                if(in_array($element['type'], array(
                    self::ELEMENT_DAYS,
                    self::ELEMENT_MINUTES,
                    self::ELEMENT_HOUR))){
                    $result_tmp[$element['type']] = $element['value'];
                }
            } else
                if(in_array($start_on_time, array(self::START_ON_TIME_ONE, self::START_ON_TIME_REGULAR))){
                    if(in_array($element['type'], array(
                        self::ELEMENT_DATE,
                        self::ELEMENT_QUARTER,
                        self::ELEMENT_DAY_IN_MONTH,
                        self::ELEMENT_WEEK,
                        self::ELEMENT_TIME))){
                        $result[] = $element['value'];
                    }
                }
        }
        if(!empty($result_tmp)) $result[] = $result_tmp;

        return $result;
    }



    /**
     * startSchedule - выполнение расписаний по конкретному процессу
     */
    public function startSchedule($executor_id){
        $executor_model = ConsoleExecutorModel::model()->findByPk($executor_id);

        if($executor_model->start_on_time == false){
            $executor_model->status = 'disable';
            $executor_model->save();
            return false;
        }

        $start_model = $this->getStartTimeModels($executor_id);

        if(empty($start_model)){
            $start_model = new self();
            $start_model->executor_id = $executor_id;
            $start_model
                ->setModel($executor_model)
                ->prepareParams();

            $start_model->start_time = $start_model->getStartDate(date('Y-m-d H:i:00'), $start_model->getDateFromParams(0), false);
            $start_model->save();
        } else {
            $start_model
                ->setModel($executor_model)
                ->prepareParams();
        }

        $result = $start_model->runSchedule($start_model, $executor_model);

        return $result;
    }



   /**
     * runSchedule - проверяем и запускаем задание, пишем новое расписание
     */
    private function runSchedule($start_model, $executor_model){

        $result = $this->checkRunning($start_model->start_time);

        if($result){
            if($this->appendNewSchedule() == true){
                $start_model_n = static::model()->findByPk($start_model->id);
                $start_model_n->executor_id = $executor_model->id;
                $start_model_n->setModel($executor_model);
                $start_model_n->prepareParams();
                $start_model_n->start_time = $this->getStartDate(date('Y-m-d H:i:00'), $this->getDateFromParams(0), true);
                $start_model_n->save();
                $start_model_n->refresh();

                $this->runSchedule($start_model_n, $executor_model); //рекурсия
            } else {
                \DataModel::getInstance()->delete($this->tableName, 'id=' . $start_model->id);
                $executor_model->status = 'disabled';
                $executor_model->save();
            }
        }

        return $result;
    }



    /**
     * appendNewSchedule - проверяет, надо ли добавить новое расписание
     */
    private function appendNewSchedule(){
        $result = false;
        if(in_array($this->_params['start_on_time'], array(
                self::START_ON_TIME_DETERMINED,
                self::START_ON_TIME_REGULAR,
            ))){
            $result = true;
        }
        return $result;
    }



    /**
     * getDateFromParams - возвращает дату из схемы по индексу
     */
    private function getDateFromParams($date_index = null){
        if (isset($this->_params['date_list'])) {
            return $this->_params['date_list'][$date_index];
        } else {
            return false;
        }
    }


    /**
     * checkRunning - проверка выполнения
     */
    private function checkRunning($start_date_db){
        $running = false;

        if($start_date_db == false) return false;

        $date_now = strtotime(date('Y-m-d H:i:s'));

        // для отладки
        $dt = \Yii::app()->request->getParam('dt');
        if(!empty($dt)){
            $date_now = strtotime($dt, strtotime(date('Y-m-d H:i:s')));
        }

        switch($this->_params['start_on_time']){
            case self::START_ON_TIME_ONE :
            case self::START_ON_TIME_REGULAR :
            case self::START_ON_TIME_DETERMINED :
            case self::START_ON_BEFORE_TIME :
            case self::START_ON_AFTER_TIME :

                if(strtotime($start_date_db) <= $date_now){
                    $running = true;
                }
                break;
        }
        return $running;
    }



    private function getPreparedDate($date_schema){
        if(is_array($date_schema) && !empty($date_schema[0]) && !empty($date_schema[1])){
            $date_schema = date('Y-m-d H:i:s', strtotime($date_schema[0] . ' ' . $date_schema[1]));
        } elseif(is_array($date_schema) && empty($date_schema[0]) && !empty($date_schema[1])){
            $date_schema = date('Y-m-d ' . $date_schema[1]);
        } elseif(is_array($date_schema) && !empty($date_schema[0]) && empty($date_schema[1])){
            $date_schema = date($date_schema[0] . ' 00:00:00');
        } elseif(is_array($date_schema) && empty($date_schema[0]) && empty($date_schema[1])){
            $date_schema = date('Y-m-d 00:00:00');
        } elseif(!is_array($date_schema) && !empty($date_schema)){
            $date_schema = date('Y-m-d H:i:s', strtotime($date_schema));
        } elseif(!is_array($date_schema) && empty($date_schema)){
            $date_schema = date('Y-m-d 00:00:00');
        }
        return $date_schema;
    }



    /**
     * getStartDate - возвращает дату на основании параметров расписания
     */
    private function getStartDate($date_base, $date_schema, $extend_period = false){
        $date = null;

        switch($this->_params['start_on_time']){
            // Единоразовый запуск
            case self::START_ON_TIME_ONE :
                $date_schema = $this->getPreparedDate($date_schema);
                $date_schema = new \DateTime($date_schema);
                $date = $date_schema->format('Y-m-d H:i:s');
                break;

            // Регулярный запуск
            case self::START_ON_TIME_REGULAR :
                $date = $this->getStartDateRegular($date_base, $date_schema, $extend_period);
                break;

            // Запуск через определенное время, запуск после
            case self::START_ON_TIME_DETERMINED :
            case self::START_ON_AFTER_TIME :
                $date = $this->getStartDateDeterminated($date_base, $date_schema, '+');
                break;

            // Запуск до
            case self::START_ON_BEFORE_TIME :
                $date = $this->getStartDateDeterminated($date_base, $date_schema, '-');
                break;
        }
        return $date;
    }



    private function getStartDateRegular($date_base, $date_schema, $extend_period = false){
        $date_base = new \DateTime($date_base);
        $date = null;

        switch($this->_params[self::ELEMENT_PERIODICITY]){
            case self::PERIODICITY_YEAR :
                $date_schema = $this->getPreparedDate($date_schema);
                $date_schema = new \DateTime($date_schema);
                if($extend_period == false){ // при сохранении из оператора
                    $date_base = clone $date_schema;
                }

                $date = new \DateTime($date_base->format('Y') . $date_schema->format('-m-d H:i:s'));
                if($extend_period) $date->modify('+1 year');
                $date = $date->format('Y-m-d H:i:s');
                break;

            case self::PERIODICITY_QUARTER :
                $month = \DateTimeOperations::getQuarterFirstMonth($date_schema[0]);
                $date_schema[0] = $date_base->format('Y').'-'.$month.'-01';
                $date_schema = $this->getPreparedDate($date_schema);
                $date = new \DateTime($date_schema);

                if($extend_period){
                    $date->modify('+1 year');
                } else {
                    $month_now =  \DateTimeOperations::getQuarterFirstMonth(\DateTimeOperations::getQuarter(date('Y-m-d')));
                    if((int)$month < (int)$month_now){
                        $date->modify('+1 year');
                    }
                }

                $date = $date->format('Y-m-d H:i:s');
                break;

            case self::PERIODICITY_MONTH :
                $day = $date_schema[0];

                if(checkdate($date_base->format('m'), $day, $date_base->format('Y'))){
                    $date_schema[0] = $date_base->format('Y-m-') . $day;
                    $date_schema = $this->getPreparedDate($date_schema);
                    $date = new \DateTime($date_schema);
                } else {
                    $date_schema[0] = $date_base->format('Y-m-01');
                    $date_schema = $this->getPreparedDate($date_schema);
                    $date = new \DateTime($date_schema);
                    $date = \DateTimeOperations::getAddMonth($date, 1, $day);
                }

                if(strtotime($date->format('Y-m-d H:i:s')) <= strtotime($date_base->format('Y-m-d H:i:s'))){
                    $date = \DateTimeOperations::getAddMonth($date, 1, $day);
                    $date = $date->format('Y-m-d H:i:s');
                } else {
                    $date = $date->format('Y-m-d H:i:s');
                }

                break;

            case self::PERIODICITY_WEEK :
                $date = clone $date_base;
                $date = $this->setNextDateWeek($date, $date_schema[0]);
                $date_schema[0] = $date->format('Y-m-d');;
                $date_schema = $this->getPreparedDate($date_schema);

                $date = new \DateTime($date_schema);
                if(strtotime($date->format('Y-m-d H:i:s')) > strtotime($date_base->format('Y-m-d H:i:s'))){
                    $date_interval = $date->diff($date_base);
                    if($date_interval->y || $date_interval->m || (integer)$date_interval->d >=7){
                        $date->modify('-7 days');
                    }
                }

                if($extend_period){
                    $date->modify('+7 days');
                }

                $date = $date->format('Y-m-d H:i:s');

                break;

            case self::PERIODICITY_DAY :
                $time = $date_schema;
                $date = new \DateTime($date_base->format('Y-m-d') . ' ' . $time);
                if($extend_period){
                    $date->modify('+1 day');
                } else {
                    if(strtotime($date->format('Y-m-d H:i:s')) <= strtotime($date_base->format('Y-m-d H:i:s'))){
                        $date->modify('+1 day');
                    }
                }
                $date = $date->format('Y-m-d H:i:s');
                break;
        }

        return $date;
    }



    /**
     * увеличивает дату к нужной недели
     */
    private function setNextDateWeek($date, $week){
        $date->modify('+1 day');

        if((integer)$date->format('w') !== (integer)$week){
            $date = $this->setNextDateWeek($date, $week);
        }
        return $date;
    }



    private function getStartDateDeterminated($date_base, $date_schema, $condition = '+'){
        $date_base = new \DateTime($date_base);

        $days = $date_schema[self::ELEMENT_DAYS];
        $hours = $date_schema[self::ELEMENT_HOUR];
        $minutes = $date_schema[self::ELEMENT_MINUTES];


        if(!empty($days))
            $date_base->modify($condition . $days . ' days');
        if(!empty($hours))
            $date_base->modify($condition . $hours . ' hours');
        if(!empty($minutes))
            $date_base->modify($condition . $minutes . ' minutes');

        return $date_base->format('Y-m-d H:i:s');
    }


}

