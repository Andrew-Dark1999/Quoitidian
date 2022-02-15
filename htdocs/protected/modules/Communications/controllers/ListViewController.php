<?php

class ListViewController extends ListView{


    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'show':
                if($this->module->extensionCopy->getIsTemplate() == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
                    \Yii::app()->request->getParam('pci') == false &&
                    \Yii::app()->request->getParam('pdi') == false
                ){
                    $this->redirect(Yii::app()->createUrl('/module/listView/showTemplate') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    return;
                }

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;

            case 'delete':

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;

        }

        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);

        $filterChain->run();
    }


    public function actionShow(){
        ViewList::setViews(array('site/listView' => '/site/list-view'));
        parent::actionShow();
    }



    /**
     *   Возвращает все данные для отображения listView
     */
    public function getDataForView($extension_copy, $only_PK = false){
        $data = parent::getDataForView($extension_copy, $only_PK = false);

        $datetime_activity_params_block = SchemaOperation::getBlockWithKey($data['submodule_schema_parse'],'type','datetime_activity');
        $name_ativity_param_value='activity_last_date';
        $field_name=$datetime_activity_params_block['name'];
        foreach($data['submodule_data'] as $key=>$value){
            $data['submodule_data'][$key][$field_name]=$data['submodule_data'][$key][$name_ativity_param_value];
        }
        return $data;
    }



    protected function getData($extension_copy, $only_PK = false){
        $pci = \Yii::app()->request->getParam('pci');
        $pdi = \Yii::app()->request->getParam('pdi');

        return (new CommunicationsModel())->getData($extension_copy, $only_PK = false, $pci, $pdi, $this->module->finishedObject(), $this->this_template);
    }




}
