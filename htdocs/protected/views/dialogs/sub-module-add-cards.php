<?php $sm_unique_index = substr(md5(date('YmdHisu')), 0, 10);  ?>
<div class="modal-dialog" style="width: 620px;">
<section class="panel">
    <div class="submodule-view sm_extension_relate_submodule"
        data-parent_copy_id="<?php echo $parent_copy_id; ?>"
        data-parent_data_id="<?php echo $parent_data_id; ?>"
        data-copy_id="<?php echo $extension_copy->copy_id; ?>"
        data-this_template="<?php echo $this_template; ?>"
        data-relate_template="<?php echo $relate_template; ?>"
        data-sm_unique_index="<?php echo $sm_unique_index;  ?>"
    >
        <header class="panel-heading">
            <?php echo Yii::t('base', 'Link elements to the submodule'); ?>
            <span class="tools pull-right">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>

        <div class="element" data-type="drop_down">
            <div
                class="panel-body element"
                data-type="drop_down_list"
                data-there_is_data="1"
                data-relate_copy_id="<?php echo $extension_copy->copy_id; ?>"
            >
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                    <?php if($filters) { ?>
                        <div class="btn-group crm-dropdown dropdown-left edit-dropdown submodule-filters filter-block">
                            <button class="btn dropdown-toggle btn-create btn-round" data-toggle="dropdown"><i class="fa fa-filter"></i></button>
                            <ul class="dropdown-menu dropdown-shadow filter-menu" style="height: auto;">
                                <div class="dropdown-menu">
                                <li><span class="filter-separator filters-created"><?php echo Yii::t('base', 'Created filters'); ?></span></li>
                                <?php  echo $filters;  ?>
                                </div>
                            </ul>
                            <div class="hover_notif"><?php echo Yii::t('base', 'Filters'); ?></div>
                            <span class="filter-install hidden" data-filter_id="" data-name="">
                                <span></span>
                                <button type="button" class="filter-btn-take-off fa fa-times"></button>
                            </span>
                        </div>
                    <?php } ?>
                </div>
                <div class="submodule-table">
                    <table class="table list-table">
                    <tbody>
                            <?php
                                if(!empty($list_view_data))
                                foreach($list_view_data as $value){
                            ?>
                                <tr class="sm_extension_data"
                                    data-id="<?php echo $value[$extension_copy->prefix_name . '_id']; ?>"
                                >

                                    <td>
                                    <?php
                                    if($extension_copy->extension->name == 'Permission'){
                                       $schema_field = $extension_copy->getFieldSchemaParams('access_id');
                                        if(empty($schema_field))
                                            $value_field = Yii::t('messages', 'None data');
                                        else
                                            $value_field = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                                                                                          array(
                                                                                            'extension_copy' => $extension_copy,
                                                                                            'params' => $schema_field['params'],
                                                                                            'value_data' => $value,
                                                                                            'file_link' => false,
                                                                                           )
                                                                                          ,
                                                                                           true);

                                        $schema_field = $extension_copy->getFieldSchemaParams('permission_code');
                                        if(!empty($schema_field)){
                                            $value_field = $value_field . ' => ' . Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                                                                                                                array(
                                                                                                                    'extension_copy' => $extension_copy,
                                                                                                                    'params' => $schema_field['params'],
                                                                                                                    'value_data' => $value,
                                                                                                                    'file_link' => false,
                                                                                                                )
                                                                                                                ,
                                                                                                                true);
                                        }
                                    ?>
                                    <span href="javasctript:void(0)" class="name"><?php echo $value_field; ?></span>
                                    <?php
                                    } else {

                                        $schema_field = $extension_copy->getFirstFieldParamsForRelate();

                                        if($schema_field === null){
                                            $value_field = Yii::t('messages', 'None data');
                                        } else {
                                            $value_field = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.TData.TData'),
                                                                                        array(
                                                                                            'extension_copy' => $extension_copy,
                                                                                            'params' => $schema_field['params'],
                                                                                            'value_data' => $value,
                                                                                            'file_link' => false,
                                                                                            'relate_add_avatar' => true,
                                                                                        ),
                                                                                        true);
                                        }
                                    ?>
                                        <span href="javasctript:void(0)" class="name"><?php echo $value_field; ?></span>
                                    <?php } ?>
                                    </td>
                                    <td><input type="checkbox" class="checkbox"></td>
                                </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                        </table>
                </div>
            </div>
            <div class="buttons-section">
                <button type="submit" class="btn btn-primary submodule_list_view_btn-tie"><?php echo Yii::t('base', 'Link')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
            </div>
            </br>
        </div>
    </div>
</section>
</div>                            
                                
            
<script type="text/javascript">
    $(document).off('change', '.element[data-type="drop_down"] .element[data-type="drop_down_list"] .submodule-search');

    DropDownListObj
        .createInstance()
        .setParent($('.sm_extension_relate_submodule[data-sm_unique_index="<?php echo $sm_unique_index;  ?>"] .element[data-type="drop_down"]'))
        .setGroupData(DropDownListObj.GROUP_DATA_SM_OPTION_LIST)
        .setPostData(<?php echo json_encode($vars); ?>)
        .run();

</script>


