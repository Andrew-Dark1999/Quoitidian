<div id="personal_information" class="tab-pane element <?php if(isset($tab_active) && $tab_active == 'personal_information') echo 'active'; ?>" data-type="profile_personal_information">
    <div class="row">
        <div class="profile_form clearfix">
            <div class="col-lg-12">
                <div class="profile_form_title col-xs-4"><?php echo Yii::t('UsersModule.base', 'Personal information') ?></div>
            </div>
            <div class="col-lg-12">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Full name') ?></label>
                <div class="col-xs-8">
                    <?php echo CHtml::textField('first_name', $personal_info_data['first_name'], array('class'=>'half_prof form-control element', 'data-type' =>'first_name', 'data-field_type' =>'text', 'placeholder'=>Yii::t('UsersModule.base', 'First name'))) ?>
                    <?php echo CHtml::error($personal_info_model,  'first_name'); ?>
                    <?php echo CHtml::textField('sur_name', $personal_info_data['sur_name'], array('class'=>'half_prof form-control element', 'data-type' =>'sur_name', 'data-field_type' =>'text', 'placeholder'=>Yii::t('UsersModule.base', 'Surname'))) ?>
                    <?php echo CHtml::error($personal_info_model,  'sur_name'); ?>
                </div>
            </div>
            <div class="col-lg-12">
                <label class="col-xs-4 profile_form_label">Email</label>
                <div class="col-xs-8">
                    <?php echo CHtml::textField('email', $personal_info_data['email'], array('class'=>'form-control element', 'data-type' =>'email', 'data-field_type' =>'text', 'placeholder'=>'mail@mail.ru',)) ?>
                    <?php echo CHtml::error($personal_info_model,  'email'); ?>
                </div>
            </div>
            <?php /* ?>
            <div class="col-lg-12 timezone">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Time zone') ?></label>
                <div class="col-xs-8">
                    <?php echo CHtml::dropDownList('ProfilePersonalInformationModel[time_zones_id]', $personal_info_data['time_zones_id'], CHtml::listData(TimeZonesModel::model()->findAll(), 'name', 'title'), array('class'=>'selectpicker element', 'data-type' =>'text')) ?>
                    <?php echo CHtml::error($personal_info_model,  'time_zones_id'); ?>
                </div>
            </div>
            <?php */ ?>
            <div class="col-lg-12 timezone">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Language') ?></label>
                <div class="col-xs-8">
                    <?php echo CHtml::dropDownList('language', $personal_info_data['language'], CHtml::listData(LanguageModel::model()->scoreActive()->findAll(), 'name', 'title'), array('class'=>'selectpicker element select', 'data-type' =>'language', 'data-field_type' =>'text')) ?>
                    <?php echo CHtml::error($personal_info_model,  'language'); ?>
                </div>
            </div>

<!--            <div class="col-lg-12">-->
<!--                <label class="col-xs-4 profile_form_label">--><?php //echo Yii::t('UsersModule.base', 'Password') ?><!--</label>-->
<!--                <div class="col-xs-8">-->
<!--                    --><?php //echo CHtml::passwordField('password', $personal_info_data['password'], array('class'=>'form-control element', 'data-type' =>'password', 'data-field_type' =>'text', 'autocomplete' => 'new-password', 'placeholder'=>'••••••••') ) ?>
<!--                    --><?php //echo CHtml::error($personal_info_model,  'password'); ?>
<!--                </div>-->
<!--            </div>-->
<!--            <div class="col-lg-12">-->
<!--                <label class="col-xs-4 profile_form_label">--><?php //echo Yii::t('UsersModule.base', 'Confirm password') ?><!--</label>-->
<!--                <div class="col-xs-8">-->
<!--                    --><?php //echo CHtml::passwordField('password_confirm', $personal_info_data['password_confirm'], array('class'=>'form-control element', 'data-type' =>'password_confirm', 'autocomplete' => 'new-password', 'data-field_type' =>'text', 'placeholder'=>'••••••••') ) ?>
<!--                </div>-->
<!--            </div>-->
            <div class="col-lg-12 button-block">
                <label class="col-xs-4"></label>
                <div class="col-xs-8 buttons-section">
                    <button class="btn btn-primary element" data-type="restore-password" data-email="<?php echo $personal_info_data['email']?>" type="button"><?php echo Yii::t('UsersModule.base', 'Change password') ?></button>
                    <div id="profile-loader" class="lds-dual-ring profile-loader hidden"></div>
                </div>
            </div>
            <div class="col-lg-12 type-editor">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('base', 'Type of editor') ?></label>
                <div class="col-xs-8">
                    <?php
                        echo CHtml::dropDownList(
                                'activity_editor',
                                $personal_info_data['activity_editor'],
                                BlockActivityEditorDefinition::getTitleCollection(),
                                    [
                                        'data-type' => 'activity_editor',
                                        'data-field_type' => 'text',
                                        'class' => 'selectpicker element select',
                                    ]
                            );
                        echo CHtml::error($personal_info_model, 'activity_editor');
                    ?>
                </div>
            </div>
            <div class="col-lg-12 background-field">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Background') ?></label>
                <div class="col-xs-8 element" data-type="file_upload_block">
                    <?php echo CHtml::hiddenField(
                            'background',
                            $personal_info_data['background'],
                            [
                                    'class'=>'form-control element',
                                    'data-type' =>'background',
                                    'data-format' => (implode(',', [FormatImageFileDefinition::JPG, FormatImageFileDefinition::JPEG, FormatImageFileDefinition::GIF, FormatImageFileDefinition::PNG])),
                                    'data-image_size_pixels' => '1920,1080',
                                    'data-field_type' =>'text',
                            ])
                    ?>
                    <input class="upload_image_link form-control element" type="text" data-type="file_view" value="<?php echo $personal_info_data['background_file_title'] ?>" readonly placeholder="<?php echo Yii::t('base', 'Upload from disk') ?>">
                    <?php echo CHtml::error($personal_info_model,'background'); ?>

                    <a href="javascript:void(0)" class="underline element <?php echo $personal_info_data['background'] ? '' : 'hidden'; ?>" data-type="remove_image_file"><?php echo Yii::t('UsersModule.base', 'Restore default background') ?></a>
                    <div class="sub-format-file-upload">
                        <span><?php echo Yii::t('base', 'Image format') ?>:  <?php echo implode(', ', [FormatImageFileDefinition::JPG, FormatImageFileDefinition::JPEG, FormatImageFileDefinition::GIF, FormatImageFileDefinition::PNG]) ?></span>
                        <span><?php echo Yii::t('base', 'Maximum allowable size') ?>: 1920*1080px, <?php echo HelperIniParams::getPostUploadMaxFileSize(HelperIniParams::UNIT_MEGABYTE, false) . Yii::t('base', 'Mb') ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 button-block">
                <label class="col-xs-4"></label>
                <div class="col-xs-8 buttons-section">
                    <button class="btn btn-primary element" data-type="personal_information_save" type="button"><?php echo Yii::t('UsersModule.base', 'Save') ?></button>
                    <button class="btn btn-default" type="button"><?php echo Yii::t('UsersModule.base', 'Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
