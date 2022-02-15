<?php

class ProcessViewController extends ProcessView{

    /**
     *   Возвращает сгрупированные данные поля модуля
     */
    protected function getData($extension_copy){
        $global_params = array(
            'pci' => \Yii::app()->request->getParam('pci'),
            'pdi' => \Yii::app()->request->getParam('pdi'),
            'finished_object' => \Yii::app()->request->getParam('finished_object'),
            'data_id_list' => \Yii::app()->request->getParam('data_id_list'),
            'sorting_list_id' => \Yii::app()->request->getParam('sorting_list_id'),
        );

        $flush_empty_panels = true;
        if(\Yii::app()->request->getParam('process_view_load_panels')){
            $flush_empty_panels = false;
        }

        $data = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($this->module->finishedObject())
            ->setThisTemplate($this->this_template)
            ->setGlobalParams($global_params)
            ->setModule($this->module)
            ->setProcessViewFlushEmptyPanels($flush_empty_panels)
            ->setAppendToSelect('(select  max(date_create) from {{activity_messages}} where( copy_id = '.$extension_copy->copy_id.' and data_id = {{communications.communications_id}})) as activity_last_date')
            ->setLastCondition('user_create=:user_id',array(':user_id' => \WebUser::getUserId()))
            ->prepare(\DataListModel::TYPE_PROCESS_VIEW)
            ->getData();


        ProcessViewSortingListModel::getInstance(true)
            ->setGlobalVars([
                '_extension_copy' => $extension_copy,
                '_pci' => \Yii::app()->request->getParam('pci'),
                '_pdi' => \Yii::app()->request->getParam('pdi'),
                '_finished_object' => \Yii::app()->request->getParam('finished_object'),
                '_this_template' => $this->this_template,
            ]);

        if(\Yii::app()->request->getParam('process_view_load_panels') == false && empty($data)){
            ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
        }


        // возникает, если было изменено значени поля, по которому произошла сортировка
        /*
        if(\Yii::app()->request->getParam('process_view_load_panels') && empty($data)){
            ProcessViewSortingListModel::getInstance()->flushPanelEntities(false);
        }
        */


        return $data;
    }

}
