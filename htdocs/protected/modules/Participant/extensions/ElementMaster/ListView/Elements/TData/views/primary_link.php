<?php
switch($primary_link){
    case ListViewBulder ::PRIMARY_LINK_EDIT_VIEW :
            if($this->editViewIsEnable()){
                echo '<a href="javascript:void(0)" class="edit_view_show name" data-controller="ev">' . $value . '</a>';
            } else {
                echo $value;
            }
            break;
    case ListViewBulder::PRIMARY_LINK_EDIT_VIEW_SUBMODULE :
            echo '<a href="javascript:void(0)" class="submodule_edit_view_dnt-edit">' . $value . '</a>';
            break;
    case ListViewBulder::PRIMARY_LINK_LIST_VIEW :
            echo '<a href="javascript:void(0)" class="navigation_module_link_child" data-action_key="'. (new \ContentReloadModel(8))->addVars(['module' => ['copy_id' => $params['relate_module_copy_id'], 'params'=> ['pci'=>$extension_copy->copy_id,'pdi'=>$value_data[$extension_copy->prefix_name . '_id']]]])->prepare()->getKey() .'">' . $value . '</a> ' .
                ($params['edit_view_edit'] == true ? '<a href="javascript:void(0)" class="edit_view_show name pencil" data-controller="ev"><i class="fa fa-pencil"></i></a>' : '');
            break;
    case ListViewBulder::PRIMARY_LINK_NONE_LINK :
            echo $value;
            break;
    default :
        echo $value;
}
