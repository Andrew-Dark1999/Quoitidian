
/**************************************************************
 *               Communications
 ***************************************************************/

var Communication = {
        _interface: 'communication',

        _copy_id: 13,
        _instance: null,
        _params: 0,
        _parent : null,

        getFilter: null,

        getInstance: function(){
            if(!Communication._instance){
                Communication._instance = Communication.createInstance();
            }

            return Communication._instance;
        },

        createInstance: function () {
            var Obj = function () {
                for (var key in Communication) {
                    this[key] = Communication[key];
                }
            }

            return new Obj().constructor();
        },
        constructor: function () {
            this.events();

            this.getFilter = Filter; //.createInstance();

            return this;
        },
        setId: function (id) {
            this._copy_id = id;

            return this;
        },
        getId: function () {
            return this._copy_id;
        },
        isCommunicationsEditView: function () {
            return $('.edit-view[data-copy_id="'+this.getId()+'"]').length ? true : false;
        },
        isCommunicationsModule: function () {
            return $('.list_view_block[data-copy_id=' + this.getId() + ']').length || $('.process_view_block[data-copy_id=' + this.getId() + ']').length ? true : false;
        },

        openMenuServices: function () {
            this.actions.onClickServicesParams();

            return this;
        },
        showPreloader: function () {
            var status = false;

            if (this.isCommunicationsModule()) {
                ViewType.getPreloader().showPreloader();
                status = true;
            }

            return status;
        },
        events: function () {
            var data = {
                    instance: this
                },
                path = this.actions;


            this._events = [
                {
                    parent: document,
                    selector: '.element[data-type="communications-settings"]:not([data-name])',
                    event: 'click',
                    func: path.onClickServicesParams
                },
                {
                    parent: document,
                    selector: '.element[data-type="communications-settings"][data-name]',
                    event: 'click',
                    func: path.onClickUpdateService
                },
                {
                    parent: document,
                    selector: 'ul.channels-list>li.element[data-type="communications_menu_channel"]',
                    event: 'click',
                    func: path.onGetChannel
                },
                {parent: document, selector: '.right-sidebar .edit_view_dnt-add', event: 'click', func: path.onAddCard},
                {
                    parent: document,
                    selector: '.communication-services .list-services .element[data-id]',
                    event: 'click',
                    func: path.onClickOpenParams
                },
                {
                    parent: document,
                    selector: '.communication-services .service-param .close-button-back',
                    event: 'click',
                    func: path.onClickCloseParams
                },
                {parent: document, selector: '.activity-ext', event: 'click', func: path.onClickAreaText},

                // ??? !!! Нельзя привязываться только к атрибутам
                {
                    parent: document,
                    selector: '[data-type_comment="email"] .emoji-wysiwyg-editor',
                    event: 'keyup',
                    func: path.onKeyUpAreaText
                },


                {
                    parent: document,
                    selector: '.communication-services .element[data-type="save"]',
                    event: 'click',
                    func: path.onSave
                },
                {
                    parent: document,
                    selector: '.edit-view .element[data-type="editors"] .element[data-type="drop_down"] .link .element[data-type="add-channel"]',
                    event: 'click',
                    func: path.onAddChannel
                },
                {parent: document, selector: '.activity-ext input.form-control', event: 'click', func: path.onFocusArea},
                {parent: document, selector: '.activity-ext input.form-control', event: 'blur', func: path.onBlurInput},
                {
                    parent: document,
                    selector: '.communication-services .service-param:not([data-update="false"]) .close-button',
                    event: 'click',
                    func: path.onClickServiceParamClose
                },
                {
                    parent: document,
                    selector: '.communication-services .service-param:not([data-update="true"]) .close-button',
                    event: 'click',
                    func: path.onClickPopupClose
                },
                {
                    parent: document,
                    selector: '.communication-services [data-type="list-services"] select',
                    event: 'change',
                    func: path.onChangeService
                },
                {parent: document, selector: '.activity-ext .recipients input', event: 'keyup', func: path.onAddRecipient},
                {parent: document, selector: '.activity-ext .recipients', event: 'click', func: path.onFocus},
                {
                    parent: document,
                    selector: '.activity-ext .recipients .todo-remove',
                    event: 'click',
                    func: path.onRemoveRecipient
                },
            ]

            Global.addEvents(this._events, data);

            return this;
        },

        importModel: function (data) {
            for (var key in data) {
                if (typeof data[key] != 'object') {
                    this[key] = data[key];
                }
            }

            return this;
        },

        initTextArea: function () {
            var offset, offsetTop, $list,
                $communication = $('[data-sub_type="btn-group-editors"]'),
                $activity = $communication.find('.activity-ext'),
                $textArea = $communication.find('.message_field[data-type_comment="email"] .emoji-wysiwyg-editor:visible'),
                $message = $activity.find('.message');

            if (!$activity.length) {
                return this;
            }

            $list = $activity.find('li');
            offset = $activity.find('[data-type="data"]').height();
            offsetTop = parseInt($textArea.css('padding-top'));

            if (offsetTop == 0) {
                $textArea.css({
                    'padding-top': offset - 14,
                    'padding-bottom': 4
                });
            } else {
                $textArea.css({
                    'padding-top': $list.first().height() * $list.length + 4,
                    'padding-bottom': 4
                });
            }
            if ($.browser && $.browser.mozilla) {
                $textArea.height($list.first().height());
            }

            Global.groupDropDowns().init();

            $.each($activity.find('input'), function () {
                var $this = $(this);

                ($this.val().length) ? $this.removeClass('empty') : $this.addClass('empty');
            })

            if ($textArea.length && $textArea.html().length) {
                $message.addClass('hide')
            } else {
                $message.removeClass('hide')
            }
            return this;
        },
        reloadChannel: function () {
            var _this = this;

            if (this.interval_actions == null && QuickViewPanel.getInstance()) {
                this.interval_actions = setInterval(function () {
                    if (QuickViewPanel.getInstance()) {
                        _this.refreshMenuItems(true);
                    } else {
                        clearInterval(_this.interval_actions);
                    }
                }, crmParams.global.intervals.quick_view.block_communications);
            }
        },
        refreshMenuItems: function (period) {
            CommunicationsBlock
                .getInstance()
                .setPeriod(period)
                .refreshChannels(false);
        },

    actions: {
        onAddChannel : function (e) {
            var _this = $(this).closest('.edit-view').find('.element[data-type="block"] .sm_extension[data-type="submodule"][data-relate_copy_id="'+Communication.getId()+'"] .submodule_edit_view_dnt-create');

            if(!_this){
                return;
            }

            Preloader
                .createInstance()
                .setModal(true)
                .setModalSub(true)

            EditView.subModules.cardCreate(_this);

        },
        onFocus : function () {
            $(this).find('input').focus();
        },
        onClickSubWindow : function (step, param) {
            var $blocks = $('[data-type="sub-window"]');

            $blocks.filter('.active').find('.panel-body>.element').addClass('hide');
            $blocks.removeClass('active')

            if (step == 1) {
                $blocks.first().addClass('active');
            } else if (step == 2) {
                var $element = $blocks.last();

                $element.addClass('active');
                if (param == 'change') {
                    $('.close-button').attr('data-dismiss', 'modal');
                    $element.find('[data-type="list-services"]').removeClass('hide');
                    $element.find('[data-id="'+ $element.find('select').val() +'"]').removeClass('hide');
                    Global.initSelects();
                }
            }
        },

        onClickUpdateService : function (e) {
            var $this = $(this);

            $.ajax({
                url: '/module/communication/UpdateService/' + e.data.instance.copy_id,
                data : {
                    name : $this.attr('data-name')
                },
                type: "POST",
                timeout : crmParams.global.ajax.get_url_timeout,
                success: function(data) {
                    if(data.status == true){
                        Message.show(data.html, true);
                    }
                    e.data.instance.onClickSubWindow(2, 'change');
                    return false;
                },
                error: function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                },
            });
            return false;
        },

        onClickServicesParams : function (e) {
            var id,
                data = {};

            if (e) {
                data.page = 2;
                id = e.data.instance._copy_id;
            } else {
                data.page = 1;
                id = Communication._copy_id;
            }

            data.page = (e) ? 2 : 1;

            AjaxObj.createInstance()
                .setUrl('/module/communication/serviceParams/' + id)
                .setData(data)
                .setType("post")
                .setDataType('html')
                .setTimeOut(crmParams.global.ajax.get_url_timeout)
                .setCallBackSuccess(function (html) {
                        Communication.createInstance().instanceDialog = modalDialog
                            .createInstance()
                            .show(html)

                    Global.initSelects();
                })
                .send();

            return false;
        },

        onRemoveRecipient : function (e) {
            $(this).closest('.recipients').remove();
            e.data.instance.initTextArea();
        },
        onFocus : function () {
            $(this).find('input').focus();
        },
        onAddRecipient : function (e) {
            var $this = $(this),
                $activityExt = $('.activity-ext');

            if (e.keyCode == 13) {
                var $object = $activityExt.find('li.recipients').last(),
                    $clone = $object.clone();

                $clone.find('input').val('');

                $object.after($clone);
                e.data.instance.initTextArea();
            } else {
                ($this.val().length) ? $this.removeClass('empty') : $this.addClass('empty');
            }
        },
        onClickPopupClose : function (e) {
            $('[data-type="modal-dialog"]').modal('hide');
        },
        onChangeService : function () {
            var $this = $(this),
                $services = $('.service-param [data-id]'),
                id = $this.val();

            $services.addClass('hide').filter('[data-id="'+id+'"]').removeClass('hide');
        },
        onClickServiceParamClose : function (e) {
            e.data.instance.onClickSubWindow(1);
        },
        onBlurInput : function () {
            $('.emoji-wysiwyg-editor').removeClass('focus');
        },
        onFocusArea : function () {
            $('.emoji-wysiwyg-editor').addClass('focus');
        },
        onKeyUpAreaText : function () {
            var $this = $(this),
                $message = $this.closest('.activity-ext').find('.message');

            ($this.html().length) ? $message.addClass('hide') : $message.removeClass('hide');
        },
        onClickUpdateService : function (e) {
            var $this = $(this);

            AjaxObj
                .createInstance()
                .setUrl('/module/communication/UpdateService/' + Communication.copy_id)
                .setData({
                    name: $this.attr('data-name')
                })
                .setType("POST")
                .setTimeOut(crmParams.global.ajax.get_url_timeout)
                .setCallBackSuccess(function (data) {
                    if (data.status == true) {
                        Message.show(data.html, true);
                    }
                    e.data.instance.onClickSubWindow(2, 'change');
                    return false;
                })
                .send();

            return false;
        },
        onGetChannel : function(e){
            var $this = $(this);

            if (($this.closest('.panel-heading').length && $this.closest('.process_view_block').length) // What we is on PV
                || $this.closest('.sm_extension_data.editing').length) {
                return;
            }
            var parent = QuickViewPanel.getInstance() || e.data.instance,
                EVInstance = EditView.createInstance().setParent(parent);

            EVInstance
                .setAfterLoadViewCallBack(function () {
                    $this.find('.user-status').empty()
                })
                .editCard(this, null, function(data){
                    this.runAfterEditCardLV(data);

                    Global
                        .createLinkByEV($('.edit-view:last'))
                        .fixSubstrateInModal();
            });
        },
        onAddCard : function (e) {
            var instance,
                QV = QuickViewPanel.getInstance();

            instance = EditView.createInstance().setParent(QV)

            if (QV) {
                instance.setPreloader(QV.getModel().getCurrentModel().getPreloader());
            }

            instance.addCard(this, null, function(data){
                this.runAfterAddCardLV(data);
                Communication.initTextArea();
            });
        },
        onSave : function (e) {
            var time,
                $bloks = $('.element.active[data-type="sub-window"] [data-id].element:not(.hide)'),
                $inputs = $bloks.find('input, select'),
                $this = $(this),
                ajax = AjaxObj.createInstance(),
                data = {
                    id : parseInt($bloks.attr('data-id')),
                    source_name : 'email',
                    service_name : $bloks.attr('data-service-name'),
                    signature : $bloks.find('textarea.element[data-name="signature"]').val(),
                    list : {}
                };

            $.each($inputs, function(key, value) {
                var $value = $(value),
                    key = $value.attr('data-name');

                data.list[key] = $value.val();
            });

            Global.btnSaveSetDisabled($this, true);

            ajax.setData(data)
                .setAsync(false)
                .setType('POST')
                .setUrl('/module/communication/SaveServiceParams/' + e.data.instance._copy_id)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    if(data.status == 'access_error' || data.status == 'error'){
                        Message.show(data.messages, false);
                    } else if (data.status == 'error_email_connect') {
                        Message.show([{'type': 'error', 'message': data.messages}], false);
                    } else if(data.status == 'error_validate'){
                        $this.closest('.sm_extension').find('.element[data-type="objects"] .errorMessage').html('');
                        $.each(data.messages, function(data_type, message){
                            $this.closest('.sm_extension').find('.element[data-type="objects"] .element[data-type="'+data_type+'"] .errorMessage').html(message);
                        })
                    } else if(data.status){
                        $this.closest('.sm_extension').find('.element[data-type="objects"] .errorMessage').html('');
                        if(Communication.getInstance().instanceDialog){
                            Communication.getInstance().instanceDialog.hide();
                        }
                        else {
                            $('.close-button:visible').trigger('click');
                        }
                    }
                    Global.btnSaveSetDisabled($this, false);
                })
                // .setCallBackError(function(jqXHR, textStatus, errorThrown){
                //     Message.showErrorAjax(jqXHR, textStatus);
                // });

            time = setTimeout(function () {
                clearTimeout(time);
                ajax.send();
            }, 50)
        },
        onClickAreaText : function (e) {
            var $this = $(this),
                $target = $(e.target);

            if ($target.is('.form-control') || $target.is('.form-control')) {
                return;
            }

            $this.find('.emoji-wysiwyg-editor').addClass('is-focused').focus();
            return false;
        },
        onClickOpenParams : function (e) {
            var $block,
                $this = $(this);

            $block = $('[data-type="sub-window"]').removeClass('active').last();
            Global.initSelects();
            $block.addClass('active');
            $block.find('[data-id="'+$this.attr('data-id')+'"]').removeClass('hide');
            $block.find('.panel-heading .title').html($this.find('.text').text());
            $block.find('.errorMessage').html('');

            return false;
        },
        onClickCloseParams : function (e) {
            var $block,

            $block = $('[data-type="sub-window"]').removeClass('active').first();
            Global.initSelects();
            $block.addClass('active');
            $('[data-type="sub-window"]').last().find('.panel-body .element[data-service-name]').addClass('hide');

            return false;
        },
    },
    remove: function () {

    },
    destroy: function () {
        var instance = Communication._instance;

        if (instance) {
            Global.removeEvents(this._events);
            Communication._instance = null;
        }
        delete this;
    }
}









/**************************************************************
*               CommunicationsBlock
***************************************************************/


var CommunicationsBlock = {
    _status: false,
    _instance: null,
    _nice_scroll: null,

    _update_store_data: null,
    _period: null,


    _visible : false,
    _name : null,
    _block_group : null,
    _block_object : null,
    _there_is_data : null,


    getInstance : function(status){
        if(status || !CommunicationsBlock._instance){
            if(CommunicationsBlock._instance){
                CommunicationsBlock._instance.destroy();
            }

            CommunicationsBlock._instance = this.createInstance();
        }
        return CommunicationsBlock._instance;
    },

    createInstance : function(){
        var Obj = function(){
            for(var key in CommunicationsBlock){
                this[key] = CommunicationsBlock[key];
            }
        }

        return CommunicationsBlock._instance = new Obj().constructor();
    },

    constructor: function () {
        this.events();

        return this;
    },
    setName : function(name){
        this._name = name;
        return this;
    },
    getName : function(){
        return this._name;
    },
    setBlockGroup : function(block_group){
        this._block_group = block_group;
        return this;
    },
    getBlockGroup : function(){
        return this._block_group;
    },
    setBlockObject : function(block_object){
        this._block_object = block_object;
        return this;
    },
    getBlockObject : function(){
        return $('.right-sidebar [data-type="content"] [data-name="'+this._name+'"]');
    },
    setThereIsData : function(there_is_data){
        this._there_is_data = there_is_data
        return this;
    },
    getThereIsData : function(){
        return this._there_is_data;
    },
    setPeriod : function (period) {
        this._period = period || CommunicationsBlock._period;
        return this;
    },
    setVisible : function(visible){
        this._visible = visible;
    },
    getVisible : function(){
        return this._visible;
    },
    setUpdateStoreData : function (bool) {
        this._update_store_data = bool || CommunicationsBlock._update_store_data;
        return this;
    },
    updateContent: function () {
        CommunicationsBlock
            .getInstance()
            .setPeriod(true)
            .setUpdateStoreData(true)
            .refreshChannels(true);

        return this;
    },
    events : function () {
        var actions = this.actions;

        this._events = [
            { parent: document, selector: '.right-sidebar .widget-container .ajax_content_reload', event: 'click', func: actions.onOpenAllChannel},
        ]

        Global.addEvents(this._events, {
            instance: this
        });

        return this;
    },

    actions : {
        // onOpenAllChannel
        onOpenAllChannel : function () {
            ProcessView.destroy();
        },
    },

    show : function(){
        this._visible = true;
        $(this.getBlockObject()).removeClass('hide');

        var instance = Communication.getInstance();

        if (instance) {
            instance.reloadChannel();
        }

        return this;
    },

    hide : function(){
        this._visible = false;
        $(this.getBlockObject()).addClass('hide');

        return this;
    },

    refreshChannels: function(check) {
        if(check == true){//проверять ли в каком модуле находимся
            if(!Communication.isCommunicationsEditView() && crmParams.page_interface_type !== PAGE_IT_COMMUNICATIONS){//усли мы не в модуле/карточке коммуникаций, то выходим
                return false;
            }
        }

        this.resizeNiceScroll()
            .update(this.activeMenu.ACTION_UPDATE_CURRENT_LIST);
    },

    setStatus : function(status){
        this._status = status;
        return this;
    },

    initScroll: function(parent){
        QuickViewPanel.setVerticalPosition();

        if (parent && parent._type == 'events') {} else this._nice_scroll.clear();

        this.resizeNiceScroll();
        return this;
    },

    resizeNiceScroll: function () {
        var time,
            _this = this,
            $channel = $(_this._block_object).find('.channels-list');

        this._nice_scroll.init();

        if (!this._nice_scroll._native || !Object.keys(this._nice_scroll._native).length) return this;

        this._nice_scroll._native.scrollend(function () {
            if(_this.getThereIsData() == false){
                return;
            }

            if ((parseInt(this.scrollvaluemax) - 55) <= (parseInt(this.scroll.y)) && _this._nice_scroll._status_balance_data) {
                _this._nice_scroll.setStatusLoadData(false);

                QuickViewPanel.getInstance().setStatusLoad(QuickViewPanel.VIEW_PRELOADER_IN_BOTTOM);

                if (!$channel.find('.small-preloading').length) {
                    $channel.append(Preloader._item_li_for_loading).find('>li.small-preloading').addClass('init-preloader set-preloader');
                }

                _this._nice_scroll.update();
                $channel.getNiceScroll(0).doScrollTop(999999);
                time = setTimeout(function () {
                    clearTimeout(time);
                    _this.update(_this.activeMenu.ACTION_UPDATE_WITH_NEXT);
                }, 1000);
            }
        });

        return this;
    },

    update: function(action){
        var request, time,
            active_com_menu_model = this.activeMenu,
            panel = QuickViewPanel.getInstance();

        active_com_menu_model._parent = this;
        this.self = active_com_menu_model;

        request = function () {
            active_com_menu_model
                .setAction(action)
                .run();
        }

        if (!this._period) {
            panel.setPreloaderToView();
            time = setTimeout(function () {
                clearTimeout(time);
                request()
            }, 100);
        } else {
            request();
        }

        this._period = CommunicationsBlock._period; // clear
    },

    updateMenuByPermissions: function(){
        CommunicationsBlock.checkModulePermission('rule_view',
            Communication.getId(),
            function () {
                QuickViewPanel.show();
            },
            function () {
                QuickViewPanel.close();
            },
            true);

        CommunicationsBlock.checkModulePermission('rule_create',
            Communication.getId(),
            function () {
                var btn_create_channel = $('.right-sidebar .edit_view_dnt-add');
                if (btn_create_channel.length > 0) {
                    btn_create_channel.show();
                }
            },
            function() {
                var btn_create_channel = $('.right-sidebar .edit_view_dnt-add');
                if (btn_create_channel.length > 0) {
                    btn_create_channel.hide();
                }
            }
        );
    },

    checkModulePermission: function(permission_const,copy_id,callback_true,callback_false,check_active_module){
        var _data = {
            permission_const: permission_const,
            copy_id: copy_id,
            check_active_module: check_active_module,
        };
        $.ajax({
            url: '/module/permission/CheckModulePermission/' + 3,
            data: _data,
            type: 'POST', async: false, dataType: "json",
            success: function (data) {
                if (data.status == true) {
                    callback_true();
                }
                else {
                    callback_false();
                }
            },
            error: function (xhr) {
                if (xhr.status) {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                }
            }
        });
    },

    activeMenu : {

        ACTION_UPDATE_CURRENT_LIST :       1,
        ACTION_UPDATE_WITH_NEXT :          2,

        _action : null,
        _this : null,
        _error : false,
        _ajax_result : null,
        _search_text : '',
        _limit : 20,
        _parent : null,
        _running : false,

        init : function(){
            if(this._error) return this;
            return this;
        },

        setError : function(){
            this._error = true;
            return this;
        },

        setAction : function(action){
            this._action = action;
            return this;
        },

        setThis : function(_this){
            if(typeof _this == 'undefined' || !_this) return this.setError();

            this._this = _this;
            return this;
        },

        setSearchText : function(text){
            if(typeof(text) != 'undefined' && text){
                this._search_text = text; //encodeURI(text);
            } else {
                this._search_text = '';
            }
            return this;
        },

        clearSearchText : function () {
            this._search_text = '';
        },

        getSearchText : function(){
            return this._search_text;
        },

        getChannelsObject : function () {
            return $('.element[data-type="communications_menu_channel"]');
        },

        getCurrentCount : function(){
            var channels_object = this.getChannelsObject();
            if(!channels_object){
                return 0
            }
            return channels_object.length;
        },

        getLimitItems : function(){
            var limit = this.getCurrentCount();
            if(!limit || limit < this._limit){
                limit = this._limit;
            }

            return limit;
        },

        run : function(){
            if(this._error) return this;
            if(this._running == true) return this;

            this._running = true;

            var ajax_data = this.getAjaxData();

            this.runAjax(ajax_data);

            return this;
        },

        //getAjaxData
        getAjaxData : function(){
            var result = {
                'block_name' : this._parent.getName(),
            };

            switch(this._action){
                case this.ACTION_UPDATE_WITH_NEXT:
                    result['limit'] = this.getCurrentCount() + this._limit;
                    result['offset'] = 1;
                    break;
                case this.ACTION_UPDATE_CURRENT_LIST:
                    result['limit'] = this.getLimitItems();
                    result['offset'] = 1;
                    break;
            }

            return result;
        },


        //runAjax
        runAjax : function(ajax_data){
            var _url,
                _this = this;

            if(this._error){
                this._running = false
                return this;
            }

            _url = this.getUrl();

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setData(ajax_data)
                .setUrl(_url)
                .setType('post')
                .setCallBackComplete(function(){
                    _this._running = false;
                })
                .setCallBackSuccess(function(data){
                    _this._running = false;
                    if(data.status){
                        _this.runAfterAjaxSuccess(data);
                    } else {
                        CommunicationsBlock.getInstance().setStatus(false);
                        this.preloader();
                    }
                })
                .send();
        },

        preloader: function (callback) {
            var $element = Preloader._item_li_for_loading,
                $channel = $('.channels-list'),
                $list = $channel.find('>li');

            if (CommunicationsBlock.getInstance()._status) {
                if ($list.length) {
                    $list.last().after($element);

                    var time = setTimeout(function () {
                        clearTimeout(time);

                        if (Global.isHandler(callback)) {
                            callback();
                        }
                    }, 200);
                }
            } else {
                $('.channels-list li.small-preloading').remove();
                this._parent._parent.getPreloader().hideFull();
            }
        },

        //runAfterAjaxSuccess
        runAfterAjaxSuccess : function(data){
            this._ajax_result = data;
            CommunicationsBlock.getInstance()._nice_scroll.setStatusLoadData(data.there_is_data)

            if(this._error){
                return;
            }

            switch (this._action){
                case this.ACTION_UPDATE_CURRENT_LIST:
                case this.ACTION_UPDATE_WITH_NEXT:
                    var _this = this;
                    CommunicationsBlock.getInstance().setStatus(false);
                    this.preloader();
                    _this.refreshChannelsHtml();
                    break;
            }

            if (this._action == this.ACTION_UPDATE_WITH_NEXT || this._parent._update_store_data) {
                this._parent._parent.setStoreData(data['html_result']);
                this._parent._update_store_data = CommunicationsBlock._update_store_data;
            }

            _this._parent.resizeNiceScroll();
        },

        refreshChannelsHtml: function(){
            var $channel = $('.right-sidebar .channels-list[data-copy_id="'+Communication.getId()+'"]'),
                html_result = this._ajax_result['html_result']

            $channel.html(html_result);

            if(html_result && !html_result.length){
                $channel.height(0);
            } else {
                QuickViewPanel.setVerticalPosition();
            }
        },

        addChannelsHtml: function(){
            this.getChannelsObject().last().after(this._ajax_result['html_result']);
        },


        removeChannels : function(){
            this.getChannelsObject().remove();

            return this;
        },


        getUrl : function(){
            var str = '';

            switch(this._action){
                case this.ACTION_UPDATE_WITH_NEXT :
                case this.ACTION_UPDATE_CURRENT_LIST :
                default : {
                    var url,
                        data = QuickViewPanel.getInstance().search.getParamAsJson();

                    str = '/quickView/getItems';
                    url = Url.createInstance().setUrl(str);

                    if (data.search.length) {
                        str = url.jsonToUrl(data).getUrl();
                    }
                }
            }

            return str;
        },


    },

    destroy : function () {
        CommunicationsBlock._instance = null;
        Events.removeHandler(Events.TYPE_EVENT_RESIZE, 'initScroll');
        NiceScroll.clear($('.right-stat-bar[data-name="communications"] .channels-list'));
    }
}

