<?php


abstract class QuickViewBlockModel implements QuickViewBlockModelInterface {

    private $_items_model;

    protected $_enable = true;
    protected $_visible = false;


    public function __construct(){
        $this->init();
    }

    private function init(){
        $this->initEnable();
        $this->initItemsModel();
    }


    public function getItemsModel(){
        return $this->_items_model;
    }


    public function setVisible($visible){
        $this->_visible = $visible;
        return $this;
    }


    public function getEnable(){
        return $this->_enable;
    }


    public function initEnable(){
        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getCopyId());
        $this->_enable = (bool)$extension_copy->active;

        if(Yii::app()->user->isGuest == true || !Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->getCopyId(), Access::ACCESS_TYPE_MODULE)){
            $this->_enable = false;
        }

        return $this;
    }


    private function initItemsModel(){
        $class_name = $this->getItemsModelName();
        $this->_items_model = (new $class_name());
        $this->_items_model->setBlockModel($this);

        return $this;
    }


    public function getVisible(){
        return $this->_visible;
    }


}
