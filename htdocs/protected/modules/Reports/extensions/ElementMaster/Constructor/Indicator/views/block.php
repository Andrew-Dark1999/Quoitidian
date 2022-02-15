<div class="panel-body element" data-type="block_panel" style="overflow: hidden; display: block;">
    <div class="reports-params">
    <?php 
        if(!empty($schema['elements']))
        foreach($schema['elements'] as $element){
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Indicator\Indicator',
                                   array(
                                    'views' => array('panel'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
            
        }
    ?>
    </div>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</div>