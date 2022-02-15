<?php



class FilterVirtualModel{
    
    // виртульные фильтры
    const VF_MY                 = '-1';
    const VF_FINISHED_OBJECT    = '-2';

    const ID_VF_MY              = '-1';
    const ID_VF_FINISHED_OBJECT = '-2';
    
    private $_extension_copy;
    
    private $_result_filters = array();
    private $_result_attr = array();
    
    // список названий виртульних фильтров
    public static $filters = array(
                            self::VF_MY => 'vfMy',                              // предустановненный фильтр "My""
                            self::VF_FINISHED_OBJECT => 'vfFinishedObject',     // фильтрация данных по параметру "Заверщенные обьекты"  
                        );




    // список атрибутов для отображения
    private static $filters_attr = array(
                            self::VF_MY => array('class_a' => 'my_filter', 'class_icon' => 'fa-heart'),
                            self::VF_FINISHED_OBJECT => array('class_a' => '', 'class_icon' => ''),
                        );

    
    
    
    public static function getInstance(){
        return new self();
    }

    

    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        
        return $this;
    }

    
    public static function isSetFilter($filter_id){
        return (in_array($filter_id, array_keys(self::$filters)) ? true : false);
    }



    public static function isShowFilter($filter_id, $copy_id){
        if(self::isSetFilter($filter_id) && $copy_id){

            $extension_copy = ExtensionCopyModel::model()->findByPk($copy_id);
            if($extension_copy){

                $filter = new FilterVirtualModel();
                $filter->setExtensionCopy($extension_copy);

                switch($filter_id){
                    case self::VF_MY:
                        return $filter->isVfMy();
                    break;
                    case self::VF_FINISHED_OBJECT:
                        return $filter->isVfFinishedObject();
                    break;
                }
            }

        }

        return false;
    }


    public function getResultFilters(){
        return $this->_result_filters; 
    }


    public function getResultAttr(){
        return $this->_result_attr; 
    }


    /**
     * добавляем виртульные фильтры
     */
    public function appendFiltes($filter_id_list, $params = null){
        
        if(!is_array($filter_id_list)) $filter_id_list = array($filter_id_list);
        $filters = array_keys(self::$filters);
        foreach($filter_id_list as $filter_id){
            if(in_array($filter_id, $filters) == false) continue;

            $param = (isset($params[$filter_id]) ? $params[$filter_id] : null);
            $result =  $this->{self::$filters[$filter_id]}($filter_id, $param);
            
            if(empty($result)) continue;
            
            $this->_result_filters[] = $result;
            $this->_result_attr[$filter_id] = self::$filters_attr[$filter_id];
        }
        return $this;
    }

    

    /**
     * слияние массива виртульних фильтров с предложенным массивом 
     */
    public function marge(&$callback){
        if(empty($this->_result_filters)) return $this;
        foreach($this->_result_filters as $filter){
            array_unshift($callback, $filter);
        }
        return $this;
    }


    
    /**
     * виртульный фильтр vfFinishedObject
     */    
    private function vfFinishedObject($filter_name, $params){
        $statues = $this->_extension_copy->getFieldSchemaParamsByType('select', null, false);
        if(empty($statues)) return;
        $status_params = null;
        foreach($statues as $status){
            if($status['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS)
                $status_params = $status;
        }
        if(empty($status_params)) return;
        $status_data = null;
        
        $data_model = new DataModel();
        $data_model
            ->setFrom($this->_extension_copy->getTableName($status_params['params']['name']))
            ->setWhere($status_params['params']['name'] . '_finished_object = "1"');
        $data_model = $data_model->findAll();

        if(empty($data_model)) return;
        
        $filter_model = new \FilterModel();
        $filter_model->setAttribute('filter_id', self::ID_VF_FINISHED_OBJECT); //0 use to check at frontend if it is my filter
        $filter_model->setAttribute('copy_id', $this->_extension_copy->copy_id);
        $filter_model->setAttribute('name', $filter_name);
        $filter_model->setAttribute('title', '');
        $filter_model->setAttribute('params', '[{"name":"'.$status_params['params']['name'].'","condition":"'.(isset($params['corresponds']) ? $params['corresponds'] : 'corresponds').'","condition_value":["'.$data_model[0][$status_params['params']['name'] . '_id'].'"]}]');

        return $filter_model;
    }
    


    
    
    /**
     * виртульный фильтр vfMy
     */
    private function vfMy($filter_name){
        if($this->isVfMy($name) == false) return;
        
        $filter_model = new \FilterModel();
        $filter_model->setAttribute('filter_id', self::ID_VF_MY);//0 use to check at frontend if it is my filter
        $filter_model->setAttribute('copy_id', $this->_extension_copy->copy_id);
        $filter_model->setAttribute('name', $filter_name);
        $filter_model->setAttribute('title', Yii::t('base', 'My'));
        $filter_model->setAttribute('params', '[{"name":"'.$name.'","condition":"corresponds_rp","condition_value":["' . Yii::app()->user->id . '","user"]}]');
        
        return $filter_model;
    }
    

    /**
     * проверка допустимости установки вирутального фильтра My
     */
    public function isVfMy(&$name = ''){
        if($this->_extension_copy) {
            $field = $this->_extension_copy->getParticipantField();
            if(!$field){
                $field = $this->_extension_copy->getResponsibleField();
            }

            if ($field) {
                $name = $field['params']['name'];
                return true;
            }
        }

        return false;
    }

    /**
     * проверка допустимости установки вирутального фильтра finished_object
     */
    public function isVfFinishedObject(){

        if($this->_extension_copy) {
            return  !empty($this->_extension_copy->finished_object);
        }

        return false;
    }


} 





