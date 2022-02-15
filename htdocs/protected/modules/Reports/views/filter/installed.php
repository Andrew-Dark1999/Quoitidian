<?php
    if(!empty($filter_id_list)){
    foreach($filter_id_list as $filter_id){
        $filter_data = \Reports\models\ReportsFilterModel::model()->onlyPersonal()->find('filter_id=:filter_id', array(':filter_id'=>$filter_id));

        if(!$filter_data){
            $filter_data = array();
            \FilterVirtualModel::getInstance()
                                    ->setExtensionCopy($extension_copy)
                                    ->appendFiltes(array($filter_id))
                                    ->marge($filter_data);
            if(!empty($filter_data)) $filter_data = $filter_data[0];
         }
         
         if(empty($filter_data)) continue;
    ?>
    <span class="filter-install" data-filter_id="<?php echo $filter_data->filter_id; ?>" data-name="<?php echo $filter_data->name; ?>">
        <span><?php echo $filter_data->title; ?></span>
        <button type="button" class="filter-btn-take-off fa fa-times"></button>
    </span>
    <?php } ?>
<?php } ?>
