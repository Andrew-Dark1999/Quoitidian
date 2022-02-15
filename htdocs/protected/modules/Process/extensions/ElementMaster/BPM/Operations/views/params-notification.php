<?php
// Тип сообщения
if($type == \Process\models\OperationNotificationFactoryModel::ELEMENT_TYPE_MESSAGE){ ?>
    <li class="clearfix form-group inputs-group">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Message type'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    $this->operation_model->getTypeMessagesList(),
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
// Название сервиса отправки
if($type == \Process\models\OperationNotificationFactoryModel::ELEMENT_SERVICE_NAME){ ?>
    <li class="clearfix form-group inputs-group" style="display: none">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Sending service'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    $this->operation_model->getServiceNameList(),
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

