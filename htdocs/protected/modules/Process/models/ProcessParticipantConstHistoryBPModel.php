<?php

namespace Process\models;

class ProcessParticipantConstHistoryBPModel  extends \ActiveRecord{



    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return '{{process_participant_const_history_bp}}';
    }

    public function rules(){
        return array(
            array('process_id, ug_id, unique_index', 'safe'),
        );
    }

    public function relations(){
        return array(
            'process' => array(self::BELONGS_TO, '\Process\models\ProcessModel', 'process_id'),
        );
    }


}
