<?php
    foreach($button_actions as $button_action){
?>
    <?php if($button_action['name'] == \DropDownListModel::BUTTON_ACTION_ADD){ ?>
            <span class="add hide"><i class="fa fa-plus-circle" aria-hidden="true"></i></span>
    <?php } ?>

    <?php if($button_action['name'] == \DropDownListModel::BUTTON_ACTION_REMOVE){ ?>
            <span class="remove hide"><i class="fa fa-times" aria-hidden="true"></i></span>
    <?php } ?>

    <?php if($button_action['name'] == \DropDownListModel::BUTTON_ACTION_EDIT){ ?>
            <span class="edit hide"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></span>
    <?php } ?>
<?php } ?>
