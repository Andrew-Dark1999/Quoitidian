;(function (exports) {

    var _self = {},
        _private, _public, ProcessObj;

    var Process = function(){
        for(var key in ProcessObj) {
            this[key] = ProcessObj[key];
        }
    }

    var BpmModel = {
        addNewBranch : null,
        removedOfOperator : null
    }

    var CircleController = {
        get : function () {
            return $('svg.arrows circle');
        },
        set: function (cx, cy) {
            return this.get().attr({
                cx: cx,
                cy: cy
            });
        },
        hide: function () {
            return this.get().attr({
                cx: -10,
                cy: -10
            })
        }
    }

    var ModelArrows = {
        end: null,
        begin: null,
        $: null,
        d : {
            x1: null,
            y1: null,
            x2: null,
            y2: null,
            x3: null,
            y3: null,
            type: null
        },
        update: function () {
            return Arrows.parse(this.$);
        }
    }
    var Arrows = {
        name_model_object: 'modelArrow',

        TYPE_BOTTOM_STRAIGHT: 1,
        TYPE_EMPTY: 2,
        TYPE_STRAIGHT: 3,
        TYPE_TOP_STRAIGHT: 4,
        TYPE_ERROR: 5,

        MIN_WIDTH: 144,

        SUB_TYPE_RT: 6,
        SUB_TYPE_TR: 7,
        SUB_TYPE_BR: 8,

        get: function () {
            return $('svg.arrows path.arrow');
        },
        getByBegin : function (key) {
            return  $('svg path[arr-begin="'+key+'"]');
        },
        getByEnd : function (key) {
            return  $('svg path[arr-end="'+key+'"]');
        },
        createModel : function (path) {
            var model = Object.assign({}, ModelArrows);

            this.calculate(model, $(path));

            $(path).data(this.name_model_object, model);

            return model;
        },
        getModel : function (path) {
            return $(path).data()[this.name_model_object];
        },
        calculate: function (o, $element) {
            o.begin = $element.attr('arr-begin')
            o.end = $element.attr('arr-end');
            o.$ = $element;

            var coordD = $($element).attr('d').split(' ');

            o.d = {
                type : null,
                sub_type : null,
                width : null,
                length : coordD.length,
                x1 : parseInt(coordD[1]),
                y1 : parseInt(coordD[2]),

                x2 : parseInt(coordD[4]),
                y2 : parseInt(coordD[5]),

                x3 : parseInt(coordD[7]) || null,
                y3 : parseInt(coordD[8]) || null
            }

            switch (o.d.length) {
                case 21: {
                    o.d.width = o.d.x3 -o.d.x1;
                    o.d.type = this.TYPE_EMPTY;
                    break;
                }
                case 18: {
                    o.d.width = o.d.x3 -o.d.x1;

                    if (o.d.y3 < o.d.y2 || (o.d.y1 > o.d.y2 && o.d.y2 == o.d.y3)) {
                        o.d.type = this.TYPE_TOP_STRAIGHT;

                        if (o.d.y1 == o.d.y2) {
                            o.d.sub_type = this.SUB_TYPE_RT;
                        }
                    }
                    if (o.d.y1 < o.d.y2) {
                        o.d.type = this.TYPE_BOTTOM_STRAIGHT;
                    }

                    break;
                }
                case 15: {
                    o.d.width = o.d.x2 -o.d.x1;
                    o.d.type = this.TYPE_EMPTY;
                    break;
                }
                default: break;
            }

            if (o.d.width != null && o.d.width == 0) {
                o.d.type = this.TYPE_ERROR;
            }

            return o;
        },
        parse: function (element) {
            var model;

            if (!element) {
                return null;
            }

            model = this.getModel($(element));
            return this.calculate(model, model.$);
        },
        getCrossingByList : function (baseD, $list) {
            var object = null;

            if (!$list) return object;

            object = $list.filter(function () {
                var bool = true,
                    $this = $(this),
                    d = Arrows.parse($this);

                if (baseD.type == Arrows.TYPE_BOTTOM_STRAIGHT || baseD.type == Arrows.TYPE_TOP_STRAIGHT) {
                    if (
                        (d.type == Arrows.TYPE_BOTTOM_STRAIGHT && (d.y1 < baseD.y3 && baseD.y3 < d.y2 ))
                        || (d.type == Arrows.TYPE_TOP_STRAIGHT && (d.y1 > baseD.y3 && baseD.y3 > d.y2))){
                        bool = false;
                    }

                    if (d.type == Arrows.TYPE_BOTTOM_STRAIGHT && ((baseD.y2 > d.y2 && d.y2 > d.y1) || (baseD.y2 < d.y2 && d.y2 < d.y1))) {
                        bool = false;
                    }
                }

                return !bool ? this : null; //crossing direction
            })

            return object;
        },
        getCrossing : function (baseD) {
            //визначаємо перехрестя ліній з базовою
            var crossing;

            $.each(Arrows.get(), function (key, data) {
                var label,
                    $this = $(this),
                    d = Arrows.parse($this);

                if (baseD.type == Arrows.TYPE_BOTTOM_STRAIGHT || baseD.type == Arrows.TYPE_TOP_STRAIGHT) {
                    if ((d.type == Arrows.TYPE_TOP_STRAIGHT || d.type == Arrows.TYPE_BOTTOM_STRAIGH) && (d.Y1 > baseD.Y2 || baseD.Y2 < d.Y2 )) {
                        crossing = {
                            operatorBegin:  BpmOperator.getBeginOperator($this),
                            operatorEnd:  BpmOperator.getEndOperator($this)
                        }
                    }
                }
            });

            return crossing;
        },
        recountAll : function() { // recounting arrows an helpers(fake_operators)
            var operator = $('.fake_operator').remove(),
                outerArr = [],
                _this =  this;

            $('path[outer]').each(function(){
                if ($(this).is('[stroke-dasharray]')) {
                    outerArr.push($(this).attr('arr-begin'));
                }
                $(this).remove();
            });
            ProcessObj.BPM.setBranchEnds();

            $.each(this.get(), function(i){
                var $this = $(this);
                if (!$this.is('[branch]')) {
                    _this.recount($this);
                }
                if (i == $('svg.arrows path.arrow').length-1) {
                    for (var b=1; b<11; b++) {
                        $('path[branch="'+b+'"]').each(function(){
                            _this.recount($(this));
                        });
                    }
                }
            }).promise().done(function(){
                if (operator.length) {
                    operator.each(function(){
                        if (parseInt($(this).attr('gridrow'))>parseInt($(this).closest('.bpm_unit').attr('rows'))) {
                            $(this).closest('.bpm_unit').attr('rows',$(this).attr('gridrow')+'');
                        }
                    });
                }
            });
            return this;
        },
        recount : function($this){ //перерасчет стрелок при любом движении или добавлении елементов
            var bpmOperator = $('div.bpm_operator'),
                arrows = $('svg.arrows'),
                elemBegin = {
                    $ : bpmOperator.filter('[data-unique_index="'+$this.attr('arr-begin')+'"]')
                },
                elemEnd = {
                    $ : bpmOperator.filter('[data-unique_index="'+$this.attr('arr-end')+'"]')
                };

            elemBegin.$body = elemBegin.$.find('.bpm_body');
            elemBegin.$tree = elemBegin.$.closest('.bpm_tree');
            elemBegin.row = parseInt(elemBegin.$.attr('gridrow'));
            elemBegin.col = parseInt(elemBegin.$.attr('gridcol'));

            elemEnd.$body = elemEnd.$.find('.bpm_body');
            elemEnd.$tree = elemEnd.$.closest('.bpm_tree');
            elemEnd.row = parseInt(elemEnd.$.attr('gridrow'));
            elemEnd.col = parseInt(elemEnd.$.attr('gridcol'));

            if (!elemBegin.$body.length && !elemEnd.$body.length) {
                return;
            }
            var widthB = elemBegin.$body.width(),
                heightB = elemBegin.$body.height(),
                widthE = elemEnd.$body.width(),
                heightE = elemEnd.$body.height();

            var topDifB = elemBegin.$body.offset().top - arrows.offset().top,
                leftDifB = elemBegin.$body.offset().left - arrows.offset().left,
                topDifE = elemEnd.$body.offset().top - arrows.offset().top,
                leftDifE = elemEnd.$body.offset().left - arrows.offset().left;

            //p-point T-top L-left R-right B-bottom B-begin E-end x,y-coordinates ex:pBBy-(pont bottom begin y)
            //      pT
            //   pL( )pR
            //      pB
            var pTBx = leftDifB + widthB / 2,
                pTBy = topDifB,
                pRBx = leftDifB + widthB,
                pRBy = topDifB + heightB / 2,
                pBBx = leftDifB + widthB / 2,
                pBBy = topDifB + heightB + 36,
                pLBx = leftDifB,
                pLBy = topDifB + heightB / 2;

            var pTEx = leftDifE + widthE / 2,
                pTEy = topDifE,
                pREx = leftDifE + widthE,
                pREy = topDifE + heightE / 2,
                pBEx = leftDifE + widthE / 2,
                pBEy = topDifE + heightE + 36,
                pLEx = leftDifE,
                pLEy = topDifE + heightE / 2;

            if (leftDifB == leftDifE) { // to client
                var stingX1 = pBBx-2.5,
                    stingY1 = pLEy+15,
                    stingX2 = pBBx+2.5,
                    stingY2 = pLEy+15;

                pLEy -= 2;

                $this.attr('d', 'M '+pBBx+' '+pLBy+' L '+pBBx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pBBx+' '+pREy);
            } else
            if (leftDifB+20 < leftDifE) {
                if (topDifB+20 < topDifE) {
                    if($this.is('[branch-end]') && $this.is('[branch]') && $this.attr('branch-end')!=='main'){
                        var corner1x = pREy+($this.attr('branch')-1)*100;
                        if ($this.attr('branch') && $this.attr('modifier')) {
                            corner1y = pREy+($this.attr('modifier')-200);
                        } else if ($this.attr('branch')=='1') {
                            corner1y = pREy+100;
                        }
                        var stingX1 = corner1x-2.5,
                            stingY1 = pBEy+15,
                            stingX2 = corner1x+2.5,
                            stingY2 = pBEy+15;
                        $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pBBx+' '+corner1y+' L '+corner1x+' '+corner1y+' L '+corner1x+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+corner1x+' '+pBEy+''); /// ATTENTION!!  replace pLEy => pBEy

                        elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemEnd.row+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                        elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+(elemEnd.row+($this.attr('branch')-1))+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                    } else if ($this.attr('branch-end') && $this.attr('branch-end')!=='main') {
                        var corner1x = pBEx,
                            corner1y = pREy;
                        stingX1 = corner1x+2.5,
                            stingY1 = pTEy-15,
                            stingX2 = corner1x-2.5,
                            stingY2 = pTEy-15;
                        $this.attr('d', 'M '+pRBx+' '+pRBy+' L '+corner1x+' '+pRBy+' L '+corner1x+' '+pTEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+corner1x+' '+pTEy+'');
                        elemBegin.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemBegin.row+'" gridcol="'+elemEnd.col+'"></div>');
                    } else {
                        var stingX1 = pLEx-15,
                            stingY1 = pLEy-2.5,
                            stingX2 = pLEx-15,
                            stingY2 = pLEy+2.5;
                        $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pBBx+' '+pLEy+' L '+pLEx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pLEx+' '+pLEy+'');
                    }
                } else if (topDifB-20 > topDifE) {
                    if($this.is('[branch-end]') && $this.attr('branch-end')!=='main' && $this.attr('branch')!=1){
                        var corner1y = pRBy+100;

                        if ($this.is('[branch]') && $this.is('[modifier]')) {
                            corner1y = pRBy+($this.attr('modifier')-200);
                        } else if ($this.attr('branch')) {
                            corner1y = pRBy+($this.attr('branch')-2)*100;
                        }

                        var corner1x = pBEx,
                            stingX1 = corner1x-2.5,
                            stingY1 = pBEy+15,
                            stingX2 = corner1x+2.5,
                            stingY2 = pBEy+15;

                        if ($this.attr('branch')>2) {
                            $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pBBx+' '+corner1y+' L '+corner1x+' '+corner1y+' L '+corner1x+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+corner1x+' '+pBEy+'');
                        } else {
                            $this.attr('d', 'M '+pRBx+' '+pRBy+' L '+corner1x+' '+pRBy+' L '+corner1x+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+corner1x+' '+pBEy+'');
                        }
                        if ($this.attr('branch-end') && $this.attr('branch')==2) {
                            elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemEnd.row+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                        } else if ($this.attr('branch-end') && $this.attr('branch')>2) {
                            elemBegin.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemBegin.row+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                        } else {
                            elemBegin.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemBegin.row+'" gridcol="'+(elemEnd.col)+'"></div>');
                        }
                    } else {
                        var stingX1 = pLEx-15,
                            stingY1 = pLEy-2.5,
                            stingX2 = pLEx-15,
                            stingY2 = pLEy+2.5;

                        $this.attr('d', 'M '+pTBx+' '+pTBy+' L '+pTBx+' '+pREy+' L '+pLEx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pLEx+' '+pLEy+'');
                    }
                } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
                    if($this.is('[branch-end]') && $this.attr('branch-end')!=='main'){
                        var corner1y,
                            corner1x = pRBy+100;

                        if ($this.is('[branch]') && $this.is('[modifier]')) {
                            corner1y = pREy+parseInt($this.attr('modifier'));
                        } else if ($this.is('[branch]')) {
                            corner1y = pRBy+($this.attr('branch')-1)*100;
                        } else corner1y = pREy;

                        var corner2x = pBEx,
                            stingX1 = corner2x-2.5,
                            stingY1 = pBEy+15,
                            stingX2 = corner2x+2.5,
                            stingY2 = pBEy+15;

                        $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pBBx+' '+corner1y+' L '+corner2x+' '+corner1y+' L '+corner2x+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+corner2x+' '+pBEy+'');
                        if ($this.is('[branch-end]') && corner2x-pBBx<200) {
                            elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+(elemEnd.row+($this.attr('branch')-1))+'" gridcol="'+elemEnd.col+'"></div>');
                        } else {
                            elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+(elemEnd.row-1)+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                        }
                    } else {
                        var stingX1 = pLEx-15,
                            stingY1 = pLEy-2.5,
                            stingX2 = pLEx-15,
                            stingY2 = pLEy+2.5;

                        $this.attr('d', 'M '+pRBx+' '+pRBy+' L '+pLEx+' '+pLEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pLEx+' '+pLEy+'');
                        if ($this.is('[branch-end]')) {
                            elemEnd.$tree.append('<div class="fake_operator bpm_operator" gridrow="'+elemEnd.row+'" gridcol="'+(elemEnd.col-1)+'"></div>');
                        }
                    }
                }
            } else if (leftDifB-20 > leftDifE) {
                if (topDifB+20 < topDifE) {
                    var stingX1 = pTEx-2.5,
                        stingY1 = pTEy-15,
                        stingX2 = pTEx+2.5,
                        stingY2 = pTEy-15;

                    $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pBEx+' '+pLBy+' L '+pTEx+' '+pTEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pTEx+' '+pTEy+'');
                } else if (topDifB-20 > topDifE) {
                    var stingX1 = pREx+15,
                        stingY1 = pREy-2.5,
                        stingX2 = pREx+15,
                        stingY2 = pREy+2.5;

                    $this.attr('d', 'M '+pTBx+' '+pTBy+' L '+pTBx+' '+pREy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
                } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
                    var stingX1 = pREx+15,
                        stingY1 = pREy-2.5,
                        stingX2 = pREx+15,
                        stingY2 = pREy+2.5;

                    $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
                }
            } else {
                if (topDifB+20 < topDifE) {
                    var stingX1 = pTEx-2.5,
                        stingY1 = pTEy-15,
                        stingX2 = pTEx+2.5,
                        stingY2 = pTEy-15;

                    $this.attr('d', 'M '+pBBx+' '+pBBy+' L '+pTEx+' '+pTEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pTEx+' '+pTEy+'');
                } else if (topDifB-20 > topDifE) {
                    var stingX1 = pBEx-2.5,
                        stingY1 = pBEy+15,
                        stingX2 = pBEx+2.5,
                        stingY2 = pBEy+15;

                    $this.attr('d', 'M '+pTBx+' '+pTBy+' L '+pBEx+' '+pBEy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pBEx+' '+pBEy+'');
                } else if (topDifB-20 <= topDifE && topDifB+20 >= topDifE) {
                    var stingX1 = pREx+15,
                        stingY1 = pREy-2.5,
                        stingX2 = pREx+15,
                        stingY2 = pREy+2.5;
                    $this.attr('d', 'M '+pLBx+' '+pLBy+' L '+pREx+' '+pREy+' L '+stingX1+' '+stingY1+' L '+stingX2+' '+stingY2+' L '+pREx+' '+pREy+'');
                }
            }
            arrows.find('circle').appendTo('svg.arrows'); // TODO: This need optimization
        },
        recounts : function (array) {
            var _this = this;

            ProcessObj.BPM.setBranchEnds();
            $.each(array, function () {
                var $this = $(this);

                $this.removeAttr('branch-end');
                _this.recount($this);
            });
        },
        //подписать стрелки
        sign : function(){
            var arrows = $('svg.arrows'),
                $list = Arrows.get().filter('[title]');

            arrows.find('text').remove();

            $.each($list, function(){
                var coordx, coordy, width,
                    $this = $(this),
                    element = $('svg.hidden text.b_title').clone(true),
                    offset = 5;
                var titleBranch = $this.attr('title'),
                    coordArr = $this.attr('d').split(' ');

                if (coordArr.length<16) {
                    coordx = parseInt(coordArr[1])+offset;
                    coordy = parseInt(coordArr[2])-offset;
                    width = coordArr[4] - coordArr[1];
                } else {
                    coordx = parseInt(coordArr[4])+offset;
                    coordy = parseInt(coordArr[5])-offset;
                    width = coordArr[7] - coordArr[1];

                    if ($this.is('[branch-end="true"]')) coordx = parseInt(coordArr[1])+offset;
                }
                // text description in center
                if ($(".bpm_operator[data-unique_index='"+$this.attr('arr-begin')+"'][data-name=condition]") )
                {
                    coordx += Math.floor(width / 2) - offset;
                }
                element.attr('data-id', $this.attr('arr-end')).text(titleBranch).attr({
                    x: coordx,
                    y: coordy
                });
                arrows.append(element);

                var svgText = arrows.find('text[data-id="'+$this.attr('arr-end')+'"]'),
                    span = $('.b_bpm_top'),
                    $textDynamic = $('.text-dynamic');

                if (!$textDynamic.length) {
                    span.append("<div class='text-dynamic' style='float:left'></div>"); // for firefox
                }

                span = $($textDynamic.selector).text(svgText.text());

                if (span.width() > (width - 5)) {
                    span.html(svgText.text()+'...');
                    var str,
                        i = 100;
                    while (span.width() > (width - 40) || i <= 0) {
                        str = span.text();
                        str = str.substring(0, str.length-4);
                        span.text(str+'...');
                        i--;
                    }
                }
                svgText.html(span.text());
                $($textDynamic.selector).remove();
                arrows.find('[data-id]').removeAttr('data-id');
            });
        },
    };

    var ModelBpmOperator = {
        TYPE_NAME_CONDITION: 'condition',
        TYPE_NAME_AND: 'and',
        TYPE_NAME_NOTIFICATION: 'notification',
        TYPE_NAME_TASK: 'task',
        TYPE_NAME_AGREETMENT: 'agreetment',
        TYPE_NAME_END: 'end',
        TYPE_NAME_BEGIN: 'begin',

        $: null,
        unique_index : null,
        name : null,
        type : null,
        ug_id : null,
        ug_type : null,
        flag: null,
        responsible: null, // обект ответственного
        'end-branches': null,
        'responsible_index': null,
        'responsible_order': null,
        status: null,

        getResponsible : function () {
            return Scheme.getResponsibleByIndex(this['responsible_index']);
        },
        next: function () {
            return $([]);
        },
        prev: function () {
            return $([]);
        },
        isHelper: function () {
          return this.$.is('.and_helper') ? true : false;
        },
        isEnd: function () {
            return this.name == this.TYPE_NAME_END ? true : false;
        },
        isHelperHasEmptyBranch: function () {
            var r = null;

            if (this.isHelper()) {
                var $prev = this.prev();

                r = $prev.length == 1 && $prev.is('[end-branches]') ? true : false;
            }

            return r;
        },
        // записати параметр
        set: function (json) {
            var index;

            for (var key in json) {
                if (key.indexOf('data') == 0) {
                    index = key.substring(5);
                    this[index] = json[key];
                } else {
                    if (key.indexOf('grid')) {
                        this[key] = parseInt(json[key]);
                    } else this[key] = json[key];
                }
            }

            return this;
        },
        attr: function (json) {
            this.$.attr(json);

            return this;
        },
        convertParam: function(json) {
            var index,
                o = {};

            for(var key in json) {
                if (key.indexOf('data') == 0) {
                    index = key.substring(5);
                } else index = key;

                o[index] = json[key];
            }

            return o;
        },
        setResponsible: function (key) {
            if (key) {
                var responsible,
                    operator = Scheme.getOperatorByIndex(key);

                if (operator) {
                    this.responsible_index = operator.responsible.unique_index;
                    responsible = operator.responsible;
                } else {
                    responsible = Scheme.get()[0];
                    this.responsible_index = responsible.unique_index;
                }

                var data = {
                    'data-ug_id': responsible.ug_id,
                    'data-ug_type': responsible.ug_type,
                    'data-responsible_index': responsible.unique_index,
                    'data-flag': (responsible.flag) ? responsible.flag+'' : ''
                }

                //renameKey
                var json = _self.renameKey('data-unique_index','data-responsible_index', data);
                this.responsible = this.convertParam(json);
                this.responsible['title'] = responsible.title;

                if (this.isResponsible()) {
                    this.attr(data);

                }
            }
            return this;
        },
        setNewElement: function(bool) {
            if (bool) {
                this.$.attr('data-new', true);
            } else {
                this.$.removeData('data-new');
            }
            return this;
        },
        isResponsible: function () {
          return (this.name == this.TYPE_NAME_TASK || this.name == this.TYPE_NAME_AGREETMENT);
        },
        showTitle: function (title) {
            var sign, responsible,
                $titleOperator = this.$.find('.bpm_title');

            sign = title || this.title;

            if (this.isResponsible()) {
                responsible = this.getResponsible();
                this.$.find('.bpm-responsible').html(responsible.title);
            }

            $titleOperator.html(sign);
        },
        update : function (array) {
            var item, index;

            for(var i = 0; i < array.length; i++) {
                item = array[i];

                for(var key in item) {
                    if (key.indexOf('data')==0) {
                        index = key.substring(5);
                        this[index] = item[key];
                    } else if (key.indexOf('grid')) {
                        this[key] = parseInt(item[key]);
                    } else this[key] = item[key];
                }
            }
            return this;
        },
    };

    var param = {},
        _selfProcess = _self;

    ;(function (exports) {
        var _self = {},
            BpmOperator, _private;

        _private = {
            create : function (element) {
                var data,
                    unique_key = element.data('unique_index'),
                    model = Scheme.getOperatorByIndex(unique_key);

                data = (model) ? Object.create(model) : _selfProcess.createParent(BpmOperator.type, {});
                for(var key in ModelBpmOperator) {
                    data[key] = ModelBpmOperator[key];
                }

                return data;
            }
        };
        BpmOperator = {
            type: 'BpmOperator',
            _name_model_object: 'modelOperator', // name for save object in data object DOM
            _instance: null, // if exist instance/ Can only 1 instance;

            createModel: function ($element) {
                var model = _private.create($element);

                $.map($element[0].attributes, function (item) {
                    var key =  item.name == 'class' ? null : item.name;

                    if (key) {
                        if (key.indexOf('data') == 0) {
                            key = key.substring(5);
                        }
                        if (key.indexOf('grid')==0) {
                            item.value = parseInt(item.value);
                        }
                        model[key] = item.value;
                    }
                });

                model.$ = $element;
                model.setResponsible(model.unique_index);

                $element.data(this._name_model_object, model);
                return model;
            },
            showTitle: function ($element, title) {
                var model = $element.data()[BpmOperator._name_model_object];

                model.showTitle(title);
            },
            getByAttr : function(dataAttr) {
                return $('.bpm_operator'+dataAttr);
            },
            titleOperatorRename : function(unique_index, title){
                var $operator = BpmOperator.getByKey(unique_index),
                    titleOperator = $operator.find('.bpm_title');

                this.showTitle($operator, title);

                if (titleOperator.height()>30) {
                    titleOperator.html(titleOperator.html()+'...');
                    // while (titleOperator.height()>30) {
                    //     str = titleOperator.html();
                    //     str = str.substring(0, str.length-4);
                    //     titleOperator.html(str+'...');
                    // }
                }
            },
            getModel : function ($element) {
                return $element && $element.length ? $element.data()[this._name_model_object] : null;
            },
            getMaxAttrList: function ($list, gridParam) {
                return Math.max.apply(null, $list.map(function () { return parseInt($(this).attr(gridParam)); }).get());
            },
            getBeginOperator(path) {
                return $('.bpm_operator[data-unique_index="'+path.attr('arr-begin')+'"]');
            },
            getEndOperator(path) {
                return $('.bpm_operator[data-unique_index="'+path.attr('arr-end')+'"]');
            },
            getByCol : function (col) {
                return  $('.bpm_operator[data-unique_index][gridcol="'+col+'"]');
            },
            getByRowCol : function (row, col) {
                return  $('.bpm_operator[data-unique_index][gridrow="'+row+'"][gridcol="'+col+'"]');
            },
            getOperatorByRow : function (row) {
                return  $('.bpm_operator[data-unique_index][gridrow="'+row+'"]');
            },
            getHasResponsible: function () {
                return $('.bpm_operator[data-responsible_index]');
            },
            getByKey : function(key) {
                return $('.bpm_operator[data-unique_index="'+key+'"]');
            },
            get : function() {
                return $('.bpm_operator[data-unique_index]');
            },
            getListByIndexes : function (list) {
                var $arr = null,
                    count = list.length;

                if (count) {
                    $arr = this.getByKey(list[0]);
                }
                for (var i=1; i < count; i++) {
                    $arr = $arr.add(this.getByKey(list[i]));
                }

                return $arr;
            },
            parse : function (element) {
                var data;

                data = this.getModel(element);

                data['$'] =  element;
                data['key'] = element.data('unique_index');
                data['col'] = parseInt(element.attr('gridcol'));
                data['row'] = parseInt(element.attr('gridrow'));
                data['name'] = element.data('name');
                data['type'] = element.data('type');
                data['helper'] = element.attr('end-branches');

                return data;
            },
            getMarked : function () {
                return $('.bpm_operator[mark="marked"]');
            },
            getHelper : function (endKey) {
                return  this.getByKey(endKey);
            },
            recountNextOperators : function(nextInd, direction, helper){
                this.markOperators(nextInd);
                this.moveMarkedOperators(direction);
                this.unmarkOperators();
            },
            moveMarkedOperators : function(direction){
                if ($('.bpm_operator[mark="marked"]').length>0) {
                    $('.bpm_operator[mark="marked"]').each(function(){
                        if (direction=='left') {
                            var currCol = parseInt($(this).attr('gridcol'))-1;
                        } else if (direction=='right') {
                            var currCol = parseInt($(this).attr('gridcol'))+1;
                        }
                        $(this).attr('gridcol',currCol+'');
                    });
                }
            },
            markOperators : function(indexStart, indexStop) {
                $('.bpm_operator[data-unique_index="'+indexStart+'"]').attr('mark','inwork');
                for (k=0; k<100; k++) {
                    if ($('.bpm_operator[mark="inwork"]').length>0) {
                        $('.bpm_operator[mark="inwork"]').each(function(){
                            $('svg.arrows path.arrow[arr-begin="'+$(this).data('unique_index')+'"]').each(function(){
                                if ($(this).attr('arr-end')!=indexStop) {
                                    $('.bpm_operator[data-unique_index="'+$(this).attr('arr-end')+'"]').attr('mark','inwork');
                                }
                            });
                            $(this).attr('mark','marked');
                        });
                    } else {
                        k=100;
                    }
                }
            },
            unmarkOperators : function(indexStart, indexStop){
                if (!indexStart && !indexStop) { // unmark all operators
                    $('.bpm_operator[mark="marked"]').each(function(){
                        $(this).removeAttr('mark');
                    });
                } else { // unmark from indexStart to indexStop
                    $('.bpm_operator[data-unique_index="'+indexStart+'"]').attr('mark','inwork');
                    for (k=0; k<100; k++) {
                        if ($('.bpm_operator[mark="inwork"]').length>0) {
                            $('.bpm_operator[mark="inwork"]').each(function(){
                                $('svg.arrows path.arrow[arr-begin="'+$(this).data('unique_index')+'"]').each(function(){
                                    if ($(this).attr('arr-end')!=indexStop) {
                                        $('.bpm_operator[data-unique_index="'+$(this).attr('arr-end')+'"]').attr('mark','inwork');
                                    }
                                });
                                $(this).removeAttr('mark');
                            });
                        } else {
                            k=100;
                        }
                    }
                    $('.bpm_operator[data-unique_index="'+indexStop+'"]').removeAttr('mark');
                }
            },
            // оптимізація операторів на вільні місця зліва.
            moveToLeft: function ($operator) {
                var $list = $operator || BpmOperator.get();

                $.each($list, function (key, data) {
                    var modelArrow,
                        status = true,
                        $this = $(this),
                        modelOperator = BpmOperator.getModel($this);

                    modelArrow = Arrows.getModel(modelOperator.listArrows.begin);

                    while (modelArrow && modelArrow.d.width > 144 && status == true) {
                        var modelOperator = BpmOperator.getModel(BpmOperator.getByKey(modelArrow.end)),
                            $element = BpmOperator.getByRowCol(modelOperator.row, modelOperator.col-1);

                        if (!$element.length) {
                            $operator.attr('gridcol', modelOperator.col-1);
                            Arrows.recounts([
                                Arrows.getByBegin(modelOperator.key),
                                Arrows.getByEnd(modelOperator.key)
                            ])
                            modelArrow = modelArrow.update();
                        } else {
                            status = false
                        }
                    }
                });
            },
            remove: function () {

            },
            destroy: function () {

            }
        }

        for(var key in _private) {
            _self[key] = _private[key];
        }

        for(var key in BpmOperator) {
            _self[key] = BpmOperator[key];
        }

        exports.BpmOperator = BpmOperator;

    })(param);

    var BpmOperator = param.BpmOperator;

    //обект работы со схемой.
    var Scheme = {
        type: 'scheme',
        NAME_OPERATOR_BEGIN: 'begin',
        NAME_OPERATOR_END: 'end',
        NAME_OPERATOR_TIMER: 'timer',
        NAME_OPERATOR_NOTIFICATION: 'notification',

        get: function () {
            return ProcessObj.BPM.schema;
        },
        getResponsibleByIndex : function (index) {
            var item,
                r = null,
                scheme = this.get();

            for (var i = 0; i < scheme.length; i++) {
                item = scheme[i];

                if (item.unique_index == index) {
                    r = item;
                    break;
                }
            };

            return r;
        },
        getListElementsUniqueIndexByResp: function (key) {
            var item,
                r = [],
                scheme = this.get();

            for (var i = 0; i < scheme.length; i++) {
                item = scheme[i];

                if (item.unique_index == key) {
                    for (var j = 0; j < item.elements.length; j++)
                    {
                        r.push(item.elements[j].unique_index);
                    }
                    break;
                }
            };

            return r;
        },
        getOperatorByIndex : function (index) {
            var responsible,
                item,
                data = null,
                scheme = this.get();

            for (var i = 0; i < scheme.length; i++) {
                for (var j = 0; j < scheme[i].elements.length; j++) {
                    item = scheme[i].elements[j];
                    if (item.unique_index == index) {
                        data = item;
                        responsible = i;
                        break;
                    }
                }
            };

            if (data) {
                data = _self.createParent(this.type, data);

                data['responsible'] = {
                    'ug_id' : scheme[responsible].ug_id,
                    'ug_type' : scheme[responsible].ug_type,
                    'unique_index' : scheme[responsible].unique_index,
                    'title' : scheme[responsible].title,
                    'type' : scheme[responsible].type
                }
            }

            return data;
        },
        getBeginOperator: function () {
            var item,
                operator = null,
                scheme = this.get();

            for (var i = 0; i < scheme.length; i++) {
                for (var j = 0; j < scheme[i].elements.length; j++) {
                    item = scheme[i].elements[j];
                    if (item.name == this.NAME_OPERATOR_BEGIN) {
                        operator = item;
                        break;
                    }
                }
            };

            return operator;
        },
        getEndOperator: function () {
            var item,
                operator = null,
                scheme = this.get();

            for (var i = 0; i < scheme.length; i++) {
                for (var j = 0; j < scheme[i].elements.length; j++) {
                    item = scheme[i].elements[j];
                    if (item.name == this.NAME_OPERATOR_END) {
                        operator = item;
                        break;
                    }
                }
            };

            return operator;
        }
    }

    var Migration = {
        init : function(){
            if(ProcessObj.versions.script == ProcessObj.versions.schema){
                return;
            }

            switch(ProcessObj.versions.schema){
                case null:
                case '1':
                    this.to2();
                    break;
                default:
                    return;
            }
        },
        to2 : function(){
            ProcessObj.versions.schema = '2';

            //RUN
            var modelBPM, modelOperator, arrows, currentElement, firstArrow, _element, $operator,
                i = 200;

            modelBPM = BPMCtrl.createModel();
            modelOperator = BpmOperator.getModel(BpmOperator.getByKey(Scheme.getBeginOperator().unique_index));
            modelBPM.setElement(modelOperator);

            while (i > 0) {
                i--;
                currentElement = modelBPM.getCurrentElement();
                if (currentElement.isEnd()) { //exit
                    i = -1;
                    continue;
                }

                arrows = modelBPM.getNextArrows();

                //exception
                if (!arrows.length) {
                    var modelOperator = BpmOperator.getModel(modelBPM.goNextElement());
                    arrows = modelBPM._next_arrows = modelOperator.arrows;

                    if (modelBPM._currentElement['end-branches']) {
                        var json = {
                            'gridrow': modelBPM._currentElement.$.attr('gridrow')
                        }
                        if (modelOperator.isHelperHasEmptyBranch()) {
                            json['gridcol'] = parseInt(modelBPM._currentElement.$.attr('gridcol')) + 1;
                            modelBPM.nextCollumn();
                        }

                        modelOperator.attr(json).update(json);
                        if (modelBPM.drawHelper(modelOperator)) {
                            continue;
                        }
                    }
                }
                // Якщо пуста структура з кількома гілками, то в
                if (arrows.length > 1) {
                    //ставимо в чергу
                    firstArrow = arrows.shift();
                    $.each(arrows, function (key, value) {
                        modelBPM.setElementToLine(value.unique_index);
                    })
                    arrows = [firstArrow]; // select 1 branch;
                }
                _element = Scheme.getOperatorByIndex(arrows[0].unique_index);

                if (_element == null) {
                    var model = BpmOperator.getModel(BpmOperator.getByKey(Scheme.getEndOperator().unique_index));
                    modelBPM.setCurrentCollumn(model.prev().attr('gridcol'));
                    modelBPM.nextCollumn();
                    modelBPM.setElement(model);
                    i = -1;
                    continue;
                }

                modelBPM.setDrawnLine(arrows[0].unique_index);

                modelOperator = BpmOperator.getModel(BpmOperator.getByKey(_element.unique_index));

                if (modelOperator.isHelper()) {
                    if (modelOperator.isHelperHasEmptyBranch()) {
                        if (modelBPM.drawHelper(modelOperator)) {
                            continue;
                        }
                    }
                    if (modelBPM._lineElements.length) {
                        var rModel = modelBPM.switchToBranch(modelOperator);
                        if (rModel) {
                            modelBPM._lineElements = rModel.balance; // clear -1 branch;
                            var arrowModel = Arrows.getModel(Arrows.getByEnd(rModel.next_element_ui)),
                                operator = Scheme.getOperatorByIndex(arrowModel.begin);

                            modelBPM.setNextArrows(operator.arrows);
                            modelBPM.setCurrentCollumn(rModel.gridcol);
                            modelBPM._currentElement = rModel.model;

                            modelBPM.nextRow();
                            continue;
                        }
                    }
                }

                //row next operator == row prev operator
                var modelPrev,
                    $prev = modelOperator.prev();

                if ($prev.length == 1 && !$prev.is('[end-branches]')) {
                    modelBPM.setRow($prev.attr('gridrow'));
                    modelBPM.setCurrentCollumn($prev.attr('gridcol'));
                } else {
                    //calc max Row in кроми 1 гілки
                    modelPrev = BpmOperator.getModel($prev);
                    if ($prev.is('[end-branches]') && modelPrev.arrows[0].unique_index != modelOperator.unique_index) {
                        //знаходимо лінії які вже промалювали
                        var modelArrow, $path, $mark, row = [];
                        for (var key in modelBPM._listDrawnLines) {
                            $path = modelPrev.listArrows.end.filter('[arr-end="'+ modelBPM._listDrawnLines[key] +'"]')
                                .not(modelOperator.listArrows.begin);

                            if ($path.length) {
                                modelArrow = Arrows.getModel($path);
                                BpmOperator.unmarkOperators();

                                //exception
                                var endBranches = modelPrev.$.attr('end-branches');
                                if (modelArrow.end == endBranches) { continue; }

                                BpmOperator.markOperators(modelArrow.end, endBranches);

                                $mark = BpmOperator.getMarked();
                                //макс рядок в И ИЛИ
                                $.each($mark.filter('[end-branches]'), function () {
                                    var value = 0,
                                        model = BpmOperator.getModel($(this));

                                    if (model.listArrows.end.length) {
                                        value = model.listArrows.end.length - 1;
                                    }
                                    row.push(parseInt(model.$.attr('gridrow')) + value);
                                });

                                row.push(BpmOperator.getMaxAttrList($mark,'gridrow'));
                            }
                        }
                        if (row.length) {
                            row = Math.max.apply(null, row) + 1;
                            modelBPM.setRow(row);
                        }
                        //model.arrows
                    };
                }

                modelBPM.nextCollumn();
                modelBPM.setElement(modelOperator);

                if (modelOperator.isHelper()) {
                    modelBPM.setNextArrows(modelOperator.arrows);
                }
            }
            Arrows
                .recountAll()
                .sign();
            _self.saveSchema();
        }
    };

    var ModelCell ={
        status: false, // true - fixed, false -  not fixed
        operators: null,
        arrows: null,
    };

    var ModelBPM = {
        _cells: null, // {row_col 1_1:{}, 21_20:{}....}
        _currentCollumn: null,  // поточна колонка
        _maxCollumn: null,  // максимальна колонка
        _maxRow: null,  // максимальний рядок
        _currentRow: null,  // поточний рядок
        _currentElement: null,  // поточний елемент
        _lineElements:null, // [], черга елементів на побудову.
        _listDrawnLines: null,

        _next_arrows: null, // наступні стрілки з оператора

        //key of cell
        getKey: function () {
            return this._currentRow + '_' + this._currentCollumn;
        },
        goNextElement: function() {
            var $operator,
                key = this._currentElement.$.attr('end-branches');

            if (key) {
                $operator = BpmOperator.getByKey(key);
            } else {
                //simple $operator
                $operator = this._currentElement.next();
            }

            return $operator;
        },
        getNextArrows: function () {
            //вертаємо лінії які ще не проходили.
            var array = [],
                _this = this;

            // only in struct I, AND operators
            if (this._next_arrows.length > 1) {
                var label;

                $.each(this._next_arrows, function (i, json) {
                    if ($.inArray(json.unique_index, _this._listDrawnLines) < 0) {
                        array.push(json);
                    }
                });
            } else {
                array = this._next_arrows;
            }

            return array;
        },
        getCurrentElement: function () {
            return this._currentElement;
        },
        setDrawnLine: function (arrow_index) {
            this._listDrawnLines.push(arrow_index); // set index of arr-end
        },
        switchToBranch: function (modelHelper) {
            var select, item, label,
                balance = [];

            for (var key in this._lineElements) {
                item = this._lineElements[key];
                if (item.model.$.attr('end-branches') === modelHelper['unique_index'] && !label) {
                    select = item; // only one time
                    label = true;
                } else {
                    balance.push(item);
                }
            };

            if (select) {
                select.balance = balance;
            }

            return select ? select : null;
        },
        drawHelper: function (modelOperator) {
            var label = null,
                $operator = modelOperator.prev().filter('[end-branches]');
            if ($operator.length) {
                var model = BpmOperator.getModel($operator);

                this.setHelper(modelOperator);
                //this.setRow(parseInt(modelOperator.$.attr('gridrow')));

                // if (modelOperator.isHelperHasEmptyBranch()) {
                //     this.setCurrentCollumn(parseInt(modelOperator.$.attr('gridcol'))+1);
                // }
                label = true;
            }
            return label;
        },
        setNextArrows: function(arrows) {
            this._next_arrows = arrows;
        },
        setHelper: function (model) {
            if (model && model.isHelper()) {
                if (this._lineElements.length) {
                    var rModel = this.switchToBranch(model);
                    if (!rModel) {
                        //we drawwing
                        var json, maxCol,
                            element = BpmOperator.getByAttr('[end-branches="' + model.unique_index + '"]');
                            maxCol = BpmOperator.getMaxAttrList(model.prev(),'gridcol');

                        json = {
                            'gridcol': maxCol + 1,
                            'gridrow': element.attr('gridrow'),
                        }

                        model.attr(json).update(json);

                        return this;
                    }
                }

                //empty branches end lineElements :)
                var json, maxCol,
                    element = BpmOperator.getByAttr('[end-branches="' + model.unique_index + '"]');

                maxCol = BpmOperator.getMaxAttrList(model.prev(), 'gridcol') + 1;
                json = {
                    'gridcol': maxCol,
                    'gridrow': element.attr('gridrow'),
                }
                this.setCurrentCollumn(maxCol);
                this.setNextArrows(model.arrows);
                model.attr(json).update(json);
            }
            return this;
        },
        setElement: function (modelOperator) {
            var json, priorityRow,
                row = this._currentRow,
                nextArrows = modelOperator.arrows;

            if (modelOperator.isEnd()) {
                row = 1;
            }

            if (modelOperator.isHelper()) {
                // if (modelOperator.unique_index == "f469f8cca7a4a773d3731148981be3e0") { debugger;}

                if (this._lineElements.length) {
                    //draw helper
                    var modelOperatorNext,
                        $operator = this.goNextElement();

                    if ($operator.length) {
                        modelOperatorNext = BpmOperator.getModel($operator);
                        nextArrows = modelOperatorNext.arrows;

                        if (modelOperatorNext.isHelper()) {
                            nextArrows = modelOperatorNext.arrows;
                        }
                    } else {
                        //exception in helper
                        nextArrows = modelOperator.arrows;
                    }
                } else {
                    //lineElements is empty
                    this.setCurrentCollumn(BpmOperator.getMaxAttrList(modelOperator.prev(), 'gridcol')+1);
                }

                var element = BpmOperator.getByAttr('[end-branches="' + modelOperator.unique_index + '"]');

                row = element.attr('gridrow');
                col = BpmOperator.getMaxAttrList(modelOperator.prev(), 'gridcol');
                this.setCurrentCollumn(col+1);
            }

            json = {
                'gridcol': this._currentCollumn,
                'gridrow': row,
            }

            this.cells[this.getKey()]['operators'] = modelOperator;
            //this.setRow(row);

            this._next_arrows = nextArrows;
            this._currentElement = modelOperator;
            modelOperator.attr(json).update(json);
        },
        //наступний елемент зі схеми
        nextElement: function () {

        },
        setCurrentCollumn: function (number) {
            this._currentCollumn = number;

            this._maxCollumn = this._currentCollumn > this._maxCollumn ? this._currentCollumn : this._maxCollumn;

            if (!this.cells[this.getKey()]) {
                for (var i = 1; i <= this._currentRow; i++) {
                    this.cells[i+'_'+this._currentCollumn] = Object.assign({}, ModelCell);
                }
            }
        },
        nextCollumn: function () {
            this._currentCollumn++;
            this._maxCollumn = this._currentCollumn > this._maxCollumn ? this._currentCollumn : this._maxCollumn;

            if (!this.cells[this.getKey()]) {
                for (var i = 1; i <= this._currentRow; i++) {
                    this.cells[i+'_'+this._currentCollumn] = Object.assign({}, ModelCell);
                }
            }

            return this._currentCollumn;
        },
        nextRow: function () {
            this._currentRow++;
            this._maxRow = this._currentCollumn > this._maxRow ? this._currentCollumn : this._maxRow;

            if (!this.cells[this.getKey()]) {
                for (var i = 1; i <= this._currentCollumn; i++) {
                    this.cells[this._currentRow + '_' + i] = Object.assign({}, ModelCell);
                }
            }

            return this._currentRow;
        },
        setRow: function (number) {
            this._currentRow = number;
        },
        //Добавити елементи в чергу
        setElementToLine: function (unique_index) {

            var model = {
                'next_element_ui' : unique_index, // unique_index arr-end because is next element
                'gridcol' : this._currentCollumn,
                'model': this._currentElement
            }

            this._lineElements.push(model);
        },
        getElementsFromLine: function () {
          return this._lineElements;
        },
        addCol: function() {

        },
        addRow: function () {

        },
        _constructor: function () {
            this.cells = {}
            this.cells['1_1'] = Object.assign({}, ModelCell);
            this._currentCollumn = 1;
            this._currentRow = 1;
            this._lineElements = [];
            this._listDrawnLines = [];
        }
    }

    var BPMCtrl = {
        name_class_object: 'ModllBPM',
        createModel: function () {
            var model = Object.assign({}, ModelBPM);

            model._constructor();

            $('.bmp-container').data(this.name_class_object, model);

            return model;
        },
        getModel: function () {
            return $('.bmp-container').data()[this.name_class_object];
        },
        setElements: function(model) {

        },
        fixedCells: function(model) {

        },
        moveDownNotFixedCells: function(array) {

        },
        moveRightOperators: function () {

        },
        findFreeCol: function () {

        },
        isFreeColByOperator: function () {

        }
    }

    //public property

    _private = {
        PROCESS_ACTION_MC_EDIT		: 'mc_edit',
        PROCESS_ACTION_MC_VIEW		: 'mc_view',

        createParent: function (type, json) {
            var data = null;

            switch (type) {
                case Scheme.type: {
                    data = Object.create(ProcessObj.getModel());
                    break;
                }
                case ModelProcess.type: {
                    data = Object.create(ModelGlobal.protected);
                    break;
                }
                case BpmOperator.type: {
                    data = Object.create(ProcessObj.getModel());
                }
                default: {

                }
            }

            // only parent object;
            if (data) {
                for (var key in json) {
                    data[key] = json[key];
                }
            }

            return data;
        },

        setVersion: function () {
            $('.bpm_block').attr('data-version', _self.getVersion());
        },

        renameKey: function (newKey, oldKey, json) {
            var o = Object.assign({}, json);

            o = Object.defineProperty(o, newKey, Object.getOwnPropertyDescriptor(o, oldKey));
            delete o[oldKey];

            return o;
        },
        getResponsibleList : function(){
            var list = [],
                arrAllowId = [];

            $.each(Scheme.get(), function(i, value){
                var object = Object.assign({}, value);
                object.elements = [];
                arrAllowId.push(object.ug_id);
                list.push(object);
            });

            return list;
        },


        /**
         * getSchema
         * Собирает схему из элементов верстки для сохранения
         */
        getSchemaHtml : function(){
            var schema = [],
                responsible_list = this.getResponsibleList();

            // собрать...
            $.each(responsible_list, function(i, responsible){
                var $operators,
                    elementItem, arrowsArr, arrowItem,
                    elementsArr = [];

                var list = Scheme.getListElementsUniqueIndexByResp(responsible.unique_index);
                $operators = BpmOperator.getListByIndexes(list) || $([]);

                //add new element if it is;
                if (i == 0 ){
                    var $newItem = $('.bpm_operator[data-new="true"]');
                    $operators = $operators.add($newItem.removeData('data-new'));
                }

                $operators.each(function(){
                    var $this = $(this),
                        model = BpmOperator.getModel($this);

                    var name = $this.data('name'),
                        unique_index = $this.data('unique_index'), //model['data-unique_index'],
                        gridrow = $this.attr('gridrow'),
                        gridcol = $this.attr('gridcol'),
                        title = model.$.find('.bpm_title').text(),
                        helper = model['end-branches'];

                    var arrowBegin = Arrows.getByBegin(unique_index);

                    arrowsArr = [];
                    arrowItem = {
                        'unique_index' : '',
                        'type' : '',
                        'title' : '',
                    };

                    if (arrowBegin.length) {
                        if (arrowBegin.length>1) {
                            for (i=0; i<arrowBegin.length; i++) {
                                var branch = arrowBegin.filter('[branch="'+(i+1)+'"]');
                                arrowsArr.push({
                                    'unique_index' : branch.attr('arr-end'),
                                    'type' : '',
                                    'title' : (branch.attr('title')) ? branch.attr('title') : '',
                                });
                            }
                        } else {
                            arrowBegin.each(function(){
                                var _this = $(this);
                                var title = _this.attr('title'),
                                    arrowEnd = _this.attr('arr-end');
                                arrowItem.title = (title) ? title : '';
                                arrowItem.unique_index = (arrowEnd) ? arrowEnd : '';
                                arrowsArr.push(arrowItem);
                            });
                        }
                    } else {
                        arrowsArr = [arrowItem];
                    }

                    elementItem = {
                        'type' : 'operation',
                        'name' : name,
                        'title' : title,
                        'unique_index' : unique_index,
                        'unique_index_parent' : [],
                        'coordinates' : {
                            'row' : gridrow,
                            'col' : gridcol
                        },
                        'arrows' : arrowsArr
                    };

                    if (name=='and' && !$(this).is('.and_helper') || name=='condition' && !$(this).is('.and_helper') && $(this).attr('end-branches')) {
                        elementItem.helper = helper;
                        elementsArr.push(elementItem);
                    } else {
                        elementsArr.push(elementItem);
                    }
                });

                schema.push({
                    'type' : 'responsible',
                    'ug_id' : responsible.ug_id,
                    'ug_type' : responsible.ug_type,
                    'flag' : responsible.flag,
                    'unique_index' : responsible.unique_index,
                    'elements' : $operators.length ? elementsArr : null
                });
            });
            return schema;
        },

        saveSchema : function(){
            var data = {
                'process_id' : ProcessObj.process_id,
                'version_schema' : ProcessObj.versions.schema,
                'schema' : _self.getSchemaHtml()
            }

            // send to server
            AjaxObj
                .createInstance()
                .setData(data)
                .setAsync(false)
                .setUrl('/module/BPM/saveSchema/' + ProcessObj.copy_id)
                .setCallBackSuccess(function(data){
                    if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else {
                        if(data.status){
                            if(ProcessObj.process_status == ProcessObj.PROCESS_B_STATUS_IN_WORK){
                                ProcessObj.BPM.bpmParamsRun(ProcessObj.PROCESS_BPM_PARAMS_ACTION_CHECK);
                            }
                            ProcessObj.BPM.setSchema(data.schema);

                            var modelProcess = ProcessObj.getModel();
                            modelProcess.update();
                        } else {
                            Message.show(data.messages, false);
                        }
                    }
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                })
                .send()
        },
    };

    _public = {
        getModel : function() {
            return $('.bpm_block').data()[ModelProcess.name_model_object];
        },
        getVersion : function () {
            return _self.versions.script;
        },
        createModel : function () {
            return ModelProcess.create();
        }
    }

    var ViewProcess = {
        modelProcess: null,
        timingUpdate: function (modelTiming, boolTemplate) {
            var $timeContainer, $timeItem, $timeContainer,
                modelGlobal = Global.getModel();
                $timingBlock = $('.element[data-type="timing-block"]');

            $timeItem = $timingBlock.find('.default').clone().removeClass('default');
            $timeContainer = $timingBlock.find('.element[data-type="timing-container"]')
            $timeContainer.empty(); // clear all

            for (var key in modelTiming) {
                var date, $item, momentDataEnding,
                    element = modelTiming[key];

                $item = $timeItem.clone()
                    .attr({
                        'data-col': element.col
                    })
                    .css({
                        left: element.left
                    });

                date = element.date;

                if (boolTemplate) {
                    momentDataEnding = moment(element.date, modelGlobal.FORMAT_DATE);
                    date = Math.abs(moment(modelTiming[0].date, modelGlobal.FORMAT_DATE).diff(momentDataEnding, "days")) + 1;
                } else {
                    date = this.modelProcess.getData(moment(date, modelGlobal.FORMAT_DATE).format(modelGlobal.getCurrentFormatDate()));
                }

                $item.find('[data-type="date"]').text(date);
                $timeContainer.append($item);
            }
        }
    };

    var ModelProcess= {
        type: 'ModelProcess',

        name_model_object: 'modelProcess',
        _timing: false, // true - enable; false - disable

        create: function () {
            var data = _self.createParent(this.type, ModelProcess);

            $('.bpm_block').data(this.name_model_object, data);

            data.constructor();

            return data;
        },

        update: function () {
            this.createModelsOperators()
                .createModelsArrows()
                .updateRelationsOperators()
                .timingUpdate();
        },
        updateRelationsOperators: function () {
            var modelListArrows,
                $list = BpmOperator.get();

            modelListArrows = {
                end: null,
                begin: null,
                update: function() {
                    var $list = $([]);

                    $.each(this.end || [], function () {
                        $list = $list.add($(this));
                    });

                    $.each(this.begin || [], function () {
                        $list = $list.add($(this));
                    });
                    Arrows.recounts($list);
                }
            }

            $.each($list, function () {
                var model = BpmOperator.getModel($(this));

                var outputArrows = Arrows.getByBegin(model.unique_index),
                    inputArrows = Arrows.getByEnd(model.unique_index);

                model.listArrows = Object.assign({}, modelListArrows);
                model.listArrows['end'] = outputArrows.length ? outputArrows : null;
                model.listArrows['begin'] = inputArrows.length ? inputArrows : null;


                model['next'] = function () {
                    var $list = $([]);

                    for (var key in model.arrows) {
                        $list = $list.add(BpmOperator.getByKey(model.arrows[key].unique_index));
                    }

                    return $list;
                };
                model['prev'] = function () {
                    var $list = $([]),
                        $arrow = Arrows.getByEnd(model.unique_index);

                    $.each($arrow, function () {
                        var modelArrow = Arrows.parse($(this));

                        $list = $list.add(BpmOperator.getByKey(modelArrow.begin));
                    });

                    return $list;
                };
            });

            return this;
        },
        //verified!
        createModelsArrows: function () {
            $.each(Arrows.get(), function () {
               Arrows.createModel($(this));
            });

            return this;
        },
        createModelsOperators: function () {
            var $listOperators = BpmOperator.get();

            $.each($listOperators, function () {
                BpmOperator.createModel($(this));
            });

            return this;
        },
        // виставляємо блоки таймінга
        timingUpdate: function () {
            var $list, $operatorEnd,
                modelOperatoEnd,
                arrayModelTiming = [];

            if (!this._timing) return;

            $operatorEnd = BpmOperator.getByAttr('[data-name="end"]')

            if (!$operatorEnd.length) return;

            modelOperatoEnd = BpmOperator.getModel($operatorEnd);

            var modelGlobal = Global.getModel(),
                originFormat = modelGlobal.FORMAT_DATE,
                count = parseInt(modelOperatoEnd.$.attr('gridcol'));

            for (var i= 1; i <= count; i++) {
                $list = BpmOperator.getByCol(i);

                if ($list.length) {
                    var model = BpmOperator.getModel($list.first()),
                        momentCurrentDate = moment(model.date_ending, originFormat);

                    // find max of date time;
                    $list = $list.slice(1);
                    $.each($list, function () {
                        var model = BpmOperator.getModel($(this)),
                            momentDataEnding = moment(model.date_ending, originFormat);

                        // this date > momentCurrentDate
                        if (momentCurrentDate.diff(momentDataEnding, 'minutes') < 0) {
                            momentCurrentDate = momentDataEnding;
                        }
                    });

                    arrayModelTiming.push({
                        'left': model.$.offset().left - 15,
                        'col': i,
                        'date': momentCurrentDate.format(originFormat)
                    });
                }
            }
            ViewProcess.modelProcess = ProcessObj.getModel();
            ViewProcess.timingUpdate(arrayModelTiming, ProcessObj.mode == ProcessObj.PROCESS_MODE_CONSTRUCTOR);

            return this;
        },
        constructor: function () {

            return this;
        }
    }

    ProcessObj = {
        PROCESS_B_STATUS_IN_WORK    : 2, // in_work
        PROCESS_B_STATUS_STOPED		: 3, // stoped
        PROCESS_B_STATUS_TERMINATED	: 1, // terminated

        PROCESS_MODE_CONSTRUCTOR      : 'constructor',
        PROCESS_MODE_RUN              : 'run',

        PROCESS_MODE_CHANGE_VIEW		: 'view',
        PROCESS_MODE_CHANGE_EDIT		: 'edit',

        PROCESS_ARROW_STATUS_ACTIVE   : 'active',
        PROCESS_ARROW_STATUS_UNACTIVE : 'unactive',

        PROCESS_AGREETMENT_APPROVE    : '1', // =Завершена
        PROCESS_AGREETMENT_REJECT     : '4', // =Создана

        PROCESS_PARTICIPANT_ACTION_ADD    : 'add',
        PROCESS_PARTICIPANT_ACTION_CHANGE : 'change',
        //PROCESS_PARTICIPANT_DELETE_PARTICIPANT_ROLE : 'delete_participant_role',

        PROCESS_BPM_PARAMS_ACTION_CHECK  : 'action_check',
        PROCESS_BPM_PARAMS_ACTION_UPDATE : 'action_update',

        PROCESS_ACTION_START		: 'start',
        PROCESS_ACTION_STOP			: 'stop',
        PROCESS_ACTION_TERMINATE	: 'terminate',

        versions : {
            script : '2',
            schema : null
        },
        copy_id : 9,
        process_id : null,
        process_status : null, // string
        this_template : null,  // booleansaveSchema
        mode : null, // constructor|run
        mode_change : null, // edit|view
        is_bpm_view : false,
        binding_object_check : true,

        branchSignatures: function () {
            Arrows.sign();
        },
        statusRightPanel : function (status) {
            var $wrapper = $('.wrapper');

            if ($('.bpm_block').length) {
            if (status || QuickViewPanel.isOpen()) {
                    $wrapper.addClass('bpm_process');
                } else {
                    $wrapper.removeClass('bpm_process');
                }

                ProcessObj.recountRespBlocks();
            } else {
                $wrapper.removeClass('bpm_process');
            }
        },
        scenario : {
            init : function () {
                var $block = $('.CodeMirror');

                ($('[id="code"]').is('[disabled]')) ? $block.addClass('disabled') : $block.removeClass('disabled');
            },
            render: function () {
                window.bpmOperatorScript = CodeMirror.fromTextArea(document.getElementById("code"), {
                    lineNumbers: true,
                    extraKeys: {
                        "F11": function(cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function(cm) {
                            if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                        }
                    }
                });
            }
        },
        getParams : function (element){
            var r = {};

            if (element.length){
                if (element.data('unique_index')) {
                    var $unit = element.closest('.bpm_unit');

                    r = { // element
                        $: element,
                        _this: element[0],
                        key : element.data('unique_index'),
                        col : parseInt(element.attr('gridcol')),
                        row : parseInt(element.attr('gridrow')),
                        name : element.data('name'),
                        type : element.data('type'),
                        ug_id : $unit.data('ug_id'),
                        ug_type : $unit.data('ug_type'),
                        endBranches: element.attr('end-branches'),
                        keyRespUnique: $unit.data('unique_index')
                    };
                } else {
                    r = { // path
                        $:element,
                        _this: element[0],
                        d : element.attr('d').split(' '),
                        arrBegin: element.attr('arr-begin'),
                        arrEnd: element.attr('arr-end'),
                        branchEnd: element.attr('branch-end'),
                        branch: element.attr('branch'),
                    }
                }
            }
            return r;
        },
        getPairsOfArrows: function () {
            var pairs = [],
                arrows = $('svg.arrows path.arrow'),
                bpmOperators = $('.bpm_operator[data-unique_index]');

            $.each(arrows, function() {
                var begOperator, endOperator,
                    _this = $(this),
                    path = ProcessObj.getParams(_this);

                begOperator = ProcessObj.getParams(bpmOperators.filter('[data-unique_index="'+path.arrBegin+'"]'));
                endOperator = ProcessObj.getParams(bpmOperators.filter('[data-unique_index="'+path.arrEnd+'"]'));

                if (path.d.length == 18) {
                    if (endOperator.col-begOperator.col>1 || Math.abs(begOperator.row-endOperator.row)>1) {
                        arrows.not('[d*="'+path.d[1]+'"]').filter(function () {
                            var element = [], _this = $(this),
                                d = _this.attr('d').split(' '),
                                term = Math.abs(d[2]-d[5]); // 'vertical' : 'horizontal'

                            element[0] = bpmOperators.filter('[data-unique_index="'+_this.attr('arr-begin')+'"]');
                            element[1] = bpmOperators.filter('[data-unique_index="'+_this.attr('arr-end')+'"]');
                            element[2] = bpmOperators.filter('[data-unique_index="'+$(path._this).attr('arr-end')+'"]');
                            element[3] = bpmOperators.filter('[data-unique_index="'+$(path._this).attr('arr-begin')+'"]');

                            if (d[4] - d[1] == 144 && d.length == 15
                                || _this.is('[data-is]') //(Math.abs(d[2]-d[5])==82
                                || (Math.abs(d[4]-d[1])==162)) { //min branch return
                                return false;
                            }

                            _this.attr('data-is', true);
                            if (Math.abs(d[5]-d[2])> 82) {
                                pairs.push({
                                    x: parseInt(d[1]),
                                    y: parseInt(d[2]),
                                    x1: parseInt(d[4]),
                                    y1: parseInt(d[5]),
                                    path: this,
                                    col: term ? parseInt(element[0].attr('gridcol')) : parseInt(element[3].attr('gridcol')),
                                    row: term ? parseInt(element[2].attr('gridrow')) : parseInt(element[1].attr('gridrow')),
                                    type: term ? 'vertical' : 'horizontal'
                                });
                            }

                            if (d.length != 15) {
                                term = d[7] - d[4]; // 'vertical' : 'horizontal'

                                if (!term) {
                                    return false;
                                }

                                pairs.push({
                                    x: parseInt(d[4]),
                                    y: parseInt(d[5]),
                                    x1: parseInt(d[7]),
                                    y1: parseInt(d[8]),
                                    path: this,
                                    col: !term ? parseInt(element[0].attr('gridcol')) : parseInt(element[3].attr('gridcol')),
                                    row: !term ? parseInt(element[2].attr('gridrow')) : parseInt(element[1].attr('gridrow')),
                                    type: !term ? 'vertical' : 'horizontal'
                                });
                            }

                        });
                    }
                }
            });
            arrows.removeAttr('data-is');

            return pairs;
        },
        listPointByCrossing:{
            set: function (col, row, element, type, ug_id, ug_type) {
                this.listPointByCrossing.push({ // set corner disabled
                    row : row,
                    col : col,
                    path : element,
                    type : type,
                    ug_id : ug_id,
                    ug_type : ug_type,
                });
            },
            get : function (col, row, operatorIndex, ug_id, ug_type) {
                var r = null;

                $.each(this.listPointByCrossing || [], function () {
                    var _this = $(this)[0];

                    if (_this['row'] == row && _this['col'] == col && _this.ug_id == ug_id && _this.ug_type==ug_type) {
                        r = true;
                    }
                    if (r) {
                        if ((operatorIndex == $(_this['path']).attr('arr-begin') || operatorIndex == $(_this['path']).attr('arr-end')) && _this.type == 'corner' ) {
                            r = null;
                        }
                        return false;
                    }
                })

                return r;
            },
            init : function () {
                var pairs, arrows = $('svg.arrows path'),
                    bpmOperators = $('.bpm_operator[data-unique_index]');

                this.listPointByCrossing = [];

                arrows.each(function() {
                    var begOperator, endOperator,
                        _this = $(this),
                        path = ProcessObj.getParams(_this);

                    begOperator = ProcessObj.getParams(bpmOperators.filter('[data-unique_index="'+path.arrBegin+'"]'));
                    endOperator = ProcessObj.getParams(bpmOperators.filter('[data-unique_index="'+path.arrEnd+'"]'));

                    if (path.d.length == 18) {
                        ProcessObj.listPointByCrossing.set(endOperator.col, endOperator.row, this, 'corner', endOperator.ug_id, endOperator.ug_type); // set corner disabled
                    }
                });

                pairs = ProcessObj.getPairsOfArrows();
                $.each(pairs, function () {
                    var currentRow, base = this;

                    $.each(pairs, function () {
                        var bmpOperator, element = this;

                        if (base.type != element.type && !$(element.path).is('[data-is]')) {
                            if (base.x < element.x && element.x< base.x1) { //by X
                                if (element.y > base.y && base.y > element.y1 || element.y < base.y && base.y < element.y1) {
                                    $(element.path).attr('data-is');
                                    currentRow = (base.type == 'horizontal') ? base.row : element.row;
                                    bmpOperator = $('.bpm_operator[data-unique_index]').filter('[data-unique_index="'+$(element.path).attr('arr-end')+'"]');
                                    ProcessObj.listPointByCrossing.set(element.col, currentRow, element.path, 'line', bmpOperator.closest('.bpm_unit').data('ug_id'));
                                }
                            }
                        }
                    });
                });
                arrows.removeAttr('data-is');
            }
        },

        init : function(){
            /*
        if(ProcessObj.copy_id === null){
            var sm_extension = $('.process_view_block.sm_extension, .list_view_block.sm_extension');
            if(sm_extension && typeof(sm_extension) != 'undefined')
                ProcessObj.copy_id = sm_extension.data('copy_id');
        }
        */

            if(ProcessObj.this_template === null){
                var sm_extension = $('.process_view_block.sm_extension, .list_view_block.sm_extension');
                if(sm_extension && typeof(sm_extension) != 'undefined')
                    ProcessObj.this_template = sm_extension.data('this_template');
            }


            if(ProcessObj.mode === null){
                ProcessObj.mode = (ProcessObj.this_template ? ProcessObj.PROCESS_MODE_CONSTRUCTOR : ProcessObj.PROCESS_MODE_RUN);
            }

            if(ProcessObj.mode_change === null){
                ProcessObj.mode_change = (ProcessObj.mode == ProcessObj.PROCESS_MODE_CONSTRUCTOR ? ProcessObj.PROCESS_MODE_CHANGE_EDIT : (ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN ? ProcessObj.PROCESS_MODE_CHANGE_VIEW : ProcessObj.PROCESS_MODE_CHANGE_VIEW));
            }

            if(ProcessObj.process_id !== null && ProcessObj.process_id){
                ProcessObj.is_bpm_view = true;
            }

        },

        setServerParams : function(params){
            this.versions.schema = params.version_schema;

            this.copy_id = params.copy_id;
            this.process_id = params.process_id;
            this.process_status = params.process_status;
            this.this_template = params.this_template;
            this.mode = params.mode;
            this.mode_change = params.mode_change;
            this.binding_object_check = params.binding_object_check;

            this.BPM.schema = params.BPM.schema;
            this.BPM.elements.operations = params.BPM.elements.operations;
            this.BPM.elements.responsible = params.BPM.elements.responsible;
        },

        recountNextOperators : function(nextInd, direction, helper){
            BpmOperator.markOperators(nextInd);
            BpmOperator.moveMarkedOperators(direction);
            BpmOperator.unmarkOperators();
        },


        BPM : {
            reDrawOfArrows: true, // перемальовувати лінії в даній ітерації
            schema : [],
            /**
             * elements
             */
            saveSchema: function () {
                _self.saveSchema();
            },
            recountArrows: function () {
                Arrows.recountAll();
            },
            unmarkOperators : function(indexStart, indexStop){
                if (!indexStart && !indexStop) { // unmark all operators
                    $('.bpm_operator[mark="marked"]').each(function(){
                        $(this).removeAttr('mark');
                    });
                } else { // unmark from indexStart to indexStop
                    $('.bpm_operator[data-unique_index="'+indexStart+'"]').attr('mark','inwork');
                    for (k=0; k<100; k++) {
                        if ($('.bpm_operator[mark="inwork"]').length>0) {
                            $('.bpm_operator[mark="inwork"]').each(function(){
                                $('svg.arrows path.arrow[arr-begin="'+$(this).data('unique_index')+'"]').each(function(){
                                    if ($(this).attr('arr-end')!=indexStop) {
                                        $('.bpm_operator[data-unique_index="'+$(this).attr('arr-end')+'"]').attr('mark','inwork');
                                    }
                                });
                                $(this).removeAttr('mark');
                            });
                        } else {
                            k=100;
                        }
                    }
                    $('.bpm_operator[data-unique_index="'+indexStop+'"]').removeAttr('mark');
                }
            },
            markOperators: function(indexStart, indexStop) {
                return BpmOperator.markOperators(indexStart, indexStop);
            },
            clear:function () {
                $('.bpm_unit .bpm_operator').not('[data-name="begin"]').not('[data-name="end"]').remove();
                var begin = $('.bpm_unit .bpm_operator[data-name="begin"]');
                var path = $('svg path.arrow[arr-begin="'+begin.attr('data-unique_index')+'"]');
                var end = $('.bpm_unit .bpm_operator[data-name="end"]');
                path.attr('arr-end', end.attr('data-unique_index'));
                $('svg path').not(path).remove();
            },

            refreshResponsible: function () {
                for(param in this.schema) {
                    var data = this.schema[param];

                    $('.bpm_unit[data-unique_index="'+ data.unique_index +'"]').find('.bpm_uname_title').text(data.title);
                };
            },

            elements: {
                responsible: '',
                operations: {}, //Запись данных
                arrows: {
                    'begin' : {'html' : ''}, //Начало
                    'end': {'html': ''}, //Конец
                    'condition': {'html': ''}, //Условие
                    'and': {'html': ''}, //И
                    'timer': {'html': ''}, //Таймер
                    'task': {'html': ''}, //Задача
                    'agreetment': {'html': ''}, //Согласование
                    'notification': {'html': ''}, //Оповещение
                    'data_record': {'html': ''}, //Запись данных
                    'scenario': {'html': ''}, //
                },
            },




            /**
             * getResponsible
             */
            getResponsible: function (params) {
                var responsible = this.elements.responsible;

                return responsible;
            },

            /**
             * getOperation
             */
            getOperation: function (operation_name) {
                var operation = this.elements.operations[operation_name]

                return operation;
            },

            /**
             * getArrow
             */
            getArrow: function (operation_name) {
                var arrow = this.elements.arrows[operation_name]

                return arrow;
            },


            /**
             * setSchema
             */
            setSchema: function(schema){
                this.schema = schema;
                return this;
            },


            /**
             * getSchema
             * Возвращает схему
             */
            getSchema: function () {

                return this.schema;
            },

            verifyEmptySpace: function (index, colCicle) {
                var operCicle, marked,
                    $arrows = $('svg.arrows');

                if (index) {
                    for (var v=1; v<100; v++) {
                        if ($('.bpm_operator[data-unique_index="'+index+'"][gridcol="'+colCicle+'"]').length) {
                            operCicle = $('.bpm_operator[data-unique_index="'+index+'"][gridcol="'+colCicle+'"]');

                            if (operCicle.is('[end-branches]')) {
                                BpmOperator.markOperators(operCicle.data('unique_index'), operCicle.attr('end-branches'));
                                $('.bpm_operator[data-unique_index="'+operCicle.attr('end-branches')+'"]').attr('mark','marked');
                                index = $arrows.find('.arrow[arr-begin="'+operCicle.attr('end-branches')+'"]').attr('arr-end');
                                colCicle = parseInt($('.bpm_operator[data-unique_index="'+operCicle.attr('end-branches')+'"]').attr('gridcol'))+1;
                            } else {
                                operCicle.attr('mark','marked');
                                index = $arrows.find('.arrow[arr-begin="'+operCicle.data('unique_index')+'"]').attr('arr-end');
                                colCicle++;
                            }
                        } else {
                            v=100;
                        }
                    }
                }

                marked = $('[mark]');
                marked.sort(function (a, b) {
                    var a = parseInt($(a).attr('gridcol')),
                        b = parseInt($(b).attr('gridcol'));

                    return (a > b) ? 1 : 0;
                })

                return marked;
            },

            /**
             * buildBPM
             * строит ВРМ из сохраненной или дефолтной схемы
             * Вызавается 1 раз после загрузки страницы
             */
            buildBPM: function(){
                var modelProcess = ProcessObj.createModel();

                ProcessObj.BPM.buildDefault();
                // update all models relate arrows

                var insertionPermission = false;

                ProcessObj
                    .dragInit()
                    .dropInit()
                    .activateDropdowns();

                //ProcessObj.inspection.init(false, true, true);

                Arrows.recountAll();
                modelProcess
                    .updateRelationsOperators()
                    .createModelsArrows();

                _self.setVersion();

                ProcessObj
                    .recountRespBlocks()
                    .branchSignatures();

                // Migrate schema
                Migration.init();

                modelProcess.update();
                Arrows.recountAll();
            },

            buildDefault : function(){ // построение сохраненной схемы beta версия
                var data,
                    $newRespBlock,
                    $blockBpm = $('.bpm_block'),
                    i = 0;

                data = {
                    'data-ug_id': ProcessObj.BPM.schema[i].ug_id,
                    'data-ug_type': ProcessObj.BPM.schema[i].ug_type,
                    'data-unique_index': ProcessObj.BPM.schema[i].unique_index,
                    'data-flag': (ProcessObj.BPM.schema[i].flag) ? ProcessObj.BPM.schema[i].flag+'' : ''
                }

                $newRespBlock = $(ProcessObj.BPM.elements.responsible).attr(data);

                data['data-responsible_index'] = ProcessObj.BPM.schema[i].unique_index,

                $newRespBlock.find('.bpm_uname_title').text(ProcessObj.BPM.schema[i].title);
                $newRespBlock.insertAfter($blockBpm.find('.bpm_unit:last')); // inserting responsible block

                while (i<ProcessObj.BPM.schema.length) { // responsible cycle
                    var o = 0;
                    while (o<ProcessObj.BPM.schema[i].elements.length){ // operators cycle
                        var json,
                            $newOperator,
                            thOpArr = ProcessObj.BPM.schema[i].elements[o], // data array for current operator
                            operatorName = thOpArr.name,
                            $currentUnit = $('.bpm_unit[data-unique_index]');

                        json = {
                            gridrow: thOpArr.coordinates.row,
                            gridcol: thOpArr.coordinates.col,
                            'data-unique_index': thOpArr.unique_index,
                            'end-branches': thOpArr.helper ? thOpArr.helper : null,
                            'data-status': (ProcessObj.mode==ProcessObj.PROCESS_MODE_CONSTRUCTOR) ? 'done': thOpArr.status
                        };

                        $newOperator = $(ProcessObj.BPM.elements.operations[operatorName]);
                        $newOperator.attr(json);

                        $newOperator.appendTo($currentUnit.find('.bpm_tree')); // adding operator
                        var model = BpmOperator.createModel($newOperator);

                        model
                            .update([data, json])
                            .showTitle(thOpArr.title);

                        if (thOpArr.coordinates.row >= $currentUnit.attr('rows')) {
                            $currentUnit.attr('rows' , parseInt(thOpArr.coordinates.row)+1+'');
                        }
                        o++;
                    }
                    i++;
                }

                var a = 0; // arrows cycle
                while (a<ProcessObj.BPM.schema.length) {
                    var o = 0;
                    while (o<ProcessObj.BPM.schema[a].elements.length){
                        if ($('div.bpm_operator[data-unique_index="'+ProcessObj.BPM.schema[a].elements[o].arrows[0].unique_index+'"]').length>0) {
                            var arrows = ProcessObj.BPM.schema[a].elements[o].arrows;

                            $.each(arrows, function(u){
                                var thArArr = ProcessObj.BPM.schema[a].elements[o], // data array for current operator
                                    $obpb = $('div.bpm_operator[data-unique_index="'+thArArr.unique_index+'"] .bpm_body'),  // operator body where path begins
                                    $sa = $('svg.arrows'), // arrows adding target
                                    numB = thArArr.unique_index,
                                    status = thArArr.arrows[u].status,
                                    numE = thArArr.arrows[u].unique_index,
                                    title = thArArr.arrows[u].title,
                                    colorArr = $obpb.css('background-color'),
                                    arrowClone = $('div.bpm_def path.arrow').clone(true);

                                if (colorArr=='rgb(255, 255, 255)') {
                                    colorArr = 'rgb(197, 197, 197)';
                                }
                                if (status == ProcessObj.PROCESS_ARROW_STATUS_ACTIVE) {
                                    colorArr =  colorArr;
                                    if (thArArr.name=='condition') {
                                        arrowClone.attr('is-active','true')
                                    }
                                } else {
                                    if (status == ProcessObj.PROCESS_ARROW_STATUS_UNACTIVE && ProcessObj.mode==ProcessObj.PROCESS_MODE_RUN && ProcessObj.process_status != ProcessObj.PROCESS_B_STATUS_TERMINATED) {
                                        colorArr =  'rgb(197, 197, 197)';
                                    }
                                }

                                if (arrows.length>1) {
                                    arrowClone.attr('branch',u+1+'');
                                }
                                if (title) {
                                    arrowClone.appendTo($sa).attr('stroke', colorArr+'').attr('arr-begin', numB+'').attr('arr-end', numE+'').attr('title', title+'');
                                } else {
                                    arrowClone.appendTo($sa).attr('stroke', colorArr+'').attr('arr-begin', numB+'').attr('arr-end', numE+'');
                                }
                                // adding arrow
                            });

                            $('svg.arrows path[is-active]').each(function () {
                                $(this).remove().clone().removeAttr('is-active').appendTo($('svg.arrows'));
                            });
                        }
                        o++;
                    }
                    a++;
                }
                Arrows.recountAll();
                ProcessObj.BPM.branchesRestore();

                if (!arguments[0] && arguments[0] != 'noSignatureOfBranch') {
                    ProcessObj.branchSignatures();
                }

            },
            getEmptyPlace: function (startRow, beginIndex, endIndex, arrow) {
                var i,
                    result = false,
                    bpmOperator = $('.bpm_operator[data-unique_index]');
                var beginOperator = bpmOperator.filter('[data-unique_index="'+beginIndex+'"]'),
                    endOperator = bpmOperator.filter('[data-unique_index="'+endIndex+'"]');
                var beginCol = parseInt(beginOperator.attr('gridcol')) + 1,
                    endCol = parseInt(endOperator.attr('gridcol')),
                    row = startRow,
                    maxRow = 59,
                    path = $('svg.arrows path.arrow[arr-end="'+endIndex+'"][branch]').not('[modifier=0]');
                var dad = beginOperator.closest('.bpm_unit').find(bpmOperator);

                while (row<=maxRow+1) {
                    var resultInRow = true;

                    for (i = beginCol; i<endCol; i++) {
                        if (dad.filter('[gridrow="'+row+'"][gridcol="'+i+'"]').length) {
                            resultInRow = false;
                        }
                    };
                    if (resultInRow) { // we tested on operator. It is verify by empty arrow
                        var probablyRow = row*100+24;
                        var helperEnd = dad.filter('[data-unique_index="'+arrow.attr('arr-begin')+'"]'),
                            andHelper = dad.filter('[data-unique_index="'+arrow.attr('arr-end')+'"]');

                        var crossing = $('svg.arrows path.arrow[branch][branch-end=true]').not('[modifier=0]').not('[arr-begin="'+beginIndex+'"]')
                            .filter(function () { // filter on inner branch
                                var d = $(this).attr('d').split(' ');
                                var result =  ((helperEnd.offset().left < d[1] && d[1] < andHelper.offset().left))  ? this : null; // find inner element in struct
                                return result;
                            }).map(function () { // We search vertical crossing with inner elements
                                var y,
                                    result = null,
                                    d = $(this).attr('d').split(' '),
                                    bpmOpCurrentLine = dad.filter('[data-unique_index="'+$(this).attr('arr-begin')+'"]'),
                                    andHelperParentDrawingLine = dad.filter('[data-unique_index="'+arrow.attr('arr-end')+'"]');
                                var newRow = parseInt(bpmOpCurrentLine.attr('gridrow'))*100+24 + parseInt($(this).attr('modifier'));

                                if (parseInt(andHelperParentDrawingLine.attr('gridcol')) > parseInt(bpmOpCurrentLine.attr('gridcol'))) {
                                    y = probablyRow;
                                    if ((((d[2]> y && y > d[5] || d[2]< y && y < d[5]) || (y == newRow)))) {
                                        //if ((((d[2]> y && y > d[5]) || (y == newRow)))) {
                                        //if (d[2]> y && y > d[5] || y == d[5] || (y == newRow)) {
                                        result = this;
                                    }
                                }

                                return result;
                            }).get();

                        if (!crossing.length) {
                            maxRow = 0; //if free than exit
                            result = row
                        }
                    }
                    row++;
                };

                return result ? result : row;
            },
            createDataUnique: function () {
                return Global.generateMD5((+new Date()).toString());
            },

            sendToClient : function () {
                var client = $('.outer_unit').removeClass('hide');
                var svg = $('svg.arrows');
                var listNotification = $('.bpm_operator[data-unique_index][data-name="notification"]');
                var arrow = $('svg.hidden').find('path');

                listNotification.each(function () {
                    var d,
                        _this = $(this),
                        uniqueIndex = ProcessObj.BPM.createDataUnique(),
                        currentClone = arrow.clone();
                    var color = _this.find('.bpm_body').css('background-color');

                    d = currentClone.attr('d').split(' ');
                    var bpmTree = client.find('.bpm_tree');

                    if (!bpmTree.length) {
                        client.append('<div class=bpm_tree></div>');
                    }
                    client.find('.bpm_tree').append('<div class="bpm_operator" gridrow="1" data-unique_index="'+uniqueIndex+'" gridcol="'+_this.attr('gridcol')+'"><div class="bpm_body"></div></div>');
                    currentClone.attr('arr-begin', _this.attr('data-unique_index')).attr('stroke', color).attr('arr-end', uniqueIndex).attr('stroke-dasharray', 5);

                    svg.prepend(currentClone);
                });

                Arrows.recountAll();
                ProcessObj.branchSignatures();
            },
            branchesRestore : function() { // Restore branches|| checking branches and define numbers
                var element = $('.element[data-type="responsible"] .element[end-branches]');
                prevAnd = false;
                if (element.length>0) {
                    element.each(function(){
                        //endBranches = ProcessObj.defineEndBranches($(this).data('unique_index'));
                        //$(this).attr('end-branches',endBranches+'');
                        indEndBranches = $(this).attr('end-branches');
                        $('.bpm_operator[data-unique_index="'+indEndBranches+'"]').addClass('and_helper');
                    });
                }
                ProcessObj.BPM.setBranchEnds();
                Arrows.recountAll();
            },
            restrictArrows : function(){
                $('.bpm_operator[mark="marked"]').each(function(){
                    var markedInd = $(this).data('unique_index');
                    $('svg.arrows path.arrow[arr-begin="'+markedInd+'"]').each(function(){
                        $(this).attr('restrict','true');
                    });
                });
            },
            unrestrictArrows : function(){
                $('svg.arrows path.arrow[restrict]').each(function(){
                    $(this).removeAttr('restrict');
                });
            },
            isChild : function (key) {
                var listOfMarkedElements = $('.bpm_operator[mark="marked"]'),
                    s = 0;

                $('.bpm_operator[data-unique_index]').removeAttr('mark');
                BpmOperator.markOperators(key);
                $.each($('.element[mark]'), function (key, data) {
                    var $data = $(data);

                    if ($data.is('[end-branches]')) s++;
                    if ($data.is('.and_helper')) s--;
                })
                BpmOperator.unmarkOperators();

                listOfMarkedElements.attr('mark','marked');

                return s<0 ? true : false;
            },
            collectAllElements : function(index) {
                var andArray = [];
                //var nextOp = $('svg.arrows path.arrow[arr-begin="'+index+'"]')
                $.each($('svg.arrows path.arrow[arr-begin="'+index+'"]'), function(){
                    var nextOp = $(this).attr('arr-end');
                    while (!$('.bpm_operator[data-unique_index="'+nextOp+'"]').hasClass('and_helper')){//$('.bpm_operator[data-unique_index="'+nextOp+'"]').attr('data-unique_index') != $('.bpm_operator[data-unique_index="'+$(this).attr('arr-begin')+'"]').attr('end-branches')) {
                        if($('.bpm_operator[data-unique_index="'+nextOp+'"]').data('name') == "and"){
                            var recursiveArray = ProcessObj.BPM.collectAllElements(nextOp);
                            andArray = andArray.concat(recursiveArray);
                        } else {
                            andArray.push(nextOp);
                            nextOp = $('svg.arrows path.arrow[arr-begin="'+nextOp+'"]').attr('arr-end');
                        }
                    }
                });
                return andArray;
            },
            getAllBranchIndexes : function(unique_index, brunchNumber) {
                indArr = [];
                endBranches = $('.bpm_operator[data-unique_index="'+unique_index+'"]').attr('end-branches');
                if (brunchNumber=='all') {
                    $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').each(function(){
                        nextOp = $(this).attr('arr-end');
                        pointsCounter = 1;
                        //while (nextOp && endBranches && nextOp!=endBranches) {
                        while (!$('.bpm_operator[data-unique_index="'+nextOp+'"]').hasClass('and_helper')){//$('.bpm_operator[data-unique_index="'+nextOp+'"]').attr('data-unique_index') != $('.bpm_operator[data-unique_index="'+$(this).attr('arr-begin')+'"]').attr('end-branches')) {
                            if($('.bpm_operator[data-unique_index="'+nextOp+'"]').data('name') == "and"){
                                indArr.push(nextOp)
                                var andArray = ProcessObj.BPM.collectAllElements(nextOp);
                                indArr = indArr.concat(andArray);
                                nextOp = $('svg.arrows path.arrow[arr-begin="'+nextOp+'"]').attr('arr-end');
                            } else {
                                indArr.push(nextOp);
                                /*if ($('.bpm_operator[data-unique_index="'+nextOp+'"]').data('name')=='and') {
                                addArr = ProcessObj.BPM.getAllBranchIndexes(nextOp, 'all');
                                indArr.push(addArr);
                                nextOp = $('.bpm_operator[data-unique_index="'+nextOp+'"]').attr('end-branches');
                            } else {*/
                                nextOp = $('svg.arrows path.arrow[arr-begin="'+nextOp+'"]').attr('arr-end');
                                //}
                                if ($('svg.arrows path.arrow[arr-end="'+nextOp+'"]').length>1) {
                                    pointsCounter--;
                                    if (pointsCounter==0) {
                                        $('svg.arrows path.arrow[arr-end="'+unique_index+'"]').attr('arr-end',nextOp+'');
                                    }
                                } else if ($('svg.arrows path.arrow[arr-begin="'+nextOp+'"]').length>1) {
                                    pointsCounter++;
                                }
                            }
                        }
                    });
                } else {
                }
                var uniqueNames = [];
                $.each(indArr, function(i, el){
                    if($.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
                });
                return uniqueNames;
            },

            whatOperatorsBetweenCol: function (row, colBegin, colEnd) {
                var bpmOperator = $('.bpm_operator[data-unique_index]');
                var operators = [];

                while (colBegin < colEnd){
                    var item = bpmOperator.filter('[gridrow='+row+'][gridcol='+colBegin+']');
                    if (item.length)
                    {
                        operators.push(item);
                        colBegin = colEnd;
                    }
                    colBegin++;
                }
                return operators;
            },


            //analize and set barnches ends
            setBranchEnds : function() {
                $.each($('.element[data-type="responsible"] .bpm_operator[data-unique_index][end-branches]'), function(){
                    var emptyFirstBranch,
                        currentElement = $(this),
                        unique_index = currentElement.attr('end-branches'),
                        pathes = $('svg.arrows path.arrow'),
                        bpmOperator = $('.bpm_operator'),
                        gridRow = parseInt(currentElement.attr('gridrow')),
                        helperOperator = ProcessObj.getParams(bpmOperator.filter('[data-unique_index='+unique_index+']')),
                        arrowEnd = pathes.filter('[arr-end="'+unique_index+'"]');

                    if (arrowEnd.not('[branch][branch-end="main"]').attr('branch-end','true').length) {
                        arrowEnd.filter('[arr-begin != "'+currentElement.data('unique_index')+'"]').each(function () {
                            var _this = $(this);
                            var bpmItem = bpmOperator.filter('[data-unique_index='+_this.attr('arr-begin')+']'),
                                bpmUnit = bpmItem.closest('.bpm_unit');

                            if (gridRow == parseInt(bpmItem.attr('gridrow')) && parseInt(bpmUnit.data('ug_id')) == helperOperator.ug_id && bpmUnit.data('ug_type') == helperOperator.ug_type) {
                                _this.attr('branch-end', 'main');
                            }
                        });

                        emptyFirstBranch = arrowEnd.filter('[branch=1][arr-begin='+currentElement.data('unique_index')+']');
                        if (emptyFirstBranch.length)
                        {
                            var colBegin = parseInt(currentElement.attr('gridcol'))+1;
                            var colEnd = parseInt(bpmOperator.filter('.and_helper').filter('[data-unique_index='+currentElement.attr('end-branches')+']').attr('gridcol'));
                            var listOperators = ProcessObj.BPM.whatOperatorsBetweenCol(gridRow, colBegin, colEnd);

                            if (!listOperators.length) {
                                emptyFirstBranch.attr('branch-end', 'main');
                            }
                        }
                    }
                });
            },
            deleteQeue : function(indexes, callback) {
                $.each(indexes, function(i,val) {
                    process.BPM.operationParams.delete(val, function(unique_index){
                        $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').remove();
                        $('.element[data-type="operation"][data-unique_index="'+unique_index+'"]').remove();
                    });
                });
                callback(true);
            },
            deleteCallback : function(unique_index) {
                BpmOperator.getByKey(unique_index).remove();
                var newEnd = Arrows.getByBegin(unique_index).attr('arr-end');

                Arrows.getByEnd(unique_index).attr('arr-end',newEnd+'');

                toMove = true;
                $('svg.arrows path.arrow[arr-end="'+newEnd+'"][branch-end]').each(function(){
                    branEndCol = $('.bpm_operator[data-unique_index="'+$(this).attr('arr-begin')+'"]').attr('gridcol');
                    newEndCol = $('.bpm_operator[data-unique_index="'+newEnd+'"]').attr('gridcol');
                    if (parseInt(branEndCol)+2>=newEndCol) {
                        toMove = false;
                    }
                });
                Arrows.getByBegin(unique_index).remove();

                if (toMove) {
                    BpmOperator.recountNextOperators(newEnd, 'left');
                }
                BpmModel.removedOfOperator = true;
                ProcessObj.recountRespBlocks();
                ProcessObj.inspection.init()
                Arrows.recountAll();

                _self.saveSchema();
                BpmModel.removedOfOperator = null;

                ProcessObj.getModel().update();
            },
            deleteQeueCallback : function(deletedInd, indAfterHelper) {
                $('svg.arrows path.arrow[arr-end="'+deletedInd+'"]').attr('arr-end',indAfterHelper+'');
            },
            createHelperAnd : function(genInd) {
                var nextCol = parseInt($('.bpm_operator[data-unique_index="'+genInd+'"]').attr('gridcol'))+1;
                var newGenInd = ProcessObj.BPM.createDataUnique();
                /*var $andClone = */$('.bpm_operator[data-unique_index="'+genInd+'"]').clone(true)
                    .attr('gridcol',nextCol+'').attr('data-unique_index', newGenInd+'').addClass('and_helper').data('unique_index', newGenInd+'')
                    .insertAfter($('.bpm_operator[data-unique_index="'+genInd+'"]'));
                var branchEnd = $('.bpm_operator[data-unique_index="'+genInd+'"]').attr('end-branches');
                var $realPath = $('svg.arrows path.arrow[arr-begin="'+genInd+'"]');
                var $clonePath = $realPath.clone(true);
                $clonePath.removeAttr('title').attr('arr-end',$realPath.attr('arr-end')+'').attr('arr-begin',newGenInd+'').insertAfter($realPath);
                $realPath.attr('arr-end',newGenInd+'');
            },

            separateArrow : function($path, interIndex) {  //разделение и переопредиление стрелок при присоединении нового елемента
                if ($path.attr('arr-begin') !== interIndex && $path.attr('arr-end') !== interIndex) { // проверка против зацикливания
                    var $clonew = $path.clone(true).attr('arr-end', interIndex+'').insertAfter($path),
                        bpmOperator = $('div.bpm_operator');

                    var colorArr = $('div.bpm_operator.condrag .bpm_body').css('background-color'),
                        arrows = $('svg.arrows');

                    if (colorArr=='rgb(255, 255, 255)') {
                        colorArr = 'rgb(197, 197, 197)';
                    }
                    $path.attr('arr-begin', interIndex+'').attr('stroke', colorArr+'');
                    $('div.bpm_operator.condrag').removeClass('condrag');
                    $path.removeAttr('branch').removeAttr('modifier');
                    $clonew.removeAttr('modifier');

                    if (bpmOperator.filter('[data-name=condition][data-unique_index="'+$clonew.attr('arr-begin')+'"]').length>0)
                    {
                        $path.removeAttr('title');
                    } else $clonew.removeAttr('title');

                    if ($clonew.attr('branch-end')) {
                        if ($clonew.attr('new-end')) {
                            $path.removeAttr('branch-end').removeAttr('new-end');
                            arrEnd = $path.attr('arr-end');
                            $path.attr('restricted','true');
                            arrows.find('path.arrow[arr-end="'+arrEnd+'"]').not('path[restricted]').each(function(){
                                $(this).attr('arr-end',interIndex+'');
                            });
                            $('path[restricted]').removeAttr('restricted');
                            $clonew.removeAttr('new-end');
                        } else {
                            $clonew.removeAttr('branch-end');
                        }
                    }

                    Arrows.recount($clonew);
                    Arrows.recount($path);
                }

            },

            createOuterArrow : function(unique_index) {  // crating outer arrow in top outer unit
                var operator = $('.bpm_operator[data-unique_index="'+unique_index+'"]'),
                    color = operator.find('.bpm_body').css('background-color'),
                    showOuterUnit = !$('.outer_unit').is('visible'),
                    path = $('svg.hidden path.arrow').clone();
                if (operator.length>0) {
                    if (showOuterUnit) {
                        $('.outer_unit').removeClass('hide');
                        Arrows.recountAll();
                    }
                    if (operator.data('status')=='active') {
                        color = 'rgb(31, 181, 173)';
                    }
                    var operatoroTopPoint = operator.offset().top-$('svg.arrows').offset().top,
                        operatoroLeftPoint = (operator.offset().left+operator.width()/2) - $('svg.arrows').offset().left,
                        unitBottomPoint = $('.outer_unit').offset().top+$('.outer_unit').height()+30-$('svg.arrows').offset().top+2;

                    path.attr('stroke',color).attr('stroke-dasharray','5,5').attr('arr-begin',unique_index).attr('outer','')
                        .attr('d','M '+operatoroLeftPoint+' '+operatoroTopPoint+' L '+operatoroLeftPoint+' '+unitBottomPoint+'');//.attr('d','M 313 114 L 457 114');
                    $('svg.arrows').prepend(path);
                    path = path.clone();
                    path.attr('stroke',color)
                        .attr('d','M '+operatoroLeftPoint+' '+(unitBottomPoint+15)+' L '+operatoroLeftPoint+' '+unitBottomPoint+' L '+(operatoroLeftPoint-2.5)+' '+(unitBottomPoint+15)+' L '+(operatoroLeftPoint+2.5)+' '+(unitBottomPoint+15)+' L '+operatoroLeftPoint+' '+unitBottomPoint+'').removeAttr('stroke-dasharray');
                    $('svg.arrows').prepend(path);
                }
            },

            destroyOuterArrow : function(unique_index) {
                var operator = $('.bpm_operator[data-unique_index="'+unique_index+'"]'),
                    showOuterUnit = !$('.outer_unit').is('visible'),
                    path = $('svg.hidden path.arrow').clone();
            },


            /**
             * open
             */
            open: function (process_id, process_mode, $this){
                var vars = {
                    'selector_content_box' : '#content_container',
                    'module' : {
                        'copy_id' : ProcessObj.copy_id,
                        'process_id' :  process_id,
                        'process_mode' : process_mode
                    }
                }

                modalDialog.hideAll();

                if ($this && $this.length) {
                    instanceGlobal.preloaderShow($this);
                }

                instanceGlobal.contentReload
                    .clear()
                    .setVars(vars)
                    .loadBpmProcess();
            },


            switchProcessStatus : function(_this, bpm_params_run){
                var process = new Process();
                if(ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN) {
                    process.BPM.runAction(_this, function(){
                        if(bpm_params_run && ProcessObj.process_status == ProcessObj.PROCESS_B_STATUS_IN_WORK) {
                            ProcessObj.BPM.bpmParamsRun(ProcessObj.PROCESS_BPM_PARAMS_ACTION_CHECK);
                        }
                        HeaderNotice.refreshAllHeaderNotices();
                    });
                } else if(ProcessObj.mode == ProcessObj.PROCESS_MODE_CONSTRUCTOR){
                    process.BPM.addCardSelect();
                }
                $(_this).closest('.crm-dropdown.element.open').removeClass('open');
                $('.element[data-type="actions"] .element[data-type="mc_'+ProcessObj.mode_change+'"]').parent().addClass('active');
                ProcessObj.editOrViewProcess();
            },


            /**
             * runAction
             */
            runAction : function(_this, callback){
                var action_type = $(_this).data('type');
                var active_action = $('.bpm_block .element[data-type="actions"] ul li.active a.element').data('type');

                if(active_action == action_type) {
                    $( 'div.bpm_operator' ).draggable({ disabled: false });
                    return;
                }

                var data = {
                    'process_id' : ProcessObj.process_id,
                };

                switch(action_type){
                    case ProcessObj.PROCESS_ACTION_START :
                        data['b_status'] = ProcessObj.PROCESS_B_STATUS_IN_WORK;
                        break;
                    case ProcessObj.PROCESS_ACTION_STOP :
                        data['b_status'] = ProcessObj.PROCESS_B_STATUS_STOPED;
                        break;
                    case ProcessObj.PROCESS_ACTION_TERMINATE :
                        data['b_status'] = ProcessObj.PROCESS_B_STATUS_TERMINATED;
                        break;
                }

                var ajax = new Ajax();
                ajax
                    .setData(data)
                    .setAsync(false)
                    .setUrl('/module/BPM/setProcessStatus/' + ProcessObj.copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status){
                                ProcessObj.BPM.updateProcessStatus(data.b_status);
                                ProcessObj.refreshStatus(data.schema, 'all');
                                if(typeof(callback) == 'function') callback(data);
                            } else {
                                Message.show(data.messages, false);
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .setCallBackDone(function(){
                        $( 'div.bpm_operator' ).draggable({ disabled: false });
                    })
                    .send();


            },
            modeChangeSwitch : function(_this){
                var mc_active = $(_this).data('type'),
                    element = $(_this).closest('.element[data-type="bpm_menu"]');


                switch(mc_active){
                    case _self.PROCESS_ACTION_MC_EDIT:
                        ProcessObj.mode_change = ProcessObj.PROCESS_MODE_CHANGE_EDIT;
                        element.find('li.active .element[data-type="mc_view"]').closest('li.active').removeClass('active');
                        element.find('li .element[data-type="mc_edit"]').closest('li').addClass('active');
                        $('.bpm_block[data-page_name="BPMView"] .element[data-type="operation_menu"]').removeClass('hidden');
                        break;
                    case _self.PROCESS_ACTION_MC_VIEW:
                        ProcessObj.mode_change = ProcessObj.PROCESS_MODE_CHANGE_VIEW;
                        element.find('li.active .element[data-type="mc_edit"]').closest('li.active').removeClass('active');
                        element.find('li .element[data-type="mc_view"]').closest('li').addClass('active');
                        $('.bpm_block[data-page_name="BPMView"] .element[data-type="operation_menu"]').addClass('hidden');
                        break;
                }
            },
            /**
             * setProcessStatus
             */
            setProcessStatus : function(b_status){
                if(b_status) {
                    ProcessObj.process_status = b_status;

                    var action_type;

                    if(b_status == ProcessObj.PROCESS_B_STATUS_IN_WORK) {
                        action_type = 'start';
                    } else if(b_status == ProcessObj.PROCESS_B_STATUS_STOPED) {
                        action_type = 'stop';
                    } else if(b_status == ProcessObj.PROCESS_B_STATUS_TERMINATED) {
                        action_type = 'terminate';
                    }

                    $('.bpm_block .element[data-type="actions"] ul li').each(function(i, li){
                        $(li).removeClass('active');
                        if(action_type){
                            if($(li).find('a.element').data('type') == action_type){
                                $(li).addClass('active');
                            }
                        }
                    });
                }

            },


            addCardSelect : function(){
                var data = {};
                data['parent_copy_id'] = null,
                    data['parent_data_id'] = null,
                    data['this_template'] = 0,
                    data['parent_class'] = 'list-view',
                    data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
                data['process_id'] = ProcessObj.process_id;

                var ajax = new Ajax();
                ajax
                    .setData(data)
                    .setAsync(false)
                    .setUrl('/module/editView/editSelect/' + ProcessObj.copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                if(data.status == true){
                                    modalDialog.show(data.data, true);

                                    jScrollInit();
                                    niceScrollCreate($('.submodule-table'));
                                    imagePreview();
                                    $('.form-control.time').each(function(){
                                        initElements('.edit-view', $(this).val());
                                    });
                                }
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                    })
                    .setCallBackDone(function(){
                        $( 'div.bpm_operator' ).draggable({ disabled: false });
                    })
                    .send();
            },


            /**
             * operationParams
             */
            operationParams : {
                settings : {},

                setSettings : function(unique_index, settings){
                    this.settings[unique_index] = settings;
                },

                getSettings: function(unique_index){
                    return this.settings[unique_index];
                },


                /**
                 * getOperationChevronData
                 */
                getOperationChevronData : function(_this){
                    var data = {
                        'process_id' : ProcessObj.process_id,
                        'mode' : ProcessObj.mode,
                        'mode_change' : ProcessObj.mode_change,
                        'unique_index' : $(_this).data('unique_index'),
                        'element_name' : $(_this).data('name'),

                        'pci' : null,
                        'pdi' : null,
                    }

                    return data;
                },

                /**
                 * show
                 */
                show : function(data, callback){
                    AjaxObj
                        .createInstance()
                        .setData(data)
                        .setAsync(false)
                        .setUrl('/module/BPM/showOperationParams/' + ProcessObj.copy_id)
                        .setCallBackSuccess(function(data){
                            if(data.status == 'access_error'){
                                Message.show(data.messages, false);
                            } else {
                                if(data.status) {
                                    modalDialog
                                        .createInstance()
                                        .show(data.html, true);

                                } else if(data.messages) {
                                    Message.show(data.messages, false);
                                }
                            }
                            if(typeof(callback) == 'function'){
                                callback(data);
                            }
                        })
                        .setCallBackError(function(jqXHR, textStatus, errorThrown){
                            Message.showErrorAjax(jqXHR, textStatus);
                        })
                        .setCallBackDone(function(){
                            ProcessObj.initsOperatorModalShow(data);
                            Preloader.modalHide();
                        })
                        .send()
                },




                /**
                 * delete
                 * удаляем схему оператора
                 */
                delete : function(unique_index, callback){
                    var data = {
                        'process_id' : ProcessObj.process_id,
                        'unique_index' : unique_index,
                        'mode' : ProcessObj.mode,
                        'mode_change' : ProcessObj.mode_change,
                    }

                    AjaxObj
                        .createInstance()
                        .setData(data)
                        .setAsync(false)
                        .setUrl('/module/BPM/deleteSchemaOperation/' + ProcessObj.copy_id)
                        .setCallBackSuccess(function(data){
                            if(data.status == 'access_error'){
                                Message.show(data.messages, false);
                            } else {
                                if(data.status){
                                    callback(unique_index)
                                } else if(data.messages){
                                    Message.show(data.messages, false);
                                }
                            }
                        })
                        .setCallBackError(function(jqXHR, textStatus, errorThrown){
                            Message.showErrorAjax(jqXHR, textStatus);
                        })
                        .send()
                },


                /**
                 * getSaveData
                 */
                getSaveData : function(_this, element_name, unique_index){

                    switch(element_name){
                        case 'data_record':
                            var data = {
                                'process_id' : ProcessObj.process_id,
                                'unique_index' : unique_index,
                                'element_name' : element_name,
                                'mode' : ProcessObj.mode,
                                'schema_operation' : this.getSchemaOperation(element_name, _this)
                            }

                            break;
                        default:
                            var data = {
                                'process_id' : ProcessObj.process_id,
                                'unique_index' : unique_index,
                                'element_name' : element_name,
                                'mode' : ProcessObj.mode,
                                'schema_operation' : this.getSchemaOperation(element_name, _this)
                            }
                            break;
                    }


                    return data;
                },

                /**
                 * save
                 * сохраняет схему оператора на сервере
                 */
                save : function(_this, element_name, unique_index, callback){
                    var data  = this.getSaveData(_this, element_name, unique_index);

                    // send to server
                    AjaxObj
                        .createInstance()
                        .setData(data)
                        .setAsync(false)
                        .setDataType('json')
                        .setUrl('/module/BPM/saveSchemaOperation/' + ProcessObj.copy_id)
                        .setCallBackSuccess(function(data){
                            if(data.status == 'access_error'){
                                Message.show(data.messages, false);
                            } else {
                                if(data.status){
                                    if(callback && typeof callback == 'function'){
                                        callback(data);
                                    }
                                } else {
                                    if(data.html){
                                        if(element_name == 'notification' || element_name == 'scenario'){
                                            $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(data.html);
                                        } else if(element_name == 'begin' || element_name == 'timer'){
                                            $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block .dinamic:not(add_list)').remove();
                                            $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').children('li').after(data.html);
                                            ProcessObj.initDatePicker();
                                            ProcessObj.initTimePicker();
                                        }

                                        if (element_name == 'scenario') {
                                            _this.find('textarea#code').val(bpmOperatorScript.getValue());
                                            ProcessObj.scenario.init();
                                            ProcessObj.scenario.render();
                                        }


                                        Global.initSelects();
                                        ProcessObj.activateDropdowns();
                                        ProcessObj.getCountOptions($('.select[multiple]'));
                                        Global.groupDropDowns(0).init($(_this).find('.add_list .element'));
                                    } else if(data.messages){
                                        Message.show(data.messages, false);
                                    }
                                }
                            }
                            instanceGlobal.contentReload.preloaderHide();
                        })
                        .setCallBackError(function(jqXHR, textStatus, errorThrown){
                            Message.showErrorAjax(jqXHR, textStatus);
                        })
                        .send()
                },

                done : function(_this, unique_index, callback){
                    var data = {
                        'process_id' : ProcessObj.process_id,
                        'unique_index' : unique_index,
                    }

                    // send to server
                    var ajax = new Ajax();
                    ajax
                        .setData(data)
                        .setAsync(false)
                        .setDataType('json')
                        .setUrl('/module/BPM/doneOperation/' + ProcessObj.copy_id)
                        .setCallBackSuccess(function(data){
                            if(data.status == 'access_error'){
                                Message.show(data.messages, false);
                            } else {
                                if(data.status){
                                    if(callback && typeof callback == 'function'){
                                        callback(data);
                                    }
                                } else {
                                    Message.show(data.messages, false);
                                }
                            }
                        })
                        .setCallBackError(function(jqXHR, textStatus, errorThrown){
                            Message.showErrorAjax(jqXHR, textStatus);
                        })
                        .send()

                },

                getSchemaOperation : function(element_name, _this){
                    var result = null;
                    switch(element_name){
                        case 'begin' :
                            result = this.getSchemaOperationBegin(_this);
                            break;
                        case 'end':
                            result = this.getSchemaOperationEnd(_this);
                            break;
                        case 'condition':
                            result = this.getSchemaOperationCondition(_this);
                            break;
                        case 'and':
                            result = this.getSchemaOperationAnd(_this);
                            break;
                        case 'timer':
                            result = this.getSchemaOperationTimer(_this);
                            break;
                        case 'task':
                            result = this.getSchemaOperationTask(_this);
                            break;
                        case 'agreetment':
                            result = this.getSchemaOperationAgreetment(_this);
                            break;
                        case 'notification':
                            result = this.getSchemaOperationNotification(_this);
                            break;
                        case 'data_record':
                            result = this.getSchemaOperationDataRecord(_this);
                            break;
                        case 'scenario':
                            result = this.getSchemaOperationScenario(_this);
                            break;
                    }

                    return result;
                },

                getSchemaOperationRunOnTimeElements : function(_this){
                    var schema_element = [];
                    var start_on_time = _this.find('.element[data-type="start_on_time"]').val();

                    switch(start_on_time){
                        case 'start_on_time_disabled':
                            break;

                        case 'start_on_time_disposable_start':
                        case 'start_on_time_determined':
                        case 'start_on_before_time':
                        case 'start_on_after_time':
                            var selections = '';
                            var value = '';
                            element_list = ProcessObj.BPM.elementsActions.runOnTime.elements[start_on_time];
                            for(i = 0; i < element_list.length; i++){
                                selections += 'li.dinamic .element[data-type="' + element_list[i] + '"]';
                                if((i + 1) < element_list.length) selections += ',';
                            }

                            $(selections).each(function(i, ul){
                                var li_dinamic = $(ul).closest('li.dinamic');
                                var pretitle = li_dinamic.find('.element[data-type="title"]').clone(true);
                                $(pretitle).find('.counter').remove();

                                if(start_on_time == 'start_on_time_disposable_start'){
                                    value = [$(ul).val(), li_dinamic.find('.element[data-type="sub_time"]').val()];
                                } else if($.inArray(start_on_time, ['start_on_time_determined', 'start_on_before_time', 'start_on_after_time']) >= 0){
                                    value = $(ul).val();
                                }
                                schema_element.push({
                                    "type": $(ul).data('type'),
                                    "title": pretitle.text(),
                                    "value": value
                                });
                            })
                            break;

                        case 'start_on_time_regular_start':
                            var li_dinamic = _this.find('.element[data-type="periodicity"]').closest('li.dinamic');
                            var pretitle = li_dinamic.find('.element[data-type="title"]').clone(true);
                            $(pretitle).find('.counter').remove();
                            schema_element.push({
                                "type": 'periodicity',
                                "title": pretitle.text(),
                                "value": _this.find('.element[data-type="periodicity"]').val()
                            });

                            var selections = '';
                            var element_list = [];

                            $.each(ProcessObj.BPM.elementsActions.runOnTime.elements.start_on_time_regular_start, function(key, value){
                                element_list.push(value);
                            });

                            for(i = 0; i < element_list.length; i++){
                                selections += 'li.dinamic .element[data-type="' + element_list[i] + '"]';
                                if((i + 1) < element_list.length) selections += ',';
                            }

                            $(selections).each(function(i, ul){
                                var value = '';
                                var li_dinamic = $(ul).closest('li.dinamic');
                                var pretitle = li_dinamic.find('.element[data-type="title"]').clone(true);
                                $(pretitle).find('.counter').remove();

                                if($(ul).data('type') != 'time'){
                                    value = [$(ul).val(), li_dinamic.find('.element[data-type="sub_time"]').val()];
                                } else {
                                    value = $(ul).val();
                                }

                                schema_element.push({
                                    "type": $(ul).data('type'),
                                    "title": pretitle.text(),
                                    "value": value
                                });
                            })

                            schema_element.push({
                                "type": 'label_add_date',
                                "title": '',
                                "value": ''
                            });
                            break;


                        //start_on_after_created_entity
                        //start_on_after_changed_entity
                        case 'start_on_after_created_entity' :
                            schema_element.push({
                                "type": 'object_name',
                                "value": _this.find('.element[data-type="object_name"]').val()
                            });
                            break;
                        case 'start_on_after_changed_entity' :
                            schema_element.push({
                                "type": 'object_name',
                                "value": _this.find('.element[data-type="object_name"]').val()
                            });
                            schema_element.push({
                                "type": 'field_name',
                                "value": _this.find('.element[data-type="field_name"]').val()
                            });

                            _this.find('li.form-group').each(function(){
                                var type = $(this).find('.element[data-type="value_scalar"],' +
                                    '.element[data-type="value_datetime"],' +
                                    '.element[data-type="value_select"],' +
                                    '.element[data-type="drop_down_button"]').data('type');
                                if(typeof(type) == 'undefined') return true;

                                switch(type){
                                    case 'value_scalar':
                                        schema_element.push({
                                            "type": type,
                                            "value": null,
                                            "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                            "value_value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                        });
                                        break;
                                    case 'value_select':
                                        schema_element.push({
                                            "type": type,
                                            "value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                        });
                                        break;
                                    case 'drop_down_button':
                                        schema_element.push({
                                            "type": type,
                                            "value": [$(this).find('.element_filter[data-name="condition_value"]').data('id')]
                                        });
                                        break;
                                    case 'value_datetime':
                                        var dates = [];
                                        $(this).find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                                            var v = $(ul).val();
                                            if(v){
                                                dates.push(v);
                                            }
                                        });
                                        schema_element.push({
                                            "type": type,
                                            "value": null,
                                            "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                            "value_value": (!$.isEmptyObject(dates) ? dates : '')
                                        });
                                        break;
                                }
                            });

                            schema_element.push({
                                "type": 'label_add_value',
                                "title": '',
                                "value": ''
                            });


                            break;
                    }

                    return schema_element;
                },

                getSchemaOperationBegin : function(_this){
                    var previous_process = _this.find('.element[data-type="previous_process"]').val(),
                        start_on_time = _this.find('.element[data-type="start_on_time"]').val();

                    var schema = [{
                        "type": "previous_process",
                        "value": (previous_process ? previous_process : ""),
                    }, {
                        "type": "start_on_time",
                        "value": start_on_time,
                        "elements": this.getSchemaOperationRunOnTimeElements(_this)
                    }];


                    /*
                //start_on_after_created_entity
                //start_on_after_changed_entity
                switch(start_on_time){
                    case 'start_on_after_created_entity' :
                        schema.push({
                            "type": 'object_name',
                            "value": _this.find('.element[data-type="object_name"]').val()
                        });
                        break;
                    case 'start_on_after_changed_entity' :
                        schema.push({
                            "type": 'object_name',
                            "value": _this.find('.element[data-type="object_name"]').val()
                        });
                        schema.push({
                            "type": 'field_name',
                            "value": _this.find('.element[data-type="field_name"]').val()
                        });

                        _this.find('li.form-group').each(function(){
                            var type = $(this).find('.element[data-type="value_scalar"],' +
                                '.element[data-type="value_datetime"],' +
                                '.element[data-type="value_select"],' +
                                '.element[data-type="drop_down_button"]').data('type');
                            if(typeof(type) == 'undefined') return true;

                            switch(type){
                                case 'value_scalar':
                                    schema.push({
                                        "type": type,
                                        "value": null,
                                        "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                        "value_value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                    });
                                    break;
                                case 'value_select':
                                    schema.push({
                                        "type": type,
                                        "value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                    });
                                    break;
                                case 'drop_down_button':
                                    schema.push({
                                        "type": type,
                                        "value": [$(this).find('.element_filter[data-name="condition_value"]').data('id')]
                                    });
                                    break;
                                case 'value_datetime':
                                    var dates = [];
                                    $(this).find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                                        var v = $(ul).val();
                                        if(v){
                                            dates.push(v);
                                        }
                                    });
                                    schema.push({
                                        "type": type,
                                        "value": null,
                                        "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                        "value_value": (!$.isEmptyObject(dates) ? dates : '')
                                    });
                                    break;
                            }
                        });

                        schema.push({
                            "type": 'label_add_value',
                            "title": '',
                            "value": ''
                        });


                        break;
                }
                */

                    return schema;
                },

                getSchemaOperationTimer : function(_this){
                    var schema = [{
                        "type": "start_on_time",
                        "value": _this.find('.element[data-type="start_on_time"]').val(),
                        "elements": this.getSchemaOperationRunOnTimeElements(_this)
                    }];

                    return schema;
                },

                getSchemaOperationEnd : function(_this){
                    schema = [
                        {
                            "type": "next_process",
                            "value": _this.find('.element[data-type="next_process"]').val()
                        }
                    ];
                    return schema;
                },
                getSchemaOperationCondition : function(_this){
                    var schema = [
                        {
                            "type": "object_name",
                            "value": _this.find('.element[data-type="object_name"]').val()
                        },
                        {
                            "type": "relate_module",
                            "value": _this.find('.element[data-type="relate_module"]').val()
                        },
                        {
                            "type": "field_name",
                            "value": _this.find('.element[data-type="field_name"]').val()
                        }
                    ];
                    _this.find('li.form-group').each(function(){
                        var type = $(this).find('.element[data-type="value_scalar"],' +
                            '.element[data-type="value_datetime"],' +
                            '.element[data-type="value_select"],' +
                            '.element[data-type="drop_down_button"]').data('type');
                        if(typeof(type) == 'undefined') return true;

                        switch(type){
                            case 'value_scalar':
                                schema.push({
                                    "type": type,
                                    "value": null,
                                    "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                    "value_value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                });
                                break;
                            case 'value_select':
                                schema.push({
                                    "type": type,
                                    "value": [$(this).find('.element_filter[data-name="condition_value"]').val()]
                                });
                                break;
                            case 'drop_down_button':
                                schema.push({
                                    "type": type,
                                    "value": [$(this).find('.element_filter[data-name="condition_value"]').data('id')]
                                });
                                break;
                            case 'value_datetime':
                                var dates = [];
                                $(this).find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                                    var v = $(ul).val();
                                    if(v){
                                        dates.push(v);
                                    }
                                });
                                schema.push({
                                    "type": type,
                                    "value": null,
                                    "value_condition": $(this).find('.element[data-type="value_condition"]').val(),
                                    "value_value": (!$.isEmptyObject(dates) ? dates : '')
                                });
                                break;
                        }

                    });

                    return schema;
                },
                getSchemaOperationAnd : function(_this){
                    var schema = [
                        {
                            "type": "number_branches",
                            "value": _this.find('.element[data-type="number_branches"]').val(),
                        }
                    ];
                    return schema;
                },

                getSchemaOperationTask : function(_this){
                    var execution_time_values = {
                        'days' : _this.find('.edit-view .element[data-type="execution_time"][name="days"]').val(),
                    };

                    var schema = [{
                        'type' : 'copy_id',
                        'value' : _this.find('.edit-view').data('copy_id'),
                    }, {
                        'type' : 'card_id',
                        'value' : _this.find('.edit-view').data('id'),
                    }, {
                        'type' : 'sdm_operation_task',
                        'value' : _this.find('.edit-view .element[data-type="sdm_operation_task"]').val(),
                    }, {
                        'type' : 'execution_time',
                        'value' : execution_time_values,
                    }
                    ];

                    return schema;
                },
                getSchemaOperationAgreetment : function(_this){
                    var execution_time_values = {
                        'days' : _this.find('.edit-view .element[data-type="execution_time"][name="days"]').val(),
                    };

                    var schema = [{
                        'type' : 'copy_id',
                        'value' : _this.find('.edit-view').data('copy_id'),
                    }, {
                        'type' : 'card_id',
                        'value' : _this.find('.edit-view').data('id'),
                    }, {
                        'type' : 'sdm_operation_task',
                        'value' : _this.find('.edit-view .element[data-type="sdm_operation_task"]').val(),
                    }, {
                        'type' : 'type_agreetment',
                        'value' : _this.find('.edit-view .element[data-type="type_agreetment"]').val(),
                    }, {
                        'type' : 'email',
                        'value' : (_this.find('.edit-view .element[data-type="type_agreetment"]').val() == 'external' ?_this.find('.edit-view .element[data-type="email"]').val() : ''),
                    }, {
                        'type' : 'execution_time',
                        'value' : execution_time_values,
                    }
                    ];

                    return schema;
                },
                getSchemaOperationNotification : function(_this){
                    var schema = [{
                        'type' : 'type_message',
                        'value' : _this.find('.element[data-type="type_message"]').val()
                    }, {
                        'type' : 'service_name',
                        'value' : _this.find('.element[data-type="service_name"]').val()
                    }, {
                        'type' : 'service_vars',
                        'value' : ProcessObj.BPM.operationParams.getSchemaOperationNotificationElements(_this)
                    }
                    ];

                    return schema;
                },
                getSchemaOperationDataRecord : function(_this){
                    var schema = [];
                    $(_this).find('.element').each(function(i, ul){
                        var type = $(ul).data('type');
                        var value = null;
                        var add_value = false;
                        switch(type){
                            case 'type_operation':
                                value = $(ul).val();
                                add_value = true;
                                break;
                            case 'module_name':
                                value = $(ul).val();
                                add_value = true;
                                break;
                            case 'record_name_list':
                                value = $(ul).val();
                                add_value = true;
                                /*
                            schema.push({
                                'type' : type,
                                'title' : $(ul).find('.element_relate').text(),
                                'value' : {'relate_copy_id' : $(ul).find('.element_relate').data('relate_copy_id'), 'relate_data_id' : $(ul).find('.element_relate').data('id')},
                            });
                            */
                                break;
                            case 'record_name_text':
                                value = $(ul).val();
                                add_value = true;
                                break;
                            case 'call_edit_view':
                                value = $(ul).val();
                                add_value = true;
                                break;
                            case 'required_fields':
                                var required_fields = $(ul).val();
                                if(required_fields){
                                    required_fields = required_fields.toString();
                                } else {
                                    required_fields = null;
                                }
                                value = required_fields;
                                add_value = true;
                                break;
                            case 'message':
                                value = $(ul).val();
                                add_value = true;
                                break;
                            case 'value_block':
                                $(ul).find('.element[data-type="value_value"]').each(function(i, ul) {
                                    var column = $(ul).closest('.column_half');
                                    var field_type = $(ul).data('field_type');
                                    switch(field_type) {
                                        case 'display':
                                        case 'relate_string':
                                        case 'string':
                                        case 'numeric':
                                            var value_value = column.find('.element[data-type="value_value"] .column>.form-control').val();
                                            break;
                                        case 'select':
                                        case 'logical':
                                            var value_value = column.find('select.select').val();
                                            break;
                                        case 'relate':
                                        case 'relate_this':
                                            var value_value = {'relate_copy_id' : $(ul).find('.element_relate').data('relate_copy_id'), 'relate_data_id' : $(ul).find('.element_relate').data('id')}
                                            break;
                                        case 'relate_participant':
                                            var value_value = {'ug_id' : $(ul).find('.element_relate_participant').data('ug_id'), 'ug_type' : $(ul).find('.element_relate_participant').data('ug_type')}
                                            break;
                                        case 'datetime':
                                            var date = column.find('.element[data-type="value_value"] .column>.form-datetime .date').val();
                                            var time = column.find('.element[data-type="value_value"] .column>.form-datetime .time').val();
                                            var value_value = date+' '+time;
                                            break;
                                    }
                                    if (!value_value) {
                                        value_value = null;
                                    }
                                    schema.push({
                                        'type' : 'value_block',
                                        'value' : value_value,
                                        'field_name' : column.closest('.column').find('.element[data-type="value_field_name"]').val(),
                                        'counter' : column.closest('.column').closest('.inputs-group').find('.counter').text(),
                                    });
                                });
                                break;
                            case 'label_add_value':
                                schema.push({
                                    'type': type
                                });
                                break;

                        }

                        if(add_value) {
                            schema.push({
                                'type': type,
                                'value': value
                            });
                        }
                    });


                    return schema;
                },

                getSchemaOperationScenario : function(_this){
                    var schema = [
                        {
                            "type": "script_text",
                            "value": _this.find('.element[data-type="script_text"]').val(),
                        },
                        {
                            "type": "script_type",
                            "value": _this.find('.element[data-type="script_type"]').val(),
                        }
                    ];
                    return schema;
                },

            },

            updateProcessStatus : function(process_status){
                ProcessObj.process_status = process_status;
                this.setProcessStatus(process_status);
            },




            getDataForChangeParams : function(_this, _element_name){

                if(_element_name == 'begin'){
                    var data = {
                        'process_id': ProcessObj.process_id,
                        'unique_index': $(_this).closest('.element[data-type="params"][data-module="process"]').data('unique_index'),
                        'element_name': _element_name,
                        'action': 'changed_' + $(_this).data('type'),
                        'params': {
                            'schema_operation': ProcessObj.BPM.operationParams.getSchemaOperation(_element_name, $(_this).closest('.element[data-name="'+_element_name+'"]'))
                        }
                    }
                } else if(_element_name == 'condition'){
                    var data = {
                        'process_id': ProcessObj.process_id,
                        'unique_index': $(_this).closest('.element[data-type="params"][data-module="process"]').data('unique_index'),
                        'element_name': _element_name,
                        'action': 'changed_' + $(_this).data('type'),
                        'params': {
                            'schema_operation': ProcessObj.BPM.operationParams.getSchemaOperation(_element_name, $(_this).closest('.element[data-name="'+_element_name+'"]'))
                        }
                    }
                } else if(_element_name == 'data_record'){
                    var data = {
                        'process_id': ProcessObj.process_id,
                        'unique_index': $(_this).closest('.element[data-type="params"][data-module="process"]').data('unique_index'),
                        'element_name': _element_name,
                        'action': 'changed_' + $(_this).data('type'),
                        'params': {
                            'schema_operation': ProcessObj.BPM.operationParams.getSchemaOperation(_element_name, $(_this).closest('.element[data-name="'+_element_name+'"]')),
                            'field_name' : $(_this).val(),
                        }
                    }
                } else if(_element_name == 'notification'){
                    var data = {
                        'process_id': ProcessObj.process_id,
                        'unique_index': $(_this).closest('.element[data-type="params"][data-module="process"]').data('unique_index'),
                        'element_name': _element_name,
                        'action': 'changed_' + $(_this).data('type'),
                        'params': {
                            'schema_operation': ProcessObj.BPM.operationParams.getSchemaOperation(_element_name, $(_this).closest('.element[data-name="'+_element_name+'"]')),
                        }
                    }
                }  else if(_element_name == 'timer'){
                    var data = {
                        'process_id': ProcessObj.process_id,
                        'unique_index': $(_this).closest('.element[data-type="params"][data-module="process"]').data('unique_index'),
                        'element_name': _element_name,
                        'action': 'changed_' + $(_this).data('type'),
                        'params': {
                            'schema_operation': ProcessObj.BPM.operationParams.getSchemaOperation(_element_name, $(_this).closest('.element[data-name="'+_element_name+'"]')),
                        }
                    }
                }

                return data;
            },

            changeParamsContent : function(_this, _element_name, callback){
                var data = this.getDataForChangeParams(_this, _element_name);

                AjaxObj
                    .createInstance()
                    .setData(data)
                    .setAsync(false)
                    .setUrl('/module/BPM/changeParamsContent/' + ProcessObj.copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status){
                                callback(data)
                            } else {
                                Message.show(data.messages, false);
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();
            },




            autoShowTask : function(unique_index){
                if(typeof unique_index == 'undefined'){
                    var url = location.href;
                    if(url.match(/unique_index=/)){
                        var unique_index = url.match(/unique_index=(\w*)/)[1];
                        $('.element[data-unique_index="' + unique_index + '"] .bpm_body').trigger('click');

                        Preloader.afterPreloader();
                    }
                } else {
                    $('.element[data-unique_index="' + unique_index + '"] .bpm_body').trigger('click');
                }
            },



            /**
             * updateRelateModule
             */
            updateRelateModule : function(_this, callback){
                var data = {
                    'process_id' : ProcessObj.process_id,
                    'copy_id' : $(_this).data('id')
                }

                var ajax = new Ajax();
                ajax
                    .setData(data)
                    .setAsync(false)
                    .setUrl('/module/BPM/updateRelateModule/' + ProcessObj.copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(typeof(callback) == 'function') callback(data.status);
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();
            },


            /**
             * getBpmParamsData
             */
            getBpmParamsData : function(action, process_id, _this){
                var bpm_block = $('.bpm_block');
                var responsible = bpm_block.find('.element[data-type="responsible"]'); //[data-ug_type="group"]
                if(action == ProcessObj.PROCESS_BPM_PARAMS_ACTION_CHECK && typeof(bpm_block) != 'undefined' && bpm_block.length != 0 && responsible.length == 0){
                    return null;
                }

                var objects = {};

                if(ProcessObj.binding_object_check){
                    objects.binding_object = null;
                }
                objects.participants = null;
                if(process_id === null){
                    process_id = ProcessObj.process_id;
                }

                var data = {
                    'action' : action,
                    'process_id' : process_id,
                    'objects' : objects
                }

                if(_this !== null){
                    var binding_object = $(_this).closest('.sm_extension').find('.element[data-type^="relate_object_block"] .element[data-type="drop_down_button"]');
                    var participant_object = $(_this).closest('.sm_extension').find('.element[data-type^="participant_block"] .element[data-type="drop_down_button"]');

                    if(action == ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE && (typeof(binding_object) == 'undefined' || binding_object.length == false) && (typeof(participant_object) == 'undefined' || participant_object.length == false)){
                        return;
                    }
                }

                switch(action){
                    case ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE :
                        // binding_oblect
                        if(typeof(binding_object) != 'undefined' && binding_object){
                            data.objects.binding_object = {'attributes': null};
                            data.objects.binding_object.attributes = {
                                'copy_id': binding_object.data('relate_copy_id'),
                                'data_id': binding_object.data('id'),
                            }
                        }

                        //participants
                        if(typeof(participant_object) != 'undefined' && participant_object){
                            data.objects.participants = [];
                            participant_object.each(function(i, ul){
                                data.objects.participants.push(
                                    {
                                        'ug_id' : $(ul).closest('.element[data-type^="participant_block"]').data('ug_id'),
                                        'ug_type' : $(ul).closest('.element[data-type^="participant_block"]').data('ug_type'),
                                        'attributes' : {
                                            'ug_id': $(ul).data('ug_id'),
                                            'ug_type': $(ul).data('ug_type'),
                                        }
                                    }
                                );
                            })
                        }
                        break;
                }

                return data;
            },

            /**
             * bpmParamsRun
             */
            bpmParamsRun : function(action, _this){
                var _data = ProcessObj.BPM.getBpmParamsData(action, ProcessObj.process_id, _this);

                if(_data === null){
                    return;
                }

                AjaxObj
                    .createInstance()
                    .setData(_data)
                    .setAsync(false)
                    .setUrl('/module/BPM/bpmParamsRun/' + ProcessObj.copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == false && data.message){
                                modalDialog.hide();
                                modalDialog.show(data.message, true);
                            } else
                            if(data.status == true) {

                                ProcessObj.BPM.updateProcessStatus(data.process_status);
                                ProcessObj.refreshStatus(data.schema, 'all');
                                HeaderNotice.refreshAllHeaderNotices();

                                if(action == ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE && data.group_data['participants'] !== null){
                                    ProcessObj.BPM.participants.runActionChange($(_this).closest('.sm_extension'), false);
                                }

                                modalDialog.hide();

                                if(_data['action'] == ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE && data.params_repeat && data.params_repeat.status == false){
                                    modalDialog.show(data.params_repeat.message, true);
                                }

                                if(ProcessObj.binding_object_check){
                                    ProcessObj.binding_object_check = false;
                                    // run process
                                    var bpm_params_run = false;
                                    if(ProcessObj.process_status != ProcessObj.PROCESS_B_STATUS_IN_WORK &&
                                        _data['action'] == ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE &&
                                        data.params_repeat && data.params_repeat.status == true)
                                    {
                                        bpm_params_run = true;
                                    }
                                    ProcessObj.BPM.switchProcessStatus($('.element[data-type="actions"] .element[data-type="start"]'), bpm_params_run);

                                }
                            }

                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();
            },



            /**
             * elementsActions - actions on elements.
             */
            elementsActions : {

                /**
                 * for operations: begin, timer
                 */
                runOnTime : {
                    elements : {
                        'start_on_time_disabled' : null,
                        'start_on_time_disposable_start' : ['date'],
                        'start_on_time_regular_start' : {
                            'periodicity_year' : 'date',
                            'periodicity_quarter' : 'quarter',
                            'periodicity_month' : 'day_in_month',
                            'periodicity_week' : 'week',
                            'periodicity_day' : 'time',
                        },
                        'start_on_time_determined' : ['days', 'hour', 'minutes'],
                        'start_on_before_time' : ['object_name', 'relate_module', 'field_name', 'days', 'hour', 'minutes'],
                        'start_on_after_time' : ['object_name', 'relate_module', 'field_name', 'days', 'hour', 'minutes'],
                        'start_on_after_created_entity' : ['object_name'],
                        'start_on_after_changed_entity' : ['object_name', 'field_name', 'value_scalar', 'label_add_value']
                    },

                    startOnTimeChanged : function(_this){
                        var element_type = $(_this).val();
                        var inputs_block = $(_this).closest('.inputs-block');
                        var unique_index = $(_this).closest('.element[data-module="process"]').data('unique_index');

                        $(_this).closest('.inputs-block').find('li.dinamic').remove();

                        switch(element_type) {
                            case 'start_on_time_disposable_start':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.date));
                                ProcessObj.initDatePicker();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                break;

                            case 'start_on_time_regular_start':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.periodicity));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.date));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                Global.initSelects();
                                ProcessObj.initDatePicker();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                break;

                            case 'start_on_time_determined':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.days));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.hour));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.minutes));
                                break;

                            case 'start_on_before_time':
                            case 'start_on_after_time':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.object_name));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.relate_module));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.field_name));
                                Global.initSelects();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.days));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.hour));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.minutes));
                                break;

                            case 'start_on_after_created_entity':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.object_name));
                                Global.initSelects();
                                break;

                            case 'start_on_after_changed_entity':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.object_name));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.field_name));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.value_scalar));
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_value));
                                Global.initSelects();
                                $(_this).closest('.element[data-type="params"]').find('.element[data-type="object_name"]').trigger('change');
                                break;
                        }
                        return;
                    },


                    periodicityChanged : function(_this){
                        var element_type = $(_this).val();
                        var inputs_block = $(_this).closest('.inputs-block');
                        var unique_index = $(_this).closest('.element[data-module="process"]').data('unique_index');

                        // remove old
                        element_list = [];
                        $.each(ProcessObj.BPM.elementsActions.runOnTime.elements.start_on_time_regular_start, function(key, value){
                            element_list.push(value);
                        })

                        if(element_list.length) {
                            for (var i = 0; i < element_list.length; i++) {
                                inputs_block.find('.element[data-type="' + element_list[i] + '"]').closest('li.dinamic').remove();
                            }
                            inputs_block.find('.element[data-type="label_add_date"]').closest('li.dinamic').remove();
                        }

                        // add new
                        switch(element_type) {
                            case 'periodicity_year':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.date));
                                ProcessObj.initDatePicker();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                break;
                            case 'periodicity_quarter':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.quarter));
                                Global.initSelects();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                break;
                            case 'periodicity_month':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.day_in_month));
                                Global.initSelects();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                break;
                            case 'periodicity_week':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.week));
                                Global.initSelects();
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                break;
                            case 'periodicity_day':
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.time));
                                ProcessObj.initTimePicker();
                                inputs_block.find('.todo-remove').hide();
                                inputs_block.append($(ProcessObj.BPM.operationParams.settings[unique_index].elements.label_add_date));
                                break;
                        }
                        return;
                    },

                    labelAddDate : function(typeElement, _thisLi, unique_index){
                        switch(typeElement) {
                            case 'periodicity_year':
                                _thisLi.before($(ProcessObj.BPM.operationParams.settings[unique_index].elements.date));
                                ProcessObj.initDatePicker();
                                break;
                            case 'periodicity_quarter':
                                _thisLi.before($(ProcessObj.BPM.operationParams.settings[unique_index].elements.quarter));
                                Global.initSelects();
                                break;
                            case 'periodicity_month':
                                _thisLi.before($(ProcessObj.BPM.operationParams.settings[unique_index].elements.day_in_month));
                                Global.initSelects();
                                break;
                            case 'periodicity_week':
                                _thisLi.before($(ProcessObj.BPM.operationParams.settings[unique_index].elements.week));
                                Global.initSelects();
                                break;
                            case 'periodicity_day':
                                _thisLi.before($(ProcessObj.BPM.operationParams.settings[unique_index].elements.time));
                                break;
                        }
                        ProcessObj.initTimePicker();
                        return;
                    },

                },
            },




            /**
             * participants
             */
            participants : {
                getDataForShow : function(_this, action){
                    var data = {
                        'process_id' : ProcessObj.process_id,
                        'action' : action,
                    }
                    switch(action){
                        case 'add' :
                            data['ug_id'] = null;
                            data['ug_type'] = null;
                            break;

                        case 'change' :
                            data['ug_id'] = $(_this).closest('.element[data-type="responsible"]').data('ug_id');
                            data['ug_type'] = $(_this).closest('.element[data-type="responsible"]').data('ug_type');
                            data['unique_index'] = $(_this).closest('.element[data-type="responsible"]').data('unique_index');
                            break;
                    }

                    return data;
                },

                show : function(_this, action){
                    var data = this.getDataForShow(_this, action);

                    var ajax = new Ajax();
                    ajax
                        .setData(data)
                        .setAsync(false)
                        .setUrl('/module/BPM/showParticipant/' + ProcessObj.copy_id)
                        .setCallBackSuccess(function(data){
                            if(data.status == 'access_error'){
                                Message.show(data.messages, false);
                            } else {
                                if(data.status){
                                    modalDialog.show(data.html, true);
                                } else {
                                    Message.show(data.messages, false);
                                }
                            }
                        })
                        .setCallBackError(function(jqXHR, textStatus, errorThrown){
                            Message.showErrorAjax(jqXHR, textStatus);
                        })
                        .send();
                },

                runAction : function(_this, action){
                    switch(action){
                        case 'add' :
                            this.runActionAdd(_this)
                            break;
                        case 'change' :
                            this.runActionChange(_this, true)
                            break;
                    }
                },

                runActionAdd : function(_this){
                    var respBlock = ProcessObj.BPM.elements.responsible;
                    var relate_participant = $(_this).find('.element[data-type="drop_down_button"]');
                    if (relate_participant.val()) {
                        $(respBlock).insertAfter('.bpm_block .bpm_unit:last');
                        $('.element[data-type="responsible"]:last').find('.bpm_uname_title').text(relate_participant.val()+'')

                        $('.element[data-type="responsible"]:last')
                            .attr('data-ug_id', relate_participant.data('ug_id'))
                            .attr('data-ug_type', relate_participant.data('ug_type'))
                            .attr('data-unique_index', $(_this).closest('.sm_extension').data('unique_index'));

                        ProcessObj.dragInit();
                        ProcessObj.dropInit();
                        modalDialog.hide();

                        _self.saveSchema();

                        ProcessObj.activateDropdowns();
                    } else {
                        if (!relate_participant.next().is('.errorMessage')) {
                            relate_participant.after('<div class="errorMessage">'+Message.translate_local('You must select the participant')+'</div>');
                        }
                    }
                },


                runActionChange : function(_this, save_schema){
                    $(_this).find('.element[data-type^="participant_block"] .element[data-type="drop_down_button"]').each(function(i1, ul1){
                        var ug_id = $(ul1).closest('.element[data-type^="participant_block"]').data('ug_id');
                        var ug_type = $(ul1).closest('.element[data-type^="participant_block"]').data('ug_type');

                        $('.bpm_block .element[data-type="responsible"]').each(function(i, ul){
                            if($(ul).data('unique_index') == $(_this).data('unique_index')){
                                $(ul).find('.bpm_uname_title').text($(ul1).text());
                                $(ul).data('ug_id', $(ul1).data('ug_id'));
                                $(ul).data('ug_type', $(ul1).data('ug_type'));
                                return true;
                            }
                        });
                    })

                    modalDialog.hide();
                    if(save_schema)
                        _self.saveSchema();
                },

            },


            prepateFilterDateTime : function(_this){
                // show single calendar
                if ($(_this).find('.dateinput').length) {
                    var $dateinput = $(_this).find('.dateinput')
                    Filter.singleCalendar($dateinput);
                    $dateinput.datepicker('setDate', $dateinput.attr('value'));
                } else
                // show range calendar
                if ($(_this).find('.dp1').length) {
                    var $dp1 = $(_this).find('.dp1'),
                        $dp2 = $(_this).find('.dp2');
                    Filter.rangeCalendar($dp1, $dp2);
                    date1 = 0;
                    date2 = 0;
                }
            },





        },

        /* BPM end */


        /**
         * createFromTemplate
         */
        createFromTemplate : function(_this, process_id, callback){
            var time,
                ajax = new Ajax(),
                data = {
                    'process' :  {
                        'module_title' : $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').val(),
                        'process_id' : process_id,
                        'b_status' : ProcessObj.PROCESS_B_STATUS_IN_WORK,
                        'parent_copy_id' : $(_this).closest('.sm_extension').data('parent_copy_id'),
                        'parent_data_id' : $(_this).closest('.sm_extension').data('parent_data_id'),
                    },
                    'bpm_params' : ProcessObj.BPM.getBpmParamsData(ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE, process_id, _this)
                }

            //Base.btnSaveSetDisabled($(_this), true);

            ajax
                .setData(data)
                .setAsync(false)
                .setUrl('/module/listView/createFromTemplate/' + ProcessObj.copy_id)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    //Base.btnSaveSetDisabled($(_this), false);
                    if(data.status == 'access_error' || data.status == 'error'){
                        Message.show(data.messages, false);
                    } else if(data.status == 'error_validate'){
                        $(_this).closest('.sm_extension').find('.element[data-type="objects"] .errorMessage').each(function(i,ul){
                            $(ul).html('');
                        })
                        $.each(data.messages, function(data_type, message){
                            $(_this).closest('.sm_extension').find('.element[data-type="objects"] .element[data-type="'+data_type+'"] .errorMessage').html(message);
                        });
                    } else if(data.status){
                        callback(data);
                    }
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                });

            time = setTimeout(function () {
                clearTimeout(time);
                ajax.send();
            })
        },



        activateDropdowns : function(){
            $('body').off('click').on('click', function (e) {
                if (!$('.crm-dropdown').is(e.target)
                    && $('.crm-dropdown').has(e.target).length === 0
                    && $('.open').has(e.target).length === 0
                ) {
                    var $editView = $('.modal-dialog:visible');
                    if ($editView.find('.client-name .edit-dropdown.open').length) {
                        EditView.setTitle($editView);
                    }

                    $('.crm-dropdown').removeClass('open');
                }
                $('.crm-dropdown').filter('.opened').removeClass('opened').addClass('open');
            });

            $(document).on('click','.inputs-block [data-type="service_vars"] .crm-dropdown .select.open .dropdown-menu.inner.selectpicker li.selected', function () {
                $(this).closest('.select.open').removeClass('open');
            });

            $('.crm-dropdown > .dropdown-toggle').removeAttr('data-toggle').on('click',function(){
                $('.crm-dropdown.open').removeClass('open');
                $(this).parent().toggleClass('open');
            });

            return this;
        },
        getDirectionBpmUnit: function(currentBpmUnit, fromBpmUnit){ // get direction where located first element by second element
            var direction = null;
            if (currentBpmUnit.offset().top > fromBpmUnit.offset().top) {
                direction = false; // currentBpmUnit in bottom
            } else {
                if (currentBpmUnit.offset().top < fromBpmUnit.offset().top){
                    direction = true; // currentBpmUnit in top
                }
            }
            return direction;
        },
        getPositionOnHelper: function (operator, rowHelper) {
            var currentBpmUnit = operator.closest('.bpm_unit'),
                gridrow = parseInt(operator.attr('gridrow')),
                space = 0,
                fromBpmUnit = $('.bpm_unit.responsible'),
                bpmUnits = $('.bpm_unit.element');
            var _currentBpmUnit = currentBpmUnit,
                locationBpmOperator = ProcessObj.getDirectionBpmUnit(currentBpmUnit, fromBpmUnit);

            while (_currentBpmUnit.length>0) {
                var baseRows = parseInt(_currentBpmUnit.attr('rows'));
                if (!_currentBpmUnit.find(operator).length) {
                    gridrow = 0; // empty responsible
                }
                if (_currentBpmUnit.data('ug_id') == fromBpmUnit.data('ug_id')) {
                    _currentBpmUnit = [];
                    gridrow = parseInt(operator.attr('gridrow'));
                    if (locationBpmOperator == null) {
                        space += Math.abs(rowHelper - gridrow);
                    } else {
                        space += (locationBpmOperator) ? rowHelper : baseRows - gridrow;
                    }
                } else {
                    space += (baseRows - gridrow);
                }

                var bpmUnit = bpmUnits.filter(_currentBpmUnit); //                   search down        search up
                _currentBpmUnit = (locationBpmOperator && _currentBpmUnit.length) ? bpmUnit.next() : bpmUnit.prev();
            }
            return space;
        },
        heightMarkInStruct: function (beginIndex, endIndex) {
            BpmOperator.markOperators(beginIndex, endIndex);
            var bpmOperator = $('.bpm_operator[data-unique_index]');
            var beginHelper = bpmOperator.filter('[data-unique_index="'+beginIndex+'"]');
            var endHelper = bpmOperator.filter('[data-unique_index="'+endIndex+'"]');
            var gridrow,
                maxElement = beginHelper,
                fromBpmUnit = endHelper.closest('.bpm_unit'),
                fromIndexUnit = parseInt(endHelper.attr('gridrow')),
                bpmUnits = $('.bpm_unit.element'),
                pathes = $('svg.arrows path.arrow'),
                branches = 0,
                branchesInUser = 0,
                minObject = 0,
                maxObject = 0,
                minOperatorInUser = 0,
                maxOperatorInUser = 0;

            bpmOperator.filter('[mark][data-unique_index="'+endIndex+'"]').removeAttr('mark');
            bpmOperator.filter('.condrag').removeAttr('mark');

            bpmOperator.not('.and_helper').filter('[mark]').each(function () {
                var _this = $(this),
                    space = 0;
                var currentBpmUnit = _this.closest('.bpm_unit');
                var _currentBpmUnit = currentBpmUnit;
                gridrow = parseInt(_this.attr('gridrow'));
                var _row = gridrow;

                while (_currentBpmUnit.length>0) {
                    var direction = null;

                    if (_currentBpmUnit.offset().top > fromBpmUnit.offset().top) {
                        direction = true; // up
                    } else {
                        if (_currentBpmUnit.offset().top < fromBpmUnit.offset().top){
                            direction = false; // bottom
                        }
                    }

                    if (!_currentBpmUnit.find(_this).length) {
                        gridrow = 0; // empty responsible
                        if (currentBpmUnit.offset().top > fromBpmUnit.offset().top) {
                            space += parseInt(fromBpmUnit.attr('rows')) - fromIndexUnit;
                        } else {
                            if (currentBpmUnit.offset().top < fromBpmUnit.offset().top) {
                                space += fromIndexUnit;
                            }
                        }
                        if (direction == null) {
                            _currentBpmUnit = [];
                        }
                    } else {
                        if (direction == null) {
                            space += Math.abs(fromIndexUnit - _row);
                            _currentBpmUnit = [];
                        } else {
                            space += (direction) ? gridrow : parseInt(_currentBpmUnit.attr('rows')) - gridrow;
                        }
                    }

                    if ((currentBpmUnit.offset().top < fromBpmUnit.offset().top) && _currentBpmUnit.length) {
                        _currentBpmUnit =  bpmUnits.filter(_currentBpmUnit).next();
                    } else {
                        if ((currentBpmUnit.offset().top > fromBpmUnit.offset().top) && _currentBpmUnit.length) {
                            _currentBpmUnit = bpmUnits.filter(_currentBpmUnit).prev();
                        }
                    }
                }
                gridrow = parseInt(_this.attr('gridrow'));
                if (currentBpmUnit.offset().top < fromBpmUnit.offset().top && minObject < space) {
                    minObject = space;
                }
                else
                if (currentBpmUnit.offset().top > fromBpmUnit.offset().top && maxObject < space) {
                    maxObject = space;
                }
                else {
                    //It is current usergroup
                    if (minObject < space && gridrow < fromIndexUnit) {
                        minObject = space;
                    }
                    if (maxObject < space && gridrow > fromIndexUnit) {
                        maxObject = space;
                        maxElement = _this;
                    }
                    if (minOperatorInUser < space && gridrow < fromIndexUnit) {
                        minOperatorInUser = space;
                    }
                    if (maxOperatorInUser < space && gridrow > fromIndexUnit) {
                        maxOperatorInUser = space;
                    }

                }

                //search empty branch
                var list = _this.filter('[end-branches]'); //.not('[data-unique_index="'+beginIndex+'"]');
                var beginItem = list.filter('[data-name="and"]').length ? list.filter('[data-name="and"]') : list.filter('[data-name="condition"]');
                if (beginItem.length)
                {
                    branches += pathes.filter('[arr-begin="'+beginItem.data('unique_index')+'"][branch-end]').not('[branch-end="main"]').length;
                }
            });

            BpmOperator.unmarkOperators();

            if (maxElement) { //search empty branches in user
                var i,
                    maxRow = parseInt(maxElement.attr('gridrow')),
                    minCol = parseInt(bpmOperator.filter('[data-unique_index="'+beginIndex+'"]').attr('gridcol')),
                    toCol = parseInt( bpmOperator.filter('[data-unique_index="'+endIndex+'"]').attr('gridcol'));
                var realMax = {
                    row: maxRow,
                    countEmptyBranches: 0
                };
                bpmOperator = maxElement.closest('.bpm_unit').find(bpmOperator);

                for ( i=minCol+1; i<toCol; i++) {
                    var j = maxRow;
                    while (j) {
                        var item = bpmOperator.filter('[gridcol="'+i+'"][gridrow="'+j+'"]');
                        if (item.length) {
                            if (item.is('[end-branches]')){
                                var rowMaxElement = 0,
                                    maxBpmOperator = [],
                                    index = item.data('unique_index'),
                                    rowBase = parseInt(item.attr('gridrow'));
                                var countEmptyBranches = pathes.filter('[arr-begin="'+index+'"][branch-end]').not('[branch-end="main"]').length,
                                    countBranches = pathes.filter('[arr-begin="'+index+'"][branch]').length;

                                //i = parseInt($('.and_helper[data-unique_index="'+item.attr('end-branches')+'"]').attr('gridcol')); // continue from next row

                                if (countEmptyBranches != countBranches){ // for optimize; if exist more 1 branch;
                                    BpmOperator.markOperators(index, item.attr('end-branches'));
                                    var list = bpmOperator.filter('[mark]').not('[data-unique_index="' + index + '"]').not('.and_helper');

                                    rowMaxElement = parseInt(list.first().attr('gridrow'));
                                    maxBpmOperator = list.first();

                                    list.each(function(){
                                        var _this = $(this);
                                        var row = parseInt(_this.attr('gridrow'));
                                        if (rowMaxElement < row) {
                                            rowMaxElement = row;
                                            maxBpmOperator = $(this);
                                        }
                                    });
                                    BpmOperator.unmarkOperators();
                                }
                                rowMaxElement = (rowMaxElement < rowBase) ? rowBase: rowMaxElement;

                                var diffRow = rowMaxElement ?  rowMaxElement + countEmptyBranches : rowBase + countEmptyBranches,
                                    rowOutsideMaxElement = parseInt(maxElement.attr('gridrow'));

                                if (!realMax.row) {
                                    realMax.row = diffRow;
                                    realMax.countEmptyBranches = countEmptyBranches;
                                } else {
                                    if (realMax.row <= diffRow) {
                                        realMax.row = diffRow;
                                        realMax.countEmptyBranches = (rowMaxElement > rowOutsideMaxElement) ? diffRow - rowMaxElement : diffRow - rowOutsideMaxElement;
                                    }
                                }
                            };
                            j = 1; // exit
                        }
                        j--;
                    }
                }
                branchesInUser = realMax.countEmptyBranches;
            }

            return {
                top: minObject,
                bottom : maxObject,
                emptyBranches: branches,
                currentUser: {
                    top: minOperatorInUser,
                    bottom: maxOperatorInUser,
                    emptyBranches: branchesInUser,
                }
            };
        },
        moveToPlace: function (event, toRow) { // change position inner operators below basic row in 1 responsible
            var unique_index = $(event.target).data('unique_index');
            var endBranches = $(event.target).attr('end-branches');
            var fromIndexRow = parseInt($(event.target).attr('gridrow')),
                fromBpmUnit = $('.bpm_unit.responsible'),
                heightMark = ProcessObj.heightMarkInStruct(unique_index, endBranches),
                bpmOperator = $('.bpm_operator[data-unique_index]'),
                placeTo = $('.tmp_target'),
                bpmUnits = $('.bpm_unit.element');

            BpmOperator.markOperators(unique_index, endBranches);
            bpmOperator.filter('[data-unique_index="'+unique_index+'"]').removeAttr('mark');
            bpmOperator.filter('[data-unique_index="'+endBranches+'"]').removeAttr('mark');

            if (placeTo.data('ug_id') == fromBpmUnit.data('ug_id')) { // not offset operator what exist in other responsible
                $('.bpm_unit').not('.responsible').find(bpmOperator).filter('[mark]').removeAttr('mark');
            }

            if (toRow - heightMark.top >= 1) { //scheme placed
                bpmOperator.filter('[mark]').each(function () {
                    var _this = $(this).removeAttr('mark');
                    var gridrow = parseInt(_this.attr('gridrow'));

                    if (_this.closest('.bpm_unit').data('ug_id') != fromBpmUnit.data('ug_id')) {
                        gridrow = ProcessObj.getPositionOnHelper(_this, fromIndexRow);
                    } else {
                        gridrow -= fromIndexRow;
                    }

                    if (!gridrow) {
                        gridrow = toRow;
                    } else  gridrow = (gridrow>0) ? gridrow + toRow : toRow - Math.abs(gridrow);

                    _this.attr('gridrow', gridrow);
                    // analized responsible
                    if (placeTo.data('ug_id') != fromBpmUnit.data('ug_id') || (placeTo.find(_this).length)) {
                        _this.appendTo(placeTo.find('.bpm_tree'));
                    } else  {
                        _this.appendTo(_this.closest('.bpm_tree'));
                    }

                });
                BpmOperator.unmarkOperators();
            } else { //scheme + offset in bottom
                bpmOperator.filter('[mark]').each(function () {
                    var _this = $(this);
                    var gridrow = parseInt(_this.attr('gridrow')),
                        locationBpmOperator = ProcessObj.getDirectionBpmUnit(_this.closest('.bpm_unit'), fromBpmUnit);

                    var delta = ProcessObj.getPositionOnHelper(_this, fromIndexRow);
                    var attitudeToMin = Math.abs(delta - heightMark.top);

                    if (locationBpmOperator) {
                        gridrow = toRow + attitudeToMin; // row(operator) < row (base helper)
                    } else {
                        if (locationBpmOperator == null) { // ? row(operator) == row (base helper) : row(operator) > row (base helper)
                            if (fromIndexRow >= gridrow) {
                                var attitudeToBase = Math.abs(fromIndexRow - parseInt(_this.attr('gridrow')));
                                var attitudeToMin = Math.abs(attitudeToBase - heightMark.top);
                                gridrow = toRow + attitudeToMin;
                            } else if (fromIndexRow < gridrow) {
                                gridrow = toRow + heightMark.top + (gridrow - fromIndexRow);
                            }
                        } else gridrow = toRow + delta + heightMark.top;
                    }

                    _this.attr('gridrow', gridrow);
                    // analized responsible
                    if (placeTo.data('ug_id') != fromBpmUnit.data('ug_id') || (placeTo.find(_this).length)) {
                        _this.appendTo(placeTo.find('.bpm_tree'));
                    } else  {
                        _this.appendTo(_this.closest('.bpm_tree'));
                    }
                });
            }
        },
        dragInit : function(){
            var insertCandidat;

            $( 'div.bpm_operator' ).draggable({
                handle: '.bpm_body',
                helper: 'clone',
                cancel: '.fake_operator, .and_helper',
                distance: 10,
                start: function( event, ui ) {
                    var helper = $(ui.helper).addClass('condrag')
                    if (helper.is('[end-branches]')) {
                        var beginInd = helper.data('unique_index');
                        var endInd = $('svg path.arrow[arr-begin="'+helper.attr('end-branches')+'"]').attr('arr-end');
                        BpmOperator.markOperators(beginInd, endInd);
                        ProcessObj.BPM.restrictArrows();
                        BpmOperator.unmarkOperators();
                    }
                },
                drag: function( event, ui ) {
                    insertionPermission = false;
                    helperFollow = false;

                    var $clone = $('.condrag'),
                        bpmTree = $('.target .bpm_tree'),
                        arrows = $('svg.arrows'),
                        $target = $('.target'),
                        circle = {
                            coordinate: null,
                            $: CircleController.hide()
                        },
                        listAngleUnResolved = [];

                    insertCandidat = {
                        status: null,
                        $: null,
                        begOperator: null,
                        endOperator: null
                    };

                    if (bpmTree.length>0) {//тут нужен будет рефакторинг
                        var conDTop1 = (Math.round((ui.offset.top-bpmTree.offset().top)/100+1)-1)*100+18+bpmTree.offset().top-arrows.offset().top;
                        var conDLeft1 = (Math.round((ui.offset.left-bpmTree.offset().left)/180+1)-1)*180+90+bpmTree.offset().left-arrows.offset().left;
                        var gridRow = Math.round((ui.offset.top-bpmTree.offset().top)/100+1);
                        var gridCol = Math.round((ui.offset.left-bpmTree.offset().left)/180+1);

                        if (gridRow<1 || gridRow>$('.target').attr('rows')) {
                            conDLeft1 = '-10';
                            conDTop1 = '-10';
                            insertionPermission = false;
                        } else {
                            insertionPermission = true;
                            if (bpmTree.find('.bpm_operator[gridrow="'+gridRow+'"][gridcol="'+gridCol+'"]').length>0 && gridCol!=1) {
                                conDLeft1 = conDLeft1-90;
                            }
                        }
                        arrows.find('path.arrow').not('[restrict]').each(function(){
                            var $this = $(this),
                                arrArr = $this.attr('d'),
                                arrArr = arrArr.split(' ');

                            if ($this.attr('branch') || $this.attr('branch-end')) {
                                if (arrArr.length == 21) {
                                    if (arrArr[5]==conDTop1 && arrArr[4]<conDLeft1 && conDLeft1<arrArr[7]) {
                                        insertCandidat.$ = $this;
                                    }
                                    listAngleUnResolved.push(arrArr[4]+'-'+arrArr[5]);
                                } else if (arrArr.length == 18) {
                                    if ((arrArr[5]==conDTop1 && arrArr[4]<conDLeft1 && conDLeft1<arrArr[7])
                                        || (arrArr[2]==conDTop1 && arrArr[1]<conDLeft1 && conDLeft1<arrArr[4])) {
                                        insertCandidat.$ = $this;
                                    }
                                    listAngleUnResolved.push(arrArr[4]+'-'+arrArr[5]);
                                } else {
                                    if (arrArr.length == 15 && arrArr[2]==conDTop1 && arrArr[1]<conDLeft1 && conDLeft1<arrArr[4]) {
                                        insertCandidat.$ = $this;
                                    }
                                }
                            } else {
                                if (arrArr.length == 18 && (arrArr[5]==conDTop1 && arrArr[4]<conDLeft1 && conDLeft1<arrArr[7])
                                    || arrArr.length == 15 && (arrArr[2]==conDTop1 && arrArr[1]<conDLeft1 && conDLeft1<arrArr[4])) {
                                    insertCandidat.$ = $this;

                                }
                            }
                        });

                        if (insertCandidat.$ && insertCandidat.$.length) {
                            var begOperator = $('.bpm_operator[data-unique_index="'+insertCandidat.$.attr('arr-begin')+'"'),
                                endOperator = $('.bpm_operator[data-unique_index="'+insertCandidat.$.attr('arr-end')+'"');

                            insertCandidat.status = true;
                            insertCandidat.begOperator = {
                                $: begOperator,
                                col: parseInt(begOperator.attr('gridcol')),
                                row: parseInt(begOperator.attr('gridrow'))
                            }
                            insertCandidat.endOperator = {
                                $: endOperator,
                                col: parseInt(endOperator.attr('gridcol')),
                                row: parseInt(endOperator.attr('gridrow'))
                            }
                        }

                        if (!$clone.data('unique_index') && insertCandidat.status) {
                            if (insertCandidat.endOperator.$.data('name') == 'agreetment'
                                || ($clone.data('name') == 'agreetment' && insertCandidat.begOperator.$.data('name') != 'task')
                                || (insertCandidat.$.attr('stroke')!=='rgb(197, 197, 197)' && ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN)) {
                                insertionPermission = false;
                            } else {
                                circle.coordinate = { cx: conDLeft1, cy: conDTop1}
                            }
                        } else if ($clone.data('unique_index')) { //тут нужен будет рефакторинг
                            if (insertCandidat.$) {
                                if ($clone.data('name') == 'and' && insertCandidat.$.attr('restrict')
                                    || ($clone.data('name') == 'begin' || $clone.data('name') == 'end' || $clone.data('name') == 'and' && insertCandidat.$.attr('arr-begin')==$clone.attr('end-branches'))) {
                                    insertionPermission = false;
                                } else if (insertCandidat.$.attr('arr-end') == $clone.data('unique_index') ||
                                    insertCandidat.$.attr('arr-begin') == $clone.data('unique_index')) {
                                    if (insertCandidat.$.attr('arr-begin') == $clone.data('unique_index') &&
                                        insertCandidat.$.attr('branch-end') &&
                                        parseInt($('.bpm_operator[data-unique_index="'+insertCandidat.$.attr('arr-begin')+'"]').attr('gridcol'))==parseInt(gridCol) &&
                                        $clone.data('name')!='agreetment') { //special for end branches
                                        circle.coordinate = { cx: conDLeft1, cy: conDTop1}
                                    } else {
                                        insertionPermission = false;
                                    }
                                } else if (insertCandidat.$.attr('stroke')!=='rgb(197, 197, 197)' && ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN
                                    || ($('.bpm_operator[data-unique_index="'+insertCandidat.$.attr('arr-end')+'"]').data('name') == 'agreetment')) {
                                    insertionPermission = false;
                                } else if ($clone.data('name') == 'agreetment' && insertCandidat.begOperator.$.data('name') != 'task') { // insertCandidat.attr('stroke')!=='rgb(197, 197, 197)' && ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN) {
                                    insertionPermission = false;
                                } else {
                                    circle.coordinate = { cx: conDLeft1, cy: conDTop1}
                                }
                            } else {
                                var nextCol = $('.bpm_operator[data-unique_index="'+arrows.find('path.arrow[arr-begin="'+$clone.data('unique_index')+'"]').attr('arr-end')+'"').attr('gridcol'),
                                    prevCol = $('.bpm_operator[data-unique_index="'+arrows.find('path.arrow[arr-end="'+$clone.data('unique_index')+'"]').attr('arr-begin')+'"').attr('gridcol');

                                if (nextCol>gridCol && gridCol>prevCol) {
                                    circle.coordinate = { cx: conDLeft1, cy: conDTop1}
                                } else if (!prevCol && nextCol>gridCol) {
                                    circle.coordinate = { cx: 115, cy: conDTop1}
                                } else if (!nextCol && gridCol>prevCol) {
                                    if (parseInt($('.bpm_operator[data-unique_index="'+$clone.data('unique_index')+'"').attr('gridcol')) == gridCol) {
                                        circle.coordinate = { cx: conDLeft1, cy: conDTop1}
                                    } else {
                                        insertionPermission = false;
                                    }
                                } else {
                                    insertionPermission = false;
                                }
                            }
                        } else {
                            insertionPermission = false;
                        }
                    }

                    if (circle.coordinate && insertCandidat.$) {
                        CircleController.set(circle.coordinate.cx, circle.coordinate.cy);
                    }

                    var listPointTerm = ProcessObj.listPointByCrossing.get(gridCol, gridRow, $clone.data('unique_index'), $target.data('ug_id'), $target.data('ug_type'));
                    if  (!insertCandidat.$) {
                        if (parseInt($clone.attr('gridcol')) != gridCol || (parseInt(circle.$.attr('cx'))!= -10 && listPointTerm)) {
                            CircleController.hide();
                        }
                        if (insertionPermission) {
                            if ($.inArray(conDLeft1+'-'+conDTop1, listAngleUnResolved) >=0 && circle.$ || listPointTerm) {
                                insertionPermission = false;
                                CircleController.hide();
                            }
                        }
                    }
                },
                stop: function( event, ui ) {
                    var modelOperator,
                        modelProcess = ProcessObj.getModel(),
                        _this = $(this),
                        tmpTarget = $('div.tmp_target'),
                        $condrag = $('.condrag'),
                        arrows = $('svg.arrows');

                    ProcessObj.BPM.reDrawOfArrows = true;
                    $(ui.helper).removeClass('condrag');
                    $(ui.helper).closest('.bpm_unit').addClass('responsible');
                    if (parseInt(arrows.find('circle').attr('cx')) < 0) {
                        $condrag.remove();
                        return;
                    }
                    $condrag.data('old-row',$condrag.attr('gridrow'));
                    if (insertionPermission) {
                        var bpmTree = tmpTarget.find('.bpm_tree');
                        if (_this.closest('.bpm_tree').length>0) { // проверка на новый элемент
                            var oldCol = $(event.target).attr('gridcol');
                            $(event.target).remove();
                        }
                        if (bpmTree.length>0) { // check for target responsible
                            var termChangeBranch,
                                $clone = $('.condrag'),
                                gridRow = Math.round((ui.offset.top - bpmTree.offset().top)/100+1),
                                gridCol = Math.round((ui.offset.left - bpmTree.offset().left)/180+1),
                                gridColCheck = gridCol,
                                opInCellInd = $clone.closest('.bpm_block').find('.bpm_operator[gridrow="'+gridRow+'"][gridcol="'+gridColCheck+'"]').data('unique_index');

                            modelOperator = BpmOperator.createModel($clone);
                            modelOperator.setNewElement(true);

                            if (gridCol!=oldCol) { // if operator not moved = false
                                if (insertCandidat.$.length) { // if the place is arrow and exist
                                    if (insertCandidat.$.attr('branch') || insertCandidat.$.attr('branch-end')) { //special conditions for begin and End of branches
                                        if (insertCandidat.endOperator.col-1==gridCol) { // check if putting place is too close
                                            var opInCellInd = insertCandidat.$.attr('arr-end');
                                        }
                                    }
                                }
                                BpmOperator.recountNextOperators(opInCellInd, 'right');
                            }
                            if (insertCandidat.$ && insertCandidat.$.length) {
                                termChangeBranch = parseInt(arrows.find('path.arrow[arr-end="'+$clone.data('unique_index')+'"]').attr('branch')) != parseInt(insertCandidat.$.attr('branch'));
                            }
                            if ($(event.target).is('[end-branches]')
                                && ($(ui.helper).closest(".bpm_unit").data('ug_id') != $('.tmp_target').data('ug_id')
                                    || (gridCol!=oldCol)
                                    || (termChangeBranch) ))
                            {
                                ProcessObj.moveToPlace(event, gridRow);
                            }
                        }
                        if (!$clone.data('unique_index')) { // if operator new = get new index
                            var genInd = ProcessObj.BPM.createDataUnique();
                            $('.bpm_operator[end-branches="no-index"]').attr('end-branches',genInd+'');
                            var json = {
                                    'data-unique_index': genInd
                                };

                            modelOperator
                                .set(json)
                                .attr(json)
                                .setResponsible(genInd);

                            modelOperator.showTitle();

                            if ($clone.data('name')=='and') {
                                var p = {
                                    'dublikate': 'true'
                                }
                                modelOperator.set(p).attr(p);

                                var candidatEnd = insertCandidat.$.attr('arr-end');  // maby start function (make space)
                                for (c=1; c<100; c++) {
                                    if ($('.bpm_operator[data-unique_index="'+candidatEnd+'"]').data('name')=='end') {
                                        c=100;
                                    } else if ($('.bpm_operator[data-unique_index="'+candidatEnd+'"]').hasClass('and_helper')) { // (!) one level branches
                                        c=100;
                                        var beginInd = $('.bpm_operator[end-branches="'+candidatEnd+'"]').data('unique_index');
                                        if ($('.bpm_operator[data-unique_index="'+beginInd+'"]').attr('gridcol')<gridCol &&
                                            $('.bpm_operator[data-unique_index="'+candidatEnd+'"]').attr('gridcol')>gridCol) { // isert inside branches
                                            var ugID = $clone.closest('.bpm_unit').data('ug_id');
                                            BpmOperator.markOperators(beginInd,candidatEnd);
                                            $('.bpm_operator[data-unique_index="'+beginInd+'"]').removeAttr('mark');
                                            $('.bpm_unit[data-ug_id="'+ugID+'"] .bpm_operator[mark]').each(function(){
                                                var thisGridRow = parseInt($(this).attr('gridrow'));
                                                if (thisGridRow>gridRow) {
                                                    $(this).attr('gridrow', thisGridRow+1);
                                                }
                                            });
                                            BpmOperator.unmarkOperators();
                                        }
                                    } else {
                                        candidatEnd = $('svg.arrows path.arrow[arr-begin="'+candidatEnd+'"]').attr('arr-end');
                                    }
                                } // maby end function (make space)
                            }
                            modelOperator.set(json);
                        } else {
                            if (insertCandidat.$ !=null && insertCandidat.$.attr('arr-begin')!=$clone.data('unique_index')) { // if we put on connected arrow
                                if ($clone.data('name')!='end') {
                                    var unInd = $clone.data('unique_index');
                                    if ($clone.is('[end-branches]')) {
                                        andOldCol = parseInt($clone.attr('gridcol'));
                                        andOldRow = parseInt($clone.attr('gridrow'));
                                        var ing = $clone.attr('end-branches');
                                        var newEnd = $('svg.arrows path.arrow[arr-begin="'+ing+'"]').attr('arr-end');
                                        $('svg.arrows path.arrow[arr-end="'+unInd+'"]').each(function(){
                                            $(this).attr('arr-end',newEnd+'').attr('depricate','');
                                        });
                                        var candidatEnd = insertCandidat.$.attr('arr-end'); // maby start function (make space)
                                        for (c=1; c<100; c++) {
                                            if ($('.bpm_operator[data-unique_index="'+candidatEnd+'"]').data('name')=='end') {
                                                c=100;
                                            } else if ($('.bpm_operator[data-unique_index="'+candidatEnd+'"]').hasClass('and_helper')) { // (!) one level branches
                                                c=100;
                                                var beginInd = $('.bpm_operator[end-branches="'+candidatEnd+'"]').data('unique_index');
                                                if ($('.bpm_operator[data-unique_index="'+beginInd+'"]').attr('gridcol')<gridCol &&
                                                    $('.bpm_operator[data-unique_index="'+candidatEnd+'"]').attr('gridcol')>gridCol) { // isert inside branches
                                                    var ugID = $clone.closest('.bpm_unit').data('ug_id');
                                                    BpmOperator.markOperators(unInd, $('.bpm_operator[data-unique_index="'+unInd+'"]').attr('end-branches'));
                                                    var rowsArray = [];
                                                    $('.bpm_operator[mark]').each(function(){
                                                        //var anGridRow = parseInt($(this).attr('gridrow'));
                                                        rowsArray.push(gridRow);
                                                        if ($('svg.arrows path.arrow[branch][branch-end][modifier][arr-begin="'+$(this).data('unique_index')+'"]').length>0) {
                                                            $('svg.arrows path.arrow[branch][branch-end][modifier][arr-begin="'+$(this).data('unique_index')+'"]').each(function(){
                                                                var modifEmp = parseInt($(this).attr('modifier'))/100+gridRow;
                                                                rowsArray.push(modifEmp);
                                                            });
                                                        }
                                                    });
                                                    var minRow = Math.min.apply(null, rowsArray);
                                                    var maxRow = Math.max.apply(null, rowsArray);
                                                    BpmOperator.unmarkOperators();
                                                    BpmOperator.markOperators(beginInd,candidatEnd);
                                                    var thisGridRowArr=[];
                                                    $('.bpm_unit[data-ug_id="'+ugID+'"] .bpm_operator[mark]').each(function(){
                                                        var thisGridRow = parseInt($(this).attr('gridrow'));
                                                        if (thisGridRow>gridRow) {
                                                            thisGridRowArr.push(thisGridRow);
                                                        } else {
                                                            $(this).removeAttr('mark');
                                                        }
                                                    });
                                                    var thisGridRowMin = Math.min.apply(null, thisGridRowArr);
                                                    if (thisGridRowMin<maxRow||thisGridRowMin==maxRow) {
                                                        var difRow = maxRow-thisGridRowMin+1;
                                                        $('.bpm_unit[data-ug_id="'+ugID+'"] .bpm_operator[mark]').each(function(){
                                                            var thisGridRow = parseInt($(this).attr('gridrow'));
                                                            $(this).attr('gridrow', (thisGridRow+difRow)+'');
                                                        });
                                                    }
                                                    BpmOperator.unmarkOperators();
                                                }
                                            } else {
                                                candidatEnd = $('svg.arrows path.arrow[arr-begin="'+candidatEnd+'"]').attr('arr-end');
                                            }
                                        }  // maby end function (make space)
                                        helperFollow = true;
                                    } else {
                                        var pathes = $('svg.arrows path.arrow');
                                        var arrowBegin = pathes.filter('[arr-begin="'+unInd+'"]');
                                        var newEnd = arrowBegin.attr('arr-end'),
                                            title = arrowBegin.attr('title');
                                        pathes.filter('[arr-end="'+unInd+'"]').attr('arr-end',newEnd+'');
                                        if (title) {
                                            insertCandidat.$.attr('title',title);
                                        }
                                        arrowBegin.remove();
                                    }
                                }
                            }
                        }
                        andOldRow = parseInt($clone.attr('gridrow'));
                        $clone.attr('style','').attr('gridrow',gridRow+'').attr('gridcol',gridCol+'').attr('style','');
                        if (gridRow >= tmpTarget.attr('rows')) {
                            tmpTarget.attr('rows',parseInt(tmpTarget.attr('rows'))+1+'');
                        }

                        $(ui.helper).removeAttr('data-unique_index');
                        var $obj = $clone.find('.bpm_body');
                        var conDTop = (gridRow-1)*100+18+ bpmTree.offset().top- arrows.offset().top;
                        var conDLeft = (gridCol-1)*100+90+ bpmTree.offset().left- arrows.offset().left;

                        if (insertCandidat.$!=null) {
                            var interIndex = $clone.data('unique_index');
                            if ($clone.is('[end-branches]') && !$clone.attr('dublikate')) { // condition is need too
                                var indexes = ProcessObj.BPM.getAllBranchIndexes(interIndex, 'all');
                                var newBranchEnd = insertCandidat.$.attr('arr-end');
                                var newEndBefore = insertCandidat.$.attr('arr-begin');
                                var oldBranchEnd = $clone.attr('end-branches');
                                $('svg.arrows path.arrow[arr-begin="'+oldBranchEnd+'"]').attr('arr-end',newBranchEnd+'').removeAttr('branch-end');
                                insertCandidat.$.attr('arr-end',interIndex+'').removeAttr('branch-end');
                                $('svg.arrows path.arrow[depricate]').removeAttr('depricate');
                            } else {
                                ProcessObj.BPM.separateArrow(insertCandidat.$, interIndex);
                            }
                            if ($clone.attr('dublikate')) {
                                ProcessObj.BPM.createHelperAnd(interIndex);
                                ProcessObj.branchesManage('2', interIndex);
                                $clone.removeAttr('dublikate');

                                var v = {
                                    'end-branches': ProcessObj.defineEndBranches(interIndex)
                                }
                                modelOperator.set(v).attr(v);
                            }
                        } else if ($('.bpm_operator[row-correct]').length>0) {
                            var rowsArr = [];
                            $('.bpm_operator[row-correct]').each(function(){
                                var grdrw = parseInt($(this).attr('gridrow'));
                                rowsArr.push(grdrw);
                            });
                            var highestRow = Math.min.apply(null, rowsArr);
                            $('.bpm_operator[row-correct]').each(function(){
                                $thisOperator = $(this);
                                if (andOldRow<gridRow) {
                                    var newRow = parseInt($thisOperator.attr('gridrow'))+gridRow-andOldRow;
                                    $thisOperator.attr('gridrow',newRow+'');
                                } else if (andOldRow>gridRow) {
                                    var newRow = parseInt($thisOperator.attr('gridrow'))-(highestRow-gridRow);
                                    $thisOperator.attr('gridrow',newRow+'');
                                }
                            });
                        }

                        $('.bpm_operator[row-correct]').removeAttr('row-correct');
                        helperFollow = true;
                        if (gridCol!=oldCol && $clone.data('name')!='and') {
                            BpmOperator.recountNextOperators(newEnd, 'left');
                        }
                        ProcessObj.recountRespBlocks();
                        Arrows.recountAll();
                        ProcessObj.inspection.init(helperFollow, true, false);
                    }
                    //else ProcessObj.inspection.init(false, false);

                    modelOperator.showTitle();

                    ProcessObj.BPM.unrestrictArrows();
                    tmpTarget.removeClass('tmp_target');
                    $( 'div.condrag' ).removeClass( 'condrag' );
                    CircleController.hide();
                    var timerId = setInterval(function() {
                        if (!$( 'div.condrag' ).length>0) {
                            clearInterval(timerId);
                            _self.saveSchema();
                            modelProcess.update();
                        }
                    }, 200);
                    ProcessObj.dragInit();
                    ProcessObj.branchSignatures();
                    $(ui.helper).closest('.bpm_unit').removeClass('responsible');

                    BpmModel.addNewBranch = null;
                }
            });

            return this;
        },

        dropInit : function(){
            $('div.bpm_unit').droppable({
                hoverClass: "target",
                drop: function(event,ui){
                    if (true) { // если добавление подтверждено. доработать контроль
                        $target = $( this )
                        $target.addClass('tmp_target');
                        if (insertionPermission) {
                            var arrow = $('svg.arrows path.arrow');
                            var index = $(ui.helper).data('unique_index');
                            if (arrow.filter('[arr-begin="'+index+'"]').length>0 ||
                                arrow.filter('[arr-end="'+index+'"]').length>0) {
                                $(ui.helper).clone().appendTo($target.find('.bpm_tree'));
                            } else {
                                var operatorName = $(ui.helper).data('name');
                                $newOperator = $(ProcessObj.BPM.elements.operations[operatorName]).addClass('condrag');
                                if (ProcessObj.mode == ProcessObj.PROCESS_MODE_CONSTRUCTOR) {
                                    $newOperator.data('status','done').attr('data-status','done');
                                }
                                $newOperator.appendTo($target.find('.bpm_tree'));
                            }
                        }
                        $('.ui-draggable-dragging').removeClass('ui-draggable-dragging');
                    }
                }
            });

            return this;
        },

        initDatePicker : function(){
            $('.form-control.date').datepicker({
                language: Message.locale.language,
                format: Message.locale.dateFormats.medium_js,
                minDate: '1/1/1970',
                autoclose: true
            }).on('show', function(e){
                if ( e.date ) {
                    $(this).data('stickyDate', e.date);
                }
                else {
                    $(this).data('stickyDate', null);
                }
            }).on('hide', function(e){
                var stickyDate = $(this).data('stickyDate');

                if ( !e.date && stickyDate ) {
                    $(this).datepicker('setDate', stickyDate);
                    $(this).data('stickyDate', null);
                }
            });
        },
        initTimePicker : function(){
            $('.time').each(function(){
                var timeVal = $(this).val();
                /*if (!timeVal) {
                var d = new Date();
                var h = d.getHours();
                var m = d.getMinutes();
                var s = d.getSeconds();
                timeVal = h+':'+m+':'+s;
                timeVal = '00:00:00';
                $(this).val(timeVal);
            }*/
                $(this).timepicker({
                    minuteStep: 1,
                    secondStep: 5,
                    showSeconds: true,
                    showMeridian: false,
                    defaultTime: timeVal,
                });
            });
        },
        titleOperatorRename : function(unique_index, textTitle){
            BpmOperator.titleOperatorRename(unique_index, textTitle);
        },

        initValueAsDefault : function () {
            var $list = $('.modal .element[data-type="value_scalar"], .modal .element[data-type="value_datetime"]');

            $.each($list, function(){
                var inpVal = $(this).closest('.column').find('.element_filter[data-name="condition_value"]').val(),
                    addingEl = $(this).closest('.column').find('.element[data-type="value_condition"]'),
                    addingVal = addingEl.find('option[value="'+addingEl.val()+'"]').text(),
                    mergedVal = addingVal+' '+inpVal;
                $(this).val(mergedVal).attr('disabled','disabled').css('background-color','#fff');
            });
        },
        initsOperatorModalShow : function(data){
            Global.initSelects();
            ProcessObj.initDatePicker();
            ProcessObj.initTimePicker();
            inpBl = $('.bpm_modal_dialog .element[data-unique_index="'+data.unique_index+'"]').find('ul.inputs-block');
            switch (data.element_name) {
                case 'data_record':
                    EditView.activityMessages.init();
                    textAreaResize();
                    imagePreview();
                    $('.select[multiple]').trigger('change');
                    var btnRemove = $(inpBl).find('.todo-remove');
                    (btnRemove.length == 1) ? btnRemove.hide() : btnRemove.show();
                    if ($(inpBl).find('span.counter').length == $(inpBl).find('select[data-type="value_field_name"]:first option').length) {
                        $(inpBl).find('.add_list').hide();
                    }
                    break;
                case 'task':
                    EditView.activityMessages.init();
                    textAreaResize();
                    imagePreview();
                    Global.createLinkByEV($('.edit-view.in:last'));
                    break;
                case 'agreetment':
                    EditView.activityMessages.init();
                    textAreaResize();
                    imagePreview();
                    $('.element[data-type="type_agreetment"]').closest('.panel-body.element[data-type="panel_block"]').css('padding-bottom','0');
                    if ($('.element[data-type="type_agreetment"]').val() == 'external') {
                        $('.element[data-type="type_agreetment"]').closest('li').next().show();
                    }
                    break;
                case 'condition':
                    $('.modal .element[data-name="condition"] .settings-menu .selectpicker li>a').on('click', function() {
                        $(this).closest('.bootstrap-select.open').removeClass('open');
                    });
                    if (inpBl.find('.counter').length == 1) {
                        inpBl.find('.counter').text('');
                        inpBl.find('.counter').closest('li.inputs-group').attr('branch','1');
                        if ($('.bpm_modal_dialog >.element').data('name')=='condition') {
                            inpBl.find('.todo-remove').hide();
                        }
                    } else if ($(inpBl).find('.counter').length == 10 || $(inpBl).find('.counter').length > 10) {
                        inpBl.find('li.add_list').hide();
                        $(inpBl).find('.counter').each(function(index){
                            $(this).text(index+1).closest('li.inputs-group').attr('branch',index+1);
                            $(inpBl).find('.todo-remove').show();
                        });
                    } else {
                        inpBl.find('li.add_list').show();
                        inpBl.find('.counter').each(function(index){
                            $(this).text(index+1).closest('li.inputs-group').attr('branch',index+1);

                        });
                    }

                    this.initValueAsDefault();
                    break;
                case 'begin':
                    if (inpBl.find('.counter').length == 1) {
                        inpBl.find('.counter').text('');
                    } else {
                        inpBl.find('.counter').each(function(index){
                            $(this).text(index+1);
                        });
                    }
                    this.initValueAsDefault();
                    break;
                case 'timer':
                    inpBl.find('.column>.element[data-type="date"], .column>.element[data-type="days"], .column>.element[data-type="hour"], .column>.element[data-type="minutes"]').closest('li.inputs-group').addClass('added');
                    inpBl.find('.element[data-type="remove_panel"]').hide();
                    break;
                case 'notification':
                    EditView.activityMessages.init();
                    textAreaResize();
                    imagePreview();
                    break;
            }
        },
        inspection: {
            callback: null,
            listToCorrection : [],
            functionsOfCorrection : {
                checkOverlay: function (list) {
                    $.each(list, function () {
                        BpmOperator.recountNextOperators(this.key, this.direction);
                    });
                },
                checkBranchEnds: function (list) {
                    $.each(list, function () {
                        for (var i=0; i<this.moveCounter; i++) {
                            BpmOperator.recountNextOperators(this.index, this.direction);
                        }
                    });
                },
                checkAndHelperPlaces: function (list) {
                    $.each(list, function () {
                        $(this.element).attr('gridrow',this.row);

                        if (this.object) {
                            $(this.element).appendTo(this.object);
                        }
                    });
                },
                checkingHorizontalCrossing: function (helperArray) {
                    var cikleInd;

                    $.each(helperArray, function(i){  // helperArray -is array of helper indexes
                        var oneOperator,
                            helperInd = $(this).data('unique_index'), // Branches End Operator Index
                            braBeg = $('.bpm_operator[end-branches="'+helperInd+'"]'), // Branches Begin Operator
                            braBegInd = braBeg.data('unique_index'), // Branches Begin Operator Index
                            grow, prevHelperInd,
                            movedInd=[], analizeInd=[],
                            formove=false;
                        BpmOperator.markOperators(braBegInd, helperInd); // mark branches for operatiions inside
                        $('.bpm_operator[data-unique_index="'+helperInd+'"]').attr('mark','marked');
                        if ($('.bpm_operator.and_helper[mark]').length) {

                            prevHelperInd = $(helperArray[i-1]).data('unique_index'); // find previos and_helper
                            if (prevHelperInd) {
                                for (var g=1; g<100; g++) {  // cikle for finding first opertator on branch with previos and_helper
                                    cikleInd = $('path.arrow[arr-end="'+prevHelperInd+'"]').attr('arr-begin');
                                    if (cikleInd==braBegInd) {
                                        g=100; // close cikle
                                        BpmOperator.unmarkOperators(prevHelperInd, helperInd); // unmark operators for ignoring
                                        $('.bpm_operator[mark]').not('[data-unique_index="'+braBegInd+'"]').each(function(){
                                            var ind = $(this).data('unique_index');
                                            analizeInd.push(ind);
                                        });
                                    } else {
                                        prevHelperInd = cikleInd;
                                    }
                                }
                            } else { // if bpm_operator = last in branch and $('.condrag').gridrow = X.gridrow() in build
                                if (helperArray.length==1) {
                                    var pairs = ProcessObj.getPairsOfArrows();
                                    $.each(pairs, function () {
                                        var base = this;

                                        if (base.type == 'horizontal' && !$(base.path).is('[data-is]')) {
                                            $.each(pairs, function () {
                                                var element = this;

                                                if (base.type == element.type && !$(element.path).is('[data-is]') && base.y == element.y && $(base.path).attr('arr-end') != $(element.path).attr('arr-end')) {
                                                    if (element.x <= base.x //by X
                                                        && (base.x <= element.x1)) {
                                                        $(element.path).attr('data-is', true);
                                                        oneOperator = $('.bpm_operator[data-unique_index]').filter('[data-unique_index="'+$(element.path).attr('arr-end')+'"]');
                                                        oneOperator.attr('gridrow', parseInt(oneOperator.attr('gridrow'))+1);
                                                        return false;
                                                    }
                                                }
                                            });
                                        }
                                    });
                                    $('svg.arrows path.arrow').removeAttr('data-is');
                                }
                            }
                        }
                        BpmOperator.unmarkOperators();
                        if (analizeInd.length>0 && prevHelperInd) {
                            BpmOperator.markOperators(prevHelperInd, helperInd);
                            var listResponsible = {};

                            $('.bpm_operator[mark]').each(function () {
                                var _this = $(this);
                                grow = parseInt($(this).attr('gridrow'));
                                var ugId = _this.closest('.bpm_unit').data('ug_id')
                                if (listResponsible[ugId] && listResponsible[ugId].length) {
                                    listResponsible[ugId].push(grow);
                                } else {
                                    listResponsible[ugId] = [grow];
                                }
                                ;

                                if ($(this).is('[end-branches]')) {
                                    $('path.arrow[branch][branch-end][arr-begin="' + $(this).data('unique_index') + '"]').not('[barnch="1"][modifier="100"]').each(function () {
                                        var growa = parseInt($(this).attr('modifier')) / 100 + grow;
                                        if (growa) {
                                            listResponsible[ugId].push(growa);
                                        }
                                    });
                                }
                            });
                            BpmOperator.unmarkOperators();

                            $.each(analizeInd, function(i,val){
                                var $val = $('.bpm_operator[data-unique_index="'+val+'"]');
                                var tmpGrow = parseInt($val.attr('gridrow')),
                                    ugId = $val.closest('.bpm_unit').data('ug_id');

                                if (!listResponsible[ugId]) {
                                    listResponsible[ugId] = [0];
                                };

                                var maxRow = Math.max.apply(null, listResponsible[ugId]),
                                    minRow = Math.min.apply(null, listResponsible[ugId]);

                                listResponsible[ugId].minRowToMove = maxRow;
                                if (tmpGrow>=minRow) {
                                    movedInd.push(val);
                                    if (tmpGrow<maxRow+1) {
                                        formove=true;
                                        if (tmpGrow < listResponsible[ugId].minRowToMove) {
                                            listResponsible[ugId].minRowToMove = tmpGrow;
                                        }
                                    }
                                }
                            });
                            if (formove && movedInd.length) {
                                $.each(movedInd, function(i,val){
                                    var $val = $('.bpm_operator[data-unique_index="'+val+'"]');
                                    var preGrow = parseInt($val.attr('gridrow')),
                                        ugId = $val.closest('.bpm_unit').data('ug_id');
                                    var maxRow = Math.max.apply(null, listResponsible[ugId]);
                                    var offset = listResponsible[ugId].minRowToMove-1;
                                    $val.attr('gridrow',(maxRow-offset)+preGrow+1+'')
                                });
                            }
                        }
                    });
                    this.listToCorrection = [];
                },
                offsetElement: function (list) {
                    $.each(list, function () {
                        $(this.element).attr('gridrow', this.row);
                    });
                },
                verticalUnCrossing: function (list) {
                    $.each(list, function(i,val){
                        var colCicle, indexCicle,
                            valArr = val.split('-'),
                            _crossOper = $('.bpm_unit[data-unique_index="'+valArr[0]+'"][data-ug_id="'+valArr[1]+'"][data-ug_type="'+valArr[2]+'"] .bpm_operator[gridrow="'+valArr[3]+'"][gridcol="'+valArr[4]+'"]').not('.fake_operator').not('[style*=";"]');

                        if (_crossOper.length){
                            _crossOper.attr('mark','marked');
                            indexCicle = _crossOper.data('unique_index');
                            colCicle = parseInt(_crossOper.attr('gridcol'));

                            if (ProcessObj.BPM.verifyEmptySpace(indexCicle, colCicle).length) { // return 'mark' operators
                                BpmOperator.moveMarkedOperators('right');
                                BpmOperator.unmarkOperators();
                            };
                            return false;
                        }
                    });
                    this.listToCorrection = [];
                },
                cornersUnCrossing: function () {},
                // Накладання вертикальних гілок 1 на 1, і нижня зсуваєтсья вправо.
                verticalOverlayArrowsUnCrossing: function (list) {
                    Arrows.recountAll();
                    $.each(list, function (){
                        var $item = this.big.list.attr('mark','marked');

                        BpmOperator.moveMarkedOperators('right');
                        $item.removeAttr('mark');

                        $.each(this.small.list.not(':last'), function () {
                            var _this = $(this);
                            _this.attr('gridcol', parseInt(_this.attr('gridcol')) + 1);
                        });
                    })
                }
            },
            correction: function () {
                if (this.listToCorrection.length && (typeof this.callback == 'function')) {
                    this.callback(this.listToCorrection)
                }
                return this.listToCorrection.length ? true : false;
            },
            // Проверяет наложения операторов
            checkOverlay: function (marked) { // check overlay operators
                var object = this,
                    $responsible, coordinates;

                object.callback = object.functionsOfCorrection.checkOverlay;
                object.listToCorrection = [];
                if (marked) {
                    $('.element[data-type="responsible"]').each(function(){
                        $.each($(this).find($('.bpm_operator[data-unique_index]')), function(){
                            var $this = $(this);

                            $responsible = $this.closest('.element[data-type="responsible"]');
                            coordinates = $this.attr('gridrow')+'-'+$this.attr('gridcol');

                            if ($responsible.find('.bpm_operator[data-unique_index][gridrow="'+$this.attr('gridrow')+'"][gridcol="'+$this.attr('gridcol')+'"]').not($this).length > 1) {
                                object.listToCorrection.push({ key : $this.data('unique_index'), direction : 'right' });
                            }
                        });
                    });
                }
                return object;
            },
            checkBranchEnds: function () { // Checking helper position after zeroBuild / Проверка позиции хелпера after zeroBuild
                var gridparent, gridcol, gridcolMax,
                    object = this;

                object.callback = object.functionsOfCorrection.checkBranchEnds;
                object.listToCorrection = [];
                $('.element[data-type="responsible"]').each(function(){
                    $(this).find('.bpm_operator[end-branches]').not('.condrag').each(function(){
                        var $svgArrows = $('svg.arrows'),
                            index = $(this).attr('end-branches'),
                            gridcolArr = [];
                        if ($svgArrows.find('path.arrow[arr-end="'+index+'"]').length>1) {
                            gridparent = $('.bpm_operator[data-unique_index="'+index+'"]').attr('gridcol');
                            $svgArrows.find('path.arrow[arr-end="'+index+'"]').each(function(){
                                gridcol = $('.bpm_operator[data-unique_index="'+$(this).attr('arr-begin')+'"]').attr('gridcol');
                                gridcolArr.push(gridcol);
                            });

                            if (gridcolArr.length) {
                                gridcolMax = Math.max.apply(null, gridcolArr);
                                if (gridcolMax+1>gridparent) {
                                    object.listToCorrection.push({
                                        moveCounter : gridcolMax-gridparent+1,
                                        index: index,
                                        direction: 'right'
                                    })
                                }
                            }
                        }
                    });
                });

                return object;
            },
            checkAndHelperPlaces: function (helperFollow) {
                var bpmOperator = $('.bpm_operator[data-unique_index]'),
                    object = this;

                object.callback = object.functionsOfCorrection.checkAndHelperPlaces;
                object.listToCorrection = [];

                if (helperFollow) {
                    bpmOperator.filter('.and_helper').each(function(){
                        var helperInd = $(this).data('unique_index'),
                            nextInd = bpmOperator.filter('[end-branches="'+helperInd+'"]').data('unique_index'),
                            _nextOp = bpmOperator.filter('[data-unique_index="'+nextInd+'"]');
                        if (typeof(nextInd)=='string') {
                            var nextRow = _nextOp.attr('gridrow'),
                                nextResp = _nextOp.closest('.bpm_unit').data('ug_id'),
                                thisResp = $(this).closest('.bpm_unit').data('ug_id');
                            if (!$('path.arrow[arr-begin="'+helperInd+'"][arr-end="'+nextInd+'"]').attr('branch-end')) {
                                object.listToCorrection.push({
                                    element: this,
                                    row: nextRow,
                                    object: nextResp!=thisResp ? _nextOp.closest('.bpm_tree') : null
                                });
                            }
                        }
                    });
                }
                return object;
            },
            checkingHorizontalCrossing  : function () {
                var operCounter = $('.bpm_tree .bpm_operator').not('.fake_operator').length,
                    pathes = $('svg.arrows path.arrow'),
                    bpmOperators = $('.bpm_operator'),
                    object = this;

                object.callback = object.functionsOfCorrection.checkingHorizontalCrossing;
                object.listToCorrection = [];

                $.each($('.element[data-type="responsible"]'), function(){
                    var list, maxCountRows, $oper,
                        $this = $(this);

                    if ($this.find('.bpm_operator').length) {
                        maxCountRows = operCounter;

                        for (var c=1; c<operCounter; c++) {
                            list = $this.find('.bpm_operator.and_helper[gridcol="'+c+'"]');

                            if (list.length) {
                                maxCountRows = Math.max.apply(null,list.map(function () { return parseInt($(this).attr('gridrow')); }).get());
                            }

                            for (var r=1; r<=maxCountRows; r++) {
                                $oper = list.filter('[gridrow="'+r+'"]');

                                if ($oper.length) {
                                    var b, andHelperIndex = $oper.data('unique_index'),
                                        beginIndex = bpmOperators.filter('[end-branches="'+andHelperIndex+'"]').data('unique_index'),
                                        emptyBranches = pathes.filter('[arr-begin="'+beginIndex+'"][arr-end="'+andHelperIndex+'"]'),
                                        baseRow = parseInt($oper.attr('gridrow'));

                                    if (emptyBranches.attr('modifier',0).length) {
                                        var startRow = baseRow;
                                        for (b=1; b<=10; b++) {
                                            var arrow = emptyBranches.filter('[branch="'+b+'"][arr-end="'+andHelperIndex+'"]');
                                            if (arrow.length) {
                                                var emptyPlace = ProcessObj.BPM.getEmptyPlace(startRow,beginIndex,andHelperIndex, arrow);
                                                var delta = Math.abs(baseRow - emptyPlace) * 100;

                                                startRow = emptyPlace+1;
                                                if (arrow.is('[branch-end="main"]')) {
                                                    emptyBranches.filter('[branch-end="main"]').attr('branch-end','true');
                                                }
                                                if (delta) {
                                                    arrow.attr('modifier', delta);
                                                } else {
                                                    arrow.attr('branch-end','main').removeAttr('modifier');
                                                }
                                                Arrows.recount(arrow);
                                            }
                                        }
                                    }
                                    object.listToCorrection.push($oper[0]);
                                }
                            }
                        }
                    }
                });

                return object;
            },
            rebuildFromZero: function (){
                var $inwork,
                    $currentOp = $('.bpm_operator[data-name="begin"]'),
                    arrow = $('svg.arrows path.arrow'),
                    bpmOperator = $('.bpm_operator[data-unique_index]'); // entering

                $currentOp.attr('mark','inwork');
                for (var i=0; i<100; i++) {
                    $inwork = bpmOperator.filter('[mark="inwork"]');
                    if ($inwork.length) {
                        $.each($inwork, function(){
                            var willGo = true,
                                $this = $(this);

                            $.each(arrow.filter('[arr-end="'+$this.data('unique_index')+'"]'), function(){
                                var arrBegin = $(this).attr('arr-begin');
                                if (bpmOperator.filter('[data-unique_index="'+arrBegin+'"]').attr('mark')=='inwork' || !bpmOperator.filter('[data-unique_index="'+arrBegin+'"]').attr('mark')) {
                                    willGo = false;
                                }
                            });
                            if (willGo) {
                                $.each(arrow.filter('[arr-begin="'+$this.data('unique_index')+'"]'), function(){
                                    bpmOperator.filter('[data-unique_index="'+$(this).attr('arr-end')+'"]').attr('mark','inwork');
                                });
                                $this.attr('mark','marked').attr('gridcol',i+1);
                            }
                        });
                    } else {
                        i=100;
                    }
                }
                BpmOperator.unmarkOperators();
            },
            //пересечение операторов c вертикальной линией и пересечение углов
            //vertical crossing and corners crossing
            verticalUnCrossing : function () {
                var correctionSet,
                    object = this;

                correctionSet = function (key) {
                    if ($.inArray(key,object.listToCorrection)<0) { object.listToCorrection.push(key);}
                };

                this.listToCorrection = [];
                this.callback = this.functionsOfCorrection.verticalUnCrossing;

                $.each($('svg.arrows path.arrow'), function(){ // analize arrows
                    var $this = $(this);

                    if ($this.attr('d').split(' ').length>15) { // arrow not straight
                        var key, operCitype,
                            _this = $this,
                            begOperator = ProcessObj.getParams($('.bpm_operator[data-unique_index]').filter('[data-unique_index="'+_this.attr('arr-begin')+'"]')),
                            endOperator = ProcessObj.getParams($('.bpm_operator[data-unique_index]').filter('[data-unique_index="'+_this.attr('arr-end')+'"]'));

                        begOperator['offsetTop'] = begOperator.$.offset().top,
                            endOperator['offsetTop'] = endOperator.$.offset().top;
                        key = begOperator.keyRespUnique +'-'+begOperator.ug_id+'-'+begOperator.ug_type;

                        if (begOperator.$.is('.and_helper')) {
                            return true; // continue
                        }

                        if (_this.is('[branch-end]')) {  // not normal arrow
                            if (_this.is('[branch]')) {// empty branch
                                var midif = parseInt(_this.attr('modifier'))/100;

                                for (var i=begOperator.row+1; i<midif+1; i++) {
                                    correctionSet(key+'-'+i+'-'+begOperator.col);
                                }
                                for (i=endOperator.row+1; i<midif+1; i++) {
                                    correctionSet(key+'-'+i+'-'+endOperator.col);
                                }
                            } else { // arrow - end of branch
                                if (begOperator.offsetTop < endOperator.offsetTop) { // Begin is higer then End
                                    if (begOperator.ug_id == endOperator.ug_id && begOperator.ug_type == endOperator.ug_type) { // Begin and End in same responsible block
                                        for (i=begOperator.row+1; i<endOperator.row; i++) {
                                            object.listToCorrection.push(key+'-'+i+'-'+endOperator.col);
                                        }
                                    } else { // Begin and End not in same responsible block
                                        var operBrespRows = parseInt($('.bpm_unit[data-ug_id="'+begOperator.ug_id+'"]').attr('rows'));
                                        for (i=begOperator.row+1; i< operBrespRows+1; i++) {
                                            object.listToCorrection.push(key+'-'+begOperator.ug_id+'-'+i+'-'+endOperator.col);
                                        }
                                        for (i=1; i<100; i++) {
                                            if ($('.bpm_unit[data-ug_id="'+begOperator.ug_id+'"]').next().data('ug_id')== endOperator.ug_id) {
                                                i=100;
                                                for (var o=1; o<endOperator.row; o++) {
                                                    object.listToCorrection.push(endOperator.ug_id+'-'+endOperator.ug_type+'-'+o+'-'+endOperator.col);
                                                }
                                            } else {
                                                operCicle = $('.bpm_unit[data-ug_id="'+begOperator.ug_id+'"]').next().data('ug_id');
                                                var operCrespRows = parseInt($('.bpm_unit[data-ug_id="'+operCicle+'"]').attr('rows'));
                                                for (o=1; o<operCrespRows+1; o++) {
                                                    object.listToCorrection.push(key+'-'+o+'-'+endOperator.col);
                                                }
                                            }
                                        }
                                    }
                                } else {  // End is higer then Begin
                                    if (begOperator.ug_id==endOperator.ug_id && begOperator.ug_type == endOperator.ug_type) { // Begin and End in same responsible block
                                        for (i= endOperator.row+1; i<begOperator.row; i++) {
                                            object.listToCorrection.push(key +'-'+i+'-'+endOperator.col);
                                        }
                                    } else { // Begin and End not in same responsible block
                                        var operErespRows = parseInt($('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"][data-ug_type="'+endOperator.ug_type+'"]').attr('rows'));
                                        for (i=endOperator.row+1; i<=operErespRows; i++) {
                                            object.listToCorrection.push(endOperator.ug_id+'-'+endOperator.ug_type+'-'+i+'-'+endOperator.col); // +begOperator.col
                                        }

                                        for (i=1; i<100; i++) {
                                            if ($('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"]').next().data('ug_id')==begOperator.ug_id) {
                                                i=100;
                                                for (o=1; o<begOperator.row; o++) {
                                                    object.listToCorrection.push(key +'-'+o+'-'+endOperator.col);
                                                }
                                            } else {
                                                var operCicle = $('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"]').next().data('ug_id');
                                                var operCrespRows = parseInt($('.bpm_unit[data-ug_id="'+operCicle+'"]').attr('rows'));
                                                for (o=1; o<operCrespRows+1; o++) {
                                                    object.listToCorrection.push(operCicle+'-'+o+'-'+endOperator.col);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else { // normal arrow
                            if (begOperator.offsetTop<endOperator.offsetTop) { // Begin is higher then End
                                if (begOperator.ug_id==endOperator.ug_id && begOperator.ug_type == endOperator.ug_type && begOperator.keyRespUnique == endOperator.keyRespUnique) { // Begin and End in same responsible block
                                    for (i=begOperator.row+1; i<endOperator.row; i++) {
                                        correctionSet(key+'-'+i+'-'+begOperator.col);
                                    }
                                } else { // Begin and End not in same responsible block

                                    var operBrespRows = parseInt($('.bpm_unit[data-unique_index="'+begOperator.keyRespUnique+'"][data-ug_id="'+begOperator.ug_id+'"][data-ug_type="'+begOperator.ug_type+'"]').attr('rows'));
                                    for (i=begOperator.row+1; i<=operBrespRows; i++) {
                                        correctionSet(key+'-'+i+'-'+begOperator.col);
                                    }
                                    var nextRespB = $('.bpm_unit[data-unique_index="'+begOperator.keyRespUnique+'"][data-ug_id="'+begOperator.ug_id+'"][data-ug_type="'+begOperator.ug_type+'"]').next();

                                    for (i=1; i<100; i++) {
                                        if ($('.bpm_unit[data-unique_index="'+begOperator.keyRespUnique+'"][data-ug_id="'+begOperator.ug_id+'"]').next().data('ug_id')==endOperator.ug_id &&
                                            $('.bpm_unit[data-unique_index="'+begOperator.keyRespUnique+'"][data-ug_type="'+begOperator.ug_type+'"]').next().data('ug_type')==endOperator.ug_type) {
                                            i=100;
                                            for (o=1; o<endOperator.row; o++) {
                                                correctionSet(endOperator.keyRespUnique + '-' + endOperator.ug_id + '-' + endOperator.ug_type + '-' + o + '-' + begOperator.col);
                                            }
                                        } else {
                                            nextRespB = $('.bpm_unit[data-ug_id="'+begOperator.ug_id+'"][data-ug_id="'+begOperator.ug_id+'"]').next();
                                            for (nr=1; nr<=$('.bpm_unit').length; nr++) {
                                                operCicle = nextRespB.data('ug_id');
                                                operCitype = nextRespB.data('ug_type');
                                                if (operCicle == endOperator.ug_id && operCitype == endOperator.ug_type) {
                                                    var $element = nextRespB.find('.bpm_operator[gridcol="'+begOperator.col+'"]');
                                                    if ($element.length) {
                                                        correctionSet(operCicle+'-'+operCitype+'-'+$element.attr('gridrow')+'-'+begOperator.col);
                                                    }
                                                    i=100;
                                                    break;
                                                } else {
                                                    var operCrespRows = parseInt(nextRespB.attr('rows'));
                                                    for (o=1; o<operCrespRows+1; o++) {
                                                        correctionSet(nextRespB.data('unique_index') + '-' + operCicle + '-' + operCitype + '-' + o + '-' + begOperator.col);
                                                    }
                                                    nextRespB = nextRespB.next();
                                                }
                                            } i=100;
                                            if (nextRespB.data('ug_id')==endOperator.ug_id &&
                                                nextRespB.data('ug_type')==endOperator.ug_type) {
                                                i=100;
                                            }
                                        }
                                    }
                                }
                            } else {  // End is higher then Begin
                                if (begOperator.ug_id==endOperator.ug_id
                                    &&
                                    begOperator.ug_type==endOperator.ug_type
                                    && $(begOperator.$.closest('.bpm_unit')).data('unique_index') === $(endOperator.$.closest('.bpm_unit')).data('unique_index')) { // Begin and End in same responsible block
                                    for (i=endOperator.row+1; i<begOperator.row; i++) {
                                        correctionSet(key +'-'+i+'-'+begOperator.col);
                                    }
                                } else { // Begin and End not in same responsible block
                                    var operErespRows = parseInt($('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"]').attr('rows'));
                                    for (i=endOperator.row+1; i<=operErespRows; i++) {
                                        correctionSet(endOperator.keyRespUnique + '-' + endOperator.ug_id + '-' + endOperator.ug_type + '-' + i + '-' + begOperator.col);
                                    }
                                    for (i=1; i<100; i++) {
                                        if ($('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"]').next().data('ug_id')==begOperator.ug_id &&
                                            $('.bpm_unit[data-ug_type="'+endOperator.ug_type+'"]').next().data('ug_type')==begOperator.ug_type) {
                                            i=100;
                                            for (o=1; o<begOperator.row; o++) {
                                                correctionSet(key +'-'+o+'-'+begOperator.col);
                                            }
                                        } else {
                                            nextRespB = $('.bpm_unit[data-ug_id="'+endOperator.ug_id+'"][data-ug_id="'+endOperator.ug_id+'"]').next();
                                            for (var nr=1; nr<=$('.bpm_unit').length; nr++) {
                                                operCicle = nextRespB.data('ug_id');
                                                operCitype = nextRespB.data('ug_type');
                                                if (operCicle == begOperator.ug_id && operCitype == begOperator.ug_type) {
                                                    var $element = nextRespB.find('.bpm_operator[gridcol="'+begOperator.col+'"][data-unique_index]').not(begOperator.$)
                                                    if ($element.length) {
                                                        if (endOperator.$.offset().top < begOperator.$.offset().top && begOperator.$.offset().top < $element.offset().top) { }
                                                        else correctionSet(operCicle+'-'+operCitype+'-'+$element.attr('gridrow')+'-'+begOperator.col);
                                                    }
                                                    i=100;
                                                    break;
                                                } else {
                                                    var operCrespRows = parseInt(nextRespB.attr('rows'));
                                                    for (o=1; o<operCrespRows+1; o++) {
                                                        correctionSet(nextRespB.data('unique_index') + '-' + operCicle + '-' + operCitype + '-' + o + '-' + begOperator.col);
                                                    }
                                                    nextRespB = nextRespB.next();
                                                }
                                            } i=100;
                                            if (nextRespB.data('ug_id')==begOperator.ug_id &&
                                                nextRespB.data('ug_type')==begOperator.ug_type) {
                                                i=100;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                });

                return object;
            },
            // Накладання вертикальних гілок 1 на 1, і нижня зсуваєтсья вправо.
            verticalOverlayArrowsUnCrossing : function () {
                var object = this,
                    bpmOperators = $('.bpm_operator[data-unique_index]');

                object.callback = object.functionsOfCorrection.verticalOverlayArrowsUnCrossing;
                object.listToCorrection = [];
                $.each($('svg.arrows path.arrow'), function () {
                    var arrows, _this = $(this),
                        path = {
                            d: _this.attr('d').split(' ')
                        };

                    if (_this.is('[data-is-crossing]')) {
                        return false;
                    }

                    if (path.d.length == 18) {
                        // if line is vertical we will have y coord else x coord
                        var param = path.d[1] == path.d[4] ? path.d[1] : path.d[2];

                        arrows = $('svg.arrows path.arrow').filter('[d*="' + param + '"]:not([branch])').not('[data-is]');
                        //arrows = $('svg.arrows path.arrow').filter('[d*="' + param + '"]:not([branch]):not([branch-end])').not('[data-is]');
                        arrows.attr('data-is', true);

                        if (arrows.length==1) {
                            return true; // continue;
                        }

                        //filter in order from top to bottom
                        arrows.sort(function (a, b) {
                            var a = $(a).attr('d').split(' '),
                                b = $(b).attr('d').split(' ');
                            return (a[2] < b[2]) ? -1 : 0;
                        })


                        $.each(arrows, function () {
                            var $list,
                                $selected, _this = $(this),
                                baseD = Arrows.parse(_this);

                            if (!_this.not('[data-mark]').length) return true; //continue

                            $list = arrows.filter('[branch-end]').length ? arrows : arrows.not(_this);

                            $selected = $list.filter(function () {
                                var bool,
                                    $this = $(this),
                                    d = Arrows.parse($this);

                                bool = (d.y1 > baseD.y2 && baseD.y2 > d.y2 && $this.attr('arr-begin') != _this.attr('arr-begin'));

                                if ($this.is('[branch-end]')) {
                                    bool = (d.y2 > baseD.y1 && baseD.y2<d.y3 && $this.attr('arr-begin') != _this.attr('arr-begin'))
                                }

                                return bool ? this : null; //crossing direction
                            });

                            if ($selected.length) {
                                var path, operator, searchHelper, marked,
                                    search = 0,
                                    element = [ {start: bpmOperators.filter('[data-unique_index="' + _this.attr('arr-end') + '"]')},
                                        {start: bpmOperators.filter('[data-unique_index="' + $selected.attr('arr-begin') + '"]')}];

                                $selected.attr('data-mark', true); // marked
                                _this.attr('data-mark', true);     // marked

                                for (var i = element.length - 1; i >= 0; i--) {
                                    element[i].start.attr('mark', 'marked');
                                    element[i]['list'] = $([]);
                                    searchHelper = true;

                                    while (searchHelper) {
                                        path = $('svg.arrows path.arrow').filter('[arr-begin="' + element[i].start.data('unique_index') + '"]');
                                        operator = bpmOperators.filter('[data-unique_index="' + path.attr('arr-end') + '"]').attr('mark', 'marked');

                                        if (operator.is('[end-branches]')) {
                                            search++;
                                            BpmOperator.markOperators(operator.attr('data-unique_index'), operator.attr('end-branches'));
                                        } else {
                                            if (operator.is('.and_helper')) {
                                                if (search) {
                                                    search--;
                                                } else {
                                                    searchHelper = !searchHelper;
                                                }
                                            }
                                        }
                                        if (operator.length) {
                                            element[i].start = operator.is('[end-branches]') ? bpmOperators.filter('[data-unique_index="' + operator.attr('end-branches') + '"]') : operator;
                                        } else searchHelper = !searchHelper;
                                    }
                                    marked = $('[mark]');

                                    marked.sort(function (a, b) {
                                        var contentA = parseInt($(a).attr('gridcol'));
                                        var contentB = parseInt($(b).attr('gridcol'));
                                        return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;
                                    })

                                    $.each(marked, function () {
                                        element[i].list.push(this);
                                    });
                                    marked.removeAttr('mark');
                                }

                                var $item, marked,
                                    data = {
                                        big: (element[0].list.length > element[1].list.length) ? element[0] : element[1],
                                        small: (element[0].list.length > element[1].list.length) ? element[1] : element[0]
                                    }
                                // optimization
                                if (data.small.list.length) {
                                    var $elBig = $(data.big.list).first(),
                                        $elSmall = $(data.small.list).first(),
                                        begRespOfsetTop = $elBig.closest('.bpm_unit').offset().top,
                                        toRespOfsetTop = $(data.big.list).eq(1).closest('.bpm_unit').offset().top,
                                        baseRespOfElement = $elSmall.closest('.bpm_unit').offset().top;

                                    if (toRespOfsetTop < baseRespOfElement && begRespOfsetTop < baseRespOfElement
                                        || ($elBig.offset().top < $elSmall.offset().top && begRespOfsetTop == baseRespOfElement && toRespOfsetTop)) { //our item is out side in bottom
                                        data.small.list = $(data.small.list).filter('.and_helper');// only helper
                                    }
                                    $item = $(data.small.list);
                                    if (ProcessObj.BPM.verifyEmptySpace($item.first().attr('data-unique_index'), $item.first().attr('gridcol')).length) { // return 'mark' operators
                                        data.small.list = $item.filter('[mark]').removeAttr('mark');
                                    }
                                }
                                if (data.big.list.length) {
                                    $item = $(data.big.list);
                                    if (ProcessObj.BPM.verifyEmptySpace($item.first().attr('data-unique_index'), $item.first().attr('gridcol')).length) { // return 'mark' operators
                                        data.big.list = $item.filter('[mark]').removeAttr('mark');
                                    }
                                }
                                object.listToCorrection.push(data);

                                $selected.removeAttr('data-mark'); // marked
                                _this.removeAttr('data-mark');     // marked
                                return false; // continue;
                            }
                        })
                    }
                });
                $('svg.arrows path.arrow').removeAttr('data-is');

                return object;
            },
            cornersUnCrossing : function () {
                var resultCorners, recountCorners = true,
                    bpmOperators = $('.bpm_operator[data-unique_index]'),
                    i = 1;

                while (i<100 && recountCorners) {
                    resultCorners=[];
                    recountCorners=false;
                    i++;
                    $.each($('svg.arrows path.arrow'), function(){
                        var line = $(this).attr('d').split(' '),
                            operB = bpmOperators.filter('[data-unique_index="'+$(this).attr('arr-begin')+'"]'),
                            operE = bpmOperators.filter('[data-unique_index="'+$(this).attr('arr-end')+'"]'),
                            offsetTopB = operB.offset().top,
                            offsetTopE = operE.offset().top,
                            operBrow = parseInt(operB.attr('gridrow')),
                            operErow = parseInt(operE.attr('gridrow')),
                            operEresp = operE.closest('.bpm_unit').data('ug_id'),
                            operErespType = operE.closest('.bpm_unit').data('ug_type');

                        if (line.length>15 && line.length<19 && !$(this).attr('branch-end')) { // arrow not straight & not branch end
                            var $unit = operB.closest('.bpm_unit'),
                                operBresp = $unit.data('ug_id'),
                                operBrespType = $unit.data('ug_type'),
                                keyE = operE.closest('.bpm_unit').data('unique_index');

                            if (offsetTopB<offsetTopE) { // Begin is higher then End
                                var _cornerOper = $('.bpm_unit[data-unique_index="'+ keyE +'"][data-ug_id="'+operEresp+'"][data-ug_type="'+operErespType+'"] .bpm_operator[gridrow="'+operErow+'"][gridcol="'+operB.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');

                                if (_cornerOper.length){ // if find smb in corner
                                    resultCorners.push(_cornerOper);
                                    for (r=1; r<100; r++) {  // collect operators downward
                                        var newoperErow = operErow+r,
                                            _cornerOper = $('.bpm_unit[data-ug_id="'+operEresp+'"] .bpm_operator[gridrow="'+newoperErow+'"][gridcol="'+operB.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');
                                        if (_cornerOper.length) {
                                            resultCorners.push(_cornerOper);
                                        } else {
                                            r=100;
                                        }
                                    }
                                    $.each(resultCorners, function(i,val){ // move collected oparetors
                                        $(this).attr('gridrow',parseInt($(this).attr('gridrow'))+1+'');
                                    });
                                    recountCorners=true; // go on new cicle
                                    return false; // exit from each
                                }
                            } else if (offsetTopB>offsetTopE) {  // End is higher then Begin
                                var r,
                                    keyE = operE.closest('.bpm_unit').data('unique_index');
                                _cornerOper = $('.bpm_unit[data-unique_index="'+ keyE +'"][data-ug_id="'+operEresp+'"][data-ug_type="'+operErespType+'"] .bpm_operator[gridrow="'+operErow+'"][gridcol="'+operB.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');

                                if (_cornerOper.length){
                                    resultCorners.push(operE);
                                    for (r=1; r<100; r++) {  // collect operators downward
                                        var newoperErow = operErow+r,
                                            _cornerOper = $('.bpm_unit[data-ug_id="'+operEresp+'"] .bpm_operator[gridrow="'+newoperErow+'"][gridcol="'+operE.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');
                                        if (_cornerOper.length>0) {
                                            resultCorners.push(_cornerOper);
                                        } else {
                                            r=100;
                                        }
                                    }
                                    var keyB = operB.closest('.bpm_unit').data('unique_index'),
                                        keyE = operE.closest('.bpm_unit').data('unique_index')

                                    if (operEresp==operBresp && operErespType == operBrespType && keyB == keyE) {
                                        resultCorners.push(operB);
                                        for (r=1; r<100; r++) {  // collect operators downward
                                            var newoperBrow = operBrow+r,
                                                _cornerOper = $('.bpm_unit[data-ug_id="'+operEresp+'"] .bpm_operator[gridrow="'+newoperBrow+'"][gridcol="'+operB.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');
                                            if (_cornerOper.length>0) {
                                                resultCorners.push(_cornerOper);
                                            } else {
                                                r=100;
                                            }
                                        }
                                    }
                                    $.each(resultCorners, function(i,val){ // move collected oparetors
                                        $(this).attr('gridrow',parseInt($(this).attr('gridrow'))+1+'');
                                    });
                                    recountCorners=true; // go on new cicle
                                    return false; // exit from each
                                }
                            }
                        } else {
                            if (line.length==21 && $(this).is('[branch-end]')) {
                                if (offsetTopB==offsetTopE) {
                                    operErow = operBrow + (parseInt($(this).attr('modifier'))/100);
                                    var _cornerOper = $('.bpm_unit[data-ug_id="'+operEresp+'"] .bpm_operator[gridrow="'+operErow+'"][gridcol="'+operB.attr('gridcol')+'"]').not('.fake_operator').not('[style*=";"]');

                                    if (_cornerOper.length) { // if find smb in corner
                                        _cornerOper.attr('gridrow',parseInt(_cornerOper.attr('gridrow'))+1);
                                        recountCorners=true; // go on new cicle
                                        return false; // exit from each
                                    }
                                };
                            }
                        }
                    });

                    if (resultCorners.length) {
                        $.each(resultCorners, function(){
                            var ind = $(this).data('unique_index');
                            Arrows.recount($('svg.arrows path.arrow').filter('[arr-begin="'+ind+'"]'));
                            Arrows.recount($('svg.arrows path.arrow').filter('[arr-end="'+ind+'"]'));
                        });
                    }
                }

                return false;
            },
            offsetElement : function () {
                var $clone = $('.condrag').not('[data-name="and"]'),
                    object = this,
                    elements = $('.bpm_operator[data-unique_index]');

                object.listToCorrection = [];
                object.callback = object.functionsOfCorrection.offsetElement;

                if ($clone.length) {
                    $.each(elements.filter('.and_helper'), function () {
                        var bpmBegin,
                            _this = $(this);

                        bpmBegin = elements.filter('[end-branches="'+_this.data('unique_index')+'"]');
                        if (bpmBegin.length) {
                            var bpmOperators = $([]),
                                path = $('path[arr-begin="'+bpmBegin.data("unique_index")+'"]').not('[branch-end]');

                            $.each(path,function () {
                                var item = elements.filter('[data-unique_index="'+$(this).attr('arr-end')+'"]');

                                if (item.length) {
                                    bpmOperators.push(item[0]);
                                }
                            });

                            if (bpmOperators.length && bpmOperators.is('.condrag')) {
                                var bpmUnit = bpmOperators.filter('.condrag').closest('.bpm_unit');

                                bpmUnit.find(bpmOperators).not('.condrag').filter('[gridrow="'+$clone.attr('gridrow')+'"]').filter(function(){
                                    var _this = $(this);
                                    if (path.filter('[arr-end="'+_this.data('unique_index')+'"]').length) {
                                        object.listToCorrection.push({element: _this, row: parseInt(_this.attr('gridrow'))+1 });
                                    }
                                });
                            }
                        }
                    });
                }
                return object;
            },
            init: function (helperFollow, marked, isHorisontalChecking) {
                var label,
                    moved = true,
                    object = this,
                    fullCircle = 16, //NOT < 4
                    byError = 6;

                object.rebuildFromZero();
                object.checkAndHelperPlaces(helperFollow).correction();

                while (moved) {
                    ProcessObj.BPM.reDrawOfArrows ? Arrows.recountAll() : '';
                    if ( object.checkOverlay(marked).correction()
                        || object.verticalOverlayArrowsUnCrossing().correction()
                        || object.verticalUnCrossing().correction()
                        || object.checkBranchEnds().correction()
                        || object.offsetElement().correction()
                        || object.cornersUnCrossing()
                        || fullCircle
                    ) {
                        label = false;
                        if (BpmModel.addNewBranch || BpmModel.removedOfOperator || isHorisontalChecking) {
                            if (object.checkingHorizontalCrossing()) {
                                object.correction();
                                label = !label;
                            }
                        }
                        (object.listToCorrection.length && label) ? byError-- : fullCircle--;
                    } else {
                        fullCircle = -1;
                    }

                    if (fullCircle<0 || byError<0) {
                        moved = !moved;
                    }
                };

                //Inspection.moveOperatorsToLeft();

                //ProcessObj.listPointByCrossing.init();
                ProcessObj.recountRespBlocks();
                Arrows.recountAll();
            }
        },
        recountRespBlocks : function(){
            var bpmUnit = $('div.bpm_unit').not('.outer_unit'),
                $bpmBlock = $('.bpm_block');

            $.each(bpmUnit, function(){
                var $this = $(this),
                    bpmOperator = $this.find('.bpm_operator').not('[style*=";"]').not('.fake_operator'),
                    maxRow = 1;

                (!bpmOperator.length>0) ? $this.attr('rows', '2') : '';
                bpmOperator.not('.and_helper').each(function(){
                    var dynamicRow,
                        _this = $(this),
                        attrName = _this.data('name'),
                        uniqueIndex = _this.data('unique_index'),
                        arrows = $('svg.arrows'),
                        arrBegin = arrows.find('path.arrow[arr-begin="'+uniqueIndex+'"]');

                    if (attrName =='and' || attrName =='condition' && arrBegin.length) {
                        var values = [],
                            row = parseInt(_this.attr('gridrow')),
                            $path = arrBegin.filter('[modifier], [branch-end="true"]').not('[branch-end=main]');

                        $.each($path, function (){
                            var d = $(this).attr('d').split(' '),
                                d5 = parseInt(d[5]),
                                d2 = parseInt(d[2]);

                            if (d5 > d2) {
                                values.push(d5 - d2);
                            }
                        });

                        if (values.length) {
                            values = Math.max.apply(null, values);
                            values = (values < 100) ? 1 : Math.floor(values/100) + 1;

                            dynamicRow = row + values;
                        }
                    }

                    if (maxRow < dynamicRow) {
                        maxRow = dynamicRow;
                    }

                    if (maxRow < _this.attr('gridrow')) {
                        maxRow = parseInt(_this.attr('gridrow'));
                    }

                }).promise().done(function(){
                    $(this).closest('.bpm_unit').attr('rows', maxRow+1+'');
                });
            });

            var endOperator = bpmUnit.find('.bpm_operator[data-name="end"]');

            if (endOperator.length) {
                var area,
                    workSpace = $('#main-content').width();

                area = endOperator.offset().left+endOperator.width();

                if ( area > workSpace ) {
                    area += 15;
                if (QuickViewPanel.isOpen()) {
                    area += QuickViewPanel.getWidth();
                    }

                    $bpmBlock.addClass('offsetRight');
                    $bpmBlock.attr('style', '').width(area);
                } else {
                    $bpmBlock.css('width','100%').removeClass('offsetRight');
                }
            }

            return this;
        },

        // recountRespBlocks : function(){
        //     var bpmUnit = $('div.bpm_unit').not('.outer_unit'),
        //         container = $('#content_container');
        //     bpmUnit.each(function(){
        //         var _this = $(this);
        //         var countRows = _this.attr('rows'),
        //             bpmOperator = _this.find('.bpm_operator').not('[style*=";"]').not('.fake_operator'),
        //             maxRow = 1;
        //         (!bpmOperator.length>0) ? _this.attr('rows', '2') : '';
        //         bpmOperator.not('.and_helper').each(function(){
        //             var _this = $(this);
        //             var attrName = _this.data('name'),
        //                 uniqueIndex = _this.data('unique_index'),
        //                 arrows = $('svg.arrows'),
        //                 arrBegin = arrows.find('path.arrow[arr-begin="'+uniqueIndex+'"]');
        //
        //             if (attrName =='and' || attrName =='condition' && arrBegin.length>0) {
        //                 var andGridRow = parseInt(_this.attr('gridrow')),
        //                     modif = 0;
        //
        //                 arrBegin.filter('[modifier]').not('[branch-end=main]').each(function(){
        //                     modif = parseInt($(this).attr('modifier'))/100 + andGridRow;
        //                     if (maxRow < modif) {
        //                         maxRow = modif;
        //                     }
        //                 });
        //             }
        //
        //             if (maxRow < _this.attr('gridrow')) {
        //                 maxRow = parseInt(_this.attr('gridrow'));
        //             }
        //
        //         }).promise().done(function(){
        //             $(this).closest('.bpm_unit').attr('rows', maxRow+1+'');
        //         });
        //     });
        //     var endOperator = bpmUnit.find('.bpm_operator[data-name="end"]');
        //     if (endOperator.length>0) {
        //         endOperatorSide = endOperator.offset().left+endOperator.width();
        //         blockWidth = $('.bpm_block').width();
        //         if (endOperatorSide>$(window).width()) {
        //             container.width(endOperatorSide);
        //         } else {
        //             container.css('width','100%');
        //         }
        //     }
        //     if (false) { //($('.fake_operator').length>0) {
        //         $('.fake_operator').each(function(){
        //             if ($(this).attr('gridrow')!='NaN' && parseInt($(this).attr('gridrow'))>parseInt($(this).closest('.bpm_unit').attr('rows'))) {
        //                 $(this).closest('.bpm_unit').attr('rows',$(this).attr('gridrow')+'');
        //             }
        //         });
        //     }
        // },

        setEndBranchNumber : function(path){
            prevOpInd = path.attr('arr-begin');
            lastOpInd = prevOpInd;
            andInd = $('.bpm_operator[end-branches="'+path.attr('arr-end')+'"]').data('unique_index');
            cycle=true;
            while (cycle) {
                if (prevOpInd == andInd) {
                    cycle=false;
                    if ($('svg.arrows path.arrow[arr-end="'+lastOpInd+'"][arr-begin="'+andInd+'"]').length>0) {
                        branchN=$('svg.arrows path.arrow[arr-end="'+lastOpInd+'"][arr-begin="'+andInd+'"]').attr('branch');
                    } else {
                        branchN=$('svg.arrows path.arrow[arr-end="'+path.attr('arr-end')+'"][arr-begin="'+andInd+'"]').attr('branch');
                    }
                } else {
                    lastOpInd = prevOpInd;
                    prevOpInd = $('svg.arrows path.arrow[arr-end="'+prevOpInd+'"]').attr('arr-begin');
                    cycle=true;
                }
            }
            return branchN;
        },
        defineEndBranches : function(unique_index){
            // unique_index - index of I/OR
            var endBranchesInd, nextOp = ProcessObj.defineNextOperator(unique_index);
            for (i=0; i<100; i++) {
                if (typeof(nextOp)=='object') {
                    nextOp = nextOp[0]
                }
                pointsCounter = 1;
                if ($('svg.arrows path.arrow[arr-end="'+nextOp+'"]').length>1 && pointsCounter == 1) {
                    endBranchesInd = nextOp;
                    break;
                }
                if ($('svg.arrows path.arrow[arr-end="'+nextOp+'"]').length>1) {
                    pointsCounter--;
                }
                if ($('svg.arrows path.arrow[arr-begin="'+nextOp+'"]').length>1) {
                    pointsCounter++;
                }
                nextOp = ProcessObj.defineNextOperator(nextOp);
            }
            // endBranchesIndex - final branch operator
            return endBranchesInd;
        },
        defineNextOperator : function(unique_index){
            if (typeof(unique_index)=='object') {
                unique_index = unique_index.data('unique_index');
            }
            if ($('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').length==1) {
                nextOperatorInd = $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').attr('arr-end');
            } else if ($('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').length>1) {
                var nextOperatorInd = [];
                $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').each(function(){
                    nextOperatorInd.push($(this).attr('arr-end'));
                });
            }
            //NextOperatorInd - next operator index or array of indexes in case of branches
            return nextOperatorInd
        },
        refreshStatus : function(dataShema, parametr){
            if (ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN) {
                var dataShemaArr = (new Function("return " + dataShema + ";")());
                $.each(dataShemaArr, function () {
                    $.each(this.elements, function() {
                        if (parametr=='all') {
                            $('.element[data-type="operation"][data-unique_index="'+this.unique_index+'"]').data('status',this.status).attr('data-status',this.status+'');
                        } else {
                            if (this.unique_index==parametr) {
                                $('.element[data-type="operation"][data-unique_index="'+this.unique_index+'"]').data('status',this.status).attr('data-status',this.status+'');
                            }
                        }
                    });
                });
                $.each($('svg.arrows path.arrow'), function() {
                    var _this = $(this);
                    var thisColor = $('.element[data-type="operation"][data-unique_index="'+_this.attr('arr-begin')+'"] .bpm_body').css('background-color');
                    if (thisColor=='rgb(255, 255, 255)') {
                        thisColor = 'rgb(197, 197, 197)';
                    } else {
                        var arrowBegin = _this.attr('arr-begin'),
                            arrowEnd = _this.attr('arr-end');

                        if ($('.bpm_operator[data-unique_index="' + _this.attr('arr-begin') + '"]').is('[data-name=condition]')) {
                            $.each(JSON.parse(dataShema)[0].elements, function (key, val) {
                                if (val.unique_index == arrowBegin) {
                                    $.each(val.arrows, function (key, val) {
                                        if (val.unique_index == arrowEnd) {
                                            if (val.status == ProcessObj.PROCESS_ARROW_STATUS_UNACTIVE && ProcessObj.process_status != ProcessObj.PROCESS_B_STATUS_TERMINATED) {
                                                thisColor = 'rgb(197, 197, 197)';
                                            } else {
                                                thisColor = 'rgb(255, 124, 84)';
                                                if (ProcessObj.process_status != ProcessObj.PROCESS_B_STATUS_TERMINATED) {
                                                    _this.attr('is-active','true')
                                                }
                                            }
                                            return false;
                                        }
                                    });
                                }
                            });
                        }
                    }
                    _this.attr('stroke', thisColor);
                });

                $('svg.arrows path[is-active]').each(function () {
                    $(this).remove().clone().removeAttr('is-active').appendTo($('svg.arrows'));
                });
            }
        },

        branchesManage : function(branchesCount, _unique_index, callback){ //creating and deleting branches
            var arrows = $('svg.arrows path.arrow'),
                bpmOperator = $('.bpm_operator');
            var thisOp = bpmOperator.filter('[data-unique_index="'+_unique_index+'"]'),
                nextOp = bpmOperator.filter('[data-unique_index="'+arrows.filter('[arr-begin="'+_unique_index+'"]').attr('arr-end')+'"]');
            var branchesOld = arrows.filter('[arr-begin="'+_unique_index+'"]').length;
            if (branchesCount>branchesOld) { // add branches
                var createCount = branchesCount-branchesOld,
                    arrowBegin = arrows.filter('[arr-begin="'+_unique_index+'"]'),
                    i=0;

                BpmModel.addNewBranch = true;

                if (branchesOld==1) { // working only in first operator incertion when only one branch
                    arrowBegin.attr('branch','1');
                    barchesEndInd = arrowBegin.attr('arr-end');
                    modifier = bpmOperator.filter('[data-unique_index="'+barchesEndInd+'"]').offset().top - $('svg.arrows').offset().top -6;
                } else if (branchesOld>1) { // when branches more than one
                    barchesEndInd = thisOp.attr('end-branches');//nextOp.data('unique_index');
                    arrowBegin = arrows.filter('[arr-begin="'+_unique_index+'"][branch="'+branchesOld+'"]');
                    if (arrowBegin.attr('arr-end')==barchesEndInd) {
                        var modifier = arrowBegin.attr('modifier');
                    } else if (arrowBegin.attr('arr-end')!=barchesEndInd) {
                        beginOfBranchInd = arrowBegin.attr('arr-end');
                        BpmOperator.markOperators(beginOfBranchInd, barchesEndInd);
                        markedMass = [];
                        $('.bpm_operator[mark="marked"]').each(function(){
                            markedMass.push($(this).offset().top - $('svg.arrows').offset().top);
                            $(this).removeAttr('mark');
                        });
                        var modifier = Math.max.apply(null, markedMass)-6;
                    }
                } else {
                }
                // modifier need to update in recount
                while (createCount>i) {
                    $('svg.arrows').prepend(arrows.filter('[arr-begin="'+_unique_index+'"][branch="1"]').clone(true).attr('branch-end','true').attr('branch',branchesOld+i+1+'').attr('arr-end',barchesEndInd+'')); //.attr('modifier',modifier+''));
                    i++;
                }
            } else if (branchesCount<branchesOld) { // delete branches
                var _function = function(){
                    i = branchesOld;
                    while(i > branchesCount) {
                        var item = arrows.filter('[arr-begin="' + _unique_index + '"][branch="' + i + '"]');
                        if(item.attr('arr-end') != thisOp.attr('end-branches')){ // cheking if not empty branch
                            index = item.attr('arr-end');
                            stopIndex = thisOp.attr('end-branches');
                            BpmOperator.markOperators(index, stopIndex);
                        }
                        item.remove();
                        i--;
                    }

                    if(!thisOp.attr('end-branches')){
                        thisOp.attr('end-branches', nextOp.data('unique_index') + '');
                    }
                }

                Message.show([{'type':'confirm', 'message': Message.translate_local('Operators inside the branches will be removed') + '?'}], false, function(_this_c){
                    if($(_this_c).hasClass('yes-button')){
                        modalDialog.hide();
                        _function();
                        callback();
                    }
                }, Message.TYPE_DIALOG_CONFIRM);
                return;
            } else {
                if(!thisOp.attr('end-branches')){
                    thisOp.attr('end-branches', nextOp.data('unique_index') + '');
                }
            }
            callback && callback();
        },



        // счетчик выбраных option в select[multiple]
        getCountOptions : function(select){
            if (select.val() && select.length>0) {
                select.each(function(){
                    var multiCount = $(this).val().length,
                        chosen = Message.translate_local('Selected fields');///'Выбрано полей';
                    $(this).next().find('.filter-option.pull-left').text(chosen+': '+multiCount);
                })
            }
        },

        editOrViewProcess : function(){
            if (ProcessObj.is_bpm_view && ProcessObj.mode_change == ProcessObj.PROCESS_MODE_CHANGE_VIEW) {
                $('.bpm_def').addClass('hidden');
                $('.bpm_responsible_add').addClass('hidden');
                $('.bpm_uname > .crm-dropdown').addClass('hidden');
                $('.element[data-type="actions"] .element[data-type="mc_edit"]').removeClass('hidden');
                $('.element[data-type="actions"] .element[data-type="mc_view"]').addClass('hidden');
                $('div.bpm_operator').draggable({ disabled: true });
            } else if (ProcessObj.is_bpm_view && ProcessObj.mode_change == ProcessObj.PROCESS_MODE_CHANGE_EDIT) {
                $('.bpm_def').removeClass('hidden');
                $('.bpm_responsible_add').removeClass('hidden');
                $('.bpm_uname > .crm-dropdown').removeClass('hidden');
                $('.element[data-type="actions"] .element[data-type="mc_edit"]').addClass('hidden');
                $('.element[data-type="actions"] .element[data-type="mc_view"]').removeClass('hidden');
                $('div.bpm_operator').draggable({ disabled: false });//.draggable('enable');
            }
        },

        checkingShowParams : function(_body){
            var permition = false,
                _this = _body.parent(),
                _thisName =_this.data('name'),
                _thisStatus = _this.data('status');
            if (_thisName=='task' || _thisName=='agreetment' || _thisName=='data_record') {
                if (_thisStatus=='active' || _thisStatus=='done') {
                    if (ProcessObj.mode==ProcessObj.PROCESS_MODE_RUN) {
                        permition = true;
                    }
                }
            }
            return permition;
        }

    }

    for(var key in _public) {
        ProcessObj[key] = _public[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in ProcessObj) {
        _self[key] = ProcessObj[key];
    }

    exports.Process = Process;
    exports.ProcessObj = ProcessObj;

    exports.Arrows = Arrows;
    exports.BpmOperator = BpmOperator;
    exports.Migration = Migration;
    exports.ViewProcess = ViewProcess;
    exports.Scheme = Scheme;
}(window));
