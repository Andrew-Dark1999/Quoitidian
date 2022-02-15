<div id="upload_template" class="hide">
    <section class="panel">
        <div id="drop_zone">
            <div class="images-block">
                <img src="/static/images/files/file_example1.png" alt="">
                <img src="/static/images/files/file_example2.png" alt="">
                <img src="/static/images/files/file_example3.png" alt="">
            </div>
            <div class="upload-section">
                <h3><?php echo Yii::t('base', 'Move file for upload'); ?></h3>
                <div class="file-block">
                    <button class="btn btn-lg btn-primary"><?php echo Yii::t('base', 'Upload from disk'); ?>
                    </button>
                        <input type="file" id="files" name="file" class="upload_file" />
                </div>
            </div>
        </div>
        <div class="upload-status">
            <p class="upload-filename"></p>
            <div class="progress progress-striped progress-xs">
                <div style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" role="progressbar" class="progress-bar progress-bar-info">
                    <span class="sr-only"><span class="sr-value">0</span>% Complete (success)</span>
                </div>
            </div>
        </div>
    </section>
</div>
