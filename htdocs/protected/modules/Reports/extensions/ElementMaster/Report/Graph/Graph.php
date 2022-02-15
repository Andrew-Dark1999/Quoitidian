<?php
/**
* Graph widget - графики
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Report\Graph;

class Graph extends \CWidget{
 
 
    public $views = array('block');
    public $schema = null;
    public $element = null;
    public $select_indicator = null;
    public $element_remove = true;
    

    
    public function init(){
        if(empty($this->views)) return;
        $result =  '';
        foreach($this->views as $view){
            switch($view){
                case 'block_setting' :
                    $result = $this->render('block-setting',
                                                   array(
                                                    'schema' => $this->schema,
                                                    'element' => $this->element,
                                                   ),
                                                   true
                                                   );  
                    break;   

                case 'block_setting_indicator' :
                    $result = $this->render('block-setting-indicator',
                                                   array(
                                                    'schema' => $this->schema,
                                                    'element' => $this->element,
                                                    'select_indicator' => $this->select_indicator,
                                                    'element_remove' => $this->element_remove,
                                                   ),
                                                   true
                                                   );  
                    break;   

                case 'block' :
                    $result= $this->render('block', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;
                    
                case 'graph_element' :
                    $result = $this->render('graph', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;
            }
        }

        echo $result;
    }
  
  
  

}