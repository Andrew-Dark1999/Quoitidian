<?php
    // Тип оператора
    if($type == \Process\models\OperationDataRecordModel::ELEMENT_TYPE_OPERATION){ ?>
        <li class="clearfix form-group inputs-group">
            <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Operation type'); ?></span>
            <div class="columns-section col-1">
                <div class="column">
                    <?php echo \CHtml::dropDownList(
                        $type,
                        $value,
                        \Process\models\OperationDataRecordModel::getTypeOperationsList(),
                        array(
                            'class' => 'select element',
                            'data-type' => $type,
                        )
                    ); ?>
                </div>
            </div>
        </li>
<?php
    }
?>


<?php
// Название модуля
if($type == \Process\models\OperationDataRecordModel::ELEMENT_MODULE_NAME){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Module name'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    \Process\models\OperationDataRecordModel::getModuleNameList(),
                    array(
                        'class' => 'select element',
                        'data-type' => $type,
                    )
                ); ?>
            </div>
        </div>
    </li>
    <?php
}
?>



<?php
// Название записи (текст)
if($type == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Parameter name'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <input type="text" class="form-control element" data-type="<?php echo $type; ?>" value="<?php echo $value; ?>">
            </div>
        </div>
    </li>
<?php } ?>


<?php
// Название записи (список)
if($type == \Process\models\OperationDataRecordModel::ELEMENT_RECORD_NAME_LIST){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Parameter name'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                    echo \CHtml::dropDownList(
                        $type,
                        $value,
                        $this->operation_model->getRecordNameList($this->operations_model->unique_index),
                        array(
                            'class' => 'select element',
                            'data-type' => $type,
                        )
                    );
                ?>
            </div>
        </div>
    </li>
<?php } ?>



<?php
// Вызов Edit view
if($type == \Process\models\OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Call Edit view'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    \Process\models\OperationDataRecordModel::getCallEditViewList(),
                    array(
                        'class' => 'select element',
                        'data-type' => $type,
                    )
                ); ?>
            </div>
        </div>
    </li>
    <?php
}
?>


<?php
// Обязательные поля
if($type == \Process\models\OperationDataRecordModel::ELEMENT_REQUIRED_FIELDS){
    $fields = $this->operation_model->getRequiredFields();
    $html_value = \Yii::t('ProcessModule.base', 'Selected fields') . ': ' . count(explode(',', $value));
?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Required fields'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <select class="select element" data-type="<?php echo $type; ?>" multiple="">
                    <?php foreach($fields as $field_name => $field_value){ ?>
                        <option value="<?php echo $field_name ?>" <?php echo(in_array($field_name, explode(',',$value)) ? 'selected="selected"' : ''); ?> ><?php echo $field_value ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </li>
<?php } ?>


<?php
// Уведомление
if($type == \Process\models\OperationDataRecordModel::ELEMENT_MESSAGE){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label"></span>
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Message to performer'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <textarea class="form-control element" data-type="<?php echo $type ?>" placeholder="<?php echo \Yii::t('ProcessModule.base', 'Message'); ?>" rows="5"><?php echo $value; ?></textarea>
            </div>
        </div>
    </li>
<?php } ?>


<?php
// Элемент выбора значения
if($type == \Process\models\OperationDataRecordModel::ELEMENT_VALUE_VALUE){
    if(!isset($value) || $value === '') $value = null;

    $value_data = $this->operation_model->getValueValue($value, $field_name);
    if(!empty($value_data) && !empty($value_data['content'])){
    ?>
    <div class="column_half">
        <span class="element" data-type="<?php echo \Process\models\OperationDataRecordModel::ELEMENT_VALUE_VALUE ?>" data-field_type="<?php echo $this->operation_model->getFieldType($value_data['field_name']); ?>">
    <?php echo $value_data['content']; ?>
        </span>
    </div>
<?php

    } else {
?>
    <div class="column_half">
        <span class="element" data-type="<?php echo \Process\models\OperationDataRecordModel::ELEMENT_VALUE_VALUE ?>" data-field_type="string">
            <div class="column">
                <input class="form-control" type="text" value="<?php echo (is_array($value) ? '' : $value); ?>">
            </div>
        </span>
    </div>

<?php } ?>
<?php } ?>


<?php
// Значение
if($type == \Process\models\OperationDataRecordModel::ELEMENT_VALUE_BLOCK){
    $fields = $this->operation_model->getRequiredFields();
    if(!isset($value) || $value === '') $value = null;
    ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementValueBlockTitle(); ?> <span class="counter"><?php echo $counter ?></span></span>
        <div class="columns-section col-1 element" data-type="<?php echo \Process\models\OperationDataRecordModel::ELEMENT_VALUE_BLOCK; ?>">
            <div class="column">
                <div class="column_half">
                    <select class="select element" data-type="<?php echo \Process\models\OperationDataRecordModel::ELEMENT_VALUE_FIELD_NAME ?>">
                        <?php foreach($fields as $f_name => $f_value){ ?>
                            <option value="<?php echo $f_name ?>" <?php echo ($f_name==$field_name ? 'selected="selected"' : ''); ?> ><?php echo $f_value ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php

                    \Yii::app()->controller->widget('\Process\extensions\ElementMaster\BPM\Operations\Operations',
                        array(
                            'operation_model' => $this->operation_model,
                            'operations_model' => $this->operations_model,
                            'view_type' => 'params',
                            'element_name' => $this->element_name,
                            'element_schema' => array(
                                'type' => \Process\models\OperationDataRecordModel::ELEMENT_VALUE_VALUE,
                                'value' => $value,
                                'field_name' => $field_name,
                            ),
                        ));

                ?>
            </div>

            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>
<?php } ?>



<?php
// Добавить
if($type == \Process\models\OperationDataRecordModel::ELEMENT_LABEL_ADD_VALUE){ ?>
<li class="clearfix form-group inputs-group add_list" data-type>
    <div class="columns-section col-1">
        <div class="column">
            <div class="operations">
                <a href="javascript:void(0)" class="element" data-type="<?php echo $type; ?>"><?php echo \Yii::t('ProcessModule.base', 'Add value'); ?></a>
            </div>
        </div>
    </div>
</li>
<?php } ?>
