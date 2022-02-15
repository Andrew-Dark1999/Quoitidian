<select class="element_filter edit-dropdown timeselect select list-modules" data-name="module" placeholder="<?php echo \Yii::t('base', 'Modules'); ?>">
    <?php
     if(!empty($this->modules)){ ?>
    <?php foreach($this->modules as $module){ ?>
        <option value="<?php echo $module['module_copy_id'] ?>"
                <?php if($this->selected_copy_id !== null && $this->selected_copy_id == $module['module_copy_id']) echo 'selected="selected"'; ?>
        ><?php echo $module['title']; ?></option>
    <?php }
    } else { ?>
        <option value=""></option>
    <?php } ?>
</select>
