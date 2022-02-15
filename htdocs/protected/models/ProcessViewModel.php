<?php
/**
 * ProcessViewModel
 * @autor Alex R.
 */

class ProcessViewModel{


    // GROUP DATA
    const GROUP_DATA_GENERAL                    = 'general';
    const GROUP_DATA_FINISHED_OBJECT            = 'finished_object';
    const GROUP_DATA_FINISHED_OBJECT_TEMPLATE   = 'finished_object_template';
    const GROUP_DATA_TEMPLATE                   = 'template';


    private $_extension_copy;
    private $_pci;
    private $_pdi;
    private $_this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    private $_finished_object;

    private $_group_data;

    private $_status = true;
    private $_validate;


    private static $_instance;


    private function __construct(){}


    public static function getInstance($refresh = false){
        if(self::$_instance === null || $refresh){
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function isInit(){
        return (bool)self::$_instance;
    }


    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
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


    public function setFinishedObject($finished_object){
        $this->_finished_object = $finished_object;
        return $this;
    }



    private function setStatus($status){
        $this->_status = $status;
        return $this;
    }


    private function getStatus(){
        return $this->_status;
    }


    public function setInitVars($init_vars){
        $this->_init_vars = $init_vars;
        return $this;
    }


    public function getResult(){
        $result = [
                'status' => $this->getStatus(),
            ];

        if($this->isSetMessages()){
            $result['messages'] = $this->getMessages();
        }

        return $result;
    }



    private function setValidate(){
        if($this->isSetValidate() == false){
            $this->_validate = new Validate();
        }
    }


    private function isSetValidate(){
        return (bool)$this->_validate;
    }


    private function addMessages($message, $status = false){
        $this->setValidate();
        $this->setStatus($status);
        $this->_validate->addValidateResult('e', Yii::t('messages', $message));

        return $this;
    }


    private function getMessages(){
        if($this->isSetMessages() == false) return;
        $this->_validate->getValidateResultHtml();
    }



    private function isSetMessages(){
        if($this->isSetValidate()){
            return $this->_validate->beMessages(Validate::TM_ERROR);
        }

        return false;
    }


    private function prepareGroupData(){
        $i = 0;
        if($this->_finished_object){
            $i+=1;
        }
        if($this->_this_template){
            $i+=3;
        }

        switch($i){
            case 0:
                $this->_group_data = self::GROUP_DATA_GENERAL;
                break;
            case 1:
                $this->_group_data = self::GROUP_DATA_FINISHED_OBJECT;
                break;
            case 3:
                $this->_group_data = self::GROUP_DATA_TEMPLATE;
                break;
            case 4:
                $this->_group_data = self::GROUP_DATA_FINISHED_OBJECT_TEMPLATE;
                break;
        }
    }


    public static function getGroupDataList(){
        return [
            self::GROUP_DATA_GENERAL,
            self::GROUP_DATA_FINISHED_OBJECT,
            self::GROUP_DATA_FINISHED_OBJECT_TEMPLATE,
            self::GROUP_DATA_TEMPLATE,
        ];
    }


    public function getGroupData($prepare = true){
        if($prepare && $this->_group_data === null){
            $this->prepareGroupData();
        }

        return $this->_group_data;
    }







   /**
     * saveSecondFieldView
     */
    public function saveSecondFieldView($index, $fields_view){
        if($index == false){
            return $this->addMessages('Not defined parameters');
        }

        $index.= '_' . $this->getGroupData();

        if($fields_view == false){
            $fields_view = '';
        }


        $process_view_builder = new ProcessViewBuilder();

        $process_view_builder
            ->setExtensionCopy($this->_extension_copy)
            ->setPci($this->_pci)
            ->setPdi($this->_pdi);
        $process_view_builder
            ->setFieldsGroup($process_view_builder->getFieldsGroup(false, true));

        $fields_group = $process_view_builder->getFieldsGroupStr();


        if($fields_group == false){
            return $this->addMessages('Not defined parameters');
        }


        $storage_value = (new History())->getUserStorage(UsersStorageModel::TYPE_PV_SECOND_FIELDS, $index, $this->_pci, null);

        if($storage_value){
            $storage_value[$fields_group] = $fields_view;
        } else {
            $storage_value = [
                $fields_group => $fields_view,
            ];
        }

        (new History())->setUserStorage(UsersStorageModel::TYPE_PV_SECOND_FIELDS, $index, $storage_value, false, $this->_pci, null);

        return $this;
    }


    /**
     * checkAddNewPanel - проверяет и возвращает статус на добавление
     * @param $vars
     * @return bool
     */
    public function checkAddNewPanel($vars){
        $sorting_list_data = (new \ProcessViewSortingListModel())->findByPk($vars['sorting_list_id']);
        if($sorting_list_data == false){
            return false;
        }

        \Yii::app()->controller->module->initPropertiesForProcessView([
            'pci' => $sorting_list_data['pci'],
            'pdi' => $sorting_list_data['pdi'],
        ]);


        if(\Yii::app()->controller->module->process_view_btn_add_panel == false){
            return true;
        }

        return false;
    }








}
