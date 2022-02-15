<?php
/**
 * ListViewFilter
 *
 * @author Alex R.
 * @version 1.0
 */

use application\components\filter\FilterPanel;

class ListViewFilter extends Controller
{

    /**
     * @param $extension_copy ExtensionCopyModel
     * @param null $filters_instaled
     * @return string
     * @throws CException
     * Список установленых фильтров для определенного модуля
     */
    public function menuList($extension_copy, $filters_instaled = null, $view = 'list-menu')
    {
        $not_filters = '';
        $filters = [];

        if (is_array($filters_instaled) && count($filters_instaled) > 0) {
            foreach ($filters_instaled as $filter) {
                $filters[] = '"' . $filter . '"';
            }
            if (!empty($filters)) {
                $not_filters = ' AND filter_id not in (' . implode(',', $filters) . ') ';
            }
        }

        $not_filters .= ' AND (user_create = ' . WebUser::getUserId() . ' OR `view` = "' . FilterModel::VIEW_GENERAL . '")';

        $list = FilterModel::model()->findAll('copy_id=:copy_id' . $not_filters, [':copy_id' => $extension_copy->copy_id]);

        return $this->renderPartial(ViewList::getView('filter/' . $view),
            ['lists' => $list],
            true
        );
    }

    /**
     * Список установленых виртульных фильтров
     */
    public function menuListVirtualFilters($extension_copy, $filters_instaled = null)
    {
        $filter_id_list = [FilterVirtualModel::VF_MY];

        if (is_array($filters_instaled) && count($filters_instaled) > 0) {
            foreach ($filter_id_list as $key => $filter) {
                if (in_array($filter, $filters_instaled)) {
                    unset($filter_id_list[$key]);
                }

            }
        }
        if (empty($filter_id_list)) {
            return;
        }

        $vf = FilterVirtualModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->appendFiltes($filter_id_list);
        $virtual_filters = $vf->getResultFilters();

        if (!empty($virtual_filters)) {
            return $this->renderPartial(ViewList::getView('filter/list-menu'),
                [
                    'lists' => $virtual_filters,
                    'attr'  => $vf->getResultAttr(),
                ],
                true
            );
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
            ],
            true
        );

    }

    /**
     * возвращает список фильтров без виртульных
     */
    private function getWithOutVirtualFilters($filter_id_list)
    {
        $result = [];
        $vf = array_keys(FilterVirtualModel::$filters);
        foreach ($filter_id_list as $filter_id) {
            if (!in_array($filter_id, $vf)) {
                $result[] = $filter_id;
            }
        }

        return $result;
    }

    /**
     *  Возвращает параметры условия для SQL запроса
     */
    public function getParamsToQuery($extension_copy, $filter_id_list, $filter_params = null, $add_table_name = true, $filters_max = 2)
    {
        if ($filters_max) {
            $filter_id_list = array_slice($filter_id_list, 0, $filters_max);
        }

        $filters = [];
        $filter_id_wovf = $this->getWithOutVirtualFilters($filter_id_list);

        if (!empty($filter_id_wovf)) {
            $criteria = new CDbCriteria();
            $criteria->addCondition("copy_id=:copy_id");
            $criteria->params = [':copy_id' => $extension_copy->copy_id];
            $criteria->addInCondition("filter_id", $filter_id_wovf);

            $filters = FilterModel::model()->findAll($criteria);
        }

        $filter_virtual = new FilterVirtualModel();
        $filter_virtual
            ->setExtensionCopy($extension_copy)
            ->appendFiltes($filter_id_list, $filter_params)
            ->marge($filters);

        $conditions = [];
        $params = [];
        $having = [];
        $filter_params = [
            'there_is_participant' => false,
        ];

        foreach ($filters as $filter) {
            $data = $filter
                ->setAddTableName($add_table_name)
                ->prepareQuery()
                ->getQuery();
            if ($filter->getThereIsParticipant()) {
                $filter_params['there_is_participant'] = true;
            }

            if (!empty($data['conditions'])) {
                $conditions = array_merge($conditions, $data['conditions']);
            }

            if (!empty($data['having'])) {
                $having = array_merge($having, $data['having']);
            }

            if (!empty($data['params'])) {
                $params = array_merge($params, $data['params']);
            }
        }

        if (empty($conditions) && empty($having)) {
            return;
        }

        if (!empty($conditions)) {
            array_unshift($conditions, 'AND');
        }

        return [
            'having'        => $having,
            'conditions'    => $conditions,
            'params'        => $params,
            'filter_params' => $filter_params,
        ];

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

        if (isset($_POST['data']['id']) && !empty($_POST['data']['id'])) {
            $model = FilterModel::model()->findByPk($_POST['data']['id']);
            $filter_id_old = $model->filter_id;
        } else {
            if (FilterModel::model()->exists('copy_id = :copy_id AND title = :title', [":copy_id" => $_POST['data']['copy_id'], ":title" => $_POST['data']['title']])) {
                $validate->addValidateResult('w', Yii::t('messages', 'Filter with the same name has already exists'));

                return $this->renderJson([
                    'status'   => false,
                    'messages' => $validate->getValidateResultHtml(),
                ]);
            }
            $model = new FilterModel();
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
                'menu_list'     => $this->menuList(ExtensionCopyModel::model()->findByPk($model->copy_id)),
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

        $filter_model = FilterModel::model()->findByPk($_POST['id']);
        if ($filter_model->validate() == false) {
            return $this->renderJson([
                'status'   => false,
                'messages' => $filter_model->getErrorsHtml(),
            ]);
        }

        $delete = $filter_model->delete();

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

        $filter_model = FilterModel::model()->findByPk($filter_id);
        if (empty($filter_model)) {
            return $this->renderJson(
                [
                    'status' => false,
                ]
            );
        }

        $filter_model->setAccessToChange();

        $filter_params = $filter_model ? $filter_model->getParams() : null;

        if (FilterModel::$_access_to_change && !empty($filter_params)) {
            foreach ($filter_params as $value) {
                $content .= $this->actionAddPanel($copy_id,
                    $filter_id,
                    $value['name'],
                    $value['condition'],
                    (isset($value['condition_value']) ? $value['condition_value'] : ""),
                    false);
            }
            $status = true;
            $result = $this->renderPartial(ViewList::getView('filter/block'), [
                'filter_id'         => $filter_id,
                'content'           => $content,
                'btn_filter_delete' => true,
                'filter_model'      => $filter_model,
            ],
                true);
        }

        return $this->renderJson(
            [
                'status' => $status,
                'data'   => $result,
            ]
        );
    }

    /**
     *   Добавляем новый блок
     */
    public function actionAddBlock($copy_id, $renderJSON = true)
    {
        $content = $this->actionAddPanel($copy_id, null, null, null, '', false, Yii::app()->request->getParam('this_template'));
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

    /**
     * Добравляем новую панель
     */
    public function actionAddPanel($copy_id, $filter_id = null, $field_value = null, $condition_value = null, $condition_value_value = '', $renderJSON = true, $this_template = EditViewModel::THIS_TEMPLATE_MODULE)
    {
        $result = $this->renderPartial(ViewList::getView('filter/panel'), [
                'filterPanel' => new FilterPanel([
                    'extensionCopy'       => \ExtensionCopyModel::model()->findByPk($copy_id),
                    'fieldValue'          => $field_value,
                    'destination'         => 'listView',
                    'conditionValue'      => $condition_value,
                    'conditionValueValue' => $condition_value_value,
                    'thisTemplate'        => $this_template,
                ])
            ]
        , true);

        if ($renderJSON === true) {
            return $this->renderJson([
                'status' => true,
                'data'   => $result,
            ]);
        } else {
            return $result;
        }
    }

    /**
     *
     */
    public function actionAddCondition($copy_id, $field_name, $condition_value = null, $renderJSON = true)
    {
        $result = '';
        $field_schema = ExtensionCopyModel::model()
            ->findByPk($copy_id)
            ->getFieldSchemaForFilter($field_name, 'block_participant');

        if (!empty($field_schema)) {
            $result = Yii::app()->controller->widget(ViewList::getView('ext.Filters.ListView.Elements.FilterCondition.FilterCondition'),
                [
                    'field_schema'    => $field_schema,
                    'condition_value' => $condition_value,
                    'destination'     => 'listView',
                ],
                true);

        }
        if ($renderJSON === true) {
            return $this->renderJson([
                'status' => true,
                'data'   => $result,
            ]);
        } else {
            return $result;
        }
    }

    /**
     *
     */
    public function actionAddConditionValue($copy_id, $field_name, $condition_value = null, $condition_value_value = '', $this_template, $renderJSON = true)
    {
        $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
        $schema = null;
        if (!empty($field_name)) {
            $schema = $extension_copy->getFieldSchemaForFilter($field_name, 'block_participant');
        }

        $result = Yii::app()->controller->widget(ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
            [
                'extension_copy'        => $extension_copy,
                'schema'                => $schema,
                'condition_value'       => $condition_value,
                'condition_value_value' => $condition_value_value,
                'this_template'         => $this_template,
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

}
