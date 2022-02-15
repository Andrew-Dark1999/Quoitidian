<li class="prog-row total clearfix">
    <div>
        <p>
            <?php if($this->isCallsActive()){ ?>
            <a class="ajax_content_reload"
               data-action_key="<?php
                echo (new \ContentReloadModel(6, ['_use_auto_pci_pdi'=> false]))
                                    ->addVars(['module'=>['copy_id' => ExtensionCopyModel::MODULE_CALLS, 'params'=>['this_template'=>0]]])
                                    ->prepare()
                                    ->getKey(); ?>"
               href="javascript:void(0)"
            >
                <?php echo Yii::t('calls', 'All calls'); ?>
            </a>
            <?php } else { ?>
            <a class="ajax_content_reload"
               data-action_key="<?php echo (new \ContentReloadModel(5))->addVars(array('index' => 'plugins'))->prepare()->getKey(); ?>"
               href="javascript:void(0)"
            ><?php echo Yii::t('calls', 'Tune'); ?></a>
            <?php } ?>
        </p>
    </div>
</li>
