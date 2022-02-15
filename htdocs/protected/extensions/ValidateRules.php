<?php

/**
* ������� � �������� ��������� ������ �������� ������� 
*/ 

class ValidateRules {
    



    
    /**
     * ���������� ������� ������ � ������
     */
    public static function isDataFromParentModule($pci, $pdi){
        $result = true;
        $extension_copy = ExtensionCopyModel::model()->findByPk($pci);
        if(empty($extension_copy)) return false; 

        $model = new DataModel();
        $model
            ->setSelect('count(*) as count_rows')
            ->setFrom($extension_copy->getTableName(null, true, false))
            ->setWhere(array('AND', $extension_copy->prefix_name . '_id = :id'), array(':id'=>$pdi));
        $count = $model->findRow();
        if($count['count_rows'] == 0) $result = false;

        return $result;
    }       




    /**
     * ���������, ���� �� ������ � ������ �� � ������������ ������
     */
    public static function checkIsSetParentDataModule(){
        $result = true;
        $pci = null;
        $pdi = null;
        
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            if(array_key_exists('pci', $_GET)) $pci = $_GET['pci'];
            if(array_key_exists('pdi', $_GET)) $pdi = $_GET['pdi'];
        } else if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(array_key_exists('pci', $_POST)) $pci = $_POST['pci'];
            if(array_key_exists('pdi', $_POST)) $pdi = $_POST['pdi'];
        }

        
        if((boolean)Yii::app()->controller->module->extensionCopy->be_parent_module == true){
            
            if(!empty($pci) || !empty($pdi)) $result = false;
            $result = self::isDataFromParentModule($pci, $pdi); 
        } else {
            if((!empty($pci) && empty($pdi)) || (empty($pci) && !empty($pdi))) $result = false;
            if(!empty($pci) && !empty($pdi)) $result = self::isDataFromParentModule($pci, $pdi);
        }

        return $result;
    }
    
    
    
    
    
    
    
}


