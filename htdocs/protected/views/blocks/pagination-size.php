<div class="dataTables_length" id="list-table_length">
    <label>
        <select class="select list-table-length pagination_size" name="list-table_length">
            <?php foreach($page_sizes as $key => $value): ?>
                <option <?php if($active_page_size == $key) echo 'selected="selected"' ?>  value="<?php echo $key ?>"><?php echo Yii::t('base', $value); ?></option>
            <?php endforeach; ?>
        </select>
        <?php echo Yii::t('base', 'records per page'); ?>
    </label>
</div>
