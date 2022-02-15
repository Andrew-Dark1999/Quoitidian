<div class="reports-cell <?php echo $element['color']; ?> element"
    data-type="panel"
    data-unique_index="<?php echo $element['unique_index']; ?>"
>
    <div class="reports-box">
        <div class="reports-numb"><?php echo \Reports\models\ConstructorModel::formatIndicatorValue($schema, $element, 'indicator_number', array('percent_value' => '%')); ?></div>
        <div class="reports-name"><?php echo \Reports\models\ConstructorModel::formatIndicatorValue($schema, $element, 'title'); ?></div>
    </div>
</div>
