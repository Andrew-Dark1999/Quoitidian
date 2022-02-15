<?php
/**
* @author Alex R.
*/


class EditViewSubModuleModel {
    
    private $_extension_copy;
    private $_parent_extension_copy;
    
    private $_vars = array();

    private $_pci = null;
    private $_pdi = null;

    private $_data_list_limit;
    private $_data_list_offset;

    private $_is_set_next_option_list_data = true;
    
    //use filters
    private $_filters = false;

    
    
    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    public function setDataListLimit($data_list_limit){
        $this->_data_list_limit = $data_list_limit;
        return $this;
    }

    public function setDataListOffset($data_list_offset){
        $this->_data_list_offset = $data_list_offset;
        return $this;
    }
   
    public function prepareVars(){
        $this
            ->setPci()
            ->setPdi()
            ->setParentExtensionCopy();

        $this->_data_list_limit = \DropDownListOptionsModel::getLimit();
        $this->_data_list_offset = \DropDownListOptionsModel::getOffset();

        return $this; 
    }
    
    
    
    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }
    

    private function setParentExtensionCopy(){
        $this->_parent_extension_copy = ExtensionCopyModel::model()->findByPk($this->_vars['parent_copy_id']);
        return $this;
    }

    
    
    /**
     * устанавливает copy_id связаного модуля по один-ко-многим  в аргумент self::pci  
     */
    public function setPci(){
        if(!empty($this->_vars['primary_entities']['primary_pci'])){
            $this->_pci = $this->_vars['primary_entities']['primary_pci'];
            return $this;
        }

        
        return $this;
    }
    


    /**
     * устанавливает copy_id связаного модуля по один-ко-многим  в аргумент self::pci  
     */
    public function setPdi(){
        if(!empty($this->_vars['primary_entities']['primary_pdi'])){
            $this->_pdi = $this->_vars['primary_entities']['primary_pdi'];
        }
        return $this;
    }





    /**
     * getIsSetNextOptionListData - возвращает статус еще существования данных списка
     * вызывать после отработки метода getOptionsDataList()
     */
    public function getIsSetNextOptionListData(){
        return $this->_is_set_next_option_list_data;
    }


    /**
     * Использование фильтров во время поиска
     */
    public function setFilters($filters){
        $this->_filters = $filters;
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
                                                                ':relate_copy_id' => $this->_extension_copy->copy_id)
                     
                                                ));

        if(empty($relate_table)){
            return $result; 
        } 

        if(empty($this->_pdi)){
            $result['get_option_list'] = false;
            return $result;
        }
        
        // поиск в связующей таблице списка ИД записей
        $data_list = DataModel::getInstance()
                            ->setFrom('{{' . $relate_table->table_name . '}}')
                            ->setWhere(array('AND', $relate_table->parent_field_name . '=' . $this->_pdi))
                            ->findAll();
        
        if(empty($data_list)){
            $result['get_option_list'] = false;
            return $result;
        }
        
        // передаем ИД как массив
        $result['options_id_list'] = array_unique(array_keys(CHtml::listData($data_list, $relate_table->relate_field_name, '')));
         
        
        return $result;
    }
    

    /**
     * поиск доп. параметров для отбора данных для options.
     * в 'options_id_list' - возвращается список ИД полей, если модуль имеет связь с другим модулем один-ко-многим
     */
    private function getOptionsDataParamsRelateThree(){
        $result = array(
            'get_option_list' => true,
            'options_id_list' => array(),
        );
        
        if((boolean)$this->_vars['relate_template'] == true){
            return $result;
        }
        
        
        if($this->_pci && !empty($this->_vars['primary_entities'])){
            // проверка на существование связи многие-к-одному
            $relate_table = ModuleTablesModel::model()->count(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $this->_extension_copy->copy_id,
                                                                    ':relate_copy_id' => $this->_pci,
                                                    )));
            if($relate_table){
                $result = $this->getOptionsIdListFromDB();
            }
        }
        
        return $result;
    }




    private function getOptionsDataParamsRelateTwo($option_data_params){
        $result = $option_data_params;

        // проверка, есть ли в модуле поле relate на родительский  
        $relate_module_table = ModuleTablesModel::model()->find(array(
                                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                                                            'params' => array(':copy_id' => $this->_extension_copy->copy_id, ':relate_copy_id' => $this->_vars['parent_copy_id'])
                                                        )
                                                    );

        // если есть связь relate_module_one    
        if(!empty($relate_module_table)){
            if($this->_vars['parent_data_id']){

                $module_relate = new DataModel();
                $module_relate = 
                    $module_relate
                        ->setFrom('{{' . $relate_module_table['table_name'] . '}}')
                        ->setWhere(array('AND',
                            $this->_parent_extension_copy->prefix_name . '_id=:id'),
                            array(':id'=>$this->_vars['parent_data_id']));
                
                $relate_data_id = $module_relate->findAll();

                if(empty($relate_data_id))
                    $result['options_id_list_two'] = array('AND', 'not exists (SELECT * FROM {{'. $relate_module_table['table_name'] .'}}'.
                                                                ' WHERE {{'.$relate_module_table['table_name'].'}}.'.$relate_module_table['parent_field_name'].' = '.
                                                                $this->_extension_copy->getTableName() .'.'. $this->_extension_copy->prefix_name . '_id)');
                else
                    $result['options_id_list_two'] = array('AND', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . '_id in ('.implode(',', array_unique(array_keys(CHtml::listData($relate_data_id, $this->_extension_copy->prefix_name . '_id', '')))).') OR ' .
                                                   'not exists (SELECT * FROM {{'. $relate_module_table['table_name'] .'}}'.
                                                                ' WHERE {{'.$relate_module_table['table_name'].'}}.'.$relate_module_table['parent_field_name'].' = '.
                                                                $this->_extension_copy->getTableName() .'.'. $this->_extension_copy->prefix_name . '_id)');

            } else {
                $result['options_id_list_two'] = array('AND', 'not exists (SELECT * FROM {{'. $relate_module_table['table_name'] .'}}'.
                                                            ' WHERE {{'.$relate_module_table['table_name'].'}}.'.$relate_module_table['parent_field_name'].' = '.
                                                            $this->_extension_copy->getTableName() .'.'. $this->_extension_copy->prefix_name . '_id)');
            }
        }

        return $result;        
    }








    /**
     * getValue
     */
    public function getValue($id){
        if(empty($id)){
            return false;
        }

        $module_tables_model = ModuleTablesModel::getRelateModel(
                                    $this->_parent_extension_copy->copy_id,
                                    $this->_extension_copy->copy_id,
                                    ModuleTablesModel::TYPE_RELATE_MODULE_MANY
                                );

        $selected_id_list = $this->getSelectedIdList($module_tables_model);

        if($selected_id_list == false){
            return false;
        }


        $data_model = DataModel::getInstance()
                        ->setExtensionCopy($this->_extension_copy)
                        ->setFromModuleTables()
                        ->setFromFieldTypes();
        //responsible
        if($this->_extension_copy->isResponsible())
            $data_model->setFromResponsible();
        //participant
        if($this->_extension_copy->isParticipant())
            $data_model->setFromParticipant();

        $data_model
            ->setCollectingSelect()
            ->andWhere(array('AND', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . '_id=:id'), array(':id' => $id))
            ->andWhere(array('in', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name. '_id', $selected_id_list))
            ->setGroup();

        $result = $data_model->findRow();

        if(empty($result)){
            return false;
        }

        return $result;
    }





    /**
     * getOptionsDataList - возвращает список сущностей, что используются для выбора нового значения из списка
     */
    public function getOptionsDataList(){
        $data_list = array();

        $option_data_params = $this->getOptionsDataParamsRelateThree();
        // если модуль не связан с другим с подченением
        if($option_data_params['get_option_list'] == true && empty($option_data_params['options_id_list'])){
            $option_data_params = $this->getOptionsDataParamsRelateTwo($option_data_params);
        }

        $search = new Search();
        $search->setTextFromUrl();

        $data_model = new DataModel();
        $data_model
            ->setIsSetSearch(($search::$text === null ? false : true))
            ->setSearchWsSm(true)
            ->setExtensionCopy($this->_extension_copy);
                      
        if($option_data_params['get_option_list'] !== false){
            if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS){
                $data_model->andWhere(array('AND', '(is_bpm_operation is NULL OR is_bpm_operation = "0")'));
            }

            if(!empty($option_data_params['options_id_list'])){
                // поиск названия связующей таблицы
                $relate_module_table = ModuleTablesModel::model()->find(array(
                                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one")',
                                                                    'params' => array(':copy_id' => $this->_extension_copy->copy_id, ':relate_copy_id' => $this->_vars['parent_copy_id'])
                                                                )
                                                            );
                if(!empty($relate_module_table))
                    $data_model->andWhere(array('AND', 'not exists (SELECT * FROM {{'. $relate_module_table['table_name'] .  '}} ' .
                                                ' WHERE {{'.$relate_module_table['table_name'].'}}.'.$relate_module_table['parent_field_name'].' = '.
                                                $this->_extension_copy->getTableName() .'.'. $this->_extension_copy->prefix_name . '_id)'));

                $data_model->andWhere(array('AND', array('in', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . '_id', $option_data_params['options_id_list'])));
            } else {                    
                if(!empty($option_data_params['options_id_list_two']))
                    $data_model->andWhere($option_data_params['options_id_list_two']);
            }

        
            // устанавливаем фильтр, если есть обратная связь c базовым модулем по полю relate
            if(!empty($this->_vars['id_added'])){
                $data_model->andWhere(array('AND', array('not in', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . '_id', $this->_vars['id_added'])));
            } 
    

            if($this->_extension_copy->getModule(false)->isTemplate($this->_extension_copy) && ($this->_vars['relate_template']) == true){
                $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE.'" '));
            } else  
            if($this->_parent_extension_copy->getModule(false)->isTemplate($this->_parent_extension_copy) && $this->_extension_copy->getModule(false)->isTemplate($this->_extension_copy)){
                if($this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                    $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE.'"'));
                } elseif($this->_vars['this_template'] == EditViewModel::THIS_TEMPLATE_MODULE){
                    $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'));
                }
            } else {
                $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'));
            }


            $data_model
                ->setFromModuleTables()
                ->setConcatGroupFieldSM($this->_extension_copy)
                ->setFromFieldTypes();

            //filters
            $there_is_participant = false;
            if($this->_filters) {
                list($filter_controller) = Yii::app()->createController($this->_extension_copy->extension->name . '/ListViewFilter');
                $filter_data = $filter_controller->getParamsToQuery($this->_extension_copy, $this->_filters);
                if(!empty($filter_data)){
                    $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
                    $there_is_participant = $filter_data['filter_params']['there_is_participant'];
                }
            }
                  
            //responsible
            if($this->_extension_copy->isResponsible())
                $data_model->setFromResponsible($there_is_participant);
            //participant
            if($this->_extension_copy->isParticipant())
                $data_model->setFromParticipant($there_is_participant);

            $data_model
                ->setGroup()
                ->setCollectingSelect();

            if($this->_extension_copy->dataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())){
                $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
            }

            if(!$this->_extension_copy->dataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())){
                $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
            }

            $alias = '';

            //search
            if($search::$text !== null){
                $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
                $alias = 'data';
            }

            $b = false;

            if($this->_data_list_limit || $this->_data_list_offset){
                $data_model
                    ->setLimit($this->_data_list_limit)
                    ->setOffSet($this->_data_list_offset)
                    ->addSqlCallFoundRows();

                $b = true;
                $alias = 'data';
            }

            $data_model->withOutRelateTitleTemplate(null, $alias);

            $data_list = $data_model->findAll();

            if($b){
                $this->getAllFoundRows();
            }
        }
        
        return $data_list;
    }



    /**
     * getSelectedIdList - возвращает список отобраных ID сущностей сабмодуля
     */
    public function getSelectedIdList($module_tables_model){
        if(empty($this->_vars['parent_data_id'])){
            return;
        }

        $data_id_list = (new DataModel())
                            ->setSelect($module_tables_model->relate_field_name)
                            ->setFrom('{{' . $module_tables_model->table_name . '}}')
                            ->setWhere(array('in', $module_tables_model->parent_field_name , $this->_vars['parent_data_id']))
                            ->findCol();

        return $data_id_list;
    }



    /**
     * getSelectedDataList - возвращает список сущноситей сабмодуля
     */
    public function getSelectedDataList(){
        $module_tables_model = ModuleTablesModel::getRelateModel(
                                                        $this->_parent_extension_copy->copy_id,
                                                        $this->_extension_copy->copy_id,
                                                        ModuleTablesModel::TYPE_RELATE_MODULE_MANY
                                                     );

        $selected_id_list = $this->getSelectedIdList($module_tables_model);

        if($selected_id_list == false){
            return;
        }

        $data_model = DataModel::getInstance()
                            ->setExtensionCopy($this->_extension_copy)
                            ->setFromModuleTables();


        //filters
        $there_is_participant = false;
        if($this->_filters) {
            list($filter_controller) = Yii::app()->createController($this->_extension_copy->extension->name . '/ListViewFilter');
            $filter_data = $filter_controller->getParamsToQuery($this->_extension_copy, $this->_filters);
            if(!empty($filter_data)){
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
                $there_is_participant = $filter_data['filter_params']['there_is_participant'];
            }
        }

        //responsible
        if($this->_extension_copy->isResponsible($there_is_participant)){
            $data_model->setFromResponsible();
        }
        //participant
        if($this->_extension_copy->isParticipant($there_is_participant)){
            $data_model->setFromParticipant();
        }

        $data_model
            ->setWhere(array('in', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name. '_id', $selected_id_list))
            ->setCollectingSelect()
            ->setGroup();

        if($this->_extension_copy->dataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())){
            $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
        }

        $b = false;

        if($this->_data_list_limit || $this->_data_list_offset){
            $data_model
                ->addSqlCallFoundRows()
                ->setLimit($this->_data_list_limit)
                ->setOffSet($this->_data_list_offset);

            $b = true;
        }

        $data_list = $data_model->findAll();

        if($b){
            $this->getAllFoundRows();
        }

        return $data_list;
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




}
