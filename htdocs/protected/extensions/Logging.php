<?php

/**
 * Class Logging
 */


class Logging {

    const BLACK  = "\033[0;30m";
    const RED    = "\033[0;31m";
    const GREEN  = "\033[0;32m";
    const YELLOW = "\033[0;33m";
    const CYAN   = "\033[0;36m";

    private static $_instance;

    private $_strings_to_hide = [];
    private $_log_name;
    private $_prefix;

    private $_screen_view = false;
    private $_log_to_file = true;
    private $_append_prefix_to_message = false;


    private function __clone() {}
    private function __construct(){}



    public static function getInstance($prefix = null) {
        if($prefix === null){
            $prefix = 'zero';
        }

        if (self::$_instance === null || array_key_exists($prefix, self::$_instance) == false) {
            self::$_instance[$prefix] = new static();
        }

        self::$_instance[$prefix]->setPrefix($prefix);

        return self::$_instance[$prefix];
    }



    public function setLogName($log_name) {
        $this->_log_name = $log_name;
        return $this;
    }


    public function setScreenView($screen_view){
        $this->_screen_view = $screen_view;
        return $this;
    }


    public function setLogToFile($log_to_file){
        $this->_log_to_file = $log_to_file;
        return $this;
    }


    public function setAppendPrefixToMessage($status){
        $this->_append_prefix_to_message = $status;
        return $this;
    }

    private function setPrefix($prefix){
        $this->_prefix = $prefix;
        return $this;
    }


    private function getPrefixToFile(){
        if($this->_append_prefix_to_message){
            return '[' . $this->_prefix . ']';
        }
    }


    public function addStringsToHide($string) {
        $this->_strings_to_hide = array_merge($this->_strings_to_hide, [$string]);
        return $this;
    }


    private function printColored($text, $color, $level){
        if($this->_screen_view){
            $nc = "\033[0m";
            print str_replace($this->_strings_to_hide, '******', "$color$text$nc\n");
        }

        if($this->_log_to_file){
            $this->toFile($text, $level);
        }
    }


    public function toFile($text, $level = CLogger::LEVEL_INFO){
        Yii::log(str_replace($this->_strings_to_hide, '******', ($this->getPrefixToFile() ? $this->getPrefixToFile() . ' ' : '') . $text), $level, $this->_log_name);

        return $this;
    }


    public function black($text, $level = CLogger::LEVEL_INFO) {
        $this->printColored($text, self::BLACK, $level);

        return $this;
    }


    public function yellow($text, $level = CLogger::LEVEL_INFO) {
        $this->printColored($text, self::YELLOW, $level);

        return $this;
    }


    public function cyan($text, $level = CLogger::LEVEL_INFO) {
        $this->printColored($text, self::CYAN, $level);

        return $this;
    }


    public function red($text, $level = CLogger::LEVEL_INFO) {
        $this->printColored($text, self::RED, $level);

        return $this;
    }


    public function green($text, $level = CLogger::LEVEL_INFO) {
        $this->printColored($text, self::GREEN, $level);

        return $this;
    }



}
