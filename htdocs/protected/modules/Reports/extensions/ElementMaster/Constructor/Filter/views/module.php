<select class="select element first_empty <?php echo (!$element['show_module_copy_id'] ? 'hide' : ''); ?>" data-type="module_copy_id">
    <option value=""><?php echo \Yii::t('ReportsModule.base', 'Module name'); ?></option>
    <?php
        $modules = (!empty($schema['data']['modules']) ? $schema['data']  : null) ;
        if(empty($schema['data']['modules'])){
            $modules = \Reports\extensions\ElementMaster\Schema::getInstance()->getModulesForFilterPanel($element);
        }

        if(!empty($modules))
            foreach($modules['modules'] as $module){
    ?>
        <option
            value="<?php echo $module['module_copy_id']; ?>"
            <?php if($module['module_copy_id'] == $element['module_copy_id']) echo 'selected="selected"' ?>
        ><?php echo $module['title']; ?></option>
    <?php } ?>
</select>
