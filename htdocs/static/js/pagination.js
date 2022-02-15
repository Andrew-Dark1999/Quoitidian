var Pagination = {
    _this : null,
    _params : null,
    index : null,
    
    init : function(active_page){
        this.setIndex();
        this.setParams(active_page);
        return this;
    },
    setThis : function(_this){
        this._this = _this;
        return this;
    },
    setIndex : function(index){
        if(index)
            this.index = index;
        else
            this.index = $(this._this).closest('.local-storage').data('pagination_index');
        
        return this;
    },
    getParams: function(){
        return this._params;  
    },
    setParams : function(active_page){
        var params = {};
        if($('.pagination_size').val()) params['page_size'] = $('.pagination_size').val();
        if(typeof(active_page) != 'undefined' && active_page) params['page'] = active_page;
        this._params = params;
        
        return this;
    },
        
    getParamsToUrl : function(){
        var result = [];
        var params = this.getParams();
        if(params){
            $.each(params, function(key, value){
               result.push(key+'='+value);  
            });
            return result.join('&');
        } else return '';
    }, 
    
    getFullUrl : function(){
        var params = Url.parseURLParams();
        
        var params_parse = [];
        $.each(params, function(key, value){
            if(key != 'page' && key != 'page_size') params_parse.push(key +'='+ value);
        });
        
        params_parse = params_parse.join('&');
        
        var pagination_params = Pagination.getParamsToUrl();
        var url = window.location.href.split("?");
        url = url[0] + (params_parse ? '?' + params_parse : '') + (params_parse ? (pagination_params ? '&' + pagination_params : '') : (pagination_params ? '?' + pagination_params : '')) ;  
                
        return url;
    },
    
    
    apply : function(){
        var url = Pagination.getFullUrl();
        var action_key = $(Pagination._this).closest('.element[data-type="pagination_block"]').data('action_key');
        var vars = instanceGlobal.contentReload.getContentVars(action_key);

        Global.getInstance().setContentReloadInstance(instanceGlobal.contentReload);

        instanceGlobal.contentReload
            .clear()
            .setObject(Pagination._this)
            .setActionKey(action_key)
            .setVars(vars)
            .setUrl(url)
            .run();
    },
    
    
}


$(document).ready(function(){
    var eventPath = '.pagination .info .form-control';
    $(document).off('blur', eventPath).on('blur', eventPath, function(){

        if(!$(this).val()){
            $(this).val(1);
            return;
        }

        if(parseInt($(this).val()) > parseInt($(this).data('max-page'))){
           $(this).val($(this).data('max-page'));
        }

    });

    eventPath = '.pagination .info .form-control';
    $(document).off('keydown', eventPath).on('keydown', eventPath, function(e){

        if(parseInt($(this).data('max-page')) == 1){ //only one page
           e.preventDefault();
           return;
        }

        // Allow: backspace, delete, tab, escape, enter
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40))
        {

            // Allow: enter
            if(e.keyCode ==13){

                var page = parseInt($(this).val());

                if(!page){
                    page = 1
                    $(this).val(page);
                }

                if(page == 1){
                    var prev = $(this).closest('.pagination').find('.prev');
                    if(prev && prev.hasClass('disabled')){
                        return;
                    }
                }

                if(page > parseInt($(this).data('max-page'))){

                    page = parseInt($(this).data('max-page'));
                    $(this).val(page);

                    var next = $(this).closest('.pagination').find('.next');
                    if(next && next.hasClass('disabled')){
                        return;
                    }

                }

                instanceGlobal.preloaderShow($(this));
                Pagination.setThis(this).init(page).apply($(this));

            }
            // let it happen, don't do anything
            return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

    });

    eventPath = '.pagination .page, .pagination .prev a, .pagination .next a';
    $(document).off('click', eventPath).on('click', eventPath, function(){
        var active_page,
            $this = $(this);

        if ($this.closest('li').is('.disabled')) {
            return;
        }
        instanceGlobal.preloaderShow($this);

        active_page = $this.data('active_page');
        if (active_page) {
            Pagination.setThis(this).init(active_page).apply();
        }
    });

    eventPath = '.pagination_size';
    $(document).off('change', eventPath).on('change', eventPath, function(){
        instanceGlobal.preloaderShow($(this));

        Pagination.setThis(this).init().apply();
    });
});