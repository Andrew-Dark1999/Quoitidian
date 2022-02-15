<?php


class DropDownList extends CWidget{

    /*
    constaints views in the class DropDownListModel
    const WIDGET_VIEW_BUTTON    = 'view_button';
    const WIDGET_VIEW_SPAN      = 'view_span';
    const WIDGET_OPTIONS        = 'options';
    const WIDGET_OPTION         = 'option';
    */

    public $view;
    public $vars;
    public $view_checkbox = false;



    public function init(){
        return $this->render($this->view, $this->vars);
    }


}
