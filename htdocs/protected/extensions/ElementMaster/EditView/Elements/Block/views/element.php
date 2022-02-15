<!-- block  -->
<div class="panel inputs-panel element <?php if($block_name == 'block_sub_module') echo "table-section" ?>"
    data-type="block"
    data-unique_index="<?php echo $unique_index ?>"
    style="<?php
        if(isset($schema['params']['border_top']) && $schema['params']['border_top'] == false) echo 'border-top : none; ';
        if(isset($schema['params']['edit_view_display']) && $schema['params']['edit_view_display'] == false) echo 'display : none; '; 
        ?>"
>
    <header class="panel-heading editable-block" <?php if(isset($schema['params']['header_hidden']) && (boolean)$schema['params']['header_hidden'] == true) echo 'style="display: none;"'; ?>>
        <?php if($block_name == 'block_sub_module'){ ?>
            <span><?php echo $block_title; ?></span>
        <?php } elseif($block_name == 'block_fields') { ?>
            <span class="editable-field"><?php echo $block_title;?></span>
        <?php } ?>
        <?php if(isset($schema['params']['chevron_down']) && (boolean)$schema['params']['chevron_down'] == true){ ?>
		<span class="tools pull-right">
	        <a href="javascript:;" class="fa <?php if($status = EditViewBuilder::getEvBlockDisplayStatus($unique_index, $block_name)) echo $status; ?> element" data-type="switch"></a>
	    </span>
        <?php } ?>
    </header>

    <?php if(!empty($content)) echo $content; ?>
</div>
<!-- block  END -->