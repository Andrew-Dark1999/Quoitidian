<div class="wizz_form_wrapper password">
    <a href="/login"><img src="/static/images/wizz/logo.png" class="logo_img" /></a>
    <div class="content_form">
        <form action="<?php echo Yii::app()->createUrl('restore-password-change'); ?>" method="post">
            <?php if(Yii::app()->user->hasFlash('success')): ?>
                <div class="errorMessage message-email"><span><?php echo Yii::app()->user->getFlash('success'); ?></span></div>
            <?php endif; ?>
            <?php if(Yii::app()->user->hasFlash('error')): ?>
                <div class="errorMessage message-email"><span><?php echo Yii::app()->user->getFlash('error'); ?></span></div>
            <?php endif; ?>
            <input type="hidden" name='token' value="<?php echo $token ?>">
            <input type="hidden" name="email" value="<?php echo $email ?>" placeholder="<?php echo Yii::t('UsersModule.base', 'Enter your e-mail address')?>">
            <input type="password" name="password"  value="<?php echo $password??'' ?>" placeholder="<?php echo Yii::t('UsersModule.base', 'Password')?>">
            <input type="password" name="confirm_password" value="<?php echo $confirmPassword??'' ?>"  placeholder="<?php echo Yii::t('UsersModule.base', 'Confirm password')?>">

<!--            --><?php //echo CHtml::activeTextField($restorePasswordModel, 'token', array('placeholder' => Yii::t('UsersModule.base', 'Enter your e-mail address'))) ?>
<!--            --><?php //echo CHtml::activeTextField($user_model, 'email', array('placeholder' => Yii::t('UsersModule.base', 'Enter your e-mail address'))); ?>
<!--            --><?php //if($user_model->hasErrors('email')){ ?>
<!--                <div class="errorMessage message-email"><span>--><?php //echo $user_model->getError('email'); ?><!--</span></div>-->
<!--            --><?php //} ?>
            <button class="next" type="submit"><?php echo Yii::t('UsersModule.base', 'Send')?></button>

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

<style>
    .errorMessage.message-email{
        background: transparent!important;
        padding: 0;
        border-radius: 0px;
        left:0!important;
        width: 100%;
    }

    .errorMessage.message-email span{
        background: #fff!important;
        padding: 5px;
        display: inline-block;
        color: rgb(253, 93, 93);
        border-radius: 5px;
    }

    .content_form .errorMessage.message{
        width: 100%;
        background: transparent;
    }
    .content_form .message div{
        width: 410px;
        margin: 0 auto;
        background: white;
        padding: 5px;
        border-radius: 5px;
    }
</style>