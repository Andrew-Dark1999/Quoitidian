<div class="modal-dialog" style="width: 620px;">
    <section class="panel element" data-type="constructor_add_sub_module">
    <header class="panel-heading editable-block">
        <span class="editable-field"><?php echo Yii::t('base', 'Adding a new sabmodule'); ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
    </header>
    <div class="panel-body">
        <div class="panel-body">
            <?php
                if(!empty($module_data)){
            ?>
            <ul class="inputs-submod ui-sortable">
                <li class="clearfix form-group inputs-group">
                    <span class="inputs-label"><?php echo Yii::t('base', 'Available modules'); ?></span>
                    <div class="columns-section col-1">
                        <div class="column">
                            <select class="select" style="display: none;">
                            <?php
                                foreach($module_data as $module){
                                    if($module['template'] == false) $title = $module['title'];
                                    else $title = Yii::t('base', '{s} (templates)', array('{s}' => $module['title']));
                            ?>
                                <option value="<?php echo $module['copy_id'] ?>" data-template="<?php echo $module['template']?>" ><?php echo $title ?></option>
                            <?php
                                }
                            ?>
                            </select>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="buttons-section">
        	<button type="button" class="btn btn-primary constructor_btn-add-submodule"><?php echo Yii::t('base', 'Add')?></button>
            <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        </div>
        <?php } ?>


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



