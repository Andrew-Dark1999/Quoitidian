<?php

namespace Reports\extensions;


class ExcelExport{

    private $_object;

    private $_extension_copy;

    private $_schema;

    private $_schema_data;
    private $_data;
    private $_indicators;
    private $_dv_model;

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

    private $_sheet_index = 0;
    private $_export_avatar = true;

    private $_hidden_column_group_index = array();

    const RATIO_WIDTH = 6.9;
    const RATIO_HEIGHT = 1.2;

    // масив подготовленных схемы полей
    private $fields_params = array();



    public function __construct(){
        $this->_object = new \PhpExcel();
        $this->_col_width_base = $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->getWidth();
        $this->_row_height_base = $this->_object->setActiveSheetIndex($this->_sheet_index)->getRowDimension($this->_row)->getRowHeight();
    }


    public static function getInstance(){
        return new self();
    }

    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
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


    private function inGroupIndex($group_index){
        if(empty($this->_hidden_column_group_index)) return false;
        if(in_array($group_index, $this->_hidden_column_group_index)) return true;
        return false;
    }


    public function setSchema(){
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


    private function setCol($column, $append = true){
        if($append)
            $this->_col+= $column;
        else
            $this->_col = $column;
        return $this;
    }

    private function setRow($row, $append = true){
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



    private function setCellValueByColumnAndRow($value){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->setCellValueByColumnAndRow($this->_col, $this->_row, $value);

    }

    private function setCellValueByColumnAndRowFromArray($value){
        $this->_object->setActiveSheetIndex($this->_sheet_index)->setCellValueByColumnAndRow($this->_col, $this->_row, $value);
    }

    private function setCellImage($file_name){
        if(empty($file_name)) return;
        if(!file_exists($file_name)) return;
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
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
        $activeCell->getAlignment()
                   ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        switch($type){
            case 'display':
            case 'string':
            case 'logical':
            case 'select':
            case 'file':
                if(!empty($params) && $params['is_primary']){
                    if($this->_export_avatar && $params['avatar']) $activeCell->getAlignment()->setIndent(round(30 / 10)+1);
                }
                $activeCell->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $activeCell->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                break;
            case 'numeric':
            case 'datetime':
                $activeCell->getAlignment()
                           ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                break;
            case 'file_image':
                if(!empty($params)) $activeCell->getAlignment()->setIndent(round($params['file_thumbs_size'] / 10)+1);
                $activeCell->getAlignment()
                           ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $activeCell->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $activeCell->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                break;
            case 'relate':
            case 'relate_string':
                $activeCell->getAlignment()
                           ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $activeCell->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $activeCell->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                break;
        }

    }


    /**
    *   Добавляем картинку
    */
    /*
    private function addImageToCell($files){
        if(is_array($files)){
            foreach($files as $file_name){
                $this->setCellImage($file_name, $this->_sheet_index);
            }
        } else $this->setCellImage($files, $this->_sheet_index);
    }
    */




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




    /**
    *  Вставка елемента данных из массыва данних
    */
    private function addToCell($data){
        if($data === false) return;

        if(empty($data['params'])){
            $this->setCellValueByColumnAndRow($data['value']);
            $this->setStyleCell();
            return;
        }


        $field_name = $data['field_name'];

        if(isset($data['params'][$field_name])){
            if($data['params'][$field_name]['type'] != 'relate'){
                if(isset($data['value']) || isset($data['files'][$field_name])){
                    if(isset($data['value'])) $this->setCellValueByColumnAndRow($data['value']);
                    if(isset($data['files'][$field_name])) $this->addImageToCell($data['files'][$field_name]);
                    //$this->setStyleCell($data['params'][$field_name]['type'], $data['params'][$field_name]); // отступ для картинки
                }
            } elseif($data['params'][$field_name]['type'] == 'relate') {
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
        } else {

            if(isset($data['value']) || isset($data['files'][$field_name])){
                if(isset($data['value'])) $this->setCellValueByColumnAndRow($data['value']);
                if(isset($data['files'][$field_name])) $this->addImageToCell($data['files'][$field_name]);
                if(isset($data['params'][$field_name])) $this->setStyleCell($data['params'][$field_name]['type'], $data['params'][$field_name]);
            }
        }
    }


    /**
    * Добавляем заголовок документа
    */
    private function addHeader($header){
        $this->setCellValueByColumnAndRow($header);

        $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);
        $this->_object->getActiveSheet()->getCellByColumnAndRow($this->_col, $this->_row)->setDataType(\PHPExcel_Cell_DataType::TYPE_STRING);

        $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());
        $activeCell->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $activeCell->getFont()->setBold(true)->setSize(14);


        return $this;
    }




    /**
    * добавляем заголок таблицы
    */
    private function addFieldHeaders(){
        if(empty($this->_indicators)) return false;
        foreach($this->_indicators as $indicator){
            // title
            $this->setCellValueByColumnAndRow($indicator['title']);

            $this->_object->setActiveSheetIndex($this->_sheet_index)->setSelectedCellByColumnAndRow($this->_col, $this->_row);

            $activeCell = $this->_object->getActiveSheet()->getStyle($this->_object->getActiveSheet()->getActiveCell());
            $activeCell->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeCell->getFont()->setBold(true);
            $this->_object->getActiveSheet()->getCellByColumnAndRow($this->_col, $this->_row)->setDataType(\PHPExcel_Cell_DataType::TYPE_STRING);

            // ширина стобца
            if(isset($this->_col_width['f'.$indicator['unique_index']])){
                $col_width = $this->_col_width['f'.$indicator['unique_index']];
                $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->setWidth(round($col_width / self::RATIO_WIDTH));
            } else {
                $this->_object->setActiveSheetIndex($this->_sheet_index)->getColumnDimensionByColumn($this->_col)->setAutoSize(true);
            }

            $this->setCol(1, true);
        }

        return $this;
    }




    /**
    *   установка высоты для всего ряда
    */
    private $_row_height = -1;
    private function setRowHeight($field_name, $data){
        $field = $data['field_name'];
        if(!$field) return;

        if(isset($data['params'][$field]) && !empty($data['value']) && !is_array($data['value'])){
            if($data['params'][$field]['type'] == 'file_image'){
                if($this->_row_height == $this->_row_height_base || $this->_row_height < $data['params'][$field]['file_thumbs_size'])
                    $this->_row_height = $data['params'][$field]['file_thumbs_size'];
            }elseif($data['params'][$field]['type'] == 'relate' || $data['params'][$field]['is_primary'] == true){
                if($this->_row_height == $this->_row_height_base || $this->_row_height < 30)
                    $this->_row_height = 30;
            }
        }
    }


    /**
     * getTd
     */
    public function getTd($unique_index, $value, $data_id){
        $result = array(
                    'value' => null,
                    'params' => null,
                    'files' => null,
                    'field_name' => null,
                    );
        $schema_param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->_schema_data, 'data_analysis_param');

        if(\Reports\models\ConstructorModel::isPeriodConstant($schema_param['field_name']) && $unique_index == 'param_x'){
            $result['value'] = $value;
            return $result;
        } else {
            if($unique_index == 'param_x'){
                $field_name = $schema_param['field_name'];
                if($field_name == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID)
                    $field_name = 'module_title';
                return $this->getTdElememnt($field_name, $this->_data[$data_id]);
            } else {

                $schema_indicators = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($this->_schema_data, 'data_analysis_indicator');
                foreach($schema_indicators as $indicator){
                    if('f' . $indicator['unique_index'] == $unique_index){
                        if($schema_param['field_name'] == \Reports\models\ConstructorModel::PARAM_FIELD_NAME_ID){
                            if($indicator['module_copy_id'] == $schema_param['module_copy_id']){
                                return $this->getTdElememnt($indicator['field_name'], $this->_data[$data_id]);
                            } else {
                                $result['value'] = \Reports\models\ConstructorModel::formatNumber($indicator['type_indicator'], $value, ',', ' ', array('percent_value' => '%'));
                                return $result;
                            }
                        } else {
                            $result['value'] = \Reports\models\ConstructorModel::formatNumber($indicator['type_indicator'], $value, ',', ' ', array('percent_value' => '%'));
                            return $result;
                        }
                    }
                }
            }
        }

        return false;
    }




    /**
     * getTdElememnt
     */
    private function getTdElememnt($field_name, $data){
        $result = $this->_dv_model
                    ->prepareData(array($data), array($field_name))
                    ->getProcessedData()
                    ->getData();
        $result = $result[0][$field_name];;
        $result['field_name'] = $field_name;
        return $result;
    }




    /**
    * формирование таблицы данных
    */
    private function addData($table_data){
        if(empty($table_data)) return $this;

        $this->_dv_model = new \DataValueModel();
        $this->_dv_model
            ->setSchemaFields($this->_schema)
            ->setExtensionCopy($this->_extension_copy)
            //->setAddAvatar($this->_export_avatar)
            ->setFileReturnModel(true)
            ->setAddAvatar(false)
            ->setFileType(\DataValueModel::FILE_TYPE_ALL);

        foreach($table_data as $row){
            foreach($this->_indicators as $indicator){
                $unique_index = 'f'.$indicator['unique_index'];
                if($indicator['type'] == 'data_analysis_param') $unique_index = 'param_x';
                $data = $this->getTd($unique_index, $row[$unique_index], $row['id']);
                $this->addToCell($data);
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
    private function setTableBorder(){
        for($col = $this->_col_table_start; $col < $this->_col_table_end; $col++){
            for($row = $this->_row_table_start; $row < $this->_row_table_end; $row++){
                $borders = $this->_object->setActiveSheetIndex($this->_sheet_index)->getStyleByColumnAndRow($col, $row)->getBorders();
                $borders->getTop()->applyFromArray(array('style' =>\PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getBottom()->applyFromArray(array('style' =>\PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getLeft()->applyFromArray(array('style' =>\PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
                $borders->getRight()->applyFromArray(array('style' =>\PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '000000')));
            }
        }
        return $this;
    }



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





    /**
    *   создает документа Excel
    */
    public function makeExcelFromListView($data){
        ini_set('max_execution_time', 3600); // 1ч

        $this->_schema_data = $data['schema'];
        $this->_indicators = \Reports\extensions\ElementMaster\Schema::getInstance()->getDataAnalysisEntityesBySchema($data['schema']);

        $schema_param = \Reports\extensions\ElementMaster\Schema::getDataAnalysisElement($data['schema'], 'data_analysis_param');

        $this->_extension_copy = \ExtensionCopyModel::model()->findByPk($schema_param['module_copy_id']);
        $this->setSchema();

        // оригинальные данные listView
        $this->_data = \Reports\models\ReportsTableModel::getInstance()
            ->setSchema($data['schema'])
            ->setData($data['table_data'])
            ->setParentExtensionCopy($this->_extension_copy)
            ->prepare('id')
            ->getResultData();


        $this
         ->setTablePosition('start')
         ->addFieldHeaders()
         ->setCol(0, false)->setRow(1, true)
         ->addData($data['table_data'])
         ->setTablePosition('end')
         ->setTableBorder();

        return $this;
    }




    /**
    *   созвращает сформированный документ
    */
    public function getDocument($file_type = 'excel', $report_title){
        $params = array();
        switch($file_type){
            case 'excel': $params = array(
                                    'content_type' => '',
                                    'file_ext_type' => '.xlsx',
                                    'class_const' => 'Excel2007',
                                    );
                        break;
            case 'pdf': $params = array(
                                    'content_type' => 'application/pdf',
                                    'file_ext_type' => '.pdf',
                                    'class_const' => 'PDF',
                                    );
                        break;
        }
        header('Content-Type: ' . $params['content_type']);
        header('Content-Disposition: attachment;filename="'. $report_title. $params['file_ext_type'] .'"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($this->_object, $params['class_const']);
        $objWriter->save('php://output');

        return;
    }








    
}




