<?php

/**
 * Class QuickViewModel - Основной класс панели (бокового блока) быстого отображения и запуска сущностей
 */


class QuickViewModel {

    static private $_instance;

    private $_enable = false;

    private $_block_model_list = [];



    private function __construct(){}
    private function __clone(){}


    static public function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
            self::$_instance->init();
        }

        return self::$_instance;
    }


    private function init(){
        $this->prepareBlockModelList();

        return $this;
    }



    public function getEnable(){
        return $this->_enable;
    }


    public function getBlockModelList(){
        return $this->_block_model_list;
    }



    private function getBlockModelClassNameList(){
        return [
            'QuickViewBlockCommunicationsModel',
            'QuickViewBlockCallsModel',
        ];
    }



    private function prepareBlockModelList(){
        $block_class_name_list = $this->getBlockModelClassNameList();

        foreach($block_class_name_list as $class_name){
            $block_model = (new $class_name());

            if($block_model->getEnable() == false){
                continue;
            }

            $this->_block_model_list[$block_model->getName()] = $block_model;
        }

        if($this->_block_model_list){
            $this->_enable = true;
        }

        if($this->_block_model_list && count($this->_block_model_list) === 1){
            $this->_block_model_list[array_keys($this->_block_model_list)[0]]->setVisible(true);
        }

        return $this;
    }




    public function prepareDataItemsModelList($only_visible = true){
        foreach($this->_block_model_list as $block_model){
            if($only_visible){
                if($block_model->getVisible()){
                    $block_model->getItemsModel()->prepareDataModelList();
                }
                continue;
            }

            $block_model->getItemsModel()->prepareDataModelList();
        }

        return $this;
    }


    public function hasBlockModel($block_name){
        return array_key_exists($block_name, $this->_block_model_list);
    }



    public function getBlockModelByName($block_name){
        if($this->hasBlockModel($block_name)){
            return $this->_block_model_list[$block_name];
        }
    }


    /**
     * getBlockModelListJs - Возвращает подготовленный список блоков моделей для JS
     */
    public function getBlockModelListJs(){
        $result = [];

        $block_model_list = $this->getBlockModelList();
        if($block_model_list == false){
            return $result;
        }

        foreach($block_model_list as $block_name => $block_model){
            $html_block = (new QuickViewBuilder())
                                ->setQuickViewBlockModel($block_model)
                                ->prepare(QuickViewBuilder::VIEW_BLOCK)
                                ->getResult();

            $block_result = [
                'copy_id'=> $block_model->getCopyId(),
                'visible' => $block_model->getVisible(),
                'name' => $block_model->getName(),
                'block_group' => $block_model->getBlockGroupName(),
                'js_class_name' => $block_model->getJsClassName(),
                'is_data' => $block_model->getItemsModel()->isDataModelList(),
                'there_is_data' => $block_model->getItemsModel()->getThereIsData(),
                'html' => [
                    'block' => $html_block,
                ],
            ];

            $result[$block_model->getName()] = $block_result;
        }


        return $result;
    }



    public function getBlockModelListByGroupName($block_group_name){
        $result = [];

        if($this->_block_model_list == false){
            return $result;
        }

        foreach($this->_block_model_list as $block_model){
            if($block_model->getBlockGroupName() == $block_group_name){
                $result[$block_model->getName()] = $block_model;
            }

        }

        return $result;
    }





    public function countBlockGroupName($block_group_name){
        $count = 0;
        if($this->_block_model_list == false){
            return $count;
        }

        foreach($this->_block_model_list as $block_model){
            if($block_model->getBlockGroupName() == $block_group_name){
                $count++;
            }
        }

        return $count;
    }










/*


QuickViewModel
    _enable = false


    QuickViewBlockCommunicationsModel
        _items_model
        QuickViewItemsCommunicationsModel
    QuickViewBlockCallModel
        QuickViewItemsCallsModel
*/



}
