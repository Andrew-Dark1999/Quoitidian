<!-- FieldType params -->
<ul class="dropdown-menu settings-menu element_field_type_params element" data-type="field_type_params" role="menu">
    <?php if (isset($params['type']) && (($params['type'] == 'numeric') || ($params['type'] == 'string') || ($params['type'] == 'relate_string'))) {
        echo '<li>' .
            CHtml::textField('',
                (isset($params['default_value']) ? $params['default_value'] : ''),
                [
                    'class'       => 'form-control element_params',
                    'placeholder' => Yii::t('base', 'valor predefinido'),
                    'data-type'   => 'default_value',
                ]
            )
            . '</li>';
    } ?>

    <?php if (isset($params['type']) && $params['type'] == 'datetime' && $params['type_view'] != Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
        echo '<li><div class="input-group form-datetime">' .
            CHtml::textField('',
                (isset($params['default_value']) ? date(LocaleCRM::getInstance2()->_data_p['dateFormats']['medium'], strtotime($params['default_value'])) : ''),
                [
                    'class'       => 'form-control date element_params',
                    'placeholder' => Yii::t('base', 'Default date value'),
                    'data-type'   => 'default_value',
                ]
            )
            . '<span class="input-group-btn">
                <button type="button" class="btn btn-default date-set"><i class="fa fa-calendar"></i></button>
              </span>
            </div></li>';

        echo '<li><div class="input-group form-datetime bootstrap-timepicker">' .
            CHtml::textField('',
                (isset($params['default_value']) ? date(LocaleCRM::getInstance2()->_data_p['timeFormats']['medium'], strtotime($params['default_value'])) : ''),
                [
                    'class'       => 'form-control time element_params',
                    'placeholder' => Yii::t('base', 'Default time value'),
                    'data-type'   => 'default_value',
                ]
            )
            . '<span class="input-group-btn">
                <button class="btn btn-default" type="button"><i class="fa fa-clock-o"></i></button>
            </span>
        </div></li>';
    } ?>

    <?php if (isset($params['type']) && $params['type'] == 'logical') {
        $logical = ['' => ''] + Fields::getInstance()->getLogicalData();
        echo '<li>' .
            CHtml::dropDownList('',
                (isset($params['default_value']) ? $params['default_value'] : ''),
                $logical,
                [
                    'id'        => 'qwer',
                    'class'     => 'form-control element_params',
                    'data-type' => 'default_value',
                ]
            )
            . '</li>';
    } ?>

    <?php if (isset($params['type']) && $params['type'] == 'select') {
        if (empty($params['values'])) {
            echo '<li>' . CHtml::dropDownList('', '', [], ['class' => 'form-control element_params', 'data-type_element' => 'select', 'data-type' => 'default_value']) . '</li>';
        } else {
            $options = ['' => ''] + $params['values'];
            echo '<li>' .
                CHtml::dropDownList('',
                    (isset($params['default_value']) ? $params['default_value'] : ''),
                    $options,
                    [
                        'class'             => 'form-control element_params',
                        'data-type'         => 'default_value',
                        'data-type_element' => 'select',

                    ]
                )
                . '</li>';
        }
    }
    ?>


    <?php if (isset($params['type']) && $params['type'] == 'file_image') {
        echo '<li>' .
            CHtml::dropDownList('',
                (isset($params['file_thumbs_size']) ? $params['file_thumbs_size'] : ''),
                UploadsModel::model()->getThumbsSizeForDropDown(),
                [
                    'class'     => 'form-control element_params',
                    'data-type' => 'file_thumbs_size',
                ]
            )
            . '</li>';
    } ?>


    <?php if (isset($params['type']) && ($params['type'] == 'relate' || $params['type'] == 'relate_this' || $params['type'] == 'relate_string')) {

        $exception_copy_condition = '';
        $change_constructor = '1';

        if (!empty($exception_copy_id) && $params['type'] == 'relate') {
            $exception_copy_condition = ' AND copy_id not in(' . implode(',', $exception_copy_id) . ')';
        }

        if ($params['type'] == 'relate_this' && $params['relate_module_copy_id']) {
            $exception_copy_condition .= ' AND copy_id = ' . $params['relate_module_copy_id'];
            $change_constructor = ExtensionCopyModel::model()->findByPK($params['relate_module_copy_id'])->constructor;
        }

        $html_params = [
            'class'     => 'form-control element_params',
            'data-type' => 'relate_module_copy_id',
        ];

        $modules = ExtensionCopyModel::model()->changeConstructor($change_constructor)
            ->findAll(
                [
                    'condition' => '(`schema` != "" OR `schema` is not NULL) ' . $exception_copy_condition
                ]
            );

        $exclude_ids = [];

        if ($params['type'] == 'relate_string') {

            $html_params['class'] .= ' ' . $params['type'];
            $html_params['data-copy_id'] = $params['relate_module_copy_id'];

            foreach ($modules as $value) {

                if (ExtensionCopyModel::MODULE_REPORTS == $value->copy_id) {
                    continue;
                }

                $primary = $value->getPrimaryField();

                if (!empty($primary) && $primary['params']['type'] == 'relate_string') {

                    if (!isset($params['relate_module_copy_id']) || $primary['params']['relate_module_copy_id'] != $params['relate_module_copy_id']) {
                        $exclude_ids[] = $primary['params']['relate_module_copy_id'];
                    }

                }
            }

            if (!empty($exclude_ids)) {
                $exclude_ids = array_unique($exclude_ids);
            }
        }

        $relate_module_copy_id = [];
        $first_copy_id = null;

        if (!empty($modules)) {
            foreach ($modules as $value) {

                if ($params['type'] == 'relate_string' && !empty($exclude_ids)) {

                    if (in_array($value['copy_id'], $exclude_ids)) {
                        continue;
                    }

                }

                if ($first_copy_id === null) {
                    $first_copy_id = $value['copy_id'];
                }

                $relate_module_copy_id[$value['copy_id']] = $value['title'];
                if (isset($params['relate_module_copy_id']) && $params['relate_module_copy_id'] == $value['copy_id']) {
                    $first_copy_id = $value['copy_id'];
                }
            }
        }

        echo '<li>' .
            CHtml::dropDownList('relate_module_copy_id',
                (isset($params['relate_module_copy_id']) ? $params['relate_module_copy_id'] : ''),
                $relate_module_copy_id,
                $html_params
            )
            . '</li>';

        if ($params['type'] == 'relate' || $params['type'] == 'relate_this') {

            $model = ExtensionCopyModel::model()->findByPk($first_copy_id)->setAddDateCreateEntity(false);
            $sub_module_schema_parse = $model->getSchemaParse([], [], [], false);

            $params_ = SchemaConcatFields::getInstance()
                ->setSchema($sub_module_schema_parse['elements'])
                ->setWithoutFieldsForListViewGroup($model->getModule(false)->getModuleName())
                ->parsing()
                ->primaryOnFirstPlace()
                ->prepareWithConcatName()
                ->getResult();

            $fields = [];
            if (!empty($params_['header'])) {
                foreach ($params_['header'] as $aField) {
                    $fields[$aField['name']] = $aField['title'];
                }
            }

            echo '<li>' .
                CHtml::dropDownList('relate_field',
                    $params['relate_field'],
                    $fields,
                    [
                        'class'     => 'form-control element_params',
                        'data-type' => 'relate_field',
                    ]
                )
                . '</li>';
        }

    } ?>


    <?php if (isset($params['type']) && $params['type'] == Fields::MFT_DATETIME && $params['type_view'] != Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
        echo '<li>' .
            CHtml::dropDownList('',
                (isset($params['type_view']) ? $params['type_view'] : ''),
                [
                    Fields::TYPE_VIEW_DT_DATETIME => Yii::t('base', 'Date and time'),
                    Fields::TYPE_VIEW_DT_DATE     => Yii::t('base', 'Date'),
                ],
                [
                    'class'             => 'form-control element_params',
                    'data-type'         => 'type_view',
                    'data-type_element' => 'select',
                ]
            )
            . '</li>';
    }
    ?>


    <?php if ($params['type_view'] != Fields::TYPE_VIEW_BUTTON_RESPONSIBLE && $params['type_view'] != Fields::TYPE_VIEW_BUTTON_DATE_ENDING) { ?>
        <li class="<?php if ($params['type'] == 'relate_string' || $params['type'] == 'auto_number') {
            echo 'hidden';
        } ?>"><input type="text" class="form-control element_params"
                     placeholder="<?php echo Yii::t('base', 'Field name in the DB'); ?>"
                     data-type="name"
                     value="<?php if (!empty($field_attr['field_name'])) {
                         echo $field_attr['field_name'];
                     } else {
                         if ($params['name']) {
                             echo $params['name'];
                         }
                     } ?>"></li>


        <?php if (isset($params['type'])) { ?>
            <li <?php if ($params['type'] == 'display_block' || $params['type'] == 'auto_number') {
                echo 'style="display: none;"';
            } ?> >
                <div class="checkbox">
                    <label><input type="checkbox" class="element_params" data-type="required" <?php if ($params['required'] && (bool)$params['required'] === true) {
                            echo 'checked="checked"';
                        } ?> ><?php echo Yii::t('base', 'Campo requerido'); ?></label>
                </div>
            </li>
        <?php } ?>
    <?php } ?>

    <?php if ($params['type'] == 'string') { ?>
        <li>
            <div class="checkbox">
                <label><input type="checkbox" class="element_params" data-type="size" <?php if (isset($params['size']) && $params['size'] == \FieldTypes::TYPE_SIZE_TEXT) {
                        echo 'checked="checked"';
                    } ?> ><?php echo Yii::t('constructor', 'Campo de texto extenso'); ?></label>
            </div>
        </li>
    <?php } ?>

    <?php if (in_array($params['type_view'], [Fields::TYPE_VIEW_BUTTON_RESPONSIBLE])) { ?>
        <li class="element_params">
            <input
                    type="text"
                    class="form-control element_params"
                    placeholder="<?php echo Yii::t('base', 'Field title'); ?>"
                    data-type="title"
                    value="<?php echo $params['title'] ?? Yii::t('base', 'Responsible'); ?>">
        </li>
    <?php } ?>
    <?php if (!in_array($params['type'], ['display', 'display_block', 'auto_number'])) { ?>
        <li>
            <div class="checkbox">
                <label><input type="checkbox" class="element_params" data-type="read_only" <?php if (!empty($params['read_only'])) {
                        echo 'checked="checked"';
                    } ?> ><?php echo Yii::t('constructor', 'Campo de solo lectura'); ?></label>
            </div>
        </li>
    <?php } ?>


    <?php if ($params['type'] == Fields::MFT_NUMERIC) { ?>
        <li>
            <div class="checkbox">
                <label><input type="checkbox" class="element_params" data-type="money_type" <?php if (!empty($params['money_type'])) {
                        echo 'checked="checked"';
                    } ?> ><?php echo Yii::t('constructor', 'Money type'); ?></label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label><input type="checkbox" class="element_params" data-type="add_hundredths" <?php if (!empty($params['add_hundredths'])) {
                        echo 'checked="checked"';
                    } ?> ><?php echo Yii::t('constructor', 'Add hundredths of a share'); ?></label>
            </div>
        </li>
    <?php } ?>









    <?php if ($params['type'] == 'calculated') { ?>
        <li>
            <div class="calculate-field">
                <div class="" data-type="name-of-fields">
                    <label><?php echo Yii::t('constructor', 'Numeric fields'); ?>:</label>
                </div>
                <input type="text" class="form-control element element_params" data-type="formula"
                       data-old-value="<?php if (!empty($params['formula'])) {
                           echo $params['formula'];
                       } ?>"
                       value="<?php if (!empty($params['formula'])) {
                           echo $params['formula'];
                       } ?>">
            </div>
        </li>
    <?php } ?>

    <?php if ($params['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING || $params['type_view'] == Fields::TYPE_VIEW_BUTTON_RESPONSIBLE || $params['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS) { ?>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="element_params" data-type="process_view_group" <?php if (isset($params['process_view_group']) && (bool)$params['process_view_group'] == true) {
                        echo 'checked="checked"';
                    } ?> />
                    <?php echo Yii::t('base', 'Sorting in the ProcessView'); ?>
                </label>
            </div>
        </li>
    <?php } ?>

    <?php if (isset($params['type']) && $params['type'] == 'select'): ?>
        <li><a href="javascript:void(0)" class="sub-menu-link show_element_field_type_params_select"><?php echo Yii::t('base', 'Setting list'); ?></a></li>
    <?php endif; ?>



    <?php if (isset($params['type']) && $params['type'] == 'auto_number') {
        if (empty($extension_copy)) {
            $extension_copy = ExtensionCopyModel::model()->findByPK($extension_copy_id);
        }
        $gen_params = Fields::getInstance()->getNameGenerationParams($extension_copy);

        ?>
        <li class="auto_number default hidden">
            <select class="form-control set_auto_number_field">
                <?php
                foreach ($gen_params[0] as $key => $value) {
                    ?>
                    <option value="<?php echo $value['name']; ?>"><?php echo $value['title']; ?></option>
                <?php } ?>
                <option value="sm"><?php echo Yii::t('constructor', 'Related module'); ?></option>
            </select>
            <a href="javascript:void(0)" class="auto_number_remove" data-element="select"><i class="fa fa-times"></i></a>
        </li>

        <div class="dropdown_params_auto_number">
            <a href="javascript:void(0)" class="sub-menu-link add_autonumber_field"><?php echo Yii::t('base', 'Add'); ?></a>
            <span class="module_auto_number_dropdown crm-dropdown dropdown hidden">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                      <select class="form-control module_auto_number_select" name="">
                        <?php
                        foreach ($gen_params[1] as $key => $value) {
                            ?>
                            <option value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                        <?php } ?>
                      </select>
                    </li>
                      <?php
                      foreach ($gen_params[1] as $key => $value) {
                          ?>
                          <li>
                        <select class="form-control field_auto_number_select" name="" select-module-id="<?php echo $value['id']; ?>">
                          <?php
                          foreach ($value['fields'] as $key => $value) {
                              ?>
                              <option type="<?php echo $value['type']; ?>" value="<?php echo $value['name']; ?>"><?php echo $value['title']; ?></option>
                          <?php } ?>
                        </select>
                      </li>
                      <?php } ?>
                </ul>
            </span>

            <span class="static_text_auto_number_dropdown crm-dropdown dropdown hidden">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle"><i class="fa fa-cog"></i></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                    <input class="form-control" type="text" name="static_text" value="">
                    </li>
                </ul>
            </span>
        </div>

        <?php
        $params['name_generate'] = htmlspecialchars($params['name_generate'], ENT_QUOTES, 'UTF-8');
    } ?>

    <?php // подолнительные параметры в скрытых aтрибутах ?>
    <input type="hidden" class="element_params" data-type="display" value="<?php if (isset($params['display'])) {
        echo (integer)$params['display'];
    } else {
        '1';
    } ?>"/>
    <input type="hidden" class="element_params" data-type="c_types_list_index" value="<?php if (isset($field_attr['c_types_list_index'])) {
        echo $field_attr['c_types_list_index'];
    } elseif (isset($params['c_types_list_index'])) {
        echo $params['c_types_list_index'];
    } else {
        echo Fields::TYPES_LIST_INDEX_DEFAULT;
    } ?>"/>
    <input type="hidden" class="element_params" data-type="is_primary" value="<?php if (isset($field_attr['is_primary'])) {
        echo $field_attr['is_primary'];
    } elseif (isset($params['is_primary'])) {
        echo (integer)$params['is_primary'];
    } else {
        echo '0';
    } ?>"/>
    <input type="hidden" class="element_params" data-type="edit_view_show" value="<?php if (isset($params['edit_view_show'])) {
        echo (integer)$params['edit_view_show'];
    } else {
        echo '1';
    } ?>"/>
    <input type="hidden" class="element_params" data-type="c_load_params_btn_display" value="<?php if (isset($params['c_load_params_btn_display'])) {
        echo (integer)$params['c_load_params_btn_display'];
    } else {
        '1';
    } ?>"/>
    <input type="hidden" class="element_params" data-type="add_zero_value" value="<?php if (isset($params['add_zero_value'])) {
        echo (integer)$params['add_zero_value'];
    } else {
        '1';
    } ?>"/>
    <input type="hidden" class="element_params" data-type="relate_many_select" value="<?php if (isset($params['relate_many_select'])) {
        echo (integer)$params['relate_many_select'];
    } else {
        '0';
    } ?>"/>
    <?php if (isset($params['type']) && $params['type'] != Fields::MFT_DATETIME && $params['type_view'] !== Fields::TYPE_VIEW_BUTTON_DATE_ENDING) { ?>
        <input type="hidden" class="element_params" data-type="type_view" value="<?php echo $params['type_view']; ?>"/>
    <?php } ?>
    <input type="hidden" class="element_params" data-type="rules" value="<?php echo isset($params['rules']) && !empty($params['rules']) ? $params['rules'] : ''; ?>"/>
    <input type="hidden" class="element_params" data-type="file_generate" value="<?php echo isset($params['file_generate']) ? (int)$params['file_generate'] : '0'; ?>"/>
    <input type="hidden" class="element_params" data-type="name_generate" value="<?php if (isset($params['name_generate'])) {
        echo $params['name_generate'];
    } ?>"/>
    <input type="hidden" class="element_params" data-type="name_generate_params" value="<?php if (isset($params['name_generate_params'])) {
        echo htmlspecialchars($params['name_generate_params'], ENT_QUOTES, 'UTF-8');
    } ?>"/>
    <input type="hidden" class="element_params" data-type="unique" value="<?php if (isset($params['unique'])) {
        echo (integer)$params['unique'];
    } ?>"/>

    <span style="display: none;" class="element_params params_value_json" data-type="input_attr"><?php if (isset($params['input_attr']) && !empty($params['input_attr'])) {
            echo $params['input_attr'];
        } ?></span>
    <?php // end ?>
</ul>


<!-- /.auto_number-menu -->
<?php if (isset($params['type']) && $params['type'] == 'auto_number') { ?>
<?php } ?>

<!-- /.settings-menu -->
<?php if (isset($params['type']) && $params['type'] == 'select') { ?>
    <ul class="sub-menu hide element_field_type_params_select element" data-type="field_type_params_select">
        <?php if (!empty($select_list)) {
            foreach ($select_list as $value) {
                echo $field_type_params = Yii::app()->controller->widget('ext.ElementMaster.Constructor.Params.Params',
                    [
                        'view'               => 'select',
                        'select_color_block' => ($params['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS ? true : false),
                        'select_params'      => [
                            'id'              => $value[$params['name'] . '_id'],
                            'value'           => $value[$params['name'] . '_title'],
                            'select_color'    => $value[$params['name'] . '_color'],
                            'select_sort'     => (isset($value[$params['name'] . '_sort'])) ? $value[$params['name'] . '_sort'] : 0,
                            'btn_remove'      => (boolean)$value[$params['name'] . '_remove'],
                            'finished_object' => (boolean)$value[$params['name'] . '_finished_object'],
                            'slug'            => $value[$params['name'] . '_slug'],
                        ],
                        'params'             => $params,
                    ],
                    true);
            }
        } ?>
        <div class="btn-element">
            <a href="javascript:void(0)" class="sub-menu-link add-field"><?php echo Yii::t('base', 'Add'); ?></a>
        </div>
    </ul><!-- /.sub-menu -->
<?php } ?>


<!-- FieldType params END -->
