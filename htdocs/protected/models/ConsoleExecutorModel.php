<?php

/**
 * User: rom
 * Date: 10.05.17
 * Time: 11:52
 */
class ConsoleExecutorModel extends ActiveRecord
{
    const STATUS_ENABLE       = 'enable';
    const STATUS_DISABLE      = 'disable';

    public $tableName = 'console_executor';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function getSchema(){
        return json_decode($this->start_on_time, true);
    }



    public function getProperties(){
        return json_decode($this->properties, true);
    }

}