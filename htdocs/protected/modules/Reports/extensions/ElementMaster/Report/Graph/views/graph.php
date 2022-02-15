<div class="graph-area element graph-set-preloader" id="<?php echo 'id_' . $element['unique_index'] ?>" unique_index="<?php echo $element['unique_index'] ?>" data-type="graph_element" data-graph_type="<?php echo $element['graph_type']; ?>">
    <div class="b-spinner"><div class="loader"></div></div>
</div>

<script type="text/javascript">
    if (!Global.getInstance()) {
        $('#container').addClass('hide-inner-preloaders');
    }

    Reports.graph_data.<?php echo 'id_' . $element['unique_index'] ?> = <?php echo json_encode($element['data']) ?>;
    $(document).ready(function(){
        var globalInstance = Global.getInstance();

        if (globalInstance.getPreloader()) {
            globalInstance.getPreloader().hide();
            globalInstance.setPreloader(null);
        }

        var timerId = setInterval(function(){
            clearInterval(timerId);

            var t1,
                element = '<?php echo 'id_' . $element['unique_index']; ?>',
                type = '<?php echo $element['graph_type']; ?>',
                key = Reports.graph_data.<?php echo 'id_' . $element['unique_index'] ?>;

            t1 = setTimeout(function () {
                clearTimeout(t1);
                Reports.buildGraph(element, type, key);
                var $container = $('#'+element);
                Reports.prepareHidePreloaderByGraph($container);
            }, 150);
        }, 500);
    });   
</script>
