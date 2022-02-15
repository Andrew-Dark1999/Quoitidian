<div class="wizz_form_wrapper locked">
    <a href="/login"><img src="/static/images/wizz/logo.png" class="logo_img" /></a>
    
    <div class="content_form">
        <p>
            <?php echo Yii::t('UsersModule.base', 'Unfortunately, your account has been locked.'); ?>
        </p>
        
        <p>
            <?php echo Yii::t('UsersModule.base', 'To unlock, you need to contact the system administrator.'); ?>
        </p>
    </div>
</div>

<script>
    $(document).ready(function() {
        //screen adapt
        if ( $(window).height() > $('.wizz_form_wrapper').height() ) {
            $('.wizz_form_wrapper').css('margin-top', ($(window).height()-$('.wizz_form_wrapper').height())/2);
        }
    });
</script>
