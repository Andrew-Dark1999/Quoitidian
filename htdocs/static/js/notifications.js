;(function (exports) {
    var _private, _public, _protected, HeaderNotice,
        _self = {}; //link for instance

    _protected = {

    };
    _private = {
        events: function () {
            this._events = [
                { parent: document, selector: '.right-sidebar .search-input', event: 'keyup', func: _self.onKeyUpContextSearch},
            ]

            this.addEvents(this._events, {
                instance: this
            });

            return this;
        },
    };

    _public = {
        constructor: function () {

        },
    };

    HeaderNotice = {

        HN_ID_TASKS       : 'header_task_bar',
        HN_ID_NOTICE      : 'header_notification_bar',

        NOTICE_LINK_ACTION_CARD                      : 'card',
        NOTICE_LINK_ACTION_CARD_DELETE               : 'card_delete',
        NOTICE_LINK_ACTION_MODULE                    : 'module',
        NOTICE_LINK_ACTION_PROCESS_RUN               : 'process_run',
        NOTICE_LINK_ACTION_PROCESS_OPERATION_RUN     : 'process_operation_run',
        NOTICE_LINK_ACTION_USER_PROFILE              : 'user_profile',

        NOTICE_OBJECT_ACTIVITY   : 'activity',
        NOTICE_OBJECT_NOTICE     : 'notice',

        link_actions : {},

        id_list : [],
        date_last : {},

        interval_actions : [],


        init: function(id){
            var _this = this;

            if(!id){
                return;
            }

            //push
            _this.id_list.push(id);

            //_initScroll
            _this._initScroll(id);


            //interval_actions
            if(_this.interval_actions[id]){
                clearInterval(_this.interval_actions[id]);
            }
            _this.interval_actions[id] = setInterval(function () {
                _this._refreshHeaderNotices(id);
            }, crmParams.global.intervals.notifications);

        },

        createInstance : function(){
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);
            return _self._instance = new Obj().constructor();
        },

        getDateLast : function(id){
            return this.date_last.id;
        },

        setDateLast : function(id, date){
            this.date_last.id = date;
        },

        clearLinkAction : function(object_type){
            if($.isEmptyObject(HeaderNotice.link_actions)) return;

            var prefix = 'key_';
            if(object_type == HeaderNotice.NOTICE_OBJECT_ACTIVITY){
                prefix += 'a_';
            }
            var list_la = {};

            $.each(HeaderNotice.link_actions, function(key, value){
                if(key.indexOf(prefix) == -1){
                    list_la[key] = value;
                }
            });

            HeaderNotice.link_actions = list_la;
        },

        // HeaderNotice link action
        addLinkActions : function(link_action_list){
            if(typeof(link_action_list) == 'undefined' || !link_action_list) return;

            $.each(link_action_list, function(key, link_action){
                HeaderNotice.link_actions[key] = link_action;
            });
        },

        addContentReloadActions : function(object){
            if(typeof(object) == 'undefined' || object == false || object.length == 0) return;

            $(object).each(function(ind, obj){
                $(obj).find('.element[data-type="link_action_key"]').each(function(i, ul){
                    content_vars = $(ul).textHeaderNo();
                    if(content_vars == false) return true;
                    content_vars = JSON.parse(content_vars);
                    instanceGlobal.contentReload.addContentVars(content_vars);
                });
            });
        },


        getLinkAction : function(action_key){
            return HeaderNotice.link_actions[action_key];
        },

        getHeaderNoticeObject : function(id){
            return $('#' + id);
        },


        refreshAllHeaderNotices: function(){
            if(this.id_list) {
                $.each(this.id_list, function(key, id){
                    HeaderNotice._refreshHeaderNotices(id);
                })
            }
        },

        _refreshHeaderNotices: function(id){
            var neader_notice = this.getHeaderNoticeObject(id);

            if(!id || neader_notice.hasClass('open')){
                return;
            }

            var active_notice_model = new HeaderNotice.activeNoticeClass();
            active_notice_model
                .setAction(HeaderNotice.activeNotice.ACTION_REFRESH_ALL)
                .setId(id)
                .run();
        },


        _initScroll: function(id){
            $(document).on('click', '#' + id + ' a[class="dropdown-toggle"]', function () {

                if(!$('#'+  id).hasClass('open')){
                    return;
                }

                var node_scroll = $(this).parent().find('.scroll');

                var nice_scroll = node_scroll.getNiceScroll();
                if(nice_scroll) {
                    nice_scroll.remove();
                }

                var nodes = $.grep(node_scroll.find('li'), function(el) {
                    return $(el).hasClass('temp') ? ($(el).remove(), false) : true;
                });

                var count_show = 5;
                if (nodes.length > count_show) {

                    var iterationList = 0;
                    var heightNotice = 0;
                    var offset = 0;

                    $(nodes).each(function () {
                        if (iterationList++ < count_show) {
                            heightNotice += $(this).height();
                            if (iterationList < count_show) {
                                offset += parseInt($(this).css('margin-bottom'));
                            }
                        }
                    });

                    node_scroll.height(heightNotice + offset);

                    if (heightNotice + offset  + 116 > $(window).height()) {
                        node_scroll.height($(window).height() - 126);
                    }

                    var scroll = niceScrollCreate(node_scroll);
                    scroll.setScrollTop(0);
                    scroll.scrollend(function () {
                        setTimeout(function () {
                            if (parseInt(scroll.scrollvaluemax) == parseInt(scroll.scroll.y)) {
                                var active_notice_model = new HeaderNotice.activeNoticeClass();
                                active_notice_model
                                    .setAction(HeaderNotice.activeNotice.ACTION_GET_NEXT)
                                    .setId(id)
                                    .run();
                            }
                        }, 100);
                    });
                    setTimeout(function () {
                        $dropdown = $('.dropdown');
                        $dropdown.find('.dropdown-menu .nicescroll-rails div').off('mousedown').on('mousedown', function(){
                            $thisdropdown = $(this).closest('.dropdown');
                            $('#container').off('mouseup').on('mouseup', function (e) {
                                if (!$thisdropdown.is(e.target)
                                    && $('.open').has(e.target).length === 0 ||
                                    !$thisdropdown.is(e.target)
                                    && $('.open').has(e.target).length === 0 ) {
                                    $thisdropdown.addClass('opened');
                                    setTimeout(function(){
                                        $('.opened').addClass('open').removeClass('opened')
                                    }, 300);
                                }
                                $('#container').off('mouseup');
                            });
                        });
                    }, 100);
                }
            });
        },

        setReadNotice : function(_this){
            if($(_this).closest('.element[data-type="notice"]').hasClass('new') == false){
                return;
            }

            var active_notice_model = new HeaderNotice.activeNoticeClass();
            active_notice_model
                .setAction(HeaderNotice.activeNotice.ACTION_SET_MARK_VIEW)
                .setThis(_this)
                .init()
                .run();
        },


        setReadAll : function(){
            var active_notice_model = new HeaderNotice.activeNoticeClass();
            active_notice_model
                .setAction(HeaderNotice.activeNotice.ACTION_SET_MARK_VIEW_ALL)
                .setId(this.HN_ID_NOTICE)
                .run();
        },

        activeNoticeClass : function(){
            for(var key in HeaderNotice.activeNotice) {
                this[key] = HeaderNotice.activeNotice[key];
            }
        },


        activeNotice : {

            ACTION_LOAD_DEFAULT :       1,
            ACTION_REFRESH_ALL :        2,
            ACTION_SET_MARK_VIEW :      3,
            ACTION_SET_MARK_VIEW_ALL :  4,
            ACTION_GET_NEXT :           5,

            _id : null,
            _action : null,
            _this : null,
            _error : false,
            _ajax_result : null,

            init : function(){
                if(this._error) return this;

                this.prepareId();

                return this;
            },

            setError : function(){
                this._error = true;
                return this;
            },

            setAction : function(action){
                this._action = action;
                return this;
            },

            setThis : function(_this){
                if(typeof _this == 'unidefined' || !_this) return this.setError();

                this._this = _this;
                return this;
            },

            prepareId : function(){
                if(this._this == false) return this;

                var id = $(this._this).closest('.element[data-type="header_notice"]').attr('id')

                if(typeof id == 'unidefined' || !id) return this.setError();

                this._id = id;
                return this;
            },

            setId : function(id){
                this._id = id;
                return this;
            },


            run : function(){
                if(this._error) return this;

                var ajax_data = this.getAjaxData();

                this.runAjax(ajax_data);

                return this;
            },


            //getAjaxData
            getAjaxData : function(){
                var vars = {};

                switch(this._action){
                    case this.ACTION_LOAD_DEFAULT :
                        vars.limit = 20;
                        vars.get_date_last = true;
                        break;
                    case this.ACTION_REFRESH_ALL :
                        vars.date_last = HeaderNotice.getDateLast(this._id);
                        vars.limit = this.getLimit();
                        vars.get_new = true;
                        vars.get_date_last = true;
                        break;

                    case this.ACTION_SET_MARK_VIEW :
                        vars.history_id = this.getHistoryId();

                    case this.ACTION_SET_MARK_VIEW_ALL :
                        vars.date_last = HeaderNotice.getDateLast(this._id);
                        vars.limit = this.getLimit();
                        vars.get_new = false;
                        break;

                    case this.ACTION_GET_NEXT :
                        vars.date_last = HeaderNotice.getDateLast(this._id);
                        vars.limit = this.getLimit();
                        vars.get_new = false;
                        vars.limit_append = true;
                        break;
                }


                return {
                    'id' : this._id,
                    'vars' : vars
                };
            },

            //runAjax
            runAjax : function(ajax_data){
                var _this = this;
                if(this._error) return this;

                var _url = this.getUrl();

                AjaxObj
                    .createInstance()
                    .setAsync(true)
                    .setData(ajax_data)
                    .setUrl(_url)
                    .setType('post')
                    .setCallBackSuccess(function(data){
                        _this._ajax_result = data;
                        _this.runAfterAjaxSuccess()
                    })
                    .send();
            },

            //runAfterAjaxSuccess
            runAfterAjaxSuccess : function(){
                if(this._error){
                    return this;
                }

                this.refreshDateLast();
                this.refreshNoticeHtml();
                this.refreshCounts();
                this.refreshOtherElements();
            },

            //refreshDateLast
            refreshDateLast : function(){
                switch(this._action){
                    case this.ACTION_LOAD_DEFAULT :
                    case this.ACTION_REFRESH_ALL :
                        HeaderNotice.setDateLast(this._id, this._ajax_result.date_last);
                        break;
                }
            },

            //refreshNoticeHtml
            refreshNoticeHtml: function(){
                var _this = this;

                switch(this._action){
                    case this.ACTION_LOAD_DEFAULT :
                    case this.ACTION_REFRESH_ALL :
                        this.removeHotices();
                }

                var notice_html_list = this._ajax_result.notice_html_list;
                if(!notice_html_list) return;

                var object = HeaderNotice.getHeaderNoticeObject(this._id);
                var notice_block = object.find('.element[data-type="notice_block"]');
                var notices = notice_block.find('.element[data-type="notice"]');
                var nl_count = notices.length;


                if(this._action == this.ACTION_GET_NEXT && this._ajax_result.updated == false){
                    nl_count = false;
                }

                $.each(notice_html_list, function(i, html_list){
                    if(nl_count && (nl_count) > i){
                        _this.noticeHtmlReplace(notices, html_list, i);
                    } else {
                        _this.noticeHtmlAppend(notice_block, html_list);
                    }

                    _this.addLinkActionParams(html_list);
                })

                if(notice_html_list.length < notices.length){
                    switch(this._action){
                        case this.ACTION_LOAD_DEFAULT :
                        case this.ACTION_REFRESH_ALL :
                        case this.ACTION_SET_MARK_VIEW :
                        case this.ACTION_SET_MARK_VIEW_ALL :
                        case this.ACTION_GET_NEXT :
                            if(this._action == this.ACTION_GET_NEXT && this._ajax_result.updated == false){
                                break;
                            }
                            var i = notice_html_list.length - 1;
                            notice_block.find('.element[data-type="notice"]:gt('+i+')').remove();

                            break;
                    }
                }
            },



            noticeHtmlReplace : function(notices, html_list, index){
                notices.eq(index).after(html_list.html).remove();
            },

            noticeHtmlAppend : function(notice_block, html_list){
                notice_block.append(html_list.html);
            },

            addLinkActionParams : function(html_list){
                if(typeof html_list['link_actions'] != 'undefined' && html_list['link_actions']){
                    HeaderNotice.addLinkActions(html_list['link_actions']);
                }

                if(typeof html_list['content_reload'] != 'undefined' && html_list['content_reload']){
                    instanceGlobal.contentReload.addContentVars(html_list['content_reload']);
                }
            },


            removeHotices : function(){
                var notices = HeaderNotice.getHeaderNoticeObject(this._id).find('.element[data-type="notice"]');

                notices.remove();
            },


            refreshCounts : function(){
                if(typeof this._ajax_result.counts == 'undefined' || !this._ajax_result.counts) return;

                var counts = this._ajax_result.counts;
                var object = HeaderNotice.getHeaderNoticeObject(this._id);

                object.find('.bg-warning').text(counts['new']);
                object.find('.count_total').text(counts['total']);
            },

            refreshOtherElements : function(){
                var counts = this._ajax_result.counts;
                var object = HeaderNotice.getHeaderNoticeObject(this._id);

                var notice_set_read = object.find('.element[data-type="notice_set_read"]');
                if(counts.new){
                    notice_set_read.show();
                } else {
                    notice_set_read.hide();
                }
            },


            getUrl : function(){
                var url = '';

                switch(this._action){
                    case this.ACTION_SET_MARK_VIEW :
                        url = '/headerNotice/setMarkView';
                        break;
                    case this.ACTION_SET_MARK_VIEW_ALL :
                        url = '/headerNotice/setMarkViewAll';
                        break;
                    default :
                        url = '/headerNotice/getEntities';
                }

                return url;
            },


            getLimit : function(){
                switch(this._action){
                    case this.ACTION_REFRESH_ALL :
                    case this.ACTION_SET_MARK_VIEW :
                    case this.ACTION_SET_MARK_VIEW_ALL :
                    case this.ACTION_GET_NEXT :
                        limit = $('#' + this._id + ' .element[data-type="notice"]').length;
                        if(typeof limit != 'undeHeaderNofined' && limit){
                            return limit;
                        }
                }

                return 20;
            },


            getHistoryId : function(){
                var history_id = $(this._this).closest('.element[data-type="notice"]').data('id');
                if(typeof history_id == 'undefined' || !history_id) return;

                return history_id
            },


        },




        // onClickNavigationLink
        onClickNavigationLink : function(_this){
            var action_params, vars, _object,
                $notificationBar = $('#header_notification_bar'),
                action_key = _this.data('action_key');

            if(typeof(action_key) == 'undefined' || !action_key || action_key.length == 0) return;

            action_params = HeaderNotice.getLinkAction(action_key);

            if(typeof(action_params) == 'undefined' || !action_params || $.isEmptyObject(action_params)) return;

            if(action_params && !action_params.action) return;

            switch(action_params.action){
                //NOTICE_LINK_ACTION_CARD
                case HeaderNotice.NOTICE_LINK_ACTION_CARD :
                    vars = {
                        'selector_content_box' : '#content_container',
                        'check_expediency_switch' : true,
                        'module' : {
                            'copy_id' : action_params['copy_id'],
                            'data_id' :  action_params['data_id'],
                            'params' : {'this_template' : 'auto'}
                        }
                    }

                    if(typeof(action_params['pci']) != 'unidefined' && typeof(action_params['pdi']) != 'unidefined' && action_params['pci'] && action_params['pdi']){
                        vars.module.params['pci'] = action_params['pci'];
                        vars.module.params['pdi'] = action_params['pdi'];
                    }

                    var instanceContent = ContentReload.createInstance().clear().setVars(vars);

                    iPreloader.implements.call(instanceContent);
                    Global.getInstance().setContentReloadInstance(instanceContent);

                    instanceContent
                        .setPreloader(MainMenu.getPreloader())
                        .reDefinition()
                        .showPreloader()
                        .setCallBackComplete(function(data){
                            if(data && data.status == 'access_error'){
                                HeaderNotice.setReadNotice(_this);
                            }
                        })
                        .setCallBackSuccessComplete(function(){
                            instanceContent
                                .actionSwitchMenuByCopyId()
                                .actionHideLeftMenu();

                            Events
                                .createInstance()
                                .setType(Events.TYPE_AJAX_COMPLETE)
                                .setKey('navigation_link')
                                .setHandler(function () {
                                    instanceContent.actionShowEditView();
                                    Events.removeHandler({ key: 'navigation_link', type: Events.TYPE_AJAX_COMPLETE});
                                })
                                .run();
                        })
                        .loadModule();

                    break;

                //NOTICE_LINK_ACTION_CARD_DELETE
                case HeaderNotice.NOTICE_LINK_ACTION_CARD_DELETE :
                    var active_notice_model = new HeaderNotice.activeNoticeClass();
                    active_notice_model
                        .setAction(HeaderNotice.activeNotice.ACTION_SET_MARK_VIEW)
                        .setThis(_this)
                        .init()
                        .run();
                    break;

                //NOTICE_LINK_ACTION_MODULE
                case HeaderNotice.NOTICE_LINK_ACTION_MODULE :
                    instanceGlobal.preloaderShow(_this);
                    vars = {
                        'selector_content_box' : '#content_container',
                        'check_expediency_switch' : true,
                        'module' : {
                            'copy_id' : action_params['copy_id'],
                            'params' : {'this_template' : '0'}
                        }
                    }

                    _object = instanceGlobal.contentReload
                        .clear()
                        .setVars(vars);
                    _object.setCallBackComplete(function(data){
                        if(data && data.status == 'access_error'){
                            HeaderNotice.setReadNotice(_this);
                        }
                    });
                    _object.setCallBackSuccessComplete(function(){
                        $notificationBar.removeClass('open');

                        _object
                            .actionSwitchMenuByCopyId()
                            .actionHideLeftMenu()
                    })
                    _object.loadModule();

                    break;

                //NOTICE_LINK_ACTION_PROCESS_OPERATION_RUN
                case HeaderNotice.NOTICE_LINK_ACTION_PROCESS_OPERATION_RUN :
                    vars = {
                        'selector_content_box' : '#content_container',
                        'check_expediency_switch' : true,
                        'module' : {
                            'copy_id' : action_params['copy_id'],
                            'process_id' :  action_params['data_id'],
                            'process_mode' : 'run',
                        }
                    }

                    if(action_params['operations_unique_index']){
                        vars.module.unique_index = action_params['operations_unique_index'];
                    }

                    var instanceContent = ContentReload.createInstance().clear().setVars(vars);

                    iPreloader.implements.call(instanceContent);
                    Global.getInstance().setContentReloadInstance(instanceContent);

                    instanceContent
                        .setPreloader(MainMenu.getPreloader())
                        .reDefinition()
                        .showPreloader()
                        .setCallBackComplete(function(data){
                            if(data && data.status == 'access_error'){
                                HeaderNotice.setReadNotice(_this);
                            }
                        })
                        .setCallBackSuccessComplete(function(){
                            instanceContent
                                .actionSwitchMenuByCopyId()
                                .actionHideLeftMenu()
                                .actionShowProcessBpmOperation();
                        })
                        .loadBpmProcess();
                    // _object = instanceGlobal.contentReload
                    //     .clear()
                    //     .setVars(vars);


                    break;

                //NOTICE_LINK_ACTION_PROCESS_RUN
                case HeaderNotice.NOTICE_LINK_ACTION_PROCESS_RUN :
                    vars = {
                        'selector_content_box' : '#content_container',
                        'check_expediency_switch' : true,
                        'module' : {
                            'copy_id' : action_params['copy_id'],
                            'process_id' :  action_params['data_id'],
                            'process_mode' : 'run',
                        }
                    }

                    var instanceContent = ContentReload.createInstance().clear().setVars(vars);

                    iPreloader.implements.call(instanceContent)
                    Global.getInstance().setContentReloadInstance(instanceContent);

                    instanceContent
                        .setPreloader(MainMenu.getPreloader())
                        .reDefinition()
                        .showPreloader()
                        .setCallBackComplete(function(data){
                            if(data && data.status == 'access_error'){
                                HeaderNotice.setReadNotice(_this);
                            }
                        })
                        .setCallBackSuccessComplete(function(){
                            $notificationBar.removeClass('open');

                            instanceContent
                                .actionSwitchMenuByCopyId()
                                .actionHideLeftMenu();
                        })
                        .loadBpmProcess();

                    break;

                //NOTICE_LINK_ACTION_USER_PROFILE
                case HeaderNotice.NOTICE_LINK_ACTION_USER_PROFILE :
                    instanceGlobal.preloaderShow(_this);
                    vars = {
                        'selector_content_box' : '#content_container',
                        'url' : '/profile/?users_id='+action_params['user_id']
                    }
                    _object = instanceGlobal.contentReload
                        .clear()
                        .setVars(vars);
                    _object.setCallBackSuccessComplete(function(){
                        _object
                            .actionDeactiveElementsMenu()
                            .actionHideLeftMenu();
                    })
                    _object.loadToLink();

                    break;
            }


        }
    }

    exports.HeaderNotice = HeaderNotice;
})(window);
