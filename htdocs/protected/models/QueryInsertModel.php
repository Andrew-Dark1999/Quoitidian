<?php
/**
 * class QueryInsertModel
 *
 * @author Alex R.
 */

class QueryInsertModel
{

    private $_qi_data_model = [];

    private $_qi_data_model_key = 'general';

    private static $_data_model = null;

    private $_limit = 1000;

    //private $_limit_max = 10000;

    /**
     * getInstance
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * setQIDataModel
     */
    public function setQIDataModel(QueryInsertDataModel $query_insert_data_model, $qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);
        $this->_qi_data_model[$qi_data_model_key] = $query_insert_data_model;

        return $this;
    }

    /**
     * getQIDataModel
     */
    public function getQIDataModel($qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);

        return $this->_qi_data_model[$qi_data_model_key];
    }

    /**
     * setQIDataModelKey
     */
    public function setQIDataModelKey($qi_data_model_key)
    {
        $this->_qi_data_model_key = $qi_data_model_key;

        return $this;
    }

    /**
     * getQIDataModelKey
     */
    private function getQIDataModelKey($qi_data_model_key = null)
    {
        if ($qi_data_model_key === null) {
            return $this->_qi_data_model_key;
        } else {
            return $qi_data_model_key;
        }
    }

    /**
     * setTableName
     */
    public function setTableName($table_name, $qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);
        $insert_data_model = $this->getQIDataModel($qi_data_model_key);
        $insert_data_model->setTableName($table_name);

        return $this;
    }

    /**
     * setFields
     */
    public function setFields($fields, $qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);
        $insert_data_model = $this->getQIDataModel($qi_data_model_key);
        $insert_data_model->setFields($fields);

        return $this;
    }

    /**
     * appendValues
     */
    public function appendValues($values, $qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);

        /*
        if($this->checkCountAndExecute()){
            $this->executeAll()
        }
        */

        $insert_data_model = $this->getQIDataModel($qi_data_model_key);
        $insert_data_model->appendValues($values);

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
     * getDataModel
     */
    private function getDataModel($reset = true)
    {
        if (self::$_data_model === null) {
            self::$_data_model = new DataModel();

            return self::$_data_model;
        } else {
            if ($reset) {
                self::$_data_model->reset();
            }

            return self::$_data_model;
        }
    }

    /**
     * getSteepEnd
     */
    private function getSteepEnd($values)
    {
        $steep = 1;
        $rows = count($values);

        if ($rows > $this->_limit) {
            $steep = $rows / $this->_limit;
            $steep = ceil($steep);
        }

        return $steep;
    }

    private function getPreparedInsertQuery($table_name, $fields, $values)
    {
        $query = 'INSERT INTO {{' . $table_name . '}}' . ' (' . $fields . ') VALUES ' . $values;

        return $query;
    }

    /**
     * execute
     */
    public function execute($qi_data_model_key = null)
    {
        $qi_data_model_key = $this->getQIDataModelKey($qi_data_model_key);
        $data_model = $this->getDataModel();
        $insert_data_model = $this->getQIDataModel($qi_data_model_key);
        $values = $insert_data_model->getValues();

        if (empty($values)) {
            return;
        }

        $steep_end = $this->getSteepEnd($values);
        $off_set = 0;

        for ($i = 0; $i < $steep_end; $i++) {
            $values_slice = implode(',', array_slice($values, $off_set, $this->_limit));
            $insert_query = $this->getPreparedInsertQuery(
                $insert_data_model->getTableName(),
                $insert_data_model->getFields(),
                $values_slice);

            $data_model
                ->reset()
                ->setText($insert_query)
                ->execute();

            $off_set += $this->_limit; // next pack
        }

        $insert_data_model->clearValues();

        return $this;
    }

    /**
     * executeAll
     */
    public function executeAll()
    {
        if (empty($this->_qi_data_model)) {
            return;
        }

        $qi_data_model_keys = array_keys($this->_qi_data_model);

        foreach ($qi_data_model_keys as $qi_data_model_key) {
            $this->execute($qi_data_model_key);
        }

        return $this;
    }

}
