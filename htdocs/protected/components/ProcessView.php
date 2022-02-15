<?php
/**
* ProcessView  
* @author Alex R.
* @version 1.0
*/ 


class ProcessView extends Controller {
    
    

    /**
     * filter
     */

    public function filters(){
        return array(
            'checkAccess',
        );
    }  


    
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
                /*
                if( array_key_exists('pci', $_GET) || array_key_exists('pdi', $_GET)) // если модуль открыт как подчиненный родительского
                    $this->redirect(Yii::app()->createUrl('/module/listView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')); 
                    //throw new CHttpException(404);

                */

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
                /*
                if(array_key_exists('pci', $_GET) || array_key_exists('pdi', $_GET)) // если модуль открыт как подчиненный родительского
                    $this->redirect(Yii::app()->createUrl('/module/listView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')); 
                    //throw new CHttpException(404);
                if(!Yii::app()->controller->module->isTemplate())
                    $this->redirect(Yii::app()->createUrl('/module/listView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                */
                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false)){
                    throw new CHttpException(404);
                }

                break;
            case 'copy':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'getTodoList' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'update':
            case 'panelSort':
            case 'cardSort':
            case 'panelSortDelete':
            case 'saveSecondFieldView':
            case 'getPanel':
            case 'editPanelTitle':
            case 'savePanelTitle':
            case 'panelMenuActionRun':
            case 'getHtmlEditPanelTitle' :
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
    

    
    
    
    /**
    *   Возвращает данные для обновления 
    */
    private function getUpdateData($extension_copy, $fields){
        $values = array();
        $values_relate = array();
        $values_relate_participant = '';        
        $values_file = array();
        
        foreach($fields as $field_name => $value){
            $params = $extension_copy->getFieldSchemaParams($field_name);
            if($params['params']['type'] == 'relate'){
                $values_relate[$params['params']['relate_module_copy_id']] = ($value === '' ? null : $value); 
            } elseif($params['params']['type'] == 'relate_participant'){
                $values_relate_participant = ($value === '' ? '' : (integer)$value);
            } elseif($params['params']['type'] == 'file' || $params['params']['type'] == 'file_image'){ 
                $values_file[$field_name] = ($value === '' ? null : (string)$value);
            } elseif($params['params']['type'] == 'numeric'){ 
                $values[$field_name] = ($value === '' ? null : (float)$value);
            } elseif($params['params']['type'] == 'logical'){ 
                $values[$field_name] = ($value === '' ? '' : (integer)$value);
            } elseif($params['params']['type'] == 'select'){ 
                $values[$field_name] = ($value === '' ? null : (integer)$value);
            } else {
                $values[$field_name] = ($value === '' ? '' : (string)$value);
            }
        }        
        
        return array(
            'values' => $values,
            'values_relate' => $values_relate,
            'values_relate_participant' => $values_relate_participant,            
            'values_file' => $values_file,
        );
    }    
    
    
    


    
    
    /**
    * обноление данных модуля (при перетаскивании)
    */ 
    public function actionUpdate($copy_id){
        $validate = new Validate();

        if(empty($copy_id) || empty($_POST['data_id_list']) || empty($_POST['fields_group_values'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        $data = $this->getUpdateData($extension_copy, $_POST['fields_group_values']);

        $data_id_list = array_unique($_POST['data_id_list']);
        foreach($data_id_list as $key => $id){
            $data_id_list[$key] = (int)$id;
        }

        $todo_list = array();

        $data_id_list_saved = [];

        foreach($data_id_list as $data_id){
            $alias = 'evm_' . $extension_copy->copy_id;
            $dinamic_params = array(
                'tableName'=> $extension_copy->getTableName(null, false)
            );

            $model = EditViewModel::modelR($alias, $dinamic_params)->findByPk($data_id);
            if($model == false){
                continue;
            }
            $model->extension_copy = $extension_copy;
            $model->setElementSchema($extension_copy->getSchemaParse());

            //todo list
            if($copy_id == ExtensionCopyModel::MODULE_TASKS){
                if($model->todo_list !== null){
                    $todo_list[] = $model->todo_list;
                }
            }

            if(count($data['values']) > 0) $model->setMyAttributes($data['values']);



            $model->setScenario('process_view_update');
            // сохраняем
            if($model->save()){
                $model->refresh();
                $data_id_list_saved[] = $data_id;

                // files
                if(count($data['values_file']) > 0){
                    $model->updateFiles($data['values_file']);
                }

                // responsible
                if($data['values_relate_participant']){
                    $model->updateResponsible($data['values_relate_participant']);
                }


                // обновление relate
                if(count($data['values_relate'])> 0){
                    $primary_entities  = array('primary_pci' => null, 'primary_pdi' => null);

                    $data['values_relate'] = $this->setAutoPrimaryRelateValue($extension_copy, $model, $data['values_relate']);

                    foreach($data['values_relate'] as $relate_copy_id => $value){

                        $reloader_status = $this->getRelateAttrReloader($extension_copy, $relate_copy_id, $_POST['this_template']);
                        if($reloader_status == EditViewRelateModel::RELOADER_STATUS_PARENT){
                            $primary_entities['primary_pci'] = $relate_copy_id;
                            $primary_entities['primary_pdi'] = $value;
                        }

                        //копируем данные связаных таблиц
                        $relate_module_table = ModuleTablesModel::model()->findAll(array(
                                                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                                                                            'params' => array(':copy_id' => $copy_id, ':relate_copy_id'=>$relate_copy_id)
                                                                        )
                                                                    );
                        if(!empty($relate_module_table) && is_array($relate_module_table)){
                            foreach($relate_module_table as $relate_value){
                                $relate_data = DataModel::getInstance()
                                                                ->setFrom('{{' . $relate_value->table_name . '}}')
                                                                ->setWhere($relate_value->parent_field_name . '=:parent_field_name', array('parent_field_name'=>$model->primaryKey))
                                                                ->findAll();
                                if(empty($value))
                                    DataModel::getInstance()->Delete('{{' . $relate_value->table_name . '}}',
                                                                 $relate_value->parent_field_name . '=:parent_field_name',
                                                                 array('parent_field_name'=>$model->primaryKey));
                                elseif(!empty($relate_data))
                                    DataModel::getInstance()->Update('{{' . $relate_value->table_name . '}}',
                                                                 array($relate_value->relate_field_name => $value),
                                                                 $relate_value->parent_field_name . '=:parent_field_name',
                                                                 array('parent_field_name' => $model->primaryKey));
                                else
                                    DataModel::getInstance()->Insert('{{' . $relate_value->table_name . '}}',
                                                                 array($relate_value->parent_field_name => $model->primaryKey, $relate_value->relate_field_name => $value));
                            }
                        }
                        $this->updatePrimaryRelateValue($reloader_status, $primary_entities, $model);
                    }
                }
                
            }
        }

        if($data_id_list_saved){
            $fields_group = \Yii::app()->request->getParam('fields_group');
            if($fields_group){
                $fields_group  = array_flip($fields_group);
                foreach($fields_group as &$value){
                    $value = null;
                }
            }
            $_GET['sort'] = ($fields_group ? json_encode($fields_group) : null);

            $global_params = array(
                'pci' => \Yii::app()->request->getParam('pci'),
                'pdi' => \Yii::app()->request->getParam('pdi'),
                'data_id_list' => $data_id_list_saved,
                'sorting_list_id' => \Yii::app()->request->getParam('sorting_list_id'),
                'finished_object' => \Yii::app()->request->getParam('finished_object'),
            );

            $global_params = $global_params;

            $panel_data = \DataListModel::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setThisTemplate($this->this_template)
                ->setGlobalParams($global_params)
                ->setModule($this->module)
                ->prepare(\DataListModel::TYPE_PROCESS_VIEW)
                ->getData();



            if($panel_data){
                $vars = [
                    'extension_copy' => $extension_copy,
                    'panel_data' => $panel_data,
                    'process_view_index' => '',
                    'data_id_list' => $data_id_list_saved,
                ];

                $panels = (new ProcessViewBuilder())
                            ->setExtensionCopy($vars['extension_copy'])
                            ->setPci(\Yii::app()->request->getParam('pci'))
                            ->setPdi(\Yii::app()->request->getParam('pdi'))
                            ->setThisTemplate($this->this_template)
                            ->setFinishedObject(\Yii::app()->request->getParam('finished_object'))
                            ->setModuleThisTemplate($this->module->isTemplate($vars['extension_copy']))
                            ->setPanelData($vars['panel_data'])
                            ->setAutoProcessViewIndex($this)
                            ->setBlockFieldData()
                            ->setJsContentReloadAddVars(true)
                            ->setAppendCardsHtmlToPanel(false)
                            ->setDataIdList($data_id_list_saved)
                            ->prepare()
                            ->getPanelList(true);

            }
        }

        if($validate->error_count > 0)
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        else {
            return $this->renderJson(array(
                'status' => true,
                'panels' => (!empty($panels) ? $panels : ''),
            ));
        }        
        
        
    }




    
    /**
     * поиск copy_id первичного модуля
     */
    private function getRelateAttrReloader($extension_copy, $relate_module_copy_id, $this_template){
        $schema['params']['relate_module_copy_id'] = $relate_module_copy_id;
        $relate_model = EditViewRelateModel::getInstance()
                        ->setVars(array('extension_copy' => $extension_copy, 'schema' => $schema, 'this_template' => $this_template, 'extension_data' => null))
                        ->setRelateExtensionCopy()
                        ->setPci()
                        ->setPdi();
                        
        $relate_model->getOptionDataParams();
        
        return $relate_model->getReloaderStatus();
    }
    
    
    
    
    /**
     * поиск первичного поля, установка значения в массив полей relate
     */
    private function setAutoPrimaryRelateValue($extension_copy, $model, $values_relate){
        $primary_schema = $extension_copy->getFieldSchemaParamsByType('relate');
        if(empty($primary_schema)) return $values_relate;
        
        $primary_copy_id = $primary_schema['params']['relate_module_copy_id'];
        $reloader_status = $this->getRelateAttrReloader($extension_copy, $primary_copy_id, $_POST['this_template']);
        if($reloader_status != EditViewRelateModel::RELOADER_STATUS_PARENT) return $values_relate;
        
        if(array_key_exists($primary_copy_id, $values_relate)) return $values_relate;

        foreach($values_relate as $relate_copy_id => $value){
            if(empty($value)){
                $result = Helper::arrayMerge(array($primary_copy_id => null), $values_relate);
                return $result;                                  
            }
            //данные связаных таблиц
            $relate_module_table = ModuleTablesModel::model()->find(array(
                                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                                                                'params' => array(':copy_id' => $extension_copy->copy_id, ':relate_copy_id'=>$relate_copy_id)
                                                            )
                                                        );
            // Id записи первого связаного поля
            $relate_data = DataModel::getInstance()
                                            ->setFrom('{{' . $relate_module_table->table_name . '}}')
                                            ->setWhere($relate_module_table->relate_field_name . '=:relate_field_name', array('relate_field_name' => $value))
                                            ->findRow();
            if(count($relate_data) <= 1) return $values_relate;

            //данные связаных таблиц
            $relate_module_table_2 = ModuleTablesModel::model()->find(array(
                                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                                                                'params' => array(':copy_id' => $extension_copy->copy_id, ':relate_copy_id'=>$primary_copy_id)
                                                            ));
            // Id первичной записи                
            $relate_data = DataModel::getInstance()
                                            ->setFrom('{{' . $relate_module_table_2->table_name . '}}')
                                            ->setWhere($relate_module_table_2->parent_field_name . '=:parent_field_name', array('parent_field_name' => $relate_data[$relate_module_table->parent_field_name]))
                                            ->findRow();
            if(empty($relate_data)) return $values_relate;
            
            $result = Helper::arrayMerge(array($primary_copy_id => $relate_data[$relate_module_table_2->relate_field_name]), $values_relate);
            return $result;                                  
        }
    }




    /**
     * обновление значений связаных полей при наличии первичного поля
     */
    private function updatePrimaryRelateValue($reloader_status, $primary_entities, $model){
        switch($reloader_status){
            case EditViewRelateModel::RELOADER_STATUS_PARENT :
                $model->miss_first_module = false;
                $model
                    ->setPrimaryEntities($primary_entities)
                    ->setPrimaryEntityChanged(true)
                    ->updateAllPrimaryModelValue();
                break;
        }        
    }









    /**
    *   Возвращает сгрупированные данные поля модуля 
    */
    protected function getData($extension_copy){
        $global_params = array(
            'pci' => \Yii::app()->request->getParam('pci'),
            'pdi' => \Yii::app()->request->getParam('pdi'),
            'finished_object' => \Yii::app()->request->getParam('finished_object'),
            'data_id_list' => \Yii::app()->request->getParam('data_id_list'),
            'sorting_list_id' => \Yii::app()->request->getParam('sorting_list_id'),
        );

        $flush_empty_panels = true;
        if(\Yii::app()->request->getParam('process_view_load_panels')){
            $flush_empty_panels = false;
        }

        $data = \DataListModel::getInstance()
                        ->setExtensionCopy($extension_copy)
                        ->setFinishedObject($this->module->finishedObject())
                        ->setThisTemplate($this->this_template)
                        ->setGlobalParams($global_params)
                        ->setModule($this->module)
                        ->setProcessViewFlushEmptyPanels($flush_empty_panels)
                        ->prepare(\DataListModel::TYPE_PROCESS_VIEW)
                        ->getData();


        ProcessViewSortingListModel::getInstance(true)
            ->setGlobalVars([
                '_extension_copy' => $extension_copy,
                '_pci' => \Yii::app()->request->getParam('pci'),
                '_pdi' => \Yii::app()->request->getParam('pdi'),
                '_finished_object' => \Yii::app()->request->getParam('finished_object'),
                '_this_template' => $this->this_template,
            ]);

        if(\Yii::app()->request->getParam('process_view_load_panels') == false && empty($data)){
            ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
        }


        // возникает, если было изменено значени поля, по которому произошла сортировка
        /*
        if(\Yii::app()->request->getParam('process_view_load_panels') && empty($data)){
            ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
        }
        */


        return $data;
    }


    /**
    *   Возвращает все данные для отображения processView
    */ 
    private function getDataForView($extension_copy){
        Search::getInstance()->setTextFromUrl();

        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');


        $data = array();
        $data['extension_copy'] = $extension_copy;
        $data['panel_data'] = $this->getData($extension_copy);



        $data['fields_view'] = $this->getFieldsView();
        $data['process_view_index'] = 'processView_' . $extension_copy->copy_id;

        if($this->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS) {
            if ($this->module->view_related_task) {
                $data['process_view_index'] .= TasksModule::$relate_store_postfix_params;
            }
        }


        $fields_group = Sorting::getInstance()->getParamFieldName();
        if(empty($fields_group)) $fields_group = null;
        $data['fields_group'] = $fields_group;        
        
        $filters = Filters::getInstance()->setTextFromUrl()->getText();

        $data['filter_menu_list_virual'] = $filter_controller->menuListVirtualFilters($extension_copy, $filters);
        $data['filter_menu_list'] = $filter_controller->menuList($extension_copy, $filters);
        $data['filters_installed'] = (is_array($filters) ? $filter_controller->filtersInstalled($extension_copy, $filters) : "");

        $data['finished_object'] = Yii::app()->request->getParam('finished_object');
                
        return $data;
    }



    /**
    *   Возвращает фильтр
    */
    private function getFieldsView(){
        if(isset($_GET['fields_view']))
            return explode(',', $_GET['fields_view']);
    }

    




    // запись в хранилище данных о отображении второго поля
    public function actionSaveSecondFieldView($copy_id){
        $result = ProcessViewModel::getInstance()
                            ->setExtensionCopy(\ExtensionCopyModel::model()->findByPk($copy_id))
                            ->setPci(\Yii::app()->request->getParam('pci'))
                            ->setPdi(\Yii::app()->request->getParam('pdi'))
                            ->setThisTemplate(\Yii::app()->request->getParam('this_template', false))
                            ->setFinishedObject(\Yii::app()->request->getParam('finished_object'))
                            ->saveSecondFieldView(Yii::app()->request->getPost('index'), Yii::app()->request->getPost('fields_view'))
                            ->getResult();

        return $this->renderJson($result);
    }




    /**
     * Возвращает (базовую) форму ProcessView
     */
    public function actionShow(){
        //$date = date('H:i:s');
        if(!SchemaOperation::getInstance()->beProcessViewGroupParam($this->module->extensionCopy->getSchemaParse())){
            $this->redirect(Yii::app()->createUrl('/module/listView/show/' . $this->module->extensionCopy->copy_id));
        }

        $data = $this->getDataForView($this->module->extensionCopy);
        $this->data = array_merge($this->data, $data);

        if(\Yii::app()->request->getParam('process_view_load_panels')){
            $this->data['data_id_list'] = \Yii::app()->request->getPost('data_id_list');
            $panels = \DataListModel::getProcessViewListByDataIdList($this, $this->data);

            $this->renderJson(array(
                'status' => true,
                'panels' => $panels,
            ));
        } else {
            $this->setMenuMain();
            History::getInstance()->updateUserStorageFromUrl(
                                        $this->module->extensionCopy->copy_id,
                                        'processView',
                                        false,
                                        \Yii::app()->request->getParam('pci'),
                                        \Yii::app()->request->getParam('pdi')
                                    );
            History::getInstance()->updateUserStorageFromUrl(
                                        array('destination' => 'processView', 'copy_id' => $this->module->extensionCopy->copy_id),
                                        null,
                                        null,
                                        \Yii::app()->request->getParam('pci'),
                                        \Yii::app()->request->getParam('pdi')
                                    );

            $this->renderAuto(ViewList::getView('site/processView'), $this->data);
        }
    }



    /**
     * Возвращает (базовую) форму-шаблон ProcessView
     */
    public function actionShowTemplate(){
        $this->this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE;
        $this->actionShow();
    }





    /**
     * Сортировка панелей
     */
    public function actionPanelSort($copy_id){
        $status = \ProcessViewSortingListModel::getInstance()
                        ->setGlobalVars([
                            '_extension_copy' => \ExtensionCopyModel::model()->findByPk($copy_id),
                            '_pci' => Yii::app()->request->getParam('pci'),
                            '_pdi' => Yii::app()->request->getParam('pdi'),
                            '_finished_object' => \Yii::app()->request->getParam('finished_object'),
                            '_this_template' => \Yii::app()->request->getParam('this_template'),
                        ])
                        ->updatePanelSort([
                                'sorting_list_id_before' => Yii::app()->request->getParam('sorting_list_id_before'),
                                'sorting_list_id' => Yii::app()->request->getParam('sorting_list_id'),
                            ]);

        return $this->renderJson(array(
            'status' => $status,
        ));
    }


    /**
     * Сортировка карточек
     */
    public function actionCardSort($copy_id){
        $status = (new ProcessViewSortingCardsModel())
            ->setGlobalVars([
                '_extension_copy' => \ExtensionCopyModel::model()->findByPk($copy_id),
                '_sorting_list_id' => Yii::app()->request->getParam('sorting_list_id'),
                '_pci' => Yii::app()->request->getParam('pci'),
                '_pdi' => Yii::app()->request->getParam('pdi'),
            ])
            ->updateCardsSort([
                'sorting_cards_id_before' => Yii::app()->request->getParam('sorting_cards_id_before'),
                'sorting_cards_id_list' => Yii::app()->request->getParam('sorting_cards_id_list'),
            ]);

        return $this->renderJson(array(
            'status' => $status,
        ));
    }


    /**
     * Удаление индекса сортировки панели
     */
    public function actionPanelSortDelete(){
        $add_panel = \ProcessViewModel::getInstance()
                            ->checkAddNewPanel(['sorting_list_id'=>\Yii::app()->request->getParam('sorting_list_id')]);

        $status = \ProcessViewSortingListModel::getInstance()
                            ->deletePanel(\Yii::app()->request->getParam('sorting_list_id'), true, false);

        return $this->renderJson([
            'status' => $status,
            'add_panel' => $add_panel,
        ]);
    }






    /**
     * Возвращает чистую панель
     */
    public function actionGetPanel($copy_id){
        $panels_list = (new ProcessViewBuilder())
            ->setExtensionCopy(\ExtensionCopyModel::model()->findByPk($copy_id))
            ->setPci(\Yii::app()->request->getParam('pci'))
            ->setPdi(\Yii::app()->request->getParam('pdi'))
            ->setThisTemplate(\Yii::app()->request->getParam('this_template', false))
            ->setFinishedObject(\Yii::app()->request->getParam('finished_object'))
            ->setFieldsGroup(\Yii::app()->request->getParam('fields_group'))
            ->setModuleThisTemplate($this->module->isTemplate())
            ->setProcessViewIndex('process_view_' . $copy_id)
            ->setBlockFieldData()
            ->setAppendCardsHtmlToPanel(false)
            ->setLoadCards(false)
            ->prepare()
            ->getPanelList();


        $html = '';
        if($panels_list){
            $html = implode($panels_list);
        }

        return $this->renderJson(array(
            'status' => true,
            'html' => $html,
        ));
    }








    /**
     * сохранение названия отредактированого названия списка
     */
    public function actionSavePanelTitle($copy_id){
        if(empty($_POST['fields_data_list'])){
            return $this->renderJson(array(
                'status' => false,
                'messages' => (new Validate())->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'))->getValidateResultHtml(),
            ));
        }

        $result = \ProcessViewSortingListModel::getInstance()
                        ->setGlobalVars([
                            '_extension_copy' => \ExtensionCopyModel::model()->findByPk($copy_id),
                            '_pci' => \Yii::app()->request->getParam('pci'),
                            '_pdi' => \Yii::app()->request->getParam('pdi'),
                            '_finished_object' => \Yii::app()->request->getParam('finished_object'),
                            '_this_template' => \Yii::app()->request->getParam('this_template'),
                        ])
                        ->saveTitle([
                            'sorting_list_id' => \Yii::app()->request->getParam('sorting_list_id'),
                            'fields_data_list' => \Yii::app()->request->getParam('fields_data_list'),
                        ]);

        return $this->renderJson(array(
            'status' => true,
            ) + $result
        );
    }





    /**
     * actionPanelMenuActionRun - исполняет действие из меню списка
     * @param $copy_id
     * @return string
     */
    public function actionPanelMenuActionRun($copy_id){
        $result = \ProcessViewSortingListModel::getInstance()
            ->setGlobalVars([
                '_extension_copy' => \ExtensionCopyModel::model()->findByPk($copy_id),
                '_pci' => \Yii::app()->request->getParam('pci'),
                '_pdi' => \Yii::app()->request->getParam('pdi'),
                '_finished_object' => \Yii::app()->request->getParam('finished_object'),
                '_this_template' => \Yii::app()->request->getParam('this_template'),
            ])
            ->panelMenuActionRun([
                'run_action' => \Yii::app()->request->getParam('run_action'),
                'sorting_list_id' => \Yii::app()->request->getParam('sorting_list_id'),
            ])
            ->getResult();

        $this->renderJson($result);
    }




    /**
     * возвращает обьекты для редактирования названия списка
     */
    public function actionGetHtmlEditPanelTitle(){
        $validate = new Validate();

        if(empty($_POST['fields_data'])) $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
        if($validate->error_count > 0){
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }


        $extension_copy = ExtensionCopyModel::model()->findByPk($this->module->extensionCopy->copy_id);
        $process_view_builder = new ProcessViewPanelEditBuilder();
        $process_view_builder->setExtensionCopy($extension_copy);

        $fields_data = array();
        foreach($_POST['fields_data'] as $data){
            $fields_data[] = $data;
            $fields_data[count($fields_data)-1]['text'] = $process_view_builder->getPanelTitleValue($data['field_name'], $data['value']);
        }


        $html = $process_view_builder
            ->setFieldsData($fields_data)
            ->buildElemenets();

        return $this->renderJson(array(
            'status' => true,
            'html' => $html,
        ));
    }






    /**
     * actionGetTodoList - возвращает список Todo листов
     */
    public function actionGetTodoList($copy_id){
        $result = array(
            'status' => false
        );

        return $this->renderJson($result);
    }






    public function getSwitchIconList($extension_copy){
        $icon_list = [];

        $crm_properties = [
            '_active_object' => $this,
            '_extension_copy' => $extension_copy,
        ];

        if($this->showListView()){
            if($this->module->list_view_icon_show['switch_to_pv']){
                $icon_list[] = [
                    'data-action_key' => null,
                    'data-type' => null,
                    'class' => null,
                    'i_class' => 'fa fa-bars active',
                ];
            }

            if($this->module->list_view_icon_show['switch_to_lv']){
                $icon_list[] = [
                    'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(array('module' => array('destination' => 'listView')))->prepare()->getKey(),
                    'data-type' => null,
                    'class' => 'ajax_content_reload',
                    'i_class' => 'fa fa-th-list',
                ];
            }


            if($extension_copy->isCalendarView() && $this->module->list_view_icon_show['switch_to_cv']){
                $icon_list[] = [
                    'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(array('module' => array('destination' => 'calendarView')))->prepare()->getKey(),
                    'data-type' => 'calendar',
                    'class' => 'ajax_content_reload element',
                    'i_class' => 'fa fa-calendar',
                ];
            }
        }

        if(count($icon_list) == 1){
            $icon_list = [];
        }

        return $icon_list;
    }




}
