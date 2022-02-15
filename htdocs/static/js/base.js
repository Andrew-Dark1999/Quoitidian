/**
 * Created by andrew on 7/25/17.
 */
Base = {
    copyObject : function (paramObject) {
        var object = Object.create(null);

        for(var key in paramObject) {
            object[key] = paramObject[key];
        }
        return object;
    },
    isListView : function() {
        return $('.list_view_block').length ? true : false;
    },
    isProcessView : function() {
        return $('.process_view_block').length ? true : false;
    },
    addEvents : function (events) {
        $.each(events, function (i, data) {
            $(document).off(data.event, data.name).on(data.event, data.name, data.func);
        });
    },
    removeEvents : function (events) {
        $.each(events, function (i, data) {
            $(document).off(data.event, data.name);
        });
    }
}
