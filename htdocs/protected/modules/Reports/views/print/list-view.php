<div class="list-view-panel print">
        <section class="panel">
            <header class="panel-heading">
                <?php echo $report_model->module_title; ?>
            </header>
            <div class="panel-body sm_extension" data-copy_id="<?php echo $extension_copy->copy_id; ?>" >
                <div class="adv-table editable-table">
                    <?php
                    \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Report\Table\Table',
                        array(
                            'schema' => $schema,
                            'table_data' => $table_data,
                            'title_add_avatar' => $title_add_avatar,
                            'files_only_url' => $files_only_url,
                        ));
                    ?>
                </div>
            </div>
        </section>
</div><!-- /.list-view-panel -->

