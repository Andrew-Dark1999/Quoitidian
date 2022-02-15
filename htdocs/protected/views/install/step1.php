<div class="reg_background">
	<div class="wizz_form_wrapper">
		<img src="./static/images/wizz/logo.png" class="logo_img" />
        <div class="steps">
    		<div class="step1">
    			<div class="title">
    				<span class="number">1</span>
    				<span class="text"><?php echo Yii::t('install', 'Language selection'); ?></span>
    			</div>
    			<form>
    			  <select id="crm_language" name="crm_language" class="selectpicker">
    				  <option value="ru"<?php if(Yii::app()->language == 'ru') ?> >Русский</option>
    				  <option value="en"<?php if(Yii::app()->language == 'en') echo ' selected'; ?> >English</option>
    			  </select>
    			</form>
    			<button class="next"><?php echo Yii::t('install', 'Continue'); ?></button>
    		</div>
    </div>
	</div>
</div>

<script>

$('.selectpicker').selectpicker({style: 'btn-white'});


installer = {
    sendAjax : function(url, data, dataType, callback){
        $.ajax({
        	'url': url, 'type': 'POST', 'dataType': dataType, 'async' : false,
        	'data': data,
        	'success': function(data){
        	   callback(data);
        	},        
            error: function(){
                Message.show([{'type':'error', 'message': Global.urls.url_ajax_error}], true);
            },
         });
    },
    
    stepAdd : function(html){
        $('.steps').append(html);
        $('.selectpicker').selectpicker({style: 'btn-white'});
    },
    
    stepNext : function(step_this, step_next){
		$('.'  + step_this).animate({height: 0}, 'fast', function(){
			$('.'  + step_next).animate({height: 780}, 'fast' , function(){
                $('.reg_background').css('height','auto');
                $('.reg_background').css('height',$(window).height()+'px');
            });
		});          
    },
    
    stepRun : function(step_this, step_next){
        switch(step_this){
            case  'step1':
                installer.sendAjax('/install/step1/', data, 'json', function(data){
                    installer.stepAdd(data.html);
                    installer.stepNext(step_this, step_next);
                });
                break;
            case  'step2':
                var data  = {
                    'crm_language' : $('#crm_language :selected').val(),
                    'crm_name' : $('#crm_name').val(),
                    'crm_description' : $('#crm_description').val(),
                    'admin_email' : $('#admin_email').val(),
                    'admin_password' : $('#admin_password').val(),
                }
                installer.sendAjax('/install/step2/', data, 'json', function(data){
                    if(data.status == true){
                        installer.stepAdd(data.html);
                        installer.stepNext(step_this, step_next);
                        $('.errorMessage').remove();
                    } else{
                        $('.steps .' + step_this).remove();
                        installer.stepAdd(data.html);
                        $('.steps .' + step_this).css({'overflow': 'hidden', 'height': 750});
                        $('.errorMessage').prev().addClass('insterr');
                    }
                });
                break;
            case  'step3':
                var data  = {
                    'crm_language' : $('#crm_language :selected').val(),
                    'crm_name' : $('#crm_name').val(),
                    'crm_description' : $('#crm_description').val(),
                    'admin_email' : $('#admin_email').val(),
                    'admin_password' : $('#admin_password').val(),
                    'db_type' : $('#db_type').val(),
                    'db_server_name' : $('#db_server_name').val(),
                    'db_user' : $('#db_user').val(),
                    'db_password' : $('#db_password').val(),
                    'db_name' : $('#db_name').val(),
                    'db_name_create' : ($('#db_name_create').attr('checked') ? '1' : '0'),
                    'db_prefix' : $('#db_prefix').val(),
                }
                installer.sendAjax('/install/step3/', data, 'json', function(data){
                    if(data.status == true){
                        installer.stepAdd(data.html);
                        installer.stepNext(step_this, step_next);
                        $('.errorMessage').remove();
                    } else{
                        $('.steps .' + step_this).remove();
                        installer.stepAdd(data.html);
                        $('.steps .' + step_this).css({'overflow': 'hidden', 'height': 835});
                        $('.errorMessage').prev().addClass('insterr');
                        $('.reg_background').css('height','auto');
                        $('.reg_background').css('height',$(window).height()+'px');
                    }
                });
                break;
        };
    },
        
    
    
    
    
}






$(document).ready(function() {

    $('.reg_background').css('height',$(window).height()+'px');
    $('.wizz_form_wrapper').css('margin-top','295px');
    $('.wizz_form_wrapper .step1').height($(window).height()-351);

	/*if($(window).height() > 700){
		$('.wizz_form_wrapper').css('margin-top', ($(window).height()-$('.wizz_form_wrapper').height())/2);
	}
    
    
    var step2 = $("#formstep2").validate({
        rules: {
                crm_name: {required: true},
                crm_description: {required: true},
                admin_email : {required: true, email:true},
                admin_password : {required: true},
        },
        highlight: function(element, errorClass) {
                $(element).addClass('error');
        },
        unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('error');
        }
    });
    */

/*
    var step3 = $("#formstep3").validate({
        rules: {
                db_type: {required: true},
                db_server_name: {required: true},
                db_user: {required: true},
                db_password: {required: true},
                db_name: {required: true},
        },
        highlight: function(element, errorClass) {
                $(element).addClass('error');
        },
        unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('error');
        }
    });
	*/
    
    $('#crm_language').on('change', function() {
        $.ajax({
        	url: '/install/index/',
        	type: 'POST',
        	dataType: 'json',
        	data: { crm_language : $(this).val()},
        	success: function (data) {
                if(data.status){
            	    location.reload(true);
                }
        	}
        });
    });
    

   
	$(document).on('click', '.step1 .next', function(){
	   installer.stepRun('step1', 'step2');
	});
    
	$(document).on('click', '.step2 .next', function(){
	   installer.stepRun('step2', 'step3');
	});

    $(document).on('click', '.step3 .next', function(){
        //if(step3.form()){
            installer.stepRun('step3', 'step4') 
        //}
	});
});
</script>
