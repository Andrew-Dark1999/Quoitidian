var Language = {

    set : function(language){
        $.get(Global.urls.url_set_language, {'language' : language}, function(data){
            if(data.status == true) document.location.reload();
        }, 'json');  
    },

}


$(document).on('change', '.element_language', function(){
   Language.set($(this).val()); 
});

