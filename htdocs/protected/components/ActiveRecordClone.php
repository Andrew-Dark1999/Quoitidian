<?php

/**
 * ActiveRecordClone - наследник CActiveRecord с возможностью создания клонов моделей
 */

class ActiveRecordClone extends ActiveRecord {

    public static $_models = array();
    public static $_md=array();				// class name => meta data


    public $_active_alias = null;
    public $_dinamic_params = array();


    public function __construct($scenario='insert'){
        $ca = func_num_args();
        if($ca == 3){
            $dp = func_get_arg(1);
            $al = func_get_arg(2);

            $this->setDinamicParams($dp);
            $this->_active_alias = $al;
        }

        $this->setValidators();

        parent::__construct($scenario);
    }



    /**
     * ActiveRecordClone  - Возвращает обьект ActiveRecordClone
     * @param string $alias             - название альяса для экземпляра обьекта. Используется при кэширования обьекта
     * @param string $dinamicParams     - динамические параметры. Устанавливаються перед создание объекта
     * @param bool $is_new_record       - указыват на создание нового "пустого" обьекта
     * @param bool $cache_new_record    - при true новый обьект будет закэширован
     * @param string $className         - название класса
     * @return object ActiveRecordClone
     */
    public static function modelR($alias = null, $dinamicParams = array(), $is_new_record = false, $cache_new_record = true, $className=__CLASS__){
        if(Cache::enabled(Cache::CACHE_TYPE_DB)){
            $db_models = Cache::getParam(Cache::CACHE_TYPE_DB, 'ar_models');
            if(empty($db_models) || !in_array($className, $db_models))
                return self::getModelR($alias, $dinamicParams, $is_new_record, $cache_new_record, $className);
            return self::getModelR($alias, $dinamicParams, $is_new_record, $cache_new_record, $className)->cache(Cache::getParam(Cache::CACHE_TYPE_DB, 'duration', 60));
        } else {
            return self::getModelR($alias, $dinamicParams, $is_new_record, $cache_new_record, $className);
        }
    }




    private static function getModelR($alias = null, $dinamicParams = array(), $is_new_record = false, $cache_new_record = true, $className){
        if($alias === null) $alias = $className;

        if(isset(self::$_models[$alias])){
            return self::$_models[$alias];
        } else {
            if($is_new_record == true){
                if($cache_new_record == true){
                    $model = static::$_models[$alias] = new $className('insert', $dinamicParams, $alias);
                } else {
                    $model = new $className('insert', $dinamicParams, $alias);
                }
            } else {
                $model = self::$_models[$alias] = new $className(null, $dinamicParams, $alias);
                $model->attachBehaviors($model->behaviors());
            }

            return $model;
        }
    }


    private function setValidators(){
        CValidator::$builtInValidators['unique'] = 'TUniqueValidator';
        CValidator::$builtInValidators['exist'] = 'TExistValidator';
    }



    protected function instantiate($attributes){
        $class=get_class($this);
        $model=new $class(null, $this->_dinamic_params, $this->_active_alias);
        return $model;
    }



    public static function getModel($alias){
        return self::$_models[$alias];
    }


    public function getAllModels(){
        return self::$_models;
    }


    public function destroyInstance(){
        if(isset(self::$_models[$this->_active_alias])){
            unset(self::$_models[$this->_active_alias]);
        }
        if(isset(self::$_md[$this->_active_alias])){
            unset(self::$_md[$this->_active_alias]);
        }
    }



    public function getMetaData(){
        if(!array_key_exists($this->_active_alias ,self::$_md)){
            self::$_md[$this->_active_alias]=null; // preventing recursive invokes of {@link getMetaData()} via {@link __get()}
            self::$_md[$this->_active_alias]=new CActiveRecordMetaData($this);
        }
        return self::$_md[$this->_active_alias];
    }



    public function refreshMetaData(){
        if(array_key_exists($this->_active_alias ,self::$_md)){
            unset(self::$_md[$this->_active_alias]);
        }
    }



    public function getAttributeLabel($attribute){
        $labels=$this->attributeLabels();
        if(isset($labels[$attribute]))
            return $labels[$attribute];
        elseif(strpos($attribute,'.')!==false)
        {
            $segs=explode('.',$attribute);
            $name=array_pop($segs);
            $model=$this;
            foreach($segs as $seg)
            {
                $relations=$model->getMetaData()->relations;
                if(isset($relations[$seg]))
                    $model=$this->getModel($this->_active_alias);
                else
                    break;
            }
            return $model->getAttributeLabel($name);
        }
        else
            return $this->generateAttributeLabel($attribute);
    }




    public function setDinamicParams(array $param){
        $this->_dinamic_params = $param;
        $this->tableName = $param['tableName'];
    }




    public function tableName() {
        return '{{' . $this->tableName  . '}}';
    }


    public function rules(){
        if(isset($this->_dinamic_params['params']['rules'])){
            return $this->_dinamic_params['params']['rules'];
        } else{
            return array();
        }
    }


    public function relations(){
        if(isset($this->_dinamic_params['params']['relations']))
            return $this->_dinamic_params['params']['relations'];
        else
            return array();
    }


    public function scopes(){
        if(isset($this->_dinamic_params['params']['scopes']))
            return $this->_dinamic_params['params']['scopes'];
        else
            return array();
    }


    public function attributeLabels(){
        if(isset($this->_dinamic_params['params']['attributeLabels']))
            return $this->_dinamic_params['params']['attributeLabels'];
        else
            return array();
    }






    protected function beforeValidate(){
        TUniqueValidator::$active_alias = $this->_active_alias;
        TExistValidator::$active_alias = $this->_active_alias;

        return true;
    }


    protected function afterValidate(){
        TUniqueValidator::$active_alias = null;
        TExistValidator::$active_alias = null;

        return true;
    }


}
