<?php

/**
 * DataQueryRelateOneModel - создание части запросса для отбора связанных значения данных полей типа "relate"
 *
 * @author Alex R.
 */
class DataQueryRelateOneModel{

    private $_parent_extension_copy;
    private $_extension_copy;
    private $_relate_extension_copy;
    private $_module_tables_model;
    private $_is_set_search = false;

    private static $sql_select = array();
    private static $sql_select_concat = array();
    private static $sql_join = array();

    public static $iteration = 0;




    public static function getInstance($flush = true){
        if($flush){
            self::$sql_select = array();
            self::$sql_select_concat = array();
            self::$sql_join = array();
            self::$iteration = 0;
        }

        return new self();
    }


    public function setParentExtensionCopy($parent_extension_copy){
        $this->_parent_extension_copy = $parent_extension_copy;
        return $this;
    }


    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }


    public function setRelateExtensionCopy($relate_extension_copy){
        $this->_relate_extension_copy = $relate_extension_copy;
        return $this;
    }


    public function setModuleTablesModel($module_tables_model){
        $this->_module_tables_model = $module_tables_model;
        return $this;
    }


    public function setIsSetSearch($is_set_search){
        $this->_is_set_search = $is_set_search;
        return $this;
    }


    public function getResult(){
        $result = array(
            'select' => self::$sql_select,
            'select_concat' => self::$sql_select_concat,
            'join' => self::$sql_join,
        );

        return $result;
    }



    public function prepare(){
        $this->addRelateIntermediateTableQuery();
        $this->addRelateQuery();

        self::$iteration++;

        return $this;
    }


    /**
     * addRelateIntermediateTableQuery - связующая таблица
     */
    private function addRelateIntermediateTableQuery(){
        if(!empty($this->_parent_extension_copy) && $this->_parent_extension_copy->copy_id == $this->_relate_extension_copy->copy_id) return;

        self::$sql_join[] = array(
                                'table' => '{{' . $this->_module_tables_model->table_name . '}}',
                                'on' => $this->_extension_copy->getTableName() . '.' . $this->_module_tables_model->parent_field_name . '=' . '{{' . $this->_module_tables_model->table_name . '}}.' . $this->_module_tables_model->parent_field_name
        );


        if(self::$iteration === 0){
            self::$sql_select[] = '{{' . $this->_module_tables_model->table_name . '}}.' . $this->_module_tables_model->relate_field_name . ' AS ' . $this->_relate_extension_copy->getTableName(null, false) . '_' . $this->_relate_extension_copy->prefix_name . '_id';
        }
    }



    /**
     * addRelateQuery - связанная таблица
     */
    private function addRelateQuery($relate_fields = null){
        if(!$this->_is_set_search) return;

        // fields
        if($relate_fields === null){
            $relate_fields = $this->getSelectRelateFields();
        }
        if($relate_fields == false){
            return;
        }

        $table_init=$this->_relate_extension_copy->getTableName();
        $table_name=$table_init;
        // join
        if(empty($this->_parent_extension_copy) || $this->_parent_extension_copy->copy_id != $this->_relate_extension_copy->copy_id){

            if($this->_relate_extension_copy->copy_id==ExtensionCopyModel::MODULE_STAFF || $this->_relate_extension_copy->copy_id==ExtensionCopyModel::MODULE_USERS)
            {
                $table_init=$this->_relate_extension_copy->getTableName().' as users_alias';
                $table_name='users_alias';
            }
            self::$sql_join[] = array(
                'table' => $table_init,
                'on' => $table_name . '.' . $this->_relate_extension_copy->prefix_name . '_id' . '=' . '{{' . $this->_module_tables_model->table_name . '}}.' . $this->_module_tables_model->relate_field_name
            );
        }

        if(!is_array($relate_fields)) $relate_fields =  explode(',', $relate_fields);

        foreach($relate_fields as $relate_field_name){
            $relate_field_params = $this->_relate_extension_copy->getFieldSchemaParams($relate_field_name);
            if($relate_field_params['params']['type'] == 'display_none'){
                $relate_field_params = $this->_relate_extension_copy->getFieldSchemaParams($this->_relate_extension_copy->getPrimaryViewFieldName());
            }

            switch($relate_field_params['params']['type']){
                case 'numeric':
                case 'string':
                case 'datetime':
                case 'display':
                case 'relate_string':
                    self::$sql_select_concat[] = 'COALESCE(' . $table_name . '.' . $relate_field_params['params']['name'] . ', "")';
                    break;

                case 'logical':
                    self::$sql_select_concat[] = 'IF(' . $table_name.'.'. $relate_field_params['params']['name'] . ' = 1,"'.\Yii::t('base','Yes').'","'.\Yii::t('base','No').'")';
                    break;

                case 'select':
                    $module_tables = $this->getModuleTablesSelectModel($this->_relate_extension_copy->copy_id);
                    self::$sql_join[] = array(
                        'table' => '{{' . $module_tables->table_name . '}}',
                        'on' => $table_name . '.' . $module_tables->parent_field_name . '=' . '{{' . $module_tables->table_name . '}}.' . $module_tables->relate_field_name
                    );
                    //self::$sql_select[] = '{{' . $module_tables->table_name . '}}.' . $module_tables->parent_field_name . '_title AS ' . $this->_relate_extension_copy->prefix_name . '_' . $module_tables->parent_field_name . '_title';;
                    self::$sql_select_concat[] = 'COALESCE({{' . $module_tables->table_name . '}}.' . $module_tables->parent_field_name . '_title, "")';
                    break;
                //case 'file':
                //case 'file_image':
                //case 'module':

                case 'relate':
                    $relate_one_result = DataQueryRelateOneModel::getInstance(false)
                                                ->setExtensionCopy($this->_relate_extension_copy)
                                                ->setRelateExtensionCopy(ExtensionCopyModel::model()->findByPk($relate_field_params['params']['relate_module_copy_id']))
                                                ->setModuleTablesModel($this->getModuleTablesModel($this->_relate_extension_copy->copy_id, $relate_field_params['params']['relate_module_copy_id']))
                                                ->setIsSetSearch($this->_is_set_search)
                                                ->prepare()
                                                ->getResult();

                    break;

                //case 'relate_dinamic':
                //case 'relate_this':
                //case 'permission':
            }

        }
    }






    /**
     * ищет и возвращает название поля связаного подуля по СДМ
     */
    private function getSelectRelateFields(){
        $relates = SchemaOperation::getRelates($this->_extension_copy->getSchemaParse());

        if(empty($relates)) return false;

        foreach($relates as $relate){
            if($relate['params']['relate_module_copy_id'] == $this->_relate_extension_copy->copy_id){
                return $relate['params']['relate_field'];
            }
        }

        return false;
    }




    /**
     * getModuleTablesModel
     */
    public function getModuleTablesModel($copy_id, $relate_copy_id){
        $module_tables_model = ModuleTablesModel::model()->find(
            array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                'params' => array(':copy_id' => $copy_id, ':relate_copy_id' => $relate_copy_id)
            )
        );

        return $module_tables_model;
    }



    /**
     * getModuleTablesSelectModel
     */
    public function getModuleTablesSelectModel($copy_id){
        $module_tables_model = ModuleTablesModel::model()->find(
            array(
                'condition' => 'copy_id=:copy_id AND `type` = "relate_select"',
                'params' => array(':copy_id' => $copy_id)
            )
        );

        return $module_tables_model;
    }




}
