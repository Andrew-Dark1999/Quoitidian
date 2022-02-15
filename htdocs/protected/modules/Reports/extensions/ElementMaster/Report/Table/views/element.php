<div class="sm_extension"
     data-copy_id="<?php if(!empty(Reports\extensions\ElementMaster\Report\Table\Table::$_parent_extension_copy)) echo Reports\extensions\ElementMaster\Report\Table\Table::$_parent_extension_copy->copy_id; ?>"
     data-parent_copy_id=""
     data-parent_data_id=""
     data-this_template="0"
>
    <?php
        $crm_properties = [
            '_active_object' => Yii::app()->controller,
            '_extension_copy' => \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_REPORTS),
        ];
        $vars = array(
            'selector_content_box' => '#list-table_wrapper',
            'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_3),
        );
        $action_key = (new \ContentReloadModel(8, $crm_properties))->addVars($vars)->prepare()->getKey();
    ?>
    <table class="table table-bordered table-striped crm-table first-cell-visible list-table local-storage"
           data-sort_index="listView_<?php echo \ExtensionCopyModel::MODULE_REPORTS; ?>_2"
           id="list-table"
           data-action_key="<?php echo $action_key; ?>"
    >
        <!-- thead -->
        <thead>
        <tr>
        <?php
            $param_x = true;
            $i = 0;
            foreach($indicators as $indicator){

                $sort_fn = 'f'.$indicator['unique_index'];
                if($param_x){
                    $param_x = false;
                    if($this->showColumn('param_x') == false) continue;
                    $sort_fn = 'param_x';
                }
        ?>
                <th id="order-<?php echo $i; ?>"
                    class="draggable sorting <?php if(\Sorting::getInstance()->fieldExists(null, $sort_fn)) echo 'sorting_'.\Sorting::$params[explode(',', $sort_fn)[0]]; ?>"
                    data-group_index="<?php echo $sort_fn; ?>"
                    data-name="<?php echo $sort_fn; ?>"
                >
                    <span class="table-handle"><?php echo $indicator['title']; ?></span>
                    <span class="sorting-arrows"></span>

                </th>
        <?php

                $i++;
            }
         ?>
        </tr>
        </thead>
        <tbody>
        <!-- tbody -->
        <?php
            if(!empty($table_data) && !empty($indicators)){
                foreach($table_data as $row){
        ?>
                    <tr class="sm_extension_data" data-id="<?php echo $row['id']?>">
                    <?php
                    foreach($row as $unique_index => $value){
                        if($this->showColumn($unique_index) == false) continue;

                        $td = $this->getTd($unique_index, $value, $row['id']);
                        if($td === false) continue;
                        ?>
                        <td><?php echo $td; ?></td>
                        <?php
                    }
                ?>
            </tr>
        <?php }
            }
        ?>

        </tbody>
    </table>
</div>
