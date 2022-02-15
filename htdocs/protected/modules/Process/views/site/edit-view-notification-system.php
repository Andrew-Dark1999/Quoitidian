<?php
if(isset($vars['edit_view']['status']) && in_array($vars['edit_view']['status'], array('error', false))){
    echo $vars['edit_view']['messages'];
    return;
}
?>
<div class="modal-dialog">
    <section
        class="panel element"
        data-type="params"
        data-module="process"
        data-name="<?php echo $operation_model->getOperationsModel()->element_name; ?>"
        data-unique_index="<?php echo $operation_model->getOperationsModel()->unique_index; ?>"
    >
        <div class="edit-view in"
             data-unique_index="<?php echo (isset($_POST['unique_index']) ? $_POST['unique_index'] : md5(date('YmdHisu'))); ?>"
             data-copy_id="<?php echo \ExtensionCopyModel::MODULE_NOTIFICATION?>"
             data-id="<?php if(!empty($vars['edit_view']['id']) && (!isset($_POST['from_template']) || (isset($_POST['from_template']) && $_POST['from_template'] == EditViewModel::THIS_TEMPLATE_MODULE))) echo $vars['edit_view']['id']; ?>"
             data-parent_copy_id="<?php echo $vars['edit_view']['parent_copy_id']['parent_copy_id']; ?>"
             data-parent_data_id="<?php echo $vars['edit_view']['parent_data_id']['parent_data_id']; ?>"
             data-pci="<?php echo $vars['edit_view']['pci']; ?>"
             data-pdi="<?php echo $vars['edit_view']['pdi']; ?>"
             data-this_template="<?php echo $vars['edit_view']['this_template']; ?>"
             data-relate_template="<?php echo $vars['edit_view']['relate_template']; ?>"
             data-template_data_id="<?php if(isset($vars['edit_view']['template_data_id'])) echo $vars['edit_view']['template_data_id']; ?>"
             data-history="hide"
        >

            <!-- Отображается только для edit-view первого уровня -->
            <header class="panel-heading editable-block hidden-edit">
                <?php
                $primary_field_schema = $vars['edit_view']['extension_copy']->getPrimaryField(null, false);
                $is_primary_title = SchemaOperation::getInstance()->primaryFieldActive($primary_field_schema);
                $primary_field_name = null;
                if($is_primary_title && !empty($primary_field_schema)){
                    foreach($primary_field_schema as $field_schema){
                        $primary_field_name = $field_schema['params']['name'];
                        ?>
                        <span class="client-name">
                <?php
                //string, display
                $primary_title = (isset($_POST['module_title']) ? $_POST['module_title'] : (!$vars['edit_view']['extension_data']->isNewRecord ? $vars['edit_view']['extension_data']->{$primary_field_name} : Yii::t('ProcessModule.base', 'Notification')));
                ?>
            <span class="editable-field element_data" data-type="module_title" data-name="EditViewModel[<?php echo $primary_field_name ?>]"><?php echo $primary_title; ?></span>
                <span class="todo-actionlist actionlist-inline">
                    <span class="edit-dropdown crm-dropdown title-edit dropdown-right dropdown">
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <input type="text" class="form-control element" data-type="module_title" value="<?php echo $primary_title;  ?>" autofocus />
                            </li>
                            <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save')?></a></li>
                        </ul>
                    </span>
                </span>
            </span>
                        <?php
                    }
                }
                ?>

                <span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
            </header>

            <div class="panel-body">
                <?php
                echo $vars['edit_view']['content'];
                ?>
                <div class="buttons-section">
                    <button type="submit" class="btn btn-primary edit_view_card_btn-save"><?php echo Yii::t('base', 'Save')?></button>
                    <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
                </div>
            </div>
        </div>

    </section>
</div>



<script type="text/javascript">



    ProcessObj.BPM.operationParams.getSchemaOperationNotification = function(_this){
         var schema = [{
         'type' : 'copy_id',
         'value' : _this.find('.edit-view').data('copy_id'),
         }, {
         'type' : 'card_id',
         'value' : _this.find('.edit-view').data('id'),
         }, {
         'type' : 'sdm_operation_task',
         'value' : _this.find('.edit-view .element[data-type="sdm_operation_task"]').val(),
         }];

        return schema;
    }


var datePerm = null;

    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });

    if (top.location != location) {
        top.location.href = document.location.href ;
    }


    $('.date').datepicker({
        language: Message.locale.language,
        format: Message.locale.dateFormats.medium_js,
        minDate: '1/1/1970',
        autoclose: true
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

    $('.datetime[type="button"]').datepicker({
        language: Message.locale.language,
        format: Message.locale.dateFormats.medium_js,
        startDate:new Date(),
        autoclose: true
    }).on('show', function(e){
        datePerm = $(this).val();
        if ( e.date ) {
            $(this).data('stickyDate', e.date);
        }
        else {
            $(this).data('stickyDate', null);
        }
        if ($(this).closest('.buttons-block').length>0) {
            $('.datepicker.datepicker-dropdown').css('left', $(this).closest('label').offset().left+'px');
        }
    }).on('hide', function(e){
        var stickyDate = $(this).data('stickyDate');
        if ( !e.date && stickyDate ) {
            $(this).datepicker('setDate', stickyDate);
            $(this).data('stickyDate', null);
        }
        if ($(this).val() == "") {
            $(this).val(datePerm);
        }
    });


    $('.datetime[type="text"]').mask(Message.locale.dateTimeFormats.mask_js);
    $(".date").mask(Message.locale.dateFormats.mask_js);
    $(".time").mask(Message.locale.timeFormats.mask_js);


</script>
