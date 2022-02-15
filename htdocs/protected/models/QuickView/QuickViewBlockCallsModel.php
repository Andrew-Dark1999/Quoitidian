<?php


class QuickViewBlockCallsModel extends QuickViewBlockModel {

    protected $_visible = false;


    public function getName(){
        return 'calls';
    }


    public function getJsClassName(){
        return 'CallsBlock';
    }


    public function getTitle(){
        return \Yii::t('calls', 'Calls');
    }


    public function getItemsModelName(){
        return 'QuickViewItemsCallsModel';
    }



    public function getBlockGroupName(){
        return \QuickViewBlockGroup::BLOCK1;
    }


    public function getWidgetAlias(){
        return 'ext.ElementMaster.QuickView.Calls.QuickViewCalls';
    }


    public function getCopyId(){
        return \ExtensionCopyModel::MODULE_CALLS;
    }


}
