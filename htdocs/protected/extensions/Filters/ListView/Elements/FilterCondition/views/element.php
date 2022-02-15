<select class="element_filter edit-dropdown timeselect select" data-name="condition" <?php echo (FilterModel::$_access_to_change ? '' : 'disabled="disabled"') ?> placeholder="<?php echo Yii::t('base', 'Condition'); ?>">
    <?php
     if(!empty($filter_list)){ ?>
    <?php foreach($filter_list as $key => $value){ ?>
        <option value="<?php echo $key ?>"
                <?php if($condition_value !== null && $key == $condition_value) echo 'selected="selected"'; ?>
        ><?php echo Yii::t('filters', $value['title']); ?></option>
    <?php }
    } else { ?>
        <option value=""></option>
    <?php } ?>
</select>
