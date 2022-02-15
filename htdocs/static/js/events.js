/**
 * Created by andrew on 7/16/18.
 */
;(function (exports) {
    var _private, modelEvents, Events,
        _self = {}; //link for instance

    _private = {
        line_resize: null,
        line_scroll: null,
        line_ajax_complete: null,
        line_body_click: null,
        line_change_hash: null,
        line_pop_state: null,
        line_update_data: null,
        line_destroy_data: null,
        line_window_load: null,
        line_snapshot: null,
        line_load_graph: null,

        _type: 'events',

        getObject: function (type) {
            var list = null;

            switch (type) {
                case Events.TYPE_AJAX_COMPLETE: {
                    list = this.line_ajax_complete;
                    break;
                }
                case this.TYPE_EVENT_RESIZE: {
                    list = this.line_resize;
                    break;
                }
                case this.TYPE_EVENT_BODY_CLICK: {
                    list = this.line_body_click;
                    break;
                }
                case this.TYPE_EVENT_SCROLL: {
                    list = this.line_scroll;
                    break;
                }
                case this.TYPE_LOAD_GRAPH: {
                    list = this.line_load_graph;
                    break;
                }
                case this.TYPE_POP_STATE: {
                    list = this.line_pop_state;
                    break;
                }
                case this.TYPE_CHANGE_HASH: {
                    list = this.line_change_hash;
                    break;
                }
                case this.TYPE_DESTROY: {
                    list = this.line_destroy_data;
                    break;
                }
                case this.TYPE_UPDATE_DATA: {
                    list = this.line_update_data;
                    break;
                }
                case this.TYPE_WINDOW_LOAD: {
                    list = this.line_window_load;
                    break;
                }
                case this.TYPE_SNAPSHOT: {
                    list = this.line_snapshot;
                    break;
                }
                default : {
                    break;
                }
            }

            return list;
        },
        universal: function (list, model) {
            var status = null;
            list = list || [];
            model = model || {};

            $.each(Object.keys(list), function (key, value) {
                status = list[value](model.event, model.data)
            });

            return status;
        },
        helperChangeHash: function (model) {
            this.universal(this.line_change_hash, model);
        },
        helperPopState: function (model) {
            this.universal(this.line_pop_state, model);
        },
        helperTypeAjaxComplete: function (model) {
            this.universal(this.line_ajax_complete, model);
        },
        helperBodyClick: function (model) {
            this.universal(this.line_body_click, model);
        },
        helperScroll: function (model) {
            this.universal(this.line_scroll, model);
        },
        helperUpdateData: function (model) {
            return this.universal(this.line_update_data, model);
        },
        helperDestroy: function (model) {
            return this.universal(this.line_destroy_data, model);
        },
        helperWindowLoad: function (model) {
            return this.universal(this.line_window_load, model);
        },
        helperLoadGraph: function (model) {
            return this.universal(this.line_load_graph, model);
        },
        helperSnapshot: function (model) {
            return this.universal(this.line_snapshot, model);
        },
        helperResize: function (model) {
            var list = this.line_resize,
                _this = this;

            if (list && list.initScroll) {
            }

            $.each(Object.keys(list), function (key, value) {
                if (list[value].handler) {
                    list[value].handler.call(list[value].instance, _this);
                } else {
                    list[value](model.event, model.data);
                }
            });
        },
    };

    modelEvents = {
        _type: null,
        _handler: null,
        _instance: null,
        _key: null,

        //{ type: '', key: '', handler: '', instance: ''}
        run: function () {
            var object = _self.getObject(this._type);

            if (object) {
                object[this._key] = this._handler; // TODO: ошибочное, проверить.
                object[this._key].handler = this._handler;
                object[this._key].instance = this._instance;
            }

            return this;
        },
        setKey: function (key) {
            this._key = key;

            return this;
        },
        setInstance: function (instance) {
            this._instance = instance

            return this;
        },
        setHandler: function (handler) {
            this._handler = handler;

            return this;
        },
        setType: function (type) {
            this._type = type;

            return this;
        },
        constructor: function () {
            return this;
        }
    };

    Events = {
        type: 'Events',
        TYPE_EVENT_RESIZE: 1,
        TYPE_EVENT_BODY_CLICK: 2,
        TYPE_AJAX_COMPLETE : 3,
        TYPE_EVENT_SCROLL: 4,
        TYPE_CHANGE_HASH: 5,
        TYPE_POP_STATE: 6,
        TYPE_UPDATE_DATA: 7,
        TYPE_DESTROY: 8,
        TYPE_WINDOW_LOAD: 9,
        TYPE_SNAPSHOT: 10,
        TYPE_LOAD_GRAPH: 11,

        createInstance: function () {
            var Obj = function(){
                for(var key in modelEvents){
                    this[key] = modelEvents[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return new Obj().constructor();
        },

        removeHandler: function (json) {
            if (json.type && _self.getObject(json.type)) {
                delete _self.getObject(json.type)[json.key];
            }
            return this;
        },
        init: function () {
            _self.line_resize = {};
            _self.line_ajax_complete = {};
            _self.line_scroll = {};
            _self.line_body_click = {};
            _self.line_change_hash = {};
            _self.line_update_data = {};
            _self.line_destroy_data = {};
            _self.line_window_load = {};
            _self.line_snapshot = {};
            _self.line_load_graph = {};

            return this;
        },
        getCountLine: function (type) {
            var count = 0;

            switch (type) {
                case Events.TYPE_EVENT_RESIZE: {
                    count = Object.keys(_self.line_resize).length
                    break;
                }
                case Events.TYPE_EVENT_SCROLL: {
                    count = Object.keys(_self.line_scroll).length
                    break;
                }
                case Events.TYPE_LOAD_GRAPH: {
                    count = Object.keys(_self.line_load_graph).length
                    break;
                }
                case Events.TYPE_UPDATE_DATA: {
                    count = Object.keys(_self.line_update_data).length
                    break;
                }
                default: break;
            }

            return count;
        },
        runHandler: function (type, model) {
            var status = null;

            switch (type) {
                case Events.TYPE_EVENT_RESIZE: {
                    _self.helperResize(model);
                    break;
                }
                case Events.TYPE_EVENT_SCROLL: {
                    _self.helperScroll(model);
                    break;
                }
                case Events.TYPE_AJAX_COMPLETE: {
                    _self.helperTypeAjaxComplete(model);
                    break;
                }
                case Events.TYPE_EVENT_BODY_CLICK: {
                    _self.helperBodyClick(model);
                    break;
                }
                case Events.TYPE_CHANGE_HASH: {
                    _self.helperChangeHash(model);
                    break;
                }
                case Events.TYPE_UPDATE_DATA: {
                    status = _self.helperUpdateData(model);
                    break;
                }
                case Events.TYPE_WINDOW_LOAD: {
                    status = _self.helperWindowLoad(model);
                    break;
                }

                case Events.TYPE_DESTROY: {
                    status = _self.helperDestroy(model);
                    break;
                }
                case Events.TYPE_SNAPSHOT: {
                    status = _self.helperSnapshot(model);
                    break;
                }
                case Events.TYPE_POP_STATE: {
                    _self.helperPopState(model);
                    break;
                }
                case Events.TYPE_LOAD_GRAPH: {
                    _self.helperLoadGraph(model);
                    break;
                }

                default: break;
            }
            return status;
        },
        run: function () {
            $window.on('resize', function(e) {
                _self.helperResize(e);
            });

            $window.on('scroll', function(e) {
                if (QuickViewPanel.isHover()) {
                    return false;
                }
                _self.helperScroll(e);
            });

            $('body').on('click', function(e) {
                _self.helperBodyClick(e);
            });

            $(window).load(function (e) {
                if (!ModelGlobal.isAuth()) {
                    return;
                }

                var callback;

                setTimeout (function(){
                    var instance = Global.getInstance(),
                        subInstance = ViewType.getCurrentInstance();

                    instance.setPreloader(instance.preloader && instance.preloader.destroy());

                    $('#container').removeClass('set-preloader reload-page show');

                    setCheckboxHeight();
                    $('body > .nicescroll-rails').css('opacity','1');

                    if (subInstance) {
                        subInstance.afterLoadView()
                    }
                }, 500);
                Global.responsiveNav();
                poliDot();

                if (typeof ListViewDisplay == 'undefined') {
                    return;
                }
                ListViewDisplay
                    .setIndex()
                    .setHiddenGroupIndex();

                if (EditView.willExist()) {
                    callback = function () {
                        _self.helperWindowLoad(e);
                    };
                } else {
                    _self.helperWindowLoad(e);
                }

                History.loadModal(callback);

                ;
            });

            return this;
        }
    }

    for(var key in _private){
        _self[key] = _private[key];
    }

    for(var key in Events){
        _self[key] = Events[key];
    }

    exports.Events = Events;
})(window);
