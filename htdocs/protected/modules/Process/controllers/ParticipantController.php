<?php
/**
 *    ParticipantController
 *    @author Alex R.
 */

use \Process\models\ParticipantActionsModel as ParticipantActionsModel;


class ParticipantController extends Participant{



    protected function getParticipantActionsInstance(){
        return new ParticipantActionsModel();
    }





    /**
     * возвращает список новых учасников
     */
    public function actionGetItemList($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = $this->getParticipantActionsInstance()
                            ->setProperties($properties)
                            ->run(ParticipantActionsModel::ACTION_PREPARE_SELECT_ITEM_LIST)
                            ->getResult();

        $result['without_participant_const'] = true;
        if($dinamic_copy_id = \Yii::app()->request->getPost('dinamic_copy_id')){
            $result['without_participant_const'] = !ParticipantModel::hasElementParticipant($dinamic_copy_id);
        }

        return $this->renderJson($result);
    }


}
