<?php

class ListViewController extends \ListView{
    

    private $_set_pagination = false;

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        if(!isset($_GET['pci']) && !isset($_POST['pci'])) {
            return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
        }
        
        if(isset($_GET['pci'])) $copy_id = $_GET['pci'];
        elseif(isset($_POST['pci'])) $copy_id = $_POST['pci'];
        
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

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(ValidateRules::checkIsSetParentDataModule() == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false) &&
                    !Access::checkAccessDataOnParticipant(\Yii::app()->request->getParam('pci', null), \Yii::app()->request->getParam('pdi', null))
                ){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                break;
            case 'showTemplate':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(ValidateRules::checkIsSetParentDataModule() == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false)){
                    throw new CHttpException(404);
                }

                break;
            case 'copy':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'delete' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'import' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_IMPORT, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'print' :
            case 'export' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EXPORT, $copy_id, Access::ACCESS_TYPE_MODULE)){
                    echo Yii::t('messages', 'You do not have access to this object');
                    return false;
                }
                break;
        }
        
        $this->module->setAccessCheckParams($copy_id, Access::ACCESS_TYPE_MODULE);
        
        $filterChain->run();
    }    


    /**
    *   Возвращает данные модуля 
    */ 
    public function getData($extension_copy, $only_PK = false){

        ParticipantModel::checkCorrectParticipantUsersIfExistParentModule(
            Yii::app()->request->getParam('pci'),
            Yii::app()->request->getParam('pdi')
        );

        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $search = new Search();
        $search->setTextFromUrl();
        $filters = new Filters();
        $filters->setTextFromUrl();


        //*********************
        // *** get data
        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();
            
        $data_model->addSelect('if({{participant}}.ug_type = "'.ParticipantModel::PARTICIPANT_UG_TYPE_USER.'", (SELECT concat(COALESCE(sur_name,""), " ", COALESCE(first_name,""), " ", COALESCE(father_name,"")) as participant_title FROM {{users}} WHERE {{users}}.users_id = participant.ug_id), "") as bl_participant');
            
        //filters
        if(!$filters->isTextEmpty()){
            $filter_data = $filter_controller->getParamsToQuery($extension_copy, $filters->getText());
            if(!empty($filter_data))
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }
        //order
        Sorting::getInstance()->setParamsFromUrl();
        $data_model->setOrder($data_model->getOrderFromSortingParams());


        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();

        $data_model->setParticipantParentModule(
            Yii::app()->request->getParam('pci'),
            Yii::app()->request->getParam('pdi')
        );
        
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

        $data = $data_model->findAll();

        return $data;
    }




    /**
    *   Возвращает все данные для отображения listView 
    */ 
    public function getDataForView($extension_copy, $only_PK=false){
        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        if($this->this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            $exception_params_list = array('type'=>'relate_dinamic');
        } else {
            $exception_params_list = array('type'=>'module');
        }

        $data = array();
        $data['extension_copy'] = $extension_copy;

        $data['submodule_data'] = $this->getData($extension_copy);

        // обработка пагинации
        if($this->_set_pagination){
            $this->_set_pagination = false;
            \Pagination::getInstance()->setItemCount();

            // если страница пагинации указан больше чем есть в действительности
            if(\Pagination::switchActivePageIdLarger()){
                $data['submodule_data'] = $this->getData($extension_copy);
            }
        };



        $data['submodule_schema_parse'] = $extension_copy->getSchemaParse(array(), $exception_params_list, array(), false);
        
        $filters = Filters::getInstance()->setTextFromUrl()->getText();
        $data['filter_menu_list'] = $filter_controller->menuList($extension_copy, $filters);
        $data['filters_installed'] = (is_array($filters) ? $filter_controller->filtersInstalled($extension_copy, $filters) : "");
        $data['finished_object'] = Yii::app()->request->getParam('finished_object');
        
        $data['dnt_card_add_class'] = 'edit_view_dnt-add';
        if($this->add_inline_data)
            $data['dnt_card_add_class'] = 'inline_dnt-add';
        else if($this->module->isTemplate($extension_copy) && $this->this_template == EditViewModel::THIS_TEMPLATE_MODULE)
            $data['dnt_card_add_class'] = 'edit_view_select_dnt-add';
        
        return $data;
    }




 
    /**
    * Возвращает (базовую) форму ListView
    */
    public function actionShow(){
        ViewList::setViews(array('site/listView' => '/site/list-view'));
        ViewList::setViews(array('ext.ElementMaster.ListView.Elements.TData.TData' => 'Participant.extensions.ElementMaster.ListView.Elements.TData.TData'));
        
        $this->data = array_merge($this->data, $this->getDataForView($this->module->extensionCopy));
        $this->setMenuMain();
        
        History::getInstance()->updateUserStorageFromUrl(
                                    $this->module->extensionCopy->copy_id,
                                    'listView',
                                    false,
                                    \Yii::app()->request->getParam('pci'),
                                    \Yii::app()->request->getParam('pdi')
                                );
        History::getInstance()->updateUserStorageFromUrl(
                                    array('destination' => 'listView', 'copy_id' => $this->module->extensionCopy->copy_id),
                                    null,
                                    null,
                                    \Yii::app()->request->getParam('pci'),
                                    \Yii::app()->request->getParam('pdi')
                                );

        $this->renderAuto(ViewList::getView('site/listView'), $this->data);
    }







    /**
    * Копирование данных субмодуля в ListView
    */
    public function actionCopy($copy_id)
    {
        $validate = new Validate();

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        if(!empty($_POST['id'])){
            if(!is_array($_POST['id'])){
                $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
                $result = array(
                            'status' => false,
                            'messages' => $validate->getValidateResultHtml(),
                );
            } else {
                $result = EditViewCopyModel::getInstance($extension_copy)
                                ->copy($_POST['id'], $extension_copy, true, null)
                                ->getResult();
            }
        } else { 
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                        'status' => false,
                        'messages' => $validate->getValidateResultHtml(),
            );
        }
        return $this->renderJson($result);
    }
    
    
    
    
    
    
    

    public function actionAddParticipantUsers()
    {
        if(Yii::app()->request->isPostRequest && Yii::app()->request->isAjaxRequest) {
            $this->SaveParticipantUsers();
        } else {

            $extension_copy = ExtensionCopyModel::model()->findByPk(1);
            $extension_copy->getModule(null);

            $parent_m = ModuleTablesModel::getParentModuleInfo(Yii::app()->request->getParam('pci'), Yii::app()->request->getParam('pdi'));

            if(!empty($parent_m)){
                $users_in_p = ParticipantModel::getParticipants(
                            $parent_m['pci'],
                            $parent_m['pdi']
                );

                if(!empty($users_in_p)) {

                    $users_in_p_id = array_keys(CHtml::listData($users_in_p, 'ug_id', ''));

                    $users = UsersModel::model()
                        ->activeUsers()
                        ->scopesThisTemplate(false)
                        ->scopesUserIdIn($users_in_p_id)
                        ->findAll();

                }

            }

            if(!isset($users)) {
                $users = UsersModel::model()
                        ->activeUsers()
                        ->scopesThisTemplate(false)
                        ->findAll();
            }

            if(!$users || !count($users)){
                return;
            }

            $users_in = ParticipantModel::getParticipants(
                Yii::app()->request->getParam('pci'),
                Yii::app()->request->getParam('pdi')
            );

            $users_in_id = array_keys(CHtml::listData($users_in,'ug_id',''));
            foreach($users as $ikey => $user){
                if(in_array($user->getPrimaryKey(), $users_in_id)){
                    unset($users[$ikey]);
                }
            }

            return $this->renderPartial('/site/list-participant',
                array(
                    'parent_copy_id' => Yii::app()->request->getParam('pci'),
                    'parent_data_id' => Yii::app()->request->getParam('pdi'),
                    'users' => $users
                )
            );

        }

    }


    private function SaveParticipantUsers(){
        $aIds = Yii::app()->request->getPost('data_id_list');
        if($aIds) {
            foreach($aIds as $iId){
                $oModel = new ParticipantModel();
                $oModel->responsible = 0;
                $oModel->setAttribute('copy_id', Yii::app()->request->getPost('pci'));
                $oModel->setAttribute('data_id', Yii::app()->request->getPost('pdi'));
                $oModel->setAttribute('ug_id',   $iId);
                $oModel->setAttribute('ug_type', ParticipantModel::PARTICIPANT_UG_TYPE_USER);

                if(!$oModel->save()){
                    return $this->renderJson(array(
                        'error' => Yii::t('ParticipantModule.base','Participants can`t add')
                    ));
                }
            }

        } else {
            return $this->renderJson(array(
                'error' => Yii::t('message','It should be noted entries')
            ));
        }

    }
 
   

}
