(function ($) {
    "use strict";

    //функція сховання правого сайд бара
    function second_passed() {  
        $('#container').removeClass('nyw');
        $('.header').removeClass('nyw');
        $('.right-sidebar').removeClass('nyw');        
        $('#main-content').removeClass('nyw');
    }

    $(document).ready(function () {
        /*==Left Navigation Accordion ==*/
        if ($.fn.dcAccordion) {
            $('#nav-accordion').dcAccordion({
                eventType: 'click',
                autoClose: true,
                saveState: true,
                disableLink: true,
                speed: 'slow',
                showCount: false,
                autoExpand: true,
                classExpand: 'dcjq-current-parent'
            });
        }
        /*==Slim Scroll ==*/
        if ($.fn.slimScroll) {
            $('.event-list').slimscroll({
                height: '305px',
                wheelStep: 20
            });
            $('.conversation-list').slimscroll({
                height: '360px',
                wheelStep: 35
            });
            $('.to-do-list.scrollable').slimscroll({
                height: '300px',
                wheelStep: 35
            });
        }
        /*==Nice Scroll ==*/
        if ($.fn.niceScroll) {


            $(".leftside-navigation").niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px"
            });

            $(".leftside-navigation").getNiceScroll().resize();
            if ($('#sidebar').hasClass('hide-left-bar')) {
                $(".leftside-navigation").getNiceScroll().hide();
            }
            $(".leftside-navigation").getNiceScroll().show();
        }

        /*==Easy Pie chart ==*/
        if ($.fn.easyPieChart) {

            $('.notification-pie-chart').easyPieChart({
                onStep: function (from, to, percent) {
                    $(this.el).find('.percent').text(Math.round(percent));
                },
                barColor: "#39b6ac",
                lineWidth: 3,
                size: 50,
                trackColor: "#efefef",
                scaleColor: "#cccccc"

            });

            $('.pc-epie-chart').easyPieChart({
                onStep: function(from, to, percent) {
                    $(this.el).find('.percent').text(Math.round(percent));
                },
                barColor: "#5bc6f0",
                lineWidth: 3,
                size:50,
                trackColor: "#32323a",
                scaleColor:"#cccccc"

            });

        }

        /*== SPARKLINE==*/
        if ($.fn.sparkline) {

            $(".d-pending").sparkline([3, 1], {
                type: 'pie',
                width: '40',
                height: '40',
                sliceColors: ['#e1e1e1', '#8175c9']
            });



            var sparkLine = function () {
                $(".sparkline").each(function () {
                    var $data = $(this).data();
                    ($data.type == 'pie') && $data.sliceColors && ($data.sliceColors = eval($data.sliceColors));
                    ($data.type == 'bar') && $data.stackedBarColor && ($data.stackedBarColor = eval($data.stackedBarColor));

                    $data.valueSpots = {
                        '0:': $data.spotColor
                    };
                    $(this).sparkline($data.data || "html", $data);


                    if ($(this).data("compositeData")) {
                        $spdata.composite = true;
                        $spdata.minSpotColor = false;
                        $spdata.maxSpotColor = false;
                        $spdata.valueSpots = {
                            '0:': $spdata.spotColor
                        };
                        $(this).sparkline($(this).data("compositeData"), $spdata);
                    };
                });
            };

            var sparkResize;
            $(window).resize(function (e) {
                clearTimeout(sparkResize);
                sparkResize = setTimeout(function () {
                    sparkLine(true)
                }, 500);
            });
            sparkLine(false);



        }


        if ($(".target-sell").length) {
            if ($.fn.plot) {
                var datatPie = [30, 50];
                // DONUT
                $.plot($(".target-sell"), datatPie, {
                    series: {
                        pie: {
                            innerRadius: 0.6,
                            show: true,
                            label: {
                                show: false

                            },
                            stroke: {
                                width: .01,
                                color: '#fff'

                            }
                        }




                    },

                    legend: {
                        show: true
                    },
                    grid: {
                        hoverable: true,
                        clickable: true
                    },

                    colors: ["#ff6d60", "#cbcdd9"]
                });
            }
        }



        /*==Collapsible==*/
        //сворачивает/разворачивает список каналов
        /*$('.widget-head').click(function (e) {
            var widgetElem = $(this).children('.widget-collapse').children('i');

            $(this)
                .next('.widget-container')
                .slideToggle('slow');
            if ($(widgetElem).hasClass('ico-minus')) {
                $(widgetElem).removeClass('ico-minus');
                $(widgetElem).addClass('ico-plus');
            } else {
                $(widgetElem).removeClass('ico-plus');
                $(widgetElem).addClass('ico-minus');
            }
            e.preventDefault();
        });*/




        /*==Sidebar Toggle==*/

        $(".leftside-navigation .sub-menu > a").click(function () {
            var o = ($(this).offset());
            var diff = 80 - o.top;
            if (diff > 0)
                $(".leftside-navigation").scrollTo("-=" + Math.abs(diff), 500);
            else
                $(".leftside-navigation").scrollTo("+=" + Math.abs(diff), 500);
        });



        $('.sidebar-toggle-box .fa-bars').click(function (e) {

            $(".leftside-navigation").niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px"
            });

            $('#sidebar').toggleClass('hide-left-bar');
            if ($('#sidebar').hasClass('hide-left-bar')) {
                $(".leftside-navigation").getNiceScroll().hide();
            }
            $(".leftside-navigation").getNiceScroll().show();
            $('#main-content').toggleClass('merge-left');
            e.stopPropagation();

            // if (instanceAdditionalPanel && instanceAdditionalPanel.isOpen()) {
            //     instanceAdditionalPanel.close();
            // }

            if ($('.header').hasClass('merge-header')) {
                $('.header').removeClass('merge-header')
            }


        });

        // Toggle Rigth Menu
        /*$('.toggle-right-box .fa-bars').click(function (e) {
            $('#container').addClass('nyw');
            $('.header').addClass('nyw');
            $('.right-sidebar').addClass('nyw');
            $('#main-content').addClass('nyw');
            $('#container').toggleClass('open-right-panel');
            $('.right-sidebar').toggleClass('open-right-bar');
            $('.header').toggleClass('merge-header');

            setTimeout(second_passed, 300) 

            e.stopPropagation();
        });*/

        $('.header,#main-content,#sidebar').click(function () {
            // if ($('#container').hasClass('open-right-panel')) {
            //     $('#container').removeClass('open-right-panel')
            // }
            // if ($('.right-sidebar').hasClass('open-right-bar')) {
            //     $('.right-sidebar').removeClass('open-right-bar')
            // }
            // if ($('.header').hasClass('merge-header')) {
            //     $('.header').removeClass('merge-header')
            // }


        });


        // $('.panel .tools .fa').click(function () {
        //     var el = $(this).closest(".panel").children(".panel-body");
        //     if ($(this).hasClass("fa-chevron-down")) {
        //         $(this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
        //         el.slideUp(200);
        //     } else {
        //         $(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
        //         el.slideDown(200); }
        // });

        $(document).on('click', '.panel .tools .fa-cog', function () {
            $(this).removeClass('fa-chevron-down');
        });

        $(document).on('click', '.panel .tools .fa-times', function () {
            $(this).parents(".panel").parent().remove();
        });

        // tool tips

        $('.tooltips').tooltip();

        // popovers

        $('.popovers').popover();

    });


})(jQuery);