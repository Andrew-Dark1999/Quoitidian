<div class="wizz_form_wrapper locked">
    <a href="/login"><img src="/static/images/wizz/logo.png" class="logo_img" /></a>

    <div class="content_form">
        <p>
            <?php echo Yii::t('UsersModule.base', 'At the moment, the server is carrying out<br>technical work'); ?>.
        </p>
        <p>
            <?php echo Yii::t('UsersModule.base', 'The system will be available soon'); ?>.
        </p>
        <p>
            <?php echo Yii::t('UsersModule.base', 'We apologize for the temporary inconvenience'); ?>
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
