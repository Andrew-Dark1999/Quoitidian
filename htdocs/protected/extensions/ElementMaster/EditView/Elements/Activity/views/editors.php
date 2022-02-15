<?php if($type_comment == ActivityMessagesModel::TYPE_COMMENT_GENERAL){ //Форма ввода сообщения для General ?>
    <div class="message_field" data-type_comment="general">
        <div class="message_upload_btn edit-dropdown crm-dropdown dropdown-right dropdown">
            <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cloud-upload"></i></a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="javascript:void(0)" class="upload_link_activity"><?php echo Yii::t('base', 'Upload file'); ?></a></li>
                <li><a href="javascript:void(0)" class="upload_link_activity_google_doc"><?php echo Yii::t('base', 'Download file from the cloud'); ?></a></li>
            </ul>
        </div>
        <form><textarea rows="5" class="emojis-wysiwyg"></textarea></form>
    </div>
<?php } ?>

<?php if($type_comment == ActivityMessagesModel::TYPE_COMMENT_EMAIL){ //Форма ввода сообщения для Email ?>
    <div class="message_field" data-type_comment="email">
        <div class="message_upload_btn edit-dropdown crm-dropdown dropdown-right dropdown">
            <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cloud-upload"></i></a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="javascript:void(0)" class="upload_link_activity"><?php echo Yii::t('base', 'Upload file'); ?></a></li>
            </ul>
        </div>
        <form>
            <textarea rows="5" class="emojis-wysiwyg"></textarea>
            <?php
                if($this->getBtnShowChannel()){
                    echo $this->getBtnChannelHtml();
                }
            ?>
        </form>
    </div>
<?php } ?>
