<?php
/**
* QueryMemoryTable
* 
* @author Alex R.
*/

namespace Reports\extensions\Query;


class QueryMemoryTable{
    
    
    private $_data_setting;
    private $_data = array();
    private $_fields = array();
    private $_fields_type = array();
    private $_element_type;
    private $_reports_schema;
    private $_data_real;
    private $_param_x_sort_field_name;


    private $_result = array(
                            'create' => '',
                            'insert' => array(),
                            'select' => '',
                            'drop' => '',
                        );
    
    


    public static function getInstance(){
        return new self();
    }


    public function setDataSetting($setting){
        $this->_data_setting = $setting;
        
        return $this;
    }
    

    public function setData($data){
        $this->_data = $data;
        
        return $this;
    }

    
    public function getResult(){
        return $this->_result;
    }


    public function setElementType($element_type){
        $this->_element_type = $element_type;
        return $this;
    }


    public function setReportsSchema($reports_schema){
        $this->_reports_schema = $reports_schema;
        return $this;
    }


    
    public function build($element){
        switch($element){
            case \Reports\models\ConstructorModel::GRAPH_LINE:
            case \Reports\models\ConstructorModel::GRAPH_HISTOGRAM:
                $this->prepareFields();
                $this->prepareCreateQuery();
                $this->prepareInsertQuery();
                $this->prepareSelectQuery();
                $this->prepareDropQuery();
                break;
            case \Reports\models\ConstructorModel::GRAPH_CIRCULAR:
                $this->prepareFieldsGraphCircular();
                $this->prepareCreateQuery();
                $this->prepareInsertQuery();
                $this->prepareSelectCircularQuery();
                $this->prepareDropQuery();
                break;
            case \Reports\models\ConstructorModel::TABLE:
                $this->prepareFields();
                $this->prepareCreateQuery();
                $this->prepareInsertQuery();
                $this->prepareSelectQuery();
                $this->prepareDropQuery();
                break;
        }

        return $this;
    }








    /*************************************************************
     * private mothods
     ************************************************************/



    private function isSortedKey($field_name){
        $result = false;

        $keys = null;
        if(!empty($this->_data_setting['sorting'])){
            $keys = array_keys($this->_data_setting['sorting']);
            if(in_array($field_name, $keys)) $result = true;
        }

        return $result;
    }




    private function setFieldsType(){
        $this->_fields_type['param_x_sort'] = 'string';

        if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
            $this->_fields_type['param_x'] = 'datetime';
            $this->_fields_type['param_x_sort'] = 'datetime';
        } else{
            $this->_fields_type['param_x'] = 'string';
        }
        $this->_param_x_sort_field_name = 'param_x';

        // param_x_sort for Param
        $extension_copy_param = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id']);

        if($this->isSortedKey('param_x')){
            $this->_param_x_sort_field_name = 'param_x';
            if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name']) == false){
                $field_name = $this->_data_setting['param']['field_name'];
                if($this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                    $field_name = $extension_copy_param->getPrimaryViewFieldName();
                }
                $params = $extension_copy_param->getFieldSchemaParams($field_name);
                $this->_fields_type['param_x_sort'] = $params['params']['type'];
            }
        }

        //fields_type and param_x_sort for Indicators
        foreach($this->_data_setting['indicators'] as $indicator){
            if(empty($indicator['module_copy_id']) || empty($indicator['field_name'])){
                $this->_fields_type['f' . $indicator['unique_index']] = 'string';
            } elseif($indicator['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT){
                $this->_fields_type['f' . $indicator['unique_index']] = 'numeric';
                if($this->isSortedKey('f' . $indicator['unique_index'])){
                    $this->_fields_type['param_x_sort'] = 'numeric';
                    $this->_param_x_sort_field_name = 'f' . $indicator['unique_index'];
                }
            } else {
                $extension_copy = \ExtensionCopyModel::model()->findByPk($indicator['module_copy_id']);
                $params = $extension_copy->getFieldSchemaParams($indicator['field_name']);
                $this->_fields_type['f' . $indicator['unique_index']] = $params['params']['type'];

                //param_x_sort
                if($this->isSortedKey('f' . $indicator['unique_index'])){
                    $this->_fields_type['param_x_sort'] = $params['params']['type'];
                    $this->_param_x_sort_field_name = 'f' . $indicator['unique_index'];
                }
            }
        }


        return $this;
    }





    private function setFieldsTypeGraphCircular(){
        $this->_fields_type['label'] = 'string';
        $this->_fields_type['formatted'] = 'string';
        $this->_fields_type['value'] = 'string';
        $this->_fields_type['param_x_sort'] = 'string';

        foreach($this->_data_setting['indicators'] as $indicator){
            if(empty($indicator['module_copy_id']) || empty($indicator['field_name'])){
                $this->_fields_type['f' . $indicator['unique_index']] = 'string';
            } else {
                $extension_copy = \ExtensionCopyModel::model()->findByPk($indicator['module_copy_id']);
                $params = $extension_copy->getFieldSchemaParams($indicator['field_name']);
                //param_x_sort
                if($this->isSortedKey('f' . $indicator['unique_index'])){
                    $this->_fields_type['param_x_sort'] = $params['params']['type'];
                    $this->_param_x_sort_field_name = 'f' . $indicator['unique_index'];
                }
            }
        }

    }




    private function getSqlFieldQuery($field_name){
        if(!key_exists($field_name, $this->_fields_type)) return false;
        switch($this->_fields_type[$field_name]){
            case 'string':
            case 'numeric':
            case 'datetime':
            case 'logical':
            case 'select':
            case 'file':
            case 'file_image':
            case 'relate':
            case 'relate_this':
            case 'relate_string':
            case 'relate_participant':
            case 'display':
            case 'display_none':
                return 'varchar(255) default NULL';

            defuult :
                return 'varchar(255) default NULL';
        }
    }



    private function getSqlFieldSortQuery(){
        $sort_type = $this->_fields_type['param_x_sort'];
        if(empty($sort_type))
            $sort_type = 'string';

        switch($sort_type){
            case 'numeric':
                return 'numeric(16,5)';
            case 'datetime':
                return 'DateTime default NULL';

            case 'string':
            case 'logical':
            case 'select':
            case 'file':
            case 'file_image':
            case 'relate':
            case 'relate_this':
            case 'relate_string':
            case 'relate_participant':
            case 'display':
            case 'display_none':
                return 'varchar(255) default NULL';

            defuult :
                return 'varchar(255) default NULL';
        }
    }





    /**
     * getSqlFieldQueryValue
     */
    private function getSqlFieldQueryValue($parent_extension_copy, $module_params, $field_name, $data){
        if(!array_key_exists($field_name, $this->_fields_type)) return false;
        $value = $data[$field_name];
        if($this->_element_type != \Reports\models\ConstructorModel::TABLE){ // если график
            if($field_name == 'value'){ // график
                return $value;
            } else {
                return '"' . $value . '"';
            }
        }
        if(empty($module_params)){
            return $value;
        }

        $params = null;
        if(isset($module_params[$field_name])){
            $params = $module_params[$field_name];
        }


        // для таблиц
        if($this->_element_type == \Reports\models\ConstructorModel::TABLE){
            // param
            if(
                !empty($params['param_p']) &&
                \Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name']) == false &&
                $field_name != 'param_x_sort'
            ){
                $data_real = ($data['param_x'] !== null && array_key_exists($data['param_x'], $this->_data_real) ? $this->_data_real[$data['param_x']] : null);
                $value = $this->getListViewData($parent_extension_copy, $params['module_p']['params'], $data_real);
            }

            // indicator
            elseif(
                !empty($params['indicator_p']) &&
                $this->_data_setting['param']['module_copy_id'] == $params['indicator_p']['module_copy_id'] &&
                $this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID &&
                $field_name != 'param_x_sort'
            ){
                $value = $this->getListViewData($parent_extension_copy, $params['module_p']['params'], $this->_data_real[$data['param_x']]);
            }

            // param_x_sort
            elseif($field_name == 'param_x_sort'){
                $field_name = $this->_param_x_sort_field_name;
                if(isset($module_params[$field_name])){
                    $params = $module_params[$field_name];
                }

                if(!empty($params['param_p']) && \Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name']) == false){ // первое поле
                    $data_real = ($data['param_x'] !== null && array_key_exists($data['param_x'], $this->_data_real) ? $this->_data_real[$data['param_x']] : null);
                    $value = $this->getListViewData($parent_extension_copy, $params['module_p']['params'], $data_real);
                } elseif(
                        !empty($params['indicator_p'])&&
                        $this->_data_setting['param']['module_copy_id'] == $params['indicator_p']['module_copy_id'] &&
                        $this->_data_setting['param']['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID
                ){
                    if(!empty($params)){
                        $data_real = ($data['param_x'] !== null && array_key_exists($data['param_x'], $this->_data_real) ? $this->_data_real[$data['param_x']] : null);
                        $value = $this->getListViewData($parent_extension_copy, $params['module_p']['params'], $data_real);
                    }
                }
            }
        }

        switch($this->_fields_type[$field_name]){
            case 'string':
            case 'numeric':
            case 'datetime':
            case 'logical':
            case 'select':
            case 'file':
            case 'file_image':
            case 'relate':
            case 'relate_this':
            case 'relate_string':
            case 'relate_participant':
            case 'display':
            case 'display_none':
                return '"' . addslashes($value) . '"';

            defuult :
                return '"' . addslashes($value) . '"';
        }
    }





    /**
     * getListViewData
     */
    private function getListViewData($parent_extension_copy, $params, $value_data){
        return \Reports\models\DataRealModel::getElementDataReal($parent_extension_copy, $params, $value_data, false);
    }





    /**
     * getParams
     */
    private function getParams($parent_extension_copy){
        $result = array();

        //param
        $param_p = $this->_data_setting['param'];
        if($param_p['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
            $param_p['field_name'] = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id'])->getPrimaryViewFieldName();
        }
        $module_p = $parent_extension_copy->getFieldSchemaParams($param_p['field_name']);

        $result['param_x'] = array(
            'param_p' => $param_p,
            'indicator_p' => false,
            'module_p' => $module_p,
        );

        //indicators
        foreach($this->_data_setting['indicators'] as $indicator){
            $field_name = $indicator['field_name'];
            if(empty($indicator['module_copy_id']) || empty($field_name)) continue;
            if($indicator['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT) continue;

            $extension_copy = $parent_extension_copy;
            if($indicator['module_copy_id'] != $param_p['module_copy_id']){
                $extension_copy = \ExtensionCopyModel::model()->findByPk($indicator['module_copy_id']);
            }
            $module_p = $extension_copy->getFieldSchemaParams($indicator['field_name']);

            $result['f' . $indicator['unique_index']] = array(
                'param_p' => false,
                'indicator_p' => $indicator,
                'module_p' => $module_p,
            );

        }


        return $result;
    }








    /*************************************************************
     * public mothods
     ************************************************************/


    public function prepareFields(){
        $this->setFieldsType();

        if(empty($this->_data)) return $this;

        if($this->_element_type == \Reports\models\ConstructorModel::TABLE){
            $this->_fields['id'] = 'varchar(255) default NULL';
        }
        $this->_fields['param_x'] = 'varchar(255) default NULL';

        $field_type = $this->getSqlFieldSortQuery();
        if($field_type !== false){
            $this->_fields['param_x_sort'] = $field_type;
        } else {
            $this->_fields['param_x_sort'] = 'varchar(255) default NULL';
        }

        foreach($this->_data[0] as $field_name => $value){
            if($field_name != 'param_x' && $field_name != 'param_x_sort' && $field_name != 'param_s1' && $field_name != 'param_s2' && $field_name != 'param_s3'){
                $sql_field_request = $this->getSqlFieldQuery($field_name);
                if($sql_field_request !== false){
                    $this->_fields[$field_name] =  $sql_field_request;
                }
            }
        }

        return $this;
    }





    public function prepareFieldsGraphCircular(){
        $this->setFieldsTypeGraphCircular();

        if(empty($this->_data)) return $this;

        $this->_fields['label'] = 'VARCHAR(255) DEFAULT NULL';
        $this->_fields['formatted'] = 'VARCHAR(255) DEFAULT NULL';
        $this->_fields['value'] = 'VARCHAR(255) DEFAULT NULL';

        return $this;
    }



    
    
    /**
     * create
     */
    private function prepareCreateQuery(){
        $fields = array();
        foreach($this->_fields as $field_name => $field_param){
            $fields[] = $field_name . ' ' . $field_param;
        }

        $fields = implode(',', $fields);
        
        $this->_result['create'] = "DROP TABLE IF EXISTS {{reports_tmp}}; CREATE TABLE {{reports_tmp}} ($fields) ENGINE = InnoDB DEFAULT CHARSET=utf8;";
    }





    /**
     * insert
     */
    private function prepareInsertQuery(){
        $insert = array();
        
        $i = 0;
        $lich = 0;

        $count = count($this->_data);

        $parent_extension_copy = \ExtensionCopyModel::model()->findByPk($this->_data_setting['param']['module_copy_id']);
        $params = $this->getParams($parent_extension_copy);

        $this->_data_real = \Reports\models\ReportsTableModel::getInstance()
            ->setSchema($this->_reports_schema)
            ->setData($this->_data)
            ->setParentExtensionCopy($parent_extension_copy)
            ->prepare('param_x')
            ->getResultData();

        $fields = array_keys($this->_fields);

        foreach($this->_data as $data){
            $row = array();
            foreach($this->_fields as $field_name => $value){
                if($field_name == 'id'){
                    if(\Reports\models\ConstructorModel::isPeriodConstant($this->_data_setting['param']['field_name'])){
                        $row[] = 0;
                    } else{
                        $row[] = ($data['param_x'] === null ? "null" : $data['param_x']);
                    }
                    continue;
                }

                $field_value = $this->getSqlFieldQueryValue($parent_extension_copy, $params, $field_name, $data);
                if($field_value !== false){
                    $row[] = $field_value;
                }
            }
            $insert_tmp[] = '(' . implode(',', $row) . ')';
            
            
            if($i == 100 || $count == $lich){
                $insert[] = implode(',', $insert_tmp);
                $i = 0;
                $lich++;
                $insert_tmp = array();
                continue;
            } 

            $lich++;
            $i++;

            if($count == $lich){
               $insert[] = implode(',', $insert_tmp);
            }
        }
        if(!empty($insert)){
            foreach($insert as $value){
                $this->_result['insert'][] = 'INSERT INTO {{reports_tmp}} ('.implode(',', $fields).') VALUES ' . $value . ';';
            }
        }

        return $this;
    }








    /**
     * select
     */
    public function prepareSelectQuery(){
        if($this->_element_type == \Reports\models\ConstructorModel::TABLE){
            $direction = 'asc';
        } else {
            $direction = 'desc';
        }
        $order_by = 'ORDER BY param_x_sort ' . $direction;


        if(!empty($this->_data_setting['sorting'])){
            $keys =  array_keys($this->_data_setting['sorting']);
            if($this->_element_type == \Reports\models\ConstructorModel::TABLE){
                $direction = $this->_data_setting['sorting'][$keys[0]];
            }

            $order_by = 'ORDER BY param_x_sort ' . $direction;
        }


        $limit = '';
        $offset = '';

        if(!empty($this->_data_setting['pagination'])){
            $limit = 'LIMIT ' . $this->_data_setting['pagination']['limit'];
            $offset = 'OFFSET ' . $this->_data_setting['pagination']['offset'];
        }


        //search
        $search = array();
        if(!empty($this->_data_setting['filters']['search_model'])){
            $search_model = $this->_data_setting['filters']['search_model'];
            $search_text = $search_model::$text;
            if(!empty($search_text)){
                $search_text = str_replace('_', '\_', $search_text);
                $search[] = 'param_x LIKE "%' . $search_text . '%"';
                foreach($this->_data_setting['indicators'] as $indicator){
                    $search[] = 'f' . $indicator['unique_index'] . ' LIKE "%' . $search_text . '%"';
                }
            }
        }


        $fields = $this->_fields;
        unset($fields['param_x_sort']);
        $fields = implode(',', array_keys($fields));

        $calc_found_rows = '';
        if($this->_element_type == \Reports\models\ConstructorModel::TABLE){
            $calc_found_rows = 'SQL_CALC_FOUND_ROWS ';
        }


        $query = "
            SELECT
                $calc_found_rows
            	$fields
             FROM
                {{reports_tmp}}
            " . (!empty($search) ? 'WHERE ' . implode(' OR ', $search) : '') . "
             $order_by
             $limit
             $offset
        ";

        $this->_result['select'] = $query;
        
        return $this;        
    }







    /**
     * select circular
     */
    public function prepareSelectCircularQuery(){
        $search = array();
        if(!empty($this->_data_setting['filters']['search_model'])){
            $search_model = $this->_data_setting['filters']['search_model'];
            $search_text = $search_model::$text;
            if(!empty($search_text)){
                $search_text = str_replace('_', '\_', $search_text);
                $search[] = 'label LIKE "%' . $search_text . '%"';
                $search[] = 'formatted LIKE "%' . $search_text . '%"';
                $search[] = 'value LIKE "%' . $search_text . '%"';
            }
        }
        $fields = $this->_fields;
        $fields = implode(',', array_keys($fields));

        $query = "
            SELECT
            	$fields
             FROM
                {{reports_tmp}}
            " . (!empty($search) ? 'WHERE ' . implode(' OR ', $search) : '') . "
             ORDER BY value DESC
        ";

        $this->_result['select'] = $query;

        return $this;
    }




    /**
     * drop
     */
    private function prepareDropQuery(){
        $this->_result['drop'] = "DROP TABLE IF EXISTS {{reports_tmp}};";
    }
 
    
    
    
    
    
    
    
}
