<?php

if(in_array($type, array(
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_SENDER_NAME,
))){ ?>
    <li class="clearfix form-group inputs-group element" data-type="service_vars">
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementsLabelTitle($type) ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <input type="text" class="form-control element" data-type="<?php echo $type; ?>" value="<?php echo $value; ?>">
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>



<?php
if(in_array($type, array(
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_RECIPIENT_TYPE,
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_OBJECT_NAME,
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_MODULE_NAME,
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FIELD_NAME,
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_MESSAGE_TEMPLATE,
))){
    $vars = array(
            'type' => $type,
            'value' => $value,
    );
?>
    <li class="clearfix form-group inputs-group element" data-type="service_vars">
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementsLabelTitle($type) ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    $this->operation_model->getOptionList($type, false, $vars),
                    array(
                        'class' => 'select element',
                        'data-type' => $type,
                    )
                ); ?>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>


<?php
if(in_array($type, array(
    \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_MESSAGE_TEXT))){ ?>
    <li class="clearfix form-group inputs-group element" data-type="service_vars">
        <span class="inputs-label"></span>
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementsLabelTitle($type) ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <textarea class="form-control element" data-type="<?php echo $type ?>" placeholder="<?php echo \Yii::t('ProcessModule.base', 'Message'); ?>" rows="5"><?php echo $value; ?></textarea>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>




<?php
if($type == \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FILTER_FIELD_NAME){ ?>
    <li class="clearfix form-group inputs-group element" data-type="service_vars">
        <span class="inputs-label"></span>
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementsLabelTitle($type) ?><span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    $this->operation_model->getOptionList($type),
                    array(
                        'class' => 'select element',
                        'data-type' => $type,
                    )
                ); ?>

                <div class="settings crm-dropdown dropdown element" data-type="settings">
                    <a href="javascript:void(0)" class="dropdown-toggle field-param"  data-toggle="dropdown" style="right: 35px"><i class="fa fa-cog"></i></a>
                    <ul class="dropdown-menu settings-menu" role="menu">
                        <li>
                            <?php
                            $svc = \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FFN_CONDITION;
                            $svv = \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FFN_CONDITION_VALUE;

                            echo \CHtml::dropDownList(
                                \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FFN_CONDITION,
                                $$svc,
                                $this->operation_model->getValueConditionList($value),
                                array('class'=>'select element', 'data-type'=>\Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FFN_CONDITION)
                            ); ?>
                        </li>
                        <li>
                            <input type="text" class="form-control element" data-type="<?php echo \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_FFN_CONDITION_VALUE; ?>" value="<?php echo $$svv; ?>">
                        </li>
                    </ul>
                </div>
                <div class="errorMessage"><?php
                        $key = \Process\models\NotificationService\NotificationUnisenderModel::$active_filter_fn_index;
                        if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type. (\Process\models\NotificationService\NotificationUnisenderModel::$active_filter_fn_index));
                        \Process\models\NotificationService\NotificationUnisenderModel::$active_filter_fn_index++;
                    ?></div>
            </div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel" ><i class="fa fa-times"></i></a>
        </div>
    </li>

<?php } ?>


<?php
// Добавить значение
if($type == \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_LABEL_ADD_FILTER /*&& $this->operations_model->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR*/){ ?>
    <li class="clearfix form-group inputs-group add_list">
        <div class="columns-section col-1">
            <div class="column">
                <div class="operations">
                    <a href="javascript:void(0)" class="element" data-type="<?php echo \Process\models\NotificationService\NotificationUnisenderModel::ELEMENT_LABEL_ADD_FILTER; ?>"><?php echo \Yii::t('ProcessModule.base', 'Add filter'); ?></a>
                </div>
            </div>
        </div>
    </li>
<?php } ?>
