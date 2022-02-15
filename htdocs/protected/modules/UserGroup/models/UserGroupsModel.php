<?php

/**
 * UserGroupsModel
 *
 * @author Alex R.
 * @copyright 2016
 */
class UserGroupsModel extends ActiveRecord
{
    public $tableName = 'users_groups';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function rules(){
        return array();
    }


    public function relations(){
        return array();
    }


    public function scopes(){
        return array();
    }






}
