<div class="panel inputs-panel element"
     data-type="block"
     data-module="reports"
     data-unique_index="<?php echo $data['schema']['unique_index'] ?>"
     data-element_type="<?php echo $data['schema']['type']; ?>"
     <?php if($data['schema']['type'] == 'graph') echo 'data-position="'.$data['schema']['elements'][0]['position'].'"' ?>
>
    <header class="panel-heading editable-block">
        <span class="editable-field element" data-type="title" style="opacity: 1;"><?php echo $data['schema']['title']; ?></span>
        <span class="todo-actionlist actionlist-inline">
            <span class="edit-dropdown dropdown-right crm-dropdown title-edit dropdown">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle" data-toggle="dropdown"><i class="fa fa-pencil"></i></a>
                <ul class="dropdown-menu" role="menu">
                    <li><input type="text" class="form-control" value="<?php echo $data['schema']['title']?>" maxlength="50" style="width: 186px;"><pre class="invisi" style="font-size: 17px; font-weight: bold; font-family: 'CustomOpenSans', sans-serif;"><?php echo $data['schema']['title']; ?></pre></li>
                    <li><a href="javascript:void(0)" class="save-input"><?php echo Yii::t('base', 'Save')  ?></a></li>
                </ul>
            </span>
            <?php if(!empty($data['setting'])){?>
            <span class="crm-dropdown dropdown sub-module-params-cog-span">
                <a href="javascript:void(0)" class="todo-edit dropdown-toggle sub-module-params-cog" data-toggle="dropdown"><i class="fa fa-cog"></i></a>
                <?php echo $data['setting']; ?>
            </span>
            <?php } ?>
            <?php if($data['schema']['remove']){ ?>            
            <a href="javascript:void(0)" class="todo-remove element" data-type="remove_block"><i class="fa fa-times"></i></a>
            <?php } ?>
        </span>
        <span class="tools pull-right">
            <a href="javascript:;" class="fa <?php if($status = EditViewBuilder::getEvBlockDisplayStatus($data['schema']['unique_index'], 'block_fields')) echo $status; ?> element" data-type="switch"></a>
        </span>
    </header>
    
    <?php echo $data['content']; ?>

</div>