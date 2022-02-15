<!-- block FieldType -->
<div class="columns-section col-<?php if(isset($schema['params']['count_edit']) && (integer)$schema['params']['count_edit'] <= 5) echo $schema['params']['count_edit']; else echo 5;  ?> element_block_field_type element" data-type="block_field_type">
    <?php if(!empty($content)) echo $content; ?>
</div>
<!-- block FieldType AND-->