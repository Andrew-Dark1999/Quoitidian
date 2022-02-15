<div
    class="participants-block element"
    data-type="block_participant"
    data-ug_id="<?php echo WebUser::getUserId(); ?>"
    data-block_type="edit_view"
    data-change_responsible="<?php echo (\ParticipantModel::getChangeResponsible($this->extension_copy, $this->extension_copy_data, true) ? "1" : "0"); ?>"
>
    <div class="participants element" data-type="drop_down">
        <span class="element" data-type="block-card">
            <?php echo $this->content; ?>
        </span>
        <?php
            echo $this->getBlockParticipantSelectedHtml();
        ?>
    </div>
</div>




