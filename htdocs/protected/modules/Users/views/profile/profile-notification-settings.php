<div id="notification_settings" class="tab-pane relative element <?php if(isset($tab_active) && $tab_active == 'notification_settings') echo 'active'; ?>" data-type="profile_notification_settings">
    <div class="profile_form">
        <div class="row">
            <div class="col-lg-12">
                <div class="profile_form_title col-xs-4"><?php echo Yii::t('UsersModule.base', 'Notification settings') ?></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 b_visible">
                <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Sending notifications') ?></label>

                <div class="col-xs-8">
                    <?php
                    echo CHtml::dropDownList(
                                        'setting_notification',
                                        $notification_setting_model->setting_notification,
                                        $notification_setting_model->getSettingNotificationList(),
                                        array(
                                            'class'=>'select form-control element',
                                            'data-field_type' => 'text',
                                            'data-type' =>'setting_notification')) ?>
                    <?php echo CHtml::error($notification_setting_model,  'setting_notification'); ?>
                </div>
            </div>
        </div>
            <?php if($notification_setting_model['setting_notification'] == ProfileNotificationSettingModel::ELEMENT_SN_ENABLED){ ?>

            <div class="row" style="display: none">
                <div class="col-lg-12 b_visible">
                    <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Sending method') ?></label>
                    <div class="col-xs-8">
                        <?php
                        echo CHtml::dropDownList(
                                            'sending_method',
                                            $notification_setting_model->sending_method,
                                            $notification_setting_model->getSendingMethodList(),
                                            array(
                                                'class'=>'select element form-control',
                                                'data-field_type' => 'text',
                                                'data-type' =>'sending_method')) ?>
                        <?php echo CHtml::error($notification_setting_model,  'sending_method'); ?>
                    </div>
                </div>
            </div>

             <?php if($notification_setting_model['sending_method'] == ProfileNotificationSettingModel::ELEMENT_SM_EMAIL){ ?>
            <div class="row">
                <div class="col-lg-12 b_visible">
                    <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Email Notification') ?></label>
                    <div class="col-xs-8">
                        <div class="email-notification">
                            <?php
                            echo CHtml::textField(
                                'email_notification',
                                $notification_setting_model->email_notification,
                                array(
                                    'class'=>'form-control element',
                                    'data-field_type' => 'text',
                                    'data-type' =>'email_notification')) ?>
                            <?php echo CHtml::error($notification_setting_model,  'email_notification'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-lg-12 b_visible">
                    <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Frequency of sending') ?></label>
                    <div class="col-xs-8 b_visible">
                        <?php
                        echo CHtml::dropDownList(
                                            'frequency_sending',
                                            $notification_setting_model->frequency_sending,
                                            $notification_setting_model->getFrequencySendingList(),
                                            array(
                                                'class'=>'select form-control element',
                                                'data-field_type' => 'text',
                                                'data-type' =>'frequency_sending')) ?>
                        <?php echo CHtml::error($notification_setting_model,  'frequency_sending'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 b_visible">
                    <label class="col-xs-4 profile_form_label"><?php echo Yii::t('UsersModule.base', 'Notifications modules') ?></label>
                    <div class="col-xs-8 list_radio_button">
                        <?php
                        echo CHtml::radioButtonList(
                                            'notifications_modules',
                                            $notification_setting_model->notifications_modules,
                                            $notification_setting_model->getNotificationsModulesList(),
                                            array(
                                                'class'=>'element',
                                                'data-field_type' => 'radio',
                                                'data-type' =>'notifications_modules')) ?>
                        <?php echo CHtml::error($notification_setting_model,  'notifications_modules'); ?>
                    </div>
                </div>
            </div>

            <?php
            if($notification_setting_model['notifications_modules'] == ProfileNotificationSettingModel::ELEMENT_NM_COME){
                $nsm_list = $notification_setting_model->getNotificationSettingModulesList();
                if($nsm_list !== false){
            ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-xs-4"></div>
                    <div class="col-xs-8 list-checkbox">
                        <?php
                        foreach($nsm_list as $element){
                            echo CHtml::checkBox(
                                'notifications_module_element' . $element['copy_id'],
                                $element['checked'],
                                array(
                                    'class'=>'element',
                                    'data-type' =>'notifications_module_element',
                                    'data-field_type' => 'check_array',
                                    'value' => $element['copy_id']));
                            echo CHtml::label($element['title'], 'notifications_module_element' . $element['copy_id']);
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php } } ?>
            <?php } ?>
        <div class="row">
            <div class="col-lg-12">
                <label class="col-xs-4"></label>
                <div class="col-xs-8 buttons-section">
                    <button class="btn btn-primary element" data-type="notification_settings_save" type="button"><?php echo Yii::t('UsersModule.base', 'Save') ?></button>
                    <button class="btn btn-default" type="button"><?php echo Yii::t('UsersModule.base', 'Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>