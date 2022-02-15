<?php

/**
 * Class PluginIELogModel - Логирование ответов от sip-сервера
 */


class PluginIELogModel extends ActiveRecord{


    public static function model($className = __CLASS__){
        return parent::model($className);
    }


    public function tableName(){
        return '{{plugin_ie_log}}';
    }



    public function rules(){
        return array(
            array('ie_log_id, date_create, plugin_user_params_id, request_name, unique_key, external_params', 'safe'),
        );
    }


    public function getParams($json_decode = true){
        if($json_decode && $this->external_params){
            return json_decode($this->external_params, true);
        }

        return $this->params;
    }



    public function beforeSave(){
        if($this->getIsNewRecord()){
            $this->date_create = new CDbExpression('now()');
        }

        if($this->external_params && is_array($this->external_params)){
            $this->external_params = json_encode($this->external_params);
        }

        return true;
    }


}
