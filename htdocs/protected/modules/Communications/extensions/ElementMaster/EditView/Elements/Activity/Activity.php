<?php

namespace Communications\extensions\ElementMaster\EditView\Elements\Activity;

use Communications\extensions\ElementMaster\ParticipantItemListBulder;

\Yii::import('ext.ElementMaster.EditView.Elements.Activity.Activity');




class Activity extends \Activity{


    public function getBtnShowChannel(){
        return false;
    }


}
