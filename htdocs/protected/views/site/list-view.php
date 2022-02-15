<?php
$crm_properties = [
    '_active_object'  => $this,
    '_extension_copy' => $extension_copy,
];

$name_storage = 'listView_' . $extension_copy->copy_id;

?>
<?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
<div class="list_view_block copy_id<?php echo $extension_copy->copy_id; ?> sm_extension"
     data-copy_id="<?php echo $extension_copy->copy_id; ?>"
     data-page_name="listView"
     data-parent_copy_id="<?php echo(array_key_exists('pci', $_GET) ? $_GET['pci'] : ''); ?>"
     data-parent_data_id="<?php echo(array_key_exists('pdi', $_GET) ? $_GET['pdi'] : ''); ?>"
     data-this_template="<?php echo (integer)$this->this_template; ?>"
>
    <div class="filter-block clearfix">

        <div class="wievs_tuggle">
            <?php
            foreach ($this->getSwitchIconList($extension_copy) as $switch_icon) {
                echo '<a class="' . $switch_icon['class'] . '" data-action_key="' . $switch_icon['data-action_key'] . '" data-type="' . $switch_icon['data-type'] . '" href="javascript:void(0)"><i class="' . $switch_icon['i_class'] . '"></i></a>';
            }
            ?>
        </div>

        <form class="search-filter" method="get">
            <div class="search-field form-control">
                <div class="filters-installed">
                    <?php echo $filters_installed ?>
                </div>
                <input type="text" class="search-input" placeholder="<?php echo Yii::t('base', 'Search'); ?>"
                       value="<?php if (Search::$text !== null) {
                           echo Search::$text;
                       } ?>">
            </div>
        </form><!-- /.search-block -->
        <?php if ($this->module->list_view_btn_filter) { ?>
            <div class="btn-group crm-dropdown dropdown-right edit-dropdown">
                <button class="btn dropdown-toggle btn-create btn-round" data-toggle="dropdown"><i
                            class="fa fa-filter"></i></button>
                <ul class="dropdown-menu dropdown-shadow filter-menu">
                    <?php if ($filter_menu_list_virual) {
                        echo $filter_menu_list_virual;
                    } ?>
                    <li><a href="javascript:void(0);" class="filter-create"><i
                                    class="fa fa-plus-circle"></i><?php echo Yii::t('base', 'Create filter'); ?></a>
                    </li>
                    <?php if ($filter_menu_list) { ?>
                        <li>
                            <span class="filter-separator filters-created"><?php echo Yii::t('base', 'Created filters'); ?></span>
                        </li>
                        <?php echo $filter_menu_list; ?>
                    <?php } ?>
                </ul>
                <div class="hover_notif"><?=Yii::t('base', 'Filters');?></div>
            </div>
        <?php } ?>

        <?php if ($extension_copy->copy_id == \ExtensionCopyModel::MODULE_COMMUNICATIONS) { ?>
            <div class="btn-group block-settings-communication">
                <button class="btn btn-default btn-round element" data-type="communications-settings"><i
                            class="fa fa-cog"></i></button>
                <div class="hover_notif"><?=Yii::t('communications', 'Post settings');?></div>
            </div>
        <?php } ?>

        <?php if ($this->module->finishedObject()) { ?>
            <div class="btn-group">
                <button class="btn btn-default btn-round element <?php if ($finished_object) {
                    echo 'active';
                } ?>"
                        data-type="finished_object"
                        data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->prepare()->getKey(); ?>">
                    <i class="fa fa-check-square"></i></button>
                <div class="hover_notif"><?=Yii::t('constructor', 'Completed');?></div>
            </div>
        <?php } ?>
        <?php
        if (
            $this->module->list_view_btn_templates &&
            $this->module->isTemplate($extension_copy) &&
            $extension_copy->getIsTemplate() != \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
            in_array($this->this_template, [EditViewModel::THIS_TEMPLATE_MODULE, EditViewModel::THIS_TEMPLATE_TEMPLATE]) &&
            !\Yii::app()->request->getParam('pci') &&
            !\Yii::app()->request->getParam('pdi')
        ) {
            $template_go = $this->this_template == EditViewModel::THIS_TEMPLATE_MODULE;
            ?>
            <div class="btn-group">
                <button <?php if (!$template_go) { ?> data-back="true" <?php } ?>
                        class="btn btn-default btn-round navigation_module_template_link <?=!$template_go ? 'active' : '';?>"
                        data-type="template"
                        data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->addVars(['module' => ['params' => ['this_template' => (int)!$this->this_template]]])->prepare()->getKey(); ?>">
                    <i class="fa fa-copy"></i></button>
                <div class="hover_notif"><?=Yii::t('base', 'Templates');?></div>
            </div>
            <?php
        }
        ?>
        <?php // кнопка Проекты
        if ($this->module->list_view_btn_project) {
            if (!empty($pm_extension_copy)) {
                ?>
                <div class="submodule-link btn-group crm-dropdown dropdown-right edit-dropdown element"
                     data-type="project_menu">
                    <button class="btn btn-default dropdown-toggle"
                            data-toggle="dropdown"><?php echo $project_menu_pdi_active ?></button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                        <div class="search-section">
                            <input type="text" class="submodule-search form-control"
                                   placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                        </div>

                        <div class="submodule-table">
                            <table class="table list-table">
                                <tbody>
                                <?php
                                if (!empty($project_menu_module_data)) {
                                    foreach ($project_menu_module_data as $project_menu) {
                                        $active = '';
                                        if ($project_menu[$pm_extension_copy->prefix_name . '_id'] == (integer)$_GET['pdi']) {
                                            $active = 'active';
                                        }
                                        ?>
                                        <tr class="sm_extension_data <?php echo $active; ?>"
                                            data-id="<?php echo $project_menu[$pm_extension_copy->prefix_name . '_id']; ?>">
                                            <td>
                                                <span href="javasctript:void(0)"
                                                      class="name"><?php echo $project_menu['module_title']; ?></span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr class="sm_extension_data" data-id="">
                                        <td><span href="javasctript:void(0)"
                                                  class="name"><?php echo Yii::t($this->module->getModuleName() . 'Module.base', 'there are no projects') ?></span>
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
            <?php }
        } ?>
        <div class="filter" style="display: none;">
            <hr/>
            <div class="filter-box-container"></div>
        </div>
    </div><!-- /. filter-block -->
    <div class="list-view-panel content-panel">
        <section class="panel">
            <header class="panel-heading">
                <?php
                // название модуля
                echo Yii::app()->controller->module->getModuleTitle();
                if ($this->this_template == EditViewModel::THIS_TEMPLATE_TEMPLATE) {
                    echo ': ' . Yii::t('base', 'Templates');
                }
                ?>
                <?php if (Yii::app()->controller->module->menu_list_view) { ?>
                    <span class="tools  pull-right">
			        <span class="crm-dropdown table-dropdown dropdown">
						<a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i
                                    class="fa fa-cog"></i></a>
                        <ul class="dropdown-menu dropdown-shadow local-storage"
                            data-hidden_index="<?php echo $name_storage; ?>" role="menu">
						    <li>
                                <?php
                                // меню отображения полей в гриде
                                $field_gi = '';
                                $count = 0;
                                $storage_params = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE, $name_storage);
                                $storage_params_date = '';

                                if (!empty($submodule_schema_parse)) {
                                    $params = SchemaConcatFields::getInstance()
                                        ->setSchema($submodule_schema_parse['elements'])
                                        ->setWithoutFieldsForListViewGroup()
                                        ->parsing()
                                        ->prepareWithOutDeniedRelateCopyId()
                                        ->primaryOnFirstPlace()
                                        ->prepareWithConcatName()
                                        ->getResult();

                                    $hide = [];
                                    foreach ($params['header'] as $value) {
                                        $fields = explode(',', $value['name']);
                                        if ($field_gi == $params['params'][$fields[0]]['group_index']) {
                                            continue;
                                        }
                                        if (isset($params['params'][$fields[0]]['display']) && (bool)$params['params'][$fields[0]]['display'] == false) {
                                            continue;
                                        }
                                        if (isset($params['params'][$fields[0]]['list_view_visible']) && (bool)$params['params'][$fields[0]]['list_view_visible'] == false) {
                                            continue;
                                        }
                                        if (isset($params['params'][$fields[0]]['list_view_display']) && (bool)$params['params'][$fields[0]]['list_view_display'] == false) {
                                            continue;
                                        }
                                        $count++;

                                        $checked = false;
                                        if ((empty($storage_params) || !in_array($value['group_index'], $storage_params))) {
                                            $not_check = (isset($params['params'][$fields[0]]['list_view_def_not_show']) && (bool)$params['params'][$fields[0]]['list_view_def_not_show'] == true);
                                            if ($not_check) {
                                                if ($storage_params_date === '') {
                                                    $storage_params_date = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE_FIRST_TIME, $name_storage);
                                                }
                                                if (!empty($storage_params_date)) {
                                                    $not_check = false;
                                                }
                                            }
                                            $checked = !$not_check;
                                        }

                                        ?>
                                        <div class="checkbox">
                                            <label><input type="checkbox" <?php if ($checked) {
                                                    echo 'checked="checked"';
                                                } ?>  data-group_index="<?php echo $value['group_index'] ?>"><span><?php echo ListViewBulder::getFieldTitle(['title' => $value['title']] + $params['params'][$fields[0]]) ?></span></label>
                                        </div>
                                        <?php
                                        $field_gi = $value['group_index'];
                                        if (!$checked) {
                                            $hide[] = $field_gi;
                                        }
                                    }

                                    if (!empty($hide)) {
                                        if ($storage_params_date !== '' && empty($storage_params_date)) {
                                            History::getInstance()->setUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE, $name_storage, $hide);
                                            History::getInstance()->setUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE_FIRST_TIME, $name_storage, true);
                                        }
                                    }

                                }
                                if ($count === 0) {
                                    echo '<label class="no_checkbox">' . Yii::t('messages', 'field is not set') . '</label>';
                                }
                                ?>
                            </li>
					    </ul>
					</span>
                </span>
                <?php } ?>
            </header>
            <div class="panel-body">
                <div class="adv-table editable-table">
                    <div class="btn-section clearfix">
                        <?php
                        if (
                            Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type')) &&
                            Yii::app()->controller->module->edit_view_enable
                        ) {
                            ?>
                            <div class="btn-group">
                                <button class="btn btn-primary <?php echo $dnt_card_add_class; ?> ">
                                    <?php echo \Yii::app()->controller->module->getListViewGeneralBtn('add', (bool)$this->this_template)['title']; ?>
                                </button>
                            </div>
                        <?php }

                        //btn Actions
                        $btn_action_list = Yii::app()->controller->module->getListViewBtnActionList();
                        if (!empty($btn_action_list)) {
                            ?>
                            <div class="btn-group crm-dropdown edit-dropdown">
                                <button class="btn btn-default dropdown-toggle"
                                        data-toggle="dropdown"><?php echo Yii::t('base', 'Actions') ?></button>
                                <ul class="dropdown-menu dropdown-shadow">
                                    <?php foreach ($btn_action_list as $action) { ?>
                                        <li><a href="javascript:void(0)"
                                               class="<?php echo $action['class']; ?>"><?php echo $action['title']; ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php
                        }

                        //btn Tools
                        $btn_tools_list = Yii::app()->controller->module->getListViewBtnToolsList();
                        if ($btn_tools_list) {
                            ?>
                            <div class="btn-group crm-dropdown edit-dropdown instruments">
                                <button class="btn btn-default dropdown-toggle"
                                        data-toggle="dropdown"><?php echo Yii::t('base', 'Tools'); ?></button>
                                <ul class="dropdown-menu dropdown-shadow">
                                    <?php foreach ($btn_tools_list as $tools) { ?>
                                        <li><a href="javascript:void(0)"
                                               class="<?php echo $tools['class']; ?>"><?php echo $tools['title']; ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div id="list-table_wrapper_all" class="list-table_wrapper_all">
                        <?php } //BLOCK_1 ?>
                        <?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                        <div class="crm-table-wrapper" id="list-table_wrapper">
                            <?php } //BLOCK_2 ?>
                            <?php
                            if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_3)) {

                                $vars = [
                                    'selector_content_box' => '#list-table_wrapper',
                                    'content_blocks'       => [\ControllerModel::CONTENT_BLOCK_3],
                                ];
                                $action_key = (new \ContentReloadModel(8, $crm_properties))->addVars($vars)->prepare()->getKey();
                                ?>
                                <table class="table table-bordered table-striped crm-table first-cell-visible list-table local-storage"
                                       data-sort_index="listView_<?php echo $extension_copy->copy_id; ?>"
                                       id="list-table"
                                       data-action_key="<?php echo $action_key; ?>"
                                >
                                    <thead>
                                    <tr>
                                        <th id="order-<?php echo $extension_copy->copy_id; ?>-0">
                                    <span class="visible-cell"><input <?php if (empty($btn_action_list)) { ?> disabled="true" <?php } ?>
                                                type="checkbox" class="checkbox"></span>
                                        </th>
                                        <?php
                                        // формируем заголовок таблицы
                                        $schema_params = [];
                                        $schema_params_parse = [];
                                        $storageThWidthParams = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_WIDTH, $name_storage);

                                        if (isset($submodule_schema_parse['elements'])) {
                                            $schema_params = SchemaConcatFields::getInstance()
                                                ->setSchema($submodule_schema_parse['elements'])
                                                ->setWithoutFieldsForListViewGroup()
                                                ->parsing()
                                                ->prepareWithOutDeniedRelateCopyId()
                                                ->primaryOnFirstPlace()
                                                ->prepareWithConcatName()
                                                ->getResult();
                                        }

                                        (new DataListModel())
                                            ->setExtensionCopy($extension_copy)
                                            ->listViewCollumnPosition($schema_params['header']);

                                        $order = 0;

                                        if (isset($schema_params['header']) && !empty($schema_params['header'])) {
                                            foreach ($schema_params['header'] as $value) {
                                                $fields = explode(',', $value['name']);
                                                if (isset($schema_params['params'][$fields[0]]['list_view_visible']) && (bool)$schema_params['params'][$fields[0]]['list_view_visible'] == false) {
                                                    continue;
                                                }
                                                if (isset($schema_params['params'][$fields[0]]['display']) && (bool)$schema_params['params'][$fields[0]]['display'] == false) {
                                                    continue;
                                                }

                                                foreach ($fields as $field) {
                                                    if (isset($schema_params['params'][$field])) {
                                                        $schema_params_parse[$field] = $schema_params['params'][$field];
                                                    }
                                                }
                                                ?>
                                                <th id="order-<?php echo $extension_copy->copy_id . '_' . ++$order; ?>"
                                                    class="sorting <?php if (Sorting::getInstance()->fieldExists(null, $value['name'])) {
                                                        echo 'sorting_' . Sorting::$params[explode(',', $value['name'])[0]];
                                                    } ?>
                                                   <?php if ($value['group_index'] != 0) {
                                                        echo "draggable";
                                                    } ?>
                                                   <?php if (isset($schema_params['params'][$fields[0]]['list_view_display']) && $schema_params['params'][$fields[0]]['list_view_display'] != true) {
                                                        echo 'hidden';
                                                    } ?>
                                                   <?php if (isset($schema_params['params'][$fields[0]]['inline_edit']) && $schema_params['params'][$fields[0]]['inline_edit'] == true) {
                                                        echo 'data_edit';
                                                    } ?>
                                                   "
                                                    data-name="<?php echo $value['name']; ?>"
                                                    data-group_index="<?php echo $value['group_index']; ?>">
                                                    <span class="table-handle"
                                                        style=" <?php if ($storageThWidthParams && array_key_exists($value['name'], $storageThWidthParams)) {
                                                            echo 'width:' . $storageThWidthParams[$value['name']].'px';
                                                        } ?>"><?php echo ListViewBulder::getFieldTitle(['title' => $value['title']] + $schema_params['params'][$fields[0]]); ?></span>
                                                    <span class="sorting-arrows"></span>
                                                </th>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // заполняем данными
                                    $field_value = '';
                                    if (!empty($submodule_data)) {
                                        foreach ($submodule_data as $data_row) {
                                            $list_view_row_model = (new \ListViewRowModel())
                                                ->setExtensionCopy($extension_copy)
                                                ->setParentCopyId(\Yii::app()->request->getParam('pci'))
                                                ->setParentCopyId(\Yii::app()->request->getParam('pdi'))
                                                ->setThisTemplate($this->this_template)
                                                ->setFinishedObject($finished_object)
                                                ->setSchemaParams($schema_params_parse)
                                                ->setData($data_row)
                                                ->prepareHtmlRow();
                                            ?>
                                            <tr class="sm_extension_data element"
                                                data-id="<?php echo $list_view_row_model->getPkValue(); ?>"
                                                data-controller="edit_view"
                                                data-render_type="html"
                                                data-entity_key="<?php echo $list_view_row_model->getEntityModel()->getKey(); ?>"
                                            >
                                                <td>
                                                    <span class="visible-cell"><input <?php if (empty($btn_action_list)) { ?> disabled="true" <?php } ?> type="checkbox"
                                                                                                                                                         class="checkbox"></span>
                                                </td>
                                                <?php //echo $list_view_row_model->getEntityModel()->getKey();
                                                ?>
                                                <?php echo $list_view_row_model->getHtml(); ?>
                                            </tr>
                                        <?php }
                                    } ?>

                                    </tbody>
                                </table>
                            <?php } // BLOCK_3 end ?>
                            <?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                        </div>
                    <?php
                    $vars = [
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks'       => [\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3],
                    ];
                    $action_key = (new \ContentReloadModel(8, $crm_properties))->addVars($vars)->prepare()->getKey();
                    ?>
                        <div
                                class="row local-storage element pagination-block"
                                data-type="pagination_block"
                                data-action_key="<?php echo $action_key; ?>"
                                data-pagination_index="listView_<?php echo $extension_copy->copy_id; ?>"
                        >
                            <div class="col-sm-6">
                                <div class="dataTables_info" id="list-table_info" role="status" aria-live="polite">
                                    <?php echo Pagination::getInstance()->getPaginatorReport(); ?>
                                </div>
                                <?php echo Pagination::getInstance()->getPaginatorSize(); ?>
                            </div>
                            <div class="col-sm-6 pull-right">
                                <?php echo Pagination::getInstance()->getPaginatorView(); ?>
                            </div>
                        </div>
                    <?php } //BLOCK_2 end ?>
                        <?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
                    </div>
                </div>
            </div>
        </section>
    </div><!-- /.list-view-panel -->
</div>
<input type="file" id="file_import_data" accept="<?php echo FileOperations::getInstance()->getFileMime('xlsx'); ?>"
       style="display: none;"/>
<?php } // BLOCK_1 ?>


<?php
$action_key_b = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(true)->getKey();
$action_key_s = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(false)->getKey();
?>

<script type="text/javascript">
    $(document).ready(function () {
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>',
            param1 = [<?php echo $action_key_b; ?>, true],
            param2 = [<?php echo $action_key_s; ?>, false];

        instanceGlobal.contentReload.setVarsFromPage(content_vars, param1, param2);
        var key = 1 // key for lv
        AjaxContainers.arrayOfKeys.push(key);

        inLineEdit.setAjaxKey(key).loadElements();
        inLineEdit.setCallbackSuccessAfterSave(null);

        <?php if(Yii::app()->user->hasFlash('import_status')): ?>
        Message.show(<?php echo Yii::app()->user->getFlash("import_status");?>, false);
        if ($('.modal-dialog').height() > $(window).height() - 220) {
            $('.modal-dialog .panel.element[data-type="message"]>*:first-child').css({
                'height': $(window).height() - 220 + '',
                'overflow': 'auto'
            });
        }
        <?php endif; ?>

        ListView.constructorReLoad(); //Конструктор
    });
</script>
