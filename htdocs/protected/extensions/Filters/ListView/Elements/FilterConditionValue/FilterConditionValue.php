<?php
/**
* FilterConditionValue widget  
* @author Alex R.
* @version 1.0
*/ 

class FilterConditionValue extends CWidget{

    //extension_copy
    public $extension_copy;
    // параметры схемы даного елемета
    public $schema;
    // pyfxtybq 
    public $condition_value = null;
    // данные елемента
    public $condition_value_value = '';
    //для шаблонов
    public $this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    //дополнительные атрибуты для элементов
    public $attr = array();
    
        
    
    public function init(){
        $this->render('element', array(
                                    'extension_copy' => $this->extension_copy,
                                    'schema' => $this->schema,
                                    'condition_value' => $this->condition_value,
                                    'condition_value_value' => $this->condition_value_value,
                                    'this_template' => $this->this_template,
                                    'attr' => $this->attr,
                                 )
        );
    }
 

}
