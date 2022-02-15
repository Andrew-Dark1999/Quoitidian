<?php


class ConstructorController extends Controller {

    public $path;


    public function init(){
        parent::init();
        
        $this->data['menu_main'] = array('index' => 'constructor');
        Cache::flush(Cache::CACHE_TYPE_DB);
    } 
   

    
    public function __destruct(){
        Cache::flush(Cache::CACHE_TYPE_DB);
    }
   
   
   
    /**
     * установка фильтров
     */
    public function filters(){
        return array(
            'checkAccess',
        );
    }  


    /**
     * Фильтр проверки доступа
     */
    public function filterCheckAccess($filterChain){
        switch(Yii::app()->controller->action->id){
            case 'index':
            case 'listModule':
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'createModule' :
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'copyModule' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'saveModule' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'setListOrder' :
            case 'setModuleStatus' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'deleteModule' :
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
            case 'deleteModuleData' :
                $copy_id_list = \Yii::app()->request->getParam('copy_id', false);
                if(empty($copy_id_list)){
                    return $this->returnCheckMessage('e', Yii::t('messages', 'Not defined data parameters'), false, false);
                }

                $module_names = array();
                foreach($copy_id_list as $copy_id){
                    if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $copy_id, Access::ACCESS_TYPE_MODULE)){
                        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
                        $module_names[] = $extension_copy->title;
                    }
                }
                if(!empty($module_names)){
                    return $this->returnCheckMessage('e', Yii::t('messages', 'Action is disabled from module(s): {s}', array('{s}' => implode(', ', $module_names))), false);
                }

                break;

        }
       
        $filterChain->run();
    }



    public function getPageInterfaceType(){
        return \Module::PAGE_IT_CONSTRUCTOR;
    }



    /**
    * Страница списка модулей
    */
    public function actionIndex(){
        $this->actionListModule();
    }


    /**
    * Возвращает список модулей
    */
    public function getModules($add_template = false){
        $modules = ExtensionCopyModel::model()->findAll(array(
                                                            'condition'=>'(`schema` != "" OR `schema` is not NULL) AND make_relate = "1"',
                                                            'order'=> 'sort',
                                                            )
                                                        );
        // если добавляем шаблоны модулей
        if($add_template && !empty($modules)){
            $result = array();
            foreach($modules as $module){
                $result[] = array(
                    'copy_id' => $module->copy_id,
                    'title' => $module->title,
                    'template' => false,
                );
                if($module->getModule(false)->isTemplate($module)){
                    $result[] = array(
                        'copy_id' => $module->copy_id,
                        'title' => $module->title,
                        'template' => true,
                    );
                }    
            }
            return $result;    
        }
        
        
        return $modules;
    }



    /**
     *  исключает из массива уже установленные модули  
     */
    private function exceptionIsSetModules($module_model, $exception_list, &$module_data){
        if(empty($exception_list)){
            $module_data[] = $module_model;
            return;
        } 
        $is_set = true;
        foreach($exception_list as $exception){
            //if($exception['copy_id'] == $module_model['copy_id'] && (boolean)$exception['template'] == (boolean)$module_model['template']){
            if($exception['copy_id'] == $module_model['copy_id']){
                $is_set = false;
                break;
            }
        }
        if($is_set == true) $module_data[] = $module_model;
    }
    

    /**
    * Отображение списка полей для подключения сабмодуля
    */
    public function actionAddElementSubModuleList(){
        $exception_list = null;
        if(isset($_POST['exception_list'])) $exception_list = $_POST['exception_list'];

        $module_data = array(); 
        $modules = $this->getModules(true);
        if(!empty($modules))
        foreach($modules as $module){
            $this->exceptionIsSetModules($module, $exception_list, $module_data);
        }
        
        $this->data['module_data'] = $module_data;
        if(empty($module_data)) return;
        $this->renderPartial('//dialogs/constructor-add-sub-module', $this->data);
    }


    /**
     * Отображение списка полей для подключения блока
     */
    public function actionAddElementBlockList(){
        $this->renderPartial('//dialogs/constructor-add-block');
    }




    /**
     * проверяет наличие обратной связи в элементе СДМ по полю Название
     */ 
    private function checkRelateTitle($copy_id, $parent_copy_id){
        $result = false;
        $module_table = ModuleTablesModel::model()->findAll(array(
                                                            'condition' => 'relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                            'params' => array(':relate_copy_id' => $copy_id))
                                                        );
        if(empty($module_table)) return $result;
        
        foreach($module_table as $module){
            $extension_copy = ExtensionCopyModel::model()->findByPk($module->copy_id);
            $params = $extension_copy->getPrimaryField(null, false);
            if(empty($params)) continue;
            foreach($params as $param){
                if($param['params']['type'] != 'relate_string') continue;
                if($param['params']['relate_module_copy_id'] == $copy_id && $module->copy_id != $parent_copy_id){
                    $result = true;
                    break;
                }
            }
            if($result) break;            
        }
        
        return $result;
    }


    /**
     * проверяет наличие связи в элементе СДМ по полю Название с данным модулем
     */ 
    private function checkRelateTitle2($copy_id, $parent_copy_id){
        $result = false;
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $params = $extension_copy->getPrimaryField(null, false);
        
        if(empty($params)) return $result;
        foreach($params as $param){
            if($param['params']['type'] != 'relate_string') continue;
            if($param['params']['relate_module_copy_id'] == $parent_copy_id){
                $result = true;
                break;
            }
        }
        
        return $result;
    }



    /**
    * Возвращает список модулей
    */
    public function actionGetModuleNames(){
        $exception_list = null;
        if(isset($_POST['exception_list']) && !empty($_POST['exception_list'])) $exception_list = $_POST['exception_list'];
        
        $modules = $this->getModules(false);

        $module_data = array();
        if(!empty($modules)){
            foreach($modules as $module){
                if(isset($_POST['is_primary']) && (boolean)$_POST['is_primary'] == true && $this->checkRelateTitle($module->copy_id, (isset($_POST['copy_id']) ? $_POST['copy_id'] : null)) == true) continue;
                if(isset($_POST['is_primary']) && (boolean)$_POST['is_primary'] == true && $this->checkRelateTitle2($module->copy_id, (isset($_POST['copy_id']) ? $_POST['copy_id'] : null)) == true) continue;
                $this->exceptionIsSetModules($module, $exception_list, $module_data);
            }
        }

        if(!empty($module_data)){
            $related = Yii::app()->request->getParam('relate_string');
            $exclude_ids = array();

            if($related) {
                $relate_ids = array_unique(Yii::app()->request->getParam('relate_module_copy_ids', array()));
                foreach($module_data as $module){
                    $primary = $module->getPrimaryField();
                    if(!empty($primary) && $primary['params']['type'] == 'relate_string'){
                        if(empty($relate_ids) || !in_array($primary['params']['relate_module_copy_id'], $relate_ids)){
                            $exclude_ids[] = $primary['params']['relate_module_copy_id'];
                        }
                    }
                }

                if(!empty($exclude_ids)){
                    $exclude_ids = array_unique($exclude_ids);
                }

            }

            $result = array();
            foreach($module_data as $module){
                if($related && !empty($exclude_ids)) {
                    if(in_array($module['copy_id'], $exclude_ids)){
                        continue;
                    }
                }

                $result[] = array(
                    'id' => $module['copy_id'],
                    'title' => $module['title'],
                );
            }
        }
        
        if(!empty($result)){
            return $this->renderJson(array(
                                        'status' => true,
                                        'data' => $result,
            ));
        } else {
            return $this->renderJson(array('status' => false));
        } 

    }


    
    /**
     * возвращает элемент
     */ 
    public function actionAddElement($element, array $params = array(), $extension_id = 1){
        $schema = Schema::getInstance(array('params'=>$params))->generateConstructorSchema($element);
        $use_wrapper = (!empty($_GET['wrapper'])) ? filter_var($_GET['wrapper'], FILTER_VALIDATE_BOOLEAN) : true;
        $html = ConstructorBuilder::getInstance()
                                        ->setExtension(ExtensionModel::model()->findByPk($extension_id))
                                        ->buildConstructorPage(array($schema), $use_wrapper);
        echo $html;
    }




    /**
     * возвращает элемент списка для select
     */ 
    public function actionAddElementDataForSelect(){
        echo $field_type_params = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Params.Params',
                                        array(
                                            'view' => 'select',
                                            'select_color_block' => (boolean)$_POST['color_block'],
                                            'select_params' => array('id' => $_POST['id'], 'value' => $_POST['value'], 'select_color' => 'gray', 'btn_remove' => true, 'finished_object' => false, 'slug' => "", 'select_sort' => 0),
                                        ),
                                        true);
    }





    /**
    * Возвращает список полей модуля
    */
    public function actionGetModuleFields($copy_id, $selected_field = null, $only_autoname_fields = false){

        $model = ExtensionCopyModel::model()->findByPk($copy_id)->setAddDateCreateEntity(false);
        $sub_module_schema_parse = $model->getSchemaParse(array(), array(), array(), false);
        $fields = array();
        
        if(!$only_autoname_fields) {
           
            $params = SchemaConcatFields::getInstance()
                ->setSchema($sub_module_schema_parse['elements'])
                ->setWithoutFieldsForListViewGroup()
                ->parsing()
                ->primaryOnFirstPlace()
                ->prepareWithConcatName()
                ->getResult();

            
            if(!empty($params['header'])) {
                foreach ($params['header'] as $aField) {
                    $fields[$aField['name']] = $aField['title'];
                }
            }
        
        }else {
            
            //выводим только поля для автогенерации
            $flds = Fields::getInstance()->getNameGenarationFields($sub_module_schema_parse);
            if(!empty($flds)) {
                foreach ($flds as $aField) {
                    $fields[$aField['name']] = $aField['title'];
                }
            }
        }
        
        echo CHtml::dropDownList('relate_field',
                            ($selected_field !== null ? $selected_field : ''),
                            $fields,
                            array(
                                   'class'=>'form-control element_params',
                                   'data-type' => 'relate_field',
                               )
                            );
    }







    /**
    * Ajax. Возвращает массив обьектов в html формате для создания поля Модуля
    * @param string $filed_type
    * @return string 
    */     
    public function actionFieldParams(){
        echo Fields::getInstance()->fieldParamsView($_POST['field_attr'],
                                                (isset($_POST['exception_list']) ? $_POST['exception_list'] : null),
                                                $_POST['copy_id']);
    }


    /**
    * Хлебные крошки. Базовая ветка
    */
    private function getCrumbsBase(){
        return  array(
                    'title'=>Yii::t('base', 'Constructor'),
                    'url'=>$this->createUrl('/constructor'),
                );
    }





    /**
    * Страница списка модулей
    */ 
    public function actionListModule(){
        $this->left_menu = true;
        $this->path =  array(
                array(
                    'title'=>Yii::t('base', 'Module list'),
                ),
        );
        array_unshift($this->path, $this->getCrumbsBase());
        
        $this->data['extension_copy_data'] = $this->getData();

            // если страница пагинации указан больше чем есть в действительности
        if(\Pagination::switchActivePageIdLarger()){
            $this->data['extension_copy_data'] = $this->getData();
        }

        History::getInstance()->updateUserStorageFromUrl('constructor');

        $this->renderAuto('list', $this->data);
    }



    private function getData(){
        $criteria=new CDbCriteria;
        $criteria->select ='SQL_CALC_FOUND_ROWS *, if(active=1, "'.Yii::t('base', 'Public').'", "'.Yii::t('base', 'Remove from public').'") as active_title';


        //pagination
        $pagination = new Pagination();
        $pagination->setParamsFromUrl();

        $search = \Search::getInstance()->setTextFromUrl();
        $search_text = addslashes($search->getText());


            //order
        $order = new Sorting;
        $order->setParamsFromUrl();
        $order_params = $order->getParams();
        if(!empty($order_params)){
            $criteria->order = $order->getParamsToString();
        } else {
            $criteria->order = 'sort asc';
        }

        if($search_text !== null && $search_text !== ''){
            $criteria->condition = 'title like "%'.$search_text.'%"';
        }

        if($pagination->getActivePageSize() > 0){
            $criteria->limit = $pagination->getActivePageSize();
            $criteria->offset = $pagination->getOffset();
        }



        $result = ExtensionCopyModel::model()->changeConstructor(1)->findAll($criteria);

        $pagination->setItemCount();

        return $result;
    }



   /**
     * Сортровка модуля в спискe
     */
    public function actionSetListOrder(){
        if(!isset($_POST['direction']) || !isset($_POST['copy_id']) || !in_array($_POST['direction'], array('up', 'down')) )
            return $this->renderJson(array('status' => false));
            
        $sort_this = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
        
        if($_POST['direction'] == 'up'){
            $sort_preceding = ExtensionCopyModel::model()->findAll(array('condition' => 'sort<=:sort', 'params' => array(':sort' => $sort_this->sort - 1), 'order' => 'sort'));
            $preceding_index =  count($sort_preceding)-1;
        } elseif($_POST['direction'] == 'down'){
            $sort_preceding = ExtensionCopyModel::model()->findAll(array('condition' => 'sort>=:sort', 'params' => array(':sort' => $sort_this->sort + 1), 'order' => 'sort'));
            $preceding_index = 0;
        }
        if(empty($sort_preceding)) return $this->renderJson(array('status' => false));

        ExtensionCopyModel::model()->UpdateAll(array('sort' => $sort_preceding[$preceding_index]->sort ), 'copy_id=:copy_id', array(':copy_id'=>$sort_this->copy_id));
        ExtensionCopyModel::model()->UpdateAll(array('sort' => $sort_this->sort), 'copy_id=:copy_id', array(':copy_id'=>$sort_preceding[$preceding_index]->copy_id));

        return $this->renderJson(array('status' => true));
    }

    
    /**
    *  Создание нового экземпляра модуля 
    */
    public function actionCreateModule($extension_id){
        if(empty($extension_id)) return $this->renderJson(array('status' => false));
        
        $extension = ExtensionModel::model()->findByPk($extension_id);        

        $schema = $extension->getModule()->getSchemaConstructor();

        return $this->renderJson(array(
                        'status' => true,
                        'data' => $this->renderPartial('edit-view', array(
                                            'extension'=> $extension,
                                            'content' => ConstructorBuilder::getInstance()
                                                                        ->setExtension(ExtensionModel::model()->findByPk($extension_id))
                                                                        ->buildConstructorPage($schema),
                                           ), true)
 
        ));

    }




    /**
    *  Создание нового экземпляра Субмодуля 
    */
    public function actionCreateSubModule($copy_id){
        if(empty($extension_id)) return $this->renderJson(array('status' => false));
        
        $extension = ExtensionModel::model()->findByPk($extension_id);        

        $schema = $extension->getModule()->getSchemaConstructor();

        $this->renderPartial('edit-view', array(
                                            'extension'=> $extension,
                                            'content' => ConstructorBuilder::getInstance()
                                                                        ->setExtension($extension)
                                                                        ->buildConstructorPage($schema),
                                           )
        );


    }
    
    
    
    
    /**
    * Редактирование экземпляра модуля
    */
    public function actionEditModule($copy_id){
        if(empty($copy_id)) return $this->renderJson(array('status' => false));
        
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);        
        if(isset($_POST['submit'])){
            $extension_copy->attributes = $_POST['ExtensionCopyModel'];
            if(!$extension_copy->save())
                $this->data['errors'] = $extension_copy->getErrors();
        }

        $schema = $extension_copy->extension->getModule($extension_copy)->getSchemaConstructor();

        return  $this->renderPartial('edit-view', array(
                                            'extension_copy'=> $extension_copy,
                                            'extension'=> $extension_copy->extension,
                                            'content' => ConstructorBuilder::getInstance()
                                                                            ->setExtension($extension_copy->extension)
                                                                            ->setExtensionCopy($extension_copy)
                                                                            ->buildConstructorPage($schema),
                                           )
                                    );
    }







    /**
     * проверка элементов модуля перед удалением
     */
    public function actionValidateBeforeDeleteModule(){
        $validate = new SchemaValidate();

        if(empty($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        $modules = \ExtensionCopyModel::model()->findAllByPk($_POST['copy_id']);

        if(empty($modules)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }


        foreach($modules as $extension_copy){
            $validate->setExtensionCopy($extension_copy);
            $validate->ValidateAllForDelete($extension_copy->getSchema());
        }

        if($validate->beMessages())
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        else {
            return $this->renderJson(array(
                'status' => true,
            ));
        }
    }










    /**
    * Удаление экземпляра модуля
    */
    public function actionDeleteModule(){
        $validate = new Validate();

        if(empty($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
        } else {
            if($_POST['copy_id']){
                $delete_model = new EditViewDeleteModel();
                $delete_model
                    ->setValidateModel($validate)
                    ->deleteAll($_POST['copy_id']);
                $validate = $delete_model->getValidateModel();

                $this->validateConfirmActionsInstalledModules();
            }
        }

        return $this->renderJson(array(
            'status' => (($validate->error_count+$validate->warning_count) ? false : true),
            'messages' => $validate->getValidateResultHtml(),
        ));
     
    }





    /**
     * Удаление данных модуля
     */
    public function actionDeleteModuleData(){
        $validate = new Validate();
        $copy_id_list = \Yii::app()->request->getParam('copy_id', false);

        if(empty($copy_id_list)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
        } else {
            $status = true;

            foreach($copy_id_list as $copy_id){
                $delete_result = EditViewDeleteModel::getInstance()
                                                ->setLoggingRemove(false)
                                                ->prepare($copy_id, 'all')
                                                ->delete()
                                                ->getStatus();
                \QueryDeleteModel::getInstance()->executeAllData();
                if($status !== false) $status = $delete_result;
            }


        }

        return $this->renderJson(array(
            'status' => $status
        ));

    }



    /**
     * выполнение валидации в подключенных модулях
     */
    private function validateConfirmActionsInstalledModules(){
        $params = \ValidateActionsModel::getParams(\ValidateActionsModel::KEY_CONSTRUCTOR_MODULE_DELETE);
        if(empty($params)) return;

        foreach($params as  $param){
            ExtensionCopyModel::model()->findByPk($param['copy_id'])->getModule(false);
            $class = new $param['class'];
            foreach($param['actions'] as $action)
                $class->runAction($action);
        }
    }




    /**
     * @param $schema
     * @param array $titles
     */
    private function getTitlesFieldsBySchema($schema, array &$titles){
        if(!empty($schema) && is_array($schema)){
           foreach($schema as $node){
               if(!empty($node['field'])){
                   foreach($node['field'] as $val){
                      if($val['type'] == 'label' && !empty($val['params']['title'])){
                         $titles [] = $val['params']['title'];
                      }
                   }
               }
               $this->getTitlesFieldsBySchema($node, $titles);
           }
        }
    }



    /**
     * проверка элементов модуля
     */
    public function actionValidateModule(){
        $validate = new SchemaValidate();

        $schema_fature = array();

        $titles_fields = array();
        if(empty($_POST['schema'])){
            $validate->addValidateResult('e', Yii::t('messages', 'None elements form'));
        } else {
            foreach($_POST['schema']['elements'] as $value){
                $schema_fature[] = Schema::getInstance()->generateDefaultSchema($value);
                $this->getTitlesFieldsBySchema($value, $titles_fields);
            }
        }

        //валидация
        if(empty($_POST['title'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Do not include the name of the module'));
        } else {
            $extension_copy_title = ExtensionCopyModel::model()->FindAll(array('condition'=>'title=:title', 'params'=>array(':title'=>$_POST['title'])));
            if(!empty($extension_copy_title))
            if((!empty($_POST['copy_id']) && $_POST['copy_id'] != $extension_copy_title[0]->copy_id) || empty($_POST['copy_id']))
                $validate->addValidateResult('e', Yii::t('messages', 'Module name already exists'));
        }

        if(count($titles_fields) != count(array_unique($titles_fields))){
           $validate->addValidateResult('e', Yii::t('messages', 'Field names are repeated'));
        }
        
        //валидации алиаса модуля
        if(!empty($_POST['schema']['module_params']['alias'])) {
            //только латиница, цифра и знак подчеркивание
            if(preg_match('/[^a-z0-9_-]/i', $_POST['schema']['module_params']['alias'])) {
                $validate->addValidateResult('e', Yii::t('messages', 'The module alias can contain only Latin characters or numbers'));
            }else {
                
                $condition = 'alias=:alias';
                $params = array(':alias'=>$_POST['schema']['module_params']['alias']);

                if(!empty($_POST['copy_id'])) {
                    $condition .= ' AND copy_id<>:copy_id';
                    $params = array_merge($params, array(':copy_id'=>$_POST['copy_id']));  
                }

                $extension_copy_name = ExtensionCopyModel::model()->FindAll(array('condition'=>$condition, 'params'=>$params));

                if(!empty($extension_copy_name))
                    $validate->addValidateResult('e', Yii::t('messages', 'Module alias already exists'));
            }
            
        }
        //валидация схемы
        if(!empty($_POST['copy_id'])){
            $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
            if(!$extension_copy->schema && $extension_copy->schema_fature){
                $extension_copy->schema = $extension_copy->schema_fature;
            }
            $validate->setExtensionCopy($extension_copy);
        }
        $validate->module_params = $_POST['schema']['module_params'];
        $validate->ValidateAll($schema_fature);

        if($validate->beMessages())
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        else {
            return $this->renderJson(array(
                'status' => true,
            ));
        }
    }


    
    /**
     * проверка на валидацию поля типа "Вычисляемое"
     */
    public function actionValidationCalculated(){

        $result = \CalculatedFields::getInstance()
                ->setExtensionCopy(ExtensionCopyModel::model()->findByPk($_GET['copy_id']))
                ->setUseAggregateFunctions(true)
                ->setValidationType(\CalculatedFields::FORMULA_VALIDATE_SIMPLY)
                ->validate($_GET['body']);

        return $this->renderJson(array(
            'status' => $result,
        ));
        
    }



    /**
    * Сохранение экземпляра модуля
    */
    public function actionSaveModule(){
        $validate = new SchemaValidate();

        $schema_fature = array();

        if(empty($_POST['schema'])){
            $validate->addValidateResult('e', Yii::t('messages', 'None elements form'));
        } else {
            foreach($_POST['schema']['elements'] as $value){
                $schema_fature[] = Schema::getInstance()->generateDefaultSchema($value);
            }
            $module_params = $_POST['schema']['module_params'];
        }


        if(!empty($_POST['copy_id'])){
            $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
        } else {
            $extension_copy = new ExtensionCopyModel();
        }
        $extension_copy->setAddDateCreateEntity(false);

        $module = ExtensionModel::model()->findByPk($_POST['extension_id'])->getModule();
        if(empty($_POST['copy_id'])){
            // new
            $prefix_name = $module->getPrefixName();
            if($prefix_name === null) $prefix_name = substr(
                Helper::strToLower(
                    Helper::replaceToSqlParam(
                        Translit::forDataBase($_POST['title'])
                    )
                ), 0, 20
            );

            $data = array(
                'extension_id' => $_POST['extension_id'],
                'prefix_name' => $prefix_name,
                'title' => $_POST['title'],
                'alias' => (array_key_exists('alias', $module_params)) ? $module_params['alias'] : null,
                'schema_fature' => json_encode($schema_fature),
                'menu' => $module->menu,
                'clone' => (integer)$module->clone,
                'set_access' => $module->db_set_access,
                'be_parent_module' => (integer)$module->getBeParentModule(),
                'sort' => ExtensionCopyModel::model()->find(array('select' => 'max(sort) as sort'))->sort + 1,
                'destroy' => (array_key_exists('destroy', $module_params) ? (integer)$module_params['destroy'] : '0'),
                'is_template' => (array_key_exists('is_template', $module_params) ? (integer)$module_params['is_template'] : '0'),
                'menu_display' => (array_key_exists('menu_display', $module_params) ? (integer)$module_params['menu_display'] : '1'),
                'data_if_participant' => (array_key_exists('data_if_participant', $module_params) ? (integer)$module_params['data_if_participant'] : '0'),
                'finished_object' => (array_key_exists('finished_object', $module_params) ? (integer)$module_params['finished_object'] : '0'),
                'show_blocks' => (array_key_exists('show_blocks', $module_params) ? (integer)$module_params['show_blocks'] : '1'),
                'calendar_view' => (array_key_exists('calendar_view', $module_params) ? (integer)$module_params['calendar_view'] : '0'),
                'make_relate' => '1',
            );
        } else {
            // update
    
            if(!empty($_POST['cleanable_select_ids'])) {
                
                //очищаем статусы и поля типа "выбор значения", которые есть в данном массиве
                $schema_parser = $extension_copy->getSchemaParse();
            
                $alias = 'evm_' . $extension_copy->copy_id;
                $dinamic_params = array(
                    'tableName'=> $extension_copy->getTableName(null, false, true)
                );
                
                foreach($_POST['cleanable_select_ids'] as $cleanable_select_ids) {
                    $editViewModels = EditViewModel::modelR($alias, $dinamic_params)->findAll($cleanable_select_ids['field_name'] . '=:' . $cleanable_select_ids['field_name'], array(':'.$cleanable_select_ids['field_name'] => $cleanable_select_ids['select_id']));
                    foreach($editViewModels as $editViewModel) {
                        $editViewModel->scenario = 'update_scalar';
                        $editViewModel->setElementSchema($schema_parser);
                        $editViewModel->extension_copy = $extension_copy;
                        $editViewModel->setMyAttributes(array($cleanable_select_ids['field_name'] => null));
                        $editViewModel->save();
                    }
                }
            }

            $data = array(
                'title' => $_POST['title'],
                'alias' => (array_key_exists('alias', $module_params)) ? $module_params['alias'] : null,
                'menu' => $module->menu,
                'be_parent_module' => (integer)$module->getBeParentModule(),
                'schema_fature' => json_encode($schema_fature),
                'destroy' => (array_key_exists('destroy', $module_params) ? (integer)$module_params['destroy'] : '0'),
                'is_template' => (array_key_exists('is_template', $module_params) ? (integer)$module_params['is_template'] : '0'),
                'menu_display' => (array_key_exists('menu_display', $module_params) ? (integer)$module_params['menu_display'] : '1'),
                'data_if_participant' => (array_key_exists('data_if_participant', $module_params) ? (integer)$module_params['data_if_participant'] : '0'),
                'finished_object' => (array_key_exists('finished_object', $module_params) ? (integer)$module_params['finished_object'] : '0'),
                'show_blocks' => (array_key_exists('show_blocks', $module_params) ? (integer)$module_params['show_blocks'] : '1'),
                'calendar_view' => (array_key_exists('calendar_view', $module_params) ? (integer)$module_params['calendar_view'] : '0'),
            );
        }

        $extension_copy->attributes = $data;
        if(!$extension_copy->save()){
            $validate->addValidateResult('e', Yii::t('messages', 'Error saving module'));
        }

        if($validate->error_count){
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }


        $this->installModule($extension_copy);
        $validate->addValidateResult('s', Yii::t('messages', 'Create an instance of the module "{s}"', array('{s}'=>$extension_copy->title)));


        return $this->renderJson(array(
            'status' => ($validate->error_count ? false : true),
            'messages' => $validate->getValidateResultHtml(),
        ));

    }






    /**
    * Инсталяция модуля(ей))
    */
    public function installModule($extension_copy){
        $schema = $extension_copy->getSchema();
        if(empty($schema)){
            InstallModule::getInstance($extension_copy)
                ->parseSchemaFature()
                ->createTables();
        } else {
            InstallModule::getInstance($extension_copy)
                ->parseSchema()
                ->parseSchemaFature()
                ->updateTables();
        }
        $this->addRelateTitleChildrenForTasks($extension_copy, $schema);
    }







    /**
     * добавляем автоматом модуль к модулю Задачи
     */
    public function addRelateTitleChildrenForTasks($extension_copy){
        $primary_field_params = $extension_copy->getPrimaryField();
        if($primary_field_params['params']['type'] == 'relate_string'){
            if((integer)$primary_field_params['params']['relate_module_copy_id'] !== ExtensionCopyModel::MODULE_TASKS) return;
            $extension_copy_child = ExtensionCopyModel::model()->findByPk($primary_field_params['params']['relate_module_copy_id']);
            $schema_child = $extension_copy_child->getSchemaParse();
            $relate_field_params = $extension_copy_child->getFirstFieldParamsForRelate();

            if(SchemaOperation::getInstance()->isModuleHookUp($schema_child, $extension_copy->copy_id)){
                return;
            } else {
                $schema =
                    array_merge(
                    $extension_copy_child->getSchema(),
                    array(
                        Schema::getInstance()->generateDefaultSchema(
                        array(
                            'block' =>
                                array(
                                    'type' => 'block',
                                    'params' => array(
                                        'title' => $extension_copy->title,
                                        'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000)),
                                    ),
                                    'elements' => array(
                                        array(
                                            'block_panel' => array(
                                                array(
                                                    'type' => 'block_panel',
                                                    'params' => array('count_panels' => 1),
                                                    'elements' => array(
                                                        array(
                                                            'type' => 'panel',
                                                            'params' => array(
                                                                'active_count_select_fields' => 1,
                                                            ),
                                                            'elements' => array(
                                                                array(
                                                                    'field' => array(
                                                                        array(
                                                                            'type' => 'label',
                                                                            'params' => array('title' => $extension_copy->title),
                                                                        ),
                                                                        array(
                                                                            'type' => 'block_field_type',
                                                                            'params' => array('count_edit' => 1),
                                                                            'elements' => array(
                                                                                array(
                                                                                    'type'=>'edit',
                                                                                    'params'=> array(
                                                                                        'name' => $extension_copy_child->prefix_name . '_' . $extension_copy->prefix_name . '_1',
                                                                                        'type' => 'relate',
                                                                                        'relate_module_copy_id' => $extension_copy->copy_id,
                                                                                        'relate_index' => ($extension_copy->copy_id+1) * 10 + 98,
                                                                                        'relate_field' => (!empty($relate_field_params) ? $relate_field_params['params']['name'] : null),
                                                                                        'group_index' => ($extension_copy->copy_id+1) * 10 + 99,
                                                                                    ),
                                                                                ),
                                                                            ),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),

                                    ),
                                ),
                            )
                        )
                    )
                );
                // save & install
                $data = array(
                    'schema_fature' => json_encode($schema),
                );
                $extension_copy_child->attributes = $data;
                if($extension_copy_child->save()){
                    $extension_copy_child->setAddDateCreateEntity(false);
                    $this->installModule($extension_copy_child);
                }
            }
        }

    }







    /**
    * Копия модуля
    */
    public function actionCopyModule(){
        $validate = new SchemaValidate();

        if(empty($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
        } else {
            foreach($_POST['copy_id'] as $value){
                if(empty($value)) continue;
                $extension_copy = ExtensionCopyModel::model()->FindByPk($value);
                if($extension_copy->clone == '0'){
                    $validate->addValidateResult('e', Yii::t('messages',  'Module "{s}" copy is prohibited', array('{s}' => $extension_copy->title)));
                    continue;
                }


                $extension_copy_title = $extension_copy->title;
                $title = $extension_copy_title . ' [Copy]';
                for($lich = 1; $lich < 1000; $lich++){
                    $extension_copy_list = ExtensionCopyModel::model()->FindAll(array('condition'=>'title=:title', 'params'=>array(':title'=>$title)));
                    if(!empty($extension_copy_list)){
                        $title = $extension_copy_title . ' [Copy ' . $lich .']' ;
                        continue;
                    }
                    break;
                }

                $extension_copy_new = new ExtensionCopyModel();
                $extension_copy_new->setAddDateCreateEntity(false);
                $extension_copy_new->extension_id = $extension_copy->extension_id;
                $extension_copy_new->prefix_name = substr(Helper::strToLower(Translit::forDataBase($extension_copy_title . $lich)), 0, 20);
                $extension_copy_new->title = $title;
                $extension_copy_new->description = $extension_copy->description;
                $extension_copy_new->schema_fature = (!empty($extension_copy->schema_fature) ? $extension_copy->schema_fature : $extension_copy->schema);
                $extension_copy_new->menu = $extension_copy->menu;
                $extension_copy_new->clone = $extension_copy->clone;
                $extension_copy_new->active = $extension_copy->active;
                $extension_copy_new->set_access = $extension_copy->getModule()->db_set_access;
                $extension_copy_new->constructor = $extension_copy->constructor;
                $extension_copy_new->sort = ExtensionCopyModel::model()->find(array('select' => 'max(sort) as sort'))->sort + 1;
                $extension_copy_new->be_parent_module = $extension_copy->be_parent_module;
                $extension_copy_new->destroy = $extension_copy->destroy;
                $extension_copy_new->is_template = (string)$extension_copy->is_template;
                $extension_copy_new->data_if_participant = $extension_copy->data_if_participant;
                $extension_copy_new->menu_display = $extension_copy->menu_display;
                $extension_copy_new->finished_object = $extension_copy->finished_object;
                $extension_copy_new->make_relate = $extension_copy->make_relate;

                if(!$extension_copy_new->save()){
                    $validate->addValidateResultFromModel($extension_copy_new->getErrors());
                } else {
                    $this->installModule($extension_copy_new);

                    $validate->addValidateResult('i', Yii::t('messages', 'Create a new module "{s}"', array('{s}'=> $title)));
                }
            }
        }

        return $this->renderJson(array(
            'status' => ($validate->error_count ? false : true),
            'messages' => $validate->getValidateResultHtml(),
        ));

    }



    /**
    *  Публикация/снятие с публикации модуля
    */
    public function actionSetModuleStatus(){
        $validate = new SchemaValidate();

        if(empty($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined module parameters'));
        } else {
            $model = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
            $model->active = $_POST['active'];
            $model->save();
        }

        return $this->renderJson(array(
            'status' => ($validate->error_count ? false : true),
            'messages' => $validate->getValidateResultHtml(),
        ));

    }



    public function actionIsUsedSelect($copy_id, $field_name, $select_id, $delete_agree=false){
        $validate = new SchemaValidate();

        if(empty($copy_id) && empty($field_name) && empty($select_id)){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
        } else {
            $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
            if(!$extension_copy->getFieldSchemaParams($field_name)){
                return $this->renderJson(array(
                    'status' => ($validate->error_count ? false : true),
                    'messages' => $validate->getValidateResultHtml(),
                ));
            }

            $schema_parser = $extension_copy->getSchemaParse();
            
            $alias = 'evm_' . $extension_copy->copy_id;
            $dinamic_params = array(
                'tableName'=> $extension_copy->getTableName(null, false, true)
            );

            $count = EditViewModel::modelR($alias, $dinamic_params)->count($field_name . '=:' . $field_name, array(':'.$field_name => $select_id));
            if((integer)$count == 0){
                $validate->addValidateResult('s', 'ok');
            } else {
                if($delete_agree) {
                    //удаление значения и очистка значений записей
                    $editViewModels = EditViewModel::modelR($alias, $dinamic_params)->findAll($field_name . '=:' . $field_name, array(':'.$field_name => $select_id));
                    foreach($editViewModels as $editViewModel) {
                        $editViewModel->scenario = 'update_scalar';
                        $editViewModel->setElementSchema($schema_parser);
                        $editViewModel->extension_copy = $extension_copy;
                        $editViewModel->setMyAttributes(array($field_name => null));
                        $editViewModel->save();
                    }
                    $validate->addValidateResult('s', 'ok');
                }else {
                    //$validate->addValidateResult('e', Yii::t('messages', 'This element of the list has already been used in the selection of values') . '.<br />' . Yii::t('messages', 'Removal of prohibited'));
                    $validate->addValidateResultConfirm(
                        'c',
                        \Yii::t('messages', 'This value is used in the system. Do you really want to delete it?'),
                        null,
                        false
                    );
                }
            }
        }

        return $this->renderJson(array(
            'status' => ($count && !$delete_agree) ? false : true,
            'messages' => $validate->getValidateResultHtml(),
        ));


    }


    public function actionSettings(){
        $extension_copy = ExtensionCopyModel::model()->findByPk($_POST['copy_id']);
        $this->renderPartial('//dialogs/constructor-settings', array('params' => $_POST['params'], 'extension_copy' => $extension_copy));
    }



    public function actionShowSchema($copy_id){
        print_r(ExtensionCopyModel::model()->findByPk($copy_id)->getSchema());
    }




}
