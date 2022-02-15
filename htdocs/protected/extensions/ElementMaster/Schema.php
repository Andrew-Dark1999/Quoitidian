<?php


class Schema{


    // массив дополнительных параметров, передаваемых при создании класса
    private $params = array(); 
    // количество вложеных элементов при рекурсии
    private $_put = null;
    // количество вложеных элементов при рекурсии. Изменяется автом. при генерации схемы
    private $_put_work = 0;
    // количество панелей
    private $_count_panels = 1;
    // количество елементов полей в списку панели   
    private $_count_select_fields = 4;
    // количество Полей данных    
    private $_count_edit = 1;
    // количество Полей данных    
    private $_count_edit_hidden = 1;
    // количество Колонок талицы
    private $_count_table_column = 1;    
    // количество Кнопок
    private $_count_buttons = 0;
    
    
    //указывает на существование блока block_panel_contact 
    private $_be_block_panel_contact = false;
    
    /**
    * @param $params array - массив параметров для передачи в свойства класса
    */
    public function __construct(array $params){
        if(!empty($params))
        foreach($params as $key => $value)
            $this->{$key} = $value;
    }
    
    public static function getInstance(array $params = array()){
        return new self($params); 
    }

    /**
    * Субэлементи элемента "Блок"
    */
    private function getSubElementsBlock(){
        $array = array(
            'block_panel' => array('block_panel'),
            'block_button' => array('block_button'),
            'activity' => array('activity'),
            'participant' => array('participant'),
            'attachments' => array('attachments'),
            'sub_module' => array('sub_module'),
        );
        if($this->_be_block_panel_contact) $array['block_panel'] = array('block_panel_contact', 'block_panel');
        return $array;
    }

    /**
    * Субэлементы элемента "Блок Панель"
    */
    private function getSubElementsBlockPanel($settings = array()){
        $array = array();
        $count = $this->_count_panels;
        if(isset($settings['params']['count_panels'])) $count = $settings['params']['count_panels'];
        for($i = 0; $i < $count; $i++) $array[] = 'panel'; 
        return $array;
    }

    /**
    * Субэлементы элемента "Блок Панель Контактов"
    */
    private function getSubElementsBlockPanelContact($settings = array()){
        return array('block_field_type_contact');
    }

    /**
    * Субэлементы элемента "Панель"
    */
    private function getSubElementsPanel(){
        return array(
            'field' => array('label', 'block_field_type'),
            'table' => array('table'),
        );
    }
    
    /**
    * Субэлементы элемента "Блок Типов данных"
    */
    private function getSubElementsBlockFieldType($settings = array()){
        $array = array();
        $count = $this->_count_edit;
        if(isset($settings['params']['count_edit'])) $count = $settings['params']['count_edit'];
        for($i = 0; $i < $count; $i++) $array[] = 'edit'; 
        return $array;
    }




    /**
    * Субэлементы элемента "Блок Типов данных Контактов"
    */
    private function getSubElementsBlockFieldTypeContact($settings = array()){
        $array = array();
        $count = $this->_count_edit_hidden;
        if(isset($settings['params']['count_edit_hidden'])) $count = $settings['params']['count_edit_hidden'];
        for($i = 0; $i < $count; $i++) $array[] = 'edit_hidden'; 
        return $array;
    }

    /**
    * Субэлементы элемента "Таблица"
    */
    private function getSubElementsTable($settings = array()){
        $array = array();
        $count = $this->_count_table_column;
        if(isset($settings['params']['count_table_column'])) $count = $settings['params']['count_table_column'];
        for($i = 0; $i < $count; $i++) $array[] = 'table_column'; 
        return $array;
    }
    


    /**
    * Субэлементи элемента "Блок кнопок"
    */
    private function getSubElementsBlockButton($settings = array()){
        $array = array();
        $count = $this->_count_buttons;
        if(isset($settings['params']['count_buttons'])) $count = $settings['params']['count_buttons'];
        for($i = 0; $i < $count; $i++) $array[] = 'button'; 
        return $array;
    }


    
    
    /**
    * Субэлементы элемента "Колонка таблицы"
    */
    private function getSubElementsTableColumn(){
        return array (
            'table_header', 'edit', 'table_footer'
        );
    }

    
    
   

    
    /**
    * Генерация схемы элементов конструктора
    * @param $element string
    * @return array 
    */
    public function generateConstructorSchema($element){
        
        if(empty($element)) return;
        switch($element){
            case 'block' : $array = array('block' => array());
                $this->_put = 1; 
                break;
            case 'block_panel' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'New block'),
                                                'unique_index' => md5(date('YmdHis') . mt_rand(1, 1000)), 
                                            ),
                                            'elements' => array(
                                                array(
                                                    'block_panel' => array(
                                                        array(
                                                            'type' => 'block_panel_contact',
                                                            'params' => array('make' => false),
                                                        ),
                                                        array(
                                                            'type' => 'block_panel',
                                                            'params' => array('make' => true),
                                                            'elements' => array(
                                                                array(
                                                                    'type' => 'panel',
                                                                    'elements' => array(
                                                                        array(
                                                                            'field' => array(),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                $this->_put = 4 ; 
                break;
            case 'block_panel_title' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'New block'),
                                                'destroy' => true,
                                                'unique_index' => md5(date('YmdHis')),
                                            ),
                                            'elements' => array(
                                                array(
                                                    'block_panel' => array(
                                                        array(
                                                            'type' => 'block_panel_contact',
                                                            'params' => array('make' => false),
                                                        ),
                                                        array(
                                                            'type' => 'block_panel',
                                                            'params' => array('make' => true),
                                                            'elements' => array(
                                                                array(
                                                                    'type' => 'panel',
                                                                    'params' => array(
                                                                        'c_count_select_fields_display' => true, 
                                                                        'c_list_view_display' => true,  
                                                                        'c_process_view_group_display' => true,                                                                    ),
                                                                    'elements' => array(
                                                                        array(
                                                                            'field_title' => array(),
                                                                        ),
                                                                    ),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                $this->_put = 1; 
                break;           
                
            case 'block_button' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'New button block'),
                                                'header_hidden' => true,
                                                'border_top' => false,
                                                'unique_index' => md5(date('YmdHis')),
                                                
                                            ),
                                            'elements' => array(
                                                array(
                                                    'block_button' => array(
                                                        array(
                                                            'type' => 'block_button',
                                                            'elements' => array(),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                break;

            case 'block_activity' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'Activity'),
                                                'unique_index' => md5(date('YmdHis')),
                                            ),
                                            'elements' => array(
                                                array(
                                                    'activity' => array(
                                                        array(
                                                            'type' => 'activity',
                                                            'elements' => array(),
                                                            'params' => array(),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                break;

            case 'block_participant' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'Participant'),
                                                'header_hidden' => false,
                                                'border_top' => false,
                                                'chevron_down' => false,
                                                'unique_index' => md5(date('YmdHis')),
                                            ),
                                            'elements' => array(
                                                array(
                                                    'participant' => array(
                                                        array(
                                                            'type' => 'participant',
                                                            'elements' => array(),
                                                            'params' => array(
                                                                'c_db_create' => false,
                                                            ),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                break;
            case 'block_attachments' : $array = array(
                                        'block' => array(
                                            'type' => 'block',
                                            'params' => array(
                                                'title' => Yii::t('base', 'Attachments'),
                                                'header_hidden' => false,
                                                'border_top' => true,
                                                'chevron_down' => true,
                                                'unique_index' => md5(date('YmdHis')),
                                            ),
                                            'elements' => array(
                                                array(
                                                    'attachments' => array(
                                                        array(
                                                            'type' => 'attachments',
                                                            'elements' => array(),
                                                            'params' => array(),
                                                        ),
                                                    ),
                                                ),
                                            )
                                        )
            );
                break;
            case 'block_panel_contact' : $array = array(
                                                    'block_panel_contact' => array(
                                                        'type' => 'block_panel_contact',
                                                        'params' => array('make' => true),
                                                        'elements' => array(
                                                            array(
                                                                'type' => 'block_field_type_contact',
                                                                'params' => array(
                                                                    'count_edit_hidden' => 3,
                                                                ),
                                                                'elements' => array(
                                                                    array(
                                                                        'type' => 'edit_hidden',
                                                                        'params' => array(
                                                                            'title' => Yii::t('base', 'Phone'),
                                                                            'name' => 'ehc_field1'
                                                                        )
                                                                    ),
                                                                    array(
                                                                        'type' => 'edit_hidden',
                                                                        'params' => array(
                                                                            'title' => Yii::t('base', 'Mobile'),
                                                                            'name' => 'ehc_field2'
                                                                        )
                                                                    ),
                                                                    array(
                                                                        'type' => 'edit_hidden',
                                                                        'params' => array(
                                                                            'title' => Yii::t('base', 'Email'),
                                                                            'name' => 'ehc_field3'
                                                                        )
                                                                    ),
                                                                ),
                                                            ),
                                                        ),
                                                    ),
            );            
            $this->_put = 4;
                break;
            case 'block_submodule' : $array = array(
                            'block' => array(
                                'type' => 'block',
                                'params' => array(
                                    'title' => Yii::t('base', 'New submodule'),
                                    'title_edit' => false,
                                    'unique_index' => md5(date('YmdHis')),
                                ),
                                'elements' => array(
                                    array(
                                        'sub_module'=>array(
                                            'type' => 'sub_module',
                                            'params' => array(
                                                'relate_module_copy_id' => $this->params['relate_module_copy_id'],
                                                'relate_module_template' => $this->params['relate_module_template'],
                                                'relate_index' => null, 
                                                //'relate_type' => 'MANY_MANY',      // BELONGS_TO, HAS_MANY, HAS_ONE, MANY_MANY
                                                //'relate_field_index' => ExtensionCopyModel::model()->findByPk($this->params['relate_module_copy_id'])->prefix_name . '_id',
                                            )
                                        )
                                    ),
                                ),
                            ),
                        );
                break;
            case 'panel_field' : $array = array(
                            'panel' => array(
                                'elements' => array(
                                    array(
                                        'field'=>array(
                                            array(
                                                'type' => 'edit',
                                                'params' => array(
                                                    'c_load_params_view' => false,
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        );
                break;
            case 'panel_field_title' : $array = array(
                            'panel' => array(
                                'params' => array(
                                        'c_count_select_fields_display' => false, 
                                        'c_list_view_display' => false,  
                                        'c_process_view_group_display' => false,
                                        'destroy' => false,
                                    ),                                                                
                                'elements' => array(
                                    array(
                                        'field'=>array(
                                            array(
                                                'type' => 'label',
                                                'params' => array(
                                                    'title' => Yii::t('base', 'Name'),
                                                ),
                                            ),
                                            array(
                                                'type' => 'block_field_type',
                                                'params' => array('count_edit' => 1),
                                                'elements' => array(
                                                    array(
                                                        'type'=>'edit',
                                                        'params'=> array(
                                                            'c_load_params_view' => false,
                                                            'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                                            'name' => 'module_title',
                                                            'type' => 'string',
                                                            'is_primary' => true,
                                                            'c_load_params_btn_display' => false,
                                                            'group_index' => 0,
                                                            'avatar' => false,
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        );
                break;
            case 'panel_table' : $array = array(
                            'panel' => array(
                                'params' => array(
                                    'active_count_select_fields' => 4,
                                    'count_select_fields' => 10,
                                ),
                                'elements' => array(
                                    array(
                                        'table'=>array(
                                            array(
                                                'type' => 'table',
                                                'params' => array(
                                                    'count_table_column' => 4,
                                                ),
                                                'elements' => array(
                                                    array(
                                                        'type' => 'table_column',
                                                        'elements' => array(),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        );
                break;
            case 'table_column' : $array = array(
                                                'table_column' => array(
                                                ),
                                            );
                break;                
            case 'field' : $array = array(
                            'edit' => array(
                            ),
                        );
                break;
            case 'field_title' : $array = array(
                            'edit' => array(
                                'type' => 'edit',
                                'attr' => array('title' => Yii::t('base', 'Title')),
                                'params' => array(
                                    'c_load_params_view' => false,
                                    'c_types_list_index' => Fields::TYPES_LIST_INDEX_TITLE,
                                    'name' => 'module_title',
                                    'type' => 'string',
                                    'is_primary' => true,
                                    'c_load_params_btn_display' => false,
                                    'group_index' => 0,
                                ),
                            ),
                        );
                break;
            case 'field_hidden' : $array = array(
                            'edit_hidden' => array(
                                'params' => array(
                                    'title' => Yii::t('base', 'New field')
                                )
                            ),
                        );
                break;

            case 'button_date_ending' : $array = array(
                            'button' => array(
                                'type' => 'button',
                                'params' => array(
                                    'name' => 'b_date_ending',
                                    'type' => 'datetime',
                                    'type_view' => Fields::TYPE_VIEW_BUTTON_DATE_ENDING,
                                    'c_db_create' => false,
                                ),
                            ),
                        );
                break;
                
            case 'button_subscription' : $array = array(
                            'button' => array(
                                'type' => 'button',
                                'params' => array(
                                    'name' => 'b_subscription',
                                    'type' => 'relate_participant',
                                    'type_view' => Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION,
                                    'c_db_create' => false,
                                ),
                            ),
                        );
                break;

            case 'button_responsible' : $array = array(
                            'button' => array(
                                'type' => 'button',
                                'params' => array(
                                    'name' => 'b_responsible',
                                    'type' => 'relate_participant',
                                    'type_view' => Fields::TYPE_VIEW_BUTTON_RESPONSIBLE,
                                    'c_db_create' => false,
                                ),
                            ),
                        );
                break;

            case 'button_status' : $array = array(
                            'button' => array(
                                'type' => 'button',
                                'params' => array(
                                    'name' => 'b_status',
                                    'type' => 'select',
                                    'type_view' => Fields::TYPE_VIEW_BUTTON_STATUS,
                                ),
                            ),
                        );
                break;

            case 'block_field' : $array = array(
                            'panel' => array(
                                'params' => array(
                                    'destroy' => false,
                                    'c_count_select_fields_display' => false,
                                ),
                                'elements' => array(
                                    array(
                                       'field'=>array(
                                            array(
                                                'type' => 'label',
                                                'params' => array(
                                                    //'title' => Yii::t('base', 'Name'),
                                                ),
                                            ),
                                            array(
                                                'type' => 'block_field_type',
                                                'elements' => array(
                                                    array(
                                                        'type'=>'edit',
                                                        'params'=> array(
                                                            'type' => 'display_block',
                                                            'c_load_params_view' => true,
                                                            'c_types_list_index' => Fields::TYPES_LIST_INDEX_BLOCK,
                                                            'required' => true,
                                                        ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        );
                break;
                
        }
        return $this->generateDefaultSchema($array);
    }
        
    
    
    /**
    * Генерация схемы по умолчанию
    * @param $elements array
    * @return array 
    */
    public function generateDefaultSchema(array $elements){
        if(empty($elements)) return;
        $result = array();
        
        $this->_be_block_panel_contact = SchemaOperation::getInstance()->beBlockPanelContact($elements);
        
        
        foreach($elements as $key => $value){
            $result+=$this->addElementSchema($key, $value);
        }
        return $result;
    }
    
    
    /**
    * Добавление элемента схемы 
    * @param $element string
    * @param $settings array
    * @return array 
    */
    private function addElementSchema($element, $settings){
        if($this->_put !== null && $this->_put == $this->_put_work) return;
        if(empty($element)) return;
        $result = array();
        switch($element){
            case 'block' :
                    $this->_put_work++;
                    $result += $this->getBlock($settings);break;
            case 'block_panel' :
                    $this->_put_work++; 
                    $result += $this->getBlockPanel($settings); break;
            case 'block_panel_contact' :
                    $this->_put_work++; 
                    $result += $this->getBlockPanelContact($settings); break;
                    $this->_be_block_panel_contact = false;
            case 'block_button' :
                    $this->_put_work++; 
                    $result += $this->getBlockButton($settings); break;
            case 'activity' : 
                    $this->_put_work++; 
                    $result += $this->getActivity($settings); break;
            case 'participant' : 
                    $this->_put_work++; 
                    $result += $this->getParticipant($settings); break;
            case 'attachments' : 
                    $this->_put_work++; 
                    $result += $this->getAttachments($settings); break;
            case 'panel' :
                    $this->_put_work++; 
                    $result += $this->getPanel($settings); break;
            case 'label' :
                    $result += $this->getLabel($settings); break;
            case 'block_field_type' :
                    $this->_put_work++; 
                    $result += $this->getBlockFieldType($settings); break;
            case 'block_field_type_contact' :
                    $this->_put_work++; 
                    $result += $this->getBlockFieldTypeContact($settings); break;
            case 'edit' : 
                    $result += $this->getEdit($settings); break;
            case 'edit_hidden' : 
                    $result += $this->getEditHidden($settings); break;
            case 'button' : 
                    $result += $this->getButton($settings); break;
            case 'table' : 
                    $result += $this->getTable($settings); break;
            case 'table_column' : 
                    $result += $this->getTableColumn($settings); break;
            case 'table_header' : 
                    $result += $this->getTableHeader($settings); break;
            case 'table_footer' : 
                    $result += $this->getTableFooter($settings); break;
            case 'sub_module' : 
                    $result += $this->getSubModule($settings); break;

        }
        return $result;
    }
    
    
       
    
    /**
    * Возвращает массив субэлементов по умолчанию
    * @param $element string
    * @return array
    */
    private function getSubElementsDefaultArray($element, $settings = array()){
        $method = '';
        switch($element){
            case 'block' : $method = 'getSubElementsBlock'; break;
            case 'block_panel' : $method = 'getSubElementsBlockPanel'; break;
            case 'block_panel_contact' : $method = 'getSubElementsBlockPanelContact'; break;
            case 'block_button' : $method = 'getSubElementsBlockButton'; break;
            case 'panel' : $method = 'getSubElementsPanel'; break;
            case 'block_field_type' : $method = 'getSubElementsBlockFieldType'; break;
            case 'block_field_type_contact' : $method = 'getSubElementsBlockFieldTypeContact'; break;
            case 'table' : $method = 'getSubElementsTable'; break;
            case 'table_column' : $method = 'getSubElementsTableColumn'; break;
        }
        if($method) return $this->{$method}($settings);
    }
    
    
    /**
    * Добавление субэлемента схемы 
    * @param $element string
    * @param $settings array
    * @param $step array
    * @return array 
    */
    private function findSubSettings($element, $settings, $step = null){
        $sub_settings = array();
        $step_local = array();
        if(is_array($settings))
        foreach($settings as $value){
            $tmp = $this->findSubSettings($element, $value, $step);
            if(!empty($tmp)){
                if(!isset($step_local[$tmp['type']]))$step_local[$tmp['type']] = 1; else $step_local[$tmp['type']]++;
                if($step === null || $step !== null && $step[$element] == $step_local[$element])
                    return $tmp;
            }
        } 
        
        if(isset($settings['type'])){
            if($settings['type'] == $element){
                    $sub_settings = $settings;
            }
        } else {
            if(isset($settings[$element])){
                $sub_settings = $settings[$element];
            }
        }

        return $sub_settings;        
    }
  
  
    /**
    * Поиск и проверка ключа субэлемента в дефолтной схеме 
    * @param $key string
    * @param $settings array
    * @return array 
    */
    private function isSetKey($key, $settings){
        if(!isset($settings['elements'])) return false;
        foreach($settings['elements'] as $value){
            if(is_array($value) && isset($value[$key])) return $value[$key];
        }
        return false;
    } 
    
    
    /**
    * Добавление субэлемента схемы 
    * @param $element string
    * @param $settings array
    * @return array 
    */
    private function addSubElement($element, $settings){
        $result = array();
        
        $sub_element_schema = $this->getSubElementsDefaultArray($element, $settings);
        
        $sub_settings = array();
        
        if(!empty($sub_element_schema)){
             $step1 = array();
             foreach($sub_element_schema as $key => $value){ // идем по схеме елементов по дефолту
                if(!is_array($value)){// если не массив (не имеет подчиненных елементов)

                    if(!isset($step1[$value]))$step1[$value] = 1; else $step1[$value]++;
                    if(isset($settings['elements']))
                        $sub_settings = $this->findSubSettings($value, $settings['elements'], $step1);
                    else $sub_settings = $this->findSubSettings($value, $settings, $step1);
 
                    $result[] = $this->addElementSchema($value, $sub_settings);


                } else {// если массив (имеет подчиненные елементы)
                    $sub_settings = $this->isSetKey($key, $settings);
                    if($sub_settings !== false){
                        $step2 = array();
                        foreach($value as $sub_value){
                            if(!isset($step2[$sub_value]))$step2[$sub_value] = 1; else $step2[$sub_value]++;                            
                            $result[] = $this->addElementSchema($sub_value, $this->findSubSettings($sub_value, $sub_settings, $step2));
                        }
                        break;
                    } 
                }
             }
        }/* else {
            if(isset($settings['elements']))
                $sub_settings = $this->findSubSettings($element, $settings['elements']);
            //else $sub_settings = $this->findSubSettings($element, $settings);

            $result[] = $this->addElementSchema($element, $this->findSubSettings($element, $sub_settings));
        }*/
        return $result;
    } 
    
    

    /**
    * Возвращает схему элемента "Блок" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlock($settings){
        $settings['elements'] = $this->addSubElement('block', $settings);
        $schema = array(
                    'type' => 'block',
                    'attr' => array(), //title, id, width, class...
                    'params' => array(
                        'title' => '',
                        'title_edit' => true, 
                        'destroy' => true,
                        
                        'header_hidden' => false, // делает обьект названия блока скрытым
                        'unique_index' => null, // уникальный индекс. Для userStorage...
                        'border_top' => true,   // рисует линию над блоком
                        'chevron_down' => true, // елемент сворачивания/разворачивания блока
                        'edit_view_show' => true, // Отображение всего блока в EditView
                        'edit_view_display' => true, // Отображение всего блока в EditView style="display : ... "
                        'block_panel_contact_exists' => false, //указывает на существование блока Контакты 
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Блок кнопок" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlockButton($settings){
        $settings['elements'] = $this->addSubElement('block_button', $settings);
        $schema = array(
                    'type' => 'block_button',
                    'attr' => array(), //title, id, width, class...
                    'params' => array(
                        'count_buttons' => $this->_count_buttons,
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }



    /**
    * Возвращает схему элемента блока "Активность"   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getActivity($settings){
        $schema =  array(
                    'type' => 'activity',
                    'attr' => array(), //title, id, width, class...
                    'params' => Helper::arrayMerge(Fields::getInstance()->getDefaultSchemaParams('activity'), array('name' => 'bl_activity'))
        );
        
        return Helper::arrayMerge($schema, $settings);        
    }




    /**
    * Возвращает схему элемента блока "Учасники"   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getParticipant($settings){
        $schema =  array(
                    'type' => 'participant',
                    'attr' => array(), //title, id, width, class...
                    'params' => Helper::arrayMerge(Fields::getInstance()->getDefaultSchemaParams('relate_participant'), array('name' => 'bl_participant', 'type_view' => Fields::TYPE_VIEW_BLOCK_PARTICIPANT))
        );
        
        return Helper::arrayMerge($schema, $settings);        
    }


    /**
    * Возвращает схему элемента блока "Вложения"   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getAttachments($settings){
        $schema =  array(
                    'type' => 'attachments',
                    'attr' => array(), //title, id, width, class...
                    'params' => Helper::arrayMerge(Fields::getInstance()->getDefaultSchemaParams('attachments'), array('name' => 'bl_attachments', 'type_view' => Fields::TYPE_VIEW_BLOCK_ATTACHMENTS))
        );
        return Helper::arrayMerge($schema, $settings);        
    }


    /**
    * Возвращает схему элемента "Блок Панель" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlockPanel($settings){
        $settings['elements'] = $this->addSubElement('block_panel', $settings);
        $schema =  array(
                    'type' => 'block_panel',
                    'params' => array(
                        'count_panels' => $this->_count_panels,
                        'make' => true,
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Блок Контактов" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlockPanelContact($settings){
        $settings['elements'] = $this->addSubElement('block_panel_contact', $settings);
        $schema =  array(
                    'type' => 'block_panel_contact',
                    'params' => array(
                        'make' => false,
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }

    /**
    * Возвращает схему элемента "Панель" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getPanel($settings){
        $settings['elements'] = $this->addSubElement('panel', $settings);
        $schema =  array(
                    'type' => 'panel',
                    'attr' => array(), //title, id, width, class...
                    'params' => array(
                        'destroy' => true,
                        'active_count_select_fields' => 1,      
                        'count_select_fields' => $this->_count_select_fields,  
                        'c_count_select_fields_display' => true, // скрывает елемент выбора кол. полей 
                        'c_list_view_display' => true,           // скрывает елемент "Отображать в ListView"  
                        'c_process_view_group_display' => true,  // скрывает елемент "Сортировать в ProcessView"
                        'list_view_visible' => true,                       // значение елемента "Отображать в ListView" по умолчанию
                        'process_view_group' => false,                     // значение елемента "Сортировать в ProcessView" по умолчанию
                        'edit_view_edit' => true,               //!!!! параметр не прописан.  разрешает/запрещает EditView редактирование
                        'inline_edit' => true,                  // разрешает/запрещает Inline редактирование
                        'edit_view_show' => true,               //разрешает/запрещает добавление на страницу EditView данного блока
                        'list_view_display' => true,            // значение елемента "ListView" style="display : ... "
                        'edit_view_display' => true,            // значение елемента "EditView" style="display : ... "
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }






    /**
    * Возвращает схему элемента "Блок типов данных" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlockFieldType($settings){
        $settings['elements'] = $this->addSubElement('block_field_type', $settings);
        $schema =  array(
                    'type' => 'block_field_type',
                    'params' => array(
                        'count_edit' => $this->_count_edit,
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }

    /**
    * Возвращает схему элемента "Блок типов данных Контактов" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getBlockFieldTypeContact($settings){
        $settings['elements'] = $this->addSubElement('block_field_type_contact', $settings);
        $schema =  array(
                    'type' => 'block_field_type_contact',
                    'params' => array(
                        'count_edit_hidden' => $this->_count_edit_hidden,
                        'destroy' => true,
                    ),
                    'elements' => array(),
        );
        return Helper::arrayMerge($schema, $settings);
    }

    /**
    * Возвращает схему элемента "Метка" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getLabel($settings){
        $schema =  array(
                    'type' => 'label',
                    'attr' => array(), //title, id, width, class...
                    'params' => array(
                        'title' => '',
                    ),
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Строка" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getEdit($settings){
        $schema =  array(
                    'type' => 'edit',
                    'attr' => array(), //title, id, width, class...
                    'params' => Fields::getInstance()->getDefaultSchemaParams(!empty($settings['params']['type']) ? $settings['params']['type'] : 'string'),
        );
        return Helper::arrayMerge($schema, $settings);        
    }


    /**
    * Возвращает схему элемента "Строка со скрытым редатированием"    
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getEditHidden($settings){
        $schema =  array(
                    'type' => 'edit_hidden',
                    'attr' => array(), //title, id, width, class...
                    'params' => Fields::getInstance()->getDefaultSchemaParams(!empty($settings['params']['type']) ? $settings['params']['type'] : 'string'),
        );
        return Helper::arrayMerge($schema, $settings);        
    }



    /**
    * Возвращает схему элемента "Кнопка" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getButton($settings){
        $params = Fields::getInstance()->getDefaultSchemaParams(!empty($settings['params']['type']) ? $settings['params']['type'] : 'string');

        //add setting in module
        /*if($settings['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
            $params['process_view_group'] =  true;
        }*/
        
        $schema =  array(
                    'type' => 'button',
                    'attr' => array(), //title, id, width, class...
                    'params' =>  $params,
        );
        return Helper::arrayMerge($schema, $settings);        
    }



    /**
    * Возвращает схему элемента "Таблица" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getTable($settings){
        $settings['elements'] = $this->addSubElement('table', $settings);
        $schema =  array(
                        'type' => 'table',
                        'attr' => array(), //title, id, width, class...
                        'params' => array(
                            'count_table_column' => $this->_count_table_column,
                        ),
                        'elements' => array()
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Поле таблицы" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getTableColumn($settings){
        $settings['elements'] = $this->addSubElement('table_column', $settings);
        $schema =  array(
                        'type' => 'table_column',
                        'attr' => array(), //title, id, width, class...
                        'params' => array(),
                        'elements' => array()
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Заголовок поля таблицы" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getTableHeader($settings){
        $schema =  array(
                        'type' => 'table_header',
                        'attr' => array(), //title, id, width, class...
                        'params' => array(
                            'title' => '',
                        ),
        );
        return Helper::arrayMerge($schema, $settings);
    }


    /**
    * Возвращает схему элемента "Подвал поля таблицы" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getTableFooter($settings){
        $schema =  array(
                        'type' => 'table_footer',
                        'attr' => array(), //title, id, width, class...
                        'params' => array(
                            'title' => '',
                            'total_type' => '', // none, sum, count
                            'total_field' => '',
                        ),
        );
        return Helper::arrayMerge($schema, $settings);
    }
    


    /**
    * Возвращает схему элемента "Cубмодуля" с ее субэлементами   
    * @param $settings array - параметры для обновления полей массива 
    * @return array 
    */
    private function getSubModule($settings){
        $schema =  array(       
                        'type' => 'sub_module',
                        'attr' => array(), //title, id, width, class...
                        'params' => Fields::getInstance()->getDefaultSchemaParams(!empty($settings['params']['type']) ? $settings['params']['type'] : 'sub_module'),
        );
        return Helper::arrayMerge($schema, $settings);
    }

    
}

