<?php

class ExcelExport {

    const TYPE_EXCEL    = 'excel';
    const TYPE_PDF      = 'pdf';

    private $_object;

    private $_extension_copy;

    private $_schema;

    private $_data = array();

    private $_col = 0;
    private $_row = 1;
    private $_col_table_start = 0;
    private $_col_table_end = 0;
    private $_row_table_start = 1;
    private $_row_table_end = 1;
    private $_col_width = array();

    private $_col_width_base = 0;
    private $_row_height_base = 0;

    private $_max_file_thumbs_size = null;
    private $_add_header_field_name = true;

    private $_sheet_index = 0;
    private $_export_avatar = false;

    private $_params = array();
    private $_tmp_file_name;

    private $_hidden_column_group_index = array();
    private $_add_image_file_name = true;
    private $_file_thumbs_size = true;
    private $_format_cell = array(
                    'alignment_horizontal' => null,
                    'alignment_vertical' => null,
                );

    private $_obj_writer;

    const RATIO_WIDTH = 6.9;
    const RATIO_HEIGHT = 1.2;

    // масив подготовленных схемы полей
    private $_fields_params = array();

    private $blocks = array();

    public function __construct(){
        $this->_object = new PhpExcel();
        $this->_col_width_base = $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->getWidth();
        $this->_row_height_base = $this->_object->setActiveSheetIndex($this->_sheet_index)->getRowDimension($this->_row)->getRowHeight();
    }


    public static function getInstance(){
        return new self();
    }

    public function setExtensionCopy($extension_copy){
        if(!$extension_copy->isShowAllBlocks()) {
            $blocks = $extension_copy->getSchemaBlocksData();
            if($blocks) {
                foreach($blocks as $block) {
                    $this->blocks[$block['unique_index']] = $block['title'];
                }
            }
        }
        $this->_extension_copy = $extension_copy;
        return $this;
    }

    public function setAddHeaderFieldName($add_header_field_name){
        $this->_add_header_field_name = $add_header_field_name;
        return $this;
    }

    public function setData($data){
        $this->_data = $data;
        return $this;
    }

    public function setWithOutGroupIndex($group_index){
        if(is_array($group_index))
            $this->_hidden_column_group_index = $group_index;
        else
        if(is_string($group_index) && $group_index !== '')
            $this->_hidden_column_group_index = explode(',', $group_index);

        return $this;
    }

    public function setWidthColumn($column_width){
        $this->_col_width = $column_width;
        return $this;
    }

    public function setAddImageFileName($add_image_file_name){
        $this->_add_image_file_name = $add_image_file_name;
        return $this;
    }

    public function getObject(){
        return $this->_object;
    }


    public function getSheetIndex(){
        return $this->_sheet_index;
    }


    public function getCol(){
        return $this->_col;
    }


    public function getRow(){
        return $this->_row;
    }


    public function getColTableStart(){
        return $this->_col_table_start;
    }

    public function getColTableEnd(){
        return $this->_col_table_end;
    }


    public function getRowTableStart(){
        return $this->_row_table_start;
    }


    public function getRowTableEnd(){
        return $this->_row_table_end;
    }


    public function setFormatCell($format_cell){
        $this->_format_cell = array_merge($this->_format_cell, $format_cell);
        return $this;
    }

    public function setFileThumbsSize($file_thumbs_size){
        $this->_file_thumbs_size = $file_thumbs_size;
        return $this;
    }


    private function inGroupIndex($group_index){
        if(empty($this->_hidden_column_group_index)) return false;
        if(in_array($group_index, $this->_hidden_column_group_index)) return true;
        return false;
    }

    public function setSchema(){
        $this->_extension_copy->setAddId();
        $schema = $this->_extension_copy->getSchemaParse();

        if(empty($schema) || !isset($schema['elements'])) return $this;
        foreach($schema['elements'] as $element){
            if(isset($element['field'])){
                if($element['field']['params']['type'] == 'activity') continue;

                if(!$this->inGroupIndex($element['field']['params']['group_index']))
                    $this->_schema[] = $element['field'];
            }
        }
        return $this;
    }

    public function setDocunentProperties($properties = array()){
        $this->_object->getProperties()
                                ->setTitle((isset($properties['title']) ? $properties['title'] : ''))
                                ->setSubject((isset($properties['subject']) ? $properties['subject'] : ''))
                                ->setDescription((isset($properties['description']) ? $properties['description'] : ''))
                                ->setKeywords((isset($properties['keywords']) ? $properties['keywords'] : ''))
                                ->setCategory((isset($properties['category']) ? $properties['category'] : ''));
        return $this;
    }


    public function setCol($column, $append = true){
        if($append)
            $this->_col+= $column;
        else
            $this->_col = $column;
        return $this;
    }

    public function setRow($row, $append = true){
        if($append)
            $this->_row+= $row;
        else
            $this->_row = $row;
        return $this;
    }


    private function setColSize($size){
        $sheet = $this->_object->setActiveSheetIndex($this->_sheet_index);
        $sheet->getColumnDimensionByColumn($this->_col)
              ->setWidth($size);
    }

    private function setRowSize($size){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->getRowDimension($this->_row)
              ->setRowHeight($size);
    }



    public function setCellValueByColumnAndRow($value){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->setCellValueExplicitByColumnAndRow($this->_col, $this->_row, $value);
    }

    private function setCellValueByColumnAndRowFromArray($value){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->setCellValueByColumnAndRow($this->_col, $this->_row, $value);
    }

    private function setCellImage($file_name){
        if(empty($file_name)) return;
        if(!file_exists($file_name)) return;
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Name_img');
        $objDrawing->setDescription('Description_img');
        $objDrawing->setPath($file_name);
        $objDrawing->SetOffsetX(2);
        $objDrawing->SetOffsetY(2);

        $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
        $activeSheet = $this->_object->getActiveSheet();

        // Insert picture
        $objDrawing->setCoordinates($activeSheet->getActiveCell());
        $objDrawing->setWorksheet($activeSheet);
    }


    /*
    * set Style for cell
    */
    private function setStyleCell($type = 'string', $params = array()){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
        $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());

        //$activeCell->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        switch($type){
            case 'display':
            case 'string':
            case 'logical':
            case 'select':
            case 'relate':
            case 'relate_string':
                if($this->_format_cell['alignment_vertical']){
                    $activeCell->getAlignment()->setVertical($this->_format_cell['alignment_vertical']);
                }
                if($this->_format_cell['alignment_horizontal']){
                    $activeCell->getAlignment()->setVertical($this->_format_cell['alignment_horizontal']);
                }

                /*
                if(!empty($params) && $params['is_primary']){
                    if($this->_export_avatar && $params['avatar']) $activeCell->getAlignment()->setIndent(round(30 / 10)+1);
                }
                */


            break;

            case 'file':
                //$activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                //$activeCell->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                break;

            case 'numeric':
            case 'datetime':
                //$activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                if($this->_format_cell['alignment_vertical']){
                    $activeCell->getAlignment()->setVertical($this->_format_cell['alignment_vertical']);
                }
                if($this->_format_cell['alignment_horizontal']){
                    $activeCell->getAlignment()->setVertical($this->_format_cell['alignment_horizontal']);
                }


                break;

            case 'file_image':
                $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
                $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());

                if(!empty($params)) $activeCell->getAlignment()->setIndent(round($params['file_thumbs_size'] / 10)+1);
                //$activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                //$activeCell->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                break;
            /*
            case 'relate':
            case 'relate_string':
                $activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $activeCell->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $activeCell->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                break;
            */
        }

    }





    /**
    *   Добавляем картинку
    */
    private function addImageToCell($upload_models){
        if(is_array($upload_models)){
            foreach($upload_models as $upload_model){
                $this->setCellValueByColumnAndRow($upload_model->getFileUrl(true));
            }
        } else {
            $this->setCellValueByColumnAndRow($upload_models->getFileUrl(true));
        }
    }




    /**
     * Возвращает данные для поля relate
     */
    /*
    private function getRelateData($value, $file_add, &$result){
        if(isset($value[0]) && is_array($value[0]))
            return $this->getRelateData($value[0], $file_add, $result);

        if(isset($value['value']) && !empty($value['value'])){
            if($result['value_concat'] === ''){
                $result['value_concat'] = $value['value'];
            } else {
                $result['value_concat'] .= ' ' . $value['value'];
            }
        }
        if(isset($value['files']) && !empty($value['files']) && !$file_add) $this->addImageToCell($value['files']);
        if(isset($value['params']['params']) && ($value['params']['params']['type'] == 'file_image' || $value['params']['params']['is_primary'] == true)){
            $result['type'] = $value['params']['params']['type'];
            $result['params'] = $value['params']['params'];
        }

        return $result;
    }
    */



    /**
    *  Вставка елемента данных из массыва данних
    */
    private function addToCell($field_name, $data){ 
    
        $fields = explode(',', $field_name);
        foreach($fields as $field){

            if(isset($data['params'][$field])){
                // other fields
                //if($data['params'][$field]['type'] != 'relate'){
                    if(isset($data['value']) || isset($data['files'][$field])){
                        if(isset($data['value'])) {
                            
                            if($data['params'][$field]['type'] == 'display_block') {
                                $data['value'] = (isset($this->blocks[$data['value']])) ? $this->blocks[$data['value']] : '';
                            }
                            
                            $this->setCellValueByColumnAndRow($data['value']);
                            
                        };
                        if(isset($data['files'][$field]) && $data['files'][$field]){
                            $this->addImageToCell($data['files'][$field]);
                            if($this->_add_image_file_name == false){
                                $this->setCellValueByColumnAndRow('');
                            }
                        }
                        $this->setStyleCell($data['params'][$field]['type'], $data['params'][$field]);
                    }

                // field "relate"
                //}
                /*
                elseif($data['params'][$field]['type'] == 'relate') {
                    $file_add = false;
                    $result = array(
                        'value_concat' => '',
                        'type' => 'string',
                        'params' => array(),
                    );

                    foreach($data['value'] as $value){
                        $this->getRelateData($value, $file_add, $result);
                        $file_add = true;
                    }

                    $this->setCellValueByColumnAndRow($result['value_concat']);
                    $this->setStyleCell($result['type'], $result['params']);
                }
                */
            } else {
                if(isset($data['value']) || isset($data['files'][$field_name])){
                    if(isset($data['value'])) $this->setCellValueByColumnAndRow($data['value']);
                    if(isset($data['files'][$field_name]))
                        $this->addImageToCell($data['files'][$field_name]);
                    if(isset($data['params'][$field_name])) $this->setStyleCell($data['params'][$field_name]['type'], $data['params'][$field_name]);
                }
            }

        }
        
    }


    /**
    * Добавляем заголовок документа
    */
    private function addHeader($header){
        $this->setCellValueByColumnAndRow($header);

        $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
        $this->_object->getActiveSheet()->getCellByColumnAndRow($this->_col, $this->_row)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);

        $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());
        $activeCell->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $activeCell->getFont()->setBold(true)->setSize(14);


        return $this;
    }

    /**
    * добавляем заголок таблицы
    */
    private function addFieldHeaders($export_fields){
        if(!empty($this->_schema)){
            $this->_extension_copy->setAddId();
            $schema = $this->_extension_copy->getSchemaParse();
        }

        $fields = SchemaConcatFields::getInstance()
                        ->setSchema($schema['elements'])
                        ->setWithoutFieldsForListViewGroup($this->_extension_copy->getModule()->getModuleName())
                        ->parsing()
                        ->prepareWithOutDeniedRelateCopyId()
                        ->primaryOnFirstPlace(true)
                        ->prepareWithConcatName()
                        ->getResult();
        if(isset($fields['header'])){
            foreach($fields['header'] as $field){
                $fields_emp = explode(',', $field['name']);

                if(!empty($export_fields)) 
                    if(!in_array($field['name'], $export_fields))
                        continue;

                foreach($fields_emp as $field_emp){
                    if($this->inGroupIndex($field['group_index'])) continue; // пропускаем скрытые колонки

                    if(isset($fields['params'][$field_emp]['display']) && (bool)$fields['params'][$field_emp]['display'] == false) continue;
                    //if(isset($fields['params'][$field_emp]['list_view_visible']) && (bool)$fields['params'][$field_emp]['list_view_visible'] == false) continue;
                    //if(isset($fields['params'][$field_emp]['list_view_display']) && (bool)$fields['params'][$field_emp]['list_view_display'] == false) continue;

                    $this->_fields_params[] = $fields['params'][$field_emp];

                    // данные
                    if($this->_add_header_field_name){
                        $this->setCellValueByColumnAndRow(ListViewBulder::getFieldTitle(array('title' => $field['title']) + $fields['params'][$fields_emp[0]]) . ' [' . $field_emp . ']');
                    } else {
                        $this->setCellValueByColumnAndRow(ListViewBulder::getFieldTitle(array('title' => $field['title']) + $fields['params'][$fields_emp[0]]));
                    }

                    $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
                    $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());
                    $activeCell->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $activeCell->getFont()->setBold(true);
                    $this->_object->getActiveSheet()->getCellByColumnAndRow($this->_col, $this->_row)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING);

                    // ширина стобца
                    if(isset($this->_col_width[$field['group_index']])){
                        $col_width = $this->_col_width[$field['group_index']];
                        $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->setWidth(round($col_width / self::RATIO_WIDTH));
                    } else
                    if(isset($this->_col_width[$field['name']])){
                        $col_width = $this->_col_width[$field['name']];
                        $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->setWidth(round($col_width / self::RATIO_WIDTH));
                    } else {

                        $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->setAutoSize(true);
                    }

                    $this->setCol(1, true);
                }
            }
        }

        return $this;
    }

    /**
    *   установка высоты для всего ряда
    */
    private $_row_height = -1;
    private function setRowHeight($field_name, $data){
        $fields = explode(',', $field_name);
        foreach($fields as $field){
            if(count($fields) == 1){
                if(isset($data['params'][$field]) && !empty($data['value']) && !is_array($data['value'])){
                    if($data['params'][$field]['type'] == 'file_image'){
                        if($this->_row_height == $this->_row_height_base || $this->_row_height < $data['params'][$field]['file_thumbs_size'])
                            $this->_row_height = $data['params'][$field]['file_thumbs_size'];
                    }
                    /*elseif($data['params'][$field]['type'] == 'relate' || $data['params'][$field]['is_primary'] == true){
                        if($this->_row_height == $this->_row_height_base || $this->_row_height < 30)
                            $this->_row_height = 30;
                    }*/
                } elseif(is_array($data['value'])){
                    foreach($data['value'] as $value){
                        if(!isset($value['params']['params'])) continue;
                        if($value['params']['params']['type'] == 'file_image'){
                            if($this->_row_height == $this->_row_height_base || $this->_row_height < $value['params']['params']['file_thumbs_size'])
                                $this->_row_height = $value['params']['params']['file_thumbs_size'];
                        }
                        /*elseif($value['params']['params']['type'] == 'relate' || $value['params']['params']['is_primary'] == true){
                            if($this->_row_height == $this->_row_height_base || $this->_row_height < 30)
                                $this->_row_height = 30;
                        }*/
                    }
                }
            }
        }
    }


    /**
    * формирование таблицы данных
    */
    private function addData($data){
        $data = DataValueModel::getInstance()
                    ->setSchemaFields($this->_schema)
                    ->setExtensionCopy($this->_extension_copy)
                    ->setFileType(DataValueModel::FILE_TYPE_ALL)
                    ->setFileThumbsSize($this->_file_thumbs_size)
                    ->setFileReturnModel(true)
                    //->setAddAvatar($this->_export_avatar)
                    ->setAddAvatar(false)
                    ->setOnlyRelateId(true)
                    ->prepareData($data)
                    ->getProcessedData() // без обьеденения значений
                    //->concatProcessedData() // обьеденяет значения
                    ->getData();

        $col_end = 0;
        foreach($data as $data_row){
            $this->_row_height = $this->_row_height_base;
            foreach($this->_fields_params as $field){
                $this->setRowHeight($field['name'], $data_row[$field['name']]);
                $this->addToCell($field['name'], $data_row[$field['name']]);
                $this->setCol(1, true);
            }
            $this->setRowSize($this->_row_height / self::RATIO_HEIGHT);

            $col_end = $this->_col;
            $this->setRow(1, true);
            $this->setCol(0, false);
        }

        $this->setCol($col_end, false);

        return $this;
    }



    /**
    *  устанавливает значение начала или конца координат таблицы
    */
    private function setTablePosition($position = 'start'){
        if($position == 'start'){
            $this->_col_table_start = $this->_col;
            $this->_row_table_start = $this->_row;
        } elseif($position == 'end'){
            $this->_col_table_end = $this->_col;
            $this->_row_table_end = $this->_row;
        }
        return $this;
    }



    /**
    * красим таблицу в сетку
    */
    /*
    private function setTableBorder(){
        for($col = $this->_col_table_start; $col < $this->_col_table_end; $col++){
            for($row = $this->_row_table_start; $row < $this->_row_table_end; $row++){
                $borders = $this->_object->setActiveSheetIndex($this->_sheet_index)->getStyleByColumnAndRow($col, $row)->getBorders();
                $borders->getTop()->applyFromArray(array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getBottom()->applyFromArray(array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getLeft()->applyFromArray(array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getRight()->applyFromArray(array('style' =>PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
            }
        }
        return $this;
    }
    */


    /*
    private function setMaxColumnWidth($find_width = 40, $set_width = 40){
         for($i = 0; $i < $this->_col_table_end; $i++){
            $this->_col = $i;
            $column = $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col);
            if($column->getWidth() > 10){
                $column->setAutoSize(false);
                $this->setWidth($set_width);
            }
         }
         return $this;
    }
    */


    /**
    *   создает документа Excel
    */
    public function makeExcelFromListView($data, $fields=false){
        ini_set('max_execution_time', 3600); // 1ч
        $this
             //->addHeader($this->_extension_copy->title)
             //->setCol(0, false)->setRow(2, true)
             ->setTablePosition('start')
             ->addFieldHeaders($fields)
             ->setCol(0, false)
             ->setRow(1, true)
             ->addData($data)
             ->setTablePosition('end');
             //->setTableBorder();

        return $this;
    }



    /**
     * getParams - устанавливает параметры для будущего файла
     */
    public function setParams($file_type = self::TYPE_EXCEL){
        switch($file_type){
            case self::TYPE_EXCEL:
                $this->_params = array(
                    'content_type' => '',
                    'file_ext_type' => '.xlsx',
                    'class_const' => 'Excel2007',
                );
                break;
            case self::TYPE_PDF:
                $this->_params = array(
                    'content_type' => 'application/pdf',
                    'file_ext_type' => '.pdf',
                    'class_const' => 'PDF',
                );
                break;
        }

        return $this;
    }




    /**
    *  созвращает сформированный документ
    *  @param bool $return_fn - возвратить название файла
    */
    public function prepareDocument($save = true){
        if($this->_params['class_const']){
            PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
        }

        $this->_obj_writer = PHPExcel_IOFactory::createWriter($this->_object, $this->_params['class_const']);

        if($save){
            $this->save();
        }

        return $this;
    }



    public function save(){
        $this->_tmp_file_name =  \FileOperations::getTempFileName('phpxltmp');
        if($this->_tmp_file_name === false){
            return $this;
        }

        $this->_obj_writer->save($this->_tmp_file_name);

        return $this;
    }



    /**
     * loadHtml - возвращает документ в вывод
     * @return bool
     */
    public function loadHtml(){
        if($this->_tmp_file_name === false){
            return false;
        }

        header('Content-Type: ' . $this->_params['content_type']);
        header('Content-Disposition: attachment;filename="' . $this->_extension_copy->title . $this->_params['file_ext_type'] . '"');
        header('Cache-Control: max-age=0');

        echo file_get_contents($this->_tmp_file_name);

        unlink($this->_tmp_file_name);
    }



    /**
     * copyTo - копирует экспортированный файл в директорию
     */
    public function copyTo($destination_dir, $fprefix_name = '', $translit_file = true){
        if($this->_tmp_file_name === false){
            return false;
        }

        $file_name = $this->_extension_copy->title . $fprefix_name . $this->_params['file_ext_type'];
        if($translit_file){
            $file_name = Translit::forFileName($file_name);
        }

        $new_fname = $destination_dir . '/' . $file_name;
        $content = file_get_contents($this->_tmp_file_name);

        file_put_contents($new_fname, $content);
        unlink($this->_tmp_file_name);

        return $file_name;
    }

}




