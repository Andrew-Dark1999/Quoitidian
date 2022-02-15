<?php
    //numeric, string
    if($params['type'] == 'numeric'){
        $data = $value_data[$params['name']];
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.Helper::TruncateEndZero($data).'" ></span>' . Helper::TruncateEndZero($data);
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
        
        if(array_key_exists('is_primary', $params) && $params['is_primary'] == true && $relate_add_avatar){
            echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                   array(
                    'extension_copy' => $extension_copy,
                    'data_array' => $value_data,
                    'thumb_size' => 32,
                   ),
                   true); 
            echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$attr_value.'" ></span>' . $attr_value;
        } else {
            echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$attr_value.'" ></span>' . $attr_value;
        }
    }
    //file, file_image
    elseif($params['type'] == 'file' || $params['type'] == 'file_image'){
        if(!empty($value_data[$params['name']])){
            if($value_data[$params['name']]){
            $criteria=new CDbCriteria;
            $criteria->condition='relate_key=:relate_key';
            $criteria->params=array(':relate_key' => $value_data[$params['name']]);
            $criteria->limit = 1;
            $criteria->order = 'file_date_upload desc';
            $upload_model = UploadsModel::model()->find($criteria);
        }
        if(empty($upload_model)) return;
        ?>
            <span class="element_data"
                  data-name="<?php echo $params['name']; ?>"
                  data-href="<?php echo '/' . $upload_model->getFileUrl(); ?>"
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
            <a href="/<?php echo $upload_model->getFileUrl(); ?>" target="_blank" class="modal_dialog name"><span class="file <?php echo "sm_".$upload_model->getFileTypeClass(); ?>"><?php echo $upload_model->getFileType(); ?></span><?php echo $upload_model->getFileTitle() ?></a>
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
                <img class="img-preview" src="<?php echo '/' . $upload_model->setFileType('file_image')->getFileThumbsUrl($params['file_thumbs_size']); ?>" alt=""
                    title="<?php echo $upload_model->getFileTitle() ?>"
                /><?php echo $upload_model->getFileTitle() ?>
            </a>
            <?php } else { ?>
                <img src="<?php echo '/' . $upload_model->setFileType('file_image')->getFileThumbsUrl($params['file_thumbs_size']); ?>" alt=""
                    title="<?php echo $upload_model->getFileTitle() ?>"
                /> <?php echo $upload_model->getFileTitle() ?>
        <?php
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
    elseif($params['type'] == 'select'){
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$value_data[$params['name']].'" style="display: none"></span>' .  Yii::t('PermissionModule.base', $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title']);
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
    elseif($params['type'] == 'relate'){
        $relate_extension_copy = ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
        $relate_module_table = ModuleTablesModel::model()->find(array(
                                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                        'params' => array(
                                                                        ':copy_id'=>$extension_copy->copy_id,
                                                                        ':relate_copy_id'=>$params['relate_module_copy_id'])));

        $relate_data_id = DataModel::getInstance()
                                                    ->setFrom('{{' . $relate_module_table->table_name . '}}')
                                                    ->setWhere($extension_copy->prefix_name . '_id = :id',
                                                               array(':id' => $value_data[$extension_copy->prefix_name.'_id']))
                                                    ->findRow();
        if(!empty($relate_data_id))
        $relate_data = DataModel::getInstance()
                            ->setFrom($relate_extension_copy->getTableName())
                            ->setWhere($relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id',
                                       array(':'.$relate_extension_copy->prefix_name . '_id' => $relate_data_id[$relate_extension_copy->prefix_name . '_id']))
                            ->findAll();
        $html = array();            
        if(!empty($relate_data))
        foreach($relate_data as $relate_value){
            $html[] = DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
        }

        echo '<span 
            class="element_data"
            data-name="'.$params['name'].'"
            data-id="'.$relate_data_id[$relate_extension_copy->prefix_name . '_id'].'"
            data-relate_copy_id="'.$params['relate_module_copy_id'].'"
            ><span>' . implode(' ', $html) . '</span></span>';
    }
?>
