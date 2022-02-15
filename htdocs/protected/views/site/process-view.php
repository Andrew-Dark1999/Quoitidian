<?php
    $crm_properties = [
        '_active_object' => $this,
        '_extension_copy' => $extension_copy,
    ];

    $process_view_builder_model = (new ProcessViewBuilder())
                                        ->setExtensionCopy($extension_copy)
                                        ->setPci(\Yii::app()->request->getParam('pci'))
                                        ->setPdi(\Yii::app()->request->getParam('pdi'))
                                        ->setThisTemplate($this->this_template)
                                        ->setFinishedObject(\Yii::app()->request->getParam('finished_object'))
                                        ->setModuleThisTemplate($this->module->isTemplate($extension_copy))
                                        ->setPanelData($panel_data)
                                        ->setProcessViewIndex($process_view_index)
                                        ->setBlockFieldData()
                                        ->setProcessViewLoadPanels(\Yii::app()->request->getParam('process_view_load_panels'))
                                        ->prepare();

    $fields_group_list = $process_view_builder_model->getFieldsGroupList();

    if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){
?>
<div class="process_view_block copy_id<?php echo $extension_copy->copy_id; ?> sm_extension"
     data-copy_id="<?php echo $extension_copy->copy_id; ?>"
     data-index="<?php echo $process_view_index ?>"
     data-page_name="processView"
     data-parent_copy_id="<?php echo \Yii::app()->request->getParam('pci', ''); ?>"
     data-parent_data_id="<?php echo \Yii::app()->request->getParam('pdi', ''); ?>"
     data-this_template="<?php echo (integer)$this->this_template; ?>"
     >
<div class="filter-block clearfix">

    <div class="wievs_tuggle">
        <?php
        foreach($this->getSwitchIconList($extension_copy) as $switch_icon){
            echo '<a class="'.$switch_icon['class'].'" data-action_key="'.$switch_icon['data-action_key'].'" data-type="'.$switch_icon['data-type'].'" href="javascript:void(0)"><i class="'.$switch_icon['i_class'].'"></i></a>';
        }
        ?>

        <?php
        /*
        if($this->module->list_view_icon_show['switch_to_pv']){ ?>
            <a href="javascript:void(0)"><i class="fa fa-bars active"></i></a>
        <?php } ?>
        <?php if($this->module->list_view_icon_show['switch_to_lv']){ ?>
        <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->addVars(array('module' => array('destination' => 'listView')))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class="fa fa-th-list"></i></a>
        <?php }
        */
        ?>
    </div>


	<form class="search-filter" method="get">
		<div class="search-field form-control">
            <div class="filters-installed">
                <?php echo $filters_installed ?>
            </div>
			<input type="text" class="search-input" placeholder="<?php echo Yii::t('base', 'Search'); ?>" value="<?php if(Search::$text !== null) echo Search::$text; ?>">
		</div>
    </form><!-- /.search-block -->
    <div class="btn-group crm-dropdown dropdown-right edit-dropdown">
        <button class="btn dropdown-toggle btn-create btn-round" data-toggle="dropdown"><i class="fa fa-filter"></i></button>
        <ul class="dropdown-menu dropdown-shadow filter-menu">
            <?php if($filter_menu_list_virual) echo $filter_menu_list_virual; ?>
            <li><a href="javascript:void(0);" class="filter-create" ><i class="fa fa-plus-circle"></i><?php echo Yii::t('base', 'Create filter'); ?></a></li>
            <?php if($filter_menu_list) { ?>
                <li><span class="filter-separator filters-created"><?php echo Yii::t('base', 'Created filters'); ?></span></li>
                <?php echo $filter_menu_list; ?>
            <?php } ?>
        </ul>
        <div class="hover_notif"><?= Yii::t('base', 'Filters'); ?></div>
    </div>
    <?php if(array_key_exists('pdi', $_GET)){?>
    <div class="btn-group">
        <button class="btn btn-default btn-round edit_view_show name" data-controller="module_param"><i class="fa fa-pencil"></i></button>
        <div class="hover_notif"><?= Yii::t('constructor', 'Updated'); ?></div>
    </div>
    <?php }?>
    <?php if($extension_copy->copy_id == \ExtensionCopyModel::MODULE_COMMUNICATIONS){ ?>
        <div class="btn-group">
            <button class="btn btn-default btn-round element" data-type="communications-settings"><i class="fa fa-cog"></i></button>
            <div class="hover_notif"><?= Yii::t('communications', 'Post settings'); ?></div>
        </div>
    <?php } ?>
    <div class="btn-group crm-dropdown dropdown-right edit-dropdown element local-storage"
        <?php echo ($this->module->getProcessViewBtnSorting() == false || count($fields_group_list) <= 1 ? 'style="display : none"' : ''); ?>
         data-sort_index="processView_<?php echo $extension_copy->copy_id; ?>"
         data-name="process_view_fields_group"
         data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->prepare()->getKey(); ?>"
    >
        <button class="btn dropdown-toggle btn-default btn-round sorting-arrows" data-toggle="dropdown"></button>
        <ul class="dropdown-menu dropdown-shadow">
                <?php
                    foreach($fields_group_list as $fields_group){
                ?>
                        <li class="<?php if($fields_group['active']) echo "active" ?>">
                            <a href="javascript:void(0)" data-name="<?php echo $fields_group['name']; ?>"  data-group_index="<?php echo $fields_group['group_index']; ?>"><?php echo $fields_group['title'] ?></a>
                        </li>
                <?php
                    }
                ?>
        </ul>
        <div class="hover_notif"><?= Yii::t('base', 'Sorting'); ?></div>
    </div>
    <?php if($this->module->finishedObject()){ ?>
    <div class="btn-group">
        <button class="btn btn-default btn-round element <?php if($finished_object) echo 'active'; ?>" data-type="finished_object" data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->prepare()->getKey(); ?>"><i class="fa fa-check-square"></i></button>
        <div class="hover_notif"><?= Yii::t('constructor', 'Completed'); ?></div>
    </div>
    <?php } ?>
    <div class="btn-group">
        <button class="btn btn-default btn-round element" data-type="fields_view_setting"><i class="fa fa-cog"></i></button>
        <div class="hover_notif"><?= Yii::t('base', 'Display Settings'); ?></div>
    </div>
    <?php
        if(
            $this->module->list_view_btn_templates &&
            $this->module->isTemplate($extension_copy) &&
            $extension_copy->getIsTemplate() != \ExtensionCopyModel::IS_TEMPLATE_ENABLE_ONLY &&
            in_array($this->this_template, array(EditViewModel::THIS_TEMPLATE_MODULE, EditViewModel::THIS_TEMPLATE_TEMPLATE)) &&
            !\Yii::app()->request->getParam('pci') &&
            !\Yii::app()->request->getParam('pdi')
        ){
            $template_go = ($this->this_template == EditViewModel::THIS_TEMPLATE_MODULE);
    ?>
        <div class="btn-group">
            <button <?php if(!$template_go){ ?> data-back="true" <?php } ?> class="btn btn-default btn-round navigation_module_template_link <?= !$template_go ? 'active':''; ?>" data-type="template" data-action_key="<?php echo (new \ContentReloadModel(8, $crm_properties))->addVars(['module'=>['params'=>['this_template'=>(int)!$this->this_template]]])->prepare()->getKey(); ?>"><i class="fa fa-copy"></i></button>
            <div class="hover_notif"><?= Yii::t('base', 'Templates'); ?></div>
        </div>
    <?php
        }
    ?>
    <?php
        if($this->module->process_view_btn_project){
            if(!empty($pm_extension_copy)){
    ?>
                <div class="btn-group element" data-type="drop_down">
                <div class="submodule-link btn-group crm-dropdown dropdown-right edit-dropdown element" data-type="project_menu">
                <button class="btn btn-default dropdown-toggle element max-width-btn" data-type="drop_down_button" data-toggle="dropdown"><?php echo $project_menu_pdi_active ?></button>
                <ul class="dropdown-menu element" role="menu" data-type="drop_down_list" aria-labelledby="dropdownMenu1">
                    <div class="search-section">
                        <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                    </div>
                    <div class="submodule-table">
                        <table class="table list-table">
                            <tbody>
                            <?php
                            if(!empty($project_menu_module_data)){
                                foreach($project_menu_module_data as $project_menu){
                                    $active = '';
                                    if($project_menu[$pm_extension_copy->prefix_name . '_id'] == (integer)$_GET['pdi']) $active = 'active';
                                    ?>
                                    <tr class="sm_extension_data <?php echo $active; ?>"
                                        data-id="<?php echo $project_menu[$pm_extension_copy->prefix_name . '_id']; ?>">
                                        <td>
                                            <span href="javasctript:void(0)" class="name"><?php echo $project_menu['module_title']; ?></span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                    <tr class="sm_extension_data" data-id=""><td><span href="javasctript:void(0)" class="name"><?php echo Yii::t($this->module->getModuleName() . 'Module.base', 'there are no projects') ?></span></td></tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </ul>
            </div></div>
    <?php } } ?>
    <div class="filter" style="display: none;" >
        <hr />
        <div class="filter-box-container"></div>
    </div>
</div><!-- /. filter-block -->


<div id="process_wrapper" class="process_wrapper content-panel">
    <?php } //BLOCK_1 ?>
    <?php if(ControllerModel::inContentBlock(array(ControllerModel::CONTENT_BLOCK_2, ControllerModel::CONTENT_BLOCK_3))){ ?>
    <ul class="process_list element" data-name="process_view_panel">
      <?php
        $panel_list = $process_view_builder_model->getPanelList();
        $html = '';
        if($panel_list){
            $html = implode($panel_list);
        }

        echo $html;
      ?>
    
    <?php
        if(!empty($this->module->checkProcessViewBttnAddZeroPanel()) &&
            Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))
        ){
    ?>
    <div class="btn-group">
        <button class="btn btn-create process_view_dnt-add_list"><?php echo Yii::t($this->module->getModuleName() . 'Module.base', 'Add list') ?> +</button>
    </div>
    <?php } ?>
    
    </ul>
    <?php } //BLOCK_2 ?>
    <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
</div>



<div class="element" data-type="fields_view_settings_dialog" style="display: none">
    <?php echo $this->renderPartial(ViewList::getView('dialogs/processViewFieldsViewSettings'), ['fields_view_list' => $process_view_builder_model->getFieldsViewList()], true) ?>
</div>


</div>
<?php } // BLOCK_1 ?>


<?php
$action_key_b = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList()->getKey();
$action_key_s = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(false)->getKey();
?>
<script>
    $(document).ready(function(){
        ProcessView.active_fields_view = '<?php echo $process_view_builder_model->getFieldsView(true); ?>'

        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        if(content_vars){
            content_vars = JSON.parse(content_vars);
            instanceGlobal.contentReload.addContentVars(content_vars);

            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_b; ?>, true);
            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_s; ?>, false);
        }

        ProcessView.active_fields_view = "<?php echo $process_view_builder_model->getFieldsView(true) ?>";

        //Конструктор
        var contentReload = Global.getInstance().getContentReloadInstance();
        var object = contentReload || {},
            reloadPage;

        //F5 перегрузка страницы
        if (!Object.keys(object).length) {
            reloadPage = true;
        }

        object.afterLoadView = function () {
            var instance = ProcessView.createInstance();

            //по F5
            instance
                .setCopyId(<?php echo $extension_copy->copy_id; ?>)
                .run()

            var list = [],
                data = Base.parseUrl(location.href),
                $btnFinish = $('[data-type="finished_object"]'),
                $btnTemplate = $('[data-type="template"]');

            ($btnFinish.length) ? list.push($btnFinish.attr('data-action_key')) : '';
            ($btnTemplate.length) ? list.push($btnTemplate.attr('data-action_key')) : '';

            $.each(list, function (key, value) {
                var object = instanceGlobal.contentReload._content_vars[value].module.params;

                if (data) {
                    object.pci = data.pci;
                    object.pdi = data.pdi;
                }
            });

            return this;
        }

        if (reloadPage) {
            object.afterLoadView()
        }
    });
</script>
