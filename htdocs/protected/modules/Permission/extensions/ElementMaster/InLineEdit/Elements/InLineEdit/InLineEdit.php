<?php
/**
* InlineEdit widget  
* @author Alex R.
* @version 1.0
*/ 

class InlineEdit extends CWidget{

    // Отображдение
    public $view = 'element';
    // Схема
    public $params;
    //extensionCopyModel
    public $extension_copy;
    // pci
    public $parent_copy_id;
    //
    public $this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    
    
    public function init()
    {
        $this->render($this->view, array(
                                    'params' => $this->params,
                                    'extension_copy' => $this->extension_copy,
                                    'parent_copy_id' => $this->parent_copy_id,
                                    'this_template' => $this->this_template,
                                 )
        );
    }
 

}