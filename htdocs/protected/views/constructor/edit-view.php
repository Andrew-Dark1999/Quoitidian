<?php
    $show_edit   = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
    $show_delete = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
?>
<div class="modal-dialog">
    <section class="panel">

    <div class="constructor"
        data-copy_id="<?php if(isset($extension_copy)) echo $extension_copy->copy_id; ?>"
        data-extension_id="<?php if(isset($extension)) echo $extension->extension_id; ?>"
    >
    <header class="panel-heading editable-block">
        <span class="client-name">
            <span class="editable-field"><?php if(isset($extension_copy))echo $extension_copy->title; else echo $extension->getActiveModule()->getConstructorTitle(); ?></span>
            <span class="todo-actionlist actionlist-inline">
                <span class="edit-dropdown dropdown-right title-edit crm-dropdown dropdown">
                    <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-pencil"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><input type="text" class="form-control element" data-type="module_title" value="<?php if(isset($extension_copy)) echo $extension_copy->title;  else echo $extension->getActiveModule()->getConstructorTitle(); ?>"></li>
                        <?php if($show_edit){ ?>
                            <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save'); ?></a></li>
                        <?php } ?>
                    </ul>
                </span>
            </span>
        </span>
        <span class="tools pull-right">
                <!-- <a href="javascript:;" class="fa fa-chevron-down"></a> -->
            <?php if($show_edit || $show_delete){?>
                <span class="edit-dropdown crm-dropdown dropdown">
        			<a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
        		    <ul class="dropdown-menu" role="menu">
                    <?php if($show_edit){ ?>
                        <li><a href="javascript:void(0)" class="btn-action-modal" data-controller="module_settings"><?php echo Yii::t('base', 'Settings'); ?></a></li>
                        <li><a href="javascript:void(0)" class="btn-action-modal" data-controller="module_copy"><?php echo Yii::t('base', 'Copy'); ?></a></li>
                    <?php } ?>
                        <?php if($show_delete){ ?>
                            <li><a href="javascript:void(0)" class="btn-action-modal" data-controller="module_delete"><?php echo Yii::t('base', 'Delete'); ?></a></li>
                        <?php } ?>
        		    </ul>
        		</span>
            <?php } ?>
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
    </header>
    <div class="panel-body">

        <?php echo $content; ?>
        
        <?php if($show_edit){ ?>
    	<div class="buttons-section">
    		<button type="button" class="btn constructor_btn-save btn-primary"><?php echo Yii::t('base', 'Save'); ?></button>
    		<button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel'); ?></button>
    		<button type="button" class="btn constructor_btn-show-submodule-list btn-create sub-modal-link" data-target="#modal_dialog_child"><?php echo Yii::t('base', 'Submodule'); ?> +</button>
            <button type="button" class="btn constructor_btn-show-block-list btn-create sub-modal-link" data-target="#modal_dialog_child"><?php echo Yii::t('base', 'Block'); ?> +</button>
        </div>
        <?php } ?>

    </div>
    <span class="element" data-type="module_params_block">
        <input type="hidden" class="element" data-type="module_params" data-name="destroy" value="<?php if((isset($extension_copy) && $extension_copy->getModule()->Destroy()) || $extension->getActiveModule()->Destroy())  echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="is_template" value="<?php if(isset($extension_copy)) echo $extension_copy->getIsTemplate(); ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="data_if_participant" value="<?php if((isset($extension_copy) && $extension_copy->getModule()->getRawDataIfParticipant()) || $extension->getActiveModule()->getRawDataIfParticipant())  echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="menu_display" value="<?php if((isset($extension_copy) && $extension_copy->getModule()->menuDisplay()) || $extension->getActiveModule()->menuDisplay())  echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="finished_object" value="<?php if((isset($extension_copy) && $extension_copy->getModule()->finishedObject()) || $extension->getActiveModule()->finishedObject())  echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="show_blocks" value="<?php if((isset($extension_copy) && $extension_copy->getModule()->showBlocks()) || $extension->getActiveModule()->showBlocks())  echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="calendar_view" value="<?php if(isset($extension_copy) && $extension_copy->isCalendarView()) echo '1'; else echo '0'; ?>"/>
        <input type="hidden" class="element" data-type="module_params" data-name="alias" value="<?php if(isset($extension_copy)) echo $extension_copy->alias; ?>"/>
    </span>
    </div>

</section>
</div>




<script type="text/javascript">

    $('.element[data-type="panel"]').each(function(i, ul){
        var count = $(ul).find('select.element_field_type').length;
        $(ul).find('.element_count_select_fields').val(count);
    });



    /*
     * Constructor sctipts
     */

    if(parseInt($('.constructor').data('copy_id')) > 0){
        $('.constructor .element_field_type').each(function(i, ul){
            if($(ul).closest('.element[data-type="field_type"]').find('.element_params[data-type="is_primary"]').val() != "1"
            )
            $(ul).attr('disabled', 'disabled');
        });
        
        $('.constructor .element_params[data-type="name"]').each(function(i, ul){
            $(ul).attr('disabled', 'disabled');
        });
    }

    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });

    $('.date').datepicker({
        language: Message.locale.language,
        format: Message.locale.dateFormats.medium_js,
        minDate: '1/1/1970',
        autoclose: true,
    }).on('show', function() {
        var $popup = $('.datepicker');
        $popup.click(function () { return false; });
    }).on('show', function(e){
        if ( e.date ) {
             $(this).data('stickyDate', e.date);
        }
        else {
             $(this).data('stickyDate', null);
        }
    }).on('hide', function(e){
        var stickyDate = $(this).data('stickyDate');

        if ( !e.date && stickyDate ) {
            $(this).datepicker('setDate', stickyDate);
            $(this).data('stickyDate', null);
        }
    });



    $('.time').each(function(i, ul){
        var time = $(ul).val();
        if(typeof(time) == 'undefined' || !time) time = '';
        
        $(ul).timepicker({
            minuteStep: 1,
            secondStep: 5,
            showSeconds: true,
            showMeridian: false,
            defaultTime: time,
        });
    });

    

    $(".date").mask(Message.locale.dateFormats.mask_js);
    $(".time").mask(Message.locale.timeFormats.mask_js);


    Constructor
        .setFieldNameList(<?php if(!empty($extension_copy)) echo json_encode(array_keys($extension_copy->getFieldsSchemaList())); ?>)

    Constructor.getInstance().isAutoNumberExist();
</script>
