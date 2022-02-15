/**
 * Created by andrew on 10/30/17.
 */

var StartupGuide = {
    //common properties
    _interface: 'StartupGuide',

    _vars : {},
    _instance: null,
    _active_step: null,
    _order_step: null,
    _callback: {
        other: null
    },
    _max_step: 5,
    params: null,

    active_key: null,
    TYPE_KEY_BTN_PREV: 1,
    TYPE_KEY_BTN_NEXT: 2,

    getInstance : function(){
        return StartupGuide._instance;
    },
    setParams : function (params) {
        this.params = params;

        return this;
    },
    createInstance : function(){
        var Obj = function(){
            for(var key in StartupGuide){
                this[key] = StartupGuide[key];
            }
            this.stepForLeft = ['step2'];
        }

        return StartupGuide._instance = new Obj();
    },
    importModel : function (data) {

        for(var key in data){
            if (typeof data[key] != 'object') {
                this[key] = data[key];
            }
        }

        this.clearViewAfterImport()

        return this;
    },
    isOpen : function () {
      return $('.modal .step1').length ? true : false;
    },
    nextOrder: function () {
        this._order_step++;

        return this;
    },
    reLoadParams: function () {
        var instance = StartupGuide._instance;

        if (instance) {
            this.apiGetParams(function (data) {
                instance
                    .setParams(data.steps)
                    .calcDots(instance.getActiveStep())
            });
        }

        return this;
    },
    clearViewAfterImport: function () {
        $('body').removeClass('step1 step2 step3 step4 step5');
        modalDialog.hideAll();
        $('.show-shadow').removeClass('show-shadow');

        return this;
    },
    snapshot: function (instance) {
        sessionStorage.setItem(this._interface, true);
        window.backForward = true;
        return this;
    },
    //static
    isStorage : function () {
        return sessionStorage.getItem(StartupGuide._interface) == 'true' ? true : false;
    },
    clearStorage : function () {
        sessionStorage.removeItem(StartupGuide._interface);
        return this;
    },
    calcDots: function (active_step) {
        this._order_step = Number(active_step.substring(4));

        var delta, order,
            _this = this,
            array = [],
            steps = this.getDisableSteps();

        $.each(Object.keys(this.params), function (key, value) {
            if (value.indexOf('step') ==0) {
                array.push(_this.params[value]);
            }
        });

        order = $.inArray(false, array);
        if (order < 0 || (order+1) > this._order_step) {
            delta = this._order_step;
        } else {
            delta = Math.abs(steps - this._order_step)
        }

        this._order_step = delta || 1;

        return this;
    },
    apiGetParams: function ($callback) {
        AjaxObj
            .createInstance()
            .setAsync(true)
            .setUrl('/ajax/StartupGuideActionRun?action=steps_list&vars[only_status]=1')
            .setType('GET')
            .setTimeOut(crmParams.global.ajax.get_url_timeout)
            .setCallBackSuccess(function(data) {
                if(data.status == true){
                    if ($.isFunction($callback)) {
                        $callback(data);
                    }
                }
            })
            .send()

        return this;
    },
    init : function(params){
        if (this.isStorage()) {
            this.clearStorage();
            return;
        }

        if(!params.startup_guide) return;

        history.pushState(null, null, location.pathname);
        Url.appendParam('#startup_guide=1');
        history.pushState(null, null, null);

        window.backForward = true;

        instanceGlobal = new _Global();

        this.apiGetParams(function (data) {
            StartupGuide
                .createInstance()
                .setParams(data.steps)
                .calcDots(params.startup_guide.active_step)
                .setVars(params.startup_guide.vars)
                .run(params.startup_guide.active_step)
                .setCallBackExit(function(){
                    var step = 'step5',
                        dialog,
                        _vars = this.getVars(step),
                        videoContent = this.getVars('finish');

                    window.backForward = false;

                    instanceGlobal.preloaderShow($('.nav').find('>li>a').first());

// constructor: {"active_step":"step1", "role_id":2, "deals_copy_id":260, "last_step":{ "action_after":["actionSwitchMenu","actionShowLeftMenu"], "action_run": "loadPage", "index" : "constructor", "selector_content_box" : "#content_container"}}
// default: { "action_after":["actionSwitchMenu","actionHideLeftMenu"], action_run : "loadModule" module : copy_id : 260 destination : "listView" params : {this_template: 0} __proto__ : Object selector_content_box : "#content_container"}
// reports: {"active_step":"step1","role_id":2,"deals_copy_id":260,"last_step":{"action_run":"loadModule","module":{"copy_id":"8","destination":"listView","params":{"this_template":"0"}},"selector_content_box":"#content_container"}}

                    if (!_vars['content_reload_end']) {
                        _vars['content_reload_end'] = {
                            'vars': JSON.parse(_vars['last_step']),
                            'key': 145739272258
                        };
                    }

                    dialog = function () {
                        modalDialog
                            .createInstance()
                            .setParentClass('center-in-work-place')
                            .setShowBackdrop(false)
                            .setContent(videoContent)
                            .setCallbackSuccess( function () {
                                setCheckboxHeight();
                            })
                            .show()
                    }

                    instanceGlobal
                        .contentReload
                        .createInstance()
                        .clear()
                        .setObject($('#container')[0])
                        .setActionKey(_vars['content_reload_end']['key'])
                        .setVars(_vars['content_reload_end']['vars'])
                        .setCallBackSuccessComplete(function (data){
                            if(data.status == true){
                                delete crmParams.startup_guide;

                                var id = _vars['content_reload_end'].vars.module && _vars['content_reload_end'].vars.module.id,
                                    report = Reports.getInstance(true);

                                if (id && report) {
                                    var preloader = MainMenu.getPreloader(),
                                        instanceContent = ContentReload.createInstance();

                                    Global
                                        .getInstance()
                                        .setPreloader(preloader)
                                        .setContentReloadInstance(instanceContent);

                                    iPreloader.implements.call(instanceContent);

                                    instanceContent
                                        .reDefinition()
                                        .setPreloader(preloader)
                                        .showPreloader();

                                    report.open(null, id, function () {
                                        dialog();
                                    })
                                } else {
                                    dialog();
                                }

                            }
                        })
                        .run();
                });
        })

        return this;
    },

    setCallBackExit : function (callback) {
        StartupGuide._callback_exit = callback;
        return this;
    },

    clearEvents: function(step) {
        if ($.isNumeric(step)) {
            var instance = this.events['initStep' + step].instance;

            if (instance) {
                if (instance._events) {
                    Global.removeEvents(instance._events);
                }
                instance.destroy();
            }
        }
        return this;
    },

    getActiveStep : function(){
        var st =  'step1';

        if(this._active_step){
            st =  'step' + this._active_step;
        }
        return st;
    },

    setVars : function(vars){
        StartupGuide._vars = vars;
        return this;
    },

    getVars : function(element_key){
        return StartupGuide._vars[element_key];
    },

    run : function(step){
        if(this._vars == false){
            return this;
        }

        if (!step) {
            step = this.getActiveStep();
        }
        //step = 'step1'; //TODO: remove;

        switch(step){
            case 'step1':
                this.runStep1()
                break;
            case 'step2':
                this.runStep2()
                break;
            case 'step3':
                this.runStep3()
                break;
            case 'step4':
                this.runStep4();
                break;
            case 'step5':
                this.runStep5();
                break;
            default:
                break;
        }

        return this;
    },

    runNext : function(event){
        var _this = (!event) ? this : event.data.instance;

        _this.active_key = StartupGuide.TYPE_KEY_BTN_NEXT;
        _this.clearEvents(_this._active_step);
        //BackForwardHistory.setStateHistory(false);
        if (event) {
            _this.nextOrder();
        }

        if(_this._active_step == _this._max_step){
            _this._active_step = null;
            _this.runExit();
            return;
        }

        _this._active_step++;

        _this.run(_this.getActiveStep());
        _this.actions.memoriesActiveStep();
    },

    runPrevious : function(event){
        var _this = (!event) ? this : event.data.instance;

        _this.active_key = StartupGuide.TYPE_KEY_BTN_PREV;

        if (event) {
            _this._order_step--;
        }

        _this.clearEvents(_this._active_step);

        if(_this._active_step == 1){
            return;
        }

        _this._active_step--;

        if (_this._active_step <= 0) {
            _this._active_step = 1;
        }

        _this.run(_this.getActiveStep());
        _this.actions.memoriesActiveStep();
    },

    runExit : function(){
        var _this = this;

        this.actions.flush(function(){
            if ($.isFunction(StartupGuide._callback_exit)) {
                StartupGuide._callback_exit();
                _this.destroy();
            }
        });

    },

    setView : function () {
        var step = this.getActiveStep(),
            $body = $('body').addClass('guide'),
            vars = this.getVars(step),
            $panelGuide = $('.guide-panel');

        if (!$panelGuide.length) {
            $body.append(vars.elements.panel);
        } else {
            $panelGuide.find('.guide-sidebar').remove();
            $panelGuide.append($(vars.elements.panel).html());
        }

        modalDialog.hide();
        if(vars.elements.dialog){
            modalDialog
                .createInstance()
                .setStatusAllowToCloseOutSide(false)
                .show(vars.elements.dialog);
        }

        if ($.inArray(step, this.stepForLeft) >= 0) {
            $body.addClass('is-left');
        } else {
            $body.removeClass('is-left');
        }

        return this;
    },

    loadUrl : function(callback, param){
        var step = this.getActiveStep(),
            _this = this,
            _vars = this.getVars(step);

        this.showPreloader();
        NiceScroll.clear()

        if (!_vars['content_reload']) {
            if (this.active_key == StartupGuide.TYPE_KEY_BTN_NEXT) {
                this.runNext();
            }
            if (this.active_key == StartupGuide.TYPE_KEY_BTN_PREV) {
                this.runPrevious();
            }
            return;
        }

        instanceGlobal
            .contentReload
            .createInstance()
            .clear()
            .setObject($('#container')[0])
            .setPreloaderAutoHide(param && param.hasOwnProperty('preloader_auto_hide') ? param.preloader_auto_hide : true)
            .setActionKey(_vars['content_reload']['key'])
            .setVars(_vars['content_reload']['vars'])
            .setCallBackSuccessComplete(function (data){
                if(data.status == true){
                    callback(data);
                    _this.afterLoad();
                }
            })
            .run();
    },
    getDisableSteps: function () {
        var _this = this,
            count = 0;

        $.each(Object.keys(this.params), function (key, value) {
            if (value.indexOf('step') == 0 && _this.params[value] == true) {
                count++;
            }
        });
      return 5 - count;
    },
    afterLoad : function() {
        var delta,
            $dots = $('.guide-sidebar [data-type="dot"] li'),
            _this = this,
            count = 0;

        delta = this.getDisableSteps();

        for (var i =0; i< delta; i++){
            $dots.last().remove();
        }

        $dots.removeClass('active');
        for (var i=0; i < this._order_step; i++){
            $dots.eq(i).addClass('active');
        }

        return this;
    },
    runStep1 : function(){
        var _this = this;
        this._active_step = 1;

        this.setBackSide(true);

        this.loadUrl(function(data){
            _this
                .clearEvents(_this._active_step)
                .setEvents()
                .setView()
                .setBackSide(false)
                .hidePreloader();

            _this.events.initStep1(_this);
            _this.snapshot(_this.events.initStep1.instance);
        });
    },

    runStep2 : function(){
        var _this = this;

        this._active_step = 2;
        this.setBackSide(true)
            .loadUrl(function(data){
            _this
                .setEvents()
                .setView()
                .setBackSide(false)
                .hidePreloader();

            _this.events.initStep2(_this);
            _this.snapshot(_this.events.initStep2.instance);
        });
    },

    runStep3 : function(){
        this._active_step = 3;

        var time,
            $body = $('body'),
            _this = this;

        this.setBackSide(true);

        this.loadUrl(function(data){
            _this.setEvents();
            _this.setView();

            $body.addClass('hidden-dialog');

            _this.setBackSide(false)
                .hidePreloader();
            _this.initSelectColor();

            var $submenu = $('.step3 ul.element_field_type_params_select');

            $submenu.toggleClass('hide');
            $submenu.find('.selectpicker').selectpicker({style: 'btn-white'});

            $submenu.find('input.element_params[data-type="select_option"]').each(function(){
                var $this = $(this),
                    color = $this.data('color'),
                    $label = $this.closest('li').find('div.select-color .dropdown-menu .label[data-color="'+color+'"]'),
                    $labelClone = $label.clone();

                $this.closest('li').find('div.select-color button .label').parent().html($labelClone);
                $label.closest('ul').find('.selected').removeClass('selected');
                $label.closest('li').addClass('selected');
            });

            time = setTimeout(function () {
                var $dialog = $('.modal-dialog.step3');

                clearTimeout(time);
                $('.step3 ul.sub-menu').removeClass('hide');
                $dialog.attr('style', '');
                $body.removeClass('hidden-dialog');
                Global.setWindowInCenter($dialog);
            }, 100);

            _this.events.initStep3(_this);
            _this
                .listSorting()
                .hidePreloader()
                .snapshot(_this.events.initStep3.instance);
        });
    },

    runStep4 : function(){
        var param,
            _this = this;

        this._active_step = 4;
        this.setBackSide(true);

        param = {
            preloader_auto_hide : false
        };

        this.loadUrl(function(data){
            _this.setEvents();
            _this.setView()
                 .hidePreloader();

            Profile
                .createInstance()
                .setEnableNotification();

            $('a[href="#notification_settings"]').trigger('click');
            _this.events.initStep4(_this);

            _this.setBackSide(false)
                .hidePreloader()
                .snapshot(_this.events.initStep4.instance);
        }, param);
    },

    runStep5 : function(){
        var _this = this;
        this._active_step = 5;

        modalDialog.hide();
        this.setBackSide(true);

        this.loadUrl(function(data){
            _this.setEvents()
                .setView();

            _this.events.initStep5(_this);
            _this.setBackSide(false)
                .hidePreloader()
                .snapshot(_this.events.initStep5.instance);
        });
    },

    setEvents: function () {
        this._events = [
            { parent: document, selector: '.guide-sidebar [type="button"]', event: 'click', func: this.runPrevious},
            { parent: document, selector: '.guide-sidebar [type="submit"]', event: 'click', func: this.runNext}
        ]

        Global.addEvents(this._events, {
            instance: this
        });

        return this;
    },

    initSelectColor : function(){
        var step = this.getActiveStep();

        var $submenu = $('.'+step+' ul.element_field_type_params_select');

        $submenu.find('.selectpicker').selectpicker({style: 'btn-white'});

        $(this).closest('.settings').css('z-index', '30');

        $submenu.find('input.element_params[data-type="select_option"]').each(function(){
            var $this = $(this),
                color = $this.data('color'),
                $label = $this.closest('li').find('div.select-color .dropdown-menu .label[data-color="'+color+'"]'),
                $labelClone = $label.clone();

            $this.closest('li').find('div.select-color button .label').parent().html($labelClone);
            $label.closest('ul').find('.selected').removeClass('selected');
            $label.closest('li').addClass('selected');
        });

        return this;
    },

    onClose: function (event) {
        var $target = $(event.target);

        if ($target.is('.modal')) {
            $('body').removeClass('guide');
            $('.guide-panel').remove();
        }

    },

    listSorting : function () {
        var $subMenu = $('.step3 .sub-menu');

        $subMenu.sortable({
            items: '>li:not(.btn-element)',
            placeholder: "ui-state-highlight",
            delay: 150
        });
        return this;
    },
    //events
    events: {
        base : {
            setShadow : function (status) {
                 var $menu = $('.fixed-top'),
                     $notification = $('#notification_settings');

                 if (status) {
                     $menu.addClass('show-shadow');
                     $notification.find('.email-notification').addClass('show-shadow');
                     $('.right-sidebar').addClass('show-shadow');
                 } else {
                     $('.show-shadow').removeClass('show-shadow');
                 }
             },
            destroy : function () {
                $('.guide-sidebar').empty();
            }
        },
        initStep1: function(_this){
            var instance, step1;

            step1 = function(){
                this.events();
            };
            step1.prototype = Object.create(StartupGuide.events.base);
            step1.prototype.events = function () {
                this._events = [
                    { parent: document, selector: '.guide .step1 .btn-add', event: 'click', func: _this.actions.step1Save},
                    { parent: document, selector: '.guide .step1 .btn-skip', event: 'click', func: _this.actions.step1Skip},
                ]
            }

            step1.prototype.destroy = function () {
                Global.removeEvents(this._events);
            }

            $('.guide [type="button"]').addClass('hide');

            instance = new step1();
            this.initStep1.instance = instance;

            Global.addEvents(instance._events, {
                'instance': _this
            });
        },

        initStep2: function(_this){
            var instance, step2,
                step = _this.getActiveStep();

            step2 = function(){
                $('.guide').addClass('step2');
                $('html').removeClass('overflowHidden');

                this.resize();

                 Events
                    .createInstance()
                    .setType(Events.TYPE_EVENT_RESIZE)
                    .setKey('guideStep2Resize')
                    .setHandler(this.resize)
                    .run();

                this.setShadow(true);
            };
            step2.prototype = Object.create(StartupGuide.events.base);
            step2.prototype.resize = function () {
                var vars, $element, $arrow;

                vars = _this.getVars(step);
                $element = $('.crm-table-wrapper select.module_set_status').first();
                $arrow = $('.step2_arrow');

                $('#main-content').addClass('show-shadow');

                if (!$arrow.length) {
                    $element.after(vars.elements.content);
                }
            };
            step2.prototype.destroy = function () {
                $('body, html').scrollTop(0);
                Events.removeHandler({ key: 'guideStep2Resize', type: Events.TYPE_EVENT_RESIZE});

                $('.step2_arrow').remove();
                $('.show-shadow').removeClass('show-shadow');
                $('.guide').removeClass('step2');

                this.setShadow(false);
            }

            $('.guide [type="button"]').removeClass('hide');

            instance = new step2();
            this.initStep2.instance = instance;
        },

        initStep3: function(_this){
            var step3 = function(){
                this.events();
                $('.guide').addClass('step3');

                // Events
                //     .createInstance()
                //     .setType(Events.TYPE_EVENT_RESIZE)
                //     .setKey('guideStep3Resize')
                //     .setHandler(function () {
                //         $('.step2_arrow').remove();
                //     })
                //     .run();
            };
            step3.prototype = Object.create(null);
            step3.prototype.events = function () {
                this._events = [
                    { parent: document, selector: '.guide .step3 .add-field', event: 'click', func: this.onAddSelectItem},
                    { parent: document, selector: '.guide .step3 .selectpicker li', event: 'click', func: this.onSelectColor},
                    { parent: document, selector: '.guide .step3 .todo-remove', event: 'click', func: this.onRemoveRow},

                    { parent: document, selector: '.guide .step3 .element[data-type="save"]', event: 'click', func: _this.actions.step3Save},
                ]
            };

            step3.prototype.onRemoveRow = function (event) {
                var $this = $(this),
                    $list = $this.closest('ul');

                $this.closest('li').remove();

                if ($list.find('>li').length == 1) {
                    $list.find('>li .todo-remove').addClass('hide');
                }
            };
            step3.prototype.onSelectColor = function (event) {
                var $target = $(event.target),
                    color = $target.data('color'),
                    $blockColor = $target.closest('.select-color');

                $blockColor.closest('li').find('input').attr('data-color', color);
                $blockColor.find('button [data-color]').attr('data-color', color);
            };
            step3.prototype.onAddSelectItem = function (event) {
                var $subMenu = $('.step3 .sub-menu'),
                    vars = event.data.instance.getVars('step3');

                $subMenu.find('.btn-element').before(vars.elements.select_item);

                event.data.instance
                    .initSelectColor()
                    .listSorting();
            };

            step3.prototype.destroy = function () {
                $('.guide').removeClass('step3');
                //Events.removeHandler({ key: 'guideStep3Resize', type: Events.TYPE_EVENT_RESIZE});
                Global.removeEvents(this._events);
            }

            var instance = new step3();
            this.initStep3.instance = instance;

            Global.addEvents(instance._events, {
                'instance': _this
            });
        },

        initStep4: function(_this){
            var instance, step4, vars,
                step = _this.getActiveStep(),
                $notification = $('#notification_settings');

            step4 = function(){
                $('.guide').addClass('step4');
                this.setShadow(true);
                $('#main-content').height($('html').height());
            };
            step4.prototype = Object.create(null);
            step4.prototype.setShadow = function (status) {
                var $menu = $('.fixed-top');

                if (status) {
                    $menu.addClass('show-shadow');
                    $notification.find('.email-notification').addClass('show-shadow');
                } else {
                    $('.show-shadow').removeClass('show-shadow');
                }
            };
            step4.prototype.destroy = function () {
                $('.guide').removeClass('step4');
                this.setShadow(false);

                Url.replace('#notification_settings','');

                $('.step4_arrow_save_change').remove();
                $('.step4_arrow_email').remove();
                $('.step4_arrow_period').remove();
                $('#main-content').attr('style', '');
            }

            vars = _this.getVars(step);

            $notification.find('.email-notification').append(vars.elements.content);

            instance = new step4();
            this.initStep4.instance = instance;
        },

        initStep5: function(_this){
            var step5, vars, instance,
                step = _this.getActiveStep();

            step5 = function(){
                $('.guide').addClass('step5');
                $('html').addClass('overflowHidden');

                this.resize();

                Events
                    .createInstance()
                    .setType(Events.TYPE_AJAX_COMPLETE)
                    .setKey('guideStep5')
                    .setHandler(this.resize)
                    .run();

                this.setShadow(true);
            };
            step5.prototype = Object.create(StartupGuide.events.base);
            step5.prototype.resize = function () {
                var $element = $('.profile-pic .file-block');

                $('.step5_arrow').css({
                    left: (parseInt($element.width()) / 2) + 90
                })

                var $profile = $('.profile-pic [data-type="profile"]'),
                    $element = $profile.find('.file-block > .upload_link_contact_image');

                if ($element.length) {
                    $element.appendTo($profile.find('a.name'));
                }
            };
            step5.prototype.destroy = function () {
                $('.guide').removeClass('step5');
                $('.step5_arrow').remove();
                this.setShadow(false);

                Events.removeHandler(Events.TYPE_AJAX_COMPLETE, 'guideStep5')
            }

            vars = _this.getVars(step);
            $('.profile-pic [data-type="profile"]').addClass('show-shadow').after(vars.elements.content);

            instance = new step5();
            this.initStep5.instance = instance;
        },

    },

    setBackSide: function (status) {
        if (status) {
            var $backSide = $('[data-name="back-side"]');

            if (!$backSide.length) {
                $('body').append('<div data-name="back-side"></div>');
            };

            $('.show-shadow').removeClass('show-shadow');
            modalDialog.hide();
        } else {
            $('[data-name="back-side"]').remove();
        }

        return this;
    },
    showPreloader: function() {
        var $panel = $('.guide-panel');

        Preloader
            .createInstance()
            .setWhereContentHide(Preloader.GUIDE)
            .setPlaceForSpinner($panel)
            .run();

        return this;
    },
    hidePreloader: function() {
        Global.spinner.remove($('.guide-panel'));
        Preloader.destroy();

        return this;
    },
    //actions
    actions : {

        //step1Save
        step1Save : function(event){
            var data, ajax,
                instance = event.data.instance,
                _this = this,
                attributes = {};

            $(_this).closest('.modal-dialog').find('.element[data-type="user"]').each(function(i, ul){
                var name = $(ul).data('name');
                attributes[name] = $(ul).val();
                $(ul).attr('value', attributes[name]);
            })

            data = {
                'action' : 'step1_save',
                'vars' : attributes,
            };

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setData(data)
                .setUrl('/ajax/startupGuideActionRun')
                .setType('post')
                .setCallBackSuccess(function(data){
                    $(_this).closest('.modal-dialog').find('.errorMessage').text('');

                    if(data.status == true){
                        $(_this).closest('.modal-dialog').find('.element[data-type="user"]').attr('disabled', 'disabled');
                        $(_this).attr('disabled', 'disabled');

                        var dialog_html = $('#modal_dialog1').html();
                        StartupGuide._vars.step1.elements.dialog = dialog_html;

                        instance
                            .nextOrder()
                            .runNext();
                    } else
                    if(data.status == false){
                        if(data.messages){
                            $.each(data.messages, function(field_name, message){
                                $(_this).closest('.modal-dialog').find('.element[data-name="'+field_name+'"]').closest('.column').find('.errorMessage').text(message);
                            })
                        }
                    }
                })
                .send();
        },
        step1Skip : function (e) {
            e.data.instance.runNext(e);
        },
        //step3Save
        step3Save : function(event){
            var instance = event.data.instance,
                _this = this,
                attributes = [];

            $(_this).closest('.modal-dialog').find('ul.sub-menu>li input.element_params[data-type="select_option"]').each(function(i, ul){
                var attribute = {
                    'id' : $(ul).data('id'),
                    'remove' : $(ul).data('remove'),
                    'finished_object' : $(ul).data('finished_object'),
                    'sort' : $(ul).data('sort'),
                    'slug' : $(ul).data('slug'),
                    'color' : $(ul).data('color'),
                    'title' : $(ul).val(),
                }
                attributes.push(attribute);
            })

            var data = {
                'action' : 'step3_save',
                'vars' : attributes,
            };

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setData(data)
                .setUrl('/ajax/startupGuideActionRun')
                .setType('post')
                .setCallBackSuccess(function(data){
                    $(_this).closest('.modal-dialog').find('.errorMessage').text('');

                    if(data.status == true){
                        if(data.attributes){
                            $('.modal-dialog.step3 ul.sub-menu>li').each(function(i, ul){
                                var input = $(ul).find('input.element_params[data-type="select_option"]');
                                if(input.data('id') == false){
                                    input
                                        .data('id', data.attributes[i].id)
                                        .attr('data-id', data.attributes[i].id);
                                }
                                input.attr('value', data.attributes[i].title);
                            })
                        }

                        var dialog_html = $('#modal_dialog1').html();
                        StartupGuide._vars.step3.elements.dialog = dialog_html;

                        instance.runNext();
                    } else
                    if(data.status == false){
                    }
                })
                .send();
        },

        //flush
        flush : function(callback){
            var data = {
                'action' : 'flush',
            };

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setData(data)
                .setUrl('/ajax/startupGuideActionRun')
                .setType('post')
                .setCallBackSuccess(function(data){
                    if(data.status == true){
                        callback()
                    }
                })
                .send();
        },


        //actionMemoriesActiveStep
        memoriesActiveStep : function(){
            var _this = StartupGuide.getInstance();
            var data = {
                'action' : 'memories_active_step',
                'vars' : _this.getActiveStep()
            };

            AjaxObj
                .createInstance()
                .setAsync(true)
                .setData(data)
                .setUrl('/ajax/startupGuideActionRun')
                .setType('post')
                .setCallBackSuccess(function(data){
                })
                .send();
        },
    },
    remove : function () {
        $('body').removeClass('guide step1 step2 step3 step4 step5');
        $('.guide-sidebar').remove();
        $('.guide-panel').remove();
        $('.show-shadow').removeClass('show-shadow');
        $('.step2_arrow').remove();

        modalDialog.hideAll();
    },
    destroy: function () {
        var instance = StartupGuide._instance;

        this.remove();
        if (instance) {
            Global.removeEvents(instance._events)
            StartupGuide._instance = null;
        }
        delete this;
    }
}
