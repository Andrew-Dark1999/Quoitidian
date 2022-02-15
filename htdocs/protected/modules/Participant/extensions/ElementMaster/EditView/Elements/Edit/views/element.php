<?php

if ($schema['params']['edit_view_show'] == false) {
    return;
}
if ($schema['params']['type'] == 'numeric') {
    if ($default_data !== null) {
        $data = $default_data;
    } else {
        $data = $extension_data->{$schema['params']['name']};
    }
    ?>
    <div class="column">
        <?php
        $class = ['form-control'];
        if (!empty($schema['params']['add_hundredths'])) {
            $class[] = 'add_hundredths';
        }
        if (!empty($schema['params']['money_type'])) {
            $class[] = 'money_type';
        }

        echo CHtml::textField(
            'EditViewModel[' . $schema['params']['name'] . ']',
            $this->formatNumeric($data),
            [
                'id'    => $schema['params']['name'],
                'class' => implode(' ', $class),
            ]
        );

        ?>

        <?php echo CHtml::error($extension_data, $schema['params']['name']); ?>
    </div>

<?php } ?>

<?php
if ($schema['params']['type'] == 'string' || $schema['params']['type'] == 'display' || $schema['params']['type'] == 'relate_string') {
    if ($default_data !== null) {
        $data = $default_data;
    } else {
        $data = $extension_data->{$schema['params']['name']};
    }

    $attr = ['id' => $schema['params']['name'], 'class' => 'form-control'];
    if (isset($schema['params']['input_attr'])) {
        $attr_tmp = json_decode($schema['params']['input_attr'], true);
        if (!empty($attr_tmp)) {
            $attr += $attr_tmp;
            if (in_array('password', $attr_tmp)) {
                $data = '';
            }
        }
    }
    ?>
    <div class="column">
        <?php
        if (array_key_exists('type', $attr)) {
            $method = $attr['type'] . 'Field';
            echo CHtml::$method('EditViewModel[' . $schema['params']['name'] . ']', $data, $attr);
        } else {
            if (in_array($schema['params']['size'], [FieldTypes::TYPE_SIZE_TEXT, FieldTypes::TYPE_SIZE_MEDIUMTEXT])) {
                echo CHtml::textArea('EditViewModel[' . $schema['params']['name'] . ']', $data, $attr);
            } else {
                echo CHtml::TextField('EditViewModel[' . $schema['params']['name'] . ']', $data, $attr);
            }
        }
        ?>
        <?php echo CHtml::error($extension_data, $schema['params']['name']); ?>
    </div>

<?php } ?>

<?php
if ($schema['params']['type'] == 'file' ||
    $schema['params']['type'] == 'file_image') {
    $data = $extension_data->{$schema['params']['name']};
    if (!empty($data)) {
        if (is_array($data)) {
            $upload_model = UploadsModel::model()->findAll('id in (' . implode(',', $data) . ')');
            $data = '';
        } else {
            $upload_model = UploadsModel::model()->setRelateKey($data)->findAll();
        }
    }
    ?>
    <div class="file-box"
         data-name="<?php echo 'EditViewModel[' . $schema['params']['name'] . ']' ?>"
    >
        <?php
        if (!empty($upload_model)) {
            foreach ($upload_model as $upload_value) {
                echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                    [
                        'schema'         => $schema,
                        'upload_model'   => $upload_value,
                        'extension_copy' => $extension_copy,
                        'extension_data' => $extension_data,
                    ],
                    true);
            }
        } else {
            echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                [
                    'schema'         => $schema,
                    'upload_model'   => null,
                    'extension_copy' => $extension_copy,
                    'extension_data' => $extension_data,
                ],
                true);
        }

        ?>
    </div>
    <?php
}
?>



<?php if ($schema['params']['type'] == 'datetime') {
    if ($default_data !== null) {
        $data = $default_data;
    } else {
        $data = $extension_data->{$schema['params']['name']};
    }
    ?>
    <div class="column">
        <div class="input-group form-datetime" style="float: left; padding-right: 5px;">
            <?php echo CHtml::textField('EditViewModel[' . $schema['params']['name'] . ']',
                (!empty($data) && strtotime($data) ?
                    date(LocaleCRM::getInstance2()->_data_p['dateFormats']['medium'], strtotime($data)) :
                    ''),
                [
                    'class'            => 'form-control date',
                    'data-date-format' => LocaleCRM::getInstance2()->_data_p['dateFormats']['medium_js'],
                    'value'            => Helper::formatDateTimeShort($data),
                ]
            );
            ?>
            <span class="input-group-btn">
                <button type="button" class="btn btn-default date-set"><i class="fa fa-calendar"></i></button>
            </span>
        </div>
        <div class="input-group form-datetime bootstrap-timepicker <?php echo $this->hasTime() == false ? 'hide' : ''; ?>">
            <?php echo CHtml::textField('EditViewModel[' . $schema['params']['name'] . ']',
                (!empty($data) && strtotime($data) ?
                    date(LocaleCRM::getInstance2()->_data_p['timeFormats']['medium'], strtotime($data)) :
                    ''),
                [
                    'class'            => 'form-control time',
                    'data-date-format' => LocaleCRM::getInstance2()->_data_p['timeFormats']['medium_js'],
                    'value'            => $this->hasTime() ? Helper::formatDateTimeShort($data) : '00:00:00',
                ]
            );
            ?>
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
            </span>
        </div>
        <?php echo CHtml::error($extension_data, $schema['params']['name']); ?>
    </div>

<?php } ?>

<?php
if ($schema['params']['type'] == 'logical') {
    $logical = Fields::getInstance()->getLogicalData();
    if (!isset($schema['params']['add_zero_value']) || (boolean)$schema['params']['add_zero_value'] === true) {
        $logical = ['' => ''] + $logical;
    }
    if ($default_data !== null) {
        $data = $default_data;
    } else {
        $data = $extension_data->{$schema['params']['name']};
    }

    ?>
    <div class="column">
        <?php echo CHtml::dropDownList('EditViewModel[' . $schema['params']['name'] . ']', $data, $logical, ['id' => $schema['params']['name'], 'class' => 'select']); ?>
    </div>

<?php } ?>

<?php
if ($schema['params']['type'] == 'select') {
    $select_list = DataModel::getInstance()->setFrom($extension_copy->getTableName($schema['params']['name']))
        ->findAll();

    $select = [];
    foreach ($select_list as $value) {
        $select[$value[$schema['params']['name'] . '_id']] = $value[$schema['params']['name'] . '_title'];
    }

    if (!isset($schema['params']['add_zero_value']) || (boolean)$schema['params']['add_zero_value'] === true) {
        $select = ['' => ''] + $select;
    }

    if ($default_data !== null) {
        $data = $default_data;
    } else {
        $data = $extension_data->{$schema['params']['name']};
    }
    ?>
    <div class="column">
        <?php echo CHtml::dropDownList('EditViewModel[' . $schema['params']['name'] . ']', $data, $select, ['id' => $schema['params']['name'], 'class' => 'select']); ?>
    </div>
<?php } ?>


<?php
if ($schema['params']['type'] == 'access') {
    $select_list = AccessModel::getInstance()->getSelectAccessList();
    $select = [];
    if ($default_data !== null) {
        $id = $default_data['id'];
        $type = $default_data['type'];
    } else {
        $id = $extension_data->{$schema['params']['name']};
        $type = $extension_data->{$schema['params']['name'] . '_type'};
    }
    ?>
    <div class="column">
        <select class="select element_edit_access" name="<?php echo 'EditViewModel[' . $schema['params']['name'] . ']'; ?>" id="<?php echo $schema['params']['name']; ?>">
            <option value="" data-type="" <?php if (empty($id) && empty($type))
                echo 'selected="selected"' ?> ></option>
            <?php
            foreach ($select_list as $value) {
                ?>
                <option value="<?php echo $value['id'] ?>" data-type="<?php echo $value['type'] ?>" <?php if ($id == $value['id'] && $type == $value['type'])
                    echo 'selected="selected"' ?> ><?php echo($value['type'] == 'module' ? $value['title'] : Yii::t('base', $value['title'])) ?></option>
                <?php
            }
            ?>
        </select>
    </div>
<?php } ?>


<?php
/*
    if($schema['params']['type'] == 'permission'){
        $select_list = PermissionModel::getInstance()->getSelectPermissionList();
        if($default_data !== null) $data = $default_data;
            else $data = $extension_data->{$schema['params']['name']};
?>
    <div class="column">
        <?php echo CHtml::dropDownList('EditViewModel['.$schema['params']['name'].']', $data, array('' => '',) + $select_list, array('id'=>$schema['params']['name'], 'class'=>'select')); ?>
    </div>

<?php } */
?>



<?php
if ($schema['params']['type'] == 'relate_this') {
    if (!empty(EditViewBuilder::$relate_module_copy_id_exception) && in_array($schema['params']['relate_module_copy_id'], EditViewBuilder::$relate_module_copy_id_exception)) {
        return;
    }
    $relate_module = ExtensionCopyModel::model()->findByPk($extension_copy->copy_id);
    $select_list = DataModel::getInstance()->setFrom($relate_module->getTableName())->findAll();
    $id = $extension_data->{$schema['params']['name']};
    if ($id) {
        $relate_data = DataModel::getInstance()
            ->setFrom($extension_copy->getTableName())
            ->setWhere($extension_copy->prefix_name . '_id = :id', [':id' => $id])
            ->findAll();
    }
    ?>

    <div class="column">
        <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
            <button
                    name="<?php echo 'EditViewModel[' . $schema['params']['name'] . ']'; ?>"
                    class="btn btn-white dropdown-toggle element element_relate element_relate_this"
                    data-toggle="dropdown"
                    data-type="drop_down_button"
                    data-id="<?php if (!empty($id)) {
                        echo $id;
                    } ?>"
                    data-relate_copy_id="<?php echo $extension_copy->copy_id; ?>"
            >
                <?php if (!empty($relate_data))
                    echo DataValueModel::getInstance()->getRelateValuesToHtml($relate_data[0], $schema['params']) ?>
            </button>

            <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="0"
                    data-relate_copy_id="<?php echo $extension_copy->copy_id; ?>"
                    role="menu"
                    aria-labelledby="dropdownMenu1"
            >
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                </div>

                <div class="submodule-table">
                    <table class="table list-table">
                        <tbody>
                        <?php
                        foreach ($select_list as $value) {
                            ?>
                            <tr class="sm_extension_data" data-id="<?php echo $value[$relate_module->prefix_name . '_id']; ?>">
                                <td>
                                    <span href="javasctript:void(0)" class="name"><?php echo DataValueModel::getInstance()->setFileLink(false)->getRelateValuesToHtml($value, $schema['params']); ?></span>
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
    </div>
<?php } ?>



<?php
if ($schema['params']['type'] == 'relate') {
    $relate_value = [];
    $relate_model = EditViewRelateModel::getInstance()
        ->setVars(get_defined_vars())
        ->prepareVars();

    if (!isset($schema['params']['relate_get_value']) || (boolean)$schema['params']['relate_get_value'] == true) {
        $relate_value = $relate_model->getValue();
    }
    $select_list = $relate_model->getOptionsDataList();
    ?>

    <div class="column">
        <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
            <button <?php echo $relate_model->getRelateDisabledAttr(); ?>
                    name="<?php echo 'EditViewModel[' . $schema['params']['name'] . ']'; ?>"
                    class="btn btn-white dropdown-toggle element element_relate"
                    data-reloader="<?php echo $relate_model->getReloaderStatus(); ?>"
                    data-toggle="dropdown"
                    data-type="drop_down_button"
                    data-id="<?php echo $relate_model->getId(); ?>"
                    data-relate_copy_id="<?php echo $schema['params']['relate_module_copy_id']; ?>"
            >
                <?php echo DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $schema['params']) ?>
            </button>

            <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="<?php echo $relate_model->getIsSetNextOptionListData(); ?>"
                    data-relate_copy_id="<?php echo $schema['params']['relate_module_copy_id']; ?>"
                    role="menu"
                    aria-labelledby="dropdownMenu1"
            >
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                </div>

                <div class="submodule-table">
                    <table class="table list-table">
                        <tbody>
                        <?php
                        foreach ($select_list as $option) {
                            ?>
                            <tr class="sm_extension_data" data-id="<?php echo $option[$relate_model->relate_extension_copy->prefix_name . '_id']; ?>">
                                <td>
                                    <span href="javasctript:void(0)" class="name"><?php echo DataValueModel::getInstance()->setFileLink(false)->getRelateValuesToHtml($option, $schema['params']); ?></span>
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
        <span class="hide m-link element" data-type="m-link">
            <a href="javascript:void(0)" class="modal_dialog sdm_edit_view_dnt-edit"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i></a>
        </span>
    </div>
<?php } ?>







<?php

if ($schema['params']['type'] == 'relate_participant') {
    $select_list = ParticipantModel::getParticipantList(ParticipantModel::PARTICIPANT_UG_TYPE_USER);
    $html = '';

    if (!empty($extension_data)) {
        if ($extension_data->ug_type == ParticipantModel::PARTICIPANT_UG_TYPE_USER) {
            $relate_select_list = DataModel::getInstance()
                ->setFrom('{{users}}')
                ->setWhere('users_id = ' . $extension_data->ug_id)
                ->findAll();

            if (!empty($relate_select_list)) {
                $html = DataValueModel::getInstance()
                    ->setFileLink(false)
                    ->getRelateValuesToHtml($relate_select_list[0], [
                        'relate_field'          => ['sur_name', 'first_name', 'father_name'],
                        'relate_module_copy_id' => 5
                    ]);
            } //берем данные из пользователей
        } elseif ($extension_data->ug_type == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP) {

        }
    }
    ?>
    <div class="column">
        <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
            <button
                    class="btn btn-white dropdown-toggle element element_relate_participant"
                    style="width : 100%"
                    data-toggle="dropdown"
                    data-type="drop_down_button"
                    data-id="<?php if (!empty($extension_data)) {
                        echo $extension_data->participant_id;
                    } ?>"
                    data-participant_id="<?php if (!empty($extension_data)) {
                        echo $extension_data->participant_id;
                    } ?>"
                    data-ug_id="<?php if (!empty($extension_data)) {
                        echo $extension_data->ug_id;
                    } ?>"
                    data-ug_type="<?php if (!empty($extension_data)) {
                        echo $extension_data->ug_type;
                    } ?>"
            ><?php echo $html; ?></button>

            <ul
                    class="dropdown-menu element"
                    data-type="drop_down_list"
                    data-there_is_data="0"
                    data-relate_copy_id="<?php echo \ExtensionCopyModel::MODULE_PARTICIPANT; ?>"
                    role="menu"
                    aria-labelledby="dropdownMenu1"
            >
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                </div>

                <div class="submodule-table">
                    <table class="table list-table">
                        <tbody>
                        <?php
                        foreach ($select_list as $value) {
                            if (ParticipantModel::checkAccessParticipantForModule($extension_copy->copy_id, $value['ug_id']) == false) {
                                continue;
                            }
                            ?>
                            <tr class="sm_extension_data" data-ug_id="<?php echo $value['ug_id']; ?>" data-ug_type="<?php echo $value['ug_type']; ?>">
                                <td>
                                <span href="javasctript:void(0)" class="name"><?php
                                    if ($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER) {
                                        echo DataValueModel::getInstance()
                                            ->setFileLink(false)
                                            ->getRelateValuesToHtml($value, [
                                                'relate_field'          => ['sur_name', 'first_name', 'father_name'],
                                                'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF
                                            ]);
                                    } elseif ($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP) {
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
    </div>
<?php } ?>





