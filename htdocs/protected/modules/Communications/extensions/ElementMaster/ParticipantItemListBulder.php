<?php
/**
 * ParticipantItemListBulder
 */

namespace Communications\extensions\ElementMaster;

use Communications\models;


class ParticipantItemListBulder extends \ParticipantItemListBulder{




    public function getBilItemListSwitchShow(){
        return false;
    }



    public function getBlockAvatarParams($prepare = true){
        $params = parent::getBlockAvatarParams(false);

        if(($this->getBilLinkItemListAddEmail() && $this->getBilTypeItemList() == self::TYPE_ITEM_LIST_SELECTED_ITEM_LIST) ||
            $this->getBilTypeItemList() == self::TYPE_ITEM_LIST_EMAIL
        ){
            $params['btn_attr']['class'] = 'dropdown-toggle btn btn-primary element link-item-list-add-email-button';
        }

        $params['btn_attr'] = $this->getPareparedAttr($params['btn_attr']);

        return $params;
    }

}
