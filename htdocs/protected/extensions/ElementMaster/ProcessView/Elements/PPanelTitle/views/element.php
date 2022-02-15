<?php
//numeric, string
if($params['type'] == 'numeric'){
    $attr_value = $value_data[$params['name']];
    echo Helper::TruncateEndZero($attr_value);
}
//string
elseif($params['type'] == 'string' || $params['type'] == 'display' || $params['type'] == 'relate_string'){
    //while type = password
    $attr_value = $value_data[$params['name']];
    if(isset($params['input_attr'])){
        $attr_tmp = json_decode($params['input_attr'], true);
        if(!empty($attr_tmp)){
            if(in_array('password', $attr_tmp)){
                $attr_value = '';
            }
        }
    }

    echo '<span class="text">'.$attr_value.'</span>';
}

//datetime
elseif($params['type'] == 'datetime'){
    $date_full = Helper::formatDateTimeShort($value_data[$params['name']]);

    if($params['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
        if($this->isDateTimeAllDay()){
            $date_full = Helper::formatDate($value_data[$params['name']]);
        } else {
            $date_full = Helper::formatDateTimeShort($value_data[$params['name']]);
        }
    }
    echo $date_full;
}

//logical
elseif($params['type'] == 'logical'){
    $logical = Fields::getInstance()->getLogicalData();
    if(isset($logical[$value_data[$params['name']]])){
        echo $logical[$value_data[$params['name']]];
    }
}

//select
elseif($params['type'] == 'select'){
    echo $this->getHtmlSelect();
}

//relate
elseif($params['type'] == 'relate'){
    $html = $this->getHtmlRelate();
    echo $html;
}

//relate_dinamic
elseif($params['type'] == 'relate_dinamic'){
    $vars = get_defined_vars();
    unset($vars['params']);
    unset($vars['value_data']);
    $vars['schema']['params'] = $params;
    $vars['extension_data'] = $value_data;

    $ddl_data = \DropDownListModel::getInstance()
        ->setActiveDataType(\DropDownListModel::DATA_TYPE_5)
        ->setVars($vars)
        ->prepareHtml()
        ->getResultHtml();

    if($ddl_data['status'] == false){
        return;
    }

    echo $ddl_data['html'];
}


//relate_this
elseif($params['type'] == 'relate_this'){
    $id = $value_data[$params['name']];
    if($id){
        $relate_data = DataModel::getInstance()
            ->setFrom($extension_copy->getTableName())
            ->setWhere($extension_copy->prefix_name . '_id = :id', array(':id' => $id))
            ->findAll();
    }


    $html = array();
    if(!empty($relate_data))
        foreach($relate_data as $relate_value){
            $html[] = DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
        }

    echo '<span
           class="element_data"
           data-name="'.$params['name'].'"
           data-id="'.$id.'"
           data-relate_copy_id="'.\ExtensionCopyModel::MODULE_STAFF.'"
           ><span><a href="javascript:void(0)" class="edit_view_show" data-controller="sdm" >' . implode(' ', $html) . '</a></span></span>';


}


//responsible
elseif($params['type'] == 'relate_participant' && ($params['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE || $params['type_view'] ==  Fields::TYPE_VIEW_BLOCK_PARTICIPANT)) {
    echo $this->getHtmlRelateParticipant();
}
