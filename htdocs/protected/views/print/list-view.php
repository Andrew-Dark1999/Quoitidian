<div class="list-view-panel print">
    <section class="panel">
        <header class="panel-heading">
            <?php echo $extension_copy->title; ?>
        </header>
        <div class="panel-body sm_extension" data-copy_id="<?php echo $extension_copy->copy_id; ?>">
            <div class="adv-table editable-table">
                <table class="table table-bordered crm-table list-table" id="list-table">
                    <thead>
                    <tr>
                        <?php
                        $params = [];
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

                        if (isset($params['header']) && !empty($params['header'])) {
                            foreach ($params['header'] as $value) {
                                $tHWidth = '';
                                if (in_array($value['group_index'], $col_hidden)) {
                                    continue;
                                }
                                $fields = explode(',', $value['name']);
                                if (isset($params['params'][$fields[0]]['display']) && (bool)$params['params'][$fields[0]]['display'] == false) {
                                    continue;
                                }
                                if (isset($params['params'][$fields[0]]['list_view_visible']) && (bool)$params['params'][$fields[0]]['list_view_visible'] == false) {
                                    continue;
                                }
                                if (isset($params['params'][$fields[0]]['list_view_display']) && (bool)$params['params'][$fields[0]]['list_view_display'] == false) {
                                    continue;
                                }

                                //пропускаем определенные столбцы
                                if (!empty($col_name_hidden)) {
                                    if (!in_array($value['name'], $col_name_hidden)) {
                                        continue;
                                    }
                                }

                                foreach ($fields as $field) {
                                    if (isset($params['params'][$field])) {
                                        $params_for_data[$field] = $params['params'][$field];
                                    }
                                }
                                ?>
                                <th <?php echo $tHWidth; ?>
                                        data-name="<?php echo $value['name']; ?>"
                                        data-group_index="<?php echo $value['group_index']; ?>">
                                    <span class=""
                                          style="<?php   if ($storageThWidthParams && array_key_exists($value['name'], $storageThWidthParams)) {
                                              $tHWidth = 'width:' . $storageThWidthParams[$value['name']] . 'px';
                                          }?>"
                                    ><?php echo $value['title']; ?></span>
                                </th>
                            <?php }
                        } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $field_value = '';
                    foreach ($submodule_data as $value_data) {
                        ?>
                        <tr class="sm_extension_data"
                            data-id="<?php echo $value_data[$extension_copy->prefix_name . '_id']; ?>"
                            data-controller="edit_view"
                            data-render_type="html"
                        >
                            <?php
                            echo ListViewBulder::getInstance($extension_copy)
                                ->setParticipantListHidden(true)
                                ->setTitleAddAvatar($title_add_avatar)
                                ->setFilesOnlyUrl($filter_only_url)
                                ->setImgTag('img')
                                ->buildListViewRow($params_for_data, $value_data, $col_hidden);
                            ?>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div><!-- /.list-view-panel -->

