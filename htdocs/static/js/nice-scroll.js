
var NiceScroll = {
    _element: null,
    _handler_scroll_end: null,
    _status_balance_data: true,
    _native: null, // native object NiceScroll
    _position: false, // true - horizontale; false - verticale
    _container: null,
    _parent: null,
    cursorwidth_default: '3px',

    config: {
        cursorcolor: "#1FB5AD",
        cursorborder: "0px solid #fff",
        cursorborderradius: "0px",
        cursorwidth: "3px",
        railalign: 'right',
        enablemousewheel: true,
        autohidemode: false
    },
    createInstance : function(){
        var Obj = function(){
            for(var key in NiceScroll){
                this[key] = NiceScroll[key];
            }
        }

        return new Obj();
    },
    setElement : function (element) {
        if (element && element.length) {
            this._element = element;
        }

        return this;
    },
    setStatusLoadData : function (bool) {
        this._status_balance_data = bool;

        return this;
    },
    setPosition: function (status) {
        this._position = status;

        return this;
    },
    getEnableMouseWheel: function () {
      return Global.browser.getDevice() == Constant.DEVICE_IPHONE ? true : false;
    },
    setHandlerScrollEnd : function (handler) {
        var _this = this;

        if (handler && this._native) {
            this._handler_scroll_end = function() {
                var nThis = this;

                if ((parseInt(nThis.scrollvaluemax) - 25) <= (parseInt(nThis.scroll.y)) && _this._status_balance_data) {
                    handler();
                }
            };

            this._native.scrollend(this._handler_scroll_end);
        }

        return this;
    },
    setContainer: function (container) {
        this._container = container;
        return this;
    },
    setParent: function (parent) {
        this._parent = parent;

        return this;
    },
    fullClear: function () {
        var $container = this._container || this._parent;

        if ($container && $container.length) {
            $container.find('.nicescroll-rails').remove();
        }

        return this;
    },
    init: function ($element) {
        $element = $element || this._element || $('.list_view_block .crm-table-wrapper');

        if ($element.length) {
            if (this._position) {
                // horizontale
                this._native = $element.niceScroll({
                    cursorcolor: "#1FB5AD",
                    cursorborder: "0px solid #fff",
                    cursorborderradius: "0px",
                    cursorwidth: "6px",
                    railalign: 'right',
                    enablemousewheel: this.getEnableMouseWheel(),
                    autohidemode: false
                });
            } else {
                //TODO: this.config was error!!!
                this._native = $element.niceScroll({
                    cursorcolor: "#1FB5AD",
                    cursorborder: "0px solid #fff",
                    cursorborderradius: "0px",
                    cursorwidth: this.cursorwidth_default,
                    railalign: 'right',
                    enablemousewheel: true,
                    autohidemode: false
                });
            }
        }

        return this;
    },
    clear: function ($element) {
        $element = $element || this._element;

        if ($element && $element.length) {
            $element.getNiceScroll().remove();
        }

        return this;
    },
    update: function () {
        if (this._native && this._element) {
            this._element = $(this._element.selector);
            this.init();
        }

        return this;
    }
}

var niceScroll = {
    update: function ($object) {
        if ( $object.length) {
            NiceScroll
                .createInstance()
                .setElement($('.list_view_block .crm-table-wrapper'))
                .init();
        }
    },
    //DEPRECATED
    init: function () {
        $('.list_view_block .crm-table-wrapper').niceScroll({
            cursorcolor: "#1FB5AD",
            cursorborder: "0px solid #fff",
            cursorborderradius: "0px",
            cursorwidth: "6px",
            railalign: 'right',
            enablemousewheel: false,
            autohidemode: false
        }); //.resize();
    },
    clear: function () {
        $('.list_view_block .crm-table-wrapper').attr('style', '').getNiceScroll().remove();
    }
}

var niceScrollInit = function() {
    NiceScroll
        .createInstance()
        .setPosition(true)
        .setElement($('.list_view_block .crm-table-wrapper'))
        .init();
};