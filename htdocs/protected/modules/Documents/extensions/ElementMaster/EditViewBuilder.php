<?php

/**
 * EditViewBuilder - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */

namespace Documents\extensions\ElementMaster;

class EditViewBuilder extends \EditViewBuilder{
    
    
    /**
    * Возвращает елемент "Тип поля" (Edit)
    * @return string (html)  
    */
    public function getEditViewElementEdit($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;

        $default_data = null; 

        if($this->_extension_data->isNewRecord || $this->_default_data_from_template){
            $default_data = $schema['params']['default_value'];
            if($this->_default_data !== null && isset($this->_default_data[$schema['params']['name']]))
                $default_data = $this->_default_data[$schema['params']['name']];
        }
        
        if($this->_only_one_block_select && $this->_default_data_from_linked_card) {
            if(isset($this->_default_data_from_linked_card[$schema['params']['name']]))
                $default_data = $this->_default_data_from_linked_card[$schema['params']['name']];   
        }
        
        $showGenerateUrl = false;

        //if(@$_POST['from_template'] && in_array($schema['params']['type'], array('file', 'file_image')))
        if((@$_POST['parent_copy_id']>0 || @$_POST['from_template'] ) && in_array($schema['params']['type'], array('file', 'file_image'))){
            //модуль Документы, шаблон
            //теперь проверка на скрытый атрибут
            if(isset($schema['params']['file_generate']))
                if($schema['params']['file_generate'])
                    $showGenerateUrl = true;

        }    
        $result = \Yii::app()->controller->widget(\ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
           array(
            'schema' => $schema,
            'primary_entities' => $this->_primary_entities,
            'parent_copy_id' => $this->_parent_copy_id,
            'parent_data_id' => $this->_parent_data_id,
            'parent_relate_data_list' => $this->_parent_relate_data_list,
            'extension_copy' => $this->_extension_copy,
            'extension_data' => $this->_extension_data,
            'default_data' => $default_data,
            'this_template' => $this->_this_template,
            'relate' => $this->_relate,
            'show_generate_url' => $showGenerateUrl,
            'only_one_block_select' => $this->_only_one_block_select,
            'builder_model' => $this,
           ),
           true);

           
        return $result;
    }



}
