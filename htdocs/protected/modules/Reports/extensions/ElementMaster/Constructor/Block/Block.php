<?php
/**
* Block widget - Набор показателей
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Constructor\Block;

class Block extends \CWidget{
    
    public $data;
    
    
    
    public function init(){
        $result = $this->render('block',
                    array(
                        'data' => $this->data,
                    ),
                    true 
        ); 
        echo $result;
    }
    
    


    
    
    
    
 
}

