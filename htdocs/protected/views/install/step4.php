<div class="step4">
	<div class="title">
		<span class="number">4</span>
		<span class="text"><?php echo Yii::t('install', 'Ready'); ?></span>
	</div>
    <div class="install_ready"><?php echo Yii::t('install', 'Installation completed successfully'); ?></div>
    <a class="next gotomain" href="/"><?php echo Yii::t('install', 'Go to main page'); ?></a>
    
    <?php if(isset($errors) && !empty($errors)){ ?>
    <br /><br /><br />
    <div class="errorMessage1" style="color: red;">
        <?php echo implode('. ', $errors); ?> 
    </div>
    <?php } ?>

</div>