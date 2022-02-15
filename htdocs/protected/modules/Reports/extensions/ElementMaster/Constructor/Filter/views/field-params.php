 <!-- Fields -->
<div class="columns-section col-1 filter-box-panel element" data-type="field_params">
    <div class="select-item column">
        <select class="select element" data-type="field_name">
            <?php
            $field_schema_c = null;
            if(!empty($fields_schema)){
                $lich = 0;
                $sel = false;
                foreach($fields_schema['header'] as $field_schema){
                    $lich++;
                    $selected = '';
                    $field_name = explode(',', $field_schema['name']);

                    if($fields_schema['params'][$field_name[0]]['filter_enabled'] == false) continue;
                    if($sel == false && ($element['field_name'] == $field_schema['name'] || $element['field_name'] === null)){
                        $field_schema_c['params']  = $fields_schema['params'][$field_name[0]];
                        $element['field_name'] = $field_schema['name'];
                        $selected  = 'selected="selected"';
                        $sel = true;
                    }
                    ?>
                    <option value="<?php echo $field_schema['name']; ?>" <?php echo $selected; ?>><?php echo ListViewBulder::getFieldTitle(array('title'=>$field_schema['title']) + $fields_schema['params'][$field_name[0]]); ?></option>
                <?php
                }
            } else {
                ?>
                    <option disabled="disabled" value=""><?php echo \Yii::t('ReportsModule.base', 'Field name'); ?></option>
                <?php
            }
            ?>
        </select>



        <!-- Settings -->
        <div class="settings crm-dropdown dropdown element" data-type="settings">
            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>

            <ul class="dropdown-menu settings-menu" role="menu">
                <li class="filter-box-condition">
                    <?php
                    echo \Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterCondition.FilterCondition'),
                        array(
                            'field_schema' => $field_schema_c,
                            'condition_value' => $element['condition'],
                            'destination' => $destination,
                        ),
                        true);
                    ?>
                </li>
                <li class="filter-box-condition-value">
                    <?php
                    echo \Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
                        array(
                            'extension_copy' => $extension_copy,
                            'schema' => $field_schema_c,
                            'condition_value' => $element['condition'],
                            'condition_value_value' => $element['condition_value'],
                            'this_template' => \EditViewModel::THIS_TEMPLATE_MODULE,
                        ),
                        true);
                    ?>
                </li>
            </ul>
        </div>
    </div>
</div>
