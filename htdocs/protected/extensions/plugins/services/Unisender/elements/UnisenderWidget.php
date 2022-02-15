<?php
/**
* UnisenderWidget
*/ 

class UnisenderWidget extends CWidget{

    public $data;
    public $view;

    public function init(){
        $this->render($this->view, $this->data);
    }

}
