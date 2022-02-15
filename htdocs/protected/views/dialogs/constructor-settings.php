<div class="modal-dialog" style="width: 620px;">
    <section class="panel">
        <header class="panel-heading editable-block">
            <span class="editable-field"><?php echo Yii::t('constructor', 'Module settings'); ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
        </header>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-blocks ui-sortable">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'The name of the module in the database'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <input type="text" class="form-control element"  pattern="^[A-Za-z0-9_]+$" data-type="module_params" data-name="alias" value="<?php echo $params['alias']; ?>">
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Templates'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_TEMPLATES)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="is_template" style="display: none;" >
                                        <option value="0" <?php if($params['is_template'] == \ExtensionCopyModel::IS_TEMPLATE_DISABLED) echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Do not use templates'); ?></option>
                                        <option value="1" <?php if($params['is_template'] == \ExtensionCopyModel::IS_TEMPLATE_ENABLE) echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Use templates'); ?></option>
                                        <option value="2" <?php if($params['is_template'] == \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY) echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Use only templates'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Data visibility'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_DATA_IF_PARTICIPANT)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="data_if_participant" style="display: none;">
                                        <option value="0" <?php if($params['data_if_participant'] == '0') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Display data to all users'); ?></option>
                                        <option value="1" <?php if($params['data_if_participant'] == '1') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Display data only to participants'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Display in the menu'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_DISPLAY_IN_THE_MENU)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="menu_display" style="display: none;">
                                        <option value="1" <?php if($params['menu_display'] == '1') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Display'); ?></option>
                                        <option value="0" <?php if($params['menu_display'] == '0') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Do not display'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Finished objects'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_FINISHED_OBJECTS)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="finished_object" style="display: none;">
                                        <option value="0" <?php if($params['finished_object'] == '0') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Not to hide'); ?></option>
                                        <option value="1" <?php if($params['finished_object'] == '1') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Hide'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Show blocks'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_SHOW_BLOCKS)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="show_blocks" style="display: none;">
                                        <option value="1" <?php if($params['show_blocks'] == '1') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Show all blocks'); ?></option>
                                        <option value="0" <?php if($params['show_blocks'] == '0') echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Show one block'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('constructor', 'Show calendar'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" <?php if(!empty($extension_copy) && $extension_copy->getModule(false)->constructorSettingBlocked(\ConstructorModel::SETTING_CALENDAR_VIEW)) echo 'disabled="disabled"'; ?> data-type="module_params" data-name="calendar_view" style="display: none;">
                                    <option value="1" <?php if(!empty($extension_copy) && $extension_copy->isCalendarView()) echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Display'); ?></option>
                                    <option value="0" <?php if(!empty($extension_copy) && $extension_copy->isCalendarView() == false) echo 'selected="selected"'; ?>><?php echo Yii::t('constructor', 'Do not display'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="button" class="btn btn-primary constructor_btn-set-settings"><?php echo Yii::t('base', 'Save')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">

    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });

</script>
