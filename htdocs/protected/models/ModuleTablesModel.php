<?php

class ModuleTablesModel extends ActiveRecord
{
    const TYPE_PARENT = 'parent';
    const TYPE_RELATE_SELECT = 'relate_select';
    const TYPE_RELATE_MODULE_ONE = 'relate_module_one';
    const TYPE_RELATE_MODULE_MANY = 'relate_module_many';

    public $tableName = 'module_tables';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return [
            ['table_name', 'length', 'max' => 255],
            ['id, copy_id, relate_copy_id, type, relate_type, parent_field_name, relate_field_name', 'safe'],
        ];
    }

    public function relations()
    {
        return [
            'extensionCopy'       => [self::BELONGS_TO, 'ExtensionCopyModel', 'copy_id'],
            'relateExtensionCopy' => [self::BELONGS_TO, 'ExtensionCopyModel', 'relate_copy_id'],
        ];
    }

    /**
     * getParentModuleInfo - возвращает информацию о звязаном модулей через поле название и всех его ИД полях
     *
     * @param $copy_id
     * @param $data_id
     * @return array
     */
    public static function getParentModuleInfo($copy_id, $data_id, $all_parent_data_id = false)
    {
        $result = false;
        if ($copy_id == false) {
            return $result;
        }
        if ($data_id == false && $all_parent_data_id == false) {
            return $result;
        }

        $parent_copy_id = self::getParentModuleCopyId($copy_id);
        if (!empty($parent_copy_id)) {
            $parent_data_id = self::getParentModuleDataId($parent_copy_id, $copy_id, $data_id);

            return [
                'pci' => $parent_copy_id,
                'pdi' => $parent_data_id
            ];
        }

        return false;
    }

    /**
     * getParentModuleInfo - возвращает информацию о звязаном модулей через поле Название и всех его ИД полях
     *
     * @param $copy_id
     * @param $data_id
     * @return array
     */
    public static function getParentModuleCopyId($copy_id)
    {
        $result = false;
        if ($copy_id == false) {
            return $result;
        }

        $related = self::model()->findAll([
                'condition' => 'relate_copy_id = :relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':relate_copy_id' => $copy_id
                ]
            ]
        );

        foreach ($related as $module) {
            $parent_copy_id = $module->getAttribute('copy_id');
            if ($parent_copy_id) {
                $extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id);
                $params = $extension_copy->getPrimaryField();
                if (isset($params['params']['relate_module_copy_id']) && $params['params']['relate_module_copy_id'] == $copy_id) {
                    return $parent_copy_id;
                }
            }
        }

        return false;
    }

    /**
     * getParentModuleDataId - возвращает информацию о звязаном модулей через поле название и всех его ИД полях
     *
     * @param $copy_id
     * @param $data_id
     * @return array
     */
    public static function getParentModuleDataId($parent_copy_id, $relate_copy_id, $relate_data_id, $all_id_list = false)
    {
        $result = false;
        if (empty($parent_copy_id) || empty($relate_copy_id)) {
            return $result;
        }
        if (empty($parent_data_id) && $all_id_list == false) {
            return $result;
        }

        $relate_model = self::model()->find([
                'condition' => 'copy_id =:copy_id AND relate_copy_id =:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id'        => $parent_copy_id,
                    ':relate_copy_id' => $relate_copy_id,
                ]
            ]
        );

        if (empty($relate_model)) {
            return false;
        }

        $data_model = new DataModel();
        $data_model
            ->setSelect($relate_model['parent_field_name'])
            ->setFrom('{{' . $relate_model['table_name'] . '}}')
            ->setGroup($relate_model['parent_field_name']);

        if ($all_id_list == false) {
            $data_model->setWhere($relate_model['relate_field_name'] . '=:id', [':id' => $relate_data_id]);
            $result = $data_model->findScalar();
        } else {
            $result = $data_model->findCol();
        }

        return $result;
    }

    /**
     * @param $copy_id
     * @param $data_id
     * @return array
     */
    public static function getChildrenModuleInfo($copy_id, $data_id)
    {

        if (!$copy_id || !$data_id) {
            return [];
        }

        $related = self::model()->findAll([
                'condition' => 'copy_id = :copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params'    => [
                    ':copy_id' => $copy_id
                ]
            ]
        );

        $child = [];
        foreach ($related as $module) {

            $child_copy_id = $module->getAttribute('copy_id');
            $child_r_copy_id = $module->getAttribute('relate_copy_id');

            if ($child_copy_id && $child_r_copy_id) {

                $exc = ExtensionCopyModel::model()->findByPk($child_copy_id);
                if ($exc) {

                    $params = $exc->getPrimaryField();
                    if (isset($params['params']['relate_module_copy_id']) && $params['params']['relate_module_copy_id'] == $child_r_copy_id) {

                        try {

                            $data_model = new DataModel();
                            $data_model
                                ->setSelect($module->getAttribute('relate_field_name'))
                                ->setFrom('{{' . $module->getAttribute('table_name') . '}}')
                                ->setWhere($module->getAttribute('parent_field_name') . '=:id', [':id' => $data_id]);
                            $child_data_id = $data_model->findCol();

                            if (empty($child_data_id)) {
                                continue;
                            }

                        } catch (Exception $e) {
                            continue;
                        }

                        foreach ($child_data_id as $d_id) {
                            $child[] = [
                                'pci' => $child_r_copy_id,
                                'pdi' => $d_id
                            ];
                        }

                    }
                }
            }
        }

        return $child;
    }

    /**
     * @param $copy_id
     * @param $schema
     * @param $relate mixed
     * @return bool
     */
    public static function isRelated($copy_id, $schema, $relate = 0)
    {

        if ($copy_id && $schema && isset($schema['params']) && isset($schema['params']['relate_module_copy_id'])) {

            if ($relate === 0) {
                $relate = ExtensionCopyModel::model()->findByPk($schema['params']['relate_module_copy_id']);
            }

            if ($relate) {

                $parent = $relate->getPrimaryField();
                if ($parent) {
                    if ($parent['params']['relate_module_copy_id'] == $copy_id) {
                        return true;
                    }
                }

            }
        }

        return false;
    }

    public static function getFinishedObjectIdList($extension_copy)
    {
        if (empty($extension_copy)) {
            return;
        }

        $b_status = $extension_copy->getStatusField();
        if (empty($b_status)) {
            return;
        }

        $data_model = new DataModel();
        $data_model
            ->setSelect($b_status['params']['name'] . '_id')
            ->setFrom($extension_copy->getTableName($b_status['params']['name']))
            ->andWhere($b_status['params']['name'] . '_finished_object = "1"');
        $data = $data_model->findCol();

        return $data;
    }

    private static function getCardDataParams($extension_copy, $id)
    {
        if (empty($extension_copy)) {
            return false;
        }

        $b_status = $extension_copy->getStatusField();

        $data_model = new DataModel();
        $data_model
            ->setFrom($extension_copy->getTableName())
            ->andWhere(['AND', $extension_copy->prefix_name . '_id=:id'], [':id' => $id]);

        if ($extension_copy->finished_object == true) {
            $data_model->join($extension_copy->getTableName($b_status['params']['name']), $b_status['params']['name'] . '=' . $b_status['params']['name'] . '_id', [], 'left', false);
        }

        $data = $data_model->findRow();
        $data = $data;

        return [
            'finished_object' => ($extension_copy->finished_object == false ? null : (boolean)$data[$b_status['params']['name'] . '_' . 'finished_object']),
            'this_template'   => (boolean)$data['this_template'],
        ];
    }

    /**
     * @param $extension_copy
     * @param $module
     * @return array
     */
    public static function getProjects($extension_copy, $id)
    {
        $global_params = [
            'pci'             => \Yii::app()->request->getParam('pci', null),
            'pdi'             => \Yii::app()->request->getParam('pdi', null),
            'finished_object' => false,
        ];
        //$_GET['sort'] = '{"module_title" : "a"}';

        $cdp = self::getCardDataParams($extension_copy, $id);
        $finished_object = ($cdp['finished_object'] === null ? false : true);

        $result = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($cdp['this_template'])
            ->setGlobalParams($global_params)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            ->setGetAllData(true)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();

        return $result;
    }

    /**
     * @param $extension_copy
     * @param $module
     * @return array
     */
    public static function getProcesses($extension_copy, $id)
    {
        $global_params = [
            'pci'             => \Yii::app()->request->getParam('pci', null),
            'pdi'             => \Yii::app()->request->getParam('pdi', null),
            'finished_object' => false,
        ];
        $_GET['sort'] = '{"module_title" : "a"}';

        $cdp = self::getCardDataParams($extension_copy, $id);
        $finished_object = ($cdp['finished_object'] === null ? false : true);
        if ($finished_object && $cdp['this_template']) {
            $finished_object = false;
        }

        $result = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($cdp['this_template'])
            ->setGlobalParams($global_params)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            ->setGetAllData(true)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();

        return $result;
    }

    /**
     * Взвращает модель/ли связи между модулями
     *
     * @param integer $copy_id
     * @param integer $relate_copy_id
     * @param string|array $relate_type
     * @param bool $find_one
     */
    public static function getRelateModel($copy_id, $relate_copy_id = null, $relate_type = null, $find_one = true)
    {
        $condition = [];
        $params = [];

        if (!empty($copy_id)) {
            $condition[] = 'copy_id=:copy_id';
            $params[':copy_id'] = $copy_id;
        }
        if (!empty($relate_copy_id)) {
            $condition[] = 'relate_copy_id=:relate_copy_id';
            $params[':relate_copy_id'] = $relate_copy_id;
        }

        if (!empty($relate_type)) {
            if (is_array($relate_type)) {
                foreach ($relate_type as &$item) {
                    $item = '"' . $item . '"';
                }
                $condition[] = '`type` in(' . implode(',', $relate_type) . ')';
            } else {
                $condition[] = '`type`="' . $relate_type . '"';
            }
        }

        $condition = implode(' AND ', $condition);

        if ($find_one) {
            $model = self::model()->find([
                'condition' => $condition,
                'params'    => $params
            ]);
        } else {
            $model = self::model()->findAll([
                'condition' => $condition,
                'params'    => $params
            ]);
        }

        return $model;
    }

    /**
     * возвращает наличие связи между модулями
     */
    public static function isSetRelate($copy_id, $relate_copy_id, $relate_type)
    {
        $relate_table = \ModuleTablesModel::model()->count([
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="' . $relate_type . '"',
            'params'    => [
                ':copy_id'        => $copy_id,
                ':relate_copy_id' => $relate_copy_id,
            ]
        ]);

        return (boolean)$relate_table;
    }

    /**
     * getRelateModuleTableData
     */
    public static function getRelateModuleTableBoth($copy_id, $relate_copy_id)
    {
        $relate_model = \ModuleTablesModel::model()->find([
            'condition' => '((copy_id=:copy_id AND relate_copy_id=:relate_copy_id) OR (copy_id=:relate_copy_id AND relate_copy_id=:copy_id)) AND type in ("relate_module_one", "relate_module_many")',
            'params'    => [
                ':copy_id'        => $copy_id,
                ':relate_copy_id' => $relate_copy_id,
            ],
        ]);

        return $relate_model;
    }

    /**
     * getRelateModuleTableData
     */
    public static function getRelateModuleTableData($copy_id, $relate_copy_id)
    {
        $relate_data = [];

        if (empty($relate_copy_id)) {
            return;
        }

        $relate_model = \ModuleTablesModel::model()->find([
            'condition' => '((copy_id=:copy_id AND relate_copy_id=:relate_copy_id) OR (copy_id=:relate_copy_id AND relate_copy_id=:copy_id)) AND type in ("relate_module_one", "relate_module_many")',
            'params'    => [
                ':copy_id'        => $copy_id,
                ':relate_copy_id' => $relate_copy_id,
            ],
        ]);

        // связь между модулями не найдена
        if (empty($relate_model)) {
            return;
        }

        $relate_data['table_name'] = $relate_model['table_name'];
        $relate_data['copy_id'] = $relate_model['copy_id'];
        $relate_data['relate_copy_id'] = $relate_model['relate_copy_id'];
        $relate_data['parent_field_name'] = $relate_model['parent_field_name'];
        $relate_data['relate_field_name'] = $relate_model['relate_field_name'];

        if ($relate_model['copy_id'] == $relate_copy_id) {
            $relate_data['copy_id'] = $relate_model['relate_copy_id'];
            $relate_data['relate_copy_id'] = $relate_model['copy_id'];
            $relate_data['parent_field_name'] = $relate_model['relate_field_name'];
            $relate_data['relate_field_name'] = $relate_model['parent_field_name'];
        }

        return $relate_data;
    }

}
