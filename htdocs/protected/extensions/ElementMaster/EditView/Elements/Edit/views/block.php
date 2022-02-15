<!-- block FieldType -->
<div class="columns-section col-<?php if(isset($schema['params']['count_edit']) && (integer)$schema['params']['count_edit'] <= 5) echo (integer)$schema['params']['count_edit'] - count(EditViewBuilder::$relate_module_copy_id_exception) ; else echo 5 - count(EditViewBuilder::$relate_module_copy_id_exception);  ?>">
    <?php if(!empty($content)) echo $content; ?>
</div>
<!-- block FieldType AND-->
