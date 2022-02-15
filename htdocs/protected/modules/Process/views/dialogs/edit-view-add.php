<div class="modal-dialog <?php echo $parent_class; ?>" data-is-center style="width: 620px;">
    <section class="panel" >
    <div class="edit-view in sm_extension no_middle"
        data-copy_id="<?php echo $extension_copy->copy_id; ?>"
        data-parent_copy_id="<?php echo $parent_copy_id; ?>"
        data-parent_data_id="<?php echo $parent_data_id; ?>"
        data-this_template="<?php echo $this_template; ?>"
        data-finished_object="<?php echo $finished_object; ?>"
   >
        <span style="display: none;" class="default_data"><?php echo $default_data; ?></span>
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('ProcessModule.base', 'Starting the process'); ?></span>
            </span>
        	<span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>
        <?php
            $read_only = '';
            if($extension_copy->isAutoEntityTitle()){
                $field_schema = $extension_copy->getPrimaryField();
                $auto_title = Fields::getInstance()->getNewRecordTitle($field_schema['params']['name_generate'], $field_schema['params']['name_generate_params'], $extension_copy, false);
                if($auto_title !== false) {
                    $process_title = $auto_title;
                    $read_only = 'readonly';
                }
            }
        ?>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block element" data-type="objects">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('ProcessModule.base', 'Process name'); ?></span>
                        <div class="columns-section col-1 element" data-type="project_name_block">
                            <div class="column">
                                <input name="project_name" <?php echo $read_only;?> class="form-control element" data-type="project_name" value="<?php echo $process_title; ?>" type="text" >
                                <div class="errorMessage"></div>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group" style="display: none;">
                        <input type="hidden" class="element" data-type="project_select" value="from_process_template">
                    </li>
                    <li class="clearfix form-group inputs-group" <?php if($process_id) echo 'style="display : none"' ?>>
                        <span class="inputs-label"><?php echo Yii::t('base', 'Templates'); ?></span>
                        <div class="columns-section col-1 element" data-type="template_block">
                            <div class="column">
                                <select class="select element" data-type="template" data-changed="1">
                                    <?php foreach($templates as $template){ ?> 
                                    <option value="<?php echo $template['id'] ?>" <?php if($process_id && $process_id == $template['id']) echo 'selected'?> ><?php echo $template['module_title'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="errorMessage"></div>
                            </div>
                        </div>
                    </li>
                    <?php if(!empty($bpm_params_html)) echo $bpm_params_html; ?>
                </ul>
            </div>
        	<div class="buttons-section">
        		<button type="submit" class="btn btn-primary edit_view_select_btn-create" data-type="process"><?php echo Yii::t('base', 'Create')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        	</div>
        </div>
    </div>

</section>
</div>



<script type="text/javascript">
    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });
    
    $(document).on('change', '.element[data-type="template"]', function(){
        if(!$(this).closest('.sm_extension').find('.element[data-type="project_name"]').prop('readonly')) {
            var template_name = $(this).closest('.sm_extension').find('.element[data-type="template"] :selected').text();
            $(this).closest('.sm_extension').find('.element[data-type="project_name"]').val(template_name);  
        }
    });
    modalDialog.setPosition(modalDialog.TYPE_POSITION_CENTER);
</script>


