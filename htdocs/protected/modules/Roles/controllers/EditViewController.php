<?php

class EditViewController extends EditView{
    
   
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'edit':
                if(Yii::app()->controller->module->edit_view_enable == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if($data_id = \Yii::app()->request->getParam('id')){
                    (new EditViewActionModel())
                        ->setExtensionCopy($this->module->extensionCopy)
                        ->findAndMarkHistoryIsView($data_id);
                }

            case 'inLineSave':
                if(Yii::app()->controller->module->inline_edit_enable == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(!Access::checkAccessDataOnParticipant($this->module->extensionCopy->copy_id, \Yii::app()->request->getParam('id',  null))){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;
        }
        
        $this->module->setAccessCheckParams(RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
        
        $filterChain->run();
    }  
   

}
