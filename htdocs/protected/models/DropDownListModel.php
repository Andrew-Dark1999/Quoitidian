<?php

/**
 * Class DropDownListModel - элемент DropDownList
 *
 * @author Alex R.
 */


class DropDownListModel{

    const WIDGET_VIEW_BUTTON            = 'view-button';
    const WIDGET_VIEW_SPAN              = 'view-span';
    const WIDGET_VIEW_BUTTON_ACTIONS    = 'view-button-actions';
    const WIDGET_OPTIONS                = 'options';
    const WIDGET_OPTIONS_PANEL          = 'options-panel';
    const WIDGET_OPTION                 = 'option';

    //button actions
    const BUTTON_ACTION_ADD         = 'add';
    const BUTTON_ACTION_ADD_AUTO    = 'add_auto';
    const BUTTON_ACTION_ADD_CHANNEL = 'add_channel';
    const BUTTON_ACTION_REMOVE      = 'remove';
    const BUTTON_ACTION_EDIT        = 'edit';


    const DATA_TYPE_1       = 'Type1'; // EditView relate - EditView, inline-edit
    const DATA_TYPE_2       = 'Type2'; // EditView relate_this
    const DATA_TYPE_3       = 'Type3'; // EditView relate_participant
    const DATA_TYPE_4       = 'Type4'; // EditView relate_dinamic for Process or Task
    const DATA_TYPE_5       = 'Type5'; // ListView relate_dinamic for Process or Task
    const DATA_TYPE_6       = 'Type6'; // SubModules
    const DATA_TYPE_7       = 'Type7'; // Filter. Relate condition list
    const DATA_TYPE_8       = 'Type8'; // ListView. relate
    const DATA_TYPE_9       = 'Type9'; // EditView. Button "Channel"


    private $_vars;
    private $_status = true;

    private $_data = array(); // подготовленные данные
    private $_html; // готовый подготовленный html верстки из $_data

    private $_active_data_type;
    private $_active_group_data = \DropDownListOptionsModel::GROUP_DATA_SDM_OPTION_LIST;
    private $_active_widget_view;

    //private $_relate_model;
    private $_entity_model;

    private $_default_data_id = null; //запись, открываемая по-умолчанию

    public static function getInstance(){
        return new self();
    }




    public function getResult(){
        $result = array(
            'status' => $this->_status,
            'vars' => $this->_vars,
            'data' => $this->_data,
        );

        return $result;
    }


    public function getResultHtml(){
        $result = array(
            'status' => $this->_status,
            'vars' => $this->_vars,
            'html' => $this->_html,
        );

        return $result;
    }


    public function setActiveWidgetView($active_widget_view){
        $this->_active_widget_view = $active_widget_view;
        return $this;
    }


    public function setActiveDataType($active_data_type){
        $this->_active_data_type = $active_data_type;
        return $this;
    }


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }
    
    
    public function setDefaultDataId($default_data_id){
        $this->_default_data_id = $default_data_id;
        return $this;
    }


    /*
    public function setObjectType($object_type){
        $this->_object_type = $object_type;
        return $this;
    }
    */


    private function prepareData(){
        $this->{'prepareData' . $this->_active_data_type}();

        $this->prepareDataAfter();

        return $this;
    }



    public function prepareHtml(){
        $this->prepareData();

        if(empty($this->_data)) return $this;


        $params = array(
                'view' => $this->_active_widget_view,
                'vars' => $this->_data);

        $this->_html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params, true);

        return $this;
    }


    /**
     * getHtmlOptions
     */
    private function getHtmlOptions($option_data_model_list, $there_is_data){
        //html_options
        $params = array(
            'vars' => array(
                'copy_id' => $this->_vars['extension_copy']->copy_id,
                'field_name' => $this->_vars['schema']['params']['name'],
                'relate_module_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                'options' => $this->_data['options'],
            ),
            'active_group_data' => $this->_active_group_data,
        );

        $options_result = \DropDownListOptionsModel::getInstance()
                                    ->setOptionDataModelList($option_data_model_list)
                                    ->setThereIsData($there_is_data)
                                    ->setAllParams($params)
                                    ->setPrepareDataList(false)
                                    ->initEntities()
                                    ->prepareOptionsHtmlList(true)
                                    ->getResult();
        return $options_result;
    }




    public function paramsType1AddOptionsButtonActions(){
        $schema_primary = $this->_vars['relate_extension_copy']->getPrimaryField();

        if($schema_primary == false || $schema_primary['params']['type'] == \Fields::MFT_DISPLAY_NONE){
            return;
        }

        $span_title = true;
        if($this->_vars['relate_extension_copy']->isAutoEntityTitle()){
            $span_title = false;
        }

        $this->_data['options']['button_actions'] = array(
            array(
                'name' => self::BUTTON_ACTION_ADD_AUTO,
                'attr' => array('span_title' => $span_title),
            )
        );
    }





    /**
     * prepareDataType1 - EditView "relate"
     */
    private function prepareDataType1(){
        $this->setDefaultData(self::DATA_TYPE_1);

        $this->_vars['relate_extension_copy'] = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
        $this->_data['options']['attr']['data-relate_copy_id'] = (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null);

        //edit_view_relate_model
        $relate_model = \EditViewRelateModel::getInstance()
                                ->setVars($this->_vars)
                                ->prepareVars();

        $relate_value = array();
        if(!isset($this->_vars['schema']['params']['relate_get_value']) || (boolean)$this->_vars['schema']['params']['relate_get_value'] == true){
            $relate_value = $relate_model->getValue($this->_default_data_id);
        }

        if(!empty($this->_default_data_id) && count($relate_value)){
            $this->_data['view']['button']['data-id'] = $this->_default_data_id;
            $relate_model->setId($this->_default_data_id);
        }

        $this->_data['view']['button']['data-reloader'] = $relate_model->getReloaderStatus();
        $this->_data['view']['button']['data-module_parent'] = ($relate_model->isModuleParent() ? 1 : 0);
        $this->_data['view']['button']['data-id'] = (!isset($this->_vars['schema']['params']['relate_get_value']) || (boolean)$this->_vars['schema']['params']['relate_get_value'] == true ? $relate_model->getId() : '');

        $da = $relate_model->getRelateDisabledAttr();
        if(!empty($da)){
            $this->_data['view']['button']['disabled'] = 'disabled';
            //$this->_data['view']['button_actions'] = array();
        }

        if($this->_vars['relate_extension_copy']->copy_id == \ExtensionCopyModel::MODULE_PROCESS){
            //$this->_data['view']['button_actions'] = array();
        }

        // set button_actions for options block
        $this->paramsType1AddOptionsButtonActions();


        //html_view
        $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $this->_vars['schema']['params']);

        //html_options
        $data_list = $relate_model->getOptionsDataList();
        $this->_data['options']['attr']['data-there_is_data'] = (int)$relate_model->getIsSetNextOptionListData();
        $options_result = $this->getHtmlOptions($data_list, $relate_model->getIsSetNextOptionListData());

        $this->_data['view']['html_options'] = $options_result['html_options'];

        //relate_model
        $this->_data['relate_model'] = $relate_model;

        // set Entity properties
        $this
            ->setEntity($relate_model)
            ->setEntityVarsType1($relate_model)
            ->setEntityEventsType1()
            ->resetEntityToProperties()
        ;

        $this->_data['view']['attr']['data-entity_key'] = $this->_entity_model->getKey();
        $this->_data['view']['entity_model'] = $this->_entity_model;

        return $this;
    }



    private function prepareDataType2(){
        return $this;
    }


    private function prepareDataType3(){
        return $this;
    }




    /**
     * paramsForProcessType4
     */
    private function paramsType4(){
        $this->_vars['schema']['params']['relate_module_copy_id'] = $this->_vars['extension_data']['related_module'];

        return array(
            'process_id' => $this->_vars['extension_data']->{$this->_vars['extension_copy']->prefix_name . '_id'},
        );
    }



    /**
     * paramsForTasksType4
     */
    private function paramsType4ForTasks(){
        $process_id = null;
        $relate_module_copy_id = null;

        if(\Yii::app()->controller->module->extensionCopy->copy_id == \ExtensionCopyModel::MODULE_PROCESS){
            $process_id = \Process\models\ProcessModel::getInstance()->process_id;
        }


        if($process_id === null && !empty($this->_vars['extension_data'])){
            $tasks_id = $this->_vars['extension_data']->{$this->_vars['extension_copy']->prefix_name . '_id'};
            $data_model = \DataModel::getInstance()
                ->setFrom('{{process_operations}}')
                ->setWhere('copy_id=:copy_id AND card_id=:card_id', array(':copy_id'=>\ExtensionCopyModel::MODULE_TASKS, ':card_id'=>$tasks_id))
                ->findRow();

            // переключаем на модуль Process
            if(!empty($data_model)){
                $process_id = $data_model['process_id'];
            }
        }

        if(!empty($process_id)){
            $data_model = \DataModel::getInstance()
                ->setFrom('{{process}}')
                ->setWhere('process_id=:process_id', array(':process_id'=>$process_id))
                ->findRow();
            $relate_module_copy_id = $data_model['related_module'];
        }


        $this->_vars['schema']['params']['relate_module_copy_id'] = $relate_module_copy_id;

        return array(
            'process_id' => $process_id,
        );
    }


    /**
     * prepareDataType4 - EditView "relate_dinamic" for Process or Task
     * @return $this
     */
    private function prepareDataType4(){
        $this->setDefaultData(self::DATA_TYPE_4);

        $state = false;

        // проверка для обычного модуля
        if(!empty($this->_vars['extension_data']['related_module'])){
            $params = $this->paramsType4();
            $state = true;
        } else
        // проверка для Tasks
        if(in_array($this->_vars['extension_copy']->copy_id, [\ExtensionCopyModel::MODULE_TASKS, \ExtensionCopyModel::MODULE_PROCESS])){
            $params = $this->paramsType4ForTasks();
            $state = true;
        }

        if($state && !empty($this->_vars['schema']['params']['relate_module_copy_id'])){
            $this->_vars['schema']['params']['relate_field'] = array('module_title');

            $this->_vars['relate_extension_copy'] = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
            $this->_data['options']['attr']['data-relate_copy_id'] = (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null);

            if(!empty($this->_vars['default_data'])){
                $this->_data['view']['button']['data-id'] = $this->_vars['default_data'];
            } else {
                \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
                $relate_data = \Process\models\BindingObjectModel::getInstance()
                                                    ->setVars(array('process_id' => $params['process_id']))
                                                    ->getRelateDataByProcessId();
                if(!empty($relate_data)){
                    $this->_data['view']['button']['data-id'] = $relate_data[$this->_vars['relate_extension_copy']->prefix_name . '_id'];
                }
            }


            $relate_model = \EditViewRelateModel::getInstance()
                                ->setVars($this->_vars)
                                ->prepareVars()
                                ->setRelateExtensionCopy($this->_vars['relate_extension_copy']);

            if(!empty($this->_data['view']['button']['data-id'])){
                $relate_model->setId($this->_data['view']['button']['data-id']);
                $relate_value = $relate_model->getValue();
                $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $this->_vars['schema']['params']);
            }


            //html_options
            $data_list = $relate_model->getOptionsDataList($relate_model->getOptionsDataParamsDefault());

            $this->_data['options']['attr']['data-there_is_data'] = (int)$relate_model->getIsSetNextOptionListData();

            $options_result = $this->getHtmlOptions($data_list, $relate_model->getIsSetNextOptionListData());

            $this->_data['view']['html_options'] = $options_result['html_options'];

            $this->_data['relate_model'] = $relate_model;
        }

        return $this;
    }









    /**
     * paramsType5
     */
    private function paramsType5(){
        $this->_vars['schema']['params']['relate_module_copy_id'] = $this->_vars['extension_data']['related_module'];

        return array(
            'process_id' => $this->_vars['extension_data'][$this->_vars['extension_copy']->prefix_name . '_id'],
        );
    }



    /**
     * paramsForTasksType5
     */
    private function paramsType5ForTasks(){
        $process_id = null;
        $relate_module_copy_id = null;

        if(\Yii::app()->controller->module->extensionCopy->copy_id == \ExtensionCopyModel::MODULE_PROCESS){
            $process_id = \Process\models\ProcessModel::getInstance()->process_id;
        }


        if($process_id   === null && !empty($this->_vars['extension_data'])){
            $tasks_id = $this->_vars['extension_data'][$this->_vars['extension_copy']->prefix_name . '_id'];
            $data_model = \DataModel::getInstance()
                ->setFrom('{{process_operations}}')
                ->setWhere('copy_id=:copy_id AND card_id=:card_id', array(':copy_id'=>\ExtensionCopyModel::MODULE_TASKS, ':card_id'=>$tasks_id))
                ->findRow();

            // переключаем на модуль Process
            if(!empty($data_model)){
                $process_id = $data_model['process_id'];
            }
        }

        if(!empty($process_id)){
            $data_model = \DataModel::getInstance()
                ->setFrom('{{process}}')
                ->setWhere('process_id=:process_id', array(':process_id'=>$process_id))
                ->findRow();
            $relate_module_copy_id = $data_model['related_module'];
        }


        $this->_vars['schema']['params']['relate_module_copy_id'] = $relate_module_copy_id;

        return array(
            'process_id' => $process_id,
        );
    }


    /**
     * prepareDataType5 - ListView "relate_dinamic" for Process or Task
     * @return $this
     */
    private function prepareDataType5(){
        $this->setDefaultData(self::DATA_TYPE_5);

        $state = false;

        // проверка для Tasks
        if($this->_vars['extension_copy']->copy_id == \ExtensionCopyModel::MODULE_TASKS){
            $params = $this->paramsType5ForTasks();
            $state = true;
        } else{
            if(!empty($this->_vars['extension_data']['related_module'])){
                $params = $this->paramsType5();
                $state = true;
            }
        }


        if($state && !empty($this->_vars['schema']['params']['relate_module_copy_id'])){
            $this->_vars['schema']['params']['relate_field'] = array('module_title');

            $this->_vars['relate_extension_copy'] = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
            $this->_data['options']['attr']['data-relate_copy_id'] = (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null);

            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
            $relate_data = \Process\models\BindingObjectModel::getInstance()
                                    ->setVars(array('process_id' => $params['process_id']), true)
                                    ->getRelateDataByProcessId();

            if(!empty($relate_data)){
                $this->_data['view']['span']['data-id'] = $relate_data[$this->_vars['relate_extension_copy']->prefix_name . '_id'];
            }

            $relate_model = \EditViewRelateModel::getInstance()
                ->setVars($this->_vars)
                ->setRelateExtensionCopy($this->_vars['relate_extension_copy']);

            if(!empty($this->_data['view']['span']['data-id'])){
                $relate_model->setId($this->_data['view']['span']['data-id']);

                $relate_value = $relate_model->getValue();
                $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $this->_vars['schema']['params'], $this->_vars['relate_add_avatar']);
            }
        }

        return $this;
    }






    /**
     * prepareDataType7 - Filter. Relate condition list
     */
    private function prepareDataType7(){
        $this->setDefaultData(self::DATA_TYPE_7);

        $this->_vars['parent_copy_id'] = array('pci' => null, 'parent_copy_id' => null);
        $this->_vars['parent_data_id'] = array('pci' => null, 'parent_data_id' => null);
        $this->_vars['primary_entities'] = array('primary_pci' => null, 'primary_pdi' => null);


        $this->_vars['relate_extension_copy'] = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
        $this->_data['options']['attr']['data-relate_copy_id'] = (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null);

        $id = (!empty($this->_vars['condition_value_value'][0]) ? $this->_vars['condition_value_value'][0] : null);

        $action_model = new EditViewActionModel($this->_vars['extension_copy']->copy_id);
        $action_model
            ->setEditData(array('id' => $id))
            ->createEditViewModel();
        $edit_view_model = $action_model->getEditModel();

        $this->_vars['extension_data'] = $edit_view_model;

        //relate_model
        $relate_model = \EditViewRelateModel::getInstance();
        $relate_model
            ->setVars($this->_vars)
            ->setRelateExtensionCopy()
            ->setPci()
            ->setPdi();

        if($id){
            $relate_model->setId($id);
        }


        $relate_value = array();
        if(!isset($this->_vars['schema']['params']['relate_get_value']) || (boolean)$this->_vars['schema']['params']['relate_get_value'] == true){
            $relate_value = $relate_model->getValue($this->_default_data_id);
            $relate_model->setId($this->_default_data_id);
        }

        $this->_data['view']['button']['data-module_parent'] = ($relate_model->isModuleParent() ? 1 : 0);
        $this->_data['view']['button']['data-id'] = (!isset($this->_vars['schema']['params']['relate_get_value']) || (boolean)$this->_vars['schema']['params']['relate_get_value'] == true ? $relate_model->getId() : '');


        if(FilterModel::$_access_to_change == false){
            $this->_data['view']['button']['disabled'] = 'disabled';
            //$this->_data['view']['button_actions'] = array();
        }


        //html_view
        $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $this->_vars['schema']['params']);

        //html_options
        $data_list = $relate_model->getOptionsDataList();
        $this->_data['options']['attr']['data-there_is_data'] = (int)$relate_model->getIsSetNextOptionListData();
        $options_result = $this->getHtmlOptions($data_list, $relate_model->getIsSetNextOptionListData());

        $this->_data['view']['html_options'] = $options_result['html_options'];

        //relate_model
        $this->_data['relate_model'] = $relate_model;

        return $this;
    }






    private function getRelateDataIdList($relate_extension_copy){
        $data_id = $this->_vars['extension_data'][$this->_vars['extension_copy']->getPkFieldName()];
        if($data_id == false){
            return;
        }

        $relate_module_table = ModuleTablesModel::model()->find(array(
                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                                    'params' => array(
                                        ':copy_id' => $this->_vars['extension_copy']->copy_id,
                                        ':relate_copy_id' => $relate_extension_copy->copy_id)));

        $id_list = DataModel::getInstance()
                                    ->setSelect($relate_extension_copy->getPkFieldName())
                                    ->setFrom('{{' . $relate_module_table->table_name . '}}')
                                    ->setWhere($this->_vars['extension_copy']->getPkFieldName() . ' = :data_id',
                                        array(':data_id' => $data_id))
                                    ->findCol();
        return $id_list;
    }









    /**
     * prepareDataType8 - ListView
     * @return $this
     */
    private function prepareDataType8(){
        $this->setDefaultData(self::DATA_TYPE_8);

        $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
        $this->_vars['relate_extension_copy'] = $relate_extension_copy;


        $data_id_list = $this->getRelateDataIdList($relate_extension_copy);

        $this->_data['view']['span']['data-id'] = '';
        $this->_data['view']['html_view'] = '';



        if($this->_vars['schema']['params']['relate_many_select'] && count($data_id_list) > 1){
            $this->_data['view']['span']['data-id'] = implode(',', $data_id_list);
            $this->_data['view']['html_view'] = count($data_id_list);
        }else {
            if($data_id_list){
                //$data_id_list = count 1
                $action_model = new EditViewActionModel($relate_extension_copy->copy_id);
                $action_model
                    ->setEditData(array('id' => $data_id_list[0]))
                    ->createEditViewModel();

                $edit_view_model = $action_model->getEditModel();

                if($edit_view_model){
                    $this->_vars['extension_data'] = $edit_view_model;

                    //relate_model
                    $relate_model = \EditViewRelateModel::getInstance();
                    $relate_model
                        ->setVars($this->_vars)
                        ->setRelateExtensionCopy()
                        ->setPci()
                        ->setPdi();

                    if($data_id_list[0]){
                        $relate_model->setId($data_id_list[0]);
                    }

                    $relate_value = $relate_model->getValue($data_id_list[0]);

                    // set Entity properties - general
                    $this
                        ->setEntity($relate_model)
                        ->setEntityForVars(true)
                        ->setEntityVarsType8($relate_model)
                        ->setEntityEventsType8()
                        ->resetEntityToProperties();

                    // set Entity properties for Edit link
                    $this
                        ->setEntity($relate_model)
                        ->setEntityForVars(false)
                        ->setEntityVarsType8($relate_model)
                        ->setEntityEventsType8After();
                    $this->_data['view']['entity_model']  = $this->_entity_model;

                    $this->_data['view']['span']['data-entity_key'] = $this->_entity_model->getKey();
                    $this->_data['view']['span']['data-id'] = implode(',', $data_id_list);
                    $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $this->_vars['schema']['params']);

                    $this->resetEntityToProperties();
                }

            } else {
                // if ID = null
                $action_model = new EditViewActionModel($relate_extension_copy->copy_id);
                $action_model
                    ->setEditData(array('id' => null))
                    ->createEditViewModel();

                $edit_view_model = $action_model->getEditModel();
                $this->_vars['extension_data'] = $edit_view_model;

                //relate_model
                $relate_model = \EditViewRelateModel::getInstance();
                $relate_model
                    ->setVars($this->_vars)
                    ->setRelateExtensionCopy()
                    ->setPci()
                    ->setPdi();

                // set Entity properties
                $this
                    ->setEntity($relate_model)
                    ->setEntityForVars(true)
                    ->setEntityVarsType8($relate_model)
                    ->setEntityEventsType8()
                    ->resetEntityToProperties();

                $this->_data['view']['entity_model']  = $this->_entity_model;
            }
        }


        return $this;
    }






    public static function getChannelCopyId(){
        return \ExtensionCopyModel::MODULE_COMMUNICATIONS;
    }



    private function getPrepareDataType9Schema(){
        return $this->_vars['extension_copy']->getFieldSchemaParams(self::getChannelCopyId());
    }



    /**
     * prepareDataType9 - EditView. Button "Channel"
     */
    private function prepareDataType9(){
        $this->_active_group_data = DropDownListOptionsModel::GROUP_DATA_ACTIVITY_OPTION_LIST;
        $this->setDefaultData(self::DATA_TYPE_9);

        $this->_vars['schema'] = $this->getPrepareDataType9Schema();

        $this->_vars['relate_extension_copy'] = \ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
        $this->_data['options']['attr']['data-relate_copy_id'] = (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null);

        $vars = array(
            'parent_copy_id' => $this->_vars['extension_copy']->copy_id,
            'parent_data_id' => $this->_vars['data_id'],
        );

        $relate_model = (new EditViewSubModuleModel())
            ->setVars($vars)
            ->setExtensionCopy($this->_vars['relate_extension_copy'])
            ->prepareVars();

        //html_view
        $this->_data['view']['html_view'] = Yii::t('communications', 'Chat');

        if($this->_default_data_id !== null){
            $relate_value = $relate_model->getValue($this->_default_data_id);
            if($relate_value === false){
                $this->_data['view']['id'] = null;
            } else {
                $field_schema = $this->_vars['extension_copy']->getFirstFieldParamsForRelate();
                $field_params = $field_schema['params'];
                $field_params['relate_field'] = $field_params['name'];
                $field_params['relate_module_copy_id'] = $this->_vars['relate_extension_copy']->copy_id;

                $this->_data['view']['html_view'] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $field_params, false);
            }
        }

        //html_options
        $data_list = $relate_model->getSelectedDataList();
        $this->_data['options']['attr']['data-there_is_data'] = (int)$relate_model->getIsSetNextOptionListData();
        $options_result = $this->getHtmlOptions($data_list, $relate_model->getIsSetNextOptionListData());

        $this->_data['view']['html_options'] = $options_result['html_options'];

        //relate_model
        $this->_data['relate_model'] = $relate_model;

        return $this;
    }

















    /**
     * prepareDataAfter - доп. параметры
     * @return $this
     */
    private function prepareDataAfter(){
        switch($this->_active_data_type){
            case self::DATA_TYPE_1:
                $result_button = array(
                    'class' => 'btn btn-white dropdown-toggle element element_relate',
                    'data-relate_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                );
                $this->_data['view']['button'] = array_merge($this->_data['view']['button'], $result_button);
                $this->_data['options']['attr']['data-relate_copy_id'] = $this->_vars['schema']['params']['relate_module_copy_id'];
                break;

            case self::DATA_TYPE_2:
                break;

            case self::DATA_TYPE_3:
                break;

            case self::DATA_TYPE_4:
                $result_button = array(
                    'class' => 'btn btn-white dropdown-toggle element element_relate_dinamic',
                    'data-sub_type' => 'dinamic',
                    'data-relate_copy_id' => (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null),
                    'disabled' => 'disabled',
                    'data-save' => '0',
                );

                $this->_data['view']['button'] = array_merge($this->_data['view']['button'], $result_button);
                //$this->_data['view']['button_actions'] = array();
                break;

            case self::DATA_TYPE_5:
                $result_span = array(
                    'class' => 'edit_view_show modal_dialog element_data element name lessening',
                    'data-type' => 'edit_view_edit',
                    'data-controller' => 'sdm',
                    'data-name' => $this->_vars['schema']['params']['name'],
                    'data-relate_copy_id' => (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null),
                );

                $this->_data['view']['span'] = array_merge($this->_data['view']['span'], $result_span);
                break;

            case self::DATA_TYPE_8:
                $result_span = array(
                    'class' => 'edit_view_show modal_dialog element_data element name lessening',
                    'data-type' => 'edit_view_edit',
                    'data-controller' => 'sdm',
                    'data-name' => $this->_vars['schema']['params']['name'],
                    'data-relate_copy_id' => (!empty($this->_vars['relate_extension_copy']->copy_id) ? $this->_vars['relate_extension_copy']->copy_id : null),
                );

                $this->_data['view']['span'] = array_merge($this->_data['view']['span'], $result_span);
                break;

            case self::DATA_TYPE_9:
                $result_button = array(
                    'class' => 'btn btn-default dropdown-toggle element',
                    'data-relate_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                );
                $this->_data['view']['button'] = array_merge($this->_data['view']['button'], $result_button);
                $this->_data['options']['attr']['data-relate_copy_id'] = $this->_vars['schema']['params']['relate_module_copy_id'];
                break;

        }

        return $this;
    }






    /**
     * setDefaultData - Параметры данных по умолчанию
     * @param $element_type
     * @return $this
     */
    private function setDefaultData($data_type){
        switch($data_type){
            case self::DATA_TYPE_1: // +
            case self::DATA_TYPE_2:
            case self::DATA_TYPE_3:
            case self::DATA_TYPE_4: // +
                $this->_active_widget_view = static::WIDGET_VIEW_BUTTON;
                $this->_data = array(
                    'view' => array(
                        'attr' => array(
                            'class' => 'dropdown submodule-link crm-dropdown element',
                            'data-type' => 'drop_down',
                        ),
                        'button' => array(
                            'name' => 'EditViewModel[' . $this->_vars['schema']['params']['name'] . ']',
                            'data-parent_copy_id' => $this->_vars['extension_copy']->copy_id,
                            'data-relate_copy_id' => null,
                            'data-id' => null,
                            'data-reloader' => EditViewRelateModel::RELOADER_STATUS_DEFAULT,
                            'data-module_parent' => 0,
                            'data-toggle' => 'dropdown',
                            'data-type' => 'drop_down_button',
                        ),
                        'button_actions' => array(
                            //array('name' => self::BUTTON_ACTION_ADD),
                            array('name' => self::BUTTON_ACTION_REMOVE),
                            array('name' => self::BUTTON_ACTION_EDIT),
                        ),
                        'html_view' => null,
                        'html_options' => null,
                    ),
                    'options' =>  array(
                        'attr' => array(
                            'data-relate_copy_id' => null,
                            'data-there_is_data' => '1',
                            'data-type' => 'drop_down_list',
                        ),
                        'search_display' => true,
                    ),
                    'relate_model' => null,
                    'vars' => $this->_vars,
                );
                break;

            case self::DATA_TYPE_5:
                $this->_active_widget_view = static::WIDGET_VIEW_SPAN;
                $this->_data = array(
                    'view' => array(
                        'span' => array(
                            'data-relate_copy_id' => null,
                            'data-id' => null,
                        ),
                        'span_params' => array(
                            'show_sdm_link' => $this->_vars['show_sdm_link'],
                        ),
                        'html_view' => null,
                        'html_options' => null,
                    ),
                );
                break;

            case self::DATA_TYPE_7: // +
                $this->_active_widget_view = static::WIDGET_VIEW_BUTTON;
                $this->_data = array(
                    'view' => array(
                        'attr' => array(
                            'class' => 'dropdown submodule-link crm-dropdown element',
                            'data-type' => 'drop_down',
                        ),
                        'button' => array(
                            'class' => 'btn btn-white dropdown-toggle element element_relate element_filter selectpicker',
                            'name' => 'EditViewModel[' . $this->_vars['schema']['params']['name'] . ']',
                            'data-name' => 'condition_value',
                            'data-parent_copy_id' => $this->_vars['extension_copy']->copy_id,
                            'data-relate_copy_id' => null,
                            'data-id' => null,
                            'data-toggle' => 'dropdown',
                            'data-type' => 'drop_down_button',
                        ),
                        'html_view' => null,
                        'html_options' => null,
                    ),
                    'options' =>  array(
                        'attr' => array(
                            'data-relate_copy_id' => null,
                            'data-there_is_data' => '1',
                            'data-type' => 'drop_down_list',
                        ),
                        'search_display' => true,
                    ),
                    'relate_model' => null,
                    'vars' => $this->_vars,
                );
                if(isset($this->_vars['attr'])){
                    $this->_data = \Helper::arrayMerge($this->_data, $this->_vars['attr']);
                }

                break;

            case self::DATA_TYPE_8:
                $this->_active_widget_view = static::WIDGET_VIEW_SPAN;
                $this->_data = array(
                    'view' => array(
                        'span' => array(
                            'data-relate_copy_id' => null,
                            'data-id' => null,
                        ),
                        'span_params' => array(
                            'show_sdm_link' => $this->_vars['show_sdm_link'],
                        ),
                        'html_view' => null,
                        'html_options' => null,
                    ),
                );
                break;

            case self::DATA_TYPE_9: // +
                $this->_active_widget_view = static::WIDGET_VIEW_BUTTON;
                $this->_data = array(
                    'view' => array(
                        'attr' => array(
                            'class' => 'dropdown submodule-link crm-dropdown element',
                            'data-type' => 'drop_down',
                        ),
                        'button' => array(
                            'name' => '',
                            'data-parent_copy_id' => $this->_vars['extension_copy']->copy_id,
                            'data-relate_copy_id' => null,
                            'data-id' => $this->_default_data_id,
                            'data-reloader' => EditViewRelateModel::RELOADER_STATUS_ACTIVITY_CHANNEL,
                            //'data-module_parent' => 0,
                            'data-toggle' => 'dropdown',
                            'data-type' => 'drop_down_button',
                        ),
                        'html_view' => null,
                        'html_options' => null,
                    ),
                    'options' =>  array(
                        'attr' => array(
                            'data-relate_copy_id' => null,
                            'data-there_is_data' => '1',
                            'data-type' => 'drop_down_list',
                        ),
                        'button_actions' => array(
                            //array('name' => self::BUTTON_ACTION_ADD),
                            //array('name' => self::BUTTON_ACTION_REMOVE),
                            array('name' => self::BUTTON_ACTION_ADD_CHANNEL),
                        ),
                        'search_display' => true,
                    ),
                    'relate_model' => null,
                    'vars' => $this->_vars,
                );
                break;

        }

        if(isset($this->_vars['schema']['params']['button_actions']) && $this->_vars['schema']['params']['button_actions'] === false){
            if(isset($this->_data['view']['button_actions'])){
                $this->_data['view']['button_actions'] = array();
            }
        }

        return $this;
    }












    //***************************************************************
    //      ENTITY
    //***************************************************************


    /**
     * setEntityModule - установка EntityModel параметров по умолчанию
     */
    private function setEntity(EditViewRelateModel $relate_model){
        $vars = array(
            'copy_id' => $relate_model->getRelateExtensionCopy()->copy_id,
            'id' => $relate_model->getId(),
            'this_template' => $relate_model->getVars()['this_template'],
        );

        $this->_entity_model = (new EntityModel(true, true))
                                        ->setVars((new \EntityVarsModel())->prepareModuleVars($vars)->getVars());

        return $this;
    }


    private function setEntityForVars($status){
        $this->_entity_model->setEntityForVars($status);
        return $this;
    }


    /**
     * setEntityVarsType1 - установка доп параметров для EntityModel
     */
    private function setEntityVarsType1(EditViewRelateModel $relate_model){
        $this->_entity_model
            ->setElementType(\EntityElementTypeModel::TYPE_SDM)
            ->addVars([
                'reloader' => $relate_model->getReloaderStatus(),
                'module_parent' => ($relate_model->isModuleParent() ? 1 : 0),
            ]);

        return $this;
    }



    /**
     * setEntityVarsType8 - установка доп параметров для EntityModel
     */
    private function setEntityVarsType8(EditViewRelateModel $relate_model){
        $this->_entity_model
            ->setElementType(\EntityElementTypeModel::TYPE_SDM)
            ->addVars([
                'reloader' => $relate_model->getReloaderStatus(),
                'module_parent' => ($relate_model->isModuleParent() ? 1 : 0),
                'field_name' => $this->_vars['schema']['params']['name'],
            ]);

        return $this;
    }




    /**
     * setEntityEventsType1 - установка евентов для EntityModel
     */
    protected function setEntityEventsType1(){
        $this->_entity_model
            ->addEvent('.element[data-type="drop_down_button"]', EntityEventsModel::EVENT_READY, ['DropDownListObj', 'actions', 'init'])
            ->addEvent('.element[data-type="actions"] .add', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionAdd'], ['event_id' => EntityEventsModel::EID_EDIT_VIEW_SDM_ADD])
            ->addEvent('.element[data-type="actions"] .remove', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionRemove'], ['event_id' => EntityEventsModel::EID_EDIT_VIEW_SDM_REMOVE])
            ->addEvent('.element[data-type="actions"] .edit', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionEdit'], ['event_id' => EntityEventsModel::EID_EDIT_VIEW_SDM_EDIT])
            ->addEvent('.element[data-type="drop_down_list"] .submodule-table tr', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'selectItem'], ['event_id' => EntityEventsModel::EID_EDIT_VIEW_SDM_SELECT_ITEM]);

        return $this;
    }



    /**
     * setEntityEventsType - установка евентов для EntityModel
     */
    protected function setEntityEventsType8(){
        $this->_entity_model
            ->addEvent('.element[data-type="drop_down_button"]', EntityEventsModel::EVENT_READY, ['DropDownListObj', 'actions', 'init'])
            ->addEvent('.element[data-type="actions"] .add', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionAdd'], ['event_id' => EntityEventsModel::EID_LIST_VIEW_SDM_ADD])
            ->addEvent('.element[data-type="actions"] .remove', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionRemove'], ['event_id' => EntityEventsModel::EID_LIST_VIEW_SDM_REMOVE])
            ->addEvent('.element[data-type="drop_down_list"] .submodule-table tr', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'selectItem'], ['event_id' => EntityEventsModel::EID_LIST_VIEW_SDM_SELECT_ITEM]);

        return $this;
    }







    /**
     * setEntityEventsType8After - установка евентов для EntityModel
     */
    protected function setEntityEventsType8After(){
        $this->_entity_model
            ->addEvent('', EntityEventsModel::EVENT_CLICK, ['DropDownListObj', 'actions', 'actionEdit'], ['event_id' => EntityEventsModel::EID_LIST_VIEW_SDM_EDIT]);

        return $this;
    }






    /**
     * resetEntityToProperties
     */
    private function resetEntityToProperties(){
        if($this->_entity_model){
            $this->_entity_model->resetToEntityProperties();
        }

        return $this;
    }





}

