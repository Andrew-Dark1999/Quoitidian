<!-- FieldType -->
<div class="select-item column element"  <?php if($schema['params']['type'] == 'relate_string') { ?> data-relate_string = '<?php echo $schema['params']['relate_module_copy_id']; ?>' <?php } ?> data-type="field_type">
    <select id="" name="ExtensionCopyModel[extension_id]" class="select element_field_type" data-type="type" >
        <?php foreach($fields_type as $key => $value): ?>
    		<option value="<?php echo $key; ?>" <?php if($key == $schema['params']['type']) echo 'selected="selected"' ?> ><?php echo Yii::t('constructor', $value['title']); ?></option>
        <?php endforeach; ?>
	</select>
	<div class="settings crm-dropdown dropdown">
        <a href="javascript:void(0)" class="dropdown-toggle field-param add_element_field_type_params" data-toggle="dropdown"
            <?php if(array_key_exists('c_load_params_btn_display', $schema['params']) && $schema['params']['c_load_params_btn_display'] == false) echo 'style="display:none"'; ?>
            ><i class="fa fa-cog"></i></a>
        
        
        <?php if(!empty($field_type_params)) echo $field_type_params; ?>
        
	</div>
</div>
<!-- FieldType END -->



