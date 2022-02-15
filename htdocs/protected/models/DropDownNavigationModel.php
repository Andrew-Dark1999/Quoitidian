<?php

/**
 * DropDownNavigationModel - нивагиционное меню по данных в модулях
 * @author Alex R.
 */
class DropDownNavigationModel {

    const MENU_TASK_PROJECT = 1;
    const MENU_PROCESS_BPM  = 2;

    private $_data;
    private $_vars;




    public static function getInstance(){
        return new self();
    }


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }


    public function getResult(){
        return array(
            'status' => true,
            'data' => $this->_data,
        );
    }


    public function prepare($menu){
        switch($menu){
            case self::MENU_TASK_PROJECT:
                $this->prepareDataTasksProject();
                break;
            case self::MENU_PROCESS_BPM:
                $this->prepareProcessBPM();
                break;
        }

        return $this;
    }



    /**
     * getCardDataParams
     */
    private function getCardDataParams(){
        $extension_copy = $this->_vars['extension_copy'];

        if(empty($extension_copy)) return false;

        $b_status = $extension_copy->getStatusField();

        $data_model = new DataModel();
        $data_model
            ->setFrom($extension_copy->getTableName())
            ->andWhere(array('AND', $extension_copy->prefix_name . '_id=:id'), array(':id'=>$this->_vars['id']));

        if($extension_copy->finished_object == true){
            $data_model->join($extension_copy->getTableName($b_status['params']['name']), $b_status['params']['name'] .'='. $b_status['params']['name'] . '_id', array(), 'left', false);
        }

        $data = $data_model->findRow();
        $data = $data;
        return array(
            'finished_object' => ($extension_copy->finished_object == false ? null : (boolean)$data[$b_status['params']['name'] . '_' . 'finished_object']),
            'this_template' => (boolean)$data['this_template'],
        );
    }







    /**
     * prepareDataTasksProject - список проектов для меню быстрого перехода
     */
    private function prepareDataTasksProject(){
        $extension_copy = $this->_vars['extension_copy'];

        $cdp = $this->getCardDataParams();
        $finished_object = ($cdp['finished_object'] === null ? false : true);

        $global_params = array(
            'pci' => \Yii::app()->request->getParam('pci', null),
            'pdi' => \Yii::app()->request->getParam('pdi', null),
            'finished_object' => (boolean)$cdp['finished_object'],
        );

        $sorting_params_old = Sorting::$params;
        Sorting::getInstance()->setParams(array('module_title' => 'a'), true);


        $this->_data = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($cdp['this_template'])
            ->setGlobalParams($global_params)
            ->setAppentCheckPciPdiIsEmpty(false)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            ->setGetAllData(true)
            ->setSortingParams(false)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();

        Sorting::$params  = $sorting_params_old;
    }



    /**
     * prepareProcessBPM - список ВРМ процессов для меню быстрого перехода
     */
    private function prepareProcessBPM(){
        $extension_copy = $this->_vars['extension_copy'];

        $global_params = array(
            'pci' => \Yii::app()->request->getParam('pci', null),
            'pdi' => \Yii::app()->request->getParam('pdi', null),
            'finished_object' => false,
        );

        $sorting_params_old = Sorting::$params;
        Sorting::getInstance()->setParams(array('module_title' => 'a'), true);

        $cdp = $this->getCardDataParams();

        $finished_object = ($cdp['finished_object'] === null ? false : true);
        if($finished_object && $cdp['this_template']){
            $finished_object = false;
        }

        $this->_data = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($cdp['this_template'])
            ->setGlobalParams($global_params)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            ->setGetAllData(true)
            ->setSortingParams(false)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();

        Sorting::$params  = $sorting_params_old;
    }


}
