var ProcessViewBase = new function () {
    var start = '[data-copy_id="7"][data-parent_copy_id="10"] ';

    this.init = function(){
        var parent = this,
            processViewPanelHeader = {
            editing : false,

            setEditing : function(editing){
                if(editing == false){
                    if($('.panel-heading .process_view-save-input').length) return;
                }

                processViewPanelHeader.editing = editing;
            },

            addPanel : function(_this){
                ProcessView.addPanel(function(data){
                    if(data.status == 'access_error'){
                        Message.show(data.messages, false);
                    } else {
                        var process_view_panel = $(_this).closest('.sm_extension').find('.element[data-name="process_view_panel"]');
                        processViewPanelHeader.setEditing(false);
                        process_view_panel.append(data.html)
                        processViewPanelHeader.edit($('.process_view_block .element[data-name="process_view_panel"] .element[data-name="panel"]:last'));

                        ProcessView
                            .headerCheck(process_view_panel.find('.panel-heading:last .header_check'))
                            .initUiSort();

                        $('ul.process_list').width($('ul.process_list > li').length * 300 + 155);
                        ProcessView.initElements();
                        $(".process_list").sortable("destroy");
                        //processView.initUiSort();
                        EditView.hiddenBlocks();

                        $('.process_view_dnt-add_list').closest('.btn-group').appendTo('.process_list');
                    }
                });
            },


            savePanelTitle : function(sorting_list_id, fields_data_list, callback){
                var copy_id = $('.process_view_block.sm_extension').data('copy_id');

                $.ajax({
                    url: Global.urls.url_process_view_save_panel_title+ '/' + copy_id,
                    dataType: "json", type: "POST",
                    data : {
                        'sorting_list_id' : sorting_list_id,
                        'fields_data_list' : fields_data_list,
                        'pci' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id'),
                        'pdi' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id'),
                        'this_template' : $('.process_view_block.sm_extension').data('this_template'),
                        'finished_object' : ($('.process_view_block.sm_extension .element[data-type="finished_object"]').hasClass('active') ? 1 : 0)
                    },
                    success: function(data) {
                        if(data.status == false || data.status == 'access_error'){
                            if(typeof data.messages != 'undefined'){
                                Message.show(data.messages, false);
                            }
                        } else if(data.status == true){
                            callback(data);
                        }
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                    },
                });
            },

            getHtmlEditPanelTitle : function(fields_data, callback){
                var copy_id = $('.process_view_block.sm_extension').data('copy_id');
                $.ajax({
                    url: Global.urls.url_process_view_get_html_edit_panel_title + '/' + copy_id,
                    dataType: "json", type: "POST",
                    data : {
                        'fields_data' : fields_data,
                        'pci' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_copy_id'),
                        'pdi' : $('.list_view_block.sm_extension, .process_view_block.sm_extension').data('parent_data_id'),
                    },
                    success: function(data){
                        if(data.status == true){
                            callback(data)
                        } else if(data.status == false){
                            Message.show(data.messages, false);
                        }
                    },
                    error: function(){
                        Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
                    },
                }).done(function(){
                    $('.element[data-name="field_title"]+input[data-name="todo_list"]').focus();
                });
            },


            isEmptyPanelTitle : function(_this){
                var result = true,
                    selector = '.process_view_block .element[data-name="process_view_panel"] .element[data-name="panel"]';

                if(typeof _this != 'undefined' && _this){
                    selector = $(_this).closest('.element[data-name="panel"]');
                }

                $(selector).each(function(i, ul){
                    if(!$(ul).find('section.panel').data('unique_index')
                        || !$(ul).find('[data-field="todo_list"]').text().length){
                        result = false;
                        return false;
                    }
                });

                return result;
            },

            editEmptyPanelTitle : function(){
                $('.process_view_block .element[data-name="process_view_panel"] .element[data-name="panel"]').each(function(i, ul){
                    if(!$(ul).find('section.panel').data('unique_index')){
                        processViewPanelHeader.edit(ul);
                        return false;
                    }
                });
            },


            edit : function(_this){
                var field_title = $(_this).find('.element[data-name="field_title"]'),
                    fields_data = [];

                parent.getFieldId(field_title.children('.element[data-name="field_title_value"]'), fields_data);

                if($(_this).find('.panel-heading .process_view-save-input').length) return;

                processViewPanelHeader.getHtmlEditPanelTitle(fields_data, function(data_title){
                    field_title.css('display', 'none');
                    field_title.after(data_title.html);

                    processViewPanelHeader.setEditing(true);
                });
            },


            autoEditIfNewParentEntity : function(){
                var panel = $('.process_view_block .element[data-name="process_view_panel"] .element[data-name="panel"]');
                if(panel.length != 1) return;
                if(panel.find('.panel').data('unique_index') != 'd41d8cd98f00b204e9800998ecf8427e') return; // еще не сохраненная
                processViewPanelHeader.edit(panel);

            },

            save : function(_this, callback){
                if(processViewPanelHeader.editing == false){
                    if(typeof(callback) == 'function'){
                        return callback(true);
                    }
                    return true;
                }

                var fields_data_list = [];
                fields_data_list.push({
                    'field_name' : 'todo_list',
                    'id' :  _this.find('.element[data-name="todo_list"]').data('id'),
                    'value' : _this.find('.element[data-name="todo_list"]').val(),
                });


                if($.isEmptyObject(fields_data_list)){
                    if(typeof(callback) == 'function'){
                        return callback(false);
                    }
                    return false;
                }

                var sorting_list_id = _this.closest('.panel').data('sorting_list_id');

                //savePanelTitle
                processViewPanelHeader.savePanelTitle(sorting_list_id, fields_data_list, function(data){
                    if(data.status == true){
                        $.each(data.fields_data, function(key, value){
                            _this.find('.element[data-name="field_title_value"]')
                                .data({
                                    'id': value.id
                                })
                                .attr({
                                    'data-value': value.value
                                });
                            _this.find('.element[data-name="field_title"]').css('display', 'inline');
                            _this.closest('.panel')
                                .data('unique_index', data.unique_index)
                                .data('sorting_list_id', data.sorting_list_id)
                                .attr('data-unique_index', data.unique_index)
                                .attr('data-sorting_list_id', data.sorting_list_id);
                            _this.find('input.element[data-name="todo_list"]').remove();
                            _this.find('a.process_view-save-input').remove();
                        });
                        ProcessView.initUiSort();
                    }

                    processViewPanelHeader.setEditing(false);

                    if($.isFunction(callback)){
                        return callback();
                    }
                    return data.status;
                })
            }
        }

        this.events();


        //add_list
        var eventPath = start+'.process_view_dnt-add_list';

        // add new panel
        $(document).off('click', eventPath).on('click', eventPath, function(){
            processViewPanelHeader.addPanel(this);
        })



        //edit title
        eventPath = start+'.element[data-name="process_view_panel"] li .panel-heading';
        $(document).off('click', eventPath).on('click', eventPath, function(){
            if($(this).find('.element[data-name="field_title_value"]').length) {
                if(processViewPanelHeader.editing) {
                    return;
                }

                processViewPanelHeader.edit(this);
            }
        })


        //save title
        eventPath = start+'.process_view-save-input';
        $(document).off('click', eventPath).on('click', eventPath, function(e, data){
            var value, param, $button,
                $this = $(this),
                $heading = $this.closest('.panel-heading'),
                $sectionPanel = $this.closest('section.panel');

            e.preventDefault();

            $button = $this.closest('section').find('button.btn-create');

            if (data) {
                //TODO: есть проблема на мастере. смена TODO листа
                if ($button.is('.edit_view_dnt-add')) {
                    if (!data.saving) {
                        param = null
                    } else {
                        param = function () {
                            this
                                .setSortingListId($sectionPanel.data('sorting_list_id'))
                                .addCard($button);
                        };
                    }

                } else {
                    param = !data.saving ? null : parent.onAddSelectDnt;
                }
            }

            processViewPanelHeader.save($heading, function (data) {
                $heading.closest('li[data-name="panel"]').removeAttr('data-update-title');

                if ($.isFunction(param)) {
                    param.call(ProcessView.getInstance(), $button);
                }
            });

            value = $heading.find('input.element[data-name="todo_list"]').attr('value');
            $heading.find('a.process_view-save-input').remove();
            $heading.find('input.element[data-name="todo_list"]').remove();
            $heading.find('.element[data-name="field_title"]').show().find('[data-name="field_title_value"]').text(value);
        })


        //save title - 27
        eventPath = start+' input.element[data-name="todo_list"]';
        $(document).off('keydown', eventPath).on('keydown', eventPath, function( e ) {
            if(e.keyCode == 13){
                $(start+' .process_view-save-input').trigger('click');
            } else if (e.keyCode == 27) {
                var $panel = $(this).closest('.panel-heading');

                $panel.find('.element[data-name="field_title"]').show();
                $panel.find('a.process_view-save-input').remove();
                $panel.find('input.element[data-name="todo_list"]').remove();
                processViewPanelHeader.setEditing(false);
                e.stopPropagation();
                ProcessView.initUiSort();
            } else {
                return (e.keyCode);
            }
        });

        //focus
        eventPath = start+'.element[data-name="process_view_panel"] li .panel-heading input, .process_view_block .element[data-name="process_view_panel"] li .panel-heading .dropdown-menu';
        $(document).off('click', eventPath).on('click', eventPath, function(e){
            e.stopPropagation();
            $(this).focus();
        })



        eventPath = '#container';
        $(document).off('click', eventPath).on('click', eventPath, function(e){
            var $heading, saving,
                $target = $(e.target);

            if ($(e.target).closest('section').data('unique_index') ==  $(start+' .process_view-save-input').closest('section').data('unique_index')) {
                saving = true;
            }

            $(start+' .process_view-save-input').trigger('click', {
                saving: $(e.target).is('.btn-create:not(.process_view_dnt-add_list)') && saving ? true : false
            });

            if ($target.is('.process_view_dnt-add_list') ||
                $target.is('.edit_view_select_dnt-add') ||
                $target.is('.edit_view_select_btn-create') ||
                $target.is('.process_view-save-input')
            ) {
                return;
            }

            $heading = $('.panel-heading');
            $heading.find('a.process_view-save-input').remove();
            $heading.find('input.element[data-name="todo_list"]').remove();
            $heading.find('.element[data-name="field_title"]').show();
            $heading.closest('li[data-name="panel"]').removeAttr('data-update-title');

            processViewPanelHeader.editing = false;
            processViewPanelHeader.setEditing(false);
            //processView.initUiSort();
        });





        $(document).ready(function(){
            var event;

            processViewPanelHeader.autoEditIfNewParentEntity();

            $('ul.process_list').width($('ul.process_list > li').length * 300 + 155);

            // add new card
            // event = '.process_view_block .edit_view_dnt-add';
            // $(document).off('click', event).on('click', event, function(e) {
            //     var _this = this;
            //
            //     if ($(e.target).closest('li[data-update-title="true"]').length) {
            //         return;
            //     }
            //
            //     processView.setSortingListId($(_this).closest('section.panel').data('sorting_list_id'));
            //     processView.addCard(_this);
            // });

            // add new card over Select form
            event = '.process-view .edit_view_select_btn-create';
            $(document).off('click', event).on('click', event, function(){
                var _this = this;
                EditView.cardSelectValidate(this, function(data){
                    if(data){
                        var _default_data = $(_this).closest('.edit-view.sm_extension').find('.default_data').text();
                        if(_default_data){
                            _default_data = JSON.parse(_default_data);
                        } else {
                            _default_data = null;
                        }

                        modalDialog.hide();
                        EditView.addCardFromTemplate(_this, _default_data);
                    }
                })
            });
        });
    }
    this.saveTitles = function () {
        $(start +' .process_view-save-input').trigger('click');
    },
    this.isTitleSaved = function () {
        return $('[data-update-title="true"]').length ? false : true;
    }
    this.events = function () {
        var data = {
            instance: this
        };

        this._events = [
            { name: '.process_view_block .edit_view_select_dnt-add', eve1nt: 'click', func: this.onAddSelectDnt }, // add new card over Select form
            //{ name: '', event: '', func: '' },
        ];

        Base.addEvents(this._events, data);
    }

    this.onAddSelectDnt = function($element){
        var instance,
            _data = [],
            $this = $element && $element.length ? $element : $(this),
            $extension = $this.closest('.sm_extension'),
            $item = $this.closest('.panel').find('.element[data-name="field_title"] .element[data-name="field_title_value"]');

        instance = ProcessView.getInstance();

        instance.setSortingListId($this.closest('section.panel').data('sorting_list_id'));
        //TODO: on test
        ProcessViewBase.getFieldId($item, _data);
        _data = {'default_data' : _data};

        instance.$panel_change = $this.closest('section.panel');
        EditView.addCardSelect($extension, 'process-view', _data);
    }

    this.getFieldId = function ($element, toArray) {
        $.each($element, function(){
            var $this = $(this);
            toArray.push({'field_name' : $this.data('field'), 'value' : $this.attr('data-value')});
        });
        return true;
    }
}
