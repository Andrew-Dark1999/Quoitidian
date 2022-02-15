<?php

class ExcelImport
{

    private $_extension_copy;

    private $_to_import_logical = [];

    private $_to_import_select = [];

    private $_insert_model = null;

    private $_import_relate_model = null;

    //импорт возможен только при условии наличия столбца с ID
    private $_only_with_PK = false;

    private $_this_template = false;

    private $_pci;

    private $_pdi;

    private $field_block_name = false;

    private $fields_blocks_data = false;

    public static function getInstance()
    {
        return new self();
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->_extension_copy = $extension_copy;

        return $this;
    }

    public function setSchema()
    {
        $schema = $this->_extension_copy->getSchemaParse();

        if (empty($schema) || !isset($schema['elements'])) {
            return $this;
        }
        foreach ($schema['elements'] as $element) {
            if (isset($element['field'])) {
                if ($element['field']['params']['type'] == 'activity') {
                    continue;
                }

                $this->_schema[] = $element['field'];
            }
        }

        return $this;
    }

    public function setThisTemplate($this_template)
    {
        $this->_this_template = $this_template;

        return $this;
    }

    public function setPciPdi($pci, $pdi)
    {
        $this->_pci = $pci;
        $this->_pdi = $pdi;

        return $this;
    }

    public function onlyWithPK()
    {
        $this->_only_with_PK = true;

        return $this;
    }

    // подготовка списков
    private function prepareSelectList($params)
    {
        if ($params['params']['type'] != 'select') {
            return;
        }

        $field_name = $params['params']['name'];
        $data = DataModel::getInstance()
            ->setSelect([$field_name . '_id', $field_name . '_title'])
            ->setFrom($this->_extension_copy->getTableName($field_name))
            ->findAll();

        $list = [];
        if (!empty($data)) {
            foreach ($data as $row) {
                $list[$row[$field_name . '_title']] = $row[$field_name . '_id'];
            }
        }
        $this->_to_import_select[$field_name] = $list;
    }

    /**
     * Получаем id записей
     */
    private function getImportDataIds($sheet)
    {

        $columns = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

        $cell_primary_id = false;
        $ids = [];

        for ($cell_index = 0; $cell_index < $columns; $cell_index++) {
            $activeCell = $sheet->getCellByColumnAndRow($cell_index, 1);
            $value_t = addslashes($activeCell->getValue());
            $field = $this->parsingFieldName($value_t);

            if ($field == $this->_extension_copy->prefix_name . '_id') {
                $cell_primary_id = $cell_index;
                break;
            }

        }

        if ($cell_primary_id !== false) {
            $rows = $sheet->getHighestRow();
            $columns = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
            for ($row_index = 2; $row_index <= $rows; $row_index++) {
                $activeCell = $sheet->getCellByColumnAndRow($cell_primary_id, $row_index);
                $ids [] = addslashes($activeCell->getValue());
            }

        }

        return [
            'cell_primary_id' => $cell_primary_id,
            'ids'             => $ids,
        ];

    }

    /**
     * Преобразовывает и возвращает значения данних в их внутринее значения (для БД)
     */
    private function getImportData($field_name, $field_type, $value)
    {
        switch ($field_type) {
            case 'logical' :
                if ($value === '1' || $value === '0') {
                    return $value;
                }
                if (isset($this->_to_import_logical[$value])) {
                    return (string)$this->_to_import_logical[$value];
                }
                break;
            case 'select' :
                if (!empty($this->_to_import_select[$field_name]) && isset($this->_to_import_select[$field_name][$value])) {
                    return $this->_to_import_select[$field_name][$value];
                }
                break;
            case 'relate' :
                return $this->_import_relate_model->getCheckedId($field_name, $value);

            case 'relate_participant' :
                return $this->_import_relate_model->getId($field_name, $value);

            case 'display_block' :
                if ($this->field_block_name && $this->fields_blocks_data) {
                    if ($this->field_block_name == $field_name) {
                        if (isset($this->fields_blocks_data[$value])) {
                            return $this->fields_blocks_data[$value];
                        }
                    }
                }
                break;

            case 'file':
            case 'file_image':
                $full_file_name = $value;

                $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $full_file_name;
                $relate_key = md5($relate_key);

                $uploads_model = new UploadsModel();
                if ($field_type == 'file_image' && $uploads_model->checkImageFile($uploads_model->getFileName($full_file_name)) == false) {
                    break;
                }
                $uploads_model->relate_key = $relate_key;
                $uploads_model->setFileType($field_type);
                $uploads_model->setThumbScenario('upload');
                if ($uploads_model->copyFromSource($full_file_name)) {
                    return [
                        'relate_key'       => $relate_key,
                        'file_path'        => $uploads_model->file_path,
                        'file_name'        => $uploads_model->getFileName(),
                        'file_title'       => $uploads_model->getFileTitle(),
                        'file_date_upload' => date('Y-m-d H:i:s'),
                        'date_create'      => date('Y-m-d H:i:s'),
                        'thumbs'           => $uploads_model->thumbs,
                    ];
                }
                break;

            default :
                return $value;
        }
    }

    /**
     * извлекаем из названия поля в документе название поля в БД
     */
    private function parsingFieldName($field_name)
    {
        $result = [];
        $count = preg_match('/\[(\w)+\]/', $field_name, $result);

        if ($count == false) {
            $result = null;
        } else {
            $result = substr($result[0], 1, -1);
        }

        return $result;
    }

    /**
     * prepareDataToEditViewModel
     */
    private function prepareDataToEditViewModel($fields, $data, $cell_index)
    {
        $result = [
            'EditViewModel'  => [],
            'element_relate' => [],
            'responsible'    => [],
        ];
        $element_relate = [];
        $files = [];
        $responsible = null;

        $insert_data_model = $this->_insert_model->getQIDataModel('important');

        for ($i = 0; $i < $cell_index; $i++) {
            if (!isset($fields[$i])) {
                continue;
            }

            $field_name = $fields[$i];
            $type = $fields[$fields[$i]];

            switch ($type) {
                case 'relate':
                    $this->_insert_model->setQIDataModelKey($field_name);
                    $relate_fields = $this->_insert_model->getQIDataModel()->getFields(false);
                    $relate_id = $data[$field_name];
                    if (empty($relate_id)) {
                        continue;
                    }

                    $element_relate[$field_name] = [
                        $relate_fields[0] => $insert_data_model->getPrimaryKey($field_name),
                        $relate_fields[1] => $relate_id,
                    ];
                    break;

                case 'relate_participant':
                    $ug_id = $data[$field_name];
                    if (empty($ug_id)) {
                        break;
                    }
                    $responsible[$field_name] = [
                        'copy_id'       => $this->_extension_copy->copy_id,
                        'data_id'       => $insert_data_model->getPrimaryKey($field_name),
                        'ug_id'         => $ug_id,
                        'ug_type'       => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                        'responsible'   => '1',
                        'date_create'   => date('Y-m-d H:i:s'),
                        'user_create'   => WebUser::getUserId(),
                        'this_template' => '0',
                    ];
                    break;
                case 'file' :
                case 'file_image' :
                    if (empty($data[$field_name])) {
                        $result['EditViewModel'][$field_name] = null;
                        break;
                    }

                    $file_data = $data[$field_name];
                    $result['EditViewModel'][$field_name] = $file_data['relate_key'];
                    $files[$field_name] = [
                        'relate_key'       => $file_data['relate_key'],
                        'file_source'      => UploadsModel::SOURCE_MODULE,
                        'file_path'        => $file_data['file_path'],
                        'file_name'        => $file_data['file_name'],
                        'file_title'       => $file_data['file_title'],
                        'file_date_upload' => $file_data['date_create'],
                        'date_create'      => $file_data['date_create'],
                        'user_create'      => WebUser::getUserId(),
                        'status'           => 'asserted',
                        'thumbs'           => $file_data['thumbs'],
                        'copy_id'          => $this->_extension_copy->copy_id,
                    ];
                    break;

                default:
                    $result['EditViewModel'][$field_name] = $data[$field_name];

            }
        }
        $result['element_relate'] = $element_relate;
        $result['responsible'] = $responsible;
        $result['files'] = $files;

        return $result;
    }

    private function setQueryInsertModelForRelate($params)
    {
        if (!in_array($params['params']['type'], ['relate', 'relate_participant', 'file', 'file_image'])) {
            return;
        }

        switch ($params['params']['type']) {
            case 'relate':
                $extension_copy = ExtensionCopyModel::model()->findByPk($params['params']['relate_module_copy_id']);

                $relate_table = ModuleTablesModel::model()->find([
                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                    'params'    => [
                        ':copy_id'        => $this->_extension_copy->copy_id,
                        ':relate_copy_id' => $extension_copy->copy_id,
                    ]
                ]);
                $table_name = $relate_table->table_name;
                $fields = [$relate_table->parent_field_name, $relate_table->relate_field_name];
                break;

            case 'relate_participant':
                $table_name = 'participant';
                $fields = ['copy_id', 'data_id', 'ug_id', 'ug_type', 'responsible', 'date_create', 'user_create', 'this_template'];
                break;

            case 'file':
            case 'file_image':
                $table_name = 'uploads';
                $fields = ['relate_key', 'file_source', 'file_path', 'file_name', 'file_title', 'file_date_upload', 'date_create', 'user_create', 'status', 'thumbs', 'copy_id'];
                break;

        }

        $this->_insert_model
            ->setQIDataModelKey($params['params']['name'])
            ->setQIDataModel(new QueryInsertDataModel())
            ->setTableName($table_name)
            ->setFields($fields);
    }

    /**
     * Удаляем некоторые правила валидации
     *
     * @param $edit_model
     */
    private function deleteSomeRulesFromDinamicParams($dinamic_params)
    {
        $rules_tmp = [];
        foreach ($dinamic_params['params']['rules'] as $rules) {
            if ($rules[1] == 'required' || $rules[1] == 'relateCheckRequired') {
                continue;
            }
            $rules_tmp[] = $rules;
        }

        $dinamic_params['params']['rules'] = $rules_tmp;
    }

    /**
     *   импорт данных
     */
    public function import()
    {
        ini_set('max_execution_time', 3600); // 1ч

        $validate = new Validate();

        $excel_object = new PhpExcel();
        $upload = CUploadedFile::getInstanceByName('file');

        $obj_reader = PHPExcel_IOFactory::createReaderForFile($upload->getTempName());
        $obj_reader->setReadDataOnly(true);
        $xls = $obj_reader->load($upload->getTempName());

        $this->_import_relate_model = ImportRelatesModel::getInstance()
            ->setThisTemplate($this->_this_template)
            ->setPciPdi($this->_pci, $this->_pdi);

        $schema_parser = $this->_extension_copy->getSchemaParse();

        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        if ($this->_only_with_PK) {
            //файл должен содержать столбец с ID
            $PK_is_exists = false;
            $columns = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
            for ($cell_index = 0; $cell_index < $columns; $cell_index++) {
                $activeCell = $sheet->getCellByColumnAndRow($cell_index, 1);
                $value_t = addslashes($activeCell->getValue());
                $field = $this->parsingFieldName($value_t);

                if ($field == $this->_extension_copy->prefix_name . '_id') {
                    $PK_is_exists = true;
                    break;
                }

            }

            if (!$PK_is_exists) {
                $validate->addValidateResult('w', Yii::t('messages', 'Import is impossible without primary key column'));

                return [
                    'status'  => false,
                    'message' => $validate->getValidateResult(),
                ];
            }
        }

        $isFirstRow = true;
        $fields = [];

        $this->_to_import_logical = array_flip(Fields::getInstance()->getLogicalData());

        if (!$this->_extension_copy->isShowAllBlocks()) {
            //включен параметр "показ одного блока"
            $block_field_data = $this->_extension_copy->getFieldBlockData();
            if (isset($block_field_data['name'])) {
                $this->field_block_name = $block_field_data['name'];
                $this->fields_blocks_data = [];
                $blocks = $this->_extension_copy->getSchemaBlocksData();
                if (count($blocks) > 0) {
                    foreach ($blocks as $block) {
                        $this->fields_blocks_data[$block['title']] = $block['unique_index'];
                    }
                }
            }
        }

        // идем по данных
        $alias = 'evm_' . $this->_extension_copy->copy_id;
        $dinamic_params = [
            'tableName' => $this->_extension_copy->getTableName(null, false),
            'params'    => Fields::getInstance()->getActiveRecordsParams($schema_parser),
        ];

        $this->deleteSomeRulesFromDinamicParams($dinamic_params);

        $edit_view_model = EditViewModel::modelR($alias, $dinamic_params, true);
        $edit_view_model->extension_copy = $this->_extension_copy;
        $edit_view_model->setElementSchema($schema_parser);
        $edit_view_model->setTruncateLongValue(true);

        $table_fields = array_keys($edit_view_model->attributes);
        unset($table_fields[$this->_extension_copy->prefix_name . '_id']);

        //находим id записей импортируемого файла
        $skipped = [];
        $prepare = $this->getImportDataIds($sheet);

        if ($prepare['cell_primary_id'] !== false && !empty($prepare['ids'])) {
            $in = "'" . implode("','", $prepare['ids']) . "'";

            //ищем записи с такими id в базе данных, найденные пропускаем для последущей обработки
            $exists_cards = \DataModel::getInstance()->setFrom('{{' . $this->_extension_copy->getTableName(null, false) . '}}')->setWhere($this->_extension_copy->prefix_name . "_id" . " in ($in)")->findAll();
            if (!empty($exists_cards)) {
                foreach ($exists_cards as $exists_card) {
                    $skipped [] = $exists_card[$this->_extension_copy->prefix_name . '_id'];
                }
            }

        }

        //new QueryInsertDataModel
        $last_id = DataModel::getInstance()->setText('Select max(' . $this->_extension_copy->prefix_name . '_id' . ') FROM {{' . $this->_extension_copy->getTableName(null, false) . '}}')->findScalar();
        $insert_data_model = new \QueryInsertDataModel();
        $insert_data_model
            ->setPrimaryFieldName($this->_extension_copy->prefix_name . '_id')
            ->setPrimaryKeyStart($last_id + 1)
            ->setThisTemplate($this->_this_template);

        //new QueryInsertModel
        $this->_insert_model = QueryInsertModel::getInstance()
            ->setQIDataModelKey('important')
            ->setQIDataModel($insert_data_model)
            ->setTableName($dinamic_params['tableName'])
            ->setFields($table_fields);

        // обходим строки
        $rows = $sheet->getHighestRow();
        $rows_imported = 0;

        //truncated_long_fields
        $tlf = [];

        for ($row_index = 1; $row_index <= $rows; $row_index++) {
            $data = [];
            $columns = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

            if (!empty($skipped) && !$isFirstRow) {
                $cell = $sheet->getCellByColumnAndRow($prepare['cell_primary_id'], $row_index);
                if (in_array(addslashes($cell->getValue()), $skipped)) {
                    //текущая запись имеется, пропускаем
                    continue;
                }
            }

            for ($cell_index = 0; $cell_index < $columns; $cell_index++) {
                $activeCell = $sheet->getCellByColumnAndRow($cell_index, $row_index);
                $value_t = addslashes($activeCell->getValue());

                if (!$isFirstRow && !empty($fields) && isset($fields[$cell_index]) && $fields[$fields[$cell_index]] == 'datetime') {
                    if (is_numeric($value_t)) {
                        $value_t = date('Y-m-d 00:00:00', PHPExcel_Shared_Date::ExcelToPHP($value_t));
                    }
                }

                // названия полей
                if ($isFirstRow == true) {
                    $fields[$cell_index] = $this->parsingFieldName($value_t);
                    // данные
                } else {
                    if (isset($fields[$cell_index])) {
                        $data[$fields[$cell_index]] = $this->getImportData($fields[$cell_index], $fields[$fields[$cell_index]], $value_t);
                    }
                }
            }

            // сами данные
            if ($isFirstRow == false) {
                // собираем sql для insert-a
                if (!empty($data)) {
                    $data = $this->prepareDataToEditViewModel($fields, $data, $cell_index);

                    $edit_view_model->setMyAttributes($data['EditViewModel']);

                    //validate
                    if ($edit_view_model->validate()) {
                        //important
                        $this->_insert_model
                            ->setQIDataModelKey('important')
                            ->appendValues($edit_view_model->attributes);

                        // relate tables
                        if (!empty($data['element_relate'])) {
                            foreach ($data['element_relate'] as $field_name => $element_relate) {
                                $this->_insert_model
                                    ->setQIDataModelKey($field_name)
                                    ->appendValues($element_relate);
                            }
                        }

                        // responsible
                        if (!empty($data['responsible'])) {
                            foreach ($data['responsible'] as $field_name => $responsible) {
                                $this->_insert_model
                                    ->setQIDataModelKey($field_name)
                                    ->appendValues($responsible);
                            }
                        }

                        // files
                        if (!empty($data['files'])) {
                            foreach ($data['files'] as $field_name => $element_relate) {
                                $this->_insert_model
                                    ->setQIDataModelKey($field_name)
                                    ->appendValues($element_relate);
                            }
                        }

                        $truncated_long_fields = $edit_view_model->getTruncatedLongFields();
                        if ($truncated_long_fields) {
                            $tlf = array_unique(array_merge($tlf, $truncated_long_fields));
                        }

                        $rows_imported++;
                    } else {
                        $er = implode('</br>', $edit_view_model->getErrorsList());
                        $validate->addValidateResult('e', Yii::t('messages', 'String {s} is not stored', ['{s}' => $row_index - 1]) . ':</br>' . $er);
                    }
                }
            } else {
                // парсим заголовок названий полей
                foreach ($fields as $key => $field_name) {
                    $params = $this->_extension_copy->getFieldSchemaParams($field_name);
                    $this->prepareSelectList($params);
                    $this->_import_relate_model->prepareRelateList($params);
                    // пропускаем "плохие" поля
                    if (empty($params)) {
                        unset($fields[$key]);
                    } else {
                        switch ($params['params']['type']) {
                            case 'attachments' :
                            case 'activity' :
                            case 'display_none' :
                            case 'permission' :
                            case 'access' :
                            case 'sub_module' :
                            case 'file_image' :
                            case 'file' :
                            case 'calculated' :
                            case 'relate_dinamic' :
                                unset($fields[$key]);
                            default :
                                $fields[$params['params']['name']] = $params['params']['type'];
                        }
                    }
                    $this->setQueryInsertModelForRelate($params);
                }

            }
            $isFirstRow = false;
        }

        $this->_insert_model->executeAll();
        $this->updateUid();


        // результат
        if ($tlf) {
            $validate->addValidateResult('w', Yii::t('base', 'Warning') . '! ' . Yii::t('messages', 'The data is not imported correctly: some values for field(s) {s} are accustomed to the maximum allowed size', ['{s}' => implode(', ', $this->getFieldsTitle($tlf))]));
        }

        if ($validate->error_count > 0 || $tlf) {
            $validate->addValidateResult('i', Yii::t('messages', 'Data import completed. Imported lines - {s}', ['{s}' => $rows_imported]), null, true);
        } else {
            $validate->addValidateResult('i', Yii::t('messages', 'Data import completed successfully. Imported lines - {s}', ['{s}' => $rows_imported]));
        }

        $temp_file = false;
        if (count($skipped)) {
            $validate->addValidateResult('w', Yii::t('messages', '{s} cards match. Please specify the action for these cards', ['{s}' => count($skipped)]));

            $tmp = $upload->getTempName() . '~';
            if ($upload->saveAs($tmp)) {
                $temp_file = $tmp;
            }
        }

        return [
            'status'   => true,
            'file'     => $temp_file,
            'skipped'  => serialize($skipped),
            'messages' => $validate->getValidateResult(),
        ];

    }

    /**
     * Обновляем uid
     */
    private function updateUid()
    {
        $tableName = $this->_extension_copy->getTableName(null, false);
        $pkFieldName = $this->_extension_copy->getPkFieldName();
        $sql = '
            UPDATE {{'. $tableName .'}}
            SET uid = CONCAT(' . ModuleEntityUid::generateCopyId($this->_extension_copy->copy_id) . ', LPAD(' . $pkFieldName . ', 10, 0))
            WHERE uid = 0     
        ';

        (new DataModel())
            ->setText($sql)
            ->execute();
    }

    private function getFieldsTitle($fields)
    {
        $result = [];

        foreach ($fields as $field_name) {
            $params = $this->_extension_copy->getFieldSchemaParams($field_name);
            $result[$field_name] = '"' . $params['title'] . '"';
        }

        return $result;
    }

    /**
     *   замещение либо совмещение записей
     */
    public function importPostProccesing($file, $skipped, $type)
    {
        ini_set('max_execution_time', 3600); // 1ч

        $validate = new Validate();

        $excel_object = new PhpExcel();
        $obj_reader = PHPExcel_IOFactory::createReaderForFile($file);
        $obj_reader->setReadDataOnly(true);
        $xls = $obj_reader->load($file);

        if (!file_exists($file) || empty($skipped)) {
            return;
        }

        unlink($file);

        $ids = unserialize($skipped);

        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        //модель для связей
        $this->_import_relate_model = ImportRelatesModel::getInstance()
            ->setThisTemplate($this->_this_template)
            ->setPciPdi($this->_pci, $this->_pdi);

        $this->_to_import_logical = array_flip(Fields::getInstance()->getLogicalData());

        //блоки
        if (!$this->_extension_copy->isShowAllBlocks()) {
            //включен параметр "показ одного блока"
            $block_field_data = $this->_extension_copy->getFieldBlockData();
            if (isset($block_field_data['name'])) {
                $this->field_block_name = $block_field_data['name'];
                $this->fields_blocks_data = [];
                $blocks = $this->_extension_copy->getSchemaBlocksData();
                if (count($blocks) > 0) {
                    foreach ($blocks as $block) {
                        $this->fields_blocks_data[$block['title']] = $block['unique_index'];
                    }
                }
            }
        }

        $cell_primary_id = false;

        $rows = $sheet->getHighestRow();
        $columns = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

        //массив полей, перебираем "первые" столбцы
        $fields = [];
        for ($cell_index = 0; $cell_index < $columns; $cell_index++) {
            $activeCell = $sheet->getCellByColumnAndRow($cell_index, 1);
            $value_t = addslashes($activeCell->getValue());

            $field = $this->parsingFieldName($value_t);
            $params = $this->_extension_copy->getFieldSchemaParams($field);

            $this->prepareSelectList($params);
            $this->_import_relate_model->prepareRelateList($params);

            if (empty($params)) {
                if ($field == $this->_extension_copy->prefix_name . '_id') {
                    $cell_primary_id = $cell_index;
                }
            } else {
                switch ($params['params']['type']) {
                    case 'attachments' :
                    case 'activity' :
                    case 'display_none' :
                    case 'permission' :
                    case 'access' :
                    case 'sub_module' :
                    case 'file_image' :
                    case 'file' :
                    case 'calculated' :
                    case 'relate_dinamic' :
                        break;

                    case 'relate':
                        $fields[$cell_index] = ['name' => $field, 'type' => $params['params']['type'], 'relate_module_copy_id' => $params['params']['relate_module_copy_id']];
                        break;

                    default :
                        $fields[$cell_index] = ['name' => $field, 'type' => $params['params']['type']];
                }
            }
        }

        $rows_changed = 0;
        $data = [];

        if (!empty($fields)) {
            for ($row_index = 2; $row_index <= $rows; $row_index++) {

                $skip_cell = addslashes($sheet->getCellByColumnAndRow($cell_primary_id, $row_index)->getValue());
                if (!in_array($skip_cell, $ids)) {
                    continue;
                }

                for ($cell_index = 0; $cell_index < $columns; $cell_index++) {
                    $activeCell = $sheet->getCellByColumnAndRow($cell_index, $row_index);
                    $value_t = addslashes($activeCell->getValue());

                    if (isset($fields[$cell_index]) && $fields[$cell_index]['type'] == 'datetime') {
                        if (is_numeric($value_t)) {
                            $value_t = date('Y-m-d 00:00:00', PHPExcel_Shared_Date::ExcelToPHP($value_t));
                        }

                        if (!empty($value_t)) {
                            $value_t = date('Y-m-d H:i:s', strtotime($value_t));
                        }
                    }

                    // данные
                    if (isset($fields[$cell_index])) {
                        $data[] = [
                            'name'                                      => $fields[$cell_index]['name'],
                            'value'                                     => $this->getImportData($fields[$cell_index]['name'], $fields[$cell_index]['type'], $value_t),
                            'type'                                      => $fields[$cell_index]['type'],
                            'relate_module_copy_id'                     => (!empty($fields[$cell_index]['relate_module_copy_id'])) ? $fields[$cell_index]['relate_module_copy_id'] : false,
                            $this->_extension_copy->prefix_name . '_id' => $skip_cell,
                        ];
                    }
                }
                $rows_changed++;
            }
        }

        if (!empty($data)) {

            \QueryUpdateModel::getInstance()
                ->setPrimaryFieldName($this->_extension_copy->prefix_name . '_id')
                ->setExtensionCopy($this->_extension_copy)
                ->setType($type)
                ->prepareData($data)
                ->execute();
        }

        // результат
        if ($type == 'replace') {
            $validate->addValidateResult('i', Yii::t('messages', 'Data import completed successfully. Replaced lines - {s}', ['{s}' => $rows_changed]));
        } else {
            $validate->addValidateResult('i', Yii::t('messages', 'Data import completed successfully. Combined lines - {s}', ['{s}' => $rows_changed]));
        }

        Yii::app()->user->setFlash('import_status', CJSON::encode($validate->getValidateResultHtml()));
    }

}




