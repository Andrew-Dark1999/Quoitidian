<?php
/**
 * InstallModule
 *
 * @author Alex R.
 * @version 1.0
 * Между таблицами поддерживается связь many-to-many  <-> many_to_one
 */

class InstallModule
{

    // роспарсеная схема полей Субмодуля     
    private $_schema = [];

    // роспарсеная временная схема полей Субмодуля
    private $_schema_fature = [];

    // экземпляр класса ExtensionCopyModel
    private $_extension_copy;

    // экземпляр ОРМ
    private $_orm_command;

    // показывает наличие ошибок
    private $_beError = false;

    // файлы ключений для удаления
    private $_file_keys_for_delete = [];

    // список удаленных полей (при редактировании)
    private $_deleted_field_name_list = [];

    // список блоков данных для удаления в случае изменения схемы
    // данные удаляются по полю copy_id
    private $blocks_for_delete = [
        'activity_messages'       => false,
        'participant_all'         => false,
        'participant_responsible' => false,
    ];

    public function __construct($extension_copy)
    {
        $this->_extension_copy = $extension_copy;
    }

    public static function getInstance($extension_copy)
    {
        return new static($extension_copy);
    }

    public function getSchema()
    {
        return $this->_schema;
    }

    public function getSchemaFature()
    {
        return $this->_schema_fature;
    }

    public function beError()
    {
        return $this->_beError;
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->_extension_copy = $extension_copy;

        return $this;
    }

    /**
     * проверка на существование поля в БД
     */
    private function isSetField($table_name, $column_name, $column_type = null)
    {
        $field_data = $this->_orm_command->setText('SHOW FIELDS FROM {{' . $table_name . '}} WHERE field = "' . $column_name . '"')->queryRow();
        if (empty($field_data)) {
            return false;
        } else {
            if ($column_type !== null && $column_type != $field_data['Type']) {
                return false;
            }

            return true;
        }
    }

    /**
     * Разбирает схему на составляюще в будущем для создания запросов в БД
     */
    public function parseSchema()
    {
        $schema = json_decode($this->_extension_copy->schema, true);

        $this->findFields($schema, $this->_schema);
        $this->findFieldSelect($this->_schema);
        $this->findFieldSubModule($schema, $this->_schema);

        return $this;
    }

    /**
     * Разбирает временную схему на составляюще в будущем для создания запросов в БД
     */
    public function parseSchemaFature()
    {
        $schema_fature = json_decode($this->_extension_copy->schema_fature, true);

        $this->findFields($schema_fature, $this->_schema_fature);
        $this->findFieldSelect($this->_schema_fature);
        $this->findFieldSubModule($schema_fature, $this->_schema_fature);

        return $this;
    }

    /**
     * Поиск полей для главной таблицы Субмодуля
     */
    public function findFields($schema, &$callback)
    {
        foreach ($schema as $value) {
            if (isset($value['params']['make']) && $value['params']['make'] == false) {
                continue;
            }

            if (isset($value['type']) && $value['type'] != 'table') {
                if (isset($value['elements'])) {
                    $this->findFields($value['elements'], $callback);
                }
            }

            if (isset($value['type'])) {
                switch ($value['type']) {
                    case 'edit':
                    case 'edit_hidden':
                    case 'attachments':
                        if (isset($value['params'])) {
                            if ($value['params']['type'] == 'relate' || $value['params']['type'] == 'relate_string') {
                                $callback['relate'][] = $value['params'];
                            }
                            if ($value['params']['type'] != 'relate' && $value['params']['type'] != 'relate_dinamic') {
                                $callback['fields'][] = $value['params'];
                            }
                        }
                        break;
                    case 'button':
                        if ($value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                            $callback['fields'][] = $value['params'];
                        }
                        if ($value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS) {
                            $callback['fields'][] = $value['params'];
                        }
                        if ($value['params']['type'] == 'relate_participant' && $value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                            $callback['fields_cloud'][] = $value['params'];
                        }

                        break;
                    case 'participant':
                        if ($value['params']['type'] == 'relate_participant' && $value['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
                            $callback['fields_cloud'][] = $value['params'];
                        }
                        break;
                    case 'activity':
                        if ($value['params']['type'] == 'activity') {
                            $callback['fields_cloud'][] = $value['params'];
                        }
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Поиск полей для Select
     */
    private function findFieldSelect(&$callback)
    {
        if (!isset($callback['fields']) || empty($callback['fields'])) {
            return;
        }
        foreach ($callback['fields'] as $value) {
            if (isset($value['type']) && $value['type'] == 'select') {
                if (isset($value['values'])) // && !empty($value['values']))
                {
                    $callback['select'][$value['name']] = [
                        'value'                  => $value['values'],
                        'select_color'           => (isset($value['select_color']) ? $value['select_color'] : []),
                        'select_remove_forbid'   => (isset($value['select_remove_forbid']) ? $value['select_remove_forbid'] : []),
                        'select_finished_object' => (isset($value['select_finished_object']) ? $value['select_finished_object'] : []),
                        'select_sort'            => (isset($value['select_sort']) ? $value['select_sort'] : []),
                        'select_slug'            => (isset($value['select_slug']) ? $value['select_slug'] : []),
                    ];
                }
            }
        }
    }

    /**
     * Поиск полей для SubModule
     */
    private function findFieldSubModule($schema, &$callback)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && $value['type'] != 'table') {
                if (isset($value['elements'])) {
                    $this->findFieldSubModule($value['elements'], $callback);
                }
            }
            if (isset($value['type']) && $value['type'] == 'sub_module') {
                $callback['sub_module'][] = $value['params'];
            }
        }

        return $this;
    }

    /**
     * Созданий таблиц
     */
    public function createTables()
    {
        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        $this->_orm_command = $connection->createCommand();

        try {
            $this->createTableFields();
            $this->createTableSelect();
            $this->createTableSubModule();
            $this->createTableRelate();
            $this->createTableSubModuleProcess();
            $this->moveSchemaFature();
            $this->setRolesPermission();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $this;
    }

    /**
     * Обновление таблиц
     */
    public function updateTables()
    {
        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        $this->_orm_command = $connection->createCommand();

        try {
            $this->addTableSpecialFields();
            $this->updateTableFields();
            $this->updateTableSelect();
            $this->createTableSubModule();
            $this->createTableRelate();
            $this->createTableSubModuleProcess();
            $this->dropTableSubModule();
            $this->dropTableRelate();
            $this->dropRelateData();
            $this->clearModuleFilter();
            $this->moveSchemaFature();
            $this->runLastOperation();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();

            $this->_extension_copy->schema_fature = null;
            $this->_extension_copy->save();
        }

        return $this;
    }

    public function setRolesPermission()
    {
        foreach (RolesModel::model()->findAll() as $role) {
            $permission_role = new PermissionRolesModel();
            $permission_role->roles_id = $role->roles_id;
            if ($role->roles_id == 1) {
                $permission_admin = new PermissionsModel();
                $permission_admin->fillWithParams(WebUser::getUserId(), $this->_extension_copy->copy_id, PermissionsModel::ACCESS_TYPE_MODULE, 1, 1, 1, 1, 1, 1, 2);
                $permission_admin->save();

                $permission_role->permission_id = $permission_admin->permission_id;
            } else {
                $permission_casual = new PermissionsModel();
                $permission_casual->fillWithParams(WebUser::getUserId(), $this->_extension_copy->copy_id, PermissionsModel::ACCESS_TYPE_MODULE, 2, 1, 1, 1, 1, 1, 2);
                $permission_casual->save();

                $permission_role->permission_id = $permission_casual->permission_id;
            }
            $permission_role->save();
        }
    }



    /**
     *   удаляем "старые" таблицы, связаные с данным модулем
     */
    /*
    private function moveOldTables(){
        $table_data = ModuleTablesModel::model()->findAll(array(
                                                            // удаляем только таблицы, осозданые данным модулем
                                                            'condition' => 'copy_id=:copy_id AND `type` in ("parent", "relate_select", "relate_module_many")',
                                                            'params' => array(':copy_id'=>$this->_extension_copy->copy_id),
                                                            )
                                                        );
        if(!empty($table_data)){
            $model_table = new ModuleTablesModel();
            foreach($table_data as $value){
                if($model_table->count(array(
                                        'condition' => 'table_name=:table_name',
                                        'params' => array(':table_name' => $value->table_name)
                ))> 1) continue; 
                $this->_orm_command->setText('DROP TABLE IF EXISTS ' . $value->table_name)->execute();
            }
        }

        ModuleTablesModel::model()->deleteAll(array(
                                            'condition' => 'copy_id=:copy_id',
                                            'params' => array(':copy_id'=>$this->_extension_copy->copy_id),
                                            )
                                        );        

    }
    */

    /**
     * Переносим схему полей из schema_fature в schema
     */
    private function moveSchemaFature()
    {
        $this->_extension_copy->schema = $this->_extension_copy->schema_fature;
        $this->_extension_copy->schema_fature = null;
        $this->_extension_copy->save();
    }

    /**
     * Добавляем запись о созданой таблице в module_tables
     * Типы таблиц (связей), поле - type:
     *   parent - ролительская таблица
     *   relate_select - таблица-справочник значений для поля select
     *   relate_module_one - запись-ссылка на подключенный модуль по принципу один-ко-многим
     *   relate_module_many - запись на связаную таблицу на подключенный модуль по принципу многие-ко-многим
     */
    private function saveInModuleTablesModel($table_name, $type, $relate_type = null, $parent_field_name = null, $relate_field_name = null, $relate_copy_id = null)
    {

        $criteria = new CDbCriteria();
        $criteria->condition = '
            copy_id=:copy_id AND
            table_name=:table_name
        ';

        $criteria->params = [
            ':copy_id'    => $this->_extension_copy->copy_id,
            ':table_name' => $table_name
        ];

        if ($relate_copy_id) {
            $criteria->condition .= ' AND relate_copy_id=:relate_copy_id';
            $criteria->params[':relate_copy_id'] = $relate_copy_id;
        } else {
            $criteria->condition .= ' AND relate_copy_id IS NULL';
        }

        if ($parent_field_name) {
            $criteria->condition .= ' AND parent_field_name=:parent_field_name';
            $criteria->params[':parent_field_name'] = $parent_field_name;
        } else {
            $criteria->condition .= ' AND parent_field_name IS NULL';
        }

        if ($relate_field_name) {
            $criteria->condition .= ' AND relate_field_name=:relate_field_name';
            $criteria->params[':relate_field_name'] = $relate_field_name;
        } else {
            $criteria->condition .= ' AND relate_field_name IS NULL';
        }

        $models = ModuleTablesModel::model()->findAll($criteria);
        if (!empty($models)) {

            foreach ($models as $key => $model) {
                if ($model->type != $type || $model->relate_type != $relate_type) {
                    $model->delete();
                    unset($models[$key]);
                }
            }

            if (!empty($models)) {
                return;
            }

        }

        $this->_orm_command->insert('{{module_tables}}', [
            'copy_id'           => $this->_extension_copy->copy_id,
            'relate_copy_id'    => $relate_copy_id,
            'table_name'        => $table_name,
            'type'              => $type,
            'relate_type'       => $relate_type,
            'parent_field_name' => $parent_field_name,
            'relate_field_name' => $relate_field_name,
        ]);
    }

    /**
     * Создание главной таблицы
     */
    private function createTableFields()
    {
        if (!isset($this->_schema_fature['fields']) || empty($this->_schema_fature['fields'])) {
            return false;
        }

        $table_name = $this->_extension_copy->getTableName(null, false);
        $this->_orm_command->setText('DROP TABLE IF EXISTS {{' . $table_name . '}}')->execute();
        $this->_orm_command->createTable('{{' . $table_name . '}}',
            $this->getFieldColumns('fields'),
            'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );
        $this->_orm_command->createIndex('uid_ind1', '{{' . $table_name . '}}', 'uid');
        $this->saveInModuleTablesModel($table_name, 'parent');
    }

    /**
     * getFieldType
     */
    private function getFieldType($params)
    {
        $field_type = null;

        if ($params['size'] == FieldTypes::TYPE_SIZE_VARCHAR) {
            $field_type = 'varchar';
        } elseif ($params['size'] == FieldTypes::TYPE_SIZE_TEXT) {
            $field_type = 'text';
        } elseif ($params['size'] == FieldTypes::TYPE_SIZE_MEDIUMTEXT) {
            $field_type = 'mediumtext';
        }

        return $field_type;
    }

    /**
     * Обновление главной таблицы
     */
    private function updateTableFields()
    {
        $this->dropColumns();
        $this->dropSpecialColumns();

        if (!isset($this->_schema_fature['fields']) || empty($this->_schema_fature['fields'])) {
            return false;
        }

        $table_name = $this->_extension_copy->getTableName(null, false);
        $virtual_fields = [Fields::MFT_DATETIME_ACTIVITY];
        foreach ($this->_schema_fature['fields'] as $params) {
            $fd = $this->differenceStatus($params);
            switch ($fd) {
                case 'add' :
                    if ($this->isSetField($table_name, $params['name']) || in_array($params['type'], $virtual_fields)) {
                        continue;
                    }

                    $this->_orm_command->addColumn('{{' . $table_name . '}}',
                        $params['name'],
                        FieldTypes::getInstance()->{'getSqlCreateColumn' . $params['type_db']}($params));

                    if ($params['type'] == \Fields::MFT_DATETIME && $params['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                        $this->_orm_command->addColumn('{{' . $table_name . '}}',
                            $params['name'] . '_ad',
                            FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['0', '1'], 'default' => '"1"']));
                    }

                    if ($params['type'] == 'access') {
                        $this->_orm_command->addColumn('{{' . $table_name . '}}',
                            $params['name'] . '_type',
                            FieldTypes::getInstance()->getSqlCreateColumnTinyInt());
                    }
                    break;
                case 'alter' :
                    $field_type = $this->getFieldType($params);
                    if ($this->isSetField($table_name, $params['name'], $field_type)) {
                        continue;
                    }
                    $this->_orm_command->alterColumn('{{' . $table_name . '}}',
                        $params['name'],
                        FieldTypes::getInstance()->{'getSqlCreateColumn' . $params['type_db']}($params));
                    break;
            }
        }
    }

    /**
     * Добавление специальных полей к главной таблице при обновлении модуля
     */
    private function addTableSpecialFields()
    {
        $table_name = $this->_extension_copy->getTableName(null, false);

        $columns = [
            'uid'           => FieldTypes::getInstance()->getSqlCreateColumnBigInt(),
            'date_create'   => FieldTypes::getInstance()->getSqlCreateColumnDateStamp(),
            'date_edit'     => FieldTypes::getInstance()->getSqlCreateColumnDateStamp(),
            'user_create'   => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
            'user_edit'     => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
            'this_template' => FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => [EditViewModel::THIS_TEMPLATE_MODULE, EditViewModel::THIS_TEMPLATE_TEMPLATE, EditViewModel::THIS_TEMPLATE_TEMPLATE_CM], 'default' => '"' . EditViewModel::THIS_TEMPLATE_MODULE . '"']),
        ];

        /*
        if($this->_extension_copy->getModule(false)->isTemplate($this->_extension_copy)){
            $columns['this_template'] = FieldTypes::getInstance()->getSqlCreateColumnEnum(array('values' => array('1', '0'), 'default' => '"0"'));
        }
        */

        foreach ($columns as $column_name => $params) {
            if (!$this->isSetField($table_name, $column_name)) {
                $this->_orm_command->addColumn('{{' . $table_name . '}}',
                    $column_name,
                    $params);
            }
        }

    }

    /**
     * Возвращает статус сравнения двух полей
     */
    private function differenceStatus($params_fature)
    {
        //новая
        if (!isset($this->_schema['fields']) || empty($this->_schema['fields'])) {
            return 'add';
        }

        //новая со "старым" названием поля        
        foreach ($this->_schema['fields'] as $params) {
            if ($params['name'] == $params_fature['name']) {
                if ($params['type'] != $params_fature['type']) {
                    return 'add';
                } elseif ($params['size'] != $params_fature['size'] || $params['default_value'] != $params_fature['default_value'] || $params['decimal'] != $params_fature['decimal']) {
                    return 'alter';
                } else {
                    return 'corresponds';
                }
            }
        }

        //новая
        return 'add';
    }

    /**
     * Удаляем поле в таблице БД
     */
    private function dropColumn($table_name, $column_name)
    {
        if (!$this->isSetField($table_name, $column_name)) {
            return false;
        }
        $this->_orm_command->dropColumn('{{' . $table_name . '}}', $column_name);
    }

    /**
     * Поиск удаленных и удаление полей + связаных с ними елементов: таблиц, данных таблиц...
     */
    private function dropColumns()
    {
        if (!isset($this->_schema['fields']) || empty($this->_schema['fields'])) {
            return false;
        }

        $table_name = $this->_extension_copy->getTableName(null, false);

        // блок поле fields 
        foreach ($this->_schema['fields'] as $params) {
            $status = false;

            // пропускаем поля, что не надо удалять
            if (isset($this->_schema_fature['fields']) && !empty($this->_schema_fature['fields'])) {
                foreach ($this->_schema_fature['fields'] as $params_fature) {
                    if (($params['name'] == $params_fature['name'] && $params['type'] == $params_fature['type']) ||
                        ($params['name'] == $params_fature['name'] && $params['type'] != $params_fature['type'] &&
                            ($params['type'] == 'display' || $params['type'] == 'display_none' || $params['type'] == 'relate_string' || $params['type'] == 'auto_number') &&
                            ($params_fature['type'] == 'display' || $params_fature['type'] == 'display_none' || $params_fature['type'] == 'relate_string' || $params_fature['type'] == 'auto_number'))
                    ) {
                        $status = true;
                        break;
                    }
                }
            }

            // удаляем
            if ($status == false) {
                if ($params['type'] == 'select') {
                    $table_module = ModuleTablesModel::model()->find([
                        'condition' => 'copy_id=:copy_id AND type = "relate_select" AND parent_field_name=:parent_field_name',
                        'params'    => [':copy_id' => $this->_extension_copy->copy_id, ':parent_field_name' => $params['name']]
                    ]);
                    $this->dropColumn($table_name, $params['name']);
                    $this->_orm_command->setText('DROP TABLE IF EXISTS {{' . $table_module->table_name . '}}')->execute();
                    $table_module->delete();
                    $this->_deleted_field_name_list[] = $params['name'];
                } elseif ($params['type'] == 'file' || $params['type'] == 'file_image' || $params['type'] == 'attachments') {
                    $this->_file_keys_for_delete = array_merge($this->_file_keys_for_delete, FileOperations::getInstance()->getKeysByField($table_name, $params['name']));
                    $this->dropColumn($table_name, $params['name']);
                    $this->_deleted_field_name_list[] = $params['name'];
                } else {
                    $this->dropColumn($table_name, $params['name']);
                    $this->_deleted_field_name_list[] = $params['name'];

                    if ($params['type'] == \Fields::MFT_DATETIME && $params['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                        $this->_orm_command->dropColumn('{{' . $table_name . '}}', $params['name'] . '_ad');
                    }
                    if ($params['type'] == 'access') {
                        $this->_orm_command->dropColumn('{{' . $table_name . '}}', $params['name'] . '_type');
                    }
                }
            }
        }

        // блок поле fields_cloud
        if (isset($this->_schema['fields_cloud'])) {
            foreach ($this->_schema['fields_cloud'] as $params) {
                $status = false;
                // пропускаем поля, что не надо удалять
                if (isset($this->_schema_fature['fields_cloud']) && !empty($this->_schema_fature['fields_cloud'])) {
                    foreach ($this->_schema_fature['fields_cloud'] as $params_fature) {
                        if (($params['name'] == $params_fature['name'] && $params['type'] == $params_fature['type'])) {
                            $status = true;
                            break;
                        }
                    }
                }
                // удаляем
                if ($status == false) {
                    if ($params['type'] == 'activity') {
                        $this->blocks_for_delete['activity_messages'] = true;
                    } elseif ($params['type'] == 'relate_participant') {
                        $fpo = 1;
                        if (isset($this->_schema_fature['fields_cloud']) && !empty($this->_schema_fature['fields_cloud'])) {
                            $fpo = $this->changedParticipantOther($params, $this->_schema_fature['fields_cloud']);
                        }
                        if ($fpo === 1) {
                            $this->blocks_for_delete['participant_all'] = true;
                        }
                        if ($fpo === 2) {
                            $this->blocks_for_delete['participant_responsible'] = true;
                        }
                        if ($fpo === 1 || $fpo === 2) {
                            $this->_deleted_field_name_list[] = $params['name'];
                        }
                    }
                }
            }
        }

    }

    /**
     * поиск изменения блоков типа "участники"
     * relate_participant, [type_view] => block_participant - участники
     * relate_participant, [type_view] => button_responsible - ответственный
     */
    private function changedParticipantOther($params, $schema_fature)
    {
        $result = 1; // 
        foreach ($schema_fature as $params_fature) {
            if ($params['type'] == 'relate_participant' && $params_fature['type'] == 'relate_participant' && $params['type_view'] == 'block_participant' && $params_fature['type_view'] == 'button_responsible') {
                $result = 2;
                break;
            }
            if ($params['type'] == 'relate_participant' && $params_fature['type'] == 'relate_participant' && $params['type_view'] == 'button_responsible' && $params_fature['type_view'] == 'block_participant') {
                $result = 3;
                break;
            }
        }

        return $result;
    }

    /**
     * Поиск удаленных и удаление служебных полей + связаных с ними елементов: таблиц, данных таблиц...
     */
    private function dropSpecialColumns()
    {
        $table_name = $this->_extension_copy->getTableName(null, false);

        // this_template
        if ($this->_extension_copy->getModule(false)->isTemplate($this->_extension_copy) == false) {
            if ($this->isSetField($table_name, 'this_template')) {
                $id_list = [];
                $relate_string_extension_copy = null;
                $relate_string_table = null;

                $relate_tables = ModuleTablesModel::model()->findAll([
                    'condition' => 'relate_copy_id=:relate_copy_id AND `type`="relate_module_one"',
                    'params'    => [
                        ':relate_copy_id' => $this->_extension_copy->copy_id,
                    ]
                ]);
                if (!empty($relate_tables)) {
                    foreach ($relate_tables as $relate_table) {
                        //берем значение первичного поля и проверяем тип relate_string
                        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate_table->copy_id);

                        $first_field_params = $relate_extension_copy->getPrimaryField();
                        if (empty($first_field_params) || $first_field_params['params']['type'] != 'relate_string') {
                            continue;
                        }
                        if ($first_field_params['params']['relate_module_copy_id'] != $this->_extension_copy->copy_id) {
                            continue;
                        }
                        $relate_string_extension_copy = $relate_extension_copy;
                        $relate_string_table = $relate_table;
                        break;
                    }
                }

                // удаляем данные-шаблонов
                $data_model = DataModel::getInstance()
                    ->setSelect($this->_extension_copy->prefix_name . '_id')
                    ->setFrom('{{' . $table_name . '}}')
                    ->andWhere('this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE . '"');
                if (!empty($relate_string_extension_copy)) {  // пропускаем данные, если они связаны по полю Нпзвание...
                    $data_model->andWhere('not exists (SELECT * FROM {{' . $relate_string_table->table_name . '}} WHERE {{' . $table_name . '}}.' . $this->_extension_copy->prefix_name . '_id' . ' = {{' . $relate_string_table->table_name . '}}.' . $this->_extension_copy->prefix_name . '_id)');
                }
                $data_model = $data_model->findAll();

                if (!empty($data_model)) {
                    foreach ($data_model as $value) {
                        $id_list[] = $value[$this->_extension_copy->prefix_name . '_id'];
                    }
                    EditViewDeleteModel::getInstance()
                        ->prepare($this->_extension_copy->copy_id, $id_list)
                        ->delete();
                }
            }
        }
    }

    /**
     * Возвращает список полей для SQL запроса при создании главной таблицы
     */
    private function getFieldColumns($index)
    {
        $columns = [
            $this->_extension_copy->prefix_name . '_id' => FieldTypes::getInstance()->getSqlCreateColumnInteger(['pk' => true]),
            'uid'                                       => FieldTypes::getInstance()->getSqlCreateColumnBigInt(),
            'date_create'                               => FieldTypes::getInstance()->getSqlCreateColumnDateStamp(),
            'date_edit'                                 => FieldTypes::getInstance()->getSqlCreateColumnDateStamp(),
            'user_create'                               => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
            'user_edit'                                 => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
            'this_template'                             => FieldTypes::getInstance()->getSqlCreateColumnEnum([
                'values'  => [EditViewModel::THIS_TEMPLATE_MODULE, EditViewModel::THIS_TEMPLATE_TEMPLATE, EditViewModel::THIS_TEMPLATE_TEMPLATE_CM],
                'default' => '"' . EditViewModel::THIS_TEMPLATE_MODULE . '"'
            ]),
        ];
        /*
        if($this->_extension_copy->getModule(false)->isTemplate($this->_extension_copy)){
            $columns['this_template'] = FieldTypes::getInstance()->getSqlCreateColumnEnum(array('values' => array('1', '0'), 'default' => '"0"'));
        }
        */

        foreach ($this->_schema_fature[$index] as $value) {
            $method_name = 'getSqlCreateColumn' . $value['type_db'];
            $columns[$value['name']] = FieldTypes::getInstance()->$method_name($value);

            // date_time, TYPE_VIEW_BUTTON_DATE_ENDING
            if ($value['type'] == \Fields::MFT_DATETIME && $value['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                $columns[$value['name'] . '_ad'] = FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['0', '1'], 'default' => '"1"']);
            }

            if ($value['type'] == 'access') {
                $columns[$value['name'] . '_type'] = FieldTypes::getInstance()->getSqlCreateColumnTinyInt();
            }
        }

        return $columns;
    }

    /**
     * Создания таблиц для "Select"
     */
    private function createTableSelect()
    {
        if (!isset($this->_schema_fature['select']) || empty($this->_schema_fature['select'])) {
            return false;
        }

        foreach ($this->_schema_fature['select'] as $key => $value) {
            $table_name = $this->_extension_copy->getTableName($key, false);
            $this->_orm_command->setText('DROP TABLE IF EXISTS {{' . $table_name . '}}')->execute();
            $this->_orm_command->createTable('{{' . $table_name . '}}',
                [
                    $key . '_id'              => FieldTypes::getInstance()->getSqlCreateColumnInteger(['pk' => true]),
                    $key . '_title'           => FieldTypes::getInstance()->getSqlCreateColumnString(),
                    $key . '_color'           => FieldTypes::getInstance()->getSqlCreateColumnString(['size' => 20]),
                    $key . '_sort'            => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
                    $key . '_remove'          => FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['1', '0'], 'default' => '"1"']),
                    $key . '_finished_object' => FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['1', '0'], 'default' => '"0"']),
                    $key . '_slug'            => FieldTypes::getInstance()->getSqlCreateColumnString(),
                ],
                'ENGINE=InnoDB DEFAULT CHARSET=utf8'
            );
            $this->insertSelectData($key, $value);
            $this->saveInModuleTablesModel($table_name, 'relate_select', 'belongs_to', $key, $key . '_id');
        }
    }

    /**
     * Обновление данных таблиц "Select"
     */
    private function updateTableSelect()
    {
        if (!isset($this->_schema_fature['select']) || empty($this->_schema_fature['select'])) {
            return false;
        }

        foreach ($this->_schema_fature['select'] as $field_name => $value) {
            $table_name = $this->_extension_copy->getTableName($field_name, false);
            $table = $this->_orm_command->setText('SHOW TABLES like "' . $this->_orm_command->getConnection()->tablePrefix . $table_name . '"')->queryAll();
            if (empty($table)) {
                $this->_orm_command->createTable('{{' . $table_name . '}}',
                    [
                        $field_name . '_id'              => FieldTypes::getInstance()->getSqlCreateColumnInteger(['pk' => true]),
                        $field_name . '_title'           => FieldTypes::getInstance()->getSqlCreateColumnString(),
                        $field_name . '_color'           => FieldTypes::getInstance()->getSqlCreateColumnString(['size' => 20]),
                        $field_name . '_sort'            => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
                        $field_name . '_remove'          => FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['1', '0'], 'default' => '"1"']),
                        $field_name . '_finished_object' => FieldTypes::getInstance()->getSqlCreateColumnEnum(['values' => ['1', '0'], 'default' => '"0"']),
                        $field_name . '_slug'            => FieldTypes::getInstance()->getSqlCreateColumnString(),
                    ],
                    'ENGINE=InnoDB DEFAULT CHARSET=utf8'
                );
                $this->insertSelectData($field_name, $value);
                $this->saveInModuleTablesModel($table_name, 'relate_select', 'belongs_to', $field_name, $field_name . '_id');
            } else {
                $this->_orm_command->truncateTable('{{' . $table_name . '}}');
                $this->insertSelectData($field_name, $value);
            }
        }
    }

    /**
     *  Создает массив данных для вставки в таблицу БД
     */
    private function insertSelectData($field_name, array $data)
    {
        if (!empty($data)) {
            foreach ($data['value'] as $key => $value) {
                $insert_value = [
                    $field_name . '_id'    => $key,
                    $field_name . '_title' => $value,
                    $field_name . '_color' => null,
                ];
                if (isset($data['select_color'][$key])) {
                    $insert_value[$field_name . '_color'] = $data['select_color'][$key];
                }
                if (isset($data['select_remove_forbid'][$key])) {
                    $insert_value[$field_name . '_remove'] = $data['select_remove_forbid'][$key];
                }
                if (isset($data['select_finished_object'][$key])) {
                    $insert_value[$field_name . '_finished_object'] = $data['select_finished_object'][$key];
                }
                $insert_value[$field_name . '_sort'] = (isset($data['select_sort'][$key])) ? $data['select_sort'][$key] : 0;
                if (isset($data['select_slug'][$key])) {
                    $insert_value[$field_name . '_slug'] = $data['select_slug'][$key];
                }

                $this->_orm_command->insert('{{' . $this->_extension_copy->getTableName($field_name, false) . '}}', $insert_value);
            }
        }

    }

    /**
     * Создание таблицы для cвязаных модулей
     */
    private function createRelateTable(array $params)
    {
        $model_table = $this->getModuleTableEntity($params, false);
        if (!empty($model_table)) {
            return false;
        }

        $table_name = $this->_extension_copy->getTableName(
            ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id'])->prefix_name .
            '_' .
            $params['relate_index']
            , false);

        $table = $this->_orm_command->setText('SHOW TABLES like "' . $this->_orm_command->getConnection()->tablePrefix . $table_name . '"')->queryAll();

        if (!empty($table)) {
            return false;
        }

        $this->_orm_command->createTable('{{' . $table_name . '}}',
            [
                'id'                                                                                         => FieldTypes::getInstance()->getSqlCreateColumnInteger(['pk' => true]),
                $this->_extension_copy->prefix_name . '_id'                                                  => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
                ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id'])->prefix_name . '_id' => FieldTypes::getInstance()->getSqlCreateColumnInteger(),
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->_orm_command->createIndex($table_name . '_i1', '{{' . $table_name . '}}', $this->_extension_copy->prefix_name . '_id');
        $this->_orm_command->createIndex($table_name . '_i2', '{{' . $table_name . '}}', ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id'])->prefix_name . '_id');

        return $table_name;
    }

    /**
     * Возвращает данные о таблице
     */
    private function getModuleTableEntity($element_schema, $get_partner = false)
    {
        $params = [
            ':copy_id'        => $this->_extension_copy->copy_id,
            ':relate_copy_id' => $element_schema['relate_module_copy_id'],
        ];
        if ($get_partner == true) {
            $params = [
                ':copy_id'        => $element_schema['relate_module_copy_id'],
                ':relate_copy_id' => $this->_extension_copy->copy_id,
            ];
        }

        $model = ModuleTablesModel::model()->find([
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type in ("relate_module_one", "relate_module_many")',
            'params'    => $params
        ]);

        return $model;
    }

    /**
     * Создание таблицы для SubModule
     */
    private function createTableSubModule()
    {
        if (!isset($this->_schema_fature['sub_module']) || empty($this->_schema_fature['sub_module'])) {
            return false;
        }

        foreach ($this->_schema_fature['sub_module'] as $value) {
            $model_table = $this->getModuleTableEntity($value, true);

            if ($model_table) {
                $this->saveInModuleTablesModel($model_table->table_name,
                    'relate_module_many',
                    'many_many',
                    $this->_extension_copy->prefix_name . '_id',
                    $model_table->parent_field_name,
                    $model_table->copy_id
                );
            } else {
                $table_name = $this->createRelateTable($value);
                if ($table_name !== false) {
                    $this->saveInModuleTablesModel($table_name,
                        'relate_module_many',
                        'many_many',
                        $this->_extension_copy->prefix_name . '_id',
                        ExtensionCopyModel::model()->findByPk($value['relate_module_copy_id'])->prefix_name . '_id',
                        $value['relate_module_copy_id']
                    );
                }
            }
        }
    }

    /**
     * Создание таблицы СМ для Процессов в случае, если СДМ или СМ смодулем процессы небыл дабавлен
     */
    private function createTableSubModuleProcess()
    {
        if ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS) {
            return;
        }

        $params = [
            'relate_module_copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
            'relate_index'          => 1,
        ];

        $module_tables = $this->getModuleTableEntity($params, true);

        if ($module_tables) {
            $module_tables_process = $this->getModuleTableEntity($params, false);

            if ($module_tables_process == false) {
                $module_tables->setIsNewRecord(true);
                $module_tables->setAttributes([
                    'id'                => null,
                    'copy_id'           => $module_tables->relate_copy_id,
                    'relate_copy_id'    => $module_tables->copy_id,
                    'type'              => \ModuleTablesModel::TYPE_RELATE_MODULE_MANY,
                    'relate_type'       => 'many_many',
                    'parent_field_name' => $module_tables->relate_field_name,
                    'relate_field_name' => $module_tables->parent_field_name,
                ]);
                $module_tables->save();

            }

            return;
        }

        $table_name = $this->createRelateTable($params);

        if ($table_name !== false) {
            $this->saveInModuleTablesModel(
                $table_name,
                'relate_module_many',
                'many_many',
                $this->_extension_copy->prefix_name . '_id',
                ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id'])->prefix_name . '_id',
                $params['relate_module_copy_id']
            );
        }
    }

    /**
     *  Запись в базу ModuleTables о связаных модулях
     */
    private function createTableRelate()
    {
        $field_types = ['relate', 'relate_string'];

        foreach ($field_types as $type) {
            if (!isset($this->_schema_fature[$type])) {
                continue;
            }

            foreach ($this->_schema_fature[$type] as $params) {

                // 1. еслм смена из СМ на СДМ
                $module_tables_model = \ModuleTablesModel::getRelateModel(
                    $this->_extension_copy->copy_id,
                    $params['relate_module_copy_id'],
                    [
                        \ModuleTablesModel::TYPE_RELATE_MODULE_ONE,
                        \ModuleTablesModel::TYPE_RELATE_MODULE_MANY,
                    ]
                );
                if ($module_tables_model) {
                    if ($module_tables_model->type == \ModuleTablesModel::TYPE_RELATE_MODULE_MANY) {
                        $module_tables_model->setAttributes([
                            'type'        => \ModuleTablesModel::TYPE_RELATE_MODULE_ONE,
                            'relate_type' => 'belongs_to',
                        ]);
                        $module_tables_model->save();

                        continue;
                    }

                } else {
                    // 2. Если новый - ищем уже сущеструющую связь в привязанном модуле
                    $module_tables_model = \ModuleTablesModel::getRelateModel(
                        $params['relate_module_copy_id'],
                        $this->_extension_copy->copy_id,
                        [
                            \ModuleTablesModel::TYPE_RELATE_MODULE_ONE,
                            \ModuleTablesModel::TYPE_RELATE_MODULE_MANY,
                        ]
                    );

                    if ($module_tables_model) {
                        $module_tables_model->setIsNewRecord(true);
                        $module_tables_model->setAttributes([
                            'id'                => null,
                            'copy_id'           => $module_tables_model->relate_copy_id,
                            'relate_copy_id'    => $module_tables_model->copy_id,
                            'type'              => \ModuleTablesModel::TYPE_RELATE_MODULE_ONE,
                            'relate_type'       => 'belongs_to',
                            'parent_field_name' => $module_tables_model->relate_field_name,
                            'relate_field_name' => $module_tables_model->parent_field_name,
                        ]);
                        $module_tables_model->save();

                    } else {
                        // 3. Создаем новую таблицу
                        $table_name = $this->createRelateTable($params);
                        if ($table_name !== false) {
                            $this->saveInModuleTablesModel($table_name,
                                'relate_module_one',
                                'belongs_to',
                                $this->_extension_copy->prefix_name . '_id',
                                ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id'])->prefix_name . '_id',
                                $params['relate_module_copy_id']
                            );
                        }
                    }
                }

            }
        }
    }

    private function getBlockedRelateCopyIdList()
    {
        return [
            \ExtensionCopyModel::MODULE_PROCESS,
        ];
    }

    /**
     * Поиск удаленных субмодулей и удаление связующей таблицы
     */
    private function dropTableSubModule()
    {
        if (!isset($this->_schema['sub_module']) || empty($this->_schema['sub_module'])) {
            return false;
        }

        foreach ($this->_schema['sub_module'] as $sub_module_params) {
            if (in_array($sub_module_params['relate_module_copy_id'], $this->getBlockedRelateCopyIdList())) {
                continue;
            }

            $status = false;
            if (isset($this->_schema_fature['sub_module']) && !empty($this->_schema_fature['sub_module'])) {
                foreach ($this->_schema_fature['sub_module'] as $params_fature) {
                    if ($sub_module_params['name'] == $params_fature['name'] && $sub_module_params['relate_module_copy_id'] == $params_fature['relate_module_copy_id']) {
                        $status = true;
                        break;
                    }
                }
            }

            // если СМ сменили на СДМ
            if ($status == false) {
                $field_types = ['relate', 'relate_string'];
                foreach ($field_types as $type) {
                    if (isset($this->_schema_fature[$type]) && !empty($this->_schema_fature[$type])) {
                        foreach ($this->_schema_fature[$type] as $params_fature_2) {
                            if ($sub_module_params['relate_module_copy_id'] == $params_fature_2['relate_module_copy_id']) {
                                $status = true;
                                break;
                            }
                        }
                    }
                }
                if ($status == true) {
                    $model_table = $this->getModuleTableEntity($sub_module_params);
                    if (!empty($model_table)) {
                        $model_table->type = 'relate_module_one';
                        $model_table->relate_type = 'belongs_to';
                        $model_table->save();
                    }
                }
            }

            if ($status == false) {
                $this->deleteTemplateCM($sub_module_params);
                $model_table = $this->getModuleTableEntity($sub_module_params, false);
                if (empty($model_table)) {
                    continue;
                }
                $model_table_relate = $this->getModuleTableEntity($sub_module_params, true);
                if (empty($model_table_relate)) {
                    $this->_orm_command->setText('DROP TABLE IF EXISTS {{' . $model_table->table_name . '}}')->execute();
                }
                $model_table->delete();
            }
        }
    }

    /**
     * удяляем данные связаного модуля, если this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE_CM
     */
    private function deleteTemplateCM($sub_module_params)
    {
        EditViewDeleteModel::getInstance()->deleteTemplateCM($this->_extension_copy, $sub_module_params);
    }

    /**
     * Поиск удаленных типов полей "Связь с другим модулем" и удаление связующей таблицы
     */
    private function dropTableRelate()
    {
        $field_types = ['relate', 'relate_string'];
        foreach ($field_types as $type) {
            if (!isset($this->_schema[$type]) || empty($this->_schema[$type])) {
                continue;
            }

            foreach ($this->_schema[$type] as $params) {

                $status = false;
                if (isset($this->_schema_fature[$type]) && !empty($this->_schema_fature[$type])) {
                    foreach ($this->_schema_fature[$type] as $params_fature) {
                        if ($params['name'] == $params_fature['name'] && $params['relate_module_copy_id'] == $params_fature['relate_module_copy_id']) {
                            $status = true;
                            break;
                        }
                        if ($params['relate_module_copy_id'] == $params_fature['relate_module_copy_id'] &&
                            (($params['type'] == 'relate' && $params_fature['type'] == 'relate_string') || ($params['type'] == 'relate_string' && $params_fature['type'] == 'relate'))) {
                            $status = true;
                            break;
                        }
                    }
                }

                // поиск и удаляем связи, если изменили с СДМ через название на элемент СДМ
                if ($params['type'] == 'relate_string') {
                    if (
                        isset($this->_schema_fature['relate']) &&
                        !empty($this->_schema_fature['relate']) &&
                        in_array($params['relate_module_copy_id'], $this->getBlockedRelateCopyIdList()) == false
                    ) {
                        foreach ($this->_schema_fature['relate'] as $params_fature) {
                            if ($params['type'] == 'relate_string' && $params_fature['type'] == 'relate' && $params['relate_module_copy_id'] == $params_fature['relate_module_copy_id']) { // удаляем
                                $model_table = $this->getModuleTableEntity($params);
                                if (!empty($model_table)) {
                                    $this->_orm_command->truncateTable('{{' . $model_table->table_name . '}}');
                                }
                            }
                        }
                    }
                }

                // если СДМ сменили на СМ
                if ($status == false) {
                    if (isset($this->_schema_fature['sub_module']) && !empty($this->_schema_fature['sub_module'])) {
                        foreach ($this->_schema_fature['sub_module'] as $params_fature_2) {
                            if ($params['relate_module_copy_id'] == $params_fature_2['relate_module_copy_id']) {
                                $status = true;
                                break;
                            }
                        }
                    }

                    if ($status == true) {
                        $model_table = $this->getModuleTableEntity($params);
                        if (!empty($model_table)) {
                            $model_table->type = 'relate_module_many';
                            $model_table->relate_type = 'many_many';
                            $model_table->save();
                        }
                    }

                }

                if ($status == false) { // чистим таблицу и(возможно) удаляем таблицу связей
                    $model_table = $this->getModuleTableEntity($params, false);
                    if (empty($model_table)) {
                        continue;
                    }

                    if (in_array($params['relate_module_copy_id'], $this->getBlockedRelateCopyIdList())) {
                        $model_table->type = 'relate_module_many';
                        $model_table->relate_type = 'many_many';
                        $model_table->save();
                        continue;
                    }

                    $model_table_submodule = $this->getModuleTableEntity($params, true);
                    if (empty($model_table_submodule)) {
                        $this->_orm_command->setText('DROP TABLE IF EXISTS {{' . $model_table->table_name . '}}')->execute();
                    }

                    $model_table->delete();
                    $this->_deleted_field_name_list[] = $params['name'];
                }
            }
        }
    }

    /**
     * Удаляем все связаные данные по удаленных полям
     */
    private function dropRelateData()
    {
        EmptyRelateDataModel::dropFiles($this->_file_keys_for_delete);
        foreach ($this->blocks_for_delete as $block_name => $status) {
            switch ($block_name) {
                case 'activity_messages' :
                    if ($status) {
                        EmptyRelateDataModel::dropActivityMessages($this->_extension_copy->copy_id);
                    }
                    break;
                case 'participant_all' :
                    if ($status) {
                        EmptyRelateDataModel::dropParticipant($this->_extension_copy->copy_id);
                    }
                    break;
                case 'participant_responsible' :
                    if ($status) {
                        EmptyRelateDataModel::dropParticipant($this->_extension_copy->copy_id, false);
                    }
                    break;
            }
        }
    }

    /**
     * удаляет фильтра, у которых были использованы удаленные поля
     */
    private function clearModuleFilter()
    {
        if (empty($this->_deleted_field_name_list)) {
            return;
        }
        foreach ($this->_deleted_field_name_list as $field_name) {
            $filter_model = FilterModel::model()->findAll([
                'condition' => 'copy_id=:copy_id',
                'params'    => ['copy_id' => $this->_extension_copy->copy_id],
            ]);
            if (empty($filter_model)) {
                return;
            }
            foreach ($filter_model as $filter) {
                $params = $filter->getParams();
                if (empty($params)) {
                    continue;
                }
                foreach ($params as $param_value) {
                    if ($param_value['name'] == $field_name) {
                        $filter->delete();
                        break;
                    }
                }
            }
        }
    }

    /**
     * исполняет дополнительные операции после проведения инсталяции
     */
    private function runLastOperation()
    {
        ValidateConfirmActions::getInstance()->runAction(ValidateConfirmActions::ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE);
        ValidateConfirmActions::getInstance()->runAction(ValidateConfirmActions::ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE);
        ValidateConfirmActions::getInstance()->runAction(ValidateConfirmActions::ACTION_MODULE_DELETE_TEMPLATES);
        //ValidateConfirmActions::getInstance()->runAction(ValidateConfirmActions::ACTION_SUB_MODULE_TEMPLATE_REMOVE);
        ValidateConfirmActions::getInstance()->runAction(ValidateConfirmActions::ACTION_PROCESS_BO_CLEAR);

        $this->validateConfirmActionsInstalledModules();
    }

    /**
     * выполнение валидации в подключенных модулях
     */
    private function validateConfirmActionsInstalledModules()
    {
        $params = \ValidateActionsModel::getParams(\ValidateActionsModel::KEY_CONSTRUCTOR_MODULE_SAVE);
        if (empty($params)) {
            return;
        }

        foreach ($params as $param) {
            ExtensionCopyModel::model()->findByPk($param['copy_id'])->getModule(false);
            $class = new $param['class'];
            foreach ($param['actions'] as $action) {
                $class->runAction($action);
            }
        }
    }

}





