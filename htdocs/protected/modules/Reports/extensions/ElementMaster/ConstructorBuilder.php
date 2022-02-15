<?php

/**
* ConstructorBuilder - Мастер динамических полей модуля
* @author Alex R.
* @version 1.0
*/
namespace Reports\extensions\ElementMaster;
 


class ConstructorBuilder{
    
    const ELEMENT_INDICATOR     = 'indicator';
    const ELEMENT_GRAPH         = 'graph';
    const ELEMENT_DATA_ANALYSIS = 'data_analysis';
    const ELEMENT_FILTER        = 'filter';


    private $extension_copy;


    public static $indicator_block_added = false;
    
    
    public static function getInstance(){
        return new self;
    }
    


    public function setExtensionCopy($extension_copy){
        $this->extension_copy = $extension_copy;
        return $this;
    }



    /**
    * собирает элементы для страницы коструктора
    * @return string (html) 
    */
    public function buildConstructorPage($schema){
        if(empty($schema)) return;
        if(count($schema) == 0) return;
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
                case self::ELEMENT_DATA_ANALYSIS :
                    $result.= $this->getDataAnalysis(array('schema' => $value));
                    break;
                case self::ELEMENT_FILTER :
                    $result.= $this->getFilter(array('schema' => $value));
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
        self::$indicator_block_added = true;
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Indicator\Indicator',
                                   $params,
                                   true);
                                   
        return $result; 
    }
    

    /**
    * Возвращает элемент "График" (graph)
    * @return string (html)  
    */
    public function getGraph($params){
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Graph\Graph',
                                   $params,
                                   true);
        return $result; 
        
    }


    /**
    * Возвращает элемент "Данные для анализа" (data_analysis)
    * @return string (html)  
    */
    public function getDataAnalysis($params){
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                                   $params,
                                   true);
        return $result; 
        
    }


    /**
    * Возвращает элемент "Фильтр" (filter)
    * @return string (html)  
    */
    public function getFilter($params){
        $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Filter\Filter',
                                   $params,
                                   true);
        return $result; 
        
    }












}
