<?php

class WordExport {

    private $_object;

    private $_extension_copy;

    private $_data = array();


    public function __construct(){
        
        spl_autoload_unregister(array('YiiBase','autoload'));
        Yii::import('ext.PHPWord.PHPWord', true);
        $this->_object = new PHPWord();
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
    * @file - путь к файлу
    * @params - ассоциативный массив (key - имя пареметра (без {}), value - значение)
    */
    public function getTemplateDocument($file, $params = array()){
        
        $document = $this->_object->loadTemplate($file);
                
        if(count($params)>0)
            foreach($params as $k=>$v) 
                $document->setValue($k, $v);
                
        $tmp = tempnam(sys_get_temp_dir(), 'CRM');
        
        $document->save($tmp);

        header('Content-Description: File Transfer');
        header('Content-type: application/force-download');
        header('Content-Disposition: attachment; filename=template.docx');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($tmp));
        readfile($tmp);
        
        unlink($file2);

        return;
    }





}




