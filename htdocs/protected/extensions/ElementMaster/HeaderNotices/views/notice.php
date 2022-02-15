<li
    class="link<?php if(!empty($data['new']) && $data['new']) { ?> new<?php } ?> notice_navigation_link element"
    data-type="notice"
    data-id="<?php echo $data['history_model']->history_id; ?>"
    data-action_key="<?php echo (!empty($data['block_action_key']) ? $data['block_action_key'] : ''); ?>"
>
    <div class="alert clearfix <?php echo $data['message_data']['ico']; ?>">
        <span class="alert-icon">
            <i class="fa <?php echo $data['message_data']['class_color']; ?>"></i>
        </span>
        <div class="noti-info">
            <b><?php echo $data['message_data']['subject']; ?></b>
            <br />
            <?php echo $data['message_data']['message']; ?>
            <br />
            <i><?php echo DateTimeOperations::getDateTimeOldStr($data['history_model']->date_create); ?></i>
        </div>
    </div>
</li>
