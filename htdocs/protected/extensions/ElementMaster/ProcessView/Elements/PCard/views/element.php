<?php
    $primary_field = $this->extension_copy->getPrimaryField(null, false);
    $relate_copy_id = null;
    $module_title = array();
    $pencil_show = false;
    $action_key = null;
    if($primary_field !== null)
    
    foreach($primary_field as $field){
        $module_title[] = $this->card_data[$field['params']['name']];
        if(!$relate_copy_id && $field && isset($field['params']) && $field['params']['is_primary'] && $field['params']['type'] == 'relate_string'){
            $relate_copy_id = $field['params']['relate_module_copy_id'];
        }
    }
    if($relate_copy_id || $this->extension_copy->copy_id == ExtensionCopyModel::MODULE_PROCESS){
        $pencil_show = true;
        $crm_properties = [
            '_active_object' => $this,
            '_extension_copy' => $this->extension_copy,
        ];
        $vars = array(
            'module' => array(
                'copy_id' => $relate_copy_id,
                'params' => array(
                    'pci' => $this->extension_copy->copy_id,
                    'pdi' => $this->card_data[$this->extension_copy->prefix_name . '_id'],
                )
            )
        );
        $action_key = (new \ContentReloadModel(8, $crm_properties))->addVars($vars)->prepare()->getKey();
    }


?>

<li class="clearfix sm_extension_data element modal_dialog"
    data-sorting_cards_id="<?php echo $this->card_data['sorting_cards_id']; ?>"
    data-id="<?php echo $this->card_data[$this->extension_copy->prefix_name . '_id']; ?>"
    data-type="drop_down"
    data-controller="process_view_edit"
    <?php if($action_key){ ?>data-action_key="<?php echo $action_key; ?>" <?php } ?>
>
    <?php //echo $this->card_data['sorting_cards_id']; ?>

    <?php
        if($this->extension_copy->isResponsible() || $this->extension_copy->isParticipant()){
        ?>
            <div class="todo-check pull-left">
            <?php

            echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                   array(
                    'extension_copy' => $this->extension_copy,
                    'data_array' => $this->card_data,
                    'thumb_size' => 32,
                    'avatar_view_participant_as_responsible' => true,
                    'avatar_view_participant_as_responsible_once' => true
                   ),
                   true);

            ?>
            </div>
    <?php
        }
    ?>
    
    <div class="card_middle">
        <p class="card_title">
                <span>
                    <?php if(!empty($module_title)) echo implode(' ', $module_title); ?>
                </span>
        </p>
        <?php 
            if(!empty($this->fields_view) && (empty($this->fields_group) || implode(',', $this->fields_group) != implode(',', $this->fields_view))) //не выводим поле, если оно = полю сортировки
            foreach($this->fields_view as $field){
                $field_exp = explode(',', $field);
                $value_render = array();
                foreach($field_exp as $field_e){
                    $params = $this->extension_copy->getFieldSchemaParams($field_e);
                    if(empty($params['params'])) continue;
                    if($params['params']['is_primary'] == true) continue;
    
                    $denied_relate = SchemaOperation::getDeniedRelateCopyId(array($params['params']));    
                    if($denied_relate['be_fields'] == false) continue;

                    //*****
                    $value_render[] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                                  array(
                                    'extension_copy' => $this->extension_copy,
                                    'params' => $params['params'],
                                    'value_data' => $this->card_data,
                                    'relate_add_avatar' => false,
                                    'show_file_name_only' => true,
                                    'show_sdm_link' => false,
                                   )
                                  , true);


                }
                $value_render = (!empty($value_render) ? implode(' ', $value_render) : '');
                $color = null;
                if($params['params']['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
                    $date_diff = DateTimeOperations::dateDiff($this->card_data[$params['params']['name']], date('Y-m-d H:i:s'));
                    if($date_diff !== null && $date_diff === -1) $color = 'red';
                    $value_render = Yii::t('base', 'Date ending') . ' ' . $value_render;
                } 

                if($this->block_field_name_replace){
                    if($params['params']['name'] == $this->block_field_name_replace){
                        if(isset($this->panel_data[$this->extension_copy->getTableName() . '.' . $this->block_field_name_replace])){
                            $value_render = $this->panel_data[$this->extension_copy->getTableName() . '.' . $this->block_field_name_replace];
                        }
                    }
                }

                echo '<p class="card_text text-ellipsis" '. ($color ? "style='color : $color'" : "") .' >'. $value_render .'</p>';
            }
        ?>
    </div>
    <div class="todo-actionlist pull-right clearfix">
        <?php if($pencil_show) { ?>
                <a href="javascript:void(0)" class="modal_dialog link" data-controller="process_view_edit"><i class="fa fa-pencil"></i></a>
        <?php } ?>
        <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))||
                 Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))){ ?>
                    <input onclick="event.stopPropagation();" type="checkbox" class="card_check">
        <?php } ?>
    </div>
    <?php
        if(isset($last_img_file) && $last_img_file && isset($last_img_file['file_url'])){
           ?>
            <br />
            <img width="100%" src="<?php echo $last_img_file['file_url'];  ?>" />
            <?php
        }
    ?>


    <?php if($this->js_content_reload_add_vars){ ?>
        <script type="text/javascript">
            $(document).ready(function(){
                var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
                if(content_vars){
                    content_vars = JSON.parse(content_vars);
                    instanceGlobal.contentReload.addContentVars(content_vars);
                }
            });
        </script>
    <?php } ?>


</li>

