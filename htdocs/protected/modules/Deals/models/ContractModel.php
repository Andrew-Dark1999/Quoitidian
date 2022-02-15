<?php
/**
 * Оформление договора
 * @author Alex B.
 */

namespace Deals\models;


class ContractModel {

    public static function getJointProperty(){
        $list = array(
            0 => \Yii::t('DealsModule.base', 'No'),
            1 => \Yii::t('DealsModule.base', 'Yes'),
        );

        return $list;
    }

    public static function getCondition($id){
        $list = array(
            0 => \Yii::t('DealsModule.messages', 'The deal should have a object'),
            1 => \Yii::t('DealsModule.messages', 'Action impossible object has the status'),
            2 => \Yii::t('DealsModule.messages', 'Action impossible, there is no information about the Customer'),
            3 => \Yii::t('DealsModule.messages', 'The selected object is in the process of termination of dealings, execution of the Contract can not be'),
            4 => \Yii::t('DealsModule.messages', 'The selected object is not active, execution of the Contract can not be'),
        );

        return $list[$id];
    }
    
    public static function getSigned(){
        $list = array();
        $signed = \DataModel::getInstance()->setFrom('{{documents_templates_doc_signedby}}')->findAll();
        if(count($signed)) {
            foreach($signed as $sign){
                $list[$sign['doc_signedby_id']] = $sign['doc_signedby_title'];
            }
        }
        return $list;
    }
    
    
    public static function getEmptyParametersMessages($empty_level_date=false){
        
        if(!$empty_level_date) {
           return array(
                1 => \Yii::t('DealsModule.messages', 'The amount of square in the stages of payment should be equal to the total area'),
                2 => \Yii::t('DealsModule.messages', 'The amount of payment stages should equal the sum of the contract'),
                3 => \Yii::t('DealsModule.messages', 'Stage area can not be zero'),
                4 => \Yii::t('DealsModule.messages', 'Date first stage should be greater than or equal to today'),
                5 => \Yii::t('DealsModule.messages', 'Date second stage should be greater than or equal to the first date'),
                6 => \Yii::t('DealsModule.messages', 'Date third stage should be greater than or equal to second date'),
            );
        }else
            return \Yii::t('DealsModule.messages', 'Not specified date of payment for the {s} payment stage', array('{s}' => $empty_level_date));

    }
    
    /**
     * Шаблоны
     */
    public static function getTemplates(){

        return \DataModel::getInstance()->setFrom('{{documents_templates}}')->setWhere("this_template = '" . \EditViewModel::THIS_TEMPLATE_TEMPLATE. "' AND doc_newtype = '" . \DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT . "'")->findAll();

    }
    
    
    /**
     * Оформление договора
     */
    public static function makeAgreement($data){
        
        //правим сделку
        $deal_date = $data['deal_date'];
        $explode_date = explode('.', $deal_date);
        if(count($explode_date)==3)
            $deal_date = $explode_date[2] . '.' . $explode_date[1] . '.' . $explode_date[0];
        
        //счета типа площадь
        $deal_1_sum = round($data['deal_object_parammetrprice'] * $data['deal_1_square']);
        self::addFinance($data['deal_object_parammetrprice'], $data['deal_1_square'], $deal_1_sum, $data['deal_1_date'], \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL, 1, $data['deal_id']);

        if((int)$data['deal_level_count'] > 1) {
            $deal_2_sum = ((int)$data['deal_level_count'] == 2) ? $data['deal_agreement_sum'] - $data['deal_add_payments'] - $deal_1_sum : round($data['deal_object_parammetrprice'] * $data['deal_2_square']);
            self::addFinance($data['deal_object_parammetrprice'], $data['deal_2_square'], $deal_2_sum, $data['deal_2_date'], \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL, 1, $data['deal_id']);
            if((int)$data['deal_level_count'] == 3) {
               $deal_3_sum = $data['deal_agreement_sum'] - $data['deal_add_payments'] - $deal_1_sum - $deal_2_sum;
               self::addFinance($data['deal_object_parammetrprice'], $data['deal_3_square'], $deal_3_sum, $data['deal_3_date'], \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL, 1, $data['deal_id']);
            }
        }

        //счет типа дополнительный платеж
        if($data['deal_add_payments'] > 0)
            self::addFinance(null, null, $data['deal_add_payments'], null, \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL, 3, $data['deal_id']);
        
        //счет типа юридические услуги
        if((int)$data['deal_jur_service'] > 0)
            self::addFinance(null, null, $data['deal_jur_service'], null, \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL, 4, $data['deal_id']);
        
        //добавление документа
        $doc_data = self::addDocument($data);

        //меняем статус Сделки на "Продажа", а также сумму и дату, и название
        $data_doc = json_decode($doc_data['ev_refresh_fields']);
        \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_status' => \DocumentsGenerateModelExt::getDealStatusId(\DocumentsGenerateModelExt::$STATUSES_DEAL['sale']), 'deal_sum' => $data['deal_agreement_sum'], 'deal_salesdate' => $deal_date, 'module_title' => $data_doc->deal_contract_number),  'sdelkin_id = ' . $data['deal_id']);
        
        $deal_creditinsurecompany = '';
        $deal_creditinsurenumber = '';
        $deal_creditinsuredate = '';
        
        //меняем статус Объекта на "Продано"
        $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $data['deal_id'])->findRow();
        if(!empty($sm_object['kvartiry_id'])) {
            
            \DataModel::getInstance()->Update('{{ms_base_kvartiry}}', array('object_status' => \DocumentsGenerateModelExt::getObjectStatusId(\DocumentsGenerateModelExt::$STATUSES_OBJECT['sold'])),  'kvartiry_id = ' . $sm_object['kvartiry_id']);
        
            //указываем Страховые данные как у Дома
            $sm_doma = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_doma_1}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
             if(!empty($sm_doma['doma_id'])) {
                $doma = \DataModel::getInstance()->setFrom('{{ms_base_doma}}')->setWhere('doma_id = ' . $sm_doma['doma_id'])->findRow();
                
                //если не указаны страховые данные, загружаем их из Домов
                if(empty($data['deal_creditinsurecompany']) && !empty($doma['deal_insurecompany'])) {
                    \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_creditinsurecompany' => $doma['deal_insurecompany']),  'sdelkin_id = ' . $data['deal_id']);
                    $deal_creditinsurecompany = $doma['deal_insurecompany'];
                }else {
                    if(!empty($data['deal_creditinsurecompany']))
                        $deal_creditinsurecompany = $data['deal_creditinsurecompany'];
                }
                
                if(empty($data['deal_creditinsurenumber']) && !empty($doma['deal_insurenumber'])) {
                    \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_creditinsurenumber' => $doma['deal_insurenumber']),  'sdelkin_id = ' . $data['deal_id']);
                    $deal_creditinsurenumber = $doma['deal_insurenumber'];
                }else {
                    if(!empty($data['deal_creditinsurenumber']))
                        $deal_creditinsurenumber = $data['deal_creditinsurenumber'];
                }
                
                if(empty($data['deal_creditinsuredate']) && !empty($doma['deal_insuredate'])) {
                    \DataModel::getInstance()->Update('{{ms_base_sdelkin}}', array('deal_creditinsuredate' => $doma['deal_insuredate']),  'sdelkin_id = ' . $data['deal_id']);
                    $deal_creditinsuredate = $doma['deal_insuredate'];
                }else {
                    if(!empty($data['deal_creditinsuredate']))
                        $deal_creditinsuredate = $data['deal_creditinsuredate'];
                }
                
            }
        
        }
        
        self::saveToFile1C($data['deal_id'], $doc_data['doc_id']);
        
        $deal_creditinsuredate_1 = '';
        $deal_creditinsuredate_2 = '';
        
        if($deal_creditinsuredate) {
            $r = explode(' ', $deal_creditinsuredate);
            if(count($r)==2) {
                $r_date = explode('-', $r[0]);
                if(count($r_date)==3) {
                    $deal_creditinsuredate_1 = $r_date[2] . '.' . $r_date[1] . '.' . $r_date[0];
                    $deal_creditinsuredate_2 = $r[1];
                }
            }
        }
        
        return array(
            'doc_id' => $doc_data['doc_id'], 
            'ev_refresh_fields' => json_encode(array(
                'deal_contract_number' => $data_doc->deal_contract_number,
                'deal_creditinsurecompany' => $deal_creditinsurecompany,
                'deal_creditinsurenumber'=> $deal_creditinsurenumber,
                'deal_creditinsuredate_1' => $deal_creditinsuredate_1,
                'deal_creditinsuredate_2' => $deal_creditinsuredate_2,
             )));
        //return $doc_data;
    
    }
    
    
    /**
     * Эскпорт 1С
     */
    private static function saveToFile1C($deal_id, $doc_id) {
        
        $export_1c_data = self::getExportData($deal_id, $doc_id);
        
        $params = \DataModel::getInstance()->setFrom('{{ms_base_parametry}}')->setWhere("module_title = 'Путь к файлу экспорта в 1С'")->findRow();
        
        $try_local = true;
        if(!empty($params['param_value'])) {
            $saved = @file_put_contents($params['param_value'], $export_1c_data, FILE_APPEND); 
            if (($saved === false) || ($saved == -1)) {
               //запись в файл 1С не удалась
            }else
                $try_local = false;
        }
            
        if($try_local) {
            //в основной 1С файл не получилось записать, пишем в локальный
            $params = \DataModel::getInstance()->setFrom('{{ms_base_parametry}}')->setWhere("module_title = 'Путь к локальному файлу экспорта в 1С'")->findRow();
            if(!empty($params['param_value'])) 
                @file_put_contents($params['param_value'], $export_1c_data, FILE_APPEND); 
        }

        //запись в резервный файл   
        $params = \DataModel::getInstance()->setFrom('{{ms_base_parametry}}')->setWhere("module_title = 'Путь к резервному файлу экспорта в 1С'")->findRow();
        if(!empty($params['param_value'])) 
            @file_put_contents($params['param_value'], $export_1c_data, FILE_APPEND);    
           
    }
    
    
    /**
     * Получение данных для 1С
     */
    private static function getExportData($deal_id, $doc_id) {
    
        $result = '';
        $info_clients_deals = \DataModel::getInstance()->setFrom('{{ms_base_informacija_o_klient_sdelkin_1}}')->setWhere('sdelkin_id = ' . $deal_id)->findAll();
        
        if(count($info_clients_deals)) {
            
            $ids = array();
            foreach($info_clients_deals as $info_cd) 
                $ids []= $info_cd['informacija_o_klient_id'];
                
            $info_clients = \DataModel::getInstance()->setFrom('{{ms_base_informacija_o_klient_klienty2_2}}')->setWhere("informacija_o_klient_id in ('" . implode("','", $ids) . "')")->findAll();
            
            if(count($info_clients)) {
                
                foreach($info_clients as $info_client) {
                    $client = \DataModel::getInstance()->setFrom('{{ms_base_klienty2}}')->setWhere('klienty2_id = ' . $info_client['klienty2_id'])->findRow();
                    
                    $client_type = '';
                    if($client['client_type']=='1')
                        $client_type = 'ФизЛицо';
                    
                    if($client['client_type']=='3')
                        $client_type = 'ИП';
                    
                    $result .= iconv('utf-8', 'windows-1251', 'NEW') . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'ТипКлиента=' . $client_type) . "\r\n";
                    
                    $client_name = explode(' ', $client['module_title']);
                    if(!empty($client_name[0]))
                        $result .= iconv('utf-8', 'windows-1251', 'Фамилия=' . $client_name[0]) . "\r\n";
                    if(!empty($client_name[1]))
                        $result .= iconv('utf-8', 'windows-1251', 'Имя=' . $client_name[1]) . "\r\n";
                    if(!empty($client_name[2]))
                        $result .= iconv('utf-8', 'windows-1251', 'Отчество=' . $client_name[2]) . "\r\n";
                    
                    //адреса клиента
                    $juridical_address = '';
                    $main_address = '';
                    $mail_address = '';
                        
                    $info_adresses_clients = \DataModel::getInstance()->setFrom('{{ms_base_adresa_klienty2_1}}')->setWhere('klienty2_id = ' . $info_client['klienty2_id'])->findAll();
                    
                    if(count($info_adresses_clients)) {
                        
                        $ids = array();
                        foreach($info_adresses_clients as $info_ad) 
                            $ids []= $info_ad['adresa_id'];
                            
                        $info_adresses = \DataModel::getInstance()->setFrom('{{ms_base_adresa}}')->setWhere("adresa_id in ('" . implode("','", $ids) . "')")->findAll();     
                        
                        if(count($info_adresses)) {
                            foreach($info_adresses as $info_adress) {
                                if($info_adress['adress_type']=='1')
                                    $main_address = $info_adress['adress_adress'];
                                if($info_adress['adress_type']=='2')
                                    $mail_address = $info_adress['adress_adress'];
                                if($info_adress['adress_type']=='3')
                                    $juridical_address = $info_adress['adress_adress'];     
                            }
                        }
                    }
                    
                    if($juridical_address=='')
                        $juridical_address = $main_address;
                    
                    if($mail_address=='')
                        $mail_address = $main_address;
                    
                    $result .= iconv('utf-8', 'windows-1251', 'АдресПрописка=' . $juridical_address) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'АдресПроживания=' . $main_address) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'АдресКорреспонденции=' . $mail_address) . "\r\n";
                        
                    //данные документа    
                    $doc = \DataModel::getInstance()->setFrom('{{documents_templates}}')->setWhere('documents_id = ' . $doc_id)->findRow();    
                    $result .= iconv('utf-8', 'windows-1251', 'НомерДоговора=' . $doc['doc_number']) . "\r\n";
                    
                    $doc_date = '';
                    if(!empty($doc['doc_date']))
                        $doc_date = date("d.m.Y", strtotime($doc['doc_date']));
                    
                    $result .= iconv('utf-8', 'windows-1251', 'ДатаДоговора=' . $doc_date) . "\r\n";                        
                    
                    //паспортные данные (это СМ, загружаем первую запись, как в SMARTY)
                    $result .= iconv('utf-8', 'windows-1251', 'ИНН=') . "\r\n";    
                    $passport_seria = '';
                    $passport_number = '';
                    $passport_date = '';
                    $passport_whoissued = '';
                        
                    $info_passports_clients = \DataModel::getInstance()->setFrom('{{ms_base_pasportnye_dannye_klienty2_1}}')->setWhere('klienty2_id = ' . $info_client['klienty2_id'])->findRow();              
                    if(!empty($info_passports_clients['pasportnye_dannye_id'])) {
                        $passport_data = \DataModel::getInstance()->setFrom('{{ms_base_pasportnye_dannye}}')->setWhere('pasportnye_dannye_id = ' . $info_passports_clients['pasportnye_dannye_id'])->findRow();
                        $passport_seria = $passport_data['passport_seria'];
                        $passport_number = $passport_data['passport_number'];
                        
                        if(!empty($passport_data['passport_whenissued']))
                            $passport_date = date("d.m.Y", strtotime($passport_data['passport_whenissued']));
                        
                        $passport_whoissued = $passport_data['passport_whoissued'];
                    }
                    
                    $result .= iconv('utf-8', 'windows-1251', 'ПаспортСерия=' . $passport_seria) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'ПаспортНомер=' . $passport_number) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'ПаспортДатаВыдачи=' . $passport_date) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'ПаспортКемВыдан=' . $passport_whoissued) . "\r\n";
                    
                    //телефоны клиента
                    $result .= iconv('utf-8', 'windows-1251', 'Телефон1=' . $client['ehc_field3']) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'Телефон2=' . $client['ehc_field1']) . "\r\n";
                    $result .= iconv('utf-8', 'windows-1251', 'Телефон3=' . $client['ehc_field2']) . "\r\n";
                    
                    $result .= "\r\n";
                }
            }
        }
           
        return $result;
    }
    
    
    private static function addFinance($finances_payment_metrecost, $finances_payment_square, $finances_sum, $finances_invoice_pay_date, $type, $type_id, $deal_id){
        
        $date_create = date('Y.m.d');
        
        $finances_doc_number = 1;
        $last_finance = \DataModel::getInstance()->setFrom('{{ms_base_finansy}}')->setSelect('max(finances_doc_number) as max')->findRow();
        if(!empty($last_finance['max'])){
            $finances_doc_number += (int)$last_finance['max'];
        }
        
        $explode_date = explode('.', $finances_invoice_pay_date);
        if(count($explode_date)==3)
            $finances_invoice_pay_date = $explode_date[2] . '.' . $explode_date[1] . '.' . $explode_date[0];
        
        \DataModel::getInstance()->Insert('{{ms_base_finansy}}', array(
            'date_create' => $date_create, 
            'user_create' => \WebUser::getUserId(), 
            'finances_status' => \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['planned']), 
            'finances_typenew' => $type, 
            'finances_payment_metrecost' => $finances_payment_metrecost, 
            'finances_payment_square' => $finances_payment_square,
            'finances_sum' => $finances_sum,
            'finances_invoice_pay_date' => $finances_invoice_pay_date,
            'finances_doc_number' => $finances_doc_number
        ));

        $data_model = new \DataModel();
        $last_id = $data_model->setText('SELECT LAST_INSERT_ID();')->findScalar();
        
        \DataModel::getInstance()->Insert('{{ms_base_finansy_tipy_schetov_i_plate_3}}', array('finansy_id' => $last_id, 'tipy_schetov_i_plate_id' => $type_id));
        \DataModel::getInstance()->Insert('{{ms_base_sdelkin_finansy_6}}', array('finansy_id' => $last_id, 'sdelkin_id' => $deal_id));

    }
    
    
    private static function addDocument($data){
        
        $date_create = date('Y.m.d');
        
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_DOCUMENTS);
        $template = \DataModel::getInstance()->setFrom($extension_copy->getTableName())->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = ' . $data['template_id'] . ' AND this_template = "'.\EditViewModel::THIS_TEMPLATE_TEMPLATE.'"')->findRow();
        
        if(!empty($template['doc_file'])) {
            $uploads_id = array();
            $relate_key = false;
            $files = \UploadsModel::model()->setRelateKey($template['doc_file'])->findAll();
            if(!empty($files)){
                foreach($files as $value_file){
                    
                    $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $template['doc_file'];
                    $relate_key = md5($relate_key);
                    
                    $upload_model = new \UploadsModel();
                    $upload_model->setScenario('copy');
                    $upload_model->setThumbScenario('copy');
                    $upload_model->copy_id = $value_file->copy_id;
                    $upload_model->file_source = $value_file->file_source;
                    $upload_model->file_path_copy = $value_file->file_path;
                    $upload_model->file_name = $value_file->file_name;
                    $upload_model->file_title = $value_file->file_title;
                    $upload_model->thumbs = $value_file->thumbs;                        
                    $upload_model->relate_key = $relate_key;
                    $upload_model->status = 'asserted';
                    $upload_model->save();
                    $upload_model->refresh();

                    $parent_upload_model = new \UploadsParentsModel();
                    $parent_upload_model->parent_upload_id = $value_file->id;
                    $parent_upload_model->upload_id = $upload_model->getPrimaryKey();
                    $parent_upload_model->parent_doc_id = $data['template_id'];
                    $parent_upload_model->save();

                    break;
                }
            }
            
            if($relate_key) {
                
                //получаем номер документа
                $doc_number = array(date('y'));
                $deal = \DataModel::getInstance()->setFrom('{{ms_base_sdelkin}}')->setWhere('sdelkin_id = ' . $data['deal_id'])->findRow();
            
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
                $module_title = $template['module_title'];
                
                // $primary_field_schema = $extension_copy->getPrimaryField(null, false);
                // foreach($primary_field_schema as $field_schema){  
                    // if(!empty($field_schema['params']['name_generate'])) {
                        // $auto_name = Fields::getInstance()->getNewRecordTitle($field_schema['params']['name_generate'], $this->_edit_model->extension_copy, $this->_edit_data);
                        // if($auto_name !== false) {
                            // $this->_edit_model->saveAttributes(array($field_schema['params']['name'] => $auto_name));
                        // }
                    // }
                // }
                
                $deal_date = $data['deal_date'];
                $explode_date = explode('.', $deal_date);
                if(count($explode_date)==3)
                    $deal_date = $explode_date[2] . '.' . $explode_date[1] . '.' . $explode_date[0];
  
                $query = '("' . $date_create . '", "' . $template['user_create'] . '", "' . $template['doc_status'] . '", "' . $data['deal_agreement_sum'] . '", "' . $data['deal_j_property'] . '", "' . $data['deal_signed'] . '", "' . $deal_date  . '", "' . $date_create . '", "' . $template['doc_contracttype'] . '", "' . $template['doc_contract_pay_type'] . '", "' . $doc_number . '", "' . $module_title . '", "' . \DocumentsGenerateModelExt::BLOCK_ID_AGREEMENT . '", "' . $relate_key . '")';
                $query = 'INSERT into {{documents_templates}} (date_create,user_create,doc_status,doc_sum,doc_contractisshareproperty,doc_signedby,doc_signeddate,doc_date,doc_contracttype,doc_contract_pay_type,doc_number,module_title,doc_newtype,doc_file) VALUES ' . $query;

                $data_model = new \DataModel();
                $data_model->setText($query)->execute();
                $last_id = $data_model->setText('SELECT LAST_INSERT_ID();')->findScalar();
                
                if(!empty($deal['sdelkin_id'])) {
                    \DataModel::getInstance()->Insert('{{documents_templates_sdelkin_5}}', array('documents_id' => $last_id, 'sdelkin_id' => $deal['sdelkin_id']));
                    \DocumentsGenerateModelExt::setDealNumber($deal['sdelkin_id'], $doc_number);
                }
                return array('doc_id'=>$last_id, 'ev_refresh_fields'=>json_encode(array('deal_contract_number'=>$doc_number)));
            }
        }
    }
    
    
    /**
     * Проверяем условия, можем ли оформить договор
     */
    public static function checkConditions($deal_id){
        
        $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal_id)->findRow();
        
        if(empty($sm_object['kvartiry_id']))
            return array('error' => self::getCondition(0));
        
        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
        
        if(!in_array((int)$object['object_status'], array(\DocumentsGenerateModelExt::getObjectStatusId(\DocumentsGenerateModelExt::$STATUSES_OBJECT['reservations']), \DocumentsGenerateModelExt::getObjectStatusId(\DocumentsGenerateModelExt::$STATUSES_OBJECT['free'])))) {
            $statuses = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_object_status}}')->findAll();
            $st = '';
            foreach($statuses as $v) {
                if($v['object_status_id'] == (int)$object['object_status']) {
                    $st = $v['object_status_id'] = $v['object_status_title'];
                    break;
                }   
            }    
            return array('error' => self::getCondition(1) . " <$st>");
        }
        
        if(!empty($object['object_is_on_cancellation']) && $object['object_is_on_cancellation'])
            return array('error' => self::getCondition(3));
        
        if(isset($object['object_is_active']) && $object['object_is_active']==0)
            return array('error' => self::getCondition(4));
        
        $sm_client = \DataModel::getInstance()->setFrom('{{ms_base_informacija_o_klient_sdelkin_1}}')->setWhere('sdelkin_id = ' . $deal_id)->findAll();

        if(count($sm_client)==0)
            return array('error' => self::getCondition(2));
        
        //загружаем данные по-умолчанию
        $default_data = self::getDefaultData($deal_id);
        
        return array('error' => 0, 'default_data' => $default_data);
        
    }
    
    
    /**
     * Параметры по-умолчанию для формы Оформление договора
     */
    public static function getDefaultData($deal_id){
        
        $sm_object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry_sdelkin_2}}')->setWhere('sdelkin_id = ' . $deal_id)->findRow();
        if(empty($sm_object)) return;
        
        $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $sm_object['kvartiry_id'])->findRow();
        
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
        
        $result['square'] = sprintf("%.2f", $square);
        $result['object_parammetrprice'] = sprintf("%.2f", $object['object_parammetrprice']);
        
        $object_copy = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Объекты'));
        $sum_add_payments = 0;

        $add_payments = \DocumentsGenerateModel::collectSM($object_copy->getSchema(), $object_copy->copy_id, $object['kvartiry_id'], array(), 'Доп_платежи');

        if(count($add_payments)>0){
            if(isset($add_payments['Доп_платежи'][2])) {
                foreach($add_payments['Доп_платежи'][2] as $k => $v) {             
                    if(!empty($v['addpay_sum'])) {
                        $sum_add_payments += $v['addpay_sum'];
                    }
                }
            }
        }
  
        $result['add_payments'] = sprintf("%.2f", $sum_add_payments);
        $result['agreement_sum'] = sprintf("%.2f", $square * $object['object_parammetrprice'] + $sum_add_payments);
        
        return $result;
        
    }    


}
