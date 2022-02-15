<?php
/**
 * LogModel
 * @author Alex B.
 */


class LogModel{
    

    private $write_method = 'file';
    
    //актуально для тестового файла. FILE_APPEND | LOCK_EX
    private $type_of_writing = null;
    
    private $filename = 'log.txt';
    private $data = false;
    private $write_datetime = false;
    
    public static  function getInstance(){
        return new self();
    }

    
    /**
     * Устанавливаем метод записи лога
     */
    public function setWriteMethod($method){

        $this->write_method = $method;
        return $this;
    }
    
    
    /**
     * Устанавливаем тип записи
     */
    public function setTypeWriting($type){

        $this->type_of_writing = $type;
        return $this;
    }
    
    
    /**
     * Имя файла
     */
    public function setFileName($filename){

        $this->filename = $filename;
        return $this;
    }
    
    
    /**
     * Содержимое
     */
    public function setData($data){

        $this->data = $data;
        return $this;
    }
    
    
    /**
     * Время запуска лога
     * @param write_datetime boolean
     */
    public function setDateTime($write_datetime){

        $this->write_datetime = $write_datetime;
        return $this;
    }
    

    /**
     * start log
     * @param message varchar
     */
    public function start($message=''){

        $data = array();
        
        if($this->write_datetime)
            $data []= date("Y-m-d H:i:s");
    
        if(!empty($message))
            $data []= $message;
    
        $data []= "\r\n";
        $result = implode(' ', $data);
    
        if(!empty($result)) {
            switch($this->write_method) {
                case 'file':
                
                    @file_put_contents($this->filename, $result, $this->type_of_writing); 
                
                break;
                
            }
        }

        
        return $this;
    }













}
