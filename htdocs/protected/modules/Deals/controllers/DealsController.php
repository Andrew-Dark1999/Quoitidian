<?php

use \Deals\extensions\ElementMaster as Extensions;

class DealsController extends \EditView{

    
    /**
     * возвращает новый элемент
     */
    public function actionMakeAgreement($copy_id){
        
        $doc_data = \Deals\models\ContractModel::makeAgreement($_POST);
        
        return $this->renderJson(array(
            'status' => true,
            'id' => $doc_data['doc_id'],
            'ev_refresh_fields' => $doc_data['ev_refresh_fields'], 
        ));
    }

    
    /**
     * возвращает новый элемент
     */
    public function actionCheckConditions($copy_id){
       
        $result = \Deals\models\ContractModel::checkConditions($_POST['deal_id']);
       
        return $this->renderJson(array(
            'error' => $result['error'],
            'default_data' => (!empty($result['default_data'])) ? $result['default_data'] : false,
        ));
    }

    
    public function actionGetEmptyParametersMessages($copy_id){
       
        return $this->renderJson(array(
            'error' => \Deals\models\ContractModel::getEmptyParametersMessages($_POST['level']),
        ));
    }
    
    









}
