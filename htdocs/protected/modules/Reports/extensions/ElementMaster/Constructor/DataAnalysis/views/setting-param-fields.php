<?php
    // fields
    $isset_field = false;
    $options = '';
    $s_field_name = '';

    if(!empty($schema['data']['param']['modules'])){
        foreach($schema['data']['param']['modules'] as $module){
            if($element['module_copy_id'] === null) continue;
            if(empty($module['fields'])) continue;
            if($element['module_copy_id'] !== null && $module['module_copy_id'] != $element['module_copy_id']) continue;
            $isset_field = true;
            foreach($module['fields'] as $field){
                $s = '';
                if($field['field_name'] == $element['field_name']){
                    $s = ' selected="selected"';
                    $s_field_name = $element['field_name'];
                }
                $options .= '<option value="' . $field['field_name'] . '"' . $s . '>' . $field['title'] . '</option>';
            }
        }
    }

    if($isset_field == false){
        $fields_array = \Reports\models\ConstructorModel::getVirtualFieldsPeriods();
        foreach($fields_array as $field){
            $options .= '<option value="' . $field['field_name'] . '">' . $field['title'] . '</option>';
        }
        ?>

    <?php } ?>

    <select class="select element" data-type="field_name" data-selected="<?php echo $s_field_name; ?>">
        <?php echo $options; ?>
    </select>
