<li>
    <select class="selectpicker element" data-type="indicator">
        <?php
            if(!empty($schema['data']['indicators'])){
            foreach($schema['data']['indicators'] as $indicator){
        ?>
        <option
            value="<?php echo $indicator['unique_index'] ?>"
            <?php if(isset($element) && !empty($element['indicator'])  && $indicator['unique_index'] == $element['indicator'])  echo 'selected="selected"' ?>
         ><?php echo $indicator['title']; ?></option>
        <?php }} else { ?>
                <option value=""><?php echo \Yii::t('ReportsModule.base', 'Default indicator'); ?></option>
        <?php  } ?>
    </select>
</li>
