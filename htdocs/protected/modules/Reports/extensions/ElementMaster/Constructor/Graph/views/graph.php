<div class="graph-area element graph-set-preloader set-preloader" id="<?php echo 'id_' . $element['unique_index'] ?>" unique_index="<?php echo $element['unique_index'] ?>" data-type="graph_element" data-graph_type="<?php echo $element['graph_type']; ?>">
    <div class="b-spinner"><div class="loader"></div></div>
</div>
<script type="text/javascript">
    Reports.graph_data.<?php echo 'id_' . $element['unique_index'] ?> = <?php echo json_encode($element['data']) ?>;
    $(document).ready(function(){
        var id = '<?php echo 'id_' . $element['unique_index']; ?>',
            $element = $('#'+id),
            $parent = $element.closest('[data-element_type="graph"]');

        $parent.addClass('report-parent-graph');
        $parent.find('.graph-area').addClass('set-preloader');

        var timerId = setInterval(function(){
            var instance = Reports.getInstance();

            clearInterval(timerId);
            Reports.buildGraph(id, '<?php echo $element['graph_type']; ?>', Reports.graph_data.<?php echo 'id_' . $element['unique_index'] ?>);

            if (Events.getCountLine(Events.TYPE_LOAD_GRAPH)) {
                Events.runHandler(Events.TYPE_LOAD_GRAPH);
            } else {
                $parent.find(Preloader.spinner.selector).remove();
                $parent.find('.graph-area').removeClass('graph-set-preloader');
                $parent.removeClass('report-parent-graph');
                $parent.find(Preloader.spinner.selector).remove();

                instance.setCountLoadedGraph({
                    element: $element
                })
            }
        }, 500);
    });   
</script>
