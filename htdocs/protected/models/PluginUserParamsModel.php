<?php


class PluginUserParamsModel extends ActiveRecord{



    public static function model($className = __CLASS__){
        return parent::model($className);
    }


    public function tableName(){
        return '{{plugin_user_params}}';
    }


    public function rules(){
        return array(
            array('users_id, plugin_params_id, params', 'required'),
            array('id', 'safe'),
        );
    }




    public function scopeUsersId($users_id){
        if($users_id == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        if(is_array($users_id)){
            $criteria->addInCondition('users_id', $users_id);
        } else {
            $criteria->addCondition('users_id=:users_id');
            $criteria->params = [
                ':users_id' => $users_id,
            ];
        }

        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    public function scopePluginParamsId($plugin_params_id){
        if($plugin_params_id == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        $criteria->addCondition('plugin_params_id=:plugin_params_id');
        $criteria->params = [
            ':plugin_params_id' => $plugin_params_id,
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
            $this->params = (json_encode($this->params));
        }

        return true;
    }


}
