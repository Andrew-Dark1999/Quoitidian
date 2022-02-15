/*------------------------------------------------------*/
/*------------------------------------------------------*/
/*------------------------------------------------------*/
/*--   general object
/*------------------------------------------------------*/

var $window = $(window);

Base = {
    copyObject : function (paramObject) {
        var object;

        if (Global.browser.isMSIE()) {
            object = Object.create(null);
            for(var key in paramObject) {
                object[key] = paramObject[key];
            }
        } else {
            object = Object.assign({}, paramObject);
        }

        return object;
    },
    copyObjectTo : function (from, to) {
        var object = Object.assign(to, from);
        return object;
    },
    isListView : function() {
        return $('.list_view_block').length ? true : false;
    },
    isLVProcess : function () {
      return $('[data-copy_id="9"]').length ? true : false;
    },
    isObject : function (object) {
        return (typeof object === 'object') ? true : false;
    },
    addEvents : function (events, object) {
        $.each(events, function (i, data) {
            $(document).off(data.event, data.name).on(data.event, data.name, object ? object : {}, data.func);
        });
    },
    getWorkArea: function () {
      return $('.wrapper');
    },
    removeEvents : function (events) {
        $.each(events, function (i, data) {
            $(document).off(data.event, data.name);
        });
    },
    parseUrl : function (url) {
        var arrParam = url.split('?');


        if (!arrParam[1]) {
            return null;
        }

        return JSON.parse('{"' + decodeURI(arrParam[1]).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}')
    }
}
//closing configuration dropdown menu. Robert
$(document).mouseup(function (e){
    var modal = $("ul.element");
    if (!modal.is(e.target) && modal.has(e.target).length === 0){
        $(".element.open").removeClass("open");
    }

});

var arrActionKeys = [];

var MainMenu = {
    _timerResizeMenu: null,
    _period: 2000,

    /* Сжимаем навигацию, переносим пункты в выпажающее меню "Ещё" если оно не помещается  */
    getWidth : function() {
        return ($('.navbar-header').width() - $('.brand').width() - $('.notify-row').width() - $('.hr-top-nav .top-menu').width() - 80);
    },
    stopResize : function () {
        clearTimeout(this._timerResizeMenu);

        return this;
    },
    resize : function() {
        var menuCountRecive = null,
            menuCounter = $('.horizontal-menu > .nav > li > a.navigation_module_link').length;

        LocalStorage
            .createInstance()
            .setAsync(true)
            .setKey('menu_count')
            .getValueFromServer(1, function(data) {
                var instance = LocalStorage
                    .createInstance()
                    .setKey('menu_count')

                if (data) {
                    menuCountRecive = +data.count;
                    if (menuCounter !== menuCountRecive) {
                        instance.setValueToServer(1, {count: menuCounter});
                    }
                } else {
                    instance.setValueToServer(1, {count: menuCounter});
                }
            });
    },
    functionResizeMenu : function() {
        this._timerResizeMenu = setTimeout(this.resize, this._period);

        return this;
    },

    clickElse: function (bool) {
        var $header = $('.navbar-header');

        if (bool) {
            $header.removeClass('overflowHidden');
        } else {
            $header.addClass('overflowHidden');
        }
    },
    run : function() {
        var menu      = $('.horizontal-menu > ul'),
            menulinks = $('.horizontal-menu > ul > li'),
            more      = $('#more-links'),
            moremenu  = $('#more-links > ul'),
            $moremenuLi  = $('#more-links > ul>li'),
            offset = 130,
            _this = this;

        more.css('display','inline-block');
        menulinks.each(function(e) {
            if(_this.getWidth() < $('.horizontal-menu').width() || $(window).width() < 980 ) {
                more.css('display','inline-block');
                menulinks.each(function(e) {
                    if(_this.getWidth() < $('.horizontal-menu').width()) {
                        moremenu.prepend($('.horizontal-menu > ul > li:nth-last-child(2)'));
                    }
                });
            } else if (_this.getWidth() > $('.horizontal-menu').width() + offset) {
                $moremenuLi.each(function(e) {
                    if (_this.getWidth() > $('.horizontal-menu').width() + offset) {
                        $('.horizontal-menu > ul > li:last-child').before($('#more-links > ul > li:first-child'));
                    } else {
                        return false;
                    }
                });
            }
        });
        if ($('#more-links li').length == 0) {
            more.hide();
        }

        $('div.navbar-header').css('overflow', 'visible');

        Global
            .stopFunctionResizeMenu()
            .functionResizeMenu();
    },
    _updateState: function () {
        var $header = $('.navbar-header');

        if (!$header.is('.overflowHidden')) {
            $header.find('#more-links, [data-type="main_top_profile_menu_user"], [data-type="header_notice"]').removeClass('open');
            $header.addClass('overflowHidden');
        }
    },
    init: function () {
        var _this = this;

        Events
            .createInstance()
            .setType(Events.TYPE_EVENT_BODY_CLICK)
            .setKey('MainMenuBodyClick')
            .setHandler(function (e) {
                var $header = $('.navbar-header');
                $header.find('#more-links.open > ul.dropdown-menu').getNiceScroll().remove();

                _this._updateState(e);
            })
            .run();

        Events
            .createInstance()
            .setType(Events.TYPE_EVENT_RESIZE)
            .setKey('MainMenuResize')
            .setHandler(function (e) {
                _this._updateState(e);
                _this.run();

                if ($(window).width() >= 980) {
                    $('.nav.notify-row').show();
                }
            })
            .run();

        iPreloader.implements.call(this);
        this.setPreloader(Preloader.createInstance())
            .setShowPreloaderHandler(function () {
                this.setRunning(false)
                    .setAddClass('html', 'overflowHidden')
                    //.setAddClass('#container', 'hide-nice-scroll')
                    .setElement($('html').selector, ['overflowHidden'])
                    .setWhereContentHide(Preloader.TYPE_RELOAD_PAGE)
                    .setPlaceForSpinner($('#container'))
                    .run();
            });

        return this;
    }
}

var Constant = {
    VIEW_TYPE_EDIT_VIEW: 'editView',
    VIEW_TYPE_LIST_VIEW: 'listView',

    PAGE_IT_CONSTRUCTOR    : 'constructor',
    PAGE_IT_REPORTS        : 'reports',
    PAGE_IT_PROCESS        : 'process',
    PAGE_IT_COMMUNICATIONS : 'communication',
    PAGE_IT_CALLS          : 'calls',

    DEVICE_DESKTOP: 'desktop',
    DEVICE_IPHONE: 'iPhone',
    DEVICE_IPAD: 'iPad',

    EDITOR_EMOJI    : 'emoji',
    EDITOR_TINY_MCE : 'tiny_mce'
};

;(function (exports) {
    var _private, _public, Global, ContentReload, iGlobal, iStore, iTimeStamp, iTemplate, iContentReload, iCommon, ViewType, iModule, iAction, iQueue, iLifeCycle,
        _self = {}; //link for instance

    _private = {
        instance: null,
        setInstance: function (instance) {
            this.instance = instance;

            return this;
        },
        events : function () {
            this._events = [
                { parent: document, selector: '.element[data-type="finished_object"]', event: 'click', func: this.onSwitchData},
                { parent: document, selector: '#header_notification_bar>a, #header_task_bar>a', event: 'click', func: this.onClickNotificationBlock},
                { parent: document, selector: '.notify-row .dropdown', event: 'show.bs.dropdown', func: this.onShowBsDropDown},
                { parent: document, selector: '.element[data-type="drop_down"] .element[data-type="actions"] .remove', event: 'click', func: this.onRemoveDataSDM},
                { parent: document, selector: '.element[data-type="drop_down"] .element[data-type="actions"] .add', event: 'click', func: this.onAddCardSDM},
                { parent: document, selector: '.element[data-type="drop_down"] .element[data-type="actions"] .edit', event: 'click', func: this.onEditCardSDM},
                { parent: document, selector: '.constructor, .edit-view[data-copy_id="8"]', event: 'click', func: this.onClickOutSideSubMenu},
                { parent: document, selector: '.ajax_content_reload', event: 'click', func: this.onClickAjaxRequest},
                { parent: document, selector: '.navigation_module_template_link', event: 'click', func: this.onClickTemplateLink},
                { parent: document, selector: '.element[data-page="plugins"] .element[data-type="block"] .element[data-type="service_name"]', event: 'change', func: this.onChangeServiceName},
                { parent: document, selector: '.element[data-page="plugins"] .element[data-type="save"]', event: 'click', func: this.onClickPluginsSave},
                { parent: document, selector: '.element[data-page="plugins"] .element[data-type="cancel"]', event: 'click', func: this.onClickPluginsCancel},
                { parent: document, selector: '.navigation_module_link_child', event: 'click', func: this.onClickNavigationModuleChild},
                { parent: document, selector: '.navigation_module_link_child_from_submodule', event: 'click', func: this.onSubModuleLinkNavigation},
                { parent: document, selector: '.navigation_page_link', event: 'click', func: this.onClickPageLink},
                { parent: document, selector: '.navigation_module_link', event: 'click', func: this.onModuleLink},
                { parent: document, selector: '.element[data-type="save_form"]', event: 'click', func: this.onSaveFormParams},
                { parent: document, selector: '.element[data-type="cancel_form"]', event: 'click', func: this.onCancelFormParams},
                { parent: document, selector: '.navigation_message_notice_ro', event: 'click', func: this.onMessageNotice},
                { parent: document, selector: 'input.money_type', event: 'focus', func: this.onMoneyTypeFocus},
                { parent: document, selector: 'input.money_type', event: 'blur', func: this.onMoneyTypeBlur},
                { parent: document, selector: 'input.money_type', event: 'keydown', func: this.onTypingMoneyTypeKeyDown},
                { parent: document, selector: 'input.money_type', event: 'keyup', func: this.onTypingMoneyTypeKeyUp},
                { parent: document, selector: '.list_view_btn-delete, .edit_view_btn-delete', event: 'click', func: this.onRemoveData},
                { parent: document, selector: '#more-links>a.dropdown, #top-menu>li>a.dropdown-toggle, .top-menu>li>a.dropdown-toggle', event: 'click', func: this.onClickMenuElse},
                { parent: document, selector: '.fake-backdrop', event: 'click', func: this.onClickFakeBackDrop},
                { parent: document, selector: '.edit_view_show', event: 'click', func: _self.onEditViewOpen },
                { parent: document, selector: '.instruments .btn', event: 'click', func: _self.onOpenInstruments },
                { parent: document, selector: '.submodule-link .submodule-table .sm_extension_data', event: 'click', func: this.onClickExtenstionData },
                { parent: document, selector: '.editable-block.element > span, .editable-block.element .editable-field', event: 'click', func: this.onClickEditableField},
                //{ parent: document, selector: '', event: '', func: this.},
            ]

            Global.addEvents(this._events, {
                instance: this.instance
            });
        },
        onClickEditableField: function(e) {
            var $this = $(this);

            e.stopPropagation();
            $('.edit-dropdown.open').each(function () {
                $(this).removeClass('open');
            });
            $('.editable-block').find('.editable-field').css('opacity', '1');
            $this.css('opacity', '0');
            $this.closest('.editable-block').find('.edit-dropdown').addClass('open');
            $this.closest('.editable-block').find('.element[type="text"]').attr('maxlength','40').attr('value', $(this).closest('.editable-block').find('.editable-field').text()).select();
        },

        onClickExtenstionData :function () {
            var submoduleLink = $(this).closest('.submodule-link');

            if(submoduleLink.find('.element_relate').data('module_parent')) {
                TodoList.rebuild(this);
            }
            if ($(this).closest('.sm_extension_generate').length>0) {
                $(this).closest('.submodule-link').addClass('opened');
                setTimeout(function(){
                    $('.opened').addClass('open').removeClass('opened')
                }, 300);
                $(this).closest('.submodule-link').find('.btn.element_relate').html('');
                var $lenght = $(this).closest('.submodule-table').find('input:checked').length;
                $(this).closest('.submodule-table').find('input:checked').each(function(i,val){
                    $name = $(this).closest('label').find('.name').clone(true);
                    $name.appendTo($(this).closest('.submodule-link').find('.btn.element_relate'));
                    if(i+1!=$lenght){
                        $(this).closest('.submodule-link').find('.btn.element_relate').append(', ');
                    }
                });
            }
        },
        onOpenInstruments: function(e) {
            var tools = Tools.createInstance(),
                json = Url.getParams(location.href),
                currentInstance = Global.getInstance().getCurrentInstance();

            if (json && json.id && currentInstance._type == 'reports') {
                currentInstance.reDefinitionTools(tools);
            }
        },
        onEditViewOpen : function (e) {
            var currentInstance, instanceEV,
                $this = $(this),
                instanceGlobal =  e.data.instance;

            currentInstance = ViewType.getCurrentInstance() || Global.getInstance().getCurrentInstance();

            instanceEV = EditView.createInstance(modalDialog.createInstance()).setParent(currentInstance);

            if (($this.closest('.panel-heading').length && $this.closest('.process_view_block').length) // What we is on PV
                || $this.closest('.sm_extension_data.editing').length) {
                return;
            }

            if ($this.closest('[data-type="submodule"]').length) {
                var preloader = Preloader.createInstance();

                preloader.setModal(true).setModalSub(true);
                instanceEV.setPreloader(preloader);
            }

            instanceGlobal.editCard(this, instanceEV);

            // instance
            //     .setParent(currentInstance)
            //     .editCard(this, null, function(data){
            //         instance.runAfterEditCardLV(data);
            //         Global
            //             .createLinkByEV($('.edit-view:last'))
            //             .fixSubstrateInModal();
            //     });
        },
        onClickFakeBackDrop: function (e) {
            EditView. clearQueue();
            $(this).remove();

            return this;
        },
        onSwitchData: function (e) {
            Global.finishedObject.switchData(this);
        },
        onRemoveData: function (e) {

        },
        onClickMenuElse: function (e) {
            var $this = $(this),
                value = $this.closest('li').is('.open');

            MainMenu.clickElse(value);
        },
        onMoneyTypeBlur: function (e) {
            var text,
                $this = $(this);

            if (EditView.getInstance()) {
                text = e.data.instance.getModel().money.groupString($this.val());
                if (text) {
                    $this.val(text);
                }
            }
            return false;
        },
        onMoneyTypeFocus: function (e) {
            var text,
                $this = $(this);

            text = e.data.instance.getModel().money.concat($this.val());
            if (text) {
                $this.val(text);
            }
            return false;
        },
        onTypingMoneyTypeKeyDown: function (e) {
            var model = Global.getModel();

            if (e.ctrlKey) {
                if (e.keyCode == '67') { // Ctrl + C
                    Global.copyToClipboard(this);
                }
                return true;
            } else {
                return model.money.typingKeyDown(e);
            }
        },
        onTypingMoneyTypeKeyUp: function (e) {
            var model = Global.getModel();

            if (e.ctrlKey) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            } else {
                return model.money.typingKeyUp(e);
            }
        },
        onSaveFormParams : function(e){
            Global.Forms.saveAjax(this);
        },
        onCancelFormParams : function(e){
            Global.Forms.cancel(this);
        },
        onMessageNotice : function(){
            var _this = this;
            var vars = {
                'action_key' : null,
                'action_run' : 'loadModule',
                'action_after' : ['actionHideLeftMenu', 'actionSwitchMenu', 'actionShowEditView'],
                'task' : true,
                'check_expediency_switch' : true,
                'selector_content_box' : '#content_container',
                'module' : {
                    'copy_id' : $(_this).data('copy_id'),
                    'data_id' : $(_this).data('card_id'),
                    'params' : {
                        'this_template' : 0
                    }
                }
            }

            //clear param of search
            var param = Url.getParams(location.href);
            if (param && param.search) {
                Url.replace('search='+param.search,'');
            }

            content_reload = instanceGlobal.contentReload
                .clear()
                .prepareVariablesToGeneralContent(true);

            instanceGlobal.preloaderShow($(_this));
            content_reload
                .setVars(vars)
                .run()
        },
        onClickNavigationModuleChild : function () {
            Global.showChildListEntities(this);
        },
        onSubModuleLinkNavigation: function(){
            EditView.subModules.linkNavigation(this);
        },
        onClickPageLink: function(){
            instanceGlobal.contentReload
                .setObject(this)
                .setLinkType(CR_LINK_TYPE_LOAD_PAGE)
                .run();
        },
        onModuleLink: function(e){
            e.preventDefault();

            var _this = this,
                action_key = $(_this).data('action_key'),
                countOpen = Object.keys(modalDialog.getOpens()).length;

            modalDialog.hideAll(function(){
                var time, json,
                    preloader = ViewType.getPreloader(),
                    instanceContent = ContentReload.createInstance().clear(),
                    vars = instanceGlobal.contentReload.getContentVars(action_key);

                json = Url.parseFull();
                iPreloader.implements.call(instanceContent);

                if (vars.module && preloader) {
                    var globalInstance = Global.getInstance();

                    if (json.id != vars.module.copy_id || countOpen>1) {
                        preloader = MainMenu.getPreloader();
                    }

                    globalInstance.setPreloader(preloader); //подовжуємо роботу прелоадера через інтерфейс
                    instanceContent.setPreloader(preloader)
                };

                time = setTimeout(function () {
                    clearTimeout(time);

                    Global.getInstance().setContentReloadInstance(instanceContent);

                    instanceContent
                        .reDefinition()
                        .showPreloader()
                        .setObject(_this)
                        .setActionKey(action_key)
                        .setVars(vars)
                        .run();
                }, 100);
            })
        },        
        onChangeServiceName : function () {
            Global.Plugins.BlockParams.change(this);
        },
        onClickPluginsSave : function(){
            Global.Plugins.BlockParams.save(this);
        },
        onClickPluginsCancel : function () {
            Global.Plugins.BlockParams.cancel(this);
        },
        onClickTemplateLink: function(){
            var this_template = $(this).data('back') ? '0' : '1';
            $('.list_view_block, .process_view_block').data('this_template', this_template);

            instanceGlobal.preloaderShow($(this)).contentReload
                .setObject(this)
                .prepareVariablesAuto()
                .setCallBackSuccessComplete(function(data) {
                    var json = Url.parseFull();

                    switch (json.controller) {
                        case 'listView': {
                            ListView.getInstance().createModel();
                            break;
                        }
                        case ProcessView._type: {

                            break;
                        }
                        case CalendarView._type: {

                            break;
                        }
                        default: {
                        }
                    }
                })
                .run();
        },
        onClickAjaxRequest : function(e){
            e.preventDefault();

            var $this = $(this),
                instance = e.data.instance,
                key = parseInt($this.attr('data-action_key'));

            if ($this.closest('.header').length) {
                Search.destroy();
            }

            InLineSet($('tr.editing td:first'), true, 'cancel');
            Global.scrollTop($this);

            AjaxContainers.addKey(key);
            arrActionKeys.push(key);

            if ($(this).closest('.wievs_tuggle').length) {
                Events.runHandler(Events.TYPE_DESTROY);
            }

            var instanceContent = ContentReload.createInstance();
            e.data.instance.setContentReloadInstance(instanceContent);

            iPreloader.implements.call(instanceContent)

            if ($this.closest('header').length || $this.closest('[data-type="footer_buttons"]').length) {
                Search.implements(iCommon);
                instanceContent.setPreloader(MainMenu.getPreloader());
            }
            if ($this.closest('.wievs_tuggle').length) {
                instanceContent
                    .setPreloader(Preloader.createInstance())
                    .setShowPreloaderHandler(function () {
                        this.setRunning(false)
                            .setElement($('html').selector, ['overflowHidden'])
                            .setSpinnerPosition(Preloader.POSITION_SPINNER_CONTENT)
                            .setWhereContentHide(Preloader.TYPE_RELOAD_СONTENT_PAGE)
                            .setPlaceForSpinner($('#container'))
                            .run();
                    })
            }

            if ($this.closest('[data-type="left_menu"]').length) {
                instanceContent
                    .setPreloader(Preloader.createInstance())
                    .setShowPreloaderHandler(function () {
                        this.setRunning(false)
                            .setWhereContentHide(Preloader.TYPE_RELOAD_PANEL)
                            .setPlaceForSpinner($('#container'))
                            .run();
                    })
            }


            //Черга відкриття модулів
            ContentReload
                .setQueueStatus(true)
                .setDataToQueue(key);

            instanceContent
                .reDefinition()
                .showPreloader()
                .abort()
                .setObject(this)
                .prepareVariablesAuto()
                .run();
        },
        onClickNotificationBlock : function () {
            var delta,
                offset = 0,
                $this = $(this).closest('li'),
                $notification = $this.find('.dropdown-menu');

            if (!$notification.length) {
                return;
            }

            delta = $('body').width() - ($notification.offset().left + $notification.width());
            $this.removeClass('open');
            offset = ($notification.width()/2 - 57) + offset;

            if (delta <= 0) {
                offset = Math.abs(delta) + 5;
                delta = (Math.abs(parseInt($notification.css('left'))) + offset);
                $notification.css('left', - delta + 'px');
            }

            $this.find('.m-pointer').attr('style', '').offset({left: $this.find('>a').offset().left - 8 });
            $this.addClass('open');
        },
        onShowBsDropDown : function () {
            var $this = $(this);

            delete window.backForward;

            if ($this.offset().left + $('.notification').width() > $(window).width()) {
                $this.find('.notification').addClass('skew');
            }
        },
        onAddCardSDM : function () {
            EditView.cardCreateNew(this, function(data){

            });
            /*
            EditView.addCard(this, null, function(data){
                EditView.runAfterAddCardLV(data);
                instanceCommunications && instanceCommunications.initTextArea();
            });
            */

        },
        onEditCardSDM : function () {
            EditView.subModules.cardEditSDM(this);
        },
        onRemoveDataSDM : function () {
            var $button,
                $this = $(this),
                $operation = $this.addClass('hide').closest('.icon-operation');

            $button = $this.closest('[data-type="drop_down"]').find('button');
            $button
                .html('')
                .data('id', '');

            $operation.find('.add').removeClass('hide');
            $operation.find('.edit').addClass('hide');

            var base_id = null;
            var relate = $(this).closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant');
            if(relate.data('reloader') == 'parent'){
                base_id = relate.data('id');
            }

            EditView.relates.reloadEditView(this, base_id);
            EditView.relates.reloadInLine(this, null, base_id, true);
        },
        onClickOutSideSubMenu : function(event) {
            if ($(event.target).closest('.sub-menu').length) return;
            $('.sub-menu').addClass('hide').closest('.settings').css('z-index', '20');
            event.stopPropagation();
        }
    };

    _public = {
        //пишем методи інтерфейса, щоб не було переопреділення.
        interface: null,
        sub_instance: null,
        current_instance: null,

        constructor: function () {
            _self
                .setInstance(this)
                .events();
            _self.dateTimePopUp = new dateTimePopUp();

            iGlobal.implements.call(this);
            iBackForwardHistory.implements.call(this);
            iPreloader.implements.call(this);
            iContentReload.implements.call(this);

            Draft.runCron();

            return this;
        },
        ctrlF5: function () {
            var preloader,
                offset = 0,
                $spinner = $('#container>.b-spinner');

            $spinner.css({
                left: ($('#main-content').width() -  offset) / 2
            });

            preloader = Preloader.createInstance();

            this.setPreloader(preloader);

            //Муляж прелоадера - але він має бути
            preloader
                .setWhereContentHide(Preloader.TYPE_COMMON)
                .setAddClass('#container', 'hide-nice-scroll')
                .setElement('#container', 'hide-nice-scroll set-preloader show-fix reload-page hide-inner-preloaders')
                .setPlaceForSpinner($('#container'))
                .run();

            //Старт прелоадера
            $('body').removeClass('delay-pre');

            return this;
        },
        getModel : function () {
            return $('[data-model-global]').data()['modelGlobal'];
        },        
        setCurrentInstance: function (instance) {
            this.current_instance = instance;
            return this;
        },
        getCurrentInstance: function () {
            return instanceGlobal.currentInstance || this.current_instance;
        },
        setSubInstance: function (instance) {
            this.sub_instance = instance;
            instanceGlobal.subInstance = instance;

            return this;
        },
        getSubInstance: function () {
            return this.sub_instance;
        },
        editCard: function (_this, instanceEditView) {
            var currentInstance = this.getCurrentInstance(),
                subInstance = ViewType.getCurrentInstance();

            if (!instanceEditView && subInstance) {
                instanceEditView = EditView.createInstance().setParent(subInstance);
                subInstance.getPreloader().setRunning(false);
            }

            //line
            EditView
                .setQueueStatus(true)
                .setDataToQueue({});

            switch (currentInstance._type) {
                case Constant.PAGE_IT_REPORTS: {
                    currentInstance.editCard(_this, instanceEditView);
                    break;
                }
                default: {
                    instanceEditView
                        .setParentElement($(_this) || null)
                        .editCard(_this, null, function(data){
                            this.runAfterEditCardLV(data);
                        });
                }
            }

            return this;
        },
        addCard: function (_this, data, instanceEditView) {
            var currentInstance = this.getCurrentInstance(),
                subInstance = ViewType.getCurrentInstance();

            if (!instanceEditView) {
                instanceEditView = EditView.createInstance().setParent(subInstance);
            }

            switch (currentInstance._type) {
                case Constant.PAGE_IT_REPORTS: {
                    currentInstance.addCard(_this);
                    break;
                }
                default : {
                    instanceEditView.addCard(_this, data, function(data){
                        this.runAfterAddCardLV(data);
                    });
                    break;
                }
            }
            return this;
        }
    };

    var publicAction = {
        type_action: null,

        setTypeAction: function (type_action) {
            this.type_action = type_action;

            return this;
        },
        getTypeAction: function () {
            return this.type_action;
        }
    }
    iAction = {
        TYPE_SEARCH: 1,
        TYPE_FILTER: 2,
        TYPE_FINISH: 3,

        implements: function () {
            for (var key in publicAction) {
                this[key] = publicAction[key];
            }

            return this;
        }
    }

    //static class
    ViewType = {
        init: function (_this) {
            History.addState(_this);

            this.syncSearch.call(_this);
            this.syncFilter.call(_this);

            return this;
        },
        getAllTypesAsString: function () {
            return [CalendarView._type, ListView._type, ProcessView._type];
        },
        //this = object who call
        syncSearch: function () {
            //реализуєм общие интерфейсы и обнуляем свойства при переходе с других модулей.
            if (typeof Search.common_instance == 'undefined') {
                Search.implements(iCommon);
            }

            /*
            * If exist search in url address apply search
            * */
            if (this.search) {
                if (Search.getCommonInstance() == null){
                    if (Search.isSearchByUrl()) {
                        this.search.updateByUrl()

                        Search.setCommonInstance(this.search);
                    } else {
                        return this;
                    }
                };

                // синхронізували дані з інтерфейса при змінні типу view
                Search
                    .setInstance.call(this.search, Search.getCommonInstance())
                    .setTextToView()
            } else {
                Search.clearCommonInstance(); // очистили дані інтерфейса при переході на модуль.
            }
        },
        syncFilter: function () {
            //реализуєм общие интерфейсы и обнуляем свойства при переходе с других модулей.
            if (typeof Filter.common_instance == 'undefined') {
                Filter.implements(iCommon);
            }

            /*
             * If exist search in url address apply search
             * */
            var instance = this.filter || this.getFilter;

            if (instance) {
                if (Filter.getCommonInstance() == null){
                    if (Filter.isFilterByUrl()) {
                        instance.updateByUrl()

                        Filter.setCommonInstance(instance);
                    } else {
                        return this;
                    }
                };

                // синхронізували дані з інтерфейса при змінні типу view
                Filter.setInstance.call(instance, Filter.getCommonInstance())
            } else {
                Filter.clearCommonInstance(); // очистили дані інтерфейса при переході на модуль.
            }
        },

        // get preloader from some type view
        getCurrentInstance: function () {
            var instance = null,
                data = Url.parseFull();

            switch (data.controller) {
                case 'listView': {
                    instance = ListView.getInstance();
                    break;
                }
                case ProcessView._type: {
                    instance = ProcessView.getInstance(true);
                    break;
                }
                case CalendarView._type: {
                    instance = CalendarView.getInstance();
                    break;
                }
                default: {
                }
            }

            return instance;
        },

        // get preloader from some type view
        getPreloader: function () {
            var preloader,
                data = Url.parseFull();

            switch (data.controller) {
                case 'listView': {
                    preloader = ListView.getInstance().getPreloader();
                    break;
                }
                case ProcessView._type: {
                    preloader = ProcessView.getInstance().getPreloader();
                    break;
                }
                case CalendarView._type: {
                    preloader = CalendarView.getInstance().getPreloader();
                    break;
                }
                default: {
                    preloader = Preloader.createInstance();
                }
            }

            return preloader;
        },

        //static
        afterLoadView: function () {
            var device = Global.browser.getDevice(),
                element = device[device.length-1];

            if ($.inArray(element, [Constant.DEVICE_IPHONE, Constant.DEVICE_IPAD]) >= 0) {
                var instance = CommunicationsBlock.getInstance();

                if (instance) {
                    instance.initScroll()
                }

                return this;
            }
        }
    }

    iModule = {
        copy_id: null,
        key: null,
        parent: null, // ccылка на обект родителя
        parent_element: null, //если смотреть визуально, то єто ссылка на родительский елемент в HTML

        //constructor
        implements: function () {
            for (var key in iModule) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iModule[key];
                }
            }

            return this;
        },
        setCopyId: function (copy_id) {
            this.copy_id = copy_id;

            return this;
        },
        getCopyId: function () {
            return this.copy_id;
        },
        setParent: function (parent) {
            this.parent = parent || null;

            return this;
        },
        setParentElement: function (parent_element) {
            this.parent_element = parent_element || null;

            return this;
        },
        getParent: function () {
            return this.parent;
        },
        getParentElement: function () {
            return this.parent_element;
        },
        parseUrlByCopyId: function () {
            var r = null,
                path = location.pathname;

            if (this.copy_id) {
                return this.copy_id;
            }

            if (path) {
                r = Number(location.pathname.substring(location.pathname.lastIndexOf('/')+1));
            };

            return r;
        },
        setKey : function(key){
            this.key = key;
            return this;
        },
        getKey : function(){
            return this.key;
        }
    }

    /*Интерфейс очереди*/
    iQueue = {
        queue: null,
        queue_status: false, // true - enable; false - disable;

        implements: function () {
            for (var key in iQueue) {
                this[key] = iQueue[key];
            }

            //clear as static property
            this.queue = null;
            this.queue_status = false;

            return this;
        },
        getCounterQueue: function () {
            return this.queue ? this.queue.length : 0;
        },
        isQueue: function () {
            return this.queue && this.queue.length ? true : false;
        },
        getQueueStatus: function () {
            return this.queue_status;
        },
        setQueueStatus: function (bool) {
            this.queue_status = bool;

            return this;
        },
        setDataToQueue: function (data) {
            if (!this.queue) {
                this.queue = []
            }

            this.queue.push(data);

            return this;
        },
        getLastDataFromQueue: function () {
            return this.queue[this.queue.length - 1];
        },
        removeDataFromQueue: function () {
            return this;
        },
        clearQueue: function () {
            this.queue = [];

            return this;
        },
    }

    iGlobal = {
        finished_object_switch: false,

        implements : function(){
            for (var key in iGlobal) {
                if ($.inArray(key,['implements'])< 0) {
                    this[key] = iGlobal[key];
                }
            }
        },
        setSwitchFinishedObject: function (bool) {
            this.finished_object_switch = bool;

            return this;
        },
        getSwitchFinishedObject: function (bool) {
            return this.finished_object_switch;
        },
        setParent : function (parent) {
            this._parent = parent;

            return this;
        }
    };

    iStore = {
        store: null,

        implements : function(){
            for (var key in iStore) {
                if ($.inArray(key,['implements'])< 0) {
                    this[key] = iStore[key];
                }
            }
            this.store = {};
        },
        setDataToStore: function (key, json) {
            this.store = this.store || {};

            this.store[key] = json;

            return this;
        },
        getDataFromStore: function (key) {
            return this.store[key] || null;
        },
        clearDataStore: function () {
            return this.store = {};
        }
    }

    var iDraft = {
        _keyOfDraft: null,

        //constructor
        implements: function () {
            for (var key in iDraft) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iDraft[key];
                }
            }

            return this;
        },

        isDraft() {
            return this.getKeyOfDraft ? true : false
        },
        getKeyOfDraft: function() {
            return this._keyOfDraft;
        },
        setKeyOfDraft: function (key) {
            this._keyOfDraft = key;

            return this;
        }
    }

    iLifeCycle = {
        after_load_view_callback: null,

        implements : function(){
            for (var key in iLifeCycle) {
                if ($.inArray(key,['implements'])< 0) {
                    this[key] = iLifeCycle[key];
                }
            }
        },
        afterViewChanges: function () {
            return this;
        },
        afterLoadView: function () {
            return this;
        },
        onChange : function () {
          return this;
        },
        setAfterLoadViewCallBack: function (handler) {
            this.after_load_view_callback = handler;

            return this;
        },
        isAfterLoadViewCallBack: function () {
            return this.after_load_view_callback ? true : false;
        },
        callAfterLoadViewCallBack: function () {
            this.after_load_view_callback();

            return this;
        },
    }

    iTimeStamp = {
        time_stamp: null,

        //constructor
        implements: function () {
            for (var key in iTimeStamp) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iTimeStamp[key];
                }
            }

            return this;
        },
        setTimeStamp: function (time) {
            this.time_stamp = time || this.getNewTimeStamp();

            return this;
        },
        getTimeStamp: function () {
            return this.time_stamp;
        },
        getNewTimeStamp: function () {
            return moment().unix();
        },
        getNewMilliseconds: function () {
            return moment().valueOf();
        }
    };

    iTemplate = {
        template: null,

        //constructor
        implements: function () {
            for (var key in iTemplate) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iTemplate[key];
                }
            }

            return this;
        },
        setTemplate: function(template) {
            return this.template = template;
        },
        getTemplate: function () {
            return this.template
        },
    }

    // Предположительно будет использоваться в поисках и фильтрах между LV/PV/CV
    iCommon = {
        common_instance: null,

        getThis: function () {
            return this;
        },
        //constructor
        implements: function () {
            for (var key in iCommon) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iCommon[key];
                }
            }

            return this;
        },
        setCommonInstance: function (instance) {
            this.common_instance = instance;

            return this;
        },
        clearCommonInstance: function () {
            this.common_instance = null;

            return this;
        },
        getCommonInstance: function () {
            return this.common_instance;
        }
    }

    iContentReload = {
        content_reload: null,

        //constructor
        implements: function () {
            for (var key in iContentReload) {
                if ($.inArray(key, ['implements'])< 0) {
                    this[key] = iContentReload[key];
                }
            }

            return this;
        },
        // для передачи временно текущего екземпляра запроса, между обектами.
        setContentReloadInstance: function (instance) {
            this.content_reload = instance;

            return this;
        },
        getContentReloadInstance: function () {
            return this.content_reload;
        },
    }

    ContentReload = {
        _content_vars : {}, // static

        _object : null,
        _action_key : null,
        _vars : null,
        _vars_general_content_b : null,
        _vars_general_content_s : null,
        _url : null,
        _status : true,
        _search : null,
        _running: false,
        _headers : {
            'Cache-Control' : 'max-age=3600'
        },

        _preloader_auto_hide : true,
        _ajax_data : {},
        _callback_complete : null,
        _callback_success_complete : null,

        callback_done: null,
        _timer: {
            begin: 0
        },
        // реализация интерфейса
        implements: function (object) {
            object.implements.call(this);

            return this;
        },
        reDefinition: function () {
            this.showPreloader = ContentReload.showPreloader;
            this.hidePreloader = function() {
                //Основна версія
                if (this.preloader && this.preloader._element && this.preloader._element.length) {
                    this.preloader.hide();
                } else {
                    // КОСТИЛЬ
                    $.each(this.preloaderClasses || [], function (i, val) {
                        $('.' + val).removeClass(val);
                    });
                    $.each(this.preloaderBlocks || [], function (i, val) {
                        $(val).addClass('hide');
                    });
                }

                $('#container').find('>.b-spinner').addClass('hide');

                AjaxContainers.cash.snapshot();
                return this;
            };

            return this;
        },
        isRunning: function () {
            return this._running;
        },
        setRunning: function (bool) {
            this._running = bool;

            return this
        },
        createInstance : function(){
            var Obj = function(){
                for(var key in ContentReload){
                    this[key] = ContentReload[key];
                }
            }

            var instance = new Obj;

            iAction.implements.call(instance);
            return instance;
        },
        setSearch : function (data) {
            if (data) {
                this._search = true;
            } else {
                delete this._search;
            }

            return this;
        },
        getTimeOut : function() {
            var delta,
                timerForPreloader = 100; // original 100

            this._timer.end = moment().unix();
            delta = timerForPreloader - (this._timer.end - this._timer.begin);

            return (delta <= 0) ? 0 : delta;
        },
        setPreloaderAutoHide: function(data) {
            this._preloader_auto_hide = data;

            return this;
        },
        showPreloader: function () {
            if (!this.preloader) {
                this.preloader = Preloader.createInstance();
            }
            this.preloader.showPreloader();

            return this;
        },
        //clear
        clear : function(){
            this._action_key = null;
            this._vars = null;
            this._url = null;
            this._status = true;
            this._exit = false; // abort
            this._ajax_data = {};
            this._callback_complete = null;
            this._search = null;
            this._callback_success_complete = null;

            return this;
        },

        abort : function () {
            var _this = this;

            $.each(Object.keys(AjaxContainers.keys), function (key, data) {
                $.each($(data).instance || [], function(key, value){
                    $(value).abort();
                })
                AjaxContainers.removeAll().addKey(_this._action_key);
            })

            if (!this._ajax) {
                this._ajax = new Ajax();
            }
            return this;
        },

        //setObject
        setObject : function(_object){
            this._object = $(_object);
            return this;
        },


        //setCallBackComplete
        setCallBackComplete : function(callback){
            this._callback_complete = callback;
            return this;
        },

        //setCallBackSuccessComplete
        setCallBackSuccessComplete : function(callback){
            this._callback_success_complete = callback;
            return this;
        },

        //setVars
        setVars : function(vars){
            if(typeof(vars) == 'undefined' || !vars){
                this._status = false;
                return this;
            }

            this._vars = vars;
            return this;
        },
        setVarsFromPage : function (content_vars, key_b, key_s) {
            if(content_vars){
                content_vars = JSON.parse(content_vars);
                ContentReload.addContentVars(content_vars);

                if (key_b && key_b.length) {
                    ContentReload.actionSetVarsToGeneralContent(key_b[0], key_b[1]);
                }

                if (key_s && key_s.length){
                    ContentReload.actionSetVarsToGeneralContent(key_s[0], key_s[1]);
                }
            }
        },

        //appendVars
        appendVars : function(vars, _this_vars){
            var _this = this;
            if(typeof(vars) == 'undefined' || vars == false){
                return this;
            }

            $.each(vars, function(key, value){
                if(typeof (_this_vars) == 'undefined'){
                    _this_vars = _this._vars;
                }

                // рекурсия работает только для первого уровня вложенности
                if(typeof (value) == 'object' && value !== null){
                    if(typeof (_this_vars[key]) == 'undefined'){
                        _this_vars[key] = {}
                    }

                    _this.appendVars(value, _this_vars[key]);
                    return true;
                }

                _this_vars[key] = value;
            })

            return this;
        },

        //getVars
        getVars : function(){
            return this._vars;
        },

        //setHistoryUrl
        setHistoryUrl : function(url){
            if (!window.backForward) {
                History.history_params_list.push(url);
                history.pushState(null, null, url);
            }
            return this;
        },

        //setActionKey
        setActionKey : function(action_key){
            if(typeof(action_key) == 'undefined' || action_key == false){
                this._status = false;
                return this;
            }
            this._action_key = action_key;
            return this;
        },

        //setUrl
        setUrl : function(url){
            this._url = url;
            return this;
        },

        //setStatus
        setStatus : function(status){
            this.status = status;
            return this;
        },
        setCallBackDone : function(callback){
            this.callback_done = callback;

            return this;
        },
        //addContentVars
        addContentVars : function(content_vars){
            if(typeof(content_vars) == 'undefined' || !content_vars) return;

            var _this = this;
            $.each(content_vars, function(key, value){
                _this._content_vars[key] = value;
            })

            return this;
        },

        //getLinkAction
        getContentVars : function(action_key){
            if(typeof(action_key) == 'undefined' || action_key == false){
                this._status = false;
                return false;
            }
            return this._content_vars[action_key];
        },


        //prepareAjaxData
        prepareAjaxData : function(){
            if(this._status == false) return this;
            if(this._vars){
                if(this._vars['content_blocks']){
                    this._ajax_data['content_blocks'] = this._vars['content_blocks'];
                }
                if(this._vars['content_blocks_different']){
                    this._ajax_data['content_blocks_different'] = this._vars['content_blocks_different'];
                }
            }

            return this;
        },

        //prepareVariablesAuto
        prepareVariablesAuto : function(){
            this.clear();

            var action_key = this._object.data('action_key');
            var vars = this.getContentVars(action_key);

            if(!vars) return this.setStatus(false);

            this._vars = Base.copyObject(vars);
            this._action_key = action_key;

            return this;
        },

        //prepareVariablesToGeneralContent - перегрузка контента главной (активной) страницы: listView, processView
        prepareVariablesToGeneralContent : function(all_page){
            this.clear();

            // big - блок + данные
            if(typeof all_page != 'undefined' && all_page){
                if(this._vars_general_content_b == null || this._vars_general_content_b == false){
                    return this.setStatus(false);
                }
                this._vars = Base.copyObject(this._vars_general_content_b);
                this._action_key = this._vars_general_content_b['action_key'];
                //small - сами данные
            } else {
                if(this._vars_general_content_s == null || this._vars_general_content_s == false){
                    return this.setStatus(false);
                }
                this._vars = Base.copyObject(this._vars_general_content_s);
                this._action_key = this._vars_general_content_s['action_key'];
            }

            if (Search.isSearchByUrl()) {
                this.setSearch(true);
            }

            return this;
        },

        //prepareVariablesToProcessView
        prepareVariablesToProcessView : function(list_exist){
            if(typeof(this._vars['module']['destination']) == 'undefined' || this._vars['module']['destination'] != 'processView') return this;

            if(typeof(this._vars['module']['data_id_list']) != 'undefined' && !$.isEmptyObject(this._vars['module']['data_id_list'])){
                this._vars['module']['process_view_load_panels'] = 1;

                this._ajax_data['data_id_list'] = this._vars['module']['data_id_list'];
                this._ajax_data['process_view_load_panels'] = this._vars['module']['process_view_load_panels'];

                if(typeof this._vars['module']['process_view_action'] != 'undefined' && typeof this._vars['module']['process_view_action']){
                    this._ajax_data['process_view_action'] = this._vars['module']['process_view_action'];
                }
            }

            if(typeof(this._vars['module']['sorting_list_id']) != 'undefined' && this._vars['module']['sorting_list_id']){
                if (list_exist == false) {
                    return this;
                }
                this._ajax_data['sorting_list_id'] = this._vars['module']['sorting_list_id'];
            }

            return this;
        },

        //action run
        run : function(){
            if(this._status == false) return this;

            if(!this._vars) return this.setStatus(false);
            if(this._action_key == false) return this.setStatus(false);
            if(!this._vars['action_run'] || this._vars['action_run'] == false)  return this.setStatus(false);

            this._running = true;

            // run method
            var method_name = this._vars['action_run'];
            this[method_name]();

            Global.changeBackground();

            return this;
        },
        //action runActionAfter
        runActionAfter : function(){
            var _this = this;
            if(this._vars && this._vars['action_after'] == false) return;
            if($.isEmptyObject(this._vars['action_after'])) return;

            $.each(this._vars['action_after'], function(key, method_name){
                _this[method_name]();
            })
        },

        //checkExpediencySwitchPage - проверка активного модуля и целесообразности его загрузки\перегрузки
        checkExpediencySwitchPage : function(){
            var url = document.location.pathname + document.location.search;
            url = decodeURIComponent(url.replace(/\+/g,  " "));

            if(url == this._url) return false;

            return true;
        },


        //loadPage
        loadPage : function() {
            var _this = this;

            this.getPageUrl(this._vars && this._vars['index'], function() {
                if(_this._vars && typeof _this._vars['check_expediency_switch'] != 'undefined' && _this._vars['check_expediency_switch']){
                    if(_this.checkExpediencySwitchPage() == false){
                        if(typeof _this._callback_success_complete == 'function'){
                            _this._callback_success_complete();
                            _this._callback_success_complete = null;
                        }
                        _this.runActionAfter();
                        return;
                    }
                }


                if(_this._vars['selector_content_box']){
                    _this.ajaxLoadContent(_this._url, function(data){
                        if(data){
                            _this.setHistoryUrl(_this._url);
                            if(typeof _this._callback_success_complete == 'function'){
                                _this._callback_success_complete(data);
                                _this._callback_success_complete = null;
                            }
                        }
                    })
                } else {
                    document.location.href = _this._url;
                }
            });
        },

        //loadModule
        loadModule : function(){
            var _this = this;

            this.getModuleUrl(function(){
                if(_this._vars && typeof _this._vars['check_expediency_switch'] != 'undefined' && _this._vars['check_expediency_switch']){
                    if(_this.checkExpediencySwitchPage() == false){
                        if(typeof _this._callback_success_complete == 'function'){
                            _this._callback_success_complete();
                            _this._callback_success_complete = null;
                        }
                        _this.runActionAfter();
                        _this.afterLoadView();
                        _this.hidePreloader();
                        return;
                    }
                }

                if(_this._vars['selector_content_box']){
                    _this.ajaxLoadContent(_this._url, function(data){
                        if(data){
                            _this.setHistoryUrl(_this._url);
                            if(typeof _this._callback_success_complete == 'function'){
                                _this._callback_success_complete(data);
                                _this._callback_success_complete = null;
                            }
                        }
                    })
                } else {
                    if(typeof _this._callback_success_complete == 'function'){
                        _this._callback_success_complete(_this._url);
                        _this._callback_success_complete = null;
                    } else {
                        document.location.href = _this._url;
                    }
                }
            });
        },

        //loadBpmProcess
        loadBpmProcess  : function(){
            var _this = this;

            //this.runBefore();

            var _url = '/';

            switch(this._vars['module']['process_mode']){
                case 'run' :
                    _url = Global.urls.url_process_view_run;
                    break;
                case 'constructor' :
                    _url = Global.urls.url_process_view_constructor;
                    break;
            }

            _this.setUrl(_url + '?process_id=' + this._vars['module']['process_id']);

            if(_this._vars && typeof _this._vars['check_expediency_switch'] != 'undefined' && _this._vars['check_expediency_switch']){
                if(_this.checkExpediencySwitchPage() == false){
                    if(typeof _this._callback_success_complete == 'function'){
                        _this.hidePreloader();
                        _this._callback_success_complete();
                        _this._callback_success_complete = null;
                    }
                    _this.runActionAfter();
                    return this;
                }
            }

            _this.ajaxLoadContent(_this._url, function(data){
                if(data){
                    _this.setHistoryUrl(_this._url);
                    if(typeof _this._callback_success_complete == 'function'){
                        _this._callback_success_complete(data);
                        _this._callback_success_complete = null;
                    }
                }
            });

            return this;
        },



        //loadToLink
        loadToLink : function(){
            var _this = this;
            var url = this._vars['url'];
            _this.setUrl(url);

            //this.runBefore();
            this.ajaxLoadContent(url, function(data){
                if(data){
                    _this.setHistoryUrl(url);
                    if(typeof _this._callback_success_complete == 'function'){
                        _this._callback_success_complete(data);
                        _this._callback_success_complete = null;
                    }
                }
            })
        },

        //loadLogo
        loadLogo : function(){
            var action_key = $('.element[data-type="module_menu"] li a.ajax_content_reload').data('action_key')
            if(typeof (action_key) == 'undefined' || action_key == false){
                document.location.href = '/';
            }

            this.actionHideLeftMenu();

            var vars = this.getContentVars(action_key);
            this
                .setActionKey(action_key)
                .setVars(vars)
                .run()
                .actionSwitchMenuByActionKey();

        },

        //loadThis
        loadThis : function(){
            var _this = this;

            _this.setUrl(this._url);

            this.ajaxLoadContent(this._url, function(data){
                if(data.status == true){
                    if(typeof _this._callback_success_complete == 'function'){
                        _this._callback_success_complete(data);
                        _this._callback_success_complete = null;
                    }
                }
            });
        },

        //getPreparedContent
        getPreparedContent : function(html_content){
            var html_content = $(html_content);
            html_content.find('.select').selectpicker({
                style: 'btn-white',
                noneSelectedText: Message.translate_local('None selected')
            });

            return html_content;
        },

        //runBefore
        runBefore : function(){
            var $wrapper = $('.list_view_block .crm-table-wrapper'),
                $_wrapper = $('.wrapper');

            $wrapper.getNiceScroll().remove();
            $_wrapper.attr('style',''); // pv
            $wrapper.addClass('overflowHidden'); // remove old niceScroll
            $('html').addClass('overflowHidden');

            if (this.clazz.indexOf('goto-pv')>=0) {
                $_wrapper.addClass('page_process_view');
            }
        },


        //runAfterAjaxSuccess
        runAfterAjaxSuccess : function(data, callback){
            var _this = this,
                module = _this._vars['module'];

            //process_view panels
            if(typeof (module) != 'undefined' && typeof (module['process_view_load_panels']) != 'undefined' && module['process_view_load_panels']){
                ProcessView.runAfterAjaxSuccess(data, _this._vars['module'], true);
                delete module['process_view_load_panels'];
            } else {
                var $selector = $(this._vars['selector_content_box']),
                    container_copy = Base.copyObject(AjaxContainers);

                $.each($selector.find('[data-action_key]'), function (key, value) {
                    delete _this._content_vars[$(value).attr('data-action_key')];
                });
                AjaxContainers = Base.copyObject(container_copy);

                $selector.html(this.getPreparedContent(data.content_html));
            }

            //content_html_different
            if(data.content_html_different){
                $.each(data.content_html_different, function(source_name, html){
                    var selector = '';
                    $.each(_this._vars['content_blocks_different'], function(key, row){
                        if(row['name'] == source_name){
                            selector = row['selector'];
                        }
                    });
                    $(selector).after(html).remove();
                })
                Global.responsiveNav();
            }

            this.runActionAfter();
            this._object = null;

            AjaxContainers.removeAll();
            if(typeof (callback) == 'function'){
                callback();
            }
        },

        //base function for redefining
        hidePreloader : function () {
            var preloader,
                globalInstance = Global.getInstance();

            if (globalInstance && globalInstance.getPreloader()) {
                preloader = globalInstance.getPreloader()
            }

            if (preloader) {
                preloader.hide();
                AjaxContainers.cash.snapshot();
            } else {
                Preloader.hideAll();
                this.preloaderHide();
            }

            return this;
        },
        //preloaderHideclass="b-spinner"
        preloaderHide : function(){
            $.each(Preloader.preloaderClasses || [], function (i, val) {
                $('.' + val).removeClass(val);
            });
            $.each(this.preloaderBlocks || [], function (i, val) {
                $(val).addClass('hide');
            });

            $('#container').find('>.b-spinner').addClass('hide');

            AjaxContainers.cash.snapshot();
            return this;
        },
        //для переопределения
        afterLoadView: function () {

          return this;
        },
        //ajaxLoadContent
        ajaxLoadContent : function(url, callback){
            var _data, time,
                _this = this;

            this.prepareAjaxData();

            _this._ajax_data.action_key = _this._action_key;

            if (!this._ajaxContent) {
                this._ajaxContent = AjaxObj.createInstance();
            }

            this._ajaxContent
                .setAsync(true)
                .setUrl(url)
                .setData(_this._ajax_data)
                .setHeaders(_this._headers)
                .setTimeOut(crmParams.global.ajax.get_url_timeout)
                .setCallBackSuccess(function(data){
                    if (data && data.action_key && _this._action_key != data.action_key) {
                        data.status = false;
                        _data = null;
                        return;
                    }

                    if(data && data.status == 'error' || data && data.status == 'access_error'){
                        Message.show(data.messages, false);
                        callback(false);
                    }

                    _data = data;
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    var isOldQuery = this.data.action_key in AjaxContainers.keys;

                    if (isOldQuery && Object.keys(AjaxContainers.keys).length == 1) {
                        if (arrActionKeys.length) {
                            arrActionKeys = [];
                            Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                            _this.hidePreloader();
                        }
                    } else {
                        _this._status = false;
                    }

                    callback(false);
                })
                .setCallBackComplete(function(){
                    if(typeof _this._callback_complete == 'function'){
                        _this._callback_complete(_data);
                        _this._callback_complete = null;
                    }

                    arrActionKeys.shift();

                    if(!_data || _data.status != true || (_data.action_key && _data.action_key != _this._action_key)) {
                        return;
                    }

                    //відкидаємо відповідь на попередній запит
                    if (ContentReload.isQueue() && ContentReload.getCounterQueue() > 1) {
                        var value = ContentReload.getLastDataFromQueue();

                        if (value != _this._action_key) return this;
                    }
                    ContentReload.clearQueue();

                    _this.runAfterAjaxSuccess(_data);

                    $('.list-view-panel [data-controller="sdm"]').not(":has(.list-view-avatar)").addClass('is-not-avatar');
                    callback(_data); //here change url in history and realy

                    instanceGlobal.createOfInstances(_data.page_interface_type, _data.page_name);

                    _this.afterLoadView();

                    setCheckboxHeight(); //костиль RP || LV || constructor
                    _this.hidePreloader();
                    _this.setSearch(false);

                    _this._running = false;

                    if ($('.search-filter .search-input').length) {
                        var instance = Search.getInstance(true);
                        Global._search_instance = instance;
                        instance.updateByUrl();
                    }

                    var currentInstance = ViewType.getCurrentInstance()
                    if (currentInstance && currentInstance.afterLoadView) {
                        currentInstance.afterLoadView();
                    }

                    // Cохранение видимости столбцов в памяти. // КОСТИЛЬ
                    ListViewDisplay
                        .setThis(null)
                        .setIndex()
                        .setHiddenGroupIndex(this);

                    setCheckboxHeight();
                })
                .setCallBackDone(function (data) {
                    if ($.isFunction(_this.callback_done)) {
                        _this.callback_done();
                        _this.callback_done = null;
                    }
                })

            time = setTimeout(function () {
                clearTimeout(time);
                AjaxContainers.addInstance(_this._action_key, _this._ajaxContent);
                _this._ajaxContent.send();
            }, 50);
        },

        //getPageUrl
        getPageUrl: function(index, callback){
            if(this._url !== null){
                return callback(this._url);
            }

            var _this = this;

            $.post(Global.urls.get_user_storage_url_from_index, {'index' : index}, function(data){
                if(data.status == true){
                    _this.setUrl(data.url);
                    if($.isFunction(callback)){
                        callback();
                    }
                }
            })
        },


        //getModuleUrlData
        getModuleUrlData : function(){
            return this._vars.module;
        },

        //getModuleUrl
        getModuleUrl : function(callback){
            if(this._url !== null){
                return callback(this._url);
            }

            var send,
                _this = this;

            if (!this._ajax) {
                this._ajax = AjaxObj.createInstance();
            }

            AjaxContainers.addInstance(this._action_key, this._ajax);

            send = function (send_more) {
                var data = _this.getModuleUrlData();

                data.action_key = _this._action_key;

                _this._ajax
                    .setAsync(true)
                    .setUrl(Global.urls.get_user_storage_url)
                    .setData(data)
                    .setHeaders(_this._headers)
                    .setType('POST')
                    .setTimeOut(crmParams.global.ajax.get_url_timeout)
                    .setCallBackSuccess(function(data) {
                        if(data.status == true){
                            data._search = _this._search;//  старый подход
                            data.url = Search.checkCommonInstance(data);

                            _this.setUrl(data.url);

                            if(typeof data.params != 'undefined' && typeof data.params.pci != 'undefined'){
                                if(typeof _this._vars.module != 'undefined' && _this._vars.module.params && _this._vars.module.params.pci){
                                    _this._vars.module.params.pci = data.params.pci;
                                }
                            }
                            if(typeof data.params != 'undefined' && typeof data.params.pdi != 'undefined'){
                                if(typeof _this._vars.module != 'undefined' && _this._vars.module.params && _this._vars.module.params.pdi){
                                    _this._vars.module.params.pdi = data.params.pdi;
                                }
                            }

                            if($.isFunction(callback)){
                                callback();
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        if ($('[data-type="message"]').length) return;

                        if(jqXHR.status == 0 && jqXHR.readyState == 0  && !send_more){
                            send(true);
                        } else {
                            Message.showErrorAjax(jqXHR, textStatus);
                            Preloader.modalHide();
                            arrActionKeys = [];
                        }
                    })
                    .send();
            }

            send();
        },


        /**
         * getActionKeyMenuByCopyId
         */
        getActionKeyMenuByCopyId : function(copy_id){
            var _action_key = null;
            var _this = this;

            if(_this._content_vars == null || _this._content_vars === false) return _action_key;
            $.each(_this._content_vars, function(action_key, vars){
                if(vars['action_run'] != 'loadModule' && vars['action_run'] != 'loadBpmProcess'){
                    return true;
                }

                if(
                    typeof(vars['module']) != 'undefined' &&
                    typeof(vars['module']['params']) != 'undefined' &&
                    typeof(vars['module']['params']['pci']) != 'undefined' &&
                    vars['module']['params']['pci'] == copy_id
                ){
                    _action_key = action_key;
                } else if(
                    typeof(vars['module']) != 'undefined' &&
                    typeof(vars['module']['copy_id']) != 'undefined' &&
                    vars['module']['copy_id'] == copy_id
                ){
                    _action_key = action_key;
                }


                if(_action_key !== null && _action_key){
                    var i = $('.element[data-type="module_menu"] .nav li .ajax_content_reload[data-action_key="'+_action_key+'"], ' +
                        '.element[data-type="left_menu"] .nav li .ajax_content_reload[data-action_key="'+_action_key+'"]').length;
                    if(i){
                        return false;
                    }
                }
            })

            return _action_key;
        },


        /**
         * Actions
         */


        //SwitchMenu
        actionSwitchMenu : function(){
            var is_set = false;

            if(this._status == false) return this;

            var _this = this;

            $.each(this._content_vars, function(action_key, vars){
                if(
                    this._action_key != action_key &&
                    (
                        (typeof(_this._vars['index']) != 'undefined' && vars['index'] == _this._vars['index']) ||
                        (
                            typeof(_this._vars['module']) != 'undefined' && typeof(_this._vars['module']['copy_id']) != 'undefined' &&
                            typeof(vars['module']) != 'undefined' &&  vars['module']['copy_id'] == _this._vars['module']['copy_id']
                        )
                    )
                ){

                    if(
                        typeof _this._vars.module != 'undefined' &&
                        typeof _this._vars.module.params != 'undefined' &&
                        typeof _this._vars.module.params.pci != 'undefined' &&
                        _this._vars.module.params.pci)
                    {
                        _this.actionSwitchMenuByCopyId(_this._vars.module.params.pci);
                        is_set = true;
                        return false;
                    }

                    var len = $('.element[data-type="module_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"], ' +
                        '.element[data-type="left_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"]').length;
                    if(len == false){
                        return true;
                    }

                    _this.actionSwitchMenuByActionKey(action_key);
                    is_set = true;
                    return false;
                }
            });


            if($(this._object).closest('.element[data-type="user_menu"]').length){
                return this;
            }

            if(is_set == false){
                _this.actionSwitchMenuByActionKey();
            }

            return this;
        },

        //SwitchMenuByActionKey
        actionSwitchMenuByActionKey : function(action_key){
            if(this._status == false) return this;

            if(typeof(action_key) == 'undefined' || action_key == false){
                action_key = this._action_key;
            }

            this.actionDeactiveElementsMenu();

            var _el = $('.ajax_content_reload[data-action_key="' + action_key + '"]');
            if(_el.closest('.element[data-type="user_menu"]').length > 0) return this;

            $('.element[data-type="module_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"]').closest('li').addClass('active');
            $('.element[data-type="left_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"]').closest('li').addClass('active');

            return this;
        },

        //SwitchMenuByActionKey
        actionSwitchMenuByCopyId : function(copy_id){
            if(this._status == false) return this;

            if(typeof(copy_id) == 'undefined' || copy_id == false){
                if(
                    typeof this._vars['module'] != 'undefined' &&
                    typeof this._vars['module']['params'] != 'undefined' &&
                    typeof this._vars['module']['params']['pci'] != 'undefined' &&
                    this._vars['module']['params']['pci']){
                    copy_id = this._vars['module']['params']['pci'];
                } else {
                    copy_id = this._vars['module']['copy_id'];
                }
            }

            var action_key = this.getActionKeyMenuByCopyId(copy_id);
            if(action_key == null || action_key == false){
                return this;
            }

            this.actionDeactiveElementsMenu();

            var _el = $('.ajax_content_reload[data-action_key="' + action_key + '"]');
            if(_el.closest('.element[data-type="user_menu"]').length > 0) return;

            $('.element[data-type="module_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"]').closest('li').addClass('active');
            $('.element[data-type="left_menu"] .nav li .ajax_content_reload[data-action_key="'+action_key+'"]').closest('li').addClass('active');

            return this;
        },


        //ShowLeftMenu
        actionShowLeftMenu : function(){
            if(this._status == false) return this;

            $('.element[data-type="left_menu"]').removeClass('hide');
            $('.settings-section').removeClass('col-xs-12').addClass('col-xs-9');
            $('html').addClass('page-with-left-menu');

            return this;
        },

        actionShowCommunicationConfigPopup : function(){
            Communication.openMenuServices();
            return this;
        },

        //HideLeftMenu
        actionHideLeftMenu : function(){
            if(this._status == false) return this;

            $('.element[data-type="left_menu"]').addClass('hide');
            $('.settings-section').removeClass('col-xs-9').addClass('col-xs-12');
            $('html').removeClass('page-with-left-menu');

            return this;
        },

        //DeactiveElementsMenu
        actionDeactiveElementsMenu : function(){
            if(this._status == false) return this;
            $('.element[data-type="module_menu"] .nav li').removeClass('active');
            $('.element[data-type="left_menu"] .nav li').removeClass('active');

            return this;
        },

        //ShowEditView
        actionShowEditView : function(data_id){
            if(this._status == false) return this;

            var task,
                url = EditView.getEditViewUrl() + '/' + this._vars['module']['copy_id'],
                contentReload = instanceGlobal.contentReload;

            if(typeof data_id == 'undefined' || data_id == false){
                data_id = this._vars['module']['data_id'];
            }

            task = contentReload._vars && contentReload._vars['task'];

            EditView
                .createInstance()
                .setParent(ViewType.getCurrentInstance())
                .setPreloader(ViewType.getPreloader().setModal(true))
                .show(this._vars['module']['copy_id'], data_id, url, function(){
                    if(task) {
                        Preloader.modalHide();
                        delete instanceGlobal.contentReload._vars['task'];
                    }
                });

            return this;
        },

        //actionShowProcessBpmOperation
        actionShowProcessBpmOperation : function(){
            if(this._status == false) return this;

            ProcessObj.BPM.autoShowTask(this._vars['module']['unique_index']);

            return this;
        },


        //SetVarsToGeneralContent
        actionSetVarsToGeneralContent : function(action_key, all_page){
            if(typeof all_page != 'undefined' && all_page){
                if(typeof(action_key) != 'undefined' && action_key){
                    this._vars_general_content_b = this.getContentVars(action_key);
                    this._vars_general_content_b['action_key'] = action_key;
                } else {
                    this._vars_general_content_b = this._vars;
                    this._vars_general_content_b['action_key'] = this._action_key;
                }
            } else {
                if(typeof(action_key) != 'undefined' && action_key){
                    this._vars_general_content_s = this.getContentVars(action_key);
                    this._vars_general_content_s['action_key'] = action_key;
                } else {
                    this._vars_general_content_s = this._vars;
                    this._vars_general_content_s['action_key'] = this._action_key;
                }
            }

            return this;
        },

        actionRefreshContentReloadDifferentBlocks : function(vars){
            var ajax = new Ajax();
            ajax
                .setAsync(true)
                .setUrl('/site/getContentReloadDifferentBlock')
                .setData(vars)
                .setType('POST')
                .setTimeOut(crmParams.global.ajax.get_url_timeout)
                .setCallBackSuccess(function(data) {
                    if(data.status == true){
                        if(data.content_html_different){
                            $.each(data.content_html_different, function(source_name, html){
                                var selector = '';
                                $.each(vars['content_blocks_different'], function(key, row){
                                    if(row['name'] == source_name){
                                        selector = row['selector'];
                                    }
                                });
                                $(selector).after(html).remove();
                            })
                            Global.responsiveNav();
                        }
                    }
                })
                .send();
        }
    }


    Global = {
        DISABLE: 'disable',
        ENABLE: 'enable',
        labels: {}, // метки

        urls: null, // список методів на бекенді які викликаємо з js
        _search_instance: null,
        list_instances: null, // {} - список інтерфейсів

        getModel : function () {
            return $('[data-model-global]').data()['modelGlobal'];
        },
        addClass : function (element, name) {
            $(element).addClass(name);
        },
        removeClass : function (element, name) {
            $(element).removeClass(name);
        },
        //генерация MD5 хеш кода
        generateMD5 : function(r){
            function n(r,n){return r<<n|r>>>32-n}function t(r,n){var t,o,e,u,f;return e=2147483648&r,u=2147483648&n,t=1073741824&r,o=1073741824&n,f=(1073741823&r)+(1073741823&n),t&o?2147483648^f^e^u:t|o?1073741824&f?3221225472^f^e^u:1073741824^f^e^u:f^e^u}function o(r,n,t){return r&n|~r&t}function e(r,n,t){return r&t|n&~t}function u(r,n,t){return r^n^t}function f(r,n,t){return n^(r|~t)}function i(r,e,u,f,i,a,c){return r=t(r,t(t(o(e,u,f),i),c)),t(n(r,a),e)}function a(r,o,u,f,i,a,c){return r=t(r,t(t(e(o,u,f),i),c)),t(n(r,a),o)}function c(r,o,e,f,i,a,c){return r=t(r,t(t(u(o,e,f),i),c)),t(n(r,a),o)}function C(r,o,e,u,i,a,c){return r=t(r,t(t(f(o,e,u),i),c)),t(n(r,a),o)}function g(r){for(var n,t=r.length,o=t+8,e=(o-o%64)/64,u=16*(e+1),f=Array(u-1),i=0,a=0;t>a;)n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|r.charCodeAt(a)<<i,a++;return n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|128<<i,f[u-2]=t<<3,f[u-1]=t>>>29,f}function h(r){var n,t,o="",e="";for(t=0;3>=t;t++)n=r>>>8*t&255,e="0"+n.toString(16),o+=e.substr(e.length-2,2);return o}function d(r){r=r.replace(/\r\n/g,"\n");for(var n="",t=0;t<r.length;t++){var o=r.charCodeAt(t);128>o?n+=String.fromCharCode(o):o>127&&2048>o?(n+=String.fromCharCode(o>>6|192),n+=String.fromCharCode(63&o|128)):(n+=String.fromCharCode(o>>12|224),n+=String.fromCharCode(o>>6&63|128),n+=String.fromCharCode(63&o|128))}return n}var v,m,S,l,A,s,y,b,p,w=Array(),L=7,j=12,k=17,q=22,x=5,z=9,B=14,D=20,E=4,F=11,G=16,H=23,I=6,J=10,K=15,M=21;for(r=d(r),w=g(r),s=1732584193,y=4023233417,b=2562383102,p=271733878,v=0;v<w.length;v+=16)m=s,S=y,l=b,A=p,s=i(s,y,b,p,w[v+0],L,3614090360),p=i(p,s,y,b,w[v+1],j,3905402710),b=i(b,p,s,y,w[v+2],k,606105819),y=i(y,b,p,s,w[v+3],q,3250441966),s=i(s,y,b,p,w[v+4],L,4118548399),p=i(p,s,y,b,w[v+5],j,1200080426),b=i(b,p,s,y,w[v+6],k,2821735955),y=i(y,b,p,s,w[v+7],q,4249261313),s=i(s,y,b,p,w[v+8],L,1770035416),p=i(p,s,y,b,w[v+9],j,2336552879),b=i(b,p,s,y,w[v+10],k,4294925233),y=i(y,b,p,s,w[v+11],q,2304563134),s=i(s,y,b,p,w[v+12],L,1804603682),p=i(p,s,y,b,w[v+13],j,4254626195),b=i(b,p,s,y,w[v+14],k,2792965006),y=i(y,b,p,s,w[v+15],q,1236535329),s=a(s,y,b,p,w[v+1],x,4129170786),p=a(p,s,y,b,w[v+6],z,3225465664),b=a(b,p,s,y,w[v+11],B,643717713),y=a(y,b,p,s,w[v+0],D,3921069994),s=a(s,y,b,p,w[v+5],x,3593408605),p=a(p,s,y,b,w[v+10],z,38016083),b=a(b,p,s,y,w[v+15],B,3634488961),y=a(y,b,p,s,w[v+4],D,3889429448),s=a(s,y,b,p,w[v+9],x,568446438),p=a(p,s,y,b,w[v+14],z,3275163606),b=a(b,p,s,y,w[v+3],B,4107603335),y=a(y,b,p,s,w[v+8],D,1163531501),s=a(s,y,b,p,w[v+13],x,2850285829),p=a(p,s,y,b,w[v+2],z,4243563512),b=a(b,p,s,y,w[v+7],B,1735328473),y=a(y,b,p,s,w[v+12],D,2368359562),s=c(s,y,b,p,w[v+5],E,4294588738),p=c(p,s,y,b,w[v+8],F,2272392833),b=c(b,p,s,y,w[v+11],G,1839030562),y=c(y,b,p,s,w[v+14],H,4259657740),s=c(s,y,b,p,w[v+1],E,2763975236),p=c(p,s,y,b,w[v+4],F,1272893353),b=c(b,p,s,y,w[v+7],G,4139469664),y=c(y,b,p,s,w[v+10],H,3200236656),s=c(s,y,b,p,w[v+13],E,681279174),p=c(p,s,y,b,w[v+0],F,3936430074),b=c(b,p,s,y,w[v+3],G,3572445317),y=c(y,b,p,s,w[v+6],H,76029189),s=c(s,y,b,p,w[v+9],E,3654602809),p=c(p,s,y,b,w[v+12],F,3873151461),b=c(b,p,s,y,w[v+15],G,530742520),y=c(y,b,p,s,w[v+2],H,3299628645),s=C(s,y,b,p,w[v+0],I,4096336452),p=C(p,s,y,b,w[v+7],J,1126891415),b=C(b,p,s,y,w[v+14],K,2878612391),y=C(y,b,p,s,w[v+5],M,4237533241),s=C(s,y,b,p,w[v+12],I,1700485571),p=C(p,s,y,b,w[v+3],J,2399980690),b=C(b,p,s,y,w[v+10],K,4293915773),y=C(y,b,p,s,w[v+1],M,2240044497),s=C(s,y,b,p,w[v+8],I,1873313359),p=C(p,s,y,b,w[v+15],J,4264355552),b=C(b,p,s,y,w[v+6],K,2734768916),y=C(y,b,p,s,w[v+13],M,1309151649),s=C(s,y,b,p,w[v+4],I,4149444226),p=C(p,s,y,b,w[v+11],J,3174756917),b=C(b,p,s,y,w[v+2],K,718787259),y=C(y,b,p,s,w[v+9],M,3951481745),s=t(s,m),y=t(y,S),b=t(b,l),p=t(p,A);var N=h(s)+h(y)+h(b)+h(p);return N.toLowerCase()
        },
        createDataUnique: function () {
            return this.generateMD5((+new Date()).toString());
        },
        createUniqueKey: function(copy_id, card_id, pci, pdi, this_template, finished_object) {
            // Правила індекса:
            //     1. символи індекса - числа.
            // 2. маються складатися з значень полів copy_id +card_id шляхом їх групування.
            // 3. Загальна довжина індекса: 9-0000+0000000000 = 15 символи.
            // 9 - 1 символ
            // copy_id - 4 символів
            // card_id - 10
            // Якщо кількість символів менше зазначеного - на початку добавляються нулі.

            var key = '';
            var array = [9+'-'+1, copy_id+'-'+4 , card_id + '-' + 10];

            array.filter(function(item) {
                var value = item.split('-');

                value[0] = Number(value[0]);
                value[1] = Number(value[1]);

                var delta = value[1]-value[0].toString().length;
                var result = '';

                for(var i = 0; i< delta; i++){
                    result += '0';
                }
                key += (result + value[0]);
            });

            return key;
        },
        addInstance : function (key, json) {
            this.list_instances = (!this.list_instances) ? {} : this.list_instances;

            this.list_instances[key] = json;
            return this;
        },
        isProcessView : function() {
            return $('.process_view_block').length ? true : false;
        },

        copyToClipboard: function (elem) {
            // create hidden text element, if it doesn't already exist
            var targetId = "_hiddenCopyText_";
            var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
            var origSelectionStart, origSelectionEnd;
            if (isInput) {
                // can just use the original source element for the selection and copy
                target = elem;
                origSelectionStart = elem.selectionStart;
                origSelectionEnd = elem.selectionEnd;
            } else {
                // must use a temporary form element for the selection and copy
                target = document.getElementById(targetId);
                if (!target) {
                    var target = document.createElement("textarea");
                    target.style.position = "absolute";
                    target.style.left = "-9999px";
                    target.style.top = "0";
                    target.id = targetId;
                    document.body.appendChild(target);
                }
                target.textContent = elem.textContent;
            }
            // select the content
            var currentFocus = document.activeElement;
            target.focus();
            target.setSelectionRange(0, target.value.length);

            // copy the selection
            var succeed;
            try {
                succeed = document.execCommand("copy");
            } catch(e) {
                succeed = false;
            }
            // restore original focus
            if (currentFocus && typeof currentFocus.focus === "function") {
                currentFocus.focus();
            }

            if (isInput) {
                // restore prior selection
                elem.setSelectionRange(origSelectionStart, origSelectionEnd);
            } else {
                // clear temporary content
                target.textContent = "";
            }
            return succeed;
        },
        groupDropDowns : function (maxCount) {
            var inner = function (maxcount) {
                this.maxCount = maxcount;
            };
            inner.prototype = Object.create(null);
            inner.prototype.init = function ($linkAddItem) {
                var block, counter;

                block = $linkAddItem ? $($linkAddItem.closest('ul.inputs-block')) : $('ul.inputs-block:visible');
                counter = block.find('.counter');

                if (counter.length==1) {
                    counter.text('');
                    block.find('.todo-remove').hide();
                    counter.closest('li').attr('branch', 1);
                } else {
                    block.find('.todo-remove').show();
                    $.each(counter, function (key) {
                        key++;
                        $(this).text(key).closest('li').attr('branch', key);
                    });

                    if (this.maxCount && counter.size() >= this.maxCount) {
                        $linkAddItem.closest('li.add_list').hide();
                    }
                }
            }

            inner.prototype.listValuesOfFilters = function ($dom, key, countOfBranches) { // список параметров фильтров для Условия

                var innerObject = function ($dom, key, countOfBranches) {
                    this.key = key;
                    this.$body = $dom;
                    ProcessObj.listOfElements[key].listValuesOfFilters = Object.create(null);
                    this.listValuesOfFilters = ProcessObj.listOfElements[key].listValuesOfFilters;

                    for (var i=1; i<=countOfBranches; i++) {
                        this.listValuesOfFilters[i] = '';
                    }
                }
                innerObject.prototype = Object.create(null);
                innerObject.prototype.set = function ($element, index) {
                    var _data;

                    if ($element.is('.datepicker-range')) {
                        _data = {
                            type: 'datepicker-range',
                            dp1: $element.find('.dp1').val(),
                            dp2: $element.find('.dp2').val()
                        }
                    }
                    else {
                        _data = $element.val();
                    }

                    ProcessObj.listOfElements[this.key].listValuesOfFilters[index] = _data;
                }
                innerObject.prototype.get = function () {
                    var object = this;

                    $.each(object.$body.find('>li[branch]'), function (key, data) {
                        var branch = $(data).attr('branch'),
                            $input = $(data).find('input[type="text"]'),
                            value = object.listValuesOfFilters[branch] ? object.listValuesOfFilters[branch] : '';

                        if (!$input.not('[disabled]').length) return true; //continue

                        if (typeof value == 'object') {
                            if (value.type == 'datepicker-range') {
                                var element = $input.filter('[data-type="value_datetime"]');

                                $input.filter('.dp1').val(value.dp1);
                                $input.filter('.dp2').val(value.dp2);
                                element.val(element.val() + ' '+ value.dp1+ '-' + value.dp2);
                            }
                        } else {
                            if (!value.length || $input.is('.dp1, .dp2')) return true; // continue;

                            $(this).find('input').first().val($input.val() + ' ' + value);
                            if ($input.not(':first').length==1) {
                                $input.last().not('[disabled]').val(value);
                            }

                        }
                    });
                }
                innerObject.prototype.load = function () {
                    var object = this;

                    $.each(object.$body.find('>li[branch]'), function (key, data) {
                        var mergedVal,
                            $data = $(data),
                            branch = $data.attr('branch'),
                            $select = $data.find('[data-type="value_condition"]'),
                            addingVal = $select.find('option[value="'+$select.val()+'"]').text(),
                            $dataRange = $data.find('.datepicker-range');

                        if ($dataRange.length) {
                            mergedVal = $data.find('.dp1').val()+'-'+$data.find('.dp2').val();
                        } else {
                            mergedVal = $data.find('.element_filter[data-name="condition_value"]').val();
                        }
                        $data.find('[data-type="value_datetime"]').val(addingVal+' '+ mergedVal).attr('disabled','disabled').addClass('backWhite');
                    });
                }
                innerObject.prototype.reloadDom = function ($dom) {
                    this.$body = $dom;
                    return this;
                }

                var object = (this.data) ? this.data.reloadDom($dom) : new innerObject($dom, key, countOfBranches);
                this.data = object;
                return object;
            }

            return new inner(maxCount);
        },
        createInstance: function () {
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            _self.setInstance(new Obj().constructor()) ;
            return _self.getInstance();
        },
        getInstance : function (status) {
            if (status && !_self.instance) {
                _self.instance = this.createInstance()
                instanceGlobal = new _Global();
            }
            return _self.instance;
        },

        getInstanceFromKey : function (key) {
            return this.list_instances[key] || null;
        },
        isBmpView : function () {
            return $('.bmp-container').length ? true : false;
        },
        addOperationInSDM : function ($button) {
            var $block = $button || $('[data-type="drop_down"] button');

            if(!$block.closest('.edit-view').length) return;

            $.each($block, function () {
                var value, $list,
                    $this = $(this),
                    $operation = $this.next(),
                    id = $this.data('id');

                value = $this.attr('value');

                $list = $block.filter('button.readonly');
                $list = $list.add($block.filter('button[disabled="disabled"]'));

                $.each($list, function () {
                    $(this).next().filter('[data-type="actions"]').find('.remove').remove();
                });

                if ($operation.is('.icon-operation')) {
                    $operation.find('span').addClass('hide');
                    if (typeof value == 'string' && !value.length) {
                        value = $.trim($(this).text());
                    }

                    if (id) {
                        $operation.find('.remove').removeClass('hide');
                        $operation.find('.edit').removeClass('hide');
                    } else if (typeof value == 'string' && !value.length) {
                        $operation.find('.add').removeClass('hide');
                        $operation.find('.edit').addClass('hide');
                    } else {
                        $operation.find('.edit').addClass('hide');
                    }
                }
            });
        },
        // показываем/скрываем блок
        setBlockDisplay : function(object, status, delay){
            if(delay === null) delay = 200;
            if(status == "fa-chevron-up") {
                object.slideUp(delay);
            } else
            if(status == "fa-chevron-down") {
                object.slideDown(delay);
            }
        },
        setWindowInCenter : function ($element) {
            $element.filter('[data-is-center]').css('margin-top', ($(window).height() - $element.height())/2+'px'); // window is center
        },
        // установить скролл в елементах мах 10 записей.
        setScroll: function () {
            $('.dropdown-menu.inner.selectpicker').each(function () {
                var _element = $(this);

                if (_element.find('>li').length > 10) {
                    _element.height(240);
                }

                NiceScroll.setElement(_element).init();
            });
        },
        copyObjectTo : function (from, to) {
            var object = Object.assign(to, from);
            return object;
        },
        addEvents : function (events, object) {
            $.each(events, function (i, data) {
                if (!data.disable) {
                    // дозволені девайси
                    if (data.device) {
                        var element = Global.browser.getDevice();

                        element = element[element.length-1];
                        if ($.inArray(element, data.device) > 0) {
                            return true;
                        }
                    }
                    $(data.parent).off(data.event, data.selector).on(data.event, data.selector, object ? object : {}, data.func);
                }
            });
        },
        isHandler: function (handler) {
            return (typeof handler == 'function') ? true : false;
        },
        isListView : function() {
            return $('.list_view_block').length ? true : false;
        },
        isReport : function () {
            return $('.report-content').length ? true : false;
        },
        removeEvents : function (events) {
            $.each(events || [], function (i, data) {
                $(data.parent).off(data.event, data.selector);
            });
        },
        btnSaveSetDisabled : function($this, disabled){
            $this.attr('disabled', disabled);
        },

        btnSaveSetDisabled : function($this, disabled){
            $this.attr('disabled', disabled);
        },
        scrollTop: function ($this) {
            if ($this.closest('.header').length) {
                $('body, html').scrollTop(0);
            }
            return this;
        },
        createInstancesFront : function (data) {
            var subInstance,
                object = JSON.parse(data);

            if (object.contentReload) {
                Base.copyObjectTo(object.contentReload, instanceGlobal.contentReload);
                instanceGlobal.contentReload._ajax = null;
                instanceGlobal.contentReload._ajaxContent = null;
            }
            instanceGlobal.currentInstance.getFilter = {};
            instanceGlobal.currentInstance.getFilter = Base.copyObject(Filter);

            switch (object._type || object.type) {
                case 'reports': {
                    instanceReports = Reports.createInstance();
                    instanceGlobal.currentInstance = instanceReports;
                    break;
                }
                case 'process': {
                    instanceGlobal.currentInstance = ProcessEvents.createInstance();
                    break;
                }
                case 'constructor': {
                    instanceGlobal.currentInstance = Constructor.createInstance();
                    break;
                }
                default: {
                    instanceGlobal.currentInstance = instanceGlobal;
                    break;
                }
            }

            this.getInstance().setSubInstance(ViewType.getCurrentInstance());

            return this;
        },
        isContentOverflow : function () {
            var $wrapper = $('.list-view-panel .crm-table-wrapper');

            return $wrapper.length && ($wrapper.offset().top + $wrapper.height() > $(window).height()) ? true : false;
        },
        browser: {
            device: [],

            getDevice: function () {
                return this.device;
            },
            setDevice: function (device) {
                this.device = this.device || [];

                this.device.push(device);

                return this;
            },
            isIOS: function () {
                return $('body').is('.ios') ? true : false;
            },
            isMSIE: function () {
                var rv = -1;
                if (navigator.appName == 'Microsoft Internet Explorer')
                {
                    var ua = navigator.userAgent;
                    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                    if (re.exec(ua) != null)
                        rv = parseFloat( RegExp.$1 );
                }
                else if (navigator.appName == 'Netscape')
                {
                    var ua = navigator.userAgent;
                    var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
                    if (re.exec(ua) != null)
                        rv = parseFloat( RegExp.$1 );
                }
                return rv;
            },
            setParent : function () {
                var $body = $('body');
                switch (this.isMSIE()) {
                    case 9:
                    case 10:
                    case 11: {
                        $body.addClass('msie');

                        break
                    }
                    default:{
                        break;
                    }
                }
                this.setDevice(Constant.DEVICE_DESKTOP);

                if (navigator.userAgent.indexOf(' Firefox/') > -1) {
                    $body.addClass('firefox');

                }
                if (navigator.userAgent.indexOf(' Edge/') > -1) {
                    $body.addClass('browser-edge');

                } else
                if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
                    this.setDevice(navigator.userAgent.match(/(iPod|iPhone|iPad)/)[0]);
                    $body.addClass('ios');
                }
            },
            isFirefox: function() {
                return navigator.userAgent.indexOf(' Firefox/') > 0 ? true : false;
            }
        },
        //element for spinner
        spinner : {
            selector: '.b-spinner',
            get: function () {
                return $(this.selector);
            },
            clone: function () {
                return this.get().first().clone().removeClass('hide').attr('style', '');
            },
            remove : function ($element) {
                $element && $element.length ? $element.find(this.selector).remove() : '';
            }
        },
        actions: {
                    },
        // управление видимость блоков на поп-апах
        blockDisplaySwitch : function(object, callback){
            var $el = $(object).closest(".panel").children(".panel-body");
            if ($(object).hasClass("fa-chevron-down")) {
                $el.addClass('slided');
                $(object).removeClass("fa-chevron-down").addClass("fa-chevron-up");
                Global.setBlockDisplay($el, "fa-chevron-up");

                if(typeof(callback) == 'function') callback();
            } else {
                $(object).removeClass("fa-chevron-up").addClass("fa-chevron-down");
                Global.setBlockDisplay($el, "fa-chevron-down");
                if(typeof(callback) == 'function') callback();
                setTimeout(function() {
                    $el.removeClass('slided');
                }, 200);
            }
        },
        updateTab : function (){
            if ( $('.list-table_wrapper_all>div').is('.crm-table-wrapper') ){
                ListView.changeShadow();
                poliDot();
                clearInterval(window.tableInterval);
                shadowEnd();

                ListView.updateSDMField();
            }
        },
        fixSubstrateInModal : function(){
            var b = $('body');

            if (!b.find('.modal-backdrop.in').length) {
                b.append('<div class="modal-backdrop in"></div>');
                $('.modal-backdrop.in').on('click', function(){
                    $(this).remove();
                });
            }
        },
        // возвращает параметры родительского модуля ListView
        getLvParamsOnTheGroundParent : function(){
            var params = {};
            var pci = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id');
            var pdi = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id');
            if(typeof(pci) != 'undefined' && pci) params['pci'] = pci;
            if(typeof(pdi) != 'undefined' && pdi) params['pdi'] = pdi;

            return params;
        },
        createLinkByEV: function(editView, element) {
            var dropDown = editView.find('[data-type="panel"] [data-type="drop_down"]');
            var list = element ? element : dropDown;

            list.each(function(){
                var _this = $(this);
                var button = _this.find('[data-type="drop_down_button"]');
                var link = _this.find('.element[data-type="m-link"]');

                //if (link.length && button.data('id') && !button.attr('disabled')) {
                if (link.length && button.data('id')) {
                    link.removeClass('hide').show();
                }
            });
            return this;
        },
        clearRubbish : function(uploads_id, activity_messages_id){

            var ev = $('.edit-view:visible');
            var linked_card = null;
            if(!!ev.data('auto_new_card')) {
                linked_card = ev.data('auto_new_card');
                ev.data('auto_new_card', '');
            }

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setUrl(Global.urls.url_data_clear_rubbish)
                .setData({ 'uploads_id' : uploads_id, 'activity_messages_id' : activity_messages_id, 'linked_card' : linked_card})
                .setType('POST')
                .setDataType('text')
                .send();
        },

        initHandler : function () {
            if (window.backForward) {
                $('body').off('click');
            }
            $('body').on('click', function(e) {
                var editView = $('.edit-view:visible'),
                    $open = $('.open'),
                    data = $(this).data(),
                    $target = $(e.target);

                // :) disable event when it always enable
                if (data && data.eventClick == Global.DISABLE) {
                    data.eventClick = Global.ENABLE;
                    return;
                }

                if (!StartupGuide.getInstance()) {
                    delete window.backForward;
                }

                if (editView.find('.client-name .edit-dropdown.open').length || editView.closest('[data-module="process"]').length) {
                    EditView.setTitle(editView);
                } else {
                    EditView.setTitle($('.constructor'));
                }

                if (modalDialog.isOpen() && $('.edit-view:last').find('.contacts-block .edit-dropdown.open').length) {
                    if (!$(e.target).not('span.client-name').closest('.client-name').find('.open').length) {
                        EditView.saveContacts(e, true);
                    }
                }

                if (editView.length) {
                    // КОСТИЛЬ
                    if ($open.find('>.btn-ending').parent().has($target).length) {
                        return true;
                    }
                }

                // for report - fix
                if ($open.find('.sub-menu:not(.hide) .open').length && $('.edit-view.in[data-copy_id=8]').length) {
                    $open = $open.find('.open');
                }
                //========

                if ( typeof in_lite_lw == 'object' && $('tr.editing').not('.lot-edit').length) {
                    in_lite_lw = $('tr.editing .data_edit:first');
                }
                if (!$('.crm-dropdown').is(e.target)
                    //&& $('.crm-dropdown').has(e.target).length === 0
                    && $open.has(e.target).length === 0
                    && $('.datepicker-dropdown *').has(e.target).length === 0
                    && e.target.className !== 'month'
                    && e.target.className !== 'year'
                ) {
                    if($('.element[data-type="block_participant"] .element[data-type="select"]+.element[data-type="select"].open').length>0) {
                        Participant.remove();
                    };

                    $('.crm-dropdown').removeClass('open');
                }
            });
            return this;
        },

        responsiveNav : function(timerResize) {
            MainMenu.run();
        },
        initSelects : function(){
            $.each($('.select'), function () {
                $(this).selectpicker({
                    style: 'btn-white',
                    noneSelectedText: Message.translate_local('None selected')
                });
            })
            return this;
        },
        showParticipant : function () {
            $('.element[data-type="block_participant"] .crm-dropdown > .dropdown-toggle').removeAttr('data-toggle').on('click',function(){
                $('.element[data-type="block_participant"] .crm-dropdown.open').removeClass('open');
                $(this).parent().toggleClass('open');
            });
        },
        functionResizeMenu : function() {
            timerResizeMenu = setTimeout(function() {
                var menuCountRecive = null;
                var menuCounter = $('.horizontal-menu > .nav > li > a.navigation_module_link').length;
                var lStorage = new LocalStorage();
                lStorage
                    .clear()
                    .setAsync(true)
                    .setKey('menu_count').getValueFromServer(1, function(data){
                    var lStorage = new LocalStorage();

                    if (data) {
                        menuCountRecive = +data.count;
                        if (menuCounter !== menuCountRecive){
                            lStorage
                                .clear()
                                .setKey('menu_count')
                                .setValueToServer(1, {count:menuCounter}, function(data){});
                        }
                    } else {
                        lStorage
                            .clear()
                            .setKey('menu_count')
                            .setValueToServer(1, {count:menuCounter}, function(data){});
                    }
                });
            }, 2000);
            return this;
        },
        stopFunctionResizeMenu : function () {
            clearTimeout(timerResizeMenu);

            return this;
        },
        isSetModuleInMenu : function(copy_id){
            var result = false;
            var el = $('.element[data-type="module_menu"] a[data-copy_id="'+copy_id+'"]')
            if(typeof(el) != 'undefined' && el && el.length > 0){
                result = true;
            }

            return result;
        },
        blockErrors : {
            init : function () {
                $(document).on('change', '.column.b_error input[data-type="project_name"], .column.b_error .form-control[name*="EditViewModel"]', function () {
                    var _this = $(this),
                        block = _this.closest('.b_error');

                    if (_this.val().length) {
                        block.removeClass('b_error').find('.errorMessage').hide();
                    } else {
                        block.addClass('b_error').find('.errorMessage').show();
                    }
                });
            },
            verify: function () {
                $('.column.b_error').each(function(){
                    var _this = $(this);

                    if (_this.find('input.upload_file').val()) {
                        _this.find('.errorMessage').hide();
                    }
                });
            },
        },
        changeBackground: function() {
            if (crmParams.background) {
                $('body.with-bg').attr('style','background-image:url('+crmParams.background+')');
            } else {
                $('body.with-bg').removeAttr('style');
            }

        },
        showChildListEntities : function(_this){
            var action_key,
                preloader = MainMenu.getPreloader(),
                $this = $(this),
                instanceContent = ContentReload.createInstance(),
                object = instanceReports || ProcessEvents.getInstance();

            Global
                .getInstance()
                .setPreloader(preloader)
                .setContentReloadInstance(instanceContent);

            iPreloader.implements.call(instanceContent);

            instanceContent
                .reDefinition()
                .setPreloader(preloader)
                .showPreloader();

            if (object && object.open) {
                object.open(_this);
                return;
            }

            action_key = $this.data('action_key');

            instanceContent
                .clear()
                .setObject(_this)
                .setActionKey(action_key)
                .prepareVariablesAuto()
                .run();
        },

        /*****************************************
         Работа с файлами
         **********************************/
        Files : {
            showGoogleDocView : function(data){
                $.post(Global.urls.url_upload_show_google_doc_view, data, function(data){
                    modalDialog.show(data);
                })
            },


            uploadUrlLink : function(_data, callback){
                $.ajax({
                    url: Global.urls.url_upload_url_link,
                    data: _data,
                    type: "POST", dataType: 'json',
                    success: function(data){
                        if(data.status == false){
                            Message.show(data.messages, false);
                        } else {
                            callback(data)
                        }
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                    }
                });

            },


            fileDelete : function(id, beRelateKey, cb){
                $.ajax({
                    url : Global.urls.url_upload_delete_file,
                    data : { 'id' : id, 'be_relate_key' : beRelateKey},
                    type : 'POST', async: false, dataType: "json",
                    success: function(data){
                        cb(data);
                    },
                    error : function(){
                        cb({status : false, messages : [Global.urls.url_ajax_error]});
                    }
                });
            },

            fileDeleteAvatar : function(cb){
                $.ajax({
                    url : Global.urls.url_upload_delete_file_avatar,
                    type : 'POST', async: false, dataType: "json",
                    success: function(data){
                        cb(data);
                    },
                    error : function(){
                        cb({status : false, messages : [Global.urls.url_ajax_error]});
                    }
                });
            },
        },



        finishedObject : {
            createInstance : function(){
                var Obj = function(){
                    for(var key in Global.finishedObject){
                        this[key] = Global.finishedObject[key];
                    }

                    iModule.implements.call(this);
                }

                return new Obj();
            },
            deleteFromUserStorage : function(callback){
                var copy_id = $('.sm_extension').data('copy_id');
                //var pci = $('.sm_extension').data('parent_copy_id');
                //var pdi = $('.sm_extension').data('parent_data_id');

                var lStorage = new LocalStorage();

                lStorage
                    .clear()
                    .setKey('finished_object')
                    //.setPci(pci)
                    //.setPdi(pdi)
                    .deleteFromServer(copy_id, function(){callback()});
            },

            setValueToUserStorage : function(callback){
                var copy_id = $('.sm_extension').data('copy_id') || this.copy_id;

                var lStorage = new LocalStorage();

                lStorage
                    .clear()
                    .setKey('finished_object')
                    //.setPci(pci)
                    //.setPdi(pdi)
                    .setValueToServer(copy_id, {"finished_object" : "1"}, function(data){ callback(data) });
            },

            switchData : function(_this){
                var action_key, time,
                    object = this,
                    active = $(_this).hasClass('active'),
                    subInstance = Global.getInstance().getSubInstance();

                action_key = $(_this).data('action_key');

                /*if (subInstance && $.inArray(subInstance._type, ['CalendarView']) >= 0) {
                    CalendarView.runFinishedObject($(_this));

                    return;
                }*/

                instanceGlobal.preloaderShow($(_this));

                time = setTimeout(function(){
                    var $callback,
                        contentInstance = ContentReload.createInstance().clear();

                    clearTimeout(time);

                    Global.getInstance()
                        .setContentReloadInstance(contentInstance)
                        .setSwitchFinishedObject(active);

                    var handler = function() {
                        contentInstance
                            .setObject(_this)
                            .setActionKey(action_key)
                            .prepareVariablesAuto()
                            .setCallBackSuccessComplete($.isFunction($callback) ? $callback : null)
                            .run();
                    }

                    if (active) {
                        object.deleteFromUserStorage(handler)
                    } else {
                        object.setValueToUserStorage(handler);
                    }
                }, 50);
            },
        },



        Plugins : {
            executeToServer : function(action, _data, callback){
                var _action = '';
                switch(action){
                    case 'change' :
                        _action = 'plugins-change';
                        break;
                    case 'save' :
                        _action = 'plugins-save';
                        break;
                    case 'cancel' :
                        _action = 'plugins-cancel';
                        break;
                }

                $.ajax({
                    url: _action,
                    data: _data,
                    type: "POST", dataType: 'json',
                    success: function(data){
                        if(data.status == 'error' && data.messages){
                            Message.show(data.messages, false);
                        }
                        callback(data)
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message':Global.urls.url_ajax_error}], true);
                    }
                });
            },


            BlockParams : {
                getAttributes : function(_this){
                    var attributes = {}

                    $(_this).find('.element[data-type="service_params"] .element[data-service_params="1"]').each(function(i, ul){
                        attributes[$(ul).data('type')] = $(ul).val();
                    })

                    return attributes;
                },

                change : function(_this){
                    _this = $(_this).closest('.element[data-type="block"]');
                    var _data = {
                        'source_name' : _this.data('source_name'),
                        'service_name' : _this.find('.element[data-type="service_name"]').val(),
                    }

                    Global.Plugins.executeToServer('change', _data, function(data){
                        if(data.status == true){
                            $(_this).find('.element[data-type="service_params"]').html(data.html);
                        }
                    });
                },

                save : function(_this){
                    var attributes = [];
                    $(_this).closest('.element[data-page="plugins"]').find('.element[data-type="block"]').each(function(i, ul){
                        var service_data = {
                            'source_name' : $(ul).data('source_name'),
                            'service_name' : $(ul).find('.element[data-type="service_name"]').val(),
                            'attributes' : Global.Plugins.BlockParams.getAttributes($(ul))
                        };
                        attributes.push(service_data);
                    });

                    $(_this).attr('disabled', 'disabled');

                    Global.Plugins.executeToServer('save', {'attributes' : attributes}, function(data){
                        $(_this).closest('.element[data-page="plugins"]').find('.errorMessage').each(function(i, ul){
                            $(ul).text('');
                        })

                        $.each(data, function(i, vars){
                            $(_this)
                                .closest('.element[data-page="plugins"]')
                                .find('.element[data-type="block"][data-source_name="'+vars.source_name+'"] .element[data-type="service_params"]')
                                .html(vars.html);
                        })
                        $(_this).removeAttr('disabled');

                        QuickViewPanel.updateContent();
                    });
                },

                cancel : function(_this){
                    Global.Plugins.executeToServer('cancel', null, function(data){
                        if(data.status == true){
                            var html = $(data.html).find('.content_form').html();
                            $(_this).closest('.element[data-page="plugins"]').find('.content_form').html(html);
                            Global.initSelects();
                        }
                    });
                },
            }
        }, // Plugins

        Forms : {
            saveAjax : function(_this){
                var _form = $(_this).closest('.content_form').find('form');
                $(_this).attr('disabled', 'disabled');

                var ajax = new Ajax();
                ajax
                    .setAsync(true)
                    .setData(_form.serialize())
                    .setUrl(_form.attr('action'))
                    .setType(_form.attr('method'))
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status){
                                setTimeout(function(){
                                    $(_form.data('selector_content_box')).html(instanceGlobal.contentReload.getPreparedContent(data.content_html));
                                }, 500)
                            } else {
                                Message.show(data.messages, false);
                            }
                        }
                    })
                    .setCallBackComplete(function(){
                        setTimeout(function(){
                            $(_this).removeAttr('disabled');
                        }, 500)
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();
            },

            cancel : function(_this){
                $(_this).closest('.content_form').find('form').trigger('reset');
            }

        }



    }


    var UI = {
        setSelect: function ($object, value, dataContent) {
            var $button = $object.next();
            $object.find('option').removeAttr('selected');
            $object.val(value);
            var $currentOption = $object.find('option[value="'+value+'"]').prop('selected', true);
            var text = $currentOption.text();

            // use when bStatus
            if (dataContent && $button && $button.length) {
                var code = $currentOption.attr(dataContent);
                text = $object.find('option[value="'+value+'"]').text();
                $button.attr('title', text).find('.filter-option').html(code);
            }

            if ($button && $button.length) {
                $button.attr('title', text);

                if (!dataContent) {
                    $button.find('.filter-option').text(text);
                }
                $button.find('.dropdown-menu li').removeClass('selected');
                $button.find('.dropdown-menu li').eq($currentOption.index()).addClass('selected');
            }
        },
        setDataTimeBlock: function ($root, options) {
            /*
            * options = {
            *   all_day: 0
            *   date_time: "29.11.2019 01:05"
            * }
            * */
            $root.find('>label input')
                .data('all_day', Number(options.all_day))
                .attr('data-all_day', options.all_day).val(options.date_time);
            $root.find('#ckbAllDay').prop('checked', Number(options.all_day) ? true : false);
            $root.find('.element[data-type="calendar-place"]').removeData('datepicker');

            return this;
        }
    }

    var Console = {
        log: function (message, data) {
            if (!Environments.isProductionMode()) {

                if (data) {
                    console.log(message, data)
                } else  {
                    console.log(message);
                }
            }
        }
    }


    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in Global) {
        _self[key] = Global[key];
    }

    exports.UI = UI;
    exports.Console = Console;
    exports.Global = Global;
    exports.iGlobal = iGlobal;
    exports.iStore = iStore;
    exports.iDraft = iDraft;
    exports.ContentReload = ContentReload;
    exports.iTimeStamp = iTimeStamp;
    exports.iTemplate = iTemplate;
    exports.iCommon = iCommon;
    exports.ViewType = ViewType;
    exports.iModule = iModule;
    exports.iAction = iAction;
    exports.iQueue = iQueue;
    exports.iLifeCycle = iLifeCycle;
})(window);


var eventPath;
    instanceGlobal = null; // екземпляр

var _Global = function () {
        this.type = 'global';
        this.getFilter = typeof Filter == 'undefined' ? {} : Filter;
    }

//_Global.prototype = Object.create(Global);
_Global.prototype = Object.create(Base);

_Global.prototype.preloaderShow = function ($this, params) {
    var $placeForSpinner, $object, $spinner, checkStateHeightOfContainer,
        $listTableParent = $('[id="list-table_wrapper_all"]'),
        $container = $('#container'),
        $bpmBlock = $('.bpm_block'),
        $listView = $('.list_view_block'),
        $body = $('body'),
        $editableBlock = $('.editable-table'),
        $crmTableWrapper = $listTableParent.find('.crm-table-wrapper');

    this.contentReload._timer.begin =  moment().unix();

    checkStateHeightOfContainer = function () {
        if ($crmTableWrapper.length) {
            if ($crmTableWrapper.offset().top + $crmTableWrapper.height() > $(window).height()) {
                $($listTableParent).removeClass('init-preloader');
                $($placeForSpinner).removeClass('init-preloader');
            }
        }
    };

    //clear old styles
    NiceScroll.clear($('.list_view_block .crm-table-wrapper'));

    $crmTableWrapper.addClass('overflowXh');

    if ($this.closest('#list-table_paginate').length
        || $this.is('.pagination_size')) {
        //detected pagiantion
        $object = $listTableParent;

        if ($listTableParent.height() >= $(window).height()) {
            $object = $container;
            $object.addClass('transparent list-view-hide');
        }
        $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
        Global.spinner.remove($placeForSpinner);
    }

    if ($this.is('.filter-btn-delete')) {
        $placeForSpinner = $object = $editableBlock.addClass('init-preloader center-position');
    } else
    if ($this.closest('[data-type="module_menu"]').length
        || $this.closest('.logout').length
        || (params && params.settings) // detect guide run
        || $this.closest('.tasks-bar').length) { // detected tasks
        // detected top menu
        $object = $container.addClass('reload-page');
        $('.widget-left').addClass('hide');
        ProcessView.destroy();
        Global.spinner.get().not(':first').remove();
    }

    if (params && params.settings) {
        $body.addClass('hide-edit-view');
    }

    if ($this.is('[data-type="roles_menu"]')) {
        $placeForSpinner = $object = $listTableParent.addClass('roles-menu init-preloader center-position');
    }

    if ($('.widget-left:visible').length) {
        //constructor
        //detected copies module
        if ($this.is('.btn-action[data-controller="module_copy"]')) {
            $placeForSpinner = $object = $listTableParent.addClass('init-preloader constructor__copies-modules');
        }
    }
    if ($this.is('.edit_view_select_btn-create[data-type="process"]')) {
        $object = $container.addClass('reload-page');
    }

    if ($this.is('.edit_view_btn-save')
        || $this.is('.edit_view_btn-delete') && Global.isReport()
        || $this.is('.edit_view_btn-copy') && Global.isReport() ) {
        if (Base.isLVProcess()) {
            $object = $container.addClass('reload-page');
        } else
        // detect save edit-view
        if ($listView.length) {
            $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $listTableParent.addClass('init-preloader center-position');
            Global.spinner.get().not(':first').remove();
        }
    }

    if ($this.closest('.right-side-accordion').length) {
        $placeForSpinner = $object = $container.addClass('reload-page');
    } else
    if ($this.is('.bpm_block')) {
        if (params.hide) {
            $object = $container.addClass('reload-page');
        }
    } else
    if ($this.closest('.notice_navigation_link').length) {
        $object = $container.addClass('reload-page');
        $this.closest('.open').removeClass('open');
    } else if (params && params.goto == 'back') {
        $placeForSpinner = $object = $container.addClass('reload-page');
    } else if ($this.is('.sorting-arrows')) {
        // detect sorting in lv
        $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
        Global.spinner.remove($placeForSpinner);
        checkStateHeightOfContainer();
    } else if (Global.isReport()) {
        if (params && params.status == 'create') {
            if ($listTableParent.height() >= $(window).height()) {
                $object = $container;
                $object.addClass('transparent list-view-hide');
            }
            $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
            Global.spinner.remove($placeForSpinner);
        } else if ($this.closest('[data-type="reports_menu"]').length) {         // detect REPORTS edit-view save
            $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $container.addClass('center-position reports-content');
        } else if ($this.is('.search-filter')){
            $placeForSpinner = $object = $container.addClass('center-position reports-filter-in');
        } else if ($this.is('.edit_view_report_constructor_btn-save')) {
            $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $listTableParent.addClass('center-position init-preloader');
        }
        /**Reports===========*/
    }  else
        if ($bpmBlock.length) {
        /**BPM Block****************/
        // detect BPM edit-view save
        if ($this.is('.edit_view_btn-save')
            || $this.closest('.b_bpm').length
            || $this.is('.edit_view_select_btn-create')) { // detect click change process in BPM
             $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $container.addClass('center-position');
        }
        /**BPM Block===============*/
    } else if (Global.isProcessView()) {
        // PV
        if ($this.closest('.fields-view-settings').length
            || $this.is('.filter-btn-delete')) {
            $placeForSpinner = $object = $('.process_wrapper').addClass('init-preloader center-position');
            Global.spinner.remove($placeForSpinner);
        } else if ($this.closest('[data-type="project_menu"]').length) {
            $object = $container;
            $body.addClass('with-table');
        } else if ($this.closest('.todo-action-bar').length && !$this.is('.edit_view_dnt-add')) {
            // detected copy/ remove btn
            $placeForSpinner = $object = $this.closest('li').addClass('block-reload').find('.slimscrolldiv').addClass('init-preloader center-position');
            // if (!$placeForSpinner.find('.slimscrolldiv ul li').length == 1) {
            //     $placeForSpinner.addClass('first-task');
            // }
            if ($placeForSpinner.find('.slimscrolldiv ul li').length == 1) {
                $placeForSpinner.addClass('first-task');
            }
            Global.spinner.remove($placeForSpinner);
        } else if ($this.is('.search-filter')
                   || $this.is('[data-controller="process_view_edit"]')) {
            $object = $container;
            $body.addClass('with-table');
        } else if ($this.is('.edit_view_btn-save') && params && params.$) {
            // detect save edit-view
            $placeForSpinner = $object = params.$.find('.panel-body .slimscrolldiv').addClass('init-preloader center-position');
            Global.spinner.remove($placeForSpinner);
        } else if ($this.is('[id="content_container"]') && params && params.status == 'create' && ProcessView.getInstance().$panel_change) {
            //pv create new card
            var $section = ProcessView.getInstance().$panel_change;
            $placeForSpinner = $object = $section.find('.slimscrolldiv').parent().addClass('init-preloader center-position');
            $section.closest('li').addClass('block-reload');
            if (!$placeForSpinner.find('.slimscrolldiv ul li').length) {
                $placeForSpinner.addClass('first-task');
            }
            Global.spinner.remove($placeForSpinner);
        } else if ($this.is('[id="content_container"]') && params && params.status == 'create') {
            //pv from notification
            $object = $container;
            $body.addClass('with-table');
        } else if (params && params.project) {
            $object = $container;
            $body.addClass('with-table');
        }
    } else if ($listView.length) {
        /*List View*/
        if ($this.closest('#list-table_paginate').length) {
            //detected pagiantion
            $object = $listTableParent;

            if ($listTableParent.height() >= $(window).height()) {
                $object = $container;
                $object.addClass('transparent list-view-hide');
            }
            $object = $('.editable-table').addClass('init-preloader center-position');
            $placeForSpinner = $object;
        } else if (params && params.project) {
            $object = $container;
            $body.addClass('with-table');
        } else if ($this.is('.search-filter') ||
                $this.is('.pagination_size') //detected pagination set count visible of records
        ) {
            $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');

        } else if ($this.is('.list_view_btn-copy') || $this.is('.list_view_btn-delete')) {
            //detect copy / remove in lv
            $placeForSpinner = $object = $crmTableWrapper.addClass('init-preloader center-position');
        } else if ($this.is('[data-type="finished_object"]') || ($this.is('[data-type="template"]') )) {
            $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
        } else if ($this.is('[id="file_import_data"]')) { // detect import file
            $placeForSpinner = $object = $listTableParent.addClass('init-preloader center-position');
        } else if ($this.is('.edit_view_btn-copy') || $this.is('.edit_view_btn-delete')) { // detect copy/REMOVE in ev
            $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $listTableParent.addClass('init-preloader center-position');
        } else if ($this.closest('[data-type="lot-edit"]').length) { // detect lot edit
            $body.addClass('hide-edit-view');
            $placeForSpinner = $object = $listTableParent.addClass('init-preloader center-position');
        } else if ($this.filter('.navigation_module_link_child').closest('.copy_id8').length) {
            // detect open reports
            $object = $container.addClass('reload-page');
        } else if ($this.is('[id="content_container"]') && params && params.status == 'create') {
            //report new card
            $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
            Global.spinner.remove($placeForSpinner);
        } else if ($this.is('.navigation_module_link_child')) {
            $object = $container.addClass('reload-page');
        }

        $placeForSpinner && $placeForSpinner.length ? Global.spinner.remove($placeForSpinner) : '';
        checkStateHeightOfContainer();
    }

    if ($this.is('.filter')) {
        $placeForSpinner = $object = $this.addClass('init-preloader center-position');
        Global.spinner.remove($placeForSpinner);
    }

    // detected filter
    if ($this.is('.filter-btn-set')
        || $this.is('.filter-btn-take-off')
        || $this.is('.filter-btn-save')) {
        $placeForSpinner = $object = $container.addClass('lv-pv-inner-hide-content center-position');

        if ($this.closest('.process_view_block').length) {
            $body.addClass('with-table');
        } else { //list-view
            $placeForSpinner = $object = $('.editable-table').addClass('init-preloader center-position');
        }

        $placeForSpinner && $placeForSpinner.length ? Global.spinner.get().not(':first').remove() : '';
        checkStateHeightOfContainer();
    } else if (params && params['edit-view']) {
        // detected edit-view
        $placeForSpinner = $object = $('.fake-modal');
    }
    // else if ($this.is('.filter-btn-save')) {
    //     // detected filter save
    //     if ($this.closest('.process_view_block').length) {
    //         $body.addClass('with-table');
    //         $placeForSpinner = $object = $container.addClass('reload-page');
    //     } else { //list-view
    //         $placeForSpinner = $object = $listTableParent.addClass('init-preloader center-position');
    //     }
    //}
    else if ($this.is('.element[data-type="personal_information_save"]') || $this.is('[data-type="notification_settings_save"]') || $this.is('[data-type="api_save"]')) {
        // detected btn save on profesional info
        $placeForSpinner = $object = $('.profile_form:visible').addClass('init-preloader');
    } else if ($this.closest('.brand').length) {
        // detected brand
        $placeForSpinner = $object = $container.addClass('reload-page');
    } else if ($this.closest('.wievs_tuggle').length
        || ($this.is('[data-type="template"]') && !$listTableParent.length)
        || $this.is('[data-name="process_view_fields_group"]')
        || $this.is('[data-type="finished_object"]') && !$listTableParent.length) {
        // detected button finished
        $object = $container;
        $body.addClass('with-table');
    } else if ($this.closest('[data-name="process_view_fields_group"]').length) {
        // detected button sorting
        $object = $container;
        $body.addClass('with-table');
    } else if ($this.closest('.widget-left').length || $this.closest('.list_view_block[data-copy_id="4"]').length) {
        $object = $container.addClass('reload-page page-settings-with-menu');
    }

    if (params && params.visible && $object) {
        $object.addClass('visible');
    }

    /*GLOBAL*/
    if (!$object) return this;

    $object.addClass('set-preloader');

    $spinner = Global.spinner.get().first().removeClass('hide');
    //set preloader
    if ($placeForSpinner && $placeForSpinner.length && !$object.is('[id=container]')
        && !$placeForSpinner.find($spinner.selelctor).length) {
        $placeForSpinner.append($spinner.clone().attr('style','').removeClass('default'));
    } else {
        if ($placeForSpinner && $placeForSpinner.find(Global.spinner.selector).length) {}
        else $spinner.removeClass('default');
    }
    this.contentReload.preloaderClasses = [
        'set-preloader',
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
        'lv-pv-inner-hide-content'];

    var offset,
        $wrapper = $('.wrapper'),
        $element = $placeForSpinner && $placeForSpinner.find(Global.spinner.selector);

    $wrapper.addClass('static');
    offset = $wrapper.width()/2;
    $wrapper.removeClass('static');

    if ($crmTableWrapper.length) {
        if ($crmTableWrapper.offset().top + $crmTableWrapper.height() > $(window).height()
            && $element && $element.parent().is('[id="container"]')) {
            offset += 17;
        }
    }

    if (!$element) {
        $element = $object.find('>.b-spinner').removeClass('hide');
    }

    if ($element && $element.css('position') == 'fixed' ) {
        if ($element.parent().is('[id="container"]')) {
            $element.css('left', offset);
        } else {
            if (!$('[data-type="left_menu"]:visible').length) {
                $element.remove();
                $container.addClass('set-preloader common-spinner');
                $container.find('>.b-spinner').css({
                    left: $('#main-content').width() / 2
                })
            } else {
                //constructor
                if ($('[data-type="left_menu"]:visible').length) {
                    var $place = $('.set-preloader');

                    $element.css({
                        left: ($place.width()/2) + $place.offset().left
                    })
                }
            }
        }
    }
    return this;
}

// Pages interface types
const PAGE_IT_DEFAULT        = 'default';
const PAGE_IT_CONSTRUCTOR    = 'constructor';
const PAGE_IT_REPORTS        = 'reports';
const PAGE_IT_PROCESS        = 'process';
const PAGE_IT_COMMUNICATIONS = 'communication';
const PAGE_IT_CALLS          = 'calls';

// Page name
const PAGE_NAME_DEFAULT         = 'default';
const PAGE_NAME_LIST_VIEW       = 'list_view';
const PAGE_NAME_PROCESS_VIEW    = 'process_view';


_Global.prototype.createOfInstances = function(page_interface_type, page_name) {
    var globalInstance = Global.getInstance();

    if(typeof(page_interface_type) == 'undefined' || page_interface_type == false){
        page_interface_type = crmParams.page_interface_type;
    } else {
        crmParams.page_interface_type = page_interface_type;
        page_interface_type = crmParams.page_interface_type;
    }

    if(typeof(page_name) == 'undefined' || page_name == false){
        page_name = crmParams.page_name;
    } else {
        crmParams.page_name = page_name;
        page_name = crmParams.page_name;
    }

    if (instanceGlobal.currentInstance && page_interface_type != instanceGlobal.currentInstance.type){
        if (typeof instanceGlobal.currentInstance.remove == 'function'
            && page_interface_type != PAGE_IT_REPORTS) {
            instanceGlobal.currentInstance.remove();
        }

        if (instanceGlobal.subInstance && instanceGlobal.subInstance.remove) {
            instanceGlobal.subInstance.remove();
        }
        instanceGlobal.currentInstance = null;
        instanceGlobal.subInstance = null;
    }

    instanceReports = null;
    Constructor.destroy();

    //TODO: optimized;
    var json = Url.parseFull();
    if ($.inArray(json.controller, [CalendarView._type]) != 0) {
        CalendarView.destroy();
    }

    switch (page_interface_type) {
        case PAGE_IT_CONSTRUCTOR :
            instanceGlobal.currentInstance = Constructor.createInstance();

            globalInstance.setCurrentInstance(instanceGlobal.currentInstance);
            break;

        case PAGE_IT_REPORTS :
            instanceReports = Reports.createInstance();
            instanceGlobal.currentInstance = instanceReports;

            globalInstance.setCurrentInstance(instanceGlobal.currentInstance);
            break;

        case PAGE_IT_PROCESS :
            instanceGlobal.currentInstance = ProcessEvents.createInstance();

            globalInstance.setCurrentInstance(instanceGlobal.currentInstance);
            break;

        case PAGE_IT_COMMUNICATIONS :
            instanceGlobal.currentInstance = Communication.getInstance();

            break;

        case PAGE_IT_CALLS :
            instanceGlobal.currentInstance = Calls.getInstance();

            break;

        case PAGE_IT_DEFAULT :
            ProcessEvents.destroy();
            globalInstance.setSubInstance(null);

            var instance = ProcessView.getInstance();
            if (!ProcessView.checkOrDestroy() && instance) {
                globalInstance.setSubInstance(instance);
            }

            break;
    }

    if(page_name == PAGE_NAME_PROCESS_VIEW){
        globalInstance.setSubInstance(ProcessView.getInstance(true));
    }

    $('.wrapper').attr('style',''); //TODO: optimized move to

    if(!instanceGlobal.currentInstance){
        instanceGlobal.currentInstance = Base.copyObject(instanceGlobal);
    }
}



/*
// Content reload Actions run
const CR_ACTION_RUN_LOAD_MODULE     = 'loadModule';
const CR_ACTION_RUN_LOAD_PAGE       = 'loadPage';
const CR_ACTION_RUN_LOAD_TO_LINK    = 'loadToLink';
*/

/*
// Content reload Actions after
const CR_ACTION_AFTER_SHOW_LEFT_MENU  = 'actionShowLeftMenu';
const CR_ACTION_AFTER_HIDE_LEFT_MENU  = 'actionHideLeftMenu';
const CR_ACTION_AFTER_SHOW_EDIT_VIEW  = 'actionShowEditView';
*/


_Global.prototype.contentReload = ContentReload;

/*------------------------------------------------------*/
/*--   Ajax                           
/*------------------------------------------------------*/

// constructor
var Ajax = function(){
    for(var key in AjaxObj) {
        this[key] = AjaxObj[key];
    }
    this.ajax = $.ajax;
}


// object
AjaxObj = {
    url : '',
    data : {},
    type : 'POST',
    async : false,
    time_out : 0,
    data_type : 'json',
    content_type : 'application/x-www-form-urlencoded',
    callback_beforesend : null,
    callback_success : null,
    callback_error : null,
    callback_done : null,
    callback_complete : null,

    _list_callback_later: {}, // Only static
    _instance: null,

    createInstance : function(){
        var Obj = function(){
            for(var key in AjaxObj) {
                this[key] = AjaxObj[key];
            }
            this.ajax = $.ajax;
        }

        return AjaxObj._instance = new Obj();
    },

    setUrl : function(url){
        this.url = url;
        return this;
    },
    setData : function(data){
        this.data = data;
        return this;
    },
    setType : function(type){
        this.type = type;
        return this;
    },
    setAsync : function(async){
        this.async = async;
        return this;
    },
    setDataType : function(data_type){
        this.data_type = data_type;
        return this;
    },
    setHeaders : function(object){
        this.headers = object;
        return this;
    },
    abort : function () {
        this.ajax().abort();
        return this;
    },
    setTimeOut : function(time_out){
        this.time_out = time_out;
        return this;
    },
    setContentType : function(content_type){
        this.content_type = content_type;
        return this;
    },
    setCallBackBeforeSend : function(callback){
        this.callback_beforesend = callback;
        return this;
    },
    setCallBackSuccess : function(callback){
        this.callback_success = callback;
        return this;
    },
    setCallBackError : function(callback){
        this.callback_error = callback;
        return this;
    },
    setCallBackComplete : function(callback){
        this.callback_complete = callback;
        return this;
    },
    setCallBackDone : function(callback){
        this.callback_done = callback;
        return this;
    },
    send : function(){
        var _this = this;

        this.xhr = this.ajax({
            'url' : _this.url,
            'data' : _this.data,
            'type' : _this.type,
            'async': _this.async,
            'headers' : _this.headers,
            'dataType': _this.data_type,
            'timeout': _this.time_out,
            contentType: _this.content_type,
            before : function(jqXHR, settings){
                if(typeof(_this.callback_beforesend) == 'function'){
                    _this.callback_beforesend()
                }
            },
            success: function(data){
                if (data && data.user_logout == true) {
                    document.location.href = location.origin+'/logout';
                    return;
                }

                if(typeof(_this.callback_success) == 'function'){
                    _this.callback_success(data)
                }
            },
            error : function(jqXHR, textStatus, errorThrown){
                if(typeof(_this.callback_error) == 'function'){
                    _this.callback_error(jqXHR, textStatus, errorThrown)
                }
            },
            complete : function(jqXHR, textStatus){
                if(typeof(_this.callback_complete) == 'function'){
                    _this.callback_complete()
                }

                Events.runHandler(Events.TYPE_AJAX_COMPLETE);
            }
        }).done(function(){
                if(typeof(_this.callback_done) == 'function'){
                    _this.callback_done()    
                }
        });
    },   
};

$(document).on('click', '.navigation_card_link, .navigation_module_data_link', function (e) {
    e.stopPropagation();
    var copy_id = $(this).data('copy_id');
    var data_id = $(this).data('id');

    if (copy_id && data_id) {
        History.getViaParent(copy_id, data_id, function (url) {
            window.location.href = url;
        });
    }
});

var TodoList = {

    _todo_list:  {
        todo : null,
        edit_view: null
    },

    isTodoElement: function () {
        return this._todo_list.todo ? true : false;
    },
    _get : function (_this) {

        var edit_view = $(_this).closest('.edit-view');

        if(edit_view.length){
            TodoList._todo_list.todo = edit_view.find('#todo_list');
            TodoList._todo_list.edit_view = edit_view;
        } else {
            TodoList._todo_list.todo = $(_this).closest('.sm_extension_data.editing').find('#todo_list');
        }
    },

    _refresh : function () {
        if(TodoList._todo_list.todo) {
            if ($('tr.editing').data('open') != true) {
                TodoList._todo_list.todo.val([]);
            }
            TodoList._todo_list.todo.selectpicker('refresh');
        }
    },

    clear : function (_this){
        TodoList._get(_this);
        TodoList._todo_list.todo.find('option').remove();
        TodoList._todo_list.todo.attr('title', Message.translate_local('ToDo lists are not created'));

        TodoList._refresh();
    },

    rebuild : function(_this, callback, callback1){
        var $list = $([]);

        TodoList._get(_this);

        var url = Global.urls.url_todo_list;

        if(url && TodoList._todo_list.todo) {
            var _data = {
                id: null,
                pci: $(_this).closest('.submodule-link').find('.element_relate').data('relate_copy_id'),
                pdi: $(_this).data('id'),
                finished_object : 0
            };

            //edit_view
            if(TodoList._todo_list.edit_view){
                var copy_id = TodoList._todo_list.edit_view.data('copy_id');
                _data['this_template'] = TodoList._todo_list.edit_view.data('this_template');
                _data['id'] = TodoList._todo_list.edit_view.data('id');

                var status_btn = TodoList._todo_list.edit_view.find('.element[data-type="block_button"] select#b_status')
                if(typeof status_btn != 'undefined' && status_btn){
                    var b_status = status_btn.val();
                    if(b_status === "1"){
                        _data.finished_object = 1;
                    }
                }
            // inline edit
            } else {
                var copy_id = $(_this).closest('.sm_extension').data('copy_id');
                _data['this_template'] = $(_this).closest('.sm_extension').data('this_template');
                _data['id'] = $(_this).closest('.sm_extension_data[data-controller="edit_view"]').data('id');

                var status_btn = $(_this).closest('.sm_extension_data[data-controller="edit_view"]').find('td.data_edit select#b_status')
                if(typeof status_btn != 'undefined' && status_btn){
                    var b_status = status_btn.val();
                    if(b_status === "1"){
                        _data.finished_object = 1;
                    }
                }
            }

            $.ajax({
                url: url + '/' + copy_id,
                data: _data,
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    if(data){
                        TodoList.clear(_this);
                        if(data.select_list && $.isEmptyObject(data.select_list) == false){
                            TodoList._todo_list.todo.removeAttr('title');
                            $.each(data.select_list, function(id, title){
                                TodoList._todo_list.todo.prepend($("<option></option>")
                                    .attr('value', id)
                                    .text(title)
                                );
                            });

                            if (callback) {
                                callback(data)
                            }
                            this.label = true;
                        }
                    } else {
                        TodoList.clear(_this);
                    }

                    if(TodoList._todo_list.todo.is(':disabled')){
                        TodoList._todo_list.todo.removeProp('disabled');
                    }

                    TodoList._refresh();

                    if (this.label && callback1) {
                        callback1();
                    }
                },

                error: function () {
                    TodoList.clear(_this);
                }

            });
        }

    },

    setValue : function(value){
        TodoList._todo_list.todo.val(value);

        return this;
    }

};

/*------------------------------------------------------*/
/*--         notice navigation                        --*/
/*------------------------------------------------------*/

$(document).on('click', '#header_notification_bar .notice_navigation_link, .prof_activity .notice_navigation_link', function(e){
    e.stopPropagation();
    if ($(e.target).is('.navigation_message_notice_ro')) return;

    HeaderNotice.onClickNavigationLink($(this));
});



$(document).on('click', '#header_notification_bar .element[data-type="notice_set_read"]', function(){
    HeaderNotice.setReadAll();
});





$(document).on('click', '#header_notification_bar .navigation_card_link, #header_notification_bar .navigation_module_data_link', function (e) {
    e.stopPropagation();
    var copy_id = $(this).data('copy_id');
    var data_id = $(this).data('id');

    if (copy_id && data_id) {
        History.getViaParent(copy_id, data_id, function (url) {
            window.location.href = url;
        });
    }
});









/*------------------------------------------------------*/
/*--         localStorage                             --*/
/*------------------------------------------------------*/
function isLocalStorageAvailable() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}

function writeStorage(key, value){
    if(isLocalStorageAvailable) localStorage.setItem(key, value);
}

function readStorage(key){
    if(isLocalStorageAvailable) return localStorage.getItem(key);
}

function removeStorage(key){
    if(isLocalStorageAvailable) return localStorage.removeItem(key);
}





String.prototype.format = function() {
    var formatted = this;
    if($.isEmptyObject(arguments[0])) return this;
    $.each(arguments[0], function(key, value){
        formatted = formatted.replace("{" + key + "}", value);
    })
    return formatted;
};

//LocalStrorage function  table clear
var deleteTableStatus = function(tablesId) {
    if (typeof(tablesId) == 'string') {
        removeStorage('listview-id:' + parseInt(tablesId));
    } else {
        for (i = 0; i < tablesId.length; i++) {
            removeStorage('listview-id:' + tablesId[i]);
        }
    }
};










/*------------------------------------------------------*/
/*--         OTHER                                    --*/
/*------------------------------------------------------*/


var timerResizeMenu;

var toPrint = function(url){
    window.open(url, '_blank');
}


var getModuleUrl = function(copy_id, destination, module_dependent_params, callback){
    var this_template = $('.list_view_block, .process_view_block').data('this_template');
    var params = {'copy_id' : copy_id, 'destination' : destination, 'params' : {'this_template' : this_template}};

    if($.isEmptyObject(module_dependent_params) && module_dependent_params !== false){
        module_dependent_params = {};
        var pci = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id');
        var pdi = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id');
        if(pci) module_dependent_params['pci'] = pci;
        if(pdi) module_dependent_params['pdi'] = pdi;
    }
    if(module_dependent_params)
    $.each(module_dependent_params, function(key, value){
        params.params[key] = value;
    });

    var send = function(send_more) {
        if (!crmParams.global.ajax) {
            var data = JSON.parse(localStorage.getItem('loadParams'));

            crmParams.global = data.global;
            Message.list = data.list;
            Message.locale = data.locale;
            Message.message_dialog_info = data.message_dialog;
        }
        $.ajax({
            url: Global.urls.get_user_storage_url,
            data : params,
            type: "POST",
            timeout : crmParams.global.ajax.get_url_timeout,
            success: function(data) {
                if(data.status == true){
                    callback(data.url);
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                if(jqXHR.status == 0 && jqXHR.readyState == 0 && !send_more){
                    send(true);
                } else {
                    Message.showErrorAjax(jqXHR, textStatus);
                    Preloader.modalHide();
                }
            },
        });
    };

    send();

}



var getModuleUrlViaParemt = function(copy_id, data_id, destination, callback){
    var base_url = '';
    var this_template = $('.list_view_block, .process_view_block').data('this_template');
    var params = {'copy_id' : copy_id, 'data_id' : data_id, 'destination' : destination, 'params' : {'this_template' : this_template}};

    $.post(Global.urls.get_user_storage_url_via_parent, params, function(data){
        if(data.status == true){
            base_url = data.url;
            callback(base_url);
        }
    })
}

var loadModule = function(copy_id, destination, module_dependent_params, callback, ajax_content_box){
    var $this = arguments[5];
    if ($this && $this.length) {
        $this.closest('.nav').find('li').removeClass('active').find($this).closest('li').addClass('active');
    }
    getModuleUrl(copy_id, destination, module_dependent_params, function(url){
            var $content = $(ajax_content_box);
            if($content.length){
                $content.addClass('addPreloader');
                $('.list_view_block .crm-table-wrapper').getNiceScroll().remove(); // remove old niceScroll
                setTimeout(function () {
                    instanceGlobal.ajax.ajaxContentReload(url, function(html){
                        if(html !== false){
                            html = instanceGlobal.ajax.ajaxContentInit($(html));
                            $content.html(html);
                            instanceGlobal.ajax.ajaxContentAfter();
                            $content.removeClass('addPreloader');
                        }
                    })
                }, 200);
                return;
            }

            if(typeof callback == 'function'){
                callback(url);
            } else {
                document.location.href = url;
            }

        });
}



// function initElements(block, time){
//     if (typeof(time) == 'undefined') {
//         time = '00:00:00';
//     }
//     var block = $(block);
//
//     // block.find('#EditViewModel_b_date_ending.date-time').datetimepicker({
//     //     'format': crmParams.FORMAT_DATE,
//     //     'startDate': new Date(),
//     //     'autoclose': true,
//     //     'language': Message.locale.language,
//     //     'locale': Message.locale.language
//     // }).on('show', function(e){
//     //     datePerm = $(this).val();
//     // }).on('hide', function(e){
//     //     var $this = $(this),
//     //         endDate = $this.datetimepicker("getDate");
//     //
//     //     $this.val(moment(endDate).format(crmParams.getCurrentFormatDate()));
//     // });

//     block.find('#EditViewModel_b_date_ending.date').datepicker({
//         language: Message.locale.language,
//         format: Message.locale.dateFormats.medium_js,
//         startDate:new Date(),
//         autoclose: true
//     }).on('show', function(e){
//         datePerm = $(this).val();
//         if ( e.date ) {
//             $(this).data('stickyDate', e.date);
//         }
//         else {
//             $(this).data('stickyDate', null);
//         }
//     }).on('hide', function(e){
//         var stickyDate = $(this).data('stickyDate');
//         if ( !e.date && stickyDate ) {
//             $(this).datepicker('setDate', stickyDate);
//             $(this).data('stickyDate', null);
//         }
//         if ($(this).val() == "") {
//             $(this).val(datePerm);
//         }
//     });
//
//     block.find('.date').datepicker({
//         language: Message.locale.language,
//         format: Message.locale.dateFormats.medium_js,
//         minDate: '1/1/1970',
//         autoclose: true
//     }).on('show', function(e){
//         if ( e.date ) {
//             $(this).data('stickyDate', e.date);
//         }
//         else {
//             $(this).data('stickyDate', null);
//         }
//     }).on('hide', function(e){
//         var stickyDate = $(this).data('stickyDate');
//
//         if ( !e.date && stickyDate ) {
//             $(this).datepicker('setDate', stickyDate);
//             $(this).data('stickyDate', null);
//         }
//     });
//
//     // block.find(".date").mask(Message.locale.dateFormats.mask_js);
//     // block.find(".time").mask(Message.locale.timeFormats.mask_js);
//
//
//     $('.date').datepicker({
//         language: Message.locale.language,
//         format: Message.locale.dateFormats.medium_js,
//         minDate: '1/1/1970',
//         autoclose: true
//     }).on('show', function(e){
//         if ( e.date ) {
//             $(this).data('stickyDate', e.date);
//         }
//         else {
//             $(this).data('stickyDate', null);
//         }
//     }).on('hide', function(e){
//         var stickyDate = $(this).data('stickyDate');
//
//         if ( !e.date && stickyDate ) {
//             $(this).datepicker('setDate', stickyDate);
//             $(this).data('stickyDate', null);
//         }
//     });
//
//     $('.time').timepicker({
//         minuteStep: 1,
//         secondStep: 5,
//         showSeconds: true,
//         showMeridian: false,
//         defaultTime: time,
//     });
// }


function initElements(block, time){
    if (typeof(time) == 'undefined') {
        time = '00:00:00';
    }
    var block = $(block);

    block.find('.date').datepicker({
        language: Message.locale.language,
        locale: Message.locale.language,
        format: Message.locale.dateFormats.medium_js,
        minDate: '1/1/1970',
        autoclose: true
    }).on('show', function(e){
        if ( e.date ) {
            $(this).data('stickyDate', e.date);
        }
        else {
            $(this).data('stickyDate', null);
        }
    }).on('hide', function(e){
        var stickyDate = $(this).data('stickyDate');

        if ( !e.date && stickyDate ) {
            $(this).datepicker('setDate', stickyDate);
            $(this).data('stickyDate', null);
        }
    });

    $('.date').datepicker({
        language: Message.locale.language,
        locale: Message.locale.language,
        format: Message.locale.dateFormats.medium_js,
        minDate: '1/1/1970',
        autoclose: true
    }).on('show', function(e){
        if ( e.date ) {
            $(this).data('stickyDate', e.date);
        }
        else {
            $(this).data('stickyDate', null);
        }
    }).on('hide', function(e){
        var stickyDate = $(this).data('stickyDate');

        if ( !e.date && stickyDate ) {
            $(this).datepicker('setDate', stickyDate);
            $(this).data('stickyDate', null);
        }
    });

    $('.time').timepicker({
        minuteStep: 1,
        secondStep: 5,
        // showSeconds: true,
        showMeridian: false,
        defaultTime: time,
    });

}





/**
 * Простой поиск по таблице (без инициализации editTable)
 */
var TableSearchInit = function(table, searchField) {
    return;
    var rows = $(table + ' tr');

    $(document).on('keyup', searchField, function() {
        var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
        rows.show().filter(function() {
            var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
            return !~text.indexOf(val);
        }).hide();
        $('.submodule-table').getNiceScroll().resize();
    });
}




String.format = function() {
    var theString = arguments[0];
    for (var i = 1; i < arguments.length; i++) {
        var regEx = new RegExp("\\{" + (i - 1) + "\\}", "gm");
        theString = theString.replace(regEx, arguments[i]);
    }
    return theString;
}

//делаем ФИО пользователей одной ссылкой
var userSingleLink = function(){
    $('.list-table a span.element_data[data-name="sur_name"]').each(function(){
        if ($(this).closest('a').find('span:last-child').is('[data-name="sur_name"]')) {
            $(this).closest('a').append(' '+$(this).closest('a').next().html());
            $(this).closest('a').append(' '+$(this).closest('a').next().next().html());
            $(this).closest('a').next().hide();
            $(this).closest('a').next().next().hide();
        }
    });
}

var profileImgProp = function() {
    setTimeout (function(){
        var $img = $('.profile-information .profile-pic img[src*="."]');
        var $link = $('.profile-information .profile-pic .upload-result .name');
        if ($img.width()>$link.width()) {
            $img.css('margin-left', '-'+($img.width()-$link.width())/2+'px');
        } else {
            $img.css('margin-top', '-'+($img.height()-$link.height())/2+'px');
        }
    }, 300);
}




// dropdown-menu fix
$(document).on('click', '.dropdown-menu > li input,  .table-dropdown li, .settings .dropdown-menu, .todo-remove', function(e){
    e.stopPropagation();
})

// setup field name in dropdown
$(document).on('click', '.editable-block .todo-edit', function() {
    $(this).parent().find('.form-control').attr('value', $(this).closest('.editable-block').find('.editable-field').text());
});


// edit fields
$(document).on('click', '.edit-dropdown .save-input', function() {
    var $this = $(this),
        $editableBlock = $this.closest('.editable-block'),
        $clientName = $this.closest('.client-name'),
        value = $this.closest('ul').find('.form-control').attr('value');

    $clientName.find('.editable-field').text(value);
    if ($editableBlock.closest('.constructor').length) {
        $editableBlock.find('.editable-field').text($editableBlock.find('.form-control[type="text"]').attr('value')).css('opacity', '1');
    }
    var from = value ? '' : 'none';
    $editableBlock.find('#from').css('display', from);
    if ($clientName.find('.editable-field').html() == '') {
            $clientName.find('.editable-field').addClass('empty');
        } else {
            $clientName.find('.editable-field').removeClass('empty');
        }
    $this.closest('.edit-dropdown').removeClass('open');
    $('.client-name .editable-field').addClass('opacityIn')
});

// edit fields
$(document).on('click', '.constructor-save-input-hidden', function() {
    var value = $(this).closest('ul').find('.form-control').attr('value'),
        edit_block = $(this).closest('.editable-block');

    edit_block.find('.editable-field').text(value);
    edit_block.find('.element_params[data-type="title"]').val(value);

    var from = value ? '' : 'none';
    edit_block.find('#from').css('display', from);
    $(this).closest('.edit-dropdown').removeClass('open');
});

// save text by click on "enter" or delete by "esc"
$(document).on('keydown', '.form-control[type="text"]', function( e ) {
    if (e.keyCode == 13) {
        $(this).closest('.client-name').find('.editable-field').text($(this).closest('.client-name').find('.form-control[type="text"]').attr('value')).css('opacity', '1');
        $(this).closest('.contact-item').find('.editable-field').text($(this).closest('.contact-item').find('.form-control[type="text"]').attr('value')).css('opacity', '1');
        $(this).closest('.contact-item').find('.element_params[data-type="title"]').val($(this).attr('value'));
        $(this).closest('.client-name').find('.edit-dropdown').removeClass('open');
        $(this).closest('.contact-item').find('.edit-dropdown').removeClass('open');
        if ($(this).closest('.editable-block').parent().is('.constructor')) {
            $(this).closest('.editable-block').find('.editable-field').text($(this).closest('.editable-block').find('.form-control[type="text"]').attr('value')).css('opacity', '1');
            $(this).closest('.editable-block').find('.edit-dropdown').removeClass('open');
        } else if ($(this).closest('header').closest('.constructor').length) {
            $(this).closest('.editable-block').find('.editable-field').text($(this).attr('value')).css('opacity', '1');
            $(this).closest('.editable-block').find('.edit-dropdown').removeClass('open');
        }
        if ($(this).closest('.client-name').find('.editable-field').html() == '') {
            //$(this).closest('.client-name').find('.edit-dropdown').addClass('open');
            $(this).closest('.client-name').find('.editable-field').addClass('empty');
        } else {
            $(this).closest('.client-name').find('.editable-field').removeClass('empty');
        }
    } else if (e.keyCode == 27) {
        if ($(document).find('.edit-dropdown').hasClass('open')) {
            $(document).find('.edit-dropdown').removeClass('open');
        }
        $(document).find('.edit-dropdown').removeClass('open');
        $(this).closest('.editable-block').find('.editable-field').css('opacity', '1');
    } else {
        return (e.keyCode);
    }
    return false;
});

$(document).on('click', '.client-name .editable-field', function(e) {
    e.stopPropagation();
    $('.edit-dropdown.open').each(function () {
        $(this).removeClass('open');
    });
    $('.editable-block').find('.editable-field').css('opacity', '1');
    $(this).css('opacity', '0');
    var clientName = $(this).closest('.client-name');
    clientName.find('.edit-dropdown').addClass('open');
    clientName.find('.form-control[type="text"]').attr('maxlength','50').attr('value', clientName.find('.editable-field').text()).select();
    clientName.find('.form-control[type="text"]').width($(this).closest('.client-name').find('.editable-field').width());
    $(this).next().find('.dropdown-menu').width($(this).closest('.editable-block').width() - $(this).closest('.client-name').prev().width() - $(this).closest('.client-name').prev().prev().width());
});

$(document).on('click', '.constructor .editable-block .editable-field', function(e) {
    e.stopPropagation();
    $('.edit-dropdown.open').each(function () {
        $(this).removeClass('open');
    });
    $('.editable-block').find('.editable-field').css('opacity', '1');
    $(this).css('opacity', '0');
    $(this).closest('.editable-block').find('.todo-actionlist .edit-dropdown').addClass('open');
    $(this).closest('.editable-block').find('.form-control[type="text"]').attr('maxlength','50').attr('value', $(this).closest('.editable-block').find('.editable-field').text()).select();
    $(this).closest('.editable-block').find('.form-control[type="text"]').width($(this).closest('.editable-block').find('.editable-field').width());
});

//save input
$(document).on('click', '.constructor-save-input-hidden', function(e) {
    var edit_block = $(this).closest('.editable-block');

    e.stopPropagation();
    edit_block.find('.editable-field').text(edit_block.find('.form-control[type="text"]').attr('value')).css('opacity', '1');
    edit_block.find('.edit-dropdown').removeClass('open');
    return false;
});



// ********************* edit fields contacts in listView dropdown - end    ***************************************************




$(document).on('click','.form-datetime .time', function() {
    $(this).next().find('button').trigger('click');
})

$(document).on('click', '.form-datetime .date-set', function() {
    $(this).parent().prev().datepicker('show');
});

$(document).on('click', '.crm-dropdown[data-type="inline-edit"] .date-calendar', function() {
    $(this).parent().prev().datepicker('show');
});


/**
* NiceScroll Init for ajax loading
**/

var niceScrollCreate = function(selector, config) {
    var param, base;

    base = {
        cursorcolor: "#1FB5AD",
        cursorborder: "0px solid #fff",
        cursorborderradius: "0px",
        cursorwidth: "3px",
        railalign: 'right',
        autohidemode: false,
        horizrailenabled: false,
        preservenativescrolling: false
    }

    param = config ? config : base;

    return selector.niceScroll(param);
};


var jScrollCreate = function(selector) {
    selector.jScrollPane();
}

var jScrollInit = function() {
    $('.edit-view .crm-table-wrapper').each(function(){
        var $this = $(this);
        if ($this.closest('.panel-body').css('display') == 'block') {
            if ($(this).width()<$(this).find('.crm-table').width()){
                $this.jScrollPane();
            }
        }
    });
};

var jScrollRemove = function() {
    $('.edit-view .crm-table-wrapper').each(function(){
        var $this = $(this);

        $this.find('.jspPane').css('position','static');
        $this.find('.jspContainer').css('height','auto');
        if($('.jspScrollable').data('jsp')) {
            $('.jspScrollable').data('jsp').destroy();
        }else if ($('.crm-table-wrapper').data('jsp')) {
            $('.crm-table-wrapper').data('jsp').destroy();
        }
    });
};


/**
* Magnific popup for image previews
**/
var imagePreview = function(notRemoveMargin) {
    var main_class = '';
    if(!notRemoveMargin){
       main_class = 'mfp-container';
    }

    var test = {}; // than remove this object
    test.function = 'imagePreview()';
    test.notRemoveMargin = notRemoveMargin;
    test.object = this;

    if ($('.wizz_form_wrapper').hasClass('registration')) {} else {
        test.imagePreview = $('.image-preview');
        test.magnificPopup = $('.image-preview').magnificPopup();

        $('.image-preview').magnificPopup({
            tClose: Message.translate_local('Close')  + ' (Esc)',
            type: 'image',
            mainClass: main_class, // class to remove default margin from left and right side
            image: {
                verticalFit: false,
                titleSrc: function(item) {
                        var show_link = false;
                        var link_download = '', link_remove = '';
                        if(item.el.data('show-link-download')){
                            show_link = true;
                            var link_download = '<a href="' + item.el.data('download-link') + '" download>' + Message.translate_local('Download') +'</a>';
                        }
                        if(item.el.data('show-link-remove')){
                            show_link = true;
                            var link_remove = '<a class="preview-file-remove" href="javascript:void(0)" data-id="'+item.el.data('id')+'">' + Message.translate_local('Remove') + '</a>';
                        }

                        return item.el.attr('title') +
                            '<div class="mfp-date">' + Message.translate_local('Added') +  ' ' + item.el.data('dateupload') + ' - ' + item.el.data('filesize') + Message.translate_local('Kb') + '</div>' +
                            (show_link ? ('<div class="mfp-operations">' + link_download + link_remove + '</div>') : '');
                }
            }

        }).on('click', function() {
            niceScrollCreate($('.mfp-wrap'));
        });
    }
};

/*=============================================
=       Inline edit dropdowns outside         =
=============================================*/

var dropDownPosition = function($selector) {
    var $input       = $selector.parent(),
        $dropdown    = $selector.parent().find('.dropdown-menu').first(),
        offset       = $selector.offset(),
        toggleMargin = $selector.hasClass('form-control') ? 0 : 3,
        top          = 0,
        left         = 0;
    // if this is "select"
    if (!$selector.hasClass('element_relate') && !$selector.hasClass('element_relate_participant')) {
        var width = $input.width();
    } else {
        var subWidth = $input.width();
    }
    $dropdown.css({
        'min-width' : width,
        'width'     : subWidth
    });
    // open dropdown at the bottom
    top = offset.top + $input.height() - toggleMargin - $(document).scrollTop();
    /**
     * calc left postion:
     * $('#main-content').width() - $('.crm-table-wrapper').width() - position of block with table
     * offset.left - position of input with dropdown
     * $('.list-view-panel .crm-table-wrapper').offset().left - position of scroll _inside_ table
     */
    try {
        left = $('#main-content').width() - $('.crm-table-wrapper').width() - 30 + offset.left - $('.list-view-panel .crm-table-wrapper').offset().left;
    } catch(e) {
        left = offset.left;
    }
    if ($input.hasClass('select')) {
        if($dropdown.find('li').length>10) {
            $dropdown.height(240);
        } else {
            $dropdown.height($dropdown.find('li').length*24.8);
        }
    }
    if ($(window).height() - offset.top + $(document).scrollTop() - $input.height() - 30 < $dropdown.height()) {
        if (offset.top > $dropdown.height()) {
            top = offset.top -$(document).scrollTop() - $input.height() - $dropdown.height();
            if ($input.hasClass('select')) {
                top += $input.height() - 8;
            }
        } else {
            //$dropdown.height($(window).height() - offset.top + $(document).scrollTop() - $input.height());
        }
    }
    // if dropdown will cut right
    if ($input.hasClass('submodule-link') && ($(window).width() -  offset.left - $('.open-right-bar').width() < $dropdown.width() )) {
        left = offset.left - $dropdown.width() + $input.width() - $(document).scrollLeft();
    }
    $dropdown.css({
        'top'       : top,
        'left'      : left,
    });
 };



//ссылки в таблице скрывать многоточием
    var poliDot = function() {
        var th = $(this);
        var listTable = $('#list-table');
        if ($('body table').is('#list-table')) {
            $('.color-status').each(function() {
                th.css('min-width', 'inherit');
                var statusL = th.width() + 16;
                var stCellL = th.parent().width();
                if (statusL > stCellL) {
                    th.css({'min-width':'100%', 'max-width':'100%'});
                } else {
                    th.css('min-width', '92px');
                }
            });
        } else if ($('body table').is('#settings-table')) {
            var cellL = $('#settings-table tr td:nth-child(2)').width();
            $('#settings-table .modal_dialog.name').each(function() {
                $(this).css('width', 'auto');
                var nameL = $(this).width();
                if (nameL > cellL) {
                    $(this).width(cellL);
                }
            });
        }

        //IE9 table bug fix
        if (document.addEventListener && !window.requestAnimationFrame) {
            $('.list-view-panel .list-table tbody').hide();
            $('.list-view-panel .list-table th>span:first-child').each(function(){
                if ($(this).width()<$(this).closest('th').width()) {
                    var indCellWid = $(this).closest('th').index();
                    $('.list-view-panel .list-table tr td:nth-child('+indCellWid+')').not(':first-child').each(function(){
                        if ($(this).find('.cell_size_wrap').length>0) {} else {
                            $(this).wrapInner('<div class="cell_size_wrap"></div>');
                        }
                        if ($(this).closest('table').find('th:nth-child('+indCellWid+')').width()<69) {
                            $(this).find('.cell_size_wrap').width(69);
                        } else {
                            $(this).find('.cell_size_wrap').width($(this).closest('table').find('th:nth-child('+indCellWid+')').width());
                        }
                        if ($(this).find('a.modal_dialog').prev().hasClass('list-view-avatar')) {
                            $(this).find('a.modal_dialog').width($(this).find('.cell_size_wrap').width()-41);
                        } else if ($(this).find('a.modal_dialog').prev().hasClass('navigation_module_link_child')) {
                        } else {
                            $(this).find('a.modal_dialog').width($(this).find('.cell_size_wrap').width());
                        }
                    });
                }
            });
            $('.list-view-panel .list-table tbody').show();
        }
    }

    var shadowEnd = function() {
        var $wrapper = $('#list-table_wrapper'),
            $crmTable = $wrapper.find('.crm-table');

        if ($wrapper.length) {
            var litable = $crmTable.offset().left + $crmTable.width(),
                lewrapper = $wrapper.offset().left + $('#list-table_wrapper.crm-table-wrapper').width(),
                $tableShadow = $('.crm-table-wrapper .table-shadow');

            if (litable <= lewrapper) {
                $tableShadow.hide();
            } else {
                $tableShadow.show();
                ListView.changeShadow();
            }
        }
    }

    var textAreaResize = function() {
        $('textarea.form-control').each(function(){
            $(this).height(40);
            for (i=1; i<100; i++) {
                $(this).scrollTop(1);
                if ($(this).scrollTop()) {
                    $(this).height($(this).height()+5);
                } else {
                    i=100;
                }
            }
            $(this).off('keyup');
            $(this).on('keyup', function(){
                textAreaResize();
            });
        });
    }


$(function() {
    if ($.inArray(location.pathname, ['/registration', '/restore', '/login'])<0) {
        Global.initHandler();
    }

    if (!Global.getModel().isAuth()) return;

    modalDialog.implements(iStore); // static
    $('body > .nicescroll-rails').css('opacity','0');

    StartupGuide.init(crmParams.getParams());

    Global.createInstance();
    instanceGlobal = new _Global();
    instanceGlobal.createOfInstances();

    EditView.implements(iQueue);// реализация очереди при открытии карточки
    ContentReload.implements(iQueue);// реализация очереди при открытии карточки
    // Search.instance = Search.createInstance();

    Url.clean();

    Events
        .init()
        .run()

    AjaxContainers.init();

    try {
        //instanceCalendarView = new CalendarView();
        Communication.getInstance();
        Calls.getInstance();

        Profile.setStyleOfDescription();
    } catch (e){
    }

    Global.browser.setParent();
    QuickViewPanel.init();
    Global.getInstance().ctrlF5();

    MainMenu.init();

    $('.widget-left:not(.hide)').closest('html').addClass('page-with-left-menu'); // TODO: optimized move

    $('.wizz_form_wrapper.login .errorMessage').css('margin-left', '-'+$('.wizz_form_wrapper.login .errorMessage').width()/2+'px');
    $('.wizz_form_wrapper.password .errorMessage').css('margin-left', '-'+$('.wizz_form_wrapper.password .errorMessage').width()/2+'px');

    eventPath = '.navigation_card_link, .navigation_module_data_link';
    $(document).off('click', eventPath).on('click', eventPath, function () {
        var copy_id = $(this).data('copy_id');
        var data_id = $(this).data('id');

        if (copy_id && data_id) {
            History.get(copy_id, data_id, {}, function (url) {
                window.location.href = url;
            });
        }

    });




    $('.reg_background .login').closest('.reg_background').css('height','100%');
    $('.reg_background .wizz_form_wrapper.password').closest('.reg_background').css('height','100%');






    // minify panel
    eventPath = '.panel .tools .fa-chevron-down, .panel .tools .fa-chevron-up';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        var _this = this,
            $this = $(this),
            $header = $this.closest('header'),
            $constructor = $this.closest('.constructor');

        Global.blockDisplaySwitch(_this, function(){
            EditView.saveBlockDisplayStatus(_this)
        });

        if ($this.is('.fa-chevron-down')){
            $header.next().addClass('slided');
            $header.animate({'padding-bottom':'35px'}, 'slow');
            if ($constructor.length){
                $this.closest('.panel').animate({'padding-bottom':'32px'}, 'slow');
            }
        } else if ($this.is('.fa-chevron-up')){
            $header.animate({'padding-bottom':'5px'}, 'slow');
            if ($constructor.length){
               $this.closest('.panel').animate({'padding-bottom':'5px'}, 'slow');
            }
        }
    });

    eventPath = '.panel .tools .fa-cog';
    $(document).off('click', eventPath).on('click', eventPath, function () {
        $(this).removeClass('fa-chevron-down');
    });

    eventPath = '.panel .tools .fa-times';
    $(document).off('click', eventPath).on('click', eventPath, function () {
        if (!$(this).parent().is('.element[data-type="remove_indicator"]')) {
            $(this).parents(".panel").parent().remove();
        }
    });

    // Toogle right menu
    $('.toggle-right-box .fa-bars').on('click', function() {
        setTimeout (function(){Global.responsiveNav();}, 150);
        setTimeout (function(){Global.responsiveNav();}, 300);
        $('.crm-table-wrapper').getNiceScroll().remove();
        niceScrollInit();
        $(this).parent().removeClass('hover');

        var state = ($('body').hasClass('open-right-panel')) ? false : true;

        // write sidebar status to localstorage
        //writeStorage('sidebar_opened', state);
        //getSidebarStatus();
    });

    //fix serching bug
    $('.top-nav .form-control').focusin(function() {
        setTimeout (function(){Global.responsiveNav();}, 150);
        setTimeout (function(){Global.responsiveNav();}, 300);
    });
    $('.top-nav .form-control').focusout(function() {
        setTimeout (function(){Global.responsiveNav();}, 150);
        setTimeout (function(){Global.responsiveNav();}, 300);
    });

    $('.toggle-right-box .fa-bars').hover(function(){
        $(this).parent().addClass('hover');
    }, function() {
        $(this).parent().removeClass('hover');
    });

    $('.top-menu .form-control.search').on('focusout', function() {
        if ($(window).width() < 1120) {
            $('.nav.notify-row').show();
        }
    });

    /*  Конец секции для Сжатия навигации */

    imagePreview();

    //делаем ФИО пользователей дной ссылкой
    userSingleLink();

    //drop menu with checkboxes
    eventPath = '.table-dropdown .dropdown-toggle';
    $(document).off('click', eventPath).on('click', eventPath, function() {
        var obj, dropdownMenu,
            maxCountForBlock = 10,
            t = $(this);

        dropdownMenu = t.closest('.dropdown').find('.dropdown-menu');

        if (t.data('list-is-checkbox') == undefined)
        {
            var li = dropdownMenu.find('li');
            obj = (li.find('div').length > maxCountForBlock) ? li.addClass('height270') : null;
        }
        else
        if (!t.data('list-is-checkbox'))
        {
            obj = (dropdownMenu.find('li').length > maxCountForBlock) ? dropdownMenu.find('ul') : null;
        }
        (obj) ? niceScrollCreate(obj): '';
    });

    eventPath = '.jspVerticalBar';
    $(document).off('click', eventPath).on('click', eventPath, function() {
      return false;
    });

    eventPath = '.table-dropdown';
    $(document).off('hide.bs.dropdown', eventPath).on('hide.bs.dropdown', eventPath, function() {
        $(this).find('.dropdown-menu').getNiceScroll().remove();
    });

    eventPath = '.edit-dropdown .dropdown-menu .form-control[type="text"]';
    $(document).off('input', eventPath).on('input', eventPath, function() {
        var t = $(this);
        if (t.next('pre').is('.invisi') ) {
           t.next('pre.invisi').text(t.val());
            t.width(t.next('pre.invisi').width()+2);
        } else {
            t.after('<pre class="invisi"></pre>');
            t.next('pre.invisi').css({
                'font-size': t.css('font-size'),
                'font-weight': t.css('font-weight'),
                'font-family': t.css('font-family')
            });
           t.next('pre.invisi').html(t.val());
            t.width(t.next('pre.invisi').width()+2);
        }
    });

    eventPath = '.edit-dropdown .dropdown-menu .form-control[type="text"]';
    $(document).off('focus', eventPath).on('focus', eventPath, function() {
        var t = $(this);
        if (t.next('pre').is('.invisi') ) {
           t.next('pre.invisi').text(t.val());
            t.width(t.next('pre.invisi').width()+2);
        } else {
            t.after('<pre class="invisi"></pre>');
            t.next('pre.invisi').css({
                'font-size': t.css('font-size'),
                'font-weight': t.css('font-weight'),
                'font-family': t.css('font-family')
            });
           t.next('pre.invisi').html(t.val());
            t.width(t.next('pre.invisi').width()+2);
        }
    });



    eventPath = '.list_view_block .element[data-type="project_menu"] tr.sm_extension_data, .process_view_block .element[data-type="project_menu"] tr.sm_extension_data';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        var $this = $(this);

        if($this.hasClass('active')) return;

        instanceGlobal.preloaderShow($this);

        $this.closest('table').find('.sm_extension_data.active').removeClass('active');
        $this.addClass('active');
        var title = $this.find('.name').text();
        $this.closest('.element[data-type="project_menu"]').find('.element[data-type="drop_down_button"]').text(title);



        var params = Url.parseURLParams();
        var params_parse = [];

        $.each(params, function(key, value){
            if(key != 'pdi') params_parse.push(key +'='+ value);
        });

        params_parse = params_parse.join('&');
        var url = window.location.href.split("?");
        url = url[0] + (params_parse ? '?' + params_parse + '&pdi=' + $this.data('id') : '');

        instanceGlobal.contentReload
            .clear()
            .prepareVariablesToGeneralContent()
            .setUrl(url)
            .setCallBackSuccessComplete(function(){
                $this.closest('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id', $this.data('id'));
            })
            .run();
    });



    eventPath = '.bpm_block .element[data-type="process_menu"] tr.sm_extension_data';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        var $this = $(this),
            process = new Process();

        instanceGlobal.preloaderShow($this);
        process.BPM.open($this.data('id'), ProcessObj.mode);
    });

    eventPath = '.filter-block .search-filter+.btn-group .btn-create';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        if($('.filter-block').data('open')) {
        } else {
            $btnUl = $('.filter-block .search-filter+.btn-group .btn-create').next('ul')
            $btnUl.getNiceScroll().remove();
            $pureList = $btnUl.find('div').html();
            $btnUl.removeClass('padded').html($pureList).css('height', 'auto');
        }
        var list = $(this).next('ul');
        if (list.height() > 260) {
            list.addClass('padded').wrapInner('<div></div>');
            list.find('div').css({'height': '264px', 'overflow': 'hidden'});
            ProcessView.setScroll($(this).next('ul').find('div'));
        }
    });

    $(document).on('click', function() {
        if($('.filter-block').data('open')) {
        } else {
            $btnUl = $('.filter-block .search-filter+.btn-group .btn-create').next('ul')
            $btnUl.getNiceScroll().remove();
            $pureList = $btnUl.find('div').html();
            $btnUl.removeClass('padded').html($pureList).css('height', 'auto');
            $('.extended').getNiceScroll().remove();
            $('.profile-contacts .edit-dropdown.open').removeClass('open');
        }
    });

    eventPath = 'a[data-toggle="dropdown"]';
    $(document).on('click', eventPath, function() {
        $('.extended').getNiceScroll().remove();
    });

    eventPath = '.wizz_form_wrapper.registration .logo_img';
    $(document).on('click', eventPath, function() {
        var arr = window.location.href.split('/');
        window.location.replace('http://'+arr[2]);
    });

    profileImgProp();


    //множественный выбор
    $('.adv-table').on('click', '.many_select+ul table td', function(e){
        e.stopPropagation();
        var target = $( e.target );
        if (target.is('span')){
            tarid = target.closest('tr').data('id');
            if (target.next().prop('checked')){
                target.next().prop('checked', false);
            } else {
                target.next().prop('checked', true);
            }
        } else if (target.is('.checkbox')){
            tarid = target.closest('tr').data('id');
        }
        manySelect = $(this).closest('.crm-dropdown').find('.many_select');
        manySelCount = null;
        manySelect.data('id','');
            $(this).closest('.dropdown-menu').find('.checkbox').each(function(){
                if ($(this).prop('checked')) {
                    checkedId = $(this).closest('tr').data('id');
                    if (manySelect.data('id') == '') {
                        manySelect.data('id', checkedId);
                        manySelCount = 1;
                        manySelect.text($(this).prev().text());
                    } else {
                        manySelect.data('id', manySelect.data('id') + ',' + checkedId);
                        manySelCount = manySelCount + 1;
                        manySelect.text(manySelCount);
                    }
                }
            });
    });

    $('.data_edit').on('show.bs.dropdown', '.crm-dropdown' ,function () {
        if ($(this).find('.many_select').length > 0) {
            manySelectId = $(this).find('.many_select').data('id').toString();
            if (manySelectId.search(',')) {
                    var arr = manySelectId.split(',');
                    $.each(arr, function( index, value ) {
                    $('.many_select').closest('.crm-dropdown').find('tr[data-id="'+value+'"] .checkbox').prop('checked', true);
                });
            } else {
                $('.many_select').closest('.crm-dropdown').find('tr[data-id="'+manySelectId+'"] .checkbox').prop('checked', true);
            }
        }
    });

    eventPath = '#more-links';
    $(document).off('show.bs.dropdown', eventPath).on('show.bs.dropdown', eventPath, function () {
        $(this).find('ul.dropdown-menu').css('height','auto');
        var menuVari = 68 + $(this).find('ul.dropdown-menu').height();
        if (menuVari > $(window).height()) {
            $(this).find('ul.dropdown-menu').height($(window).height() - 85).css('overflow','hidden');
            niceScrollCreate($(this).find('ul.dropdown-menu'));
        }
    });

    $(document).off('hide.bs.dropdown', eventPath).on('hide.bs.dropdown', eventPath,function () {
        var list = $(this).find('ul');
        list.getNiceScroll().remove();
        list.css('height','auto');
    });

    eventPath = '.modal';
    $(document).off('click', eventPath).on('click', eventPath, function (e) {
        var $target = $(e.target);

        if ($target.is('[data-type="switch"]') && $target.is('.fa-chevron-down')) {
            EditView.SMCrmTable($target.closest('[data-type="block"]').find('.crm-table .list-view-avatar'));
        } else if ($target.is('.jspDrag')) {} else {
            // if ($('.modal').length>0){
            //     EditView.textRedLine();
            //     jScrollRemove();
            //     jScrollInit();
            //     setTimeout(function(){ EditView.textRedLine(); jScrollRemove(); jScrollInit(); }, 20);
            // }
        }
    });

    $('.modal').off('click').on('click', function () {
        if ($('.modal').length>0){
            EditView.textRedLine();
            jScrollRemove();
            jScrollInit();
            setTimeout(function(){ EditView.textRedLine(); jScrollRemove(); jScrollInit(); }, 20);
        }
    });

    // $(document).keyup(function(e){
    //     if(e.which == 27){
    //         if ($(modalDialog.getModalName()).length ) {
    //           EditView.close();
    //         }
    //     }
    // });

    $(window).on('mousewheel', function(e) {
        if ($('.element[data-type="drop_down_list"] .nicescroll-rails[id*=-hr]').is(':visible')) {
            e.preventDefault();
        }
    });

    //виклик скрола для поиска
    eventPath = '.element[data-type="drop_down"] .element[data-type="drop_down_button"]:not(.readonly), .edit-view .element[data-type="drop_down"] .element[data-type="select"]';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        var _this = $(this),
            $parent = _this.closest('.element[data-type="drop_down"]')
            drop_down_list = new DropDownList();

        if ($(this).is('[data-type=select]')) {
            $parent = $('.participants.element[data-type="drop_down"]');
        }

        if (!$parent.position()) {
            return;
        }

        //change position by Y
        var coordTop,
            list = _this.find('[data-type="drop_down_list"]');
        var deltaOfBottom = $(window).height() - $parent.position().top;
        if (deltaOfBottom<list.height()) { // change position top
            coordTop = -list.height()-35;
        } else {
            coordTop = _this.height(); //(_this.find('button').text().length) ? _this.height :34;
        }
        list.css('top', coordTop);

        drop_down_list
            .setParent($parent)
            .prepareGroupData(this)
            .run();

        var style = $parent.find('ul').attr('style');

        if (Global.isListView() && !style) {
            $parent.attr('hidden-list', true);

            setTimeout(function () {
                var $list = $('.editing .open:visible'),
                    style = $list.find('ul').attr('style');

                if ($list.length && !style) {
                    var $element = $parent.find('.dropdown-menu');

                    GlobalView
                        .createInstance()
                        .setParent($parent)
                        .setElement($element)
                        .setWidthElement($element.css('min-width'))
                        .initPosition();

                    drop_down_list.run($parent);
                }
                $parent.find('.submodule-table').width('100%')
                $parent.removeAttr('hidden-list')
            }, 100);
        }

        Events
            .createInstance()
            .setType(Events.TYPE_EVENT_RESIZE)
            .setKey('dropDown')
            .setHandler(function () {
                $parent.removeClass('open'); })
            .run();

    });


    $(window).load(function() {
        Global.initSelects();
        if(typeof(crmParams) !== 'undefined'){
            AjaxObj.setTimeOut(crmParams.global.ajax.global_timeout);
        }
    });

    eventPath = '.markIsRead';
    $(document).off('click', eventPath).on('click', eventPath, function() {
        $(this).hide().closest('#header_notification_bar').addClass('opened');
        $('.navbar-header').removeClass('overflowHidden');
        setTimeout(function(){
            $('.opened').addClass('open').removeClass('opened')
        }, 300);
    });

    eventPath = '#header_notification_bar .dropdown-menu';
    $(document).off('click', eventPath).on('click', eventPath, function() {
         $(this).closest('#header_notification_bar').addClass('opened');
        setTimeout(function(){
            $('.opened').addClass('open').removeClass('opened')
        }, 300);
    });

    eventPath = '.list-table td';
    $(document).on('click', eventPath, function(){
        setTimeout(function() {
            Global.createLinkByEV($('.edit-view.in:last'));
        }, 300);
    });
    $(document).on('show.bs.modal', function(){
        Global.createLinkByEV($('.edit-view.in:last'));
    });

    eventPath = 'popstate';
    $(window).off(eventPath).on(eventPath, function(e) {
        History.createInstance().initBackForward(e);
    });


});

;(function (exports) {
    var _private, _public, Api,
        _self = {}; //link for instance

    _private = {

    };

    _public = {
        constructor: function () {
            return this;
        }
    };

    Api = {
        createInstance: function () {
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            return new Obj().constructor();
        },
        history: {
            data: null,
            url: null,
            async: false,
            action_id: null,
            controller_id: null,

            TYPE_ACTION_SHOW: 'show',
            TYPE_ACTION_PROFILE: 'profile',
            TYPE_ACTION_NULL: 'null',
            TYPE_ACTION_PARAMETERS: 'parameters',
            TYPE_ACTION_PLUGINS: 'plugins',
            TYPE_ACTION_MAINLING: 'mailingServices',

            TYPE_CONTROLLER_LIST_VIEW: 'listView',
            TYPE_CONTROLLER_PROCESS_VIEW: 'processView',
            TYPE_CONTROLLER_CALENDAR_VIEW: 'calendarView',
            TYPE_CONTROLLER_PROFILE: 'profile',
            TYPE_CONTROLLER_CONSTRUCTOR: 'constructor',
            TYPE_CONTROLLER_SITE: ['parameters','plugins','mailingServices'],

            createInstance: function () {
                var Obj = function(){
                    for(var key in Api.history){
                        this[key] = Api.history[key];
                    }

                    iModule.implements.call(this);
                }

                return new Obj();
            },
            setData: function (data) {
                this.data = data;
                return this;
            },
            setControllerId: function (controller_id) {
                this.controller_id = controller_id;

                return this;
            },
            setActionId: function (action_id) {
                this.action_id = action_id;

                return this;
            },
            setUrl: function (url) {
                this.url = url;

                return this;
            },
            setUserStorageBackUrl: function (callback) {
                AjaxObj
                    .createInstance()
                    .setUrl(Global.urls.set_user_storage_back_url)
                    .setData({
                        'controller_id' : this.controller_id,
                        'action_id' : this.action_id,
                        'url' : this.url
                    })
                    .setType('POST')
                    .setDataType('json')
                    .setAsync(this.async)
                    .setCallBackSuccess(function(data) {
                        if(typeof(callback) == 'function') callback(data);
                    })
                    .setCallBackError(function(){
                        if(typeof(callback) == 'function') callback(false);
                    })
                    .send();
            },
            setUserStorage: function (callback) {
                AjaxObj
                    .createInstance()
                    .setUrl(Global.urls.url_set_user_storage)
                    .setData({
                        'type' : this.getKey(),
                        'index' : this.copy_id,
                        'value' : this.data
                        // 'pci' : this.pci,
                        // 'pdi' : this.pdi,
                    })
                    .setType('POST')
                    .setDataType('json')
                    .setAsync(this.async)
                    .setCallBackSuccess(function(data) {
                        if(typeof(callback) == 'function') callback(data);
                    })
                    .setCallBackError(function(){
                        if(typeof(callback) == 'function') callback(false);
                    })
                    .send();
            }
        }
    }

    for(var key in _private){
        _self[key] = _private[key];
    }

    for(var key in Api){
        _self[key] = Api[key];
    }

    exports.Api = Api;
})(window)


;(function (exports) {
    var _private, _public, GlobalView,
        _self = {}; //link for instance

    _private = {

    };

    _public = {
        _parent: null,
        _element: null,
        _offset: 0,
        _width_element: null,

        setWidthElement: function (_width) {
            this._width_element = _width;

            return this;
        },
        initPosition: function () {
            var parentTop, parentLeft, offset,
                heightContainer = this._element.height(),
                widthContainer = this._width_element || this._element.width(),

            offset = this._parent.height();

            parentTop = this._parent.offset().top + offset;
            parentLeft = (this._parent.offset().left - ListView.getScrollLeft());

            if (inLineEdit.isEditing()) {
                parentTop -= $(window).scrollTop();

            }

            this._element.css({
                top: parentTop,
                left: parentLeft,
                width: widthContainer
            });

            return this;
        },
        setOffset: function (_offset) {
            this._offset = _offset;

            return this;
        },
        setParent: function (_parent) {
            this._parent = _parent;

            return this;
        },
        setElement: function (_element) {
            this._element = _element;

            return this;
        },
        constructor: function () {
            return this;
        }
    };

    GlobalView = {
        createInstance: function () {
            var Obj = function(){
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return new Obj().constructor();
        },
    }

    for(var key in _private){
        _self[key] = _private[key];
    }

    for(var key in GlobalView){
        _self[key] = GlobalView[key];
    }

    exports.GlobalView = GlobalView;
})(window)
