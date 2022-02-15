<div class="panel-body element" data-type="block_activity" style="overflow: hidden; display: block;">
    <div class="task_comments">

        <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $content['extension_copy']->copy_id, Access::ACCESS_TYPE_MODULE)){ ?>
<!--            --><?php //if(CommunicationsServiceParamsModel::isSetUserParams() !== false){ ?>
                <div class="element" data-type="editors">
                    <div class="task_message element"
                         data-unique_index="<?php echo md5(date('YmdHis')); ?>"
                         data-sub_type="btn-group-editors" data-type="edit" data-type_comment="<?php echo $this->type_comment_list[0]?>">
                        <?php echo $editors; ?>
                        <?php echo $content['block_attachments']; ?>
                        <?php echo $editor_buttons ?>
                    </div>
                </div>
<!--            --><?php //} ?>
        <?php } ?>

        <!-- message_block -->
        <?php echo $content['block_message']; ?>
    </div>
</div>
