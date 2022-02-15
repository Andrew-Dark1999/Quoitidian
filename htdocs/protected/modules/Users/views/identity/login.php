<div class="wizz_form_wrapper login">
    <a href="/login"><img src="/static/images/wizz/logo.png" class="logo_img" /></a>
    <?php if(Yii::app()->user->hasFlash('success')):?>
        <div class="flash-success">
            <?php echo Yii::app()->user->getFlash('success'); ?>
        </div>
    <?php endif; ?>
    <div class="content_form">
        <form action="<?php echo Yii::app()->createUrl('login'); ?>" method="post">
            <?php
                if($user_model->hasErrors()){
                    $errors = $user_model->getErrorsList();
                ?>
                <div class="errorMessage">
                    <span><?php echo implode(' ', $errors);?></span>
                </div>
                <?php
                }
            ?>
            <?php echo CHtml::activeTextField($user_model, 'email', array('placeholder' => Yii::t('UsersModule.base', 'Email'))); ?>
            <?php //echo CHtml::error($user_model, 'email'); ?>
            
            <?php echo CHtml::activePasswordField($user_model, 'password', array('placeholder' => Yii::t('UsersModule.base', 'Contraseña'))); ?>
            <?php //echo CHtml::error($user_model, 'password'); ?>
    
            <button class="next" type="submit"><?php echo Yii::t('UsersModule.base', 'Ingresar')?></button>
            <div class="row">
                <div class="col-md-5"></div>
                <div class="col-md-7 text-right"><a href="<?php echo Yii::app()->createUrl('restore'); ?>"><?php echo Yii::t('UsersModule.base', '¿Olvidaste tu contraseña?')?></a></div>
            </div>


        </form>
    </div>
</div>