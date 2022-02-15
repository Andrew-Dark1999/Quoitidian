<?php
    if($params['type'] == 'numeric' ||
       $params['type'] == 'display' ||
       $params['type'] == 'relate_string'){

        $class = ['form-control'];
        if($params['type'] == 'numeric'){
            if(!empty($params['add_hundredths'])){
                $class[] = 'add_hundredths';
            }
            if(!empty($params['money_type'])){
                $class[] = 'money_type';
            }
        }

        $text_field = CHtml::textField(
            'EditViewModel[' . $params['name'] . ']',
            '',
            array(
                'id' => $params['name'],
                'class' => implode(' ', $class),
            )
        );
        echo Helper::deleteLinefeeds($text_field);

        //string
    } elseif($params['type'] == 'string'){
        if(in_array($params['size'], array(FieldTypes::TYPE_SIZE_TEXT, FieldTypes::TYPE_SIZE_MEDIUMTEXT))){
            echo CHtml::textArea('EditViewModel[' . $params['name'] . ']', '', array('id' => $params['name'], 'class' => 'form-control'));
        } else {
            echo CHtml::textField('EditViewModel[' . $params['name'] . ']', '', array('id' => $params['name'], 'class' => 'form-control'));
        }

    //select
    }elseif($params['type'] == 'select'){
        $select_list = DataModel::getInstance()->setFrom($extension_copy->getTableName($params['name']))
                                                    ->findAll();
        $select = array();
        foreach($select_list as $value)
        $select[$value[$params['name'] . '_id']] = Yii::t('PermissionModule.base', $value[$params['name'] . '_title']);
        if(!isset($params['add_zero_value']) || $params['add_zero_value'] === true) $select = array('' => '') + $select;
        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $select, array('id'=>$params['name'], 'class'=>'select')));


    //access
    }elseif($params['type'] == 'access'){
        $select_list = AccessModel::getInstance()->getSelectAccessList();
        $select = '';
        foreach($select_list as $value){
            $select.= '<option value="' . $value['id'] . '" data-type="' . $value['type'] . '">' . ($value['type'] == 'module' ? $value['title'] : Yii::t('base', $value['title'])) . '</option>';
        }
        $str = <<<EOD
                <select class="select element_edit_access" name="EditViewModel[{$params['name']}]" id="{$params['name']}" >
                    $select
                </select>
EOD;
            echo Helper::deleteLinefeeds($str);
/*
    //permission
    }elseif($params['type'] == 'permission'){
        $select = PermissionModel::getInstance()->getSelectPermissionList();
        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $select, array('id'=>$params['name'], 'class'=>'select')));
*/        
        
    //logical
    }elseif($params['type'] == 'logical'){
        $logical = Fields::getInstance()->getLogicalData();
        if(!isset($params['add_zero_value']) || $params['add_zero_value'] === true) $logical = array('' => '') + $logical;
        
        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $logical, array('id'=>$params['name'], 'class'=>'select')));
    
    //datetime
    }elseif($params['type'] == 'datetime'){
        $date_element = CHtml::textField('EditViewModel['.$params['name'].']',
                                         '',
                                         array(
                                            'class' => 'form-control date-time',
                                            'value' => '',
                                          )
                                        );             
        $time_element = CHtml::textField('EditViewModel['.$params['name'].']',
                                         '',
                                         array(
                                            'class' => 'form-control time',
                                            'value' => '',
                                          )
                                        );        
        

        $str = <<<EOD
        <div class="input-group form-datetime" style="float: left; padding-right: 5px;">
            $date_element
            <span class="input-group-btn">
                <button type="button" class="btn btn-default date-set"><i class="fa fa-calendar"></i></button>
            </span>
        </div>
        <div class="input-group form-datetime bootstrap-timepicker">
            $time_element
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
            </span>
        </div>
EOD;
        echo Helper::deleteLinefeeds($str);
    
    //relate
    } elseif($params['type'] == 'relate'){
        $relate_module = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
        
        if($relate_module->getModule(false)->isTemplate($relate_module) && (!empty($parent_copy_id) && $parent_copy_id == $relate_module->copy_id)){
            $str1 = <<<EOD
                 <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                    <button  
                           disabled="disabled" 
                           name="EditViewModel[{$params['name']}]"
                           class="btn btn-white dropdown-toggle element element_relate"
                           type="button"
                           data-toggle="dropdown"
                           data-type="drop_down_button"
                           value=""
                           data-id=""
                           data-relate_copy_id="{$params['relate_module_copy_id']}"
                           >
                    </button>
                 </div>
EOD;
            
            echo Helper::deleteLinefeeds($str1);            
        } else {
            $select_list = DataModel::getInstance()->setFrom($relate_module->getTableName())
                                                        ->findAll();
            $find_str = Yii::t('base', 'Search');
            $str1 = <<<EOD
                 <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
    
                    <button  
                           name="EditViewModel[{$params['name']}]"
                           class="btn btn-white dropdown-toggle element element_relate"
                           type="button"
                           data-type="drop_down_button"
                           data-toggle="dropdown"
                           value=""
                           data-id=""
                           data-relate_copy_id="{$params['relate_module_copy_id']}"
                           >
                    </button>
                    <ul
                        class="dropdown-menu element"
                        data-type="drop_down_list"
                        data-there_is_data="0"
                        data-relate_copy_id="{$params['relate_module_copy_id']}"
                        role="menu"
                        aria-labelledby="dropdownMenu1"
                    >
                        <div class="search-section">
                            <input type="text" class="submodule-search form-control" placeholder="{$find_str}">
                        </div>
        
                        <div class="submodule-table">
                            <table class="table list-table">
                            <tbody>
EOD;
                            $str2 = '';
                            foreach($select_list as $value){
                                $data = DataValueModel::getInstance()
                                                        ->setFileLink(false)
                                                        ->getRelateValuesToHtml($value, $params);
                                $str2 .= <<<EOD
                                <tr class="sm_extension_data" data-id="{$value[$relate_module->prefix_name . '_id']}">
                                    <td>
                                        <span href="javasctript:void(0)" class="name">{$data}</span>
                                    </td>
                                </tr>
EOD;
                            }
    $str1 = $str1 . $str2 . <<<EOD
                            </tbody>
                            </table>
                        </div>
                    </ul>
                </div> 
EOD;
        echo Helper::deleteLinefeeds($str1);
    }

    //file, file_image
    } elseif($params['type'] == 'file' ||
             $params['type'] == 'file_image'){
        $element_html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                               array(
                                'schema' => array('params' => $params),
                                'upload_model' => null,
                                'extension_copy' => $extension_copy,
                                'extension_data' => null,
                               ),
                               true);
        echo '<div class="file-box" data-name="EditViewModel['.$params['name'].']">'.Helper::deleteLinefeeds($element_html).'</div>';
    } else {
        
    }

?>
