<?php

class BPMController extends \Controller{


    /**
     * filter
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }






    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'constructor':
            case 'run':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, \ExtensionCopyModel::MODULE_PROCESS, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', \Yii::t('messages', 'You do not have access to this object'));
                }

                if(!ParticipantModel::model()->checkUserSubscription(
                        \ExtensionCopyModel::MODULE_PROCESS,
                        \Yii::app()->request->getParam('process_id', null)
                    )
                ){
                    return $this->returnCheckMessage('w', \Yii::t('messages', 'You do not have access to this object'));
                }
            case 'showOperationParams':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, \ExtensionCopyModel::MODULE_PROCESS, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', \Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'saveSchema':
            case 'saveSchemaOperation':
            case 'doneOperation':
            case 'setProcessStatus':
            case 'saveSchemaOperation':
            case 'saveSchemaOperationCard':
            case 'deleteSchemaOperation':
            case 'showParticipant' :
            case 'changeParamsContent' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, \ExtensionCopyModel::MODULE_PROCESS, Access::ACCESS_TYPE_MODULE)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }

        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);

        $filterChain->run();
    }






    /**
     *   Возвращает все данные для отображения listView
     */
    public function prepareDataForBPM($extension_copy, $process_id, $mode){
        $validate = new Validate();

        if(empty($process_id)){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $process_model = \Process\models\ProcessModel::getInstance($process_id);
        if(empty($process_model)){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $data = array();
        $data['extension_copy'] = $extension_copy;
        $data['submodule_schema_parse'] = $extension_copy->getSchemaParse(array(), array(), array(), false);
        $data['mode'] = $mode;


        $data['process_model'] = $process_model;
        $data['actions'] = $process_model->getActions();
        $data['server_params'] = $process_model->getServerParams(array('copy_id' => $extension_copy->copy_id));

        $this->setMenuMain();

        if($validate->error_count == true){
            return $this->returnCheckMessage('w', Yii::t('messages', 'Page not found'), false);
        }

        $this->data = array_merge($this->data, $data);
    }




    private  function setProcessMenu(){
        // process menu
        $this->data['process_menu_module_data'] = null;
        $this->data['process_menu_active_value'] = Yii::t($this->module->getModuleName() . 'Module.base', 'Processes');
        $this->data['pm_extension_copy'] = null;

        $pm_extension_copy = ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS);
        $this->data['pm_extension_copy'] = $pm_extension_copy;
        $this->data['process_menu_module_data'] = DropDownNavigationModel::getInstance()
                                                            ->setVars(array('extension_copy' => $pm_extension_copy, 'id' => \Yii::app()->request->getParam('process_id')))
                                                            ->prepare(\DropDownNavigationModel::MENU_PROCESS_BPM)
                                                            ->getResult()['data'];

        foreach($this->data['process_menu_module_data'] as $process){
            if($process[$pm_extension_copy->prefix_name . '_id'] == \Yii::app()->request->getParam('process_id')){
                $this->data['process_menu_active_value'] = $process['module_title'];
                break;
            }
        }
    }



    public function actionConstructor(){
        $process_id = \Yii::app()->request->getParam('process_id');
        $this->prepareDataForBPM($this->module->extensionCopy, $process_id, \Process\models\ProcessModel::MODE_CONSTRUCTOR);
        $this->setProcessMenu();

        return $this->renderAuto('/site/bpm', $this->data);
    }





    public function actionRun(){
        $process_id = \Yii::app()->request->getParam('process_id');
        $this->prepareDataForBPM($this->module->extensionCopy, $process_id, \Process\models\ProcessModel::MODE_RUN);
        $this->setProcessMenu();

        return $this->renderAuto('/site/bpm', $this->data);
    }





    /**
     * actionSaveSchema
     * @param $process_id
     * @param $schema
     */
    public function actionSaveSchema(){
        $validate = new Validate();
        $result = array();

        if(empty($_POST['process_id']) || !array_key_exists('schema', $_POST)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }

        $schema_process = $_POST['schema'];
        $process_id = $_POST['process_id'];
        $process_model = \Process\models\ProcessModel::getInstance($process_id);

        if(!is_array($schema_process) || empty($process_model)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }

        if($validate->error_count){
            return $this->renderJson($result);
        }

        // обновление схемы
        $process_model->setSchema($schema_process);
        $process_model->save();


        // обновление версии схемы
        \Process\models\ProcessSchemaVersions::updateVersion(\Yii::app()->request->getParam('version_schema'));

        (new \Process\models\OperationsModel)->updateFromProcessSchema();

        $result = array(
            'status' => true,
            'schema' => \Process\models\SchemaModel::getInstance()
                                            ->setOperationsExecutionStatus()
                                            ->reloadOtherParamsForSchema()
                                            ->getSchema(),
        );

        return $this->renderJson($result);
    }







    /**
     * setProcessStatus  - установка статуса процесса. Запуск / остановка / завершение
     */
    public function actionSetProcessStatus(){
        $validate = new Validate();

        if(empty($_POST['process_id']) || empty($_POST['b_status']))
        {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
            return $this->renderJson($result);
        }

        $process_model = \Process\models\ProcessModel::getInstance(\Yii::app()->request->getParam('process_id'));
        $process_model->setAttribute('b_status', \Yii::app()->request->getParam('b_status'));
        $process_model->setScenario('switch_process_status');


        if($process_model->save()){
            $result = array(
                'status' => true,
                'schema' => \Process\models\SchemaModel::getInstance()
                                                ->setOperationsExecutionStatus()
                                                ->reloadOtherParamsForSchema()
                                                ->getSchema(true),
            );
            $result['b_status'] = \Process\models\ProcessModel::getInstance(null, true)->b_status;
        } else {
            $result = array(
                'status' => false,
            );

        }

        return $this->renderJson($result);
    }






    /************************************************************
     *
     *                  --- OPERATIONS ---
     *
     ************************************************************/






    /**
     * action ShowOperationParams
     */
    public function actionShowOperationParams(){
        $validate = new Validate();

        if(
            !\Yii::app()->request->getParam('process_id', false) ||
            !\Yii::app()->request->getParam('unique_index', false) ||
            !\Yii::app()->request->getParam('element_name', false) ||
            !\Yii::app()->request->getParam('mode', null) ||
            !\Yii::app()->request->getParam('mode_change', null)
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                            'status' => false,
                            'messages' => $validate->getValidateResultHtml(),
                        ));
        }

        \Process\models\ProcessModel::getInstance(\Yii::app()->request->getParam('process_id'))
                            ->setMode(\Yii::app()->request->getParam('mode'))
                            ->setModeChange(\Yii::app()->request->getParam('mode_change'));

        $operations_model = \Process\models\OperationsModel::findByParams(\Yii::app()->request->getParam('process_id'), \Yii::app()->request->getParam('unique_index'));

        // Проверка режима редактирования
        if($operations_model->checkShowOperation() == false){
            return $this->renderJson(array(
                'status' => false,
            ));
        }


        if(empty($operations_model)){
            $operations_model = new \Process\models\OperationsModel();
            $operations_model->unique_index = \Yii::app()->request->getParam('unique_index');
            $operations_model->element_name = \Yii::app()->request->getParam('element_name');
        }

        // отключено
        //\ParticipantModel::setChangeResponsible(false);

        $html = $operations_model->getParamsHtml();

        return $this->renderJson(array(
                        'status' => true,
                        'html' => $html,
                    ));

    }









    /**
     * action SaveSchemaOperation - Сохранение схемы оператора
     */
    public function actionSaveSchemaOperation(){
        $validate = new Validate();
        $result = array();

        if(
            empty($_POST['process_id']) ||
            empty($_POST['unique_index']) ||
            !array_key_exists('schema_operation', $_POST))
        {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }

        if(!is_array($_POST['schema_operation'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
        }
        if($validate->error_count){
            return $this->renderJson($result);
        }

        \Process\models\ProcessModel::getInstance($_POST['process_id']);

        // сохраняем данные оператора
        $result = \Process\models\OperationsSaveModel::getInstance()
                        ->setParams($_POST)
                        ->save()
                        ->getResult();

        return $this->renderJson($result);
    }







    /**
     * action saveSchemaOperationCard - Сохранение оператора и данных краточки модуля
     */
    public function actionSaveSchemaOperationCard(){
        $validate = new Validate();

        $params_tmp = $_POST;
        if(array_key_exists('process_operation', $params_tmp)){
            // пересобираем данные
            $params = $params_tmp['process_operation'];
            unset($params_tmp['process_operation']);
            $params['edit_view_data'] = $params_tmp;

            \Process\models\ProcessModel::getInstance($_POST['process_operation']['process_id']);

            // сохраняем данные оператора
            $result = \Process\models\OperationsSaveModel::getInstance()
                            ->setParams($params)
                            ->save()
                            ->getResult();

            return $this->renderJson($result);

        } else { // error
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

    }






    /**
     * action DeleteSchemaOperation
     *
     * @param $operator_name
     * @param $process_id
     * @param $schema
     */
    public function actionDeleteSchemaOperation(){
        $validate = new Validate();

        if(
            empty($_POST['process_id']) ||
            empty($_POST['unique_index']) ||
            empty($_POST['mode']) ||
            empty($_POST['mode_change'])
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        \Process\models\ProcessModel::getInstance(\Yii::app()->request->getParam('process_id'))
                            ->setMode(\Yii::app()->request->getParam('mode'))
                            ->setModeChange(\Yii::app()->request->getParam('mode_change'));

        $operations_model = \Process\models\OperationsModel::model()->find(array(
            'condition' => 'process_id=:process_id AND unique_index=:unique_index',
            'params' => array(
                ':process_id' => $_POST['process_id'],
                ':unique_index' => $_POST['unique_index'],
            ),
        ));

        // Проверка режима редактирования
        if($operations_model->checkShowOperation() == false){
            return $this->renderJson(array(
                'status' => false,
            ));
        }

        $delete = false;
        if(!empty($operations_model)){
            $delete = $operations_model->deleteOperation();
        }

        if($delete){
            return $this->renderJson(array(
                'status' => true,
            ));
        } else {
            $validate->addValidateResult('e', \Yii::t('messages', 'Error delete data'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }
    }





    /**
     * action DoneOperation - Установка статуса "выполнен" для оператора
     */
    public function actionDoneOperation(){
        $validate = new Validate();

        if(
            empty($_POST['process_id']) ||
            empty($_POST['unique_index'])
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            );
            return $this->renderJson($result);
        }

        \Process\models\ProcessModel::getInstance($_POST['process_id']);

        // сохраняем данные оператора
        \Process\models\OperationsModel::findByParams($_POST['process_id'], $_POST['unique_index'])
            ->setOperationDone();

        $result = array(
            'status' => true,
            'schema' => \Process\models\SchemaModel::getInstance()
                ->setOperationsExecutionStatus()
                ->reloadOtherParamsForSchema()
                ->getSchema(true),
        );

        return $this->renderJson($result);
    }





    /**
     * actionChangeParamsContent -  Перегрузка (изменение) параметров или значений в параметра оператора
     *                              Возвращает новый откоректированный контент для замены
     * @return json
     */
    public function actionChangeParamsContent(){
        $validate = new Validate();
        $post = $_POST;

        if(
            !array_key_exists('process_id', $post) ||
            !array_key_exists('unique_index', $post) ||
            !array_key_exists('element_name', $post) ||
            !array_key_exists('action', $post) ||
            !array_key_exists('params', $post)
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        \Process\models\ProcessModel::getInstance($post['process_id']);
        $class = \Process\models\OperationsModel::getOperationClassName($post['element_name']);
        $params_result = $class::getInstance()
                                    ->setOperationsModel(\Process\models\OperationsModel::findByParams($post['process_id'], $post['unique_index']))
                                    ->changeParamsContent($post['action'], $post['params']);

        return $this->renderJson(array(
            'status' => true,
            'params_result' => $params_result,
        ));
    }






    /**
     * actionShowParticipant - возвращает участников
     * @return string
     */
    public function actionShowParticipant(){
        $validate = new Validate();
        $post = $_POST;

        if(
            !array_key_exists('action', $post) ||
            !array_key_exists('process_id', $post) ||
            !array_key_exists('ug_id', $post) ||
            !array_key_exists('ug_type', $post)
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        \Process\models\ProcessModel::getInstance($post['process_id']);

        $participant_model = new \Process\models\ParticipantModel();
        $participant_model
            ->setVars($post)
            ->setApplyException(true)
            ->setExceptionList();

        $group_data = null;
        if(
            \Process\models\ProcessModel::getInstance()->getMode() == \Process\models\ProcessModel::MODE_RUN &&
            \Process\models\SchemaModel::getInstance()->isSetActiveOperationsInResponsible(array('ug_id'=>$post['ug_id'], 'ug_type'=>$post['ug_type']))
        ){
            $group_data = \ParticipantModel::PARTICIPANT_UG_TYPE_USER;
        }

        $data = $post;
        $data['base_ug_id'] = $data['ug_id'];
        $data['base_ug_type'] = $data['ug_type'];
        $data['html_values'] = $participant_model->getHtmlValues($group_data, $post['process_id'], null, null, true);
        $data['html_active_responsible'] = '';
        $data['unique_index'] = (!empty($post['unique_index']) ? $post['unique_index'] : md5(date_format(date_create(), 'YmdHisu')) . '99');

        if(!empty($post['ug_id']) && !empty($post['ug_type'])){
            $html_active_responsible = $participant_model->getHtmlValues($post['ug_type'], $post['process_id'], $post['ug_id'], $post['ug_type'], true);
            if(!empty($html_active_responsible)) $data['html_active_responsible'] = $html_active_responsible[0]['html'];
        }

        $data['li_html'] = $this->renderPartial('/dialogs/li-participant', $data, true);
        $html = $this->renderPartial('/dialogs/participant', $data, true);

        return $this->renderJson(array(
            'status' => true,
            'html' => $html,
        ));
    }




    /**
     * actionBpmParams
     */
    public function actionBpmParamsRun(){
        $result = (new \Process\models\BpmParamsModel())
            ->setVars($_POST)
            ->setRunIfProcessRunning(true)
            ->validate()
            ->run(true)
            ->getResult();

        $this->renderJson($result);
    }








}
