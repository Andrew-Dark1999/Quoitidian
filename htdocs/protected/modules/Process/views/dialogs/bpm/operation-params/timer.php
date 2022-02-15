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
            <span class="editable-field element_data" data-type="module_title"><?php echo \Process\models\SchemaModel::getInstance()->getOperationTitleByUniqueIndex($operation_model->getOperationsModel()['unique_index']); ?></span>
                <span class="todo-actionlist actionlist-inline">
                    <span class="edit-dropdown crm-dropdown title-edit dropdown-right dropdown">
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <input type="text" class="form-control element" data-type="module_title" value="" autofocus />
                            </li>
                            <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save')?></a></li>
                        </ul>
                    </span>
                </span>
        </span>
        </header>

        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Start on time'); ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <?php echo \CHtml::dropDownList(
                                    \Process\models\OperationTimerModel::ELEMENT_START_ON_TIME,
                                    \Process\models\OperationTimerModel::getParentElement($operation_model->getOperationsModel()->getSchema(), \Process\models\OperationTimerModel::ELEMENT_START_ON_TIME)['value'],
                                    \Process\models\OperationTimerModel::getParamsDataStartOnTime(),
                                    array(
                                        'class'=>'select element',
                                        'data-type'=>\Process\models\OperationTimerModel::ELEMENT_START_ON_TIME,
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

