<?php

class Pagination{
    
    public $page_sizes = array(10 => 10, 15 => 15, 20 => 20, 50 => 50); // 0 => 'All'

    /**
     * @var bool
     * тип пагинации, инпут или набор страниц
     */
    public static $type_input = true;

    // активная страница
    public static $active_page = 1;
    // количество строк на странице по умолчанию
    public static $active_page_size = 10;
    // количество записей в БД
    public static $item_сount = 0;
    // количество страниц
    public static $count_pages = 1;
    // указывает, что в GET параметре указана страница больше, чем есть в действительности
    public static $active_page_id_larger = false;


    public static function getInstance(){
        return new self(); 
    }


    public static function switchActivePageIdLarger($to_switch_value = false){
        $result = self::$active_page_id_larger;
        self::$active_page_id_larger = $to_switch_value;
        return $result;
    }

    public function setParamsFromUrl(){
        if(isset($_GET['page']) && !empty($_GET['page'])){
            self::$active_page = $_GET['page'];
        }

        if(isset($_GET['page_size'])){
            if($_GET['page_size'] === '0' || in_array($_GET['page_size'], $this->page_sizes))
                self::$active_page_size = $_GET['page_size'];
        }

        return $this;
    }
    
    public function setItemCount($item_сount = null){
        if($item_сount === null){
            $item_сount = (new \DataModel())->setSelectFoundRows()->findScalar();
        }

        self::$item_сount = $item_сount;
        $this->setCountPages();
        return $this;
    }
    
    public function setCountPages(){
        if(self::$item_сount == 0 || self::$active_page_size == 0) return;

        $count = ceil(self::$item_сount / self::$active_page_size);
        self::$count_pages = $count;

        if(self::$count_pages < self::$active_page){
            self::$active_page = self::$count_pages;
            $_GET['page'] = self::$active_page;
            if(self::$count_pages){
                self::$active_page_id_larger = true;
            }
        }

        return $this;        
    }

    public function getCountPages(){
        return self::$count_pages;        
    }

    public function getOffSet(){
        return (self::$active_page_size * (self::$active_page-1));
    }

    public function getFistNumberRecord(){
        $first = (self::$active_page_size * (self::$active_page-1)) + 1;
        //if($first > self::$item_сount) $first = self::$item_сount;
        return $first;       
    }

    public function getLastNumberRecord(){
        $last = self::$active_page_size * (self::$active_page);
        if($last > self::$item_сount || (integer)self::$active_page_size === 0) $last = self::$item_сount;
        return $last;        
    }

    public function getActivePageSize(){
        return self::$active_page_size;        
    }

  
    

    public function makePaginator($criteria = null){
        if($criteria === null)
            $criteria = new CDbCriteria();                            

        $pagination=new CPagination(self::$item_сount);
        $pagination->pageSize = self::$active_page_size;
        $pagination->applyLimit($criteria);
        return $pagination;
    }    
    

    public function getUrl($page_number = null){
        $base = explode('?', Yii::app()->request->getUrl());
        $query = array();
        
        $get = Yii::app()->request->getQueryString(); 
        if(!empty($get)){
            $get = explode('&', $get);   
            foreach($get as $value){
                if(!preg_match('/^page=(\d)+$/', $value)) $query[] = $value;
            }
        }
        if(!empty($page_number)) $query[] = 'page' . '='. $page_number;

        return $base[0] . (!empty($query) ? '?' . implode('&', $query) : '');
    }



    public function getNextBtn(){
        $params = array(
            'class' => 'disabled',
            'active_page' => '',
        );
        if(self::$active_page < self::$count_pages){
            $params = array(
                'class' => '',
                'active_page' => self::$active_page + 1,
            );
            return $params;
        }
        return $params;
    }
    
    
    private function getPreviousBtn(){
        $params = array(
            'class' => 'disabled',
            'active_page' => '',
        );
        
        if(self::$active_page > 1){
            $params = array(
                'class' => '',
                'active_page' => (self::$count_pages > 1 ?(self::$active_page > self::$count_pages ? self::$count_pages : self::$active_page - 1) : ''),
            );
            return $params;
        }
        return $params;
    } 
    
    
    public function getPagesBtn(){
        $pages = array();
        for($i=1; $i < self::$count_pages+1; $i++){
            $pages[$i] = array('title' => $i, 'active_page' => $i);
            if($i == self::$active_page){
                $pages[$i]['class'] = 'active';
                $pages[$i]['active_page'] = '';
            } 
        }
            
        return $pages;
    }
    
    public function getPaginatorView(){

        $params = array(
            'previous' => $this->getPreviousBtn(),
            'next' => $this->getNextBtn(),
            'pages' => $this->getPagesBtn(),
        );

        if(self::$type_input) {
            Yii::app()->controller->renderBlock('pagination.input', $params);
        } else {
            Yii::app()->controller->renderBlock('pagination', $params);
        }
    }
    


    public function getPaginatorSize(){
        $params = array(
            'page_sizes' => $this->page_sizes,
            'active_page_size' => self::$active_page_size,
        );
        
        Yii::app()->controller->renderBlock('pagination-size', $params);
    }


    public function getPaginatorReport(){
        return Yii::t('base', 'n==1#Showing {s1} to {s2} of {s3} entry|n>1#Showing {s1} to {s2} of {s3} entries', array(
                                                                                self::$item_сount,
                                                                                '{s1}'=>$this->getFistNumberRecord(),
                                                                                '{s2}'=>$this->getLastNumberRecord(),
                                                                                '{s3}'=>self::$item_сount,
                                                                            ));
        
        
    }
    
}
