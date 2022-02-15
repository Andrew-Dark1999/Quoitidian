<div class="panel-body element" data-type="block_filter">
    <ul class="to-do-list inputs-block ui-sortable element" data-type="block_panels" id="req">    
    <?php 
        if(!empty($schema['elements']))
        foreach($schema['elements'] as $element){ 
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Filter\Filter',
                                   array(
                                    'views' => array('panel'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
        }
    ?>         
    </ul>
    <div class="operations">
        <a href="javascript:void(0)" class="add-field-action element" data-type="add_filter"><?php echo \Yii::t('ReportsModule.base', 'Add filter'); ?></a>
    </div>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</div>