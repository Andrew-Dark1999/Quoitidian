<li
    class="clearfix form-group inputs-group element"
    data-type="block_panel"
    data-data_analysis_type="<?php echo $element['type']; ?>"
    data-unique_index="<?php echo $element['unique_index']; ?>"
>
    <?php
        if($element['type'] == 'data_analysis_param'){ $title = 'Option'; }
        if($element['type'] == 'data_analysis_indicator') { $title = 'Indicator'; }

        if($element['drag_marker']){
    ?>
        <span class="drag-marker"><i></i></span>
    <?php } ?>

    <input type="text" class="main-input form-control element" data-type="title" placeholder="<?php echo \Yii::t('ReportsModule.base', $title); ?>" value="<?php echo $element['title']?>">
            
    <?php 
        \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                               array(
                                'views' => array('module-params'),
                                'schema' => $schema,
                                'element' => $element,
                               ));
        
    ?>
    
    
    <?php if($element['remove']){ ?>
    <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
    <?php } ?>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</li>
