<?php


class ListViewController extends \ListView{


    /**
     * filter
     */
    public function filterCheckAccess($filterChain){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PERMISSION)->setAddDateCreateEntity(false)->getSchemaParse();

        switch(Yii::app()->controller->action->id){
            case 'index':
            case 'show':
            case 'showPermission':
                if($this->module->extensionCopy->getIsTemplate() == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
                    \Yii::app()->request->getParam('pci') == false &&
                    \Yii::app()->request->getParam('pdi') == false
                ){
                    $this->redirect(Yii::app()->createUrl('/module/listView/showTemplate') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    return;
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if((boolean)Yii::app()->controller->module->extensionCopy->be_parent_module == true && !array_key_exists('pdi', $_GET)){
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
            case 'import' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_IMPORT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'print' :
            case 'export' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EXPORT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    echo Yii::t('messages', 'You do not have access to this object');
                    return false;
                }
                break;
        }
        
        
        $this->module->setAccessCheckParams(RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
        
        $filterChain->run();
    }    
          
   
       
    public function getRolesList(){
        $data_model = new DataModel();
        $data_model
            ->setFrom('{{roles}}')
            ->setOrder('module_title');
        return $data_model->findAll();
    }





    public function getRoleActiveTitle(){
        if(empty($_GET['pdi'])) return;
        $data_model = new DataModel();
        $role_title = $data_model
                            ->setSelect('module_title')
                            ->setFrom('{{roles}}')
                            ->setWhere('roles_id=:roles_id', array(':roles_id'=>$_GET['pdi']))
                            ->findScalar();

        return $role_title;
    }






    /**
    *  
    */ 
    public function getData($extension_copy, $only_PK = false){

        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $only_id = DataValueModel::getInstance()->getIdOnTheGroundParent($extension_copy->copy_id, \Yii::app()->request->getParam('pci'), \Yii::app()->request->getParam('pdi'));
        if($only_id === false) return array();
        
        $pagination = new Pagination();
        $pagination->setParamsFromUrl();

        $search = new Search();
        $search->setTextFromUrl();

        $filters = new Filters();
        $filters->setTextFromUrl();

        //*********************
        // get data
        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables()
            ->setFromFieldTypes()
            ->setCollectingSelect();
        //filters
        if(!$filters->isTextEmpty()){
            $filter_data = $filter_controller->getParamsToQuery($extension_copy, $filters->getText());
            if(!empty($filter_data))
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }

        if(!empty($only_id)) $data_model->andWhere(array('AND', $extension_copy->prefix_name . '_id in (' . $only_id . ')'));
        //order
        Sorting::getInstance()->setParamsFromUrl();
        $data_model->setOrder($data_model->getOrderFromSortingParams());

        $data_model->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();

        $data_model1 = new DataModel();
        $data_model1
            ->setExtensionCopy($extension_copy)
            ->setSelect(array('permission.*', '{{regulation}}.title as module_title', '{{regulation}}.sort', 'concat("regulation") as type'))
            ->setFrom( '(' . $data_model->getText() . ') as permission')
            ->join('regulation', '{{regulation}}.regulation_id = permission.access_id')
            ->setWhere('access_id_type = 1');
        $data_model2 = new DataModel();
        $data_model2
            ->setSelect(array('permission.*', '{{extension_copy}}.title as module_title', '{{extension_copy}}.sort + 10000', 'concat("module") as type'))
            ->setFrom( '(' . $data_model->getText() . ') as permission')
            ->join('extension_copy', '{{extension_copy}}.copy_id = permission.access_id')
            ->setWhere('access_id_type = 2');
        $data_model1->setUnion($data_model2->getText());
        $data_model1->setParams($data_model->getParams());
        
        $data_model_result = new DataModel();
        $data_model_result->setExtensionCopy($extension_copy);
        $data_model_result->setFrom('('.$data_model1->getText().') as data');
        $data_model_result->setParams($data_model1->getParams());

        //search
        if($search::$text !== null){
            $data_model_result->addSelect('data.*');
            $data_model_result->addSelect('(case when (permission_rule_view_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'" 
                                        END) 
                                        as permission_rule_view_title_translated');
            $data_model_result->addSelect('(case when (permission_rule_create_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'" 
                                        END) 
                                        as permission_rule_create_title_translated');
            $data_model_result->addSelect('(case when (permission_rule_delete_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'" 
                                        END) 
                                        as permission_rule_delete_title_translated');
            $data_model_result->addSelect('(case when (permission_rule_edit_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'"
                                        END) 
                                        as permission_rule_edit_title_translated');
            $data_model_result->addSelect('(case when (permission_rule_export_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'" 
                                        END) 
                                        as permission_rule_export_title_translated');
            $data_model_result->addSelect('(case when (permission_rule_import_title = "Allowed") 
                                        THEN "'.\Yii::t('PermissionModule.base','Allowed').'" 
                                        ELSE "'.\Yii::t('PermissionModule.base','Prohibited').'" 
                                        END) 
                                        as permission_rule_import_title_translated');

            $data_model_result->setCollectingSelect();
            $data_model_result->setSelectNew();
            $data_model_result->setWhere('module_title LIKE :search_param OR 
                                              permission_rule_view_title_translated LIKE :search_param OR 
                                              permission_rule_create_title_translated LIKE :search_param OR 
                                              permission_rule_delete_title_translated LIKE :search_param OR 
                                              permission_rule_edit_title_translated LIKE :search_param OR 
                                              permission_rule_export_title_translated LIKE :search_param OR 
                                              permission_rule_import_title_translated LIKE :search_param
                                              ',[':search_param'=>'%'.Search::$text.'%']);
        }
        

        if($pagination->getActivePageSize() > 0){
            $data_model_result
                ->setSelect('SQL_CALC_FOUND_ROWS (0)' . (!empty($select) ? ',' . $select : ', data.*'))
                ->setLimit($pagination->getActivePageSize())
                ->setOffSet($pagination->getOffset());


        }

        $sorting = Sorting::getInstance()->getParams();
        if(!empty($sorting) && array_key_exists('module_title', $sorting)){
            $data_model_result->setOrder('module_title ' . $sorting['module_title']);
        } else {
            $sort_params = $data_model_result->getOrderFromSortingParams();
            if(!empty($sort_params))
                $data_model_result->setOrder($sort_params);
            else
                $data_model_result->setOrder('sort asc');
        }


        //findAll
        $data = $data_model_result->findAll();

        \Pagination::getInstance()->setItemCount();

        // tramslate module title
        $lich = 0;
        foreach($data as $value){
            $data[$lich]['module_title'] = $value['module_title'];
            $lich++;
        }

        return $data;
    }



   
   
    /**
    *
    */
    public function actionShow(){
        $this->addNewModuleToPermission();
                
        $this->left_menu = true;
        ViewList::setViews(array('site/listView' => '/site/list-view'));
        ViewList::setViews(array('ext.ElementMaster.ListView.Elements.TData.TData' => 'Permission.extensions.ElementMaster.ListView.Elements.TData.TData'));
        ViewList::setViews(array('ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit' => 'Permission.extensions.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit'));

        parent::actionShow();
    }


    
    public function actionShowPermission($pdi){
        $_GET['pci'] = ExtensionCopyModel::MODULE_ROLES;  
        $_GET['pdi'] = $pdi;
        
        $this->actionShow();
    }





    public function isSetRulesForModule($role_id, $access_id, $access_id_type){
       
        $module_model = new DataModel();
        $module_model->setText("SELECT {{permission}}.permission_id
                                FROM {{permission_roles}}
                                    LEFT JOIN {{permission}} ON {{permission}}.permission_id = {{permission_roles}}.permission_id
                                WHERE {{permission_roles}}.roles_id = $role_id AND {{permission}}.access_id = $access_id AND {{permission}}.access_id_type = $access_id_type");
        $modules = $module_model->findAll();
        if(empty($modules)) return false; else return true;
    }
    
    
    
    public function addRuleForModule($role_id, $access_id, $access_id_type){
        $module_model = new DataModel();
        if($module_model->Insert('{{permission}}', array(
                                                    'date_create' => date('Y-m-d H:i:s'),
                                                    'user_create' => WebUser::getUserId(),
                                                    'access_id' => $access_id,
                                                    'access_id_type' => $access_id_type,
                                                    'rule_view' => '2',
                                                    'rule_create' => '2',
                                                    'rule_edit' => '2',
                                                    'rule_delete' => '2',
                                                    'rule_import' => '2',
                                                    'rule_export' => '2',
        ))){
            $module_model = new DataModel();
            $module_model->setText('SELECT max(permission_id) as permission_id FROM {{permission}}');
            $modules = $module_model->findAll();
            $permission_id = null;
            if(!empty($modules)) $permission_id = $modules[0]['permission_id'];
            
            $module_model = new DataModel();
            $module_model->Insert('{{permission_roles}}', array(
                                                        'roles_id' => $role_id,
                                                        'permission_id' => $permission_id,
            ));
        }
    }
    
    

    public function addNewModuleToPermission(){
        if(empty($_GET['pdi'])) return;
        $module_model = new DataModel();
        $module_model->setText('SELECT regulation_id as access_id, concat("1") as access_id_type
                                    FROM {{regulation}}
                                UNION
                                SELECT copy_id as access_id, concat("2") as access_id_type
                                    FROM {{extension_copy}}
                                    WHERE set_access = "1"');
        $modules = $module_model->findAll();
        if(empty($modules)) return;
        $role_id = addslashes($_GET['pdi']);
        foreach($modules as $module){
            if(!$this->isSetRulesForModule($role_id, $module['access_id'], $module['access_id_type'])){
                $this->addRuleForModule($role_id, $module['access_id'], $module['access_id_type']);
            }
        }
    }






 
  

}
