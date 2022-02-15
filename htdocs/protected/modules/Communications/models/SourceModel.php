<?php



namespace Communications\models;


class SourceModel {


    /**
     * @return array
     */
    public function getSourceList($only_active = true){
        if($only_active){
            return \CommunicationsSourceModel::getSourceList();
        }else{
            return \Yii::app()->params['communications']['sources'];
        }
    }


    /**
     * @param $source_name
     * @return boolean
     */
    public function checkActiveSource($source_name){
        return (new \CommunicationsSourceModel())->checkActiveSource($source_name);
    }








}
