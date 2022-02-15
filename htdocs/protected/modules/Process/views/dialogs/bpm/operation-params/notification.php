<?php
    $params = get_defined_vars();
    unset($params['js_settings']);

    echo $this->renderPartial('/site/edit-view-notification-' . $params['vars']['view'], $params, true);
?>


<script type="text/javascript">
    ProcessObj.BPM.operationParams.setSettings('<?php echo $operation_model->getOperationsModel()->unique_index; ?>', <?php echo json_encode($js_settings); ?>);
</script>
