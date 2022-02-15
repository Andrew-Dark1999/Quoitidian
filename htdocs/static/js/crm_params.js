
var ENUM_EDITOR={

}

var crmParams = {
    FORMAT_DATE: 'YYYY-MM-DD HH:mm:ss',
    FORMAT_DATE_END_RU: 'DD.MM.YYYY HH:mm',
    FORMAT_DATE_END_EN: 'MM/DD/YYYY hh:mm A',
    FORMAT_DATE_END_ES: 'DD.MM.YYYY HH:mm',

    params: null,
    page_interface_type : null,
    page_name : null,
    global : {},
    templates : null,
    background: null,
    message_dialog_upload_select_file: null,
    activity_editor: null,

    setParams : function(params){
        localStorage.clear();
        localStorage.setItem('loadParams', JSON.stringify(params));

        this.background = params['template_design']['background'];

        setTimeout(function () {
            Global && Global.changeBackground();
        }, 300);

        this.activity_editor = params.edit_view.activity_editor;
        this.message_dialog_upload_select_file = params.message_dialog_upload_select_file;
        crmParams.global = params.global;
        crmParams.startup_guide = params.startup_guide;
        crmParams.templates = params.templates;

        Message.list = params.list;
        Message.locale = params.locale;

        Message.message_dialog_default = params.message_dialog_default;
        Message.message_dialog_info = params.message_dialog_info;
        Message.message_dialog_confirm = params.message_dialog_confirm;

        this.params = params;

        return this;
    },
    getParams: function () {
        return this.params;
    },
    reloadFromServer : function(){
        $.ajax({
            'url' : '/site/getCrmParams',
            'dataType' : "json",
            'type' : "POST", async : true,
            'success' : function(crm_params) {
                if(!crm_params){
                    return;
                }
                crmParams.setParams(crm_params);
            },
            'error' : function(){
                Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
            },
        });
    },

    setParamsPage : function (name, interface) {
        this.page_name = name;
        this.page_interface_type = interface;

        return this;
    },
    getCurrentFormatDate : function () {
        return this['FORMAT_DATE_END_' + Message.locale.language.toUpperCase()];
    }
}

var Message = {
    TYPE_DIALOG_DEFAULT     : 'default',
    TYPE_DIALOG_INFORMATION : 'info',
    TYPE_DIALOG_CONFIRM     : 'confirm',

    LOCALE_RU: 'ru',
    LOCALE_EN: 'en',
    LOCALE_ES: 'es',

    list : {},
    locale : {},
    last_code_action : null,

    message_dialog_default : '',
    message_dialog_info : '',
    message_dialog_confirm : '',

    _handler: null,
    _handler_general: null,
    _instance: null,

    /**
     *   Выводит сообщения
     *   Метод принимает строку или массив сообщений
     *   [{'type':'' , 'message':''}{'type':'' , 'message':''}{'type':'' , 'message':''}{'type':'' , 'message':''}]
     *   @param type = error|warning|information|success
     */

    createInstance : function(){
        var Obj = function(){
            for(var key in Message){
                this[key] = Message[key];
            }
        }

        return Message._instance = new Obj();
    },

    setHandler : function (handler) {
        if (handler) {
            this._handler_general = handler;
        }
        return this;
    },
    setHandlerAsConfirmAgree : function (handler) {
        if (handler) {
            this._handler = function (data) {
                if($(data).is('.yes-button')){
                    handler(data);
                }
            };
        }
        return this;
    },
    show : function(messages, translate, callback, type_dialog, modal_name){
        var params = {};

        callback = callback || this._handler;

        if(Array.isArray(messages)){
            if(!translate) translate = 0;
            if(translate){
                var message_tmp = [];
                $.each(messages, function(key, value){
                    if(value.params) params = value.params; else params = {};
                    if(value.type){
                        var type = Message.translate_local(value.type, {});
                        type = type.slice(0, 1).toUpperCase() + type.slice(1);
                        message_tmp.push({'type' : type, 'message' : Message.translate_local(value.message, params)});
                    } else {
                        message_tmp.push({'message' : Message.translate_local(value.message, params)});
                    }
                });
                messages = message_tmp;
            }
            modalDialog.show(Message.createModalDialog(messages, type_dialog), false, callback, modal_name);
        } else {
            modalDialog.show(messages, false, callback, modal_name);
        }
        return this;
    },

    showErrorAjax : function(jqXHR, textStatus, callback){
        var message = '';

        switch(textStatus){
            case 'timeout' :
                message = Message.translate_local('No response from the server, try again later');
                break;
            default :
                message = Message.translate_local('An error has occurred. Please check your Internet connection and try again');
        }

        Message.show([{'type': 'error', 'message': message}], false);

        if (callback && typeof(callback) == 'function') {
            callback();
        }
        Preloader.modalHide();
    },


    createModalDialog : function(messages, type_dialog){
        if(typeof type_dialog == 'undefined' || !type_dialog){
            type_dialog = Message.TYPE_DIALOG_INFORMATION;
        }

        switch(type_dialog){
            case Message.TYPE_DIALOG_DEFAULT :
                var message_dialog = Message.message_dialog_default;
                break;
            case Message.TYPE_DIALOG_INFORMATION :
                var message_dialog = Message.message_dialog_info;
                break;
            case Message.TYPE_DIALOG_CONFIRM :
                var message_dialog = Message.message_dialog_confirm;
                break;
        }

        var message_dialog = $("<div>"+message_dialog+"</div>");

        var tr = message_dialog.find('table tbody tr');
        var tr_list = '';
        if(Array.isArray(messages)){
            $.each(messages, function(key, value){
                var type = '';
                if(value['type']){
                    var type = Message.translate_local(value['type'], {});
                    type = type.slice(0, 1).toUpperCase() + type.slice(1);
                    if(type){
                        type+=':'
                    }
                }

                tr_list+= '<tr>' +
                            $(tr)
                                .clone()
                                .find('td')
                                .eq(0)
                                .html(type)
                                .closest('tr')
                                .find('td')
                                .eq(2)
                                .html(value['message'])
                                .closest("tr")
                                .html() +
                            '</tr>';
            });
            message_dialog.find('table tbody').html(tr_list);
        }
        var $dialog = $(message_dialog);
        $dialog.find('.modal-dialog').addClass('info-popup');

        modalDialog
            .createInstance()
            .events();

        return $dialog.html();
    },

    translate_local : function(message, params){
        if($.isEmptyObject(Message.list)){
            if(!$.isEmptyObject(params)) return message.format(params)
            else return message;
        }
        var result = '';
        $.each(Message.list, function(key, value){
            if(message == key){
                if(!$.isEmptyObject(params)) result = value.format(params);
                    else
                    result = value;
                return false;
            }
        });
        if(!result){
            if(!$.isEmptyObject(params)) result = message.format(params);
            else result = message;
        }
        return result;
    },

    getCodeAction : function(message_object){
        var code_action = $(message_object).closest('.element[data-type="message"]').find('.element[data-type="code_action"]').val();
        if(typeof(code_action) == 'undefined' || !code_action) return;
        code_action = code_action.split(',');
        Message.last_code_action = code_action;
        return code_action;
    },

    getParams : function(message_object){
        var params = $(message_object).closest('.element[data-type="message"]').find('.element[data-type="params"]').text();
        if(typeof(params) == 'undefined' || !params) return;
        params = JSON.parse(params);
        return params;
    },

    showConfirmDialog : function (options, dataAfterConfirm, callback) {
        /*
        *  options = {
        *       type: 'confirm',
        *       message: Message.translate_local('Download draft data') + '?'}
        *       closeAfterConfirm: true / false
        * }
        * */
        var _this = this;

        if (!options) {
            return;
        }

        Message
            .createInstance()
            .setHandlerAsConfirmAgree(function () {
                //_this.instance.showPreloader(true);

                var time = setTimeout(function() {
                    clearTimeout(time);
                    callback.call(_this, dataAfterConfirm);

                    if (options.closeAfterConfirm == true) {
                        modalDialog.hide(false, null, $('.modal.in').last().attr('id'));
                        //close
                    }

                }, 100);
            })
            .show([{'type': options.type, 'message': options.message }], false, null, Message.TYPE_DIALOG_CONFIRM);
    }

}




