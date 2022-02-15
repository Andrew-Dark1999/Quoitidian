<?php
if(in_array($type, array(
    \Process\models\OperationChangeElementModel::ELEMENT_OBJECT_NAME,
    \Process\models\OperationChangeElementModel::ELEMENT_RELATE_MODULE,
    \Process\models\OperationChangeElementModel::ELEMENT_FIELD_NAME))
){
    $this->render('params-change-element', get_defined_vars());
}
?>


<?php
    // Периодичность
    if($type == \Process\models\OperationBeginModel::ELEMENT_PERIODICITY){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?></span>
        <div class="columns-section col-1">
            <div class="column">
                    <?php
                        $attr = array('class'=>'select element', 'data-type'=>$type);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo \CHtml::dropDownList(
                                            $type,
                                            $value,
                                            \Process\models\OperationBeginModel::getParamsDataPeriodicity(),
                                            $attr
                                            );
                    ?>
            </div>
        </div>
    </li>
<?php

    // Дата запуска
    // Периодичность: год
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_DATE){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?> <span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'form-control date element', 'data-type'=>$type, 'autocomplete' =>'off');
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo CHtml::textField($type, $value[0], $attr);
                    ?>
                </div>
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'form-control time element', 'data-type'=>\Process\models\OperationBeginModel::ELEMENT_SUB_TIME, 'autocomplete' =>'off');
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo CHtml::textField(\Process\models\OperationBeginModel::ELEMENT_SUB_TIME, $value[1], $attr);
                    ?>
                </div>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
            </div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>
<?php

    // Периодичность: квартал
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_QUARTER){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?> <span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'select element', 'data-type'=>$type);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo \CHtml::dropDownList(
                            $type,
                            $value[0],
                            array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4'),
                            $attr
                        );
                    ?>
                </div>
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'form-control time element', 'data-type'=>\Process\models\OperationBeginModel::ELEMENT_SUB_TIME);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo CHtml::textField(\Process\models\OperationBeginModel::ELEMENT_SUB_TIME, $value[1], $attr);
                    ?>
                </div>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
            </div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>
<?php

    // Периодичность: месяц
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_DAY_IN_MONTH){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?> <span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'select element', 'data-type'=>$type);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo \CHtml::dropDownList(
                            $type,
                            $value[0],
                            array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14', '15'=>'15', '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19', '20'=>'20', '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24', '25'=>'25', '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29', '30'=>'30', '31'=>'31'),
                            $attr
                        );
                    ?>
                </div>
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'form-control time element', 'data-type'=>\Process\models\OperationBeginModel::ELEMENT_SUB_TIME);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo CHtml::textField(\Process\models\OperationBeginModel::ELEMENT_SUB_TIME, $value[1], $attr);
                    ?>
                </div>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
            </div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>
<?php

    // Периодичность: неделя
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_WEEK){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?> <span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'select element', 'data-type'=>$type);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo \CHtml::dropDownList(
                            $type,
                            $value[0],
                            \DateTimeOperations::getWeeks(),
                            $attr
                        );
                    ?>
                </div>
                <div class="column_half">
                    <?php
                        $attr = array('class'=>'form-control time element', 'data-type'=>\Process\models\OperationBeginModel::ELEMENT_SUB_TIME);
                        if($this->getElementsEnabled() == false){
                            $attr['disabled'] = 'disabled';
                        }

                        echo CHtml::textField(\Process\models\OperationBeginModel::ELEMENT_SUB_TIME, $value[1], $attr);
                    ?>
                </div>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
            </div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>


<?php

    // Периодичность: день
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_TIME){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?> <span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                    $attr = array('class'=>'form-control time element', 'data-type'=>$type);
                    if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                    }

                    echo CHtml::textField($type, $value, $attr);
                ?>
            </div>
            <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
        </div>
    </li>
<?php

    // Часов
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_HOUR){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                    $attr = array('class'=>'form-control element', 'data-type'=>$type, 'min'=>0, 'max'=>23);
                    if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                    }

                    echo CHtml::numberField($type, $value, $attr);
                ?>
            </div>
            <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
        </div>
    </li>
<?php

    // Минут
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_MINUTES){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                    $attr = array('class'=>'form-control element', 'data-type'=>$type, 'min'=>0, 'max'=>59);
                    if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                    }

                    echo CHtml::numberField($type, $value, $attr);
                ?>
            </div>
            <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
        </div>
    </li>
<?php

    // Дней
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_DAYS){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $title; ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                    $attr = array('class'=>'form-control element', 'data-type'=>$type, 'min'=>0, 'max'=>365);
                    if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                    }

                    echo CHtml::numberField($type, $value, $attr);
                ?>
            </div>
            <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type . \Process\models\OperationBeginModel::$_validate_element_count) ?></div>
        </div>
    </li>
<?php

    // Добавить
    } elseif($type == \Process\models\OperationBeginModel::ELEMENT_LABEL_ADD_DATA){ ?>
    <li class="clearfix form-group inputs-group dinamic add_list" data-type>
        <div class="columns-section col-1">
            <div class="column">
                <div class="operations">
                    <a href="javascript:void(0)" class="element" data-type="<?php echo $type; ?>"><?php echo \Yii::t('ProcessModule.base', 'Add date start'); ?></a>
                </div>
            </div>
        </div>
    </li>
<?php } ?>

