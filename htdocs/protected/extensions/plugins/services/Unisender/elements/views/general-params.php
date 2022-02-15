<div class="settings_form_group">
    <label class="col-lg-4 control-label text-right"><?php echo \Yii::t('base', 'Api key'); ?></label>
    <div class="col-lg-8">
        <?php
        echo \CHtml::activeTelField($params_model, 'api_key', array('class' => 'element form-control', 'data-type'=>'api_key', 'data-service_params'=>1)); ?>
        <?php echo \CHtml::error($params_model, 'api_key'); ?>
    </div>
</div>


