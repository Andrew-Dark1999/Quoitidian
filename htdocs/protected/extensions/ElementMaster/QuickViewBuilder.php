<?php


class QuickViewBuilder{


    const VIEW_BLOCK = 'block';
    const VIEW_ITEMS = 'items';


    private $_quick_view_block_model;
    private $_quick_view_items_model;

    private $_result;


    public function setQuickViewBlockModel($quick_model){
        $this->_quick_view_block_model = $quick_model;
        return $this;
    }

    public function setQuickViewItemsModel($quick_model){
        $this->_quick_view_items_model = $quick_model;
        return $this;
    }


    public function getResult(){
        return $this->_result;
    }


    public function prepare($view_name){
        switch($view_name){
            case static::VIEW_BLOCK:
                $this->prepareBlockHtml();
                break;
            case static::VIEW_ITEMS:
                $this->prepareItemsHtml();
                break;
        }

        return $this;
    }



    /**
     * prepareBlockHtml - Подготовка базового блока
     */
    private function prepareBlockHtml(){
        $content = null;
        if($this->_quick_view_block_model->getItemsModel()->getDataModelList()){
            $content = (new QuickViewBuilder())
                            ->setQuickViewItemsModel($this->_quick_view_block_model->getItemsModel())
                            ->prepare(QuickViewBuilder::VIEW_ITEMS)
                            ->getResult();
        }

        $widget_data = array(
            'quick_view_model' => $this->_quick_view_block_model,
            'content' => $content,
        );

        $widget_alias = $this->_quick_view_block_model->getWidgetAlias();

        $this->_result = $this->getHtml($widget_alias, self::VIEW_BLOCK, $widget_data);
    }



    /**
     * prepareItemsHtml - Подготовка сущностей блока
     */
    private function prepareItemsHtml(){
        $widget_data = array(
            'quick_view_model' => $this->_quick_view_items_model,
        );



        $widget_alias = $this->_quick_view_items_model->getBlockModel()->getWidgetAlias();

        $this->_result = $this->getHtml($widget_alias, self::VIEW_ITEMS, $widget_data);
    }



    /**
     * getHtml
     */
    private function getHtml($widget_alias, $view_name, $widget_data = []){
        $widget_data =
            array(
                'view' => $view_name,
            ) + $widget_data;

        return Yii::app()->controller->widget($widget_alias, $widget_data, true);
    }



}
