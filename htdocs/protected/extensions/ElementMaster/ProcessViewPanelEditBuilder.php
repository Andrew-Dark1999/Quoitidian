<?php
/**
 * ProcessViewPanelEditBuilder
 *
 * 
 *
 */
class ProcessViewPanelEditBuilder{
    
    private $_fields_data;
    private $_extension_copy = null;


    public static function getInstance(){
        return new self();
    }
    
    
    
    
    public function setFieldsData($fields_data){
        $this->_fields_data = $fields_data;
        
        return $this;
    }
    

    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }
    
    
    /**
    * строит елементы полей  
    * @return string (html) 
    */
    public function buildElemenets(){
        $result = $this->getElement();
        $result = $this->getBlock($result);
        
        return $result;
    }    



    /**
    * Возвращает блок
    * @return string (html)  
    */
    public function getBlock($content){
        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ProcessView.Elements.PPanelEdit.PPanelEdit'),
                                   array(
                                    'view' => 'block',
                                    'content' => $content,
                                   ),
                                   true);
        return $result; 
    }
    


    
    /**
    * Возвращает елемент
    * @return string (html)  
    */
    public function getElement(){
        $result = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ProcessView.Elements.PPanelEdit.PPanelEdit'),
                                   array(
                                    'view' => 'element',
                                    'extension_copy' => $this->_extension_copy,
                                    'fields_data' => $this->_fields_data,
                                   ), true);
        return $result; 
    }
    
    
    
    /**
     * возвращает заголовок панели (списка) 
     */
    public function getPanelTitleValue($field_name, $value){
        $text = '';
        $params = $this->_extension_copy->getFieldSchemaParams($field_name);
        switch($params['params']['type']){
            case 'select' :
                $data_model = new DataModel();
                $data = $data_model
                    ->setFrom($this->_extension_copy->getTableName($field_name))
                    ->setWhere($field_name .'_id=:id', array(':id'=>$value))
                    ->findRow();
                if(!empty($data)) $text = $data[$field_name . '_title'];
                break;
        }
        
        return $text;
    }
    
    
    
}
