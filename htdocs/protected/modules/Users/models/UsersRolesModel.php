<?php

/**
 * UsersRolesModel
 *
 * @author Alex R.
 * @copyright 2016
 */
class UsersRolesModel extends ActiveRecord{

    public $tableName = 'users_roles';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function rules(){
        return array(
            array('users_id, roles_id', 'safe'),
        );
    }




}
