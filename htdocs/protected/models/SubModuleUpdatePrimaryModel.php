<?php


class SubModuleUpdatePrimaryModel {
    
    //static 
    
    private $_primary_entities;
    
    
    public static function getInstance(){
        return new self();
    }
    
    
    public function setPrimaryEntities($primary_entity){
        $this->_primary_entities = $primary_entity;
        return $this;
    }
    


    /**
     *  обновляем связь между самими модулями
     */
    public function update($parent_copy_id, $parent_data_id, $relate_copy_id, $relate_data_id){
        //проверка на наличия первичного поля в подчиненном модуле
        if($this->isPrimaryModule($relate_copy_id)){
            $this->recursiveUpdateFromSubModules($parent_copy_id, $relate_copy_id, $relate_data_id);
            
            $this->updateRelate($relate_copy_id, $this->_primary_entities['primary_pci'], $relate_data_id);
        }
        $this->updateFromTitleRelate($parent_copy_id, $parent_data_id);
    }    
    
    
    
    



    /**
     * обновляем связь первичного поля СДМ в связаном модуле по полю Название
     */
    public function updateFromTitleRelate($parent_copy_id, array $id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id);
        
        //берем значение первичного поля и проверяем тип relate_string
        $first_field_params = $extension_copy->getPrimaryField();

        if(empty($first_field_params) || $first_field_params['params']['type'] != 'relate_string') return; 
         
        $title_extension_copy = ExtensionCopyModel::model()->findByPk($first_field_params['params']['relate_module_copy_id']);
        // первое поле relate из title
        $first_title_field_params = $title_extension_copy->getFieldSchemaParamsByType('relate');

        if(!empty($first_title_field_params)){
            // если первичное
            if($this->isPrimaryModule($first_title_field_params['params']['relate_module_copy_id']) == true ||
               $first_title_field_params['params']['relate_module_copy_id'] == $this->_primary_entities['primary_pci'])
            {
                $relate_table = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                'params' => array(
                                                                ':copy_id' => $extension_copy->copy_id,
                                                                ':relate_copy_id' => $title_extension_copy->copy_id)));
                if(!empty($relate_table)){
                    // все данные модуля
                    $sub_module_data = new DataModel();
                    $sub_module_data
                        ->setFrom('{{' . $relate_table->table_name . '}}')
                        ->setWhere($relate_table->parent_field_name . ' in (' . implode(',', $id) .')' ) ;
                    
                    
                    $sub_module_data = $sub_module_data->findAll();
                    if(!empty($sub_module_data)){
                        $sub_module_data_id = array_unique(array_keys(CHtml::listData($sub_module_data, $relate_table->relate_field_name, '')));
                        $this->updateRelate($title_extension_copy->copy_id, $this->_primary_entities['primary_pci'], $sub_module_data_id);
                        
                    }
                }
            }
        }

        if(!empty($sub_module_data_id)){
            $this->recursiveUpdateFromSubModules($parent_copy_id, $title_extension_copy->copy_id, $sub_module_data_id);
            
            foreach($sub_module_data_id as $sm_id){
                $this->deletePrimaryValueForRelate($extension_copy, $title_extension_copy, $sm_id);
            }
            
        }
    }

 
             
    
    /**
     * рекурсивное обновление связей значения первичиного модуля  
     */
    private function recursiveUpdateFromSubModules($parent_copy_id, $relate_copy_id, $relate_data_id){
            $sub_modules = SchemaOperation::getSubModules(ExtensionCopyModel::model()->findByPk($relate_copy_id)->getSchemaParse()) ; 

            if(!empty($sub_modules)){
                foreach($sub_modules as $module){
                    $module = $module['sub_module'];
                    if($parent_copy_id == $module['params']['relate_module_copy_id']) continue;  //?
        
                    if($this->isPrimaryModule($module['params']['relate_module_copy_id']) == false) continue;

                    $relate_table = ModuleTablesModel::model()->find(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $relate_copy_id,
                                                                    ':relate_copy_id' => $module['params']['relate_module_copy_id'])));
                    // все данные сабмодуля
                    $sub_module_data = new DataModel();
                    $sub_module_data
                        ->setFrom('{{' . $relate_table->table_name . '}}')
                        ->setWhere($relate_table->parent_field_name . ' in (' . implode(',', $relate_data_id) . ')');
                                                
                    $sub_module_data = $sub_module_data->findAll();
                    if(empty($sub_module_data)) continue;
                    
                    $sub_module_data_id = array_unique(array_keys(CHtml::listData($sub_module_data, $relate_table->relate_field_name, '')));
                    $this->update($relate_copy_id, $relate_data_id, $module['params']['relate_module_copy_id'], $sub_module_data_id);
                }
            }
            $this->updateFromTitleRelate($relate_copy_id, $relate_data_id);
    }
    


    
    /**
     * проверка на наличия первичного поля в подчиненном модуле
     */ 
    private function isPrimaryModule($relate_copy_id){
        $result = false;

        if(!empty($this->_primary_entities['primary_pci']) &&
           ModuleTablesModel::isSetRelate($this->_primary_entities['primary_pci'], $relate_copy_id, 'relate_module_many') &&
           ModuleTablesModel::isSetRelate($relate_copy_id, $this->_primary_entities['primary_pci'], 'relate_module_one')){
            $result = true;
        } 
        
        return $result;
    }
    
    
    
    /**
     * устанавляваем новое значение первичного поля 
     */
    public function updateRelate($parent_copy_id, $relate_copy_id, array $relate_data_id){
        if(empty($this->_primary_entities['primary_pdi'])) return;
        
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(
                                                        ':copy_id' => $parent_copy_id,
                                                        ':relate_copy_id' => $relate_copy_id)));
                                                        
        DataModel::getInstance()->Delete('{{' .$relate_table->table_name. '}}', $relate_table->parent_field_name .' in ('.implode(',', $relate_data_id).')');

        foreach($relate_data_id as $data_id){
            DataModel::getInstance()->Insert(
                                '{{' .$relate_table->table_name. '}}',
                                array(
                                    $relate_table->relate_field_name => $this->_primary_entities['primary_pdi'],
                                    $relate_table->parent_field_name => $data_id,
                                ));
        }
    }




       
    /**
     * удаляет связи во всех елементах СДМ определенного модуля
     */
    public function deletePrimaryValueForRelate($parent_extension_copy, $extension_copy, $id){
        // элементы блока СДМ
        $relates = SchemaOperation::getRelates($extension_copy->getSchemaParse()); 
        if(!empty($relates)){
            foreach($relates as $relate){
                if($parent_extension_copy !== null && $parent_extension_copy->copy_id == $relate['params']['relate_module_copy_id']) continue;
                
                RelateUpdatePrimaryModel::getInstance()
                                            ->setPrimaryEntities($this->_primary_entities)
                                            ->delete(
                                                $relate['params']['relate_module_copy_id'],
                                                $extension_copy->copy_id,
                                                $id);
            }
        }        
    }       



   
    
    
}
