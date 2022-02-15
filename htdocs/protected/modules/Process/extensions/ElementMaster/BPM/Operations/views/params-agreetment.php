<?php
// Связь с задачей
if($type == \Process\models\OperationTaskBaseModel::ELEMENT_SDM_OPERATION_TASK){ ?>
    <span class="element" data-type="button">
        <div class="column">
            <?php echo \CHtml::dropDownList(
                $type,
                $value,
                $this->operation_model->getSDMOperatorTaskDataList($this->operations_model, true),
                array('class'=>'select element', 'data-type'=>$type)
            ); ?>
        </div>
    </span>

<?php }

//Тип согласования
if($type == \Process\models\OperationAgreetmentModel::ELEMENT_TYPE_AGREETMENT){ ?>
    <li class="clearfix form-group inputs-group element" data-type="panel">
        <span class="inputs-label"><?php echo \Yii::t('ProcessModule.base', 'Type agreetment'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php echo \CHtml::dropDownList(
                    $type,
                    $value,
                    \Process\models\OperationAgreetmentModel::getTypeArgetments(),
                    array('class'=>'select element', 'data-type'=>$type)
                ); ?>
            </div>
        </div>
    </li>
<?php }

//E-mail (при Типе согласовани - Внешний)
if($type == \Process\models\OperationAgreetmentModel::ELEMENT_EMAIL){ ?>
    <li class="clearfix form-group inputs-group element" data-type="panel" <?php echo($this->operation_model->typeArgetmentIsExternal() ? '' : 'style="display: none"');  ?>">
        <span class="inputs-label">E-mail</span>
        <div class="columns-section col-1">
            <div class="column">
                <input class="form-control element" type="text" value="<?php echo $value; ?>" data-type="<?php echo $type; ?>">
            </div>
        </div>
    </li>
<?php } ?>

<?php
// Строк выполнениня
if($type == \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME){ ?>
    <span class="element" data-type="button">
        <div class="column execution-time">
            <button class="process_view btn btn-default btn-st add_element_field_type_params_for_button remove" data-toggle="dropdown">
                <?php echo \Yii::t('ProcessModule.base', 'Execution time'); ?>
            </button>
            <ul class="dropdown-menu inputs-block element" data-type="objects">
                <li class="clearfix form-group inputs-group">
                    <span class="inputs-label"><?php echo \Yii::t('ProcessModule.base', 'Days to complete'); ?></span>
                    <div class="columns-section col-1 element" data-type="project_name_block">
                        <div class="column">
                             <?php
                             $attr = array(
                                 'class'=>'form-control element',
                                 'data-type'=>\Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME,
                                 'min'=>0,
                                 'max'=>365
                             );
                             echo \CHtml::numberField(
                                 \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY,
                                 $value[\Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY],
                                 $attr
                             );
                             ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </span>
<?php } ?>
