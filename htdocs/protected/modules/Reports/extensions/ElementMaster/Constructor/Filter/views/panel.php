<li class="clearfix form-group inputs-group element" data-type="block_panel" data-unique_index="<?php echo $element['unique_index'] ?>">
    <?php if($element['drag_marker']){?>
        <span class="drag-marker"><i></i></span>
    <?php } ?>
    <div class="columns-section col-1">
        <div class="select-item column">
        <?php
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Filter\Filter',
                               array(
                                'views' => array('module'),
                                'schema' => $schema,
                                'element' => $element,
                               ));
        ?>
        </div>
    </div>
    
    <?php
        \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Filter\Filter',
                               array(
                                'views' => array('field-params'),
                                'schema' => $schema,
                                'element' => $element,
                               ));
    ?>
    <?php if($element['remove']){ ?>
    <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
    <?php } ?>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</li>
