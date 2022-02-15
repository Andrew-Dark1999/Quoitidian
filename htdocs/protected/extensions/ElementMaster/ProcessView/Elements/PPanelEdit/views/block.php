<?php echo $content ?>
<a href="javascript:void(0)" class="process_view-save-input"><?php echo Yii::t('base', 'Save'); ?></a>


<script>
    $.each($('.process_view-save-input'), function () {
        $(this).closest('li[data-name="panel"]').attr('data-update-title', 'true');
    })
</script>

