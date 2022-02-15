<?php
/**
* PPanelEdit widget  
* @author Alex R.
* @version 1.0
*/ 

class PPanelEdit extends CWidget{
    
    public $view;
    public $fields_data;
    public $extension_copy;
    public $content;
    
    
    private function renderElement($params, $field_data){
        return $this->render($this->view, array(
                                'params' => $params,
                                'field_data' => $field_data,
                            ), true);
    }
    

    private function getElement(){
        $result = '';
        foreach($this->fields_data as $field_data){
            $params = $this->extension_copy->getFieldSchemaParams($field_data['field_name']);
            switch ($params['params']['type']){
                case 'string' :
                    $result.= $this->renderElement($params, $field_data); 
                    break;
                case 'select' :
                    $result.= $this->renderElement($params, $field_data); 
                    break;
            }
        }
        return $result;
    }



    private function getBlock(){
        return $this->render($this->view, array(
                                'content' => $this->content,
                            ), true);
    }
    



    public function init(){
        $html = '';
        switch($this->view){
            case 'block' :  $html = $this->getBlock();
            break;
            case 'element' :  $html = $this->getElement();
            break;
        }
        
        echo $html;
    }

}



