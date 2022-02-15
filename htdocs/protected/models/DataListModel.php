<?php

/**
 * @author Alex R.
 */
class DataListModel
{
    const TYPE_LIST_VIEW = 'lv';
    const TYPE_PROCESS_VIEW = 'pv';
    const TYPE_PARENT_LIST_VIEW = 'plv';
    const TYPE_FOR_SELECT_TYPE_LIST = 'stl';

    const METHTOD_LOAD_DATA_ALL = 'all';
    const METHTOD_LOAD_DATA_ROW = 'row';
    const METHTOD_LOAD_DATA_COUNT = 'count';
    const METHTOD_LOAD_DATA_BOOL = 'boll';

    /**
     * @var ExtensionCopyModel
     */
    protected $_extension_copy;

    protected $_data_id;

    protected $_this_template;

    protected $_finished_object;

    protected $_with_out_bpm_operation;

    protected $_global_params = [];

    protected $_module;

    protected $_data = [];

    protected $_data_model;

    protected $_data_if_participant;

    protected $_before_condition = null;

    protected $_before_params = [];

    protected $_append_to_select = null;

    protected $_order_fields = [];

    protected $_pagination_page = null;

    protected $_pagination_page_size = null;

    protected $_last_condition = null;

    protected $_last_params = [];

    protected $_use_process_mark = false;

    protected $_sorting_to_pk = null; // сортировка по первичному полю: null, asc, desc

    protected $_get_all_data = false;   // отбирает все данные (без пагинации)

    protected $_set_sorting_params = true;

    protected $_appent_check_pci_pdi_is_empty = true;     // включает проверку параметров pci & pdi для фильтрации по участникам

    protected $_set_pagination = false;

    protected $_create_filter_controller = true;

    protected $_process_view_flush_empty_panels = true;

    protected $_defined_PK = false; //отбирает только определенные primary keys

    public static function getInstance()
    {
        return new self;
    }

    public function setExtensionCopy($extension_copy)
    {
        $this->_extension_copy = $extension_copy;

        if ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS) {
            $this->_with_out_bpm_operation = true;
        }

        return $this;
    }

    /**
     * @param array $only_PK
     * @return $this
     */
    public function setDefinedPK($only_PK)
    {
        if (!empty($only_PK) && is_array($only_PK)) {
            $this->_defined_PK = $only_PK;
        }

        return $this;
    }

    public function setFinishedObject($finished_object)
    {
        $this->_finished_object = $finished_object;

        return $this;
    }

    public function setThisTemplate($this_template)
    {
        $this->_this_template = $this_template;

        return $this;
    }

    public function setPaginationPage($pagination_page)
    {
        $this->_pagination_page = $pagination_page;

        return $this;
    }

    public function setPaginationPageSize($pagination_page_size)
    {
        $this->_pagination_page_size = $pagination_page_size;

        return $this;
    }

    public function setGlobalParams($global_params)
    {
        $this->_global_params = $global_params;

        return $this;
    }

    public function setModule($module)
    {
        $this->_module = $module;

        return $this;
    }

    public function setProcessViewFlushEmptyPanels($process_view_flush_empty_panels)
    {
        $this->_process_view_flush_empty_panels = $process_view_flush_empty_panels;

        return $this;
    }

    public function setDataId($data_id)
    {
        $this->_data_id = $data_id;

        return $this;
    }

    public function setUseProcessMark($use_process_mark)
    {
        $this->_use_process_mark = $use_process_mark;

        return $this;
    }

    public function setCreateFilterController($create_filter_controller)
    {
        $this->_create_filter_controller = $create_filter_controller;

        return $this;
    }

    public function setSortingParams($set_sorting_params)
    {
        $this->_set_sorting_params = $set_sorting_params;

        return $this;
    }

    public function setGetAllData($get_all_data)
    {
        $this->_get_all_data = $get_all_data;

        return $this;
    }

    public function setAppentCheckPciPdiIsEmpty($appent_check_pci_pdi_is_empty)
    {
        $this->_appent_check_pci_pdi_is_empty = $appent_check_pci_pdi_is_empty;

        return $this;
    }

    public function getDataModel()
    {
        return $this->_data_model;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function setBeforeCondition($condition, $params = [])
    {
        $this->_before_condition = $condition;
        $this->_before_params = $params;

        return $this;
    }

    public function setLastCondition($condition, $params = [])
    {
        $this->_last_condition = $condition;
        $this->_last_params = $params;

        return $this;
    }

    public function addOrderField($field)
    {
        if (!empty($field)) {
            $this->_order_fields[] = $field;
        }

        return $this;
    }

    public function setAppendToSelect($condition)
    {
        $this->_append_to_select = $condition;

        return $this;
    }

    public function setSortingToPk($sorting_to_pk)
    {
        $this->_sorting_to_pk = $sorting_to_pk;

        return $this;
    }

    public function prepare($type, $methtod_load_data = self::METHTOD_LOAD_DATA_ALL)
    {
        switch ($type) {
            case self::TYPE_LIST_VIEW :
                $this->prepareListView();
                break;
            case self::TYPE_PROCESS_VIEW :
                $this->prepareProcessView();
                break;
            case self::TYPE_PARENT_LIST_VIEW :
                $this->prepareParentListView();
                break;
            case self::TYPE_FOR_SELECT_TYPE_LIST :
                $this->prepareSelectTypeList();
                break;
        }

        if ($methtod_load_data !== null) {
            $this->loadData($type, $methtod_load_data);
        }

        return $this;
    }

    /**
     * getDataIfParticipant
     */
    private function getDataIfParticipant()
    {
        if ($this->_data_if_participant !== null) {
            return $this->_data_if_participant;
        } else {
            return Yii::app()->controller->module->dataIfParticipant();
        }
    }

    /**
     * setDataIfParticipant - принудительная установка параметра _data_if_participant
     */
    public function setDataIfParticipant($data_if_participant)
    {
        if ($data_if_participant && $this->_extension_copy) {
            $data_if_participant = $this->_extension_copy->dataIfParticipant();
        }

        $this->_data_if_participant = $data_if_participant;

        return $this;
    }

    private function loadData($type, $methtod_load_data = self::METHTOD_LOAD_DATA_ALL)
    {
        $b = (boolean)($this->_data_model instanceof \DataModel);

        switch ($methtod_load_data) {
            case self::METHTOD_LOAD_DATA_ALL:
                $this->_data = ($b ? $this->_data_model->findAll() : []);
                break;
            case self::METHTOD_LOAD_DATA_ROW:
                $this->_data = ($b ? $this->_data_model->findRow() : []);
                break;
            case self::METHTOD_LOAD_DATA_COUNT:
                $this->_data = ($b ? $this->_data_model->findCount() : 0);
                break;
            case self::METHTOD_LOAD_DATA_BOOL:
                $this->_data = ($b ? (boolean)$this->_data_model->findCount() : false);
                break;
        }

        // обработка пагинации
        if ($this->_set_pagination) {
            $this->_set_pagination = false;
            \Pagination::getInstance()->setItemCount();

            // если страница пагинации указан больше чем есть в действительности
            if (\Pagination::switchActivePageIdLarger()) {
                $this->prepare($type, $methtod_load_data);
            }
        };

        return $this;
    }

    /**
     * prepare ListView
     */
    private function prepareListView()
    {
        if ($this->_create_filter_controller) {
            [$filter_controller] = Yii::app()->createController($this->_extension_copy->extension->name . '/ListViewFilter');
            if ($filter_controller == false) {
                $filter_controller = new ListViewFilter('');
            }
        }

        $only_id = DataValueModel::getInstance()->getIdOnTheGroundParent($this->_extension_copy->copy_id, $this->_global_params['pci'], $this->_global_params['pdi']);
        if ($only_id === false) {
            return $this;
        }

        $search = new Search();
        $search->setTextFromUrl();

        $filters = new Filters();
        $filters->setTextFromUrl();
        $there_is_participant = false;
        if (!$filters->isTextEmpty()) {
            $filter_data = $filter_controller->getParamsToQuery($this->_extension_copy, $filters->getText());
            if ($filter_data) {
                $there_is_participant = $filter_data['filter_params']['there_is_participant'];
            }
        }

        //*********************
        // *** get data
        $data_model = (new DataModel)
            ->setIsSetSearch($search::$text === null ? false : true)
            ->setExtensionCopy($this->_extension_copy)
            ->setFromModuleTables();

        if ($this->_append_to_select) {
            $data_model->addSelect($this->_append_to_select);
        }

        //replace values for block type
        /*
        if(!$this->_extension_copy->isShowAllBlocks()) {
            $block_field_data = $this->_extension_copy->getFieldBlockData();
            if(isset($block_field_data['name'])) 
                $data_model->addCase($block_field_data['name'], $this->_extension_copy->getSchemaBlocksData(), 'unique_index', 'title', true);
        }  
        */
        //only defined PK
        if ($this->_defined_PK) {
            $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . "_id in ('" . implode("','", $this->_defined_PK) . "')"]);
        }

        //set _before_condition
        if (!empty($this->_before_condition)) {
            $data_model->andWhere($this->_before_condition, $this->_before_params);
        }

        //set "is_bpm_operation = 0"
        if ($this->_with_out_bpm_operation === true) {
            $data_model->andWhere(['AND', '(' . $this->_extension_copy->getTableName() . '.is_bpm_operation is NULL OR ' . $this->_extension_copy->getTableName() . '.is_bpm_operation = "0")']);
        }

        //responsible
        if ($this->_extension_copy->isResponsible()) {
            $data_model->setFromResponsible($there_is_participant);
        }

        //participant
        if ($this->_extension_copy->isParticipant()) {
            $data_model->setFromParticipant($there_is_participant);
        }

        //filters
        if (!empty($filter_data)) {
            if (!empty($filter_data['conditions'])) {
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
            }
            if (!empty($filter_data['having'])) {
                $having_array = [];
                foreach ($filter_data['having'] as $having_element) {
                    $having_array[] = $having_element['query'];
                }
                $data_model->setHaving(implode(' AND ', $having_array), $filter_data['params']);
            }
        }
        //finished_object
        if ($this->_finished_object) {
            if ($this->_global_params['finished_object']) {
                $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, [FilterVirtualModel::VF_FINISHED_OBJECT], [FilterVirtualModel::VF_FINISHED_OBJECT => ['corresponds' => 'corresponds']]);
            } else {
                $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, [FilterVirtualModel::VF_FINISHED_OBJECT], [FilterVirtualModel::VF_FINISHED_OBJECT => ['corresponds' => 'corresponds_not']]);
            }
            if (!empty($filter_data_2)) {
                $data_model->andWhere($filter_data_2['conditions'], $filter_data_2['params']);
            }
        }

        //this_template
        if ($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE) {
            $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE . '" ']);
        } else {
            if ($this->_global_params['pci']) {
                $data_model->andWhere([
                    'AND',
                    $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '" OR ' . $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'
                ]);
            } else {
                $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null']);
            }
        }

        if ($this->_use_process_mark) {
            $data_model->andWhere(['AND', '(NOT EXISTS (select mark_id FROM {{api_processing_mark}} WHERE copy_id = ' . $this->_extension_copy->copy_id . ' AND card_id = ' . $this->_extension_copy->getTableName() . '.' . $this->_extension_copy->prefix_name . '_id))']);
        }

        //order
        if ($this->_set_sorting_params) {
            Sorting::getInstance()->setParamsFromUrl();
        }

        $order_data = $data_model->getOrderFromSortingParams('both', $this->_sorting_to_pk);
        if (!empty($this->_order_fields)) {
            $this->_order_fields[] = $order_data;
            $order_data = implode(',', $this->_order_fields);
        }

        $data_model->setOrder($order_data);

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();

        //parent module
        if (!empty($only_id)) {
            $data_model->setParentModule($only_id);
        }

        //search
        if ($search::$text !== null) {
            $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
        }

        // Добавляет условие отбора where как новый уровень. _last_condition может устанавливаться дополнительно в классе..
        if ($this->_last_condition !== null) {
            $data_model->addGlobalCondition($this->_last_condition, $this->_last_params);
        }

        //participant only
        if ($this->getDataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())) {
            if ($this->_appent_check_pci_pdi_is_empty) {
                if (empty($this->_global_params['pci']) && empty($this->_global_params['pdi'])) {
                    $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
                }
            } else {
                $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
            }
        }

        // Добавляет условие отбора данных "только участники по связи через модуль через поле Название
        if ($this->getDataIfParticipant() == false && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())) {
            if ($this->_appent_check_pci_pdi_is_empty) {
                if (empty($this->_global_params['pci']) && empty($this->_global_params['pdi'])) {
                    $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
                }
            } else {
                $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
            }
        }

        if ($this->_pagination_page === null || $this->_pagination_page_size === null) {
            // pagination
            $pagination = new Pagination();
            $pagination->setParamsFromUrl();
            $limit = $pagination->getActivePageSize();
            $offset = $pagination->getOffset();
        }

        if ($this->_pagination_page !== null && $this->_pagination_page_size !== null) {
            $limit = $this->_pagination_page_size;
            $offset = ($this->_pagination_page - 1) * $this->_pagination_page_size;
        }

        if ($this->_get_all_data == false && $limit > 0) {
            $select = $data_model->getSelect();
            $data_model
                ->setSelect('SQL_CALC_FOUND_ROWS (0)' . (!empty($select) ? ',' . $select : ', data.*'))
                ->setLimit($limit)
                ->setOffSet($offset);

            $this->_set_pagination = true;
        }

        $data_model->withOutRelateTitleTemplate($this->_global_params['pdi']);

        $this->_data_model = $data_model;

        return $this;
    }

    /**
     * prepareProcessViewSortingListModel
     */
    private function prepareProcessViewSortingListModel()
    {
        ProcessViewSortingListModel::getInstance()
            ->setGlobalVars([
                '_extension_copy'  => $this->_extension_copy,
                '_pci'             => $this->_global_params['pci'],
                '_pdi'             => $this->_global_params['pdi'],
                '_finished_object' => $this->_global_params['finished_object'],
                '_this_template'   => $this->_this_template,
            ]);

        return $this;
    }

    /**
     * processViewFlushList - Проверяет на наличие "старой" сортировки. Если сортировка изменилась - чистим базу
     */
    private function processViewFlushList()
    {
        //$fields_group = Sorting::getInstance()->getParamFieldName();

        $fields_group = (new ProcessViewBuilder())->setExtensionCopy($this->_extension_copy)->getFieldsGroup(false);

        $is_changed_sorting = ProcessViewSortingListModel::getInstance()->isChangedSorting($fields_group);

        // если изменилась сортировка - $is_changed_sorting = true
        if ($is_changed_sorting) {
            if ($this->_extension_copy->copy_id == ExtensionCopyModel::MODULE_TASKS) {
                if ($this->_module->view_related_task == false) { // если открыто не через родительскую сущность (pci=pdi=null)
                    // Очищает от старых пустых списков
                    ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
                }
            } else {
                // Очищает от старых пустых списков
                ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
            }
        }

        // Очищает от старых пустых списков. Только, если панели cоздаются и удаляются автоматически (стандартный функционал)
        if ($this->_process_view_flush_empty_panels && ProcessViewSortingListModel::getInstance()->accessChangePanels() == false) {
            ProcessViewSortingListModel::getInstance()->flushPanelEntities(true);
        }
    }

    /**
     * prepare ProcessView - созвращает данные (сгрупированные) для формирования список для Process View.
     * Сами карточки для списков формируются билдером
     */
    private function prepareProcessView()
    {
        [$filter_controller] = Yii::app()->createController($this->_extension_copy->extension->name . '/ListViewFilter');
        $filters = new Filters();
        $filters->setTextFromUrl();
        $there_is_participant = false;
        if (!$filters->isTextEmpty()) {
            $filter_data = $filter_controller->getParamsToQuery($this->_extension_copy, $filters->getText());
            if ($filter_data) {
                $there_is_participant = $filter_data['filter_params']['there_is_participant'];
            }
        }

        //if($this->countImportStatus($this->_extension_copy)) $this->redirect(Yii::app()->createUrl('/module/listView/show/'.$this->_extension_copy->copy_id));
        $only_id = DataValueModel::getInstance()->getIdOnTheGroundParent($this->_extension_copy->copy_id, $this->_global_params['pci'], $this->_global_params['pdi']);

        $search = new Search();
        $search->setTextFromUrl();

        if ($this->_module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS && $this->_module->view_related_task) {
            Sorting::$params = null;
            $_GET['sort'] = json_encode(['todo_list' => 'a']);
        }

        //order
        if ($this->_set_sorting_params) {
            Sorting::getInstance()->setParamsFromUrl();
        }
        $sorting_params = Sorting::getInstance()->getParams();

        if ($this->_extension_copy->isSetFieldInSchema(Sorting::getInstance()->getParamFieldName($sorting_params)) == false ||
            SchemaOperation::inProcessViewCheckedGroup(Sorting::getInstance()->getParamFieldName($sorting_params), $this->_extension_copy->getSchemaParse()) == false) {
            Sorting::$params = null;
            $sorting_params = null;
        }

        $sorting_params = SchemaOperation::getWithOutDeniedRelateCopyId($sorting_params, $this->_extension_copy);
        if (empty($sorting_params)) {
            Sorting::$params = null;
            $sorting_params = null;
        }

        if (empty($sorting_params)) {
            Sorting::getInstance()->setParamsFromFieldNames($this->getFirstFieldName($this->_extension_copy));
        }

        $this
            ->prepareProcessViewSortingListModel()
            ->processViewFlushList();

        // data_id_list - только опеределенные карточки (по id)
        $data_id_list = null;
        if ($this->_global_params['data_id_list']) {
            $data_id_list = (array)$this->_global_params['data_id_list'];
            $data_id_list = array_unique($data_id_list);
        }

        // order END
        $data_model = new DataModel();
        $data_model->setExtensionCopy($this->_extension_copy);

        if ($only_id || $only_id === null) {

            $data_model->setFromModuleTables();

            //replace values for block type
            /*
            if(!$this->_extension_copy->isShowAllBlocks()) {
                $block_field_data = $this->_extension_copy->getFieldBlockData();
                if(isset($block_field_data['name']))
                    $data_model->addCase($block_field_data['name'], $this->_extension_copy->getSchemaBlocksData(), 'unique_index', 'title', true);
            }
            */

            if ($this->_append_to_select) {
                $data_model->addSelect($this->_append_to_select);
            }

            // data_id_list - только опеределенные карточки (по id)
            if ($this->_global_params['data_id_list']) {
                $data_model->andWhere(['IN', $this->_extension_copy->getPkFieldName(true), $data_id_list]);
            }

            //set "is_bpm_operation = 0"
            if ($this->_with_out_bpm_operation === true) {
                $data_model->andWhere(['AND', '(' . $this->_extension_copy->getTableName() . '.is_bpm_operation is NULL OR ' . $this->_extension_copy->getTableName() . '.is_bpm_operation = "0")']);
            }

            //responsible
            if ($this->_extension_copy->isResponsible()) {
                $data_model->setFromResponsible($there_is_participant);
            }

            //participant
            if ($this->_extension_copy->isParticipant()) {
                $data_model->setFromParticipant($there_is_participant);
            }

            //filters
            if (!empty($filter_data)) {
                if (!empty($filter_data['conditions'])) {
                    $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
                }
                if (!empty($filter_data['having'])) {
                    $having_array = [];
                    foreach ($filter_data['having'] as $having_element) {
                        $having_array[] = $having_element['query'];
                    }
                    $data_model->setHaving(implode(' AND ', $having_array), $filter_data['params']);
                }
            }

            //finished_object
            if ($this->_finished_object) {
                if ($this->_global_params['finished_object']) {
                    $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, [FilterVirtualModel::VF_FINISHED_OBJECT], [FilterVirtualModel::VF_FINISHED_OBJECT => ['corresponds' => 'corresponds']]);
                } else {
                    $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, [FilterVirtualModel::VF_FINISHED_OBJECT], [FilterVirtualModel::VF_FINISHED_OBJECT => ['corresponds' => 'corresponds_not']]);
                }
                if (!empty($filter_data_2)) {
                    $data_model->andWhere($filter_data_2['conditions'], $filter_data_2['params']);
                }
            }

            //this_template
            if ($this->_module->isTemplate($this->_extension_copy)) {
                if ($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE) {
                    $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE . '" ']);
                } else {
                    if ($this->_global_params['pci']) {
                        $data_model->andWhere([
                            'AND',
                            $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '" OR ' . $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'
                        ]);
                    } else {
                        $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null']);
                    }
                }
            } else {
                if ($this->_global_params['pci']) {
                    $data_model->andWhere([
                        'AND',
                        $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '" OR ' . $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'
                    ]);
                } else {
                    $data_model->andWhere(['AND', $this->_extension_copy->getTableName() . '.this_template = "' . EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $this->_extension_copy->getTableName() . '.this_template is null']);
                }
            }

            $data_model
                ->setFromFieldTypes()
                ->setCollectingSelect();

            //parent module
            if (!empty($only_id)) {
                $data_model->setParentModule($only_id);
            }

            //search
            if ($search::$text !== null) {
                $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
            }

            $data_model
                ->setOrder($data_model->getOrderFromSortingParams());

            // Добавляет условие отбора where как новый уровень. _last_condition может устанавливаться дополнительно в классе..
            if ($this->_last_condition !== null) {
                $data_model->addGlobalCondition($this->_last_condition, $this->_last_params);
            }

            //participant only
            if ($this->getDataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())) {
                if ($this->_appent_check_pci_pdi_is_empty) {
                    if (empty($this->_global_params['pci']) && empty($this->_global_params['pdi'])) {
                        $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
                    }
                } else {
                    $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
                }
            }

            if ($this->getDataIfParticipant() == false && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())) {
                if ($this->_appent_check_pci_pdi_is_empty) {
                    if (empty($this->_global_params['pci']) && empty($this->_global_params['pdi'])) {
                        $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
                    }
                } else {
                    $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
                }
            }
        } // end $only_id

        $data_model->withOutRelateTitleTemplate($this->_global_params['pdi']);

        $fields_group = (new ProcessViewBuilder())->setExtensionCopy($this->_extension_copy)->getFieldsGroup(false);
        $fields_group_as = (new ProcessViewBuilder())->setExtensionCopy($this->_extension_copy)->getFieldsGroup(true);
        $fields_group_after_as = $data_model->getComparedWithFields(Sorting::getInstance()->getParamFieldName(), 'value');
        if (empty($fields_group_after_as)) {
            return;
        }

        $data_model
            ->setUniqueIndex($fields_group_as, 'unique_index', true)
            ->setProcessViewPanelQuery([
                'fields_group'          => $fields_group,
                'fields_group_after_as' => $fields_group_after_as,
                'data_id_list'          => $data_id_list,
                'pci'                   => $this->_global_params['pci'],
                'pdi'                   => $this->_global_params['pdi'],
                'sorting_list_id'       => $this->_global_params['sorting_list_id'],
                'only_id'               => $only_id,
                'group_data'            => ProcessViewSortingListModel::getInstance()->getGroupData(),
            ]);

        $this->_data_model = $data_model;

        return $this;
    }

    public function prepareParentListView()
    {
        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($this->_extension_copy)
            ->setFromModuleTables();

        //set "is_bpm_operation = 0"
        if ($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS) {
            $data_model->andWhere(['AND', '(' . $this->_extension_copy->getTableName() . '.is_bpm_operation is NULL OR ' . $this->_extension_copy->getTableName() . '.is_bpm_operation = "0")']);
        }

        //responsible
        if ($this->_extension_copy->isResponsible()) {
            $data_model->setFromResponsible();
        }

        //participant
        if ($this->_extension_copy->isParticipant()) {
            $data_model->setFromParticipant();
        }

        $data_model
            ->setCollectingSelect()
            ->setParentModule($this->_data_id)
            ->setDataBasedParentModule($this->_extension_copy->copy_id, true);

        $this->_data_model = $data_model;

        return $this;
    }

    /**
     * prepareSelectTypeListView - список для типа поля Select
     *
     * @return $this
     */
    protected function prepareSelectTypeList()
    {
        $schema_field = $this->_global_params['schema_field'];

        $data_list = DataModel::getInstance()
            ->setFrom($this->_extension_copy->getTableName($schema_field['name']))
            ->setOrder($schema_field['name'] . '_sort')
            ->findAll();

        $select_list = [];

        if ($data_list) {
            foreach ($data_list as $data) {
                $select_list[$data[$schema_field['name'] . '_id']] = $data[$schema_field['name'] . '_title'];
            }
        }

        if (!isset($schema_field['add_zero_value']) || (boolean)$schema_field['add_zero_value'] == true) {
            $select_list = ['' => ''] + $select_list;
        }

        $this->_data = $select_list;

        return $this;
    }

    private function getFirstFieldName($extension_copy)
    {
        $field_name = [];
        $submodule_schema_parse = $extension_copy->getSchemaParse();
        if (isset($submodule_schema_parse['elements'])) {
            $header_list = SchemaConcatFields::getInstance()
                ->setSchema($submodule_schema_parse['elements'])
                ->setWithoutFieldsForProcessViewGroup()
                ->parsing()
                ->prepareWithOutDeniedRelateCopyId()
                ->prepareWithConcatName()
                ->getResult();
        }
        if (isset($header_list['header']) && !empty($header_list['header'])) {
            foreach ($header_list['header'] as $value) {
                $field_name = explode(',', $value['name']);
                if ($header_list['params'][$field_name[0]]['process_view_group'] != true) {
                    continue;
                }

                break;
            }
        }
        if (empty($field_name)) {
            return [];
        }

        return $field_name;
    }

    /**
     * getProcessViewBuilderModelByUniqueIndex -  - возвращает подготовленную модель ProcessViewBuilder с карточками для ProcessView, отфильтрованные по определенных карточках
     *
     * @param $controller
     * @param $data
     * @return array
     */
    public static function getProcessViewListByDataIdList($controller, $vars)
    {
        $process_view_builder_model = (new ProcessViewBuilder())
            ->setExtensionCopy($vars['extension_copy'])
            ->setPci(\Yii::app()->request->getParam('pci'))
            ->setPdi(\Yii::app()->request->getParam('pdi'))
            ->setThisTemplate($controller->this_template)
            ->setModuleThisTemplate($controller->module->isTemplate($vars['extension_copy']))
            ->setFinishedObject(\Yii::app()->request->getParam('finished_object'))
            ->setPanelData($vars['panel_data'])
            ->setProcessViewIndex($vars['process_view_index'])
            ->setBlockFieldData()
            ->setJsContentReloadAddVars(true)   //
            ->setAppendCardsHtmlToPanel(false)  //
            ->setDataIdList($vars['data_id_list'])
            ->setProcessViewLoadPanels(\Yii::app()->request->getParam('process_view_load_panels'))
            ->prepare()
            ->getPanelList(true);

        return $process_view_builder_model;
    }

    /**
     * listViewCollumnPosition - сортировка полей таблице в listView
     *
     * @param $field_params
     */
    public function listViewCollumnPosition(&$field_header_list)
    {
        $name_storage = 'listView_' . $this->_extension_copy->copy_id;

        if (empty($field_header_list)) {
            History::getInstance()->deleteFromUserStorage(UsersStorageModel::TYPE_LIST_TH_POSITION, $name_storage);

            return;
        }

        $storage_params = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_POSITION, $name_storage);

        if (empty($storage_params)) {
            $storage_params = [];
        }

        $position_index = 0;
        foreach ($field_header_list as &$field_params) {
            if ($storage_params && array_key_exists($field_params['name'], $storage_params)) {
                $position_index = $storage_params[$field_params['name']];
            } else {
                if (!$storage_params) {
                    $position_index++;
                } else {
                    $max = max($storage_params);
                    if ($max) {
                        $position_index = $max + 1;
                    } else {
                        $position_index++;
                    }
                }
            }

            $storage_params[$field_params['name']] = $position_index;
            $field_params['position'] = $position_index;
        }

        // sorting function
        $sorting_function = function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return strCaseCmp($a['title'], $b['title']);
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        };
        usort($field_header_list, $sorting_function);
        $storage_params = $storage_params;
        History::getInstance()->setUserStorage(UsersStorageModel::TYPE_LIST_TH_POSITION, $name_storage, $storage_params);
    }
}
