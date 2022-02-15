$.fn.initNewSelects = function() {
    $('.modal .edit-view[data-copy_id="8"] select').each(function(){
        if (!$(this).next().hasClass('bootstrap-select')) {
            $(this).selectpicker({style: 'btn-white',noneSelectedText: ' '});
        }
    });
    this.find('li.inputs-group:last-child .crm-dropdown > .dropdown-toggle').removeAttr('data-toggle').on('click',function(){
        $('.crm-dropdown.open').removeClass('open');
        $(this).parent().toggleClass('open');
    });
}

;(function (exports) {
    var _private, _public, _protected, Reports, ExtEditView, puclicExtEditView,
        callbacksarr = [],
        _self; //link for instance

    _protected = {

    };

    _self = {
        indicator: {
            onInit: function(e){
                var _this = this;
                Reports.Constructor.addElement(_this, 'data_analysis_indicator', function(data){
                    if(data.status == true){
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_data_analysis"] .element[data-type="block_panels"]').append(data.html).initNewSelects();
                        $('.element[data-type="block_data_analysis"] .inputs-block > li:last-child').hide();
                        var new_this = $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_data_analysis"] .element[data-type="block_panels"]:last')

                        // Indicator
                        Reports.Constructor.changeElement(new_this, 'indicator_add', {}, function(data){
                            Reports.Constructor.changeIndicators(new_this, data);
                            Reports.Constructor.InitNewSelects();

                            // Graph
                            Reports.Constructor.changeElement(new_this, 'graph', {}, function(data){
                                Reports.Constructor.changeGraphics(new_this, data, null);
                                Reports.Constructor.InitNewSelects();
                            });

                            Reports.Constructor.initSorting();
                            Reports.Constructor.triggerElements();

                        });
                        Reports.Constructor.initSorting();
                    }

                });
            },
            onAdd: function(e){
                var _this = this;
                Reports.Constructor.changeElement(_this, 'indicator_add', {}, function(data){

                    if(data.indicator_setting_indicator){
                        $(_this).closest('.element[data-type="settings"]').find('.element[data-type="setting"]:last').after(data.indicator_setting_indicator);
                    }


                    if(data.indicator_panel){
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_panel"] .reports-params .element[data-type="panel"]:last').after(data.indicator_panel);
                    }

                    count_settings = $(_this).closest('.element[data-type="settings"]').find('.element[data-type="setting"]').length;
                    if(count_settings >= 4) $(_this).hide();

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();
                });
            },
            onChangeDropDown: function(e){
                var _this = this;
                var index = $(_this).closest('.element[data-type="setting"]').index();

                Reports.Constructor.setIndicatorEntities(_this);

                Reports.Constructor.changeElement(_this, 'indicator_indicator', {}, function(data){
                    //indicator_panel
                    if(data.indicator_panel){
                        Reports.Constructor.changeIndicatorPanel(data.indicator_panel, null, index);

                    }
                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();
                });
            }
        },
        graph : {
            onClickAdd: function(){
                var _this = this;
                Reports.Report.addGraphData(_this, false, function(data){
                    Reports.Report.graphIndicator.add(_this, data);
                });
            },
            onClickRemove: function(){
                var _this = this;

                Reports.Report.graphIndicator.delete(_this);
            },
            onChangeDropDown: function(){
                //$(document).on('click', '.list_view_block[data-copy_id="8"] [data-type="settings"] ul>li', function(){
                //var _this = $(this).closest('ul').closest('li').find('select');
                var $this = $(this);
                //if ($this.index() == $this.find('[value="'+$this.val()+'"]').index()) return;

                Reports.Report.addGraphData($this, true, function(data){
                    Reports.Report.graphIndicator.change($this, data);
                });
                Reports.Report.selectsOptionsOrganize($this);
                $this.closest('.report-droptools').addClass('opened');
                setTimeout(function() {
                    $('.list_view_block[data-module="reports"] .crm-dropdown.opened').addClass('open').removeClass('opened');
                }, 300);
            },
            onInit: function() {
                Reports.Constructor.addGraphDialog();

                var event = ['click', '.modal-dialog .element[data-type="add_graph"]']
                $(document).off(event[0], event[1]).on(event[0], event[1], _self.graph.onAdd);
            },
            onAdd: function(){
                var _this = this;
                Reports.Constructor.addElement(_this, 'graph_block', function(data){
                    if(data.status == true){
                        var graph_block = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]');
                        if(graph_block.length >= 1){
                            if($(_this).closest('.modal-dialog').find('.element[data-type="position"]').val() == 'left'){
                                graph_block.last().find('li:first-child>select').trigger('change');
                                graph_block.last().before(data.html);
                            }else if($(_this).closest('.modal-dialog').find('.element[data-type="position"]').val() == 'right'){
                                graph_block.last().attr('forced','left').find('li:first-child>select').trigger('change');
                                graph_block.last().after(data.html);
                            }else{
                                graph_block.last().after(data.html);
                            }
                        } else {
                            var data_analysis_block = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="data_analysis"]');
                            data_analysis_block.before(data.html);
                        }
                        modalDialog.hide();
                    }

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.triggerElements();
                })
            },
            onChangeIndicator : function(){
                var _this = this;
                Reports.Constructor.setIndicatorEntities(_this);
            }
        },
        editView : {
            onClickRemoveBlock: function(e){
                var $this = $(this),
                    instance = e.data.instance,
                    $panelOfRight = $this.closest('.inputs-panel[data-position="right"]'),
                    $panelOfLeft = $this.closest('.inputs-panel[data-position="left"]'),
                    $panelOfBottom = $this.closest('.inputs-panel[data-position="botton"]');

                if ($panelOfRight.length>0){
                    $panelOfRight.prev().removeAttr('forced').find('li:first-child>select').trigger('change');
                } else if ($panelOfLeft.length>0) {
                    $panelOfLeft.next().find('li:first-child>select').trigger('change');
                } else if ($panelOfBottom.length>0 && $panelOfBottom.prev('.inputs-panel[data-position="left"]').length>0) {
                    $panelOfBottom.prev().attr('data-position','botton').find('li:first-child>select').trigger('change');
                } else if ($panelOfBottom.length>0 && $panelOfBottom.next('.inputs-panel[data-position="right"]').length>0) {
                    $panelOfBottom.next().attr('data-position','botton').find('li:first-child>select').trigger('change');
                }

                Reports.Constructor.removeBlock(this);
                Reports.afterViewChanges(true);
            },
            onChangeDataAnalysisIndicator_DropDown: function(){
                var unique_index,
                    _this = this,
                    $this = $(this),
                    $li = $this.closest('li'),
                    selNumb = $li.index();

                if ($this.is('.element[data-type="field_name"]') && selNumb==0) {
                    var valName = $(this).val();
                    if ($this.find('option[value="'+valName+'"]').data('num')==0 || valName == '__amount__') {
                        $li.addClass('not_numb');
                    } else {
                        $li.removeClass('not_numb');
                    }
                }
                unique_index = $this.closest('.element[data-type="block_panel"]').data('unique_index');

                Reports.Constructor.changeElement(this, 'data_analysis_indicator_settings', {}, function(data){
                    Reports.Constructor.changeIndicators(_this, data);
                    Reports.Constructor.changeGraphics(_this, data, unique_index);

                    //indicator_panel
                    if(data.indicator_panel){
                        Reports.Constructor.changeIndicatorPanel(data.indicator_panel, unique_index, null);
                    }

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();

                    Reports.Constructor.changeElement(null, 'update_output_elements', {}, function(data){
                        //graph_element
                        if(data.graph_element){
                            $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                                if($(_this).data('type') == 'field_name'){
                                    Reports.Constructor.changeGraphElement($(ul).find('.element[data-type="graph"]'), data.graph_element);
                                } else {
                                    var unique_index_g = $(ul).find('.element[data-type="settings"] .element[data-type="indicator"]').val();
                                    if(unique_index == unique_index_g){
                                        Reports.Constructor.changeGraphElement($(ul).find('.element[data-type="graph"]'), data.graph_element);
                                    }
                                }
                            });
                        }

                        Reports.Constructor.InitNewSelects();
                        Reports.Constructor.layoutIndicators();
                        Reports.Constructor.triggerElements();

                        Reports.afterViewChanges(true);
                    }, null, true);


                });
            },
            onChangeDataAnalysisParam_DropDown: function(){
                var _this = this;
                var unique_index = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');

                var _this_pmci = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]');

                if($(_this).val() == '__id__' || $(_this).data('selected') == '__id__'){

                    $(this).closest('ul.ui-sortable').find('select[data-type="module_copy_id"]').each(function(){
                        $(this).prop('disabled', 'disabled');
                        $(this).parent().next().find('.dropdown-menu').addClass('hide');
                    });
                    Reports.Constructor.changeElement(_this_pmci, 'data_analysis_param_module', {}, function(data){
                        //data_analysis_indicator_module_params
                        // data_analysis. Параметры
                        if(data.data_analysis_indicator_module_params){
                            $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_panel"][data-data_analysis_type="data_analysis_indicator"]').each(function(i, ul){
                                var element_module = $(ul).find('.element[data-type="module_copy_id"]');
                                var element_new = $(data.data_analysis_indicator_module_params);

                                var value_copy_id = element_module.val();
                                if(value_copy_id){
                                    var is_set = element_new.find('.element[data-type="module_copy_id"] option[value="'+value_copy_id+'"]').length;
                                    if(is_set){
                                        element_new.find('.element[data-type="module_copy_id"]').val(value_copy_id);
                                        element_new = element_new.find('.element[data-type="module_copy_id_block"]');
                                        $(ul).find('.element[data-type="module_params"] .element[data-type="module_copy_id_block"]').after(element_new).remove();
                                    } else {
                                        $(ul).find('.element[data-type="module_params"]').after(element_new).remove();
                                    }
                                } else {
                                    $(ul).find('.element[data-type="module_params"]').after(element_new).remove();
                                }

                                Reports.Constructor.InitNewSelects();
                                Reports.Constructor.layoutIndicators();
                                Reports.Constructor.triggerElements();
                            })


                            var isset_modules = false;
                            var parent_module_copy_id = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]').val();
                            var indicator_block_panels = $('.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"]');

                            indicator_block_panels.find('.element[data-type="module_copy_id"]').each(function(i, ul){
                                if($(ul).val() == parent_module_copy_id){
                                    isset_modules = true;
                                    return false;
                                }
                            });

                            // update "field_name" list
                            if(isset_modules){
                                Reports.Constructor.changeElement(_this, 'data_analysis_indicator_settings', {}, function(data_f){

                                    indicator_block_panels.find('.element[data-type="module_copy_id"]').each(function(i, ul){

                                        if($(ul).val() == parent_module_copy_id){
                                            var element = $(data_f.data_analysis_indicator_settings).find('.element[data-type="field_name"]').closest('li');
                                            var field_name = $(ul).closest('.element[data-type="block_panel"]').find('.element[data-type="settings"] .element[data-type="field_name"]').val();
                                            element.find('.element[data-type="field_name"]').val(field_name);
                                            $(ul).closest('.element[data-type="block_panel"]').find('.element[data-type="settings"] .element[data-type="field_name"]').closest('li').after(element).remove();
                                        }
                                    });

                                    Reports.Constructor.InitNewSelects();
                                    Reports.Constructor.layoutIndicators();
                                    Reports.Constructor.triggerElements();
                                    Reports.Constructor.UpdateOutputElementsAll(_this_pmci)
                                });

                            } else {
                                Reports.Constructor.UpdateOutputElementsAll(_this_pmci)
                            }
                        }
                    });

                } else {
                    Reports.Constructor.UpdateOutputElementsAll(_this_pmci)
                }

                $(_this).data('selected', $(_this).val());

            },
            onClickIndicatorTitle: function(){
                var $editDropdown = $(this).next().find('.edit-dropdown');
                $editDropdown.addClass('opened');
                var value = $(this).text();
                $editDropdown.find('pre.invisi').text(value);
                $editDropdown.find('.form-control').val(value).width($editDropdown.find('pre.invisi').width()+2).select();
            },
            onClickIndicatorTitle_Save: function(){
                $(this).closest('.editable-block').find('.editable-field').text($(this).closest('ul').find('.form-control').val());
            },
            /* FILTERs*******************************************/
            onFilterChange: function(){
                var _this = this;

                Reports.Constructor.changeElement(this, 'filter_module_params', {}, function(data){
                    //filter_field_params
                    if(data.filter_field_params){
                        $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="field_params"]').after(data.filter_field_params).remove();
                    }
                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();
                    Reports.Constructor.Filter.changedFieldName($(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="field_name"]'), function(){
                        Reports.Constructor.UpdateOutputElements();
                    });
                });
            },
            onFilterChange_DropDown: function(){
                var _this = this;
                Reports.Constructor.Filter.changedFieldName(_this, function(){
                    Reports.Constructor.UpdateOutputElements();
                });
            },
            onChangeConditionValue: function(){
                Reports.Constructor.UpdateOutputElements();

                var $this = $(this),
                    value = $this.val(),
                    arrValues = value.split('.');

                $this.closest('.crm-dropdown').parent().closest('[data-type="settings"]').addClass('opened');
                if (isNaN(arrValues[0]) && arrValues.length>1 || !value.length) {
                    $this.val($this.data('old-value'));
                } else {
                    $this.data('old-value', value);
                }

            },
            onChangeConditionValue_DropDown: function(){
                var _this = this;
                var block_panel = $(_this).closest('.element[data-type="block_panel"]');
                var copy_id = block_panel.find('.element[data-type="module_copy_id"]').val();
                var field_name = block_panel.find('.element[data-type="field_name"]').val();
                var condition_value = block_panel.find('.element_filter[data-name="condition"]').val();

                if(!$(_this).val()){
                    Reports.Constructor.Filter.clearConditionValue(_this);
                }

                Reports.Constructor.Filter.setConditionValue(_this, copy_id, field_name, condition_value, function(){
                    Reports.Constructor.UpdateOutputElements();
                });
            },
            onChangePeriodGraph: function(){
                var _this = this;

                Reports.Constructor.addElement(this, 'graph_element', function(data){
                    if(data.html){
                        Reports.Constructor.changeGraphElement($(_this).closest('.element[data-type="block"]'), data.html);
                        Reports.afterViewChanges(true);
                    }
                });
            },

            onChangeDataAnalysisParam: function(){
                var _this = this;
                $(this).closest('ul.ui-sortable').find('select[data-type="module_copy_id"]').each(function(){
                    $(this).prop('disabled', 'disabled');
                    $(this).parent().next().find('.dropdown-menu').addClass('hide');
                });
                Reports.Constructor.changeElement(this, 'data_analysis_param_module', {}, function(data){
                    //data_analysis_param_settings
                    if(data.data_analysis_param_settings){
                        $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="settings"]').after(data.data_analysis_param_settings).remove();
                    }
                    //data_analysis_indicator_module_params
                    if(data.data_analysis_indicator_module_params){
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_panel"][data-data_analysis_type="data_analysis_indicator"]').each(function(i, ul){
                            $(ul).find('.element[data-type="module_params"]').after(data.data_analysis_indicator_module_params).remove();
                        })
                    }

                    Reports.Constructor.changeFilters(_this, data);
                    Reports.Constructor.changeIndicators(_this, data);
                    Reports.Constructor.changeGraphics(_this, data, null);

                    //indicator_panel
                    if(data.indicator_panel){
                        var unique_index_list = [];
                        $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .reports-params .element[data-type="panel"]').each(function(i, ul){
                            unique_index_list.push($(ul).data('unique_index'));
                        })

                        $.each(unique_index_list, function(key, unique_index){
                            Reports.Constructor.changeIndicatorPanel(data.indicator_panel, unique_index, null);
                        })

                    }

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.initSorting();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();
                });
            },
            onClickDataAnalysisIndicator_SettingOfFilters: function(e){
                var $this = $(this),
                    $submenu = $this.closest('.element[data-data_analysis_type="data_analysis_indicator"]').find('.element[data-type="filter_block_panels"]');

                $submenu.toggleClass('hide');
                $submenu.find('.selectpicker').selectpicker({style: 'btn-white'});
                $this.closest('.settings').css('z-index', '30');
                $this.closest('ul').addClass('hide').next().addClass('b_block').find('>li').removeClass('inputs-group');

                // Add filter for indicator
                var event = ['click', '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="add_filter"]'];
                $(document).off(event[0], event[1]).on(event[0], event[1], _self.editView.onClickDataAnalysisIndicator_addFilters);
            },
            onClickDataAnalysisIndicator_addFilters: function(e){
                var _this = this;
                Reports.Constructor.addElement(_this, 'filter_indicator', function(data){
                    if(data.status == true){
                        var $panels = $(_this).closest('.element[data-type="filter_block_panels"]'),
                            block_panel = $panels.find('>li:last');

                        if(block_panel.length) {
                            block_panel.after(data.html).initNewSelects();
                        } else {
                            $panels.find('>.btn-element').before(data.html).initNewSelects();
                        }
                        $panels.find('>li').removeClass('inputs-group');
                    }
                    Reports.Constructor.initSorting();
                    Reports.Constructor.triggerElements();
                    Reports.Constructor.runAfter(data);
                    Reports.afterViewChanges(true);
                })
            },
            onChangeDataAnalysisIndicator: function(){
                var _this = this;
                var unique_index = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');

                $(this).closest('ul.ui-sortable').find('select[data-type="module_copy_id"]').each(function(){
                    $(this).prop('disabled', 'disabled');
                    $(this).parent().next().find('.dropdown-menu').addClass('hide');
                });
                Reports.Constructor.changeElement(_this, 'data_analysis_indicator_module', {'this_ui_clear' : true}, function(data){
                    if(data.data_analysis_indicator_settings){
                        $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="settings"]').after(data.data_analysis_indicator_settings).remove();
                    }
                    Reports.Constructor.InitNewSelects();


                    Reports.Constructor.changeElement(_this, 'data_analysis_indicator_module', {'this_ui_clear' : false}, function(data){
                        if(data.indicator_panel){
                            Reports.Constructor.changeIndicatorPanel(data.indicator_panel, unique_index, null);
                        }
                        Reports.Constructor.changeFilters(_this, data);
                        Reports.Constructor.changeIndicators(_this, data);
                        Reports.Constructor.changeGraphics(_this, data, unique_index);
                        Reports.Constructor.InitNewSelects();
                        Reports.Constructor.layoutIndicators();
                        Reports.Constructor.triggerElements();
                    });

                });
            },
            onChangeTitle: function(){
                Reports.Constructor.changedDataAnalysisTitle(this);
            },
            onRemovePanel: function(){
                $(this).closest('li').hide();
                var $this = this;

                arrByDeleteFilter.push($this)

                if (deleteInterval == null) {
                    deleteInterval = setInterval(function() {
                        timer(arrByDeleteFilter[0]);
                        arrByDeleteFilter.shift();
                    }, 300);
                }

                function timer($this){
                    if (!$('.edit-view').find('li.to_remove').length) {
                        clearInterval(deleteInterval);
                        arrByDeleteFilter = [];
                        deleteInterval = null;
                    }
                    Reports.Constructor.removePanel($this);
                    return;
                }
            }
        }
    }

    puclicExtEditView = {

    }
    ExtEditView = {
        createInstance : function(_model_dialog){
            var Obj = function(){
                for(var key in EditView){
                    this[key] = EditView[key];
                }

                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return _self._instance = new Obj().constructor(_model_dialog);
        },
    }


    _private = {
        _instance: null,
        onAddPreloaderForGraph : function () {
            var $parent = $('.graph-area.element').parent(),
                $container = $('#list-table_wrapper_all'),
                spinner = Global.spinner;

            if (!$parent.find('>'+spinner.selector).length) {
                $parent.addClass('graph-set-preloader').append(spinner.clone().first());
            }

            $('#content_container').addClass('report-parent-graph');
            $container.addClass('set-preloader');

            if (!$container.find(spinner.selector).length && !$container.next().filter('.b-spinner:visible').length) {
                $container.addClass('init-preloader center-position').append(spinner.clone().first());
            }
        },
        onEditViewConstructorShow : function (e) {
            // $(document).off('change', '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="period"]');
            var instanceReport = e.data.instance,
                instanceEditView = EditView.createInstance(modalDialog.createInstance()).setParent(ViewType.getCurrentInstance());

            instanceReport.status_opening_ev = true;
            instanceReport.openCard(this, instanceEditView);
        },
        onSave : function(){
            var _this = this,
                saveInterval = setInterval(function() { timer(_this); }, 100);

            function timer(_this){
                if ($('.edit-view').find('li.to_remove').length==0) {
                    clearInterval(saveInterval);
                    var $parent = $('.report-content .graph-area.element').parent();

                    if (!$parent.find('>'+Global.spinner.selector).length) {
                        $parent.addClass('graph-set-preloader').append(Global.spinner.clone());
                    }

                    Reports.Constructor
                        .createInstance()
                        .setElementHtml(_this)
                        .save(_this);
                    return;
                }
            }
        },
        onAddFilter : function(){
            var _this = this;
            Reports.Constructor.addElement(_this, 'filter', function(data){
                if(data.status == true){
                    $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_filter"] .element[data-type="block_panels"]').append(data.html).initNewSelects();
                }
                Reports.Constructor.initSorting();
                Reports.Constructor.triggerElements();
            })
        },
        onArrayIndicatorsOnAdd : function(){
        var _this = this;
        Reports.Constructor.addElement(_this, 'indicator_block', function(data){
            if(data.status == true){
                $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"]:eq(0)').before(data.html).initNewSelects();
                $(_this).hide();
            }
            Reports.Constructor.InitNewSelects();
            Reports.Constructor.triggerElements();
            Reports.Constructor.layoutIndicators();
        });
        },
        onChangeFilter : function(){
            var _this = this;
            var copy_id = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="module"]').val();
            var condition_value = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();

            var data = {
                'selected_copy_id' : $(this).closest('.filter-box-panel').find('.element_filter[data-name="module"]').val(),
                'id' : $(this).closest('.list_view_block[data-module="reports"]').data('id'),
            };

            if(!$(_this).val()){
                Filter.clearCondition(_this);
                Filter.clearConditionValue(_this);
                Filter.setConditionValue(_this, copy_id, '', condition_value);
                return;
            }

            Filter.setCondition(_this, copy_id, $(_this).val(), function(data_value){
                var condition_value = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();
                Filter.setConditionValue(_this, copy_id, $(_this).val(), condition_value, data);
            }, data);
        },
        onChangeModule : function(e){
             e.data.instance.filter.changePanel(this);
        },
        onChangePeriod : function(e){
            var time,
                _this = this,
                instance = e.data.instance,
                $parent = $(_this).closest('.element[data-type="block"]').find('.graph-area.element').parent();

            if (!$parent.find('>'+Global.spinner.selector).length) {
                $parent.addClass('graph-set-preloader').append(Global.spinner.clone());
            }

            time = setTimeout(function () {
                clearInterval(time);

                Reports.Report.addGraphData(_this, true, function(data){
                    Events
                        .createInstance()
                        .setType(Events.TYPE_LOAD_GRAPH)
                        .setKey('ReportLoadGraph')
                        .setHandler(function (e) {
                            Events.removeHandler({ key: 'ReportLoadGraph', type: Events.TYPE_LOAD_GRAPH});

                            Reports.preloaderForGraph.remove($('#content_container'));
                        })
                        .run();

                    data.html_graph = data.html_graph.replace('graph-set-preloader','');
                    data.html_graph = data.html_graph.replace('<div class="b-spinner"><div class="loader"></div></div>', '');
                    Reports.Report.graphIndicator.change(_this, data);
                });
            }, 100);
        },
        onOpenReports : function(e){
            var tr,
                instance = e.data.instance,
                $this = $(this);

            if ($this.closest('.filter').length) {
                return this;
            }

            base_id = null;
            var relate = $this.closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant');
            if(relate.data('reloader') == 'parent'){
                base_id = relate.data('id');
            }

            $this
                .closest('.submodule-link')
                .find('.element_relate, .element_relate_this, .element_relate_participant')
                .val($this.find('.name').text())
                .data('id', $this.closest('tr').data('id'));

            $this
                .closest('.submodule-link')
                .find('.element_relate_participant')
                .data('ug_id', $this.closest('tr').data('ug_id'))
                .data('ug_type', $this.closest('tr').data('ug_type'));

            tr = $this.closest('tr');
            tr.closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant').html(tr.find('td').html());

            instance.setPreloader(Preloader.createInstance())
                .setShowPreloaderHandler(function () {
                    this.setRunning(false)
                        .setWhereContentHide(Preloader.TYPE_RELOAD_СONTENT_PAGE)
                        .setSpinnerPosition(Preloader.POSITION_SPINNER_CONTENT)
                        .setPlaceForSpinner($('#container'))
                        .run();
                });
            //подовжуємо
            // instanceGlobal.preloaderShow($this.closest('tr'));
            var instanceContent = ContentReload.createInstance();

            iPreloader.implements.call(instanceContent);
            instanceContent.setPreloader(instance.getPreloader());

            Global.getInstance().setContentReloadInstance(instanceContent)

            instanceContent
                .reDefinition()
                .showPreloader();

            Reports.Report.view($this.closest('tr'), $this.closest('tr').data('id'));
        },
        onChangeFilterCondition : function(){
            var _this = this;
            var copy_id = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="module"]').val();
            var field_name = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="field"]').val();
            var condition_value = $(_this).closest('.filter-box-panel').find('.element_filter[data-name="condition"]').val();

            if(!$(_this).val()){
                Filter.clearConditionValue(_this);
            }

            Filter.setConditionValue(_this, copy_id, field_name, condition_value);
        },
        onFilterByResponsible : function(e){
            var tr, relate,
                $this = $(this);

            base_id = null;
            relate = $this.closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant');
            if(relate.data('reloader') == 'parent'){
                base_id = relate.data('id');
            }

            $this
                .closest('.submodule-link')
                .find('.element_relate, .element_relate_this, .element_relate_participant')
                .val($this.find('.name').text())
                .data('id', $this.closest('tr').data('id'));

            $this
                .closest('.submodule-link')
                .find('.element_relate_participant')
                .data('ug_id', $this.closest('tr').data('ug_id'))
                .data('ug_type', $this.closest('tr').data('ug_type'));

            tr = $this.closest('tr');
            tr.closest('.submodule-link').find('.element_relate, .element_relate_this, .element_relate_participant').html(tr.find('td').html());

            Reports.Constructor.UpdateOutputElements();
        },
        onClickFieldParam : function () {
            var $this = $(this);
            $(this).closest('.crm-dropdown').removeClass('opened');
        },
        onClickFieldsParamsBtn : function () {
            var $this = $(this),
                $dropDown = $this.next(),
                $container = $dropDown.closest('[data-type="field_params"]');

            if ($dropDown.is('div.dropdown-menu')) {
                if ($container.offset().top + parseInt($dropDown.find('>ul').css('height')) >= $(window).height()-40) {
                    $dropDown.addClass('topAuto');
                }
            }
        },
        onClickLVTR : function () {
            instanceGlobal.preloaderShow($(this));
            Reports.Report.view(this, $(this).data('id'));
        },
        onClickSettingsMenu : function(e) {
            var $target = $(e.target);

            if (!$target.is('a')) {
                $(this).find('>li>.bootstrap-select.open').removeClass('open');
            }
        },
        onClickDialog : function(){
            var url,
                id = $('.sm_extension_data').attr('data-id'),
                pageSize = $('.pagination_size').val(),
                page = $('.pagination [name="page"]').val();

            url = '/module/reports/view/8?id=' + id + '&page_size='+ pageSize+'&page=' + page;
            var instanceEditView = EditView.createInstance();

            //TODO: change it in inner object with instance
            instanceGlobal.currentInstance._open_sub_link = true;

            instanceEditView
                .setUrlAfterSave(url)
                .editCard(this, null, function(data){
                this.runAfterEditCardLV(data);
            });
        },
        onKeyDownGraphInput : function(e){
            if (e.keyCode == 13) {
                $(this).closest('.editable-block').find('.editable-field').text($(this).val());
                $(this).closest('.edit-dropdown').removeClass('open');
            } else if (e.keyCode == 27) {
                $(this).closest('.edit-dropdown').removeClass('open');
            } else {
                return (e.keyCode);
            }
            return false;
        }
    };

    _public = {
        count_loaded_graph_ev: null,

        constructor: function () {
            this._type = 'reports';

            if (!Message.locale.dateFormats) {
                return;
            }

            this.events() // register events
                .allMethod() // все события

            this.run();
            return this;
        },
        run: function () {
            iModule.implements.call(this);

            var urlParams = Url.getParams(location.href) || {};
            if (!urlParams.id) return;

            this.search = Search.createInstance();
            this.filter = Filter.createInstance();

            iPreloader.implements.call(this);
            this.setPreloader(Preloader.createInstance());

            Search.setCommonInstance && Search.setCommonInstance(null);

            this.reDefinition();
        },
        setEmits: function () {
            var _this = this;

            Events
                .createInstance()
                .setType(Events.TYPE_DESTROY)
                .setKey('ReportsDestroy')
                .setHandler(function (e) {
                    _this.destroy();
                    return true;
                })
                .run();

            return this;
        },
        setCountLoadedGraph: function (data) {
            this.count_loaded_graph_ev = this.count_loaded_graph_ev || [];

            if (!data) {
                this.count_loaded_graph_ev = [];
            } else {
                this.count_loaded_graph_ev.push(data);
            }

            return this;
        },
        getCountLoadedGraph: function () {
            return this.count_loaded_graph_ev ? this.count_loaded_graph_ev.length : 0;
        },
        //TRUE
        editCard: function (_this, instanceEditView) {
            this.openCard(_this, instanceEditView);

            return this;
        },
        openCard: function (_this, instanceEditView) {
            //static
            var instance = Reports.getInstance();

            Reports.Constructor.edit(_this, function(data){
                instance.setCountLoadedGraph(null);

                Reports.Constructor.runAfter(data, instanceEditView);

                var count_graphs = $('.edit-view').find('[data-element_type="graph"]').find('.graph-area').length;

                if (!count_graphs) {
                    instanceEditView.afterLoadView();
                } else {
                    var time = setInterval(function () {
                        var count_loaded = instance.getCountLoadedGraph();

                        if (count_loaded >= count_graphs) {
                            clearInterval(time);
                            instance.setCountLoadedGraph(null);
                            instanceEditView.afterLoadView();
                            Reports.preloaderForGraph.remove($('.edit-view'));
                        }
                    }, 150);
                }
            });
            Preloader.modalShow();

            return this;
        },
        onClickFilterInstallSpan : function($element){
            var data = {
                'id' : $element.closest('.list_view_block[data-module="reports"]').data('id'),
            };
            Filter.show($element.closest('.sm_extension').data('copy_id'),
                $element.closest('.filter-install').data('filter_id'),
                $element.closest('.filter-install').data('name'),
                $element.closest('.filter-install').find('span').text(),
                data
            );
        },
        reDefinitionTools(instance) {
            instance.print = extTools.print;
            instance.saveToPdf = extTools.saveToPdf;
            instance.saveToExcel = extTools.saveToExcel;
            //instance
            return this;
        },
        reDefinition: function () {
            var _showPreloader,
                _this = this;

            _showPreloader = function () {
                var $list = $('.element[data-element_type="graph"]'),
                    preloaderInstance = Preloader.createInstance();

                $.each($list, function (key, value) {
                    $(this).addClass('report-parent-graph');
                    $(this).find('.graph-area').addClass('set-preloader graph-set-preloader');
                    $(this).find('.graph-area').append(Preloader.spinner.getElement());
                })

                this._contentReload = instanceGlobal.contentReload.createInstance();

                iPreloader.implements.call(this._contentReload);

                this._contentReload.setPreloader(preloaderInstance);

                preloaderInstance
                    .setPriorityDisable(false)
                    .setWhereContentHide(Preloader.REPORT)
                    .run()

                return this;
            }

            this.search.apply = function () {
                var url = this.getFullUrl(),
                    contentReload = this._contentReload;

                Global.getInstance().setContentReloadInstance(contentReload);

                contentReload.hidePreloader = function () {
                    this.preloader && this.preloader.hide();
                    Reports.preloaderForGraph.remove($('#content_container'));
                }

                contentReload
                    .prepareVariablesToGeneralContent()
                    .setUrl(url)
                    .run();

                return this;
            };
            this.search.showPreloader = function () {
                _showPreloader.call(this);
                return this;
            };
            this.filter.changePanel = function(_this){
                var filter_box_panel = $(_this).closest('.filter-box-panel');

                data = {
                    'this_template' : 0,
                    'selected_copy_id'  : filter_box_panel.find('.element_filter[data-name="module"]').val(),
                    'id' : $(_this).closest('.list_view_block[data-module="reports"]').data('id'),
                };

                $.get(Global.urls.url_filter_add_panel+'/'+$(_this).closest('.sm_extension').data('copy_id'), data, function(data){
                    filter_box_panel
                        .after(data.data)
                        .remove();
                    $('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                }, 'json').done(function() {

                });
                $('.crm-table-wrapper').getNiceScroll().remove();
                niceScrollInit();
            };

            this.filter.apply = function(destination){
                    var other_params = {'id' : $('.sm_extension').data('id')},
                        get_user_storage_url = Global.urls.get_user_storage_url;

                    Global.urls.get_user_storage_url = '/module/history/getUserStorageUrl/8';

                    loadModule($('.list_view_block[data-module="reports"]').data('copy_id') + '_' +  $('.list_view_block[data-module="reports"]').data('id'), destination, other_params, function(url){
                        var url = url.split("?");
                        url = '/module/reports/view/8?' + url[1];

                        var vars = {
                            'selector_content_box' : '#content_container'
                        }

                        instanceGlobal.contentReload
                            .clear()
                            .createInstance()
                            .setVars(vars)
                            .setUrl(url)
                            .setCallBackComplete(function (json) {
                                Reports.preloaderForGraph.remove($('#content_container'));
                            })
                            .loadPage();

                        Global.urls.get_user_storage_url = get_user_storage_url;
                    });
                };

            this.filter.save = function ($this) {
                var data, id,
                    $element = $this,
                    $filterBox = $('.filter-box'),
                    _this = this,
                    destination = $element.closest('.sm_extension').data('page_name'),
                    params = [];

                $('.filter-box-panels .filter-box-panel').each(function(i, ul){
                    var name = $(ul).find('.element_filter[data-name="field"]').val();
                    var condition_value = [];
                    $(ul).find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                        if($(this).hasClass('element_relate') || $(this).hasClass('element_relate_this')){
                            condition_value.push($(ul).data('id'));
                        } else if($(this).hasClass('element_relate_participant')){
                            condition_value.push($(ul).data('ug_id'));
                            condition_value.push($(ul).data('ug_type'));
                        } else {
                            condition_value.push($(ul).val());
                        }
                    });
                    if(name){
                        params.push({
                            'copy_id' : $(ul).find('.element_filter[data-name="module"]').val(),
                            'name' : name,
                            'condition' : $(ul).find('.element_filter[data-name="condition"]').val(),
                            'condition_value' : condition_value,
                        })
                    }
                })
                id = $('.filter-box').data('filter_id');

                data = {
                    'id' : id,
                    'copy_id' : $('.list_view_block[data-module="reports"]').data('copy_id'),
                    'reports_id' : $('.list_view_block[data-module="reports"]').data('id'),
                    'title' : $('.filter-box').find('.element_filter[data-name="filter_title"]').val(),
                    'params' : params,
                    'view' : $filterBox.find('.element_filter[data-name="filter_view"]').val()
                }

                $.post(Global.urls.url_filter_save +'/'+$('.filter-box').closest('.sm_extension').data('copy_id'), {'data' : data}, function(data){
                    if(data.status == true){
                        _this.showPreloader();

                        $('.filter').hide().find('.filter-box-container').empty();
                        $('ul.filter-menu .filter-btn-set').remove();
                        $('ul.filter-menu').append(data.menu_list);

                        if(id){
                            if(id != data.filter_id) {
                                Filter.updateInLocalStorage(destination, data.copy_id, data.filter_id_old, data.filter_id, function () {
                                    _this.apply(destination);
                                });
                            } else {
                                    _this.apply();
                                }
                        } else {
                            _this.set(data.copy_id, null, data.filter_id, destination);
                        }
                    } else {
                        Message.show(data.messages, false);
                    }
                }, 'json');
            }
            this.filter.showPreloader = function ($element) {
                _showPreloader.call(this);

                return this;
            };
            this.filter.addPanel = function ($this) {
                var data = {
                    'this_template' : 0,
                    'selected_copy_id' : null,
                    'id' : $this.closest('.list_view_block[data-module="reports"]').data('id')
                };

                Filter.addPanel($this.closest('.sm_extension').data('copy_id'), data);
            }
            this.filter.create = function ($this) {
                var data = {
                    'this_template' : 0,
                    'id' : $this.closest('.list_view_block[data-module="reports"]').data('id'),
                };

                Filter
                    .showPreloaderInner($this)
                    .create($this.closest('.sm_extension').data('copy_id'), data);
            }

            return this;
        },
        events : function () {
            this._events = [
                { parent: document, selector: 'a.field-param', event: 'click', func: _self.onClickFieldParam},
                { parent: document, selector: '.edit_view_constructor_show', event: 'click', func: _self.onEditViewConstructorShow},
                { parent: document, selector: '.edit_view_report_constructor_btn-save', event: 'click', func: _self.onSave}, // Constructor: save
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-type="add_data_analysis_indicator"]', event: 'click', func: _self.indicator.onInit},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="add_indicator"]', event: 'click', func: _self.indicator.onAdd},
                { parent: document, selector: '.element[data-type="block_filter"] .element[data-type="add_filter"]', event: 'click', func: _self.onAddFilter},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"] .element[data-type="block_data_analysis"] .element[data-type="add_indicator_block"]', event: 'click', func: _self.onArrayIndicatorsOnAdd},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"] .element[data-type="block_data_analysis"] .element[data-type="add_graph_dialog"]',
                    event: 'click', func: _self.graph.onInit},
                /** * Params.* module_copy_id (change) */
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="module_copy_id"]',
                    event: 'change', func: _self.editView.onChangeDataAnalysisParam},
                /** * Indicators. * module_copy_id (change)*/
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="module_copy_id"]',
                    event: 'change', func: _self.editView.onChangeDataAnalysisIndicator},
                /** * Params. * field_name, type_date (change)*/
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="field_name"], ' +
                '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="type_date"]',
                    event: 'change', func: _self.editView.onChangeDataAnalysisParam_DropDown},
                /* Indicators. field_name, type_indicator (change)*/
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="field_name"], .element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="type_indicator"]',
                    event: 'change', func: _self.editView.onChangeDataAnalysisIndicator_DropDown},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="filter"] .element[data-type="module_copy_id"]',
                    event: 'change', func: _self.editView.onFilterChange},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"].element[data-element_type="filter"] .element[data-type="field_name"], .element[data-type="block"][data-module="reports"][data-element_type="data_analysis"] .element[data-type="filter_block_panels"] .element[data-type="field_name"]',
                    event: 'change', func: _self.editView.onFilterChange_DropDown},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"] .element_filter[data-name="condition_value"]',
                    event: 'change', func: _self.editView.onChangeConditionValue},
                { parent: document, selector: '.list_view_block[data-module="reports"] .element_filter[data-name="module"]',
                    event: 'change', func: _self.onChangeModule},
                // Show filter for indicator
                { parent: document, selector: '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="show_filters"]',
                    event: 'click', func: _self.editView.onClickDataAnalysisIndicator_SettingOfFilters},
                { parent: document, selector: '[data-module="reports"] .element_filter[data-name="field"]', event: 'change', func: _self.onChangeFilter},
                { parent: document, selector: '.list_view_block[data-module="reports"] .submodule-link td', event: 'click', func: _self.onOpenReports},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"] .element[data-type="remove_block"]',
                    event: 'click', func: _self.editView.onClickRemoveBlock},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="settings"] .element[data-type="indicator"], .element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="settings"] .element[data-type="color"]',
                    event: 'change', func: _self.indicator.onChangeDropDown},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="indicator"]',
                    event: 'change', func: _self.graph.onChangeIndicator},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="data_analysis"] .element[data-type="title"]',
                    event: 'change', func: _self.editView.onChangeTitle},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"] .element[data-type="remove_panel"]',
                    event: 'click', func: _self.editView.onRemovePanel},
                //period change
                { parent: document, selector: '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="period"]',
                    event: 'change', func: _self.onChangePeriod},
                // indicator add
                { parent: document, selector: '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="add_indicator"]',
                    event: 'click', func: _self.graph.onClickAdd},
                //indicator delete
                { parent: document, selector: '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="remove_indicator"]',
                    event: 'click', func: '_self.graph.onClickRemove'},
                //indicator change
                { parent: document, selector: '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="indicator"]',
                    event: 'change', func: _self.graph.onChangeDropDown},

                /**TOOLS**/
                { parent: document, selector: '.is-page-report .list_view_btn-print', event: 'click', func: _self.onClickPrint}, //print
                { parent: document, selector: '.is-page-report .list_view_btn-export_to_excel', event: 'click', func: _self.onClickExportToExcel}, //export_to_excel
                { parent: document, selector: '.is-page-report .list_view_btn-export_to_pdf', event: 'click', func: _self.onClickSaveToPdf}, //save_to_pdf

                /**FILTERS**/
                { parent: document, selector: '.element[data-type="block"][data-module="reports"].element[data-element_type="filter"] .element_filter[data-name="condition"], ' +
                '.element[data-type="block"][data-module="reports"].element[data-element_type="data_analysis"] .element[data-type="filter_block_panels"] .element_filter[data-name="condition"]',
                    event: 'change', func: _self.editView.onChangeConditionValue_DropDown},
                { parent: document, selector: '.element[data-type="block"][data-module="reports"].element[data-element_type="filter"] .submodule-link td',
                    event: 'change', func: _self.onFilterByResponsible},
                { parent: document, selector: '.list_view_block[data-module="reports"] .element_filter[data-name="condition"]',
                    event: 'change', func: _self.onChangeFilterCondition},
                // { parent: document, selector: '.list_view_block[data-module="reports"] .filter-install span',
                //     event: 'click', func: _self.onClickFilterInstallSpan},

                /**FILTERS==========**/
                { parent: document, selector: '.element[data-type="block"][data-module="reports"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="period"], ' +
                '.element[data-type="block"][data-module="reports"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="indicator"], ' +
                '.element[data-type="block"][data-module="reports"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="display_option"] ',
                    event: 'change', func: _self.editView.onChangePeriodGraph},
                { parent: document, selector: '.element[data-element_type="graph"] .editable-field , .element[data-element_type="indicator"] .editable-field',
                    event: 'click', func: _self.editView.onClickIndicatorTitle},
                { parent: document, selector: '.element[data-element_type="graph"] .editable-field+span .save-input, .element[data-element_type="indicator"] .editable-field+span .save-input',
                    event: 'click', func: _self.editView.onClickIndicatorTitle_Save},
                { parent: document, selector: '.element[data-element_type="graph"] .editable-field+span .form-control[type="text"], ' +
                '.element[data-element_type="indicator"] .editable-field+span .form-control[type="text"]',
                    event: 'keydown', func: _self.onKeyDownGraphInput},
                { parent: document, selector: '.modal_dialog[data-controller="edit_view_report"]', event: 'click', func: _self.onClickDialog},
                { parent: document, selector: '[data-type="field_params"] select+.bootstrap-select button', event: 'click', func: _self.onClickFieldsParamsBtn},
                { parent: document, selector: 'ul.settings-menu', event: 'click', func: _self.onClickSettingsMenu},
                // { parent: document, selector: '.list_view_block .element[data-type="reports_menu"] tr.sm_extension_data', event: 'click', func: this.onClickLVTR},
                //{ parent: document, selector: '', event: '', func: ''},
            ]

            Global.addEvents(this._events, {
                instance: this
            });

            return this;
        },
        allMethod: function () {
            var _this = this,
                event = [];

            Filter.local_storage_index_prefix = $('.list_view_block[data-module="reports"]').data('id');

            var instanceGlobal = new _Global();

            var _inputDateRange = $('.input-daterange');
            _inputDateRange.datepicker({
                language: Message.locale.language,
                format: Message.locale.dateFormats.medium_js,
                startDate: '1/1/1970',
                // endDate: new Date(),
                multidate: true,
                autoclose: true
            });

            _inputDateRange.find('input[name="start"]').datepicker('setEndDate', _inputDateRange.find('input[name="end"]').val());
            _inputDateRange.find('input[name="end"]').datepicker('setStartDate', _inputDateRange.find('input[name="start"]').val());
            _inputDateRange.find('input[name="start"]').mask(Message.locale.dateFormats.medium_js.replace(/d|m|y/gi, "9"));
            _inputDateRange.find('input[name="end"]').mask(Message.locale.dateFormats.medium_js.replace(/d|m|y/gi, "9"));

            var reportInterval = setInterval(function() {
                if (!$('#container.preloader').length > 0) {
                    if ($('#modal_dialog1').length >0) {
                        if($('.element[data-element_type="graph"]').length >0 ) {
                            Reports.Constructor.layoutIndicators();
                            Reports.Constructor.triggerElements();
                            clearInterval(reportInterval);
                            if ($('.list_view_block.copy_id8 ').length > 0 && $('.input-daterange').length > 0) {
                                $('.first_empty').removeClass('first_empty');
                            }
                            $('.edit-view[data-copy_id="8"] .inputs-panel[data-position="right"]').prev().attr('forced','left').find('li:first-child>select').trigger('change');
                        } else if ($('.element[data-element_type="graph"]').length ==0 ) {
                            Reports.Constructor.layoutIndicators();
                            Reports.Constructor.triggerElements();
                            clearInterval(reportInterval);
                            if ($('.list_view_block.copy_id8 ').length > 0 && $('.input-daterange').length > 0) {
                                $('.first_empty').removeClass('first_empty');
                            }
                            $('.edit-view[data-copy_id="8"] .inputs-panel[data-position="right"]').prev().attr('forced','left').find('li:first-child>select').trigger('change');
                        }
                    }
                    if ($('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dateinput').length) {
                        var $dateinput = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dateinput')
                        Filter.singleCalendar($dateinput);
                        $dateinput.datepicker('setDate', new Date());
                    }
                    if ($('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp1').length) {
                        var $dp1 = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp1'),
                            $dp2 = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp2');
                        Filter.rangeCalendar($dp1, $dp2);
                        date1 = 0;
                        date2 = 0;
                    }
                }
            }, 100);

            Reports.init();

            Reports.Constructor.layoutIndicators();
            Reports.Constructor.triggerElements();

            // $(document).on('click', '.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"] .element[data-type="show_filters"]', function(e){
            //     var $this = $(this),
            //         $crmDropdown = $this.closest('[data-type="settings"]'),
            //         $submenu = $crmDropdown.find('.element[data-type="filter_block_panels"]');
            //
            //     $submenu.toggleClass('hide');
            //     $submenu.find('.selectpicker').selectpicker({style: 'btn-white'});
            //     $crmDropdown.css('z-index', '30');
            //     $this.closest('ul').addClass('hide').next().find('>li').removeClass('inputs-group');
            //
            //     $('body').on('click', function (e) {
            //         if (!$crmDropdown.has(e.target).length) {
            //             var $list = $crmDropdown.find('>ul');
            //             $list.first().removeClass('hide');
            //             $list.last().addClass('hide');
            //         }
            //     });
            // });

            var deleteInterval = null,
                arrByDeleteFilter = [];




            /*******************************************
             *
             *    REPORT page
             *
             *******************************************/


            $(document).off('click', '.panel .tools .fa-times');

            var curDateStart,curDateEnd,curDateStartVal,curDateEndVal,
                $inputDateRange = $('.input-daterange');

            $inputDateRange.on('show.daterangepicker', function(e){
                var $start = $inputDateRange.find('input[name="start"]'),
                    $end = $inputDateRange.find('input[name="end"]');

                curDateStart = $start.datepicker('getDate');
                curDateEnd = $end.datepicker('getDate');
                $start.on('keyup', function(){
                    curDateStartVal = $(this).val();
                });
                $end.on('keyup', function(){
                    curDateEndVal = $(this).val();
                });

                event = 'hide.daterangepicker';
                $inputDateRange.off(event).on(event, function(e){
                    if (!e.date) {
                        if (curDateStartVal) {
                            $(this).find('input[name="start"]').datepicker('setDate', curDateStartVal);
                        }
                        if (curDateEndVal) {
                            $(this).find('input[name="end"]').datepicker('setDate', curDateEndVal);
                        }
                    }

                    var newDateStart = $('.input-daterange input[name="start"]').datepicker('getDate');
                    var newDateEnd = $('.input-daterange input[name="end"]').datepicker('getDate');
                    if (isNaN(newDateStart)==true || isNaN(newDateEnd)==true) {
                        $('.input-daterange input[name="start"]').datepicker('setDate',curDateStart);
                        $('.input-daterange input[name="end"]').datepicker('setDate',curDateEnd);
                    } else if (curDateStart+curDateEnd!==newDateStart+newDateEnd) {

                        _self.onAddPreloaderForGraph();

                        Reports.Report.updateUserStorage(this, 'date_interval', function(data){
                            var vars = {
                                'selector_content_box' : '#content_container',
                            }
                            instanceGlobal.contentReload
                                .clear()
                                .setVars(vars)
                                .setUrl(Url.getCurrent())
                                .setCallBackSuccessComplete(function(){
                                    Reports.Constructor.layoutIndicators();
                                })
                                .loadThis();
                        });
                    }
                });
            });






            //display_option change
            /*
             $(document).on('change', '.list_view_block[data-module="reports"] .element[data-type="block"][data-element_type="graph"] .element[data-type="settings"] .element[data-type="display_option"]', function(){
             var _this = this;
             Reports.Report.addGraphData(_this, true, function(data){
             Reports.Report.graphIndicator.change(_this, data);
             });
             });
             */

            /*******************************************
             *    REPORT page        (END)
             *******************************************/

            event = 'ul.selectpicker';
            $('body').off('click', event).on('click', event, function(){
                $(this).closest('.bootstrap-select.open').removeClass('open');
            });

            event = '[data-copy_id="8"] .select-item div:first-child button.selectpicker';
            $(document).on('click', event, function(){
                if (!$(this).closest('.settings[data-type="settings"]').length) {
                    $('.edit-view .sub-menu').addClass('hide');
                }
            });

            event = '.edit-view[data-copy_id="8"] .select button.selectpicker';
            $('body').off('click', event).on('click', event , function(){ // fix
                setTimeout(function () {
                    var $dropUp = $('.element[data-type="block_data_analysis"]').find('.dropup');

                    if ($dropUp.length && $dropUp.find('button').offset().top < $dropUp.find('>div').offset().top) {
                        $dropUp.removeClass('dropup');
                    }
                },100)
            });

            event = '.modal .tools .crm-dropdown > .dropdown-toggle';
            $('body').off('click', event).on('click', event, function() {
                $(this).parent().toggleClass('opened');
            });

            event = '.dropdown-menu select+.bootstrap-select';
            $('body').off('click', event).on('click', event, function(){
                var liLenght = $(this).find('ul.dropdown-menu li:not(.disabled)').length;
                if ($(this).closest('.modal-dialog').length>0) {
                    if (liLenght<10) {
                        $(this).find('div.dropdown-menu').height(liLenght*24).css('min-height',liLenght*24+'px');
                    } else {
                        $(this).find('div.dropdown-menu').height(240);
                    }
                } else {
                    if (liLenght<10) {
                        $(this).find('ul.dropdown-menu').height(liLenght*24).css('min-height',liLenght*24+'px');
                    } else {
                        $(this).find('ul.dropdown-menu').height(240);
                    }
                }
                if($(this).closest('.settings-menu').find('.bootstrap-select>button')) {
                    $(this).children('.dropdown-menu').css('top','36px');
                    if ($(this).offset().top+$(this).height()+$(this).children('.dropdown-menu').outerHeight()+10>$(window).height()) {
                        var topcss = $(this).children('.dropdown-menu').height()+16;
                        $(this).children('.dropdown-menu').css('top','-'+topcss+'px');
                    }
                }
            });

            event = '.filter-box-container select+.bootstrap-select';
            $('body').off('click', event).on('click', event, function(){
                var liLenght = $(this).find('ul.dropdown-menu li:not(.disabled)').length;
                if (liLenght<10) {
                    $(this).find('div.dropdown-menu').height(liLenght*24);
                } else {
                    $(this).find('ul.dropdown-menu').height(240);
                    niceScrollCreate($(this).find('ul.dropdown-menu'));
                }
            });

            event = '.edit-view[data-copy_id="8"] .inputs-panel[data-position="right"] .panel-heading .tools.pull-right a.fa, .edit-view[data-copy_id="8"] .inputs-panel[data-position="left"]+.inputs-panel[data-position="botton"] .panel-heading .tools.pull-right a.fa';
            $('body').off('click', event).on('click', event , function(){
                $(this).closest('.inputs-panel').prev().find('.panel-heading .tools a.fa').trigger('click');
            });

            event = '.edit-view[data-copy_id="8"] .element[data-type="block_data_analysis"]>ul>li>.form-control';
            $('body').off('focus', event).on('focus', event, function(){
                var valCur = $(this).val();
                $(this).off('blur');
                $(this).on('blur', function(){
                    if (valCur!==$(this).val()) {
                        $(this).closest('.element[data-type="block_data_analysis"]').find('li.element[data-data_analysis_type="data_analysis_indicator"]:nth-child(2) .settings>ul>li:first-child>select').trigger('change');
                    }
                });
            });


            Reports.Constructor.InitNewSelects();
            $('.report-droptools select.selectpicker').selectpicker({style: 'btn-white',noneSelectedText: ' '});//.addClass('first_empty');

            event = '.report-droptools';
            $('body').off('click', event).on('click', event, function(){
                var _this = $(this).find('input+li select[data-type="indicator"]');
                $('select[data-type="indicator"]').each(function() {
                    if ($(this).find('option').length==0) {
                        $(this).closest('li').addClass('hide');
                    }
                });
                Reports.Report.selectsOptionsOrganize(_this);
            });

            event = '.modal .edit-view[data-copy_id="8"] .element[data-element_type="filter"] .element_relate_participant, .modal .edit-view[data-copy_id="8"] .element[data-element_type="filter"] .element_relate';
            $('body').off('click', event).on('click', event, function(){
                $(this).closest('.dropdown-menu').parent().addClass('open');
                $(this).next().css('top','100%');
                if ($(this).offset().top+$(this).height()+$(this).next().outerHeight()+10>$(window).height()) {
                    var topcss = $(this).next().height()+$(this).height()+8;
                    $(this).next().css('top','-'+topcss+'px');
                }
            });

            var funcAbout = function () {
                var $editView = $('.modal div[data-copy_id="8"]'),
                    $reOpen = $editView.find('.crm-dropdown.opened');

                $reOpen.addClass('open').removeClass('opened');

                if ($reOpen.find('>ul.sub-menu').length) {
                    $reOpen.first().find('>ul:first').addClass('hide');
                    $reOpen.find('>ul.sub-menu').removeClass('hide').show();
                }
            };

            event = '.input-large.datepicker-range';
            $('body').off('hide.daterangepicker', event).on('hide.daterangepicker', event, function(){
                var $this = $(this);

                $this.closest('ul.sub-menu[data-type="filter_block_panels"]').attr('data-submenu', true);
                $this.closest('.crm-dropdown').addClass('opened');
                $this.closest('.crm-dropdown').parent().closest('.crm-dropdown').addClass('open')
                setTimeout(function() { funcAbout()}, 100);
            });

            //TODO: optimized event
            event = '.modal-dialog .edit-view';
            $(document).off('click', event).on('click', event, function () {
                $('.bootstrap-select.open').removeClass('open')
            });

            event = '.dateinput';
            $('body').off('hide.datepicker', event).on('hide.datepicker', event, function() {
                $(this).closest('.crm-dropdown').addClass('opened');
                setTimeout(function() { funcAbout()}, 100);
            });

            event = '.list_view_block.copy_id8 .filter-menu li a';
            $('body').off('click', event).on('click', event, function(){
                $(this).closest('.crm-dropdown').removeClass('open');
            });

            event = '.element[data-data_analysis_type="data_analysis_indicator"] .field-param';
            $('body').off('click', event).on('click', event, function(){
                var $select = $(this).next().find('li:first-child select')
                var valName = $select.val();
                if ($(this).next().find('li:first-child select option[value="'+valName+'"]').data('num')==0  || valName == '__amount__') {
                    $select.closest('li').addClass('not_numb');
                } else {
                    $select.closest('li').removeClass('not_numb');
                }

            });



            event = '.element[data-data_analysis_type="data_analysis_indicator"] .field-param';
            $('body').off('click', event).on('click', event, function(){
                var $select = $(this).next().find('li:first-child select')
                var valName = $select.val();
                if ($(this).next().find('li:first-child select option[value="'+valName+'"]').data('num')==0  || valName == '__amount__') {
                    $select.closest('li').addClass('not_numb');
                } else {
                    $select.closest('li').removeClass('not_numb');
                }
            });

            return this;
        },
        addCard : function () { // Constructor: add
            Reports.Constructor.add(this, function(data){
                Reports.Constructor.runAfter(data);
            });
            Preloader.modalShow()

            return this;
        },
         open : function (element, id, $callback) {
            var id = id || $(element).closest('.sm_extension_data').data('id');

            Reports.Report.view(null, id, $callback);
        },
    }

    var extTools = {
        /**TOOLS**/
        print : function(){
            var copy_id = $('.sm_extension').data('copy_id');
            var id = $('.sm_extension').data('id');
            var params = 'page_size=0&col_hidden=' + ListViewDisplay._hidden_group_index;
            if(document.location.search == '')
                toPrint('/module/reports/printR/'+copy_id+'?id=' + id + '&' + params);
            else
                toPrint('/module/reports/printR/' + copy_id + document.location.search + '&' + params);
        },
        saveToExcel : function(){
            var copy_id = $('.sm_extension').data('copy_id'),
                id = $('.sm_extension').data('id'),
                params = 'page_size=0&col_width=' + JSON.stringify(Reports.Report.getColumnWidth()) + '&col_hidden=&type=excel';

            if(document.location.search == '')
                document.location.href =  '/module/reports/exportR/'+copy_id+'?id=' + id + '&' + params;
            else
                document.location.href =  '/module/reports/exportR/' + copy_id + document.location.search + '&' + params;

            modalDialog.hide();
        },
        saveToPdf : function(){
            var copy_id = $('.sm_extension').data('copy_id');
            var id = $('.sm_extension').data('id');
            var params = 'page_size=0&col_hidden=' + ListViewDisplay._hidden_group_index + '&type=pdf';

            if(document.location.search == '')
                document.location.href =  '/module/reports/exportR/'+copy_id+'?id=' + id + '&' + params;
            else
                document.location.href =  '/module/reports/exportR/' + copy_id + document.location.search + '&' + params;
        },
        /**TOOLS============**/
    }

    Reports = {
        reports_id : null,
        copy_id : null,
        graph_data : {},
        status_opening_ev: false,

        init : function(){
            var sm_extension = $('.process_view_block.sm_extension, .list_view_block.sm_extension');
            Reports.copy_id = sm_extension.data('copy_id');
        },
        setReportsId : function(reports_id){
            Reports.reports_id = reports_id;
        },
        prepareHidePreloaderByGraph: function ($element) {
            var report = Reports.getInstance(),
                maxCountGraph = $('.list_view_block .element[data-element_type="graph"]').length;

            report.countGraph = report.countGraph || 0;

            report.countGraph++;

            if (report.countGraph >= maxCountGraph) {
                report.countGraph = 0;

                this.preloaderForGraph.remove($('#content_container'));
            }
        },
        getPreloader: function () {

            var preloader = Preloader.createInstance();

            this.setPreloader(Preloader.createInstance())
                .setShowPreloaderHandler(function () {
                    this.setRunning(false)
                        .setWhereContentHide(Preloader.TYPE_RELOAD_PAGE)
                        .setPlaceForSpinner($('#container'))
                        .run();
                });

            return preloader;
        },
        preloaderForGraph : {
            clazz: 'graph-set-preloader',
            set : function ($element) {

                $element.addClass(this.clazz);
                if ($element && $element.length) {
                    var $value = $element.last();

                    if (!$value.find(Global.spinner.selector).length) {
                        $value.append(Global.spinner.clone());
                    }
                }
            },
            remove: function ($element) {
                var $base = $element.removeClass('report-parent-graph');
                $base.find('.graph-set-preloader').removeClass('graph-set-preloader');
                $base.find(Preloader.spinner.selector).remove();

                Events.runHandler(Events.TYPE_SNAPSHOT);
            }
        },
        // buildGraph
        buildGraph : function(element, graph_type, data){
            var $element = $('[id="'+element+'"]');

            if ($element.length>1) {
                $('#'+element).attr('id','temporary');
            }
            if(graph_type=='graph-line') {
                $('#'+element).find('.morris-hover').remove();

                Morris.Area({
                    element: element,
                    behaveLikeLine: true,
                    gridEnabled: false,
                    gridLineColor: '#dddddd',
                    axes: true,
                    fillOpacity:.5,
                    data: data.data,
                    lineColors: data.lineColors,
                    xkey: data.xkey,
                    ykeys: data.ykeys,
                    labels: data.labels,
                    pointSize: 0,
                    lineWidth: 0,
                    dateFormat: function (x) {
                        var x = new Date(x);
                        Reports.Constructor.formatingDatesInit();
                        //var xxx = xx.format(Message.locale.dateFormats.medium_js);
                        switch(data.xLabels){
                            case 'day':
                            case 'all_period' :
                                var xxx = x.format(Message.locale.dateFormats.medium_js);
                                break;
                            case 'week' :
                                var begx = x.format(Message.locale.dateFormats.medium_js);
                                if (x.getDay()==0) {
                                    var endx = new Date(x.setTime(x.getTime()));
                                } else {
                                    var endx = new Date(x.setTime(x.getTime() + ((7-x.getDay()) * (1000 * 60 * 60 * 24))));
                                }
                                if ($('.input-daterange').length>0) {
                                    if (data.data[0].is_first_period && endx.getTime()>$('.input-daterange input[name="start"]').datepicker('getDate').getTime()) {
                                        var endx = new Date(x.setTime($('.input-daterange input[name="end"]').datepicker('getDate').getTime()));
                                    } else if (endx.getTime()>$('.input-daterange input[name="end"]').datepicker('getDate').getTime()) {
                                        var endx = new Date(x.setTime($('.input-daterange input[name="end"]').datepicker('getDate').getTime()));
                                    }
                                }
                                endx = endx.format(Message.locale.dateFormats.medium_js);
                                var xxx = begx+'-'+endx;
                                break;
                            case 'month' :
                                var xxx = x.format('mmmm yyyy');
                                break;
                            case 'quarter' :
                                var year = x.format('yyyy');
                                var monthx = x.getMonth();
                                if (monthx==0||monthx==1||monthx==2) {
                                    var qwe = 'Q1';
                                } else if (monthx==3||monthx==4||monthx==5) {
                                    var qwe = 'Q2';
                                } else if (monthx==6||monthx==7||monthx==8) {
                                    var qwe = 'Q3';
                                } else if (monthx==9||monthx==10||monthx==11) {
                                    var qwe = 'Q4';
                                }
                                var xxx = qwe+' '+year;
                                break;
                            case 'year' :
                                var xxx = x.format('yyyy');
                                break;
                        }
                        if(typeof(xxx) != 'undefined') return xxx.toString();
                        else return x;
                    },
                    xLabels: data.xLabels,

                    xLabelFormat: function (x) {
                        switch(data.xLabels){
                            case 'day':
                            case 'all_period' :
                            case 'week' :
                                var xxx = x.format(Message.locale.dateFormats.medium_js);
                                break;
                            case 'month' :
                            case 'quarter' :
                            case 'year' :
                                var xxx = x.format(Message.locale.dateFormats.medium_js.replace("dd", "01"));
                                break;
                        }
                        if(typeof(xxx) != 'undefined') return xxx.toString();
                        else return x;
                    },

                    hideHover: 'auto'
                });
            } else if (graph_type=='graph-histogram') {
                Morris.Bar({
                    element: element,
                    data: data.data,
                    xkey: data.xkey,
                    ykeys: data.ykeys,
                    labels: data.labels,
                    gridLineColor: '#dddddd',
                    barColors: data.barColors,
                });
            } else if (graph_type=='graph-circular') {
                Morris.Donut({
                    element: element,
                    data: data.data,
                    backgroundColor: '#fff',
                    labelColor: data.labelColor,
                    colors: data.colors,
                    formatter: function (x, data) { return data.formatted; }
                });
            } else if (graph_type=='graph-crater') {
                $(data.element).html('воронка продаж');
            }
            $('#temporary').attr('id',element);
        },

        open: function (id) {
            Reports.Report.view(null, id);
        },
        getReportUrl : function(reports_id, callback){
            var _copy_id = $('.list_view_block').data('copy_id');
            var _storage_index = _copy_id + '_' + reports_id;
            var params = {'copy_id' : _storage_index, 'destination' : null, 'params' : {'this_template' : 0}};

            var send = function(callback) {
                $.ajax({
                    url: '/module/history/getUserStorageUrl/' + _copy_id,
                    data : params,
                    type: "POST",
                    timeout : crmParams.global.ajax.get_url_timeout,
                    success: function(data) {
                        if(data.status == true){
                            callback(data.url);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                        Message.showErrorAjax(jqXHR, textStatus);
                        Preloader.modalHide();
                    },
                });
            };

            send(function(url){
                if(!url) return;

                var url = url.split("?");

                url = url[0] + '?id=' + reports_id + (url[1] ? '&' + url[1] : '');

                callback(url);
            });

        },

        //bool - status synchronization loaded graphs
        afterViewChanges: function (bool) {
            var instanceEV = modalDialog.getDataFromStore($('.edit-view').last().data('unique_index')),
                instanceReport = Reports.getInstance();

            $('.edit-view').find('.b-spinner').closest('[data-element_type="graph"]').addClass('report-parent-graph');

            if (bool) {
                var count_graphs = 1;

                instanceReport.setCountLoadedGraph(null);

                var time = setInterval(function () {
                    var count_loaded = instanceReport.getCountLoadedGraph();

                    if (count_loaded >= count_graphs) {
                        clearInterval(time);
                        instanceEV.afterViewChanges();
                    }
                }, 150);
            } else {
                if (instanceEV) {
                    var time = setTimeout(function () {
                        clearTimeout(time);
                        instanceEV.afterViewChanges();
                    }, 100)
                }
            }
        },
        createInstance : function(){
            var Obj = function(){
                for(var key in Reports){
                    if ($.inArray(key, ['createInstance']) < 0) {
                        this[key] = Reports[key];
                    }
                }
                for(var key in _public){
                    this[key] = _public[key];
                }
            }

            Obj.prototype = Object.create(Global);

            return _self._instance = new Obj().constructor();
        },
        getInstance: function (bool) {
            if (bool && !_self._instance) {
                this.createInstance();
            }

            return _self._instance;
        },

        //Constructor
        Constructor : {
            _instance: null,
            _element: null,

            runAfter : function(data, edit_view){
                var modal;

                if (edit_view) {
                    modal = edit_view.getModal();
                } else {
                    modal = modalDialog.createInstance();
                }

                if(data.status == 'data'){
                    modal.show(data.data, true);
                }

                Global.addOperationInSDM();

                $('.crm-dropdown > .dropdown-toggle').removeAttr('data-toggle').on('click',function(){
                    $('.crm-dropdown.open').removeClass('open');
                    $(this).parent().toggleClass('open');
                });
                $('.reports-mark').selectpicker({style: 'btn-white',noneSelectedText: ' '});
                $('.reports-color').selectpicker();
                $('.selectpicker').selectpicker({style: 'btn-white',noneSelectedText: ' '});
                $('select.edit-dropdown.element').selectpicker({style: 'btn-white',noneSelectedText: ' '});
                $('.sub-module-params-cog-span').on('show.bs.dropdown', function () {
                    $(this).find('.reports-menu > li').getNiceScroll().remove();
                });
                Reports.Constructor.InitNewSelects();
                Reports.Constructor.initSorting();
                $('.edit-view[data-copy_id="8"] .inputs-panel[data-position="right"]').prev().attr('forced','left').find('li:first-child>select').trigger('change');
                Reports.Constructor.layoutIndicators();
                Reports.Constructor.triggerElements();
                if ($('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dateinput').length) {
                    var $dateinput = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dateinput')
                    Filter.singleCalendar($dateinput);
                    $dateinput.datepicker('setDate', new Date());
                }
                if ($('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp1').length) {
                    var $dp1 = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp1'),
                        $dp2 = $('.edit-view[data-copy_id="8"] .element[data-type="block_filter"]').find('.dp2');
                    Filter.rangeCalendar($dp1, $dp2);
                    date1 = 0;
                    date2 = 0;
                }
                $('.select.element.first_empty').on("click", function () {
                    return (callbacksarr.length <=2) ? true : false;
                });
            },

            createInstance : function(){
                var Obj = function(){
                    for(var key in Reports.Constructor){
                        this[key] = Reports.Constructor[key];
                    }
                }

                return Reports.Constructor._instance = new Obj();
            },

            setElementHtml : function (element) {
                if (element && $(element).length) {
                    this._element = $(element);
                }
                return this;
            },

            //add
            add : function(_this, callback){
                var _data = {};
                _data['primary_entities'] = EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false));

                var ajax = new Ajax();
                ajax
                    .setUrl('/module/constructor/add/' + Reports.copy_id)
                    .setData(_data)
                    .setAsync(true)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                callback(data);
                            }
                        }
                        Preloader.modalHide();
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                        Preloader.modalHide();
                    })
                    .setCallBackDone(
                        function(){
                            $('.modal .contacts-block img').on('load', function() {
                                EditView.contactImg();
                            });
                            EditView.emptyFields();
                            EditView.hiddenBlocks();
                        }
                    )
                    .send();
            },

            //edit
            edit : function(_this, callback){
                var sm_extension = $(_this).closest('.sm_extension');
                var _data = {
                    'copy_id' : Reports.copy_id,
                    'id' : $(_this).closest('.sm_extension_data').data('id'),
                    'pci' : sm_extension.data('parent_copy_id'),
                    'pdi' : sm_extension.data('parent_data_id'),
                    'primary_entities' : EditView.relateDataStory.getPrimaryEtitiesFromEditView(null, (EditView.countEditView() == 1 ? true : false)),
                    'this_template' : sm_extension.data('this_template'),
                    'from_template' : 0,
                };

                if(_data['template_data_id']){
                    _data['from_template'] = 1;
                    _data['id'] = _data['template_data_id'];
                }

                AjaxObj
                    .createInstance()
                    .setUrl('/module/constructor/view/' + Reports.copy_id)
                    .setData(_data)
                    .setAsync(true)
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                callback(data);
                            }
                        }
                        Preloader.modalHide();
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                        Preloader.modalHide();
                    })
                    .setCallBackDone(
                        function(){
                            $('.modal .contacts-block img').on('load', function() {
                                EditView.contactImg();
                            });
                            EditView.emptyFields();
                            EditView.hiddenBlocks();
                        }
                    )
                    .send();
            },


            /**
             *  Схема фильтра отчета
             */
            getSchemaFilters : function(ul){
                var elements = [];
                var params;

                $(ul).find('.element[data-type="block_panels"] > .element[data-type="block_panel"], .element[data-type="filter_block_panels"] .element[data-type="block_panel"]').each(function(i, ul){
                    params = $(ul).find('.params_hidden').text();
                    params = JSON.parse(params);

                    params['module_copy_id'] = $(ul).find('.element[data-type="module_copy_id"]').val();
                    params['field_name'] = $(ul).find('.element[data-type="field_name"]').val();
                    params['unique_index'] = $(ul).data('unique_index');

                    var settings = $(ul).find('.element[data-type="settings"]');
                    params['condition'] = settings.find('.element_filter[data-name="condition"]').val();

                    var condition_value = [];
                    settings.find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                        if($(this).hasClass('element_relate') || $(this).hasClass('element_relate_this')){
                            condition_value.push($(ul).data('id'));
                        } else if($(this).hasClass('element_relate_participant')){
                            condition_value.push($(ul).data('ug_id'));
                            condition_value.push($(ul).data('ug_type'));
                        } else {
                            var val = $(ul).val();
                            if(val) condition_value.push(val);
                        }
                    });

                    if(!$.isEmptyObject(condition_value))
                        params['condition_value'] = condition_value;
                    else
                        params['condition_value'] = [""];

                    elements.push(params);
                });

                return elements;
            },


            /**
             *  Схема отчета
             */
            getSchema : function(_this, return_json){
                var schema = [];
                var elements = [];
                var params, element;

                $(_this).closest('.edit-view').find('.element[data-type="block"].element[data-module="reports"]').each(function(i, ul){
                    var element = $(ul).find('> .element[data-type="block_panel"] > .params_hidden, > .element[data-type="block_data_analysis"] > .params_hidden, > .element[data-type="block_filter"] > .params_hidden');
                    if(typeof(element) == 'undefined' || element==false){
                        element = {};
                    } else {
                        element = JSON.parse(element.text());
                    }

                    element['title'] = $(ul).find('> header .element[data-type="title"]').text();

                    //indicator
                    if(element.type == 'indicator'){
                        var elements = [];
                        $(ul).find('> header .element[data-type="settings"] .element[data-type="setting"]').each(function(i, ul){
                            var el = $(ul).find('.element[data-type="indicator"]');
                            params = $(ul).find('> .params_hidden').text();
                            params = JSON.parse(params);
                            params['module_copy_id'] = el.data('module_copy_id');
                            params['field_name'] = el.data('field_name');
                            params['unique_index'] = el.data('unique_index');
                            params['color'] = $(ul).find('.element[data-type="color"]').val();
                            elements.push(params);
                        })
                        element['elements'] = elements;
                    }

                    // graph
                    if(element.type == 'graph'){
                        var elements = [];
                        var settings = $(ul).find('> header .element[data-type="settings"]');

                        params = settings.find('.params_hidden').text();
                        params = JSON.parse(params);

                        var display_option = settings.find('.element[data-type="display_option"]').val();
                        var period = settings.find('.element[data-type="period"]').val();
                        params['period'] = (typeof(period) != 'undefined' ? period : '');
                        params['display_option'] = (typeof(display_option) != 'undefined' ? display_option : '');
                        params['indicator'] = settings.find('.element[data-type="indicator"]').val();
                        params['position'] = $(ul).data('position');

                        elements.push(params);
                        element['elements'] = elements;
                    }

                    // data_analysis
                    if(element.type == 'data_analysis'){
                        var elements = [];
                        $(ul).find('.element[data-type="block_panels"] > .element[data-type="block_panel"]').each(function(i, ul){
                            params = $(ul).find('> .params_hidden').text();
                            params = JSON.parse(params);

                            params['title'] = $(ul).find('.element[data-type="title"]').val();
                            params['module_copy_id'] = $(ul).find('.element[data-type="module_copy_id"]').val();
                            params['field_name'] = $(ul).find('.element[data-type="field_name"]').val();
                            params['unique_index'] = $(ul).data('unique_index');

                            if($(ul).data('data_analysis_type') == 'data_analysis_param'){
                                params['type_date'] = $(ul).find('.element[data-type="type_date"]').val();
                            } else
                            if($(ul).data('data_analysis_type') == 'data_analysis_indicator'){
                                params['type_indicator'] = $(ul).find('.element[data-type="type_indicator"]').val();
                            }

                            params['filters'] = Reports.Constructor.getSchemaFilters(ul);

                            elements.push(params);
                        })
                        element['elements'] = elements;
                    }


                    // filter
                    if(element.type == 'filter'){
                        element['elements'] = Reports.Constructor.getSchemaFilters(ul);
                    }


                    schema.push(element);
                });

                if(return_json) schema = JSON.stringify(schema);

                return schema;
            },



            //save
            save : function(_this){
                var _params = {},
                    __this = this;

                _params['EditViewModel[schema]'] =  Reports.Constructor.getSchema(_this, true);

                var callback = null;

                if(Reports.reports_id){
                    callback = function(){
                        __this.open();
                    }
                }
                EditView.save(Reports.copy_id, _params, callback, '/module/constructor/save/' + Reports.copy_id);
            },

            open : function () {
                var _this = this;
                History.close(true);
                modalDialog.hideAll();

                Reports.getReportUrl(Reports.reports_id, function(url){
                    Preloader
                        .createInstance()
                        .setPriorityDisable(false)
                        .setWhereContentHide(Preloader.REPORT)
                        .run()

                    instanceGlobal.contentReload
                        .clear()
                        .setVars({
                            'selector_content_box' : '#content_container'
                        })
                        .setCallBackSuccessComplete(function(){
                            Reports.Constructor.layoutIndicators();
                        })
                        .setUrl(url)
                        .loadPage();
                });

                return this;
            },

            //getDataAnalysisModuleCopyIdList
            getDataAnalysisModuleCopyIdList : function(_this, error_view, skip_to_remove){
                var to_remove = '';
                if(skip_to_remove) to_remove = ':not(.to_remove)';
                var list = [];
                $(_this)
                    .closest('.edit-view')
                    .find('.element[data-type="block"] .element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"], .element[data-type="block"] .element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"]'+to_remove)
                    .find('.element[data-type="module_copy_id"]').each(function(i, ul){
                    var s = $(ul).val();
                    if(s) list.push(s);
                });
                if(error_view && $.isEmptyObject(list)){
                    Message.show([{'type': 'error', 'message': 'In the "Data analysis" is not selected modules'}], true);
                    return;
                }

                return list;
            },


            //getParam
            getParam : function(error_view){
                var list = {};

                $('.edit-view .element[data-type="block"][data-element_type="data_analysis"] .element[data-data_analysis_type="data_analysis_param"]').each(function(i, ul){

                    var title = $(ul).find('.element[data-type="title"]').val();
                    var module_copy_id = $(ul).find('.element[data-type="module_copy_id"]').val();
                    var field_name = $(ul).find('.element[data-type="field_name"]').val();
                    var unique_index = $(ul).data('unique_index');
                    var type_date = $(ul).find('.element[data-type="type_date"]').val();

                    list = {
                        'title' : title,
                        'module_copy_id' : module_copy_id,
                        'field_name' : field_name,
                        'type_date' : type_date,
                        'unique_index' : unique_index,
                    };
                });

                if(error_view && $.isEmptyObject(list)){
                    Message.show([{'type': 'error', 'message': 'In the "Data analysis" is not indicators'}], true);
                    return;
                }

                return list;
            },


            //getIndicators
            getIndicators : function(error_view, skip_to_remove){
                var to_remove = '';
                if(skip_to_remove) to_remove = ':not(.to_remove)';
                var list = [];

                $('.edit-view .element[data-type="block"][data-element_type="data_analysis"] .element[data-data_analysis_type="data_analysis_indicator"]' + to_remove).each(function(i, ul){
                    var title = $(ul).find('.element[data-type="title"]').val();
                    var module_copy_id = $(ul).find('.element[data-type="module_copy_id"]').val();
                    var field_name = $(ul).find('.element[data-type="field_name"]').val();
                    var unique_index = $(ul).data('unique_index');
                    var type_indicator = $(ul).find('.element[data-type="type_indicator"]').val();

                    list.push({
                        'title' : title,
                        'module_copy_id' : module_copy_id,
                        'field_name' : field_name,
                        'type_indicator' : type_indicator,
                        'unique_index' : unique_index,
                    });
                });

                if(error_view && $.isEmptyObject(list)){
                    Message.show([{'type': 'error', 'message': 'In the "Data analysis" is not indicators'}], true);
                    return;
                }

                return list;
            },



            //getFilters
            getFilters : function(error_view){
                var list = [];
                $('.edit-view .element[data-type="block"][data-element_type="filter"] .element[data-type="block_panels"] > .element[data-type="block_panel"]').each(function(i, ul){
                    var params = {};
                    var copy_id = $(ul).find('.element[data-type="module_copy_id"]').val();
                    var name = $(ul).find('.element[data-type="field_name"]').val();

                    params['copy_id'] = copy_id;
                    params['name'] = name;

                    var settings = $(ul).find('.element[data-type="settings"]');
                    var condition = settings.find('.element_filter[data-name="condition"]').val();
                    params['condition'] = condition;

                    var condition_value = [];
                    settings.find('.element_filter[data-name="condition_value"]').each(function(i, ul){
                        if($(this).hasClass('element_relate') || $(this).hasClass('element_relate_this')){
                            condition_value.push($(ul).data('id'));
                        } else if($(this).hasClass('element_relate_participant')){
                            condition_value.push($(ul).data('ug_id'));
                            condition_value.push($(ul).data('ug_type'));
                        } else {
                            condition_value.push($(ul).val());
                        }
                    });

                    if(!copy_id || !name || !condition){
                        return true;
                    }

                    if(!$.isEmptyObject(condition_value))
                        params['condition_value'] = condition_value;
                    else
                        params['condition_value'] = ["",""];

                    list.push(params);
                });

                return list;
            },



            addGraphDialog : function(){
                var graph = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]');
                var positions = [];
                if(graph.length > 0){
                    graph.each(function(i,ul){
                        positions.push($(ul).data('position'));
                    });
                }

                var ajax = new Ajax();
                ajax
                    .setUrl('/module/constructor/addGraphdialog/' + Reports.copy_id)
                    .setData({'graph_count' : graph.length, 'positions' : positions})
                    .setDataType('html')
                    .setCallBackSuccess(function(data){
                        if(!data){
                            Message.show([{'type':'error', 'message': 'None installed copies blocks for connection'}], true);
                            return;
                        }
                        modalDialog.show(data);
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();
            },

            //addElement
            addElement : function(_this, element, callback){
                var _params = {};

                switch(element){
                    case 'indicator_block' :
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        list = Reports.Constructor.getIndicators(true);
                        if(!list) return;
                        _params['indicators'] = list;

                        break;


                    case 'graph_element' :
                        //graph_list
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : $(ul).find('.element[data-type="indicator"]').val(),
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;
                        //param
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        break;

                    case 'graph_block' :
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //indicators
                        list = Reports.Constructor.getIndicators(true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        _params['graph_type'] = $(_this).closest('.modal-dialog').find('.element[data-type="graph_type"]').val();
                        _params['position'] = $(_this).closest('.modal-dialog').find('.element[data-type="position"]').val();
                        break;

                    case 'data_analysis_indicator' :
                        _params['module_copy_id'] = $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="module_copy_id"]').val();
                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;

                        break;

                    case 'filter' :
                        var list = Reports.Constructor.getDataAnalysisModuleCopyIdList(_this, false, true);
                        if(!list) return;
                        _params['module_copy_id_list'] = list;
                        break;

                    case 'filter_indicator' :
                        var copy_id = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]').val();
                        if(!copy_id) return;
                        _params['module_copy_id'] = copy_id;
                        break;

                }

                AjaxObj
                    .createInstance()
                    .setUrl('/module/constructor/addElement/' + Reports.copy_id)
                    .setData({
                        'element' : element,
                        'params' : _params
                    })
                    .setDataType('json')
                    .setCallBackSuccess(function(data){
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
                    })
                    .send();
            },





            getViewElements : function(){
                var element_list = {
                    'indicator' : [],
                    'graphs' : {}
                }


                $('.element[data-type="block"][data-module="reports"][data-element_type="indicator"], ' +
                    '.element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                    // indicator
                    if($(ul).data('element_type') == 'indicator'){
                        $(ul).find('.element[data-type="settings"] .element[data-type="setting"] .element[data-type="indicator"]').each(function(i2, ul2){
                            var ui = $(ul2).data('unique_index');
                            if(ui){
                                element_list.indicator.push(ui);
                            }
                        });
                    } else
                    // graph
                    if($(this).data('element_type') == 'graph'){
                        var ui_list = [];
                        $(ul).find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i2, ul2){
                            var ui = $(ul2).val();
                            if(ui){
                                ui_list.push(ui);
                            }
                            element_list.graphs[$(ul).data('unique_index')] = ui_list;
                        });

                    }
                });




                return element_list;
            },


            //changeElement
            changeElement : function(_this, element, params, callback, condition, ajax_async){
                if(typeof(condition) != 'undefined' && condition !== null && condition == false){
                    if(typeof(callback) == 'function') callback(false);
                    return;
                }

                var _params = {};

                switch(element){
                    //indicator_indicator
                    case 'indicator_indicator' :
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();
                        //indicators
                        list = Reports.Constructor.getIndicators(true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;
                        _params['unique_index'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('unique_index');
                        _params['module_copy_id'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('module_copy_id');
                        _params['field_name'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('field_name');
                        _params['color'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="color"]').val();

                        break;


                    //indicator_add
                    case 'indicator_add' :
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        _params['unique_index'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('unique_index');
                        _params['module_copy_id'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('module_copy_id');
                        _params['field_name'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="indicator"]').data('field_name');
                        _params['color'] = $(_this).closest('.element[data-type="setting"]').find('.element[data-type="color"]').val();

                        break;


                    case 'graph' :
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            var indicator = $(ul).find('.element[data-type="indicator"]').val();
                            if('data_analysis_panel_delete' in params && params.data_analysis_panel_delete == true){
                                var unique_index = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                                if(unique_index == indicator) indicator = '';
                            }

                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : indicator,
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;

                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        break;


                    //data_analysis_param_module
                    case 'data_analysis_param_module' :
                        //graph_list
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : $(ul).find('.element[data-type="indicator"]').val(),
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;

                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;

                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();

                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        var list_i = [];
                        var list_v;
                        $.each(list, function(key, value){
                            list_v = value;
                            list_v.module_copy_id = null;
                            list_v.field_name = null;
                            list_v.type_indicator = null;
                            //list_v.type_date = null;
                            list_i.push(list_v);
                        })
                        _params['indicators'] = list_i;

                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        _params['module_copy_id'] = $(_this).val();
                        _params['module_copy_id_list'] = '';
                        if(_params['module_copy_id']) _params['module_copy_id_list'] = [$(_this).val()];

                        _params['unique_index'] = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                        _params['field_name'] = '';
                        _params['color'] = '';

                        break;

                    //data_analysis_indicator_module
                    case 'data_analysis_indicator_module' :
                        //graph_list
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            var indicator = $(ul).find('.element[data-type="indicator"]').val();

                            if('data_analysis_panel_delete' in params && params.data_analysis_panel_delete == true){
                                var unique_index = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                                if(unique_index == indicator) indicator = '';
                            }
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : indicator,
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;

                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;

                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();

                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        var list_i = [];
                        var list_v;
                        var unique_index = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                        $.each(list, function(key, value){
                            list_v = value;
                            if('this_ui_clear' in params && params.this_ui_clear == true && unique_index == value.unique_index){
                                list_v.module_copy_id = $(_this).val();
                                list_v.field_name = null;
                                list_v.type_indicator = null;
                                //list_v.type_date = null;
                            }
                            list_i.push(list_v);
                        })

                        _params['indicators'] = list_i;

                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        _params['parent_module_copy_id'] = $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="module_copy_id"]').val();
                        _params['parent_field_name'] = $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_data_analysis"] .element[data-data_analysis_type="data_analysis_param"] .element[data-type="field_name"]').val();
                        _params['module_copy_id'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]').val();
                        _params['unique_index'] = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                        _params['field_name'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="field_name"]').val();
                        _params['color'] = '';

                        var list = Reports.Constructor.getDataAnalysisModuleCopyIdList(_this, false, true);
                        _params['module_copy_id_list'] = '';
                        if($.isEmptyObject(list) == false)
                            _params['module_copy_id_list'] = list;

                        break;



                    //data_analysis_panel_settings
                    case 'data_analysis_panel_settings':
                        //graph
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : $(ul).find('.element[data-type="indicator"]').val(),
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;
                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        var list = Reports.Constructor.getDataAnalysisModuleCopyIdList(_this, false, true);
                        _params['module_copy_id_list'] = '';
                        if ($.isEmptyObject(list) == false)
                            _params['module_copy_id_list'] = list;

                        break;

                    //data_analysis_indicator_settings
                    case 'data_analysis_indicator_settings':
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : $(ul).find('.element[data-type="indicator"]').val(),
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;
                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();
                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        _params['unique_index'] = $(_this).closest('.element[data-type="block_panel"]').data('unique_index');
                        _params['parent_module_copy_id'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]').val();
                        _params['parent_field_name'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="field_name"]').val();
                        _params['module_copy_id'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="module_copy_id"]').val();
                        _params['field_name'] = $(_this).closest('.element[data-type="block_panel"]').find('.element[data-type="field_name"]').val();
                        _params['color'] = '';

                        var list = Reports.Constructor.getDataAnalysisModuleCopyIdList(_this, false, true);
                        _params['module_copy_id_list'] = '';
                        if ($.isEmptyObject(list) == false)
                            _params['module_copy_id_list'] = list;

                        break;


                    //update_output_elements
                    case 'update_output_elements':
                        var graph_list = {};
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"]').each(function(i, ul){
                            var display_option = $(ul).find('.element[data-type="display_option"]').val();
                            var unique_index = $(ul).find('.element[data-type="graph_element"]').attr('unique_index');
                            graph_list[unique_index] = {
                                'period' : $(ul).find('.element[data-type="period"]').val(),
                                'graph_type' : $(ul).find('.element[data-type="graph_element"]').data('graph_type'),
                                'display_option' : (typeof(display_option) != 'undifined' && display_option ? display_option : null),
                                'indicator' : $(ul).find('.element[data-type="indicator"]').val(),
                                'unique_index' : unique_index,
                            }
                        })
                        if($.isEmptyObject(graph_list)) graph_list = '';
                        _params['graph_list'] = graph_list;
                        //params
                        var list = Reports.Constructor.getParam(true);
                        if(!list) return;
                        _params['param'] = list;
                        //view elements
                        _params['view_elements'] = Reports.Constructor.getViewElements();
                        //indicators
                        list = Reports.Constructor.getIndicators(true, true);
                        if(!list) return;
                        _params['indicators'] = list;
                        //filters
                        list = Reports.Constructor.getFilters();
                        if(!list) return;
                        _params['filters'] = list;

                        var indicator_indicators = [];
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="data_analysis"] .element[data-type="block_panels"] .element[data-type="block_panel"][data-data_analysis_type="data_analysis_indicator"]').each(function(i, ul){
                            var tmp = {};
                            tmp['unique_index'] = $(ul).data('unique_index');
                            tmp['module_copy_id'] = $(ul).find('.element[data-type="module_copy_id"]').val();
                            tmp['field_name'] = $(ul).find('.element[data-type="field_name"]').val();
                            tmp['color'] = '';
                            indicator_indicators.push(tmp);
                        });
                        _params['indicator_indicators'] = indicator_indicators;

                        break;

                    //filter_module_params
                    case 'filter_module_params' :
                        _params['module_copy_id'] = $(_this).val();
                        break;

                }

                var ajax = new Ajax(),
                    _data = {
                        'element' : element,
                        'params' : _params,
                    };
                callbacksarr.push(callback);
                ajax
                    .setUrl('/module/constructor/changeElement/' + Reports.copy_id)
                    .setData(_data)
                    .setAsync(true)
                    .setCallBackSuccess(function(data){
                        $('select[disabled]').removeAttr('disabled');
                        var callback = callbacksarr[0];
                        callbacksarr.shift();
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
                        $('select[disabled]').removeAttr('disabled');
                        Message.showErrorAjax(jqXHR, textStatus);
                        callbacksarr = []; // clear; old -  callbacksarr.shift()
                    })
                    .send();
            },





            //clearIndicators
            clearIndicators : function(_this, unique_index, data){
                // indicator
                var block = $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"]');

                if(typeof(block) != 'undefined' && block.length > 0){
                    block.find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i, ul){
                        if($(ul).data('unique_index') == unique_index){
                            var setting = $(ul).closest('.element[data-type="setting"]');
                            var params_hidden = $(ul).closest('.element[data-type="setting"]').find('.params_hidden').text();
                            var remove_panel = $(ul).closest('.element[data-type="setting"]').find('.element[data-type="remove_panel"]');
                            var element = $(data.indicator_setting_indicator);

                            element.find('.params_hidden').text(params_hidden);
                            if(remove_panel.length == 0) element.find('.element[data-type="remove_panel"]').remove();

                            $(setting).after(element).remove();

                            block.find('.element[data-type="block_panel"] .element[data-type="panel"][data-unique_index="'+unique_index+'"]').after(data.indicator_panel).remove();
                        } else {
                            var setting = $(ul).closest('.element[data-type="setting"]');
                            var unique_index_2 = $(ul).data('unique_index');
                            var color = $(ul).closest('.element[data-type="setting"]').find('.element[data-type="color"]').val();
                            var params_hidden = $(ul).closest('.element[data-type="setting"]').find('.params_hidden').text();
                            var remove_panel = $(ul).closest('.element[data-type="setting"]').find('.element[data-type="remove_panel"]');
                            var element = $(data.indicator_setting_indicator);

                            element.find('option[data-unique_index="'+unique_index_2+'"]').attr('selected', 'selected');
                            element.find('.element[data-type="color"]').val(color);
                            element.find('.params_hidden').text(params_hidden);
                            if(remove_panel.length == 0) element.find('.element[data-type="remove_panel"]').remove();
                            var option = element.find('option[data-unique_index="'+unique_index_2+'"]');

                            element = Reports.Constructor.setIndicatorEntities(element, {'unique_index' : option.data('unique_index'),'module_copy_id' : option.data('module_copy_id'),'field_name' : option.data('field_name')}, true);

                            $(setting).after(element).remove();
                        }
                    })

                }
            },



            //changeIndicators setting
            changeIndicators : function(_this, data){
                // indicator
                var block = $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"]');

                if(typeof(block) != 'undefined' && block.length > 0){
                    block.find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i, ul){
                        var setting = $(ul).closest('.element[data-type="setting"]');
                        var unique_index = $(ul).data('unique_index');
                        var color = $(ul).closest('.element[data-type="setting"]').find('.element[data-type="color"]').val();
                        var params_hidden = $(ul).closest('.element[data-type="setting"]').find('.params_hidden').text();
                        var remove_panel = $(ul).closest('.element[data-type="setting"]').find('.element[data-type="remove_panel"]');
                        var element = $(data.indicator_setting_indicator);


                        element.find('option[data-unique_index="'+unique_index+'"]').attr('selected', 'selected');
                        element.find('.element[data-type="color"]').val(color);
                        element.find('.params_hidden').text(params_hidden);
                        if(remove_panel.length == 0) element.find('.element[data-type="remove_panel"]').remove();
                        var option = element.find('option[data-unique_index="'+unique_index+'"]');

                        element = Reports.Constructor.setIndicatorEntities(element, {'unique_index' : option.data('unique_index'),'module_copy_id' : option.data('module_copy_id'),'field_name' : option.data('field_name')}, true);

                        $(setting).after(element).remove();
                    });
                }
            },



            //clearGraphics
            clearGraphics : function(_this, data){
                if(!data.graph_setting_indicator) return;

                $(_this).closest('.edit-view').find('.element[data-type="block"][data-element_type="graph"]').each(function(i, ul){
                    $(ul).find('.element[data-type="indicator"]').closest('li').after(data.graph_setting_indicator).remove();
                });
            },



            //changeGraphics
            changeGraphics : function(_this, data, unique_index){
                $(_this).closest('.edit-view').find('.element[data-type="block"][data-element_type="graph"]').each(function(i, ul){
                    var li = $(ul).find('.element[data-type="indicator"]').closest('li')
                    var unique_index_el = $(ul).find('.element[data-type="indicator"]').val();
                    // graph indicator
                    if(data.graph_setting_indicator) {
                        var element = $(data.graph_setting_indicator);
                        if(unique_index_el) {
                            element.find('option[value="' + unique_index_el + '"]').attr('selected', 'selected');
                            $(li).after(element).remove();
                        } else {
                            $(li).after(element).remove();
                            var unique_index_el = $(ul).find('.element[data-type="indicator"]').val();
                        }

                    }

                    // graph

                    if(data.graph_element && unique_index && unique_index == unique_index_el) {
                        Reports.Constructor.changeGraphElement(ul, data.graph_element);
                    }
                });

            },


            //changeGraphElement
            changeGraphElement : function(_graph_block_object, data){
                var graph_element = $(_graph_block_object).find('.element[data-type="graph_element"]');
                var id = graph_element.attr('id');

                if(data[id]){
                    graph_element.after(data[id]).remove();
                }
            },

            //changeFilters
            changeFilters : function(_this, data){
                var filter;

                if(!data.filter_base && !data.filter) return;

                var copy_id_list = Reports.Constructor.getDataAnalysisModuleCopyIdList(_this, false, true);

                $(_this).closest('.edit-view').find('.element[data-type="block"] .element[data-type="block_filter"] .element[data-type="block_panel"]').each(function(i, ul){
                    var module_copy_id = $(ul).find('.element[data-type="module_copy_id"]').val();

                    var isset_module = ($.isEmptyObject(copy_id_list) == false && module_copy_id ? $.inArray(module_copy_id, copy_id_list) : -1);

                    if(isset_module ===  -1){
                        if(i == 0){
                            filter = data.filter_base;
                        } else {
                            filter = data.filter;
                        }
                        $(ul).after(filter).remove();
                    }
                    else {
                        if(module_copy_id){
                            var filter_module = $(data.filter_module);
                            filter_module.find('option[value="'+module_copy_id+'"]').attr('selected', 'selected');

                            $(ul).find('.element[data-type="module_copy_id"]').after(filter_module).remove();
                            filter_module.next('.bootstrap-select').remove();
                            filter_module.selectpicker({style: 'btn-white',noneSelectedText: ' '});
                        }
                    }
                });
            },


            setIndicatorEntities : function(_this, entyties, this_return){
                if(entyties){
                    var unique_index = entyties.unique_index;
                    var module_copy_id = entyties.module_copy_id;
                    var field_name = entyties.field_name;
                } else {
                    var index = $(_this).find('+ div ul li.selected').attr('rel');
                    var option = $(_this).find('option').eq(index);

                    var unique_index = option.data('unique_index');
                    var module_copy_id = option.data('module_copy_id');
                    var field_name = option.data('field_name');
                }

                if(this_return){
                    if(typeof(unique_index) != 'undefined') _this.find('.element[data-type="indicator"]').data('unique_index', unique_index); else _this.find('.element[data-type="indicator"]').data('unique_index', '');
                    if(typeof(module_copy_id) != 'undefined') _this.find('.element[data-type="indicator"]').data('module_copy_id', module_copy_id); else _this.find('.element[data-type="indicator"]').data('module_copy_id', '');
                    if(typeof(field_name) != 'undefined') _this.find('.element[data-type="indicator"]').data('field_name', field_name); else _this.find('.element[data-type="indicator"]').data('field_name', '');

                    return _this;
                } else {
                    if(typeof(unique_index) != 'undefined') $(_this).data('unique_index', unique_index); else $(_this).data('unique_index', '');
                    if(typeof(module_copy_id) != 'undefined') $(_this).data('module_copy_id', module_copy_id); else $(_this).data('module_copy_id', '');
                    if(typeof(field_name) != 'undefined') $(_this).data('field_name', field_name); else $(_this).data('field_name', '');
                }

            },


            changeIndicatorPanel : function(html, unique_index, index){
                var block_panel = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="block_panel"] .reports-params');
                if(block_panel.length == false) return;
                var panel_index = null;
                if(unique_index){
                    panel = $(block_panel).find('.element[data-type="panel"][data-unique_index="'+unique_index+'"]');
                    panel_index = panel.index();
                } else if(index !== null){
                    panel = $(block_panel).find('.element[data-type="panel"]').eq(index);
                    panel_index = panel.index();
                }

                if(unique_index){
                    html = $(html);
                    html.attr('data-unique_index', unique_index);
                }

                if(typeof(panel) != 'undefined' && panel.length > 0){
                    $(panel).after(html).remove();
                } else {
                    $(block_panel).find('.reports-params').append(html);

                    panel_index = $(block_panel).find('.reports-params .element[data-type="panel"]').length - 1;
                }

                Reports.Constructor.clearIndicatorPanelUniqueIndex(panel_index);

                Reports.Constructor.setIndicatorPanelColor();
            },


            clearIndicatorPanelUniqueIndex : function(index){
                if(index === null || typeof(index) == 'undefined') return;

                var block_panel = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="block_panel"] .reports-params');
                var panel_unique_index = $(block_panel).find('.element[data-type="panel"]').eq(index).data('unique_index');
                var indicator_inique_index = $('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="settings"] .element[data-type="setting"] .element[data-type="indicator"]').eq(index).data('unique_index');

                if(panel_unique_index != indicator_inique_index){
                    $(block_panel).find('.element[data-type="panel"]').eq(index).attr('data-unique_index', '');
                }
            },


            setIndicatorPanelColor : function(){
                var color_list = {};
                $('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="settings"] .element[data-type="setting"]').each(function(i,  ul){
                    color_list[i] =$(ul).find('.element[data-type="color"]').val();
                });

                $('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"] .element[data-type="block_panel"] .element[data-type="panel"]').each(function(i,  ul){
                    $(ul).addClass(color_list[i]);
                });
            },

            removeBlock : function(_this){
                var block = $(_this).closest('.element[data-type="block"][data-module="reports"]');

                switch(block.data('element_type')){
                    case 'indicator' :
                        $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="data_analysis"] .element[data-type="add_indicator_block"]').show();
                        block.remove();
                        break;
                    default :
                        block.remove();
                }
            },


            removePanel: function(_this){
                var block = $(_this).closest('.element[data-type="block"][data-module="reports"]');

                switch(block.data('element_type')){
                    case 'indicator' :
                        $(_this).closest('.element[data-type="settings"]').find('.element[data-type="add_indicator"]').show();
                        var panel = $(_this).closest('.element[data-type="setting"]');
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="block_panel"] .reports-params .element[data-type="panel"]:eq('+panel.index()+')').remove();
                        panel.remove();
                        Reports.Constructor.layoutIndicators();
                        Reports.Constructor.triggerElements();
                        break;
                    case 'data_analysis' :
                        $(_this).closest('.element[data-type="block_panel"]').addClass('to_remove');
                        // filters
                        Reports.Constructor.changeElement(_this, 'data_analysis_indicator_module', {'this_ui_clear' : true, 'data_analysis_panel_delete' : true}, function(data){
                            Reports.Constructor.changeFilters(_this, data);
                            Reports.Constructor.InitNewSelects();

                            // Indicator
                            Reports.Constructor.changeElement(_this, 'indicator_add', {'data_analysis_panel_delete' : true}, function(data){
                                if(data && !$.isEmptyObject(data)){
                                    Reports.Constructor.clearIndicators(_this, $(_this).closest('.element[data-type="block_panel"]').data('unique_index'), data);
                                    Reports.Constructor.InitNewSelects();
                                }

                                // graph
                                Reports.Constructor.changeElement(_this, 'graph', {'data_analysis_panel_delete': true}, function(data){
                                    if(data && !$.isEmptyObject(data)){
                                        data.graph_element = null;
                                        Reports.Constructor.changeGraphics(_this, data, null);
                                        Reports.Constructor.InitNewSelects();
                                    }

                                    // graph
                                    Reports.Constructor.changeElement(_this, 'graph', {'data_analysis_panel_delete': false}, function(data){
                                        if(data && !$.isEmptyObject(data)){
                                            Reports.Constructor.changeGraphics(_this, data, null);
                                            Reports.Constructor.InitNewSelects();
                                        }
                                        $(_this).closest('.element[data-type="block_panel"]').remove();
                                        Reports.Constructor.layoutIndicators();
                                    }, $('.element[data-type="block"].element[data-module="reports"].element[data-element_type="graph"]').length)
                                }, $('.element[data-type="block"].element[data-module="reports"].element[data-element_type="graph"]').length);

                            }, $('.element[data-type="block"].element[data-module="reports"].element[data-element_type="indicator"]').length);

                        });




                        break;
                    case 'filter' :
                        $(_this).closest('.element[data-type="block_panel"]').remove();
                        Reports.Constructor.UpdateOutputElements();
                        break;
                }
            },


            //changedDataAnalysisTitle
            changedDataAnalysisTitle : function(_this){
                var block_panel = $(_this).closest('.element[data-type="block_panel"]');
                var unique_index = block_panel.data('unique_index');
                var title = block_panel.find('.element[data-type="title"]').val();

                // indicator
                var block = $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="indicator"]');

                if(typeof(block) != 'undefined' && block.length > 0){
                    block.find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i, ul){
                        $(ul).find('option').each(function(i, ul){
                            if($(ul).data('unique_index') == unique_index) $(ul).text(title);
                        })
                    })
                    block.find('.element[data-type="block_panel"] .element[data-type="panel"]').each(function(i, ul){
                        if($(ul).data('unique_index') == unique_index) $(ul).find('.reports-name').text(title);
                    })
                    $('.reports-mark').selectpicker('refresh');
                }
                // graph
                var block = $(_this).closest('.edit-view').find('.element[data-type="block"][data-module="reports"][data-element_type="graph"]');

                if(typeof(block) != 'undefined' && block.length > 0){
                    block.find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i, ul){
                        $(ul).find('option').each(function(i, ul){
                            if($(ul).val() == unique_index) $(ul).text(title);
                        })
                    })
                    $('.element[data-element_type="graph"] select').selectpicker('refresh');
                }


            },


            UpdateOutputElements : function(){
                Reports.Constructor.changeElement(null, 'update_output_elements', {}, function(data){
                    //indicator_panels
                    var block_panel = $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="indicator"]');
                    if(data.indicator_panels && !$.isEmptyObject(data.indicator_panels) && block_panel.length != 0){
                        $.each(data.indicator_panels, function(unique_index, html){
                            Reports.Constructor.changeIndicatorPanel(html, unique_index, null);
                        })
                    }

                    //graph_element
                    if(data.graph_element){
                        $('.edit-view .element[data-type="block"][data-module="reports"][data-element_type="graph"] .element[data-type="graph"]').each(function(i, ul){
                            Reports.Constructor.changeGraphElement(ul, data.graph_element);
                        });
                    }

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.triggerElements();
                    Reports.afterViewChanges(true);
                }, null, true);

            },


            UpdateOutputElementsAll : function(_this){
                Reports.Constructor.changeElement(_this, 'data_analysis_panel_settings', {}, function(data) {
                    Reports.Constructor.changeFilters(_this, data);
                    Reports.Constructor.changeIndicators(_this, data);
                    Reports.Constructor.changeGraphics(_this, data, null);

                    Reports.Constructor.InitNewSelects();
                    Reports.Constructor.layoutIndicators();
                    Reports.Constructor.UpdateOutputElements();

                    Reports.afterViewChanges(true);
                })
            },



            //initiation new selects
            InitNewSelects : function(){
                var $dropdown = $('.crm-dropdown > .dropdown-toggle').removeAttr('data-toggle');

                $('.modal .edit-view[data-copy_id="8"] select').each(function(){
                    if (!$(this).next().hasClass('bootstrap-select')) {
                        $(this).selectpicker({style: 'btn-white',noneSelectedText: ' '});
                    }
                });
                $dropdown.off('click');
                $dropdown.on('click',function(e){
                    var $this = $(this),
                        $element = $this.parent();

                    if ($this.is('.dropdown-toggle')) {
                        var param, $menu,
                            block = $('.crm-dropdown.open').not($this.parent()).removeClass('open');

                        $element.toggleClass('open');
                        if ($element.is('[data-type="drop_down"]')) {
                            block.addClass('open');
                        }

                        $('.bootstrap-select.open').removeClass('open');

                        $menu = $this.parent().filter('.open').find('>.dropdown-menu');

                        if (!$menu.length) return;

                        // $menu.removeClass('no-top').css({
                        //     'margin-top': 29
                        // });
                        $menu.removeClass('no-top');

                        param = {
                            top: parseInt($menu.css('padding-top')),
                            bottom: parseInt($menu.css('padding-bottom')),
                            scroll: $('#modal_dialog_container .modal:last').scrollTop()
                        };

                        if (($(window).height())  < ($menu.offset().top + $menu.height() + param.top )) {
                            $menu.css({
                                'margin-top': - ($menu.height() + param.top + param.bottom )
                            });

                            $menu.addClass('no-top');
                        }
                    }
                    $this.parent().filter('.open').find('ul:first').removeClass('hide');
                });
                Global.showParticipant();
            },


            //initialization sorting
            initSorting : function(){
                $('.element[data-type="block_data_analysis"] .inputs-block').each(function() {
                    var forSorting = $(this).find('.drag-marker').parent();
                    $(this).sortable({
                        dropOnEmpty: true,
                        items: forSorting,
                        cancel: ".dropdown-menu, .form-control, .select, .todo-remove, .field-param",
                        start: function( event, ui ) {
                            $('.element[data-type="block_data_analysis"] .inputs-block').sortable('refresh');
                        }
                    });
                });
                $('.element[data-type="block_filter"] .inputs-block').each(function() {
                    var forSorting = $(this).find('.drag-marker').parent();
                    $(this).sortable({
                        dropOnEmpty: true,
                        items: forSorting,
                        cancel: ".dropdown-menu, .form-control, .select, .todo-remove, .field-param",
                        start: function( event, ui ) {
                            $('.element[data-type="block_filter"] .inputs-block').sortable('refresh');
                        }
                    });
                });
                $('.element[data-type="block_data_analysis"] .inputs-block > li').show();
            },


            //set indicators field
            layoutIndicators : function(){
                $.each($('.reports-params'), function () {
                    var reportsCell = $(this).find('.reports-cell');

                    reportsCell.each(function(){
                        $(this).css('width',100/reportsCell.length+'%');
                    });
                })
            },

            triggerElements : function(){
                var $subMenu,
                    $paramAnalysis = $('.element[data-data_analysis_type="data_analysis_param"] select.element[data-type="module_copy_id"]');

                if ($paramAnalysis.val() == "") {
                    $('select.element[data-type="module_copy_id"]').prop('disabled', 'disabled').selectpicker('refresh');
                    $paramAnalysis.prop('disabled', false).selectpicker('refresh');
                    $paramAnalysis.parent().next().find('.dropdown-menu').addClass('hide');
                } else {
                    $paramAnalysis.parent().next().find('.dropdown-menu').removeClass('hide');
                };
                $('.element[data-element_type="filter"] select.element[data-type="module_copy_id"]').each(function(){
                    if ($(this).val() == "") {
                        $(this).closest('li').find('select.element[data-type="field_name"]').prop('disabled', 'disabled').selectpicker('refresh');
                    };
                });

                var $list = $('.element[data-data_analysis_type="data_analysis_indicator"] [data-type="filter_block_panels"]:visible select.element[data-type="module_copy_id"]');
                $.each($list, function(){
                    var $this = $(this),
                        $dropDown = $this.parent().next().find('.dropdown-menu');

                    if ($this.val() == "") {
                        $dropDown.addClass('hide');
                    } else {
                        $dropDown.removeClass('hide');
                    };
                });
                $('.element[data-element_type="filter"] select.element[data-type="field_name"]').each(function(){
                    var $this = $(this),
                        $dropDown = $this.parent().next().find('.dropdown-menu');

                    if ($this.val() == "") {
                        $dropDown.addClass('hide');
                    } else {
                        $dropDown.removeClass('hide');
                    };
                });

                $subMenu = $('[data-element_type="data_analysis"] .sub-menu:visible');
                if ($subMenu.length) {
                    $subMenu.prev().addClass('hide');
                }
            },

            formatingDatesInit : function(){
                var dateFormat = function () {
                    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                        timezoneClip = /[^-+\dA-Z]/g,
                        pad = function (val, len) {
                            val = String(val);
                            len = len || 2;
                            while (val.length < len) val = "0" + val;
                            return val;
                        };

                    // Regexes and supporting functions are cached through closure
                    return function (date, mask, utc) {
                        var dF = dateFormat;

                        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
                        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
                            mask = date;
                            date = undefined;
                        }

                        // Passing date through Date applies Date.parse, if necessary
                        date = date ? new Date(date) : new Date;
                        if (isNaN(date)) return //throw SyntaxError("invalid date");

                        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

                        // Allow setting the utc argument via the mask
                        if (mask.slice(0, 4) == "UTC:") {
                            mask = mask.slice(4);
                            utc = true;
                        }

                        var _ = utc ? "getUTC" : "get",
                            d = date[_ + "Date"](),
                            D = date[_ + "Day"](),
                            m = date[_ + "Month"](),
                            y = date[_ + "FullYear"](),
                            H = date[_ + "Hours"](),
                            M = date[_ + "Minutes"](),
                            s = date[_ + "Seconds"](),
                            L = date[_ + "Milliseconds"](),
                            o = utc ? 0 : date.getTimezoneOffset(),
                            flags = {
                                d:    d,
                                dd:   pad(d),
                                ddd:  dF.i18n.dayNames[D],
                                dddd: dF.i18n.dayNames[D + 7],
                                m:    m + 1,
                                mm:   pad(m + 1),
                                mmm:  dF.i18n.monthNames[m],
                                mmmm: dF.i18n.monthNames[m + 12],
                                yy:   String(y).slice(2),
                                yyyy: y,
                                h:    H % 12 || 12,
                                hh:   pad(H % 12 || 12),
                                H:    H,
                                HH:   pad(H),
                                M:    M,
                                MM:   pad(M),
                                s:    s,
                                ss:   pad(s),
                                l:    pad(L, 3),
                                L:    pad(L > 99 ? Math.round(L / 10) : L),
                                t:    H < 12 ? "a"  : "p",
                                tt:   H < 12 ? "am" : "pm",
                                T:    H < 12 ? "A"  : "P",
                                TT:   H < 12 ? "AM" : "PM",
                                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                            };

                        return mask.replace(token, function ($0) {
                            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
                        });
                    };
                }();

                // Some common format strings
                dateFormat.masks = {
                    "default":      "ddd mmm dd yyyy HH:MM:ss",
                    shortDate:      "m/d/yy",
                    mediumDate:     "mmm d, yyyy",
                    longDate:       "mmmm d, yyyy",
                    fullDate:       "dddd, mmmm d, yyyy",
                    shortTime:      "h:MM TT",
                    mediumTime:     "h:MM:ss TT",
                    longTime:       "h:MM:ss TT Z",
                    isoDate:        "yyyy-mm-dd",
                    isoTime:        "HH:MM:ss",
                    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
                    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
                };

                var monthNamesFromBase = Message.locale.monthNamesSA.abbreviated.concat(Message.locale.monthNamesSA.wide);
                var dayNamesFromBase = Message.locale.weekDayNamesSA.abbreviated.concat(Message.locale.weekDayNamesSA.wide);

                // Internationalization strings
                dateFormat.i18n = {
                    dayNames: dayNamesFromBase/*[
                     "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
                     "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
                     ]*/,
                    monthNames: monthNamesFromBase/*[
                     "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
                     "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
                     ]*/
                };

                // For convenience...
                Date.prototype.format = function (mask, utc) {
                    return dateFormat(this, mask, utc);
                };
            },



            //Filter
            Filter : {
                clearCondition : function(_this){
                    var obj = $(_this).closest('.element[data-type="block_panel"]')
                        .find('.element[data-name="condition"] option')
                        .empty()
                        .parent()
                        .html('<option value=""></option>')
                    obj.selectpicker('refresh');
                },


                clearConditionValue : function(_this){
                    $(_this).closest('.element[data-type="block_panel"]').find('.element[data-name="condition_value"]').remove();
                },


                setConditionValue : function(_this, copy_id, field_name, condition_value, callback){
                    if(!copy_id){
                        if(typeof callback == 'function') return callback();
                    }
                    data = {
                        'field_name' : field_name,
                        'condition_value' : condition_value,
                        'this_template' : 0,
                    }

                    $.get(Global.urls.url_filter_add_condition_value+'/'+copy_id, data, function(data){
                        $(_this).closest('.element[data-type="block_panel"]')
                            .find('.filter-box-condition-value')
                            .html(data.data)
                            .find('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});

                        // show single calendar
                        if ($(data.data).find('.dateinput').length) {
                            var $dateinput = $(_this).closest('.element[data-type="block_panel"]').find('.dateinput')
                            Filter.singleCalendar($dateinput);
                            $dateinput.datepicker('setDate', new Date());
                        }
                        // show range calendar
                        if ($(data.data).find('.dp1').length) {
                            var $dp1 = $(_this).closest('.element[data-type="block_panel"]').find('.dp1'),
                                $dp2 = $(_this).closest('.element[data-type="block_panel"]').find('.dp2');
                            Filter.rangeCalendar($dp1, $dp2);
                            date1 = 0;
                            date2 = 0;
                        }

                        if(typeof callback == 'function') callback();
                    }, 'json');
                },




                setCondition : function(_this, copy_id, field_name, cb){
                    $.get(Global.urls.url_filter_add_condition+'/'+copy_id, {'field_name' : field_name}, function(data){
                        $(_this).closest('.element[data-type="block_panel"]')
                            .find('.filter-box-condition')
                            .html(data.data)
                            .find('select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                        cb();
                    }, 'json');
                },



                changedFieldName : function(_this, callback){
                    var block_panel = $(_this).closest('.element[data-type="block_panel"]');
                    var copy_id = block_panel.find('.element[data-type="module_copy_id"]').val();
                    var condition_value = block_panel.find('.element_filter[data-name="condition"]').val();

                    if(!$(_this).val()){
                        Reports.Constructor.Filter.clearCondition(_this);
                        Reports.Constructor.Filter.clearConditionValue(_this);
                        Reports.Constructor.Filter.setConditionValue(_this, copy_id, '', condition_value, function(){
                            if(typeof callback == 'function') callback();
                        });
                        return;
                    }


                    Reports.Constructor.Filter.setCondition(_this, copy_id, $(_this).val(), function(data_value){
                        var condition_value = $(_this).closest('.element[data-type="block_panel"]').find('.element_filter[data-name="condition"]').val();
                        Reports.Constructor.Filter.setConditionValue(_this, copy_id, $(_this).val(), condition_value, function(){
                            if(typeof callback == 'function') callback();
                        });
                    });
                },
            },





        },

        //Report
        Report : {
            _instance: null,
            _handler_after_request: null,

            view : function(_this, reports_id, $callback){
                Reports.getReportUrl(reports_id, function(url){
                    var contentInstance = Global.getInstance().getContentReloadInstance() || ContentReload.createInstance();

                    contentInstance.hidePreloader = function () {
                        if (this.preloader) {
                            this.preloader.hide()
                        }
                        if ($callback) {
                            $callback();
                        }
                        return this;
                    };

                    contentInstance
                        .clear() // TODO: щось мышаэ обекту. і тут очищамо його.
                        .setVars({
                            'selector_content_box' : '#content_container'
                        })
                        .setUrl(url)
                        .loadPage();
                });

            },
            getInstance : function(){
                return Reports.Report._instance;
            },
            createInstance : function(){
                var Obj = function(){
                    for(var key in Reports.Report){
                        this[key] = Reports.Report[key];
                    }
                }

                return Reports.Report._instance = new Obj();
            },
            setCcallbackAfterRequest : function (handler) {
                this._handler_after_request = handler;
                return this;
            },
            getUSDateIntervalValue : function(){
                var value = {};

                var dis = $('.list_view_block[data-module="reports"] .element[data-type="dis"]').val(),
                    die = $('.list_view_block[data-module="reports"] .element[data-type="die"]').val();

                if(dis) value['_date_interval_start'] = dis;
                if(die) value['_date_interval_end'] = die;

                return {'date_interval' : value};
            },


            getUSGraphParamsValue : function(_this){
                var value = Reports.Report.getIndicators(_this);
                var graph_unique_index = $(_this).closest('.element[data-type="block"]').find('.element[data-type="graph_element"]').attr('unique_index');
                var graph_indicators = {};
                graph_indicators[graph_unique_index] = value;
                return {'graph_indicators' : graph_indicators};
            },


            updateUserStorage : function(_this, type, callback){
                var value;
                var reports_id = $('.list_view_block[data-module="reports"]').data('id');
                switch(type){
                    case 'date_interval' :
                        value = Reports.Report.getUSDateIntervalValue();
                        break;

                    case 'graph_indicators' :
                        value = Reports.Report.getUSGraphParamsValue(_this);
                        break;
                    default :
                        return;
                }

                var ajax = new Ajax();
                ajax
                    .setUrl('/module/reports/saveUserStorage/' + Reports.copy_id)
                    .setData({'reports_id' : reports_id, 'type' : type, 'value' : value})
                    .setCallBackSuccess(function(data){
                        if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                        } else {
                            if(data.status == 'error'){
                                Message.show(data.messages);
                            } else {
                                if(typeof(callback) == 'function')
                                    callback(data);
                            }
                        }
                    })
                    .setCallBackError(function(jqXHR, textStatus, errorThrown){
                        Message.showErrorAjax(jqXHR, textStatus);
                    })
                    .send();

            },


            getIndicators : function(_this, skip_index){
                var result = {};
                var element;

                $(_this).closest('.element[data-type="block"]').find('.element[data-type="settings"] .element[data-type="indicator"]').each(function(i, ul){
                    if(skip_index && skip_index == i) return true;
                    result[i] = $(ul).val();
                })
                return result;
            },

            //addGraphData
            addGraphData : function(_this, update_user_storage, callback){
                var graph_block = $(_this).closest('.element[data-type="block"]');
                var data = ['id=' + $('.sm_extension').data('id')];

                var search = $('.list_view_block[data-module="reports"] .search-input').val();
                if(search) data.push('search=' + search);

                var filters = Filter.getFilterInstaled();
                if(!$.isEmptyObject(filters)) data.push(filters.join('&'));

                if(!$.isEmptyObject(data))
                    data = '?' +  data.join('&');

                var _params = {
                    'reports_id' : $('.list_view_block').data('id'),
                    'graph_period' :  graph_block.find('.element[data-type="period"]').val(),
                    'graph_indicators' : Reports.Report.getIndicators(_this),
                    'graph_display_option' : graph_block.find('.element[data-type="display_option"]').val(),
                    'graph_unique_index' : graph_block.find('.element[data-type="graph_element"]').attr('unique_index'),
                };

                // this.reloadGraph({
                //     'update_user_storage' : update_user_storage,
                //     'params' : _params,
                // }, data, callback)

                var _data = {
                    'update_user_storage' : update_user_storage,
                    'params' : _params,
                };


                AjaxObj
                    .createInstance()
                    .setUrl('/module/reports/addGraphData/' + Reports.copy_id + data)
                    .setData(_data)
                    .setDataType('json')
                    .setCallBackSuccess(function(data){
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
                    })
                    .send();

                return this;
            },

            reloadGraph : function (_data, _search, callback) {
                var _this = this;

                AjaxObj
                    .createInstance()
                    .setUrl('/module/reports/addGraphData/' + Reports.copy_id + _search)
                    .setData(_data)
                    .setDataType('json')
                    .setCallBackSuccess(function(data){
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
                    })
                    .send();
                return this;
            },

            graphIndicator : {
                add : function(_this, data){
                    $(_this).closest('li').before(data.html_indicator);

                    Reports.Report.addGraphData(_this, true, function(data){
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="graph_element"]').after(data.html_graph).remove();
                    });

                    Reports.Report.InitNewSelects(_this);

                    var graph_type = $(_this).closest('.element[data-type="block"]').find('.element[data-type="graph_element"]').data('graph_type');
                    var indicator_count = $(_this).closest('.element[data-type="block"]').find('.element[data-type="settings"] .element[data-type="indicator"]').length;

                    if(data['max_indicators'][graph_type] <= indicator_count){
                        $(_this).closest('.element[data-type="block"]').find('.element[data-type="settings"] .element[data-type="add_indicator"]').hide();
                    }
                },

                change : function(_this, data){
                    //change  graph_element
                    $(_this).closest('.element[data-type="block"]').find('.element[data-type="graph_element"]').after(data.html_graph).remove();
                },


                delete : function(_this){
                    var settings = $(_this).closest('.element[data-type="settings"]');
                    $(_this).closest('.element[data-type="block"]').find('.element[data-type="settings"] .element[data-type="add_indicator"]').show();
                    $(_this).closest('li').remove();

                    Reports.Report.addGraphData(settings, true, function(data){
                        $(settings).closest('.element[data-type="block"]').find('.element[data-type="graph_element"]').after(data.html_graph).remove();
                    });
                },
            },




            //initiation new selects
            InitNewSelects : function(_this){
                var block = $(_this).closest('.element[data-type="block"]');
                block.find('select').each(function(){
                    if (!$(this).next().hasClass('bootstrap-select')) {
                        $(this).selectpicker({style: 'btn-white',noneSelectedText: ' '});
                    }
                });
                block.find('.crm-dropdown > .dropdown-toggle').removeAttr('data-toggle').on('click',function(){
                    block.find('.crm-dropdown.open').removeClass('open');
                    $(this).parent().toggleClass('open');
                });
            },


            selectsOptionsOrganize : function(_this){
                var selcetedIndex = $(_this).find('option:selected').index()+1;
                $(_this).closest('li').addClass('primarysel')
                $(_this).closest('ul').find('li:not(".primarysel") select[data-type="indicator"] option:disabled').removeAttr("disabled");
                $(_this).closest('ul').find('li:not(".primarysel") select[data-type="indicator"] option:nth-child('+selcetedIndex+')').attr("disabled","disabled").removeAttr("selected");
                var selcetedIndex = $(_this).closest('ul').find('li:not(".primarysel") select[data-type="indicator"] option:selected').index()+1;
                $(_this).closest('ul').find('li.primarysel select[data-type="indicator"] option:disabled').removeAttr("disabled");
                $(_this).closest('ul').find('li.primarysel select[data-type="indicator"] option:nth-child('+selcetedIndex+')').attr("disabled","disabled").removeAttr("selected");
                $(_this).closest('ul').find('li.primarysel').removeClass('primarysel');
                $(_this).closest('ul').find('.selectpicker').selectpicker('refresh');
            },
            getColumnWidth : function(){
                var col_index = {};
                $('.list-table thead').find('th').each(function(i, ul){
                    if(i==0) return true;
                    if($(ul).css('display') == 'none') return true;
                    col_index[$(ul).data('group_index')] = $(ul).width();
                });
                return col_index;
            }
        },
        remove : function () {
            //Reports.preloaderForGraph.remove();

            Global.removeEvents(this._events);
        },
        destroy: function () {
            this.remove();

            Events.removeHandler({ key: 'ReportsDestroy', type: Events.TYPE_DESTROY});

            return null;
        }
    }

    var deleteInterval = null,
        arrByDeleteFilter = [];

    for(var key in _private) {
        _self[key] = _private[key];
    }

    exports.Reports = Reports;
})(window);
