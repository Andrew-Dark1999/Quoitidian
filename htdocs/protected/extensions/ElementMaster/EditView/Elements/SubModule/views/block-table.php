<!-- SubModule -->
<div class="panel-body sm_extension" data-type="submodule"
                                     data-relate_copy_id="<?php echo $schema['params']['relate_module_copy_id'] ?>"
                                     data-relate_template="<?php echo (integer)$schema['params']['relate_module_template'] ?>"
                                     data-relate_table_module_id="<?php echo $table_module_relate->id ?>"
>
<div class="operations table-operations">
    <?php
        if(!empty($relate_links))
        foreach($relate_links as $link){
            if((boolean)$link['checked'] == false) continue;
    
    ?>
        <a href="javascript:void(0)" class="add-field-action submodule_edit_view_dnt-<?php echo $link['value'] ?>"><?php echo $link['title'] ?></a>
    <?php } ?>
</div>
<div class="crm-table-wrapper">
    <table class="crm-table table">
        <thead>
            <tr>
                <?php
                    if(empty($schema['params']['values'])){
                ?>
                    <th>
                        <?php echo Yii::t('base', 'Not specified fields to display'); ?>
                    </th>
                <?php
                    } else {
                        $schema['params']['values'] = Fields::getInstance()->UnGroupFieldIfTitleSimilar($schema['params']['values']);
                ?>
                
                <th><input type="checkbox" class="checkbox"></th>
                <?php
                    $field_params = array();
                    $schema_parse = $extension_copy_relate->getSchemaParse();
                    $params = SchemaConcatFields::getInstance()
                                    ->setSchema($schema_parse['elements'])
                                    ->setWithoutFieldsForListViewGroup(null)
                                    ->parsing()
                                    ->prepareWithOutDeniedRelateCopyId()
                                    ->primaryOnFirstPlace()
                                    ->prepareWithConcatName()
                                    ->getResult();
                    
                    foreach($params['header'] as $value){
                        $fields = explode(',', $value['name']);
                        if(isset($params['params'][$fields[0]]['list_view_visible']) && (bool)$params['params'][$fields[0]]['list_view_visible'] == false) continue;
                        if(isset($params['params'][$fields[0]]['display']) && (bool)$params['params'][$fields[0]]['display'] == false) continue;
                        if(!in_array($params['params'][$fields[0]]['name'], $schema['params']['values'])) continue;
                        foreach($fields as $field)  if(isset($params['params'][$field])) $field_params[$field] = $params['params'][$field];
                    ?>
                        <th><?php echo ListViewBulder::getFieldTitle(array('title'=>$value['title']) + $params['params'][$fields[0]]); ?></th>
                    <?php
                        
                    } 
                ?>
                <th>
                    <!--<i class="fa fa-pencil" title="<?php //echo yii::t('base', 'Edit'); ?>"></i>-->
                </th>
                <?php
                    } 
                ?>
            </tr>
        </thead>
        <tbody>
    <?php 
        $i=0;
        if(empty($schema['params']['values'])){
        ?>
            <tr>
                <td></td>
            </tr>
        <?php
        } else
        if(empty($extension_copy_data)){
        ?>
            <tr >
                <td colspan="<?php echo count($schema['params']['values'])+3; ?>"><?php echo Yii::t('base', 'Data not available'); ?></td>
            </tr>
        <?php
        } else
            Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.SubModule.TBody'), array(
                                                                                                        'parent_field_schema' => $schema,
                                                                                                        'extension_copy_data' => $extension_copy_data,
                                                                                                        'extension_copy_relate' => $extension_copy_relate,
                                                                                                        'field_params' => $field_params,
                                                                                                        )
                                                                                                    );
            
           
    ?>    
        </tbody>
    </table>
</div>
</div>


<?php
/*
    if($this->module_parent_data_id === null &&
       \EditViewModel::checkOnObjectInstance($extension_copy->copy_id, $schema['params']['relate_module_copy_id']) &&
       \Yii::app()->controller->module->extensionCopy->copy_id != \ExtensionCopyModel::MODULE_PROCESS)
    {
?>
<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(function(){
            Message.show('<?php echo \EditViewModel::getObjectInstanceConfirmMessage(); ?>', false);
        }, 500);
    });
</script>
<?php } */ ?>

<!-- SubModule END -->
