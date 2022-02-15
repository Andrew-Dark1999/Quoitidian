<?php
/**
 * NotificationUnisenderModel - модель для сервиса рассылки - UniSender
 *
 * @author Alex R.
 */

namespace Process\models\NotificationService;


use Process\models\OperationDataRecordModel;
use Process\models\ProcessModel;

class NotificationUnisenderModel extends \Process\components\OperationModel{

    const ELEMENT_LETTER_SUBJECT    = 'letter_subject';     //Тема письма
    const ELEMENT_SENDER_NAME       = 'sender_name';        //Название отправителя
    const ELEMENT_SENDER_ADDRESS    = 'sender_address';     //Адрес отправителя
    const ELEMENT_RECIPIENT_TYPE    = 'recipient_type';     //Тип получателя
    const ELEMENT_OBJECT_NAME       = 'object_name';     //Название параметра
    const ELEMENT_MODULE_NAME       = 'module_name';     //Название модуля
    const ELEMENT_FIELD_NAME        = 'field_name';      //Фильтр - Название поля
    const ELEMENT_FILTER_FIELD_NAME = 'filter_field_name';          //Фильтр
    const ELEMENT_MESSAGE_TEXT      = 'message_text';    //Текст сообщения
    const ELEMENT_MESSAGE_TEMPLATE  = 'message_template';//Шаблоны сообщений (из сервиса)
    const ELEMENT_LABEL_ADD_FILTER  = 'label_add_filter'; // ссылка добавить фильтр

    const ELEMENT_FFN_CONDITION         = 'condition';   // условие фильтра
    const ELEMENT_FFN_CONDITION_VALUE   = 'condition_value';  // значение условия фильтра


    const RECEPIENT_TYPE_ONE        = 'recipient_type_one';     //Тип получателя - один
    const RECEPIENT_TYPE_LIST       = 'recipient_type_list';    //Тип получателя - список



    public static $filter_fn_index = 0;
    public static $active_filter_fn_index = 0;

    private $_operation_nf_model = null; //OperationNotificationFactoryModel
    private $_action;


    private $_active_recipient_type = null;
    private $_active_object_name = null;
    private $_active_extension_copy = array();      // список модулей
    private $_active_field_name = null;             // название первого активного поля. Используется только при подготовке схемы

    private $cache_object_name_list = null;
    private $cache_module_name_list = null;
    private $cache_module_fields_list = null;

    private $_schema;

    private $_service_model;


    private $_type_messages = array(
        array(
            'name' => NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL,
            'title' => 'Email',
            'enabled' => true,
        ),
        array(
            'name' => NotificationUnisenderSendModel::TYPE_MESSAGE_SMS,
            'title' => 'SMS',
            'enabled' => true,
        ),
    );






    public function init(){
        $source_name = null;
        $service_model = null;

        foreach(['email', 'sms'] as $source_name){
            $service_model = (new \PluginsModel())->getServiceModel($source_name, 'unisender');
            if($service_model){
                break;
            }
        }

        if($service_model == false){
            return $this;;
        }

        $this->_service_model = $service_model;

        return $this;
    }


    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Notification');
    }



    public function getServiceName(){
        return $this->_service_model->getName();
    }


    public function getServiceTitle(){
        return $this->_service_model->getTitle();
    }


    public function getView(){
        return 'unisender';
    }


    private function getTypeMessageTitle($type_message){
        foreach($this->_type_messages as $value){
            if($value['name'] == $type_message){
                return $value['title'];
            }
        }
    }


    public function getEnabled(){
        if($this->_service_model){
            return true;
        }

        return false;
    }




    public function setValidateElements($validate){
        $this->_validate_elements = $validate;
        return $this;
    }



    /**
     * getLastExtensionCopy
     */
    private function getLastExtensionCopy(){
        return $this->_active_extension_copy[count($this->_active_extension_copy) - 1];
    }




    /**
     * send - проверка выполнения, установка статуса
     */
    public function send(){
        $nus_model = (new NotificationUnisenderSendModel($this))->run();

        $result = $nus_model->getResult();

        return $result;
    }


    public function getSchema(){
        return $this->_schema;
    }



    public function setOperationNfModel($operation_nf_model){
        $this->_operation_nf_model = $operation_nf_model;
        return $this;
    }


    public function getOperationNfModel(){
        return $this->_operation_nf_model;
    }


    private function getRecepientTypeList(){
        return array(
            self::RECEPIENT_TYPE_ONE => \Yii::t('ProcessModule.base', 'One'),
            self::RECEPIENT_TYPE_LIST => \Yii::t('ProcessModule.base', 'List'),
        );

    }


    public static function getElementList(){
        return array(
            //self::ELEMENT_LETTER_SUBJECT,
            //self::ELEMENT_SENDER_ADDRESS,
            self::ELEMENT_SENDER_NAME,
            self::ELEMENT_RECIPIENT_TYPE,
            self::ELEMENT_OBJECT_NAME,
            self::ELEMENT_MODULE_NAME,
            self::ELEMENT_FIELD_NAME,
            self::ELEMENT_FILTER_FIELD_NAME,
            self::ELEMENT_MESSAGE_TEMPLATE,
            self::ELEMENT_MESSAGE_TEXT,
        );
    }


    /**
     * getObjectNameList
     */
    private function getObjectNameList($refresh = true){
        if($refresh == true || $this->cache_object_name_list === null){
            $this->cache_object_name_list = \Process\models\OperationDataRecordModel::getInstance()->getRecordNameList($this->_operations_model->unique_index);
        }

        return $this->cache_object_name_list;
    }


    /**
     * getModuleNamelist
     */
    private function getModuleNamelist($refresh = true, $base_copy_id = null){
        $result = array();

        if($result == false && !empty($this->cache_module_name_list)){
            return $this->cache_module_name_list;
        }

        if($base_copy_id === null){
            return (!empty($this->cache_module_name_list) ? $this->cache_module_name_list : array());
        }

        if(empty($this->cache_object_name_list)) return $result;

        if($refresh == true || $this->cache_module_name_list === null){
            $this->cache_module_name_list = array(null => '');

            // base copy_id
            $this->cache_module_name_list[$base_copy_id] = \ExtensionCopyModel::model()->findByPk($base_copy_id)->title;


            $relate_table = \ModuleTablesModel::model()->findAll(array(
                'condition' => 'copy_id=:copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params' => array(
                    ':copy_id' => $base_copy_id,
                )));

            $id_list = \CHtml::listData($relate_table, 'id', 'relate_copy_id');

            if(!empty($relate_table)){
                // related copy_id
                $extension_models = \ExtensionCopyModel::model()
                                            ->modulesActive()
                                            ->setAccess()
                                            ->setScopesWithOutId(array(\ExtensionCopyModel::MODULE_PROCESS, \ExtensionCopyModel::MODULE_REPORTS))
                                            ->findAll(array(
                                                'condition' => 'copy_id in ('.implode(',', $id_list).')',
                                                'order' => 'title',
                                            ));

                if(!empty($extension_models))
                foreach($extension_models as $module){
                    $this->cache_module_name_list[$module['copy_id']] = $module['title'];
                }
            }
        }

        return $this->cache_module_name_list;
    }





    /**
     * getFieldNamelist
     */
    private function getFieldNamelist($refresh = true){
        $result = array();

        if($refresh == false && !empty($this->cache_module_fields_list)){
            return $this->cache_module_fields_list;
        }

        if(empty($this->_active_extension_copy)){
            $this->cache_module_fields_list = $result;
            return $result;
        }


        $schema_parse = $this->getLastExtensionCopy()->getSchemaParse();

        $params = \SchemaConcatFields::getInstance()
                            ->setSchema($schema_parse['elements'])
                            ->setWithoutFieldsForListViewGroup($this->getLastExtensionCopy()->getModule(false)->getModuleName())
                            ->parsing()
                            ->primaryOnFirstPlace()
                            ->prepareWithoutCompositeFields()
                            ->getResult();


        $exceptions_field_types = array();
        if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL){
            $exceptions_field_types = array('string');
        } else if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
            $exceptions_field_types = array('string', 'numeric');
        }

        if(!empty($params['header'])){
            foreach ($params['header'] as &$fields) {
                foreach(explode(',', $fields['name']) as $field_name){
                    if(!empty($exceptions_field_types) && !in_array($params['params'][$field_name]['type'], $exceptions_field_types)) continue;

                    $result[$field_name] = $fields['title'];
                }
            }
        }

        if(!empty($result)){
            $result = array_merge(array(null => ''), $result);
        }

        $this->cache_module_fields_list = $result;

        return $result;
    }






    /**
     * getMessageTemplatesList
     */
    private function getMessageTemplatesList($refresh = true, $vars = null){
        $result = array(null => '');

        $templates = (new NotificationUnisenderSendModel($this))->getTemplates();

        if($vars !== null){
            if(!empty($templates)){
                if(!in_array($vars['value'], $templates) && $this->_operations_model->getStatus() == \Process\models\OperationsModel::STATUS_DONE){
                    $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_schema, array('only_first' => true, 'type' => self::ELEMENT_MESSAGE_TEMPLATE));
                    if(!empty($from_schema['title'])){
                        $templates[$vars['value']] = $from_schema['title'];
                    }
                }
            }
        }

        if(!empty($templates)){
            $result = $result + $templates;
        }

        return $result;
    }




    public function getOptionList($element_name, $refresh = false, $vars = null){
        switch($element_name){
            case self::ELEMENT_RECIPIENT_TYPE:
                return $this->getRecepientTypeList();
            case self::ELEMENT_OBJECT_NAME:
                return $this->getObjectNameList($refresh);
            case self::ELEMENT_MODULE_NAME:
                return $this->getModuleNamelist($refresh);
            case self::ELEMENT_FIELD_NAME:
            case self::ELEMENT_FILTER_FIELD_NAME:
                return $this->getFieldNamelist($refresh);
            case self::ELEMENT_MESSAGE_TEMPLATE:
                return $this->getMessageTemplatesList($refresh, $vars);
        }
    }


    /**
     * getFirstValue - возвращает первое значение из списка
     * $type_data - key|value
     */
    private function getFirstValue($element_name, $type_data = 'value'){
        $list = $this->getOptionList($element_name);
        if(empty($list)) return;

        if($type_data == 'key'){
            return array_keys($list)[0];
        }
        if($type_data == 'value'){
            return $list[array_keys($list)[0]];
        }
    }


    public function getElementsLabelTitle($element_name){
        switch($element_name){
            case self::ELEMENT_LETTER_SUBJECT:
                return \Yii::t('ProcessModule.base', 'Letter subject');
            case self::ELEMENT_SENDER_ADDRESS:
                return \Yii::t('ProcessModule.base', 'Sender address');
            case self::ELEMENT_SENDER_NAME:
                return \Yii::t('ProcessModule.base', 'Sender');
            case self::ELEMENT_RECIPIENT_TYPE:
                return \Yii::t('ProcessModule.base', 'Recipient type');
            case self::ELEMENT_OBJECT_NAME:
                return \Yii::t('ProcessModule.base', 'Object name');
            case self::ELEMENT_MODULE_NAME:
                return \Yii::t('ProcessModule.base', 'Module name');
            case self::ELEMENT_FIELD_NAME:
                return \Yii::t('ProcessModule.base', 'Field name');
            case self::ELEMENT_FILTER_FIELD_NAME:
                return \Yii::t('ProcessModule.base', 'Filter');
            case self::ELEMENT_MESSAGE_TEMPLATE:
                return \Yii::t('ProcessModule.base', 'Message template');
            case self::ELEMENT_MESSAGE_TEXT:
                if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                    return \Yii::t('ProcessModule.base', 'Message text');
                }
        }
    }




    /**
     *  getOtherVars - параметры оператора для формирования отображения
     */
    public function getOtherVars(){
        return array(
            'service_name' => $this->getServiceName(),
            'view' => $this->getView(),
        );
    }



    /**
     * getTypeMessagesList - возвращает список типов сообщений (email, sms)
     */
    public function getTypeMessagesList($type_message_name = null){
        $result = array();

        if(!empty($this->_type_messages)){

            foreach($this->_type_messages as $type_message){
                if($type_message['enabled'] == false) continue;
                if($type_message_name !== null){
                    if($type_message['name'] == $type_message_name){
                        return array($type_message['name'] => \Yii::t('ProcessModule.base', $type_message['title']));
                    } else {
                        continue;
                    }
                }
                $result[$type_message['name']] = \Yii::t('ProcessModule.base', $type_message['title']);
            }
        }

        return $result;
    }



    /**
     * isTypeMessage - проверяет наличие сервиса
     */
    public function isTypeMessage($type_message_name){
        $tm = $this->getTypeMessagesList();

        if(empty($tm)) return false;

        foreach($tm as $name => $item){
            if($name == $type_message_name)
                return true;
        }

        if(empty($tm)) return false;
    }



    /**
     * getFieldNameValue
     */
    private function getFieldNameValue(){
        if(empty($this->_active_field_name)) return;
        return $this->_active_field_name;
    }



    /**
     * getFieldType - Возвращает тип поля
     * @param $field_name - название поля
     */
    public function getFieldType($field_name){
        if(empty($this->_active_extension_copy)) return;
        if(empty($field_name)) return;

        $schema_params = $this->getLastExtensionCopy()->getFieldSchemaParams($field_name);
        if(!empty($schema_params)) return $schema_params['params']['type'];
    }




    /**
     * Возвращает список условий для опеределенного типа
     * @param $field_type - тип поля
     */
    public function getValueConditionList($field_name){
        $result = array();

        if(empty($field_name)) return $result;

        if(empty($this->_active_extension_copy)) return $result;

        $field_type = $this->getFieldType($field_name);
        if($field_type === null){
            return $result;
        }

        $filter_list = null;

        switch($field_type){
            case \Fields::MFT_STRING :
                $filter_list = \FilterMap::getFilterList(\FilterMap::GROUP_1, null);
                break;
            case \Fields::MFT_NUMERIC :
                $filter_list = \FilterMap::getFilterList(\FilterMap::GROUP_2, null);
                break;
        }

        if(!empty($filter_list)){
            foreach($filter_list as $key => $value){
                $result[$key] = \Yii::t('filters', $value['title']);
            }
        }

        return $result;
    }





    /**
     * getBaseCopyId - возвращает copy_id связаного с параметром модуля
     */
    private function getBaseCopyId(){
        $copy_id = null;

        if(!empty($this->_active_object_name)){
            $dr_id = $this->_active_object_name;

            if($dr_id['type'] == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO){
                return $dr_id['copy_id'];
            } elseif($dr_id['type'] == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM){
                $dr_id = $dr_id['data_record_id'];
            }
            $operations_id = \DataModel::getInstance()
                ->setSelect('operations_id')
                ->setFrom('{{process_operation_data_record_params}}')
                ->setWhere('data_record_id=' . $dr_id)
                ->findScalar();

            if(!empty($operations_id)){
                $operations_model = \Process\models\OperationsModel::model()->findByPk($operations_id);
                if(!empty($operations_model)){
                    $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($operations_model->getSchema(), array('only_first' => true, 'type' => \Process\models\OperationDataRecordModel::ELEMENT_MODULE_NAME));
                    if(!empty($from_schema['value'])){
                        $copy_id = $from_schema['value'];
                    }
                }
            }
        }

        return $copy_id;
    }






    /**
     * getDataId -
     */
    private function getDataId(){
        $operations_id = null;
        $data_id = null;
        $data_record_id = null;

        if(!empty($this->_active_object_name)){
            $dr_id = $this->_active_object_name;

            if($dr_id['type'] == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO){
                $bo_relate_field_data = \Process\models\BindingObjectModel::getInstance()
                                                                ->setVars(array('process_id' => \Process\models\ProcessModel::getInstance()->process_id))
                                                                ->getRelateFieldData(false);
                if(!empty($bo_relate_field_data)){
                    return $bo_relate_field_data['card_id'];
                } else{
                    return;
                }
            } elseif($dr_id['type'] == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_PARAM){
                $data_record_id = $dr_id['data_record_id'];
            }

            if(!empty($data_record_id)){
                $data_id = \DataModel::getInstance()
                    ->setSelect('data_id')
                    ->setFrom('{{process_operation_data_record_params}}')
                    ->setWhere('data_record_id=' . $data_record_id)
                    ->findScalar();
            }
        }

        if(empty($data_id)){
            $data_id = null;
        }

        return $data_id;
    }







    /**
     * changeParamsContent - изменение элемента
     */
    public function changeParamsContent($action, $schema){
        $result = '';

        $this->_action = $action;

        switch($action){
            case 'changed_' . self::ELEMENT_LABEL_ADD_FILTER:
            case 'changed_' . self::ELEMENT_OBJECT_NAME:
            case 'changed_' . self::ELEMENT_MODULE_NAME:
            default :
                $result = $this->getBuildedParamsContent($schema);
        }

        return $result;
    }





    public function prepareAll($schema = null){
        if(empty($schema)) $schema = $this->getDefaultSchema();

        // подготовка схемы
        $this->prepareBaseEntities($schema);
        // обновление параметров схемы
        $this->refreshSchema($schema);

        $this->_schema = $schema;

        if($this->_validate_elements){
            $this->validate();
        }

        return $this;
    }





    /**
     * getBuildedParamsContent - собирает контент
     */
    public function getBuildedParamsContent($schema = null){
        $this->prepareAll($schema);

        // собираем контент
        $content = '';
        foreach($this->_schema as $element_schema){
            $content .= $this->getElementHtml($element_schema);
        }

        return $content;
    }







    /**
     * prepareEntities - подготовка базовых параметров для формирования параметров
     */
    private function prepareBaseEntities($schema){
        // Тип получателя
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_RECIPIENT_TYPE));
        if(!empty($from_schema['value'])){
            $this->_active_recipient_type = $from_schema['value'];
        } else {
            $this->_active_recipient_type = $this->getFirstValue(self::ELEMENT_RECIPIENT_TYPE, 'key');
        }

        //Название объекта
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_OBJECT_NAME));
        if(!empty($from_schema['value'])){
            $objects_list =  $this->getObjectNameList();
            if(!empty($objects_list)){
                foreach($objects_list as $key => $value){
                    if($key == $from_schema['value']){
                        $this->_active_object_name = json_decode($from_schema['value'], true);
                        break;
                    }
                }
            }
        }


        // активный модуль из Названия объекта
        $main_copy_id = $this->getBaseCopyId($schema);

        if(!empty($main_copy_id)){
            $modules_list = $this->getModuleNamelist(true, $main_copy_id);

            // Модуль
            $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_MODULE_NAME));
            if(!empty($from_schema['value'])){
                if(!empty($modules_list)){
                    foreach($modules_list as $key => $value){
                        if($key == $from_schema['value']){
                            $this->_active_extension_copy[] = \ExtensionCopyModel::model()->findByPk($main_copy_id);
                            if($main_copy_id != $from_schema['value']){
                                $this->_active_extension_copy[] = \ExtensionCopyModel::model()->findByPk($from_schema['value']);
                            }
                            break;
                        }
                    }
                }
            }
        }

        // Название поля
        $fields_list = $this->getFieldNamelist(true);
        if(!empty($fields_list)){
            $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_FIELD_NAME));
            if(!empty($from_schema['value'])){
                if(!empty($fields_list)){
                    foreach($fields_list as $key => $value){
                        if($key == $from_schema['value']){
                            $this->_active_field_name = $from_schema['value'];
                            break;
                        }
                    }
                }
            } else {
                $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_FILTER_FIELD_NAME));
                if(!empty($from_schema['value'])){
                    if(!empty($fields_list)){
                        foreach($fields_list as $key => $value){
                            if($key == $from_schema['value']){
                                $this->_active_field_name = $from_schema['value'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }



    /**
     * refreshSchema - обновляет (изменяет) элементы схемы
     */
    private function refreshSchema(&$schema){
        if(empty($schema)) return $this;

        $schema_tmp = array();
        $added_rt = false;
        $filter_index = 1;

        foreach($schema as $element){
            switch($element['type']){
                case self::ELEMENT_SENDER_NAME:
                    if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                        $schema_tmp[] = $element;
                        $added_rt = true;
                    }

                    break;

                case self::ELEMENT_RECIPIENT_TYPE:
                    if($added_rt == false && $this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                        $schema_tmp[] = array(
                            'type' => self::ELEMENT_SENDER_NAME,
                            'value' => null,
                        );
                    }

                    $schema_tmp[] = $element;
                    break;

                case self::ELEMENT_FIELD_NAME:
                    if($this->_active_recipient_type == self::RECEPIENT_TYPE_ONE){
                        $schema_tmp[] = $element;
                    } elseif($this->_active_recipient_type == self::RECEPIENT_TYPE_LIST){
                        $schema_e = $this->getDefaultSchemaFFN();
                        if($filter_index === 1) $schema_e['value'] = $this->getFieldNameValue();
                        $schema_tmp[] = $schema_e;
                        $filter_index++;
                    }
                    break;

                case self::ELEMENT_FILTER_FIELD_NAME:
                    if($this->_active_recipient_type == self::RECEPIENT_TYPE_LIST){
                        $schema_tmp[] = $element;
                    } elseif($this->_active_recipient_type == self::RECEPIENT_TYPE_ONE){
                        if($filter_index > 1) continue;
                        $schema_e = $this->getDefaultSchemaFN();
                        if($filter_index === 1) $schema_e['value'] = $this->getFieldNameValue();
                        $schema_tmp[] = $schema_e;
                    }
                    $filter_index++;

                    break;

                case self::ELEMENT_MESSAGE_TEMPLATE :
                    if($this->_active_recipient_type == self::RECEPIENT_TYPE_LIST){
                        if($this->_action == 'changed_' . self::ELEMENT_LABEL_ADD_FILTER){
                            $this->_filter_remove_panel = true;
                            $schema_e = $this->getDefaultSchemaFFN();
                            $schema_tmp[] = $schema_e;
                        }

                        $schema_tmp[] = array(
                            'type' => self::ELEMENT_LABEL_ADD_FILTER,
                        );
                    }

                    if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                        $schema_tmp[] = array(
                            'type' => self::ELEMENT_MESSAGE_TEXT,
                            'value' => null,
                        );
                        break;
                    }
                    $schema_tmp[] = $element;

                    break;

                case self::ELEMENT_MESSAGE_TEXT :
                    if($this->_active_recipient_type == self::RECEPIENT_TYPE_LIST){
                        if($this->_action == 'changed_' . self::ELEMENT_LABEL_ADD_FILTER){
                            $schema_e = $this->getDefaultSchemaFFN();
                            $this->_filter_remove_panel = true;
                            $schema_tmp[] = $schema_e;
                        }

                        $schema_tmp[] = array(
                            'type' => self::ELEMENT_LABEL_ADD_FILTER,
                        );
                    }

                    if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL){
                        $schema_tmp[] = array(
                            'type' => self::ELEMENT_MESSAGE_TEMPLATE,
                            'value' => null,
                        );
                        break;
                    }
                    $schema_tmp[] = $element;

                    break;

                default :
                    $schema_tmp[] = $element;

            }
        }

        $schema = $schema_tmp;

        return $this;
    }



    /**
     * getDefaultSchema - возвращает схему по умолчанию
     */
    public function getDefaultSchema(){
        if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL){
            return $this->getDefaultSchemaEmail();
        } else if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
            return $this->getDefaultSchemaSms();
        }
    }



    /**
     * getDefaultSchemaEmail - Notification UniSender Email
     */
    private function getDefaultSchemaEmail(){
        $schema = array(
            array(
                'type' => self::ELEMENT_RECIPIENT_TYPE,
                'value' => self::RECEPIENT_TYPE_ONE,
            ),
            array(
                'type' => self::ELEMENT_OBJECT_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_MODULE_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_FIELD_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_MESSAGE_TEMPLATE,
                'value' => null,
            ),
        );

        return $schema;
    }







    /**
     * getDefaultSchemaSms - Notification UniSender Sms
     */
    private function getDefaultSchemaSms(){
        $schema = array(
            array(
                'type' => self::ELEMENT_SENDER_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_RECIPIENT_TYPE,
                'value' => self::RECEPIENT_TYPE_ONE,
            ),
            array(
                'type' => self::ELEMENT_OBJECT_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_MODULE_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_FIELD_NAME,
                'value' => null,
            ),
            array(
                'type' => self::ELEMENT_MESSAGE_TEXT,
                'value' => null,
            ),
        );

        return $schema;
    }




    private function getDefaultSchemaFN(){
        return array(
            'type' => self::ELEMENT_FIELD_NAME,
            'value' => null,
        );
    }




    private function getDefaultSchemaFFN(){
        return array(
            'type' => self::ELEMENT_FILTER_FIELD_NAME,
            'value' => null,
            self::ELEMENT_FFN_CONDITION => null,
            self::ELEMENT_FFN_CONDITION_VALUE => null,
        );
    }






    /**
     * getAddresses - возвращает список адресов для рассылки
     */
    public function getAddressList(){
        $result = array();

        $params = $this->getAddressListParams($this->_schema);
        if(empty($params)) return $result;

        $result = $this->getAddressListData($params);
        if(empty($result)) return;

        return $result;
    }



    /**
     * getAddressListParams - подготавлявает и возвращает параметры за формирования запроса в БД
     */
    private function getAddressListParams(){
        $result = array();

        if($this->_active_recipient_type == self::RECEPIENT_TYPE_ONE){
            $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($this->_schema, array('only_first' => true, 'type' => self::ELEMENT_FIELD_NAME));
            $result[] = array(
                'field_name' => $from_schema['value'],
                'filter_params' => false,
            );
        } else if($this->_active_recipient_type == self::RECEPIENT_TYPE_LIST){
            foreach($this->_schema as $element){
                if($element['type'] == self::ELEMENT_FILTER_FIELD_NAME){
                    $result[] = array(
                        'field_name' => $element['value'],
                        'filter_params' => array('name' => $element['value'], 'condition' => $element[self::ELEMENT_FFN_CONDITION], 'condition_value' => array($element[self::ELEMENT_FFN_CONDITION_VALUE])),
                    );
                }
            }

        }

        return $result;
    }


    /**
     * getAddressListData - Подготавливает и возвращает данные по адресам
     */
    private function getAddressListData($params){
        $data_models = array();
        if(empty($this->_active_extension_copy)) return;

        $extension_copy = $this->getLastExtensionCopy();

        // собираем запросы
        foreach($params as $param){
            $data_model = \DataModel::getInstance();
            $data_model
                ->setFrom($this->_active_extension_copy[0]->getTableName())
                ->setSelect($extension_copy->getTableName() . '.' . $param['field_name'] . ' as address_data');



            if(count($this->_active_extension_copy) > 1){
                $relate_table = \ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                                        'params' => array(
                                            ':copy_id' => $this->_active_extension_copy[0]->copy_id,
                                            ':relate_copy_id' => $extension_copy->copy_id,
                                    )));

                $data_model->join($relate_table['table_name'], $this->_active_extension_copy[0]->getPkFieldName(true) . '={{' . $relate_table['table_name'] . '}}.' . $relate_table['parent_field_name']);
                $data_model->join($extension_copy->getTableName(null, false), '{{' . $relate_table['table_name'] . '}}.' . $relate_table['relate_field_name'] . '=' . $extension_copy->getPkFieldName(true));
            }

            if($this->_active_recipient_type == self::RECEPIENT_TYPE_ONE){
                $data_id = $this->getDataId();
                if(empty($data_id)) return;
                $data_model->andWhere($this->_active_extension_copy[0]->getPkFieldName(true) . ' = ' . $data_id);
            }



            if(!empty($param['filter_params'])){
                $filter = new \FilterModel();
                $filter->copy_id = $extension_copy->copy_id;
                $filter_data = $filter
                                    ->setCopyId($extension_copy->copy_id)
                                    ->setParams(array($param['filter_params']), true)
                                    ->setAddTableName(true)
                                    ->prepareQuery()
                                    ->getQuery();

                if(!empty($filter_data['conditions'])){
                    array_unshift($filter_data['conditions'], 'AND');
                    $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
                }
            }

            $data_models[] = $data_model;
        }

        if(empty($data_models)) return;

        // собираем запрос
        $sql = array();
        $params = array();
        $query = null;
        if(count($data_models) == 1){
            $query = $data_models[0]->getText();
            $params = $data_models[0]->getParams();
        } else {
            foreach($data_models as $data_model){
                $sql[] = '(' . $data_model->getText() . ')';
                $params = array_merge($params, $data_model->getParams());
            }
            $query = implode(' UNION ', $sql);
        }

        // выполняем запрос
        $data_model = new \DataModel();
        $result = $data_model
                        ->setFrom('('.$query.') as DATA')
                        ->setParams($params)
                        ->setOrder('address_data asc')
                        ->findCol();

        return $result;
    }





    public function serviceIsEnabled(){
        $status = $this->getOperationsModel()->getStatus();
        if($status == \Process\models\OperationsModel::STATUS_DONE){
            return array(
                'status' => true
            );
        }


        $source_name = null;
        switch($this->_operation_nf_model->getActiveTypeMessage()){
            case NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL :
                $source_name = \PluginSources::SOURCE_EMAIL;
                break;
            case NotificationUnisenderSendModel::TYPE_MESSAGE_SMS :
                $source_name = \PluginSources::SOURCE_SMS;
                break;
        }

        if($source_name == false){
            return;
        }

        $service_model = (new \PluginsModel())->getServiceModel($source_name, 'unisender');

        if($service_model == false){
            return;
        }


        if(!empty($service_model)){
            return array(
                'status'=>true
            );
        } else {
            $buttons = array(
                \Yii::t('ProcessModule.base','Connect') => array('type'=>'a', 'href'=>'/plugins', 'class'=>'btn btn-primary element', 'onclick' => 'modalDialog.hide();', 'target'=>'_blank'),
                'Close' => array('type'=>'button', 'class'=>'btn btn-default close-button', 'data-dismiss'=>'modal'),
            );

            return array(
                'status' => false,
                'messages' => \Validate::getInstance()
                                    ->addValidateResult('w', \Yii::t('ProcessModule.messages', '{s} delivery is disabled', array('{s}' => $this->getTypeMessageTitle($this->_operation_nf_model->getActiveTypeMessage()))))
                                    ->setButtons($buttons)
                                    ->getValidateResultHtml(),
            );
        }



    }




    /**
     * validate
     */
    public function validate(){
        self::$filter_fn_index = 0;
        if(empty($this->_schema)) return $this;

        // is required
        foreach($this->_schema as &$element){
            switch($element['type']){
                case self::ELEMENT_RECIPIENT_TYPE :
                case self::ELEMENT_OBJECT_NAME :
                case self::ELEMENT_MODULE_NAME :
                case self::ELEMENT_FIELD_NAME :
                    if($element['value'] === '' || $element['value'] === null){
                        $this->addValidateMessage($element['type'], \Yii::t('messages', 'You must fill field'));
                    }
                    break;

                case self::ELEMENT_SENDER_NAME :
                    if($element['value'] === '' || $element['value'] === null){
                        $this->addValidateMessage($element['type'], \Yii::t('messages', 'You must fill field'));
                        continue;
                    }
                    if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                        if(mb_strlen($element['value']) < 3  || mb_strlen($element['value']) > 8){
                            $this->addValidateMessage($element['type'], \Yii::t('messages', 'The field must contain from {s1} to {s2} characters', array('{s1}'=>3, '{s2}'=>8)));
                            continue;
                        }
                        if(!preg_match("/^[a-zA-Z0-9\s+]*$/", $element['value'])){
                            $this->addValidateMessage($element['type'], \Yii::t('messages', 'Valid only Latin characters and numbers'));
                        }
                    }

                    break;

                case self::ELEMENT_FILTER_FIELD_NAME :
                    if($element['value'] === '' || $element['value'] === null){
                        $key = self::$filter_fn_index;
                        $this->addValidateMessage($element['type'] . $key, \Yii::t('messages', 'You must fill field'));
                    }
                    self::$filter_fn_index++;
                    break;

                case self::ELEMENT_MESSAGE_TEMPLATE :
                    if($element['value'] === '' || $element['value'] === null){
                        $this->addValidateMessage($element['type'], \Yii::t('messages', 'You must fill field'));
                        continue;
                    }
                    break;

                case self::ELEMENT_MESSAGE_TEXT :
                    if($element['value'] === '' || $element['value'] === null){
                        $this->addValidateMessage($element['type'], \Yii::t('messages', 'You must fill field'));
                        continue;
                    }

                    if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_EMAIL){
                        if(mb_strlen($element['value']) > 65536){
                            $this->addValidateMessage($element['type'], \Yii::t('messages', 'The length field value longer than {s} characters', array('{s}'=>65536)));
                        }
                    } else
                        if($this->_operation_nf_model->getActiveTypeMessage() == NotificationUnisenderSendModel::TYPE_MESSAGE_SMS){
                            if(mb_strlen($element['value']) > 1000){
                                $this->addValidateMessage($element['type'], \Yii::t('messages', 'The length field value longer than {s} characters', array('{s}'=>1000)));
                            }
                    }
                    break;
            }
        }


        /*
        if($this->_be_error == false){
            $service_is_enabled = $this->serviceIsEnabled();
            if($service_is_enabled['status'] == false){
                $this->addValidateMessage('e', \Yii::t('ProcessModule.messages', '{s} delivery is disabled', array('{s}' => $this->getTypeMessageTitle($this->_operation_nf_model->getActiveTypeMessage()))));
            }
        }
        */


        return $this;
    }





    public static function getParsedSendResult($send_result){
        $result = array(
            'status' => true,
            'validate' => new \Validate(),
        );

        if(is_string($send_result)){
            $send_result = json_decode($send_result, true);
        }

        if(empty($send_result)){
            $result['status'] = false;
            $result['validate']->addValidateResult('e', \Yii::t('ProcessModule.messages', 'API access error'));
            return $result;
        } else {
            //error
            if(isset($send_result['error'])){
                $result['status'] = false;
                $result['validate']->addValidateResult('e', \Yii::t('ProcessModule.messages', '{s1} (code: {s2})', array('{s1}' => addslashes($send_result['error']), '{s2}' => $send_result['code'])));
            }
            //warnings
            if(isset($send_result['warnings']) && !empty($send_result['warnings'])){
                foreach($send_result['warnings'] as $val){
                    if(!empty($val['warning'])){
                        $result['status'] = false;
                        $result['validate']->addValidateResult('w', addslashes($val['warning']));
                    }
                }
            }
            //result
            if(isset($send_result['result']) && !empty($send_result['result'])){
                foreach($send_result['result'] as $val){
                    if(isset($val['errors'])){
                        foreach($val['errors'] as $val2){
                            $result['status'] = false;
                            if(!empty($val['email'])){
                                $result['validate']->addValidateResult('e', \Yii::t('ProcessModule.messages', 'E-mail: {s0}, {s1} (code: {s2})', array('{s0}' => $val['email'], '{s1}' => addslashes($val2['message']), '{s2}' => $val2['code'])));
                            } else{
                                $result['validate']->addValidateResult('e', \Yii::t('ProcessModule.messages', '{s1} (code: {s2})', array('{s1}' => addslashes($val2['message']), '{s2}' => $val2['code'])));
                            }

                        }
                    }
                }
            }
        }

        return $result;
    }





    private function getLogData($send_method_list){
        $methods = array();
        foreach($send_method_list as $item){
            $methods[] = '"' . $item .'"';
        }
        $methods = implode(',',$methods);

        $log_model = \DataModel::getInstance()
                        ->setSelect('recive_text')
                        ->setFrom('{{unisender_log}}')
                        ->setWhere('process_id=:process_id AND
                                    operation_unique_index=:unique_index AND
                                    send_method in ('.$methods.')',
                                   array(
                                       ':process_id' => \Process\models\ProcessModel::getInstance()->process_id,
                                       ':unique_index' => $this->getOperationsModel()->unique_index,
                                   ));
        return $log_model->findAll();
    }


    /**
     * getLastSendStatus - возвращает статус последней отправки из лог файла
     */
    public function getLastSendStatus(){
        $status = $this->getOperationsModel()->getStatus();
        if($status == \Process\models\OperationsModel::STATUS_DONE) return array('status' => true);

        $result = array(
            'status' => true,
            'validate' => null,
        );

        $log_data = $this->getLogData(array(
                                    \UnisenderSend::METHOD_SEND_EMAIL,
                                    \UnisenderSend::METHOD_SEND_SMS,
                                    \UnisenderSend::METHOD_CREATE_LIST,
                                ));

        if(empty($log_data)) return $result;

        $send_result = $log_data[count($log_data) - 1]['recive_text'];

        return self::getParsedSendResult($send_result);
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

        //Название объекта
        $from_schema = \Process\models\SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_OBJECT_NAME));
        if(!empty($from_schema['value'])){
            if($from_schema['value']){
                $object_name = json_decode($from_schema['value'], true);
                if(
                    $object_name &&
                    $object_name['type'] == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO &&
                    $object_name['copy_id'] == $compare_value
                ){
                    return true;
                }
            }
        }

        return false;
    }
    */





}


