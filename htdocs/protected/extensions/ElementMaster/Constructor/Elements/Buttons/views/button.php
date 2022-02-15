<span class="element" data-type="button">
    <?php if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING){ ?>
        <button class="btn btn-default btn-e add_element_field_type_params_for_button" data-toggle="dropdown"><?php echo Yii::t('base', 'Deadline'); ?></button><button type="button" class="delete-btn fa fa-times"></button>
    <?php } elseif($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION){ ?>
        <button class="btn btn-default btn-s"><?php echo Yii::t('base', 'Inscribirse'); ?></button><button type="button" class="delete-btn fa fa-times"></button>
    <?php } elseif($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){ ?>

        <button class="process_view btn btn-default btn-r add_element_field_type_params_for_button"  data-toggle="dropdown">
            <?php echo Yii::t('base', 'Responsable'); ?>
        </button>
        <button type="button" class="delete-btn fa fa-times"></button>
    <?php } elseif($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS){ ?>

        <button class="process_view btn btn-default btn-st add_element_field_type_params_for_button <?php if(isset($schema['params']['с_remove']) && (boolean)$schema['params']['с_remove']) echo 'remove'; ?>" data-toggle="dropdown">
            <?php echo Yii::t('base', 'Estatus'); ?>
        </button>
        
        <?php if(isset($schema['params']['с_remove']) && (boolean)$schema['params']['с_remove']){ ?>
            <button type="button" class="delete-btn fa fa-times"></button>
        <?php } ?>
    <?php } ?>

    <?php if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING || $schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION || $schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){ ?>
        <input type="hidden" class="element_params" data-type="name" value="<?php echo $schema['params']['name']; ?>" />
        <input type="hidden" class="element_params" data-type="type" value="<?php echo $schema['params']['type']; ?>" />
        <input type="hidden" class="element_params" data-type="type_view" value="<?php echo $schema['params']['type_view']; ?>" />
        <input type="hidden" class="element_params" data-type="c_db_create" value="<?php echo $schema['params']['c_db_create']; ?>" />
    <?php } ?>

    <?php if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING || $schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS || $schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){ ?>
        <?php if(!empty($field_type_params)) echo $field_type_params; ?>
        <input type="hidden" class="element_params" data-type="type" value="<?php echo $schema['params']['type']; ?>" />
        <input type="hidden" class="element_params" data-type="с_remove" value="<?php if(isset($schema['params']['с_remove'])) echo (integer)$schema['params']['с_remove']; else echo '1'; ?>" />
    <?php } ?>

</span>

