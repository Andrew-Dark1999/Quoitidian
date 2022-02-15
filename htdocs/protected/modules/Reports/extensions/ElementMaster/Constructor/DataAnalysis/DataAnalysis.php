<?php
/**
* DataAnalysis widget - Данные для анализа
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\ElementMaster\Constructor\DataAnalysis;

class DataAnalysis extends \CWidget{
 

    public $views = array('block', 'block.block');
    public $schema = null;  //array();
    public $element = null;
    

    
    public function init(){
        if(empty($this->views)) return;
        $result =  '';
        foreach($this->views as $view){
            switch($view){
                case 'block.block' :
                    $data = array(
                                'schema' => $this->schema,
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

                case 'module-params' :
                    $result = $this->render('module-params', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;
                
                case 'panel' :
                    $params_hidden = json_encode(array(
                                        'type' => $this->element['type'],
                                        'remove' => $this->element['remove'],
                                        'drag_marker' => $this->element['drag_marker'],
                    ));

                    $result = $this->render('panel', array('schema' => $this->schema, 'element' => $this->element, 'params_hidden' => $params_hidden), true);
                    break;

                case 'settings' :
                    $active_module_copy_id = null;
                    if(!empty($this->schema['data']['indicator']['modules']))
                    foreach($this->schema['data']['indicator']['modules'] as $module){
                        if($this->element['module_copy_id'] === null) continue;
                        if(empty($module['fields'])) continue;
                        if($this->element['module_copy_id'] !== null && $module['module_copy_id'] != $this->element['module_copy_id']) continue;
                        
                        $active_module_copy_id = $module['module_copy_id'];
                        break;
                    }





                    $type_indicator = null;
                    if($this->element['type'] == 'data_analysis_param'){
                        $type_date = \Reports\models\ConstructorModel::getInstance()->getTypeDateList($active_module_copy_id);
                    } else if($this->element['type'] == 'data_analysis_indicator'){
                        $type_indicator = \Reports\models\ConstructorModel::getInstance()->getTypeIndicator($this->element);
                        $type_date = null;
                    }
                    
                    $result = $this->render('settings', array('schema' => $this->schema, 'element' => $this->element, 'type_indicator' => $type_indicator, 'type_date' => $type_date, 'active_module_copy_id' => $active_module_copy_id), true);
                    break;

                case 'setting-param-fields' :
                    $result = $this->render('setting-param-fields', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;

                case 'setting-indicator-fields' :
                    $result = $this->render('setting-indicator-fields', array('schema' => $this->schema, 'element' => $this->element), true);
                    break;

            }
        }

        echo $result;
    }
 




 
 


}
