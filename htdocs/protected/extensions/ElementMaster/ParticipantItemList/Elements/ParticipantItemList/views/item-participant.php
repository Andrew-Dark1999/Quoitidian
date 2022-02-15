<?php $i_data = $this->model->getIData(); ?>
<tr class="sm_extension_data element <?php if($this->model->getIClassAdd()) echo 'add' ?>"
    data-type="<?php echo ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT ?>"
    data-participant_id="<?php echo $i_data['participant_id'] ?>"
    data-ug_id="<?php echo $i_data['ug_id']; ?>"
    data-ug_type="<?php echo $i_data['ug_type']; ?>">
    <td>
        <span class="name"><?php
            echo (new AvatarModel())
                        ->setDataArray($i_data)
                        ->setSrc($this->getSrc())
                        ->getAvatar();

            echo $i_data['full_name'];
        ?></span>
        <?php if($this->model->getShowLinkRemoveParticipant($i_data)){ ?>
            <i class="fa fa-times remove"></i>
        <?php } ?>
    </td>
</tr>
