var Participant  = {
    TYPE_ITEM_PARTICIPANT   : 'participant',
    TYPE_ITEM_EMAIL         : 'email',

    init : function(){
        this.events();
    },

    events : function () {
        var path = this.actions;

        this._events = [
            //on...AddNewItemEmail
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"][data-type_item_list="email"] .submodule-search', event: 'keyup', func: path.onKeyUpEnterAddNewItemEmail},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"][data-type_item_list="email"] .search-section .add', event: 'click', func: path.onClickAddNewItemEmail},

            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="block-card"] .participant .remove', event: 'click', func: path.onClickRemoveSelectedItemIcon},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"][data-type_item_list="selected_item_list"] .remove', event: 'click', func: path.onClickRemoveSelectedItemList},

            //onClickSwitchItemList
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .element[data-type="item_list_switch"]', event: 'click', func: path.onClickSwitchItemList},

            // onClickAddParticipant
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .element[data-type="block-card-participant"] .element[data-type="participant"].add', event: 'click', func: path.onClickAddSelectedParticipant},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .element[data-type="block-card-participant"] .element[data-type="email"].add', event: 'click', func: path.onClickAddSelectedParticipant},

            //onClickSelectedItemList
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .link-selected-item-list', event: 'click', func: path.onClickSelectedItemList},

            //onClickItemListAdd...
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .link-item-list-add-participant', event: 'click', func: path.onClickItemListAddParticipant},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .link-item-list-add-email', event: 'click', func: path.onClickItemListAddEmail},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .link-item-list-add-participant-button', event: 'click', func: path.onClickItemListAddParticipantButton},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .link-item-list-add-email-button', event: 'click', func: path.onClickItemListAddEmailButton},

            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"]+.element[data-type="select"]', event: 'hide.bs.dropdown', func: path.onDropDownHide},

            // Кнопка "Ответсвенный"
            { parent: document, selector: '.edit-view .element[data-type="block_participant"] a.make-responsible', event: 'click', func: path.onClickSetButtonMakeResponsible},
            { parent: document, selector: '.edit-view .element[data-type="block_participant"] a.remove-responsible', event: 'click', func: path.onClickSetBtnRemoveResponsible},
            { parent: document, selector: '.element[data-type="block_participant"] .element[data-type="select"] .element[data-type="block-card-responsible"] .element[data-type="participant"]', event: 'click', func: path.onClickGetHtmlPaticipantListItemAsResponsible},
            { parent: document, selector: '.element[data-type="block_button"] .element[data-type="button"] .element[data-type="button_subscription"]', event: 'click', func: path.onClickSetButtonSubscription},
        ]

        Global.addEvents(this._events, {
            instance: this
        });

        return this;
    },


    // actions - методы-действия для эвентов
    actions: {

        //onClickSetButtonMakeResponsible - сделать ответственным
        onClickSetButtonMakeResponsible : function(e){
            Participant.setButtonMakeResponsible(this);
            $(this).closest('.participant').removeClass('open');
        },

        //onClickSetBtnRemoveResponsible - убрать ответственного
        onClickSetBtnRemoveResponsible : function(e){
            Participant.setBtnRemoveResponsible(this);
        },

        // onClickGetHtmlPaticipantListItemAsResponsible - выбираем ответственного - для ProcessView
        onClickGetHtmlPaticipantListItemAsResponsible : function(e){
            Participant.getHtmlPaticipantListItemAsResponsible(this);
        },

        // onClickSetButtonSubscription - выбираем ответственного - для ProcessView
        onClickSetButtonSubscription : function(e){
            Participant.setSubscription(this);
        },

        //onClickSelectedItemList
        onClickSelectedItemList : function(e){
            var _this = this;

            if(Participant.getParticipantBlockType(_this) == 'list_view') {
                var $blPartici = $(this).closest('.element[data-type="block_participant"]'),
                    thoff = $blPartici.offset(),
                    topnew = thoff.top + 29;

                $blPartici.find('ul').css({ top:''+topnew+'px', left:''+thoff.left+'px'});
            }

            Participant.showSelectedItemList(this);
        },

        //onClickItemListAddParticipant
        onClickItemListAddParticipant : function(e){
            var _this = this

            $(_this).closest('.element[data-type="drop_down"]').find('.b-clone-data .table').remove(); // It is clear old clone data

            var post_data = Participant.getItemListAjaxPostData(_this);
            post_data.type_item_list = Participant.TYPE_ITEM_PARTICIPANT;

            Participant.getItemList(_this, post_data, true);
        },

        //onClickItemListAddParticipantButton
        onClickItemListAddParticipantButton : function(e){
            var _this = this

            $(_this).closest('.element[data-type="block_participant"]').find('.b-clone-data .table').remove(); // It is clear old clone data

            var post_data = Participant.getItemListAjaxPostData(_this);
            post_data.type_item_list = Participant.TYPE_ITEM_PARTICIPANT;

            Participant.getItemList(_this, post_data, true);
        },

        //onClickItemListAddEmail
        onClickItemListAddEmail : function(e){
            var _this = this

            $(_this).closest('.element[data-type="drop_down"]').find('.b-clone-data .table').remove(); // It is clear old clone data

            var post_data = Participant.getItemListAjaxPostData(_this);
            post_data.type_item_list = Participant.TYPE_ITEM_EMAIL;

            Participant.getItemList(_this, post_data, true);
        },

        //onClickItemListAddEmailButton
        onClickItemListAddEmailButton : function(e){
            var _this = this

            $(_this).find('.b-clone-data .table').remove(); // It is clear old clone data

            var post_data = Participant.getItemListAjaxPostData(_this);
            post_data.type_item_list = Participant.TYPE_ITEM_EMAIL;

            Participant.getItemList(_this, post_data, true);
        },

        //onClickAddSelectedParticipant - добавляем выбранного участника
        onClickAddSelectedParticipant : function(e){
            e.stopPropagation();

            Participant.addSelectedParticipant(this);
        },

        //onClickSwitchItemList
        onClickSwitchItemList : function(e){
            e.stopPropagation();

            var _this = this,
                $this = $(_this);

            $this.closest('.element[data-type="drop_down"]').find('.b-clone-data .table').remove(); // It is clear old clone data

            var post_data = Participant.getItemListAjaxPostData(_this);
            post_data.type_item_list = $this.data('type_item_list');

            Participant.getItemList(_this, post_data, false, function(){
                $this.removeClass('active');
            });

            DropDownListObj
                .createInstance()
                .setGroupData(DropDownListObj.GROUP_DATA_SDM_OPTION_LIST)
                .run($('.edit-view:visible .participant[data-type="select"]:not(.hide)'));
        },

        //onKeyUpEnterAddNewItemEmail
        onKeyUpEnterAddNewItemEmail: function (e) {
            $(this).closest('.search-section').removeClass('b-error');

            if (e.keyCode == 13) {
                Participant.addNewItemEmail(this);
            }
        },

        //onClickAddNewItemEmail
        onClickAddNewItemEmail : function(e) {
            e.preventDefault();

            Participant.addNewItemEmail(this);
            return false;
        },

        //onClickRemoveSelectedItemIcon
        onClickRemoveSelectedItemIcon : function(e){
            e.stopPropagation();

            Participant.removeSelectedItemIcon(this)
        },

        //onClickRemoveSelectedItemList
        onClickRemoveSelectedItemList : function(e){
            e.stopPropagation();

            Participant.removeSelectedItemList(this)
        },

        //onDropDownHide
        onDropDownHide : function(){
            if (Global.browser.isFirefox()) return false;

            Participant.remove();
        },

    },


    //getCopyId
    getCopyId : function(_this){
        var copy_id = null;
        if(!_this || !$(_this).length){
            return null;
        }

        if(Participant.getParticipantBlockType(_this) == 'edit_view'){
            var element = $(_this).closest('.edit-view');
            if(element){
                copy_id = element.data('copy_id');
            }
        } else if(Participant.getParticipantBlockType(_this) == 'list_view'){
            copy_id = $(_this).closest('.sm_extension').data('copy_id');
        }

        return copy_id;
    },

    //getDataId
    getDataId : function(_this){
        var data_id = null;
        if(!_this || !$(_this).length){
            return null;
        }

        if(Participant.getParticipantBlockType(_this) == 'edit_view'){
            var element = $(_this).closest('.edit-view');
            if(element){
                data_id = element.data('id');
            }
        } else if(Participant.getParticipantBlockType(_this) == 'list_view'){
            data_id = $(_this).closest('.sm_extension_data').data('id');
        }

        return data_id;
    },


    removeFromStore : function ($place, json) {
        if (typeof json != 'object') {
            $place.find('[data-email_id="'+ json +'"]').remove();
        } else {
            $place.find('[data-ug_id="'+json.ug_id+'"][data-ug_type="' + json.ug_type + '"]').remove();
        }

        return this;
    },

    //getTypeItemlList
    getTypeItemlList : function(_this){
        return $(_this).closest('.element[data-type="select"]').data('type_item_list');
    },

    //getParticipantBlockType - возвращает тип блока Participant
    getParticipantBlockType : function(_this){
        return $(_this).closest('.element[data-type="block_participant"]').data('block_type');
    },
    
    //setButtonMakeResponsible - сделать ответственным
    setButtonMakeResponsible : function(_this){
        //all
        var participant = $(_this).closest('.element[data-type="block_participant"]');
        participant.find('.element[data-type="participant"]').each(function(i, ul){
            $(ul).removeClass('active').data('responsible', '0'); 
            $(ul).find('.remove-responsible').addClass('hide');
            $(ul).find('.make-responsible').removeClass('hide');
        });
        
        //this
        var card = $(_this).closest('.element[data-type="participant"]');
        card.addClass('active');
        card.data('responsible', '1'); 
        card.find('.remove-responsible').removeClass('hide');
        $(_this).addClass('hide');
    },
    
    //setBtnRemoveResponsible - убрать ответственного
    setBtnRemoveResponsible : function(_this){
        //this
        var card = $(_this).closest('.element[data-type="participant"]');
        card.removeClass('active').data('responsible', '0');;
         
        card.find('.make-responsible').removeClass('hide');
        $(_this).addClass('hide');
    },


    //getItemListAjaxPostData
    getItemListAjaxPostData : function(_this){
        var $this = $(_this),
            post_vars = {},
            exception_list_id = [];

        var post_data = {
            'type_item_list' : $this.closest('.element[data-type="select"]').data('type_item_list')
        }


        post_vars['exception_list_id'] = {};

        // card - participant
        $this.closest('.element[data-type="block_participant"]').find('.element[data-type="select"][data-type_item_list="selected_item_list"] .element[data-type="block-card-participant"] .element[data-type="participant"]').each(function(i, ul){
            exception_list_id.push({
                'ug_id' : $(ul).data('ug_id'),
                'ug_type' : $(ul).data('ug_type'),
            })
        })
        if(!$.isEmptyObject(exception_list_id)){
            post_vars['exception_list_id']['participant'] = JSON.stringify(exception_list_id);
            exception_list_id = [];
        }

        // card - email
        $(_this)
            .closest('.element[data-type="block_participant"]')
            .find('.element[data-type="select"][data-type_item_list="selected_item_list"] [data-type="drop_down_list"] .element[data-type="block-card-participant"] .element[data-type="email"]').each(function(i, ul){
            exception_list_id.push($(ul).data('email_id'));
        })
        if(!$.isEmptyObject(exception_list_id)){
            post_vars['exception_list_id']['email'] = JSON.stringify(exception_list_id);
        }

        switch (Participant.getParticipantBlockType(_this)){
            case 'edit_view': {
                var edit_view = $this.closest('.edit-view');
                if(edit_view) {
                    post_vars['copy_id'] = edit_view.data('copy_id');
                    post_vars['data_id'] = edit_view.data('id');
                    post_vars['pci'] = edit_view.data('pci');
                    post_vars['pdi'] = edit_view.data('pdi');

                    // for process
                    if(dinamic_copy_id = edit_view.find('.element[data-type="drop_down"] .element[data-type="drop_down_button"].element_module').data('id')){
                        post_data['dinamic_copy_id'] = dinamic_copy_id;
                    }
                }

                break;
            }
            case 'list_view': {
                post_vars['copy_id'] = $('.sm_extension').data('copy_id');
                post_vars['data_id'] = $this.closest('.sm_extension_data[data-controller="edit_view"], .sm_extension_data[data-controller="process_view_edit"]').data('id');
                post_vars['pci'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id');
                post_vars['pdi'] = $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id');
                post_vars['show_responsible'] = 1;

                break;
            }
            default: break;
        }


        if(!post_data.vars){
            post_data.vars = post_vars;
        } else {
            for(key in post_vars){
                post_data.vars[key] = post_vars[key];
            }
        }

        return post_data;
    },

    //issetSelectBlockByTypeItemList
    issetSelectBlockByTypeItemList : function(_this, type_item_list){
        return $(_this).closest('.element[data-type="block_participant"]').find('.element[data-type="select"]').is('[data-type_item_list="'+type_item_list+'"]');
    },

    //prepareItemListReturnData
    prepareItemListReturnData : function(data){
        Participant.prepareItemListReturnDataForProcess(data);
    },

    //prepareItemListReturnDataForProcess
    prepareItemListReturnDataForProcess : function(data){
        if(data.without_participant_const){
            var html = $(data.html);
            html.find('.element[data-type="participant"][data-ug_type="const"]').remove();
            data.html = html;
        }
    },

    //getItemList  - Загрузка с сервера и показ списка для добавления новых участников
    getItemList : function(_this, post_data, refresh, callback){
        var refresh = refresh || false;

        if(refresh == false){
            //switch blocks
            var isset_block = Participant.issetSelectBlockByTypeItemList(_this, post_data.type_item_list);
            if(isset_block){
                Participant.switchToItemList(_this, post_data.type_item_list);
                return;
            }

            refresh = true;
        }

        // load block
        if(refresh){
            return functionReload();
        }

        //function Reload
        function functionReload(){
            //loadItemList
            Participant.loadItemList(_this, post_data, function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else if(data.status == true){
                    var to_remove = Participant.issetSelectBlockByTypeItemList(_this, post_data.type_item_list);


                    Participant.prepareItemListReturnData(data);

                    $(_this).closest('.element[data-type="select"]').after(data.html).addClass('hide').removeClass('open');
                    $(_this).closest('.element[data-type="select"]').next().addClass('opened');

                    if(to_remove){
                        $(_this).closest('.element[data-type="select"]').remove();
                    }


                    if(typeof callback == 'function'){
                        callback()
                    }

                    niceScrollCreate($('.submodule-table'));
                }
            });
        }

    },

    //loadItemList - Загрузка с сервера списка участников
    loadItemList : function(_this, post_data, callback){
        $.ajax({
            url : Global.urls.url_participant_get_item_list + '/' + Participant.getCopyId(_this),
            data : post_data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                callback(data)
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },


    //addSelectedParticipant
    addSelectedParticipant : function(_this){
        var block_type = Participant.getParticipantBlockType(_this);
        var type_item_list = Participant.getTypeItemlList(_this);

        //TYPE_ITEM_PARTICIPANT
        switch (type_item_list) {
            case Participant.TYPE_ITEM_PARTICIPANT: {
                if(block_type == 'edit_view'){
                    if($(_this).attr('data-ug_id') == $('.element[data-type="block_button"] .element[data-type="button_subscription"]').data('ug_id')){
                        Participant.setSubscription('.element[data-type="block_button"] .element[data-type="button"] .element[data-type="button_subscription"]', true);
                    }
                    Participant.getHtmlSelectedIconItemParticipant(_this);
                } else {
                    var participant = $(_this).closest('.element[data-type="block_participant"]'),
                        offset = participant.offset(),
                        top_new = offset.top + 25;

                    participant.find('ul').css({top: '' + top_new + 'px', left: '' + offset.left + 'px'});
                }

                Participant.getHtmlSelectedPaticipantListItem(_this, Participant.TYPE_ITEM_PARTICIPANT);

                $(_this).closest('.element[data-type="participant"]').remove();

                break;
            }
            //TYPE_ITEM_EMAIL
            case Participant.TYPE_ITEM_EMAIL: {

                Participant.hasParticipantUserByEmailId(_this, function(data){
                    if(data.status == true && data.participan_list){
                        var skip = 0;
                        _this_p = $(_this).closest('.edit-view').find('.element[data-type="block"] .element[data-type="block_participant"]:eq(0)');
                        $.each(data.participan_list, function(i, participant_data){
                            if(_this_p.find('.element[data-type_item_list="selected_item_list"] .element[data-type="block-card-participant"] .element[data-type="participant"][data-ug_id="' + participant_data.users_id + '"][data-ug_type="user"]').length){
                                skip++;
                                return true;
                            } else {
                                messages = null;
                                var post_vars = {
                                    'ug_id': participant_data.users_id,
                                    'ug_type': 'user',
                                }
                                Participant.getHtmlSelectedIconItemParticipant(_this_p, post_vars);
                                Participant.getHtmlSelectedPaticipantListItem(_this_p, Participant.TYPE_ITEM_PARTICIPANT, post_vars);
                                Participant.removeFromStore($(_this).closest('[data-type="drop_down_list"]').next().filter('.b-clone-data'), parseInt($(_this).attr('data-email_id')));
                                $(_this).closest('.element[data-type="email"]').remove();
                            }
                        });

                        if(skip && skip === data.participan_list.length){
                            Message.show([{
                                'type': 'error',
                                'message': 'A participant with this email address is already selected'
                            }], true);
                        }
                    } else {
                        if(block_type == 'edit_view'){
                            Participant.getHtmlSelectedIconItemEmail(_this);
                        }

                        Participant.getHtmlSelectedPaticipantListItem(_this, Participant.TYPE_ITEM_EMAIL);
                        Participant.removeFromStore($(_this).closest('[data-type="drop_down_list"]').next().filter('.b-clone-data'), parseInt($(_this).attr('data-email_id')));
                        $(_this).closest('.element[data-type="email"]').remove();
                    }
                })

                break;
            }
            default: break;
        }

        TableSearchInit('.submodule-table', '.submodule-search');
    },

    //hasParticipantUserByEmailId
    hasParticipantUserByEmailId : function(_this, callback){
        if(!post_vars){
            var post_vars = {
                'email_id': $(_this).data('email_id'),
            };
        }
        var data = {
            'vars' : post_vars,
        };

        $.ajax({
            url : Global.urls.url_participant_has_participant_user_by_email_id + '/' + Participant.getCopyId(_this),
            data : data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                callback(data);
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });




    },


    // showSelectedItemList - показ списка добавленных участников
    showSelectedItemList : function(_this){
        var $this = $(_this);

        $this.closest('.participants.element[data-type="drop_down"]').find('.b-clone-data .table').remove(); // It is clear old clone data
        $this.closest('[data-type="drop_down"]').find('.submodule-search').off('keyup');
        $this.closest('.element[data-type="block_participant"]')
            .find('.element[data-type="select"].hide')
            .not('.list-participants')
            .not('[data-type_item_list="email"]')
            .removeClass('hide')
            .addClass('opened')
            .closest('.element[data-type="select"]')
            .find('.submodule-search').val('').trigger('keyup');

        Participant.remove();
    },


    //addResponsibleIfNotExist - устанавливаем при добавлении ответственым залогиненого пользователя
    addResponsibleIfNotExist : function(){
        $('.edit-view').each(function(){
            var edit_view = $(this);
            if(edit_view.data('id') === ""){

                var participant = edit_view.find('.element[data-type="block_participant"]');

                if(participant && !participant.find('.participants .element[data-type="block-card"] .element').length){

                    var subscription = edit_view.find('.element[data-type="block_button"] .element[data-type="button"] .element[data-type="button_subscription"]');
                    if(subscription && subscription.length){

                        var user_id = $('.element[data-type="block_button"] .element[data-type="button_subscription"]').data('ug_id');

                        Participant.setSubscription(subscription, false, function(){
                            participant.find('.element[data-type="block-card"]').find('.element[data-type="participant"]').each(function(){
                                if($(this).data('ug_id') == user_id){
                                    var responsible_link = $(this).find('.make-responsible');
                                    if(responsible_link) {
                                        Participant.setButtonMakeResponsible(responsible_link);
                                    }
                                }
                            });
                        });
                    } else {
                        var user_id = participant.data('ug_id');

                        participant.find('.element[data-type="select"]:eq(1)').find('.element[data-type="participant"].add').each(function(){

                            if($(this).data('ug_id') == user_id){
                                var _this = this;
                                Participant.getHtmlSelectedIconItemParticipant(_this, null, function(){
                                    Participant.getHtmlSelectedPaticipantListItem(_this, Participant.TYPE_ITEM_PARTICIPANT, null, function(){

                                        participant.find('.element[data-type="block-card"]').find('.element[data-type="participant"]').each(function(){

                                            if($(this).data('ug_id') == user_id){

                                                var responsible_link = $(this).find('.make-responsible');
                                                if(responsible_link) {
                                                    Participant.setButtonMakeResponsible(responsible_link);
                                                    Participant.remove();
                                                }
                                            }
                                        });
                                    });
                                });
                            }
                        });
                    }
                }

                var participant_related = edit_view.find('.buttons-block .element_relate_participant');
                if(participant_related &&  participant_related.length && participant_related.data('ug_id') === ""){

                    var user_id = participant_related.data('u_id');
                    participant_related.closest('.element').find('.sm_extension_data').each(function(){

                        if($(this).data('ug_id') == user_id){
                            participant_related.html($(this).find('td').html());
                            participant_related.data('ug_id', $(this).data('ug_id'));
                        }
                    });
                }
            }
        });
    },

    //isExistResponsible - проверяем или есть ответственый
    isExistResponsible : function(_this){
        var edit_view = $(_this).closest('.edit-view');
        var participant = edit_view.find('.element[data-type="block_participant"]');
        if(participant && participant.length){
            if(!participant.find('.participants .element[data-type="block-card"] .element.active').length){
                return false;
            }
        }

        var participant_related = edit_view.find('.buttons-block .element_relate_participant');
        if(participant_related && participant_related.length){
           if(!participant_related.data('ug_id')){
               return false;
           }
        }

        return true;
    },

    // getChangeResponsible
    getChangeResponsible : function(_this){
        var block_participant = $(_this).closest('.element[data-type="block_participant"]');
        if(!block_participant){
            return true;
        }

        if(block_participant.data('block_type') != 'edit_view'){
            return true;
        }

        return block_participant.data('change_responsible');
    },

    //getHtmlSelectedIconItemParticipant - устанавливаем одну каточку в блок  participant
    getHtmlSelectedIconItemParticipant : function(_this, post_vars, callback){
        var _data,
            $this = $(_this),
            copy_id = Participant.getCopyId(_this);

        if(!post_vars){
            post_vars = {
                'ug_id': $this.data('ug_id'),
                'ug_type': $this.data('ug_type')
            }
        }

        post_vars['change_responsible'] = Participant.getChangeResponsible(_this);
        post_vars['copy_id'] = copy_id;
        post_vars['data_id'] = Participant.getDataId(_this);

        _data = {
            'vars' : post_vars,
            'type_item_list' : Participant.TYPE_ITEM_PARTICIPANT
        };

        $.ajax({
            url : Global.urls.url_participant_get_selected_icon_item + '/' + copy_id,
            data : _data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else if(data.status == true){
                    var place = $(_this).closest('.element[data-type="block_participant"]').find('.element[data-type="block-card"]'),
                        element = place.find('.participant').first();

                    Participant.removeFromStore($this.closest('.participant').find('.b-clone-data'), _data.vars);

                    if($(data.html).is('[data-ug_type="group"], [data-ug_type="const"]') && element.length){
                        if (!$(data.html).is('[data-ug_type="const"]')) {
                            //only red
                            var _elementConst = $('[data-type="block-card"] [data-ug_type="const"]').last();

                            if (_elementConst.length) {
                                _elementConst.after(data.html)
                            } else place.find('>span').first().before(data.html);
                        } else place.find('>span').first().before(data.html);
                    } else {
                        place.append(data.html);
                    }

                    if(callback){
                        callback();
                    }
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },

    //getHtmlSelectedIconItemEmail - устанавливаем одну каточку-email в блок  participant
    getHtmlSelectedIconItemEmail : function(_this, post_vars, callback){
        if(!post_vars){
            var post_vars = {
                'email_id': $(_this).data('email_id'),
            };
        }
        var data = {
            'vars' : post_vars,
            'type_item_list' : 'email'
        };

        $.ajax({
            url : Global.urls.url_participant_get_selected_icon_item + '/' + Participant.getCopyId(_this),
            data : data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else if(data.status == true){
                    var place = $(_this).closest('.element[data-type="block_participant"]').find('.element[data-type="block-card"]:eq(0)');
                    place.append(data.html);

                    if(callback){
                        callback();
                    }
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },


    //getHtmlSelectedIconItemForUser - устанавливаем одну каточку в блок participant текущего пользователя
    getHtmlSelectedIconItemForUser : function(_this, callback){
        var copy_id = $(_this).closest('.edit-view').data('copy_id');

        var _data = {
            'copy_id' : copy_id,
            'data_id' : $(_this).closest('.edit-view').data('id')
        }

        $.ajax({
            url : Global.urls.url_participant_get_selected_icon_item_for_user + '/' + copy_id,
            type : 'POST',
            async: false,
            dataType: "json",
            data : _data,
            success: function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else if(data.status == true){
                    $(_this)
                        .closest('.edit-view')
                        .find('.element[data-type="block_participant"]:eq(0)')
                        .find('.element[data-type="block-card"]')
                        .append(data.html_first);

                    $(_this)
                        .closest('.edit-view')
                        .find('.element[data-type="block_participant"]:eq(0)')
                        .find('.element[data-type="select"]')
                        .find('.element[data-type="block-card-participant"]')
                        .append(data.html_second);

                    /*
                    $(_this)
                        .closest('.element[data-type="select"]')
                        .find('.element[data-type="block_participant"]')
                        .find('.element[data-type="select"]:eq(0)')
                        .find('.element[data-type="block-card-participant"] tbody')
                        .append(data.html_first);
                        */

                    //Participant.deleteSecondSelectBlock(_this);

                    if(callback){
                       callback();
                    }
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },


    //getHtmlSelectedPaticipantListItem - устанавливаем одну каточку в блок participant select
    getHtmlSelectedPaticipantListItem : function(_this, type_item_list, post_vars, callback){
        var _this = _this;
        if(!post_vars){
            // participant
            if(type_item_list == Participant.TYPE_ITEM_PARTICIPANT){
                var post_vars = {
                    'ug_id': $(_this).data('ug_id'),
                    'ug_type': $(_this).data('ug_type'),
                };

                if(Participant.getParticipantBlockType(_this) == 'list_view'){
                    post_vars['copy_id'] = $('.sm_extension').data('copy_id');
                    post_vars['data_id'] = $(_this).closest('.sm_extension_data[data-controller="edit_view"], .sm_extension_data[data-controller="process_view_edit"]').data('id');
                    post_vars['save_entity'] = 1;
                }
            } else
            // email
            if(type_item_list == Participant.TYPE_ITEM_EMAIL){
                var post_vars = {
                    'email_id': $(_this).data('email_id'),
                };
            }
        }

        post_vars['copy_id'] = Participant.getCopyId(_this);
        post_vars['data_id'] = Participant.getDataId(_this);

        var post_data = {
            'vars' : post_vars,
            'type_item_list' : type_item_list
        }

        $.ajax({
            url : Global.urls.url_participant_get_selected_list_item  + '/' + Participant.getCopyId(_this),
            data : post_data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else if(data.status == true){
                    var $element = $([]),
                        $place = $(_this)
                            .closest('.element[data-type="block_participant"]')
                            .find('.element[data-type_item_list="selected_item_list"]')
                            .find('.element[data-type="block-card-participant"]');

                    if (!$place.find('tbody').length) {
                        $place.append('<tbody></tbody>')
                    } else {
                        $element = $place.find('tr');
                    }
                    $place = $place.find('tbody');

                    if ($(data.html).is('[data-ug_type="group"], [data-ug_type="const"]') && $element.length) {
                        if ($(data.html).is('[data-ug_type="const"]')) {
                            var record = $element.filter('[data-ug_type="const"]').last();
                            if (record.length) {
                                record.after(data.html);
                            }
                        } else {
                            var items = $element.filter('[data-ug_type="const"]').last();
                            if (items.length) {
                                items.after(data.html);
                            } else $element.first().before(data.html);
                        }
                    } else {
                        $place.append(data.html);
                    }

                    if(callback){
                       callback();
                    }
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },




    //getHtmlPaticipantListItemAsResponsible - устанавливаем выбраного сотрудника как ответственного
    getHtmlPaticipantListItemAsResponsible : function(_this){
        var post_data = {
                'ug_id' : $(_this).data('ug_id'),
                'ug_type' : $(_this).data('ug_type')
        };

        if(Participant.getParticipantBlockType(_this) == 'list_view'){
            post_data['copy_id'] = $('.sm_extension').data('copy_id');
            post_data['data_id'] = $(_this).closest('.sm_extension_data[data-controller="edit_view"], .sm_extension_data[data-controller="process_view_edit"], .sm_extension_data.navigation_module_link_child_pv').data('id');
        } 

        $.ajax({
            url : Global.urls.url_participant_get_list_item_as_responsible + '/' + Participant.getCopyId(_this),
            data : post_data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data.status == false){
                    Message.show(data.messages, false);
                } else
                if(data.status == true){
                    $(_this).closest('.element[data-type="block_participant"]').find('.element[data-type="select"]').children('button').after(data.html).remove();
                      
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },


    //checkAndClearSelectedItemIcon_TypeConst - поиск и удаление
    checkAndClearSelectedItemIcon_TypeConst : function(_this){
        var edit_view = $(_this).closest('.edit-view');
        if(!edit_view.length){
            return;
        }

        var drop_down = $(_this).closest('.element[data-type="drop_down"]');
        if(!drop_down.find('.element[data-type="drop_down_button"].element_module').length){
            return;
        }

        var block_participant = edit_view.find(
                                    '.element[data-type="block_participant"] .element[data-type="block-card"] .element[data-type="participant"][data-ug_type="const"],' +
                                    '.element[data-type="block_participant"] .element[data-type="select"][data-type_item_list="selected_item_list"] .element[data-type="participant"][data-ug_type="const"]'
                                );
        if(!block_participant.length){
            return;
        }

        var copy_id = drop_down.find('.element[data-type="drop_down_button"]').data('id');
        if(!copy_id){
            return;
        }

        // callback
        var callback_function = function(data, block_participant){
            if(!data.status){
                block_participant.each(function(i, ul){
                    $(ul).remove();
                })
            }
        };

        // ajax
        $.ajax({
            url : Global.urls.url_participant_has_participant + '/' + copy_id,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                callback_function(data, block_participant);
            },
        });
    },


    //removeSelectedItemIcon - удалить выбранную карточку (блок иконок)
    removeSelectedItemIcon : function(_this){
        var element_participant = $(_this).closest('.participant');
        var type_item_list = element_participant.data('type');

        if(type_item_list == Participant.TYPE_ITEM_PARTICIPANT){
            var button_subscription = $(_this).closest('.edit-view').find('.element[data-type="block_button"] .element[data-type="button_subscription"]');


            $(_this)
                .closest('.element[data-type="block_participant"]')
                .find('.element[data-type="select"] .element[data-ug_id="' + element_participant.attr('data-ug_id') + '"][data-ug_type="' + element_participant.attr('data-ug_type') + '"]')
                .remove();

            //setSubscription
            if(button_subscription && element_participant && element_participant.attr('data-ug_type') == 'user' && element_participant.attr('data-ug_id') == button_subscription.attr('data-ug_id')){
                Participant.setSubscription(button_subscription);
            }
        } else
        if(type_item_list == Participant.TYPE_ITEM_EMAIL){
            $(_this)
                .closest('.element[data-type="block_participant"]')
                .find('.element[data-type="select"] .element[data-email_id="' + element_participant.attr('data-email_id') + '"]')
                .remove();
        }

        element_participant.remove();
    },



    //removeSelectedItemList - удалить выбранную карточку (выпадающий список)
    removeSelectedItemList : function(_this){
        var type_item_list = $(_this).closest('.sm_extension_data.element').data('type');
        var element_select = $(_this).closest('.element[data-type="block_participant"]').find('.element[data-type="select"][data-type_item_list="selected_item_list"]');

        // remove item
        if(type_item_list == Participant.TYPE_ITEM_PARTICIPANT){
            var button_subscription = $(_this).closest('.edit-view').find('.element[data-type="block_button"] .element[data-type="button_subscription"]');
            var sm_extension_data = $(_this).closest('.sm_extension_data.element');

            $(_this)
                .closest('.element[data-type="block_participant"]')
                .find('.element[data-type="block-card"], .element[data-type="block-card-participant"]')
                .find('.element[data-type="participant"][data-ug_id="' + $(_this).closest('.element[data-type="participant"]').data('ug_id') + '"][data-ug_type="' + $(_this).closest('.element[data-type="participant"]').data('ug_type') + '"]')
                .remove();

            //setSubscription
            if(button_subscription && sm_extension_data && sm_extension_data.attr('data-ug_type') == 'user' && sm_extension_data.attr('data-ug_id') == button_subscription.attr('data-ug_id')){
                Participant.setSubscription(button_subscription);
            }
        } else
        if(type_item_list == Participant.TYPE_ITEM_EMAIL){
            $(_this)
                .closest('.element[data-type="block_participant"]')
                .find('.element[data-type="block-card"], .element[data-type="block-card-participant"]')
                .find('.element[data-type="email"][data-email_id="' + $(_this).closest('.element[data-type="email"]').data('email_id') + '"]')
                .remove();
        }

        element_select.find('.dropdown-menu .submodule-table').getNiceScroll().resize();
    },
    
    //setUnSubscription - отписаться
    setUnSubscription : function(_this){
        $(_this)
            .closest('.edit-view')
            .find('.element[data-type="block_participant"]')
            .find('.element[data-type="block-card"], .element[data-type="block-card-participant"]')
            .find('.element[data-type="participant"][data-ug_id="'+$(_this).data('ug_id')+'"][data-ug_type="user"]').remove();
        //Participant.deleteSecondSelectBlock(_this);
    },

    //setSubscription -  кнопка: подписаться/отписаться
    setSubscription : function(_this, no_action, callback){
        var value = $(_this).val();
        switch(value){
            case "0" :
                $(_this)
                    .val('1') // отметка о подписке
                    .text(Message.translate_local('Unsubscribe'));
                if(!no_action) {
                    Participant.getHtmlSelectedIconItemForUser(_this, callback);
                }
                break;
            case "1" : 
                $(_this)
                    .val('0') // отметка об отписке
                    .text(Message.translate_local('Subscribe'));
                if(!no_action) {
                    Participant.setUnSubscription(_this);
                }
                break;
            
        }
    },

    // switchToItemList переключение списка Участники|Email
    switchToItemList : function(_this, type_item_list){
        var block_participant =  $(_this).closest('.element[data-type="block_participant"]');

        var select = $(_this).closest('.element[data-type="select"]');

        if(select.data('type_item_list') == type_item_list){
            return;
        }

        block_participant
            .find('.element[data-type="select"]:not([data-type_item_list="'+type_item_list+'"])')
            .addClass('hide')
            .removeClass('open');

        block_participant
            .find('.element[data-type="select"][data-type_item_list="'+type_item_list+'"]')
            .removeClass('hide')
            .addClass('opened');

        return this;
    },



    //saveItemEmail
    saveItemEmail : function(_this, post_vars, callback){
        var post_data = {
            'vars' : post_vars,
            'type_item_list' : Participant.TYPE_ITEM_EMAIL
        }

        $.ajax({
            url : Global.urls.url_participant_save_item_email + '/' + Participant.getCopyId(_this),
            data : post_data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                callback(data);
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
    },

    //validateNewItemEmail
    validateNewItemEmail : function(_this){
        var result = true;
        var search_section = $(_this).closest('.search-section'),
            value = search_section.find('.submodule-search').val();

        if(!value || !value.length){
            search_section.addClass('b-error');
            result = false;
        } else {
            search_section.removeClass('b-error');
        }

        return result;
    },

    //addNewItemEmail
    addNewItemEmail : function(_this){
        if(Participant.validateNewItemEmail(_this) == false){
            return;
        }

        var search_section = $(_this).closest('.search-section');
        var submodule_search = search_section.find('.submodule-search');

        // switch
        switch (Participant.getTypeItemlList(_this)){

            //TYPE_ITEM_EMAIL
            case Participant.TYPE_ITEM_EMAIL: {
                var post_vars,
                    email = submodule_search.val();

                post_vars = {
                    'email' : email,
                    'exception_list_id' : {}
                };

                // exception_list_id
                var exception_list_id = [];
                $(_this)
                    .closest('.element[data-type="block_participant"]')
                    .find('.element[data-type="select"][data-type_item_list="selected_item_list"] [data-type="drop_down_list"] .element[data-type="block-card-participant"] .element[data-type="email"]')
                    .each(
                        function(i, ul){
                            exception_list_id.push($(ul).data('email_id'));
                        });

                if(!$.isEmptyObject(exception_list_id)){
                    post_vars['exception_list_id']['email'] = JSON.stringify(exception_list_id);
                }

                // save
                Participant.saveItemEmail(_this, post_vars, function(data){
                    var $dropDownList = $(_this).closest('[data-type="drop_down_list"]');

                    if(data.status == false){
                        search_section.addClass('b-error');
                        return;
                    }

                    clearSelectedItems = function(){
                        Participant.removeFromStore($(_this).closest('.participant').find('.b-clone-data'), email);

                        if(data.email_id){
                            // remove selected element
                            $dropDownList.find('.sm_extension_data[data-email_id="' + data.email_id + '"]').remove();
                        }

                        // clear
                        submodule_search.val('');
                    }


                    // if return user-participant
                    if(data.users_id){
                        _this_p = $(_this).closest('.edit-view').find('.element[data-type="block"] .element[data-type="block_participant"]:eq(0)');
                        if(_this_p.find('.element[data-type_item_list="selected_item_list"] .element[data-type="block-card-participant"] .element[data-type="participant"][data-ug_id="'+data.users_id+'"][data-ug_type="user"]').length){
                            $(_this).closest('.element[data-type="select"]').find('.search-section').addClass('b-error');
                            return;
                        } else {
                            clearSelectedItems();

                            var post_vars = {
                                'ug_id': data.users_id,
                                'ug_type': 'user',
                            }
                            Participant.getHtmlSelectedIconItemParticipant(_this_p, post_vars);
                            Participant.getHtmlSelectedPaticipantListItem(_this_p, Participant.TYPE_ITEM_PARTICIPANT, post_vars);

                        }
                    } else
                    // if return email-participant
                    if(data.email_id){
                        clearSelectedItems();

                        var post_vars = {
                            'email_id': data.email_id
                        };

                        // block 1 (icon)
                        Participant.getHtmlSelectedIconItemEmail(_this, post_vars);

                        // block 2 (list)
                        Participant.getHtmlSelectedPaticipantListItem(_this, Participant.TYPE_ITEM_EMAIL, post_vars);
                    }


                    $dropDownList.find('.submodule-table tr').show();
                });

                break;
            }
        }
    },

    getParticipantList : function($edit_view, block_participant_type_list){
        var block_participant = {};

        for(var i=0; i <block_participant_type_list.length; i++){
            var data_type = block_participant_type_list[i];
            var data_participant;

            if(data_type == 'participant'){
                data_participant = []
                $edit_view.find('.element[data-type="block_participant"] .element[data-type="block-card"]>.element[data-type="' + data_type + '"]').each(function(i, ul){
                    data_participant.push({
                        'ug_id' : $(ul).data('ug_id'),
                        'ug_type' : $(ul).data('ug_type')
                    });
                });
            } else
            //email
            if(data_type == 'email'){
                data_participant = []
                $edit_view.find('.element[data-type="block_participant"] .element[data-type="block-card"]>.element[data-type="' + data_type + '"]').each(function(i, ul){
                    data_participant.push({
                        'email_id' : $(ul).data('email_id'),
                    });
                });
            }

            if(data_participant){
                block_participant[data_type] = data_participant;
            }
        }
        return block_participant;
    },


    // clearEmailParticipantIfExistsInCommunications
    clearEmailParticipantIfExistsInCommunications : function(_this, callback){
        var _this = _this;
        var $edit_view = $(_this).closest('.edit-view');
        var type_comment = $(_this).closest('.element[data-type="edit"]').attr('data-type_comment');
        var block_participant = null;

        switch(type_comment){
            case 'email' :{
                // block_attributes: block_participant and other
                block_participant = Participant.getParticipantList($edit_view, ['participant', 'email'])
            }
        }

        if(!block_participant){
            return callback(_this);
        }

        AjaxObj
            .createInstance()
            .setUrl(Global.urls.url_participant_find_exists_email_participant_in_communications +'/'+$edit_view.data('copy_id'))
            .setData({'vars' : {'block_participant' : block_participant}})
            .setAsync(true)
            .setDataType('json')
            .setType('POST')
            .setCallBackSuccess(function(data) {
                if(data.email_list){
                    $.each(data.email_list, function(i, email_id){
                        $edit_view.find('.element[data-type="block_participant"] .element[data-type="block-card"] > .element[data-type="email"][data-email_id="' + email_id + '"]').remove();
                        $edit_view.find('.participant.element[data-type="select"][data-type_item_list="selected_item_list"] .element[data-type="block-card-participant"] .element[data-type="email"][data-email_id="' + email_id + '"]').remove();
                    });
                }
                callback(_this);
            })
            .setCallBackError(function(jqXHR, textStatus, errorThrown){
                Message.showErrorAjax(jqXHR, textStatus);
            })
            .send()

    },


    // remove
    remove : function () {
        $('.element[data-type="block_participant"] .element[data-type="select"]+.element[data-type="select"]').remove();
        $('.element[data-type="block_participant"] .element[data-type="select"]').removeClass('hide');
    },

}





$(document).ready(function(){

    Participant.init();


    $(document).on('mouseover','body', function(){
        $('span.opened').removeClass('opened').addClass('open');
    });


});


