<div class="panel-body" style="overflow: hidden; display: block;">
    <div class="task_comments example_comments">
        <div class="task_message">
            <div class="message_field">
                <div class="message_upload_btn edit-dropdown crm-dropdown dropdown-right dropdown">
                    <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cloud-upload"></i></a>
                </div>
                <div class="emoji-wysiwyg-editor"></div>
                <a href="javascript:void(0)" class="emoji-button" title="Emojis"></a>
            </div>
        </div>
        <div class="comments_block">
            <div class="user_comment">
                <div class="user_comment_pic">
                    <img src="/static/images/avatar-mini.jpg" alt="">
                </div>
                <div class="user_comment_right">
                    <div class="user_comment_box">
                        <div class="user_comment_name"><?php echo Yii::t('base', 'Nombre de usuario'); ?></div>
                        <div class="user_comment_text"><?php echo Yii::t('base', 'Mensaje '); ?></div>
                    </div>
                    <div class="user_comment_info">
                        <span class="user_comment_date">01 <?php echo Yii::t('base', 'January'); ?> <?= Yii::t('base', 'in')?> 00:00</span>
                    </div>
                </div>
                <div class="user_comment_adds">
                    <div class="col-xs-6">
                        <div class="file-block" data-type="file">
                            <div class="upload-result" style="display: block">
                                <span class="file_thumb file_text">txt</span>
                                <div class="filedata">
                                    <div class="filename">Archivo.txt</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="file-block" data-type="file">
                            <div class="upload-result" style="display: block">
                                <span class="file_thumb file_image">txt</span>
                                <div class="filedata">
                                    <div class="filename">Archivo.txt</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<span class="element" data-type="block_activity">
    <input type="hidden" class="element_params" data-type="name" value="<?php echo $schema['params']['name']; ?>" />
    <input type="hidden" class="element_params" data-type="type" value="<?php echo $schema['params']['type']; ?>" />
</span>
