<?php

/**
 * PermissionRolesModel
 *
 * @author Alex R.
 * @copyright 2017
 */
class PermissionRolesModel extends ActiveRecord{

    public $tableName = 'permission_roles';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function rules(){
        return array(
            array('permission_id, roles_id', 'safe'),
        );
    }




}
