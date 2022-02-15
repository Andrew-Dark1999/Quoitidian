<?php

use Tasks\extensions\ElementMaster\InLineEdit\Elements\InLineEdit;
use Tasks\models\DataListModel;



class ListViewController extends \ListView{
    
    




    private function setProjectMenu(){
        $this->data['project_menu_module_data'] = null;
        $this->data['project_menu_pdi_active'] = Yii::t($this->module->getModuleName() . 'Module.base', 'Projects');
        $this->data['pm_extension_copy'] = null;

        if(isset($_GET['pci']) && isset($_GET['pdi'])){

            if($this->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS){
                $this->module->view_related_task = true;
            }

            $pm_extension_copy = ExtensionCopyModel::model()->findByPk($_GET['pci']);

            $this->data['pm_extension_copy'] = $pm_extension_copy;
            $this->data['project_menu_module_data'] = DropDownNavigationModel::getInstance()
                                                                ->setVars(array('extension_copy' => $pm_extension_copy, 'id' => $_GET['pdi']))
                                                                ->prepare(\DropDownNavigationModel::MENU_TASK_PROJECT)
                                                                ->getResult()['data'];

            foreach($this->data['project_menu_module_data'] as $project){
                if($project[$pm_extension_copy->prefix_name . '_id'] == $_GET['pdi']){
                    $this->data['project_menu_pdi_active'] = $project['module_title'];
                    break;
                }
            }
        }
    }


    public function actionShow(){
        $this->setProjectMenu();
        parent::actionShow();
    }


    public function actionShowTemplate(){
        $this->this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE;
        $this->setProjectMenu();
        parent::actionShowTemplate();
    }





    protected function showListView(){
        $pci = \Yii::app()->request->getParam('pci', false);
        if($pci){
            return false;
        }

        return true;
    }




    public function actionLoadInlineElements(){
        ViewList::setViews(array('ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit' => '\Tasks\extensions\ElementMaster\InLineEdit\Elements\InLineEdit\InLineEdit'));
        parent::actionLoadInlineElements();
    }




}
