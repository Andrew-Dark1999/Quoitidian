<?php

use \Reports\extensions\ElementMaster as Extensions;


class ConstructorController extends \EditView{
 
        
    public $content_report;

    public function init(){
        parent::init();
        /*
            Yii::app()->clientScript
                ->registerScriptFile('/static/js/reports.general.js')
                ->registerCssFile('/static/css/reports.crm.css');        
          */  
    }




    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain){
        
        switch(Yii::app()->controller->action->id){
            case 'add':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'view':
            case 'save':
            case 'addElement':
            case 'changeElement':
            case 'addGraphDialog':
                if(Yii::app()->request->getPost('EditViewModel')){
                    if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                } else {
                    if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)){
                        return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                    }
                }
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE))
                    ListViewBulder::$participant_list_hidden = true;

                break;
        }
        
        parent::filterCheckAccess($filterChain);
    }




    /**
    * добавление
    */     
    public function actionAdd(){
        \Reports\models\DataReportModel::$use_filters = true;
        \ViewList::setViews(array('site/editView' => '/site/edit-view'));

        $schema = \Reports\extensions\ElementMaster\Schema::getInstance()->getDefaultSchema();
        $this->content_report = Extensions\ConstructorBuilder::getInstance()->buildConstructorPage($schema);

        $action_model = new EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new \Reports\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }




    /**
    * Просмотр/редактирование данних
    */     
    public function actionView(){
        \Reports\models\DataReportModel::$use_filters = true;
        $validate = new Validate();
        
        \ViewList::setViews(array('site/editView' => '/site/edit-view'));

        $schema = \Reports\models\ReportsModel::getSavedSchema(\Yii::app()->request->getParam('id'), $validate);
        $schema = \Reports\extensions\ElementMaster\Schema::getInstance()
                        ->setSchema($schema)
                        ->prepareForConstructor()
                        ->buildSchema(true);

        if($validate->error_count > 0){
            $result = array(
                        'status' => 'error',
                        'messages' => $validate->getValidateResultHtml(),
            );
        }

        $this->content_report = Extensions\ConstructorBuilder::getInstance()->buildConstructorPage($schema);

        $action_model = new EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new \Reports\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }




    /**
    * сохранение данних
    */     
    public function actionSave(){
        $action_model = new EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new \Reports\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();


        $id = \Yii::app()->request->getParam('id');
        if($id){
            \Reports\models\ReportsFilterModel::clearFilters($id);
            \Reports\models\ReportsUsersStorageModel::clearStorage($id);
        }
    }




    /**
     * возвращает новый элемент
     */
    public function actionAddElement(){
        \Reports\models\DataReportModel::$use_filters = true;
        $validate = new Validate();
        $return_html_array = true;        
                
        $elements = Yii::app()->request->getParam('element');
        
        if($elements === false){
            $validate->addValidateResult('e', \Yii::t('messages', 'Not defined data parameters'));            
            return $this->renderJson(array(
                            'status' => 'error',
                            'messages' => $validate->getValidateResultHtml(),                            
            ));
        }                       
        
        if(!is_array($elements)){
            $elements = array($elements);            
            $return_html_array = false;
        }

        $params = Yii::app()->request->getParam('params');
        if(!empty($params['filters'])) \Reports\models\DataReportModel::$filter_params = $params['filters'];

        foreach($elements as $element){                        
            $html[] = \Reports\extensions\ElementMaster\Schema::getInstance()->generateConstructorSchema($element, $params, true);
        }            
        
        return $this->renderJson(array(
                        'status' => true,
                        'html' => ($return_html_array ? $html : $html[0]),
        ));
    }







    /**
     * изменение значение элемента в констурукторе 
     */
    public function actionChangeElement(){
        \Reports\models\DataReportModel::$use_filters = true;
        $params = Yii::app()->request->getParam('params');
        if(!empty($params['filters'])) \Reports\models\DataReportModel::$filter_params = $params['filters'];

        $result = \Reports\models\ConstructorChangeElementModel::getInstance()
                        ->setParams($params)
                        ->prepareElementData(Yii::app()->request->getParam('element'))
                        ->getResult();
        
        $this->renderJson($result);
    }





    /**
     * выбор графика
     */
    public function actionAddGraphDialog(){
        $this->renderPartial('/dialogs/add_graph', array(
                                                    'graph_count' => \Yii::app()->request->getParam('graph_count'),
                                                    'positions' => \Yii::app()->request->getParam('positions'),
                                                    ));
    }



}

