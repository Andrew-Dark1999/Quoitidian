<?php ?>
<div class="phone-block contact-item editable-block element client-name" data-type="field_type_hidden">
    <input type="hidden" class="form-control element_params" data-type="name" value="<?php if(!empty($schema['params']['name'])) echo $schema['params']['name'] ?>">
    <input type="hidden" class="form-control element_params" data-type="title" value="<?php if(!empty($schema['params']['title'])) echo $schema['params']['title']; ?>">
    <input type="hidden" class="form-control element_params" data-type="type" value="string">
    <input type="hidden" class="form-control element_params" data-type="type_view" value="<?php echo Fields::TYPE_VIEW_EDIT_HIDDEN?>">
    <input type="hidden" class="form-control element_params" data-type="destroy" value="<?php if(array_key_exists('destroy', $schema['params'])) echo (integer)$schema['params']['destroy']; else echo '1' ?>">
    <input type="hidden" class="form-control element_params" data-type="unique" value="<?php if(isset($schema['params']['unique'])) echo (integer)$schema['params']['unique']; ?>" />

    <span class="phone editable-field"><?php if(!empty($schema['params']['title'])) echo $schema['params']['title']; ?></span>
    <span class="todo-actionlist actionlist-inline">
        <span class="edit-dropdown dropdown-right crm-dropdown dropdown">
            <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-pencil"></i></a>
            <ul class="dropdown-menu" role="menu">
                <li><input type="text" class="form-control element" data-type="module_title" value="<?php if(!empty($schema['params']['title'])) echo $schema['params']['title']; ?>"></li>
                <li><a href="javascript:void(0)" class="constructor-save-input-hidden"><?php echo Yii::t('base', 'Save') ?></a></li>
            </ul>
        </span>

        <span class="additional-menu">
            <div class="settings crm-dropdown dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle field-param " data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu settings-menu element_field_type_params element" data-type="field_type_params" role="menu">
                    <li>
                        <div class="checkbox">
                            <label><input type="checkbox" class="element_params" data-type="sip_number" <?php if(!empty($schema['params']['sip_number'])) echo 'checked="checked"'; ?> ><?php echo Yii::t('constructor', 'Phone'); ?></label>
                        </div>
                    </li>
                </ul>
            </div>

            <?php if(!isset($schema['params']['destroy']) || $schema['params']['destroy'] == true){ ?>
                <a href="javascript:void(0)" class="todo-remove-contact"><i class="fa fa-times"></i></a>
            <?php } ?>
        </span>
    </span>
</div>
