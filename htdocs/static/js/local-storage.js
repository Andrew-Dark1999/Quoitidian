var LocalStorage = function(){
    for(var key in LocalStorageObject) {
        this[key] = LocalStorageObject[key];
    }
}


var LocalStorageObject = {
    key : '',
    value : '',
    pci : null,
    pdi : null,
    async : false,

    clear : function(){
        this.key = '';
        this.value = '';
        this.pci = null;
        this.pdi = null;
        this.async = false;

        return this;
    },
    isLocalStorageAvailable : function(){
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    },
    writeStorage : function(key, value){
        if(this.isLocalStorageAvailable) localStorage.setItem(key, value);
        return this;
    },
    readStorage : function(key){
        if(this.isLocalStorageAvailable) return localStorage.getItem(key);
    },
    setKey : function(key){
        this.key = key;
        return this;
    },
    getKey : function(){
        return this.key;
    },
    setPci : function(pci){
        this.pci = pci;
        return this;
    },
    setPdi : function(pdi){
        this.pdi = pdi;
        return this;
    },
    setAsync : function(async){
        this.async = async;
        return this;
    },

    /**
    *   Запись параметра в локальное хранилище
        index - уникальний индекс данных в массиве 
        value_append - сами данные
        set_in_array - если true, данние из value_append будут помещены в мыссив
        append - если true, данные будут добавлены к имеющимся  
    */
    setValue : function(index, value_append, set_in_array, append){
        var value = this.readStorage(this.getKey());
        var beSet = false;
        value = JSON.parse(value);

        var result = {}; 
        var value_new = [];
        if(!$.isEmptyObject(value)){
            $.each(value, function(key, val){
                if(key == index){
                    value_new = val;
                    if(append == true){
                        if($.isArray(value_new))
                            value_new.push(value_append);
                        else value_new += value_append;
                    } else {
                        if(set_in_array == true)
                            value_new = [value_append];
                        else value_new = value_append;
                    }
                    result[key] = value_new;
                    beSet = true;
                } else {
                    result[key] = val;
                }
            });
        }
        if(beSet == false){
            if(set_in_array == true)
                result[index] = [value_append];
            else
                result[index] = value_append;
        } 
        this.writeStorage(this.getKey(), JSON.stringify(result));
        
        return this;
    },
    
    
    getValue : function(index){
        var value = this.readStorage(this.getKey());
        value = JSON.parse(value);
        return (value && value[index] ? value[index] : false);
    },
    
    setValueToServer : function(index, value, callback, context){
        if (context && !AjaxContainers[context._action_key]) {
            return;
        }

        AjaxObj
            .createInstance()
            .setUrl(Global.urls.url_set_user_storage)
            .setData({
                'type' : this.getKey(),
                'index' : index,
                'value' : value,
                'pci' : this.pci,
                'pdi' : this.pdi,
            })
            .setType('POST')
            .setDataType('json')
            .setAsync(this.async)
            .setCallBackSuccess(function(data) {
                if(typeof(callback) == 'function') callback(data);
            })
            .setCallBackError(function(){
                if(typeof(callback) == 'function') callback(false);
            })
            .send();
    },

    getValueFromServer : function(index, callback){
        $.ajax({
            url: Global.urls.url_get_user_storage,
            data : {
                'type' : this.getKey(),
                'index' : index,
                'pci' : this.pci,
                'pdi' : this.pdi,
            },
            dataType: "json", type: "POST", async: this.async,
            success: function(data) {
                if(data.status == true)
                    if(typeof(callback) == 'function') callback(data.value);
                else
                    if(typeof(callback) == 'function') callback(false);
            },
            error: function(){
                if(typeof(callback) == 'function') callback(false);
            },
        });
    },

    deleteFromServer : function(index, callback){
        $.ajax({
            url: $('#global_params').data('delete_user_storage'),
            data : {
                'type' : this.getKey(),
                'index' : index,
                'pci' : this.pci,
                'pdi' : this.pdi,
            },
            dataType: "json", type: "POST", async: this.async,
            success: function(data) {
                if(data.status = true)
                    if(typeof(callback) == 'function') callback(data.value);
                else
                    if(typeof(callback) == 'function') callback(false);
            },
            error: function(){
                if(typeof(callback) == 'function') callback(false);
            },
        });
    },

    delete : function(index){
        var value = this.readStorage(this.getKey());
        value = JSON.parse(value);

        var result = {}; 
        if(!$.isEmptyObject(value)){
            $.each(value, function(key, val){
                if(key == index){
                    return true;  
                } else {
                    result[key] = val;
                }
            });
        }
        this.writeStorage(this.getKey(), JSON.stringify(result));
        
        return this;
    },    
    
   
    
}
