<?php

/**
 * Class DropDownListOptionsModel - работа из выпадающим списком для элемента DropDownList (relate, relate_this)
 *
 * @author Alex R.
 */


class DropDownListOptionsModel{

    const GROUP_DATA_SDM_OPTION_LIST        = 'gd_sdm_option_list';
    const GROUP_DATA_SM_OPTION_LIST         = 'gd_sm_option_list';
    const GROUP_DATA_ACTIVITY_OPTION_LIST   = 'gd_activity_selected_list';

    private static $_search;
    private static $_limit = 20;
    private static $_offset = 0;
    private static $_filters = false;

    private $_status = true;
    private $_html_options;
    private $_html_option;
    private $_there_is_data = true;

    private $_vars; // пернеменные для EditViewRelateModel
    private $_prepare_data_list = false;

    private $_extension_copy;
    private $_relate_module_extension_copy;
    private $_option_data_model_list = array();


    private $_active_group_data;    // активная группа данных. От параметра зависит выходной результат




    public static function getInstance(){
        return new self();
    }


    public function setPrepareDataList($prepare_data_list){
        $this->_prepare_data_list = $prepare_data_list;
        return $this;
    }


    public function setThereIsData($there_is_data){
        $this->_there_is_data = $there_is_data;
        return $this;
    }

    public function getStatus(){
        return $this->_status;
    }


    public function setActiveGroupData($active_group_data){
        $this->_active_group_data = $active_group_data;
        return $this;
    }


    public function getResult(){
        if($this->_status == false){
            $this->_there_is_data = true;
        }

        $result = array(
            'status' => $this->_status,
            'html_options' => $this->_html_options,
            'html_option' => $this->_html_option,
            'there_is_data' => $this->_there_is_data,
        );

        return $result;
    }



    /**
     * setAllParams - установка всех параметров
     * список параметров: search, limit, offset
     * @param array $params
     */
    public function setAllParams(array $params){
        if(empty($params)) return $this;

        if(array_key_exists('vars', $params))       $this->setVars($params['vars']);
        if(array_key_exists('search', $params))     self::setSearch($params['search']);
        if(array_key_exists('limit', $params))      self::setLimit($params['limit']);
        if(array_key_exists('offset', $params))     self::setOffset($params['offset']);
        if(array_key_exists('active_group_data', $params)) $this->setActiveGroupData($params['active_group_data']);
        if(array_key_exists('filters', $params))    self::setFilters($params['filters']);

        return $this;
    }

    public function getAllParams(){
        return array(
            self::getSearch(),
            self::getLimit(),
            self::getOffset(),
        );
    }

    public static function setDefaultValues(){
        self::setSearch(null);
        self::setLimit(20);
        self::setOffset(0);
    }


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    public static function setSearch($search){
        self::$_search = $search;
    }


    public static function getSearch(){
        return self::$_search;
    }


    public static function setLimit($limit){
        self::$_limit = $limit;
    }


    public static function getLimit(){
        return self::$_limit;
    }



    public static function setOffset($offset)
    {
        self::$_offset = $offset;
    }


    public static function getOffset(){
        return self::$_offset;
    }

    
    public static function setFilters($filters)
    {
        if(!empty($filters))
            self::$_filters = $filters;
    }
    
    public function setExtensionCopy(){
        if(isset($this->_vars['copy_id'])){
            $this->_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['copy_id']);;
        }

        return $this;
    }


    public function setRelateModuleExtensionCopy(){
        if(isset($this->_vars['relate_module_copy_id'])){
            $this->_relate_module_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['relate_module_copy_id']);;
        }

        return $this;
    }


    public function setOptionDataModelList($option_data_model_list){
        $this->_option_data_model_list = $option_data_model_list;
        return $this;
    }



    public function getOptionDataModelList(){
        return $this->_option_data_model_list;
    }



    public function initEntities(){
        switch($this->_active_group_data){
            case static::GROUP_DATA_SDM_OPTION_LIST:
            case static::GROUP_DATA_ACTIVITY_OPTION_LIST:
                $this->setExtensionCopy();
                $this->setRelateModuleExtensionCopy();
                break;
            case static::GROUP_DATA_SM_OPTION_LIST:
                $this->setExtensionCopy();
                break;
        }

        return $this;
    }


    private function getOptionsView($params){
        $params['view'] = DropDownListModel::WIDGET_OPTIONS;
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params, true);
    }


    private function getOptionsPanelView($params){
        $params['view'] = DropDownListModel::WIDGET_OPTIONS_PANEL;
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params, true);
    }



    private function getOptionView($params){
        $params['view'] = DropDownListModel::WIDGET_OPTION;
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params, true);
    }




    private function getVars(){
        $result = $this->_vars;
        $result['extension_copy'] = $this->_extension_copy;
        $result['parent_copy_id'] = null;

        $alias = 'evm_' . $this->_extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $this->_extension_copy->getTableName(null, false),
        );

        $result['extension_data'] = EditViewModel::modelR($alias, $dinamic_params, true);

        return $result;
    }


    /**
     * prepareOptionsHtmlList - собирает всю верстку выпадающего списка
     *
     * @return $this
     */
    public function prepareOptionsHtmlList($prepare_html_list = false){
        if($prepare_html_list){
            $this->prepareHtmlList();
        }
        switch($this->_active_group_data){
            case static::GROUP_DATA_SDM_OPTION_LIST:
            case static::GROUP_DATA_ACTIVITY_OPTION_LIST:
                $html = $this->getOptionsView(
                    array(
                        'vars' => array(
                            'html_option' => $this->_html_option,
                            'vars' => $this->_vars['options'],
                        )));
                break;
            case static::GROUP_DATA_SM_OPTION_LIST:
                $html = $this->getOptionsPanelView(
                    array(
                        'vars' => array(
                            'html_option' => $this->_html_option,
                            'vars' => $this->_vars['options'],
                        )));
                break;
        }


        $this->_html_options = $html;

        return $this;
    }



    /**
     * prepareHtmlList - собирает верстку выпадающего списка
     */
    public function prepareHtmlList(){
        switch($this->_active_group_data){
            case static::GROUP_DATA_SDM_OPTION_LIST:
                $this->prepareHtmlSdmOptionList();
                break;
            case static::GROUP_DATA_SM_OPTION_LIST:
                $this->prepareHtmlSmOptionList();
                break;
            case static::GROUP_DATA_ACTIVITY_OPTION_LIST:
                $this->prepareHtmlActivityOptionList();
                break;

        }

        return $this;
    }




    /**
     * prepareHtml Sdm OptionList
     */
    private function prepareHtmlSdmOptionList(){
        $this->initEntities();

        if(empty($this->_relate_module_extension_copy)){
            $this->_status = false;
            return $this;
        }


        $field_params = $this->_extension_copy->getFieldSchemaParams($this->_vars['field_name']);
        $field_params = $field_params['params'];

        if(empty($field_params['relate_module_copy_id']) && !empty($this->_relate_module_extension_copy)) $field_params['relate_module_copy_id'] = $this->_relate_module_extension_copy->copy_id;
        if(empty($field_params['relate_field']) && !empty($this->_relate_module_extension_copy)) $field_params['relate_field'] = array('module_title');

        // prepare data
        if($this->_prepare_data_list){
            $this->prepareHtmlSdmOptionDataList();
        }

        if(empty($this->_option_data_model_list)) return $this;

        $relate_pk_name = $this->_relate_module_extension_copy->prefix_name . '_id';
        $html = null;

        foreach($this->_option_data_model_list as $value){
            $option_value = DataValueModel::getInstance()
                ->setFileLink(false)
                ->getRelateValuesToHtml($value, $field_params);

            $html.= $this->getOptionView(
                array(
                    'vars' => array(
                        'id' => $value[$relate_pk_name],
                        'value' => $option_value
                    )));
        }

        $this->_html_option = $html;

        return $this;
    }




    /**
     * prepareHtml Sm OptionList - возвращает список для поп-апа при привязке новых значений в СМ
     */
    private function prepareHtmlSmOptionList(){
        $this->initEntities();

        // prepare data
        if($this->_prepare_data_list){
            $this->prepareHtmlSmOptionDataList();
        }

        if(empty($this->_option_data_model_list)) return $this;

        $html = null;

        foreach($this->_option_data_model_list as $value){
            $id = $value[$this->_extension_copy->prefix_name . '_id'];
            $option_value = null;

            if($this->_extension_copy->extension->name == 'Permission'){
                $schema_field = $this->_extension_copy->getFieldSchemaParams('access_id');
                if(empty($schema_field)){
                    $id = null;
                    $option_value = Yii::t('messages', 'None data');
                } else {
                    $option_value = $this->getElementTData(
                        array(
                            'extension_copy' => $this->_extension_copy,
                            'params' => $schema_field['params'],
                            'value_data' => $value,
                            'file_link' => false,
                        ));
                }

                $schema_field = $this->_extension_copy->getFieldSchemaParams('permission_code');
                if(!empty($schema_field)){
                    $option_value = $option_value . ' => ' . $this->getElementTData(
                            array(
                                'extension_copy' => $this->_extension_copy,
                                'params' => $schema_field['params'],
                                'value_data' => $value,
                                'file_link' => false,
                            ));
                }
            } else {
                $schema_field = $this->_extension_copy->getFirstFieldParamsForRelate();
                if($schema_field === null){
                    $id = null;
                    $option_value = Yii::t('messages', 'None data');
                } else {
                    $option_value = $this->getElementTData(
                        array(
                            'extension_copy' => $this->_extension_copy,
                            'params' => $schema_field['params'],
                            'value_data' => $value,
                            'file_link' => false,
                            'relate_add_avatar' => true,
                        ));
                }
            }

            // html
            $html.= $this->getOptionView(
                array(
                    'view_checkbox' => true,
                    'vars' => array(
                        'id' => $id,
                        'value' => $option_value
                    )));
        }

        $this->_html_option = $html;

        return $this;
    }







    /**
     * prepareHtml Block "Activity" OptionList - возвращает список для кнопки Channel блока активность
     */
    private function prepareHtmlActivityOptionList(){
        $this->initEntities();

        if(empty($this->_relate_module_extension_copy)){
            $this->_status = false;
            return $this;
        }

        $field_schema = $this->_relate_module_extension_copy->getFirstFieldParamsForRelate();
        if($field_schema == false){
            $this->_status = false;
            return $this;
        }

        $field_params = $field_schema['params'];
        $field_params['relate_field'] = $field_params['name'];
        $field_params['relate_module_copy_id'] = $this->_relate_module_extension_copy->copy_id;

        // prepare data
        if($this->_prepare_data_list){
            $this->prepareHtmlSmSelectedDataList();
        }

        if(empty($this->_option_data_model_list)) return $this;

        $relate_pk_name = $this->_relate_module_extension_copy->prefix_name . '_id';
        $html = null;

        foreach($this->_option_data_model_list as $value){
            $option_value = DataValueModel::getInstance()
                ->setFileLink(false)
                ->getRelateValuesToHtml($value, $field_params, false);

            $html.= $this->getOptionView(
                array(
                    'vars' => array(
                        'id' => $value[$relate_pk_name],
                        'value' => $option_value
                    )));
        }

        $this->_html_option = $html;

        return $this;
    }





    /**
     * prepareHtmlSdmOptionDataList - подготовка данных для prepareHtmlSdmOptionList()
     * @return $this
     */
    private function prepareHtmlSdmOptionDataList(){
        $field_params = $this->_extension_copy->getFieldSchemaParams($this->_vars['field_name']);
        $field_params = $field_params['params'];

        if(empty($field_params['relate_module_copy_id']) && !empty($this->_relate_module_extension_copy)) $field_params['relate_module_copy_id'] = $this->_relate_module_extension_copy->copy_id;
        if(empty($field_params['relate_field']) && !empty($this->_relate_module_extension_copy)) $field_params['relate_field'] = array('module_title');

        $vars = $this->getVars();
        $vars['params'] = $field_params;
        $vars['schema']['params'] = $field_params;

        $relate_model = EditViewRelateModel::getInstance()
                                ->setVars($vars)
                                ->prepareVars();

        $this->_option_data_model_list = $relate_model->getOptionsDataList();

        $this->setThereIsData($relate_model->getIsSetNextOptionListData());

        return $this;
    }



    /**
     * prepareHtmlSmOptionDataList - подготовка данных списка для prepareHtmlSmOptionList()
     * @return $this
     */
    public function prepareHtmlSmOptionDataList(){
        $sub_module_model = (new EditViewSubModuleModel())
                                ->setVars($this->_vars)
                                ->setFilters(self::$_filters)
                                ->setExtensionCopy($this->_extension_copy)
                                ->prepareVars();

        $this->_option_data_model_list = $sub_module_model->getOptionsDataList();

        $this->setThereIsData($sub_module_model->getIsSetNextOptionListData());

        return $this;
    }






    /**
     * prepareHtmlSmSelectedDataList - подготовка данных списка для prepareHtmlActivityOptionList()
     * @return $this
     */
    public function prepareHtmlSmSelectedDataList(){
        $vars = array(
            'parent_copy_id' => $this->_extension_copy->copy_id,
            'parent_data_id' => $this->_vars['data_id'],
        );

        $sub_module_model = (new EditViewSubModuleModel())
                                ->setVars($vars)
                                ->setFilters(self::$_filters)
                                ->setExtensionCopy($this->_relate_module_extension_copy)
                                ->prepareVars();

        $this->_option_data_model_list = $sub_module_model->getSelectedDataList();

        $this->setThereIsData($sub_module_model->getIsSetNextOptionListData());

        return $this;
    }




    /**
     * prepareHtmlActivityOptionDataList - подготовка данных списка для prepareHtmlSmOptionList()
     * @return $this
     */
    public function prepareHtmlActivityOptionDataList(){
        $sub_module_model = (new EditViewSubModuleModel())
                                    ->setVars($this->_vars)
                                    ->setFilters(self::$_filters)
                                    ->setExtensionCopy($this->_extension_copy)
                                    ->prepareVars();

        $this->_option_data_model_list = $sub_module_model->getSelectedDataList();

        $this->setThereIsData($sub_module_model->getIsSetNextOptionListData());

        return $this;
    }







    /**
     * getElementTData
     */
    private function getElementTData($params){
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'), $params, true);
    }



}

