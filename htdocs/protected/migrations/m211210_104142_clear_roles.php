<?php

class m211210_104142_clear_roles extends CDbMigration
{
    public function up()
    {
        $this->clearPermissions();
        $this->clearPermissionRoles();
        $this->resaveRoles();
        $this->clearPermissions();
    }

    private function resaveRoles()
    {
        $roles = (new DataModel())->setText('SELECT roles_id FROM {{roles}}')->findCol();
        if (!$roles) {
            return;
        }

        foreach ($roles as $roleId) {
            $permissions = (new DataModel())
                ->setText('
                    SELECT t2.id as permission_roles_id, t1.*
                    FROM {{permission}} t1
                    LEFT JOIN {{permission_roles}} t2 on t1.permission_id =t2.permission_id
                    WHERE t2.roles_id = ' . $roleId . '
                    ORDER BY access_id_type, access_id;

                ')
                ->findAll();

            if (!$permissions) {
                continue;
            }

            foreach ($permissions as $permissionAttr) {
                $attributes = array_filter($permissionAttr, function ($key) {
                    return !in_array($key, ['permission_roles_id', 'permission_id', 'uid', 'date_edit']);
                }, ARRAY_FILTER_USE_KEY);

                $this->insert('{{permission}}', $attributes);

                $permissionId = (new DataModel())->setText('SELECT max(permission_id) FROM {{permission}}')->findScalar();
                if (!$permissionId) {
                    continue;
                }

                $this->insert('{{permission_roles}}', [
                    'roles_id'      => $roleId,
                    'permission_id' => $permissionId,
                ]);

                $this->delete('{{permission_roles}}', 'id = ' . $permissionAttr['permission_roles_id']);
            }
        }
    }

    private function clearPermissionRoles()
    {
        (new DataModel())
            ->setText('
                DELETE
                FROM {{permission_roles}}
                WHERE not exists(SELECT 1 FROM {{roles}} WHERE {{roles}}.roles_id = {{permission_roles}}.roles_id)'
            )
            ->execute();

        (new DataModel())
            ->setText('
                DELETE
                FROM {{permission_roles}}
                WHERE not exists(SELECT 1 FROM {{permission}} WHERE {{permission}}.permission_id = {{permission_roles}}.permission_id)
            ')
            ->execute();
    }

    private function clearPermissions()
    {
        $accessIDList = (new DataModel())
            ->setFrom('{{extension_copy}}')
            ->setSelect('copy_id')
            ->setWhere('set_access = "0"')
            ->findCol();

        if ($accessIDList) {
            $this->delete(
                '{{permission}}',
                'access_id_type = 2 AND access_id in(' . implode(',', $accessIDList) . ')'
            );
        }

        (new DataModel())
            ->setText('
                DELETE
                FROM {{permission}}
                WHERE not exists(SELECT 1 FROM {{permission_roles}} WHERE {{permission_roles}}.permission_id = {{permission}}.permission_id)
            ')
            ->execute();
    }

    public function down()
    {
        echo "m211210_104142_clear_roles does not support migration down.\n";

        return true;
    }
}
