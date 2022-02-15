


;(function (exports) {
    var _private, _public, _protected,
        _self = {}; //link for instance

    _protected = {

    };
    /*config file package.json environments -> editor:
    * 1 - emoji
    * 2 - tinymci
    */
    var redactor = {
        type: null,

        setType: function (type) {
            this.type = type;

            return this;
        },
        isEmoji: function () {
            return crmParams.activity_editor == Constant.EDITOR_EMOJI
        },
        isTinyMCE: function () {
            return crmParams.activity_editor == Constant.EDITOR_TINY_MCE
        },
        enablePaste: function(_this) {
            switch ( crmParams.activity_editor) {
                case Constant.EDITOR_EMOJI: {
                    $('.emoji-wysiwyg-editor').on('paste', function() {
                        var wysiwyg = $(this);
                        _this
                            .setElement(wysiwyg)
                            .setPoint(wysiwyg.closest('.element[data-type="editors"]'))
                            .calcPositionDeltaScroll();

                        setTimeout(function(){
                            var notImages = wysiwyg.find('*:not(:has(img[src*="static/images/emoji"]))');
                            notImages.find('input, video, textarea, select, button, iframe, style, script, meta').remove();
                            notImages.find('br').removeAttr('style');

                            while (notImages.filter('*[style]').not('br').length) {
                                var values = notImages.filter('*[style]').not('br');
                                $.each(values, function(key, val){
                                    var it = $(this),
                                        attributes = $.map(this.attributes, function (item) {
                                            return item.name;
                                        });
                                    if (it.is('[src*="static/images/emoji"]')) return true;
                                    $.each(attributes, function(i, item) {
                                        it.removeAttr(item);
                                    });

                                    $.each((it.is('table')) ? [it] : [], function (key,val){
                                        var parent = val.wrap( "<div></div>" ).parent();
                                        parent.html(parent.text());
                                    });

                                    if (it.find("*").not("b, strong, em").first().is('br')) {
                                        it.find('>br').wrap( "<div></div>" ).parent();
                                        if (!it.text().length) {
                                            it.before(it.html());
                                            it.remove();
                                        }
                                        return true;
                                    }

                                    if (it.find(">div").length) {
                                        it.before(it.html());
                                        it.remove();
                                        return true;
                                    }

                                    it.find(">*").each(function(){
                                        var t = $(this);
                                        if ( t.text().length==0 && !t.is('br')) {
                                            if (t.find('>br').length) return true;
                                            t.remove();
                                        } else {
                                            if (t.is('div')) {
                                                if (t.find('>br').length) return true;
                                                it.before(t.clone());
                                                t.remove();
                                            } else {
                                                if (t.text().length) {
                                                    t.after(' '+t.text()).remove();
                                                };
                                            };
                                        };
                                    });

                                    !it.text().trim().length && !it.find('br').length && !it.is('br') ? it.remove() : '';
                                    if (it.is('a')) {
                                        it.after('<div>'+it.text()+'</div>').remove();
                                    }
                                });
                                notImages = wysiwyg.find('*:not(:has(img[src*="static/images/emoji"]))');
                            }

                            //if (!this.editing) {
                            modalDialog.setScrollTop(_this._position_scroll_relative_point);
                            //}
                        }, 100);
                    });

                    break;
                }
                case Constant.EDITOR_TINY_MCE: {
                    break;
                }
                default: break
            }

        },
        getContent: function($root, type) {
            var content = '';

            switch ( crmParams.activity_editor) {
                case Constant.EDITOR_EMOJI: {
                    content = $root.find('[data-type_comment="'+type+'"] .emoji-wysiwyg-editor').html();
                    break;
                }
                case Constant.EDITOR_TINY_MCE: {
                    if (!tinyMCE.activeEditor) {
                        return '';
                    }
                    content = tinyMCE.activeEditor.getContent();
                    break;
                }
                default: break
            }

            return content;
        },
        getObject: function($root, type) {
            var object = '';

            switch ( crmParams.activity_editor) {
                case Constant.EDITOR_EMOJI: {
                    object = $root.find('[data-type_comment="'+type+'"] .emoji-wysiwyg-editor');
                    break;
                }
                case Constant.EDITOR_TINY_MCE: {
                    object = $root.find('[data-type_comment="' + type + '"] .emojis-wysiwyg'); // 4 - version
                    break;
                }
                default: break
            }

            return object;
        },


    setContent: function ($root, data, options) {
        var content = '';
        /*
        * When change version of TinyMCI, you need change classes
        * */
        //object = $root.find('[data-type_comment="' + type + '"] .tox-tinymce'); // 5 - version
        // object = $root.find('[data-type_comment="' + type + '"] .emojis-wysiwyg'); // 4 - version

        switch (crmParams.activity_editor) {
            case Constant.EDITOR_EMOJI: {
                $root.find('form div.emoji-wysiwyg-editor')
                    .empty()

                $root.find('form div.emoji-wysiwyg-editor')
                    .html(data.message.text)
                    .attr('data-id', options.id)
                    .addClass('editting');
                $root.find('.element[data-type="block_attachments"]')
                    .html(data.message.attachments);

                break;
            }
            case Constant.EDITOR_TINY_MCE: {
                var id = $root.find('form textarea').attr('id');

                tinymce.get(id).focus();
                $root
                    //.find('form div.tox-tinymce') // 5 version
                    .find('form .emojis-wysiwyg') // 4 version
                    .attr('data-id', options.id)
                    .addClass('editting');

                tinyMCE.activeEditor.setContent(data.message.text)

                break;
            }
            default:
                break
        }

        return content;
    },
    init: function (ev) {
        var _this = this;

        switch (crmParams.activity_editor) {
            case Constant.EDITOR_EMOJI: {
                $('.emojis-plain').emojiarea({wysiwyg: false});
                break;
            }
            case Constant.EDITOR_TINY_MCE: {
                tinymce.init({
                    selector: 'textarea.emojis-wysiwyg',
                    setup: function (editor) {
                        editor.on('focus', function (e) {
                            ev.find('.btn.send_massage_activity').last().show('fast');
                        });
                        editor.on('keyup', EditView.onKeyUp);
                        editor.on('paste', function (e) {
                            setTimeout(function () {
                                var sub = ($(e.currentTarget).text() || '').substring($(e.currentTarget).text().length - redactor.keyDetectCopying.length);
                                if (sub.length == redactor.keyDetectCopying.length) {
                                    redactor.pasteAfterCopying($(e.currentTarget));
                                    return;
                                }
                            }, 100);
                        });
                    },
                    plugins: "emoticons",
                    toolbar: "emoticons",
                    branding: false,
                    contextmenu: "link image imagetools table spellchecker"
                })
                break;
            }
        }

        $.each(ev.find('[data-editor_type]'), function (){
            var $this = $(this);
            var type = $this.data('editor_type');
            console.log('type', type);
            switch (type) {
                case 'tiny_mce': {
                    tinymce.init({
                        selector: 'textarea[data-editor_type="tiny_mce"]',
                        setup: function (editor) {
                            editor.on('focus', function (e) {
                                ev.find('.btn.send_massage_activity').last().show('fast');
                            });
                            editor.on('keyup', EditView.onKeyUp);
                            editor.on('paste', function (e) {
                                setTimeout(function () {
                                    var sub = ($(e.currentTarget).text() || '').substring($(e.currentTarget).text().length - redactor.keyDetectCopying.length);
                                    if (sub.length == redactor.keyDetectCopying.length) {
                                        redactor.pasteAfterCopying($(e.currentTarget));
                                        return;
                                    }
                                }, 100);
                            });
                        },
                        plugins: "emoticons",
                        menubar: "false",
                        formats: {
                            // Changes the default format for h1 to have a class of heading
                            h1: { block: 'h1', classes: 'heading' },
                            bold: [
                                { inline: 'strong', remove: 'all' },
                                { inline: 'span', styles: { fontWeight: 'bold' } },
                                { inline: 'b', remove: 'all' }
                            ]
                        },
                        branding: false,
                        contextmenu: "link image imagetools table spellchecker"
                    })
                    break
                }
            }
        });
    },

        close: function (target) {
            var $editing = $('.edit-view .editting');

            if ($editing.length) {
                var $comment = $editing.closest('.user_comment'),
                    modelEditView = $comment.closest('.edit-view').data();

                if (modelEditView && modelEditView.updateMessage) {
                    $comment.after(modelEditView.updateMessage);
                    $comment.remove();
                }
            }
        }
    }


    _private = {
        onCancelDialog: function(e){
        },
        onSave: function(e){
            var $this = $(this),
                instance = e.data.instance,
                process_view_block = $('.process_view_block');

            if (instanceGlobal.currentInstance.type === 'process') {
                ProcessEvents.getInstance().editView.onClickSave($(this));
                return;
            }

            clearTimeout(window.saveDraftTime);

            if (process_view_block.data('page_name') == 'processView'){
                var currentStatePageName = {
                    posScroll: process_view_block.find('.process_wrapper').scrollLeft(),
                    list: []
                };
                process_view_block.find('.process_list > li .slimscrolldiv').each(function () {
                    var t = $(this);
                    if (t.getNiceScroll(0))
                    {
                        currentStatePageName.list.push({
                            position :  t.getNiceScroll(0).getScrollTop(),
                            data_unique_index : t.closest('section.panel').attr('data-unique_index')
                        });
                    }
                });
                localStorage.setItem('currentStatePageName', JSON.stringify(currentStatePageName));
            }

            var $view = $(this).closest('.edit-view'),
                $general = $view.find('.message_field[data-type_comment="general"] .emoji-wysiwyg-editor'),
                $email = $view.find('.message_field[data-type_comment="email"] .emoji-wysiwyg-editor');

            $view.find('.client-name .title-edit.open').removeClass('open');
            if ((($general.length) && ($general.html() !== ""))
                || (($email.length) && ($email.html() !== ""))) {
                $view.find('.send_massage_activity').trigger('click', {
                    status:true,
                    callback: function() {
                        if (EditView.activityMessages.data_status) {
                            instance.save($view.data('copy_id'));
                        }
                    }
                });
                return;
            } else if ($view.find('.task_comments>.task_message .file_thumb.file_other').text() == "GDoc") {
                $view.find('.emoji-wysiwyg-editor').text(Message.translate_local('Added document Google Doc'));
                $view.find('.send_massage_activity').trigger('click');
            }

            if (EditView.activityMessages.request_start) {
                var intervalId = setInterval(function() {
                    if(!EditView.activityMessages.request_start) {
                        instance.save($view.data('copy_id'));
                        clearInterval(intervalID);
                    }
                }, 300);
            } else {
                instance.setHandlerAfterSave(
                    instanceGlobal.currentInstance.type == PAGE_IT_REPORTS
                    && instance.getParent()._interface != QuickViewPanel._interface  ? function () {
                            Reports.Constructor
                                .createInstance()
                                .setElementHtml($this)
                                .open()

                        } : null)
                    .save($view.data('copy_id'));
            }
        },
        onChangeContentActivity: function () {

        },
        onClickDelete : function(e){
            var  modal,
                _this = this,
                edit_view = $(_this).closest('.edit-view'),
                id = edit_view.data('id'),
                copy_id = edit_view.data('copy_id'),
                instance = e.data.instance,
                parent_copy_id = edit_view.data('parent_copy_id'),
                $modal = edit_view.closest('.modal-dialog');



            modal = $modal.data()[$modal.data('key')];

            if(!id){
                Message.show([{'type':'error', 'message':'You can not delete any unsaved data'}], true);
                return;
            } else {

                var _function = function(){

                    var params = {'id': [id]}
                    if(!parent_copy_id || typeof(parent_copy_id) == 'undefined'){
                        var pci = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id');
                        var pdi = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id');
                        if(pci) params['pci'] = pci;
                        if(pdi) params['pdi'] = pdi;
                    }

                    Global.getModel().removeData({
                        copy_id: copy_id,
                        data: params
                    }, function(data){
                        if(data.status){
                            if(!parent_copy_id || typeof(parent_copy_id) == 'undefined'){
                                if(!data.messages){
                                    History.close(true);

                                    var parent = instance.getParent();

                                    if (parent && parent._type == QuickViewPanel._type) {
                                        QuickViewPanel.updateContent(true);
                                        return this;
                                    }

                                    if (Events.getCountLine(Events.TYPE_UPDATE_DATA)) {
                                        modalDialog.hide();
                                        Events.runHandler(Events.TYPE_UPDATE_DATA, {
                                            event: e,
                                            data: null
                                        });
                                    } else {
                                        if($('.process_view_block.sm_extension').length){
                                            var card = $('.process_view_block.sm_extension .element[data-name="process_view_panel"]  li.element[data-type="drop_down"][data-id="' + id + '"]');
                                            var panel = card.closest('.element[data-name="panel"]');

                                            card.remove();

                                            var sorting_list_id_list = ProcessView.convert.dataIdToSortingListIdList([id])
                                            ProcessView.initLists(sorting_list_id_list);

                                            instanceGlobal.contentReload.preloaderHide();

                                            ProcessView.checkAndRemoveEmptyPanel(panel);
                                        } else {
                                            callback = function(){
                                                if (instanceGlobal.currentInstance.type == PAGE_IT_REPORTS && Global.isReport()) {
                                                    Reports.Constructor
                                                        .createInstance()
                                                        .setElementHtml($(_this))
                                                        .open()
                                                } else {
                                                    instanceGlobal.contentReload
                                                        .createInstance()
                                                        .prepareVariablesToGeneralContent()
                                                        .setSearch(Search.getInstance(true).getText())
                                                        .run();
                                                }
                                            }
                                        }

                                        if(typeof callback == 'function'){
                                            modalDialog.setCallbackSuccess(callback);
                                        }
                                        modalDialog.hide();
                                    }

                                    CommunicationsBlock.updateContent();
                                } else {
                                    Message.show(data.messages, false);
                                    instance.getParent().getPreloader().hide();
                                }
                            } else {
                                $(modalDialog.getModalName(2)).find('.element[data-type="drop_down_button"][data-relate_copy_id="' + copy_id + '"]').data('id', '');
                                modalDialog.hide();
                                Preloader.destroy();
                            }
                        } else if(data.messages){
                            Message.show(data.messages, false);
                        }

                        HeaderNotice.refreshAllHeaderNotices();
                        EditView.textRedLine();
                        jScrollRemove();
                        jScrollInit();
                        EditView.textRedLine();
                        setTimeout(function(){
                            EditView.textRedLine();
                        }, 100);
                    })
                }

                Message
                    .createInstance()
                    .setHandlerAsConfirmAgree(function () {
                        modal
                            .hide()
                            .isOpenAsHandler(function () {
                                instance
                                    .getParent()
                                    .showPreloader();
                            })

                        var time = setTimeout(function () {
                            clearTimeout(time);

                            _function();
                        }, 100);
                    })
                    .show([{'type':'confirm', 'message': Message.translate_local('Delete data')+ '?'}], false, null, Message.TYPE_DIALOG_CONFIRM);
            }
        },
        onBtnCopy : function(e){
            var modal,
                $this = $(this),
                $editView = $this.closest('.edit-view'),
                id = $editView.data('id'),
                $modal = $this.closest('.modal-dialog'),
                instance = e.data.instance,
                copy_id = $editView.data('copy_id');

            var modelGlobal = Global.getModel();

            modal = $modal.data()[$modal.data('key')];

            if(!id){
                Message.show([{'type':'error', 'message':'You can not copy any unsaved data'}], true);
                return;
            } else {
                var pci,
                    pdi,
                    parent_copy_id = $this.closest('.edit-view').data('parent_copy_id'),
                    parent_data_id = $this.closest('.edit-view').data('parent_data_id'),
                    params = {
                        'id' : [id],
                        'parent_copy_id' : parent_copy_id,
                        'parent_data_id' : parent_data_id,
                        'this_template' : $this.closest('.edit-view').data('this_template'),
                    };

                if(!parent_copy_id || typeof(parent_copy_id) == 'undefined'){
                    pci = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id');
                    if(pci) params['pci'] = pci;
                }
                if(!parent_data_id || typeof(parent_data_id) == 'undefined'){
                    pdi = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id');
                    if(pdi) params['pdi'] = pdi;
                }


                modelGlobal.copyData({
                    copy_id: copy_id,
                    data: params
                }, function (data) {
                    if(data.status){

                        if (Events.getCountLine(Events.TYPE_UPDATE_DATA)) {
                            modalDialog.hide();

                            Events.runHandler(Events.TYPE_UPDATE_DATA, {
                                event: e,
                                data: data
                            });
                        } else {
                            if(!$this.closest('.edit-view').data('parent_copy_id')){
                                if($('.process_view_block.sm_extension').length){
                                    callback = function(){
                                        var preloader = instance.getParent().getPreloader(),
                                            contentReload = instanceGlobal.contentReload.createInstance();

                                        iPreloader.implements.call(contentReload);

                                        contentReload
                                            .setPreloader(preloader)
                                            .reDefinition()
                                            .showPreloader()
                                            .prepareVariablesToGeneralContent()
                                            .appendVars({
                                                'module': {
                                                    'data_id_list': [data.id[0]],
                                                    'sorting_list_id' : ProcessView.sorting_list_id,
                                                    'process_view_action' : ProcessView.ACTION_COPY_ENTITY
                                                }
                                            })
                                            .setCallBackSuccessComplete(function(){
                                                var sorting_list_id_list = ProcessView.convert.dataIdToSortingListIdList([data.id[0]])
                                                ProcessView.initLists(sorting_list_id_list);

                                                this.actionShowEditView(data.id[0]);
                                            })
                                            .prepareVariablesToProcessView()
                                            .run();
                                    };
                                } else {
                                    callback = function(){
                                        if (instanceGlobal.currentInstance.type == PAGE_IT_REPORTS && Global.isReport()) {
                                            Reports.Constructor
                                                .createInstance()
                                                .setElementHtml($this)
                                                .open()
                                        } else {
                                            var preloader = instance.getParent().getPreloader(),
                                                contentReload = instanceGlobal.contentReload.createInstance();

                                            iPreloader.implements.call(contentReload);

                                            contentReload
                                                .setPreloader(preloader)
                                                .reDefinition()
                                                .showPreloader()
                                                .prepareVariablesToGeneralContent()
                                                .setCallBackSuccessComplete(function () {
                                                    var copy_id2 = this._vars['module']['copy_id'];
                                                    this._vars['module']['copy_id'] = copy_id;
                                                    this.actionShowEditView(data.id[0]);
                                                    this._vars['module']['copy_id'] = copy_id2;
                                                })
                                                .run();
                                        }
                                    };
                                }

                                modalDialog.setCallbackSuccess(callback);
                            }
                            modalDialog.hide();
                        }
                    }
                });
            }
        },
        //додавання комментаря
        onSendMessageActivity : function(e, param){
            var $messageCont, type,
                $this = $(this),
                $content,
                instance = e.data.instance,
                $editView = $this.closest('.edit-view'),
                $taskMessages = $this.closest('.task_message'),
                status_editing = $taskMessages.find('.editting').length,
                activity = instance.activityMessages,
                key = $editView.data('unique_index');

            if(activity.request_start){
                return;
            }

            if (status_editing) {
                if (modalDialog.getDataFromStore(key)._scrolled) {
                    activity
                        .setElement($this)
                        .calcPositionDeltaScroll();
                }
            }

            type = $taskMessages.attr('data-type_comment');
            $messageCont = redactor.getObject($taskMessages, type)

            if(type==='email')
            {
                EditView.checkServiceParams(Communication._copy_id);
            }

            if (redactor.isEmoji()) {
                $.each($messageCont.find('div'), function(){
                    $(this).html($(this).html().replace(/&nbsp;/g,' ').trim());
                });
            }

            $content = redactor.getContent($taskMessages, type);

            activity.addMessage($messageCont, $content, function(upload_files){
                var $button = $editView.find('.buttons-section');

                if (!upload_files) {
                    $this.hide('fast');
                };

                if (status_editing) {
                    modalDialog.setScrollTop($button.position().top - activity._position_delta_scroll);
                }

                activity.activityImagesNoRedact();

                Communication.initTextArea();

                if (param && param.status) {
                    param.callback();
                }
                CommunicationsBlock
                    .getInstance()
                    .setPeriod(true)
                    .refreshChannels(true);
            });

            return true;
        },
        // onClickBtnEnding : function(e){
        //     e.preventDefault();
        //
        //     var $this = $(this), $element, $timeBlock,
        //         currentDate, outputDate,
        //         instance = e.data.instance,
        //         $item = $this.find('.date-time'),
        //         currentFormat = crmParams.getCurrentFormatDate(),
        //         $target = $(e.target),
        //         $parent = $this.parent(),
        //         $elementValue = $this.find('.element[data-type="value"]');
        //
        //     $element = $parent.find('.element[data-type="calendar-place"]');
        //     $timeBlock = $parent.find('.time-block');
        //     $this.data().param = {};
        //
        //     if ($target.parent().is('.checkbox-line') || $target.is('.checkbox-line') || $target.is('.description')) {
        //         return false; // next
        //     }
        //
        //     if ($parent.is('.open')) {
        //         $parent.toggleClass('open');
        //         return;
        //     }
        //
        //     $parent.toggleClass('open');
        //
        //     if ($item.val()) {
        //         outputDate = moment($item.val(), crmParams.FORMAT_DATE).format(currentFormat);
        //     } else {
        //         outputDate = moment().format(currentFormat);
        //     }
        //
        //     if (!$element.data().datepicker) {
        //         instance.dateTimePopUp
        //             .setElement($parent)
        //             .setCurrentFormat()
        //             .setTimer(!$elementValue.is('.only-date'))
        //             .setOutputDate(outputDate)
        //             .setOutputBlock($elementValue)
        //             .init();
        //     }
        //
        //     instance.dateTimePopUp.setDefaultParam({
        //         'time' : $timeBlock.data().timepicker.getTime()
        //     });
        // }
    };

    _public = {
        url_after_save: null,

        prepateDataForDraft: function(model, beginDraft) {
            var data = this.prepareData(model.copy_id) || {};
            var json = {};

            $.each( data, function( key, value ) {
                if (key.indexOf('EditViewModel') == 0) {
                    var nameOfField = key.substring(14, key.length-1);
                    json[nameOfField] = value;
                }
            });

            json['activity'] = this.getActivityData();

            if (beginDraft) {
                var draft = {};
                $.each(Object.keys(beginDraft || {}), function (key, value) {
                    $.each(Object.keys(json || {}), function (_key, _item) {
                        if (value == _item) {
                            if (typeof beginDraft[value] == "object") {
                                var one = JSON.stringify(beginDraft[value]);
                                var two = JSON.stringify(json[_item]);

                                if (one !== two) {
                                    draft[value] = json[_item];
                                }
                            } else {
                                if (beginDraft[value] != json[_item]) {
                                    draft[value] = json[_item];
                                }
                            }
                        }
                    })
                })
                json = draft;
            }

            return json;
        },
        getActivityData: function () {
            var $taskMessages = $('.edit-view.in').find('.task_message');
            var type = $taskMessages.attr('data-type_comment');

            return redactor.getContent($taskMessages, type);
        },
        editCardByParam : function(model){
            var data = model.data,
                _this = this;

            AjaxObj
                .createInstance()
                .setUrl(Global.urls.url_edit_view_edit +'/' + data['copy_id'])
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

                            EditView
                                .createModel(data)
                                .setView()
                                .drawReadOnlyFields();

                            model.callback(data);

                            _this.afterLoadView(data);
                        }
                    }
                    Preloader.modalHide();
                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                    Preloader.modalHide();
                })
                .setCallBackDone(function() {
                    $('.modal .contacts-block img').on('load', function() {
                        EditView.contactImg();
                    });
                    EditView.emptyFields();
                    EditView.hiddenBlocks();

                    QuickViewPanel.updateContent();
                })
                .send()
        },
        constructor : function (modal_dialog) {
            iModule.implements.call(this);
            iPreloader.implements.call(this);
            iLifeCycle.implements.call(this);
            iDraft.implements.call(this);

            if (modal_dialog) {
                this._modal_instance = modal_dialog;
                modal_dialog.setContentInstance(this);
            }

            this.events()
                .reDefinition();

            return this;
        },
        events: function () {
            this._events = [
                { parent: document, selector: '.edit_view_btn-copy', event: 'click', func: _self.onBtnCopy}, // Копируем запись(и))
                { parent: document, selector: '.edit_view_btn-delete', event: 'click', func: _self.onClickDelete}, // Копируем запись(и))
                { parent: document, selector: '.edit_view-save-input-hidden', event: 'click', func: EditView.saveContacts}, // save text by click on "Save"
                { parent: document, selector: '.edit_view_btn-save', event: 'click', func: _self.onSave },
                { parent: document, selector: '.edit_view .close-button', event: 'click', func: _self.onCancelDialog},
                { parent: document, selector: '.edit_view .emoji-wysiwyg-editor', event: 'change', func: _self.onChangeContentActivity},
                { parent: document, selector: '.task_message .btn.send_massage_activity', event: 'click', func: _self.onSendMessageActivity}
            ]
            Global.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        //спрацьовує коли карточка Записалась в DOM, визиває модал діалог.
        initView: function (data) {

        },
        setUrlAfterSave: function (url) {
            this.url_after_save = url;

            return this;
        },
        // addInitials: function () {
        //     var $list = $('[data-type="message"]');
        //
        //     try {
        //         $.each($list, function () {
        //             var initials,
        //                 $this = $(this),
        //                 name = $this.find('.user_comment_name').text();
        //
        //             initials = name.split(' ')[0].substring(0, 1);
        //             initials += name.split(' ')[1].substring(0, 1)
        //
        //             var $avatar_block = $this.find('.user_comment_pic');
        //
        //             if (!$avatar_block.find('.initials').length) {
        //                 $avatar_block.append('<span class="list-view-avatar initials">'+initials+'</span>');
        //             }
        //         });
        //     } catch (e) {
        //         console.log('Error by parse name in EditView');
        //     }
        //
        //     return this;
        // },
        reDefinition: function () {
            this.afterViewChanges = function () {
                var instance = Reports.getInstance();

                if (instance) {
                    instance.setCountLoadedGraph(null);
                    Reports.preloaderForGraph.remove($('.edit-view'));
                }
                // this.addInitials();

                return this;
            };

            //спрацьовує після callback (її потрібно визвати :) )
            this.afterLoadView = function (data) {
                History.addState(this);

                Global.addOperationInSDM();
                Global.getModel().money.groupSymbols($('.edit-view .money_type'));

                var key = Draft.createKeyByEV(this.getModel())
                this.setKeyOfDraft(key);
                this.getModalInstance()._keyOfDraft = key;

                Communication
                    .initTextArea()
                    .events()

                if (this.isAfterLoadViewCallBack()) {
                    this.callAfterLoadViewCallBack()
                }

                this.beginDraft = this.prepateDataForDraft(this.getModel(), this.beginDraft);

                if (data.draft) {
                    Message.showConfirmDialog.call(this,{
                        type: 'confirm',
                        message: Message.translate_local('Found data that you entered earlier but did not save. Download?'),
                        closeAfterConfirm: true
                    }, data.draft, this.onLoadDraft)
                }
            }

            this.onChange = function () {
                $('.form-control.time').each(function(i, ul){
                    initElements('.edit-view', $(ul).val());
                });

                return this;
            }
            return this;
        }
    };

    var instanceEditView = null;
    var EditViewContainer = function (data) {
        this.editView_shown = {};
    }

    EditViewContainer.prototype = Object.create(_Global.prototype);
    EditViewContainer.prototype.get = function () {
        return this.editView_shown;
    }
    EditViewContainer.prototype.getByIndex = function (index) {
        return this.editView_shown[index];
    }
    EditViewContainer.prototype.remove = function () {
        this.editView_shown = {};
    }
    EditViewContainer.prototype.removeByIndex = function (index) {
        delete this.editView_shown[index];
    }

    var ModelEditView = {
        $: null,
        _name_class_model: 'ModelEditView',
        //...other properties build dynamically
        copy_id: null,
        id: null,
        parent_copy_id: null,
        parent_data_id: null,
        pci: null,
        pdi: null,
        relate_template: null,
        template_data_id: null,
        this_template: null,
        
        constructor: function (data) {
            for (var key in data){
                this[key] = data[key];
            }

            return this;
        },
        setView: function () {
            var _this = this;
            var $editView = $('.edit-view:last');
            this.$ = $editView;

            Object.keys(this.$.data()).filter(function(item){
                _this[item] = _this.$.data(item);
            })
            var json = Url.getParams(location.href);
            this['finished_object'] = json && json.finished_object || 0; // 1 true 0 false

            $editView.data(this._name_class_model, this);

            return this;
        },
        drawReadOnlyFields: function () {
          this.showReadOnlyFields(this.$);
        },
    };

    var EditView = {
        saved_first_ev : false,
        _interface: 'EditView',
        _handler_after_save: null,

        _modal_instance: null,

        // реализация интерфейса
        implements: function (object) {
            object.implements.call(this);

            return this;
        },
        onKeyUp: function(e){
            if (e.keyCode === 27) { // esc
                redactor.close();
            }
            if ($('.edit-view.in').length) {
                EditView.saveDraftToLocalStorage(e);
            }

            return true;
        },
        saveDraftToLocalStorage: function(e) {
            clearTimeout(window.saveDraftTime);

            var ev = EditView.getInstance();
            var model = ev && ev.getModel();

            if (!model || $(e.target).is('.edit_view_btn-save')) { return };

            var data = ev.prepateDataForDraft(model, ev.beginDraft);

            window.saveDraftTime = setTimeout( function () {
                if (!model) return;

                var key = ev.getKeyOfDraft();
                var draft = JSON.parse(LocalStorageObject.readStorage(Draft.getKey()) || '{}');

                draft[key] = data;
                Console.log('saveDraftToLocalStorage()', draft);
                LocalStorageObject.writeStorage(Draft.getKey(), JSON.stringify(draft));
            }, Environments.sendDraftJsonToLocalStorage);

            return this;
        },
        editCard : function(element, _data, callback){
            var $element = $(element),
                _this = this;

            if (this._preloader) {
                this._preloader.modalRun();
            } else Preloader.modalShow();

            var _data = _data || {};

            switch($element.data('controller')){
                case 'sdm' :
                case 'edit_view_edit_sdm' :
                    element_data = $element.closest('.element_data');
                    _data['copy_id'] = element_data.data('relate_copy_id');
                    _data['id'] = element_data.data('id');
                    _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
                    _data['pci'] = 0;
                    _data['pdi'] = 0;

                    if($element.closest('.sm_extension').data('type') == 'submodule'){
                        _data['parent_copy_id'] = $element.closest('.edit-view').data('copy_id');
                        _data['parent_data_id'] = $element.closest('.edit-view').data('id');
                    }
                    _data['this_template'] = 0;
                    _data['from_template'] = 0;
                    _data['finished_object'] = 0;
                    break;

                case 'module_param' :
                    var sm_extension = $element.closest('.sm_extension');
                    _data['copy_id'] = sm_extension.data('parent_copy_id');
                    _data['id'] = sm_extension.data('parent_data_id');
                    _data['pci'] = 0;
                    _data['pdi'] = 0;
                    _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
                    _data['this_template'] = 0;
                    _data['from_template'] = 0;
                    _data['finished_object'] = 0;
                    break;

                case 'module_param_report' :
                case 'module_param_bpm' :
                    var sm_extension = $element.closest('.sm_extension');
                    _data['copy_id'] = sm_extension.data('copy_id');
                    _data['id'] = sm_extension.data('id');
                    _data['pci'] = sm_extension.data('parent_copy_id');
                    _data['pdi'] = sm_extension.data('parent_data_id');
                    _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
                    _data['this_template'] = sm_extension.data('this_template');
                    _data['from_template'] = 0;
                    _data['finished_object'] = 0;
                    break;
                default:
                    var sm_extension = $element.closest('.sm_extension');
                    _data['copy_id'] = sm_extension.data('copy_id');
                    _data['id'] = $(element).closest('.sm_extension_data').data('id');
                    _data['pci'] = sm_extension.data('parent_copy_id');
                    _data['pdi'] = sm_extension.data('parent_data_id');
                    _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
                    _data['this_template'] = sm_extension.data('this_template');
                    _data['from_template'] = 0;
                    _data['finished_object'] = 0;

                    var finished_object = sm_extension.find('.element.active[data-type="finished_object"]');
                    if(typeof finished_object != 'undefined' && finished_object.length){
                        _data['finished_object'] = 1;
                    }
                    break;
            }

            if(_data['template_data_id']){
                _data['from_template'] = 1;
                _data['id'] = _data['template_data_id'];
            }

            AjaxObj
                .createInstance()
                .setUrl(Global.urls.url_edit_view_edit +'/'+_data['copy_id'])
                .setData(_data)
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
                            // if (_this.isOpenCard($(data.data).find('.edit-view'))) {
                            //     return;
                            // }

                            if (EditView.getQueueStatus()) {
                                if (EditView.getCounterQueue() > 1 || EditView.getCounterQueue()== 0) {
                                    return;
                                }
                                EditView.clearQueue();
                            }

                            _this.getModalInstance()
                                .saveInstance(_this)
                                .show(data.data, true);

                            EditView
                                .createModel(data)
                                .setView()
                                .drawReadOnlyFields();

                            callback.call(_this, data);

                            _this.afterLoadView(data);
                        }
                    }
                    Preloader.modalHide();

                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                    Preloader.modalHide();
                })
                .setCallBackDone(function() {
                    $('.modal .contacts-block img').on('load', function() {
                        EditView.contactImg();
                    });
                    EditView.emptyFields();
                    EditView.hiddenBlocks();
                })
                .send()
        },

        getInstance : function(){
            return _self._instance;
        },

        getModel: function() {
            return $('.edit-view:last').data(ModelEditView._name_class_model)
        },

        isOpen: function () {

        },

        willExist: function () {
            var url = Url.createInstance().setUrl(location.href),
                json = url.parse();

            return (json && json.modal_ev) ? true : false;
        },
        createModel: function (data) {
            var model = Object.create(ModelGlobal.protected);

            for (var key in ModelEditView){
                model[key] = ModelEditView[key];
            }

            return model.constructor(data);
        },

        createInstance : function(modal_dialog){
            var Obj = function(){
                for(var key in EditView){
                    this[key] = EditView[key];
                }

                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return _self._instance = new Obj().constructor(modal_dialog);
        },

        // Удаляем запись(и))
        saveContacts : function(e, next) {
            var $element = $('.editable-block .edit-dropdown.open'),
                $editable = $element.closest('.editable-block');

            if ($(e.target).filter('input[type=text]').closest('.contact-item').length
                || !$element.length) return false;

            if (!next) {
                e ? e.stopPropagation() : '';
            }
            $editable.find('.editable-field').text($element.find('input').val()).css('opacity', '1');
            $editable.find('.edit-dropdown').removeClass('open');
            return false;
        },
        //obsolete - устаревшее
        setModalInstance: function (instance) {
            this._modal_instance = instance;

            return this;
        },
        //BASE
        getModal: function () {
            return this._modal_instance || modalDialog.createInstance();
        },
        //DEPRECATED
        getModalInstance: function () {
            return this._modal_instance || modalDialog.createInstance();
        },

        setPreloaderByMenu : function (_const) {
            var arr = [Preloader.PV, Preloader.REPORT],
                label = true,
                parent = this.getParent() || {};

            if (parent._interface != QuickViewPanel._interface || Communication.isCommunicationsModule()) {
                arr.push(Preloader.LV)
            } else {
                var instance = QuickViewPanel.getInstance();

                if (instance) {
                    instance.setPreloaderToView(instance._instance_preloader);
                    label = false;
                }
            }

            if (label) {
                Preloader.createInstance()
                    .setFromBlock(_const)
                    .setWhereContentHide(arr)
                    .run()
            } else {
                parent.getPreloader().show();
            }
        },
        setHandlerAfterSave : function (handler) {
            if (handler) {
                this._handler_after_save = handler;
            }

            return this;
        },

        setModifier : function (status) {
            if (status) {
                EditView.modifier = true;
            } else {
                delete EditView.modifier;
            }
        },
        changeBlockLoadedMessages : function ($activity) {
            var $block = $activity || $('.element[data-type="block_message"]'),
                $data = $block.find('[data-type="message"]');

            $.each($data, function () {
                var $preview = $(this).find('.image-preview.name');

                if ($preview.length && !$preview.attr('href').length) {
                    $preview.closest('.thumb-block').addClass('hidden')
                }
            });

            return this;
        },
        close : function () {
            delete EditView.modifier; // temp

            if (EditView.modifier) {
                delete EditView.modifier;
                //Message.show(Message.translate_local('Changes have not been saved. Do you want to save?'), true);
                var _data = {};
                // _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, true);
                // _data['parent_copy_id'] = $(_this).closest('.edit-view').data('copy_id');
                // _data['parent_data_id'] = $(_this).closest('.edit-view').data('id');
                // _data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                // _data['this_template'] = $(_this).closest('.edit-view').data('this_template');
                // _data['relate_template'] = $(_this).closest('.sm_extension[data-type="submodule"]').data('relate_template');
                _data['copy_id'] = $(modalDialog.getModalName()).find('.edit-view').data('copy_id');
                _data['status'] = 'changed';

                $.ajax({
                    url: Global.urls.url_edit_view_edit +'/'+_data['copy_id'],
                    data : _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data) {
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        }
                        Preloader.modalHide();
                    }
                });
            } else {
                modalDialog.hide();
            }
        },
        setTitle : function(view) {
            var client = view.find('.client-name') || $([]);

            if (client.length && client.find('.edit-dropdown.open').length || client.closest('[data-module="process"]').length) {
                client.each(function() {
                    var _this = $(this);
                    var value = _this.find('input.element[data-type="module_title"]').val();

                    if (_this.is('[data-type="field_type_hidden"]')) { // for constructor block with avatarom
                        _this.find('[data-type="title"]').val(value);
                    }
                    if (value) {
                        _this.find('span.editable-field').removeClass('empty').text(value);
                    }
                });
                client.find('.element[data-type="module_title"]').removeClass('opacityOut');
                client.find('.editable-field').attr('style','');

                EditView.setModifier(true);
            }
        },

        replaceForLink: function (message) {
            var list = message || $('.user_comment[data-type="message"]');

            list.each(function () {
                var  _this = $(this);
                var userComment = _this.find('.user_comment_text');
                var reg = /(www\.\w*\.[\w]*)|((htt)\w+[:]\/\/([\w:\/_.?=&%+#A-Z-\[\]+;\,{}]*))/igm

                var text = userComment.html().replace(reg, function(s){
                    s = s.replace('&nbsp;','');
                    return "<a href='"+ (/:\/\//.exec(s) === null ? "http://" + s : s ) + "' target='_blank'>" + s +"</a>";
                });
                _this.find('.user_comment_text').html(text);
            });
        },

        getEditViewUrl : function(){
            var url;
            var copy_id = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('copy_id');

            switch(copy_id){
                case 8:
                    url = Global.urls.url_edit_view_report_constructor;
                    break;
                default :
                    url = Global.urls.url_edit_view_edit;
            }

            return url;
        },

        show : function(copy_id, id, url, callback){
            var _data = {},
                _this = this,
                preloader = this.getPreloader(),
                sm_extension = $('.sm_extension');

            _data['copy_id'] = copy_id;
            _data['id'] = id;
            _data['pci'] = sm_extension.data('parent_copy_id');
            _data['pdi'] = sm_extension.data('parent_data_id');
            _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, false);
            _data['this_template'] = sm_extension.data('this_template');
            _data['from_template'] = 0;

            if (preloader) {
                preloader.modalRun();
            } else Preloader.modalShow();

            $.ajax({
                url: url,
                data : _data,
                dataType: "json",
                type: "POST",
                success: function(data) {
                    if(data.status == 'error' || data.status == 'access_error'){
                        Message.show(data.messages, false);
                        HeaderNotice.refreshAllHeaderNotices();
                    } else {
                        if(data.status == 'data'){
                            var modelEditView = EditView.createModel(data)
                            History.add(copy_id, '', {});

                            var modal = _this.getModalInstance() || modalDialog.createInstance();

                            modal.saveInstance(_this)
                                .show(data.data, true);

                            modelEditView
                                .setView()
                                .drawReadOnlyFields();

                            EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+copy_id+'"]'));
                            EditView.changeBlockLoadedMessages();

                            niceScrollInit();
                            imagePreview();
                            $('.form-control.time').each(function(){
                                initElements('.edit-view', $(this).val());
                            });
                            EditView.activityMessages.init();
                            textAreaResize();

                            if(typeof callback == 'function'){
                                callback();
                            }

                            _this.afterLoadView(data);
                        }
                    }
                    Preloader.modalHide();
                },
                error: function(){
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                }
            }).done(function() {
                $('.modal .contacts-block img').on('load', function() {
                    EditView.contactImg();
                });
                EditView.hiddenBlocks();
                EditView.textRedLine();
                jScrollRemove();
                jScrollInit();
                setTimeout(function(){ jScrollRemove(); jScrollInit(); }, 200);
                Global.createLinkByEV($('.edit-view.in:last'));
                //Temp.initSubModule();
            });

        },

        prepareData : function(copy_id, params) {
            var params = params || {};
            var _this = $('.edit-view:visible[data-copy_id="'+copy_id+'"]');
            var  date_time = '';

            // данные формы модуля
            _this.find('.element_data[data-type="module_title"], .element_edit_hidden, .element[data-type="block_panel_contact"] .file-box, .element[data-type="block"] .element[data-type="panel"] .file-box, .element[data-type="block"] .element[data-type="attachments"], .element[data-type="block"] .element[data-type="block_activity"], input[type="text"], input[type="password"], input[type="email"], input[type="submit"], input[type="button"], input[type="hidden"]:not(.upload_file), input:checked, textarea, select, .element_module').each(function(e){
                var $this = $(this),
                    value = $this.val();

                if($this.hasClass('date')){
                    if(value) date_time = value; else date_time = '';
                } else
                if($this.hasClass('time')){
                    if(value)
                        if(date_time) date_time += ' ' + value;

                    params[$this.attr('name')] = date_time;
                    date_time = '';
                } else
                if($this.hasClass('date-time')){
                    params[$this.attr('name')] = {
                        'date_time' : value,
                        'all_day' : $this.data('all_day')
                    };
                } else
                if($this.hasClass('element_data')){
                    params[$this.data('name')] = $this.text();
                } else
                //file-box
                if($this.hasClass('file-box')){
                    var _files = [];
                    $this.find('input.upload_file').each(function(i, ul){
                        if($(ul).val()) _files.push($(ul).val());
                    });
                    if($.isEmptyObject(_files)){
                        _files = '';
                    }
                    params[$this.data('name')] = _files;
                } else
                //attachments
                if($this.hasClass('element') && $this.data('type') == 'attachments'){
                    var _files = [];
                    $this.find('.file-box .file-block[data-type="attachments"]').each(function(i, ul){
                        $(this).find('input.upload_file').each(function(i, ul){
                            if($(ul).val()) _files.push($(ul).val());
                        });
                    })
                    if($.isEmptyObject(_files)){
                        _files = '';
                    }
                    params[$(this).data('name')] = _files;
                } else
                //block_activity
                if($(this).hasClass('element') && $(this).data('type') == 'block_activity'){
                    var activity_messages = [];
                    $(this).find('.element[data-type="message"]').each(function(i, ul){
                        if($(ul).data('status') == 'temp') activity_messages.push($(ul).data('id'));
                    });
                    params['element_block_activity'] = activity_messages;
                } else
                if($(this).hasClass('element_edit_hidden')){
                    params[$(this).data('name')] = $(this).text();
                } else
                if($(this).hasClass('element_edit_access')){
                    params[$(this).attr('name')] = {'id' : $(this).val(), 'type' :  $(this).find('option[value="'+$(this).val()+'"]').data('type')};
                } else
                if($(this).hasClass('element_module')) {
                    params[$(this).attr('name')] = $(this).data('id');
                } else if ($(this).attr('name')){
                    params[$(this).attr('name')] = $(this).val();
                }
                // $('.table-section .crm-table-wrapper').getNiceScroll().remove();
                // niceScrollInit();
            });


            // edit-view params
            params['id'] = _this.data('id');
            params['parent_copy_id'] = _this.data('parent_copy_id');
            params['parent_data_id'] = _this.data('parent_data_id');
            params['this_template'] = _this.data('this_template');
            params['relate_template'] = _this.data('relate_template');
            params['block_unique_index'] = _this.data('block_unique_index');
            params['auto_new_card'] = _this.data('auto_new_card');
            params['unique_index'] = _this.data('unique_index');
            params['params'] = _this.data('params');

            if(_this.data('template_data_id')){
                params['template_data_id'] = _this.data('template_data_id');
                params['from_template'] = 1;
            }

            // родительская форма
            if(!params['parent_copy_id'] && !params['parent_data_id']){
                var pci = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id');
                var pdi = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id');
                if(pci) params['pci'] = pci;
                if(pdi) params['pdi'] = pdi;
            }

            // субмодули
            var params_submodule = [];
            var submodule_tmp = [];
            $(modalDialog.getModalName() + ' .sm_extension[data-type="submodule"]').each(function(i, ul){
                $(ul).find('table tbody tr').each(function(i1, ul1){
                    submodule_tmp.push($(ul1).data('id'));
                });
                params_submodule.push({
                    'relate_table_module_id' :  $(ul).data('relate_table_module_id'),
                    'data_id_list': submodule_tmp,
                });
                submodule_tmp = [];
            });
            params['submodules'] = params_submodule;

            // element_relate
            params_submodule = [];
            _this.find('.element_relate, .element_relate_dinamic').each(function(i, ul){
                if($(ul).hasClass('element_relate_this') || $(ul).hasClass('element_filter')) return true;
                if($(ul).data('save') == false) return true;
                var relate_params = {
                    'name' : $(ul).attr('name'),
                    'relate_copy_id' :  $(ul).data('relate_copy_id'),
                    'id': $(ul).data('id'),
                };
                if($(ul).attr('disabled') == 'disabled') relate_params.disabled = 1;
                params_submodule.push(relate_params);
            });

            params['element_relate'] = params_submodule;

            // element_relate_this
            _this.find('.element_relate_this').each(function(i, ul){
                params[$(ul).attr('name')] = $(this).data('id');
            });

            // element_relate_participant
            var element_responsible = [];
            _this.find('.element_relate_participant').each(function(i, ul){
                element_responsible.push({
                    'name' : $(ul).attr('name'),
                    'participant_id': $(ul).data('participant_id'),
                    'ug_id': $(ul).data('ug_id'),
                    'ug_type': $(ul).data('ug_type'),
                    'responsible' : "1",
                });
            });
            params['element_responsible'] = element_responsible;

            // block_attributes: block_participant and other
            var block_attributes = {
                'block_participant' : {}
            };

            var block_participant_type_list = ['participant', 'email'];
            for(var i=0; i<block_participant_type_list.length; i++){
                var data_type = block_participant_type_list[i];

                var element_participant_id = [];
                var element_participant = [];

                //participant
                if(data_type == 'participant'){
                    _this.find('.element[data-type="block_participant"] .element[data-type="block-card"]>.element[data-type="' + data_type + '"]').each(function(i, ul){
                        var participant_id = $(ul).data('participant_id');
                        if(participant_id){
                            element_participant_id.push({
                                'participant_id': participant_id,
                                'ug_id': $(ul).data('ug_id'),
                                'ug_type': $(ul).data('ug_type'),
                                'responsible': $(ul).data('responsible'),
                            });
                        } else {
                            element_participant.push({
                                'participant_id': null,
                                'ug_id': $(ul).data('ug_id'),
                                'ug_type': $(ul).data('ug_type'),
                                'responsible': $(ul).data('responsible'),
                            });
                        }
                    });
                } else
                //email
                if(data_type == 'email'){
                    _this.find('.element[data-type="block_participant"] .element[data-type="block-card"]>.element[data-type="' + data_type + '"]').each(function(i, ul){
                        var participant_email_id = $(ul).data('participant_email_id');
                        if(participant_email_id){
                            element_participant_id.push({
                                'participant_email_id': participant_email_id,
                                'email_id' : $(ul).data('email_id'),
                            });
                        } else {
                            element_participant.push({
                                'participant_email_id': null,
                                'email_id' : $(ul).data('email_id'),
                            });
                        }
                    });
                }

                block_attributes['block_participant'][data_type] = {
                    'element_participant_id': element_participant_id,
                    'element_participant': element_participant,
                }
            }

            params['block_attributes'] = block_attributes;
            params['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this.find('div'), (EditView.countEditView() == 1 ? true : false));

            return params;
        },
        save : function(copy_id, params, callback, send_url){
            var _this = $('.edit-view:visible[data-copy_id="'+copy_id+'"]'),
                _contentType = 'application/x-www-form-urlencoded',
                _object = this;

            var _params = this.prepareData(copy_id, params);
            var $btn = _this.find('.edit_view_btn-save');
            var instance = this;

            Global.btnSaveSetDisabled($btn, true);

            if(typeof(send_url) == 'undefined' || send_url == false) send_url = Global.urls.url_edit_view_edit +'/'+copy_id;

            // сохраняем
            $.ajax({
                url : send_url,
                data : _params,
                type : 'POST', dataType: "json",
                contentType: _contentType,
                success: function(data){

                    if(data.status == 'save'){

                        QuickViewPanel.updateContent();
                        Draft.removeDraftFromLocalStorage(instance.getKeyOfDraft);

                        /*
                    if(!_this.data('id') && _this.data('template_data_id') && data.id){
                        _this.data('id', data.id);
                        var params_relate = {
                            'id': data.id,
                            'primary_entities': EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this.find('div'), (EditView.countEditView() == 1 ? true : false))
                        };
                        $.post($('#global_params').data('url_edit_view_update_relate_for_template') + '/' + copy_id, params_relate);
                    }
                    */
                        if(typeof(data.attributes_data) != 'undefined' && data.attributes_data && typeof(data.attributes_data.module_title) != 'undefined' && data.attributes_data.module_title){
                            $(_this).find('.element[data-type="module_title"]').val(data.attributes_data.module_title);
                            $(_this).find('.element_data[data-type="module_title"]').text(data.attributes_data.module_title);
                        }
                        //$(edit_view).closest('.edit-view').find('.element[data-type="module_title"]').text(data.attributes_data.module_title);


                        _this.closest('.edit-view').data('template_data_id', '');
                        var parent_copy_id = _this.data('parent_copy_id');
                        // if from SM

                        if(parent_copy_id){
                            if(typeof(callback) == 'function' || _object._handler_after_save){
                                callback(data);
                            } else {
                                modalDialog.hide(false, data);
                                imagePreview();
                                EditView.textRedLine();
                                jScrollRemove();
                                jScrollInit();
                                EditView.textRedLine();

                                if(data.ev_refresh_field) {
                                    fields = JSON.parse(data.ev_refresh_field);
                                    for (id in fields) {
                                        $('#'+id).val(fields[id]).change();
                                    }
                                }

                                if(data.auto_next_card != false){
                                    var _edit_view_object = $('.edit-view[data-copy_id="'+parent_copy_id+'"]');
                                    _edit_view_object.data('auto_new_card', data.id);
                                    $( '.panel-body[data-relate_copy_id="' + data.auto_next_card + '"] .submodule_edit_view_dnt-create-select' ).trigger('click');
                                } else {
                                    if(data.params){
                                        var params = JSON.parse(JSON.stringify(data.params));
                                        //дополнительная обработка
                                        if(params['create_new_card']) {
                                            $( '.edit-view[data-copy_id="' + params['parent_copy_id'] + '"]').data('default_data', JSON.stringify(data.params['default_data']));
                                            $( '.panel-body[data-relate_copy_id="' + params['this_copy_id'] + '"] .submodule_edit_view_dnt-create' ).trigger('click');
                                        }
                                        if(params['edit_next_card']){
                                            $('.panel-body[data-relate_copy_id="' + params['copy_id'] + '"] .sm_extension_data[data-id="'+params['id']+'"] .submodule_edit_view_dnt-edit').trigger('click');
                                        }
                                    }
                                }
                            }
                            Global.btnSaveSetDisabled($btn, false);
                        } else {
                            if (_object._handler_after_save) {
                                callback = _object._handler_after_save;
                            }
                            if(typeof(callback) == 'function'){
                                callback(data);
                                Global.btnSaveSetDisabled($btn, false);
                            } else {
                                var element,
                                    url,
                                    data_id_list = [],
                                    sorting_list_id = ProcessView.sorting_list_id;

                                History.close(true);
                                modalDialog.hideAll();

                                // for new Project
                                if(typeof data['show_child_list_entities'] != 'undefined' && data['show_child_list_entities']){

                                    var content_vars = data['show_child_list_entities']['vars'];
                                    if(content_vars){
                                        instanceGlobal.preloaderShow($([]), {project: true});

                                        instanceGlobal.contentReload
                                            .addContentVars(content_vars)
                                            .clear()
                                            .setActionKey(data['show_child_list_entities']['action_key'])
                                            .setVars(data['show_child_list_entities']['vars'])
                                            .run();

                                        return true;
                                    }
                                }


                                if(_params['id'] !== null && _params['id']){
                                    data_id_list.push(_params['id']);
                                }
                                if(data['id'] !== null && data['id'] && _params['id'] != data['id']){
                                    data_id_list.push(data['id']);
                                }


                                if (callback == null) {
                                    if (instanceGlobal.currentInstance.type == PAGE_IT_REPORTS && instanceGlobal.currentInstance._open_sub_link) {
                                        delete instanceGlobal.currentInstance._open_sub_link;
                                        element = $('.crm-table-wrapper a.modal_dialog:first');

                                    } else element = $('#content_container');
                                }

                                var parent;
                                if (_object.getParent) {
                                    parent = _object.getParent();
                                }

                                if (parent && parent._type == QuickViewPanel._type) {
                                    QuickViewPanel.updateContent(true);

                                    if (!Communication.showPreloader()) {
                                        return this;
                                    }
                                } else {
                                    if (_object.showPreloader && _object.isPreloader()) {
                                        _object.showPreloader();
                                    } else {
                                        if (!parent
                                            || parent._type != QuickViewPanel._type
                                            || Communication.isCommunicationsModule()) {
                                            instanceGlobal.preloaderShow(element, {
                                                status: 'create'
                                            })
                                        }
                                    }
                                }

                                if (Events.getCountLine(Events.TYPE_UPDATE_DATA)) {
                                    Events.runHandler(Events.TYPE_UPDATE_DATA, {
                                        event: null,
                                        data: null
                                    });
                                } else {
                                    var _list_exist = true,
                                        url = _object.url_after_save;
                                    var data = {
                                        'module' : {
                                            'data_id_list' : data_id_list
                                        }};
                                    var $element = _object.getParentElement && _object.getParentElement();

                                    if (!$element || $element && !$element.closest('.filter-block').length) {
                                        data.module['sorting_list_id'] = ProcessView.sorting_list_id;
                                    } else {
                                        _list_exist = false;
                                    }

                                    Global.getInstance().setContentReloadInstance(instanceGlobal.contentReload);
                                    instanceGlobal.contentReload
                                        .prepareVariablesToGeneralContent()
                                        .setUrl(url)
                                        .setCallBackSuccessComplete(function(){

                                            // if processView:
                                            if(Global.isProcessView()){
                                                //1 - preloader
                                                var param = { status: 'create' },
                                                    sorting_list_id_list = ProcessView.convert.dataIdToSortingListIdList(data_id_list),
                                                    instance = ProcessView.getInstance();

                                                if (instance) {
                                                    $.each(sorting_list_id_list, function(i, id){ // ????
                                                        if(instance.$panel_change && instance.$panel_change.attr('data-sorting_list_id') != id){
                                                            param = {
                                                                $: $('section[data-sorting_list_id="'+id+'"]')
                                                            };
                                                            return false;
                                                        }
                                                    })
                                                }

                                                instanceGlobal.preloaderShow(element, param);
                                                instance.initLists(sorting_list_id_list);

                                                // 2 - removing empty lists
                                                if ($('section.panel[data-sorting_list_id]').length>1) {
                                                    $.each($('section.panel[data-sorting_list_id=""]'), function (key, value) {
                                                        var $value = $(value);
                                                        if (!$value.find('li').length) {
                                                            $value.remove();
                                                        }
                                                    })
                                                }

                                                instanceGlobal.contentReload.preloaderHide();

                                                // 3 - removing empty list
                                                if(sorting_list_id){
                                                    $('.process_view_block.sm_extension .element[data-name="process_view_panel"] .element[data-name="panel"] .panel[data-sorting_list_id="' + sorting_list_id + '"]').each(function(i, ul){
                                                        instance.checkAndRemoveEmptyPanel($(ul).closest('.element[data-name="panel"]'));
                                                    })
                                                }
                                            }
                                        })
                                        .appendVars(data)
                                        .prepareVariablesToProcessView(_list_exist)
                                        .run();
                                }
                            }
                        }

                        // открытие формы, что не прошла валидацию
                    } else {
                        if(data.status == 'data'){
                            /*var blocks = [];
                        _this.find('.element.panel .column').each(function(){
                            blocks.push($(this));
                        });*/

                            Global.blockErrors.init();
                            var blocks_errors = [];
                            $(data.data).find('.element.panel .column').each(function(){
                                var _this = $(this);
                                if (_this.find('.file-block').length) {
                                    _this = _this.closest('.columns-section');
                                }
                                blocks_errors.push(_this);
                            });
                            $('.errorMessage').remove();
                            $('.b_error').removeClass('b_error');

                            var i = 0;
                            for(; i < blocks_errors.length; i++){
                                var error, name,
                                    checkElName = blocks_errors[i].find('.errorMessage').closest('.column').find('*[name*="EditViewModel"]').attr('name'),
                                    block = _this.find('.element.panel .column *[name="'+checkElName+'"]').closest('.column');

                                if (block.length>1) {
                                    name = blocks_errors[i].find('>div[data-name*="EditViewModel"]').data('name');

                                    block = _this.find('.element.panel .columns-section [data-name="'+name+'"]').find('.column');
                                };

                                block.find('.errorMessage').remove();
                                error = blocks_errors[i].find('.errorMessage');

                                if (error.length){
                                    //blocks[i].append(error[0].outerHTML);
                                    block.addClass('b_error').append(error[0].outerHTML);
                                }
                            }

                            // если не использовать EditView.activityMessages.init() то можно удалить еэти 2 строки
                            //edit_view.find('.task_comments .emoji-wysiwyg-editor').remove();
                            //edit_view.find('.task_comments .emoji-button').remove();

                            //$(_this).closest('.edit-view').html(data.data);
                            imagePreview();
                        } else {
                            if(data.status == 'access_error'){
                                imagePreview();
                                Message.show(data.messages, false);
                                // ошибка окрытия формы
                            } else {
                                if(data.status == 'error'){
                                    imagePreview();
                                    Message.show(data.messages, false);
                                }
                            }
                        }
                        EditView.scrollToError();
                        Global.btnSaveSetDisabled($btn, false);
                    }
                    //EditView.activityMessages.init();
                },
                error: function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                    Global.btnSaveSetDisabled($btn, false);
                },
            }).done(function(){
                EditView.textRedLine();
                jScrollRemove();
                jScrollInit();
                EditView.textRedLine();
            });
        },

        // сохраняем состояние блоков в userStorage
        saveBlockDisplayStatus : function(object){
            var value = [];
            var copy_id = $(object).closest('.edit-view').data('copy_id');
            if(typeof(copy_id) == 'undefined' || !copy_id) return;

            $(object).closest('.panel-body').find('.element[data-type="block"]').each(function(i, ul){
                var block = $(ul);
                var unique_index = block.data('unique_index');

                var a_switch = block.find('.element[data-type="switch"]');
                var status = 'fa-chevron-up';
                if(a_switch.hasClass('fa-chevron-down')) status = 'fa-chevron-down';

                value.push({'unique_index' : unique_index, 'status' : status});

            });

            if(value){
                var index = 'editView' + '_' + $(object).closest('.edit-view').data('copy_id');
                var lStorage = new LocalStorage();
                lStorage
                    .clear()
                    .setKey('ev_block_display')
                    .setValueToServer(index, value);
            }
        },


        // сохраняем состояние блоков в userStorage
        setBlockDisplayStatus : function(popup_object){
            $(popup_object).find('.panel-body').find('.panel-body').each(function(i, ul){
                var a_switch = $(ul).closest('.panel').find('.element[data-type="switch"]');
                var status = 'fa-chevron-up';
                if(a_switch.hasClass('fa-chevron-down')) status = 'fa-chevron-down';
                Global.setBlockDisplay($(ul), status, 0);
            });
        },

        addCard : function(element, _data, callback){
            var $element = $(element),
                _this = this,
                sm_extension = $element.closest('.process_view_block.sm_extension, .list_view_block.sm_extension, .edit-view.sm_extension, .bpm_block.sm_extension, .right-sidebar'),
                copy_id =  this.copy_id || sm_extension.data('copy_id');

            _data = _data;

            EditView.checkServiceParams(_data, copy_id, function(_data) {
                var _data = _data || {};

                if ($element.closest('.process_view_block').length) {
                    instanceGlobal.preloaderShow($element);
                }

                _data['pci'] = sm_extension.data('parent_copy_id');
                _data['pdi'] = sm_extension.data('parent_data_id');
                _data['this_template'] = sm_extension.data('this_template');
                _data['finished_object'] = 0;
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));

                if(sm_extension.hasClass('edit-view')){
                    _data['finished_object'] = sm_extension.data('finished_object');
                } else {
                    var finished_object = sm_extension.find('.element.active[data-type="finished_object"]');
                    if(typeof finished_object != 'undefined' && finished_object.length){
                        _data['finished_object'] = 1;
                    }
                }

                Preloader.modalShow();
                $.ajax({
                    url: Global.urls.url_edit_view_edit + '/' + copy_id,
                    dataType: "json",
                    data : _data,
                    type: "POST",
                    success: function(data) {
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                callback.call(_this, data);
                                initElements('.edit-view', $('.time').last().val());
                            }
                        }
                        Preloader.modalHide();
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                        Preloader.modalHide();
                    },
                }).done(function() {
                    $('.modal .contacts-block img').on('load', function() {
                        EditView.contactImg();
                    });
                    EditView.emptyFields();
                    EditView.hiddenBlocks();
                });

                //Preloader.hide();
            });
        },

        checkServiceParams : function(_data, copy_id, callback){
            if(copy_id!==Communication._copy_id){
                if(typeof(callback) == 'function') {
                    return callback(_data);
                }
                else {
                    return;
                }
            }

            $.ajax({
                url: '/module/communication/CheckServiceParams' + '/' + Communication._copy_id,
                dataType: "json",
                data: null,
                type: "POST",
                success: function (data) {
                    if (data.status == 'access_error') {
                        Message.show(data.messages, false);
                    } else {
                        if(data.status == 'error'){
                            Message.show(data.messages);
                        } else {
                            setTimeout(function(){
                                if(!data.check){
                                    Communication.openMenuServices();
                                } else {
                                    if(typeof(callback) == 'function'){
                                        callback(_data);
                                    }
                                }
                            }, 200);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    callback();
                },
            });
        },

        runAfterAddCardLV : function(data){
            if(data.status == 'data'){
                var model = EditView.createModel(data);

                this.getModalInstance()
                    .saveInstance(this)
                    .show(data.data, true);

                model
                    .setView()
                    .drawReadOnlyFields();

                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+data.copy_id+'"]'));
                History.add(data.copy_id, '', {});
                jScrollInit();

                Global.addOperationInSDM();

                niceScrollCreate($('.submodule-table'));
                var $modal = $(modalDialog.getModalName()).find('.client-name');
                if ( $modal.find('span').first().text() == "" ) {
                    $modal.find('.edit-dropdown').first().addClass('open');
                }
            }
            EditView.activityMessages.init();
            textAreaResize();
        },


        runAfterEditCardLV : function(data){
            if(data.status == 'data'){
                Global.addOperationInSDM();

                Global.getModel().money.groupSymbols($('.edit-view .money_type'));
                this.setBlockDisplayStatus($('.edit-view[data-copy_id="'+data.copy_id+'"]'));
                imagePreview();
                this.textRedLine();
                jScrollRemove();
                jScrollInit();
                this.textRedLine();
                niceScrollCreate($('.submodule-table'));

                this.activityMessages.init();
                textAreaResize();
            }
        },
        onLoadDraft: function (draft) {
            Draft.removeDraft(this.getKeyOfDraft());

            var data = draft.data;
            var $editView = $('.edit-view.in');

            Object.keys(data).filter(function (item) {
                var value = data[item];

                switch (item) {
                    case "b_date_ending": {
                        var object = $editView.find('[name="EditViewModel[b_date_ending]"]');
                        var $root = object.closest('.crm-dropdown');
                        $root.find('>label').removeAttr('datetime');

                        UI.setDataTimeBlock($root, value);

                        break;
                    }
                    case "activity": {
                        var root = $editView.find('.task_message');
                        redactor.setContent(root, {
                            message:{
                                text: value
                            },
                            attachment: []
                        }, {
                            id: 0
                        });
                        break;
                    }
                    case "module_title": {
                        $editView.find('[data-name="EditViewModel[module_title]"]').text(value);
                        break
                    };

                    default: break;
                }
            });

            $editView.find('input[type=text]').each(function(item) {
                var $this = $(this);
                var id = $this.attr('id');

                if (!id) return;

                var value = data[id];
                $this.val(value);
            });

            $editView.find('button[data-id]').each(function(item) {
                var $button = $(this);
                var $select = $button.parent().prev();
                var id = $select.attr('id');

                if (!id) return true;

                var value = data[id];

                if (value) {
                    if (id == "b_status") {
                        UI.setSelect($select, value, 'data-content');
                    } else {
                        UI.setSelect($select, value);
                    }
                }
            });

            $editView.find('.form-datetime').each(function(item) {
                var $this = $(this);
                var $date = $this.find('.date');
                var $time = $this.find('.time');
                var $common = $this.find('input');
                var value = $common.attr('name');
                value = data[value.substring(14, value.length-1)];

                if (value) {
                    if ($date.length) {
                        var v = value.toString().split(' ')[0];
                        $date.removeData('datepicker');
                        $date.attr('value', v).val(v);
                    }

                    if ($time.length) {
                        var v = value.toString().split(' ')[1];
                        $time.removeData('timepicker');
                        $time.attr('value', v).val(v);
                    }
                }
            });

            this.onChange();
            //modalDialog.hide();
        },
        addCardFromTemplate : function(_this, _default_data){
            var $this = $(_this),
                _data = {},
                instance = EditView.createInstance(),

                $smExtension = $this.closest('.sm_extension'),
                project_select = $smExtension.find('.element[data-type="project_select"]').val(),
                project_name = $smExtension.find('.element[data-type="project_name"]').val();

            if(_default_data){
                _data.default_data = _default_data;
            }

            switch (project_select) {
                case 'from_template': {
                    _data.template_data_id = $smExtension.find('.element[data-type="template"]').val();
                    _data.module_title = project_name;

                    instance.editCard( _this, _data,
                        function(data){
                            instance.runAfterEditCardLV(data);
                        }
                    );

                    break;
                }
                case 'new_card': {
                    var block_field_name = $smExtension.find('.element[data-type="block_field_name"]').val();

                    if(block_field_name) {
                        var obj = {},
                            block_unique_index = $smExtension.find('.element[data-type="block"]').val();

                        obj[block_field_name] = block_unique_index;
                        _data.default_data = obj;
                    }

                    _data.module_title = project_name;

                    instance.addCard(_this, _data, function(data){
                        instance.runAfterAddCardLV(data);
                    });
                    break;
                }
                default: break;
            }
        },

        cardSelectValidate : function(_this, callback){
            /*
        var project_name = $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').val();
        if(!project_name){
            Message.show([{
                    'type':'error',
                    'message': 'You must fill in the "{s}"',
                    'params' : {'s' : $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').closest('li').find('.inputs-label').text()}}]
                    , true, function(){
                        callback(false);
                    });
            return;
        }

        var project_select = $(_this).closest('.sm_extension').find('.element[data-type="project_select"]').val();
        var template_select = $(_this).closest('.sm_extension').find('select.element[data-type="template"] option');

        if(project_select == 'from_template' && (!template_select || !template_select.length)){
            Message.show([{
                    'type':'error',
                    'message': 'You must fill templates'
                    }]
                , true, function(){
                    callback(false);
                });
            return;
        }

        callback(true);
        */
            var projectNameMessage = $('#project_name_error');
            projectNameMessage.hide();
            $('#block_error').hide();
            $('#template_error').hide();
            var project_name = $(_this).closest('.sm_extension').find('.element[data-type="project_name"]').val();
            if(!project_name){
                projectNameMessage.show().closest('.column').addClass('b_error');
                callback(false);
            }

            var project_select = $(_this).closest('.sm_extension').find('.element[data-type="project_select"]').val();
            if(project_select == 'from_template'){
                var block_field_name = $(_this).closest('.sm_extension').find('.element[data-type="block_field_name"]').val();
                if(block_field_name) {
                    //используются блоки
                    var block_select = $(_this).closest('.sm_extension').find('select.element[data-type="block"]').val();
                    if(block_select=='' || !block_select){
                        $('#block_error').show().closest('.column').addClass('b_error');
                        callback(false);
                        return;
                    }
                }

                var template_select = $(_this).closest('.sm_extension').find('select.element[data-type="template"]').val();
                if(template_select=='' || !template_select){
                    $('#template_error').show().closest('.column').addClass('b_error');
                    callback(false);
                    return;
                }
            }
            if(!project_name){
                return;
            }
            callback(true);
        },

        addCardSelect : function(_this, parent_class, _data){
            var copy_id = _this.data('copy_id');

            if(!_data) var _data = {};

            _data['parent_copy_id'] = _this.data('parent_copy_id'),
                _data['parent_data_id'] = _this.data('parent_data_id'),
                _data['this_template'] = _this.data('this_template'),
                _data['parent_class'] = parent_class,
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));
            _data['finished_object'] = 0;

            var finished_object = $(_this).find('.element.active[data-type="finished_object"]');
            if(typeof finished_object != 'undefined' && finished_object.length){
                _data['finished_object'] = 1;
            }


            $.ajax({
                url: Global.urls.url_edit_view_edit_select + '/' + copy_id,
                data: _data,
                dataType: "json",
                type: "POST",
                success: function(data) {
                    if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else {
                        if(data.status == 'error'){
                            Message.show(data.messages);
                        } else {
                            if(data.status == true){
                                modalDialog.show(data.data, true);
                                jScrollInit();
                                niceScrollCreate($('.submodule-table'));
                                imagePreview();
                                $('.form-control.time').each(function(){
                                    initElements('.edit-view', $(this).val());
                                });
                            }
                        }
                    }
                },
                error: function(){
                    Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                },
            });
        },




        /**
         * changeTemplateValue
         */
        changeTemplateValue : function(_this){
            var objects = {};
            var copy_id = $(_this).closest('.sm_extension').data('copy_id');
            var template = $(_this).closest('.sm_extension').find('.element[data-type="template"]');
            if(template.data('changed') != '1') return;

            var data_id = template.val();
            if(!data_id){
                $(_this).closest('.sm_extension').find('.element[data-type="dinamic"]').remove();
                return;
            }


            if($(_this).closest('.modal-dialog').hasClass('edit-view') == false){
                objects.binding_object = null;
            }
            objects.participants = null;

            var data = {
                'action' : ProcessObj.PROCESS_BPM_PARAMS_ACTION_CHECK,
                'process_id' : data_id,
                'objects' : objects
            }

            var ajax = new Ajax();
            ajax
                .setData(data)
                .setAsync(false)
                .setUrl('/module/listView/changeTemplateValue/' + copy_id)
                .setDataType('json')
                .setCallBackSuccess(function(data){
                    if(data.status == 'access_error' || data.status == 'error'){
                        Message.show(data.messages, false);
                    } else {
                        $(_this).closest('.sm_extension').find('.element[data-type="dinamic"]').remove();
                        if(data.status == false){
                            $(_this).closest('.sm_extension').find('.element[data-type="objects"]').children('li:last').after(data.message);
                        }
                    }

                })
                .setCallBackError(function(jqXHR, textStatus, errorThrown){
                    Message.showErrorAjax(jqXHR, textStatus);
                })
                .send();
        },






        contactImg : function(){
            $img = $('.modal .contacts-block img.thumb')
            if ($img.width() > $img.height()) {
                var widImg = ($img.width()-$img.closest('a').width())/2;
                $('.modal .contacts-block img.thumb').css({'margin-left':'-'+widImg+'px', 'margin-top':'0'});
            } else {
                var widImg = ($img.height()-$img.closest('a').height())/2;
                $('.modal .contacts-block img.thumb').css({'margin-top':'-'+widImg+'px' , 'margin-left':'0'});
            }
        },

        emptyFields : function(){
            $('span.client-name .editable-field').each(function(){
                if ($(this).html() == '') {
                    $(this).addClass('empty');
                }

            });
        },

        textRedLine : function($this){
            if ($this) {
                if ($this.hasClass('submodule-link')) {
                    $this.find('.list-view-avatar').each(function(){
                        if($(this).closest('.edit-view').length && $(this).closest('.name').height() > 20) {
                            $(this).css('float','left');
                            $(this).closest('.name').css({'white-space':'normal', 'line-height':'16px'});
                            if ($(this).closest('.name').height() > 40) {
                                $(this).closest('.name').css({ 'word-break':'break-all',
                                    'max-height':'32px',
                                    'display':'inline-block' });
                            } else if ($(this).closest('.name').height() < 20) {
                                $(this).closest('.name').css('line-height', '28px');
                            }
                        } else {
                            $(this).closest('.name').css('width','auto');
                            if ($(this).closest('td').width() && $(this).closest('.name').width() > $(this).closest('td').width()-20) {
                                $(this).closest('.name').css({'white-space':'normal', 'line-height':'16px'});
                                if ($(this).closest('.name').length){
                                    $(this).css('float','left');
                                };
                                $(this).closest('td').css('overflow-x',' hidden');
                            }
                            $(this).closest('.name').css('width','100%');
                        }
                    });
                }
            } else {
                $('.sm_extension[data-type="submodule"] .crm-table-wrapper .list-view-avatar').each(function(){ //+.submodule_edit_view_dnt-edit//.addClass('sub_ava_cell');
                    var $text,
                        $this = $(this),
                        $parent = $this.parent(),
                        $td = $(this).closest('td'),
                        $next = $this.next();

                    $td.css('min-width','140px');

                    if ($parent.is('.text')) {
                        $parent.before($this);
                    }

                    if ($this.closest('a').is('[data-controller="sdm"]') || $this.closest('.element_data[data-ug_type="user"]').length) {
                        if (!$this.parent().is('.parent-avatar')) $this.wrap('<div class="parent-avatar"></div>');
                    }

                    if ($next.is('.navigation_module_link_child_from_submodule')) {
                        $next.find('*').first().before($this.wrap('<div class="parent-avatar"></div>').parent());
                    }

                    $next = $this.next();

                    if ($next.hasClass('submodule_edit_view_dnt-edit') || $next.hasClass('navigation_module_link_child_from_submodule')){
                        $next.css('float','right').width($(this).closest('td').width()-$(this).width()-6);
                    }

                    if ($next.height()<20) {
                        $next.css('margin-top','7px');
                    } else if ($next.height()>19) {
                        $next.css('margin-top','0');
                    }
                });
                $('.sm_extension[data-type="submodule"] .crm-table-wrapper table tr:nth-child(2) .list-view-avatar').each(function(){
                    var tdNumb = $(this).closest('td').index()+1;
                    var widthReMake = $(this).next().width();
                    $(this).closest('table').find('tr:first-child td:nth-child('+tdNumb+') .list-view-avatar').next().width(widthReMake);
                });
            }

            var $list = $('[data-type="submodule"] .text');
            $.each($list, function (key, value) {
                if ($(value).text().indexOf('_')>0) {
                    $(value).addClass('one-line');
                }
            });

            $('.sm_extension[data-type="submodule"] .sm_extension_data .name .file').each(function(){
                $(this).closest('td').addClass('sub_file_cell');
                if ($(this).next().height()>19) {
                    $(this).next().css('margin-top','-4px');
                    $(this).closest('a').css('margin-top','5px');
                }
            });
        },

        SMCrmTable : function ($list) {
            $.each($list, function(){ //+.submodule_edit_view_dnt-edit//.addClass('sub_ava_cell');
                var $this = $(this),
                    $td = $(this).closest('td'),
                    $next = $this.next();

                $td.css('min-width','140px');

                if ($this.parent().is('.text')) {
                    $this.parent().before($this);
                }

                if ($this.closest('a').is('[data-controller="sdm"]')) {
                    $this.wrap('<div class="parent-avatar"></div>');
                }

                if ($next.is('.navigation_module_link_child_from_submodule')) {
                    $next.find('*').first().before($this.wrap('<div class="parent-avatar"></div>').parent());
                }

                if ($this.next().hasClass('submodule_edit_view_dnt-edit') || $this.next().hasClass('navigation_module_link_child_from_submodule')){
                    $this.next().css('float','right').width($this.closest('td').width()-$this.width()-6);
                } else {
                    if (!$this.parent().hasClass('element_data')) {
                        if ($this.closest('td').width()>80){
                            if (!$this.parent().is('.parent-avatar')) $this.parent().addClass('pull-right').width($this.closest('td').width()-40);
                        } else {
                        }
                    }
                }

                if ($(this).next().height()<20) {
                    $(this).next().css('margin-top','7px');
                } else if ($(this).next().height()>19) {
                    $(this).next().css('margin-top','0');
                }

                if ($this.parent().is('[data-name="b_responsible"]')) {
                    var _height = $next.height();
                    $td.height(_height);
                    if ($(this).next().height()<20) {
                        $(this).next().css('margin-top','0');
                    }
                }
            });
            $('.sm_extension[data-type="submodule"] .crm-table-wrapper table tr:nth-child(2) .list-view-avatar').each(function(){
                var tdNumb = $(this).closest('td').index()+1;
                var widthReMake = $(this).next().width();
                $(this).closest('table').find('tr:first-child td:nth-child('+tdNumb+') .list-view-avatar').next().width(widthReMake);
            });

            $('.sm_extension[data-type="submodule"] .sm_extension_data .name .file').each(function(){
                $(this).closest('td').addClass('sub_file_cell');
                if ($(this).next().height()>19) {
                    $(this).next().css('margin-top','-4px');
                    $(this).closest('a').css('margin-top','5px');
                }
            });
        },

        hiddenBlocks : function() {
            $('.modal:visible .edit-view .panel-body:hidden').prev().css('padding-bottom','5px');
            $('.modal .edit-view .buttons-block').each(function(){
                if($(this).closest('.panel').next().find('.contacts-block').length>0 || $(this).closest('.panel').next().find('.participants-block').length>0){} else {
                    $(this).closest('.panel').css('padding-bottom','35px');
                }
            });
            $('.edit-view .fa-chevron-up').each(function(){
                $(this).closest('.panel[data-type="block"]').find('.panel-body').hide();
            });
            Participant.addResponsibleIfNotExist();
        },

        scrollToError : function() {
            $('.errorMessage').each(function(ind, ele){
                if ($(this).text() !== '') {
                    if ($(this).is(':hidden')) {
                        $(this).closest('.panel-body').show();
                        $(this).closest('.panel').find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                    $('.modal').animate({
                        scrollTop: Math.abs($('.edit-view').offset().top-$(this).offset().top)
                    }, 500);
                    return false;
                }
            });
        },


        countEditView : function(){
            return $('.edit-view').length;
        },



        refreshBlockActivity : function(_edit_view_object){
            if(EditView.activityMessages.issetBlockActivity(_edit_view_object)){
                if(EditView.activityMessages.refresh_messages){
                    EditView.activityMessages.refreshMessages($(_edit_view_object).find('.element[data-type="block_activity"]'));
                }
                EditView.relates.reloadSDMChannel($(_edit_view_object).find('.element[data-type="drop_down"] .element[data-type="drop_down_button"][data-reloader="activity_channel"]'));
            }
        },



        /********************************
         *       relateDataStory
         *********************************/
        relateDataStory : {
            list_relate : {},

            getPrimaryEtitiesFromEditView : function(_this, return_this_copy_id){
                var result = {'primary_pci' : null, 'primary_pdi' : null};

                if(!_this) return result;
                var parent = $(_this).closest('.edit-view').find('.element_relate[data-reloader="parent"]');
                if(parent.length == 0){
                    if(return_this_copy_id == true){ // только для первой дочерней формы editView
                        result['primary_pci'] = $(_this).closest('.edit-view').data('copy_id');
                        result['primary_pdi'] = $(_this).closest('.edit-view').data('id');
                    }
                    return result;

                } else {
                    result['primary_pci'] = parent.data('relate_copy_id');
                    result['primary_pdi'] = parent.data('id');
                }

                return result;
            },

            findRelateDataList : function(_this){
                var result = {};
                $(_this).closest('.edit-view').find('.element_relate').each(function(i, ul){
                    realte_result = $(ul).data('id');
                    if(!realte_result) realte_result = null;
                    result[$(ul).data('relate_copy_id')] = realte_result;
                });
                return result;
            },

            findRelateDataListFromInLine : function(_this){
                var result = {};
                $(_this).closest('.sm_extension_data.editing').find('.element_relate').each(function(i, ul){
                    realte_result = $(ul).data('id');
                    if(!realte_result) realte_result = null;
                    result[$(ul).data('relate_copy_id')] = realte_result;
                });
                return result;
            },

            findSubModuleDataListPrimaryModule : function(_this, return_this_copy_id){
                var primary = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, return_this_copy_id);

                if(primary.primary_pci == false){
                    return EditView.relateDataStory.findSubModuleDataList(_this);
                }

                parent_edit_view = $('.edit-view[data-copy_id="'+primary.primary_pci+'"] div');
                return EditView.relateDataStory.findSubModuleDataList(parent_edit_view);
            },

            findSubModuleDataList : function(_this){
                var result = {};
                $(_this).closest('.edit-view').find('.sm_extension[data-type="submodule"]').each(function(i, ul){
                    var sub_result = [];
                    $(ul).find('table.crm-table tbody .sm_extension_data').each(function(i, ul){
                        sub_result.push($(ul).data('id'));
                    });
                    if($.isEmptyObject(sub_result)) sub_result = null;
                    result[$(ul).data('relate_copy_id')] = sub_result;
                });

                return result;
            },

            setAll : function(_this){
                EditView.relateDataStory.list_relate = EditView.relateDataStory.findRelateDataList(_this);
            },

            clearAll : function(_this){
                EditView.relateDataStory.list_relate = {};
                EditView.relateDataStory.list_primary = {};
            },

            getRelateDataList : function(){
                var list = EditView.relateDataStory.list_relate;
                return list;
            },
        },






        /**********************************
         *         relates
         **********************************/
        relates : {

            reloadSDM : function(_this){
                $(_this).each(function(i,ul){
                    var data = {};

                    data['copy_id'] = $(ul).closest('.edit-view').data('copy_id');
                    if(typeof(data['copy_id']) == 'undefined' || !data['copy_id']) return;

                    data['id'] = $(ul).closest('.edit-view').data('id');
                    data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(ul, true);
                    data['this_template'] = $(ul).closest('.edit-view').data('this_template');
                    data['pci'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id') || '';
                    data['pdi'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id') || '';
                    data['relate_element'] = {
                        'copy_id' : $(ul).data('relate_copy_id'),
                        'data_id' : $(ul).data('id'),
                        'sub_type' : $(ul).data('sub_type')
                    }

                    $.ajax({
                        'url' : Global.urls.url_edit_view_relate_reload_sdm +'/'+data['copy_id'],
                        'data' : data,
                        'dataType' : "json",
                        'type' : "POST", async : false,
                        'success' : function(data_r) {
                            if(data_r.status == false){
                                return;
                            } else if(data_r.status == true){
                                var disabled = $(ul).closest('.element[data-type="drop_down"]').find('.element[data-type="drop_down_button"]').attr('disabled');
                                var html = $(data_r.html);
                                html.find('.element[data-type="drop_down_button"]').attr('disabled', disabled);
                                $(ul).closest('.column').html(html);
                                Global.createLinkByEV(html.closest('.edit-view.in'));
                            }
                        },
                        'error' : function(){
                            Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                        },
                    });
                });
            },


            reloadSDMChannel : function(_drop_down_button, _data){
                if(!_drop_down_button){
                    return;
                }

                var edit_view = $(_drop_down_button).closest('.edit-view');
                var data = {
                    'copy_id' : edit_view.data('copy_id'),
                    'id' : edit_view.data('id'),
                    'data_id' : $(_drop_down_button).data('id')
                };

                if(_data){
                    for(key in _data){
                        data[key] = _data[key];
                    }
                }

                $.ajax({
                    'url' : Global.urls.url_edit_view_relate_reload_sdm_channel +'/'+data['copy_id'],
                    'data' : data,
                    'dataType' : "json",
                    'type' : "POST",
                    'async' : true,
                    'success' : function(data_r) {
                        if(data_r.status == false){
                            return;
                        } else if(data_r.status == true){
                            $(_drop_down_button).closest('.element[data-type="drop_down"]').replaceWith(data_r.html);
                        }
                    },
                    'error' : function(){
                        Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                    },
                });

            },


            reloadEditView : function(_this, base_id, callback){
                var relate = $(_this).closest('.column').find('.element_relate');
                if(relate.data('reloader') != 'parent') return;

                var data = {};

                if(_this){
                    data['relate_get_value'] = 1;
                    data['relate_check_about_parent'] = 1;
                    data['copy_id'] = $(_this).closest('.edit-view').data('copy_id');
                    data['id'] = $(_this).closest('.edit-view').data('id');

                    var relate_id = relate.data('id');
                    if(typeof(base_id) != 'undefined' && base_id && base_id != relate_id){
                        data['relate_get_value'] = 0;
                    }


                    data['primary_entities'] = {
                        'primary_pci' : relate.data('relate_copy_id'),
                        'primary_pdi' : relate_id,
                    };

                    data['parent_relate_data_list'] = EditView.relateDataStory.findRelateDataList(_this);
                    data['this_template'] = $(_this).closest('.edit-view').data('this_template');
                    //data['from_template'] = 0;
                    data['pci'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id');
                    data['pdi'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id');
                }

                $.ajax({
                    'url' : Global.urls.url_edit_view_relate_reload +'/'+data['copy_id'],
                    'data' : data,
                    'dataType' : "json",
                    'type' : "POST", async : true,
                    'success' : function(data) {
                        if(data.status == false){
                            Message.show(data.messages);
                        } else if(data.status == true){
                            if(data.data_list){
                                TodoList.clear(_this);
                                $.each(data.data_list, function(relate_copy_id, html){
                                    $(_this).closest('.edit-view').find('.element_relate[data-relate_copy_id="'+relate_copy_id+'"][data-reloader="children"]').closest('.column').html(html);
                                });
                            }
                            if(typeof(callback) == 'function' && callback){
                                callback();
                            }
                        }
                    },
                    'error' : function(){
                        Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                    },
                });

            },

            reloadInLine : function(_this, data, base_id, only_parent, callback, relate_check_about_parent){
                if(_this && !data){
                    var relate = $(_this).closest('.data_edit');
                    var reloader = relate.find('.element_relate').data('reloader');
                    if(only_parent == true){
                        if(reloader != 'parent') return;
                    } else {
                        if(reloader != 'parent' && reloader != 'one_to_one') return;
                    }

                    var data = {};

                    data['relate_get_value'] = 1;
                    data['relate_check_about_parent'] = relate_check_about_parent || 0;
                    data['copy_id'] = $(_this).closest('.sm_extension').data('copy_id');
                    data['id'] = $(_this).closest('.sm_extension_data.editing').data('id');

                    var relate_id = $(_this).closest('.sm_extension_data.editing').find('.data_edit  .element_relate').data('id');
                    if(typeof(base_id) != 'undefined' && base_id && base_id != relate_id)
                        data['relate_get_value'] = 0;

                    data['primary_entities'] = {
                        'primary_pci' : $(_this).closest('.sm_extension_data.editing').find('.data_edit .element_relate').data('relate_copy_id'),
                        'primary_pdi' : relate_id,
                    };

                    data['parent_relate_data_list'] = EditView.relateDataStory.findRelateDataListFromInLine(_this);
                    data['this_template'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('this_template');
                    //data['from_template'] = 0;
                    data['pci'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_copy_id');
                    data['pdi'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension, .bpm_block.sm_extension').data('parent_data_id');
                };

                if (inLineEdit.attributes["todo_list"] && $('tr.editing').data('open') == true) {
                    var text = inLineEdit.attributes["todo_list_text"];

                    $('#todo_list').next().find('.filter-option').text(text);
                }

                $.ajax({
                    'url' : Global.urls.url_edit_view_relate_reload +'/'+data['copy_id'],
                    'data' : data,
                    'dataType' : "json",
                    'type' : "POST", async : true,
                    'success' : function(data) {
                        if (data.status==false ) {
                        }
                        if(data.status == false && data.messages){
                            Message.show(data.messages);
                        } else
                        if(data.status == true){
                            if(data.data_list){
                                $.each(data.data_list, function(relate_copy_id, html){
                                    if(only_parent == true)
                                        $(_this).closest('.sm_extension_data.editing').find('.element_relate[data-relate_copy_id="'+relate_copy_id+'"][data-reloader="children"]').closest('div').html($(html).html());
                                    else
                                        $(_this).closest('.sm_extension_data.editing').find('.element_relate[data-relate_copy_id="'+relate_copy_id+'"]').closest('div').html($(html).html());
                                });


                                var el = $(_this).closest('.sm_extension_data.editing').find('td.data_edit .element_relate[data-module_parent="1"]');

                                if(el.length){
                                    TodoList.rebuild(el, function (data) {
                                        TodoList.setValue(inLineEdit.attributes.todo_list);
                                    }, callback);
                                } else {
                                    TodoList.clear(_this);
                                    callback ? callback() : null;
                                }
                            }
                        }
                    },
                    'error' : function(){
                        Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                    },
                });

            },


            cardCreate : function(_this, callback){
                var copy_id = $(_this).closest('.element[data-type="drop_down"]').find('.element[data-type="drop_down_button"]').data('relate_copy_id')

                //EditView.relateDataStory.setAll(_this);

                if(!_data) var _data = {};
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, true);
                _data['parent_copy_id'] = $(_this).closest('.edit-view').data('copy_id');
                _data['parent_data_id'] = $(_this).closest('.edit-view').data('id');
                _data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                _data['this_template'] = $(_this).closest('.edit-view').data('this_template');
                _data['relate_template'] = '0';
                _data['parent_object'] = 'sdm';
                Preloader.modalShow();
                Preloader.modalSub();
                $.ajax({
                    url: Global.urls.url_edit_view_edit + '/' + copy_id,
                    data : _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'data'){
                                modalDialog.show(data.data, true);
                                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+copy_id+'"]'));
                                var $modal = $(modalDialog.getModalName()).find('.client-name');
                                if ( $modal.find('span').first().text() == "" ) {

                                    $modal.find('.edit-dropdown').first().addClass('open');
                                }
                                $('.form-control.time').each(function(){
                                    initElements('.edit-view', $(_this).val());
                                });
                            }
                        }
                        Preloader.modalHide();
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                        Preloader.modalHide();
                    },
                }).done(function(){
                    EditView.activityMessages.init();
                    textAreaResize();
                    EditView.hiddenBlocks();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                });


            },


            /*
        reloadActivityMessages: function (copy_id,data_id,module_title,relate_copy_id,relate_data_id,_this){
            EditView.activityMessages
                .setUrl(Global.urls.url_edit_view_activity_get_messages_block)
                .setData({
                    'copy_id': copy_id,
                    'data_id': data_id,
                    'module_title': module_title,
                    'relate_copy_id': relate_copy_id,
                    'relate_data_id': relate_data_id
                })
                .setElement(_this)
                .setHandler(function (data) {
                    if(data['result_html']){
                        $('.comments_block.element[data-type="block_message"]').replaceWith(data['result_html']);
                    }
                })
                .ajax();
        }
        */
        },




        /**********************************
         *         subModules
         **********************************/
        subModules : {
            //EditView.relates.reloadActivityMessages(copy_id,data_id,module_title,Communication._copy_id, $this.closest('.sm_extension_data').data('id'),$this);
            evInstance: null, //Instance EditView
            addNewProcesses : function(copy_id, data_id, callback){
                var data = {
                    'copy_id' : copy_id,
                    'data_id' : data_id,
                };

                var ajax = new Ajax();
                ajax
                    .setData(data)
                    .setAsync(false)
                    .setUrl('/module/listView/addNewProcessesSubModule/' + copy_id)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status){
                                callback(data);
                            } else {
                                Message.show(data.messages, false);
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();


            },
            saveInstanceEV: function(evInstance) {
                this.evInstace = evInstance;
                return this;
            },

            addCardFromTemplate : function(_this, _edit_view) {
                var auto_new_card, id,
                    $this = $(_this),
                    _data = {},
                    $smExtension = $this.closest('.sm_extension'),
                    project_select = $smExtension.find('.element[data-type="project_select"]').val(),
                    project_name = $smExtension.find('.element[data-type="project_name"]').val();

                _data['module_title'] = project_name;

                auto_new_card = $smExtension.data("auto_new_card");

                if(auto_new_card) {
                    $('.edit-view:visible').data('auto_new_card', auto_new_card);
                    $smExtension.data("auto_new_card", '')
                }

                switch (project_select) {
                    case 'from_template': {
                        id = $smExtension.find('.element[data-type="template"]').val();
                        _data['template_data_id'] = _data['id'] = $smExtension.find('.element[data-type="template"]').val();
                        _data['from_template'] = 1;
                        _data['auto_new_card'] = auto_new_card;
                        EditView.subModules.cardEditSM(_edit_view, _data);
                        modalDialog.hide();
                        break;
                    }
                    case 'from_process_template': {
                        ProcessObj.createFromTemplate(_this, $smExtension.find('.element[data-type="template"]').val(), function(data){
                            var edit_view_object = $('.edit-view[data-copy_id="'+$smExtension.data('parent_copy_id')+'"]');
                            EditView.subModules.updateSubModuleDataList(null, edit_view_object, function(){
                                EditView.refreshBlockActivity(edit_view_object);
                            });

                            imagePreview();
                            EditView.textRedLine();
                            EditView.textRedLine();
                            jScrollRemove();
                            jScrollInit();
                            modalDialog.hide();
                        });
                        break;
                    }
                    case 'new_card': {
                        var block_field_name = $smExtension.find('.element[data-type="block_field_name"]').val();

                        if(block_field_name) {
                            var obj = {},
                                block_unique_index = $smExtension.find('.element[data-type="block"]').val();

                            obj[block_field_name] = block_unique_index;
                            _data['default_data'] = obj;
                        }

                        Preloader
                            .createInstance()
                            .setModal(true)
                            .setModalSub(true)

                        EditView.subModules.cardCreate(_edit_view, _data);
                        modalDialog.hide();
                        break;
                    }
                    default: {
                        break;
                    }
                }
            },

            //cardCreate
            cardCreate : function(element, _data){
                var $element = $(element),
                    instance = EditView.createInstance(),
                    copy_id = $element.closest('.sm_extension').data('relate_copy_id');

                EditView.relateDataStory.setAll(element);

                if(!_data) var _data = {};

                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(element, true);
                _data['parent_copy_id'] = $(element).closest('.edit-view').data('copy_id');
                _data['parent_data_id'] = $(element).closest('.edit-view').data('id');
                _data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                _data['this_template'] = $(element).closest('.edit-view').data('this_template');
                _data['relate_template'] = $(element).closest('.sm_extension[data-type="submodule"]').data('relate_template');
                _data['parent_object'] = 'sub_module';

                if(!_data['parent_data_id']){
                    EditView.save(_data['parent_copy_id'], {}, function(data){
                        var edit_view = $element.closest('.edit-view');
                        edit_view.data('id', data.id);

                        EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                            var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+copy_id+'"] .submodule_edit_view_dnt-create');
                            if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                            EditView.subModules.cardCreate(sub_module);
                        });
                    });

                    return;
                }


                if($(element).closest('.edit-view').data('default_data')) {
                    var obj = {};
                    fields = JSON.parse($(element).closest('.edit-view').data('default_data'));

                    for(field in fields) {
                        if(field=='block_unique_index') {
                            _data['block_unique_index'] = fields[field];
                        }else {
                            obj[field] = fields[field];
                        }
                    }

                    _data['default_data'] = obj;
                    $(element).closest('.edit-view').data('default_data', '');
                }
                Preloader.getInstance().modalRun();
                $.ajax({
                    url: Global.urls.url_edit_view_edit+'/'+copy_id,
                    data : _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        if(data.status == 'error' || data.status == 'access_error'){
                            Preloader.modalAnSub();
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'data'){
                                var modelEditView = EditView.createModel(data);

                                modalDialog.show(data.data, true);
                                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+copy_id+'"]'));

                                modelEditView
                                    .setView()
                                    .drawReadOnlyFields();

                                Global.addOperationInSDM();

                                var $modal = $(modalDialog.getModalName()).find('.client-name');
                                if ( $modal.find('span').first().text() == "" ) {

                                    $modal.find('.edit-dropdown').first().addClass('open');
                                }
                                $('.form-control.time').each(function(){
                                    initElements('.edit-view', $(this).val());
                                });
                            }
                        }
                        Preloader.modalHide();
                    },
                    error: function(){
                        Preloader.modalAnSub();
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                        Preloader.modalHide();
                    },
                }).done(function(){
                    EditView.activityMessages.init();
                    textAreaResize();
                    EditView.hiddenBlocks();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                });
            },

            //addCardSelect
            addCardSelect : function(_this, parent_class){
                var $this = $(_this),
                    edit_view = $this.closest('.edit-view'),
                    sm_extension = $this.closest('.sm_extension');
                    //copy_id = sm_extension.data('relate_copy_id');

                var _data = {
                    'primary_entities' : EditView.relateDataStory.getPrimaryEtitiesFromEditView(edit_view, true),
                    'id' : edit_view.data('id'),
                    'parent_copy_id' : edit_view.data('copy_id'),
                    'parent_data_id' : edit_view.data('id'),
                    'this_template' : edit_view.data('this_template'),
                    'auto_new_card' : edit_view.data('auto_new_card'),
                    'parent_class' : parent_class,
                    'parent_relate_data_list' : EditView.relateDataStory.findRelateDataList(_this),
                    finished_object : 0
                };

                if(!edit_view.data('id')){
                    EditView.save(edit_view.data('copy_id'), {}, function(data){
                        edit_view.data('id', data.id);

                        EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                            var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+copy_id+'"]');
                            if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                            EditView.subModules.addCardSelect(sub_module);
                        });
                    });

                    return;
                }

                edit_view.data('auto_new_card', '');

                $.ajax({
                    url: Global.urls.url_edit_view_edit_select + '/' + sm_extension.data('relate_copy_id'),
                    data: _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data) {
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                if(data.status == true){

                                    var noMiddle = true;
                                    if($(data.data).find('.edit-view.sm_extension.no_middle').length){
                                        noMiddle = false;
                                    }

                                    modalDialog.show(data.data, noMiddle);

                                    jScrollInit();
                                    niceScrollCreate($('.submodule-table'));
                                    imagePreview();
                                    $('.form-control.time').each(function(){
                                        initElements('.edit-view', $(this).val());
                                    });
                                }
                            }
                        }
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message':  Global.urls.url_ajax_error }], true);
                    },
                });
            },




            cardEditSM : function(_this, _data){
                var copy_id = $(_this).closest('.sm_extension').data('relate_copy_id')

                EditView.relateDataStory.setAll(_this);

                if(!_data) var _data = {};
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, true);
                _data['parent_copy_id'] = $(_this).closest('.edit-view').data('copy_id');
                _data['parent_data_id'] = $(_this).closest('.edit-view').data('id');
                _data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                _data['this_template'] = $(_this).closest('.edit-view').data('this_template');
                _data['relate_template'] = $(_this).closest('.sm_extension[data-type="submodule"]').data('relate_template');
                _data['id'] = (_data['id'] ? _data['id'] : $(_this).closest('.sm_extension_data').data('id'));
                _data['parent_object'] = 'sub_module';

                if(!_data['parent_data_id']){
                    EditView.save(_data['parent_copy_id'], {}, function(data){
                        var edit_view = $(_this).closest('.edit-view');
                        edit_view.data('id', data.id);

                        EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                            var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+copy_id+'"] .submodule_edit_view_dnt-create');
                            if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                            EditView.subModules.cardEditSM(sub_module);
                        });
                    });

                    return;
                }

                Preloader.modalShow();
                Preloader.modalSub();
                $.ajax({
                    url: Global.urls.url_edit_view_edit + '/' + copy_id,
                    data : _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'data'){
                                var modelEditView = EditView.createModel(data);

                                modalDialog.show(data.data, true);

                                modelEditView
                                    .setView()
                                    .drawReadOnlyFields();

                                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+copy_id+'"]'));

                                Global.addOperationInSDM();

                                var $modal = $(modalDialog.getModalName()).find('.client-name');
                                if ( $modal.find('span').first().text() == "" ) {

                                    $modal.find('.edit-dropdown').first().addClass('open');
                                }
                                $('.form-control.time').each(function(){
                                    initElements('.edit-view', $(_this).val());
                                });
                                Global.createLinkByEV($('.edit-view.in:last'));

                               var subModule = $('#modal_dialog2 .edit-view');
                               var id = subModule.attr('data-id');
                               var copy_id = subModule.attr('data-copy_id');
                               var subModule_key = Global.createUniqueKey(copy_id, id)
                             
                                LocalStorageObject.writeStorage('draft', JSON.stringify(
                                    subModule_key
                                ));
                            }
                        }
                    },
                    error: function(){
                        Preloader.modalAnSub();
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                        Preloader.modalHide();
                    },
                }).done(function(){
                    EditView.activityMessages.init();
                    textAreaResize();
                    EditView.hiddenBlocks();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                    Preloader.modalHide();
                });


            },


            cardEditSDM : function(_this, _data){
                var copy_id = $(_this).closest('.element[data-type="drop_down"]').find('.element[data-type="drop_down_button"]').data('relate_copy_id')

                EditView.relateDataStory.setAll(_this);

                if(!_data) var _data = {};
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, true);
                _data['parent_copy_id'] = $(_this).closest('.edit-view').data('copy_id');
                _data['parent_data_id'] = $(_this).closest('.edit-view').data('id');
                _data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                _data['this_template'] = $(_this).closest('.edit-view').data('this_template');
                _data['relate_template'] = '0';

                _data['id'] = (_data['id'] ? _data['id'] : $(_this).closest('.element[data-type="drop_down"]').find('.element[data-type="drop_down_button"]').data('id'));
                _data['parent_object'] = 'sdm';
                Preloader.modalShow();
                Preloader.modalSub();

                $.ajax({
                    url: Global.urls.url_edit_view_edit + '/' + copy_id,
                    data : _data,
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'data'){

                                modalDialog
                                    .createInstance()
                                    .show(data.data, true);

                                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="'+copy_id+'"]'));

                                Global.addOperationInSDM();

                                var $modal = $(modalDialog.getModalName()).find('.client-name');
                                if ( $modal.find('span').first().text() == "" ) {

                                    $modal.find('.edit-dropdown').first().addClass('open');
                                }
                                $('.form-control.time').each(function(){
                                    initElements('.edit-view', $(_this).val());
                                });
                            }
                        }
                        Preloader.modalHide();
                    },
                    error: function(){
                        var $dialog;

                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                        Preloader.modalHide();

                        $dialog = $('.modal');
                        if ($dialog.length > 1) {
                            $dialog.first().show();
                        }
                    },
                }).done(function(){
                    EditView.activityMessages.init();
                    textAreaResize();
                    EditView.hiddenBlocks();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                });


            },


            cardSelect : function(_this){
                var id_added = [];

                $(_this).closest('.sm_extension').find('table tbody tr.new').each(function(i, ul){
                    id_added.push($(ul).data('id'));
                });

                EditView.relateDataStory.setAll(_this);

                var copy_id = $(_this).closest('.sm_extension').data('relate_copy_id');
                var parent_copy_id = $(_this).closest('.edit-view').data('copy_id');
                var parent_data_id = $(_this).closest('.edit-view').data('id');

                if(!parent_data_id){
                    EditView.save(parent_copy_id, {}, function(data){
                        var edit_view = $(_this).closest('.edit-view');
                        edit_view.data('id', data.id);

                        EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                            var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+copy_id+'"] .submodule_edit_view_dnt-create');
                            if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                            EditView.subModules.cardSelect(sub_module);
                        });
                    });

                    return;
                }


                $.ajax({
                    url: Global.urls.url_list_view_add_cards_sub_module + '/' + copy_id,
                    data: {
                        'primary_entities' : EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, false),
                        'parent_copy_id' : parent_copy_id,
                        'parent_data_id' : parent_data_id,
                        'parent_relate_data_list' : EditView.relateDataStory.getRelateDataList(),
                        'this_template' : $(_this).closest('.edit-view').data('this_template'),
                        'relate_template' : $(_this).closest('.sm_extension[data-type="submodule"]').data('relate_template'),
                        'id_added' : id_added,
                    },
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                        } else if(data.status == 'data'){
                            modalDialog.show(data.html);
                            niceScrollCreate($('.submodule-table'));
                            TableSearchInit('.submodule-table', '.submodule-search');
                        }
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                    },
                });

            },

            cardCopy : function(_this){
                var input = $(_this).closest('.panel-body').find('table input.checkbox:checked');
                var params = [];
                var edit_view = $(_this).closest('.edit-view');

                if(input.length == 0){
                    Message.show([{'type':'error', 'message': 'It should be noted entries'}], true);
                    return false;
                }

                //if(!confirm(Message.translate_local('Copy selected entries') + '?')) return;

                $(input).each(function(i, ul){
                    params.push($(ul).closest('tr').data('id'));
                });


                var copy_id = $(_this).closest('.sm_extension').data('relate_copy_id');
                var parent_copy_id = $(_this).closest('.edit-view').data('copy_id');
                var parent_data_id = $(_this).closest('.edit-view').data('id');

                if(!parent_data_id){
                    EditView.save(parent_copy_id, {}, function(data){
                        edit_view.data('id', data.id);

                        EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                            var sub_module = $(edit_view).find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+copy_id+'"] .submodule_edit_view_dnt-create');
                            if(modalDialog.modal_dialog_shown.length == 1) EditView.saved_first_ev = true;
                            EditView.subModules.cardCopy(sub_module);
                        });
                    });

                    return;
                }


                $.post(
                    Global.urls.url_list_view_copy_for_sub_module + '/' + copy_id,
                    {
                        'id': params,
                        'primary_entities' : EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, false),
                        'parent_copy_id' : edit_view.data('copy_id'),
                        'parent_data_id' : edit_view.data('id'),
                        'this_template' : edit_view.data('this_template'),
                    },
                    function(data){
                        if(data.status == true){
                            $.ajax({
                                url : Global.urls.url_list_view_card_list_for_sub_module + '/' + $(_this).closest('.sm_extension').data('relate_copy_id'),
                                data : {
                                    'parent_copy_id' : edit_view.data('copy_id'),
                                    'parent_data_id' : edit_view.data('id'),
                                    'this_template' : edit_view.data('this_template'),
                                },
                                type : 'POST', async: false, dataType: "json",
                                success: function(data2){
                                    if(data2.status == false)
                                        Message.show(data.messages, false);
                                    else {
                                        var parent_mudule = $(_this).closest('.sm_extension');
                                        parent_mudule.after(data2.data);
                                        parent_mudule.remove();
                                    }
                                },
                                error : function(){
                                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                                }
                            });
                            EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                                EditView.refreshBlockActivity(edit_view);
                            });
                        } else {
                            Message.show(data.messages, false);
                        }
                    }, 'json').done(function(){
                    EditView.textRedLine();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                });

            },

            cardRemoveTr : function(input, edit_view){
                input.closest('tr').remove();
                EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                    EditView.refreshBlockActivity(edit_view);
                });
                EditView.textRedLine();
                jScrollRemove();
                jScrollInit();
                EditView.textRedLine();
            },

            cardRemoved : function(_this, show_confirm){
                var input = $(_this).closest('.panel-body').find('table tbody input.checkbox:checked');
                var edit_view = $(_this).closest('.edit-view');

                if(edit_view.data('template_data_id') !== ''){
                    EditView.subModules.cardRemoveTr(input, edit_view);
                    return;
                }

                if(input.length == 0){
                    Message.show([{'type':'error', 'message': 'It should be noted entries'}], true);
                    return false;
                }
                if(show_confirm !== false)

                    var _function = function(){
                        id = [];
                        $.each(input, function(i, ul){
                            id.push($(ul).closest('tr').data('id'));
                        })

                        EditView.relateDataStory.setAll(_this);
                        var copy_id = $(_this).closest('.sm_extension').data('relate_copy_id');


                        //удаление
                        $.ajax({
                            url: Global.urls.url_list_view_delete_from_sub_module + '/' + copy_id,
                            data: {
                                'id': id,
                                'parent_copy_id': edit_view.data('copy_id'),
                                'parent_data_id': edit_view.data('id'),
                                'primary_entities': EditView.relateDataStory.getPrimaryEtitiesFromEditView(_this, true),
                                'this_template': edit_view.data('this_template'),
                            },
                            type: 'POST', async: false, dataType: "json",
                            success: function(data){
                                if(data.status == 'error'){
                                    Message.show(data.messages, false);
                                } else if(data.status == true){
                                    if(data.ev_refresh_field){
                                        fields = JSON.parse(data.ev_refresh_field);
                                        for(id in fields){
                                            $('#' + id).val(fields[id]).change();
                                        }
                                    }
                                    EditView.subModules.cardRemoveTr(input, edit_view);
                                }
                            },
                            error: function(){
                                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                            }
                        });
                    }

                if(show_confirm !== false){
                    Message.show([{
                        'type': 'confirm',
                        'message': Message.translate_local('Delete selected entries') + '?'
                    }], false, function(_this_c){
                        if($(_this_c).hasClass('yes-button')){
                            modalDialog.hide();
                            _function();
                        }
                    }, Message.TYPE_DIALOG_CONFIRM);
                }
            },

            cardTie : function(_this){
                var input = $(modalDialog.getModalName() + ' table input.checkbox:checked');
                var _select_list = [];

                if(input.length == 0){
                    Message.show([{'type':'error', 'message': 'It should be noted entries'}], true);
                    return false;
                }

                input.each(function(){
                    _select_list.push($(this).closest('.sm_extension_data').data('id'));
                });

                var copy_id = $(_this).closest('.sm_extension_relate_submodule').data('copy_id');
                var parent_copy_id = $(_this).closest('.sm_extension_relate_submodule').data('parent_copy_id');
                var parent_data_id = $(_this).closest('.sm_extension_relate_submodule').data('parent_data_id');
                var edit_view = $('.edit-view[data-copy_id="'+parent_copy_id+'"]');

                $.ajax({
                    url : Global.urls.url_list_view_insert_card_in_sub_module + '/' + copy_id,
                    data : {
                        'parent_copy_id' : parent_copy_id,
                        'parent_data_id' : parent_data_id,
                        'select_list' : _select_list,
                        'this_template' : $(_this).closest('.sm_extension_relate_submodule').data('this_template'),
                        'relate_template' : $(_this).closest('.sm_extension_relate_submodule').data('relate_template'),
                        'primary_entities' : EditView.relateDataStory.getPrimaryEtitiesFromEditView(edit_view.find('div'), true),
                    },
                    type : 'POST', async: false, dataType: "json",
                    success: function(data){
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                        } else if(data.status == true){
                            modalDialog.hide();
                            EditView.subModules.updateSubModuleDataList(null, edit_view, function(){
                                EditView.refreshBlockActivity(edit_view);
                            });
                            imagePreview();
                            EditView.textRedLine();
                            EditView.textRedLine();
                            jScrollRemove();
                            jScrollInit();
                            if(data.ev_refresh_field) {
                                fields = JSON.parse(data.ev_refresh_field);
                                for (id in fields) {
                                    $('#'+id).val(fields[id]).change();
                                }
                            }
                        }
                    },
                    error : function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                    }
                });
            },


            updateSubModuleDataList : function(_parent_object, edit_view, callback){
                var copy_id = $(edit_view).data('copy_id'),
                    id = $(edit_view).data('id');

                if(_parent_object !== null){
                    var parent_edit_view = $(_parent_object).find('.edit-view');
                    if(typeof(parent_edit_view) == 'undifined' || !parent_edit_view) return;
                }

                if(copy_id == false) return;

                $.ajax({
                    url: Global.urls.url_list_view_update_card_list_sub_modules + '/' + copy_id,
                    data : {
                        'id' : id,
                        'this_template' : edit_view.data('this_template'),
                    },
                    dataType: "json", type: "POST", async : false, timeout : 10000,
                    success: function(data){
                        if(data.status == true){
                            if(!$.isEmptyObject(data.data)){
                                $.each(data.data, function(relate_copy_id, html){
                                    // данные субмодуля
                                    $(edit_view)
                                        .find('.sm_extension[data-type="submodule"][data-relate_copy_id="'+relate_copy_id+'"]')
                                        .after(html)
                                        .remove();
                                });
                            }

                            if(typeof(callback) == 'function'){
                                callback();
                            }
                        }
                    },
                }).done(function(){
                    EditView.activityMessages.init();
                    textAreaResize();
                    EditView.hiddenBlocks();
                    EditView.textRedLine();
                    jScrollRemove();
                    jScrollInit();
                    EditView.textRedLine();
                    Global.createLinkByEV($('.edit-view:last'));
                });
            },



            linkNavigation : function(_this){
                var target = $(_this).data('target');
                switch(target){
                    case 'tg_edit_view':
                        var evInstance = EditView.createInstance();
                        evInstance.subModules
                            .saveInstanceEV(evInstance)
                            .cardEditSM(_this);
                        break;
                }
            },


        },





        /********************************
         *      activityMessages
         *********************************/
        activityMessages : {
            icons_path : '/static/images/emoji',
            _instance: null,
            _url: null,
            _data: null,
            _async: true,
            _handler: null,
            _element: null,
            _async: null,
            _show_message: null,
            _refresh_resource : null,
            _position_delta_scroll : null,
            _position_scroll_relative_point : null,
            _point: null,
            _editing: false,
            refresh_messages  : true,
            request_start: false,

            createInstance : function(){
                var Obj = function(){
                    for(var key in EditView.activityMessages){
                        this[key] = EditView.activityMessages[key];
                    }
                }

                return EditView.activityMessages._instance = new Obj();
            },
            setMessage: function (bool) {
                this._show_message = bool
                return this;
            },
            setData : function(json) {
                this._data = json;
                return this;
            },
            setAsync : function(bool) {
                this._async = bool;
                return this;
            },
            setElement : function(element) {
                this._element = element;
                return this;
            },
            setEditMode : function(bool) {
                this._editing = bool;
                return this;
            },
            setHandler : function(handler) {
                this._handler = handler;
                return this;
            },
            icons : {
                ':smile happy:'  : 'smilehappy.png',
                ':smile:'     : 'smile.png',
                ':smile big:'  : 'smilebig.png',
                ':smile shy:'  : 'smileshy.png',
                ':smile very shy:'  : 'smileveryshy.png',
                ':wink:'  : 'wink.png',
                ':in love:'  : 'inlove.png',
                ':kiss wink:'  : 'kisswink.png',
                ':whistle:'  : 'whistle.png',
                ':whistle shy:'  : 'whistleshy.png',
                ':whistle happy:'  : 'whistlehappy.png',
                ':tongue:'  : 'tongue.png',
                ':tongue eye:'  : 'tongueeye.png',
                ':tongue squint:'  : 'tonguesquint.png',
                ':shy:'  : 'shy.png',
                ':teeth:'  : 'teeth.png',
                ':teeth squint:'  : 'teethsquint.png',
                ':tired sad:'  : 'tiredsad.png',
                ':tired smile:'  : 'tiredsmile.png',
                ':sad side look:'  : 'sadsidelook.png',
                ':sad dawn look:'  : 'saddawn.png',
                ':sad squink:'  : 'sadsquink.png',
                ':cry:'  : 'cry.png',
                ':laugh cry:'  : 'laughcry.png',
                ':wailing:'  : 'wailing.png',
                ':tired:'  : 'tired.png',
                ':sweat:'  : 'sweat.png',
                ':sweat fear:'  : 'sweatfear.png',
                ':laugh sweat:'  : 'laughsweat.png',
                ':tired sweat:'  : 'tiredsweat.png',
                ':desperate:'  : 'desperate.png',
                ':desperate squint:'  : 'desperatesquint.png',
                ':surprised:'  : 'surprised.png',
                ':surprised brows:'  : 'surprisedbrows.png',
                ':surprised bad:'  : 'surprisedbad.png',
                ':surprised fear:'  : 'surprisedfear.png',
                ':scream:'  : 'scream.png',
                ':angry:'     : 'angry.png',
                ':very angry:'     : 'veryangry.png',
                ':boiling:'  : 'boiling.png',
                ':perplex:'  : 'perplex.png',
                ':laughing:'  : 'laughing.png',
                ':tongue smile:'  : 'tonguesmile.png',
                ':glass:'  : 'glass.png',
                ':mask:'  : 'mask.png',
                ':sleep:'  : 'sleep.png',
                ':shok surprise:'  : 'shoksurprise.png',
                ':shok:'  : 'shok.png',
                ':devil:'  : 'devil.png',
                ':devil angry:'  : 'devilangry.png',
                ':surprised o:'  : 'surprisedo.png',
                ':neutral:'  : 'neutral.png',
                ':doubts:'  : 'doubts.png',
                ':surprised o brows:'  : 'surprisedobrows.png',
                ':no moth:'  : 'nomoth.png',
                ':angel:'  : 'angel.png',
                ':smirk:'  : 'smirk.png',
                ':expressionless:'  : 'expressionless.png',
                ':good:'  : 'good.png',
                ':bad:'  : 'bad.png',
                ':ok:'  : 'ok.png',
                ':fist:'  : 'fist.png',
                ':fist up:'  : 'fistup.png',
                ':v:'  : 'v.png',
                ':hand:'  : 'hand.png',
                ':two hands:'  : 'twohands.png',
                ':up:'  : 'up.png',
                ':down:'  : 'down.png',
                ':right:'  : 'right.png',
                ':left:'  : 'left.png',
            },

            ajax : function(){
                var _this = this;

                this.request_start = true;
                this.data_status = false;

                // EditView.activityMessages.data_messages = data.messages;
                AjaxObj
                    .createInstance()
                    .setUrl(this._url + '/' + $(this._element).closest('.edit-view').data('copy_id'))
                    .setData(this._data)
                    .setType('POST')
                    .setDataType('json')
                    .setAsync(this._async)
                    .setCallBackSuccess(function(data){
                        data = data || {};

                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else if(data.status == 'error'){
                            Message.show([{'type': 'error', 'message': data.messages}], true);
                        }else {
                            if(data.status == false){
                                Message.show(data.messages);
                            } else {
                                if(data.status == true){
                                    EditView.activityMessages.data_status = true;

                                    _this._handler(data);
                                }
                            }
                        }
                    })
                    .setCallBackError(function(){
                        if(_this._show_message){
                            Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                        }
                    })
                    .setCallBackDone(function() {
                        EditView.activityMessages.request_start = false;
                    })
                    .send();
            },

            setPoint : function (_element) {
                this._point = _element;
                this._position_scroll_relative_point = modalDialog.getScrollTop();

                return this;
            },

            init : function(){
                var $wysiwyg,
                    _this = this,
                    label = false,
                    $edit_view = $('.edit-view').last(),
                    current_ui = $edit_view.data('unique_index');

                $.emojiarea.path = EditView.activityMessages.icons_path;
                $.emojiarea.icons = EditView.activityMessages.icons;
                redactor.init($edit_view);
                if ( redactor.isTinyMCE()) {
                    return;
                }

                if ($('.user_comment_right .emojis-wysiwyg').length) {
                    $wysiwyg = $('.user_comment_right .emojis-wysiwyg').emojiarea({wysiwyg: true});
                } else if ($('.modal+.modal').length) {
                    if (!$('.modal.in:last-child .emojis-wysiwyg+.emoji-wysiwyg-editor').length) {
                        $wysiwyg = $('.modal.in:last-child .emojis-wysiwyg').emojiarea({wysiwyg: true});
                        label = true;
                    }
                } else {
                    $wysiwyg = $edit_view.find('.emojis-wysiwyg');
                    label = true;
                }

                if (label ) {
                    $.each($wysiwyg, function(key, data) {
                        if (!$(data).next().is('.emoji-wysiwyg-editor')){
                            $(data).emojiarea({wysiwyg: true})
                        }
                    })
                }

                var $wysiwyg_value = $('#emojis-wysiwyg-value');

                if ($wysiwyg && $wysiwyg.length) {
                    $wysiwyg.on('change', function() {
                        var $this = $(this);

                        if ($this.val()) {
                            $this.closest('.message_field').data('modify', true);
                        }

                        $wysiwyg_value.text($this.val());
                    });
                }

                redactor.enablePaste(_this);

                if ( redactor.isEmoji()) {
                    return;
                }

                if ($wysiwyg && $wysiwyg.length) {
                    $wysiwyg.trigger('change');
                }

                EditView.textRedLine();
                jScrollRemove();
                jScrollInit();
                EditView.textRedLine();
                $('.emoji-button+.emoji-wysiwyg-editor+.emoji-button').remove();
                $('.emoji-button+.emoji-wysiwyg-editor').remove();
                $('.message_field .emoji-wysiwyg-editor').on('focus', function(){
                    $('.message_field .emoji-wysiwyg-editor').addClass('is-focused');
                });
                $('.message_field .emoji-wysiwyg-editor').on('blur', function(){
                    setTimeout(function(){
                        $('.message_field .emoji-wysiwyg-editor').removeClass('is-focused');
                    }, 300);
                });
                $('.modal').on('hide.bs.modal', function () {
                    if ($('.message_field .emoji-wysiwyg-editor').hasClass('is-focused')) {
                        return false; // prevent closing of modal
                    }
                });

                if ($wysiwyg && $wysiwyg.find('~ [data-type="drop_down"]').length) {
                    $wysiwyg.find('~ .emoji-wysiwyg-editor').addClass('channel');
                }

                //emoji smiles
                $('.emoji-button').on('click', function() {
                    var $btn = $('a.emoji-button.target'),
                        $this = $(this);
                    $btn.removeClass('target');
                    $this.addClass('target');
                    $btn.after($('.emoji-menu:visible'));
                    //$this.closest('[data-type="message"]').addClass('b_visible');
                });
                EditView.activityMessages.activityImagesNoRedact();
            },

            before : function(unique_index){
                var $block = $('.edit-view[data-unique_index="'+unique_index+'"]'),
                    $select = $block.find('[data-type="switch_type_comment"]'),
                    $activity = $block.find('[data-type="block_activity"]');

                if ($select.length) {
                    $select.selectpicker({style: ''});
                    $select.next().find('button').addClass('btn-primary');

                    var showArea = function (value) {
                        $activity.find('[data-type="editors"] .message_field[data-type_comment]').hide().filter('[data-type_comment="'+ value +'"]').show();
                        $block.find('[data-sub_type="btn-group-editors"]').attr('data-type_comment', value);
                    };

                    showArea($select.val());

                    $select.on('change', function () {
                        var $currentMessage = $activity.find('.message_field:visible'),
                            content = $currentMessage.find('div.emoji-wysiwyg-editor').html();

                        showArea($(this).val());

                        if ($currentMessage.data('modify')) {
                            $activity.find('.message_field:visible div.emoji-wysiwyg-editor').html(content);

                            $currentMessage.removeData('modify');
                        }

                        Communication.initTextArea();
                    })

                    var time = setTimeout(function () {
                        clearTimeout(time);
                        Communication.initTextArea();
                    }, 50);
                }
            },
            setUrl : function (url) {
                this._url = url;
                return this;
            },

            setElementsDefault : function(_this){
                $(_this).closest('.element[data-type="block_activity"]').find('.emoji-wysiwyg-editor').html('');
                var $activity = $(_this).closest('.element[data-type="block_activity"]').find('.activity-ext'),
                    $recipients = $activity.find('.recipients');

                $recipients.not(':first').remove();
                $activity.find('input').val('');
                $recipients.find('.counter').html('');
                $recipients.find('.todo-remove').hide();
                Communication.initTextArea();

            },

            saveMessage : function(_this, content, callback){
                var data = {},
                    attachment = {},
                    $editView = $(_this).closest('.edit-view'),
                    id = $(_this).data('id'),
                    date_edit = {},
                    list_other_id = [],
                    type_comment = $(_this).closest('.element[data-type="edit"]').attr('data-type_comment');

                $(_this).closest('.element[data-type="block_activity"]').find('.element[data-type="block_message"] .element[data-type="message"]').each(function(i, ul){
                    date_edit[$(ul).data('id')] = $(ul).data('date_edit');
                    if($(ul).data('status') == 'temp') list_other_id.push($(ul).data('id'));
                })

                if(typeof(id) == 'undefined' || !id) id = null;

                switch (type_comment) {
                    case 'general' : {
                        data = {
                            'copy_id': $editView.data('copy_id'),
                            'data_id': $editView.data('id'),
                            'id': id,
                            'message': content,
                            'date_edit': date_edit,
                            'list_other_id': list_other_id,
                            'type_comment': 'general',
                        };
                        break;
                    }
                    case 'email' : {
                        var
                            subject_text = $editView.find('.panel-heading .client-name .element_data[data-type="module_title"]').text(),
                            channel_data_id = $editView.find('.element[data-type="block_activity"] .element[data-type="editors"] .element[data-type="drop_down_button"][data-relate_copy_id="'+Communication.createInstance().getId()+'"]').data('id');

                        // block_attributes: block_participant and other
                        var block_participant = Participant.getParticipantList($editView, ['participant', 'email'])

                        data = {
                            'copy_id': $editView.data('copy_id'),
                            'data_id': $editView.data('id'),
                            'id': id,
                            'subject': subject_text,
                            'message': content,
                            'date_edit': date_edit,
                            'list_other_id': list_other_id,
                            'type_comment': 'email',
                            'channel_data_id' : channel_data_id,
                            'block_participant' : block_participant,
                        };
                        break;

                    }
                    default: {
                        break;
                    }
                }

                $(_this).closest('.element[data-type="edit"]').find('.element[data-type="block_attachments"]').each(function(i, ul){
                    var _files = [];
                    $(ul).find('input.upload_file').each(function(i, ul){
                        if($(ul).val()) _files.push($(ul).val());
                    });
                    attachment = _files;
                })
                data['attachment'] = attachment;

                EditView.activityMessages
                    .createInstance()
                    .setUrl(Global.urls.url_edit_view_activity_save_message)
                    .setData(data)
                    .setElement(_this)
                    .setHandler(function(data){
                        EditView.activityMessages.refresh_messages = true;
                        callback(data);
                    })
                    .ajax()
            },

            //issetBlockActivity
            issetBlockActivity: function (edit_view_object) {
                if (edit_view_object) {
                    return ($(edit_view_object).find('.element[data-type="block_activity"]').length ? true : false);
                } else {
                    return ($('.edit-view .element[data-type="block_activity"]').length ? true : false);
                }
            },


            //startRefreshMessagesInterval
            startRefreshMessagesInterval: function () {
                if (this._refresh_resource == null && this.issetBlockActivity()) {
                    this._refresh_resource = setInterval(function () {
                        EditView.activityMessages.refreshMessagesInterval()
                    }, 10000);
                }
            },

            //stopRefreshMessagesInterval
            stopRefreshMessagesInterval: function () {
                if (this._refresh_resource) {
                    clearInterval(this._refresh_resource);
                    this._refresh_resource = null;
                }
            },

            //refreshMessagesInterval
            refreshMessagesInterval: function () {
                if (EditView.activityMessages.refresh_messages == false) {
                    return;
                }

                if (this.issetBlockActivity() == false) {
                    return EditView.activityMessages.stopRefreshMessagesInterval();
                }
                $('.edit-view .element[data-type="block_activity"]').each(function (i, _block_activity) {
                    EditView.activityMessages.refreshMessages(_block_activity);
                })
            },

            //refreshMessages
            refreshMessages: function (_block_activity) {
                var date_edit = {},
                    _this = this,
                    $editView = $(_block_activity).closest('.edit-view'),
                    ev = $editView.data();

                $(_block_activity).find('.element[data-type="block_message"] .element[data-type="message"]').each(function (i, ul) {
                    date_edit[$(ul).data('id')] = $(ul).data('date_edit');
                })

                var data = {
                    'copy_id': ev && ev['copy_id'] ? ev['copy_id'] : null,
                    'data_id': ev && ev['id'] ? ev['id'] : null,
                    'date_edit': date_edit,
                };

                message_alien = EditView.activityMessages
                    .createInstance()
                    .setUrl(Global.urls.url_edit_view_activity_get_message_list)
                    .setData(data)
                    .setAsync(true)
                    .setElement(_block_activity)
                    .setMessage(false)
                    .setHandler(function (data) {
                        if (data.status == true)
                            EditView.activityMessages.refreshMessagesHtml(_block_activity, data.message_list);

                        var instance = modalDialog.getDataFromStore($(_block_activity).closest('.edit-view').data('unique_index'));
                        if (instance && instance.afterViewChanges) {
                            instance.afterViewChanges();
                        }
                    })
                    .ajax();
            },

            //refreshMessagesHtml
            refreshMessagesHtml: function (_block_activity, message_list) {
                if ($.isEmptyObject(message_list)) {
                    return;
                }
                var block_message = $(_block_activity).find('.element[data-type="block_message"]');
                // обновляем
                if ($.isEmptyObject(message_list.message_alien_editing) == false) {
                    $.each(message_list.message_alien_editing, function (data_id, html) {
                        var message = block_message.find('.element[data-type="message"][data-id="' + data_id + '"]');
                        if (message.length > 0) {
                            message.html(html);
                        }
                    });
                }
                // новые
                if ($.isEmptyObject(message_list.message_alien_new) == false) {
                    $.each(message_list.message_alien_new, function (data_id, html) {
                        block_message.prepend(html);
                    });
                }
                // удаляем
                if ($.isEmptyObject(message_list.message_alien_deleted) == false) {
                    $.each(message_list.message_alien_deleted, function (i, data_id) {
                        block_message.find('.element[data-type="message"][data-id="' + data_id + '"]').remove();
                    });
                }
            },

            //addMessage
            addMessage : function(_this, content, callback){
                var $this = $(_this),
                    $editView = $this.closest('.edit-view'),
                    modelEditView = $editView.data(),
                    $attachments = $this.closest('[data-type="edit"]').find('[data-type="block_attachments"]');

                if((content == null || content == undefined) && !$this.closest('[data-type_comment="email"]').length) {
                    Message.show([{
                            'type': 'error',
                            'message': 'No message text entered'
                        }]
                        , true, function () {
                            callback(false);
                        });

                    return;
                }

                var block_activity = $this.closest('.element[data-type="block_activity"]');

                // save Message
                var funcstion_save_message = function(_this){
                    var $this = $(_this),
                        $editView = $this.closest('.edit-view'),
                        modelEditView = $editView.data(),
                        $attachments = $this.closest('[data-type="edit"]').find('[data-type="block_attachments"]');

                    EditView.activityMessages.saveMessage(_this, content,function(data){
                        EditView.activityMessages.setElementsDefault(_this)

                        var upload_files,
                            editting = $this.hasClass('editting'),
                            param = 'size=60';

                        EditView.activityMessages.request_start = false;

                        if(data.status == true){
                            // insert to block Attachments
                            var $attach = $this.closest('.edit-view').find('.element[data-type="attachments"]');
                            $.each(block_activity.find('div.col-xs-6, div.col-xs-12'), function(){
                                var $clone, $img, source, id,
                                    $this = $(this),
                                    closAtt = $this.closest('.edit-view').find('.element[data-type="attachments"] h3');

                                id = parseInt($this.find('input').attr('value'));

                                if(!$attach.find('input.upload_file[value="' + id + '"]').length){
                                    $clone = $this.clone();
                                    $img = $clone.find('img');
                                    source = $img.attr('src') || '';


                                    if(source.indexOf(param) == -1 && source.length){
                                        $img.attr({
                                            'src': source + '&' + param,
                                            'style': $img.attr('style') + ' max-height: 60px'
                                        });
                                    }

                                    $clone.insertBefore(closAtt);
                                }
                            });

                            $('.element[data-type="attachments"] .col-xs-6, .element[data-type="attachments"] .col-xs-12').not('.file-item').wrap('<div class="file-box"></div>').addClass('file-item');
                        }

                        if (redactor.isEmoji()) {
                            $this.find('div').remove();
                        }
                        // refresh messages
                        EditView.activityMessages.refreshMessagesHtml($(_this).closest('.element[data-type="block_activity"]'), data.message_list);

                        var id = $(data.message.html).filter('[data-type="message"]').data('id');
                        // insert where edit message
                        if(editting){
                            var $block = block_activity.find('.element[data-type="block_message"] .element[data-type="message"] .user_comment_right .emoji-wysiwyg-editor')
                                .closest('.element[data-type="message"]')

                            if($block.length){
                                $block.after(data.message.html).show('fast')
                                $block.remove();
                            }
                            // insert new message
                        } else {
                            var is_append = false;
                            if(id){
                                if($.isEmptyObject(data.message_list) == false && $.isEmptyObject(data.message_list.message_alien_new) == false){
                                    $.each(data.message_list.message_alien_new, function(data_id, html){
                                        if(id == data_id){
                                            is_append = true;
                                        }
                                    });
                                }
                            }

                            if(is_append == false){
                                // append new message
                                block_activity.find('.element[data-type="block_message"]')
                                    .prepend(data.message.html)
                                    .find('.element[data-type="message"]:first-child')
                                    .show('fast');
                            }
                        }

                        EditView
                            .changeBlockLoadedMessages(block_activity);

                        if (redactor.isEmoji()) {
                            if(modelEditView){
                                if(!modelEditView['activity_context']){
                                    $this.empty();
                                } else {
                                    $this.html(modelEditView['activity_context']);
                                    delete modelEditView['activity_context'];
                                    upload_files = $attachments;
                                }
                            }
                        }
                        if (redactor.isTinyMCE()) {
                            tinyMCE.activeEditor.setContent('');
                        }

                        $attachments
                            .empty();

                        if(editting){
                            block_activity
                                .find('.element[data-type="edit"] form div.emoji-wysiwyg-editor')
                                .data('id', '')
                                .removeClass('editting');
                        }
                        EditView.replaceForLink($('.element[data-type="message"][data-id="' + id + '"]'));

                        var instance = modalDialog.getDataFromStore($('.edit-view').last().closest('.edit-view').data('unique_index'));
                        if (instance && instance.afterViewChanges) {
                            instance.afterViewChanges();
                        }
                        callback(upload_files);
                    });
                }

                Participant.clearEmailParticipantIfExistsInCommunications(_this, function(_this){
                    funcstion_save_message(_this);
                });


                imagePreview();
            },

            calcPositionDeltaScroll : function () {
                var param,
                    $button = $(this._element).closest('.edit-view').find('.buttons-section'),
                    $modal = $('.modal').last();

                param = this._point_relative_calc_position_scroll ? this._point_relative_calc_position_scroll : $modal.scrollTop();
                this._position_delta_scroll = $button.position().top - param;

                return this;
            },

            //editMessage
            editMessage : function(_this, callback){
                var edit, edit_message, message, offsetOfElement,
                    $this = $(_this),
                    $editView = $this.closest('.edit-view'),
                    ev = $editView.data(),
                    $commentRight = $this.closest('.user_comment_right'),
                    $activityEdit = $this.closest('.element[data-type="block_activity"]').find('.element[data-type="edit"]'),
                    $button = $editView.find('.buttons-section');
                this
                    .setElement($this)
                    .calcPositionDeltaScroll();

                $('.user_comment_right .task_message').each(function(){
                    var $this = $(this);

                    $this.closest('.user_comment_right').find('.user_comment_box').show();
                    $this.closest('.user_comment_right').find('.user_comment_info').show();
                    $this.next().show('fast');
                    $this.remove();
                });

                var type = $(_this).closest('.user_comment').attr('data-type_comment'),
                    $area = '<div class="task_message element" ' +
                        'data-unique_index="'+ Global.createDataUnique() +'"' +
                        'data-type_comment="'+ type +'" data-type="edit"><div class="message_field" data-type_comment="'+ type +'"><form><textarea rows="5" class="emojis-wysiwyg"></textarea></form></div><div class="user_comment_adds element" data-type="block_attachments"></div></div>';

                $commentRight.append($area);
                $commentRight.find('.user_comment_box').hide();
                $(_this).closest('.user_comment_info').hide();
                $commentRight.next().hide('fast');
                /*var fields_for_editing = */
                $activityEdit.find('.message_upload_btn').clone().prependTo( $commentRight.find('.message_field') );
                $activityEdit.find('.send_massage_btns').first().clone().appendTo( $commentRight.find('.task_message') );

                $commentRight.find('.send_massage_activity').show();

                EditView.activityMessages.setEditMode(true).init();
                edit = $commentRight.find('.element[data-type="edit"]');
                edit.find('.element[data-type="switch_type_comment"]').next().hide();
                edit_message = $(_this).closest('.element[data-type="message"]');

                message = $commentRight.find('.user_comment_text');

                // unwrap links
                var results ='',
                    arrLinks = message.html().split('</a>'),
                    arrLinksLength = arrLinks.length;

                for (var i = 0; i< arrLinksLength; i++){
                    if (arrLinks[i].length) {
                        var toChar = arrLinks[i].indexOf('<a href=');
                        var fromCar = arrLinks[i].indexOf('target="_blank">');
                        results += arrLinks[i].substring(0, toChar) + arrLinks[i].substring(fromCar);
                    }
                };

                if(arrLinks.length>1){
                    message.html(results);
                    EditView.replaceForLink(message.closest('[data-type="message"]'));
                }

                //===============
                edit.find('form div.emoji-wysiwyg-editor').html(message.html());

                EditView.activityMessages
                    .setUrl(Global.urls.url_edit_view_activity_get_message_by_id)
                    .setData({
                        'copy_id' : ev['copy_id'],
                        'activity_messages_id' : edit_message.data('id')
                    })
                    .setElement(_this)
                    .setHandler(function(data){
                        EditView.activityMessages.refresh_messages = false;
                        if(data.status == true) {
                                redactor.setContent(edit_message, data, {
                                    'id' : edit_message.data('id')
                                });
                            }
                            imagePreview();
                    })
                    .ajax();

                $this.closest('.user_comment_right .message_upload_btn .upload_link_activity').remove();

                modalDialog.setScrollTop($button.position().top - this._position_delta_scroll - 19)
                var time,
                    key = $(_this).closest('.edit-view').data('unique_index');

                time = setTimeout(function () {
                    clearTimeout(time);
                    modalDialog.getDataFromStore(key)._scrolled = null;
                }, 100);

                callback();
            },

            deleteMessage : function(_this, callback){
                var data = {
                    'id' : $(_this).closest('.element[data-type="message"]').data('id'),
                    'data_id' : $(_this).closest('.edit-view').data('id'),
                };
                $(_this).closest('.element[data-type="message"]').find('.user_comment_adds .file-block').each(function(){
                    $('.element[data-type="attachments"] .file-block[data-type="activity"] .image-preview[data-id="'+$(this).find('.image-preview').data('id')+'"]').closest('.file-box').remove();
                });
                EditView.activityMessages
                    .setUrl(Global.urls.url_edit_view_activity_delete_message)
                    .setData(data)
                    .setElement(_this)
                    .setHandler(function(data){
                        $(_this).closest('.element[data-type="message"]').hide('fast', function(){
                            $(_this).remove();
                            callback();
                        });
                    })
                    .ajax()
            },

            // удаляет файл из сообщения навсегда
            deleteFile : function(upload_id, _this){
                EditView.activityMessages
                    .setUrl(Global.urls.url_edit_view_activity_delete_file)
                    .setData({'upload_id' : upload_id})
                    .setElement(_this)
                    .setHandler(function(data){})
                    .ajax();
            },

            addGooleDoc : function(_this){
                var ev = $(_this).closest('.edit-view').data();

                Global.Files.showGoogleDocView({'copy_id' : ev['copy_id']});
            },

            uploadUrlLink : function(_this){
                var data = {
                    'url' : $(_this).closest('.panel').find('input').val(),
                    'get_view' : '1',
                    'thumb_scenario' : 'activity',
                    'copy_id' : $(_this).closest('.panel').data('copy_id'),
                }
                Global.Files.uploadUrlLink(data, function(data){
                    $('.edit-view[data-copy_id="'+$(_this).closest('.panel').data('copy_id')+'"]')
                        .find('.element[data-type="block_activity"] .gdoc_target.element[data-type="edit"] .element[data-type="block_attachments"]')
                        .append(data.view);
                    modalDialog.hide();
                })



            },

            sendUploadMassage : function($target){
                var type_comment = $target.closest('.edit-view').find('.element[data-sub_type="btn-group-editors"]').attr('data-type_comment'),
                    $message = $target.closest('.task_comments .task_message');

                if (type_comment == 'general'){
                    $message.find('form .emoji-wysiwyg-editor').text(Message.translate_local('Upload file'));
                    $message.find('.send_massage_activity').trigger('click');
                }
            },

            activityImagesNoRedact : function(){
                if($('.edit-view .user_comment_adds .image-preview img').attr('src')) {
                    $('.edit-view .user_comment_adds .image-preview').closest('.user_comment').find('.user_comment_redact').remove();
                }
            },
        },



    }


    $(document).ready(function() {

        var eventPath;

        /********************************
         *      activityMessages
         *********************************/

        eventPath = 'a.user_comment_delete';
        //видалення коментаря
        $(document).off('click', eventPath).on('click', eventPath, function() {
            EditView.activityMessages.deleteMessage(this, function(){return false;});
        });

        //редагуванння комментаря
        eventPath = 'a.user_comment_redact';
        $(document).off('click', eventPath).on('click', eventPath, function() {
            var object,
                $block = $(this).closest('.user_comment');

            object = $block.closest('.edit-view').data();

            if (object) {
                object.updateMessage = $block.clone().wrap('<div/>').parent().html();
            }

            EditView.activityMessages.editMessage(this, function(){return false;})
        });

        //редагуванння комментаря
        eventPath = 'a.upload_link_activity_google_doc';
        $(document).off('click', eventPath).on('click', eventPath, function() {
            EditView.activityMessages.addGooleDoc(this);
            $('.gdoc_target').removeClass('gdoc_target');
            $(this).closest('.element[data-type="edit"]').addClass('gdoc_target');
        });

        //редагуванння комментаря
        eventPath = '.activity_btn-add-google-doc';
        $(document).off('click', eventPath).on('click', eventPath, function() {
            EditView.activityMessages.uploadUrlLink(this);
        });

        eventPath = 'form div.emoji-wysiwyg-editor';
        $(document).off('focus', eventPath).on('focus', eventPath, function(){
            $(this).closest('.task_message').find('.btn.send_massage_activity').show('fast');
        });


        /********************************
         *      activityMessages End
         *********************************/

        $(document).on('keyup', EditView.onKeyUp);

        $(document).on('click', function(e){
            if ($('.edit-view.in').length) {
                EditView.saveDraftToLocalStorage(e);
            }
            return true;
        });

        /**
         * Operations for submodule
         */
        eventPath = '.submodule-link';
        $(document).off('show.bs.dropdown', eventPath).on('show.bs.dropdown', eventPath, function () {
            TableSearchInit('.submodule-table', '.submodule-search');
            $this = $(this);
            setTimeout(function(){ EditView.textRedLine($this); }, 200);
        });

        eventPath = '.submodule-table td:first-child';
        $(document).off('click', eventPath).on('click', eventPath, function() {
            var $checkbox,
                $this = $(this),
                card_element = $this.closest('.edit-view'),

                //dd_button = $this.closest('.dropdown.submodule-link.crm-dropdown').find('[data-type="drop_down_button"]'),
                //copy_id = card_element.data('copy_id'),
                //data_id = card_element.data('id'),
                //module_title = card_element.find('span[data-type="module_title"]').text();

                /*
        if(dd_button.length>0 && Communication._copy_id && dd_button.attr('data-relate_copy_id') && dd_button.attr('data-relate_copy_id') == Communication._copy_id && $('.edit-view').length){
            EditView.relates.reloadActivityMessages(copy_id,data_id,module_title,Communication._copy_id, $this.closest('.sm_extension_data').data('id'),$this);
        }
        */
                $checkbox = $this.closest('tr').find('.checkbox');
            $checkbox.not('[data-name="all-checked"]').prop('checked', !($checkbox.is(':checked')));
        });

        $(document).on('click', '.submodule-link .submodule-search', function (e) {
            e.stopPropagation();
        });

        eventPath = '.submodule-link td';
        $(document).off('click', eventPath).on('click', eventPath, function(e){
            var _this = this;

            //if (instanceReports) return;

            base_id = null;
            var relate = $(this).closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant');
            if(relate.data('reloader') == 'parent'){
                base_id = relate.data('id');
            }

            $(_this)
                .closest('.submodule-link')
                .find('.element_relate, .element_relate_this, .element_relate_participant')
                .val($(_this).find('.name').text())
                .data('id', $(this).closest('tr').data('id'));

            $(_this)
                .closest('.submodule-link')
                .find('.element_relate_participant')
                .data('ug_id', $(_this).closest('tr').data('ug_id'))
                .data('ug_type', $(_this).closest('tr').data('ug_type'));
            var tr = $(_this).closest('tr');
            tr.closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant').html(tr.find('td').html());

            EditView.relates.reloadEditView(_this, base_id);
            EditView.relates.reloadInLine(_this, null, base_id, true, false, 1);
        });

        $(document).on('click', '.submodule_edit_view_dnt-create', function(){
            Preloader
                .createInstance()
                .setModal(true)
                .setModalSub(true)

            EditView.subModules.cardCreate(this);
        });

        $(document).on('click', '.submodule_edit_view_dnt-create-select', function(){
            EditView.subModules.addCardSelect(this, 'edit-view');
        });

        $(document).on('click', '.submodule_edit_view_dnt-edit', function(){
            EditView.subModules.cardEditSM(this);
        });

        $(document).on('click', '.sdm_edit_view_dnt-edit', function(){
            EditView.subModules.cardEditSDM(this);
        });

        $(document).on('click', '.submodule_edit_view_dnt-select', function(){
            EditView.subModules.cardSelect(this);
        });

        $(document).on('click', '.submodule_edit_view_dnt-copy', function(){
            EditView.subModules.cardCopy(this);
        });

        $(document).on('click', '.submodule_edit_view_dnt-delete', function(){
            EditView.subModules.cardRemoved(this);
        });

        $(document).on('click', '.submodule_list_view_btn-tie', function(){
            EditView.subModules.cardTie(this);
        });


        $(document).on('click', '.edit-view .edit-view .edit_view_select_btn-create', function(){
            var _this = this
            EditView.cardSelectValidate(_this, function(data){
                if(data){
                    var sub_module = $('.edit-view[data-copy_id="'+$(_this).closest('.sm_extension').data('parent_copy_id')+'"] .sm_extension[data-type="submodule"][data-relate_copy_id="'+$(_this).closest('.sm_extension').data('copy_id')+'"] .submodule_edit_view_dnt-create-select');
                    EditView.subModules.addCardFromTemplate(_this, sub_module);
                }
            })
        });

        $(document).on('click', '.edit-view .edit_view_select_btn-create', function(){
            $(this).closest('.edit-view').data('submitted',true);
        });





        /**
         * Uploading files
         */

        // Удаляем взагруженный файл. Используется, если input[type="hidden"]
        $(document).on('click','.element[data-type="remove_image_file"]', function() {
            $(this).addClass('hidden');
            $(this).closest('.element[data-type="file_upload_block"]').find('input[type="hidden"]').val('');
            $(this).closest('.element[data-type="file_upload_block"]').find('.element[data-type="file_view"]').val('');
        });

        //Стандартный тип, контакты - upload_link, upload_link_contact_image
        $(document).on('click','.upload_link, .element[data-type="upload_image"][data-name="edit_view"]', function() {

            var ev,
                _this = $(this);

            upload_link_class_name = 'has-file';
            var thumb_scenario = 'upload';
            var file_type = 'file';

            if(_this.hasClass('upload_link_contact_image')){
                thumb_scenario = 'avatar';
                file_type = 'file_image';
                upload_link_class_name = 'has-file-contact-image';
            }

            $('.upload-status .progress-bar').width('0%');

            _this.parent().addClass(upload_link_class_name);
            $('.upload-section').show();

            modalDialog
                .createInstance()
                .setParentClass(inLineEdit.isEditing() ? 'popup-upload' : null)
                .show('<div class="modal-dialog upload-modal">' +  $('#upload_template').html() + '</div>');

            if ($('.'+upload_link_class_name).data('type') == 'file_image') {
                $('.upload-modal #files').attr('accept', 'image/*');
                file_type = 'file_image';
            }
            // Обработчик событий для файла
            $('#files').data('thumb_scenario', thumb_scenario);
            $('#files').data('file_type', file_type);
            // copy_id
            if(_this.hasClass('upload_link')){
                var data_edit = _this.closest('td.data_edit');
                if(data_edit.length > 0){
                    $('#files').data('copy_id', _this.closest('.sm_extension').data('copy_id'));
                } else {
                    ev = _this.closest('.edit-view').data();
                    $('#files').data('copy_id', ev['copy_id']);
                }
            } else if(_this.hasClass('upload_link_contact_image')){
                var modal = _this.closest('.modal-dialog').data();

                $('#files').data('copy_id', modal['edit-view'].copy_id);
            }

            document.getElementById('files').addEventListener('change', handleFileSelect, false);
        });


        //Загрузка изображения - upload_image_link
        $(document).on('click','.upload_image_link', function() {
            var _this = $(this);

            upload_link_class_name = 'has-file';
            upload_link_class_view = _this;

            modalDialog
                .createInstance()
                .setParentClass(null)
                .show('<div class="modal-dialog upload-modal">' +  $(crmParams.message_dialog_upload_select_file).html() + '</div>');

            $('.upload-status .progress-bar').width('0%');

            // Обработчик событий для файла
            _this.parent().find('input[type="hidden"]').addClass(upload_link_class_name);
            $('.upload-modal #files').attr('accept', 'image/*');
            $('#files').data('thumb_scenario', 0);
            $('#files').data('file_type', 'file_image');
            $('#files').data('copy_id', -1);

            document.getElementById('files').addEventListener('change', handleFileSelectInput, false);
        });

        //upload_link_activity
        $(document).on('click', '.upload_link_activity', function() {
            var $this = $(this),
                $editView = $this.closest('.edit-view'),
                ev = $editView.data();

            $('.download_target').removeClass('download_target');
            $this.closest('.element[data-type="edit"]').find('.element[data-type="block_attachments"]').addClass('download_target');
            $('.upload-status .progress-bar').width('0%');
            $('.upload-section').show();

            modalDialog.show('<div class="modal-dialog upload-modal">' +  $('#upload_template').html() + '</div>');

            var handler,
                data = Url.parseFull();

            if (data && (data.id == 'profile' || data.id == 'parameters' )) {
                $('#files').data('parentElement', $this);
                handler = handleFileSelectBackground;
            } else {
                // Обработчик событий для файла
                $('#files').data('copy_id', ev['copy_id']);
                handler = handleFileSelectActivity;
            }

            document.getElementById('files').addEventListener('change', handler, false);
        });


        //Вложения
        $(document).on('click', '.element[data-type="attachments"] .drop_zone', function(e){
            return false;
        });



        // Профиль - upload profile
        $(document).on('click', '.element[data-type="upload_image"][data-name="profile"]', function() {
            $('.upload-status .progress-bar').width('0%');
            $('.upload-section').show();
            modalDialog.show('<div class="modal-dialog upload-modal">' +  $('#upload_template').html() + '</div>');
            $('.upload-modal #files').attr('accept', 'image/*');
            // Обработчик событий для файла
            document.getElementById('files').addEventListener('change', handleFileProfile, false);
        });

        $(document).on('click', '.file-block[data-type="activity"] .file-remove', function(){
            var $userComment,
                $this = $(this),
                deletedId = $this.closest('.upload-result').find('.image-preview').data('id'),
                $massageForDelete = $('.user_comment_adds a.image-preview[data-id="'+deletedId+'"]').closest('.user_comment_adds'),
                $deletedPreview = $this.closest('.modal').find('.element[data-type="block_activity"] .image-preview[data-id="'+deletedId+'"]');

            EditView.activityMessages.deleteFile(deletedId, this);
            $(this).closest('.modal').find('.element[data-type="attachments"] .image-preview[data-id="'+deletedId+'"]').closest('.file-box').remove();
            $deletedPreview.closest('.col-xs-6, .col-xs-12').remove();
            $(this).closest('.file-box').remove();

            if (!$massageForDelete.find('img').length) {
                $massageForDelete.closest('.user_comment').find('.user_comment_delete').trigger('click');
            }
        });

        $(document).on('click', '.element[data-type="attachments"] .file-remove', function(){
            $(this).closest('.col-xs-6, .col-xs-12').remove();
        });


        $(document).on('click', '.element[data-type="remove_image"][data-name="edit_view"]', function(){
            var _this = this;
            Global.Files.fileDelete($(_this).closest('.file-block').find('.upload_file').val(), false, function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else {
                    $(_this).closest('.file-block')
                        .find('.thumb').attr('src', '').attr('title', '')
                        .hide()
                        .closest('.file-block').find('.upload_file').val('')
                    $(_this).closest('.file-block').find('.thumb_zero').show();
                    $(_this).closest('.file-block').find('.errorMessage').text('');
                    // $('.table-section .crm-table-wrapper').getNiceScroll().remove();
                    // niceScrollInit();
                }
            });
        });


        $(document).on('click', '.file-block .btn', function() {
            $(this).next('input').trigger('click');
        });



        function getUploadStatus(bar){
            var file_status;
            $.ajax({
                url: Global.urls.url_upload_file_progress,
                dataType: 'json',
                success: function(data){
                    if(data.percent) {
                        bar.width(data.percent+'%');
                    }
                }
            });
        }


        // upload file
        function handleFileSelect(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;
            status.width('0%');
            var _this = this;
            var form_data = new FormData();
            form_data.append(Global.urls.session_upload_progress_name, 'test');
            var has_file = $('.'+upload_link_class_name);
            form_data.append("file", evt.target.files[0]);
            form_data.append("thumb_scenario", $(_this).data('thumb_scenario'));
            form_data.append("file_type", $(_this).data('file_type'));
            form_data.append("copy_id", $(_this).data('copy_id'));

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);
            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }

            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-section').hide();
                    $('.upload-status').show();
                    // Вставляем имя файла
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        $('.upload-section, .'+upload_link_class_name+' .upload_link').hide();
                        $('.upload-status, .'+upload_link_class_name+' .upload-result').show();
                        has_file.find('.filename').text(data.fileInfo.file_title);
                        // Вставляем миниатюру
                        if (data.fileInfo.file_type_class == 'file_image' && has_file.closest('.file-block').data('type') == 'file_image' ) {
                            has_file.find('.image-preview').attr('href', data.fileInfo.file_url)
                                .attr('title', data.fileInfo.file_title)
                                .data('id', data.fileInfo.id)
                                .data('dateupload', data.fileInfo.file_date_upload)
                                .data('filesize', data.fileInfo.file_size)
                                .data('download-link', data.fileInfo.file_url);

                            has_file.find('.file_thumb').addClass('hide');
                            if(upload_link_class_name == 'has-file')
                                has_file.find('.thumb-block').show().find('.thumb')
                                    .prop('src', data.fileInfo.file_thumb_url)
                                    .prop('title', data.fileInfo.file_title);
                            else
                            if(upload_link_class_name == 'has-file-contact-image'){
                                has_file.find('.thumb-block-contact-image').show()
                                    .find('.thumb')
                                    .show()
                                    .prop('src', data.fileInfo.file_thumb_url)
                                    .prop('title', data.fileInfo.file_title);
                                has_file.find('.thumb_zero').hide();
                            }


                            imagePreview();
                        } else {
                            has_file.find('.thumb-block').hide();
                            has_file.find('.file_thumb').show().removeClass().addClass('file_thumb ' + data.fileInfo.file_type_class).text(data.fileInfo.file_type);
                        }

                        has_file.find('.upload_link').addClass('hide');
                        has_file.closest('td').addClass('content-image');

                        // Вставляем дату и название файла
                        $('.list-view-panel .crm-table-wrapper').getNiceScroll().remove();
                        niceScrollInit();
                        status.width('100%');
                        has_file.find('.filedate').text(data.fileInfo.file_date_upload);
                        has_file.find('.upload_file').val(data.fileInfo.id);
                        has_file.find('.file-download').attr('href', data.fileInfo.file_url);
                        //проверяем, есть ли ссылка для генерации документа
                        var generate_link = has_file.find('.image-preview').data('parent_id');
                        if(!!generate_link) {
                            $('.upload-status, .'+upload_link_class_name+' .upload-result').removeClass('generate_only');
                            $('.list_view_btn-generate').hide();
                            $('.list_view_btn-generate_edit').hide();
                        }
                        has_file.removeClass(upload_link_class_name);
                        setTimeout(function() {
                            modalDialog.hide();
                            Global.blockErrors.verify();
                        }, 500);
                        setCheckboxHeight();
                    }
                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        }


        // upload image file
        function handleFileSelectInput(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;
            status.width('0%');
            var _this = this;
            var has_file = $('.'+upload_link_class_name);
            var form_data = new FormData();

            form_data.append(Global.urls.session_upload_progress_name, 'test');
            form_data.append("file", evt.target.files[0]);
            form_data.append("thumb_scenario", $(_this).data('thumb_scenario'));
            form_data.append("file_type", $(_this).data('file_type'));
            form_data.append("copy_id", $(_this).data('copy_id'));
            form_data.append("format", $(has_file).data('format'));
            form_data.append("image_size_pixels", $(has_file).data('image_size_pixels'));

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);

            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }

            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-section').hide();
                    $('.upload-status').show();
                    // Вставляем имя файла
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        $('.upload-section, .'+upload_link_class_name+' .upload_link').hide();
                        $('.upload-status, .'+upload_link_class_name+' .upload-result').show();
                        $('.upload-status, .'+upload_link_class_name+' .upload-result').show();
                        $(has_file).closest('.element[data-type="file_upload_block"]').find('.element[data-type="remove_image_file"]').removeClass('hidden');

                        has_file.val(data.fileInfo.id);
                        upload_link_class_view.val(data.fileInfo.file_title);
                        has_file.removeClass(upload_link_class_name);
                        status.width('100%');
                        setTimeout(function() {
                            modalDialog.hide();
                            Global.blockErrors.verify();
                        }, 500);
                    }
                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        }



        // upload profile file
        function handleFileProfile(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;
            status.width('0%');
            var _this = this;
            var form_data = new FormData();
            form_data.append(Global.urls.session_upload_progress_name, 'test');
            form_data.append("file", evt.target.files[0]);
            form_data.append("thumb_scenario", "profile");
            form_data.append("file_type", "file_image");
            form_data.append("get_view", "1");
            form_data.append("copy_id", '-1');

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);
            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }


            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-status').show();
                    $('.upload-section').hide(); // new line
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        $('.profile-information .element[data-type="profile"] .file-block').after(data.view.avatar_140).remove();
                        $('.element[data-type="main_top_profile_menu_user"]').find('.list-view-avatar').after(data.view.avatar_32).remove();
                        imagePreview();
                        niceScrollInit();
                        status.width('100%');
                        setTimeout(function() {
                            modalDialog.hide();
                        }, 500);
                        setCheckboxHeight();
                    }

                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            }).done(function(){
                $('.profile-information .profile-pic img').on('load', function() {
                    profileImgProp();
                });
            });
        }




        function  handleFileSelectBackground(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;

            status.width('0%');

            var _this = this,
                parentElement = $(this).data('parentElement');

            var form_data = new FormData();

            form_data.append(Global.urls.session_upload_progress_name, 'test');
            form_data.append("file", evt.target.files[0]);
            form_data.append("file_type", "background");

            parentElement.val(evt.target.files[0].name);

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);
            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }


            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-status').show();
                    $('.upload-section').hide(); // new line
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        var modelEditView,
                            $target = $('.download_target'),
                            $ev = $target.closest('.edit-view');

                        $('.download_target').append(data.view);
                        $('.download_target').removeClass('download_target');

                        status.width('100%');
                        setTimeout(function() {
                            modalDialog.hide();
                        }, 500);
                    }
                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        }



        // upload activity file
        function handleFileSelectActivity(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;
            status.width('0%');
            var _this = this;
            var form_data = new FormData();
            form_data.append(Global.urls.session_upload_progress_name, 'test');
            form_data.append("file", evt.target.files[0]);
            form_data.append("thumb_scenario", "activity");
            form_data.append("file_type", "activity");
            form_data.append("get_view", "1");
            form_data.append("copy_id", $(_this).data('copy_id'));

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);
            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }


            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-status').show();
                    $('.upload-section').hide(); // new line
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        var modelEditView,
                            $target = $('.download_target'),
                            $ev = $target.closest('.edit-view');

                        if (!$target.closest('.element[data-type_comment="email"]').length) {
                            modelEditView = $ev.data();

                            if (modelEditView) {
                                modelEditView['activity_context'] = $ev.find('form .emoji-wysiwyg-editor').html();
                            }
                        }

                        $('.download_target').append(data.view);
                        $('.download_target').removeClass('download_target');
                        niceScrollInit();
                        status.width('100%');
                        EditView.activityMessages.sendUploadMassage($target);
                        setTimeout(function() {
                            modalDialog.hide();
                            imagePreview();
                            EditView.activityMessages.activityImagesNoRedact();
                        }, 500);
                        setCheckboxHeight();
                    }
                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        }






        // upload Attachments file
        function handleFileSelectAttachments(evt) {
            var status = $('.upload-status .progress-bar');
            var intervalID;
            status.width('0%');
            var _this = this;

            var form_data = new FormData();
            form_data.append(Global.urls.session_upload_progress_name, 'test');
            form_data.append("file", evt.target.files[0]);
            form_data.append("thumb_scenario", "attachments");
            form_data.append("file_type", "attachments");
            form_data.append("get_view", "1");
            form_data.append("copy_id", $(_this).data('copy_id'));

            var post_max_size = parseInt(Global.urls.post_max_size);
            var upload_max_filesize = parseInt(Global.urls.upload_max_filesize);
            if(post_max_size <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum size of {s2} bytes to POST request',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }
            if(upload_max_filesize <  parseInt(evt.target.files[0].size)){
                Message.show([{'type':'error',
                        'message':'File size {s1} bytes exceeds the maximum allowable size, amounting to {s2} bytes',
                        params: {'s1': evt.target.files[0].size, 's2': upload_max_filesize}}],
                    true);
                return;
            }

            $.ajax({
                url: Global.urls.url_upload_file,
                data: form_data,
                processData: false, type: "POST", dataType: 'json',
                contentType: false,
                timeout: 0,
                beforeSend: function() {
                    intervalID = setInterval(function(){getUploadStatus(status)}, 300);
                    $('.upload-status').show();
                    $('.upload-section').hide(); // new line
                    $('.upload-status .upload-filename').text(evt.target.files[0].name);
                },
                complete : function(){
                    clearInterval(intervalID);
                },
                success: function(data){
                    if(data.status == false){
                        modalDialog.hide();
                        Message.show(data.messages, false);
                    } else {
                        var files_block = $('.download_target');

                        files_block.prepend(data.view);
                        files_block.closest('.task_comments').find('.btn.send_massage_activity').show('fast');
                        files_block.removeClass('download_target');

                        if (files_block.find('[data-type="attachments"]').length) {
                            files_block.find('.file_is_empty').hide();
                        }

                        imagePreview();
                        niceScrollInit();
                        status.width('100%');
                        setTimeout(function() {
                            modalDialog.hide();
                        }, 500);
                        setCheckboxHeight();
                    }
                },
                error: function(){
                    clearInterval(intervalID);
                    Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
                }
            });
        }






        // end of uploading files
        $(document).on('click', '.preview-file-remove', function(){
            Message.show([{'type':'confirm', 'message': Message.translate_local('Remove image')+ '?'}], false, function(_this_c){
                if($(_this_c).hasClass('yes-button')){
                    modalDialog.hide();

                    Global.Files.fileDelete($(this).data('id'), null, function(data){
                        if(data.status == false){
                            Message.show(data.messages, false);
                        } else {
                            document.location.reload();
                        }
                    });
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        });


        $(document).on('click', '.edit-view .fa-chevron-up', function(){
            EditView.textRedLine();
            jScrollRemove();
            jScrollInit();
            EditView.textRedLine();
        });

        $(document).on('change','.edit-view.in input[type="text"].form-control', function(){
            EditView.setModifier(true);
        });
        $(document).on('click','.edit-view.in input[type="checkbox"], .edit-view.in .operations a', function(){
            EditView.setModifier(true);
        });

        $(document).on('dragenter', 'div.edit-view .element[data-type="block_activity"], div.edit-view .element[data-type="attachments"]', function(){
            var _this = this,
                ev = $(_this).closest('.edit-view').data();

            $('.download_target').removeClass('download_target');
            var block_activity = $(_this).closest('.panel').closest('.panel-body').find('.element[data-type="block_activity"]');

            if (block_activity.length) {
                block_activity.find('.task_comments .task_message .element[data-type="block_attachments"]').addClass('download_target');
                $('.upload-status .progress-bar').width('0%');
                $('.upload-section').show();
                modalDialog.show('<div class="modal-dialog upload-modal">' +  $('#upload_template').html() + '</div>');
                $('#files').data('copy_id', ev.copy_id);
                document.getElementById('files').addEventListener('change', handleFileSelectActivity, false);
            } else {
                var block_attachments = $(_this).closest('[data-type="attachments"]');

                if (block_attachments.length) {
                    block_attachments.addClass('download_target');

                    $('.upload-status .progress-bar').width('0%');
                    $('.upload-section').show();
                    modalDialog.show('<div class="modal-dialog upload-modal">' + $('#upload_template').html() + '</div>');
                    $('#files').data('copy_id', ev.copy_id);

                    document.getElementById('files').addEventListener('change', handleFileSelectAttachments, false);
                }
            }
        });

        $(document).on('keydown', function(e){
            if (e.ctrlKey && e.altKey && e.which == 86 && e.type == 'keydown') {
                if ($('div.edit-view .element[data-type="block_activity"]').length){
                    var _this = $('div.edit-view .element[data-type="block_activity"]');
                } else {
                    var _this = $('div.edit-view .element[data-type="attachments"]');
                }
                var modelEditView = $(_this).closest('.edit-view').data();

                $('.download_target').removeClass('download_target');
                var block_activity = $(_this).closest('.panel').closest('.panel-body').find('.element[data-type="block_activity"]');
                if (block_activity.length) {
                    block_activity.find('.task_comments>.task_message .element[data-type="block_attachments"]').addClass('download_target');
                    $('.upload-status .progress-bar').width('0%');
                    $('.upload-section').show();
                    modalDialog.show('<div class="modal-dialog upload-modal">' +  $('#upload_template').html() + '</div>');
                    $('#files').data('copy_id', modelEditView['copy_id']);
                    document.getElementById('files').addEventListener('change', handleFileSelectActivity, false);
                } else {
                    var block_attachments = $(_this).closest('[data-type="attachments"]');

                    if (block_attachments.length) {
                        block_attachments.addClass('download_target');

                        $('.upload-status .progress-bar').width('0%');
                        $('.upload-section').show();
                        modalDialog.show('<div class="modal-dialog upload-modal">' + $('#upload_template').html() + '</div>');
                        $('#files').data('copy_id', modelEditView['copy_id']);

                        document.getElementById('files').addEventListener('change', handleFileSelectAttachments, false);
                    }
                }
            }
        });

        $(document).on('click', '.edit-view.in ul.selectpicker', function(){
            var column = $(this).closest('.column');
            EditView.setModifier(true);
            if (!column.find('.errorMessage').length || !$(this).closest('.edit-view').data('submitted')) { return; }

            var selected = column.find('li.selected');

            if (column.is('.b_error') && selected.index()) {
                column.removeClass('b_error').find('.errorMessage').hide();
            } else {
                if (!selected.index()) {
                    column.addClass('b_error').find('.errorMessage').show();
                }
            }
        });

        $(document).on('hover', '.message_field .emoji-menu a img', function(){
            $emojiFrom = $(this);
            $emojiTo = $(this).parent().parent().next();
            $emojiTo.css('background-image', 'url('+$emojiFrom.attr('src')+')');
            $emojiTo.find('span').html($emojiFrom.attr('alt'));
        });

        //change color on end-date
        $(document).on('change', '.buttons-block .element[data-type="button"] label input[type="button"]',function () {
            var todayDate = new Date();
            var endDate = $(this).datepicker("getDate");
            if (todayDate.getFullYear() < endDate.getFullYear()) {
                $(this).parent().attr('datetime','');
            } else if (todayDate.getMonth()+1 < endDate.getMonth()+1) {
                $(this).parent().attr('datetime','');
            } else if (todayDate.getDate() <= endDate.getDate()) {
                $(this).parent().attr('datetime','');
            } else {
                $(this).attr('datetime','red');
            }
        });

        //change color on end-date
        //  $(document).on('change', '.buttons-block .element[data-type="button"] label input[type="button"]',function () {
        //      var $this = $(this),
        //          endDate = $this.datetimepicker("getDate"),
        //          $parent = $this.parent(),
        //          currentDate = moment($this.val()).format(crmParams.getCurrentFormatDate());
        //
        //      if (moment().diff(endDate, 'minutes') > 0) {
        //          $parent.attr('datetime','red');
        //      } else {
        //          $parent.attr('datetime','');
        //      }
        //
        //      $this.datetimepicker('hide');
        //      $this.val(moment(currentDate, crmParams.getCurrentFormatDate()).format(crmParams.FORMAT_DATE));
        //      $this.next().text(currentDate);
        //  });

        $('body').on('click', '.fa-arrow-circle-up', function(){
            $('.task_comments').on('click', '.list-view-avatar', function(){$('a.emoji-button').on('click', function(){$(this).solar();});});
        });

        $('body').on('click', '.edit-view .bootstrap-select, .list-view-panel #list-table_wrapper .bootstrap-select', function(){
            var viewType,
                $this = $(this);

            if ($this.find('ul.dropdown-menu li').length > 8 && !$this.is('.open')){
                var event = 'shown.bs.dropdown';

                viewType = $('.edit-view').length ? Constant.VIEW_TYPE_EDIT_VIEW : Constant.VIEW_TYPE_LIST_VIEW;

                $this.find('ul.dropdown-menu').css('min-width',$this.width()+'px');
                $this.find('div.dropdown-menu').addClass('padded h-auto');

                $this.off(event).on(event, function() {
                    var config,
                        $scroll = $(this).find('ul.dropdown-menu');

                    $scroll.getNiceScroll().remove();

                    if (Global.browser.isIOS()) {
                        $this.find('.dropdown-backdrop').remove();
                    }
                    DropDownListObj
                        .setViewType(viewType)
                        .setElement($this)
                        .agreeIsScroll($this.find('ul.dropdown-menu'), function (data) {
                            $this.find('ul.dropdown-menu').height(data.max_height);
                            $this.find('div.dropdown-menu').height(data.max_height);

                            config = {
                                cursorcolor: "#1FB5AD",
                                cursorborder: "0px solid #fff",
                                cursorborderradius: "0px",
                                cursorwidth: "3px",
                                railalign: 'right',
                                preservenativescrolling: false,
                                horizrailenabled:false,
                                autohidemode: false
                            }
                            niceScrollCreate($scroll, config);
                        });
                });
            } else {
                // scroll not need
                $this.find('div.dropdown-menu').css('min-height', 0);
            }
        });
    });


    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.instanceEditView = instanceEditView;
    exports.EditViewContainer = EditViewContainer;
    exports.EditView = EditView;
})(window);

