<?php
if($this->module->list_view_layout === true){
    $this->render('//site/list-view', get_defined_vars());
} else {
    $this->renderPartial('//site/list-view', get_defined_vars());
}
?>


<script type="text/javascript">

    $(".edit_view_dnt-add" ).on( "click", function(e) {

        var params = $(this).closest('#main-content').find('.sm_extension');
        $.ajax({
            url : Global.urls.url_participant_add_users +'?pci='+ params.data('parent_copy_id') + '&' + 'pdi=' + params.data('parent_data_id'),
            type : 'GET', async: false, dataType: "html",
            success: function(data){
                if(!data){
                    Message.show([{'type':'error', 'message': '<?php echo Yii::t('ParticipantModule.base','Participants project empty users'); ?>'}], false);
                    return;
                }
                modalDialog.show(data);
                if($(".submodule-table").find('tr.sm_extension_data').length) {
                   TableSearchInit(".submodule-table", ".submodule-search");
                }
                EditView.hiddenBlocks();
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });

        e.stopPropagation();
    });


    $(document).on('click', '.participant_list_view_btn-tie', function(){

        var input = $(modalDialog.getModalName() + ' table input.checkbox:checked');
        var _this = this;
        var _data = [];
        if(!input.length){
            Message.show([{'type':'error', 'message': 'It should be noted entries'}], true);
            return false;
        }

        input.each(function(){
            _data.push($(this).closest('.sm_extension_data').data('id'));
        });

        //var params = $(this).closest('#main-content').find('.sm_extension');
        $.ajax({
            url : Global.urls.url_participant_add_users,
            data : {'data_id_list' : _data, 'pci' : $(_this).closest('.sm_extension_relate_participant').data('parent_copy_id'), 'pdi' : $(_this).closest('.sm_extension_relate_participant').data('parent_data_id')},
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data && data.error){
                    Message.show([{'type':'error', 'message': data.error }], false);
                } else {
                    modalDialog.hide();
                    location.reload();
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message':Global.urls.url_ajax_error }], true);
            }
        });
    });

    inLineEdit.setCallbackSuccessAfterSave(null);
    inLineEdit['save'] = function(obj, cb){
        var obj_data, td, field_name;
        var _data = {};
        var _data_relate = [];
        var _data_element_responsible = [];

        $(obj).closest('table').find('tbody').find('tr.editing').find('td.data_edit').each(function(i, ul){
            td = $(ul);
            var names = inLineEdit.getFieldName(td)+'';
            if(names && names != 'undefined'){
                names = names.split(",");

                $(names).each(function(key, field_name){
                    if(field_name){
                        switch(elements[field_name]['type']){
                            case 'string':
                            case 'numeric':
                            case 'display':
                            case 'relate_string':
                                    obj_data = $(td).find('input[type="text"][name="EditViewModel['+field_name+']"]');
                                    _data[obj_data.attr('name')] = obj_data.val();
                                    break;
                            case 'datetime':
                                        var obj_date = $(td).find('input.date[name="EditViewModel['+field_name+']"]');
                                        var obj_time = $(td).find('input.time[name="EditViewModel['+field_name+']"]');
                                        if(obj_date+obj_time)
                                        _data[obj_date.attr('name')] = obj_date.val() + ' ' + obj_time.val();
                                        break;
                            case 'logical':
                            case 'select':
                            case 'permission':
                                        obj_data = $(td).find('select[name="EditViewModel['+field_name+']"]');
                                        _data[obj_data.attr('name')] = obj_data.val();
                                        break;
                            case 'access':
                                        obj_data = $(td).find('select[name="EditViewModel['+field_name+']"]');
                                        _data[obj_data.attr('name')] = {'id' : obj_data.val(), 'type' : obj_data.find('option[value="'+obj_data.val()+'"]').data('type')} ;
                                        break;
                            case 'file':
                            case 'file_image':
                                        var obj_box = $(td).find('.file-box[data-name="EditViewModel['+field_name+']"]');
                                        var _files = [];
                                        obj_box.find('input.upload_file').each(function(i, ul){
                                            if($(ul).val()) _files.push($(ul).val());
                                        });
                                        _data[obj_box.data('name')] = _files;
                                        break;
                            case  'relate':
                                        var obj_box = $(td).find('button[name="EditViewModel['+field_name+']"]');
                                        _data_relate.push({
                                            'name' : obj_box.attr('name'),
                                            'relate_copy_id' :  obj_box.data('relate_copy_id'),
                                            'id': obj_box.data('id'),
                                        })
                                        break;
                            case  'relate_this':
                                        var obj_box = $(td).find('button[name="EditViewModel['+field_name+']"]');
                                        _data[obj_box.attr('name')] = obj_box.data('id');
                                        break;
                            case  'relate_participant':
                                        var obj_box = $(td).find('button[name="EditViewModel['+field_name+']"]');
                                        _data_element_responsible.push({
                                            'name' : obj_box.attr('name'),
                                            'participant_id': obj_box.data('participant_id'),
                                            'ug_id': obj_box.data('ug_id'),
                                            'ug_type': obj_box.data('ug_type'),
                                        })
                                        break;
                                        
                        }
                    }
                });
            }
        });
        _data['id_list'] = [$(obj).data('id')];
        _data['element_relate'] = _data_relate;
        _data['element_responsible'] = _data_element_responsible;
        _data['parent_copy_id'] = $(obj).closest('.sm_extension').data('parent_copy_id');
        _data['parent_data_id'] = $(obj).closest('.sm_extension').data('parent_data_id');
        
        
        
        $.ajax({
            url : Global.urls.url_in_line_save +'/'+ $(obj).closest('.sm_extension').data('copy_id'),
            data : _data,
            type : 'POST', async: false, dataType: "json",
            success: function(data){
                if(data.status == 'save'){
                    inLineEdit.append(obj, data.element_data, data.id)
                    if(_data['EditViewModel[responsible]'] == "1") document.location.reload();
                    cb({'status' : true});
                    
                } else {
                    if(data.status == 'error_save'){
                        Message.show(data.messages, false);
                        cb({'status' : false});
                    } else {
                        if(data.status == 'error'){
                            Message.show(data.messages, false);
                            cb({'status' : false});
                        } else  if(data.status == 'access_error'){
                            Message.show(data.messages, false);
                            cb({'status' : false});
                        }                        
                    }
                }
            },
            error : function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error }], true);
            }
        });
        
        
    }


</script>
