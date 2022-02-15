<?php
/**
* Attachments widget  
* @author Alex R.
* @version 1.0
*/ 

class Attachments extends CWidget{

    // Схема
    public $schema;
    

    public function init()
    {
        $this->render('element', array(
                                    'schema' => $this->schema,
                                 )
        );
    }
 

}