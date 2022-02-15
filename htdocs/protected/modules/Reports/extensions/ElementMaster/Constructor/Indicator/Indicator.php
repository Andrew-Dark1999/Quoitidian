<?php
/**
* Indicator widget - Набор показателей
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Constructor\Indicator;


/**
 * Indicator
 * 
 * @package crm
 * @author alex1
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class Indicator extends \CWidget{

    public $views = array('panels', 'block', 'block.block');
    public $schema = null;  //array();
    public $element = null;
    
    public function init(){
        if(empty($this->views)) return;
        $result = '';

        foreach($this->views as $view){
            switch($view){
                case 'block.block' :
                    $data = array(
                                'schema' => $this->schema,
                                'setting' => $this->render('block-block-setting',
                                                           array(
                                                                'schema' => $this->schema,
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
                    
                    $result = $this->render('block', array('schema' => $this->schema, 'params_hidden' => $params_hidden), true);
                    break;
                                                            
                case 'panel' :
                    $result = $this->render('panel', array('schema' => $this->schema, 'element' => $this->element), true);
                    
                    break;

                case 'block-block-setting-indicator' :
                    if(!isset($this->element) || empty($this->element)) break;
                    $params_hidden = json_encode(array(
                                        'type' => $this->element['type'],
                                        'remove' => $this->element['remove'],
                    ));

                    $result = $this->render('block-block-setting-indicator',
                                                   array(
                                                    'schema' => $this->schema,
                                                    'element' => $this->element,
                                                    'params_hidden' => $params_hidden,
                                                   ),
                                                   true
                                                   );  
                    break;   
            } 
        }
        
        echo $result;
    }
 
 
 

 

}