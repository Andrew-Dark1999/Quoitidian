<?php
/**
 * OperationChangeElementModel
 *
 * @author Alex R.
 *
 * Базовая модель для операторов Begin, Timer, Operation
 * Управляет списками
 */



namespace Process\models;

use Process\components\OperationModel;

class OperationChangeElementModel extends OperationModel{


    const ELEMENT_OBJECT_NAME       = 'object_name';    // связанный обьект
    const ELEMENT_RELATE_MODULE     = 'relate_module';  // связанный модуль
    const ELEMENT_FIELD_NAME        = 'field_name';     // название поля (только типы "датавремя")

    const ELEMENT_VALUE_SCALAR          = 'value_scalar';
    const ELEMENT_VALUE_DATETIME        = 'value_datetime';
    const ELEMENT_VALUE_SELECT          = 'value_select';
    const ELEMENT_VALUE_RELATE          = 'drop_down_button';

    const ELEMENT_LABEL_ADD_VALUE       = 'label_add_value';

    const ELEMENT_VALUE_SCALAR_CONDITION       = 'value_condition';
    const ELEMENT_VALUE_SCALAR_VALUE           = 'value_value';



    protected $_active_object_name_type;
    protected $_active_object_name;
    protected $_active_extension_copy;
    protected $_active_relate_extension_copy;
    protected $_active_card_id;
    protected $_active_field_name;
    protected $_active_field_type;


    private $_only_types = null;


    protected function setTitle(){}



    public function getExtensionCopy(){
        if($this->_active_relate_extension_copy){
            return $this->_active_relate_extension_copy;
        }
        if($this->_active_extension_copy){
            return $this->_active_extension_copy;
        }
    }


    public function getActiveFieldName(){
        return $this->_active_field_name;
    }



    protected function setOnlyTypes($field_types){
        if(is_string($field_types)){
            $field_types = array($field_types);
        }
        $this->_only_types = $field_types;
        return $this;
    }




    /**
     * refreshSchema - обновляет элементы в схеме относительно рание подготовленных базовых парамеров
     */
    protected function refreshSchema(&$schema){
        foreach($schema as &$element){
            if(array_key_exists('elements', $element)){
                return $this->refreshSchema($element['elements']);
            }

            switch($element['type']){
                //ELEMENT_OBJECT_NAME
                case self::ELEMENT_OBJECT_NAME:
                    $element['value'] = $this->_active_object_name;
                    break;

                //ELEMENT_RELATE_MODULE
                case self::ELEMENT_RELATE_MODULE:
                    $element['value'] = ($this->_active_relate_extension_copy ? $this->_active_relate_extension_copy->copy_id : null) ;
                    break;

                //ELEMENT_FIELD_NAME
                case self::ELEMENT_FIELD_NAME:
                    $element['value'] = $this->_active_field_name;
                    break;

                //ELEMENT_VALUE_SCALAR
                case self::ELEMENT_VALUE_SCALAR:
                    if(empty($this->_active_field_name)){
                        $element['value'] = null;
                        $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                        $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                    } else {
                        $field_type = $this->getFieldType($this->_active_field_name);
                        //numeric
                        if($field_type == \Fields::MFT_NUMERIC){
                            if(!is_numeric($element[self::ELEMENT_VALUE_SCALAR_VALUE][0])){
                                $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            }
                            //datetime
                        } elseif($field_type == \Fields::MFT_DATETIME){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_DATETIME;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //relate
                        } elseif(in_array($field_type, array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_THIS))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_RELATE;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                            //select
                        } elseif($field_type == \Fields::MFT_SELECT){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SELECT;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                        }
                    }
                    break;

                //ELEMENT_VALUE_DATETIME
                case self::ELEMENT_VALUE_DATETIME:
                    if(empty($this->_active_field_name)){
                        $element['value'] = null;
                        $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                        $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                    } else {
                        $field_type = $this->getFieldType($this->_active_field_name);
                        //scalar
                        if(in_array($field_type, array(\Fields::MFT_NUMERIC, \Fields::MFT_STRING, \Fields::MFT_DISPLAY, \Fields::MFT_RELATE_STRING))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SCALAR;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //relate
                        } elseif(in_array($field_type, array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_THIS))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_RELATE;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                            //select
                        } elseif(in_array($field_type, array(\Fields::MFT_SELECT))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SELECT;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                        } elseif(in_array($field_type, array(\Fields::MFT_DATETIME))){
                            // при изменении типа проверки
                            if(!empty($element[self::ELEMENT_VALUE_SCALAR_VALUE])){
                                if(empty($element[self::ELEMENT_VALUE_SCALAR_VALUE][0])){
                                    $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                                }
                            }
                        }

                    }
                    break;

                //ELEMENT_VALUE_SELECT
                case self::ELEMENT_VALUE_SELECT:
                    if(empty($this->_active_field_name)){
                        $element['value'] = null;
                        unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                        unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                    } else {
                        $field_type = $this->getFieldType($this->_active_field_name);
                        //scalar
                        if(in_array($field_type, array(\Fields::MFT_NUMERIC, \Fields::MFT_STRING, \Fields::MFT_DISPLAY, \Fields::MFT_RELATE_STRING))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SCALAR;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //datetime
                        } elseif(in_array($field_type, array(\Fields::MFT_DATETIME))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_DATETIME;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //relate
                        } elseif(in_array($field_type, array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_THIS))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_RELATE;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                        }
                    }
                    break;

                //ELEMENT_VALUE_RELATE
                case self::ELEMENT_VALUE_RELATE:
                    if(empty($this->_active_field_name)){
                        $element['value'] = null;
                        unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                        unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                    } else {
                        $field_type = $this->getFieldType($this->_active_field_name);
                        //scalar
                        if(in_array($field_type, array(\Fields::MFT_NUMERIC, \Fields::MFT_STRING, \Fields::MFT_DISPLAY, \Fields::MFT_RELATE_STRING))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SCALAR;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //datetime
                        } elseif(in_array($field_type, array(\Fields::MFT_DATETIME))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_DATETIME;
                            $element[self::ELEMENT_VALUE_SCALAR_CONDITION] = null;
                            $element[self::ELEMENT_VALUE_SCALAR_VALUE] = null;
                            //select
                        } elseif(in_array($field_type, array(\Fields::MFT_SELECT))){
                            $element['value'] = null;
                            $element['type'] = self::ELEMENT_VALUE_SELECT;
                            unset($element[self::ELEMENT_VALUE_SCALAR_CONDITION]);
                            unset($element[self::ELEMENT_VALUE_SCALAR_VALUE]);
                        }
                    }
                    break;
            }
        }
        unset($element);

        return $this;
    }



    protected function inRecordNameList($unique_index, $value){
        return \Process\models\OperationDataRecordModel::getInstance()->inRecordNameList($unique_index, $value);
    }



    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    protected function prepareBaseEntities($schema){
        $this->_active_object_name_type = null;
        $this->_active_object_name = null;
        $this->_active_extension_copy = null;
        $this->_active_relate_extension_copy = null;
        $this->_active_card_id = null;
        $this->_active_field_name = null;

        // object_name
        $on_isset = 0;
        $on_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_OBJECT_NAME));
        if(!empty($on_schema) && !empty($on_schema['value'])){
            if(is_string($on_schema['value'])){
                $dr_id = json_decode($on_schema['value'], true);
            } else {
                $dr_id = $on_schema['value'];
            }
            if(
                $dr_id['type'] == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
                $this->inRecordNameList($this->_operations_model->unique_index, $on_schema['value'])
            ){
                $this->_active_object_name_type = OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO;
                $bo_relate_field_data = BindingObjectModel::getInstance()
                    ->setVars(array('process_id' => ProcessModel::getInstance()->process_id))
                    ->getRelateFieldData(false);
                if(!empty($bo_relate_field_data)){
                    $this->_active_card_id = $bo_relate_field_data['card_id'];
                }

                $this->_active_object_name = $dr_id;
                $this->_active_extension_copy = \ExtensionCopyModel::model()->findByPk($dr_id['copy_id']);
                $on_isset = 1;
            } elseif($dr_id['type'] == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM){
                $this->_active_object_name_type = OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM;
                if($this->issetObjectName($dr_id['data_record_id'])){
                    $this->_active_object_name = $dr_id;
                    $this->_active_card_id = $dr_id['data_record_id'];
                    $on_isset = 2;
                }
            }
        }

        if($on_isset === 0){
            if($this->_operations_model){
                $ob_list = OperationDataRecordModel::getInstance()->getRecordNameList($this->_operations_model->unique_index);
                if(!empty($ob_list)){
                    $this->_active_object_name_type = OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM;
                    $this->_active_object_name = array('type' => OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM, 'data_record_id' => array_keys($ob_list)[0]);
                    $this->_active_card_id = array_keys($ob_list)[0];
                } else{
                    return;
                }
            }
        }

        if($on_isset !== 1){
            // extension_copy
            $copy_id = $this->findValueInDataRecord(OperationDataRecordModel::ELEMENT_MODULE_NAME);
            if(empty($copy_id)) return;
            $this->_active_extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);

        }

        //relate_module
        $fn_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_RELATE_MODULE));

        if(!empty($fn_schema) && !empty($fn_schema['value'])){
            $modules = $this->getRelateModuleList();
            if($modules && array_key_exists($fn_schema['value'], $modules)){
                $this->_active_relate_extension_copy = \ExtensionCopyModel::model()->findByPk($fn_schema['value']);
            }
        }

        // field_name
        $fn_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_FIELD_NAME));

        if(!empty($fn_schema) && !empty($fn_schema['value']) && $this->issetFieldName($fn_schema['value'])){
            $this->_active_field_name = $fn_schema['value'];
        } else {
            $fields = $this->getFieldNameList();
            if(!empty($fields)){
                $this->_active_field_name = array_keys($fields)[0];
            } else {
                return;
            }
        }
        $this->_active_field_type = $this->getFieldType($this->_active_field_name);


        return $this;
    }








    /**
     * issetObjectName - проверка существования object_name
     * @param $object_name
     * @return bool
     */
    public function issetObjectName($object_name){
        $result = false;

        if($this->_operations_model == false) return $result;

        $object_name_list = OperationDataRecordModel::getInstance()->getRecordNameList($this->_operations_model->unique_index);
        if(empty($object_name_list)) return $result;
        $list = array();
        foreach($object_name_list as $key => $value){
            if(empty($key)) continue;
            $str = json_decode($key, true);
            if($str['type'] == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM){
                $list[$str['data_record_id']] = $value;
            }
        }

        if(array_key_exists($object_name, $list)){
            $result = true;
        }

        return $result;
    }






    /**
     * findValueInDataRecord - Возвращает copy_id модуля по параметру object_name (Из оператора Запись данных)
     * @return int
     */
    public function findValueInDataRecord($element_name){
        if(empty($this->_active_card_id)) return;

        $operations_id = \DataModel::getInstance()
            ->setSelect('operations_id')
            ->setFrom('{{process_operation_data_record_params}}')
            ->setWhere('data_record_id=:data_record_id', array(':data_record_id'=>$this->_active_card_id))
            ->findScalar();

        if(empty($operations_id)) return;

        $operation_model = OperationModel::findOperationModel(array('operations_id' => $operations_id));

        $schema = $operation_model->_operations_model->getSchema();
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => $element_name));
        $value = $from_schema['value'];

        if(empty($value)) $value = null;

        return $value;
    }




    public function getElementObjectNameTitle(){
        return \Yii::t('ProcessModule.base', 'Parameter');
    }


    /**
     * getObjectNameList - возвращает список сущностей объекта-параметра для поля
     * @return array
     */
    public function getObjectNameList($unique_index){
        return \Process\models\OperationDataRecordModel::getInstance()->getRecordNameList($unique_index);
    }



    /**
     * getRelateModuleList - возвращает список связанных модулей (СДМ, СДМ-название)
     */
    public function getRelateModuleList($add_zero_value = false){
        if($this->_active_extension_copy == false){
            return array();
        }

        $module_tables = \ModuleTablesModel::getRelateModel($this->_active_extension_copy->copy_id, null, \ModuleTablesModel::TYPE_RELATE_MODULE_ONE, false);
        if(!$module_tables) return array();

        $result = array();
        foreach($module_tables as $module_table){
            $extension_copy = $module_table->relateExtensionCopy;
            if($extension_copy->isActive() == false) continue;
            $result[$extension_copy->copy_id] = $extension_copy->title;
        }

        asort($result);

        if($add_zero_value){
            $result = array('' => '') + $result;
        }

        return $result;
    }









    /**
     * getFieldNameList - Возвращает список полей
     * @param $field_type - тип поля
     */
    public function getFieldNameList(){
        $result = array();

        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false) return $result;

        $sub_module_schema_parse = $extension_copy->getSchemaParse($extension_copy->getSchema(), array(), array(), false);

        $params = \SchemaConcatFields::getInstance()
            ->setSchema($sub_module_schema_parse['elements'])
            ->setWithoutFieldsForListViewGroup($extension_copy->getModule(false)->getModuleName())
            ->parsing()
            ->primaryOnFirstPlace()
            ->prepareWithoutCompositeFields()
            ->getResult();

        if(!empty($params['header']))
            foreach ($params['header'] as &$fields) {
                foreach(explode(',', $fields['name']) as $field_name){
                    if(!in_array($params['params'][$field_name]['type'],
                        array(
                            //scalar
                            \Fields::MFT_STRING,
                            \Fields::MFT_NUMERIC,
                            \Fields::MFT_DISPLAY,
                            \Fields::MFT_RELATE_STRING,
                            //select
                            \Fields::MFT_SELECT,
                            //date_time
                            \Fields::MFT_DATETIME,
                            //relate
                            \Fields::MFT_RELATE,
                            \Fields::MFT_RELATE_THIS,
                        ))
                    ){
                        continue;
                    }

                    if($this->_only_types && in_array($params['params'][$field_name]['type'], $this->_only_types) == false){
                        continue;
                    }


                    $result[$field_name] = $fields['title'];
                }
            }

        return $result;
    }




    /**
     * issetFieldName - проверка существования field_name
     * @param $object_name
     * @return bool
     */
    public function issetFieldName($field_name){
        $result = false;

        $field_name_list = $this->getFieldNameList();
        if(empty($field_name_list)) return $result;
        if(array_key_exists($field_name, $field_name_list)){
            $result = true;
        }

        return $result;
    }




    /**
     * getFieldType - Возвращает тип поля
     * @param $field_name - название поля
     */
    public function getFieldType($field_name){
        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false) return;
        if($field_name == false) return;

        $schema_params = $extension_copy->getFieldSchemaParams($field_name);
        if(!empty($schema_params)) return $schema_params['params']['type'];
    }





















    /**
     * Возвращает список условий для опеределенного типа
     * @param $field_type - тип поля
     */
    public function getValueConditionList(){
        $result = array();

        $extension_copy = $this->getExtensionCopy();

        if($extension_copy == false || $this->_active_field_name == false) return $result;

        $field_type = $this->_active_field_type;
        if($field_type === null){
            $field_type = \Fields::MFT_STRING;
        }

        switch($field_type){
            case \Fields::MFT_STRING:
            case \Fields::MFT_DISPLAY :
            case \Fields::MFT_RELATE_STRING :
                $result = array(
                    \FilterModel::FT_CORRESPONDS => \FilterMap::getFilterTitle(\FilterModel::FT_CORRESPONDS),
                    \FilterModel::FT_CONTAINS => \FilterMap::getFilterTitle(\FilterModel::FT_CONTAINS),
                    \FilterModel::FT_BEGIN_WITH => \FilterMap::getFilterTitle(\FilterModel::FT_BEGIN_WITH),
                    \FilterModel::FT_END => \FilterMap::getFilterTitle(\FilterModel::FT_END),
                );
                break;

            case \Fields::MFT_DATETIME:
                $result = \FilterMap::getFilterList(\FilterMap::GROUP_3, null);
                \FilterMap::oneArrayFilterList($result);
                break;

            case \Fields::MFT_NUMERIC:
                $result = array(
                    \FilterModel::FT_CORRESPONDS => \FilterMap::getFilterTitle(\FilterModel::FT_CORRESPONDS),
                    \FilterModel::FT_MORE => \FilterMap::getFilterTitle(\FilterModel::FT_MORE),
                    \FilterModel::FT_LESS => \FilterMap::getFilterTitle(\FilterModel::FT_LESS),
                    \FilterModel::FT_EQUAL_NOT => \FilterMap::getFilterTitle(\FilterModel::FT_EQUAL_NOT),
                );
                break;
        }

        return $result;
    }











    /**
     * changeParamsContent
     * @param $action
     * @param $params
     * @return array
     */
    public function changeParamsContent($action, $params){
        $result = null;

        switch($action){
            case 'changed_object_name':
            case 'changed_relate_module':
            case 'changed_field_name':
                $result = $this->getBuildedParamsContent($params['schema_operation']);
                $result.= static::getInstance()
                                ->setOperationsModel($this->_operations_model)
                                ->getElementHtml(array('type'=>\Process\models\OperationConditionModel::ELEMENT_LABEL_ADD_VALUE));

                break;
            case 'changed_label_add_value':
                $result = $this->cpLabelAddValue($params);
                break;

        }


        return $result;
    }










    protected function cpLabelAddValue($params){
        $content = null;

        $schema = $params['schema_operation'];
        if(empty($schema)) return $content;

        $this->prepareBaseEntities($schema);


        switch($this->_active_field_type){
            case \Fields::MFT_SELECT :
                $element_schema = array(
                    'type' => \Process\models\OperationConditionModel::ELEMENT_VALUE_SELECT,
                    'value' => null,
                );
                break;

            case \Fields::MFT_RELATE :
            case \Fields::MFT_RELATE_THIS :
                $element_schema = array(
                    'type' => \Process\models\OperationConditionModel::ELEMENT_VALUE_RELATE,
                    'value' => null,
                );
                break;

            case \Fields::MFT_DATETIME :
                $element_schema = array(
                    'type' => \Process\models\OperationConditionModel::ELEMENT_VALUE_DATETIME,
                    'value' => null,
                    \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION => null,
                    \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_VALUE => null,
                );
                break;

            case \Fields::MFT_STRING :
            case \Fields::MFT_NUMERIC :
            case \Fields::MFT_DISPLAY :
            case \Fields::MFT_RELATE_STRING :
                $element_schema = array(
                    'type' => \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR,
                    'value' => null,
                    \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION => null,
                    \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_VALUE => null,
                );
                break;
        }

        if(!empty($element_schema)){
            $content = $this->getElementHtml($element_schema);
        }


        return $content;

    }








    //***********************************************************
    //          Check running condition
    //***********************************************************




    /**
     * checkDirect - сама проверка
     */
    protected function checkDirect($element, $edit_model){
        $status = false;

        switch($this->_active_field_type){
            case \Fields::MFT_STRING :
            case \Fields::MFT_NUMERIC :
            case \Fields::MFT_DISPLAY :
            case \Fields::MFT_RELATE_STRING :
                $status = $this->checkForScalarType($element, $edit_model);
                break;
            case \Fields::MFT_DATETIME :
                $status = $this->checkForDateTimeType($element, $edit_model);
                break;
            case \Fields::MFT_SELECT :
                $status = $this->checkForSelectType($element, $edit_model);
                break;
            case \Fields::MFT_RELATE :
            case \Fields::MFT_RELATE_THIS :
                $status = $this->checkForRelateType($element, $edit_model);
                break;
        }

        return $status;
    }

    // scalar types: string, numeric
    private function checkForScalarType($element, $edit_model){
        $result = false;

        $condition = $element[OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION];
        if(empty($condition)){
            return $result;
        }
        $condition = str_replace('_', '', $condition);
        $method = 'checkCondition' . $condition;

        $value = $element[OperationConditionModel::ELEMENT_VALUE_SCALAR_VALUE];
        if($value === '' || (is_array($value) && !$value)) $value = null;

        $result = $this->{$method}($edit_model, $value);

        return $result;
    }

    // date_time type
    private function checkForDateTimeType($element, $edit_model){
        $result = false;

        $condition = $element[OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION];
        if(empty($condition)){
            return $result;
        }

        $value = $element[OperationConditionModel::ELEMENT_VALUE_SCALAR_VALUE];
        if($value === '' || (is_array($value) && !$value)) $value = null;

        $result = $this->checkConditionDate($edit_model, $condition, $value);

        return $result;
    }

    // select type
    private function checkForSelectType($element, $edit_model){
        $value = $element['value'];
        if($value === '' || (is_array($value) && !$value)) $value = null;

        $result = $this->checkConditionCorresponds($edit_model, $value);

        return $result;
    }

    // relate type
    private function checkForRelateType($element, $edit_model){
        $value = $element['value'];
        if($value === '' || (is_array($value) && !$value)) $value = null;

        $result = $this->checkConditionCorrespondsSdm($edit_model, $value);

        return $result;
    }

    //Соответствует
    private function checkConditionCorresponds($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($this->_active_field_type == 'numeric'){
            $edit_value = \Helper::TruncateEndZero($edit_value);
        }

        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if($value === $edit_value) return true;

        return false;
    }





    //Соответствует (СДМ)
    private function checkConditionCorrespondsSdm($edit_model, $value){
        $edit_value = $edit_model->getRelateModuleData($this->_active_field_name);

        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }
        if($value === '') $value = null;

        if($value === null && $value ===  $edit_value) return true;
        if(is_array($edit_value) && $value && in_array($value, $edit_value)) return true;

        return false;
    }




    //Содержит
    private function checkConditionContains($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($value === null){
            return true;
        }
        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if($edit_value !== null){
            return (boolean)preg_match('/'.$value.'/',  $edit_value);
        }

        return false;
    }


    //Начинается
    private function checkConditionBeginWith($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($value === null){
            return true;
        }
        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if($edit_value !== null){
            return (boolean)preg_match('/^'.$value.'/',  $edit_value);
        }

        return false;
    }


    //Заканчивается
    private function checkConditionEnd($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($value === null){
            return true;
        }
        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if($edit_value !== null){
            return (boolean)preg_match('/'.$value.'$/',  $edit_value);
        }

        return false;
    }


    //Больше
    private function checkConditionMore($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($this->_active_field_type == 'numeric'){
            $edit_value = \Helper::TruncateEndZero($edit_value);
        }
        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if(!is_numeric($value) || !is_numeric($edit_value)) return false;

        if($edit_value > $value) return true;

        return false;
    }

    //Менше
    private function checkConditionLess($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($this->_active_field_type == 'numeric'){
            $edit_value = \Helper::TruncateEndZero($edit_value);
        }
        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if(!is_numeric($value) || !is_numeric($edit_value)) return false;

        if($edit_value < $value) return true;

        return false;
    }

    //Не равно
    private function checkConditionEqualNot($edit_model, $value){
        $edit_value = $edit_model->{$this->_active_field_name};
        if($edit_value === null || $edit_value == '') $edit_value = null;

        if($this->_active_field_type == 'numeric'){
            $edit_value = \Helper::TruncateEndZero($edit_value);
        }

        if(is_array($value) && array_key_exists(0, $value)){
            $value = $value[0];
        }

        if(!is_numeric($value) || !is_numeric($edit_value)) return false;

        if($edit_value != $value) return true;

        return false;
    }

    // для тип ДатаВремя
    private function checkConditionDate($edit_model, $condition, $value){
        $edit_date = $edit_model->{$this->_active_field_name};
        if($edit_date == '') $edit_date = null;

        $date1 = null;
        $date2 = null;

        if(is_array($value)){
            if(count($value) == 1){
                $date1 = $value[0];
                if(!$date1 || !\Helper::checkCharForDate($date1)) $date1 = null;
            } else {
                $date1 = $value[0];
                $date2 = $value[1];
                if(!$date1 || !\Helper::checkCharForDate($date1)) $date1 = null;
                if(!$date2 || !\Helper::checkCharForDate($date2)) $date2 = null;
            }
        }

        switch($condition){
            case \FilterModel::FT_DATE_FOR_TODAY :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $diff1 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 00:00:00'));
                $diff2 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_FOR_7_DAYS :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $diff1 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 00:00:00', strtotime('-7 days')));
                $diff2 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_FOR_30_DAYS :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $diff1 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 00:00:00', strtotime('-30 days')));
                $diff2 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_CURRENT_MONTH :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d2 = new \DateTime(date('Y-m-01 00:00:00'));
                $d2->modify('+1 month -1 day');
                $diff1 = \DateTimeOperations::dateDiff($edit_date, date('Y-m-01 00:00:00'));
                $diff2 = \DateTimeOperations::dateDiff($edit_date, $d2->format('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_PAST_MONTH :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d1 = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $d2 = new \DateTime($d1);
                $d2->modify('+1 month -1 day');
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                $diff2 = \DateTimeOperations::dateDiff($edit_date, $d2->format('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_CURRENT_YEAR :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d1 = date('Y-01-01 00:00:00');
                $d2 = new \DateTime($d1);
                $d2->modify('+1 year -1 day');
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                $diff2 = \DateTimeOperations::dateDiff($edit_date, $d2->format('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_PAST_YEAR :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d1 = new \DateTime(date('Y-01-01'));
                $d1->modify('-1 year');
                $d1 = $d1->format('Y-m-d 00:00:00');
                $d2 = new \DateTime($d1);
                $d2->modify('+1 year -1 day');

                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                $diff2 = \DateTimeOperations::dateDiff($edit_date, $d2->format('Y-m-d 23:59:59'));
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_AFTER :
                if(($edit_date == false || !\Helper::checkCharForDate($edit_date)) && !$date1) return true;
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                if($date1 == false) return false;

                $d1 = date('Y-m-d 00:00:00', strtotime($date1 . '+1 day'));
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                if($diff1 === 0 || $diff1 === 1){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_AFTER_CURRENT :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d1 = date('Y-m-d 00:00:00', strtotime('+1 day'));
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                if($diff1 === 0 || $diff1 === 1){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_TO :
                if(($edit_date == false || !\Helper::checkCharForDate($edit_date)) && !$date1) return true;
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                if($date1 == false) return false;

                $d1 = date('Y-m-d 23:59:59', strtotime($date1 . '-1 day'));
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                if($diff1 === 0 || $diff1 === -1){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_PRIOR_TO_CURRENT :
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                $d1 = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                if($diff1 === 0 || $diff1 === -1){
                    return true;
                }
                break;

            case \FilterModel::FT_DATE_PERIOD :
                if(($edit_date == false || !\Helper::checkCharForDate($edit_date)) && !$date1 && !$date2) return true;
                if($edit_date == false || !\Helper::checkCharForDate($edit_date)) return false;
                if($date1 == false) return false;
                if($date2 == false) return false;

                $d1 = date('Y-m-d 00:00:00', strtotime($date1));
                $d2 = date('Y-m-d 23:59:59', strtotime($date2));
                $diff1 = \DateTimeOperations::dateDiff($edit_date, $d1);
                $diff2 = \DateTimeOperations::dateDiff($edit_date, $d2);
                if(($diff1 === 0 || $diff1 === 1) && ($diff2 == 0 || $diff2 === -1)){
                    return true;
                }
                break;

            default :
                return false;

        }

        return false;
    }







    protected function getCardData(){
        $edit_data = array(
            'id' => $this->findRelateDataIDInDataRecord(),
            'pci' => null,
            'pdi' => null,
            'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
            'relate_template' => '0',
            'template_data_id' => null,
        );

        $edit_model = new \EditViewActionModel($this->_active_extension_copy->copy_id);
        $edit_model = $edit_model
            ->setEditData($edit_data)
            ->createEditViewModel()
            ->getEditModel();

        // если контроль в связанном модуле
        if($edit_model && $this->_active_relate_extension_copy){
            $field_schema_list = $this->_active_extension_copy->getFieldsSchemaList();
            foreach($field_schema_list as $field_schema){
                if(!in_array($field_schema['params']['type'], array(\Fields::MFT_RELATE, \Fields::MFT_RELATE_THIS, \Fields::MFT_RELATE_STRING))) continue;
                if($field_schema['params']['relate_module_copy_id'] != $this->_active_relate_extension_copy->copy_id) continue;

                $module_data_list = $edit_model->getRelateModuleData($field_schema['params']['name']);
                if(empty($module_data_list)) return;

                $edit_data['id'] = $module_data_list[0];

                $edit_model = new \EditViewActionModel($this->_active_relate_extension_copy->copy_id);
                $edit_model = $edit_model
                    ->setEditData($edit_data)
                    ->createEditViewModel()
                    ->getEditModel();
                break;
            }

        }

        return $edit_model;
    }





    /**
     * findRelateDataIDInDataRecord - Возвращает id карточки из модуля по параметру object_name (Из оператора Запись данных)
     * @return int
     */
    protected function findRelateDataIDInDataRecord(){
        if(empty($this->_active_card_id)) return;

        if($this->_active_object_name_type == OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO){
            return $this->_active_card_id;
        }

        $data_id = \DataModel::getInstance()
            ->setSelect('data_id')
            ->setFrom('{{process_operation_data_record_params}}')
            ->setWhere('data_record_id=:data_record_id', array(':data_record_id'=>$this->_active_card_id))
            ->findScalar();

        if(empty($data_id)) return;

        return $data_id;
    }







    //***********************************************************
    //          Check running condition     - END
    //***********************************************************








}
