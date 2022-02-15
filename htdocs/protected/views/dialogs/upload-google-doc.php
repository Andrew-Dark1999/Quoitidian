<div class="modal-dialog" style="width: 620px;">
    <section class="panel" data-copy_id="<?php if(isset($copy_id)) echo $copy_id; ?>">
    <header class="panel-heading editable-block">
        <span class="editable-field"><?php echo  Yii::t('messages', 'Download file from the cloud') ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
    </header>
    <div class="panel-body">
        <div class="panel-body">
            <ul class="inputs-submod ui-sortable">
                <li class="clearfix form-group inputs-group">
                    <span class="inputs-label goodoc-txt"><?php echo  Yii::t('messages', 'File link') ?></span>
                    <div class="columns-section col-1 goodoc-inp">
                        <div class="column">
                            <input type="text" />
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="buttons-section">
        	<button type="button" class="btn btn-primary activity_btn-add-google-doc"><?php echo Yii::t('base', 'Add')?></button>
            <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        </div>
    </div>
</section>
</div>
      
      