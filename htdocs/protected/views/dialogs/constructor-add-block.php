<div class="modal-dialog" style="width: 620px;">
    <section class="panel">
        <header class="panel-heading editable-block">
            <span class="editable-field"><?php echo Yii::t('base', 'Adding a new block'); ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
        </header>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-blocks ui-sortable">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo Yii::t('base', 'Available blocks'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select" style="display: none;">
                                        <option value="standard"><?php echo Yii::t('base', 'Standard'); ?></option>
                                        <option value="participant"><?php echo Yii::t('base', 'Participant'); ?></option>
                                        <option value="attachments"><?php echo Yii::t('base', 'Attachments'); ?></option>
                                        <option value="activity"><?php echo Yii::t('base', 'Activity'); ?></option>
                                </select>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="button" class="btn btn-primary constructor_btn-add-blocks"><?php echo Yii::t('base', 'Add')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">

    // sortable-list
    $( ".inputs-block" ).sortable({
        connectWith: ".inputs-block",
        dropOnEmpty: true
    });


    $('.select').selectpicker({
        style: 'btn-white',
        noneSelectedText: '<?php echo Yii::t('messages', 'None selected'); ?>'
    });

</script>