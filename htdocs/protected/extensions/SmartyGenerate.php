<?php

class SmartyGenerate {

    private $_object;

    private $_extension_copy;

    private $_data = array();


    public function __construct(){
        
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import('ext.Smarty.*', true);
        
        require_once('SmartyBC.class.php');
        
        $this->_object = new SmartyBC();
        
        $this->_object->caching = false;
        $this->_object->error_reporting = E_ALL & ~E_NOTICE;
        
        $this->_object->setCompileDir(ParamsModel::model()->titleName('upload_path_tmp')->find()->getValue());
        
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
    * @importFile - путь к файлу, который импортируем
    * @exportFile - конечный файл
    */
    public function generateDocument($vars, $importFile, $uploadModel, $params = array(), $new_filename = false){

        // print_r($vars);
        // print_r($params);
        // die();
                
        if(count($vars['simple_variables'])){
            if(in_array('DEBUG_PARAMS', $vars['simple_variables'])) {
                
                print_r($vars);
                print_r($params);
                die();
            }
        }   
        
        //поскольку смарти не поддерживает кириллицу и не поддерживает переменные со знаком :, 
        //то сначала мы делаем замену, сохраняем во временном файле, а потом открываем шаблонизатором 
        if(count($params)>0)
            foreach($params as $k=>$v) 
               $this->_object->assign($this->translit($k), $v);
        
        $f = file_get_contents($importFile);
        
        if(count($vars['simple_variables']))
            foreach($vars['simple_variables'] as $v) {
                $f = str_replace("$" . $v . "}",  "$" . $this->translit($v) . "}", $f);
                $f = str_replace("$" . $v . " ",  "$" . $this->translit($v) . " ", $f);
                $f = str_replace("$" . $v . ")",  "$" . $this->translit($v) . ")", $f);
            }
        if(count($vars['sm_1st_variables']))
            foreach($vars['sm_1st_variables'] as $v) {
                $f = str_replace("$" . $v . "}", "$" . $this->translit($v) . "}", $f);    
                $f = str_replace("$" . $v . " ", "$" . $this->translit($v) . " ", $f);
                $f = str_replace("$" . $v . ")", "$" . $this->translit($v) . ")", $f);
                $f = str_replace("$" . $v . ".", "$" . $this->translit($v) . ".", $f);
                
            }    
        if(count($vars['sm_other_variables']))
            foreach($vars['sm_other_variables'] as $v) {
                $f = str_replace("$" . $v . "}", "$" . $this->translit($v) . "}", $f); 
                $f = str_replace("$" . $v . " ", "$" . $this->translit($v) . " ", $f); 
                $f = str_replace("$" . $v . ")", "$" . $this->translit($v) . ")", $f); 
                $f = str_replace("$" . $v . ".", "$" . $this->translit($v) . ".", $f); 
            }    
            
        //echo $f; die();
        $tmpfname = tempnam(ParamsModel::model()->titleName('upload_path_tmp')->find()->getValue(), "CRM");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $f);
        fclose($handle);

        //загружаем подмененный шаблон и удаляем временный файл
        $o = $this->_object->fetch($tmpfname);
        
        if(file_exists($tmpfname))
            @unlink($tmpfname);

        //очищаем скомпиленные смарти файлы
        $this->_object->clear_compiled_tpl();
                
        //получаем текущие данные
        $filename = pathinfo($uploadModel->file_name);
        $filetitle = pathinfo($uploadModel->file_title);

        //удаляем первоначальный шаблон и сохраняем новый файл 
        $path = ParamsModel::model()->titleName('upload_path_module')->find()->getValue() .  DIRECTORY_SEPARATOR . $uploadModel->file_path;

        if(file_exists($path . DIRECTORY_SEPARATOR . $uploadModel->file_name))
            @unlink($path . DIRECTORY_SEPARATOR . $uploadModel->file_name);
        
        if($new_filename) {
            $filename['filename'] = Translit::forFileName($new_filename);
            $filetitle['filename'] = $new_filename;
        }   
        
        //записываем html в базу
        DocumentsModel::setDBData($o, 'html', $uploadModel->id);
        
        $file = $path . DIRECTORY_SEPARATOR . $filename['filename'] . '.pdf';

        $this->saveToPDF($file, $o);

        //меняем запись в БД
        $time = date('Y-m-d H:i:s');
        DataModel::getInstance()->Update('{{uploads}}', array('file_date_upload' => $time, 'date_create' => $time, 'file_name' => $filename['filename'] . '.pdf', 'file_title' => $filetitle['filename'] . '.pdf'), 'id = ' . $uploadModel->id);

        $p_time = date('d', strtotime($time)) . ' ' . 
                         mb_strtolower(Yii::t('base', date('F', strtotime($time)),2), 'utf-8') . ' ' .
                         Yii::t('base', 'in') . ' ' . 
                         date('H:i', strtotime($time));
        
        return array(
            'status' => true, 
            'link' => '/' . $file, 
            'title' => $filetitle['filename'] . '.pdf', 
            'filedate' => $p_time,
            'show_edit_link' => true,
        );
    }
    
    
   /**
    * Сохраняем в pdf   
    */
    public function saveToPDF($file, $data){

        $mpdf=new mPDF('','', 0, '', 5, 5, 10, 5, 9, 9, 'L');
        $mpdf->WriteHTML($data);
        $mpdf->output($file, 'F');
    
    }
    
    
    
   /**
    * Получаем все переменные из смарти шаблона 
    * пояснение: это паттерн на проверку php переменных, + символы [., :]
    * ВАЖНО: 
    * 1. название переменной и модуля должно содержать только буквы;
    * 2. для разделения используется символ ":";
    * 3. символ "." используется только в названиях переменных (это означает, что переменная является массивом).    
    */
    public function getVariablesFromTemplate($import_file, $gen_module_id){
        
        $text = file_get_contents($import_file);
        
        $pattern = '/\$([a-zA-Z_\x7f-\xff][a-z.:A-Z0-9_\x7f-\xff]*)/';
        
        preg_match_all($pattern, $text, $matches);
   
        //обычные переменные, также СДМ связи
        $vars = array();
        
        //это массивы (СМ связи)
        $arrays = array();
        
        //СМ, которые ищем через поле is_main или через отдельный попап
        $m_arrays = array();
        
        //записи, для которых не выводим попап
        $no_popup = array();
        
        if(count($matches)>0) {
        
            //переменные найдены, в том числе и возможные массивы и вообще все подряд, вместе с модулями
        
            //сначала перебираем их, ищем символ ".", первый элемент это и есть название массива
            foreach($matches[1] as $v) {
                if($x = mb_stristr($v, ".", true))
                    $arrays[] = $x; else  //это запись массива
                    $vars [] = $v;        //это запись переменной
            }
        
            //теперь полностью блок проверяем на запись цикла
            preg_match_all('/{foreach(.*?)}/s', $text, $matches);
            if(count($matches>0)) {
             
                //блок с массиывами найден, ищем название массива
                foreach($matches[1] as $v) {
                 
                    preg_match($pattern, $v, $arr);
                    if(count($arr)>0)
                        $arrays[]= $arr[1];
                    
                }
                
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
            
            //загружаем директивы по показу попапа
            preg_match_all("#\{SYSTEM_NO_POPUP\}(.*)\{\/SYSTEM_NO_POPUP\}#Ui", $text, $np);
            if(count($np>0)) {
                $no_popup = explode(',', $np[1][0]);
            }
            
        }    

        return array($vars, $arrays, $m_arrays, 'no_popup'=>$no_popup);

    }    


    function translit($str) 
    {
        $translit = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            ":"=>"_", " "=>""
            
        );
        return strtr($str,$translit);
    }


}




