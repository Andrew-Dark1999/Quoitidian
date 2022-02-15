<?php


class QuickViewBlockCommunicationsModel extends QuickViewBlockModel {


    protected $_visible = true;


    public function getName(){
        return 'communications';
    }


    public function getJsClassName(){
        return 'CommunicationsBlock';
    }


    public function getTitle(){
        return \Yii::t('communications', 'Chats');
    }


    public function getItemsModelName(){
        return 'QuickViewItemsCommunicationsModel';
    }



    public function getBlockGroupName(){
        return \QuickViewBlockGroup::BLOCK1;
    }


    public function getWidgetAlias(){
        return 'ext.ElementMaster.QuickView.Communications.QuickViewCommunications';
    }


    public function getCopyId(){
        return \ExtensionCopyModel::MODULE_COMMUNICATIONS;
    }
}
