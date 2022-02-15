<div class="step3">
	<div class="title">
		<span class="number">3</span>
		<span class="text"><?php echo Yii::t('install', 'Database Configuration'); ?></span>
	</div>

    <?php if(isset($errors) && !empty($errors)){ ?>
    <div class="errorMessage">
        <?php echo implode('. ', $errors); ?> 
    </div>
    <?php } ?>
    
	<form id="formstep3">
        <?php echo CHtml::textField('db_server_name', $model->db_server_name, array('placeholder'=> Yii::t('install', 'Server name'))); ?>
        <?php echo CHtml::error($model, 'db_server_name'); ?>
        
        <?php echo CHtml::textField('db_user', $model->db_user, array('placeholder'=> Yii::t('install', 'Username'))); ?>
        <?php echo CHtml::error($model, 'db_user'); ?>
        
        <?php echo CHtml::passwordField('db_password', $model->db_password, array('placeholder'=> Yii::t('install', 'Password'))); ?>
        <?php echo CHtml::error($model, 'db_password'); ?>
        
        <?php echo CHtml::textField('db_name', $model->db_name, array('placeholder'=> Yii::t('install', 'Database name'))); ?>
        <?php echo CHtml::error($model, 'db_name'); ?>
        
        <?php echo CHtml::textField('db_prefix', $model->db_prefix, array('placeholder'=> Yii::t('install', 'Table prefix'))); ?>
        <?php echo CHtml::error($model, 'db_prefix'); ?>
        
        <label class="checkbox"><?php echo CHtml::checkBox('db_name_create', $model->db_name_create, array('class' => '')); ?><span><?php echo Yii::t('install', 'Create a new database'); ?></span></label>
	</form>

	<button class="next"><?php echo Yii::t('install', 'Install'); ?></button>
 </div>