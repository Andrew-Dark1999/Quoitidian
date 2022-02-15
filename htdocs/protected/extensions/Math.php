<?php

class Math {


    /**
    *   Возможные операнды
    */
    public static $operands = array('+' , '-', '*', '/');
    
    
    /**
    *   Объект для работы с математической библиотекой
    */
    private $_object;

    
    /**
    *   Запись формулы вида =+1, вместо =1+1. Второй используется по-умолчанию
    */
    private $operator_after_equal = false;
    
    
    /**
    *   Математическое выражение, которое рассчитываем
    */
    private $expression = false;
    
    
    
    public function __construct(){
        
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import('ext.Math.*', true);
        
        require_once('Parser.php');
        require_once('Lexer.php');
        require_once('Token.php');
        require_once('Operator.php');
        require_once('TranslationStrategy/TranslationStrategyInterface.php');
        require_once('TranslationStrategy/ShuntingYard.php');
        
        $this->_object = new \Math\Parser();
        
        spl_autoload_register(array('YiiBase','autoload'));

        
    }


    public static function getInstance(){
        return new self();
    }

    
    public function setOperatorAfterEqual($operator_after_equal){
        $this->operator_after_equal = $operator_after_equal;
        return $this;
    }
    
    
    /**
     *   Запуск общих правил корректности выражения
     */
     public function setRules($expression){
         
         //выражение не должно быть пустым
         if(!empty($expression)) {
             
            //первый знак должен быть символ "="
            if($expression[0] == '=') {
               
                //не меньше трех знаков
                if(mb_strlen($expression)>=3) {
                
                      if($this->operator_after_equal) {
                          
                           //после знака "=" должен идти знак оператора
                           if(in_array($expression[1], self::$operands))
                              $this->expression = $expression;
                          
                       }else
                           $this->expression = $expression;
                }
            }    
        }

        return $this;
    }
     
    
     public function getExpression(){
         return $this->expression;
     }
     
     public function setExpression($expression){
         $this->expression = $expression;
         return $this;
     }
     
    
    /**
     *   Подготовливаем мат. выражение
     */
    public function preparedExpression(){
        
        if($this->expression) {
       
            //пропускаем первый символ (признак формулы)
            $this->expression = mb_substr($this->expression, 1);
            
            //разделяем дополнительно операнды пробелами
            //$this->expression = chunk_split($this->expression, 1, ' ');
            $expression = '';
            for($i=0; $i<strlen($this->expression); $i++){
                
                if(is_numeric($this->expression[$i]) || $this->expression[$i]=='.') {
                   $expression .= $this->expression[$i];
                }else {
                   $expression .=  ' ' . $this->expression[$i];
                   //проверяем следущий знак, если это число, то добавляем пробел и после строки
                   if(isset($this->expression[$i+1]) && (is_numeric($this->expression[$i+1]) || $this->expression[$i+1]=='.'))
                       $expression .= ' ';
                }

            }
              
            $this->expression = $expression;
              
        }
        
        return $this;
    }
    
   
   /**
    *   Получаем рсчет по формуле
    */
    public function evaluate($expression){

        return($this->_object->evaluate($expression));

    }
    
   
   /**
    *   Получаем результат формулы
    */
    public function getCalculatedValue($operand=0){

        $result = false;
        
        if($this->expression) {
            
            set_error_handler(function () {
                
            });
            
            try {

                $this->expression = $operand . ' ' . $this->expression;
                $result = $this->_object->evaluate($this->expression);

            } catch (Exception $e) {
                //division by error

            }
            
            restore_error_handler();
        }

        return $result;
    }
    

}




