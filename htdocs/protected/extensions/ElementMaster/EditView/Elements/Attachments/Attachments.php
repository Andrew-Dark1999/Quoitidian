<?php
/**
* Attachments widget  
* @author Alex R.
* @version 1.0
*/ 

class Attachments extends CWidget{

    // Схема
    public $schema = null;
    // Контент
    public $content = null;
    // Отображдение
    public $view = 'element';
    //extensionCopyModel
    public $extension_copy = null;
    // данные елемента
    public $extension_data = null;
    // $upload_value
    public $upload_value = null;
    // кнопки управления картинкой
    public $buttons = null;
    // величина картинки миниатюры 
    public $thumb_size = 60;
    
    public function init(){
        $this->render($this->view, array(
                        'schema' => $this->schema,
                        'content' => $this->content,
                        'extension_copy' => $this->extension_copy,
                        'extension_data' => $this->extension_data,
                        'upload_value' => $this->upload_value,
                        'buttons' => $this->buttons,
                        'thumb_size' => $this->thumb_size,
                     ));
    }
 

}