<?php
    echo $this->renderPartial('/dialogs/bpm/operation-params/task', get_defined_vars(), true);
?>


<script type="text/javascript">

    ProcessObj.BPM.operationAgreetmrntEnabled = function(){
        var operation_mode = '<?php echo $operation_model->getOperationsModel()->getMode(true); ?>';
        if(operation_mode == '<?php echo \Process\models\OperationsModel::MODE_RUN_BLOCKED ?>'){
            $('.element[data-type="params"][data-module="process"] .edit_view_task_task-approve').attr('disabled', 'disabled');
            $('.element[data-type="params"][data-module="process"] .edit_view_task_task-reject').attr('disabled', 'disabled');
        }
    }
    ProcessObj.BPM.operationAgreetmrntEnabled();

</script>
