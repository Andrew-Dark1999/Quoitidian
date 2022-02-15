<?php
foreach($button_actions as $button_action){
    ?>
    <?php if($button_action['name'] == \DropDownListModel::BUTTON_ACTION_ADD_CHANNEL){ ?>
        <div class="link">
            <a href="javascript:void(0)" class="element" data-type="add-channel"><?php echo Yii::t('communications', 'Add chat'); ?></a>
        </div>
    <?php } ?>

    <?php if($button_action['name'] == \DropDownListModel::BUTTON_ACTION_ADD_AUTO){ ?>
        <div class="link">
            <a href="javascript:void(0)" class="element underline hide" data-type="add-auto"><?php echo Yii::t('base', 'Create'); ?><?php echo ($button_action['attr']['span_title'] ? '&nbsp;<span class="title"></span>' : '') ?></a>
        </div>
    <?php } ?>

<?php } ?>

