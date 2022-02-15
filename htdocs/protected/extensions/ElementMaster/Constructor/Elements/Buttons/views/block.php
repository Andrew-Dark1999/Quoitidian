<div class="buttons-block element" data-type="block_button">

    <?php echo $content; ?>

    <div class="btn-group crm-dropdown edit-dropdown projects-drop-down element <?php echo ConstructorBuilder::displayBlockButtonBox($schema['elements']); ?>"
         data-type="block_button_box">

        <button class="btn btn-create dropdown-toggle"
                data-toggle="dropdown"><?php echo Yii::t('base', 'Button'); ?> +
        </button>
        <ul class="dropdown-menu dropdown-shadow">
            <li><a href="javascript:void(0)"
                   class="constructor_btn-add-block-button <?php if(SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_DATE_ENDING, $schema['elements'])) echo 'hidden'; ?>"
                   data-type="<?php echo Fields::TYPE_VIEW_BUTTON_DATE_ENDING; ?>"><?php echo Yii::t('base', 'Deadline'); ?></a>
            </li>
            <li><a href="javascript:void(0)" class="constructor_btn-add-block-button hidden"
                   data-type="<?php echo Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION; ?>"><?php echo Yii::t('base', 'Subscribe'); ?></a>
            </li>
            <li><a href="javascript:void(0)"
                   class="constructor_btn-add-block-button <?php if(SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_RESPONSIBLE, $schema['elements'])) echo 'hidden'; ?>"
                   data-type="<?php echo Fields::TYPE_VIEW_BUTTON_RESPONSIBLE; ?>"><?php echo Yii::t('base', 'Responsible'); ?></a>
            </li>
            <li><a href="javascript:void(0)"
                   class="constructor_btn-add-block-button <?php if(SchemaOperation::getInstance()->isSetButton(Fields::TYPE_VIEW_BUTTON_STATUS, $schema['elements'])) echo 'hidden'; ?>"
                   data-type="<?php echo Fields::TYPE_VIEW_BUTTON_STATUS; ?>"><?php echo Yii::t('base', 'Status'); ?></a>
            </li>
        </ul>
    </div>

    <button type="button"
            class="btn constructor_btn-save btn-primary"><?php echo \Yii::t('base', 'Save'); ?></button>
    <button type="button" class="btn btn-default constructor_btn-cancel"
            data-dismiss="modal"><?php echo Yii::t('base', 'Cancel') ?></button>
</div>
