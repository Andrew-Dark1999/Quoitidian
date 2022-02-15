<div class="modal-dialog" style="width: 620px;" data-is-center>
    <section class="panel element panel-lot-edit" data-type="message">
    <div class="edit-view in sm_extension sm_extension_export no_middle">
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Do you really want to change the data?') ?></span>
            </span>
        </header>
        <div class="panel-body">
        	<div class="buttons-section element" data-type="lot-edit">
                <?php
                    $vars = array(
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                    );
                ?>
                <button type="submit" class="btn btn-primary"><?php echo Yii::t('base', 'Change')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        	</div>
        </div>
    </div>

</section>
</div>
