;(function (exports) {
    var _public, History, AjaxContainers, iBackForwardHistory, iBackForwardUpdate,
        _self = null; //link for instance

    iBackForwardHistory = {
        cash: false, // статус истории
        mode_history_state: false,// статус ячейки памяти для BackForward Кеша

        implements: function () {
            for (var key in iBackForwardHistory) {
                if ($.inArray(key,['implements'])< 0) {
                    this[key] = iBackForwardHistory[key];
                }
            }

            return this;
        },
        //TODO: BackForwardCash тимчасово читаю window.backForward
        isCash: function() {
            return window.backForward; //this.cash;
        },
        setStatusCash: function (bool) {
            this.cash = bool;
            return this;
        },
        updateProperties: function () {
            return this;
        },

        /*
         для ячейки памяти BackForward Кеша
         true - буде використовуватись pushState
         false - буде використовуватись replaceState
         */
        setHistoryStateMode: function (bool) {
            this.mode_history_state = bool;

            return this;
        },
        isHistoryStateMode: function () {
            return this.mode_history_state;
        }
    }

    iBackForwardUpdate = {
        implements : function () {
            for (var key in iBackForwardUpdate) {
                if ($.inArray(key,['implement'])< 0) {
                    this[key] = iBackForwardUpdate[key];
                }
            }

            return this;
        },
        updateAfterGetCash: function () {
            this.events ? this.events() : '';

            if (this.search) {
                this.search.setTextToView(this.search.getText())
            }

            return this;
        }
    }

    _public = {
        data: null, // json даних для комірки в памяті
        mode: false, //

        constructor: function () {
            return this;
        },
        initBackForward: function (e) {
            var $content,
                globalInstance = Global.getInstance(),
                data = AjaxContainers.cash.get(location.href),
                seacrh = Url.getParam(null, Search._field);

            window.backForward = true;
            globalInstance.setStatusCash(true);

            History.history_params_list = [];
            if (StartupGuide.isStorage()) {
                var time = setTimeout(function () {
                    clearTimeout(time);
                    Url.redirect(location.pathname);
                })
                return;
            }

            $.isReady = true; // stop the ready handler

            ProcessEvents.destroy();
            instanceReports && instanceReports.remove();
            Constructor.destroy();
            ProcessView.destroy();

            //Common destroy each object
            Events.runHandler(Events.TYPE_DESTROY, {
                event: e,
                data: null
            });

            ProcessObj.mode = false;
            ProcessObj.is_bpm_view = false;

            if (!data) {
                var _data = {
                    get_full_page : 1
                }

                $('body').removeClass('guide step1 step2 step3 step4 step5');
                $('#container').addClass('set-preloader reload-page show');
                $('#modal_dialog_container').html('');
                $('.modal-backdrop.in').remove();

                $.ajax({
                    url: location.pathname + location.search,
                    data: _data,
                    type: "POST", dataType: 'json',
                    success: function(data){
                        var $context = $(data.content_html),
                            arrClazzToPreloader = [
                                'set-preloader',
                                'reload-page',
                                'show'];

                        $(document).off();
                        instanceGlobal.contentReload.preloaderClasses = arrClazzToPreloader;

                        if (data.status === 'access_error') {
                            Message.show([{'type':'error', 'message': data.messages[0].message }], true);
                            instanceGlobal.contentReload.hidePreloader();
                            return;
                        }

                        $('body').html('');
                        $.each($context, function (key, value) {
                            var $value = $(value);

                            if ($value.is('meta, link')) {
                                return true;
                            }
                            if ($value.is('body')) {

                                $('body').addClass($value.attr('class'));
                                return true;
                            }
                            $('body').append(value);
                        })

                        Global.responsiveNav();
                        Global.initSelects();
                        Global.createInstance();

                        instanceGlobal = new _Global();
                        instanceGlobal.currentInstance = instanceGlobal;
                        instanceGlobal.contentReload.preloaderClasses = arrClazzToPreloader;

                        instanceGlobal.contentReload.hidePreloader();
                        if (Base.isListView()) {
                            ListView.createInstance();
                        }

                        History.loadModal()
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                    }
                });
                return;
            }

            if (data && data.content) {
                //clear cash for guide
                $content = $(data.content).removeClass('guide step1 step2 step3 step4 step5');
                $content.find('.show-shadow').removeClass('.show-shadow');
                $content.find('.guide-panel').remove();
                $content.find('.step5_arrow, .step4_arrow_email, .step4_arrow_period, .step4_arrow_save_change, .step2_arrow').remove();

                if (instanceGlobal.currentInstance && instanceGlobal.currentInstance.remove) {
                    instanceGlobal.currentInstance.remove();
                }

                var mainContent = $content.find('[data-type="main-content"]'),
                    headerContent = $content.find('header.header');

                $('body header.header').html(headerContent.html());
                $('body [data-type="main-content"]').html(mainContent.html());
                $('html').removeClass('overflowHidden');

                Search.checkCommonInstanceByUrl();
                Filter.checkCommonInstanceByUrl();

                $('.nicescroll-rails').remove();
                if (data.currentInstance) {
                    Global.createInstancesFront(data.currentInstance);
                    instanceGlobal.contentReload.clear();
                }

                $('body, html').scrollTop(0);

                instanceGlobal.subInstance = data.subInstance ? JSON.parse(data.subInstance) : null;

                if (instanceGlobal.currentInstance && $.isFunction(instanceGlobal.currentInstance.events)) {
                    instanceGlobal.currentInstance.events();
                }

                var value = instanceGlobal.subInstance ? instanceGlobal.subInstance._type : '';

                switch (value) {
                    case 'CalendarView': {
                        var instance = CalendarView
                            .createInstance()
                            .updateProperties(instanceGlobal.subInstance)
                            .run()

                        Global.getInstance().setSubInstance(instance);

                        break;
                    }
                    case ProcessView._type: {
                        var instance = ProcessView.getInstance().run();

                        Global.getInstance().setSubInstance(instance);

                        break;
                    }
                    default: {
                        break
                    }
                }

                value = instanceGlobal.currentInstance.type || instanceGlobal.currentInstance._type;

                switch (value) {
                    case 'reports': {
                        Reports.prepareHidePreloaderByGraph();
                        break;
                    }
                    default: {
                        break
                    }
                }


                QuickViewPanel.setModelFromCash(data)
                    .updateContentScroll();

                /*clear initialization elements*/
                modalDialog.hideAll();
                $('.bootstrap-select').remove()

                Global
                    .initHandler()
                    .initSelects()
                    .responsiveNav();

                if (data.ev) {
                    $.each(data.ev, function (key, value) {
                        modalDialog.show(value, true);
                    });
                    if ($('.edit-view [data-type="block_activity"]').length) {
                        EditView.activityMessages.init();
                    }
                }

                if (instanceGlobal.subInstance && !instanceGlobal.subInstance.search) {
                    // deprecated
                    Search
                        .createInstance()
                        .setText(seacrh ? seacrh : data._search ? data._search : '')
                }

                if (Communication.getInstance()) {
                    Communication.initTextArea()
                }

                if (data.url.indexOf('module/BPM/')>=0) {

                    var mode, obj,
                        process = new Process(),
                        arrParam = data.url.split('?');

                    if (!arrParam[1]) {
                        return;
                    }

                    instanceGlobal.preloaderShow($('.bpm_block'), { hide: true });

                    if(arrParam[0].indexOf('constructor')>0){
                        mode = ProcessObj.PROCESS_MODE_CONSTRUCTOR;
                    } else {
                        mode = ProcessObj.PROCESS_MODE_RUN;
                    }

                    obj = Url.parse(arrParam[1]);
                    process.BPM.open(obj.process_id, mode);
                }

                Events.runHandler(Events.TYPE_EVENT_RESIZE, null);

                if (Global.isListView()) {
                    ListView.createInstance();
                }

                Global.initSelects();
            }

            return this;
        },
        /*
        * true - буде використовуватись pushState
          false - буде використовуватись replaceState
        * */
        setData: function (data) {
            this.data = data;

            return this;
        },
        setHash: function (hash) {
            if (typeof this.mode_history_state != 'undefined') {
                var data = this.data;

                if (this.mode_history_state) {
                    history.pushState(data, null, hash);
                } else {
                    history.replaceState(data, null, hash);
                }
            }

            return this;
        },
        replaceState: function (from, to) {
            var symbol,
                str = location.href.replace(from, to);

            symbol = str.lastIndexOf('&') || str.lastIndexOf('?');
            if (symbol>0) {
                str = str.substring(0, symbol);
            }
            history.replaceState(this.data, null, str);
        },
    };

    var ModelState = {
        instance: null,
        url: null,

        createInstance: function (instance) {
            var Obj = function(){
                for(var key in ModelState){
                    this[key] = ModelState[key];
                }
            }

            return new Obj().constructor(instance);
        },
        constructor: function (instance) {
            this.instance = instance;
            this.url = location.href;

            delete this.constructor;
            delete this.createInstance;
            return this;
        }
    };

    History = {
        history_params_list : [],
        states: null, // расширение history_params_list. История посещений.

        parent_url : '',

        addState: function (state) {
            this.states = this.states || [];

            this.states.push(ModelState.createInstance(state));

            return this;
        },
        getStates: function () {
          return this.states;
        },
        previosState: function () {
            var r = null;

            if (this.states && this.states.length > 1) {
                r = this.states[this.states.length - 2];
            }

            return r;
        },
        clearStates: function () {
            this.states = null;
        },
        createInstance : function(){
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            var instance = new Obj();
            iBackForwardHistory.implements.call(instance);

            return instance.constructor();
        },

        add : function(copy_id, id, module_dependent_params){
            getModuleUrl(copy_id, null, module_dependent_params, function(url){
                var length = History.history_params_list.length;

                if(length == false){
                    var p_url = document.location.href;
                    p_url = Url.getWithOutParams(p_url, ['modal_ev', 'unique_index'], false);
                    History.parent_url = p_url;
                }

                if(id !== ''){
                    url = Url.getWithOutParams(url, ['modal_ev'], false);
                    url = Url.appendParams(url, 'modal_ev=' + id);
                } else {
                    var value = History.history_params_list[History.history_params_list-1];

                    if(length && value){
                        History.history_params_list.push(value);
                    }

                    if (!value) return;
                }
                if (!window.backForward) {
                    history.pushState('', '', url);
                }

                History.history_params_list.push(url);
                AjaxContainers.cash.snapshot(modalDialog.modal_dialog_shown_window)

                /*
                 var match = /.*(\?.*)$/.exec(url);
                 if(match) {
                 history.pushState('', '', copy_id + match[1]);
                 History.history_params_list.push(copy_id + match[1]);
                 }
                 */
            });

        },
        get : function(copy_id, id, module_dependent_params, callback){
            getModuleUrl(copy_id, null, module_dependent_params, function(data){
                var url = data;
                if(id !== ''){
                    url = Url.appendParams(data, 'modal_ev=' + id);
                }
                callback(url);
            });
        },
        getViaParent : function(copy_id, id, callback){
            getModuleUrlViaParemt(copy_id, id, null, function(data){
                var url = data;
                if(id !== ''){
                    url = Url.appendParams(data, 'modal_ev=' + id);
                }
                callback(url);
            });
        },

        close : function(showParent){
            var length = History.history_params_list.length;
            if (showParent == true || length === 1){
                if (History.parent_url && History.parent_url.length) {
                    history.pushState('', '', History.parent_url);
                }
            } else
            if(length > 1) {
                var back = History.history_params_list[length - 2];
                if(!back){
                    history.pushState('', '', History.parent_url);
                } else {
                    history.pushState('', '', back);
                }
            }
            History.history_params_list.pop();
        },

        loadModal : function($callback){
            var url = location.href;

            if(url.match(/modal_ev=/)){
                var id,
                    copy_id = url.match(/show\/*(\d*)/)[1];

                if(!copy_id){
                    copy_id = url.match(/showTemplate\/*(\d*)/)[1];
                }

                id = url.match(/modal_ev=(\w*)/)[1];
                url = EditView.getEditViewUrl() + '/' + copy_id;

                EditView
                    .createInstance(modalDialog.createInstance())
                    .setParent(ViewType.getCurrentInstance())
                    .show(copy_id, id, url, $callback);
            }
            Preloader.afterPreloader();
        },

        setHash: function (hash) {
            history.pushState(null, null, hash);
        },
        replaceHash: function (hash) {
            history.replaceState(null, null, hash);
        },
        pushState: function (state) {
            history.pushState(null, null, state);
        },
        replaceState: function (state) {
            history.replaceState(null, null, state);
        },
        getVars : function(vars_str, key){
            if(!vars_str) return;
            var vars = JSON.parse(vars_str)
            if(vars && key && vars[key]){
                vars = vars[key];
            }

            return vars;
        }
    }

    AjaxContainers = {
        keys: {},
        arrayOfKeys : [], // чтоб я знал порядок запросов (что есть следующий запрос, который важный предыдущего).
        timeLast: null,
        cash: {
            listOfUrls: [], // for urls
            contents: {},
            listVisibleOfUrls: [],
            clear : function () {
                this.listOfUrls = [];
                this.contents = {};
            },
            removeKey : function (url) {
                for (key in this.contents) {
                    if (this.contents[key].url == url) {
                        delete this.contents[key];
                        break;
                    }
                }
            },
            set : function ($body, url, currentInstance, subInstance, ev){
                var _subInstance,
                    key = this.getKey(url),
                    time = moment().unix(),
                    data = {};

                if (key < 0){
                    this.listOfUrls.push(url);
                    key = time;

                    data = {
                        url : url,
                        create : time,
                        ev: ev ? ev.slice() : null,
                    };
                } else {
                    data = this.contents[key];
                    data['ev'] = ev ? ev.slice() : data['ev'];
                }

                var o,
                    obj = Base.copyObject(currentInstance);

                obj.contentReload = Base.copyObject(instanceGlobal.contentReload);

                if (Search.isSearchByUrl()) {
                    // var searchValue = Search.parseUrl();
                    var instance = CalendarView.getInstance();
                    if (instance && instance.search) {
                        obj['_search'] = instance.search;

                    } else {
                        obj['_search'] = Search.getInstance();
                    }
                }

                if (location.search) {
                    var param = Url.parse(location.search.substring(1));

                    if (param && param.id) {
                        data['id'] = param.id
                    }
                }


                if (Base.isObject(instanceGlobal.getFilter)) {
                    obj.getFilter = Base.copyObject(Filter);
                }

                // remove links
                delete obj.currentInstance;
                delete obj.subInstance;

                if (subInstance) {
                    delete subInstance.preloader;
                    delete subInstance.nice_scroll;

                    if (subInstance.search) {
                        try {
                            subInstance.search = JSON.stringify(subInstance.search);
                        } catch (e) {
                            console.log('error by search:', e);
                        }
                    }

                    if (subInstance.filter) {
                        try {
                            subInstance.filter = JSON.stringify(subInstance.filter);
                        } catch (e) {
                            console.log('error by filter:', e);
                        }
                    }
                }

                try {
                    o = JSON.stringify(obj);
                    _subInstance = Object.keys(subInstance).length ? JSON.stringify(subInstance) : null;
                } catch (e){
                    console.log('error ', e);
                }

                data['subInstance'] = _subInstance;
                data['content'] = $body;
                data['currentInstance'] = o;

                QuickViewPanel.getModelToCash(data);

                this.contents[key] = data;
            },
            getKey : function (url) {
                var time = -1;

                $.each(Object.keys(this.contents), function (key, data) {
                    var value = AjaxContainers.cash.contents[data];

                    if (value.url === url) {
                        time = AjaxContainers.cash.contents[data].create;
                        return;
                    }
                })

                return time;
            },
            get : function (url) {
                var result = null,
                    key = this.getKey(url);

                if (key>=0) {
                    result = this.contents[key];
                }

                return result;
            },
            snapshot: function (editView) {
                if (window.backForward) return;
                var subInstance,
                    currentInstance = instanceGlobal.currentInstance;

                if (instanceGlobal.subInstance) {
                    subInstance = Object.assign({}, instanceGlobal.subInstance);
                }

                this.set($('body').clone(), location.href, currentInstance, subInstance, editView);
            }
        },
        removeAll : function () {
            this.keys = {};
            this.arrayOfKeys = [];
            return this;
        },
        remove : function (key) {
            delete this.keys[key];
        },
        addKey : function (key) {
            var time = moment().unix();

            if (!this.keys[key]) {
                this.keys[key] = Object.create(null);

                this.arrayOfKeys.push[key];
                this.keys[key].create = time;
                this.timeLast = time;
            }

            return this.keys[key];
        },
        addInstance : function (key, instance) {
            var data = this.addKey(key)
            data.instance = [instance];

            this.arrayOfKeys.push(key);
        },
        init: function () {
            Events
                .createInstance()
                .setType(Events.TYPE_SNAPSHOT)
                .setKey('AjaxSnapshot')
                .setHandler(function (e) {
                    AjaxContainers.cash.snapshot();
                })
                .run();
        }
    }

    exports.History = History;
    exports.iBackForwardUpdate = iBackForwardUpdate;
    exports.iBackForwardHistory = iBackForwardHistory;
    exports.AjaxContainers = AjaxContainers;
})(window);
