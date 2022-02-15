<?php
/**
* SubModuleDeleteModel - удаление связи в элементе СМ. Работает рекурсивно
* 
* @author Alex R.
*/

class SubModuleDeleteModel {
    
    private $_primary_entities;
    private $_this_template = null;
    
    public static function getInstance(){
        return new self();
    }
    
    
    public function setPrimaryEntities($primary_entity){
        $this->_primary_entities = $primary_entity;
        return $this;
    }
    

    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }



    /**
     *  удялаяем связь между самими модулями
     */
    public function delete($parent_copy_id, $parent_data_id, $relate_copy_id, $relate_data_id){
        $this->recursiveDeleteFromSubModules($parent_copy_id, $relate_copy_id, $relate_data_id);

        $this->deleteRelatePrimary($parent_copy_id, $relate_copy_id, $relate_data_id);
        
        $this->deleteFromTitleRelate($parent_copy_id, array($parent_data_id));
        
        $this->deleteRelateData($parent_copy_id, array($parent_data_id), $relate_copy_id, $relate_data_id, null);
    }    
    
    
    
    


    /**
     * удаляем связь первичного поля СДМ в связаном модуле по полю Название
     */
    private function deleteFromTitleRelate($parent_copy_id, array $id){
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
                        $this->deleteRelateParent($title_extension_copy->copy_id, $id, $this->_primary_entities['primary_pci'], $sub_module_data_id);
                    }
                }
            }
        }
        if(!empty($sub_module_data_id))
            $this->recursiveDeleteFromSubModules($parent_copy_id, $title_extension_copy->copy_id, $sub_module_data_id);
    }

            





    /**
     * удаление связи с первичным полем  
     */
    private function deleteRelatePrimary($parent_copy_id, $relate_copy_id, $relate_data_id){
            $relates = SchemaOperation::getInstance()->getAllElementsWhereType(ExtensionCopyModel::model()->findByPk($relate_copy_id)->getSchemaParse(), array('relate'));
            if(!empty($relates)){
                foreach($relates as $relate){
                    if($this->isPrimaryModule($relate['relate_module_copy_id']) == false) continue;

                    if($relate['relate_module_copy_id'] == $parent_copy_id ||
                       $relate['relate_module_copy_id'] == $this->_primary_entities['primary_pci'] ||
                       $relate['relate_module_copy_id'] == ExtensionCopyModel::MODULE_TASKS) continue;


                    $relate_table = ModuleTablesModel::model()->find(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $relate_copy_id,
                                                                    ':relate_copy_id' => $relate['relate_module_copy_id'])));

                    DataModel::getInstance()->Delete('{{' . $relate_table->table_name  . '}}', $relate_table->parent_field_name . ' in (' . implode(',', $relate_data_id) . ')');
                }
            }
    }
    


    
    /**
     * рекурсивное удаление связей значения первичиного модуля  
     */
    private function recursiveDeleteFromSubModules($parent_copy_id, $relate_copy_id, $relate_data_id){
            $sub_modules = SchemaOperation::getSubModules(ExtensionCopyModel::model()->findByPk($relate_copy_id)->getSchemaParse()); 
            if(!empty($sub_modules)){
                foreach($sub_modules as $module){
                    $module = $module['sub_module'];
                    if($module['params']['relate_module_copy_id'] == $parent_copy_id) continue; // если СМ - обратная связь
                    
                    $relate_table = ModuleTablesModel::model()->find(array(
                                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                    'params' => array(
                                                                    ':copy_id' => $relate_copy_id,
                                                                     ':relate_copy_id' => $module['params']['relate_module_copy_id'])));
                    $sub_module_data = new DataModel();
                    $sub_module_data
                        ->setFrom('{{' . $relate_table->table_name . '}}')
                        ->setWhere($relate_table->parent_field_name . ' in (' . implode(',', $relate_data_id) . ')');
                                                
                    $sub_module_data = $sub_module_data->findAll();
                    if(empty($sub_module_data)) continue;
                    
                    $sub_module_data_id = array_unique(array_keys(CHtml::listData($sub_module_data, $relate_table->relate_field_name, '')));
                    
                    if($this->isPrimaryModule($relate_copy_id) && $this->isPrimaryModule($module['params']['relate_module_copy_id'])){                                        
                        $this->deleteFromSubmodule($relate_copy_id, $module['params']['relate_module_copy_id'], $sub_module_data_id, $relate_data_id);
                        $this->deleteRelateData($relate_copy_id, $relate_data_id, $module['params']['relate_module_copy_id'], $sub_module_data_id, $module);
                    }
                }
            }
            $this->deleteFromTitleRelate($relate_copy_id, $relate_data_id);
    }
    

                    
                  
                  


    /**
     *  удаляем данные модуля, если тип EditViewModel::THIS_TEMPLATE_TEMPLATE_CM
     */             
    private function deleteRelateData($parent_copy_id, array $parent_data_id, $relate_copy_id, array $relate_data_id, $relate_module_params = null){
        if($relate_module_params === null){
            $relate_module_params = $this->getSubModuleParams($parent_copy_id, $relate_copy_id);
        }

        if(!empty($relate_module_params))
            $allow_status = EditViewDeleteModel::getInstance()->allowDeleteRelateData($parent_copy_id, $relate_module_params['params']);

        //удаляем только связи
        if($allow_status == false){
            $this->deleteRelate($parent_copy_id, $parent_data_id, $relate_copy_id, $relate_data_id);
            return;
        }
        
        $delete_where_this_template = null;
        
        if($allow_status == EditViewDeleteModel::DELETE_CM_TEMPLATE){
            if($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
                $delete_where_this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM;
            } else {
                $this->deleteRelate($parent_copy_id, $parent_data_id, $relate_copy_id, $relate_data_id);
                return;
            }
        } 
        
               
        // иначе удаляем данные и связь  
        $extension_copy = ExtensionCopyModel::model()->findByPk($relate_copy_id);
        
        // все данные сабмодуля
        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $extension_copy->getTableName(null, false),
            'params' => Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
        );

        $edit_view_model = EditViewModel::modelR($alias, $dinamic_params, true);
        //$edit_view_model->refreshMetaData();
        $sub_module_data = $edit_view_model->findAllByPk($relate_data_id);

        if(empty($sub_module_data)) return;
        
        foreach($sub_module_data as $data){
            EditViewDeleteModel::getInstance() // рекурсивно удаляем данные
                                    ->prepare($relate_copy_id, array($data->getPrimaryKey()), $delete_where_this_template)
                                    ->delete();
        }         
    }
    
                    



    
    /**
     *  рекурсивное удаление звязей с первичными модулями из сабмодулей 
     */
    private function deleteFromSubmodule($parent_copy_id, $relate_copy_id, $relate_data_id, $parent_data_id){
        if($this->isPrimaryModule($relate_copy_id)){
            $this->recursiveDeleteFromSubModules($parent_copy_id, $relate_copy_id, $relate_data_id);
        
        $this->deleteFromTitleRelate($relate_copy_id, $relate_data_id);
        
        $this->deleteRelateParent($relate_copy_id, $parent_data_id, $this->_primary_entities['primary_pci'], $relate_data_id);
        }
    }       
    
    

    /**
     * возвращает параметры схемы подключенного самбмодуля в родительском элементе 
     */
    private function getSubModuleParams($parent_copy_id, $relate_copy_id){
        $params = array();
    
        $sub_modules = SchemaOperation::getSubModules(ExtensionCopyModel::model()->findByPk($parent_copy_id)->getSchemaParse()); 
        if(empty($sub_modules)) return $params;
        
        foreach($sub_modules as $module){
            if($module['sub_module']['params']['relate_module_copy_id'] == $relate_copy_id){
                $params = $module['sub_module'];
                break;
            }
        }
        return $params;
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
     * удялаяем связь между самими модулями
     */
    private function deleteRelate($parent_copy_id, array $parent_data_id, $relate_copy_id, array $relate_data_id){
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                        'params' => array(
                                                        ':copy_id' => $parent_copy_id,
                                                        ':relate_copy_id' => $relate_copy_id)));                        
        DataModel::getInstance()->Delete('{{' . $relate_table->table_name  . '}}', $relate_table->parent_field_name . ' in (' . implode(',', $parent_data_id) . ') AND ' . $relate_table->relate_field_name . ' in (' . implode(',', $relate_data_id) . ')');
    }
    

    /**
     * удялаяем связь между связаным и первичным модулями 
     */
    private function deleteRelateParent($parent_copy_id, array $parent_data_id, $relate_copy_id, array $relate_data_id){
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(
                                                        ':copy_id' => $parent_copy_id,
                                                        ':relate_copy_id' => $relate_copy_id)));                        
        DataModel::getInstance()->Delete('{{' . $relate_table->table_name  . '}}', $relate_table->relate_field_name . ' in (' . implode(',', $parent_data_id) . ') AND ' . $relate_table->parent_field_name . ' in (' . implode(',', $relate_data_id) . ')');
    }




   


    
    
}
