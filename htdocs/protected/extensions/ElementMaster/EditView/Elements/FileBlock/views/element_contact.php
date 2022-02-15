<div class="file-block" data-type="file_image" >
    <?php
        echo CHtml::hiddenField('',
                                $file_params['id'],
                                array(
                                    'id' => '',
                                    'class'=>'upload_file',
                                    )
                                );
    ?>

    <?php if(in_array('download_file', $buttons)){ ?>
        <div class="upload_link_contact_image element" data-type="upload_image" data-name="<?php echo $upload_element_name; ?>">
            <i class="fa fa-cloud-upload"></i>
        </div>
    <?php } ?>
    <?php if(in_array('delete_file', $buttons) && (empty($extension_copy) || Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, $access_check_params['access_id'], $access_check_params['access_id_type']))) { ?>
        <i class="fa fa-times remove_contact_image element" data-type="remove_image" data-name="<?php echo $remove_element_name; ?>" ></i>
    <?php } ?>
    <div class="upload-result" style="display: block;">
        <div class="thumb-block-contact-image" <?php if(!$file_params['status']) echo 'style="display: none"' ?>>
            <a class="name"
                href="javascript:"
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
            >


                <img src="<?php if($file_params['status'])
                                        if($file_params['file_thumb_url'])
                                            echo '/' . $file_params['file_thumb_url'];
                                         ?>"
                         class="thumb"
                         alt=""
                         title="<?php if($file_params['status']) echo $file_params['file_title']; ?>" />
            </a>
        </div>
    </div>
        <img src="/static/images/lock_thumb.jpg" alt="" class="thumb_zero" <?php if($file_params['status']) echo 'style="display: none"' ?> />



    <?php
        echo '<div class="' . CHtml::$errorMessageCss . '">' . Helper::getFileError($extension_data->getErrors(),
                                                                                    'ehc_image1',
                                                                                    $file_params['file_name']) . '</div>';
    ?>
</div>


