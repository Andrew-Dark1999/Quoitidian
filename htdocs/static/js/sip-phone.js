$(document).on('click', '.edit-view .element[data-type="sip-link"][data-action="call"]', function(){
    Sip.events.linkCall_EditView(this);
});




var Sip = {

    //events
    events : {
        /*
        linkActionRun : function(_this){
            var action = $(_this).data('action');
            
            if(!action){
                return;
            }

            switch(action){
                case 'call':
                    return this.linkCall(_this);
            }

        },
        */

        // linkCall_EditView
        linkCall_EditView : function(_this){
            var data = {
                'action_name' : Sip.internalActions.ACTION_CALL,
                'params' : {
                    'to_number' :  $(_this).closest('.element[data-type="field_type_hidden"]').find('.element_edit_hidden[data-name]').text()
                }
            }

            modal_name = 'modal_dialog_sp1';

            var callback_md = function(){
                console.log('callback_md');
            }


            var callback_ajax = function(data){
                /*
                setTimeout(function(){
                    modalDialog.hide(false, false, modal_name);
                }, 3000);
                */
            }

            Message.show(
                [{'type':'Action', 'message': Message.translate_local('Calling the subscriber') + '...'}],
                false,
                callback_md,
                Message.TYPE_DIALOG_INFORMATION,
                modal_name
            );

            Sip.internalActions.ajaxSend(data, callback_ajax);
        }
    },


    //internalActions
    internalActions : {
        ACTION_CALL     : 'Call',
        ACTION_HANGUP   : 'Hungup',

        //ajaxSend
        ajaxSend : function(data, callback){

            AjaxObj
                .createInstance()
                .setUrl('/sip/internalAction')
                .setData(data)
                .setAsync(true)
                .setTimeOut(crmParams.global.ajax.get_url_timeout)
                .setDataType('json')
                .setType('POST')
                .setCallBackSuccess(function(data) {
                    if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else {
                        if(data.status == 'error'){
                            Message.show(data.messages);
                        } else {
                            callback(data);
                        }
                    }
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                    Preloader.modalHide();
                })
                .send()
        }




    },






}

/*
;(function(exports) {
    //list of private variables
    var _self = null; //link for instance
    var _private = {
        events: function () {
            var data = {
                    instance: this
                },
                path = this.actions;


            this._events = [
                {
                    parent: document,
                    selector: '.sip-phone-number-panel .element[data-type="number"]',
                    event: 'click',
                    func: path.onClickNumber
                },
                {
                    parent: document,
                    selector: '.sip-phone-number-panel .current-number .element[data-type="clear"]',
                    event: 'click',
                    func: path.onClickClearNumber
                },
                {
                    parent: document,
                    selector: '.sip-phone-bottom-panel .element[data-type="set"]',
                    event: 'click',
                    func: path.onClickToogleKeyBoard
                },
                {
                    parent: document,
                    selector: '.sip-phone-bottom-panel .element[data-type="microphone"]',
                    event: 'click',
                    func: path.onClickMicrophone
                },
                {
                    parent: document,
                    selector: '.sip-phone-bottom-panel .element[data-type="handset"]',
                    event: 'click',
                    func: path.onClickHandset
                },
            ]

            Global.addEvents(this._events, data);
            return this;
        },
        actions : {
            onClickHandset : function () {
                $(this).toggleClass('hold-off');
            },
            onClickMicrophone : function () {
                $(this).toggleClass('disable');
            },
            onClickToogleKeyBoard : function () {
                $('.sip-phone-number-panel').toggleClass('active');
            },
            onClickNumber : function (e) {
                var $currentNumber = $('.current-number'),
                    $list = $currentNumber.find('.element[data-type="number"]'),
                    value = $list.text()+ $(this).find('.up').text(),
                    $clear = $currentNumber.find('.element[data-type="clear"]');

                $list.text(value);

                if (value.length) {
                    $clear.removeClass('hide');
                } else {
                    $clear.addClass('hide');
                }
            },
            onClickClearNumber : function (e) {
                var $currentNumber = $('.current-number'),
                    $list = $currentNumber.find('.element[data-type="number"]'),
                    value = $list.text(),
                    $clear = $currentNumber.find('.element[data-type="clear"]');

                e.preventDefault();

                if (!value.length) return;

                value = value.substring(0, value.length-1);
                $list.text(value);

                if (value.length) {
                    $clear.removeClass('hide');
                } else {
                    $clear.addClass('hide');
                }
            }
        },
        init : function () {
            AjaxObj
                .createInstance()
                .setUrl('/sip/showSipPhone/')
                .setData({})
                .setDataType('json')
                .setAsync(false)
                .setCallBackSuccess(function(data){
                    if(data.status){
                        $('body').append($(data.html).html());
                    }
                })
                .send();
        },
        authorization : function () {
            //_self.vox.login("echo@a010echo.demos.voximplant.com", "echoecho");
            //_self.vox.login("test.bw@myapp.myaccount.voximplant.com", "");
            _self.vox.login("test.bw@myapp.myaccount.voximplant.com", "");
            //_self.vox = VoxImplant.getInstance().requestOneTimeLoginKey('');
            _self.vox.addEventListener(VoxImplant.Events.AuthResult, _private.handleAuthResult)
        },



        handleSDKReady : function () {
            _self.vox.connect();

            _self.vox.addEventListener(VoxImplant.Events.ConnectionEstablished, _private.handleConnectionEstablished);
            _self.vox.addEventListener(VoxImplant.Events.ConnectionFailed, _private.handleConnectionFailed);
            _self.vox.addEventListener(VoxImplant.Events.MicAccessResult, _private.handleMicAccessResult);

            _private.authorization();
        },
        handleConnectionEstablished : function (e) {
            console.log('handleConnectionEstablished', e)
        },
        handleAuthResult : function (e) {
            console.log('handleAuthResult', e)
        },
        handleConnectionFailed : function (e) {
            console.log('handleConnectionFailed', e)
        },
        handleMicAccessResult : function (e) {
            console.log('handleMicAccessResult', e)
        },
        handleCallConnected : function (e) {
            $('.element[data-type="handset"]').toggleClass('hold-off');
            console.log('handle', e)
        },
        handleCallDisconnected : function (e) {
            $('.element[data-type="handset"]').toggleClass('hold-off');
            console.log('handle', e)
        },
        handleCallFailed : function (e) {
            console.log('handle', e)
        },
        handleMessageReceived : function (e) {
            console.log('handle', e)
        },
        handleNetStatsReceived : function (e) {
            console.log('handle', e)
        }
    }

    var _public = {
        call : function (sip_uri) {
            //this.vox.call(sip_uri, false, null, { "X-MyCustomHeader":"Some Value"});
            var vox = VoxImplant.getInstance();
            var currentCall = vox.call(116, false, null, null);

            currentCall.addEventListener(VoxImplant.CallEvents.Connected, _self.handleCallConnected);
            currentCall.addEventListener(VoxImplant.CallEvents.Disconnected, _self.handleCallDisconnected);
            currentCall.addEventListener(VoxImplant.CallEvents.Failed, _self.handleCallFailed);
            currentCall.addEventListener(VoxImplant.CallEvents.ICETimeout, _self.handleCallFailed);
            currentCall.addEventListener(VoxImplant.CallEvents.MessageReceived, _self.handleMessageReceived);
            currentCall.addEventListener(VoxImplant.CallEvents.RTCStatsReceived, _self.handleNetStatsReceived);
        }
    }

    var SipPhone = function () {
        _private
            .events()
            .init();

        _self = this;

        //example for public
        //this.public1 = 'test';
        this.vox = VoxImplant.getInstance();
        this.vox.init({micRequired: true});
        this.vox.addEventListener(VoxImplant.Events.SDKReady, _private.handleSDKReady);

        this.call = _public.call;
        return this;
    };

    //static - not visible throw instance
    SipPhone._interface = 'SipPhone';
    //SipPhone.prototype.publicStatic2 = 7;

    exports.SipPhone = SipPhone;
})(window);

*/








/*

var socket = new JsSIP.WebSocketInterface('ws://');
var configuration = {
    sockets  : [ socket ],
    uri      : 'sip:',
    password : ''
};




var ua = new JsSIP.UA(configuration);

//ua.start();

// Register callbacks to desired call events
var eventHandlers = {
    'progress': function(e) {
        console.log('call is in progress');
    },
    'failed': function(e) {
        console.log('call failed with cause: '+ e.data.cause);
    },
    'ended': function(e) {
        console.log('call ended with cause: '+ e.data.cause);
    },
    'confirmed': function(e) {
        console.log('call confirmed');
    }
};

var options = {
    'eventHandlers'    : eventHandlers,
    'mediaConstraints' : { 'audio': true, 'video': false }
};

//var session = ua.call('sip:bob@example.com', options);

*/
