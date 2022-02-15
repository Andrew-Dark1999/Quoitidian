<?php
/**
* SubModule widget  
* @author Alex R.
* @version 1.0
*/ 

class SubModule extends CWidget{

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