<div class="modal-dialog <?php echo $parent_class; ?>" data-is-center style="width: 620px;">
    <section class="panel" >
    <div class="edit-view in sm_extension no_middle"
        data-copy_id="<?php echo $extension_copy->copy_id; ?>"
        data-parent_copy_id="<?php echo $parent_copy_id; ?>"
        data-parent_data_id="<?php echo $parent_data_id; ?>"
        data-this_template="<?php echo $this_template; ?>"
        data-finished_object="<?php echo $finished_object; ?>"
        data-auto_new_card="<?php echo $auto_new_card; ?>"
   >
        <span style="display: none;" class="default_data"><?php echo $default_data; ?></span>
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Creating') ?></span>
            </span>
        	<span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>
        <?php
            $title = '';
            $read_only = '';
            $primary_field_schema = $extension_copy->getPrimaryField(null, false);
            foreach($primary_field_schema as $field_schema){  
                if(!empty($field_schema['params']['name_generate'])) {
                    $auto_name = Fields::getInstance()->getNewRecordTitle($field_schema['params']['name_generate'], $field_schema['params']['name_generate_params'], $extension_copy, false);
                    if($auto_name !== false) {
                        $title = $auto_name;
                        $read_only = 'readonly';
                    }
                }
            }
            
            $disable_new_card = false;
            $hide_block_select_by_default = true;
            
            if(!empty($auto_new_card)) {
                
                //переопределяем параметры в случае ожидания создания определенной карточки
                if(array_key_exists('disable_new_card', $params))
                    $disable_new_card = $params['disable_new_card'];
                
                if(array_key_exists('only_specific_block', $params))
                    $hide_block_select_by_default = false;
                
            }
        ?>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('base', 'Name'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <input name="project_name" <?php echo $read_only;?> class="form-control element" value="<?php echo $title; ?>" data-type="project_name" type="text" >
                                <div id="project_name_error" class="errorMessage" style="display:none;" ><?php echo Yii::t('messages', 'You will fill the field'); ?></div>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('base', 'Type'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" data-type="project_select">
                                    <?php if(!$disable_new_card){ ?>
                                    <option value="new_card" selected="selected"><?php echo Yii::t('base', 'New'); ?></option>
                                    <?php }?>
                                    <option value="from_template"><?php echo Yii::t('base', 'From template'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group" <?php if($hide_block_select_by_default) {?> style="display: none;" <?php }?>>
                        <span class="inputs-label"><?php echo $block_field_title; ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element select_templates_from_block" data-type="block">
                                    <?php foreach($blocks as $block){ ?>
                                    <option value="<?php echo $block['unique_index']; ?>"><?php echo $block['title']; ?></option>
                                    <?php } ?>
                                </select>
                                <div id="block_error" class="errorMessage" style="display:none;" class="errorMessage"><?php echo Yii::t('messages', 'You will fill the field'); ?></div>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group" style="display:  none;">
                        <span class="inputs-label"><?php echo Yii::t('base', 'Templates'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" data-type="template">
                                    <?php foreach($templates as $template){ ?>
                                    <option value="<?php echo $template['id'] ?>"><?php echo $template['module_title'] ?></option>
                                    <?php } ?>
                                </select>
<!--                                <div class="element" data-type="template">-->
<!--                                    <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">-->
<!--                                        <button data-id="--><?php //echo $templates[0]['id'] ?><!--" data-toggle="dropdown" data-reloader="parent" data-type="drop_down_button" class="btn btn-white dropdown-toggle element">-->
<!--                                            <span class="name">--><?php //echo $templates[0]['module_title'] ?><!--</span>-->
<!--                                        </button>-->
<!--                                        <ul class="dropdown-menu element" data-type="drop_down_list">-->
<!--                                            <div class="search-section">-->
<!--                                                <input type="text" class="submodule-search form-control" placeholder="--><?php //echo Yii::t('base', 'Search'); ?><!--">-->
<!--                                            </div>-->
<!--                                            <div class="submodule-table">-->
<!--                                                <table class="table list-table">-->
<!--                                                    <tbody>-->
<!--                                                    --><?php
//                                                    foreach($templates as $template) {
//                                                        ?>
<!--                                                        <tr class="sm_extension_data" data-id="--><?php //echo $template['id'] ?><!--">-->
<!--                                                            <td>-->
<!--                                                                <span class="name">--><?php //echo $template['module_title'] ?><!--</span>-->
<!--                                                            </td>-->
<!--                                                        </tr>-->
<!--                                                    --><?php //} ?>
<!--                                                    </tbody>-->
<!--                                                </table>-->
<!--                                            </div>-->
<!--                                        </ul>-->
<!--                                    </div>-->
<!--                                </div>-->
                                <div id="template_error" class="errorMessage" style="display:none;" class="errorMessage"><?php echo Yii::t('messages', 'You will fill the field'); ?></div>
                            </div>
                        </div>
                    </li>
                </ul>
                <input class="element" type="hidden" data-type="block_field_name" value="<?php echo $block_field_name; ?>">
            </div>
        	<div class="buttons-section">
        		<button type="submit" class="btn btn-primary edit_view_select_btn-create"><?php echo Yii::t('base', 'Create')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        	</div>
        </div>
    </div>

</section>
</div>



<script type="text/javascript">
    EditViewSelect = {
        templateDisplay : function(_this){
            var project_select = $(_this).closest('.sm_extension').find('.element[data-type="project_select"]');
            var project_select_value = project_select.val(); 
            var display = 'list-item'; 
            switch(project_select_value){
                case 'new_card' :
                        display = 'none';        
            }
            $(_this).closest('.sm_extension').find('.element[data-type="template"]').closest('li').css('display', display);
<?php 
            if($show_blocks) {
                //для ExtensionCopyModel включена настройка показа определенного блока
?>
                $(_this).closest('.sm_extension').find('.element[data-type="block"]').closest('li').css('display', display);
                
                if($(_this).closest('.sm_extension').find('.element[data-type="block"]').closest('li').css('display')=='list-item' && $(_this).closest('.sm_extension').find('.element[data-type="block"]').val()=='')
                    $(_this).closest('.sm_extension').find('.element[data-type="template"]').closest('li').css('display', 'none');
<?php
            }
?>
            EditViewSelect.setViewName(_this);
        },
        
        setViewName : function(_this){
            if(!$(_this).closest('.sm_extension').find('.element[data-type="project_name"]').prop('readonly')) {
                var project_select = $(_this).closest('.sm_extension').find('.element[data-type="project_select"]');
                var project_select_value = project_select.val(); 
                switch(project_select_value){
                    case 'new_card' :
                        $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').val(''); 
                    break;
                    case 'from_template' :
                        var template_name = $(_this).closest('.sm_extension').find('.element[data-type="template"] :selected').text();
                        $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').val(template_name);  
                    break;     
                }
            }   
        }
    }

    $(document).on('change', '.element[data-type="project_select"]', function(){
        EditViewSelect.templateDisplay(this);
    });
    
    $(document).on('change', '.element[data-type="template"]', function(){
        EditViewSelect.setViewName(this);
    });

    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });

    modalDialog.setPosition(modalDialog.TYPE_POSITION_CENTER);
    
</script>


