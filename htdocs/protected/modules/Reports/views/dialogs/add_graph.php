<div class="modal-dialog" style="width: 620px;">
    <section class="panel">
        <header class="panel-heading editable-block">
            <span class="editable-field"><?php echo \Yii::t('ReportsModule.base', 'Adding graphics'); ?></span>
		<span class="tools pull-right">
            <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
	    </span>
        </header>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-blocks ui-sortable">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo \Yii::t('ReportsModule.base', 'Graphics type'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" data-type="graph_type">
                                    <?php foreach(\Reports\models\ConstructorModel::getGraphicsList() as $index => $title){ ?>
                                        <option value="<?php echo $index; ?>"><?php echo $title; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo \Yii::t('ReportsModule.base', 'Arrangement of'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" data-type="position">
                                    <?php foreach(\Reports\models\ConstructorModel::getGraphPosition($graph_count, $positions) as $index => $title){ ?>
                                        <option value="<?php echo $index; ?>"><?php echo $title; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="button" class="btn btn-primary element" data-type="add_graph"><?php echo Yii::t('base', 'Add')?></button>
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