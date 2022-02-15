<?php
 if(empty($schema)){
        echo CHtml::textField('', '', array('disabled'=>'disabled', 'data-name'=>'condition_value', 'class'=>'form-control element_filter'));
        return;
    }

    if(in_array($schema['params']['type'], array(
                                            \Fields::MFT_NUMERIC,
                                            \Fields::MFT_STRING,
                                            \Fields::MFT_FILE,
                                            \Fields::MFT_FILE_IMAGE,
                                            \Fields::MFT_DISPLAY,
                                            \Fields::MFT_DISPLAY_NONE,
                                            \Fields::MFT_RELATE_STRING,
                                            \Fields::MFT_CALCULATED
                                            )
    )){
            $options = array('data-name'=>'condition_value','class'=>'form-control element_filter', 'placeholder'=> Yii::t('base', 'Value'));
            $options = array_merge($options, $attr);
            if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';
            echo CHtml::textField(
                    '',
                    (isset($condition_value_value[0]) ? $condition_value_value[0] : ''),
                    $options
            );
    }
?>

<?php if($schema['params']['type'] == \Fields::MFT_DATETIME){
        if(in_array($condition_value, array(FilterModel::FT_DATE_AFTER, FilterModel::FT_DATE_TO))){
?>
            <div class="date-field">
                <?php
                $options = array(
                    'class' => 'form-control dateinput element_filter',
                    'data-name' => 'condition_value',
                );
                $options = array_merge($options, $attr);
                if(FilterModel::$_access_to_change == false){
                    $options['disabled'] = 'disabled';
                }
                echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ""), $options);
                ?>
            </div>
    <?php } elseif(in_array($condition_value, array(FilterModel::FT_DATE_PERIOD))) { ?>
            <div class="date-field">
            <div class="input-group input-large datepicker-range">
                <?php
                    $options = array('class' => 'form-control dp1 element_filter', 'data-name' => 'condition_value',
                    );
                    $options = array_merge($options, $attr);
                    if(FilterModel::$_access_to_change == false){
                        $options['disabled'] = 'disabled';
                    }
                    echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ""), $options);
                ?>
                <span class="input-group-addon"><?php echo Yii::t('filters', 'To'); ?></span>
                <?php
                $options = array('class' => 'form-control dp2 element_filter', 'data-name' => 'condition_value',
                );
                $options = array_merge($options, $attr);
                if(FilterModel::$_access_to_change == false){
                    $options['disabled'] = 'disabled';
                }
                echo CHtml::textField('', (isset($condition_value_value[1]) ? $condition_value_value[1] : ""), $options);
                ?>
            </div>
            </div>
    <?php } else {
            echo CHtml::textField('', '', array('disabled'=>'disabled', 'data-name'=>'condition_value', 'class'=>'form-control element_filter'));
          } ?>
<?php } ?>


<?php if($schema['params']['type'] == \Fields::MFT_DATETIME_ACTIVITY){
    if(in_array($condition_value, array(FilterModel::FT_DATE_AFTER, FilterModel::FT_DATE_TO))){
        ?>
        <div class="date-field">
            <?php
            $options = array(
                'class' => 'form-control dateinput element_filter',
                'data-name' => 'condition_value',
            );
            $options = array_merge($options, $attr);
            if(FilterModel::$_access_to_change == false){
                $options['disabled'] = 'disabled';
            }
            echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ""), $options);
            ?>
        </div>
    <?php } elseif(in_array($condition_value, array(FilterModel::FT_DATE_PERIOD))) { ?>
        <div class="date-field">
            <div class="input-group input-large datepicker-range">
                <?php
                $options = array('class' => 'form-control dp1 element_filter', 'data-name' => 'condition_value',
                );
                $options = array_merge($options, $attr);
                if(FilterModel::$_access_to_change == false){
                    $options['disabled'] = 'disabled';
                }
                echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ""), $options);
                ?>
                <span class="input-group-addon"><?php echo Yii::t('filters', 'To'); ?></span>
                <?php
                $options = array('class' => 'form-control dp2 element_filter', 'data-name' => 'condition_value',
                );
                $options = array_merge($options, $attr);
                if(FilterModel::$_access_to_change == false){
                    $options['disabled'] = 'disabled';
                }
                echo CHtml::textField('', (isset($condition_value_value[1]) ? $condition_value_value[1] : ""), $options);
                ?>
            </div>
        </div>
    <?php } else {
        echo CHtml::textField('', '', array('disabled'=>'disabled', 'data-name'=>'condition_value', 'class'=>'form-control element_filter'));
    } ?>
<?php } ?>


<?php
    if($schema['params']['type'] == \Fields::MFT_LOGICAL){
        $options = array('data-name'=>'condition_value', 'class'=>'select element_filter');
        if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';

        echo CHtml::dropDownList('',
                                 (isset($condition_value_value[0]) ? $condition_value_value[0] : ''),
                                 array(''=>'') + Fields::getInstance()->getLogicalData(),
                                 $options);
    }
?>


<?php
    if($schema['params']['type'] == \Fields::MFT_SELECT){
        if(in_array($condition_value,  array(FilterModel::FT_BEGIN_WITH, FilterModel::FT_CONTAINS))){
            $options = array('data-name'=>'condition_value', 'class'=>'form-control element_filter', 'placeholder'=>Yii::t('base', 'Value'));
            if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';
            $options = array_merge($options, $attr);
            echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ''), $options);
        }elseif($condition_value == FilterModel::FT_CORRESPONDS || $condition_value == FilterModel::FT_CORRESPONDS_RP){
            $options = array('data-name'=>'condition_value', 'class'=>'select element_filter', 'placeholder'=>Yii::t('base', 'Value'));
            $options = array_merge($options, $attr);

            if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';

            $select_list = DataModel::getInstance()->setFrom($extension_copy->getTableName($schema['params']['name']))
                                               ->findAll();
            $select = array(''=>'');
            foreach($select_list as $value){
                $select[$value[$schema['params']['name'] . '_id']] = $value[$schema['params']['name'] . '_title'];
            }

            echo CHtml::dropDownList('',
                                     (isset($condition_value_value[0]) ? $condition_value_value[0] : ''),
                                     $select,
                                     $options
                                     );
        }

    }
?>


<?php

    if($schema['params']['type'] == \Fields::MFT_DISPLAY_BLOCK){
        if(in_array($condition_value,  array(FilterModel::FT_BEGIN_WITH, FilterModel::FT_CONTAINS))){
            $options = array('data-name'=>'condition_value', 'class'=>'form-control element_filter', 'placeholder'=>Yii::t('base', 'Value'));
            if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';
            echo CHtml::textField('', (isset($condition_value_value[0]) ? $condition_value_value[0] : ''), $options);
        } elseif($condition_value == FilterModel::FT_CORRESPONDS){
            $options = array('data-name'=>'condition_value', 'class'=>'select element_filter', 'placeholder'=>Yii::t('base', 'Value'));
            if(FilterModel::$_access_to_change == false) $options['disabled'] = 'disabled';

            $select_list = $extension_copy->getSchemaBlocksData();
            
            $select = array(''=>'');
            if($select_list) {
                foreach($select_list as $value){
                    $select[$value['unique_index']] = $value['title'];
                }
            }

            echo CHtml::dropDownList('',
                                     (isset($condition_value_value[0]) ? $condition_value_value[0] : ''),
                                     $select,
                                     $options
                                     );
        }

    }
?>


<?php
    if($schema['params']['type'] == \Fields::MFT_RELATE || $schema['params']['type'] == \Fields::MFT_RELATE_THIS){

        $vars = get_defined_vars();

        $ddl_data = \DropDownListModel::getInstance()
            ->setActiveDataType(\DropDownListModel::DATA_TYPE_7)
            ->setVars($vars)
            ->prepareHtml()
            ->getResultHtml();

        if($ddl_data['status'] == false){
            return;
        }
?>
        <div class="column">
            <?php
            echo $ddl_data['html'];
            if($ddl_data['vars']['extension_data']){
                echo \CHtml::error($ddl_data['vars']['extension_data'], $ddl_data['vars']['schema']['params']['name']);
            }
            ?>
        </div>

<?php
    }

    //responsible
    elseif($schema['params']['type'] == \Fields::MFT_RELATE_PARTICIPANT && ($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE ||
                                                                            $schema['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT)){
        ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_ROLES)->getModule(false);

        $ug_type = null;
        if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){
            $ug_type = ParticipantModel::PARTICIPANT_UG_TYPE_USER;
        }

        $select_list = ParticipantModel::getParticipantList($ug_type);

        $html = '';
        
        if(!empty($condition_value_value[0]) && !empty($condition_value_value[1])){ // 0 - ug_id, 1-ug_type
            // user
            if($condition_value_value[1] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                $html_value = DataModel::getInstance()
                                        ->setFrom('{{users}}')
                                        ->setWhere('users_id = ' . $condition_value_value[0])
                                        ->findRow();
                if(!empty($html_value))
                $html = DataValueModel::getInstance()
                                        ->setFileLink(false)
                                        ->getRelateValuesToHtml($html_value, array(
                                            'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                            'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF), false);
            // group
            } elseif($schema['params']['type_view'] == Fields::TYPE_VIEW_BLOCK_PARTICIPANT && $condition_value_value[1] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                $html_value = DataModel::getInstance()
                                        ->setFrom('{{roles}}')
                                        ->setWhere('roles_id = ' . $condition_value_value[0])
                                        ->findRow();
                if(!empty($html_value))
                    $html = DataValueModel::getInstance()
                                        ->setFileLink(false)
                                        ->getRelateValuesToHtml($html_value, array(
                                            'relate_field'=>array('module_title'),
                                            'relate_module_copy_id' => \ExtensionCopyModel::MODULE_ROLES), false);
            }
        }
        
    ?>
         <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
            <button
                class="btn btn-white dropdown-toggle element_relate_participant element_filter element selectpicker"
                   data-name="condition_value"
                   data-toggle="dropdown"
                   data-type="drop_down_button"
                   <?php echo (FilterModel::$_access_to_change ? '' : 'disabled="disabled"'); ?>
                   data-ug_id="<?php echo (!empty($condition_value_value[0]) ? $condition_value_value[0] : '');  ?>"
                   data-ug_type="<?php echo (!empty($condition_value_value[1]) ? $condition_value_value[1] : '');  ?>"
            ><?php echo $html; ?></button>


            <ul class="dropdown-menu element" role="menu" aria-labelledby="dropdownMenu1" data-type="drop_down_list">
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                </div>
                <div class="submodule-table">
                    <table class="table list-table">
                    <tbody>
                    <?php
                        foreach($select_list as $value){
                            if($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                                if(ParticipantModel::checkAccessParticipantForModule($extension_copy->copy_id, $value['ug_id']) == false) continue;
                            } elseif($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                                if(ParticipantModel::checkAccessRoleForModule($extension_copy->copy_id, $value['ug_id']) == false) continue;
                            }
                    ?>
                        <tr class="sm_extension_data" data-ug_id="<?php echo $value['ug_id']; ?>" data-ug_type="<?php echo $value['ug_type']; ?>">
                            <td>
                                <span href="javasctript:void(0)" class="name"><?php
                                if($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                                    echo DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->getRelateValuesToHtml($value, array(
                                                                                'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                                                                'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF), false);
                                } elseif($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                                    echo DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->setAvatarSrc(RolesModel::getAvatarSrc())
                                                ->getRelateValuesToHtml($value, array(
                                                                                'relate_field'=>array('module_title'),
                                                                                'relate_module_copy_id' => \ExtensionCopyModel::MODULE_ROLES), false);

                                }
                                ?></span>
                            </td>
                        </tr>

                    <?php
                        }
                    ?>
                    </tbody>
                    </table>
                </div>
            </ul>
        </div>    
    
    
<?php } ?>    



