<?php
/**
* Panel widget  
* @author Alex R.
* @version 1.0
*/ 

class Panel extends CWidget{

    // Схема
    public $schema;
    // Внутренний контент
    public $content;
    // Отображение
    public $view = 'element';
    //extensionCopyModel
    public $extension_copy;
    // orm актуальной части данных сабмодуля для EditView
    public $extension_data;
    // copy_id родительского модуля
    
    
    public function init()
    {
        $this->render($this->view, array(
                                    'schema'=>$this->schema,
                                    'extension_copy' => $this->extension_copy,
                                    'extension_data' => $this->extension_data,
                                    'content' => $this->content,
                                    
                                 )
        );
    }
 

}