<?php
/**
 * ParticipantItemListBulder - базовый клас для построения списков виджета ParticipantItemList.ParticipantItemListBulder

 * @author Alex R.
 */

class ParticipantItemListBulder{

    const VIEW_BLOCK            = 'block';
    const VIEW_BLOCK_AVATAR     = 'block_avatar';
    const VIEW_BLOCK_ITEM_LIST  = 'block_item_list';
    const VIEW_ITEM_PARTICIPANT = 'item_participant';
    const VIEW_ITEM_EMAIL       = 'item_email';

    //type search
    const TYPE_ITEM_LIST_SELECTED_ITEM_LIST = 'selected_item_list';

    const TYPE_ITEM_LIST_PARTICIPANT        = 'participant';
    const TYPE_ITEM_LIST_EMAIL              = 'email';





    // VIEW_BLOCK
    private $_b_display = true;
    private $_b_data;

    // VIEW_BLOCK_AVATAR
    private $_ba_data;
    private $_ba_responsible_avatar;

    // VIEW_BLOCK_ITEM_LIST
    private $_bil_data;
    private $_bil_header_title;
    private $_bil_item_list_add_participant = false;
    private $_bil_item_list_add_email = false;
    private $_bil_link_selected_item_list = false;
    private $_bil_type_item_list = self::TYPE_ITEM_LIST_SELECTED_ITEM_LIST; // if "null" that Auto
    private $_bil_item_list_switch_show = false;

    // VIEW_ITEM_PARTICIPANT || VIEW_ITEM_EMAIL
    private $_i_data;
    private $_i_class_add = false;
    private $_i_link_remove = false;

    private $_html;



    // VIEW_BLOCK

    public function setBDisplay($b_display){
        $this->_b_display = $b_display;
        return $this;
    }

    public function getBDisplay(){
        return $this->_b_display;
    }

    public function setBData($b_data){
        $this->_b_data = $b_data;
        return $this;
    }

    public function getBData(){
        return $this->_b_data;
    }

    // VIEW_BLOCK_AVATAR

    public function setBaData($ba_data){
        $this->_ba_data = $ba_data;
    }

    public function getBaData(){
        return $this->_ba_data;
    }

    public function setBaResponsibleAvatar($ba_responsible_avatar){
        $this->_ba_responsible_avatar = $ba_responsible_avatar;
        return $this;
    }

    public function getBaResponsibleAvatar(){
        return $this->_ba_responsible_avatar;
    }

    // VIEW_BLOCK_ITEM_LIST

    public function setBilData($bil_data){
        $this->_bil_data = $bil_data;
        return $this;
    }

    public function getBilData(){
        return $this->_bil_data;
    }

    public function setBilHeaderTitle($bil_header_title){
        $this->_bil_header_title = $bil_header_title;
        return $this;
    }

    public function getBilHeaderTitle(){
        return $this->_bil_header_title;
    }

    public function setBilLinkItemListAddParticipant($bil_item_list_add_participant){
        $this->_bil_item_list_add_participant = $bil_item_list_add_participant;
        return $this;
    }

    public function getBilLinkItemListAddParticipant(){
        return $this->_bil_item_list_add_participant;
    }

    public function setBilLinkItemListAddEmail($bil_item_list_add_email){
        $this->_bil_item_list_add_email = $bil_item_list_add_email;
        return $this;
    }


    public function getBilLinkItemListAddEmail(){
        return $this->_bil_item_list_add_email;
    }


    public function setBilLinkSelectedItemList($bil_link_selected_item_list){
        $this->_bil_link_selected_item_list = $bil_link_selected_item_list;
        return $this;
    }

    public function getBilLinkSelectedItemList(){
        return $this->_bil_link_selected_item_list;
    }

    public function setBilTypeItemList($bil_type_item_list){
        $this->_bil_type_item_list = $bil_type_item_list;
        return $this;
    }


    public function setBilTypeItemListByIDataModel(){
        if($this->_i_data == false){
            $this->_bil_type_item_list = null;
        }

        if(array_key_exists('participant_id', $this->_i_data)){
            $this->_bil_type_item_list = self::TYPE_ITEM_LIST_PARTICIPANT;
        } else
        if(array_key_exists('participant_email_id', $this->_i_data)){
            $this->_bil_type_item_list = self::TYPE_ITEM_LIST_EMAIL;
        }

        return $this;
    }

    public function getBilTypeItemList(){
        return $this->_bil_type_item_list;
    }

    public function setBilItemListSwitchShow($bil_item_list_switch_show){
        $this->_bil_item_list_switch_show = $bil_item_list_switch_show;
        return $this;
    }

    public function getBilItemListSwitchShow(){
        return $this->_bil_item_list_switch_show;
    }

    // VIEW_ITEM_PARTICIPANT || VIEW_ITEM_EMAIL

    public function setIData($i_data){
        $this->_i_data = $i_data;
        return $this;
    }

    public function getIData(){
        return $this->_i_data;
    }

    public function setIClassAdd($i_class_add){
        $this->_i_class_add = $i_class_add;
        return $this;
    }

    public function getIClassAdd(){
        return $this->_i_class_add;
    }

    public function setILinkRemove($i_link_remove){
        $this->_i_link_remove = $i_link_remove;
        return $this;
    }

    public function getILinkRemove(){
        return $this->_i_link_remove;
    }

    private function getShowLinkResponsible($i_data){
        return \ParticipantModel::showLinkResponsible($i_data);
    }

    public function getShowLinkRemoveParticipant($i_data){
        if($this->getILinkRemove() == false){
            return false;
        }

        if($this->getShowLinkResponsible($i_data) == false && $i_data['responsible'] == true){
            return false;
        }

        return true;
    }



    /**
     * getView
     * @return string
     */
    public function getView($view_name){
        switch($view_name){
            case ParticipantItemListBulder::VIEW_BLOCK :
                return 'block';
            case ParticipantItemListBulder::VIEW_BLOCK_AVATAR :
                return 'block-avatar';
            case ParticipantItemListBulder::VIEW_BLOCK_ITEM_LIST :
                return 'block-item-list';
            case ParticipantItemListBulder::VIEW_ITEM_PARTICIPANT :
                return 'item-participant';
            case ParticipantItemListBulder::VIEW_ITEM_EMAIL :
                return 'item-email';
        }
    }



    /**
     * getHtml
     */
    public function getHtml(){
        return $this->_html;
    }



    /**
     * prepareHtml - возвращает контент виджета исходя из переданых параметров и отображения
     */
    public function prepareHtml($view_name){
        $this->_html = $this->getWingetHtml($this->getView($view_name));

        return $this;
    }



    /**
     * getContent
     */
    public function getWingetHtml($view, $model = null){
        if($model === null){
            $model = $this;
        }

        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList'),
            array(
                'view' => $view,
                'model' => $model,
            ),
            true);
    }



    /**
     * getPareparedAttr
     */
    protected function getPareparedAttr(array $attr){
        if($attr == false){
            return;
        }

        $array = [];
        foreach($attr as $key => $value){
            $array[] = $key . '="'.$value.'"';
        }

        return implode(' ', $array);
    }




    /***********************************************************************
     *  block AVATAR
     ***********************************************************************/

    public function getPrepareBlockAvatarParams(){
        if($this->getBData()){
             $this->setBaData($this->getBData()->getEntityData());
        }
        return $this;
    }



    /**
     * getBlockAvatarParams
     * @return array
     */
    public function getBlockAvatarParams($prepare = true){
        $result = array(
            'btn_attr' => array(),
            'btn_html' => null,
        );

        $participant_data = $this->getBaData();

        // если показываем аватар выбранного ответственного
        if($this->_ba_responsible_avatar !== null && $participant_data){
            $btn_attr = array(
                'class' => 'dropdown-toggle element',
                'data-ug_id' => $participant_data['ug_id'],
                'data-ug_type' => $participant_data['ug_type'],
                'data-participant_id' => $participant_data['participant_id'],
            );

            $result['btn_html'] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                                                    array(
                                                        'data_array' => $participant_data,
                                                        'thumb_size' => 32,
                                                        'src' => ($participant_data['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP ? RolesModel::getAvatarSrc() : null),
                                                    ),
                                                    true);
        } else {
            $btn_attr = array(
                'class' => 'dropdown-toggle btn btn-primary element link-item-list-add-participant-button',
                'data-ug_id' => null,
                'data-ug_type' => null,
                'data-participant_id' => null,
            );

        }


        if($prepare){
            $result['btn_attr'] = $this->getPareparedAttr($btn_attr);
        } else {
            $result['btn_attr'] = $btn_attr;
        }

        return $result;
    }








    /***********************************************************************
     *  ITEM
     ***********************************************************************/


    /**
     * getItemParams
     * @return array
     */
    public function getItemParams(){
        $result = array(
            'view' => $this->getItemParamsView(),
            'table_attr' => $this->getItemParamsTableAttr(),
            'item_data_list' => $this->getItemParamsDataList(),
        );

        return $result;
    }




    /**
     * isShowBlockItemList
     */
    public function isShowBlockItemList(){
        return !ListViewBulder::$participant_list_hidden;
    }


    /**
     * getItemParamsView
     */
    public function getItemParamsView(){
        switch($this->getBilTypeItemList()){
            case self::TYPE_ITEM_LIST_SELECTED_ITEM_LIST:
            case self::TYPE_ITEM_LIST_PARTICIPANT:
                return 'item-participant';
            case self::TYPE_ITEM_LIST_EMAIL:
                return 'item-email';
            default:
                return 'item-participant';

        }
    }


    /**
     * getItemParamsTableAttr
     */
    private function getItemParamsTableAttr(){
        $table_attr = array(
            'class' => 'table list-table element',
            'data-type' => 'block-card-participant',
        );

        if($this->_ba_responsible_avatar){
            $table_attr['data-type'] = 'block-card-responsible';
        }

        return $this->getPareparedAttr($table_attr);
    }



    /**
     * getItemParamsDataList
     */
    private function getItemParamsDataList(){
        return $this->getBilData();
    }





    /**
     * getItemSwitchKeyActiveLabel
     */
    public function getItemSwitchKeyActiveLabel($type_item_list){
        if($type_item_list == $this->getBilTypeItemList()){
            return 'active';
        }
    }


}




    /*
    Структура элементов блока block_participant:
    ***winget EditView.Participant.card
    element[data-type="block_participant"]
    {
        element[data-type="drop_down"]
        {
            //
            element[data-type="block-card"]
            {
                **winget EditView.Participant.card
                element[data-type="participant"]
            }

            //
            **winget Select.block_participant
            data-type="select"
            {
                data-type="drop_down_button"
                data-type="drop_down_list"
                {
                    **winget Select.block_card_participant_list
                    data-type="block-card-participant"
                    {
                        **winget Select.card_participant
                        data-type="card-participant"
                    }
                }
            }
        }
    }
    */
