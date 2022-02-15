<?php
/**
* Buttons widget  
* @author Alex R.
* @version 1.0
*/ 




class Buttons extends CWidget{

    // Схема
    public $schema;
    // контент
    public $content;
    // Елемент отображения
    public $view = 'block';
    //
    public $extension_copy;
    //
    public $extension_data;
    // атрибут новой строки 
    public $is_new_record = true;
    // значение по умолчанию
    public $default_data = null;

    public $button_attr = array(
        'save' => array(
                    'class' => 'edit_view_btn-save',
                ),
    );
    
    
    public function init()
    {
    
        $get_related_responsible = true;
        
        //в случае, если документ создается из шаблона и модуль Документы, ответственного не загружаем
        if(Yii::app()->request->getParam('from_template') && $this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {
            $get_related_responsible = false; 
        }    
    
        $this->render($this->view, array(
                                    'schema' => $this->schema,
                                    'content' => $this->content,
                                    'extension_copy' => $this->extension_copy,
                                    'extension_data' => $this->extension_data,
                                    'is_new_record' => $this->is_new_record,
                                    'default_data' => $this->default_data,
                                    'get_related_responsible' => $get_related_responsible,
                                 )
                                );
        
        
    }




    public function isDateTimeAllDay(){
        $field_name = $this->schema['params']['name'] . '_ad';
        $value = $this->extension_data->{$field_name};

        return (bool)$value;
    }


    private function getDateTime(){
        $date_time = null;

        if($this->is_new_record){
            if(!empty($this->default_data)){
                $date_time = date(LocaleCRM::getInstance2()->_data_p['dateTimeFormats']['medium_short'], strtotime($this->default_data));
            }
        } else {
            $date_time = $this->extension_data->{$this->schema['params']['name']};
        }

        return $date_time;
    }


    public function getDateTimeFormat(){
        $date_time = $this->getDateTime();

        if($this->isDateTimeAllDay()){
            $result = Helper::formatDate($date_time);
        } else {
            $result = Helper::formatDateTimeShort($date_time);
        }

        return $result;
    }


    public function getDateTimeColor(){
        $date_time = $this->getDateTime();

        if(empty($date_time) || !strtotime($date_time)){
            return;
        }

        $color = null;

        $date_diff = DateTimeOperations::dateDiff($date_time, date('Y-m-d H:i:s'));

        if($date_diff !== null && $date_diff === -1){
            $color = 'red';
        }

        return $color;
    }

}
