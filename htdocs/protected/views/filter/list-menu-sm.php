<?php if(!empty($lists)){ ?>
    <?php
        foreach($lists as $list){
            if(isset($attr[$list['name']]))
                $attr = $attr[$list['name']];
            else 
                $attr = null;
    ?>
        <li><a href="javascript:void(0)"
               class="submodule-filter-btn-set <?php if(isset($attr['class_a']) && $attr['class_a']) echo $attr['class_a']; ?>"
               data-id="<?php echo $list['filter_id']; ?>"
               data-name="<?php echo $list['name']; ?>" ><?php if(isset($attr['class_icon']) && $attr['class_icon']) { ?><i class="fa <?php echo $attr['class_icon']; ?>"></i><?php } ?><?php echo $list['title']; ?></a></li>
    <?php } ?>
<?php } ?>