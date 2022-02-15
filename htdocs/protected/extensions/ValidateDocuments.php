<?php
/**
* ValidateDocuments - проверка на ошибки модуля Документы  
* @version 1.0
*/

class ValidateDocuments extends Validate{
    
    
    /**
     * Регулярное выражение для того, чтобы достать в скобках
     */
    const REG_TEXT_BETWEEN_BRACKETS = '/\((.+)\)/';

    /**
     * Получаем только цифры
     */
    const REG_ONLY_NUMBERS = '/[^0-9]/';
    
    public static function getInstance(){
        return new static();
    }
    
    /**
     * Пользовательский уровень ошибки
     */
    static function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        //throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    
    static function fatal_error_handler() {
        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            // fatal error
            //exception_error_handler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }
    
    
    /**
     *   Добавление сообщения
     *   @param string $message
     */
    public static function getMessage($message){
        return ValidateDocuments::parseMessage($message);
    }
    
    
    /**
     *   Парсинг сообщения
     */
    private static function parseMessage($message) {
        
        $result = $message;

        if((mb_substr($message, 0, 8)=='include(') || (mb_substr($message, 0, 18)=='file_get_contents(')) {
            
            //попытка подключение дополнительного файла, вероятно шаблона или какого-нибудь ксс
            preg_match(ValidateDocuments::REG_TEXT_BETWEEN_BRACKETS, $message, $m);
            $result = Yii::t('messages', 'Connecting the file') . ' <b>"' . $m[1] . '"</b> ' . Yii::t('messages', 'in the Smarty template failed. Try to remove the line connecting the said file from the template.'); 

        }elseif(mb_substr($message, 0, 24) == 'Syntax error in template'){
            
            //синтаксическая ошибка в шаблоне
            $result = Yii::t('messages', 'During the generation of the document occurred Smarty syntax error');
            
            //синтаксическая ошибка обычно имеет формат ниже, ориентируемся на такой формат
            //Syntax error in template "<filename>" on line 52 "<string>" description
            $t_array = explode('"', $message);
            
            if(isset($t_array[2])) 
                $result .= '<br/>' . Yii::t('messages', '№ строки:') . ' ' . preg_replace(ValidateDocuments::REG_ONLY_NUMBERS, '', $t_array[2]);
              
            if(isset($t_array[3]))   
                $result .= '<br/>' . Yii::t('messages', 'The string:') . ' ' . $t_array[3];
             
            if(isset($t_array[4])) 
                $result .= '<br/>' . Yii::t('messages', 'Description:') . ' ' . Yii::t('messages', trim($t_array[4]));
             
            if(count($t_array)>5){
                for($i = 5; $i <= count($t_array); $i++) {
                    $result .=  ' ' . Yii::t('messages', trim($t_array[$i]));
                }
            }

            //$result .= $message;
            
        }else {
            $result = Yii::t('messages', 'Во время генерации документа произошла ошибка:') . ' <br/><b>"' . $message . '"</b>';
        }
        
        return $result;
        
    }


}
