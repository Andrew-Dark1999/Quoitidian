<li class="element" data-type="setting">
    <select class="selectpicker reports-mark element first_empty" data-type="indicator" data-unique_index="<?php echo $element['unique_index'] ?>" data-module_copy_id="<?php echo $element['module_copy_id']; ?>" data-field_name="<?php echo $element['field_name']; ?>" >
        <option value=""><?php echo \Yii::t('ReportsModule.base', 'Indicator'); ?></option>
        <?php 
            if(!empty($schema['data']['indicators']))
            foreach($schema['data']['indicators'] as $data){
                ?>
            <option
                data-unique_index="<?php echo $data['unique_index'] ?>"
                data-module_copy_id="<?php echo $data['module_copy_id']; ?>"
                data-field_name="<?php echo $data['field_name']; ?>"
                <?php if(isset($element) && !empty($element['unique_index'])  && $data['unique_index'] == $element['unique_index'])  echo 'selected="selected"' ?>
            ><?php echo $data['title']; ?></option>
        <?php } ?>
    </select>
    <select class="selectpicker reports-color select-color element" data-type="color">
        <?php foreach(array('gray','orange','yellow','green','sea','blue','violet') as $color){ ?>
            <option value="<?php echo $color; ?>" data-content="<span class='label label-<?php echo $color; ?>' data-color='<?php echo $color; ?>'></span>" <?php if($element['color'] == $color) echo 'selected="selected"'; ?>></option>
        <?php } ?>
    </select>
    
    <?php if($element['remove'] == true){ ?>
        <a href="javascript:void(0)" class="todo-remove element" data-type="remove_panel"><i class="fa fa-times"></i></a>
    <?php } ?>
    <span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span>
</li>


