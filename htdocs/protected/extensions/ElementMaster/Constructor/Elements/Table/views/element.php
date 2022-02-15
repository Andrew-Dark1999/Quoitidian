<!-- Table -->
    <?php
    if(isset($schema['params']['count_table_column'])){
        $count_table_column = $schema['params']['count_table_column'];
        if($count_table_column > 5) $count_table_column = 5; 
    }
    else $count_table_column = 4;
    ?>
    <div class="columns-section no-main col-<?php echo $count_table_column; ?> element" data-type="table">
        <?php echo $content; ?>
    </div><!-- /.columns-section -->
<!-- Table END -->
			                			                