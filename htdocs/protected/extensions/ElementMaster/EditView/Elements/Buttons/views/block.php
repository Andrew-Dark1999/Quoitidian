<div class="buttons-block element" data-type="block_button">
    <?php echo $content; ?>
    <span class="element" data-type="button">
        <?php
            if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id, Access::ACCESS_TYPE_MODULE)){
        ?>
        <button type="submit" class="btn btn-primary <?php echo $this->button_attr['save']['class']?>"><?php echo \Yii::t('base', 'Save'); ?></button>
        <?php } ?>
    </span>
</div>
