<?php if($type == \Process\models\OperationAndModel::ELEMENT_NUMBER_BRANCHES){ ?>
<li class="clearfix form-group inputs-group">
    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Number of branches'); ?></span>
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
                array('2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10'),
                $attr
            ); ?>
        </div>
    </div>
</li>
<?php } ?>
