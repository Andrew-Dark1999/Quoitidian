<div class="reports-params element"
    data-type="block"
    data-element_type="<?php echo $schema['type']; ?>"
    data-unique_index="<?php echo $schema['unique_index'] ?>"
>

    <?php 
        if(!empty($schema['elements']))
        foreach($schema['elements'] as $element){
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Indicator\Indicator',
                                   array(
                                    'views' => array('panel'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
            
        }
    ?>
</div>