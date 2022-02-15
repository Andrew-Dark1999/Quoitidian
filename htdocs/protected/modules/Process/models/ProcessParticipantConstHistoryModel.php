<?php
namespace Process\models;

class ProcessParticipantConstHistoryModel  extends \ActiveRecord{



    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return '{{process_participant_const_history}}';
    }

    public function rules(){
        return array(
            array('process_id, ug_id', 'safe'),
        );
    }

    public function relations(){
        return array(
            'process' => array(self::BELONGS_TO, '\Process\models\ProcessModel', 'process_id'),
        );
    }


}
