<?php
/**
 * ReportsModel
 * 
 * @author Alex R.
 * @copyright 2014
 */
 
namespace Reports\models;
 

class ReportsModel extends \ActiveRecord{


    public $tableName = 'reports';
    
    
	public static function model($className=__CLASS__){
		return parent::model($className);
	}
    


    public static function getSchemaModel(){
        return self::$_schema_model;
    }





    public static function getSavedSchema($report_id, &$validate = null){
        $model = self::model()->findByPk($report_id);

        if(empty($model)){
            if(!empty($validate))
                $validate->addValidateResult('e', \Yii::t('messages', 'Not defined schema'));
            return;
        }
        $schema = $model->schema;
        
        if(!empty($schema)){
            $schema = json_decode($schema, true);            
        } else {
            if(!empty($validate))
                $validate->addValidateResult('e', \Yii::t('messages', 'Not defined schema'));
            return;
        }
        
        return $schema;
    }
    
    
    
    
    
    
    public static function getReportsList(){
        $extension_copy = \Yii::app()->controller->module->extensionCopy;

        $data_model = new \DataModel();
        $data_model->setExtensionCopy($extension_copy);
        $data_model->addSelect('reports_id, module_title');
        $data_model->setFrom('{{reports}}');
        $data_model->setOrder('module_title');

        //responsible
        if($extension_copy->isResponsible()){
            $data_model->setFromResponsible();
        }

        //participant
        if($extension_copy->isParticipant()){
            $data_model->setFromParticipant();
        }

        $data_model->setCollectingSelect();
        //participant only
        if($extension_copy->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())){
            $data_model->setOtherPartisipantAllowed($extension_copy->copy_id);
        }

        return $data_model->findAll();
    }








}
