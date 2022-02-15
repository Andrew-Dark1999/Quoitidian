;(function (exports) {
    var _private, _public, dateTimePopUp,
        _self = null; //link for instance


    _private = {
        events: function () {
            this._events = [
                { parent: document, selector: '.btn-ending-block .btn-save', event: 'click', func: this.onClickDateEndSave },
                { parent: document, selector: '.btn-ending-block .checkbox-line', event: 'click', func: this.onClickDateEndAllDay},
                { parent: document, selector: '.container-date-time', event: 'click', func: this.onClickDateTimePopup},
            ]
            Global.addEvents(this._events, {
                instance: _self
            });
        },
        onClickDateTimePopup : function (e) {
            var $this = $(this),
                $target = $(e.target),
                currentDate, $element, $timeBlock, currentFormat,
                instance = e.data.instance,
                $parent = $this.closest('.crm-dropdown'),
                $elementValue,
                modelGlobal = Global.getModel();

            currentFormat = modelGlobal.getCurrentFormatDate();

            instance
                .setParent($parent)
                .setTypeView();

            $elementValue = instance.getElementValue();

            instance.setElement($elementValue);

            currentDate = $elementValue.val().trim();
            $element = $parent.find('.element[data-type="calendar-place"]');
            $timeBlock = $parent.find('.time-block');
            $this.data().param = {};

            $this.find('.date-time').val();

            if ($target.parent().is('.checkbox-line') || $target.is('.checkbox-line') || $target.is('.description')) {
                return false; // next
            }

            if ($(e.target).is('span.element[data-type="title"]')) {
                return;
            }

            if ($parent.is('.open')) {
                $parent.toggleClass('open');
                return;
            }

            $parent.toggleClass('open');
            e.preventDefault();

            //change date if date is yesterday
            if (modelGlobal.date.diffToDay($elementValue.val()) > 0) {
                currentDate = modelGlobal.date.getNow();
            }

            instance.calcPosition();

            if ($element && !$element.data().datepicker) {
                instance
                    .setCurrentFormat(modelGlobal.getCurrentFormatDate()+':ss')
                    .setCurrentDate(currentDate ? currentDate : moment().format(currentFormat))
                    .setTimer($elementValue.data('all_day') ? false : true)
                    .parseDate(currentDate)
                    .init();

                Events
                    .createInstance()
                    .setType(Events.TYPE_EVENT_SCROLL)
                    .setKey('datetime')
                    .setHandler(function () { $parent.removeClass('open');})
                    .run();
            }


            instance.setDefaultParam({
                'time' : $timeBlock.data().timepicker.getTime()
            });
        },
        onClickDateEndSave : function(e) {
            var $value, date, value,
                time = '',
                $this = $(this),
                instance = e.data.instance,
                $dropdown = $this.closest('.crm-dropdown'),
                $checkAllDay = $dropdown.find('#ckbAllDay'),
                modelGlobal = Global.getModel(),
                $element = $dropdown.find('.container-date-time');

            $value = instance._element;

            if ($checkAllDay.is(':checked')) {
                $value.data('all_day', 1);
            } else {
                time = ' ' + $dropdown.find('.time-block').data().timepicker.getTime();
                $value.data('all_day', 0);
            }

            date =  $value.data().newDate || instance._currentDate.split(' ')[0];

            value = date + time;
            //change date if date is майбутньому
            if (time) {
                if (modelGlobal.date.diffCurrentDateToMinutes(value) < 0) {
                    $element.removeAttr('datetime');
                }
            } else {
                if (modelGlobal.date.diffToDay(value) <= 0) {
                    $element.removeAttr('datetime');
                }
            }

            $value.val(value);
            $dropdown.removeClass('open').closest('.data_edit').addClass('editing');
        },
        onClickDateEndAllDay : function(e) {
            var $this = $(this),
                instance = e.data.instance,
                $input = $this.parent().find('[type="checkbox"]');

            if (!$(e.target).is('input')) {
                $input.prop('checked', $input.is(':checked') ? false : true);
                e.preventDefault();
                e.stopPropagation();
            }

            instance.setTimer($input.is(':checked') ? false : true);
        },
    };

    _public = {
        TYPE_VIEW_INLINE_EDIT: 'inline-edit',
        TYPE_VIEW_EDIT_VIEW: 'edit-view',

        _date: null,
        _time: null,
        _parent:null,
        _element : null,
        _outputDate : null,
        _datepicker: null,
        _timepicker: null,
        _currentDate: null,
        _view: null,
        _timer: false,

        isInlineEdit: function () {
            return this._view == this.TYPE_VIEW_INLINE_EDIT ? true : false;
        },
        setCheckboxAllDay : function () {
            this._parent.find('#ckbAllDay').prop('checked', (this._timer) ? false : true);

            return this;
        },
        setTypeView: function () {
            this._view = this._parent.data('type');

            return this;
        },
        getElementValue: function () {
            return this._parent.find('.date-time');
        },
        setOutputDate : function (date) {
            this._outputDate = date;

            return this;
        },
        setCurrentFormat : function (format) {
            this._currentFormat = format;
            this._currentFormat = format;

            return this;
        },
        setElement : function (_element) {
            this._element = _element ? _element : null;

            return this;
        },
        setTimer : function (bool) {
            var $timeBlock = this._parent.find('.time-block');

            if (bool) {
                this._timer = true;
                $timeBlock.removeClass('disable');
            } else {
                this._timer = false;
                $timeBlock.addClass('disable');
            }

            return this;
        },
        setDefaultParam : function (json) {
            _self._element.data(json);
            _self._element.data({
                'currentDate': _self._currentDate
            })
            return this;
        },
        setParent : function (_item) {
            this._parent = _item;

            return this;
        },
        setCurrentDate: function (date) {
            _self._currentDate = date;

            return this;
        },
        parseDate : function (date) {
            var arr = date.split(' ');

            this._date = arr[0];
            this._time = arr[1];

            return this;
        },
        calcPosition: function () {
            var top, left,
                $element = this._parent.find('.btn-ending-block'),
                heightContainer = $element.height(),
                widthContainer = $element.width(),
                offset = 37;

            top = this._parent.offset().top;
            left = (this._parent.offset().left - ListView.getScrollLeft());

            if (this.isInlineEdit()) {
                top -= $(window).scrollTop() - offset;

                this._parent.find('.dropdown-menu').css({
                    top: $element.offset().top + 37,
                    left: $element.offset().left
                });
                if ($(window).height() < (top + heightContainer)) {
                    top -= (heightContainer + 14 + offset);
                }
            } else {
                top = offset;
                left = 0;
            }

            if ($(window).width() < (left + widthContainer)) {
                left = $(window).width() - (widthContainer + 40);
            } else {};

            $element.css({
                top: top,
                left: left
            });

            return this;
        },
        init: function () {
            var fOpened,
                $parent = _self._parent,
                $element = $parent.find('.element[data-type="calendar-place"]'),
                $timeBlock = $parent.find('.time-block'),
                modelGlobal = Global.getModel(),
                _this = this;

            fOpened = function () {
                $('body').data().eventClick = Global.DISABLE;
                $parent.addClass('opened');

                var time = setTimeout(function(){
                    clearTimeout(time);
                    $parent.addClass('open').removeClass('opened');
                }, 300);
            };


            $element.datepicker({
                'format' : 'mm/dd/yyyy',
                'autoclose': true,
                'startDate': new Date(),
                'language': Message.locale.language,
                'locale': Message.locale.language,
                'inline': true
            }).on('changeDate', function (e) {
                var date = moment(e.date),
                    json = _self._element.data();

                json['currentDate'] = date.format(_this._currentFormat);
                json['newDate'] = date.format(_this._currentFormat).split(' ')[0];

                $('body').data().eventClick = Global.DISABLE;

                return true;
            })
                .on('clearDate', function (e) {
                    fOpened();

                    return true;
                })
                .on('changeYear', function (e) {
                    fOpened();

                    return true;
                }).on('changeMonth', function (e) {
                fOpened();

                return true;
            }).on('changeDecade', function (e) {
                fOpened();

                return true;
            }).on('changeCentury', function (e) {
                fOpened();

                return true;
            });


            $timeBlock.timepicker({
                'inline': true,
                minuteStep: 5,
                showMeridian: false,
                defaultTime: this._timer ? this._time : '00:00',
                enableOnReadonly: true
            });


            _self.setCheckboxAllDay();

            $timeBlock.data().timepicker.$widget.appendTo($timeBlock);

            _self._timepicker = $timeBlock.data().timepicker;
            _self._datepicker = $element.data('datepicker');
            _self._datepicker.update(moment(this._currentDate, modelGlobal.getCurrentFormatDate()).format('MM/DD/YYYY'));

            _self._datepicker.show();
            _self._datepicker.hide = function () {
                return true;
            };

            return this;
            // output value
            //$item.val(moment(currentDate, crmParams.getCurrentFormatDate()).format(crmParams.FORMAT_DATE));
        }
    };

    dateTimePopUp = function () {
        _self = this;

        for(var key in _private) {
            _self[key] = _private[key];
        }

        _private
            .events();

        for(var key in _public) {
            this[key] = _public[key];
        }

        return this;
    };

    exports.dateTimePopUp = dateTimePopUp;
})(window);
