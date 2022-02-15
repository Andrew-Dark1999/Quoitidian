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
        
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$attr_value.'" ></span>' . $attr_value;
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
        echo '<span class="element_data" data-name="'.$params['name'].'" data-value="'.$value_data[$params['name']].'" style="display: none"></span>' .  $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
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
                                                    ->setFrom('{{'.$relate_module_table->table_name.'}}')
                                                    ->setWhere($extension_copy->prefix_name . '_id = :id',
                                                               array(':id' => $value_data[$extension_copy->prefix_name.'_id']))
                                                    ->findRow();
        if(!empty($relate_data_id))
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
                            ->setGroup()
                            ->setCollectingSelect()
                            ->setWhere($relate_extension_copy->getTableName() . '.' . $relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id',
                                       array(':'.$relate_extension_copy->prefix_name . '_id' => $relate_data_id[$relate_extension_copy->prefix_name . '_id']))
                            ->findAll();

        $html = array();
        if(!empty($relate_data))
        foreach($relate_data as $relate_value){
            $html[] = DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
        }

        echo '<a
            href="javascript:void(0)"
            class="edit_view_show element_data"
            data-controller="sdm"
            data-name="'.$params['name'].'"
            data-id="'.$relate_data_id[$relate_extension_copy->prefix_name . '_id'].'"
            data-relate_copy_id="'.$params['relate_module_copy_id'].'"
            ><span>' . implode(' ', $html) . '</span></a>';
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
           data-relate_copy_id="'.$params['relate_module_copy_id'].'"
           ><span><a href="javascript:void(0)" class="edit_view_show" data-controller="sdm" >' . implode(' ', $html) . '</a></span></span>';

    }    


    //responsible
    elseif($params['type'] == 'relate_participant'){
        
        if(!empty($value_data['ug_id'])){
            if((integer)$value_data['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                $relate_select_list = DataModel::getInstance()
                                        ->setFrom('{{users}}')
                                        ->setWhere('users_id = ' . $value_data['ug_id'])
                                        ->findAll();
                if(!empty($relate_select_list))
                $html = DataValueModel::getInstance()
                                        ->setFileLink(false)
                                        ->getRelateValuesToHtml($relate_select_list[0], array(
                                                                        'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                                                        'relate_module_copy_id' => 5), $relate_add_avatar); //берем данные из пользователей        
            }elseif((integer)$value_data['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
            
            }
        }
        
        echo '<span 
            class="element_data"
            data-name="'.$params['name'].'"
            data-participant_id="'.$value_data['participant_id'].'"
            data-ug_id="' . $value_data['ug_id'] .  '"
            data-ug_type="' .$value_data['ug_type'] . '"
            data-relate_copy_id="5"
            ><span>' . $html . '</span></span>';
    }    
 
 





