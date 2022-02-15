<?php

/**
 * SchemaConcatFields - Разбирает распарсенную схему полей и возвраащет результат в виде подготовленных (сгрупированых) полей
 *
 * @author Alex R.
 */

class SchemaConcatFields
{
    //розпарсенная схема
    private $_schema = [];

    //список полей, которые будут пропущены в процессе подготовки данных 
    private $_without_fields = [];

    // массив подготовленных данных полей
    private $_fields = [];

    // массив подготовленных схемы полей. Ключ - название поля (name)
    private $_params = [];

    // последний индекс при парсинге 
    private $_last_index = 0;

    // последний груповой индекс при парсинге
    private $_last_group_index = null;

    // окончательний массив для отдачи
    private $_result_array = [];

    // указывает, что поле Название должжно быть на первом месте 
    private $_primary_on_first_place = false;

    public static function getInstance()
    {
        return new self();
    }

    /**
     * розпарсенная схема
     */
    public function setSchema(array $schema)
    {
        $this->_schema = $schema;

        return $this;
    }

    /*
     *
     */
    public function getPrepareFields()
    {
        return $this->_fields;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных
     */
    public function setWithoutFields($without_fields = [], $append = false)
    {
        if (empty($without_fields)) {
            return $this;
        }

        if ($append) {
            $this->_without_fields = array_merge($this->_without_fields, $without_fields);
        } else {
            $this->_without_fields = $without_fields;
        }

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных для групировки в SubModules
     */
    public function setWithoutFieldsForSubModuleGroup($this_template)
    {
        $without_fields = [
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_AVATAR],
            'attachments',
            'activity',
        ];
        if ($this_template) {
            $without_fields[] = 'relate_dinamic';
        } else {
            $without_fields[] = 'module';
        }

        $this->_without_fields = $without_fields;

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных для групировки в ListView
     */
    public function setWithoutFieldsForListViewGroup()
    {
        $without_fields = [
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_AVATAR],
            'attachments',
            'activity',
        ];
        //if(!empty($module_name) && $module_name != 'Participant') $without_fields[] = array('type'=>'relate_participant', 'type_view'=>Fields::TYPE_VIEW_BLOCK_PARTICIPANT);
        $this->_without_fields = $without_fields;

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных для Отчета
     */
    public function setWithoutFieldsForReports()
    {
        $this->setWithoutFieldsForListViewGroup();

        $this->_without_fields[] = 'display_none';

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных для групировки в Фильтре
     */

    public function setWithoutFieldsForFilterGroup()
    {
        $without_fields = [
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_AVATAR],
            ['type' => 'string', 'input_attr' => ['type' => 'password']],
            'attachments',
            'activity',
        ];
        $this->_without_fields = $without_fields;

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе подготовки данных для групировки в ProcessView
     */
    public function setWithoutFieldsForProcessViewGroup()
    {
        $this->_without_fields = [
            'file',
            'file_image',
            'relate_dinamic',
            'module',
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_AVATAR],
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_EDIT_HIDDEN],
            ['type' => Fields::MFT_DATETIME_ACTIVITY],
            //array('type'=>'datetime', 'type_view'=>Fields::TYPE_VIEW_BUTTON_DATE_ENDING),
            //array('type'=>'relate_participant', 'type_view'=>Fields::TYPE_VIEW_BLOCK_PARTICIPANT),
            ['type' => 'string', 'input_attr' => ['type' => 'password']],
            'attachments',
            'activity',

        ];

        return $this;
    }

    /**
     * список полей, которые будут пропущены в процессе отображения второго поля в ProcessView
     */
    public function setWithoutFieldsForProcessViewSecond()
    {
        $this->_without_fields = [
            ['is_primary' => true],
            //array('type'=>'datetime', 'type_view'=>Fields::TYPE_VIEW_BUTTON_DATE_ENDING),
            //array('type'=>Fields::MFT_DATETIME_ACTIVITY),
            ['type' => 'string', 'type_view' => Fields::TYPE_VIEW_AVATAR],
            ['type' => 'relate_participant', 'type_view' => Fields::TYPE_VIEW_BLOCK_PARTICIPANT],
            ['type' => 'string', 'input_attr' => ['type' => 'password']],
            'attachments',
            'activity',
        ];

        return $this;
    }

    /**
     * поиск и сравнивание параметров поля с массивом полей для исключения
     *
     * @return boolean - true если присутствует
     */
    private function findExcludeField($field_param)
    {
        $result = false;

        if (empty($this->_without_fields)) {
            return $result;
        }
        foreach ($this->_without_fields as $fields) {
            if (is_array($fields)) {
                // сравниваем по type, type_view, is_primary
                $lich = 0;
                foreach ($fields as $key => $value) {
                    if ($key == 'input_attr') { // параметр input_attr (сохраняется в json)
                        $input_attr = json_decode($field_param['params'][$key], true);
                        $lich_attr = 0;
                        foreach ($value as $attr_key => $attr_value) {
                            if (is_bool($attr_value)) {
                                if ((boolean)$attr_value == (boolean)$input_attr[$attr_key]) {
                                    $lich_attr++;
                                }
                            } else {
                                if ($input_attr && $attr_value == $input_attr[$attr_key]) {
                                    $lich_attr++;
                                }
                            }
                        }
                        if ($lich_attr == count($value)) {
                            $lich++;
                        }
                    } else { //все остальные параметры
                        if (is_bool($value)) {
                            if ((boolean)$field_param['params'][$key] == (boolean)$value) {
                                $lich++;
                            }
                        } else {
                            if ($field_param['params'][$key] == $value) {
                                $lich++;
                            }
                        }
                    }
                }
                if ($lich == count($fields)) {
                    $result = true;
                }
            } else {
                // сравниваем по type
                if ((string)$field_param['params']['type'] == (string)$fields) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * непосредственно добавляет данные в аргументы-массивы
     */
    private function setSafe($field_param)
    {
        $this->_fields[$this->_last_index]['title'] = $field_param['title'];
        $this->_fields[$this->_last_index]['name'][] = $field_param['params']['name'];
        $this->_fields[$this->_last_index]['group_index'] = $field_param['params']['group_index'];
        $this->_params[$field_param['params']['name']] = $field_param['params'];
    }

    /**
     * проверяет и добавляет данные в аргументы-массивы
     */
    private function checkAndAppendToSafe($field_param)
    {
        if ($this->findExcludeField($field_param)) {
            return;
        }

        if ($this->_last_group_index == null || $this->_last_group_index == $field_param['params']['group_index']) {
            $this->setSafe($field_param);
            $this->_last_group_index = $field_param['params']['group_index'];
        } elseif ($this->_last_group_index != $field_param['params']['group_index']) {
            $this->_last_index++;
            $this->setSafe($field_param);
            $this->_last_group_index = $field_param['params']['group_index'];
        }
    }

    /**
     * парсим данные
     */
    public function parsing()
    {
        if (empty($this->_schema)) {
            return $this;
        }

        foreach ($this->_schema as $schema_value) {
            if (!isset($schema_value['field'])) {
                continue;
            } else {
                $field_param = $schema_value['field'];
            }

            $this->checkAndAppendToSafe($field_param);
        }

        return $this;
    }

    /**
     * Перемещает поле Название на первое место
     */
    public function primaryOnFirstPlace($first_id = false)
    {
        $result_fields = [];

        if (empty($this->_fields)) {
            return $this;
        }

        foreach ($this->_fields as $value) {
            if ($this->_params[$value['name'][0]]['is_primary'] == true) { //проверяем только первое поле из группы
                array_unshift($result_fields, $value);
            } else {
                array_push($result_fields, $value);
            }
        }

        $this->_fields = $result_fields;
        if ($first_id) {
            $result_fields = [];
            foreach ($this->_fields as $value) {
                if (isset($this->_params[$value['name'][0]]['is_id_field']) && $this->_params[$value['name'][0]]['is_id_field'] == true) {
                    array_unshift($result_fields, $value);
                } else {
                    array_push($result_fields, $value);
                }
            }
            $this->_fields = $result_fields;
        }

        return $this;
    }

    /**
     * Удаляем елементы пипа Связь с другим модулем, если сам модуль деактивирован, или недоступен
     */
    public function prepareWithOutDeniedRelateCopyId()
    {
        $result_fields = [];

        if (empty($this->_fields)) {
            return $this;
        }

        foreach ($this->_fields as $value) {
            $fields = [];
            foreach ($value['name'] as $field) {
                $denied_relate = \SchemaOperation::getDeniedRelateCopyId([$this->_params[$field]]);
                if ($denied_relate['be_fields'] == true) {
                    $fields[] = $field;
                }
            }
            if (!empty($fields)) {
                $value['name'] = $fields;
            } else {
                continue;
            }
            array_push($result_fields, $value);
        }
        $this->_fields = $result_fields;

        return $this;
    }

    /**
     * подготавливает данные с конкатирацией поля Name
     */
    public function prepareWithConcatName($concat_decimal = ',')
    {
        $result = [
            'header' => [],
            'params' => [],
        ];

        $result_fields = [];
        if (empty($this->_fields)) {
            return $result;
        }
        foreach ($this->_fields as $field) {
            $result_fields[] = [
                'title'       => $field['title'],
                'group_index' => $field['group_index'],
                'name'        => implode($concat_decimal, $field['name'])
            ];
        }

        $result['params'] = $this->_params;
        $result['header'] = $result_fields;

        $this->_result_array = $result;

        return $this;
    }

    /**
     * подготавливает данные без составних полей
     */
    public function prepareWithoutCompositeFields()
    {
        $result = [
            'header' => [],
            'params' => [],
        ];

        $result_fields = [];
        if (empty($this->_fields)) {
            return $result;
        }
        foreach ($this->_fields as $field) {
            if (count($field['name']) > 1) {
                continue;
            }
            $result_fields[] = [
                'title'       => $field['title'],
                'group_index' => $field['group_index'],
                'name'        => $field['name'][0]
            ];
        }

        $result['params'] = $this->_params;
        $result['header'] = $result_fields;

        $this->_result_array = $result;

        return $this;
    }

    /**
     * возвращает окончательный результат парсинга
     */
    public function getResult()
    {
        return $this->_result_array;
    }

}
