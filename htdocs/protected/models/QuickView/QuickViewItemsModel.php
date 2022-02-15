<?php


abstract class QuickViewItemsModel implements QuickViewItemsModelInterface {

    protected $limit  = 20;
    protected $offset = 1;


    protected $_block_model;
    protected $_data_model_list;
    protected $_there_id_data = false;



    public function setLimit($limit){
        $this->limit = $limit;
        return $this;
    }


    public function getLimit(){
        return $this->limit;
    }


    public function setOffset($offset){
        $this->offset = $offset;
        return $this;
    }


    public function getOffset(){
        return $this->offset;
    }


    public function setBlockModel($block_model){
        $this->_block_model = $block_model;
        return $this;
    }


    public function getBlockModel(){
        return $this->_block_model;
    }


    public function isDataModelList(){
        return (bool)$this->_data_model_list;
    }


    public function getDataModelList(){
        return $this->_data_model_list;
    }

    public function getThereIsData(){
        return $this->_there_id_data;
    }


    public function prepareThereIsData(){
        if($this->isDataModelList() == false){
            return $this;
        }

        $rows = Pagination::$item_сount;

        $find_rows = (integer)$this->getLimit() + $this->getOffset() - 1;

        if($find_rows >= $rows){
            $this->_there_id_data = false;
        } else {
            $this->_there_id_data = true;
        }

        return $this;
    }

}
