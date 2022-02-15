<?php
/**
* ParticipantItemList widget
* @author Alex R.
*/

class ParticipantItemList extends CWidget{

    // file view name
    public $view;

    // ParticipantItemListBulder model
    public $model;



    public function init(){
        $this->render($this->view, null);
    }


    public function getSrc(){
        $i_data = $this->model->getIData();

        if(in_array($i_data['ug_type'], [
            \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP,
            \ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
        ])
        ){
            return $i_data['ehc_image1'];
        }
    }

}
