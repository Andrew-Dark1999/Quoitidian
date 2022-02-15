<span class="participant dropdown crm-dropdown dropdown-right element"
      data-type="email"
      data-participant_email_id="<?php echo $this->participant_data['participant_email_id'] ?>"
      data-email_id="<?php echo $this->participant_data['email_id'] ?>"
>
    <?php
        echo (new AvatarModel())
                ->setDataArray($this->participant_data)
                ->setAttr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
                ->getAvatar();
    ?>
        <span class="participant-body">
            <span class="title" <?php echo ($this->showEmailParticipantTitleAttr() ? 'data-id' : ''); ?>><?php echo $this->participant_data['title']; ?></span>
            <span><?php echo $this->participant_data['email']; ?></span>
        </span>

    <ul class="dropdown-menu" role="menu">
        <table class="list-table">
            <tbody>
                <tr>
                    <td>
                        <span class="name block-email"><?php
                                echo (new AvatarModel())
                                        ->setDataArray($this->participant_data)
                                        ->getAvatar();
                            ?>
                            <span class="participant-body">
                                <span class="title" <?php echo ($this->showEmailParticipantTitleAttr() ? 'data-id' : ''); ?>><?php echo $this->participant_data['title']; ?></span>
                                <span><?php echo $this->participant_data['email']; ?></span>
                            </span>
                        </span>
                        <i class="fa fa-times remove"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </ul>
</span>
