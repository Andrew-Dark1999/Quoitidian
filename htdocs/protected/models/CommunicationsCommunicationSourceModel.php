<?php

class CommunicationsCommunicationSourceModel extends ActiveRecord{

    const SOURCE_SLUG_EMAIL         = 'email';
    const SOURCE_SLUG_SKIPE         = 'skype';
    const SOURCE_SLUG_WHATS_APP     = 'whats_app';
    const SOURCE_SLUG_TELEGRAM      = 'telegram';
    const SOURCE_SLUG_FACEBOOK      = 'facebook';

    static private $_source_model_list = array();



    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return '{{communications_communication_source}}';
    }



    private static function getSourceModelList(){
        if(self::$_source_model_list){
            return self::$_source_model_list;
        }

        self::$_source_model_list = static::model()->findAll();

        return self::$_source_model_list;
    }



    public static function getSourceIdBySlug($source_slug){
        $source_model_list = static::getSourceModelList();

        if($source_model_list == false){
            return;
        }

        foreach($source_model_list as $source_model){
            if($source_slug == $source_model->communication_source_slug){
                return $source_model->communication_source_id;
            }
        }
    }



}
