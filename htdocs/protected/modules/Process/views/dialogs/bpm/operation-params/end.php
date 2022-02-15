<div class="modal-dialog bpm_modal_dialog">
    <section
        class="panel element"
        data-type="params"
        data-module="process"
        data-name="<?php echo $operation_model->getOperationsModel()['element_name']; ?>"
        data-unique_index="<?php echo $operation_model->getOperationsModel()['unique_index']; ?>"
    >

    <header class="panel-heading editable-block hidden-edit">
        <span class="client-name">
           <span><?php echo \Yii::t('ProcessModule.base', 'End process') ?></span>
        </span>
        </header>

        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <?php echo $content; ?>
                </ul>
            </div>

            <div class="buttons-section">
                <button type="button" class="btn btn-primary element" data-type="save" <?php echo ($operation_model->getOperationsModel()->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR ? '' : 'disabled'); ?>><?php echo Yii::t('base', 'Save')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
        </div>

        <script type="text/javascript">
            ProcessObj.BPM.operationParams.setSettings('<?php echo $operation_model->getOperationsModel()->unique_index; ?>', <?php echo json_encode($js_settings); ?>);
        </script>

    </section>
</div>

