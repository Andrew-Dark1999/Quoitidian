<?php
if($type == \Process\models\OperationEndModel::ELEMENT_NEXT_PROCESS){ ?>
<li class="clearfix form-group inputs-group">
    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Next process'); ?></span>
    <div class="columns-section col-1">
        <div class="column">
        <?php
            //echo $this->operation_model->getNextProcessHtml($value);
            if(
                \Process\models\ProcessModel::getInstance()->getMode() == \Process\models\ProcessModel::MODE_RUN &&
                $this->operations_model->getStatus() == \Process\models\OperationsModel::STATUS_DONE
                //\Process\models\ProcessModel::getInstance()->getBStatus() == \Process\models\ProcessModel::B_STATUS_TERMINATED
            ){
                $this_template = null;
            } else {
                $this_template = true;
            }

            echo \CHtml::dropDownList(
                $type,
                $value,
                \Process\models\ProcessModel::getProcessListForOperation($this_template),
                array(
                    'class'=>'select element',
                    'data-type'=>$type,
                    'disabled' => ($this->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR ? '' : 'disabled'),
                )

            );

        ?>
        </div>
    </div>
</li>
<?php } ?>
