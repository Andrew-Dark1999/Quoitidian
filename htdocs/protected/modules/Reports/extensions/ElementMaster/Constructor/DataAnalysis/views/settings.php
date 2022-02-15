<div class="settings crm-dropdown dropdown element" data-type="settings" >
    <a href="javascript:void(0)" class="dropdown-toggle field-param" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
    <ul class="dropdown-menu settings-menu" role="menu">

<?php if($element['type'] == 'data_analysis_param'){ //data_analysis_param ?>
        <li>
            <?php
                \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                                       array(
                                        'views' => array('setting-param-fields'),
                                        'schema' => $schema,
                                        'element' => $element,
                                       ));
            ?>
        </li>
        <li>
            <select class="select element" data-type="type_date">
                <?php
                if(!empty($type_date)){
                    foreach($type_date as $key => $value){
                        ?>
                        <option value="<?php echo $key; ?>" <?php if($element['type_date'] !== null && $element['type_date'] == $key) echo 'selected="selected"' ?> ><?php echo $value; ?></option>
                        <?php
                    }
                } else {
                    ?>
                    <option disabled="disabled" value=""><?php echo \Yii::t('ReportsModule.base', 'Date type'); ?></option>
                <?php } ?>
            </select>
        </li>

<?php } elseif($element['type'] == 'data_analysis_indicator'){ //data_analysis_indicator ?>
        <li>
            <?php
                \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\DataAnalysis\DataAnalysis',
                                       array(
                                        'views' => array('setting-indicator-fields'),
                                        'schema' => $schema,
                                        'element' => $element,
                                       ));
            ?>
        </li>
        <li>
            <select class="select element" data-type="type_indicator">
                <?php
                if($active_module_copy_id && !empty($type_indicator)){
                    foreach($type_indicator as $key => $value){
                ?>
                    <option value="<?php echo $key; ?>" <?php if($element['type_indicator'] !== null && $element['type_indicator'] == $key) echo 'selected="selected"' ?> ><?php echo $value; ?></option>
                <?php
                    }
                } else {
                ?>
                        <option disabled="disabled" value=""><?php echo \Yii::t('ReportsModule.base', 'Type of Indicator'); ?></option>
                <?php } ?>
            </select>
        </li>
        <li>
            <a href="javascript:void(0)" class="sub-menu-link element" data-type="show_filters"><?php echo Yii::t('ReportsModule.base', 'Configure filter'); ?></a>
        </li>
<?php } ?>

    </ul>
    <ul class="sub-menu hide element element_field_type_params_select" data-type="filter_block_panels">
        <?php
        // filters
        if(!empty($element['filters'])){
            foreach($element['filters'] as $element){
                \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Filter\Filter',
                    array(
                        'views' => array('panel'),
                        'schema' => $schema,
                        'element' => $element,
                    ));

            }
        }
        ?>
        <div class="btn-element">
            <a href="javascript:void(0)" class="sub-menu-link element" data-type="add_filter"><?php echo Yii::t('ReportsModule.base', 'Add filter'); ?></a>
        </div>
    </ul><!-- /.sub-menu -->
</div>
<?php

?>


