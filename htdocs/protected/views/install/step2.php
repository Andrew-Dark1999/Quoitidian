<div class="step2">
	<div class="title">
		<span class="number">2</span>
		<span class="text"><?php echo Yii::t('install', 'Configuration'); ?></span>
	</div>
	<form id="formstep2">
      <?php echo CHtml::textField('crm_name', $model->crm_name, array('placeholder'=> Yii::t('install', 'Company name'))); ?>
      <?php echo CHtml::error($model, 'crm_name'); ?>
      
      <?php echo CHtml::textField('crm_description', $model->crm_description, array('placeholder'=> Yii::t('install', 'Description'))); ?>
      <?php echo CHtml::error($model, 'crm_description'); ?>  
      
      <?php echo CHtml::textField('admin_email', $model->admin_email, array('placeholder'=> Yii::t('install', 'Administrator Email'))); ?>
      <?php echo CHtml::error($model, 'admin_email'); ?>
      
      <?php echo CHtml::passwordField('admin_password', $model->admin_password, array('placeholder'=> Yii::t('install', 'Password'))); ?>
      <?php echo CHtml::error($model, 'admin_password'); ?>      
	</form>
	<button class="next"><?php echo Yii::t('install', 'Continue'); ?></button>
</div>
