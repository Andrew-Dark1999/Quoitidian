<?php
/**
* Block widget  
* @author Alex R.
* @version 1.0
*/ 

class Block extends CWidget{

    // Схема
    public $schema;
    // Внутренний контент 
    public $content;
    
    //для обертки блока
    public $start_wrapper = false;
    public $finish_wrapper = false;
    
    public function init()
    {
        $this->render('element', array(
                                    'schema'=>$this->schema,
                                    'content' => $this->content,
                                    'start_wrapper' => $this->start_wrapper,
                                    'finish_wrapper' => $this->finish_wrapper,
                                 )
        );
    }
 

}