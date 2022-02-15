/**
 * Created by andrew on 11/23/17.
 */

/*------------------------------------------------------*/
/*--         modalDialog                              --*/
/*------------------------------------------------------*/

;(function (exports) {
    var _private, _public, ModalDialog,
        _self = {}; //link for instance

    var ModelDialog = {
        disableOutClick: false, // true - agree, false - disagree
    };

    _private = {
        _model:null,

        createModel : function (modalParent) {
            _self._model = Object.create(modalParent || ModelGlobal);

            for(var key in ModelDialog) {
                _self._model[key] = ModelDialog[key];
            };

            return _self._model;
        },
        onClickOutDialog : function(e) {
            var $this = $(this),
                client = $this.find('.client-name'),
                editable = client.find('.editable-field');

            $('.emoji-menu').hide();
            if (editable.html() == '') {
                $this.find('.edit-dropdown').addClass('open')
                    .end().find('.client-name .form-control').select();
            }

            editable.addClass('opacityIn');
            client.find('.edit-dropdown').removeClass('open');

            return true;
        },
        onKeyUpInfoPopUp : function (e) {
            if (e.keyCode == 13) {
                $(this).trigger('click');
            }
        },
        onClickOutSide : function(e) {
            var instance = e.data.instance;

            if (instance.getStatusAllowToCloseOutSide() &&  $(e.target).is('.modal.in') && window.getSelection().type != 'Range') {
                instance.hide();
            }
        },
    };

    _public = {
        _parent: null,
        content_instance: null,
        _allow_close_out_side: true,

        //always check attribute
        setPosition: function (position) {
            if ($(this._content).filter('[data-is-center]').length || position) {
                position = ($(window).height()-256)/2+'px';
            } else {
                position = 0;
            }

            $('.select[data-type="template"]').closest('.modal-dialog').css('margin-top', position);
            $('.panel.sm_extension[data-action="add"]').css('margin-top', position);
        },
        setParent: function (_parent) {
            this._parent = _parent;
        },
        setStatusAllowToCloseOutSide: function (status) {
            this._allow_close_out_side = status;

            return this;
        },
        getStatusAllowToCloseOutSide: function () {
            return this._allow_close_out_side;
        },
        getContentInstance: function () {
            return this.content_instance;
        },
        setContentInstance: function (instance) {
            this.content_instance = instance;

            return this;
        }
    };

    ModalDialog = {
        TYPE_POSITION_TOP: 0,
        TYPE_POSITION_CENTER: 1,

        _interface: 'modalDialog',
        _content: null, // content part window
        _element: null,
        _scrolled: null, // was scrolled
        default_module_name : 'modal_dialog',
        modal_dialog_shown : [],
        modal_dialog_shown_window : [],
        dinamic_data : null,
        callback_success : {},
        _callback_success: null,
        _store_instances: {},
        _enable_click_outside: true,
        _show_backdrop : true,
        _name_clazz_title : '',
        _cleaning: null,

        createInstance : function(modelParent){
            var Obj, instance;

            Obj = function(){
                for(var key in modalDialog){
                    this[key] = modalDialog[key];
                }

                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            _self.createModel(modelParent);

            instance = new Obj().constructor();
            instance.timestamp = moment().unix();
            instance.addInstance(instance.timestamp, instance);

            return instance;
        },
        getModel : function () {
            return _self._model;
        },
        getOpens: function () {
            var _this = this,
                data = {};

            $.each($('.edit-view'), function () {
                var key = $(this).data('unique_index');

                data[key] = _this.getDataFromStore(key);
            });

            return data;
        },
        constructor : function () {
            this.events();

            return this;
        },

        // реализация интерфейсов.
        implements: function (object) {
            object.implements.call(this);

            return this;
        },

        importInstance : function (instance) {
            var _instance = this
                .createInstance()
                .importModel(instance)

            return _instance;
        },

        getInstanceByKey : function(key){
            return this.getInstanceFromKey(key);
        },
        onCancelDialog : function(e) {
            Console.log('onCancelDialog()', e);
            var json = $(this).closest('.edit-view').data();
            
            if (!json) return true;
            
            var key = Global.createUniqueKey(json.copy_id, json.id);

            if (key) {
                Draft.removeDraft(key);
            }
        },
        importModel : function (data) {

            for(var key in data){
                if (typeof data[key] != 'object') {
                    this[key] = data[key];
                }
            }

            return this;
        },
        saveInstance : function (instance) {
            if (instance) {
                this._store_instances[instance._interface] = instance;
            }
            return this;
        },
        getInstanceFromStorage : function (key) {
            return key ? this._store_instances[key] : null;
        },
        setParentClass : function (title) {
            if (title) {
                this._name_clazz_title = title;
            }
            return this;
        },
        setShowBackdrop : function (status) {
            this._show_backdrop = status;

            return this;
        },
        setViewPosition : function () {
            if (!Base.isEditView()) {
                $('.upload-modal').closest('.modal').addClass('popup-upload');
            }
            return this;
        },
        setContent : function (data) {
            this._content = data;
            return this;
        },
        setElement : function (_element) {
            this._element = _element;

            return this;
        },
        //getModalName
        getModalName : function(index){
            if(typeof index == 'undefined' || index == false){
                index = modalDialog.modal_dialog_shown.length - 1;
            } else {
                index = modalDialog.modal_dialog_shown.length - index;
            }
            return modalDialog.modal_dialog_shown[index];
        },

        isOpen : function () {
            return modalDialog.modal_dialog_shown.length ? true : false;
        },
        getCountOpen : function () {
            return modalDialog.modal_dialog_shown.length;
        },

        setCleaning: function(bool) {
            this._cleaning = bool;

            return this;
        },
        isOpenAsHandler : function (callback) {
            if (typeof callback == 'function' && this.isOpen()) {
                callback();
            }
            return this;
        },
        //setCallbackSuccess
        setCallbackSuccess : function(callback){
            var modal_name = this.getModalName();
            modalDialog.callback_success[modal_name] = callback;

            return this;
        },

        deleteCallbackSuccess : function(modal_name){
            if(typeof (modal_name) == 'undefined' || modal_name == false){
                modal_name = modalDialog.getModalName();
            }
            delete modalDialog.callback_success[modal_name];

            return this;
        },

        setScrollTop : function (value) {
            $('.modal').last().scrollTop(value);

            return this;
        },
        getScrollTop : function () {
            return $('.modal').last().scrollTop();
        },

        //getCallbackSuccess
        getCallbackSuccess : function(){
            var modal_name = modalDialog.getModalName();
            if(typeof modalDialog.callback_success[modal_name] == 'function'){
                var callback = modalDialog.callback_success[modal_name];
                modalDialog.deleteCallbackSuccess(modal_name);

                return callback;
            }

            return false;
        },

        //getNewModalName
        getNewModalName : function(){
            for(var i=1; i<100; i++){
                if($('#' + modalDialog.default_module_name + i).length == 0) return modalDialog.default_module_name + i;
            }
            return this;
        },
        afterUpdateContent : function () {
            Global.setScroll();
        },
        events: function () {
            var _this = this,
                model = this.getModel();

            this._events = [
                { parent: document, selector: '.info-popup .yes-button', event: 'click', func: _self.onKeyUpInfoPopUp},
                { parent: document, disable: model.disableOutClick, selector: '#modal_dialog_container .modal', event: 'click', func: _self.onClickOutSide},
                { parent: document, selector: '.modal-dialog', event: 'click', func: _self.onClickOutDialog},
                { parent: document, selector: '.modal-dialog .close-button', event: 'click', func: _self.onCancelDialog}
            ]

            this.addEvents(this._events, {
                'instance': this
            });

            var time = setTimeout(function () {
                clearTimeout(time);
                $('.info-popup .yes-button, .info-popup .close-button').focus();

                $('.modal').last().off('scroll').on('scroll',function() {
                    var key = $(this).find('.edit-view').data('unique_index')

                    modalDialog.getDataFromStore(key)._scrolled = true;
                });
            }, 100);

            return this;
        },

        //show
        show : function(data, noMiddle, callback, modal_name){
            var $dialog, childModal, modal_name, context,
                content = data || this._content,
                ev = $(data).find('.edit-view');

            if (this._cleaning) {
                $('#modal_dialog_container').empty();
            }

            modalDialog.setDataToStore(ev.data('unique_index'), this.getContentInstance() || {});

            if(!modal_name){
                modal_name = modalDialog.getNewModalName();
            }
            modalDialog.modal_dialog_shown.push('#' + modal_name);

            //callback
            if($.isFunction(callback)){
                modalDialog.callback_success['#' + modal_name] = callback;
            }

            context = '<div class="modal {0}" id="'+modal_name+'" tabindex="-1" role="dialog" aria-labelledby="constructorLabel" aria-hidden="true">{1}</div>';
            context = context.replace('{0}', this._name_clazz_title);
            context = context.replace('{1}', content);

            this.modal_dialog_shown_window.push(data);
            $('#modal_dialog_container').append(context);

            //save instance
            this.setElement($('.modal .modal-dialog').last());
            var key = this.timestamp,
                $element = $(this._element);

            $element.data()[key] = this;
            $element.attr('data-key', key);

            this
                .setContent(content)
                .setPosition();

            $dialog = $('#' + modal_name);
            $dialog.modal({
                backdrop: 'static',
                keyboard: false
            });
            childModal = $dialog.find('.modal-dialog');

            if(ev && ev.data('unique_index')){
                $(this._element).data({
                    'edit-view': ev.data()
                })
            }

            //abstract ініціювання view
            if (this._parent) {
                this._parent.initView();
            }

            $dialog.modal('show');
            if(modalDialog.modal_dialog_shown.length > 1) {
                if(noMiddle === true) {
                    $(modalDialog.getModalName()).addClass('back-modal');
                    $(modalDialog.getModalName(2)).hide();

                    // Запрещаем вывод одного и того же модального окна 2 раза
                    var copy_id = $dialog.find('.edit-view').data('copy_id');
                    if ( $(modalDialog.getModalName(2)).find('.edit-view').data('copy_id') == copy_id ) {
                        $(modalDialog.getModalName()).modal('hide');
                    }

                }
                else {
                    $(modalDialog.getModalName(2)).append($('body > .modal-backdrop.in'));

                    childModal = $('#' + modal_name + ' .modal-dialog');
                    childModal.css('margin-top', $(window).height() / 2 - childModal.height() / 2 + 'px');
                }

                if ($('.modal-backdrop.in').length > 1) {
                    $('.modal-backdrop.in').last().remove();
                }
            } else if($('#' + modal_name).find('.modal-dialog').hasClass('upload-modal')) { // This is sile upload in inline-edit. Vertical align for modal
                var $modal = $('#' + modal_name).find('.upload-modal');
                $modal.css('margin-top', $(window).height() / 2 - $modal.height() / 2 + 'px');
            }

            $dialog = childModal.filter('[data-is-center]');
            $dialog.filter('[data-is-center]').css('margin-top', ($(window).height()-$dialog.height())/2+'px'); // window is center

            if (!this._show_backdrop) {
                $('.modal-backdrop').remove();
            }

            if(ev && ev.data('unique_index')){
                // start RefreshMessagesInterval
                EditView.activityMessages.startRefreshMessagesInterval();
            }

            return this.setContent(content);
        },

        //setWidth
        setWidth : function(_dialog_name, width){
            $(modalDialog.getModalName() + ' .').css('width', width);
            return this;
        },

        //hide
        hide : function(modal, dinamic_data, modal_name){
            if(dinamic_data){
                modalDialog.dinamic_data = dinamic_data;
            }
            if(modal_name){
                modal_name = '#' + modal_name;
            } else {
                modal_name = modalDialog.getModalName();
            }

            if(typeof modal == 'undefined' || modal == false){
                var modal = $(modal_name);
            }

            if(modal.length){
                if(!modal.find('#drop_zone').length){
                    if (!instanceEditView) {
                        instanceEditView = new EditViewContainer();
                    }
                    instanceEditView.removeByIndex(modal.find('.edit-view').data('unique_index'))
                }
                modal.modal('hide');
            }

            if(modalDialog.modal_dialog_shown.length > 0){
                $(modalDialog.getModalName()).modal();
            } else {
                modalDialog.modal_dialog_shown_window = [];
                modalDialog._instance = null;
            }

            return this;
        },
        hideAll : function(callback){
            modalDialog.modal_dialog_shown = [];
            modalDialog.modal_dialog_shown_window = [];
            modalDialog.dinamic_data = null;
            modalDialog.callback_success = {};
            modalDialog._instance = null;

            modalDialog.clearDataStore();

            $('.fake-backdrop').remove();


            $('#modal_dialog_container .modal').each(function(i, modal){
                var entity_key = $(modal).find('.modal-dialod').data('entity_key');
                if(!entity_key){
                    return true;
                }
                var entity = Entity.getInstance(entity_key);
                if(entity){
                    entity.destroy(true)
                }
                return false;
            });

            $('#modal_dialog_container').html('');
            $('.modal-backdrop.in').remove();
            $('body').removeClass('modal-open');

            if($.isFunction(callback)){
                callback();
            }

            return this;
        }

    }

    eventPath = '.modal';
    $(document).off('hidden.bs.modal', eventPath).on('hidden.bs.modal', eventPath, function(e){
        var _this = this,
            modal_name = modalDialog.getModalName(),
            this_copy_id = $(modal_name).find('.edit-view').data('copy_id'),
            this_data_id = $(modal_name).find('.edit-view').data('id'),
            modal_dialog = $(modal_name).find('.edit-view');

        if(modal_dialog.length > 0){
            var _uploads_id = [];
            var _activity_message_id = [];
            modal_dialog.find('.upload_file').each(function(i, ul){
                if($(ul).val()) _uploads_id.push($(ul).val());
            });
            modal_dialog.find('.element[data-type="block_activity"] .element[data-type="message"][data-status="temp"]').each(function(i, ul){
                if($(ul).data('id')) _activity_message_id.push($(ul).data('id'));
            });

            if(_uploads_id.length || _activity_message_id) Global.clearRubbish(_uploads_id, _activity_message_id);


            if(typeof(modal_dialog.data('history')) != 'undefined' && modal_dialog.data('history') == 'hide' && !$('.bpm_block').length) {
                History.close();
            }
        }

        $('body').removeClass('modal-open');


        var sdm_copy_id = $(modal_name).find('.edit-view').data('copy_id');
        var sdm_id = $(modal_name).find('.edit-view').data('id');


        //callback
        var callback = modalDialog.getCallbackSuccess();
        var dinamic_data = modalDialog.dinamic_data

        // remove module from array
        modalDialog.modal_dialog_shown.pop();
        modalDialog.dinamic_data = null;
        $(modal_name).remove();

        //stop RefreshMessagesInterval
        if(modalDialog.modal_dialog_shown.length == false){
            EditView.activityMessages.stopRefreshMessagesInterval();
        }

        //callback run
        if($.isFunction(callback)){
            callback(_this, e);
        }

        // if(typeof modalDialog.callback_pv == 'function'){
        //     modalDialog.callback_pv();
        // }

        if(modalDialog.modal_dialog_shown.length <= 1 && EditView.saved_first_ev){
            EditView.saved_first_ev = false;
            instanceGlobal.contentReload
                .prepareVariablesToGeneralContent()
                .run();
        }


        // parent module
        $(modalDialog.getModalName()).show().find('.modal-backdrop.in').appendTo('body');

        //обновляем сабмодули

        if(typeof(this_copy_id) != 'undefined' && this_copy_id){
            var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');

            if(typeof(copy_id) != 'undefined' && copy_id){
                var _edit_view_object = $(modalDialog.getModalName()).find('.edit-view[data-copy_id="' + copy_id + '"]');

                EditView.subModules.updateSubModuleDataList(_this, _edit_view_object, function(){
                    if(EditView.activityMessages.issetBlockActivity(_edit_view_object) && EditView.activityMessages.refresh_messages == false){
                        EditView.activityMessages.refreshMessages($(_edit_view_object).find('.element[data-type="block_activity"]'));

                        var _data = null;
                        if(!this_data_id && dinamic_data && dinamic_data.id){
                            _data  = {'data_id' : dinamic_data.id}
                        }
                        EditView.relates.reloadSDMChannel($(_edit_view_object).find('.element[data-type="drop_down"] .element[data-type="drop_down_button"][data-reloader="activity_channel"]'), _data);
                    }
                })


                if(sdm_copy_id && sdm_id){
                    EditView.relates.reloadSDM($(modalDialog.getModalName()).find('.edit-view .element[data-type="drop_down"] .element[data-type="drop_down_button"][data-relate_copy_id="' + sdm_copy_id + '"]'));
                    Global.addOperationInSDM();
                }
            }
        }


        var entity_key = $(_this).find('.modal-dialog').data('entity_key');
        if(typeof entity_key != 'undefined' && entity_key){
            var entity = Entity.getInstance(entity_key);
            if(entity){
                entity.destroy()
            }
        }



        var notificationTimeOut = setTimeout(function () {
            clearTimeout(notificationTimeOut);
            HeaderNotice.refreshAllHeaderNotices();
        }, 50);

        $('.file-block').removeClass('has-file');
        //NiceScroll.init();
        if (modalDialog.modal_dialog_shown.length > 0) {
            $('body').addClass('modal-open');
            EditView.textRedLine();
            jScrollRemove();
            jScrollInit();
            setTimeout(function(){ EditView.textRedLine();}, 100);
        }
        $('.datepicker-dropdown.dropdown-menu').hide();

        if (!$.isFunction(callback)) { // preloader hide run in callback
            instanceGlobal.contentReload.preloaderHide();
        }
    });

    $(document).on('click', '.modal .yes-button', function(){
        //callback
        var callback = modalDialog.getCallbackSuccess();

        if($.isFunction(callback)){
            callback(this);
        }
    });

    $(document).on('click', '.confirm-yes-button', function(){
        var _this = this;
        var code_action = Message.getCodeAction(_this);
        var params = Message.getParams(_this);


        if(!code_action) return;

        switch(code_action[0]){
            case '100' : //ACTION_SUB_MODULE_EDIT_VIEW_CREATE
                var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
                EditView.save(copy_id, {}, function(data){
                    var edit_view = $(modalDialog.getModalName()).find('.edit-view');
                    $(edit_view).closest('.edit-view').data('id', data.id);

                    EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                        var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+params.copy_id+'"] .submodule_edit_view_dnt-create');
                        if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                        EditView.subModules.cardCreate(sub_module);
                    });
                });
                break;

            case '101' : //ACTION_SUB_MODULE_EDIT_VIEW_EDIT
                var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
                EditView.save(copy_id, {}, function(data){
                    var edit_view = $(modalDialog.getModalName()).find('.edit-view');
                    $(edit_view).closest('.edit-view').data('id', data.id);

                    EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                        var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+params.copy_id+'"] .submodule_edit_view_dnt-edit');
                        if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                        EditView.subModules.cardEditSM(sub_module);
                    });
                });
                break;

            case '102' : //ACTION_SUB_MODULE_EDIT_VIEW_SELECT
                var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');

                EditView.save(copy_id, false, function(data){
                    var edit_view = $(modalDialog.getModalName()).find('.edit-view');
                    $(edit_view).closest('.edit-view').data('id', data.id);

                    EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                        var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+params.copy_id+'"] .submodule_edit_view_dnt-select');
                        if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                        EditView.subModules.cardSelect(sub_module);
                    });
                });
                break;

            case '103' : //ACTION_SUB_MODULE_EDIT_VIEW_DELETE
                var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
                EditView.save(copy_id, {}, function(data){
                    var edit_view = $(modalDialog.getModalName()).find('.edit-view');
                    $(edit_view).closest('.edit-view').data('id', data.id);

                    EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                        var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+params.copy_id+'"] .submodule_edit_view_dnt-delete');
                        if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                        EditView.subModules.cardRemoved(sub_module,false);
                    });
                });
                break;

            case '104' : //ACTION_CONSTRUCTOR_PRIMARY_RELATE_CHANGE
            case '105' : //ACTION_CONSTRUCTOR_SCHEMA_TYPE_TO_ONE_CHANGE
            case '107' : //ACTION_MODULE_DELETE_TEMPLATES
            case '108' : //ACTION_SUB_MODULE_TEMPLATE_REMOVE
            case '109' : //ACTION_RELATE_CHENGED_SDM
            case '111' : //ACTION_PROCESS_BO_CLEAR
            case '8002' : //REPORTS. ACTION_DELETE_ELEMENT
                var _data = Constructor.getModuleDataParams();
                _data['confirm_code_action'] = code_action;
                _data['confirm_params'] = params;

                Constructor.moduleSave(_data, function(result_save){
                    if(result_save.status == true){
                        deleteTableStatus(_data.copy_id);
                        removeTableOrder(_data.copy_id);

                        callback = function(){
                            instanceGlobal.contentReload
                                .prepareVariablesToGeneralContent()
                                .run();
                        }
                        modalDialog.setCallbackSuccess(callback);
                        modalDialog.hide();
                    }
                })
                break;
            case '8001' : //REPORTS. ACTION_DELETE_MODULE
                var copy_id_list = [];
                var _data = {};

                if(modalDialog.modal_dialog_shown.length){ // length=1
                    copy_id_list.push($(modalDialog.getModalName()).find('.constructor').data('copy_id'));
                } else {
                    $('.list_view_block[data-page_name="constructor"] table input.input_ch:checked').closest('tr').each(function(i, ul){
                        copy_id_list.push($(ul).data('copy_id'));
                    });
                }

                if(copy_id_list == false || $.isEmptyObject(copy_id_list)) return;

                _data['confirm_code_action'] = code_action;
                _data['confirm_params'] = params;
                _data['copy_id'] = copy_id_list;

                Constructor.moduleDelete(_data);

                break;
            case '106' : //ACTION_SUB_MODULE_EDIT_VIEW_CREATE_SELECT
                var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
                EditView.save(copy_id, {}, function(data){
                    var edit_view = $(modalDialog.getModalName()).find('.edit-view');
                    $(edit_view).closest('.edit-view').data('id', data.id);

                    EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                        var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+params.copy_id+'"] .submodule_edit_view_dnt-create-select');
                        if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                        //EditView.subModules.addCardSelect(sub_module, params.parent_class);
                        EditView.subModules.addCardSelect(sub_module, 'edit-view');
                    });
                });
                break;
            /*
             case '110' : //ACTION_PROCESS_OBJECT_INSTANCE
             var copy_id = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
             EditView.save(copy_id, {}, function(vars){
             var edit_view = $(modalDialog.getModalName()).find('.edit-view');
             $(edit_view).closest('.edit-view').data('id', vars.data.id);

             EditView.subModules.addNewProcesses(copy_id, vars.data.id, function(){
             EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
             if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
             });
             });


             });

             break;
             */
        }

    });


    for(var key in _public) {
        ModalDialog[key] = _public[key];
    }

    for(var key in _private) {
        _self[key] = _private[key];
    }

    for(var key in ModalDialog) {
        _self[key] = ModalDialog[key];
    }


    exports.modalDialog = ModalDialog;
})(window);
