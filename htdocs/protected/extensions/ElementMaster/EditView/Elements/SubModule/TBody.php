<?php
/**
* TBody widget  
* @author Alex R.
* @version 1.0
*/ 

class TBody extends CWidget{

    
    public $parent_field_schema;
    public $field_params;
    
    public $extension_copy_data;
    public $extension_copy_relate;
    
    

    public function init(){
        $primary_link = ListViewBulder::PRIMARY_LINK_NONE_LINK;
        if(Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_VIEW, $this->parent_field_schema['params']['relate_module_copy_id'])){
            $primary_link = ListViewBulder::PRIMARY_LINK_EDIT_VIEW_SUBMODULE;
        }
        
        
        $this->render('tbody', array(
                                    'field_params' => $this->field_params,
                                    'extension_copy_data' => $this->extension_copy_data,
                                    'extension_copy_relate' => $this->extension_copy_relate,
                                    'primary_link' => $primary_link,
                                 )
        );
    }
 

}