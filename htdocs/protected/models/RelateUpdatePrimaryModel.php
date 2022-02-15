<?php


class RelateUpdatePrimaryModel {
    
    private $_primary_entities;
    
    
    public static function getInstance(){
        return new self();
    }
    
    
    public function setPrimaryEntities($primary_entity){
        $this->_primary_entities = $primary_entity;
        return $this;
    }
    

    /**
     * ��������� ����� 
     */ 
    public function update($copy_id, $data_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $relate = $extension_copy->getFieldSchemaParamsByType('relate') ;
        
        if(empty($relate)) return;
        if($this->isPrimaryModule($copy_id) == false) return;

        $this->updateRelate($copy_id, $data_id);
    }
    

    /**
     * ������� �����
     */
    public function delete($relate_copy_id, $parent_copy_id, $parent_data_id){
        $extension_copy = ExtensionCopyModel::model()->findByPk($relate_copy_id);
        $relate = $extension_copy->getFieldSchemaParamsByType('relate') ;
        
        if(empty($relate)) return;
        if($this->isPrimaryModule($relate_copy_id) == false) return;

        $this->deleteRelate($relate_copy_id, $parent_copy_id, $parent_data_id);
    }



    
    /**
     * �������� �� ������� ���������� ���� � ����������� ������
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
     * ������������� ����� �������� ���������� ���� 
     */
    public function updateRelate($copy_id, $data_id){
        if(empty($this->_primary_entities['primary_pdi'])) return;
        
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(
                                                        ':copy_id' => $copy_id,
                                                        ':relate_copy_id' => $this->_primary_entities['primary_pci'])));
                                                        
        DataModel::getInstance()->Delete('{{' .$relate_table->table_name. '}}', $relate_table->parent_field_name .' = '.$data_id.'');

        DataModel::getInstance()->Insert(
                            '{{' .$relate_table->table_name. '}}',
                            array(
                                $relate_table->parent_field_name => $data_id,
                                $relate_table->relate_field_name => $this->_primary_entities['primary_pdi'],
                            ));
    }




    /**
     * ������������� ����� �������� ���������� ���� 
     */
    public function deleteRelate($relate_copy_id, $parent_copy_id, $parent_data_id){
        if(empty($this->_primary_entities['primary_pdi'])) return;
        
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(
                                                        ':copy_id' => $parent_copy_id,
                                                        ':relate_copy_id' => $relate_copy_id)));
                                                        
        DataModel::getInstance()->Delete('{{' .$relate_table->table_name. '}}', $relate_table->parent_field_name .' = '.$parent_data_id.'');

    }



   
    
    
}
