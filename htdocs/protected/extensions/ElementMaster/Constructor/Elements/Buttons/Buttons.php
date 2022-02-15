<?php
/**
* Buttons widget  
* @author Alex R.
* @version 1.0
*/ 




class Buttons extends CWidget{

    // Схема
    public $schema;
    // Внутренний контент 
    public $content;
    // Елемент отображения
    public $view = 'block';
    // Список параметров для елемента 
    public $field_type_params;
    
    public function init()
    {
        $this->render($this->view, array(
                                    'schema' => $this->schema,
                                    'content' => $this->content,
                                    'field_type_params' => $this->field_type_params,
                                 )
        );
        
        
    }
 

}