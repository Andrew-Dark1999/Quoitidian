var Select = {
    addOptions: function (pattern, data, refresh, options) {
        if (!Object.keys(data || {}).length) {
            return;
        }
        var element$ = $(pattern);
        for( var key in data) {
            element$.append($('<option>', {
                value: key,
                text: data[key]
            }));
        }
        if (refresh) {
            var selectPicker = element$.data().selectpicker;
            if (selectPicker) {
                selectPicker.refresh();
                return;
            }
            element$.selectpicker(options);
        }
    }
}
