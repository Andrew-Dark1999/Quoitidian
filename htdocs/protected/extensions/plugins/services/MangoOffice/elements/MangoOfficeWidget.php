<?php
/**
* MangoOfficeWidget
*/ 

class MangoOfficeWidget extends CWidget{

    public $data;
    public $view;

    public function init(){
        $this->render($this->view, $this->data);
    }
 

}
