
;(function (exports) {
    var _private, _public, _protected, Tools, _self; //link for instance


    _self = {
        instance: null,

        setInstance: function (instance) {
            this.instance = instance;

            return this;
        },
        getInstance: function () {
            return this.instance;
        }
    }

    _private = {
        onClickByAllChecked : function (e) {
            var label,
                $this = $(this),
                $listOfCheckboxes = $this.closest('.sm_extension_export').find('input[type="checkbox"]');

            if ($this.is('span.name')) {
                $this = $('[data-name="all-checked"]').prop('checked', true);
                label = true;
            } else {
                label = $this.is(':checked') ? true : false;
            }

            $listOfCheckboxes.not($this).prop('checked', label);
        },
        onClickCheckboxes : function () {
            $('.element[data-name="all-checked"]').prop('checked', false);
        },
        onClickByExporting : function () {
            var _this = $('.sm_extension_export'),
                fields = [],
                ids = [];

            $(_this).closest('.edit-view').find('input[type="checkbox"]:checked').each(function(i, ul){
                fields.push($(ul).data('name'));
            })

            $.each($('.sm_extension_data input:checked'),function(i, ul){
                ids.push($(ul).closest('.sm_extension_data').data('id'));
            });

            modalDialog.hide();

            var copy_id = $('.sm_extension').data('copy_id'),
                allChecked = $('#list-table thead .checkbox').prop('checked') ? 1 : 0;

            var params = 'all_checked='+allChecked+'&page_size=0&col_width=' + JSON.stringify(ListViewDisplay.getColumnWidth()) + '&col_hidden=&type=excel' + '&fields=' + JSON.stringify(fields) + '&ids=' + JSON.stringify(ids);
            if(document.location.search == '') {
                document.location.href = Global.urls.url_list_view_export + '/' + copy_id + '?' + params;
            } else {
                var url_params = Url.getWithOutParams(document.location.href, ['page_size'], true);
                document.location.href = Global.urls.url_list_view_export + '/' + copy_id + '?' + url_params + '&' + params;
            }
        },
        print: function (e) {
            e.data.instance.print();
        },
        saveToPdf: function (e) {
            e.data.instance.saveToPdf();
        },
        saveToExcel: function (e) {
            e.data.instance.saveToExcel();
        }
    };

    _public = {
        constructor: function () {
            this.events();

            return this;
        },
        events : function () {
            this._events = [
                { parent: document, selector: '.list_view_btn-print', event: 'click', func: _private.print},
                { parent: document, selector: '.list_view_btn-select_export_to_pdf', event: 'click', func: _private.saveToPdf},
                { parent: document, selector: '.list_view_btn-select_export_to_excel', event: 'click', func: _private.saveToExcel},

                { parent: document, selector: '.sm_extension_export .list_view_btn-export_to_excel', event: 'click', func: _private.onClickByExporting},
                { parent: document, selector: '.sm_extension_export tbody input.checkbox', event: 'show.bs.dropdown', func: _private.onClickCheckboxes},
                { parent: document, selector: '.sm_extension_export .element[data-name="all-checked"], .sm_extension_export thead td:first span', event: 'click', func: _private.onClickByAllChecked},
            ]

            Global.addEvents(this._events, {
                instance: this
            });
        },
        /**
         *   select fields before export
         */
        saveToExcel: function(e){
            var copy_id = $('.sm_extension').data('copy_id');
            $.ajax({
                url: Global.urls.url_list_view_select_export+'/'+copy_id + '?type=excel',
                type: "POST",
                dataType: 'json',
                success: function(data){
                    modalDialog.show(data.data, true);
                },
                error: function(xhr, ajaxOptions, thrownError){
                    Message.show([{'type':'error', 'message':xhr.responseText}], true);
                }
            });
        },
        saveToPdf: function (e) {
            var copy_id = $('.sm_extension').data('copy_id');
            $.ajax({
                url: Global.urls.url_list_view_select_export+'/'+copy_id + '?type=pdf',
                type: "POST",
                dataType: 'json',
                success: function(data){
                    modalDialog.show(data.data, true);

                },
                error: function(xhr, ajaxOptions, thrownError){
                    Message.show([{'type':'error', 'message':xhr.responseText}], true);
                }
            });
        },
        print: function (e) {
            var copy_id = $('.sm_extension').data('copy_id');
            var params = 'page_size=0&col_hidden=' + ListViewDisplay._hidden_group_index;
            if(document.location.search == '') {
                toPrint(Global.urls.url_list_view_print + '/' + copy_id + '?' + params);
            } else {
                var url_params = Url.getWithOutParams(document.location.href, ['page_size'], true);
                toPrint(Global.urls.url_list_view_print + '/' + copy_id + '?' + url_params + '&' + params);
            }
        },
    };

    Tools = {
        createInstance: function () {
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            _self.setInstance(new Obj().constructor()) ;

            return _self.getInstance();
        },
        getInstance : function () {
            return _self.getInstance();
        }
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.Tools = Tools;
})(window);
