<li class="clearfix form-group inputs-group element" data-type="dinamic">
    <span class="inputs-label"><?php echo \Yii::t('ProcessModule.base', 'Related object'); ?></span>
    <div class="columns-section col-1">
        <div class="element" data-type="relate_object_block">
            <?php echo $bo_model->getModuleDataListContent(); ?>

        </div>
    </div>
</li>
