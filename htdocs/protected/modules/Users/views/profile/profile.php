<div class="prof_page element" data-type="profile" data-users_id="<?php echo $user_info['user_model']->users_id; ?>">
    <?php  $this->renderPartial('profile-top-block', get_defined_vars()) ?>
    <?php if(!$user_info['read_only']) { ?>
	<section class="panel">
	    <header class="panel-heading tab-bg-dark-navy-blue">
	        <ul class="nav nav-tabs nav-justified ">
	            <li class="<?php if($tab_active == 'activity') echo 'active'; ?>"><a data-toggle="tab" href="#overview"><?php echo Yii::t('UsersModule.base', 'Activity') ?></a></li>
	            <li class="<?php if($tab_active == 'personal_information') echo 'active'; ?>"><a data-toggle="tab" href="#personal_information" class="contact-map"><?php echo Yii::t('UsersModule.base', 'Personal information') ?></a></li>
                <li class="<?php if($tab_active == 'notification_settings') echo 'active'; ?>"><a data-toggle="tab" href="#notification_settings" class="contact-map"><?php echo Yii::t('UsersModule.base', 'Notification settings') ?></a></li>
                <li class="<?php if($tab_active == 'api') echo 'active'; ?>"><a data-toggle="tab" href="#api" class="contact-map"><?php echo Yii::t('UsersModule.base', 'API connection') ?></a></li>
                <!--<li class="<?php //if($tab_active == 'mailing_services') echo 'active'; ?>"><a data-toggle="tab" href="#mailing_services" class="contact-map"><?php //echo Yii::t('UsersModule.base', 'Mailing services') ?></a></li>-->
                <!--<li class="<?php //if($tab_active == 'notification_settings') echo 'active'; ?>"><a data-toggle="tab" href="#settings"><?php //echo Yii::t('UsersModule.base', 'Notification settings') ?></a></li>-->
	        </ul>
	    </header>
	    <div class="panel-body">
	        <div class="tab-content tasi-tab">
                <?php $this->renderPartial('activity', get_defined_vars()); ?>
                <?php $this->renderPartial('profile-personal-information', get_defined_vars()); ?>
                <?php $this->renderPartial('profile-notification-settings', get_defined_vars()); ?>
                <?php $this->renderPartial('profile-api', get_defined_vars()); ?>
                <?php //$this->renderPartial('profile-mailing-services', get_defined_vars()); ?>
                <?php //$this->renderPartial('notice-params'); ?>
	        </div>
	    </div>
	</section>
    <?php } ?>
</div>


<script>
    $('.selectpicker').selectpicker();

    $(document).ready(function(){
        $('#ProfilePersonalInformationModel_password').val('');
        $('#ProfilePersonalInformationModel_password_confirm').val('');

        Profile
            .createInstance()
            .setUrl('<?php echo Yii::app()->createUrl('/module/moduleOverName/module_name/Users/controller/profile/action/activity'); ?>')
            .activityLoadMore();
    })
</script>

<link href="/static/js/iCheck/skins/square/green.css" rel="stylesheet">
<script src="/static/js/iCheck/jquery.icheck.js"></script>
<script src="/static/js/icheck-init.js"></script>
