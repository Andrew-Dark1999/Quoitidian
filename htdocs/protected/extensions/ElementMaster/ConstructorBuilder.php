<?php

/**
* ConstructorBuilder - Мастер динамических полей модуля
* @author Alex R.
* @version 1.0
*/

class ConstructorBuilder{
    
    private $extension;
    private $extension_copy;
    
    
    // соджержит схему активного блока + елементы 
    public static $block_schema_active = array();
    
    public static function getInstance(){
        return new self;
    }
    
    public function setExtension($extension){
        $this->extension = $extension;
        return $this;
    }


    public function setExtensionCopy($extension_copy){
        $this->extension_copy = $extension_copy;
        return $this;
    }


    /**
     *  возвращает стасус отображения кнопки добавления новых елеметов полей типов "Кнопка"  
     */
    public static function displayBlockButtonBox($schema){
        $display = '';
        if(SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_DATE_ENDING, $schema) &&
           SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_RESPONSIBLE, $schema) &&
           SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_STATUS, $schema)
           )
        $display = 'hidden';

        return $display;
    }


    /**
     * возвращает список линков для Сабмодуля 
     */
    public function getSubModuleLinks($schema_params){
        $links = array(
            array('value' => 'create', 'checked' => true, 'title' => Yii::t('constructor', 'Creating')),
            array('value' => 'select', 'checked' => true, 'title' => Yii::t('constructor', 'Binding')),
            array('value' => 'copy',   'checked' => true, 'title' => Yii::t('constructor', 'Copying')),
            array('value' => 'delete', 'checked' => true, 'title' => Yii::t('constructor', 'Deleting')),
        );

        if(isset($schema_params['relate_links']))
            $links = Helper::arrayMerge($links, $schema_params['relate_links']);
            
        return $links;
    }

    /**
    * строит елементы полей  для страницы коструктора полей
    * @return string (html) 
    */
    public function buildConstructorPage($schema, $use_wrapper = true){
        if(empty($schema)) return;
        if(count($schema) == 0) return;
        $result = '';
        $use_last_wrapper = false;
        foreach($schema as $k => $value){
            if(isset($value['type']))
            switch ($value['type']){
                case 'block' :
                
                    //дополнительно оборачиваем блоки в определенную верстку
                    $start_wrapper = false;
                    $finish_wrapper = false;
                    
                    if($use_wrapper) {
                        //признаки "открытия" обертки: определеннный блок и предыдущий подобный блок не открыт
                        if($value['elements'] && $value['elements'][0]['type']!=$use_last_wrapper && !$value['params']['header_hidden']) {
                            switch($value['elements'][0]['type']) {
                                case 'block_panel':
                                    $use_last_wrapper = $value['elements'][0]['type'];
                                    $start_wrapper = 'standart';
                                break;
                                case 'sub_module':
                                    $use_last_wrapper = $value['elements'][0]['type'];
                                    $start_wrapper = 'submodule';
                                break;
                                case 'activity':
                                    $use_last_wrapper = $value['elements'][0]['type'];
                                    $start_wrapper = 'activity';
                                break;
                                case 'attachments':
                                    $use_last_wrapper = $value['elements'][0]['type'];
                                    $start_wrapper = 'activity';
                                break;
                            }

                        }

                        //признаки "закрытия" обертки
                        if($use_last_wrapper && in_array($value['elements'][0]['type'], array('block_panel', 'sub_module', 'activity', 'attachments'))) {
                            //она была инициализирована
                            if(!empty($schema[$k + 1]['elements'][0]['type'])) {
                                //тип следующего поля отличается от текущего (вложения \ активность исключаем, они и так последние)
                                if(in_array($value['elements'][0]['type'], array('block_panel', 'sub_module')))
                                    if($schema[$k + 1]['elements'][0]['type']!=$value['elements'][0]['type'])
                                        $finish_wrapper = true;
                            }else {
                                //это последний элемент
                                $finish_wrapper = true; 
                            }
                        }
                    }
                    
                    $result.= $this->getConstructorElementBlock($value, $start_wrapper, $finish_wrapper); 
                    break;
                case 'block_panel' :
                    $result.= $this->getConstructorElementBlockPanel($value);
                    break;
                case 'block_panel_contact' :
                    $result.= $this->getConstructorElementBlockPanelContact($value);
                    break;
                case 'block_button' :
                    $result.= $this->getConstructorElementBlockButton($value);
                    break;
                case 'activity' :
                    $result.= $this->getConstructorElementActivity($value);
                    break;
                case 'participant' :
                    $result.= $this->getConstructorElementParticipant($value);
                    break;
                case 'attachments' :
                    $result.= $this->getConstructorElementAttachments($value);
                    break;
                case 'panel' :
                    $result.= $this->getConstructorElementPanel($value);
                    break;
                case 'label' :
                    $result.= $this->getConstructorElementLabel($value);
                    break;
                case 'block_field_type' :
                    $result.= $this->getConstructorElementBlockFieldType($value);
                    break;
                case 'block_field_type_contact' :
                    $result.= $this->getConstructorElementBlockFieldTypeContact($value);
                    break;
                case 'edit' :
                    $result.= $this->getConstructorElementFieldType($value);
                    break;
                case 'edit_hidden' :
                    $result.= $this->getConstructorElementFieldTypeHidden($value);
                    break;
                case 'button' :
                    $result.= $this->getConstructorElementButton($value);
                    break;
                case 'table' :
                    $result.= $this->getConstructorElementTable($value);
                    break;
                case 'table_column' :
                    $result.= $this->getConstructorElementTableColumn($value);
                    break;
                case 'table_header' :
                    $result.= $this->getConstructorElementTableHeader($value);
                    break;
                case 'table_footer' :
                    $result.= $this->getConstructorElementTableFooter($value);
                    break;
                case 'sub_module' :
                    $result.= $this->getConstructorElementSubModule($value);
                    break;
            }
        }
        return $result;
    }
    
    
    /**
    * Возвращает елемент "Блок" (block)
    * @return string (html)  
    */
    public function getConstructorElementBlock($schema, $start_wrapper = false, $finish_wrapper = false){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        self::$block_schema_active = $schema;

        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Block.Block',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                    'start_wrapper' => $start_wrapper,
                                    'finish_wrapper' => $finish_wrapper,
                                   ),
                                   true);
        return $result; 
        
    }


    /**
    * Возвращает елемент "Блок Панель" (BlockPanel)
    * @return string (html)  
    */
    public function getConstructorElementBlockPanel($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        if(!isset($schema['params']['make']) || $schema['params']['make'] == false) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Panel.Panel',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                    'view' => 'block_panel',
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Блок Панель контактов" (BlockPanelContact)
    * @return string (html)  
    */
    public function getConstructorElementBlockPanelContact($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        if(!isset($schema['params']['make']) || $schema['params']['make'] == false) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Panel.Panel',
                                   array(
                                    'extension_copy' => $this->extension_copy,
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                    'view' => 'block_panel_contact',
                                   ),
                                   true);
        return $result;
    }
    

    /**
    * Возвращает елемент "Блок кнопок" (block_button)
    * @return string (html)  
    */
    public function getConstructorElementBlockButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Buttons.Buttons',
                                   array(
                                    'schema' => $schema,
                                    'view' => 'block', 
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                   ),
                                   true);
        return $result; 
        
    }


    /**
    * Возвращает блок-елемент "активность"
    * @return string (html)  
    */
    public function getConstructorElementActivity($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Activity.Activity',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }




    /**
    * Возвращает блок-елемент "учасники"
    * @return string (html)  
    */
    public function getConstructorElementParticipant($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Participant.Participant',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }



    /**
    * Возвращает блок-елемент "Вложения"
    * @return string (html)  
    */
    public function getConstructorElementAttachments($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Attachments.Attachments',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Панель" (Panel)
    * @return string (html)  
    */
    public function getConstructorElementPanel($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Panel.Panel',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Метка" (Label)
    * @return string (html)  
    */
    public function getConstructorElementLabel($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Label.Label',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }


    /**
     * Возвращает список типов
     */
    public function getConstructorFieldsType($schema = null){
        if(empty($schema)) return Fields::getInstance()->getFields($this->extension->getModule()->getConstructorFields());
        if(!array_key_exists('c_types_list_index', $schema['params']) || (integer)$schema['params']['c_types_list_index'] == (integer)Fields::TYPES_LIST_INDEX_DEFAULT )
            return Fields::getInstance()->getFields($this->extension->getModule()->getConstructorFields());
        else 
            return Fields::getInstance()->getFieldsByGroupIndex((integer)$schema['params']['c_types_list_index']);
    }


    /**
    * Возвращает елемент "Блок Типа поля" (BlockFieldType)
    * @return string (html)  
    */
    public function getConstructorElementBlockFieldType($schema){
        if(empty($schema)) return false;
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.FieldType.FieldType',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                    'view' => 'block',
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Блок Типа поля" (BlockFieldTypeContact)
    * @return string (html)  
    */
    public function getConstructorElementBlockFieldTypeContact($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.FieldType.FieldType',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                    'view' => 'block_contact',
                                   ),
                                   true);
        return $result;
    }
    
    
    /**
    * Возвращает елемент "Тип поля" (FieldType)
    * @return string (html)  
    */
    public function getConstructorElementFieldType($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $controller = Yii::app()->controller;
        
        $field_type_params = '';
        if(!array_key_exists('c_load_params_view', $schema['params']) || (isset($schema['params']['c_load_params_view']) && $schema['params']['c_load_params_view'] == true)){
            $exception_copy_id = array();
            if(isset($this->exception_copy)){
                $relate_tables = ModuleTablesModel::model()->findAll('copy_id='.$this->exception_copy->copy_id . ' AND type in("relate_module_one", "relate_module_many")');
                if(!empty($relate_tables))
                foreach($relate_tables as $relate)
                    if(isset($schema['params']['relate_module_copy_id']) && $schema['params']['relate_module_copy_id'] != $relate->relate_copy_id && !empty($relate->relate_copy_id))
                        $exception_copy_id[] = $relate->relate_copy_id;
            }
            
            $field_type_params = $controller->widget('ext.ElementMaster.Constructor.Params.Params',
                                                array(
                                                    'extension_copy' => $this->extension_copy,
                                                    'exception_copy_id' => $exception_copy_id,
                                                    'params' => $schema['params'],
                                                ),
                                                true);
        }
         
        $result = $controller->widget('ext.ElementMaster.Constructor.Elements.FieldType.FieldType',
                                   array(
                                    'schema' => $schema,
                                    'fields_type' => $this->getConstructorFieldsType($schema),
                                    'field_type_params' => $field_type_params,
                                   ),
                                   true);
        return $result;
    }



    /**
    * Возвращает елемент "Тип поля" (FieldTypeHidden)
    * @return string (html)  
    */
    public function getConstructorElementFieldTypeHidden($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        if(isset($schema['params']['name']) && $schema['params']['name'] == 'ehc_image1') return false;
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.FieldType.FieldType',
                                   array(
                                    'schema' => $schema,
                                    'fields_type' => $this->getConstructorFieldsType($schema),
                                    'view' => 'element_hidden',
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Кнопка" (Button)
    * @return string (html)  
    */
    public function getConstructorElementButton($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $field_type_params = '';
        $controller =  Yii::app()->controller;
        if(!array_key_exists('c_load_params_view', $schema['params']) || (isset($schema['params']['c_load_params_view']) && $schema['params']['c_load_params_view'] == true)){
            $field_type_params = $controller->widget('ext.ElementMaster.Constructor.Params.Params',
                                                array(
                                                    'params' => $schema['params'],
                                                    'extension_copy' => $this->extension_copy,
                                                ),
                                                true);
        }
        
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Buttons.Buttons',
                                   array(
                                    'schema' => $schema,
                                    'view' => 'button', 
                                    'field_type_params' => $field_type_params,
                                   ),
                                   true);
        return $result;
    }





    /**
    * Возвращает елемент "Тыблица" (Table)
    * @return string (html)  
    */
    public function getConstructorElementTable($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.Table.Table',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                   ),
                                   true);
        
        return $result;
    }


    /**
    * Возвращает елемент "Колонка тыблицы" (TableColumn)
    * @return string (html)  
    */
    public function getConstructorElementTableColumn($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.TableColumn.TableColumn',
                                   array(
                                    'schema' => $schema,
                                    'content' => $this->buildConstructorPage($schema['elements']),
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Заголовок тыблицы" (TableHeader)
    * @return string (html)  
    */
    public function getConstructorElementTableHeader($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.TableHeader.TableHeader',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }


    /**
    * Возвращает елемент "Подвал тыблицы" (TableFooter)
    * @return string (html)  
    */
    public function getConstructorElementTableFooter($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.TableFooter.TableFooter',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }





    /**
    * Возвращает елемент "СубМодуль" (SubModule)
    * @return string (html)  
    */
    public function getConstructorElementSubModule($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Elements.SubModule.SubModule',
                                   array(
                                    'schema' => $schema,
                                   ),
                                   true);
        return $result;
    }


    /**
     * getBlockChildElementName - Возвращает название подчиненного елемента для блока верстки
     */
    public static function getBlockChildElementName($schema_element){
        $child_element = 'block';

        if(isset($schema_element['elements'][0]['type']) && $schema_element['elements'][0]['type'] == 'sub_module'){
            $child_element = 'sub_module';
        } else if(
            isset($schema_element['elements'][0]['type']) &&
            $schema_element['elements'][0]['type'] == 'participant' &&
            isset($schema_element['elements'][0]['params']) &&
            $schema_element['elements'][0]['params']['type'] == \Fields::MFT_RELATE_PARTICIPANT
        ){
            $child_element = 'block_participant';
        }

        return $child_element;
    }


}
