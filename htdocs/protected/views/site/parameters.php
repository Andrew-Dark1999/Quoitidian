<div class="list_view_block paramrtrs_module">
<!--Forms begin-->
<section class="panel">
    <header class="panel-heading"><?php echo Yii::t('base', 'SYSTEM SETUP'); ?></header>
    <div class="panel-body content_form">
        <div class="adv-table">
            <?php $form = $this->beginWidget('CActiveForm',
                            array(
                                'id' => 'parameters-form',
                                'enableAjaxValidation' => false,
                                'htmlOptions' => array(
                                        'class' => 'settings_form',
                                        'data-selector_content_box' => '#content_container',
                                    ),
                                )
                    );
            ?>
				<div class="col-lg-4 settings_title text-right">
					<?php echo Yii::t('base', 'Main settings'); ?>
				</div>
				<div class="settings_form_group">
                    <?php echo $form->labelEx($model,'crm_name',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
                        <?php echo $form->textField($model,'crm_name',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'crm_name'); ?>
				</div>
                <div class="settings_form_group">
					<?php echo $form->labelEx($model,'crm_description',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'crm_description',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'crm_description'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'admin_email',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'admin_email',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'admin_email'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'admin_password',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->passwordField($model,'admin_password',array('class'=>'form-control', 'autocomplete' => 'new-password', 'placeholder'=>'••••••••')); ?>
					</div>
                    <?php echo $form->error($model,'admin_password'); ?>
				</div>
				<div sd0 class="settings_form_group">
					<?php echo $form->labelEx($model,'admin_password_confirm',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->passwordField($model,'admin_password_confirm',array('class'=>'form-control', 'autocomplete' => 'new-password', 'placeholder'=>'••••••••')); ?>
					</div>
                    <?php echo $form->error($model,'admin_password_confirm'); ?>
				</div>
                <div class="settings_form_group">
                    <label class="col-lg-4 control-label text-right">
                        <?php echo Yii::t('base', 'Background') ?>
                    </label>
                    <div class="col-lg-6 element" data-type="file_upload_block">
                        <?php echo $form->hiddenField(
                                $model,
                                'reg_background',
                                [
                                    'class' => 'element',
                                    'data-format' => (implode(',', [FormatImageFileDefinition::JPG, FormatImageFileDefinition::JPEG, FormatImageFileDefinition::GIF, FormatImageFileDefinition::PNG])),
                                    'data-image_size_pixels' => '1920,1080',
                                    'data-type' => 'file',
                                ]); ?>
                        <input class="upload_image_link form-control element" type="text" data-type="file_view" value="<?php echo $model->getRegBackgroundImageTitle() ?>" readonly placeholder="<?php echo Yii::t('base', 'Upload from disk') ?>">
                        <a href="javascript:void(0)" class="underline element <?php echo $model->reg_background ? '' : 'hidden'; ?>" data-type="remove_image_file"><?php echo Yii::t('UsersModule.base', 'Restore default background') ?></a>
                        <div class="sub-format-file-upload">
                            <span><?php echo Yii::t('base', 'Image format') ?>:  <?php echo implode(', ', [FormatImageFileDefinition::JPG, FormatImageFileDefinition::JPEG, FormatImageFileDefinition::GIF, FormatImageFileDefinition::PNG]) ?></span>
                            <span><?php echo Yii::t('base', 'Maximum allowable size') ?>: 1920*1080px, <?php echo HelperIniParams::getPostUploadMaxFileSize(HelperIniParams::UNIT_MEGABYTE, false) . Yii::t('base', 'Mb') ?></span>
                        </div>
                    </div>
                    <?php echo $form->error($model,'reg_background'); ?>
                </div>
            <?php if(\ParamsModel::getValueFromModel('parameters_db_enable')){ ?>
				<div class="col-lg-4 settings_title text-right">
					<?php echo Yii::t('base', 'Database settings'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_type',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'db_type',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_type'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_server_name',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'db_server_name',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_server_name'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_user',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'db_user',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_user'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_password',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->passwordField($model,'db_password',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_password'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_name',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'db_name',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_name'); ?>
				</div>
				<div class="settings_form_group">
					<?php echo $form->labelEx($model,'db_prefix',array('class'=>'col-lg-4 control-label text-right')); ?>
					<div class="col-lg-6">
						<?php echo $form->textField($model,'db_prefix',array('class'=>'form-control')); ?>
					</div>
                    <?php echo $form->error($model,'db_prefix'); ?>
				</div>
            <?php } ?>
			<?php $this->endWidget(); ?>

            <div class="settings_form">
            <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){ ?>
                <div class="settings_form_group">
                    <label class="col-lg-4 control-label text-right"> </label>
                    <div class="col-lg-6">
                        <div class="btn-group">
                            <?php echo CHtml::submitButton(Yii::t('base', 'Save'), array('class'=>'btn btn-primary element', 'data-type' => 'save_form')); ?>
                        </div>
                        <div class="btn-group">
                            <?php echo CHtml::submitButton(Yii::t('base', 'Cancel'), array('class'=>'btn btn-default element', 'data-type' => 'cancel_form')); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>

        </div>
    </div>
</section>
<!--Forms END-->

</div>
