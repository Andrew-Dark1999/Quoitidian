// Show/Hide rows in the ListView
//ThDisplay
var ListViewDisplay = {
    _this : null,
    _index : null,
    _hidden_group_index : [],
    
    setThis : function(_this){
        ListViewDisplay._this = _this;
        return this;
    },
    
    setIndex : function(index){
        if(!index){
            if(ListViewDisplay._this == null)
                index = $('.local-storage').data('hidden_index');
            else index = $(ListViewDisplay._this).closest('.local-storage').data('hidden_index');
        }
        ListViewDisplay._index = index;
        return this;
    },
    getColumnWidth : function(){
        var col_index = {};
        $('.list-table thead').find('th').each(function(i, ul){
            if(i==0) return true;
            if($(ul).css('display') == 'none') return true;
            col_index[$(ul).data('name')] = $(ul).width();
        });
        return col_index;
    },
    setHiddenGroupIndex : function(context){
        var table = $('.list_view_block .crm-table');
        ListViewDisplay._hidden_group_index = [];
        $('.table-dropdown').find('input[type="checkbox"]').each(function(i, ul){
            var el = $(ul);
            if(el.attr('checked') != 'checked'){
                var index = table.find('th[data-group_index="'+el.data('group_index')+'"]').hide().index();
                ListViewDisplay.hideAllRows(table, index, true);
                ListViewDisplay._hidden_group_index.push(el.data('group_index'));
            } else {
                var index = table.find('th[data-group_index="'+el.data('group_index')+'"]').show().index();
                ListViewDisplay.hideAllRows(table, index, false);
            }
        });
        ListViewDisplay.writeLocalStorage(context);
        ListView.editLinkreDraw();
        return this;
    },

    setFromStorage : function(){
        ListViewDisplay.readLocalStorage();
        $('.table-dropdown ul input').each(function(i, ul){
            if($.inArray($(ul).data('group_index')+'', ListViewDisplay._hidden_group_index) != -1){
                $(ul).attr('checked', false);
            } else {
                $(ul).attr('checked', true);
            }
        });

        return this;
    },

    hideAllRows : function(table, index, hide){
        table.find('tbody tr').each(function(i, ul){
            if(hide == true){
                $(ul).children('td').eq(index).hide();
            } else {
                $(ul).children('td').eq(index).show();
            }
        });
    },

    writeLocalStorage : function(context){
        var lStorage = new LocalStorage();

        lStorage
            .clear()
            .setKey('list_th_hide')
            .setValueToServer(ListViewDisplay._index, ListViewDisplay._hidden_group_index, null, context);
    },

    readLocalStorage : function(){
        var lStorage = new LocalStorage();

        lStorage
            .clear()
            .setKey('list_th_hide')
            .getValueFromServer(ListViewDisplay._index, function(data){ ListViewDisplay._hidden_group_index = data; });
    },
}




var ListViewPosition = {
    _this : null,
    _index : null,
    _storage_value : {},

    setThis : function(_this){
        this._this = _this;
        return this;
    },

    prepare : function(){
        this.prepareStorageValue();
        this.prepareIndex();

        return this;
    },

    prepareStorageValue : function(){
        var _this = this;

        $.each($(this._this).find('th[data-name]'), function (key, value) {
            var $value = $(value);
            _this._storage_value[$value.data('name')] = $value.index();
        })

        return this;
    },

    prepareIndex : function(){
        this._index = this._this.data('sort_index');
        return this;
    },


    writeLocalStorage : function(context){
        var lStorage = new LocalStorage();
        var _this = this;

        lStorage
            .clear()
            .setKey('list_th_position')
            .setValueToServer(_this._index, _this._storage_value, null, context);
    },


}
