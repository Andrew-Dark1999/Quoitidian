<select class="select element" data-type="field_name">
    <?php
        // fields
        $isset_field = false;
        if(
            $element['module_copy_id'] !== null && !empty($schema['data']['indicator']['modules']) &&
            ($schema['elements'][0]['type'] != 'data_analysis_param' || ($schema['elements'][0]['type'] == 'data_analysis_param' && $schema['elements'][0]['module_copy_id'] !== null))
        )
        foreach($schema['data']['indicator']['modules'] as $module){
            if(empty($module['fields'])) continue;
            if($module['module_copy_id'] != $element['module_copy_id']) continue;
            $isset_field = true;
            $extension_copy = \ExtensionCopyModel::model()->findByPk($module['module_copy_id']);
            foreach($module['fields'] as $field){
                $num = true;
                if($field['field_name'] != \Reports\models\ConstructorModel::PARAM_FIELD_NAME_AMOUNT){
                    $params = $extension_copy->getFieldSchemaParams($field['field_name']);
                    if($params['params']['type'] != 'numeric') $num = false;
                }

    ?>
    <option
        value="<?php echo $field['field_name']; ?>"
        data-num="<?php echo (integer)$num ?>"
        <?php
            if($field['field_name'] == $element['field_name']){
                echo 'selected="selected"';
            }  
        ?>
     ><?php echo $field['title']; ?></option>
     <?php
            }
            $first_record = false;
        }
        if($isset_field == false){
    ?>
        <option disabled="disabled" value=""><?php echo \Yii::t('ReportsModule.base', 'Field name'); ?></option>
    <?php } ?>

</select>



