<div class="contact-item editable-block element" data-type="field_type_hidden">
    <span><?php if(isset($schema['params']['title'])) echo $schema['params']['title']; ?></span>
    <span class="value-block">
	    <a href="javascript:void(0)" class="contact-value"><span class="editable-field element_edit_hidden" data-name="<?php if(isset($schema['params']['name'])) echo 'EditViewModel[' . $schema['params']['name'] . ']'; ?>"><?php echo $extension_data[$schema['params']['name']]; ?></span></a>
		<span class="todo-actionlist actionlist-inline">
	        <span class="edit-dropdown dropdown-right crm-dropdown dropdown">
                <?php if(!$read_only){ ?>
			    <ul class="dropdown-menu" role="menu">
				    <li><input type="text" class="form-control" value="<?php  ?><?php echo $extension_data[$schema['params']['name']]; ?>"></li>
				    <li><a href="javascript:void(0)" class="edit_view-save-input-hidden"><?php echo Yii::t('base', 'Save') ?></a></li>
			    </ul>
                <?php } ?>
			</span>
	    </span>
    </span>

    <?php if(!empty($schema['params']['sip_number'])){ ?>
        <span class="element handset" data-type="sip-link" data-action="call">
            <i class="fa fa-phone" aria-hidden="true"></i>
        </span>
    <?php } ?>
</div>
