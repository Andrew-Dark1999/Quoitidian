<?php
/**
* ListViewBulder
* @author Alex R.
* @version 1.0
*/

class ListViewBulder {
    
    // типы линков для первичного поля
    const PRIMARY_LINK_NONE                 = 0;
    const PRIMARY_LINK_EDIT_VIEW            = 1;
    const PRIMARY_LINK_EDIT_VIEW_SUBMODULE  = 2;
    const PRIMARY_LINK_LIST_VIEW            = 3;
    const PRIMARY_LINK_NONE_LINK            = 4;
    const PRIMARY_LINK_REPORT               = 5;
    const PRIMARY_LINK_REPORTS_LIST_VIEW    = 6;



    // добавление аватара к ключевому полю
    private $_title_add_avatar = false;

    // добавление аватара к связанному полю
    private $_relate_add_avatar = true;

    // для файлов возвращаются только ссылки
    private $_files_only_url = false;

    // не загружает список участников как выпадающее меню  (используется при печати, экспорте)
    public static $participant_list_hidden = false;


    // экземпляр класса ExtensionCopyModel
    private $_extension_copy;
    
    // массив параметров полей по группам 
    private $_schema_params_groups = array();

    // показать Ответственного вместо аватара
    private $_avatar_view_responsible = false;

    // span|img - тег, что будет возвращен для вывода картинки
    private $_img_tag = 'span';


    private $_this_template;
    private $_finished_object;

    // указывает, что ссылка уже добавлена. Используется виджетом TData
    public static $primary_link_aded = false;

    private $blocks = array();
    

    public function __construct($extension_copy){
        if(!$extension_copy->isShowAllBlocks()) {
            $blocks = $extension_copy->getSchemaBlocksData();
            if($blocks) {
                foreach($blocks as $block) {
                    $this->blocks[$block['unique_index']] = $block['title'];
                }
            }
        }
        $this->_extension_copy = $extension_copy;
    }

    public static function getInstance($extension_copy){
        return new self($extension_copy);
    }
    
    public function getSchema(){
        return $this->_extension_copy->getSchema();
    }


    
    public function setParticipantListHidden($participant_list_hidden){
        self::$participant_list_hidden = $participant_list_hidden;
        return $this;
    }
    


    public function setAvatarViewResponsible($param){
        $this->_avatar_view_responsible = $param;
        return $this;
    }


    public function setFilesOnlyUrl($files_only_url){
        $this->_files_only_url = $files_only_url;
        return $this;
    }


    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }

    public function setFinishedObject($finished_object){
        $this->_finished_object = $finished_object;
        return $this;
    }


    public function setTitleAddAvatar($title_add_avatar){
        $this->_title_add_avatar = $title_add_avatar;
        return $this;
    }

    public function setImgTag($img_tag){
        $this->_img_tag = $img_tag;

        return $this;
    }


    /**
     * возвращает название поля для заголовка таблицы 
     */ 
    public static function getFieldTitle($schema_params){
        $field_title = $schema_params['title'];
        
        // меняем название Участника на Ответсвенного
        if($schema_params['type'] == 'relate_participant' && $schema_params['type_view'] ==  Fields::TYPE_VIEW_BLOCK_PARTICIPANT)
            $field_title = Yii::t('base', 'Responsible');
        
        return $field_title; 
    }    
    
    
    /**
    * строит и возвращает верстку всех полей <td>..</td> для ListView
    * @param array $params               - параметры полей (ветка params)
    * @param array $value_data           - данные    
    * @param array $without_group_index  - список индексов групировки, что следует пропустить 
    * @const $primary_link               - указывает, какой линк (с каким набором атрибутов ) будет сформирован для поля с атрибутом is_primary=true 
    * @return string (html) 
    */
    public function buildListViewRow($params = null, $value_data, $without_group_index = array(), $primary_link = self::PRIMARY_LINK_EDIT_VIEW){

        if(empty($params) || empty($value_data)) return;
        if(count($params) == 0 || count($value_data) == 0) return;
        $html_result = '';
        $data_array = $this->buildHtmlListView($params, $value_data, $primary_link);

        $inline_edit_access = true;
        if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))){
            $inline_edit_access = false;
        }

        foreach($data_array as $group_index => $value){
            
            if(!empty($without_group_index) && in_array($group_index, $without_group_index)) {
                continue;
            }

            if($this->_schema_params_groups[$group_index]['inline_edit'] && !$inline_edit_access){
               $this->_schema_params_groups[$group_index]['inline_edit'] = false;
            }

            $html_result.= '<td class="'.
                            ($this->_schema_params_groups[$group_index]['list_view_display'] != true ? 'hidden ' : '').
                            ($this->_schema_params_groups[$group_index]['inline_edit'] == true ? 'data_edit' : '').
                            '" >' . $value . '</td>';
        }
        return $html_result;
        
    }


    /**
    * строит и возвращает верстку полей для ListView
    * @param array $params        - параметры полей (ветка params)
    * @param array $value_data    - данные    
    * @const $primary_link        - указывает, какой линк (с каким набором атрибутов ) будет сформирован для поля с атрибутом is_primary=true 
    * @return string (html) 
    */
    public function buildHtmlListView($params = null, $value_data, $primary_link = self::PRIMARY_LINK_EDIT_VIEW){
        if(empty($params) || empty($value_data)) return;
        if(count($params) == 0 || count($value_data) == 0) return;

        return $this->buildListViewDataArray($params, $value_data, $primary_link);
    }



    /**
    * возвращает массив html отображения данных для ListView
    * @param array $params        - параметры полей (ветка params)  
    * @param array $value_data    - данные    
    * @const $primary_link        - указывает, какой линк (с каким набором атрибутов ) будет сформирован для поля с атрибутом is_primary=true 
    * @return array(
                'header' => array(), - поля в групированом виде
                'params' => array(), - ветка params каждого поля
                )
    */
    public function buildListViewDataArray($params = null, $value_data, $primary_link = self::PRIMARY_LINK_EDIT_VIEW){
        if(empty($params) || empty($value_data)) return array();
        if(count($params) == 0 || count($value_data) == 0) return array();
        $html_result = array();

        $lich = 0;
        $element_title = '';
        $element_value = '';
        $element_value_tmp = '';
        $group_index_tmp = '';
        $element_params_tmp = array();
        $be_primary_field = SchemaOperation::getInstance()->beEditIsPrimary($params);
        self::$primary_link_aded = false;
        
        // в цикле реализовано обьединение полей, что имеют одно название
        foreach($params as $param_value){
            $lich++;
            $group_index = $param_value['group_index'];
            $element_value = $this->getViewElememnt($param_value, $value_data, $primary_link, $be_primary_field);
            if($lich == count($params)){
                if(count($params) == 1){
                    $html_result[$group_index] = $element_value;
                    $this->_schema_params_groups[$group_index] = $param_value;
                } else {
                    if($element_title == $param_value['group_index']){
                        $element_value_tmp .=  (empty($element_value_tmp) ? $element_value : ' ' . $element_value);
                        $group_index_tmp = $group_index;
                        $element_params_tmp = $param_value;
                        $html_result[$group_index_tmp] = $element_value_tmp;
                        $this->_schema_params_groups[$group_index_tmp] = $element_params_tmp;
                    } else {
                        $html_result[$group_index_tmp] = $element_value_tmp;
                        $this->_schema_params_groups[$group_index_tmp] = $element_params_tmp;
                        $html_result[$group_index] = $element_value;
                        $this->_schema_params_groups[$group_index] = $param_value;
                    }
                } 
            }
            elseif($lich == 1 || $element_title == $param_value['group_index']){
                $element_value_tmp .=  (empty($element_value_tmp) ? $element_value : ' ' . $element_value);
                $group_index_tmp = $group_index;
                $element_params_tmp = $param_value;
                $element_title = $param_value['group_index'];
            } else {
                $html_result[$group_index_tmp] = $element_value_tmp;
                $this->_schema_params_groups[$group_index_tmp] = $element_params_tmp;
                $element_value_tmp = $element_value;
                $group_index_tmp = $group_index;
                $element_params_tmp = $param_value;
                $element_title = $param_value['group_index'];
            }
        }
        return $html_result;
    }
    
    
    /**
     * возвращает отображение данных для ListView
     * @param array $params        - параметры полей (ветка params)  
     * @param array $value_data    - данные    
     * @const $primary_link        - указывает, какой линк (с каким набором атрибутов ) будет сформирован для поля с атрибутом is_primary=true 
     */
    public function getViewElememnt($params, $value_data, $primary_link = self::PRIMARY_LINK_EDIT_VIEW, $be_primary_field = false){
        if(!$params['is_primary']){
            $this->_relate_add_avatar = true;
        }
        
        $html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                                  array(
                                    'extension_copy' => $this->_extension_copy,
                                    'params' => $params,
                                    'value_data' => $value_data,
                                    'primary_link' => $primary_link,
                                    'be_primary_field' => $be_primary_field,
                                    'title_add_avatar' => $this->_title_add_avatar,
                                    'files_only_url' => $this->_files_only_url,
                                    'relate_add_avatar' => $this->_relate_add_avatar,
                                    'avatar_view_responsible' => $this->_avatar_view_responsible,
                                    'blocks' => $this->blocks,
                                    'this_template' => $this->_this_template,
                                    'finished_object' => $this->_finished_object,
                                    'img_tag' => $this->_img_tag,
                                   ), true);

        if($params['is_primary'] && $this->_relate_add_avatar){
            $this->_relate_add_avatar = false;
        }

        return $html;
    }
    
    
    
    
    
    /**
     * поиск и сравнивание параметров поля с массивом полей для исключения
     * @return boolean - если присутствует 
     */
    public function findExcludeField($params, $without_field_list){
        $result = false;
        if(empty($without_field_list)) return $result;
        foreach($without_field_list as $field){
            if(is_array($field)){ 
                // сравниваем по type и type_view
                if($params['params']['type'] == $field['type'] && $params['params']['type_view'] == $field['type_view']){
                    $result = true;
                    break;
                }
            } else {
                // сравниваем по type
                if($params['params']['type'] == $field){
                    $result = true;
                    break;
                }
            }
            
        }
        return $result;
    } 
    
}
