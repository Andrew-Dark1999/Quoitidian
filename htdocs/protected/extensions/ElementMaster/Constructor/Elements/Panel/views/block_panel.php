<!-- panel block -->
<div class="panel-body element_block_panel element" data-type="block_panel">
    <ul class="to-do-list inputs-block" id="req">

            <?php if(!empty($content)) echo $content; ?>

    </ul><!-- /.inputs-block -->
    <div class="operations">
		<a href="javascript:void(0)" class="add-field-action add_element_panel_field"><?php echo Yii::t('base', 'Agregar campo'); ?></a>
		<!--</a><a href="javascript:void(0)" class="add-field-action add_element_panel_table"><?php //echo Yii::t('base', 'Add table'); ?></a>-->
        
        <?php  
        if(SchemaOperation::getInstance()->beBlockPanelContactExists(ConstructorBuilder::$block_schema_active)){
            $style = (SchemaOperation::getInstance()->beBlockPanelContact(ConstructorBuilder::$block_schema_active['elements']) ? 'style="display: none;"' : '');
        ?>
            <a href="javascript:void(0)" class="add-field-action add_element_block_panel_contact" <?php echo $style; ?> ><?php echo Yii::t('base', 'Add block "Contact"'); ?></a>
        <?php } ?>
    </div>
</div>
<!-- panel block END -->


