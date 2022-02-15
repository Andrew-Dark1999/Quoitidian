<?php
/**
 * ReportsTableModel
 *
 * @author Alex R.
 * @copyright 2014
 */

namespace Reports\models;

class ReportsTableModel {


    private $_schema = array();
    private $_data;
    private $_result_data;
    private $_extension_copy;

    public static $changed_data = false;



    public static function getInstance(){
        return new self();
    }


    public function setData($data){
        $this->_data = $data;
        return $this;
    }


    public function setSchema($schema){
        $this->_schema = $schema;
        return $this;
    }


    public function setParentExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }

    public function getResultData(){
        return $this->_result_data;
    }





    public function prepare($id_field_name){
        if(empty($this->_data)){
            $this->_result_data = $this->_data;
            return $this;
        }

        $schema_param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->_schema, 'data_analysis_param');
        if(empty($schema_param)) return $this;

        if(\Reports\models\ConstructorModel::isPeriodConstant($schema_param['field_name'])){
            $this->_result_data = $this->_data;
            return $this;
        }

        $id_list = array();
        foreach($this->_data as $row){
            if(isset($row[$id_field_name]))
                $id_list[] = $row[$id_field_name];
        }
        if(empty($id_list)) return $this;

        $data = $this->getDataFromDB($this->_extension_copy, $id_list);
        $data = $this->setIdKey($this->_extension_copy, $data);

        self::$changed_data = true;

        $this->_result_data = $data;

        return $this;
    }





    private function setIdKey($extension_copy, $data){
        $result = array();
        foreach($data as $row){
            $result[$row[$extension_copy->prefix_name . '_id']] = $row;
        }
        return $result;
    }






    /**
     *   Возвращает данные модуля
     */
    public function getDataFromDB($extension_copy, $id_list){
        //  get data
        $data_model = new \DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        //responsible
        if($extension_copy->isResponsible())
            $data_model->setFromResponsible();

        //participant
        if($extension_copy->isParticipant())
            $data_model->setFromParticipant();

        $data_model->andWhere(array('in', $extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id' , $id_list));

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup()
            ->replaceParamsOnRealValue();

        return $data_model->findAll();
    }












}
