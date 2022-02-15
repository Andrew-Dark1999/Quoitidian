<?php
$crm_properties = [
    '_active_object'  => $this,
    '_extension_copy' => $extension_copy,
];
?>
<?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
<div class="list_view_block copy_id<?php echo $extension_copy->copy_id; ?> sm_extension"
     data-copy_id="<?php echo $extension_copy->copy_id; ?>"
     data-page_name="listView"
     data-parent_copy_id="<?php echo(array_key_exists('pci', $_GET) ? $_GET['pci'] : ''); ?>"
     data-parent_data_id="<?php echo(array_key_exists('pdi', $_GET) ? $_GET['pdi'] : ''); ?>"
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
                <input type="text" class="search-input" placeholder="<?php echo Yii::t('base', 'Search'); ?>" value="<?php if (Search::$text !== null) {
                    echo Search::$text;
                } ?>">
            </div>
        </form><!-- /.search-block -->
    </div>
    <div class="list-view-panel content-panel">
        <section class="panel">
            <header class="panel-heading">
                <?php echo Yii::t('PermissionModule.base', 'Permissions');; ?>
                <?php if (Yii::app()->controller->module->menu_list_view) { ?>
                    <span class="tools pull-right">
			        <span class="crm-dropdown table-dropdown dropdown">
						<a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
					    <ul class="dropdown-menu dropdown-shadow local-storage" data-hidden_index="listView_<?php echo $extension_copy->copy_id; ?>" role="menu">
						    <li>
                                <?php
                                // меню отображения полей в гриде
                                $field_gi = '';
                                $count = 0;
                                $storage_params = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE, 'listView_' . $extension_copy->copy_id);

                                if (!empty($submodule_schema_parse)) {
                                    $params = SchemaConcatFields::getInstance()
                                        ->setSchema($submodule_schema_parse['elements'])
                                        ->setWithoutFieldsForListViewGroup($this->module->getModuleName())
                                        ->parsing()
                                        ->prepareWithOutDeniedRelateCopyId()
                                        ->primaryOnFirstPlace()
                                        ->prepareWithConcatName()
                                        ->getResult();

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
                                        ?>
                                        <div class="checkbox">
                                            <label><input type="checkbox" <?php if (empty($storage_params) || !in_array($value['group_index'], $storage_params))
                                                    echo 'checked="checked"' ?>  data-group_index="<?php echo $value['group_index'] ?>"><span><?php echo $value['title'] ?></span></label>
                                        </div>
                                        <?php
                                        $field_gi = $value['group_index'];
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
                        //btn Tools
                        $btn_tools_list = Yii::app()->controller->module->getListViewBtnToolsList();
                        if ($btn_tools_list) {
                            ?>
                            <div class="btn-group crm-dropdown edit-dropdown instruments">
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Tools'); ?></button>
                                <ul class="dropdown-menu dropdown-shadow">
                                    <?php foreach ($btn_tools_list as $tools) { ?>
                                        <li><a href="javascript:void(0)" class="<?php echo $tools['class']; ?>"><?php echo $tools['title']; ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="btn-group crm-dropdown edit-dropdown element" data-type="roles_menu">
                            <button class="btn btn-default dropdown-toggle none-width min-width-120" data-toggle="dropdown"><?php echo $this->getRoleActiveTitle(); ?></button>
                            <ul class="dropdown-menu dropdown-shadow">
                                <?php
                                foreach ($this->getRolesList() as $role) {
                                    ?>
                                    <li <?php if (array_key_exists('pdi', $_GET) && (integer)$role['roles_id'] == (integer)$_GET['pdi']) {
                                        echo 'class="active"';
                                    } ?> ><a href="javascript:void(0)" data-pdi="<?php echo $role['roles_id']; ?>"><?php echo $role['module_title']; ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
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

                                <table
                                        class="table table-bordered table-striped crm-table first-cell-visible list-table local-storage"
                                        data-sort_index="listView_<?php echo $extension_copy->copy_id; ?>"
                                        id="list-table"
                                        data-action_key="<?php echo $action_key; ?>"
                                >
                                    <thead>
                                    <tr>
                                        <th id="order-<?php echo $extension_copy->copy_id; ?>-0">
                                            <span class="visible-cell"><input type="checkbox" class="checkbox"></span>
                                        </th>
                                        <th id="order-<?php echo $extension_copy->copy_id; ?>-1"
                                            class="sorting <?php if (Sorting::getInstance()->fieldExists(null, 'module_title')) {
                                                echo 'sorting_' . Sorting::$params['module_title'];
                                            } ?>"
                                            data-name="module_title"
                                        >
                                            <span class="sorting-arrows"></span><?php echo Yii::t('PermissionModule.base', 'Module'); ?>
                                        </th>
                                        <?php
                                        // формируем заголовок таблицы
                                        $params = [];
                                        $params_for_data = [];
                                        $storageThWidthParams = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_WIDTH, 'listView_' . $extension_copy->copy_id);

                                        if (isset($submodule_schema_parse['elements'])) {
                                            $params = SchemaConcatFields::getInstance()
                                                ->setSchema($submodule_schema_parse['elements'])
                                                ->setWithoutFieldsForListViewGroup($this->module->getModuleName())
                                                ->parsing()
                                                ->prepareWithOutDeniedRelateCopyId()
                                                ->primaryOnFirstPlace()
                                                ->prepareWithConcatName()
                                                ->getResult();
                                        }

                                        (new DataListModel())
                                            ->setExtensionCopy($extension_copy)
                                            ->listViewCollumnPosition($params['header']);

                                        if (isset($params['header']) && !empty($params['header'])) {
                                            $order = 1;
                                        }
                                        foreach ($params['header'] as $value) {
                                            $fields = explode(',', $value['name']);
                                            if (isset($params['params'][$fields[0]]['list_view_visible']) && (bool)$params['params'][$fields[0]]['list_view_visible'] == false) {
                                                continue;
                                            }
                                            foreach ($fields as $field) {
                                                $params_for_data[$field] = $params['params'][$field];
                                            }
                                            ?>
                                            <th id="order-<?php echo $extension_copy->copy_id . '_' . ++$order; ?>"
                                                class="sorting <?php if (Sorting::getInstance()->fieldExists(null, $value['name'])) {
                                                    echo 'sorting_' . Sorting::$params[explode(',', $value['name'])[0]];
                                                } ?>
                                                   <?php if ($value['group_index'] != 0) {
                                                    echo "draggable";
                                                } ?>
                                                   <?php if (isset($params['params'][$fields[0]]['list_view_display']) && $params['params'][$fields[0]]['list_view_display'] != true) {
                                                    echo 'hidden';
                                                } ?>
                                                   <?php if (isset($params['params'][$fields[0]]['inline_edit']) && $params['params'][$fields[0]]['inline_edit'] == true) {
                                                    echo 'data_edit';
                                                } ?>
                                                   "
                                                data-name="<?php echo $value['name']; ?>" data-group_index="<?php echo $value['group_index']; ?>">
                                                <span class="table-handle"
                                                    style="<?php if ($storageThWidthParams && array_key_exists($value['name'], $storageThWidthParams)) {
                                                        echo 'width:' . $storageThWidthParams[$value['name']].'px';
                                                    } ?>"><?php echo Yii::t('PermissionModule.base', $value['title']); ?></span>
                                                <span class="sorting-arrows"></span>
                                            </th>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // заполняем данными
                                    $field_value = '';
                                    foreach ($submodule_data as $value_data) {
                                        ?>
                                        <tr class="sm_extension_data"
                                            data-id="<?php echo $value_data[$extension_copy->prefix_name . '_id']; ?>"
                                            data-controller="edit_view"
                                            data-render_type="html"
                                        >
                                            <td>
                                                <span class="visible-cell"><input type="checkbox" class="checkbox"></span>
                                            </td>
                                            <td>
                                                <span></span><?php if ($value_data['access_id_type'] == RegulationModel::REGULATION_SYSTEM_SETTINGS) {
                                                    echo Yii::t('PermissionModule.regulations', $value_data['module_title']);
                                                } else {
                                                    echo $value_data['module_title'];
                                                } ?>
                                            </td>
                                            <?php
                                            echo ListViewBulder::getInstance($extension_copy)->buildListViewRow($params_for_data, $value_data);
                                            ?>
                                        </tr>
                                    <?php } ?>

                                    </tbody>
                                </table>
                            <?php } // BLOCK_3 ?>
                            <?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                        </div>
                    <?php
                    $vars = [
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks'       => [\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3],
                    ];
                    $action_key = (new \ContentReloadModel(8, $crm_properties))->addVars($vars)->prepare()->getKey();
                    ?>
                        <div class="row local-storage element pagination-block" data-type="pagination_block" data-action_key="<?php echo $action_key; ?>" data-pagination_index="listView_<?php echo $extension_copy->copy_id; ?>">
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
                    <?php } //BLOCK_2 ?>
                        <?php if (ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
                    </div>
                </div>
            </div>
        </section>
    </div><!-- /.list-view-panel -->
</div>
<input type="file" id="file_import_data" accept="<?php echo FileOperations::getInstance()->getFileMime('xlsx'); ?>" style="display: none;"/>
<?php } // BLOCK_1 ?>

<?php
$action_key_b = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(true)->getKey();
$action_key_s = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(false)->getKey();
?>


<script type="text/javascript">

    $(document).ready(function () {
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        if (content_vars) {
            content_vars = JSON.parse(content_vars);
            instanceGlobal.contentReload.addContentVars(content_vars);

            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_b; ?>, true);
            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_s; ?>, false);
        }
    });


    <?php
    $inline = InLineEditBuilder::getInstance()
        ->setExtensionCopy($extension_copy)
        ->setParentCopyId((array_key_exists('pci', $_GET) ? (integer)$_GET['pci'] : null))
        ->buildElementJSArray($params_for_data);
    ?>

    inLineEdit.elements = <?php echo(!empty($inline) ? json_encode($inline) : 'false') ?>;
    inLineEdit.setCallbackSuccessAfterSave(function () {
        var vars = {'content_blocks_different': [{'name': 'main_top_module_menu', 'selector': '.element[data-type="module_menu"]'}]};
        instanceGlobal.contentReload.actionRefreshContentReloadDifferentBlocks(vars);
    });

    <?php if(Yii::app()->user->hasFlash('success')): ?>
    $(document).ready(function () {
        Message.show(<?php echo Yii::app()->user->getFlash("success");?>, false);
    });
    <?php endif; ?>


    $(document).on('click', '.element[data-type="roles_menu"] li a', function () {
        var $this = $(this),
            $rolesMenu = $this.closest('.element[data-type="roles_menu"]');

        if ($this.closest('li').hasClass('active')) return;

        instanceGlobal.preloaderShow($this.closest('.element'));

        $rolesMenu.find('li.active').removeClass('active');
        $this.closest('li').addClass('active');
        $rolesMenu.find('button').text($this.text());


        var url = '/roles/' + $(this).data('pdi');
        var url_parse = window.location.href.split("?");
        if (url_parse[1]) {
            url += '?' + url_parse[1];
        }

        instanceGlobal.contentReload
            .clear()
            .prepareVariablesToGeneralContent()
            .setUrl(url)
            .setCallBackSuccessComplete(function () {
                $this.closest('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id', $this.data('id'));
            })
            .run();

    })
</script>
