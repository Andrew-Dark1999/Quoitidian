<?php
/**
 * @author Alex R.
 */

namespace Process\models;


use Process\extensions\ElementMaster\Schema;

class OperationDataRecordModel  extends \Process\components\OperationModel{


    const ELEMENT_TYPE_OPERATION    = 'type_operation';     //	Тип оператора
    const ELEMENT_MODULE_NAME       = 'module_name';   	    //	Название модуля
    const ELEMENT_RECORD_NAME_TEXT  = 'record_name_text';   //	Название параметра (текст)
    const ELEMENT_RECORD_NAME_LIST  = 'record_name_list';   //	Название параметра (список параметров + связанный объект)
    const ELEMENT_ID                = 'indentificator_id';  //	ID
    const ELEMENT_CALL_EDIT_VIEW    = 'call_edit_view';   	//	Вызов EditView
    const ELEMENT_REQUIRED_FIELDS   = 'required_fields';   	//	Обязательные поля
    const ELEMENT_MESSAGE           = 'message';   	        //	Сообщение для исполнителя

    const ELEMENT_RECORD_NAME_TYPE_PARAM    = 'param';          // тип параметра: Параметр
    const ELEMENT_RECORD_NAME_TYPE_RO       = 'relate_object';  // тип параметра: Связанный объект

    const ELEMENT_TO_CREATING_RECORD    = '1';
    const ELEMENT_TO_CHANGE_DATA        = '2';

    const ELEMENT_CEV_CALL              = '1';
    const ELEMENT_CEV_NOT_CALLED        = '2';

    const ELEMENT_VALUE_BLOCK       = 'value_block';        //  Блок Значения
    const ELEMENT_VALUE_FIELD_NAME  = 'value_field_name';   //	Название поля
    const ELEMENT_VALUE_VALUE       = 'value_value';   	    //	Значение 1


    const ELEMENT_LABEL_ADD_VALUE   = 'label_add_value';   	//	Добавить значение

    const SCHEMA_CLEAN              = 'schema_clean';
    const SCHEMA_ADD_ENTITIES       = 'schema_add_entities';


    private $_active_type_operation;
    private $_active_extension_copy;
    private $_active_call_edit_view;

    private $_parents_data_record_list = array();
    public static $clone_params_replase_list = array();

    public static $_none_copy_id = false;

    protected $_is_possibly_bo = true;

    protected $_is_changed_record_name = false;



    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Data record');
    }


    /**
     * checkExecution - проверка выполнения, установка статуса
     * @return $this
     */
    public function checkExecution(){
        $process_model = ProcessModel::getInstance();
        if($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if($b_status == ProcessModel::B_STATUS_STOPED) return $this;

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->getStatus() == OperationsModel::STATUS_DONE){
                return $this;
            }

            if($this->_operations_model->parentOperationsIsDone() == false) return $this;

            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);

                //обновляем ИД карточки из связаного экземпляра обьекта
                //$this->updateDataIdFromBoObjectInstance();

                $this->addHistoryMessage();
            }

            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->autoRunEditView();
            }

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){

        }

        return $this;
    }




    private function addHistoryMessage(){
        $params = null;

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW));
        if($from_schema['value'] != self::ELEMENT_CEV_CALL) return;

        $user_id = null;
        $responsible = Schema::getInstance()->getOperationResponsible(null, $this->_operations_model->unique_index);
        if(!empty($responsible)){
            $user_id = $responsible['ug_id'];
        }

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_TYPE_OPERATION));

        $copy_id = $this->getModuleNameCopyId();
        if(empty($copy_id)) return;

        $message = $this->getMessage();
        if(!empty($message)) $comment[] = '</br>' . $message;

        $message = BindingObjectModel::getRelateObjectHistoryMessage(array('process_id' => ProcessModel::getInstance()->process_id));
        if(!empty($message)) $comment[] = '</br>' . $message;


        switch($from_schema['value']){
            case self::ELEMENT_TO_CREATING_RECORD :
                $module_type = \HistoryMessagesModel::MT_OPERATION_MUST_CREATED_RECORD;
                $params = array(
                            '{module_data_title}'=> \ExtensionCopyModel::model()->findByPk($copy_id)->title,
                            '{user_id}' => $user_id,
                            '{process_id}' => ProcessModel::getInstance()->process_id,
                            '{unique_index}' => $this->_operations_model->unique_index,
                            '{comment}' =>  (!empty($comment) ? implode('', $comment) : ''),
                            );
                break;
            case self::ELEMENT_TO_CHANGE_DATA :
                $data_id = $this->getDataId();
                if(empty($data_id)) return;

                $module_type = \HistoryMessagesModel::MT_OPERATION_MUST_CHANGED_RECORD;
                $edit_data = array(
                    'id' => $data_id,
                    'pci' => null,
                    'pdi' => null,
                    'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
                    'relate_template' => '0',
                    'template_data_id' => null,
                );



                $edit_model = new \EditViewActionModel($copy_id);
                $edit_model
                    ->setEditData($edit_data)
                    ->createEditViewModel(/*true*/);

                $params = array(
                            '{module_data_title}'=> $edit_model->getEditModel()->getModuleTitle(),
                            '{user_id}' => $user_id,
                            '{process_id}' => ProcessModel::getInstance()->process_id,
                            '{unique_index}' => $this->_operations_model->unique_index,
                            '{comment}' =>  (!empty($comment) ? implode('', $comment) : ''),
                            );
                break;
        }

        $history_model = new \History();
        $history_model
            ->setAddRealteHistoryData(false)
            ->addToHistory(
                $module_type,
                \ExtensionCopyModel::MODULE_PROCESS,
                ProcessModel::getInstance()->process_id,
                $params,
                false, false, true
            );


        if(!empty($user_id)){
            $history_mark = new \HistoryMarkViewModel();
            $history_mark->user_id = $user_id;
            $history_mark->history_id = $history_model->getLastHistoryId();
            $history_mark->save();
        }

    }


    /**
     * addActivityMessageForTask - Добавление сообщения Активности для задачи при создании карточки
     */
    private function addActivityMessageForTask(){
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_TYPE_OPERATION));
        if($from_schema['value'] != self::ELEMENT_TO_CREATING_RECORD) return;

        $copy_id = $this->getModuleNameCopyId();
        if(empty($copy_id) || $copy_id != \ExtensionCopyModel::MODULE_TASKS) return;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        $schema_activity = $extension_copy->getActivityField();

        if(empty($schema_activity)) return;

        $data_id = $this->getDataId();
        if(empty($data_id)) return;

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema(
                                        SchemaModel::getInstance()->getSchema(),
                                        array('only_first' => true,
                                              'name' => \Process\models\OperationsModel::ELEMENT_DATA_RECORD,
                                              'unique_index' => $this->_operations_model->unique_index,
                                        ));

        $href = '/module/BPM/run/' . \ExtensionCopyModel::MODULE_PROCESS  . '?process_id='. ProcessModel::getInstance()->process_id .'&unique_index=' . $this->_operations_model->unique_index;

        $attributes = array(
            'copy_id' => $copy_id,
            'data_id' => $data_id,
            'message' => \Yii::t('ProcessModule.messages', 'Related object') . ': <a href="'. $href .'">'. $from_schema['title'] .'</a>',
        );

        $activity_model = new \ActivityMessagesModel();
        $activity_model->setMyAttributes($attributes);
        $activity_model->setUserIsEmpty(true);
        $activity_model->save();
    }





    public static function getTypeOperationsList(){
        return array(
            self::ELEMENT_TO_CREATING_RECORD => \Yii::t('ProcessModule.base', 'Creating data'),
            self::ELEMENT_TO_CHANGE_DATA =>\Yii::t('ProcessModule.base', 'Change data'),
        );
    }


    public static function getModuleNameList($return_first = false){
        return ProcessModel::getModuleNameList($return_first);
    }





    public static function getCallEditViewList(){
        return array(
            self::ELEMENT_CEV_CALL => \Yii::t('ProcessModule.base', 'Call'),
            self::ELEMENT_CEV_NOT_CALLED =>\Yii::t('ProcessModule.base', 'Is not called'),
        );
    }





    public function getRequiredFields($fields_only = false, $field_names = null){
        $result = array();

        $copy_id = $this->getModuleNameCopyId();

        if(empty($copy_id)) return $result;

        $result = $this->getFieldNameList(true);

        if($fields_only){
            $field_names = explode(',', $field_names);
            if(count($field_names) > 1){
                $result = count($field_names);
                return $result;
            } elseif(count($field_names) == 1){
                foreach($result as $name => $value){
                    if($name == $field_names[0]){
                        return $value;
                    }
                }
                return;
            } else {
                return;
            }
        }

        return $result;
    }



    /**
     * getFieldNameList - Возвращает список полей
     * @param $field_type - тип поля
     */
    public function getFieldNameList($skip_edit_hidden = false){
        $result = array();
        if(empty($this->_active_extension_copy)) return $result;

        $sub_module_schema_parse = $this->_active_extension_copy->getSchemaParse();

        $without_fields = array(
            array('type'=>\Fields::MFT_DATETIME, 'name'=>'date_create'),
            array('type'=>\Fields::MFT_DATETIME, 'name'=>'date_edit'),
        );

        $params = \SchemaConcatFields::getInstance()
            ->setSchema($sub_module_schema_parse['elements'])
            ->setWithoutFieldsForListViewGroup($this->_active_extension_copy->getModule(false)->getModuleName())
            ->setWithoutFields($without_fields, true)
            ->parsing()
            ->prepareWithoutCompositeFields()
            ->getResult();

        if(!empty($params['header']))
            foreach ($params['header'] as &$fields) {
                foreach(explode(',', $fields['name']) as $field_name){
                    if(!in_array($params['params'][$field_name]['type'], array('display', 'relate_string', 'relate_this', 'string', 'numeric', 'select', 'relate', 'logical', 'datetime', 'relate_participant'))) continue;
                    if($params['params'][$field_name]['type']  == 'relate_participant' && $params['params'][$field_name]['type_view'] != \Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) continue;

                    if($skip_edit_hidden){
                        $field_schema = $this->_active_extension_copy->getFieldSchemaParams($field_name);
                        if($field_schema['params']['type_view'] == 'edit_hiddel') continue;
                    }
                    $result[$field_name] = $fields['title'];
                }
            }

        return $result;
    }



    /**
     * getValueValue
     */
    public function getValueValue($value, $field_name){
        if(empty($field_name)){
            $field_list = $this->getRequiredFields();
            if(empty($field_list)) return false;
            $field_name = array_keys($field_list)[0];
        }

        if(!empty($field_name) && !empty($this->_active_extension_copy)){
            $is_rp = false;
            $extension_copy = clone($this->_active_extension_copy);
            $params = $extension_copy->getFieldSchemaParams($field_name);

            if(empty($params)){
                $fields = $this->getRequiredFields();
                if(empty($fields)){
                    return false;
                } else {
                    return $this->getValueValue($value, array_keys($fields)[0]);
                }
            }

            $alias = 'evm_' . $extension_copy->copy_id;
            $dinamic_params = array(
                'tableName' => $extension_copy->getTableName(null, false),
                'params' => \Fields::getInstance()->getActiveRecordsParams($extension_copy->getSchemaParse()),
            );

            $edit_model = \EditViewModel::modelR($alias, $dinamic_params, true);
            $edit_model->setExtensionCopy($extension_copy);
            $edit_model->setIsNewRecord(false);

            $class_builder = new \EditViewBuilder();

            if($params['params']['type'] != 'relate' && $params['params']['type'] != 'relate_participant'){
                $edit_model->{$field_name} = (is_array($value) ? null : $value);
            } else if($params['params']['type'] == 'relate'){
                \EditViewRelateModel::setReloaderDefault(\EditViewRelateModel::RELOADER_STATUS_DEFAULT);
                $relate_data_id = null;
                if(isset($value['relate_copy_id']) && $value['relate_copy_id'] == $params['params']['relate_module_copy_id']){
                    $relate_data_id = $value['relate_data_id'];
                }

                $params['params']['relate_data_id'] = $relate_data_id;

            } else if($params['params']['type'] == 'relate_participant'){
                $edit_model->setIsNewRecord(true);
                $params['params']['default_value'] = $value;
                $class_builder = (new \Process\extensions\ElementMaster\EditViewBuilderForDr());
            }

            $content = $class_builder->setExtensionCopy($extension_copy)
                                     ->setExtensionData($edit_model)
                                     ->getEditViewElementEdit($params);
            return array(
                'field_name' => $field_name,
                'content' => $content,
            );

        } else {
            return false;
        }

        return false;
    }



    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    public function prepareBaseEntities($schema){
        $this->_active_type_operation = null;
        $this->_active_extension_copy = null;
        $this->_active_call_edit_view = null;

        // type_operation
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));
        $this->_active_type_operation = $from_schema['value'];


        // extension_copy
        $copy_id = $this->getModuleNameCopyId($schema);


        /*
        if($this->_active_type_operation == self::ELEMENT_TO_CHANGE_DATA){
            $bo_relate_copy_id = BindingObjectModel::getInstance()
                ->setVars(array('process_id' => ProcessModel::getInstance()->process_id))
                ->getRelateCopyId();

            if($bo_relate_copy_id == false || $bo_relate_copy_id != $copy_id){
                $copy_id = null;
            }
        }
        */



        if(!empty($copy_id)){
            $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        } else {
            if($this->_active_type_operation == self::ELEMENT_TO_CREATING_RECORD){
                $extension_copy = self::getModuleNameList(true);
            }
        }
        if(!empty($extension_copy)){
            $this->_active_extension_copy = $extension_copy;
        }

        if(empty($extension_copy)){
            self::$_none_copy_id = true;
        }

        // call_edit_view
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_CALL_EDIT_VIEW));
        if(!empty($from_schema)){
            $this->_active_call_edit_view = $from_schema['value'];
        }

        return $this;
    }







    /**
     * refreshSchema - обновляет элементы в схеме относительно рание подготовленных базовых парамеров
     */
    public function refreshSchema(&$schema){
        $schema_tmp = array();
        $added_vb = false;

        foreach($schema as $element){
            switch($element['type']){
                //ELEMENT_CALL_EDIT_VIEW
                case OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW:
                    $schema_tmp[] = $element;

                    if($this->_active_type_operation == OperationDataRecordModel::ELEMENT_TO_CREATING_RECORD){
                        $schema_tmp[] = array(
                            'type' => OperationDataRecordModel::ELEMENT_MODULE_NAME,
                            'value' => (!empty($this->_active_extension_copy) ? $this->_active_extension_copy->copy_id : null),
                        );
                    }



                    break;

                //ELEMENT_MODULE_NAME
                case OperationDataRecordModel::ELEMENT_MODULE_NAME:
                    break;

                //ELEMENT_RECORD_NAME_LIST
                case OperationDataRecordModel::ELEMENT_RECORD_NAME_LIST:
                    if($this->_active_type_operation == OperationDataRecordModel::ELEMENT_TO_CREATING_RECORD){
                        $element['type'] = OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT;
                        $element['value'] = self::getRecordNameIndexName($this->_operations_model->operations_id);
                    }

                    $schema_tmp[] = $element;
                    break;

                //ELEMENT_RECORD_NAME_TEXT
                case OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT:
                    if($this->_active_type_operation == OperationDataRecordModel::ELEMENT_TO_CHANGE_DATA){
                        $element['type'] = OperationDataRecordModel::ELEMENT_RECORD_NAME_LIST;
                        $element['value'] = null;
                    }
                    $schema_tmp[] = $element;
                    break;

                //ELEMENT_REQUIRED_FIELDS
                case OperationDataRecordModel::ELEMENT_REQUIRED_FIELDS:
                    if($this->_active_call_edit_view == self::ELEMENT_CEV_NOT_CALLED){
                        break;
                    }
                    $schema_tmp[] = $element;
                    break;

                //ELEMENT_MESSAGE
                case OperationDataRecordModel::ELEMENT_MESSAGE:
                    if($this->_active_call_edit_view == self::ELEMENT_CEV_NOT_CALLED){
                        $schema_tmp[] = array(
                            'type' => OperationDataRecordModel::ELEMENT_VALUE_BLOCK,
                            'value' => null,
                            'field_name' => null,
                            'counter' => null,
                        );
                        $schema_tmp[] = array(
                            'type' => OperationDataRecordModel::ELEMENT_LABEL_ADD_VALUE,
                        );
                        break;
                    }
                    $schema_tmp[] = $element;
                    break;

                //ELEMENT_VALUE_BLOCK
                case OperationDataRecordModel::ELEMENT_VALUE_BLOCK:
                    if($this->_active_call_edit_view == self::ELEMENT_CEV_CALL){
                        if($added_vb == false){
                            $schema_tmp[] = array(
                                'type' => OperationDataRecordModel::ELEMENT_REQUIRED_FIELDS,
                                'value' => null,
                            );
                            $schema_tmp[] = array(
                                'type' => OperationDataRecordModel::ELEMENT_MESSAGE,
                                'value' => null,
                            );
                            $added_vb = true;
                        }
                        break;
                    }

                    if($this->_active_extension_copy == false || $this->_is_changed_record_name){
                        $element['field_name'] = null;
                        $element['value'] = null;
                    }

                    $schema_tmp[] = $element;
                    break;

                //ELEMENT_LABEL_ADD_VALUE
                case OperationDataRecordModel::ELEMENT_LABEL_ADD_VALUE:
                    if($this->_active_call_edit_view == self::ELEMENT_CEV_CALL){
                        break;
                    }

                    $schema_tmp[] = $element;
                    break;

                default:
                    $schema_tmp[] = $element;
            }
        }

        $schema = $schema_tmp;

        return $this;
    }




    /**
     * updatedOperationSchema
     * @param $schema
     * @return $this
     */
    public function updatedOperationSchema(&$schema){
        foreach($schema as &$element){
            switch($element['type']){
                case self::ELEMENT_TYPE_OPERATION:
                    if(empty($this->_active_type_operation)) break;
                    $element['value'] = $this->_active_type_operation;
                    break;

                case self::ELEMENT_MODULE_NAME:
                    if(empty($this->_active_extension_copy)) break;
                    $element['value'] = $this->_active_extension_copy->copy_id;
                    break;

                case self::ELEMENT_CALL_EDIT_VIEW:
                    if(empty($this->_active_call_edit_view)) break;
                    $element['value'] = $this->_active_call_edit_view;
                    break;

            }
        }

        return $this;
    }





    /**
     * getBuildedParamsContent - собирает контент
     */
    public function getBuildedParamsContent($schema = null){

        if($schema === null){
            if(empty($this->_operations_model)) return;
            $schema = $this->_operations_model->getSchema();
        }


        if(empty($schema)) return;

        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);

        $this->_operations_model->setSchema($schema);

        $content = '';
        foreach($schema as $element_schema){
            $content.= $this->getElementHtml($element_schema);
        }

        return $content;
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
            case 'changed_type_operation':
            case 'changed_module_name':
            case 'changed_call_edit_view':
                $result = $this->cpTypeOperation($params);
                break;
            case 'changed_record_name_list':
                $this->_is_changed_record_name = true;
                $result = $this->cpTypeOperation($params);
                break;
            case 'changed_value_field_name':
                $result = $this->cpValueFieldName($params);
                break;

            case 'changed_label_add_value':
                $result = $this->cpLabelAddValue($params);
                break;
        }

        return $result;
    }



    private function cpTypeOperation($params){
        $content = null;

        $schema = $params['schema_operation'];
        if(empty($schema)) return $content;

        $content = $this->getBuildedParamsContent($schema);

        return $content;
    }


    private function cpValueFieldName($params){
        $content = null;

        $schema = $params['schema_operation'];
        if(empty($schema)) return $content;

        $this->prepareBaseEntities($schema);

        $element_schema = array(
            'type' => OperationDataRecordModel::ELEMENT_VALUE_VALUE,
            'value' => null,
            'field_name' => $params['field_name'],
            'counter' => null,
        );

        $content = $this->getElementHtml($element_schema);

        return $content;

    }



    private function cpLabelAddValue($params){
        $content = null;

        $schema = $params['schema_operation'];
        if(empty($schema)) return $content;

        $this->prepareBaseEntities($schema);

        $element_schema = array(
            'type' => OperationDataRecordModel::ELEMENT_VALUE_BLOCK,
            'value' => null,
            'field_name' => null,
            'counter' => null,
        );

        $content = $this->getElementHtml($element_schema);

        return $content;

    }




    public function getFieldType($field_name){
        $params = $this->_active_extension_copy->getFieldSchemaParams($field_name);
        if(!empty($params)){
            return $params['params']['type'];
        }
    }




    /**
     * getRecordNameIndexNameList
     */
    private static function getRecordNameIndexNameList($operations_id = null, $fields = array('index_name'), $join_table_operations = false){
        if($operations_id === null){
            $operation_list = \DataModel::getInstance()
                ->setSelect('operations_id')
                ->setFrom('{{process_operations}}')
                ->setWhere('process_id = ' . ProcessModel::getInstance()->process_id)
                ->findCol();

            if(empty($operation_list)) return array();
        } else {
            $operation_list = array($operations_id);
        }


        $data_model = \DataModel::getInstance()
            ->setSelect($fields)
            ->setFrom('{{process_operation_data_record_params}}')
            ->setWhere('{{process_operation_data_record_params}}.operations_id in ('.implode(',', $operation_list).')');

        if($join_table_operations){
            $data_model->join('process_operations', '{{process_operations}}.operations_id = {{process_operation_data_record_params}}.operations_id');
        }

        if(count($fields) == 1){
            $params_list = $data_model->findCol();
        } else {
            $params_list = $data_model->findAll();
        }

        return $params_list;
    }



    /**
     * getRecordNameIndexName - генерация Названия параметра
     */
    public static function getRecordNameIndexName($operations_id = null){
        $str = \Yii::t('ProcessModule.base', 'Parameter');
        $result = '';

        if($operations_id !== null){
            $params_list = self::getRecordNameIndexNameList($operations_id);
            if(!empty($params_list)){
                return $params_list[0];
            }
        }

        $params_list = self::getRecordNameIndexNameList();

        $lich = 1;
        if(empty($params_list)){
            $result = $str . ' ' . $lich;
        } else {
            for($lich = 1; $lich < 1000; $lich++){
                $result = $str . ' ' . $lich;
                if(!in_array($result, $params_list)){
                    break;
                }
            }
        }

        return $result;
    }




    /**
     * saveMessage
     */
    public function saveMessage(){
        // type_operation
        $from_schema = SchemaModel::getInstance()
                            ->getElementsFromSchema(
                                $this->_operations_model->getSchema(true),
                                array('only_first' => true, 'type' => self::ELEMENT_MESSAGE)
                            );

        if(empty($from_schema)){
            \DataModel::getInstance()->delete('{{process_operation_data_record_messages}}', 'operations_id=' . $this->_operations_model->operations_id);
            return $this;
        }

        $message = $from_schema['value'];

        $count = \DataModel::getInstance()
            ->setFrom('{{process_operation_data_record_messages}}')
            ->setWhere('operations_id='.$this->_operations_model->operations_id)
            ->findCount();


        if($count){
            \DataModel::getInstance()->update(
                '{{process_operation_data_record_messages}}',
                array(
                    'operations_id' => $this->_operations_model->operations_id,
                    'message' => $message),
                'operations_id='.$this->_operations_model->operations_id
                );
        } else {
            \DataModel::getInstance()->insert(
                            '{{process_operation_data_record_messages}}',
                            array(
                                'operations_id' => $this->_operations_model->operations_id,
                                'message' => $message,
                            ));
        }
        return $this;
    }





    /**
     * saveParams
     */
    public function saveParams(){
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(true), array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));

        switch($from_schema['value']){
            //ELEMENT_TO_CREATING_RECORD
            case self::ELEMENT_TO_CREATING_RECORD:
                $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(true), array('only_first' => true, 'type' => self::ELEMENT_RECORD_NAME_TEXT));
                $data = array(
                    'operations_id' => $this->_operations_model->operations_id,
                    'index_name' => $from_schema['value'],
                    'data_id' => null,
                );

                $this->updatedDataIdInSchema($data, true);
                break;


            case self::ELEMENT_TO_CHANGE_DATA:
                $this->deleteDataRecordsParams();
                break;
        }

        return $this;
    }





    /**
     * getOperationSchema
     */
    public function getOperationSchema($action_type, $return_json = true){
        $schema = array();

        switch($action_type){
            case self::SCHEMA_CLEAN :
                $schema = $this->getOperationSchemaClean();
                break;
            case self::SCHEMA_ADD_ENTITIES :
                $schema = $this->getOperationSchemaAddEntities();
                break;
        }

        if($return_json){
            return json_encode($schema);
        } else {
            return $schema;
        }
    }





    /**
     * getOperationSchemaClean
     */
    private function getOperationSchemaClean(){
        $schema = $this->_operations_model->getSchema();

        foreach($schema as &$element){
            switch($element['type']){
                //ELEMENT_MESSAGE
                case OperationDataRecordModel::ELEMENT_MESSAGE:
                    $element['value'] = null;
                    break;
                //ELEMENT_RECORD_NAME_TEXT
                case OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT:
                    $element['value'] = null;
                    break;

            }
        }
        unset($element);

        return $schema;
    }




    private function getMessage(){
        $message = \DataModel::getInstance()
            ->setSelect('message')
            ->setFrom('{{process_operation_data_record_messages}}')
            ->setWhere('operations_id='.$this->_operations_model->operations_id)
            ->findScalar();

        return $message;
    }



    /**
     * getOperationSchemaAddEntities
     */
    private function getOperationSchemaAddEntities(){
        $schema = $this->_operations_model->getSchema(true);

        foreach($schema as &$element){
            switch($element['type']){
                //ELEMENT_MESSAGE
                case OperationDataRecordModel::ELEMENT_MESSAGE:
                    $element['value'] = $this->getMessage();
                    break;
                //ELEMENT_RECORD_NAME_TEXT
                case OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT:
                    $params_list = self::getRecordNameIndexNameList($this->_operations_model->operations_id);
                    if(!empty($params_list)){
                        $element['value'] = $params_list[0];
                    } else {
                        $element['value'] = '';
                    }

                    break;
            }
        }
        unset($element);

        return $schema;
    }




    /**
     * getModuleNameCopyId
     */
    private function getModuleNameCopyId($schema = null){
        if($schema === null){
            $schema = $this->_operations_model->getSchema();
        }

        $copy_id = null;
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));

        if($from_schema['value'] == self::ELEMENT_TO_CREATING_RECORD){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_MODULE_NAME));
            if(!empty($from_schema['value'])){
                $copy_id = $from_schema['value'];
            }
        } elseif($from_schema['value'] == self::ELEMENT_TO_CHANGE_DATA) {
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_RECORD_NAME_LIST));
            if(!empty($from_schema)){
                if($this->inRecordNameList($this->_operations_model->unique_index, $from_schema['value']) == false){
                    return;
                }

                $dr_id = $from_schema['value'];
                if(!empty($dr_id)){
                    $dr_id = json_decode($dr_id, true);
                    if($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_RO){
                        return $dr_id['copy_id'];
                    } elseif($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_PARAM){
                        $dr_id = $dr_id['data_record_id'];
                    }
                }

                if(!empty($dr_id)){
                    $operations_id = \DataModel::getInstance()
                        ->setSelect('operations_id')
                        ->setFrom('{{process_operation_data_record_params}}')
                        ->setWhere('data_record_id=' . $dr_id)
                        ->findScalar();

                    if(!empty($operations_id)){
                        $operations_model = OperationsModel::model()->findByPk($operations_id);
                        if(!empty($operations_model)){
                            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_MODULE_NAME));
                            if(!empty($from_schema['value'])){
                                $copy_id = $from_schema['value'];
                            }
                        }
                    }
                }
            }
        }

        return $copy_id;
    }









    /**
     * getDataId
     */
    private function getDataId($cicle = false){
        $operations_id = null;
        $data_id = null;

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));

        if($from_schema['value'] == self::ELEMENT_TO_CREATING_RECORD){
            $operations_id = $this->_operations_model->operations_id;
            $data_id = \DataModel::getInstance()
                ->setSelect('data_id')
                ->setFrom('{{process_operation_data_record_params}}')
                ->setWhere('operations_id=' . $operations_id)
                ->findScalar();

            /*
            if(empty($data_id) && $cicle == false){
                $data_id = $this->updateDataIdFromBoObjectInstance();
            }
            */

        } elseif($from_schema['value'] == self::ELEMENT_TO_CHANGE_DATA){
            $data_record_id = null;

            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_RECORD_NAME_LIST));
            $dr_id = $from_schema['value'];

            if(!empty($dr_id)){
                $dr_id = json_decode($dr_id, true);
                if($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_RO){
                    $bo_relate_field_data = BindingObjectModel::getInstance()
                                            ->setVars(array('process_id' => ProcessModel::getInstance()->process_id))
                                            ->getRelateFieldData(false);
                    if(!empty($bo_relate_field_data)){
                        return $bo_relate_field_data['card_id'];
                    } else {
                        return;
                    }

                } elseif($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_PARAM){
                    $data_record_id = $dr_id['data_record_id'];
                }
            }

            if(!empty($data_record_id)){
                $data_id = \DataModel::getInstance()
                    ->setSelect('data_id')
                    ->setFrom('{{process_operation_data_record_params}}')
                    ->setWhere('data_record_id='.$data_record_id)
                    ->findScalar();
            }
        }

        if(empty($data_id)){
            $data_id = null;
        }

        return $data_id;
    }




    /**
     * updateDataIdFromBoObjectInstance - обновляет ИД карточки  в peration_data_record_params из связаного экземпляра обьекта
     */
    private function updateDataIdFromBoObjectInstance(){
        $result = null;

        if($this->getStatus() != OperationsModel::STATUS_ACTIVE){
            return $result;
        }

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));
        if($from_schema['value'] == self::ELEMENT_TO_CHANGE_DATA){
            return $result;
        }

        $bo_model = BindingObjectModel::getInstance()
                                ->setVars(array('process_id' => ProcessModel::getInstance()->process_id));

        $relate_data = $bo_model->getRelateDataByProcessId();
        if(!empty($relate_data)){
            $copy_id = $this->getModuleNameCopyId();
            $relate_table = $bo_model->getRelateModuleTableData();
            if($copy_id && $copy_id != $relate_table['relate_copy_id']) return $result;
            $data = array(
                'operations_id' => $this->_operations_model->operations_id,
                'data_id' => $relate_data[$relate_table['relate_field_name']],
            );

            $this->updatedDataIdInSchema($data, true);
            $result = $relate_data[$relate_table['relate_field_name']];
        }

        return $result;
    }




    /**
     * deleteDataRecordsParams - удаляет Параметр из БД
     */
    private function deleteDataRecordsParams(){
            \DataModel::getInstance()->delete('{{process_operation_data_record_params}}', 'operations_id='.$this->_operations_model->operations_id);
    }






    /**
     * updatedDataIdInParams - обновляет (добавляет) параметр в БД
     */
    private function updatedDataIdInSchema($data, $check_is_set = false){
        $operations_id = $this->_operations_model->operations_id;

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));
        if($from_schema['value'] == self::ELEMENT_TO_CHANGE_DATA){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => self::ELEMENT_RECORD_NAME_LIST));
            $dr_id = $from_schema['value'];

            if(!empty($dr_id)){
                $dr_id = json_decode($dr_id, true);
                if($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_RO){
                    return $this;
                } elseif($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_PARAM){
                    $data_record_id = $dr_id['data_record_id'];
                }
            }

            if(!empty($data_record_id)){
                $operations_id = \DataModel::getInstance()
                                        ->setSelect('operations_id')
                                        ->setFrom('{{process_operation_data_record_params}}')
                                        ->setWhere('data_record_id='.$data_record_id)
                                        ->findScalar();
            }
        }

        $check = true;

        if($check_is_set){
            $count = \DataModel::getInstance()
                            ->setFrom('{{process_operation_data_record_params}}')
                            ->setWhere('operations_id=' . $operations_id)
                            ->findCount();
            if($count == 0) $check = false;
        }


        if($check){
            \DataModel::getInstance()->update('{{process_operation_data_record_params}}', $data, 'operations_id=' . $operations_id);
        } else {
            \DataModel::getInstance()->insert('{{process_operation_data_record_params}}', $data);
        }


        //Save copy_id and card_id in Operation
        if($from_schema['value'] == self::ELEMENT_TO_CREATING_RECORD){
            $this->_operations_model->copy_id = $this->getModuleNameCopyId();
            $this->_operations_model->card_id = $data['data_id'];
            $this->_operations_model->save();
        }

        return $this;
    }


    /**
     * getBoRelateData
     * @return array
     */
    private function getBoRelateData(){
        $result = array();

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getModuleNameCopyId());
        /*
        if($extension_copy == false){
            return $result;
        }
        */

        $bo_model = BindingObjectModel::getInstance()->setVars(array('process_id' => ProcessModel::getInstance()->process_id));
        $bo_data = $bo_model->getRelateFieldData();

        if(!empty($bo_data)){
            $isset = \SchemaOperation::getInstance()->isModuleHookUp($extension_copy->getSchema(), $bo_data['copy_id']);
            if($isset){
                $result = $bo_data;
            }
        }

        return $result;
    }



    /**
     * getEditViewDataForShow - возвращает данные для EditView
     */
    public function getEditViewDataForShow($id = null, $cicle = false){
        $copy_id = null;
        $data_id = null;

        if($id === false){
            $this->updatedDataIdInSchema(array('data_id' => null));
            $copy_id = $this->getModuleNameCopyId();
            $data_id = $this->getDataId($cicle);
        } else {
            $copy_id = $this->getModuleNameCopyId();
            $data_id = $this->getDataId($cicle);
        }

        if($data_id == false && $cicle){
            return;
        }

        $edit_data = array(
            'id' => $data_id,
            'pci' => null,
            'pdi' => null,
            'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
            'relate_template' => '0',
            'template_data_id' => null,
        );

        /*
        if($copy_id == \ExtensionCopyModel::MODULE_TASKS){
            $edit_data['EditViewModel'] = array('is_bpm_operation' => '0');
        }
        */

        $is_new = (empty($data_id) ? true : false);

        $data = $this->getPreparedEditViewForNewData($is_new);
        if(array_key_exists('EditViewModel', $data)) $edit_data['EditViewModel'] = $data['EditViewModel'];
        if(array_key_exists('element_relate', $data)) $edit_data['element_relate'] = $data['element_relate'];
        if(array_key_exists('element_responsible', $data)) $edit_data['element_responsible'] = $data['element_responsible'];



        $edit_model = new \EditViewActionModel($copy_id);
        $edit_model
            ->setEditData($edit_data)
            ->createEditViewModel()
            ->setEditViewBuilder((new \Process\extensions\ElementMaster\EditViewBuilderForDr())->setOperationsModel($this)->setIsNew($is_new))
            ->checkSubscriptionAccess();

        if(empty($edit_model->_edit_model)){
            return $this->getEditViewDataForShow(false, true);
        }

        if($this->getStatus() != OperationsModel::STATUS_DONE){
            $edit_model->_edit_model->validate();
        }

        // BO
        if($is_new){
            $bo_relate_data = $this->getBoRelateData();
            if(!empty($bo_relate_data)){
                $schema = $edit_model->getExtensionCopy()->getSchema();
                \SchemaOperation::getInstance()->schemaFindAndReplace($schema, array('relate_module_copy_id' => $bo_relate_data['copy_id']), array('relate_data_id' => $bo_relate_data['card_id']));
                $edit_model->setExtensionCopySchema($schema);
            }
        }


        $edit_model->prepareHtmlData();

        $this->markHistoryIsView();

        if($edit_model->getStatus() == \EditViewActionModel::STATUS_DATA){
            return $edit_model->getHtmlData();
        } else {
            return $this->getEditViewDataForShow(false, true);
        }
    }



    /**
     * getRules - возвращает список обязательных полей для EditView
     */
    private function getRules(){
        $rules = array();

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_TYPE_OPERATION));
        if($from_schema['value'] == OperationDataRecordModel::ELEMENT_TO_CREATING_RECORD){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_REQUIRED_FIELDS));
            $fields = null;
            if(array_key_exists('value', $from_schema)){
                $fields = $from_schema['value'];
            }
            if(!empty($fields)){
                $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_MODULE_NAME));
                $extension_copy = \ExtensionCopyModel::model()->findByPk($from_schema['value']);
                $fields = explode(',', $fields);

                foreach($fields as $field_name){
                    $fields_params = $extension_copy->getFieldSchemaParams($field_name);
                    if($fields_params == false) continue;
                    if($fields_params['params']['type'] == 'relate' || $fields_params['params']['type'] == 'relate_this'){
                        $rules[] = array($field_name, 'relateCheckRequired');
                    } else {
                        $rules[] = array($field_name, 'required');
                    }
                }
            }
        }

        return $rules;
    }





    /**
     * getEditViewDataSave - сохраняет данные EditView
     */
    public function editViewSave($edit_data, $validate_data = true){
        $copy_id = $this->getModuleNameCopyId();

        $rules = array();
        if($validate_data){
            $rules = $this->getRules();
        }

        if($copy_id == \ExtensionCopyModel::MODULE_TASKS){
            $edit_data['EditViewModel']['is_bpm_operation'] = "0";
        }

        $edit_model = new \EditViewActionModel($copy_id);
        $edit_model
            ->setEditData($edit_data)
            ->setDinamicRules($rules)
            ->setValidateData($validate_data)
            ->createEditViewModel(/*true*/)
            ->setSwitchRunProcess(false)
            ->setEditViewModelMethodParam('setDeleteParticipant', false)
            ->setEditData($edit_data);

        $edit_model->save();

        // saved
        if($edit_model->getStatus() == \EditViewActionModel::STATUS_SAVE){
            // update ID
            $this->updatedDataIdInSchema(array('data_id' => $edit_model->getEditModel()->getPrimaryKey()), true);

            //STATUS_DONE
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_DONE);
                $this->_operations_model->setStatus(OperationsModel::STATUS_DONE)->save();
                $this->addActivityMessageForTask();
            }

            return $edit_model;
        } else {
            $edit_model->prepareHtmlData();

            return $edit_model;
        }
    }




    /**
     * markHistoryIsView
     */
    private function markHistoryIsView(){
        $criteria = new \CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id AND data_id=:data_id');
        $criteria->params = [
            ':copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
            ':data_id' => ProcessModel::getInstance()->process_id,
        ];
        $criteria->addInCondition('history_messages_index', [\HistoryMessagesModel::MT_OPERATION_MUST_CREATED_RECORD, \HistoryMessagesModel::MT_OPERATION_MUST_CHANGED_RECORD]);


        $history_model_list = \HistoryModel::model()
            ->with(array(
                    'historyMarkView' => array(
                        'together' => true,
                        'joinType' => 'JOIN',
                        'condition'=> '(is_view IS NULL or is_view = "0") and user_id = :user_id',
                        'params' => array(
                            ':user_id' => \WebUser::getUserId()
                        )
                    )
                )
            )
            ->findAll($criteria);

        if($history_model_list){
            foreach($history_model_list as $history_model){
                $history_model->decodeParams();
                if(!empty($history_model->params['{unique_index}']) && $history_model->params['{unique_index}'] == $this->getOperationsModel()->unique_index){
                    \History::markHistoryIsViewByPk($history_model->history_id);
                }
            }
        }
    }




    /**
     * Проверяет и возвращает уведомление для пользователя
     * @return bool
     */
    public function getUserMessage(){
        $result = false;
        if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW));
            if($from_schema['value'] == OperationDataRecordModel::ELEMENT_CEV_CALL){
                $result = $this->getMessage();
            }
        }

        return $result;
    }





    /**
     * getPreparedEditViewForNewData - Возвращает данные модуля для созранения в классе EditViewModel
     * @return array
     */
    private function getPreparedEditViewForNewData($is_new, $add_bo_relate_data = false){
        $data = array();

        //$from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_MODULE_NAME));
        //if(empty($from_schema)) return $data;
        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getModuleNameCopyId());

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('all_types' => true, 'type' => OperationDataRecordModel::ELEMENT_VALUE_BLOCK));
        if(empty($from_schema)) return $data;

        $from_schema2 = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_TYPE_OPERATION));

        $data['EditViewModel'] = array();
        if($from_schema2['value'] == OperationDataRecordModel::ELEMENT_TO_CREATING_RECORD && $is_new == true){
            $data['EditViewModel']['module_title'] = \Yii::t('messages', 'New record');
        }


        // BO
        $rd_added = false;
        if($is_new && $add_bo_relate_data){
            $bo_relate_data = $this->getBoRelateData();
            if(!empty($bo_relate_data)){
                $data['element_relate'][] = array(
                                'name' => null,
                                'relate_copy_id' => $bo_relate_data['copy_id'],
                                'id' => $bo_relate_data['card_id'],
                            );
                $rd_added = true;
            }
        }

        foreach($from_schema as $element_field){
            if(empty($extension_copy)) continue;
            $field_params = $extension_copy->getFieldSchemaParams($element_field['field_name']);
            if(empty($field_params)) continue;
            if(!array_key_exists('value', $element_field)) $element_field['value'] = null;

            switch($field_params['params']['type']){
                case 'relate':
                case 'relate_this':
                    if( $rd_added == true &&
                        $data['element_relate'][0]['relate_copy_id'] == $element_field['value']['relate_copy_id'] &&
                        $data['element_relate'][0]['id'] == $element_field['value']['relate_data_id'])
                    {
                        $data['element_relate'][0] = array(
                            'name' => null,
                            'relate_copy_id' => $element_field['value']['relate_copy_id'],
                            'id' => $element_field['value']['relate_data_id'],
                        );
                    }
                    $data['element_relate'][] = array(
                        'name' => null,
                        'relate_copy_id' => $element_field['value']['relate_copy_id'],
                        'id' => $element_field['value']['relate_data_id'],
                    );
                    break;
                case 'relate_participant' :
                    $data['element_responsible'][] = array(
                        'participant_id' => null,
                        'ug_id' => (!empty($element_field['value']['ug_id']) ? $element_field['value']['ug_id'] : null),
                        'ug_type' => (!empty($element_field['value']['ug_type']) ? $element_field['value']['ug_type'] : null),
                        'responsible' => "1",
                    );
                    break;
                case 'string';
                case 'numeric';
                case 'select';
                case 'relate_string';
                case 'display';
                case 'logical';
                case 'datetime';
                    $data['EditViewModel'][$element_field['field_name']] = $element_field['value'];
            }
        }


        // participant
        if($from_schema2['value'] == self::ELEMENT_TO_CREATING_RECORD && empty($data['element_responsible'][0]['ug_id']) && empty($data['element_responsible'][0]['ug_type'])){
            $resporsible_list = Schema::getInstance()->getOperationResponsibleList();
            if(!empty($resporsible_list)){
                $data['element_responsible'][] = array(
                    'participant_id' => null,
                    'ug_id' => $resporsible_list[$this->_operations_model->unique_index]['ug_id'],
                    'ug_type' => $resporsible_list[$this->_operations_model->unique_index]['ug_type'],
                    'responsible' => "1",
                );
            }
        }

        return $data;
    }








    /**
     * autoRunEditView - автоматически исполняет оператор, если без вызова EditView (ELEMENT_CALL_EDIT_VIEW = ELEMENT_TO_CHANGE_DATA)
     */
    private function autoRunEditView(){
        /*
        $bo_model = BindingObjectModel::getInstance()
            ->setVars(array('process_id' => ProcessModel::getInstance()->process_id));

        $is = $bo_model->isSetRelateData();
        if($is === false){
            return;
        }
        */


        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), array('only_first' => true, 'type' => OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW));
        if($from_schema['value'] != self::ELEMENT_CEV_NOT_CALLED) return;

        $id = $this->getDataId();
        $is_new = (empty($id) ? true : false);

        $data = $this->getPreparedEditViewForNewData($is_new, true);
        if(empty($data)) return;

        $edit_data = array(
            'id' => $id,
            'parent_copy_id' => null,
            'parent_data_id' => null,
            //'this_template' => 0,
            'relate_template' => 0,
            'primary_entities' => array(
                'primary_pci' => null,
                'primary_pdi' => null,
            ),
        );

        if(array_key_exists('EditViewModel', $data)) $edit_data['EditViewModel'] = $data['EditViewModel'];
        if(array_key_exists('element_relate', $data)) $edit_data['element_relate'] = $data['element_relate'];
        if(array_key_exists('element_responsible', $data)) $edit_data['element_responsible'] = $data['element_responsible'];

        $_POST = $edit_data;

        $edit_model = $this->editViewSave($edit_data, false);
        if(!empty($edit_model)){
        }

        return $this;
    }





    /**
     * getElementValueBlockTitle - подпись к полю на отображении
     * @return string
     */
    public function getElementValueBlockTitle(){
        if($this->_active_type_operation == self::ELEMENT_TO_CREATING_RECORD){
            return \Yii::t('ProcessModule.base', 'Record value');
        } elseif($this->_active_type_operation == self::ELEMENT_TO_CHANGE_DATA){
            return \Yii::t('ProcessModule.base', 'Change value');
        }
    }












    /**
     * getRecordNameList
     */
    public function getRecordNameList($start_unique_index){
        $result = array();

        $select_fields = array(
            '{{process_operation_data_record_params}}.data_record_id',
            '{{process_operation_data_record_params}}.index_name',
            '{{process_operations}}.unique_index');

        $data_record_model = OperationDataRecordModel::getRecordNameIndexNameList(null, $select_fields, true);

        if(!empty($data_record_model)){
            $data_record_list = $this->getParentsDataRecordList($start_unique_index);

            foreach($data_record_model as $value){
                if(!$this->isSetInDataRecordList($data_record_list, $value['unique_index'])) continue;
                $result['{"type":"' . self::ELEMENT_RECORD_NAME_TYPE_PARAM . '","data_record_id":"' . $value['data_record_id'] . '"}'] = $value['index_name'];
            }
        }

        $bo_relate_copy_id = BindingObjectModel::getInstance()
                                ->setVars(array('process_id' => ProcessModel::getInstance()->process_id))
                                ->getRelateCopyId();

        if(!empty($bo_relate_copy_id)){
            $result['{"type":"' . self::ELEMENT_RECORD_NAME_TYPE_RO . '","copy_id":"'.$bo_relate_copy_id .'"}'] = \ExtensionCopyModel::model()->findByPk($bo_relate_copy_id)->title;
        }

        if(!empty($result)){
            $result = array(null => '') + $result;
        } else {
            $result = array(null => '');
        }

        return $result;
    }



    public function inRecordNameList($start_unique_index, $value){
        if($value == false){
            return false;
        }

        $record_name_list = $this->getRecordNameList($start_unique_index);

        if($record_name_list == false){
            return false;
        }

        foreach($record_name_list as $key => $v){
            if($key == $value){
                return true;
            }
        }

        return false;
    }




    /**
     * getChildrenDataRecordList - Возвращает  список предшествующих операторов Запись данных
     */
    private function getParentsDataRecordList($start_unique_index, $only_first = false){
        $this->_parents_data_record_list = array();
        $this->findParentDataRecord($start_unique_index, $only_first);

        asort($this->_parents_data_record_list);

        return $this->_parents_data_record_list;
    }




    /**
     * findParentDataRecord - Ищет операторы Запись данных
     */
    private function findParentDataRecord($unique_index, $only_first = false){
        $parent_ui_list = ArrowModel::getInstance()->getUniqueIndexParent($unique_index);

        if(!empty($parent_ui_list)){
            foreach($parent_ui_list as $unique_index){
                $schema = SchemaModel::getInstance()->getSchema();
                $element = SchemaModel::getInstance()->getElementsFromSchema(
                    $schema,
                    array(
                        'type' => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
                        'unique_index' => $unique_index,
                    ));
                if(!empty($element) && $element[$unique_index]['name'] == \Process\models\OperationsModel::ELEMENT_DATA_RECORD){
                    $this->_parents_data_record_list[$element[$unique_index]['unique_index']] = $element[$unique_index];
                }

                if(!empty($element) && $element[$unique_index]['name'] != \Process\models\OperationsModel::ELEMENT_BEGIN){
                    if($only_first) break;
                    $this->findParentDataRecord($element[$unique_index]['unique_index']);
                }
            }
        }
    }


    /**
     * isSetInDataRecordList
     */
    private function isSetInDataRecordList($data_record_list, $unique_index){
        return key_exists($unique_index, $data_record_list);
    }









    /**
     * cloneParams
     */
    private function cloneParams($operations_model, $process_id_old){
        $query = "
            SELECT t1.*
            FROM {{process_operation_data_record_params}} AS t1
            LEFT JOIN {{process_operations}} AS t2 ON t1.operations_id = t2.operations_id
            WHERE t2.process_id = $process_id_old AND t2.unique_index = '" . $operations_model['unique_index'] . "' AND t2.element_name = '". OperationsModel::ELEMENT_DATA_RECORD ."'";

        $data_record_params = \DataModel::getInstance()->setText($query)->findAll();
        if(empty($data_record_params)) return;

        foreach($data_record_params as $param_data){
            $data_id = $param_data['data_id'];
            if(!empty($param_data['data_id'])){
                $data_id = $this->cloneModuleData($param_data['data_id']);
            }

            if(empty($data_id)) $data_id = 'null';
            $query = '(' . $operations_model->operations_id . ', "' . $param_data['index_name'] . '", ' . $data_id . ')';
            $query = 'INSERT into {{process_operation_data_record_params}} (operations_id,index_name,data_id) VALUES ' . $query;

            $data_model = new \DataModel();
            $data_model->setText($query)->execute();
            $last_id = $data_model->setText('SELECT LAST_INSERT_ID();')->findScalar();
            self::$clone_params_replase_list[$param_data['data_record_id']] = $last_id;
        }

    }



    /**
     * cloneMessages
     */
    private function cloneMessages($operations_model, $process_id_old){
        $query = "
            SELECT t1.*
            FROM {{process_operation_data_record_messages}} AS t1
            LEFT JOIN {{process_operations}} AS t2 ON t1.operations_id = t2.operations_id
            WHERE t2.process_id = $process_id_old AND t2.unique_index = '" . $operations_model['unique_index'] . "' AND t2.element_name = '". OperationsModel::ELEMENT_DATA_RECORD ."'";

        $operations = \DataModel::getInstance()->setText($query)->findAll();
        if(empty($operations)) return;

        $query = array();
        foreach($operations as $operation){
            $query[] = '(' . $operations_model->operations_id . ', "' . $operation['message'] . '")';
        }

        if(!empty($query)){
            $query = 'INSERT into {{process_operation_data_record_messages}} (operations_id, message) VALUES ' . implode(',', $query);
            \DataModel::getInstance()->setText($query)->execute();
        }
    }




    /**
     * cloneModuleData
     */
    private function cloneModuleData($data_id){
        $make_loggin = false;
        if($this->_operations_model->getStatus() != OperationsModel::STATUS_UNACTIVE) $make_loggin = true;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->getModuleNameCopyId());
        $copy_result = \EditViewCopyModel::getInstance($extension_copy)
                                ->setMakeLoggin($make_loggin)
                                ->copy(array($data_id), $extension_copy, false, null)
                                ->getResult();

        $data_id = null;
        if(!empty($copy_result['id'][0])){
            $data_id = $copy_result['id'][0];
        }

        return $data_id;
    }






    /**
     * updateRecordNameListIdInSchema - заменяем ИД значения параметра на новое
     * @param $replase_list
     */
    public function updateRecordNameListIdInSchema($operations_model){
            $operations_model->refresh();
            $operations_model->refreshMetaData();
            $schema = $operations_model->getSchema(true);
            $changed = false;

            foreach($schema as &$element){
                if($element['type'] != OperationDataRecordModel::ELEMENT_RECORD_NAME_LIST) continue;

                if(empty($element['value'])) continue;

                $params = json_decode($element['value'], true);
                if($params['type'] == self::ELEMENT_RECORD_NAME_TYPE_PARAM){
                    if(!array_key_exists($params['data_record_id'], self::$clone_params_replase_list)) continue;
                    $params['data_record_id'] = self::$clone_params_replase_list[$params['data_record_id']];
                    $element['value'] = json_encode($params);
                    $changed = true;
                }

                break;
            }
            if($changed == true){
                $operations_model->setSchema($schema);
                $operations_model->save();
            }
    }








    /**
     * thereIsSettedBindingObject - проверяет схему оператора и возвращает результат сравнения связанного объекта
     * @param string|integer $compare_value - величина для сравнения
     */
    /*
    public static function thereIsSettedBindingObject($schema, $compare_value){
        if(is_string($schema)){
            $schema = json_decode($schema, true);
        }

        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_TYPE_OPERATION));

        if($from_schema['value'] == self::ELEMENT_TO_CHANGE_DATA){
            $from_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_RECORD_NAME_LIST));
            if(!empty($from_schema)){
                $dr_id = $from_schema['value'];
                if(!empty($dr_id)){
                    $dr_id = json_decode($dr_id, true);
                    if($dr_id['type'] == self::ELEMENT_RECORD_NAME_TYPE_RO && $dr_id['copy_id'] == $compare_value){
                        return true;
                    }
                }
            }
        }

        return false;
    }
    */
















    /*************************************
    * ACTIONS
    *************************************/





    /**
     * actionGetSchemaPrepared
     */
    public function actionGetSchemaPrepared(){
        return $this->getOperationSchema(self::SCHEMA_ADD_ENTITIES, false);
    }





    /**
     * actionBeforeSave
     */
    public function actionBeforeSave(){
        $this
            ->saveMessage()
            ->saveParams();

        return $this;
    }



    /**
     * actionBeforeSaveGetSchema
     */
    public function actionBeforeSaveGetSchema(){
        return $this->getOperationSchema(self::SCHEMA_CLEAN);
    }




    /**
     * actionAddNewOperationByDefault
     */
    public function actionAddNewOperationByDefault($vars = null){
        $this->saveMessage();
        $this->saveParams();
        $schema = $this->getOperationSchema(OperationDataRecordModel::SCHEMA_CLEAN);
        $this->_operations_model->setSchema($schema, false)->save();

        return $this;
    }





    /**
     * actionCloneDataAfterSave - клонирование параметров всех операторов в процессе
     */
    public function actionCloneDataAfterSave($vars = null){
        self::$clone_params_replase_list = array();

        $operations_models = OperationsModel::model()->findAll(array(
            'condition' => 'process_id=:process_id AND element_name=:element_name',
            'params' => array(
                ':process_id' => $vars['process_id_new'],
                ':element_name' => OperationsModel::ELEMENT_DATA_RECORD,
            ),
        ));

        if(empty($operations_models)) return $this;

        foreach($operations_models as $operations_model){
            $this->_operations_model = $operations_model;
            $this->cloneParams($operations_model, $vars['process_id_old']);
            $this->cloneMessages($operations_model, $vars['process_id_old']);

            $this->updateRecordNameListIdInSchema($operations_model);
        }

        return $this;
    }



    /**
     * actionPrepareDataForNewOperation
     */
    public function actionPrepareDataForNewOperation(){
        $schema = $this->_operations_model->getSchema(true);

        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);
        $this->updatedOperationSchema($schema);

        $this->_operations_model->setSchema($schema);

        return $this;
    }






/**
 * ACTIONS end
 */





}









