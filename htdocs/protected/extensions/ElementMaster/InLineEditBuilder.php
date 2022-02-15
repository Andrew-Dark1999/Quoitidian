<?php

/**
 * InLineEditBuilder - Мастер динамических полей модуля EditView
 * Мастер динамических полей модуля
 */


class InLineEditBuilder{
    
    //  экземпляр ExtensionCopyModel
    private $_extension_copy;
    // pci
    private $_parent_copy_id;
    
    //_this_template
    private $_this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    
    
    public static function getInstance(){
        return new self;
    }

    
    /**
    *  экземпляр ExtensionCopyModel
    * @return this 
    */
    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    } 



    /**
    *  pci
    * @return this 
    */
    public function setParentCopyId($parent_copy_id){
        $this->_parent_copy_id = $parent_copy_id;
        return $this;
    } 


    /**
    *  this_template
    * @return this 
    */
    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    } 




    /**
    * строит елементы полей 
    * @return array() 
    */
    public function buildElementJSArray($schema){
        if(empty($schema)) return false;
        if(count($schema) == 0) return false;
        
        $result = array();
        foreach($schema as $params){
            if($params['inline_edit'] != true) continue;

            $denied_relate = SchemaOperation::getDeniedRelateCopyId(array($params));    
            if($denied_relate['be_fields'] == false) continue;
            
            $read_only = (!empty($params['read_only'])) ? 1 : 0;
            
            $result[$params['name']] = array(
                    'type' => $params['type'],
                    'type_view' => $params['type_view'],
                    'readonly' => $read_only,
                    'element' => $this->getElement($params)
                );
        }
        return $result;
    }
    
    
    

    /**
    * Возвращает елемент поля
    * @return string (html)  
    */
    public function getElement($params){
        if(empty($params)) return;

        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit'),
                                   array(
                                    'params' => $params,
                                    'extension_copy' => $this->_extension_copy,
                                    'parent_copy_id' => $this->_parent_copy_id,
                                    'this_template' => $this->_this_template, 
                                   ),
                                   true);
    }


   
}
