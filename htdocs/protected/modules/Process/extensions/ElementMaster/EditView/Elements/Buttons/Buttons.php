<?php
/**
* Buttons widget  
* @author Alex R.
* @version 1.0
*/

namespace Process\extensions\ElementMaster\EditView\Elements\Buttons;


class Buttons extends \Buttons{


    public $operations_model;


    public function getGroupButtonsIndex(){
        return \Process\extensions\ElementMaster\EditViewBuilderForCard::getGroupButtonsIndex($this->operations_model);
    }



}
