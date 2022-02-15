<?php
/**
* TableColumn widget  
* @author Alex R.
* @version 1.0
*/ 

class TableColumn extends CWidget{

    // Схема
    public $schema;
    // Контент
    public $content;

    
    
    public function init()
    {
        $this->render('element', array(
                                    'schema' => $this->schema,
                                    'content' => $this->content,
                                 )
        );
    }
 

}