<?php
    if($this->only_body == false){

    $environments = $this->getPackage('environments');
    $min = \ParamsModel::getDefaultMin();

    $version = ($environments["mode"] == "dev") ? '' : '?ver=' . $this->getPackage('version');

    $styles = [
        '/static/fonts/opensans.css',
        '/static/css/codemirror.css',
        '/static/bs3/css/bootstrap.min.css',
        '/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.css',
        '/static/css/bootstrap-reset.css',
        '/static/fonts/lato.css',
        '/static/font-awesome/css/font-awesome.css',
        '/static/css/style.css',
        '/static/js/jvector-map/jquery-jvectormap-1.2.2.css',
        '/static/css/clndr.css',
        '/static/js/data-tables/DT_bootstrap.css',
        '/static/css/bucket-ico-fonts.css',
        '/static/fonts/myriadpro.css',
        '/static/css/bootstrap-select.css',
        '/static/js/bootstrap-datepicker/css/datepicker.css',
        '/static/js/bootstrap-timepicker/css/timepicker.css',
        '/static/js/magnific-popup/magnific-popup.css',
        '/static/js/bootstrap-daterangepicker/daterangepicker-bs3.css',
        '/static/js/jscrollpane/css/jquery.jscrollpane.css',
        '/static/css/crm.css',
        '/static/css/bootstrap-fullcalendar.css',
        '/static/css/crm-additional.css',
        '/static/css/reports.crm.css',
        '/static/css/process.crm.css'
    ];
    $scripts = [
        '/static/js/environments.js',
        '/static/js/jquery-1.8.3.min.js',
        //'/static/js/jssip/jssip-3.2.4.js',
        '/static/js/sip-phone.js',
        '/static/js/date-time.js',
        '/static/js/select.js',
        '/static/js/moment-2.18.1.js',
        '/static/js/crm_params.js',
        '/static/js/code-mirror/codemirror.js',
        '/static/js/code-mirror/xml.js',
        '/static/js/magnific-popup/jquery.magnific-popup.js',
        '/static/js/jquery-ui/jquery-ui-1.10.1.custom.min.js',
        '/static/bs3/js/bootstrap.min.js',
        '/static/js/jquery.dcjqaccordion.2.7.js',
        '/static/js/jquery.scrollTo.min.js',
        '/static/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js',
        '/static/js/jquery.nicescroll.js',
        '/static/js/bootstrap-select.js',
        '/static/js/data-tables/jquery.dataTables.js',
        '/static/js/data-tables/DT_bootstrap.js',
        '/static/js/jquery.maskedinput.js',
        '/static/js/drop-down-list.js',
        '/static/js/history.js',
        '/static/js/notifications.js',
        '/static/js/tools.js',
    ];
    $scripts1 = [
        '/static/js/model-global.js',
        '/static/js/jquery.caret.js',
        '/static/js/date-time-block.js',
        '/static/js/easypiechart/jquery.easypiechart.js',
        '/static/js/sparkline/jquery.sparkline.js',
        '/static/js/morris-chart/morris.js',
        '/static/js/morris-chart/raphael-min.js',
        '/static/js/flot-chart/jquery.flot.js',
        '/static/js/flot-chart/jquery.flot.tooltip.min.js',
        '/static/js/flot-chart/jquery.flot.resize.js',
        '/static/js/flot-chart/jquery.flot.pie.resize.js',
        '/static/js/flot-chart/jquery.flot.animator.min.js',
        '/static/js/flot-chart/jquery.flot.growraf.js',
        '/static/js/bootstrap-datepicker/js/bootstrap-datepicker.js',
        '/static/js/bootstrap-datepicker/js/datepicker-locales.js',
        '/static/js/bootstrap-timepicker/js/bootstrap-timepicker.js',
        '/static/js/jscrollpane/js/jquery.jscrollpane.min.js',
        '/static/js/jscrollpane/js/jquery.mousewheel.js',
        '/static/js/jscrollpane/js/mwheelIntent.js',
        '/static/js/bootstrap-daterangepicker/moment.js',
        '/static/js/bootstrap-daterangepicker/daterangepicker.js',
        '/static/js/jquery.dragtable/jquery.dragtable.js',
        '/static/js/scripts.js',
        '/static/js/local-storage.js',
        '/static/js/list-view-display.js',
        '/static/js/filter.js',
        '/static/js/global.js',
        '/static/js/modal-dialog.js',
        '/static/js/profile.js',
        '/static/js/quick-view-panel.js',
        '/static/js/communications.js',
        '/static/js/calls.js',
        '/static/js/events.js',
        '/static/js/calendar-view.js',
        '/static/js/edit-view.js',
        '/static/js/table-column-resize.js',
        '/static/js/list-view.js',
        '/static/js/process-view.js',
        '/static/js/process-view-base.js',
        '/static/js/jquery.emojiarea.js',
        '/static/js/pagination.js',
        '/static/js/sorting.js',
        '/static/js/search.js',
        '/static/js/participant.js',
        '/static/js/constructor.js',
        '/static/js/inline-edit.js',
        '/static/js/preloader.js',
        '/static/js/process/process.general_v0.'.$environments["bpm-version"].'.js',
        '/static/js/morris-chart/morris.js',
        '/static/js/morris-chart/raphael-min.js',
        '/static/js/reports.general.js',
        '/static/js/process/process.events.js',
        '/static/js/url.js',
        '/static/js/mouse.js',
        '/static/js/guide.js',
        '/static/js/draft.js',
        '/static/js/nice-scroll.js',
        '/static/js/colResizable-1.6.min.js'
    ];

    function getScript($scripts, $version) {
        foreach ($scripts as $script) {
            echo '<script src="'.$script.$version.'"></script>';
        };
    }

    $minStyles = [
        '/static/fonts/opensans.css',
        '/static/css/codemirror.css',
        '/static/bs3/css/bootstrap.min.css',
        '/static/min/crm.min.css',
        '/static/css/reports.crm.css',
        '/static/css/process.crm.css'
    ];
    $minScripts = [
        '/static/js/jquery-1.8.3.min.js',
        '/static/js/environments.js',
        '/static/js/crm_params.js',
        '/static/js/select.js', 
        '/static/js/date-time.js',
        '/static/js/code-mirror/codemirror.js',
        '/static/js/code-mirror/xml.js',
        '/static/js/magnific-popup/jquery.magnific-popup.js',
        //'/static/js/jssip/jssip-3.2.4.min.js',
        '/static/min/scripts.min.js'
    ];
    $minScripts1 = [
        '/static/min/scripts2.min.js',
        '/static/min/scripts3.min.js',
        '/static/js/mouse.js',
        '/static/js/quick-view-panel.js',
        '/static/js/constructor.js',
        '/static/js/inline-edit.js',
        '/static/js/morris-chart/morris.js',
        '/static/js/morris-chart/raphael-min.js',
        '/static/js/reports.general.js',
        '/static/js/calendar-view.js',
//        '/static/js/process/process.events.js',
        '/static/js/guide.js',
        '/static/js/draft.js',
        '/static/js/colResizable-1.6.min.js'
    ];

?>
<!DOCTYPE html>
<html lang="en" data-model-global>
<head>
    <meta charset="utf-8">
    <title><?php echo $this->getApplicationTitle(); ?></title>
    <link rel="shortcut icon" href="/static/images/favicon.ico" type="image/x-icon">

    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js"></script>
    <script type="text/javascript">
        WebFontConfig = {
            custom: {families: ['CustomOpenSans']}
        };
        startResizeColumn = undefined;
    </script>

    <script src="https://cdn.tiny.cloud/1/0574bpp6huf3v12fdyxx3ewfv92xjg0f5ii8uakyukx71m0y/tinymce/4/tinymce.min.js"></script>

    <?php
    $styles = ($min) ? $minStyles : $styles;
    $scripts = ($min) ? $minScripts : $scripts;

    foreach ($styles as $style) {
        echo '<link href="' . $style . $version . '" rel="stylesheet">';
    };
    getScript($scripts, $version);
    ?>
    <script type="text/javascript">
        Environments.set(<?php echo json_encode($environments); ?>);
        crmParams
            .setParams(<?php echo json_encode(ParamsModel::loadJsParams()); ?>)
            .setParamsPage('<?php echo $this->getPageName(); ?>', '<?php echo $this->getPageInterfaceType(); ?>');

        $.ajaxSetup({
            timeout: crmParams.global.ajax.global_timeout,
        });
    </script>

    <?php
    $scripts = ($min) ? $minScripts1 : $scripts1;
    getScript($scripts, $version);
    ?>
    <script>
        ModelGlobal = ModelGlobal.createInstance(<?php echo json_encode(ParamsModel::loadJsParams()); ?>);
    </script>

</head>
<?php } ?>
<body class="full-width with-bg delay-pre">
    <section id="container" class="set-preloader show-fix reload-page">
        <?php $this->renderPartial('//blocks/preloader'); ?>

        <?php echo $content; ?>
    </section>
    <?php echo $this->renderBlock('global-params'); ?>
    <div id="modal_dialog_container"></div>
    <?php $this->renderPartial(ViewList::getView('dialogs/uploadSelectFile')); ?>

</body>
<?php if($this->only_body == false){ ?>
</html>
<?php } ?>
