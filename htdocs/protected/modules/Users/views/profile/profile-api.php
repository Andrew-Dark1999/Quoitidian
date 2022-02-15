<div id="api"
         class="tab-pane element <?php if(isset($tab_active) && $tab_active == 'api') echo 'active';?>"
     data-type="profile_api">
    <div class="row">
        <div class="profile_form clearfix">
            <div class="col-lg-12">
                <div class="profile_form_title col-xs-4"><?php echo Yii::t('UsersModule.base', 'API connection') ?></div>
            </div>
            <div class="col-lg-12">
                <div class="col-xs-4"></div>
                <div class="col-xs-8 m-label-activate">
                    <?php
                    echo CHtml::checkBox(
                        'api_active',
                        (bool)$user_info['user_model']->api_active,
                        array(
                            'class' => 'element',
                            'data-type' => 'api_active',
                            'data-field_type' => 'check',
                        ));
                    echo CHtml::label(Yii::t('UsersModule.base', 'Usе'), 'api_active');
                    ?>
                </div>
            </div>
            <div class="col-lg-12">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Api key') ?></label>
                <div class="col-xs-8">
                    <?php echo CHtml::textField('api_key', $user_info['user_model']->api_key, array('class' => 'form-control element', 'data-type' => 'api_key', 'data-field_type' => 'text', 'readonly' => true)) ?>
                    <?php echo CHtml::error($user_info['user_model'],  'api_key'); ?>
                </div>
            </div>
            <div class="col-lg-12 button-block">
                <label class="col-xs-4"></label>
                <div class="col-xs-8 buttons-section">
                    <button class="btn btn-primary element" data-type="api_regenerate_token" type="button"><?php echo Yii::t('UsersModule.base', 'Regenerate') ?></button>
                    <button class="btn btn-primary element" data-type="api_save" type="button"><?php echo Yii::t('UsersModule.base', 'Save') ?></button>
                    <button class="btn btn-default" type="button"><?php echo Yii::t('UsersModule.base', 'Cancel') ?></button>

            </div>
        </div>
    </div>
</div>
