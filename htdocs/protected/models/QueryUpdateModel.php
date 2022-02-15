<?php
/**
 * class QueryUpdateModel
 *
 * @author Alex B.
 */


class QueryUpdateModel {


    private $_extension_copy;
    private $_sql;
    private $_primary_field_name;
    private $_type = 'replace';
    

    /**
     * getInstance
     */
    public static function getInstance(){
        return new self();
    }



    /**
     * primary key
     */
    public function setPrimaryFieldName($field_name){
        $this->_primary_field_name = $field_name;
        return $this;
    }
   
    
    /**
     * extension copy
     */
    public function setExtensionCopy($copy){
        $this->_extension_copy = $copy;
        return $this;
    }
    

    /**
     * тип апдейта: замена данных, совмещение
     */
    public function setType($type){
        $this->_type = $type;
        return $this;
    }



    /**
     * Подготовка данных
     */
    public function prepareData($data_list){
        if(empty($data_list)){
            return $this;
        }

        $this->_sql = '';
        $data_prepared = array();
        
        foreach($data_list as $v) {
            if($this->_type == 'combine' && empty($v['value'])){
                continue;
            }

            if($v['value'] === ''){
                $v['value'] = null;
           }

            switch($v['type']){
                case 'relate_participant' :
                    //ответственный
                    if(!empty($v['value']))
                        $this->_sql .= $this->getPreparedResponsible($v['value'], $v[$this->_primary_field_name]);
                break;
                case 'relate' :
                    //СДМ
                    if(!empty($v['relate_module_copy_id']))
                        $this->_sql .= $this->getPreparedRelate($v['value'], $v['relate_module_copy_id'], $v[$this->_primary_field_name]);
                break;
                default :
                    $data_prepared[$v[$this->_primary_field_name]][] = array('name' => $v['name'], 'value' => $v['value']);
            }
        }
        
        if(!empty($data_prepared)) {
            foreach($data_prepared as $pk_id => $data) {
                if(!empty($data)){
                    $this->_sql .= $this->getPreparedUpdateQuery($data, $pk_id);
                }
            }  
        }
        
        return $this;
    }




    private function getPreparedUpdateQuery($data_row, $condition_id){
        if(empty($data_row)){
            return;
        }

        $data_tmp = [];
        $sql = 'UPDATE {{'. $this->_extension_copy->getTableName(null, false) .'}}' . ' SET ';

        foreach($data_row as $data){
            if($data['value'] === null){
                $data_tmp[] = $data['name'] . ' = null';
            } else {
                $data_tmp[] = $data['name'] . ' = "' . $data['value'] . '"';
            }
        }

        $sql .= implode(',', $data_tmp) . ' WHERE ' . $this->_primary_field_name . ' = ' . $condition_id . ';';
        
        return $sql;
    }





    private function getPreparedResponsible($ug_id, $card_id){
        $sql = 'UPDATE {{participant}}' . ' SET ug_id = ' . $ug_id . ' WHERE copy_id = ' . $this->_extension_copy->copy_id  . ' AND data_id = ' . $card_id . ';';
        return $sql;

    }
    



    private function getPreparedRelate($field_value, $relate_copy_id, $card_id){
        $sql = '';
        
        $module_tables_model = \ModuleTablesModel::getRelateModuleTableData($this->_extension_copy->copy_id, $relate_copy_id);
        
        if(empty($field_value)) {
            //удаляем запись
            $sql = 'DELETE FROM {{' . $module_tables_model['table_name'] . '}}' . ' WHERE ' . $module_tables_model['parent_field_name'] . ' = ' .  $card_id . ';';
        }else {
            //проверям наличие, либо добавляем, либо изменяем запись
            $current_relate = DataModel::getInstance()
                                    ->setFrom('{{'  . $module_tables_model['table_name'] . '}}')
                                    ->setWhere(
                                            $module_tables_model['parent_field_name'] . '=:data_id',
                                            array(
                                                ':data_id' => $card_id,
                                                ))
                                    ->findRow();

            if(!$current_relate) {
                //новая связь
                $sql = 'INSERT INTO {{' . $module_tables_model['table_name'] . '}}' . ' (' . $module_tables_model['parent_field_name'] . ', ' . $module_tables_model['relate_field_name'] . ') VALUES (' . $card_id . ', ' . $field_value . ');';
            }else {
                //изменение
                $sql = 'UPDATE {{' . $module_tables_model['table_name'] . '}}' . ' SET ' . $module_tables_model['relate_field_name'] . ' = ' . $field_value . ' WHERE ' . $module_tables_model['parent_field_name'] . ' = ' .  $card_id . ';';
            }
            
        }

        return $sql;
    }
    

    /**
     * execute
     */
    public function execute(){
        $data_model = new DataModel();
        $data_model
            ->setText($this->_sql)
            ->execute();
        
        return $this;
    }





}
