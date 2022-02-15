<div class="send_massage_btns">
    <?php if($this->showBtnSwitchTypeComment()){
                echo CHtml::dropDownList(
                    'switch_type_comment',
                    '',
                    $this->getBtnSwitchTypeCommentTitleList(),
                    [
                        'class' => 'element select btn-primary btn',
                        'data-type' => 'switch_type_comment',
                    ]);
    } ?>
    <button type="submit" class="btn btn-primary send_massage_activity" style="display: none;" ><?php echo Yii::t('base', 'Send'); ?></button>
</div>
