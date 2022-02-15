<div class="<?php echo $this->getBlockClass(); ?>">
    <div class="file-block" data-type="<?php echo $params['file_type'] ?>" >
        <?php
            echo CHtml::hiddenField('EditViewModel[file]',
                                    $file_params['id'],
                                    array(
                                        'id' => '',
                                        'class'=>'upload_file'
                                        )
                                    );
                                    
        ?>
        <?php
            $parent_upload_id = false;
            if($show_generate_url) {
                //проверяем физическое наличие шаблона-родителя
                if($file_params['id']!='') {
                    $upload = UploadsParentsModel::model()->findByAttributes(array('upload_id'=>$file_params['id']));
                    if($upload !== null) {
                        if($upload->parent_upload_id) {
                            $parent_data = UploadsModel::model()->findByPK($upload->parent_upload_id);
                            if(isset($parent_data->file_path)) {
                                if(file_exists($parent_data->getFullFileName())) {
                                    $parent_upload_id = $upload->parent_upload_id;
                                    
                                    //если конечный файл и шаблон идентичны, не показываем блок с информацией
                                    $url = \ParamsModel::model()->getValueFromModel('site_url') . '/';
                                    if(md5_file($url . $file_params['file_url']) == md5_file($url . $parent_data->getFileUrl()))
                                       $hide_elements_block_file = true;
                                }
                            }
                        }
                    }
                }
            }

            if(!$parent_upload_id)
                $hide_elements_block_file = false;
            if($upload_link_show == true){
                if(!empty($extension_copy) && Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $access_check_params['access_id'], $access_check_params['access_id_type'])) { ?>
                    <a href="javascript:void(0)" class="upload_link lessening <?php if($file_params['status']) echo 'hide' ?>"><i class="fa fa-arrow-circle-up"></i><?php echo Yii::t('base', 'Upload file'); ?></a>
                <?php } ?>
        <?php } ?>
        <div class="upload-result<?php if($hide_elements_block_file) echo ' generate_only'; ?>" <?php if($file_params['status']) echo 'style="display: block"' ?>>
            <div class="display-flex flex-<?php echo $this->getFlexClass(); ?>">
                <div class="thumb-block display-flex flex-collumn" <?php if($file_params['status'] &&
                    ($params['file_type'] != 'file_image' ||
                    ($params['file_type'] == 'file_image' && $file_params['file_type_class'] != 'file_image')) &&
                    ($params['file_type'] == 'attachments' && $file_params['file_type_class'] != 'file_image')&&
                    ($params['file_type'] == 'activity' && $file_params['file_type_class'] != 'file_image'))  echo 'style="display: none"' ?>>

                    <a class="image-preview name" href="<?php if($file_params['status'] && ($params['file_type'] == 'file_image' || ($params['file_type'] == 'attachments' && $file_params['file_type_class'] == 'file_image') || ($params['file_type'] == 'activity' && $file_params['file_type_class'] == 'file_image'))) echo '/' . $file_params['file_url']; ?>"
                       title="<?php if($file_params['status']) echo $file_params['file_title']; ?>"
                       data-id="<?php if($file_params['status']) echo $file_params['id']; ?>"
                       data-dateupload="<?php
                       if($file_params['status'])
                           echo date('d', strtotime($file_params['file_date_upload'])) . ' ' .
                               mb_strtolower(Yii::t('base', date('F', strtotime($file_params['file_date_upload'])),2), 'utf-8') . ' ' .
                               Yii::t('base', 'in') . ' ' .
                               date('H:i', strtotime($file_params['file_date_upload']));
                       ?>"
                       data-filesize="<?php echo ($file_params['file_size'] ? round($file_params['file_size'] / 1024) : ''); ?>"
                       data-download-link="/<?php echo $file_params['file_url']; ?>"
                       data-show-link-download="<?php echo (!empty($extension_copy) && Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $access_check_params['access_id'], $access_check_params['access_id_type']) ? "1" : "0") ?>"
                       data-show-link-remove="<?php echo (!empty($extension_copy) && Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $access_check_params['access_id'], $access_check_params['access_id_type']) ? "1" : "0") ?>"

                        <?php
                        if($parent_upload_id) {
                            ?>
                            data-parent_id="<?php echo $parent_upload_id; ?>"
                            <?php
                        }
                        ?>

                    >

                        <img src="<?php
                                if(
                                    $file_params['status'] &&
                                    ($params['file_type'] == 'file_image' || ($params['file_type'] == 'attachments' && $file_params['file_type_class'] == 'file_image') || ($params['file_type'] == 'activity' && $file_params['file_type_class'] == 'file_image')) &&
                                    $file_params['file_thumb_url']
                                ){
                                    echo '/' . $file_params['file_thumb_url'];
                                }
                            ?>"
                             class="thumb"
                             alt=""
                             title="<?php if($file_params['status']) echo $file_params['file_title']; ?>"
                             style="<?php echo ($thumb_size ? 'max-height: ' . $thumb_size . 'px' : '') ?>"
                        />

                    </a>
                </div>

                <?php if($file_params['status'] && ($params['file_type'] == 'file' || ($params['file_type'] == 'attachments' && $file_params['file_type_class'] != 'file_image') || ($params['file_type'] == 'activity' && $file_params['file_type_class'] != 'file_image') || ($params['file_type'] == 'file_image' && $file_params['file_type_class'] != 'file_image'))){ ?>
                    <span class="file_thumb <?php echo $file_params['file_type_class']; ?> <?php if($file_params['status']) echo 'generate_file-block' . $file_params['id']; ?>"><?php if($file_params['status']) echo $file_params['file_type']; ?></span>
                <?php } else {  ?>
                    <span class="file_thumb hide"></span>
                <?php } ?>

                <div class="filedata">
                    <div class="filename <?php if($file_params['status']) echo 'generate_file-block' . $file_params['id']; ?>"><?php if($file_params['status']) echo $file_params['file_title']; ?></div>
                    <div class="second-row <?php if($parent_upload_id && !$hide_elements_block_file) echo 'linkMore2';?>">
                    <span class="filedate <?php if($file_params['status']) echo 'generate_file-block' . $file_params['id']; ?>">
                        <?php
                        if($file_params['status'])
                            echo date('d', strtotime($file_params['file_date_upload'])) . ' ' .
                                mb_strtolower(Yii::t('base', date('F', strtotime($file_params['file_date_upload'])),2), 'utf-8') . ' ' .
                                Yii::t('base', 'in') . ' ' .
                                date('H:i', strtotime($file_params['file_date_upload']));
                        ?>
                    </span>

                        <?php
                        if(in_array('download_file', $buttons) && (empty($extension_copy) || Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $access_check_params['access_id'], $access_check_params['access_id_type']))) {
                            if($file_params['file_source'] == UploadsModel::SOURCE_MODULE){ ?>
                                <a href="<?php if($file_params['status']) echo '/'.$file_params['file_url']; else echo 'javascript:void(0)' ?>" class="file-download <?php if($file_params['status']) echo 'generate_file-download' . $file_params['id'] . ' generate_file-block' . $file_params['id']; ?>" download><?php echo Yii::t('base', 'Download'); ?></a>
                            <?php }} ?>

                        <?php if($file_params['file_source'] == UploadsModel::SOURCE_GOOGLE_DOC){ ?>
                            <a href="<?php echo $file_params['file_url']; ?>" target="_blank"><?php echo Yii::t('base', 'Open'); ?></a>
                        <?php } ?>

                        <?php
                        if(in_array('delete_file', $buttons) && (empty($extension_copy) || Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $access_check_params['access_id'], $access_check_params['access_id_type']))) {
                            ?>
                            <a href="javascript:void(0)" class="file-remove <?php if($file_params['status']) echo 'generate_file-block' . $file_params['id']; ?>"><?php echo Yii::t('base', 'Delete'); ?></a>
                        <?php }

                        if($parent_upload_id) {
                            ?>
                            <a href="javascript:void(0)" class="list_view_btn-generate <?php if($file_params['status']) echo 'generate_link_file-block' . $file_params['id']; ?>"><?php echo Yii::t('base', 'Generate'); ?></a>

                            <?php
                            $show_edit_link = false;
                            if($file_params['status']) {
                                $data = \DocumentsModel::getDataByUploadID($file_params['id']);
                                $show_edit_link = ($data) ? true : false;
                            }
                            ?>
                            <a style="<?php if(!$show_edit_link) echo 'display:none;' ?>" target="_blank" href="<?php echo Yii::app()->createUrl('/module/listView/showDocument/' . $extension_copy->copy_id . '?data_id=' . $file_params['id']); ?>" class="list_view_btn-generate_edit <?php if($file_params['status']) echo 'generate_edit_link_file-block' . $file_params['id']; ?>"><?php echo Yii::t('base', 'Edit'); ?></a>
                            <?php

                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            if(!empty($extension_data)) 
            echo '<div class="' . CHtml::$errorMessageCss . '">' . Helper::getFileError($extension_data->getErrors(),
                                                                                                                   $params['field_name'],
                                                                                                                   $file_params['file_name']) . '</div>'
                                                                                            
        ?>
    </div>
</div>

