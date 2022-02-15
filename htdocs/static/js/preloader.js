;(function (exports) {
    var _self, _private, _public, Preloader, iPreloader;


    _self = {
        addInstancesInList: function () {
            Preloader.list_all_instances[this.getTimeStamp()] = this;
        }
    }; //link for instance

    _private ={
        clearPage: function () {
            //clear old styles
            NiceScroll.clear($('.list_view_block .crm-table-wrapper'));
        }
    }

    _public = {
        _element: null, // {} список елементов откуда удалять класы для прелоадера и какие
        add_class: null,
        mode: null,
        status_remove_all_spinner: true,
        $object: null,
        parent_element: null, //если смотреть визуально, то єто ссылка на родительский елемент в HTML

        setElement: function (element, array) {
            if (!this._element) {
                this._element = [];
            }

            this._element.push({
                element: element,
                name: array.toString()
            });

            return this;
        },
        getElement: function () {
            return this._element;
        },
        setParentElement: function (parent_element) {
            this.parent_element = parent_element || null;

            return this;
        },
        getParentElement: function () {
            return this.parent_element;
        },
        setContainer: function (container) {
            this.container = container;

            return this;
        },
        getContainer: function () {
            return this.container;
        },
        constructor: function () {
            iTimeStamp.implements.call(this);
            this.setTimeStamp(this.getNewMilliseconds());

            this._element = [];
            this.add_class = [];

            //implements inerface
            for (var key in iPreloader) {
                if ($.inArray(key, ['createInstance']) < 0) {
                    this[key] = iPreloader[key];
                }
            }
            return this;
        },
        checkOverflow: function ($element) {
            if (Global.isContentOverflow()) {
                this._css_position = this.css.FIXED;
            } else {
                $element.addClass('dynamic-relative');
                this._css_position = this.css.ABSOLUTE;
            }

            return this;
        },
        setAddClass: function ($element, name) {
            this.add_class.push({
                element: $element,
                name: name
            })

            return this;
        },
        setRemoveAllSpiner: function (bool) {
            this.status_remove_all_spinner = bool;

            return this;
        },
        //private
        runAddClass: function () {
            var _this = this;

            $.each(this.add_class, function (key, value) {
                $(value.element).addClass(value.name)
            });

            this.add_class = [];

            return this;
        },
        //розширенние
        extRun: function () {
            return this;
        },
        run: function () {
            if (this._preloader_running) {
                return this;
            }

            this._preloader_running = true;

            var name,
                label = true, // copy, remove card
                $body = $('body'),
                spinner = Global.spinner,
                $container = $('#container'),
                $listTableParent = $('[id="list-table_wrapper_all"]'),
                $crmWrapper = $('.list-view-panel .crm-table-wrapper');

            this._current_const = this._where_content_hide || null;

            this.runAddClass();

            _self.addInstancesInList.call(this);


            switch (this._where_content_hide) {
                case Preloader.TYPE_RELOAD_PAGE : {
                    this.$object = $container.addClass('hide_all_in_page');
                    spinner.get().not(':first').remove();
                    this._$place_for_spinner = $container;
                    this.setDynamicClassByArray(['hide_all_in_page']);
                    this._position_spinner = Preloader.POSITION_SPINNER_CONTENT;
                    this._css_position = Preloader.css.FIXED;

                    this.setElement(this.$object.selector, ['hide_all_in_page']);

                    break;
                }
                case Preloader.TYPE_COMMON: {
                    // this.$object = $container.addClass('hide_all_in_page');
                    // spinner.get().not(':first').remove();
                    // this._$place_for_spinner = $container;
                    // this.setDynamicClassByArray(['hide_all_in_page']);

                    this.setElement($container.selector, ['show-fix']);
                    $container.find('>.b-spinner').removeClass('hide');
                    break;
                }
                case Preloader.TYPE_BLOCK: {
                    this.$object = this._$place_for_spinner.addClass('hide_all_type_block');

                    this.setElement(this._$place_for_spinner, ['hide_all_type_block where-content-hide position-absolute']);
                    break;
                }
                case Preloader.TYPE_RELOAD_СONTENT_PAGE: { // PV
                    this.$object = $container.addClass('content_page');
                    spinner.get().not(':first').remove();
                    this._$place_for_spinner = $container;
                    this.setElement($container.selector, ['content-page']);

                    break;
                }
                case Preloader.TYPE_RELOAD_TABLE_CONTENT: { //LV
                    this.$object = this._$place_for_spinner.addClass('hide_table_content');

                    if (this.status_remove_all_spinner) {
                        $('#main-content .b-spinner').not(':first').remove();
                    } else {
                        this.$object.find(Preloader.spinner.selector).remove();
                    }

                    this.setElement(this.$object.selector, ['hide_table_content dynamic-relative'])
                        .checkOverflow(this.$object);

                    break;
                }
                case Preloader.TYPE_RELOAD_PANEL: { // PV
                    this.$object = $container.addClass('panel-constructor');
                    spinner.get().not(':first').remove();
                    this._$place_for_spinner = $container;
                    this.setElement($container.selector, ['panel-constructor']);

                    break;
                }
                case Preloader.GUIDE : {
                    this.$object = $container.addClass('hide_all_in_page');
                    spinner.get().not(':first').remove();
                    this.setDynamicClassByArray(['hide_all_in_page']);

                    break;
                }
                case Preloader.TYPE_VIEW_CALENDAR : {
                    var $list = this._$place_for_spinner; // || $('.calendar-block .fc-content');

                    this.$object = $list.addClass('here');

                    $list.find(spinner.selector).remove();
                    this.setDynamicClassByArray(['here content-overflow'])
                    this.setElement(this.$object.selector, ['content-overflow']);
                    label = false;

                    break;
                }
                case this.ADDITIONAL_PANEL : {
                    var $list = $('.channels-list'),
                        $sideBar = $('.right-stat-bar');

                    this.$object = $sideBar.addClass('here');

                    if (parseInt($list.css('height')) > 0) {
                        this.$object.addClass('content-overflow')
                    }

                    $sideBar.find(spinner.selector).remove();
                    this.setElement($sideBar.selector, ['here content-overflow']);

                    label = false;

                    break;
                }
                case this.REPORT : {
                    this.$object = $listTableParent.addClass('hide_all_in_page');

                    this._$place_for_spinner = $('.list-table_wrapper_all');
                    this._css_position = this.css.ABSOLUTE;

                    label = false;
                    this.setDynamicClassByArray(['hide_all_in_page', 'where-content-hide']);

                    break;
                }
                case Preloader.LV : {
                    this.$object = $crmWrapper.addClass('hide_all_in_page'); //init-preloader center-position

                    spinner.get().not(':first').remove();

                    this._$place_for_spinner = $crmWrapper;
                    NiceScroll.clear($('.list_view_block .crm-table-wrapper'));
                    this.setDynamicClassByArray(['hide_all_in_page', 'dynamic-relative'])

                    break;
                }
                default: {
                    // detect by DOM element

                    break;
                }
            }

            this.extRun();

            //analyse of array
            if (label && this._where_content_hide_list) {
                //this.REPORT
                if ($.inArray(this.REPORT, this._where_content_hide_list) && instanceGlobal.currentInstance.type == PAGE_IT_REPORTS && Global.isReport()) {
                    if (this._element_from_block == this.FROM_TOOLS_REMOVE) {
                        var $graph = $('.graph-area');

                        $body.addClass('hide-edit-view');
                        this.$object = $listTableParent.addClass('hide_all_in_page');
                        spinner.get().not(':first').remove();

                        this._$place_for_spinner = $('.list-table_wrapper_all');
                        this._css_position = this.css.ABSOLUTE;

                        $graph.parent().addClass('graph-set-preloader where-content-hide');
                        $graph.append(spinner.clone().first());

                        this.setDynamicClassByArray(['hide-edit-view', 'hide_all_in_page', 'graph-set-preloader', 'where-content-hide']);
                    }
                }

                if ($.inArray(this.LV, this._where_content_hide_list) >= 0 && Global.isListView()) {
                    if (this._element_from_block == this.FROM_TOOLS_REMOVE
                        || this._element_from_block == this.FROM_TOOLS_COPY) {
                        $body.addClass('hide-edit-view');
                        this.$object = $crmWrapper.addClass('hide_all_in_page'); //init-preloader center-position

                        if (Global.isContentOverflow()) {
                            this._css_position = this.css.FIXED;
                        } else {
                            this.$object.addClass('dynamic-relative');
                            this._css_position = this.css.ABSOLUTE;
                        }

                        spinner.get().not(':first').remove();

                        this._$place_for_spinner = $crmWrapper;

                        NiceScroll.clear($('.list_view_block .crm-table-wrapper'));

                        this.setDynamicClassByArray(['hide-edit-view', 'hide_all_in_page', 'dynamic-relative']);
                    }
                }
            }

            /*GLOBAL*/
            if (!this.$object) return this;

            name = 'where-content-hide';
            var clazz='';

            this.$object.addClass(name);

            switch (this._css_position) {
                case this.css.ABSOLUTE : {
                    clazz = 'position-absolute';
                    this.$object.addClass(clazz);
                    break;
                }
                default: {
                    clazz = 'position-fixed';
                    this.$object.addClass(clazz);
                    break;
                }
            }

            this.setDynamicClass(name)
                .setDynamicClass(clazz)
                .setElement(this.$object.selector, [clazz+' '+name]);

            //set preloader
            if (this._$place_for_spinner) {
                name = 'current-spinner';
                $('.current-spinner').removeClass(name);

                if (!this._$place_for_spinner.is('[id="container"]')) {
                    // base spinner in container not remove!!!
                    this._$place_for_spinner.find(spinner.selector).remove();
                    this._$place_for_spinner.append(spinner.clone().first().attr('style', '').addClass(name).removeClass('default'));
                } else {
                    this._$place_for_spinner.find('>'+spinner.selector).addClass(name).attr('style', '').removeClass('hide');
                }

                this.setDynamicClass(name);
                this._preloader_running = true;
            }

            switch (this._position_spinner) {
                case Preloader.POSITION_SPINNER_CONTENT: {
                    $('.current-spinner').css({
                        left: $('.wrapper').width() / 2
                    });
                    break;
                }
                case Preloader.POSITION_SPINNER_WINDOW: {

                    break;
                }
                default:
                    break
            }

            if ($.inArray(this._where_content_hide,[Preloader.ADDITIONAL_PANEL])< 0) {
                _self.clearPage();
            }

            return this;
        },
        //вертає константу режима работы прелоадера
        getMode: function () {
            return this.mode;
        },
        setPlaceForSpinner: function ($place) {
            this._$place_for_spinner = $place;
            return this;
        },
        setCssPositionSpinner: function (position) {
            this._css_position = position;

            return this;
        },
        setWhereContentHide: function (position) {
            if (position) {
                if ($.isArray(position)) {
                    this._where_content_hide_list = position;
                } else {
                    this._where_content_hide = position;
                    this.mode = position;
                }
            }
            return this;
        },
        hideFull: function () {
            var list = this._element || $([]);

            this.setRunning(false);

            $.each(list, function (i, val) {
                try{
                    $(val.element).removeClass(val.name);
                } catch (e) {}
            });

            this._element = null;

            return this;
        },
        hide: function (_element) {
            var $container,
                array = this.preloaderClasses || [];

            this.setRunning(false);

            if (this._element && this._element.length) {
                this.hideFull();
            } else {
                _element = _element || $('html');

                if (!this._instance) { //instance
                    array = this._dynamic_class || [];
                }

                $container = $('#container');
                if ($container.is('.set-preloader')) {
                    $('#container>.b-spinner').addClass('hide');
                }

                $.each(array, function (i, val) {
                    _element.find('.' + val).removeClass(val);
                });

                // КОСТИЛЬ ТИМЧАСОВИЙ
                if ($('.filter').is('.hide_all_type_block')) {
                    $('.filter').removeClass('hide_all_type_block where-content-hide position-absolute');
                }
            }

            return this;
        },
        setRunning: function (status) {
            this._preloader_running = status;
            return this;
        },
        setDynamicClassByArray: function (array) {
            if (!this._dynamic_class) {
                this._dynamic_class = [];
            }

            for (var key in array) {
                this._dynamic_class.push(array[key]);
            }

            return this;
        },
        setDynamicClass: function (string) {
            if (!this._dynamic_class) {
                this._dynamic_class = [];
            }

            this._dynamic_class.push(string);

            return this;
        },
    };

    iPreloader = {
        preloader: null,

        createInstance: function () {
            var Obj = function () {
                for (var key in iPreloader) {
                    if ($.inArray(key,['createInstance', 'implementsIPreloader'])< 0) {
                        this[key] = iPreloader[key];
                    }
                }
            }

            return new Obj();
        },
        implements: function () {
            for (var key in iPreloader) {
                if ($.inArray(key,['createInstance', 'implementsIPreloader'])< 0) {
                    this[key] = iPreloader[key];
                }
            }

            return this;
        },
        setPreloader: function (preloader) {
            this.preloader = preloader;

            return this;
        },
        getPreloader: function () {
          return this.preloader;
        },
        setShowPreloaderHandler: function (handler) {
            this.preloader.showPreloader = handler;

            return this;
        },
        showPreloader: function (is_running) {
            this.preloader && this.preloader.showPreloader && this.preloader.showPreloader(is_running);

            return this;
        },
        isPreloader: function () {
            return this.preloader ? true : false;
        },
        hidePreloader: function () {
            return this;
        }
    }

    Preloader = {
        /*MODE*/
        TYPE_RELOAD_PAGE: 1, // hidden all page except top menu
        TYPE_RELOAD_СONTENT_PAGE: 12, // hidden all page except top menu
        TYPE_COMMON: 13,
        TYPE_RELOAD_TABLE_CONTENT: 14, // hidden all page except top menu
        TYPE_VIEW_CALENDAR : 9,
        TYPE_BLOCK : 15,
        TYPE_RELOAD_PANEL : 16,

        ADDITIONAL_PANEL: 2,
        LV: 3,
        REPORT: 4,
        PV: 5,
        FROM_TOOLS_REMOVE: 6,
        FROM_TOOLS_COPY: 7,
        GUIDE: 8,

        POSITION_SPINNER_WINDOW: 10,
        POSITION_SPINNER_CONTENT: 11,

        css: {
            ABSOLUTE: 1,
            FIXED: 2
        },
        list_all_instances: {},
        _item_li_for_loading: '<li class="small-preloading"><div class="b-spinner"><div class="loader"></div></div></li>',
        _current_const: null, // Для визначення в режимі екземпляра щоб не видаляти інше.
        _instance: null,
        _where_content_hide: null,
        _where_content_hide_list: null,
        _$place_for_spinner: null,
        _element_from_block: null,
        _preloader_running: null, //bool - preloader working
        _priority_disable: true, // true - high, false - low
        _position_spinner: null, //POSITION_SPINNER_WINDOW || POSITION_SPINNER_CONTENT

        /*-MODAL-------------*/
        _modal_status: null,
        _modal_sub_status: null,
        /*=MODAL=============*/

        _css_position: null,

        block: [],
        preloaderClasses: [
            'where-content-hide',
            'here',
            'hide-edit-view',
            'dynamic-relative',
            'position-absolute',
            'position-fixed',
            'hide_all_in_page',
            'set-preloader',
            'show-preloader',
            'init-preloader',
            'center-position',
            'reports-content',
            'first-task',
            'roles-menu',
            'block-reload',
            'reports-hide-graph',
            'reports-filter-in',
            'with-table',
            'reload-page',
            'common-spinner',
            'copy-edit-view',
            'overflowXh',
            'show-fix',
            'hide-edit-view',
            'lv-pv-inner-hide-content'],

        _dynamic_class: null,

        createInstance: function () {
            var Obj = function () {
                for (var key in _public) {
                    this[key] = _public[key];
                }
                for (var key in Preloader) {
                    this[key] = Preloader[key];
                }
            }

            return Preloader._instance = new Obj().constructor();
        },
        getInstance: function (status) { // status = true if need create instance
            if (status && !Preloader._instance) {
                this.createInstance();
            }
            return Preloader._instance;
        },

        /*-MODAL-------------*/
        setModal: function (bool) {
            this._modal_status = bool

            return this;
        },
        setSpinnerPosition: function (position) {
            this._position_spinner = position;

            return this;
        },
        setPriorityDisable: function (bool) {
            this._priority_disable = bool;

            return this;
        },
        setModalSub: function (bool) {
            this._modal_sub_status = bool

            return this;
        },
        setFromBlock: function (_const) {
            if (_const) {
                this._element_from_block = _const
            }
            return this;
        },
        spinner : {
            selector: '.b-spinner',
            get: function () {
                return $(this.selector);
            },
            clone: function () {
                return this.get().clone().removeClass('hide').attr('style', '');
            },
            remove : function ($element) {
                $element && $element.length ? $element.find(this.selector).remove() : '';
            },
            getElement: function () {
                return '<div class="b-spinner"><div class="loader"></div></div>';
            }
        },
        modalShow: function () {
            var $body = $('body');

            if ($('.modal .modal-backdrop.in').length) {
                $body.addClass('open-two-level-ev');
            }
            ;
            $body.append('<div class="fake-backdrop"><div class="fake-modal"></div></div>');
            $('.fake-modal').append(this.getSpinner());
            return this;
        },
        modalHide: function () {
            var $modal = $('.modal-dialog').last(),
                $fakeBackDrop = $('.fake-backdrop');

            $('body').removeClass('open-two-level-ev');
            $modal.css('opacity', '0');
            if ($fakeBackDrop.css('display') !== 'block') {
                $modal.css('opacity', '1');
            } else {
                $fakeBackDrop.css('background', 'initial').find('.fake-modal').animate({
                    height: $modal.css('height'),
                    width: $modal.css('width')
                }, 'fast', function () {
                    $modal.css('opacity', '1');
                    $(this).closest('.fake-backdrop').animate({
                        opacity: 0
                    }, 'fast', function () {
                        $(this).remove();
                        $modal.css('opacity', '1');
                    });
                });
            }
            $('span[todo-select="false"]').closest('li').attr('todo-element', 'true');
            if ($('div.constructor .contacts-block').length) {
            } else {
                $('.participant.example').css('margin-bottom', '-30px');
            }
            return this;
        },
        modalSub: function () {
            $('.fake-modal').addClass('sub');
            var $modal = $('.modal-dialog').last(),
                name = $modal.find('.panel-heading .client-name .editable-field').text(),
                link = $modal.find('.panel-heading .navigation_module_link').text();
            $modal.closest('.modal').hide();
            $('.fake-modal').append('<header class="panel-heading previous-modal">\
                                    <span class="client-name">' + name + '</span>\
                                    <span class="from-label">\
                                    <span id="from">' + Message.translate_local('from') + ' </span>\
                                        <a href="javascript:void(0)" data-dismiss="modal" class="navigation_module_link">' + link + '</a>\
                                    </span>\
                                </header>');
            return this;
        },
        modalAnSub: function () {
            var $modal = $('.modal-dialog').last();
            $modal.closest('.modal').show();
            return this;
        },

        modalRun: function () {
            if (this._modal_status) {
                this.modalShow();
            }

            if (this._modal_sub_status) {
                this.modalSub();
            }

            return this;
        },
        /*=MODAL=============*/

        getSpinner: function () {
            return $('#container > .b-spinner').clone().removeClass('hide');
        },
        afterPreloader: function () {
            setTimeout(function () {
                $('#modal_dialog_container').removeClass('preloader');
                ProcessView.initElements();
                textAreaResize();
                AjaxContainers.cash.snapshot();
            }, 1000);
        },
        drawIconForMenu: function () {
            var $content, $clone,
                $list = $('.notify-row .nav > li');

            $content = $list.filter('.icon-spinner').length ? $list.filter('.icon-spinner').removeClass('hide') : $list.last();

            if (!$content.is('.icon-spinner')) {
                $clone = $content.clone();
                $clone.removeClass('dropdown').html('<a></a>').removeAttr('id').addClass('icon-spinner');
                $clone.find('a').append(this.getSpinner());
                $content.after($clone);
            }

            this.block.push($('.icon-spinner'));
        },
        hideAll: function () {
            $.each(Object.keys(Preloader.list_all_instances), function (key, value) {
                delete Preloader.list_all_instances[value].hide();
            });

            return this;
        },
        isRunning: function () {
            return this._preloader_running;
        },
        // //TODO: костиль.
        // hide: function (_element) {
        //     var $container,
        //         array = this.preloaderClasses || [];
        //
        //     _element = _element || $('html');
        //     this._preloader_running = false;
        //
        //     if (!this._instance) { //instance
        //         array = this._dynamic_class || [];
        //     }
        //
        //     $container = $('#container');
        //     if ($container.is('.set-preloader')) {
        //         $('#container>.b-spinner').addClass('hide');
        //         //КОСТИЛЬ
        //         $('#container').removeClass('hide-nice-scroll');
        //     }
        //
        //     $.each(array, function (i, val) {
        //         _element.find('.' + val).removeClass(val);
        //     });
        //
        //     // КОСТИЛЬ ТИМЧАСОВИЙ
        //     if ($('.filter').is('.hide_all_type_block')) {
        //         $('.filter').removeClass('hide_all_type_block where-content-hide position-absolute');
        //     }
        //
        //     return this;
        // },
        destroy: function () {
            if (!this._priority_disable) {
                return this;
            }

            //call as instance
            if (!this._instance) {
                var $element;
                if (this._current_const == this.ADDITIONAL_PANEL) {
                    $element = $('.right-sidebar');
                    this._preloader_running = null;
                }
                this.hide($element);
            }
            else {
                this._instance.hide();
                Preloader._instance = null;
            }

            return null;
        }
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.Preloader = Preloader;
    exports.iPreloader = iPreloader;
})(window);
