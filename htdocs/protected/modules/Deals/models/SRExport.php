<?php

class SRExport {

    private $_object;
    private $_objects_ids = array();
    
    private $address = 'г. Рязань, улица 9-ая Линия, дом 26';
    private $build_address = 'г. Рязань, улица 9-ая Линия, дом 11';
    
    private $row = 5;
    
    private $deal_copy = null;
    private $deal_schema = null;
    
    
    public function __construct(){
        $this->_object = new PhpExcel();
        $this->deal_copy = \ExtensionCopyModel::model()->modulesActive()->findByPK(DocumentsGenerateModelExt::MODULE_DEALS);
        $this->deal_schema = $this->deal_copy->getSchema();
    }


    public static function getInstance(){
        return new self();
    }

    public function setIds($ids, $all){
        
        if($all) {
            //получаем все записи модуля
            $extension_copy = \ExtensionCopyModel::model()->findByPk(DocumentsGenerateModelExt::MODULE_OBJECTS);
            $global_params = array(
                'pci' => null,
                'pdi' => null,
                'finished_object' => null,
            );
            $cards = \DataListModel::getInstance()
                    ->setExtensionCopy($extension_copy)
                    ->setGlobalParams($global_params)
                    ->setGetAllData(true)
                    ->prepare(\DataListModel::TYPE_LIST_VIEW)
                    ->getData();
 
            if($cards) {
                foreach($cards as $card) {
                    if(!empty($card[$extension_copy->prefix_name . '_id']))
                       $this->_objects_ids[] = $card[$extension_copy->prefix_name . '_id'];
                }
            }
        }else {
            if(!empty($ids))
                $this->_objects_ids = json_decode($ids);
            
        }

        return $this;
    }

    
    /**
    *   заголовок документа
    */
    private function setHeader(){
    
        $this->_object->setActiveSheetIndex(0)->setCellValue('A1', Yii::t('DealsModule.base', 'TABLE SURCHANGE-REFUNDS apartment to the address') . ': ' . $this->address . ' [' . date('d.m.Y') . ']');
        $this->_object->setActiveSheetIndex(0)->setCellValue('A2', '(' . Yii::t('DealsModule.base', 'building address') .  ': ' . $this->build_address . ']');
        
        $this->_object->setActiveSheetIndex(0)->setCellValue('A4', Yii::t('DealsModule.base', '#'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('B4', Yii::t('DealsModule.base', '# build'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('C4', Yii::t('DealsModule.base', '# mail'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('D4', Yii::t('DealsModule.base', 'area under contract'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('E4', Yii::t('DealsModule.base', 'amount of under contract'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('F4', Yii::t('DealsModule.base', 'amount paid'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('G4', Yii::t('DealsModule.base', 'last pay for 1 sq.m.'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('H4', Yii::t('DealsModule.base', 'redevelopment'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('I4', Yii::t('DealsModule.base', 'amount of redevelopment'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('J4', Yii::t('DealsModule.base', 'deferred payment'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('K4', Yii::t('DealsModule.base', 'deferred payments'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('L4', Yii::t('DealsModule.base', 'current price for 1 sq.m.'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('M4', Yii::t('DealsModule.base', 'total area'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('N4', Yii::t('DealsModule.base', 'loggia area'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('O4', Yii::t('DealsModule.base', 'calculate area'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('P4', Yii::t('DealsModule.base', 'delta by the meter'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('Q4', Yii::t('DealsModule.base', 'amount to a surcharge'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('R4', Yii::t('DealsModule.base', 'долг'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('S4', Yii::t('DealsModule.base', 'new price agreement'));
        $this->_object->setActiveSheetIndex(0)->setCellValue('T4', Yii::t('DealsModule.base', 'participant'));
        
        for ($col = 'A'; $col <= 'T'; $col++){
            $this->_object->setActiveSheetIndex(0)->getStyle($col . '4')->getAlignment()->setTextRotation(90);
        }
        //$this->_object->setActiveSheetIndex(0)->getColumnDimension('A')->setAutoSize(true);
    }
    
    /**
    *   генерация тела документа
    */
    private function setData(){
        if(!empty($this->_objects_ids)) {
            $i = 1;
            foreach($this->_objects_ids as $object_id) {
                $object = \DataModel::getInstance()->setFrom('{{ms_base_kvartiry}}')->setWhere('kvartiry_id = ' . $object_id)->findRow();
                //финансы
                $deal = null;
                $deals = \DocumentsGenerateModelExt::getObjectDeals($object_id, true);
                
                //у объекта есть сделка
                if($deals) {
                    //получаем последюю сделку
                    $deal_id = array_pop($deals);
                    $deal = \DataModel::getInstance()->setFrom('{{ms_base_sdelkin}}')->setWhere('sdelkin_id = ' . $deal_id)->findRow();
                    //получаем финансы
                    $finances = \DocumentsGenerateModel::collectSM($this->deal_schema, $this->deal_copy->copy_id, $deal_id, array(), 'Финансы');
                }
                
                if(empty($object['kvartiry_id']) || empty($deal['sdelkin_id']))
                    continue;
                
                
                //площадь по договору
                $bill_square = 0;
                //оплаченная площадь
                $pay_square = 0;
                //оплаченная сумма
                $paid_sum = 0;
                //стоимость кв. метра (последняя)
                $last_pay_1m = 0;
                //признак перепланировки
                $plan = false;
                //сумма перепланировки
                $plan_sum = 0;
                //признак отложенного платежа
                $ot_pay = false;
                //сумма их
                $ot_pay_sum = 0;
                //разные доп. платежи
                $add_payments = 0;
                //платежи
                $payments_square = array();
                //счета
                $bills_square = array();
                if(count($finances)>0){
                    if(isset($finances['Финансы'][2])) {
                        foreach($finances['Финансы'][2] as $k => $v) {             
                            if(isset($v['finances_typenew'])) {
                                if($v['finances_typenew'] == \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_BILL){
                                    //финансы типа счет
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                        //типа площадь
                                        if($v['finances_payment_square'])
                                            $bill_square += $v['finances_payment_square'];
                                        
                                        if(!empty($v['finances_date']))
                                            $bills_square[] = $v;
                                    }
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='3'){
                                        //дополнительный платеж
                                        if($v['finances_sum'])
                                            $add_payments += $v['finances_sum'];
                                    }
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='13'){
                                        //типа оплата за перепланировку
                                        $plan = true;
                                        
                                        if($v['finances_sum']) {
                                            $plan_sum += $v['finances_sum'];
                                            $add_payments += $v['finances_sum'];
                                        }
                                    }
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='14'){
                                        //оплата за переустройство
                                        if($v['finances_sum'])
                                            $add_payments += $v['finances_sum'];
                                    }
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='16'){
                                        //типа отложенный платеж
                                        $ot_pay = true;
                                        
                                        if($v['finances_sum'])
                                            $ot_pay_sum += $v['finances_sum'];
                                    }
                                }
                                
                                if($v['finances_typenew'] == \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT){
                                    //финансы типа платеж
                                    if($v['finances_type']=='1') {
                                        //тип входящие
      
                                    }    
                                    
                                    if($v['ms_base_tipy_schetov_i_plate_tipy_schetov_i_plate_id']=='1'){
                                        //типа площадь
                                        if($v['finances_payment_metrecost'])
                                            $last_pay_1m = $v['finances_payment_metrecost'];
                                        
                                        if($v['finances_sum'])
                                            $paid_sum += $v['finances_sum'];
                                        
                                        if($v['finances_payment_square'])
                                            $pay_square += $v['finances_payment_square'];
                                            
                                            
                                        if(!empty($v['finances_date']))
                                            $payments_square[] = $v;
                                    }
                                    
                                    
                                    
                                }
                            }
                        }
                    }
                }
                
                // for ($col = 'A'; $col <= 'T'; $col++){
                    // $this->setCell($col, $this->row, $i, $object, $deal, $finances);
                // }
                $calculate_square = ($object['object_type']=='1') ? $object['object_bti_calcarea'] : $object['object_bti_paramprojectarea'];
                $square = ($object['object_type']=='1') ? $object['object_paramcalcarea'] : $object['object_paramprojectarea'];
                $delta = ($object['object_type']=='1') ? $object['object_bti_calcarea'] : $object['object_bti_paramprojectarea'];
                
                $summ_add = $delta * $last_pay_1m;
                
                $new_sum = 0;
                $doc_agremeent_exist = \DocumentsGenerateModelExt::getAgreementType($this->deal_schema, $this->deal_copy->copy_id, $deal_id);
                
                if($doc_agremeent_exist) {
                    
                    $meter_cost = 0;

                    if(count($payments_square)>0) {
                        \DocumentsGenerateModelExt::sort($payments_square, 'finances_date');
                        $meter_cost = end($payments_square)['finances_payment_metrecost'];
                    }
                    
                    if($deal['deal_is_delayed']==1){
                        //у сделки есть просрочки
                        if(!$meter_cost)
                            $meter_cost = $object['object_parammetrprice'];
                        $new_sum = $paid_sum + ($bill_square - $pay_square) * $object['object_parammetrprice'] + $add_payments + ($calculate_square - $square) * $meter_cost;
                        
                    }else {
                        //просрочек нет
                        if($doc_agremeent_exist==1) {
                            //фиксированный договор
                            if(!$meter_cost) {
                                if(count($bills_square)>0) {
                                    \DocumentsGenerateModelExt::sort($bills_square, 'finances_date');
                                    $meter_cost = end($bills_square)['finances_payment_metrecost'];
                                }
                            }
                            $new_sum = $add_payments + ($calculate_square - $square) * $meter_cost;
                        }elseif($doc_agremeent_exist==2) {   
                            //нефиксированный
                            if(!$meter_cost)
                                $meter_cost = $object['object_parammetrprice'];
                            $new_sum = $paid_sum + ($bill_square - $pay_square) * $meter_cost + $add_payments + ($calculate_square - $square) * $meter_cost;
                        }         
                    }
                }
                
                $debt = $new_sum - $summ_add - $paid_sum;
                
                //клиенты сделки
                $clients_sm = \DocumentsGenerateModel::collectSM($this->deal_schema, $this->deal_copy->copy_id, $deal_id, array(), 'ИнформацияоКлиентахвСделке');

                $clients_ids = array();
                //маркер присутствия правоприемника
                $pp_exists = false;
                if(count($clients_sm)>0){
                    if(isset($clients_sm['Информация о Клиентах в Сделке'][2])) {
                        foreach($clients_sm['Информация о Клиентах в Сделке'][2] as $k => $v) {
                            $clients_ids[]= $v['ms_base_klienty2_klienty2_id'];
                            if($v['cl_deal_client_type']==2) {
                                $pp_exists = true;
                                break;
                            }
                        }
                    }
                }
                
                if($pp_exists) {
                    //все по-новой, но добавляем только правоприемников
                    unset($clients_ids);
                    $clients_ids = array();
                    foreach($clients_sm['Информация о Клиентах в Сделке'][2] as $k => $v) {
                        $clients_ids[]= $v['ms_base_klienty2_klienty2_id'];
                        if($v['cl_deal_client_type']==2) {
                            $clients_ids[]= $v['ms_base_klienty2_klienty2_id'];
                        }
                    }
                }
                
                $client_text = '';
                
                if($clients_ids) {
                    $clients = \DataModel::getInstance()->setFrom('{{ms_base_klienty2}}')->setWhere('klienty2_id in (' . implode(',', $clients_ids) . ')')->findAll();
                    if($clients) {
                        $clients_arr = array();
                        foreach($clients as $client) {
                            $clients_arr[]= $client['module_title'];
                        }
                        $client_text = implode(', ', $clients_arr);
                    }
                }

                $this->_object->setActiveSheetIndex(0)->setCellValue('A' . $this->row, $i);
                $this->_object->setActiveSheetIndex(0)->setCellValue('B' . $this->row, $object['object_buildnumber']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('C' . $this->row, $object['object_mailnumber']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('D' . $this->row, $bill_square);
                $this->_object->setActiveSheetIndex(0)->setCellValue('E' . $this->row, $deal['deal_sum']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('F' . $this->row, $paid_sum);
                $this->_object->setActiveSheetIndex(0)->setCellValue('G' . $this->row, $last_pay_1m);
                $this->_object->setActiveSheetIndex(0)->setCellValue('H' . $this->row, ($plan) ? 'Да' : 'Нет');
                $this->_object->setActiveSheetIndex(0)->setCellValue('I' . $this->row, $plan_sum);
                $this->_object->setActiveSheetIndex(0)->setCellValue('J' . $this->row, ($ot_pay) ? 'Да' : 'Нет');
                $this->_object->setActiveSheetIndex(0)->setCellValue('K' . $this->row, $ot_pay_sum);
                $this->_object->setActiveSheetIndex(0)->setCellValue('L' . $this->row, $object['object_parammetrprice']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('M' . $this->row, $object['object_bti_totalarea']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('N' . $this->row, $object['object_bti_loggiaarea']);
                $this->_object->setActiveSheetIndex(0)->setCellValue('O' . $this->row, $calculate_square);
                $this->_object->setActiveSheetIndex(0)->setCellValue('P' . $this->row, $delta);
                $this->_object->setActiveSheetIndex(0)->setCellValue('Q' . $this->row, $summ_add);
                $this->_object->setActiveSheetIndex(0)->setCellValue('R' . $this->row, $debt);
                $this->_object->setActiveSheetIndex(0)->setCellValue('S' . $this->row, $new_sum);
                $this->_object->setActiveSheetIndex(0)->setCellValue('T' . $this->row, $client_text);

                
                $this->row ++;
                $i++;
            }
            
        }
    }
    
    
    /**
    *   подготовка данных для печати
    */
    public function prepareDocument(){
        ini_set('max_execution_time', 3600); // 1ч
        $this->setHeader();
        $this->setData();
        return $this;
    }

    /**
    *   созвращает сформированный документ
    */
    public function getDocument($file_type = 'excel'){
        $params = array();
        switch($file_type){
            case 'excel': $params = array(
                                    'content_type' => '',
                                    'file_ext_type' => '.xlsx',
                                    'class_const' => 'Excel2007',
                                    );
                        break;
        }
        header('Content-Type: ' . $params['content_type']);
        header('Content-Disposition: attachment;filename="'.'Tablica_doplat-vozvratov'. $params['file_ext_type'] .'"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->_object, $params['class_const']);
        $objWriter->save('php://output');
    }

}




