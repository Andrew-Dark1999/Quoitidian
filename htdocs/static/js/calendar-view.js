/**
/**
 * Created by andrew on 9/22/17.
 */
(function(exports) {
    var _public, CalendarView, _private, View, TransferOfCards, publicTransfer, TransferModel,
        _self = {};
    var state = Object.create(null);
    _private = {
        _instance: null,
        store_data: null,

        events: function () {
            this._events = [
                { parent: document, selector: '.element[data-type="sort-period"] .btn', event: 'click', func: this.onChangePeriod},
                { parent: document, selector: '.calendar-block .btn-left, .calendar-block .btn-right, .calendar-block .btn-today', event: 'click', func: this.onGetPeriod},
                { parent: document, selector: '.element[data-controller="ev"]', event: 'click', func: this.onClickEditCard},
                { parent: document, selector: 'select[data-type="active_field_name"]', event: 'change', func: this.onChangeSortWay},
                { parent: document, selector: '.fc-widget-content', event: 'click', func: this.onAddCard},
                { parent: document, selector: '.fc-widget-content .element[data-type="else"]', event: 'click', func: this.onGetElseTasks},
            ]

            Global.addEvents(this._events, {
                instance: _self._instance
            });
            return this;
        },
        onGetElseTasks: function (e) {
            var instance = e.data.instance,
                currentDate = $(this).closest('td').data().date;

            e.preventDefault();
            e.stopPropagation();

            instance
                .setPreloader(true)
                .getDataByPeriod({
                    data: {
                        period: CalendarView.PERIOD_DAY,
                        date_time: currentDate,
                        active_field_name: instance.data.general.date_time_field_name
                    },
                    change: true
                })
        },
        onAddCard: function (e) {
          var $this= $(this),
              $target = $(e.target),
              instance = e.data.instance;

          if (!$this.find('[data-type="day-content"] [data-type="card"]').length || !$target.closest('.fc-event').length) {
              //new card;
              var ev = EditView.createInstance().setCopyId(instance.getCopyId());
              instance.reDefinitionEVPreloader(ev);

              var data = {};
              var general = instance.data.general;
              if (general && !general.update_disallow) {
                var key = general.date_time_field_name;
                data[key] = $this.data()['date'];
                if (general.has_all_day) {
                    data[key + '_ad'] = $this.closest('tr').data('all_day') || 0;
                }
              }
              Global.getInstance().addCard(this, {
                  default_data : data
              }, ev);
          }
        },
        onChangeSortWay: function (e) {
            var value = e.currentTarget.value
            var instance = e.data.instance; 
            instance
                .setPreloader(true)
                .setAbstractKey(value)
                .prepareDataToRequest(instance._period_type)
                .setRequestDataToStore()
                .sendRequest()
        },
        onClickEditCard : function (e) {
            var instance = EditView.createInstance().setParent(ViewType.getCurrentInstance());
            e.data.instance.reDefinitionEVPreloader(instance);

            Global.getInstance().editCard(this, instance);
        },
        onGetPeriod : function (e) {
            var value,
                instance = e.data.instance,
                $this = $(this);

            value = $this.is('.btn-left') ? CalendarView.CONST_LEFT :
                    $this.is('.btn-right') ? CalendarView.CONST_RIGHT :
                    $this.is('.btn-today') ? CalendarView.CONST_TODAY : '';

            Global.getInstance().setStatusCash(false);

            instance.history.setHistoryStateMode(true);

            instance
                .setPreloader(true)
                .setAbstractKey(value)
                .prepareDataToRequest(instance._period_type)
                .setRequestDataToStore()
                .sendRequest()
        },
        onChangePeriod : function (e) {
            var instance = e.data.instance,
                $this = $(this);

            Global.getInstance().setStatusCash(false);

            instance.history.setHistoryStateMode(true);

            instance
                .setCurrentDate(instance.model_global.date.getNow(instance.model_global.FORMAT_DATE))
                .setPeriod($this.data('type'))
                .setPreloader(true)
                .setAbstractKey(instance._period_type)
                .prepareDataToRequest()
                .setRequestDataToStore()
                .sendRequest()
        },
    }
    _public = {
        _type : 'CalendarView',
        hash: '',
        url_params: null,

        FORMAT_DATE: null,
        FORMAT_DAY: 'DD',
        FORMAT_MONTH: 'MM',
        title_module: '',

        preloader: null,
        content_reload: null,
        resource_template: null,

        _template: null,
        _parent: null,
        _period_type: null,
        _title_element: null,
        _original_template: null,
        _current_module: null,
        _data_request: null,
        _list_later_by_update: null,
        _abstract_key: null,
        _operations: null, //Operations: filter / search

        constructor: function() {
            var _this = this;

            iAction.implements.call(this);

            this.model_global = Global.getModel();
            this.preloader = Global.getInstance().getPreloader();
            this.FORMAT_DATE = Global.getModel().FORMAT_DATE;
            this.ajax_instance = AjaxObj.createInstance();

            if (!this.preloader) {
                this.preloader = Preloader.createInstance();
            }

            iModule.implements.call(this);
            iTimeStamp.implements.call(this);
            iBackForwardHistory.implements.call(this);

            _self.events();
            this.setTimeStamp()
                .setEmits();

            this.search = Search.createInstance();
            this.history = History.createInstance();

            this.initFilter()
                .reDefinition();

            this.ajax_instance.setCallBackComplete(function () {
                _this.preloader
                    .setPriorityDisable(true)
                    .hide();

                var json, str,
                    url = Url.createInstance();

                //http://crm.local/module/calendarView/show/260#        - 1 on cell for BackForward
                //http://crm.local/module/calendarView/show/260#180818  - 2 on cell for BackForward

                url.setUrl(location.href);

                if (_this.hash) {
                    var _data = {},
                        json = url.getParams();

                    if (json && json.search) {
                        _data['search'] = json.search;
                    }
                    if (json && json.finished_object) {
                        _data['finished_object'] = json.finished_object;
                    }

                    var urlParams = _this.getUrlParams();
                    if (urlParams) {
                        for(var key in urlParams) {
                            _data[key] = urlParams[key];
                        }
                    }

                    _data['hash'] = _this.hash;

                    str = url
                        .jsonToUrl(_data)
                        .getUrl();

                    _this.history.setHash(str);
                    _this.setUrlParams(null);
                }

                Events.runHandler(Events.TYPE_SNAPSHOT);

                //Костиль при переході з інших модулів.
                Preloader.hideAll();
                _this.resource_template = null;
                _this._operations = null;
                TransferOfCards.createInstance();
            });

            this
                .prepareFromDataStorage()
                .setAbstractKey(this._period_type)
                .prepareDataToRequest()
                .setRequestDataToStore();

            return this;
        },
        getPreloader: function() {
            return this.preloader;
        },
        setUrlParams: function (json) {
            if (json) {
                if (!this.url_params) {
                    this.url_params = {};
                }

                for(var key in json) {
                    this.url_params[key] = json[key];
                }

            } else {
                this.url_params = null;
            }
            return this;
        },
        getUrlParams: function () {
            return this.url_params;
        },
        initFilter: function () {
            var _this = this;

            this.filter = Filter.createInstance();
            this.filter.beforeApply = function () {
                _this.setTypeAction(iAction.TYPE_FILTER)
                CalendarView.saveTemplate();

                return this;
            };

            return this;
        },
        prepareFromDataStorage: function () {
            this.dataStorage = this.getDataStorage(this._type);

            if (!this.dataStorage) {
                this._period_type = CalendarView.PERIOD_MONTH;
                this.current_date = this.model_global.date.getNow(this.model_global.FORMAT_DATE);
            } else {
                this._period_type = this.dataStorage._period_type;
                this.current_date = this.dataStorage.current_date;
            }

            return this;
        },
        setAbstractKey: function (key) {
            this._abstract_key = key;

            return this;
        },
        setCurrentDate: function (date) {
            this.current_date = date;

            return this;
        },
        setDataStorage: function (data) {
            this.dataStorage = sessionStorage.setItem(this._type, JSON.stringify(data));
        },
        getDataStorage: function () {
            return JSON.parse(sessionStorage.getItem(this._type));
        },
        // Ссылка вказывает на шаблон в DOM || строку если у нас предварительная настройка.
        setResourceTemplate: function(resource_template) {
            this.resource_template = resource_template;

            return this;
        },
        setEmits: function () {
            var _this = this;

            Events
                .createInstance()
                .setType(Events.TYPE_UPDATE_DATA)
                .setKey('CalendarUpdateDate')
                .setHandler(function (e, data) {
                    var $target,
                        status = true;

                    if (e) {
                        $target = $(e.target);
                    }

                    _this._is_update = true;
                    _this.preloader.setRunning(false).show();

                    _this.setAbstractKey(_this._period_type)
                        .prepareDataToRequest(CalendarView.CONST_STORE)

                    // open card after update content;
                    if ($target && $target.is('.edit_view_btn-copy')) {
                        _this._data_request.callback = function () {
                            var _model,
                                EV = EditView.createInstance();;

                            _model = {
                                data: {
                                    copy_id: _this.getCopyId(),
                                    id: data.id[0],
                                    primary_entities : EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false)),
                                    pci : 0,
                                    pdi : 0
                                },
                                callback: function (data) {
                                    EV.runAfterAddCardLV(data);
                                }
                            };

                            EV.editCardByParam(_model);
                        }
                    } else {
                        delete _this._data_request.callback;
                    }

                    _this.sendRequest();

                    return status;
                })
                .run();

            Events
                .createInstance()
                .setType(Events.TYPE_DESTROY)
                .setKey('CalendarDestroy')
                .setHandler(function (e) {
                    _this.destroy();
                    return true;
                })
                .run();

            Events
                .createInstance()
                .setType(Events.TYPE_WINDOW_LOAD)
                .setKey('CalendarWindowLoad')
                .setHandler(function (e) {
                    var instanceEV = modalDialog.getInstanceFromStorage('EditView');

                    _this.reDefinitionEVPreloader(instanceEV);

                    return true;
                })
                .run();

            Events
                .createInstance()
                .setType(Events.TYPE_EVENT_RESIZE)
                .setKey('CalendarResize')
                .setHandler(function () {
                    TransferOfCards.createInstance();

                    return true;
                })
                .run();

            TransferOfCards.createInstance();
        },
        setHash: function (hash) {
            this.hash = hash;

            return this;
        },
        setRequestDataToStore: function (data) {
            _self.store_data = data || this._data_request;

            return this;
        },
        getRequestDataFromStore: function () {
            return _self.store_data;
        },
        reDefinitionEVPreloader: function (ev_instance) {
            var _this = this;

            if (ev_instance) {
                ev_instance
                    .setPreloader(_this.preloader)
                    .setParent(_this)
                    .getParent();

                ev_instance.getParent().showPreloader = ev_instance.showPreloader = function () {
                    _this.showPreloaderTemplate.call(_this.preloader.setRunning(false));

                    return this;
                };
            }
        },
        afterLoadView : function () {
            ViewType.afterLoadView();
            
            return this;
        },
        prepareDataToRequest: function(data) {
            var model,
                key = this._abstract_key;

            switch (data) {
                case CalendarView.CONST_STORE: {
                    model = this.getRequestDataFromStore();

                    key = false; // exit :)
                    break;
                }
                default: break;
            }

            switch (key) {
                case CalendarView.CONST_MONTH: {

                    this.dataStorage = this.getDataStorage(this._type);

                    if (!this.dataStorage) {
                        this._period_type = CalendarView.PERIOD_MONTH;
                        this.current_date = this.model_global.date.getNow(this.model_global.FORMAT_DATE);
                    } else {
                        this._period_type = this.dataStorage._period_type;
                        this.current_date = this.dataStorage.current_date;
                    }

                    date = this.model_global.date.getFirstDate(this.current_date);
                    var modelDate = this.getRangeByDate();

                    model = {
                        data: {
                            period: this._period_type,
                            date_time_from: modelDate[0].date,
                            date_time_to: modelDate[modelDate.length-1].date
                        }
                    };

                    this.setDataStorage({
                        _period_type: this._period_type,
                        current_date: this.current_date
                    });

                    break;
                }
                case CalendarView.CONST_WEEK:
                case CalendarView.CONST_DAY: {
                    break;
                }
                case CalendarView.PERIOD_MONTH: {
                    var modelDate = this.getRangeByDate();

                    model = {
                        data: {
                            period: this._period_type,
                            date_time_from: modelDate[0].date,
                            date_time_to: modelDate[modelDate.length-1].date
                        },
                        change: true
                    }

                    this.setDataStorage({
                        _period_type: this._period_type,
                        current_date: this.current_date
                    });

                    break;
                }
                case CalendarView.CONST_TODAY: {
                    this.current_date = this.model_global.date.getNow(this.model_global.FORMAT_DATE);

                    break;
                }
                case CalendarView.CONST_LEFT: {
                    this.current_date = moment(this.current_date).add(-1, this._period_type).format(this.FORMAT_DATE);

                    model = {
                        data: {
                            period: this._period_type,
                            date_time: moment(this.current_date).format(this.FORMAT_DATE)
                        },
                        change: true
                    };

                    break;
                }
                case CalendarView.CONST_RIGHT: {
                    this.current_date = moment(this.current_date).add(1, this._period_type).format(this.FORMAT_DATE);

                    model = {
                        data: {
                            period: this._period_type,
                            date_time: moment(this.current_date).format(this.FORMAT_DATE)
                        },
                        change: true
                    };
                    break;
                }
                case CalendarView.PERIOD_WEEK: {
                    var date = this.model_global.date.getPeriodToWeek(this.current_date).from;

                    model = {
                        data: {
                            period: this._period_type,
                            date_time: date
                        }
                    };

                    this.setDataStorage({
                        _period_type: this._period_type,
                        current_date: model.data.date_time
                    });

                    break;
                }
                case CalendarView.PERIOD_DAY: {
                    model = {
                        data: {
                            period: this._period_type,
                            date_time: this.current_date
                        }
                    };

                    this.setDataStorage({
                        _period_type: this._period_type,
                        current_date: this.current_date
                    });

                    break;
                }

                default: break;
            }

            switch (this._period_type) {
                case CalendarView.PERIOD_MONTH:{
                    var modelDate = this.getRangeByDate();

                    model = {
                        data: {
                            period: this._period_type,
                            date_time_from: modelDate[0].date,
                            date_time_to: modelDate[modelDate.length-1].date
                        }
                    };

                    break;}
                case CalendarView.PERIOD_WEEK: {
                    var date = this.model_global.date.getPeriodToWeek(this.current_date).from;

                    model = {
                        data: {
                            period: this._period_type,
                            date_time: date
                        }
                    };
                    break;
                }
                case CalendarView.PERIOD_DAY:{
                    model = {
                        data: {
                            period: this._period_type,
                            date_time: moment(this.current_date).format(this.FORMAT_DATE)
                        },
                        change: true
                    };
                    break;
                }
                default: break;
            }

            if (model) {
                model.data.active_field_name = $('[data-type="active_field_name"]').val();
            }

            this._data_request = model;

            return this;
        },
        sendRequest: function () {
            var v = this._data_request || this.prepareDataToRequest(),
                data = this._data_request;

            switch (this._period_type) {
                case CalendarView.PERIOD_MONTH: {
                    this.getDataByDateTimeRange(data);

                    break;
                }
                case CalendarView.PERIOD_WEEK:
                case CalendarView.PERIOD_DAY: {

                    this.getDataByPeriod(data);
                    break;
                }
                default: { break;}
            }
        },
        //Переопределение
        reDefinition: function () {
            var _this = this;

            this.search.apply = function () {
                //filter by search;
                var json, url,
                    oldSearch = Search.parseUrl();

                _this.history.setHistoryStateMode(false);

                if (oldSearch) {
                    _this.history.replaceState('search='+oldSearch, this.getParam());
                } else {
                    json = this.getParamAsJson();

                    url = Url.createInstance().setUrl(location.href);
                    url.jsonToUrl(json);

                    _this.history.setHash(url.getUrl());
                }

                _this.sendRequest();

                return this;
            };

            this.showPreloaderTemplate = function () {
                var $list = $('.calendar-block .fc-content');

                this.setRunning(false) //!!! статус прокрутки обнуляє.
                    .setAddClass('html', 'hide-edit-view')
                    .setElement('html', ['hide-edit-view'])
                    .setSpinnerPosition(Preloader.POSITION_SPINNER_CONTENT)
                    .setWhereContentHide(Preloader.TYPE_VIEW_CALENDAR)
                    .setCssPositionSpinner(Preloader.css.FIXED)
                    .setPlaceForSpinner($list)
                    .run();
            };

            this.updateProperties = function (data) {
                this.setCopyId(data.copy_id);
                this._period_type = data._period_type;
                this._original_template = $('.calendar_view_block section.panel');
                this._title_element = $('.element[data-type="period"]');
                this.hash = location.hash.split('?')[0];
                this.store_data = null;
                this._data_request = null;
                this._period_type = data._period_type;
                this.current_date = data.current_date;

                this.search.updateProperties(data.search);
                this.filter.updateProperties(data.filter);

                this.setAbstractKey(this._period_type)
                    .reDefinition();

                return this;
            };

            this.preloader.show = function () {
                if (!this.isRunning()) {
                    _this.showPreloaderTemplate.call(_this.preloader);
                }
                return this;
            };

            this.search.showPreloader = function () {
                _this.showPreloaderTemplate.call(_this.preloader);
                return this;
            };

            this.filter.showPreloader = function () {
                _this.showPreloaderTemplate.call(_this.preloader);
                return this;
            };

            return this;
        },
        //запусткається коли юрл замінився, прелоадер ще крутиться.
        run: function () {
            this.parseUrlByCopyId();

            ViewType.init(this);

            this._title_element = $('.element[data-type="period"]');

            if (!Global.getInstance().isCash()) {
                this.initTemplate()
                    .analysisUrl()
                    .sendRequest();
            } else {
                this.setParentPlace()
                    .setRelationKey()
                    .dragInit();
            }

            return this;
        },

        analysisUrl: function () {
          var data,
              paramsUrl = location.href.split('?');

          if (paramsUrl.length > 1 && paramsUrl[paramsUrl.length-1].length) {
              data = Url.parse(paramsUrl[1])

              if (data.search) {
                  this.search.setText(data.search);
              }
          }

          return this;
        },
        setPreloader: function (status) {
            if (status) {
                this.preloader
                    .setRunning(false)
                    .show();
            }

            return this;
        },
        setPeriod : function (type) {
            switch (type) {
                case 'month': {
                    this._period_type = CalendarView.PERIOD_MONTH;
                    break;
                }
                case 'week': {
                    this._period_type = CalendarView.PERIOD_WEEK;
                    break;
                }
                case 'days':
                case 'day': {
                    this._period_type = CalendarView.PERIOD_DAY;
                    break;
                }
                default: break;
            }

            return this;
        },
        setTimeCell : function () {
            var $row, value, $content,
                _this = this,
                $element = $('.fc-grid:visible').find('tbody');

            switch (this._period_type) {
                case CalendarView.PERIOD_WEEK: {
                    $content = $(this.model_global.templates['site/calendarViewTemplate'].html).find('.fc-view-basicWeek tr.default').clone().removeClass('default hide')
                    break;
                }
                case CalendarView.PERIOD_DAY: {
                    $content = $(this.model_global.templates['site/calendarViewTemplate'].html).find('.fc-view-basicDay tr.default').clone().removeClass('default hide')

                    break;
                }
                default: {
                    return this;
                }
            }

            //add cell all Day
            $row = $content.clone();
            !this.data.general.has_all_day && $row.addClass('hide');
            $row.attr({
                    'data-all_day': 1
                })
                .find('.element[data-type="time"]').text(Message.translate_local('All day'));

            $row.find('.element[data-type="day-content"]').after(_this.getLinkElse());
            $element.append($row.clone());
            //=========

            for (var i=0; i<24; i++) {
                $row = $content.clone();
                value = i < 10 ? '0'+i : i;

                $row
                    .attr({
                        'data-time': value + ':00',
                        'data-hour': value
                    })
                    .find('.element[data-type="time"]').text(value+':00');

                $row.find('.element[data-type="day-content"]').after(_this.getLinkElse());

                $element.append($row.clone());

                $row.attr({
                        'data-hour': value+'-30',
                        'data-time': value + ':30'
                    })
                    .find('.element[data-type="time"]').text(value+':30');

                $element.append($row);
            }

            return this;
        },
        setData : function (data) {
            this._period_type = data.general.period_type;
            this.setCopyId(Number(data.general.copy_id));
            this.data = data;
            this.title_module = data.general.title;
            var pattern = '[data-type="active_field_name"]';
            if ($(pattern).length && !$(pattern)[0].options.length) {
                Select.addOptions(pattern, data.general.date_time_fields, true, { style: 'btn-white'});
            }
            $('.list_view_block.sm_extension').attr('data-copy_id', this.getCopyId());
            $('.element[data-type="title-module"]').text(data.general.title);

            return this;
        },
        changeCurrentDate : function (data) {
            if (data == null) {
                return this;
            }

            var month,
                date = data.general.current_date;

            this.current_date = date;
            var _moment = moment(date);

            month = _moment.format('MMMM');
            if (!this.model_global.locale.isEnglish() && data.general.period_type != CalendarView.PERIOD_MONTH) {
                month += '_D';
            }

            this.month =  Message.translate_local(month);
            this.month_number = _moment.format('MM');
            this.month_short = Message.translate_local(_moment.format('MMM'));
            this.day = _moment.format('DD');
            this.dayOfWeek = Message.translate_local(_moment.format('dddd'));
            this.year = _moment.format('YYYY');

            return this;
        },

        getMonth: function (date) {
            return moment(date).format('MMMM');
        },
        getMonthNumber: function (date) {
            return moment(date).format('MM');
        },
        getYear: function (date) {
            return moment(date).format('YYYY');
        },
        getDay: function (date) {
            return moment(date).format('DD');
        },
        getDayOfWeek: function (date) {
            return Message.translate_local(moment(date).format('dddd'));
        },
        addLVLink : function () {
            var key = moment().unix();
            $('.element[data-type="lv"]').attr('data-action_key', key);

            instanceGlobal.contentReload._content_vars[key] = {
                action_run: "loadModule",
                selector_content_box: "#content_container",
                module: {
                    copy_id: this.getCopyId(),
                    destination: "listView",
                    params: {
                        this_template: 0
                    }
                }
            };
            return this;
        },
        addPVLink : function () {
            var key = moment().unix();
            $('.element[data-type="pv"]').attr('data-action_key', key);

            instanceGlobal.contentReload._content_vars[key] = {
                action_run: "loadModule",
                selector_content_box: "#content_container",
                module: {
                    copy_id: this.getCopyId(),
                    destination: "processView",
                    params: {
                        this_template: 0
                    }
                }
            };
            return this;
        },
        setActivePeriod: function () {
            var month, year, day,
                hash,
                $block = $('.element[data-type="sort-period"] label');

            $block.removeClass('active');
            $block.filter('[data-type="' + this._period_type + '"]').addClass('active');
            const currentDate = DateTime.get(this.current_date);

            switch (this._period_type) {
                case CalendarView.PERIOD_MONTH:{
                    hash = currentDate.month.toString().concat(currentDate.year);
                    break;
                }
                case CalendarView.PERIOD_WEEK:{
                    hash = this.period.from + currentDate.month.toString().concat(currentDate.year);
                    break;
                }
                case CalendarView.PERIOD_DAY:{
                    hash = '_'+ currentDate.day.toString + currentDate.month.toString().concat(currentDate.year);
                    break;
                }
                default: {}
            }

            this.setHash(hash);
            return this;
        },
        getDayPrevMonth: function (model) {
            var delta,
                addedDay, firstCell, //комірка на 1 число
                _day, firtstDayOfWeek, key,
                lastDay = Number(this.model_global.date.getLastDay(model.prevDate)),
                month = model.date.toMoment(model.current_date).month();


            //model.dayOfWeek // 01.08 // 4
            //model.dayOfWeek+1 // # cell
            if (model.isUnix) {
                //en
                addedDay = (model.dayOfWeek-1)+1;
                delta = moment(model.prevDate).add(-(addedDay-1), 'days');
            } else {
                //ru
                var cell = model.dayOfWeek - 1;
                if (cell == -1) {
                    firstCell = 7;
                } else
                    if (cell >= 0) {
                        firstCell = cell + 1;
                    }
                addedDay = firstCell - 1; // відносно попереднього дня // model.prevDate
                delta = moment(model.prevDate).add(-(addedDay-1), 'days');
            }

            var month, day, momentDate;

            //lastDay
            for (var count = 1; count <= addedDay; count++) {
                momentDate = delta.clone().add((count-1), 'days');

                month = momentDate.format('MM');
                day = momentDate.format('DD');
                key = momentDate.year() +'-'+ month+'-'+day;

                model.list.push({
                    'is-other-month': true,
                    key: key,
                    day: day,
                    date: key
                });
            }

            return model.list;
        },
        setDayNextMonth: function (model) {
            //add in calendar days of next month
            var day, current_date, _moment, key,
                data = model.data;

            current_date = this.model_global.date.getNextMonth(model.current_date);
            _moment = moment(current_date);

            key = '{0}-{1}-';
            key = key.replace('{0}',_moment.format('YYYY'));
            key = key.replace('{1}',_moment.format('MM'));

            for (var i = 1; i <= 14; i++) {
                day = i < 10 ? '0'+String(i) : i;
                data.push({
                    'is-other-month': true,
                    key: key + day,
                    day: i,
                    date: key + day
                });
            }
            return this;
        },
        getRangeByDate: function () {
            var firstDate, dayOfWeek, prevDate, maxDay,
                modelDate = [],
                date = this.model_global.date;

            if (this._period_type == CalendarView.PERIOD_MONTH) {
               firstDate = date.getFirstDate(this.current_date);
               dayOfWeek = date.getDayOfWeek(firstDate);
               prevDate = date.getPrevDate(firstDate);

               var key,
                   isUnix = this.model_global.locale.isEnglish();

               modelDate = this.getDayPrevMonth({
                    date: date,
                    isUnix: isUnix,
                    list: modelDate,
                    dayOfWeek: dayOfWeek,
                    prevDate: prevDate,
                    current_date: this.current_date
                });

               maxDay = date.getLastDay(this.current_date);

               for (var i = 1; i <= maxDay; i++) {
                   var _day,
                       month = (moment(this.current_date).month()+1)+'';

                   month = month.length == 1 ? '0'+month : month;
                   _day = i < 10 ? '0' + i : i;

                   key = moment(this.current_date).year() +'-'+ month+'-'+_day;

                   modelDate.push({
                       key: key,
                       day: i,
                       date: key
                   });
               }

               this.setDayNextMonth({
                   data: modelDate,
                   current_date: this.current_date
               })
           }

            return modelDate;
        },
        setRelationKey: function () {
            var $tab, firstDate, dayOfWeek,
                date = this.model_global.date,
                modelDate = [],
                $listOfMontCells = $('.fc-view-month table .fc-widget-content'),
                $week = $('.fc-view-basicWeek'),
                $sortPeriod = $('.element[data-type="sort-period"] .fc-button');

            if (this.resource_template) {
                $sortPeriod = this.resource_template.find('.element[data-type="sort-period"] .fc-button');
                $listOfMontCells = this.resource_template.find('.fc-view-month table .fc-widget-content');
                $week = this.resource_template.find('.fc-view-basicWeek');
            }

            firstDate = date.getFirstDate(this.current_date);
            dayOfWeek = date.getDayOfWeek(firstDate);

            switch (this._period_type){
                case CalendarView.PERIOD_MONTH: {
                    //days of previous month

                    modelDate = this.getRangeByDate();
                    this.period = {
                        from: modelDate[0].date,
                        to: modelDate[modelDate.length-1].date
                    };
                    $.each($listOfMontCells, function (key) {
                        var json = modelDate[key],
                            $this = $(this);

                        if (!json) {
                            return;
                        }

                        if (json['is-other-month']) {
                            $this.addClass('fc-other-month');
                        }
                        $this.attr('data-unique-key',json.key).find('.fc-day-number').text(json.day);
                        $this.data({
                            'unique-index': json.key,
                            'date': json.key
                        });
                    })

                    break;
                }
                case CalendarView.PERIOD_WEEK: {

                    $tab = $sortPeriod.filter('.fc-button-basicWeek');

                    var month, cell,
                        day = [],
                        momenOfCurrentDate = moment(this.current_date),
                        isUnix = this.model_global.locale.isEnglish();

                    dayOfWeek = date.getDayOfWeek(this.current_date);
                    month = momenOfCurrentDate.month() + 1;

                    if (isUnix) {
                        cell = dayOfWeek;
                    } else {
                        cell = dayOfWeek - 1;
                    }

                    day = [moment(this.current_date).add(-cell, 'days') ];
                    day[1] = moment(day[0].format(this.FORMAT_DATE)).add(+7, 'days').format(this.FORMAT_DAY);

                    for (var count = 0; count < 7; count ++) {
                        var key, month,
                            momentOfCurrent = day[0].clone().add(count, CalendarView.PERIOD_DAY);

                        month = momentOfCurrent.format(this.FORMAT_MONTH);
                        key = moment(this.current_date).year() +'-'+ month +'-'+momentOfCurrent.format(this.FORMAT_DAY);


                        $.each($week.find('tbody tr'), function () {
                            var _time = '',
                                $this = $(this),
                                time = $this.attr('data-time');

                            if (time) {
                                _time = '-' + time.replace(':','-');
                            }

                            $this.find('.fc-widget-content').eq(count)
                                .attr('data-unique-key', key + _time)
                                .data('date', key + ($this.data()['time'] ? ' '+$this.data()['time'] : ''));
                        });
                    }

                    break;
                }
                case CalendarView.PERIOD_DAY: {
                    var _this = this,
                        _date = _this.current_date.split(' ')[0];

                    $.each(this._parent.find('tbody tr'), function () {
                        var date = _date,
                            $this = $(this),
                            time = $this.attr('data-hour');

                        if (!$this.is('[data-all_day="1"]')) {
                            date = _date + ' ' + $this.data()['time'];
                        }

                        var key = date.replace(':','-').replace(' ','-');

                        $this.find('.fc-widget-content')
                            .attr('data-unique-key', key)
                            .data('date', date);
                    });
                    break;
                }
                default: {
                    break;
                }
            }

            return this;
        },
        setTitleToView: function (text) {
            this._title_element = $('.element[data-type="period"]');
            this._title_element.text(text);
            this.title = text;

            return this;
        },
        setTitle: function () {
            var title, $tab, month,
                $week = $('.fc-view-basicWeek'),
                $sortPeriod = $('.element[data-type="sort-period"] .fc-button');

            if (this.resource_template) {
                $sortPeriod = this.resource_template.find('.element[data-type="sort-period"] .fc-button');
                $week = this.resource_template.find('.fc-view-basicWeek');
            }

            this.month = Message.translate_local(this.getMonth(this.current_date));
            this.year = this.getYear(this.current_date);
            this.day = this.getDay(this.current_date);
            this.dayOfWeek = this.getDayOfWeek(this.current_date);

            switch (this._period_type){
                case CalendarView.PERIOD_MONTH: {
                    $tab = $sortPeriod.filter('.fc-button-month');
                    title = this.month + ' ' + this.year;
                    break;
                }
                case CalendarView.PERIOD_WEEK: {
                    $tab = $sortPeriod.filter('.fc-button-basicWeek');

                    var month,
                        momenOfCurrentDate = moment(this.current_date);

                    month = momenOfCurrentDate.month() + 1;

                    var _date = this.model_global.date.getPeriodToWeek(this.current_date);

                    for (var count = 0; count < 7; count ++) {
                        var key, month,
                            cell = $week.find('.element[data-type="week-title"]').eq(count),
                            text = cell.text(),
                            momentOfCurrent = moment(_date.from).add(count, CalendarView.PERIOD_DAY),
                            day = Number(momentOfCurrent.format(this.FORMAT_DAY));

                        month = Number(momentOfCurrent.format(this.FORMAT_MONTH));
                        key = moment(this.current_date).year() +'-'+ month +'-' + day;

                        cell.text(text + ' ' + day);
                    }

                    this.period = {
                        from: moment(_date.from).format(this.FORMAT_DAY),
                        to: moment(_date.to).format(this.FORMAT_DAY)
                    }

                    this.month = Message.translate_local(moment(_date.from).format('MMMM'));
                    this.year = moment(_date.from).format('YYYY');

                    title = this.period.from +' - ' + this.period.to +' ' + this.month + ' ' + this.year;

                    break;
                }
                case CalendarView.PERIOD_DAY: {
                    $tab = $sortPeriod.filter('.fc-button-basicDay');

                    title  = this.dayOfWeek + ', ' + this.day +' '+ this.month + ' ' + this.year;

                    var $content = this._parent.find('.fc-widget-header');
                    $content.empty().last().text(this.dayOfWeek+' '+(Number(moment(this.current_date).format('DD'))));

                    break;
                }
                default: {
                    break;
                }
            }

            this.setTitleToView(title || '');

            return this;
        },
        setParentPlace: function () {
            var $parent;

            $('[data-type="calendar"] .fc-view').addClass('hide');

            switch (this._period_type) {
                case CalendarView.PERIOD_MONTH: {
                    $parent = $('.fc-view-month');

                    break;
                }
                case CalendarView.PERIOD_WEEK: {
                    $parent = $('.fc-view-basicWeek');

                    break;
                }
                case CalendarView.PERIOD_DAY: {
                    $parent = $('.fc-view-basicDay');

                    break;
                }
            }

            this._parent = $parent.removeClass('hide');

            return this;
        },
        emptyContent: function () {
            $('.element[data-type="day-content"]').empty();
            $('.element.fc-widget-header').empty();
            $('.element[data-type="else"]').addClass('hide');

            if (this._period_type != CalendarView.PERIOD_MONTH && !Global.getInstance().isCash()){
                this._parent.find('tbody').empty();
            }

            return this;
        },
        setAllDay: function () {

            switch (this._period_type) {
                case CalendarView.PERIOD_MONTH: {
                    this._parent.find('tr').attr('data-all_day', 1);
                    break;
                }
                default: {
                    return this;
                }
            }

            return this;
        },
        build : function () {
            var _this = this,
                $buttons = $('.fc-button'),
                $tables = $('.fc-grid:visible table'),
                $sortPeriod = $('.element[data-type="sort-period"] .fc-button');

            if (this.resource_template) {
                $buttons = this.resource_template.find('.fc-button');
                $tables = this.resource_template.find('.fc-grid table:visible');
                $sortPeriod = this.resource_template.find('.element[data-type="sort-period"] .fc-button');
            }

            var $tab = $([]);
            $sortPeriod.removeClass('fc-state-active'); //clear sort


            this.setParentPlace()
                .setAllDay()
                .getTranslate()
                .setRelationKey()
                .setTitle();

            $tab.addClass('fc-state-active');

            $buttons.hover(
                function() {
                    $(this).addClass('fc-state-hover');
                },
                function() {
                    $(this).removeClass('fc-state-hover');
                }
            );

            View
                .setTemplate(this._original_template)
                .setObject(this)
                .clearDays(this._list_later_by_update)
                .setParentPlace(this._parent)
                .drawData({
                    data: this.data
                });

            $tables.find('thead tr').addClass('fc-last');
            $tables.find('tr:last').addClass('fc-last');
            $tables.find('tr').find('td:last').addClass('fc-last');

            return this;
        },
        setOverlayCell: function ($element, status) {
            var $element = $element || $('.fc-view:visible .fc-widget-content');

            if (status) {
                $element.not('.time-cell').addClass('fc-cell-overlay');
            } else {
                $element.removeClass('fc-cell-overlay');
            }

            return this
        },
        dragInit : function(){
            var _this = this,
                modelCard = {},
                currentPosition = [{
                    time: moment().unix(),
                    pageX: 0,
                    pageY: 0
                }];

            $('.element[data-type="card"]').draggable({
                handle: '.bpm_body',
                helper: 'clone',
                cancel: '[prohibit-dragging]',
                distance: 10,
                start: function( event, ui ) {
                    var $helper = $(ui.helper),
                        $element = $(event.target);

                    TransferOfCards.createInstance();

                    modelCard.width = $element.closest('td').width()
                    modelCard.$tdOld = $element.closest('td');

                    //hidden old element
                    var idElement = $helper.find('[data-id]').data('id'),
                        element = $('[data-id="'+ idElement +'"]').closest('.fc-event').not('.ui-draggable-dragging');
                    modelCard.original = {
                        element: element,
                        wrap: element.closest('[data-type="day-content"]'),
                        offset: element.offset(),
                        width : element.width()
                    };

                    element.addClass('element-hidden');
                    $helper.width(element.width());
                    $helper.css({
                        'max-width': element.width(),
                        'min-width': element.width()
                    });

                },
                drag: function(event, ui ) {
                    /*
                        Алгоритм:
                        - визначаємо напрямок руху карточки з певним проміжком
                        - від напрямку визначаєм активний кут карточки
                        - від активного кута карточки визначаємо належність до комірки.

                        Алгоритм в дії
                        - згідно курсора мишки виділяємо комірку.
                    * */

                    $(ui.helper).width(modelCard.original.width);

                    var modelCell, agree,
                        instance = TransferOfCards.getInstance(),
                        $currentCard = $(ui.helper),
                        prevPosition = currentPosition[currentPosition.length-1];
                    modelCell = instance.inRange(event, $currentCard);
                    if (modelCell && !modelCell.element.is('.fc-cell-overlay')) {
                        var $overlay = $('.fc-cell-overlay');

                        if ($overlay.length && $(modelCell.element).attr('data-unique-key') != $overlay.attr('data-unique-key')) {
                            _this.setOverlayCell(null, false);
                        }

                        if (!$(modelCell.element).is('.fc-cell-overlay')) {
                            _this.setOverlayCell(modelCell.element, true);

                            var time = instance.getNewMilliseconds();

                            instance.setTimeStamp(time);
                            currentPosition.push({
                                'time': time,
                                'pageX': event.pageX,
                                'pageY': event.pageY
                            });
                        }
                    }
                },
                stop: function( event, ui ) {
                     _this.setOverlayCell(null, false);
                     _this.dragInit();
                }
            });

            $('.fc-content .fc-widget-content').droppable({
                hoverClass: "target",
                drop: function(event,ui){
                    var $clone, container, id, handler,
                        $toElement = $(event.target),
                        $fromElement = $(ui.helper);

                    var $helper = $(ui.helper),
                        idElement = modelCard.original.element.find('[data-id]').data('id'),
                        delegate;

                    _this.setOverlayCell(null, false);

                    var originalElement = TransferOfCards.getInstance().getActiveCell();

                    if (originalElement) {
                        modelCard.$tdNew = $toElement = $(originalElement.element);
                    };

                    modelCard.original = modelCard.original || {};
                    modelCard.original['element'] = $('[data-id="'+ idElement +'"]').closest('.fc-event').not('.ui-draggable-dragging').addClass('element-hidden');

                    modelCard.$tdOld = modelCard.original['element'].closest('td');

                    delegate = function () {
                        _this.dragInit();
                    };

                    if (!modelCard.$tdNew.length) {
                        delegate();

                        return this;
                    }

                    if (modelCard.$tdNew.data('unique-key') == modelCard.$tdOld.data('unique-key')) {
                        var $clone, offset ;

                        $clone = $helper.clone();
                        $helper.remove();
                        modelCard.$tdOld.find('[data-type="day-content"]').append($clone);

                        offset = {
                            top: modelCard.original.offset.top - modelCard.original.wrap.offset().top,
                            left: modelCard.original.offset.left - modelCard.original.wrap.offset().left
                        }

                        $clone.animate(offset, 500, function () {
                            modelCard.original.element.removeClass('element-hidden');
                            $clone.remove();
                            delegate();
                        });
                    } else {
                        delegate();

                        $clone = $fromElement.clone().attr('style','').removeClass('ui-draggable ui-draggable-dragging');
                        $clone.appendTo($toElement.find('.element[data-type="day-content"]'));

                        container = $fromElement.closest('[data-type="day-content"]');
                        id = $fromElement.find('[data-id]').data('id');
                        container.find('[data-id="'+id+'"]').first().closest('.element[data-type="card"]').remove();

                        var $list = $toElement.find('.element[data-type="day-content"]');
                        $list = $list.add(container);

                        $fromElement.find('.element[data-type="else"]').addClass('hide');

                        var date_time_to = $toElement.closest('td').data('date'),
                            date_time_from = $fromElement.closest('td').data('date');

                        _this._list_later_by_update = $list;
                        var data = {
                            id : id,
                            period: _this._period_type,
                            active_field_name: state.general.date_time_field_name,
                            attributes : {
                                date_time: date_time_to
                            }
                        };
                        if (state && state.general.date_time_field_name == 'b_date_ending') {
                            data.attributes.all_day = $toElement.closest('tr').data('all_day') || 0;
                        }
                        var filter = {
                            period : _this._period_type,
                            active_field_name: state.general.date_time_field_name,
                            date_time_from: _this.period.from,
                            date_time_to: _this.period.to                                
                        }
                        if (state.general.date_time_field_name == "b_date_ending") {
                            filter.all_day = $fromElement.closest('tr').data('all_day') || 0;
                        }
                        _this.updateData(data, filter);

                    }
                }
            });

            return this;
        },

        getTranslate : function () {
            var $template = this.resource_template,
                $container, daysOfWeek, key,
                $list = $([]),
                sort = Message.translate_local('month week day today').split(' '),
                arraySortBtn = [
                    $('.element[data-type="month"] span'),
                    $('.element[data-type="week"] span'),
                    $('.element[data-type="days"] span'),
                    $('.btn-today')
                ];

            key = 'Monday Tuesday Wednesday Thursday Friday Saturday';
            if (this.model_global.locale.isEnglish()) {
                key = 'Sunday'+' '+ key;
            } else key = key + ' '+ 'Sunday';

            daysOfWeek = key.split(' ');

            if ($template) {
                arraySortBtn = [
                    $template.find('.element[data-type="month"] span'),
                    $template.find('.element[data-type="week"] span'),
                    $template.find('.element[data-type="days"] span'),
                    $template.find('.btn-today')
                ];
            }

            $container = $('#content_container');
            $container.find('.btn-filter .hover_notif').text(Message.translate_local('Filters'));
            $container.find('.element[data-type="create-filter-title"]').text(Message.translate_local('Create filter'));

            $list = this._parent.find('thead tr.fc-first');

            $.each($list, function () {
                var $this = $(this);

                $.each($this.find('th.fc-widget-header'), function (key, value) {
                    var $this = $(this);

                    $this.text(Message.translate_local(daysOfWeek[key]));
                })
            })

            $.each(arraySortBtn, function (key) {
                $(this).text(sort[key]);
            })
            return this;
        },
        fillTemplate: function () {
            var $template = this._template.wrap('<div></div>').parent().clone();

            if (this.preloader.getMode() == Preloader.TYPE_VIEW_CALENDAR) {
                $template.find('.element[data-type="title-module"]').text(this.title_module);
                $template.find('.element[data-type="period"]').text(this.title);

                switch (this._period_type) {
                    case CalendarView.PERIOD_MONTH: {
                        this._parent = $template.find('.fc-view-month');

                        break;
                    }
                    case CalendarView.PERIOD_WEEK: {
                        this._parent = $template.find('.fc-view-basicWeek');

                        break;
                    }
                    case CalendarView.PERIOD_DAY: {
                        this._parent = $template.find('.fc-view-basicDay');

                        break;
                    }
                }

                this.setResourceTemplate($template)
                    .getTranslate()
                    .setRelationKey();
            } else {
                this.setResourceTemplate(null)
            }

            return $template.html();
        },
        initTemplate: function () {
            var data,
                currentKey = $('.nav li.active a').data('action_key'),
                contentReload = instanceGlobal.contentReload;

            var commonPreloader = Global.getInstance().getPreloader();
            if (commonPreloader && !commonPreloader.isRunning()) {
                this.preloader.show();
            }

            this._template = $(this.model_global.templates['site/calendarViewTemplate'].html).clone()
            this._original_template = $(this.model_global.templates['site/calendarViewTemplate'].html).clone();
            this._template.find('[data-type="edit-view"]').remove();
            this._template.find('.hide.default').remove();

            var $container = $('.calendar_view_block .list-view-panel');

            if (this.getTypeAction() != iAction.TYPE_FILTER) {
                $container.html(this.fillTemplate());
            }

            // Работает только с Filters обектами.
            if (this.preloader.getMode() == Preloader.TYPE_VIEW_CALENDAR) {
                this.preloader.setRunning(false).show();
            }

            $container.find('.fc-view-month, .fc-view-basicWeek')
                .find('.element[data-type="day-content"]')
                .after(this.getLinkElse());

            this._current_module = contentReload._content_vars[currentKey];

            return this;
        },
        getLinkElse: function () {
            return '<a href="" class="hide element link-else" data-type="else">+ <span class="count">0</span> '+ Message.translate_local('tasks') +'</a>';
        },
        updateData: function (to, data1) {
            var time,
                _this = this,
                instance = this.ajax_instance;

            instance.setUrl('/module/calendarView/updateData/'+ this.getCopyId())
                .setData(to)
                .setType("post")
                .setDataType('JSON')
                .setTimeOut(this.model_global.global.ajax.get_url_timeout)
                .setCallBackSuccess((json) => {                    
                    if (json.status){
                        _this._is_update = true;
                        _this.getDataByDateTimes(data1);
                        return;
                    }
                    this.rejectMoveCard($('[data-id="'+to.id+'"]').closest('[data-type="card"]'), data1.date_time_list[0].date_time);                                        
                });

            time = setTimeout(function() {
                clearTimeout(time );
                instance.send();
            }, 100);

            return this;
        },
        rejectMoveCard: (card$, keyLastPosition) => {
            var date = DateTime.get(keyLastPosition);
            if (date.isTime) {
                keyLastPosition = DateTime.formatCVKey(keyLastPosition);
            }
            var to$ = $("[data-unique-key='"+keyLastPosition+"']").find('[data-type="day-content"]')
            if (!to$.length) {
                return;
            }
            card$.appendTo(to$);
        },
        getUrl: function () {
            var json = {},
                url = this.getCopyId(),
                search = this.search.getParam(),
                filterParams = Filter.getParamsByUrl(),
                params = location.search.substring(1);

            if (params) {
                json = Url.parse(params);
            }

            if (filterParams || search.length || json.finished_object) {
                url += '?';
            }

            if (json.finished_object) {
                url += 'finished_object='+json.finished_object+'&';
            }

            if (filterParams) {
                url += filterParams;
            }

            if (search.length) {
                if (url.substring(url.length-1) != '?') {
                    url += '&';
                }

                url += search;
            }

            return url;
        },
        afterRequest: function (model) {
            var data = model.data;
            if (!data || !data.status) {
                return;
             }
            state = model.data;
            this.changeCurrentDate(model.change_date ? data : null);
            this
                .setData(data)
                .setParentPlace()
                .emptyContent()
            this.setTimeCell();
            this
                .build()
                .dragInit();
            this
                .setActivePeriod()
                .addPVLink()
                .addLVLink();
            model.callback && model.callback();
        },
        getDataByDateTimeRange: function (model) {
            var time,
                _this = this,
                data = model.data,
                instance = this.ajax_instance;

            instance.setUrl('/module/calendarView/getDataByDateTimeRange/'+ this.getUrl())
                .setData(data)
                .setAsync(true)
                .setType("post")
                .setDataType('JSON')
                .setTimeOut(this.model_global.global.ajax.get_url_timeout)
                .setCallBackSuccess(function (data) {
                    _this.afterRequest({
                        data: data,
                        callback: model.callback
                    });
                });

            time = setTimeout(function() {
                clearTimeout(time );
                instance.send();
            }, 100);
        },
        getDataByPeriod: function (model) {
            var time,
                _this = this,
                data = model.data,
                instance = this.ajax_instance;

            instance.setUrl('/module/calendarView/getDataByPeriod/'+ this.getUrl())
                .setData(data)
                .setAsync(true)
                .setType("post")
                .setDataType('JSON')
                .setTimeOut(this.model_global.global.ajax.get_url_timeout)
                .setCallBackSuccess(function (data) {
                    _this.afterRequest({
                        data: data,
                        callback: model.callback,
                        change_date: model.change
                    });
                });

                time = setTimeout(function() {
                    clearTimeout(time );
                    instance.send();
                }, 100);

            return this;
        },
        getDataByDateTimes: function (data) {
            var time,
                _this = this,
                instance = this.ajax_instance;

            instance.setUrl('/module/calendarView/getDataByDateTimes/'+ this.getUrl())
                .setData(data)
                .setType("post")
                .setDataType('JSON')
                .setTimeOut(this.model_global.global.ajax.get_url_timeout)
                .setCallBackSuccess((json) => {
                    _this.afterRequest({
                        data: json
                    });
                });

            time = setTimeout(function() {
                clearTimeout(time );
                instance.send();
            }, 100);

            return this;
        },

        getCorrectTime: function (time) {
            var array = time.split(':'),
                hour = Number(array[0]),
                minute = Number(array[1]);

            minute = minute < 30 ? '00' : '30';

            return hour + ':' + minute;
        },
        destroy : function () {
            Global.removeEvents(_self._instance._events);
            _self._instance = null;
            this.filter = null;
            this.search = null;

            Events.removeHandler({ key: 'CalendarUpdateDate', type: Events.TYPE_UPDATE_DATA});
            Events.removeHandler({ key: 'CalendarDestroy', type: Events.TYPE_DESTROY});

            return null;
        }
    };

    View = {
        _original_template: null,
        _parent: null,
        _show_tasks: 0, // show tasks in month or weeks of period
        _object: null,

        setObject: function (instance) {
            this._object = instance;

            return this;
        },
        setTemplate: function(_original_template) {
            this._original_template = _original_template;

            return this;
        },
        setParentPlace: function (_parent) {
            this._parent = _parent;

            return this;
        },
        clearDays: function (list) {
            if (!list) {
                $('.element[data-type="day-content"]').empty(); // clear content
            } else {
                list.empty();
                list.next('.element[data-type="else"]').addClass('hide');
                list = null;
            }

            return this;
        },
        updateData: function (data) {

        },
        drawData: function (model) {
            //set data of card
            var momentCurrentDate,
                data = model.data,
                dayOfMonth,
                _this = this,
                card = $('.calendar_view_block [data-type="edit-view"]').html();

            momentCurrentDate = moment(data.general.current_date.date_time || data.general.current_date);
            this._period_type = data.general.period_type;

            dayOfMonth = momentCurrentDate.date();
            setTimeout(() => {
                $.each($('[data-type="card"]'), function(index, item) {
                    if (model.data.general.update_disallow) {
                        item.setAttribute('prohibit-dragging', '');
                    } else {
                        item.removeAttribute('prohibit-dragging');
                    }
                });
            }, 200);
            $.each(data.data || [], function (key, data) {
                var content, key, date, time,
                    $object = $([]),
                    arrEndDateOfTime = data['date_time'] ? data['date_time'].split(' ') : false,
                    task = card.replace('{0}', data.title),
                    allDay = Number(data['all_day'] || 0);

                if(!data['date_time']){
                    return true;
                }
                date = arrEndDateOfTime[0];
                time = arrEndDateOfTime[1];
                task = task.replace('{1}', data.id);
                key = date;
                switch(_this._period_type) {
                    case CalendarView.PERIOD_MONTH : {
                        $object = _this._parent.find('[data-unique-key="'+ date +'"] .element[data-type="day-content"]');
                        break;
                    }
                    case CalendarView.PERIOD_WEEK : {
                        var _time = _this._object.getCorrectTime(time),
                            hour = _time.split(':')[0],
                            minute = _time.split(':')[1];

                        key = date;

                        if (allDay) {
                            $object = _this._parent.find('tr[data-all_day="1"] .fc-widget-content[data-unique-key="'+ key +'"] .element[data-type="day-content"]');
                        } else {
                            hour = hour.length == 1 ? '0'+hour: hour;
                            key += '-' + hour+'-'+minute;
                            $object = _this._parent.find('.fc-widget-content[data-unique-key="'+ key +'"] .element[data-type="day-content"]');
                        }

                        break;
                    }
                    case CalendarView.PERIOD_DAY : {
                        var _time = _this._object.getCorrectTime(time),
                            hour = _time.split(':')[0],
                            minute = _time.split(':')[1];
                        hour = hour.length == 1 ? '0'+hour: hour;                                                
                        if (Number(date.split('-')[2]) == dayOfMonth) {
                            if (allDay) {
                                $object = _this._parent.find('tr[data-all_day="1"]');
                            } else {
                                key += '-' + hour+'-'+minute;
                                $object = _this._parent.find('td[data-unique-key="'+ key +'"]');
                            }
                            $object = $object.find('[data-type="day-content"]');
                        }
                    }
                    default: break;
                }
                content = $object.html() || '';
                $object.html(content + task);
            });

            $.each(model.data['data_count'] || [], function (key, data) {
                var $object, date;

                if (Number(data['all_day'])) {
                    date = data['date_time'].split(' ')[0];

                    $object = _this._object._parent.find('tr[data-all_day] .fc-widget-content[data-unique-key="' + date + '"]');
                } else {
                    date = data['date_time'];
                    date = date.replace(' ', '-').replace(':', '-');

                    $object = _this._object._parent.find('.fc-widget-content[data-unique-key="'+ date +'"]')
                }

                $object.find('.element[data-type="else"]').first().removeClass('hide').find('.count').text(data.count);
            });

            return this;
        }
    }

    var TransferModelCell = {
        x1: null,
        x2: null,
        x3: null,
        x4: null,
        y1: null,
        y2: null,
        y3: null,
        y4: null,

        element: null,

        createInstance : function(){
            var Obj = function(){
                for(var key in TransferModelCell){
                    this[key] = TransferModelCell[key];
                }
            }

            return new Obj();
        }
    }

    var TransferModel = {
        array: null,

        createInstance : function(){
            var Obj = function(){
                for(var key in TransferModel){
                    this[key] = TransferModel[key];
                }
                this.array = [];
            }

            return new Obj();
        },
        addElement: function ($element) {
            var item = TransferModelCell.createInstance();

            this.getParams(item, $element);
            item.element = $element;

            this.array.push(item);

            return this;
        },
        getParams: function (json, $element) {
            var item = json || {};

            item.x1 = $element.offset().left;
            item.y1 = $element.offset().top;

            item.x2 = $element.offset().left + $element.width();
            item.y2 = $element.offset().top;

            item.x3 = $element.offset().left;
            item.y3 = $element.offset().top + $element.height();

            item.x4 = $element.offset().left + $element.width();
            item.y4 = $element.offset().top + $element.height();

            return item;
        }
    }

    publicTransfer = {
        model: null,
        direction: null,
        direction_offset: 5,
        active_corners: null, // type: {x, y},
        active_cell: null,

        constructor: function () {
            iTimeStamp.implements.call(this);

            this.createModel();

            return this;
        },
        getActiveCell: function () {
            return this.active_cell;
        },
        inRange: function (event) {
            var data = null;

            $.each(this.model.array, function (key, value) {
                if (value.x1 < event.pageX && event.pageX < value.x2 &&
                    value.y1 < event.pageY && event.pageY < value.y3) {
                    data = value;
                    return false;
                }
            });

            if (data) {
                this.active_cell = data;
            }

            return data;
        },
        getActiveCorners: function (model) {
            var corners = {
                x: null,
                y: null
            }
            switch (this.direction) {
                case TransferOfCards.TYPE_DIRECTION_TOP: {
                    corners.x = model.x1;
                    corners.y = model.y1;
                    break;
                }
                case TransferOfCards.TYPE_DIRECTION_RIGHT: {
                    corners.x = model.x2;
                    corners.y = model.y2;

                    break;
                }
                case TransferOfCards.TYPE_DIRECTION_LEFT:
                case TransferOfCards.TYPE_DIRECTION_BOTTOM: {
                    corners.x = model.x3;
                    corners.y = model.y3;
                    break;
                }
                default: {
                    corners = null;

                    break;
                }
            }
            return corners;
        },
        setDirection: function (direction) {
            this.direction = direction;

            return this;
        },
        checkTerms: function (json) {
            var bool = false,
                prevPosition = json.prevPosition,
                event = json.event,
                calendarInstance = json.calendarInstance;

            // Якщо курсор мишки змінив комірку
            if (event.page) {
                bool = true;
            }
            return bool;
        },
        getDirection: function () {
            return this.direction;
        },
        createModel: function () {
            var _this = this,
                $list = $('.fc-view:visible tbody td[data-unique-key]');

            this.model = TransferModel.createInstance();

            $.each($list, function (key, value) {
                _this.model.addElement($(value));
            });

            return this;
        }
    }

    TransferOfCards = {
        instance: null,
        TYPE_DIRECTION_TOP: 1,
        TYPE_DIRECTION_RIGHT: 2,
        TYPE_DIRECTION_BOTTOM: 3,
        TYPE_DIRECTION_LEFT: 4,

        createInstance : function(){
            var Obj = function(){
                for(var key in publicTransfer){
                    this[key] = publicTransfer[key];
                }
            }

            this.instance = new Obj().constructor();

            return this.instance;
        },
        getInstance: function () {
            return this.instance;
        },
        isChangeDirection: function (array) {
            var  basicX = 10, basicY = 5;

            var r = true, deltaX, deltaY,
                currentMove = array[array.length-1],
                prevMove = array[array.length - 2];

            if (currentMove && prevMove) {
                deltaX = Math.abs(currentMove.pageX - prevMove.pageX);
                deltaY = Math.abs(currentMove.pageY - prevMove.pageY);

                if (deltaX < basicX || deltaY < basicY) {
                    r = false; // забороняємо зміщення
                }
            }

            return r;
        }
    }

    //static
    CalendarView = {
        PERIOD_MONTH: 'month',
        PERIOD_WEEK: 'week',
        PERIOD_DAY: 'days',

        CONST_TODAY: 1,
        CONST_PREV: 2,
        CONST_NEXT: 3,
        CONST_MONTH: 4,
        CONST_WEEK: 5,
        CONST_DAY: 6,
        CONST_STORE: 7,
        CONST_LEFT: 8,
        CONST_RIGHT: 9,

        _type : 'calendarView',

        createInstance : function(){
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            _self._instance = new Obj();

            return _self._instance.constructor();
        },
        getInstance: function (bool) {
            if (bool && !_self._instance) {
                this.createInstance();
            }

            return _self._instance;
        },

        saveTemplate: function () {
            _self.template = $('.content-panel').clone().html();
        },
        getTemplate: function () {
            return _self.template;
        },     
        destroy : function () {
            return _self._instance ? _self._instance.destroy() : null;
        }
    }

    for(var key in CalendarView) {
        _self[key] = CalendarView[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in _public) {
        _self[key] = _public[key];
    }

    CalendarView.prototype = Object.create(Global);
    exports.CalendarView = CalendarView;
    exports.TransferOfCards = TransferOfCards;
})(window);
