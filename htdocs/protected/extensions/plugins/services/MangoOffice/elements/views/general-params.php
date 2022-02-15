<?php foreach($params_model->getPublicAttributes() as $attribute_name => $title){ ?>

    <div class="settings_form_group">
        <label class="col-lg-4 control-label text-right"><?php echo $params_model->attributeLabelByName($attribute_name); ?></label>
        <div class="col-lg-8">
            <?php
            echo \CHtml::activeTelField($params_model, $attribute_name, array('class' => 'element form-control', 'data-type'=>$attribute_name, 'data-service_params'=>1)); ?>
            <?php echo \CHtml::error($params_model, $attribute_name); ?>
        </div>
    </div>

<?php } ?>
