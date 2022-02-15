<?php

class ProcessStartTimeLogModel extends ActiveRecord{


    public $tableName = 'process_start_time_log';


    public static function getInstance(){
        return new static();
    }


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('process_id, operations_id, date_create, notation', 'safe'),

        );
    }



    public static function savelog($attributes){
        $params = \Yii::app()->params;
        $enable = false;
        if(isset($params['process']['start_time_log_enabled'])){
            $enable = $params['process']['start_time_log_enabled'];
        }


        if($enable){
            \DataModel::getInstance()->insert('{{process_start_time_log}}', $attributes);
        }
    }


    public static function deleteOldRecords($limitDate){

        $table_name = '{{process_start_time_log}}';
        \DataModel::getInstance()->Delete($table_name, 'date_create < "' . $limitDate . '"');

    }


}
