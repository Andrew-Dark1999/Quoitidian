<?php
    $params = get_defined_vars();
    unset($params['js_settings']);

    echo $this->renderPartial('/site/edit-view-task', $params, true);
?>


<script type="text/javascript">
    ProcessObj.BPM.operationParams.setSettings('<?php echo $operation_model->getOperationsModel()->unique_index; ?>', <?php echo json_encode($js_settings); ?>);


    ProcessObj.BPM.operationTaskEnabled = function(){
        var operation_mode = '<?php echo $operation_model->getOperationsModel()->getMode(true); ?>';
        if(operation_mode == '<?php echo \Process\models\OperationsModel::MODE_RUN_BLOCKED ?>'){
            $('.element[data-type="params"][data-module="process"] .edit_view_card_btn-save').attr('disabled', 'disabled');
        }
    }
    ProcessObj.BPM.operationTaskEnabled();

</script>
