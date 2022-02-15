<?php

class QuickViewElements extends CWidget {

    public $view;
    public $quick_view_model;


    public function init(){
        switch($this->view){
            case 'button_switch' :
                echo $this->getButtonSwitch();
                break;
        }
    }




    private function getButtonSwitch(){
        $data = [
            'block_model_list' => QuickViewModel::getInstance()->getBlockModelListByGroupName($this->quick_view_model->getBlockGroupName()),
        ];

        return $this->render('buttons_switch', $data, true);
    }




}
