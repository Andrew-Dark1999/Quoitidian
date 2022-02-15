<?php
/**
* CalculatedFields класс для типа полей "Вычисляемое поле"  
* @author Alex B.
*/ 

class CalculatedFields{

    
    //типы валидации
    const FORMULA_VALIDATE_NONE     = false;    //не использовать валидацию
    const FORMULA_VALIDATE_SIMPLY   = 'simply'; //простая валидация, обычные формулы + поля модуля. пример: 1+2*$field1
    
    //регулярное выражение, для парсинга PHP переменных
    const PATTERN_PHP_VARIABLES     = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
    
    //для получения текст перед скобками и после. пример xyz(zyx)
    const PATTERN_PARENTHESES       = '/([a-zA-Z]+)\((.*)\)/';
    
    //для получения условия
    const PATTERN_SIMPLY_COND       = '/(.*)([<=>])(.*)/';
    
    //максимальный уровень связи
    const MAX_LEVEL_LINK            = 4;
    
    /**
    *   Возможные агрегатные функции
    */
    public static $allowable_aggregate_functions = array('SUM', 'MIN', 'MAX', 'AVG', 'VALUE');
    
    private $_extension_copy = null;
    
    //использование по умолчанию extension_data
    private $_used_extension_data = true;
    private $_extension_data = null;
    
    private $_values_data = null;
    private $_field_name = null;
    private $_formula = null;
    private $_validation_type = false;
    
    //использование агрегатных функций
    private $_use_aggregate_functions = false;

    public static function getInstance(){
        return new self;
    }

    
    /**
    * Валидация формулы
    * @param $formula string
    * @return boolean
    */
    public function validate($formula){
        
        if($this->_validation_type == self::FORMULA_VALIDATE_NONE)
            return true;
        
        $this->_formula = $formula;
        
        switch($this->_validation_type) {
        
            case self::FORMULA_VALIDATE_SIMPLY:
                return $this->validateSimply();
            break;
        
        }
        
        return false;
    }

    private function validateSimply(){
        
        $this->validateAggregateFunctions();

        $formula_fields = preg_match_all(self::PATTERN_PHP_VARIABLES, $this->_formula, $matches);
        
        if($formula_fields) {
            $module_fields = $this->_extension_copy->getFieldSchemaParamsByType(\Fields::MFT_NUMERIC, null, false);
            if($module_fields) {
                foreach($module_fields as $field) {
                    if(array_search($field['params']['name'], $matches[1])!==false) {
                        $this->_formula = str_replace('$'.$field['params']['name'], '0', $this->_formula);
                    }
                }
            }
        }
        
        $value = \Math::getInstance()
                    ->setRules($this->_formula)
                    ->preparedExpression()
                    ->getCalculatedValue();
        
        return ($value === false) ? false : true;
    }
    
    
    /**
    * Валидация корректности агрегатных функций
    * @return 
    */
    private function validateAggregateFunctions(){
        //используем подобную проверку
        if($this->_use_aggregate_functions) {
            preg_match(self::PATTERN_PARENTHESES, $this->_formula, $matches);
            if($matches) {
                //используется допустимая формула
                if(in_array($matches[1], self::$allowable_aggregate_functions) && !empty($matches[2])){
                    //$matches[0] - полностью формула + значение
                    //$matches[1] - формула
                    //$matches[2] - значение внутри формулы
                    if($this->checkAF($matches[2])) {
                        $this->_formula = trim(str_replace($matches[0], '', $this->_formula));
                        if($this->_formula == '=')
                            $this->_formula .= '0+0';
                    }else {
                        $this->_formula = false;
                    }
                }else
                    $this->_formula = false;
 
            }   
        }
    }
    
    /**
    * Проверка условий функций
    * Формат:  $<поле>;<условие1>;<условие2>
    * @param $s string
    * @return boolean
    */
    private function checkAF($s){
        
        $op = explode(';', $s);
        
        if(!$this->checkField($op[0]))
            return;
        
        //проверяем условия
        if(count($op)>1) {
            
            $i = 0;
            foreach($op as $condition) {
                $i++;
                if($i==1)
                    continue;
                
                //var_dump($condition);
                preg_match(self::PATTERN_SIMPLY_COND, $condition, $matches);
                if($matches) {
                    //$matches[0] - полностью условие
                    //$matches[1] - левая часть (поле)
                    //$matches[2] - знак
                    //$matches[3] - значение
                    if((strpbrk($matches[1], '<=>')) || (strpbrk($matches[3], '<=>'))){
                        //несколько знаков, так не должно быть
                        return;
                        exit();
                    }
                    //теперь проверяем поле
                    if(!$this->checkField($matches[1])) {
                        return;
                        exit();
                    }
                }else {
                    //мат. знаков сравнения не найдено в условии
                    return;
                    exit();
                }
            }
        }
        
        return true;
    }
    
    
    /**
    * Проверка корректности введенного поля в формуле
    * @param $field string
    * @return array
    */
    private function checkField($field){
        
        //значение не может быть пустым
        if(trim($field)=='' || $field[0]!='$')
            return;
        
        //проверяем корректность поля
        $flds = explode('.', $field);
            
        $flds[0] = substr($flds[0], 1, strlen($flds[0]));
        
        if(count($flds)>self::MAX_LEVEL_LINK)
            return;
        
        //указано просто поле, без алиаса
        if(count($flds)==1){
           $schema_params = $this->_extension_copy->getFieldSchemaParams($flds[0]);
           if(empty($schema_params) || $schema_params['params']['type'] != \Fields::MFT_NUMERIC)
               return;
        }else {
            //указана цепочка модулей (алиасов)
            $fld = array_pop($flds);
            $fls_module_alias = array_pop($flds);
            
            //остальные модули по цепочке, просто проверяем наличие алиаса
            if(count($flds)){
                foreach($flds as $fd) {
                    $extension_copy = ExtensionCopyModel::model()->findByAttributes(array('alias'=>$fd));
                    if(empty($extension_copy)){
                        return;
                        exit();
                    }
                }
            }
            
            //проверяем поле и его модуль
            $extension_copy = ExtensionCopyModel::model()->findByAttributes(array('alias'=>$fls_module_alias));
            if(empty($extension_copy))
                return;
            
            $schema_params = $extension_copy->getFieldSchemaParams($fld);
            if(empty($schema_params) || $schema_params['params']['type'] != \Fields::MFT_NUMERIC)
                return;
           
        }

        return true;
    }
    

    /**
    * Установка типа валидации формулы
    * @param $type string
    * @return array
    */
    public function setValidationType($type){
        $this->_validation_type = $type;
        return $this;
    }

    
    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }
    
    
    public function setExtensionData($extension_data){
        $this->_extension_data = $extension_data;
        return $this;
    }
    
    public function setUseAggregateFunctions($use_aggregate_functions){
        $this->_use_aggregate_functions = $use_aggregate_functions;
        return $this;
    }
    
    /**
    * Установка значений полей
    * @param $value array
    */
    public function setValuesData($value){
        $this->_used_extension_data = false;
        $this->_values_data = $value;
        return $this;
    }
    
    public function setFieldName($field_name){
        $this->_field_name = $field_name;
        return $this;
    }
    
    public function prepareFormula(){
        
        $params = $this->_extension_copy->getFieldSchemaParams($this->_field_name);
        if(!empty($params['params']['formula']))
            $this->_formula = $params['params']['formula'];
        
        return $this;
    }
    
    public function getFormula(){
        return $this->_formula;
    }
    
    /**
    * Возвращает данные, необходимые для построения SQL запроса
    * @param $value array
    */
    public function getSelectFields(){
        $formula = substr($this->_formula, 1, strlen($this->_formula));
        
        //обработка агрегатных функций
        preg_match(self::PATTERN_PARENTHESES, $formula, $matches);
        if($matches) {
            //найдена агрегатная функция, ищем значение для нее
            $formula = trim(str_replace($matches[0], $this->getAggregateValue($matches[0]), $formula));
        }

        //ищем переменные, если находим, добавляем к ним префикс модуля
        $formula_fields = preg_match_all(self::PATTERN_PHP_VARIABLES, $this->_formula, $matches);
        
        if($formula_fields) {
            foreach($matches[1] as $variable) {
                $formula = str_replace('$'.$variable, $this->_extension_copy->getTableName() . '.' . $variable, $formula);
            }

        }

        if($formula){
            return '(' . $formula . ') as ' . $this->_extension_copy->prefix_name . '_' . $this->_field_name . '_value';
        }
    }
    
    
    /**
    * Получаем значение из агрегатной функции
    * @param $value int
    */
    private function getAggregateValue($formula){
        
        $result = 0;
    
    
        return $result;
    }
    
    
    /**
    * Возвращает формулу, адаптированную под SQL, используется в фильтрах
    * @param $value array
    */
    public function getFieldCondSQL(){
        
        $field = substr($this->_formula, 1, strlen($this->_formula));
        
        //ищем переменные, если находим, убираем $
        $formula_fields = preg_match_all(self::PATTERN_PHP_VARIABLES, $this->_formula, $matches);
        
        if($formula_fields) {
            foreach($matches[1] as $variable) {
                $field = str_replace('$'.$variable, $variable, $field);
            }
        }
        return $field;
    }
     
    public function getValue(){
        
        if(empty($this->_formula))
            return;

        $formula_fields = preg_match_all(self::PATTERN_PHP_VARIABLES, $this->_formula, $matches);
        
        if($formula_fields) {
            foreach($matches[1] as $variable) {
                
                if($this->_used_extension_data) {
                    $value = (isset($this->_extension_data->{$variable})) ? $this->_extension_data->{$variable} : 0;
                }else{
                    $value = (isset($this->_values_data[$variable])) ? $this->_values_data[$variable] : 0;
                }
                
                $this->_formula = str_replace('$'.$variable, $value, $this->_formula);
            }
        }
        
        $value = \Math::getInstance()
                    ->setRules($this->_formula)
                    ->preparedExpression()
                    ->getCalculatedValue();

        return ($value === false) ? false : $value;

    }
}    
