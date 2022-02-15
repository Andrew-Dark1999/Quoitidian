<?php
/**
 * ParticipantActionsModel
 */

namespace Communications\models;

use Communications\extensions\ElementMaster\ParticipantItemListBulder;


class ParticipantActionsModel extends \ParticipantActionsModel{



    protected function getParticipantItemListBulderInstance(){
        return new ParticipantItemListBulder();
    }






}
