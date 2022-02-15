<span class="participant dropdown crm-dropdown dropdown-right element <?php if($this->participant_data['responsible']) echo 'active'; ?>"
      data-type="participant"
      data-participant_id="<?php if(!isset($_POST['from_template']) || (isset($_POST['from_template']) && (boolean)$_POST['from_template'] == false)) echo $this->participant_data['participant_id'] ?>"
      data-ug_id="<?php echo $this->participant_data['ug_id'] ?>"
      data-ug_type="<?php echo $this->participant_data['ug_type'] ?>"
      data-responsible="<?php echo (integer)$this->participant_data['responsible'] ?>"
>
    <?php
        echo (new AvatarModel())
                ->setDataArray($this->participant_data)
                ->setAttr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
                ->setSrc($this->getSrc())
                ->getAvatar();
    ?>
    <ul class="dropdown-menu" role="menu">
        <table class="list-table">
            <tbody>
                <tr>
                    <td>
                        <span class="name"><?php
                            echo (new AvatarModel())
                                    ->setDataArray($this->participant_data)
                                    ->setSrc($this->getSrc())
                                    ->getAvatar();

                            echo $this->participant_data['full_name'];
                        ?></span>
                        <?php if($this->getShowLinkRemoveParticipant()){ ?>
                            <i class="fa fa-times remove"></i>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
            <?php if($this->getShowLinkResponsible()){ ?>
                <a href="javascript:" class="make-responsible <?php if($this->participant_data['responsible']) echo 'hide'; ?>"><?php echo Yii::t('base', 'Make responsible'); ?></a>
                <a href="javascript:" class="remove-responsible <?php if(!$this->participant_data['responsible']) echo 'hide'; ?>"><?php echo Yii::t('base', 'Remove responsible'); ?></a>
            <?php } ?>
    </ul>
</span>
