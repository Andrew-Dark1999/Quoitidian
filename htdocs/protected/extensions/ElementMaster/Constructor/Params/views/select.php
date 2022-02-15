<li>
    <span class="drag-marker"><i></i></span>
	<input
        type="text"
        class="form-control element_params"
        data-type="select_option"
        data-id="<?php  echo $select_params['id'] ?>"
        data-remove="<?php echo (integer)$select_params['btn_remove']; ?>"
        data-finished_object="<?php echo (integer)$select_params['finished_object']; ?>"
        data-sort="<?php echo (isset($select_params['select_sort'])) ? (integer)$select_params['select_sort'] : 0; ?>"
        data-slug="<?php echo $select_params['slug']; ?>"
        value="<?php  echo $select_params['value'] ?>"
        placeholder="<?php echo Yii::t('base', 'Value'); ?>"
        style="width:250px; display: inline-block; vertical-align: middle;"
        <?php echo ($select_color_block == true ? 'data-color="'.$select_params['select_color'].'"' : '') ?>
        />
    <?php if($select_params['btn_remove']){ ?>
        <a href="javascript:void(0)" class="todo-remove" data-element="select" ><i class="fa fa-times"></i></a>
    <?php } ?>
    <?php if($select_color_block == true){  ?>
    <select class="selectpicker select-color">
        <option data-content="<span class='label label-gray' data-color='gray'></span>"></option>
        <option data-content="<span class='label label-orange' data-color='orange'></span>"></option>
        <option data-content="<span class='label label-yellow' data-color='yellow'></span>"></option>
        <option data-content="<span class='label label-green' data-color='green'></span>"></option>
        <option data-content="<span class='label label-sea' data-color='sea'></span>"></option>
        <option data-content="<span class='label label-blue' data-color='blue'></span>"></option>
        <option data-content="<span class='label label-violet' data-color='violet'></span>"></option>
    </select>
    <?php } ?>
</li>
