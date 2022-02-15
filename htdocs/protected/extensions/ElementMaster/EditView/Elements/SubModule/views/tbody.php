<?php
    foreach($extension_copy_data as $value_data){ 
        //replace title for block type
        if(!$extension_copy_relate->isShowAllBlocks()) {
            $block_field_data = $extension_copy_relate->getFieldBlockData();
            $blocks = $extension_copy_relate->getSchemaBlocksData();
        }?>
        <tr class="sm_extension_data new" data-id="<?php echo $value_data[$extension_copy_relate->prefix_name . '_id']; ?>">
            <td><input type="checkbox" class="checkbox" ></td>
            <?php echo ListViewBulder::getInstance($extension_copy_relate)->setTitleAddAvatar(true)->buildListViewRow($field_params, $value_data, array(), $primary_link); ?>
            <td>
                <?php if($extension_copy_relate->getModule(false)->edit_view_enable){ ?>
                <i class="fa fa-pencil submodule_edit_view_dnt-edit" title="<?php echo yii::t('base', 'Edit'); ?>"></i>
                <?php } ?>
            </td>
        </tr>
<?php } ?>
