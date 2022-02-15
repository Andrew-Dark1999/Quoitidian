<?php
    if(isset($vars['edit_view']['status']) && in_array($vars['edit_view']['status'], array('error', false))){
        echo $vars['edit_view']['messages'];
        return;
    }
    $data = $vars['edit_view'] + array('operation_params' => array('operations_model' => $operation_model->getOperationsModel(), 'content' => $content));
    $data['operations_model'] = $operation_model->getOperationsModel();
    echo $this->renderPartial('//site/edit-view', $data, true);
?>




<script type="text/javascript">
$(document).ready(function() {

    $('.edit-view').closest('section.panel')
        .addClass('element')
        .attr({
            'data-type': 'params',
            'data-module': 'process',
            'data-name': '<?php echo $operation_model->getOperationsModel()->element_name; ?>',
            'data-unique_index': '<?php echo $operation_model->getOperationsModel()->unique_index; ?>'
        });


    $('.element[data-type="params"][data-module="process"] .edit_view_btn-save').addClass('edit_view_data_record_btn-save').removeClass('edit_view_btn-save');


    setTimeout(function(){
        var user_message = '<?php echo Helper::deleteLinefeeds($vars['user_message'], '</br>'); ?>';
        if(user_message != 'false' && user_message !== ''){
            Message.show([{'type':'information', 'message': user_message}], false);
        }
    }, 700);
});
</script>
