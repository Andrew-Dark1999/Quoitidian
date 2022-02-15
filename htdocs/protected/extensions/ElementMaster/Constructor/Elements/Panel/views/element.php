<!-- panel -->
<li <?php if(isset($schema['elements'][1]['elements'][0]['params']['type']) && $schema['elements'][1]['elements'][0]['params']['type']=='display_block') echo 'todo-element="true"'; ?> class="clearfix form-group inputs-group element_panel element" data-type="panel" <?php if(array_key_exists('c_list_view_display', $schema['params']) && $schema['params']['c_list_view_display'] == false) echo 'big-w="true"'; ?> >
    <span class="drag-marker">
    <i></i>
    </span>

    <?php if(!empty($content)) echo $content; ?>


    <span <?php if(array_key_exists('c_count_select_fields_display', $schema['params']) && $schema['params']['c_count_select_fields_display'] == false) echo 'style="display:none" todo-select="false"'; ?> >
    	<select id="" name="ExtensionCopyModel[extension_id]" class="select xs element_count_select_fields">
            <?php for($i = 1; $i<$schema['params']['count_select_fields']+1; $i++): ?>
    		  <option value="<?php echo $i;?>"
                <?php
                    if(isset($schema['params']['active_count_select_fields']) && (integer)$schema['params']['active_count_select_fields'] == $i) echo 'selected="selected"';
                ?>
                ><?php echo $i;?></option>
            <?php endfor; ?>
    	</select><!-- /.select.xs -->
    </span>

	<div class="checkbox display-option" <?php if(array_key_exists('c_list_view_display', $schema['params']) && $schema['params']['c_list_view_display'] == false) echo 'style="display:none"'; ?> >
    	<label>
            <input type="checkbox" class="element_params" data-type="list_view_visible"
                   <?php if(isset($schema['params']['list_view_visible']) && (bool)$schema['params']['list_view_visible'] == true) echo 'checked="checked"'; ?>
                   title="<?php echo Yii::t('base', 'Display in ListView');  ?>"
            >
        </label>
  	</div>
	<div class="checkbox display-option" <?php if(array_key_exists('c_process_view_group_display', $schema['params']) && $schema['params']['c_process_view_group_display'] == false) echo 'style="display:none"'; ?> >
    	<label>
            <input type="checkbox" class="element_params" data-type="process_view_group"
                   <?php if(isset($schema['params']['process_view_group']) && (bool)$schema['params']['process_view_group'] == true) echo 'checked="checked"'; ?>
                   title="<?php echo Yii::t('base', 'Sorting in the ProcessView');  ?>"
            >
        </label>
  	</div>
    <?php if((!isset($schema['params']['destroy']) || $schema['params']['destroy'] == true)){ ?>
		<a href="javascript:void(0)" class="todo-remove" data-element="panel" ><i class="fa fa-times"></i></a>
    <?php } ?>
    <?php // подолнительные параметры в скрытых aтрибутах ?>
    <span style="display: none;" class="element_params"><?php echo json_encode($schema['params']) ?></span>

    

</li><!-- /.inputs-group -->
<!-- panel END -->

