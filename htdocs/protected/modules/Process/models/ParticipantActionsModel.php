<?php
namespace Process\models;


use \Process\models\ParticipantModel as ParticipantModel;


class ParticipantActionsModel extends \ParticipantActionsModel{



    protected function getParticipantClassName(){
        return '\Process\models\ParticipantModel';
    }


}
