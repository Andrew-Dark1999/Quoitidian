;(function (exports) {
    var _private, _public,
        _self = {
            mousedown: function (event) {
                //console.log('resize mousedown - start');
                start = $(this);
                startResizeColumn = true;
                startX = event.pageX;
                startWidth = $(this).width();
                $(start).addClass("resizing");
            }
        };

    var pressed = false;
    var startX, startWidth, realWidth;

    _private = {};

    _public = {
        model: null,

        setModel: function(json) {
            this.model = json || {};
        },

        constructor: function () {
            this.events();

            return this;
        },
        collectJSON: function($table) {
            var json = {
                copy_id: 'listView_'+this.model.copy_id,
                value: {}
            };

            $.each($table.find('thead th[data-name]'), function (index, value) {
                var $this = $(this);
                json.value[$this.data().name] = parseInt($this.css('padding-left'))+$this.width() + parseInt($this.css('padding-right'));
            })

            return json;
        },
        saveData: function(data) {
            Api.history
                .createInstance()
                .setKey("list_th_width")
                .setCopyId(data.copy_id)
                .setData(data.value)
                .setUserStorage();
        },
        events: function () {
            this._events = [
                //{ parent: document,  event: 'click', func: _self.mousemove}, // save text by click on "Save"
                {
                    parent: document,
                    selector: 'table th',
                    event: 'mousedown',
                    func: _self.mousedown
                }
            ];

            this.addEvents(this._events, {
                instance: this
            });

            return this;
        },
    };

    $(document).mousemove(function (e) {
        if (startResizeColumn) {
            if (e.pageX > startX) {
                realWidth = startWidth + (e.pageX - startX);
            } else {
                realWidth = startWidth - (startX - e.pageX);
            }

            //console.log('realWidth', realWidth);
            // console.log('e.pageX', e.pageX);
            //$(start).width(startWidth + (e.pageX - startX));
            $(start).find('.table-handle').width(realWidth);
        }
    });

    $(document).mouseup(function () {
        if (startResizeColumn) {
            $(start).removeClass("resizing");
            startResizeColumn = false;

            TableColumnResize.updateColumnWidth($(start).closest('#list-table'));
        }
    });

    var TableColumnResize = {
        createInstance: function () {
            var Obj = function () {
                for (var key in _public) {
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return _self._instance = new Obj().constructor();
        },
        updateColumnWidth: function($parent){
            var json = _self._instance.collectJSON($parent || $('#list-table:visible'));
            _self._instance.saveData(json);

            niceScroll.clear();
            niceScrollInit();
        },
    }

    exports.TableColumnResize = TableColumnResize;
})(window);