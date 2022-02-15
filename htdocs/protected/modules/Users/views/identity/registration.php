<div class="wizz_form_wrapper registration">
    <a href="/login"><img src="/static/images/wizz/logo.png" class="logo_img" /></a>
    <div class="content_form">
        <form action="<?php echo Yii::app()->createUrl('/registration') ?>" method="post">
            <?php echo CHtml::activeTextField($user_model, 'first_name', array('placeholder' => Yii::t('UsersModule.base', 'First name'))); ?>
            <?php echo CHtml::error($user_model, 'first_name'); ?>
            
            <?php echo CHtml::activeTextField($user_model, 'sur_name', array('placeholder' => Yii::t('UsersModule.base', 'Surname'))); ?>
            <?php echo CHtml::error($user_model, 'sur_name'); ?>
            
            <?php echo CHtml::activeTextField($user_model, 'email', array('placeholder' => Yii::t('UsersModule.base', 'Email'))); ?>
            <?php echo CHtml::error($user_model, 'email'); ?>
            
            <?php echo CHtml::activePasswordField($user_model, 'password', array('placeholder' => Yii::t('UsersModule.base', 'password'))); ?>          
            <?php echo CHtml::error($user_model, 'password'); ?>
            
            <button class="next" type="submit"><?php echo Yii::t('UsersModule.base', 'Begin')?></button>
			<a href="<?php echo Yii::app()->createUrl('restore'); ?>"><?php echo Yii::t('UsersModule.base', 'Forgot password')?>?</a>
        </form>
    </div>
</div>


<script>
    $(document).ready(function() {
        //for selects
        $('.selectpicker').selectpicker({style: 'btn-white'});

        //screen adapt
        if ( $(window).height() > $('.wizz_form_wrapper').height() ) {
            $('.wizz_form_wrapper').css('margin-top', ($(window).height()-$('.wizz_form_wrapper').height())/2);
        }
    });
</script>

