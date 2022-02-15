<li class="element" data-name="panel">
    <?php //echo (!empty($this->panel_data['sorting_list_id']) ? $this->panel_data['sorting_list_id'] : ''); ?>
    <section
            class="panel"
            data-sorting_list_id="<?php echo $this->panel_data['sorting_list_id']; ?>"
            data-unique_index="<?php echo (!empty($this->panel_data) ? $this->panel_data['unique_index'] : ''); ?>"
    >
        <?php
            $show_edit = false;
            $show_delete = false;

            if($this->extension_copy){
                if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))){
                    $show_edit = true;
                }
                if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))){
                    $show_delete = true;
                }
            }
        ?>
        <header class="panel-heading">
        <?php //формируем заголовок колонки, по которому сгрупированы данные ?>
        <span class="element" data-name="field_title">
            <?php
                if(!empty($this->fields_group)){
                    foreach($this->fields_group as $field_name){
                        $panel_data_list = $this->getPanelDataList($field_name);
                        echo '<span class="element text-ellipsis" data-name="field_title_value" data-field="' . $panel_data_list['field_name'] . '" data-value="' . $panel_data_list['field_value'] . '">' . $panel_data_list['html_value'] . '</span> ';
                    }
                } else {
                    echo '<span class="element text-ellipsis" data-name="field_title_value" data-field="" data-value=""></span> ';
                }
            ?>
        </span>
        <?php //формируем меню выбора второго поля для отображения ?>
        <span class="tools pull-right">
            <?php if($show_edit || $show_delete){ ?>
                <input type="checkbox" class="header_check">
            <?php } ?>
            <?php
                $panel_menu_list = ProcessViewSortingListModel::getInstance()->getPanelMenuList(true);
                if($panel_menu_list){
            ?>
            <span class="crm-dropdown edit-dropdown dropdown">
                 <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                 <ul class="dropdown-menu dropdown-shadow" role="menu">
                    <?php foreach($panel_menu_list as $action => $title){ ?>
                        <li><a href="javascript:void(0)" class="element" data-type="panel_menu" data-action="<?php echo $action; ?>"><?php echo $title; ?></a></li>
                    <?php } ?>
                 </ul>
            </span>
            <?php
                }
            ?>
        </span>

        </header>
        <div class="panel-body">
            <div class="slimscrolldiv">

                <ul class="to-do-list ui-sortable">
                <?php
                    if($this->append_cards_html){
                        echo $this->card_list_html;
                    }
                ?>
               </ul>
            </div>
            <div class="todo-action-bar">
                <?php
                    $btn_action_list = Yii::app()->controller->module->getListViewBtnActionList();

                    if($btn_action_list || $show_edit) {
                ?>
                    <div class="row">
                        <div class="todo-cards hidden">
                                <?php if($btn_action_list && array_key_exists('copy', $btn_action_list)){ ?>
                                <div class="col-xs-6 btn-todo-select">
                                    <button type="submit" class="btn btn-default process_view_btn-copy"><?php echo Yii::t('base', 'Copy'); ?></button>
                                </div>
                            <?php } ?>
                            <?php if($btn_action_list && array_key_exists('delete', $btn_action_list)){ ?>
                                <div class="col-xs-6 btn-todo-select">
                                    <button type="submit" class="btn btn-danger process_view_btn-delete"><?php echo Yii::t('base', 'Delete'); ?></button>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if($show_edit) { ?>
                            <div class="col-xs-6 pull-right btn-add-card">
                                <button type="submit" class="btn btn-create <?php echo $dnt_card_add_class; ?>"><i>+</i> <?php echo Yii::t('base', 'Add'); ?></button>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</li>





