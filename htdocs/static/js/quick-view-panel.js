/**
/**
 * Created by andrew on 10/12/17.
 */

;(function (exports) {
    var _private, _public, QuickViewPanel, _static,
        _self; //link for instance

    //interface
    var ModelContent = {
        instance:null,
        block_group: null,
        js_class_name: null,
        there_is_data: null,
        html: null,

        _container: null,
        _store_data: null,
        _status_load: 1,

        //...........
        constructor: function () {
            iPreloader.implements.call(this);

            this.setPreloader(Preloader.createInstance());
            this._container = $('.right-sidebar');

            return this;
        },
        _getIcoContent: function () {
            return $('[data-type="navigation_block"] [data-block_name="'+this.name+'"]');
        },
        updateRelateView: function () {
            var $element = this.getList().closest('[data-name="'+this.name+'"]');

            this._block_object = $element;

            return this;
        },
        hide: function () {
            if (this.instance) {
                this.instance.hide();
            }

            this.hideContentScroll();
            $('.element[data-name="' + this.name +'"]').remove();

            this._getIcoContent().hide();
        },
        hideContentScroll: function () {
            if (this.instance) {
                this.instance._nice_scroll.clear();
            }

            return this;
        },
        show: function () {
            var instance = this.instance;

            instance._parent = this;
            this
                .setStoreData()
                .updateScroll();

            instance
                .show()
                .initScroll();

            if (this.getPreloader().isRunning()) {
                this.getPreloader().hideFull();
            }

            Events
                .createInstance()
                .setType(Events.TYPE_EVENT_RESIZE)
                .setKey('initScroll')
                .setHandler(instance.initScroll)
                .setInstance(instance)
                .run();

            this._getIcoContent().show();
        },
        getList: function () {
            return $('[data-type="content_block"][data-name="'+this.name+'"] .channels-list');
        },
        setStoreData : function () {
            this._store_data = this.getList().find('>li');

            return this;
        },
        updateScroll: function () {
            var $list = this.getList(),
                instanceNScroll = NiceScroll.createInstance().setContainer(this._container);

            instanceNScroll
                .clear()
                .fullClear()
                .setElement($list);

            this.instance._nice_scroll = instanceNScroll.init();

            return this;
        },
        init: function () {
            var $content,
                $container = $('.right-sidebar .element[data-type="content"]');

            $content = $(this.html.block);

            $container.append($content);

            this.instance = window[this.js_class_name].getInstance(true);

            this.updateScroll();
            this.instance
                .setName(this.name)
                .setBlockGroup(this.block_group)
                .setBlockObject($content)
                .setThereIsData(this.there_is_data)
                .setVisible(this.visible);

            if(this.visible){
                var model = QuickViewPanel.getInstance().getModel().getCurrentModel();

                if (model) {
                    this.preloader = Object.assign({}, model.getPreloader())
                }

                _self.getModel().setCurrentModel(this);
            }
        },
        beforeSetModel: function () {

          return this;
        },
        setStatusLoad : function(number) {
            this._status_load = number;
            return this;
        },
        getStatusLoad : function() {
            return this._status_load;
        },
        getStoreData : function () {
            var _this = this,
                data = this._store_data;

            _this.there_is_data = true;

            if (data) {
                var time = setTimeout(function () {
                    clearTimeout(time);
                    _this.getList().empty().append(data);

                    QuickViewPanel.setVerticalPosition();

                    _this.instance._nice_scroll.setStatusLoadData(true);
                    _this.getPreloader().hide();
                    _this.updateScroll();
                    _this.instance.resizeNiceScroll();

                }, 50);
            }

            return this;
        },
        destroy: function () {
            this.hide();
        }
    };

    var ModelQuickViewPanel = {
        _type: 'ModelQuickViewPanel',
        _searh: null,
        _typing_search_timer: null,
        _current_active_model: null,

        _current_model: null,
        _model_list: null,

        changeBlock: function($element) {
            //hide all
            var item, instance,
                baseInstance = QuickViewPanel.getInstance();

            this.hiddenBlocks();

            for(var key in this._model_list){
                item = this._model_list[key];
                instance = item.instance;

                if (item.name == $element.data('block_name')) {
                    item.updateRelateView()
                        .show();

                    this.setCurrentModel(item);

                    if(!instance.activeMenu.getCurrentCount()){
                        instance.update(instance.activeMenu.ACTION_UPDATE_CURRENT_LIST);
                    }
                    baseInstance.events();
                }
            };
        },
        hiddenBlocks: function() {
            var item;

            for(var key in this._model_list){
                item = this._model_list[key];

                item.instance.setVisible(false);
                item.instance.hide();
            };

            return this;
        },
        getUrl: function (str) {
            var url,
                data = QuickViewPanel.getInstance().search.getParamAsJson();

            url = Url.createInstance().setUrl(str);

            if (data.search.length) {
                str = url.jsonToUrl(data).getUrl();
            }

            return str;
        },
        getContent: function () {
            var _this = this,
                url = '/QuickView/getBlocks';

            url = this.getUrl(url);

            AjaxObj
                .createInstance()
                .setUrl(url)
                .setData({})
                .setType('POST')
                .setAsync(true)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    if(data.status == true){

                        if (!QuickViewPanel.isOpen()) {
                            QuickViewPanel.show();
                        }

                        var model = _this._current_model;
                        _self.initBlocks(data.list);

                        //show current model
                        if (model && _this._model_list[model.name]) {
                            _this
                                .hiddenBlocks()
                                .setCurrentModel(model);

                            _this.showBlock(model.name);
                        }

                    } else if(data.status == false)QuickViewPanel.hide();
                })
                .setCallBackError(function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                })
                .send();
        },

        clearContentModel: function () {
            for (var key in this._model_list){
                this._model_list[key].destroy();
                delete this._model_list[key];
            }
            var $sidebar = $('.right-sidebar');
            $sidebar.find('.element[data-type="content"]').empty();
            $sidebar.find('.nicescroll-rails').remove();
        },
        initContentModels: function () {
            for (var key in this._model_list){
                this._model_list[key].init();
            }
            return this;
        },
        getCurrentModels: function () {
            return this._model_list;
        },
        getCurrentModel: function () {
            return this._current_model;
        },
        setCurrentModel: function (model) {
            this._current_model = model;

            return this;
        },
        showCurrentModel: function () {
            this._current_model.show();

            return this;
        },
        setCurrentModelFromView: function () {
            var $element = $('.right-sidebar [data-type="content"] .right-stat-bar').not('.hide'),
                name = $element.attr('data-name');

            this._current_model = this.getBlockModelByName(name);

            this._current_model.instance
                .setName(name)
                //.setBlockGroup(this.block_group)
                .setBlockObject($element)
                //.setThereIsData(this.there_is_data)
                .setVisible(true);

            return this;
        },
        updateContentScroll: function () {
            this.setCurrentModelFromView();
            this._current_model.show();
        },
        getBlockModelByName : function(block_name){
            if(!this._model_list || !block_name){
                return;
            }

            var model = _self.getModel();

            if(!model._model_list[block_name]){
                return;
            }

            return model._model_list[block_name];
        },

        hideBlocksByBlockGroup : function(block_group){
            if(!this._model_list){
                return this;
            }

            if(!block_group){
                return this;
            }

            for(key in this._model_list){
                if(this._model_list[key].instance.getBlockGroup() != block_group){
                    continue;
                }

                this._model_list[key].instance.hide();
            }

            return this;
        },
        showBlock : function(block_name){
            if(!this._model_list || !block_name){
                return this;
            }

            var model = _self.getModel();

            if(!model._model_list[block_name]){
                return this;
            }

            this.getBlockModelByName(block_name).show();

            return this;
        },
        hideBlock : function(block_name){
            if(!this._model_list){
                return this;
            }

            if(!block_name){
                return this;
            }

            this._model_list[block_name].instance.hide();

            return this;
        },
    }

    //instance
    _self ={
        _instance: null,

        destroy: function () {
            var instance = _self._instance;

            Events.removeHandler({ key: 'QVResize', type: Events.TYPE_DESTROY});
            Global.removeEvents(instance._events);
            _self._instance = null; // static - instance can be only one

        },
    }

    _private = {
        _name_class_model: 'ModelQuickViewPanel',
        createInstance : function(){
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);
            return _self._instance = new Obj().constructor();
        },
        createModel: function () {
            var model = Object.assign({}, ModelQuickViewPanel);

            model._model_list={};

            this.setModel(model);
            return model;
        },
        setModel: function (model) {
            $('.right-sidebar').data(this._name_class_model, model);
        },
        onKeyUpContextSearch : function(e) {
            var modelContent,
                baseInstance = e.data.instance,
                $this = $(this);

            clearTimeout(_self._typing_search_timer);

            _self._typing_search_timer = setTimeout(function(){
                var value = $this.val();

                clearTimeout(_self._typing_search_timer);

                var modelPanel = _self.getModel();

                modelContent = modelPanel.getCurrentModel();
                baseInstance.setPreloaderToView();

                baseInstance.search.setOnlyText(value);

                var instance = modelContent.instance;

                instance.setPeriod(true)
                    .activeMenu
                    .setSearchText(value)
                    .removeChannels();

                if (value) {
                    instance.refreshChannels(false);
                } else {
                    modelContent.getStoreData();
                }

            }, QuickViewPanel._typing_speed);

            return false;
        },
        onSwitchBlock : function(e){
            var baseInstance = e.data.instance,
                modelQP = _self.getInstance().getModel();

            modelQP.changeBlock($(this), baseInstance);
        },
        onHoverPanel: function (e) {
            if ($(e.srcElement).closest('.right-sidebar').length) {
                QuickViewPanel.setHover(true)
                Mouse.wheelDisable();
            }
        },
        onHoverMainContent: function (e) {
            QuickViewPanel.setHover(false);
            Mouse.wheelEnable();
        }
    };

    //get with instance
    _public={
        _type: 'QuickViewPanel',
        _status_visible: null, // static
        _instance: null, // static - instance can be only one
        _nice_scroll: null,

        search: null,

        initBlocks : function(block_list){
            if(!block_list || $.isEmptyObject(block_list)){
                return this;
            }

            var modelPanel = _self.getModel();

            modelPanel.clearContentModel();

            //create model
            for (var key in block_list){
                var block = block_list[key],
                    model = Object.assign({}, ModelContent);

                for( var sub_key in block) {
                    model[sub_key] = block[sub_key];
                };
                //copy inner
                model.html ={
                    block: block.html.block
                };
                modelPanel._model_list[model.name] = model.constructor();
            }

            modelPanel
                .initContentModels()
                .showCurrentModel();

            return this;
        },
        events: function () {
            this._events = [
                { parent: document, selector: '.right-sidebar .search-input', event: 'keyup', func: _self.onKeyUpContextSearch},
                { parent: document, selector: '.right-sidebar .element[data-type="navigation_block"] .element[data-type="link"]', event: 'click', func: _self.onSwitchBlock},
                { parent: document, selector: '.right-sidebar.open-right-bar', event: 'hover, touchstart, touchend', func: _self.onHoverPanel},
                { parent: document, selector: '#main-content, #modal_dialog_container', event: 'hover', func: _self.onHoverMainContent},
            ]

            this.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        getModel: function () {
            return $('.right-sidebar').data()[_self._name_class_model];
        },
        setStatusLoad : function(number) {
            var modelQP = this.getModel();

            modelQP.getCurrentModel().setStatusLoad(number)

            return this;
        },

        constructor: function () {
            iPreloader.implements.call(this);

            _self.createModel();
            this.search = Search.createInstance(Search.TYPE_VIEW_OTHER);

            this
                .events()
                .reDefinition();

            return this;
        },
        reDefinition: function () {
            var _this = this;

            this.showPreloader = function () {
                _this.setPreloaderToView();

                return this;
            };

            return this;
        },
        init : function(){
            var block_list = this.getBlockList();

            if($.isEmptyObject(block_list)){
                return false;
            }

            this.show();
        },

        setPreloaderToView : function() {
            var modelQVP = _self.getModel(),
                modelContent = modelQVP.getCurrentModel();

            if (modelContent.getStatusLoad() == QuickViewPanel.VIEW_PRELOADER_IN_CENTER) {
                var $element = $('.right-stat-bar.element[data-type="content_block"]').not('hide').find('.element[data-type="container"]');

                $('.right-side-accordion .channels-list').getNiceScroll().remove(); // костиль

                modelContent.getPreloader()
                    .setRunning(false)
                    .setAddClass('html', 'hide-edit-view-from-quick-view')
                    .setElement('html', ['hide-edit-view-from-quick-view'])
                    .setWhereContentHide(Preloader.ADDITIONAL_PANEL)
                    .setCssPositionSpinner(Preloader.css.ABSOLUTE)
                    .setPlaceForSpinner($element)
                    .run();
            } else {
                $('.channels-list li.small-preloading').addClass('set-preloader init-preloader');
                modelContent.setStatusLoad(QuickViewPanel.VIEW_PRELOADER_IN_CENTER)
            }

            return this;
        }
    };

    //static methods
    QuickViewPanel = {
        VIEW_PRELOADER_IN_CENTER: 1,
        VIEW_PRELOADER_IN_BOTTOM: 2,

        _interface: 'QuickViewPanel', // устаревшее
        _type: 'QuickViewPanel',
        _typing_speed: 600,
        _width: 240,
        _status_visible: false,
        hover: false,

        init: function () {
            var modelPanel, instance,
                json = Global.getModel().getQuickViewBlocks();

            if (!Object.keys(json).length) {
                return;
            }

            if (!this.isOpen()) {
                this.show();
            }

            instance = this.getInstance();
            modelPanel = instance.getModel();

            if (!modelPanel) {
                return;
            }

            instance.initBlocks(json);
        },
        setHover: function (bool) {
            this.hover = bool;

            return this;
        },
        isHover: function () {
            return this.hover;
        },
        getWidth : function () {
            return this._width;
        },
        show : function(){
            var $body = $('body'),
                $rightSideBar = $('.right-sidebar'),
                $header = $('.header');

            this._status_visible = true;
            $body.addClass('open-right-panel');
            $rightSideBar.addClass('open-right-bar');
            $header.addClass('merge-header');

            if(Global.isBmpView()){
                ProcessObj.statusRightPanel(true);
            }

            _self._instance = _self.createInstance();

            Global.responsiveNav();
            //MainMenu.run();
        },
        getInstance : function(){
            return _self._instance;
        },
        hide : function(){
            var $body = $('body'),
                $rightSideBar = $('.right-sidebar'),
                $header = $('.header');

            this._status_visible = false;
            $body.removeClass('open-right-panel');
            $rightSideBar.removeClass('open-right-bar');
            $header.removeClass('merge-header');
            if (Global.isBmpView()) {
                ProcessObj.statusRightPanel(false);
            };
            shadowEnd();

            // //remove models;
            // if (this._model_list) {
            //     for (var key in this._model_list) {
            //         this._model_list[key].destroy();
            //     }
            // }
            _self.destroy();

            Global.responsiveNav();
            //MainMenu.run();

            return this;
        },
        getModelToCash: function(json){
            if (this.isOpen()){
                var model = this.getInstance().getModel();
                json[ModelQuickViewPanel._type] = model; //cloneObject();
                //json[ModelQuickViewPanel._type] = jQuery.extend(true, {}, model); // Deep copy
            }

            return this;
        },
        setModelFromCash: function(json){
            if (this.isOpen()){
                _self.setModel(json[ModelQuickViewPanel._type]);
            }

            return this;
        },
        isOpen : function () {
            return QuickViewPanel._status_visible ? true : false;
        },
        updateContent: function (priority) {
            var instance = this.getInstance();

            if (instance) {
                if (Communication.isCommunicationsModule() || Calls.isCallsModule() || priority) {
                    if (priority) {
                        instance.showPreloader();
                        $('html').removeClass('hide-edit-view-from-quick-view');
                        modalDialog.hide();
                    }

                    instance.getModel().getContent();
                }
            }

            return this;
        },
        updateContentScroll: function () {
            var instance = this.getInstance();

            if (!instance) {
                return;
            }
            instance.getModel().updateContentScroll();
            return this;
        },
        //show / hide module (model of data)
        toggleToModule: function (json)  {
            var modelQuickViewPanel,
                instance = this.getInstance();

            if (!instance) {
                instance = _self.createInstance();
            }
            modelQuickViewPanel = instance.getModel();
            modelQuickViewPanel.getContent();
        },
        setVerticalPosition : function () {
            var $li, height, model, $element, $content,
                qvInstance = QuickViewPanel.getInstance(),
                total,
                windowHeight = $(window).height();

            if (!qvInstance) {
                return this;
            }

            model = qvInstance.getModel().getCurrentModel();
            if (!model) {
                return this;
            }

            $('.right-stat-bar .element[data-type="container"]').height($(window).height() - ($('.right-stat-bar').position().top + 50));

            $element = $('.right-side-accordion:visible .widget-container.channels-list');
            var $container = $element.closest('.element[data-type="content_block"]');

            $content = $container.closest('.element[data-type="content"]');

            if (!$content.length) {
                return this;
            }

            height = ($content.position().top + 50);
            //height = ($content.position().top + $element.next().height() + $content.find('.element:not(.hide) .section-title').height() + 18);
            //height = ($content.position().top + $element.next().height());

            $li = $element.find('>li');
            if ($li.length) {
                $li = $li.add($element.next().find('>li'));

                total = parseInt($element.css('padding-top'));

                for (var i = 0; i < $li.length; i++) {
                    total += $($li[i]).height();
                }

                var offset = total + 24;

                if (offset >= (windowHeight - height)) {
                    height = windowHeight - (height + $element.next().height());
                    $element.height(height);
                } else {
                    //all list in 1 window
                    //$element.height(offset + 12);
                    $element.height('auto');
                }

                // //перевірка
                // var delta = $element.height() + parseInt($element.css('padding-top')) + 68;
                // if (delta > $element.parent().height()) {
                //     $element.removeClass('content-box');
                // }
            } else {
                $element.height('auto');
            }

            return this;
        },

    };

    for(var key in _public) {
        _self[key] = _public[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    //only static
    for(var key in QuickViewPanel) {
        _self[key] = QuickViewPanel[key];
    }

    exports.QuickViewPanel = QuickViewPanel;
    //exports.ModelContent = ModelContent;
})(window);

