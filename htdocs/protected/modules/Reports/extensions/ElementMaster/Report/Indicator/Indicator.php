<?php
/**
* Indicator widget - Набор показателей
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Report\Indicator;

class Indicator extends \CWidget{
 

    public $views = array('block');
    public $schema = null;  //array();
    public $element = null;
    
    public function init(){
        if(empty($this->views)) return;
        $result = '';

        foreach($this->views as $view){
            switch($view){
                case 'block' :
                    $result = $this->render('block', array('schema' => $this->schema), true);
                    break;
                                                            
                case 'panel' :
                    $result = $this->render('panel', array('schema' => $this->schema, 'element' => $this->element), true);
                    
                    break;
            }
        }
        
        echo $result;
    }
 
 
 


}