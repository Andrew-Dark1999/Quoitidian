<?php

/**
 * DocumentsGenerateModelExt
 * Класс для дополнительной обработки переменных (Единство)
 * only for Edinstvo
 * @copyright 2016
 */
class DocumentsGenerateModelExt
{

    /**
     * ID блока Договор (тип документа ДУДС)
     */
    const BLOCK_ID_AGREEMENT = '123c20e51b14fc6cd9f4f6e2de0a9df4';

    
    /**
     * ID блока Договора уступки
     */
    const BLOCK_ID_CONCESSION = '15d6c78abf2289c03e5c2bfbfa32d6a6';
    
    
    /**
     * ID блока Договора счета типа Платеж
     */
    const BLOCK_ID_BILL_PAYMENT = 'd21e1b21ef4207b38107dd007c0e65ed';
    
    
    /**
     * ID блока Финансы тип Счет
     */
    const BLOCK_ID_FINANCE_BILL = 'ed1690ee5874810d10288770e2a144f0';
    
    
    /**
     * ID блока Финансы тип Платеж
     */
    const BLOCK_ID_FINANCE_PAYMENT = '907e96bff54ee178d763c166d2191566';
    
    
    /**
     * copy_id модуля Сделки
     */
    const MODULE_DEALS = 181;
    
    
    /**
     * copy_id модуля Финансы
     */
    const MODULE_FINANCES = 183;
    
    
    /**
     * copy_id модуля Объекты
     */
    const MODULE_OBJECTS = 254;
    
    
    /**
     * copy_id модуля Доп платежи
     */
    const MODULE_ADD_PAYMENTS = 271;
    

    /**
     * Статусы
     */
    public static $STATUSES_FINANCE = array(
        'planned' => 'Планируется',
        'paid' => 'Оплачено',
    );
    public static $STATUSES_OBJECT = array(
        'reservations' => 'Бронь',
        'free' => 'Свободно',
        'sold' => 'Продано',
    );
    public static $STATUSES_DEAL = array(
        'closed_success' => 'Закрыта удачно',
        'sale' => 'Продажа',
    );
    
    private static $sort_field_name = '';
    
    /**
     * Хардкод переменных
     */
    public static function getParams($result, $model){

        if(!empty(($result['Документы:doc_sum'])) && (!empty($result['Документы:doc_pfr_sheet_summa']))) {
            $pfr = $result['Документы:doc_sum'] - $result['Документы:doc_pfr_sheet_summa'];
            $result['pfr'] = $pfr;
            $result['pfr' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($pfr);
        }

        if(isset($result['Сделки']['deal_sum']))
            $result['Сделки']['deal_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Сделки']['deal_sum']);
        
        if(isset($result['Сделки']['deal_debt']))
            $result['Сделки']['deal_debt' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Сделки']['deal_debt']);
        
        if(isset($result['Сделки']['deal_salesdate']))
            $result['Сделки']['deal_salesdate' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($result['Сделки']['deal_salesdate']);
        
        if(isset($result['Сделки:deal_sum']))
            $result['Сделки:deal_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Сделки:deal_sum']);
        
        if(isset($result['Сделки:deal_debt']))
            $result['Сделки:deal_debt' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Сделки:deal_debt']);
        
        if(isset($result['Сделки:deal_salesdate']))
            $result['Сделки:deal_salesdate' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($result['Сделки:deal_salesdate']);
        
        if(isset($result['Документы:Сделки:deal_sum']))
            $result['Документы:Сделки:deal_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Документы:Сделки:deal_sum']);
        
        if(isset($result['Документы:Сделки:deal_debt']))
            $result['Документы:Сделки:deal_debt' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Документы:Сделки:deal_debt']);
        
        if(isset($result['Документы:Сделки:deal_salesdate']))
            $result['Документы:Сделки:deal_salesdate' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($result['Документы:Сделки:deal_salesdate']);

        if(isset($result['Объекты'][0]['object_paramprojectarea']))
            $result['Объекты'][0]['object_paramprojectarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramprojectarea']);

        if(isset($result['Объекты'][0]['object_paramgeneralarea']))
            $result['Объекты'][0]['object_paramgeneralarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramgeneralarea']);

        if(isset($result['Объекты'][0]['object_paramcontractprice']))
            $result['Объекты'][0]['object_paramcontractprice' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Объекты'][0]['object_paramcontractprice']);

        if(isset($result['Объекты'][0]['object_parammetrprice']))
            $result['Объекты'][0]['object_parammetrprice' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Объекты'][0]['object_parammetrprice']);

        if(isset($result['Объекты'][0]['object_paramobjectprice']))
            $result['Объекты'][0]['object_paramobjectprice' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Объекты'][0]['object_paramobjectprice']);

        if(isset($result['Объекты'][0]['object_paramcalcarea']))
            $result['Объекты'][0]['object_paramcalcarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramcalcarea']);

        if(isset($result['Объекты'][0]['object_paramkitchenarea']))
            $result['Объекты'][0]['object_paramkitchenarea' . DocumentsGenerateModel::NUMBER_AS_TEXT] = $model->nmbToText($result['Объекты'][0]['object_paramkitchenarea']);

        if(isset($result['Объекты'][0]['object_paramauxarea']))
            $result['Объекты'][0]['object_paramauxarea' . DocumentsGenerateModel::NUMBER_AS_TEXT] = $model->nmbToText($result['Объекты'][0]['object_paramauxarea']);

        if(isset($result['Объекты'][0]['object_paramloggiaarea']))
            $result['Объекты'][0]['object_paramloggiaarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramloggiaarea']);

        if(isset($result['Объекты'][0]['object_paramloggiafactor']))
            $result['Объекты'][0]['object_paramloggiafactor' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramloggiafactor']);

        if(isset($result['Объекты'][0]['object_parambalconyarea']))
            $result['Объекты'][0]['object_parambalconyarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_parambalconyarea']);

        if(isset($result['Объекты'][0]['object_parambalconfactor']))
            $result['Объекты'][0]['object_parambalconfactor' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_parambalconfactor']);

        if(isset($result['Объекты'][0]['object_paramterracearea']))
            $result['Объекты'][0]['object_paramterracearea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramterracearea']);

        if(isset($result['Объекты'][0]['object_paramterracefactor']))
            $result['Объекты'][0]['object_paramterracefactor' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramterracefactor']);

        if(isset($result['Объекты'][0]['object_paramlivingarea']))
            $result['Объекты'][0]['object_paramlivingarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_paramlivingarea']);

        if(isset($result['Объекты'][0]['object_bti_totalarea']))
            $result['Объекты'][0]['object_bti_totalarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_bti_totalarea']);

        if(isset($result['Объекты'][0]['object_bti_livingarea']))
            $result['Объекты'][0]['object_bti_livingarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_bti_livingarea']);

        if(isset($result['Объекты'][0]['object_bti_balconyarea']))
            $result['Объекты'][0]['object_bti_balconyarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_bti_balconyarea']);

        if(isset($result['Объекты'][0]['object_bti_livingarea']))
            $result['Объекты'][0]['object_bti_livingarea' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($result['Объекты'][0]['object_bti_livingarea']);

        
        if(isset($result['Объекты'][0]['object_floor'])){
            $x = explode('-', $result['Объекты'][0]['object_floor']);
            if(count($x)>1) {
                $result['Объекты'][0]['larder'] = true;
                $result['Объекты'][0]['floor_down'] = (int)$x[0];
                $result['Объекты'][0]['floor_up'] = (int)$x[1];
            }else
                $result['Объекты'][0]['larder'] = false;
        }
   
        if(isset($result['Объекты'][0]['_extension_copy_id'])){

            //start застройщики (+дома)
            
            $relates = ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$result['Объекты'][0]['_copy_id'], 'type'=>'relate_module_one'));

            if(count($relates)>0) {
                foreach($relates as $relate) {
                    
                    $ex_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($relate->relate_copy_id);
                    
                    if($model->deleteSpaces($ex_copy->title)=='Дома') {
                        $ex = \ExtensionCopyModel::model()->modulesActive()->findByPk($result['Объекты'][0]['_copy_id']);
                        $data = $model->getData($ex, $result['Объекты'][0]['_extension_copy_id']);

                        foreach($data[0] as $name => $v){
                            if(substr($name, -strlen('_'.$relate->relate_field_name))=='_'.$relate->relate_field_name) {
                                
                                //дом найден
                                $ex_d = \ExtensionCopyModel::model()->modulesActive()->findByPk($relate->relate_copy_id);
                                $data2 = $model->getData($ex_d, $v);
                                
                                if(count($data2)) {        

                                    //дома
                                    foreach($data2[0] as $house_key=>$house_value){
                                        $result['Документы:house:' . $house_key] = $house_value;
                                        if($house_key=='house_transferdeadline'){
                                            $result['Документы:house:' . $house_key . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($house_value);
                                            $result['Документы:house:' . $house_key . DocumentsGenerateModel::DATE_AS_FULLTEXT] = $model->dateToText($house_value, true);
                                        }
                                        if($house_key=='house_moneybackdeadline') {
                                            $result['Документы:house:' . $house_key] = (int)$result['Документы:house:' . $house_key];
                                            $result['Документы:house:' . $house_key . DocumentsGenerateModel::NUMBER_AS_TEXT] = $model->nmbToText($house_value);
                                        }    
                                    }
                                
                                    //выходим на застройщика
                                    $relates2 = ModuleTablesModel::model()->findAllByAttributes(array('copy_id'=>$relate->relate_copy_id, 'type'=>'relate_module_one')); 
                                   
                                    if(count($relates2)>0) {
                                        foreach($relates2 as $relate2) {
                                            
                                            $ex_copy2 = \ExtensionCopyModel::model()->modulesActive()->findByPk($relate2->relate_copy_id);
                                            
                                            if($model->deleteSpaces($ex_copy2->title)=='Застройщики') {

                                                foreach($data2[0] as $name2 => $v2){
                                                    if(substr($name2, -strlen('_'.$relate2->relate_field_name))=='_'.$relate2->relate_field_name) {
                                                        
                                                        //застройщик найден
                                                        $builder_data = $model->getData($ex_copy2, $v2)[0];
                                                        
                                                        if(count($builder_data)) {
                                                            foreach($builder_data as $k=>$v) {
                                                                
                                                                //для некоторых полей выводим целое число в значении
                                                                if(in_array($k, array('developer_remineration', 'developer_inn', 'developer_kpp', 'developer_bik', 'developer_oktmo', 'developer_rch', 'developer_ogrn', 'developer_kch')))
                                                                    $v = (int)$v;
                                                                
                                                                $result['builder:'.$k] = $v;
                                                                
                                                            }    
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }      
                                break;
                            }
                        }    
                    }
                }
            }
            
            //end застройщики
            
            //start дополнительные платежи (вместо одного загружаем все)
            
            $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($result['Объекты'][0]['_copy_id']);
            $schema = $extension_copy->getSchema();
            $add_payments = $model->collectSM($schema, $result['Объекты'][0]['_copy_id'], $result['Объекты'][0]['_extension_copy_id'], array(), 'Доп_платежи');
            
            if(count(@$add_payments['Доп_платежи'][2])>0) {
                foreach($add_payments['Доп_платежи'][2] as $k=>$v) {
                
                    $result['Объекты:Доп_платежи'][$k] = $v;
                    if(isset($v['addpay_sum']))
                        $result['Объекты:Доп_платежи'][$k]['addpay_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($v['addpay_sum']);
                    
                }
            }

            //end дополнительные платежи

        }



        //клиенты
        if(isset($result['ИнформацияоКлиентахвСделке:Клиенты'])){
            
            //дольщики
            $ex_copy_c = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Клиенты'));
            $successors_copy = $model->collectSM($model->getExtensionCopySchema(), $model->getExtensionCopy()->copy_id, $model->getExtensionCopyId(), array(), 'ИнформацияоКлиентахвСделке');

            $clients = array();
            
            if(count($successors_copy)>0){
                if(isset($successors_copy['Информация о Клиентах в Сделке'][2])) {
                    foreach($successors_copy['Информация о Клиентах в Сделке'][2] as $k => $v) {
                        $successors = array();
                        foreach($v as $name => $v2){
                            $successors[$name] = $v2;
                            if(substr($name, -strlen('_'.$ex_copy_c->prefix_name.'_id'))=='_'.$ex_copy_c->prefix_name.'_id') {
                                $c_data = $model->getRelate($ex_copy_c->copy_id, $v2, array(), $model->getMaxIterationLevel()-1);
                                //данные клиента из модуля Информация о Клиентах в Сделке
                                if(!empty($c_data)) {
                                    if(count($c_data)>0){
                                        $client = array();
                                        $client['_copy_id'] = $ex_copy_c->copy_id;
                                        $client['_extension_copy_id'] = $v2;
                                        foreach($c_data as $k3=>$v3) {
                                            $x = explode(':', $k3);
                                            $client[$x[1]] = $v3;
                                        }
                                        
                                    }
                                }
                            }
                        }    
                        $client['successors'] = $successors;
                        $clients[]= $client;
                    }
                }
            }
            
            $ex_copy_p = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Представители'));
            
            if($ex_copy_p !== null)
                $schema_p = $ex_copy_p->getSchema();

            
            if(count($clients)){
                foreach($clients as $k => $client) {
                    
                    $sex = 1;
                
                    if(isset($client['client_gender']))
                        $sex = ($client['client_gender']=='Мужской') ? 1 : 2;
                    
                    $client['module_title' . DocumentsGenerateModel::CONVERT_TO_CASE_2] = \NCLName::getInstance()->get($client['module_title'], 1, $sex);
                    $client['module_title' . DocumentsGenerateModel::CONVERT_TO_CASE_3] = \NCLName::getInstance()->get($client['module_title'], 2, $sex);
                    $client['module_title' . DocumentsGenerateModel::CONVERT_TO_CASE_5] = \NCLName::getInstance()->get($client['module_title'], 4, $sex);    
                    
                    if(isset($client['client_birthday']))  {
                        $client['client_birthday' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['client_birthday']);
                        $client['client_birthday' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($client['client_birthday'], false, true);
                    }                            
                    
                    $ex_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($client['_copy_id']);
                    $schema = $ex_copy->getSchema();
                
                    $address = $model->collectSM($schema, $client['_copy_id'], $client['_extension_copy_id'], array(), 'Адреса');

                    if(count(@$address['Адреса'][2])>0) 
                        $client['address'] = $model->getSingleData($address['Адреса'][2]);

                    $passport_data = $model->collectSM($schema, $client['_copy_id'], $client['_extension_copy_id'], array(), 'Паспортныеданные');

                    if(count(@$passport_data['Паспортные данные'][2])>0) 
                        $client['passport_data'] = $model->getSingleData($passport_data['Паспортные данные'][2]);

                    if(isset($client['passport_data']['passport_whenissued'])) {
                        $client['passport_data']['passport_whenissued' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['passport_data']['passport_whenissued']);
                        $client['passport_data']['passport_whenissued' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($client['passport_data']['passport_whenissued'], false, true);
                    }
                    
                    if(!empty($client['successors']['ms_base_predstaviteli_predstaviteli_id'])) {
                        $p_data = $model->getRelate($ex_copy_p->copy_id, $client['successors']['ms_base_predstaviteli_predstaviteli_id'], array(), $model->getMaxIterationLevel()-1);
                        
                        if(!empty($p_data) && count($p_data)) {
                            foreach($p_data as $k_p_data=>$v_p_data){
                                $w = explode(':', $k_p_data);
                                if(count($w)==2)
                                   $client['predstavitel'][$w[1]] = $v_p_data;
                            }
                        }
                    
                        if(!empty($client['predstavitel']['rep_type'])) {
                            
                            if($client['predstavitel']['rep_type'] == 'Представитель по доверенности')
                                $client['predstavitel']['rep_type'] = 4;
                            
                            if($client['predstavitel']['rep_type'] == 'С согласия родителей')
                                $client['predstavitel']['rep_type'] = 5;
                            
                            if($client['predstavitel']['rep_type'] == 'Законный представитель')
                                $client['predstavitel']['rep_type'] = 6;
                            
                        }
                    
                        $address_p = $model->collectSM($schema_p, $ex_copy_p->copy_id, $client['successors']['ms_base_predstaviteli_predstaviteli_id'], array(), 'Адреса');
                        if(count(@$address_p['Адреса'][2])>0) 
                            $client['predstavitel']['address'] = $model->getSingleData($address_p['Адреса'][2]);
                        
                        $passport_data_p = $model->collectSM($schema_p, $ex_copy_p->copy_id, $client['successors']['ms_base_predstaviteli_predstaviteli_id'], array(), 'Паспортныеданные');
                                
                        if(count(@$passport_data_p['Паспортные данные'][2])>0) 
                            $client['predstavitel']['passport_data'] = $model->getSingleData($passport_data_p['Паспортные данные'][2]);
                        
                        //дополнительные переменные
                        if(!empty($client['predstavitel']['passport_data']['passport_whenissued'])) {
                            $client['predstavitel']['passport_data']['passport_whenissued' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['passport_data']['passport_whenissued']);
                            $client['predstavitel']['passport_data']['passport_whenissued' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($client['predstavitel']['passport_data']['passport_whenissued'], false, true);
                        }
                        
                        if(!empty($client['predstavitel']['rep_birthday'])) {
                            $client['predstavitel']['rep_birthday' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['rep_birthday']);
                            $client['predstavitel']['rep_birthday' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($client['predstavitel']['rep_birthday'], false, true);
                        }
    
                        if(!empty($client['predstavitel']['notary_docdate'])) 
                            $client['predstavitel']['notary_docdate' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['notary_docdate']);
                    
                    }
                    
                    /*
                    $pred = $model->collectSM($schema, $client['_copy_id'], $client['_extension_copy_id'], array(), 'Представители');

                    if(count(@$pred['Представители'][2])>0) {
                        
                        //к каждому представителю добавляем адрес и паспортные данные
                        
                        $p_data = $model->getSingleData($pred['Представители'][2]);
                        
                        $client['predstavitel'] = $p_data;

                        if($ex_copy_p !== null) {
                            
                            $address_p = $model->collectSM($schema_p, $ex_copy_p->copy_id, $p_data[$ex_copy_p->prefix_name. '_id'], array(), 'Адреса');
                                
                            if(isset($client['predstavitel']['rep_birthday'])) 
                                $client['predstavitel']['rep_birthday' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['rep_birthday']);
    
                            if(isset($client['predstavitel']['notary_docdate'])) 
                                $client['predstavitel']['notary_docdate' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['notary_docdate']);
        
                                
                            if(count(@$address_p['Адреса'][2])>0) 
                                $client['predstavitel']['address'] = $model->getSingleData($address_p['Адреса'][2]);
                            
                            $passport_data_p = $model->collectSM($schema_p, $ex_copy_p->copy_id, $p_data[$ex_copy_p->prefix_name. '_id'], array(), 'Паспортныеданные');
                                
                            if(count(@$passport_data_p['Паспортные данные'][2])>0) 
                                $client['predstavitel']['passport_data'] = $model->getSingleData($passport_data_p['Паспортные данные'][2]);
                                
                                
                            if(isset($client['predstavitel']['passport_data']['passport_whenissued'])) 
                                $client['predstavitel']['passport_data']['passport_whenissued' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($client['predstavitel']['passport_data']['passport_whenissued']);
            
                        }
                    }
                    */
                    //обработка непосредственно долей
                    if(isset($client['successors']['cl_deal_share'])) {
                        
                        //start дольщики
                        $client['successors']['cl_deal_share' . DocumentsGenerateModel::FRACTION_AS_TEXT] = $model->fractionToText($client['successors']['cl_deal_share']);
                        
                        if(isset($result['Документы:doc_invoice_payment_square']))
                            $client['successors']['cl_deal_share_payment_square'] = $model->calcFromFraction($result['Документы:doc_invoice_payment_square'], $client['successors']['cl_deal_share']);
                        
                        if(isset($result['Документы:doc_invoice_payment_sum'])) {
                            $sum = $model->calcFromFraction($result['Документы:doc_invoice_payment_sum'], $client['successors']['cl_deal_share']);
                            $client['successors']['cl_deal_share_payment_sum'] = sprintf("%.2f", $sum);
                            $client['successors']['cl_deal_share_payment_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($sum);
                        }
                        
                        if(isset($result['Документы:doc_concession_amount'])) {
                            $sum = $model->calcFromFraction($result['Документы:doc_concession_amount'], $client['successors']['cl_deal_share']);
                            $client['successors']['cl_deal_share_concession_amount'] = sprintf("%.2f", $sum);
                            $client['successors']['cl_deal_share_concession_amount' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($sum);
                        }
                        //end дольщики
                        
                        //start дополнительные платежи (по долям)
                        if(isset($result['Объекты:Доп_платежи'])){
                            if(count($result['Объекты:Доп_платежи'])>0){
                                foreach($result['Объекты:Доп_платежи'] as $k2 => $v2){
                                    
                                    $v2['addpay_sum_all'] = sprintf("%.2f", $v2['addpay_sum']);
                                    $v2['addpay_sum'] = $model->calcFromFraction($v2['addpay_sum'], $client['successors']['cl_deal_share']);
                                    $v2['addpay_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($v2['addpay_sum']);
                                    
                                    $client['payments'][$k2] = $v2;
                                }
                            }
                        }
                        //end дополнительные платежи (по долям)
                    }
                    $result['ИнформацияоКлиентахвСделке:Клиенты'][$k] = $client;

                } 
                $result = self::roundingPayments($result, $model);
                $result = self::roundingSuccessors($result, $model);
            }
        }

        $documents = $model->collectSM($model->getExtensionCopySchema(), $model->getExtensionCopy()->copy_id, $model->getExtensionCopyId(), array(), 'Документы', false);
        $doc_agreent_exist = false;
        
        if(count($documents)>0){
            if(isset($documents['Документы'][2])) {
                foreach($documents['Документы'][2] as $k => $v) {
                    
                    if($v['doc_newtype'] == DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT){
                        //тип ДУДС
                        $result['doc_agreement_doc_date'] = $v['doc_date'];
                        $result['doc_agreement_doc_date_short' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($v['doc_date']);
                        $result['doc_agreement_doc_date' . DocumentsGenerateModel::DATE_AS_FULLTEXT] = $model->dateToText($v['doc_date'], true);
                        $result['doc_agreement_doc_date' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($v['doc_date'], false, true);

                        $result['doc_agreement_doc_number'] = $v['doc_number'];
                        
                        $result['doc_agreement_doc_sum'] = (!empty($v['doc_sum'])) ? $v['doc_sum'] : '';
                        $result['doc_agreement_doc_pay_type'] = (!empty($v['doc_contract_pay_type'])) ? $v['doc_contract_pay_type'] : '';
                        
                        $result['doc_signedby'] = (!empty($v['documents_doc_signedby_title'])) ? $v['documents_doc_signedby_title'] : '';

                        if(isset($result['Документы:doc_contract_pay_type'])) {
                            //печатается из-под документа типа ДУДС, проверяем select на форме
                            $doc_agreent_exist = ($result['Документы:doc_contract_pay_type']=='Фиксированный') ? 1 : 2;
                        }else {
                            //печать из-под иного документа
                            $doc_agreent_exist = ($v['documents_doc_contract_pay_type_title']=='Фиксированный') ? 1 : 2;
                        }
                    }
                    if($v['doc_newtype'] == DocumentsGenerateModelExt::BLOCK_ID_CONCESSION){
                        //тип Договор уступки
                        $result['doc_concession_doc_date'] = $v['doc_date'];
                        $result['doc_concession_doc_date' . DocumentsGenerateModel::DATE_AS_FULLTEXT] = $model->dateToText($v['doc_date'], true);
                        
                        $result['doc_concession_doc_number'] = $v['doc_number'];
                    }
                    
                }
            }
        }

        //анализируем финансы
        $finances = $model->collectSM($model->getExtensionCopySchema(), $model->getExtensionCopy()->copy_id, $model->getExtensionCopyId(), array(), 'Финансы');

        //долги по счетам
        if($doc_agreent_exist)
            $result['debts'] = self::getDebtsDeal($finances, $doc_agreent_exist);
        
        //этапы оплаты
        $result['payments'] = array();
        $result['all_payments'] = array();
        
        //дополнительный платеж типа Счет
        $result['addition_payment'] = array();
        
        //дополнительный платеж типа Платеж
        $result['addition_payment_pay'] = array();
        
        //юридические услуги
        $result['j_service'] = array();
        
        $sum_object_paramgeneralarea = 0;
        $sum_object_paramcontractprice = 0;
        
        if(count($finances)>0){
            if(isset($finances['Финансы'][2])) {
                foreach($finances['Финансы'][2] as $k => $v) {
                    
                    if(isset($v['finances_invoice_pay_date'])) {
                        //only for all_payments
                        $v['finances_invoice_pay_date_all' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($v['finances_invoice_pay_date'], false, true);
                    }
                    
                    $result['all_payments'][] = $v;

                    if(isset($v['finances_status'])){
                        if($v['finances_status']==\DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['paid'])) {
                            $sum_object_paramgeneralarea += (isset($v['finances_payment_square'])) ? $v['finances_payment_square'] : 0;
                            $sum_object_paramcontractprice += (isset($v['finances_sum'])) ? $v['finances_sum'] : 0;
                        }
                    }
                    
                    if(isset($v['finances_typenew'])) {
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL){
     
                    
                            if(isset($v['finances_payment_square']))
                                $v['finances_payment_square' . DocumentsGenerateModel::FLOAT_AS_TEXT] = $model->floatToText($v['finances_payment_square']);
                              
                            if(isset($v['finances_payment_metrecost']))
                                $v['finances_payment_metrecost' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($v['finances_payment_metrecost']);
                            
                            if(isset($v['finances_sum']))
                                $v['finances_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($v['finances_sum']);
                            
                            if(isset($v['finances_invoice_pay_date'])) {
                                $v['finances_invoice_pay_date' . DocumentsGenerateModel::DATE_AS_TEXT] = $model->dateToText($v['finances_invoice_pay_date']);
                                $v['finances_invoice_pay_date' . DocumentsGenerateModel::DATE_AS_TEXT_QUOTES] = $model->dateToText($v['finances_invoice_pay_date'], false, true);
                            }
                            
                            if(isset($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'])){
                  
                                if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                    //счет типа Площадь
                                    if(isset($v['finances_invoice_pay_date'])){
                                        if(!empty($v['finances_invoice_pay_date'])){
                                            //дата указана
                                            $result['payments'][] = $v;
                                        }
                                    }
                                }
                                     
                                if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3'){
                                    //счет типа Дополнительный платеж
                                    $result['addition_payment'] = $v;
                                }
                                
                                if(($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='4') &&(@$v['finances_sum']>0)){
                                    //счет типа Юридические услуги
                                    $result['j_service'] = $v;
                                }
                            }
                        }
                        
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT){
                            
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3'){
                                    //платеж типа Дополнительный платеж
                                    $result['addition_payment_pay'] = $v;
                            }
                        }   
                    } 
                }
            }

            self::sort($result['payments'], 'finances_invoice_pay_date');
            
            $result['first_payment'] = 0;
            
            if(isset($result['payments'][0]['finances_sum']))
                $result['first_payment'] += $result['payments'][0]['finances_sum'];
            
            if(isset($result['addition_payment']['finances_sum']))
                $result['first_payment'] += $result['addition_payment']['finances_sum'];
            
            $result['first_payment' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['first_payment']);
            
        }
        
        //долги
        $result['rest'] = array();
        
        $area = (isset($result['Объекты'][0]['object_paramgeneralarea'])) ? $result['Объекты'][0]['object_paramgeneralarea'] : 0;
        $price = (isset($result['Объекты'][0]['object_paramcontractprice'])) ? $result['Объекты'][0]['object_paramcontractprice'] : 0;
        
        $result['rest'] = array(
            'area' => $area - $sum_object_paramgeneralarea,
            'area' . DocumentsGenerateModel::FLOAT_AS_TEXT => $model->floatToText($area - $sum_object_paramgeneralarea),
            'price' => $price - $sum_object_paramcontractprice,
            'price'  . DocumentsGenerateModel::SUM_AS_TEXT => $model->sumToText($price - $sum_object_paramcontractprice),
        );
        
        //start поля - шаблоны
        
        $bankKreditName1p1  = ''; //ФИО первого Клиента
        $bankKreditName1    = ''; //ФИО 1 келиента в творительном падеже
        $bankKreditName2p1  = ''; //ФИО 2 -го клиента
        $bankKreditName2    = ''; //ФИО 2-го клиента в творительном падеже
        $bankKreditNamesImen= ''; //ФИО всех клиентов через запятую
        $bankKreditNames    = ''; //ФИО всех клиентов через запятую, в творительном падеже
        
        if(isset($result['ИнформацияоКлиентахвСделке:Клиенты'][0]['module_title'])) {
            $bankKreditName1p1 = $result['ИнформацияоКлиентахвСделке:Клиенты'][0]['module_title'];
            $sex = 1;
            
            if(isset($result['ИнформацияоКлиентахвСделке:Клиенты'][0]['client_gender']))
                $sex = ($result['ИнформацияоКлиентахвСделке:Клиенты'][0]['client_gender']=='Мужской') ? 1 : 2;
            
            $bankKreditName1 = \NCLName::getInstance()->get($result['ИнформацияоКлиентахвСделке:Клиенты'][0]['module_title'], 4, $sex);
        
            $bankKreditNamesImen = $bankKreditName1p1;
            $bankKreditNames = $bankKreditName1;
        }    
        
        if(isset($result['ИнформацияоКлиентахвСделке:Клиенты'][1]['module_title'])) {
            $bankKreditName2p1 = $result['ИнформацияоКлиентахвСделке:Клиенты'][1]['module_title'];
            $sex = 1;
            
            if(isset($result['ИнформацияоКлиентахвСделке:Клиенты'][1]['client_gender']))
                $sex = ($result['ИнформацияоКлиентахвСделке:Клиенты'][1]['client_gender']=='Мужской') ? 1 : 2;
            
            $bankKreditName2 = \NCLName::getInstance()->get($result['ИнформацияоКлиентахвСделке:Клиенты'][1]['module_title'], 4, $sex);
        
            $bankKreditNamesImen .= ', ' . $bankKreditName2p1;
            $bankKreditNames .= ', ' . $bankKreditName2;
        }   
        
        $bankKreditSumma = (isset($result['Документы:Сделки:deal_creditsum'])) ? $result['Документы:Сделки:deal_creditsum'] : '';
        $bankKreditSummaString = (isset($result['Документы:Сделки:deal_creditsum'])) ? $model->sumToText($result['Документы:Сделки:deal_creditsum']) : '';
        $bankKreditNumber = (isset($result['Документы:Сделки:deal_creditnumber'])) ? $result['Документы:Сделки:deal_creditnumber'] : '';
        $bankKreditDate = (isset($result['Документы:Сделки:deal_creditdate'])) ? mb_substr($result['Документы:Сделки:deal_creditdate'], 0, 10) : '';
        $bankKreditDeadline = (isset($result['Документы:Сделки:deal_creditpaybackdate'])) ? mb_substr($result['Документы:Сделки:deal_creditpaybackdate'], 0, 10) : '';

        $BuilderName        = (isset($result['builder:developer_fullname'])) ? $result['builder:developer_fullname'] : '';
        $BuilderOGRN        = (isset($result['builder:developer_ogrn'])) ? $result['builder:developer_ogrn'] : '';
        $BuilderINN         = (isset($result['builder:developer_inn'])) ? $result['builder:developer_inn'] : '';
        $BuilderKPP         = (isset($result['builder:developer_kpp'])) ? $result['builder:developer_kpp'] : '';
        $BuilderSchet       = (isset($result['builder:developer_rch'])) ? $result['builder:developer_rch'] : '';
        $BuilderBankName    = (isset($result['builder:developer_bank'])) ? $result['builder:developer_bank'] : '';
        $BuilderBankSchet   = (isset($result['builder:developer_kch'])) ? $result['builder:developer_kch'] : '';  
        $BuilderBankBik     = (isset($result['builder:developer_bik'])) ? $result['builder:developer_bik'] : '';

        $ObjectType = (isset($result['Объекты'][0]['object_type'])) ? $result['Объекты'][0]['object_type'] : '';
        
        $RedStr = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        
        $search = array('$bankKreditName1p1', '$bankKreditName1', '$bankKreditName2p1', '$bankKreditName2', '$bankKreditNamesImen', '$bankKreditNames', 
                        '$bankKreditSummaString', '$bankKreditSumma', '$bankKreditNumber', '$bankKreditDate', '$bankKreditDeadline',
                        '$BuilderName', '$BuilderOGRN', '$BuilderINN', '$BuilderKPP', '$BuilderSchet', '$BuilderBankName', '$BuilderBankSchet','$BuilderBankBik', 
                        '$ObjectType', '$RedStr'
                  );
        $replace = array($bankKreditName1p1, $bankKreditName1, $bankKreditName2p1, $bankKreditName2, $bankKreditNamesImen, $bankKreditNames, 
                        $bankKreditSummaString, $bankKreditSumma, $bankKreditNumber, $bankKreditDate, $bankKreditDeadline,
                        $BuilderName, $BuilderOGRN, $BuilderINN, $BuilderKPP, $BuilderSchet, $BuilderBankName, $BuilderBankSchet,$BuilderBankBik, 
                        $ObjectType, $RedStr
                  );
        
        foreach($result as $k=>$v) {
        
            if(in_array($k, array('Документы:Сделки:Банки:bank_item410', 'Документы:Сделки:Банки:bank_item11new1', 'Документы:Сделки:Банки:bank_item411', 'Документы:Сделки:Банки:bank_item11newdol', 'Документы:Сделки:Банки:bank_item11off', 'Документы:Сделки:Банки:bank_item11mm'))){
                $v = str_replace($search, $replace, $v);
            }
        
            $result[$k]=$v;
        }

        //end поля - шаблоны

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        $result['user'] = array('name' => UsersModel::model()->findByPk(\WebUser::getUserId())->getFullName()); 
        
        //заменяем в числах точку на запятую
        foreach($result as $k=>$v) {
            if(is_array($v) && count($v)) {
                foreach($v as $k_level2=>$v_level2) {
                    if(is_array($v_level2) && count($v_level2)) {
                        foreach($v_level2 as $k_level3 => $v_level3) {
                            if(is_array($v_level3) && count($v_level3)) {
                                foreach($v_level3 as $k_finish=>$v_finish) {
                                    if(filter_var($v_finish, FILTER_VALIDATE_FLOAT))
                                        $result[$k][$k_level2][$k_level3][$k_finish] = str_replace('.',',',($v_finish+0));
                                } 
                            }
                            if(filter_var($v_level3, FILTER_VALIDATE_FLOAT))
                                $result[$k][$k_level2][$k_level3] = str_replace('.',',',($v_level3+0));
                        }
                    }
                    if(filter_var($v_level2, FILTER_VALIDATE_FLOAT))
                        $result[$k][$k_level2] = str_replace('.',',',($v_level2+0));
                } 
            }
            if(filter_var($v, FILTER_VALIDATE_FLOAT))
                $result[$k] = str_replace('.',',',($v+0));   
        }

        return $result;
      
        
    }
    
    
    /**
     *  В случае расчета долей могут быть потеряны копейки,
     * проверяем и в случае чего добавляем разницу первому клиенту
     */ 
    private static function roundingSuccessors($result, $model){
        
        $square = 0;
        $sum = 0;
        $amount = 0;
        
        foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) {
            if(isset($client['successors']['cl_deal_share_payment_square']))
                $square += $client['successors']['cl_deal_share_payment_square'];
            if(isset($client['successors']['cl_deal_share_payment_sum']))
                $sum += $client['successors']['cl_deal_share_payment_sum'];
            if(isset($client['successors']['cl_deal_share_concession_amount']))
                $amount += $client['successors']['cl_deal_share_concession_amount'];
        }
        
        if(isset($result['Документы:doc_invoice_payment_square'])){
            if((float)$result['Документы:doc_invoice_payment_square'] != $square){
                //общая площадь отличается от суммы площади дольщиков, добавляем ее к первому
                $x = sprintf("%.2f", (float)$result['Документы:doc_invoice_payment_square'] - $square);
                foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) 
                    if(isset($client['successors']['cl_deal_share_payment_square'])){
                        $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['successors']['cl_deal_share_payment_square'] += $x;
                        break;
                    }

            }
        }

        if(isset($result['Документы:doc_invoice_payment_sum'])){
            if((float)$result['Документы:doc_invoice_payment_sum'] != $sum){
                //общая сумма отличается от суммы площади дольщиков
                $y = sprintf("%.2f", (float)$result['Документы:doc_invoice_payment_sum'] - $sum);
                foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) 
                    if(isset($client['successors']['cl_deal_share_payment_sum'])){
                        $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['successors']['cl_deal_share_payment_sum'] += $y;
                        $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['successors']['cl_deal_share_payment_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Клиенты'][$k]['successors']['cl_deal_share_payment_sum']);
                        break;
                    }
            }
        }     
        
        if(isset($result['Документы:doc_concession_amount'])){
            if((float)$result['Документы:doc_concession_amount'] != $amount){
                //общая сумма отличается от суммы площади дольщиков
                $y = sprintf("%.2f", (float)$result['Документы:doc_concession_amount'] - $amount);
                foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) 
                    if(isset($client['successors']['cl_deal_share_concession_amount'])){
                        $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['successors']['cl_deal_share_concession_amount'] += $y;
                        $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['successors']['cl_deal_share_concession_amount' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Клиенты'][$k]['successors']['cl_deal_share_concession_amount']);
                        break;
                    }
            }
        }   
        
        
        return $result;
    }
    
    
    private static function roundingPayments($result, $model){
        
        //для текущих полученных значений
        $current = array();
        
        //значения, какие должны быть
        $all = array();
        
        foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) {
            if(isset($client['payments'])) {
                if(count($client['payments'])>0){
                    foreach($client['payments'] as $k2 => $v2){
                        if(!isset($current[$k2]))
                            $current[$k2] = 0;
                        $current[$k2] += $v2['addpay_sum'];
                        $all[$k2] = $v2['addpay_sum_all'];
                    }
                }
            }
        }

        if(count($current)>0){
            foreach($current as $k=>$v){
                
                if($v!=$all[$k]) {
                    
                    $x = sprintf("%.2f", (float)$all[$k] - $v);
                    
                    end($result['ИнформацияоКлиентахвСделке:Клиенты']);
                    $last_key = key($result['ИнформацияоКлиентахвСделке:Клиенты']);
                    
                    $result['ИнформацияоКлиентахвСделке:Клиенты'][$last_key]['payments'][$k]['addpay_sum'] += $x;
                    $result['ИнформацияоКлиентахвСделке:Клиенты'][$last_key]['payments'][$k]['addpay_sum' . DocumentsGenerateModel::SUM_AS_TEXT] = $model->sumToText($result['Клиенты'][0]['payments'][$k]['addpay_sum']);
                    
                }
            }
        }
        
        //все значение пофиксили, выводим общую сумму числом и прописью
        foreach($result['ИнформацияоКлиентахвСделке:Клиенты'] as $k => $client) {
            if(isset($client['payments'])) {
                if(count($client['payments'])>0){
                    $x = 0;
                    foreach($client['payments'] as $k2 => $v2){
                        $x += $v2['addpay_sum'];
                    }
                    $result['ИнформацияоКлиентахвСделке:Клиенты'][$k]['payments_data'] = array(
                        'amount' => count($client['payments']),
                        'amount' . DocumentsGenerateModel::NUMBER_AS_TEXT => $model->nmbToText(count($client['payments'])), 
                        'sum' => $x,
                        'sum' . DocumentsGenerateModel::SUM_AS_TEXT => $model->sumToText($x),
                    );
                }
            }
        }
        
        
        return $result;
       
    }
    
    
    /**
     * Название нового документа
     */
    public static function getFilename($data, $extension_copy, $extension_copy_id, $upload_model){

        $result = false;
        $document_type = ($data['Документы:doc_newtype']) ? $data['Документы:doc_newtype'] : 'other';
        $deal_data = \DocumentsGenerateModel::getData(\ExtensionCopyModel::model()->modulesActive()->findByPk($extension_copy), $extension_copy_id, true);
        
        if($document_type=='Договор' || $document_type=='Счет'){
            
            $result = (isset($data['Документы:doc_number'])) ? $data['Документы:doc_number'] : $deal_data['module_title'];
            if(isset($data['Документы:Сделки:ИнформацияоКлиентахвСделке:Клиенты'][0]['module_title']))
                $result .= '_' . $data['Документы:Сделки:ИнформацияоКлиентахвСделке:Клиенты'][0]['module_title'];
              
        }else {
            $deal_title = $deal_data['module_title'] . '_';
            $template_filename = '';
            
            $uploads_parents = \DataModel::getInstance()->setFrom('{{uploads_parents}}')->setWhere("upload_id = " . $upload_model->id)->findRow();
            if($uploads_parents) {
                $parent_upload = \DataModel::getInstance()->setFrom('{{uploads}}')->setWhere("id = " . $uploads_parents['parent_upload_id'])->findRow();
                if($parent_upload){
                    $template_filename = pathinfo($parent_upload['file_name'], PATHINFO_FILENAME);
                }    
            }
            
            $result = $deal_title . $template_filename;
            
        }

        
        return $result;
    }    
    
    
    /**
     *  Дополнительная обработка данных, после сохранения карточки
     *  @param parent_data - родительские данные (запуск как СМ)
     *  @param copy_id - id модуля карточки
     *  @param copy_data_id - id самой сохраняемой записи
     *  @param linked_cards - массив, связь документа с финансами
     */
    public static function afterSave($parent_data, $copy_id, $copy_data_id, $is_new_card, $linked_cards=array()){
        
        //связь финансов с сооветственным документом
        if((count($linked_cards)==2) && !empty($parent_data['parent_data_id'])){
            \DataModel::getInstance()->Update('{{linked_cards}}', array('l_card_id_target' => $linked_cards[0]),  'l_card_id = ' . $linked_cards[1] . ' AND extension_copy_id = ' . DocumentsGenerateModelExt::MODULE_DEALS . ' AND l_extension_copy_id = ' . DocumentsGenerateModelExt::MODULE_FINANCES . ' AND l_extension_copy_id_target = 11 AND card_id = ' . $parent_data['parent_data_id']);
            return;
        }
        
        switch($copy_id) {
            
            /**
             * модуль Финансы
             */
            case DocumentsGenerateModelExt::MODULE_FINANCES:

                //доступ только через СМ
                if(empty($parent_data['parent_copy_id']) || empty($parent_data['parent_data_id']))
                    return;

                return self::afterSaveFinances($parent_data, $copy_id, $copy_data_id, $is_new_card);
                
            break;
            
            /**
             * модуль Объекты
             */
            case DocumentsGenerateModelExt::MODULE_OBJECTS:
                
                 return self::afterSaveObjects($parent_data, $copy_id, $copy_data_id, $is_new_card);
                 
            break;
            
            /**
             * модуль Дополнительные платежи
             */
            case DocumentsGenerateModelExt::MODULE_ADD_PAYMENTS:
                
                 if(empty($parent_data['parent_copy_id']) || empty($parent_data['parent_data_id']))
                    return;
                
                 return self::calculateContractPrice($parent_data);
                 
            break;
            
            /**
             * модуль Документы
             */
            case ExtensionCopyModel::MODULE_DOCUMENTS:
            
                if(empty($parent_data['parent_copy_id']) || empty($parent_data['parent_data_id']) || !$is_new_card) {
                    
                    $info_deal = \DataModel::getInstance()->setFrom('{{documents_templates_sdelkin_5}}')->setWhere('documents_id = ' . $copy_data_id)->findRow();

                    if(!empty($info_deal['sdelkin_id'])) {
                        $document = \DataModel::getInstance()->setFrom('{{documents_templates}}')->setWhere('documents_id = ' . $copy_data_id)->findRow();
                        if(!empty($document['doc_newtype']) && ($document['doc_newtype']==DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT || $document['doc_newtype']==DocumentsGenerateModelExt::BLOCK_ID_CONCESSION)) {
                            self::setDealNumber($info_deal['sdelkin_id'], $document['doc_number']);
                            return array('ev_refresh_fields'=>json_encode(array('deal_contract_number'=>$document['doc_number'])));
                        }else 
                            return;
                    }else
                        return;
            
                }
            
                return self::afterSaveDocuments($parent_data, $copy_id, $copy_data_id);
            
            break;
            
            default:
                return;
            break;
            
        }
        
    }
    
    
    /**
     *  Обработка после сохранения Финансов
     */
    private static function afterSaveFinances($parent_data, $copy_id, $copy_data_id, $is_new_card){
        
        $deal_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Сделки'));
        $finance_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Финансы'));
        
        if($deal_copy === null || $finance_copy === null)
            return;
        
        //проверка СМ Финансы модуля Сделки
        if($deal_copy->copy_id != $parent_data['parent_copy_id'] || $finance_copy->copy_id != $copy_id)
            return;
        
        
        if($is_new_card) {
            
            /* 
            //в качестве номера используем название
            $finance = \DataModel::getInstance()->setFrom('{{ms_base_finansy}}')->setWhere('finansy_id = ' . $copy_data_id)->findRow();
            if(!empty($finance['module_title']))
                \DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_serialnumber' => $finance['module_title']),  "finansy_id = " . $copy_data_id);
            */
            //в качестве номера - автоинкремент
            $finances_doc_number = 1;
            $last_finance = \DataModel::getInstance()->setFrom('{{ms_base_finansy}}')->setSelect('max(finances_doc_number) as max')->findRow();
            if(!empty($last_finance['max'])){
                $finances_doc_number += (int)$last_finance['max'];
            }
            DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_doc_number' => $finances_doc_number),  "finansy_id = " . $copy_data_id);
        }
        
        
        return self::dealUpdate($parent_data, $copy_data_id, $is_new_card);

    }
    
    
    /**
     *  Обновление сделки
     */
    private static function dealUpdate($parent_data, $copy_data_id, $is_new_card){
        
        $deal_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Сделки'));
        
        $deal_id_name = $deal_copy->prefix_name . '_id';
        $deals = \DocumentsGenerateModel::getData($deal_copy, 0, false, "deal_status_title <> '" . \DocumentsGenerateModelExt::$STATUSES_DEAL['closed_success'] . "' AND {$deal_copy->getTableName()}.$deal_id_name=" . $parent_data['parent_data_id']);

        if(count($deals)>0) {
            
            //текущая дата (дата, по отношению к которой идет расчет)               
            $current_date = date('Y-m-d H:i:s');      
            $current_data_obj = new DateTime($current_date);
                           
            $deal_schema = $deal_copy->getSchema();      
            
            foreach($deals as $deal) {
           
                $ev_refresh_fields = array();
           
                //ищем тип договора ДУДС
                $doc_agremeent_exist = self::getAgreementType($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name]);
                
                //загружаем финансы каждой сделки
                $finances = \DocumentsGenerateModel::collectSM($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name], array(), 'Финансы');
                               
                //расчет неустойки. и для крона и после сохранения финансов
                $deal_is_delayed = self::penaltyPayment($finances, $current_date, $current_data_obj, $doc_agremeent_exist, $deal[$deal_id_name]); 

                //отмечаем, имеет ли сделка просрочки, либо не имеет
                \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_is_delayed' => $deal_is_delayed),  "$deal_id_name = " . $deal[$deal_id_name]);

                $deal['deal_is_delayed'] = $deal_is_delayed;        
                $ev_refresh_fields['deal_is_delayed'] = $deal_is_delayed;       
                        
                        
                //долг сделки
                if($doc_agremeent_exist) {
                    $debt = self::getDebtsDeal($finances, $doc_agremeent_exist, $deal, $deal_copy, $deal_schema);   
                    if(empty($debt))
                        $debt = null;
                    \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_debt' => $debt),  "$deal_id_name = " . $deal[$deal_id_name]);

                    $ev_refresh_fields['deal_debt'] = $debt;     
                }
                
                //обновление статусов счетов
                $result = self::statusPayment($finances);
                
                $deal_is_payed = $result['deal_is_payed'];
                $deal_sum = $result['deal_sum'];

                if($deal['deal_status'] != \DocumentsGenerateModelExt::getDealStatusId(\DocumentsGenerateModelExt::$STATUSES_DEAL['sale'])) {
                    //сделка имеет статус не "Продажа" и не "Закрыта удачно"
                    //поле Стоимость имеет такое же значение, как и у прикрепленного Объекта
                    $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal['sdelkin_id'])->findRow();
                    if($sm_object) {
                        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
                        if($object) {
                            $deal_sum = $object['object_paramcontractprice'];
                        }
                    }
                    
                    if(empty($deal_sum))
                        $deal_sum = null;
                    \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_sum' => $deal_sum),  "$deal_id_name = " . $deal[$deal_id_name]);

                    $ev_refresh_fields['deal_sum'] = $deal_sum;    
                }
                          
                \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_is_payed' => $deal_is_payed),  "$deal_id_name = " . $deal[$deal_id_name]);

                $ev_refresh_fields['deal_is_payed'] = $deal_is_payed;
                $auto_next_card = false;

                //дополнительно, если запись финансов типа платеж, возвращаем true (ожидаем создание документа)
                if(count($finances)>0){
                    if(isset($finances['Финансы'][2])) {
                        foreach($finances['Финансы'][2] as $k => $v) {
                            if(isset($v['finances_typenew'])) {
                                if(($v['finansy_id'] == $copy_data_id) && ($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT)){
                                    $auto_next_card = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                return array('auto_next_card'=>$auto_next_card, 'ev_refresh_fields'=>json_encode($ev_refresh_fields));
            }    
        }
    }
    
    
    /**
     *  Обработка после сохранения Объектов
     */
    private static function afterSaveObjects($parent_data, $copy_id, $copy_data_id, $is_new_card){
     
        $deals = array();
     
        if(empty($parent_data['parent_copy_id']) || empty($parent_data['parent_data_id'])) {
            //сохранение не через СМ, находим сделки через связь
            $deals = self::getObjectDeals($copy_data_id);
        }else
            $deals []= $parent_data['parent_data_id'];
     
        return self::updateCostMeter($copy_data_id, $deals);
        
    }
    
    
    /**
     *  Обновление стоимости кв.м. в Счетах 
     * @param $object_id карточка объекта
     * @param $deals_ids сделки, привязанные к объекту
     */
    private static function updateCostMeter($object_id, $deals_ids){
        
        if(count($deals_ids)==0)
            return;
        
        $deal_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Сделки'));
        $finance_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Финансы'));
        
        if($deal_copy === null || $finance_copy === null)
            return;
        
        //проверяем права
        if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, DocumentsGenerateModelExt::MODULE_FINANCES, Access::ACCESS_TYPE_MODULE))
            return;

        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $object_id)->findRow();
        
        if(empty($object['object_parammetrprice']))
            return;

        $deal_id_name = $deal_copy->prefix_name . '_id';
        $deals = \DocumentsGenerateModel::getData($deal_copy, 0, false, "deal_status_title <> '" . \DocumentsGenerateModelExt::$STATUSES_DEAL['closed_success'] . "' AND {$deal_copy->getTableName()}.$deal_id_name in ('" . implode("','", $deals_ids) . "')");

        $current_date = date('Y-m-d H:i:s');      

        if(count($deals)>0) {
            $deal_schema = $deal_copy->getSchema();      
            foreach($deals as $deal) {
           
                //ищем тип договора ДУДС
                $doc_agremeent_exist = self::getAgreementType($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name]);
                
                if($doc_agremeent_exist) {
                    
                    //все финансы каждой сделки
                    $finances = \DocumentsGenerateModel::collectSM($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name], array(), 'Финансы');
                    
                    //финансы типа счет
                    $finances_bills = array();
                    
                    if(count($finances)>0){
                        if(isset($finances['Финансы'][2])) {
                            foreach($finances['Финансы'][2] as $k => $v) {             
                                if(isset($v['finances_typenew'])) {
                                    if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL){
                                        $finances_bills []= $v;
                                    }
                                }
                            }
                        }
                    }
                    
                    if(count($finances_bills)==0)
                        continue;
                    
                    foreach($finances_bills as $finance) {

                        if($doc_agremeent_exist==1) {
                            //фиксированный договор
                            if(!empty($finance['finances_invoice_is_delayed']) && $finance['finances_invoice_is_delayed']==1) {
                                //если есть просрочка, меняем стоимость метра + стоимость счета, иначе ничего не меняем
                                if(!empty($finance['finances_payment_square'])) {
                                    $finances_payment_metrecost = $object['object_parammetrprice'];
                                    $finances_sum = $finances_payment_metrecost * $finance['finances_payment_square'];
                                    \DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_payment_metrecost' => $finances_payment_metrecost, 'finances_sum' => $finances_sum),  "finansy_id = " . $finance['finansy_id']);
                                }
                            }
                         }else {
                            //нефиксированный договор
                            if(!empty($finance['finances_invoice_pay_date'])) {
                                if($finance['finances_invoice_pay_date'] < $current_date) {
                                    //если в счете стоит дата "оплатить до" и эта дата просрочена
                                    $finances_payment_metrecost = $object['object_parammetrprice'];
                                    $finances_sum = $finances_payment_metrecost * $finance['finances_payment_square'];
                                    \DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_payment_metrecost' => $finances_payment_metrecost, 'finances_sum' => $finances_sum),  "finansy_id = " . $finance['finansy_id']);
                                }
                            }
                        }   
                    }
                    
                    //пересчитываем долг сделки
                    $ev_refresh_fields = array();
                    
                    $debt = self::getDebtsDeal($finances, $doc_agremeent_exist, $deal, $deal_copy, $deal_schema);
                    if(empty($debt))
                        $debt = null;                    
                    \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_debt' => $debt),  "$deal_id_name = " . $deal[$deal_id_name]);                    
                    $ev_refresh_fields['deal_debt'] = $debt;     
                    
                    return array('ev_refresh_fields'=>json_encode($ev_refresh_fields));
                }
            }      
        }     
    }
    

    /**
     *  Пересчет стоимость договора в Объекте
     */
    private static function calculateContractPrice($parent_data){
                
        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $parent_data['parent_data_id'])->findRow();
        
        if(empty($object['kvartiry_id']))
            return;
        
        //объект найден, ищем дополнительные все дополнительные платежи объекта
        $object_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Объекты'));
        $sum_add_payments = 0;

        $add_payments = \DocumentsGenerateModel::collectSM($object_copy->getSchema(), $parent_data['parent_copy_id'], $parent_data['parent_data_id'], array(), 'Доп_платежи');

        if(count($add_payments)>0){
            if(isset($add_payments['Доп_платежи'][2])) {
                foreach($add_payments['Доп_платежи'][2] as $k => $v) {             
                    if(!empty($v['addpay_sum'])) {
                        $sum_add_payments += $v['addpay_sum'];
                    }
                }
            }
        }
        
        $square = $object['object_paramgeneralarea'];
        
        if(!empty($object['object_type']) && $object['object_type']==1) {
            //квартира
            if(!empty($object['object_paramcalcarea']))
                $square = $object['object_paramcalcarea'];
            
            if(!empty($object['object_bti_calcarea']))
                $square = $object['object_bti_calcarea'];
            
        }else {
            //другие типы
            if(!empty($object['object_paramprojectarea']))
                $square = $object['object_paramprojectarea'];
            
            if(!empty($object['object_bti_paramprojectarea']))
                $square = $object['object_bti_paramprojectarea'];
        }
        
        $object_paramcontractprice = sprintf("%.2f", (int)$object['object_parammetrprice'] * $square + $sum_add_payments);

        \DataModel::getInstance()->Update('{{ms_base_kvartiry}}', array('object_paramcontractprice' => $object_paramcontractprice),  'kvartiry_id = ' . $parent_data['parent_data_id']);
        
        return array('ev_refresh_fields'=>json_encode(array('object_paramcontractprice'=>$object_paramcontractprice)));
        
    }
    
    
    /**
     *  Постизменение полей после сохранения Документа
     */
    private static function afterSaveDocuments($parent_data, $copy_id, $copy_data_id){
          
        $document = \DataModel::getInstance()->setFrom('{{documents_templates}}')->setWhere('documents_id = ' . $copy_data_id)->findRow();
        
        if(!empty($document['doc_newtype']) && ($document['doc_newtype']==DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT || $document['doc_newtype']==DocumentsGenerateModelExt::BLOCK_ID_CONCESSION)) {
            
            $doc_number = array(date('y'));
            $deal = \DataModel::getInstance()->setFrom('{{ms_base_sdelkin}}')->setWhere('sdelkin_id = ' . $parent_data['parent_data_id'])->findRow();
        
            if(!empty($deal['sdelkin_id'])){
                $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal['sdelkin_id'])->findRow();
                if(!empty($sm_object['kvartiry_id'])){
                    $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
                    if(!empty($object['kvartiry_id'])) {
                         $sm_doma = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_doma_1}}')->setWhere('kvartiry_id = ' . $object['kvartiry_id'])->findRow();
                         if(!empty($sm_doma['doma_id'])) {
                            $doma = \DataModel::getInstance()->setFrom('{{ms_base_doma}}')->setWhere('doma_id = ' . $sm_doma['doma_id'])->findRow();
                            if(!empty($doma['module_title'])) {
                                $doc_number []= $doma['module_title'];
                            }
                        }
                        if(!empty($object['object_buildnumber'])){
                            $doc_number []= str_pad($object['object_buildnumber'], 3, "0", STR_PAD_LEFT);
                        }
                        if(!empty($deal['module_title'])){
                            $doc_number []= mb_substr($deal['module_title'], -4);
                        }
                    }
                }
            }
            $doc_number = implode('-', $doc_number);
            
            self::setDealNumber($deal['sdelkin_id'], $doc_number);
            \DataModel::getInstance()->Update('{{documents_templates}}', array('doc_number' => $doc_number),  'documents_id = ' . $copy_data_id);
        
            return array('ev_refresh_fields'=>json_encode(array('deal_contract_number'=>$doc_number)));
        }
    }
    
    
    /**
     *  Номер договора сделки 
     */
    public static function setDealNumber($deal_id, $number){
        
        \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_contract_number' => $number),  'sdelkin_id = ' . $deal_id);
    
    }
    
    
    /**
     *  Стоимость сделки 
     */
    public static function setDealSum($deal_id, $sum){
        
        if(empty($sum))
            $sum = null;
        \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_sum' => $sum),  'sdelkin_id = ' . $deal_id);
    
    }
    
    
    /**
     *  Ежедневный крон 
     *  Расчет долга и неустойки сделки
     */
    public static function daily($deal_id=false, $date=false, $manual=false){
        
        ini_set('max_execution_time', 3600); // 1ч
               
        \LogModel::getInstance()
            ->setFileName('/var/www/html/1C/cron.txt') //c:\1.txt
            ->setTypeWriting(FILE_APPEND)
            ->setDateTime(true)
            ->start('cron started');
            
        if($manual)
            echo date('d.m.Y H:i:s', time()) . ' cron started<br/>';
        
        $deal_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Сделки'));
        $finance_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Финансы'));
        
        if($deal_copy === null || $finance_copy === null)
            return;
        
        $deal_id_name = $deal_copy->prefix_name . '_id';

        $cond = ($deal_id) ? "AND {$deal_copy->getTableName()}.$deal_id_name = $deal_id" : '';
        
        $deals = \DocumentsGenerateModel::getData($deal_copy, 0, false, "deal_status_title <> '" . \DocumentsGenerateModelExt::$STATUSES_DEAL['closed_success'] . "' $cond");

        if(count($deals)>0) {
            
            //текущая дата (дата, по отношению к которой идет расчет)           
          
            $current_date = date('Y-m-d H:i:s');      
            $current_data_obj = new DateTime($current_date);

            $deal_schema = $deal_copy->getSchema();      
            
            foreach($deals as $deal) {
           
                //тип договора
                $doc_agremeent_exist = self::getAgreementType($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name]);

                //загружаем финансы каждой сделки
                $finances = \DocumentsGenerateModel::collectSM($deal_schema, $deal_copy->copy_id, $deal[$deal_id_name], array(), 'Финансы');
                               
                //расчет неустойки
                $deal_is_delayed = self::penaltyPayment($finances, $current_date, $current_data_obj, $doc_agremeent_exist, $deal[$deal_id_name], $date); 

                //отмечаем, имеет ли сделка просрочки, либо не имеет
                \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_is_delayed' => $deal_is_delayed),  "$deal_id_name = " . $deal[$deal_id_name]);

                $deal['deal_is_delayed'] = $deal_is_delayed;        

                //долг сделки
                if($doc_agremeent_exist) {
                    $debt = self::getDebtsDeal($finances, $doc_agremeent_exist, $deal, $deal_copy, $deal_schema);   
                    if(empty($debt))
                        $debt = null;
                    \DataModel::getInstance()->Update($deal_copy->getTableName(), array('deal_debt' => $debt),  "$deal_id_name = " . $deal[$deal_id_name]);
                }
            }    
        }

        \LogModel::getInstance()
            ->setFileName('/var/www/html/1C/cron.txt')
            ->setTypeWriting(FILE_APPEND)
            ->setDateTime(true)
            ->start('cron finished, deals processed: ' . count($deals));
            
         if($manual)
            echo date('d.m.Y H:i:s', time()) . ' cron finished, deals processed: ' . count($deals);
        
    }
    
    
    /**
     * Получаем тип договора, фиксированный договор по сделке или нет
     * проверяем по договору типа ДУДС
     */
    public static function getAgreementType($schema, $copy_id, $copy_data_id){
        
        $result = false;
        $documents = \DocumentsGenerateModel::collectSM($schema, $copy_id, $copy_data_id, array(), 'Документы', false);

        if(count($documents)>0){
            if(isset($documents['Документы'][2])) {
                foreach($documents['Документы'][2] as $k => $v) {
                    if($v['doc_newtype'] == DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT){
                        $result = ($v['documents_doc_contract_pay_type_title']=='Фиксированный') ? 1 : 2;
                        break;
                    }
                }
            }
        }
        
        return $result;
        
    }
    
    
    /**
     * Обновление статусов счетов
     */
    private static function statusPayment($finances){

        //сделка оплачена
        $deal_is_payed = 1;
        
        //сумма сделки
        $deal_sum = 0;
    
        if(count($finances)>0){
            
            //счета
            $bills = array();
            
            //платежи
            $payments = array();
            
            if(isset($finances['Финансы'][2])) {
                
                $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($finances['Финансы'][0]);

                foreach($finances['Финансы'][2] as $k => $v) {
                    
                    if(isset($v['finances_typenew']) && isset($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'])) {
                        
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL){

                            //добавляем все счета
                            if(!empty($v['finances_invoice_pay_date']))
                                $bills[$v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']][] = $v;
                            
                        }
                        
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT){

                            if(empty($payments[$v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']]))
                                    $payments[$v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']] = 0;
                                
                                
                            //добавляем все платежи
                            
                            //для типа площади считаем площадь, для остальных - сумму
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                 if(!empty($v['finances_payment_square']))
                                    $payments[$v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']] += $v['finances_payment_square']; 
                                
                            }else {
                                if(!empty($v['finances_sum']))
                                    $payments[$v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']] += $v['finances_sum']; 
                            }
                        }
                    }
                }
            } 
            
            //сортируем счета
            if(count($bills)>0){
                foreach($bills as $k=>$bill) {
                    self::sort($bill, 'finances_invoice_pay_date');
                    $bills[$k] = $bill;
                }
            }
            
            //получены все платежи и счета, обновляем статусы
            
            if(count($bills)>0) {
                foreach($bills as $type_of_bill=> $bills_of_type) {
                    
                    $rest = 0;
                    
                    if(!empty($payments[$type_of_bill]))
                        $rest = $payments[$type_of_bill];
                    
                    if(count($bills_of_type)>0){
                        foreach($bills_of_type as $bill) {
                            
                            //сумма текущего счета
                            $current_sum = ($type_of_bill=='1') ? $bill['finances_payment_square'] : $bill['finances_sum'];
                            
                            if(empty($current_sum))
                                $current_sum = 0;
                            
                            $rest -= $current_sum;
                            
                            $finances_status = ($rest >= 0) ? \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['paid']) : \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['planned']);
                            
                            //если нет оплаты по типу "Площадь" или "Дополнительный платеж", то сделка не оплачена
                            if(in_array($type_of_bill, array('1', '3')) && ($rest < 0))
                                $deal_is_payed = 2;
                            
                            //сумму сделки считаем только по типам "Площадь" и "Дополнительный платеж"
                            if(in_array($type_of_bill, array('1', '3')))
                                $deal_sum += $bill['finances_sum'];

                            \DataModel::getInstance()->Update($extension_copy->getTableName(), array('finances_status' => $finances_status),  "{$extension_copy->prefix_name}_id = " . $bill[$extension_copy->prefix_name . '_id']);
                        }
                    }    
                }
            }
        }
        
        return array('deal_is_payed'=>$deal_is_payed, 'deal_sum'=>$deal_sum);
        
    }

    public static function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    /**
     * Расчет неустойки
     */
    private static function penaltyPayment($finances, $current_date, $current_data_obj, $doc_agremeent_exist, $deal_id, $date_override=false){

        $current_date_payment = $current_date;
        
        if($date_override && self::validateDate($date_override)) {
            //указана дата "задним числом" и она корректна
            $current_data_obj = new DateTime($date_override);
            $current_date = $date_override;
        }
         
        
        
        //получаем стоимость метра из привязанного объекта
        $object_meter_cost = 0;
        $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal_id)->findRow();
        if($sm_object) {
            $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
            if($object) {
                $object_meter_cost = $object['object_parammetrprice'];
            }
        }
        
        //сделка не имеет задолженность
        $deal_is_delayed = 2;

        if(count($finances)>0){
            
            //счета (для типа Площадь)
            $bills_square = array();
            
            //платежи (для типа Площадь)
            $payments_square = array();
            
            //счет и платеж (для типа Дополнительный платеж)
            $bills_payment = array();
            $payments_payment = array();
            
            //оплаченные метры
            $payments_square_meters = 0;
            
            if(isset($finances['Финансы'][2])) {
                foreach($finances['Финансы'][2] as $k => $v) {
                                     
                    if(isset($v['finances_typenew']) && isset($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'])) {
                        
                        //указан тип финансов и его назначение
                        
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL){
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                //счет типа площадь
                                if(!empty($v['finances_invoice_pay_date'])){
                                    
                                    if($v['finances_invoice_pay_date']<=$current_date) {
                                        $bills_square []= $v;
                                    }else {
                                        //"будущая" дата, очищаем данные просрочки
                                        \DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_invoice_is_delayed' => null, 'finances_invoice_delay_days' => null, 'finances_invoice_penalty' => null),  "finansy_id = " . $v['finansy_id']);
                                    }

                                }
                            }
                            
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3'){
                                //счет типа Дополнительный платеж
                                if(!empty($v['finances_invoice_pay_date'])){
                                    if($v['finances_invoice_pay_date']<=$current_date) {
                                        $bills_payment []= $v;
                                    }else {
                                        //"будущая" дата, очищаем данные просрочки
                                        \DataModel::getInstance()->Update('{{ms_base_finansy}}', array('finances_invoice_is_delayed' => null, 'finances_invoice_delay_days' => null, 'finances_invoice_penalty' => null),  "finansy_id = " . $v['finansy_id']);
                                    }
                                }
                            }
                        }
                        
                        if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT){
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                //платеж типа площадь
                                if(!empty($v['finances_date'])){
                                    if($v['finances_date']<=$current_date_payment)
                                        $payments_square []= $v;
                                }
                            }
                            
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3'){
                                //платеж типа Дополнительный платеж
                                if(!empty($v['finances_date'])){
                                    if($v['finances_date']<=$current_date_payment)
                                        $payments_payment []= $v;
                                }
                            }
                        }
                        //print_r($v);
                    }
                }
            }

            if(count($bills_square)>0) {
            
                //у нас получены массивы счетов и платежей, рассчитываем неустойку
                
                $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($finances['Финансы'][0]);
                
                //1.получаем общую сумму оплаты на дату расчета
                $payment = 0;
                if(count($payments_square)>0)
                    foreach($payments_square as $v)
                        if(!empty($v['finances_sum']))
                            $payment += $v['finances_sum'];
                      
                $payment_add = 0;
                if(count($payments_payment)>0)
                    foreach($payments_payment as $v)
                        if(!empty($v['finances_sum']))
                            $payment_add += $v['finances_sum'];

                self::sort($bills_square, 'finances_invoice_pay_date');       

                if($payments_square)
                    self::sort($payments_square, 'finances_date'); //сортируем по дате оплаты
                
                //2. получаем ключевую ставку
                $stavka = 0.1;
                $parameters_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Параметры'));
                if($parameters_copy !== null) {
                    $params = \DocumentsGenerateModel::getData($parameters_copy, 0, false, 'module_title = "Ключевая ставка"');
                    if(count($params))
                        $stavka = $params[0]['param_value'];
                    
                }    

                //3. рассчитываем возможный долг и неустойку для каждого счета
                $sum = 0;
                foreach($bills_square as $k => $v) {
                    
                    //берем стоимость из определенного поля
                    $current_invoice_sum = (!empty($v['finances_sum'])) ? $v['finances_sum'] : 0;
                    $current_square = (!empty($v['finances_payment_square'])) ? $v['finances_payment_square'] : 0;

                    if($current_invoice_sum > 0) {
                        //$sum += $current_invoice_sum;
                        
                        $current_invoice_data_obj = new DateTime($v['finances_invoice_pay_date']);
                        $diff = $current_invoice_data_obj->diff($current_data_obj);
                        $delay_days = $diff->days;
                        
                        if($delay_days) {
                            //есть просрочка
                            $sum += $current_square * $object_meter_cost;
                        }else {
                            //просрочки нет
                            if($doc_agremeent_exist == 1) {
                                //договор фиксированный
                                $current_meter_cost = (!empty($v['finances_payment_metrecost'])) ? $v['finances_payment_metrecost'] : 0;
                                $sum += $current_square * $current_meter_cost;
                            }
                            
                            if($doc_agremeent_exist == 2) {
                                //договор нефиксированный
                                if($payments_square){
                                    //платежи есть, берем стоимость из последнего по дате оплаты
                                    $last_meter_cost = (!empty(end($payments_square)['finances_payment_metrecost'])) ? end($payments_square)['finances_payment_metrecost'] : 0;
                                    $sum += $current_square * $last_meter_cost;
                                }else {
                                    //платежей нет
                                    $sum += $current_square * $object_meter_cost;
                                }
                            }
                            
                        }
                        
                        $current_invoice_dolg = $sum - $payment;

                        if($current_invoice_dolg>0){
                            //есть долг, рассчитываем неустойку
                            $is_delayed = 1;
                            $param = ($current_invoice_dolg>$current_invoice_sum) ? $current_invoice_sum : $current_invoice_dolg;
                            $penalty = $diff->days * $stavka * $param / 300;
                            
                            $deal_is_delayed = 1;    
                            
                            $finanse_status = \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['planned']);
                        }else {
                            //долга нет
                            $is_delayed = 2;
                            $delay_days = 0;//'';
                            $penalty = 0;//'';
                            
                            $finanse_status = \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['paid']);
                        }

                        \DataModel::getInstance()->Update($extension_copy->getTableName(), array('finances_status' => $finanse_status, 'finances_invoice_is_delayed' => $is_delayed, 'finances_invoice_delay_days' => $delay_days, 'finances_invoice_penalty' => $penalty),  "{$extension_copy->prefix_name}_id = " . $v[$extension_copy->prefix_name . '_id']);

                    }
                }
                
                $sum = 0;
                foreach($payments_payment as $k => $v) {
                    $current_invoice_sum = (!empty($v['finances_sum'])) ? $v['finances_sum'] : 0;
                    if($current_invoice_sum > 0) {
                        $sum += $current_invoice_sum;
                        $current_invoice_dolg = $sum - $payment_add;
                        
                        $is_delayed = 2;
                        $delay_days = 0;//'';
                        $penalty = 0;//'';
                        
                        if($current_invoice_dolg>0){
                            
                            //есть долг, рассчитываем неустойку
                            
                            $current_invoice_data_obj = new DateTime($v['finances_invoice_pay_date']);
                            
                            $diff = $current_invoice_data_obj->diff($current_data_obj);
                            
                            $is_delayed = 1;
                            $delay_days = $diff->days;
                            
                            $param = ($current_invoice_dolg>$current_invoice_sum) ? $current_invoice_sum : $current_invoice_dolg;
                            $penalty = $diff->days * $stavka * $param / 300;
                            
                            $deal_is_delayed = 1;
                               
                        }
                        
                        \DataModel::getInstance()->Update($extension_copy->getTableName(), array('finances_invoice_is_delayed' => $is_delayed, 'finances_invoice_delay_days' => $delay_days, 'finances_invoice_penalty' => $penalty),  "{$extension_copy->prefix_name}_id = " . $v[$extension_copy->prefix_name . '_id']);
                        
                    }
                }
                
                //для счетов типа Площадь считаем неоплаченные метры
                if($payments_square){
                    foreach($payments_square as $k => $v) {
                        $current_square = (!empty($v['finances_payment_square'])) ? $v['finances_payment_square'] : 0;
                        $payments_square_meters += $current_square;
                    }
                }

                foreach($bills_square as $k => $v) {
                    if(!empty($v['finances_payment_square'])) {
                        //$current_unpaid = $v['finances_payment_square'];
                        if($payments_square_meters >= $v['finances_payment_square']) {
                            $current_unpaid = 0;
                            $payments_square_meters = $payments_square_meters - $v['finances_payment_square'];
                        }else {
                            $current_unpaid = $v['finances_payment_square'] - $payments_square_meters;
                            $payments_square_meters = 0;
                        }
                        
                        \DataModel::getInstance()->Update($extension_copy->getTableName(), array('finances_invoice_unpaid_square' => $current_unpaid),  "{$extension_copy->prefix_name}_id = " . $v[$extension_copy->prefix_name . '_id']);

                    }
                }
            }
        }

        return $deal_is_delayed;
        
    }    
    
    
    /**
     * Расчет долга сделки
     *
     * @finances Финансы сделки
     * @type_of_pay тип оплаты, 1 - фиксированный договор, 2 - нефиксированный
     * @deal сделка (deal_is_delayed 1 - есть задолженность, 2 - задолженности нет) false - не выводим общий долг
     */
    private static function getDebtsDeal($finances, $type_of_pay, $deal=false, $deal_copy=false, $deal_schema=false){
        
        $result = false;

        //долг по типам
        $debts = array('type_1'=>array(     //Площадь
                            'square'=> array('bill'=>0, 'pay'=>0),
                            'sum'   => array('bill'=>0, 'pay'=>0)), 
                       'type_13'=>array(    //Оплата за перепланировку
                            'square'=> array('bill'=>0, 'pay'=>0),
                            'sum'   => array('bill'=>0, 'pay'=>0)), 
                       'type_16'=>array(    //Отложенный платеж
                            'square'=> array('bill'=>0, 'pay'=>0),
                            'sum'   => array('bill'=>0, 'pay'=>0)), 
                       'type_14'=>array(    //Оплата за переустройство
                            'square'=> array('bill'=>0, 'pay'=>0),
                            'sum'   => array('bill'=>0, 'pay'=>0)), 
                       'type_3'=>array(     //Дополнительный платеж
                            'square'=> array('bill'=>0, 'pay'=>0),
                            'sum'   => array('bill'=>0, 'pay'=>0)), 
                    );
                    
        //разница между площадью счетов и платежей типа Площадь            
        $difference_meters_square = 0;            
        
        //разница между суммой счетов и платежей типа Дополнительный платеж            
        $difference_sum_add_payment = 0;    
        
        //массив счетов типа площадь
        $bills_square = array();
        
        //массив платежей типа площадь
        $payments_square = array();
        
        //разница между суммой счетов и платежей типа Оплата за перепланировку и Оплата за переустройство
        $difference_sum_add_other = 0;
        
        if(count($finances)>0){
            if(isset($finances['Финансы'][2])) {
                foreach($finances['Финансы'][2] as $k => $v) {
                    
 
                    //вывод долга по типам платежей
                    
                    if(isset($v['finances_typenew']) && isset($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'])) {
                        
                        if(in_array($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'], array('1', '13', '16', '14', '3'))){
                        
                            $is_bill = ($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL) ? true : false;
                            $is_pay = ($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT) ? true : false;
                            
                            if($is_bill) {
                                $debts['type_' . $v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']]['square']['bill'] += $v['finances_payment_square'];
                                $debts['type_' . $v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']]['sum']['bill'] += $v['finances_sum'];
                            }
                            if($is_pay) {
                                $debts['type_' . $v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']]['square']['pay'] += $v['finances_payment_square'];
                                $debts['type_' . $v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']]['sum']['pay'] += $v['finances_sum'];
                            
                            }
                            
                            
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1') {
                                //площадь
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL) {
                                    $difference_meters_square += $v['finances_payment_square'];
                                    if(!empty($v['finances_invoice_pay_date']))
                                        $bills_square[] = $v;
                                }
                                
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT) {
                                    $difference_meters_square -= $v['finances_payment_square'];
                                    if(!empty($v['finances_date']))
                                        $payments_square[] = $v;
                                }
                            }
                            
                            if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3') {
                                //Дополнительный платеж
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL)
                                    $difference_sum_add_payment += $v['finances_sum'];
                                
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT)
                                    $difference_sum_add_payment -= $v['finances_sum'];  
                            }
                            
                            if(in_array($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id'], array('13', '14'))){
                                //Оплата за перепланировку и Оплата за переустройство
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL)
                                    $difference_sum_add_other += $v['finances_sum'];
                                
                                if($v['finances_typenew'] == DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT)
                                    $difference_sum_add_other -= $v['finances_sum'];
                            }
                            
                        }

                    } 
                        
                }
                
                if($deal) {
                    
                    $all_debt = 0;

                    if($deal['deal_is_delayed']==1) {

                        //сделка имеет задолженность
                        
                        //получаем текущую стоимость кв. метра из объекта
                        $meter_cost = 0;
                        $objects = \DocumentsGenerateModel::collectSM($deal_schema, $deal_copy->copy_id, $deal['sdelkin_id'], array(), 'Объекты');
                        
                        if(isset($objects['Объекты'][2][0]['object_parammetrprice']))
                            $meter_cost = $objects['Объекты'][2][0]['object_parammetrprice'];
                        
                        $all_debt = $difference_meters_square * $meter_cost + $difference_sum_add_payment + $difference_sum_add_other;
                        
                        
                    }elseif($deal['deal_is_delayed']==2) {
                        
                        //сделка задолженности не имеет
                        
                        if($type_of_pay == 2) {
                            
                            //договор нефиксированный
                            
                            $meter_cost = 0;

                            if(count($payments_square)>0) {
                                self::sort($payments_square, 'finances_date');
                                $meter_cost = end($payments_square)['finances_payment_metrecost'];
                            }else {
                                
                                //берем стоимость метра из первого счета
                                if(count($bills_square)>0) {
                                    self::sort($bills_square, 'finances_invoice_pay_date');
                                    $meter_cost = $bills_square[0]['finances_payment_metrecost'];
                                }

                                /*
                                //платежей нет, берем стоимость метра из объекта
                                $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal['sdelkin_id'])->findRow();
                                if($sm_object) {
                                    $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
                                    if($object) {
                                        $meter_cost = $object['object_parammetrprice'];
                                    }
                                }
                                */
                            }

                            $all_debt = $difference_meters_square * $meter_cost + $difference_sum_add_payment;
  
                        }else {
                            
                            //договор фиксированный
                            
                            $all_debt = $debts['type_1']['sum']['bill'] + $debts['type_13']['sum']['bill'] + $debts['type_16']['sum']['bill'] + $debts['type_14']['sum']['bill'] + $debts['type_3']['sum']['bill'];
                            $all_debt -= $debts['type_1']['sum']['pay'] + $debts['type_13']['sum']['pay'] + $debts['type_16']['sum']['pay'] + $debts['type_14']['sum']['pay'] + $debts['type_3']['sum']['pay'];
                            
                        }
                        
                    }

                    return $all_debt;
                
                }else 
                    return $debts;
                
            }
            
        }
        

        return $result;   
   
    }
    
    
    /**
     *  Удаление записи финансов, для которой не был создан документ
     */
    public static function clearRubbish($card_id){
        
        $where = 'l_card_id_target IS NULL';
        
        if(!empty($card_id))
            $where .= ' AND l_card_id <> ' . $card_id;
            
        $data = \DataModel::getInstance()->setFrom('{{linked_cards}}')->setWhere($where)->findAll();
        DocumentsGenerateModelExt::deleteFromSubModule($data, '', false);
        
    }
        
    public static function addLinkedCard($card_id, $parent_card_id){
        
        \DataModel::getInstance()->Insert('{{linked_cards}}', array('extension_copy_id' => DocumentsGenerateModelExt::MODULE_DEALS, 'card_id' => $parent_card_id, 'l_extension_copy_id' => DocumentsGenerateModelExt::MODULE_FINANCES, 'l_card_id' => $card_id, 'l_extension_copy_id_target' => 11, 'l_card_id_target' => null));
        
        return array(
            'disable_new_card'      => true,
            'only_specific_block'   => DocumentsGenerateModelExt::BLOCK_ID_BILL_PAYMENT,
        );
        
    }
    
    
    /**
     *  При удалении Финансов типа счет, удаляем связанный с ним Документ
     */
    public static function clearLinked($l_extension_copy_id, $extension_copy_id, $card_id, $l_card_id){
        
        if(!empty($l_extension_copy_id)) {
            switch($l_extension_copy_id) {
            
                case DocumentsGenerateModelExt::MODULE_FINANCES:
                
                    if(count($l_card_id)) {
                        $data = \DataModel::getInstance()->setFrom('{{linked_cards}}')->setWhere("l_card_id_target IS NOT NULL AND l_extension_copy_id_target = 11 AND l_extension_copy_id = " . $l_extension_copy_id . " AND extension_copy_id = " . $extension_copy_id . " AND card_id = " . $card_id . " AND l_card_id in('" . implode("','", $l_card_id) .  "')")->findAll();
                        DocumentsGenerateModelExt::deleteFromSubModule($data, '_target');
                    }
                
                    if(!empty($extension_copy_id) && !empty($card_id)) {
                        //обновляем сделку
                        $parent_data = array(
                            'parent_copy_id'  => $extension_copy_id,
                            'parent_data_id'  => $card_id,
                        );
                        self::dealUpdate($parent_data, false, false);
                    }
                break;
                
                case DocumentsGenerateModelExt::MODULE_ADD_PAYMENTS:
                
                    if(!empty($extension_copy_id) && !empty($card_id)) {
                        //обновляем объект
                        $parent_data = array(
                            'parent_copy_id'  => $extension_copy_id,
                            'parent_data_id'  => $card_id,
                        );
                        return self::calculateContractPrice($parent_data);
                    }
                
                break;
                
                case DocumentsGenerateModelExt::MODULE_OBJECTS:
                
                    if(!empty($card_id)) {
                        //обнуляем поле Стоимость в модуле Сделки
                        self::setDealSum($card_id, 0);
                        return array('ev_refresh_fields'=>json_encode(array('deal_sum'=>0)));
                    }
                    
                break;
                
                default:
                
                break;
                
            }
        }
    }
    
    
    /**
     *  Обновление карточки после изменения СМ
     */
    public static function updateSubModule($copy_id, $parent_copy_id, $parent_data_id, $card_ids){
        
        if(!empty($copy_id)) {
            switch($copy_id) {
            
                case DocumentsGenerateModelExt::MODULE_OBJECTS:

                    if(!empty($parent_data_id) && !empty($card_ids[0])) {
                        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $card_ids[0])->findRow();
                        if(!empty($object['kvartiry_id'])) {
                            //обновляем поле Стоимость в модуле Сделки
                            self::setDealSum($parent_data_id, $object['object_paramcontractprice']);
                            return array('ev_refresh_fields'=>json_encode(array('deal_sum'=>sprintf("%.2f",$object['object_paramcontractprice']))));
                        }
                    }
                    
                break;
                
                default:
                
                break;
                
            }
        }
    }
    

    /**
     *  Удаление
     */
    public static function deleteFromSubModule($data_model, $fld_add='', $delete=true){
        
       if(count($data_model)){
            foreach($data_model as $row){
                
                $primary_entities = array(
                    'primary_pci' => $row['extension_copy_id'],
                    'primary_pdi' => $row['card_id'],
                );
                
                if($delete) {
                    
                    SubModuleDeleteModel::getInstance()
                                    ->setPrimaryEntities($primary_entities)
                                    ->setThisTemplate(0)
                                    ->delete(
                                            $row['extension_copy_id'],
                                            $row['card_id'],
                                            $row['l_extension_copy_id' . $fld_add],
                                            array($row['l_card_id' . $fld_add])
                                        );
                                        
                }                        
                                        
                
                \DataModel::getInstance()->delete('{{linked_cards}}', 'id = ' . $row['id']);
                
            }
        }  
    }
    
    
    /**
     *  Данные карточки Финансы для карточки Документы
     */
    public static function getDataFromLinkedCard($card_id){
        
        $result = array();
           
        $data = \DataModel::getInstance()->setFrom('{{ms_base_finansy}}')->setWhere('finansy_id = ' . $card_id)->findRow();
        if($data) {
            $result['doc_number'] = (int)$data['finances_doc_number'];
            $result['doc_invoice_payment_square'] = $data['finances_payment_square'];
            $result['doc_invoice_payment_metrecost'] = $data['finances_payment_metrecost'];
            $result['doc_invoice_payment_sum'] = $data['finances_sum'];
            
            $result['doc_date'] = $data['finances_date'];
            $result['doc_invoice_payment_date'] = $data['finances_date'];
            
            $result['doc_invoice_payment_is_cashless'] = ($data['finances_format'] == '2') ? 1 : 0; 
            
            $related_data = \DataModel::getInstance()->setFrom('{{ms_base_finansy_tipy_schetov_i_plate_3}}')->setWhere('finansy_id = ' . $card_id)->findRow();
            $result['doc_invoice_payment_destination'] = ($related_data) ? $related_data['tipy_schetov_i_plate_id'] : '';
            
        }
        
        return $result;
    }
    
    
    /**
     *  Расширения действий для кнопки в listview
     */
    public static function getAdditionalBtnActions($extension_copy){
        
        $result = false;
        
        switch($extension_copy->copy_id) {
            
            case DocumentsGenerateModelExt::MODULE_OBJECTS:
                //модуль Объекты
                \ExtensionCopyModel::model()->findByPk(DocumentsGenerateModelExt::MODULE_FINANCES)->getModule(false);
                $result = array(
                    //обновляем финансы типа счет, связанные через модуль Сделки
                    //права такие же, как у редактирования. если пользователь может редактировать карточку, то может обновить счет
                    'update_finances' => array('class' => 'list_view_btn-additional_update', 'title' => Yii::t('FinancesModule.base', 'Update cost sq.m. in the Bills'), 'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EDIT),
                    //генерация excel файла с расчетами выплат
                    'table_sr_export' => array('class' => 'list_view_btn-table_sr_export', 'title' => Yii::t('FinancesModule.base', 'Table surcharges, refunds'), 'permission_name' => PermissionModel::PERMISSION_DATA_RECORD_EXPORT),
                );
            break;
            
            default:
            
            break;
            
        }
       
        return $result;
        
    }
        
    
    /**
     *  Обновление карточек финансов типа Счет, привязанных к объекту
     */
    public static function additionalUpdate($copy_id, $objects_ids){
        
        if($copy_id!=DocumentsGenerateModelExt::MODULE_OBJECTS)
            return;
        
        $validate = new Validate();
        
        if(!empty($objects_ids)){
            
            foreach($objects_ids as $object_id) 
                self::updateCostMeter($object_id, self::getObjectDeals($object_id));

            $validate->addValidateResult('i', Yii::t('messages', 'The bills have been updated'));
            $result = array(
                'messages' => $validate->getValidateResultHtml(),
            );
        } else { 
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined data parameters'));
            $result = array(
                    'messages' => $validate->getValidateResultHtml(),
            );
        }
        
        return $result;
        
    }    
    
    
    /**
     *  Выгрузка таблицы возврат-доплат
     */
    public static function SRExport($copy_id, $objects_ids, $all_cards){
        
        \ExtensionCopyModel::model()->findByPk(DocumentsGenerateModelExt::MODULE_DEALS)->getModule(false);
        SRExport::getInstance()
            ->setIds($objects_ids, $all_cards)
            ->prepareDocument()
            ->getDocument('excel');
        
    }
    
    
    /**
     *  Получаем сделки определенного объекта
     */
    public static function getObjectDeals($object_id){
        
        $deals = array();
         
        $data = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('kvartiry_id = ' . $object_id)->findAll();
        
        if(count($data)) 
            foreach($data as $row)
                $deals []= $row['sdelkin_id'];
          
        return $deals;        
        
    }
    
    
    /**
     *  Получаем статус сделки по его названию
     */
    public static function getDealStatusId($title){
        
        $id = 0;
        $data = \DataModel::getInstance()->setFrom('{{ms_base_sdelkin_deal_status}}')->setWhere("deal_status_title = '$title'")->findRow();
        
        if(!empty($data['deal_status_id']))
            $id = $data['deal_status_id'];
                
        return $id;        
        
    }
    
    
    public static function getFinanceStatusId($title){
        
        $id = 0;
        $data = \DataModel::getInstance()->setFrom('{{ms_base_finansy_finances_status}}')->setWhere("finances_status_title = '$title'")->findRow();
        
        if(!empty($data['finances_status_id']))
            $id = $data['finances_status_id'];
                
        return $id;        
        
    }
    
    
    public static function getObjectStatusId($title){
        
        $id = 0;
        $data = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_object_status}}')->setWhere("object_status_title = '$title'")->findRow();
        
        if(!empty($data['object_status_id']))
            $id = $data['object_status_id'];
                
        return $id;        
        
    }
    
    
    /**
     *  Дополнительные js скрпты
     */
    public static function registerScript($copy_id){
        
        $result = array();
        
        if($copy_id == DocumentsGenerateModelExt::MODULE_DEALS || $copy_id == DocumentsGenerateModelExt::MODULE_FINANCES) {
            
            $result[]= array(
                'name'  => 'financesUpdate',
                'js'    => "$(document).on('keyup', '#finances_payment_square, #finances_payment_metrecost', function(){
                                if($('#finances_payment_square').val() != '' && $('#finances_payment_metrecost').val()){
                                    var sum = $('#finances_payment_square').val() * $('#finances_payment_metrecost').val();
                                    $('#finances_sum').val(isNaN(sum) ? '' : sum.toFixed(2));
                                }
                            })",
            );
            
        }
        
        if($copy_id == DocumentsGenerateModelExt::MODULE_DEALS || $copy_id == DocumentsGenerateModelExt::MODULE_OBJECTS) {
            
            $result[]= array(
                'name'  => 'objectUpdate',
                'js'    => "$(document).on('keyup', '#object_parammetrprice, #object_paramgeneralarea, #object_paramcalcarea, #object_bti_calcarea, #object_paramprojectarea ,#object_bti_paramprojectarea', function(){
                                var totalSum = 0;
                                $('.edit-view .sm_extension[data-relate_copy_id=\"271\"] .element_data[data-name=\"addpay_sum\"]').each(function(){
                                var val = $(this).data('value');
                                totalSum = totalSum+val;
                                });
                                var square = $('#object_paramgeneralarea').val();
                                if($('#object_type').val()=='1') {
                                    if(Number($('#object_paramcalcarea').val())>0)
                                        square = $('#object_paramcalcarea').val();
                                    if(Number($('#object_bti_calcarea').val())>0)
                                        square = $('#object_bti_calcarea').val();
                                }else {
                                    if(Number($('#object_paramprojectarea').val())>0)
                                        square = $('#object_paramprojectarea').val();
                                    if(Number($('#object_bti_paramprojectarea').val())>0)
                                        square = $('#object_bti_paramprojectarea').val();
                                }
                                var sum = $('#object_parammetrprice').val() * square + totalSum;
                                $('#object_paramcontractprice').val(isNaN(sum) ? '' : sum.toFixed(2));
                            })",
            );
            
        }

        return $result;
        
        
    }
    
    
    /**
     * Пользовательская функция сравнения массива
     */
    static function sort(&$array, $field_name) {
       self::$sort_field_name = $field_name;
       usort($array, array("DocumentsGenerateModelExt", "cmp_method"));
    } 
     
     
    static function cmp_method($a, $b) {
       if ($a[self::$sort_field_name] > $b[self::$sort_field_name])
            return 1;
    } 
    
    
}
