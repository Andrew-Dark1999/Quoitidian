var Sorting = {
    _this : null,
    _params : null,
    index : null,
    
    
    init : function(){
        this.setIndex();
        this.setParams();
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
            this.index = $(this._this).closest('.local-storage').data('sort_index');
        
        return this;
    },
    getParams: function(){
        return this._params;  
    },
    setParams : function(){
        var direction = 'a';
        if($(this._this).hasClass('sorting_asc')) direction = 'd';
        var name = $(this._this).data('name');
        if(typeof(name) != 'undefined' && name){
            name = name.split(',');
            var params = {};
            $.each(name, function(key, value){
                params[value] = direction;  
            });
            
            this._params = params; 
        }
        return this;
    },

    getParamsToUrl : function(callback){
        var params = this.getParams();
        if(params) return 'sort=' + JSON.stringify(params);
        else return '';
    },

    getFullUrl : function(){
        var params = Url.parseURLParams();

        var params_parse = [];

        $.each(params, function(key, value){
            if(key != 'sort') params_parse.push(key +'='+ value);
        });

        params_parse = params_parse.join('&');

        var sorting_params = Sorting.getParamsToUrl();
        var url = window.location.href.split("?");
        url = url[0] + (params_parse ? '?' + params_parse : '') + (params_parse ? (sorting_params ? '&' + sorting_params : '') : (sorting_params ? '?' + sorting_params : '')) ;

        return url;
    },

    apply : function(callback){
        var url = Sorting.getFullUrl(),
            action_key = $(this._this).closest('table#list-table, table#settings-table, .element[data-name="process_view_fields_group"]').data('action_key'),
            vars = instanceGlobal.contentReload.getContentVars(action_key);

        instanceGlobal.contentReload
            .clear()
            .setObject(this._this)
            .setActionKey(action_key)
            .setVars(vars)
            .setUrl(url)
            .setCallBackComplete(function () {
                if ($.isFunction(callback)) {
                    callback();
                }
            })
            .run();
    },
}




$(document).ready(function(){
    $(document).on('mouseup', '.list-table th.sorting .sorting-arrows', function(e){

        e.preventDefault();
        e.stopPropagation();

        instanceGlobal.preloaderShow($(this));

        Sorting.setThis($(this).parent()).init().apply()
    })





});




