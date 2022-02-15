<li class="element prog-row clearfix sm_extension_data"
    data-type="communications_menu_channel"
    data-controller="edit_view"
    data-render_type="html"
    data-id="<?php echo $communications_model->communications_id; ?>">
    <div class="user-status">
        <?php if(($new_messages_count = $communications_model->getCountNewMessages()) > 0){ ?>
            <?php echo $new_messages_count; ?>
            <i class="fa fa-comments-o">
            </i>
        <?php } ?>
    </div>
    <div class="offset-right">
        <div>
            <span class="title">
                <a href="javascript:void(0)">
                    <?php echo $communications_model->module_title; ?>
                </a>
            </span>
            <p>
                <?php echo $communications_model->getParticipantsCount(); ?>
            </p>
        </div>
    </div>
</li>
