<select class="element_language edit-dropdown">
    <?php if(!empty($language)){ ?>
    <?php foreach($language as $value){ ?>
        <option <?php if($language_value == $value->name) echo 'selected="selected"'; ?> value="<?php echo $value->name ?>"><?php echo $value->title ?></option>
    <?php }
    } else { ?>
        <option value=""></option>
    <?php } ?>
</select>
