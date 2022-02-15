<?php


class ProfileActivityModel {
    
    private $_user_id;

    public function __construct(){
        $this->setUserId();
    }
    
    public static function getInstance(){
        return new self(); 
    }
    
    
    public function setUserId($user_id = null){
        if($user_id === null) 
            $this->_user_id = WebUser::getUserId();
        else 
            $this->_user_id = $user_id;
        return $this;
    }
    
    
    public function getData(){
        Pagination::$active_page_size = 10; // по умолчанию
        $pagination = new Pagination();
        $pagination
            ->setParamsFromUrl()
            ->setItemCount(
                HistoryModel::model()
                    ->active()
                    ->setScopeUserCreate($this->_user_id)
                    ->group()
                    ->count()
            );

        return History::getInstance()->getFromHistoryAll(
                        $this->_user_id,
                        $pagination->getCountPages(),
                        $pagination->getActivePageSize(),
                        $pagination->getOffset()
        );


    }

} 
