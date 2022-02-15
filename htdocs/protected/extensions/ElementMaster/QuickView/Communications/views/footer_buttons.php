<li class="prog-row total clearfix">
    <div>
        <p>
            <a class="ajax_content_reload" data-action_key="<?php
                echo (new \ContentReloadModel(6, ['_use_auto_pci_pdi'=> false]))
                                    ->addVars(['module'=>['copy_id' => ExtensionCopyModel::MODULE_COMMUNICATIONS, 'params'=>['this_template'=>0]]])
                                    ->prepare()
                                    ->getKey(); ?>" href="javascript:void(0)"
            >
                <?php echo Yii::t('communications', 'All chats'); ?>
            </a>
            <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, ExtensionCopyModel::MODULE_COMMUNICATIONS, Access::ACCESS_TYPE_MODULE)) {?>
                <a href="javascript:void(0);" class="edit_view_dnt-add"><?php echo Yii::t('communications', 'Create of chat'); ?></a>
            <?php } ?>
        </p>
    </div>
</li>
