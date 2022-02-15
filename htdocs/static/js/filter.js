;(function (exports) {
    var _private, _public, _protected, Filter, iFilter,
        _self = {}; //link for instance

    _protected = {

    };
    _private = {
        instance: null,
        onClickFilterBtnTakeOff : function(e){
            var $this = $(this),
                instance = e.data.instance;

            instance.beforeAction(e);

            if ($.isFunction(instance.showPreloader)) {
                instance.showPreloader($this)
            }
            else {
                instanceGlobal.preloaderShow($this);
            }

            instance.take_off(this, $this.closest('.sm_extension').data('page_name'));
        },
        onClickFilterInstallSpan : function(e){
            var $this = $(this),
                instance = e.data.instance,
                $filter = $('.filter'),
                spinner = Global.spinner,
                $blockContent = $filter.find('.filter-box'),
                currentFilterId = parseInt($this.closest('[data-filter_id]').attr('data-filter_id'));

            if ($blockContent.length && currentFilterId == $blockContent.data().filter_id) {
                return;
            }
            $('.list_view_block .crm-table-wrapper').getNiceScroll().resize();

            $blockContent.remove();
            $filter.find(spinner.selector).remove();
            $filter.show().addClass('center-position init-preloader min-height set-preloader');
            $filter.append(spinner.clone().first());

            niceScroll.clear();

            //TODO: optimization
            var currentInstance = Global.getInstance().getCurrentInstance();
            if (currentInstance.onClickFilterInstallSpan) {
                currentInstance.onClickFilterInstallSpan($this);
                return
            }

            var base = $this.closest('.filter-install');
            Filter.show($this.closest('.sm_extension').data('copy_id'),
                base.data('filter_id'),
                base.data('name'),
                base.find('span').text());

            var time = setTimeout(function () {
                clearTimeout(time);
                Global.updateTab();
                niceScroll.init();
            }, 200);
        },
        onClickFilterBtnSet : function(e){
            var $this = $(this),
                instance = e.data.instance;

            instance.beforeAction(e);

            if ($.isFunction(instance.showPreloader)) {
                instance.showPreloader($this)
            }
            else {
                instanceGlobal.preloaderShow($this);
            }

            instance.set(
                $this.closest('.sm_extension').data('copy_id'),
                $this.closest('.sm_extension').data('parent_copy_id'),
                $this.data('id'),
                $this.closest('.sm_extension').data('page_name')
            );
        },
        onClickFilterBtnDelete : function(e){
            e.data.instance.delete(this, $(this).closest('.sm_extension').data('page_name'));
        },
        onClickFilterBtnCancel : function(e){
            Filter.cancel();

            niceScroll.clear();
            niceScroll.init();
            Global.updateTab();

            $('.input-daterange').hide();
            setTimeout(function () {
                $('.input-daterange').show(); // resize in report
            }, 75);
        },
        onClickFilterBtnSave : function(e) {
            var $this = $(this),
                instance = e.data.instance;

            instance
                .beforeAction()
                .save($this);
        },
        onClickFilterCreate : function(e){
            var instance = e.data.instance;

            instance.create($(this));
        },
        onChangeElementFilterDataNameCondition : function(e){
            if (instanceReports) return;

            var $this = $(this),
                instance = e.data.instance,
                copy_id = $this.closest('.sm_extension').data('copy_id'),
                field_name = $this.closest('.filter-box-panel').find('.element_filter[data-name="field"]').val(),
                condition_value = $this.closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();

            if(!$this.val()){
                instance.clearConditionValue(this);
            }

            Filter.setConditionValue(this, copy_id, field_name, condition_value);
        },
        onChangeElementFilterDataNameField : function(e){
            var _this = this,
                instance = e.data.instance,
                copy_id = $(_this).closest('.sm_extension').data('copy_id'),
                condition_value = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();

            if(!$(_this).val()){
                instance.clearCondition(_this);
                instance.clearConditionValue(_this);
                instance.setConditionValue(_this, copy_id, '', condition_value);
                return;
            }

            instance.setCondition(_this, copy_id, $(_this).val(), function(data_value){
                var condition_value = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();
                instance.setConditionValue(_this, copy_id, $(_this).val(), condition_value);

                $.each($('.dropdown-menu.inner.selectpicker').not('[tabindex]'), function () {
                    niceScrollCreate($(this));
                })
            });

        },
        onClickElementFilterDataNameConditionValue : function(){
            $('.filter .submodule-table').each(function () {
                var _this = $(this);
                if (_this.find('.list-view-avatar').length) {
                    _this.addClass('withAvatar');
                }
                niceScrollCreate(_this);
            });
        },
        onClickPanelDelete : function(){
            Filter.deletePanel(this);
        },
        onClickPanelAdd : function(e){
            var instance = e.data.instance;

            instance.addPanel($(this));
        },
    };

    //Фильтр панель, отвечает за прелоадер внутри фильтр панели
    //static
    var FilterPanel = {
        createInstance : function(){
            var Obj = function(){
                for(var key in FilterPanel){
                    if ($.inArray(key,['createInstance'])< 0) {
                        this[key] = FilterPanel[key];
                    }
                }

                return this;
            }

            return new Obj();
        },
        init: function () {
            iPreloader.implements.call(this);

            this.hidePreloader = function () {
                this.preloader = this.preloader && this.preloader.destroy();

                return this;
            }

            return this;
        }
    }

    _public = {
        apply_callback: null,
        content_hide_status: false, // Переменная для того чтоб скрывать или нет вставку контента после фильтра.
        list_exist: null,

        constructor: function () {
            iBackForwardHistory.implements.call(this);

            this.events()
                .reDefinition();

            return this;
        },
        reDefinition: function () {
            this.updateProperties = function (data) {

                return this;
            };

            return this;
        },
        setContentHideStatus: function (status) {
            this.content_hide_status = status;
            return this;
        },
        events : function () {
            this._events = [
                { parent: document, selector: '.filter-panel-add', event: 'click', func: _self.onClickPanelAdd },
                { parent: document, selector: '.filter-panel-delete', event: 'click', func: _self.onClickPanelDelete},
                { parent: document, selector: '.element_filter[data-name="field"]', event: 'change', func: _self.onChangeElementFilterDataNameField},
                { parent: document, selector: '.element_filter[data-name="condition"]', event: 'change', func: _self.onChangeElementFilterDataNameCondition},
                { parent: document, selector: '.filter-create', event: 'click', func: _self.onClickFilterCreate},
                { parent: document, selector: '.filter-btn-save', event: 'click', func: _self.onClickFilterBtnSave},
                { parent: document, selector: '.filter-btn-cancel', event: 'click', func: _self.onClickFilterBtnCancel},
                { parent: document, selector: '.filter-btn-delete', event: 'click', func: _self.onClickFilterBtnDelete},
                { parent: document, selector: '.element_filter[data-name="condition_value"]', event: 'click', func: _self.onClickElementFilterDataNameConditionValue},
                { parent: document, selector: '.filter-btn-set', event: 'click', func: _self.onClickFilterBtnSet},
                { parent: document, selector: '.filter-install span', event: 'click', func: _self.onClickFilterInstallSpan},
                { parent: document, selector: '.filter-btn-take-off', event: 'click', func: _self.onClickFilterBtnTakeOff},
                //{ name: '', event: '', func: ''},
            ]

            Global.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        //Методы для переопределний
        beforeApply: function () {
            return this;
        },
        //Методы для переопределний
        beforeAction: function () {
            return this;
        },
        take_off : function(element, destination){
            var _this = this;

            Filter.deleteFromLocalStorage(destination,
                $(element).closest('.sm_extension').data('copy_id'),
                $(element).closest('.sm_extension').data('parent_copy_id'),
                $(element).closest('.filter-install').data('filter_id'),
                function(){
                    _this.apply();
                });
        },
        setApplyCallBack : function (callback) {
            this.apply_callback = callback;

            return this;
        },
        create : function ($this) {
            var params,
                $smExtension = $this.closest('.sm_extension');

            params = {
                this_template: parseInt($smExtension.data('this_template'))
            };

            Filter
                .showPreloaderInner($this)
                .create($smExtension.data('copy_id'), params);

            return this;
        },
        set : function(copy_id, pci, filter_id, destination){
            Filter.set(copy_id, pci, filter_id, destination);
            return this;
        },
        setListExistFilters: function (id) {
            if (!this.list_exist) {
                this.list_exist = [];
            }

            this.list_exist.push({
                'id': id
            });

            return this;
        },
        setConditionValue: function(_this, copy_id, field_name, condition_value) {
            Filter.setConditionValue(_this, copy_id, field_name, condition_value);
       },
        setCondition : function(_this, copy_id, field_name, cb, data){
            Filter.setCondition(_this, copy_id, field_name, cb, data);
        },
        getListExistFilters: function () {
            return this.list_exist
        },
        updateByUrl : function(){
            var _this = this,
                json = Url.getParams(location.href);

            $.each(Object.keys(json), function (key, value) {
                if (value.indexOf('filters')>=0) {
                    _this.setListExistFilters(json[value]);
                }
            });

            return this;
        },
        save: function ($element) {
            Filter.save($element);

            return this;
        },
        apply : function($callback){
            var $callback = $callback || this.apply_callback;

            this.beforeApply();
            Filter.apply($callback);

            return this;
        },
        addPanel: function ($this, data) {
            Filter.addPanel($this.closest('.sm_extension').data('copy_id'), data);

            return this;
        },
        delete : function(_this, destination){
            var $this = $(_this),
                _this = this;

            var _function = function(){
                $('.filter').hide();
                $.post(Global.urls.url_filter_delete + '/' + $('.filter-box').closest('.sm_extension').data('copy_id'), {'id': $this.closest('.filter-box').data('filter_id')}, function(data){
                    if(data.status == true){
                        var id = $this.closest('.filter-box').data('filter_id');
                        $this.closest('.filter-block').find('.filter-install[data-filter_id="'+id+'"]').remove();

                        Filter.deleteFromLocalStorage(destination,
                            $this.closest('.sm_extension').data('copy_id'),
                            $this.closest('.sm_extension').data('parent_copy_id'),
                            id,
                            function(){
                                _this.apply(function(){
                                    niceScrollInit();
                                });
                            });

                    } else {
                        Message.show(data.messages, false);
                    }
                }, 'json');
                niceScroll.clear();
            }


            Message.show([{'type':'confirm', 'message': Message.translate_local('Delete filter') + '?'}], false, function(_this_c){
                if($(_this_c).hasClass('yes-button')){
                    modalDialog.hide();

                    if (_this.showPreloader) {
                        _this.showPreloader();
                    } else {
                        //КОСТИЛЬ
                        instanceGlobal.preloaderShow($this);
                    }

                    _function();
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        },
    };

    iFilter = {
        filter: null,

        setFiler: function(filter) {
            this.filter = filter;

            return this;
        },
        getFilter: function () {
            return this.filter;
        },
        closeFilter: function () {
            return this;
        },
        implementsIFilter: function () {
            for (var key in iFilter) {
                if ($.inArray(key,['implementsIFilter'])< 0) {
                    this[key] = iFilter[key];
                }
            }

            return this;
        },
    }

    Filter = {
        local_storage_index_prefix : '',

        // реализация интерфейсов.
        implements: function (object) {
            object.implements.call(this);

            return this;
        },
        checkCommonInstanceByUrl: function () {
            var data,
                instance = null;

            this.implements(iModule);

            if (Filter.isFilterByUrl()) {
                instance = Filter.createInstance();

                instance.updateByUrl();
            }

            data = Url.parseFull();

            Api.history
                .createInstance()
                .setControllerId(data['controller'])
                .setActionId(data['action'])
                .setUrl(data['pathname'])
                .setUserStorageBackUrl();

            Api.history
                .createInstance()
                .setKey('page_params')
                .setCopyId(data.id)
                .setData({
                    'active_page': data['controller'],
                    'this_template': true
                })
                .setUserStorage();

            if (Filter.setCommonInstance) {
                Filter.setCommonInstance(instance)
            };

            return this;
        },

        createInstance : function(){
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            return _self.instance = new Obj().constructor();
        },

        getInstance: function () {
            return _self.instance;
        },
        setInstance: function (instance) {
            _self.instance = instance;

            return _self.instance;
        },
        showPreloaderInner: function ($this) {
            var $filter = $this.closest('.filter-block').find('.filter').show().addClass('relative');

            FilterPanel.instance = FilterPanel
                .createInstance()
                .init()
                .setPreloader(Preloader.createInstance())
                .setShowPreloaderHandler(function () {
                    this.setPlaceForSpinner($filter)
                        .setWhereContentHide(Preloader.TYPE_BLOCK)
                        .setElement('.filter-block .filter', ['hide_all_type_block where-content-hide position-absolute'])
                        .setCssPositionSpinner(Preloader.css.ABSOLUTE)
                        .run();
                });

            FilterPanel.instance.showPreloader();

            return this;
        },
        //static
        isFilterByUrl : function(){
            var r = false;

            if (location.href.indexOf('filters[') > 0) {
                r = true;
            }
            return r;
        },
        fixedDropDown : function () {
            var filter = $('.filter-box-operations.filter-box-table .element_filter').filter('[data-name="filter_view"]')
            if (filter.length) {
                var width,
                    select = filter.next();
                var drop_menu = select.find('div.dropdown-menu');
                drop_menu.css('min-width',0);
                width = drop_menu.width()+60;
                drop_menu.width(width);
                select.find('button').css('width',width+1);
            }

            $('.filter .dropdown-menu.inner.selectpicker').each(function () {
                niceScrollCreate($(this));
            });
        },
        show : function(copy_id, filter_id, filter_name, filter_title, data){
            if(filter_id) {
                if(!data){
                    data = {};
                }
                data['filter_id'] = filter_id;

                $.get(Global.urls.url_filter_load + '/' + copy_id, data, function (data) {
                    if(data.status) {
                        var $filter = $('.filter');

                        $filter.show()
                            .find('.filter-box-container')
                            .html(data.data)
                            .find('select').selectpicker({
                            style: 'btn-white',
                            noneSelectedText: Message.translate_local('None selected')
                        })
                            .closest('.filter-box').data('filter_id', filter_id)
                            .data('filter_name', filter_name)
                            .find('.element_filter[data-name="filter_title"]').val(filter_title);

                        $filter.find(Global.spinner.selector).remove();
                        $filter.removeClass('center-position init-preloader min-height set-preloader');

                        var filterBlock = $('.filter-block');

                        // Init all sigle calendars
                        Filter.singleCalendar(filterBlock.find('.dateinput'));
                        // Init all range calendars
                        Filter.rangeCalendar(filterBlock.find('.dp1'), filterBlock.find('.dp2'));
                        Filter.fixedDropDown();

                    } else {
                        $('.filter').hide();
                    }
                }, 'json').done(function () {

                });
            }
        },

        create : function(copy_id, data){
            if(!data){
                data = {};
            }
            $.get(Global.urls.url_filter_add_block +'/'+copy_id, data, function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                    $('.filter').hide();
                } else {
                    niceScroll.clear();

                    $('.filter').show()
                        .find('.filter-box-container')
                        .html(data.data)
                        .find('select').selectpicker({
                        style: 'btn-white',
                        noneSelectedText: Message.translate_local('None selected')
                    })

                    niceScroll.init();
                    Filter.fixedDropDown();
                }

                FilterPanel.instance && FilterPanel.instance.hidePreloader();
            }, 'json').done(function() {
            });
            niceScrollInit();
        },

        save : function($element){
            var data, id,
                _this = this,
                destination = $element.closest('.sm_extension').data('page_name'),
                params = [],
                $filterBox = $('.filter-box');

            niceScroll.clear();

            $('.filter-box-panels .filter-box-panel').each(function(i, ul){
                var name = $(ul).find('.element_filter[data-name="field"]').val();
                var condition_value = [];
                $(ul).find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                    if($(this).hasClass('element_relate') || $(this).hasClass('element_relate_this')){
                        condition_value.push($(ul).data('id'));
                    } else if($(this).hasClass('element_relate_participant')){
                        condition_value.push($(ul).data('ug_id'));
                        condition_value.push($(ul).data('ug_type'));
                    } else {
                        condition_value.push($(ul).val());
                    }
                });
                if(name){
                    params.push({
                        'name' : name,
                        'condition' : $(ul).find('.element_filter[data-name="condition"]').val(),
                        'condition_value' : condition_value,
                    })
                }
            })
            var id = $filterBox.data('filter_id');
            var pci = $filterBox.closest('.sm_extension').data('parent_copy_id'),

                data = {
                    'id' : id,
                    'copy_id' : $filterBox.closest('.sm_extension').data('copy_id'),
                    'title' : $filterBox.find('.element_filter[data-name="filter_title"]').val(),
                    'params' : params,
                    'view' : $filterBox.find('.element_filter[data-name="filter_view"]').val(),
                }
            $.post(Global.urls.url_filter_save + '/' + data.copy_id, {'data' : data}, function(data){
                if(data.status == true){
                    var $filterMenu = $('ul.filter-menu');

                    _this.showPreloader && _this.showPreloader() || instanceGlobal.preloaderShow($element);

                    $('.filter').hide().find('.filter-box-container').empty();
                    $filterMenu.find('.filter-btn-set').remove();
                    $filterMenu.append(data.menu_list);

                    if(id){
                        if($filterBox.data('filter_id') != data.filter_id){
                            _this.updateInLocalStorage(destination, data.copy_id, pci, data.filter_id_old, data.filter_id, function(){
                                _this.apply();
                            });
                        } else {
                            _this.apply();
                        }

                    } else {
                        _this.set(data.copy_id, pci, data.filter_id, destination);
                    }
                } else {
                    Message.show(data.messages, false);
                }
            }, 'json');

        },
        //show preloader in filter panel
        set : function(copy_id, pci, filter_id, destination){
            var _this = this;

            Filter.findInLocalStorage(destination, copy_id, pci, filter_id, function(data){
                if(data){
                    Message.show([{'type':'warning', 'message': 'The filter not added'}], true);
                } else {
                    Filter.addToLocalStorage(destination, copy_id, pci, filter_id, function(){
                        _this.getInstance().apply();
                    });
                }

            });
        },
        cancel : function(){
            $('.filter').hide().find('.filter-box-container').empty();
        },

        getParamsByUrl : function () {
            var line = '',
                array = $([]),
                part = Url.getParams(location.href);

            if (part) {
                array = Object.keys(part)
            };

            $.each(array, function (key, data) {
                if (data.indexOf('filters')>=0) {
                    if (key !=0) {
                        line += '&';
                    }

                    line += data +'='+ part[data];
                }
            });

            return line;
        },
        addPanel : function(copy_id, data){
            if(!data){
                data = {};
            }

            $.get(Global.urls.url_filter_add_panel+'/'+copy_id, data, function(data){
                var filterBox = $('.filter-box');
                filterBox.find('.filter-box-panels')
                    .append(data.data)
                    .find('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                filterBox.find('.dropdown-menu.inner.selectpicker').each(function () {
                    niceScrollCreate($(this));
                });
            }, 'json').done(function() {
                //fltrDeb();
            });
            $('.crm-table-wrapper').getNiceScroll().remove();
            niceScrollInit();
        },

        deletePanel : function(_this){
            var panels = $(_this).closest('.filter-box-panels').find('.filter-box-panel');
            if(panels.length <= 1){
                return;
            }
            $(_this).closest('.filter-box-panel').remove();
            $('.crm-table-wrapper').getNiceScroll().remove();
            niceScrollInit();
        },

        setCondition : function(_this, copy_id, field_name, cb, data){
            if(!data){
                data = {};
            }
            data['field_name'] = field_name;

            $.get(Global.urls.url_filter_add_condition+'/'+copy_id, data, function(data){
                $(_this).closest('.filter-box-panel')
                    .find('.filter-box-condition')
                    .html(data.data)
                    .find('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                cb();
            }, 'json');
        },

        setConditionValue : function(_this, copy_id, field_name, condition_value, data){
            if(!data){
                data = {};
            }

            data['field_name'] = field_name;
            data['condition_value'] = condition_value;
            data['this_template'] = $(_this).closest('.list_view_block.sm_extension, .process_view_block.sm_extension').data('this_template');

            $.get(Global.urls.url_filter_add_condition_value+'/'+copy_id, data, function(data){
                $(_this).closest('.filter-box-panel')
                    .find('.filter-box-condition-value')
                    .html(data.data)
                    .find('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});

                // show single calendar
                if ($(data.data).find('.dateinput').length) {
                    var $dateinput = $(_this).closest('.filter-box-panel').find('.dateinput')
                    Filter.singleCalendar($dateinput);
                    $dateinput.datepicker('setDate', new Date());
                }
                // show range calendar
                if ($(data.data).find('.dp1').length) {
                    var $dp1 = $(_this).closest('.filter-box-panel').find('.dp1'),
                        $dp2 = $(_this).closest('.filter-box-panel').find('.dp2');
                    Filter.rangeCalendar($dp1, $dp2);
                    date1 = 0;
                    date2 = 0;
                }
            }, 'json');
        },

        clearCondition : function(_this){
            var obj = $(_this).closest('.filter-box-panel')
                .find('.filter-box-condition .element_filter[data-name="condition"] option')
                .empty()
                .parent()
                .html('<option value=""></option>')

            obj.selectpicker('refresh');

            return this;
        },

        clearConditionValue : function(_this){
            $(_this).closest('.filter-box-panel')
                .find('.filter-box-condition-value')
                .empty();

            return this;
        },

        /*
         getUrlParams : function(destination, copy_id, pci){
         Filter.getFromLocalStorage(destination, copy_id, pci, function(data){
         var url_params = [];
         if(!$.isEmptyObject(data)){
         var lich = 0;
         $.each(data, function(key, value){
         url_params.push('filters['+lich+']='+value['id']);
         lich++;
         });
         }
         return url_params;
         });
         },
         */

        getFilterInstaled : function(){
            var filters = [];
            var lich = 0;
            $('.search-filter .filters-installed .filter-install').each(function(i, ul){
                filters.push('filters['+lich+']='+$(ul).data('filter_id'));
                lich++;
            });

            return filters;
        },

        // apply : function($callback){
        //     var instanceContent = ContentReload.createInstance();
        //     iPreloader.implements.call(instanceContent);
        //
        //     Global.getInstance().setContentReloadInstance(instanceContent); // подовження роботи
        //
        //     instanceContent
        //         .setPreloader(this.preloader)
        //         .reDefinition()
        //         .prepareVariablesToGeneralContent(true)
        //         .setCallBackSuccessComplete($.isFunction($callback) ? $callback : null)
        //         .run();
        // },
        apply : function($callback){
            var instanceContent = ContentReload.createInstance();

            Global.getInstance().setContentReloadInstance(instanceContent); // подовження роботи

            instanceContent
                .clear()
                .setTypeAction(iAction.TYPE_FILTER)
                .reDefinition()
                .prepareVariablesToGeneralContent(true)
                .setCallBackSuccessComplete($.isFunction($callback) ? $callback : null)
                .run();
        },


        /*************************************************************
         *   Storage
         **************************************************************/
        getLocalStorageIndex : function(copy_id){
            var result = copy_id
            if(Filter.local_storage_index_prefix){
                result = copy_id + '_' + Filter.local_storage_index_prefix;
            }
            return result;
        },

        addToLocalStorage : function(destination, copy_id, pci, filter_id, callback){
            Filter.getFromLocalStorage(destination, copy_id, pci, function(data){
                var result = [];
                var new_filter = {
                    'id' : filter_id,
                };

                if(data){
                    if(data.length > 0){
                        result.push(data[0]);
                    }
                }
                result.push(new_filter);

                var lStorage = new LocalStorage();

                lStorage
                    .clear()
                    .setKey('list_filter')
                    .setPci(pci)
                    .setValueToServer(Filter.getLocalStorageIndex(copy_id), result, function(data){ callback(data); });
            });
        },

        getFromLocalStorage : function(destination, copy_id, pci, callback){
            var lStorage = new LocalStorage();

            lStorage
                .clear()
                .setKey('list_filter')
                .setPci(pci)
                .getValueFromServer(Filter.getLocalStorageIndex(copy_id), function(data){ callback(data); })
        },

        findInLocalStorage : function(destination, copy_id, pci, filter_id, callback){
            Filter.getFromLocalStorage(destination, copy_id, pci, function(data){
                var result = false;

                $.each($(data), function(key, value){
                    if(value['id'] == filter_id){
                        result = true;
                        return true;
                    }
                });
                callback(result);
            });
        },

        updateInLocalStorage : function(destination, copy_id, pci, old_filter_id, new_filter_id, callback){
            Filter.getFromLocalStorage(destination, copy_id, pci, function(data){
                var result = [];
                if(!data) return false;

                var lStorage = new LocalStorage();

                $(data).each(function(key, value){
                    result.push({ 'id' : (value['id'] != old_filter_id ? value['id'] : new_filter_id)});
                });

                if(result)
                    lStorage
                        .clear()
                        .setKey('list_filter')
                        .setPci(pci)
                        .setValueToServer(Filter.getLocalStorageIndex(copy_id), result, function(){ callback(); });
                else
                    lStorage
                        .clear()
                        .setKey('list_filter')
                        .setPci(pci)
                        .deleteFromServer(Filter.getLocalStorageIndex(copy_id), function(){ callback(); });
            });
        },

        deleteFromLocalStorage : function(destination, copy_id, pci, filter_id, callback){
            Filter.getFromLocalStorage(destination, copy_id, pci, function(data){
                var result = [];
                if(!data) return;
                var lStorage = new LocalStorage();

                $(data).each(function(key, value){
                    if(value['id'] == filter_id) return true;
                    result.push(value);
                });
                if(result)
                    lStorage
                        .clear()
                        .setKey('list_filter')
                        .setPci(pci)
                        .setValueToServer(Filter.getLocalStorageIndex(copy_id), result, function(){ callback(); });
                else
                    lStorage
                        .clear()
                        .setKey('list_filter')
                        .setPci(pci)
                        .deleteFromServer(Filter.getLocalStorageIndex(copy_id), function(){ callback(); });
            });
        },

        deleteAllFromLocalStorage : function(destination, copy_id, pci, callback){
            var lStorage = new LocalStorage();

            lStorage
                .clear()
                .setKey('list_filter')
                .setPci(pci)
                .deleteFromServer(Filter.getLocalStorageIndex(copy_id), function(){ callback(); });
        },





        /**
         * Init datepicker for single date
         * @param <Object> selector (current calendar)
         **/
        singleCalendar : function(selector) {
            selector.datepicker({
                language: Message.locale.language,
                format: Message.locale.dateFormats.medium_js,
                minDate: '1/1/1970',
                autoclose: true,
                orientation: "auto right",
            });
            selector.mask(Message.locale.dateFormats.mask_js);
        },

        /**
         * Init datepicker for single date
         * @param <Object> calendar1
         * @param <Object> calendar2
         **/
        rangeCalendar : function(calendar1, calendar2) {
            // First Calendar Init
            calendar1.mask(Message.locale.dateFormats.mask_js);
            calendar2.mask(Message.locale.dateFormats.mask_js);

            var $dp1, $dp2;
            var checkin = calendar1.datepicker({
                language: Message.locale.language,
                format: Message.locale.dateFormats.medium_js,
                minDate: '1/1/1970',
                autoclose: true,
                orientation: "auto right",
            }).on('show', function() {
                rangeShow($(this));
                $dp1 = $(this).closest('.datepicker-range').find('.dp1');
                $dp2 = $(this).closest('.datepicker-range').find('.dp2');

            }).on('changeDate', function (ev) {
                var newDate = new Date(ev.date);
                newDate.setDate(newDate.getDate() + 1);
                $dp2.datepicker('setDate', newDate);
                date1 = $dp1.datepicker('getDate');
                rangeShow($(this));
            }).on('hide', function() {
                //Filter.set('ModuleListViewRage')
            });

            // Second Calendar Init
            var dp1, dp2;
            var checkout = $('.dp2').datepicker({
                beforeShowDay: function (date) {
                    return {
                        enabled: date.valueOf() >= checkin.datepicker("getDate").valueOf(),
                    };
                },
                language: Message.locale.language,
                format: Message.locale.dateFormats.medium_js,
                minDate: '1/1/1970',
                autoclose: true,
                orientation: "auto right",
            }).on('show', function() {
                rangeShow($(this));
                $dp1 = $(this).closest('.datepicker-range').find('.dp1');
                $dp2 = $(this).closest('.datepicker-range').find('.dp2');
            }).on('changeDate', function (ev) {
                rangeShow($(this));
            }).on('hide', function() {
                //Filter.set('ModuleListViewRage')
            });
        },

    }



    /**
     * Show range in datepickers for period
     * @param <Object> calendar (current datepicker)
     **/
    var rangeShow = function(calendar) {
        $(".datepicker-days td").each(function(){
            var m = Message.locale.monthNames.wide;

            var month = m[$(".datepicker-days th").eq(1).text().split(" ")[0]], year = $(".datepicker-days th").eq(1).text().split(" ")[1];

            if($(this).hasClass('old')) month--;
            if($(this).hasClass('new')) month++;

            $(this).removeClass('startDay endDay');
            if (+(new Date(month+"."+$(this).text()+"."+year)) == +calendar.closest('.datepicker-range').find('.dp1').datepicker("getDate")) {
                $(this).addClass('startDay');
            }
            if (+(new Date(month+"."+$(this).text()+"."+year)) == +calendar.closest('.datepicker-range').find('.dp2').datepicker("getDate")) {
                $(this).addClass('endDay');
            }
            if(+(new Date(month+"."+$(this).text()+"."+year)) < +calendar.closest('.datepicker-range').find('.dp2').datepicker("getDate") && +(new Date(month+"."+$(this).text()+"."+year)) > +calendar.closest('.datepicker-range').find('.dp1').datepicker("getDate")){
                $(this).addClass('range');
            }
        });
    };

    var date1, date2;
    var dates = [];

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.Filter = Filter;
    exports.iFilter = iFilter;
})(window);
