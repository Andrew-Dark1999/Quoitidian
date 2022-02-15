<?php
/**
 * ProcessViewBuilder
 *
 * Строит панели и карточки ProcessView
 *
 */
class ProcessViewBuilder{

    private $_extension_copy;
    private $_pci;
    private $_pdi;
    private $_panel_data;
    private $_process_view_index;

    private $_fields_group;
    private $_fields_view;

    private $_panel_list = array();
    private $_card_list = array();

    private $_data_id_list = array();

    private $_this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    private $_this_module_template = EditViewModel::THIS_TEMPLATE_MODULE;
    private $_finished_object;
    private $_with_out_bpm_operation;
    private $_block_field_name_replace = false;
    private $_js_content_reload_add_vars = false;
    private $_process_view_load_panels = false;


    private $_append_cards_html_to_panel = true;
    private $_load_cards = true;

    private $_last_condition = null;
    private $_last_params = array();




    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;

        if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS){
            $this->_with_out_bpm_operation = true;
        }
        if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_COMMUNICATIONS){
            ///$this->_with_out_bpm_operation = true;
            $this->_last_condition='user_create=:user_id';
            $this->_last_params=array(':user_id' => \WebUser::getUserId());
        }


        return $this;
    }


    public function setPci($pci){
        $this->_pci = $pci;
        return $this;
    }


    public function setPdi($pdi){
        $this->_pdi = $pdi;
        return $this;
    }


    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }

    public function setModuleThisTemplate($this_template){
        $this->_this_module_template = $this_template;
        return $this;
    }

    public function setFinishedObject($finished_object){
        $this->_finished_object = $finished_object;
        return $this;
    }

    public function setJsContentReloadAddVars($js_content_reload_add_vars){
        $this->_js_content_reload_add_vars = $js_content_reload_add_vars;
        return $this;
    }


    public function setPanelData($panel_data){
        $this->_panel_data = $panel_data;

        return $this;
    }



    public function setProcessViewIndex($process_view_index){
        $this->_process_view_index = $process_view_index;
        return $this;
    }


    public function setProcessViewLoadPanels($process_view_load_panels){
        $this->_process_view_load_panels = $process_view_load_panels;
        return $this;
    }


    public function setAutoProcessViewIndex($controller){
        $class = null;

        if($controller) {
            $class = get_class($controller);
        }

        $prefix = 'listView';
        $postfix = '';

        switch($class){
            case 'ProcessViewController' :
                $prefix = 'processView';
        }

        if($controller->module->extensionCopy->copy_id == ExtensionCopyModel::MODULE_TASKS && $controller->module->view_related_task){
            $postfix = TasksModule::$relate_store_postfix_params;
        }

        $this->_process_view_index = $prefix . '_' . $this->_extension_copy->copy_id . $postfix;

        return $this;
    }


    public function setAppendCardsHtmlToPanel($append_cards_html_to_panel){
        $this->_append_cards_html_to_panel = $append_cards_html_to_panel;
        return $this;
    }

    public function setLoadCards($load_cards){
        $this->_load_cards = $load_cards;
        return $this;
    }

    public function setDataIdList($data_id_list){
        $this->_data_id_list = $data_id_list;
        return $this;
    }


    public function setFieldsGroup($fields_group){
        $this->_fields_group = $fields_group;
        return $this;
    }

    public function isActiveFieldsGroup($fields_group){
        if($this->_fields_group === null) return false;

        if($fields_group == implode(',', $this->_fields_group)){
            return true;
        }

        return false;
    }

    public function getFieldsGroupStr(){
        if($this->_fields_group == false) return;
        return implode(',', $this->_fields_group);
    }

    public function getFieldsView($implode = false){
        return ($implode ? implode(',', $this->_fields_view) : $this->_fields_view);
    }



    public function getProcessViewShowZeroPanelsIfFind(){
        $b = (Search::getInstance()->getText() || Filters::getInstance()->getText());

        if($b == false) return true;

        return Yii::app()->controller->module->getProcessViewShowZeroPanelsIfFind();
    }




    private function addPanel($panel_data){
        $card_list_html = $this->getCards($panel_data['sorting_list_id']);

        if($card_list_html == false && $this->getProcessViewShowZeroPanelsIfFind() == false){
            return $this;
        }

        if(!empty($card_list_html)){
            $card_list_html = implode($card_list_html);
        }



        $html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ProcessView.Elements.PPanel.PPanel'),
            array(
                'process_view_builder_model' => $this,
                'extension_copy' => $this->_extension_copy,
                'this_template' => $this->_this_template,
                'this_module_template' => $this->_this_module_template,
                'fields_group' => $this->_fields_group,
                'panel_data' => $panel_data,
                'block_field_name_replace' => $this->_block_field_name_replace,
                'append_cards_html' => $this->_append_cards_html_to_panel,
                'card_list_html' => $card_list_html,
            ), true);

        if($panel_data !== null){
            $this->_panel_list[$panel_data['sorting_list_id']] = $html;
        } else {
            $this->_panel_list[] = $html;
        }

        return $this;
    }



    private function addCard($panel_data, $card_data){
        $html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ProcessView.Elements.PCard.PCard'),
            array(
                'extension_copy' => $this->_extension_copy,
                'fields_group' => $this->_fields_group,
                'fields_view' => $this->_fields_view,
                'panel_data' => $panel_data,
                'card_data' => $card_data,
                'block_field_name_replace' => $this->_block_field_name_replace,
                'js_content_reload_add_vars' => $this->_js_content_reload_add_vars,
            ), true);


        $this->_card_list[$panel_data['sorting_list_id']][$card_data['sorting_cards_id']] = $html;

        return $this;
    }




    public function getPanelList($append_cards = false){
        if($append_cards){
            if(empty($this->_card_list)){
                return $this->_panel_list;
            }
            $panels = array();
            foreach($this->_panel_list as $sorting_list_id => $html){
                $panels[$sorting_list_id] = array(
                    'html' => $html,
                    'cards' => $this->getCards($sorting_list_id),
                );
            }
            return $panels;
        } else {
            return $this->_panel_list;
        }
    }

    public function getCardList(){
        return $this->_card_list;
    }


    private function getCards($panel_sorting_list_id){
        if(empty($this->_card_list[$panel_sorting_list_id])) return;

        $cards_list = $this->_card_list[$panel_sorting_list_id];

        return $cards_list;
    }


    /**
     * если включен параметр "показ блоков", подменяем индекс блока на его название
     */
    public function setBlockFieldData() {
        if(!$this->_extension_copy->isShowAllBlocks()) {
            $block_field_data = $this->_extension_copy->getFieldBlockData();
            if(isset($block_field_data['name']))
                $this->_block_field_name_replace = $block_field_data['name'];
        }
        return $this;
    }





    public function prepare(){
        // порядок вызова всех методов не менять!

        $this->initProcessViewModel();

        ProcessViewSortingListModel::getInstance(true)->setGlobalVars([
            '_extension_copy' => $this->_extension_copy,
            '_pci' => $this->_pci,
            '_pdi' => $this->_pdi,
            '_finished_object' => $this->_finished_object,
            '_this_template' => $this->_this_template,
        ]);


        $this->prepareFieldsGroup();
        $this->prepareFieldsView();

        $this->preparePanels();

        return $this;
    }





    private function initProcessViewModel(){
        if(ProcessViewModel::isInit()) return;

        ProcessViewModel::getInstance()
            ->setExtensionCopy($this->_extension_copy)
            ->setPci($this->_pci)
            ->setPdi($this->_pdi)
            ->setThisTemplate($this->_this_template)
            ->setFinishedObject($this->_finished_object);
    }





    public function getFieldNameAs($field_name, $return_array = true){
        $params = $this->_extension_copy->getFieldSchemaParams($field_name);
        $field_list = [];

        switch($params['params']['type']){
            case \Fields::MFT_RELATE_PARTICIPANT:
                $field_list[$field_name] = 'participant_ug_id';
                break;

            case \Fields::MFT_RELATE:
            case \Fields::MFT_RELATE_DINAMIC:
            case \Fields::MFT_RELATE_THIS:
                $field_name_as = (new \DataModel())
                    ->setExtensionCopy($this->_extension_copy)
                    ->getRealFieldName($field_name);

                $field_list[$field_name] = $field_name_as;
                break;

            default:
                $field_list[$field_name] = $field_name;
        }

        if($return_array == false){
            return $field_list[array_keys($field_list)[0]];
        }

        return $field_list;
    }



    /**
     * getFieldsGroup - список полей сортировка (группировки)
     * @param bool $get_as_field_name
     * @return array|null|void
     */
    public function getFieldsGroup($get_as_field_name = false){
        $fields_group = null;
        $check_field_list = true;

        // MODULE_TASKS, pci, pdi
        if($this->_extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS && $this->_pci && $this->_pdi){
            $fields_group = array('todo_list' => 'todo_list');
        } else {
            // others modules
            if($this->_fields_group !== null){
                $field_name_list = $this->_fields_group;
                $check_field_list = false;
            } else {
                $field_name_list = Sorting::getInstance()->getParamFieldName();
            }

            if($field_name_list == false){
                $field_name_list = \History::getInstance()->getUserStorage(
                    UsersStorageModel::TYPE_PV_SORTING_PANEL,
                    $this->_extension_copy->copy_id . '_' . ProcessViewModel::getInstance()->getGroupData(),
                    $this->_pci,
                    $this->_pdi
                );
            }

            if($check_field_list){
                $this->checkFieldsGroupAndCorrect($field_name_list);
            }

            if(empty($field_name_list)){
                return;
            }

            if($get_as_field_name == false){
                $fields_group = $field_name_list;
            } else {
                $fields_group = [];
                foreach($field_name_list as $field_name){
                    $field_name_as = $this->getFieldNameAs($field_name);
                    $fields_group = array_merge($fields_group, $field_name_as);
                }
            }
        }

        return $fields_group;
    }


    /**
     * checkFieldsGroupAndCorrect - спроверка полей на допустимость в списке сортровки.
     * Если все поля недопустимы, или список пустой - берем первое поле из списка допустимых
     */
    private function checkFieldsGroupAndCorrect(&$fields_group_list){
        $changed = false;

        $fields_group_def_list = $this->getFieldsGroupList();
        if($fields_group_def_list == false){
            $fields_group_list = null;
            return true;
        }

        if($fields_group_list){
            foreach($fields_group_list as $key => $field_name){
                $b = false;
                foreach($fields_group_def_list as $field_def){
                    if($field_def['name'] == $field_name){
                        $b = true;
                        break;
                    }
                }

                if($b == false){
                    unset($fields_group_list[$key]);
                    $changed = true;
                }
            }
        }

        if(empty($fields_group_list)){
            $fields_group_list[] = $fields_group_def_list[0]['name'];
            $changed = true;
        }

        return $changed;
    }




    /**
     * prepareFieldsGroup
     */
    public function prepareFieldsGroup(){
        if($this->_fields_group === null){
            $this->_fields_group = $this->getFieldsGroup(false);
        }

        return $this;
    }



    /**
     * prepareFieldsView
     */
    private function prepareFieldsView(){
        $this->_fields_view = $this->getActiveFieldsView();
        return $this;
    }






    /**
     * setPanelDataDefault - Подготавливает данные для панели по дефолту - если нет ни одной панели
     */
    private function setPanelDataDefault(){
        $panel_data = array(
            'sorting_list_id' => null,
            'unique_index' => DataValueModel::generateUniqueIndex(),
            'mirror' => null,
        );

        $fields_group  = array_flip($this->getFieldsGroup(true));
        foreach($fields_group as &$value){
            $value = null;
        }
        $panel_data['fields_data'] = json_encode($fields_group);

        $this->_panel_data = array($panel_data);
    }



    /**
     * checkAddPanelDataDefault - проверка на добавление пустой панели при отсутствии сущностей
     * @return bool
     */
    private function checkAddZeroPanel(){
        return Yii::app()->controller->module->checkProcessViewAddZeroPanel();
    }


    /**
     * preparePanels - обрабатываем данные и формируем панели данних
     */
    private function preparePanels(){
        if(empty($this->_panel_data) && $this->_process_view_load_panels == false && $this->checkAddZeroPanel()){
            $this->setPanelDataDefault();
            $this->preparePanels();
            return $this;
        }

        if(!empty($this->_panel_data)){
            $panel_data_new = [];

            foreach($this->_panel_data as $panel_data){
                if($panel_data['sorting_list_id']){
                    $this->prepareCards($panel_data);
                    $this->addPanel($panel_data);
                } else{
                    $panel_data_new[] = $panel_data;
                }
            }

            if($panel_data_new){
                $index_start = 0;
                $index_finish = 0;

                // prepare new data
                foreach($panel_data_new as $panel_data){
                    $b = ProcessViewSortingListModel::getInstance()
                        ->insertPrepareValueAndAdd($panel_data)
                        ->insertAllToDB(100);
                    if($b){
                        $this->addSortingIdToNewModuleData($panel_data_new, ProcessViewSortingListModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_list_id');
                        $index_start = $index_finish+1;
                    }

                    $index_finish++;
                }

                $index_finish--;

                if(ProcessViewSortingListModel::getInstance()->beInsertValues()){
                    ProcessViewSortingListModel::getInstance()->insertAllToDB();
                    $this->addSortingIdToNewModuleData($panel_data_new, ProcessViewSortingListModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_list_id');
                }

                // prepare cards and panels for new data
                foreach($panel_data_new as $panel_data){
                    $this->prepareCards($panel_data);
                    $this->addPanel($panel_data);
                }

                $this->insertNewIdsToProcessViewTodoList($panel_data_new);
            }
        }

        return $this;
    }



    /**
     * addSortingIdToNewModuleData - добавляет к массиву данных sorting_id новой записи
     * @param $panel_data
     * @param $sorting_id_start
     * @param $index_start
     * @param $index_finish
     */
    private function addSortingIdToNewModuleData(&$entity_data, $sorting_list_id_start, $index_start, $index_finish, $pk_field_name){
        $sorting_list_id = $sorting_list_id_start;

        for($i = $index_start; $i <= $index_finish; $i++){
            $entity_data[$i][$pk_field_name] = $sorting_list_id;
            $sorting_list_id++;
        }
    }




    /**
     * insertNewIdsToProcessViewTodoList - вставка данных о ТОДО листах в связующую таблицу
     * @param $panel_data_list
     */
    private function insertNewIdsToProcessViewTodoList($panel_data_list){
        if($panel_data_list == false || $this->_extension_copy->copy_id != \ExtensionCopyModel::MODULE_TASKS || $this->_pci == false || $this->_pdi == false){
            return;
        }

        $insert_attr = [];

        foreach($panel_data_list as $panel_data){
            if(empty($panel_data['sorting_list_id']) || empty($panel_data['fields_data'])){
                continue;
            }

            $fields_data = json_decode($panel_data['fields_data'], true);

            if(empty($fields_data['todo_list'])){
                continue;
            }

            $insert_attr[] = [
                'todo_list_id' => $fields_data['todo_list'],
                'sorting_list_id' => $panel_data['sorting_list_id'],
            ];
        }

        if($insert_attr){
            (new ProcessViewTodoListModel())->insertMulti($insert_attr);
        }

    }




    /**
     * prepareCards - обрабатываем данные и формируем карточки
     */
    private function prepareCards($panel_data){
        if($this->_load_cards == false) return;
        if($panel_data == false) return;

        $card_data_list = $this->getCardDataList($panel_data);
        $cards_data_new = [];

        if(!empty($card_data_list)){

            \ProcessViewSortingCardsModel::getInstance(true)
                ->setGlobalVars([
                    '_extension_copy' => $this->_extension_copy,
                    '_sorting_list_id' => $panel_data['sorting_list_id'],
                ]);



            foreach($card_data_list as $card_data){
                if($card_data['sorting_cards_data_id'] && ($card_data['cards_sorting_sorting_list_id'] == false || $card_data['cards_sorting_sorting_list_id'] == $panel_data['sorting_list_id'])){
                    $this->addCard($panel_data, $card_data);
                } else {
                    $cards_data_new[] = $card_data;
                }
            }

            if($cards_data_new){
                $index_start = 0;
                $index_finish = 0;

                // prepare new data
                foreach($cards_data_new as $card_data){
                    $b = ProcessViewSortingCardsModel::getInstance()
                        ->insertPrepareValueAndAdd($card_data)
                        ->insertAllToDB(100);
                    if($b){
                        $this->addSortingIdToNewModuleData($cards_data_new, ProcessViewSortingCardsModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_cards_id');
                        $index_start = $index_finish+1;
                    }

                    $index_finish++;
                }

                $index_finish--;

                if(ProcessViewSortingCardsModel::getInstance()->beInsertValues()){
                    ProcessViewSortingCardsModel::getInstance()->insertAllToDB();
                    $this->addSortingIdToNewModuleData($cards_data_new, ProcessViewSortingCardsModel::getInstance()->getLastInsertId(), $index_start, $index_finish, 'sorting_cards_id');
                }

                // prepare cards and panels for new data
                foreach($cards_data_new as $card_data){
                    $this->addCard($panel_data, $card_data);
                }
            }
        }

        // очистка пустых списков
        if($cards_data_new){
            $data_id_list = CHtml::listData($cards_data_new, null, $this->_extension_copy->getPkFieldName());
            ProcessViewSortingListModel::getInstance()->flushCardsEntities($data_id_list);
        }

        return $this;
    }





    /**
     *   Возвращает данные карточки модуля
     */
    public function getCardDataList($panel_data){
        $data_model = $this->getCardDataModel($panel_data);

        if($data_model == false){
            return array();
        }

        return $data_model->findAll();
    }




    /**
     *   Возвращает данные карточки модуля
     */
    public function getCardDataModel($panel_data){
        $filter_controller = null;

        if(Yii::app()->controller){
            list($filter_controller) = Yii::app()->createController($this->_extension_copy->extension->name . '/ListViewFilter');
        }

        $only_id = DataValueModel::getInstance()->getIdOnTheGroundParent($this->_extension_copy->copy_id, $this->_pci, $this->_pdi);
        if($only_id === false){
            return;
        }

        $where_condition = array();
        $where_params = array();

        $search = new Search();
        $search->setTextFromUrl();

        $filters = new Filters();
        $filters->setTextFromUrl();
        $there_is_participant = false;
        if(!$filters->isTextEmpty()){
            $filter_data = $filter_controller->getParamsToQuery($this->_extension_copy, $filters->getText());
            if($filter_data){
                $there_is_participant = $filter_data['filter_params']['there_is_participant'];
            }
        }

        $data_model = new DataModel();
        $data_model
            ->setExtensionCopy($this->_extension_copy)
            ->setFromModuleTables();

        if(Yii::app()->controller && $ats = Yii::app()->controller->module->getProcessViewCardDataListAppendToSelect()){
            $data_model->addSelect($ats);
        }

        // data_id_list - только опеределенные карточки (по id)
        if($this->_data_id_list){
            //$panel_data['mirror'] = null;

            $this->_data_id_list = (array)$this->_data_id_list;
            $this->_data_id_list = array_unique($this->_data_id_list);
            $data_model->andWhere(array('IN', $this->_extension_copy->getPkFieldName(true), $this->_data_id_list));
        }


        //set "is_bpm_operation = 0"
        if($this->_with_out_bpm_operation === true){
            $data_model->andWhere(array('AND', '(' . $this->_extension_copy->getTableName() . '.is_bpm_operation is NULL OR ' . $this->_extension_copy->getTableName() . '.is_bpm_operation = "0")'));
        }

        // Добавляет условие отбора where как новый уровень. _last_condition может устанавливаться дополнительно в классе..
        if($this->_last_condition !== null){
            //было addGlobalCondition вместо andWhere
            $data_model->andWhere($this->_extension_copy->getTableName().'.'.$this->_last_condition, $this->_last_params);///////
        }

        //responsible
        if($this->_extension_copy->isResponsible())
            $data_model->setFromResponsible($there_is_participant);

        //participant
        if($this->_extension_copy->isParticipant())
            $data_model->setFromParticipant($there_is_participant);


        $fields_data = null;
        if(!empty($panel_data['fields_data'])){
            $fields_data = json_decode($panel_data['fields_data'], true);
        }

        if($fields_data){
            foreach($fields_data as $field_name => $field_value){
                $condition = $field_name . '=:' . $field_name . '_finish';
                if(is_null($field_value) || $field_value === ''){
                    $condition = $field_name . '=:' . $field_name . '_finish' . ' OR ' . $field_name . ' is NULL ';
                }
                if(!empty($where_condition)){
                    $where_condition[] = $condition;
                } else{
                    $where_condition = array('AND', $condition);
                }
                $where_params[':' . $field_name . '_finish'] = $field_value;

            }
        }
        //filters
        if(!empty($filter_data)){
            if(!empty($filter_data['conditions'])) {
                $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
            }
            if(!empty($filter_data['having']))
            {
                $having_array=[];
                foreach($filter_data['having'] as $having_element)
                {
                    $having_array[]=$having_element['query'];
                }
                $data_model->setHaving(implode(' AND ',$having_array),$filter_data['params']);
            }
        }

        if($this->_extension_copy->finished_object){
            if($filter_controller){
                if(Yii::app()->request && Yii::app()->request->getParam('finished_object')){
                    $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, array(FilterVirtualModel::VF_FINISHED_OBJECT), array(FilterVirtualModel::VF_FINISHED_OBJECT => array('corresponds' => 'corresponds')));
                } else{
                    $filter_data_2 = $filter_controller->getParamsToQuery($this->_extension_copy, array(FilterVirtualModel::VF_FINISHED_OBJECT), array(FilterVirtualModel::VF_FINISHED_OBJECT => array('corresponds' => 'corresponds_not')));
                }
            }
            if(!empty($filter_data_2))
                $data_model->andWhere($filter_data_2['conditions'], $filter_data_2['params']);
        }

        //this_template
        if(Yii::app()->controller && Yii::app()->controller->module->isTemplate($this->_extension_copy)){
            if($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE)
                $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE.'" '));
            else if($this->_this_template == EditViewModel::THIS_TEMPLATE_MODULE)
                if($this->_pci)
                    $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_TEMPLATE_CM . '" OR ' . $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'));
                else
                    $data_model->andWhere(array('AND', $this->_extension_copy->getTableName() . '.this_template = "'.EditViewModel::THIS_TEMPLATE_MODULE.'" OR ' . $this->_extension_copy->getTableName() . '.this_template is null'));
        }

        //order
        $data_model->setOrder($data_model->getOrderFromSortingParams());

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup()
            ->prepare();

        //parent module
        if(!empty($only_id)){
            $data_model->setParentModule($only_id);
        }

        //search
        if($search::$text !== null){
            $data_model->setSearch($data_model->getQueryWhereForSearch(Search::$text));
        }

        $data_model->setSelectNew();
        if(!empty($where_condition)){
            $data_model->setWhere($where_condition, $where_params);
        }


        //participant only
        if(Yii::app()->controller && Yii::app()->controller->module->dataIfParticipant() && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())){
            if($this->_pci == false && $this->_pdi == false){
                $data_model->setOtherPartisipantAllowed($this->_extension_copy->copy_id);
            }
        }


        if(Yii::app()->controller && Yii::app()->controller->module->dataIfParticipant() == false && ($this->_extension_copy->isParticipant() || $this->_extension_copy->isResponsible())){
            if($this->_pci == false && $this->_pdi == false){
                $data_model->setDataBasedParentModule($this->_extension_copy->copy_id);
            }
        }

        $data_model->withOutRelateTitleTemplate($this->_pdi);

        //order (group)
        //$fields_group_after_as = $data_model->getComparedWithFields(Sorting::getInstance()->getParamFieldName(), 'value');
        $fields_group_after_as = $data_model->getComparedWithFields($this->_fields_group, 'value');
        if(empty($fields_group_after_as)) return;

        //unique index
        $data_model
            ->setUniqueIndex($this->getFieldsGroup(true))
            ->setProcessViewCardQuery([
                'fields_group_after_as' => $fields_group_after_as,
                'panel_data' => $panel_data,
                'data_id_list' => $this->_data_id_list,
                'users_id' => \WebUser::getUserId(),
                'pci' => $this->_pci,
                'pdi' => $this->_pdi,
                'group_data' => ProcessViewSortingListModel::getInstance()->getGroupData(),
            ]);

        return $data_model;
    }












    /**
     *  удаляем из истории данные о групировке полей
     */
    private function deleteFieldsViewFromStorage(){
        $fields_view = \History::getInstance()->getUserStorage(
            UsersStorageModel::TYPE_PV_SECOND_FIELDS,
            $this->_process_view_index . '_' . ProcessViewModel::getInstance()->getGroupData(),
            $this->_pci,
            null
        );

        if($fields_view){
            $fields_group = $this->getFieldsGroupStr();
            if($fields_group && array_key_exists($fields_group, $fields_view)){
                unset($fields_view[$fields_group]);
            }

            if($fields_view){
                ProcessViewModel::getInstance()->saveSecondFieldView($this->_process_view_index . '_' . ProcessViewModel::getInstance()->getGroupData(), $fields_view);
            } else {
                \History::getInstance()->deleteFromUserStorage(
                    UsersStorageModel::TYPE_PV_SECOND_FIELDS,
                    $this->_process_view_index . '_' . ProcessViewModel::getInstance()->getGroupData(),
                    $this->_pci,
                    null
                );
            }
        }
    }



    /**
     * возвращает первую группу полей в схеме
     */
    private function getDefaultFieldsView(){
        $field_names = array();
        $schema_parse = $this->_extension_copy->getSchemaParse();

        if(isset($schema_parse['elements']))
            $header_list = SchemaConcatFields::getInstance()
                ->setSchema($schema_parse['elements'])
                ->setWithoutFieldsForProcessViewSecond()
                ->parsing()
                ->prepareWithOutDeniedRelateCopyId()
                ->prepareWithConcatName()
                ->getResult();

        if(isset($header_list['header']) && !empty($header_list['header'])){
            foreach($header_list['header'] as $value){
                if($value['name'] == implode(',', $this->_fields_group)) continue;
                $field_names[] = $value['name'];
                break;
            }
        }
        if(empty($field_names)) return;

        return $field_names;
    }




    private function isSetFieldNameInSchema($field_names){
        if($field_names == false) return false;

        if(is_string($field_names)){
            $field_names = explode(',',$field_names);
        }

        $in_schema = $this->_extension_copy->isSetFieldInSchema($field_names);

        return $in_schema;
    }


    private function getActiveFieldsView(){
        $fields_view = \History::getInstance()->getUserStorage(
            UsersStorageModel::TYPE_PV_SECOND_FIELDS,
            $this->_process_view_index . '_' . ProcessViewModel::getInstance()->getGroupData(),
            $this->_pci,
            null
        );

        if($fields_view){
            $fields_group = $this->getFieldsGroupStr();
            if($fields_group && array_key_exists($fields_group, $fields_view)){
                $fields_view = $fields_view[$fields_group];
            } else {
                $fields_view = $this->getDefaultFieldsView();
                ProcessViewModel::getInstance()->saveSecondFieldView($this->_process_view_index, $fields_view);
            }

            if(!empty($fields_view) && $this->isSetFieldNameInSchema($fields_view) == false){
                $this->deleteFieldsViewFromStorage();

                $fields_view = $this->getDefaultFieldsView();
                ProcessViewModel::getInstance()->saveSecondFieldView($this->_process_view_index, $fields_view);
            }
        } else {
            $fields_view = $this->getDefaultFieldsView();
        }

        return (array)$fields_view;
    }







    public function getFieldsViewList(){
        $result = array();
        $schema_parse = $this->_extension_copy->getSchemaParse();

        if(isset($schema_parse['elements']))
            $params = SchemaConcatFields::getInstance()
                ->setSchema($schema_parse['elements'])
                ->setWithoutFieldsForProcessViewSecond()
                ->parsing()
                ->prepareWithOutDeniedRelateCopyId()
                ->prepareWithConcatName()
                ->getResult();

        $isset_active = false;
        if(isset($params['header']) && !empty($params['header'])){
            foreach($params['header'] as $value){
                if($value['name'] == implode(',', $this->_fields_group)) continue;
                $result[] = array(
                    'value' => $value['name'],
                    'title' => $value['title'],
                    'active' => ($value['name'] == $this->getFieldsView(true) ? true : false),
                );

                if($result[count($result)-1]['active']){
                    $isset_active = true;
                }
            }
        }

        if($isset_active == false && $result){
            $this->_fields_view = explode(',', $result[0]['value']);
            $result[0]['active'] = true;
        }

        return $result;
    }






    public function getFieldsGroupList($schema_parse = null){
        if($this->_this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE){
            $exception_params_list = array('type'=>'relate_dinamic');
        } else {
            $exception_params_list = array('type'=>'module');
        }

        if($schema_parse === null){
            $schema_parse = $this->_extension_copy->getSchemaParse(array(), $exception_params_list, array(), false);
        }

        if(Yii::app()->controller){
            Yii::app()->controller->module->correctProcessViewSchemaForFieldGroupList($schema_parse);
        }


        $sort_list = array();

        if(isset($schema_parse['elements'])){
            $params = SchemaConcatFields::getInstance()
                ->setSchema($schema_parse['elements'])
                ->setWithoutFieldsForProcessViewGroup()
                ->parsing()
                ->prepareWithOutDeniedRelateCopyId()
                ->prepareWithConcatName()
                ->getResult();

            if(isset($params['header']) && !empty($params['header'])){
                foreach($params['header'] as $header){
                    $field_names = explode(',', $header['name']);
                    foreach($field_names as $field_name){
                        if($params['params'][$field_name]['process_view_group'] == false){
                            continue 2;
                        }
                        if($params['params'][$field_name]['type'] == 'relate_participant' && $params['params'][$field_name]['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT){
                            $header['title'] = Yii::t('base', 'Responsible');
                        }
                    }

                    $header['active'] = false;
                    if($this->isActiveFieldsGroup($header['name'])){
                        $header['active'] = true;
                    }
                    $sort_list[] = $header;
                }
            }

        }

        return $sort_list;
    }




}
