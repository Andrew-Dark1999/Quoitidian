<?php

class WordGenerate {

    private $_template;

    private $_extension_copy;

    private $_data = array();


    public function __construct(){
        
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import('ext.PHPWord.Autoloader', true);
        \PhpOffice\PhpWord\Autoloader::register();
        
        spl_autoload_register(array('YiiBase','autoload'));
        
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
    * @import_file - путь к файлу, который импортируем
    * @upload_model - модель экспортирумого файла
    * @params - ассоциативный массив (key - имя пареметра (без {}), value - значение)
    */
    public function generateDocument($vars, $import_file, $upload_model, $params = array(), $new_filename = false){
        
        $this->_template = new \PhpOffice\PhpWord\TemplateProcessor($import_file);
        
        //fix for 2007 word
        $this->_template->fixBrokenVariables();

        if(count($vars['simple_variables'])){
            if(in_array('DEBUG_PARAMS', $vars['simple_variables'])) {
                
                print_r($vars);
                print_r($params);
                die();
            }
        }   
        
        if(count($params)>0) {
            
            //дополнительная обработка СМ записей. создаем массив соответсвий для замены необходимых переменных
            if(count($vars['sm_1st_variables']>0)) {
            
                $matches = array();

                $tVars = $this->_template->getVariables();
                
                foreach($tVars as $var) {
                    
                    if(substr($var, 0, 4)=='_SM_') {
                        
                        $v2 = \DocumentsGenerateModel::getInstance()->getSeparateSM($var);
                        
                        foreach($vars['sm_1st_variables'] as $cVar) {
                                
                            if($cVar == $v2[0])
                                $matches [$cVar][] = $v2[1];
      
                        }
                    }
                    
                }
                //print_r($matches);  
            }
            
            foreach($params as $k=>$v) {
                
                $kNoSpace = \DocumentsGenerateModel::getInstance()->deleteSpaces($k);

                if(is_array($v)) {

                    //это СМ запись
                    
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
                    
                }else
                    $this->_template->setValue($kNoSpace, $v); //обычная запись

            }
            
        }
        
        //$this->_template->setValueAdvanced($params);
        
        //получаем текущие данные
        $filename = pathinfo($upload_model->file_name);
        $filetitle = pathinfo($upload_model->file_title);

        //удаляем первоначальный шаблон и сохраняем новый файл 
        $path = ParamsModel::model()->titleName('upload_path_module')->find()->getValue() .  DIRECTORY_SEPARATOR . $upload_model->file_path;

        if(file_exists($path . DIRECTORY_SEPARATOR . $upload_model->file_name))
            @unlink($path . DIRECTORY_SEPARATOR . $upload_model->file_name);
        
        if($new_filename) {
            $filename['filename'] = Translit::forFileName($new_filename);
            $filetitle['filename'] = $new_filename;
        }   
        
        $file = $path . DIRECTORY_SEPARATOR . $filename['filename'] . '.' . $filename['extension'];

        $this->_template->saveAs($file);

        //меняем запись в БД
        $time = date('Y-m-d H:i:s');
        DataModel::getInstance()->Update('{{uploads}}', array('file_date_upload' => $time, 'date_create' => $time, 'file_name' => $filename['filename'] . '.' . $filename['extension'], 'file_title' => $filetitle['filename'] . '.' . $filename['extension']), 'id = ' . $upload_model->id);

        $p_time = date('d', strtotime($time)) . ' ' . 
                         mb_strtolower(Yii::t('base', date('F', strtotime($time)),2), 'utf-8') . ' ' .
                         Yii::t('base', 'in') . ' ' . 
                         date('H:i', strtotime($time));
        
        return array(
            'status' => true,
            'link' => '/' . $file,
            'title' => $filetitle['filename'] . '.' . $filename['extension'], 
            'filedate' => $p_time,
            'show_edit_link' => false,
        );
    }
    
    
   /**
    *   Получаем переменные из шаблона 
    */
    public function getVariablesFromTemplate($importFile, $gen_module_id){
        
        $this->_template = new \PhpOffice\PhpWord\TemplateProcessor($importFile);
        $this->_template->fixBrokenVariables();
        
        //обычные переменные, также СДМ связи
        $vars = array();
        
        //это массивы (СМ связи)
        $arrays = array();
        
        //СМ, которые ищем через поле is_main или через отдельный попап
        $m_arrays = array();
        
        $docxVars = $this->_template->getVariables();
        
        if(count($docxVars)>0) {
        
            foreach($docxVars as $v) {
                
                if(substr($v, 0, 4)=='_SM_') {
                    
                    //СМ переменная
                    $v2 = \DocumentsGenerateModel::getInstance()->getSeparateSM($v);
                    
                    $arrays [] = $v2[0];
                    
                }else
                    $vars [] = $v; //обычная переменная
                
            }
            
            $vars = array_unique($vars);
            $arrays = array_unique($arrays);
        
            if(count($arrays)>0) {
                $s_arrays = \DocumentsGenerateModel::getInstance()->separateSMFirstLevel($arrays, $gen_module_id);
                $arrays = $s_arrays[0];
                $m_arrays = $s_arrays[1];
            }   
        
            //исключаем повторение
            $vars = array_diff($vars, $arrays, $m_arrays);
            
        }

        return array($vars, $arrays, $m_arrays);
          
    }     

}




