<div class="columns-section col-1 element" data-type="module_params" >
    <div class="select-item column">
        <div class="element" data-type="module_copy_id_block">
        <select class="select element first_empty" data-type="module_copy_id">
            <option value=""><?php echo \Yii::t('ReportsModule.base', 'Module name'); ?></option>
            <?php 
                if($element['type'] == 'data_analysis_param'){ $type = 'param'; }
                if($element['type'] == 'data_analysis_indicator') { $type = 'indicator'; }

                if(!empty($schema['data'][$type]['modules']) && ($type == 'param' || 
                   ($type == 'indicator' && ($schema['elements'][0]['type'] != 'data_analysis_param' || ($schema['elements'][0]['type'] == 'data_analysis_param' && $schema['elements'][0]['module_copy_id'] !== null))))
                )
                foreach($schema['data'][$type]['modules'] as $data){ 
            ?>
            <option
                value="<?php echo $data['module_copy_id']; ?>"
                <?php
                 if($data['module_copy_id'] == $element['module_copy_id'])  echo 'selected="selected"' ?>
             ><?php echo $data['title']; ?></option>
            <?php } ?>                 
        </select>
        </div>
        <?php 
            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                                   array(
                                    'views' => array('settings'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
            
        ?>
    </div>
</div>
