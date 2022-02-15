<span class="element" data-type="button">
    <?php
    //button Date ending
    if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
        $date_time_format = $this->getDateTimeFormat();
        $date_time_color = $this->getDateTimeColor();

     ?>
        <div class="crm-dropdown" data-type="edit-view">
            <label type="button"
                   class="btn btn-default btn-ending container-date-time"
                <?php echo ($date_time_color ? 'datetime="'.$date_time_color.'"' : ''); ?>
                    title="<?php echo Yii::t('base', 'Deadline'); ?>">
                <span class="element" data-type="title"><?php echo Yii::t('base', 'Deadline'); ?></span>
                <input
                        type="button"
                        class="btn btn-default date-time"
                        name="EditViewModel[<?php echo $schema['params']['name'];  ?>]"
                        data-all_day="<?php echo (int)$this->isDateTimeAllDay() ?>"
                        value="<?php echo $date_time_format;  ?>"
                    <?php echo ($date_time_color ? 'datetime="' . $date_time_color . '"' : ''); ?>
                />
            </label>
            <div class="btn-ending-block dropdown-menu">
                <div class="content">
                    <div class="element" data-type="calendar-place"></div>
                    <div class="flex-column description">
                        <label class="flex-content-top checkbox-line" for="ckbAllDay">
                            <input type="checkbox" checked id="ckbAllDay"> <?php echo Yii::t('base', 'All day'); ?>
                        </label>

                        <div class="time-block disable flex-content-center"></div>

                        <button type="submit" class="flex-content-bottom btn btn-primary btn-save"><?php echo Yii::t('base', 'Save')?></button>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    //button subscription
    elseif($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_SUBSCRIPTION){
        $user_subscription = ParticipantModel::model()->getUserSubscription(
            $extension_copy->copy_id,
            $extension_data->{$extension_copy->prefix_name.'_id'},
            WebUser::getUserId());
        $subscriptio_value = '0';
        //$disabled = "";
        $subscriptio_title = Yii::t('base', 'Subscribe');
        if(!empty($user_subscription)){
            $subscriptio_value = '1';
            $subscriptio_title = Yii::t('base', 'Unsubscribe');
        } else {
            /*
            if(!ParticipantModel::model()->checkUserSubscription(
                                                $extension_copy->copy_id,
                                                $extension_data->{$extension_copy->prefix_name . '_id'},
                                                $extension_data))
            {
                $disabled = 'disabled="disabled"';
            }
            */
        }
        ?>
        <button
                class="btn btn-default element"
                data-type="<?php echo $schema['params']['type_view']; ?>"
                data-ug_id="<?php echo WebUser::getUserId(); ?>"
            <?php //echo $disabled; ?>
                value="<?php echo $subscriptio_value; ?>" ><?php echo $subscriptio_title; ?>
            </button>

    <?php }





    //button responsible
    elseif($schema['params']['type_view'] ==  Fields::TYPE_VIEW_BUTTON_RESPONSIBLE){
        $element_data = \EditViewBuilderElementDataModel::getDataRelateParticipant(get_defined_vars(), true, $get_related_responsible);
        ?>
        <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
            <button
                    class="btn btn-default dropdown-toggle element element_relate_participant"
                    name="EditViewModel[<?php echo $schema['params']['name']; ?>]"
                    data-toggle="dropdown"
                    data-type="drop_down_button"
                    data-participant_id="<?php if(!empty($element_data['relate_data']) && (!isset($_POST['from_template']) || (boolean)$_POST['from_template'] == false)) echo $element_data['relate_data']->participant_id; ?>"
                    data-ug_id="<?php echo $element_data['ug_id']; ?>"
                    data-ug_type="<?php echo $element_data['ug_type'] ?>"
                    data-u_id="<?php echo WebUser::getUserId(); ?>"
            ><?php  echo $element_data['html']; ?></button>

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
                    if(!empty($element_data['select_list'])){
                        foreach($element_data['select_list'] as $value){
                            if(ParticipantModel::checkAccessParticipantForModule($extension_copy->copy_id, $value['ug_id']) == false) continue;
                            ?>
                            <tr class="sm_extension_data" data-ug_id="<?php echo $value['ug_id']; ?>"
                                data-ug_type="<?php echo $value['ug_type']; ?>">
                                    <td>
                                <span href="javasctript:void(0)" class="name"><?php
                                    if($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                                        $html = DataValueModel::getInstance()
                                            ->setFileLink(false)
                                            ->getRelateValuesToHtml($value, array(
                                                'relate_field' => array('sur_name', 'first_name', 'father_name'),
                                                'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF));
                                        echo $html;
                                    } elseif($value['ug_type'] == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                                    }
                                    ?></span>
                                    </td>
                                </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                    </table>
                </div>
            </ul>
        </div>
    <?php } ?>



    <?php
    // button status
    if($schema['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS){
        $color = '';
        if($is_new_record){
            $data = Yii::t('base', 'Status');
        } else {
            $data = $extension_data->{$schema['params']['name']};
        }

        $select_list = DataModel::getInstance()->setFrom($extension_copy->getTableName($schema['params']['name']))->findAll();
        $select = array();

        if(!empty($select_list) && isset($select_list[0][$schema['params']['name'] . '_sort']))
            $select_list = Helper::arraySort($select_list, $schema['params']['name'] . '_sort');

        foreach($select_list as $value)
            $select[$value[$schema['params']['name'] . '_id']] = $value;

        if($default_data !== null) $data = $default_data;
        else $data = $extension_data->{$schema['params']['name']};
        ?>
        <div class="column">
                <select name="<?php echo 'EditViewModel['.$schema['params']['name'].']'; ?>" id="<?php echo $schema['params']['name']; ?>" class="select color" >
                    <?php if(!isset($schema['params']['add_zero_value']) || (boolean)$schema['params']['add_zero_value'] === true){ ?>
                        <option value=""><?php echo Yii::t('base', 'Status'); ?></option>
                    <?php } ?>
                    <?php foreach($select as $key => $value){
                        $selected = '';
                        if($data == $key) $selected = 'selected';
                        ?>
                        <option <?php echo $selected; ?> data-content="<span class='label label-gray' data-color='<?php echo $value[$schema['params']['name'] . '_color'] ?>' ><?php echo $value[$schema['params']['name'] . '_title'] ?></span>" value="<?php echo $key ?>"></option>
                    <?php } ?>
                </select>
            <?php echo CHtml::error($extension_data, $schema['params']['name']); ?>
            </div>
    <?php } ?>

    
    
    
</span>

