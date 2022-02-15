<?php


/**
 * ParticipantFlagsModel - дополнительные флаги для сущностей Участников
 */
class ParticipantFlagsModel extends \ActiveRecord{

    // флаги для констант:
        // связанный ответственный
    const FLAG_CONST_RELATE_RESPONSIBLE         = 'const_relate_responsible';
        // ответственный за процесс
    const FLAG_CONST_RESPONSIBLE_FOR_PROCESS    = 'const_responsible_for_process';



    public $tableName = 'participant_flags';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('participant_flags_id, participant_id, flag', 'safe'),
        );
    }


    public function relations(){
        return array(
            'participant' => array(self::BELONGS_TO, 'ParticipantModel', 'participant_id'),
        );
    }



    public static function getFlagsListFull(){
        return [
            self::FLAG_CONST_RELATE_RESPONSIBLE,
            self::FLAG_CONST_RESPONSIBLE_FOR_PROCESS,
        ];
    }




}
