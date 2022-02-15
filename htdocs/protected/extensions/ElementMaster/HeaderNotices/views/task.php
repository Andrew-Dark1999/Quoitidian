<li
    class="element <?php echo (!empty($data['new']) ? 'new' : ''); ?>"
    data-type="notice"
    data-id="<?php echo $data['zadachi_id']; ?>"

>
    <a class="ajax_content_reload" data-action_key="<?php echo $action_key; ?>" href="javascript:void(0)">
        <div class="task-info clearfix">
            <div class="desc pull-left">
                <?php
                    $color = '';
                    $date_full = $data['date_end'];

                    if($date_full) {
                        $date_diff = DateTimeOperations::dateDiff($data['date_end'], date('Y-m-d H:i:s'));
                        if ($date_diff !== null && $date_diff === -1) {
                            $color = 'red';
                        }
                        if($data['date_end_ad']){
                            $date_full = Helper::formatDate($date_full);
                        } else {
                            $date_full = Helper::formatDateTimeShort($date_full);
                        }

                        $date_full = Yii::t('base', 'Date ending').' '.$date_full;

                    } else {
                        $date_full = '';
                    }

                ?>
                <h5><?php echo $data['module_title']; ?></h5>
                <p <?php echo $color ? "style='color : $color'" : ""; ?> >
                    <?php
                        echo $date_full;
                    ?>
                </p>
            </div>
        </div>
    </a>
</li>
