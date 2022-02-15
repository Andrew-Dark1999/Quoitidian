<?php

class DataValueModel
{

    private $_extension_copy;

    private $_schema_fields = [];

    private $_concat_relate_data = [];

    private $_data_processed = [];

    private $_set_id_key = false;

    private $_only_relate_id = false;

    private $_return_only_value = false;

    private $_file_link = true; //Указывает на активацию превью и линка для файлов

    private $_avatar_src = null;

    private $_file_type = self::FILE_TYPE_ALL;

    private $_file_thumbs_size = true; //вкл/откл минимизованные картинки

    private $_file_return_model = false; //возвращает мадель UploadModel

    private $_img_tag = 'span'; // span|img - тег, что будет возвращен для вывода картинки

    private static $_add_avatar = true;

    const FILE_TYPE_ALL = 1;
    const FILE_TYPE_FILE = 2;
    const FILE_TYPE_IMAGE = 3;

    public static function getInstance()
    {
        return new self();
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->_extension_copy = $extension_copy;

        return $this;
    }

    public function setSchemaFields($schema_fields)
    {
        $this->_schema_fields = $schema_fields;

        return $this;
    }

    public function setAddAvatar($add_avatar)
    {
        self::$_add_avatar = $add_avatar;

        return $this;
    }

    public function setFileType($file_type = self::FILE_TYPE_ALL)
    {
        $this->_file_type = $file_type;

        return $this;
    }

    public function setFileThumbsSize($file_thumbs_size)
    {
        $this->_file_thumbs_size = $file_thumbs_size;

        return $this;
    }

    public function setFileReturnModel($file_return_model)
    {
        $this->_file_return_model = $file_return_model;

        return $this;
    }

    public function setImgTag($img_tag)
    {
        $this->_img_tag = $img_tag;

        return $this;
    }

    /**
     * Устанавливает параметр для активации провью изображения и линков для файла
     */
    public function setFileLink($file_link)
    {
        $this->_file_link = $file_link;

        return $this;
    }

    /**
     * Возвращает только ID поля типа relate
     */
    public function setOnlyRelateId($only_relate_id)
    {
        $this->_only_relate_id = $only_relate_id;

        return $this;
    }

    public function setSetIdKey($set_id_key)
    {
        $this->_set_id_key = $set_id_key;

        return $this;
    }

    public function setAvatarSrc($avatar_src)
    {
        $this->_avatar_src = $avatar_src;

        return $this;
    }

    public function setReturnOnlyValue($return_only_value)
    {
        $this->_return_only_value = $return_only_value;

        return $this;
    }

    private function getFieldNames($field_names_only = [])
    {
        if (empty($this->_schema_fields)) {
            return [];
        }
        $fields = [];
        foreach ($this->_schema_fields as $field) {
            if (!empty($field_names_only) && !in_array($field['params']['name'], $field_names_only)) {
                continue;
            }
            $fields[] = $field['params']['name'];
        }

        return $fields;
    }

    private function getFieldParams($field_name)
    {
        foreach ($this->_schema_fields as $field) {
            if (isset($field['params']['name']) && $field['params']['name'] == $field_name) {
                return $field['params'];
            }
        }
    }

    public function getData()
    {
        return $this->_data_processed;
    }

    /**
     * доготавливает данные, проводя через метод getValue
     */
    public function prepareData($data, $field_names_only = [])
    {
        $fields = $this->getFieldNames($field_names_only);
        $this->_data_processed = [];
        $primary_field_name = $this->_extension_copy->prefix_name . '_id';

        foreach ($data as $value) {
            $data_row = [];
            foreach ($fields as $field) {
                $data_row[$field] = $this->getValue($field, $value);
            }
            if ($this->_set_id_key) {
                $this->_data_processed[$value[$primary_field_name]] = $data_row;
            } else {
                $this->_data_processed[] = $data_row;
            }

        }

        return $this;
    }



    /*

      private $_result_relate_data = array();

      private function getRelateData($data){
          if(!is_array($data)) $_result_relate_data[] = $data;
          else {
              foreach($data as $value)
                  $this->getRelateData($value);
          }
          return $this->_result_relate_data;
      }
      */

    /**
     * возвращает подготовленые данные
     */
    public function getProcessedData()
    {
        if (empty($this->_data_processed)) {
            return $this;
        }

        foreach ($this->_data_processed as $key => &$data_row) {
            $html_result = [];

            foreach ($data_row as $value) {
                $element_value_files = [];
                $element_value_params = [];

                if ($value['params']['type'] == 'relate' && $this->_only_relate_id == false) {
                    $field_name = $value['params']['name'];
                    $this->_result_relate_data = [];
                    //$this->getRelateData($value);
                    $element_value = $value;
                    if ($this->_return_only_value == false) {
                        $element_value_params[$field_name] = $value['params'];
                    }
                } else {
                    $field_name = $value['params']['name'];
                    $element_value = $value['value'];
                    if ($this->_return_only_value == false) {
                        $element_value_files[$field_name] = $value['files'];
                        $element_value_params[$field_name] = $value['params'];
                    }
                }
                if ($this->_return_only_value == false) {
                    $html_result[$field_name]['value'] = $element_value;
                    $html_result[$field_name]['files'] = $element_value_files;
                    $html_result[$field_name]['params'] = $element_value_params;
                } else {
                    $html_result[$field_name] = $element_value;
                }
            }

            $data_row = $html_result;
        }

        return $this;
    }

    /**
     * групирует данные по ключу group_index
     */
    public function concatProcessedData()
    {
        if (empty($this->_data_processed)) {
            return $this;
        }

        $data = [];
        foreach ($this->_data_processed as $data_row) {
            $html_result = [];
            $lich = 0;
            $group_index = '';
            $element_value_tmp = '';
            $element_value_files = [];
            $element_value_files_tmp = [];
            $element_value_params_tmp = [];

            $element_name_tmp = '';

            foreach ($data_row as $value) {
                $lich++;

                if ($value['params']['type'] == 'relate') {
                    $field_name = $value['params']['name'];
                    $element_value = $value;
                    $element_value_params = $value['params'];
                } else {
                    $field_name = $value['params']['name'];
                    $element_value = $value['value'];
                    $element_value_files = $value['files'];
                    $element_value_params = $value['params'];
                }

                if ($lich == count($data_row)) {
                    if (count($data_row) == 1) {
                        $html_result[$field_name]['value'] = $element_value;
                        $html_result[$field_name]['files'] = $element_value_files;
                        $html_result[$field_name]['params'] = $element_value_params;
                        //$html_result[$field_name] = array('value' => $element_value, 'files' => array($field_name => $element_value_files), 'params' =>array($field_name => $element_value_params));
                    } else {
                        if ($group_index == $value['params']['group_index']) {
                            $element_value_tmp .= (empty($element_value_tmp) ? $element_value : ' ' . $element_value);
                            $element_name_tmp .= (empty($element_name_tmp) ? $field_name : ',' . $field_name);
                            $element_value_files_tmp[$field_name] = $element_value_files;
                            $element_value_params_tmp[$field_name] = $element_value_params;
                            $html_result[$element_name_tmp] = ['value' => $element_value_tmp, 'files' => $element_value_files_tmp, 'params' => $element_value_params_tmp];

                        } else {
                            $html_result[$element_name_tmp] = ['value' => $element_value_tmp, 'files' => $element_value_files_tmp, 'params' => $element_value_params_tmp];
                            $html_result[$field_name] = ['value' => $element_value, 'files' => [$field_name => $element_value_files], 'params' => [$field_name => $element_value_params]];
                        }
                    }
                } elseif ($lich == 1 || $group_index == $value['params']['group_index']) {
                    $element_value_tmp .= (empty($element_value_tmp) ? $element_value : ' ' . $element_value);
                    $element_name_tmp .= (empty($element_name_tmp) ? $field_name : ',' . $field_name);
                    $element_value_files_tmp[$field_name] = $element_value_files;
                    $element_value_params_tmp[$field_name] = $element_value_params;
                    $group_index = $value['params']['group_index'];
                } else {
                    $html_result[$element_name_tmp] = ['value' => $element_value_tmp, 'files' => $element_value_files_tmp, 'params' => $element_value_params_tmp];
                    $element_value_tmp = $element_value;
                    $element_name_tmp = $field_name;
                    $element_value_files_tmp = [];
                    $element_value_params_tmp = [];
                    $element_value_files_tmp[$field_name] = $element_value_files;
                    $element_value_params_tmp[$field_name] = $element_value_params;
                    $group_index = $value['params']['group_index'];

                }
            }
            $data[] = $html_result;
        }
        $this->_data_processed = $data;

        return $this;
    }

    /**
     *   возвращает значение согласно формату поля
     *   поле типа "relate" собирается рекурсивно
     *
     * @param string $field_name - название поля
     * @param array $data - строка данних в формате "название_поля" => "значение"
     * @return array   -  array('value'=>'', 'files'=>array(), 'params' => array());
     */
    public function getValue($field_name, $data)
    {
        $this->_relate_data = [];

        $result = ['files' => [], 'value' => ''];
        $params = $this->getFieldParams($field_name);
        $result['params'] = $params;

        switch ($params['type']) {
            case 'numeric' :
                $result['value'] = Helper::TruncateEndZero($data[$field_name]);
                break;
            case 'string':
            case 'display':
                if ($params['is_primary'] == true) {
                    if (self::$_add_avatar) {
                        $avatar = '';
                        if ($params['avatar']) {
                            $params_ehc_image1 = $this->getFieldParams('ehc_image1');
                            if (!empty($params_ehc_image1)) {
                                $upload = UploadsModel::model()->setRelateKey($data['ehc_image1'])->find();
                            }
                            if (!empty($upload)) {
                                $avatar = $upload->setFileType('file_image')->getFullThumbsFileName(32);
                            } else {
                                $avatar = UploadsModel::getThumbStub();
                            }
                        }
                        $params['file_thumbs_size'] = 32;
                        $result['files'] = $avatar;
                    }
                    $result['value'] = $data[$field_name];
                    $result['params'] = $params;
                } else {
                    $result['value'] = $data[$field_name];
                }
                break;
            case 'file':
            case 'file_image':
                if (!empty($data[$field_name])) {
                    $criteria = new CDbCriteria;
                    $criteria->condition = 'relate_key=:relate_key';
                    $criteria->params = [':relate_key' => $data[$field_name]];
                    $criteria->limit = 1;
                    $criteria->order = 'file_date_upload desc';
                    $upload_model = UploadsModel::model()->find($criteria);

                    if (!empty($upload_model)) {
                        $upload_model->setFileType($params['type']);
                        if ($params['type'] == 'file' && ($this->_file_type == self::FILE_TYPE_ALL || $this->_file_type == self::FILE_TYPE_FILE)) {
                            if ($this->_file_return_model) {
                                $result['files'][] = $upload_model;
                            } else {
                                $result['files'][] = $upload_model->getFullFileName();
                            }
                        } elseif ($params['type'] == 'file_image' && ($this->_file_type == self::FILE_TYPE_ALL || $this->_file_type == self::FILE_TYPE_IMAGE)) {
                            if ($this->_file_return_model) {
                                $result['files'][] = $upload_model;
                            } else {
                                $result['files'][] = $upload_model->getFullThumbsFileName(($this->_file_thumbs_size == true ? $params['file_thumbs_size'] : null));
                            }
                        }
                        $result['value'] = $upload_model->getFileTitle();
                    }
                }
                break;
            case 'datetime':
                if ($params['type_view'] == Fields::TYPE_VIEW_DT_DATE) {
                    $result['value'] = Helper::formatDate($data[$field_name]);
                } else {
                    $result['value'] = Helper::formatDateTimeShort($data[$field_name]);
                }
                break;
            case 'logical':
                $logical = Fields::getInstance()->getLogicalData();
                if (isset($logical[$data[$field_name]])) {
                    $result['value'] = $logical[$data[$field_name]];
                }
                break;
            case 'select':
                $result['value'] = $data[$this->_extension_copy->prefix_name . '_' . $field_name . '_title'];
                break;
            case 'relate':
                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
                $relate_module_table = ModuleTablesModel::model()->find([
                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"',
                    'params'    => [
                        ':copy_id'        => $this->_extension_copy->copy_id,
                        ':relate_copy_id' => $params['relate_module_copy_id']
                    ]
                ]);

                $relate_data_id = DataModel::getInstance()
                    ->setFrom('{{' . $relate_module_table->table_name . '}}')
                    ->setWhere($this->_extension_copy->prefix_name . '_id = :id',
                        [':id' => $data[$this->_extension_copy->prefix_name . '_id']])
                    ->findRow();
                if (!empty($relate_data_id)) {
                    if ($this->_only_relate_id == true) {
                        $result['value'] = $relate_data_id[$relate_extension_copy->prefix_name . '_id'];
                        break;
                    }

                    $data_model = DataModel::getInstance()
                        ->setExtensionCopy($relate_extension_copy)
                        ->setFromModuleTables()
                        ->setFromFieldTypes()
                        ->setCollectingSelect()
                        ->setWhere($relate_extension_copy->getTableName() . '.' . $relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id',
                            [':' . $relate_extension_copy->prefix_name . '_id' => $relate_data_id[$relate_extension_copy->prefix_name . '_id']]);

                    $relate_data = $data_model->findRow();
                    if (!empty($relate_data)) {
                        $result = $this->getRelateValuesToArray($relate_data, $params);
                        $result['params'] = $params;
                    }
                }
                break;
            case 'relate_dinamic':
                //связанный объект, модули Задачи и Процессы
                break;
            case \Fields::MFT_CALCULATED:
                //вычисляемое поле
                $result['value'] = '';
                if (isset($data[$this->_extension_copy->prefix_name . '_' . $field_name . '_value'])) {
                    $result['value'] = Helper::TruncateEndZero($data[$this->_extension_copy->prefix_name . '_' . $field_name . '_value']);
                }
                break;
            default:
                $result['value'] = $data[$field_name];
        }

        return $result;
    }

    /**
     *   возвращает в формате array() собраное поле типа "relate"
     */
    public function getRelateValuesToArray(array $data, $field_params)
    {
        $this->_concat_relate_data = [];
        $this->concatRelateValuesToArray($data, $field_params);

        return $this->_concat_relate_data;
    }

    /**
     *   возвращает в формате html собраное поле типа "relate"
     */
    public function getRelateValuesToHtml(array $data, $field_params, $add_avatar = true)
    {
        if (empty($data)) {
            return '';
        }

        return $this->concatRelateValuesToHtml($data, $field_params, $add_avatar);
    }

    /**
     *   собирает в формат array() данные поля типа "relate"
     */
    public function concatRelateValuesToArray(array $data, $field_params)
    {
        if (empty($field_params) || empty($field_params['relate_field'])) {
            return;
        }
        if (!is_array($field_params['relate_field'])) {
            $field_params['relate_field'] = explode(',', $field_params['relate_field']);
        }

        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($field_params['relate_module_copy_id']);

        foreach ($field_params['relate_field'] as $field_name) {
            $schema_params = $relate_extension_copy->getFieldSchemaParams($field_name);
            if ($schema_params['params']['is_primary'] == true) {
                $avatar = '';
                if (self::$_add_avatar) {
                    if ($schema_params['params']['avatar']) {
                        if (!empty($data['ehc_image1'])) {
                            $avatar = UploadsModel::model()->setRelateKey($data['ehc_image1'])->find();
                        }
                        if (!empty($avatar)) {
                            $avatar = $avatar->setFileType('file_image')->getFullThumbsFileName(32);
                        } else {
                            $avatar = UploadsModel::getThumbStub();
                        }
                    }
                }

                $this->_concat_relate_data[] = [
                    'files'  => $avatar,
                    'value'  => $data[$field_name],
                    'params' => $schema_params
                ];
            } else {
                $relate_field_schema = $relate_extension_copy->getFieldSchemaParams($field_name);
                $dv_model = new DataValueModel();
                $dv_model->setExtensionCopy($relate_extension_copy);
                $dv_model->setSchemaFields([$relate_field_schema]);
                $this->_concat_relate_data[] = $dv_model->getValue($field_name, $data);
            }
        }
    }

    /**
     *   собиарает в формат html данные поля типа "relate"
     */
    public function concatRelateValuesToHtml(array $data, $field_params, $add_avatar = true)
    {
        if (empty($field_params) || empty($field_params['relate_field'])) {
            return '';
        }
        if (!is_array($field_params['relate_field'])) {
            $field_params['relate_field'] = explode(',', $field_params['relate_field']);
        }

        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($field_params['relate_module_copy_id']);
        $result = [];
        $relate_add_avatar = true;
        if (self::$_add_avatar == false) {
            $relate_add_avatar = self::$_add_avatar;
        }

        foreach ($field_params['relate_field'] as $field_name) {
            $schema_params = $relate_extension_copy->getFieldSchemaParams($field_name);
            if ($schema_params['params']['is_primary'] == true) {
                $avatar = '';

                if ($add_avatar && $relate_add_avatar) {
                    $avatar = (new AvatarModel())
                        ->setExtensionCopy($this->_extension_copy)
                        ->setDataArray($data)
                        ->setSrc($this->_avatar_src)
                        ->setTag($this->_img_tag)
                        ->getAvatar();
                }

                $result[] = $avatar . $data[$field_name];
                $relate_add_avatar = false;
            } else {
                $relate_field_schema = $relate_extension_copy->getFieldSchemaParams($field_name);
                $result[] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                    [
                        'extension_copy' => $relate_extension_copy,
                        'params'         => $relate_field_schema['params'],
                        'value_data'     => $data,
                        'file_link'      => $this->_file_link,
                    ]
                    ,
                    true);
            }
        }

        return implode(' ', $result);
    }

    /**
     * возвращает строковый массив ID на основании переданих родительских параметров: parent_copy_id, parent_data_id
     *
     * @return string
     */
    public function getIdOnTheGroundParent($copy_id, $pci, $pdi)
    {
        if (empty($pci) || empty($pdi)) {
            return;
        }

        $relate_model = ModuleTablesModel::model()->find([
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND type = "relate_module_one"',
            'params'    => [
                ':copy_id'        => $pci,
                ':relate_copy_id' => $copy_id,
            ],
        ]);
        if (!empty($relate_model)) {
            $data_model = new DataModel();
            $data_model
                ->setSelect($relate_model->relate_field_name)
                ->setFrom('{{' . $relate_model->table_name . '}}')
                ->setWhere($relate_model->parent_field_name . '=:parent_field_name', [':parent_field_name' => $pdi]);
            $data = $data_model->findAll();

            $result = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $result[] = $value[$relate_model->relate_field_name];
                }

                return implode(',', $result);
            } else {
                return false;
            }
        }
    }

    /**
     * getParentPrimaryData
     *
     * @param $copy_id
     * @param $data_id
     * @param bool $auto
     * @return array
     */
    public function getParentPrimaryData($copy_id, $data_id, $pci, $pdi)
    {
        $result = [
            'pci' => $pci,
            'pdi' => $pdi,
        ];

        if (empty($copy_id) || empty($data_id)) {
            if (!empty($result['pci']) && !empty($result['pci'])) {
                return $result;
            }

            $result['pci'] = null;
            $result['pdi'] = null;

            return $result;
        }

        $result['pci'] = null;
        $result['pdi'] = null;

        $pci = \ExtensionCopyModel::model()->findByPk($copy_id)->getParentPrimaryCopyId();
        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);

        $data_model = \DataModel::getInstance();
        $data_model
            ->addSelect($extension_copy->getPkFieldName(true))
            ->setFrom($extension_copy->getTableName())
            ->andWhere($extension_copy->getPkFieldName(true) . '=:data_id', [':data_id' => $data_id]);

        $parent_extension_copy = null;
        if (!empty($pci)) {
            $parent_extension_copy = \ExtensionCopyModel::model()->findByPk($pci);
            $relate_tables = ModuleTablesModel::getRelateModel($parent_extension_copy->copy_id, $copy_id, ModuleTablesModel::TYPE_RELATE_MODULE_ONE);
            $data_model->addSelect('(SELECT ' . $relate_tables->parent_field_name .
                ' FROM {{' . $relate_tables->table_name . '}}' .
                ' WHERE ' . $relate_tables->relate_field_name . ' = ' . $extension_copy->getPkFieldName(true) . ') AS ' . $relate_tables->parent_field_name);
        }

        $data_model->addSelect('(SELECT process_id' .
            ' FROM {{process_operations}}' .
            ' WHERE copy_id = ' . $copy_id . ' AND card_id = ' . $extension_copy->getPkFieldName(true) . ') AS process_id');

        $data = $data_model
            ->setCollectingSelect()
            ->findRow();

        if (empty($data)) {
            return $result;
        }

        if (!empty($parent_extension_copy) && !empty($data[$relate_tables->parent_field_name])) {
            $result['pci'] = $parent_extension_copy->copy_id;
            $result['pdi'] = $data[$relate_tables->parent_field_name];
        } else {
            if (!empty($data['process_id'])) {
                $result['pci'] = \ExtensionCopyModel::MODULE_PROCESS;
                $result['pdi'] = $data['process_id'];
            }
        }

        return $result;
    }

    public function dataIsParticipant($extension_copy = null)
    {
        if ($extension_copy === null) {
            $extension_copy = $this->extensionCopy;
        }
        if ($extension_copy) {
            return $extension_copy->dataIfParticipant();
        } else {
            return $this->data_if_participant;
        }
    }

    /**
     * генерирует и возвращает уникальний индекс (md5) по переданым значениям
     */
    public static function generateUniqueIndex($values_list = '')
    {
        $concat_values = $values_list;
        if (is_array($values_list)) {
            $concat_values = implode('', $values_list);
        }
        $concat_values = '"' . $concat_values . '"';

        $data_model = new DataModel();
        $data_model->setText('SELECT md5(' . $concat_values . ') as unique_index');
        $result = $data_model->findScalar();

        return $result;
    }

    public function getFinishedObjectSelectIdList()
    {
        $status_params = $this->_extension_copy->getStatusField();

        if (empty($status_params)) {
            return false;
        }

        $data = \DataModel::getInstance()
            ->setSelect($status_params['params']['name'] . '_id')
            ->setFrom($this->_extension_copy->getTableName($status_params['params']['name']))
            ->setWhere($status_params['params']['name'] . '_finished_object = "1"')
            ->findCol();

        if (empty($data)) {
            return false;
        }

        return $data;
    }

}


