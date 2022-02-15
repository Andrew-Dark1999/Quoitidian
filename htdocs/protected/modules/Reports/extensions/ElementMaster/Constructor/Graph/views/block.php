<div class="panel-body element_block_panel element" data-type="block_panel" style="overflow: hidden; display: block;">
    <div class="element" data-type="graph">
    <?php 
        if(!empty($schema['elements']))
        foreach($schema['elements'] as $element){
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Graph\Graph',
                                   array(
                                    'views' => array('graph_element'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
        }
    ?>
    </div>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</div>