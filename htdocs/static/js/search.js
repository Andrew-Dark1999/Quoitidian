
;(function (exports) {
    var _private, _public, _protected, Search,
        _self = {}; //link for instance

    _protected = {

    };
    _private = {
        instance: null,
        common_instance: null,

        onFilter: function(e){
            var $this = $(this),
                instance = e.data.instance,
                value = $this.find('.search-input').val();

            if ($.isFunction(instance.showPreloader)) {
                instance.showPreloader($this)
            }
            else {
                instanceGlobal.preloaderShow($this);
            }

            instance
                .setText(value)
                .apply();

            if (Search.setCommonInstance) {
                Search.setCommonInstance(value.length ? instance : null);
            }

            return false;
        }
    };

    _public = {
        _parent: null,

        events: function () {
            this._events = [
                { parent: document, selector: '.search-filter', event: 'submit', func: _self.onFilter},
            ]

            Global.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        constructor: function () {
            iTemplate.implements.call(this);
            iBackForwardHistory.implements.call(this);

            this.reDefinition()
                .setTemplate(Search._getTemplate());

            return this;
        },
        reDefinition: function () {

            this.updateProperties = function (data) {
                this._text = data._text;

                this.setTextToView()
                    .setTemplate(Search._getTemplate());

                return this;
            };

            return this;
        },
        setOnlyText: function(text){
            if(typeof(text) != 'undefined' && text){
                this._text = text;
            } else {
                this._text = '';
            }
            return this;
        },
        setText : function(text){
            this.setOnlyText(text)
                .setTextToView(this._text);

            return this;
        },
        clear : function () {
            this._text = '';
        },
        updateByUrl : function(){
            this._text = Url.getParam(null, this._field) || '';

            return this;
        },
        appendToUrl: function () {
            var url,
                oldSearch = Search.parseUrl(),
                phase = this.getParam();

            if (oldSearch) {
                History.replaceState('search='+oldSearch, phase);
            } else {
                url = Url.appendParams(location.search, phase);
                History.pushState(url);
            }
            return this;
        },
        getText : function(){
            return this._text || '';
        },
        apply : function(){
            var url = this.getFullUrl();

            Global.getInstance().setContentReloadInstance(instanceGlobal.contentReload);

            instanceGlobal.contentReload
                .prepareVariablesToGeneralContent()
                .setUrl(url)
                .run();
        },
        getParamAsJson: function () {
            return {
                'search': this.getText()
            }
        },
        setTextToView: function () {
            $('.search-filter .search-input').val(this._text);

            return this;
        }
    };

    Search = {
        _text : '',
        _interface: 'Search',
        _field : 'search',
        TYPE_VIEW_OTHER: 1,

        checkCommonInstance: function (json) {
            if (Search.getCommonInstance && json.url) {
                var url,
                    common = Search.getCommonInstance();

                if (common) {
                    url = Url.createInstance();

                    url.setUrl(json.url)
                        .jsonToUrl(common.getParamAsJson());

                    json.url = url.getUrl(); //Url.appendParams(json.url, Search.getInstance(true).getParam());
                }
            } else {
                // старый подход
                if (json._search) {
                    json.url = Url.appendParams(json.url, Search.getInstance(true).getParam());
                }
            }

            return json.url;
        },
        checkCommonInstanceByUrl: function () {
            var instance = null;

            this.implements(iModule);

            if (Search.isSearchByUrl()) {
                instance = Search.createInstance();

                instance
                    .updateByUrl()
                    .setTextToView();
            } else {
                Search.setTextToView(null);
            }

            if (Search.setCommonInstance) {
                Search.setCommonInstance(instance);
            }

            return this;
        },
        // реализация интерфейсов.
        implements: function (object) {
            object.implements.call(this);

            return this;
        },
        setTextToView: function (value) {
            $('.search-filter .search-input').val(value || '');

            return this;
        },
        setInstance: function (instance) {
            _self.instance = instance;

            return _self.instance;
        },
        _getTemplate: function () {
            return $('.search-filter .search-input');
        },
        getInstance : function(status){
            if (status && !_self.instance) {
                _self.instance = this.createInstance();
            }
            return _self.instance;
        },

        createInstance : function(_const){
            var instance;

            var Obj = function(){
                for(var key in Search){
                    this[key] = Search[key];
                }
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            instance = new Obj();

            if (_const != Search.TYPE_VIEW_OTHER) {
                _self._instance = instance;
                instance.events();
            }

            return instance.constructor();
        },

        parseUrl : function () {
            var json = Url.getParams(location.href);

            return json && json.search ? json.search : null;
        },
        //static
        isSearchByUrl : function(){
            var r = Url.getParam(null, this._field) || '';
            return r && r != '' ? true : false;
        },

        getTextFromInput: function () {
            return $('.search-filter .search-input').val();
        },

        getFullUrl : function(){
            var search, url,
                params = Url.parseURLParams(),
                params_parse = [];

            $.each(params, function(key, value){
                if(key != 'search') params_parse.push(key +'='+ value);
            });

            params_parse = params_parse.join('&');

            // TODO: проверить руский поиск в мозиле.
            //search = encodeURI(this.getParam());
            search = this.getParam();
            url = window.location.href.split("?");
            url = url[0] + (params_parse ? '?' + params_parse : '') + (params_parse ? (search ? '&' + search : '') : (search ? '?' + search : '')) ;

            return url;
        },

        getParam : function(){
            var text = this.getText();

            return text.length ? 'search=' + text : text;
        },

        destroy : function () {
            //call as instance
            if (!this._instance) {

            }
            else {
                //Url.replace(Search._instance.getParam(), '');
                Search._instance = null;
            }
        }

    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in Search) {
        _self[key] = Search[key];
    }

    exports.Search = Search;
})(window);