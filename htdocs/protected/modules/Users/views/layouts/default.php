<?php
    $version = '?ver=' . $this->getPackage('version');
?>

<html lang="en" data-model-global>
<head>
    <meta charset="utf-8">
    <title><?php echo ParamsModel::model()->titleName('crm_name')->find()->getValue(); ?></title>

    <link rel="shortcut icon" href="/static/images/favicon.ico<?php echo $version;?>" type="image/x-icon">
    <!--Core CSS -->
    <link href="/static/bs3/css/bootstrap.min.css<?php echo $version;?>" rel="stylesheet">
    <link href="/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.css<?php echo $version;?>" rel="stylesheet">
    <link href="/static/css/bootstrap-reset.css<?php echo $version;?>" rel="stylesheet">
    <link href="/static/css/wizz.css<?php echo $version;?>" rel="stylesheet"> <!--стили только для этой строаницы-->

    <script src="/static/js/jquery-1.8.3.min.js<?php echo $version;?>"></script>
    <script src="/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.js<?php echo $version;?>" type="text/javascript"></script>
    <script src="/static/js/crm_params.js<?php echo $version;?>" type="text/javascript"></script>
    <script src="/static/js/jquery.nicescroll.js<?php echo $version;?>"></script>
    <script src="/static/js/bootstrap-select.js<?php echo $version;?>"></script>
    <script src="/static/js/magnific-popup/jquery.magnific-popup.js<?php echo $version;?>"></script>
    <script src="/static/js/local-storage.js<?php echo $version;?>"></script>
    <script src="/static/js/model-global.js<?php echo $version;?>"></script>
    <script src="/static/js/global.js<?php echo $version;?>"></script>
    <script src="/static/js/preloader.js<?php echo $version;?>"></script>

    <script type="text/javascript">
        ModelGlobal = ModelGlobal.createInstance(<?php echo json_encode(ParamsModel::loadJsParams()); ?>);
        ModelGlobal.setAuth(false);

        $.ajaxSetup({
            timeout : ModelGlobal.global.ajax.global_timeout
        });
    </script>
    
    </head>

    <body>
        <div class="reg_background" style="background-image: url(<?php echo $this->getRegBackgroundUrl() ?>)">
            <?php echo $content; ?>           
        </div>
        
        <?php echo $this->renderPartial('//blocks/global-params', true); ?>
        <div id="modal_dialog_container"></div>
        <?php $this->renderPartial(ViewList::getView('dialogs/uploadSelectFile')); ?>
    </body>

</html>
