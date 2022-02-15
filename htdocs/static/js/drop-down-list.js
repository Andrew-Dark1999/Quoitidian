/**
 * DropDownList
 */


;(function (exports) {
    var _private, _public, _protected, DropDownList, DropDownListObj,
        _self = null; //link for instance

    _protected = {

    };
    _private = {

    };

    _public = {

    };

    DropDownList = function(){
        for(var key in DropDownListObj) {
            this[key] = DropDownListObj[key];
        }

        this.events();
    }

    DropDownListObj = {
        GROUP_DATA_SDM_OPTION_LIST      : 'gd_sdm_option_list',
        GROUP_DATA_SM_OPTION_LIST       : 'gd_sm_option_list',
        GROUP_DATA_ACTIVITY_OPTION_LIST : 'gd_activity_selected_list',

        group_data : null,
        post_data: null,
        tableNiceScroll: null,
        old_value: '',

        _parent: null,


        _thisObject: null,

        view_type: null,
        element: null,

        setViewType: function (view_type) {
            this.view_type = view_type;

            return this;
        },
        getMaxCountFieldByView: function () {
            var count = 10;

            if (this.view_type == Constant.VIEW_TYPE_EDIT_VIEW ) {
                if (this.element.find('button[data-id*="_status"]').length) {
                    count = 8;
                }
            }

            return count;
        },
        agreeIsScroll: function ($element, $callback) {
            var $li, total = 0,
                maxCount, maxHeight;

            if ($element.length) {
                $li = $element.find('li');

                maxCount = this.getMaxCountFieldByView();

                if ($li.length > maxCount) {

                    maxHeight = modalDialog.isOpen() ? 300 : 240;
                    maxHeight = modalDialog.isOpen() && this.element.prev().is('#b_status') ? 232 : maxHeight;

                    for (var i = 0; i < $li.length; i++) {
                        total += $($li[i]).height();
                    }

                    if (maxCount == 10 && modalDialog.isOpen()) {
                        maxHeight = 240;
                    }

                    if ($callback) {
                        $callback({
                            max_count: maxCount,
                            size: $li.length,
                            max_height: maxHeight
                        });
                    }
                    return this;
                }

                $element.height('auto');
            }

            return this;
        },
        setGroupData : function(group_data){
            this.group_data = group_data;
            return this;
        },

        setParent : function (_parent) {
            this._parent = _parent;

            return this;
        },
        setElement : function (element) {
            this.element = element;

            return this;
        },
        events: function () {

            var event = ['click', '[data-type="drop_down"] .dropdown-menu tr .name'];
            $('body').off(event[0], event[1]).on('click', '[data-type="drop_down"] .dropdown-menu tr .name', function(){
                var $this = $(this);

                if (!$this.closest('[data-type="drop_down"]').is('.participants')) {
                    $this.closest('.crm-dropdown').removeClass('open');
                }
            });
        },
        prepareGroupData : function(_this){
            var reloader = $(_this).data('reloader');
            var group_data = null;

            switch (reloader){
                case 'activity_channel' :
                    group_data = DropDownListObj.GROUP_DATA_ACTIVITY_OPTION_LIST;
                    break;
                default :
                    group_data = DropDownListObj.GROUP_DATA_SDM_OPTION_LIST;
            }

            this.setGroupData(group_data);

            return this;
        },

        setPostData: function(post_data){
            this.post_data = post_data;
            return this;
        },
        createInstance : function(){
            var Obj = function(){
                for(var key in DropDownListObj){
                    this[key] = DropDownListObj[key];
                }
            }

            return new Obj().constructor();
        },
        constructor : function () {
            this.events();

            return this;
        },
        getPostData : function(){
            return this.post_data;
        },
        disabledSelected : function () {
            var id, dropDown, data, object, terms, $selected,
                _this = $(this);

            dropDown = $('.element[data-type="drop_down"] [data-type="drop_down_list"]:visible').closest('[data-type="drop_down"]');
            data = dropDown.find('.submodule-table table tr:not(.b_preloader)');
            object = dropDown.find('[data-type="drop_down_button"]');
            $selected = _this.closest('tr.sm_extension_data');

            data.removeClass('m_selected');

            if ($selected.length) {
                object = $selected;
                if (_this.closest('.process_list').length) {
                    dropDown.find('[data-name="b_responsible"]').text(_this.text());
                } else {
                    if (_this.closest('[data-type="select"]').length) {
                        return;
                    }
                    var responsible = dropDown.find('[data-type="drop_down_button"]');

                    if (responsible.length) {
                        responsible.text('');
                        var avatar = _this.find('.list-view-avatar').clone();

                        if (avatar && avatar.length) {
                            responsible.append(avatar);
                        }
                        responsible.html(responsible.html()+$.trim(_this.text()));
                        var tr = _this.closest('tr.sm_extension_data');

                        (tr.data('id')) ? responsible.data('id', tr.data('id')) : '';
                        if (tr.data('ug_id')) {
                            responsible
                                .data('ug_id', tr.data('ug_id'))
                                .attr('data-ug_id', tr.data('ug_id'));
                        };
                        (tr.data('ug_type')) ? responsible.data('ug_type', tr.data('ug_type')) : '';

                        Participant.checkAndClearSelectedItemIcon_TypeConst(_this);
                    }
                }

                var base = _this.closest('.element[data-type="drop_down"]');
                if (base.find('.element[data-type="m-link"]').length) {
                    Global.createLinkByEV(base.closest('.edit-view.in'), base);
                };

                if (_this.closest('.edit-view.in').length) {
                    _this.closest('.column.b_error').removeClass('b_error').find('.errorMessage').remove();
                };
            };

            id = object.data('id') || object.data('ug_id');
            terms = (object.data('ug_type')) ? '[data-ug_type="'+object.data('ug_type')+'"]' : '';

            data.filter('[data-id="' + id + '"]'+terms).addClass('m_selected');
            data.filter('[data-ug_id="' + id + '"]'+terms).addClass('m_selected');

            Global.addOperationInSDM(dropDown.find('button'));
        },
        showLoadData : function(){
            // var submoduleTable = $('.element[data-type="drop_down_list"]').filter(
            //     function(){
            //         return ($(this).is(':visible')) ? this : null;
            //     }).find('.submodule-table');
            //
            // submoduleTable.find('.table tr').remove();
            //
            // var cloneData = submoduleTable.closest('[data-type="drop_down"]').find('.b-clone-data table');
            // cloneData.find('tr.m_selected').removeClass('m_selected');
            // submoduleTable.find('tbody').append(cloneData.clone().find('tr'));
            // submoduleTable.prev().find('.submodule-search').val(''); // clear text field;
            //
            // return this;
        },
        createCloneData : function (source, _this) {
            var items,
                place = _this.find('.b-clone-data');

            if (place.length && !place.find('.table').length) {
                items = source.clone();
                items.find('tr').show();
                place.append(items);
            }
        },
        addClickToRow : function (submoduleTable) {
            var time,
                isFilter = submoduleTable.closest('.filter-block').length ? true : false;

            if (!$('.submodule-view.sm_extension_relate_submodule').length && !isFilter) {
                if (submoduleTable.closest('.filter-block').length) {
                    submoduleTable.find('.table tr').off('click').on('click', this.disabledSelected);
                } else submoduleTable.find('.table tr .name').off('click').on('click', this.disabledSelected);

                // time = setTimeout(function () {
                //     clearTimeout(time);
                //     var $dropDown = $('.element[data-type="drop_down"] [data-type="drop_down_list"]:visible').closest('[data-type="drop_down"]');
                //     Global.addOperationInSDM($dropDown.find('button'));
                // }, 300);
            }
        },
        handler: function () {
            var _this = this;

            $('.element[data-type="drop_down_list"]:visible').on('click', function (e) {
                if ($('.submodule-view.sm_extension_relate_submodule').length
                    || $(e.target).closest('.participant-choice').length
                    || $(e.target).hasClass('remove')
                    || ($(e.target).hasClass('fa') && $(e.target).closest('.add').length)
                    || $(e.target).closest('.btn-group').length
                ) {
                    if ($(e.target).parent().is('.add') && $(e.target).closest('.participants').length) {
                        return true; // continue to top events
                    }
                    return;
                };

                var isFilter = $(e.target).closest('.filter-block').length ? true : false;

                if ($(e.target).is('[data-type="add-channel"]') || $(e.target).is('[data-type="add-auto"]')) {
                    return;
                }

                if ($(e.target).closest('[data-type="drop_down"]').length) {
                    if ($(e.target).is('.name')
                        || ($(e.target).is('td') && isFilter)
                        || $(e.target).closest('.name').length) {
                        // _this.showLoadData().disabledSelected();
                        _this.disabledSelected();
                        //e.preventDefault();
                        if ($(e.target).closest('.process_list').length) {
                            $(e.target).closest('.open').removeClass('open');
                        }
                    } else {
                        if (
                            $(e.target).closest('[data-type="drop_down_list"]').length ||
                            $(e.target).is('[data-type="add-channel"]') ||
                            $(e.target).is('[data-type="add-auto"]')
                        ) {
                            return false;
                        }
                    }
                }
            });
        },

        addAutoRecord : function ($search) {
            var value = $search.val(),
                $ddl = $search.closest('.element[data-type="drop_down_list"]'),
                $linkBlock = $ddl.find('>.link'),
                $title = $linkBlock.find('.title'),
                $link = $linkBlock.find('.element[data-type="add-auto"]');

            if(!$search.closest('.edit-view').length || $search.closest('.edit-view[data-copy_id="'+ProcessObj.copy_id+'"]').length) return;

            //Add link for fast of create task
            if (value.length) {
                $link.removeClass('hide');
                if ($title.length) {
                    $linkBlock.find('.title').text(value);
                }
            } else {
                $link.addClass('hide');
            }
        },

        run : function(){
            var submoduleTable, typingTimer, sendTimer, loadTimer,
                this_object = this,
                search = this._parent.find('.submodule-search'),
                participants = this._parent.is('.participants'),
                labelOfLoad = false;

            var _this = this._parent;
            this._thisObject = this._parent;
            this.handler();

            if (participants) {
                submoduleTable = _this.find('[data-type=select]').not('.hide').find('.element[data-type="drop_down_list"] .submodule-table');
                search = _this.find('[data-type="select"]:not(.hide) .submodule-search');

                $.each(_this.find('.participant'), function(){
                    var $this = $(this);

                    if (!$this.find('.b-clone-data').length) {
                        $this.append('<div class="b-clone-data hide"></div>');
                    }
                })
            } else {
                submoduleTable = _this.find('.element[data-type="drop_down_list"] .submodule-table');
            }

            _this.find('.submodule-table .table tr.b_preloader').remove();

            if (_this.find('.submodule-table .table tr').length) {
                _this.addClass('b_searchBy');
            }
            if (_this.find('.list-view-avatar').length) {
                _this.addClass('b-search-with_avatar');
            }

            if (!participants && !_this.find('.b-clone-data').length) {
                _this.append('<div class="b-clone-data hide"></div>');
            };

            this_object.tableNiceScroll = niceScrollCreate(submoduleTable);
            submoduleTable.getNiceScroll().resize();

            var analizeOfSearch = function(value){
                    var button =  _this.find('[data-type="drop_down_button"]');
                    var result,
                        id = button.data('id') || button.data('ug_id');
                    list = _this.find('.element[data-type="drop_down_list"] .submodule-table table tr').show();

                    value = $.trim(value).replace(/ +/g, ' ').toLowerCase();
                    result = list.find('.name').filter(function(){
                        var result = null,
                            _this = $(this);
                        var text = _this.text().replace(/\s+/g, ' ').toLowerCase();

                        if (!~text.indexOf(value)) {
                            _this.closest('tr').hide();
                        } else {
                            result = $(this).closest('table').find('tr');
                            result.filter('[data-id="'+ id +'"]').addClass('m_selected');
                            result.filter('[data-ug_id="'+ id +'"]').addClass('m_selected');
                        };

                        return result;
                    }).map(function(){
                        return $(this)
                    }).get();

                    if(!result.length && !value.length){
                        list.show();
                    }
                },
                initSelectActionLinks = function(element){
                    element = element || $('.element[data-type="drop_down_list"]');
                    element.find('>.link a').addClass('hide');
                },
                showLoadData = function(element){
                    element = element || $('.element[data-type="drop_down_list"]');

                    submoduleTable = element.filter(
                        function(){
                            return ($(this).is(':visible')) ? this : null;
                        }).find('.submodule-table');

                    submoduleTable.find('.table tr').remove();

                    var data = submoduleTable.closest('[data-type="drop_down"]');

                    if (data.is('.participants')) {
                        data = submoduleTable.closest('.participant')
                    }

                    submoduleTable.find('tbody').append(data.find('.b-clone-data table').clone().find('tr'));
                    submoduleTable.removeClass('set-preloader init-preloader');
                    this_object.tableNiceScroll = niceScrollCreate(submoduleTable);
                    this_object.tableNiceScroll.scrollend(scrollEnd);
                    this_object.old_value = '';
                    this_object.disabledSelected();

                    initSelectActionLinks(element);
                },
                callbackData = function(data){
                    $('.showPreloader').removeClass('showPreloader set-preloader searching preloaderInner');
                    submoduleTable.find('.b_preloader').remove();

                    if (!data.status) {
                        return;
                    }
                    submoduleTable.getNiceScroll().remove();

                    var table = submoduleTable.find('table tbody');

                    if(table.length){
                        table.find('tr').remove();
                    }

                    if(data.html_option){
                        $(data.html_option).appendTo(table);
                        var elements = '<tr class="sm_extension_data b_preloader"><td><div class="b-spinner"><div class="loader"></div></div></td></tr>';

                        this_object.tableNiceScroll = niceScrollCreate(submoduleTable);
                        this_object.tableNiceScroll.setScrollTop(0);
                        this_object.tableNiceScroll.scrollend(scrollEnd);
                        this_object.addClickToRow(submoduleTable);
                        this_object.disabledSelected();

                        if (data.there_is_data) {
                            submoduleTable.find('table tbody').append(elements);
                            submoduleTable.closest('[data-type="drop_down_list"]').data('there_is_data_loading', 1); // if [data-there_is_data_loading] exist that loading
                        }
                    }

                    this_object.addAutoRecord(search);
                },
                callbackDataLoading = function(data) {
                    submoduleTable.find('tr.b_preloader').remove();

                    if (!data.status) {
                        return;
                    }

                    var list = _this.find('.element[data-type="drop_down_list"]');

                    $(data.html_option).appendTo(submoduleTable.find('table.table tbody'));
                    list.removeData('there_is_data_loading');

                    if(!data.there_is_data || !data.html_option) {
                        var elements = '<tr class="sm_extension_data b_preloader"><td><div class="b-spinner"><div class="loader"></div></div></td></tr>';

                        if (!search.val().length) {
                            list.data('there_is_data', 0);
                        } else submoduleTable.find('table tbody').append(elements);
                    }
                    if (data.there_is_data && search.val().length) {
                        list.data('there_is_data_loading',1); // if [data-there_is_data_loading] exist that loading
                    };

                    $(data.html_option).length && !submoduleTable.closest('[data-type="drop_down"]').find('.submodule-search').val().length ? _this.find('.b-clone-data .table tbody').append($(data.html_option)) : '';

                    this_object.addClickToRow(submoduleTable);
                    this_object.tableNiceScroll.scrollend(scrollEnd);
                    this_object.disabledSelected();
                },
                addPreloader = function () {
                    var spinner = Global.spinner;

                    submoduleTable.addClass('showPreloader searching set-preloader init-preloader small-preloading');

                    if (!submoduleTable.find('table tr').length) {
                        submoduleTable.addClass('preloaderInner');
                    }
                    if (!submoduleTable.find(spinner.selector).length) {
                        submoduleTable.append(spinner.clone().first());
                    }
                },
                scrollEnd = function () {
                    setTimeout(function(){
                        var list = _this.find('.element[data-type="drop_down_list"]');

                        if (parseInt(this_object.tableNiceScroll.scrollvaluemax) == parseInt(this_object.tableNiceScroll.scroll.y) && list.data('there_is_data') && !labelOfLoad) {
                            if (!list.data('there_is_data_loading') && _this.find('.submodule-search').val().length) { return;}
                            submoduleTable.addClass('showPreloader');

                            var placeData = submoduleTable.find('table tbody'),
                                content = '<tr class="sm_extension_data b_preloader set-preloader init-preloader"><td colspan="' + placeData.find('tr:first td').length + '"><div class="b-spinner"><div class="loader"></div></div></td></tr>';

                            placeData.find('tr.sm_extension_data.b_preloader').remove();
                            placeData.append(content);

                            this_object.tableNiceScroll.setScrollTop(submoduleTable.find('table').height());
                            labelOfLoad = true;
                            clearTimeout(loadTimer);
                            loadTimer = setTimeout(function(){
                                var data = this_object.getDataForNextOptionList(_this, false);
                                this_object.loadFromServer(data, callbackDataLoading);
                                labelOfLoad = false;
                            }, 900);
                        };
                    }, 100);
                },
                beginSearch = function($this){
                    var value = $this.val();

                    submoduleTable.getNiceScroll().remove();

                    if(this_object.old_value != value && value.length && _this.find('.element[data-type="drop_down_list"]').data('there_is_data')){
                        submoduleTable.find('table tr').closest(submoduleTable).addClass('showPreloader');
                        var data = this_object.getDataForNextOptionList(_this, true);

                        addPreloader();
                        this_object.old_value = value;
                        sendTimer = setTimeout(function(){
                            this_object.loadFromServer(data, callbackData);
                        }, 400);
                    } else {
                        (value.length) ? analizeOfSearch(value) : showLoadData($this.closest('[data-type="drop_down_list"]'));

                        $('.showPreloader').removeClass('showPreloader');
                        submoduleTable.find('.b_preloader').remove();

                        if(submoduleTable.find('table').find('tr').length > 5){
                            this_object.tableNiceScroll = niceScrollCreate(submoduleTable);
                        }
                        this_object.addAutoRecord($this);
                    }

                },
                searchWithFilter = function($this) {
                    var value = $this.val();

                    submoduleTable.getNiceScroll().remove();

                    submoduleTable.find('table tr').closest(submoduleTable).addClass('showPreloader');
                    var data = this_object.getDataForNextOptionList(_this, true);

                    addPreloader();
                    this_object.old_value = value;
                    sendTimer = setTimeout(function(){
                        this_object.loadFromServer(data, callbackData);
                    }, 400);
                };

            if(_this.find('.element[data-type="drop_down_list"]').length){
                var table;

                this_object.addClickToRow(submoduleTable);
                search.off('keyup');
                search.on('keyup', function(){
                    var $this = $(this);

                    clearTimeout(sendTimer);
                    typingTimer && clearTimeout(typingTimer);

                    if(_this.find('.element[data-type="drop_down_list"]').data('there_is_data')){
                        typingTimer = setTimeout(function(){
                            clearTimeout(sendTimer);
                            clearTimeout(typingTimer);
                            beginSearch($this);
                        }, 800);
                    } else {
                        beginSearch($this);
                    }
                });

                if (participants) {
                    $.each(_this.find('.participant'), function(){
                        var $this = $(this);

                        table = $this.not('.hide').find('.element[data-type="drop_down_list"] .submodule-table  table.table');
                        table.find('tr').show();

                        this_object.createCloneData(table.clone(),$this);
                    })

                } else {
                    table = _this.find('.element[data-type="drop_down_list"] .submodule-table table.table');
                    this_object.createCloneData(table.clone(),_this.closest('[data-type=drop_down]'));
                }

                this_object.disabledSelected();

                if(this_object.tableNiceScroll.name && typeof this_object.tableNiceScroll.scrollend == 'function') {
                    this_object.tableNiceScroll.scrollend(scrollEnd);
                }

                var correctSerchPadding = function(submserch){
                    var width, filtrId, countFilters, labelOfCounts,
                        sumwidth=0,
                        maxFilters = 3,
                        filters = submserch.closest('.search-section').find('.filter-install').not('.hidden');
                    submserch.closest('.search-section').removeClass('shorting-fltr');

                    if (filters.length > maxFilters) {
                        filtrId = filters.last().prev().data('filter_id');
                        submserch.closest('.search-section').find('.submodule-filter-btn-set[data-id="'+filtrId+'"]').removeClass('selected');
                        filters.last().prev().remove();
                        labelOfCounts = true;
                    }
                    filters.each(function(i){
                        width = filters.width() + parseInt(filters.css('padding-left'))+parseInt(filters.css('padding-right'));

                        sumwidth=sumwidth+width+20;
                        if(filters.length == i+1) {
                            if (sumwidth<420) {
                                submserch.css('padding-left',sumwidth+'px');
                            } else {
                                countFilters = labelOfCounts ? maxFilters : filters.length;
                                submserch.closest('.search-section').addClass('shorting-fltr');
                                sumwidth = countFilters * 50 + (countFilters*2)*5 + 13;
                                submserch.css({
                                    'padding-left': sumwidth
                                });
                            }
                        }
                    });
                    if (!filters.length) {
                        submserch.css('padding-left', 9);
                    }
                }

                $(document).off('click', '.submodule-filters button');
                $(document).on('click', '.submodule-filters button', function(){
                    var fmenu = $(this).closest('.submodule-filters').find('.filter-menu > div');
                    if (fmenu.find('li').length>10) {
                        if (fmenu.closest('.filter-menu').find('.nicescroll-rails').length) {
                            fmenu.getNiceScroll().remove();
                        }
                        fmenu.height(230).niceScroll({
                            cursorcolor: "#1FB5AD",
                            cursorborder: "0px solid #fff",
                            cursorborderradius: "0px",
                            cursorwidth: "3px",
                            railalign: 'right',
                            preservenativescrolling: false,
                            autohidemode: false
                        });
                    }
                });

                $(document).off('click', '.submodule-filter-btn-set');
                $(document).on('click', '.submodule-filter-btn-set', function(){
                    var $filter,
                        $this = $(this),
                        $section = $this.closest('.search-section');

                    if ($this.is('.selected')) {
                        var fid = $this.data('id');
                        $this.closest('.search-section').find('.filter-install[data-filter_id="'+fid+'"]').remove();
                    } else {
                        var fid = $this.data('id'),
                            fname = $this.data('name'),
                            ftext = $this.text(),
                            fclone = $this.closest('.submodule-filters').find('.filter-install.hidden').clone();

                        fclone.removeClass('hidden')
                            .data('name',fname).data('filter_id',fid)
                            .attr('data-name',fname).attr('data-filter_id',fid)
                            .find('span').text(ftext);
                        $section.append(fclone);
                    }
                    $this.toggleClass('selected').closest('.crm-dropdown');
                    submserch = $section.find('input.submodule-search');
                    correctSerchPadding(submserch);
                    $this = $section.find('.submodule-search');
                    searchWithFilter($this);

                    $filter = $section.find('.filter-install').not('.hidden');
                    $filter.off('mouseenter mouseleave');
                    $filter.hover(
                        function(){
                            var summWidth = 0,
                                $this = $(this),
                                $section = $this.closest('.search-section'),
                                $input = $section.find('.submodule-search');

                            if ($section.hasClass('shorting-fltr')) {
                                $section.find('.filter-install').not('.hidden').map(function () {
                                    var $this =  $(this).css('box-sizing', 'content-box');
                                    summWidth += $this.width() + parseInt($this.css('margin-left')) + parseInt($this.css('margin-right'));
                                    $this.css('box-sizing', 'border-box');
                                })
                                $input.css({
                                    'padding-left': summWidth +16
                                });
                            } else {
                                $section.find('.filter-install').not('.hidden').map(function () {
                                    var $this =  $(this);

                                    summWidth = summWidth + ($this.width() + parseInt($this.css('padding-left'))+parseInt($this.css('padding-right'))+20);
                                })
                                $input.css({
                                    'padding-left': summWidth
                                });
                            }


                        },
                        function () {
                            var $input = $(this).closest('.search-section').find('input.submodule-search');
                            correctSerchPadding($input);
                        });

                });

                $(document).off('click', '.search-section .filter-btn-take-off');
                $(document).on('click', '.search-section .filter-btn-take-off', function(){
                    var $this = $(this),
                        fid = $this.closest('.filter-install').data('filter_id'),
                        $section = $this.closest('.search-section');

                    $section.find('.filter-menu li a[data-id="'+fid+'"]').removeClass('selected');
                    submserch = $section.find('input.submodule-search');
                    $this.closest('.filter-install').remove();
                    correctSerchPadding(submserch);
                    searchWithFilter($section.find('.submodule-search'));
                });



                $(document).off('click', '.edit-view .element[data-type="drop_down"] .link .element[data-type="add-auto"]');
                $(document).on('click', '.edit-view .element[data-type="drop_down"] .link .element[data-type="add-auto"]', function(){
                    DropDownListObj.actions.addAutoEntity(this);
                });


            }
            return this;
        },

        getDataForNextOptionList : function(_this, offset_zero){
            switch(this.group_data){
                case this.GROUP_DATA_SDM_OPTION_LIST:
                case this.GROUP_DATA_ACTIVITY_OPTION_LIST:
                    return this.getDataForNextOptionSDMGeneralList(_this, offset_zero);
                case this.GROUP_DATA_SM_OPTION_LIST:
                    return this.getDataForNextOptionSMGeneralList(_this, offset_zero);
            }
        },


        getDataForNextOptionSDMGeneralList : function(_this, offset_zero){
            var _editView = _this.closest('.edit-view');
            var _processParam = _this.closest('.modal .element[data-module="process"][data-type="params"]');

            var this_template = '0';
            var copy_id = _this.find('.element[data-type="drop_down_button"]').data('parent_copy_id');
            var data_id = null;

            // editView
            if(_editView.length){
                var primary_pci = _this.closest('.element[data-type="drop_down"]').find('.element_relate').data('relate_copy_id'),
                    primary_pdi = _this.closest('.element[data-type="drop_down"]').find('.element_relate').data('id'),
                    pci = _editView.data('pci'),
                    parent_copy_id = _editView.data('parent_copy_id'),
                    pdi = _editView.data('pdi'),
                    parent_data_id = _editView.data('parent_data_id'),
                    data_id = _editView.data('id');

                // Process Params
            } else if(_processParam.length){
                var primary_pci = '',
                    primary_pdi = '',
                    pci = '',
                    parent_copy_id = '',
                    pdi = '',
                    parent_data_id = '';

                // ListView, ProcessView
            } else {
                var object = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension');
                if(object){
                    var primary_pci = _this.closest('.sm_extension_data.editing').find('.data_edit .element_relate').data('relate_copy_id'),
                        primary_pdi = _this.closest('.sm_extension_data.editing').find('.data_edit .element_relate').data('id'),
                        pci = object.data('parent_copy_id'),
                        parent_copy_id = '',
                        pdi = object.data('parent_data_id'),
                        parent_data_id = '';

                    var sm_extension = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .edit-view, .bpm_block.sm_extension');
                    if(typeof (sm_extension) != 'undefined' && sm_extension){
                        this_template = sm_extension.data('this_template');
                    }
                }
            }

            var field_name;
            if(_this.find('.element[data-type="drop_down_button"]').is('[name]')){
                var name = _this.find('.element[data-type="drop_down_button"]').attr('name');
                field_name = name.substring(name.indexOf('[') + 1, name.lastIndexOf(']'));
            }
            var persona_list = _this.find('.element[data-type="drop_down_list"] .submodule-table');
            var search = _this.find('.element[data-type="drop_down_list"] .submodule-search').val();


            var data = {
                'vars': {
                    'copy_id': copy_id, // copy_id модуля
                    'data_id': data_id,
                    'field_name': field_name,  // field_name - название поля СДМ
                    'relate_module_copy_id': _this.find('.element[data-type="drop_down_list"]').data('relate_copy_id'), // copy_id связаного модуля
                    'this_template': this_template,

                    'parent_copy_id': {
                        'pci': pci,
                        'parent_copy_id': parent_copy_id,
                    },
                    'parent_data_id': {
                        'pdi': pdi,
                        'parent_data_id': parent_data_id,
                    },
                    'primary_entities': {
                        'primary_pci': primary_pci,
                        'primary_pdi': primary_pdi,
                    },
                },
                'active_group_data': this.group_data,
                'search': search,   // строка поиска
                'limit': '20',  // количество строк для выгрузки
                'offset': offset_zero ? 0 : persona_list.find('table.list-table').not('[data-clone]').find('tr.sm_extension_data').length-1, // с какой строки вигрузить данные. 0 - первая. Потом last_row+limit...
            }

            return data;
        },



        getDataForNextOptionSMGeneralList : function(_this, offset_zero, post_data){
            var persona_list = _this.find('.element[data-type="drop_down_list"] .submodule-table');
            var search = _this.find('.element[data-type="drop_down_list"] .submodule-search').val();

            var post_data = this.getPostData();
            if(post_data == false){
                post_data == {};
            }
            post_data['copy_id'] = $(_this).closest('.sm_extension_relate_submodule').data('copy_id');

            var data = {
                'vars': post_data,
                'active_group_data': this.group_data,
                'search': search,   // строка поиска
                'limit': '20',  // количество строк для выгрузки
                'offset': offset_zero ? 0 : persona_list.find('table.list-table tr.sm_extension_data:not(.b_preloader)').length, // с какой строки вигрузить данные. 0 - первая. Потом last_row+limit...
            }

            return data;
        },


        // отправляем  запрос
        loadFromServer: function(data, callback){
            // send to server
            var search = (data['search']) ? '?search=' + data['search'] : '';

            if ($('.submodule-filters').length) {
                var filtersarr = [];
                $('.submodule-filters').find('.selected').each(function(){
                    var filterid = $(this).data('id');
                    filtersarr.push(filterid);
                });
                data['filters']=filtersarr;
            }

            if(data.search != undefined){
                AjaxObj
                    .createInstance()
                    .setData(data)
                    .setAsync(true)
                    .setUrl('/ajax/nextDropdownOptionList' + search)
                    .setCallBackSuccess(function(data){
                        if(typeof callback == 'function'){
                            callback(data); // передает в callback готовую верстку списка
                        }
                    })
                    .send()
            }

            return this;
        },


        actions : {
            addAutoEntity : function(_this){
                var drop_down = $(_this).closest('.element[data-type="drop_down"]');
                var copy_id = drop_down.find('.element[data-type="drop_down_button"]').data('relate_copy_id');
                var module_title = drop_down.find('.element[data-type="drop_down_list"] .submodule-search').val();

                if(module_title === ''){
                    Message.show([{'type':'error', 'message': 'You must enter a title in the search bar'}], true);
                    return;
                }

                var post_data = {
                    'EditViewModel' : {
                        'module_title' :  module_title
                    }
                };

                var callback_save = function(_this, data){
                    if(data.status == 'save'){
                        var drop_down_button = $(_this).closest('.element[data-type="drop_down"]').find('.element[data-type="drop_down_button"]');
                        drop_down_button.data('id', data.id);
                        EditView.relates.reloadSDM(drop_down_button);
                        Global.addOperationInSDM();
                    } else {
                        Message.show([{'type':'error', 'message': 'Data not saved'}], true);
                    }
                };


                var function_save = function(_this, post_data, callback){
                    AjaxObj
                        .createInstance()
                        .setData(post_data)
                        .setAsync(true)
                        .setUrl(Global.urls.url_edit_view_add_by_title + '/' + copy_id)
                        .setCallBackSuccess(function(data){
                            if(typeof callback == 'function'){
                                callback(_this, data);
                            }
                        })
                        .send()
                }

                function_save(_this, post_data, callback_save);
            }
        }

    }

    exports.DropDownList = DropDownList;
    exports.DropDownListObj = DropDownListObj;
})(window);