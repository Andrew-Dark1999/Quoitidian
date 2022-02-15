<div class="modal-dialog" style="width: 620px;" data-is-center>
    <section class="panel" >
    <div class="edit-view in sm_extension sm_extension_export no_middle">
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Export data') ?></span>
            </span>
        	<span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>
        <div class="panel-body">
            <div class="max-space">
                <div class="submodule-table">
                    <table class="table list-table">
                        <thead>
                        <tr>
                            <td><span class="name"><?php echo Yii::t('base', 'Choose all')?></span></td>
                            <td><input data-name="all-checked" type="checkbox" class="checkbox element"></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(!empty($fields))
                            foreach($fields as $field){
                                ?>
                                <tr>
                                    <td>
                                        <span class="name"><?php echo $field['title']; ?></span>
                                    </td>
                                    <td><input data-name="<?php echo $field['name']; ?>" <?php if($field['checked']) echo 'checked';?> type="checkbox" class="checkbox"></td>
                                </tr>
                                <?php
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        	<div class="buttons-section">
        		<button type="submit" class="btn btn-primary list_view_btn-export_to_<?php echo $type; ?>"><?php echo Yii::t('base', 'Export')?></button>
                <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        	</div>
        </div>
    </div>

</section>
</div>

<style>
    .modal-dialog .submodule-table{
        max-height: 220px;
        margin-bottom: 15px;
        overflow: hidden;
    }
</style>


<script>
    $(function () {
        setTimeout(function () {
            var $dialog = $('.modal-dialog');

            if (Global.isReport()) {
                $dialog.find('.sm_extension').addClass('is-page-report');
                $('.list_view_btn-print').addClass('is-page-report');
            }
            niceScrollCreate($dialog.find('.submodule-table'))
        },100);
    });
</script>
<!--<div class="modal-dialog" data-type="account-begin" data-is-center>-->
<!--    <section class="panel element">-->
<!--        <div class="edit-view in sm_extension sm_extension_export no_middle">-->
<!--            <header class="panel-heading editable-block hidden-edit">-->
<!--                <span class="tools options">-->
<!--                    <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>-->
<!--                </span>-->
<!--            </header>-->
<!--            <div class="content">-->
<!--                <div class="title-image"></div>-->
<!--                <span class="title">Ваш аккаунт готов к работе</span>-->
<!--                <p>Мы подготовили 3 коротких обучающих видео, чтобы вам, <br> было проще разобраться в системе</p>-->
<!--                <div class="panel-body">-->
<!--                    <div class="buttons-section">-->
<!--                        <a href="/faststart/" class="btn btn-default">Посмотреть видео</a>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--    </section>-->
<!--</div>-->
