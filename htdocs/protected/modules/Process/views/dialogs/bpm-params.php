<div class="modal_restricter"></div>
<div class="modal-dialog" data-is-center style="width: 620px;">
    <section
        class="panel sm_extension checking_modal"
        data-process_id="<?php echo $bpm_params_model->_vars['process_id']; ?>"
    >
        <header class="panel-heading editable-block">
            <span><?php echo $bpm_params_model->getTitle() ?></span>
        </header>
        <div class="panel-body">
            <div class="panel-body">
                <ul class="inputs-block">
                    <?php
                        foreach($bpm_params_model->getObjectModels() as $object_model){
                            echo $object_model->getDialogHtml(true);
                        }
                    ?>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="submit" class="btn btn-primary bpm_params_save" ><?php echo \Yii::t('base', 'Save')?></button>
            </div>
        </div>
    </section>
</div>