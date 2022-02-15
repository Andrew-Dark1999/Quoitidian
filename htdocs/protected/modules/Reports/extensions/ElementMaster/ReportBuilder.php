<?php

/**
* ReportBuilder - Мастер динамических полей модуля
* @author Alex R.
* @version 1.0
*/
namespace Reports\extensions\ElementMaster;
 


class ReportBuilder{
    
    const ELEMENT_INDICATOR     = 'indicator';
    const ELEMENT_GRAPH         = 'graph';
    const ELEMENT_DATA_ANALYSIS = 'data_analysis';
    const ELEMENT_FILTER        = 'filter';

    private $extension_copy;

    
    public static function getInstance(){
        return new self;
    }
    


    public function setExtensionCopy($extension_copy){
        $this->extension_copy = $extension_copy;
        return $this;
    }


    private function prepareGraph(&$schema){
        
        $count = 0;
        foreach($schema as $element){
            if($element['type'] == 'graph') $count++;
        }
        if($count <= 1) return;
        $last_position = '';
        $i = 0;
        $last_i = 0;
        foreach($schema as &$element){
            $i++;
            if($element['type'] != 'graph'){
                continue;
            } 
            
            if($i === 0){
                $last_position = $element['elements'][0]['position'];
                continue;
            } else {
                if($element['elements'][0]['position'] == \Reports\models\ConstructorModel::GRAPH_POSITION_BOTTON && $last_position === \Reports\models\ConstructorModel::GRAPH_POSITION_LEFT){
                    $element['elements'][0]['position'] = \Reports\models\ConstructorModel::GRAPH_POSITION_RIGHT;                            
                }
                if($element['elements'][0]['position'] == \Reports\models\ConstructorModel::GRAPH_POSITION_RIGHT && $last_position === \Reports\models\ConstructorModel::GRAPH_POSITION_BOTTON){
                     $schema[$last_i-1]['elements'][0]['position'] = \Reports\models\ConstructorModel::GRAPH_POSITION_LEFT;                            
                }
            } 

            $last_position = $element['elements'][0]['position'];
            $last_i = $i;
        }
    }



    /**
    * собирает элементы для страницы коструктора
    * @return string (html) 
    */
    public function buildConstructorPage($schema){
        if(empty($schema)) return;
        if(count($schema) == 0) return;
        $this->prepareGraph($schema);
        $result = '';
        foreach($schema as $value){
            if(isset($value['type']))
            switch ($value['type']){
                case self::ELEMENT_INDICATOR :
                    $result.= $this->getIndicator(array('schema' => $value)); 
                    break;
                case self::ELEMENT_GRAPH :
                    $result.= $this->getGraph(array('schema' => $value));
                    break;
            }
        }
        return $result;
    }
    

    
    
    
    /**
    * Возвращает элемент "Набор показателей" (indicator)
    * @return string (html)  
    */
    public function getIndicator($params){
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Indicator\Indicator',
                                   $params,
                                   true);
                                   
        return $result; 
    }
    

    /**
    * Возвращает элемент "График" (graph)
    * @return string (html)  
    */
    public function getGraph($params){
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Graph\Graph',
                                   $params,
                                   true);
        return $result; 
        
    }

 







}
