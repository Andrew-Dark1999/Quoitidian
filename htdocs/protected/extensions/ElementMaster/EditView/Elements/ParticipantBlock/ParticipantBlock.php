<?php
/**
* ParticipantBlock widget
* @author Alex R.
* @version 1.0
*/ 

class ParticipantBlock extends CWidget{
    // название шаблона
    public $view;
    // схема модуля
    public $schema = null;
    //$extension_copy
    public $extension_copy;
    //$extension_copy
    public $extension_copy_data;
    // внутренний контент для шаблона 
    public $content = null;
    // данные из ParticipantModel
    public $participant_data = null;
    // разрешает редактирование участников
    public $edit_partisipants = true;
    // показывает/скрывает линк на остановку ответственного.
    private $_show_link_responsible = true;

    // bil_type_item_list_email
    public $bil_type_item_list_email = ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT;


    // participant_data_entities
    protected $_participant_data_entities = array();





    protected function getViewPathAlias(){
        return 'ext.ElementMaster.EditView.Elements.ParticipantBlock.views.';
    }


    private function getView($add_path_alias = true){
        if($add_path_alias){
            return $this->getViewPathAlias() . $this->view;
        } else {
            return $this->view;
        }
    }



    public function init(){
        if($this->view == 'participant'){
            $this->prepareShowLinkResponsible();
        }

        if($this->view == 'block'){
            $this->prepareParticipantDataEntities();
        }

        $this->render($this->getView());
    }




    private function prepareParticipantDataEntities(){
        if(!empty($this->participant_data)){
            foreach($this->participant_data as $data){
                $p = $data->getEntityData();
                if($p == false) continue;
                $this->_participant_data_entities[] = $p;
            }
        }

    }


    public function showEmailParticipantTitleAttr(){
        return $this->participant_data['title'] && $this->participant_data['title']!==null ? true : false;
    }



    protected function getBilLinkItemListAddParticipant(){
        return true;
    }


    protected function getBilLinkItemListAddEmail(){
        return false;
    }


    protected function getParticipantItemListBulderInstance(){
        return new ParticipantItemListBulder();
    }


    /**
     * 1. Возвращает список выбранных участников
     */
    public function getBlockParticipantSelectedHtml(){
        if($this->edit_partisipants){
            $select_model = $this->getParticipantItemListBulderInstance()
                                    ->setBilData($this->_participant_data_entities)
                                    ->setBilLinkItemListAddParticipant($this->getBilLinkItemListAddParticipant())
                                    ->setBilLinkItemListAddEmail($this->getBilLinkItemListAddEmail())
                                    ->setBilTypeItemList(ParticipantItemListBulder::TYPE_ITEM_LIST_SELECTED_ITEM_LIST)
                                    ->setILinkRemove(true)
                                    ->prepareHtml(ParticipantItemListBulder::VIEW_BLOCK);

            return $select_model->getHtml();
        }

    }



    public function getSrc(){
        if(in_array($this->participant_data['ug_type'], [
            \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP,
            \ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
        ])
        ){
            return $this->participant_data['ehc_image1'];
        }
    }





    private function prepareShowLinkResponsible(){
        $this->_show_link_responsible = \ParticipantModel::showLinkResponsible($this->participant_data);
    }


    public function getShowLinkResponsible(){
        return $this->_show_link_responsible;
    }


    public function getShowLinkRemoveParticipant(){
        if($this->getShowLinkResponsible() == false && $this->participant_data['responsible'] == true){
            return false;
        }

        return true;
    }






}
