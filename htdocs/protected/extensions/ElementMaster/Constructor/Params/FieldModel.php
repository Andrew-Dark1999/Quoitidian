<?php


class FieldModel extends CFormModel{
    public $name;
    public $title;
    public $description;
    public $default_value;
    public $required;
    
    public $hidden = false;
    public $destroy = true;
      
    
    public function rules(){
        return array(
        
        
        );
    }
    
    
}