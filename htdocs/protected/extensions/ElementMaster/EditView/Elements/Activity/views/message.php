<?php //TYPE_COMMENT_GENERAL ?>
<?php if($activity_messages_model->type_comment == ActivityMessagesModel::TYPE_COMMENT_GENERAL){ ?>
    <div
            class="user_comment element"
            data-type="message"
            data-id="<?php echo $activity_messages_model->activity_messages_id; ?>"
            data-type_comment="<?php echo $activity_messages_model->type_comment ?>"
            data-date_edit="<?php echo $activity_messages_model->date_edit; ?>"
            data-status="<?php echo $activity_messages_model->status; ?>"
    >
        <div class="user_comment_pic">
            <?php echo $activity_messages_model->getAuthorCommentAvatar(); ?>
        </div>
        <div class="user_comment_right">
            <div class="user_comment_box">
                <div class="user_comment_name"><?php echo $activity_messages_model->getAuthorCommentName(); ?></div>
                <div class="user_comment_text"><?php echo $activity_messages_model->getCommentText(); ?></div>
            </div>
            <div class="user_comment_info">
                <span class="user_comment_date"><?php echo $activity_messages_model->getDateCreateFormated(); ?></span>
                <?php if(Yii::app()->user->id == $activity_messages_model->user_create){ ?>
                    <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $access_check_params['access_id'], $access_check_params['access_id_type'])){ ?>
                        <a class="user_comment_redact"><?php echo Yii::t('base', 'Edit'); ?></a>
                        <a class="user_comment_delete"><?php echo Yii::t('base', 'Delete'); ?></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <?php echo $block_attachments; ?>
    </div>
<?php } ?>



<?php //TYPE_COMMENT_EMAIL ?>
<?php if(($activity_messages_model->type_comment == ActivityMessagesModel::TYPE_COMMENT_EMAIL)){ ?>
    <div
            class="user_comment element"
            data-type="message"
            data-id="<?php echo $activity_messages_model->activity_messages_id; ?>"
            data-type_comment="<?php echo $activity_messages_model->type_comment ?>"
            data-date_edit="<?php echo $activity_messages_model->date_edit; ?>"
            data-status="<?php echo $activity_messages_model->status; ?>"
    >
        <div class="user_comment_pic">
            <?php echo $activity_messages_model->getAuthorCommentAvatar(); ?>
        </div>
        <div class="user_comment_right">
            <div class="user_comment_box">

                <div class="user_comment_name"><?php echo $activity_messages_model->getAuthorCommentName(); ?></div>
                <div class="user_comment_text"><?php echo $activity_messages_model->getCommentText(); ?></div>
            </div>
            <div class="user_comment_info">
                <?php if($activity_messages_model->showEmailDeliveryStatus()){ ?>
                    <span>
                        <strong><?php echo Yii::t('base', 'Status'); ?>:</strong>
                        <?php echo $activity_messages_model->getEmailDeliveryStatusTitle(); ?>,
                    </span>
                <?php } ?>
                <span class="user_comment_date"><?php echo $activity_messages_model->getDateCreateFormated(); ?></span>
                <?php if($activity_messages_model->showEmailDeliveryStatus() && $activity_messages_model->isDeleteAvailable()===true){ ?>
                    <span>
                        <a class="user_comment_delete">
                            <?php echo Yii::t('base', 'Delete'); ?>
                        </a>
                    </span>
                <?php } ?>
            </div>
        </div>
        <?php echo $block_attachments; ?>
    </div>
<?php } ?>

