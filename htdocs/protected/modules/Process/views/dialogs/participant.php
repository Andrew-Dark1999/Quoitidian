<div class="modal_restricter"></div>
<div class="modal-dialog" style="width: 620px;" data-is-center>
    <section
        class="panel sm_extension"
        data-action="<?php echo $action; ?>"
        data-unique_index="<?php echo $unique_index ?>">

        <header class="panel-heading editable-block">
            <span><?php echo Yii::t('ProcessModule.base', 'Choosing responsible') ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
        </header>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <?php echo $li_html; ?>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="submit" class="btn btn-primary bpm_responsible_save"><?php echo Yii::t('base', 'Save')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
        </div>
    </section>
</div>