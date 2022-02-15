<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class StartTimeModel extends \ActiveRecord{


    public $tableName = 'process_start_time';

    private $_operations_model;
    private $_operation_params = array();
    private $_run_result = array();
    private $_preparation = false; // включает режим проверки расписания без реальных действий ()
    private $_use_virtual_st_model = false;
    private $_virtual_models = array();



    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function setOperationsModel($operations_model){
        $this->_operations_model = $operations_model;
        return $this;
    }


    public function setPreparation($preparation){
        $this->_preparation = $preparation;
        return $this;
    }


    public function setUseVirtualStModel($use_virtual_st_model){
        $this->_use_virtual_st_model = $use_virtual_st_model;
        return $this;
    }


    /**
     * Подготовка данных
     * array(
            'process_id' => null,
            'operations_id' => null,
            'start_on_time' => '',
            'periodicity' => '',
            'dates' = array())
     */
    public function prepareOperationParams(){
        if(empty($this->_operations_model)){
            $this->_operation_params = array();
        } else {
            $this->_operation_params = array(
                'process_id' => $this->_operations_model->process_id,
                'operations_id' => $this->_operations_model->operations_id,
            );

            $schema = $this->_operations_model->getSchema();
            foreach($schema as $row){
                if($row['type'] == OperationBeginModel::ELEMENT_START_ON_TIME){
                    if($row['value'] == OperationBeginModel::START_ON_TIME_DISABLED) continue;
                    $this->_operation_params[$row['type']] = $row['value'];
                    foreach($row['elements'] as $element){
                        if($element['type'] == OperationBeginModel::ELEMENT_PERIODICITY){
                            $this->_operation_params[$element['type']] = $element['value'];
                        }
                    }

                    $this->_operation_params['date_list'] = $this->getDateList($row['value'], $row['elements']);
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
            if(in_array($start_on_time, array(OperationBeginModel::START_ON_TIME_DETERMINED, OperationBeginModel::START_ON_BEFORE_TIME, OperationBeginModel::START_ON_AFTER_TIME))){
                if(in_array($element['type'], array(
                                                OperationBeginModel::ELEMENT_DAYS,
                                                OperationBeginModel::ELEMENT_MINUTES,
                                                OperationBeginModel::ELEMENT_HOUR))){
                    $result_tmp[$element['type']] = $element['value'];
                }

            } else
            if(in_array($start_on_time, array(OperationBeginModel::START_ON_TIME_ONE, OperationBeginModel::START_ON_TIME_REGULAR))){
                if(in_array($element['type'], array(
                                                OperationBeginModel::ELEMENT_DATE,
                                                OperationBeginModel::ELEMENT_QUARTER,
                                                OperationBeginModel::ELEMENT_DAY_IN_MONTH,
                                                OperationBeginModel::ELEMENT_WEEK,
                                                OperationBeginModel::ELEMENT_TIME))){
                    $result[] = $element['value'];
                }
            }
        }

        if(!empty($result_tmp)) $result[] = $result_tmp;

        return $result;
    }




    /**
     * updateSchedule - обновляем расписание
     */
    public function updateSchedule(){
        $this->prepareOperationParams();

        if(empty($this->_operation_params)) return $this;

        $this->deleteOldSchedule();
        $this->insertNewSchedules();

        return $this;
    }




    public function getRunResult($prepare = true){
        if($prepare == false) return $this->_run_result;

        if(empty($this->_run_result) || in_array(false, $this->_run_result))
            return false;
        else
            return true;
    }




    /**
     * startSchedule - выполнение расписаний по конкретному процессу
     */
    public function startSchedule($return_result = true){
        if($this->_use_virtual_st_model == false){
            $this->prepareOperationParams();
        }

        $start_models = $this->getStartTimeModels($this->_operation_params['process_id'], $this->_operation_params['operations_id']);

        if(empty($start_models)){
            return ($return_result ? false : $this);
        }

        $date_index = 0;
        $this->_run_result = array();
        foreach($start_models as $start_model){
            $start_model
                ->setOperationsModel(OperationsModel::model()->find('process_id = ' . $this->_operation_params['process_id'] . ' AND operations_id = ' . $this->_operation_params['operations_id']))
                ->prepareOperationParams();

            $this->runSchedule($start_model, $date_index);
            $date_index++;
        }

        return ($return_result ? $this->getRunResult() : $this);
    }






    /**
     * startAllSchedules - выполнение расписаний по всем процессам.
     * Используется кронтабом
     */
    public function startAllSchedules(){
        $start_models = static::model()->findAll(array(
            'order' => 'start_date ASC',
        ));

        $log_attridute = array(
            'process_id' => null,
            'operations_id' => null,
            'date_create' => date('Y-m-d H:i:s'),
            'notation' => 'Start general test. Tasks count: ' . count($start_models),
        );
        \ProcessStartTimeLogModel::savelog($log_attridute);


        if(empty($start_models)) return;


        foreach($start_models as $start_model){
            ProcessModel::getInstance($start_model->process_id, true);
            $operations_models = OperationsModel::model()->findByPk($start_model->operations_id);

            (new StartTimeModel())->setOperationsModel($operations_models)->startSchedule();
        }


        $log_attridute = array(
            'process_id' => null,
            'operations_id' => null,
            'date_create' => date('Y-m-d H:i:s'),
            'notation' => 'Done',
        );
        \ProcessStartTimeLogModel::savelog($log_attridute);

    }







    /**
     * runSchedule - проверяем и запускаем задание, пишем новое расписание
     */
    private function runSchedule($start_model, $date_index){
        $result = false;

        $check = $this->checkRunning($start_model->getBaseStartDate($date_index));

        $log_attridute = array(
            'process_id' => $start_model['process_id'],
            'operations_id' => $start_model['operations_id'],
            'date_create' => date('Y-m-d H:i:s'),
            'notation' => 'Check',
        );

        if($check){
            $result = $this->runAction();

            if($result){
                $log_attridute['notation'].= '. ' . 'Running, date scheduled: ' . $start_model->getBaseStartDate($date_index);
            } else {
                $log_attridute['notation'].= '. ' . 'Running error, date scheduled: ' . $start_model->getBaseStartDate($date_index);
            }

            if($this->_preparation == false){
                if($this->appendNewSchedule() == true){
                    if($result){
                        $start_model_n = static::model()->findByPk($start_model->id);
                        $start_model_n->setOperationsModel(OperationsModel::model()->findByPk($start_model_n->operations_id));
                        $start_model_n->prepareOperationParams();
                        $date_base = $start_model->getBaseStartDate($date_index);
                        $start_model_n->start_date = $this->getStartDate($date_base, $this->getDateFromParams($date_index), true);
                        $start_model_n->save();
                        $start_model_n->refresh();

                        $log_attridute['notation'] .= '. New scheduled date: ' . $start_model_n->getBaseStartDate($date_index);
                        if(!empty($log_attridute)){
                            \ProcessStartTimeLogModel::savelog($log_attridute);
                            $log_attridute = array();
                        }

                        $this->runSchedule($start_model_n, $date_index); //рекурсия
                    }
                } else{
                    \DataModel::getInstance()->delete('{{' . $this->tableName . '}}', 'id=' . $start_model->id);
                    $this->unactiveSheduledInOperationBegin($start_model);
                }
            }

            $this->_run_result[] = $result;
        } else {
            $log_attridute['notation'].= '. Date ' . $start_model->getBaseStartDate($date_index) . ' has not come';
            $this->_run_result[] = $result;
        }

        if($this->_preparation == false && !empty($log_attridute)){
            \ProcessStartTimeLogModel::savelog($log_attridute);
        }

        return $result;
    }





    /**
     * unactiveSheduledInOperationBegin - удаляет из схемы расписание
     */
    private function unactiveSheduledInOperationBegin($start_model){
        $operations_model = OperationsModel::model()->findByPk($start_model['operations_id']);

        if(!$operations_model) return;
        if($operations_model->element_name != OperationsModel::ELEMENT_BEGIN) return;

        $operation_schema = $operations_model->getSchema(true);
        \Process\extensions\ElementMaster\Schema::getInstance()->unactiveSheduledInOperationBegin($operation_schema);

        $operations_model
            ->setSchema($operation_schema)
            ->save();
    }







    /**
     * getDateFromParams - возвращает дату из схемы по индексу
     */
    private function getDateFromParams($date_index){
        return $this->_operation_params['date_list'][$date_index];
    }





    /**
     * deleteOldSchedule - удаляем все засписания
     */
    private function deleteOldSchedule(){
        \DataModel::getInstance()
            ->delete('{{'.$this->tableName.'}}',
                'process_id=:process_id AND operations_id=:operations_id',
                array(
                    'process_id' => $this->_operation_params['process_id'],
                    'operations_id' => $this->_operation_params['operations_id'],
            ));
    }



    /**
     * insertNewSchedule - пишем новое расписание по всем параметрам
     */
    public function insertNewSchedules(){
        if(empty($this->_operation_params['date_list'])) return $this;

        foreach($this->_operation_params['date_list'] as $date_schema){
            if(in_array($this->_operation_params['start_on_time'], array(OperationBeginModel::START_ON_BEFORE_TIME, OperationBeginModel::START_ON_AFTER_TIME))){
                $start_date = null;
            } else {
                $date_base = date('Y-m-d H:i:s');
                $start_date = $this->getStartDate($date_base, $date_schema, false);
                if(empty($start_date)) continue;
            }

            if($this->_use_virtual_st_model){
                $this->insertScheduleInVirtualStModel($start_date);
            } else {
                $this->insertSchedule($start_date);
            }
        }

        return $this;
    }







    /**
     * insertScheduleInVirtualStModel - записываем новую дату в виртуальную модель
     */
    protected function insertScheduleInVirtualStModel($start_date){
        $model = new self();
        $model->process_id = $this->_operation_params['process_id'];
        $model->operations_id = $this->_operation_params['operations_id'];
        $model->start_date = $start_date;

        $this->_virtual_models[] = $model;
    }



    /**
     * insertSchedule - записываем новую дату
     */
    private function insertSchedule($start_date){
        \DataModel::getInstance()
            ->insert('{{'.$this->tableName.'}}', array(
                'process_id' => $this->_operation_params['process_id'],
                'operations_id' => $this->_operation_params['operations_id'],
                'start_date' => $start_date,
            ));
    }


    /**
     * appendNewSchedule - проверяет, надо ли добавить новое расписание
     */
    private function appendNewSchedule(){
        $result = false;
        if($this->_operations_model->element_name == OperationsModel::ELEMENT_BEGIN && in_array($this->_operation_params['start_on_time'], array(
                OperationBeginModel::START_ON_TIME_DETERMINED,
                OperationBeginModel::START_ON_TIME_REGULAR,
            ))){
            $result = true;
        }
        return $result;
    }


    /**
     * getBaseStartDate - возвращает базовую дату
     */
    private function getBaseStartDate($date_index = null){
        if($this->_operations_model){
            $operations_model = $this->_operations_model;
        } else {
            $operations_model = OperationsModel::model()->find('process_id = ' . $this->_operation_params['process_id'] . ' AND operations_id = ' . $this->_operation_params['operations_id']);
        }

        if(in_array($this->_operation_params['start_on_time'], array(OperationBeginModel::START_ON_BEFORE_TIME, OperationBeginModel::START_ON_AFTER_TIME))){
            $date_base = OperationsModel::getChildrenModel($operations_model->element_name)
                ->setOperationsModel($operations_model)
                ->getStartDate();
            if(!$date_base) return;
            $date_base = $this->getStartDate($date_base, $this->getDateFromParams($date_index));
            return $date_base;
        } else {
            return $this->start_date;
        }
    }


    /**
     * checkRunning - проверка выполнения
     */
    public function checkRunning($start_date_db){
        $running = false;

        if($start_date_db == false) return false;

        $date_now = strtotime(date('Y-m-d H:i:s'));

        // для отладки
        $dt = \Yii::app()->request->getParam('dt');
        if(!empty($dt)){
            $date_now = strtotime($dt, strtotime(date('Y-m-d H:i:s')));
        }

        switch($this->_operation_params['start_on_time']){
            case OperationBeginModel::START_ON_TIME_ONE :
            case OperationBeginModel::START_ON_TIME_REGULAR :
            case OperationBeginModel::START_ON_TIME_DETERMINED :
            case OperationBeginModel::START_ON_BEFORE_TIME :
            case OperationBeginModel::START_ON_AFTER_TIME :

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

        switch($this->_operation_params['start_on_time']){
            // Единоразовый запуск
            case OperationBeginModel::START_ON_TIME_ONE :
                $date_schema = $this->getPreparedDate($date_schema);
                $date_schema = new \DateTime($date_schema);
                $date = $date_schema->format('Y-m-d H:i:s');
                break;

            // Регулярный запуск
            case OperationBeginModel::START_ON_TIME_REGULAR :
                $date = $this->getStartDateRegular($date_base, $date_schema, $extend_period);
                break;

            // Запуск через определенное время, запуск после
            case OperationBeginModel::START_ON_TIME_DETERMINED :
            case OperationBeginModel::START_ON_AFTER_TIME :
                $date = $this->getStartDateDeterminated($date_base, $date_schema, '+');
                break;

            // Запуск до
            case OperationBeginModel::START_ON_BEFORE_TIME :
                $date = $this->getStartDateDeterminated($date_base, $date_schema, '-');
                break;
        }
        return $date;
    }


    private function getStartDateRegular($date_base, $date_schema, $extend_period = false){
        $date_base = new \DateTime($date_base);
        $date = null;

        switch($this->_operation_params[OperationBeginModel::ELEMENT_PERIODICITY]){
            case OperationBeginModel::PERIODICITY_YEAR :
                $date_schema = $this->getPreparedDate($date_schema);
                $date_schema = new \DateTime($date_schema);
                if($extend_period == false){ // при сохранении из оператора
                    $date_base = clone $date_schema;
                }

                $date = new \DateTime($date_base->format('Y') . $date_schema->format('-m-d H:i:s'));
                if($extend_period) $date->modify('+1 year');
                $date = $date->format('Y-m-d H:i:s');
                break;

            case OperationBeginModel::PERIODICITY_QUARTER :
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

            case OperationBeginModel::PERIODICITY_MONTH :
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

            case OperationBeginModel::PERIODICITY_WEEK :
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

            case OperationBeginModel::PERIODICITY_DAY :
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

        $days = $date_schema[OperationBeginModel::ELEMENT_DAYS];
        $hours = $date_schema[OperationBeginModel::ELEMENT_HOUR];
        $minutes = $date_schema[OperationBeginModel::ELEMENT_MINUTES];

        if(!empty($days))
            $date_base->modify($condition . $days . ' days');
        if(!empty($hours))
            $date_base->modify($condition . $hours . ' hours');
        if(!empty($minutes))
            $date_base->modify($condition . $minutes . ' minutes');

        return $date_base->format('Y-m-d H:i:s');
    }


    /**
     * runAction - исполняет действие
     */
    private function runAction(){
        if($this->_preparation == true) return true;

        switch($this->_operations_model->element_name){
            case OperationsModel::ELEMENT_BEGIN:
                return $this->runActionBegin();
                break;

            case OperationsModel::ELEMENT_TIMER:
                return $this->runActionTimer();

                break;
        }
    }


    /**
     * runActionBegin - исполняет действие для оператора Begin
     */
    private function runActionBegin(){
        switch($this->_operation_params['start_on_time']){
            case OperationBeginModel::START_ON_TIME_ONE :    // Единоразовый запуск
            case OperationBeginModel::START_ON_TIME_REGULAR :       // Регулярный запуск
                return $this->executeStartOnTimeRegular();
                break;
        }
    }


    /**
     * runActionBegin - исполняет действие для оператора Timer
     */
    private function runActionTimer(){
        // запись связей
        $process_id_old = ProcessModel::getInstance()->getProcessId();

        $process_model = \Process\models\ProcessModel::getInstance($this->_operations_model->process_id, true);
        $this->_operations_model->setStatus(OperationsModel::STATUS_DONE)->saveStatus();

        if($process_model->getMode() == ProcessModel::MODE_RUN){
            \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus();
        }

        \Process\models\ProcessModel::getInstance($process_id_old, true);

        return true;
    }


    /**
     * bpmParamsRun -
     */
    private function bpmParamsRun($process_id){
        $vars = array(
            'action' => BpmParamsModel::ACTION_CHECK,
            'process_id' => $process_id,
            'objects' => array(
                'participants' => null,
            )
        );
        (new \Process\models\BpmParamsModel())
                            ->setVars($vars)
                            ->setDeliveryMessages(true)
                            ->setHistorySetIsView(false)
                            ->validate()
                            ->run(true);
    }


    /**
     * executeStartOnTimeRegular - исполняет действие регулярного исполнения
     */
    private function executeStartOnTimeRegular(){
        // запись связей
        $process_id_old = ProcessModel::getInstance()->getProcessId();

        $process_id = $this->_operations_model->process_id;
        $process_model = \Process\models\ProcessModel::getInstance($process_id, true);

        if($process_model->getMode() == ProcessModel::MODE_RUN){
            // процесс небыл запущен - запускаем
            if($process_model->getBStatus() != ProcessModel::B_STATUS_IN_WORK && $process_model->getBStatus() != ProcessModel::B_STATUS_TERMINATED){
                ProcessModel::getInstance($process_id_old, true);
                ProcessModel::runAlertProcess($process_id);
                return true;
            // процесс был запущен - берем его шаблон
            } else {
                $process_id = ProcessModel::findProcessIdTemplate($this->_operations_model->process_id);
            }
        }

        if(empty($process_id)){
            ProcessModel::getInstance($process_id_old, true);
            return false;
        }

        if(ParticipantModel::findTypeConstByEntity(\ExtensionCopyModel::MODULE_PROCESS, $process_id)){
            ProcessModel::getInstance($process_id_old, true);
            return false;
        }

        $vars = array(
            'process' => array(
                'b_status' => ProcessModel::B_STATUS_IN_WORK,
            )
        );

        $process_id = \Process\models\ProcessModel::model()->findByPk($process_id)
                                ->setVars($vars)
                                ->createFromTemplate();

        if($process_id == false){
            ProcessModel::getInstance($process_id_old, true);
            return false;
        }

        $process_model = ProcessModel::getInstance($process_id, true);
        $process_model->runProcess();

        \Process\models\ProcessModel::addActivityMessageIfEmptyRelateOblect();

        $this->bpmParamsRun($process_id);

        // set old ProcessModel
        ProcessModel::getInstance($process_id_old, true);

        // другие операции
        $result = $process_model->getResult();

        return $result['status'];
    }




    /**
     * getStartTimeModels
     */
    private function getStartTimeModels($process_id, $operations_id){
        if($this->_use_virtual_st_model == true){
            return $this->_virtual_models;
        } else {
            return static::model()->findAll(array(
                'condition' => 'process_id=:process_id AND operations_id=:operations_id',
                'params' => array(
                    ':process_id' => $process_id,
                    ':operations_id' => $operations_id
                ),
            ));
        }
    }



}

