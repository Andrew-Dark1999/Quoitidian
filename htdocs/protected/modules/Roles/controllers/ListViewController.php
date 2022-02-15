<?php


class ListViewController extends \ListView{
    
    
    protected $add_inline_data = true;
    
    /**
     * filter
     */
    public function filterCheckAccess($filterChain){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_ROLES)->setAddDateCreateEntity(false)->getSchemaParse();

        switch(Yii::app()->controller->action->id){
            case 'index':
            case 'show':
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
       






    /**
    *   ���������� ������ ������ 
    */ 
    public function getData($extension_copy, $only_PK = false){
        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $only_id = DataValueModel::getInstance()->getIdOnTheGroundParent($extension_copy->copy_id, \Yii::app()->request->getParam('pci'), \Yii::app()->request->getParam('pdi'));
        if($only_id === false) return array();
        
        $search = new Search();
        $search->setTextFromUrl();
        $filters = new Filters();
        $filters->setTextFromUrl();


        // *** get data
        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();
        //filters
        if(!$filters->isTextEmpty()){
            $filter_data = $filter_controller->getParamsToQuery($extension_copy, $filters->getText());
            if(!empty($filter_data))
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }
        if(!empty($only_id)) $data_model->andWhere(array('AND', $extension_copy->prefix_name . '_id in (' . $only_id . ')'));
        //order
        $sorting = Sorting::getInstance()->setParamsFromUrl()->getParams();
        if(!empty($sorting) && array_key_exists('date_create', $sorting))
            $data_model->setOrder('date_create ' . $sorting['date_create']);
        else
            $data_model->setOrder($data_model->getOrderFromSortingParams());
     
        $data_model->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();
        //search
        if($search::$text !== null){
            $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
        }

        // pagination
        $pagination = new Pagination();
        $pagination->setParamsFromUrl();

        if($pagination->getActivePageSize() > 0){
            $select = $data_model->getSelect();
            $data_model
                ->setSelect('SQL_CALC_FOUND_ROWS (0)' . (!empty($select) ? ',' . $select : ', data.*'))
                ->setLimit($pagination->getActivePageSize())
                ->setOffSet($pagination->getOffset());

            $this->_set_pagination = true;
        }

        //findAll
        $data = $data_model->findAll();

        return $data;
    }




    
   
    /**
    * ���������� (�������) ����� ListView
    */
    public function actionShow(){
        $this->left_menu = true;
        ViewList::setViews(array('site/listView' => '/site/list-view'));
        parent::actionShow();
    }

  


  
  
    public function getFullData(){
        if($this->module->extension === null) $this->module->setExtension();
        if($this->module->extensionCopy === null) $this->module->setFirstExtensionCopy();
        
        Pagination::$active_page_size = 0;
        $data = $this->getData($this->module->extensionCopy);

        // обработка пагинации
        if($this->_set_pagination){
            $this->_set_pagination = false;
            \Pagination::getInstance()->setItemCount();

            // если страница пагинации указан больше чем есть в действительности
            if(\Pagination::switchActivePageIdLarger()){
                $data['submodule_data'] = $this->getData($this->module->extensionCopy);
            }
        };


        return $data;
    }
    
   
    

}
