<?php if($start_wrapper) { ?>
        <div class="element" data-sub_type="<?php echo $start_wrapper; ?>">
<?php }
    $child_element = \ConstructorBuilder::getBlockChildElementName($schema);
    $module_title = $schema['params']['title'];
	switch ($module_title) {
    case "Participants":
        $module_title = "Participantes";
        break;
    case "Attachments":
        $module_title = "Adjuntos";
        break;
    case "Activity":
        $module_title = "Actividad";
        break;
	case "Additional info":
        $module_title = "Información adicional";
        break;
}
?>

<div class="panel inputs-panel element_block element" data-type="block" data-child_element="<?php echo $child_element; ?>" <?php if(isset($schema['params']['border_top']) && $schema['params']['border_top'] == false) echo 'style="border-top : none"' ?> >
   <span class="drag-marker"><i></i></span>
   <header class="panel-heading editable-block" <?php  if(isset($schema['params']['header_hidden']) && (boolean)$schema['params']['header_hidden'] == true) echo 'style="display: none;"'; ?>>
    <span class="client-name">
        <?php if(isset($schema['elements'][0]['type']) && $schema['elements'][0]['type'] == 'sub_module'){
            $module_title = ExtensionCopyModel::model()->findByPk($schema['elements'][0]['params']['relate_module_copy_id'])->title;
            if(!array_key_exists('relate_module_template', $schema['elements'][0]['params']) || (boolean)$schema['elements'][0]['params']['relate_module_template'] == false){ ?>
                <span><?php echo $module_title; ?></span>
            <?php } else { ?>
                <span><?php echo Yii::t('base', '{s} (templates)', array('{s}' => $module_title)); ?></span>
                <?php
            }
        } else { ?>
            <span class="editable-field" data-type="module_title"]><?php echo $module_title; ?></span>
        <?php } ?>
        <span class="todo-actionlist actionlist-inline">
            <span class="edit-dropdown dropdown-right crm-dropdown title-edit dropdown">
                <?php if(!empty($schema['params']['title_edit']) && $schema['params']['title_edit'] == true ){ ?>
                    <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-pencil"></i></a>
                <?php } ?>
                <ul class="dropdown-menu" role="menu">
                    <li><input type="text" class="form-control element element_block_title" data-type="module_title" value="<?php echo $module_title; ?>"></li>
                    <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save'); ?></a></li>
                </ul>
            </span>

            <?php if($child_element == 'sub_module'){ ?>
            <span class="crm-dropdown table-dropdown dropdown sub-module-params-cog-span">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle sub-module-params-cog" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu dropdown-shadow local-storage" role="menu">
                    <li>
                        <div class="checkbox">
                            <?php foreach(ConstructorBuilder::getInstance()->getSubModuleLinks($schema['elements'][0]['params']) as $links){ ?>
                                <label>
                                <input type="checkbox" class="element_params" data-type="relate_links" <?php if($links['checked']) echo 'checked="checked"'; ?> value="<?php echo $links['value']; ?>" /><?php echo $links['title']; ?>
                            </label>
                            <?php } ?>
                        </div>
                    </li>
                </ul>
            </span>
            <?php } ?>

            <?php if($child_element == 'block_participant'){ ?>
                <span class="crm-dropdown table-dropdown dropdown sub-module-params-cog-span">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle sub-module-params-cog" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu dropdown-shadow local-storage element" data-type="field_type_params" role="menu">
                    <li>
                        <div class="checkbox">
                            <label>
                            <input type="checkbox" class="element_params" data-type="process_view_group" <?php if(isset($schema['elements'][0]['params']['process_view_group']) && (bool)$schema['elements'][0]['params']['process_view_group'] == true) echo 'checked="checked"'; ?> />
                            <?php echo Yii::t('base', 'Ordenar en la vista del proceso');  ?>
                            </label>
                        </div>
                    </li>
                </ul>
            </span>
            <?php } ?>


            <?php
            // отключено до выяснения...
            if(true === false){
            ///if(isset($schema['elements'][0]['type']) && $schema['elements'][0]['type'] == 'sub_module'){
            ?>
            <span class="crm-dropdown table-dropdown dropdown">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu dropdown-shadow local-storage" role="menu">
                    <li>

                    <?php echo Yii::t('constructor', 'Type relate'); ?>
                        <select class="edit-dropdown selectpicker element_params" data-type="relate_type" placeholder=" <?php echo Yii::t('base', 'Type relate')?>">
                            <option value="<?php echo Fields::RELATE_TYPE_ONE; ?>"><?php echo Yii::t('constructor', 'One to many'); ?></option>
                            <option value="<?php echo Fields::RELATE_TYPE_MANY; ?>"><?php echo Yii::t('constructor', 'Many to many'); ?></option>
                        </select>
                    </li>
                </ul>
            </span>
            <?php } ?>


            <?php if(!empty($schema['params']['destroy'])){ ?>
                <a href="javascript:void(0)" class="todo-remove" data-element="block"><i class="fa fa-times"></i></a>
            <?php } ?>
    </span>
        <?php if(isset($schema['params']['chevron_down']) && (boolean)$schema['params']['chevron_down'] == true){ ?>
            <span class="tools pull-right">
        <a href="javascript:;" class="fa fa-chevron-down"></a>
    </span>
        <?php } ?>
    </span>
   </header>
   <?php if(!empty($content)) echo $content; ?>

   <input type="hidden" class="element_params" data-type="unique_index"  value="<?php if(array_key_exists('unique_index', $schema['params'])) echo $schema['params']['unique_index']; else echo md5($schema['params']['title'] . date('YmdHis') . mt_rand(1, 1000)); ?>" />
   <input type="hidden" class="element_params" data-type="border_top"    value="<?php if(isset($schema['params']['border_top']) && $schema['params']['border_top'] == false) echo '0'; else echo '1'; ?>" />
   <input type="hidden" class="element_params" data-type="header_hidden" value="<?php if(isset($schema['params']['header_hidden']) && (boolean)$schema['params']['header_hidden'] == true) echo '1'; else echo '0'; ?>" />
   <input type="hidden" class="element_params" data-type="chevron_down"  value="<?php if(isset($schema['params']['chevron_down']) && (boolean)$schema['params']['chevron_down'] == true) echo '1'; else echo '0'; ?>" />
   <input type="hidden" class="element_params" data-type="edit_view_show"    value="<?php if(isset($schema['params']['edit_view_show']) && (boolean)$schema['params']['edit_view_show'] == false) echo '0'; else echo '1'; ?>" />
   <input type="hidden" class="element_params" data-type="edit_view_display" value="<?php if(isset($schema['params']['edit_view_display']) && (boolean)$schema['params']['edit_view_display'] == false) echo '0'; else echo '1'; ?>" />
   <input type="hidden" class="element_params" data-type="destroy" value="<?php if(isset($schema['params']['destroy']) && (boolean)$schema['params']['destroy'] == false) echo '0'; else echo '1'; ?>" />
   <input type="hidden" class="element_params" data-type="block_panel_contact_exists"   value="<?php if(isset($schema['params']['block_panel_contact_exists']) && (boolean)$schema['params']['block_panel_contact_exists'] == true) echo '1'; else echo '0'; ?>" />
</div>
<!-- block  END -->

<?php 
    if($finish_wrapper) {
?>
        <!-- wrapper block END -->
        </div>
<?php
    }
?>
