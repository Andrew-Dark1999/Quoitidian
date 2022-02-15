<?php

namespace Communications\extensions\ElementMaster\EditView\Elements\ParticipantBlock;

use Communications\extensions\ElementMaster\ParticipantItemListBulder;

\Yii::import('ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock');




class ParticipantBlock extends \ParticipantBlock{



    protected function getBilLinkItemListAddParticipant(){
        if($this->bil_type_item_list_email == \ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT){
            return true;
        }

        return false;
    }



    protected function getBilLinkItemListAddEmail(){
        if($this->bil_type_item_list_email == \ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL){
            return true;
        }

        return false;
    }


    protected function getParticipantItemListBulderInstance(){
        return new ParticipantItemListBulder();
    }




}
