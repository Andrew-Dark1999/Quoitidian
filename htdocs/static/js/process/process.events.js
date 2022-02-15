;(function (exports) {
    var _private, _public, _protected, instanceCurrentPopUp, ProcessEvents,
        _self = {}; //link for instance

    _protected = {

    };
    _private = {

    };

    _public = {
        constructor: function () {
            this.events()
                .allMethod();

            this.getFilter = Base.copyObject(Filter); // clone object                        
            if (ProcessObj.mode == 'constructor' && location.search.indexOf('process_id') >= 0) {
                Global.addClass(document.querySelector('body'), 'bpm-module');
            }
            return this;
        },
        events: function () {
            var path = this.actions;

            this._events = [
                { parent: document, selector: '', event: '', func: path},
            ]
            this._events = [
                { parent: document, selector: 'li.modal_dialog[data-controller="process_view_edit"]', event: 'click', func: path.onClickProcessOpen }, // BPM open
                // Show operation params
                { parent: document, selector: '.element[data-type="responsible"] .element[data-type="operation"]:not(.and_helper) .bpm_body',
                    event: 'click', func: path.onClickShowParams },
                /*Save operation params*/
                { parent: document, selector: '.bpm_modal_dialog .element[data-type="save"]',
                    event: 'click', func: path.onClickSaveParams },
                //create new process
                { parent: document, selector: '.list-view .edit_view_select_btn-create, .process-view .edit_view_select_btn-create', event: 'click', func: path.onClickCreateNewProsess},
                /*Done operation params*/
                { parent: document, selector: '.bpm_modal_dialog .element[data-type="done"]', event: 'click', func: path.onClickDialogDone},
                // { parent: document, selector: 'li.modal_dialog a[data-controller="process_view_edit"]', event: 'click', func: path.onClickDialogPVEdit},
                { parent: document, selector:
                        '.element[data-type="params"][data-module="process"][data-name="task"] .edit_view_card_btn-save,' +
                        '.element[data-type="params"][data-module="process"][data-name="agreetment"] .edit_view_card_btn-save,' +
                        '.element[data-type="params"][data-module="process"][data-name="notification"] .edit_view_card_btn-save,' +
                        '.element[data-type="params"][data-module="process"][data-name="data_record"] .edit_view_data_record_btn-save,' +
                        '.element[data-type="params"][data-module="process"][data-name="agreetment"] .edit_view_task_task-approve, ' +
                        '.element[data-type="params"][data-module="process"][data-name="agreetment"] .edit_view_task_task-reject',
                    event: 'click', func: path.onClickSave},
                /* Delete operator*/
                { parent: document, selector: '.element[data-type="operation"]:not(.ui-state-disabled) .bpm_operator_remove', event: 'click', func: path.onClickOperatorRemove},
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="condition"] .element[data-type="value_condition"]', event: 'change', func: path.onChangeValueCondition},
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="begin"] .element[data-type="value_condition"]', event: 'change', func: path.onChangeValueBegin},

                { parent: document, selector: '.modal .element[data-name="condition"] .settings-menu .element[data-type="value_condition"]', event: 'change', func: path.onChangeValueConditionFromModal},
                /*operation "begin" - change*/
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="begin"] .element[data-type="object_name"], '+
                        '.element[data-type="params"][data-module="process"][data-name="begin"] .element[data-type="field_name"]', event: 'change', func: path.onChangeBeginElement},
                /*operation "condition" - change*/
                { parent: document, selector:
                        '.element[data-type="params"][data-module="process"][data-name="condition"] .element[data-type="object_name"], '+
                        '.element[data-type="params"][data-module="process"][data-name="condition"] .element[data-type="relate_module"], '+
                        '.element[data-type="params"][data-module="process"][data-name="condition"] .element[data-type="field_name"]', event: 'change', func: path.onChangeConditionElement},
                /* operation Timer change 2 */
                { parent: document, selector:
                        '.element[data-type="params"][data-module="process"][data-name="timer"] .element[data-type="object_name"], '+
                        '.element[data-type="params"][data-module="process"][data-name="timer"] .element[data-type="relate_module"], '+
                        '.element[data-type="params"][data-module="process"][data-name="timer"] .element[data-type="field_name"]',
                    event: 'change', func: path.onChangeTimerElement},
                /*operation "data_record" - change*/
                { parent: document, selector:
                        '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="type_operation"],' +
                        '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="module_name"],' +
                        '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="record_name_list"],' +
                        '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="call_edit_view"]',
                    event: 'change', func: path.onChangeDataRecord},
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="value_field_name"]',
                    event: 'change', func: path.onChangeElementValueFieldName},
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="notification"] .element[data-type="type_message"]', event: 'change', func: path.onChangeDataTypeMessage},
                /*operation "begin" - add element*/
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="begin"] .element[data-type="label_add_value"]',
                    event: 'click', func: path.onClickAddBegin},
                /*operation "condition" - add element*/
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="condition"] .element[data-type="label_add_value"]',
                    event: 'click', func: path.onClickAddCondition},
                /* operation "data_record" - add element */
                { parent: document, selector: '.element[data-type="params"][data-module="process"][data-name="data_record"] .element[data-type="label_add_value"]',
                    event: 'click', func: path.onClickAddDataRecord},
                // add responsible
                { parent: document, selector: '.element[data-type="module_relate"] .sm_extension_data', event: 'click', func: path.onClickAddResponsible},
                { parent: document, selector: '.bpm_responsible_add', event: 'click', func: path.onClickAddResponsibleParam},

                // change responsible
                { parent: document, selector: '.bpm_responsible_change', event: 'click', func: path.onResponsibleChange},
                // save responsible
                { parent: document, selector: '.modal-dialog .bpm_responsible_save', event: 'click', func: path.onResponsibleAdd},
                { parent: document, selector: '.modal-dialog .bpm_params_save', event: 'click', func: path.onResponsibleSave},
                { parent: document, selector: '.bpm_responsible_remove', event: 'click', func: path.onResponsibleRemove},
                // switch process status
                { parent: document, selector: '.element[data-type="actions"] .element[data-type="start"], ' +
                        '.element[data-type="actions"] .element[data-type="stop"], ' +
                        '.element[data-type="actions"] .element[data-type="terminate"]', event: 'click', func: path.onSwitchProcessStatus},
                // switch mode change
                { parent: document, selector: '.element[data-type="actions"] .element[data-type="mc_edit"], ' +
                        '.element[data-type="actions"] .element[data-type="mc_view"]', event: 'click', func: path.onChangeMode},
                { parent: document, selector: 'select.element[data-type="start_on_time"]', event: 'change', func: path.onChangeStartOnTime},
                { parent: document, selector: 'select.element[data-type="periodicity"]', event: 'change', func: path.onChangeSelectPeriodiCity},
                { parent: document, selector: '.bpm_modal_dialog .inputs-block .element[data-type="label_add_date"]', event: 'click', func: path.onClickDialogLabelAddDate},
                { parent: document, selector: 'select.element[data-type="type_agreetment"]', event: 'change', func: path.onChangeSelectTypeAgreetment},
                { parent: document, selector: '.bpm_modal_dialog .inputs-block .element[data-type="remove_panel"]', event: 'click', func: path.onClickRemovePanel},

                { parent: document, selector: '.modal .settings-menu .element_filter[data-name="condition_value"]',
                    event: 'keyup', func: path.inputData},
                { parent: document, selector: '.modal .settings-menu .element_filter[data-name="condition_value"]',
                    event: 'change', func: path.inputData},

                { parent: document, selector: '.element[data-name="and"] .element[data-type="number_branches"]', event: 'keyup', func: path.onKeyUpNumberBranches},
                { parent: document, selector: '.select[multiple]', event: 'change', func: path.onChangeSelectMultiple},
                { parent: document, selector: '.submodule-link:not(.participant) td', event: 'click', func: path.onClickSubModuleTD},
                //{ name: '', event: '', func: ''},
            ];

            Global.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        editCard : function (element) {
            this.open(element);
        },
    };

    instanceCurrentPopUp = null; // текущее окно
    ProcessEvents = {
        _instance: null,
        _type: 'process',
        _interface: 'ProcessEvents',

        getInstance : function(status){
            if (!ProcessEvents._instance && status) {
                ProcessEvents._instance = this.createInstance();
            }
            return ProcessEvents._instance;
        },

        createInstance : function(){
            var Obj = function(){
                for(var key in ProcessEvents){
                    this[key] = ProcessEvents[key];
                }
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            return ProcessEvents._instance = new Obj().constructor();
        },
        actions: {
            onClickSubModuleTD : function(e){
                var dataId = $(this).closest('.sm_extension_data').data('id'),
                    txthtml = $(this).html(),
                    button = $(this).closest('.submodule-link').find('button.btn.dropdown-toggle');
                button.data('id',dataId);
                button.html(txthtml);
                $(this).closest('.submodule-link').removeClass('open');
                $(this).closest('.submodule-table').find('.active').removeClass('active');
                $(this).closest('.sm_extension_data').addClass('active');
            },
            onChangeSelectMultiple : function(){
                ProcessObj.getCountOptions($(this));
            },
            onKeyUpNumberBranches : function(e){
                if (e.keyCode == 13 && 0<$(this).val() && $(this).val()<11) {
                    $(this).closest('.element[data-name="and"]').find('.btn.element[data-type="save"]').trigger('click');
                } else {
                    return (e.keyCode);
                }
            },
            inputData : function (e) {
                var str,
                    $this = $(this),
                    addingEl = $this.closest('.settings-menu').find('.element[data-type="value_condition"]'),
                    addingVal = addingEl.find('option[value="'+addingEl.val()+'"]').text(),
                    value = $this.val(),
                    testvar = addingVal+' ' + value,
                    $element = $this.closest('.column').find('.element[data-type="value_scalar"], .element[data-type="value_datetime"]'),
                    arrValues = value.split('.');

                if (isNaN(arrValues[0]) && arrValues.length>1 || !value.length) {
                    $this.val($this.data('old-value'));
                } else {
                    $element.val(testvar);
                    $this.data('old-value',value);

                    if ($this.parent().is('.datepicker-range')) {
                        var $parent = $this.parent();

                        if ($this.is('.dp2')) {
                            str = $parent.find('input:first').val() + '-' + $this.val();
                        } else {
                            str = $this.val() + '-'+ $parent.find('input:last').val()
                        }
                        $element.val(addingVal + ' ' + str);
                    }
                }
                $(e.target).closest('.datepicker-range').closest('.open').addClass('opened');
            },
            onClickDialogLabelAddDate : function(){
                var _thisLi = $(this).closest('li');
                var inputsBlock = _thisLi.closest('.inputs-block');
                var unique_index = $(this).closest('.element[data-module="process"]').data('unique_index');
                var typeElement = inputsBlock.find('select.element[data-type="periodicity"]').val();

                ProcessObj.BPM.elementsActions.runOnTime.labelAddDate(typeElement, _thisLi, unique_index);
                Global.groupDropDowns(0).init($(this));
            },
            onChangeValueConditionFromModal : function(){
                var $this = $(this),
                    thisVal = $this.find('option[value="'+$this.val()+'"]').text(),
                    addingVal = $this.closest('.settings-menu').find('.element_filter[data-name="condition_value"]').val(),
                    testvar = thisVal+' '+addingVal;

                $('.crm-dropdown.open').addClass('opened').prev().filter('.element[data-type="value_scalar"], .element[data-type="value_datetime"]').val(testvar);
            },
            onClickRemovePanel : function(){
                var _this = $(this);
                var inpBl = _this.closest('.inputs-block'),
                    select = $('[data-type="value_field_name"]');
                var field = _this.closest('li').find(select).val();

                select.find('option[value="'+ field +'"]').removeClass('hide');
                _this.parent().find('.column_half:first').find('select option').each(function(){
                    var item = $(this);
                    $('[data-module="process"][data-name="data_record"] .element.col-1 .column_half:even').each(function () {
                        $(this).find('ul.dropdown-menu li').eq(item.index()).attr('value',item.attr('value'));
                    });
                });
                $('[data-module="process"][data-name="data_record"] .element.col-1 .column_half ul li[value="'+field+'"]').removeClass('hide').find('a').removeClass('hide');
                var object = _this.closest('.inputs-block').find('.add_list .element');
                _this.closest('li').remove();
                object.closest('.add_list').show();
                Global.groupDropDowns(0).init(object);
            },
            onChangeSelectTypeAgreetment : function() {
                var object = $(this).closest('li').next();
                ($(this).val() == 'external') ? object.show() : object.hide();
            },
            onChangeSelectPeriodiCity : function(){
                ProcessObj.BPM.elementsActions.runOnTime.periodicityChanged(this);
                modalDialog.afterUpdateContent();
            },
            onChangeStartOnTime : function(){
                ProcessObj.BPM.elementsActions.runOnTime.startOnTimeChanged(this);
                modalDialog.afterUpdateContent();
            },
            onChangeMode : function(){
                var _this = this;
                var process = new Process();
                process.BPM.modeChangeSwitch(_this);
                $(this).closest('.crm-dropdown.element.open').removeClass('open');
                ProcessObj.editOrViewProcess();
            },
            onSwitchProcessStatus : function(){
                ProcessObj.BPM.switchProcessStatus(this, true);
            },
            onResponsibleRemove : function(e){
                var instance = e.data.instance;

                if ($('.bpm_unit[data-type="responsible"]').length>1) {
                    instance.removeResponsible($(this));
                } else {
                    Message.show([{'type':'information', 'message': 'You can not delete last responsible'}], true);
                    $(this).closest('.crm-dropdown').removeClass('open');
                }

            },
            onResponsibleAdd : function(){
                var _this = $(this).closest('.sm_extension'),
                    action = _this.data('action');

                ProcessObj.BPM.participants.runAction(_this, action);
            },
            onResponsibleSave : function(){
                ProcessObj.BPM.bpmParamsRun(ProcessObj.PROCESS_BPM_PARAMS_ACTION_UPDATE, this);
            },
            onResponsibleChange : function(){
                ProcessObj.BPM.participants.show(this, ProcessObj.PROCESS_PARTICIPANT_ACTION_CHANGE);
            },
            onClickAddResponsibleParam : function(){
                ProcessObj.BPM.participants.show(this, ProcessObj.PROCESS_PARTICIPANT_ACTION_ADD);
            },
            onClickAddResponsible : function(){
                ProcessObj.BPM.updateRelateModule(this);
            },
            onClickAddDataRecord : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'data_record', function(data){
                    var currentItem;
                    if(data.status == true){
                        var parent = $(_this.closest('[data-module="process"][data-name="data_record"]'));

                        currentItem = $(jQuery.parseHTML(data.params_result)[1]).find('[data-type="value_field_name"]').addClass('weAdded'); // we adding it
                        if (parent.length) {
                            parent.find('[data-type="value_field_name"]').not('.weAdded').each(function(){
                                currentItem.find('option[value="'+$(this).val()+'"]').addClass('hide');
                                var list = currentItem.find('option').not('.hide');
                                if (list.length) {
                                    currentItem.val(list.first().val());
                                };
                            });
                        }
                        var onlyAllowing = currentItem.find('option');
                        if (onlyAllowing.not('option.hide').length)
                        {
                            var baseWindow = $('[data-module="process"][data-name="data_record"]');
                            currentItem.find('option[value="'+currentItem.val()+'"]').addClass('hide');
                            $(_this).closest('li').before(currentItem.closest('li.form-group'));
                            if (!onlyAllowing.not('option.hide').length) {
                                baseWindow.find('.inputs-group.add_list').hide();
                            }
                            baseWindow.find('.element.col-1:last .column_half:first li.selected').addClass('hide').find('a').removeClass('hide');
                        }
                    }

                    Global.initSelects();
                    ProcessObj.activateDropdowns();
                    ProcessObj.initDatePicker();
                    ProcessObj.initTimePicker();

                    if (currentItem) currentItem.trigger('change');
                    $('.weAdded').removeClass('weAdded');
                    Global.groupDropDowns(10).init($(_this));

                    modalDialog.afterUpdateContent();
                })
            },
            onClickAddBegin : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'begin', function(data){
                    var $currentLi, $select,
                        $this = $(_this),
                        $li = $this.closest('li');

                    if(data.status == true){
                        $li.before(data.params_result);
                        $('input[data-type="value_scalar"]').attr('disabled', 'disabled').attr('style', 'background-color: rgb(255, 255, 255)');
                    }
                    // fix title on branch in operator terms
                    $currentLi = $li.prev(),
                        $select = $currentLi.find('[data-type="value_condition"]');
                    $currentLi.find('[data-type="value_scalar"]').val($select.find('option[value="'+$select.val()+'"]').text());

                    Global.initSelects();
                    ProcessObj.activateDropdowns();

                    // format date_time
                    $('.modal .bpm_modal_dialog .element[data-type="settings"]').each(function(i, ul){
                        ProcessObj.BPM.prepateFilterDateTime(ul);
                    });

                    $('.modal .element[data-name="begin"] .settings-menu .selectpicker li>a').on('click', function() {
                        $(this).closest('.bootstrap-select.open').removeClass('open');
                    });
                    Global.groupDropDowns(10).init($this);

                    modalDialog.afterUpdateContent();
                })
            },
            onClickAddCondition : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'condition', function(data){
                    var $currentLi, $select,
                        $this = $(_this),
                        $li = $this.closest('li');

                    if(data.status == true){
                        $li.before(data.params_result);
                        $('input[data-type="value_scalar"]').attr('disabled', 'disabled').attr('style', 'background-color: rgb(255, 255, 255)');
                    }
                    // fix title on branch in operator terms
                    $currentLi = $li.prev(),
                        $select = $currentLi.find('[data-type="value_condition"]');
                    $currentLi.find('[data-type="value_scalar"]').val($select.find('option[value="'+$select.val()+'"]').text());

                    Global.initSelects();
                    ProcessObj.activateDropdowns();

                    // format date_time
                    $('.modal .bpm_modal_dialog .element[data-type="settings"]').each(function(i, ul){
                        ProcessObj.BPM.prepateFilterDateTime(ul);
                    });

                    $('.modal .element[data-name="condition"] .settings-menu .selectpicker li>a').on('click', function() {
                        $(this).closest('.bootstrap-select.open').removeClass('open');
                    });
                    Global.groupDropDowns(10).init($this);
                    instanceCurrentPopUp.data.load();
                    modalDialog.afterUpdateContent();
                })
            },
            onChangeDataTypeMessage : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'notification', function(data){
                    if(data.status == true){
                        var content = '';
                        $.each(data.params_result, function(key, value){
                            if($.isArray(value)) content+= value.join('');

                        })
                        $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(content);

                        Global.initSelects();
                        ProcessObj.activateDropdowns();
                        ProcessObj.getCountOptions($('.select[multiple]'));
                        Global.groupDropDowns(0).init($('.element[data-type="label_add_filter"]'));
                        modalDialog.afterUpdateContent();
                    }
                })
            },

            onChangeElementValueFieldName : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'data_record', function(data){
                    if(data.status == true){
                        $(_this).closest('.element[data-type="value_block"]').find('.column_half:last-child').after(data.params_result).remove();

                        var listBlockSelect = $('[data-module="process"][data-name="data_record"] .element.col-1 .column_half:even');
                        listBlockSelect.find('select option').removeClass('hide');
                        listBlockSelect.find('ul li').removeClass('hide').find('a').removeClass('hide');
                        listBlockSelect.find('select').each(function () {
                            var item = $(this);
                            listBlockSelect.find('select option[value="'+ item.val() +'"]').addClass('hide');
                            listBlockSelect.find('ul li[value="'+ item.val() +'"]').addClass('hide');
                        });

                        Global.initSelects();
                        ProcessObj.activateDropdowns();
                        ProcessObj.initDatePicker();
                        ProcessObj.initTimePicker();
                        modalDialog.afterUpdateContent();
                    }
                })
            },

            onChangeDataRecord : function(){
                var _this = this;

                var process = new Process();
                process.BPM.changeParamsContent(_this, 'data_record', function(data){
                    if(data.status == true){
                        $(_this).closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(data.params_result);

                        Global.initSelects();
                        ProcessObj.activateDropdowns();
                        ProcessObj.getCountOptions($('.select[multiple]'));

                        Global.groupDropDowns(0).init($('[data-type="label_add_value"]:visible'));

                        modalDialog.afterUpdateContent();
                    }
                })
            },

            /*Save operation params*/
            onClickCreateNewProsess : function(){
                var _this = this,
                    $this = $(_this);

                ProcessObj.createFromTemplate(_this, $this.closest('.sm_extension').find('.element[data-type="template"]').val(), function(data){
                    var process = new Process();
                    process.BPM.open(data.process_id, 'run', $this);
                })
            },

            onClickDialogDone : function(){
                var _unique_index = $(this).closest('.element[data-type="params"]').data('unique_index');
                var _this = $('.inputs-block');
                ProcessObj.BPM.operationParams.done(_this, _unique_index, function(data){
                    ProcessObj.refreshStatus(data.schema, 'all');
                    ProcessObj.BPM.updateProcessStatus(data.process_status);
                    if(_this.closest('.modal-dialog').find('header .client-name .editable-field').length > 0){
                        if(ProcessObj.BPM.operationParams.settings[_unique_index].status == "unactive"){
                            ProcessObj.titleOperatorRename(_unique_index, _this.closest('.modal-dialog').find('header .client-name .editable-field').text());
                        }
                    }
                    operatorsParalelArr = [];
                    $('.bpm_operator[mark="marked"]').each(function(){
                        operatorsParalelArr.push($(this).data('unique_index'));
                    });
                    ProcessObj.BPM.deleteQeue(operatorsParalelArr, function(unique_index){
                    });
                    modalDialog.hide();
                    ProcessObj.zeroBuild(false, true);
                    ProcessObj.BPM.saveSchema();
                });

                ProcessObj.zeroBuild(false, true);
                ProcessObj.recountRespBlocks();
                ProcessObj.BPM.recountArrows();
                ProcessObj.branchSignatures();
            },

            onClickSave : function(){
                if ($(this).closest('.edit-view').find('.emoji-wysiwyg-editor').html()!=="") {
                    $(this).closest('.edit-view').find('.send_massage_activity').trigger('click');
                } else if ($(this).closest('.edit-view').find('.task_comments>.task_message .file_thumb.file_other').text() == "GDoc") {
                    $(this).closest('.edit-view').find('.emoji-wysiwyg-editor').text(Message.translate_local('Added document Google Doc'));
                    $(this).closest('.edit-view').find('.send_massage_activity').trigger('click');
                }
                var _element_name = $(this).closest('.element[data-type="params"]').data('name'),
                    _unique_index = $(this).closest('.element[data-type="params"]').data('unique_index'),
                    _this = $(this).closest('.element[data-type="params"][data-module="process"]'),
                    _params = {
                        'process_operation' : ProcessObj.BPM.operationParams.getSaveData(_this, _element_name, _unique_index)
                    };

                if($(this).hasClass('edit_view_task_task-approve')){
                    _params['operation_agreetment_approve'] = ProcessObj.PROCESS_AGREETMENT_APPROVE;
                } else if($(this).hasClass('edit_view_task_task-reject')){
                    _params['operation_agreetment_approve'] = ProcessObj.PROCESS_AGREETMENT_REJECT;
                }

                var copy_id = $(this).closest('.edit-view').data('copy_id');

                EditView.save(copy_id, _params, function(data){
                    if(data.status == 'save'){
                        ProcessObj.BPM.updateProcessStatus(data.process_status);

                        ProcessObj.BPM.schema = data.schema;

                        if (_this.find('header .client-name .editable-field').length>0 && $('.modal .element[data-name="data_record"]').length<1) {
                            ProcessObj.titleOperatorRename(_unique_index, _this.find('header .client-name .editable-field').text());
                        }

                        modalDialog.hide();
                        ProcessObj.refreshStatus(data.schema, 'all');

                        ProcessObj.BPM.saveSchema();
                        HeaderNotice.refreshAllHeaderNotices();
                    }
                }, '/module/BPM/saveSchemaOperationCard/' + ProcessObj.copy_id);


            },
            onChangeValueBegin : function(){
                var listOfValues,
                    $this = $(this),
                    $select = $this.closest('.element[data-type="params"][data-module="process"]').find('.element[data-type="field_name"]'),
                    $inpBlock = $this.closest('.inputs-block'),
                    keyWindow = $this.closest('.panel[data-unique_index]').attr('data-unique_index'),
                    maxBranch = parseInt($inpBlock.find('>li[branch]:last').attr('branch'));

                ProcessObj.listOfElements[keyWindow].branch = $this.closest('li[branch]').attr('branch');

                listOfValues = instanceCurrentPopUp.listValuesOfFilters($inpBlock, keyWindow, maxBranch);
                $.each($inpBlock.find('>li[branch]'), function (key, data) {
                    var _this = $(data).find('[data-name="condition_value"]');

                    if (_this.is('.dp1,.dp2')) {
                        _this = _this.closest('.datepicker-range');
                    }

                    listOfValues.set(_this, $(data).attr('branch'));
                })

                $this.closest('.crm-dropdown').addClass('opened');
                $select.trigger('change');
            },
            onChangeValueCondition : function(){
                var listOfValues,
                    $this = $(this),
                    $select = $this.closest('.element[data-type="params"][data-module="process"][data-name="condition"]').find('.element[data-type="field_name"]'),
                    $inpBlock = $this.closest('.inputs-block'),
                    keyWindow = $this.closest('.panel[data-unique_index]').attr('data-unique_index'),
                    maxBranch = parseInt($inpBlock.find('>li[branch]:last').attr('branch'));

                ProcessObj.listOfElements[keyWindow].branch = $this.closest('li[branch]').attr('branch');

                listOfValues = instanceCurrentPopUp.listValuesOfFilters($inpBlock, keyWindow, maxBranch);
                $.each($inpBlock.find('>li[branch]'), function (key, data) {
                    var _this = $(data).find('[data-name="condition_value"]');

                    if (_this.is('.dp1,.dp2')) {
                        _this = _this.closest('.datepicker-range');
                    }

                    listOfValues.set(_this, $(data).attr('branch'));
                })

                $this.closest('.crm-dropdown').addClass('opened');
                $select.trigger('change');
            },
            onChangeBeginElement : function(){
                var _this = $(this);

                var key = _this.closest('.panel[data-unique_index]').attr('data-unique_index'),
                    popUp = ProcessObj.listOfElements[key];

                start_on_time = $(_this).closest('.element[data-name="begin"]').find('.element[data-type="start_on_time"]').val();

                if(start_on_time == 'start_on_after_created_entity'){
                    return;
                }

                var process = new Process();

                // if (_this.is('[id="field_name"]') && !popUp.branch) {
                //     var link = _this.closest('.inputs-block').find('.add_list .operations>a');
                //     instanceCurrentPopUp.init(link);
                //     instanceCurrentPopUp.listValuesOfFilters(link.closest('ul.inputs-block'), key, 10).load();
                // }

                instanceCurrentPopUp = instanceCurrentPopUp || Global.groupDropDowns(10);
                var link = _this.closest('.inputs-block').find('.add_list .operations>a');
                instanceCurrentPopUp.init(link);
                instanceCurrentPopUp.listValuesOfFilters(link.closest('ul.inputs-block'), key, 10).load();

                process.BPM.changeParamsContent(_this, 'begin', function(data){
                    if(data.status == true){
                        var inpBl = _this.closest('ul.inputs-block');

                        var inputs_block = _this.closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block');

                        inputs_block.find('.dinamic').remove();
                        inputs_block.append(data.params_result)

                        Global.initSelects();
                        ProcessObj.activateDropdowns();

                        if (!instanceCurrentPopUp) {
                            instanceCurrentPopUp = Global.groupDropDowns(10);
                        }
                        instanceCurrentPopUp.init(inpBl.find('.add_list [data-type="label_add_value"]'));

                        $('.modal .element[data-name="begin"] .settings-menu .selectpicker li>a').on('click', function(e) {
                            $(this).closest('.bootstrap-select.open').removeClass('open');
                        });


                        $('.modal .element[data-name="begin"] .element[data-type="value_scalar"], .modal .element[data-name="begin"] .element[data-type="value_datetime"]').each(function(){
                            var inpVal = $(this).closest('.column').find('.element_filter[data-name="condition_value"]'),
                                addingEl = $(this).closest('.column').find('.element[data-type="value_condition"]'),
                                addingVal = addingEl.find('option[value="'+addingEl.val()+'"]').text(),
                                mergedVal = addingVal+' '+inpVal.val(),
                                space = mergedVal.lastIndexOf(' ');

                            if (space > -1) {
                                mergedVal = mergedVal.substring(0, space);
                            }
                            inpVal.val('');
                            $(this).val(mergedVal).attr('disabled','disabled').css('background-color','#fff');
                        });

                        if (popUp && popUp.branch) {
                            var innerDropDown = $('.inputs-block:visible >li[branch="'+popUp.branch+'"]').find('.crm-dropdown')
                            innerDropDown.addClass('open');
                            instanceCurrentPopUp.data.get()
                        }

                        // format date_time
                        $('.modal .bpm_modal_dialog .element[data-type="settings"]').each(function(i, ul){
                            ProcessObj.BPM.prepateFilterDateTime(ul);
                        });

                        modalDialog.afterUpdateContent();
                    }
                })
            },
            onChangeConditionElement : function(){
                var _this = $(this),
                    key = _this.closest('.panel[data-unique_index]').attr('data-unique_index'),
                    popUp = ProcessObj.listOfElements[key];

                var process = new Process();

                if (_this.is('[id="field_name"]') && !popUp.branch) {
                    var link = _this.closest('.inputs-block').find('.add_list .operations>a');
                    instanceCurrentPopUp.init(link);
                    instanceCurrentPopUp.listValuesOfFilters(link.closest('ul.inputs-block'), key, 10).load();
                }

                process.BPM.changeParamsContent(_this, 'condition', function(data){
                    if(data.status == true){
                        var inpBl = _this.closest('ul.inputs-block');

                        _this.closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block').html(data.params_result);

                        Global.initSelects();
                        ProcessObj.activateDropdowns();

                        if (!instanceCurrentPopUp) {
                            instanceCurrentPopUp = Global.groupDropDowns(10);
                        }
                        instanceCurrentPopUp.init(inpBl.find('.add_list [data-type="label_add_value"]'));

                        $('.modal .element[data-name="condition"] .settings-menu .selectpicker li>a').on('click', function(e) {
                            $(this).closest('.bootstrap-select.open').removeClass('open');
                        });


                        $('.modal .element[data-name="condition"] .element[data-type="value_scalar"], .modal .element[data-name="condition"] .element[data-type="value_datetime"]').each(function(){
                            var inpVal = $(this).closest('.column').find('.element_filter[data-name="condition_value"]'),
                                addingEl = $(this).closest('.column').find('.element[data-type="value_condition"]'),
                                addingVal = addingEl.find('option[value="'+addingEl.val()+'"]').text(),
                                mergedVal = addingVal+' '+inpVal.val(),
                                space = mergedVal.lastIndexOf(' ');

                            if (space > -1) {
                                mergedVal = mergedVal.substring(0, space);
                            }
                            inpVal.val('');
                            $(this).val(mergedVal).attr('disabled','disabled').css('background-color','#fff');
                        });

                        if (popUp && popUp.branch) {
                            var innerDropDown = $('.inputs-block:visible >li[branch="'+popUp.branch+'"]').find('.crm-dropdown')
                            innerDropDown.addClass('open');
                            instanceCurrentPopUp.data.get()
                        }

                        // format date_time
                        $('.modal .bpm_modal_dialog .element[data-type="settings"]').each(function(i, ul){
                            ProcessObj.BPM.prepateFilterDateTime(ul);
                        });

                        modalDialog.afterUpdateContent();
                    }
                })
            },
            onChangeTimerElement : function(){
                var _this = $(this);
                var process = new Process();

                process.BPM.changeParamsContent(_this, 'timer', function(data){
                    if(data.status == true){
                        var e_dinamic = _this.closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block li.dinamic');
                        _this.closest('.element[data-type="params"][data-module="process"]').find('.panel-body .inputs-block li:last').after(data.params_result);
                        e_dinamic.remove();


                        Global.initSelects();
                        ProcessObj.activateDropdowns();

                        $('.modal .element[data-name="timer"] .settings-menu .selectpicker li>a').on('click', function(e) {
                            $(this).closest('.bootstrap-select.open').removeClass('open');
                        });

                        modalDialog.afterUpdateContent();
                    }
                })
            },
            // onClickDialogPVEdit : function(el){
            //     processView.editData(el, this);
            // },
            onClickOperatorRemove : function(){
                var bpmOperator = $(this).parent();
                var nameOperator = bpmOperator.data('name');
                unique_index = bpmOperator.data('unique_index');
                var arrowEnd = $('svg.arrows path.arrow[arr-end="'+unique_index+'"]'),
                    arrowBegin = $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]');
                branch = arrowBegin.attr('branch-end');

                if (branch && nameOperator !='and' && nameOperator !='condition') { // delete brunch number if delete 'and' operator
                    if ($('.bpm_operator[data-unique_index="'+arrowEnd.attr('arr-begin')+'"')!='and') {
                        arrowEnd.attr('branch-end',branch+'');
                    }
                    process.BPM.operationParams.delete(unique_index, function(unique_index) {
                        ProcessObj.BPM.deleteCallback(unique_index);
                    });
                } else if ((nameOperator =='and' || nameOperator =='condition') && arrowBegin.length>1) {
                    var _function = function(){

                        var connectToInd = bpmOperator.attr('end-branches');
                        ProcessObj.BPM.markOperators(unique_index, connectToInd);
                        var indexes = [];
                        bpmOperator.removeAttr('mark');
                        $('.bpm_operator[mark]').each(function(){
                            var inInd = $(this).data('unique_index');
                            indexes.push(inInd);
                        });
                        andGridCol = parseInt(bpmOperator.attr('gridcol'));
                        //indexes = ProcessObj.BPM.getAllBranchIndexes(unique_index, 'all');
                        var indAfterHelper = ProcessObj.defineNextOperator(connectToInd),
                            deletedInd = unique_index;
                        if(indexes.length != 0){
                            ProcessObj.BPM.deleteQeue(indexes, function(unique_index){
                                //ProcessObj.BPM.deleteCallback(unique_index);
                                ProcessObj.BPM.deleteQeueCallback(deletedInd, indAfterHelper);
                                moveCounter = parseInt($('.bpm_operator[data-unique_index="' + indAfterHelper + '"').attr('gridcol')) - parseInt(andGridCol);
                                for(i = 0; i < moveCounter; i++){
                                    ProcessObj.recountNextOperators(indAfterHelper, 'left');
                                }
                            });
                        } else {
                            nextOp = arrowBegin.attr('arr-end');
                            arrowEnd.attr('arr-end', nextOp + '');
                            moveCounter = parseInt($('.bpm_operator[data-unique_index="' + nextOp + '"').attr('gridcol')) - parseInt(andGridCol);
                            for(i = 0; i < moveCounter; i++){
                                ProcessObj.recountNextOperators(nextOp, 'left');
                            }
                        }
                        arrowBegin.remove();
                        process.BPM.operationParams.delete(unique_index, function(unique_index){
                            var indHelper = $('.bpm_operator[data-unique_index="' + unique_index + '"').attr('end-branches');
                            ProcessObj.BPM.deleteCallback(unique_index);
                            if(indHelper){
                                process.BPM.operationParams.delete(indHelper, function(indHelper){
                                    ProcessObj.BPM.deleteCallback(indHelper);
                                });
                            }
                        });
                        /*$.each(indexes,function(i,val){
                         $('svg.arrows path.arrow[arr-begin="'+val+'"]').remove();
                         $('.bpm_operator[data-unique_index="'+val+'"]').remove();
                         });*/

                        /*$('svg.arrows path.arrow[arr-end="'+$(this).parent().attr('end-branches')+'"]').each(function(){
                         $(this).removeAttr('branch-end');
                         });*/


                        //andGridCol = parseInt($(this).parent().attr('gridcol'));
                        //andGridRow = parseInt($(this).parent().attr('gridrow'));
                    }

                    Message.show([{'type':'confirm', 'message': Message.translate_local('Operators inside the branches will be removed')}], false, function(_this_c){
                        if($(_this_c).hasClass('yes-button')){
                            modalDialog.hide();
                            _function();
                            ProcessObj.branchSignatures();
                        }
                    }, Message.TYPE_DIALOG_CONFIRM);

                } else {
                    if ($(this).parent().data('name')=='task') {
                        prevTaskInd = $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').attr('arr-end');
                        if (prevTaskInd && $('.bpm_operator[data-unique_index="'+prevTaskInd+'"]').data('name')=='agreetment') {
                            process.BPM.operationParams.delete(prevTaskInd, function(index) {
                                ProcessObj.BPM.deleteCallback(index);
                            });
                        }
                    }
                    process.BPM.operationParams.delete(unique_index, function(index) {
                        ProcessObj.BPM.deleteCallback(index);
                    });
                }
                if ($('.bpm_operator[end-branches="'+$(this).parent().data('unique_index')+'"]').length>0) {
                    ProcessObj.BPM.branchesRestore();
                }
                //var nextInd = $('svg.arrows path.arrow[arr-begin="'+unique_index+'"]').attr('arr-end'); //#multipath remeke for
                ProcessObj.branchSignatures();
            },
            onClickSaveParams : function(){
                var _func_save,
                    $this = $(this),
                    _element_name = $this.closest('.element[data-type="params"]').data('name'),
                    _unique_index = $this.closest('.element[data-type="params"]').data('unique_index'),
                    _this = $('.inputs-block'),
                    arrows = $('svg.arrows');

                _func_save = function() {

                    ProcessObj.BPM.unmarkOperators();
                    ProcessObj.BPM.operationParams.save(_this, _element_name, _unique_index, function(data){

                        ProcessObj.refreshStatus(data.schema, 'all');
                        ProcessObj.BPM.updateProcessStatus(data.process_status);
                        if(_this.closest('.modal-dialog').find('header .client-name .editable-field').length > 0){
                            if(ProcessObj.BPM.operationParams.settings[_unique_index].status == "unactive"){
                                ProcessObj.titleOperatorRename(_unique_index, _this.closest('.modal-dialog').find('header .client-name .editable-field').text());
                            }
                        }

                        operatorsParalelArr = [];
                        $.each($('.bpm_operator[mark="marked"]'), function(){
                            operatorsParalelArr.push($(this).data('unique_index'));
                        });
                        ProcessObj.BPM.deleteQeue(operatorsParalelArr, function(unique_index){});

                        if(data.messages){
                            Message.show(data.messages, false);
                        } else {
                            modalDialog.hide();

                            ProcessObj.BPM.saveSchema();
                        }

                    });
                    ProcessObj.recountRespBlocks();
                    ProcessObj.BPM.recountArrows();
                    ProcessObj.branchSignatures();
                }

                switch (_element_name) {
                    case 'and': {
                        //change number of branches for next remove
                        function compareOffSet(arrowA, arrowB) {
                            return parseInt($(arrowA).attr('d').split(' ')[5]) > parseInt($(arrowB).attr('d').split(' ')[5]) ? 1 : -1;
                        }

                        arrows.find('path[branch][arr-begin="'+_unique_index+'"]').sort(compareOffSet).each(function (index) {
                            $(this).attr('branch',index+1);
                        });
                        var branchesCount = _this.find('.element[data-type="number_branches"]').val();
                        ProcessObj.branchesManage(branchesCount, _unique_index, _func_save);
                        return;
                    }
                    case 'condition': {
                        var _function,
                            arrowBegin = arrows.find('path.arrow[arr-begin="'+_unique_index+'"]'),
                            bpmOperator = $('.bpm_operator[data-unique_index="'+_unique_index+'"]'),
                            branchesCount = _this.find('.counter').length,
                            arrowsCount = arrowBegin.length,
                            endBranches = bpmOperator.attr('end-branches');

                        _function  = function(){

                            if(branchesCount > 1 && !endBranches){
                                ProcessObj.BPM.createHelperAnd(_unique_index);
                                var pathEnd = arrowBegin.attr('arr-end');
                                bpmOperator.attr('end-branches', pathEnd + '');
                                var nextOp = ProcessObj.defineNextOperator(pathEnd);
                                ProcessObj.recountNextOperators(nextOp, 'right');
                            }
                            for(i = 1; i < arrowsCount + 1; i++){
                                if(_this.find('.inputs-group[branch="' + i + '"]').length < 1){
                                    var branch = arrows.find('path.arrow[arr-begin="' + _unique_index + '"][branch="' + i + '"]');
                                    if(branch && !branch.attr('branch-end')){
                                        ProcessObj.BPM.markOperators($(branch).attr('arr-end'), bpmOperator.attr('end-branches'));
                                        branch.remove();
                                    } else {
                                        branch.remove();
                                    }
                                }
                            }
                            if(branchesCount == 1 && endBranches){
                                $('.bpm_operator[data-unique_index="' + endBranches + '"]').attr('mark', 'marked');
                                var pathes = arrows.find('path.arrow');
                                var leave = pathes.filter('[arr-begin="' + endBranches + '"]');
                                pathes.filter('[arr-end="' + endBranches + '"]').attr('arr-end', leave.attr('arr-end'));
                                leave.remove();
                                bpmOperator.removeAttr('end-branches');
                            }
                            var pathClone = arrowBegin.first().clone(true),
                                $listOfCounter = _this.find('.counter');

                            $.each($listOfCounter, function(index){
                                var $inpGrp = $(this).closest('li.inputs-group'),
                                    valFC = $inpGrp.find('.column>.element').not('select.element').not('.settings'),
                                    branch = $inpGrp.attr('branch');

                                valFC = valFC.is('input') ? valFC.val() : valFC.find('button').text();
                                if(!index){
                                    var pathes = arrows.find('path.arrow[arr-begin="' + _unique_index + '"]');
                                    var currentPath = ((pathes.is('[branch=1]')) ? pathes.filter('[branch=1]') : pathes.not('[branch]'));
                                    if(!pathes.not('[branch]').length
                                        && $inpGrp.closest('ul').find('li[branch]').length > 0
                                        && !pathes.filter('[branch=1]').length){
                                        currentPath = pathes.filter('[branch=2]');
                                    }
                                    ;
                                    currentPath.attr({
                                        title: valFC,
                                        branch: 1
                                    });
                                }
                                else {
                                    var endBranches = $('.bpm_operator[data-unique_index="' + _unique_index + '"]').attr('end-branches'),
                                        pathSecClone = pathClone.clone(true).attr({
                                            title: valFC,
                                            branch: index + 1,
                                            'arr-end': endBranches
                                        }),
                                        $path = arrows.find('path.arrow[arr-begin="' + _unique_index + '"]');

                                    if(branch){
                                        $path.filter("[branch='" + branch + "']").attr({
                                            title: valFC,
                                            branch: index + 1
                                        });
                                        if(!$path.is("[branch='" + branch + "']")){
                                            arrows.prepend(pathSecClone);
                                        }
                                    }
                                    else arrows.prepend(pathSecClone);
                                }
                            });

                            var listOfInnerPath = arrows.find('path.arrow[arr-begin="' + _unique_index + '"]');
                            if($listOfCounter.length == 1 && listOfInnerPath.length != $listOfCounter && !ProcessObj.BPM.isChild(_unique_index)){
                                listOfInnerPath.attr('branch-end', 'main');
                                ProcessObj.BPM.reDrawOfArrows = null;
                            } else {
                                var nextElement = $('.element[data-unique_index="' + $('path[arr-begin="' + _unique_index + '"]').attr('arr-end') + '"]');
                                if(nextElement.is('.and_helper')){
                                    listOfInnerPath.not('[branch="1"]').filter('[arr-end="' + nextElement.attr('data-unique_index') + '"]').attr('branch-end', 'true');
                                    if(listOfInnerPath.length == 1) listOfInnerPath.removeAttr('branch');
                                }
                                ProcessObj.BPM.reDrawOfArrows = true;
                            }
                        };

                        if (branchesCount != arrowsCount && branchesCount < arrowsCount){
                            Message.show([{
                                'type': 'confirm',
                                'message': Message.translate_local('Operators inside the branches will be removed')
                            }], false, function(_this_c){
                                if($(_this_c).hasClass('yes-button')){
                                    modalDialog.hide();
                                    _function();
                                    _func_save();
                                }
                            }, Message.TYPE_DIALOG_CONFIRM);
                            return;
                        } else {
                            _function();
                        }

                        break;
                    }
                    case 'scenario': {
                        _this.find('textarea#code').val(bpmOperatorScript.getValue());
                        ProcessObj.scenario.init();
                        break;
                    }
                    default: {
                        break;
                    }
                }
                _func_save();
            },
            // Show operation params
            onClickShowParams : function(){ //:not(.ui-state-disabled)
                if ($(this).parent().hasClass('ui-state-disabled')) {
                    var permitionShow = ProcessObj.checkingShowParams($(this));
                    if (!permitionShow) {
                        return false;
                    }
                }
                Preloader.modalShow();
                var _this = $(this).parent();
                intervalID = setInterval(checking, 100);
                function checking() {
                    if ($( 'div.fake-backdrop' ).length>0) {
                        clearInterval(intervalID);
                        var data = process.BPM.operationParams.getOperationChevronData(_this);
                        process.BPM.operationParams.show(data, function(data){
                            if(data.status){
                                var $content = $($(data)[0].html);

                                EditView.replaceForLink();
                                HeaderNotice.refreshAllHeaderNotices();
                                niceScrollCreate($('.submodule-table'));

                                setTimeout(function () {
                                    var link = $('.add_list .operations>a'),
                                        key = link.closest('[data-type="params"]').attr('data-unique_index');

                                    if (key) {
                                        instanceCurrentPopUp = Global.groupDropDowns(10);
                                        instanceCurrentPopUp.init(link);
                                        instanceCurrentPopUp.listValuesOfFilters(link.closest('ul.inputs-block'), key, 10).load();
                                    }
                                    // niceScrollCreate($('.bpm_modal_dialog .dropdown-menu.inner.selectpicker'));
                                    modalDialog.afterUpdateContent();
                                },400);

                                var key = $content.find('[data-type][data-unique_index]').attr('data-unique_index');

                                if ($content.find('[data-name="scenario"]').length) {
                                    ProcessObj.scenario.init();
                                    ProcessObj.scenario.render();
                                    setTimeout(function () {
                                        $('[data-name="scenario"] .col-1 .column').css({
                                            'max-height': $(window).height() - 340
                                        })
                                    },400)
                                }

                                if (!ProcessObj.listOfElements) {
                                    ProcessObj.listOfElements = {}
                                }
                                ProcessObj.listOfElements[key] = {};
                                ProcessObj.activateDropdowns();

                                Global.addOperationInSDM();

                                //TODO: on test, I added this for participant in EV
                                Global.initHandler();

                                if (location.search.indexOf('unique_index')>=0) { // remove unique_index from URL
                                    var order = location.href.indexOf('&unique_index');
                                    window.history.pushState({} ,"",location.href.replace(location.href.substring(order,order.length),''));
                                }
                            }
                        });
                    }
                }
            },
            onClickProcessOpen : function(e){
                e.data.instance.open(this);
            },
            editView : {
                onClickSave : function($element){
                    var $view = $element.closest('.edit-view');

                    if ($view.find('.emoji-wysiwyg-editor').html() !== "") {
                        $view.find('.send_massage_activity').trigger('click');
                    }

                    var this_template = $view.data('this_template'),
                        id =  $view.data('id'),
                        bpm_block_length = $('.bpm_block').length,
                        parent_ev = ($view.data('parent_copy_id') && $view.data('parent_data_id'))

                    if(((this_template && !id) || bpm_block_length) && parent_ev == false){
                        EditView.save($view.data('copy_id'), null, function(data){
                            var process = new Process();
                            process.BPM.open(data.id, 'constructor', $element);
                        });
                    } else {
                        var ev = modalDialog.getInstanceFromStorage(EditView._interface) || EditView.createInstance();

                        ev.save($view.data('copy_id'));
                    }
                }
            },
        },
        removeResponsible : function (_this) {
            var $this = $(_this),
                targetUnit = $this.closest('.bpm_unit').prev();

            if (targetUnit.hasClass('element')) {
                var targetRows = parseInt($this.closest('.bpm_unit').prev().attr('rows'));

                $this.closest('.bpm_unit').find('.bpm_operator').each(function() {
                    var $prevRespons = $(this).closest('.bpm_unit').prev().find('.bpm_tree');
                    var newRow = parseInt($(this).attr('gridrow'))+targetRows;
                    if (newRow >= $(this).closest('.bpm_unit').prev().attr('rows')) {
                        $(this).closest('.bpm_unit').prev().attr('rows',newRow+1+'');
                    }
                    $(this).attr('gridrow',newRow+'').appendTo($prevRespons);
                });
                $this.closest('.bpm_unit').remove();
            } else {
                var targetUnit = $this.closest('.bpm_unit').next(),
                    thisRows = parseInt($this.closest('.bpm_unit').attr('rows')),
                    newRows = parseInt(targetUnit.attr('rows'))+thisRows;
                targetUnit.attr('rows',newRows+'');

                targetUnit.find('.bpm_operator').each(function() {
                    var newRow = parseInt($(this).attr('gridrow'))+thisRows-1;
                    $(this).attr('gridrow',newRow+'');
                });
                $this.closest('.bpm_unit').find('.bpm_operator').each(function() {
                    $(this).appendTo(targetUnit.find('.bpm_tree'));
                });
                $this.closest('.bpm_unit').remove();
            }
            ProcessObj.BPM.recountArrows();
            ProcessObj.BPM.saveSchema();
        },
        allMethod : function () {
            var event;

            ProcessObj.statusRightPanel();

            if (!window.backForward || Global.isBmpView()) {
                ProcessObj.init();
                ProcessObj.editOrViewProcess();
            }

            if(ProcessObj.is_bpm_view && ProcessObj.mode == ProcessObj.PROCESS_MODE_RUN){
                ProcessObj.BPM.bpmParamsRun(ProcessObj.PROCESS_BPM_PARAMS_ACTION_CHECK);
            }

            Participant.isExistResponsible = function(_this){
                var element_panel = $(_this).closest('.element[data-type="params"]');

                var responsible = $('.bpm_block .element[data-name="'+element_panel.data('name')+'"][data-unique_index="'+element_panel.data('unique_index')+'"]').closest('.element[data-type="responsible"]');

                if(responsible && responsible.length){
                    return true;
                }

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
            }


            // process-view edit
            event = 'li.modal_dialog[data-controller="process_view_edit"]';
            $(document).off('click', event);

            // убрали относительно задачи #918
            // $(document).on('keyup', function(e) {
            //     if (e.keyCode === 27) { // esc
            //         if ($('.sm_extension.checking_modal:visible').length) {
            //             return false;
            //         } else {
            //             modalDialog.hide();
            //         }
            //     }
            // });

            event = '.element[data-type="params"] .element[data-type="day_in_month"]+.bootstrap-select, .bootstrap-select';
            $('body').off('click', event).on('click', event, function(){
                var $menu,
                    _this = $(this);
                if (_this.prev().is('select') && _this.prev().find('option').not('.hide').length>10) {

                    $menu = _this.find('ul.dropdown-menu').height(240);
                    niceScrollCreate($menu);
                }
                if (_this.closest('[data-module="process"][data-name="data_record"]').length){
                    $('[data-type="value_field_name"] option[value="'+_this.prev().val()+'"]').addClass('hide');
                    var currentOptions = _this.parent().find('select option');
                    currentOptions.each(function(){
                        var item = $(this);
                        $('[data-type="value_field_name"]').parent().find('ul.dropdown-menu').each(function () {
                            var  li = $(this).find('li').eq(item.index());
                            li.attr('value',item.attr('value'));
                        });
                    });

                    $('[data-module="process"][data-name="data_record"] .element.col-1 .column_half:even select').each(function () {
                        var item = $(this);
                        currentOptions.filter('[value="'+ item.val() +'"]').addClass('hide');
                        _this.find('ul li[value="'+ item.val() +'"]').addClass('hide');
                    });
                }
            });

            $('body').on('click', function () {
                delete window.backForward;
            });

            $(window).on('resize', function(){
                ProcessObj.recountRespBlocks();
            });

            if (ProcessObj.this_template) {
                $('div.bpm_block').data('this_template','1').attr('data-this_template','1');
            } else {
                $('div.bpm_block').data('this_template','0').attr('data-this_template','0');
            }


            event = '.submodule-link .element_module+ul td';
            $(document).off('click', event);

            $(window).scroll(function() {
                $('.b_bpm_fix').css({'left': $(document).scrollLeft()});
            });
            window.onload = function () {
                $('.b_bpm_fix').css({'left': $(document).scrollLeft()});
            };
        },
        open : function (element) {
            var process = new Process();
            var sm_extension = $(element).closest('.list_view_block.sm_extension, .process_view_block.sm_extension');
            var sm_extension_data = $(element).closest('.sm_extension_data');
            var mode;

            if(sm_extension.data('this_template') == false){
                mode = ProcessObj.PROCESS_MODE_RUN;
            } else {
                mode = ProcessObj.PROCESS_MODE_CONSTRUCTOR;
            }

            process.BPM.open(sm_extension_data.data('id'), mode);
        },
        remove : function () {
            $('#content_container').attr('style','');
            $('.wrapper.bpm_process').removeClass('bpm_process');
        },
        destroy: function () {
            this.remove();
            Global.removeClass(document.querySelector('body'), 'bpm-module');
            if (ProcessEvents._instance) {
                delete ProcessEvents._instance.getFilter;
                Global.removeEvents(ProcessEvents._instance._events);
                ProcessEvents._instance = null;
            }
        }
    }
    // for(var key in _private) {
    //     _self[key] = _private[key];
    // }

    exports.ProcessEvents = ProcessEvents;
    exports.instanceCurrentPopUp = instanceCurrentPopUp;
})(window);


$(document).ready(function(){
    setTimeout(function(){
        ProcessObj.BPM.autoShowTask();
    }, 500);
})
