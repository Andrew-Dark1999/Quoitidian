<?php
/**
 *    ParticipantController
 *    @author Alex R.
 */


class ParticipantController extends Participant{




    /**
     * возвращает список новых учасников
     */
    public function actionGetItemList($copy_id){
        $properties = $_POST + array('copy_id' => $copy_id);

        $result = (new \Communications\models\ParticipantActionsModel())
                            ->setProperties($properties)
                            ->run(\Communications\models\ParticipantActionsModel::ACTION_PREPARE_SELECT_ITEM_LIST)
                            ->getResult();

        return $this->renderJson($result);
    }



}
