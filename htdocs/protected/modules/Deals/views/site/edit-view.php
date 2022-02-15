<div class="modal-dialog">
    <section class="panel" >
    <div class="edit-view in"
       data-unique_index="<?php echo (isset($_POST['unique_index']) ? $_POST['unique_index'] : md5(date('YmdHisu'))); ?>"
       data-copy_id="<?php if(!empty($extension_copy)) echo $extension_copy->copy_id; ?>"
       data-id="<?php if(!empty($id) && (!isset($_POST['from_template']) || (isset($_POST['from_template']) && $_POST['from_template'] == EditViewModel::THIS_TEMPLATE_MODULE))) echo $id; ?>"
       data-parent_copy_id="<?php echo $parent_copy_id['parent_copy_id']; ?>"
       data-parent_data_id="<?php echo $parent_data_id['parent_data_id']; ?>"
       data-pci="<?php echo $pci; ?>"
       data-pdi="<?php echo $pdi; ?>"
       data-this_template="<?php echo $this_template; ?>"
       data-relate_template="<?php echo $relate_template; ?>"
       data-template_data_id="<?php if(isset($template_data_id)) echo $template_data_id; ?>"
       data-history="hide"
       data-block_unique_index="<?php if(isset($block_unique_index) && $block_unique_index) echo $block_unique_index; ?>"
       data-auto_new_card="<?php echo $auto_new_card; ?>"
       data-params=""
    >

        <!-- Отображается только заголовок для edit-view второго и последующих уровней -->
        <header class="panel-heading previous-modal">
            <?php
                if($parent_copy_id['parent_copy_id']){
                    $parent_extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id['parent_copy_id']);
                    $primary_field_schema = $parent_extension_copy->getPrimaryField(null, false);
                    foreach($primary_field_schema as $field_schema){  
                        $primary_field_name = $field_schema['params']['name'];
                        
                ?>
                <span class="client-name"><?php
                    if(!empty($parent_copy_id['parent_copy_id'])){
                        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id['parent_copy_id']);
                        $is_parent_primary_title = SchemaOperation::getInstance()->primaryFieldActive($parent_extension_copy->getPrimaryField());
                        if($is_parent_primary_title){
                            $parent_data =  DataModel::getInstance()
                                                        ->setFrom($parent_extension_copy->getTableName())
                                                        ->setWhere($parent_extension_copy->prefix_name . '_id=:id', array('id'=>$parent_data_id['parent_data_id']))
                                                        ->findRow();
                                                   
                            if(!empty($parent_data)) echo $parent_data[$primary_field_name];
                        } 
                    }
                ?></span>
                <span class="from-label">
                    <?php if(!empty($is_parent_primary_title)){ ?>
                        <span id="from" <?php if(empty($parent_data)) echo 'style="display: none; "'  ?> ><?php echo Yii::t('base', 'from'); ?></span>
                    <?php } ?>
                    <?php
                        $vars = array(
                            'module' => array(
                                'copy_id' => $parent_copy_id['parent_copy_id'],
                            ),
                            'check_expediency_switch' => true,
                        );
                        $action_key = (new \ContentReloadModel(6))->addVars($vars)->prepare()->getKey();
                    ?>
                    <a href="javascript:void(0)" data-dismiss="modal" class="navigation_module_link" data-action_key="<?php echo $action_key; ?>" >
                        <?php  if(!empty($parent_extension_copy)) echo $parent_extension_copy->title; ?>
                    </a>
                </span>
            <?php
                    }
                }
            ?>
        </header>
        <?php
            $show_create = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'));
            $show_edit   = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'));
            $show_delete = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'));
        ?>
        
        
        <!-- Отображается только для edit-view первого уровня -->
        <header class="panel-heading editable-block hidden-edit">
            <?php
                $primary_field_schema = $extension_copy->getPrimaryField(null, false);
                $is_primary_title = SchemaOperation::getInstance()->primaryFieldActive($primary_field_schema);
                $primary_field_name = null;
                $new_record_title = Yii::t('messages', 'New record');

                $arr = [];
                if(count($primary_field_schema) > 1) {
                    $arr = [ Yii::t('messages', 'Surname'), Yii::t('messages', 'First name'), Yii::t('messages', 'Middle name') ];
                    $por = 0;
                }
                $auto_name = false;
                if($is_primary_title && !empty($primary_field_schema)){  
                    foreach($primary_field_schema as $field_schema){  
                        $primary_field_name = $field_schema['params']['name'];
                        if(!empty($field_schema['params']['name_generate']) && !$this_template) {
                            $auto_name = Fields::getInstance()->getNewRecordTitle($field_schema['params']['name_generate'], $field_schema['params']['name_generate_params'], $extension_copy, false);
                            if($auto_name !== false) 
                                $new_record_title = $auto_name;
                        }
                        if(count($arr)>0) {
                            $new_record_title = $arr[$por];
                            $por++;
                        }
            ?>
            <span class="client-name">
                <?php
                    //string, display
                    $primary_title = (Yii::app()->controller->module->isTemplate($extension_copy) && isset($_POST['module_title']) ? $_POST['module_title'] : (!$extension_data->isNewRecord ? $extension_data->{$primary_field_name} : $new_record_title));
                ?>
                    <span class="<?php if($auto_name !== false) echo 'non-'; ?>editable-field element_data" data-type="module_title" data-name="EditViewModel[<?php echo $primary_field_name ?>]"><?php echo $primary_title; ?></span>
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
            <span class="from-label">
                <?php if($is_primary_title){ ?>
                    <span id="from"><?php echo Yii::t('base', 'from'); ?></span>
                <?php } ?>
                <?php
                    $vars = array(
                        'module' => array(
                            'copy_id' => $extension_copy->copy_id,
                        ),
                        'check_expediency_switch' => true,
                    );
                    $action_key = (new \ContentReloadModel(6))->addVars($vars)->prepare()->getKey();
                ?>
                <a href="javascript:void(0)" class="navigation_module_link" data-action_key="<?php echo $action_key; ?>" >
                    <?php echo $extension_copy->getModule()->getModuleTitle(); ?>
                </a>
            </span>
            
        	<span class="tools options">
                <?php if($show_edit || $show_delete){ ?>
                    <span class="edit-dropdown crm-dropdown dropdown">
                        <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                        <ul class="dropdown-menu" role="menu">
                            <?php if($show_edit){ ?>
                                <li><a href="javascript:void(0)" class="edit_view_btn-copy"><?php echo Yii::t('base', 'Copy'); ?></a></li>
                            <?php } ?>
                            <?php if($show_delete){ ?>
                                <li><a href="javascript:void(0)" class="edit_view_btn-delete"><?php echo Yii::t('base', 'Delete'); ?></a></li>
                            <?php } ?>
                        </ul>
                    </span>
                <?php } ?>
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>

        <div class="panel-body">
            <?php
                echo $content;
             ?>
        	<div class="buttons-section">
                <?php if($show_edit){ ?>
                    <div class="btn btn-default actions crm-dropdown dropdown">
                        <span class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Actions')?></span>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="javascript:void(0)" data-type="make_contract" class="element"><?php echo Yii::t('DealsModule.base', 'Make a contract');?></a></li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-primary edit_view_btn-save"><?php echo Yii::t('base', 'Save')?></button>
                    <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
                <?php } ?>
        	</div>
        </div>
    </div>

</section>
</div>



<script type="text/javascript">
    $(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        if(content_vars){
            content_vars = JSON.parse(content_vars);
            instanceGlobal.contentReload.addContentVars(content_vars);
        }
    });


    var datePerm = null;

    EditView.replaceForLink();
    Global.showParticipant();

    $(function () {
        $('.buttons-section .actions .element[data-type="make_contract"]').on('click', function () {
            
        });
    });

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
    

    History.add('<?php if(!empty($extension_copy)) echo $extension_copy->copy_id; ?>', '<?php if(!empty($id)) echo $id; ?>', {});


</script>


