<?php
if($type == \Process\models\NotificationService\NotificationSystemModel::ELEMENT_SDM_OPERATION_TASK){ ?>
    <span class="element" data-type="button">
        <div class="column">
            <?php echo \CHtml::dropDownList(
                $type,
                $value,
                $this->operation_model->getSDMOperatorTaskDataList($this->operations_model),
                array(
                    'class'=>'select element',
                    'data-type'=>$type,
                    'disabled' => ($this->getMode() != \Process\models\OperationsModel::MODE_CONSTRUCTOR ? 'disabled' : ''),
                )
            ); ?>
        </div>
    </span>
<?php } ?>
