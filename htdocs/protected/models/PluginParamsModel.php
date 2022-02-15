<?php

class PluginParamsModel extends ActiveRecord{


    public static function model($className = __CLASS__){
        return parent::model($className);
    }


    public function tableName(){
        return '{{plugin_params}}';
    }


    public function rules(){
        return array(
            array('source_name, service_name, params, active', 'required'),
        );
    }



    public function scopeSourceName($source_name){
        if($source_name == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition('source_name=:source_name');
        $criteria->params = [
            ':source_name' => $source_name,
        ];
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    public function scopeServiceName($service_name){
        if($service_name == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition('service_name=:service_name');
        $criteria->params = [
            ':service_name' => $service_name,
        ];
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }




    public function getParams($json_decode = true){
        if($json_decode && $this->params){
            return json_decode($this->params, true);
        }

        return $this->params;
    }



    public function beforeSave(){
        if($this->params && is_array($this->params)){
            $this->params = json_encode($this->params);
        }

        return true;
    }


}
