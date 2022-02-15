<?php


class PluginSourceEmail extends PluginSourceAbstractFactory{

    static private $_instance;


    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}


    public static function getInstance(){
        if(static::$_instance === null){
            static::$_instance = new static();
            static::$_instance->init();
        }

        return static::$_instance;
    }


    public function getName(){
        return 'email';
    }


    public function getTitle(){
        return Yii::t('base', 'Mailing Email');
    }

}
