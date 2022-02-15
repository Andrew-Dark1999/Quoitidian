<?php

class ProcessViewController extends ProcessView{
    
   
   
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'index':
            case 'show':
                if($this->module->extensionCopy->getIsTemplate() == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
                    \Yii::app()->request->getParam('pci') == false &&
                    \Yii::app()->request->getParam('pdi') == false
                ){
                    $this->redirect(Yii::app()->createUrl('/module/processView/showTemplate') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    return;
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false) &&
                    !Access::checkAccessDataOnParticipant(\Yii::app()->request->getParam('pci', null), \Yii::app()->request->getParam('pdi', null))
                ){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;
            case 'copy':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'delete' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }
        
        $this->module->setAccessCheckParams(RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
        
        $filterChain->run();
    }       


}
