<?php
    if($this->module->list_view_layout === true){
        $this->render('//site/process-view', get_defined_vars());
    } else {
        $this->renderPartial('//site/process-view', get_defined_vars());
    }
?>


<?php
    if(Yii::app()->controller->module->getProcessViewBtnSorting() == false){
?>

<script type="text/javascript">
    ProcessViewBase.init();
</script>

<?php } ?>
