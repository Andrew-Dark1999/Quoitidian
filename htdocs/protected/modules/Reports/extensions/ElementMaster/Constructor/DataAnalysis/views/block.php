<div class="panel-body element" data-type="block_data_analysis">
    <ul class="to-do-list inputs-block ui-sortable element" id="req" data-type="block_panels">
    <?php 
        
        if(!empty($schema['elements']))
        foreach($schema['elements'] as $element){
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                                   array(
                                    'views' => array('panel'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
        }
    ?>         
    </ul>
    <div class="operations">
        <a href="javascript:void(0)" class="add-field-action element" data-type="add_data_analysis_indicator" ><?php echo \Yii::t('ReportsModule.base', 'Add indicator'); ?></a>
        <a href="javascript:void(0)" class="add-field-action element" data-type="add_graph_dialog" style="display: inline;"><?php echo \Yii::t('ReportsModule.base', 'Add chard'); ?></a>
        <a href="javascript:void(0)" class="add-field-action element" data-type="add_indicator_block"   style="<?php if(\Reports\extensions\ElementMaster\ConstructorBuilder::$indicator_block_added) echo 'display : none;'; else echo 'display: inline;' ?>"><?php echo \Yii::t('ReportsModule.base', 'Add a set of indicators'); ?></a>
    </div>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</div>
