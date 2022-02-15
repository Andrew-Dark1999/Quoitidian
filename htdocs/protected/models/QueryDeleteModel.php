<?php
/**
 * class QueryDeleteModel
 *
 * @author Alex R.
 */


class QueryDeleteModel {

    const D_TYPE_DATA     = 'data';
    const D_TYPE_UPLOADS  = 'uploads';

    public static $_object;

    private $_delete_models = array();
    private $_data_model;


    private $_limit     = 1000;
    private $_limit_max = 10000;




    private function __construct(){}
    private function __clone(){}



    /**
     * getInstance - только один экземпляр класса в системе
     */
    public static function getInstance(){
        if(self::$_object === null){
            self::$_object = new static();
        }

        return static::$_object;
    }




    private function getDeleteModel($model_key, $delete_type){
        $model_name = '';

        switch($delete_type){
            case self::D_TYPE_DATA :
                $model_name = '\QueryDeleteDataModel';
                break;
            case self::D_TYPE_UPLOADS :
                $model_name = '\QueryDeleteUploadsModel';
                break;
        }

        if(!isset($this->_delete_models[$model_key])){
            $this->_delete_models[$model_key] = new $model_name();
        }

        return $this->_delete_models[$model_key];
    }




    public function setDeleteModelParams($model_key, $delete_type, $params){
        $delete_model = $this->getDeleteModel($model_key, $delete_type);
        $delete_model->setAllParams($params);
        return $this;
    }




    public function appendValues($model_key, $delete_type, $values){
        $delete_model = $this->getDeleteModel($model_key, $delete_type);

        switch($delete_type){
            case self::D_TYPE_DATA :
                $delete_model->appendId($values);
                break;
            case self::D_TYPE_UPLOADS :
                $delete_model->appendFile($values);
                break;
        }

        return $this;
    }








    /**
     * checkCountAndExecute
     */
    /*
    private function checkCountAndExecute(){
        if($this->_count_values >= $this->_limit_max){
            $this->execute();

            $this->_values = array();
            $this->_count_values = 0;
        }
    }
    */






    /**
     * getSteepEnd
     */
    private function getSteepEnd($values){
        $steep = 1;
        $rows = count($values);

        if($rows > $this->_limit){
            $steep = $rows / $this->_limit;
            $steep = ceil($steep);
        }

        return $steep;
    }




    private function executeDataSteep($delete_model){
        $id_list = $delete_model->getIdList();
        if(empty($id_list)) return;

        $steep_end = $this->getSteepEnd($id_list);
        $off_set = 0;

        for($i = 0; $i < $steep_end; $i++){
            $id_list_slice = array_slice($id_list, $off_set, $this->_limit);
            $condition = array('in', $delete_model->getPrimaryFieldName(), $id_list_slice);

            (new DataModel())->Delete('{{' . $delete_model->getTableName() . '}}', $condition);

            $off_set+= $this->_limit; // next pack
        }
    }


    private function executeDataCondition($delete_model){
        $condition = $delete_model->getCondition();
        $params = $delete_model->getParams();

        if($condition == false){
            return;
        }

        (new DataModel())->Delete('{{' . $delete_model->getTableName() . '}}', $condition, $params);
    }




    /**
     * executeData
     */
    private function executeData($delete_model){
        $id_list = $delete_model->getIdList();

        if($id_list){
            return $this->executeDataSteep($delete_model);
        } else {
            return $this->executeDataCondition($delete_model);
        }

        $delete_model->clearIdList();

        return $this;
    }








    /**
     * executeUploads
     */
    public function executeUploads($delete_model){
        $file_lilst = $delete_model->getFilesList();
        if(empty($file_lilst)) return $this;

        $path_module = ParamsModel::model()->titleName('upload_path_module')->find()->getValue();
        foreach($file_lilst as $file){
            if(empty($file['fp'])) continue;
            $path = $path_module .'/'. $file['fp'];
            if(!file_exists($path)) continue;
            \FileOperations::getInstance()->removeDirectory($path);
        }
    }



    /**
     * executeAll
     */
    public function executeAllData(){
        if(empty($this->_delete_models)) return $this       ;

        foreach($this->_delete_models as $delete_model){
            // удаляем данные
            if($delete_model instanceof \QueryDeleteDataModel){
                $this->executeData($delete_model);
            // удаляем файлы
            } elseif($delete_model instanceof \QueryDeleteUploadsModel){
                $this->executeUploads($delete_model);
            }
        }

        return $this;
    }



    public function clearDataModels(){
        $this->_data_model = null;
    }




}

