<div class="contacts element" data-type="block_field_type_contact">
    <div class="element" data-type="field_type_hidden">
        <input type="hidden" class="element_params" data-type="name" value="ehc_image1">
        <input type="hidden" class="element_params" data-type="title" value="">
        <input type="hidden" class="element_params" data-type="type" value="string">
        <input type="hidden" class="element_params" data-type="type_view" value="<?php echo Fields::TYPE_VIEW_AVATAR; ?>">
    </div>
    <?php if(!empty($content)) echo $content; ?>

	<div class="operations">
		<a href="javascript:void(0)" class="add-field-action add_element_field_hidden"><?php echo Yii::t('base', 'Add field'); ?></a>
        <?php if(array_key_exists('destroy', $schema['params']) && $schema['params']['destroy'] == true){ ?>
		<a href="javascript:void(0)" class="remove-block-action remove_block_panel_contact"><?php echo Yii::t('base', 'Delete block'); ?></a>
        <?php } ?>
	</div>
    <input type="hidden" class="element_params_contact" data-type="destroy" value="<?php if(array_key_exists('destroy', $schema['params'])) echo (integer)$schema['params']['destroy']; else echo '1' ?>">
</div>