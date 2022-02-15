<?php
    if($params['type'] == 'numeric' ||
       $params['type'] == 'display' ||
       $params['type'] == 'relate_string'){
            $read_only = false;
            if($params['type'] == 'display') {
                //проверяем на автонумерацию
                if(!empty($params['name_generate']) && !$this_template) {
                    $read_only = true;
                }
            }

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
                                        'readonly'=>$read_only,
                                        'class' => implode(' ', $class),
                                    )
                                );
            echo Helper::deleteLinefeeds($text_field);

    //string
    } elseif($params['type'] == 'string'){
        if(in_array($params['size'], array(FieldTypes::TYPE_SIZE_TEXT, FieldTypes::TYPE_SIZE_MEDIUMTEXT))){
            echo Helper::deleteLinefeeds(CHtml::textArea('EditViewModel[' . $params['name'] . ']', '', array('id' => $params['name'], 'class' => 'form-control')));
        } else {
            echo Helper::deleteLinefeeds(CHtml::textField('EditViewModel[' . $params['name'] . ']', '', array('id' => $params['name'], 'class' => 'form-control')));
        }

    //select
    } elseif($params['type'] == 'select'){
        $select_list = $this->getSelectList();
        $html_options = $this->getSelectHtmlOptions();

        /*
        if($extension_copy->copy_id == ExtensionCopyModel::MODULE_COMMUNICATIONS && $params['name'] == 'communication_source') {
            $options['disabled'] = true;

            $active_source_list = CommunicationsSourceModel::getSourceList();
            $list = array();
            foreach($select_list as $item){
                foreach ($active_source_list as $active_item){
                    if($item['communication_source_slug'] == $active_item['source_name']){
                        $list[] = $item;
                        break;
                    }
                }
            }
            $select_list = $list;
        }
        */

        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $select_list, $html_options));

    }elseif($params['type'] == 'display_block'){
        //blocks
        $options = array('id'=>$params['name'], 'class'=>'select');

        $select = array();
        $blocks = $extension_copy->getSchemaBlocksData();
        foreach($blocks as $block)
            $select[$block['unique_index']] = $block['title'];
          
        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $select, $options));
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
        if(!isset($params['add_zero_value']) || (boolean)$params['add_zero_value'] === true) $logical = array('' => '') + $logical;
        echo Helper::deleteLinefeeds(CHtml::dropDownList('EditViewModel['.$params['name'].']', '', $logical, array('id'=>$params['name'], 'class'=>'select')));
    
    //datetime
    }elseif($params['type'] == 'datetime'){
        $date_element = CHtml::textField('EditViewModel['.$params['name'].']',
                                         '',
                                         array(
                                            'class' => 'form-control ' . ($params['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING ? ' container-date-time date-time' : ' date'),
                                            'data-all_day' => '1',
                                            'value' => '',
                                          )
                                        );    
        $time_class_hide = '';
        $date_max_width = '';

        if(in_array($params['type_view'], [Fields::TYPE_VIEW_BUTTON_DATE_ENDING, Fields::TYPE_VIEW_DT_DATE])){
            $date_max_width = 'max-width: none;';
            $time_class_hide = 'hide';  
        } 
        
        $time_element = CHtml::textField('EditViewModel['.$params['name'].']',
                                         '',
                                         array(
                                            'class' => 'form-control time',
                                            'value' => '',
                                          )
                                        );

       $translateAllDay = Yii::t('base', 'All day');
       $translateSave = Yii::t('base', 'Save');
        if($params['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {

            $str = <<<EOD
         <div class="crm-dropdown" data-type="inline-edit">
            <div class="input-group form-datetime" style="float: left; padding-right: 5px; $date_max_width">
                $date_element
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default container-date-time"><i class="fa fa-calendar"></i></button>
                </span>
            </div>
            <div class="input-group form-datetime date-time $time_class_hide">
                $time_element
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
                </span>
            </div>
             <div class="btn-ending-block dropdown-menu">
                 <div class="content">
                    <div class="element" data-type="calendar-place"></div>
                    <div class="flex-column description">
                        <label class="flex-content-top checkbox-line" for="ckbAllDay">
                            <input type="checkbox" checked id="ckbAllDay"> $translateAllDay
                        </label>

                        <div class="time-block disable flex-content-center"></div>

                        <button type="submit" class="flex-content-bottom btn btn-primary btn-save">$translateSave</button>
                    </div>
                </div>
            </div>
        </div>
EOD;
        } else {

    $str = <<<EOD
        <div class="input-group form-datetime" style="float: left; padding-right: 5px; $date_max_width">
            $date_element
            <span class="input-group-btn">
                <button type="button" class="btn btn-default date-set"><i class="fa fa-calendar"></i></button>
            </span>
        </div>
        <div class="input-group form-datetime $time_class_hide">
            $time_element
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
            </span>
        </div>
EOD;
}
        echo Helper::deleteLinefeeds($str);
    
    //relate
    } elseif($params['type'] == 'relate'){
        $relate_module = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
        
        if(!empty($parent_copy_id) && $parent_copy_id == $relate_module->copy_id){
            $disabled_attr = 'disabled="disabled"';
        }

        $schema = array('params' => $params);

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $extension_copy->getTableName(null, false),
        );

        $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
        $vars = get_defined_vars();
        $relate_model = EditViewRelateModel::getInstance()
                                ->setVars($vars)
                                ->prepareVars();
                                
        
        $module_parent = ($relate_model->isModuleParent() ? 1 : 0);
        $disabled_attr = (empty($disabled_attr) ? $relate_model->getRelateDisabledAttr() : $disabled_attr);
        $option_list = $relate_model->getOptionsDataList();
        $there_is_data = $relate_model->getIsSetNextOptionListData();
        $reloader_status = $relate_model->getReloaderStatus();

        $find_str = Yii::t('base', 'Search');
        $many_select_class = '';
        $many_select_element = '';
        if(isset($params['relate_many_select']) && (boolean)$params['relate_many_select'] == true){
            $many_select_class = 'many_select';
            $many_select_element = '<input type="checkbox" class="checkbox">';
        }
        
        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down" data-entity_key="">
                <button  
                       {$disabled_attr}
                       
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_relate {$many_select_class}"
                       type="button"
                       data-type="drop_down_button"
                       data-reloader="$reloader_status"
                       data-module_parent="$module_parent"
                       data-toggle="dropdown"
                       value=""
                       data-id=""
                       data-relate_copy_id="{$params['relate_module_copy_id']}"
                       >
                </button>
                <!--
                <span class="icon-operation element" data-type="actions">
                    <span class="add hide"><i class="fa fa-plus-circle" aria-hidden="true"></i></span>
                    <span class="remove hide"><i class="fa fa-minus-circle" aria-hidden="true"></i></span>
                </span>
                -->
                <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="{$there_is_data}"
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
                        foreach($option_list as $value){
                            
                            $data = DataValueModel::getInstance()
                                                    ->setFileLink(false)
                                                    ->getRelateValuesToHtml($value, $params);
                            $str2 .= <<<EOD
                            <tr class="sm_extension_data" data-id="{$value[$relate_module->prefix_name . '_id']}">
                                <td>
                                    <span href="javasctript:void(0)" class="name">{$data}</span>
                                    {$many_select_element}
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



        //relate
    } elseif($params['type'] == 'relate_dinamic'){
        $find_str = Yii::t('base', 'Search');
        $disabled = 'disabled="disabled"';
        $process_copy_id = \ExtensionCopyModel::MODULE_PROCESS;

        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                <button
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_relate_dinamic"
                       type="button"
                       data-type="drop_down_button"
                       data-sub_type="dinamic"
                       data-toggle="dropdown"
                       value=""
                       data-id=""
                       data-parent_copy_id="$process_copy_id"
                       data-relate_copy_id=""
                       {$disabled}
                       >
                </button>
                <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="1"
                    data-relate_copy_id=""
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

        $str1 = $str1 . $str2 . <<<EOD
                        </tbody>
                        </table>
                    </div>
                </ul>
            </div>
EOD;

        echo Helper::deleteLinefeeds($str1);




    //relate_this
    } elseif($params['type'] == 'relate_this'){
        
        $relate_module = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
        $select_list = DataModel::getInstance()->setFrom($relate_module->getTableName())
                                                    ->findAll();
        $find_str = Yii::t('base', 'Search');
        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                <button  
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_relate element_relate_this"
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





        //module
    } elseif(in_array($params['type'], [Fields::MFT_MODULE, Fields::MFT_MODULE_PUBLIC])){
        $module_name_list = array();


        if($params['type'] == Fields::MFT_MODULE) {
            if (\EditViewBuilder::disableElementModule($extension_copy->copy_id, null, $this_template) == false) {
                $module_name_list = ExtensionCopyModel::getModulesList($extension_copy->copy_id);
            }
        } elseif($params['type'] == Fields::MFT_MODULE_PUBLIC) {
            $module_name_list = ExtensionCopyModel::getPublicModuleList();
        }

        $find_str = Yii::t('base', 'Search');
        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                <button
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_module disabled"
                       data-type="drop_down_button"
                       type="button"
                       data-toggle="dropdown"
                       data-id=""
                >
                </button>
                <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="0"
                    data-relate_copy_id=""
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
        foreach($module_name_list as $copy_id => $title){
            $str2 .= <<<EOD
                            <tr class="sm_extension_data" data-id="{$copy_id}">
                                <td>
                                    <span href="javasctript:void(0)" class="name">{$title}</span>
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




    //relate_participant
    } elseif($params['type'] == 'relate_participant' && $params['type_view'] == Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION){
        $select_list = ParticipantModel::getParticipantList(ParticipantModel::PARTICIPANT_UG_TYPE_USER);

        $participant_parent_list = array();
        if (!empty($_GET['pci']) && !empty($_GET['pdi'])){
            $participant_parent = ParticipantModel::getParticipants( $_GET['pci'], $_GET['pdi'], ParticipantModel::PARTICIPANT_UG_TYPE_USER);
            $participant_parent_list = array_keys(CHtml::listData($participant_parent, 'ug_id', ''));
        }
                    
        $find_str = Yii::t('base', 'Search');
        $relate_copy_id = \ExtensionCopyModel::MODULE_STAFF;

        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                <button  
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_relate_participant"
                       type="button"
                       data-type="drop_down_button"
                       data-toggle="dropdown"
                       data-participant_id=""
                       data-ug_id=""
                       data-ug_type=""
                       data-relate_copy_id="{$relate_copy_id}"
                       >
                </button>
                <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="0"
                    data-relate_copy_id="{$relate_copy_id}"
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
                            if(!empty($participant_parent_list) && !in_array($value['ug_id'], $participant_parent_list)) continue;
                            if(ParticipantModel::checkAccessParticipantForModule($extension_copy->copy_id, $value['ug_id']) == false) continue;
                                if($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                                    $data = DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->getRelateValuesToHtml($value, array(
                                                                                'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                                                                'relate_module_copy_id'=>5));
                                } elseif($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                                    $data = '';
                                }
                            $str2 .= <<<EOD
                            <tr class="sm_extension_data" data-ug_id="{$value['ug_id']}" data-ug_type="{$value['ug_type']}">
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





    //relate_participant
    } elseif($params['type'] == 'relate_participant'){
        $select_list = ParticipantModel::getParticipantList(ParticipantModel::PARTICIPANT_UG_TYPE_USER);
                    
        $participant_parent_list = array();
        if (!empty($_GET['pci']) && !empty($_GET['pdi'])){
            $participant_parent = ParticipantModel::getParticipants( $_GET['pci'], $_GET['pdi'], ParticipantModel::PARTICIPANT_UG_TYPE_USER);
            $participant_parent_list = array_keys(CHtml::listData($participant_parent, 'ug_id', ''));
        }

        $find_str = Yii::t('base', 'Search');
        $relate_copy_id = \ExtensionCopyModel::MODULE_STAFF;
        $str1 = <<<EOD
             <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                <button  
                       name="EditViewModel[{$params['name']}]"
                       class="btn btn-white dropdown-toggle element element_relate_participant"
                       type="button"
                       data-type="drop_down_button"
                       data-toggle="dropdown"
                       data-participant_id=""
                       data-ug_id=""
                       data-ug_type=""
                       data-relate_copy_id="{$relate_copy_id}"
                       >
                </button>
                <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="0"
                    data-relate_copy_id="{$relate_copy_id}"
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
                                if(!empty($participant_parent_list) && !in_array($value['ug_id'], $participant_parent_list)) continue;
                                if(ParticipantModel::checkAccessParticipantForModule($extension_copy->copy_id, $value['ug_id']) == false) continue;
                                if($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                                    $data = DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->getRelateValuesToHtml($value, array(
                                                                                'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                                                                'relate_module_copy_id'=>5));
                                } elseif($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                                    $data = '';
                                }
                            $str2 .= <<<EOD
                            <tr class="sm_extension_data" data-ug_id="{$value['ug_id']}" data-ug_type="{$value['ug_type']}">
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





    //file, file_image
    } elseif($params['type'] == 'file' ||
             $params['type'] == 'file_image'){
        $element_html = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                               array(
                                'schema' => array('params' => $params),
                                'upload_model' => null,
                                'extension_copy' => $extension_copy,
                                'extension_data' => null,
                                'thumb_size' => 60,
                               ),
                               true);
        echo '<div class="file-box" data-name="EditViewModel['.$params['name'].']">'.Helper::deleteLinefeeds($element_html).'</div>';
    } else {
        
    }

?>
