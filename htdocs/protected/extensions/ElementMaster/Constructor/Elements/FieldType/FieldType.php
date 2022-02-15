<?php
/**
* FieldType widget  
* @author Alex R.
* @version 1.0
*/ 

class FieldType extends CWidget{

    // Схема
    public $schema;
    // Контент
    public $content;
    // Внутренний контент
    public $field_type_params = '';
    // Отображдение
    public $view = 'element';
    // Список типов для отображения     
    public $fields_type = array();

    public function init()
    {
        $this->render($this->view, array(
                                    'schema' => $this->schema,
                                    'content' => $this->content,
                                    'fields_type' => $this->fields_type,
                                    'field_type_params' => $this->field_type_params,
                                 )
        );
    }
 

}