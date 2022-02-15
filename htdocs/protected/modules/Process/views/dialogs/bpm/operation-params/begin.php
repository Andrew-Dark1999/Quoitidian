<div class="modal-dialog bpm_modal_dialog">
    <section
        class="panel element"
        data-type="params"
        data-module="process"
        data-name="<?php echo $operation_model->getOperationsModel()['element_name']; ?>"
        data-unique_index="<?php echo $operation_model->getOperationsModel()['unique_index']; ?>"
    >

    <header class="panel-heading editable-block hidden-edit">
        <span class="client-name">
           <span><?php echo \Yii::t('ProcessModule.base', 'Process beginning') ?></span>
        </span>
    </header>

    <div class="panel-body">
        <div class="panel-body">
            <ul class="inputs-block">
                <?php if(\Process\models\ProcessModel::getInstance()->getMode() == \Process\models\ProcessModel::MODE_RUN){ ?>
                <li class="clearfix form-group inputs-group">
                    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Previous process'); ?></span>
                    <div class="columns-section col-1">
                        <div class="column">
                            <?php
                            if(
                                \Process\models\ProcessModel::getInstance()->getMode() == \Process\models\ProcessModel::MODE_RUN &&
                                $operation_model->getOperationsModel()->getStatus() == \Process\models\OperationsModel::STATUS_DONE
                                //\Process\models\ProcessModel::getInstance()->getBStatus() == \Process\models\ProcessModel::B_STATUS_TERMINATED
                            ){
                                $this_template = null;          // шаблоны
                                $not_finished_object = false;   // показывать Заверщенные обьекты
                            } else {
                                $this_template = false;
                                $not_finished_object = true;
                            }



                            echo \CHtml::dropDownList(
                                \Process\models\OperationBeginModel::ELEMENT_PREVIOUS_PROCESS,
                                \Process\models\OperationBeginModel::getParentElement($operation_model->getOperationsModel()->getSchema(), \Process\models\OperationBeginModel::ELEMENT_PREVIOUS_PROCESS)['value'],
                                \Process\models\ProcessModel::getProcessListForOperation($this_template, $not_finished_object),
                                array(
                                    'class'=>'select element',
                                    'data-type'=>\Process\models\OperationBeginModel::ELEMENT_PREVIOUS_PROCESS,
                                    'disabled' => ($operation_model->getOperationsModel()->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR ? '' : 'disabled'),
                                    )
                            ); ?>
                        </div>
                    </div>
                </li>
                <?php } ?>
                <li class="clearfix form-group inputs-group">
                    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Autostart'); ?></span>
                    <div class="columns-section col-1">
                        <div class="column">
                            <?php echo \CHtml::dropDownList(
                                                \Process\models\OperationBeginModel::ELEMENT_START_ON_TIME,
                                                \Process\models\OperationBeginModel::getParentElement($operation_model->getOperationsModel()->getSchema(), \Process\models\OperationBeginModel::ELEMENT_START_ON_TIME)['value'],
                                                \Process\models\OperationBeginModel::getParamsDataStartOnTime(),
                                                array(
                                                    'class'=>'select element',
                                                    'data-type'=>\Process\models\OperationBeginModel::ELEMENT_START_ON_TIME,
                                                    'name'=>'',
                                                    'id'=>'',
                                                    'disabled' => ($operation_model->getOperationsModel()->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR ? '' : 'disabled'),
                                                    )
                                            );
                            ?>
                        </div>
                    </div>
                </li>
                <?php echo $content; ?>
            </ul>
        </div>

        <div class="buttons-section">
            <button type="button" class="btn btn-primary element" data-type="save" <?php echo ($operation_model->getOperationsModel()->getMode() == \Process\models\OperationsModel::MODE_CONSTRUCTOR ? '' : 'disabled'); ?>><?php echo Yii::t('base', 'Save')?></button>
            <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        </div>
    </div>

    <script type="text/javascript">
        ProcessObj.BPM.operationParams.setSettings('<?php echo $operation_model->getOperationsModel()->unique_index; ?>', <?php echo json_encode($js_settings); ?>);
    </script>

</section>
</div>

