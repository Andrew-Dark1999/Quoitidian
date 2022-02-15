<?php
/**
 * ListViewRowModel - подготовка данных для отображения строки list-view
 */


class ListViewRowModel{


    private $_extension_copy;
    private $_schema_params;
    private $_this_template;
    private $_finished_object;
    private $_parent_copy_id;
    private $_parent_data_id;
    private $_data;
    private $_without_group_index = array();



    private $_primary_link = ListViewBulder::PRIMARY_LINK_EDIT_VIEW;
    private $_entity_model;
    private $_html = '';



    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }


    public function setSchemaParams($schema_params){
        $this->_schema_params = $schema_params;
        return $this;
    }


    public function setData($data){
        $this->_data = $data;
        return $this;
    }

    public function setWithoutGroupIndex($without_group_index){
        $this->_without_group_index = $without_group_index;
        return $this;
    }

    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }

    public function setFinishedObject($finished_object){
        $this->_finished_object = $finished_object;
        return $this;
    }

    public function setParentCopyId($parent_copy_id){
        $this->_parent_copy_id = $parent_copy_id;
        return $this;
    }

    public function setParentDataId($parent_data_id){
        $this->_parent_data_id = $parent_data_id;
        return $this;
    }




    public function getPkValue(){
        return $this->_data[$this->_extension_copy->getPkFieldName()];
    }


    public function getEntityModel(){
        return $this->_entity_model;
    }


    public function getHtml(){
        return $this->_html;

    }


    private function preparePrimaryLink(){
        if(
            ($this->_extension_copy->copy_id == ExtensionCopyModel::MODULE_PARTICIPANT && $this->_extension_copy->isParticipant()) ||
            $this->_extension_copy->copy_id == ExtensionCopyModel::MODULE_USERS
        ){
            $this->_primary_link = ListViewBulder::PRIMARY_LINK_NONE_LINK;
        } else
        if($this->_extension_copy->copy_id == ExtensionCopyModel::MODULE_REPORTS){
            $this->_primary_link = ListViewBulder::PRIMARY_LINK_REPORTS_LIST_VIEW;
        } else
        if($this->_extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS){
            $this->_primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
        }

        return $this;
    }



    public function prepareHtmlRow(){
        $this
            ->preparePrimaryLink()
            ->prepareEntity()
            ->prepareHtml()
            ->addToEntityChildrenProperties()
            ->resetEntityToProperties();

        return $this;
    }


    public function prepareHtmlRowArray(){
        $this
            ->preparePrimaryLink()
            ->prepareArray();

        return $this;
    }




    private function prepareHtml(){
        $this->_html = ListViewBulder::getInstance($this->_extension_copy)
                            ->setThisTemplate($this->_this_template)
                            ->setFinishedObject($this->_finished_object)
                            ->buildListViewRow($this->_schema_params, $this->_data, $this->_without_group_index, $this->_primary_link);

        return $this;
    }



    private function prepareArray(){
        $this->_html = ListViewBulder::getInstance($this->_extension_copy)
                            ->setThisTemplate($this->_this_template)
                            ->setFinishedObject($this->_finished_object)
                            ->buildHtmlListView($this->_schema_params, $this->_data, $this->_primary_link);

        return $this;
    }






    //*****************************************************
    // ENTITY
    //*****************************************************




    /**
     * prepareEntity - установка параметров EntityModel для строки
     * @return $this
     */
    private function prepareEntity(){
        $vars = array(
            'copy_id' => $this->_extension_copy->copy_id,
            'id' => $this->getPkValue(),
            'this_template' => $this->_this_template,
            'finished_object' => $this->_finished_object,
        );

        $entity_vars_model = new \EntityVarsModel();

        $this->_entity_model = (new EntityModel(true))
                        ->setElementType(\EntityElementTypeModel::TYPE_LIST_VIEW)
                        ->setParentKey($entity_vars_model->getParentKey());
        $this->_entity_model
                        ->setLastParentKey($this->_entity_model->getKey())
                        ->setParentEventId($entity_vars_model->getParentEventId())
                        ->setVars($entity_vars_model->prepareModuleVars($vars)->getVars())
                        ->setEvents($this->getEntityEvents())
                        ->setCallbacks($this->getEntityCallbacks());


        return $this;
    }


    /**
     * getEntityEvents
     */
    protected function getEntityEvents(){
        return array();
    }


    /**
     * getEntityCallbacks
     */
    protected function getEntityCallbacks(){
        $callbacks = array();

        if(($event_id = $this->_entity_model->getParentEventId()) == false){
            return $callbacks;
        }

        switch($event_id){
            case EntityEventsModel::EID_EDIT_VIEW_SDM_ADD :
                $callbacks = array(
                    array('destroy', ['EditView', 'entity', 'reloadParentRelateIfExistValue']),
                );
                break;
        }

        return $callbacks;
    }




    private function addToEntityChildrenProperties(){
        $vars['children_properties'] =  $this->_entity_model->getEntityChildrenProperties();

        $this->_entity_model->addVars($vars);

        return $this;
    }



    /**
     * resetEntityToProperties
     */
    private function resetEntityToProperties(){
        $this->_entity_model->resetToEntityProperties();

        return $this;
    }










}
