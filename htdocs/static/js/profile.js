/**
 * Created by andrew on 9/13/17.
 */

var Profile = {
    _instance: null,
    _interface: 'Profile',
    _url: null,

    getInstance : function(){
        return this._instance;
    },

    createInstance : function(){

        var Obj = function(){
            for(var key in Profile){
                this[key] = Profile[key];
            }
        }

        return Profile._instance = new Obj().constructor();
    },
    setUrl: function (url) {
        this._url = url;

        return this;
    },
    constructor: function () {
        this.events()
            .setStyleOfDescription();

        return this;
    },

    actions: {
        onKeyUp: function(e) {

            if ($('[data-type="profile_personal_information"].active').length) {
                if (e.keyCode === 27 && $(e.target).find('.upload-modal').length) { // esc
                    modalDialog.hideAll()
                }
            }

            return true;
        },
        onProfileOpenTab : function (e) {
            e.preventDefault();
            var $this = $(this),
                url = $this.attr('href');

            if (!StartupGuide.getInstance()) {
                history.pushState(null, null, url);
            }
            AjaxContainers.cash.snapshot();

            // e.preventDefault();
            // var $this = $(this);

            // BackForwardHistory
            //     .getInstance()
            //     .setHash($this.attr('href'))
            //     .snapshot();
        },
        onSaveSmallFields: function() {
            var $editable, $editableField,
                $this = $(this),
                data = {};

            $editable = $this.closest('.editable-block');

            $editableField = $editable.find('.editable-field');
            $editableField.text($editable.find('input').val());

            data[$editableField.data('name')] = $editableField.text();

            $.ajax({
                'url': Global.urls.url_profile_personal_contact_save,
                'data': data, 'type' : "POST", 'dataType': 'json', async : false,
                success: function(data){

                    if (data.status == true) {
                        var time = setTimeout(function () {
                            clearTimeout(time);
                            $editable.find('.edit-dropdown').removeClass('open');
                            $editableField.css('opacity', '1');
                        }, 50);
                    }
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        },
        onChangeNotification: function(event){
            var _data = event.data.instance.getNotificationSettingData(),
                ajax = new Ajax();

            ajax
                .setAsync(false)
                .setUrl(Global.urls.url_profile_html_refresh)
                .setData(_data)
                .setType('POST')
                .setDataType('json')
                .setCallBackSuccess(function(data) {
                    if(data.status == true){
                        $('.element[data-type="profile_notification_settings"]').children().html($(data.html).children().html());
                        Global.initSelects();
                    }
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                })
                .send();

        },
        onSaveNotificationSettings: function(event){
            var guideParam = null,
                _data = event.data.instance.getNotificationSettingData();

            if (StartupGuide.getInstance()) {
                guideParam = {
                    visible : 'true'
                };
            } else {
                instanceGlobal.preloaderShow($(this), guideParam);
            }

            $.ajax({
                'url': Global.urls.url_profile_save,
                'data': _data,
                'type' : "POST", 'dataType': 'json', async : false,
                success: function(data){
                    if(data.status == false){
                        var ppi = $('.element[data-type="profile_notification_settings"]');

                        ppi.parent().append(data.html).find('.element[data-type="profile_notification_settings"]').addClass('active');
                        $('.selectpicker').selectpicker();
                        ppi.remove();

                    } else if(data.status == true){
                        var vars = {
                            'selector_content_box' : '#content_container',
                        }

                        instanceGlobal.contentReload
                            .createInstance()
                            .clear()
                            .setVars(vars)
                            .setUrl(Url.getCurrent())
                            .setPreloaderAutoHide(guideParam ? false : true)
                            .setCallBackComplete(function (data) {
                                if (data.status && guideParam) {
                                    StartupGuide.getInstance().runNext();
                                }
                            })
                            .loadThis()
                    }
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        },
        onSavePersonalInfo: function(){
            var _this = this;
            var element_data = {};

            instanceGlobal.preloaderShow($(this));

            $(_this).closest('.element[data-type="profile_personal_information"]').find('.element[data-field_type="text"]').each(function(i, ul){
                element_data[$(ul).data('type')] = $(ul).val();
            })

            element_data['removeBackground'] = $('.background-field').data('remove') || false;

            var _data = {
                'action' : 'personal_information',
                'data' : element_data
            };

            $.ajax({
                'url': Global.urls.url_profile_save,
                'data': _data,
                'type' : "POST", 'dataType': 'json', async : false,
                success: function(data){
                    if(data.status == false){
                        var ppi = $(_this).closest('.element[data-type="profile_personal_information"]');
                        ppi.parent().append(data.html).find('.element[data-type="profile_personal_information"]').addClass('active');
                        $('.selectpicker').selectpicker();
                        ppi.remove();
                    } else if(data.status == true){
//debugger;
                        var vars = {
                            'content_blocks_different' : [
                                    {'name':'main_top_user_menu', 'selector':'.element[data-type="user_menu"]'},
                                    {'name':'main_left_module_menu', 'selector':'.element[data-type="left_menu"]'}
                                ],
                            'selector_content_box' : '#content_container',
                        }
                        instanceGlobal.contentReload
                            .clear()
                            .setUrl(Url.getCurrent())
                            .setVars(vars)
                            .setCallBackSuccessComplete(function(){
                                crmParams.reloadFromServer();
                            })
                            .loadThis();
                    }
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        },
        onRestorePassword: function(){
            var element = document.getElementById("profile-loader");
            element.classList.remove("hidden");
            var _this = this;
            var element_data = {};
            instanceGlobal.preloaderShow($(this));
            element_data['email'] = $(_this).data('email');
            element_data['removeBackground'] = $('.background-field').data('remove') || false;

            var _data = {

                'UsersModel' : element_data
            };

            $.ajax({
                'url': Global.urls.url_restore,
                'data': _data,
                'type' : "POST", 'dataType': 'json', async : true,
                success: function(data){
                    $('.profile-loader').addClass('hidden')
                    if(data.status == false){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                    } else if(data.status == true){
//debugger;
                        Message.show([{'message': data.html }], true);
                    }
                },
                error: function(){
                    $('.profile-loader').addClass('hidden')
                    Message.show([{ 'message': Global.urls.url_ajax_error }], true);
                }
            });
        },
        onRemoveBackground: function (e){
            $('.background-field').data('remove', true);
            $(e.target).closest('.remove').remove();
        },
        onSaveApi: function(event){
            var _this = this,
                _data = event.data.instance.getApiData();

            instanceGlobal.preloaderShow($(this));

            setTimeout(function () {
                $.ajax({
                    'url': Global.urls.url_profile_save,
                    'data': _data,
                    'type' : 'POST', 'dataType': 'json', async : false,
                    success: function(data){
                        var ppi = $(_this).closest('.element[data-type="profile_api"]');

                        if(data.status == false){
                            ppi.parent().append(data.html).find('.element[data-type="profile_api"]').addClass('active');
                            $('.selectpicker').selectpicker();
                            ppi.remove();
                        }

                        ppi.find('.b-spinner').remove();
                        ppi.find('.profile_form').removeClass('init-preloader set-preloader');
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                    }
                });
            }, 300);

        },
        onApiRegenerateToken : function(){
            var _this = this;

            $.ajax({
                'url': Global.urls.url_profile_api_regenerate_token,
                'type' : 'POST', 'dataType': 'text', async : false,
                success: function(api_key){
                    $(_this).closest('.element[data-type="profile_api"]').find('.element[data-type="api_key"]').val(api_key);
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        },
        onRemoveAvatar: function(){
            var _this = this;
            Global.Files.fileDeleteAvatar(function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else {
                    $(_this).closest('.element[data-type="profile"]').html(data.view.avatar_140);
                    $('.element[data-type="main_top_profile_menu_user"]').find('.list-view-avatar').after(data.view.avatar_32).remove();
                    imagePreview(true);
                    NiceScroll.init();
                    setCheckboxHeight();
                }
            });
        },
        onClickProfileActivityMore: function(event){
            event.data.instance.activityLoadMore();
        },
    },
    events: function () {
        var path = this.actions;

        this._events = [
            { parent: document, selector: 'body:not(.guide) .element[data-type="profile"] .nav a', event: 'click', func: path.onProfileOpenTab},
            { parent: document, selector: '.element[data-type="profile-activity-more"]', event: 'click', func: path.onClickProfileActivityMore },
            { parent: document, selector: '.element[data-type="remove_image"][data-name="profile"]', event: 'click', func: path.onRemoveAvatar},
            { parent: document, selector: 'body', event: 'keyup', func: path.onKeyUp},
            //personal_information
            { parent: document, selector: '.element[data-type="personal_information_save"]', event: 'click', func: path.onSavePersonalInfo},
            { parent: document, selector: '.element[data-type="restore-password"]', event: 'click', func: path.onRestorePassword},
            //notification_settings save
            { parent: document, selector: '.element[data-type="notification_settings_save"]', event: 'click', func: path.onSaveNotificationSettings},
            //notification_settings change
            { parent: document, selector: '.element[data-type="setting_notification"], .element[data-type="sending_method"], .element[data-type="notifications_modules"]', event: 'change', func: path.onChangeNotification},
            { parent: document, selector: '.profile-contacts .edit_view-save-input-hidden', event: 'click', func: path.onSaveSmallFields},
            //api save
            { parent: document, selector: '.element[data-type="api_save"]', event: 'click', func: path.onSaveApi},
            { parent: document, selector: '.background-field .remove .element', event: 'click', func: path.onRemoveBackground},
            { parent: document, selector: '.element[data-type="api_regenerate_token"]', event: 'click', func: path.onApiRegenerateToken}
        ];

        Global.addEvents(this._events, {
            instance: this
        });

        return this;
    },
    setStyleOfDescription: function() {
        var $profile = $('.profile-information');

        $profile.css({
            height: $profile.height() - 28 +'px'
        });
    },
    setEnableNotification: function(){
        $('.element[data-type="setting_notification"]').val(1).trigger('change');
    },
    getNotificationSettingData: function(){
        var element_data = {};

        $('.element[data-type="profile_notification_settings"]').find(
            '.element[data-field_type="text"],'+
            '.element[data-field_type="check"],'+
            '.element[data-field_type="radio"],'+
            '.element[data-field_type="check_array"]').each(function(i, ul)
        {

            switch($(ul).data('field_type')){
                case 'text' :
                    element_data[$(ul).data('type')] = $(ul).val();
                    break;
                case 'radio' :
                case 'check' :
                    if($(ul).prop("checked") == false) return true;

                    element_data[$(ul).data('type')] = ($(ul).val());
                    break;
                case 'check_array' :
                    if($(ul).prop("checked") == false) return true;

                    if(element_data[$(ul).data('type')])
                        element_data[$(ul).data('type')].push($(ul).val())
                    else
                        element_data[$(ul).data('type')] = [$(ul).val()];

                    break;
            }

        })

        var _data = {
            'action' : 'notification_settings',
            'data' : element_data
        };

        return _data;
    },
    getApiData : function(){
        var element_data = {};

        $('.element[data-type="profile_api"]').find(
            '.element[data-field_type="text"],'+
            '.element[data-field_type="check"]').each(function(i, ul)
        {

            switch($(ul).data('field_type')){
                case 'text' :
                    element_data[$(ul).data('type')] = $(ul).val();
                    break;
                case 'radio' :
                case 'check' :
                    element_data[$(ul).data('type')] = $(ul).prop("checked") ? "1" : "0";
                    break;
            }

        })

        var _data = {
            'action' : 'api',
            'data' : element_data
        };

        return _data;
    },
    activityLoadMore : function(){
        var data,
            activity_data = $('.element[data-type="profile-activity-data"]');

        data = {
            'last_date' : activity_data.data('date'),
            'page' : activity_data.data('page'),
            'notification_position' : activity_data.data('notification_position')
        };

        $.get(this._url, data, function(data) {
            if(data.status == true){
                HeaderNotice.addLinkActions(data.link_actions);
                $('.prof_activity .notification:last').after(data.html);
                imagePreview(true);
            }
            if(data.more == true){
                activity_data.data('page', data.page);
                activity_data.data('date', data.date);
                activity_data.data('notification_position', data.notification_position);
            } else {
                $('.prof_activity .load_more').empty();
            }
        }, 'json');
    },
    destroy: function () {
        var instance = Profile.getInstance(); //  STATIC || instance

        if (instance) {
            Global.removeEvents(instance._events);
        }
        Profile._instance = null; // static - instance can be only one
    }

}
