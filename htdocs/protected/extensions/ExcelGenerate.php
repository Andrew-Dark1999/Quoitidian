<?php

class ExcelGenerate {

    private $_object;

    private $_extension_copy;

    private $_schema;

    private $_data = array();



    public function __construct(){
        $this->_object = new PhpExcel();

    }


    public static function getInstance(){
        return new self();
    }

    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }

    public function setData($data){
        $this->_data = $data;
        return $this;
    }

    
    
    /**
    *   Заменяет в файле параметры {} 
    * @importFile - путь к файлу, который импортируем
    * @exportFile - конечный файл
    * @params - ассоциативный массив (key - имя пареметра (без {}), value - значение)
    */
    public function generateDocument($vars, $importFile, $exportFile, $params = array()){
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        //$objReader->setReadDataOnly(true); //не читаются стили

        $objPHPExcel = $objReader->load($importFile);

        if(count($params)>0) {

            //создаем массив соответсвий для замены необходимых переменных
            if(count($vars[1]>0)) 
                if(count($vars[2]>0))
                    $matches = $vars[2];

            $stringsParams = array();
            
            foreach($params as $k=>$v) {
                
                $kNoSpace = \DocumentsGenerateModel::getInstance()->deleteSpaces($k);

                if(is_array($v)) {

                    //это СМ запись
                    
                    /*
                    if(isset($matches[$kNoSpace])) {
                        
                        //есть в массиве соответсвий, копируем строку и заполняем значениями
                        $this->_template->cloneRow('_SM_' . $kNoSpace . ':' . $matches[$kNoSpace][0], count($v));
                        
                        $i=0;
                    
                        //перебор всех СМ записей 
                        foreach($v as $k2=>$v2) {
                            
                            $i++;
                            
                            //перебор всех полей
                            foreach($v2 as $k3=>$v3) 
                                $this->_template->setValue('_SM_' . $kNoSpace . ':' . \DocumentsGenerateModel::getInstance()->deleteSpaces($k3) . '#' . $i, $v3);
  
                        }
                    }
                    */
                    
                }else
                    $stringsParams['${' . $kNoSpace . '}'] = $v; //обычная запись

            }        

            //перебор листов
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

                $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
                                
                $sheetData = $objPHPExcel->getActiveSheet()->toArray();
                
                if(count($sheetData)>0) 
                    foreach($sheetData as $row => $column) {
                        
                        if(count($column)>0)
                            foreach($column as $k=>$v) {
                                if($v) 
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k, $row + 1, strtr($v, $stringsParams));

                            }
                    } 
                
            }
            
            $objPHPExcel->setActiveSheetIndex(0);
        
        }
        
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($exportFile);
        
        return array(
            'status' => true,
            'show_edit_link' => false,
        );
        
    }
    
    
    /**
    *   Получаем переменные из шаблона 
    */
    public function getVariablesFromTemplate($importFile){
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true); // - стили не читаем для экономии памяти 
        
        $objPHPExcel = $objReader->load($importFile);
        
        //обычные переменные, также СДМ связи
        $vars = array();
        
        //это массивы (СМ связи)
        $arrays = array();
        
        //это массивы (СМ связи) вместе с полями
        $arraysFull = array();
        
        
        //для заполнения данными xls файла
        $text = '';
        
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
                        
            $sheetData = $objPHPExcel->getActiveSheet()->toArray();
            
            if(count($sheetData)>0) 
                foreach($sheetData as $row => $column) {
                    
                    if(count($column)>0)
                        foreach($column as $k=>$v) {
                            if($v) 
                                $text .= $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($k, $row + 1)->getValue();
                        }
                } 
            
        }
        
        //парсим текст
        preg_match_all('/\$\{(.*?)}/i', $text, $matches);
        

        if(count($matches[1])>0) {
        
            foreach($matches[1] as $v) {
                
                if(substr($v, 0, 4)=='_SM_') {
                    
                    //СМ переменная
                    $v2 = \DocumentsGenerateModel::getInstance()->getSeparateSM($v);
                    
                    $arrays [] = $v2[0];
                    $arraysFull [$v2[0]] [] = $v2[1];
                    
                }else
                    $vars [] = $v; //обычная переменная
                
            }
            
            $vars = array_unique($vars);
            $arrays = array_unique($arrays);
        
            //исключаем повторение
            $vars = array_diff($vars, $arrays);
            
        }

        return array($vars, $arrays, $arraysFull);
        
    }    
    
    

}




