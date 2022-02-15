<?php
    $count = count(unserialize($skipped));

    $vars = array(
        'selector_content_box' => '#list-table_wrapper_all',
        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
        'module' => array(
            'copy_id' => $copy_id,
        ),
    );
    $action_key = (new \ContentReloadModel(8))->addVars($vars)->prepare()->getKey();
?>

<div class="modal-dialog modalImport" data-is-center data-action_key="<?php echo $action_key; ?>">
    <section class="panel" >
    <div class="edit-view in sm_extension sm_extension_export no_middle">
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Import data') ?></span>
            </span>
        	<span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>
        <div class="panel-body">
            <div class="import-table context">
                <table class="table list-table">
                    <tbody>
                        <?php 
                            if(!empty($messages))
                                foreach($messages as $message){
                        ?>
                            <tr>
                                <span style="<?php if ($message['type']=='warning') echo 'font-weight:bold;'; ?>"><?php echo $message['message']; ?></span><br/>
                            </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        	<div class="buttons-section">
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo (count(unserialize($skipped))) ? Yii::t('base', 'Skip') : Yii::t('base', 'Close')?></button>
                <?php
                    if($count) {
                ?>
                        <input type="hidden" id="import_file" value="<?php echo $file; ?>">
                        <input type="hidden" id="import_skipped" value="<?php echo htmlspecialchars($skipped); ?>">
                        <button type="submit" class="btn btn-primary list_view_btn-import_and_replace"><?php echo Yii::t('base', 'Replace')?></button>
                        <button type="submit" class="btn btn-primary list_view_btn-import_and_combine"><?php echo Yii::t('base', 'Combine')?></button>
                <?php
                    }
                ?>
        	</div>
        </div>
    </div>

</section>
</div>

<script>
    $(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        if(content_vars){
            content_vars = JSON.parse(content_vars);
            instanceGlobal.contentReload.addContentVars(content_vars);
        }
    });


    $(function () {
        setTimeout(function () {
            niceScrollCreate($('.modal-dialog .import-table'))
        },100);
    });
</script>
