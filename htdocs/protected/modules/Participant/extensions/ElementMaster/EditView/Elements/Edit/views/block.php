<!-- block FieldType -->
<div class="columns-section col-<?php if(isset($schema['params']['count_edit']) && (integer)$schema['params']['count_edit'] <= 5) echo $schema['params']['count_edit']; else echo 5;  ?>">
    <?php if(!empty($content)) echo $content; ?>
</div>
<!-- block FieldType AND-->