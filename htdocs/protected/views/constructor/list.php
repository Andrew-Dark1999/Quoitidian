<?php
$crm_properties = [
    '_active_object' => $this,
];
?>
<?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>

<?php
    $show_create = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_CREATE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
    $show_edit   = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
    $show_delete = Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
?>
<div class="list_view_block" data-page_name="constructor">
<div class="filter-block clearfix">
	<form class="search-filter" method="get">
        <div class="search-field form-control">
            <input type="text" class="search-input" placeholder="<?php echo Yii::t('base', 'buscar'); ?>" value="<?php echo \Search::getInstance()->setTextFromUrl()->getText(); ?>">
        </div>
	</form>
</div>
<section class="panel">
    <header class="panel-heading">
        <?php echo Yii::t('base', 'Módulos'); ?>
        <span class="tools pull-right">
	        <span class="crm-dropdown table-dropdown dropdown">
				<a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <?php $storage_params = History::getInstance()->getUserStorage(UsersStorageModel::TYPE_LIST_TH_HIDE, 'constructor'); ?>
			    <ul class="dropdown-menu local-storage" data-hidden_index="constructor" role="menu">
                    <li>
                        <div class="checkbox">
                            <label><input type="checkbox" data-group_index="1" <?php if(empty($storage_params) || !in_array(1, $storage_params)) echo 'checked="checked"' ?> ><span><?php echo Yii::t('base', 'Date created'); ?></span></label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" data-group_index="2" <?php if(empty($storage_params) || !in_array(2, $storage_params)) echo 'checked="checked"' ?> ><span><?php echo Yii::t('base', 'Date edit'); ?></span></label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" data-group_index="3" <?php if(empty($storage_params) || !in_array(3, $storage_params)) echo 'checked="checked"' ?> ><span><?php echo Yii::t('base', 'Status'); ?></span></label>
                        </div>
                    </li>
                </ul>
			</span>
        </span>
    </header>
    <div class="panel-body">
        <div class="adv-table editable-table">
            <div class="btn-section clearfix">
                <?php if($show_create){ ?>
                <div class="btn-group">
                    <input class="btn btn-primary modal_dialog" type="button" data-controller="create_module" value="<?php echo Yii::t('base', 'Crear')?> +" />
                </div>
                <?php
                    }
                    if($show_edit || $show_delete){
                ?>
                <div class="btn-group crm-dropdown edit-dropdown dropdown-right">
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Acciones'); ?></button>
                    <ul class="dropdown-menu">
                        <?php if($show_edit){ ?>
                        <li><a href="javascript:void(0)" class="btn-action" data-controller="module_copy"><?php echo Yii::t('base', 'copiar')?></a></li>
                        <?php } ?>
                        <?php if($show_delete){ ?>
                        <li><a href="javascript:void(0)" class="btn-action" data-controller="module_delete"><?php echo Yii::t('base', 'eliminar'); ?></a></li>
                        <?php } ?>
                        <?php if($show_edit){ ?>
                            <li><a href="javascript:void(0)" class="btn-action" data-controller="module_data_delete"><?php echo Yii::t('base', 'eliminar toda la información')?></a></li>
                        <?php } ?>




                    </ul>
                </div>
                <?php  } ?>
            </div>

            <div id="list-table_wrapper_all" class="list-table_wrapper_all">
                <?php } //BLOCK_1 ?>
                <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                <div class="crm-table-wrapper" id="list-table_wrapper">
                <?php } //BLOCK_2 ?>
                <?php
                    if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_3)){
                        $vars = array(
                            'selector_content_box' => '#list-table_wrapper',
                            'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_3),
                        );
                        $action_key = (new \ContentReloadModel(9, $crm_properties))->addVars($vars)->prepare()->getKey();
                    ?>

                    <table
                            class="display table table-bordered table-striped crm-table first-cell-visible list-table local-storage"
                            data-sort_index="constructor_lv"
                            id="settings-table"
                            list-table
                            data-action_key="<?php echo $action_key; ?>"
                    >
                    <thead>
                    <tr>
                        <th><span class="visible-cell"><input type="checkbox" class="checkbox"></span></th>
                        <th class="sorting <?php if(Sorting::getInstance()->fieldExists(null, 'title')) echo 'sorting_'.Sorting::$params['title']; ?>" data-name="title">
                            <span class="table-handle"><?php echo Yii::t('base', 'Nombre'); ?></span>
                            <span class="sorting-arrows"></span>
                        </th>
                        <th class="sorting <?php if(Sorting::getInstance()->fieldExists(null, 'date_create')) echo 'sorting_'.Sorting::$params['date_create']; ?>" data-name="date_create" data-group_index="1">
                            <span class="table-handle"><?php echo Yii::t('base', 'Fecha de creación'); ?></span>
                            <span class="sorting-arrows"></span>
                        </th>
                        <th class="sorting <?php if(Sorting::getInstance()->fieldExists(null, 'date_edit')) echo 'sorting_'.Sorting::$params['date_edit']; ?>" data-name="date_edit" data-group_index="2">
                            <span class="table-handle"><?php echo Yii::t('base', 'Fecha de edición'); ?></span>
                            <span class="sorting-arrows"></span>
                        </th>
                        <th class="sorting <?php if(Sorting::getInstance()->fieldExists(null, 'active_title')) echo 'sorting_'.Sorting::$params['active_title']; ?>" data-name="active_title" data-group_index="3">
                            <span class="table-handle"><?php echo Yii::t('base', 'Estatus'); ?></span>
                            <span class="sorting-arrows"></span>
                        </th>
                        <?php //if(empty(Sorting::$params) || (isset(Sorting::$params['sort']) &&  Sorting::$params['sort'] == 'asc')){ ?>
                        <th class="sorting <?php if(Sorting::getInstance()->fieldExists(null, 'sort')) echo 'sorting_'.Sorting::$params['sort']; ?>" data-name="sort" data-group_index="4">
                            <span class="table-handle"><?php echo Yii::t('base', 'Ordenar'); ?></span>
                            <span class="sorting-arrows"></span>
                        </th>
                        <?php //} ?>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            $last_extension_copy = ExtensionCopyModel::model()->changeConstructor(1)->findAll(array('select'=>'copy_id', 'order'=>'sort'));
                            if(!empty($extension_copy_data)){								
                            foreach($extension_copy_data as $value){							
                        ?>
                            <tr data-copy_id="<?php echo $value->copy_id; ?>">
                                <td>
                                    <span class="visible-cell"><?php echo CHtml::checkBox('module_id', '', array('class' => 'input_ch checkbox', 'title'=>$value->title)); ?></span>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="modal_dialog name" data-controller="edit_module" >
                                        <?php echo $value->title; ?>
                                    </a>
                                </td>
                                <td><?php echo $this->getDateTime($value->date_create, true); ?></span></td>
                                <td><?php echo $this->getDateTime($value->date_edit, true); ?></span></td>
                                <td class="non-overflow">
                                    <div class="relative">
                                        <?php
                                        if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){
                                            echo CHtml::dropDownList('status', $value->active, array( 1=> Yii::t('base', 'Público'),
                                                0=> Yii::t('base', 'Oculto')
                                            ),
                                                array(
                                                    'class' => 'select module_set_status',
                                                )
                                            );
                                        } else {
                                            if($value->active) echo Yii::t('base', 'Público');
                                            else echo Yii::t('base', 'Oculto');
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                <?php
                                    if(empty(Sorting::$params) || (isset(Sorting::$params['sort']) &&  Sorting::$params['sort'] == 'asc')){
                                        if($last_extension_copy[count($last_extension_copy)-1]->copy_id != $value->copy_id){
                                ?>
                                            <a href="javascript:void(0)" class="navigation_module_down"><i class="fa fa-arrow-circle-down"></i></a>
                                <?php
                                        }
                                        if($last_extension_copy[0]->copy_id != $value->copy_id){
                                ?>
                                            <a href="javascript:void(0)" class="navigation_module_up"><i class="fa fa-arrow-circle-up"></i></a>
                                <?php
                                        }
                                     } else echo '-'; ?>
                                </td>
                            </tr>
                        <?php
                            }
                            }
                        ?>
                    </tbody>
                    </table>
                    <?php } // BLOCK_3 end ?>

                    <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_2)){ ?>
                </div>
                <?php
                    $vars = array(
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                    );
                    $action_key = (new \ContentReloadModel(9, $crm_properties))->addVars($vars)->prepare()->getKey();
                ?>
                <div class="row local-storage element pagination-block" data-type="pagination_block" data-action_key="<?php echo $action_key; ?>" data-pagination_index="constructor_lv">
                    <div class="col-sm-5">
                        <div class="dataTables_info" id="settings-table_info" role="status" aria-live="polite">
                            <?php echo Pagination::getInstance()->getPaginatorReport(); ?>
                        </div>
                        <?php echo Pagination::getInstance()->getPaginatorSize(); ?>
                    </div>
                    <div class="col-sm-7 pull-right">
                        <?php echo Pagination::getInstance()->getPaginatorView(); ?>
                    </div>
                </div>
            <?php } //BLOCK_2 end ?>
                <?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
            </div>
        </div>
    </div>
</section>
</div>

<?php } //BLOCK_1 end  ?>


<?php
    $action_key_b = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(true)->getKey();
    $action_key_s = (new \ContentReloadModel(null, $crm_properties))->prepareModuleList(false)->getKey();
?>


<script type="text/javascript">

    $(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        if(content_vars){
            content_vars = JSON.parse(content_vars);
            instanceGlobal.contentReload.addContentVars(content_vars);

            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_b; ?>, true);
            instanceGlobal.contentReload.actionSetVarsToGeneralContent(<?php echo $action_key_s; ?>, false);
        }

    });


    constructor_create_module_id ="<?php if(isset($_GET['module_id'])) echo $_GET['module_id']; else echo 1; ?>"

</script>
