<div class="dataTables_paginate paging_bootstrap pagination" id="list-table_paginate">
    <ul>
        <li class="info">
            <?php
                $curr_page = 1;
                foreach($pages as $key => $value) {
                    if(isset($value['class']) && $value['class'] == 'active'){
                        $curr_page = $key;
                        break;
                    }
                }
                $count = count($pages);
            ?>
            <?= Yii::t('base', 'Page'); ?>&nbsp;&nbsp;<input data-max-page="<?= $count; ?>" maxlength="5" class="form-control" type="text" name="page" value="<?= $curr_page; ?>" />&nbsp;&nbsp;<?= Yii::t('base', 'from'); ?>&nbsp;&nbsp;<?= $count; ?>&nbsp;&nbsp;
        </li>
        <li class="prev <?php echo $previous['class'] ?>"><a data-active_page="<?php echo $previous['active_page']; ?>" href="javascript:void(0)">← <?php echo Yii::t('base', 'Previous'); ?></a></li>
        <li class="next <?php echo $next['class'] ?>"><a data-active_page="<?php echo $next['active_page']; ?>" href="javascript:void(0)"><?php echo Yii::t('base', 'Next'); ?> → </a></li>
    </ul>
</div>
