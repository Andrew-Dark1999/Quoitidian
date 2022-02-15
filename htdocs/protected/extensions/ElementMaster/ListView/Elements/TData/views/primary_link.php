<?php
switch($primary_link){
    case ListViewBulder::PRIMARY_LINK_EDIT_VIEW :
            if($this->editViewIsEnable()){
                echo '<a href="javascript:void(0)" class="edit_view_show name lessening" data-controller="ev">' . $value . '</a>';
            } else {
                echo $value;
            }
            break;
    case ListViewBulder::PRIMARY_LINK_REPORT :
        echo '<a href="javascript:void(0)" class="modal_dialog name lessening" data-controller="edit_view_report">' . $value . '</a>';
        break;
    case ListViewBulder::PRIMARY_LINK_EDIT_VIEW_SUBMODULE :
                    $a_params = EditViewModel::getSubModuleLink(array(
                                                'extension_copy' => $this->extension_copy,
                                                'params' => $params,
                                                'id' => $value_data[$this->extension_copy->prefix_name . '_id'],
                                                'this_template' => \Yii::app()->request->getPost('this_template'),
                                                ));
            echo "<a href='".$a_params['href']."' target='".$a_params['target']."' data-target='".$a_params['data-target']."' class='navigation_module_link_child_from_submodule'>" . $value . "</a>";

            break;
    case ListViewBulder::PRIMARY_LINK_LIST_VIEW :
            if($denied_relate['be_fields'] == true){
                echo
                    ($params['edit_view_edit'] == true ? '<a href="javascript:void(0)" class="edit_view_show name pencil" data-controller="ev"><i class="fa fa-pencil"></i></a> ' : '') .
                    '<a href="javascript:void(0)" class="navigation_module_link_child lessening" data-action_key="'. (new \ContentReloadModel(8))->addVars(['module' => ['copy_id' => $params['relate_module_copy_id'], 'params'=> ['pci'=>$extension_copy->copy_id,'pdi'=>$value_data[$extension_copy->prefix_name . '_id']]]])->prepare()->getKey() .'">'  . $value . '</a>';
            } else {
                if($params['edit_view_edit'] == true)
                    echo '<a href="javascript:void(0)" class="edit_view_show name lessening" data-controller="ev">' . $value . '</a>';
                else 
                    echo $value;
            }
            break;
    case ListViewBulder::PRIMARY_LINK_REPORTS_LIST_VIEW :
        if($denied_relate['be_fields'] == true){
            echo
            ($params['edit_view_edit'] == true ? '<a href="javascript:void(0)" class="edit_view_constructor_show name pencil" data-controller="ev"><i class="fa fa-pencil"></i></a> ' : '') .
                '<a href="javascript:void(0)" class="navigation_module_link_child lessening" data-action_key="'. (new \ContentReloadModel(8))->addVars(['module' => ['copy_id' => $extension_copy->copy_id]])->prepare()->getKey() .'">' . $value . '</a>';
        } else {
            if($params['edit_view_edit'] == true)
                echo '<a href="javascript:void(0)" class="edit_view_show name lessening" data-controller="ev">' . $value . '</a>';
            else
                echo $value;
        }
        break;
    case ListViewBulder::PRIMARY_LINK_NONE_LINK :
            echo $value;
            break;
    default :
        echo $value;
}
