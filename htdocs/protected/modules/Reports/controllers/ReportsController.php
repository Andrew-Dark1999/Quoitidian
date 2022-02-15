<?php

use \Reports\extensions\ElementMaster as Extensions;

class ReportsController extends \ListView{


    public $content_report;



    public function filters(){
        return array(
            'checkAccess - saveUserStorage, addGraphData',
        );
    }

    
    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'index':
            case 'view':
                if($this->module->extensionCopy->getIsTemplate() == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
                    \Yii::app()->request->getParam('pci') == false &&
                    \Yii::app()->request->getParam('pdi') == false
                ){
                    $this->redirect(Yii::app()->createUrl('/module/listView/showTemplate') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    return;
                }

            case 'saveUserStorage':
            case 'addGraphData':
                    if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                break;
            case 'printR':
            case 'exportR':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EXPORT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    echo Yii::t('messages', 'You do not have access to this object');
                    return false;
                }
                break;
        }

        $this->data['reports_model_list'] = \Reports\models\ReportsModel::getReportsList();
        if(!$this->isSetReport()){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);



        parent::filterCheckAccess($filterChain);
    }
    


    private function isSetReport(){
        $reports_id = \Yii::app()->request->getParam('id');
        $result = false;
        if(!empty($this->data['reports_model_list']) && !empty($reports_id)){
            foreach($this->data['reports_model_list'] as $list){
                if($list['reports_id'] == $reports_id){
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }





    

    /**
    *   Возвращает все данные для отображения listView 
    */ 
    public function getDataForView($extension_copy, $only_PK=false){
        $validate = new Validate();
        $reports_id = func_get_arg(1);

        if(empty($reports_id)){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        if($this->this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            $exception_params_list = array('type'=>'relate_dinamic');
        } else {
            $exception_params_list = array('type'=>'module');
        }

        list($filter_controller) = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $data = array();
        $data['extension_copy'] = $extension_copy;
        $data['submodule_schema_parse'] = $extension_copy->getSchemaParse(null, $exception_params_list, array(), false);

        \ViewList::setViews(array('filter/installed' => '/filter/installed'));
        $filters = \Filters::getInstance()->setTextFromUrl()->getText();
        $data['filter_menu_list'] = $filter_controller->menuList($extension_copy, $filters, $reports_id);
        $data['filters_installed'] = (is_array($filters) ? $filter_controller->filtersInstalled($extension_copy, $filters, $reports_id) : "");



        $data['report_model'] = \Reports\models\ReportsModel::model()->findByPk($reports_id);
        $data['reports_id'] = $reports_id;


        if(empty($data['report_model'])){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $this->setMenuMain();

        $schema = \Reports\models\ReportsModel::getSavedSchema($reports_id, $validate);

        $schema_model = \Reports\extensions\ElementMaster\Schema::getInstance()
            ->setSchema($schema)
            ->setFromUsersStorage($reports_id)
            ->buildSchema();
        $schema = $schema_model->getResultSchema();


        if($validate->error_count == true){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $data['schema'] = $schema;
        $data['filters'] = $schema_model->getFilters();
        $data['table_data'] = $this->getTableData($schema_model, $schema_model->getFilters(), $schema);

        $this->content_report = Extensions\ReportBuilder::getInstance()->buildConstructorPage($schema);

        return $data;
    }




    private function getTableData($schema_model, $filters, $schema){
        $data_setting = $schema_model
                            ->setupGlobalDataAnalysisParams()
                            ->getDataSettings();
        $data_setting = \Helper::arrayMerge($data_setting, array('filters' => $filters));
        
        $result = \Reports\models\ConstructorModel::getInstance()->getQueryReportForTable($schema, $data_setting);

        return $result;
    }   
    
    
     
    
    

    public function actionView(){
        ini_set('max_execution_time', 3600); // 1ч
        $reports_id = \Yii::app()->request->getParam('id');

        $data = $this->getDataForView($this->module->extensionCopy, $reports_id);
        $this->data = array_merge($this->data, $data);

        \Reports\extensions\History::getInstance()->updateUserStorageFromUrl(array('destination' => 'view', 'copy_id' => $this->module->extensionCopy->copy_id. '_' . $reports_id));

        $this->renderAuto('view', $this->data);
    }






    /**
     *   Печать
     */
    public function actionPrintR($copy_id, $id){
        $this->layout = '//layouts/print';
        ViewList::setViews(array('print/listView' => '/print/list-view'));

        if(empty($copy_id) || empty($id)) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);

        $data = $this->getDataForView($extension_copy, $id);
        $data['col_hidden'] = array();
        $data['title_add_avatar'] = true;
        $data['files_only_url'] = false;

        if(isset($_GET['col_hidden']) && !empty($_GET['col_hidden'])){
            $data['col_hidden'] = explode(',', $_GET['col_hidden']);
        }

        return $this->render(ViewList::getView('print/listView'), $data);
    }











    /**
     *   Экспорт
     */
    public function actionExportR($copy_id, $id){
        if(empty($copy_id) || empty($id)) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        $extension_copy = ExtensionCopyModel::model()->modulesActive()->findByPk($copy_id);

        $data = $this->getDataForView($extension_copy, $id);

        switch($_GET['type']){
            case 'excel':
                \Reports\extensions\ExcelExport::getInstance()
                    ->setWidthColumn(json_decode($_GET['col_width'], true))
                    ->setWithOutGroupIndex($_GET['col_hidden'])
                    ->makeExcelFromListView($data)
                    ->getDocument('excel', $data['report_model']->module_title);
                break;

            case 'pdf':
                $data['col_hidden'] = array();
                $data['title_add_avatar'] = false;
                $data['files_only_url'] = true;

                if(isset($_GET['col_hidden']) && !empty($_GET['col_hidden']))
                    $data['col_hidden'] = explode(',', $_GET['col_hidden']);

                $this->layout = '//layouts/print';
                ViewList::setViews(array('print/listView' => '/print/list-view'));

                $mpdf=new mPDF('','', 0, '', 5, 5, 10, 5, 9, 9, 'L');
                $mpdf->AddPage('L');
                $mpdf->WriteHTML(Yii::app()->controller->render(ViewList::getView('print/listView'), $data, true));
                $mpdf->output($data['report_model']->module_title . '.pdf', 'D');
                break;
        }
    }




    public function actionSaveUserStorage(){
        $validate = new Validate();

        $reports_id = Yii::app()->request->getParam('reports_id');
        $type = Yii::app()->request->getParam('type');
        $value = Yii::app()->request->getParam('value');
        
        
        if(empty($reports_id) || empty($type) || empty($value)){
            $validate->addValidateResult('e', \Yii::t('messages', 'Not defined data parameters'));            
            return $this->renderJson(array(
                            'status' => 'error',
                            'messages' => $validate->getValidateResultHtml(),                            
            ));
        }
        
        
        \Reports\models\ReportsUsersStorageModel::addToStorage($reports_id, $type, $value);
        
        return $this->renderJson(array(
                        'status' => true,
                           
        ));
        
    }    
 




    /**
     * возвращает новый элемент
     */
    public function actionAddGraphData(){
        $params = Yii::app()->request->getParam('params');

        // history
        if(Yii::app()->request->getParam('update_user_storage')){
            $reports_id = $params['reports_id'];
            
            \Reports\models\ReportsUsersStorageModel::addToStorage($reports_id, 'graph_period',  array('graph_period' => array($params['graph_unique_index'] => (!empty($params['graph_period']) ? $params['graph_period'] : null))));
            \Reports\models\ReportsUsersStorageModel::addToStorage($reports_id, 'graph_indicators',  array('graph_indicators' => array($params['graph_unique_index'] => (!empty($params['graph_indicators']) ? $params['graph_indicators'] : ''))));
            \Reports\models\ReportsUsersStorageModel::addToStorage($reports_id, 'graph_display_option',  array('graph_display_option' => array($params['graph_unique_index'] => (!empty($params['graph_display_option']) ? $params['graph_display_option'] : ''))));
        }

        
        $ic = \ParamsModel::getValueArrayFromModel('graph');
        $html_indicator = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateReportSchema('block_setting_indicator', $params, true);
        $html_graph = \Reports\extensions\ElementMaster\Schema::getInstance(true)->generateReportSchema('graph_element', $params, true);
        
        return $this->renderJson(array(
                        'status' => true,
                        'html_indicator' => $html_indicator,
                        'html_graph' => $html_graph,
                        'max_indicators' => $ic['max_indicators'],
        ));
    }










}
