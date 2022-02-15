<?php

/**
 * PermissionsModel
 *
 * @author Alex R.
 * @copyright 2017
 */
class PermissionsModel extends ActiveRecord
{

    const ACCESS_TYPE_REGULATION = 1;
    const ACCESS_TYPE_MODULE = 2;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{permission}}';
    }

    public function relations()
    {
        return [
            'permissionRoles' => [self::HAS_MANY, 'PermissionRolesModel', 'permission_id'],
            'rolesModel'      => [self::HAS_MANY, 'RolesModel', ['roles_id' => 'roles_id'], 'through' => 'permissionRoles'],
        ];
    }

    public function fillWithParams($user_create, $access_id, $access_id_type = self::ACCESS_TYPE_REGULATION, $rule_view = 2, $rule_create = 2, $rule_edit = 2, $rule_delete = 2, $rule_import = 2, $rule_export = 2, $rule_all_participants = 2, $this_template = 0)
    {
        $this->user_create = $user_create;
        $this->access_id = $access_id;
        $this->access_id_type = $access_id_type;
        $this->rule_view = $rule_view;
        $this->rule_create = $rule_create;
        $this->rule_edit = $rule_edit;
        $this->rule_delete = $rule_delete;
        $this->rule_import = $rule_import;
        $this->rule_export = $rule_export;
        $this->rule_all_participants = $rule_all_participants;
        $this->this_template = $this_template;

        return $this;
    }

}
