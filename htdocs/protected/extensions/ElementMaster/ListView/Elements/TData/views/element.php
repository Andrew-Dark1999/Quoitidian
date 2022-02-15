<?php
    //numeric, string
    if($params['type'] == 'numeric'){
        $data = $value_data[$params['name']];
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$this->formatNumeric($data, false).'" ></span>' . $this->formatNumeric($data);
    }
    //string
    elseif($params['type'] == 'string' || $params['type'] == 'display' || $params['type'] == 'relate_string'){
        //while type = password
        $attr_value = $value_data[$params['name']]; 
        if(isset($params['input_attr'])){
            $attr_tmp = json_decode($params['input_attr'], true);
            if(!empty($attr_tmp)){
                if(in_array('password', $attr_tmp)) $attr_value = '';
            } 
        } 
        
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$attr_value.'" ></span><span class="text" data-name="'.$params['name'].'">'.$attr_value.'</span>';
    }
    elseif($params['type'] == 'display_block'){
        //block field type
        $attr_value = $value_data[$params['name']]; 
        if(isset($params['input_attr'])){
            $attr_tmp = json_decode($params['input_attr'], true);
            if(!empty($attr_tmp)){
                if(in_array('password', $attr_tmp)) $attr_value = '';
            } 
        } 

        $block_title = (isset($blocks[$value_data[$params['name']]])) ? $blocks[$value_data[$params['name']]] : '';

        if(!$block_title && !$blocks){
            if(!$extension_copy->isShowAllBlocks()) {
                $block_field_data = $extension_copy->getFieldBlockData();
                $block_title_schema = $extension_copy->getSchemaBlocksData($attr_value);
                if($block_title_schema){
                    foreach($block_title_schema as $block) {
                        if($block['unique_index'] == $attr_value) {
                            $block_title = $block['title'];
                            break;
                        }
                    }
                }
            }
        }
   
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$attr_value.'" >' . $block_title . '</span>';
    }
    //file, file_image
    elseif($params['type'] == 'file' || $params['type'] == 'file_image'){
            if(!empty($value_data[$params['name']])){
                $upload_model = null;
                if($value_data[$params['name']]){
                    $criteria=new CDbCriteria;
                    $criteria->condition='relate_key=:relate_key';
                    $criteria->params=array(':relate_key' => $value_data[$params['name']]);
                    $criteria->limit = 1;
                    $criteria->order = 'file_date_upload desc';
                    $upload_model = UploadsModel::model()->find($criteria);
                }

                if($upload_model == false){
                    return;
                }
                if($this->files_only_url){
            ?>
                    <a href="<?php echo $upload_model->getFileUrl(true); ?>"><?php echo $upload_model->getFileTitle() ?></a>
            <?php
                } else
                if($show_file_name_only == true){
                    echo $upload_model->getFileTitle();
                } else {
            ?>
                <span class="element_data"
                      data-name="<?php echo $params['name']; ?>"
                      data-href="<?php echo '/' . ($params['type'] == 'file_image' ? $upload_model->setFileType('file_image')->getFileThumbsUrl(60) : $upload_model->getFileUrl()) ?>"
                      data-title="<?php echo $upload_model->getFileTitle() ?>"
                      data-id="<?php echo $upload_model->id; ?>"
                      data-dateupload="<?php 
                                    echo date('d', strtotime($upload_model->file_date_upload)) . ' ' .
                                         mb_strtolower(Yii::t('base', date('F', strtotime($upload_model->file_date_upload)),2), 'utf-8') . ' ' .
                                         Yii::t('base', 'in') . ' ' . 
                                         date('H:i', strtotime($upload_model->file_date_upload));
                                         ?>"
                      data-filesize="<?php echo round($upload_model->getFileSize() / 1024); ?>"
                      data-download-link="<?php echo '/' . $upload_model->getFileUrl(); ?>"
                      data-file_type="<?php echo $upload_model->getFileType(); ?>"
                      data-file_type_class="<?php echo $upload_model->getFileTypeClass(); ?>"
                      style="display: none"
                ></span>
                <?php
                if($params['type'] == 'file'){
                    if($file_link === true){
                    ?>
                    <a href="/<?php echo $upload_model->getFileUrl(); ?>" target="_blank" class="modal_dialog name"><span class="file <?php echo "sm_".$upload_model->getFileTypeClass(); ?>"><?php echo $upload_model->getFileType(); ?></span><span><?php echo $upload_model->getFileTitle() ?></span></a>
                    <?php } else { ?>
                    <span class="file <?php echo "sm_".$upload_model->getFileTypeClass(); ?>"><?php echo $upload_model->getFileType(); ?></span><?php echo $upload_model->getFileTitle() ?>
                <?php }} else
                if($params['type'] == 'file_image'){
                    if($file_link === true){
                    ?>
                    <a class="image-preview name" href="<?php echo '/' . $upload_model->getFileUrl(); ?>"
                        title="<?php echo $upload_model->getFileTitle() ?>"
                        data-id="<?php echo $upload_model->id; ?>"
                        data-dateupload="<?php
                                        echo date('d', strtotime($upload_model->file_date_upload)) . ' ' .
                                             mb_strtolower(Yii::t('base', date('F', strtotime($upload_model->file_date_upload)),2), 'utf-8') . ' ' .
                                             Yii::t('base', 'in') . ' ' .
                                             date('H:i', strtotime($upload_model->file_date_upload));
                        ?>"
                        data-filesize="<?php echo round($upload_model->getFileSize() / 1024); ?>"
                        data-download-link="<?php echo '/' . $upload_model->getFileUrl(); ?>">
                        <img class="img-preview <?php echo 'size' . $upload_model->getFileThumbsParams($params['file_thumbs_size'], 'title'); ?>" src="<?php echo '/' . $upload_model->setFileType('file_image')->getFileThumbsUrl($params['file_thumbs_size']); ?>" alt=""
                            title="<?php echo $upload_model->getFileTitle() ?>"
                        /><?php echo $upload_model->getFileTitle() ?>
                    </a>
                    <?php } else { ?>
                        <img
                            class="<?php echo 'size' . $upload_model->getFileThumbsParams($params['file_thumbs_size'], 'title'); ?>"
                            src="<?php echo '/' . $upload_model->setFileType('file_image')->getFileThumbsUrl($params['file_thumbs_size']); ?>"
                            alt=""
                            title="<?php echo $upload_model->getFileTitle() ?>"
                        /> <?php echo $upload_model->getFileTitle() ?>
                <?php
                       }
                    }
                }
            }
    }

    //datetime    
    elseif($params['type'] == 'datetime'){
        $date_time_format = $this->getDateTimeFormat();
        $date_time_color = $this->getDateTimeColor();
        $date_time_attriburtes = $this->getDateTimeAttributes();

        echo '<span class="element_data" '. implode(' ', $date_time_attriburtes) .' style="display: none"></span>';
        echo '<span '.($date_time_color ? 'style="color : ' .$date_time_color . '"' : '').'>' . $date_time_format . '</span>';
    }
    //logical
    elseif($params['type'] == 'logical'){
        $logical = Fields::getInstance()->getLogicalData();
        if(isset($logical[$value_data[$params['name']]]))
            echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$value_data[$params['name']].'" style="display: none"></span>' . $logical[$value_data[$params['name']]];
    }    
    //select
    elseif($params['type'] == 'select' && $params['type_view'] == Fields::TYPE_VIEW_DEFAULT){
        //if(!empty($value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'])){
            echo '<span class="element_data" data-name="' . $params['name'] . '" data-value="' . $value_data[$params['name']] . '" style="display: none"></span>' . $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
        //}
    }    

    //select (button)
    elseif($params['type'] == 'select' && $params['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS){
        $color = '';

        if(!isset($value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'])){
            $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'] = '';
            $value_data[$params['name']] = '';
        }

        if ($element_dye) {
            if(!empty($value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_color'])) $color = 'status-' . $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_color'];

            if(\Yii::app()->controller->layout == '//layouts/print')
                echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$value_data[$params['name']].'" style="display: none"></span>' .  $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
            else
                echo '<span class="color-status ' . $color . '"><span class="element_data" data-name="' . $params['name'] . '" data-value="' . $value_data[$params['name']] . '" style="display: none"></span>' . $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'] . '</span>';
        } else {
            echo '<span class="element_data" data-name="' . $params['name'] . '" data-value="' . $value_data[$params['name']] . '" style="display: none"></span>' . $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
        }
    }

    //access
    elseif($params['type'] == 'access'){
        $data = array('id'=> $value_data[$params['name']], 'type' => $value_data[$params['name'] . '_type']);
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$data['id'].'" data-type="'.$data['type'].'" style="display: none"></span>' .AccessModel::getInstance()->getAccessTitle($data);
    }    

    //permission
    /*
    elseif($params['type'] == 'permission'){
        $data = $value_data[$params['name']];
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$data.'" ></span>' . PermissionModel::getInstance()->getPermissionTitle($data);
    } 
    */   


    //relate
    /*
    elseif($params['type'] == 'relate1'){
        $html = array();
        $relate_id_list = array();
        $id = null;


        $id = $value_data[$extension_copy->prefix_name.'_id'];
        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);

        $relate_module_table = ModuleTablesModel::model()->find(array(
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
            'params' => array(
                ':copy_id' => $extension_copy->copy_id,
                ':relate_copy_id' => $params['relate_module_copy_id'])));

        $relate_data_id_list = DataModel::getInstance()
            ->setFrom('{{' . $relate_module_table->table_name . '}}')
            ->setWhere($extension_copy->prefix_name . '_id = :id',
                array(':id' => $id))
            ->findAll();


        // get Data
        if(!empty($params['relate_module_copy_id'])){
            if(!empty($relate_data_id_list)){
                if((boolean)$params['relate_many_select'] == true && count($relate_data_id_list) > 1){

                    $html[] = count($relate_data_id_list);
                    foreach($relate_data_id_list as $relate_data_id)
                        $relate_id_list[] = $relate_data_id[$relate_extension_copy->prefix_name . '_id'];
                } else{
                    foreach($relate_data_id_list as $relate_data_id){
                        $relate_data = DataModel::getInstance()
                            ->setExtensionCopy($relate_extension_copy)
                            ->setFromModuleTables();
                        $relate_data
                            ->setFromFieldTypes();
                        //responsible
                        if($relate_extension_copy->isResponsible())
                            $relate_data->setFromResponsible();
                        //participant
                        if($relate_extension_copy->isParticipant())
                            $relate_data->setFromParticipant();
                        $relate_data
                            ->setCollectingSelect()
                            ->andWhere(array('AND', $relate_extension_copy->getTableName() . '.' . $relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id'),
                                array(':' . $relate_extension_copy->prefix_name . '_id' => $relate_data_id[$relate_extension_copy->prefix_name . '_id']));
                        //this_template
                        //if($relate_extension_copy->getModule()->isTemplate()){
                        //    $relate_data->andWhere(array('AND', $relate_extension_copy->getTableName() . '.this_template = "0" OR ' . $relate_extension_copy->getTableName() . '.this_template is null'));
                        //}
                        $relate_data->setGroup($relate_extension_copy->prefix_name . '_id');
                        $relate_data = $relate_data->findAll();

                        if(!empty($relate_data)){
                            foreach($relate_data as $relate_value){
                                $html[] = DataValueModel::getInstance()->setFileLink(false)->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
                            }
                        }
                        $relate_id_list[] = $relate_data_id[$relate_extension_copy->prefix_name . '_id'];
                    }
                }
            }
        }

        if($this->show_sdm_link){
            echo '<a
            href="javascript:void(0)"
            class="edit_view_show element_data"
            data-controller="sdm"
            data-name="'.$params['name'].'"
            data-id="'. implode(',', $relate_id_list) . '"
            data-relate_copy_id="'.$params['relate_module_copy_id'].'"
            ><span>' . implode(' ', $html) . '</span></a>';
        } else {
            echo '<span>' . implode(' ', $html) . '</span>';
        }
    }
    */




elseif($params['type'] == 'relate'){
    $vars = get_defined_vars();
    unset($vars['params']);
    unset($vars['value_data']);
    $vars['schema']['params'] = $params;
    $vars['extension_data'] = $value_data;

    $ddl_model = \DropDownListModel::getInstance()
        ->setActiveDataType(\DropDownListModel::DATA_TYPE_8)
        ->setVars($vars)
        ->prepareHtml();

    $result_html = $ddl_model->getResultHtml();

    if($result_html['status'] == false){
        return;
    }

?>
    <?php echo $result_html['html']; ?>
<?php }






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

        if($this->show_sdm_link){
            echo '<span
                       class="element_data"
                       data-name="'.$params['name'].'"
                       data-id="'.$id.'"
                       data-relate_copy_id="'.\ExtensionCopyModel::MODULE_STAFF.'"
                       ><span><a href="javascript:void(0)" class="edit_view_show" data-controller="sdm" >' . implode(' ', $html) . '</a></span></span>';
        } else {
            echo '<span
                       class="element_data"
                       data-name="'.$params['name'].'"
                       data-id="'.$id.'"
                       data-relate_copy_id="'.\ExtensionCopyModel::MODULE_STAFF.'"
                       ><span>' . implode(' ', $html) . '</span></span>';
        }
    }


    //module
    elseif(in_array($params['type'], [Fields::MFT_MODULE, Fields::MFT_MODULE_PUBLIC])){
        //ELEMENT_MODULE_RELATE
        $relate_copy_id = $value_data[$params['name']];
        $relate_title = '';
        if(!empty($relate_copy_id)){
            $relate_title = \ExtensionCopyModel::model()->findByPk($relate_copy_id)->title;
        }
        echo '<span
                class="element_data"
                data-name="'.$params['name'].'"
                data-id="'. $relate_copy_id . '"
                ><span>' . $relate_title . '</span></span>';
    }



    //responsible
    elseif($params['type'] == 'relate_participant' && ($params['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE || $params['type_view'] ==  Fields::TYPE_VIEW_BLOCK_PARTICIPANT)) {
        echo '<span
            class="element_data"
            data-name="'.$params['name'].'"
            data-participant_id="'.$value_data['participant_participant_id'].'"
            data-ug_id="' . $value_data['participant_ug_id'] . '"
            data-ug_type="' .$value_data['participant_ug_type'] . '"
            data-relate_copy_id="' . \ExtensionCopyModel::MODULE_STAFF . '"
            data-disabled="' . $this->getDisabledStatus() . '"
            ><span class="text">' . $this->getValue() . '</span></span>';
    }    

    //вычисляемое поле
    elseif($params['type'] == \Fields::MFT_CALCULATED){
        
        /*
        $calculated_value = \CalculatedFields::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setValuesData($value_data)
                ->setFieldName($params['name'])
                ->prepareFormula()
                ->getValue();
        */
        $calculated_value = '';
        if(isset($value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_value'])){
            $calculated_value = $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_value'];
        }

        echo '<span>' . Helper::TruncateEndZero($calculated_value) . '</span>';
    }


    //datetime_activity
    elseif($params['type'] == 'datetime_activity' && isset($value_data[$params['name']])){
        $data = $value_data[$params['name']];
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.Helper::formatDateTimeShort($data).'" ></span>' . Helper::formatDateTime($data);
    }
