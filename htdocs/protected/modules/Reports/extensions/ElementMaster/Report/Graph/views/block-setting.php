<span class="edit-dropdown crm-dropdown dropdown report-droptools">
    <a href="javascript:;" class="fa fa-cog dropdown-toggle"></a>
    <ul class="dropdown-menu dropdown-shadow element" data-type="settings">
        <?php 
        if($element['graph_type'] == \Reports\models\ConstructorModel::GRAPH_LINE){ ?>
        <li>
            <select class="selectpicker element" data-type="period">
                <?php 
                    if(!empty($schema['data']['periods']))
                    foreach($schema['data']['periods'] as $period => $title){ 
                ?>
                <option
                    value="<?php echo $period; ?>"
                    <?php if($element['period'] == $period)  echo 'selected="selected"' ?>
                 ><?php echo $title; ?></option>
                <?php } ?>                 
            </select>
        </li>
        <?php } ?>
        
        <?php
            $count = 1;
            if(!empty($element['data_indicators'])){
                $count = count($element['data_indicators']);
                $i = 0;
                foreach($element['data_indicators'] as $indicator){
                    \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Graph\Graph',
                                           array(
                                            'views' => array('block_setting_indicator'),
                                            'schema' => $schema,
                                            'element' => $element,
                                            'select_indicator' => $indicator,
                                            'element_remove' => ($i == 0 ? false : true),
                                           ));
                    $i++;
                }
            } else {
                    \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Graph\Graph',
                                           array(
                                            'views' => array('block_setting_indicator'),
                                            'schema' => $schema,
                                            'element' => $element,
                                           ));
                
            }
        ?>    
        <li>
            <?php 
                $style = '';
                $ic = \ParamsModel::getValueArrayFromModel('graph');
                if($count >= $ic['max_indicators'][$element['graph_type']])
                    $style = 'style="display : none"';
            ?>
            <a href="javascript:void(0)" class="sub-menu-link add-field element" data-type="add_indicator" <?php echo $style; ?>><?php echo \Yii::t('ReportsModule.base', 'Add'); ?></a>
        </li>
    </ul>
</span>
