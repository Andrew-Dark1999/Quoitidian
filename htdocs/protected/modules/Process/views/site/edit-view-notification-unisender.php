<?php  $last_send_status = $operation_model->getActiveServiceModel()->getLastSendStatus(); ?>

<div class="modal-dialog bpm_modal_dialog">
    <section
            class="panel element"
            data-type="params"
            data-module="process"
            data-name="<?php echo $operation_model->getOperationsModel()->element_name; ?>"
            data-unique_index="<?php echo $operation_model->getOperationsModel()->unique_index; ?>"
    >
        <header class="panel-heading editable-block hidden-edit">
        <span class="client-name">
           <span class="editable-field element_data" data-type="module_title"><?php echo \Process\models\SchemaModel::getInstance()->getOperationTitleByUniqueIndex($operation_model->getOperationsModel()['unique_index']); ?></span>
                <span class="todo-actionlist actionlist-inline">
                    <span class="edit-dropdown crm-dropdown title-edit dropdown-right dropdown">
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <input type="text" class="form-control element" data-type="module_title" value="" autofocus />
                            </li>
                            <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save')?></a></li>
                        </ul>
                    </span>
                </span>
        </span>
        </header>

        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <?php echo $content; ?>
                </ul>
            </div>

            <div class="buttons-section">
                <button type="button" class="btn btn-primary element" data-type="save" <?php echo ($operations_model->getOperationsModel()->getStatus() != \Process\models\OperationsModel::STATUS_DONE ? '' : 'disabled'); ?>><?php echo Yii::t('base', 'Save')?></button>
                <?php if($last_send_status['status'] == false && $operation_model->getOperationsModel()->getStatus() != \Process\models\OperationsModel::STATUS_DONE){ ?>
                    <button type="button" class="btn btn-primary element" data-type="done"><?php echo Yii::t('ProcessModule.base', 'Skip')?></button>
                <?php } ?>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
        </div>
    </section>
</div>


<script type="text/javascript">
    ProcessObj.activateDropdowns();
    Global.groupDropDowns(0).init($('[data-type="label_add_filter"]'));

    <?php if($last_send_status['status'] == false){
            $m = $last_send_status['validate']->getValidateResultHtml();
        ?>
        setTimeout(function(){
            Message.show('<?php echo \Helper::deleteLinefeeds($m) ?>', false);
        }, 500);

    <?php } ?>



    ProcessObj.BPM.operationParams.getSchemaOperationNotificationElements = function(_this){
        // email
        var schema = [];
        var elements = <?php echo json_encode(\Process\models\NotificationService\NotificationUnisenderModel::getElementList()); ?>;
        $.each(elements, function(key, field_name){
            $(_this).find('.element[data-type="'+field_name+'"]').each(function(i, ul){
                var params = {'type' : field_name, 'value' : $(ul).val()}
                if(field_name == 'filter_field_name'){
                    params['condition']  = $(ul).closest('.element[data-type="service_vars"]').find('.element[data-type="condition"]').val();
                    params['condition_value']  = $(ul).closest('.element[data-type="service_vars"]').find('.element[data-type="condition_value"]').val();
                } else
                if(field_name == 'message_template'){
                    params['title'] = $(ul).find('option[value="'+params.value+'"]').text();
                }

                schema.push(params);
            })
        })

        return schema;
    }




    $(document).on('click','.element[data-type="btn_goto_params"]', function(){
        Message.hide();
    });


    /**
     * operation "data_record" - change
     */
    $(document).off('change', '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="recipient_type"],' +
                              '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="object_name"],' +
                              '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="module_name"],' +
                              '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="label_add_filter"],' +
                              '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="filter_field_name"]');
    $(document).on('change', '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="recipient_type"],' +
                             '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="object_name"],' +
                             '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="module_name"],' +
                             '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="label_add_filter"],' +
                             '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="filter_field_name"]', function(){
        var _this = this;
        var process = new Process();

        process.BPM.changeParamsContent(_this, 'notification', function(data){
            if(data.status == true){
                var content = '';
                $.each(data.params_result, function(key, value){
                    if($.isArray(value)) content+= value.join('');

                })
                $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(content);

                Global.initSelects();
                ProcessObj.activateDropdowns();
                ProcessObj.getCountOptions($('.select[multiple]'));

                Global.groupDropDowns(0).init($('[data-type="label_add_filter"]'));
            }
        })
    });


    $(document).off('click', '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="label_add_filter"]');
    $(document).on('click', '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="label_add_filter"]', function(){
        var _this = this;
        var process = new Process();
        process.BPM.changeParamsContent(_this, 'notification', function(data){
            if(data.status == true){
                var content = '';
                $.each(data.params_result, function(key, value){
                    if($.isArray(value)) content+= value.join('');

                })
                $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(content);

                Global.initSelects();
                ProcessObj.activateDropdowns();
                ProcessObj.getCountOptions($('.select[multiple]'));

                Global.groupDropDowns(0).init($('.element[data-type="label_add_filter"]'));
            }
        })
    });
</script>


