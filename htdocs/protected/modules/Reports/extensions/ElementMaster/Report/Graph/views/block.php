<div class="row element"
    data-type="block"
    data-element_type="<?php echo $schema['type']; ?>"
    data-unique_index="<?php echo $schema['unique_index'] ?>"
    data-position="<?php echo $schema['elements'][0]['position']; ?>"
>
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <?php echo $schema['title']; ?>
            <span class="tools pull-right">
                <!--a href="javascript:;" class="fa fa-chevron-down"></a-->
                <?php 
                
                    \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Graph\Graph',
                                           array(
                                            'views' => array('block_setting'),
                                            'schema' => $schema,
                                            'element' => $schema['elements'][0],
                                           ));
                ?>
             </span>
            </header>
            <div class="panel-body">
                <?php 
                    if(!empty($schema['elements']))
                    foreach($schema['elements'] as $element){
                        \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Graph\Graph',
                                               array(
                                                'views' => array('graph_element'),
                                                'schema' => $schema,
                                                'element' => $element,
                                               ));
                    }
                ?>
            </div>
        </section>
    </div>
</div>
