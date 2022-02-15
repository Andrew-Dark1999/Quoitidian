<?php
/**
* Panel widget  
* @author Alex R.
* @version 1.0
*/ 

class Panel extends CWidget{

    // extensionCopyModel
    public $extension_copy = null;
    // Схема
    public $schema;
    // Внутренний контент
    public $content;
    // Отображение
    public $view = 'element';
    
    
    public function init()
    {
        $this->render($this->view, array(
                                    'extension_copy' => $this->extension_copy,
                                    'schema'=>$this->schema,
                                    'content' => $this->content,
                                 )
        );
    }
 

}