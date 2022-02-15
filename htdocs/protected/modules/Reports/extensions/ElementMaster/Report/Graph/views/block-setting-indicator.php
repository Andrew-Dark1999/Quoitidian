<li>
    <select class="selectpicker element" data-type="indicator">
        <?php 
            if(!empty($schema['data']['indicators']))
            foreach($schema['data']['indicators'] as $indicator){
        ?>
        <option
            value="<?php echo $indicator['unique_index']; ?>"
            <?php if(!empty($select_indicator) && $indicator['unique_index'] == $select_indicator)  echo 'selected="selected"' ?>
         ><?php echo $indicator['title']; ?></option>
        <?php } ?>                 
    </select>
    <?php if($element_remove == true){ ?>
        <a href="javascript:void(0)" class="element" data-type="remove_indicator"><i class="fa fa-times"></i></a>
    <?php } ?>

    
</li>


