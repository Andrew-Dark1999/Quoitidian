<div class="buttons-block element" data-type="block_button">
    <?php echo $content; ?>

    <?php if($this->getGroupButtonsIndex() == \Process\extensions\ElementMaster\EditViewBuilderForCard::GROUP_BUTTON_SAVE){ ?>
        <span class="element" data-type="button">
            <button type="submit" class="btn btn-primary edit_view_card_btn-save"><?php echo \Yii::t('base', 'Save'); ?></button>
        </span>

    <?php } elseif($this->getGroupButtonsIndex() == \Process\extensions\ElementMaster\EditViewBuilderForCard::GROUP_BUTTON_APPROVE){ ?>
        <span class="element" data-type="button">
            <button type="submit" class="btn btn-primary edit_view_task_task-approve" data-name="agreetment"><?php echo \Yii::t('ProcessModule.base', 'Approve'); ?></button>
        </span>
        <span class="element" data-type="button">
            <button type="submit" class="btn btn-danger edit_view_task_task-reject"><?php echo \Yii::t('ProcessModule.base', 'Reject'); ?></button>
        </span>
    <?php } ?>

</div>
