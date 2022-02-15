;(function (exports) {
    var ModelGlobal, _private, _public, _protected,
        _self = {};

    var Money = {
        _phrase: null,
        createInstance : function(){
            var Obj = function(){
                for(var key in Money){
                    this[key] = Money[key];
                }
            }
            return new Obj();
        },
        _separateToGroup: function (value) {
            var value = value || this._phrase;

            if (!value.length) return null;

            var balance, delta, group;

            balance =value.length % 3;
            if (balance > 0) {
                delta =value.substring(balance);
            } else {
                delta =value;
            }

            group = delta.match(/.{1,3}/g);
            delta = (balance > 0 ) ? value.substring(0, balance) : '';
            delta = delta +(delta.length && group ? ' ': '');

            value = delta + (group ? group.join(' ') : '');
            value = value.replace(' .', '.');

            return value;
        },
        setValue: function (string) {
            this._phrase = string;

            return this;
        },
        concat: function (string) {
            var i = 30,
                phrase = string || this._phrase || '';

            while (i > 0) {
                if (phrase.indexOf(' ') >= 0) {
                    phrase = phrase.replace(' ', '');
                } else {
                    i = 1;
                }
                i--;
            }
            return phrase;
        },
        //group count symbols;
        //remove empty space
        groupString : function (string) {
            var value, arr;

            value = this
                .createInstance()
                .setValue(string)
                .concat()

            if (value.length) {
                arr = value.split('.');
                value = this._separateToGroup(arr[0]);
                value = value + (arr.length == 2 ? '.'+ arr[1]: '');
            }

            return value;
        },
        groupSymbols : function ($list) {
            //group count symbols;
            var _this = this;

            $.each($list, function () {
                var value,
                    $this = $(this);

                value = _this.groupString($this.val());
                $this.val(value);
            });

            return this;
        },
        //перевіряє на заборонений символ
        checkForbiddenSymbol: function (e) {
            var r = false;

            if (($.inArray(e.keyCode, [16, 192]) >= 0) || e.ctrlKey || e.altKey || e.shiftKey
                || e.key == '/' || e.key == '>' || e.key == '<' || e.key == 'ю') {
                r = true;
            }

            return  r;
        },
        typingKeyUp: function (e) {
            var arr, json, modelValue,
                offset = 1,
                $this = $(e.target),
                value = $this.val(),
                caret = $this.data('caret');

            value = value.replace(',','.');
            arr = value.split('.');

            if (!arr[0].length) return;
        },
        typingKeyDown: function (e) {
            var $this = $(e.target),
                countOfpoints, positionPoint,
                value = $this.val();

            positionPoint = value.indexOf('.');
            countOfpoints = (value.match(/[.]/g) || []).length;

            var arr = value.split('.');
            $this.data('model', {
                value: value,
                empty: (value.match(/[ ]/g) || []).length
            });
            $this.data('caret', $this.caret());

            if (this.checkForbiddenSymbol(e) || (e.keyCode == 110 && countOfpoints)) {
                return false;
            }

            //заборонені символи - :) нижче наведено дозволений список, дивись умовуу праворуч.
            if ($.inArray(e.keyCode, [8, 32, 35,36, 37, 39, 46, 48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105,108, 110, 188, 190, 191]) < 0) {
                return false;
            }

            if ($.inArray(e.keyCode, [8, 35,36, 37, 39, 46, 48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105, 190, 191]) >=0) {
                if ($.inArray(e.keyCode, [8,35,36,37,39,46]) >= 0 ) return true;

                if ($this.is('.add_hundredths')) {
                    if (arr.length == 2 && arr[1].length == 2 && $this.caret().begin > positionPoint && $this.caret().begin == $this.caret().end)
                    return false; //stop
                }
            }

            return true;
        },
        // typingKeyUp: function (e) {
        //     var arr, json, modelValue,
        //         offset = 1,
        //         $this = $(e.target),
        //         value = $this.val(),
        //         caret = $this.data('caret');
        //
        //     value = value.replace(',','.');
        //     arr = value.split('.');
        //
        //     if (!arr[0].length) return;
        //
        //     json = this.groupString(arr[0]);
        //     value = json.value + (arr.length == 2 ? '.'+ arr[1]: '');
        //
        //     $this.val(value);
        //     modelValue = $this.data('model');
        //
        //     if (modelValue.empty != (value.match(/[ ]/g) || []).length) {
        //         offset = 2;
        //     }
        //
        //     if ($.inArray(e.keyCode, [35,36,37,39]) < 0) {
        //         offset = (e.keyCode == 46) ? 0 : offset;
        //         offset = (e.keyCode == 8) ? 0 : offset;
        //         caret.begin = (e.keyCode == 8) ? caret.begin - 1 : caret.begin;
        //         $this.caretTo(caret.begin + offset);
        //     }
        // },
    }

    var _Date = {
        //server Format: 'YYYY-MM-DD HH:mm:ss',
        //різниця в днях:
        // -X - в майбутньому
        // 0 - сьогодні
        // X - в минулому
        diffToDay: function (date) {
            return moment().diff(moment(date, Global.getModel().getCurrentFormatDate()), 'days'); //days
        },
        //with time
        // -X - в майбутньому
        // 0 - сьогодні
        // X - в минулому
        diffCurrentDateToMinutes: function (date) {
            return moment().diff(moment(date, Global.getModel().getCurrentFormatDate()), 'minutes'); //days
        },
        getNow: function (format) {
            var value;

            if (format) {
                value = moment().format(format);
            } else {
                value = moment().format(Global.getModel().getCurrentFormatDate());
            }

            return value;
        },
        getPeriodToWeek: function (date) {
            var from, to, cell,
                momenOfCurrentDate = moment(date),
                isUnix = Locale.isEnglish();

            if (isUnix) {
                cell = momenOfCurrentDate.day();
            } else {
                cell = momenOfCurrentDate.day() - 1;
            }

            from = momenOfCurrentDate.add(-cell, 'days');
            to = moment(from.format(_self.FORMAT_DATE)).add(+6, 'days');

            return {
                from: from.format(_self.FORMAT_DATE),
                to: to.format(_self.FORMAT_DATE),
            }
        },
        toServerFormat: function (date, format) {
            return moment(date, format || _self.getCurrentFormatDate()).format(_self.FORMAT_DATE)
        },
        toMoment: function (date, format) {
            return moment(date, format);
        },
        //date format server
        getFirstDate: function (date) {
            var arrOfDate = date.split('-'),
                value = arrOfDate[0]+'-'+arrOfDate[1]+'-01';

            return value;
        },
        getDayOfWeek: function (firstDateOfMonth) {
            return moment(firstDateOfMonth).day();
        },
        getNextDay: function (date) {
            return moment(date).add(1, 'days').format(_self.FORMAT_DATE);
        },
        getNextMonth: function (date) {
            return moment(date).add(1, 'month').format(_self.FORMAT_DATE);
        },
        getNextYear: function (date) {
            return moment(date).add(1, 'year').format(_self.FORMAT_DATE);
        },
        //format date is server Date;
        // format is output,
        getPrevDate: function (date, outFormat) {
            var prevDate,
                _moment = moment(date),
                day = Number(_moment.format('DD')),
                format = outFormat || _self.FORMAT_DATE;

            if (day == 1) {
                prevDate = _moment.subtract(1,'months').endOf('month').format(format);
            } else {
                prevDate = _moment.subtract(1, 'days').calendar().format(format)
            }

            return prevDate;
        },
        getLastDate: function (date) {
            return moment(date).endOf('month');
        },
        getLastDay: function (date) {
            return moment(date).endOf('month').format('D');
        },

    }
    var Locale = {
        isEnglish: function () {
            //return (_self.locale.language == _self.LOCALE_EN) ? true : false;
            return (Message.locale.language == Message.LOCALE_EN);
        },
    }

    _private = {
        is_auth: true,
        list : null,
        urls: null, // list of urls all request
        instance: null,

        _name_model_object: 'modelGlobal', // name for save object in data object DOM
    };

    _protected = {
        getData : function(string_date_time) {
            if (!_self.date_time) {
                string_date_time = string_date_time.split(' ')[0];
            }

            return string_date_time;
        },
        getCurrentFormatDate : function () {
            debugger
            return _self['FORMAT_DATE_END_' + _self.locale.language.toUpperCase()];
        },
        showReadOnlyFields : function ($element) {
            var object = this,
                arrayOfReadonlyFields = [];

            if ($element.is('.editing')) {
                object.elements = inLineEdit.elements;
            } else {
                if ($element.is('.edit-view')) {
                    object.elements = {};
                    var modelEditView = EditView.getModel();
                    arrayOfReadonlyFields = modelEditView.readonly;

                    if (!arrayOfReadonlyFields.length) { return; }
                }
            }

            $.each($element.find('[name*="EditViewModel"]'), function (key, data) {
                var $data = $(data),
                    name = $data.attr('name');

                if ($data.is('.upload_file')) { //$data.closest('#list-table').length &&
                    $data = $data.closest('.file-box')
                    name = $data.data('name')

                    if (!name) { return; } // continue
                }

                name = name.substring(name.indexOf('[')+1, name.indexOf(']'));

                if (name in object.elements && object.elements[name].readonly
                    || $.inArray(name, arrayOfReadonlyFields)>=0) {
                    $data.addClass('readonly');
                    if ($data.is('.time') || $data.is('.date')) {
                        $data.closest('.form-datetime').addClass('readonly readonly-container');
                    }
                    if ($data.filter('input').parent().is('.data_edit')) {
                        $data.wrap('<div class="readonly-container"></div>')
                    }
                    if ($data.filter('select').length) {
                        $data.next().addClass('readonly-container');
                    }
                    if ($data.parent().is('[data-type="drop_down"]') || $data.closest('.column').length) {
                        $data.parent().addClass('readonly-container');
                    }
                    if ($data.is('.file-box')) {
                        $data.addClass('readonly-container');
                    }
                }
            })
        },
    }

    _public = {
        FORMAT_DATE : 'YYYY-MM-DD HH:mm:ss',
        FORMAT_DATE_END_RU : 'DD.MM.YYYY HH:mm',
        FORMAT_DATE_END_EN : 'MM/DD/YYYY hh:mm A',
        FORMAT_DATE_END_ES : 'DD.MM.YYYY HH:mm',
        date_time: false, // true - show date & time // false - show date;

        money: Money,
        date: _Date,
        locale: Locale,

        getCurrentFormatDate : function () {
            return _self['FORMAT_DATE_END_' + _self.locale.language.toUpperCase()];
        },
        isAuth: function () {
            return _self.is_auth ? true : false;
        },
        setAuth: function (bool) {
            _self.is_auth = bool;

            return this;
        },
        saveUrls: function (json) {
           this.urls = json;

           return this;
        },

        getUrl: function (key) {
            return _self.urls[key];
        },
        translate: function () {
            throw new Error('There is not implementation');
        },
        getQuickViewBlocks: function () {
            return this.quick_view_blocks;
        },

        copyData: function (model, callback) {
            $.post(Global.urls.url_list_view_copy + '/' + model.copy_id, model.data, function(data){
                if (data.status != true) {
                    Message.show(data.messages, false);
                }
                callback(data);
            }, 'json');
        },

        removeData: function (model, callback) {
            AjaxObj
                .createInstance()
                .setData(model.data)
                .setAsync(false)
                .setUrl(Global.urls.url_list_view_delete + '/' + model.copy_id)
                .setTimeOut(0)
                .setCallBackSuccess(function(data){
                    if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else {
                        callback(data);
                    }
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                })
                .send();
        }
    }

    //public property
    ModelGlobal = {
        locale : null,
        global : null,
        startup_guide: null,
        templates: null,
        quick_view_blocks: null,
        money_type: 3,

        createInstance : function(params){
            if (!params) return;

            var Obj = function(){
                for(var key in ModelGlobal){
                    this[key] = ModelGlobal[key];
                }
            }

            var instance;

            instance = _self.instance =  new Obj();

            instance.global = params.global;
            instance.startup_guide = params.startup_guide;
            instance.templates = params.templates;
            _self.list = params.list;
            _self.locale = params.locale;
            instance.quick_view_blocks = params.quick_view_blocks;

            //save
            $('[data-model-global]').data(_self._name_model_object, instance);
            return instance;
        },
        getInstance: function () {
            return _self.instance;
        }
    };

    for(var key in _public) {
        ModelGlobal[key] = _public[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in _protected) {
        _self[key] = _protected[key];
    }

    for(var key in ModelGlobal) {
        _self[key] = ModelGlobal[key];
    }


    ModelGlobal.protected = _protected;
    exports.ModelGlobal = ModelGlobal;
})(window);
