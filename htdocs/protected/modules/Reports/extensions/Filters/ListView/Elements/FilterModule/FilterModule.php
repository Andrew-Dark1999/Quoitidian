<?php
/**
* FilterModule widget
* @author Alex R.
* @version 1.0
*/ 
namespace Reports\extensions\Filters\ListView\Elements\FilterModule;

class FilterModule extends \CWidget{

    public $modules = array();
    public $selected_copy_id = null;

    public function init(){
        $this->render('element', array(
                                    'modules' => $this->modules,
                                    'selected_copy_id' => $this->selected_copy_id,
                                 )
                                );
    }
 

}
