
;(function (exports) {
    var _private, _public, _protected, Module,
        _self = {}; //link for instance

    var extPreloader_public = {
        constructor: function () {

            return this;
        },
        //розширенние
        extRun: function () {
            var spinner = Global.spinner,
                $container = $('#container');
//panel-body init-preloader center-position set-preloader
            switch (this._where_content_hide) {
                case extPreloader.TYPE_RELOAD_SECTION : {
                    this._$place_for_spinner = this.$object = this.getContainer().closest('.panel-body').addClass('hide_content_section');
                    this.getContainer().closest('li[data-name="panel"]').addClass('hide-nice-scroll');
                    spinner.get().not(':first').remove();
                    this.setDynamicClassByArray(['hide_all_in_page']);
                    this._position_spinner = Preloader.POSITION_SPINNER_WINDOW;
                    this._css_position = Preloader.css.ABSOLUTE;

                    this.setElement(this.$object.selector, ['hide_all_in_page']);

                    break;
                }
                    return this;
            }

            return this;
        }
    }

    var extPreloader = {
        TYPE_RELOAD_SECTION: 17,
        _instance: null,

        createInstance: function () {
            var Obj = function () {
                for (var key in extPreloader_public) {
                    this[key] = extPreloader_public[key];
                }
            }

            Obj.prototype = Object.create(Preloader.createInstance());
            return extPreloader._instance = new Obj().constructor();
        },
        getInstance: function () {
            return this._instance;
        },
    }

    _protected = {

    };
    _private = {
        onChange_checkbox : function(e){
            var $this = $(this),
                pl = $this.closest('.process_list'),
                instance = e.data.instance;

            if ($this.prop('checked')) {
                pl.find('.header_check').attr('checked', false);
                pl.find('.card_check').not($this.closest('ul').find('.card_check')).attr('checked', false);
                $this.prop('checked', true);
            } else {
                $this.prop('checked', false);
                pl.find('.header_check').attr('checked', false);
            }

            instance
                .setProcessColor()
                .setProcessButtons();
        },
        onClick_addCardSelect : function(e){
            var list,
                _data = {},
                instance = e.data.instance,
                $this = $(this);

            if ($this.closest('section').find('.process_view-save-input').length || $('.process_view_block[data-update-title]').length) {
                return;
            }

            ProcessView.setSortingListId($this.closest('section.panel').data('sorting_list_id'));

            list = $this.closest('.panel').find('.element[data-name="field_title"] .element[data-name="field_title_value"]');
            $.each(list, function(i, ul){
                _data[$(ul).data('field')] = $(ul).attr('data-value');
            });
            _data = {'default_data' : _data};

            instance.setParentElement($this.closest('section.panel'));

            EditView
                .createInstance()
                .addCardSelect($this.closest('.sm_extension'), 'process-view', _data);
        },
        //onClick_openCard
        onClick_openCard : function(el){
            var instance = el.data.instance;

            if ($('.process_view_block .participant-add.open').length) {
                return false;
            }
            EditView
                .setQueueStatus(true)
                .setDataToQueue({});

            instance.editData(el, this);
        },
        //onClick_openCardAuto
        onClick_openCardAuto : function(e){
            var instance = e.data.instance;

            if(!$(e.target).closest('.element[data-type="block_participant"]').length) {
                $('html').removeClass('overflowHidden');

                if (Global.getInstance().getCurrentInstance()._type == 'process') {
                    Global.showChildListEntities(this);

                    return;
                };

                var $this = $(this),
                    action_key = $this.data('action_key');

                instance.defaultState();
                instance.$panel_change = $this;

                if(typeof (action_key) != 'undefined' && action_key){
                    instanceGlobal.preloaderShow($this)
                        .contentReload
                        .setObject(this)
                        .setActionKey(action_key)
                        .prepareVariablesAuto()
                        .run();
                } else {
                    if ($('.process_view_block .participant-add.open').length) {
                        return false;
                    }
                    instance.editData(e, this);
                }
            }
        },
        onClickBtnDelete : function(e){
            var data_id_list = [],
                $this = $(this),
                instance = e.data.instance;

            if($this.closest('ul').find('.sm_extension_data input:checked').length == 0){
                Message.show([{'type': 'error', 'message': 'It should be noted entries'}], true);
                return;
            }

            var _function = function(){

                instanceGlobal.preloaderShow($this);
                $this.closest('ul').find('.sm_extension_data input:checked').each(function(i, ul){
                    data_id_list.push($(ul).closest('.sm_extension_data').data('id'));
                });

                $.post(Global.urls.url_list_view_delete + '/' + $this.closest('.sm_extension').data('copy_id'), {'id': data_id_list}, function(data){
                    if(data.status){
                        if(data.messages){
                            if(typeof data.deleted_id_list != 'undefined' && data.deleted_id_list){
                                $.each(data.deleted_id_list, function(key, id){
                                    $this.closest('.element[data-name="panel"]').find('.to-do-list .element[data-type="drop_down"][data-id="' + id + '"]').remove();
                                });
                            }
                            var sorting_list_id_list = instance.convert.dataIdToSortingListIdList(data_id_list)
                            instance.initLists(sorting_list_id_list);

                            instanceGlobal.contentReload.preloaderHide();
                            instance.checkAndRemoveEmptyPanel($this.closest('.element[data-name="panel"]'));
                            instance.setProcessButtons();

                            Message.show(data.messages, false);
                        } else {
                            $('.card_check[type="checkbox"]:checked').closest('li.element[data-type="drop_down"]').remove();
                            $this.closest('.panel-body').find('.slimscrolldiv').getNiceScroll().remove();

                            var sorting_list_id_list = instance.convert.dataIdToSortingListIdList(data_id_list)
                            instance.initLists(sorting_list_id_list);

                            instanceGlobal.contentReload.preloaderHide();
                            instance.checkAndRemoveEmptyPanel($this.closest('.element[data-name="panel"]'));
                            instance.setProcessButtons();
                        }

                    } else if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else if(data.status == false){
                        Message.show(data.messages, false);
                    }
                }, 'json');
            }

            Message.show([{'type':'confirm', 'message': Message.translate_local('Delete selected entries') + '?'}], false, function(_this_c){
                if($(_this_c).hasClass('yes-button')){
                    modalDialog.hide();
                    _function();
                }
            }, Message.TYPE_DIALOG_CONFIRM);


        },
        onClick_fieldsViewSettingsSave : function(e){
            if ($(e.target).closest('.communication-services').length) return;

            var $this = $(this),
                _data = {
                    'index' : $('.process_view_block').data('index'),
                    'fields_view' : $this.closest('.panel').find('.element[data-type="fields_view"]').val(),
                    'pci': $('.process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi': $('.process_view_block.sm_extension').data('parent_data_id'),
                    'this_template': $('.process_view_block.sm_extension').data('this_template'),
                    'finished_object' : ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
                };

            $.post(Global.urls.url_process_save_second_fields_view+'/'+$('.sm_extension').data('copy_id'), _data, function(data){
                if(data.status == false || data.status == 'error' || data.status == 'access_error'){
                    Message.show(data.messages, false);
                } else
                if(data.status){
                    modalDialog.hide();
                    ProcessView.active_fields_view = _data.fields_view;

                    instanceGlobal.preloaderShow($this).contentReload
                        .prepareVariablesToGeneralContent()
                        .run();
                }
            });
        },
        onClick_sorting : function(){
            var $settings = $('[data-type="fields_view_setting"]');

            instanceGlobal.preloaderShow($(this));
            $settings.attr('blocked','');
            Sorting.setThis(this)
                .init()
                .apply(function () {
                    $settings.removeAttr('blocked');
                });

        },
        onClick_addCard : function(e){
            var instance,
                $this = e.hasOwnProperty('currentTarget') ? $(this) : e;
            //TODO: this error url. show LV but має бути PV
            instance = e.data.instance || ViewType.getCurrentInstance();

            instance
                .setSortingListId($this.closest('section.panel').data('sorting_list_id'))
                .addCard($this);
        },
        onClick_showFieldsViewSettings : function(e){
            Message.show($('.element[data-type="fields_view_settings_dialog"]').html());
            $('.element[data-type="fields_view"] option[value="'+ProcessView.active_fields_view+'"]')
                .prop('selected', true)
                .closest('.element[data-type="panel"]')
                .find('.bootstrap-select')
                .remove();

            Global.initSelects();
        },
        onClickBtnCopy : function(e){
            var data_id_list = [],
                $this = $(this),
                instance = e.data.instance;

            instanceGlobal.preloaderShow($this);
            instance.setSortingListId($this.closest('.element[data-name="panel"]').find('section.panel').data('sorting_list_id'))

            if($this.closest('ul').find('.sm_extension_data input:checked').length == 0){
                Message.show([{'type':'error', 'message': 'It should be noted entries'}], true);
                return;
            } else {
                var key = $this.closest('section.panel').attr('data-unique_index');
                $.each($this.closest('ul').find('.sm_extension_data input:checked'), function(i, input){
                    var $input = $(input);
                    data_id_list.push($input.closest('.sm_extension_data').data('id'));
                });

                instance.initLists([key]);
            }

            params = {
                'id' : data_id_list,
                'pci' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id'),
                'pdi' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id'),
                'this_template' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('this_template'),
            }

            $.post(Global.urls.url_list_view_copy +'/'+$this.closest('.sm_extension').data('copy_id'), params, function(data){
                if(data.status == true){
                    data_id_list = data.id;

                    instanceGlobal.contentReload
                        .prepareVariablesToGeneralContent()
                        .appendVars({'module' : {
                            'data_id_list' : data_id_list,
                            'sorting_list_id' : ProcessView.sorting_list_id
                        }})
                        .setCallBackSuccessComplete(function () {
                            var sorting_list_id_list = instance.convert.dataIdToSortingListIdList(data_id_list);
                            instance.initLists(sorting_list_id_list);
                        })
                        .prepareVariablesToProcessView()
                        .run();

                } else if(data.status == 'access_error'){
                    Message.show(data.messages, false);
                } else if(data.status == false){
                    Message.show(data.messages, false);
                }
            }, 'json');
        },
        onClickPanelMenuAction : function(e){
            var _this = this;
            _action = $(_this).data('action'),
                instance = e.data.instance;

            switch(_action){
                case 'delete' :
                    instance.panelMenuActionDelete(_this);
                    break;
                case 'archive' :
                    instance.panelMenuActionArchive(_this);
                    break;
            }

        },
        onEditSelectBtnCreate : function(){
            var _this = this,
                instanceEV = EditView.createInstance();

            instanceEV.cardSelectValidate(this, function(data){
                if(data){
                    var _default_data = $(_this).closest('.edit-view.sm_extension').find('.default_data').text();
                    if(_default_data){
                        _default_data = JSON.parse(_default_data);
                    } else {
                        _default_data = null;
                    }

                    modalDialog.hide();
                    instanceEV.addCardFromTemplate(_this, _default_data);
                }
            })
        },
        onClickParticipantBtn : function() {
            dropDownPosition($(this));   // for use only in process-view
            $(this).next().css('position','fixed');
        },
        //отметить все чекбоксы
        onChangeChecbox: function(e){
            var $this = $(this),
                instance = e.data.instance,
                pl = $this.closest('.process_list');

            if ($this.prop('checked')) {
                pl.find('.card_check').attr('checked', false);
                pl.find('.header_check').attr('checked', false);
                $this.closest('li').find('.card_check').prop('checked', true);
                $this.prop('checked', true);
            } else {
                pl.find('.card_check').attr('checked', false);
                pl.find('.header_check').attr('checked', false);
            }

            instance
                .setProcessColor()
                .setProcessButtons();
        },
    };

    _public = {
        run: function () {
            this.setPreloader(extPreloader.createInstance());

            this.reDefinition()
                .parseUrlByCopyId();

            ViewType.init(this);

            this.afterLoadView()
                .afterViewChanges();
            return this;
        },
        constructor: function () {
            iModule.implements.call(this);
            iPreloader.implements.call(this);
            iLifeCycle.implements.call(this);

            this.setEmits();

            this.search = Search.createInstance();
            this.getFilter = Filter.createInstance();

            return this;
        },
        setEmits: function () {
            var _this = this;

            Events
                .createInstance()
                .setType(Events.TYPE_DESTROY)
                .setKey('ProcessViewResize')
                .setHandler(function (e) {
                    _this.scrollCall();

                    return true;
                })
                .run();

            return this;
        },
        reDefinition: function () {
            var _this = this;

            this.getPreloader = function () {
                return extPreloader.getInstance();
            }

            this.showPreloaderTemplate = function () {
                $('body').addClass('hide-edit-view');

                var where = Preloader.TYPE_RELOAD_СONTENT_PAGE,
                    place_spinner = $('.content-panel'),
                    parent = _this.getParentElement();

                if (parent) {
                    where = extPreloader.TYPE_RELOAD_SECTION;
                    place_spinner = parent || _this.$panel_change;
                    _this.$panel_change = null;
                    _this.setParentElement(null);
                    this.setContainer(place_spinner);
                    this.setElement('.hide-nice-scroll', ['hide-nice-scroll'])
                }

                this.setRunning(false)
                    .setElement($('body').selector, ['hide-edit-view'])
                    .setWhereContentHide(where)
                    .setPlaceForSpinner(place_spinner)
                    .run();
            };

            this.afterViewChanges = function () {
                this.events()
                    .bigWrapper()
                    .touchToBack();
                
                return this;
            };

            this.afterLoadView = function () {
                this.events()
                    .bigWrapper()
                    .touchToBack()
                    .allMethod();

                ViewType.afterLoadView();

                return this;
            };

            this.preloader.showPreloader = this.preloader.show = function () {
                // if (!this.isRunning()) { }
                _this.showPreloaderTemplate.call(_this.preloader);
                return this;
            };
            this.search.apply = function () {
                var url = this.getFullUrl(),
                    contentInstance = ContentReload.createInstance();

                iPreloader.implements.call(contentInstance);
                Global.getInstance().setContentReloadInstance(contentInstance);

                contentInstance
                    .clear()
                    .reDefinition()
                    .setPreloader(_this.preloader)
                    .prepareVariablesToGeneralContent()
                    .setUrl(url)
                    .run();
            };
            this.search.showPreloader = function () {
                _this.showPreloaderTemplate.call(_this.preloader);
                return this;
            };

            this.getFilter.apply = function () {
                var instanceContent = ContentReload.createInstance();

                iPreloader.implements.call(instanceContent);

                Global.getInstance().setContentReloadInstance(instanceContent); // подовження роботи

                instanceContent
                    .clear()
                    .setPreloader(_this.preloader)
                    .setTypeAction(iAction.TYPE_FILTER)
                    .reDefinition()
                    .prepareVariablesToGeneralContent(true)
                    .run();
            }

            return this;
        },
        events : function () {
            this._events = [
                { parent: document, selector: '.element[data-name="process_view_panel"] .element[data-type="drop_down"] a[data-controller="process_view_edit"]', event: 'click', func: _self.onClick_openCard },
                { parent: document, selector: '.element[data-name="process_view_panel"] .element[data-type="drop_down"]', event: 'click', func: _self.onClick_openCardAuto},
                { parent: document, selector: '.process_view_block li[data-name="panel"]:not([data-update-title]) .edit_view_dnt-add', event: 'click', func: _self.onClick_addCard },
                { parent: document, selector: '.process_view_block li[data-name="panel"]:not([data-update-title]) .edit_view_select_dnt-add', event: 'click', func: _self.onClick_addCardSelect},

                { parent: document, selector: '.element[data-name="process_view_fields_group"] li a', event: 'click', func: _self.onClick_sorting},

                //отметить чекбокс по одному
                { parent: document, selector: '.process_list > li .card_check', event: 'change', func: _self.onChange_checkbox },

                { parent: document, selector: '.process_view_block .element[data-type="fields_view_setting"]:not([blocked])', event: 'click', func: _self.onClick_showFieldsViewSettings},
                { parent: document, selector: '.modal-dialog .btn-primary.element[data-type="save"]', event: 'click', func: _self.onClick_fieldsViewSettingsSave},
                //process_view_btn-delete
                { parent: document, selector: '.process_view_btn-delete', event: 'click', func: _self.onClickBtnDelete},
                { parent: document, selector: '.process_view_btn-copy', event: 'click', func: _self.onClickBtnCopy }, //process_view_btn-copy
                { parent: document, selector: '.process-view .edit_view_select_btn-create', event: 'click', func: _self.onEditSelectBtnCreate },
                { parent: document, selector: '.process_wrapper .participant button', event: 'click', func: _self.onClickParticipantBtn },
                { parent: document, selector: '.process_view_block .element[data-name="panel"] .element[data-type="panel_menu"]', event: 'click', func: _self.onClickPanelMenuAction },
                { parent: document, selector: '.process_list > li .header_check', event: 'change', func: _self.onChangeChecbox }, //process_view_btn-copy
                //{ name: '', event: '', func: '' },
            ];

            Global.addEvents(this._events, {
                instance: this
            });
            return this;
        },
    };

    var ProcessWrapper = {
        scroll: 0
    }

    var ProcessList = {
        intervalCommon : null, //перемещение по списку с вправо и влево
        scrollMoveInterval : null,
        stepOfMove : 1,
        speedInterval : 3,
        setWidth: function () {
            if (this.$ && this.$.length) {
                this.$.width(this.$.find('>li').length * 300+this.$.find('>.btn-group').width());
            }
        },
        placeholder: {
            remove: function () {
                $('[data-is]').remove();
            },
            setHeight : function() { //placeholder под списком делаем высотой как список
                var height = $('.process_list .ui-sortable-helper .panel').css('height');
                ProcessList.$.find('>li.ui-state-highlight').css('height', height);
                ProcessList.$.find('>li.placeholder').css('height', height);
            },
            empty : function () {
                var li = document.createElement('li');
                li.className = "ui-state-highlight";
                li.setAttribute('data-is','');
                return li;
            },
            set : function (mouse) {
                var helper={};
                this.placeholder = {};

                this.placeholder.$ = ProcessList.$.find('.ui-state-highlight');
                helper.$ = ProcessList.$.find('.ui-sortable-helper');

                if (mouse.positionIsLeft()) {
                    if (this.placeholder.$.index()>0) {
                        this.placeholder.$.insertBefore(ProcessList.$.find('li:first'));
                        this.setHeight();
                    } else {
                        if (!this.placeholder.$.length) {
                            this.empty().insertBefore(ProcessList.$.find('li:first'));
                            this.setHeight();
                        }
                    }
                }
            },
            more50Percent : function (data) {
                var newColumn, balance, placeholder,
                    positionRightCorner = data.$.offset().left + data.width + ProcessWrapper.scroll,
                    pl = $('ul.process_list'),
                    list = pl.find('>li').not('.ui-sortable-helper').not('.ui-state-highlight');

                balance = positionRightCorner % data.width;
                if (balance > (data.width/2)) {
                    newColumn = Math.floor( positionRightCorner / data.width) - 1;
                    placeholder = pl.find('>li.ui-state-highlight');

                    if (placeholder.length) {
                        if (placeholder.index() != newColumn && newColumn>=0) {
                            placeholder.insertAfter(list.eq(newColumn));
                        }
                    }
                }
            },
            init : function($target) {
                var balance,
                    target = {
                        width: $target.width() + parseInt($target.css('margin-right')),
                        offsetLeft: ($target.offset().left<0 ? 0 : $target.offset().left) + ProcessWrapper.$.scrollLeft(),
                        $: $target
                    },
                    newOrder = Math.floor(target.offsetLeft / target.width),
                    placeholder = $('ul.process_list').find('>li.ui-state-highlight');

                if (placeholder.length>1) {
                    this.remove();
                    placeholder = $(placeholder.selector);
                }

                balance = target.offsetLeft % target.width;
                if (balance && balance > target.width/2) { // > 50%
                    newOrder += 2;
                }

                if (placeholder.length) {
                    if (placeholder.index() != newOrder) {
                        placeholder.insertAfter($('ul.process_list').find('>li').eq(newOrder));
                    }
                } else { //new element
                    $(this.empty()).insertBefore($('ul.process_list').find('>li').eq(newOrder+1));
                }

                this.more50Percent(target);
                this.setHeight();
            }
        },
        event:{
            mouseup : function () {
                ProcessList.$ = $('ul.process_list');
                ProcessWrapper.firstIteration = true;
                clearInterval(ProcessList.intervalCommon);
                clearInterval(ProcessList.scrollMoveInterval);
                ProcessList.intervalCommon = null;
                ProcessList.scrollMoveInterval = null;
                ProcessList.$.css('position', 'fixed');

                ProcessView.setNiceScroll(ProcessList.$.filter('>li .slimscrolldiv.scrollinit'));
                ProcessList.$.css({
                    position: 'relative'
                });
                $(document).off( "mousemove");
            }
        },
        scrollShow : function() {
            $('div.nicescroll-rails').delay(500).animate({"opacity": "1"}, "slow");
        },
        timerInnerStop : function() {
            clearInterval(this.scrollMoveInterval); // зупиняє таймер.
            this.scrollMoveInterval = null;

            return this;
        },
        setOffsetScroll : function () {
            var position = {},
                object = this,
                body = $('body');

            object.scrollMoveInterval = setInterval(function () {
                var $list = $('ul.process_list');

                if (Mouse.positionIsRight()) {
                    position.old = ProcessWrapper.$.scrollLeft() + object.stepOfMove;
                    position._new = ProcessWrapper.$.scrollLeft()+ body.width() > $list.width()
                        ? $list.width() - body.width() + object.stepOfMove  : position.old;
                } else {
                    if ((Mouse.positionIsLeft())) {
                        position.old = ProcessWrapper.$.scrollLeft() - object.stepOfMove;
                        position._new = position.old<=0 ? 0 : position.old;
                    }
                }

                if (Mouse.position && position._new == position.old) {
                    ProcessWrapper.$.stop().animate({'scrollLeft': position._new }, 0);

                    ProcessWrapper.scroll = position._new;
                } else {
                    if (position._new != position.old) {
                        if (Mouse.positionIsLeft()) {

                            object.placeholder.set(Mouse);
                        }
                        if (Mouse.positionIsRight()) {
                            object.placeholder.set(Mouse);
                        }
                    }
                }

            }, object.speedInterval);

            return this;
        },
        setScroll : function () {
            $.each($('.slimscrolldiv'), function (key, value) {
                var $this = $(value),
                    li = $this.closest('li'),
                    list = $this.find('>ul');

                if ($this.find('ul').height() <= list.height()) {
                    li.addClass('absolute');
                    ProcessView.setNiceScroll($this);
                    li.removeClass('absolute');
                }
            });

            return this;
        }
    }

    var UiSortable = {
        scrollMoveInterval: null,
        timer: {
            interval: 0,
            speed : 0,
            step : 1,
            clear : function() {
                clearInterval(this.interval); // зупиняє таймер.
                this.interval = null;
            }
        },
        verticalScroll: {
            interval: 0,
            speed : 100,
            step: 23,
            clear: function () {
                clearInterval(this.interval);
                this.interval = null;
            }
        },
        element: {}, // properties of current element
        event:{
            mouseup : function () {
                $(document).off( "mousemove");
                UiSortable.timer.clear();
                UiSortable.verticalScroll.clear();
            }
        },
        placeholder: {
            time: null,
            remove: function () {
                $('[data-is]').remove();
            },
            empty : function () {
                var li = document.createElement('li');
                li.className = "ui-state-highlight";
                li.setAttribute('data-is','');
                return li;
            },
            setHeight : function() { //placeholder под списком делаем высотой как список
                var height = parseInt( $('.process_list .ui-sortable-helper').css('height'));
                $('.process_list .ui-state-highlight').css({ height: height});
            },
            init : function($target) {
                var newColumn,
                    $list = $('ul.process_list'),
                    object = this,
                    borderForScroll = 120,
                    target = {
                        width: 300,
                        offsetTop: $target.offset().top - $target.offsetParent().offset().top,
                        offsetLeft: $target.offset().left + ProcessWrapper.$.scrollLeft(),
                        realOffsetTop: $target.offset().top,
                        isLastIndex: $target.index() == $target.parent().find('>li').index(),
                        currentCol: $target.parent().closest('li').index()
                    },
                    placeholder = $list.find('>li .ui-state-highlight'),
                    niceScroll = placeholder.closest('.scrollinit'),
                    scroll = niceScroll.length ? niceScroll.getNiceScroll(0).getScrollTop() : 0;

                target.newRow = Math.floor((target.offsetTop + scroll) / parseInt($target.css('min-height')));

                newColumn =  Math.floor(target.offsetLeft/target.width);
                target.isLastColumn = $list.find('>li').size()-1 == newColumn;

                if (placeholder.length>1) {
                    this.remove();
                    placeholder = $(placeholder.selector);
                }

                if (target.offsetLeft%target.width > target.width/2 && !target.isLastColumn) { // > 50%
                    newColumn++;
                }

                if ($target.height() != placeholder.height()) { // set height once
                    placeholder.height($target.height());
                }

                var ul = $list.find('>li').eq(newColumn).find('.ui-sortable'),
                    li = ul.find('li'),
                    ulParent = {
                        offsetTop: $target.closest('.slimscrolldiv').offset().top,
                        $: ul.parent()
                    },
                    niceScroll = ulParent.$.getNiceScroll(0),
                    setInLastPosition = function (li) {
                        li = li.last();
                        if (!placeholder.length) {
                            li.after($(object.empty()));
                        } else li.after(placeholder);
                    },
                    setInFirstPosition = function (li) {
                        li = li.first();
                        if (!placeholder.length) {
                            li.before($(object.empty()));
                        } else li.before(placeholder);
                    };

                if (!ul.length) return;

                ulParent.bottom = ulParent.offsetTop+ulParent.$.height();
                UiSortable.verticalScroll.clear();

                if (niceScroll) {
                    if (target.isLastIndex && !$target.data('once')) { // last item
                        niceScroll.doScrollTop(niceScroll.getScrollTop() + 1000);
                        $target.data('once', true);
                    } else {
                        if (target.realOffsetTop < ulParent.offsetTop + borderForScroll && placeholder.index() && niceScroll.getScrollTop()) {
                            UiSortable.verticalScroll.interval = setInterval(function () {
                                niceScroll.doScrollTop(niceScroll.getScrollTop() - UiSortable.verticalScroll.step);
                                // last position in list
                                if (niceScroll.getScrollTop() < 30) {
                                    setInFirstPosition(li);
                                    UiSortable.placeholder.setHeight();
                                    UiSortable.verticalScroll.clear();
                                }
                            }, UiSortable.verticalScroll.speed);
                        } else {
                            if ((target.realOffsetTop > ulParent.bottom - borderForScroll)
                                && (placeholder.index() != placeholder.parent().find('li').length) ) {
                                UiSortable.verticalScroll.interval = setInterval(function () {
                                    niceScroll.doScrollTop(niceScroll.getScrollTop() + UiSortable.verticalScroll.step);
                                    // last position in list
                                    if (li.parent().length) {
                                        if (li.parent().height() + li.parent().offset().top < $(window).height()
                                            && li.last().not('.ui-state-highlight').length) {
                                            setInLastPosition(li);
                                            UiSortable.placeholder.setHeight();
                                            UiSortable.verticalScroll.clear();
                                            niceScroll.doScrollTop(niceScroll.getScrollTop() + 500);
                                        }
                                    }
                                }, UiSortable.verticalScroll.speed);
                            }
                        }
                    }
                }

                if ($target.closest('ul').closest('li').index() != newColumn
                    && ul.parent().offset().top == ul.offset().top) {
                    if (!placeholder.length) {
                        li.eq(target.newRow).before($(this.empty()));
                    } else {
                        if (target.newRow-1 != placeholder.index() || target.isLastColumn) {
                            var element = li.eq(target.newRow).not('.ui-state-highlight');
                            if (!element) {
                                li.last().not('.ui-state-highlight').after(placeholder);
                            }
                            // else {
                            //     element.before(placeholder);
                            // }
                        }
                    };
                    this.setHeight();
                }

                var index = -1,
                    list = placeholder.closest('ul').find('>li');

                $.each(list, function (key, data) {
                    var cornerY,
                        $data = $(data);

                    if  ($data.offset().top<=0 || $data.is('.ui-state-highlight') || $data.is('.ui-sortable-helper'))
                        return true;

                    cornerY = ($target.offset().top+parseInt($target.css('height')) + parseInt($target.css('margin-bottom')));

                    if ($data.offset().top <= cornerY
                        && ($data.offset().top+ parseInt($data.css('height')) >= cornerY)) {
                        index = $data.index();
                        return false;
                    }
                });

                if (index>=0 && placeholder.length && placeholder.index() != index) {
                    li.eq(index).after(placeholder);
                    clearTimeout(object.time);
                    object.time = setTimeout(function () {
                        clearTimeout(object.time);
                        var placeIndex = $list.find('>li .ui-state-highlight').index();
                        if (index != placeIndex && UiSortable.timer.interval) {
                            li.eq(index).after(placeholder);
                        }
                    }, 550);
                }

                if (target.isLastIndex && UiSortable.countSort<=4) {
                    setInLastPosition(li);
                }
            }
        },
        multiSortEnd : function() { //вставка отобраных элементов в новый список
            $('ul').find('li.joined').each(function() {
                var lnm = $(this).attr('list-num');
                var inm = $(this).attr('ind-num');
                $('ul.process_list > li:nth-child('+lnm+') .panel-body ul li:nth-child('+inm+')').after(this);
            });
            $('ul').find('li.joined').removeClass('joined').css({
                'position': 'relative',
                'left':'auto',
                'top':'auto'
            });
            ProcessView
                .scrollCall();
        },
        setOffsetScroll : function () {
            var direction, leftCircleMouse,
                position = {},
                object = this,
                element = ProcessWrapper.$.find('.element[style*="absolute"]'),
                body = $('body');

            object.timer.interval = setInterval(function () {
                var $list = $('ul.process_list');

                direction = null;
                Mouse.init();

                if (Mouse.positionIsRight()) {
                    direction = true;
                    position.old = ProcessWrapper.$.scrollLeft() + object.timer.step;
                    position._new = ProcessWrapper.$.scrollLeft()+ body.width() > $list.width()
                        ? $list.width() - body.width() + object.timer.step  : position.old;
                } else {
                    if ((Mouse.positionIsLeft())) {
                        direction = false;
                        position.old = ProcessWrapper.$.scrollLeft() - object.timer.step;
                        position._new = position.old<=0 ? 0 : position.old;
                    }
                }
                if (Mouse.position && position._new == position.old) {
                    $list.find('.ui-state-highlight').remove();
                    ProcessWrapper.$.animate({'scrollLeft': position._new }, 0, function () {
                        element.offset({
                            left: direction ? element.offset().left + object.timer.step : element.offset().left - object.timer.step
                        });
                        leftCircleMouse = Mouse.axis.x + position._new;
                    });
                    ProcessWrapper.scroll = position._new;
                }

            }, object.timer.speed);
        }
    }

    var ProcessView = {
        _instance: null,
        _interface: 'processView',
        _type : 'processView',

        getInstance : function(status){
            if (!ProcessView._instance && status) {
                this.createInstance();
            }
            return this._instance;
        },
        checkOrDestroy: function () {
            var lastState, prevState, currentState,
                states = History.getStates();

            if (states) {
                lastState = states[states.length - 1];
                currentState = Url.parseFull(location.href);
                lastState.urlParams = Url.parseFull(lastState.url);

                if (currentState['id'] != lastState.urlParams ['id']) {
                    this.destroy();
                }
            }

            return states ? true : false;
        },
        createInstance : function(){
            var Obj = function(){
                for(var key in ProcessView){
                    this[key] = ProcessView[key];
                }

                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            return this._instance = new Obj().constructor();
        },

        afterLoadView: function () {

        },
        //вызов вертикального скролла для списков
        setNiceScroll: function(selector) {
            if (!selector) {
                return this;
            }

            selector.niceScroll({
                cursorcolor: "#1FB5AD",
                cursorborder: "0px solid #fff",
                cursorborderradius: "0px",
                cursorwidth: "3px",
                fixed: true,
                railalign: 'right',
                enablemousewheel: true,
                autohidemode: false
            });

            return this;
        },
        //static
        initLists : function (sorting_list_id_list) {
            var _this = this,
                instance;

            $.each(sorting_list_id_list, function (key, sorting_list_id) {
                var $section = $('section.panel[data-sorting_list_id="'+ sorting_list_id +'"]');

                $section.find('input[type="checkbox"]').prop('checked', false);
                $section.find('li.checked').removeClass('checked');
                $section.find('.todo-cards').addClass('hidden').next().removeClass('hidden');

                _this.scrollCall($section.closest('li'));
            });

            instance = this.getInstance();

            if (instance) {
                instance.afterViewChanges();
            }
        },
        touchToBack : function () {
            var scroll,
                instanceScroll = null,
                $wrapper = $('#process_wrapper');

            scroll = function (event, $wrapper) {
                this.axis = {
                    end: null,
                    start: {
                        x: event.clientX,
                        y: event.clientY
                    }
                };

                this.changeEvent(event);
                this.baseScroll = $wrapper.scrollLeft();

                this._direction = null;
                this.$container = $wrapper;

                this.init();
            }
            scroll.prototype = Object(null);
            scroll.prototype.changeEvent = function (event) {
                var delta;

                this.event = event;
                this.axis.end = {
                    x: event.clientX,
                    y: event.clientY
                }

                this._direction = (this.axis.end.x <= this.axis.start.x) ? false : true;
                delta = Math.abs(this.axis.end.x - this.axis.start.x);

                this._offset = !this._direction ? (this.baseScroll + delta) : (this.baseScroll - delta);
                this._offset = this._offset < 0 ? 0 : this._offset;

                return this;
            };
            scroll.prototype.init = function () {
                var _this = this;

                _this.$container.stop(true, true).animate({'scrollLeft': _this._offset }, 0);

                return this;
            };
            scroll.prototype.remove = function () {};

            $wrapper.draggable({
                cancel: '.panel-heading, .element[data-name="panel"]',
                distance: 10,
                start: function( event, ui ) {
                    instanceScroll = new scroll(event, $wrapper);
                },
                drag: function( event, ui ) {
                    instanceScroll.changeEvent(event).init();
                },
                stop: function( event, ui ) {
                }
            });

            return this;
        },
        runAfterAjaxSuccess : function (data, module, clear_cards) {
            if(data.status && data.panels){
                var data_id_list_moved = [];
                var data_id_list_adding = [];

                $.each(data.panels, function(sorting_list_id, panels){
                    var panel = $('.process_view_block .element[data-name="process_view_panel"] li .panel[data-sorting_list_id="'+sorting_list_id+'"]');

                    if(panel.length){
                        if(typeof panels.cards == 'undefined' || !panels.cards || $.isEmptyObject(panels.cards)) return true;
                        panel = panel.find('.to-do-list');

                        $.each(panels.cards, function(sorting_cards_id, card_html){
                            var ch = card_html;
                            var data_id = $(ch).data('id');

                            data_id_list_adding.push(data_id);
                            if(clear_cards && module.sorting_list_id != sorting_list_id){
                                data_id_list_moved.push(data_id);
                            }

                            var card = panel.find('.element[data-type="drop_down"][data-sorting_cards_id="'+sorting_cards_id+'"]');
                            if(card.length){
                                card.after(card_html).remove();
                            } else {
                                panel.append(card_html);
                            }
                        });

                    } else {
                        var cards_html = '';
                        if(typeof panels.cards == 'undefined' || !panels.cards || $.isEmptyObject(panels.cards)) return true;

                        $.each(panels.cards, function(sorting_cards_id, card_html){
                            var ch = card_html;
                            var data_id = $(ch).data('id');

                            data_id_list_adding.push(data_id);
                            if(clear_cards && module.sorting_list_id != sorting_list_id){
                                data_id_list_moved.push(data_id);
                            }

                            cards_html+=card_html;
                        });

                        if(!cards_html){
                            return true;
                        }

                        $('.process_view_block .element[data-name="process_view_panel"]')
                            .append(panels.html)
                            // для блика
                            .find('.element[data-name="panel"] .panel[data-sorting_list_id="'+sorting_list_id+'"]').closest('.element[data-name="panel"]').css('display', 'none');

                        var panel = $('.process_view_block .element[data-name="process_view_panel"] li .panel[data-sorting_list_id="'+sorting_list_id+'"]');
                        if(panel.length){
                            panel.find('.to-do-list').html(cards_html)
                        }
                    }
                });


                // удаляем перенесенные карточки в другой список processView
                if(!$.isEmptyObject(data_id_list_moved)){
                    $.each(data_id_list_moved, function(key, data_id){
                        $('.process_view_block .element[data-name="process_view_panel"] .panel[data-sorting_list_id="'+module.sorting_list_id+'"] li.element[data-type="drop_down"][data-id="'+data_id+'"]').remove();
                    });
                }

                // удаляем перенесенные карточки в другой список processView, что отсутвует на странице
                if(!$.isEmptyObject(module.data_id_list)){
                    /*
                     var inObject = function(data_id, data_id_list){
                     var result = false;
                     if($.isEmptyObject(data_id_list)){
                     return result;
                     }
                     $.each(data_id_list, function(key, data_id_obj){
                     if(data_id_obj == data_id){
                     result = true;
                     return false;
                     }
                     });

                     return result;
                     }
                     */

                    $.each(module.data_id_list, function(key, data_id){
                        if($.isEmptyObject(data_id_list_adding) || $.inArray(parseInt(data_id), data_id_list_adding) < 0){
                            $('.process_view_block .element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + module.sorting_list_id + '"] li.element[data-type="drop_down"][data-id="' + data_id + '"]').remove();
                        }
                    });

                }


                // для блика
                $('.process_view_block .element[data-name="process_view_panel"] .element[data-name="panel"] .panel').each(function(i, ul){
                    $(ul).closest('.element[data-name="panel"]').css('display', '');
                })
            }
        },
        convert : {
            dataIdToSortingListIdList : function (data) {
                var list = [];

                $.each(data, function (key, value) {
                    var sorting_list_id = $('section.panel').has('li[data-id="'+ value +'"]').data('sorting_list_id');

                    if ($.inArray(sorting_list_id, list) < 0) {
                        list.push(sorting_list_id);
                    }
                });

                return list;
            }
        },
        defaultState : function(){
            var $wrapper = $('.process_wrapper ');

            $wrapper.find('.checked').removeClass('checked');
            $wrapper.find('input').prop('checked', false);
            this.setProcessButtons();

            return this;
        },
        setProcessButtons : function(status) { //смена кнопок
            var $pList = $('.process_list > li');
            $pList.find('.btn-add-card').removeClass('hidden');
            $pList.find('.todo-cards').addClass('hidden');

            if (status == false) {
                var $lists = $('.process_list .to-do-list li.checked');

                $lists.find('.todo-cards').removeClass('hidden');
                $lists.find('.btn-add-card').addClass('hidden');

                return this;
            }

            $('.process_list .to-do-list li.checked').each(function() {
                var $lists = $(this).closest('.process_list > li');

                $lists.find('.todo-cards').removeClass('hidden');
                $lists.find('.btn-add-card').addClass('hidden');
            });
            return this;
        },
        allMethod : function () {
            var _this = this;

            //вызов jQuery UI сортировки для страницы Process-view
            ProcessList.$ = $('ul.process_list');
            this.initUiSort();

            if ($('.process_view_block').length) {
                //ширина окна process wiev под списки
                ProcessList.setWidth();
            }

            //fixing cut off fix
            $(document).on('mousedown', 'ul.process_list > li .slimscrolldiv', function(e){
                e.stopPropagation();
            });
            $(document).on('mousedown', 'ul.process_list > li .header_check', function(e){
                e.stopPropagation();
            });
            $(document).on('mousedown', 'ul.process_list > li i.fa', function(e){
                e.stopPropagation();
            });

            $(document).on('mousedown', 'ul.process_list li[data-type="drop_down"]', function () {
                ProcessViewBase.saveTitles();
                $(document).on( "mousemove", Mouse.event.mousemove);
                UiSortable.countSort = 0;
            });
            $(document).on('mouseup', 'ul.process_list li[data-type="drop_down"]', UiSortable.event.mouseup);

            //cut off fix
            $(document).on('mousedown','ul.process_list > li', function(){
                relFix();
                $(document).on( "mousemove", Mouse.event.mousemove);
                ProcessList.width = $(this).width();
                $('ul.process_list').css({
                    position: 'static'
                });
            });
            $(document).on('mouseup', 'ul.process_list > li', ProcessList.event.mouseup);

            //удаление по класу checked на карточке
            $('button.process_card_delete').on('click',function() {
                $(this).closest('li').find('li.checked').remove();

                _this
                    .setProcessButtons()
                    .scrollCall();
            });

            //копирование по класу checked на карточке
            $('button.process_card_copy').on('click',function() {
                $(this).closest('li').find('li.checked').clone(true).appendTo($(this).closest('li').find('ul.to-do-list'));
                _this.scrollCall();
            });

            ProcessWrapper.$ = $('.process_wrapper');
            ProcessWrapper.$.scroll(function () {
                var $this = $(this),
                    $list = $('ul.process_list');

                ProcessWrapper.scroll = $this.scrollLeft();
                $this.find('.submodule-link.open').removeClass('open');
                if ($list.length) {
                    var placeholder = $list.find('.ui-state-highlight');

                    if ($('body').width()+$this.scrollLeft() > $list.width()) { // last $list
                        if ($list.find('>li').index() != placeholder.index()) {
                            placeholder.insertAfter($list.find('>li:last'))
                        }
                    } else {
                        if ($this.scrollLeft()< $list.width()-50 && !$list.offset().left) {
                            placeholder.insertBefore($list.find('>li:first'))
                        }
                    }
                }
            });

            this
                .initElements(this)
                .loadedOfImages();

            setTimeout(function() {
                ProcessView.scrollCall();
            }, 100);

            return this;
        },
        loadedOfImages : function(){
            var $images = $('.process_wrapper  img'),
                counter = $images.length,
                object = this;

            $images.one("load", function() {
                counter--;
                if (counter == 0) {
                    object.scrollCall();
                    ProcessList.setScroll();
                }
            }).each(function() {
                if(this.complete) $(this).load();
            });

            return this;
        },
        setProcessColor : function() { //цвет карточки в зависимости от чекбокса
            $('.process_list .to-do-list li .card_check').each(function() {
                var $this = $(this);

                if ( $this.prop('checked') ) {
                    $this.closest('li').addClass('checked');
                } else {
                    $this.closest('li').removeClass('checked');
                }
            });

            return this;
        },
        bigWrapper : function() { //расчет высоты для большого горизонтального скролла
            if ($('body ul').is('.process_list')) {
                $('section.wrapper').height($(window).height() - $('section.wrapper').offset().top );
                $('div.process_wrapper').height($(window).height() - $('ul.process_list').offset().top );
                $('section.wrapper').css({'padding-bottom': '0', 'padding-top': '0', 'position':'fixed'});
                $('html').addClass('overflowHidden');
            }

            return this;
        },
        scrollCall : function($element) { //пересчет высоты списков
            var $list = $element || $('ul.process_list > li').not('.ui-sortable-helper');

            $.each($list, function() {
                var $this = $(this),
                    liSortTop =  $this.offset().top,
                    liSortHeight = $this.find('ul.to-do-list').height() + 130,
                    liSortSumm = liSortTop + liSortHeight,
                    wHeight = $(window).height(),
                    $element = $this.find('.slimscrolldiv');

                $element.css('height', 'auto');
                if (liSortSumm >= wHeight) {
                    var shift = liSortSumm - wHeight;
                    $element.filter('.scrollinit').removeClass('scrollinit');
                    $element.css('height' , liSortHeight - 130 - shift).addClass('scrollinit');
                } else {
                    $element.removeClass('scrollinit').attr('style','');
                }
            });

            this.bigWrapper();

            return this;
        },
        changeParam : function () {
            /*
             var obj = Base.parseUrl(location.href),
             $pv = $('.process_view_block');

             $pv.attr({
             'data-parent_copy_id': obj.pci,
             'data-parent_data_id': obj.pdi,
             });
             */
        },
        setScroll: function ($element) { // fixActivityInEditView
            var $list = $element || $('.element[data-name="process_view_panel"]').find('.slimscrolldiv');

            $.each($list, function () {
                var $this = $(this),
                    $li = $this.closest('li');

                $li.addClass('absolute');
                $this.getNiceScroll().remove();
                ProcessView.setNiceScroll($this);
                $li.removeClass('absolute');
            });
        },
        // состояние обновления
        process_view_update_result : true, // static
        // панель, что в настоящий момет обновляется
        process_view_update_this : '', // static

        sorting_list_id : null, // static
        sorting_cards_id_list : [], // static

        active_fields_view : null,

        initElements : function(link){
            var processListItem = $('ul.process_list > li');
            link ? link.scrollCall() : this.scrollCall();

            processListItem.css('position', 'fixed');
            ProcessView.setNiceScroll(processListItem.find('.slimscrolldiv.scrollinit'));
            processListItem.css('position', 'relative');
            $('div.process_wrapper').addClass('static');
            $('.page_process_view').removeClass('page_process_view');

            return this;
        },

        //инициализация UI sortable для списков
        initUiSort : function(){
            var _this = this;

            $( ".ui-sortable" ).sortable({
                cancel: ".dropdown-menu",
                connectWith: ".to-do-list",
                placeholder: {
                    element: function(clone, ui) {
                        return $('<li class="ui-state-highlight"></li>');
                    },
                    update: function(event,ui) {
                        var helper = ProcessList.$.find($('.ui-sortable-helper'));
                        helper.length ? UiSortable.placeholder.init(helper) : '';
                        return;
                    }
                },
                scroll: false,
                delay: 150,
                change: function( event, ui ) {
                    _this.setProcessButtons();
                },
                sort: function( event, ui ) {
                    UiSortable.countSort++;
                    //_this.setProcessButtons();
                    multiSortBgn();

                    if (ui.helper.length>1) UiSortable.placeholder.remove();

                    if (Mouse.position && !UiSortable.timer.interval) {
                        UiSortable.setOffsetScroll(); // start timer
                    } else {
                        if (!Mouse.position && UiSortable.timer.interval) {
                            UiSortable.timer.clear();
                        }
                        //UiSortable.placeholder.init(ui.helper);
                    }
                },
                over: function( event, ui ) {
                    _this.scrollCall();
                    var helper = ProcessList.$.find($('.ui-sortable-helper'));
                    //helper.length ? UiSortable.placeholder.init(helper) : '';
                    //UiSortable.placeholder.init(ui.helper);
                },
                out: function( event, ui ) {
                },
                update: function( event, ui ) {
                    _this
                        .setSortingListId($(ProcessView.process_view_update_this).data('sorting_list_id'))
                        .setSortingCardsIdList();
                    ProcessView.setProcessButtons(false);
                },
                start: function( event, ui ) {
                    ProcessView.lastPlaceMarkDel();
                    ProcessList.$.addClass('task');
                },
                stop: function( event, ui ) {
                    UiSortable.multiSortEnd();

                    var update_data = ProcessView.getUpdateData();

                    ProcessView.cardSort(function(data){
                        if(data.status){
                            ProcessView.updateData(update_data, function(list){
                                ProcessView.setProcessButtons();
                            });
                        }
                    });


                    $('.sm_extension_data').removeClass('update');
                    UiSortable.timer.clear();
                    //recUnFix();
                    ProcessList.$.removeClass('task');
                    ProcessList.setWidth();
                    ProcessList.setScroll();

                    //отметить чекбокс по одному
                    $('.process_list > li .card_check').off('change').on('change', _this.onChange_checkbox);
                    return ProcessView.process_view_update_result;
                },
                beforeStop: function( event, ui ) {
                    ProcessView.process_view_update_this = $('.ui-state-highlight').closest('.panel');
                },
                receive : function( event, ui ){
                    ProcessView.lastPlaceMark(ui);
                },
            }).disableSelection();

            ProcessWrapper.firstIteration = true;
            $('ul.process_list').sortable({
                cancel: ".dropdown-menu,.panel-body, .btn-group, .fa-cog, input[type='checkbox'], .panel-heading a, .panel-heading input",
                placeholder: "ui-state-highlight",
                connectWith: "[data-name='panel']",
                delay: 150,
                sort: function( event, ui ) {
                    if (ProcessWrapper.firstIteration) {
                        ProcessList.placeholder.setHeight();
                        ProcessWrapper.firstIteration = !ProcessWrapper.firstIteration;
                    }

                    if (ui.helper.length>1) ProcessList.placeholder.remove();

                    if (Mouse.position && !ProcessList.scrollMoveInterval) {
                        ProcessList.setOffsetScroll(); // start timer
                    } else {
                        if (!Mouse.position && ProcessList.scrollMoveInterval) {
                            ProcessList.timerInnerStop();
                        }
                        ProcessList.placeholder.init(ui.helper);
                    }
                },
                update: function( event, ui ) {
                    _this.scrollCall();
                },
                start: function( event, ui ) {
                    relFix();
                },
                beforeStop: function( event, ui ) {
                    ProcessView.panelSort();
                    ProcessView.process_view_update_this = $('.ui-sortable-helper');
                },
                stop: function( event, ui ) {
                    relUnfix();
                    ProcessList.scrollShow();
                    _this.scrollCall();
                    ProcessList.timerInnerStop();
                    ProcessList.$.find('.ui-state-highlight').remove();
                    ProcessList.setWidth();
                }
            }).disableSelection();

            return this;
        },

        //demark the lists
        lastPlaceMarkDel : function(){
            $('ul.lastlist').removeClass('lastlist');
        },

        //mark the list where card is removed
        lastPlaceMark : function(ui){
            if (ui.sender !== null) {
                ui.sender.addClass('lastlist');
            }
        },

        //checkAndRemoveEmptyPanel
        checkAndRemoveEmptyPanel : function(panel){
            var _this = this;

            if($(panel).find('.to-do-list li').length == 0){
                var sorting_list_id = $(panel).find('section.panel').data('sorting_list_id');

                if($('.element[data-name="panel"]').length > 1){
                    ProcessView.panelSortDelete(sorting_list_id, function(data){
                        if(data.status){
                            $('.element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + sorting_list_id + '"]').closest('.element[data-name="panel"]').remove();
                        }
                    });
                } else {

                    ProcessView.panelSortDelete(sorting_list_id, function(data){
                        if(data.status){
                            if(data.add_panel == false){
                                $('.element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + sorting_list_id + '"]').closest('.element[data-name="panel"]').remove();
                            }

                            if(data.add_panel){
                                ProcessView.addPanel(function(data2){
                                    $('.element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + sorting_list_id + '"]').closest('.element[data-name="panel"]').remove();

                                    var process_view_panel = $('.process_view_block .element[data-name="process_view_panel"]');
                                    process_view_panel.html(data2.html)
                                });
                            }
                        }
                    });
                }
            }
        },


        setSortingListId : function(sorting_list_id){
            ProcessView.sorting_list_id = sorting_list_id;

            return this;
        },

        getSortingListId : function(_this){
            return $(_this).closest('.element[data-name="panel"]').find('section.panel').data('sorting_list_id');
        },

        setSortingCardsIdList : function(sorting_cards_id_list){
            if(typeof sorting_list_id == 'undefined'){
                sorting_cards_id_list = ProcessView.getSortingCardsIdList();
            }

            ProcessView.sorting_cards_id_list = sorting_cards_id_list;
        },


        getSortingCardsIdList : function(){
            var list = [];
            $('.sm_extension_data.update').each(function(){
                list.push($(this).data('sorting_cards_id'));
            });
            return list;
        },



        getUpdateDataIdList : function(){
            var data_id_list = [];
            $('.sm_extension_data.update').each(function(){
                data_id_list.push($(this).data('id'));
            });
            return data_id_list;
        },


        getUpdatesortingCardsIdList : function(){
            var sorting_cards_id_list = [];
            $('.sm_extension_data.update').each(function(){
                sorting_cards_id_list.push($(this).data('sorting_cards_id'));
            });
            return sorting_cards_id_list;
        },

        getSortingCardsIdBefore : function(){
            var parent_index = -1,
                sorting_cards_id = -1,
                $pvUpdate = $(ProcessView.process_view_update_this);

            $pvUpdate.find('ul.to-do-list > li').each(function(i, ul){
                if($(ul).hasClass('update')){
                    parent_index = $(ul).index();
                    return false;
                }
            });
            if(parent_index > 0){
                sorting_cards_id = $pvUpdate
                    .find('ul.to-do-list > li')
                    .eq(parent_index-1)
                    .data('sorting_cards_id');
            }
            return sorting_cards_id;
        },

        getUpdateDataParentId : function(){
            var parent_index = -1,
                parent_unique_index = -1,
                $pvUpdate = $(ProcessView.process_view_update_this);

            $pvUpdate.find('ul.to-do-list > li').each(function(i, ul){
                if($(ul).hasClass('update')){
                    parent_index = $(ul).index();
                    return false;
                }
            });
            if(parent_index > 0){
                parent_unique_index = $pvUpdate
                    .find('ul.to-do-list > li')
                    .eq(parent_index-1)
                    .data('id');
            }
            return parent_unique_index;
        },

        //editData
        editData : function(el, _this){
            delete window.backForward;

            ProcessView.setSortingListId($(_this).closest('section.panel').data('sorting_list_id'));

            var tag_name,
                instanceEV = EditView.createInstance().setParent(this).setPreloader(this.getPreloader()),
                select = $(el.target).closest('.element[data-type="select"]');

            if(typeof(select) != 'undefined' && select.length > 0) return;

            tag_name = el.target.tagName.toLowerCase();
            if(tag_name == 'input' || tag_name == 'a') return;

            this.setParentElement($(_this));

            instanceEV
                .editCard(_this, null, function(data){
                    if(data.status == 'data'){
                        this.setBlockDisplayStatus($('.edit-view[data-copy_id="'+data.copy_id+'"]'));
                        EditView.activityMessages.init();
                        textAreaResize();

                        Global.addOperationInSDM();

                        Global.createLinkByEV($('.edit-view.in:last'));
                        jScrollInit();
                        niceScrollCreate($('.submodule-table'));
                        imagePreview();
                        $('.form-control.time').each(function(){
                            initElements('.edit-view', $(this).val());
                        });
                        EditView.changeBlockLoadedMessages();
                    }
                });

            el.stopPropagation();
        },


        getFieldsGroupList : function(){
            var fields_group_list = [];
            $('.process_view_block .element[data-name="process_view_fields_group"] li.active a').each(function(i, ul){
                var field_names = $(ul).data('name');
                field_names = field_names.split(',');
                $.each(field_names, function(key, field_name){
                    fields_group_list.push(field_name);
                });
            });

            return fields_group_list;
        },

        getUpdateData : function(){
            var data_id_list,
                result = {},
                fields_group_values = {};

            ProcessView.process_view_update_result = true;
            data_id_list = ProcessView.getUpdateDataIdList();

            //TODO: move to ProcessViewBase.getFieldId
            $(ProcessView.process_view_update_this).find('.element[data-name="field_title_value"]').each(function(){
                var id_field = $(this).data('value');
                fields_group_values[$(this).data('field')] = id_field;
            });

            if(data_id_list.length > 0){
                result = {
                    'data_id_list': data_id_list,
                    'fields_group_values': fields_group_values,
                    'this_template': $('.process_view_block.sm_extension').data('this_template'),
                    'fields_group': this.getFieldsGroupList(),
                    'sorting_list_id': ProcessView.process_view_update_this.data('sorting_list_id'),
                    'pci': $('.process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi': $('.process_view_block.sm_extension').data('parent_data_id'),
                    'finished_object' : ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
                };
            }

            return result;
        },


        //updateData - static
        updateData : function(_data, callback){
            var _this = this;

            if($.isEmptyObject(_data)){
                ProcessView.process_view_update_result = false;
                return callback();
            }

            $.ajax({
                url: Global.urls.url_process_view_update +'/'+$('.sm_extension').data('copy_id'),
                data: _data,
                type: "POST", dataType: 'json', async : false,
                success: function(data){
                    if(data.status == true){
                        _this.runAfterAjaxSuccess(data, _data, false);
                        /*
                         if(data.html){
                         $.each(data.html, function(data_id, html){
                         $(processView.process_view_update_this).find('.sm_extension_data[data-id="'+data_id+'"]').after(html).remove();
                         });

                         }
                         */

                        ProcessView.process_view_update_result = true;

                        $('.to-do-list.lastlist').each(function(i, ul){
                            ProcessView.checkAndRemoveEmptyPanel($(ul).closest('.element[data-name="panel"]'));
                        })
                    } else {
                        Message.show(data.messages, false);
                    }
                    callback(data.html);
                },
                error: function(){
                    //callback();
                    ProcessView.process_view_update_result = false;
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                }
            });
        },

        //cardSort - static
        cardSort : function(callback){
            if($(ProcessView.sorting_cards_id_list).length == 0){
                return;
            }

            $.ajax({
                url: Global.urls.url_process_view_card_sort +'/'+$('.sm_extension').data('copy_id'),
                data: {
                    'sorting_list_id' : ProcessView.sorting_list_id,
                    'sorting_cards_id_before' : ProcessView.getSortingCardsIdBefore,
                    'sorting_cards_id_list' : ProcessView.sorting_cards_id_list,
                    'pci' : $('.process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi' : $('.process_view_block.sm_extension').data('parent_data_id'),
                },
                type: "POST", dataType: 'json',
                success: function(data){
                    if(typeof callback == 'function'){
                        callback(data);
                    }
                },
                error: function(){
                    if(typeof callback == 'function'){
                        callback();
                    }
                }
            });
        },

        //panelSort
        panelSort : function(){
            var highlight = $('ul.element[data-name="process_view_panel"] > li.ui-state-highlight');
            var index = highlight.index();
            var index_parent = -1;
            var sorting_list_id_before = -1;

            // если одна панель
            if(index == 1 && $('ul.element[data-name="process_view_panel"] > li:not(.ui-state-highlight)').length == 1) return;
            index--;
            if(index > 0) index_parent = index - 1;

            if(index_parent > -1) sorting_list_id_before = $('ul.element[data-name="process_view_panel"] > li').eq(index_parent).find('section.panel').data('sorting_list_id');

            var sorting_list_id = $('ul.element[data-name="process_view_panel"] > li').eq(index).find('section.panel').data('sorting_list_id');



            $.post(Global.urls.url_process_view_panel_sort + '/' + $('.sm_extension').data('copy_id'), {
                'sorting_list_id_before' : sorting_list_id_before,
                'sorting_list_id' : sorting_list_id,
                'pci' : $('.process_view_block.sm_extension').data('parent_copy_id'),
                'pdi' : $('.process_view_block.sm_extension').data('parent_data_id'),
                'this_template' : $('.process_view_block.sm_extension').data('this_template'),
                'finished_object' : ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
            });
        },

        //panelSortDelete
        panelSortDelete : function(sorting_list_id, callback){
            $.post(
                Global.urls.url_process_view_panel_sort_delete + '/' + $('.sm_extension').data('copy_id'),
                {'sorting_list_id' : sorting_list_id},
                function(data){
                    if(typeof callback == 'function'){
                        callback(data);
                    }
                }
            );
        },


        addPanel : function(callback){
            var copy_id = $('.process_view_block.sm_extension').data('copy_id');

            $.ajax({
                url: $('#global_params').data('url_process_view_get_panel') + '/' + copy_id,
                dataType: "json", type: "POST",
                data : {
                    'this_template' : $('.process_view_block.sm_extension').data('this_template'),
                    'pci' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id'),
                    'fields_group': ProcessView.getFieldsGroupList(),
                    'this_template' : $('.process_view_block.sm_extension').data('this_template'),
                    'finished_object' : ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
                },
                success: function(data){
                    callback(data)
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                },
            });
            /*
             .done(function(){
             $('.process_view_dnt-add_list').closest('.btn-group').appendTo('.process_list');
             });
             */
        },



        //addNew
        addCard : function(_this){
            var default_data = {},
                $list = $(_this).closest('.panel').find('.element[data-name="field_title"] .element[data-name="field_title_value"]'),
                instanceEV = EditView.createInstance().setParent(this).setPreloader(this.getPreloader());

            $.each($list, function(i, ul){
                default_data[$(ul).data('field')] =  $(ul).attr('data-value');
            });

            this.setParentElement(_this); // потрібно забрати і вести через EV. Це костиль.

            instanceEV
                .setParentElement(_this)
                .addCard(_this, {'default_data' : default_data}, function(data){
                    if(data.status == 'data'){
                        instanceEditView = new EditViewContainer(data);
                        modalDialog.show(data.data, true);
                        EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+data.copy_id+'"]'));

                        Global.addOperationInSDM();

                        History.add(data.copy_id, '', {});
                        // niceScrollInit();
                        EditView.activityMessages.init();
                        textAreaResize();
                        jScrollInit();
                        niceScrollCreate($('.submodule-table'));
                        var $modal = $(modalDialog.getModalName()).find('.client-name');
                        if ( $modal.find('span').first().text() == "" ) {
                            $modal.find('.edit-dropdown').first().addClass('open');
                        }
                        instanceGlobal.contentReload.preloaderHide();
                    }
                    EditView.hiddenBlocks();
                });
            },

        headerCheck : function($this){
            var _this = this;

            $this.on('change', function(){
                if ($this.prop('checked')) {
                    $this.closest('.process_list').find('.card_check').attr('checked', false);
                    $this.closest('.process_list').find('.header_check').attr('checked', false);
                    $this.closest('li').find('.card_check').prop('checked', true);
                    $this.prop('checked', true);
                } else {
                    $this.closest('.process_list').find('.card_check').attr('checked', false);
                    $this.closest('.process_list').find('.header_check').attr('checked', false);
                }

                _this
                    .setProcessColor()
                    .setProcessButtons();
            });

            return this;
        },

        panelMenuSendAjax : function(_url, _data, callback){
            $.ajax({
                url: _url,
                dataType: "json",
                type: "POST",
                data : _data,
                success: function(data) {
                    callback(data);
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                },
            });
        },

        panelMenuActionDelete : function(element){
            var isset_entityes = $(element).closest('.panel').find('.to-do-list > .element[data-type="drop_down"]').length,
                _this = this;

            if(isset_entityes){
                var message = 'Delete list and all entities';
            } else {
                var message = 'Delete list';
            }


            var _function = function(){
                var copy_id = $('.process_view_block.sm_extension').data('copy_id');
                var _data = {
                    'run_action': 'delete',
                    'sorting_list_id': $(element).closest('.panel').data('sorting_list_id'),
                    'pci': $('.process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi': $('.process_view_block.sm_extension').data('parent_data_id'),
                    'this_template': $('.process_view_block.sm_extension').data('this_template'),
                    'finished_object': ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
                };

                ProcessView.panelMenuSendAjax(Global.urls.url_process_view_panel_menu_action_run + '/' + copy_id, _data, function(data){
                    if(data.status == false || data.status == 'access_error'){
                        if(typeof data.messages != 'undefined'){
                            Message.show(data.messages, false);
                        }
                    } else if(data.status == true){
                        $('.element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + _data.sorting_list_id + '"]').closest('.element[data-name="panel"]').remove();

                        /*
                         if($('.element[data-name="panel"]').length == 0){
                         processView.addPanel(function(data){
                         var process_view_panel = $('.process_view_block .element[data-name="process_view_panel"]');
                         process_view_panel.prepend(data.html);
                         });
                         }
                         */
                    }
                })
            }

            Message.show([{'type':'confirm', 'message': Message.translate_local(message) + '?'}], false, function(_this_c){
                if($(_this_c).hasClass('yes-button')){
                    modalDialog.hide();
                    _function();
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        },




        panelMenuActionArchive : function(element){
            var _function,
                _this = this;

            _function  = function(){
                var copy_id = $('.process_view_block.sm_extension').data('copy_id');
                var _data = {
                    'run_action': 'archive',
                    'sorting_list_id': $(element).closest('.panel').data('sorting_list_id'),
                    'pci': $('.process_view_block.sm_extension').data('parent_copy_id'),
                    'pdi': $('.process_view_block.sm_extension').data('parent_data_id'),
                };

                ProcessView.panelMenuSendAjax(Global.urls.url_process_view_panel_menu_action_run + '/' + copy_id, _data, function(data){
                    if(data.status == false || data.status == 'access_error'){
                        if(typeof data.messages != 'undefined'){
                            Message.show(data.messages, false);
                        }
                    } else if(data.status == true){
                        $('.element[data-name="process_view_panel"] .panel[data-sorting_list_id="' + _data.sorting_list_id + '"]').closest('.element[data-name="panel"]').remove();

                        /*
                         if($('.element[data-name="panel"]').length == 0){
                         processView.addPanel(function(data){
                         var process_view_panel = $('.process_view_block .element[data-name="process_view_panel"]');
                         process_view_panel.prepend(data.html);
                         });
                         }
                         */
                    }
                })
            }


            Message.show([{'type':'confirm', 'message': Message.translate_local('Archive the list') + '?'}], false, function(_this_c){
                if($(_this_c).hasClass('yes-button')){
                    modalDialog.hide();
                    _function();
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        },
        destroy : function () {
            $('html').removeClass('overflowHidden');
            NiceScroll.clear($('ul.process_list > li .slimscrolldiv'));

            if (ProcessView._instance) {
                Global.removeEvents(ProcessView._instance._events);
                Events.removeHandler({ key: 'ProcessViewResize', type: Events.TYPE_EVENT_RESIZE });
            }
            ProcessView._instance = null;
        }
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.ProcessWrapper = ProcessWrapper;
    exports.ProcessView = ProcessView;
    exports.ProcessList = ProcessList;
    exports.UiSortable = UiSortable;

    // забрати!!!!
    exports.extPreloader = extPreloader;
})(window);

/******************************************
             OTHER FUNCTION
*******************************************/

var scrollHide = function() {
    $('div.nicescroll-rails').css('opacity','0');
};

//мульти сортировка всех отмеченый чекбоксами
var multiSortBgn = function() {
    $('li.ui-sortable-helper').addClass('update');
    if ($('li.ui-sortable-helper').is('.checked')) {
        $('li.ui-sortable-helper')
            .parent().find('.checked').not('.ui-sortable-helper')
            .addClass('joined update').width(253).css('position', 'absolute');  /*.height(36)*/
        var lefthelper = $('li.ui-sortable-helper').css('left');
        var tophelper =  parseFloat($('li.ui-sortable-helper').css('top'), 10)+$('li.ui-sortable-helper').height();
        var placeInd = $('li.ui-state-highlight').index();
        if (placeInd > $('li.ui-sortable-helper').index()) {
            var placeInd = placeInd - 1;
        }
        var listInd = $('li.ui-state-highlight').parent('ul').index('.to-do-list') + 1;
        $('ul').find('li.joined').each(function() {
            var jindex = $(this).index("li.joined") + 1;
            var placePer = placeInd + jindex;
            var templeft = parseFloat(lefthelper, 10);
            var temptop = tophelper+42;
            $(this).css({
                'left': templeft+'px',
                'top' : temptop+'px'
            });
            tophelper = temptop + $(this).height();
            $(this).attr('ind-num', placePer).attr('list-num', listInd);
        });
    }
};

//fixing sorting cut off bug
var relFix = function() {
    $('div.process_wrapper').css('position', 'static');
};
var relUnfix = function() {
    $('div.process_wrapper').css('position', 'relative');
};
var recFix = function() {
    $('div.process_wrapper').css('overflow', 'visible');
};
var recUnFix = function() {
    $('div.process_wrapper').css('overflow', 'hidden');
};


window.onload = function () {
    var process_view_block = $('.process_view_block');

    if (process_view_block.length) {
        //ширина окна process view под списки
        ProcessList.setWidth();
    }

    if (process_view_block.data('page_name') != 'processView' || window.location.search.indexOf('search') > 0) {
        localStorage.removeItem('currentStatePageName');
    }
    else {
        var currentStatePageName = JSON.parse(localStorage.getItem('currentStatePageName'));
        if (!currentStatePageName) return;
        process_view_block.find('.process_wrapper').scrollLeft(currentStatePageName.posScroll)
        $.each(currentStatePageName.list, function (index, value) {
            var slimscroll = $('[data-unique_index = ' + value.data_unique_index + ']').find('.slimscrolldiv');
            if (slimscroll.length && slimscroll.getNiceScroll(0)) {
                slimscroll.getNiceScroll(0).doScrollPos(0, value.position);
            }
        });
    }
}
