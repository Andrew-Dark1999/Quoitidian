<?php
/**
* ListView  
* @author Alex R.
* @version 1.0
*/ 


class ListView extends \Controller {
    
    
    protected $add_inline_data = false;

    protected $_set_pagination = false;


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
                if($this->showListView() == false){
                    $this->redirect(Yii::app()->createUrl('/module/processView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                }

                if($this->module->extensionCopy->getIsTemplate() == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
                   \Yii::app()->request->getParam('pci') == false &&
                   \Yii::app()->request->getParam('pdi') == false
                ){
                    $this->redirect(Yii::app()->createUrl('/module/listView/showTemplate') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
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
                $r = $this->showListView();
                if($r == false){
                    $this->redirect(Yii::app()->createUrl('/module/processView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                }

                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if(ValidateRules::checkIsSetParentDataModule() == false){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if(\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false)){
                    throw new CHttpException(404);
                }

                break;

            case 'loadInlineElements':
                if(
                    Yii::app()->controller->module->inline_edit_enable == false ||
                    !Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)
                ){
                    $this->renderJson([
                        'status' => false,
                    ]);
                    return false;
                }
                break;
            case 'copy':
            case 'copyForSubModule' :
            case 'addNewProcessesSubModule' :
            case 'changeTemplateValue' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'delete' :
            case 'deleteFromSubModule' :
                /*
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                */
                break;
            case 'import' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_IMPORT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'print' :
            case 'export' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EXPORT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    echo Yii::t('messages', 'You do not have access to this object');
                    return false;
                }
                break;
        }
        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);
        
        $filterChain->run();
    }





    protected function getData($extension_copy, $only_PK=false){
        $global_params = array(
            'pci' => \Yii::app()->request->getParam('pci', null),
            'pdi' => \Yii::app()->request->getParam('pdi', null),
            'finished_object' => \Yii::app()->request->getParam('finished_object', null),
        );

        return \DataListModel::getInstance()
                        ->setExtensionCopy($extension_copy)
                        ->setFinishedObject($this->module->finishedObject())
                        ->setThisTemplate($this->this_template)
                        ->setGlobalParams($global_params)
                        ->setSortingToPk('desc')
                        ->setDefinedPK($only_PK)
                        ->prepare(\DataListModel::TYPE_LIST_VIEW)
                        ->getData();
    }

  
  

  
    /**
    *   Возвращает все данные для отображения listView 
    */ 
    public function getDataForView($extension_copy, $only_PK=false){
        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $data = array();
        $data['extension_copy'] = $extension_copy;
        $data['submodule_data'] = $this->getData($extension_copy, $only_PK);

        // обработка пагинации
        if($this->_set_pagination){
            $this->_set_pagination = false;
            \Pagination::getInstance()->setItemCount();

            // если страница пагинации указан больше чем есть в действительности
            if(\Pagination::switchActivePageIdLarger()){
                $data['submodule_data'] = $this->getData($extension_copy, $only_PK);
            }
        };

        if($this->this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            $exception_params_list = array('type'=>'relate_dinamic');
        } else {
            $exception_params_list = array('type'=>'module');
        }

        $data['submodule_schema_parse'] = $extension_copy->getSchemaParse(null, $exception_params_list);

        $filters = Filters::getInstance()->setTextFromUrl()->getText();
        $data['filter_menu_list_virual'] = $filter_controller->menuListVirtualFilters($extension_copy, $filters);
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
        $this->data = array_merge($this->data, $this->getDataForView($this->module->extensionCopy));
        $this->setMenuMain();

        History::getInstance()->updateUserStorageFromUrl(
                                        $this->module->extensionCopy->copy_id,
                                        'listView',
                                        false,
                                        \Yii::app()->request->getParam('pci'),
                                        \Yii::app()->request->getParam('pdi')
                                    ); // только  для  UsersStorageModel::TYPE_PAGE_PARAMS
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
    * Возвращает (базовую) форму-шаблон ListView
    */
    public function actionShowTemplate(){
        $this->this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE;
        
        $this->data = array_merge($this->data, $this->getDataForView($this->module->extensionCopy));
        $this->setMenuMain();

        History::getInstance()->updateUserStorageFromUrl(
                                    $this->module->extensionCopy->copy_id,
                                    'listView',
                                    true,
                                    \Yii::app()->request->getParam('pci'),
                                    \Yii::app()->request->getParam('pdi')
                                ); // только  для  UsersStorageModel::TYPE_PAGE_PARAMS
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
    *   Печать 
    */ 
    public function actionPrint($copy_id){
        $this->layout = '//layouts/print';

        if(empty($copy_id)) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);
        
        $data = $this->getDataForView($extension_copy);
        $data['col_hidden'] = array(); 
        if(isset($_GET['col_hidden']) && !empty($_GET['col_hidden'])){
            $data['col_hidden'] = explode(',', $_GET['col_hidden']);
        }

        $data['title_add_avatar'] = true;
        $data['filter_only_url'] = false;


        return $this->render(ViewList::getView('print/listView'), $data);
    }

    
   /**
    *   Выбор полей для экспорта 
    */ 
    public function actionSelectExportFields($copy_id){

        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);
        $extension_copy->setAddId();
        $schema = $extension_copy->getSchemaParse();

        $schema_fields = SchemaConcatFields::getInstance()
            ->setSchema($schema['elements'])
            ->setWithoutFieldsForListViewGroup($extension_copy->getModule()->getModuleName())
            ->parsing()
            ->prepareWithOutDeniedRelateCopyId()
            ->primaryOnFirstPlace(true)
            ->prepareWithConcatName()
            ->getResult();

        $fields = array();
        if(isset($schema_fields['header'])){
            foreach($schema_fields['header'] as $field){
                $flds = explode(',', $field['name']);
                $field['title'] = ListViewBulder::getFieldTitle(array('title'=>$field['title']) + $schema_fields['params'][$flds[0]]);
                $field['checked'] = (empty($schema_fields['params'][$flds[0]]['list_view_visible'])) ? false : true;
                $fields[] = $field;
            }
        }
        return $this->renderJson(array(
            'status' => 'popup',
            'data' => $this->renderPartial(ViewList::getView('dialogs/export'), array(
                'fields' => $fields,
                'type' => $_GET['type'],
            ), true),
        ));
    
    }
        
  
    /**
    *   Экспорт 
    */ 
    public function actionExport($copy_id){

        if(empty($copy_id)) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);
        $extension_copy->setAddId();
        
        $fields = (!empty($_GET['fields'])) ? json_decode($_GET['fields']) : false;
        $ids = (!empty($_GET['ids'])) ? json_decode($_GET['ids']) : false;
        
        if(!empty($_GET['all_checked']))
            $ids = false;
        
        switch($_GET['type']){
            case 'excel':
                $data = $this->getData($extension_copy, $ids);
                ExcelExport::getInstance()
                        ->setExtensionCopy($extension_copy)
                        ->setWidthColumn(json_decode($_GET['col_width'], true))
                        ->setWithOutGroupIndex($_GET['col_hidden'])
                        ->setSchema()
                        ->makeExcelFromListView($data, $fields)
                        ->setParams(ExcelExport::TYPE_EXCEL)
                        ->prepareDocument()
                        ->loadHtml();
                break;
            case 'pdf':
                ini_set('max_execution_time', 3600); // 1ч

                $data = $this->getDataForView($extension_copy, $ids);
                $data['col_hidden'] = array();
                $data['col_name_hidden'] = $fields;
                $data['title_add_avatar'] = false;
                $data['filter_only_url'] = true;

                if(isset($_GET['col_hidden']) && !empty($_GET['col_hidden'])){
                    $data['col_hidden'] = explode(',', $_GET['col_hidden']);
                }

                $mpdf=new mPDF('','', 0, '', 5, 5, 10, 5, 9, 9, 'L');
                $mpdf->AddPage('L'); 
                Yii::app()->controller->layout = '//layouts/print';
                $mpdf->WriteHTML(Yii::app()->controller->render(ViewList::getView('print/listView'), $data, true));
                $mpdf->output($extension_copy->title . '.pdf', 'D');
                break;
        }
    }




    /**
     *   Импорт
     */
    public function actionImport($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        
        $result = \ExcelImport::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setSchema()
                ->setThisTemplate((boolean)Yii::app()->request->getParam('this_template', false))
                ->setPciPdi(Yii::app()->request->getParam('pci', false), Yii::app()->request->getParam('pdi', false))
                ->onlyWithPK()
                ->import();

        $data = (!$result['status']) ? $result['message'] : $this->renderPartial(ViewList::getView('dialogs/import'), array(
                                                                'copy_id' => $copy_id,
                                                                'file' => $result['file'],
                                                                'skipped' => $result['skipped'],
                                                                'messages' => $result['messages'],
                                                            ), true);
        return $this->renderJson(array(
            'status' => $result['status'],
            'data' => $data,
        ));
    }
    

    /**
     *   Пост обработка данных импорта (замена или совмещение записей)
     */
    public function actionImportPostProccesing($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        \ExcelImport::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setSchema()
                ->setThisTemplate((boolean)Yii::app()->request->getParam('this_template', false))
                ->setPciPdi(Yii::app()->request->getParam('pci', false), Yii::app()->request->getParam('pdi', false))
                ->importPostProccesing($_POST['file'], $_POST['import_skipped'], $_POST['type']);
                
        return $this->renderJson(array(
            'status' => true,
        ));

    }
    
    
    /**
     *   Генерация файлов-шаблонов 
     */ 
    public function actionGenerate($copy_id){

        //показываем все ошибки, за исключением notice
        set_error_handler(array('ValidateDocuments', 'exception_error_handler'));
        //register_shutdown_function(array('ValidateDocuments', 'fatal_error_handler'));
        
        try {
            if(empty($copy_id) || empty($_POST['params'])) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
            
            $urlParams = json_decode($_POST['params']);

            if(empty($urlParams->service_data->upload_id) || empty($urlParams->service_data->parent_upload_id) || empty($urlParams->service_data->module_id) || empty($urlParams->service_data->module_generate_id))
                return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));

            
            $paramsModel = ParamsModel::model()->findByAttributes(array('title'=>'upload_path_module'));
            $uploadImportModel = UploadsModel::model()->findByPK($urlParams->service_data->parent_upload_id);
            $uploadExportModel = UploadsModel::model()->findByPK($urlParams->service_data->upload_id);
            
            if($paramsModel === null || $uploadImportModel===null || $uploadExportModel===null)
                return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));

            $importFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $paramsModel->value . DIRECTORY_SEPARATOR . $uploadImportModel->file_path . DIRECTORY_SEPARATOR . $uploadImportModel->filename;
            $exportFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $paramsModel->value . DIRECTORY_SEPARATOR . $uploadExportModel->file_path . DIRECTORY_SEPARATOR . $uploadExportModel->filename;
                   
            //предварительная проверка на СМ связи
            $checkSM = (isset($urlParams->sm_data_select)) ? false : true;
            
            if($checkSM) {

                $sm = \DocumentsGenerateModel::getInstance()->getDocumentSM($importFile, $copy_id, $urlParams->service_data->module_id, $urlParams->service_data->module_generate_id, $urlParams->sm_data);  

                $vars = $sm[0];
                
                if(count($sm[1])>0) {
                    
                    //найдены СМ записи, показываем диалоговое окно для выбора конкретной записи
                    
                    $data = array(
                        'copy_id'       => $copy_id,
                        'sm'            => $sm[1],
                        'vars'          => serialize($sm[0]),
                        'form_data'     => json_encode($urlParams->form_data),
                        'sdm_data'      => json_encode($urlParams->sdm_data),
                        'sm_data'       => json_encode($urlParams->sm_data),
                        'service_data'  => json_encode($urlParams->service_data),
                    );
                    
                    return $this->renderJson(array(
                        'status' => 'popup',
                        'data' => $this->renderPartial(ViewList::getView('dialogs/subModuleSelectGenerate'), $data, true),
                    ));
                
                }
            
            }else
                $vars = unserialize($urlParams->vars);
            
            $res = \DocumentsGenerateModel::getInstance()->generateDocument($vars, $uploadExportModel, $importFile, $exportFile, $copy_id, $urlParams->service_data->module_id, $urlParams->service_data->module_generate_id, $urlParams->form_data, $urlParams->sdm_data, @$urlParams->sm_data_new, $urlParams->service_data->doc_id);
            
            if(isset($res['link'])) {
                $exp = explode(DIRECTORY_SEPARATOR, $res['link']);
                $filename = array_pop($exp);
                $exp2 = explode('.', $filename);
                $fileext = array_pop($exp2);
            }
            
            echo CJSON::encode(array(
                'status'=>(isset($res['status'])) ? $res['status'] : false,
                'link'=>(isset($res['link'])) ? $res['link'] : false,
                'name'=>(isset($filename)) ? $filename : false,
                'title'=>(isset($res['title'])) ? $res['title'] : false,
                'extension'=>(isset($fileext)) ? $fileext : false,
                'filedate'=>(isset($res['filedate'])) ? $res['filedate'] : false,
                'show_edit_link'=>(isset($res['show_edit_link'])) ? $res['show_edit_link'] : false,
            ));
        
        } catch (Exception $e) {
            $this->renderTextOnly(ValidateDocuments::getMessage($e->getMessage()));
        }
            
    }
    
    
    /**
     *   Ручной запуск крона
     */ 
    public function actionDaily(){

        $deal_id = (!empty($_GET['deal_id'])) ? (int)$_GET['deal_id'] : false;
        $date = (!empty($_GET['date'])) ? $_GET['date'] : false;
        \AdditionalProccessingModel::getInstance()->daily($deal_id, $date, true);

    }
    
    
    /**
     *   Проверка на правильность заполнения формулы
     */ 
    public function actionFormulaCheck($copy_id){
        

        $status = true;
        $errors = array();
        
        //$_POST['field1'] = '=+1-1';

        if(!empty($_POST)) {
           foreach($_POST as $k=>$v) {
               $value = \Math::getInstance()
                    ->setOperatorAfterEqual(true)
                    ->setRules($v)
                    ->preparedExpression()
                    ->getCalculatedValue();
                    
               if($value === false) {
                   $status = false;
                   $errors []= $k;
               }
           }
        }

        return $this->renderJson(array(
            'status' => $status,
            'errors' => $errors,
            'data' => $this->renderPartial(ViewList::getView('dialogs/bulkEdit'), array(

            ), true),
        ));

    }

    
    /**
     * Возвращает данные для поля relate
     */
    private function getFindData($value, &$result){
        if(isset($value[0]) && is_array($value[0]))
            return $this->getFindData($value[0], $result);

        if(isset($value['value']) && !empty($value['value'])){
            if($result['value_concat'] === ''){
                $result['value_concat'] = $value['value'];
            } else {
                $result['value_concat'] .= ' ' . $value['value'];
            }
        }
        $result = $result;
        return $result;
    }
    
    



    /**
    * Копирование данных модуля в ListView
    */
    public function actionCopy($copy_id){
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
                                                ->setParentThisTemplate((boolean)Yii::app()->request->getParam('pci'))
                                                ->copy(array_reverse($_POST['id']), $extension_copy, true, null)
                                                ->createProcessAfterCreatedEntity()
                                                ->getResult();

                if($result['status'] == true && !empty($result['id'])){
                    // запись связей

                    // для сабмодулей
                    $parent_copy_id = Yii::app()->request->getParam('parent_copy_id');
                    $parent_data_id = Yii::app()->request->getParam('parent_data_id');
                    if(!empty($parent_copy_id)){
                        foreach($result['id'] as $id){
                            $this->createRelateLinks($extension_copy->copy_id, $parent_copy_id, $parent_data_id, $id, 'relate_module_many');                        
                        }
                    }
                    
                    // для listView, открытых через поле название
                    $parent_copy_id = Yii::app()->request->getParam('pci');
                    $parent_data_id = Yii::app()->request->getParam('pdi');
                    if(!empty($parent_copy_id)){
                        foreach($result['id'] as $id){
                            $this->createRelateLinks($extension_copy->copy_id, $parent_copy_id, $parent_data_id, $id, 'relate_module_one');
                        }
                    }
                    
                    //для автонумерации меняем название
                    if($extension_copy->copy_id != ExtensionCopyModel::MODULE_USERS || $extension_copy->copy_id != ExtensionCopyModel::MODULE_STAFF) {
                        $primary = $extension_copy->getPrimaryField();
                        if(!empty($primary) && isset($primary['params']['name']) && !empty($primary['params']['name_generate'])){
                            
                            // EditViewModel
                            $alias = 'evm_' . $extension_copy->copy_id;
                            $dinamic_params = array(
                                'tableName'=> $extension_copy->getTableName(null, false)
                            );
                            
                            //СДМ связь, для генерации названия
                            $module_tables = ModuleTablesModel::model()->findAll(array(
                                'condition' => "copy_id=:copy_id AND type='relate_module_one' AND relate_type='belongs_to'",
                                'params' => array(
                                    ':copy_id' => $extension_copy->copy_id
                                 )
                            ));
                            
                            $schema = $extension_copy->getSchemaParse();
                            
                            foreach($result['id'] as $id){
                                $edit_view_model = EditViewModel::modelR($alias, $dinamic_params)->findByPk($id);
                                unset($data);
                                if(!empty($edit_view_model->attributes)) {
                                    $data['EditViewModel'] = $edit_view_model->attributes;
                                    
                                    //добавляем также СДМ связь
                                    if(!empty($module_tables)) {
                                        foreach($module_tables as $relate_table){
                                            $data_model = new DataModel();
                                            $data_model
                                                ->setFrom('{{' . $relate_table->table_name . '}}')
                                                ->setWhere($relate_table->parent_field_name . '=:id', array(':id'=> $id));
                                            $data_model = $data_model->findRow();
                                            if(!empty($data_model[$relate_table->relate_field_name])) {
                                                if(!empty($schema['elements'])){
                                                    foreach($schema['elements'] as $element) {
                                                        if(!empty($element['field']['params']['name'])) {
                                                           if($element['field']['params']['relate_module_copy_id'] == $relate_table->relate_copy_id){
                                                               $data['element_relate'][]= array(
                                                                   'name' => 'EditViewModel[' . $element['field']['params']['name'] . ']',
                                                                   'relate_copy_id' => $relate_table->relate_copy_id,
                                                                   'id' => $data_model[$relate_table->relate_field_name],
                                                                );
                                                               break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    $auto_name = Fields::getInstance()->getNewRecordTitle($primary['params']['name_generate'], $primary['params']['name_generate_params'], $extension_copy, $data);
                                    if($auto_name !== false) {
                                        $edit_view_model->saveAttributes(array($primary['params']['name']=> $auto_name));
                                    }     
                                }
                            }
                        }
                    }
                }

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


    /**
    * Удаление данных из модуля
    */
    public function actionDelete($copy_id){
        $validate = new Validate();

        if(!empty($_POST['id'])){
            $result = EditViewDeleteModel::getInstance()
                            ->setThisTemplate(Yii::app()->request->getPost('this_template'))
                            ->prepare($copy_id, $_POST['id'])
                            ->delete()
                            ->getResult();
        } else {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                        'status' => false,
                        'messages' => $validate->getValidateResultHtml(),
            );
        }
        
        return $this->renderJson($result);
    }


   /**
    * Обновление данных
    */
    public function actionAdditionalUpdate($copy_id){
        if(!empty($_GET['scenario'])) {
            switch($_GET['scenario']){
                case 'additional_update':
                    return $this->renderJson(\AdditionalProccessingModel::getInstance()->additionalUpdate($copy_id, $_POST['id']));
                break;
                case 'table_sr_export':
                    \AdditionalProccessingModel::getInstance()->SRExport($copy_id, $_GET['id'], $_GET['all_checked']);
                break;
            }
        }
        
    
    }







    /****************************************************
    *
    *           SubModule
    *
    ****************************************************/
    




    /*
    public function actionAddNewProcessesSubModule(){
        $validate = new Validate();

        if(empty($_POST['copy_id']) || empty($_POST['data_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        } else{
            \EditViewModel::addNewProcessesForSubModule(\Yii::app()->request->getParam('copy_id'), \Yii::app()->request->getParam('data_id'));

            $result = array(
                'status' => true,
            );
        }

        return $this->renderJson($result);
    }
    */




    /**
    * добавление данных модуля в субмодуль
    */     
    public function actionAddCardsSubModule($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        
        $sub_module_validate = ValidateSubModule::getInstance()->check(array_merge($_POST, array('copy_id' => $extension_copy->copy_id)), ValidateConfirmActions::ACTION_SUB_MODULE_EDIT_VIEW_SELECT);
        if($sub_module_validate->beMessages())
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $sub_module_validate->getValidateResultHtml(),
            ));
        
        $sub_module_model = (new EditViewSubModuleModel())
                                    ->setVars($_POST)
                                    ->setExtensionCopy($extension_copy)
                                    ->prepareVars();

        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');
        $filters = $filter_controller->menuList($extension_copy, null, 'list-menu-sm');                            
                 
        return $this->renderJson(array(
            'status' => 'data',
            'html' => $this->renderPartial(ViewList::getView('dialogs/subModuleAddCards'),
                                           array(
                                            'parent_copy_id' => $_POST['parent_copy_id'],
                                            'parent_data_id' => $_POST['parent_data_id'],
                                            'extension_copy' => $extension_copy,
                                            'this_template' => (isset($_POST['this_template']) ? $_POST['this_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
                                            'relate_template' => (isset($_POST['relate_template']) ? $_POST['relate_template'] : EditViewModel::THIS_TEMPLATE_MODULE),
                                            'list_view_data' => $sub_module_model->getOptionsDataList(),
                                            'filters' => $filters,
                                            'vars' => $_POST,
                                           ), true),
        ));

    }


    /**
    * Загружаем шаблоны карточек по их блоку
    */     
    public function actionLoadTemplatesFromBlock($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        
        $add_where = $extension_copy->getTableName() . '.' . $_POST['block_field_name'] . '="' . $_POST['block_unique_index'] . '"';
        $templates = EditView::getTemplates($extension_copy, $add_where);
        
        $result = '';
        
        foreach($templates as $template){ 
            $result .='<option value="' .  $template['id'] . '">' . $template['module_title'] .'</option>';
        }
        
        return $this->renderJson(array(
            'status' => 'done',
            'templates' => $result,
        ));

    }


    /**
    * вставляем выбранные данные из формы выбора в Субмодуль 
    */     
    public function actionInsertCardInSubModule($copy_id){
        $validate = new Validate();
        $select_list = $_POST['select_list'];
        if(array_key_exists('relate_template', $_POST) && (boolean)$_POST['relate_template'] == true){
            if(empty($_POST['select_list'])){
                $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
                return $this->renderJson(array(
                    'status' => 'error',
                    'messages' => $validate->getValidateResultHtml(),
                ));
            
            }
            
            $this_template = null;
            $this_template_only_first = false;
            if( array_key_exists('this_template', $_POST) && $_POST['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE &&
                array_key_exists('relate_template', $_POST) && (boolean)$_POST['relate_template'] == true)
            {
                $this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM;
                $this_template_only_first = true;
            }
            elseif( array_key_exists('this_template', $_POST) && $_POST['this_template'] == EditViewModel::THIS_TEMPLATE_MODULE &&
                array_key_exists('relate_template', $_POST) && (boolean)$_POST['relate_template'] == true)
            {
                $this_template = EditViewModel::THIS_TEMPLATE_MODULE;   
            }
            
            $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
            
            $copy_model = EditViewCopyModel::getInstance($extension_copy)
                                ->setParentThisTemplate(Yii::app()->request->getParam('pci',null))
                                ->setThisTemplate($this_template)
                                ->setThisTemplateOnlyFirst($this_template_only_first)
                                ->copy(
                                    $_POST['select_list'],
                                    $extension_copy,
                                    false,
                                    null
                                )
                                ->getResult();

            if($copy_model['status'] == true && !empty($copy_model['id'])){
                $select_list = $copy_model['id'];
            }
        }
        
        if(!empty($select_list)){
            // запись связей
            foreach($select_list as $id){
                $relate_table = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                'params' => array(
                                                                ':copy_id' => $_POST['parent_copy_id'],
                                                                ':relate_copy_id' => $copy_id)
                                                ));    
                DataModel::getInstance()->Insert('{{' . $relate_table->table_name . '}}',
                                                      array(
                                                            $relate_table->parent_field_name =>  $_POST['parent_data_id'],
                                                            $relate_table->relate_field_name =>  $id,
                                                      ));
            }
        } else {
            return $this->renderJson(array(
                'status' => false,
            ));        
        }

        // обновляем значения полей первичного элемента
        SubModuleUpdatePrimaryModel::getInstance()
                                    ->setPrimaryEntities($_POST['primary_entities'])
                                    ->update(
                                        $_POST['parent_copy_id'],
                                        array($_POST['parent_data_id']),
                                        $copy_id,
                                        $select_list
                                    );
                                                 
        $params = \AdditionalProccessingModel::getInstance()->updateSubModule($copy_id, Yii::app()->request->getPost('parent_copy_id'), Yii::app()->request->getPost('parent_data_id'), $select_list);

        return $this->renderJson(array(
            'status' => true,
            'ev_refresh_field' => (!empty($params['ev_refresh_fields'])) ? $params['ev_refresh_fields'] : false,
        ));        
    }






    /**
     * удаление данных из сабмодуля
     */
    public function actionDeleteFromSubModule($copy_id){
        if(!Yii::app()->request->getPost('parent_copy_id') || !Yii::app()->request->getPost('id')){
            return $this->renderJson(array(
                'status' => false,
            ));
        }
        
        SubModuleDeleteModel::getInstance()
                                ->setPrimaryEntities(Yii::app()->request->getPost('primary_entities'))
                                ->setThisTemplate(Yii::app()->request->getPost('this_template'))
                                ->delete(
                                        Yii::app()->request->getPost('parent_copy_id'),
                                        Yii::app()->request->getPost('parent_data_id'),
                                        $copy_id,
                                        Yii::app()->request->getPost('id')
                                    );

        $params = \AdditionalProccessingModel::getInstance()->clearLinked($copy_id, Yii::app()->request->getPost('parent_copy_id'), Yii::app()->request->getPost('parent_data_id'), Yii::app()->request->getPost('id'));                            

        return $this->renderJson(array(
            'status' => true,
            'ev_refresh_field' => (!empty($params['ev_refresh_fields'])) ? $params['ev_refresh_fields'] : false,
        ));
    }



    /**
    * Копирование данных модуля в ListView
    */
    public function actionCopyForSubModule($copy_id){
        $validate = new Validate();

        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);

        $sub_module_validate = ValidateSubModule::getInstance()->check(array_merge($_POST, array('copy_id' => $extension_copy->copy_id)), ValidateConfirmActions::ACTION_SUB_MODULE_EDIT_VIEW_DELETE);
        if($sub_module_validate->beMessages())
            return $this->renderJson(array(
                'status' => false,
                'messages' => $sub_module_validate->getValidateResultHtml(),
            ));


        if(!empty($_POST['id'])){
            if(!is_array($_POST['id'])){
                $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
                $this->renderJson(array(
                            'status' => false,
                            'messages' => $validate->getValidateResultHtml(),
                ));
            } else {
                $copy_model = EditViewCopyModel::getInstance($extension_copy)
                                    ->setParentThisTemplate(Yii::app()->request->getParam('pci', null))
                                    ->copy($_POST['id'], $extension_copy, true, null)
                                    ->createProcessAfterCreatedEntity()
                                    ->getResult();

                if($copy_model['status'] == true && !empty($copy_model['id'])){
                    // запись связей

                    $parent_copy_id = Yii::app()->request->getParam('parent_copy_id');
                    $parent_data_id = Yii::app()->request->getParam('parent_data_id');

                    foreach($copy_model['id'] as $id){
                        $this->createRelateLinks($extension_copy->copy_id, $parent_copy_id, $parent_data_id, $id, 'relate_module_many');
                    }
                }
            }
            $result = array(
                        'status' => true,
                        );
        } else {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                        'status' => false,
                        'messages' => $validate->getValidateResultHtml(),
            );
        }
        return $this->renderJson($result);
    }


    /**
     * создает связь между скопироваными записями  
     */
    private function createRelateLinks($relate_copy_id, $parent_copy_id, $parent_data_id, $id, $type_relate){
        if(!$relate_copy_id || !$parent_copy_id || !$parent_data_id || !$id){
            return;
        }
        $relate_table = ModuleTablesModel::model()->find(array(
                                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="'.$type_relate.'"' ,
                                                        'params' => array(
                                                                        ':copy_id' => $parent_copy_id,
                                                                        ':relate_copy_id' => $relate_copy_id)
                                                        ));
        if(!empty($relate_table)){
            
            // проверяем, не ли еще связи          
            $relate_data_list =
                DataModel::getInstance()
                    ->setSelect('count(*) as xcount')
                    ->setFrom('{{' . $relate_table->table_name. '}}')
                    ->setWhere(
                            $relate_table->relate_field_name . '=:relate_data_id AND ' . $relate_table->parent_field_name . '=:parent_data_id',
                            array(
                                ':parent_data_id' => $parent_data_id,
                                ':relate_data_id' => $id,                                
                                ))
                    ->findRow();
            if(!empty($relate_data_list) && (integer)$relate_data_list['xcount'] == 0){            
            
                DataModel::getInstance()->Insert('{{' . $relate_table->table_name . '}}',
                array(
                    $relate_table->parent_field_name => $parent_data_id,
                    $relate_table->relate_field_name => $id,
                ));
            }

        }
    }





    /**
    * Возвращает данные карточек определенного сабмодуля
    */     
    public function actionCardListForSubModule($copy_id){
        $validate = new Validate();
        $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['parent_copy_id']);
        $schema = $extension_copy->getSchema();
        $schema = SchemaOperation::getInstance()->getSubModuleSchema($schema, $copy_id);

        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.SubModule.BlockTable'),
                                   array(
                                    'schema' => $schema,
                                    'extension_copy' => $extension_copy,
                                    'data_id' => $_POST['parent_data_id'],
                                    'this_template' => $_POST['this_template'],
                                   ),
                                   true);

        if($validate->error_count > 0)
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        else
            return $this->renderJson(array(
                'status' => true,
                'data' => $result,
            ));        
    }
    







    /**
    * возвращает данные сабмодулей  для обновления в EditView
    */     
    public function actionUpdateCardListSubModules($copy_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        
        $sub_modules = SchemaOperation::getSubModules($extension_copy->getSchemaParse());
        
        $result = array();
        if(empty($sub_modules)){
            return $this->renderJson(array(
                            'status' => false,
            )); 
        }

        foreach($sub_modules as $module){
            $result[$module['sub_module']['params']['relate_module_copy_id']] =
                Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.SubModule.BlockTable'),
                                       array(
                                        'schema' => $module['sub_module'],
                                        'extension_copy' => $extension_copy,
                                        'data_id' => (isset($_POST['id']) ? $_POST['id'] : null),
                                        'this_template' => $_POST['this_template'],
                                       ),
                                       true);
        }

        return $this->renderJson(array(
                        'status' => true,
                        'data' => $result,
        )); 
        
        
        
    }







    /**
     * actionChangeTemplateValue - изменение названия шаблона при создании
     */
    public function actionChangeTemplateValue($copy_id){
        if($copy_id != \ExtensionCopyModel::MODULE_PROCESS) return $this->renderJson(array('status' => true));

        $bpm_params = $_POST;

        if(empty($bpm_params)){
            $validate = new Validate();
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            );
            return $this->renderJson($result);
        }

        $result =
            (new \Process\models\BpmParamsModel())
                    ->setVars($bpm_params)
                    ->setRunIfProcessRunning(true)
                    ->validate()
                    ->run(true, true)
                    ->getResult(false, true);

        $this->renderJson($result);
    }






    public function actionLoadInlineElements(){
        $extension_copy = \ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
        $params = $extension_copy->getFieldSchemaParams($_POST['params_for_data']);

        $element = InLineEditBuilder::getInstance()
                        ->setExtensionCopy($extension_copy)
                        ->setParentCopyId((array_key_exists('pci', $_POST) ? (integer)$_POST['pci'] : null))
                        ->setThisTemplate($_POST['this_template'])
                        ->buildElementJSArray($params);

        return $this->renderJson(array(
            'status' => true,
            'elements' => ($element ? $element : '')
        ));
    }





    public function actionInlineCheckDisableElement(){
        $status = \EditViewBuilder::disableElementModule(
                    \Yii::app()->request->getParam('copy_id'),
                    \Yii::app()->request->getParam('data_id'),
                    \Yii::app()->request->getParam('this_template')
                );

        return $this->renderJson(array(
            'status' => $status,
        ));
    }





    public function getSwitchIconList($extension_copy){
        $icon_list = [];

        $crm_properties = [
            '_active_object' => $this,
            '_extension_copy' => $extension_copy,
        ];


        if(Yii::app()->controller->module->switch_to_pw && (SchemaOperation::getInstance()->beProcessViewGroupParam($extension_copy->getSchemaParse()))){
            if($this->module->list_view_icon_show['switch_to_pv']){
                $icon_list[] = [
                    'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(array('module' => array('destination' => 'processView')))->prepare()->getKey(),
                    'data-type' => null,
                    'class' => 'ajax_content_reload',
                    'i_class' => 'fa fa-bars',
                ];
            }
        }


        if($this->module->list_view_icon_show['switch_to_lv']){
            $icon_list[] = [
                'data-action_key' => null,
                'data-type' => null,
                'class' => null,
                'i_class' => 'fa fa-th-list active',
            ];
        }

        if($extension_copy->isCalendarView() && $this->module->list_view_icon_show['switch_to_cv']){
            $icon_list[] = [
                'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(array('module'=>array('destination' => 'calendarView')))->prepare()->getKey(),
                'data-type' => 'calendar',
                'class' => 'ajax_content_reload element',
                'i_class' => 'fa fa-calendar',
            ];
        }

        if(count($icon_list) == 1){
            $icon_list = [];
        }

        return $icon_list;
    }



}
