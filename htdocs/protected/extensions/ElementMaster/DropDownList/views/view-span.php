<?php //echo $view['entity_model']->getKey() ?>
<?php
    if($view['span_params']['show_sdm_link']){
        $view_attr = '';

        if(!empty($view['span'])){
            foreach($view['span'] as $name => $value){
                $view_attr .= $name . '="' . $value . '" ';
            }
        }
?>
    <a href="javascript:void(0)" <?php echo $view_attr; ?>><span class="text"><?php echo (!empty($view['html_view']) ? $view['html_view'] : '') ?></span></a>

<?php } else { ?>

    <span><?php echo (!empty($view['html_view']) ? $view['html_view'] : '') ?></span>

<?php } ?>
