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

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    ListViewBulder::$participant_list_hidden = true;
                }

                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false) &&
                    !Access::checkAccessDataOnParticipant(\Yii::app()->request->getParam('pci', null), \Yii::app()->request->getParam('pdi', null))
                ){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'showTemplate':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    ListViewBulder::$participant_list_hidden = true;
                }

                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false)){
                    throw new CHttpException(404);
                }

                break;
            case 'getTodoList' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'update':
            case 'copy':
            case 'panelSort':
            case 'cardSort':
            case 'panelSortDelete':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'delete' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }
        
        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);
        
        $filterChain->run();
    }



    private function setProjectMenu(){
        $this->data['project_menu_module_data'] = null;
        $this->data['project_menu_pdi_active'] = Yii::t($this->module->getModuleName() . 'Module.base', 'Projects');
        $this->data['pm_extension_copy'] = null;
        
        if(isset($_GET['pci']) && isset($_GET['pdi'])){

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

        // set sorting
        if($this->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS){
            $this->module->initPropertiesForProcessView();
            if($this->module->view_related_task){
                $_GET['sort'] = json_encode(array('todo_list'=>'a'));
            }
        }
    }

    public function actionShow(){
        $this->setProjectMenu();
        ViewList::setViews(array('site/processView' => '/site/process-view'));
        $this->module->list_view_layout = false;
    
        parent::actionShow();
    }

  


    public function actionShowTemplate(){
        $this->this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE;
        $this->setProjectMenu();
        ViewList::setViews(array('site/processView' => '/site/process-view'));
        $this->module->list_view_layout = false;
    
        parent::actionShowTemplate();
    }
  





    /**
    * обноление данных модуля (при перетаскивании)
    */ 
    public function actionUpdate($copy_id){
        $this->module->initPropertiesForProcessView();
        parent::actionUpdate($copy_id);
    }






    protected function showListView(){
        $pci = \Yii::app()->request->getParam('pci', false);
        if($pci){
            return false;
        }

        return true;
    }






    /**
     * actionGetTodoList - возвращает список Todo листов
     */
    public function actionGetTodoList($copy_id){
        $result = array(
            'status' => false,
            'select_list' => false,
        );

        if(Yii::app()->request->isAjaxRequest == false  || Yii::app()->request->isPostRequest == false){
            return $this->renderJson($result);
        }

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        $schema_field = $extension_copy->getFieldSchemaParams('todo_list');

        $select_list = (new Tasks\models\DataListModel())
            ->setGlobalParams([
                'card_id' => \Yii::app()->request->getParam('id'),
                'pci' => \Yii::app()->request->getParam('pci'),
                'pdi' => \Yii::app()->request->getParam('pdi'),
                'this_template' => \Yii::app()->request->getParam('this_template'),
                'finished_object' => \Yii::app()->request->getParam('finished_object'),
                'schema_field' => $schema_field['params'],
            ])
            ->setExtensionCopy($extension_copy)
            ->prepare(\DataListModel::TYPE_FOR_SELECT_TYPE_LIST, null)
            ->getData();


        $result['status'] = true;

        if($select_list){
            $result['select_list'] = $select_list;
        }

        return $this->renderJson($result);
    }



}
