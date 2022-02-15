<?php $i_data = $this->model->getIData(); ?>
<tr class="sm_extension_data element <?php if($this->model->getIClassAdd()) echo 'add' ?>"
    data-type="<?php echo ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL ?>"
    data-participant_email_id="<?php echo $i_data['participant_email_id']; ?>"
    data-email_id="<?php echo $i_data['email_id']; ?>"
>
    <td>
        <span class="name block-email">
            <?php echo (new AvatarModel())->setDataArray($i_data)->getAvatar(); ?>
            <span class="participant-body">
                <span class="title" <?php echo ($i_data['title'] && $i_data['title'] !== null ? 'data-id' : ''); ?>><?php echo $i_data['title']; ?></span>
                <span><?php echo $i_data['email']; ?></span>
            </span>
        </span>
        <?php if($this->model->getILinkRemove()){ ?>
            <i class="fa fa-times remove"></i>
        <?php } ?>

    </td>
</tr>
