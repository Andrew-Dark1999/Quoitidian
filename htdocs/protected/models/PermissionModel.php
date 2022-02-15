<?php

class PermissionModel
{

    // права доступа
    const PERMISSION_DATA_VIEW = 'rule_view';
    const PERMISSION_DATA_RECORD_CREATE = 'rule_create';
    const PERMISSION_DATA_RECORD_EDIT = 'rule_edit';
    const PERMISSION_DATA_RECORD_DELETE = 'rule_delete';
    const PERMISSION_DATA_RECORD_IMPORT = 'rule_import';
    const PERMISSION_DATA_RECORD_EXPORT = 'rule_export';
    const PERMISSION_DATA_ALL_PARTICIPANTS = 'rule_all_participants';

    // типы доступов
    const PERMISSION_ACCESS_ALLOWED = 1; // разрешено
    const PERMISSION_ACCESS_PROHIDITED = 2; // запрещено

    public static function getInstance()
    {
        return new self();
    }

    /**
     * Удаляем из permission и permission_roles записи о доступах
     */
    public function deletePermission($access_id, $access_id_type)
    {
        DataModel::getInstance()->setText('DELETE FROM {{permission_roles}} WHERE permission_id in (SELECT permission_id FROM {{permission}} WHERE access_id = ' . $access_id . ' AND access_id_type = ' . $access_id_type . ')')->execute();

        DataModel::getInstance()->Delete('{{permission}}', 'access_id=:access_id AND access_id_type=:access_id_type', [
            ':access_id'      => $access_id,
            ':access_id_type' => $access_id_type,
        ]);
    }

    /**
     * Сохранение прав
     */
    private function saveModelePermission($copy_id, $permission_code)
    {
        $extension_copy = ExtensionCopyModel::model()->findByPk(150);
        $schema_parser = $extension_copy->getSchemaParse();

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $extension_copy->getTableName(null, false),
            'params'    => Fields::getInstance()->getActiveRecordsParams($schema_parser),
        ];

        $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
        $extension_data->extension_copy = $extension_copy;
        $extension_data->setElementSchema($schema_parser);
        $extension_data->setMyAttributes([
            'access_id'       => ['id' => $copy_id, 'type' => Access::ACCESS_TYPE_MODULE],
            'permission_code' => $permission_code,
        ]);
        $extension_data->save();
    }

}
