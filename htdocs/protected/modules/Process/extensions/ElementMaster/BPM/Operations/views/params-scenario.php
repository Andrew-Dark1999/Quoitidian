<?php if($type == \Process\models\OperationScenarioModel::ELEMENT_SCRIPT_TEXT){ ?>
    <li class="clearfix form-group inputs-group left-side">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Script'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                $attr = array('id'=> 'code', 'class'=>'select element', 'data-type'=>$type);
                if($this->getElementsEnabled() == false && $this->operations_model->getStatus() == \Process\models\OperationsModel::STATUS_DONE){
                    $attr['disabled'] = 'disabled';
                }

                echo \CHtml::textArea($type, $value, $attr);
                ?>
            </div>
            <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
        </div>
    </li>
<?php } ?>

<?php
    if($type == \Process\models\OperationScenarioModel::ELEMENT_SCRIPT_TYPE){
        $attr = array('class'=>'select element', 'data-type'=>$type);
        echo \CHtml::hiddenField($type, $value, $attr);
    }
?>

