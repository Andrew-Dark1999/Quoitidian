/**
 * Created by andrew on 10/10/17.
 */

;(function (exports) {
    var _private, _public, _protected, Mouse,
        _self = {}; //link for instance

    _private = {

    };

    _public = {

    };

    Mouse = {
        koef1: 1.1,
        offSetFromBody: 175,
        step: 19,
        position: null,
        axis: {
            x: null,
            y: null
        },
        event: {
            mousemove: function( event ) {
                Mouse.axis = {
                    x: event.pageX,
                    y: event.pageY
                }
                Mouse.init();
            }
        },
        positionIsLeft: function () {
            return this.position == 'left' ? true : false;
        },
        positionIsRight: function () {
            return this.position == 'right' ? true : false;
        },
        speedShift: function () {
            var step = {};
            step.start = Mouse.positionIsRight() ? $('body').width() - Mouse.axis.x : Mouse.axis.x;
            step.delta = Math.abs(this.offSetFromBody - step.start);
            if (step.delta%this.step) {
                step.count = (step.delta - (step.delta%this.step))/this.step;
            } else step.count = step.delta%this.step;

            return !step.count ? 1 : step.count;
        },
        init: function(item){
            var count,
                label = false,
                element = item || $('ul.process_list li.ui-state-highlight');

            if (element.length) {
                if ($('.wrapper').width() - this.offSetFromBody <=Mouse.axis.x) {
                    this.position = 'right';
                    label = true;
                } else {
                    if (Mouse.axis.x < this.offSetFromBody
                        || (element.offset().left<0 && !ProcessWrapper.$.scrollLeft())) {
                        this.position = 'left';
                        label = true;
                    };
                }

                if (!label) {
                    this.position =  null;
                } else {
                    count = this.speedShift(); //count steps
                    if (count) {
                        UiSortable.timer.step = Math.floor(count*this.koef1);
                        ProcessList.stepOfMove = Math.floor(count*this.koef1);
                    }
                }
            }
        },
        wheelEnable : function disable() {
            $(window).off('wheel').on('wheel touchmove', function() {
                return true;
            });
        },
        wheelDisable : function disable() {
            $(window).off('wheel').on('wheel touchmove', function() {
                return false;
            });
        }
    }

    exports.Mouse = Mouse;
})(window);