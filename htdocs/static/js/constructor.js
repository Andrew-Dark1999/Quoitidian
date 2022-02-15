var sortableInit = function () {
    var subModule = {
            $: $('[data-child_element="sub_module"]')
        },
        configSortable = {
            placeholder: "ui-state-highlight",
            delay: 150,
            sort: function (event, ui) {
                var $target = $(event.target),
                    helperHeight = parseInt(ui.helper.css('height'));

                if (!subModule.iterationCount) {
                    ui.placeholder.height(helperHeight);
                }
                subModule.iterationCount++;
                ui.helper.css({left: 0});
                if ($target.offset().top < Mouse.axis.y) {
                    var edge,
                        lowerEdge = $target.offset().top + parseInt($target.css('height'));

                    if (lowerEdge < Mouse.axis.y + helperHeight) {
                        edge = lowerEdge - helperHeight;
                        //$(ui.placeholder).appendTo($(event.target).find('>div:last'));
                    } else edge = Mouse.axis.y;

                    ui.helper.offset({top: edge});
                } else {
                    //$(ui.placeholder).before($(event.target).find('>div:first'));
                    ui.helper.offset({top: $target.offset().top}); // from parent
                }

            },
            start: function () {
                subModule.iterationCount = 0;
                $(document).on("mousemove", Mouse.event.mousemove);
            },
            stop: function () {
                $(document).off("mousemove");
            }
        },
        container = {
            $submodule: $('.element[data-sub_type="submodule"]'),
            $standart: $('[data-sub_type="standart"]')
        };

    $(".inputs-block").sortable({
        connectWith: ".inputs-block",
        dropOnEmpty: true,
        cancel: ".dropdown-menu, .form-control, .select, .display-option, .todo-remove, .field-param, .element_field_type_params_select, .submod, li[todo-element='true'], li.inputs-group[big-w='true']"
    });

    container.$submodule.sortable(configSortable);
    container.$standart.sortable(configSortable);

};

(function (exports) {
    var _private, _public, AutoNumbering,
        _self = null; //link for instance

    _private = {
        staticText: '.static_text_auto_number_dropdown',
        sm: '.module_auto_number_dropdown',
        _generic_name: null,

        events: function () {
            this._events = [
                {parent: document, selector: '.auto_number_remove', event: 'click', func: this.onClickAutoNumberRemove},
                {parent: document, selector: '.set_auto_number_field+.settings-menu-select .option', event: 'click', func: this.onClickAutoOption},
                {parent: document, selector: 'li.auto_number .static_text_auto_number_dropdown input.form-control', event: 'change', func: this.onChangeAutoField},
                //{ parent: document, selector: '.auto_number .select-menu .option', event: 'click', func: this.onClickSelectOption },
            ]

            Global.addEvents(this._events, {
                instance: _self
            });
            return this;
        },
        // onClickSelectOption :  function (e) {
        //     var element = $(e.currentTarget),
        //         count = element.closest('[data-type="field_type_params"]').find('.auto_number').not('.default'),
        //         value = element.attr('value');
        //
        //     if ((value == 'sm' ||  value== 'static_text')) {
        //         e.data.instance.addSettings(element.closest('.auto_number'));
        //         // if (count.length == 1) {
        //         //     element.closest('li').addClass('first_element');
        //         // }
        //     }
        // },
        onClickAutoNumberRemove: function (e) {
            var _this = $(this).closest('li'),
                instance = e.data.instance,
                liAuNu = $(this).closest('.element_field_type_params').find('li.auto_number.default');

            _this.remove();
            instance.autoNumberSave(liAuNu);
            liAuNu.closest('[data-type="field_type_params"]').find('.add_autonumber_field').show();
            instance.autonumber(liAuNu.closest('ul').find('.auto_number'));
            instance.showContent(_this.find('>.settings-menu-select .selected'));
        },
        onChangeAutoField: function (e) {
            e.data.instance.autoNumberSave($(this).closest('.element_field_type_params').find('li.auto_number'));
        },
        reLoadSmField: function ($li) {
            var clonedr, module,
                $list = $li || $([]);

            //find sm field
            if (!$list.length) {
                var _elements = $('.auto_number:not(.default) select.set_auto_number_field');
                $.each(_elements, function () {
                    var $this = $(this);

                    if ($this.val() == 'sm') {
                        $list = $this.closest('.auto_number');
                        return false;
                    }
                });
            }

            if (!$list.find('.module_auto_number_dropdown').length) {
                clonedr = _self.addSettings($list, '.module_auto_number_dropdown');

                clonedr.find('.field_auto_number_select').closest('li').hide();
                module = clonedr.find('.module_auto_number_select').val();
                clonedr.find('.field_auto_number_select[select-module-id="' + module + '"]').closest('li').show();
            }
            $list.find('.static_text_auto_number_dropdown').remove();
            //set new value of SM Module
            _self.setValueSM();
        },
        onClickAutoOption: function (e) {
            var liAuNu = $(this).closest('.auto_number'),
                instance = e.data.instance,
                value = liAuNu.find('.set_auto_number_field').val();

            switch (value) {
                case 'static_text': {
                    if (!liAuNu.find('.static_text_auto_number_dropdown').length) {
                        _self.addSettings(liAuNu, '.static_text_auto_number_dropdown');
                    }
                    liAuNu.find('.module_auto_number_dropdown').remove();
                    break;
                }
                case 'sm': {
                    instance.reLoadSmField(liAuNu);
                    break;
                }
                default: {
                    liAuNu.find('.static_text_auto_number_dropdown').remove();
                    liAuNu.find('.module_auto_number_dropdown').remove();

                    break;
                }
            }

            _self.autoNumberSave(liAuNu);
        },
        getGenericNameByDOM: function () {
            this._generic_name = $('.auto_number').closest('ul').find('input.element_params[data-type="name_generate"]').val();

            return this;
        },
        setValueSM: function (reload_fields) {
            var $element = $('.auto_number:not(.default) .module_auto_number_dropdown .module_auto_number_select');

            $.each(Constructor.getInstance()._listRelatedSM || [], function (key, json) {
                if (!$element.find('option[value="' + json.id + '"]').length) {
                    var $parent = $element.parent(),
                        value = '<option value="' + json.id + '">' + json.text + '</option>',
                        option = '<option value="' + json.id + '" class="option">' + json.text + '</option>',
                        $btn = $parent.find('.filter-option');

                    $parent.find('select').append(value);
                    $parent.find('.select-menu').append(option);

                    if (!$btn.text()) {
                        $btn.text($parent.find('select option[value="' + $element.val() + '"]').text());
                    }
                }
            })

            if (Constructor.getInstance()._listRelatedSM) {
                var value = Constructor.getInstance()._listRelatedSM[0]['id'];
                $element.val(value);
            }

            /*
            if(reload_fields !== false){
                _self.reLoadSmField();
            }
            */
        }
    }

    _public = {
        hideFields: function (list, adding) { //hidden lists
            var object = this,
                result = [], available = {},
                elementDefault = list.filter('.default'),
                params = list.closest('.element_field_type_params').find('[data-type="name_generate"]');

            list = list.not('.default');
            list.find('[value]').show();

            $.each(elementDefault.find('option'), function (index, value) {
                available[$(value).val()] = $(value).text();
            });

            if (adding) { // add new li
                $.each(list, function (index, val) {
                    var _this = $(this),
                        el = _this.find('.selected'),
                        key = el.attr('value');

                    if (!(key in available)) {
                        key = Object.keys(available)[0];
                        $(val).find('.option[value="' + key + '"]');
                        $(val).find('button .filter-option').text(available[key])
                        $(val).find('.option').removeClass('selected').filter('[value="' + key + '"]').addClass('selected')
                        $(val).find('select').val(key);

                        switch (key) {
                            case 'static_text': {
                                object.addSettings($(list[index]), _private.staticText);
                                break;
                            }
                            case 'sm': {
                                object.addSettings($(list[index]), _private.sm);
                                $(list[index]).find('.dropdown-menu li').eq(2).hide();
                                $(list[index]).find('.dropdown-menu li').eq(3).hide();
                                break;
                            }
                        }
                    }

                    list.not(this).find('.option[value="' + key + '"]').remove();

                    result.push('{' + key + ':' + available[key] + '}')
                    delete available[key];
                });
            } else { // by refresh
                if (!params.val().length) return;

                var paramArray = JSON.parse(params.val());
                available = {};

                $.each(paramArray, function (key, val) {
                    var $value = $(list[key]);

                    if (typeof val == 'object') {
                        key = Object.keys(val)[0];

                        if ($.isNumeric(key)) {
                            key = 'sm';
                        }
                        available[key] = elementDefault.find('option[value="' + key + '"]').text();
                    } else {
                        key = val.substring(1, val.length - 1).split(':');
                        available[key[0]] = key[1];
                    }

                    $value.find('>.settings-menu-select .filter-option').text(available[key]);
                    $value.find('.option').removeClass('selected').filter('[value="' + key + '"]').addClass('selected');
                    $value.find('>select').val(key);
                    list.not($value).find('>.settings-menu-select .option[value="' + key + '"]').remove();
                });
            }

            if (list.length == elementDefault.find('option').size()) {
                list.closest('ul').find('.add_autonumber_field').hide();
            }

            return Object.keys(available).length;
        },
        showContent: function (value) {
            var ul = $('.element_field_type_params:visible');

            value.removeClass('selected');
            ul.find('li.auto_number').not('.default').find('>.settings-menu-select .select-menu').append(value);
            return ul.find('li.auto_number').not('.default').find('[value="' + value.attr('value') + '"]').length;
        },
        addSettings: function (li, field) {
            var $menu = li.closest('[data-type="field_type_params"]').find('.dropdown_params_auto_number ' + field).clone();
            $menu.removeClass('hidden').appendTo(li);
            return $menu;
        },
        autonumber: function (list) {
            var list = list.not('.default');
            if (list.length == 1) {
                //list.addClass('first_element')
                list.find('.auto_number_remove').hide();
            } else {
                //list.removeClass('first_element');
                list.find('.auto_number_remove').show();
            }
            ;
        },
        init: function () {
            var _this = this;
            $(document).on('click', '.auto_number .select-menu .option', function (e) {
                var element = $(e.currentTarget),
                    count = element.closest('[data-type="field_type_params"]').find('.auto_number').not('.default'),
                    value = element.attr('value');

                if ((value == 'sm' || value == 'static_text')) {
                    _this.addSettings(element.closest('.auto_number'));
                    if (count.length == 1) {
                        element.closest('li').addClass('first_element');
                    }
                }
            });
        },
        getGenericName: function () {
            return this._generic_name;
        },
        setGenericName: function (title) {
            this._generic_name = title;

            return this;
        },
        start: function ($element, onInit) {
            var $li,
                $context = $element.closest('div'),
                cloneLi = $element.closest('.element_field_type_params').find('li.auto_number.default').first().clone();

            cloneLi.removeClass('default hidden').find('.auto_number_remove').show();

            if (!onInit) {
                cloneLi.insertBefore($context);
            }

            $li = $context.closest('.element_field_type_params').find('li.auto_number');

            !this.hideFields($li, true) ? $element.hide() : '';

            this.autoNumberSave($context.closest('.element_field_type_params').find('li.auto_number'));

            _self.autonumber($li);
        },
        addIndependentCheckbox: function ($this) {
            var data,
                module = $this.closest('.module_auto_number_dropdown'),
                li = module.find('li:first').clone().html('').addClass('added_checkbox'),
                params = $('.dropdown_params_auto_number').closest('ul').find('[data-type="name_generate_params"]'),
                generateParams = params.val().length ? JSON.parse(params.val()) : {};

            li.append('<div class="checkbox"><label><input type="checkbox" class="element_params" data-type="independent">' + Message.translate_local("Independent numbering") + '</label></div>');
            module.find('ul').append(li);
            data = {
                'copy_id': $this.closest('.module_auto_number_dropdown').find('select:last').attr('select-module-id'),
                'name': $this.closest('.module_auto_number_dropdown').find('select:last').val(),
                'independed_numeration': Object.keys(generateParams).length ? generateParams[0]['independed_numeration'] : false
            };

            $('.added_checkbox input').prop('checked', data['independed_numeration']);
            params.val('[' + JSON.stringify(data) + ']');

            var event = ['click', '.element_field_type_params [data-type="independent"]'];

            $(document).off(event[0], event[1]).on(event[0], event[1], function () {
                var param = $('.dropdown_params_auto_number').closest('ul').find('[data-type="name_generate_params"]'),
                    object = JSON.parse(param.val());

                object[0]['independed_numeration'] = $(this).prop('checked');
                param.val(object.length ? JSON.stringify(object) : '[' + JSON.stringify(object) + ']');
            })
        },
        addRelateFields: function () {
            $.each($('[data-type="relate_module_copy_id"]'), function () {
                var $this = $(this);
                Constructor.getFromUrlModuleFields($this, function (data) {
                    var $clone,
                        $data = $(data),
                        li = $('.auto_number .module_auto_number_dropdown li');

                    if (!li.find('select[select-module-id="' + $this.val() + '"]').length) {
                        $clone = li.first().clone();
                        $data.addClass('field_auto_number_select select').attr('select-module-id', $this.val());
                        $clone.hide().html('');
                        $clone.prepend($data);
                        li.parent().first().append($clone);
                        Constructor.customSelect();
                    }
                    //relate_field.after(data).remove();
                });
            });
        },
        autoNumberRestore: function (liAuNu) {
            var ulDrop = liAuNu.closest('ul');
            //liAuNu.hide();
            var keys = [];
            $('.set_auto_number_field option').each(function () {
                keys.push($(this).attr('value'));
            });
            $('.set_auto_number_field').closest('li').show();
            var paramsNameGenerate = ulDrop.find('input.element_params[data-type="name_generate"]').val();
            if (paramsNameGenerate && typeof paramsNameGenerate == 'string' && paramsNameGenerate.length > 4 && !ulDrop.is('.restored')) {
                liAuNu.closest('ul').find('.auto_number').not('.default, .hidden').remove(); // rebuild from JSON
                var paramsArr = JSON.parse(paramsNameGenerate),
                    paramsArrRev = paramsArr.reverse();

                $.each(paramsArr, function (i, val) {
                    var obj = val, ind = i;
                    $.each(keys, function (i, val) {
                        if (val in obj) {
                            var cloneLi = ulDrop.children('li.auto_number.default').clone();
                            cloneLi.removeClass('default hidden').prependTo(ulDrop);
                            if (val == 'static_text') {
                                var txtDropInput = ulDrop.find('.dropdown_params_auto_number .static_text_auto_number_dropdown').clone();
                                txtDropInput.removeClass('hidden').find('input.form-control[name="static_text"]').val(obj[val]);
                                cloneLi.append(txtDropInput);
                            }
                            cloneLi.find('.set_auto_number_field').val(val);
                            Constructor.customSelectRemove();
                            Constructor.customSelect();
                            return false;
                        } else if (keys.length - 1 == i) { // if "true" -- module field
                            var cloneLi = ulDrop.children('li.auto_number.default').clone();
                            cloneLi.removeClass('default hidden');
                            cloneLi.prependTo(ulDrop);
                            var moduleDropInput = ulDrop.find('.dropdown_params_auto_number .module_auto_number_dropdown').clone(),
                                module = null, field = null;
                            $.each(obj, function (i, val) {
                                module = i;
                                field = val;
                            });
                            moduleDropInput.removeClass('hidden').find('.module_auto_number_select').val(module);
                            moduleDropInput.find('.field_auto_number_select').closest('li').hide();
                            moduleDropInput.find('.field_auto_number_select[select-module-id="' + module + '"]').val(field).closest('li').show();
                            cloneLi.append(moduleDropInput);
                            cloneLi.find('.set_auto_number_field').val(val);
                            Constructor.customSelectRemove();
                            Constructor.customSelect();
                        }
                    });
                });
            }
            ulDrop.addClass('restored');
        },
        autoNumberSave: function (liAuNu) {
            var ulDrop = liAuNu.closest('ul'),
                _this = this;

            if (liAuNu.length > 0) {
                var mkOrdr = function () {
                    var resOrd = [];
                    ulDrop.find('li.auto_number').not('.default').each(function () {
                        var $this = $(this),
                            select = $this.find('.set_auto_number_field'),
                            selVal = select.val();

                        switch (selVal) {
                            case 'static_text': {
                                resOrd.push('{"' + selVal + '":"' + $this.find('.static_text_auto_number_dropdown input.form-control').val() + '"}');
                                break;
                            }
                            case 'sm': {
                                var $dropdown = $this.find('.module_auto_number_dropdown');

                                var module = $dropdown.find('.module_auto_number_select').val(),
                                    field = $dropdown.find('.field_auto_number_select[select-module-id="' + module + '"]').val();

                                if (module && field) {
                                    resOrd.push('{"' + module + '":"' + field + '"}');
                                }
                                break
                            }
                            default: {
                                resOrd.push('{"' + selVal + '":"' + select.find('option[value="' + selVal + '"]').text() + '"}');
                                break;
                            }
                        }
                    });
                    var str = resOrd.join(',');
                    if (resOrd.length) {
                        resOrd = '[' + str + ']';
                    } else {
                        resOrd = [];
                    }
                    ulDrop.find('input.element_params[data-type="name_generate"]').val(resOrd);
                    _this._generic_name = resOrd;
                };
                mkOrdr();
            }

            return this;
        },
        addSettings: function (li, field) {
            var $menu = li.closest('[data-type="field_type_params"]').find('.dropdown_params_auto_number ' + field).clone();
            $menu.removeClass('hidden').appendTo(li);
            return $menu;
        },
        changeSMModule: function () {
            _self.setValueSM();
            return this;
        },
        destroy: function () {

        }
    }

    AutoNumbering = function () {
        _self = this;

        for (var key in _private) {
            _self[key] = _private[key];
        }

        _private
            .events()
            .getGenericNameByDOM();

        for (var key in _public) {
            this[key] = _public[key];
        }

        this.init();

        return this;
    }

    //static - not visible throw instance
    AutoNumbering.autoNumberSave = _public.autoNumberSave;
    AutoNumbering.addRelateFields = _public.addRelateFields;
    //exports from module
    exports.AutoNumbering = AutoNumbering;
})(window);

// var AutoNumber = {
//     staticText: '.static_text_auto_number_dropdown',
//     sm: '.module_auto_number_dropdown',
//     hideFields : function (list, adding) { //hidden lists
//         var object = this,
//             result=[], available = {},
//             elementDefault = list.filter('.default'),
//             params =  list.closest('.element_field_type_params').find('[data-type="name_generate"]');
//
//         list = list.not('.default');
//         list.find('[value]').show();
//
//         $.each(elementDefault.find('option'), function (index,value) {
//             available[$(value).val()] = $(value).text();
//         });
//
//         if (adding) { // add new li
//             $.each(list, function (index,val) {
//                 var _this = $(this),
//                     el = _this.find('.selected'),
//                     key = el.attr('value');
//
//                 if (!(key in available)) {
//                     key = Object.keys(available)[0];
//                     $(val).find('.option[value="'+key+'"]');
//                     $(val).find('button .filter-option').text(available[key])
//                     $(val).find('.option').removeClass('selected').filter('[value="'+key+'"]').addClass('selected')
//                     $(val).find('select').val(key);
//
//                     switch (key) {
//                         case 'static_text': {
//                             object.addSettings($(list[index]),object.staticText);
//                             break;
//                         }
//                         case 'sm': {
//                             object.addSettings($(list[index]),object.sm);
//                             $(list[index]).find('.dropdown-menu li').eq(2).hide();
//                             $(list[index]).find('.dropdown-menu li').eq(3).hide();
//                             break;
//                         }
//                     }
//                 }
//
//                 list.not(this).find('.option[value="'+key+'"]').remove();
//
//                 result.push('{'+key+':'+available[key]+'}')
//                 delete available[key];
//             });
//         } else { // by refresh
//             if (!params.val().length) return;
//
//             var paramArray = JSON.parse(params.val());
//             available = {};
//
//             $.each(paramArray, function (key, val) {
//                 var $value = $(list[key]);
//
//                 if (typeof val == 'object') {
//                     key = Object.keys(val)[0];
//
//                     if ($.isNumeric(key)) {
//                         key = 'sm';
//                     }
//                     available[key] = elementDefault.find('option[value="'+key+'"]').text();
//                 } else {
//                     key = val.substring(1, val.length-1).split(':');
//                     available[key[0]] = key[1];
//                 }
//
//                 $value.find('>.settings-menu-select .filter-option').text(available[key]);
//                 $value.find('.option').removeClass('selected').filter('[value="'+key+'"]').addClass('selected');
//                 $value.find('>select').val(key);
//                 list.not($value).find('>.settings-menu-select .option[value="'+key+'"]').remove();
//             });
//         }
//
//         if (list.length == elementDefault.find('option').size()) {
//             list.closest('ul').find('.add_autonumber_field').hide();
//         }
//
//         return Object.keys(available).length;
//     },
//     showContent : function (value) {
//         var ul = $('.element_field_type_params:visible');
//
//         value.removeClass('selected');
//         ul.find('li.auto_number').not('.default').find('>.settings-menu-select .select-menu').append(value);
//         return ul.find('li.auto_number').not('.default').find('[value="'+value.attr('value')+'"]').length;
//     },
//     addIndependentCheckbox : function ($this) {
//         var data,
//             module = $this.closest('.module_auto_number_dropdown'),
//             li = module.find('li:first').clone().html('').addClass('added_checkbox'),
//             params = $('.dropdown_params_auto_number').closest('ul').find('[data-type="name_generate_params"]'),
//             generateParams = params.val().length ? JSON.parse(params.val()) : {};
//
//         li.append('<div class="checkbox"><label><input type="checkbox" class="element_params" data-type="independent">'+Message.translate_local("Independent numbering")+'</label></div>');
//         module.find('ul').append(li);
//         data = {
//             'copy_id': $this.closest('.module_auto_number_dropdown').find('select:last').attr('select-module-id'),
//             'name': $this.closest('.module_auto_number_dropdown').find('select:last').val(),
//             'independed_numeration': Object.keys(generateParams).length ? generateParams[0]['independed_numeration'] : false
//         };
//
//         $('.added_checkbox input').prop('checked', data['independed_numeration']);
//         params.val('['+JSON.stringify(data)+']');
//
//         $(document).off('click', '.element_field_type_params [data-type="independent"]');
//         $(document).on('click', '.element_field_type_params [data-type="independent"]', function(){
//             var param = $('.dropdown_params_auto_number').closest('ul').find('[data-type="name_generate_params"]'),
//                 object = JSON.parse(param.val());
//
//             object[0]['independed_numeration'] = $(this).prop('checked');
//             param.val(object.length ? JSON.stringify(object) : '['+JSON.stringify(object)+']');
//         })
//     },
//     addRelateFields : function () {
//         $.each($('[data-type="relate_module_copy_id"]'), function () {
//             var $this = $(this);
//             Constructor.getFromUrlModuleFields($this, function (data) {
//                 var $clone,
//                     $data = $(data),
//                     li = $('.auto_number .module_auto_number_dropdown li');
//
//                 if (!li.find('select[select-module-id="'+$this.val()+'"]').length) {
//                     $clone = li.first().clone();
//                     $data.addClass('field_auto_number_select select').attr('select-module-id', $this.val());
//                     $clone.hide().html('');
//                     $clone.prepend($data);
//                     li.parent().append($clone);
//                     Constructor.customSelect();
//                 }
//                 //relate_field.after(data).remove();
//             });
//         });
//     }
// }
/*
block
|-----block_buttons
|               |-----button_date_ending
|               |-----button_subscription
|               |-----button_responsible
|-----block_participant
|-----block_attachments
|-----block_activity
|-----block_panel_contact
|               |-----block_field_type_contact
|                     |------field_type_hidden
|-----block_panel
|               |-----panel
|               |-----field
|               |	  |-----label
|               |	             |-----block_field_type
|               |                 		|-----field_type
|               |
|               |-----table
|                         |-----table_column
|			                     |-----table_header
|			                     |-----edit
|                                |-----table_footer
|-----sub_module
|-----attachments
|-----activity
*/
/*
    Парсинг полей и создание схемы
*/
var ElementParser = {
    relate_index: 0,
    getValue: function (object) {
        var value = '';
        switch ($(object).attr('type')) {
            case 'checkbox' :
            case 'radio' :
                value = ($(object).prop("checked") ? '1' : '0');
                break;
            case 'text' :
            case 'hidden' :
                value = $(object).val();
                break;
        }
        return value;
    },

    getElementParams: function (ul, field_type_pr) {
        var date_time = '';

        $(ul).find('.element_params').each(function (i, ul) {
            if ($(ul).attr('type') == 'checkbox' && $(ul).data('type') == 'size') {
                field_type_pr[$(ul).data('type')] = $(ul).prop("checked") ? 65535 : 255;
                field_type_pr['maxLength'] = $(ul).prop("checked") ? 65535 : 255;
            } else if ($(ul).attr('type') == 'checkbox') {
                field_type_pr[$(ul).data('type')] = $(ul).prop("checked") ? 1 : 0;
            } else if ($(ul).hasClass('date')) {
                if ($(ul).val()) date_time = $(ul).val();
            } else if ($(ul).hasClass('time')) {
                if ($(ul).val()) {
                    if (date_time) field_type_pr[$(ul).data('type')] = date_time + ' ' + $(ul).val();
                } else {
                    if (date_time) field_type_pr[$(ul).data('type')] = date_time;
                }
            } else if ($(ul).hasClass('params_value_json')) {
                field_type_pr[$(ul).data('type')] = $(ul).text();
            } else {
                field_type_pr[$(ul).data('type')] = $(ul).val();
            }
        });

        return field_type_pr;
    },


    getParams: function (ul, field_type_pr) {
        //field_type_params
        field_type_pr['type'] = $(ul).find('.element_field_type').val();
        if (field_type_pr['type'] == 'relate' || field_type_pr['type'] == 'relate_string') {
            ElementParser.relate_index++;
            field_type_pr['relate_index'] = ElementParser.relate_index;
        }

        field_type_pr = ElementParser.getElementParams(ul, field_type_pr);

        return field_type_pr;
    },

    getSelectParams: function (ul, field_type_pr) {
        //field_type_params_select
        var select_option = {};
        var select_color = {};
        var select_sort = {};
        var select_remove_forbid = [];
        var select_finished_object = [];
        var select_slug = [];
        var key = '';
        $(ul).find('.element[data-type="field_type_params_select"]').find('.element_params[data-type="select_option"]').each(function (i, ul) {
            key = $(ul).data('id');
            select_option[key] = $(ul).val();
            select_color[key] = $(ul).data('color');
            select_sort[key] = $(ul).data('sort');
            select_remove_forbid[key] = $(ul).data('remove');
            select_finished_object[key] = $(ul).data('finished_object');
            select_slug[key] = $(ul).data('slug');
        });
        field_type_pr['values'] = select_option;
        field_type_pr['select_color'] = select_color;
        field_type_pr['select_sort'] = select_sort;
        field_type_pr['select_remove_forbid'] = select_remove_forbid;
        field_type_pr['select_finished_object'] = select_finished_object;
        field_type_pr['select_slug'] = select_slug;

        return field_type_pr;
    },

    parseJsonParams: function (params) {
        var data = {};
        $.each(params, function (key, value) {
            if (value == 'true' || value === true) {
                data[key] = '1';
            } else if (value == 'false' || value === false) {
                data[key] = '0';
            } else {
                data[key] = value;
            }
        });
        return data;
    },

    init: function () {
        var elements = [];
        var module_params = {};
        var group_index = 0;
        $('.constructor').find('.element').each(function (i, ul) {
            //general module params
            if ($(ul).data('type') == 'module_params') {
                module_params[$(ul).data('name')] = ElementParser.getValue($(ul));
            } else
            //block or sub_module
            if ($(ul).data('type') == 'block') {
                var block_pr = {};
                var block = {
                    'type': $(ul).data('type'),
                };

                block_pr['title'] = $(ul).find('.element_block_title').val();
                $(ul).children('.element_params').each(function (i, ul) {
                    block_pr[$(ul).data('type')] = $(ul).val();
                });
                block['params'] = block_pr;

                var sub_module_fields = [];
                $(ul).find('.element[data-type="sub_module_params"] input:checked').each(function (i, ul) {
                    sub_module_fields.push($(ul).val());
                });

                var block_panel = [];
                var block_panel_element = {};
                var block_panel_list = [];
                $(ul).children('.element').each(function (i, ul) {
                    if ($(ul).data('type') == 'block_panel_contact' || $(ul).data('type') == 'block_panel') {
                        //block_panel_contact
                        if ($(ul).data('type') == 'block_panel_contact') {
                            var block_panel_element = {
                                'type': $(ul).data('type'),
                                'params': {'make': true},
                            };

                            var field_type_hidden_el = [];
                            var count_edit_hidden = 0;

                            $(ul).find('.element[data-type="block_field_type_contact"]').each(function (i, ul) {
                                var block_field_type_contact = {
                                    'type': $(ul).data('type'),
                                };
                                $(ul).find('.element[data-type="field_type_hidden"]').each(function (i, ul) {
                                    count_edit_hidden++;
                                    group_index++;
                                    field_type_hidden_pr = {};
                                    var field_type_hidden = {
                                        'type': 'edit_hidden',
                                    };


                                    ElementParser.getElementParams(ul, field_type_hidden_pr);
                                    /*
                                    $(ul).find('.element_params').each(function(i, ul){
                                        field_type_hidden_pr[$(ul).data('type')] = $(ul).val();
                                    });
                                    */


                                    field_type_hidden_pr['group_index'] = group_index;
                                    field_type_hidden['params'] = field_type_hidden_pr;
                                    field_type_hidden_el.push(field_type_hidden);
                                });
                                block_field_type_contact['params'] = {
                                    'count_edit_hidden': count_edit_hidden,
                                    'destroy': $(ul).find('.element_params_contact[data-type="deconstrustroy"]').val()
                                };

                                block_field_type_contact['elements'] = field_type_hidden_el;
                                block_panel_element['elements'] = block_field_type_contact;
                                block_panel_list.push(block_panel_element);
                            });

                            block_panel.push({'block_panel': block_panel_list});
                        } else
                        //block_panel
                        if ($(ul).data('type') == 'block_panel') {
                            block_panel_element = {
                                'type': $(ul).data('type'),
                            };
                            var block_panel_el = [];
                            var count_panels = 0;

                            //panel
                            $(ul).children('.inputs-block').children('.element[data-type="panel"]').each(function (i, ul) {
                                var panel = {
                                    'type': $(ul).data('type'),
                                };
                                var panel_el = [];
                                var field_el = [];

                                panel_pr = ElementParser.parseJsonParams(JSON.parse($(ul).children('.element_params').text()));

                                panel_pr['list_view_visible'] = ($(ul).find('.element_params[data-type="list_view_visible"]').attr('checked') == 'checked' ? 1 : 0);
                                panel_pr['process_view_group'] = ($(ul).find('.element_params[data-type="process_view_group"]').attr('checked') == 'checked' ? 1 : 0);
                                panel_pr['count_select_fields'] = $(ul).find('.element_count_select_fields option').length;
                                //panel_pr['destroy']  = $(ul).find('.element_params[data-type="destroy"]').val();


                                count_panels++;

                                // field or table
                                var active_count_select_fields = 0;
                                $(ul).children('.element[data-type="table"], .element[data-type="label"], .element[data-type="block_field_type"]').each(function (i, ul) {
                                    //table...
                                    if ($(ul).data('type') == 'table') {
                                        ElementParser.relate_index++;
                                        active_count_select_fields = $(ul).find('.element[data-type="table_column"]').length;
                                        //if(active_count_select_fields > 5) active_count_select_fields = 5;
                                        var table = {
                                            'type': $(ul).data('type'),
                                            'params': {
                                                'count_table_column': active_count_select_fields,
                                                'relate_index': ElementParser.relate_index,
                                            },
                                        };
                                        var table_el = [];
                                        //table_column
                                        $(ul).children('.element[data-type="table_column"]').each(function (i, ul) {
                                            var table_column = {
                                                'type': $(ul).data('type'),
                                            };
                                            var table_column_el = [];
                                            // table_header
                                            $(ul).children('.element[data-type="table_header"], .element[data-type="field_type"], .element[data-type="table_footer"]').each(function (i, ul) {
                                                //table_header
                                                if ($(ul).data('type') == 'table_header') {
                                                    table_column_el.push({
                                                        'type': $(ul).data('type'),
                                                        'params': {
                                                            'title': $(ul).val(),
                                                        },
                                                    })
                                                }
                                                //field_type
                                                if ($(ul).data('type') == 'field_type') {
                                                    var block_field_type_el = [];
                                                    var field_type_pr = {};
                                                    var field_type = {
                                                        'type': 'edit',
                                                    };
                                                    //field_type_params
                                                    var date_time = '';
                                                    field_type_pr['type'] = $(ul).find('.element_field_type').val();
                                                    $(ul).find('.element[data-type="field_type_params"]').find('.element_params').each(function (i, ul) {
                                                        if ($(ul).attr('type') == 'checkbox') field_type_pr[$(ul).data('type')] = $(ul).prop("checked") ? 1 : 0;
                                                        else if ($(ul).hasClass('date')) {
                                                            if ($(ul).val()) date_time = $(ul).val();
                                                        } else if ($(ul).hasClass('time')) {
                                                            if ($(ul).val()) {
                                                                if (date_time) field_type_pr[$(ul).data('type')] = date_time + ' ' + $(ul).val();
                                                            } else {
                                                                if (date_time) field_type_pr[$(ul).data('type')] = date_time;
                                                            }
                                                        }
                                                        if ($(ul).hasClass('params_value_json')) {
                                                            field_type_pr[$(ul).data('type')] = $(ul).text();
                                                        } else
                                                            field_type_pr[$(ul).data('type')] = $(ul).val();

                                                    });
                                                    //field_type_params_select
                                                    var select_option = {};
                                                    var key = '';
                                                    $(ul).find('.element[data-type="field_type_params_select"]').find('.element_params[data-type="select_option"]').each(function (i, ul) {
                                                        key = $(ul).data('id');
                                                        select_option[key] = $(ul).val();
                                                    });
                                                    field_type_pr['values'] = select_option;
                                                    field_type['params'] = field_type_pr;
                                                    block_field_type_el.push(field_type);
                                                    table_column_el.push(block_field_type_el);
                                                }
                                                //table_footer
                                                if ($(ul).data('type') == 'table_footer') {
                                                    table_column_el.push({
                                                        'type': $(ul).data('type'),
                                                        'params': {
                                                            'title': $(ul).val(),
                                                        },
                                                    })
                                                }
                                            });
                                            table_column['elements'] = table_column_el;
                                            table_el.push(table_column);
                                        });
                                        table['elements'] = table_el;
                                        panel_el.push({'table': table});
                                    } else
                                    //label
                                    if ($(ul).data('type') == 'label') {
                                        var label = {
                                            'type': $(ul).data('type'),
                                            'params': {
                                                'title': $(ul).val(),
                                            },
                                        };
                                        group_index++;
                                        field_el.push(label);
                                    } else
                                    //block_field_type
                                    if ($(ul).data('type') == 'block_field_type') {
                                        var block_field_type = {
                                            'type': $(ul).data('type'),
                                        };
                                        var block_field_type_el = [];
                                        // field_type
                                        $(ul).children('.element[data-type="field_type"]').each(function (i, ul) {
                                            var field_type_pr = {};
                                            var field_type = {
                                                'type': 'edit'
                                            };

                                            //Params
                                            field_type_pr = ElementParser.getParams(ul, field_type_pr);
                                            //SelectParams
                                            field_type_pr = ElementParser.getSelectParams(ul, field_type_pr);

                                            field_type_pr['group_index'] = group_index;

                                            active_count_select_fields++;
                                            if (active_count_select_fields > 5) active_count_select_fields = 5;
                                            field_type['params'] = field_type_pr;
                                            block_field_type_el.push(field_type);
                                        });
                                        block_field_type['elements'] = block_field_type_el;
                                        block_field_type['params'] = {'count_edit': active_count_select_fields};
                                        field_el.push(block_field_type);
                                    }
                                });
                                panel_el.push({'field': field_el});

                                panel['elements'] = panel_el;

                                panel['params'] = panel_pr;
                                block_panel_el.push(panel);
                            });

                            block_panel_element['params'] = {'count_panels': count_panels};
                            block_panel_element['elements'] = block_panel_el;
                            block_panel_list.push(block_panel_element);

                            block_panel = [];
                            block_panel.push({'block_panel': block_panel_list});
                            block_panel_list = [];
                        }
                    } else
                    //sub_module
                    if ($(ul).data('type') == 'sub_module') {
                        ElementParser.relate_index++;
                        var relate_links = [];
                        $(ul).closest('.element[data-child_element="sub_module"]').find('.element_params[data-type="relate_links"]').each(function (i, ul) {
                            relate_links.push({'name': $(ul).val(), 'checked': ($(ul).attr('checked') ? 1 : 0)});
                        });
                        var sub_module = {
                            'type': $(ul).data('type'),
                            'params': {
                                'relate_module_copy_id': $(ul).data('relate_module_copy_id'),
                                'relate_module_template': $(ul).data('relate_module_template'),
                                'relate_index': ElementParser.relate_index,
                                'relate_type': $('.element_params[data-type="relate_type"]').val(),
                                'relate_links': relate_links,
                                'values': sub_module_fields,
                            }
                        };
                        block_panel.push({'sub_module': sub_module});
                    }
                    //block_button
                    if ($(ul).data('type') == 'block_button') {
                        count_buttons = 0;
                        var block_button = {
                            'type': $(ul).data('type'),
                        };

                        var field_type_el = [];
                        $(ul).find('.element[data-type="button"]').each(function (i, ul) {
                            group_index++;
                            var field_type_pr = {};

                            //Params
                            field_type_pr = ElementParser.getParams(ul, field_type_pr);

                            //SelectParams
                            field_type_pr = ElementParser.getSelectParams(ul, field_type_pr);

                            field_type_pr['group_index'] = group_index;
                            field_type_el.push({
                                'type': 'button',
                                'params': field_type_pr,
                            });

                            count_buttons++;
                        });
                        block_button['elements'] = field_type_el;
                        block_button['params'] = {'count_buttons': count_buttons};
                        block_panel.push({'block_button': block_button});
                    }
                    //activity
                    if ($(ul).data('type') == 'block_activity') {
                        var activity_pr = {};
                        group_index++;
                        $(ul).find('.element_params').each(function (i, ul) {
                            activity_pr[$(ul).data('type')] = $(ul).val();
                        });
                        activity_pr['group_index'] = group_index;

                        var activity = {
                            'type': 'activity',
                            'params': activity_pr,
                        };
                        block_panel.push({'activity': activity});
                    }
                    //participant
                    if ($(ul).data('type') == 'block_participant') {
                        var participant_pr = {};
                        group_index++;
                        // hidden element_params
                        $(ul).find('.element_params').each(function (i, ul) {
                            participant_pr[$(ul).data('type')] = $(ul).val();
                        });
                        // menu element_params
                        ElementParser.getElementParams($(ul).closest('.element[data-type="block"][data-child_element="block_participant"]').find('.element[data-type="field_type_params"]'), participant_pr);

                        participant_pr['group_index'] = group_index;

                        var participant = {
                            'type': 'participant',
                            'params': participant_pr,
                        };
                        block_panel.push({'participant': participant});
                    }
                    //attachments
                    if ($(ul).data('type') == 'block_attachments') {
                        var attachments_pr = {};
                        group_index++;

                        $(ul).find('.element_params').each(function (i, ul) {
                            attachments_pr[$(ul).data('type')] = $(ul).val();
                        });
                        attachments_pr['group_index'] = group_index;

                        var attachments = {
                            'type': $(ul).data('type'),
                            'params': attachments_pr,
                        };
                        block_panel.push({'attachments': attachments});
                    }
                });
                block['elements'] = block_panel;
                elements.push({'block': block});
            } // block END
        });
        return {'module_params': module_params, 'elements': elements};
    }
};

var Constructor = {
    field_name_list: [],
    _instance: null,
    _type: 'constructor',
    _instance_auto_number: null,
    _listRelatedSM: null, // list of SM Modules - static

    TYPE_FIELD_AUTO_NUMBERING: 'auto_number',

    getInstance: function (status) {
        if (!Constructor._instance && status) {
            Constructor._instance = this.createInstance();
        }
        return Constructor._instance;
    },

    createInstance: function () {
        var Obj = function () {
            for (var key in Constructor) {
                this[key] = Constructor[key];
            }
        }

        return Constructor._instance = new Obj().constructor();
    },
    events: function () {
        var path = this.actions;

        this._events = [
            //Block buttons
            {parent: document, selector: '.constructor_btn-add-block-button', event: 'click', func: path.onClickAddBlockButton},
            {parent: document, selector: '.element[data-type="button"] .delete-btn', event: 'click', func: path.onClickDeleteBtn},
            // end block buttons
            {parent: document, selector: '.modal_dialog[data-controller="edit_module"]', event: 'click', func: path.onClickOpenModule},
            {parent: document, selector: '.modal_dialog[data-controller="create_module"]', event: 'click', func: path.onClickCreateModule},
            {parent: document, selector: '.btn-action[data-controller="module_copy"], .btn-action-modal[data-controller="module_copy"]', event: 'click', func: path.onClickCopyModule},
            {parent: document, selector: '.constructor_btn-show-block-list', event: 'click', func: path.onClickShowBlockList},
            {parent: document, selector: '.btn-action-modal[data-controller="module_settings"]', event: 'click', func: path.onClickActionModalModuleSettings},
            {parent: document, selector: '.constructor_btn-set-settings', event: 'click', func: path.onClickBtnSetSettings},
            {parent: document, selector: '.constructor_btn-show-submodule-list', event: 'click', func: path.onClickBtnShowSubmoduleList},
            // удаление модуля
            {parent: document, selector: '.btn-action[data-controller="module_delete"], .btn-action-modal[data-controller="module_delete"]', event: 'click', func: path.onClickBtnModuleDelete},
            // установка статуса - активен/не активен
            {parent: document, selector: '.module_set_status', event: 'change', func: path.onClickSetModuleStatus},
            //add submodule block
            {parent: document, selector: '.constructor_btn-add-submodule', event: 'click', func: path.onClickBtnAddSubModule},
            {parent: document, selector: '.add_element_field_hidden', event: 'click', func: path.onClickAddElementFieldHidden},
            {parent: document, selector: '.add_element_block_panel_contact', event: 'click', func: path.onClickAddElementBlockPanelContact},
            {parent: document, selector: '.add_element_panel_field', event: 'click', func: path.onClickAddElementPanelField},
            //добавляем поле списка для  параметра select -> option
            {parent: document, selector: '.constructor .add-field', event: 'click', func: path.onClickAddField},
            // Изменение количества елементов Тип поля в поле конструктора
            {parent: document, selector: '.element_count_select_fields li a', event: 'click', func: path.onClickElementCountSelectFields},
            //Add blocks
            {parent: document, selector: '.constructor_btn-add-blocks', event: 'click', func: path.onClickBtnAddBlocks},
            {parent: document, selector: '.add_element_panel_table', event: 'click', func: path.onClickAddElementPanelTable},
            {parent: document, selector: '.remove_block_panel_contact', event: 'click', func: path.onClickRemoveBlockPanelContact},
            {parent: document, selector: '.todo-remove-contact', event: 'click', func: path.onClickTodoRemoveContanct},
            // revove Panel or ParamsSelect
            {parent: document, selector: '.constructor .todo-remove', event: 'click', func: path.onClickTodoRemove},
            // Смена параметров поля в Конструкторе
            {parent: document, selector: '.add_element_field_type_params', event: 'click', func: path.onClickAddElementFieldTypeParams},
            // Смена параметров поля в Конструкторе на елемента "кнопка"
            {parent: document, selector: '.constructor .add_element_field_type_params_for_button', event: 'click', func: path.onClickAddElementFieldTypeParamsForButton},
            // while change FieldType - remove his params
            {parent: document, selector: 'select.element_field_type', event: 'change', func: path.onChangeSelectElementFieldType},
            // обновление списка полей связаной таблицы  в параметрах для типа поля relate
            {parent: document, selector: '.element_params[data-type="relate_module_copy_id"]', event: 'change', func: path.onChangeReloadListRelateTable},
            //main setting menu
            {parent: document, selector: '.element_field_type_params > li input, .element_field_type_params', event: 'click', func: path.onClickMainSettingsMenu},
            // show settings submenu
            {parent: document, selector: '.show_element_field_type_params_select', event: 'click', func: path.onShowElementParamsSelect},
            // hide settings submenu
            {parent: document, selector: '.crm-dropdown > .dropdown-toggle', event: 'click', func: path.onClickDropDownToogle},
            {parent: document, selector: '.settings .sub-menu *', event: 'click', func: path.onClickSettingsSubMenu},
            {parent: document, selector: '#constructor_data_type', event: 'change', func: path.onChangeConstructorDataType},
            // сохранение и инсталяция  модуля
            {parent: document, selector: '.constructor_btn-save', event: 'click', func: path.onClickBtnSave},
            // удаление данных модуля
            {parent: document, selector: '.btn-action[data-controller="module_data_delete"]', event: 'click', func: path.onClickRemoveDataModule},
            {parent: document, selector: '.form-datetime.bootstrap-timepicker', event: 'show.timepicker', func: path.onShowTimePicker},
            {parent: document, selector: '.navigation_module_up', event: 'click', func: path.onClickNavigationModuleUp},
            {parent: document, selector: '.navigation_module_down', event: 'click', func: path.onClickNavigationModuleDown},
            {parent: document, selector: '.constructor .select-color .dropdown-menu li a .label', event: 'click', func: path.onClickLabel},
            {parent: document, selector: '.dropdown_params_auto_number .add_autonumber_field', event: 'click', func: path.onClickAutoAddField},
            {parent: document, selector: '.static_text_auto_number_dropdown .dropdown-toggle, .module_auto_number_dropdown .dropdown-toggle', event: 'click', func: path.onClickAutoDropdownToggle},
            {parent: document, selector: '.module_auto_number_select+.settings-menu-select .option', event: 'click', func: path.onClickAutoSelect},
            {parent: document, selector: '.field_auto_number_select+.settings-menu-select .option', event: '', func: path.onClickAutoSave},
            {parent: document, selector: '.constructor .buttons-block .btn-st', event: 'click', func: path.onClickBtnSet},
            {parent: document, selector: '.modal-dialog .close-button, .modal.in', event: 'click', func: path.onChangeUrl},
            {parent: document, selector: '.constructor select.select', event: 'change', func: path.onHideBsDropdown},
            {parent: document, selector: '.constructor .settings-menu .settings-menu-select', event: 'click', func: path.onClickSettingsMenu},
            {parent: document, selector: '.constructor .settings-menu .settings-menu-select .option', event: 'click', func: path.onClickSettingsMenuItem},
            {parent: $('body'), selector: '.constructor', event: 'click', func: path.onClickOutside},
        ]

        Global.addEvents(this._events, {
            instance: this
        });
        return this;
    },
    constructor: function () {
        this.listOfValue = [];
        this.$ = $('.constructor');

        this.getFilter = {};
        for (var key in Filter) {
            this.getFilter[key] = Filter[key]; // clone object
        }

        this.copyId = this.$.data('copy_id');
        this.extension_id = this.$.data('extension_id');

        this.aliasMoveTo()
            .events() // register events
            .allMethod(); // все события
        this.type = 'constructor';

        return this;
    },


    storeRelateModule: function () {
        var _this = this;

        this._listRelatedSM = [];

        var list = $('.element_block_panel .inputs-block [data-type="block_field_type"] select.element_field_type')
        $.each(list, function (key, data) {
            var $parent = $(data).parent();

            if ($(data).val() == 'relate') {
                var $relateLink = $parent.find('[data-type="relate_module_copy_id"]'),
                    _value = $relateLink.val();

                _this._listRelatedSM.push({
                    id: _value,
                    text: $relateLink.find('option[value="' + _value + '"]').text()
                });
            }
        });

        if (!this._listRelatedSM.length) {
            this._listRelatedSM = null;
        }

        return this;
    },

    isAutoNumberExist: function (bool) {
        // TODO: It is need use parameter from server
        this._instance_auto_number = $('.auto_number.default').length ? new AutoNumbering() : null;

        return this;
    },
    actions: {
        onClickOutside: function (e) {
            if (!e.target.closest('.settings-menu-select')) {
                $('.constructor .settings-menu .settings-menu-select .select-menu').hide();
            }
            $('.bootstrap-select.open>.open').parent().removeClass('open');
        },
        onClickSettingsMenu: function (e) {
            var liAuNu,
                $this = $(this),
                $selectMenu = $this.find('.select-menu');

            if ($selectMenu.is(':visible')) {
                $selectMenu.hide();
                liAuNu = $this.closest('.element_field_type_params').find('li.auto_number');
                AutoNumbering.autoNumberSave(liAuNu);
            } else {
                $('.constructor .settings-menu .settings-menu-select .select-menu').hide();
                if ($this.find('.option').length > 10) {
                    if ($this.find('.wrap').length > 0) {
                    } else {
                        $selectMenu.css('padding', '5px 0').wrapInner('<div class="wrap"></div>');
                        $this.find('.wrap').height(240).niceScroll({
                            cursorcolor: "#1FB5AD",
                            cursorborder: "0px solid #fff",
                            cursorborderradius: "0px",
                            cursorwidth: "3px",
                            railalign: 'right',
                            preservenativescrolling: false,
                            autohidemode: false
                        });
                    }
                }
                $selectMenu.show();
            }
        },
        onClickSettingsMenuItem: function (e) {
            var $this = $(this),
                instAutoNumber = e.data.instance._instance_auto_number,
                $select = $this.closest('.settings-menu-select').prev(),
                menuSelect = $this.closest('.settings-menu-select'),
                ul = $this.closest('.element_field_type_params');

            if ($this.closest('li').is('.auto_number')) {
                instAutoNumber.oldElement = menuSelect.find('.selected');
                if (instAutoNumber.oldElement.attr('value') === $this.attr('value')) return;

                ul.find('.auto_number:not(.default)').not($this.closest('.auto_number')).find('.option[value="' + $this.attr('value') + '"]').remove();

                instAutoNumber.oldElement.removeClass('selected');
                instAutoNumber.showContent(instAutoNumber.oldElement);
            }

            menuSelect.find('.selected').removeClass('selected')
            menuSelect.prev().val($this.attr('value'));
            menuSelect.find('.filter-option').text($this.text());


            var addedCheckbox = $(this).closest('.module_auto_number_dropdown').find('li.added_checkbox');
            if ($select.find('option[value="' + $this.attr('value') + '"]').attr('type') == 'select') {
                if (!addedCheckbox.length) instAutoNumber.addIndependentCheckbox($this);
            } else $this.closest('ul').find('.added_checkbox').remove();

            $this.addClass('selected');
            if ($this.closest('.settings-menu-select').prev().is('.element_params[data-type="relate_module_copy_id"]')) {
                var sel = $this.closest('.settings-menu-select').prev();
                Constructor.updateFieldListFromRelate(sel, sel.val());
                $this.closest('li').next().find('.settings-menu-select').remove();
                Constructor.customSelect();
            }

            e.data.instance.storeRelateModule();
            if (instAutoNumber) {
                instAutoNumber.reLoadSmField();

                if ($(this).attr('value') == "sm") {
                    instAutoNumber.addRelateFields();
                }
            }
        },
        onHideBsDropdown: function () {
            $(this).next().removeClass('open');
        },
        onChangeUrl: function (e) {
            history.pushState(null, null, location.pathname + location.search);
        },
        onClickBtnSet: function (e) {
            $('.constructor .btn-st+.settings-menu select.form-control').each(function () {
                if ($(this).next().hasClass('settings-menu-select')) {
                } else {
                    $(this).after('<div class="settings-menu-select"><button class="btn btn-white"><span class="filter-option pull-left"></span><span class="caret"></span></button><div class="select-menu"></div></div>');
                    $(this).find('option').each(function () {
                        $(this).closest('select').next().find('.select-menu').append('<div class="option" value="' + $(this).val() + '">' + $(this).text() + '</div>');
                    });
                    var appendive = $(this).find('option[value="' + $(this).val() + '"]').text();
                    $(this).next().find('.filter-option').append(appendive);
                    $(this).next().find('.option[value="' + $(this).val() + '"]').addClass('selected');
                    $(this).hide();
                }
            });
        },
        onClickAutoSave: function (e) {
            liAuNu = $(this).closest('.element_field_type_params').find('li.auto_number');
            e.data.instance._instance_auto_number.autoNumberSave(liAuNu);
        },
        onClickAutoSelect: function (e) {
            var select,
                $this = $(this),
                value = $this.attr('value'),
                instAutoNumber = e.data.instance._instance_auto_number;

            $this.closest('li').find('select').val(value);
            select = $this.closest('.module_auto_number_dropdown').find('.field_auto_number_select[select-module-id="' + value + '"]')

            $this.closest('.module_auto_number_dropdown').find('.field_auto_number_select').closest('li').hide();

            var addedCheckbox = $this.closest('.module_auto_number_dropdown').find('li.added_checkbox');
            select.closest('li').show();
            if (select.find('[value="' + select.val() + '"]').attr('type') == 'select' && !addedCheckbox.length) {
                instAutoNumber.addIndependentCheckbox($this);
            }
            liAuNu = $this.closest('.element_field_type_params').find('li.auto_number');
            instAutoNumber.autoNumberSave(liAuNu);
        },
        onClickAutoDropdownToggle: function (e) {
            var $this = $(this),
                instAutoNumber = e.data.instance._instance_auto_number,
                ulDrop = $this.closest('.element_field_type_params');
            var parent = $this.parent(), open = false;

            if (parent.hasClass('open')) {
                open = true;
            }

            ulDrop.find('.static_text_auto_number_dropdown').removeClass('open');
            ulDrop.find('.module_auto_number_dropdown').removeClass('open');

            if (open) {
                parent.removeClass('open');
            } else {
                parent.addClass('open');

                if ($this.parent().is('.module_auto_number_dropdown')) {
                    var option, currentValue,
                        relateModules = ulDrop.closest('.constructor').find('[data-type="relate_module_copy_id"]'),
                        select = parent.find('select.module_auto_number_select'),
                        settingsMenu = parent.find('li:first .settings-menu-select .select-menu'),
                        moduleId = parent.closest('.constructor').data('copy_id'),
                        addedCheckbox = $this.closest('.module_auto_number_dropdown').find('li.added_checkbox'),
                        selectOfRelation = $this.closest('.module_auto_number_dropdown').find('li[style*="list-item"]:last select');

                    if (selectOfRelation.find('[value="' + selectOfRelation.val() + '"]').attr('type') == 'select' && !addedCheckbox.length) {
                        instAutoNumber.addIndependentCheckbox($(this));
                    }

                    var listArray = $('.auto_number').closest('ul').find('[data-type="name_generate"]').val(),
                        moduleSelect = $this.parent().find('li .module_auto_number_select');

                    $.each(JSON.parse(listArray) || [], function (key, value) {
                        if ($.isNumeric(Object.keys(value))) {
                            currentValue = Object.keys(value)[0];
                        }
                    });

                    var $li = moduleSelect.closest('li');

                    currentValue = currentValue || $li.find('.select-menu .option.selected').attr('value') || $li.find('select').val();
                    $li.find('select').val(currentValue);

                    //update base select
                    var $select = $this.parent().find('li .module_auto_number_select')
                    $select.find('option').remove();

                    $.each(e.data.instance._listRelatedSM || $([]), function (key, data) {
                        $select.append('<option value="' + data.id + '">' + data.text + '</option>')
                    });

                    $this.parent().find('li').not(':first').hide();
                    $this.parent().find('select[select-module-id="' + currentValue + '"]').closest('li').show();

                    //update select
                    $this.parent().find('li:first .settings-menu-select').remove();
                    $this.parent().find('li:first select').val(currentValue);
                    e.data.instance.customSelect();

                    if (currentValue == null || !$li.find('select option').length) {
                        moduleSelect.closest('ul').find('li').hide();
                    } else {
                        moduleSelect.closest('ul').find('li').first().show();
                    }

                    $.each(relateModules, function (key, data) {
                        var element,
                            $data = $(data),
                            option = select.find('option[value="' + $data.val() + '"]');

                        if (!option.length) {
                            select.append($data.find('[value="' + $data.val() + '"]').clone().removeAttr('selected'));
                            element = settingsMenu.find('>div').first().clone();
                            element.attr('value', $data.val()).text($data.find('[value="' + $data.val() + '"]').text());
                            settingsMenu.append(element);
                        }
                    });

                    option = select.find('option').not('[value="' + moduleId + '"]');
                    $.each(option, function (key, data) {
                        var element,
                            $data = $(data),
                            option = relateModules.find('[value="' + $data.val() + '"]');

                        if (!option.length) {
                            element = select.next().find('.select-menu .option[value="' + $data.val() + '"]');
                            if (element.is('[value="' + select.val() + '"]')) {
                                var item = select.find('[value="' + moduleId + '"]');
                                select.next().find('.filter-option').text(item.text());
                                select.val(item.val());
                            }
                            element.remove();
                            $data.remove();
                        }
                    });
                }
            }

            ulDrop.off('click');
            ulDrop.on('click', function (e) {
                if (!e.target.closest('.module_auto_number_dropdown') && !e.target.closest('.static_text_auto_number_dropdown')) {
                    parent.removeClass('open');
                }
            });
        },
        onClickAutoAddField: function (e) {
            var instance = e.data.instance;

            instance.isAutoNumberExist();
            instance._instance_auto_number.start($(this));
        },
        onClickLabel: function (e) {
            $(this).closest('.select-color').closest('li').find('input').attr('data-color', $(this).attr('data-color'));
        },
        onClickNavigationModuleDown: function (e) {
            $.post(Global.urls.url_set_list_order, {'direction': 'down', 'copy_id': $(this).closest('tr').data('copy_id')}, function (data) {
                if (data.status == true) {
                    instanceGlobal.contentReload
                        .prepareVariablesToGeneralContent(true)
                        .run();
                } else if (data.status == 'access_error') {
                    Message.show(data.messages, false);
                }
            }, 'json');
        },
        onClickNavigationModuleUp: function (e) {
            $.post(Global.urls.url_set_list_order, {'direction': 'up', 'copy_id': $(this).closest('tr').data('copy_id')}, function (data) {
                if (data.status == true) {
                    instanceGlobal.contentReload
                        .prepareVariablesToGeneralContent(true)
                        .run();
                } else if (data.status == 'access_error') {
                    Message.show(data.messages, false);
                }
            }, 'json');
        },
        onShowTimePicker: function (e) {
            var $objectTarget = $('.bootstrap-timepicker.dropdown-menu');
            var objTarHe = $('.bootstrap-timepicker.dropdown-menu').height();
            var objClick = $(this).offset().top;
            if (objClick + objTarHe + 34 > $(window).height()) {
                $objectTarget.css('margin-top', '-' + $objectTarget.height() - 45 + 'px');
            }
        },
        onClickRemoveDataModule: function (e) {
            var input = $('table input.input_ch:checked');
            var _this = this;
            if ($(this).hasClass('btn-action') && input.length == 0) {
                Message.show([{'type': 'error', 'message': 'It should be noted module(s)'}], true);
                return false;
            }

            var copy_id_list_name = Constructor.getSelectedModuleListName(_this);
            copy_id_list_name = copy_id_list_name.join(', ');

            Message.show([{'type': 'confirm', 'message': Message.translate_local('Delete all data of the module(s): {s}?', {'s': copy_id_list_name})}], false, function (_this_b) {
                if ($(_this_b).hasClass('yes-button')) {
                    modalDialog.hide();
                    var copy_id_list = Constructor.getSelectedModuleList(_this);
                    if (copy_id_list == false) return;
                    Constructor.moduleDeleteData({'copy_id': copy_id_list});
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        },
        onClickBtnSave: function (e) {
            var instance = e.data.instance;

            $(document).find('.constructor_btn-save').attr('disabled', true);

            $.each($('[data-type="field_type_params_select"]'), function (key, data) {
                $.each($(data).find('>li'), function (key) {
                    $(this).find('input').data('sort', key);
                })
            })

            var _data = Constructor.getModuleDataParams();
            setTimeout(function () {
                instance.moduleValidate(_data, Global.urls.url_validate_module, function (result_validate) {
                    if (result_validate.status == true)
                        _data.cleanable_select_ids = instance.listOfValue;
                    instance.moduleSave(_data, function (result_save) {
                        if (result_save.status == true) {
                            deleteTableStatus(_data.copy_id);
                            removeTableOrder(_data.copy_id);

                            var vars = {
                                'content_blocks_different': [{'name': 'main_top_module_menu', 'selector': '.element[data-type="module_menu"]'}],
                                'selector_content_box': '#content_container',
                            }
                            instanceGlobal.contentReload
                                .clear()
                                .setVars(vars)
                                .setUrl(Url.getCurrent())
                                .loadThis();

                            modalDialog.hideAll(function () {
                                history.pushState(null, null, location.pathname + location.search);
                            });
                        }
                    })
                })
            }, 100);
        },
        onChangeConstructorDataType: function () {
            $('#constructor_field_params').html('');
            $.post($('#module_params').data('url_field_params') + '/field_type/' + $(this).val(), function (data) {
                $('#constructor_field_params').append(data);
            }, 'html');
        },
        onClickSettingsSubMenu: function (e) {
            $(this).closest('.settings').css('z-index', '30');
        },
        onClickDropDownToogle: function (e) {
            $('.sub-menu').addClass('hide').closest('.settings').css('z-index', '20');
        },
        onShowElementParamsSelect: function (e) {
            var $submenu = $(this).parent().parent().parent().find('.element_field_type_params_select');
            $submenu.closest('.open').removeClass('open');
            $submenu.toggleClass('hide');
            $submenu.find('.selectpicker').selectpicker({style: 'btn-white'});
            $(this).closest('.settings').css('z-index', '30');

            $('.element_field_type_params_select input[data-type="select_option"]').each(function () {
                var $this = $(this),
                    color = $this.attr('data-color'),
                    $label = $this.closest('li').find('div.select-color .dropdown-menu .label[data-color="' + color + '"]'),
                    $labelClone = $label.clone();

                $this.closest('li').find('div.select-color button .label').parent().html($labelClone);
                $label.closest('ul').find('.selected').removeClass('selected');
                $label.closest('li').addClass('selected');
            });

            e.data.instance.settingOfList($('ul[data-type="field_type_params_select"]').not('.hide'));
        },
        onClickMainSettingsMenu: function (e) {
            e.stopPropagation();
        },
        onChangeReloadListRelateTable: function (e) {
            Constructor.updateFieldListFromRelate(this, $(this).val());
        },
        onChangeSelectElementFieldType: function (e) {
            var selectdir = $(this).closest('.element[data-type="field_type"]'),
                instance = e.data.instance;

            instance
                .loadTypeParams(selectdir.find('.add_element_field_type_params'), true)
                .isAutoNumberExist();
        },
        onClickAddElementFieldTypeParamsForButton: function () {
            Constructor.loadTypeParamsForButton(this, false);
            $('.element_field_type_params_select').addClass('hide').closest('.settings').css('z-index', '20');
        },
        onClickAddElementFieldTypeParams: function (e) {
            var liAuNu, element,
                instance = e.data.instance,
                instAutoNumber = instance._instance_auto_number,
                $this = $(this),
                list = $this.next('ul');

            instance
                .loadTypeParams(this, false)
                .initViewCalculateOfFields($this.next().find('.calculate-field'));

            $('.constructor .field-param+.settings-menu select.form-control').hide();

            if (instAutoNumber) {
                liAuNu = $this.parent().find('li.auto_number');
                instAutoNumber.autoNumberRestore(liAuNu.filter('.default'));
                if (liAuNu.length && $this.next('ul').is(':visible')) {
                    instAutoNumber.hideFields(list.find('li.auto_number'))
                    instAutoNumber.addRelateFields();
                }
                instAutoNumber.autonumber($this.parent().find('li.auto_number'));

                if (!list.find('[data-type="name_generate"]').val().length) {
                    if (!list.find('>li:visible').length) {
                        element = list.find('li.auto_number.default').clone();
                        //element.find('.auto_number_remove').hide();
                        element.removeClass('hidden default').insertAfter(list.find('>li:last'));
                    }
                }
            }
        },
        onClickTodoRemove: function (e) {
            var $this = $(this),
                _data = $this.data('element'),
                instance = e.data.instance,
                _this = this;

            switch (_data) {
                case 'block' :
                    /*
                     if($('.element[data-type="block"]').length <= 2){
                     Message.show([{'type':'error', 'message': 'Deleting the last block is prohibited'}], true);
                     } else {
                     $(this).closest('.element_block').remove();
                     }
                     */

                    // если блок Учасники - удаляем кнопку "Подписаться"
                    var participant = $(e.target).closest('.element[data-type="block"]').find('.element[data-type="block_participant"]');
                    if (participant && typeof (participant) != 'undefined' && participant.is('.element')) {
                        var button_subscription = $('.buttons-block .element[data-type="button"] .element_params[data-type="type_view"][value="button_subscription"]');
                        if (typeof (button_subscription) != 'undefined' && button_subscription) {
                            $(button_subscription).closest('.element[data-type="button"]').remove();
                        }
                    }


                    $(this).closest('.element_block').remove();
                    break;
                case 'panel' :
                    /*
                     var block_panel_contact = $(this).closest('.element[data-type="block"]').find('.element[data-type="block_panel_contact"]').length;
                     if(block_panel_contact == 0){
                     if($(this).closest('.element[data-type="block"]').find('.element[data-type="panel"]').length <= 1){
                     Message.show([{'type':'error', 'message': 'Deleting the last field in the block is prohibited'}], true);
                     } else {
                     $(this).parent().remove();
                     }
                     }  else {
                     $(this).parent().remove();
                     }
                     */
                    $(this).parent().remove();
                    instance.storeRelateModule();
                    break;
                case 'select' :
                    if ($(this).closest('.element[data-type="field_type_params_select"]').children('li').length < 2) {
                        Message.show([{'type': 'error', 'message': 'Removing the last elements in the list of prohibited'}], true);
                        return;
                    }
                    var copy_id = $(_this).closest('.constructor').data('copy_id'),
                        _data = {
                            copy_id: copy_id,
                            field_name: $(_this).closest('.settings, .element[data-type="block_button"]').find('.element[data-type="field_type_params"]').find('.element_params[data-type="name"]').val(),
                            select_id: $(_this).closest('li').find('.element_params[data-type="select_option"]').data('id')
                        },
                        url = Global.urls.url_is_used_select;

                    if (typeof (copy_id) != 'undefined' && copy_id) {
                        $.get(url, _data, function (data) {
                                if (data.status == true) {
                                    $(_this).parent().remove();
                                    e.stopPropagation();
                                } else {
                                    Message.show(data.messages, false);
                                    $(document).off('click', '.confirm-yes-button');
                                    $(document).on('click', '.confirm-yes-button', function () {
                                        instance.listOfValueToRemove($this);
                                        $this.closest('li').remove();
                                    });
                                }
                            },
                            'json');
                    } else {
                        $(_this).parent().remove();
                        e.stopPropagation();
                    }
                    break;
            }
        },
        onClickTodoRemoveContanct: function () {
            var container = $(this).closest('.contact-item');

            container.remove();
        },
        onClickRemoveBlockPanelContact: function () {
            var _this = this;
            $(_this).closest('.element[data-type="block"]').find('.add_element_block_panel_contact').css('display', 'inline');
            $(_this).closest('.element[data-type="block_panel_contact"]').remove();
            $('.participant.example').css('margin-bottom', '-30px');
        },
        onClickAddElementPanelTable: function () {
            var _this = this;
            Element.addPanelTable(function (data) {
                $(_this).parent().parent().find('.inputs-block').append(data).find('li').find('.select').selectpicker({style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                Constructor.setFieldTypeName('block_field_type');
                Constructor.setFieldTypeName('table');
            });
        },
        onClickBtnAddBlocks: function (e) {
            var instance = e.data.instance,
                sSelectBlock = $(this).closest('.panel-body').find('.select option:selected').val();

            modalDialog.hide();

            instance.addContainers();
            if (sSelectBlock) {
                switch (sSelectBlock) {
                    case 'standard':
                        instance.blocks.addStandardBlock();
                        break;
                    case 'participant':
                        instance.blocks.addParticipantBlock();
                        break;
                    case 'attachments':
                        instance.blocks.addAttachmentsBlock();
                        break;
                    case 'activity':
                        instance.blocks.addActivityBlock();
                        break;
                }
            }
        },
        // Изменение количества елементов Тип поля в поле конструктора
        onClickElementCountSelectFields: function () {
            var columns = parseInt($(this).find('.text').text());
            var col_class = '';
            var section = $(this).closest('.inputs-group').find('.columns-section');
            var section_items = $(this).closest('.inputs-group').find('.columns-section > div');
            switch (columns) {
                case 1:
                    col_class = '';
                    break;
                case 2:
                    col_class = 'col-2';
                    break;
                case 3:
                    col_class = 'col-3';
                    break;
                case 4:
                    col_class = 'col-4';
                    break;
                default:
                    col_class = 'col-5';
            }

            if (section_items.length > columns) {
                $(section_items).slice(columns).remove();
            } else if (section_items.length < columns) {
                if ($(this).closest('.element[data-type="panel"]').find('.element[data-type="table"]').length) {
                    Element.addTableColumn(function (data) {
                        for (var i = 0; i < columns - section_items.length; i++) {
                            $(section).append(data).find('.select').selectpicker({style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                        }
                        Constructor.setFieldTypeName('table');
                    });
                } else {
                    Element.addField(function (data) {
                        for (var i = 0; i < columns - section_items.length; i++) {
                            $(section).append(data).find('.select').selectpicker({style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                        }
                        Constructor.setFieldTypeName('block_field_type');
                    });
                }
            }
            Element.blockFieldTypeSetSize(section, col_class);
        },
        onClickAddField: function () {
            var _this = this;
            var id = 1;
            for (i = 1; i < 100; i++) {
                if ($(this).closest('.element[data-type="field_type_params_select"]').find('li input.element_params[data-id="' + i + '"]').length == 0) {
                    id = i;
                    break;
                }
            }
            color_block = 0;
            if ($(_this).closest('.element[data-type="button"]').length > 0) color_block = 1;

            $.post(Global.urls.url_add_element_data_for_select, {'id': id, 'value': '', 'color_block': color_block}, function (html_data) {
                $(_this).parent().before(html_data);
                $(_this).closest('.element[data-type="field_type_params_select"]').find('.selectpicker').selectpicker({style: 'btn-white'});
                Constructor.setFieldTypeName('block_field_type');
                Constructor.setFieldTypeName('table');
            })
        },
        onClickAddElementPanelField: function () {
            var _this = this;
            Element.addPanelField(function (data) {
                $(_this)
                    .parent().parent().find('.inputs-block').append(data)
                    .find('.select').selectpicker({style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});

                Constructor.setFieldTypeName('block_field_type');
                sortableInit();
            });
        },
        onClickAddElementBlockPanelContact: function () {
            var _this = this;
            Element.addBlockPanelContact(function (data) {
                $(_this).css('display', 'none');
                $(_this).closest('.element[data-type="block"]').prepend(data);

            });
            $('.participant.example').css('margin-bottom', '-8px');
        },
        onClickAddElementFieldHidden: function () {
            var _this = this;
            Element.addFieldHidden(function (data) {
                $(_this).closest('.operations').before(data);
                Constructor.setFieldTypeName('field_type_hidden');
                sortableInit();
            });
        },
        //add submodule block
        onClickBtnAddSubModule: function () {
            $(this).attr('disabled', true);
            modalDialog.hide();
            Constructor.addContainers();

            var panel = $(this).closest('.panel');
            var template_rel = panel.find('.selectpicker > li.selected').attr('rel');
            var template = panel.find('select option:eq(' + template_rel + ')').data('template');

            Element.addSumModule($(this).closest('.panel-body').find('.select').val(), template, function (data) {
                $(this).attr('disabled', false);
                $('.element[data-sub_type="submodule"]').append(data);
                $('.constructor .element_params[data-type="relate_type"]').selectpicker({style: 'btn-white'});
                sortableInit();
            });
        },
        // установка статуса - активен/не активен
        onClickSetModuleStatus: function (e) {
            var _this = this;

            AjaxObj
                .createInstance()
                .setUrl(Global.urls.url_module_set_status)
                .setData({
                    'copy_id': $(_this).closest('tr').data('copy_id'),
                    'active': $(_this).val(),
                })
                .setType('POST')
                .setAsync(false)
                .setDataType('json')
                .setCallBackSuccess(function (data) {
                    if (data.status == true) {
                        var json,
                            $this = $(_this),
                            vars = {
                                'content_blocks_different': [{'name': 'main_top_module_menu', 'selector': '.element[data-type="module_menu"]'}],
                                'selector_content_box': '#content_container',
                            },
                            reload = instanceGlobal.contentReload.clear();

                        reload
                            .getPageUrl('constructor', function () {
                                reload
                                    .setVars(vars)
                                    .loadThis();
                            });

                        json = {
                            'copy_id': $this.closest('tr').data('copy_id'),
                            'value': $this.val()
                        };

                        QuickViewPanel.toggleToModule(json);
                        StartupGuide.reLoadParams();
                    } else if (data.status == 'access_error') {
                        Message.show(data.messages, false);
                    } else if (data.status == false)
                        Message.show(data.messages, false);
                })
                .setCallBackError(function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                })
                .send();
        },
        onClickBtnModuleDelete: function () {
            var input = $('table input.input_ch:checked');
            var _this = this;
            if ($(this).hasClass('btn-action') && input.length == 0) {
                Message.show([{'type': 'error', 'message': 'It should be noted module(s)'}], true);
                return false;
            }

            Message.show([{'type': 'confirm', 'message': Message.translate_local('Delete selected module(s)') + '?'}], false, function (_this_b) {
                if ($(_this_b).hasClass('yes-button')) {
                    modalDialog.hide();

                    var copy_id_list = Constructor.getSelectedModuleList(_this);

                    if (copy_id_list == false) return;

                    Constructor.moduleValidate({'copy_id': copy_id_list}, Global.urls.url_validate_before_belete_module, function (result_validate) {
                        if (result_validate.status != true) return;

                        var copy_id_list = Constructor.getSelectedModuleList(_this);
                        if (copy_id_list == false) return;
                        Constructor.moduleDelete({'copy_id': copy_id_list});
                    })
                }
            }, Message.TYPE_DIALOG_CONFIRM);
        },
        onClickBtnShowSubmoduleList: function () {
            $.ajax({
                url: Global.urls.url_edit_sub_module_list,
                dataType: "html",
                data: {'exception_list': Constructor.getSubModuleException()},
                type: "POST",
                success: function (data) {
                    if (!data) {
                        Message.show([{'type': 'error', 'message': 'None installed copies sabmodules for connection'}], true);
                        return;
                    }
                    modalDialog.show(data);
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                }
            });
        },
        onClickBtnSetSettings: function (e) {
            //constructor_btn-set-settings
            var data = {},
                instance = e.data.instance;

            $(this).closest('.panel-body').find('.element[data-type="module_params"]').each(function (i, ul) {
                data[$(ul).data('name')] = $(ul).val();
            });

            var module_params_block = $('.constructor .element[data-type="module_params_block"]');

            $.each(data, function (field_name, value) {
                module_params_block.find('.element[data-type="module_params"][data-name="' + field_name + '"]').val(value);
                if (field_name == 'show_blocks')
                    instance.checkFieldsOnDisplayBlockType(value);
            });

            modalDialog.hide();
        },
        onClickActionModalModuleSettings: function () {
            var params = {};
            $('.constructor .element[data-type="module_params_block"] .element[data-type="module_params"]').each(function (i, ul) {
                params[$(ul).data('name')] = $(ul).val();
            });

            $.ajax({
                url: Global.urls.url_settings, dataType: "html", type: "POST",
                data: {'params': params, 'copy_id': $(this).closest('.constructor').data('copy_id')},
                success: function (data) {
                    if (!data) {
                        Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                        return;
                    }
                    modalDialog.show(data);
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                }
            });
        },
        onClickShowBlockList: function () {
            $.ajax({
                url: Global.urls.url_edit_block_module_list,
                dataType: "html",
                type: "POST",
                success: function (data) {
                    if (!data) {
                        Message.show([{'type': 'error', 'message': 'None installed copies blocks for connection'}], true);
                        return;
                    }
                    modalDialog
                        .setParentClass('modal-constructor')
                        .show(data);
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                }
            });
        },
        onClickCopyModule: function () {
            var input = $('table input.input_ch:checked'),
                $this = $(this);

            instanceGlobal.preloaderShow($this);
            if ($this.hasClass('btn-action') && input.length == 0) {
                Message.show([{'type': 'error', 'message': 'It should be noted module(s)'}], true);
                return false;
            }

            //if(!confirm(Message.translate_local('Create copies of the chosen module(s)') + '?')) return;

            var _data = [];
            if ($this.hasClass('btn-action')) {
                input.closest('tr').each(function (i, ul) {
                    var copy_id = $(ul).data('copy_id');
                    if (copy_id) _data.push(copy_id);
                });
            } else {
                var copy_id = $this.closest('.constructor').data('copy_id');
                if (copy_id) {
                    _data.push(copy_id);
                } else {
                    Message.show([{'type': 'error', 'message': 'You can not copy any unsaved data'}], true);
                    return;
                }
            }

            if ($.isEmptyObject(_data)) return;

            $.ajax({
                url: Global.urls.url_copy_module,
                data: {'copy_id': _data},
                type: 'POST', async: false, dataType: "json",
                success: function (data) {
                    if (data.status == true) {

                        var vars = {
                            'content_blocks_different': [{'name': 'main_top_module_menu', 'selector': '.element[data-type="module_menu"]'}],
                            'selector_content_box': '#content_container',
                        }
                        instanceGlobal.contentReload
                            .clear()
                            .setUrl(Url.getCurrent())
                            .setVars(vars)
                            .loadThis();

                        modalDialog.hideAll();
                    } else if (data.status == 'access_error') {
                        Message.show(data.messages, false);
                    } else {
                        Message.show(data.messages, false);
                    }
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                }
            });
        },
        onClickCreateModule: function () {
            Preloader.modalShow();
            var _this = this;
            $.ajax({
                url: $('#global_params').data('url_' + $(_this).data('controller')),
                data: {extension_id: constructor_create_module_id},
                dataType: "json",
                type: "GET",
                success: function (data) {
                    if (data.status == 'access_error') {
                        Message.show(data.messages, false);
                    } else if (data.status == true) {
                        modalDialog.show(data.data, true);
                        sortableInit();
                    }
                    Preloader.modalHide();
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                    Preloader.modalHide();
                },
            });
        },
        onClickOpenModule: function (e) {
            Preloader.modalShow();
            var _this = this,
                $this = $(_this),
                instance = e.data.instance,
                data = {
                    wrapper: true,
                    copy_id: $this.parent().parent().data('copy_id')
                };

            $.ajax({
                url: $('#global_params').data('url_' + $this.data('controller')),
                data: data,
                dataType: "html",
                type: "GET",
                success: function (data) {
                    modalDialog
                        .createInstance()
                        .show(data, true);

                    sortableInit();
                    Preloader.modalHide();

                    var time = setTimeout(function () {
                        clearTimeout(time);

                        history.pushState(null, null, '#' + $this.text().trim());
                        AjaxContainers.cash.snapshot([data]);
                    }, 50)

                    if ($('.constructor select option[value="auto_number"][selected]').length) {
                        instance._instance_auto_number = new AutoNumbering();
                    }
                    instance.storeRelateModule();
                    if (instance._instance_auto_number) {
                        instance._instance_auto_number.reLoadSmField();
                    }
                },
                error: function () {
                    Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                    Preloader.modalHide();
                },
            });
        },
        onClickDeleteBtn: function () {
            var block = $(this).closest('.buttons-block'),
                $container = $(this).closest('.element[data-type="button"]'),
                type_view = $container.find('.element_params[data-type="type_view"]').val();

            var block_participant = $('.constructor .element[data-type="block_participant"]');
            if (typeof (block_participant) != 'undefined' && block_participant.length > 0 && type_view == 'button_subscription') {
                Message.show([{'type': 'error', 'message': 'Removing prohibited. There is a block "Participants"'}], true);
                return;
            }

            $(block).find('.element[data-type="block_button_box"]').removeClass('hidden');
            $(block).find('.constructor_btn-add-block-button[data-type="' + type_view + '"]').removeClass('hidden');
            $container.remove();
        },
        onClickAddBlockButton: function () {
            Element.addButton(this);
        }
    },
    settingOfList: function ($ul) {
        var event;

        $ul.sortable({
            items: '>li:not(.btn-element)',
            placeholder: "ui-state-highlight",
            delay: 150
        });


        event = ['click', '.element_field_type_params_select [data-type="select_option"]'];
        $(document).off(event[0], event[1]).on(event[0], event[1], function () {
            $(this).focus();
        });
    },
    listOfValueToRemove: function ($this) {
        var object = $this.closest('[data-type="block_field_type"]').find('[data-type="name"]'),
            data = Object.create(null);

        data.field_name = object.length ? object.val() : $this.closest('[data-type="button"]').find('[data-type="name"]').val();
        data.select_id = $this.closest('li').index();

        this.listOfValue.push(data);
    },
    initViewCalculateOfFields: function ($this) {

        var typingTimer, inner,
            object = this,
            nameOfFields = $this.find('[data-type="name-of-fields"]'),
            type = $('[data-type="block_panel"]:first').find('[data-type="field_type"]>select'),
            $inputFormula = $('.calculate-field input');

        inner = {
            error: 'color-red',
            verify: 'color-green',
            clearColor: function ($data) {
                $data.removeClass(this.verify).removeClass(this.error);
            }
        };

        if ($inputFormula.is('.' + inner.error)) {
            $inputFormula.removeClass(inner.error);
            $inputFormula.val($inputFormula.data('old-value'));
        }

        object.listNumericOfFields = {};

        nameOfFields.find('div').remove(); // Output list of numberic fields
        $.each(type, function () {
            var type, text, $this = $(this);

            if ($this.val() == 'numeric') {
                type = $this.closest('[data-type="block_field_type"]').find('[data-type="name"]').val(),
                    text = $this.closest('[data-type="panel"]').find('[data-type="label"]').val();
                nameOfFields.append('<div><b>' + type + '</b>: ' + text + '</div>');
                object.listNumericOfFields[type] = text;
            }
        });

        $inputFormula.on('keyup', function () {
            var _data,
                $this = $(this),
                value = $this.val();

            inner.clearColor($this);

            if (value.indexOf('(') > value.indexOf(')')) {
                $this.addClass(inner.error);
            }
            if (value[0] != '=') {
                $this.val('=' + value);
            }

            clearTimeout(typingTimer);
            typingTimer = setTimeout(function () {
                clearTimeout(typingTimer);
                _data = {
                    copy_id: $this.closest('.constructor').data('copy_id'),
                    body: $this.val()
                };
                $.ajax({
                    url: Global.urlOfMethods.constructor_validate_calculated,
                    data: _data,
                    type: 'GET',
                    async: false,
                    dataType: "html",
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status) {
                            $this.addClass(inner.verify);
                        } else {
                            $this.addClass(inner.error);
                        }
                    },
                    error: function () {
                        $this.addClass(inner.error);
                    }
                });
            }, 800);
        });
    },
    checkFieldsOnDisplayBlockType: function (value) {
        var display_block = false;

        $('.constructor').find('.element').each(function (i, ul) {
            if ($(ul).data('type') == 'block') {
                $(ul).children('.element').each(function (i, ul) {
                    if ($(ul).data('type') == 'block_panel') {
                        //panel
                        $(ul).children('.inputs-block').children('.element[data-type="panel"]').each(function (i, ul) {
                            var _this = ul;
                            $(ul).children('.element[data-type="block_field_type"]').each(function (i, ul) {

                                //block_field_type

                                // field_type
                                $(ul).children('.element[data-type="field_type"]').each(function (i, ul) {
                                    var field_type_pr = {};

                                    field_type_pr = ElementParser.getParams(ul, field_type_pr);

                                    if (field_type_pr['type'] == 'display_block') {

                                        if (value == '1')
                                            $(_this).remove(); //показываем все блоки, поэтому удаляем необходимый тип поля

                                        display_block = true;

                                    }

                                });

                            });
                        });
                    }
                });
            }
        });

        if (value == '0') {
            if (!display_block) {
                //показыаем один блок и подобный тип поля не найден, добавляем новое поле
                var _this = $('.constructor').find('.inputs-block:first');
                Element.addPanelBlockField(function (data) {
                    $(_this)
                        .append(data)
                        .find('.select').selectpicker({style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
                    Constructor.setFieldTypeName('block_field_type');
                    sortableInit();
                });
            }
        }
    },
    aliasMoveTo: function () {
        this.$.find('#req>li:first').before(this.$.find('[data-is-alias]'));
        return this;
    },
    setFieldNameList: function (list) {
        Constructor.field_name_list = list;
        return this;
    },

    inFieldNameList: function (field_name) {
        if (Constructor.field_name_list == false || $.isEmptyObject(Constructor.field_name_list)) {
            return false;
        }
        var i = $.inArray(field_name, Constructor.field_name_list);

        return (i == -1 ? false : true);
    },

    addContainers: function () {
        var element,
            $constructor = $('.constructor'),
            elements = ['standart', 'submodule', 'activity'],
            lastElement = $('.element[data-type="block"]').last(),
            empty = '<div class="element" data-sub_type="000"></div>',
            container = {
                standart: $constructor.find('.element[data-sub_type="' + elements[0] + '"]'),
                subModules: $constructor.find('.element[data-sub_type="' + elements[1] + '"]'),
                activities: $constructor.find('.element[data-sub_type="' + elements[2] + '"]')
            };

        if (!container.standart.length) {
            element = empty.replace('000', elements[0]);
            if (container.subModules.length) {
                container.subModules.before(element);
            } else {
                if (container.activities.length) {
                    container.activities.first().before(element);
                } else lastElement.after(element)
            }
        }
        if (!$(container.subModules.selector).length) {
            element = empty.replace('000', elements[1]);
            if ($(container.standart.selector).length) {
                $(container.standart.selector).after(element);
            } else {
                if ($(container.activities.selector).length) {
                    $(container.activities.selector).before(element);
                } else lastElement.after(element)
            }
        }
        if (!$(container.activities.selector).length) {
            element = empty.replace('000', elements[2]);
            if ($(container.subModules.selector).length) {
                $(container.subModules.selector).after(element);
            } else {
                if ($(container.standart.selector).length) {
                    $(container.standart.selector).after(element);
                } else lastElement.after(element)
            }
        }
    },
    // возвращает список copy_id уже подключенных модулей с формы конструктора
    getSubModuleExceptionCopyID: function (exception_list) {
        if (!exception_list) return;
        var result = [];
        $.each(exception_list, function (key, value) {
            result.push(value.copy_id);
        })
        return result;
    },
    getFromUrlModuleFields: function ($select, callback) {

        $.ajax({
            url: Global.urls.url_module_fields,
            data: {'copy_id': $select.val(), 'selected_field': ''},
            type: 'GET', async: false, dataType: "html",
            success: function (data) {
                callback(data);
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            }
        });
    },
    // возвращает список уже подключенных модулей с формы конструктора
    getSubModuleException: function () {
        var exception_copy_id = [];
        if ($('.constructor').data('copy_id'))
            exception_copy_id.push({'copy_id': $('.constructor').data('copy_id'), 'template': '0'});
        $('.element[data-type="sub_module"]').each(function (i, ul) {
            exception_copy_id.push({'copy_id': $(ul).data('relate_module_copy_id'), 'template': $(ul).data('relate_module_template')})
        });
        $('.element_params[data-type="relate_module_copy_id"]').each(function (i, ul) {
            exception_copy_id.push({'copy_id': parseInt($(ul).val()), 'template': '0'});
        });
        return exception_copy_id;
    },

    // поиск елементов для select
    getDefaultParamSelect: function (element) {
        if (
            $(element).find('.element_params[data-type_element="select"]').length == 0 ||
            $(element).find('.element[data-type="field_type_params_select"]').length == 0
        ) {
            Constructor.customSelectRemove();
            Constructor.customSelect();
            return this;
        }


        var default_value = $(element).find('.element_params[data-type_element="select"]').val();
        var options = '<option value=""></option>';

        $(element).find('.element[data-type="field_type_params_select"] li input').each(function (i, ul) {
            options += '<option value="' + $(ul).data('id') + '">' + $(ul).val() + '</option>';
        });
        element_df = $(element).find('.element_params[data-type_element="select"]');
        element_df.empty().html(options).val(default_value);

        Constructor.customSelectRemove();
        Constructor.customSelect();

        return this;
    },

    // запрашивает список модулей и фильтрует уже подключенные
    getRelateModuleNames: function (relate_copy_id, callback, _data) {
        if ($('.element_params[data-type="relate_module_copy_id"]').length > 0) {
            $.ajax({
                url: Global.urls.url_module_names,
                data: _data,
                type: 'POST', async: false, dataType: "json",

                success: function (data) {
                    var exception_list = Constructor.getSubModuleExceptionCopyID(Constructor.getSubModuleException());
                    if (data.status = true) {
                        var result = [];
                        $.each(data.data, function (key, value) {
                            var in_array = $.inArray(parseInt(value.id), exception_list);
                            if (in_array == -1 || (in_array >= 0 && parseInt(relate_copy_id) == parseInt(value.id)))
                                result.push(value);
                        });
                        callback(result);
                    }
                },
                error: function () {
                },
            });
        }
    },

    updateRelateModules: function (_this) {
        var params = $(_this).closest('.crm-dropdown');
        if ($(params).hasClass('open')) {
            var relate_module = $(params).find('.element_params[data-type="relate_module_copy_id"]');
            var relate_field = $(params).find('.element_params[data-type="relate_field"]');

            if (relate_module.length > 0) {
                var _data = {};

                var related_string = $(params).find('.relate_string');
                if (related_string.length) {

                    _data['copy_id'] = $(_this).closest('.constructor').data('copy_id');
                    _data['relate_string'] = true;
                    _data['relate_module_copy_ids'] = [];
                    _data['is_primary'] = $(params).find('.element_params[data-type="is_primary"]').val();

                    var configure = function (id) {
                        if (id) {
                            if ($.inArray(parseInt(id), _data.relate_module_copy_ids) == -1) {
                                _data.relate_module_copy_ids.push(parseInt(id));
                            }
                        }
                    }

                    configure(related_string.data('copy_id'));
                    configure(related_string.closest('.element[data-relate_string]').data('relate_string'));

                }

                Constructor.getRelateModuleNames(relate_module.val(), function (data) {
                    var default_value = relate_module.val();
                    relate_module.empty();
                    if (data) {
                        $.each(data, function (key, value) {
                            relate_module.append('<option value="' + value.id + '" ' + (parseInt(value.id) == parseInt(default_value) ? 'selected="selected"' : '') + '>' + value.title + '</option>');
                        });
                    }
                    Constructor.updateFieldListFromRelate(relate_module, relate_module.val(), relate_field.val());
                }, _data);
            }
        }
    },


    // загрузка формы параметров для определенного типа
    loadTypeParams: function (_element, remove_params) {
        var $this = $(_element),
            _this = this,
            $fieldType = $this.closest('.element[data-type="field_type"]');

        var field_type = $fieldType.find('select.element_field_type').val(),
            field_name = $fieldType.find('.element_params[data-type="name"]').val(),
            is_primary = $fieldType.find('.element_params[data-type="is_primary"]').val(),
            c_types_list_index = $fieldType.find('.element_params[data-type="c_types_list_index"]').val(),
            copy_id = $('.constructor').data('copy_id');

        if (field_type == 'relate_this') return;

        if (remove_params == true) {
            $fieldType.find('.element_field_type_params').remove();
            $fieldType.find('.element_field_type_params_select').remove();
        }

        Constructor.updateRelateModules(_element);
        if ($fieldType.find('.settings-menu').length > 0) {
            Constructor.getDefaultParamSelect($fieldType);
            return this;
        }
        $.ajax({
            url: Global.urls.url_field_params,
            data: {
                'field_attr': {
                    'field_type': field_type,
                    'field_name': field_name,
                    'is_primary': is_primary,
                    'c_types_list_index': c_types_list_index,
                },
                'exception_list': Constructor.getSubModuleExceptionCopyID(Constructor.getSubModuleException()),
                'copy_id': copy_id,
            },
            dataType: "html", type: "POST",
            success: function (data) {
                var value;

                $fieldType.find('.add_element_field_type_params').after(data);
                Constructor.setDisplayBtnTypeParams($fieldType);

                Constructor.setFieldTypeName('block_field_type');
                Constructor.setFieldTypeName('table');
                initElements($fieldType);
                Constructor.getDefaultParamSelect($fieldType).updateRelateModules(_this);

                value = $fieldType.find('select').val();

                // sm field
                if ($('.element_params[data-type="relate_module_copy_id"]').length) {
                    $('.auto_number.first_element .module_auto_number_dropdown .dropdown-menu li').not(':first').remove(); // clear

                    AutoNumbering.addRelateFields();

                }

                _this.storeRelateModule();

                switch (value) {
                    case 'relate': {
                        if (_this._instance_auto_number) {
                            _this._instance_auto_number.changeSMModule();
                        }
                        break;
                    }
                    case Constructor.TYPE_FIELD_AUTO_NUMBERING: {
                        _this._instance_auto_number = new AutoNumbering();
                        _this._instance_auto_number.start($('.dropdown_params_auto_number .add_autonumber_field'));
                        break;
                    }
                    default: {
                        if (!$('.constructor .auto_number').length) {
                            // clear Autonumbering
                            _this._instance_auto_number && _this._instance_auto_number.destroy();
                            _this._instance_auto_number = null;
                        }

                        break;
                    }
                }
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            },
        }).done(function () {
            $('.constructor .field-param+.settings-menu select.form-control').hide();
        });

        return this;
    },

    // загрузка параметров елемента-кнопки
    loadTypeParamsForButton: function (_this, remove_params) {

        var field_type = $(_this).closest('.element[data-type="button"]').find('select.element_field_type').val();
        var field_name = $(_this).closest('.element[data-type="button"]').find('.element_params[data-type="name"]').val();
        var is_primary = $(_this).closest('.element[data-type="button"]').find('.element_params[data-type="is_primary"]').val();
        var c_types_list_index = $(_this).closest('.element[data-type="button"]').find('.element_params[data-type="c_types_list_index"]').val();

        if (remove_params == true) {
            $(_this).closest('.element[data-type="button"]').find('.element_field_type_params').remove();
            $(_this).closest('.element[data-type="button"]').find('.element_field_type_params_select').remove();
        }

        Constructor.updateRelateModules(_this);

        if ($(_this).closest('.element[data-type="button"]').find('.settings-menu').length > 0) {
            Constructor.getDefaultParamSelect($(_this).closest('.element[data-type="button"]'));
            return;
        }

        $.ajax({
            url: Global.urls.url_field_params,
            data: {
                'field_attr': {
                    'field_type': field_type,
                    'field_name': field_name,
                    'is_primary': is_primary,
                    'c_types_list_index': c_types_list_index,
                },
                'exception_list': Constructor.getSubModuleExceptionCopyID(Constructor.getSubModuleException()),
            },
            dataType: "html", type: "POST",
            success: function (data) {

                $(_this).closest('.element[data-type="button"]').find('.add_element_field_type_params').after(data);
                Constructor.setDisplayBtnTypeParams($(_this).closest('.element[data-type="button"]'));

                Constructor.setFieldTypeName('block_field_type');
                Constructor.setFieldTypeName('table');
                initElements($(_this).closest('.element[data-type="button"]'));
                Constructor.getDefaultParamSelect($(_this).closest('.element[data-type="button"]'));

            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            },
        });
    },

    // обновления списка полей для типа "связь с другим модулем"
    updateFieldListFromRelate: function (_this, _copy_id, _selected_field) {
        var relate_field = $(_this).closest('.element[data-type="field_type_params"]').find('.element_params[data-type="relate_field"]');
        relate_field.empty();

        $.ajax({
            url: Global.urls.url_module_fields,
            data: {'copy_id': _copy_id, 'selected_field': _selected_field},
            type: 'GET', async: false, dataType: "html",
            success: function (data) {
                relate_field.after(data).remove();
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            }
        });
    },

    getFieldName: function (element) {
        var names = [];
        var field_prefix;
        var field_name;
        switch (element) {
            case 'block_field_type':
                field_prefix = 'field';
                break;
            case 'field_type_hidden':
                field_prefix = 'ehc_field';
                break;
            case 'table':
                field_prefix = 'table_field';
                break;
        }

        $('.constructor .element_params[data-type="name"]').each(function (i, ul) {
            names.push($(ul).val());
        });

        for (var i = 1; i < 255; i++) {
            field_name = field_prefix + i;

            if (Constructor.inFieldNameList(field_name)) continue;
            if ($.inArray(field_name, names) == -1) {
                return field_name;
            }
        }
    },

    setFieldTypeName: function (element) {
        if (element == 'block_field_type' || element == 'field_type_hidden') {
            $('.element[data-type="' + element + '"]').each(function (i, ul) {
                $(ul).find('.element_params[data-type="name"]').each(function (i, ul) {
                    if (!$(ul).val()) $(ul).val(Constructor.getFieldName(element));
                });
            });
        } else if (element == 'table') {
            $('.element[data-type="' + element + '"]').each(function (i, ul) {
                $(ul).find('.element_params[data-type="name"]').each(function (i2, ul2) {
                    if (!$(ul2).val()) $(ul2).val(Constructor.getFieldName(element));
                })

            });
        }
        return this;
    },

    // отображаем / скрываем кнопку выбора параметров
    setDisplayBtnTypeParams: function (_this) {
        var param = $(_this).find('.element_params[data-type="c_load_params_btn_display"]').val(),
            $btnParams = $(_this).find('.add_element_field_type_params');

        $btnParams.css('display', param == '1' ? 'inline' : 'none');
        return this;
    },

    customSelect: function () {
        $('.constructor .field-param+.settings-menu select.form-control').each(function () {
            if ($(this).next().hasClass('settings-menu-select')) {
            } else {
                $(this).after('<div class="settings-menu-select"><button class="btn btn-white"><span class="filter-option pull-left"></span><span class="caret"></span></button><div class="select-menu"></div></div>');
                $(this).find('option').each(function () {
                    $(this).closest('select').next().find('.select-menu').append('<div class="option" value="' + $(this).val() + '">' + $(this).text() + '</div>');
                });
                var appendive = $(this).find('option[value="' + $(this).val() + '"]').text();
                $(this).next().find('.filter-option').append(appendive);
                $(this).next().find('.option[value="' + $(this).val() + '"]').addClass('selected');
                $(this).hide();
            }
        });
    },

    customSelectRemove: function () {
        $('.constructor .field-param+.settings-menu select.form-control').each(function () {
            if ($(this).next().hasClass('settings-menu-select')) {
                $(this).next().remove();
            }
        });
    },


    getModuleDataParams: function () {
        var _data = {
            'copy_id': $('.constructor').data('copy_id'),
            'extension_id': $('.constructor').data('extension_id'),
            'title': $('.element[data-type="module_title"]').val(),
            'schema': ElementParser.init(),
            'confirm_code_action': Message.last_code_action,
        };
        return _data;
    },


    // element = list_view | dialog
    getSelectedModuleList: function (_this) {
        var input = $('table input.input_ch:checked');
        if ($(_this).hasClass('btn-action') && input.length == 0) {
            Message.show([{'type': 'error', 'message': 'It should be noted module(s)'}], true);
            return false;
        }

        var list = [];

        if ($(_this).hasClass('btn-action')) {
            input.closest('tr').each(function (i, ul) {
                list.push($(ul).data('copy_id'));
            });
        } else {
            var copy_id = $('.constructor').data('copy_id');
            if (copy_id) {
                list.push(copy_id);
            } else {
                Message.show([{'type': 'error', 'message': 'You can not delete any unsaved data'}], true);
            }
        }

        return list;
    },


    // element = list_view | dialog
    getSelectedModuleListName: function (_this) {
        var input = $('table input.input_ch:checked');
        if ($(_this).hasClass('btn-action') && input.length == 0) {
            Message.show([{'type': 'error', 'message': 'It should be noted module(s)'}], true);
            return false;
        }

        var list = [];

        if ($(_this).hasClass('btn-action')) {
            input.closest('tr').find('td a.modal_dialog[data-controller="edit_module"]').each(function (i, ul) {
                list.push($.trim($(ul).text()));
            });
        } else {
            list.push($('.constructor').data('copy_id'));
        }

        return list;
    },


    // Проверка модуля
    moduleValidate: function (_data, _url, callback) {
        $.ajax({
            url: _url,
            data: _data,
            type: 'POST', async: false, dataType: "json",
            success: function (data) {
                if (data.status == 'access_error') {
                    $('.constructor_btn-save').removeAttr('disabled');
                    Message.show(data.messages, false);
                } else if (data.status == false) {
                    $('.constructor_btn-save').removeAttr('disabled');
                    Message.show(data.messages, false);
                } else {
                    callback(data)
                }
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
                $('.constructor_btn-save.disabled').removeClass('disabled');
            },
            complete: function () {
                $('.constructor_btn-save.disabled').removeClass('disabled');
            }
        });
    },


    // сохранение и инсталяция
    moduleSave: function (_data, callback) {
        $.ajax({
            url: Global.urls.url_save_module,
            data: _data,
            type: 'POST', async: false, dataType: "json",
            success: function (data) {
                if (data.status == 'access_error') {
                    Message.show(data.messages, false);
                } else if (data.status == false) {
                    Message.show(data.messages, false);
                } else {
                    callback(data)
                }
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            }
        });
    },


    // удаление
    moduleDelete: function (_data) {
        $.ajax({
            url: Global.urls.url_delete_module,
            data: _data,
            type: 'POST', async: false, dataType: "json",
            success: function (data) {
                if (data.status == false) {
                    Message.show(data.messages, false, function () {
                        deleteTableStatus(_data.copy_id);
                        instanceGlobal.contentReload
                            .prepareVariablesToGeneralContent(true)
                            .run();

                        modalDialog.hideAll();
                    });
                } else if (data.status == 'access_error') {
                    Message.show(data.messages, false);
                } else {
                    deleteTableStatus(_data.copy_id);

                    var vars = {
                        'content_blocks_different': [{'name': 'main_top_module_menu', 'selector': '.element[data-type="module_menu"]'}],
                        'selector_content_box': '#content_container',
                    }
                    instanceGlobal.contentReload
                        .clear()
                        .setUrl(Url.getCurrent())
                        .setVars(vars)
                        .loadThis();

                    modalDialog.hideAll();
                }
                removeTableOrder(_data.copy_id);
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            }
        });
    },


    // удаление всех данных
    moduleDeleteData: function (_data) {
        $.ajax({
            url: Global.urls.url_delete_module_data,
            data: _data,
            type: 'POST', async: false, dataType: "json",
            success: function (data) {
                if (data.status == false) {
                    instanceGlobal.contentReload
                        .prepareVariablesToGeneralContent(true)
                        .run();
                    Message.show(data.messages, false);
                } else if (data.status == 'access_error') {
                    Message.show(data.messages, false);
                } else {
                    instanceGlobal.contentReload
                        .prepareVariablesToGeneralContent(true)
                        .run();
                }
            },
            error: function () {
                Message.show([{'type': 'error', 'message': Global.urls.url_ajax_error}], true);
            }
        });
    },

    allMethod: function () {
        var event,
            _this = this;

        this.blocks = {
            addContext: function (data, param) {
                var blockActivity = $('.element[data-sub_type="activity"]').last(),
                    standart = $('[data-sub_type="standart"]');

                switch (param) {
                    case 'activity': {
                        blockActivity.append(data);
                        break;
                    }
                    case 'standart': {
                        $(data).attr('data-sub_type', 'standart');
                        standart.append(data);
                        break;
                    }
                    case 'attachment': {
                        var content = blockActivity.find('>div.element');

                        if (content.length) {
                            content.before(data)
                        } else blockActivity.append(data);
                        break;
                    }
                    default:
                        break;
                }
            },
            //add standard block
            addStandardBlock: function () {
                Element.addBlockPanel(function (data) {
                    _this.blocks.addContext(data, 'standart');
                    _this
                        .setFieldTypeName('block_field_type')
                        .setFieldTypeName('table');
                    sortableInit();
                });
            },
            //add participant (учасники)
            addParticipantBlock: function () {
                // проверка наличие блока "Учасники"
                var block_participant = $('.constructor .element[data-type="block_participant"]');
                if (typeof (block_participant) != 'undefined' && block_participant.length > 0) {
                    Message.show([{
                        'type': 'error',
                        'message': 'Block "{s}" has already been added',
                        'params': {
                            's': block_participant.closest('.element[data-type="block"]').find('.editable-field').text()
                        }
                    }], true);
                    return;
                }

                // проверка наличие кнопки "Ответственный"
                var button_responsible = $('.buttons-block .element[data-type="button"] .element_params[data-type="type_view"][value="button_responsible"]');
                if (typeof (button_responsible) != 'undefined' && button_responsible.length > 0) {
                    $('.buttons-block .btn-r').closest('span').remove();
                    $('.buttons-block .constructor_btn-add-block-button[data-type="button_responsible"]').removeClass('hidden');
                    $('.buttons-block > .btn-group.hidden').removeClass('hidden');
                    //Message.show([{'type':'error', 'message': 'You must remove the button "Responsible"'}], true);
                    //return;
                }

                Element.addBlockParticipant(function (data) {
                    Element.addButton($('.buttons-block .constructor_btn-add-block-button[data-type="button_subscription"]'), function () {
                        $('.buttons-block').closest('.element[data-type="block"]').after(data);
                    });
                });
            },
            //add attachments block
            addAttachmentsBlock: function () {
                var block_attachments = $('.constructor .element[data-type="block_attachments"]');
                if (typeof (block_attachments) != 'undefined' && block_attachments.length > 0) {
                    Message.show([{
                        'type': 'error',
                        'message': 'Block "{s}" has already been added',
                        'params': {
                            's': block_attachments.closest('.element[data-type="block"]').find('.editable-field').text()
                        }
                    }], true);
                    return;
                }

                Element.addBlockAttachments(function (data) {
                    _this.blocks.addContext(data, 'attachment');
                });
            },
            //add activity block
            addActivityBlock: function () {
                // проверка наличие блока "Активность"
                var block_activity = $('.constructor .element[data-type="block_activity"]');
                if (typeof (block_activity) != 'undefined' && block_activity.length > 0) {
                    Message.show([{
                        'type': 'error',
                        'message': 'Block "{s}" has already been added',
                        'params': {
                            's': block_activity.closest('.element[data-type="block"]').find('.editable-field').text()
                        }
                    }], true);
                    return;
                }

                Element.addBlockActivity(function (data) {
                    _this.blocks.addContext(data, 'activity');
                });
            }
        }

        //submodule select changes
        /*
         $(document).on('click', '.element[data-type="constructor_add_sub_module"] .selectpicker li.selected', function(){

         //$(this).closest('.select').data('template', $(this).data('template'));
         });
         */

        /*
         $(document).on('click', '.add_element_panel_field_title', function(){
         var _this = this
         Element.addPanelFieldTitle(function(data){
         $(_this).parent().parent().find('.inputs-block').append(data).find('.select').selectpicker({ style: 'btn-white', noneSelectedText: Message.translate_local('None selected')});
         Constructor.setFieldTypeName('block_field_type');
         sortableInit();
         });
         });
         */

        //last stroke select bug
        $('div[data-page_name="constructor"] .table tr:last-child .bootstrap-select').on('click', function () {
            $(this).addClass('dropup');
        });

        $(document).click(function (event) {
            if ($(event.target).closest('#myModal').length) return;
            if ($('.modal-backdrop.in').length != 0) {
                $('body').addClass('modal-open');
            }
            event.stopPropagation();
        });

        $('.modal-content').off('click').on('click', function () {
            $('.settings.crm-dropdown').css('z-index', '10');
        });

        event = '.element[data-type="constructor_add_sub_module"] .bootstrap-select';
        $('body').off('click', event).on('click', event, function () {
            if ($(this).find('ul.dropdown-menu li').length > 10) {
                $(this).find('div.dropdown-menu').addClass('padded');
                $(this).find('ul.dropdown-menu').height(240);
                niceScrollCreate($('ul.dropdown-menu'));
            }
        });

        $('.constructor .selectpicker').selectpicker({style: 'btn-white'});

        $('.select').selectpicker({
            style: 'btn-white',
            noneSelectedText: Message.translate_local('None selected'),
        });

        this.setFieldTypeName('block_field_type')
            .setFieldTypeName('table');

        return this;
    },
    destroy: function () {
        var instance = Constructor._instance; //  STATIC || instance

        if (instance) {
            Global.removeEvents(instance._events);
        }
        $(document).off('click', '.modal_dialog[data-controller="edit_module"]');
        Constructor._instance = null; // static - instance can be only one
    },
};


/*---------------------------------*/
/*--         Element             --*/
/*---------------------------------*/

var Element = {
    sendRequest: function (_data, cb) {
        var _this = this;
        _data['extension_id'] = $('.constructor').data('extension_id');
        _data['wrapper'] = false;
        $.ajax({
            url: Global.urls.url_edit_module_add_element,
            data: _data,
            dataType: "html",
            type: "GET",
            async: false,
            success: function (data) {
                cb(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                Message.showErrorAjax(jqXHR, textStatus);
            },
        });
    },
    addBlockPanel: function (cb) {
        Element.sendRequest({element: 'block_panel'}, function (data) {
            cb(data);
        });
    },
    addBlockPanelContact: function (cb) {
        Element.sendRequest({element: 'block_panel_contact'}, function (data) {
            cb(data);
        });
    },
    addBlockButton: function (cb) {
        Element.sendRequest({element: 'block_button'}, function (data) {
            cb(data);
        });
    },
    addBlockActivity: function (cb) {
        Element.sendRequest({element: 'block_activity'}, function (data) {
            cb(data);
        });
    },
    addBlockParticipant: function (cb) {
        Element.sendRequest({element: 'block_participant'}, function (data) {
            cb(data);
        });
    },
    addBlockAttachments: function (cb) {
        Element.sendRequest({element: 'block_attachments'}, function (data) {
            cb(data);
        });
    },
    addSumModule: function (_relate_module_copy_id, _relate_module_template, cb) {
        Element.sendRequest({element: 'block_submodule', params: {'relate_module_copy_id': _relate_module_copy_id, 'relate_module_template': _relate_module_template}}, function (data) {
            cb(data);
        });
    },
    addPanelTable: function (cb) {
        Element.sendRequest({element: 'panel_table'}, function (data) {
            cb(data);
        });
    },
    addPanelField: function (cb) {
        Element.sendRequest({element: 'panel_field'}, function (data) {
            cb(data);
        });
    },
    addPanelBlockField: function (cb) {
        Element.sendRequest({element: 'block_field'}, function (data) {
            cb(data);
        });
    },
    /*
     addPanelFieldTitle : function(cb){
     Element.sendRequest({element : 'panel_field_title'}, function(data){
     cb(data);
     });
     },
     */
    addTableColumn: function (cb) {
        Element.sendRequest({element: 'table_column'}, function (data) {
            cb(data);
        });
    },
    addField: function (cb) {
        Element.sendRequest({element: 'field'}, function (data) {
            cb(data);
        });
    },
    addFieldHidden: function (cb) {
        Element.sendRequest({element: 'field_hidden'}, function (data) {
            cb(data);
        });
    },
    addButton: function (object, cb) {
        Element.sendRequest({element: $(object).data('type')}, function (data) {
            // проверка на "ответственный"
            if ($(object).data('type') == 'button_responsible') {
                var block_participant = $('.constructor .element[data-type="block_participant"]');
                if (typeof (block_participant) != 'undefined' && block_participant.length > 0) {
                    $('span.element[data-type="block_participant"]').parent().remove();
                    $('.buttons-block .btn-s').parent().remove();
                }
            }

            var block = $(object).closest('.buttons-block');
            var button_last = $(block).find('.element[data-type="button"]:last');
            if (button_last.length > 0)
                button_last.after(data);
            else
                $(block).prepend(data);

            $(object).addClass('hidden');
            if ($(block).find('.constructor_btn-add-block-button:not(.hidden)').length == 0)
                $(object).closest('.element[data-type="block_button_box"]').addClass('hidden');

            $(object).closest('.element[data-type="block_button"]').find('.selectpicker').selectpicker({style: 'btn-white'});

            if (typeof (cb) == 'function') cb(data);
        });
    },
    blockFieldTypeSetSize: function (_element, _class) {
        $(_element).removeClass('col-1 col-2 col-3 col-4 col-5').addClass(_class);

    }
};
