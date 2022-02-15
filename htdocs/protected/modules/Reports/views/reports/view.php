<?php
$crm_properties = [
    '_active_object' => $this,
    '_extension_copy' => $extension_copy,
    '_card_id' => $reports_id,
];
?>
<?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){     ?>
<div class="list_view_block copy_id<?php echo $extension_copy->copy_id; ?> sm_extension sm_extension_data"
     data-copy_id="<?php echo $extension_copy->copy_id; ?>"
     data-page_name="listView"
     data-parent_copy_id=""
     data-parent_data_id=""
     data-this_template="<?php echo (integer)$this->this_template; ?>"
     data-module="reports"
     data-id="<?php echo $reports_id; ?>"
>

<div class="filter-block clearfix">
	<form class="search-filter" method="get">
		<div class="search-field form-control">
            <div class="filters-installed">
                <?php echo $filters_installed ?>
            </div>
			<input type="text" class="search-input" placeholder="<?php echo \Yii::t('base', 'Search'); ?>" value="<?php if(Search::$text !== null) echo Search::$text; ?>">
		</div>
    </form><!-- /.search-block -->
    <div class="btn-group crm-dropdown dropdown-right edit-dropdown">
        <button class="btn dropdown-toggle btn-create btn-round" data-toggle="dropdown"><i class="fa fa-filter"></i></button>
        <ul class="dropdown-menu dropdown-shadow filter-menu">
            <li><a href="javascript:void(0);" class="filter-create" ><i class="fa fa-plus-circle"></i><?php echo \Yii::t('base', 'Create filter'); ?></a></li>
            <?php if($filter_menu_list) { ?>
                <li><span class="filter-separator filters-created"><?php echo \Yii::t('base', 'Created filters'); ?></span></li>
                <?php echo $filter_menu_list; ?>
            <?php } ?>
        </ul>
        <div class="hover_notif"><?= \Yii::t('base', 'Filters'); ?></div>
    </div>

    <div class="btn-group">
        <button class="btn btn-default btn-round edit_view_constructor_show" data-controller="module_param_report"><i class="fa fa-pencil"></i></button>
        <div class="hover_notif"><?= Yii::t('constructor', 'Updated'); ?></div>
    </div>
    <?php // кнопка списка Отчетов
        if(!empty($reports_model_list)){
    ?>
            <div class="btn-group element"
                 data-type="drop_down"
            >
                <div class="submodule-link btn-group crm-dropdown dropdown-right edit-dropdown element" data-type="reports_menu">
<!--                    data-id="--><?php //echo $report_model->reports_id; ?><!--"-->
                    <button class="btn btn-default dropdown-toggle element max-width-btn" data-toggle="dropdown" data-type="drop_down_button" ><?php echo $report_model->module_title; ?></button>
                    <ul class="dropdown-menu element" role="menu" aria-labelledby="dropdownMenu1"
                        data-type="drop_down_list"
                        data-there_is_data="0"
                        data-relate_copy_id="<?php echo $extension_copy->copy_id; ?>"
                    >

                        <div class="search-section">
                            <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                        </div>

                        <div class="submodule-table">
                            <table class="table list-table">
                                <tbody>
                                <?php
                                if(!empty($reports_model_list)){
                                    foreach($reports_model_list as $report){
                                        $active = '';
                                        if($report['reports_id']== $report_model->reports_id) $active = 'active';
                                        ?>
                                        <tr class="sm_extension_data <?php echo $active; ?>"
                                            data-id="<?php echo $report['reports_id']; ?>">
                                            <td>
                                                <span href="javasctript:void(0)" class="name"><?php echo $report['module_title']; ?></span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr class="sm_extension_data" data-id=""><td><span href="javasctript:void(0)" class="name"><?php echo \Yii::t($this->module->getModuleName() . 'Module.messages', 'there are no reports') ?></span></td></tr>
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
    <div class="filter" style="display: none;" >
        <hr />
        <div class="filter-box-container"></div>
    </div>

     <div class="input-daterange input-group">
        <input type="text" class="input-sm form-control element" data-type="dis" name="start" value="<?php echo  \Helper::formatDate($filters['_date_interval_start']); ?>" />
        <span class="input-group-addon"><?php echo \Yii::t('ReportsModule.base', 'to'); ?></span>
        <input type="text" class="input-sm form-control element" data-type="die" name="end" value="<?php echo  \Helper::formatDate($filters['_date_interval_end']); ?>" />
    </div>


    
</div>
<!-- /. filter-block -->
<div class="report-content content-panel">

<?php echo $this->content_report; ?>


<div class="list-view-panel ">
    <section class="panel">
        <header class="panel-heading">
            <?php
                echo $report_model->module_title;
            ?>
            
            <span class="tools  pull-right">
		        <span class="crm-dropdown table-dropdown dropdown">
					<a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                    <ul class="dropdown-menu dropdown-shadow local-storage" data-hidden_index="<?php echo 'report_' . \ExtensionCopyModel::MODULE_REPORTS . '_' . $reports_id; ?>" role="menu">
                        <li>
                        <?php
                            $indicators = \Reports\extensions\ElementMaster\Schema::getInstance()->getDataAnalysisEntityesBySchema($schema);
                            $storage_params = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE, 'report_' . \ExtensionCopyModel::MODULE_REPORTS . '_' . $reports_id);
                            if(!empty($indicators)){
                                $i = 0;
                                foreach($indicators as $indicator){
                                    $fn = 'f' . $indicator['unique_index'];
                                    if($i===0){ $fn = 'param_x'; }
                                    $i++;
                                    $checked = 'checked="checked"';
                                    if(!empty($storage_params) && in_array($fn, $storage_params)){
                                        $checked = '';
                                    }
                            ?>

                                <div class="checkbox">
                                    <label><input type="checkbox" <?php echo $checked; ?> data-group_index="<?php echo $fn ?>" ><span><?php echo $indicator['title']; ?></span></label>
                                </div>
                                <?php

                                ?>

                            <?php
                                }
                            } else {
                                echo '<li><label class="no_checkbox">' . \Yii::t('messages', 'field is not set') . '</label></li>';
                            }
                        ?>
                        </li>
				    </ul>
				</span>
            </span>
            
        </header>
        
        
        <div class="panel-body element" data-type="block">
            <div class="adv-table editable-table">
                <div class="btn-section clearfix">
                    <!-- BUTTONS -->
                    <?php
                    //btn Tools
                    $btn_tools_list = Yii::app()->controller->module->getListViewBtnToolsList();
                    if($btn_tools_list){
                        ?>
                        <div class="btn-group crm-dropdown edit-dropdown instruments">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Tools'); ?></button>
                            <ul class="dropdown-menu dropdown-shadow">
                                <?php foreach($btn_tools_list as $tools){ ?>
                                    <li><a href="javascript:void(0)" class="<?php echo $tools['class']; ?>"><?php echo $tools['title']; ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div id="list-table_wrapper_all" class="list-table_wrapper_all">
                    <?php } //BLOCK_1 ?>
                    <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                    <div class="crm-table-wrapper reports-det-table element" data-type="table" id="list-table_wrapper">
                    <?php } //BLOCK_2

                        if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_3)){
                            \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Table\Table',
                                array(
                                    'schema' => $schema,
                                    'table_data' => $table_data,
                                ));
                        } // BLOCK_3 end

                        if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){
                    ?>
                    </div>

                    <?php
                        $vars = array(
                            'selector_content_box' => '#list-table_wrapper_all',
                            'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                        );
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
                                <?php echo \Pagination::getInstance()->getPaginatorReport(); ?>
                            </div>
                            <?php echo \Pagination::getInstance()->getPaginatorSize(); ?>
                        </div>
                        <div class="col-sm-6 pull-right">
                            <?php echo \Pagination::getInstance()->getPaginatorView(); ?>
                        </div>
                    </div>
                    <?php } // BLOCK_2 end ?>
                    <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
                </div>
           </div>
        </div>
    </section>
</div><!-- /.list-view-panel -->

</div>
</div>

<?php } // BLOCK_1 ?>


<?php
    $action_key_b = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(true)->getKey();
    $action_key_s = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(false)->getKey();
?>


<script type="text/javascript">
    $(document).ready(function () {
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>',
            key_b = [ <?php echo $action_key_b; ?>, true ],
            key_s = [<?php echo $action_key_s; ?>, false];

        instanceGlobal.contentReload.setVarsFromPage(content_vars, key_b, key_s);

        Reports.setReportsId(<?php echo $reports_id; ?>);
    })
</script>


<script>
    $(document).ready(function () {
            if (Global.isReport()) {
                $('.list_view_btn-print').addClass('is-page-report');
            }
    });
</script>
