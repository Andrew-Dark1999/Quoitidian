<?php

/**
 * SchemaOperation
 * Операции над схемой
 *
 * @author Alex R.
 * @version 1.0
 */

class SchemaOperation
{

    private $_schema_tmp = [];

    // роспарсеная схема полей Субмодуля     
    private $_schema_parse = [];

    private static $_schema_parse_catch = [];

    private $_block;

    private $_label;

    public static function getInstance(array $params = [])
    {
        return new self($params);
    }

    /**
     * возвращает розпарсеную схему
     */

    private function setSchemaParseCatch($copy_id, $schema_parse, $refresh = false)
    {
        if ($refresh == false && array_key_exists($copy_id, self::$_schema_parse_catch)) {
            return $this;
        }

        self::$_schema_parse_catch[$copy_id] = $schema_parse;

        return $this;
    }

    private function getSchemaParseCatch($copy_id)
    {
        if (array_key_exists($copy_id, self::$_schema_parse_catch)) {
            return self::$_schema_parse_catch[$copy_id];
        }

        return false;
    }

    public function getSchemaParse($copy_id, $schema, $exception_name_list = [], $element_keys = null, $use_cache = true)
    {
        $schema_parse = $this->getSchemaParseCatch($copy_id);

        if ($use_cache === false) {
            $schema_parse = false;
        }

        if ($schema_parse === false) {
            $this->parseSchema($schema, $exception_name_list);
            $schema_parse = $this->_schema_parse;
            $this->setSchemaParseCatch($copy_id, $schema_parse);
        }

        if ($element_keys === null) {
            return $schema_parse;
        }

        $schema = [];
        foreach ($schema_parse['elements'] as $value) {
            if (is_array($element_keys)) {
                foreach ($element_keys as $value_key) {
                    if (isset($value[$value_key])) {
                        $schema[] = $value[$value_key];
                    }
                }
            } else {
                if (isset($value[$element_keys])) {
                    $schema[] = $value[$element_keys];
                }
            }
        }

        return $schema;

    }

    /**
     * Парсит схему с расбивкой на составляющие (поля, субмодули)
     *
     * @return array(
     *           'field' => array(...),
     *           'sub_module' => array(...),
     *         )
     */
    private $_panel_params = [];

    private function parseSchema($schema, $exception_name_list = [])
    {
        if (empty($schema)) {
            return $this;
        }

        foreach ($schema as $value) {
            if (count($exception_name_list) && isset($value['params']['name'])) {
                if (in_array($value['params']['name'], $exception_name_list)) {
                    continue;
                }
            }

            // block
            if (isset($value['type']) && $value['type'] == 'block') {
                $this->_block = $value['params']['title'];
                $this->_panel_params = [
                    'list_view_visible'  => 1,
                    'process_view_group' => 0,
                    'list_view_display'  => 1,
                    'edit_view_display'  => 1,
                    'edit_view_edit'     => 1,
                    'inline_edit'        => 1,
                ];
            }

            //panel, block_button
            if (isset($value['type']) && ($value['type'] == 'panel' || $value['type'] == 'button')) {
                $this->_panel_params = [
                    'list_view_visible'  => (isset($value['params']['list_view_visible']) ? $value['params']['list_view_visible'] : 1),
                    'process_view_group' => (isset($value['params']['process_view_group']) ? $value['params']['process_view_group'] : 1),
                    'list_view_display'  => (isset($value['params']['list_view_display']) ? $value['params']['list_view_display'] : 1),
                    'edit_view_display'  => (isset($value['params']['edit_view_display']) ? $value['params']['edit_view_display'] : 1),
                    'edit_view_edit'     => (isset($value['params']['edit_view_edit']) ? $value['params']['edit_view_edit'] : 1),
                    'inline_edit'        => (isset($value['params']['inline_edit']) ? $value['params']['inline_edit'] : 1),
                ];
            }

            //block participant
            if (isset($value['type']) && ($value['type'] == 'participant')) {
                $this->_panel_params = [
                    'list_view_visible'  => (isset($value['params']['list_view_visible']) ? $value['params']['list_view_visible'] : 1),
                    'process_view_group' => (isset($value['params']['process_view_group']) ? $value['params']['process_view_group'] : 0),
                    'list_view_display'  => (isset($value['params']['list_view_display']) ? $value['params']['list_view_display'] : 1),
                    'edit_view_display'  => (isset($value['params']['edit_view_display']) ? $value['params']['edit_view_display'] : 1),
                    'edit_view_edit'     => (isset($value['params']['edit_view_edit']) ? $value['params']['edit_view_edit'] : 1),
                    'inline_edit'        => (isset($value['params']['inline_edit']) ? $value['params']['inline_edit'] : 1),
                ];
            }

            $edit_params = [];

            // Параметры для текстового поля todo_list
            if (isset($value['type']) && $value['type'] == 'edit' && isset($value['params']['name']) && $value['params']['name'] == 'todo_list') {
                $edit_params = [
                    'list_view_visible'  => 1,
                    'process_view_group' => 1,
                    'list_view_display'  => 1,
                    'edit_view_display'  => 1,
                    'edit_view_edit'     => 1,
                    'inline_edit'        => 1,
                ];
            }

            if (empty($edit_params)) {
                $edit_params = $this->_panel_params;
            }

            // label
            if (isset($value['type']) && $value['type'] == 'label') {
                $this->_label = $value['params']['title'];
            } else {
                if (isset($value['type']) && $value['type'] == 'edit') {
                    //тип "Автонумерация" заменяем на "Отображать", при этом у нас остается параметр "name_generate"
                    if (isset($value['params']['type']) && $value['params']['type'] == \Fields::MFT_AUTO_NUMBER) {
                        $value['params']['type'] = 'display';
                    }

                    if ($edit_params['process_view_group']) {
                        $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                    }

                    $this->_schema_parse['elements'][] = [
                        'field' => array_merge(
                            ['title' => (isset($value['title']) ? $value['title'] : $this->_label)],
                            $edit_params,
                            Helper::arrayMerge($value, ['params' => $edit_params])
                        )
                    ];
                } else {
                    if (isset($value['type']) && $value['type'] == 'edit_hidden') {
                        if ($edit_params['process_view_group']) {
                            $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                        }

                        $this->_schema_parse['elements'][] = [
                            'field' => array_merge(
                                ['title' => $value['params']['title']],
                                $edit_params,
                                Helper::arrayMerge($value, ['params' => $edit_params])
                            )
                        ];
                    } else {
                        if (isset($value['type']) && $value['type'] == 'button' && isset($value['params']['type_view']) && $value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
                            $edit_params['process_view_group'];
                            /*
                            if($edit_params['process_view_group']){
                                $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                            }
                            */

                            $this->_schema_parse['elements'][] = [
                                'field' => array_merge(
                                    ['title' => Yii::t('base', 'Date ending')],
                                    $edit_params,
                                    Helper::arrayMerge($value, ['params' => $edit_params])
                                )
                            ];
                            $this->_schema_parse = $this->_schema_parse;
                        } else {
                            if (isset($value['type']) && $value['type'] == 'button' && isset($value['params']['type_view']) && $value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                                if ($edit_params['process_view_group']) {
                                    $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                                }

                                $this->_schema_parse['elements'][] = [
                                    'field' => array_merge(
                                        ['title' => $value['params']['title'] ?? Yii::t('base', 'Responsible')],
                                        $edit_params,
                                        Helper::arrayMerge(['params' => $edit_params], $value)
                                    )
                                ];
                            } else {
                                if (isset($value['type']) && $value['type'] == 'button' && isset($value['params']['type_view']) && $value['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS) {
                                    if ($edit_params['process_view_group']) {
                                        $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                                    }

                                    $this->_schema_parse['elements'][] = [
                                        'field' => array_merge(
                                            ['title' => Yii::t('base', 'Status')],
                                            $edit_params,
                                            Helper::arrayMerge(['params' => $edit_params], $value)
                                        )
                                    ];
                                } else {
                                    if (isset($value['type']) && $value['type'] == 'activity') {
                                        $this->_schema_parse['elements'][] = [
                                            'field' => array_merge(
                                                ['title' => Yii::t('base', 'Activity')],
                                                $edit_params,
                                                Helper::arrayMerge($value, ['params' => $edit_params])
                                            )
                                        ];
                                    } else {
                                        if (isset($value['type']) && $value['type'] == 'participant' && isset($value['params']['type_view']) && $value['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
                                            if ($edit_params['process_view_group']) {
                                                $edit_params['process_view_group'] = (new \Fields)->getEnabledProcessViewGroup($value['params']['type']);
                                            }

                                            $this->_schema_parse['elements'][] = [
                                                'field' => array_merge(
                                                    ['title' => Yii::t('base', 'Participant')],
                                                    $edit_params,
                                                    Helper::arrayMerge($value, ['params' => $edit_params])
                                                )
                                            ];
                                        } else {
                                            if (isset($value['type']) && $value['type'] == 'attachments' && isset($value['params']['type_view']) && $value['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_ATTACHMENTS) {
                                                $edit_params['list_view_display'] = 0;

                                                $this->_schema_parse['elements'][] = [
                                                    'field' => array_merge(
                                                        ['title' => Yii::t('base', ' Attachments')],
                                                        $edit_params,
                                                        Helper::arrayMerge($value, ['params' => $edit_params])
                                                    )
                                                ];
                                            } else {
                                                if (isset($value['type']) && $value['type'] == 'sub_module') {
                                                    $this->_schema_parse['elements'][] = [
                                                        'sub_module' => [
                                                                'title' => $this->_block,
                                                            ] + $value,
                                                    ];
                                                } else {
                                                    if (isset($value['type'])) {
                                                        if (isset($value['elements'])) {
                                                            $this->parseSchema($value['elements'], $exception_name_list);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Возвращает схему искомого Сабмодуля
     *
     * @return array()
     */
    public function getSubModuleSchema($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return;
        }
        if (count($schema) == 0) {
            return;
        }

        $this->findSubModuleSchema($schema, $relate_module_copy_id);

        return $this->_schema_tmp;
    }

    /**
     * Поиск схемы Сабмодуля по указаных параметрах
     *
     * @return this
     */
    private function findSubModuleSchema($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }

        foreach ($schema as $value) {
            if (!empty($this->_schema_tmp)) {
                return;
            }
            if (isset($value['type']) &&
                $value['type'] == 'sub_module' &&
                isset($value['params']['relate_module_copy_id']) &&
                $value['params']['relate_module_copy_id'] == $relate_module_copy_id) {
                $this->_schema_tmp = $value;
                break;
            }
            if (isset($value['elements']) && is_array($value['elements'])) {
                $this->findSubModuleSchema($value['elements'], $relate_module_copy_id);
            }
        }

        return $this;
    }

    /**
     *   Возвращает схему без поля "связь с другим модулем"
     */
    public function getSchemaWithOutRelate($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return;
        }
        if (count($schema) == 0) {
            return;
        }

        return $this->deleteRelate($schema, $relate_module_copy_id);
    }

    /**
     *   Поиск и удаление поля "relate"
     */
    private function deleteRelate($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }
        $schema_tmp = [];

        foreach ($schema as $value) {
            if (isset($value['elements'])) {
                if (isset($value['type']) && $value['type'] == 'block_field_type') {
                    $block_el = $this->parseArrayBlockElements($value['elements'], $relate_module_copy_id);
                    if ($block_el['count_edit'] == 0) {
                        return false;
                    } else {
                        $value['params']['count_edit'] = $block_el['count_edit'];
                        $value['elements'] = $block_el['elements'];
                        $schema_tmp[] = $this->parseArray($value, $relate_module_copy_id);
                    }
                } else {
                    $block_el = $this->parseArray($value, $relate_module_copy_id);
                    if ($block_el !== false) {
                        $schema_tmp[] = $block_el;
                    }
                }
            } else {
                $schema_tmp[] = $value;
            }
        }

        return $schema_tmp;
    }

    /**
     *   ПОиск и проверка поля relate_module_copy_id с "copy_id"
     *   Возвращает ветку "elements" с полями
     */
    private function parseArrayBlockElements($array, $relate_module_copy_id)
    {
        $result = [];
        $count_edit = 0;
        foreach ($array as $value) {
            // если ти relate
            if (isset($value['params']['type']) && $value['params']['type'] == 'relate' && $value['params']['relate_module_copy_id'] == $relate_module_copy_id) {
                continue;
                // усли тип relate_string
            } elseif (isset($value['params']['type']) && $value['params']['type'] == 'relate_string' && $value['params']['relate_module_copy_id'] == $relate_module_copy_id) {
                $value['params']['type'] = 'display';
                $value['params']['relate_module_copy_id'] = null;
                $value['params']['relate_module_template'] = false;
                $value['params']['relate_index'] = null;
                $value['params']['relate_field'] = null;
                $value['params']['relate_type'] = null;

                $result[] = $value;
                $count_edit++;
            } else {
                $result[] = $value;
                $count_edit++;
            }
        }

        return [
            'elements'   => $result,
            'count_edit' => $count_edit,
        ];
    }

    /**
     *   Возвращает ветку "elements"
     */
    private function parseArray($array, $relate_module_copy_id)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($key != 'elements') {
                $result[$key] = $value;
            } else {
                $elements = $this->deleteRelate($value, $relate_module_copy_id);
                if ($elements === false) {
                    return false;
                } else {
                    $result['elements'] = $elements;
                }

            }
        }

        return $result;
    }

    /**
     *   Возвращает схему без поля "связь с другим модулем"
     */
    public function getSchemaWithOutSubModule($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return;
        }
        if (count($schema) == 0) {
            return;
        }

        return $this->deleteSubModule($schema, $relate_module_copy_id);
    }

    /**
     *   Поиск и удаление поля "relate"
     */
    private function deleteSubModule($schema, $relate_module_copy_id)
    {
        if (empty($schema)) {
            return false;
        }
        if (count($schema) == 0) {
            return false;
        }
        $schema_tmp = [];

        foreach ($schema as $value) {
            if (isset($value['elements'])) {
                $block_el = $this->parseArrayBlockSubModuleElements($value['elements'], $relate_module_copy_id);
                if ($block_el !== false) {
                    $value['elements'] = $block_el;
                    $schema_tmp[] = $this->parseArraySubModule($value, $relate_module_copy_id);
                }
            } else {
                $schema_tmp[] = $value;
            }
        }

        return $schema_tmp;
    }

    /**
     *   ПОиск и проверка поля relate_module_copy_id с "copy_id"
     *   Возвращает ветку "elements" с полями
     */
    private function parseArrayBlockSubModuleElements($array, $relate_module_copy_id)
    {
        $result = [];
        foreach ($array as $value) {
            if (isset($value['params']['type']) && $value['params']['type'] == 'sub_module' && $value['params']['relate_module_copy_id'] == $relate_module_copy_id) {
                return false;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     *   Возвращает ветку "elements"
     */
    private function parseArraySubModule($array, $relate_module_copy_id)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * поиск существования блока "Контакты" в основном блоке
     */
    public function beBlockPanelContactExists($schema)
    {
        return $this->findBlockPanelContactExists($schema);
    }

    private function findBlockPanelContactExists($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['block_panel_contact_exists']) && (boolean)$value['block_panel_contact_exists'] == true) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findBlockPanelContactExists($value) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск наличия блока "Контакты"
     */
    public function beBlockPanelContact($schema)
    {
        return $this->findBlockPanelContact($schema);
    }

    private function findBlockPanelContact($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && $value['type'] == 'block_panel_contact') {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findBlockPanelContact($value) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск наличия блока "Участники"
     */
    public function beBlockParticipant($schema)
    {
        return $this->findBlockParticipant($schema);
    }

    private function findBlockParticipant($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && $value['type'] == 'relate_participant' && $value['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findBlockParticipant($value) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск наличия "Ответсвенных"
     */
    public function beResponsible($schema)
    {
        return $this->findResponsible($schema);
    }

    private function findResponsible($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && $value['type'] == 'relate_participant' && $value['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findResponsible($value) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск присутствия в схеме ключового поля
     */
    public function beEditIsPrimary($schema)
    {
        return $this->findEditIsPrimary($schema);
    }

    private function findEditIsPrimary($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['is_primary']) && (boolean)$value['is_primary'] == true) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findEditIsPrimary($value) == true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск присутствия в схеме активного ключового поля
     */
    public function primaryFieldActive($schema)
    {
        return $this->findEditPrimaryFieldActive($schema);
    }

    private function findEditPrimaryFieldActive($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['is_primary']) && (boolean)$value['is_primary'] == true && $value['type'] != 'display_none') {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findEditPrimaryFieldActive($value) == true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск параметра поля   edit_view_show
     */
    public function editViewShow($schema)
    {
        return $this->findEditViewShow($schema);
    }

    private function findEditViewShow($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['edit_view_show']) && (boolean)$value['edit_view_show'] == true) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findEditViewShow($value) == true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск наличия отметки "групировка поля в ProcessView"
     */
    public function beProcessViewGroupParam($schema)
    {
        return $this->findProcessViewGroupParam($schema);
    }

    private function findProcessViewGroupParam($schema)
    {
        foreach ($schema as $value) {
            if (isset($value['process_view_group']) && (boolean)$value['process_view_group'] == true) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findProcessViewGroupParam($value) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * поиск подключенного модуля
     */
    public function isModuleHookUp($schema, $module_copy_id)
    {
        return $this->findModuleHookUp($schema, $module_copy_id);
    }

    private function findModuleHookUp($schema, $module_copy_id)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && ($value['type'] == 'relate' || $value['type'] == 'relate_string') && isset($value['relate_module_copy_id']) && $value['relate_module_copy_id'] == $module_copy_id) {
                return true;
            } else {
                if (is_array($value)) {
                    if ($this->findModuleHookUp($value, $module_copy_id) === true) {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * возвращает параметры всех елементов симейства типов "relate.."
     */
    private $_elements_relate_params = [];

    public function getElementsRelateParams($schema, $only_primary_field = false, $only_copy_id = null)
    {
        $this->findElementsRelateParams($schema, $only_primary_field, $only_copy_id);

        return $this->_elements_relate_params;
    }

    private function findElementsRelateParams($schema, $only_primary_field = false, $only_copy_id = null)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && (
                    $value['type'] == 'relate' ||
                    $value['type'] == 'relate_string' ||
                    $value['type'] == 'relate_this' ||
                    $value['type'] == 'sub_module')) {
                if ($value['type'] == 'sub_module') {
                    if ($only_primary_field == false || ($only_primary_field == true && (boolean)$value['params']['is_primary'] == true)) {
                        $this->_elements_relate_params[] = $value['params'];
                    }
                } elseif ($only_primary_field == false || ($only_primary_field == true && (boolean)$value['is_primary'] == true)) {
                    if ($only_copy_id !== null && $value['relate_module_copy_id'] == $only_copy_id) {
                        $this->_elements_relate_params = $value;

                        return true;
                    }
                    $this->_elements_relate_params[] = $value;
                }
            } elseif (is_array($value)) {
                $r = $this->findElementsRelateParams($value, $only_primary_field, $only_copy_id);
                if ($only_copy_id && $r) {
                    return true;
                }
            }
        }
    }

    /**
     * возвращает параметры всех елементов типа
     * $type - тип поля. Может быть как строкой, таки массивом
     */
    public function getAllElementsWhereType($schema, $type)
    {
        $this->findAllElementsRelate($schema, $type);

        return $this->_elements_relate_params;
    }

    private function findAllElementsRelate($schema, $type)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && ((is_array($type) && in_array($value['type'], $type)) || $value['type'] == $type)) {
                $this->_elements_relate_params[] = $value;
            } elseif (is_array($value)) {
                $this->findAllElementsRelate($value, $type);
            }
        }
    }

    /**
     * поиск полей связаного типа и контроль на доступ (активный модуль, доступ на отображение самого модуля)
     * возвращаем
     * array(
     *  'be_fields' => boolean - указывает на существование других полей
     *  'copy_id_exteptions' => array()  - список ИД полей, которым запрещено отобращение
     * )
     */

    private static $denied_relate = [];

    public static function getDeniedRelateCopyId($schema, $field_types = ['relate', 'relate_this'])
    {
        self::$denied_relate = $denied_relate = [
            'be_fields'          => false,
            'copy_id_exteptions' => [],
        ];
        self::findDeniedRelateCopyId($schema, $field_types);

        return self::$denied_relate;
    }

    private static function findDeniedRelateCopyId($schema, $field_types)
    {
        foreach ($schema as $value) {
            if (isset($value['type']) && in_array($value['type'], $field_types) && isset($value['relate_module_copy_id'])) {
                if ((boolean)ExtensionCopyModel::model()->findByPk($value['relate_module_copy_id'])->active == false) {
                    self::$denied_relate['copy_id_exteptions'][] = $value['relate_module_copy_id'];
                } else {
                    $administrative_access = Access::moduleAdministrativeAccess($value['relate_module_copy_id']);
                    if (!$administrative_access) {
                        $access_result = Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $value['relate_module_copy_id'], Access::ACCESS_TYPE_MODULE);
                    } else {
                        $access_result = Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
                    }

                    if ($access_result == false) {
                        self::$denied_relate['copy_id_exteptions'][] = $value['relate_module_copy_id'];
                    } else {
                        self::$denied_relate['be_fields'] = true;
                    }
                }
            } elseif (isset($value['type']) && isset($value['type_view']) && !isset($value['elements'])) {
                self::$denied_relate['be_fields'] = true;
            } elseif (is_array($value)) {
                self::findDeniedRelateCopyId($value, $field_types);
            }
        }
    }

    /**
     * позвращает список полей без полей связаного типа, для которых закрытый доступ
     *
     * @param array $fields = array('название поля' => 'значение')
     */
    public static function getWithOutDeniedRelateCopyId($fields, $extension_copy)
    {
        $fields_result = [];
        if (empty($fields)) {
            return $fields_result;
        }

        foreach ($fields as $field_name => $value) {
            $field_params = $extension_copy->getFieldSchemaParams($field_name);
            $denied_relate = SchemaOperation::getDeniedRelateCopyId([$field_params]);
            if ($denied_relate['be_fields'] == true) {
                $fields_result[$field_name] = $value;
            }
        }

        return $fields_result;
    }

    /**
     * Проверяет наличие кнопки в блоке
     */
    public function isSetButton($type_view, $schema_elements)
    {
        if (empty($schema_elements)) {
            return false;
        }
        foreach ($schema_elements as $element) {
            if (isset($element['params']['type_view']) && $element['params']['type_view'] == $type_view) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Возвращает список субмодулей
     */
    public static function getSubModules($schema_parsed)
    {
        $result = [];
        if (!isset($schema_parsed['elements'])) {
            return $result;
        }
        foreach ($schema_parsed['elements'] as $value) {
            if (isset($value['sub_module'])) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     *  Возвращает список полей СДМ
     */
    public static function getRelates($schema_parsed, array $exception_copy_id_list = [])
    {
        $result = [];
        if (!isset($schema_parsed['elements'])) {
            return $result;
        }
        foreach ($schema_parsed['elements'] as $value) {
            if (isset($value['field']['params']['type']) && $value['field']['params']['type'] == 'relate' &&
                (empty($exception_copy_id_list) || in_array($value['field']['params']['relate_module_copy_id'], $exception_copy_id_list) == false)
            ) {
                $result[] = $value['field'];
            }
        }

        return $result;
    }

    /**
     * Возвращает схему без опеределенных элементов
     */
    public static function getSchemaParsedWithOutElements($schema_parsed, $exception_params_list)
    {
        $schema_tmp = [];

        if (empty($schema_parsed['elements']) || empty($exception_params_list)) {
            return $schema_parsed;
        }

        foreach ($schema_parsed['elements'] as $schema) {
            $check = 0;
            foreach ($exception_params_list as $key => $value) {
                if (!empty($schema['field']['params']) && array_key_exists($key, $schema['field']['params']) && $schema['field']['params'][$key] == $value) {
                    $check++;
                }
            }

            if ($check == count($exception_params_list)) {
                continue;
            }

            $schema_tmp[] = $schema;
        }

        return ['elements' => $schema_tmp];
    }

    /**
     * Ищет в схеме $schema ветку по параметру $find и заменяет на $replace_value. Если  $replace_value массив - в случае отсутсвия будет добавлена новый параметр, или обновлен старый (не проверено :) )
     *
     * @param $schema
     * @param $find
     * @param $replace_value
     * @return bool
     */
    public static function schemaFindAndReplace(&$schema, array $find, $replace_value)
    {
        foreach ($schema as $key => &$value) {
            if (is_array($value)) {
                $result = self::schemaFindAndReplace($value, $find, $replace_value);
                if ($result == true) {
                    return true;
                }
            } else {
                $check = 0;
                foreach ($find as $f_key => $f_value) {
                    if ($key == $f_key && $value == $f_value) {
                        $check++;
                    }
                }

                if ($check == count($find)) {
                    if (!is_array($replace_value)) {
                        $value = $replace_value;
                    } else {
                        $k = array_keys($replace_value);
                        $schema[$k[0]] = $replace_value[$k[0]];
                    }

                    return true;
                }
            }

        }

        return false;
    }

    /**
     * inProcessViewCheckedGroup - проверяем поле на доступность сортировки (для ProcessView)
     * Дополнительно идет проверка на изменения списка полей и пренадлежность их к одной группе
     */
    public static function inProcessViewCheckedGroup($field_name_list, $schema_parse)
    {
        if ($field_name_list == false) {
            return false;
        }

        if (is_string($field_name_list)) {
            $field_name_list = explode(',', $field_name_list);
        }

        $first_group_index = null;
        $group_index_counts = [];

        $count_fields = 0;
        foreach ($schema_parse['elements'] as $value) {
            if (isset($value['field']) && in_array($value['field']['params']['name'], $field_name_list) && (boolean)$value['field']['params']['process_view_group'] == true) {
                if ($first_group_index === null) {
                    $first_group_index = $value['field']['params']['group_index'];
                }

                if ($first_group_index !== null && $first_group_index != $value['field']['params']['group_index']) {
                    return false;
                }

                $count_fields++;
            }

            if (!empty($value['field']['params']['group_index'])) {
                if (empty($group_index_counts[$value['field']['params']['group_index']])) {
                    $group_index_counts[$value['field']['params']['group_index']] = 1;
                } else {
                    $group_index_counts[$value['field']['params']['group_index']]++;
                }
            }

        }

        $count_fnl = count($field_name_list);

        $b = ($count_fnl === $count_fields);

        if ($b && $first_group_index) {
            $b = ($count_fnl === $group_index_counts[$first_group_index]);
        }

        return $b;
    }

    ////достает блок в котором есть элемент с заданным ключом и значением
    public static function getBlockWithKey($schema, $key, $value)
    {
        if ((!empty($schema[$key])) && ($schema[$key] == $value)) {
            return $schema;
        } else {
            if (empty($schema)) {
                return;
            }

            foreach ($schema as $block) {
                if (is_array($block)) {
                    $t_block = self::getBlockWithKey($block, $key, $value);
                    if (!empty($t_block)) {
                        return $t_block;
                    }
                }
            }
        }

        return;
    }

    //Добавляет в схему модуля поле с заданным именем и типом(если такого там нет)
    public static function addFieldToSchema($copy_id, $field_name, $field_type, $field_schema)
    {
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $schema = $extension_copy->getSchema();

        if (!SchemaOperation::getBlockWithKey($schema, 'type', $field_type)) {
            $field_params = Fields::getInstance()->getDefaultSchemaParams($field_type);
            $field_params['name'] = $field_name;
            $new_schema_panel_element = $field_schema;
            $new_schema_panel_element = json_decode($new_schema_panel_element, true);
            $new_schema_panel_element['elements'][1]['elements'][0]['params'] = $field_params;

            foreach ($schema as &$block) {
                foreach ($block['elements'] as &$block_element) {
                    if ($block_element['type'] == 'block_panel') {
                        $new_field_index = isset($block_element['elements']) ? count($block_element['elements']) : 0;
                        $block_element['elements'][$new_field_index] = $new_schema_panel_element;
                        break 2;
                    }
                }
            }
            $extension_copy->schema = json_encode($schema);
            $extension_copy->save();
        }
    }

}
    
    
