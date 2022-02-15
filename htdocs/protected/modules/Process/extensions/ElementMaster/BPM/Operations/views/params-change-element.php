<?php
// список обьектов
if($type == \Process\models\OperationChangeElementModel::ELEMENT_OBJECT_NAME){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo $this->operation_model->getElementObjectNameTitle(); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                $attr = array('class'=>'select element', 'data-type'=>$type);
                if($this->getElementsEnabled() == false){
                    $attr['disabled'] = 'disabled';
                }

                echo \CHtml::dropDownList(
                    $type,
                    (!empty($value) ? (is_array($value) ? json_encode($value) : $value) : null),
                    $this->operation_model->getObjectNameList($this->operations_model->unique_index),
                    $attr
                ); ?>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>


<?php
// список связанных модулей
if($type == \Process\models\OperationChangeElementModel::ELEMENT_RELATE_MODULE){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Relate module'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                $attr = array('class'=>'select element', 'data-type'=>$type);
                if($this->getElementsEnabled() == false){
                    $attr['disabled'] = 'disabled';
                }

                $data_list = $this->operation_model->getRelateModuleList(true);
                if($data_list == false){
                    $attr['disabled'] = 'disabled';
                }

                echo \CHtml::dropDownList(
                    $type,
                    (!empty($value) ? $value : null),
                    $data_list,
                    $attr
                ); ?>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>



<?php
// список полей
if($type == \Process\models\OperationChangeElementModel::ELEMENT_FIELD_NAME){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Field name'); ?></span>
        <div class="columns-section col-1">
            <div class="column">
                <?php
                $attr = array('class'=>'select element', 'data-type'=>$type);
                if($this->getElementsEnabled() == false){
                    $attr['disabled'] = 'disabled';
                }

                $data_list = $this->operation_model->getFieldNameList();
                if($data_list == false){
                    $attr['disabled'] = 'disabled';
                }

                echo \CHtml::dropDownList(
                    $type,
                    $value,
                    $data_list,
                    $attr
                ); ?>
                <div class="errorMessage"><?php if(!empty($this->operation_model) && $this->operation_model->getBeError()) echo $this->operation_model->getValidateMessage($type) ?></div>
            </div>
        </div>
    </li>
<?php } ?>



<?php
    // Значение - скаларный тип
    if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR){ ?>
    <li class="clearfix form-group inputs-group dinamic">
        <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Value'); ?><span class="counter"></span></span>
        <div class="columns-section col-1">
            <div class="column">
                <input type="text" class="form-control element" data-type="<?php echo $type; ?>" value="<?php echo $value; ?>">

                <div class="settings crm-dropdown dropdown element" data-type="settings">
                    <a href="javascript:void(0)" class="dropdown-toggle field-param"><i class="fa fa-cog"></i></a>
                    <ul class="dropdown-menu settings-menu" role="menu">
                        <li>
                            <?php
                            $svc = \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_CONDITION;
                            $svv = \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_VALUE;

                            $attr = array('class'=>'select element', 'data-type'=>\Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION);
                            if($this->getElementsEnabled() == false){
                                $attr['disabled'] = 'disabled';
                            }

                            echo \CHtml::dropDownList(
                                \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_CONDITION,
                                (isset($$svc) ? $$svc : null),
                                $this->operation_model->getValueConditionList(),
                                $attr
                            ); ?>
                        </li>
                        <li>
                            <?php
                            $extension_copy = $this->operation_model->getExtensionCopy();
                            $field_name = $this->operation_model->getActiveFieldName();
                            $attr = array();
                            if($this->getElementsEnabled() == false){
                                $attr['disabled'] = 'disabled';
                            }

                            if($extension_copy && $field_name){
                                $schema = null;
                                if(!empty($field_name)){
                                    $schema = $extension_copy->getFieldSchemaForFilter($field_name);
                                }

                                $html = Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
                                    array(
                                        'extension_copy' => $extension_copy,
                                        'schema' => $schema,
                                        'condition_value' => (isset($$svc) ? $$svc : null),
                                        'condition_value_value' => (isset($$svv) ? $$svv : null),
                                        'attr' => $attr,
                                    ), true);

                                if($html){
                                    echo $html;
                                }
                            }
                            if(empty($html)){
                                ?>
                                <input type="text" class="form-control element_filter hide" data-name="condition_value">
                                <?php
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
            <?php if($this->getElementsEnabled()){ ?>
                <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel" ><i class="fa fa-times"></i></a>
            <?php } ?>
        </div>
    </li>
<?php } ?>




<?php
// Значение - date_time тип
if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_DATETIME){ ?>
<li class="clearfix form-group inputs-group dinamic">
    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Value'); ?><span class="counter"></span></span>
    <div class="columns-section col-1">
        <div class="column">
            <input type="text" class="form-control element" data-type="<?php echo $type; ?>" value="<?php echo $value; ?>">

            <div class="settings crm-dropdown dropdown element" data-type="settings">
                <a href="javascript:void(0)" class="dropdown-toggle field-param"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu settings-menu" role="menu">
                    <li>
                        <?php
                        $svc = \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_CONDITION;
                        $svv = \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_VALUE;

                        $attr = array('class'=>'select element', 'data-type'=>\Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_CONDITION);
                        if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                        }

                        echo \CHtml::dropDownList(
                        \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SCALAR_CONDITION,
                        $$svc,
                        $this->operation_model->getValueConditionList(),
                        $attr
                        ); ?>
                    </li>
                    <li>
                        <?php
                        $extension_copy = $this->operation_model->getExtensionCopy();
                        $field_name = $this->operation_model->getActiveFieldName();
                        $attr = array();
                        if($this->getElementsEnabled() == false){
                        $attr['disabled'] = 'disabled';
                        }

                        if($extension_copy && $field_name){
                        $schema = null;
                        if(!empty($field_name)){
                        $schema = $extension_copy->getFieldSchemaForFilter($field_name);
                        }

                        $html = Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
                        array(
                        'extension_copy' => $extension_copy,
                        'schema' => $schema,
                        'condition_value' => $$svc,
                        'condition_value_value' => $$svv,
                        'attr' => $attr,
                        ), true);

                        if($html){
                        echo $html;
                        }
                        }
                        if(empty($html)){
                        ?>
                            <input type="text" class="form-control element_filter hide" data-name="condition_value">
                        <?php
                        }
                        ?>
                    </li>
                </ul>
            </div>
        </div>
        <?php if($this->getElementsEnabled()){ ?>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel" ><i class="fa fa-times"></i></a>
        <?php } ?>
    </div>
</li>
<?php } ?>




<?php
// Значение - СДМ
if(in_array($type, array(\Process\models\OperationChangeElementModel::ELEMENT_VALUE_SELECT, \Process\models\OperationChangeElementModel::ELEMENT_VALUE_RELATE))){ ?>
<li class="clearfix form-group inputs-group dinamic">
    <span class="inputs-label element" data-type="title"><?php echo \Yii::t('ProcessModule.base', 'Value'); ?><span class="counter"></span></span>
    <div class="columns-section col-1">
        <?php if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SELECT){ ?>
        <div class="column">
            <?php } ?>
                <?php
            $extension_copy = $this->operation_model->getExtensionCopy();
            $field_name = $this->operation_model->getActiveFieldName();

            if($extension_copy && $field_name){
            $schema = null;
            if(!empty($field_name)){
            $schema = $extension_copy->getFieldSchemaForFilter($field_name);
            }

            $attr = array();
            if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SELECT){
            $attr = array('class' => 'select element element_filter', 'data-type'=>$type);
            if($this->getElementsEnabled() == false){
            $attr['disabled'] = 'disabled';
            }
            } else if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_RELATE){
            if($this->getElementsEnabled() == false){
            $attr = ['view' => ['button' => ['disabled' => 'disabled']]];
            }
            }


            $html = Yii::app()->controller->widget(\ViewList::getView('ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'),
            array(
            'extension_copy' => $extension_copy,
            'schema' => $schema,
            'condition_value' => \FilterModel::FT_CORRESPONDS,
            'condition_value_value' => $value,
            'attr' => $attr,
            ), true);


            if($html){
            echo $html;
            }
            }

            if(empty($html)){
            echo \CHtml::dropDownList($type, (!empty($value[0]) ? $value[0] : null), array(), array('class'=>'select element element_filter', 'data-name'=>'condition_value', 'data-type'=>$type, 'disabled' => true));
            }
            ?>
                <?php if($type == \Process\models\OperationChangeElementModel::ELEMENT_VALUE_SELECT){ ?>
        </div>
        <?php } ?>
            <?php if($this->getElementsEnabled()){ ?>
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel" ><i class="fa fa-times"></i></a>
        <?php } ?>
    </div>
</li>
<?php } ?>


<?php
// Добавить значение
if($type == \Process\models\OperationChangeElementModel::ELEMENT_LABEL_ADD_VALUE && $this->getElementsEnabled()){ ?>
<li class="clearfix form-group inputs-group add_list dinamic">
    <div class="columns-section col-1">
        <div class="column">
            <div class="operations">
                <a href="javascript:void(0)" class="element" data-type="<?php echo \Process\models\OperationChangeElementModel::ELEMENT_LABEL_ADD_VALUE; ?>"><?php echo \Yii::t('ProcessModule.base', 'Add value'); ?></a>
            </div>
        </div>
    </div>
</li>
<?php } ?>




