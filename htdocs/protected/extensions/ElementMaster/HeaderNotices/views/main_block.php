<li class="dropdown element" id="<?php echo $id; ?>" data-type="header_notice">
    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0)">
        <i class="fa <?php echo $ico; ?>"></i>
        <span class="badge bg-warning"><?php echo !empty($data['new']) ? $data['new'] : 0; ?></span>
    </a>
        <div class="dropdown-menu extended <?php echo $class; ?>">
            <span class="m-pointer"></span>
            <p>
                <?php echo $text; ?>
                <?php if($notice_set_read){ ?>
                    <a href="javascript:void(0)" class="element markIsRead pull-right" data-type="notice_set_read" <?php echo(empty($data['data']) ? 'style="display:none"' : ''); ?>><?php echo Yii::t('base', 'Mark as read'); ?></a>
                <?php } ?>
            </p>

            <ul class="scroll element" data-type="notice_block">
                <?php
                    if(!empty($data['data'])) {
                        $result = $this->buildInner()->getResultConcat();
                        echo $result['html'];
                    }
                ?>
            </ul>
        </div>


    <script type="text/javascript">
        $(document).ready(function() {
            if(HeaderNotice){
                HeaderNotice.init('<?php echo $id; ?>');
                HeaderNotice.setDateLast('<?php echo $id; ?>', '<?php echo date('Y-m-d H:i:s') ?>');
                HeaderNotice.addLinkActions(<?php echo (!empty($result['link_actions']) ? json_encode($result['link_actions']) : '""') ?>);
                instanceGlobal.contentReload.addContentVars(<?php echo (!empty($result['content_reload']) ? json_encode($result['content_reload']) : '""') ?>);
            }
        });
    </script>

</li>
