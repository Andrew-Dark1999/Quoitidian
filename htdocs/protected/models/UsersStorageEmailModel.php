<?php

/**
 * Class UsersStorageEmailModel
 */
class UsersStorageEmailModel extends ActiveRecord{

    public $tableName = 'users_storage_email';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('users_id, email_id', 'safe'),
        );
    }



    public function relations(){
        return array(
            'users' => array(self::HAS_ONE, 'UsersModel', array('users_id' => 'users_id')),
            'emails' => array(self::HAS_ONE, 'EmailsModel', array('email_id' => 'email_id')),
        );
    }



    public function beforeSave(){
        if($this->getIsNewRecord()){
            $this->date_affect = new CDbExpression('now()');
        }

        return true;
    }



    public function scopeUsersId($users_id = null){
        if($users_id === null){
            $users_id = \WebUser::getUserId();
        }

        $criteria = new CDBCriteria();
        $criteria->condition = 'users_id=:users_id';
        $criteria->params = array(':users_id' => $users_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    public static function updateDateAffect($condition_array){
        if($condition_array == false){
            return;
        }
        $condition = array();
        $params = array();

        foreach($condition_array as $field_name => $value){
            $condition[] = $field_name . '=:' . $field_name;
            $params[':' . $field_name] = $value;
        }
        $condition = implode(' AND ', $condition);


        (new static())->updateAll(
                array('date_affect' => (new CDbExpression('now()'))),
                $condition,
                $params
            );
    }


}






