<?php

class ListViewFilterController extends \ListViewFilter
{

    public $modules = [];

    public $selected_copy_id = null;

    private $_use_full_field_name_real = false; // в качестве названия поля (тип relate) использовать название таблицы модуля

    public function init()
    {
        // modules
        $reports_id = \Yii::app()->request->getParam('id');
        if (empty($reports_id)) {
            parent::init();

            return;
        }

        $schema = \Reports\models\ReportsModel::getSavedSchema($reports_id);

        if (empty($schema)) {
            parent::init();

            return;
        }

        $schema_model = new \Reports\extensions\ElementMaster\Schema();
        $schema_model
            ->setSchema($schema)
            ->setFromUsersStorage($reports_id)
            ->buildSchema();
        $this->modules = $schema_model->getModulesFilter($schema_model->getResultSchema());

        // selected_copy_id
        $selected_copy_id = \Yii::app()->request->getParam('selected_copy_id');
        if ($selected_copy_id) {
            $this->selected_copy_id = $selected_copy_id;
        } elseif (!$selected_copy_id && !empty($this->modules)) {
            $this->selected_copy_id = $this->modules[0]['module_copy_id'];
        }

        parent::init();
    }

    public function menuList($extension_copy, $filters_instaled = null, $view = 'list-menu')
    {
        $not_filters = '';
        $filters = [];
        if (func_num_args() < 3) {
            return $filters;
        }

        if (is_array($filters_instaled) && count($filters_instaled) > 0) {
            foreach ($filters_instaled as $filter) {
                $filters[] = '"' . $filter . '"';
            }
            if (!empty($filters)) {
                $not_filters = ' AND filter_id not in (' . implode(',', $filters) . ')';
            }
        }

        $not_filters .= ' AND (user_create = ' . WebUser::getUserId() . ' OR `view` = "' . FilterModel::VIEW_GENERAL . '")';

        $list = \Reports\models\ReportsFilterModel::model()->findAll(
            'copy_id=:copy_id AND reports_id=:reports_id' . $not_filters,
            [':copy_id' => $extension_copy->copy_id, ':reports_id' => func_get_arg(2)]);

        return $this->renderPartial(\ViewList::getView('filter/list-menu'),
            ['lists' => $list],
            true
        );
    }

    /**
     *   Сохранение фильтра
     */
    public function actionSave()
    {
        $validate = new Validate();

        if (!isset($_POST['data'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $reports_id = $_POST['data']['reports_id'];
        if (isset($_POST['data']['id']) && !empty($_POST['data']['id'])) {
            $model = \Reports\models\ReportsFilterModel::model()->findByPk($_POST['data']['id']);
            $filter_id_old = $model->filter_id;
        } else {
            if (\Reports\models\ReportsFilterModel::model()->exists(
                'copy_id = :copy_id AND reports_id = :reports_id  AND title = :title',
                [":copy_id" => $_POST['data']['copy_id'], ":reports_id" => $reports_id, ":title" => $_POST['data']['title']])) {
                $validate->addValidateResult('w', Yii::t('messages', 'Filter with the same name has already exists'));

                return $this->renderJson([
                    'status'   => false,
                    'messages' => $validate->getValidateResultHtml(),
                ]);
            }
            $model = new \Reports\models\ReportsFilterModel();
        }
        if (isset($_POST['data']['id'])) {
            unset($_POST['data']['id']);
        }
        $model->setAttributes($_POST['data']);

        // save
        if ($model->save()) {
            return $this->renderJson([
                'status'        => true,
                'filter_id'     => $model->getPrimaryKey(),
                'copy_id'       => $model->copy_id,
                'name'          => $model->name,
                'filter_id_old' => (!empty($filter_id_old) ? $filter_id_old : $model->filter_id),
                'menu_list'     => $this->menuList(ExtensionCopyModel::model()->findByPk($model->copy_id), null, $reports_id),
            ]);
        } else {
            return $this->renderJson([
                'status'   => false,
                'messages' => $model->getErrorsHtml(),
            ]);
        }
    }

    /**
     *   Удаление фильтра
     */
    public function actionDelete()
    {
        $validate = new Validate();

        if (!isset($_POST['id'])) {
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $delete = \Reports\models\ReportsFilterModel::model()->findByPk($_POST['id'])->delete();

        return $this->renderJson([
            'status' => $delete,
        ]);
    }

    /**
     *   Загрузка фильтра
     */
    public function actionLoad($copy_id, $filter_id)
    {
        $status = false;
        $result = '';
        $content = '';

        $filter_model = \Reports\models\ReportsFilterModel::model()->findByPk($filter_id);
        if (empty($filter_model)) {
            return $this->renderJson(
                [
                    'status' => false,
                ]
            );
        }

        $filter_model->setAccessToChange();

        $filter_params = $filter_model ? $filter_model->getParams() : null;

        if (!empty($filter_params)) {
            foreach ($filter_params as $value) {
                $this->selected_copy_id = $value['copy_id'];
                $content .= $this->actionAddPanel($this->selected_copy_id,
                    $filter_id,
                    $value['name'],
                    $value['condition'],
                    (isset($value['condition_value']) ? $value['condition_value'] : ""),
                    false);
            }

            $status = true;
            $result = $this->renderPartial(ViewList::getView('filter/block'), [
                'filter_id'         => null,
                'content'           => $content,
                'btn_filter_delete' => true,
                'filter_model'      => $filter_model,
            ],
                true);
        }

        return $this->renderJson([
                'status' => $status,
                'data'   => $result
            ]
        );
    }

    public function setUseFullFieldNameReal($use_full_field_name_real)
    {
        $this->_use_full_field_name_real = $use_full_field_name_real;

        return $this;
    }

    /**
     *  Возвращает параметры условия для SQL запроса
     */
    public function getParamsToQuery($extension_copy, $filter_id_list, $filter_params = null, $add_table_name = true, $filters_max = 2)
    {

        $controller = \Yii::app()->controller->id;
        $action = \Yii::app()->controller->action->id;
        if ($controller == 'listView' && ($action == 'show')) {//} || $action == 'export')){
            return parent::getParamsToQuery($extension_copy, $filter_id_list, $filter_params, $add_table_name, $filters_max);
        }

        if ($filters_max) {
            $filter_id_list = array_slice($filter_id_list, 0, $filters_max);
        }

        \Yii::import('ext.ExcelExport');
        $reports_id = \Yii::app()->request->getParam('id');//func_get_arg(5);

        $criteria = new CDbCriteria();
        $criteria->addCondition("reports_id=:reports_id");
        $criteria->params = [':reports_id' => $reports_id];
        $criteria->addInCondition("filter_id", $filter_id_list);

        $filters = \Reports\models\ReportsFilterModel::model()->findAll($criteria);

        $conditions = [];
        $params = [];
        $filter_params = [
            'there_is_participant' => false,
        ];

        foreach ($filters as $filter) {
            $data = $filter
                ->setUseFullFieldNameReal($this->_use_full_field_name_real)
                ->setAddTableName($add_table_name)
                ->deleteUnnecessaryCopyId($extension_copy->copy_id)
                ->setCopyId($extension_copy->copy_id)
                ->prepareQuery()
                ->getQuery(false);
            if ($filter->getThereIsParticipant()) {
                $filter_params['there_is_participant'] = true;
            }
            if (!empty($data['conditions'])) {
                $conditions = array_merge($conditions, $data['conditions']);
                $params = array_merge($params, $data['params']);
            }
        }

        if (!empty($conditions)) {
            return [
                'conditions'    => $conditions,
                'params'        => $params,
                'filter_params' => $filter_params,
            ];
        }
    }

    /**
     *  Возвращает параметры условия для SQL запроса
     */
    public function getParamsToQueryFromVirtualParams($extension_copy, $filter_params, $add_table_name = true, $filters_max = 2)
    {
        $filter = \Reports\models\ReportsFilterModel::model();
        $filter->setFilterParams($filter_params);

        $conditions = [];
        $params = [];

        $data = $filter
            ->setUseFullFieldNameReal($this->_use_full_field_name_real)
            ->setAddTableName($add_table_name)
            ->deleteUnnecessaryCopyId($extension_copy->copy_id)
            ->setCopyId($extension_copy->copy_id)
            ->prepareQuery()
            ->getQuery(false);

        if (!empty($data['conditions'])) {
            $conditions = array_merge($conditions, $data['conditions']);
            $params = array_merge($params, $data['params']);
        }

        if (!empty($conditions)) {
            return [
                'conditions' => $conditions,
                'params'     => $params,
            ];
        }

    }

    /**
     *   Список примененных фильтров для определенного модуля
     */
    public function filtersInstalled($extension_copy, $filter_id_list)
    {
        $filter_id_list = array_slice($filter_id_list, 0, 2);

        return $this->renderPartial(ViewList::getView('filter/installed'),
            [
                'extension_copy' => $extension_copy,
                'filter_id_list' => $filter_id_list,
                'reports_id'     => func_get_arg(2),
            ],
            true
        );

    }

    /**
     *   Добавляем новый блок
     */
    public function actionAddBlock($copy_id, $renderJSON = true)
    {
        if (empty($this->selected_copy_id)) {
            $validate = new Validate();
            $validate->addValidateResult('e', Yii::t('messages', 'You can not create a filter - report does not contain modules'));

            return $this->renderJson([
                'status'   => false,
                'messages' => $validate->getValidateResultHtml(),
            ]);
        }

        $copy_id = $this->selected_copy_id;
        $content = $this->actionAddPanel($copy_id, null, null, null, '', false);

        $result = $this->renderPartial(ViewList::getView('filter/block'), [
            'filter_id'    => null,
            'content'      => $content,
            'filter_model' => new FilterModel(),
        ],
            true);

        if ($renderJSON === true) {
            return $this->renderJson([
                'status' => true,
                'data'   => $result,
            ]);
        } else {
            return $result;
        }
    }

    public function actionAddPanel($copy_id, $filter_id = null, $field_value = null, $condition_value = null, $condition_value_value = '', $renderJSON = true, $this_template = EditViewModel::THIS_TEMPLATE_MODULE)
    {
        $copy_id = $this->selected_copy_id;
        \ViewList::setViews(['filter/panel' => '/filter/panel']);

        return parent::actionAddPanel($copy_id, $filter_id, $field_value, $condition_value, $condition_value_value, $renderJSON, $this_template);
    }

    public function actionAddCondition($copy_id, $field_name, $condition_value = null, $renderJSON = true)
    {
        $copy_id = $this->selected_copy_id;

        return parent::actionAddCondition($copy_id, $field_name, $condition_value, $renderJSON);
    }

    public function actionAddConditionValue($copy_id, $field_name, $condition_value = null, $condition_value_value = '', $this_template, $renderJSON = true)
    {
        $copy_id = $this->selected_copy_id;

        return parent::actionAddConditionValue($copy_id, $field_name, $condition_value, $condition_value_value, $this_template, $renderJSON);
    }

}
