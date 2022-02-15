<!-- block Attachmens -->
<div class="panel-body">
<div
    class="files-block row element"
    data-type="attachments"
    data-name="<?php echo 'EditViewModel['.$schema['params']['name'].']'?>"
>
    <?php if(!empty($content)) echo $content; ?>    
    <h3 class="file_is_empty"><?php echo Yii::t('base', 'Drag files here'); ?></h3>
    <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))) { ?>
        <input class="drop_zone" type="file" name="file" class="upload_file" />
    <?php } ?>
</div>
</div>
<!-- block Attachmens AND-->
