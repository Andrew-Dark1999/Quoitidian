<?php
/**
* @author Alex R.
*/


class EditViewRelateModel {



    const RELOADER_STATUS_DEFAULT           = '';
    const RELOADER_STATUS_PARENT            = 'parent';
    const RELOADER_STATUS_CHILDREN          = 'children';
    const RELOADER_STATUS_ONE_TO_ONE        = 'one_to_one';
    const RELOADER_STATUS_ACTIVITY_CHANNEL  = 'activity_channel';

    private $_vars = array();
    private $_relate_disabled = '';
    private $_id = null;

    private $_data_list_limit;
    private $_data_list_offset;

    private static $_reloader_default = null;
    private $_reloader_status = self::RELOADER_STATUS_DEFAULT;

    private $_pci = null;
    public static $_pdi = null;

    public $relate_extension_copy = array();

    private $_is_set_next_option_list_data = true;



    public static function getInstance(){
        return new self();
    }


    public function setVars($vars){
        $this->_vars = $vars;

        return $this;
    }


    public function prepareVars(){
        $this
            ->setRelateExtensionCopy()
            ->setPci()
            ->setPdi()
            ->setId();

        $this->_data_list_limit = \DropDownListOptionsModel::getLimit();
        $this->_data_list_offset = \DropDownListOptionsModel::getOffset();

        return $this;
    }




    public function setRelateExtensionCopy($relate_copy_id = null){
        if($relate_copy_id === null){
            if(empty($this->_vars['schema']['params']['relate_module_copy_id'])) return $this;
            $this->relate_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
        } else {
            $this->relate_extension_copy = $relate_copy_id;
        }

        if($this->relate_extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS){
            if(!empty($this->_vars['extension_data'])){
                $attributes = $this->_vars['extension_data']->getAttributes();
                if($attributes && in_array('is_bpm_operation', $attributes) && $this->_vars['extension_data']->getAttribute('is_bpm_operation') === '0'){
                    $this->_relate_disabled = 'disabled="disabled"';
                }
            }
        }

        return $this;
    }



    public function getRelateExtensionCopy(){
        return $this->relate_extension_copy;
    }


    public function getRelateDisabledAttr(){
        return $this->_relate_disabled;
    }



    /**
     * getIsSetNextOptionListData - возвращает статус еще существования данных списка
     * вызывать после отработки метода getOptionsDataList()
     */
    public function getIsSetNextOptionListData(){
        return $this->_is_set_next_option_list_data;
    }


    public static function setReloaderDefault($reloader_default){
        self::$_reloader_default = $reloader_default;
    }


    public function getReloaderStatus(){
        if(self::$_reloader_default !== null){
            return self::$_reloader_default;
        }
        return $this->_reloader_status;
    }


    private function setReloaderStatus($reloader_status = null){
        if($reloader_status !== null && $this->_pci != $this->_vars['schema']['params']['relate_module_copy_id']){

            // поиск обратной связи ОКО
            $relate_table = ModuleTablesModel::model()->findAll(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                                                                    ':relate_copy_id' => $this->_vars['extension_copy']->copy_id,
                                                    )));
            if(!empty($relate_table)){
                $this->_reloader_status = self::RELOADER_STATUS_ONE_TO_ONE;
            }

            return;
        }

        if($this->_pci == $this->_vars['schema']['params']['relate_module_copy_id'])
            $this->_reloader_status = self::RELOADER_STATUS_PARENT;
        elseif($this->_pci != $this->_vars['schema']['params']['relate_module_copy_id']){
            if($this->pciIsParentToModule()){
                $this->_reloader_status = self::RELOADER_STATUS_CHILDREN;
            }
        }
    }



    private function pciIsParentToModule(){
        $schema_params_list = $this->relate_extension_copy->getFieldSchemaParamsByType(\Fields::MFT_RELATE, null, false);

        if($schema_params_list == false){
            return false;
        }

        foreach($schema_params_list as $schema_params){
            if($schema_params['params']['relate_module_copy_id'] == $this->_pci){
                return true;
            }
        }

        return false;
    }




    /**
     * возвращает ID
     */
    public function getId(){
        return $this->_id;
    }



    /**
     * возвращает PCI
     */
    public function getPci(){
        return $this->_pci;
    }



    /**
     * устанавливает copy_id связаного модуля по один-ко-многим  в аргумент self::pci
     */
    public function setPci(){
        if(!empty($this->_vars['primary_entities']['primary_pci'])){
            $this->_pci = $this->_vars['primary_entities']['primary_pci'];
            $this->setReloaderStatus();
            return $this;
        }
        $this->setAutoPci();
        $this->setReloaderStatus();

        return $this;
    }



    /**
     * устанавливает copy_id связаного модуля по один-ко-многим  в аргумент self::pci
     */
    public function setPdi(){
        if(!empty($this->_vars['primary_entities']['primary_pdi'])){
            self::$_pdi = $this->_vars['primary_entities']['primary_pdi'];
        }
        return $this;
    }



    public function getVars(){
        return $this->_vars;
    }


    /**
     * поиск и установка copy_id первичного модуля по схеме ОКМ в аргумент self::pci
     */
    public function setAutoPci(){
        if(!empty($this->_pci)) return $this;

        // 1. Установка первичного поля по связи ОКМ меджу элементами СДМ и СМ
        // первое поле типа relate в модуле
        $first_relate_field_params = $this->_vars['extension_copy']->getFieldSchemaParamsByType('relate');
        if(empty($first_relate_field_params)){
            return $this;
        }
        // поиск названия связующей таблицы
        $relate_table = ModuleTablesModel::model()->findAll(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                'params' => array(
                                                                ':copy_id' => $first_relate_field_params['params']['relate_module_copy_id'],
                                                                ':relate_copy_id' => $this->_vars['extension_copy']->copy_id,
                                                )));
        if(!empty($relate_table)){
            $this->_pci = $first_relate_field_params['params']['relate_module_copy_id'];
            return $this;
        }



        // 2. Установка первичного поля по связи ОКМ по полю "Название"
        // ищем первичное поле в связаном модуле по полю "Название" (если такая связь есть)
        // поиск названия связующей таблицы
        $relate_tables = ModuleTablesModel::model()->findAll(array(
                                                'condition' => 'relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                'params' => array(
                                                                ':relate_copy_id' => $this->_vars['extension_copy']->copy_id,
                                                )));

        if(empty($relate_tables)) return $this;
        foreach($relate_tables as $relate_table){
            $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate_table->copy_id);
            $first_field_params = $relate_extension_copy->getPrimaryField();
            if(empty($first_field_params) || $first_field_params['params']['relate_module_copy_id'] != $this->_vars['extension_copy']->copy_id) continue;

            $first_relate_field_params = $relate_extension_copy->getFieldSchemaParamsByType('relate');
            if(empty($first_relate_field_params)) continue;
            // поиск названия связующей таблицы
            $relate_table = ModuleTablesModel::model()->findAll(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $first_relate_field_params['params']['relate_module_copy_id'],
                                                                    ':relate_copy_id' => $relate_extension_copy->copy_id,
                                                    )));
            if(!empty($relate_table)){
                $this->_pci = $first_relate_field_params['params']['relate_module_copy_id'];
                return $this;
            }
        }


        return $this;
    }






    /**
     * getOptionsIdListFromDB
     */
    private function getOptionsIdListFromDB(){
        $result = array(
            'get_option_list' => true,
            'options_id_list' => array(),
        );

        // поиск названия связующей таблицы
        $relate_table = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                'params' => array(
                                                                ':copy_id' => $this->_pci,
                                                                ':relate_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'])
                                                ));

        if(empty($relate_table)){
            return $result;
        }

        if((!empty($this->_vars['extension_data'])) && $this->_vars['extension_data']->isNewRecord && empty(self::$_pdi)){
            if($this->_vars['schema']['params']['relate_module_copy_id'] != $this->_pci)
                $result['get_option_list'] = false;
            return $result;
        }

        if(empty(self::$_pdi)){
            $result['get_option_list'] = false;
            return $result;
        }

        // поиск в связующей таблице списка ИД записей
        $data_list = DataModel::getInstance()
                            ->setFrom('{{' . $relate_table->table_name . '}}')
                            ->setWhere(array('AND', $relate_table->parent_field_name . '=' . self::$_pdi))
                            ->findAll();

        if(empty($data_list)){
            $result['get_option_list'] = false;
            return $result;
        }

        // передаем ИД как массив
        $id = array_unique(array_keys(CHtml::listData($data_list, $relate_table->relate_field_name, '')));


        // поиск названия связующей таблицы c родительским модулем
        $relate_table_b_parent = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                'params' => array(
                                                                ':copy_id' => $this->_vars['extension_copy']->copy_id,
                                                                ':relate_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'])
                                                ));
        // поиск названия связующей таблицы c родительским модулем
        $relate_table_c_parent = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                'params' => array(
                                                                ':copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                                                                ':relate_copy_id' => $this->_vars['extension_copy']->copy_id)
                                                ));

        // исключаем данные, если они уже привязаны к другой карточке
        if(!empty($relate_table_b_parent) && !empty($relate_table_c_parent)){
            $extension_copy_fp = ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id'])->getPrimaryField();

            $bonus_where = '';
            if(!empty($this->_id)){
                $bonus_where = ' AND ' . $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id != '. $this->_id;
            }

            if(!empty($extension_copy_fp) && $extension_copy_fp['params']['type'] == 'relate_string' && $extension_copy_fp['params']['relate_module_copy_id'] == $this->_vars['extension_copy']->copy_id){
                $result['options_id_list'] = array('in', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id', $id);
            } else {
                $not_exists = ' not exists (SELECT * FROM {{'.$relate_table_b_parent->table_name.'}}' .
                                                             ' WHERE {{'.$relate_table_b_parent->table_name.'}}.'.$relate_table_b_parent->relate_field_name.
                                                             ' = '.
                                                             $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id ' . $bonus_where . ')';
                $result['options_id_list'] = array('AND', $not_exists, array('in', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id', $id));
            }
        } else {
            $result['options_id_list'] = array('in', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id', $id);
        }


        //$result['options_id_list'] = array('in', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id', $id);

        return $result;
    }


    public function getOptionsDataParamsDefault(){
        return array(
            'get_option_list' => true,
            'options_id_list' => array(),
        );
    }


    /**
     * поиск доп. параметров для отбора данных для options.
     * в 'options_id_list' - возвращается список ИД полей, если модуль имеет связь с другим модулем один-ко-многим
     */
    private function getOptionsDataParamsRelateThree(){
        $result = $this->getOptionsDataParamsDefault();
        if($this->_vars['extension_copy']->getModule(false)->isTemplate($this->_vars['extension_copy']) && $this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            return $result;
        }

        if($this->_pci){
            // проверка на существование связи многие-к-одному, то  есть модуль есть вторичний к перчивному...
            $relate_table = ModuleTablesModel::model()->count(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'],
                                                                    ':relate_copy_id' => $this->_pci,
                                                    )));
            if($relate_table){
                $result = $this->getOptionsIdListFromDB();
            }
        }
        return $result;
    }



    /**
     * созвращает тип связи по приоритету для условий отбора данных
     */
    private function getModuleTablesType($copy_id, $relate_copy_id){
        $result = array(
            'status' => false,
            'model' => null,
        );


        $module_tables = ModuleTablesModel::model()->findAll(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id' ,
                                                'params' => array(
                                                                ':copy_id' => $copy_id,
                                                                ':relate_copy_id' => $relate_copy_id)

                                                ));
        if(!empty($module_tables))
        foreach($module_tables as $table){
            if($table['type'] == 'relate_module_one'){

                $primary_field = ExtensionCopyModel::model()->findByPk($copy_id)->getPrimaryField(); // если связь на поле "Название " (relate_string)
                if(!empty($primary_field) && $primary_field['params']['type'] == 'relate_string'){
                    $result['status'] = 2;
                } else {
                    $result = array(
                        'status' => 1,
                        'model' => $table,
                    );
                }
                return $result;
            }
            if($table['type'] == 'relate_module_many') $result['status'] = 2;
        }

        return $result;
    }


    private function getOptionsDataParamsRelateTwo($option_data_params){
        $result = $option_data_params;
        $this->setReloaderStatus(self::RELOADER_STATUS_DEFAULT);

        $module_type = $this->getModuleTablesType($this->_vars['schema']['params']['relate_module_copy_id'], $this->_vars['extension_copy']->copy_id);
        switch($module_type['status']){
            case false:
                return $result;
            case 1:
                if(!empty($this->_id))
                    $result['options_id_list_two'] = 'not exists (SELECT * FROM {{'.$module_type['model']->table_name.'}}'.
                                                     ' WHERE {{' . $module_type['model']->table_name.'}}.'.$module_type['model']->parent_field_name.
                                                     ' = '.
                                                     ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id'])->getTableName() .'.'.$module_type['model']->parent_field_name .
                                                     ' AND {{' . $module_type['model']->table_name.'}}.'.$module_type['model']->parent_field_name . ' != ' . $this->_id . ')';
                else
                    $result['options_id_list_two'] = 'not exists (SELECT * FROM {{'.$module_type['model']->table_name.'}}' .
                                                     ' WHERE {{'.$module_type['model']->table_name.'}}.'.$module_type['model']->parent_field_name.
                                                     ' = '.
                                                     ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id'])->getTableName() .'.'.
                                                     $module_type['model']->parent_field_name.')';
                break;
            case 2: // связь по полю "название"
                return $result;
                break;
        }

        return $result;
    }



    public function getOptionDataParams(){
        $option_data_params = $this->getOptionsDataParamsRelateThree();

        // если модуль не связан с другим с подченением
        if($option_data_params['get_option_list'] == true && empty($option_data_params['options_id_list'])){
            $option_data_params = $this->getOptionsDataParamsRelateTwo($option_data_params);
        }
        return $option_data_params;
    }





    /**
     * checkRevertToCM - проверка на подгрузку данных из связи через СМ
     */
    public function checkRevertToCM(){
        if(empty($this->_vars['schema']['params']['type'])) return true;
        if($this->_vars['schema']['params']['type'] != 'relate_dinamic') return false;
        // проверка на существование связи через СДМ
        $module_tables_model = ModuleTablesModel::model()->find(
            array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                'params' => array(':copy_id' => $this->_vars['extension_copy']->copy_id, ':relate_copy_id' => $this->_vars['schema']['params']['relate_module_copy_id'])
            )
        );

        if(empty($module_tables_model)){
            return true;
        }

        return false;
    }

    /**
     * Возвращает статус, что поле для отображения есть типа Участник или ответственный
     *
     * @return bool
     */
    private function viewFieldIsPartisipant()
    {
        $fieldNames = $this->_vars['schema']['params']['relate_field'];
        if(!$fieldNames){
            return false;
        }

        foreach ((array)$fieldNames as $fieldName) {
            $schemaParams = $this->relate_extension_copy->getFieldSchemaParams($fieldName);

            if (!$schemaParams) {
                continue;
            }

            return $schemaParams['params']['type'] == Fields::MFT_RELATE_PARTICIPANT;
        }

        return false;
    }


    /**
     * getOptionsDataList - возвращает список сущностей, что используются для выбора нового значения из списка
     */
    public function getOptionsDataList($option_data_params = null){
        $result = array();

        if(!empty(EditViewBuilder::$relate_module_copy_id_exception) && in_array($this->_vars['schema']['params']['relate_module_copy_id'], EditViewBuilder::$relate_module_copy_id_exception)){
            return $result;
        }

        if($option_data_params === null){
            $option_data_params = $this->getOptionDataParams();
        }


        if($option_data_params['get_option_list'] !== false){
            $search = new Search();
            $search->setTextFromUrl();

            $data_model = new DataModel();
            $data_model
                ->setIsSetSearch(($search::$text === null ? false : true))
                ->setSearchWsSdm(true)
                ->setExtensionCopy($this->relate_extension_copy)
                ->setFromModuleTables()
                ->setConcatGroupFieldSDM($this->_vars['extension_copy'], $this->checkRevertToCM())
                ->setFromFieldTypes();

            if($this->relate_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS){
                $data_model->andWhere(array('AND', '(is_bpm_operation is NULL OR is_bpm_operation = "0")'));
            }

            $viewFieldIsPartisipant = $this->viewFieldIsPartisipant();

            //responsible
            if($viewFieldIsPartisipant) {
                if ($this->relate_extension_copy->isResponsible()) {
                    $data_model->setFromResponsible();
                }
                //participant
                if ($this->relate_extension_copy->isParticipant()) {
                    $data_model->setFromParticipant();
                }
            }

            $data_model
                    ->setGroup()
                    ->setCollectingSelect();

            if(!empty($option_data_params['options_id_list'])){
                $data_model
                    ->andWhere(array('AND', $option_data_params['options_id_list']));
                    //->andWhere(array('in', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id', $option_data_params['options_id_list']));
            }

            if(!empty($option_data_params['options_id_list_two'])){
                $data_model
                    ->clearWhere()
                    ->andWhere(array('AND', $option_data_params['options_id_list_two']));
            }

            if($this->_vars['extension_copy']->getModule(false)->isTemplate($this->_vars['extension_copy']) && $this->relate_extension_copy->getModule(false)->isTemplate($this->relate_extension_copy)){
                if($this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                    $data_model->andWhere(array('AND', $this->relate_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE.'"'));
                } elseif($this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_MODULE){
                    $data_model->andWhere(array('AND', $this->relate_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->relate_extension_copy->getTableName() . '.this_template is null'));
                }
            } else {
                $data_model->andWhere(array('AND', $this->relate_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->relate_extension_copy->getTableName() . '.this_template is null'));
            }

            $alias = '';

            if($viewFieldIsPartisipant) {
                if ($this->relate_extension_copy->dataIfParticipant() && ($this->relate_extension_copy->isParticipant() || $this->relate_extension_copy->isResponsible())) {
                    $data_model->setOtherPartisipantAllowed($this->relate_extension_copy->copy_id);
                    $alias = 'data';
                }
            }

            //search
            if($search::$text !== null){
                $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
                $alias = 'data';
            }

            $b = false;
            if($this->_data_list_limit || $this->_data_list_offset){
                $data_model
                    ->addSqlCallFoundRows()
                    ->setLimit($this->_data_list_limit)
                    ->setOffSet($this->_data_list_offset);

                $b = true;
                $alias = 'data';
            }


            if(isset($option_data_params['only_id']) && $option_data_params['only_id']){
                $data_model->setSelectNew();
                $data_model->setWhere($this->relate_extension_copy->getPkFieldName() . ' = ' . $this->_id);
                $alias = 'data';
            }

            $data_model->withOutRelateTitleTemplate(null, $alias);
            
            $result = $data_model->findAll();

            if($b){
                $this->getAllFoundRows();
            }
        }

        return $result;
    }


    /**
     * getAllFoundRows - возвращает статус еще существования данных списка
     * @return $this
     */
    private function getAllFoundRows(){
        $rows = (integer)Yii::app()->db->createCommand('SELECT FOUND_ROWS()')->queryScalar();

        $find_rows = (integer)\DropDownListOptionsModel::getLimit() + (integer)\DropDownListOptionsModel::getOffset();

        if($find_rows >= $rows){
            $this->_is_set_next_option_list_data = false;
        } else {
            $this->_is_set_next_option_list_data = true;
        }

        return $this;
    }







    /**
     * вычисление и установка ID записи поля в БД
     */
    public function setId($id = null){
        if($id !== null){
            $this->_id = $id;
            return $this;
        }

        //если данные пришли постом для сохранении и возникли ошибки при валидации
        if(!empty($this->_vars['relate'])){
            foreach($this->_vars['relate'] as $value){
                if($value['name'] == 'EditViewModel[' . $this->_vars['schema']['params']['name'] . ']' && $value['relate_copy_id'] == $this->_vars['schema']['params']['relate_module_copy_id']){
                    $this->_id = $value['id'];
                    break;
                }
            }
        }

        // при редактировании из inline-edit
        if(!$this->_id){
            $this->_id = (isset($this->_vars['schema']['params']['relate_data_id']) ? $this->_vars['schema']['params']['relate_data_id'] : null);
        }


        // 1. если модуль имеет родителя (окрыт через поле название)  - параметр pdi.
        // 2. если edit-view имеет родителя (окрыт из сабмодуля)  - параметр parent_copy_id
        if(!empty($this->_vars['parent_copy_id']) && is_array($this->_vars['parent_copy_id']) && $parent_field_name = (array_search($this->_vars['schema']['params']['relate_module_copy_id'], $this->_vars['parent_copy_id']))){

            switch($parent_field_name){
                case 'pci' :
                    if($this->_vars['parent_copy_id']['pci'] == $this->_vars['schema']['params']['relate_module_copy_id']){
                        if(!$this->_id) $this->_id = $this->_vars['parent_data_id']['pdi'];
                        $this->_relate_disabled = 'disabled="disabled"';
                    }
                    break;
                case 'parent_copy_id' :
                    if(!$this->_id) $this->_id = $this->_vars['parent_data_id']['parent_data_id'];
                    break;
            }
        }

        // это первичный модуль - устанавливаем  его ІД по модулю-родителю pci
        if($this->_pci == $this->_vars['schema']['params']['relate_module_copy_id'] && !empty($this->_vars['parent_copy_id']['pci']) && $this->_vars['parent_copy_id']['pci']){

            if($this->_pci == $this->_vars['schema']['params']['relate_module_copy_id']){
                //$pci_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id']);
                $pci_module_table = ModuleTablesModel::model()->find(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $this->_vars['parent_copy_id']['pci'],
                                                                    ':relate_copy_id' => $this->_pci)));
                if(!empty($pci_module_table)){
                    $relate_data_id = DataModel::getInstance()
                                                    ->setFrom('{{' . $pci_module_table->table_name . '}}')
                                                    ->setWhere($pci_module_table->parent_field_name . ' = :id', array(':id' => $this->_vars['parent_data_id']['pdi']))
                                                    ->findRow();
                    if(!empty($relate_data_id)){
                        if(!$this->_id) $this->_id = $relate_data_id[$pci_module_table->relate_field_name];
                    }

                    $this->_relate_disabled = 'disabled="disabled"';
                }
            }
        }



        // данние из базы (связаные данные при редактировании) - берем ИД связаного модуля из связующей таблицы
        if($this->_vars['extension_data'] && $this->_vars['extension_data']->isNewRecord == false){
            $data_id = $this->_vars['extension_data']->{$this->_vars['extension_copy']->prefix_name.'_id'};

            $mt_model = ModuleTablesModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                'params' => array(
                    ':copy_id'=>$this->_vars['extension_copy']->copy_id,
                    ':relate_copy_id'=>$this->_vars['schema']['params']['relate_module_copy_id'])));


            if($mt_model){
                $relate_data_id = DataModel::getInstance()
                    ->setFrom('{{' . $mt_model->table_name . '}}')
                    ->setWhere($this->_vars['extension_copy']->prefix_name . '_id = :id', array(':id' => $data_id))
                    ->findRow();

                if(!empty($relate_data_id) && !$this->_id) $this->_id = $relate_data_id[$this->relate_extension_copy->prefix_name . '_id'];
            }
        }

        if(!$this->_id){
            // если edit-view окрыт из сабмодуля
            if(!empty($this->_vars['parent_relate_data_list'])){
                if(!empty($this->_vars['parent_relate_data_list'][$this->_vars['schema']['params']['relate_module_copy_id']])){
                    if($this->_vars['extension_data']->isNewRecord && $this->_vars['parent_relate_data_list'][$this->_vars['schema']['params']['relate_module_copy_id']]){
                        if(!$this->_id) $this->_id = $this->_vars['parent_relate_data_list'][$this->_vars['schema']['params']['relate_module_copy_id']];

                        // если поле первичное
                        if(ModuleTablesModel::isSetRelate($this->_pci, $this->_vars['extension_copy']->copy_id, 'relate_module_many')){
                            $this->_relate_disabled = 'disabled="disabled"';
                        }
                    } /*else
                    if(ModuleTablesModel::isSetRelate($this->_pci, $this->_vars['extension_copy']->copy_id, 'relate_module_many'))
                        $this->_relate_disabled = 'disabled="disabled"';
                        */
                } else {
                    /*
                    if(!empty($this->_pci) && $this->_pci == $this->_vars['schema']['params']['relate_module_copy_id'])
                        $this->_relate_disabled = 'disabled="disabled"';
                    */
                }
            } else {
                if(isset($this->_vars['default_data']) && $this->_vars['default_data'] !== null) $this->_id = $this->_vars['default_data'];
            }
        }


        if(empty(self::$_pdi) && !empty($this->_id) && !empty($this->_pci) && $this->_pci == $this->_vars['schema']['params']['relate_module_copy_id']){
            self::$_pdi = $this->_id;
        }


        // если из шаблона
        if(isset($_POST['from_template']) && (boolean)$_POST['from_template'] == true && !empty($this->_pci) && $this->_pci == $this->_vars['schema']['params']['relate_module_copy_id']){
            if(!empty(self::$_pdi) && empty($this->_id)) {
                $this->_id = self::$_pdi;
            }
        }


        if(!empty($this->_pci) && $this->_pci == $this->_vars['schema']['params']['relate_module_copy_id'] && isset($this->_vars['parent_copy_id']['parent_copy_id']) && $this->_vars['parent_copy_id']['parent_copy_id']){
            if(ModuleTablesModel::isSetRelate($this->_pci, $this->_vars['extension_copy']->copy_id, 'relate_module_many'))
                $this->_relate_disabled = 'disabled="disabled"';
        }

        if(!empty($this->_vars['parent_copy_id']['parent_copy_id']) && $this->_vars['parent_copy_id']['parent_copy_id'] == $this->_vars['schema']['params']['relate_module_copy_id']){
            $this->_relate_disabled = 'disabled="disabled"';
        }

        if($this->_vars['extension_copy']->getModule(false)->isTemplate($this->_vars['extension_copy']) && $this->relate_extension_copy->getModule(false)->isTemplate($this->relate_extension_copy) == false){
            if($this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                $this->_relate_disabled = 'disabled="disabled"';
            }
        }


        $b = (!empty($_POST['relate_check_about_parent']));
        if($b && $this->isSetDataIdAboutParent() == false){
            $this->_id = null;
        }


        return $this;
    }


    /**
     * Проверка ID, есть ли он относительно связанного родительского СДМ
     *
     * @return bool
     */
    private function isSetDataIdAboutParent(){
        if($this->_reloader_status != self::RELOADER_STATUS_CHILDREN){
            return true;
        }
        if($this->_id == false || $this->_id === 'false'){
            return true;
        }

        $option_data_params = $this->getOptionDataParams();

        if($this->_id && $option_data_params['get_option_list'] == false){
            return false;
        }

        $option_data_params['only_id'] = true;

        $list = $this->getOptionsDataList($option_data_params);

        return (bool)$list;
    }








    /**
     * getValue
     */
    public function getValue($id=null){
        $result = array();

        if(empty($id)){
            $id = $this->_id;

            if(isset($_POST['from_template']) && (boolean)$_POST['from_template'] == true && $this->_pci && $this->_pci == $this->_vars['schema']['params']['relate_module_copy_id']){
                if(!empty(self::$_pdi)){
                    $id = self::$_pdi;
                } else if(isset($_POST['default_data'][$this->_vars['schema']['params']['name']])) {
                    $id = $_POST['default_data'][$this->_vars['schema']['params']['name']];
                }
            }

        }

        if(!empty($id)){
            $data_model = DataModel::getInstance()
                                        ->setExtensionCopy($this->relate_extension_copy)
                                        ->setFromModuleTables();
            $data_model = $data_model
                    ->setFromFieldTypes();
            //responsible
            if($this->relate_extension_copy->isResponsible())
                $data_model->setFromResponsible();
            //participant
            if($this->relate_extension_copy->isParticipant())
                $data_model->setFromParticipant();

            $data_model
                    ->setCollectingSelect()
                    ->andWhere(array('AND', $this->relate_extension_copy->getTableName() . '.' . $this->relate_extension_copy->prefix_name . '_id=:id'), array(':id' => $id))
                    ->setGroup();

            $result = $data_model->findRow();

            if(empty($result)){
                $result = array();
            }
        }



        return $result;
    }



       
    
    public function isModuleParent(){
        $result = false;

        $primary_schema = ExtensionCopyModel::model()->findByPk($this->_vars['schema']['params']['relate_module_copy_id'])->getPrimaryField();
        if(!empty($primary_schema) && $primary_schema['params']['relate_module_copy_id'] == $this->_vars['extension_copy']->copy_id) 
            $result = true;
            
        return $result;
    }
    
    
    

    
}
