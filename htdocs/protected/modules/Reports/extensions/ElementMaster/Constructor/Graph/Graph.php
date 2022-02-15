<?php
/**
* Graph widget - графики
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Constructor\Graph;

class Graph extends \CWidget{
 
    public $views = array('block', 'block.block');
    public $schema = null;  //array();
    public $element = null;
    

    
    public function init(){
        if(empty($this->views)) return;
        $result =  '';
        foreach($this->views as $view){
            switch($view){
                case 'block.block' :
                    $params_hidden = json_encode(array(
                                        'type' => $this->schema['elements'][0]['type'],
                                        'graph_type' => $this->schema['elements'][0]['graph_type'],
                                        'unique_index' => $this->schema['elements'][0]['unique_index'],
                    ));
                    $data = array(
                                'schema' => $this->schema,
                                'setting' => $this->render('block-block-setting',
                                                           array(
                                                                'schema' => $this->schema,
                                                                'element' => $this->schema['elements'][0], 
                                                                'params_hidden' => $params_hidden,
                                                           ),
                                                           true),
                                'content' => $result,
                            );
                            
                    $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Block\Block',
                                                                   array(
                                                                    'data' => $data,
                                                                   ),
                                                                   true);
                    break;
                
                case 'block' :
                    $params_hidden = json_encode(array(
                                        'type' => $this->schema['type'],
                                        'remove' => $this->schema['remove'],
                                        'unique_index' => $this->schema['unique_index'],
                    ));
                    $result= $this->render('block', array('schema' => $this->schema, 'element' => $this->element, 'params_hidden' => $params_hidden), true);
                    break;
                    
                case 'graph_element' :
                    $result = $this->render('graph', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;

                case 'block_block_setting_indicator' :
                    $result = $this->render('block-block-setting-indicator',
                                                   array(
                                                    'schema' => $this->schema,
                                                    'element' => $this->element,
                                                   ),
                                                   true
                                                   );  
                    break;   
            }
        }

        echo $result;
    }
 
 

}
