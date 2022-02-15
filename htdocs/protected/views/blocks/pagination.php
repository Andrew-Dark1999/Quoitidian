<div class="dataTables_paginate paging_bootstrap pagination" id="list-table_paginate">
    <ul>
        <li class="prev <?php echo $previous['class'] ?>"><a data-active_page="<?php echo $previous['active_page']; ?>" href="javascript:void(0)">← <?php echo Yii::t('base', 'Previous'); ?></a></li>
        <?php foreach($pages as $value): ?>
            <li <?php if(isset($value['class'])) echo 'class="' . $value['class'] . '"'; ?>><a class="page" data-active_page="<?php echo $value['active_page']; ?>" href="javascript:void(0)"><?php echo $value['title']; ?></a></li>
        <?php endforeach; ?>
        <li class="next <?php echo $next['class'] ?>"><a data-active_page="<?php echo $next['active_page']; ?>" href="javascript:void(0)"><?php echo Yii::t('base', 'Next'); ?> → </a></li>
    </ul>
</div>
