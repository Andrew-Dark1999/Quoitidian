<?php
/**
 * CalendarView
 * Author: Alex R.
 */

class CalendarView extends \Controller
{

    /**
     * filter
     */
    public function filters()
    {
        return [
            'checkAccess',
        ];
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'index':
            case 'show':
                if ($this->module->extensionCopy->isCalendarView() == false) {
                    $this->redirect(Yii::app()->createUrl('/module/listView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                }

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if (ValidateRules::checkIsSetParentDataModule() == false) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }

                if (\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false) &&
                    !Access::checkAccessDataOnParticipant(\Yii::app()->request->getParam('pci', null), \Yii::app()->request->getParam('pdi', null))
                ) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;

            case 'showTemplate':
                if ($this->module->extensionCopy->isCalendarView() == false) {
                    $this->redirect(Yii::app()->createUrl('/module/listView/show') . '/' . $this->module->extensionCopy->copy_id . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                }

                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if (ValidateRules::checkIsSetParentDataModule() == false) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if (\Yii::app()->request->getParam('pci', false) && \Yii::app()->request->getParam('pdi', false)) {
                    throw new CHttpException(404);
                }

                break;

            case 'getDataByPeriod' :
            case 'getDataByDateTimes' :
            case 'getDataByDateTimeRange' :
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'), false);
                }
                break;

            case 'updateData' :
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have write access to this object'), false);
                }
                break;

        }

        $this->module->setAccessCheckParams($this->module->extensionCopy->copy_id, Access::ACCESS_TYPE_MODULE);
        $filterChain->run();
    }

    public function actionGetDataByPeriod($copy_id)
    {
        $vars = $_POST + ['copy_id' => $copy_id];
        $vars['finished_object'] = \Yii::app()->request->getParam('finished_object', null);

        $result = (new CalendarViewModel($vars))
            ->setFinishedObject($this->module->finishedObject())
            ->actionGetDataByPeriod(
                Yii::app()->request->getParam('active_field_name')
            )
            ->getResult();

        return $this->renderJson($result);
    }

    public function actionGetDataByDateTimes($copy_id)
    {
        $vars = $_POST + ['copy_id' => $copy_id];
        $vars['finished_object'] = \Yii::app()->request->getParam('finished_object', null);

        $result = (new CalendarViewModel($vars))
            ->setFinishedObject($this->module->finishedObject())
            ->actionGetDataByDateTimes(
                Yii::app()->request->getParam('active_field_name')
            )
            ->getResult();

        return $this->renderJson($result);
    }

    public function actionGetDataByDateTimeRange($copy_id)
    {
        $vars = $_POST + ['copy_id' => $copy_id];
        $vars['finished_object'] = \Yii::app()->request->getParam('finished_object', null);

        $result = (new CalendarViewModel($vars))
            ->setFinishedObject($this->module->finishedObject())
            ->actionGetDataByDateTimeRange(
                Yii::app()->request->getParam('active_field_name')
            )
            ->getResult();

        return $this->renderJson($result);
    }

    public function actionUpdateData($copy_id)
    {
        $vars = $_POST + ['copy_id' => $copy_id];

        $result = (new CalendarViewModel($vars))
            ->actionUpdateDataByParams(
                Yii::app()->request->getParam('active_field_name')
            )
            ->getResult();

        return $this->renderJson($result);
    }

    /**
     * Возвращает (базовую) форму ListView
     */
    public function actionShow()
    {
        $this->data = array_merge($this->data, $this->getDataForView($this->module->extensionCopy));
        $this->setMenuMain();

        History::getInstance()->updateUserStorageFromUrl(
            $this->module->extensionCopy->copy_id,
            'calendarView',
            true,
            \Yii::app()->request->getParam('pci'),
            \Yii::app()->request->getParam('pdi')
        ); // только  для  UsersStorageModel::TYPE_PAGE_PARAMS
        History::getInstance()->updateUserStorageFromUrl(
            ['destination' => 'calendarView', 'copy_id' => $this->module->extensionCopy->copy_id],
            null,
            null,
            \Yii::app()->request->getParam('pci'),
            \Yii::app()->request->getParam('pdi')
        );

        $this->renderAuto(ViewList::getView('site/calendarView'), $this->data);
    }

    /**
     * Возвращает (базовую) форму ListView
     */
    public function actionShowTemplate()
    {
        $this->this_template = EditViewModel::THIS_TEMPLATE_TEMPLATE;
        $this->data = array_merge($this->data, $this->getDataForView($this->module->extensionCopy));
        $this->setMenuMain();

        $this->renderAuto(ViewList::getView('site/calendarView'), $this->data);
    }

    /**
     * установка параметров для выделения пункта меню
     * принцип: если есть pci и this_template = отмечаем меню родителя
     */
    protected function setMenuMain()
    {
        $copy_id = (isset($_GET['pci']) ? $_GET['pci'] : $this->module->extensionCopy->copy_id);
        $index = $copy_id;

        if ($this->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS) {
            if ($this->module->view_related_task) {
                $index = $this->module->extensionCopy->copy_id . TasksModule::$relate_store_postfix_params;
            }
        }

        $history_model = \History::getInstance()->getUserStorage(UsersStorageModel::TYPE_PAGE_PARAMS, $index);

        $menu_main = [
            'index'         => $copy_id,
            'this_template' => (isset($_GET['pci']) ? $history_model['this_template'] : $this->this_template),
        ];
        $this->data = array_merge($this->data, ['menu_main' => $menu_main]);
    }

    /**
     *   Возвращает все данные для отображения listView
     */
    public function getDataForView($extension_copy)
    {
        [$filter_controller] = Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        $data = [];
        $data['extension_copy'] = $extension_copy;

        $filters = Filters::getInstance()->setTextFromUrl()->getText();
        $data['filter_menu_list_virual'] = $filter_controller->menuListVirtualFilters($extension_copy, $filters);
        $data['filter_menu_list'] = $filter_controller->menuList($extension_copy, $filters);
        $data['filters_installed'] = (is_array($filters) ? $filter_controller->filtersInstalled($extension_copy, $filters) : "");
        $data['finished_object'] = Yii::app()->request->getParam('finished_object');

        return $data;
    }

    public function getSwitchIconList($extension_copy)
    {
        $icon_list = [];

        $crm_properties = [
            '_active_object'  => $this,
            '_extension_copy' => $extension_copy,
        ];

        if (Yii::app()->controller->module->switch_to_pw && (SchemaOperation::getInstance()->beProcessViewGroupParam($extension_copy->getSchemaParse()))) {
            if ($this->module->list_view_icon_show['switch_to_pv']) {
                $icon_list[] = [
                    'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(['module' => ['destination' => 'processView']])->prepare()->getKey(),
                    'data-type'       => null,
                    'class'           => 'ajax_content_reload',
                    'i_class'         => 'fa fa-bars',
                ];
            }
        }

        if ($this->module->list_view_icon_show['switch_to_lv']) {
            $icon_list[] = [
                'data-action_key' => (new \ContentReloadModel(8, $crm_properties))->addVars(['module' => ['destination' => 'listView']])->prepare()->getKey(),
                'data-type'       => null,
                'class'           => 'ajax_content_reload',
                'i_class'         => 'fa fa-th-list',
            ];
        }

        $icon_list[] = [
            'data-action_key' => null,
            'data-type'       => 'calendar',
            'class'           => 'element',
            'i_class'         => 'fa fa-calendar active',
        ];

        return $icon_list;
    }

}
