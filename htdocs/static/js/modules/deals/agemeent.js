var _deal_square = '';
var _deal_object_parammetrprice = '';
var _deal_agreement_sum = '';

$(document).off('click','.btn.actions .element[data-type="make_contract"]');
$(document).off('click','.deal_go:last');
$(document).off('keyup','.deal_object_parammetrprice:last, .deal_discount:last, .deal_discount:last, .deal_minus:last');
$(document).on('click','.btn.actions .element[data-type="make_contract"]', function(){

    if($('.sdelkin_id:last').val() > 0) {
        showAgreementPopup($('.sdelkin_id:last').val());
    }else {
        //это новая сделка, сначала сохраняем запись
        EditView.save($('.extension_copy_id:last').val(), {}, function(data){
            var edit_view = $(modalDialog.getModalName()).find('.edit-view');
            $(edit_view).closest('.edit-view').data('id', data.id);
            $('.sdelkin_id:last').val(data.id);
            showAgreementPopup(data.id);
        });       
    }
});
$(document).on('change','.steps_counter', function(){
    
  $('.deal_1_sum:last').val('0');
  $('.deal_1_square:last').val('');  
  
  if ($(this).val()=='1') {
    $('.deal_1_sum:last').val(window._deal_agreement_sum);
    $('.deal_1_square:last').val(window._deal_square);
    $('.make_contract .element[step-order="2"]').hide();
    $('.make_contract .element[step-order="3"]').hide();
  } else if ($(this).val()=='2') {
    $('.make_contract .element[step-order="2"]').show();
    $('.make_contract .element[step-order="3"]').hide();
  } else {
    $('.make_contract .element[step-order="2"]').show();
    $('.make_contract .element[step-order="3"]').show();
  }
});
$(document).on('keyup', '.deal_object_parammetrprice:last', function() {
    $('.deal_discount:last').val('');
    $('.deal_minus:last').val('');
    calculate(true);
});
$(document).on('keyup', '.deal_discount:last, .deal_minus:last', function() {
    calculate(false);
});

$(document).on('click', '.deal_go:last', function() {
    
    var btn = $(this);                        
    var sum_levels =  $('.deal_1_sum:last').val();
    var square_levels = $('.deal_1_square:last').val();
    
    var set_date = true;
    var level_empty_date = false;
    
    var set_square = true;
    
    var compare_date = '';
    
    if($('.deal_1_date:last').val()=='') {
        set_date = false;
        level_empty_date = 1;
    }else {
        
        var d = $('.deal_1_date:last').val().split('.');
        var date1 = new Date(Number(d[2]), Number(d[1])-1, Number(d[0]));
        
        var today = new Date();
        today.setHours(0,0,0,0);

        if(today > date1) 
            compare_date = $('.empty_parameter_4:last').val(); 
    }
    

    if(Number($('.deal_1_square:last').val())==0) {
        set_square = false;
    }
    
    var levels = $(".deal_level_count option:selected").last().val();
    
    if(levels > 1) {
        sum_levels = Number ( sum_levels ) + Number ( $('.deal_2_sum:last').val() );
        square_levels =  Number ( square_levels ) + Number ( $('.deal_2_square:last').val() );
        if($('.deal_2_date:last').val()=='') {
            set_date = false;
            level_empty_date = 2;
        }else {
             
            if(Number($('.deal_2_square:last').val())==0) {
                set_square = false;
            } else {
             
                var d2 = $('.deal_2_date:last').val().split('.');
                var date2 = new Date(Number(d2[2]), Number(d2[1])-1, Number(d2[0]));

                if(date1 > date2) 
                    compare_date = $('.empty_parameter_5:last').val(); 
               
                if(levels == 3) {
                    sum_levels =  Number ( sum_levels ) + Number ( $('.deal_3_sum:last').val() );
                    square_levels =  Number ( square_levels ) + Number ( $('.deal_3_square:last').val() );
                    if($('.deal_3_date:last').val()=='') {
                        set_date = false;
                        level_empty_date = 3; 
                    }else {
                        
                        if(Number($('.deal_3_square:last').val())==0) {
                            set_square = false;
                        }else {
                        
                            var d3 = $('.deal_3_date:last').val().split('.');
                            var date3 = new Date(Number(d3[2]), Number(d3[1])-1, Number(d3[0]));

                            if(date2 > date3) 
                                compare_date = $('.empty_parameter_6:last').val(); 
                        
                        }
                    }
                }
            } 
        }
    }

    if(set_date) {
        if(compare_date == '') {
            if(set_square) {
                if( Number(square_levels) == Number ( $('.deal_square:last').val())) {
                    
                    var check_sum = true;
                    
                    if( Number(sum_levels) != Number ( $('.deal_agreement_sum:last').val())) 
                        check_sum = false;
                            
                   if(check_sum) {
                        
                        params = {};
                        params['deal_square'] = $('.deal_square:last').val();
                        params['deal_discount'] = $('.deal_discount:last').val();
                        params['deal_object_parammetrprice'] = $('.deal_object_parammetrprice:last').val();
                        params['deal_add_payments'] = $('.deal_add_payments:last').val();
                        params['deal_minus'] = $('.deal_minus:last').val();
                        params['deal_agreement_sum'] = $('.deal_agreement_sum:last').val();
                        params['deal_level_count'] = $(".deal_level_count option:selected").last().val();
                        params['deal_j_property'] = $(".deal_j_property option:selected").last().val();
                        params['deal_1_sum'] = $('.deal_1_sum:last').val();
                        params['deal_1_square'] = $('.deal_1_square:last').val();
                        params['deal_1_date'] = $('.deal_1_date:last').val();
                        params['deal_2_sum'] = $('.deal_2_sum:last').val();
                        params['deal_2_square'] = $('.deal_2_square:last').val();
                        params['deal_2_date'] = $('.deal_2_date:last').val();
                        params['deal_3_sum'] = $('.deal_3_sum:last').val();
                        params['deal_3_square'] = $('.deal_3_square:last').val();
                        params['deal_3_date'] = $('.deal_3_date:last').val();
                        params['deal_jur_service'] = $('.deal_jur_service:last').val();
                        params['deal_signed'] = $(".deal_signed option:selected").last().val();
                        params['deal_date'] = $('.deal_date:last').val();
                        params['deal_id'] = $('.sdelkin_id:last').val();
                        params['template_id'] = $(".deal_template option:selected").last().val();
                        
                        var ajax = new Ajax();
                        ajax
                          .setUrl('/module/deals/MakeAgreement/' + $('.extension_copy_id:last').val())
                          .setData(params)
                          .setDataType('json')
                          .setCallBackSuccess(function(data){
                                 var doc_id = data.id;
                                 var _data = {};
                                
                                _data['primary_entities'] = {
                                    'primary_pci' : 181,
                                    'primary_pdi' : $('.sdelkin_id:last').val(),
                                  };
                                _data['parent_copy_id'] = 181;
                                _data['parent_data_id'] = $('.sdelkin_id:last').val();
                                _data['this_template'] = 0;
                                _data['relate_template'] = 0;
                                _data['parent_object'] = 'sub_module';
                                _data['id'] = doc_id;
                          
                                //_data['parent_relate_data_list'] = EditView.relateDataStory.getRelateDataList();
                               
                                btn.closest('.modal').modal('hide');
                                
                                $('#deal_sum').val(params['deal_agreement_sum']);
                                $('#EditViewModel_deal_salesdate').val(params['deal_date']);
                                var fields = JSON.parse(data.ev_refresh_fields);
                                $('#deal_contract_number').val(fields['deal_contract_number']);
                                $('[data-name="EditViewModel[module_title]"]').text(fields['deal_contract_number']);

                                var $d = $('#deal_creditinsurecompany');
                                $d.val(fields['deal_creditinsurecompany']);
                                $d.next().find('button span.filter-option').text($d.find('[value="'+$d.val()+'"]').text());
                                
                                $('#deal_creditinsurenumber').val(fields['deal_creditinsurenumber']);
                                $('.date#EditViewModel_deal_creditinsuredate').val(fields['deal_creditinsuredate_1']);
                                $('.time#EditViewModel_deal_creditinsuredate').val(fields['deal_creditinsuredate_2']);

                                
                                $.ajax({
                                    url: Global.urls.url_edit_view_edit + '/11',
                                    data : _data,
                                    dataType: "json",
                                    type: "POST",
                                    success: function(data){
                                        if(data.status == 'error'){
                                            Message.show(data.messages, false);
                                        } else {
                                            if(data.status == 'data'){
                                                modalDialog.show(data.data, true);
                                                EditView.setBlockDisplayStatus($('.edit-view[data-copy_id="11"]'));
                                                var $modal = $(modalDialog.getModalName()).find('.client-name');
                                                if ( $modal.find('span').first().text() == "" ) {
                                                    $modal.find('.edit-dropdown').first().addClass('open');
                                                }                        
                                                $('.form-control.time').each(function(){
                                                    initElements('.edit-view', $(_this).val());
                                                });
                                                Global.createLinkByEV($('.edit-view.in:last'));
                                            }
                                        }
                                    },
                                    error: function(){
                                        Message.show([{'type':'error', 'message':Global.urls.url_ajax_error}], true);
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
                                
                               
                                
                          })
                          .setCallBackError(function(jqXHR, textStatus, errorThrown){
                                Message.showErrorAjax(jqXHR, textStatus);
                          })
                          .send();     
                                 
                   }else
                        Message.show([{'type':'error', 'message': $('.empty_parameter_2:last').val()}], true);
                }else
                    Message.show([{'type':'error', 'message': $('.empty_parameter_1:last').val()}], true);
            }else
                Message.show([{'type':'error', 'message': $('.empty_parameter_3:last').val()}], true);
        }else
            Message.show([{'type':'error', 'message':compare_date}], true);
    }else {
        
        step = {};
        step['level'] = level_empty_date;
        
        var ajax = new Ajax();
        ajax
          .setUrl('/module/deals/GetEmptyParametersMessages/' + $('.extension_copy_id:last').val())
          .setData(step)
          .setDataType('json')
          .setCallBackSuccess(function(data){
                Message.show([{'type':'error', 'message':JSON.stringify(data.error)}], true);
          })
          .setCallBackError(function(jqXHR, textStatus, errorThrown){
                Message.showErrorAjax(jqXHR, textStatus);
          })
          .send();  

    }    
});

function calculate(use_default){
    
    var discount = $('.deal_discount:last').val();
    var deal_minus = $('.deal_minus:last').val();
    var metrpice = Math.round(window._deal_object_parammetrprice);
    var deal_object_parammetrprice_without_rounding = $('.deal_object_parammetrprice:last').val();
    
    if(!use_default) {
        
        if($.isNumeric(discount)) {
            if(discount>100) {
                $('.deal_discount:last').val('100');
                discount = 100;
            }
            var square = metrpice - metrpice * discount / 100;
            $('.deal_object_parammetrprice:last').val(square);
        }else{
            $('.deal_discount:last').val('');  
        }
        
        if(!$.isNumeric(deal_minus)) {
            $('.deal_minus:last').val('');
        }
        
        deal_object_parammetrprice_without_rounding = ( metrpice * ( 1 - Number ($('.deal_discount:last').val())/100 ) * $('.deal_square:last').val() - $('.deal_minus:last').val() ) / $('.deal_square:last').val();
        $('.deal_object_parammetrprice:last').val(parseFloat(deal_object_parammetrprice_without_rounding).toFixed(0));
    
    }
    
    
    var deal_agreement_sum = Number ( $('.deal_square:last').val() * deal_object_parammetrprice_without_rounding ) + Number ( $('.deal_add_payments:last').val() );
    $('.deal_agreement_sum:last').val(parseFloat(deal_agreement_sum).toFixed(0));

}

function showAgreementPopup(deal_id) {
    
    param = {};
    param['deal_id'] = deal_id;
                      
    var condition = false;                  

    var ajax = new Ajax();
    ajax
      .setUrl('/module/deals/checkConditions/' + $('.extension_copy_id:last').val())
      .setData(param)
      .setDataType('json')
      .setCallBackSuccess(function(data){

            if(data.error==0) {
                modalDialog.show($('.actions').next().html());
                if(data.default_data) {
                    window._deal_square = data.default_data['square'];
                    window._deal_object_parammetrprice = data.default_data['object_parammetrprice'];
                    window._deal_agreement_sum = data.default_data['agreement_sum'];
                    
                    $('.deal_square:last').val(data.default_data['square']);
                    $('.deal_object_parammetrprice:last').val(data.default_data['object_parammetrprice']);
                    $('.deal_add_payments:last').val(data.default_data['add_payments']);
                    $('.deal_agreement_sum:last').val(data.default_data['agreement_sum']);
                    $('.deal_1_sum:last').val(data.default_data['agreement_sum']);
                    $('.deal_1_square:last').val(data.default_data['square']);
                }
                condition = true;
            }else
                condition = data.error;
      })
      .setCallBackError(function(jqXHR, textStatus, errorThrown){
            Message.showErrorAjax(jqXHR, textStatus);
      })
      .send();  

     if(condition==true) {
          
          $('.modal.in .modal-dialog.hidden').removeClass('hidden').css('margin-top','100px');
          $('.modal.in .make_contract').find('.select+.select').remove();
          $('.modal.in .make_contract').find('.select').selectpicker({ style: 'btn-white'});
          $('.modal.in .make_contract .date').datepicker('update');
          
          $('.element[step-order]').hide().filter('[step-order=1]').show();
          
          $('.element-is-scroll').each(function () {
              var _this = $(this);
              niceScrollCreate(_this.find('ul.dropdown-menu.inner.selectpicker'));
          });
     }else {     
        Message.show([{'type':'error', 'message':condition}], true);
    }  
    
}
