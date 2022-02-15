<?php

namespace Process\models;


class ProcessSchemaVersions extends \ActiveRecord{


    public function tableName(){
        return '{{process_schema_versions}}';
    }



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function rules(){
        return array(
            array('process_id ', 'numerical', 'integerOnly'=>true),
            array('version', 'length', 'max' => 10),
            array('schema_versions', 'safe'),
        );
    }





    /**
     * исключает ИД из запроса
     */
    public function scopeProcessId($process_id){
        if(empty($process_id)){
            return $this;
        }

        $criteria = new \CDbCriteria();
        $criteria->addCondition('process_id=:process_id');
        $criteria->params = [
            'process_id' => $process_id,
        ];
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }






    public static function updateVersion($version, $process_id = null){
        if($version == false){
            return false;
        }

        if($process_id === null){
            $process_id = ProcessModel::getInstance()->process_id;
        }

        if($process_id == false){
            return false;
        }

        $process_schema_model = static::model()->scopeProcessId($process_id)->find();

        if($process_schema_model == false){
            $process_schema_model = new static();
            $process_schema_model->setAttribute('process_id', $process_id);
        }


        $process_schema_model->setAttribute('version', $version);

        $status = $process_schema_model->save();

        return $status;
    }



}
