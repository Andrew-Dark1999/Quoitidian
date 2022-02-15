<?php
namespace Process\models;


class ProcessAutostartByEntityModel extends \ActiveRecord {


    const ACTION_NAME_CREATE    = 'create';
    const ACTION_NAME_CHANGE    = 'change';



    public $tableName = 'process_autostart_by_entity';




    public static function model($className=__CLASS__){
        return parent::model($className);
    }





    public function rules(){
        return array(
            array('operations_id, copy_id, action_name', 'safe'),
        );
    }


    public function relations(){
        return array(
            'operations' => array(self::BELONGS_TO, '\Process\models\OperationsModel', 'operations_id'),
        );
    }




    public function findByProcessId($process_id){
        return static::model()->with('operations')->find(
            array(
                'condition' => 'operations.process_id=:process_id',
                'params' => array(
                    ':process_id' => $process_id,
                )
            )
        );
    }






}
